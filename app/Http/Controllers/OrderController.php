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
        return redirect()->route('marketplace.orders.index');
    }

    /**
     * Show order detail / invoice
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        if ((int) $order->user_id !== (int) $user->id && ! $user->isAdmin()) {
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
        $user = Auth::user();
        if ((int) $order->user_id !== (int) $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $order->load(['items.program.coach', 'user']);

        return view('orders.invoice', [
            'order' => $order,
        ]);
    }
}
