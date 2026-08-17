@extends('layouts.pacerhub')

@php
    $formattedDist = number_format((float)($item->distance_km ?? 0), 2);
    $cityName = $item->city ?? 'Indonesia';
    $pageTitle = e($item->title) . ' (' . $formattedDist . ' km, ' . e($cityName) . ') - Download GPX & Navigasi | RuangLari';
    $pageDesc = 'Unduh gratis file GPX rute lari ' . e($item->title) . ' di ' . e($cityName) . '. Jarak ' . $formattedDist . ' km, elevasi +' . round($item->elevation_gain_m ?? 0) . 'm. Dilengkapi mode navigasi live GPS dan analisis PacePro.';
    $coordsJson = json_encode($item->coordinates_json ?? []);
    $distKm = (float)($item->distance_km ?? 0);
    $gainM = (float)($item->elevation_gain_m ?? 0);
    $lossM = (float)($item->elevation_loss_m ?? 0);
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)
@section('meta_keywords', 'rute gpx ' . strtolower($item->title) . ', navigasi gpx ' . strtolower($item->title) . ', download gpx ' . strtolower($cityName) . ', gpx garmin, gpx strava route')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        #gpx-detail-map {
            height: 460px;
            width: 100%;
            background: #0d121c;
            z-index: 1;
        }
        .leaflet-tile {
            filter: brightness(0.95) contrast(1.05);
        }
        .pacepro-table-wrap::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }
        .pacepro-table-wrap::-webkit-scrollbar-track {
            background: #0b0f17;
        }
        .pacepro-table-wrap::-webkit-scrollbar-thumb {
            background: #1e2638;
            border-radius: 2px;
        }
    </style>

    <!-- OpenGraph & Twitter Meta Tags -->
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:type" content="place">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="RuangLari">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ url('/') }}"
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": "Database GPX",
          "item": "{{ route('gpx.index') }}"
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "name": "{{ addslashes($item->title) }}",
          "item": "{{ url()->current() }}"
        }
      ]
    }
    </script>
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "ExercisePlan",
      "name": "{{ addslashes($item->title) }}",
      "description": "{{ addslashes($pageDesc) }}",
      "exerciseType": "Running",
      "distance": "{{ $formattedDist }} km",
      "spatialCoverage": {
        "@@type": "Place",
        "name": "{{ addslashes($cityName) }}"
      },
      "author": {
        "@@type": "Person",
        "name": "{{ addslashes($item->user?->name ?? 'RuangLari Official') }}"
      }
    }
    </script>
