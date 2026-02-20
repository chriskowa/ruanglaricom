<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function init(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:marketplace_products,id']);

        $product = MarketplaceProduct::findOrFail($request->product_id);

        if ($product->sale_type === 'auction') {
            return back()->with('error', 'Produk lelang tidak bisa dibeli langsung.');
        }

        if ($product->stock < 1) {
            return back()->with('error', 'Product is out of stock.');
        }

        if ($product->user_id == Auth::id()) {
            return back()->with('error', 'You cannot buy your own product.');
        }

        // Check for existing pending order for this product by this user to avoid duplicates
        $existingOrder = MarketplaceOrder::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->latest()
            ->first();

        if ($existingOrder) {
            return redirect()->route('marketplace.checkout.show', $existingOrder->id);
        }

        // Calculate fees
        $commissionRate = AppSettings::get('marketplace_commission_percentage', 1);
        $commissionAmount = $product->price * ($commissionRate / 100);

        $consignmentFeeRate = AppSettings::get('marketplace_consignment_fee_percentage', 0);
        $consignmentFeeAmount = 0;
        if ($product->fulfillment_mode === 'consignment') {
            $consignmentFeeAmount = $product->price * ($consignmentFeeRate / 100);
        }

        $totalFee = $commissionAmount + $consignmentFeeAmount;
        $sellerAmount = $product->price - $totalFee;

        try {
            DB::beginTransaction();

            $order = MarketplaceOrder::create([
                'invoice_number' => 'INV-RL-'.strtoupper(Str::random(10)),
                'buyer_id' => Auth::id(),
                'seller_id' => $product->user_id,
                'total_amount' => $product->price,
                'commission_amount' => $totalFee,
                'seller_amount' => $sellerAmount,
                'status' => 'pending',
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_title_snapshot' => $product->title,
                'price_snapshot' => $product->price,
                'quantity' => 1,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membuat order: '.$e->getMessage());
        }

        return redirect()->route('marketplace.checkout.show', $order->id);
    }

    public function show(MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
            return redirect()
                ->route('marketplace.orders.index')
                ->with('error', 'Order ini bukan milik Anda. Silakan checkout dari halaman produk untuk membuat order Anda sendiri.');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.orders.show', $order->id)->with('info', 'Order already processed.');
        }

        $order->load(['items.product', 'seller']);

        $user = Auth::user();
        $wallet = $user->wallet instanceof Wallet ? $user->wallet : null;

        $productSubtotal = $order->items->sum(function ($item) {
            return (float) $item->price_snapshot * (int) $item->quantity;
        });

        $shippingOptions = [
            'regular' => [
                'label' => 'Regular Courier (2-3 hari)',
                'cost' => 20000,
            ],
            'express' => [
                'label' => 'Express Courier (1 hari)',
                'cost' => 35000,
            ],
            'pickup' => [
                'label' => 'Ambil sendiri / COD di lokasi',
                'cost' => 0,
            ],
        ];

        return view('marketplace.checkout.page', compact('order', 'wallet', 'productSubtotal', 'shippingOptions'));
    }

    public function process(Request $request, MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
            return redirect()
                ->route('marketplace.orders.index')
                ->with('error', 'Order ini bukan milik Anda. Silakan checkout dari halaman produk untuk membuat order Anda sendiri.');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.orders.show', $order->id)->with('info', 'Order already processed.');
        }

        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:120'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_courier' => ['required', 'in:regular,express,pickup'],
            'shipping_note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:wallet,midtrans'],
        ]);

        $shippingOptions = [
            'regular' => 20000,
            'express' => 35000,
            'pickup' => 0,
        ];

        $shippingCost = $shippingOptions[$validated['shipping_courier']] ?? 0;

        $items = $order->items()->get();
        $productSubtotal = $items->sum(function ($item) {
            return (float) $item->price_snapshot * (int) $item->quantity;
        });

        $total = $productSubtotal + $shippingCost;

        $user = Auth::user();
        $wallet = $user->wallet instanceof Wallet ? $user->wallet : null;

        if ($validated['payment_method'] === 'wallet') {
            if (! $wallet) {
                return back()->withErrors(['error' => 'Wallet tidak ditemukan. Silakan lakukan top up terlebih dahulu.']);
            }

            $wallet->refresh();

            if ($wallet->balance < $total) {
                return back()->withErrors([
                    'error' => 'Saldo wallet tidak cukup. Saldo Anda: Rp '.number_format($wallet->balance, 0, ',', '.').', Diperlukan: Rp '.number_format($total, 0, ',', '.'),
                ]);
            }

            DB::transaction(function () use ($order, $validated, $shippingCost, $total, $wallet) {
                $order->update([
                    'shipping_name' => $validated['shipping_name'],
                    'shipping_phone' => $validated['shipping_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_city' => $validated['shipping_city'],
                    'shipping_postal_code' => $validated['shipping_postal_code'],
                    'shipping_courier' => $validated['shipping_courier'],
                    'shipping_cost' => $shippingCost,
                    'shipping_note' => $validated['shipping_note'] ?? null,
                    'total_amount' => $total,
                    'payment_method' => 'wallet',
                    'status' => 'paid',
                ]);

                $wallet->refresh();
                $before = $wallet->balance;
                $wallet->decrement('balance', $total);
                $wallet->refresh();
                $after = $wallet->balance;

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'withdraw',
                    'amount' => $total,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'status' => 'completed',
                    'description' => 'Marketplace order: '.$order->invoice_number,
                    'reference_type' => MarketplaceOrder::class,
                    'reference_id' => $order->id,
                    'processed_at' => now(),
                ]);
            });

            return redirect()
                ->route('marketplace.orders.show', $order->id)
                ->with('success', 'Pembayaran via wallet berhasil. Order menunggu pengiriman dari seller.');
        }

        $order->update([
            'shipping_name' => $validated['shipping_name'],
            'shipping_phone' => $validated['shipping_phone'],
            'shipping_address' => $validated['shipping_address'],
            'shipping_city' => $validated['shipping_city'],
            'shipping_postal_code' => $validated['shipping_postal_code'],
            'shipping_courier' => $validated['shipping_courier'],
            'shipping_cost' => $shippingCost,
            'shipping_note' => $validated['shipping_note'] ?? null,
            'total_amount' => $total,
            'payment_method' => 'midtrans',
        ]);

        $itemDetails = $items->map(function ($it) {
            return [
                'id' => $it->product_id,
                'price' => (int) $it->price_snapshot,
                'quantity' => (int) $it->quantity,
                'name' => substr($it->product_title_snapshot, 0, 50),
            ];
        })->values()->all();

        if ($shippingCost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $shippingCost,
                'quantity' => 1,
                'name' => 'Shipping - '.$validated['shipping_courier'],
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->invoice_number,
                'gross_amount' => (int) $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return back()->with('error', 'Payment gateway error: '.$e->getMessage());
        }

        return redirect()->route('marketplace.checkout.pay', $order->id);
    }

    public function pay(MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
            return redirect()
                ->route('marketplace.orders.index')
                ->with('error', 'Order ini bukan milik Anda. Silakan checkout dari halaman produk untuk membuat order Anda sendiri.');
        }
        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.index')->with('info', 'Order already processed.');
        }

        if (! $order->snap_token) {
            $items = $order->items()->with('product')->get();
            $itemDetails = $items->map(function ($it) {
                return [
                    'id' => $it->product_id,
                    'price' => (int) $it->price_snapshot,
                    'quantity' => (int) $it->quantity,
                    'name' => substr($it->product_title_snapshot, 0, 50),
                ];
            })->values()->all();

            $params = [
                'transaction_details' => [
                    'order_id' => $order->invoice_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
                'item_details' => $itemDetails,
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $order->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                return back()->with('error', 'Payment gateway error: '.$e->getMessage());
            }
        }

        return view('marketplace.checkout.pay', compact('order'));
    }
}
