@extends('layouts.pacerhub')

@section('title', 'Database Rute GPX Lari Indonesia - Download Gratis untuk Garmin, Coros & Strava | RuangLari')
@section('meta_title', 'Database Rute GPX Lari Indonesia - Download Gratis untuk Garmin, Coros, Suunto & Strava | RuangLari')
@section('meta_description', 'Kumpulan rute GPX lari terverifikasi di Jakarta, Bandung, Surabaya, Bali, Yogyakarta dan kota lainnya. Unduh gratis file GPX siap pakai untuk Garmin, Coros, Suunto, dan Strava.')
@section('meta_keywords', 'rute gpx lari, download gpx, rute lari jakarta, gpx garmin, gpx coros, gpx suunto, gpx strava, rute lari bandung, rute lari indonesia, database gpx')
@section('og_image', 'https://ruanglari.com/storage/blog/media/7fd6f9b8-5b5f-49d6-a0f6-f1d35b915e36.webp')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        /* Custom GPX Marker Cluster */
        .gpx-cluster-wrapper {
            background: transparent !important;
            border: none !important;
        }
        .gpx-map-cluster {
            width: 100%;
            height: 100%;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 11px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            border: 2px solid #020617;
            transition: transform 0.15s ease;
        }
        .gpx-map-cluster:hover {
            transform: scale(1.08);
        }
        .gpx-cluster-small {
            background: #ccff00;
            color: #020617;
        }
        .gpx-cluster-medium {
            background: #38bdf8;
            color: #020617;
        }
        .gpx-cluster-large {
            background: #f97316;
            color: #ffffff;
        }

        /* Leaflet Container & Controls */
        .leaflet-container {
            font-family: inherit !important;
            background: #020617 !important;
        }
        .leaflet-container a {
            text-decoration: none !important;
        }
        .leaflet-control-zoom {
            border: 1px solid #334155 !important;
            border-radius: 6px !important;
            overflow: hidden;
        }
        .leaflet-control-zoom a {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
            line-height: 28px !important;
        }
        .leaflet-control-zoom a:hover {
            background-color: #1e293b !important;
            color: #ccff00 !important;
        }

        /* Custom Leaflet Popup */
        .gpx-custom-leaflet-popup .leaflet-popup-content-wrapper {
            background: #0f172a !important;
            color: #f1f5f9 !important;
            border-radius: 8px !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.7) !important;
            padding: 0 !important;
        }
        .gpx-custom-leaflet-popup .leaflet-popup-content {
            margin: 12px !important;
            line-height: 1.4 !important;
            min-width: 220px;
            max-width: 280px;
        }
        .gpx-custom-leaflet-popup .leaflet-popup-tip {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
        }
        .gpx-custom-leaflet-popup a.leaflet-popup-close-button {
            color: #94a3b8 !important;
            padding: 6px !important;
        }
        .gpx-custom-leaflet-popup a.leaflet-popup-close-button:hover {
            color: #ffffff !important;
        }

        .gpx-svg-grid {
            background-color: #020617;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 20px 20px, 20px 20px;
        }

        /* Hide floating chat on GPX database page */
        #chatbox-toggle, #ph-chatbox {
            display: none !important;
        }

        /* Route Type Segment Buttons */
        .route-type-btn {
            background-color: #020617 !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
            transition: all 0.15s ease-in-out;
        }
        .route-type-btn.is-active,
        .route-type-btn:has(input:checked) {
            background-color: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Mobile Filter Sheet */
        @media (max-width: 767px) {
            #mobile-filter-btn-wrap {
                position: fixed !important;
                bottom: 0 !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                z-index: 99980 !important;
            }
        }
    </style>

    <!-- OpenGraph & Twitter Meta Tags -->
    <meta property="og:title" content="Database Rute GPX Lari Indonesia - RuangLari">
    <meta property="og:description" content="Kumpulan rute GPX lari terverifikasi di Jakarta, Bandung, Surabaya, Bali dan kota lainnya. Unduh gratis untuk Garmin, Coros, Suunto & Strava.">
    <meta property="og:image" content="https://ruanglari.com/storage/blog/media/7fd6f9b8-5b5f-49d6-a0f6-f1d35b915e36.webp">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="RuangLari">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Database Rute GPX Lari Indonesia - RuangLari">
    <meta name="twitter:description" content="Unduh gratis file GPX rute lari untuk Garmin, Coros, Suunto, dan Strava.">
    <meta name="twitter:image" content="https://ruanglari.com/storage/blog/media/7fd6f9b8-5b5f-49d6-a0f6-f1d35b915e36.webp">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "CollectionPage",
      "name": "Database Rute GPX Lari Indonesia",
      "description": "Kumpulan rute lari dan file GPX terverifikasi untuk pelari di Indonesia. Siap dipakai di Garmin, Coros, Suunto, dan Strava.",
      "url": "{{ url()->current() }}",
      "publisher": {
        "@@type": "Organization",
        "name": "RuangLari",
        "url": "{{ url('/') }}"
      },
      "mainEntity": {
        "@@type": "ItemList",
        "numberOfItems": {{ $items->total() ?? 0 }},
        "itemListElement": [
          @if(isset($items) && $items->count() > 0)
            @foreach($items->take(10) as $index => $item)
            {
              "@@type": "ListItem",
              "position": {{ $index + 1 }},
              "name": "{{ addslashes($item->title) }}",
              "url": "{{ route('gpx.show', $item->slug ?: $item->id) }}"
            }{{ $loop->last ? '' : ',' }}
            @endforeach
          @endif
        ]
      }
    }
    </script>
@endpush

