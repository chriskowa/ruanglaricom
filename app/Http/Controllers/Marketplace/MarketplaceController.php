<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Marketplace\MarketplaceBrand;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceProduct::with(['category', 'primaryImage', 'seller.city', 'brand'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('is_archived')->orWhere('is_archived', false);
            })
            ->where(function ($q) {
                $q->where('sale_type', 'fixed')
                    ->orWhere(function ($q2) {
                        $q2->where('sale_type', 'auction')
                            ->where('auction_status', 'running');
                    });
            });

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
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        // City Filter (Seller's Location)
        if ($request->filled('city')) {
            $query->whereHas('seller', function ($q) use ($request) {
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
        $cities = City::whereHas('users.marketplaceProducts', function ($q) {
            $q->where('is_active', true);
        })->orderBy('name')->get();

        // Min/Max price for slider
        $minPrice = MarketplaceProduct::min('price') ?? 0;
        $maxPrice = MarketplaceProduct::max('price') ?? 1000000;

        return view('marketplace.index', compact('products', 'categories', 'brands', 'cities', 'minPrice', 'maxPrice'));
    }

    public function show(Request $request, $slug)
    {
        $product = MarketplaceProduct::with(['category', 'images', 'seller.city', 'brand'])->where('slug', $slug)->firstOrFail();

        // Unique IP View Tracker (increment stats view only if unique IP within 24h)
        $ip = $request->ip();
        $cacheKey = "mp_product_view_{$product->id}_{$ip}";
        if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
            try {
                $product->increment('views_count');
                $product->refresh();
            } catch (\Throwable $e) {
                // Fallback if views_count column missing in DB
            }
        }

        $recentBids = collect();
        $withSidebar = true;

        $isAuction = $product->sale_type === 'auction';
        $currentBid = $isAuction ? ($product->current_price ?? $product->starting_price ?? $product->price) : null;
        $auctionRunning = $product->auction_status === 'running';
        $now = now();
        $auctionEnded = $isAuction && ($product->auction_status === 'ended' || ($product->auction_end_at && $now->gte($product->auction_end_at)));

        if ($isAuction) {
            $recentBids = $product->bids()->with('bidder')->latest()->take(10)->get();
        }
        $relatedProducts = MarketplaceProduct::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                  ->orWhere('user_id', $product->user_id);
            })
            ->with(['category', 'primaryImage', 'seller.city', 'brand'])
            ->latest()
            ->take(4)
            ->get();

        if ($relatedProducts->count() < 4) {
            $existingIds = $relatedProducts->pluck('id')->push($product->id);
            $moreProducts = MarketplaceProduct::where('is_active', true)
                ->whereNotIn('id', $existingIds)
                ->with(['category', 'primaryImage', 'seller.city', 'brand'])
                ->latest()
                ->take(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($moreProducts);
        }

        return view('marketplace.show', compact('product', 'relatedProducts', 'recentBids', 'withSidebar', 'isAuction', 'currentBid', 'auctionRunning', 'auctionEnded', 'now'));
    }

    public function sellerStore(Request $request, $username)
    {
        $seller = \App\Models\User::where('username', $username)
            ->orWhere('id', $username)
            ->with('city')
            ->firstOrFail();

        $query = MarketplaceProduct::where('user_id', $seller->id)
            ->where('is_active', true)
            ->with(['category', 'primaryImage', 'brand', 'seller']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'latest':
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('marketplace.partials.seller-product-grid', compact('products', 'seller'))->render();
        }

        $categories = MarketplaceCategory::whereNull('parent_id')
            ->whereHas('products', function($q) use ($seller) {
                $q->where('user_id', $seller->id)->where('is_active', true);
            })
            ->get();

        $salesCount = \App\Models\Marketplace\MarketplaceOrder::where('seller_id', $seller->id)
            ->whereIn('status', ['paid', 'shipped', 'completed'])
            ->count();

        return view('marketplace.seller-store', compact('seller', 'products', 'salesCount', 'categories'));
    }
}
