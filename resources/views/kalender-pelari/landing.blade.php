@extends('layouts.pacerhub', ['hideFooter' => false, 'hideChat' => true, 'hideNav' => false])

@section('title', $pageMetaTitle)
@section('meta_title', $pageMetaTitle)
@section('meta_description', $pageMetaDescription)

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .kp-landing {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .kp-landing h1,
    .kp-landing h2,
    .kp-landing h3,
    .kp-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .kp-mono {
        font-family: 'JetBrains Mono', monospace;
        font-variant-numeric: tabular-nums;
    }
    .kp-faq-item summary::-webkit-details-marker {
        display: none;
    }
    .kp-faq-item[open] .kp-faq-chevron {
        transform: rotate(180deg);
    }
</style>
@endpush

@push('structured_data')
<meta name="keywords" content="kalender pelari {{ $nextYear }}, buat kalender lari custom, kalender meja A4 lari, kalender dinding A3 pelari, template kalender strava, kalender libur nasional indonesia {{ $nextYear }}, cetak kalender pdf 300 dpi">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/kalender-pelari') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url('/kalender-pelari') }}">
<meta property="og:title" content="{{ $pageMetaTitle }}">
<meta property="og:description" content="{{ $pageMetaDescription }}">
<meta property="og:image" content="{{ asset('images/kalender-pelari/bg/788438538_18142926247584739_300127733191259686_n.jpg') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageMetaTitle }}">
<meta name="twitter:description" content="{{ $pageMetaDescription }}">
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebApplication",
  "name": "Kalender Pelari Ruang Lari",
  "url": "{{ url('/kalender-pelari') }}",
  "applicationCategory": "DesignApplication",
  "operatingSystem": "Web Browser",
  "description": "{{ $pageMetaDescription }}",
  "offers": {
    "@@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Export PDF 300 DPI siap cetak ukuran A4 dan A3",
    "Format 1 bulan, 2 bulan, dan 4 bulan per lembar",
    "Sinkronisasi tanggal merah dan cuti bersama resmi Indonesia",
    "Penanda jadwal race lari Indonesia dan World Marathon Majors",
    "QR Code tautan Strava atau galeri foto pelari"
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    {
      "@@type": "Question",
      "name": "Apakah kalender lari ini bisa langsung dicetak ke percetakan?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Ya. File diekspor dalam format PDF beresolusi 300 DPI pada rasio A4 Landscape untuk kalender meja maupun A3 Portrait untuk kalender dinding, lengkap dengan 13 halaman termasuk cover depan."
      }
    },
    {
      "@@type": "Question",
      "name": "Apakah tanggal merah Indonesia sudah tersedia otomatis?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Ya. Data tanggal libur nasional, cuti bersama, serta jadwal lomba lari populer di Indonesia dan luar negeri otomatis tertanam pada penanggalan."
      }
    },
    {
      "@@type": "Question",
      "name": "Apakah foto pribadi dan teks desain aman?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Semua foto yang Anda unggah diproses langsung di browser untuk penyusunan tata letak dan ekspor PDF."
      }
    }
  ]
}
</script>
@endpush