@section('content')
<div class="min-h-screen pt-10 pb-20 px-4 md:px-8 bg-slate-950 text-slate-200 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Hero Header -->
        <div class="bg-slate-900 border border-slate-800 p-6 md:p-8 rounded-lg">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white tracking-tight">
                        Database Rute GPX Lari Indonesia
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-xl">
                        Temukan dan unduh rute lari terverifikasi di berbagai kota di Indonesia. File GPX siap disinkronkan ke jam Garmin, Coros, Suunto, dan aplikasi Strava.
                    </p>
                </div>
                
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap shrink-0">
                    <a href="{{ route('tools.buat-rute-lari') }}" class="w-full sm:w-auto px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-medium transition text-center">
                        + Buat Rute Baru
                    </a>
                    
                    <button id="btn-open-submit-gpx-modal" type="button" class="btn-trigger-gpx-modal w-full sm:w-auto px-4 py-2 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition text-center">
                        Submit Rute (+10 poin)
                    </button>
                </div>
            </div>
        </div>

        <!-- Location Detection Banner -->
        <div id="gpx-geo-notice-bar" class="{{ $hasCoordinates ? '' : 'hidden' }} p-3.5 rounded-lg bg-slate-900 border border-slate-800 text-xs text-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-neon animate-pulse shrink-0"></span>
                <div>
                    <span>Lokasi GPS terdeteksi: <strong id="gpx-detected-city-name" class="text-white font-mono">{{ $userLat ? number_format($userLat, 4) . ', ' . number_format($userLng, 4) : '...' }}</strong></span>
                    <span id="gpx-detected-radius-badge" class="text-xs text-slate-400 font-medium ml-1">({{ request('radius') ? 'Radius ' . request('radius') . ' km' : 'Semua radius' }})</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="btn-apply-detected-city" type="button" class="px-3 py-1.5 bg-neon text-dark font-semibold rounded-md text-xs hover:bg-white transition">
                    Urutkan Terdekat
                </button>
                <button id="btn-clear-gps-filter" type="button" class="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium transition" title="Hapus Filter GPS">
                    Reset GPS
                </button>
            </div>
        </div>

        <!-- Interactive Explorer Map Section -->
        <div id="gpx-explorer-map-section" class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden transition-all duration-300">
            <!-- Header bar with Layer Switcher and Recenter -->
            <div class="px-4 py-3 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 select-none" id="btn-toggle-explorer-map">
                <div class="flex items-center gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-semibold text-white">Peta Sebaran Rute GPX</h2>
                            <span id="gpx-map-routes-count" class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300 text-xs font-mono">
                                {{ count($mapRoutes ?? []) }} Rute
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 hidden sm:block">Klik marker untuk melihat ringkasan elevasi dan membuka detail rute.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <!-- Map Style Switcher (Jalan / Satelit / Topografi / Dark) -->
                    <div class="flex items-center bg-slate-950 p-0.5 rounded-md border border-slate-800 text-xs">
                        <button type="button" onclick="switchExplorerMapStyle('streets')" id="btn-map-style-streets" class="px-2 py-1 rounded font-medium transition bg-slate-800 text-white" title="Mapbox Streets (Jalan)">
                            Jalan
                        </button>
                        <button type="button" onclick="switchExplorerMapStyle('satellite')" id="btn-map-style-satellite" class="px-2 py-1 rounded font-medium transition text-slate-400 hover:text-white" title="Mapbox Satellite Imagery">
                            Satelit
                        </button>
                        <button type="button" onclick="switchExplorerMapStyle('outdoors')" id="btn-map-style-outdoors" class="px-2 py-1 rounded font-medium transition text-slate-400 hover:text-white" title="Mapbox Topografi / Outdoors">
                            Topografi
                        </button>
                        <button type="button" onclick="switchExplorerMapStyle('dark')" id="btn-map-style-dark" class="px-2 py-1 rounded font-medium transition text-slate-400 hover:text-white" title="Mapbox Dark Mode">
                            Dark
                        </button>
                    </div>

                    <button type="button" id="btn-map-recenter" class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition" title="Pusatkan Peta ke Seluruh Rute">
                        Pusatkan
                    </button>
                    <button type="button" id="btn-map-minimize-toggle" class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition" title="Minimize / Tampilkan Peta">
                        <span id="label-map-toggle">Sembunyikan</span>
                    </button>
                </div>
            </div>

            <!-- Map Container Wrapper -->
            <div id="explorer-map-collapse-wrap" class="relative transition-all duration-300">
                <div id="gpx-explorer-map" class="w-full h-[360px] sm:h-[420px] md:h-[460px] z-0 bg-slate-950"></div>
                
                <!-- Map Legend / Interactive Route Type Filter Overlay -->
                <div id="gpx-map-type-filter-overlay" class="absolute bottom-3 left-3 z-[400] bg-slate-900 border border-slate-800 rounded-md p-1 text-xs text-slate-300 shadow-xl pointer-events-auto flex items-center gap-1">
                    <span class="text-xs text-slate-400 font-medium px-1.5">Tipe:</span>
                    <button type="button" onclick="setMapAndFormRouteType('')" class="btn-map-type-pill px-2 py-1 rounded text-xs font-medium transition border {{ (!request('route_type') || request('route_type') == '') ? 'bg-slate-800 text-white border-slate-700' : 'text-slate-400 hover:text-white border-transparent' }}">
                        Semua
                    </button>
                    <button type="button" onclick="setMapAndFormRouteType('road')" class="btn-map-type-pill px-2 py-1 rounded text-xs font-medium transition border {{ request('route_type') == 'road' ? 'bg-slate-800 text-white border-slate-700' : 'text-slate-400 hover:text-white border-transparent' }} flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#ccff00]"></span>
                        <span>Road</span>
                    </button>
                    <button type="button" onclick="setMapAndFormRouteType('trail')" class="btn-map-type-pill px-2 py-1 rounded text-xs font-medium transition border {{ request('route_type') == 'trail' ? 'bg-slate-800 text-white border-slate-700' : 'text-slate-400 hover:text-white border-transparent' }} flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#10b981]"></span>
                        <span>Trail</span>
                    </button>
                    <button type="button" onclick="setMapAndFormRouteType('track')" class="btn-map-type-pill px-2 py-1 rounded text-xs font-medium transition border {{ request('route_type') == 'track' ? 'bg-slate-800 text-white border-slate-700' : 'text-slate-400 hover:text-white border-transparent' }} flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#38bdf8]"></span>
                        <span>Track</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Docked Bottom Mobile Filter Trigger -->
        <div id="mobile-filter-btn-wrap" class="md:hidden">
            <button id="btn-open-mobile-filter"
                type="button"
                class="flex items-center gap-2 px-5 py-2.5 rounded-t-lg bg-slate-900 border border-b-0 border-slate-700 text-white shadow-2xl text-xs font-semibold">
                <span>Filter Rute</span>
                <span id="mobile-filter-active-dot" class="hidden w-2 h-2 bg-neon rounded-full"></span>
            </button>
        </div>

        <!-- Search & Filter Section -->
        <div id="mobile-filter-backdrop" class="fixed inset-0 bg-black/70 z-[99990] hidden transition-opacity"></div>

        <div id="filter-wrapper-container" class="hidden md:block fixed md:static inset-x-3 top-20 z-[99999] md:z-auto bg-slate-900 border border-slate-800 rounded-lg max-h-[85vh] md:max-h-none overflow-y-auto md:overflow-visible shadow-2xl md:shadow-none p-5 md:p-0 transition-all">
            <form id="form-gpx-filter" method="GET" action="{{ route('gpx.index') }}" class="bg-slate-900 md:border md:border-slate-800 p-0 md:p-5 md:rounded-lg space-y-4">
                <!-- GPS Coordinates Hidden Inputs -->
                <input type="hidden" name="user_lat" id="filter-user-lat" value="{{ request('user_lat', request('lat')) }}">
                <input type="hidden" name="user_lng" id="filter-user-lng" value="{{ request('user_lng', request('lng')) }}">

                <!-- Mobile Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 md:hidden">
                    <h3 class="text-sm font-semibold text-white">Filter & Pencarian Rute</h3>
                    <button id="btn-close-mobile-filter" type="button" class="text-slate-400 hover:text-white text-lg leading-none">
                        &times;
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Top Row: Tipe Rute & Search Input -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Tipe Rute -->
                        <div class="space-y-1 flex-1 max-w-xl">
                            <label class="block text-xs font-medium text-slate-300">Tipe Rute</label>
                            <div class="grid grid-cols-4 gap-1 bg-slate-950 p-1 rounded-md border border-slate-800 select-none text-xs">
                                <label class="route-type-filter-btn {{ (!request('route_type') || request('route_type') == '') ? 'is-active bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white' }} flex items-center justify-center py-1.5 px-2 rounded-md cursor-pointer text-center transition">
                                    <input type="radio" name="route_type" value="" class="hidden" {{ (!request('route_type') || request('route_type') == '') ? 'checked' : '' }}>
                                    <span>Semua</span>
                                </label>
                                <label class="route-type-filter-btn {{ request('route_type') == 'road' ? 'is-active bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white' }} flex items-center justify-center py-1.5 px-2 rounded-md cursor-pointer text-center transition">
                                    <input type="radio" name="route_type" value="road" class="hidden" {{ request('route_type') == 'road' ? 'checked' : '' }}>
                                    <span>Road</span>
                                </label>
                                <label class="route-type-filter-btn {{ request('route_type') == 'trail' ? 'is-active bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white' }} flex items-center justify-center py-1.5 px-2 rounded-md cursor-pointer text-center transition">
                                    <input type="radio" name="route_type" value="trail" class="hidden" {{ request('route_type') == 'trail' ? 'checked' : '' }}>
                                    <span>Trail</span>
                                </label>
                                <label class="route-type-filter-btn {{ request('route_type') == 'track' ? 'is-active bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white' }} flex items-center justify-center py-1.5 px-2 rounded-md cursor-pointer text-center transition">
                                    <input type="radio" name="route_type" value="track" class="hidden" {{ request('route_type') == 'track' ? 'checked' : '' }}>
                                    <span>Track</span>
                                </label>
                            </div>
                        </div>

                        <!-- Search Input -->
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-300 mb-1">Cari Nama Rute</label>
                            <input type="text" name="q" value="{{ request('q') }}" 
                                placeholder="Ketik kata kunci rute (e.g. GBK, Sudirman, Tahura)..."
                                class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition">
                        </div>
                    </div>

                    <!-- Bottom Row: Filters (City, Distance, Elevation, Sort) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- Kota / Wilayah (Combobox) -->
                        <div class="relative" id="filter-city-combobox-wrap">
                            <label class="block text-xs font-medium text-slate-300 mb-1">Kota / Wilayah</label>
                            <div class="relative">
                                <input type="text" id="select-filter-city" name="city" value="{{ request('city') }}" autocomplete="off"
                                    placeholder="Semua kota..."
                                    class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition pr-14">
                                <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                    <button type="button" id="btn-clear-filter-city" class="{{ request('city') ? '' : 'hidden' }} text-slate-400 hover:text-white text-xs p-1" title="Hapus Filter Kota">
                                        &times;
                                    </button>
                                    <button type="button" id="btn-toggle-filter-city" class="text-slate-400 hover:text-white text-xs p-1" title="Lihat Pilihan Kota">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="filter-city-dropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-slate-900 border border-slate-800 rounded-md shadow-2xl z-50 max-h-56 overflow-y-auto"></div>
                        </div>

                        <!-- Jarak Rute -->
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Kategori Jarak</label>
                            <select name="distance" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:ring-1 focus:ring-slate-500 outline-none transition">
                                <option value="">Semua jarak</option>
                                <option value="under_5k" {{ request('distance') == 'under_5k' ? 'selected' : '' }}>&lt; 5 km (Short Run)</option>
                                <option value="5k_10k" {{ request('distance') == '5k_10k' ? 'selected' : '' }}>5 – 10 km (Medium Run)</option>
                                <option value="10k_21k" {{ request('distance') == '10k_21k' ? 'selected' : '' }}>10 – 21,1 km (Half Marathon)</option>
                                <option value="over_21k" {{ request('distance') == 'over_21k' ? 'selected' : '' }}>&gt; 21,1 km (Long Run)</option>
                            </select>
                        </div>

                        <!-- Elevasi -->
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Karakter Elevasi</label>
                            <select name="elevation" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:ring-1 focus:ring-slate-500 outline-none transition">
                                <option value="">Semua elevasi</option>
                                <option value="flat" {{ request('elevation') == 'flat' ? 'selected' : '' }}>Datar (&lt; 100 m)</option>
                                <option value="hilly" {{ request('elevation') == 'hilly' ? 'selected' : '' }}>Berbukit (100 – 300 m)</option>
                                <option value="mountainous" {{ request('elevation') == 'mountainous' ? 'selected' : '' }}>Pegunungan (&gt; 300 m)</option>
                            </select>
                        </div>

                        <!-- Urutan -->
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Urutan</label>
                            <select name="sort" id="select-filter-sort" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:ring-1 focus:ring-slate-500 outline-none transition">
                                <option value="nearest" {{ (request('sort') == 'nearest' || (!request('sort') && $hasCoordinates)) ? 'selected' : '' }}>Lokasi Terdekat (GPS)</option>
                                <option value="latest" {{ (request('sort') == 'latest' || (!request('sort') && !$hasCoordinates)) ? 'selected' : '' }}>Rute Terbaru</option>
                                <option value="distance_asc" {{ request('sort') == 'distance_asc' ? 'selected' : '' }}>Jarak Terpendek</option>
                                <option value="distance_desc" {{ request('sort') == 'distance_desc' ? 'selected' : '' }}>Jarak Terpanjang</option>
                                <option value="elevation_desc" {{ request('sort') == 'elevation_desc' ? 'selected' : '' }}>Elevasi Tertinggi</option>
                                <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Abjad Judul (A - Z)</option>
                                <option value="city" {{ request('sort') == 'city' ? 'selected' : '' }}>Berdasarkan Kota</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                    <div class="text-xs text-slate-300">
                        Menampilkan <strong id="gpx-total-count" class="text-white font-mono font-semibold">{{ $items->total() }}</strong> rute
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="btn-reset-filters" class="px-3 py-1.5 rounded-md border border-slate-700 bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-medium transition">
                            Reset
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Dynamic AJAX GPX Cards & Pagination Container -->
        <div id="gpx-catalog-results" class="relative transition-all duration-200">
            @include('gpx.partials.cards')
        </div>

        <!-- Educational Section -->
        <section class="mt-12 pt-8 border-t border-slate-800 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-white">Panduan Penggunaan Berkas GPX</h2>
                <p class="text-slate-400 text-xs mt-0.5">Tata cara mengimpor rute GPX ke jam olahraga Garmin, Coros, dan platform olahraga favorit.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-lg bg-slate-900 border border-slate-800 space-y-1.5">
                    <h3 class="text-xs font-semibold text-white">Apa itu berkas GPX?</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        GPX (GPS Exchange Format) adalah format standar yang menyimpan koordinat, jarak, dan elevasi rute untuk navigasi turn-by-turn di jam olahraga dan aplikasi tracking.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-slate-900 border border-slate-800 space-y-1.5">
                    <h3 class="text-xs font-semibold text-white">Cara impor ke Garmin</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Unduh berkas GPX &rarr; buka Garmin Connect &rarr; Training & Planning &rarr; Courses &rarr; Import &rarr; pilih berkas &rarr; Send to Device.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-slate-900 border border-slate-800 space-y-1.5">
                    <h3 class="text-xs font-semibold text-white">Cara impor ke Coros</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Unduh berkas GPX &rarr; buka di aplikasi COROS &rarr; simpan ke Route Library &rarr; Sync with Watch.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-slate-900 border border-slate-800 space-y-1.5">
                    <h3 class="text-xs font-semibold text-white">Poin kontribusi komunitas</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Setiap rute yang kamu bagikan memberikan +10 Run Points yang dapat digunakan untuk fitur-fitur eksklusif di RuangLari.
                    </p>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Submit GPX Modal -->
