@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manage Categories - Marketplace Admin')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <div class="flex justify-between items-end mb-8">
        <div>
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">Admin Dashboard</p>
            <h1 class="text-4xl font-black text-white italic tracking-tighter">
                MARKETPLACE <span class="text-neon">CATEGORIES</span>
            </h1>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Add Category
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
                        <th class="p-4 font-bold border-b border-slate-700">Name</th>
                        <th class="p-4 font-bold border-b border-slate-700">Slug</th>
                        <th class="p-4 font-bold border-b border-slate-700">Parent</th>
                        <th class="p-4 font-bold border-b border-slate-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($categories as $category)
                    <tr class="group hover:bg-slate-800/50 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-neon">
                                    <i class="fa-solid fa-{{ $category->icon ?? 'tag' }}"></i>
                                </div>
                                <span class="text-white font-bold">{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-slate-400 font-mono text-sm">{{ $category->slug }}</td>
                        <td class="p-4">
                            @if($category->parent)
                                <span class="px-2 py-1 rounded bg-slate-800 text-slate-300 text-xs border border-slate-700">{{ $category->parent->name }}</span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->parent_id }}', '{{ $category->icon }}')" class="p-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('admin.marketplace.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
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
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-xl font-black text-white italic">CREATE <span class="text-neon">CATEGORY</span></h3>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-slate-500 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="{{ route('admin.marketplace.categories.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" required placeholder="e.g. Running Shoes">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Parent Category</label>
                    <select name="parent_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">None (Top Level)</option>
                        @foreach($categories as $cat)
                            @if(!$cat->parent_id)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Icon Class (FontAwesome)</label>
                    <div class="flex gap-2">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-neon">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <input type="text" name="icon" class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="e.g. shoe-prints">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Use FontAwesome class names without 'fa-solid fa-'</p>
                </div>
                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-bold transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-colors">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-xl font-black text-white italic">EDIT <span class="text-neon">CATEGORY</span></h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-500 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" id="editName" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Parent Category</label>
                    <select name="parent_id" id="editParentId" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">None (Top Level)</option>
                        @foreach($categories as $cat)
                            @if(!$cat->parent_id)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-bold mb-2">Icon Class (FontAwesome)</label>
                    <div class="flex gap-2">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-neon">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <input type="text" name="icon" id="editIcon" class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                    </div>
                </div>
                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-bold transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-colors">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, parentId, icon) {
    const form = document.getElementById('editForm');
    form.action = `/admin/marketplace/categories/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editParentId').value = parentId || "";
    document.getElementById('editIcon').value = icon || "";
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection
