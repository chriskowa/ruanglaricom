<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceBrand;
use App\Models\Marketplace\MarketplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MarketplaceBrandController extends Controller
{
    public function index()
    {
        $brands = MarketplaceBrand::with('categories')->orderBy('name')->get();
        $categories = MarketplaceCategory::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('admin.marketplace.brands.index', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:marketplace_categories,id',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('marketplace/brands', 'public');
        }

        $brand = MarketplaceBrand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'logo' => $logoPath,
        ]);

        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()->route('admin.marketplace.brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, MarketplaceBrand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:marketplace_categories,id',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('marketplace/brands', 'public');
        }

        $brand->update($data);

        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        } else {
            $brand->categories()->detach();
        }

        return redirect()->route('admin.marketplace.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(MarketplaceBrand $brand)
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();
        return redirect()->route('admin.marketplace.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
