<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Marketplace\MarketplaceProduct;
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
            ->with(['program.coach', 'product.primaryImage', 'product.seller.city', 'product.brand'])
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
     * Add marketplace physical product to cart
     */
    public function addProduct(Request $request, $productId)
    {
        $user = Auth::user();
        $product = MarketplaceProduct::findOrFail($productId);

        if ($product->stock < 1) {
            return back()->withErrors(['error' => 'Stok produk ini sedang habis.']);
        }

        if ((int) $product->user_id === (int) $user->id) {
            return back()->withErrors(['error' => 'Anda tidak bisa membeli produk Anda sendiri.']);
        }

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        $qty = (int) $request->input('quantity', 1);

        if ($cartItem) {
            $newQty = min($product->stock, $cartItem->quantity + $qty);
            $cartItem->update(['quantity' => $newQty]);
        } else {
            Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => min($product->stock, $qty),
                'price' => $product->price,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke Keranjang Belanja!',
                'count' => Cart::where('user_id', $user->id)->count(),
            ]);
        }

        return redirect()->route('marketplace.cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Add program to cart
     */
    public function add(Request $request, Program $program)
    {
        $user = Auth::user();

        if (! $program->is_published || ! $program->is_active) {
            return back()->withErrors(['error' => 'Program tidak tersedia.']);
        }

        $isEnrolled = $program->enrollments()
            ->where('runner_id', $user->id)
            ->whereIn('status', ['purchased', 'active'])
            ->exists();

        if ($isEnrolled) {
            return back()->withErrors(['error' => 'Anda sedang menjalankan program aktif ini. Harap selesaikan terlebih dahulu sebelum mendaftar ulang.']);
        }

        // Check if already in cart
        $cartItem = Cart::where('user_id', $user->id)
            ->where('program_id', $program->id)
            ->first();

        if ($cartItem) {
            return redirect()->route('marketplace.cart.index')
                ->with('info', 'Program sudah ada di keranjang.');
        }

        Cart::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'quantity' => 1,
            'price' => $program->price,
        ]);

        return redirect()->route('marketplace.cart.index')
            ->with('success', 'Program berhasil ditambahkan ke keranjang.');
    }

    /**
     * Remove item from cart
     */
    public function remove(Cart $cart)
    {
        if ((int) $cart->user_id !== (int) Auth::id()) {
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
        if ((int) $cart->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $maxStock = $cart->product ? $cart->product->stock : 10;
        $qty = min($maxStock, (int) $validated['quantity']);

        $cart->update(['quantity' => $qty]);

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
     * Get cart count (for header badge)
     */
    public function count()
    {
        $count = Cart::where('user_id', Auth::id())->count();

        return response()->json(['count' => $count]);
    }
}
