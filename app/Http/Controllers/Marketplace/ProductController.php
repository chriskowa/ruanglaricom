<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceBrand;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = MarketplaceProduct::where('user_id', Auth::id())->with('primaryImage')->latest()->paginate(10);

        return view('marketplace.seller.products.index', compact('products'));
    }

    public function create()
    {
        $categories = MarketplaceCategory::all();
        $brands = MarketplaceBrand::with('categories:id,slug')->orderBy('name')->get();

        return view('marketplace.seller.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:marketplace_categories,id',
            'brand_id' => 'nullable|exists:marketplace_brands,id',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|in:new,used',
            'type' => 'required|in:physical,digital_slot',
            'description' => 'required|string',
            'stock' => 'required|integer|min:1',
            'image' => 'required|image|max:2048', // Primary image
        ]);

        $product = MarketplaceProduct::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title).'-'.Str::random(6),
            'description' => $request->description,
            'price' => $request->price,
            'condition' => $request->condition,
            'type' => $request->type,
            'stock' => $request->stock,
            'meta_data' => $request->meta_data ?? [],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('marketplace/products', 'public');
            $product->images()->create([
                'image_path' => $path,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('marketplace.seller.products.index')->with('success', 'Product listed successfully!');
    }

    public function edit(MarketplaceProduct $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }
        $categories = MarketplaceCategory::all();
        $brands = MarketplaceBrand::with('categories:id,slug')->orderBy('name')->get();

        return view('marketplace.seller.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, MarketplaceProduct $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:marketplace_categories,id',
            'brand_id' => 'nullable|exists:marketplace_brands,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($request->only(['title', 'category_id', 'brand_id', 'description', 'price', 'stock', 'condition']));

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('marketplace/products', 'public');
            // Unset old primary
            $product->images()->update(['is_primary' => false]);
            $product->images()->create([
                'image_path' => $path,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('marketplace.seller.products.index')->with('success', 'Product updated.');
    }

    public function destroy(MarketplaceProduct $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }
}
