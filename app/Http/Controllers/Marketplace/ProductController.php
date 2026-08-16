<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceBrand;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceConsignmentIntake;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Marketplace\MarketplaceOrder;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Product counts
        $totalProductsCount = MarketplaceProduct::where('user_id', $userId)->count();
        $activeProductsCount = MarketplaceProduct::where('user_id', $userId)
            ->where(fn($q) => $q->whereNull('is_archived')->orWhere('is_archived', false))
            ->where(fn($q) => $q->whereNull('is_sold')->orWhere('is_sold', false))
            ->count();
        $soldProductsCount = MarketplaceProduct::where('user_id', $userId)->where('is_sold', true)->count();
        $archivedProductsCount = MarketplaceProduct::where('user_id', $userId)->where('is_archived', true)->count();
        $totalViewsCount = MarketplaceProduct::where('user_id', $userId)->sum('views_count');

        $query = MarketplaceProduct::where('user_id', $userId)->with(['primaryImage', 'soldToUser'])->latest();

        $productFilter = $request->get('product_filter', 'all');
        if ($productFilter === 'active') {
            $query->where(fn($q) => $q->whereNull('is_archived')->orWhere('is_archived', false))
                ->where(fn($q) => $q->whereNull('is_sold')->orWhere('is_sold', false));
        } elseif ($productFilter === 'sold') {
            $query->where('is_sold', true);
        } elseif ($productFilter === 'archived') {
            $query->where('is_archived', true);
        }

        $products = $query->paginate(10)->withQueryString();
        $withSidebar = true;

        // Fetch active orders (pending, paid, shipped, disputed)
        $activeOrders = MarketplaceOrder::where('seller_id', $userId)
            ->whereIn('status', ['pending', 'paid', 'shipped', 'disputed'])
            ->with(['items.product.primaryImage', 'buyer'])
            ->latest()
            ->get();

        // Fetch sales history (completed, cancelled)
        $salesHistory = MarketplaceOrder::where('seller_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['items.product.primaryImage', 'buyer'])
            ->latest()
            ->get();

        return view('marketplace.seller.products.index', compact(
            'products',
            'activeOrders',
            'salesHistory',
            'withSidebar',
            'totalProductsCount',
            'activeProductsCount',
            'soldProductsCount',
            'archivedProductsCount',
            'totalViewsCount',
            'productFilter'
        ));
    }

    public function create()
    {
        $categories = MarketplaceCategory::all();
        $brands = MarketplaceBrand::with('categories:id,slug')->orderBy('name')->get();
        $withSidebar = true;

        return view('marketplace.seller.products.create', compact('categories', 'brands', 'withSidebar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:marketplace_categories,id',
            'brand_id' => 'nullable|exists:marketplace_brands,id',
            'size' => 'nullable|string|max:50',
            'condition' => 'required|in:new,used',
            'type' => 'required|in:physical,digital_slot',
            'description' => 'required|string',
            'sale_type' => 'required|in:fixed,auction',
            'fulfillment_mode' => 'required|in:self_ship,consignment',
            'price' => 'nullable|required_if:sale_type,fixed|numeric|min:0',
            'stock' => 'nullable|required_if:sale_type,fixed|integer|min:1',
            'starting_price' => 'nullable|required_if:sale_type,auction|numeric|min:0',
            'min_increment' => 'nullable|required_if:sale_type,auction|numeric|min:0',
            'auction_end_at' => 'nullable|required_if:sale_type,auction|date|after:now',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
            'dropoff_method' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'images' => 'required_without:image|array|max:4',
            'images.*' => 'image|max:3072',
            'image' => 'nullable|image|max:3072',
        ]);

        $saleType = $request->sale_type;
        $fulfillment = $request->fulfillment_mode;

        $basePrice = $saleType === 'auction' ? (float) $request->starting_price : (float) $request->price;
        $stock = $saleType === 'auction' ? 1 : (int) $request->stock;

        $requireApproval = \App\Models\AppSettings::get('marketplace_require_approval', false);
        $isApproved = ! $requireApproval;
        $approvalStatus = $requireApproval ? 'pending' : 'approved';

        $product = MarketplaceProduct::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'size' => $request->size,
            'title' => $request->title,
            'slug' => Str::slug($request->title).'-'.Str::random(6),
            'description' => $request->description,
            'price' => $basePrice,
            'condition' => $request->condition,
            'type' => $request->type,
            'stock' => $stock,
            'sale_type' => $saleType,
            'fulfillment_mode' => $fulfillment,
            'consignment_status' => $fulfillment === 'consignment' ? 'requested' : 'none',
            'auction_start_at' => $saleType === 'auction' ? now() : null,
            'auction_end_at' => $saleType === 'auction' ? $request->auction_end_at : null,
            'starting_price' => $saleType === 'auction' ? (float) $request->starting_price : null,
            'current_price' => $saleType === 'auction' ? (float) $request->starting_price : null,
            'min_increment' => $saleType === 'auction' ? (float) $request->min_increment : null,
            'reserve_price' => $saleType === 'auction' ? ($request->reserve_price !== null ? (float) $request->reserve_price : null) : null,
            'buy_now_price' => $saleType === 'auction' ? ($request->buy_now_price !== null ? (float) $request->buy_now_price : null) : null,
            'auction_status' => $saleType === 'auction' ? 'running' : 'draft',
            'is_active' => $fulfillment !== 'consignment',
            'is_approved' => $isApproved,
            'approval_status' => $approvalStatus,
            'meta_data' => $request->meta_data ?? [],
        ]);

        if ($fulfillment === 'consignment') {
            MarketplaceConsignmentIntake::create([
                'product_id' => $product->id,
                'seller_id' => Auth::id(),
                'status' => 'requested',
                'dropoff_method' => $request->dropoff_method,
                'dropoff_location' => $request->dropoff_location,
            ]);
        }

        $uploadedFiles = [];
        if ($request->hasFile('images')) {
            $uploadedFiles = array_slice($request->file('images'), 0, 4);
        } elseif ($request->hasFile('image')) {
            $uploadedFiles = [$request->file('image')];
        }

        if (!empty($uploadedFiles)) {
            /** @var ImageUploadService $imageService */
            $imageService = app(ImageUploadService::class);
            foreach ($uploadedFiles as $index => $file) {
                if ($file && $file->isValid()) {
                    $sizes = $imageService->upload($file, 'marketplace/products', [], 80);
                    $path = $sizes['large'] ?? ($sizes['medium'] ?? reset($sizes));
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => ($index === 0),
                    ]);
                }
            }
        }

        return redirect()->route('marketplace.seller.products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(MarketplaceProduct $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }
        $categories = MarketplaceCategory::all();
        $brands = MarketplaceBrand::with('categories:id,slug')->orderBy('name')->get();
        $withSidebar = true;

        return view('marketplace.seller.products.edit', compact('product', 'categories', 'brands', 'withSidebar'));
    }

    public function update(Request $request, MarketplaceProduct $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $hasBids = $product->sale_type === 'auction' ? $product->bids()->exists() : false;

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:marketplace_categories,id',
            'brand_id' => 'nullable|exists:marketplace_brands,id',
            'size' => 'nullable|string|max:50',
            'price' => $product->sale_type === 'fixed' ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'stock' => $product->sale_type === 'fixed' ? 'required|integer|min:0' : 'nullable|integer|min:0',
            'auction_end_at' => $product->sale_type === 'auction' && ! $hasBids ? 'nullable|date|after:now' : 'nullable',
            'reserve_price' => $product->sale_type === 'auction' && ! $hasBids ? 'nullable|numeric|min:0' : 'nullable',
            'buy_now_price' => $product->sale_type === 'auction' && ! $hasBids ? 'nullable|numeric|min:0' : 'nullable',
            'fulfillment_mode' => 'nullable|in:self_ship,consignment',
            'dropoff_method' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['title', 'category_id', 'brand_id', 'size', 'description', 'condition']);

        if ($product->sale_type === 'fixed') {
            $data['price'] = (float) $request->price;
            $data['stock'] = (int) $request->stock;
        }

        if ($product->sale_type === 'auction' && ! $hasBids) {
            if ($request->filled('auction_end_at')) {
                $data['auction_end_at'] = $request->auction_end_at;
            }
            $data['reserve_price'] = $request->reserve_price !== null ? (float) $request->reserve_price : null;
            $data['buy_now_price'] = $request->buy_now_price !== null ? (float) $request->buy_now_price : null;
        }

        if ($request->filled('fulfillment_mode')) {
            $targetFulfillment = $request->fulfillment_mode;
            if ($product->fulfillment_mode !== $targetFulfillment) {
                if ($targetFulfillment === 'consignment') {
                    $data['fulfillment_mode'] = 'consignment';
                    $data['consignment_status'] = 'requested';
                    $data['is_active'] = false;
                    MarketplaceConsignmentIntake::updateOrCreate(
                        ['product_id' => $product->id],
                        [
                            'seller_id' => $product->user_id,
                            'status' => 'requested',
                            'dropoff_method' => $request->dropoff_method,
                            'dropoff_location' => $request->dropoff_location,
                        ]
                    );
                }
                if ($targetFulfillment === 'self_ship' && in_array($product->consignment_status, ['none', 'requested'], true)) {
                    $data['fulfillment_mode'] = 'self_ship';
                    $data['consignment_status'] = 'none';
                    $data['is_active'] = true;
                    MarketplaceConsignmentIntake::where('product_id', $product->id)->delete();
                }
            }
        }

        $product->update($data);

        /** @var ImageUploadService $imageService */
        $imageService = app(ImageUploadService::class);

        // Hapus foto lama yang ditandai seller
        if ($request->filled('delete_images') && is_array($request->delete_images)) {
            foreach ($request->delete_images as $imageId) {
                $img = $product->images()->find($imageId);
                if ($img) {
                    $imageService->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        // Upload foto baru jika ada (maksimal 4 foto total)
        $currentCount = $product->images()->count();
        $allowedNew = max(0, 4 - $currentCount);

        $newFiles = [];
        if ($request->hasFile('images')) {
            $newFiles = array_slice($request->file('images'), 0, $allowedNew);
        } elseif ($request->hasFile('image') && $allowedNew > 0) {
            $newFiles = [$request->file('image')];
        }

        if (!empty($newFiles)) {
            foreach ($newFiles as $file) {
                if ($file && $file->isValid()) {
                    $sizes = $imageService->upload($file, 'marketplace/products', [], 80);
                    $path = $sizes['large'] ?? ($sizes['medium'] ?? reset($sizes));
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => false,
                    ]);
                }
            }
        }

        // Pastikan selalu ada 1 foto primary
        if (!$product->images()->where('is_primary', true)->exists()) {
            $product->images()->first()?->update(['is_primary' => true]);
        }

        return redirect()->route('marketplace.seller.products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(MarketplaceProduct $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    public function processOrder(Request $request, MarketplaceOrder $order)
    {
        if ((int) $order->seller_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'paid') {
            return response()->json(['success' => false, 'message' => 'Hanya pesanan yang sudah dibayar (paid) yang dapat diproses.'], 400);
        }

        $trackingNumber = $request->input('tracking_number') ?: 'TRK-' . strtoupper(Str::random(10));

        $order->update([
            'status' => 'shipped',
            'shipping_tracking_number' => $trackingNumber,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diproses & ditandai sebagai dikirim.',
            'tracking_number' => $trackingNumber
        ]);
    }

    public function cancelOrder(MarketplaceOrder $order)
    {
        if ((int) $order->seller_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'paid'])) {
            return response()->json(['success' => false, 'message' => 'Hanya pesanan pending atau paid yang dapat dibatalkan.'], 400);
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // Restore product stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    }

    /**
     * Mark a product as SOLD (with sold channel, location/note, and buyer details)
     */
    public function markSold(Request $request, MarketplaceProduct $product)
    {
        if ((int) $product->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate([
            'sold_channel' => 'required|string|in:ruanglari,tokopedia,shopee,instagram_wa,cod_offline,other',
            'sold_channel_note' => 'nullable|string|max:255',
            'sold_to_buyer_name' => 'nullable|string|max:255',
            'sold_to_user_id' => 'nullable|exists:users,id',
        ]);

        $product->update([
            'is_sold' => true,
            'is_active' => false,
            'sold_channel' => $request->sold_channel,
            'sold_channel_note' => $request->sold_channel_note,
            'sold_to_buyer_name' => $request->sold_to_buyer_name,
            'sold_to_user_id' => $request->sold_to_user_id,
            'sold_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditandai sebagai TERJUAL (SOLD)!',
            ]);
        }

        return back()->with('success', 'Produk berhasil ditandai sebagai TERJUAL (SOLD)!');
    }

    /**
     * Mark a product as UNSOLD (re-activate)
     */
    public function markUnsold(MarketplaceProduct $product)
    {
        if ((int) $product->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $product->update([
            'is_sold' => false,
            'is_active' => true,
            'sold_channel' => null,
            'sold_channel_note' => null,
            'sold_to_buyer_name' => null,
            'sold_to_user_id' => null,
            'sold_at' => null,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status Terjual dibatalkan. Produk kembali aktif.',
            ]);
        }

        return back()->with('success', 'Status Terjual dibatalkan. Produk kembali aktif.');
    }

    /**
     * Archive or Unarchive a product
     */
    public function toggleArchive(MarketplaceProduct $product)
    {
        if ((int) $product->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $newArchivedStatus = ! $product->is_archived;

        $product->update([
            'is_archived' => $newArchivedStatus,
            'archived_at' => $newArchivedStatus ? now() : null,
            'is_active' => $newArchivedStatus ? false : (! $product->is_sold),
        ]);

        $message = $newArchivedStatus
            ? 'Produk berhasil diarsipkan. Produk tidak akan tampil di Marketplace publik.'
            : 'Produk berhasil dikeluarkan dari arsip.';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_archived' => $newArchivedStatus,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Boost a product to the top of the marketplace listing
     */
    public function boost(MarketplaceProduct $product)
    {
        if ((int) $product->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($product->is_sold || $product->is_archived || ! $product->is_active) {
            $msg = 'Produk tidak aktif atau sudah terjual sehingga tidak dapat di-boost.';
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
        }

        // Cooldown check (e.g. 1 boost per 6 hours)
        if ($product->boosted_at && $product->boosted_at->diffInHours(now()) < 6) {
            $remainingHours = 6 - $product->boosted_at->diffInHours(now());
            $msg = "Produk baru saja di-boost. Silakan tunggu {$remainingHours} jam lagi untuk melakukan boost berikutnya.";
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
        }

        $product->update([
            'boosted_at' => now(),
        ]);

        $msg = 'Produk berhasil di-boost ke urutan teratas marketplace!';

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg, 'boosted_at' => $product->boosted_at->toISOString()])
            : back()->with('success', $msg);
    }

    /**
     * Request or Activate Featured Product (Rp 25.000 / 3 hari)
     */
    public function requestFeatured(MarketplaceProduct $product)
    {
        if ((int) $product->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($product->is_sold || $product->is_archived || ! $product->is_active) {
            $msg = 'Produk tidak aktif sehingga tidak dapat dijadikan Featured.';
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
        }

        $featuredDays = 3;
        $currentExpiry = ($product->is_featured && $product->featured_until && $product->featured_until->gt(now()))
            ? $product->featured_until
            : now();

        $product->update([
            'is_featured' => true,
            'featured_until' => $currentExpiry->addDays($featuredDays),
        ]);

        $msg = 'Produk berhasil dijadikan Featured Product selama 3 hari!';

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg, 'featured_until' => $product->featured_until->format('d M Y H:i')])
            : back()->with('success', $msg);
    }
}
