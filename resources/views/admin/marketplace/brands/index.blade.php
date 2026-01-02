@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manage Brands - Marketplace Admin')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <div class="flex justify-between items-end mb-8">
        <div>
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">Admin Dashboard</p>
            <h1 class="text-4xl font-black text-white italic tracking-tighter">
                MARKETPLACE <span class="text-neon">BRANDS</span>
            </h1>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Add Brand
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-slate-400 text-sm uppercase tracking-wider">
                        <th class="p-4 font-bold border-b border-slate-700">Logo</th>
                        <th class="p-4 font-bold border-b border-slate-700">Name</th>
                        <th class="p-4 font-bold border-b border-slate-700">Type</th>
                        <th class="p-4 font-bold border-b border-slate-700">Categories</th>
                        <th class="p-4 font-bold border-b border-slate-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($brands as $brand)
                    <tr class="group hover:bg-slate-800/50 transition-colors">
                        <td class="p-4">
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="w-10 h-10 rounded-lg object-contain bg-white">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 font-bold">
                                    {{ substr($brand->name, 0, 1) }}
                                </div>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="text-white font-bold">{{ $brand->name }}</span>
                            <div class="text-xs text-slate-500 font-mono">{{ $brand->slug }}</div>
                        </td>
                        <td class="p-4">
                            @if($brand->type)
                                <span class="px-2 py-1 rounded bg-slate-800 text-slate-300 text-xs border border-slate-700 uppercase">{{ $brand->type }}</span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($brand->categories as $category)
                                    <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 text-xs border border-blue-500/20">{{ $category->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="editBrand({{ $brand->id }}, '{{ addslashes($brand->name) }}', '{{ $brand->type }}', {{ json_encode($brand->categories->pluck('id')) }})" class="p-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('admin.marketplace.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('createModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-xl font-black text-white italic">CREATE <span class="text-neon">BRAND</span></h3>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-slate-500 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-6">
                <form action="{{ route('admin.marketplace.brands.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Brand Name</label>
                        <input type="text" name="name" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" required placeholder="e.g. Nike">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Type</label>
                        <select name="type" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Select Type</option>
                            <option value="International">International</option>
                            <option value="Local">Local</option>
                            <option value="Tech">Tech</option>
                            <option value="Nutrition">Nutrition</option>
                            <option value="Accessories">Accessories</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Categories</label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-800 border border-slate-700 rounded-xl p-4 max-h-48 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="inline-flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="form-checkbox text-neon rounded bg-slate-700 border-slate-600 focus:ring-neon">
                                    <span class="text-slate-300 text-sm">{{ $category->name }}</span>
                                </label>
                                @foreach($category->children as $child)
                                    <label class="inline-flex items-center space-x-2 cursor-pointer ml-4">
                                        <input type="checkbox" name="categories[]" value="{{ $child->id }}" class="form-checkbox text-neon rounded bg-slate-700 border-slate-600 focus:ring-neon">
                                        <span class="text-slate-300 text-sm">{{ $child->name }}</span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-bold transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-colors">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-xl font-black text-white italic">EDIT <span class="text-neon">BRAND</span></h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-500 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-6">
                <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Brand Name</label>
                        <input type="text" name="name" id="editName" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" required>
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Type</label>
                        <select name="type" id="editType" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Select Type</option>
                            <option value="International">International</option>
                            <option value="Local">Local</option>
                            <option value="Tech">Tech</option>
                            <option value="Nutrition">Nutrition</option>
                            <option value="Accessories">Accessories</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Logo (Leave empty to keep current)</label>
                        <input type="file" name="logo" accept="image/*" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm font-bold mb-2">Categories</label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-800 border border-slate-700 rounded-xl p-4 max-h-48 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="inline-flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="form-checkbox text-neon rounded bg-slate-700 border-slate-600 focus:ring-neon edit-category-checkbox">
                                    <span class="text-slate-300 text-sm">{{ $category->name }}</span>
                                </label>
                                @foreach($category->children as $child)
                                    <label class="inline-flex items-center space-x-2 cursor-pointer ml-4">
                                        <input type="checkbox" name="categories[]" value="{{ $child->id }}" class="form-checkbox text-neon rounded bg-slate-700 border-slate-600 focus:ring-neon edit-category-checkbox">
                                        <span class="text-slate-300 text-sm">{{ $child->name }}</span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-bold transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-colors">Update Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editBrand(id, name, type, categories) {
    const form = document.getElementById('editForm');
    form.action = `/admin/marketplace/brands/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editType').value = type || "";
    
    // Reset checkboxes
    document.querySelectorAll('.edit-category-checkbox').forEach(cb => cb.checked = false);
    
    // Check associated categories
    if (categories) {
        categories.forEach(catId => {
            const cb = document.querySelector(`.edit-category-checkbox[value="${catId}"]`);
            if (cb) cb.checked = true;
        });
    }

    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection
