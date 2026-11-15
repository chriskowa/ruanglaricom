@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Template Idola Lari - Admin Ruang Lari')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .kp-admin-templates {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .kp-admin-templates h1,
    .kp-admin-templates h2 {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .kp-mono {
        font-family: 'JetBrains Mono', monospace;
        font-variant-numeric: tabular-nums;
    }
</style>
@endpush

@section('content')
<div class="kp-admin-templates max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-10 pb-12">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl text-white">Manajemen Template Idola Lari</h1>
            <p class="text-slate-300 text-sm mt-1.5 max-w-2xl">
                Kelola paket foto atlet lari yang tersedia sebagai template cepat di Studio Kalender.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.kalender-pelari.projects.index') }}"
               class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs sm:text-sm font-semibold transition duration-150 whitespace-nowrap">
                Proyek Cetak
            </a>
            <a href="{{ route('admin.kalender-pelari.templates.create') }}"
               class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-xs sm:text-sm font-bold transition duration-150 whitespace-nowrap">
                + Tambah Template
            </a>
        </div>
    </div>

    {{-- Solid Flash Notification --}}
    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-md bg-slate-900 border border-slate-700 text-white text-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-sm bg-neon shrink-0"></span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-semibold text-slate-400 hover:text-white transition">
                Tutup
            </button>
        </div>
    @endif

    {{-- Compact Single-Row Summary Bar --}}
    <div class="bg-slate-900 border border-slate-800 rounded-lg px-5 py-3.5 mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-6">
            <div class="flex items-center gap-2.5">
                <span class="text-xs text-slate-400">Total Template</span>
                <span class="kp-mono text-sm font-bold text-white">{{ number_format($totalAthletes) }}</span>
            </div>
            <div class="h-4 w-px bg-slate-800 hidden sm:block"></div>
            <div class="flex items-center gap-2.5">
                <span class="text-xs text-slate-400">Aktif di Studio</span>
                <span class="kp-mono text-sm font-bold text-neon">{{ number_format($activeAthletes) }}</span>
            </div>
        </div>
        <div class="text-xs text-slate-300">
            Slot foto: <span class="kp-mono text-white font-semibold">13 halaman / paket</span>
        </div>
    </div>

    {{-- Template Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($templates as $t)
            <article class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden flex flex-col justify-between hover:border-slate-700 transition duration-150">
                <div>
                    {{-- Top Opaque Status Strip --}}
                    <div class="px-4 py-2.5 bg-slate-950 border-b border-slate-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="kp-mono text-[11px] px-2 py-0.5 rounded bg-slate-800 text-slate-200 truncate">
                                {{ $t->category ?: 'Atlet' }}
                            </span>
                            @if($t->is_active)
                                <span class="kp-mono text-[11px] font-bold px-2 py-0.5 rounded bg-neon text-slate-950">
                                    Aktif
                                </span>
                            @else
                                <span class="kp-mono text-[11px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                        <span class="kp-mono text-[11px] font-semibold text-slate-300 shrink-0">
                            {{ $t->filled_photos_count }}/13 Foto
                        </span>
                    </div>

                    {{-- Clean Unobstructed Cover Image --}}
                    <div class="aspect-[16/10] bg-slate-950 border-b border-slate-800 overflow-hidden">
                        <img src="{{ $t->cover_url }}" alt="{{ $t->name }}" loading="lazy" class="w-full h-full object-cover object-center">
                    </div>

                    {{-- Body Content --}}
                    <div class="p-5 pb-3">
                        <h2 class="text-base sm:text-lg text-white line-clamp-1">{{ $t->name }}</h2>
                        <div class="kp-mono text-[11px] text-slate-400 mt-0.5 mb-3 truncate">{{ $t->slug }}</div>

                        <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed mb-4">
                            {{ strip_tags($t->bio) ?: 'Belum ada deskripsi profil atau biografi atlet.' }}
                        </p>

                        {{-- Template Preset Meta --}}
                        <div class="grid grid-cols-3 gap-2 p-2.5 rounded-md bg-slate-950 border border-slate-800 text-[11px]">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Aksen</span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="w-3 h-3 rounded-sm border border-slate-700 shrink-0" style="background-color: {{ $t->accent_color }};"></span>
                                    <span class="kp-mono text-white text-[10px] truncate">{{ $t->accent_color }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Kertas</span>
                                <span class="text-white font-medium capitalize mt-0.5 block truncate">{{ $t->paper_color }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">Font</span>
                                <span class="text-white font-medium truncate mt-0.5 block">{{ $t->default_font }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="px-5 pb-5 pt-3 border-t border-slate-800 flex items-center justify-between gap-3">
                    <span class="kp-mono text-xs text-slate-400">Urutan #{{ $t->sort_order }}</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.kalender-pelari.templates.edit', $t) }}"
                           class="inline-flex items-center justify-center h-9 px-3.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150">
                            Edit
                        </a>
                        <form method="POST"
                              action="{{ route('admin.kalender-pelari.templates.destroy', $t) }}"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus template atlet ini?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-950 hover:bg-red-950 border border-slate-800 hover:border-red-900 text-xs font-medium text-slate-300 hover:text-red-200 transition duration-150">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full py-12 px-6 text-center bg-slate-900 border border-slate-800 rounded-lg">
                <h2 class="text-lg text-white">Belum Ada Template Idola Lari</h2>
                <p class="text-sm text-slate-300 mt-1.5 max-w-md mx-auto">
                    Buat template atlet pertama agar foto contoh siap dipilih oleh pengguna di Studio Kalender.
                </p>
                <a href="{{ route('admin.kalender-pelari.templates.create') }}"
                   class="inline-flex items-center justify-center h-10 px-5 mt-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                    + Tambah Template Sekarang
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($templates->hasPages())
        <div class="mt-8">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
