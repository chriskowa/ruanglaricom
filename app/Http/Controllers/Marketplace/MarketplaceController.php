<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceBrand;
use App\Models\City;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceProduct::with(['category', 'primaryImage', 'seller.city', 'brand'])
            ->where('is_active', true);

        // Filter by Category (and sub-category if needed)
        if ($request->filled('category')) {
            $cat = MarketplaceCategory::where('slug', $request->category)->first();
            if ($cat) {
                if ($cat->parent_id) {
                    // It is a subcategory
                    $query->where('sub_category_id', $cat->id);
                } else {
                    // It is a parent category
                    $query->where('category_id', $cat->id);
                }
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // City Filter (Seller's Location)
        if ($request->filled('city')) {
            $query->whereHas('seller', function($q) use ($request) {
                $q->where('city_id', $request->city);
            });
        }

        // Condition Filter
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Brand Filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Size Filter
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        // Price Range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12);

        if ($request->ajax()) {
            return view('marketplace.partials.product-grid', compact('products'))->render();
        }

        $categories = MarketplaceCategory::whereNull('parent_id')->with('children')->withCount('products')->get();
        $brands = MarketplaceBrand::with('categories:id')->orderBy('name')->get();
        // Fetch cities that have sellers? Or just all cities. All cities might be too many.
        // Optimization: Only fetch cities that have active listings.
        $cities = City::whereHas('users.marketplaceProducts', function($q) {
            $q->where('is_active', true);
        })->orderBy('name')->get();

        // Min/Max price for slider
        $minPrice = MarketplaceProduct::min('price') ?? 0;
        $maxPrice = MarketplaceProduct::max('price') ?? 1000000;

        return view('marketplace.index', compact('products', 'categories', 'brands', 'cities', 'minPrice', 'maxPrice'));
    }

    public function show($slug)
    {
        $product = MarketplaceProduct::with(['category', 'images', 'seller.city', 'brand'])->where('slug', $slug)->firstOrFail();
        $relatedProducts = MarketplaceProduct::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('primaryImage')
            ->take(4)
            ->get();

        return view('marketplace.show', compact('product', 'relatedProducts'));
    }
}
