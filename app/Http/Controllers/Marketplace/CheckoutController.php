<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Notification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    /**
     * Autocomplete Indonesian cities / regencies (used identically like GPX submit)
     */
    public function searchCities(Request $request, RajaOngkirService $rajaOngkirService)
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $results = $rajaOngkirService->searchCities($q);

        return response()->json($results);
    }

    /**
     * Calculate dynamic shipping costs from seller origin to buyer destination
     */
    public function calculateShipping(Request $request, RajaOngkirService $rajaOngkirService)
    {
        $request->validate([
            'order_id' => 'required|exists:marketplace_orders,id',
            'destination_city_id' => 'required|integer',
        ]);

        $order = MarketplaceOrder::with(['seller.city', 'items.product'])->findOrFail($request->order_id);

        if ((int) $order->buyer_id !== (int) Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Determine origin city: from seller's city relation, city_name, or default (152 = Jakarta Selatan)
        $sellerCity = $order->seller?->city?->name
            ?: ($order->seller?->city_name
            ?: 'Jakarta Selatan');
        $originCityId = $rajaOngkirService->findCityIdByName($sellerCity);
        $destinationCityId = (int) $request->destination_city_id;

        // Calculate package weight (1000g default per item)
        $itemCount = $order->items->sum('quantity') ?: 1;
        $weightInGrams = $itemCount * 1000;

        $options = $rajaOngkirService->calculateShippingCost($originCityId, $destinationCityId, $weightInGrams);

        return response()->json([
            'success' => true,
            'origin_city_id' => $originCityId,
            'destination_city_id' => $destinationCityId,
            'weight_grams' => $weightInGrams,
            'options' => $options,
        ]);
    }

    public function init(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:marketplace_products,id']);

        $product = MarketplaceProduct::findOrFail($request->product_id);

        if ($product->sale_type === 'auction') {
            return back()->with('error', 'Produk lelang tidak bisa dibeli langsung.');
        }

        if ($product->is_sold || $product->stock < 1) {
            return back()->with('error', 'Produk ini sudah terjual atau stok habis.');
        }

        if ($product->isReservedByOther(Auth::id())) {
            $remaining = $product->getReservationRemainingMinutes();
            return back()->with('error', "Produk ini sedang dalam proses checkout oleh pembeli lain. Jika pembayaran tidak diselesaikan dalam {$remaining} menit, produk akan tersedia kembali.");
        }

        if ($product->user_id == Auth::id()) {
            return back()->with('error', 'Anda tidak bisa membeli produk Anda sendiri.');
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
            // Refresh reservation TTL for this user's active session
            if ((int) $product->stock === 1) {
                $product->update([
                    'reserved_by_user_id' => Auth::id(),
                    'reserved_until' => now()->addMinutes(15),
                ]);
            }

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

            $lockedProduct = MarketplaceProduct::where('id', $product->id)->lockForUpdate()->first();
            if ($lockedProduct->is_sold || $lockedProduct->stock < 1) {
                DB::rollBack();
                return back()->with('error', 'Produk ini sudah terjual atau stok habis.');
            }

            if ($lockedProduct->isReservedByOther(Auth::id())) {
                DB::rollBack();
                $remaining = $lockedProduct->getReservationRemainingMinutes();
                return back()->with('error', "Produk ini sedang dalam proses checkout oleh pembeli lain. Sisa waktu: {$remaining} menit.");
            }

            // Reserve single-stock item for 15 minutes
            if ((int) $lockedProduct->stock === 1) {
                $lockedProduct->update([
                    'reserved_by_user_id' => Auth::id(),
                    'reserved_until' => now()->addMinutes(15),
                ]);
            }

            $order = MarketplaceOrder::create([
                'invoice_number' => 'INV-RL-' . strtoupper(Str::random(10)),
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

            return back()->with('error', 'Gagal membuat order: ' . $e->getMessage());
        }

        return redirect()->route('marketplace.checkout.show', $order->id);
    }

    public function show(MarketplaceOrder $order, RajaOngkirService $rajaOngkirService)
    {
        if ((int) $order->buyer_id !== (int) Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
            return redirect()
                ->route('marketplace.orders.index')
                ->with('error', 'Order ini bukan milik Anda. Silakan checkout dari halaman produk untuk membuat order Anda sendiri.');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.orders.show', $order->id)->with('info', 'Order already processed.');
        }

        $order->load(['items.product', 'seller.city']);

        $originCity = $order->seller?->city?->name
            ?: ($order->seller?->city_name
            ?: 'Jakarta Selatan');
        $originCityId = $rajaOngkirService->findCityIdByName($originCity);

        $user = Auth::user();
        $wallet = $user->wallet instanceof Wallet ? $user->wallet : null;

        $productSubtotal = $order->items->sum(function ($item) {
            return (float) $item->price_snapshot * (int) $item->quantity;
        });

        // Calculate package weight (1000g default per item)
        $itemCount = $order->items->sum('quantity') ?: 1;
        $weightInGrams = $itemCount * 1000;

        // If order already has destination_city_id, calculate for it. Otherwise calculate for origin (intra-city preview)
        $destCityId = (int) ($order->destination_city_id ?: $originCityId);
        $shippingOptions = $rajaOngkirService->calculateShippingCost($originCityId, $destCityId, $weightInGrams);

        return view('marketplace.checkout.page', compact('order', 'wallet', 'productSubtotal', 'shippingOptions', 'originCity'));
    }

    public function process(Request $request, MarketplaceOrder $order)
    {
        if ((int) $order->buyer_id !== (int) Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
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
            'destination_city_id' => ['nullable', 'integer'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_courier' => ['required', 'string', 'max:50'],
            'shipping_service_name' => ['nullable', 'string', 'max:100'],
            'shipping_etd' => ['nullable', 'string', 'max:50'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'shipping_note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:wallet,midtrans'],
        ]);

        $shippingCost = (float) $validated['shipping_cost'];

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
                    'error' => 'Saldo wallet tidak cukup. Saldo Anda: Rp ' . number_format($wallet->balance, 0, ',', '.') . ', Diperlukan: Rp ' . number_format($total, 0, ',', '.'),
                ]);
            }

            DB::transaction(function () use ($order, $validated, $shippingCost, $total, $wallet) {
                $order->update([
                    'shipping_name' => $validated['shipping_name'],
                    'shipping_phone' => $validated['shipping_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_city' => $validated['shipping_city'],
                    'destination_city_id' => $validated['destination_city_id'] ?? null,
                    'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                    'shipping_courier' => $validated['shipping_courier'],
                    'shipping_service_name' => $validated['shipping_service_name'] ?? null,
                    'shipping_etd' => $validated['shipping_etd'] ?? null,
                    'shipping_cost' => $shippingCost,
                    'shipping_note' => $validated['shipping_note'] ?? null,
                    'total_amount' => $total,
                    'payment_method' => 'wallet',
                    'status' => 'paid',
                ]);

                $wallet->refresh();
                $before = (float) $wallet->balance;
                $wallet->decrement('balance', $total);
                $wallet->refresh();
                $after = (float) $wallet->balance;

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'withdraw',
                    'amount' => $total,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'status' => 'completed',
                    'description' => 'Pembayaran pesanan marketplace: ' . $order->invoice_number,
                    'reference_type' => MarketplaceOrder::class,
                    'processed_at' => now(),
                ]);

                // Decrement stock, mark sold if stock reaches 0, clear reservation, and cleanup other carts
                foreach ($order->items as $item) {
                    $prod = MarketplaceProduct::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($prod) {
                        $newStock = max(0, (int) $prod->stock - (int) $item->quantity);
                        $updateData = [
                            'stock' => $newStock,
                            'reserved_by_user_id' => null,
                            'reserved_until' => null,
                        ];
                        if ($newStock <= 0) {
                            $updateData['is_sold'] = true;
                            $updateData['sold_at'] = now();
                            $updateData['sold_to_user_id'] = $order->buyer_id;
                            $updateData['sold_channel'] = 'ruanglari';
                        }
                        $prod->update($updateData);

                        // Auto-remove product from other buyers' shopping carts
                        \App\Models\Cart::where('product_id', $prod->id)
                            ->where('user_id', '!=', $order->buyer_id)
                            ->delete();
                    }
                }
            });

            // Dispatch Notifications to Seller & Admin
            try {
                $order->load(['seller', 'buyer', 'items.product']);
                if ($order->seller && $order->seller->email) {
                    \Illuminate\Support\Facades\Mail::to($order->seller->email)->send(new \App\Mail\MarketplaceOrderPaidSellerMail($order));
                }

                $adminEmail = config('mail.admin_address', 'admin@ruanglari.com');
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\MarketplaceOrderPaidAdminMail($order));

                if ($order->seller && $order->seller->phone && $order->seller->is_receive_wa) {
                    $waMsg = "Halo {$order->seller->name}! Ada pesanan baru masuk di RuangLari Marketplace.\n\n"
                        . "Invoice: {$order->invoice_number}\n"
                        . "Pembeli: " . ($order->buyer->name ?? 'Buyer') . "\n"
                        . "Alamat: {$order->shipping_address}, {$order->shipping_city}\n"
                        . "Nominal Bersih: Rp " . number_format($order->seller_amount, 0, ',', '.') . "\n\n"
                        . "Silakan cek dashboard seller Anda untuk memproses pengiriman.";
                    \App\Helpers\WhatsApp::send($order->seller->phone, $waMsg, 'seller_order');
                }

                // Create In-App Notification for Seller
                if ($order->seller_id) {
                    Notification::create([
                        'user_id' => $order->seller_id,
                        'type' => 'marketplace_sale',
                        'title' => 'Pesanan Baru Masuk',
                        'message' => 'Pesanan #' . $order->invoice_number . ' (' . ($order->buyer->name ?? 'Pembeli') . ') telah dibayar. Silakan proses pesanan.',
                        'reference_type' => MarketplaceOrder::class,
                        'reference_id' => $order->id,
                        'is_read' => false,
                    ]);
                }

                // Create In-App Notification for Admin users
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $adminUser) {
                    Notification::create([
                        'user_id' => $adminUser->id,
                        'type' => 'marketplace_order_paid',
                        'title' => 'Penjualan Marketplace Baru',
                        'message' => 'Pesanan #' . $order->invoice_number . ' senilai Rp ' . number_format($order->total_amount, 0, ',', '.') . ' telah dibayar oleh ' . ($order->buyer->name ?? 'Pembeli'),
                        'reference_type' => MarketplaceOrder::class,
                        'reference_id' => $order->id,
                        'is_read' => false,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send marketplace order notifications: ' . $e->getMessage());
            }

            return redirect()
                ->route('marketplace.orders.show', $order->id)
                ->with('success', 'Pembayaran via wallet berhasil! Pesanan menunggu diproses oleh penjual.');
        }

        // Midtrans Flow
        $order->update([
            'shipping_name' => $validated['shipping_name'],
            'shipping_phone' => $validated['shipping_phone'],
            'shipping_address' => $validated['shipping_address'],
            'shipping_city' => $validated['shipping_city'],
            'destination_city_id' => $validated['destination_city_id'] ?? null,
            'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
            'shipping_courier' => $validated['shipping_courier'],
            'shipping_service_name' => $validated['shipping_service_name'] ?? null,
            'shipping_etd' => $validated['shipping_etd'] ?? null,
            'shipping_cost' => $shippingCost,
            'shipping_note' => $validated['shipping_note'] ?? null,
            'total_amount' => $total,
            'payment_method' => 'midtrans',
        ]);

        return redirect()->route('marketplace.orders.show', $order->id);
    }

    public function getSnapToken(MarketplaceOrder $order)
    {
        if ((int) $order->buyer_id !== (int) Auth::id() && (! Auth::user() || ! Auth::user()->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order already processed.'], 400);
        }

        if ($order->snap_token) {
            return response()->json([
                'success' => true,
                'snap_token' => $order->snap_token,
            ]);
        }

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) $item->product_id,
                'price' => (int) round($item->price_snapshot),
                'quantity' => (int) $item->quantity,
                'name' => (string) Str::limit($item->product_title_snapshot, 45),
            ];
        }

        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => (int) round($order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos Kirim (' . strtoupper($order->shipping_courier ?: 'Ekspedisi') . ')',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->invoice_number,
                'gross_amount' => (int) round($order->total_amount),
            ],
            'customer_details' => [
                'first_name' => $order->shipping_name ?: (Auth::user()->name ?? 'Customer'),
                'email' => Auth::user()->email ?? 'customer@ruanglari.com',
                'phone' => $order->shipping_phone ?: (Auth::user()->phone ?? '08123456789'),
            ],
            'item_details' => $items,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sesi pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}