<div id="modal-submit-gpx" class="fixed inset-0 z-[99999] bg-black/70 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-lg max-w-xl w-full p-5 space-y-4 shadow-2xl relative max-h-[90vh] overflow-y-auto text-slate-200">
        
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-base font-semibold text-white">Bagikan Rute GPX</h3>
                <p class="text-xs text-slate-400 mt-0.5">Unggah berkas GPX rute lari untuk komunitas.</p>
            </div>
            <button id="btn-close-submit-gpx-modal" type="button" class="text-slate-400 hover:text-white text-lg leading-none" title="Tutup Modal">
                &times;
            </button>
        </div>

        <div id="gpx-modal-alert" class="hidden"></div>

        <form id="form-submit-gpx-modal" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="text" name="website_url" class="hidden" style="display:none !important;" tabindex="-1" autocomplete="off">

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Berkas GPX <span class="text-rose-400">*</span>
                </label>
                
                <!-- Interactive Dropzone -->
                <div id="modal-gpx-dropzone" class="relative border border-dashed border-slate-700 hover:border-slate-500 rounded-md p-5 transition bg-slate-950 text-center cursor-pointer flex flex-col items-center justify-center min-h-[120px] select-none">
                    <input id="input-modal-gpx-file" name="gpx_file" type="file" accept=".gpx,application/gpx+xml,text/xml" class="hidden" required>
                    
                    <!-- Empty State -->
                    <div id="dropzone-empty-state" class="flex flex-col items-center space-y-1.5 pointer-events-none">
                        <p class="text-xs font-medium text-slate-300">
                            Tarik berkas <span class="text-white font-semibold">.gpx</span> ke sini atau <span class="text-neon underline underline-offset-2">pilih berkas</span>
                        </p>
                        <p class="text-xs text-slate-400 font-mono">Format .gpx (maksimal 10 MB)</p>
                    </div>

                    <!-- Loaded File State -->
                    <div id="dropzone-file-state" class="hidden w-full flex items-center justify-between gap-3 bg-slate-900 border border-slate-800 rounded-md p-3 text-left">
                        <div class="min-w-0">
                            <div id="dropzone-filename" class="text-xs font-semibold text-white truncate">route.gpx</div>
                            <div id="dropzone-filesize" class="text-xs text-slate-400 font-mono mt-0.5">0 KB • Siap diproses</div>
                        </div>
                        <button type="button" id="btn-dropzone-remove" class="px-2 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition shrink-0">
                            Ganti
                        </button>
                    </div>
                </div>
            </div>

            <div id="modal-gpx-preview-container" class="hidden space-y-3 p-3.5 rounded-md bg-slate-950 border border-slate-800">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-300 font-medium">Pratinjau Rute</span>
                    <span id="modal-gpx-point-count" class="text-neon font-semibold font-mono">0 pts</span>
                </div>
                <div id="modal-gpx-preview-map" class="h-44 w-full rounded-md overflow-hidden border border-slate-800 bg-slate-900"></div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="bg-slate-900 p-2 rounded-md border border-slate-800">
                        <span class="text-[11px] text-slate-400 block">Jarak</span>
                        <strong id="modal-stat-distance" class="text-white font-mono font-semibold">-</strong>
                    </div>
                    <div class="bg-slate-900 p-2 rounded-md border border-slate-800">
                        <span class="text-[11px] text-slate-400 block">Elev Gain</span>
                        <strong id="modal-stat-gain" class="text-white font-mono font-semibold">-</strong>
                    </div>
                    <div class="bg-slate-900 p-2 rounded-md border border-slate-800">
                        <span class="text-[11px] text-slate-400 block">Elev Loss</span>
                        <strong id="modal-stat-loss" class="text-white font-mono font-semibold">-</strong>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Judul Rute <span class="text-rose-400">*</span>
                </label>
                <input id="input-modal-title" type="text" name="title" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition"
                    placeholder="Contoh: Rute CFD Sudirman – GBK Loop">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">
                    Tipe Rute
                </label>
                <div class="grid grid-cols-3 gap-2" id="modal-route-type-group">
                    <label class="route-type-btn is-active flex items-center justify-center p-2 rounded-md border border-slate-800 bg-slate-950 cursor-pointer text-xs font-medium text-center select-none transition">
                        <input type="radio" name="route_type" value="road" class="hidden" checked>
                        <span>Road</span>
                    </label>
                    <label class="route-type-btn flex items-center justify-center p-2 rounded-md border border-slate-800 bg-slate-950 cursor-pointer text-xs font-medium text-center select-none transition">
                        <input type="radio" name="route_type" value="trail" class="hidden">
                        <span>Trail</span>
                    </label>
                    <label class="route-type-btn flex items-center justify-center p-2 rounded-md border border-slate-800 bg-slate-950 cursor-pointer text-xs font-medium text-center select-none transition">
                        <input type="radio" name="route_type" value="track" class="hidden">
                        <span>Track</span>
                    </label>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-medium text-slate-300">
                        Kota / Kabupaten <span class="text-rose-400">*</span>
                    </label>
                    <button id="btn-modal-detect-location" type="button" class="text-xs text-neon hover:underline font-medium">
                        Deteksi Lokasi
                    </button>
                </div>
                <div class="relative" id="modal-city-autocomplete-wrapper">
                    <input id="input-modal-city" type="text" name="city" required autocomplete="off"
                        class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition"
                        placeholder="Nama kota atau kabupaten...">
                    <div id="modal-city-autocomplete-list" class="hidden absolute left-0 right-0 top-full mt-1 bg-slate-900 border border-slate-800 rounded-md shadow-2xl z-[99999] max-h-48 overflow-y-auto divide-y divide-slate-800"></div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tambahan (opsional)</label>
                <textarea name="notes" rows="2"
                    class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition resize-none leading-relaxed"
                    placeholder="Contoh: Aspal mulus, cocok long run pagi. Ada water station di km 5."></textarea>
            </div>

            <input type="hidden" id="input-modal-dist" name="client_distance_km">
            <input type="hidden" id="input-modal-gain" name="client_elevation_gain">
            <input type="hidden" id="input-modal-loss" name="client_elevation_loss">
            <input type="hidden" id="input-modal-coords" name="coordinates_json">

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('btn-close-submit-gpx-modal').click()"
                    class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition">
                    Batal
                </button>
                <button id="btn-submit-gpx-form" type="submit"
                    class="px-4 py-2 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition">
                    Kirim Rute
                </button>
            </div>
        </form>
    </div>
