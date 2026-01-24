<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Create Order
        $order = MarketplaceOrder::create([
            'invoice_number' => 'INV-RL-'.strtoupper(Str::random(10)),
            'buyer_id' => Auth::id(),
            'seller_id' => $product->user_id,
            'total_amount' => $product->price,
            'commission_amount' => $totalFee,
            'seller_amount' => $sellerAmount,
            'status' => 'pending',
        ]);

        // Create Order Item
        $order->items()->create([
            'product_id' => $product->id,
            'product_title_snapshot' => $product->title,
            'price_snapshot' => $product->price,
            'quantity' => 1,
        ]);

        // Midtrans Payload
        $params = [
            'transaction_details' => [
                'order_id' => $order->invoice_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
            'item_details' => [
                [
                    'id' => $product->id,
                    'price' => (int) $product->price,
                    'quantity' => 1,
                    'name' => substr($product->title, 0, 50),
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);

            return redirect()->route('marketplace.checkout.pay', $order->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Payment gateway error: '.$e->getMessage());
        }
    }

    public function pay(MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id() && (!Auth::user() || !Auth::user()->isAdmin())) {
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
