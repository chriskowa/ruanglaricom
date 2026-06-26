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

    public function destroy(Order $order)
    {
        $user = Auth::user();
        if ((int) $order->user_id !== (int) $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending') {
            return back()->with('error', 'Hanya pesanan pending yang dapat dibatalkan.');
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('marketplace.orders.index')->with('success', 'Pesanan program berhasil dibatalkan.');
    }
}