</div>

@include('gpx.partials.suggest-title-modal')
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast notification helper
            window.showGpxToast = function(msg, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-5 right-5 z-[99999] px-4 py-2.5 rounded-md border text-xs font-medium transition-all duration-300 shadow-2xl ${
                    type === 'success' 
                        ? 'bg-emerald-950 border-emerald-800 text-emerald-200' 
                        : 'bg-rose-950 border-rose-800 text-rose-200'
                }`;
                toast.textContent = msg;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            };

            // ==========================================
            // SUBMIT GPX MODAL CONTROLLER
            // ==========================================
            const modal = document.getElementById('modal-submit-gpx');
            const btnClose = document.getElementById('btn-close-submit-gpx-modal');
            const form = document.getElementById('form-submit-gpx-modal');
            const fileInput = document.getElementById('input-modal-gpx-file');
            const previewContainer = document.getElementById('modal-gpx-preview-container');
            const alertBox = document.getElementById('gpx-modal-alert');
            let previewMap = null;
            let previewPolyline = null;

            function showAlert(msg, isSuccess = false) {
                if (!alertBox) return;
                alertBox.className = `p-3 rounded-md text-xs font-medium border ${isSuccess ? 'bg-emerald-950 border-emerald-800 text-emerald-200' : 'bg-rose-950 border-rose-800 text-rose-200'}`;
                alertBox.textContent = msg;
                alertBox.classList.remove('hidden');
            }

            function hideAlert() {
                if (alertBox) alertBox.classList.add('hidden');
            }

            function openGpxModal() {
                const isAuth = {{ auth()->check() ? 'true' : 'false' }};
                if (!isAuth) {
                    if (typeof openLoginModal === 'function') {
                        openLoginModal();
                    } else {
                        window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
                    }
                    return;
                }
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }
            window.open_gpx_modal = openGpxModal;

            function closeGpxModal() {
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                hideAlert();
            }

            document.querySelectorAll('.btn-trigger-gpx-modal').forEach(btn => {
                btn.addEventListener('click', openGpxModal);
            });

            if (btnClose) btnClose.addEventListener('click', closeGpxModal);

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeGpxModal();
                });
            }

            // ==========================================
            // MAPBOX TILES & LEAFLET EXPLORER MAP
            // ==========================================
            const mapContainer = document.getElementById('gpx-explorer-map');
            let explorerMap = null;
            let explorerClusterGroup = null;
            const allInitialMapRoutes = @json($mapRoutes ?? []);
            const mapboxToken = "{{ config('services.mapbox.token') }}";

            let currentTileLayer = null;
            let currentMapStyle = 'streets';

            function getMapTileLayer(style) {
                if (mapboxToken) {
                    let mapboxStyleId = 'mapbox/dark-v11';
                    if (style === 'satellite') mapboxStyleId = 'mapbox/satellite-streets-v12';
                    else if (style === 'outdoors') mapboxStyleId = 'mapbox/outdoors-v12';
                    else if (style === 'streets') mapboxStyleId = 'mapbox/streets-v12';

                    return L.tileLayer(`https://api.mapbox.com/styles/v1/${mapboxStyleId}/tiles/{z}/{x}/{y}?access_token=${mapboxToken}`, {
                        tileSize: 512,
                        zoomOffset: -1,
                        maxZoom: 19,
                        attribution: '&copy; Mapbox'
                    });
                } else {
                    if (style === 'satellite') {
                        return L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
                    } else if (style === 'outdoors' || style === 'streets') {
                        return L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: ['a', 'b', 'c', 'd'] });
                    } else {
                        return L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: ['a', 'b', 'c', 'd'] });
                    }
                }
            }

            window.switchExplorerMapStyle = function(style) {
                currentMapStyle = style;
                if (explorerMap) {
                    if (currentTileLayer) explorerMap.removeLayer(currentTileLayer);
                    currentTileLayer = getMapTileLayer(style);
                    currentTileLayer.addTo(explorerMap);
                    if (explorerClusterGroup) explorerClusterGroup.bringToFront();
                }

                ['dark', 'satellite', 'outdoors', 'streets'].forEach(s => {
                    const btn = document.getElementById('btn-map-style-' + s);
                    if (btn) {
                        if (s === style) {
                            btn.className = 'px-2 py-1 rounded font-medium transition bg-slate-800 text-white';
                        } else {
                            btn.className = 'px-2 py-1 rounded font-medium transition text-slate-400 hover:text-white';
                        }
                    }
                });
            };

            function escapeHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function updateExplorerMapMarkers(routes, shouldZoom = false) {
                if (!explorerMap || !explorerClusterGroup) return;

                explorerClusterGroup.clearLayers();

                const countBadge = document.getElementById('gpx-map-routes-count');
                if (countBadge) {
                    countBadge.textContent = (routes ? routes.length : 0) + ' Rute';
                }

                if (!routes || routes.length === 0) return;

                const validBounds = [];

                routes.forEach(route => {
                    if (route.start_lat && route.start_lng) {
                        let dotColor = '#ccff00';
                        if (route.route_type === 'trail') dotColor = '#10b981';
                        else if (route.route_type === 'track') dotColor = '#38bdf8';

                        const customIcon = L.divIcon({
                            className: 'custom-gpx-pin',
                            html: `
                                <div style="
                                    width: 14px; 
                                    height: 14px; 
                                    background: ${dotColor}; 
                                    border: 2px solid #020617; 
                                    border-radius: 9999px; 
                                    box-shadow: 0 2px 6px rgba(0,0,0,0.5);
                                "></div>
                            `,
                            iconSize: [14, 14],
                            iconAnchor: [7, 7]
                        });

                        const marker = L.marker([route.start_lat, route.start_lng], {
                            icon: customIcon,
                            title: route.title
                        });

                        const popupHtml = `
                            <div class="p-1 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase font-mono bg-slate-800 text-slate-300 border border-slate-700">
                                        ${route.route_type ? route.route_type.toUpperCase() : 'ROAD'}
                                    </span>
                                    <span class="text-xs text-slate-400">${escapeHtml(route.city || 'Indonesia')}</span>
                                </div>
                                <h4 class="text-xs font-semibold text-white line-clamp-2">
                                    <a href="${route.url}" class="hover:text-neon transition">
                                        ${escapeHtml(route.title)}
                                    </a>
                                </h4>
                                
                                <div class="grid grid-cols-3 gap-1 text-center bg-slate-950 p-1.5 rounded border border-slate-800 text-xs">
                                    <div>
                                        <div class="text-[10px] text-slate-400">Jarak</div>
                                        <div class="font-semibold text-white font-mono">${route.distance_km ? route.distance_km + ' km' : '-'}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-400">Gain</div>
                                        <div class="font-semibold text-white font-mono">+${route.elevation_gain_m}m</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-400">Loss</div>
                                        <div class="font-semibold text-white font-mono">-${route.elevation_loss_m}m</div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-1.5 pt-1">
                                    <a href="${route.url}" class="flex-1 px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium text-center border border-slate-700 transition">
                                        Detail Rute
                                    </a>
                                    <a href="${route.download_url}" class="px-2.5 py-1 rounded bg-neon text-dark hover:bg-white text-xs font-semibold text-center transition">
                                        Unduh
                                    </a>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupHtml, {
                            maxWidth: 280,
                            minWidth: 230,
                            className: 'gpx-custom-leaflet-popup'
                        });

                        explorerClusterGroup.addLayer(marker);
                        validBounds.push([route.start_lat, route.start_lng]);
                    }
                });

                if (shouldZoom && validBounds.length > 0) {
                    try {
                        if (validBounds.length === 1) {
                            explorerMap.setView(validBounds[0], 13, { animate: true });
                        } else {
                            const bounds = L.latLngBounds(validBounds);
                            if (bounds.isValid()) {
                                explorerMap.fitBounds(bounds, { padding: [35, 35], maxZoom: 13, animate: true });
                            }
                        }
                    } catch(e) {}
                }
            }

            function initExplorerMap() {
                if (!mapContainer || explorerMap) return;

                const defaultCenter = [-2.5489, 118.0149];
                const defaultZoom = 5;

                explorerMap = L.map('gpx-explorer-map', {
                    zoomControl: true,
                    attributionControl: false,
                    scrollWheelZoom: true
                }).setView(defaultCenter, defaultZoom);

                currentTileLayer = getMapTileLayer('streets');
                currentTileLayer.addTo(explorerMap);

                explorerClusterGroup = L.markerClusterGroup({
                    showCoverageOnHover: false,
                    maxClusterRadius: 40,
                    spiderfyOnMaxZoom: true,
                    iconCreateFunction: function(cluster) {
                        const count = cluster.getChildCount();
                        let size = 32;
                        let cClass = 'gpx-cluster-small';
                        if (count >= 10) {
                            size = 38;
                            cClass = 'gpx-cluster-medium';
                        }
                        if (count >= 50) {
                            size = 46;
                            cClass = 'gpx-cluster-large';
                        }

                        return L.divIcon({
                            html: `<div class="gpx-map-cluster ${cClass}"><span>${count}</span></div>`,
                            className: 'gpx-cluster-wrapper',
                            iconSize: L.point(size, size)
                        });
                    }
                });

                explorerMap.addLayer(explorerClusterGroup);
                updateExplorerMapMarkers(allInitialMapRoutes, true);
            }

            initExplorerMap();

            // Recenter button
            const btnMapRecenter = document.getElementById('btn-map-recenter');
            if (btnMapRecenter) {
                btnMapRecenter.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (explorerMap && explorerClusterGroup) {
                        try {
                            const bounds = explorerClusterGroup.getBounds();
                            if (bounds.isValid()) {
                                explorerMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
                            }
                        } catch(err) {}
                    }
                });
            }

            // Minimize / Expand Toggle
            const btnMapMinimizeToggle = document.getElementById('btn-map-minimize-toggle');
            const explorerMapCollapseWrap = document.getElementById('explorer-map-collapse-wrap');
            const labelMapToggle = document.getElementById('label-map-toggle');
            let isMapMinimized = false;

            if (btnMapMinimizeToggle) {
                btnMapMinimizeToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    isMapMinimized = !isMapMinimized;
                    if (isMapMinimized) {
                        explorerMapCollapseWrap.style.display = 'none';
                        if (labelMapToggle) labelMapToggle.textContent = 'Tampilkan';
                    } else {
                        explorerMapCollapseWrap.style.display = 'block';
                        if (labelMapToggle) labelMapToggle.textContent = 'Sembunyikan';
                        setTimeout(() => {
                            if (explorerMap) explorerMap.invalidateSize();
                        }, 100);
                    }
                });
            }

            function parseCoordinatePair(item) {
                if (!item) return null;
                let lat = null, lng = null;
                if (Array.isArray(item)) {
                    lat = parseFloat(item[0]);
                    lng = parseFloat(item[1]);
                } else if (typeof item === 'object') {
                    lat = parseFloat(item.lat !== undefined ? item.lat : (item.latitude !== undefined ? item.latitude : item[0]));
                    lng = parseFloat(item.lng !== undefined ? item.lng : (item.lon !== undefined ? item.lon : (item.longitude !== undefined ? item.longitude : item[1])));
                }
                if (!isNaN(lat) && !isNaN(lng) && isFinite(lat) && isFinite(lng)) {
                    return [lat, lng];
                }
                return null;
            }

            // ==========================================
            // FAST AJAX FILTER & PAGINATION ENGINE
            // ==========================================
            const gpxCatalogResults = document.getElementById('gpx-catalog-results');
            const totalCountEl = document.getElementById('gpx-total-count');
            const selectCity = document.getElementById('select-filter-city');
            const filterForm = document.getElementById('form-gpx-filter');
            const searchInput = filterForm?.querySelector('input[name="q"]');
            let filterDebounceTimer = null;
            let currentFilterAbortCtrl = null;

            // City Filter Combobox
            const catalogCities = @json($cities ?? []);
            const filterCityDropdown = document.getElementById('filter-city-dropdown');
            const btnClearFilterCity = document.getElementById('btn-clear-filter-city');
            const btnToggleFilterCity = document.getElementById('btn-toggle-filter-city');
            let cityFilterDebounce = null;

            function renderCityFilterDropdown(query = '') {
                if (!filterCityDropdown) return;
                const cleanQuery = (query || '').toLowerCase().trim();
                let matches = catalogCities.filter(c => c && c.toLowerCase().includes(cleanQuery));

                let html = `
                    <div class="p-2 border-b border-slate-800 bg-slate-950 text-[10px] text-slate-400 font-medium flex items-center justify-between">
                        <span>Pilihan Kota</span>
                        <span>${matches.length} kota</span>
                    </div>
                    <div class="py-1 divide-y divide-slate-800 bg-slate-900">
                        <button type="button" data-city="" class="city-filter-option w-full text-left px-3 py-2 text-xs font-medium text-white hover:bg-slate-800 flex items-center transition">
                            Semua Kota (Reset)
                        </button>
                `;

                if (matches.length > 0) {
                    matches.forEach(city => {
                        const isSelected = selectCity?.value?.trim().toLowerCase() === city.toLowerCase();
                        html += `
                            <button type="button" data-city="${city}" class="city-filter-option w-full text-left px-3 py-2 text-xs text-white hover:bg-slate-800 flex items-center justify-between transition ${isSelected ? 'bg-slate-800 font-semibold' : ''}">
                                <span class="truncate">${city}</span>
                                ${isSelected ? '<span class="text-xs text-neon">&#10003;</span>' : ''}
                            </button>
                        `;
                    });
                } else {
                    html += `
                        <div class="px-3 py-2.5 text-xs text-slate-400 text-center">
                            Tidak ditemukan kota "${cleanQuery}".
                        </div>
                    `;
                }

                html += `</div>`;
                filterCityDropdown.innerHTML = html;
                filterCityDropdown.classList.remove('hidden');
            }

            function selectFilterCity(cityName) {
                if (selectCity) selectCity.value = cityName;
                if (btnClearFilterCity) {
                    if (cityName) btnClearFilterCity.classList.remove('hidden');
                    else btnClearFilterCity.classList.add('hidden');
                }
                if (filterCityDropdown) filterCityDropdown.classList.add('hidden');
                applyFiltersAjax();
            }

            if (selectCity) {
                selectCity.addEventListener('focus', function() {
                    renderCityFilterDropdown(this.value);
                });

                selectCity.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (btnClearFilterCity) {
                        if (val) btnClearFilterCity.classList.remove('hidden');
                        else btnClearFilterCity.classList.add('hidden');
                    }
                    clearTimeout(cityFilterDebounce);
                    cityFilterDebounce = setTimeout(() => {
                        renderCityFilterDropdown(val);
                        if (!val) applyFiltersAjax();
                    }, 150);
                });

                selectCity.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (filterCityDropdown) filterCityDropdown.classList.add('hidden');
                        applyFiltersAjax();
                    } else if (e.key === 'Escape') {
                        if (filterCityDropdown) filterCityDropdown.classList.add('hidden');
                    }
                });
            }

            if (btnToggleFilterCity) {
                btnToggleFilterCity.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (filterCityDropdown && !filterCityDropdown.classList.contains('hidden')) {
                        filterCityDropdown.classList.add('hidden');
                    } else {
                        renderCityFilterDropdown(selectCity?.value || '');
                    }
                });
            }

            if (btnClearFilterCity) {
                btnClearFilterCity.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    selectFilterCity('');
                });
            }

            document.addEventListener('click', function(e) {
                const optBtn = e.target.closest('.city-filter-option');
                if (optBtn) {
                    e.preventDefault();
                    const cityVal = optBtn.getAttribute('data-city') || '';
                    selectFilterCity(cityVal);
                    return;
                }

                const comboboxWrap = document.getElementById('filter-city-combobox-wrap');
                if (comboboxWrap && !comboboxWrap.contains(e.target)) {
                    if (filterCityDropdown) filterCityDropdown.classList.add('hidden');
                }
            });

            function renderAllRouteSvgs() {
                const routeSvgs = document.querySelectorAll('.gpx-route-svg');
                routeSvgs.forEach(function(svg) {
                    let rawCoords = [];
                    try {
                        rawCoords = JSON.parse(svg.getAttribute('data-coords') || '[]');
                    } catch(e) {
                        rawCoords = [];
                    }

                    const validCoords = [];
                    if (Array.isArray(rawCoords)) {
                        rawCoords.forEach(pt => {
                            const parsed = parseCoordinatePair(pt);
                            if (parsed) validCoords.push(parsed);
                        });
                    }

                    if (validCoords.length < 2) {
                        svg.innerHTML = `
                            <text x="160" y="95" fill="#64748b" font-family="system-ui" font-size="11" text-anchor="middle">Rute terverifikasi</text>
                        `;
                        return;
                    }

                    let minLat = Infinity, maxLat = -Infinity;
                    let minLng = Infinity, maxLng = -Infinity;

                    validCoords.forEach(([lat, lng]) => {
                        if (lat < minLat) minLat = lat;
                        if (lat > maxLat) maxLat = lat;
                        if (lng < minLng) minLng = lng;
                        if (lng > maxLng) maxLng = lng;
                    });

                    const latSpan = maxLat - minLat || 0.0001;
                    const lngSpan = maxLng - minLng || 0.0001;

                    const width = 320;
                    const height = 180;
                    const padX = 35;
                    const padY = 25;
                    const drawW = width - (padX * 2);
                    const drawH = height - (padY * 2);

                    const scale = Math.min(drawW / lngSpan, drawH / latSpan);
                    const offsetX = (width - (lngSpan * scale)) / 2;
                    const offsetY = (height - (latSpan * scale)) / 2;

                    const pointsStr = validCoords.map(([lat, lng]) => {
                        const x = offsetX + ((lng - minLng) * scale);
                        const y = height - (offsetY + ((lat - minLat) * scale));
                        return `${x.toFixed(1)},${y.toFixed(1)}`;
                    }).join(' ');

                    const startPt = validCoords[0];
                    const startX = (offsetX + ((startPt[1] - minLng) * scale)).toFixed(1);
                    const startY = (height - (offsetY + ((startPt[0] - minLat) * scale))).toFixed(1);

                    const endPt = validCoords[validCoords.length - 1];
                    const endX = (offsetX + ((endPt[1] - minLng) * scale)).toFixed(1);
                    const endY = (height - (offsetY + ((endPt[0] - minLat) * scale))).toFixed(1);

                    svg.innerHTML = `
                        <polyline points="${pointsStr}" fill="none" stroke="#ccff00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="${startX}" cy="${startY}" r="3.5" fill="#10b981" stroke="#000" stroke-width="1.5" />
                        <circle cx="${endX}" cy="${endY}" r="3.5" fill="#f43f5e" stroke="#000" stroke-width="1.5" />
                    `;
                });
            }

            renderAllRouteSvgs();

            async function fetchGpxResults(targetUrl, pushToHistory = true) {
                if (!gpxCatalogResults) return;

                if (currentFilterAbortCtrl) {
                    currentFilterAbortCtrl.abort();
                }
                currentFilterAbortCtrl = new AbortController();

                gpxCatalogResults.style.opacity = '0.4';
                gpxCatalogResults.style.pointerEvents = 'none';

                try {
                    const res = await fetch(targetUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: currentFilterAbortCtrl.signal
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.html !== undefined) {
                            gpxCatalogResults.innerHTML = data.html;
                            if (totalCountEl && data.total !== undefined) {
                                totalCountEl.textContent = data.total;
                            }
                            if (data.mapRoutes !== undefined) {
                                updateExplorerMapMarkers(data.mapRoutes, true);
                            }
                            renderAllRouteSvgs();

                            if (pushToHistory) {
                                window.history.pushState({}, '', targetUrl);
                            }
                        }
                    }
                } catch(err) {
                    if (err.name !== 'AbortError') console.error(err);
                } finally {
                    gpxCatalogResults.style.opacity = '1';
                    gpxCatalogResults.style.pointerEvents = 'auto';
                }
            }

            function applyFiltersAjax() {
                if (!filterForm) return;
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();

                for (const [key, val] of formData.entries()) {
                    if (val !== '' && val !== null && val !== undefined) {
                        params.append(key, val);
                    }
                }

                const baseUrl = filterForm.action || '{{ route("gpx.index") }}';
                const finalUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');
                fetchGpxResults(finalUrl, true);
            }

            window.setMapAndFormRouteType = function(type) {
                const radio = filterForm?.querySelector(`input[name="route_type"][value="${type}"]`);
                if (radio) radio.checked = true;

                document.querySelectorAll('.route-type-filter-btn').forEach(btn => {
                    const r = btn.querySelector('input[name="route_type"]');
                    if (r && r.value === type) {
                        btn.className = 'route-type-filter-btn is-active bg-slate-800 text-white font-medium flex items-center justify-center py-1.5 px-2 rounded-md cursor-pointer text-center transition';
                    } else {
                        btn.className = 'route-type-filter-btn text-slate-400 hover:text-white flex items-center justify-center py-1.5 px-2 rounded-md cursor-pointer text-center transition';
                    }
                });

                document.querySelectorAll('.btn-map-type-pill').forEach(btn => {
                    btn.classList.remove('bg-slate-800', 'text-white', 'border-slate-700');
                    btn.classList.add('text-slate-400', 'border-transparent');
                });

                applyFiltersAjax();
            };

            // Form Event Listeners
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    applyFiltersAjax();
                    closeMobileFilter();
                });

                filterForm.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', applyFiltersAjax);
                });

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        clearTimeout(filterDebounceTimer);
                        filterDebounceTimer = setTimeout(applyFiltersAjax, 350);
                    });
                }

                filterForm.querySelectorAll('.route-type-filter-btn input[type="radio"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        setMapAndFormRouteType(this.value);
                    });
                });
            }

            // Pagination Interceptor
            document.addEventListener('click', function(e) {
                const pageLink = e.target.closest('.gpx-pagination-wrap a, .pagination a');
                if (pageLink && pageLink.href) {
                    e.preventDefault();
                    fetchGpxResults(pageLink.href, true);
                    const resultsTop = gpxCatalogResults.getBoundingClientRect().top + window.pageYOffset - 100;
                    window.scrollTo({ top: resultsTop, behavior: 'smooth' });
                }
            });

            // Reset Button
            const btnResetFilters = document.getElementById('btn-reset-filters');
            if (btnResetFilters) {
                btnResetFilters.addEventListener('click', function() {
                    if (filterForm) filterForm.reset();
                    if (selectCity) selectCity.value = '';
                    if (btnClearFilterCity) btnClearFilterCity.classList.add('hidden');
                    setMapAndFormRouteType('');
                    applyFiltersAjax();
                });
            }

            document.addEventListener('click', function(e) {
                if (e.target && (e.target.id === 'btn-reset-empty-filters' || e.target.closest('#btn-reset-empty-filters'))) {
                    if (btnResetFilters) btnResetFilters.click();
                }
            });

            // Mobile Filter Controls
            const mobileFilterWrap = document.getElementById('filter-wrapper-container');
            const mobileBackdrop = document.getElementById('mobile-filter-backdrop');
            const btnOpenMobileFilter = document.getElementById('btn-open-mobile-filter');
            const btnCloseMobileFilter = document.getElementById('btn-close-mobile-filter');

            function openMobileFilter() {
                if (mobileFilterWrap) mobileFilterWrap.classList.remove('hidden');
                if (mobileBackdrop) mobileBackdrop.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileFilter() {
                if (mobileFilterWrap && window.innerWidth < 768) mobileFilterWrap.classList.add('hidden');
                if (mobileBackdrop) mobileBackdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }

            if (btnOpenMobileFilter) btnOpenMobileFilter.addEventListener('click', openMobileFilter);
            if (btnCloseMobileFilter) btnCloseMobileFilter.addEventListener('click', closeMobileFilter);
            if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMobileFilter);

            // ==========================================
            // GPS LOKASI DETECTION ENGINE
            // ==========================================
            const btnApplyDetectedCity = document.getElementById('btn-apply-detected-city');
            const btnClearGpsFilter = document.getElementById('btn-clear-gps-filter');

            if (btnApplyDetectedCity) {
                btnApplyDetectedCity.addEventListener('click', function() {
                    const sortSelect = document.getElementById('select-filter-sort');
                    if (sortSelect) sortSelect.value = 'nearest';
                    applyFiltersAjax();
                });
            }

            if (btnClearGpsFilter) {
                btnClearGpsFilter.addEventListener('click', function() {
                    document.getElementById('filter-user-lat').value = '';
                    document.getElementById('filter-user-lng').value = '';
                    document.getElementById('gpx-geo-notice-bar').classList.add('hidden');
                    applyFiltersAjax();
                });
            }

            // ==========================================
            // SUBMIT MODAL DROPZONE & PARSER
            // ==========================================
            const dropzone = document.getElementById('modal-gpx-dropzone');
            const emptyState = document.getElementById('dropzone-empty-state');
            const fileState = document.getElementById('dropzone-file-state');
            const dropFilename = document.getElementById('dropzone-filename');
            const dropFilesize = document.getElementById('dropzone-filesize');
            const btnRemoveFile = document.getElementById('btn-dropzone-remove');
            const modalCityInput = document.getElementById('input-modal-city');

            function formatBytes(bytes) {
                if (!bytes || bytes === 0) return '0 KB';
                const k = 1024;
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                const sizes = ['Bytes', 'KB', 'MB'];
                return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
            }

            function setDropzoneFileUI(file) {
                if (!file) {
                    if (emptyState) emptyState.classList.remove('hidden');
                    if (fileState) fileState.classList.add('hidden');
                    return;
                }
                if (dropFilename) dropFilename.textContent = file.name;
                if (dropFilesize) dropFilesize.textContent = formatBytes(file.size) + ' • Siap diunggah';
                if (emptyState) emptyState.classList.add('hidden');
                if (fileState) fileState.classList.remove('hidden');
            }

            function resetDropzone() {
                if (fileInput) fileInput.value = '';
                setDropzoneFileUI(null);
                const titleInput = document.getElementById('input-modal-title');
                if (titleInput) titleInput.value = '';
                if (modalCityInput) modalCityInput.value = '';
                if (previewContainer) previewContainer.classList.add('hidden');
            }

            if (dropzone && fileInput) {
                dropzone.addEventListener('click', function(e) {
                    if (e.target.closest('#btn-dropzone-remove')) {
                        e.stopPropagation();
                        resetDropzone();
                        return;
                    }
                    fileInput.click();
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.add('border-neon');
                    }, false);
                });

                ['dragleave', 'dragend'].forEach(eventName => {
                    dropzone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.remove('border-neon');
                    }, false);
                });

                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-neon');

                    const dt = e.dataTransfer;
                    if (dt && dt.files && dt.files.length > 0) {
                        const droppedFile = dt.files[0];
                        if (!droppedFile.name.toLowerCase().endsWith('.gpx') && !droppedFile.type.includes('xml')) {
                            showAlert('Harap pilih berkas dengan format .gpx');
                            return;
                        }
                        
                        try {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(droppedFile);
                            fileInput.files = dataTransfer.files;
                        } catch(err) {
                            fileInput.files = dt.files;
                        }

                        processGpxFile(droppedFile);
                    }
                }, false);

                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) processGpxFile(file);
                });
            }

            if (btnRemoveFile) {
                btnRemoveFile.addEventListener('click', function(e) {
                    e.stopPropagation();
                    resetDropzone();
                });
            }

            function haversineDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3;
                const φ1 = lat1 * Math.PI / 180;
                const φ2 = lat2 * Math.PI / 180;
                const Δφ = (lat2 - lat1) * Math.PI / 180;
                const Δλ = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                          Math.cos(φ1) * Math.cos(φ2) *
                          Math.sin(Δλ/2) * Math.sin(Δλ/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            function processGpxFile(file) {
                hideAlert();
                if (!file) return;

                setDropzoneFileUI(file);

                const titleInput = document.getElementById('input-modal-title');
                const reader = new FileReader();

                reader.onload = function(evt) {
                    const xmlText = evt.target.result;
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(xmlText, "text/xml");

                    const nameTags = xmlDoc.getElementsByTagName('name');
                    let extractedTitle = '';
                    if (nameTags.length > 0 && nameTags[0].textContent && nameTags[0].textContent.trim()) {
                        extractedTitle = nameTags[0].textContent.trim();
                    }
                    if (!extractedTitle) {
                        const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
                        extractedTitle = nameWithoutExt.replace(/[-_]/g, ' ').replace(/\s+/g, ' ').trim();
                    }
                    if (titleInput && extractedTitle) {
                        titleInput.value = extractedTitle;
                    }

                    let trkpts = xmlDoc.getElementsByTagName('trkpt');
                    if (trkpts.length === 0) {
                        trkpts = xmlDoc.getElementsByTagName('rtept');
                    }

                    const coords = [];
                    let prevLat = null, prevLon = null, prevEle = null;
                    let totalDist = 0, gainSum = 0, lossSum = 0;

                    for (let i = 0; i < trkpts.length; i++) {
                        const pt = trkpts[i];
                        let lat = pt.getAttribute('lat') || pt.getAttribute('latitude');
                        let lon = pt.getAttribute('lon') || pt.getAttribute('lng') || pt.getAttribute('longitude');

                        if (!lat && pt.querySelector('lat')) lat = pt.querySelector('lat').textContent;
                        if (!lon && (pt.querySelector('lon') || pt.querySelector('lng'))) lon = (pt.querySelector('lon') || pt.querySelector('lng')).textContent;

                        lat = parseFloat(lat);
                        lon = parseFloat(lon);

                        if (isNaN(lat) || isNaN(lon) || !isFinite(lat) || !isFinite(lon)) continue;

                        coords.push([lat, lon]);

                        const eleEl = pt.getElementsByTagName('ele')[0] || pt.querySelector('ele');
                        const ele = eleEl ? parseFloat(eleEl.textContent) : null;

                        if (prevLat !== null) {
                            totalDist += haversineDistance(prevLat, prevLon, lat, lon);
                            if (prevEle !== null && ele !== null && !isNaN(ele)) {
                                const diff = ele - prevEle;
                                if (diff > 0) gainSum += diff;
                                if (diff < 0) lossSum += Math.abs(diff);
                            }
                        }

                        prevLat = lat; prevLon = lon; prevEle = ele;
                    }

                    if (coords.length < 2) {
                        showAlert('Berkas GPX tidak memiliki titik koordinat yang dapat diproses.');
                        resetDropzone();
                        return;
                    }

                    const distKm = (totalDist / 1000).toFixed(2);
                    document.getElementById('modal-stat-distance').textContent = distKm + ' km';
                    document.getElementById('modal-stat-gain').textContent = '+' + Math.round(gainSum) + ' m';
                    document.getElementById('modal-stat-loss').textContent = '-' + Math.round(lossSum) + ' m';
                    document.getElementById('modal-gpx-point-count').textContent = coords.length + ' pts';

                    document.getElementById('input-modal-dist').value = (totalDist / 1000).toFixed(3);
                    document.getElementById('input-modal-gain').value = Math.round(gainSum);
                    document.getElementById('input-modal-loss').value = Math.round(lossSum);
                    
                    const step = Math.max(1, Math.floor(coords.length / 200));
                    const sampledCoords = coords.filter((_, idx) => idx % step === 0);
                    document.getElementById('input-modal-coords').value = JSON.stringify(sampledCoords);

                    if (previewContainer) previewContainer.classList.remove('hidden');

                    try {
                        if (!previewMap) {
                            previewMap = L.map('modal-gpx-preview-map', {
                                zoomControl: false,
                                attributionControl: false
                            }).setView([coords[0][0], coords[0][1]], 13);
                            
                            const previewTileLayer = getMapTileLayer('streets');
                            previewTileLayer.addTo(previewMap);
                        } else {
                            previewMap.setView([coords[0][0], coords[0][1]], 13);
                        }

                        if (previewPolyline) {
                            previewMap.removeLayer(previewPolyline);
                        }

                        previewPolyline = L.polyline(coords, {
                            color: '#ccff00',
                            weight: 3.5,
                            opacity: 0.95,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(previewMap);

                        const bounds = previewPolyline.getBounds();
                        if (bounds.isValid()) {
                            previewMap.fitBounds(bounds, { padding: [15, 15] });
                        }
                    } catch(mapErr) {}

                    if (coords.length > 0 && modalCityInput) {
                        reverseGeocodeModalCity(coords[0][0], coords[0][1]);
                    }

                    setTimeout(() => {
                        if (previewMap) previewMap.invalidateSize();
                    }, 200);
                };
                reader.readAsText(file);
            }

            // Reverse Geocoding City Detection
            async function reverseGeocodeModalCity(lat, lng) {
                const cityInput = document.getElementById('input-modal-city');
                if (!cityInput) return;
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.address) {
                            const addr = data.address;
                            const cityName = addr.city || addr.town || addr.county || addr.state_district || addr.state || '';
                            if (cityName) {
                                cityInput.value = cityName.replace(/^(Kota|Kabupaten)\s+/i, '');
                            }
                        }
                    }
                } catch(e) {}
            }

            const btnModalDetectLoc = document.getElementById('btn-modal-detect-location');
            if (btnModalDetectLoc) {
                btnModalDetectLoc.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        showAlert('Browser tidak mendukung geolokasi GPS.');
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        pos => reverseGeocodeModalCity(pos.coords.latitude, pos.coords.longitude),
                        err => showAlert('Gagal membaca posisi GPS.')
                    );
                });
            }

            // Submit GPX Form
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    hideAlert();

                    const submitBtn = document.getElementById('btn-submit-gpx-form');
                    const origText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mengirim...';

                    const formData = new FormData(form);

                    fetch('{{ route("tools.buat-rute-lari.submit-gpx") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(resObj => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = origText;

                        if (resObj.status === 200 && resObj.body.success) {
                            showAlert(resObj.body.message, true);
                            form.reset();
                            resetDropzone();
                            setTimeout(() => {
                                closeGpxModal();
                                window.location.reload();
                            }, 1500);
                        } else {
                            showAlert(resObj.body.message || 'Gagal mengirim rute GPX. Periksa input Anda.');
                        }
                    })
                    .catch(err => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = origText;
                        showAlert('Terjadi kesalahan koneksi saat mengirim berkas GPX.');
                    });
                });
            }
        });
    </script>
@endpush