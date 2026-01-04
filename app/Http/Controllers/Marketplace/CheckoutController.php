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

        if ($product->stock < 1) {
            return back()->with('error', 'Product is out of stock.');
        }

        if ($product->user_id == Auth::id()) {
            return back()->with('error', 'You cannot buy your own product.');
        }

        // Calculate fees
        $commissionRate = AppSettings::get('marketplace_commission_percentage', 1);
        $commissionAmount = $product->price * ($commissionRate / 100);
        $sellerAmount = $product->price - $commissionAmount;

        // Create Order
        $order = MarketplaceOrder::create([
            'invoice_number' => 'INV-RL-'.strtoupper(Str::random(10)),
            'buyer_id' => Auth::id(),
            'seller_id' => $product->user_id,
            'total_amount' => $product->price,
            'commission_amount' => $commissionAmount,
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
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.index')->with('info', 'Order already processed.');
        }

        return view('marketplace.checkout.pay', compact('order'));
    }
}
