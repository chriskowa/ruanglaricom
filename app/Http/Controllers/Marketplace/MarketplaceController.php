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
        $requireApproval = \App\Models\AppSettings::get('marketplace_require_approval', false);

        $query = MarketplaceProduct::with(['category', 'primaryImage', 'seller.city', 'brand'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('is_archived')->orWhere('is_archived', false);
            })
            ->when($requireApproval, function ($q) {
                $q->where('is_approved', true);
            })
            ->where(function ($q) {
                $q->where('sale_type', 'fixed')
                    ->orWhere(function ($q2) {
                        $q2->where('sale_type', 'auction')
                            ->where('auction_status', 'running');
                    });
            });

        // Fulfillment Mode Filter (Titip Jual vs Kirim Langsung) - strictly whitelisted
        $fulfillmentMode = $request->query('fulfillment_mode', $request->query('fulfillment'));
        if (is_string($fulfillmentMode) && in_array($fulfillmentMode, ['consignment', 'self_ship'], true)) {
            $query->where('fulfillment_mode', $fulfillmentMode);
        }

        // Filter by Category (and sub-category if needed) - strict slug validation
        $categorySlug = $request->query('category');
        if (is_string($categorySlug) && preg_match('/^[a-zA-Z0-9\-_]{1,60}$/', $categorySlug)) {
            $cat = MarketplaceCategory::where('slug', $categorySlug)->first();
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

        // Search - strict string check, length limit, tag strip, escaped LIKE wildcards
        $rawSearch = $request->query('search');
        if (is_string($rawSearch) && trim($rawSearch) !== '') {
            $cleanSearch = mb_substr(trim(strip_tags($rawSearch)), 0, 100);
            $escapedSearch = addcslashes($cleanSearch, '%_\\');
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'like', '%' . $escapedSearch . '%')
                    ->orWhere('description', 'like', '%' . $escapedSearch . '%');
            });
        }

        // City Filter (Seller's Location) - strictly validated as positive integer
        if ($request->filled('city')) {
            $cityId = filter_var($request->query('city'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($cityId !== false) {
                $query->whereHas('seller', function ($q) use ($cityId) {
                    $q->where('city_id', $cityId);
                });
            }
        }

        // Condition Filter - strictly whitelisted
        $condition = $request->query('condition');
        if (is_string($condition) && in_array($condition, ['new', 'used'], true)) {
            $query->where('condition', $condition);
        }

        // Brand Filter - strictly validated as positive integer
        if ($request->filled('brand')) {
            $brandId = filter_var($request->query('brand'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($brandId !== false) {
                $query->where('brand_id', $brandId);
            }
        }

        // Size Filter - strict string check, length limit, escaped LIKE wildcards
        $rawSize = $request->query('size');
        if (is_string($rawSize) && trim($rawSize) !== '') {
            $cleanSize = mb_substr(trim(strip_tags($rawSize)), 0, 30);
            $escapedSize = addcslashes($cleanSize, '%_\\');
            $query->where(function ($q) use ($cleanSize, $escapedSize) {
                $q->where('size', $cleanSize)
                  ->orWhere('size', 'like', '%' . $escapedSize . '%')
                  ->orWhere('meta_data->shoe_sizes->us', $cleanSize)
                  ->orWhere('meta_data->shoe_sizes->uk', $cleanSize)
                  ->orWhere('meta_data->shoe_sizes->eu', $cleanSize)
                  ->orWhere('meta_data->shoe_sizes->cm', $cleanSize);
            });
        }

        // Price Range - strictly validated as non-negative floats
        if ($request->filled('price_min')) {
            $priceMin = filter_var($request->query('price_min'), FILTER_VALIDATE_FLOAT);
            if ($priceMin !== false && $priceMin >= 0) {
                $query->where('price', '>=', $priceMin);
            }
        }
        if ($request->filled('price_max')) {
            $priceMax = filter_var($request->query('price_max'), FILTER_VALIDATE_FLOAT);
            if ($priceMax !== false && $priceMax >= 0) {
                $query->where('price', '<=', $priceMax);
            }
        }

        // Sorting (with Boosted priority) - strictly whitelisted
        $sort = is_string($request->query('sort')) ? $request->query('sort') : null;
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderByRaw('CASE WHEN boosted_at IS NOT NULL THEN 0 ELSE 1 END')
                  ->orderBy('boosted_at', 'desc')
                  ->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('marketplace.partials.product-grid', compact('products'))->render();
        }

        $categories = MarketplaceCategory::whereNull('parent_id')->with('children')->withCount('products')->get();
        $brands = MarketplaceBrand::with('categories:id')->orderBy('name')->get();
        
        $cities = City::whereHas('users.marketplaceProducts', function ($q) use ($requireApproval) {
            $q->where('is_active', true)
              ->when($requireApproval, fn($sub) => $sub->where('is_approved', true));
        })->orderBy('name')->get();

        // Featured Products (active featured products within valid expiry)
        $featuredProducts = MarketplaceProduct::with(['category', 'primaryImage', 'seller.city', 'brand'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')->orWhere('featured_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('is_archived')->orWhere('is_archived', false);
            })
            ->when($requireApproval, function ($q) {
                $q->where('is_approved', true);
            })
            ->inRandomOrder()
            ->take(6)
            ->get();

        // Min/Max price for slider
        $minPrice = MarketplaceProduct::min('price') ?? 0;
        $maxPrice = MarketplaceProduct::max('price') ?? 1000000;

        return view('marketplace.index', compact('products', 'categories', 'brands', 'cities', 'minPrice', 'maxPrice', 'featuredProducts'));
    }

    public function show(Request $request, $slug)
    {
        if (!is_string($slug) || !preg_match('/^[a-zA-Z0-9\-_]{1,120}$/', $slug)) {
            abort(404);
        }

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
            ->take(6)
            ->get();

        if ($relatedProducts->count() < 6) {
            $existingIds = $relatedProducts->pluck('id')->push($product->id);
            $moreProducts = MarketplaceProduct::where('is_active', true)
                ->whereNotIn('id', $existingIds)
                ->with(['category', 'primaryImage', 'seller.city', 'brand'])
                ->latest()
                ->take(6 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($moreProducts);
        }

        return view('marketplace.show', compact('product', 'relatedProducts', 'recentBids', 'withSidebar', 'isAuction', 'currentBid', 'auctionRunning', 'auctionEnded', 'now'));
    }

    public function sellerStore(Request $request, $username)
    {
        if (!is_string($username) && !is_numeric($username)) {
            abort(404);
        }
        $cleanUsername = trim(strip_tags((string)$username));
        if (!preg_match('/^[a-zA-Z0-9_\-\.]{1,80}$/', $cleanUsername)) {
            abort(404);
        }

        $seller = \App\Models\User::where(function ($q) use ($cleanUsername) {
            $q->where('username', $cleanUsername);
            if (is_numeric($cleanUsername)) {
                $q->orWhere('id', (int)$cleanUsername);
            }
        })->with('city')->firstOrFail();

        $query = MarketplaceProduct::where('user_id', $seller->id)
            ->where('is_active', true)
            ->with(['category', 'primaryImage', 'brand', 'seller']);

        // Search - strict string check, length limit, tag strip, escaped LIKE wildcards
        $rawSearch = $request->query('search');
        if (is_string($rawSearch) && trim($rawSearch) !== '') {
            $cleanSearch = mb_substr(trim(strip_tags($rawSearch)), 0, 100);
            $escapedSearch = addcslashes($cleanSearch, '%_\\');
            $query->where('title', 'like', '%' . $escapedSearch . '%');
        }

        // Category - strict slug validation
        $categorySlug = $request->query('category');
        if (is_string($categorySlug) && preg_match('/^[a-zA-Z0-9\-_]{1,60}$/', $categorySlug)) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Condition - strictly whitelisted
        $condition = $request->query('condition');
        if (is_string($condition) && in_array($condition, ['new', 'used'], true)) {
            $query->where('condition', $condition);
        }

        // Sort - strictly whitelisted
        $sort = is_string($request->query('sort')) ? $request->query('sort') : null;
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
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
