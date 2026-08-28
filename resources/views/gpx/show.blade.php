@extends('layouts.pacerhub')

@php
    $formattedDist = number_format((float)($item->distance_km ?? 0), 2);
    $cityName = $item->city ?? 'Indonesia';
    $pageTitle = 'Download GPX ' . e($item->title) . ' (' . $formattedDist . ' km, ' . e($cityName) . ') - Rute Lari & Navigasi | RuangLari';
    $pageDesc = 'Download gratis file GPX rute lari ' . e($item->title) . ' (' . $formattedDist . ' km, elevasi +' . round($item->elevation_gain_m ?? 0) . 'm) di ' . e($cityName) . '. Kompatibel untuk Garmin, Coros, Suunto, Strava, navigasi GPS offline, dan kalkulator PacePro.';
    $coordsJson = json_encode($item->coordinates_json ?? []);
    $distKm = (float)($item->distance_km ?? 0);
    $gainM = (float)($item->elevation_gain_m ?? 0);
    $lossM = (float)($item->elevation_loss_m ?? 0);
    $masterGpxId = $item->id;
    $canonicalUrl = route('gpx.show', $item->slug ?: $item->id);
    $downloadUrl = route('gpx.download', $item->id);

    $defaultTargetSec = max(60, (int)round($distKm * 300));
    $defaultH = (int)floor($defaultTargetSec / 3600);
    $defaultM = (int)floor(($defaultTargetSec % 3600) / 60);
    $defaultS = (int)($defaultTargetSec % 60);
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)
@section('meta_keywords', 'download gpx ' . strtolower($item->title) . ', rute gpx ' . strtolower($item->title) . ', file gpx ' . strtolower($cityName) . ', gpx garmin ' . strtolower($cityName) . ', gpx strava route, rute lari ' . strtolower($cityName))
@section('canonical_url', $canonicalUrl)
@section('og_url', $canonicalUrl)
@section('og_type', 'website')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        .leaflet-div-icon {
            background: transparent !important;
            border: none !important;
        }
        #gpx-detail-map {
            height: 480px;
            width: 100%;
            background: #070B12;
            z-index: 1;
        }
        .leaflet-tile {
            filter: brightness(0.92) contrast(1.1);
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
        #gpx-nav-hud {
            background-color: #070B12 !important;
            background: #070B12 !important;
            z-index: 99999 !important;
        }
        .nav-solid-header {
            background-color: #070B12 !important;
            border-bottom: 1px solid #1E293B !important;
            z-index: 30 !important;
        }
        .nav-solid-bottom {
            background-color: #0B0F17 !important;
            border-top: 1px solid #1E293B !important;
            z-index: 30 !important;
            opacity: 1 !important;
        }
        .nav-metrics-box {
            background-color: #111724 !important;
            border: 1px solid #1E293B !important;
            opacity: 1 !important;
        }
        #post-run-modal {
            background-color: rgba(0, 0, 0, 0.88) !important;
            z-index: 999999 !important;
        }
        .post-run-card {
            background-color: #0F172A !important;
            border: 1px solid #334155 !important;
        }
        body.gpx-nav-active #chatbox-toggle,
        body.gpx-nav-active #ph-chatbox,
        body.gpx-nav-active .crisp-client,
        body.gpx-nav-active #crisp-chatbox {
            display: none !important;
        }

        /* Neon Glow Multi-layer Trail Effect */
        .leaflet-pane svg path.leaflet-interactive {
            filter: drop-shadow(0 0 6px rgba(252, 76, 2, 0.85)) drop-shadow(0 0 14px rgba(252, 76, 2, 0.45));
        }

        /* Runner Beacon Pulse Marker */
        .runner-beacon {
            position: relative;
            width: 32px;
            height: 32px;
        }
        .runner-beacon-inner {
            width: 100%;
            height: 100%;
            background: #FC4C02;
            border: 3px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px #FC4C02, 0 4px 10px rgba(0, 0, 0, 0.5);
            z-index: 2;
            position: relative;
        }
        .runner-arrow-rotator {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform;
        }
        .runner-beacon-ring {
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: rgba(252, 76, 2, 0.35);
            animation: beaconRipple 1.4s ease-out infinite;
            z-index: 1;
        }
        @keyframes beaconRipple {
            0% { transform: scale(0.6); opacity: 1; }
            100% { transform: scale(2.2); opacity: 0; }
        }
    </style>

    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="RuangLari">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
@endpush

