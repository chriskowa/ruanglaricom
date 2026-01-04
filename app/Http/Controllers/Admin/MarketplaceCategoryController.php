<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketplaceCategoryController extends Controller
{
    public function index()
    {
        $categories = MarketplaceCategory::with('parent', 'children')->get();

        return view('admin.marketplace.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:marketplace_categories,id',
            'icon' => 'nullable|string',
        ]);

        MarketplaceCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.marketplace.categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, MarketplaceCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:marketplace_categories,id',
            'icon' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.marketplace.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(MarketplaceCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.marketplace.categories.index')->with('success', 'Category deleted successfully.');
    }
}