@section('content')
@php
    $heroPhoto = 'images/kalender-pelari/bg/788438538_18142926247584739_300127733191259686_n.jpg';

    $templateVisuals = [
        'bold' => [
            'photo' => 'images/kalender-pelari/bg/788438538_18142926247584739_300127733191259686_n.jpg',
            'tag' => 'A4 / A3',
            'barClass' => 'bg-neon text-slate-950',
            'label' => 'JAN ' . $nextYear,
            'stat' => '42.2 KM',
        ],
        'strava' => [
            'photo' => 'images/kalender-pelari/bg/815920233_18619567672013478_7707120302547332417_n.jpg',
            'tag' => 'Telemetri',
            'barClass' => 'bg-strava text-white',
            'label' => 'PACE 4:45',
            'stat' => '184 KM/BLN',
        ],
        'classic' => [
            'photo' => 'images/kalender-pelari/bg/819207920_18620464486013478_340093240344695805_n.jpg',
            'tag' => 'Editorial',
            'barClass' => 'bg-slate-800 text-white border-b border-slate-700',
            'label' => '21.1 KM',
            'stat' => 'RACE DAY',
        ],
        'night' => [
            'photo' => 'images/kalender-pelari/bg/825352127_18622491895013478_5874230830227099846_n.jpg',
            'tag' => 'Kontras Gelap',
            'barClass' => 'bg-slate-950 text-neon border-b border-slate-800',
            'label' => 'NIGHT RUN',
            'stat' => '10.0 KM',
        ],
        'badge' => [
            'photo' => 'images/kalender-pelari/bg/830540436_18146023228584739_537799408712608694_n.jpg',
            'tag' => 'Finisher',
            'barClass' => 'bg-slate-800 text-neon border-b border-slate-700',
            'label' => 'FINISHER',
            'stat' => 'MEDAL LOG',
        ],
        'topography' => [
            'photo' => 'images/kalender-pelari/bg/840363425_18625537549013478_2717575911219471235_n.jpg',
            'tag' => 'Trail & Elevasi',
            'barClass' => 'bg-slate-900 text-slate-100 border-b border-slate-700',
            'label' => 'ULTRA 50K',
            'stat' => '+1,420M EG',
        ],
    ];

    $janDays = [
        ['d' => '', 'm' => false], ['d' => '', 'm' => false], ['d' => '', 'm' => false],
        ['d' => '1', 'm' => 'red'], ['d' => '2', 'm' => false], ['d' => '3', 'm' => false], ['d' => '4', 'm' => 'red'],
        ['d' => '5', 'm' => false], ['d' => '6', 'm' => false], ['d' => '7', 'm' => false], ['d' => '8', 'm' => false],
        ['d' => '9', 'm' => false], ['d' => '10', 'm' => false], ['d' => '11', 'm' => 'race'],
        ['d' => '12', 'm' => false], ['d' => '13', 'm' => false], ['d' => '14', 'm' => false], ['d' => '15', 'm' => false],
        ['d' => '16', 'm' => false], ['d' => '17', 'm' => false], ['d' => '18', 'm' => 'red'],
        ['d' => '19', 'm' => false], ['d' => '20', 'm' => false], ['d' => '21', 'm' => false], ['d' => '22', 'm' => false],
        ['d' => '23', 'm' => false], ['d' => '24', 'm' => false], ['d' => '25', 'm' => 'race'],
        ['d' => '26', 'm' => false], ['d' => '27', 'm' => false], ['d' => '28', 'm' => false], ['d' => '29', 'm' => 'red'],
        ['d' => '30', 'm' => false], ['d' => '31', 'm' => false], ['d' => '', 'm' => false],
    ];
@endphp

