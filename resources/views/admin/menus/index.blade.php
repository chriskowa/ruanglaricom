@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Menu Management')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" x-data="{ showCreateModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-white italic tracking-tighter">
                MENU <span class="text-neon">MANAGER</span>
            </h1>
            <p class="text-slate-400">Manage your website menus and navigation.</p>
        </div>
        <button @click="showCreateModal = true" class="px-6 py-3 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90 transition-all flex items-center gap-2 shadow-lg shadow-neon/20">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create New Menu
        </button>
    </div>

    <!-- Alert -->
    @if(session('success'))
    <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Menu List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($menus as $menu)
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-neon/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition-colors">{{ $menu->name }}</h3>
                    <span class="text-xs font-mono text-slate-500 uppercase tracking-widest bg-slate-800 px-2 py-1 rounded border border-slate-700">{{ $menu->location }}</span>
                </div>
                <div class="flex gap-2">
                    <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this menu?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-red-500/10 hover:text-red-500 transition-colors" title="Delete">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="flex items-center justify-between text-sm text-slate-400 mt-4 pt-4 border-t border-slate-700/50">
                <span>{{ $menu->items_count }} items</span>
                <a href="{{ route('admin.menus.edit', $menu) }}" class="text-neon hover:underline font-bold flex items-center gap-1">
                    Manage Items <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 bg-card/30 rounded-2xl border border-dashed border-slate-700">
            <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-1">No Menus Found</h3>
            <p class="text-slate-400 text-sm">Create your first menu to get started.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $menus->links() }}
    </div>

    <!-- Create Modal -->
    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCreateModal" @click="showCreateModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-white mb-4" id="modal-title">Create New Menu</h3>
                    <form action="{{ route('admin.menus.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-400 mb-2">Menu Name</label>
                            <input type="text" name="name" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="e.g. Main Header" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-400 mb-2">Location (Slug)</label>
                            <input type="text" name="location" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="e.g. header, footer, sidebar" required>
                            <p class="text-xs text-slate-500 mt-1">Unique identifier for this menu location.</p>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 font-bold transition-colors">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90 transition-colors">Create Menu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
