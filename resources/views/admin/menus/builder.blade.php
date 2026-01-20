@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Menu Builder - ' . $menu->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.menus.index') }}" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <h1 class="text-3xl font-black text-white italic tracking-tighter">
                    MENU <span class="text-neon">BUILDER</span>
                </h1>
            </div>
            <p class="text-slate-400">Editing: <span class="text-white font-bold">{{ $menu->name }}</span> ({{ $menu->location }})</p>
        </div>
        <button id="save-menu" class="px-6 py-3 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90 transition-all flex items-center gap-2 shadow-lg shadow-neon/20 loading-state">
            <span class="default-text flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                Save Menu Structure
            </span>
            <span class="loading-text hidden flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            </span>
        </button>
    </div>

    <!-- Alert -->
    @if(session('success'))
    <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Settings & Add Items -->
        <div class="space-y-6">
            
            <!-- Menu Settings -->
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Settings
                </h3>
                <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Name</label>
                            <input type="text" name="name" value="{{ $menu->name }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Location</label>
                            <input type="text" name="location" value="{{ $menu->location }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" required>
                        </div>
                        <button type="submit" class="w-full py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold text-sm transition-colors">Update Settings</button>
                    </div>
                </form>
            </div>

            <!-- Add Custom Link -->
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    Add Custom Link
                </h3>
                <form action="{{ route('admin.menus.items.store', $menu) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Link Text</label>
                            <input type="text" name="title" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="My Page" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">URL</label>
                            <input type="text" name="url" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="https://..." required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Target</label>
                            <select name="target" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold text-sm transition-colors border border-slate-600 hover:border-slate-500 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            Add to Menu
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Column: Menu Structure -->
        <div class="lg:col-span-2">
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 min-h-[500px]">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                    Menu Structure
                </h3>
                
                <div class="dd" id="nestable">
                    <ol class="dd-list space-y-2">
                        @foreach($menu->items as $item)
                            <li class="dd-item" data-id="{{ $item->id }}">
                                <div class="dd-handle bg-slate-800 border border-slate-700 rounded-lg p-3 hover:border-slate-600 cursor-move flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <span class="text-slate-500 cursor-move">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                        </span>
                                        <div>
                                            <div class="font-bold text-white text-sm">{{ $item->title }}</div>
                                            <div class="text-xs text-slate-500">{{ $item->url }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity dd-nodrag">
                                        <button onclick="editItem({{ $item }})" type="button" class="p-1.5 rounded text-blue-400 hover:bg-blue-400/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <form action="{{ route('admin.menus.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this item?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded text-red-400 hover:bg-red-400/10 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if($item->children->count() > 0)
                                    <ol class="dd-list pl-8 pt-2 space-y-2 border-l border-slate-700 ml-4">
                                        @foreach($item->children as $child)
                                            <li class="dd-item" data-id="{{ $child->id }}">
                                                <div class="dd-handle bg-slate-800 border border-slate-700 rounded-lg p-3 hover:border-slate-600 cursor-move flex items-center justify-between group">
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-slate-500 cursor-move">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                                        </span>
                                                        <div>
                                                            <div class="font-bold text-white text-sm">{{ $child->title }}</div>
                                                            <div class="text-xs text-slate-500">{{ $child->url }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button onclick="editItem({{ $child }})" type="button" class="p-1.5 rounded text-blue-400 hover:bg-blue-400/10 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                        </button>
                                                        <form action="{{ route('admin.menus.items.destroy', $child->id) }}" method="POST" onsubmit="return confirm('Delete this item?')" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded text-red-400 hover:bg-red-400/10 transition-colors">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                @if($child->children->count() > 0)
                                                    <ol class="dd-list pl-8 pt-2 space-y-2 border-l border-slate-700 ml-4">
                                                        @foreach($child->children as $subchild)
                                                            <li class="dd-item" data-id="{{ $subchild->id }}">
                                                                <div class="dd-handle bg-slate-800 border border-slate-700 rounded-lg p-3 hover:border-slate-600 cursor-move flex items-center justify-between group">
                                                                    <div class="flex items-center gap-3">
                                                                        <span class="text-slate-500 cursor-move">
                                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                                                        </span>
                                                                        <div>
                                                                            <div class="font-bold text-white text-sm">{{ $subchild->title }}</div>
                                                                            <div class="text-xs text-slate-500">{{ $subchild->url }}</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                        <button onclick="editItem({{ $subchild }})" type="button" class="p-1.5 rounded text-blue-400 hover:bg-blue-400/10 transition-colors">
                                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                                        </button>
                                                                        <form action="{{ route('admin.menus.items.destroy', $subchild->id) }}" method="POST" onsubmit="return confirm('Delete this item?')" class="inline-block">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="p-1.5 rounded text-red-400 hover:bg-red-400/10 transition-colors">
                                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if($menu->items->isEmpty())
                    <div class="text-center py-12 border border-dashed border-slate-700 rounded-xl bg-slate-800/30">
                        <p class="text-slate-500">No items in this menu yet.</p>
                        <p class="text-slate-600 text-sm">Add items from the left panel.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-white mb-4">Edit Menu Item</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Link Text</label>
                                <input type="text" name="title" id="edit-title" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">URL</label>
                                <input type="text" name="url" id="edit-url" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Target</label>
                                <select name="target" id="edit-target" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                    <option value="_self">Same Tab</option>
                                    <option value="_blank">New Tab</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-neon text-base font-medium text-dark hover:bg-neon/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save Changes</button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-600 shadow-sm px-4 py-2 bg-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Nestable2/1.6.0/jquery.nestable.min.css" />
<style>
    .dd { max-width: 100%; }
    .dd-list .dd-list { padding-left: 30px; }
    .dd-handle { height: auto; padding: 0; background: transparent; border: none; }
    .dd-item > button { 
        margin-left: 0; 
        color: #94a3b8;
    }
    .dd-placeholder {
        background: rgba(255,255,255,0.05);
        border: 1px dashed #475569;
        border-radius: 8px;
        min-height: 40px;
        margin-bottom: 8px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    // Ensure jQuery is globally available before Nestable loads
    window.jQuery = window.$ = jQuery;
</script>
<script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/jquery.nestable.min.js"></script>
<script>
    (function($) {
        $(document).ready(function() {
            // Check if Nestable is loaded
            if (typeof $.fn.nestable === 'undefined') {
                console.error('Nestable2 plugin not loaded properly.');
                console.log('jQuery version:', $.fn.jquery);
                console.log('Available plugins:', Object.keys($.fn));
                alert('System Warning: Menu editor libraries failed to load (Nestable2). Please check your internet connection or console for details.');
                return;
            }

            // Initialize Nestable
            $('#nestable').nestable({
                maxDepth: 3,
                group: 1
            });

            // Save Menu
            $('#save-menu').on('click', function() {
                var btn = $(this);
                
                // Double check before action
                if (typeof $('#nestable').nestable !== 'function') {
                    alert('Error: Menu editor not initialized.');
                    return;
                }

                var items = $('#nestable').nestable('serialize');
                
                // Show loading
                btn.find('.default-text').addClass('hidden');
                btn.find('.loading-text').removeClass('hidden');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.menus.reorder', $menu) }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        items: items
                    },
                    success: function(response) {
                        alert('Menu structure saved successfully!');
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        alert('Error saving menu structure: ' + (xhr.statusText || 'Unknown error'));
                    },
                    complete: function() {
                        // Hide loading
                        btn.find('.default-text').removeClass('hidden');
                        btn.find('.loading-text').addClass('hidden');
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    })(jQuery);

    function editItem(item) {
        document.getElementById('edit-title').value = item.title;
        document.getElementById('edit-url').value = item.url;
        document.getElementById('edit-target').value = item.target;
        
        var form = document.getElementById('editForm');
        // Use manual URL construction to avoid route helper issues with placeholders
        form.action = "{{ url('admin/menus/items') }}/" + item.id;
        
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endpush

@endsection