<div id="top" class="kp-landing bg-slate-950 text-slate-100 pt-0">
    {{-- Section 1: Hero & Interactive Specimen --}}
    <section id="pratinjau" class="border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 sm:pt-14 pb-10 sm:pb-14 lg:pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                {{-- Left Column --}}
                <div class="lg:col-span-6">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl text-white leading-[1.1] mb-4">
                        Buat Kalender Lari {{ $nextYear }} dan Cetak PDF Presisi
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6 max-w-xl">
                        Susun foto finis terbaikmu ke dalam tata letak kalender meja A4 atau kalender dinding A3. Sudah dilengkapi tanggal merah resmi Indonesia, penanda lomba lari nasional, dan QR Code profil Strava dalam 13 halaman siap cetak.
                    </p>

                    {{-- Symmetric Equal-Width Equal-Height CTAs --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md mb-4">
                        <a href="{{ $studioUrl }}"
                           class="inline-flex items-center justify-center h-11 px-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                            Mulai Desain
                        </a>
                        <a href="{{ $wizardUrl }}"
                           class="inline-flex items-center justify-center h-11 px-5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-semibold transition duration-150">
                            Wizard Cepat
                        </a>
                    </div>
                    <div class="mb-8">
                        <a href="{{ route('kalender-pelari.my-projects') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-300 hover:text-neon transition duration-150">
                            <span>Buka Proyek Kalender Tersimpan</span>
                            <span aria-hidden="true">-&gt;</span>
                        </a>
                    </div>

                    {{-- Clean Spec Metrics Strip --}}
                    <div class="grid grid-cols-3 gap-3 pt-6 border-t border-slate-800">
                        <div>
                            <div class="kp-mono text-base sm:text-lg font-bold text-white">300 DPI</div>
                            <div class="text-xs text-slate-400 mt-0.5">Output PDF Cetak</div>
                        </div>
                        <div>
                            <div class="kp-mono text-base sm:text-lg font-bold text-white">13 Hal</div>
                            <div class="text-xs text-slate-400 mt-0.5">Cover & 12 Bulan</div>
                        </div>
                        <div>
                            <div class="kp-mono text-base sm:text-lg font-bold text-white">Gratis</div>
                            <div class="text-xs text-slate-400 mt-0.5">Tanpa Registrasi</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Interactive Live Calendar Specimen --}}
                <div class="lg:col-span-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-800">
                            <div>
                                <div class="text-xs font-semibold text-white">Simulasi Tata Letak</div>
                                <div id="kpSpecimenMeta" class="kp-mono text-[11px] text-slate-400 mt-0.5">Format 1 Bulan · Meja A4 Landscape</div>
                            </div>

                            {{-- Symmetric 3-Tab Format Switcher --}}
                            <div class="grid grid-cols-3 gap-1.5 bg-slate-950 p-1 rounded-md border border-slate-800" role="tablist" aria-label="Pilih format halaman kalender">
                                <button type="button"
                                        data-specimen-tab="1m"
                                        aria-selected="true"
                                        class="kp-tab-btn h-8 px-2.5 rounded-md text-xs font-bold bg-neon text-slate-950 transition duration-150 whitespace-nowrap">
                                    1 Bulan
                                </button>
                                <button type="button"
                                        data-specimen-tab="2m"
                                        aria-selected="false"
                                        class="kp-tab-btn h-8 px-2.5 rounded-md text-xs font-semibold bg-slate-950 text-slate-300 hover:text-white transition duration-150 whitespace-nowrap">
                                    2 Bulan
                                </button>
                                <button type="button"
                                        data-specimen-tab="4m"
                                        aria-selected="false"
                                        class="kp-tab-btn h-8 px-2.5 rounded-md text-xs font-semibold bg-slate-950 text-slate-300 hover:text-white transition duration-150 whitespace-nowrap">
                                    4 Bulan
                                </button>
                            </div>
                        </div>

                        {{-- Specimen Sheet --}}
                        <div class="bg-slate-950 border border-slate-800 rounded-lg overflow-hidden">
                            {{-- Top Photo Area (Click or Drop Photo) --}}
                            <div id="kpSpecimenPhotoDrop" class="relative aspect-[16/9] bg-slate-800 overflow-hidden border-b border-slate-800 cursor-pointer group" title="Klik atau seret foto lari Anda ke sini">
                                <img id="kpSpecimenPhotoImg"
                                     src="{{ asset($heroPhoto) }}"
                                     data-default-src="{{ asset($heroPhoto) }}"
                                     alt="Contoh foto pelari pada tata letak kalender"
                                     class="w-full h-full object-cover object-center transition duration-150">
                                <div class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded bg-slate-950 border border-slate-800 text-[11px] font-semibold text-slate-200 group-hover:border-neon group-hover:text-neon transition duration-150">
                                    Klik / Seret Foto
                                </div>
                                <div class="absolute inset-x-0 bottom-0 bg-slate-950 border-t border-slate-800 px-3.5 py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div id="kpSpecimenTitle" class="kp-heading text-sm sm:text-base text-white truncate">JANUARI {{ $nextYear }}</div>
                                        <div id="kpSpecimenSubtitle" class="kp-mono text-[11px] text-slate-300 truncate">BOROBUDUR MARATHON · 42.195 KM</div>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-2">
                                        <div class="w-7 h-7 rounded bg-white p-1 flex items-center justify-center" title="Slot QR Code Strava">
                                            <div class="w-full h-full grid grid-cols-3 gap-0.5">
                                                <span class="bg-slate-950 block"></span><span class="bg-white block"></span><span class="bg-slate-950 block"></span>
                                                <span class="bg-white block"></span><span class="bg-slate-950 block"></span><span class="bg-slate-950 block"></span>
                                                <span class="bg-slate-950 block"></span><span class="bg-slate-950 block"></span><span class="bg-white block"></span>
                                            </div>
                                        </div>
                                        <span class="kp-mono text-[10px] font-bold text-slate-300">QR</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Mode 1 Bulan --}}
                            <div id="kpSpecimen1m" class="p-3.5 sm:p-4">
                                <div class="grid grid-cols-7 gap-1 text-center mb-2">
                                    @foreach(['SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB', 'MIN'] as $dayName)
                                        <div class="kp-mono text-[10px] font-bold {{ $dayName === 'MIN' ? 'text-red-400' : 'text-slate-400' }}">{{ $dayName }}</div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-7 gap-1 text-center">
                                    @foreach($janDays as $cell)
                                        @php
                                            $cellClass = 'text-slate-200 bg-slate-900 border-slate-800';
                                            if ($cell['d'] === '') {
                                                $cellClass = 'bg-transparent border-transparent text-transparent';
                                            } elseif ($cell['m'] === 'red') {
                                                $cellClass = 'text-red-400 bg-slate-900 border-slate-800 font-bold';
                                            } elseif ($cell['m'] === 'race') {
                                                $cellClass = 'text-slate-950 bg-neon border-neon font-bold';
                                            }
                                        @endphp
                                        <div class="kp-mono h-7 rounded flex items-center justify-center text-xs border {{ $cellClass }}">
                                            {{ $cell['d'] ?: '·' }}
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-slate-800 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-300">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded bg-neon inline-block"></span>
                                        Jadwal Lomba / Long Run
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded bg-red-500 inline-block"></span>
                                        Libur Nasional Indonesia
                                    </span>
                                </div>
                            </div>

                            {{-- Mode 2 Bulan --}}
                            <div id="kpSpecimen2m" class="hidden p-3.5 sm:p-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach(['JANUARI', 'FEBRUARI'] as $mName)
                                        <div class="bg-slate-900 border border-slate-800 rounded p-2.5">
                                            <div class="kp-mono text-xs font-bold text-white mb-2 flex items-center justify-between">
                                                <span>{{ $mName }} {{ $nextYear }}</span>
                                                <span class="text-neon">6 HAL</span>
                                            </div>
                                            <div class="grid grid-cols-7 gap-1 text-center kp-mono text-[10px] text-slate-400 mb-1">
                                                <span>S</span><span>S</span><span>R</span><span>K</span><span>J</span><span>S</span><span class="text-red-400">M</span>
                                            </div>
                                            <div class="grid grid-cols-7 gap-1 text-center kp-mono text-[10px]">
                                                @for($i = 1; $i <= 28; $i++)
                                                    <span class="py-0.5 rounded {{ $i === 11 || $i === 25 ? 'bg-neon text-slate-950 font-bold' : ($i % 7 === 0 ? 'text-red-400' : 'text-slate-300') }}">{{ $i }}</span>
                                                @endfor
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Mode 4 Bulan --}}
                            <div id="kpSpecimen4m" class="hidden p-3.5 sm:p-4">
                                <div class="grid grid-cols-2 gap-2.5">
                                    @foreach(['JAN', 'FEB', 'MAR', 'APR'] as $qName)
                                        <div class="bg-slate-900 border border-slate-800 rounded p-2">
                                            <div class="kp-mono text-[11px] font-bold text-white mb-1.5 flex items-center justify-between">
                                                <span>{{ $qName }} {{ $nextYear }}</span>
                                                <span class="text-slate-400">Q1/3</span>
                                            </div>
                                            <div class="grid grid-cols-7 gap-0.5 text-center kp-mono text-[9px]">
                                                @for($i = 1; $i <= 21; $i++)
                                                    <span class="py-0.5 rounded {{ $i === 14 ? 'bg-neon text-slate-950 font-bold' : ($i % 7 === 0 ? 'text-red-400' : 'text-slate-300') }}">{{ $i }}</span>
                                                @endfor
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Interactive Local Photo Try-On Bar (Zero Server Upload) --}}
                        <div class="mt-3 pt-3 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <input type="file" id="kpLandingPhotoInput" accept="image/jpeg,image/png,image/webp" class="hidden">
                            <div class="flex items-center gap-2">
                                <button type="button" id="kpUploadPhotoBtn"
                                        class="inline-flex items-center justify-center h-9 px-3.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150 whitespace-nowrap">
                                    + Coba Foto Anda
                                </button>
                                <button type="button" id="kpResetPhotoBtn"
                                        class="hidden inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-xs font-medium text-slate-300 transition duration-150 whitespace-nowrap">
                                    Reset
                                </button>
                            </div>
                            <a id="kpContinueWithPhotoBtn" href="{{ $studioUrl }}"
                               class="hidden inline-flex items-center justify-center h-9 px-4 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-xs font-bold transition duration-150 whitespace-nowrap">
                                Masuk Studio dengan Foto Ini
                            </a>
                            <span id="kpPhotoHint" class="text-[11px] text-slate-400">
                                Diproses lokal di browser, tidak tersimpan di server/filemanager.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: Template Showcase (Authentic Editor Canvas Previews + Load More to Studio Editor) --}}
    <section id="template" class="border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-2xl sm:text-3xl text-white">Pilihan Template Siap Pakai</h2>
                        <span class="kp-mono text-xs px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-neon font-semibold">
                            {{ count($templateHighlights) }} dari {{ $totalTemplatesCount ?? 37 }} Desain
                        </span>
                    </div>
                    <p class="text-sm text-slate-300 mt-1.5 max-w-2xl">
                        Pratinjau asli dari mesin desain studio. Klik salah satu template untuk langsung membuka desain tersebut di editor, atau buka seluruh {{ $totalTemplatesCount ?? 37 }} koleksi desain di Studio Editor.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0 self-start md:self-auto">
                    <div class="inline-flex bg-slate-900 p-1 rounded-md border border-slate-800" role="group" aria-label="Orientasi pratinjau template">
                        <button type="button"
                                data-tpl-layout="wall"
                                class="kp-tpl-layout-btn h-8 px-3 rounded text-xs font-bold bg-neon text-slate-950 transition duration-150 cursor-pointer">
                            Dinding A3+
                        </button>
                        <button type="button"
                                data-tpl-layout="landscape"
                                class="kp-tpl-layout-btn h-8 px-3 rounded text-xs font-semibold text-slate-300 hover:text-white transition duration-150 cursor-pointer">
                            Meja A4
                        </button>
                    </div>
                    <a href="{{ $studioUrl }}"
                       class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-sm font-semibold text-white transition duration-150">
                        Buka 37 Desain di Editor
                    </a>
                </div>
            </div>

            {{-- Hidden state inputs required by the authentic Kalender Pelari canvas engine --}}
            <div class="hidden" aria-hidden="true">
                <input id="page" value="0">
                <input id="name" value="{{ auth()->user()?->name ?? 'RUNNER' }}">
                <input id="year" value="{{ $nextYear }}">
                <input id="accent" value="#f45123">
                <input id="paper" value="#ffffff">
                <input id="template" value="classic">
                <input id="layout" value="wall">
                <input id="monthsPerPage" value="1">
                <input id="font" value="Plus Jakarta Sans">
                <input id="showHolidays" type="checkbox" checked>
                <input id="runningMode" type="checkbox" checked>
                <canvas id="canvas" width="240" height="360"></canvas>
                <span id="status"></span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($templateHighlights as $tpl)
                    <article class="group flex flex-col justify-between bg-slate-900 border border-slate-800 rounded-lg overflow-hidden hover:border-slate-700 transition duration-150">
                        <a href="{{ $studioUrl }}?template={{ $tpl['id'] }}"
                           data-tpl-link="{{ $tpl['id'] }}"
                           class="block focus:outline-none">
                            <div class="bg-slate-950 border-b border-slate-800 p-3 flex items-center justify-center">
                                <canvas class="kp-landing-tpl-canvas w-full h-auto rounded border border-slate-800 bg-slate-900 block transition duration-150 group-hover:border-slate-600"
                                        data-template="{{ $tpl['id'] }}"
                                        width="220"
                                        height="330"
                                        aria-label="Pratinjau template {{ $tpl['name'] }}"></canvas>
                            </div>

                            <div class="p-4 pb-2">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm sm:text-base text-white group-hover:text-neon transition-colors">{{ $tpl['name'] }}</h3>
                                    @if($tpl['id'] === 'poster12m')
                                        <span class="kp-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-neon shrink-0">12 BLN</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-300 mt-1 leading-relaxed line-clamp-2">{{ $tpl['desc'] }}</p>
                            </div>
                        </a>

                        <div class="px-4 pb-4 pt-2">
                            <a href="{{ $studioUrl }}?template={{ $tpl['id'] }}"
                               data-tpl-link="{{ $tpl['id'] }}"
                               class="w-full inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-800 hover:bg-neon hover:text-slate-950 border border-slate-700 hover:border-neon text-xs font-semibold text-white transition duration-150">
                                Pakai {{ $tpl['name'] }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Load More -> Go to Studio Editor with all 37 Designs --}}
            <div class="mt-8 bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="text-base font-bold text-white kp-heading">
                        Masih Ada +{{ max(0, ($totalTemplatesCount ?? 37) - count($templateHighlights)) }} Desain Template Lainnya di Studio Editor
                    </div>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Termasuk Botanical Calm, Retro Paper 70s, Split 50/50, Runner's Journal, Cinematic Film 35mm, Y2K Run Club, World Majors 7-Star, KOM Crown, Athletic Blueprint, Race Week &amp; Taper, hingga Golden Hour Miles.
                    </p>
                </div>
                <a href="{{ $studioUrl }}"
                   id="kpLoadMoreToEditorBtn"
                   class="w-full md:w-auto inline-flex items-center justify-center min-h-[44px] px-6 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150 shrink-0">
                    Load More ({{ $totalTemplatesCount ?? 37 }} Desain di Studio Editor) &rarr;
                </a>
            </div>
        </div>
    </section>

    {{-- Section 3: Asymmetric Workflow & Technical Print Specs --}}
    <section id="spesifikasi" class="border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Left: 3-Step Workflow --}}
                <div class="lg:col-span-6">
                    <h2 class="text-2xl sm:text-3xl text-white mb-2">Alur Kerja dari Foto ke File Siap Cetak</h2>
                    <p class="text-sm text-slate-300 mb-6">
                        Tanpa instalasi software desain. Atur penanggalan, masukkan foto terbaik tiap bulan, lalu unduh satu berkas PDF utuh.
                    </p>

                    <div class="space-y-3.5">
                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex items-start gap-4">
                            <span class="kp-mono text-sm font-bold text-slate-950 bg-neon px-2.5 py-1 rounded shrink-0">01</span>
                            <div>
                                <h3 class="text-base text-white">Pilih Format Kertas & Template</h3>
                                <p class="text-sm text-slate-300 mt-1 leading-relaxed">Tentukan kalender meja A4 atau kalender dinding A3, lalu pilih tampilan 1 bulan, 2 bulan, atau 4 bulan per lembar.</p>
                            </div>
                        </div>

                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex items-start gap-4">
                            <span class="kp-mono text-sm font-bold text-white bg-slate-800 border border-slate-700 px-2.5 py-1 rounded shrink-0">02</span>
                            <div>
                                <h3 class="text-base text-white">Unggah Foto & Tandai Target Lomba</h3>
                                <p class="text-sm text-slate-300 mt-1 leading-relaxed">Pasang foto lari per halaman, sisipkan catatan jarak atau pace, dan aktifkan penanda otomatis libur nasional serta jadwal race.</p>
                            </div>
                        </div>

                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex items-start gap-4">
                            <span class="kp-mono text-sm font-bold text-white bg-slate-800 border border-slate-700 px-2.5 py-1 rounded shrink-0">03</span>
                            <div>
                                <h3 class="text-base text-white">Ekspor PDF 300 DPI</h3>
                                <p class="text-sm text-slate-300 mt-1 leading-relaxed">Unduh paket lengkap 13 halaman (cover depan plus Januari hingga Desember) siap dibawa ke percetakan digital terdekat.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Crisp Specification Table --}}
                <div class="lg:col-span-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between gap-2">
                            <h3 class="text-base text-white">Spesifikasi Teknis Cetak</h3>
                            <span class="kp-mono text-xs text-neon font-semibold">STANDAR PERCETAKAN</span>
                        </div>
                        <dl class="divide-y divide-slate-800 text-sm">
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Resolusi Ekspor PDF</dt>
                                <dd class="kp-mono text-white font-semibold">300 DPI Tajam</dd>
                            </div>
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Kalender Meja</dt>
                                <dd class="kp-mono text-white font-semibold">A4 Landscape (297 x 210 mm)</dd>
                            </div>
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Kalender Dinding</dt>
                                <dd class="kp-mono text-white font-semibold">A3 Portrait (297 x 420 mm)</dd>
                            </div>
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Opsi Tata Letak Halaman</dt>
                                <dd class="kp-mono text-white font-semibold">1, 2, atau 4 Bulan / Lembar</dd>
                            </div>
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Kalender Libur & Lomba</dt>
                                <dd class="kp-mono text-white font-semibold">Libur RI {{ $nextYear }} + Event Lari</dd>
                            </div>
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Integrasi Tautan</dt>
                                <dd class="kp-mono text-white font-semibold">QR Code Strava / Galeri</dd>
                            </div>
                            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <dt class="text-slate-300">Privasi Berkas Foto</dt>
                                <dd class="text-slate-200 font-medium">Diproses Lokal di Browser</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 4: FAQ & Closing CTA --}}
    <section id="faq">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <div class="mb-8">
                <h2 class="text-2xl sm:text-3xl text-white">Pertanyaan yang Sering Diajukan</h2>
                <p class="text-sm text-slate-300 mt-1.5">Detail mengenai format ekspor, penanggalan Indonesia, dan privasi foto Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
                <details class="kp-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm sm:text-base font-semibold text-white">
                        <span>Apakah hasil PDF bisa langsung dicetak oleh percetakan?</span>
                        <svg class="kp-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                        Ya. Studio mengekspor file PDF beresolusi 300 DPI pada rasio A4 Landscape atau A3 Portrait berisi 13 halaman lengkap dari cover hingga Desember.
                    </p>
                </details>

                <details class="kp-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm sm:text-base font-semibold text-white">
                        <span>Apakah tanggal merah Indonesia sudah tersedia otomatis?</span>
                        <svg class="kp-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                        Ya. Tanggal libur nasional, cuti bersama resmi di Indonesia, dan daftar jadwal lomba lari populer sudah tertanam langsung pada kalender {{ $nextYear }}.
                    </p>
                </details>

                <details class="kp-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm sm:text-base font-semibold text-white">
                        <span>Apakah perlu mendaftar atau membayar sebelum mengunduh?</span>
                        <svg class="kp-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                        Tidak perlu membuat akun. Anda bisa langsung membuka Studio atau Wizard Cepat, menyusun seluruh halaman, dan mengekspor berkas PDF secara gratis.
                    </p>
                </details>

                <details class="kp-faq-item bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-sm sm:text-base font-semibold text-white">
                        <span>Bagaimana keamanan foto pribadi yang saya unggah?</span>
                        <svg class="kp-faq-chevron w-4 h-4 text-slate-400 shrink-0 transition duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                        Foto yang Anda pilih diproses langsung di browser perangkat Anda untuk pratinjau dan pembuatan dokumen cetak.
                    </p>
                </details>
            </div>

            {{-- Solid Closing CTA Banner --}}
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="max-w-xl">
                    <h2 class="text-xl sm:text-2xl text-white">Siap Menyusun Kalender Lari {{ $nextYear }}?</h2>
                    <p class="text-sm text-slate-300 mt-1.5">
                        Mulai dari kanvas studio lengkap atau gunakan mode wizard bertahap untuk hasil cepat dalam hitungan menit.
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 shrink-0 w-full lg:w-auto">
                    <a href="{{ $studioUrl }}"
                       class="inline-flex items-center justify-center h-11 px-6 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150 whitespace-nowrap">
                        Buka Studio Desain
                    </a>
                    <a href="{{ $wizardUrl }}"
                       class="inline-flex items-center justify-center h-11 px-6 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-semibold transition duration-150 whitespace-nowrap">
                        Coba Mode Wizard
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Hero Interactive Calendar Specimen Switcher (1M / 2M / 4M)
    const tabButtons = document.querySelectorAll('.kp-tab-btn');
    const panels = {
        '1m': {
            el: document.getElementById('kpSpecimen1m'),
            meta: 'Format 1 Bulan · Meja A4 Landscape',
            title: 'JANUARI {{ $nextYear }}'
        },
        '2m': {
            el: document.getElementById('kpSpecimen2m'),
            meta: 'Format 2 Bulan · 7 Halaman Ringkas',
            title: 'JAN - FEB {{ $nextYear }}'
        },
        '4m': {
            el: document.getElementById('kpSpecimen4m'),
            meta: 'Format 4 Bulan · Rekap Kuartal Cetak',
            title: 'JAN - APR {{ $nextYear }}'
        }
    };
    const metaLabel = document.getElementById('kpSpecimenMeta');
    const titleLabel = document.getElementById('kpSpecimenTitle');

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const key = btn.getAttribute('data-specimen-tab');
            if (!panels[key]) return;

            tabButtons.forEach(function (other) {
                const isActive = other === btn;
                other.setAttribute('aria-selected', isActive ? 'true' : 'false');
                other.classList.toggle('bg-neon', isActive);
                other.classList.toggle('text-slate-950', isActive);
                other.classList.toggle('font-bold', isActive);
                other.classList.toggle('bg-slate-950', !isActive);
                other.classList.toggle('text-slate-300', !isActive);
                other.classList.toggle('font-semibold', !isActive);
            });

            Object.keys(panels).forEach(function (k) {
                if (panels[k].el) {
                    panels[k].el.classList.toggle('hidden', k !== key);
                }
            });

            if (metaLabel) metaLabel.textContent = panels[key].meta;
            if (titleLabel) titleLabel.textContent = panels[key].title;
        });
    });

    // Local Photo Preview & Zero-Storage IndexedDB Handoff to Studio
    const photoInput = document.getElementById('kpLandingPhotoInput');
    const photoDrop = document.getElementById('kpSpecimenPhotoDrop');
    const photoImg = document.getElementById('kpSpecimenPhotoImg');
    const uploadBtn = document.getElementById('kpUploadPhotoBtn');
    const resetBtn = document.getElementById('kpResetPhotoBtn');
    const continueBtn = document.getElementById('kpContinueWithPhotoBtn');
    const photoHint = document.getElementById('kpPhotoHint');
    const subtitleLabel = document.getElementById('kpSpecimenSubtitle');
    let currentObjectUrl = null;

    function openHandoffDb() {
        return new Promise(function (resolve, reject) {
            if (!window.indexedDB) return reject(new Error('IndexedDB unsupported'));
            const req = window.indexedDB.open('kp_landing_handoff', 1);
            req.onupgradeneeded = function () {
                const db = req.result;
                if (!db.objectStoreNames.contains('photos')) {
                    db.createObjectStore('photos');
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }

    async function saveLandingPhotoBlob(blob, filename) {
        try {
            const db = await openHandoffDb();
            return new Promise(function (resolve, reject) {
                const tx = db.transaction('photos', 'readwrite');
                tx.objectStore('photos').put({ blob: blob, name: filename, ts: Date.now() }, 'cover_photo');
                tx.oncomplete = function () { db.close(); resolve(true); };
                tx.onerror = function () { db.close(); reject(tx.error); };
            });
        } catch (e) {
            return false;
        }
    }

    async function clearLandingPhotoBlob() {
        try {
            const db = await openHandoffDb();
            return new Promise(function (resolve) {
                const tx = db.transaction('photos', 'readwrite');
                tx.objectStore('photos').delete('cover_photo');
                tx.oncomplete = function () { db.close(); resolve(); };
                tx.onerror = function () { db.close(); resolve(); };
            });
        } catch (e) {}
    }

    async function handleSelectedPhoto(file) {
        if (!file || !file.type || !file.type.startsWith('image/')) return;
        if (currentObjectUrl) URL.revokeObjectURL(currentObjectUrl);
        currentObjectUrl = URL.createObjectURL(file);
        if (photoImg) photoImg.src = currentObjectUrl;
        if (subtitleLabel) subtitleLabel.textContent = file.name.toUpperCase() + ' · PRATINJAU LOKAL';

        await saveLandingPhotoBlob(file, file.name);

        if (typeof setSlotPhoto === 'function') {
            const customImg = new Image();
            customImg.onload = function () {
                setSlotPhoto(0, 0, customImg);
                if (typeof window.renderLandingTemplates === 'function') {
                    window.renderLandingTemplates();
                }
            };
            customImg.src = currentObjectUrl;
        }

        if (resetBtn) resetBtn.classList.remove('hidden');
        if (continueBtn) continueBtn.classList.remove('hidden');
        if (photoHint) photoHint.classList.add('hidden');
    }

    if (uploadBtn && photoInput) {
        uploadBtn.addEventListener('click', function () { photoInput.click(); });
    }
    if (photoDrop && photoInput) {
        photoDrop.addEventListener('click', function () { photoInput.click(); });
        photoDrop.addEventListener('dragover', function (e) {
            e.preventDefault();
            photoDrop.classList.add('ring-2', 'ring-neon');
        });
        photoDrop.addEventListener('dragleave', function () {
            photoDrop.classList.remove('ring-2', 'ring-neon');
        });
        photoDrop.addEventListener('drop', function (e) {
            e.preventDefault();
            photoDrop.classList.remove('ring-2', 'ring-neon');
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) handleSelectedPhoto(file);
        });
    }
    if (photoInput) {
        photoInput.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (file) handleSelectedPhoto(file);
        });
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', async function () {
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }
            if (photoImg) photoImg.src = photoImg.getAttribute('data-default-src');
            if (subtitleLabel) subtitleLabel.textContent = 'BOROBUDUR MARATHON · 42.195 KM';
            if (photoInput) photoInput.value = '';
            resetBtn.classList.add('hidden');
            if (continueBtn) continueBtn.classList.add('hidden');
            if (photoHint) photoHint.classList.remove('hidden');
            if (typeof setSlotPhoto === 'function') {
                setSlotPhoto(0, 0, null);
                if (typeof window.renderLandingTemplates === 'function') {
                    window.renderLandingTemplates();
                }
            }
            await clearLandingPhotoBlob();
        });
    }
});
</script>
<script>
    window.KP_BG_BASE_URL = "{{ asset('images/kalender-pelari/bg') }}/";
