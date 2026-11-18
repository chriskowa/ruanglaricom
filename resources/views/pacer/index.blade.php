@extends('layouts.pacerhub', [
    'skipHeavyAssets' => true,
    'skipLoader' => true,
])

@php
    $seoTitle = 'Direktori Pacer Lari Indonesia | Cari & Booking Pacer 10K, HM, Marathon | Ruang Lari';
    $seoDesc = 'Temukan pacer lari berpengalaman dan terverifikasi di Indonesia untuk target waktu 10K, Half Marathon (21K), hingga Full Marathon (42K). Cek target pace, personal best, kota asal, dan lihat profil lengkapnya di Ruang Lari.';
    $seoCanonical = route('pacer.index');
    $verifiedCount = $pacers->where('verified', true)->count();
    $cityCount = $cities->count();

    $pacerAvatarUrl = function ($pacer) {
        $avatar = $pacer->user?->avatar;
        if ($avatar) {
            if (str_starts_with($avatar, 'http')) {
                return $avatar;
            }
            if (str_starts_with($avatar, '/storage')) {
                return asset(ltrim($avatar, '/'));
            }
            return asset('storage/' . $avatar);
        }
        return ($pacer->user?->gender === 'female')
            ? asset('images/default-female.svg')
            : asset('images/default-male.svg');
    };

    $itemListElements = $pacers->take(30)->values()->map(function ($pacer, $idx) {
        return [
            '@type' => 'ListItem',
            'position' => $idx + 1,
            'item' => [
                '@type' => 'Person',
                'name' => $pacer->user?->name ?? $pacer->nickname ?? 'Pacer Ruang Lari',
                'url' => route('pacer.show', $pacer->seo_slug),
                'jobTitle' => 'Running Pacer (' . ($pacer->category ?: 'Road Run') . ')',
            ],
        ];
    })->all();
@endphp

