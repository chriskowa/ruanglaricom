<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceApiController extends BaseApiController
{
    /**
     * Browse running gear & shoes catalog
     */
    public function products(Request $request): JsonResponse
    {
        $query = MarketplaceProduct::query()
            ->with(['brand', 'category', 'primaryImage']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return $this->successResponse([
            'products' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ], 'Katalog peralatan & sepatu lari berhasil dimuat');
    }

    /**
     * Get detail of a running product
     */
    public function showProduct(string $slug): JsonResponse
    {
        $product = MarketplaceProduct::where('slug', $slug)
            ->orWhere('id', $slug)
            ->with(['brand', 'category', 'images'])
            ->first();

        if (! $product) {
            return $this->errorResponse('Produk tidak ditemukan.', 404);
        }

        return $this->successResponse($product, 'Detail produk berhasil dimuat');
    }

    /**
     * Get marketplace categories
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = MarketplaceCategory::whereNull('parent_id')
            ->with('children')
            ->get();

        return $this->successResponse($categories, 'Kategori marketplace berhasil dimuat');
    }
}
