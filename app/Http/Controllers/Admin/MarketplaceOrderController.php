<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Notification;
use App\Models\Wallet;
use App\Services\PlatformWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceOrderController extends Controller
{
    /**
     * Admin Marketplace Orders List (Supports AJAX and HTML)
     */
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $q = trim($request->string('q')->toString());
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $query = MarketplaceOrder::query()
            ->with([
                'seller:id,name,email,username,phone',
                'buyer:id,name,email,username,phone',
                'items.product:id,title,type,price',
                'items.product.primaryImage'
            ]);

        // Filter status
        if ($status !== '') {
            $query->where('status', $status);
        }

        // Search query
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('shipping_tracking_number', 'like', "%{$q}%")
                    ->orWhereHas('buyer', function ($buyerQ) use ($q) {
                        $buyerQ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    })
                    ->orWhereHas('seller', function ($sellerQ) use ($q) {
                        $sellerQ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    });
            });
        }

        // Filter date
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Optimized Financial Metrics
        $metrics = [
            'total_gmv' => (float) MarketplaceOrder::where('status', 'completed')->sum('total_amount'),
            'total_platform_income' => (float) MarketplaceOrder::where('status', 'completed')->sum('commission_amount'),
            'total_escrow_funds' => (float) MarketplaceOrder::whereIn('status', ['paid', 'shipped', 'disputed'])->sum('total_amount'),
            'disputed_count' => MarketplaceOrder::where('status', 'disputed')->count(),
            'paid_count' => MarketplaceOrder::where('status', 'paid')->count(),
            'shipped_count' => MarketplaceOrder::where('status', 'shipped')->count(),
        ];

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            $html = view('admin.marketplace.orders.partials.order-rows', compact('orders'))->render();
            $pagination = view('admin.marketplace.orders.partials.pagination', compact('orders'))->render();
            return response()->json([
                'status' => 'success',
                'html' => $html,
                'pagination' => $pagination,
                'total' => $orders->total(),
                'metrics' => $metrics
            ]);
        }

        return view('admin.marketplace.orders.index', compact('orders', 'metrics', 'status', 'q', 'dateFrom', 'dateTo'));
    }

    /**
     * View detailed order breakdown
     */
    public function show(MarketplaceOrder $order)
    {
        $order->load([
            'seller:id,name,email,username,phone,avatar',
            'seller.city',
            'buyer:id,name,email,username,phone,avatar',
            'items.product:id,title,type,price,slug',
            'items.product.primaryImage'
        ]);

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'order' => $order
            ]);
        }

        return view('admin.marketplace.orders.show', compact('order'));
    }

    /**
     * Update Shipping Tracking Number by Admin
     */
    public function updateTracking(Request $request, MarketplaceOrder $order)
    {
        $request->validate([
            'shipping_tracking_number' => 'required|string|max:100',
        ]);

        $order->update([
            'shipping_tracking_number' => $request->shipping_tracking_number,
            'status' => $order->status === 'paid' ? 'shipped' : $order->status
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Nomor resi berhasil diperbarui.',
                'order' => $order
            ]);
        }

        return back()->with('success', 'Nomor resi berhasil diperbarui.');
    }

    /**
     * Resolve Dispute: Force Complete & Release Funds to Seller
     */
    public function resolveReleaseToSeller(Request $request, MarketplaceOrder $order)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        if (in_array($order->status, ['completed', 'cancelled'])) {
            $msg = 'Pesanan ini sudah selesai atau dibatalkan sebelumnya.';
            return $request->ajax() ? response()->json(['status' => 'error', 'message' => $msg], 422) : back()->with('error', $msg);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'completed',
                'admin_notes' => 'Dispute resolved by Admin (Released to Seller): ' . ($request->admin_notes ?: 'No extra notes.')
            ]);

            // Release Funds to Seller Wallet
            $seller = $order->seller;
            if ($seller) {
                $sellerWallet = Wallet::firstOrCreate(
                    ['user_id' => $seller->id],
                    ['balance' => 0]
                );

                $before = (float) $sellerWallet->balance;
                $amount = (float) $order->seller_amount;
                $after = $before + $amount;

                $sellerWallet->update(['balance' => $after]);

                $sellerWallet->transactions()->create([
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'status' => 'completed',
                    'description' => 'Sale Revenue (Dispute Resolved by Admin): ' . $order->invoice_number,
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'processed_at' => now(),
                ]);

                // Notify Seller
                Notification::create([
                    'user_id' => $seller->id,
                    'type' => 'marketplace_dispute_resolved',
                    'title' => 'Pencairan Dana Diselesaikan Admin',
                    'message' => 'Dana pesanan #' . $order->invoice_number . ' sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah dicairkan ke wallet Anda oleh Admin.',
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                ]);
            }

            // Record Platform Fee Income
            $fee = (float) $order->commission_amount;
            if ($fee > 0) {
                $platformWalletService = app(PlatformWalletService::class);
                $platformWallet = $platformWalletService->getPlatformWallet();
                $platformBefore = (float) $platformWallet->balance;
                $platformWallet->increment('balance', $fee);

                $platformWallet->transactions()->create([
                    'type' => 'platform_fee_income',
                    'amount' => $fee,
                    'balance_before' => $platformBefore,
                    'balance_after' => (float) $platformWallet->balance,
                    'status' => 'completed',
                    'description' => 'Marketplace fee (Admin Resolved): ' . $order->invoice_number,
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'processed_at' => now(),
                ]);
            }

            // Notify Buyer
            if ($order->buyer_id) {
                Notification::create([
                    'user_id' => $order->buyer_id,
                    'type' => 'marketplace_dispute_resolved',
                    'title' => 'Kendala Pesanan Selesai',
                    'message' => 'Admin telah meninjau komplain pesanan #' . $order->invoice_number . ' dan memutuskan pelepasan dana ke penjual.',
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                ]);
            }
        });

        $msg = 'Kendala berhasil diselesaikan: Dana telah dicairkan ke Seller.';
        return $request->ajax() 
            ? response()->json(['status' => 'success', 'message' => $msg]) 
            : back()->with('success', $msg);
    }

    /**
     * Resolve Dispute: Force Cancel & Refund Full Amount to Buyer
     */
    public function resolveRefundToBuyer(Request $request, MarketplaceOrder $order)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        if (in_array($order->status, ['completed', 'cancelled'])) {
            $msg = 'Pesanan ini sudah selesai atau dibatalkan sebelumnya.';
            return $request->ajax() ? response()->json(['status' => 'error', 'message' => $msg], 422) : back()->with('error', $msg);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'cancelled',
                'admin_notes' => 'Dispute resolved by Admin (Full Refund to Buyer): ' . ($request->admin_notes ?: 'No extra notes.')
            ]);

            // Restore product stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                    $item->product->update(['is_sold' => false]);
                }
            }

            // Refund Buyer Wallet Balance
            $buyer = $order->buyer;
            if ($buyer) {
                $buyerWallet = Wallet::firstOrCreate(
                    ['user_id' => $buyer->id],
                    ['balance' => 0]
                );

                $before = (float) $buyerWallet->balance;
                $amount = (float) $order->total_amount;
                $after = $before + $amount;

                $buyerWallet->update(['balance' => $after]);

                $buyerWallet->transactions()->create([
                    'type' => 'refund',
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'status' => 'completed',
                    'description' => 'Refund Marketplace (Admin Resolved): ' . $order->invoice_number,
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'processed_at' => now(),
                ]);

                // Notify Buyer
                Notification::create([
                    'user_id' => $buyer->id,
                    'type' => 'marketplace_refund',
                    'title' => 'Refund Dana Berhasil',
                    'message' => 'Dana pesanan #' . $order->invoice_number . ' sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah dikembalikan ke saldo Wallet Anda.',
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                ]);
            }

            // Notify Seller
            if ($order->seller_id) {
                Notification::create([
                    'user_id' => $order->seller_id,
                    'type' => 'marketplace_order_cancelled',
                    'title' => 'Pesanan Dibatalkan Admin',
                    'message' => 'Pesanan #' . $order->invoice_number . ' telah dibatalkan dan dana dikembalikan ke pembeli oleh Admin.',
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                ]);
            }
        });

        $msg = 'Kendala berhasil diselesaikan: Pesanan dibatalkan & Dana di-refund ke Wallet Pembeli.';
        return $request->ajax() 
            ? response()->json(['status' => 'success', 'message' => $msg]) 
            : back()->with('success', $msg);
    }
}