@section('title', $seoTitle)
@section('meta_title', $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords', 'pacer lari indonesia, jasa pacer marathon, cari pacer half marathon, pacer 10k, booking pacer lari, komunitas pacer ruang lari')
@section('canonical_url', $seoCanonical)

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .rl-pacers-page {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .rl-pacers-page h1,
    .rl-pacers-page h2,
    .rl-pacers-page h3,
    .rl-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .rl-mono {
        font-family: 'JetBrains Mono', monospace;
        font-variant-numeric: tabular-nums;
    }
    .rl-faq-item summary::-webkit-details-marker {
        display: none;
    }
    .rl-faq-item[open] .rl-faq-chevron {
        transform: rotate(180deg);
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "CollectionPage",
  "name": "{{ $seoTitle }}",
  "description": "{{ $seoDesc }}",
  "url": "{{ $seoCanonical }}",
  "mainEntity": {
    "@@type": "ItemList",
    "numberOfItems": {{ $pacers->count() }},
    "itemListElement": @json($itemListElements)
  }
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    {
      "@@type": "Question",
      "name": "Apa tugas pacer dalam event lari?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Pacer membantu pelari menjaga ritme kecepatan (pace) yang stabil sepanjang lomba agar finis sesuai target waktu pada kategori 10K, Half Marathon, maupun Full Marathon."
      }
    },
    {
      "@@type": "Question",
      "name": "Bagaimana cara mencari dan memilih pacer di Ruang Lari?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Gunakan filter kota, kategori jarak (10K, HM 21K, FM 42K), target pace, dan catatan personal best (PB) untuk menemukan pacer yang sesuai dengan target lomba Anda."
      }
    },
    {
      "@@type": "Question",
      "name": "Bagaimana cara mendaftar menjadi pacer resmi di Ruang Lari?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Pelari berpengalaman dapat mendaftar melalui halaman Daftar Pacer dengan melengkapi biodata, kota domisili, target pace, serta catatan Personal Best resmi."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div id="pacer-directory" class="rl-pacers-page min-h-screen bg-slate-950 text-slate-100 pt-0">

    {{-- Hero Section (Clean, Compact, Zero Blobs/Glassmorphism) --}}
    <section class="border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 sm:pt-12 pb-8 sm:pb-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-end">
                <div class="lg:col-span-8">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl text-white leading-[1.1]">
                        Direktori Pacer Lari Indonesia
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 mt-3 max-w-2xl leading-relaxed">
                        Temukan pacer berpengalaman untuk menjaga ritme lari dan membantu Anda mencapai target waktu terbaik di kategori 10K, Half Marathon, hingga Full Marathon.
                    </p>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md">
                        <a href="#daftar-pacer"
                           class="inline-flex items-center justify-center h-11 px-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                            Cari Pacer Sekarang
                        </a>
                        <a href="{{ route('pacer.register') }}"
                           class="inline-flex items-center justify-center h-11 px-5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-semibold transition duration-150">
                            + Daftar Jadi Pacer
                        </a>
                    </div>
                </div>

                {{-- Factual Stats Strip --}}
                <div class="lg:col-span-4 grid grid-cols-3 gap-3 bg-slate-900 border border-slate-800 rounded-lg p-4">
                    <div>
                        <div class="rl-mono text-lg sm:text-xl font-bold text-white">{{ $pacers->count() }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Total Pacer</div>
                    </div>
                    <div class="border-l border-slate-800 pl-3">
                        <div class="rl-mono text-lg sm:text-xl font-bold text-neon">{{ $verifiedCount }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Terverifikasi</div>
                    </div>
                    <div class="border-l border-slate-800 pl-3">
                        <div class="rl-mono text-lg sm:text-xl font-bold text-white">{{ $cityCount }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Kota</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Directory Section --}}
    <section id="daftar-pacer" class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        {{-- Mobile Search & Filter Trigger --}}
        <div class="md:hidden mb-5 flex items-center gap-2.5">
            <div class="relative flex-1">
                <input id="pacerSearchMobile"
                       type="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama atau panggilan pacer..."
                       class="w-full h-10 px-3.5 rounded-md bg-slate-900 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
            </div>
            <button type="button" id="btnOpenMobileFilter"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white whitespace-nowrap shrink-0">
                Filter <span id="mobileFilterBadge" class="rl-mono ml-1.5 hidden px-1.5 py-0.5 rounded bg-neon text-slate-950 text-[10px] font-bold">0</span>
            </button>
        </div>

        <div class="flex flex-col md:flex-row gap-7 items-start">

            {{-- Desktop Sidebar Filters --}}
            <aside aria-label="Filter direktori pacer" class="hidden md:block w-72 shrink-0 bg-slate-900 border border-slate-800 rounded-lg p-5 space-y-5 sticky top-24">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h2 class="text-sm text-white">Filter Pacer</h2>
                    <button type="button" id="btnResetDesktop" class="hidden text-xs font-semibold text-slate-400 hover:text-neon transition">
                        Reset
                    </button>
                </div>

                <div>
                    <label for="pacerSearchDesktop" class="block text-xs font-semibold text-slate-300 mb-1.5">Cari Nama</label>
                    <input id="pacerSearchDesktop"
                           type="search"
                           value="{{ request('search') }}"
                           placeholder="Nama atau panggilan..."
                           class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                </div>

                <div>
                    <span class="block text-xs font-semibold text-slate-300 mb-2">Kategori Jarak</span>
                    <div class="grid grid-cols-2 gap-1.5" id="desktopCategoryGroup">
                        @foreach(['All' => 'Semua', '10K' => '10K', 'HM (21K)' => 'HM (21K)', 'FM (42K)' => 'FM (42K)'] as $catVal => $catLabel)
                            @php $isActiveCat = request('category', 'All') === $catVal; @endphp
                            <button type="button"
                                    data-category="{{ $catVal }}"
                                    class="kp-cat-btn h-9 px-2.5 rounded-md border text-xs font-semibold transition duration-150 {{ $isActiveCat ? 'bg-neon text-slate-950 border-neon font-bold' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700 hover:text-white' }}">
                                {{ $catLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="pacerCityDesktop" class="block text-xs font-semibold text-slate-300 mb-1.5">Kota Domisili</label>
                    <select id="pacerCityDesktop"
                            class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pacerPaceDesktop" class="block text-xs font-semibold text-slate-300 mb-1.5">Target Pace</label>
                    <input id="pacerPaceDesktop"
                           type="text"
                           value="{{ request('pace') }}"
                           placeholder="Contoh: 5:30"
                           class="rl-mono w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                </div>

                <details class="bg-slate-950 border border-slate-800 rounded-md p-3">
                    <summary class="cursor-pointer list-none flex items-center justify-between text-xs font-semibold text-slate-300">
                        <span>Batas Maksimal PB</span>
                        <span class="text-slate-500 text-[11px]">Opsional</span>
                    </summary>
                    <div class="mt-3 space-y-2.5">
                        @foreach(['pb_5k' => '5K PB', 'pb_10k' => '10K PB', 'pb_hm' => 'Half Marathon', 'pb_fm' => 'Full Marathon'] as $pbKey => $pbLabel)
                            <div>
                                <label for="desktop_{{ $pbKey }}" class="block text-[11px] text-slate-400 mb-1">{{ $pbLabel }}</label>
                                <input id="desktop_{{ $pbKey }}"
                                       data-pb-field="{{ $pbKey }}"
                                       type="text"
                                       value="{{ request($pbKey) }}"
                                       placeholder="00:00:00"
                                       class="rl-mono kp-pb-input w-full h-9 px-2.5 rounded-md bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-neon">
                            </div>
                        @endforeach
                    </div>
                </details>
            </aside>

            {{-- Results Column --}}
            <div class="flex-1 w-full min-w-0">
                <div class="flex items-center justify-between gap-3 pb-4 mb-5 border-b border-slate-800">
                    <p class="text-sm text-slate-300">
                        Menampilkan <span id="pacerResultCount" class="rl-mono font-bold text-white">{{ $pacers->count() }}</span> pacer
                    </p>
                    <span id="pacerLoadingNote" class="hidden text-xs text-neon font-medium">Memuat data...</span>
                </div>

                {{-- SSR Pacer Grid (Crawlable by Googlebot immediately) --}}
                <div id="pacerGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 {{ $pacers->isEmpty() ? 'hidden' : '' }}">
                    @foreach($pacers as $pacer)
                        <article class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden flex flex-col justify-between hover:border-slate-700 transition duration-150">
                            <div>
                                {{-- Clean Top Status Strip --}}
                                <div class="px-4 py-2.5 bg-slate-950 border-b border-slate-800 flex items-center justify-between gap-2">
                                    <span class="rl-mono text-[11px] font-bold px-2 py-0.5 rounded {{ $pacer->verified ? 'bg-neon text-slate-950' : 'bg-slate-800 text-slate-300' }}">
                                        {{ $pacer->verified ? 'Verified' : 'Pacer' }}
                                    </span>
                                    <span class="rl-mono text-[11px] font-semibold text-slate-300">
                                        {{ $pacer->category ?: 'Road Run' }}
                                    </span>
                                </div>

                                {{-- Unobstructed Portrait --}}
                                <a href="{{ route('pacer.show', $pacer->seo_slug) }}" class="block aspect-[4/3] bg-slate-800 border-b border-slate-800 overflow-hidden">
                                    <img src="{{ $pacerAvatarUrl($pacer) }}"
                                         alt="Pacer {{ $pacer->user?->name ?? 'Ruang Lari' }} - {{ $pacer->category }}"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='{{ asset('images/default-male.svg') }}';"
                                         class="w-full h-full object-cover object-center">
                                </a>

                                {{-- Card Body --}}
                                <div class="p-5 pb-3">
                                    <div class="flex items-end justify-between gap-3 pb-3.5 mb-3.5 border-b border-slate-800">
                                        <div>
                                            <div class="text-[11px] text-slate-400">Target Pace</div>
                                            <div class="rl-mono text-2xl font-bold text-white mt-0.5">{{ $pacer->pace ?: '-' }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[11px] text-slate-400">Pengalaman</div>
                                            <div class="rl-mono text-sm font-semibold text-slate-200 mt-0.5">{{ (int) $pacer->total_races }} Race</div>
                                        </div>
                                    </div>

                                    <h3 class="text-base sm:text-lg text-white truncate">
                                        <a href="{{ route('pacer.show', $pacer->seo_slug) }}" class="hover:text-neon transition duration-150">
                                            {{ $pacer->user?->name ?? 'Pacer' }}
                                        </a>
                                    </h3>

                                    @if($pacer->nickname)
                                        <div class="text-xs font-medium text-slate-300 mt-0.5 truncate">"{{ $pacer->nickname }}"</div>
                                    @endif

                                    <div class="text-xs text-slate-400 mt-2 truncate">
                                        {{ $pacer->user?->city?->name ?? 'Indonesia' }}
                                    </div>
                                </div>
                            </div>

                            <div class="px-5 pb-5 pt-2">
                                <a href="{{ route('pacer.show', $pacer->seo_slug) }}"
                                   class="w-full inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150">
                                    Lihat Profil &amp; Booking
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Empty State --}}
                <div id="pacerEmptyState" class="{{ $pacers->isEmpty() ? '' : 'hidden' }} bg-slate-900 border border-slate-800 rounded-lg p-10 text-center">
                    <h3 class="text-lg text-white">Pacer Tidak Ditemukan</h3>
                    <p class="text-sm text-slate-300 mt-1.5 max-w-md mx-auto">
                        Tidak ada pacer yang sesuai dengan filter pencarian Anda. Coba reset filter atau ubah target pace.
                    </p>
                    <button type="button" id="btnResetEmpty"
                            class="mt-5 inline-flex items-center justify-center h-10 px-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- SEO City Directory & FAQ Section --}}
    <section class="border-t border-slate-800 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-12 space-y-10">
            @if($cities->isNotEmpty())
            <div>
                <h2 class="text-lg sm:text-xl text-white mb-3">Cari Pacer Berdasarkan Kota</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cities as $city)
                        <a href="{{ route('pacer.index', ['city_id' => $city->id]) }}"
                           class="inline-flex items-center h-9 px-3.5 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-medium text-slate-200 hover:text-white transition duration-150">
                            Pacer {{ $city->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <h2 class="text-lg sm:text-xl text-white mb-4">Pertanyaan Umum Seputar Pacer</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <details class="rl-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm font-semibold text-white">
                            <span>Apa tugas pacer saat lomba lari?</span>
                            <svg class="rl-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <p class="text-xs sm:text-sm text-slate-300 mt-3 leading-relaxed">
                            Pacer menjaga kecepatan lari secara konsisten sesuai target waktu finis sehingga pelari tidak kehabisan tenaga di awal lomba.
                        </p>
                    </details>

                    <details class="rl-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm font-semibold text-white">
                            <span>Bagaimana cara memilih pacer yang tepat?</span>
                            <svg class="rl-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <p class="text-xs sm:text-sm text-slate-300 mt-3 leading-relaxed">
                            Cocokkan target pace lomba, kategori jarak (10K, HM 21K, FM 42K), serta riwayat Personal Best pacer pada halaman profil masing-masing.
                        </p>
                    </details>

                    <details class="rl-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm font-semibold text-white">
                            <span>Apakah komunitas/EO bisa booking pacer?</span>
                            <svg class="rl-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <p class="text-xs sm:text-sm text-slate-300 mt-3 leading-relaxed">
                            Ya. Buka profil pacer pilihan Anda lalu ajukan booking langsung untuk jadwal latihan bersama maupun event resmi.
                        </p>
                    </details>
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile Filter Drawer (Opaque, Clean, No Blur) --}}
    <div id="mobileFilterDrawer" class="hidden fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Filter pacer">
        <div id="mobileFilterBackdrop" class="absolute inset-0 bg-black/75"></div>
        <div class="absolute inset-x-0 bottom-0 max-h-[88dvh] bg-slate-900 border-t border-slate-800 rounded-t-lg p-5 flex flex-col">
            <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-800 shrink-0">
                <h3 class="text-base text-white">Filter Pacer</h3>
                <button type="button" id="btnCloseMobileFilter"
                        class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-200">
                    Tutup
                </button>
            </div>

            <div class="overflow-y-auto flex-1 space-y-4 pr-1">
                <div>
                    <span class="block text-xs font-semibold text-slate-300 mb-2">Kategori Jarak</span>
                    <div class="grid grid-cols-2 gap-2" id="mobileCategoryGroup">
                        @foreach(['All' => 'Semua', '10K' => '10K', 'HM (21K)' => 'HM (21K)', 'FM (42K)' => 'FM (42K)'] as $catVal => $catLabel)
                            @php $isActiveCat = request('category', 'All') === $catVal; @endphp
                            <button type="button"
                                    data-category="{{ $catVal }}"
                                    class="kp-cat-btn-mobile h-10 px-3 rounded-md border text-xs font-semibold transition {{ $isActiveCat ? 'bg-neon text-slate-950 border-neon font-bold' : 'bg-slate-950 text-slate-300 border-slate-800' }}">
                                {{ $catLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="pacerCityMobile" class="block text-xs font-semibold text-slate-300 mb-1.5">Kota Domisili</label>
                    <select id="pacerCityMobile"
                            class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pacerPaceMobile" class="block text-xs font-semibold text-slate-300 mb-1.5">Target Pace</label>
                    <input id="pacerPaceMobile"
                           type="text"
                           value="{{ request('pace') }}"
                           placeholder="Contoh: 5:30"
                           class="rl-mono w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400">
                </div>

                <div>
                    <span class="block text-xs font-semibold text-slate-300 mb-2">Batas Maksimal PB (Opsional)</span>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach(['pb_5k' => '5K PB', 'pb_10k' => '10K PB', 'pb_hm' => 'Half Marathon', 'pb_fm' => 'Full Marathon'] as $pbKey => $pbLabel)
                            <div>
                                <label for="mobile_{{ $pbKey }}" class="block text-[11px] text-slate-400 mb-1">{{ $pbLabel }}</label>
                                <input id="mobile_{{ $pbKey }}"
                                       data-pb-field="{{ $pbKey }}"
                                       type="text"
                                       value="{{ request($pbKey) }}"
                                       placeholder="00:00:00"
                                       class="rl-mono kp-pb-input-mobile w-full h-9 px-2.5 rounded-md bg-slate-950 border border-slate-700 text-xs text-white placeholder-slate-500">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-800 grid grid-cols-2 gap-2.5 shrink-0">
                <button type="button" id="btnResetMobile"
                        class="h-10 rounded-md bg-slate-800 border border-slate-700 text-xs font-semibold text-white">
                    Reset
                </button>
                <button type="button" id="btnApplyMobileFilter"
                        class="h-10 rounded-md bg-neon text-slate-950 text-xs font-bold">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoint = @json(route('pacer.index'));
    const storageBase = @json(asset('storage'));
    const defaultMale = @json(asset('images/default-male.svg'));
    const defaultFemale = @json(asset('images/default-female.svg'));

    const state = {
        search: @json((string) request('search', '')),
        city_id: @json((string) request('city_id', '')),
        category: @json((string) request('category', 'All')),
        pace: @json((string) request('pace', '')),
        pb_5k: @json((string) request('pb_5k', '')),
        pb_10k: @json((string) request('pb_10k', '')),
        pb_hm: @json((string) request('pb_hm', '')),
        pb_fm: @json((string) request('pb_fm', '')),
    };

    const pacerGrid = document.getElementById('pacerGrid');
    const emptyState = document.getElementById('pacerEmptyState');
    const countLabel = document.getElementById('pacerResultCount');
    const loadingNote = document.getElementById('pacerLoadingNote');
    const btnResetDesktop = document.getElementById('btnResetDesktop');
    const mobileBadge = document.getElementById('mobileFilterBadge');

    const drawer = document.getElementById('mobileFilterDrawer');
    const btnOpenDrawer = document.getElementById('btnOpenMobileFilter');
    const btnCloseDrawer = document.getElementById('btnCloseMobileFilter');
    const drawerBackdrop = document.getElementById('mobileFilterBackdrop');
    const btnApplyMobile = document.getElementById('btnApplyMobileFilter');

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }

    function resolveAvatar(pacer) {
        const avatar = pacer && pacer.user ? pacer.user.avatar : null;
        if (avatar) {
            if (avatar.startsWith('http')) return avatar;
            if (avatar.startsWith('/storage')) return avatar;
            return storageBase + '/' + avatar.replace(/^\/+/, '');
        }
        return (pacer && pacer.user && pacer.user.gender === 'female') ? defaultFemale : defaultMale;
    }

    function activeFilterCount() {
        let count = 0;
        if (state.search) count++;
        if (state.city_id) count++;
        if (state.category && state.category !== 'All') count++;
        if (state.pace) count++;
        ['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm'].forEach(k => { if (state[k]) count++; });
        return count;
    }

    function syncInputs() {
        const sDesk = document.getElementById('pacerSearchDesktop');
        const sMob = document.getElementById('pacerSearchMobile');
        const cDesk = document.getElementById('pacerCityDesktop');
        const cMob = document.getElementById('pacerCityMobile');
        const pDesk = document.getElementById('pacerPaceDesktop');
        const pMob = document.getElementById('pacerPaceMobile');

        if (sDesk) sDesk.value = state.search;
        if (sMob) sMob.value = state.search;
        if (cDesk) cDesk.value = state.city_id;
        if (cMob) cMob.value = state.city_id;
        if (pDesk) pDesk.value = state.pace;
        if (pMob) pMob.value = state.pace;

        ['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm'].forEach(function (k) {
            const dEl = document.getElementById('desktop_' + k);
            const mEl = document.getElementById('mobile_' + k);
            if (dEl) dEl.value = state[k];
            if (mEl) mEl.value = state[k];
        });

        document.querySelectorAll('.kp-cat-btn, .kp-cat-btn-mobile').forEach(function (btn) {
            const active = btn.getAttribute('data-category') === state.category;
            btn.classList.toggle('bg-neon', active);
            btn.classList.toggle('text-slate-950', active);
            btn.classList.toggle('border-neon', active);
            btn.classList.toggle('font-bold', active);
            btn.classList.toggle('bg-slate-950', !active);
            btn.classList.toggle('text-slate-300', !active);
            btn.classList.toggle('border-slate-800', !active);
        });

        const cnt = activeFilterCount();
        if (btnResetDesktop) btnResetDesktop.classList.toggle('hidden', cnt === 0);
        if (mobileBadge) {
            mobileBadge.textContent = String(cnt);
            mobileBadge.classList.toggle('hidden', cnt === 0);
        }
    }

    function renderGrid(pacers) {
        if (!pacerGrid || !emptyState) return;
        if (countLabel) countLabel.textContent = String(pacers.length);

        if (!pacers.length) {
            pacerGrid.innerHTML = '';
            pacerGrid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        pacerGrid.classList.remove('hidden');

        pacerGrid.innerHTML = pacers.map(function (p) {
            const name = escapeHtml(p.user && p.user.name ? p.user.name : 'Pacer');
            const slug = encodeURIComponent(p.seo_slug || '');
            const city = escapeHtml(p.user && p.user.city && p.user.city.name ? p.user.city.name : 'Indonesia');
            const category = escapeHtml(p.category || 'Road Run');
            const pace = escapeHtml(p.pace || '-');
            const races = parseInt(p.total_races || 0, 10);
            const verified = !!p.verified;
            const nickname = p.nickname ? `<div class="text-xs font-medium text-slate-300 mt-0.5 truncate">"${escapeHtml(p.nickname)}"</div>` : '';
            const avatar = escapeHtml(resolveAvatar(p));

            return `
                <article class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden flex flex-col justify-between hover:border-slate-700 transition duration-150">
                    <div>
                        <div class="px-4 py-2.5 bg-slate-950 border-b border-slate-800 flex items-center justify-between gap-2">
                            <span class="rl-mono text-[11px] font-bold px-2 py-0.5 rounded ${verified ? 'bg-neon text-slate-950' : 'bg-slate-800 text-slate-300'}">
                                ${verified ? 'Verified' : 'Pacer'}
                            </span>
                            <span class="rl-mono text-[11px] font-semibold text-slate-300">${category}</span>
                        </div>
                        <a href="/pacer/${slug}" class="block aspect-[4/3] bg-slate-800 border-b border-slate-800 overflow-hidden">
                            <img src="${avatar}" alt="Pacer ${name}" loading="lazy" onerror="this.onerror=null; this.src='${defaultMale}';" class="w-full h-full object-cover object-center">
                        </a>
                        <div class="p-5 pb-3">
                            <div class="flex items-end justify-between gap-3 pb-3.5 mb-3.5 border-b border-slate-800">
                                <div>
                                    <div class="text-[11px] text-slate-400">Target Pace</div>
                                    <div class="rl-mono text-2xl font-bold text-white mt-0.5">${pace}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] text-slate-400">Pengalaman</div>
                                    <div class="rl-mono text-sm font-semibold text-slate-200 mt-0.5">${races} Race</div>
                                </div>
                            </div>
                            <h3 class="text-base sm:text-lg text-white truncate">
                                <a href="/pacer/${slug}" class="hover:text-neon transition duration-150">${name}</a>
                            </h3>
                            ${nickname}
                            <div class="text-xs text-slate-400 mt-2 truncate">${city}</div>
                        </div>
                    </div>
                    <div class="px-5 pb-5 pt-2">
                        <a href="/pacer/${slug}" class="w-full inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150">
                            Lihat Profil &amp; Booking
                        </a>
                    </div>
                </article>
            `;
        }).join('');
    }

    let fetchTimer = null;
    async function fetchPacers() {
        syncInputs();
        if (loadingNote) loadingNote.classList.remove('hidden');

        const params = new URLSearchParams();
        Object.keys(state).forEach(function (k) {
            if (state[k] && !(k === 'category' && state[k] === 'All')) {
                params.append(k, state[k]);
            }
        });

        const qs = params.toString();
        const url = qs ? `${endpoint}?${qs}` : endpoint;

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                renderGrid(data.pacers || []);
                window.history.replaceState({}, '', url);
            }
        } catch (err) {
            console.error('Error loading pacers:', err);
        } finally {
            if (loadingNote) loadingNote.classList.add('hidden');
        }
    }

    function scheduleFetch() {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(fetchPacers, 350);
    }

    function resetAllFilters() {
        state.search = '';
        state.city_id = '';
        state.category = 'All';
        state.pace = '';
        state.pb_5k = '';
        state.pb_10k = '';
        state.pb_hm = '';
        state.pb_fm = '';
        fetchPacers();
    }

    // Bind Search
    ['pacerSearchDesktop', 'pacerSearchMobile'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function () {
                state.search = el.value;
                scheduleFetch();
            });
        }
    });

    // Bind Category
    document.querySelectorAll('.kp-cat-btn, .kp-cat-btn-mobile').forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.category = btn.getAttribute('data-category') || 'All';
            fetchPacers();
        });
    });

    // Bind City
    ['pacerCityDesktop', 'pacerCityMobile'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                state.city_id = el.value;
                fetchPacers();
            });
        }
    });

    // Bind Pace
    ['pacerPaceDesktop', 'pacerPaceMobile'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function () {
                state.pace = el.value;
                scheduleFetch();
            });
        }
    });

    // Bind PB inputs
    document.querySelectorAll('.kp-pb-input, .kp-pb-input-mobile').forEach(function (el) {
        el.addEventListener('input', function () {
            const key = el.getAttribute('data-pb-field');
            if (key) {
                state[key] = el.value;
                scheduleFetch();
            }
        });
    });

    // Reset buttons
    ['btnResetDesktop', 'btnResetMobile', 'btnResetEmpty'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', resetAllFilters);
    });

    // Mobile drawer
    function setDrawer(open) {
        if (!drawer) return;
        drawer.classList.toggle('hidden', !open);
        document.body.style.overflow = open ? 'hidden' : '';
    }
    if (btnOpenDrawer) btnOpenDrawer.addEventListener('click', () => setDrawer(true));
    if (btnCloseDrawer) btnCloseDrawer.addEventListener('click', () => setDrawer(false));
    if (drawerBackdrop) drawerBackdrop.addEventListener('click', () => setDrawer(false));
    if (btnApplyMobile) btnApplyMobile.addEventListener('click', () => setDrawer(false));

    syncInputs();
});
</script>
@endpush
