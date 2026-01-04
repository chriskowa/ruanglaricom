<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Show order list
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items.program')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show order detail / invoice
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.program.coach', 'user']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Download invoice as PDF (optional)
     */
    public function invoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.program.coach', 'user']);

        return view('orders.invoice', [
            'order' => $order,
        ]);
    }
}