</script>
<script src="{{ asset('js/kalender-pelari/data.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/state.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/canvas.js') }}"></script>
<script>
(function () {
    let previewTimer = null;
    const baseStudioUrl = @json($studioUrl);

    function renderLandingTemplatesNow() {
        previewTimer = null;
        const tplInput = document.getElementById('template');
        const paperInput = document.getElementById('paper');
        const accentInput = document.getElementById('accent');
        const layoutInput = document.getElementById('layout');
        if (!tplInput || !paperInput || !accentInput || !layoutInput || typeof draw !== 'function') return;

        const isWall = layoutInput.value === 'wall';

        const canvases = document.querySelectorAll('.kp-landing-tpl-canvas');
        canvases.forEach(function (c) {
            const t = c.getAttribute('data-template') || 'classic';
            // poster12m is always wall/portrait A3+
            const useWall = (t === 'poster12m') ? true : isWall;
            const prevLayout = layoutInput.value;
            layoutInput.value = useWall ? 'wall' : 'landscape';

            const w = useWall ? 220 : 260;
            const h = useWall ? 330 : 174;
            if (c.width !== w || c.height !== h) {
                c.width = w;
                c.height = h;
            }

            tplInput.value = t;
            const pal = (typeof palettes !== 'undefined' && palettes[t]) ? palettes[t] : ['#f45123', '#ffffff'];
            accentInput.value = pal[0];
            paperInput.value = pal[1];

            try {
                draw(c, 0);
            } catch (e) {}

            layoutInput.value = prevLayout;
        });
    }

    window.renderLandingTemplates = function () {
        if (previewTimer) clearTimeout(previewTimer);
        previewTimer = setTimeout(renderLandingTemplatesNow, 60);
    };

    // Hook into data.js image onload callbacks which call render()
    window.render = window.renderLandingTemplates;

    document.addEventListener('DOMContentLoaded', function () {
        window.renderLandingTemplates();

        const layoutBtns = document.querySelectorAll('.kp-tpl-layout-btn');
        const layoutInput = document.getElementById('layout');
        const tplLinks = document.querySelectorAll('[data-tpl-link]');

        layoutBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetLayout = btn.getAttribute('data-tpl-layout') || 'wall';
                if (layoutInput) layoutInput.value = targetLayout;

                layoutBtns.forEach(function (b) {
                    const active = b.getAttribute('data-tpl-layout') === targetLayout;
                    b.className = active
                        ? 'kp-tpl-layout-btn h-8 px-3 rounded text-xs font-bold bg-neon text-slate-950 transition duration-150 cursor-pointer'
                        : 'kp-tpl-layout-btn h-8 px-3 rounded text-xs font-semibold text-slate-300 hover:text-white transition duration-150 cursor-pointer';
                });

                tplLinks.forEach(function (a) {
                    const tplId = a.getAttribute('data-tpl-link');
                    const formatParam = (targetLayout === 'wall' || tplId === 'poster12m') ? 'A3_LANDSCAPE' : 'A4_LANDSCAPE';
                    const layoutParam = (tplId === 'poster12m') ? 'wall' : targetLayout;
                    a.href = baseStudioUrl + '?template=' + encodeURIComponent(tplId) + '&format=' + formatParam + '&layout=' + layoutParam;
                });

                renderLandingTemplatesNow();
            });
        });
    });

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function () {
            window.renderLandingTemplates();
        });
    }
})();
</script>
@endpush
