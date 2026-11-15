@extends('layouts.pacerhub', ['hideFooter' => false, 'hideChat' => true, 'hideNav' => false])

@section('title', 'Proyek Kalender Saya - Ruang Lari')
@section('meta_title', 'Proyek Kalender Saya - Ruang Lari')
@section('meta_description', 'Kelola proyek desain kalender lari pribadi Anda, buka editor studio, lihat pratinjau cetak, atau hapus proyek yang tidak diperlukan.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .kp-projects {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .kp-projects h1,
    .kp-projects h2,
    .kp-projects h3 {
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

@push('structured_data')
<meta name="robots" content="noindex,nofollow">
@endpush

@section('content')
@php
    $templatePhotos = [
        'bold' => 'images/kalender-pelari/bg/788438538_18142926247584739_300127733191259686_n.jpg',
        'strava' => 'images/kalender-pelari/bg/815920233_18619567672013478_7707120302547332417_n.jpg',
        'classic' => 'images/kalender-pelari/bg/819207920_18620464486013478_340093240344695805_n.jpg',
        'night' => 'images/kalender-pelari/bg/825352127_18622491895013478_5874230830227099846_n.jpg',
        'badge' => 'images/kalender-pelari/bg/830540436_18146023228584739_537799408712608694_n.jpg',
        'topography' => 'images/kalender-pelari/bg/840363425_18625537549013478_2717575911219471235_n.jpg',
    ];

    $formatLabels = [
        'A4_LANDSCAPE' => 'Meja A4 Landscape',
        'A3_LANDSCAPE' => 'Dinding A3 Landscape',
        'A3_PORTRAIT' => 'Dinding A3 Portrait',
        'DESK_CALENDAR' => 'Kalender Meja',
    ];

    $statusLabels = [
        'ACTIVE' => ['label' => 'Aktif', 'class' => 'bg-slate-800 text-neon'],
        'DRAFT_SAVED' => ['label' => 'Tersimpan', 'class' => 'bg-slate-800 text-slate-200'],
        'DRAFT_TRIAL' => ['label' => 'Draft Uji Coba', 'class' => 'bg-slate-800 text-slate-300'],
        'PENDING_CHECKOUT' => ['label' => 'Menunggu Bayar', 'class' => 'bg-slate-800 text-amber-300'],
        'PRODUCTION_ACTIVE' => ['label' => 'Siap Cetak', 'class' => 'bg-neon text-slate-950'],
        'ARCHIVED' => ['label' => 'Diarsipkan', 'class' => 'bg-slate-800 text-slate-400'],
        'EXPIRED_TRIAL' => ['label' => 'Kedaluwarsa', 'class' => 'bg-slate-800 text-red-300'],
    ];

    $capacityPct = $free_limit > 0 ? min(100, (int) round(($total_owned / $free_limit) * 100)) : 100;
@endphp

<div class="kp-projects bg-slate-950 text-slate-100 min-h-screen pt-10 sm:pt-12 pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        {{-- Breadcrumb & Header Row --}}
        <div class="mb-6">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                <a href="{{ route('kalender-pelari.landing') }}" class="hover:text-white transition duration-150">Kalender Pelari</a>
                <span aria-hidden="true">/</span>
                <span class="text-slate-200 font-medium">Proyek Saya</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl text-white">Proyek Kalender Saya</h1>
                    <p class="text-sm text-slate-300 mt-1.5 max-w-2xl">
                        Kelola seluruh desain kalender yang sudah Anda simpan, lanjutkan pengeditan di studio, atau buka pratinjau PDF siap cetak.
                    </p>
                </div>

                {{-- Symmetric Action Buttons --}}
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2.5 shrink-0">
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.kalender-pelari.projects.index') }}"
                           class="col-span-2 sm:col-span-1 inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-sm font-semibold text-white transition duration-150 whitespace-nowrap">
                            Antrean Admin
                        </a>
                    @endif
                    <a href="{{ route('kalender-pelari.wizard') }}"
                       class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-sm font-semibold text-white transition duration-150 whitespace-nowrap">
                        Wizard
                    </a>
                    <a href="{{ route('kalender-pelari.studio') }}"
                       class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150 whitespace-nowrap">
                        + Buat Kalender
                    </a>
                </div>
            </div>
        </div>

        {{-- Solid Capacity Status Strip --}}
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="mt-1 sm:mt-0 w-2.5 h-2.5 rounded-sm shrink-0 {{ $can_create_new === false ? 'bg-amber-400' : 'bg-neon' }}"></span>
                    <div>
                        <div class="text-sm font-semibold text-white">
                            @if($can_create_new === false)
                                Batas Penyimpanan Gratis Penuh
                            @else
                                Kapasitas Penyimpanan Proyek
                            @endif
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">
                            @if($can_create_new === false)
                                Hapus proyek lama yang sudah tidak dipakai agar bisa membuat desain kalender baru.
                            @else
                                Setiap akun dapat menyimpan hingga {{ $free_limit }} proyek kalender secara gratis dan melakukan autosave di editor.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="sm:w-44 shrink-0">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-400">Terpakai</span>
                        <span class="kp-mono font-bold text-white">{{ $total_owned }} / {{ $free_limit }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-950 border border-slate-800 rounded-sm overflow-hidden">
                        <div class="h-full rounded-sm {{ $can_create_new === false ? 'bg-amber-400' : 'bg-neon' }}" style="width: {{ $capacityPct }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-slate-900 border border-slate-700 rounded-md px-4 py-3 mb-6 flex items-center gap-3 text-sm text-white">
                <span class="w-2 h-2 rounded-sm bg-neon shrink-0"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($projects->isEmpty())
            <section class="bg-slate-900 border border-slate-800 rounded-lg p-8 sm:p-12 text-center">
                <h2 class="text-xl sm:text-2xl text-white mb-2">Belum Ada Proyek Tersimpan</h2>
                <p class="text-sm text-slate-300 max-w-md mx-auto mb-6 leading-relaxed">
                    Mulai rancang kalender lari Anda di Studio Editor atau gunakan Wizard 5 langkah untuk menyusun seluruh halaman lebih cepat.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-sm mx-auto">
                    <a href="{{ route('kalender-pelari.studio') }}"
                       class="inline-flex items-center justify-center h-10 px-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                        Buka Studio Editor
                    </a>
                    <a href="{{ route('kalender-pelari.wizard') }}"
                       class="inline-flex items-center justify-center h-10 px-5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-semibold transition duration-150">
                        Wizard Cepat
                    </a>
                </div>
            </section>
        @else
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($projects as $p)
                    @php
                        $coverUrl = $p->coverAsset?->storagePublicUrl();
                        $fallbackImg = $templatePhotos[$p->template_id ?? 'bold'] ?? $templatePhotos['bold'];
                        $thumbSrc = $coverUrl ?: asset($fallbackImg);
                        $formatText = $formatLabels[$p->format_type] ?? str_replace('_', ' ', (string) $p->format_type);
                        $statusInfo = $statusLabels[$p->status] ?? ['label' => $p->status, 'class' => 'bg-slate-800 text-slate-300'];
                    @endphp

                    <article class="flex flex-col justify-between bg-slate-900 border border-slate-800 rounded-lg overflow-hidden hover:border-slate-700 transition duration-150">
                        <div>
                            {{-- Cover Specimen Thumbnail --}}
                            <a href="{{ $p->routeEditor() }}" class="block bg-slate-950 border-b border-slate-800 p-3 focus:outline-none">
                                <div class="rounded overflow-hidden border border-slate-800 bg-slate-900">
                                    <div class="px-2.5 py-1.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between gap-2">
                                        <span class="kp-mono text-[10px] font-bold px-2 py-0.5 rounded {{ $statusInfo['class'] }}">
                                            {{ $statusInfo['label'] }}
                                        </span>
                                        <span class="kp-mono text-[11px] font-semibold text-slate-300">
                                            {{ $p->year }}
                                        </span>
                                    </div>
                                    <div class="aspect-[16/10] bg-slate-800 overflow-hidden">
                                        <img src="{{ $thumbSrc }}"
                                             alt="Pratinjau {{ $p->name }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover object-center">
                                    </div>
                                    <div class="px-2.5 py-1.5 bg-slate-950 flex items-center justify-between kp-mono text-[10px] text-slate-400">
                                        <span>{{ strtoupper($p->template_id ?: 'BOLD') }}</span>
                                        <span>{{ $p->pages_count ?? 13 }} HALAMAN</span>
                                    </div>
                                </div>
                            </a>

                            {{-- Project Metadata --}}
                            <div class="p-5 pb-3">
                                <a href="{{ $p->routeEditor() }}" class="block group">
                                    <h2 class="text-base sm:text-lg text-white group-hover:text-neon transition duration-150 truncate">
                                        {{ $p->name }}
                                    </h2>
                                </a>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-300">
                                    <span>{{ $formatText }}</span>
                                    <span class="text-slate-600" aria-hidden="true">/</span>
                                    <span class="kp-mono text-slate-400">v{{ $p->version_counter ?? 1 }}</span>
                                    @if($p->updated_at)
                                        <span class="text-slate-600" aria-hidden="true">/</span>
                                        <span class="text-slate-400">{{ $p->updated_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Symmetric Card Actions --}}
                        <div class="px-5 pb-5 pt-2 grid grid-cols-[1fr_auto_auto] gap-2">
                            <a href="{{ $p->routeEditor() }}"
                               class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150">
                                Buka Editor
                            </a>
                            <a href="{{ route('kalender-pelari.print.preview', $p) }}"
                               class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-xs font-medium text-slate-200 hover:text-white transition duration-150"
                               title="Pratinjau Cetak PDF">
                                Pratinjau
                            </a>
                            <form method="POST"
                                  action="{{ route('kalender-pelari.destroy', $p) }}"
                                  onsubmit="return confirm('Hapus proyek {{ \Illuminate\Support\Str::limit($p->name, 40) }}? Data halaman dan elemen ikut terhapus permanen.');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-950 hover:bg-red-950 border border-slate-800 hover:border-red-900 text-xs font-medium text-slate-300 hover:text-red-200 transition duration-150"
                                        title="Hapus Proyek">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </section>

            @if($projects->hasPages())
                <nav aria-label="Navigasi halaman proyek" class="mt-8 flex justify-center">
                    {{ $projects->links() }}
                </nav>
            @endif
        @endif
    </div>
</div>
@endsection