@endpush

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-[#0B0F17] text-slate-200 font-sans">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Breadcrumb & Top Utility Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <nav class="flex items-center gap-1.5 font-medium">
                <a href="{{ route('gpx.index') }}" class="hover:text-slate-200 transition">Database GPX</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 truncate max-w-[200px] sm:max-w-sm">{{ $item->title }}</span>
            </nav>

            <div class="flex items-center gap-2">
                <button type="button" onclick="shareGpxRoute()" class="px-2.5 py-1.5 rounded-md border border-slate-700/80 hover:border-slate-600 text-slate-300 hover:text-white text-xs font-medium transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-share-nodes text-[11px] text-slate-400"></i>
                    <span>Bagikan</span>
                </button>
            </div>
        </div>

        <!-- Route Header & Primary Action Bar -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-2 border-b border-slate-800/80">
            <div class="space-y-2.5 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded border border-slate-700 bg-slate-850 text-slate-300 font-medium">
                        <i class="fa-solid fa-location-dot text-[#FC4C02] text-[10px]"></i>
                        <span>{{ $cityName }}</span>
                    </span>
                    
                    @if($item->event)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded border border-slate-700 bg-slate-850 text-slate-300 font-medium">
                            <i class="fa-solid fa-trophy text-slate-400 text-[10px]"></i>
                            <span>{{ $item->event->title ?? $item->event->name ?? 'Event Lari' }}</span>
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded border border-emerald-800/60 bg-emerald-950/40 text-emerald-400 text-[11px] font-medium">
                        <i class="fa-solid fa-check text-[10px]"></i>
                        <span>Terverifikasi</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    {{ $item->title }}
                </h1>

                <p class="text-slate-400 text-sm leading-relaxed max-w-2xl font-normal">
                    {{ $item->description ?? $item->notes ?? 'File rute GPX lari terverifikasi untuk Garmin, Coros, Suunto, dan Strava dengan profil elevasi dan panduan strategi pacing.' }}
                </p>
            </div>

            <!-- Clear Action Hierarchy -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 shrink-0">
                <!-- Primary CTA: Download GPX -->
                <a href="{{ route('gpx.download', $item->id) }}" class="px-4 py-2.5 rounded-lg bg-[#FC4C02] hover:bg-[#e04300] text-white font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download GPX</span>
                </a>

                <!-- Secondary CTA: Live Navigation -->
                <button type="button" onclick="startLiveNavigation()" class="px-4 py-2.5 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-850 hover:bg-slate-800 text-slate-200 font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-location-arrow text-xs text-[#FC4C02]"></i>
                    <span>Mulai Navigasi</span>
                </button>

                <!-- Minor/Utility Action -->
                <a href="{{ route('tools.buat-rute-lari') }}" class="px-3 py-2.5 rounded-lg border border-slate-750 hover:border-slate-700 text-slate-400 hover:text-slate-200 text-xs font-medium transition flex items-center justify-center gap-1.5" title="Buka di Route Builder">
                    <i class="fa-solid fa-draw-polygon text-[11px]"></i>
                    <span>Editor</span>
                </a>
            </div>
        </div>

        <!-- Consolidated Statistics Strip -->
        <div class="bg-[#111724] border border-slate-800/80 rounded-lg grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-800/80">
            <div class="p-4">
                <span class="text-xs font-medium text-slate-400 block">Jarak Total</span>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-white tabular-nums">{{ $formattedDist }}</span>
                    <span class="text-xs text-slate-400 font-medium">km</span>
                </div>
            </div>

            <div class="p-4">
                <span class="text-xs font-medium text-slate-400 block">Elevasi Naik</span>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-white tabular-nums">+{{ number_format($gainM) }}</span>
                    <span class="text-xs text-slate-400 font-medium">m</span>
                </div>
            </div>

            <div class="p-4">
                <span class="text-xs font-medium text-slate-400 block">Elevasi Turun</span>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-white tabular-nums">-{{ number_format($lossM) }}</span>
                    <span class="text-xs text-slate-400 font-medium">m</span>
                </div>
            </div>

            <div class="p-4">
                <span class="text-xs font-medium text-slate-400 block">Kontributor</span>
                <div class="mt-1 truncate">
                    <span class="text-sm font-semibold text-white block truncate">{{ $item->user?->name ?? 'RuangLari' }}</span>
                    <span class="text-[11px] text-slate-500 block">{{ $item->created_at ? $item->created_at->format('d M Y') : 'Terverifikasi' }}</span>
                </div>
            </div>
        </div>

        <!-- Primary Map & Integrated Elevation Stage -->
        <div class="bg-[#111724] border border-slate-800/80 rounded-lg overflow-hidden">
            <!-- Map Sub-header -->
            <div class="px-4 py-3 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FC4C02]"></span>
                    <h2 class="font-semibold text-slate-200 text-xs">Peta Jalur GPS</h2>
                </div>

                <div class="flex items-center gap-3 text-xs text-slate-400 font-medium">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Start
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#0d121c] border border-slate-600 text-[9px] font-mono text-center flex items-center justify-center font-bold text-white">1</span> KM Mark
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#FC4C02]"></span> Finish
                    </span>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="gpx-detail-map" class="relative"></div>

            <!-- Elevation Profile (Integrated directly underneath) -->
            <div class="p-4 bg-[#0D131F] border-t border-slate-800 space-y-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
                    <div class="flex items-center gap-1.5 font-medium text-slate-300">
                        <i class="fa-solid fa-chart-area text-[#FC4C02] text-[11px]"></i>
                        <span>Profil Elevasi</span>
                    </div>
                    <div class="flex items-center gap-3 font-mono text-[11px]">
                        <span>Min: <strong id="elev-min-val" class="text-slate-200 font-semibold">0m</strong></span>
                        <span>Max: <strong id="elev-max-val" class="text-slate-200 font-semibold">0m</strong></span>
                        <span>Gain: <strong id="elev-gain-val" class="text-slate-200 font-semibold">+{{ round($gainM) }}m</strong></span>
                    </div>
                </div>

                <!-- Elevation SVG Chart -->
                <div class="relative w-full h-28 select-none" id="elevation-chart-container">
                    <svg id="gpx-elev-svg" class="w-full h-full block overflow-visible" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="elevGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#FC4C02" stop-opacity="0.2" />
                                <stop offset="100%" stop-color="#FC4C02" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path id="elev-area-path" fill="url(#elevGrad)" d="" />
                        <path id="elev-line-path" fill="none" stroke="#FC4C02" stroke-width="1.8" stroke-linecap="round" d="" />
                    </svg>

                    <!-- Hover Tracker -->
                    <div id="elev-hover-line" class="absolute top-0 bottom-0 w-[1px] bg-slate-300 pointer-events-none hidden"></div>
                    <div id="elev-hover-dot" class="absolute w-2 h-2 rounded-full bg-white border border-[#FC4C02] pointer-events-none hidden -translate-x-1/2 -translate-y-1/2"></div>
                    <div id="elev-tooltip" class="absolute hidden bg-[#0B0F17] border border-slate-700 text-white text-[11px] font-mono font-medium px-2 py-0.5 rounded shadow pointer-events-none z-20 whitespace-nowrap -top-7 -translate-x-1/2">
                        <span id="elev-tooltip-km">0.0 KM</span> • <span id="elev-tooltip-m" class="text-[#FC4C02]">0m</span>
                    </div>
                </div>

                <div class="flex justify-between items-center text-[10px] font-mono text-slate-500 pt-0.5">
                    <span>0 km</span>
                    <span id="elev-chart-mid-km">{{ number_format($distKm / 2, 1) }} km</span>
                    <span>{{ $formattedDist }} km</span>
                </div>
            </div>
        </div>

        <!-- PACEPRO RACE STRATEGY PLANNER (Data-Focused Running Tool) -->
        <section id="gpx-pacepro-section" class="bg-[#111724] border border-slate-800/80 rounded-lg p-5 md:p-6 space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">
                        PacePro™ Strategy Planner
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Rencana target split per kilometer berdasarkan profil tanjakan rute dan tipe pacing yang dipilih.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-slate-400 font-medium">Strategi:</span>
                    <span id="pp-strategy-badge" class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-xs font-semibold text-white">Even Split (0%)</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-12 gap-5">
                <!-- Left: Analytical Form Inputs -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-[#0D131F] border border-slate-800 rounded-lg p-4 space-y-4">
                        <!-- Target Time -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Target Waktu Selesai
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <input type="number" id="pp-time-h" min="0" max="23" value="0" class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded p-2 text-center text-white font-mono font-bold text-sm outline-none transition">
                                    <span class="text-[10px] text-slate-400 text-center block mt-1">Jam</span>
                                </div>
                                <div>
                                    <input type="number" id="pp-time-m" min="0" max="59" value="{{ $distKm >= 20 ? 55 : ($distKm >= 10 ? 50 : 28) }}" class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded p-2 text-center text-white font-mono font-bold text-sm outline-none transition">
                                    <span class="text-[10px] text-slate-400 text-center block mt-1">Menit</span>
                                </div>
                                <div>
                                    <input type="number" id="pp-time-s" min="0" max="59" value="00" class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded p-2 text-center text-white font-mono font-bold text-sm outline-none transition">
                                    <span class="text-[10px] text-slate-400 text-center block mt-1">Detik</span>
                                </div>
                            </div>
                        </div>

                        <!-- Presets -->
                        <div>
                            <span class="block text-[11px] font-medium text-slate-400 mb-1.5">Preset Target Pace:</span>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-1">
                                <button type="button" onclick="setPacePreset(4, 30)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 text-center cursor-pointer transition">4:30</button>
                                <button type="button" onclick="setPacePreset(5, 0)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 text-center cursor-pointer transition">5:00</button>
                                <button type="button" onclick="setPacePreset(5, 30)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 text-center cursor-pointer transition">5:30</button>
                                <button type="button" onclick="setPacePreset(6, 0)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 text-center cursor-pointer transition">6:00</button>
                                <button type="button" onclick="setPacePreset(6, 30)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 text-center cursor-pointer transition">6:30</button>
                                <button type="button" onclick="setPacePreset(7, 0)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 text-center cursor-pointer transition">7:00</button>
                            </div>
                        </div>

                        <!-- Split Strategy Slider -->
                        <div class="pt-2.5 border-t border-slate-800">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-semibold text-slate-300">Pacing Split</label>
                                <span id="pp-strategy-label" class="text-xs font-semibold text-slate-200 font-mono">Even (0%)</span>
                            </div>
                            <input type="range" id="pp-strategy-slider" min="-10" max="10" value="0" step="1" class="w-full h-1 bg-slate-800 rounded appearance-none cursor-pointer accent-[#FC4C02]">
                            <div class="flex justify-between text-[10px] text-slate-500 mt-1 font-mono">
                                <span>Negative (-10%)</span>
                                <span>Even (0%)</span>
                                <span>Positive (+10%)</span>
                            </div>
                        </div>

                        <!-- Hill Strategy Slider -->
                        <div class="pt-2.5 border-t border-slate-800">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-semibold text-slate-300">Adaptasi Tanjakan</label>
                                <span id="pp-hill-label" class="text-xs font-semibold text-[#FC4C02] font-mono">Normal (0)</span>
                            </div>
                            <input type="range" id="pp-hill-slider" min="-10" max="10" value="0" step="1" class="w-full h-1 bg-slate-800 rounded appearance-none cursor-pointer accent-[#FC4C02]">
                            <div class="flex justify-between text-[10px] text-slate-500 mt-1 font-mono">
                                <span>Agresif (-10)</span>
                                <span>Normal (0)</span>
                                <span>Hemat Tenaga (+10)</span>
                            </div>
                        </div>

                        <button type="button" id="pp-btn-generate" class="w-full py-2.5 px-3 rounded-lg bg-[#FC4C02] hover:bg-[#e04300] text-white font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-1.5 cursor-pointer shadow-sm">
                            <i class="fa-solid fa-calculator text-[11px]"></i>
                            <span>Hitung Ulang Split</span>
                        </button>
                    </div>
                </div>

                <!-- Right: Clean Splits Table -->
                <div class="lg:col-span-7 space-y-3">
                    <!-- Summary Strip -->
                    <div class="bg-[#0D131F] border border-slate-800 rounded-lg p-3 grid grid-cols-3 divide-x divide-slate-800 text-center">
                        <div>
                            <span class="text-[10px] text-slate-400 font-medium block">Rata-Rata Pace</span>
                            <span id="pp-avg-pace" class="text-base font-bold font-mono text-white mt-0.5 block">--:-- /km</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-medium block">Target Waktu</span>
                            <span id="pp-target-total-time" class="text-base font-bold font-mono text-white mt-0.5 block">00:00:00</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-medium block">Total Jarak</span>
                            <span class="text-base font-bold font-mono text-slate-300 mt-0.5 block">{{ $formattedDist }} km</span>
                        </div>
                    </div>

                    <!-- Splits Table -->
                    <div class="bg-[#0D131F] border border-slate-800 rounded-lg overflow-hidden">
                        <div class="px-3.5 py-2.5 bg-[#111724] border-b border-slate-800 flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-200">Tabel Split per Kilometer</span>

                            <div class="flex items-center gap-1.5">
                                <button type="button" id="pp-btn-copy" onclick="copyPaceStrategy()" class="px-2 py-1 rounded bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-xs font-medium text-slate-300 transition cursor-pointer flex items-center gap-1">
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                    <span id="pp-copy-text">Salin</span>
                                </button>
                                <button type="button" id="pp-btn-csv" onclick="exportPaceCsv()" class="px-2 py-1 rounded bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-xs font-medium text-slate-300 transition cursor-pointer flex items-center gap-1">
                                    <i class="fa-solid fa-file-csv text-[10px] text-[#FC4C02]"></i>
                                    <span>CSV</span>
                                </button>
                            </div>
                        </div>

                        <div class="pacepro-table-wrap max-h-[340px] overflow-y-auto overflow-x-auto">
                            <table class="w-full text-left text-xs" id="pp-pace-table">
                                <thead class="bg-[#090D16] text-slate-400 uppercase text-[10px] font-mono sticky top-0 border-b border-slate-800 z-10">
                                    <tr>
                                        <th class="py-2 px-3.5 font-medium">KM</th>
                                        <th class="py-2 px-3.5 font-medium">Target Pace</th>
                                        <th class="py-2 px-3.5 font-medium">Waktu Split</th>
                                        <th class="py-2 px-3.5 font-medium">Waktu Kumulatif</th>
                                        <th class="py-2 px-3.5 text-right font-medium">Elevasi</th>
                                    </tr>
                                </thead>
                                <tbody id="pp-table-body" class="divide-y divide-slate-800/80 font-mono text-slate-300">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Route Notes & Details -->
        @if($item->description || $item->notes)
            <div class="bg-[#111724] border border-slate-800/80 rounded-lg p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 border-b border-slate-800 pb-2.5">
                    Informasi & Catatan Rute
                </h3>
                
                <div class="text-slate-300 text-sm leading-relaxed space-y-2.5">
                    @if($item->description)
                        <p class="whitespace-pre-line">{{ $item->description }}</p>
                    @endif

                    @if($item->notes && $item->notes !== $item->description)
                        <div class="p-3 rounded bg-[#0D131F] border border-slate-800 text-xs text-slate-400">
                            <strong class="text-white block mb-0.5">Catatan Tambahan:</strong>
                            <p class="whitespace-pre-line">{{ $item->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Smartwatch Sync Guide (Garmin, Coros, Strava) -->
        <section class="bg-[#111724] border border-slate-800/80 rounded-lg p-5 space-y-4"
                 x-data="{ activeTab: 'garmin' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3.5">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase">Cara Memasukkan GPX ke Jam Tangan</h3>
                    <span class="text-xs text-slate-400">Panduan sinkronisasi ke aplikasi pendukung jam pelari.</span>
                </div>

                <!-- Clean Tabs -->
                <div class="flex items-center gap-1 bg-[#0D131F] p-0.5 rounded border border-slate-800">
                    <button type="button" @click="activeTab = 'garmin'"
                            class="px-3 py-1 rounded text-xs font-medium transition cursor-pointer"
                            :class="activeTab === 'garmin' ? 'bg-[#111724] text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white'">
                        Garmin
                    </button>
                    <button type="button" @click="activeTab = 'coros'"
                            class="px-3 py-1 rounded text-xs font-medium transition cursor-pointer"
                            :class="activeTab === 'coros' ? 'bg-[#111724] text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white'">
                        Coros
                    </button>
                    <button type="button" @click="activeTab = 'strava'"
                            class="px-3 py-1 rounded text-xs font-medium transition cursor-pointer"
                            :class="activeTab === 'strava' ? 'bg-[#111724] text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white'">
                        Strava
                    </button>
                </div>
            </div>

            <!-- Garmin Guide -->
            <div x-show="activeTab === 'garmin'" class="space-y-2 text-xs text-slate-300 leading-relaxed">
                <ol class="space-y-1.5 list-decimal list-inside text-slate-400">
                    <li><strong class="text-white">Download file GPX</strong> rute ini dengan menekan tombol <em>Download GPX</em> di atas.</li>
                    <li>Buka aplikasi <strong class="text-white">Garmin Connect</strong> di smartphone Anda.</li>
                    <li>Pilih menu <strong>More (&hellip;) &rarr; Training & Planning &rarr; Courses</strong>.</li>
                    <li>Klik opsi <strong>Import Course / Import Course File</strong> dan pilih file GPX yang baru saja Anda download.</li>
                    <li>Pilih tipe aktivitas (contoh: <em>Running / Trail Running</em>), lalu simpan rute (<em>Save</em>).</li>
                    <li>Klik icon <strong>Send to Device</strong> di pojok kanan atas untuk menyinkronkan rute ke jam Garmin Anda.</li>
                </ol>
            </div>

            <!-- Coros Guide -->
            <div x-show="activeTab === 'coros'" class="space-y-2 text-xs text-slate-300 leading-relaxed">
                <ol class="space-y-1.5 list-decimal list-inside text-slate-400">
                    <li><strong class="text-white">Download file GPX</strong> rute ini ke smartphone Anda.</li>
                    <li>Buka file GPX yang terunduh dan pilih <strong>Open with COROS App</strong>.</li>
                    <li>Aplikasi COROS akan otomatis menampilkan preview rute dan peta. Klik <strong>Save to My Routes</strong>.</li>
                    <li>Buka menu <strong>Profile &rarr; Route Library</strong> di aplikasi COROS.</li>
                    <li>Pilih rute tersebut dan klik <strong>Sync with Watch</strong> untuk mengunggahnya langsung ke jam Coros.</li>
                </ol>
            </div>

            <!-- Strava Guide -->
            <div x-show="activeTab === 'strava'" class="space-y-2 text-xs text-slate-300 leading-relaxed">
                <ol class="space-y-1.5 list-decimal list-inside text-slate-400">
                    <li>Download file GPX di RuangLari.</li>
                    <li>Buka website <strong>Strava.com</strong> di browser komputer / HP.</li>
                    <li>Buka menu <strong>Dashboard &rarr; My Routes &rarr; Create New Route</strong> &rarr; Pilih <strong>Upload GPX</strong>.</li>
                    <li>Simpan rute di akun Strava Anda. Rute akan otomatis muncul di menu <em>Saved Routes</em> pada aplikasi mobile Strava Anda.</li>
                </ol>
            </div>
        </section>

        <!-- Related GPX Routes in the Same Region -->
        @if(isset($related) && $related->count() > 0)
            <div class="space-y-3.5 pt-3 border-t border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase">Rute GPX Serupa</h3>
                        <span class="text-xs text-slate-400">Rute lari lain di wilayah sekitarnya.</span>
                    </div>
                    <a href="{{ route('gpx.index') }}" class="text-xs text-slate-400 hover:text-white transition font-medium">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($related as $rel)
                        <a href="{{ route('gpx.show', $rel->slug ?: $rel->id) }}" class="group bg-[#111724] border border-slate-800 hover:border-slate-700 rounded-lg p-3.5 transition flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between gap-2 text-[11px] font-mono text-slate-400">
                                    <span class="text-[#FC4C02] font-semibold">{{ $rel->city ?? 'Indonesia' }}</span>
                                    <span class="text-white font-semibold">{{ number_format((float)($rel->distance_km ?? 0), 1) }} km</span>
                                </div>

                                <h4 class="text-sm font-medium text-slate-200 group-hover:text-white transition-colors line-clamp-1">
                                    {{ $rel->title }}
                                </h4>
                            </div>

                            <div class="mt-3 pt-2 border-t border-slate-800 flex items-center justify-between text-xs font-mono text-slate-400">
                                <span>+{{ round($rel->elevation_gain_m ?? 0) }}m</span>
                                <span class="text-slate-300 font-semibold group-hover:text-[#FC4C02] transition-colors">Detail &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

<!-- ========================================================================= -->
<!-- FULLSCREEN RUNNING NAVIGATION HUD (High-Contrast, Utility-First) -->
<!-- ========================================================================= -->
<div id="gpx-nav-hud" class="fixed inset-0 z-[99999] bg-[#070B12] text-white flex flex-col hidden select-none">
    <!-- Top HUD Bar -->
    <div class="absolute top-0 left-0 right-0 z-30 p-3 bg-gradient-to-b from-[#070B12] via-[#070B12]/90 to-transparent flex items-center justify-between gap-2.5 pointer-events-auto">
        <!-- Status Banner -->
        <div id="nav-banner-status" class="flex-1 bg-[#111724]/95 border border-slate-700/80 rounded-lg px-3 py-2 flex items-center gap-2.5 shadow-lg">
            <div id="nav-status-icon" class="w-8 h-8 rounded bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                <i class="fa-solid fa-location-arrow text-xs"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div id="nav-status-title" class="text-[10px] font-mono font-bold uppercase text-emerald-400 tracking-wider truncate">
                    Mencari Sinyal GPS...
                </div>
                <div id="nav-status-desc" class="text-xs font-semibold text-white truncate">
                    Menghubungkan ke satelit GPS
                </div>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" id="nav-btn-audio" onclick="toggleNavAudio()" class="w-9 h-9 rounded-lg bg-[#111724]/95 border border-slate-700 text-slate-200 hover:text-white flex items-center justify-center transition cursor-pointer shadow" title="Suara Navigasi">
                <i id="nav-audio-icon" class="fa-solid fa-volume-high text-xs text-[#FC4C02]"></i>
            </button>
            <button type="button" onclick="closeLiveNavigation()" class="w-9 h-9 rounded-lg bg-[#111724]/95 border border-slate-700 text-slate-300 hover:text-red-400 flex items-center justify-center transition cursor-pointer shadow" title="Keluar Navigasi">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Fullscreen Leaflet Map -->
    <div id="nav-fullscreen-map" class="flex-1 w-full h-full relative z-10"></div>

    <!-- Recenter Button -->
    <button type="button" id="nav-btn-recenter" onclick="recenterNavMap()" class="absolute right-3.5 bottom-36 z-30 w-10 h-10 rounded-lg bg-[#111724]/95 border border-slate-700 text-[#FC4C02] hover:text-white flex items-center justify-center transition cursor-pointer shadow-lg" title="Kunci Posisi Saya">
        <i class="fa-solid fa-crosshairs text-sm"></i>
    </button>

    <!-- Bottom Running Metrics HUD Bar -->
    <div class="absolute bottom-0 left-0 right-0 z-30 p-3 bg-gradient-to-t from-[#070B12] via-[#070B12]/95 to-transparent pointer-events-auto">
        <div class="max-w-lg mx-auto bg-[#111724]/95 border border-slate-700/90 rounded-xl p-3.5 shadow-xl space-y-2.5">
            <!-- 3 Main Metrics -->
            <div class="grid grid-cols-3 gap-2 text-center divide-x divide-slate-800">
                <div>
                    <span class="text-[10px] font-mono text-slate-400 font-medium uppercase block">Pace Saat Ini</span>
                    <span id="nav-live-pace" class="text-xl font-mono font-bold text-white mt-0.5 block">--:--</span>
                    <span class="text-[9px] font-mono text-slate-500 block">/km</span>
                </div>
                <div>
                    <span class="text-[10px] font-mono text-slate-400 font-medium uppercase block">Sisa Jarak</span>
                    <span id="nav-remaining-dist" class="text-xl font-mono font-bold text-[#FC4C02] mt-0.5 block">{{ $formattedDist }}</span>
                    <span class="text-[9px] font-mono text-slate-500 block">km</span>
                </div>
                <div>
                    <span class="text-[10px] font-mono text-slate-400 font-medium uppercase block">Waktu Lari</span>
                    <span id="nav-running-time" class="text-xl font-mono font-bold text-white mt-0.5 block">00:00</span>
                    <span class="text-[9px] font-mono text-slate-500 block" id="nav-eta-label">Durasi</span>
                </div>
            </div>

            <!-- Route Progress Bar -->
            <div>
                <div class="flex justify-between text-[10px] font-mono text-slate-400 mb-1">
                    <span>Progress: <strong id="nav-progress-pct" class="text-white font-semibold">0%</strong></span>
                    <span id="nav-completed-dist">0.00 / {{ $formattedDist }} km</span>
                </div>
                <div class="w-full h-1 bg-slate-800 rounded overflow-hidden">
                    <div id="nav-progress-bar" class="h-full bg-[#FC4C02] transition-all duration-300 w-0"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const rawRouteCoords = {!! $coordsJson !!};
        const totalRouteDistance = {{ $distKm > 0 ? $distKm : 10.0 }};
        const totalElevationGain = {{ $gainM }};
        const routeTitle = "{{ addslashes($item->title) }}";

        let map = null;
        let polyline = null;
        let hoverMarker = null;
        let routePoints = []; // Array of {lat, lng, ele, dist}
        let elevationByKm = []; // Gain per KM
        let lastPaceTableData = [];

        // Navigation Mode State
        let navMap = null;
        let navPolyline = null;
        let navUserMarker = null;
        let navAccuracyCircle = null;
        let navWatchId = null;
        let navWakeLock = null;
        let navAudioEnabled = true;
        let navStartTime = null;
        let navTimerInterval = null;
        let navLastPassedKm = 0;
        let navIsOffCourse = false;
        let navUserPos = null;
        let navLastSpokenTime = 0;

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            initElevationProfile();
            initPacePro();
        });

        // 1. Map Initialization (Strava Athletic Orange Theme + KM Split Markers)
        function initMap() {
            const tileUrl = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

            map = L.map('gpx-detail-map', {
                zoomControl: true,
                scrollWheelZoom: false,
                dragging: true,
            }).setView([-6.2088, 106.8456], 12);

            L.tileLayer(tileUrl, {
                maxZoom: 19,
                subdomains: 'abcd'
            }).addTo(map);

            function parseCoordinatePoint(item, idx) {
                if (!item) return null;
                let lat = null, lng = null, ele = null, dist = null;
                if (Array.isArray(item)) {
                    lat = parseFloat(item[0]);
                    lng = parseFloat(item[1]);
                    if (item.length > 2 && item[2] !== null) ele = parseFloat(item[2]);
                } else if (typeof item === 'object') {
                    lat = parseFloat(item.lat !== undefined ? item.lat : (item.latitude !== undefined ? item.latitude : item[0]));
                    lng = parseFloat(item.lng !== undefined ? item.lng : (item.lon !== undefined ? item.lon : (item.longitude !== undefined ? item.longitude : item[1])));
                    if (item.ele !== undefined && item.ele !== null) ele = parseFloat(item.ele);
                    if (item.dist !== undefined && item.dist !== null) dist = parseFloat(item.dist);
                }
                if (!isNaN(lat) && !isNaN(lng) && isFinite(lat) && isFinite(lng)) {
                    return { lat: lat, lng: lng, ele: ele, dist: dist };
                }
                return null;
            }

            let validCoords = [];
            let cumulativeKm = 0;

            if (Array.isArray(rawRouteCoords)) {
                rawRouteCoords.forEach((pt, i) => {
                    const parsed = parseCoordinatePoint(pt, i);
                    if (parsed) {
                        if (parsed.dist === null || parsed.dist === undefined) {
                            if (validCoords.length > 0) {
                                const prev = validCoords[validCoords.length - 1];
                                cumulativeKm += calculateHaversine(prev.lat, prev.lng, parsed.lat, parsed.lng);
                            }
                            parsed.dist = cumulativeKm;
                        }
                        validCoords.push(parsed);
                    }
                });
            }

            routePoints = validCoords;

            if (validCoords.length > 0) {
                const latlngs = validCoords.map(p => [p.lat, p.lng]);

                // Strava Athletic Orange Polyline (#FC4C02)
                polyline = L.polyline(latlngs, {
                    color: '#FC4C02',
                    weight: 4,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(map);

                // Start Marker (Emerald Green)
                const startIcon = L.divIcon({
                    className: 'custom-start-marker',
                    html: '<div class="w-5 h-5 rounded-full bg-emerald-600 border border-white text-[9px] font-mono font-bold text-white flex items-center justify-center shadow">S</div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                // Finish Marker (Strava Orange)
                const finishIcon = L.divIcon({
                    className: 'custom-finish-marker',
                    html: '<div class="w-5 h-5 rounded-full bg-[#FC4C02] border border-white text-[9px] font-mono font-bold text-white flex items-center justify-center shadow">F</div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                // KM Split Markers (1 km, 2 km, 3 km, ...)
                const totalKmWhole = Math.floor(validCoords[validCoords.length - 1]?.dist || totalRouteDistance);
                let currentTargetKm = 1;

                validCoords.forEach((p) => {
                    if (currentTargetKm <= totalKmWhole && p.dist >= currentTargetKm) {
                        const kmBadgeIcon = L.divIcon({
                            className: 'custom-km-marker',
                            html: `<div class="w-4 h-4 rounded-full bg-[#0d121c] border border-slate-500 text-white text-[8px] font-mono font-bold flex items-center justify-center shadow select-none">${currentTargetKm}</div>`,
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        L.marker([p.lat, p.lng], { icon: kmBadgeIcon })
                            .addTo(map)
                            .bindPopup(`<div style="font-family:sans-serif; font-size:12px;"><b>Kilometer ${currentTargetKm}</b><br><span style="color:#64748b;">${p.ele ? 'Ketinggian: ' + Math.round(p.ele) + 'm' : ''}</span></div>`);
                        
                        currentTargetKm++;
                    }
                });

                // Hover Tracker Marker
                const hoverIcon = L.divIcon({
                    className: 'custom-hover-marker',
                    html: '<div class="w-3 h-3 rounded-full bg-[#FC4C02] border border-white shadow"></div>',
                    iconSize: [12, 12],
                    iconAnchor: [6, 6]
                });

                hoverMarker = L.marker(latlngs[0], { icon: hoverIcon, zIndexOffset: 1000 });

                L.marker(latlngs[0], { icon: startIcon }).addTo(map).bindPopup('<b style="color:#000;">Titik Mulai (Start)</b>');
                L.marker(latlngs[latlngs.length - 1], { icon: finishIcon }).addTo(map).bindPopup('<b style="color:#000;">Titik Selesai (Finish)</b>');

                const bounds = polyline.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [30, 30] });
                } else {
                    map.setView(latlngs[0], 13);
                }
            }
        }

        // 2. Elevation Profile Graph
        function initElevationProfile() {
            const svg = document.getElementById('gpx-elev-svg');
            const areaPath = document.getElementById('elev-area-path');
            const linePath = document.getElementById('elev-line-path');
            const container = document.getElementById('elevation-chart-container');
            const minEl = document.getElementById('elev-min-val');
            const maxEl = document.getElementById('elev-max-val');
            const hoverLine = document.getElementById('elev-hover-line');
            const hoverDot = document.getElementById('elev-hover-dot');
            const tooltip = document.getElementById('elev-tooltip');
            const tooltipKm = document.getElementById('elev-tooltip-km');
            const tooltipM = document.getElementById('elev-tooltip-m');

            if (!container || !areaPath || !linePath) return;

            let pointsWithEle = routePoints.filter(p => p.ele !== null && !isNaN(p.ele));

            if (pointsWithEle.length < 5) {
                const totalPts = routePoints.length || 50;
                pointsWithEle = routePoints.map((p, idx) => {
                    const progress = idx / totalPts;
                    const simEle = 15 + Math.sin(progress * Math.PI * 3) * 10 + Math.cos(progress * Math.PI * 5) * 6;
                    return {
                        lat: p.lat,
                        lng: p.lng,
                        dist: p.dist || (progress * totalRouteDistance),
                        ele: Math.max(5, Math.round(simEle))
                    };
                });
            }

            const elevations = pointsWithEle.map(p => p.ele);
            const minElev = Math.min(...elevations);
            const maxElev = Math.max(...elevations);
            const elevRange = Math.max(maxElev - minElev, 10);

            if (minEl) minEl.textContent = Math.round(minElev) + 'm';
            if (maxEl) maxEl.textContent = Math.round(maxElev) + 'm';

            const maxDist = pointsWithEle[pointsWithEle.length - 1]?.dist || totalRouteDistance || 1;

            function drawElevationSvg() {
                const width = container.clientWidth || 800;
                const height = container.clientHeight || 112;
                svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

                const padTop = 8;
                const padBottom = 8;
                const chartHeight = height - padTop - padBottom;

                const pathData = [];
                pointsWithEle.forEach(p => {
                    const x = (p.dist / maxDist) * width;
                    const normEle = (p.ele - minElev) / elevRange;
                    const y = height - padBottom - (normEle * chartHeight);
                    pathData.push({ x, y, dist: p.dist, ele: p.ele, lat: p.lat, lng: p.lng });
                });

                if (pathData.length === 0) return;

                let lineD = `M ${pathData[0].x.toFixed(1)} ${pathData[0].y.toFixed(1)}`;
                for (let i = 1; i < pathData.length; i++) {
                    lineD += ` L ${pathData[i].x.toFixed(1)} ${pathData[i].y.toFixed(1)}`;
                }

                const lastX = pathData[pathData.length - 1].x;
                const areaD = lineD + ` L ${lastX.toFixed(1)} ${height} L 0 ${height} Z`;

                linePath.setAttribute('d', lineD);
                areaPath.setAttribute('d', areaD);

                calculateElevationGainPerKm(pointsWithEle, maxDist);
            }

            drawElevationSvg();
            window.addEventListener('resize', drawElevationSvg);

            // Interactive Elevation Hover
            container.addEventListener('mousemove', function(e) {
                const rect = container.getBoundingClientRect();
                const mouseX = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
                const hoverDist = (mouseX / rect.width) * maxDist;

                let closest = pointsWithEle[0];
                let minDiff = 999999;
                for (let i = 0; i < pointsWithEle.length; i++) {
                    const diff = Math.abs(pointsWithEle[i].dist - hoverDist);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closest = pointsWithEle[i];
                    }
                }

                if (closest) {
                    const normEle = (closest.ele - minElev) / elevRange;
                    const height = container.clientHeight || 112;
                    const chartHeight = height - 16;
                    const y = height - 8 - (normEle * chartHeight);

                    hoverLine.style.left = mouseX + 'px';
                    hoverLine.classList.remove('hidden');

                    hoverDot.style.left = mouseX + 'px';
                    hoverDot.style.top = y + 'px';
                    hoverDot.classList.remove('hidden');

                    tooltip.style.left = mouseX + 'px';
                    tooltip.classList.remove('hidden');
                    tooltipKm.textContent = closest.dist.toFixed(2) + ' KM';
                    tooltipM.textContent = Math.round(closest.ele) + 'm';

                    if (map && hoverMarker && closest.lat && closest.lng) {
                        if (!map.hasLayer(hoverMarker)) hoverMarker.addTo(map);
                        hoverMarker.setLatLng([closest.lat, closest.lng]);
                    }
                }
            });

            container.addEventListener('mouseleave', function() {
                hoverLine.classList.add('hidden');
                hoverDot.classList.add('hidden');
                tooltip.classList.add('hidden');
                if (map && hoverMarker && map.hasLayer(hoverMarker)) {
                    map.removeLayer(hoverMarker);
                }
            });
        }

        function calculateElevationGainPerKm(points, totalDist) {
            const totalKm = Math.ceil(totalDist);
            elevationByKm = [];
            let currentKm = 1;
            let kmGain = 0;
            let lastEle = points[0] ? points[0].ele : 0;

            points.forEach(p => {
                if (p.dist >= currentKm) {
                    elevationByKm.push(Math.round(kmGain));
                    kmGain = 0;
                    currentKm += 1;
                }
                if (p.ele > lastEle) {
                    kmGain += (p.ele - lastEle);
                }
                lastEle = p.ele;
            });
            elevationByKm.push(Math.round(kmGain));
        }

        // 3. PacePro Engine
        function initPacePro() {
            const btnGenerate = document.getElementById('pp-btn-generate');
            const sliderStrategy = document.getElementById('pp-strategy-slider');
            const sliderHill = document.getElementById('pp-hill-slider');
            const labelStrategy = document.getElementById('pp-strategy-label');
            const labelHill = document.getElementById('pp-hill-label');
            const badgeStrategy = document.getElementById('pp-strategy-badge');

            if (sliderStrategy) {
                sliderStrategy.addEventListener('input', function() {
                    const val = parseInt(this.value, 10);
                    let label = 'Even (0%)';
                    if (val < 0) label = `Negative (${val}%)`;
                    else if (val > 0) label = `Positive (+${val}%)`;
                    
                    if (labelStrategy) labelStrategy.textContent = label;
                    if (badgeStrategy) badgeStrategy.textContent = label;
                    calculatePaceProPlan();
                });
            }

            if (sliderHill) {
                sliderHill.addEventListener('input', function() {
                    const val = parseInt(this.value, 10);
                    let label = 'Normal (0)';
                    if (val < 0) label = `Agresif (${val})`;
                    else if (val > 0) label = `Hemat Tenaga (+${val})`;
                    
                    if (labelHill) labelHill.textContent = label;
                    calculatePaceProPlan();
                });
            }

            if (btnGenerate) {
                btnGenerate.addEventListener('click', calculatePaceProPlan);
            }

            calculatePaceProPlan();
        }

        function setPacePreset(min, sec) {
            const dist = totalRouteDistance || 10;
            const totalSec = (min * 60 + sec) * dist;
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = Math.floor(totalSec % 60);

            document.getElementById('pp-time-h').value = h;
            document.getElementById('pp-time-m').value = m;
            document.getElementById('pp-time-s').value = s;

            calculatePaceProPlan();
        }

        function calculatePaceProPlan() {
            const h = parseInt(document.getElementById('pp-time-h')?.value || '0', 10) || 0;
            const m = parseInt(document.getElementById('pp-time-m')?.value || '0', 10) || 0;
            const s = parseInt(document.getElementById('pp-time-s')?.value || '0', 10) || 0;

            const totalTargetSec = (h * 3600) + (m * 60) + s;
            if (totalTargetSec <= 0) return;

            const totalDist = totalRouteDistance || 10;
            const avgPaceSec = totalTargetSec / totalDist;

            const avgPaceEl = document.getElementById('pp-avg-pace');
            const totalTimeEl = document.getElementById('pp-target-total-time');
            const tableBody = document.getElementById('pp-table-body');

            if (avgPaceEl) avgPaceEl.textContent = formatPaceTime(avgPaceSec) + ' /km';
            if (totalTimeEl) totalTimeEl.textContent = formatDurationTime(totalTargetSec);
            if (!tableBody) return;

            tableBody.innerHTML = '';

            const stratVal = parseInt(document.getElementById('pp-strategy-slider')?.value || '0', 10) || 0;
            const intensity = stratVal / 100;
            const startFactor = 1 - intensity;
            const endFactor = 1 + intensity;

            const hillVal = parseInt(document.getElementById('pp-hill-slider')?.value || '0', 10) || 0;
            const hillFactor = 1 + (hillVal / 20);

            const totalKm = Math.ceil(totalDist);
            let rawSplits = [];
            let rawSumSec = 0;

            for (let km = 1; km <= totalKm; km++) {
                let distInKm = 1;
                if (km > totalDist) {
                    distInKm = totalDist - (km - 1);
                }

                const progress = (km - 1) / (totalKm - 1 || 1);
                const currentFactor = startFactor + (endFactor - startFactor) * progress;
                let targetPaceSec = avgPaceSec * currentFactor;

                const gain = elevationByKm[km - 1] || 0;
                if (gain > 0) {
                    targetPaceSec += (gain / 10) * 1.5 * hillFactor;
                }

                const splitTimeSec = targetPaceSec * distInKm;
                rawSumSec += splitTimeSec;

                rawSplits.push({
                    km: km,
                    dist: distInKm,
                    rawTime: splitTimeSec,
                    gain: gain
                });
            }

            const timeDiff = totalTargetSec - rawSumSec;
            const adjPerKm = timeDiff / totalDist;
            let cumSec = 0;
            lastPaceTableData = [];

            rawSplits.forEach(s => {
                const adjTime = Math.max(1, s.rawTime + (adjPerKm * s.dist));
                cumSec += adjTime;
                const pacePerKm = adjTime / s.dist;

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-800/40 transition-colors';

                const elevText = s.gain > 0 ? `+${s.gain}m` : '-';
                const elevClass = s.gain > 15 ? 'text-[#FC4C02] font-semibold' : (s.gain > 0 ? 'text-slate-200' : 'text-slate-500');

                tr.innerHTML = `
                    <td class="py-2 px-3.5 font-medium text-slate-300">${s.km}${s.dist < 1 ? ` (${(s.dist * 1000).toFixed(0)}m)` : ''}</td>
                    <td class="py-2 px-3.5 text-white font-semibold">${formatPaceTime(pacePerKm)}</td>
                    <td class="py-2 px-3.5 text-slate-300">${formatDurationTime(adjTime)}</td>
                    <td class="py-2 px-3.5 text-slate-400">${formatDurationTime(cumSec)}</td>
                    <td class="py-2 px-3.5 text-right ${elevClass}">${elevText}</td>
                `;
                tableBody.appendChild(tr);

                lastPaceTableData.push({
                    km: s.km,
                    pace: formatPaceTime(pacePerKm),
                    split: formatDurationTime(adjTime),
                    cum: formatDurationTime(cumSec),
                    elev: elevText
                });
            });
        }

        // =========================================================================
        // 4. LIVE GPS NAVIGATION MODE ENGINE (Option A Fullscreen HUD + Sound)
        // =========================================================================
        function startLiveNavigation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung sensor geolokasi GPS.');
                return;
            }

            const hudEl = document.getElementById('gpx-nav-hud');
            if (hudEl) hudEl.classList.remove('hidden');

            // Lock screen from sleeping
            requestScreenWakeLock();

            // Initialize Nav Map
            if (!navMap) {
                initNavMap();
            } else {
                navMap.invalidateSize();
            }

            navStartTime = Date.now();
            navLastPassedKm = 0;
            navIsOffCourse = false;
            navLastSpokenTime = 0;

            // Start Running Timer
            if (navTimerInterval) clearInterval(navTimerInterval);
            navTimerInterval = setInterval(updateNavTimer, 1000);

            // Voice Welcome
            speakVoice(`Navigasi rute dimulai. Ikuti jalur peta.`);

            // Watch Position
            navWatchId = navigator.geolocation.watchPosition(
                handleNavGpsSuccess,
                handleNavGpsError,
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 10000
                }
            );
        }

        function initNavMap() {
            const tileUrl = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

            navMap = L.map('nav-fullscreen-map', {
                zoomControl: false,
                scrollWheelZoom: true,
                dragging: true,
            }).setView([-6.2088, 106.8456], 15);

            L.tileLayer(tileUrl, { maxZoom: 19, subdomains: 'abcd' }).addTo(navMap);

            if (routePoints.length > 0) {
                const latlngs = routePoints.map(p => [p.lat, p.lng]);
                navPolyline = L.polyline(latlngs, {
                    color: '#FC4C02',
                    weight: 5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(navMap);

                // Add KM Markers on Nav Map
                const totalKmWhole = Math.floor(routePoints[routePoints.length - 1]?.dist || totalRouteDistance);
                let curKm = 1;
                routePoints.forEach(p => {
                    if (curKm <= totalKmWhole && p.dist >= curKm) {
                        const kmIcon = L.divIcon({
                            className: 'nav-km-marker',
                            html: `<div class="w-5 h-5 rounded-full bg-[#070B12] border border-slate-300 text-white text-[9px] font-mono font-bold flex items-center justify-center shadow">${curKm}</div>`,
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });
                        L.marker([p.lat, p.lng], { icon: kmIcon }).addTo(navMap);
                        curKm++;
                    }
                });

                // User Location Marker (Blue Pulse Dot)
                const userDotIcon = L.divIcon({
                    className: 'nav-user-dot',
                    html: `
                        <div class="relative flex items-center justify-center w-7 h-7">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-60"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border border-white shadow-lg"></span>
                        </div>
                    `,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                navUserMarker = L.marker(latlngs[0], { icon: userDotIcon, zIndexOffset: 2000 }).addTo(navMap);
                navAccuracyCircle = L.circle(latlngs[0], { radius: 15, color: '#3b82f6', fillOpacity: 0.12, weight: 1 }).addTo(navMap);

                navMap.fitBounds(navPolyline.getBounds(), { padding: [35, 35] });
            }
        }

        function handleNavGpsSuccess(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const accuracy = pos.coords.accuracy || 10;
            const speedMps = pos.coords.speed;

            navUserPos = { lat, lng };

            if (navUserMarker) navUserMarker.setLatLng([lat, lng]);
            if (navAccuracyCircle) {
                navAccuracyCircle.setLatLng([lat, lng]);
                navAccuracyCircle.setRadius(Math.min(accuracy, 50));
            }

            // Calculate Distance to nearest point
            let nearestPoint = routePoints[0];
            let minDistanceM = 999999;

            routePoints.forEach((p) => {
                const distM = calculateHaversine(lat, lng, p.lat, p.lng) * 1000;
                if (distM < minDistanceM) {
                    minDistanceM = distM;
                    nearestPoint = p;
                }
            });

            // Off-course threshold: 45m
            const isOffCourseNow = minDistanceM > 45;

            const bannerIcon = document.getElementById('nav-status-icon');
            const bannerTitle = document.getElementById('nav-status-title');
            const bannerDesc = document.getElementById('nav-status-desc');

            if (isOffCourseNow) {
                if (!navIsOffCourse) {
                    navIsOffCourse = true;
                    if (bannerIcon) {
                        bannerIcon.className = 'w-8 h-8 rounded bg-red-600/20 border border-red-500/30 flex items-center justify-center text-red-400 shrink-0';
                        bannerIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-xs"></i>';
                    }
                    if (bannerTitle) {
                        bannerTitle.className = 'text-[10px] font-mono font-bold uppercase text-red-400 tracking-wider truncate';
                        bannerTitle.textContent = 'Melenceng dari Jalur';
                    }
                    if (bannerDesc) {
                        bannerDesc.textContent = `${Math.round(minDistanceM)} meter di luar jalur rute`;
                    }

                    vibratePhone([200, 100, 200]);
                    speakVoice(`Peringatan, Anda melenceng ${Math.round(minDistanceM)} meter dari jalur rute.`);
                }
            } else {
                if (navIsOffCourse) {
                    navIsOffCourse = false;
                    speakVoice(`Anda telah kembali ke jalur rute.`);
                }

                if (bannerIcon) {
                    bannerIcon.className = 'w-8 h-8 rounded bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0';
                    bannerIcon.innerHTML = '<i class="fa-solid fa-location-arrow text-xs"></i>';
                }
                if (bannerTitle) {
                    bannerTitle.className = 'text-[10px] font-mono font-bold uppercase text-emerald-400 tracking-wider truncate';
                    bannerTitle.textContent = 'Di Jalur Rute';
                }

                const currentKmProgress = nearestPoint.dist || 0;
                const nextKmTarget = Math.floor(currentKmProgress) + 1;
                const distToNextKm = (nextKmTarget - currentKmProgress) * 1000;

                if (nextKmTarget <= totalRouteDistance) {
                    if (bannerDesc) bannerDesc.textContent = `${Math.round(distToNextKm)}m menuju KM ${nextKmTarget}`;
                } else {
                    const distToFinishM = (totalRouteDistance - currentKmProgress) * 1000;
                    if (bannerDesc) bannerDesc.textContent = `${Math.round(Math.max(0, distToFinishM))}m menuju Finish`;
                }

                const completedKmInt = Math.floor(currentKmProgress);
                if (completedKmInt > navLastPassedKm && completedKmInt > 0) {
                    navLastPassedKm = completedKmInt;
                    const remaining = (totalRouteDistance - currentKmProgress).toFixed(1);
                    speakVoice(`Kilometer ${completedKmInt} terlewati. Sisa jarak ${remaining} kilometer.`);
                    vibratePhone([150, 100]);
                }
            }

            const completedDist = Math.min(totalRouteDistance, nearestPoint.dist || 0);
            const remainingDist = Math.max(0, totalRouteDistance - completedDist);
            const progressPct = Math.min(100, Math.round((completedDist / totalRouteDistance) * 100));

            const progBar = document.getElementById('nav-progress-bar');
            const progPctEl = document.getElementById('nav-progress-pct');
            const compDistEl = document.getElementById('nav-completed-dist');
            const remDistEl = document.getElementById('nav-remaining-dist');

            if (progBar) progBar.style.width = `${progressPct}%`;
            if (progPctEl) progPctEl.textContent = `${progressPct}%`;
            if (compDistEl) compDistEl.textContent = `${completedDist.toFixed(2)} / ${totalRouteDistance.toFixed(2)} km`;
            if (remDistEl) remDistEl.textContent = remainingDist.toFixed(2);

            const livePaceEl = document.getElementById('nav-live-pace');
            if (livePaceEl) {
                if (speedMps && speedMps > 0.5) {
                    const secPerKm = 1000 / speedMps;
                    livePaceEl.textContent = formatPaceTime(secPerKm);
                } else {
                    livePaceEl.textContent = '--:--';
                }
            }

            if (navMap) {
                navMap.panTo([lat, lng], { animate: true, duration: 0.8 });
            }
        }

        function handleNavGpsError(err) {
            console.warn('GPS Error:', err);
            const bannerTitle = document.getElementById('nav-status-title');
            const bannerDesc = document.getElementById('nav-status-desc');
            if (bannerTitle) {
                bannerTitle.className = 'text-[10px] font-mono font-bold uppercase text-amber-400 tracking-wider truncate';
                bannerTitle.textContent = 'Menunggu Sinyal GPS';
            }
            if (bannerDesc) {
                bannerDesc.textContent = 'Pastikan sensor GPS HP aktif dan izin browser disetujui.';
            }
        }

        function recenterNavMap() {
            if (navUserPos && navMap) {
                navMap.setView([navUserPos.lat, navUserPos.lng], 16, { animate: true });
            } else if (navPolyline && navMap) {
                navMap.fitBounds(navPolyline.getBounds(), { padding: [35, 35] });
            }
        }

        function updateNavTimer() {
            if (!navStartTime) return;
            const elapsedSec = Math.floor((Date.now() - navStartTime) / 1000);
            const timeEl = document.getElementById('nav-running-time');
            if (timeEl) timeEl.textContent = formatDurationTime(elapsedSec);
        }

        function closeLiveNavigation() {
            const hudEl = document.getElementById('gpx-nav-hud');
            if (hudEl) hudEl.classList.add('hidden');

            if (navWatchId) {
                navigator.geolocation.clearWatch(navWatchId);
                navWatchId = null;
            }

            if (navTimerInterval) {
                clearInterval(navTimerInterval);
                navTimerInterval = null;
            }

            releaseScreenWakeLock();

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        }

        function toggleNavAudio() {
            navAudioEnabled = !navAudioEnabled;
            const icon = document.getElementById('nav-audio-icon');
            if (icon) {
                if (navAudioEnabled) {
                    icon.className = 'fa-solid fa-volume-high text-xs text-[#FC4C02]';
                    speakVoice('Suara navigasi aktif.');
                } else {
                    icon.className = 'fa-solid fa-volume-xmark text-xs text-slate-500';
                    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                }
            }
        }

        function speakVoice(text) {
            if (!navAudioEnabled) return;
            const now = Date.now();
            if (now - navLastSpokenTime < 2500) return;
            navLastSpokenTime = now;

            try {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    utterance.rate = 1.05;
                    utterance.pitch = 1.0;
                    window.speechSynthesis.speak(utterance);
                } else {
                    playToneBeep(520, 180);
                }
            } catch (e) {
                playToneBeep(520, 180);
            }
        }

        function playToneBeep(freq, duration) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq || 440;
                gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + (duration / 1000));
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + (duration / 1000));
            } catch (e) {}
        }

        function vibratePhone(pattern) {
            try {
                if (navigator.vibrate) navigator.vibrate(pattern);
            } catch (e) {}
        }

        async function requestScreenWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    navWakeLock = await navigator.wakeLock.request('screen');
                }
            } catch (e) {
                console.log('Screen WakeLock:', e);
            }
        }

        function releaseScreenWakeLock() {
            if (navWakeLock) {
                navWakeLock.release().catch(() => {});
                navWakeLock = null;
            }
        }

        // =========================================================================
        // Helper Calculation Functions
        // =========================================================================
        function calculateHaversine(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function formatPaceTime(seconds) {
            if (isNaN(seconds) || !isFinite(seconds) || seconds <= 0 || seconds > 3600) return '--:--';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${String(s).padStart(2, '0')}`;
        }

        function formatDurationTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            if (h > 0) {
                return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            }
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }

        function copyPaceStrategy() {
            if (!lastPaceTableData || lastPaceTableData.length === 0) return;
            const title = "{{ addslashes($item->title) }}";
            let text = `STRATEGI PACEPRO: ${title}\n`;
            text += `Jarak: ${totalRouteDistance.toFixed(2)} km | Elevasi: +${totalElevationGain}m\n`;
            text += `Target: ${document.getElementById('pp-target-total-time')?.textContent || ''} (Avg Pace: ${document.getElementById('pp-avg-pace')?.textContent || ''})\n\n`;
            text += `KM | Pace | Split | Waktu Kumulatif\n`;
            text += `---------------------------------\n`;
            lastPaceTableData.forEach(r => {
                text += `KM ${r.km} : ${r.pace}/km | Split ${r.split} | ${r.cum}\n`;
            });
            text += `\nRuangLari PacePro: ${window.location.href}`;

            navigator.clipboard.writeText(text).then(() => {
                const btnText = document.getElementById('pp-copy-text');
                if (btnText) {
                    btnText.textContent = 'Tersalin!';
                    setTimeout(() => btnText.textContent = 'Salin', 2500);
                }
            });
        }

        function exportPaceCsv() {
            if (!lastPaceTableData || lastPaceTableData.length === 0) return;
            let csv = 'KM,Target Pace,Waktu Split,Waktu Kumulatif,Elevasi\n';
            lastPaceTableData.forEach(r => {
                csv += `${r.km},"${r.pace}","/km","${r.split}","${r.cum}","${r.elev}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `PacePro_${slugify("{{ $item->title }}")}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '_')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '_');
        }

        function shareGpxRoute() {
            if (navigator.share) {
                navigator.share({
                    title: "{{ e($item->title) }}",
                    text: "{{ e($pageDesc) }}",
                    url: window.location.href,
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link rute GPX berhasil disalin ke clipboard!');
                });
            }
        }
    </script>
@endpush
