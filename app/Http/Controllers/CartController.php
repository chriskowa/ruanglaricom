<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Show cart page
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)
            ->with('program.coach')
            ->get();

        $subtotal = $cartItems->sum('subtotal');
        $tax = 0; // No tax for now
        $total = $subtotal + $tax;

        return view('cart.index', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }

    /**
     * Add program to cart
     */
    public function add(Request $request, Program $program)
    {
        if (! $program->is_published || ! $program->is_active) {
            return back()->withErrors(['error' => 'Program tidak tersedia.']);
        }

        $user = Auth::user();
        $isEnrolled = $program->enrollments()
            ->where('runner_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($isEnrolled) {
            return back()->withErrors(['error' => 'Anda sudah terdaftar di program ini.']);
        }

        // Check if already in cart
        $cartItem = Cart::where('user_id', $user->id)
            ->where('program_id', $program->id)
            ->first();

        if ($cartItem) {
            return redirect()->route('marketplace.checkout.index')
                ->with('info', 'Program sudah ada di keranjang. Silakan lanjut ke checkout.');
        }

        Cart::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'quantity' => 1,
            'price' => $program->price,
        ]);

        return redirect()->route('marketplace.checkout.index')
            ->with('success', 'Program berhasil ditambahkan ke keranjang. Silakan selesaikan pembayaran.');
    }

    /**
     * Remove item from cart
     */
    public function remove(Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart->update(['quantity' => $validated['quantity']]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'subtotal' => $cart->subtotal,
                'total' => Cart::where('user_id', Auth::id())->get()->sum('subtotal'),
            ]);
        }

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return back()->with('success', 'Keranjang berhasil dikosongkan.');
    }

    /**
     * Get cart count (for header)
     */
    public function count()
    {
        $count = Cart::where('user_id', Auth::id())->count();

        return response()->json(['count' => $count]);
    }
}
