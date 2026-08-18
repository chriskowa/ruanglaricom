<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PlatformWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        $purchases = MarketplaceOrder::where('buyer_id', Auth::id())
            ->with(['items.product.primaryImage', 'seller'])
            ->latest()
            ->get();

        $sales = MarketplaceOrder::where('seller_id', Auth::id())
            ->with(['items.product.primaryImage', 'buyer'])
            ->latest()
            ->get();

        $programOrders = \App\Models\Order::where('user_id', Auth::id())
            ->with('items.program.coach')
            ->latest()
            ->get();

        $cartItems = \App\Models\Cart::where('user_id', Auth::id())
            ->with(['program.coach', 'product.primaryImage', 'product.seller'])
            ->get();

        $cartSubtotal = $cartItems->sum('subtotal');
        $cartTotal = $cartSubtotal;

        return view('marketplace.orders.index', compact('purchases', 'sales', 'programOrders', 'cartItems', 'cartSubtotal', 'cartTotal'));
    }

    public function show(MarketplaceOrder $order)
    {
        $user = Auth::user();
        if ((int) $order->buyer_id !== (int) $user->id && (int) $order->seller_id !== (int) $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $order->load(['items.product.primaryImage', 'buyer', 'seller']);

        return view('marketplace.orders.show', compact('order'));
    }

    public function markShipped(Request $request, MarketplaceOrder $order)
    {
        if ((int) $order->seller_id !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate(['tracking_number' => 'required|string|max:100']);

        $order->update([
            'status' => 'shipped',
            'shipping_tracking_number' => trim($request->tracking_number),
            'shipped_at' => now(),
        ]);

        if ($order->buyer_id) {
            Notification::create([
                'user_id' => $order->buyer_id,
                'type' => 'marketplace_order_shipped',
                'title' => 'Pesanan Telah Dikirim',
                'message' => 'Pesanan #' . $order->invoice_number . ' telah dikirim dengan nomor resi ' . $order->shipping_tracking_number,
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Pesanan berhasil ditandai sebagai dikirim.');
    }

    /**
     * Buyer: Confirm package received from courier (delivered -> inspecting)
     */
    public function markDelivered(MarketplaceOrder $order)
    {
        if ((int) $order->buyer_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'shipped') {
            return back()->with('error', 'Hanya pesanan yang sedang dikirim yang dapat dikonfirmasi sampai.');
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Notify Seller
        if ($order->seller_id) {
            Notification::create([
                'user_id' => $order->seller_id,
                'type' => 'marketplace_order_delivered',
                'title' => 'Paket Telah Sampai ke Pembeli',
                'message' => 'Pembeli telah mengonfirmasi paket pesanan #' . $order->invoice_number . ' telah sampai dan sedang dalam tahap pengecekan barang.',
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Paket berhasil dikonfirmasi sampai. Silakan periksa kelayakan barang sebelum menyetujui penyelesaian transaksi.');
    }

    /**
     * Buyer: Approve & Complete transaction (auto release escrow funds to seller)
     */
    public function markCompleted(MarketplaceOrder $order)
    {
        if ((int) $order->buyer_id !== (int) Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        if (!in_array($order->status, ['shipped', 'delivered'])) {
            return back()->with('error', 'Pesanan harus dalam status dikirim atau diterima terlebih dahulu.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Release funds to Seller
            $sellerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->seller_id],
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
                'description' => 'Hasil Penjualan Pesanan: ' . $order->invoice_number,
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'processed_at' => now(),
            ]);

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
                    'description' => 'Marketplace fee: ' . $order->invoice_number,
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'processed_at' => now(),
                ]);
            }

            // Notify Seller of Funds Release
            if ($order->seller_id) {
                Notification::create([
                    'user_id' => $order->seller_id,
                    'type' => 'marketplace_funds_released',
                    'title' => 'Dana Penjualan Cair ke Wallet',
                    'message' => 'Pembeli telah menyetujui pesanan #' . $order->invoice_number . '. Dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah masuk ke saldo wallet Anda.',
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'is_read' => false,
                ]);
            }
        });

        return back()->with('success', 'Transaksi berhasil diselesaikan! Terima kasih, dana telah diteruskan ke dompet penjual.');
    }

    /**
     * Buyer: Submit Dispute / Complaint for damaged or mismatched item
     */
    public function submitDispute(Request $request, MarketplaceOrder $order)
    {
        if ((int) $order->buyer_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['shipped', 'delivered'])) {
            return back()->with('error', 'Komplain hanya dapat diajukan untuk pesanan yang telah dikirim atau sampai.');
        }

        $validated = $request->validate([
            'dispute_reason' => ['required', 'string', 'in:damaged,wrong_item,fake,not_as_described,other'],
            'dispute_notes' => ['required', 'string', 'max:1000'],
            'dispute_proofs' => ['nullable', 'array', 'max:3'],
            'dispute_proofs.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $proofPaths = [];
        if ($request->hasFile('dispute_proofs')) {
            foreach ($request->file('dispute_proofs') as $file) {
                $path = $file->store('marketplace/disputes', 'public');
                $proofPaths[] = $path;
            }
        }

        $order->update([
            'status' => 'disputed',
            'dispute_reason' => $validated['dispute_reason'],
            'dispute_notes' => $validated['dispute_notes'],
            'dispute_proof_images' => $proofPaths,
            'disputed_at' => now(),
        ]);

        // Notify Seller
        if ($order->seller_id) {
            Notification::create([
                'user_id' => $order->seller_id,
                'type' => 'marketplace_order_disputed',
                'title' => 'Komplain Pesanan Masuk',
                'message' => 'Pembeli mengajukan komplain pada pesanan #' . $order->invoice_number . ' ("' . $validated['dispute_notes'] . '"). Dana rekber ditahan sementara.',
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
        }

        // Notify Admins
        $adminUsers = User::where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'marketplace_dispute_admin',
                'title' => 'Sengketa Marketplace Baru',
                'message' => 'Komplain baru pada pesanan #' . $order->invoice_number . ' oleh ' . ($order->buyer->name ?? 'Pembeli') . '. Menunggu mediasi.',
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Komplain berhasil diajukan. Penjual dan Admin telah diberitahu untuk menindaklanjuti proses retur/refund.');
    }

    /**
     * Seller: Accept return request
     */
    public function acceptReturn(MarketplaceOrder $order)
    {
        if ((int) $order->seller_id !== (int) Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($order->status !== 'disputed') {
            return back()->with('error', 'Hanya pesanan yang sedang dalam status sengketa/komplain yang dapat disetujui untuk retur.');
        }

        $order->update([
            'status' => 'return_in_progress',
        ]);

        // Notify Buyer
        if ($order->buyer_id) {
            Notification::create([
                'user_id' => $order->buyer_id,
                'type' => 'marketplace_return_accepted',
                'title' => 'Pengajuan Retur Disetujui',
                'message' => 'Penjual telah menyetujui pengembalian barang untuk pesanan #' . $order->invoice_number . '. Silakan kirim balik barang dan input nomor resi retur.',
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Pengajuan retur telah disetujui. Menunggu pembeli mengirimkan barang kembali.');
    }

    /**
     * Buyer: Submit return tracking number
     */
    public function submitReturnTracking(Request $request, MarketplaceOrder $order)
    {
        if ((int) $order->buyer_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'return_in_progress') {
            return back()->with('error', 'Status pesanan saat ini tidak dalam proses pengembalian barang.');
        }

        $validated = $request->validate([
            'return_tracking_number' => ['required', 'string', 'max:100'],
            'return_courier' => ['required', 'string', 'max:50'],
        ]);

        $order->update([
            'return_tracking_number' => trim($validated['return_tracking_number']),
            'return_courier' => trim($validated['return_courier']),
            'returned_at' => now(),
        ]);

        // Notify Seller
        if ($order->seller_id) {
            Notification::create([
                'user_id' => $order->seller_id,
                'type' => 'marketplace_return_shipped',
                'title' => 'Barang Retur Sedang Dikirim Balik',
                'message' => 'Pembeli telah mengirimkan barang retur untuk pesanan #' . $order->invoice_number . ' dengan resi ' . $validated['return_tracking_number'] . ' (' . strtoupper($validated['return_courier']) . ').',
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Nomor resi retur berhasil disimpan. Penjual akan memeriksa fisik barang saat tiba.');
    }

    /**
     * Seller / Admin: Confirm return received safely -> Execute 100% Refund to Buyer
     */
    public function confirmReturnReceived(MarketplaceOrder $order)
    {
        $user = Auth::user();
        if ((int) $order->seller_id !== (int) $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if (!in_array($order->status, ['return_in_progress', 'disputed'])) {
            return back()->with('error', 'Pesanan tidak dalam proses pengembalian barang.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            // 100% Refund to Buyer's Wallet
            $buyerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->buyer_id],
                ['balance' => 0]
            );

            $before = (float) $buyerWallet->balance;
            $refundAmount = (float) $order->total_amount;
            $after = $before + $refundAmount;

            $buyerWallet->update(['balance' => $after]);

            $buyerWallet->transactions()->create([
                'type' => 'deposit',
                'amount' => $refundAmount,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'completed',
                'description' => 'Pengembalian Dana (Refund) Retur Pesanan: ' . $order->invoice_number,
                'reference_type' => MarketplaceOrder::class,
                'reference_id' => $order->id,
                'processed_at' => now(),
            ]);

            // Restore product stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Notify Buyer
            if ($order->buyer_id) {
                Notification::create([
                    'user_id' => $order->buyer_id,
                    'type' => 'marketplace_order_refunded',
                    'title' => 'Pengembalian Dana Berhasil',
                    'message' => 'Barang retur telah diterima dan diverifikasi. Dana pesanan #' . $order->invoice_number . ' sebesar Rp ' . number_format($refundAmount, 0, ',', '.') . ' telah dikembalikan 100% ke saldo wallet Anda.',
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'is_read' => false,
                ]);
            }
        });

        return back()->with('success', 'Barang retur telah dikonfirmasi diterima. Dana pesanan 100% telah direfund ke dompet pembeli.');
    }

    public function destroy(MarketplaceOrder $order)
    {
        $user = Auth::user();
        if ((int) $order->buyer_id !== (int) $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya pesanan pending yang dapat dibatalkan.');
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('marketplace.orders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
