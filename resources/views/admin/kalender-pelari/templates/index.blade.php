@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Template Idola Lari - Admin Ruang Lari')

@section('content')
<div class="container mx-auto px-4 md:px-8 py-8 pt-0">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                MANAJEMEN TEMPLATE IDOLA LARI
            </h1>
            <p class="text-slate-300 text-sm mt-1">
                Kelola paket foto idola atlet lari (Agus Prayogo, Odekta, Kipchoge, dll.) yang dapat diterapkan 1-klik di Studio Kalender.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.kalender-pelari.projects.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-md border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Ke Proyek Cetak</span>
            </a>
            <a href="{{ route('admin.kalender-pelari.templates.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm transition flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Tambah Template Idola</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Template Atlet</div>
            <div class="text-2xl font-bold text-white font-mono mt-1">{{ number_format($totalAthletes) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Koleksi template idola lari</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Template Aktif</div>
            <div class="text-2xl font-bold text-emerald-400 font-mono mt-1">{{ number_format($activeAthletes) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tampil di Studio Kalender</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 col-span-2 md:col-span-1">
            <div class="text-xs font-semibold uppercase tracking-wider text-sky-400">Integrasi Studio</div>
            <div class="text-sm font-semibold text-white mt-1.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>API Tersinkronisasi</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Auto-load 13 slot foto ke canvas</div>
        </div>
    </div>

    <!-- Template Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $t)
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden shadow-sm flex flex-col justify-between hover:border-slate-700 transition">
                <!-- Cover Image & Header Tags -->
                <div class="relative h-48 bg-slate-950 overflow-hidden border-b border-slate-800">
                    <img src="{{ $t->cover_url }}" alt="{{ $t->name }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                    
                    <!-- Top Badges -->
                    <div class="absolute top-3 left-3 flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-950/80 backdrop-blur-sm text-white border border-slate-800">
                            {{ $t->category ?: 'Atlet' }}
                        </span>
                        @if($t->is_active)
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                                Aktif
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <!-- Photo counter badge -->
                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-slate-950/80 backdrop-blur-sm text-slate-200 border border-slate-800 flex items-center gap-1.5">
                            <i class="fas fa-camera text-[10px]"></i>
                            <span>{{ $t->filled_photos_count }}/13 Foto</span>
                        </span>
                    </div>

                    <!-- Title overlaid at bottom of image -->
                    <div class="absolute bottom-3 left-3 right-3">
                        <h2 class="text-base font-bold text-white tracking-tight line-clamp-1" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                            {{ $t->name }}
                        </h2>
                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">slug: {{ $t->slug }}</div>
                    </div>
                </div>

                <!-- Body Content -->
                <div class="p-4 flex-1 flex flex-col justify-between">
                    <p class="text-xs text-slate-300 line-clamp-3 leading-relaxed mb-4">
                        {{ strip_tags($t->bio) ?: 'Belum ada deskripsi profil atau biografi atlet.' }}
                    </p>

                    <!-- Template Preset Meta -->
                    <div class="grid grid-cols-3 gap-2 p-2.5 rounded-md bg-slate-950 border border-slate-800/80 text-[11px] mb-4">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Aksen</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-3 h-3 rounded-sm border border-slate-700" style="background-color: {{ $t->accent_color }};"></span>
                                <span class="text-white font-mono text-[10px]">{{ $t->accent_color }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Kertas</span>
                            <span class="text-white font-medium capitalize mt-0.5 block">{{ $t->paper_color }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Font</span>
                            <span class="text-white font-medium truncate mt-0.5 block">{{ $t->default_font }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                        <span class="text-[11px] text-slate-400 font-mono">Urutan: #{{ $t->sort_order }}</span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.kalender-pelari.templates.edit', $t) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-md text-xs font-semibold border border-slate-700 transition flex items-center gap-1.5">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.kalender-pelari.templates.destroy', $t) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template atlet ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-rose-300 rounded-md text-xs transition" title="Hapus Template">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-slate-900 border border-slate-800 rounded-lg">
                <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400">
                    <i class="fas fa-images text-xl"></i>
                </div>
                <div class="font-semibold text-white">Belum ada template idola lari</div>
                <p class="text-xs text-slate-400 mt-1">Buat template atlet pertama agar foto contoh dapat dipilih oleh pengguna di Studio Kalender.</p>
                <a href="{{ route('admin.kalender-pelari.templates.create') }}" class="inline-block mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md transition">
                    Tambah Template Sekarang
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
