<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Marketplace\MarketplaceWishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display runner's wishlisted products
     */
    public function index()
    {
        $user = Auth::user();
        $wishlists = MarketplaceWishlist::where('user_id', $user->id)
            ->with(['product.primaryImage', 'product.seller.city', 'product.brand'])
            ->latest()
            ->paginate(12);

        return view('marketplace.wishlist.index', compact('wishlists'));
    }

    /**
     * Toggle item in wishlist (AJAX)
     */
    public function toggle(Request $request, $productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu untuk menambah ke Wishlist.'
            ], 401);
        }

        $userId = Auth::id();
        $product = MarketplaceProduct::findOrFail($productId);

        $existing = MarketplaceWishlist::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
            $message = 'Produk telah dihapus dari Wishlist.';
        } else {
            MarketplaceWishlist::create([
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);
            $inWishlist = true;
            $message = 'Produk berhasil ditambahkan ke Wishlist!';
        }

        $count = MarketplaceWishlist::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'count' => $count,
            'message' => $message,
        ]);
    }
}
