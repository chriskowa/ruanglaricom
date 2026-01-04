<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $purchases = MarketplaceOrder::where('buyer_id', Auth::id())->with('items.product.primaryImage')->latest()->get();
        $sales = MarketplaceOrder::where('seller_id', Auth::id())->with('items.product.primaryImage')->latest()->get();

        return view('marketplace.orders.index', compact('purchases', 'sales'));
    }

    public function show(MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            abort(403);
        }

        return view('marketplace.orders.show', compact('order'));
    }

    public function markShipped(Request $request, MarketplaceOrder $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['tracking_number' => 'nullable|string']);

        $order->update([
            'status' => 'shipped',
            'shipping_tracking_number' => $request->tracking_number,
        ]);

        return back()->with('success', 'Order marked as shipped.');
    }

    public function markCompleted(MarketplaceOrder $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        if ($order->status !== 'shipped') {
            abort(400, 'Order must be shipped first.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'completed']);

            // Release funds to Seller
            $sellerWallet = $order->seller->wallet ?? $order->seller->wallet()->create(['balance' => 0]);

            $before = $sellerWallet->balance;
            $amount = $order->seller_amount;
            $after = $before + $amount;

            $sellerWallet->update(['balance' => $after]);

            $sellerWallet->transactions()->create([
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'completed',
                'description' => 'Sale Revenue: '.$order->invoice_number,
                'reference_type' => 'App\Models\Marketplace\MarketplaceOrder',
                'reference_id' => $order->id,
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', 'Order completed. Funds released to seller.');
    }
}
