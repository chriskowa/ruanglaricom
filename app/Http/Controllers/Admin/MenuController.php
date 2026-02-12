<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::withCount('items')->latest()->paginate(10);

        return view('admin.menus.index', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255|unique:menus,location',
        ]);

        Menu::create([
            'name' => $request->name,
            'location' => $request->location,
            'is_active' => true,
        ]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['items' => function ($query) {
            $query->whereNull('parent_id')->with('children.children')->orderBy('order');
        }]);

        return view('admin.menus.builder', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255|unique:menus,location,'.$menu->id,
        ]);

        $menu->update($request->only('name', 'location'));

        return redirect()->back()->with('success', 'Menu details updated');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully');
    }

    public function addItem(Request $request, Menu $menu)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string',
            'target' => 'required|in:_self,_blank',
            'parent_id' => 'nullable|exists:menu_items,id',
        ]);

        $menu->items()->create([
            'title' => $request->title,
            'url' => $request->url,
            'target' => $request->target,
            'parent_id' => $request->parent_id,
            'order' => $menu->items()->max('order') + 1,
        ]);

        return redirect()->back()->with('success', 'Item added successfully');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string',
            'target' => 'required|in:_self,_blank',
        ]);

        $item->update($request->only('title', 'url', 'target', 'icon'));

        return redirect()->back()->with('success', 'Item updated successfully');
    }

    public function deleteItem(MenuItem $item)
    {
        $item->delete();

        return redirect()->back()->with('success', 'Item deleted successfully');
    }

    public function reorder(Request $request, Menu $menu)
    {
        $items = $request->input('items', []);

        DB::transaction(function () use ($items) {
            $this->updateTree($items);
        });

        return response()->json(['status' => 'success']);
    }

    private function updateTree(array $items, $parentId = null)
    {
        foreach ($items as $index => $item) {
            MenuItem::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'order' => $index,
            ]);

            if (isset($item['children']) && ! empty($item['children'])) {
                $this->updateTree($item['children'], $item['id']);
            }
        }
    }
}