@section('content')
<div class="min-h-screen pt-20 sm:pt-24 pb-20 px-3 sm:px-4 md:px-8 bg-[#0B0F17] text-slate-200 font-sans overflow-x-hidden w-full max-w-full">
    <div class="max-w-6xl mx-auto space-y-5 sm:space-y-6">
        
        <!-- Breadcrumb & Top Utility Bar -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 text-xs text-slate-200">
            <nav class="flex items-center gap-1.5 font-medium truncate max-w-[240px] sm:max-w-none">
                <a href="{{ route('gpx.index') }}" class="hover:text-slate-200 transition shrink-0">Database GPX</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('gpx.index', ['city' => $cityName]) }}" class="hover:text-white transition shrink-0 text-slate-300">{{ $cityName }}</a>
                <span class="text-slate-600">/</span>
                <span class="text-white font-semibold truncate">{{ $item->title }}</span>
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        onclick="openSuggestTitleModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ addslashes($cityName) }}')" 
                        class="px-2.5 py-1.5 rounded-md border border-slate-700/80 hover:border-accent/40 text-slate-300 hover:text-accent text-xs font-medium transition cursor-pointer flex items-center gap-1.5"
                        title="Sarankan Nama Rute Baru">
                    <i class="fa-solid fa-pen-to-square text-[11px] text-[#FC4C02]"></i>
                    <span>Saran Nama</span>
                </button>
                <button type="button" onclick="shareGpxRoute()" class="px-2.5 py-1.5 rounded-md border border-slate-700/80 hover:border-slate-600 text-slate-300 hover:text-white text-xs font-medium transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-share-nodes text-[11px] text-slate-200"></i>
                    <span>Bagikan</span>
                </button>
            </div>
        </div>

        <!-- Route Header & Primary Action Bar -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 sm:gap-6 pb-3 border-b border-slate-800/80">
            <div class="space-y-2 max-w-3xl">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs">
                    <a href="{{ route('gpx.index', ['city' => $cityName]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded border border-slate-700 bg-slate-850 hover:bg-slate-800 text-slate-300 hover:text-white font-medium text-[11px] transition">
                        <i class="fa-solid fa-location-dot text-[#FC4C02] text-[10px]"></i>
                        <span>{{ $cityName }}</span>
                    </a>

                    <span class="inline-flex items-center px-2 py-0.5 rounded border border-slate-700 bg-slate-850 text-slate-200 font-semibold text-[11px] font-mono uppercase tracking-wider">
                        {{ $item->route_type_label }}
                    </span>
                    
                    @if($item->event)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded border border-slate-700 bg-slate-850 text-slate-300 font-medium text-[11px]">
                            <span class="truncate max-w-[150px]">{{ $item->event->title ?? $item->event->name ?? 'Event Lari' }}</span>
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight text-white break-words">
                    Download GPX {{ $item->title }}
                </h1>

                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl font-normal">
                    {{ $item->description ?? $item->notes ?? 'File rute GPX lari terverifikasi untuk Garmin, Coros, Suunto, dan Strava dengan profil elevasi dan panduan strategi pacing.' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto shrink-0">
                <a href="{{ route('gpx.download', $item->id) }}" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-lg bg-[#FC4C02] hover:bg-[#e04300] text-white font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-2 shadow-sm cursor-pointer text-center">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download GPX</span>
                </a>

                <div class="grid grid-cols-3 sm:flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" onclick="startLiveNavigation()" class="px-3 py-2.5 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-850 hover:bg-slate-800 text-slate-200 font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-1.5 cursor-pointer text-center">
                        <i class="fa-solid fa-location-arrow text-xs text-[#FC4C02]"></i>
                        <span>Navigasi</span>
                    </button>

                    <button type="button" id="btn-offline-cache-toggle" onclick="openOfflineCacheModal()" class="px-3 py-2.5 rounded-lg border border-slate-750 hover:border-slate-700 text-slate-200 hover:text-white text-xs font-medium transition flex items-center justify-center gap-1.5 text-center cursor-pointer" title="Simpan Peta & Rute untuk Offline di Gunung">
                        <i class="fa-solid fa-cloud-arrow-down text-[11px] text-slate-300" id="icon-offline-btn"></i>
                        <span id="label-offline-btn">Offline</span>
                    </button>

                    <a href="{{ route('tools.buat-rute-lari', ['gpx_id' => $item->id]) }}" class="px-3 py-2.5 rounded-lg border border-slate-750 hover:border-slate-700 text-slate-200 hover:text-slate-200 text-xs font-medium transition flex items-center justify-center gap-1.5 text-center" title="Buka dan Edit di Route Builder">
                        <i class="fa-solid fa-draw-polygon text-[11px] text-[#FC4C02]"></i>
                        <span>Editor</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Consolidated Statistics Strip -->
        <div class="bg-[#111724] border border-slate-800/80 rounded-lg grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-800/80">
            <div class="p-3 sm:p-4">
                <span class="text-[11px] sm:text-xs font-medium text-slate-400 block">Jarak Total</span>
                <div class="mt-0.5 sm:mt-1 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold text-white tabular-nums">{{ $formattedDist }}</span>
                    <span class="text-xs text-slate-400 font-medium">km</span>
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <span class="text-[11px] sm:text-xs font-medium text-slate-400 block">Elevasi Naik</span>
                <div class="mt-0.5 sm:mt-1 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold text-white tabular-nums">+{{ number_format($gainM) }}</span>
                    <span class="text-xs text-slate-400 font-medium">m</span>
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <span class="text-[11px] sm:text-xs font-medium text-slate-400 block">Elevasi Turun</span>
                <div class="mt-0.5 sm:mt-1 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold text-white tabular-nums">-{{ number_format($lossM) }}</span>
                    <span class="text-xs text-slate-400 font-medium">m</span>
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <span class="text-[11px] sm:text-xs font-medium text-slate-400 block">Kontributor</span>
                <div class="mt-0.5 sm:mt-1 truncate">
                    <span class="text-xs sm:text-sm font-semibold text-white block truncate">{{ $item->user?->name ?? 'RuangLari' }}</span>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 block">{{ $item->created_at ? $item->created_at->format('d M Y') : 'Terverifikasi' }}</span>
                </div>
            </div>
        </div>

        <!-- Primary Map & Integrated Elevation Stage -->
        <div class="bg-[#111724] border border-slate-800/80 rounded-2xl shadow-2xl relative" id="gpx-map-card-wrap">
            <!-- Map Sub-header with Mode Switchers -->
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2.5 text-xs bg-[#0c121e] relative z-40 rounded-t-2xl">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FC4C02] shadow-[0_0_8px_#FC4C02]"></span>
                    <h2 class="font-bold text-white text-xs tracking-wide">Peta Interaktif Jalur GPX</h2>
                </div>

                <!-- Navigation Controls Toolbar -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <button type="button" id="btn-toggle-km-markers" onclick="toggleKmMarkers()" class="px-2 sm:px-2.5 py-1 rounded-lg bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-[11px] font-semibold text-slate-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm" title="Tampilkan / Sembunyikan Marker KM">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#FC4C02] text-white text-[8px] font-mono font-black flex items-center justify-center">1</span>
                        <span>KM</span>
                        <span id="badge-km-toggle-state" class="text-[9px] px-1 py-0.2 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold">ON</span>
                    </button>

                    <!-- Map Layer Switcher -->
                    <div class="relative inline-block z-50" id="map-layer-dropdown-wrapper">
                        <button type="button" id="btn-toggle-map-layer" onclick="toggleMapLayerMenu()" class="px-2 sm:px-2.5 py-1 rounded-lg bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-[11px] font-medium text-slate-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-layer-group text-[10px] text-[#FC4C02]"></i>
                            <span id="label-active-map-layer" class="hidden xs:inline">Voyager Light</span>
                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-400"></i>
                        </button>
                        <div id="map-layer-menu" class="hidden absolute right-0 top-full mt-1.5 bg-[#0c121e] border border-slate-700 rounded-xl shadow-2xl py-1.5 z-[99999] min-w-[140px] text-xs divide-y divide-slate-800" style="background-color: #0c121e !important; opacity: 1 !important;">
                            <button type="button" onclick="setMapLayer('street')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>Voyager Light</span>
                                <span class="layer-check-street text-[#FC4C02] text-[11px] font-bold"><i class="fa-solid fa-check"></i></span>
                            </button>
                            <button type="button" onclick="setMapLayer('dark')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>Dark Mode</span>
                                <span class="layer-check-dark text-[#FC4C02] text-[11px] font-bold hidden"><i class="fa-solid fa-check"></i></span>
                            </button>
                            <button type="button" onclick="setMapLayer('satellite')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>Satelit Topo</span>
                                <span class="layer-check-satellite text-[#FC4C02] text-[11px] font-bold hidden"><i class="fa-solid fa-check"></i></span>
                            </button>
                        </div>
                    </div>

                    <button type="button" id="btn-map-locate-me" onclick="locateMeOnDetailMap()" class="px-2 sm:px-2.5 py-1 rounded-lg bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-[11px] font-medium text-slate-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-location-crosshairs text-[10px] text-[#FC4C02]"></i>
                        <span class="hidden sm:inline">Lokasi Saya</span>
                    </button>

                    <button type="button" id="btn-map-fit-bounds" onclick="fitRouteBounds()" class="px-2 py-1 rounded-lg bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-[11px] font-medium text-slate-200 transition cursor-pointer flex items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-expand text-[10px]"></i>
                        <span class="hidden md:inline">Fit</span>
                    </button>

                    <button type="button" id="btn-toggle-animator-panel" onclick="toggleGpxAnimator()" class="px-3 py-1 rounded-lg bg-[#FC4C02] text-white font-bold text-[11px] hover:bg-[#e04300] transition cursor-pointer flex items-center gap-1.5 shadow-lg shadow-[#FC4C02]/30">
                        <i class="fa-solid fa-play text-[10px]" id="icon-anim-toggle-btn"></i>
                        <span>Flyover 3D</span>
                    </button>
                </div>
            </div>

            <!-- Leaflet Map Canvas & Floating HUD -->
            <div class="relative overflow-hidden">
                <div id="gpx-detail-map" class="relative"></div>

                <!-- Floating Live Telemetry HUD (Top-Right) matching UI reference -->
                <div id="gpx-live-telemetry-hud" class="hidden absolute top-3 right-3 sm:top-4 sm:right-4 z-[990] bg-[#070B12] border border-slate-700/90 rounded-[20px] p-3.5 sm:p-4 shadow-2xl space-y-3 w-[290px] sm:w-[320px] text-white select-none pointer-events-auto" style="background-color: #070B12 !important; opacity: 1 !important;">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse"></span>
                            <span class="text-xs font-bold font-mono tracking-wider text-slate-200 uppercase">LIVE TELEMETRY</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full border border-emerald-500/40 bg-emerald-950/80 text-emerald-400 text-[10px] font-bold tracking-wider uppercase font-mono">
                            2D EXPLORER
                        </span>
                    </div>

                    <!-- Main Elevation & Gauge Row -->
                    <div class="flex items-center justify-between gap-3 pt-0.5">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                                <i class="fa-solid fa-chart-line text-[#FC4C02] text-xs"></i>
                                <span>Ketinggian Rute</span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span id="anim-telemetry-elev" class="text-3xl sm:text-4xl font-black text-white tracking-tight font-mono">0</span>
                                <span class="text-xs sm:text-sm font-semibold text-slate-400">m mdpl</span>
                            </div>
                        </div>

                        <!-- Gauge Box -->
                        <div class="bg-[#111724] border border-slate-800 rounded-xl p-2 px-3 flex flex-col items-center justify-center shrink-0 w-16 sm:w-20">
                            <span class="text-[9px] font-bold text-slate-400 tracking-wider uppercase font-mono mb-1.5">GAUGE</span>
                            <div class="w-3.5 h-11 bg-slate-800 rounded-full p-0.5 relative overflow-hidden flex flex-col justify-end">
                                <div id="anim-gauge-bar" class="w-full bg-gradient-to-t from-emerald-500 via-yellow-400 to-[#FC4C02] rounded-full transition-all duration-150" style="height: 30%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 2 Sub Metric Cards -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Distance Card -->
                        <div class="bg-[#111724] border border-slate-800 rounded-xl p-2.5 space-y-1">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block font-mono">JARAK TEMPUH</span>
                            <div class="text-xs sm:text-sm font-bold text-white font-mono truncate">
                                <span id="anim-telemetry-km" class="text-white">0.00</span> / {{ $formattedDist }} KM
                            </div>
                        </div>

                        <!-- Slope Card -->
                        <div class="bg-[#111724] border border-slate-800 rounded-xl p-2.5 space-y-1">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block font-mono">KEMIRINGAN</span>
                            <div id="anim-telemetry-slope" class="text-xs sm:text-sm font-bold text-white font-mono truncate">
                                — 0.0% (Datar)
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Status Banner -->
                    <div class="bg-[#111724] border border-slate-800 rounded-xl px-3 py-2 flex items-center gap-2 text-xs font-semibold text-slate-200">
                        <i class="fa-solid fa-flag text-rose-500 text-xs shrink-0"></i>
                        <span id="anim-telemetry-status-text" class="truncate">Lintasan Lari KM 0.0 — Bersiap!</span>
                    </div>
                </div>

                <!-- Floating Replay Controller HUD (Bottom) -->
                <div id="gpx-animator-hud" class="hidden absolute bottom-3 left-3 right-3 z-[999] bg-[#070B12]/95 backdrop-blur-xl border border-slate-700/90 rounded-2xl p-3 sm:p-4 shadow-2xl space-y-2.5 transition-all">
                    <!-- Progress Scrubber Slider -->
                    <div class="relative flex flex-col space-y-1">
                        <input type="range" id="anim-progress-slider" min="0" max="1000" value="0" step="1"
                            class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-[#FC4C02] focus:outline-none"
                            oninput="onAnimSliderInput(this.value)">
                        <div class="flex justify-between text-[10px] font-mono text-slate-400">
                            <span>KM 0.0</span>
                            <span id="anim-telemetry-time" class="text-slate-300 font-bold">00:00:00</span>
                            <span>KM {{ $formattedDist }}</span>
                        </div>
                    </div>

                    <!-- Bottom Controls Line -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-800/80 text-xs">
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-anim-play-pause" onclick="togglePlayPauseAnimation()" class="px-3.5 py-1.5 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-bold text-xs transition cursor-pointer flex items-center gap-1.5 shadow-lg shadow-[#FC4C02]/25">
                                <i class="fa-solid fa-play text-[11px]" id="icon-anim-play"></i>
                                <span id="label-anim-play">Play</span>
                            </button>
                            <button type="button" onclick="restartAnimation()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs transition cursor-pointer flex items-center gap-1">
                                <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                <span>Reset</span>
                            </button>
                        </div>

                        <!-- Speed Toggle & Camera Follow -->
                        <div class="flex items-center gap-1.5 sm:gap-2 font-mono text-[11px]">
                            <div class="flex items-center gap-1 bg-slate-900 border border-slate-800 rounded-lg p-0.5" id="anim-speed-btn-group">
                                <button type="button" onclick="setAnimSpeed(1)" class="anim-speed-btn px-2 py-0.5 rounded text-[10px] text-slate-400 hover:text-white cursor-pointer" data-speed="1">1x</button>
                                <button type="button" onclick="setAnimSpeed(2)" class="anim-speed-btn px-2 py-0.5 rounded text-[10px] text-slate-400 hover:text-white cursor-pointer" data-speed="2">2x</button>
                                <button type="button" onclick="setAnimSpeed(5)" class="anim-speed-btn px-2 py-0.5 rounded text-[10px] bg-slate-800 text-white font-bold cursor-pointer" data-speed="5">5x</button>
                                <button type="button" onclick="setAnimSpeed(10)" class="anim-speed-btn px-2 py-0.5 rounded text-[10px] text-slate-400 hover:text-white cursor-pointer" data-speed="10">10x</button>
                            </div>

                            <button type="button" id="btn-anim-camera-follow" onclick="toggleCameraFollow()" class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-[10px] text-slate-300 hover:text-white transition cursor-pointer flex items-center gap-1">
                                <span class="hidden xs:inline">Follow</span>
                                <span id="badge-camera-follow" class="text-[9px] px-1 py-0.2 rounded bg-emerald-500/20 text-emerald-400 font-bold">ON</span>
                            </button>

                            <button type="button" onclick="toggleGpxAnimator()" class="p-1 text-slate-400 hover:text-white transition cursor-pointer ml-1" title="Tutup Animasi">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Elevation Profile (Synchronized with Map) -->
            <div class="p-4 bg-[#0D131F] border-t border-slate-800 space-y-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-300">
                    <div class="flex items-center gap-1.5 font-medium text-slate-300">
                        <i class="fa-solid fa-chart-area text-[#FC4C02] text-[11px]"></i>
                        <span>Profil Elevasi Rute</span>
                    </div>
                    <div class="flex items-center gap-3 font-mono text-[11px]">
                        <span>Min: <strong id="elev-min-val" class="text-white font-semibold">0m</strong></span>
                        <span>Max: <strong id="elev-max-val" class="text-white font-semibold">0m</strong></span>
                        <span>Gain: <strong id="elev-gain-val" class="text-emerald-400 font-semibold">+{{ round($gainM) }}m</strong></span>
                    </div>
                </div>

                <div class="relative w-full h-28 select-none" id="elevation-chart-container">
                    <svg id="gpx-elev-svg" class="w-full h-full block overflow-visible" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="elevGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#FC4C02" stop-opacity="0.45" />
                                <stop offset="100%" stop-color="#FC4C02" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path id="elev-area-path" fill="url(#elevGrad)" d="" />
                        <path id="elev-line-path" fill="none" stroke="#FC4C02" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="" />
                    </svg>

                    <div id="elev-hover-line" class="absolute top-0 bottom-0 w-[1.5px] bg-[#FC4C02] pointer-events-none hidden border-r border-dashed border-white/40"></div>
                    <div id="elev-hover-dot" class="absolute w-3 h-3 rounded-full bg-white border-2 border-[#FC4C02] pointer-events-none hidden -translate-x-1/2 -translate-y-1/2 shadow-lg shadow-[#FC4C02]"></div>
                    <div id="elev-tooltip" class="absolute hidden bg-[#070B12] border border-slate-700 text-white text-[11px] font-mono font-medium px-2 py-0.5 rounded shadow pointer-events-none z-20 whitespace-nowrap -top-7 -translate-x-1/2">
                        <span id="elev-tooltip-km">0.0 KM</span> • <span id="elev-tooltip-m" class="text-[#FC4C02] font-bold">0m</span>
                    </div>
                </div>

                <div class="flex justify-between items-center text-[10px] font-mono text-slate-500 pt-0.5">
                    <span>0 km</span>
                    <span id="elev-chart-mid-km">{{ number_format($distKm / 2, 1) }} km</span>
                    <span>{{ $formattedDist }} km</span>
                </div>
            </div>
        </div>

        <!-- PACEPRO RACE STRATEGY PLANNER -->
        <section id="gpx-pacepro-section" class="bg-[#111724] border border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-5 overflow-hidden shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-stopwatch text-[#FC4C02]"></i>
                        <span>PacePro™ Strategy Planner</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Rencana target split per kilometer berdasarkan profil tanjakan rute dan tipe pacing yang dipilih.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-slate-400 font-medium">Strategi:</span>
                    <span id="pp-strategy-badge" class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-xs font-semibold text-white">Even Split (0%)</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-12 gap-5">
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-[#0D131F] border border-slate-800 rounded-xl p-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Target Waktu Selesai (Default Pace 5:00/km)
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <input type="number" id="pp-time-h" min="0" max="23" value="{{ $defaultH }}" class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded-lg p-2 text-center text-white font-mono font-bold text-sm outline-none transition">
                                    <span class="text-[10px] text-slate-400 text-center block mt-1">Jam</span>
                                </div>
                                <div>
                                    <input type="number" id="pp-time-m" min="0" max="59" value="{{ $defaultM }}" class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded-lg p-2 text-center text-white font-mono font-bold text-sm outline-none transition">
                                    <span class="text-[10px] text-slate-400 text-center block mt-1">Menit</span>
                                </div>
                                <div>
                                    <input type="number" id="pp-time-s" min="0" max="59" value="{{ str_pad($defaultS, 2, '0', STR_PAD_LEFT) }}" class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded-lg p-2 text-center text-white font-mono font-bold text-sm outline-none transition">
                                    <span class="text-[10px] text-slate-400 text-center block mt-1">Detik</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[11px] font-medium text-slate-400 mb-1.5">Preset Target Pace:</span>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 text-center">
                                <button type="button" onclick="setPacePreset(4, 30)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 cursor-pointer transition">4:30</button>
                                <button type="button" onclick="setPacePreset(5, 0)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 cursor-pointer transition">5:00</button>
                                <button type="button" onclick="setPacePreset(5, 30)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 cursor-pointer transition">5:30</button>
                                <button type="button" onclick="setPacePreset(6, 0)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 cursor-pointer transition">6:00</button>
                                <button type="button" onclick="setPacePreset(6, 30)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 cursor-pointer transition">6:30</button>
                                <button type="button" onclick="setPacePreset(7, 0)" class="py-1 px-1 rounded bg-[#111724] hover:bg-slate-800 text-xs font-mono font-semibold text-slate-300 border border-slate-700 cursor-pointer transition">7:00</button>
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-slate-800">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-semibold text-slate-300">Pacing Split</label>
                                <span id="pp-strategy-label" class="text-xs font-semibold text-slate-200 font-mono">Even (0%)</span>
                            </div>
                            <input type="range" id="pp-strategy-slider" min="-10" max="10" value="0" step="1" class="w-full h-1 bg-slate-800 rounded appearance-none cursor-pointer accent-[#FC4C02]">
                        </div>

                        <div class="pt-2.5 border-t border-slate-800">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-semibold text-slate-300">Adaptasi Tanjakan</label>
                                <span id="pp-hill-label" class="text-xs font-semibold text-[#FC4C02] font-mono">Normal (0)</span>
                            </div>
                            <input type="range" id="pp-hill-slider" min="-10" max="10" value="0" step="1" class="w-full h-1 bg-slate-800 rounded appearance-none cursor-pointer accent-[#FC4C02]">
                        </div>

                        <button type="button" id="pp-btn-generate" class="w-full py-2.5 px-3 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-1.5 cursor-pointer shadow-lg shadow-[#FC4C02]/20">
                            <i class="fa-solid fa-calculator text-[11px]"></i>
                            <span>Hitung Ulang Split</span>
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-3">
                    <div class="bg-[#0D131F] border border-slate-800 rounded-xl p-3 grid grid-cols-3 divide-x divide-slate-800 text-center">
                        <div class="px-1">
                            <span class="text-[10px] sm:text-xs text-slate-400 font-medium block">Avg Pace</span>
                            <span id="pp-avg-pace" class="text-xs sm:text-base font-bold font-mono text-[#FC4C02] mt-0.5 block truncate">5:00 /km</span>
                        </div>
                        <div class="px-1">
                            <span class="text-[10px] sm:text-xs text-slate-400 font-medium block">Target Waktu</span>
                            <span id="pp-target-total-time" class="text-xs sm:text-base font-bold font-mono text-white mt-0.5 block truncate">00:00:00</span>
                        </div>
                        <div class="px-1">
                            <span class="text-[10px] sm:text-xs text-slate-400 font-medium block">Total Jarak</span>
                            <span class="text-xs sm:text-base font-bold font-mono text-slate-200 mt-0.5 block truncate">{{ $formattedDist }} km</span>
                        </div>
                    </div>

                    <div class="bg-[#0D131F] border border-slate-800 rounded-xl overflow-hidden">
                        <div class="px-3.5 py-2.5 bg-[#111724] border-b border-slate-800 flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-200 truncate">Tabel Split KM</span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" id="pp-btn-copy" onclick="copyPaceStrategy()" class="px-2.5 py-1 rounded-lg bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-xs font-medium text-slate-300 transition cursor-pointer flex items-center gap-1">
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                    <span id="pp-copy-text">Salin</span>
                                </button>
                                <button type="button" id="pp-btn-csv" onclick="exportPaceCsv()" class="px-2.5 py-1 rounded-lg bg-[#0D131F] hover:bg-slate-800 border border-slate-700 text-xs font-medium text-slate-300 transition cursor-pointer flex items-center gap-1">
                                    <i class="fa-solid fa-file-csv text-[10px] text-[#FC4C02]"></i>
                                    <span>CSV</span>
                                </button>
                            </div>
                        </div>

                        <div class="pacepro-table-wrap max-h-[360px] overflow-y-auto overflow-x-auto w-full">
                            <table class="w-full text-xs text-center table-fixed" id="pp-pace-table">
                                <thead class="bg-[#090D16] text-slate-300 uppercase text-[10px] sm:text-[11px] font-mono sticky top-0 border-b border-slate-800 z-10">
                                    <tr>
                                        <th class="py-2.5 px-1 sm:px-2 font-semibold text-center w-[16%]">KM</th>
                                        <th class="py-2.5 px-1 sm:px-2 font-semibold text-center w-[22%]">Pace</th>
                                        <th class="py-2.5 px-1 sm:px-2 font-semibold text-center w-[22%]">Split</th>
                                        <th class="py-2.5 px-1 sm:px-2 font-semibold text-center w-[23%]">Kumulatif</th>
                                        <th class="py-2.5 px-1 sm:px-2 font-semibold text-center w-[17%]">Elev</th>
                                    </tr>
                                </thead>
                                <tbody id="pp-table-body" class="divide-y divide-slate-800/80 font-mono text-slate-300 text-center">
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
            <div class="bg-[#111724] border border-slate-800/80 rounded-2xl p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 border-b border-slate-800 pb-2.5">
                    Informasi & Catatan Rute
                </h3>
                <div class="text-slate-300 text-sm leading-relaxed space-y-2.5">
                    @if($item->description)
                        <p class="whitespace-pre-line">{{ $item->description }}</p>
                    @endif
                    @if($item->notes && $item->notes !== $item->description)
                        <div class="p-3 rounded-xl bg-[#0D131F] border border-slate-800 text-xs text-slate-300">
                            <strong class="text-white block mb-0.5">Catatan Tambahan:</strong>
                            <p class="whitespace-pre-line">{{ $item->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>

<!-- Navigation HUD & Post Run Modal Partials -->
<div id="gpx-nav-hud" class="fixed inset-0 z-[99990] bg-[#070B12] text-white flex flex-col hidden select-none font-sans overflow-hidden">
    <div class="nav-solid-header absolute top-0 left-0 right-0 z-30 px-3 py-2.5 sm:px-4 sm:py-3 bg-[#070B12] border-b border-slate-800 flex items-center justify-between gap-3 shadow-2xl">
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <div id="nav-gps-indicator" class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></div>
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 block truncate" id="nav-status-label">
                    Navigasi GPS Aktif
                </span>
                <h2 class="text-xs sm:text-sm font-bold text-white truncate" id="nav-status-title">
                    {{ $item->title }}
                </h2>
            </div>
        </div>
        <button type="button" onclick="closeLiveNavigation()" class="w-8 h-8 rounded-lg bg-[#111724] border border-slate-700 text-slate-300 hover:text-red-400 flex items-center justify-center transition cursor-pointer shadow" title="Keluar Navigasi">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <div id="nav-fullscreen-map" class="flex-1 w-full h-full relative z-10 pt-14 pb-64"></div>

    <div class="nav-solid-bottom absolute bottom-0 left-0 right-0 z-30 bg-[#0B0F17] border-t border-slate-800 p-4 sm:p-5 shadow-2xl">
        <div class="max-w-xl mx-auto space-y-3.5">
            <div class="nav-metrics-box grid grid-cols-3 gap-3 text-center divide-x divide-slate-800 bg-[#111724] border border-slate-800 rounded-xl p-3.5 shadow-inner">
                <div>
                    <span class="text-[10px] font-mono font-semibold uppercase text-slate-400 block tracking-wider">Pace Saat Ini</span>
                    <div class="mt-0.5">
                        <span id="nav-live-pace" class="text-2xl sm:text-3xl font-black font-mono text-white tabular-nums">--:--</span>
                        <span class="text-[10px] font-mono text-slate-500 block">/km</span>
                    </div>
                </div>
                <div>
                    <span class="text-[10px] font-mono font-semibold uppercase text-slate-400 block tracking-wider">Jarak Lari</span>
                    <div class="mt-0.5">
                        <span id="nav-actual-dist" class="text-2xl sm:text-3xl font-black font-mono text-[#FC4C02] tabular-nums">0.00</span>
                        <span class="text-[10px] font-mono text-slate-500 block">km</span>
                    </div>
                </div>
                <div>
                    <span class="text-[10px] font-mono font-semibold uppercase text-slate-400 block tracking-wider">Waktu Lari</span>
                    <div class="mt-0.5">
                        <span id="nav-running-time" class="text-2xl sm:text-3xl font-black font-mono text-white tabular-nums">00:00</span>
                        <span class="text-[10px] font-mono text-slate-500 block">Durasi</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-xs font-mono text-slate-300 mb-1.5">
                    <span>Progress: <strong id="nav-progress-pct" class="text-white font-bold">0%</strong></span>
                    <span>Sisa: <strong id="nav-remaining-dist" class="text-[#FC4C02] font-bold">{{ $formattedDist }}</strong> km</span>
                </div>
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div id="nav-progress-bar" class="h-full bg-[#FC4C02] transition-all duration-300 w-0"></div>
                </div>
            </div>

            <div id="nav-action-buttons-wrap" class="pt-1">
                <button type="button" onclick="startRunningSession()" class="w-full py-3.5 px-4 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-black text-sm uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-[#FC4C02]/25">
                    <i class="fa-solid fa-play text-xs"></i>
                    <span>Mulai Lari (Start)</span>
                </button>
            </div>
        </div>
    </div>
</div>

@include('gpx.partials.suggest-title-modal')
@include('gpx.partials.offline-cache-modal')
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const rawRouteCoords = {!! $coordsJson !!};
        const totalRouteDistance = {{ $distKm > 0 ? $distKm : 10.0 }};
        const totalElevationGain = {{ $gainM }};
        const routeTitle = "{{ addslashes($item->title) }}";
        const currentMasterGpxId = {{ $masterGpxId }};

        let map = null;
        let polyline = null;
        let hoverMarker = null;
        let routePoints = [];
        let elevationByKm = [];
        let lastPaceTableData = [];

        // Layers Multi-layer Neon
        let baseRouteLine = null;
        let animGlowLine = null;
        let animActiveLine = null;
        let kmMarkersLayer = null;
        let showKmMarkers = true;
        let activeTileLayer = null;
        let baseTileLayers = {};

        // Animation State
        let isAnimPlaying = false;
        let animProgressRatio = 0.0;
        let animSpeedMultiplier = 5;
        let animRafId = null;
        let animLastTimestamp = null;
        let animCameraFollow = true;
        let animRunnerMarker = null;
        let currentHeading = 0.0;
        const animBaseDurationSeconds = 45;

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            initElevationProfile();
            initPacePro();
        });

        // 1. Bearing & Rotation Calculations
        function getBearing(lat1, lon1, lat2, lon2) {
            const toRad = Math.PI / 180;
            const toDeg = 180 / Math.PI;
            const dLon = (lon2 - lon1) * toRad;
            const y = Math.sin(dLon) * Math.cos(lat2 * toRad);
            const x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad) - Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLon);
            return (Math.atan2(y, x) * toDeg + 360) % 360;
        }

        function lerpAngle(fromAngle, toAngle, factor) {
            let diff = ((toAngle - fromAngle + 540) % 360) - 180;
            return (fromAngle + diff * factor + 360) % 360;
        }

        // 2. Map Initialization
        function initMap() {
            kmMarkersLayer = L.layerGroup();

            const mapboxToken = "{{ config('services.mapbox.token') }}";
            baseTileLayers = {
                dark: mapboxToken
                    ? L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/navigation-night-v1/tiles/{z}/{x}/{y}?access_token=' + mapboxToken, { tileSize: 512, zoomOffset: -1, maxZoom: 19, attribution: '&copy; Mapbox' })
                    : L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: '&copy; Esri' }),
                street: mapboxToken
                    ? L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/outdoors-v12/tiles/{z}/{x}/{y}?access_token=' + mapboxToken, { tileSize: 512, zoomOffset: -1, maxZoom: 19, attribution: '&copy; Mapbox &copy; OpenStreetMap' })
                    : L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: ['a', 'b', 'c', 'd'], attribution: '&copy; CARTO &copy; OpenStreetMap' }),
                satellite: mapboxToken
                    ? L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/satellite-streets-v12/tiles/{z}/{x}/{y}?access_token=' + mapboxToken, { tileSize: 512, zoomOffset: -1, maxZoom: 19, attribution: '&copy; Mapbox' })
                    : L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: '&copy; Esri' })
            };

            map = L.map('gpx-detail-map', {
                zoomControl: true,
                scrollWheelZoom: false,
                dragging: true,
            }).setView([-6.2088, 106.8456], 13);

            activeTileLayer = baseTileLayers.street;
            activeTileLayer.addTo(map);
            kmMarkersLayer.addTo(map);

            let validCoords = [];
            let cumulativeKm = 0;

            if (Array.isArray(rawRouteCoords)) {
                rawRouteCoords.forEach((pt) => {
                    let lat = null, lng = null, ele = null;
                    if (Array.isArray(pt)) {
                        lat = parseFloat(pt[0]);
                        lng = parseFloat(pt[1]);
                        if (pt.length > 2) ele = parseFloat(pt[2]);
                    } else if (typeof pt === 'object' && pt !== null) {
                        lat = parseFloat(pt.lat || pt.latitude);
                        lng = parseFloat(pt.lng || pt.lon || pt.longitude);
                        ele = parseFloat(pt.ele || 0);
                    }

                    if (!isNaN(lat) && !isNaN(lng)) {
                        if (validCoords.length > 0) {
                            const prev = validCoords[validCoords.length - 1];
                            cumulativeKm += calculateHaversine(prev.lat, prev.lng, lat, lng);
                        }
                        validCoords.push({ lat, lng, ele, dist: cumulativeKm });
                    }
                });
            }

            routePoints = validCoords;

            if (validCoords.length > 0) {
                const latlngs = validCoords.map(p => [p.lat, p.lng]);

                // Main Route: Bold Strava Athletic Orange (#FC4C02)
                baseRouteLine = L.polyline(latlngs, {
                    color: '#FC4C02',
                    weight: 5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(map);

                // Animation Replay Highlights
                animGlowLine = L.polyline([], {
                    color: '#FFE600',
                    weight: 8,
                    opacity: 0.75,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(map);

                animActiveLine = L.polyline([], {
                    color: '#FFFFFF',
                    weight: 3,
                    opacity: 1.0,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(map);

                // Start / Finish Markers
                const startIcon = L.divIcon({
                    className: 'border-0 bg-transparent',
                    html: '<div class="w-5 h-5 rounded-full bg-emerald-500 border-2 border-white text-[9px] font-mono font-bold text-white flex items-center justify-center shadow">S</div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                const finishIcon = L.divIcon({
                    className: 'border-0 bg-transparent',
                    html: '<div class="w-5 h-5 rounded-full bg-[#FC4C02] border-2 border-white text-[9px] font-mono font-bold text-white flex items-center justify-center shadow">F</div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                L.marker(latlngs[0], { icon: startIcon }).addTo(map);
                L.marker(latlngs[latlngs.length - 1], { icon: finishIcon }).addTo(map);

                // KM Markers
                const totalKmWhole = Math.floor(validCoords[validCoords.length - 1]?.dist || totalRouteDistance);
                let currentTargetKm = 1;
                validCoords.forEach((p) => {
                    if (currentTargetKm <= totalKmWhole && p.dist >= currentTargetKm) {
                        const kmBadgeIcon = L.divIcon({
                            className: 'border-0 bg-transparent',
                            html: `<div class="w-5 h-5 rounded-full bg-white border-2 border-[#FC4C02] text-[#FC4C02] text-[10px] font-mono font-black flex items-center justify-center shadow">${currentTargetKm}</div>`,
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });
                        L.marker([p.lat, p.lng], { icon: kmBadgeIcon }).addTo(kmMarkersLayer);
                        currentTargetKm++;
                    }
                });

                map.fitBounds(baseRouteLine.getBounds(), { padding: [35, 35] });
            }
        }

        // 3. Animation Engine (Flyover 3D Style)
        function initAnimatorLayers() {
            if (!map) return;
            if (!animRunnerMarker) {
                const runnerBeaconIcon = L.divIcon({
                    className: 'border-0 bg-transparent',
                    html: `
                        <div class="runner-beacon">
                            <div class="runner-beacon-ring"></div>
                            <div class="runner-beacon-inner">
                                <div class="runner-arrow-rotator">
                                    <i class="fa-solid fa-location-arrow text-[11px] text-white -rotate-45"></i>
                                </div>
                            </div>
                        </div>
                    `,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                const startPos = (routePoints && routePoints.length > 0) ? [routePoints[0].lat, routePoints[0].lng] : [-6.2088, 106.8456];
                animRunnerMarker = L.marker(startPos, { icon: runnerBeaconIcon, zIndexOffset: 2000 }).addTo(map);
            }
        }

        function toggleGpxAnimator() {
            const hud = document.getElementById('gpx-animator-hud');
            const teleHud = document.getElementById('gpx-live-telemetry-hud');
            if (!hud) return;

            const isCurrentlyOpen = !hud.classList.contains('hidden');
            if (isCurrentlyOpen) {
                pauseAnimation();
                hud.classList.add('hidden');
                if (teleHud) teleHud.classList.add('hidden');
                if (animRunnerMarker && map.hasLayer(animRunnerMarker)) map.removeLayer(animRunnerMarker);
                if (animGlowLine) animGlowLine.setLatLngs([]);
                if (animActiveLine) animActiveLine.setLatLngs([]);
                animRunnerMarker = null;
                fitRouteBounds();
            } else {
                hud.classList.remove('hidden');
                if (teleHud) teleHud.classList.remove('hidden');
                initAnimatorLayers();
                renderAnimationState(animProgressRatio);
                startAnimation();
            }
        }

        function togglePlayPauseAnimation() {
            if (isAnimPlaying) {
                pauseAnimation();
            } else {
                if (animProgressRatio >= 1.0) {
                    animProgressRatio = 0.0;
                }
                startAnimation();
            }
        }

        function startAnimation() {
            initAnimatorLayers();
            isAnimPlaying = true;
            animLastTimestamp = null;
            const playIcon = document.getElementById('icon-anim-play');
            const playLabel = document.getElementById('label-anim-play');
            if (playIcon) playIcon.className = 'fa-solid fa-pause text-[11px]';
            if (playLabel) playLabel.textContent = 'Pause';

            if (animRafId) cancelAnimationFrame(animRafId);
            animRafId = requestAnimationFrame(animLoop);
        }

        function pauseAnimation() {
            isAnimPlaying = false;
            if (animRafId) cancelAnimationFrame(animRafId);
            const playIcon = document.getElementById('icon-anim-play');
            const playLabel = document.getElementById('label-anim-play');
            if (playIcon) playIcon.className = 'fa-solid fa-play text-[11px]';
            if (playLabel) playLabel.textContent = 'Play';
        }

        function restartAnimation() {
            pauseAnimation();
            animProgressRatio = 0.0;
            renderAnimationState(0.0);
            startAnimation();
        }

        function onAnimSliderInput(val) {
            animProgressRatio = Math.max(0, Math.min(1000, parseFloat(val))) / 1000;
            renderAnimationState(animProgressRatio);
        }

        function setAnimSpeed(speed) {
            animSpeedMultiplier = parseFloat(speed) || 5;
            document.querySelectorAll('#anim-speed-btn-group .anim-speed-btn').forEach(btn => {
                const s = parseFloat(btn.getAttribute('data-speed'));
                if (s === animSpeedMultiplier) {
                    btn.className = 'anim-speed-btn px-2 py-0.5 rounded text-[10px] bg-slate-800 text-white font-bold cursor-pointer';
                } else {
                    btn.className = 'anim-speed-btn px-2 py-0.5 rounded text-[10px] text-slate-400 hover:text-white cursor-pointer';
                }
            });
        }

        function toggleCameraFollow() {
            animCameraFollow = !animCameraFollow;
            const badge = document.getElementById('badge-camera-follow');
            if (badge) {
                badge.textContent = animCameraFollow ? 'ON' : 'OFF';
                badge.className = animCameraFollow ? 'text-[9px] px-1 py-0.2 rounded bg-emerald-500/20 text-emerald-400 font-bold' : 'text-[9px] px-1 py-0.2 rounded bg-slate-800 text-slate-400 font-bold';
            }
        }

        function getPointAtRatio(ratio) {
            if (!routePoints || routePoints.length === 0) return null;
            if (routePoints.length === 1 || ratio <= 0) return { ...routePoints[0], index: 0 };
            if (ratio >= 1.0) return { ...routePoints[routePoints.length - 1], index: routePoints.length - 1 };

            const maxDist = routePoints[routePoints.length - 1].dist || totalRouteDistance || 1;
            const targetDist = ratio * maxDist;

            for (let i = 0; i < routePoints.length - 1; i++) {
                const p1 = routePoints[i];
                const p2 = routePoints[i + 1];
                if (targetDist >= p1.dist && targetDist <= p2.dist) {
                    const span = (p2.dist - p1.dist) || 0.0001;
                    const frac = Math.max(0, Math.min(1, (targetDist - p1.dist) / span));
                    return {
                        lat: p1.lat + (p2.lat - p1.lat) * frac,
                        lng: p1.lng + (p2.lng - p1.lng) * frac,
                        ele: (p1.ele !== null && p2.ele !== null) ? (p1.ele + (p2.ele - p1.ele) * frac) : (p1.ele || 0),
                        dist: targetDist,
                        index: i
                    };
                }
            }
            return { ...routePoints[routePoints.length - 1], index: routePoints.length - 1 };
        }

        function renderAnimationState(ratio) {
            const pt = getPointAtRatio(ratio);
            if (!pt) return;

            // Telemetry values
            const kmEl = document.getElementById('anim-telemetry-km');
            const elevEl = document.getElementById('anim-telemetry-elev');
            const timeEl = document.getElementById('anim-telemetry-time');
            const slider = document.getElementById('anim-progress-slider');
            const slopeEl = document.getElementById('anim-telemetry-slope');
            const gaugeBar = document.getElementById('anim-gauge-bar');
            const statusEl = document.getElementById('anim-telemetry-status-text');

            if (kmEl) kmEl.textContent = pt.dist.toFixed(2);
            if (elevEl) elevEl.textContent = Math.round(pt.ele);
            if (slider) slider.value = Math.round(ratio * 1000);
            if (timeEl) timeEl.textContent = formatDurationTime(pt.dist * 300);

            // Gauge Bar Calculation (0 - 100% relative to min/max elevation)
            if (gaugeBar && routePoints && routePoints.length > 0) {
                const allEles = routePoints.map(p => p.ele || 0);
                const minE = Math.min(...allEles);
                const maxE = Math.max(...allEles);
                const rangeE = Math.max(5, maxE - minE);
                const pct = Math.max(8, Math.min(100, Math.round(((pt.ele - minE) / rangeE) * 100)));
                gaugeBar.style.height = `${pct}%`;
            }

            // Slope Calculation & Dynamic Status Message
            const prevIdx = Math.max(0, pt.index - 1);
            const nextIdx = Math.min(routePoints.length - 1, pt.index + 1);
            const pPrev = routePoints[prevIdx];
            const pNext = routePoints[nextIdx];

            if (pNext && pPrev) {
                const dElev = (pNext.ele || 0) - (pPrev.ele || 0);
                const dDist = Math.max(0.005, (pNext.dist || 0) - (pPrev.dist || 0));
                const slopeVal = parseFloat(((dElev / (dDist * 1000)) * 100).toFixed(1));

                if (slopeEl) {
                    if (slopeVal > 1.0) slopeEl.innerHTML = `<span class="text-emerald-400 font-bold">+${slopeVal}% (Naik)</span>`;
                    else if (slopeVal < -1.0) slopeEl.innerHTML = `<span class="text-amber-400 font-bold">${slopeVal}% (Turun)</span>`;
                    else slopeEl.innerHTML = `<span class="text-slate-300 font-bold">— 0.0% (Datar)</span>`;
                }

                if (statusEl) {
                    const curKm = pt.dist.toFixed(1);
                    if (ratio >= 0.98) {
                        statusEl.textContent = `Finish Line! Rute Selesai!`;
                    } else if (slopeVal > 3.0) {
                        statusEl.textContent = `Tanjakan KM ${curKm} — Atur Pace & Napas!`;
                    } else if (slopeVal < -3.0) {
                        statusEl.textContent = `Turunan KM ${curKm} — Manfaatkan Gravitasi!`;
                    } else {
                        statusEl.textContent = `Lintasan Lari KM ${curKm} — Terus Melaju!`;
                    }
                }
            }

            // Marker position & rotation
            if (animRunnerMarker) {
                animRunnerMarker.setLatLng([pt.lat, pt.lng]);
                if (pNext && (pNext.lat !== pt.lat || pNext.lng !== pt.lng)) {
                    const targetBearing = getBearing(pt.lat, pt.lng, pNext.lat, pNext.lng);
                    currentHeading = lerpAngle(currentHeading, targetBearing, 0.35);
                    const markerEl = animRunnerMarker.getElement();
                    if (markerEl) {
                        const rotator = markerEl.querySelector('.runner-arrow-rotator');
                        if (rotator) rotator.style.transform = `rotate(${currentHeading.toFixed(1)}deg)`;
                    }
                }
            }

            // Update Neon Multi-layer Polyline
            if (routePoints.length > 0) {
                const subPoints = [];
                for (let i = 0; i <= pt.index; i++) {
                    subPoints.push([routePoints[i].lat, routePoints[i].lng]);
                }
                subPoints.push([pt.lat, pt.lng]);

                if (animGlowLine) animGlowLine.setLatLngs(subPoints);
                if (animActiveLine) animActiveLine.setLatLngs(subPoints);
            }

            // Smooth Camera Follow
            if (animCameraFollow && map) {
                map.panTo([pt.lat, pt.lng], { animate: false });
            }

            // Chart Tracker Sync
            const container = document.getElementById('elevation-chart-container');
            const hoverLine = document.getElementById('elev-hover-line');
            const hoverDot = document.getElementById('elev-hover-dot');
            if (container && hoverLine && hoverDot) {
                const svgWidth = container.clientWidth || 300;
                const x = ratio * svgWidth;
                hoverLine.style.left = x + 'px';
                hoverLine.classList.remove('hidden');
                hoverDot.style.left = x + 'px';
                hoverDot.classList.remove('hidden');
            }
        }

        function animLoop(timestamp) {
            if (!isAnimPlaying) return;
            if (!animLastTimestamp) animLastTimestamp = timestamp;

            const deltaSec = (timestamp - animLastTimestamp) / 1000;
            animLastTimestamp = timestamp;

            const step = (deltaSec / animBaseDurationSeconds) * animSpeedMultiplier;
            animProgressRatio += step;

            if (animProgressRatio >= 1.0) {
                animProgressRatio = 1.0;
                renderAnimationState(1.0);
                pauseAnimation();
                return;
            }

            renderAnimationState(animProgressRatio);
            animRafId = requestAnimationFrame(animLoop);
        }

        // 4. Elevation Profile Graph
        function initElevationProfile() {
            const svg = document.getElementById('gpx-elev-svg');
            const areaPath = document.getElementById('elev-area-path');
            const linePath = document.getElementById('elev-line-path');
            const container = document.getElementById('elevation-chart-container');
            if (!container || !areaPath || !linePath) return;

            const elevations = routePoints.map(p => p.ele || 0);
            const minElev = Math.min(...elevations);
            const maxElev = Math.max(...elevations);
            const elevRange = Math.max(maxElev - minElev, 10);
            const maxDist = routePoints[routePoints.length - 1]?.dist || totalRouteDistance || 1;

            function draw() {
                const width = container.clientWidth || 800;
                const height = container.clientHeight || 112;
                svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

                let lineD = `M 0 ${height - 10}`;
                let areaD = `M 0 ${height} L 0 ${height - 10}`;

                routePoints.forEach((p, idx) => {
                    const x = (p.dist / maxDist) * width;
                    const normEle = (p.ele - minElev) / elevRange;
                    const y = height - 10 - (normEle * (height - 20));
                    lineD += ` L ${x.toFixed(1)} ${y.toFixed(1)}`;
                    areaD += ` L ${x.toFixed(1)} ${y.toFixed(1)}`;
                });

                areaD += ` L ${width} ${height} Z`;
                linePath.setAttribute('d', lineD);
                areaPath.setAttribute('d', areaD);
            }

            draw();
            window.addEventListener('resize', draw);
        }

        // 5. PacePro Calculations
        function initPacePro() {
            calculatePaceProPlan();
            document.getElementById('pp-btn-generate')?.addEventListener('click', calculatePaceProPlan);
        }

        function setPacePreset(min, sec) {
            const dist = totalRouteDistance || 10;
            const totalSec = (min * 60 + sec) * dist;
            document.getElementById('pp-time-h').value = Math.floor(totalSec / 3600);
            document.getElementById('pp-time-m').value = Math.floor((totalSec % 3600) / 60);
            document.getElementById('pp-time-s').value = Math.floor(totalSec % 60);
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
            document.getElementById('pp-avg-pace').textContent = formatPaceTime(avgPaceSec) + ' /km';
            document.getElementById('pp-target-total-time').textContent = formatDurationTime(totalTargetSec);

            const tableBody = document.getElementById('pp-table-body');
            if (!tableBody) return;
            tableBody.innerHTML = '';

            const totalKm = Math.ceil(totalDist);
            let cumSec = 0;
            lastPaceTableData = [];

            for (let km = 1; km <= totalKm; km++) {
                const splitDist = km > totalDist ? (totalDist - (km - 1)) : 1;
                const splitSec = avgPaceSec * splitDist;
                cumSec += splitSec;

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-800/40 transition-colors text-center';
                tr.innerHTML = `
                    <td class="py-2.5 px-2 font-medium text-slate-300">${km}</td>
                    <td class="py-2.5 px-2 text-white font-semibold">${formatPaceTime(avgPaceSec)}</td>
                    <td class="py-2.5 px-2 text-slate-300">${formatDurationTime(splitSec)}</td>
                    <td class="py-2.5 px-2 text-slate-400">${formatDurationTime(cumSec)}</td>
                    <td class="py-2.5 px-2 text-slate-500">-</td>
                `;
                tableBody.appendChild(tr);
                lastPaceTableData.push({ km, pace: formatPaceTime(avgPaceSec), split: formatDurationTime(splitSec), cum: formatDurationTime(cumSec), elev: '-' });
            }
        }

        // Helpers
        function calculateHaversine(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
        }

        function formatPaceTime(seconds) {
            if (isNaN(seconds) || seconds <= 0) return '--:--';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${String(s).padStart(2, '0')}`;
        }

        function formatDurationTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return h > 0 ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}` : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }

        function toggleKmMarkers() {
            showKmMarkers = !showKmMarkers;
            if (showKmMarkers) map.addLayer(kmMarkersLayer);
            else map.removeLayer(kmMarkersLayer);
            const badge = document.getElementById('badge-km-toggle-state');
            if (badge) {
                badge.textContent = showKmMarkers ? 'ON' : 'OFF';
                badge.className = showKmMarkers ? 'text-[9px] px-1 py-0.2 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold' : 'text-[9px] px-1 py-0.2 rounded bg-slate-800 text-slate-400 font-mono font-bold';
            }
        }

        function toggleMapLayerMenu() {
            document.getElementById('map-layer-menu')?.classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('map-layer-dropdown-wrapper');
            const menu = document.getElementById('map-layer-menu');
            if (wrap && menu && !wrap.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        function setMapLayer(type) {
            if (!map || !baseTileLayers[type]) return;
            if (activeTileLayer) map.removeLayer(activeTileLayer);
            activeTileLayer = baseTileLayers[type];
            activeTileLayer.addTo(map);
            const labelMap = {
                'street': 'Voyager Light',
                'dark': 'Dark Mode',
                'satellite': 'Satelit Topo'
            };
            const labelEl = document.getElementById('label-active-map-layer');
            if (labelEl) labelEl.textContent = labelMap[type] || (type.charAt(0).toUpperCase() + type.slice(1));
            document.querySelectorAll('.layer-check-dark, .layer-check-street, .layer-check-satellite').forEach(el => el.classList.add('hidden'));
            document.querySelector('.layer-check-' + type)?.classList.remove('hidden');
            document.getElementById('map-layer-menu')?.classList.add('hidden');
        }

        function fitRouteBounds() {
            if (map && baseRouteLine) map.fitBounds(baseRouteLine.getBounds(), { padding: [35, 35] });
        }

        function shareGpxRoute() {
            if (navigator.share) {
                navigator.share({ title: "{{ e($item->title) }}", url: window.location.href }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => alert('Link berhasil disalin!'));
            }
        }

        function locateMeOnDetailMap() {
            if (!navigator.geolocation) {
                alert('Geolocation tidak didukung oleh browser Anda.');
                return;
            }

            const locateBtn = document.getElementById('btn-map-locate-me');
            const originalBtnHtml = locateBtn ? locateBtn.innerHTML : '';
            if (locateBtn) {
                locateBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin text-[10px] text-[#FC4C02]"></i><span class="hidden sm:inline">Mencari...</span>';
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    if (locateBtn) locateBtn.innerHTML = originalBtnHtml;
                    const uLat = pos.coords.latitude;
                    const uLng = pos.coords.longitude;
                    const uAcc = pos.coords.accuracy;

                    if (userLocationMarker && map.hasLayer(userLocationMarker)) map.removeLayer(userLocationMarker);
                    if (userLocationCircle && map.hasLayer(userLocationCircle)) map.removeLayer(userLocationCircle);
                    if (userDistanceLine && map.hasLayer(userDistanceLine)) map.removeLayer(userDistanceLine);

                    const userIcon = L.divIcon({
                        className: 'border-0 bg-transparent',
                        html: '<div class="relative flex items-center justify-center w-6 h-6"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-sky-500 border-2 border-white shadow"></span></div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    userLocationMarker = L.marker([uLat, uLng], { icon: userIcon, zIndexOffset: 1500 }).addTo(map);
                    userLocationCircle = L.circle([uLat, uLng], { radius: uAcc, color: '#38bdf8', weight: 1, fillColor: '#38bdf8', fillOpacity: 0.12 }).addTo(map);

                    if (routePoints.length > 0) {
                        const startPt = routePoints[0];
                        const distToStartKm = calculateHaversine(uLat, uLng, startPt.lat, startPt.lng);

                        userDistanceLine = L.polyline([[uLat, uLng], [startPt.lat, startPt.lng]], {
                            color: '#38bdf8',
                            weight: 2,
                            dashArray: '4, 6',
                            opacity: 0.8
                        }).addTo(map);

                        userLocationMarker.bindPopup(`
                            <div style="font-family:sans-serif; font-size:12px; color:#0f172a; line-height:1.4;">
                                <strong style="color:#0284c7;">Lokasi Anda Saat Ini</strong><br>
                                <span>Akurasi GPS: ±${Math.round(uAcc)}m</span><br>
                                <span style="font-weight:600; color:#FC4C02;">${distToStartKm.toFixed(2)} km</span> ke titik Start rute
                            </div>
                        `).openPopup();

                        const groupBounds = L.latLngBounds([[uLat, uLng], [startPt.lat, startPt.lng]]);
                        map.fitBounds(groupBounds, { padding: [50, 50] });
                    } else {
                        map.setView([uLat, uLng], 15);
                    }
                },
                function(err) {
                    if (locateBtn) locateBtn.innerHTML = originalBtnHtml;
                    alert('Gagal mendapatkan lokasi GPS: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        // =========================================================================
        // LIVE GPS NAVIGATION ENGINE (HUD & Run Tracking)
        // =========================================================================
        let navMap = null;
        let navPolyline = null;
        let navUserMarker = null;
        let navAccuracyCircle = null;
        let navWatchId = null;
        let navWakeLock = null;
        let navTimerInterval = null;
        let navSessionState = 'ready';
        let navRunStartTimestamp = null;
        let navAccumulatedElapsedSec = 0;
        let recordedActualDistanceKm = 0;
        let recordedGpsTrack = [];
        let userLocationMarker = null;
        let userLocationCircle = null;
        let userDistanceLine = null;

        function startLiveNavigation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung sensor geolokasi GPS.');
                return;
            }

            const hudEl = document.getElementById('gpx-nav-hud');
            if (hudEl) hudEl.classList.remove('hidden');

            document.body.classList.add('gpx-nav-active');
            const chatToggle = document.getElementById('chatbox-toggle');
            if (chatToggle) chatToggle.style.setProperty('display', 'none', 'important');
            const phChat = document.getElementById('ph-chatbox');
            if (phChat) phChat.style.setProperty('display', 'none', 'important');

            if ('wakeLock' in navigator) {
                navigator.wakeLock.request('screen').then(lock => { navWakeLock = lock; }).catch(() => {});
            }

            if (!navMap) {
                initNavMap();
            } else {
                navMap.invalidateSize();
                if (navPolyline) navMap.fitBounds(navPolyline.getBounds(), { padding: [35, 35] });
            }

            navSessionState = 'ready';
            navAccumulatedElapsedSec = 0;
            navRunStartTimestamp = null;
            recordedActualDistanceKm = 0;
            recordedGpsTrack = [];

            const actualDistEl = document.getElementById('nav-actual-dist');
            const timeEl = document.getElementById('nav-running-time');
            const progBar = document.getElementById('nav-progress-bar');
            const progPctEl = document.getElementById('nav-progress-pct');
            const remDistEl = document.getElementById('nav-remaining-dist');
            const livePaceEl = document.getElementById('nav-live-pace');

            if (actualDistEl) actualDistEl.textContent = '0.00';
            if (timeEl) timeEl.textContent = '00:00';
            if (progBar) progBar.style.width = '0%';
            if (progPctEl) progPctEl.textContent = '0%';
            if (remDistEl) remDistEl.textContent = totalRouteDistance.toFixed(2);
            if (livePaceEl) livePaceEl.textContent = '--:--';

            if (navWatchId) navigator.geolocation.clearWatch(navWatchId);
            navWatchId = navigator.geolocation.watchPosition(
                handleNavGpsSuccess,
                handleNavGpsError,
                { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
            );
        }

        function initNavMap() {
            const mapboxToken = "{{ config('services.mapbox.token') }}";
            navMap = L.map('nav-fullscreen-map', {
                zoomControl: false,
                scrollWheelZoom: true,
                dragging: true,
            }).setView([-6.2088, 106.8456], 15);

            if (mapboxToken) {
                L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/outdoors-v12/tiles/{z}/{x}/{y}?access_token=' + mapboxToken, {
                    tileSize: 512,
                    zoomOffset: -1,
                    maxZoom: 19,
                    attribution: '&copy; Mapbox &copy; OpenStreetMap'
                }).addTo(navMap);
            } else {
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: ['a', 'b', 'c', 'd'],
                    attribution: '&copy; CARTO &copy; OpenStreetMap'
                }).addTo(navMap);
            }

            if (routePoints.length > 0) {
                const latlngs = routePoints.map(p => [p.lat, p.lng]);
                navPolyline = L.polyline(latlngs, {
                    color: '#FC4C02',
                    weight: 5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(navMap);

                const userDotIcon = L.divIcon({
                    className: 'border-0 bg-transparent',
                    html: `
                        <div class="relative flex items-center justify-center w-7 h-7">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-60"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow-lg"></span>
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
            const now = Date.now();

            if (navUserMarker) navUserMarker.setLatLng([lat, lng]);
            if (navAccuracyCircle) {
                navAccuracyCircle.setLatLng([lat, lng]);
                navAccuracyCircle.setRadius(Math.min(accuracy, 25));
            }

            if (navSessionState === 'running') {
                if (recordedGpsTrack.length > 0) {
                    const prev = recordedGpsTrack[recordedGpsTrack.length - 1];
                    const segmentDist = calculateHaversine(prev.lat, prev.lng, lat, lng);
                    if (segmentDist > 0.002) {
                        recordedActualDistanceKm += segmentDist;
                        recordedGpsTrack.push({ lat, lng, dist: recordedActualDistanceKm, time: now });
                    }
                } else {
                    recordedGpsTrack.push({ lat, lng, dist: 0, time: now });
                }
            }

            let nearestPoint = routePoints[0];
            let minDistanceM = 999999;

            routePoints.forEach((p) => {
                const distM = calculateHaversine(lat, lng, p.lat, p.lng) * 1000;
                if (distM < minDistanceM) {
                    minDistanceM = distM;
                    nearestPoint = p;
                }
            });

            const routeProgressKm = Math.min(totalRouteDistance, nearestPoint?.dist || 0);
            const remainingRouteDist = Math.max(0, totalRouteDistance - routeProgressKm);
            const progressPct = Math.min(100, Math.round((routeProgressKm / totalRouteDistance) * 100));

            const progBar = document.getElementById('nav-progress-bar');
            const progPctEl = document.getElementById('nav-progress-pct');
            const remDistEl = document.getElementById('nav-remaining-dist');
            const actualDistEl = document.getElementById('nav-actual-dist');
            const livePaceEl = document.getElementById('nav-live-pace');

            if (progBar) progBar.style.width = `${progressPct}%`;
            if (progPctEl) progPctEl.textContent = `${progressPct}%`;
            if (remDistEl) remDistEl.textContent = remainingRouteDist.toFixed(2);
            if (actualDistEl) actualDistEl.textContent = recordedActualDistanceKm.toFixed(2);

            if (livePaceEl) {
                if (speedMps && speedMps > 0.5) {
                    const secPerKm = 1000 / speedMps;
                    livePaceEl.textContent = formatPaceTime(secPerKm);
                } else {
                    livePaceEl.textContent = '--:--';
                }
            }

            if (navMap) navMap.panTo([lat, lng], { animate: true, duration: 0.5 });
        }

        function handleNavGpsError(err) {
            console.warn('Nav GPS Error:', err);
        }

        function startRunningSession() {
            navSessionState = 'running';
            navRunStartTimestamp = Date.now();
            if (navTimerInterval) clearInterval(navTimerInterval);
            navTimerInterval = setInterval(() => {
                const currentSec = Math.floor((Date.now() - navRunStartTimestamp) / 1000);
                const totalSec = navAccumulatedElapsedSec + currentSec;
                const timeEl = document.getElementById('nav-running-time');
                if (timeEl) timeEl.textContent = formatDurationTime(totalSec);
            }, 1000);

            const wrap = document.getElementById('nav-action-buttons-wrap');
            if (wrap) {
                wrap.innerHTML = `
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="pauseRunningSession()" class="py-3 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-pause text-xs"></i>
                            <span>Jeda</span>
                        </button>
                        <button type="button" onclick="closeLiveNavigation()" class="py-3 px-4 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-[#FC4C02]/20">
                            <i class="fa-solid fa-flag-checkered text-xs"></i>
                            <span>Selesai</span>
                        </button>
                    </div>
                `;
            }
        }

        function pauseRunningSession() {
            if (navRunStartTimestamp) {
                navAccumulatedElapsedSec += Math.floor((Date.now() - navRunStartTimestamp) / 1000);
            }
            navRunStartTimestamp = null;
            if (navTimerInterval) clearInterval(navTimerInterval);
            navSessionState = 'paused';

            const wrap = document.getElementById('nav-action-buttons-wrap');
            if (wrap) {
                wrap.innerHTML = `
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="startRunningSession()" class="py-3 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-play text-xs"></i>
                            <span>Lanjut</span>
                        </button>
                        <button type="button" onclick="closeLiveNavigation()" class="py-3 px-4 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-[#FC4C02]/20">
                            <i class="fa-solid fa-flag-checkered text-xs"></i>
                            <span>Selesai</span>
                        </button>
                    </div>
                `;
            }
        }

        function closeLiveNavigation() {
            const hudEl = document.getElementById('gpx-nav-hud');
            if (hudEl) hudEl.classList.add('hidden');

            document.body.classList.remove('gpx-nav-active');
            const chatToggle = document.getElementById('chatbox-toggle');
            if (chatToggle) chatToggle.style.removeProperty('display');
            const phChat = document.getElementById('ph-chatbox');
            if (phChat) phChat.style.removeProperty('display');

            if (navWatchId) {
                navigator.geolocation.clearWatch(navWatchId);
                navWatchId = null;
            }
            if (navTimerInterval) {
                clearInterval(navTimerInterval);
                navTimerInterval = null;
            }
            if (navWakeLock) {
                navWakeLock.release().catch(() => {});
                navWakeLock = null;
            }

            const wrap = document.getElementById('nav-action-buttons-wrap');
            if (wrap) {
                wrap.innerHTML = `
                    <button type="button" onclick="startRunningSession()" class="w-full py-3.5 px-4 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-black text-sm uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-[#FC4C02]/25">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Mulai Lari (Start)</span>
                    </button>
                `;
            }
        }

        function copyPaceStrategy() {
            if (!lastPaceTableData || lastPaceTableData.length === 0) return;
            let txt = `Rencana Pacing PacePro - ${routeTitle} (${totalRouteDistance.toFixed(2)} km)\n`;
            txt += `KM | Target Pace | Waktu Split | Kumulatif\n`;
            lastPaceTableData.forEach(r => {
                txt += `KM ${r.km} | ${r.pace} | ${r.split} | ${r.cum}\n`;
            });
            navigator.clipboard.writeText(txt).then(() => {
                const btn = document.getElementById('pp-copy-text');
                if (btn) {
                    btn.textContent = 'Disalin!';
                    setTimeout(() => { btn.textContent = 'Salin'; }, 2000);
                }
            });
        }

        function exportPaceCsv() {
            if (!lastPaceTableData || lastPaceTableData.length === 0) return;
            let csv = "KM,Target Pace,Split Time,Cumulative Time\n";
            lastPaceTableData.forEach(r => {
                csv += `${r.km},"${r.pace}","${r.split}","${r.cum}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `pacepro-${routeTitle.toLowerCase().replace(/[^a-z0-9]/g, '-')}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
@endpush