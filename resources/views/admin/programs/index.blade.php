@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manajemen Program Lari - Admin Ruang Lari')

@section('content')
<div class="container mx-auto px-4 md:px-8 py-8">
    
    <!-- Header Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">MANAJEMEN PROGRAM LARI</h1>
            <p class="text-slate-300 text-sm mt-1 font-medium">Kelola program latihan lari, status publikasi, program pilihan, dan tindakan moderasi.</p>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <!-- Filters & Search Bar -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-lg p-5 mb-8 shadow-sm">
        <form method="GET" action="{{ route('admin.programs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search Input -->
            <div class="lg:col-span-4 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul program, deskripsi, atau coach..." class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3.5 py-2 pl-9 text-sm text-white placeholder-slate-400 focus:border-neon focus:ring-1 focus:ring-neon outline-none">
                <i class="fas fa-search text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
            </div>

            <!-- Category Filter -->
            <div class="lg:col-span-2">
                <select name="category" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3 py-2 text-sm text-white focus:border-neon outline-none">
                    <option value="">Semua Kategori</option>
                    <option value="5k" {{ request('category') === '5k' ? 'selected' : '' }}>5K</option>
                    <option value="10k" {{ request('category') === '10k' ? 'selected' : '' }}>10K</option>
                    <option value="21k" {{ request('category') === '21k' ? 'selected' : '' }}>Half Marathon (21K)</option>
                    <option value="42k" {{ request('category') === '42k' ? 'selected' : '' }}>Marathon (42K)</option>
                </select>
            </div>

            <!-- Publish Status Filter -->
            <div class="lg:col-span-2">
                <select name="published" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3 py-2 text-sm text-white focus:border-neon outline-none">
                    <option value="">Status Publikasi</option>
                    <option value="1" {{ request('published') === '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ request('published') === '0' ? 'selected' : '' }}>Draft (Unpublish)</option>
                </select>
            </div>

            <!-- Featured Filter -->
            <div class="lg:col-span-2">
                <select name="featured" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700/80 rounded-md px-3 py-2 text-sm text-white focus:border-neon outline-none">
                    <option value="">Status Featured</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Non-Featured</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="lg:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2 bg-neon hover:bg-lime-400 text-dark font-bold rounded-md text-xs uppercase tracking-wider transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'featured', 'published', 'sort']))
                    <a href="{{ route('admin.programs.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md text-xs flex items-center justify-center transition-colors" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Programs Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-lg overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Program</th>
                        <th class="px-6 py-4">Coach</th>
                        <th class="px-6 py-4">Kategori / Level</th>
                        <th class="px-6 py-4 text-center">Atlet / Ulasan</th>
                        <th class="px-6 py-4">Harga</th>
                        <th class="px-6 py-4 text-center">Publikasi</th>
                        <th class="px-6 py-4 text-center">Featured</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($programs as $prog)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Title & Image -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($prog->thumbnail || $prog->banner)
                                            <img src="{{ $prog->thumbnail_url ?? $prog->banner_url }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-person-running text-neon text-base"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0 max-w-xs">
                                        <a href="{{ route('programs.show', $prog->slug) }}" target="_blank" class="font-bold text-white hover:text-neon transition-colors truncate block">
                                            {{ $prog->title }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 font-medium">{{ $prog->duration_weeks ?? 8 }} Minggu • {{ $prog->days_per_week ?? 4 }} Sesi/Mgg</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Coach -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $prog->coach && $prog->coach->avatar ? (Str::startsWith($prog->coach->avatar, 'http') ? $prog->coach->avatar : (Str::startsWith($prog->coach->avatar, 'images/') ? '/'.$prog->coach->avatar : '/storage/'.$prog->coach->avatar)) : 'https://ui-avatars.com/api/?name='.urlencode($prog->coach->name ?? 'Coach').'&background=1e293b&color=39FF14' }}" class="w-7 h-7 rounded-full object-cover border border-slate-700">
                                    <span class="font-semibold text-slate-200 text-xs">{{ $prog->coach->name ?? 'Coach' }}</span>
                                </div>
                            </td>

                            <!-- Category / Difficulty -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-200 border border-slate-700">
                                        {{ strtoupper($prog->distance_target ?: 'General') }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 capitalize">{{ $prog->difficulty ?: 'Semua Level' }}</span>
                                </div>
                            </td>

                            <!-- Athletes / Reviews -->
                            <td class="px-6 py-4 text-center">
                                <div class="font-mono font-bold text-white text-xs">{{ $prog->enrolled_count ?? $prog->active_athletes_count ?? 0 }} Atlet</div>
                                <div class="text-[10px] text-amber-400 font-medium flex items-center justify-center gap-0.5">
                                    <i class="fas fa-star text-[9px]"></i> {{ number_format($prog->average_rating ?: 5.0, 1) }}
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="px-6 py-4 font-mono font-bold text-white">
                                {{ $prog->price > 0 ? 'Rp ' . number_format($prog->price, 0, ',', '.') : 'Gratis' }}
                            </td>

                            <!-- Publish Status -->
                            <td class="px-6 py-4 text-center">
                                @if($prog->is_published)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                        Draft
                                    </span>
                                @endif
                            </td>

                            <!-- Featured Status -->
                            <td class="px-6 py-4 text-center">
                                @if($prog->is_featured)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                        Featured
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Standard</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Toggle Publish Button -->
                                    <form method="POST" action="{{ route('admin.programs.toggle-publish', $prog->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-md text-xs font-semibold transition-colors {{ $prog->is_published ? 'bg-slate-800 text-amber-300 border border-amber-500/30 hover:bg-amber-500/10' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30' }}" title="{{ $prog->is_published ? 'Tarik program ke status Draft (Unpublish)' : 'Publikasikan Program' }}">
                                            {{ $prog->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>

                                    <!-- Toggle Featured Button -->
                                    <form method="POST" action="{{ route('admin.programs.toggle-featured', $prog->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-md text-xs font-semibold transition-colors {{ $prog->is_featured ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30' : 'bg-slate-800 text-slate-300 border border-slate-700 hover:border-slate-500 hover:text-white' }}" title="{{ $prog->is_featured ? 'Batalkan Featured' : 'Jadikan Featured Program' }}">
                                            {{ $prog->is_featured ? 'Unstar' : 'Featured' }}
                                        </button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.programs.destroy', $prog->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program {{ addslashes($prog->title) }}? Tindakan ini tidak dapat dibatalkan!');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-md bg-rose-500/10 border border-rose-500/30 text-rose-400 hover:bg-rose-500/20 text-xs font-semibold transition-colors" title="Hapus Program">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-folder-open text-4xl mb-3 text-slate-600 block"></i>
                                <p class="font-bold text-slate-300">Tidak ada program lari ditemukan</p>
                                <p class="text-xs text-slate-500 mt-1">Coba ubah kata kunci pencarian atau filter yang Anda gunakan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($programs->hasPages())
            <div class="px-6 py-4 bg-slate-950 border-t border-slate-800">
                {{ $programs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
