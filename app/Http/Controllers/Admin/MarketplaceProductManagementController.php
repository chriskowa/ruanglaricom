<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Http\Request;

class MarketplaceProductManagementController extends Controller
{
    /**
     * Admin Product Oversight List with AJAX filtering
     */
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $q = trim($request->string('q')->toString());
        $isFeatured = $request->string('is_featured')->toString();

        $query = MarketplaceProduct::query()
            ->with([
                'seller:id,name,email,username,avatar',
                'category:id,name',
                'brand:id,name',
                'primaryImage'
            ]);

        // Status Filter
        if ($status === 'active') {
            $query->where('is_active', true)->where('is_sold', false)->where('is_archived', false);
        } elseif ($status === 'sold') {
            $query->where('is_sold', true);
        } elseif ($status === 'archived') {
            $query->where('is_archived', true);
        } elseif ($status === 'hidden') {
            $query->where('is_active', false);
        }

        // Featured Filter
        if ($isFeatured === '1') {
            $query->where('is_featured', true);
        } elseif ($isFeatured === '0') {
            $query->where(fn($q) => $q->whereNull('is_featured')->orWhere('is_featured', false));
        }

        // Search Filter
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhereHas('seller', function ($sellerQ) use ($q) {
                        $sellerQ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    });
            });
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'html' => view('admin.marketplace.products.partials.product-rows', compact('products'))->render(),
                'pagination' => view('admin.marketplace.products.partials.pagination', compact('products'))->render(),
                'total' => $products->total()
            ]);
        }

        return view('admin.marketplace.products.index', compact('products', 'status', 'q', 'isFeatured'));
    }

    /**
     * AJAX Toggle Featured Status
     */
    public function toggleFeatured(MarketplaceProduct $product)
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return response()->json([
            'status' => 'success',
            'is_featured' => (bool) $product->is_featured,
            'message' => $product->is_featured ? 'Produk berhasil ditandai sebagai Featured.' : 'Status Featured produk telah dilepas.'
        ]);
    }

    /**
     * AJAX Toggle Active / Hide Status
     */
    public function toggleActive(MarketplaceProduct $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json([
            'status' => 'success',
            'is_active' => (bool) $product->is_active,
            'message' => $product->is_active ? 'Produk diaktifkan.' : 'Produk telah disembunyikan (Hidden).'
        ]);
    }

    /**
     * Delete product (Admin Moderation)
     */
    public function destroy(MarketplaceProduct $product)
    {
        $product->images()->delete();
        $product->delete();

        return request()->ajax()
            ? response()->json(['status' => 'success', 'message' => 'Produk berhasil dihapus oleh Admin.'])
            : back()->with('success', 'Produk berhasil dihapus oleh Admin.');
    }
}
