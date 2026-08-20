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
            font-weight: 800;
            font-size: 12px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45);
            border: 2px solid #0c121e;
            transition: transform 0.15s ease;
        }
        .gpx-map-cluster:hover {
            transform: scale(1.1);
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

        /* Custom Leaflet Popup Styling (Pure Dark & RuangLari Palette - No #0078A8) */
        .leaflet-container {
            font-family: inherit !important;
            background: #111827 !important;
        }
        .leaflet-container a {
            text-decoration: none !important;
        }
        .leaflet-control-zoom a {
            background-color: #0c121e !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        .leaflet-control-zoom a:hover {
            background-color: #1e293b !important;
            color: #ccff00 !important;
        }
        .leaflet-control-zoom a:focus {
            background-color: #1e293b !important;
            color: #ccff00 !important;
            outline: none !important;
            border-color: #ccff00 !important;
        }
        .gpx-custom-leaflet-popup a.btn-popup-detail,
        .gpx-custom-leaflet-popup a.btn-popup-detail * {
            color: #020617 !important;
            font-weight: 700 !important;
        }
        .gpx-custom-leaflet-popup a.btn-popup-detail:hover,
        .gpx-custom-leaflet-popup a.btn-popup-detail:hover * {
            color: #020617 !important;
        }
        .gpx-custom-leaflet-popup h4 a {
            color: #ffffff !important;
            transition: color 0.15s ease;
        }
        .gpx-custom-leaflet-popup h4 a:hover {
            color: #ccff00 !important;
        }
        .gpx-custom-leaflet-popup .leaflet-popup-content-wrapper {
            background: #0c121e !important;
            color: #f1f5f9 !important;
            border-radius: 1rem !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7) !important;
            padding: 0 !important;
        }
        .gpx-custom-leaflet-popup .leaflet-popup-content {
            margin: 0.875rem !important;
            line-height: 1.4 !important;
            min-width: 220px;
            max-width: 280px;
        }
        .gpx-custom-leaflet-popup .leaflet-popup-tip {
            background: #0c121e !important;
            border: 1px solid #334155 !important;
        }
        .gpx-custom-leaflet-popup a.leaflet-popup-close-button {
            color: #94a3b8 !important;
            padding: 8px !important;
        }
        .gpx-custom-leaflet-popup a.leaflet-popup-close-button:hover {
            color: #ffffff !important;
        }
        .gpx-svg-grid {
            background-color: #0c121e;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(204, 255, 0, 0.04) 0%, transparent 70%),
                linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 100% 100%, 20px 20px, 20px 20px;
        }
        /* Accent color override */
        .text-accent { color: #ccff00; }
        .bg-accent { background-color: #ccff00; }
        .border-accent { border-color: #ccff00; }
        .hover\:bg-accent:hover { background-color: #ccff00; }
        .shadow-accent { box-shadow: 0 4px 14px rgba(204, 255, 0, 0.2); }

        /* Dark styling for all filter dropdowns & native selects */
        select, select option {
            background-color: #0c121e !important;
            color: #f1f5f9 !important;
        }
        select option:hover, select option:focus, select option:checked {
            background-color: #1e293b !important;
            color: #ccff00 !important;
        }

        /* Hide floating chat on GPX database page */
        #chatbox-toggle, #ph-chatbox {
            display: none !important;
        }

        /* Route Type Radio Buttons (Neon Lime Active) */
        .route-type-btn {
            background-color: #090D16 !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
            transition: all 0.2s ease-in-out;
        }
        .route-type-btn.is-active,
        .route-type-btn:has(input:checked) {
            background-color: #ccff00 !important;
            background: #ccff00 !important;
            border-color: #ccff00 !important;
            color: #020617 !important;
            font-weight: 700 !important;
            box-shadow: 0 0 12px rgba(204, 255, 0, 0.25) !important;
        }
        .route-type-btn.is-active span,
        .route-type-btn:has(input:checked) span {
            color: #020617 !important;
            font-weight: 700 !important;
        }

        /* Mobile Floating Filter Drawer & Bottom Docked Button */
        @media (max-width: 767px) {
            #mobile-filter-btn-wrap {
                position: fixed !important;
                bottom: 0 !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                z-index: 99980 !important;
                margin: 0 !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding: 0 !important;
            }
            #btn-open-mobile-filter {
                background-color: #0c121e !important;
                background: #0c121e !important;
                margin: 0 !important;
                margin-bottom: 0 !important;
            }
            #mobile-filter-backdrop {
                position: fixed !important;
                inset: 0 !important;
                background-color: rgba(0, 0, 0, 0.85) !important;
                backdrop-filter: blur(6px) !important;
                -webkit-backdrop-filter: blur(6px) !important;
                z-index: 99990 !important;
            }
            #filter-wrapper-container {
                position: fixed !important;
                left: 0.75rem !important;
                right: 0.75rem !important;
                top: 4.75rem !important;
                bottom: auto !important;
                max-height: calc(100vh - 5.75rem) !important;
                overflow-y: auto !important;
                background-color: #0c121e !important;
                background: #0c121e !important;
                opacity: 1 !important;
                border: 1px solid rgba(204, 255, 0, 0.4) !important;
                border-radius: 1.25rem !important;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.95) !important;
                z-index: 99999 !important;
                padding: 1.25rem !important;
            }
            #filter-wrapper-container form#form-gpx-filter {
                background-color: #0c121e !important;
                background: #0c121e !important;
                opacity: 1 !important;
                border: none !important;
                padding: 0 !important;
            }
            #filter-wrapper-container input,
            #filter-wrapper-container select {
                background-color: #090D16 !important;
                background: #090D16 !important;
                opacity: 1 !important;
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
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-[#090D16] text-slate-200">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Hero -->
        <div class="bg-[#0c121e] border border-slate-800 p-6 md:p-9 rounded-2xl relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-[#ccff00]/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 relative z-10">
                <div class="space-y-3 max-w-2xl">
                    
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight text-white leading-tight">
                        Database Rute <span class="text-accent">GPX</span> Lari Indonesia
                    </h1>

                    <p class="text-slate-200 text-sm leading-relaxed max-w-xl">
                        Temukan dan unduh rute lari terbaik di seluruh Indonesia. File GPX siap sinkron ke Garmin, Coros, Suunto, dan Strava.
                    </p>
                </div>
                
                <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap shrink-0">
                    <a href="{{ route('tools.buat-rute-lari') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-medium transition flex items-center justify-center gap-2">
                        <span>Buat rute baru</span>
                    </a>
                    
                    <button id="btn-open-submit-gpx-modal" type="button" class="btn-trigger-gpx-modal w-full sm:w-auto px-5 py-2.5 rounded-xl bg-accent text-slate-950 hover:bg-white text-sm font-bold transition shadow-accent flex items-center justify-center gap-2 cursor-pointer">
                        <span>Submit rute (+10 poin)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Location Detection Banner -->
        <div id="gpx-geo-notice-bar" class="{{ $hasCoordinates ? '' : 'hidden' }} p-4 rounded-xl bg-slate-900/90 border border-[#ccff00]/30 text-sm text-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-accent animate-pulse shrink-0"></span>
                <div>
                    <span>Lokasi GPS terdeteksi: <strong id="gpx-detected-city-name" class="text-white">{{ $userLat ? number_format($userLat, 4) . ', ' . number_format($userLng, 4) : '...' }}</strong></span>
                    <span id="gpx-detected-radius-badge" class="text-xs text-accent font-semibold ml-2">({{ request('radius') ? 'Radius ' . request('radius') . ' km' : 'Semua radius' }})</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="btn-apply-detected-city" type="button" class="px-3.5 py-1.5 bg-accent text-slate-950 font-bold rounded-lg text-xs hover:bg-white transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                    <span>Urutkan Terdekat</span>
                </button>
                <button id="btn-clear-gps-filter" type="button" class="px-2.5 py-1.5 rounded-lg border border-slate-700 hover:border-rose-500/50 text-slate-400 hover:text-rose-400 text-xs font-semibold transition cursor-pointer" title="Hapus Filter GPS">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Interactive Explorer Map Section (Minimizable, OpenStreetMap with CARTO Light style, Marker Clustering) -->
        <div id="gpx-explorer-map-section" class="bg-[#0c121e] border border-slate-800 rounded-2xl overflow-hidden shadow-xl transition-all duration-300">
            <!-- Header bar with Minimize/Expand toggle -->
            <div class="px-5 py-3.5 bg-[#090D16]/90 border-b border-slate-800 flex items-center justify-between gap-4 cursor-pointer select-none" id="btn-toggle-explorer-map">
                <div class="flex items-center gap-3">                    
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm sm:text-base font-bold text-white tracking-tight">Peta Sebaran Rute GPX</h2>
                            <span id="gpx-map-routes-count" class="px-2 py-0.5 rounded-md bg-accent/20 text-accent border border-accent/30 text-[11px] font-bold">
                                {{ count($mapRoutes ?? []) }} Rute terdaftar
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 hidden sm:block">Jelajahi titik awal rute di peta (CARTO Light). Klik marker untuk melihat detail rute.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <button type="button" id="btn-map-recenter" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm" title="Pusatkan Peta ke Seluruh Rute">
                        <i class="fa-solid fa-expand text-[11px]"></i>
                        <span class="hidden sm:inline">Pusatkan</span>
                    </button>
                    <button type="button" id="btn-map-minimize-toggle" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm" title="Minimize / Tampilkan Peta">
                        <span id="label-map-toggle" class="text-xs">Sembunyikan</span>
                        <i id="icon-map-toggle" class="fa-solid fa-chevron-up transition-transform duration-300 text-[10px]"></i>
                    </button>
                </div>
            </div>

            <!-- Map Container Wrapper -->
            <div id="explorer-map-collapse-wrap" class="relative transition-all duration-300">
                <div id="gpx-explorer-map" class="w-full h-[360px] sm:h-[420px] md:h-[460px] z-0 bg-[#f2efe9]"></div>
                
                <!-- Map Legend / Interactive Route Type Filter Overlay -->
                <div id="gpx-map-type-filter-overlay" class="absolute bottom-3 left-3 z-[400] bg-[#0c121e] border border-slate-700 rounded-xl p-1.5 text-xs text-slate-300 shadow-2xl pointer-events-auto flex items-center gap-1.5" style="background-color: #0c121e !important;">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider px-1">Tipe:</span>
                    <button type="button" onclick="setMapAndFormRouteType('')" class="btn-map-type-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border {{ (!request('route_type') || request('route_type') == '') ? 'bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }}">
                        Semua
                    </button>
                    <button type="button" onclick="setMapAndFormRouteType('road')" class="btn-map-type-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border {{ request('route_type') == 'road' ? 'bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ccff00]"></span>
                        <span>Road</span>
                    </button>
                    <button type="button" onclick="setMapAndFormRouteType('trail')" class="btn-map-type-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border {{ request('route_type') == 'trail' ? 'bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span>
                        <span>Trail</span>
                    </button>
                    <button type="button" onclick="setMapAndFormRouteType('track')" class="btn-map-type-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border {{ request('route_type') == 'track' ? 'bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#38bdf8]"></span>
                        <span>Track</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Docked Bottom Mobile Filter Trigger (Bottom 0) -->
        <div id="mobile-filter-btn-wrap"
            class="md:hidden !m-0 !mt-0 !mb-0 !p-0"
            style="
                position: fixed !important;
                left: 50% !important;
                bottom: 0 !important;
                margin: 0 !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding: 0 !important;
                transform: translateX(-50%) !important;
                z-index: 99980 !important;
                width: max-content;
            ">

            <button id="btn-open-mobile-filter"
                type="button"
                class="group relative flex items-center gap-2.5 px-6 pt-2.5 pb-2.5 rounded-t-2xl rounded-b-none bg-[#0c121e] border-2 border-b-0 border-accent text-white shadow-[0_-8px_25px_rgba(0,0,0,0.9)] hover:scale-105 active:scale-95 transition-all cursor-pointer select-none !m-0 !mt-0 !mb-0"
                style="background-color: #0c121e !important; margin: 0 !important; margin-top: 0 !important; margin-bottom: 0 !important;"
                title="Buka Filter Rute">

                <i class="fa-solid fa-sliders text-accent text-sm group-hover:scale-110 transition-transform"></i>

                <span class="text-xs font-bold text-white tracking-wide uppercase">
                    Filter Rute
                </span>

                <span id="mobile-filter-active-dot"
                    class="hidden w-2.5 h-2.5 bg-accent rounded-full animate-pulse">
                </span>

            </button>
        </div>

        <!-- Search & Filter Wrapper (Desktop In-Line & Mobile Top Modal) -->
        <div id="mobile-filter-backdrop" class="fixed inset-0 bg-black/85 backdrop-blur-sm z-[99990] hidden transition-opacity" style="position: fixed; z-index: 99990 !important;"></div>

        <div id="filter-wrapper-container" class="hidden md:block fixed md:static inset-x-3 top-20 z-[99999] md:z-auto bg-[#0c121e] border border-accent/40 md:border-0 rounded-2xl md:rounded-2xl max-h-[85vh] md:max-h-none overflow-y-auto md:overflow-visible shadow-2xl md:shadow-none p-5 md:p-0 transition-all" style="background-color: #0c121e !important; z-index: 99999 !important;">
            <form id="form-gpx-filter" method="GET" action="{{ route('gpx.index') }}" class="bg-[#0c121e] md:border md:border-slate-800 p-0 md:p-6 md:rounded-2xl space-y-4">
                <!-- GPS Coordinates Hidden Inputs -->
                <input type="hidden" name="user_lat" id="filter-user-lat" value="{{ request('user_lat', request('lat')) }}">
                <input type="hidden" name="user_lng" id="filter-user-lng" value="{{ request('user_lng', request('lng')) }}">

                <!-- Mobile Header with Close Button (Visible only on mobile) -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 md:hidden">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-accent/15 border border-accent/40 flex items-center justify-center text-accent">
                            <i class="fa-solid fa-sliders text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Filter & Pencarian Rute</h3>
                            <p class="text-[10px] text-slate-400">Atur filter jarak, radius, kota, dan elevasi</p>
                        </div>
                    </div>
                    <button id="btn-close-mobile-filter" type="button" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Tutup Filter">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Top Row: Tipe Rute Segmented Radio & Search Box -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Tipe Rute (Segmented Radio Group like Submit Modal) -->
                        <div class="space-y-1.5 flex-1 max-w-xl">
                            <label class="block text-xs text-slate-200 font-semibold">Tipe Rute</label>
                            <div class="grid grid-cols-4 gap-1.5 bg-[#090D16] p-1 rounded-xl border border-slate-700 select-none">
                                <label class="route-type-filter-btn {{ (!request('route_type') || request('route_type') == '') ? 'is-active bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center justify-center py-2 px-2 rounded-lg cursor-pointer text-xs transition border text-center shadow-sm">
                                    <input type="radio" name="route_type" value="" class="hidden" {{ (!request('route_type') || request('route_type') == '') ? 'checked' : '' }}>
                                    <span>Semua</span>
                                </label>
                                <label class="route-type-filter-btn {{ request('route_type') == 'road' ? 'is-active bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center justify-center py-2 px-2 rounded-lg cursor-pointer text-xs transition border text-center shadow-sm">
                                    <input type="radio" name="route_type" value="road" class="hidden" {{ request('route_type') == 'road' ? 'checked' : '' }}>
                                    <span>Road</span>
                                </label>
                                <label class="route-type-filter-btn {{ request('route_type') == 'trail' ? 'is-active bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center justify-center py-2 px-2 rounded-lg cursor-pointer text-xs transition border text-center shadow-sm">
                                    <input type="radio" name="route_type" value="trail" class="hidden" {{ request('route_type') == 'trail' ? 'checked' : '' }}>
                                    <span>Trail</span>
                                </label>
                                <label class="route-type-filter-btn {{ request('route_type') == 'track' ? 'is-active bg-slate-800 text-white font-bold border-slate-600' : 'text-slate-300 hover:text-white border-transparent' }} flex items-center justify-center py-2 px-2 rounded-lg cursor-pointer text-xs transition border text-center shadow-sm">
                                    <input type="radio" name="route_type" value="track" class="hidden" {{ request('route_type') == 'track' ? 'checked' : '' }}>
                                    <span>Track</span>
                                </label>
                            </div>
                        </div>

                        <!-- Cari Rute Keyword -->
                        <div class="space-y-1.5 flex-1">
                            <label class="block text-xs text-slate-200 font-semibold">Cari Rute</label>
                            <div class="relative">
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama rute..." 
                                    class="w-full bg-[#090D16] border border-slate-700 rounded-xl pl-9 pr-3 py-2 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Row: Kota, Radius, Jarak, Elevasi, Urutan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                        
                        <!-- Kota -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs text-slate-200 font-semibold">Kota</label>
                                <button id="btn-detect-user-city" type="button" class="text-xs text-slate-300 hover:text-white flex items-center gap-1 font-semibold cursor-pointer" title="Deteksi GPS">
                                    <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                                    <span>GPS</span>
                                </button>
                            </div>
                            <div class="relative" id="filter-city-combobox-wrap">
                                <div class="relative flex items-center">
                                    <input type="text" 
                                        id="select-filter-city" 
                                        name="city" 
                                        value="{{ request('city') }}" 
                                        placeholder="Semua kota / cari..." 
                                        autocomplete="off"
                                        class="w-full bg-[#090D16] border border-slate-700 rounded-xl pl-8 pr-12 py-2 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition cursor-text">
                                    
                                    <div class="pointer-events-none absolute left-2.5 text-slate-400">
                                        <i class="fa-solid fa-location-dot text-xs text-slate-400"></i>
                                    </div>

                                    <div class="absolute right-2 flex items-center gap-1">
                                        <button type="button" id="btn-clear-filter-city" class="text-slate-400 hover:text-white p-1 text-xs cursor-pointer {{ request('city') ? '' : 'hidden' }}" title="Hapus Filter Kota">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <button type="button" id="btn-toggle-filter-city" class="text-slate-400 hover:text-white p-1 text-[10px] cursor-pointer" title="Lihat Pilihan Kota">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Floating Autocomplete / Selection Menu (100% Solid Opaque Dark) -->
                                <div id="filter-city-dropdown" class="hidden absolute left-0 right-0 top-full mt-1.5 z-[99999] bg-[#0c121e] border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto" style="background-color: #0c121e !important; background: #0c121e !important; opacity: 1 !important; z-index: 99999 !important;">
                                    <!-- Populated dynamically by JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Radius GPS -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs text-slate-200 font-semibold">Radius GPS</label>
                                <span id="label-gps-active" class="text-[10px] text-emerald-400 font-mono {{ $hasCoordinates ? '' : 'hidden' }}">Aktif</span>
                            </div>
                            <div class="relative">
                                <select id="select-filter-radius" name="radius" 
                                    class="w-full bg-[#090D16] border border-slate-700 rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:border-slate-500 transition cursor-pointer [&>option]:bg-[#0c121e]">
                                    <option value="">Semua radius</option>
                                    <option value="5" {{ request('radius') == '5' ? 'selected' : '' }}>Radius &lt; 5 km</option>
                                    <option value="10" {{ request('radius') == '10' ? 'selected' : '' }}>Radius 10 km</option>
                                    <option value="25" {{ request('radius') == '25' ? 'selected' : '' }}>Radius 25 km</option>
                                    <option value="50" {{ request('radius') == '50' ? 'selected' : '' }}>Radius 50 km</option>
                                    <option value="100" {{ request('radius') == '100' ? 'selected' : '' }}>Radius 100 km</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-200">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Jarak Rute -->
                        <div>
                            <label class="block text-xs text-slate-200 mb-1.5 font-semibold">Jarak Rute</label>
                            <div class="relative">
                                <select name="distance" 
                                    class="w-full bg-[#090D16] border border-slate-700 rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:border-slate-500 transition cursor-pointer [&>option]:bg-[#0c121e]">
                                    <option value="">Semua jarak</option>
                                    <option value="under_5k" {{ request('distance') == 'under_5k' ? 'selected' : '' }}>&lt; 5 km</option>
                                    <option value="5k_10k" {{ request('distance') == '5k_10k' ? 'selected' : '' }}>5 – 10 km</option>
                                    <option value="10k_21k" {{ request('distance') == '10k_21k' ? 'selected' : '' }}>10 – 21,1 km</option>
                                    <option value="over_21k" {{ request('distance') == 'over_21k' ? 'selected' : '' }}>&gt; 21,1 km</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-200">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Elevasi -->
                        <div>
                            <label class="block text-xs text-slate-200 mb-1.5 font-semibold">Elevasi</label>
                            <div class="relative">
                                <select name="elevation" 
                                    class="w-full bg-[#090D16] border border-slate-700 rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:border-slate-500 transition cursor-pointer [&>option]:bg-[#0c121e]">
                                    <option value="">Semua elevasi</option>
                                    <option value="flat" {{ request('elevation') == 'flat' ? 'selected' : '' }}>Datar (&lt; 100 m)</option>
                                    <option value="hilly" {{ request('elevation') == 'hilly' ? 'selected' : '' }}>Berbukit (100 – 300 m)</option>
                                    <option value="mountainous" {{ request('elevation') == 'mountainous' ? 'selected' : '' }}>Pegunungan (&gt; 300 m)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-200">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Urutan (Sorting) -->
                        <div>
                            <label class="block text-xs text-slate-200 mb-1.5 font-semibold">Urutan</label>
                            <div class="relative">
                                <select name="sort" id="select-filter-sort"
                                    class="w-full bg-[#090D16] border border-slate-700 rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:border-slate-500 transition cursor-pointer [&>option]:bg-[#0c121e]">
                                    <option value="nearest" {{ (request('sort') == 'nearest' || (!request('sort') && $hasCoordinates)) ? 'selected' : '' }}>Lokasi Terdekat (GPS)</option>
                                    <option value="latest" {{ (request('sort') == 'latest' || (!request('sort') && !$hasCoordinates)) ? 'selected' : '' }}>Rute Terbaru</option>
                                    <option value="distance_asc" {{ request('sort') == 'distance_asc' ? 'selected' : '' }}>Jarak Terpendek</option>
                                    <option value="distance_desc" {{ request('sort') == 'distance_desc' ? 'selected' : '' }}>Jarak Terpanjang</option>
                                    <option value="elevation_desc" {{ request('sort') == 'elevation_desc' ? 'selected' : '' }}>Elevasi Tertinggi</option>
                                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Abjad Judul (A - Z)</option>
                                    <option value="city" {{ request('sort') == 'city' ? 'selected' : '' }}>Berdasarkan Kota</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-200">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                    <div class="text-xs sm:text-sm text-slate-200">
                        Menampilkan <strong id="gpx-total-count" class="text-white font-bold">{{ $items->total() }}</strong> rute
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="btn-reset-filters" class="px-3.5 py-2 rounded-xl border border-slate-700 hover:border-slate-500 text-slate-300 hover:text-white text-xs sm:text-sm transition cursor-pointer">
                            Reset
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-accent text-slate-950 hover:bg-white text-xs sm:text-sm font-bold transition flex items-center gap-1.5 cursor-pointer shadow-accent">
                            <i class="fa-solid fa-sliders text-xs"></i>
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Dynamic AJAX GPX Cards & Pagination Container -->
        <div id="gpx-catalog-results" class="relative transition-all duration-200">
            @include('gpx.partials.cards')
        </div>

        <!-- FAQ / Educational Section -->
        <section class="mt-16 pt-12 border-t border-slate-800 space-y-6">
            <div class="max-w-2xl">
                <p class="text-sm text-accent font-medium mb-1">Panduan</p>
                <h2 class="text-2xl font-bold text-white tracking-tight">
                    Cara memakai file GPX untuk lari
                </h2>
                <p class="text-slate-200 text-sm leading-relaxed mt-1">
                    Pelajari cara mengimpor rute GPX ke jam Garmin, Coros, dan aplikasi training favoritmu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-accent text-xs"></i>
                        Apa itu file GPX?
                    </h3>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        GPX (GPS Exchange Format) adalah format standar yang menyimpan koordinat, jarak, dan elevasi rute. File ini dipakai untuk navigasi turn-by-turn di jam olahraga dan aplikasi lari.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-watch text-accent text-xs"></i>
                        Cara import ke Garmin
                    </h3>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        Unduh file GPX → buka Garmin Connect → Training & Planning → Courses → Import → pilih file → Send to Device.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-mobile-screen text-accent text-xs"></i>
                        Cara import ke Coros
                    </h3>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        Unduh file GPX → buka di aplikasi COROS → simpan ke Route Library → Sync with Watch.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-award text-accent text-xs"></i>
                        Dapat poin dengan berbagi rute
                    </h3>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        Setiap rute yang kamu unggah memberikan +10 Run Points. Poin bisa dipakai untuk fitur-fitur di RuangLari.
                    </p>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Submit GPX Modal (Solid Opaque High-Contrast) -->
<div id="modal-submit-gpx" class="fixed inset-0 z-[99999] bg-black/90 hidden items-center justify-center p-4" style="z-index: 99999 !important;">
    <div class="bg-[#0c121e] border border-slate-700 rounded-2xl max-w-xl w-full p-6 md:p-8 space-y-5 shadow-2xl relative max-h-[90vh] overflow-y-auto text-slate-200" style="background-color: #0c121e !important; opacity: 1 !important;">
        
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <p class="text-xs text-slate-400 font-medium">Kontribusi Komunitas</p>
                <h3 class="text-lg font-bold text-white mt-0.5">Bagikan Rute GPX</h3>
            </div>
            <button id="btn-close-submit-gpx-modal" type="button" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Tutup Modal">
                <i class="fa-solid fa-times text-xs"></i>
            </button>
        </div>

        <div id="gpx-modal-alert" class="hidden"></div>

        <form id="form-submit-gpx-modal" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <!-- Anti-Bot Honeypot -->
            <input type="text" name="website_url" class="hidden" style="display:none !important;" tabindex="-1" autocomplete="off">

            <div>
                <label class="block text-sm font-semibold text-white mb-2">
                    File GPX <span class="text-rose-400">*</span>
                </label>
                
                <!-- Interactive Dropzone -->
                <div id="modal-gpx-dropzone" class="relative border-2 border-dashed border-slate-700 hover:border-slate-500 rounded-2xl p-5 transition-all duration-200 bg-[#111724] text-center cursor-pointer group flex flex-col items-center justify-center min-h-[130px] select-none" style="background-color: #111724 !important;">
                    <input id="input-modal-gpx-file" name="gpx_file" type="file" accept=".gpx,application/gpx+xml,text/xml" class="hidden" required>
                    
                    <!-- Empty State -->
                    <div id="dropzone-empty-state" class="flex flex-col items-center space-y-2 pointer-events-none">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 group-hover:text-white transition-all duration-200">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">
                                Tarik & lepaskan file <span class="text-white font-bold">GPX</span> di sini, atau <span class="text-white underline underline-offset-2">pilih file</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 font-mono">Format .gpx (maksimal 10 MB)</p>
                        </div>
                    </div>

                    <!-- Loaded File State -->
                    <div id="dropzone-file-state" class="hidden w-full flex items-center justify-between gap-3 bg-slate-900 border border-slate-700 rounded-xl p-3 text-left">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 text-white flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-file-lines text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <div id="dropzone-filename" class="text-xs sm:text-sm font-bold text-white truncate">route.gpx</div>
                                <div id="dropzone-filesize" class="text-[11px] text-slate-300 font-mono">0 KB • Siap diproses</div>
                            </div>
                        </div>
                        <button type="button" id="btn-dropzone-remove" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition shrink-0 cursor-pointer">
                            <i class="fa-solid fa-rotate-left mr-1"></i> Ganti
                        </button>
                    </div>
                </div>
            </div>

            <div id="modal-gpx-preview-container" class="hidden space-y-3 p-4 rounded-xl bg-[#111724] border border-slate-700" style="background-color: #111724 !important;">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-200 font-medium">Preview Rute</span>
                    <span id="modal-gpx-point-count" class="text-[#FC4C02] font-semibold font-mono">0 pts</span>
                </div>
                <div id="modal-gpx-preview-map" class="h-44 w-full rounded-xl overflow-hidden border border-slate-700"></div>
                <div class="grid grid-cols-3 gap-2 text-center text-sm">
                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-800">
                        <span class="text-xs text-slate-300 block">Jarak</span>
                        <strong id="modal-stat-distance" class="text-white">-</strong>
                    </div>
                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-800">
                        <span class="text-xs text-slate-300 block">Gain</span>
                        <strong id="modal-stat-gain" class="text-white">-</strong>
                    </div>
                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-800">
                        <span class="text-xs text-slate-300 block">Loss</span>
                        <strong id="modal-stat-loss" class="text-white">-</strong>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm text-slate-200 mb-1.5 font-medium">
                    Judul Rute <span class="text-rose-400">*</span>
                </label>
                <input id="input-modal-title" type="text" name="title" required
                    class="w-full bg-[#111724] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition"
                    placeholder="Contoh: Rute CFD Sudirman – GBK Loop">
            </div>

            <div>
                <label class="block text-sm text-slate-200 mb-1.5 font-medium">
                    Tipe Rute
                </label>
                <div class="grid grid-cols-3 gap-2" id="modal-route-type-group">
                    <label class="route-type-btn is-active flex items-center justify-center p-2.5 rounded-xl border border-slate-700 bg-[#111724] hover:border-slate-500 cursor-pointer text-xs font-semibold text-center select-none shadow-sm transition">
                        <input type="radio" name="route_type" value="road" class="hidden" checked>
                        <span>Road</span>
                    </label>
                    <label class="route-type-btn flex items-center justify-center p-2.5 rounded-xl border border-slate-700 bg-[#111724] hover:border-slate-500 cursor-pointer text-xs font-semibold text-center select-none shadow-sm transition">
                        <input type="radio" name="route_type" value="trail" class="hidden">
                        <span>Trail</span>
                    </label>
                    <label class="route-type-btn flex items-center justify-center p-2.5 rounded-xl border border-slate-700 bg-[#111724] hover:border-slate-500 cursor-pointer text-xs font-semibold text-center select-none shadow-sm transition">
                        <input type="radio" name="route_type" value="track" class="hidden">
                        <span>Track</span>
                    </label>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm text-slate-200 font-medium">
                        Kota / Kabupaten <span class="text-rose-400">*</span>
                    </label>
                    <button id="btn-modal-detect-location" type="button" class="text-xs text-slate-300 hover:text-white flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                        <span>Deteksi otomatis</span>
                    </button>
                </div>
                <div class="relative" id="modal-city-autocomplete-wrapper">
                    <div class="relative">
                        <input id="input-modal-city" type="text" name="city" required autocomplete="off"
                            class="w-full bg-[#111724] border border-slate-700 rounded-xl pl-9 pr-8 py-2.5 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition"
                            placeholder="Ketik nama kota / kabupaten di Indonesia...">
                        <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <div id="modal-city-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-white text-sm">
                            <i class="fa-solid fa-spinner animate-spin"></i>
                        </div>
                    </div>

                    <!-- Floating Autocomplete Dropdown List (Solid Opaque Dark Background) -->
                    <div id="modal-city-autocomplete-list" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-[#0c121e] border border-slate-700 rounded-xl shadow-2xl z-[99999] max-h-52 overflow-y-auto divide-y divide-slate-800" style="background-color: #0c121e !important; background: #0c121e !important; opacity: 1 !important; z-index: 99999 !important;">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm text-slate-200 mb-1.5 font-medium">Catatan Tambahan (opsional)</label>
                <textarea name="notes" rows="2"
                    class="w-full bg-[#111724] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition"
                    placeholder="Contoh: Aspal mulus, cocok long run pagi. Ada water station di km 5."></textarea>
            </div>

            <input type="hidden" id="input-modal-dist" name="client_distance_km">
            <input type="hidden" id="input-modal-gain" name="client_elevation_gain">
            <input type="hidden" id="input-modal-loss" name="client_elevation_loss">
            <input type="hidden" id="input-modal-coords" name="coordinates_json">

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('btn-close-submit-gpx-modal').click()"
                    class="px-4 py-2.5 rounded-xl border border-slate-700 hover:border-slate-600 bg-slate-800 text-slate-300 hover:text-white text-sm font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button id="btn-submit-gpx-form" type="submit"
                    class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold transition border border-slate-700 flex items-center gap-2 cursor-pointer shadow-md">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Kirim Rute</span>
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
            const isUserLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

            // ==========================================
            // INTERACTIVE GPX EXPLORER MAP (Stadia Alidade Smooth & Clustering)
            // ==========================================
            const mapContainer = document.getElementById('gpx-explorer-map');
            const allInitialMapRoutes = @json($mapRoutes ?? []);
            const mapRoutesCountEl = document.getElementById('gpx-map-routes-count');
            let explorerMap = null;
            let explorerClusterGroup = null;

            function getPinIcon(routeType) {
                let color = '#ccff00';
                let textColor = '#020617';
                let iconClass = 'fa-person-running';
                if (routeType === 'trail') {
                    color = '#10b981';
                    iconClass = 'fa-mountain';
                    textColor = '#ffffff';
                } else if (routeType === 'track') {
                    color = '#38bdf8';
                    iconClass = 'fa-stopwatch';
                    textColor = '#020617';
                }

                return L.divIcon({
                    className: 'custom-gpx-pin-wrapper',
                    html: `<div style="
                        background: ${color};
                        color: ${textColor};
                        width: 30px;
                        height: 30px;
                        border-radius: 50% 50% 50% 0;
                        transform: rotate(-45deg);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 3px 8px rgba(0,0,0,0.35), 0 0 0 2px #0c121e;
                        cursor: pointer;
                    ">
                        <i class="fa-solid ${iconClass}" style="transform: rotate(45deg); font-size: 12px;"></i>
                    </div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -30]
                });
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function updateExplorerMapMarkers(routesList, shouldZoom = true) {
                if (!explorerMap || !explorerClusterGroup) return;

                const routes = Array.isArray(routesList) ? routesList : [];
                explorerClusterGroup.clearLayers();

                if (mapRoutesCountEl) {
                    mapRoutesCountEl.textContent = `${routes.length} Titik Rute`;
                }

                const validBounds = [];

                routes.forEach(route => {
                    if (route.start_lat && route.start_lng && !isNaN(route.start_lat) && !isNaN(route.start_lng)) {
                        const marker = L.marker([route.start_lat, route.start_lng], {
                            icon: getPinIcon(route.route_type)
                        });

                        let typeBadgeClass = 'bg-[#ccff00]/15 text-accent border border-accent/30';
                        if (route.route_type === 'trail') {
                            typeBadgeClass = 'bg-[#10b981]/15 text-emerald-400 border border-emerald-500/30';
                        } else if (route.route_type === 'track') {
                            typeBadgeClass = 'bg-[#38bdf8]/15 text-sky-400 border border-sky-500/30';
                        }

                        const popupHtml = `
                            <div class="gpx-map-popup-card">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider font-mono ${typeBadgeClass}">
                                        ${escapeHtml(route.route_type_label || route.route_type)}
                                    </span>
                                    <span class="text-xs text-slate-300 flex items-center gap-1 pr-3">
                                        <i class="fa-solid fa-location-dot text-accent text-[10px]"></i>
                                        <span>${escapeHtml(route.city || 'Indonesia')}</span>
                                    </span>
                                </div>
                                
                                <h4 class="text-sm font-bold text-white mb-2.5 line-clamp-2 leading-snug">
                                    <a href="${route.url}" class="text-white hover:text-accent transition-colors" style="color: #ffffff !important;">
                                        ${escapeHtml(route.title)}
                                    </a>
                                </h4>
                                
                                <div class="grid grid-cols-3 gap-1.5 text-center mb-3 bg-[#090D16] p-2 rounded-xl border border-slate-800">
                                    <div>
                                        <div class="text-[10px] text-slate-400">Jarak</div>
                                        <div class="text-xs font-bold text-white font-mono">${route.distance_km ? route.distance_km + ' km' : '-'}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-400">Gain</div>
                                        <div class="text-xs font-bold text-white font-mono">+${route.elevation_gain_m}m</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-400">Loss</div>
                                        <div class="text-xs font-bold text-white font-mono">-${route.elevation_loss_m}m</div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <a href="${route.url}" class="btn-popup-detail flex-1 px-3 py-1.5 rounded-lg bg-accent text-slate-950 hover:bg-white text-xs font-bold transition flex items-center justify-center gap-1 text-center shadow-sm" style="color: #020617 !important;">
                                        <span style="color: #020617 !important;">Detail Rute</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]" style="color: #020617 !important;"></i>
                                    </a>
                                    <a href="${route.download_url}" class="btn-popup-download px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium transition flex items-center justify-center gap-1 border border-slate-700" title="Unduh GPX">
                                        <i class="fa-solid fa-download text-[11px]"></i>
                                    </a>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupHtml, {
                            maxWidth: 300,
                            minWidth: 240,
                            className: 'gpx-custom-leaflet-popup'
                        });

                        explorerClusterGroup.addLayer(marker);
                        validBounds.push([route.start_lat, route.start_lng]);
                    }
                });

                if (shouldZoom && validBounds.length > 0) {
                    try {
                        if (validBounds.length === 1) {
                            explorerMap.setView(validBounds[0], 14, { animate: true });
                        } else {
                            const bounds = L.latLngBounds(validBounds);
                            if (bounds.isValid()) {
                                explorerMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 14, animate: true });
                            }
                        }
                    } catch(e) {}
                }
            }

            function initExplorerMap() {
                if (!mapContainer || explorerMap) return;

                // Center of Indonesia as default fallback
                const defaultCenter = [-2.5489, 118.0149];
                const defaultZoom = 5;

                explorerMap = L.map('gpx-explorer-map', {
                    zoomControl: true,
                    attributionControl: false,
                    scrollWheelZoom: true
                }).setView(defaultCenter, defaultZoom);

                // CARTO Light (Positron) Tile Layer
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: 'abcd',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
                }).addTo(explorerMap);

                // Initialize Marker Cluster Group with custom styling
                explorerClusterGroup = L.markerClusterGroup({
                    showCoverageOnHover: false,
                    maxClusterRadius: 45,
                    spiderfyOnMaxZoom: true,
                    iconCreateFunction: function(cluster) {
                        const count = cluster.getChildCount();
                        let size = 36;
                        let cClass = 'gpx-cluster-small';
                        if (count >= 10) {
                            size = 42;
                            cClass = 'gpx-cluster-medium';
                        }
                        if (count >= 50) {
                            size = 50;
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
                                explorerMap.fitBounds(bounds, { padding: [35, 35], maxZoom: 12 });
                            }
                        } catch(err) {}
                    }
                });
            }

            // Minimize / Expand Toggle Engine
            const btnToggleExplorerMap = document.getElementById('btn-toggle-explorer-map');
            const btnMapMinimizeToggle = document.getElementById('btn-map-minimize-toggle');
            const explorerMapCollapseWrap = document.getElementById('explorer-map-collapse-wrap');
            const iconMapToggle = document.getElementById('icon-map-toggle');
            const labelMapToggle = document.getElementById('label-map-toggle');
            let isMapMinimized = false;

            function toggleExplorerMap(forceState) {
                if (!explorerMapCollapseWrap) return;
                
                isMapMinimized = (typeof forceState === 'boolean') ? forceState : !isMapMinimized;

                if (isMapMinimized) {
                    explorerMapCollapseWrap.style.display = 'none';
                    if (iconMapToggle) iconMapToggle.classList.add('rotate-180');
                    if (labelMapToggle) labelMapToggle.textContent = 'Tampilkan';
                } else {
                    explorerMapCollapseWrap.style.display = 'block';
                    if (iconMapToggle) iconMapToggle.classList.remove('rotate-180');
                    if (labelMapToggle) labelMapToggle.textContent = 'Sembunyikan';
                    
                    setTimeout(() => {
                        if (explorerMap) {
                            explorerMap.invalidateSize();
                        }
                    }, 100);
                }
            }

            if (btnToggleExplorerMap) {
                btnToggleExplorerMap.addEventListener('click', function() {
                    toggleExplorerMap();
                });
            }
            if (btnMapMinimizeToggle) {
                btnMapMinimizeToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleExplorerMap();
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

            // ==========================================
            // SEARCHABLE CITY FILTER COMBOBOX ENGINE
            // ==========================================
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
                    <div class="p-2.5 border-b border-slate-800 bg-[#111724] text-[10px] text-slate-300 font-bold uppercase tracking-wider flex items-center justify-between" style="background-color: #111724 !important;">
                        <span>Pilihan Kota Katalog</span>
                        <span class="text-white font-bold">${matches.length} kota</span>
                    </div>
                    <div class="py-1 divide-y divide-slate-800/60 bg-[#0c121e]" style="background-color: #0c121e !important;">
                        <button type="button" data-city="" class="city-filter-option w-full text-left px-3.5 py-2.5 text-xs font-bold text-white hover:bg-[#111724] flex items-center gap-2.5 transition cursor-pointer" style="background-color: #0c121e !important;">
                            <i class="fa-solid fa-globe text-[11px] text-slate-400"></i>
                            <span>Semua Kota (Reset)</span>
                        </button>
                `;

                if (matches.length > 0) {
                    matches.forEach(city => {
                        const isSelected = selectCity?.value?.trim().toLowerCase() === city.toLowerCase();
                        html += `
                            <button type="button" data-city="${city}" class="city-filter-option w-full text-left px-3.5 py-2.5 text-xs font-medium text-white hover:bg-[#111724] flex items-center justify-between transition cursor-pointer ${isSelected ? 'bg-slate-800 font-bold' : ''}" style="${isSelected ? 'background-color: #1e293b !important;' : 'background-color: #0c121e !important;'}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <i class="fa-solid fa-location-dot text-[11px] ${isSelected ? 'text-white' : 'text-slate-400'}"></i>
                                    <span class="truncate ${isSelected ? 'text-white font-bold' : 'text-slate-200'}">${city}</span>
                                </div>
                                ${isSelected ? '<i class="fa-solid fa-check text-white text-[11px]"></i>' : ''}
                            </button>
                        `;
                    });
                } else {
                    html += `
                        <div class="px-3.5 py-3 text-xs text-slate-300 text-center bg-[#0c121e]" style="background-color: #0c121e !important;">
                            Tidak ada kota katalog bernama "<strong class="text-white">${cleanQuery}</strong>".
                        </div>
                    `;
                }

                html += `</div>`;
                filterCityDropdown.innerHTML = html;
                filterCityDropdown.classList.remove('hidden');
            }

            function selectFilterCity(cityName) {
                if (selectCity) {
                    selectCity.value = cityName;
                }
                if (btnClearFilterCity) {
                    if (cityName) {
                        btnClearFilterCity.classList.remove('hidden');
                    } else {
                        btnClearFilterCity.classList.add('hidden');
                    }
                }
                if (filterCityDropdown) {
                    filterCityDropdown.classList.add('hidden');
                }
                applyFiltersAjax();
            }

            if (selectCity) {
                selectCity.addEventListener('focus', function() {
                    renderCityFilterDropdown(this.value);
                });

                selectCity.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (btnClearFilterCity) {
                        if (val) {
                            btnClearFilterCity.classList.remove('hidden');
                        } else {
                            btnClearFilterCity.classList.add('hidden');
                        }
                    }
                    clearTimeout(cityFilterDebounce);
                    cityFilterDebounce = setTimeout(() => {
                        renderCityFilterDropdown(val);
                        if (!val) {
                            applyFiltersAjax();
                        }
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

            // Click option or outside listener
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
                    if (filterCityDropdown) {
                        filterCityDropdown.classList.add('hidden');
                    }
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
                        <polyline points="${pointsStr}" fill="none" stroke="#FC4C02" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="${startX}" cy="${startY}" r="4" fill="#22c55e" stroke="#000" stroke-width="1.5" />
                        <circle cx="${endX}" cy="${endY}" r="4" fill="#FC4C02" stroke="#000" stroke-width="1.5" />
                    `;
                });
            }

            // Initial SVG render
            renderAllRouteSvgs();

            async function fetchGpxResults(targetUrl, pushToHistory = true) {
                if (!gpxCatalogResults) return;

                if (currentFilterAbortCtrl) {
                    currentFilterAbortCtrl.abort();
                }
                currentFilterAbortCtrl = new AbortController();

                // Smooth loading indicator
                gpxCatalogResults.style.opacity = '0.35';
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
                                window.history.pushState({ gpxFilterUrl: targetUrl }, '', targetUrl);
                            }
                        }
                    }
                } catch(err) {
                    if (err.name !== 'AbortError') {
                        console.error('GPX Filter Error:', err);
                    }
                } finally {
                    gpxCatalogResults.style.opacity = '1';
                    gpxCatalogResults.style.pointerEvents = 'auto';
                }
            }

            // ==========================================
            // MOBILE FLOATING FILTER DRAWER CONTROLLER
            // ==========================================
            const btnOpenMobileFilter = document.getElementById('btn-open-mobile-filter');
            const btnCloseMobileFilter = document.getElementById('btn-close-mobile-filter');
            const mobileFilterBackdrop = document.getElementById('mobile-filter-backdrop');
            const filterWrapperContainer = document.getElementById('filter-wrapper-container');
            const mobileFilterActiveDot = document.getElementById('mobile-filter-active-dot');

            function openMobileFilterDrawer() {
                if (!filterWrapperContainer) return;
                filterWrapperContainer.classList.remove('hidden');
                if (mobileFilterBackdrop) mobileFilterBackdrop.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileFilterDrawer() {
                if (!filterWrapperContainer) return;
                if (window.innerWidth < 768) {
                    filterWrapperContainer.classList.add('hidden');
                }
                if (mobileFilterBackdrop) mobileFilterBackdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    filterWrapperContainer?.classList.remove('hidden');
                    if (mobileFilterBackdrop) mobileFilterBackdrop.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
                    if (mobileFilterBackdrop && mobileFilterBackdrop.classList.contains('hidden')) {
                        filterWrapperContainer?.classList.add('hidden');
                    }
                }
            });

            function updateMobileFilterActiveBadge() {
                if (!filterForm || !mobileFilterActiveDot) return;
                const qVal = searchInput?.value?.trim() || '';
                const cityVal = selectCity?.value?.trim() || '';
                const radiusVal = document.getElementById('select-filter-radius')?.value?.trim() || '';
                const distVal = filterForm.querySelector('select[name="distance"]')?.value?.trim() || '';
                const eleVal = filterForm.querySelector('select[name="elevation"]')?.value?.trim() || '';
                const sortVal = filterForm.querySelector('select[name="sort"]')?.value?.trim() || 'default';
                const latVal = document.getElementById('filter-user-lat')?.value?.trim() || '';
                const routeTypeVal = filterForm.querySelector('input[name="route_type"]:checked')?.value?.trim() || '';

                const hasActiveFilters = qVal !== '' || cityVal !== '' || radiusVal !== '' || distVal !== '' || eleVal !== '' || latVal !== '' || routeTypeVal !== '' || (sortVal !== '' && sortVal !== 'default' && sortVal !== 'nearest');
                if (hasActiveFilters) {
                    mobileFilterActiveDot.classList.remove('hidden');
                } else {
                    mobileFilterActiveDot.classList.add('hidden');
                }
            }

            // Sync Route Type Pills (Both on Map & in Filter Form)
            window.setMapAndFormRouteType = function(type) {
                const targetVal = type || '';
                
                // Update Radio in form
                const radio = filterForm?.querySelector(`input[name="route_type"][value="${targetVal}"]`);
                if (radio) {
                    radio.checked = true;
                }

                // Update form segmented buttons UI
                document.querySelectorAll('.route-type-filter-btn').forEach(btn => {
                    const input = btn.querySelector('input[name="route_type"]');
                    const isActive = (input?.value || '') === targetVal;
                    if (isActive) {
                        btn.className = 'route-type-filter-btn is-active bg-slate-800 text-white font-bold border-slate-600 flex items-center justify-center py-2 px-2 rounded-lg cursor-pointer text-xs transition border text-center shadow-sm';
                    } else {
                        btn.className = 'route-type-filter-btn text-slate-300 hover:text-white border-transparent flex items-center justify-center py-2 px-2 rounded-lg cursor-pointer text-xs transition border text-center shadow-sm';
                    }
                });

                // Update map overlay pills UI
                document.querySelectorAll('#gpx-map-type-filter-overlay .btn-map-type-pill').forEach(pill => {
                    const onclickAttr = pill.getAttribute('onclick') || '';
                    const isPillActive = onclickAttr.includes(`'${targetVal}'`);
                    if (isPillActive) {
                        pill.classList.add('bg-slate-800', 'text-white', 'font-bold', 'border-slate-600');
                        pill.classList.remove('text-slate-300', 'border-transparent');
                    } else {
                        pill.classList.remove('bg-slate-800', 'text-white', 'font-bold', 'border-slate-600');
                        pill.classList.add('text-slate-300', 'border-transparent');
                    }
                });

                applyFiltersAjax();
            };

            // Form radio change listener
            document.querySelectorAll('input[name="route_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    window.setMapAndFormRouteType(this.value);
                });
            });

            if (btnOpenMobileFilter) {
                btnOpenMobileFilter.addEventListener('click', openMobileFilterDrawer);
            }
            if (btnCloseMobileFilter) {
                btnCloseMobileFilter.addEventListener('click', closeMobileFilterDrawer);
            }
            if (mobileFilterBackdrop) {
                mobileFilterBackdrop.addEventListener('click', closeMobileFilterDrawer);
            }

            // Initial active filter check
            updateMobileFilterActiveBadge();

            function applyFiltersAjax() {
                if (!filterForm) return;
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();

                for (const [key, value] of formData.entries()) {
                    if (value && value.toString().trim() !== '') {
                        params.set(key, value.toString().trim());
                    }
                }

                const baseUrl = filterForm.action || '{{ route("gpx.index") }}';
                const queryString = params.toString();
                const targetUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;

                updateMobileFilterActiveBadge();
                fetchGpxResults(targetUrl, true);
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    applyFiltersAjax();
                    if (window.innerWidth < 768) {
                        closeMobileFilterDrawer();
                    }
                });

                // Auto filter on select change & sort nearest GPS handling
                filterForm.querySelectorAll('select').forEach(sel => {
                    sel.addEventListener('change', function() {
                        if (this.id === 'select-filter-sort' && this.value === 'nearest') {
                            const inputLat = document.getElementById('filter-user-lat');
                            const inputLng = document.getElementById('filter-user-lng');
                            if (!inputLat?.value || !inputLng?.value) {
                                triggerGeoLocation(true);
                                return;
                            }
                        }
                        applyFiltersAjax();
                    });
                });

                // Live search with debounce
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        clearTimeout(filterDebounceTimer);
                        filterDebounceTimer = setTimeout(() => {
                            applyFiltersAjax();
                        }, 250);
                    });
                }
            }

            // Reset filters button in filter bar
            const btnResetFilters = document.getElementById('btn-reset-filters');
            if (btnResetFilters) {
                btnResetFilters.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (filterForm) filterForm.reset();
                    if (searchInput) searchInput.value = '';
                    if (selectCity) selectCity.value = '';
                    if (btnClearFilterCity) btnClearFilterCity.classList.add('hidden');
                    if (filterCityDropdown) filterCityDropdown.classList.add('hidden');
                    const inputLat = document.getElementById('filter-user-lat');
                    const inputLng = document.getElementById('filter-user-lng');
                    if (inputLat) inputLat.value = '';
                    if (inputLng) inputLng.value = '';
                    const lblGps = document.getElementById('label-gps-active');
                    if (lblGps) lblGps.classList.add('hidden');
                    const noticeBar = document.getElementById('gpx-geo-notice-bar');
                    if (noticeBar) noticeBar.classList.add('hidden');
                    filterForm?.querySelectorAll('select').forEach(s => s.value = '');
                    window.setMapAndFormRouteType('');
                });
            }

            // Click listener for dynamic pagination links & empty state reset button
            document.addEventListener('click', function(e) {
                // Pagination links
                const paginationLink = e.target.closest('#gpx-catalog-results .gpx-pagination-wrap a, #gpx-catalog-results nav a');
                if (paginationLink && paginationLink.href) {
                    e.preventDefault();
                    fetchGpxResults(paginationLink.href, true);
                    window.scrollTo({ top: filterForm ? filterForm.offsetTop - 80 : 0, behavior: 'smooth' });
                    return;
                }

                // Reset button inside empty state
                if (e.target.closest('#btn-reset-empty-filters')) {
                    e.preventDefault();
                    if (filterForm) filterForm.reset();
                    if (searchInput) searchInput.value = '';
                    if (selectCity) selectCity.value = '';
                    if (btnClearFilterCity) btnClearFilterCity.classList.add('hidden');
                    if (filterCityDropdown) filterCityDropdown.classList.add('hidden');
                    const inputLat = document.getElementById('filter-user-lat');
                    const inputLng = document.getElementById('filter-user-lng');
                    if (inputLat) inputLat.value = '';
                    if (inputLng) inputLng.value = '';
                    const lblGps = document.getElementById('label-gps-active');
                    if (lblGps) lblGps.classList.add('hidden');
                    const noticeBar = document.getElementById('gpx-geo-notice-bar');
                    if (noticeBar) noticeBar.classList.add('hidden');
                    filterForm?.querySelectorAll('select').forEach(s => s.value = '');
                    applyFiltersAjax();
                }
            });

            // Browser back/forward navigation support
            window.addEventListener('popstate', function(e) {
                const currentUrl = window.location.href;
                fetchGpxResults(currentUrl, false);
                
                // Sync form inputs with current URL search params
                const params = new URLSearchParams(window.location.search);
                if (searchInput) searchInput.value = params.get('q') || '';
                if (selectCity) {
                    selectCity.value = params.get('city') || '';
                    if (btnClearFilterCity) {
                        if (selectCity.value) btnClearFilterCity.classList.remove('hidden');
                        else btnClearFilterCity.classList.add('hidden');
                    }
                }
                const radiusSel = document.getElementById('select-filter-radius');
                if (radiusSel) radiusSel.value = params.get('radius') || '';
                const distanceSel = filterForm?.querySelector('select[name="distance"]');
                if (distanceSel) distanceSel.value = params.get('distance') || '';
                const elevationSel = filterForm?.querySelector('select[name="elevation"]');
                if (elevationSel) elevationSel.value = params.get('elevation') || '';
                const sortSel = document.getElementById('select-filter-sort') || filterForm?.querySelector('select[name="sort"]');
                if (sortSel) sortSel.value = params.get('sort') || 'default';
                const inputLat = document.getElementById('filter-user-lat');
                const inputLng = document.getElementById('filter-user-lng');
                if (inputLat) inputLat.value = params.get('user_lat') || params.get('lat') || '';
                if (inputLng) inputLng.value = params.get('user_lng') || params.get('lng') || '';
            });

            // Geolocation & GPS Radius helpers
            const detectCityBtn = document.getElementById('btn-detect-user-city');
            const applyCityBtn = document.getElementById('btn-apply-detected-city');
            const clearGpsBtn = document.getElementById('btn-clear-gps-filter');
            const geoNoticeBar = document.getElementById('gpx-geo-notice-bar');
            const detectedCityNameEl = document.getElementById('gpx-detected-city-name');
            const inputUserLat = document.getElementById('filter-user-lat');
            const inputUserLng = document.getElementById('filter-user-lng');
            const selectRadius = document.getElementById('select-filter-radius');
            const selectSort = document.getElementById('select-filter-sort');
            const labelGpsActive = document.getElementById('label-gps-active');

            async function reverseGeocodeUserLoc(lat, lon) {
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.address) {
                            const a = data.address;
                            const city = a.city || a.town || a.city_district || a.county || a.municipality || a.state_district || a.state;
                            if (city) {
                                return city.replace(/^(Kota|Kabupaten)\s+/i, '').trim();
                            }
                        }
                    }
                } catch(e) {}
                return null;
            }

            function triggerGeoLocation(forceFilter = true) {
                if (!navigator.geolocation) {
                    alert('Geolocation tidak didukung oleh browser Anda.');
                    return;
                }

                if (detectCityBtn) {
                    detectCityBtn.classList.add('animate-pulse', 'text-white');
                }

                navigator.geolocation.getCurrentPosition(
                    async function(pos) {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;

                        if (inputUserLat) inputUserLat.value = lat;
                        if (inputUserLng) inputUserLng.value = lng;

                        if (labelGpsActive) labelGpsActive.classList.remove('hidden');
                        if (geoNoticeBar) geoNoticeBar.classList.remove('hidden');

                        if (detectedCityNameEl) {
                            detectedCityNameEl.textContent = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        }

                        // Try to get city name for better user display
                        reverseGeocodeUserLoc(lat, lng).then(city => {
                            if (city && detectedCityNameEl) {
                                detectedCityNameEl.textContent = `${city} (${lat.toFixed(3)}, ${lng.toFixed(3)})`;
                            }
                        });

                        // Set sort to nearest
                        if (selectSort) {
                            selectSort.value = 'nearest';
                        }

                        if (detectCityBtn) {
                            detectCityBtn.classList.remove('animate-pulse', 'text-white');
                        }

                        if (forceFilter) {
                            applyFiltersAjax();
                        }
                    },
                    function(err) {
                        console.warn('Geolocation Error:', err);
                        if (detectCityBtn) {
                            detectCityBtn.classList.remove('animate-pulse', 'text-white');
                        }
                        if (forceFilter && err.code === err.PERMISSION_DENIED) {
                            alert('Izin lokasi GPS belum diaktifkan di browser.');
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }

            if (detectCityBtn) {
                detectCityBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    triggerGeoLocation(true);
                });
            }

            if (applyCityBtn) {
                applyCityBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!inputUserLat?.value || !inputUserLng?.value) {
                        triggerGeoLocation(true);
                    } else {
                        if (selectSort) selectSort.value = 'nearest';
                        applyFiltersAjax();
                    }
                });
            }

            if (clearGpsBtn) {
                clearGpsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (inputUserLat) inputUserLat.value = '';
                    if (inputUserLng) inputUserLng.value = '';
                    if (selectRadius) selectRadius.value = '';
                    if (labelGpsActive) labelGpsActive.classList.add('hidden');
                    if (geoNoticeBar) geoNoticeBar.classList.add('hidden');
                    if (selectSort && (selectSort.value === 'nearest' || selectSort.value === 'default')) {
                        selectSort.value = 'default';
                    }
                    applyFiltersAjax();
                });
            }

            // Auto-detect geolocation if not set in query param and permission already granted
            if (navigator.permissions && navigator.geolocation && !inputUserLat?.value && !inputUserLng?.value) {
                navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
                    if (result.state === 'granted') {
                        triggerGeoLocation(false);
                    }
                }).catch(function() {});
            }

            // Modal handlers (warna disesuaikan ke accent)
            const modal = document.getElementById('modal-submit-gpx');
            const triggerBtns = document.querySelectorAll('.btn-trigger-gpx-modal');
            const closeBtn = document.getElementById('btn-close-submit-gpx-modal');
            const fileInput = document.getElementById('input-modal-gpx-file');
            const previewContainer = document.getElementById('modal-gpx-preview-container');
            const form = document.getElementById('form-submit-gpx-modal');
            const alertBox = document.getElementById('gpx-modal-alert');
            let previewMap = null;
            let previewPolyline = null;

            function showAlert(msg, isSuccess = false) {
                if (!alertBox) return;
                alertBox.className = isSuccess 
                    ? 'p-3.5 rounded-xl text-sm bg-emerald-500/10 border border-emerald-500/30 text-emerald-300'
                    : 'p-3.5 rounded-xl text-sm bg-rose-500/10 border border-rose-500/30 text-rose-300';
                alertBox.innerHTML = msg;
                alertBox.classList.remove('hidden');
            }

            function hideAlert() {
                if (alertBox) alertBox.classList.add('hidden');
            }

            function openGpxModal() {
                if (!modal) return;
                if (!isUserLoggedIn) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('open_gpx_modal', '1');
                    const returnUrl = currentUrl.toString();

                    if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal(returnUrl);
                        return;
                    }
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(returnUrl);
                    return;
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeGpxModal() {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            triggerBtns.forEach(btn => {
                btn.addEventListener('click', openGpxModal);
            });

            if (closeBtn) closeBtn.addEventListener('click', closeGpxModal);

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeGpxModal();
                });
            }

            const currentUrlParams = new URLSearchParams(window.location.search);
            if (currentUrlParams.get('open_gpx_modal') === '1') {
                openGpxModal();
                try {
                    const cleanUrl = new URL(window.location.href);
                    cleanUrl.searchParams.delete('open_gpx_modal');
                    window.history.replaceState({}, document.title, cleanUrl.toString());
                } catch(e) {}
            }

            function haversineDistance(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            // ==========================================
            // INDONESIAN CITIES AUTOCOMPLETE ENGINE
            // ==========================================
            const INDONESIA_CITIES_MASTER = [
                { name: 'Jakarta Pusat', province: 'DKI Jakarta' },
                { name: 'Jakarta Selatan', province: 'DKI Jakarta' },
                { name: 'Jakarta Barat', province: 'DKI Jakarta' },
                { name: 'Jakarta Timur', province: 'DKI Jakarta' },
                { name: 'Jakarta Utara', province: 'DKI Jakarta' },
                { name: 'Kepulauan Seribu', province: 'DKI Jakarta' },
                { name: 'Bandung', province: 'Jawa Barat' },
                { name: 'Bandung Barat', province: 'Jawa Barat' },
                { name: 'Bekasi', province: 'Jawa Barat' },
                { name: 'Bogor', province: 'Jawa Barat' },
                { name: 'Cimahi', province: 'Jawa Barat' },
                { name: 'Cirebon', province: 'Jawa Barat' },
                { name: 'Depok', province: 'Jawa Barat' },
                { name: 'Sukabumi', province: 'Jawa Barat' },
                { name: 'Tasikmalaya', province: 'Jawa Barat' },
                { name: 'Garut', province: 'Jawa Barat' },
                { name: 'Karawang', province: 'Jawa Barat' },
                { name: 'Kuningan', province: 'Jawa Barat' },
                { name: 'Majalengka', province: 'Jawa Barat' },
                { name: 'Pangandaran', province: 'Jawa Barat' },
                { name: 'Purwakarta', province: 'Jawa Barat' },
                { name: 'Subang', province: 'Jawa Barat' },
                { name: 'Sumedang', province: 'Jawa Barat' },
                { name: 'Cianjur', province: 'Jawa Barat' },
                { name: 'Ciamis', province: 'Jawa Barat' },
                { name: 'Indramayu', province: 'Jawa Barat' },
                { name: 'Tangerang', province: 'Banten' },
                { name: 'Tangerang Selatan', province: 'Banten' },
                { name: 'Serang', province: 'Banten' },
                { name: 'Cilegon', province: 'Banten' },
                { name: 'Lebak', province: 'Banten' },
                { name: 'Pandeglang', province: 'Banten' },
                { name: 'Semarang', province: 'Jawa Tengah' },
                { name: 'Surakarta (Solo)', province: 'Jawa Tengah' },
                { name: 'Magelang', province: 'Jawa Tengah' },
                { name: 'Pekalongan', province: 'Jawa Tengah' },
                { name: 'Salatiga', province: 'Jawa Tengah' },
                { name: 'Tegal', province: 'Jawa Tengah' },
                { name: 'Banyumas (Purwokerto)', province: 'Jawa Tengah' },
                { name: 'Boyolali', province: 'Jawa Tengah' },
                { name: 'Cilacap', province: 'Jawa Tengah' },
                { name: 'Kudus', province: 'Jawa Tengah' },
                { name: 'Klaten', province: 'Jawa Tengah' },
                { name: 'Kendal', province: 'Jawa Tengah' },
                { name: 'Karanganyar', province: 'Jawa Tengah' },
                { name: 'Kebumen', province: 'Jawa Tengah' },
                { name: 'Pati', province: 'Jawa Tengah' },
                { name: 'Purworejo', province: 'Jawa Tengah' },
                { name: 'Sukoharjo', province: 'Jawa Tengah' },
                { name: 'Wonogiri', province: 'Jawa Tengah' },
                { name: 'Wonosobo', province: 'Jawa Tengah' },
                { name: 'Yogyakarta', province: 'DI Yogyakarta' },
                { name: 'Sleman', province: 'DI Yogyakarta' },
                { name: 'Bantul', province: 'DI Yogyakarta' },
                { name: 'Gunungkidul', province: 'DI Yogyakarta' },
                { name: 'Kulon Progo', province: 'DI Yogyakarta' },
                { name: 'Surabaya', province: 'Jawa Timur' },
                { name: 'Malang', province: 'Jawa Timur' },
                { name: 'Batu', province: 'Jawa Timur' },
                { name: 'Sidoarjo', province: 'Jawa Timur' },
                { name: 'Gresik', province: 'Jawa Timur' },
                { name: 'Kediri', province: 'Jawa Timur' },
                { name: 'Blitar', province: 'Jawa Timur' },
                { name: 'Madiun', province: 'Jawa Timur' },
                { name: 'Mojokerto', province: 'Jawa Timur' },
                { name: 'Pasuruan', province: 'Jawa Timur' },
                { name: 'Probolinggo', province: 'Jawa Timur' },
                { name: 'Banyuwangi', province: 'Jawa Timur' },
                { name: 'Jember', province: 'Jawa Timur' },
                { name: 'Lamongan', province: 'Jawa Timur' },
                { name: 'Lumajang', province: 'Jawa Timur' },
                { name: 'Tuban', province: 'Jawa Timur' },
                { name: 'Tulungagung', province: 'Jawa Timur' },
                { name: 'Denpasar', province: 'Bali' },
                { name: 'Badung (Kuta / Canggu)', province: 'Bali' },
                { name: 'Gianyar (Ubud)', province: 'Bali' },
                { name: 'Tabanan', province: 'Bali' },
                { name: 'Buleleng (Singaraja)', province: 'Bali' },
                { name: 'Karangasem', province: 'Bali' },
                { name: 'Klungkung', province: 'Bali' },
                { name: 'Bangli', province: 'Bali' },
                { name: 'Jembrana', province: 'Bali' },
                { name: 'Mataram', province: 'Nusa Tenggara Barat' },
                { name: 'Lombok Barat', province: 'Nusa Tenggara Barat' },
                { name: 'Lombok Tengah', province: 'Nusa Tenggara Barat' },
                { name: 'Lombok Timur', province: 'Nusa Tenggara Barat' },
                { name: 'Lombok Utara', province: 'Nusa Tenggara Barat' },
                { name: 'Sumbawa', province: 'Nusa Tenggara Barat' },
                { name: 'Bima', province: 'Nusa Tenggara Barat' },
                { name: 'Kupang', province: 'Nusa Tenggara Timur' },
                { name: 'Manggarai Barat (Labuan Bajo)', province: 'Nusa Tenggara Timur' },
                { name: 'Medan', province: 'Sumatera Utara' },
                { name: 'Binjai', province: 'Sumatera Utara' },
                { name: 'Pematangsiantar', province: 'Sumatera Utara' },
                { name: 'Deli Serdang', province: 'Sumatera Utara' },
                { name: 'Karo (Berastagi)', province: 'Sumatera Utara' },
                { name: 'Samosir (Danau Toba)', province: 'Sumatera Utara' },
                { name: 'Padang', province: 'Sumatera Barat' },
                { name: 'Bukittinggi', province: 'Sumatera Barat' },
                { name: 'Payakumbuh', province: 'Sumatera Barat' },
                { name: 'Pekanbaru', province: 'Riau' },
                { name: 'Dumai', province: 'Riau' },
                { name: 'Batam', province: 'Kepulauan Riau' },
                { name: 'Tanjungpinang', province: 'Kepulauan Riau' },
                { name: 'Bintan', province: 'Kepulauan Riau' },
                { name: 'Palembang', province: 'Sumatera Selatan' },
                { name: 'Prabumulih', province: 'Sumatera Selatan' },
                { name: 'Lubuklinggau', province: 'Sumatera Selatan' },
                { name: 'Bengkulu', province: 'Bengkulu' },
                { name: 'Jambi', province: 'Jambi' },
                { name: 'Bandar Lampung', province: 'Lampung' },
                { name: 'Metro', province: 'Lampung' },
                { name: 'Pangkalpinang', province: 'Bangka Belitung' },
                { name: 'Belitung', province: 'Bangka Belitung' },
                { name: 'Banda Aceh', province: 'Aceh' },
                { name: 'Sabang', province: 'Aceh' },
                { name: 'Lhokseumawe', province: 'Aceh' },
                { name: 'Pontianak', province: 'Kalimantan Barat' },
                { name: 'Singkawang', province: 'Kalimantan Barat' },
                { name: 'Banjarmasin', province: 'Kalimantan Selatan' },
                { name: 'Banjarbaru', province: 'Kalimantan Selatan' },
                { name: 'Palangkaraya', province: 'Kalimantan Tengah' },
                { name: 'Balikpapan', province: 'Kalimantan Timur' },
                { name: 'Samarinda', province: 'Kalimantan Timur' },
                { name: 'Bontang', province: 'Kalimantan Timur' },
                { name: 'Nusantara (IKN)', province: 'Kalimantan Timur' },
                { name: 'Tarakan', province: 'Kalimantan Utara' },
                { name: 'Makassar', province: 'Sulawesi Selatan' },
                { name: 'Parepare', province: 'Sulawesi Selatan' },
                { name: 'Palopo', province: 'Sulawesi Selatan' },
                { name: 'Gowa', province: 'Sulawesi Selatan' },
                { name: 'Maros', province: 'Sulawesi Selatan' },
                { name: 'Toraja', province: 'Sulawesi Selatan' },
                { name: 'Manado', province: 'Sulawesi Utara' },
                { name: 'Tomohon', province: 'Sulawesi Utara' },
                { name: 'Bitung', province: 'Sulawesi Utara' },
                { name: 'Palu', province: 'Sulawesi Tengah' },
                { name: 'Kendari', province: 'Sulawesi Tenggara' },
                { name: 'Baubau', province: 'Sulawesi Tenggara' },
                { name: 'Gorontalo', province: 'Gorontalo' },
                { name: 'Mamuju', province: 'Sulawesi Barat' },
                { name: 'Ambon', province: 'Maluku' },
                { name: 'Ternate', province: 'Maluku Utara' },
                { name: 'Jayapura', province: 'Papua' },
                { name: 'Sorong', province: 'Papua Barat Daya' },
                { name: 'Manokwari', province: 'Papua Barat' },
                { name: 'Merauke', province: 'Papua Selatan' },
                { name: 'Mimika (Timika)', province: 'Papua Tengah' }
            ];

            const modalCityInput = document.getElementById('input-modal-city');
            const cityAutoList = document.getElementById('modal-city-autocomplete-list');
            let cityDebounceTimer = null;
            let currentFocusIdx = -1;

            function renderCityAutocomplete(suggestions, query) {
                if (!cityAutoList) return;
                if (!suggestions || suggestions.length === 0) {
                    cityAutoList.innerHTML = `
                        <div class="px-3.5 py-2.5 text-xs text-slate-200 text-center">
                            Tidak ada kota yang cocok. Tetap gunakan "<strong>${escapeHtml(query)}</strong>"
                        </div>
                    `;
                    cityAutoList.classList.remove('hidden');
                    return;
                }

                currentFocusIdx = -1;
                cityAutoList.innerHTML = suggestions.map((item, idx) => {
                    const isGlobal = item.country && item.country !== 'Indonesia';
                    const provLabel = isGlobal 
                        ? `<span class="text-[10px] text-slate-300 font-normal block">${escapeHtml(item.province ? item.province + ', ' : '')}${escapeHtml(item.country)}</span>` 
                        : (item.province ? `<span class="text-[10px] text-slate-300 font-normal block">${escapeHtml(item.province)}</span>` : '');
                    const iconClass = isGlobal ? 'fa-globe text-cyan-400' : 'fa-location-dot text-accent';

                    return `
                        <div class="city-auto-item px-3.5 py-2.5 bg-[#0b1220] hover:bg-[#1e293b] cursor-pointer flex items-center justify-between text-xs text-slate-200 transition-colors" data-index="${idx}" data-name="${escapeHtml(item.name)}" style="background-color: #0b1220;">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i class="fa-solid ${iconClass} text-[11px] shrink-0"></i>
                                <div class="truncate">
                                    <strong class="text-white font-semibold">${escapeHtml(item.name)}</strong>
                                    ${provLabel}
                                </div>
                            </div>
                            <span class="text-[10px] text-accent font-bold shrink-0">Pilih &rarr;</span>
                        </div>
                    `;
                }).join('');

                cityAutoList.querySelectorAll('.city-auto-item').forEach(el => {
                    el.addEventListener('click', function() {
                        const cityName = this.getAttribute('data-name');
                        if (modalCityInput) {
                            modalCityInput.value = cityName;
                            modalCityInput.focus();
                        }
                        closeCityAutocomplete();
                    });
                });

                cityAutoList.classList.remove('hidden');
            }

            function closeCityAutocomplete() {
                if (cityAutoList) {
                    cityAutoList.classList.add('hidden');
                    cityAutoList.innerHTML = '';
                    currentFocusIdx = -1;
                }
            }

            function escapeHtml(str) {
                return String(str || '').replace(/[&<>"']/g, function(m) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
                });
            }

            if (modalCityInput) {
                modalCityInput.addEventListener('input', function(e) {
                    const q = this.value.trim().toLowerCase();
                    clearTimeout(cityDebounceTimer);

                    if (q.length === 0) {
                        closeCityAutocomplete();
                        return;
                    }

                    // 1. Instant local filter from master list
                    const localMatches = INDONESIA_CITIES_MASTER.filter(c => 
                        c.name.toLowerCase().includes(q) || (c.province && c.province.toLowerCase().includes(q))
                    ).slice(0, 8);

                    renderCityAutocomplete(localMatches, q);

                    // 2. Fetch from backend API endpoint (searches Indonesian DB + Photon Global API)
                    cityDebounceTimer = setTimeout(() => {
                        fetch('{{ route("gpx.cities.autocomplete") }}?q=' + encodeURIComponent(q))
                            .then(res => res.json())
                            .then(apiData => {
                                if (Array.isArray(apiData) && apiData.length > 0) {
                                    // Merge & deduplicate
                                    const merged = [...localMatches];
                                    apiData.forEach(item => {
                                        if (!merged.some(m => m.name.toLowerCase() === item.name.toLowerCase())) {
                                            merged.push(item);
                                        }
                                    });
                                    renderCityAutocomplete(merged.slice(0, 15), q);
                                }
                            })
                            .catch(err => {});
                    }, 200);
                });

                modalCityInput.addEventListener('keydown', function(e) {
                    if (!cityAutoList || cityAutoList.classList.contains('hidden')) return;
                    const items = cityAutoList.querySelectorAll('.city-auto-item');
                    if (items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        currentFocusIdx = (currentFocusIdx + 1) % items.length;
                        items.forEach((it, i) => it.classList.toggle('bg-slate-800', i === currentFocusIdx));
                        items[currentFocusIdx]?.scrollIntoView({ block: 'nearest' });
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        currentFocusIdx = (currentFocusIdx - 1 + items.length) % items.length;
                        items.forEach((it, i) => it.classList.toggle('bg-slate-800', i === currentFocusIdx));
                        items[currentFocusIdx]?.scrollIntoView({ block: 'nearest' });
                    } else if (e.key === 'Enter') {
                        if (currentFocusIdx >= 0 && items[currentFocusIdx]) {
                            e.preventDefault();
                            items[currentFocusIdx].click();
                        }
                    } else if (e.key === 'Escape') {
                        closeCityAutocomplete();
                    }
                });
            }

            document.addEventListener('click', function(e) {
                const wrapper = document.getElementById('modal-city-autocomplete-wrapper');
                if (wrapper && !wrapper.contains(e.target)) {
                    closeCityAutocomplete();
                }
            });

            async function reverseGeocodeModalCity(lat, lon) {
                const loader = document.getElementById('modal-city-loading');
                if (loader) loader.classList.remove('hidden');
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.address) {
                            const a = data.address;
                            const city = a.city || a.town || a.city_district || a.county || a.municipality || a.state_district || a.state;
                            const country = a.country || '';
                            const countryCode = (a.country_code || '').toLowerCase();
                            if (city) {
                                const cleanCity = city.replace(/^(Kota|Kabupaten)\s+/i, '').trim();
                                const finalCityName = (country && countryCode !== 'id') ? `${cleanCity}, ${country}` : cleanCity;
                                if (modalCityInput) modalCityInput.value = finalCityName;
                            }
                        }
                    }
                } catch (e) {} finally {
                    if (loader) loader.classList.add('hidden');
                }
            }

            const detectModalGpsBtn = document.getElementById('btn-modal-detect-location');
            if (detectModalGpsBtn) {
                detectModalGpsBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        showAlert('Browser tidak mendukung Geolocation.');
                        return;
                    }
                    const loader = document.getElementById('modal-city-loading');
                    if (loader) loader.classList.remove('hidden');
                    navigator.geolocation.getCurrentPosition(
                        function(pos) {
                            reverseGeocodeModalCity(pos.coords.latitude, pos.coords.longitude);
                        },
                        function(err) {
                            if (loader) loader.classList.add('hidden');
                            showAlert('Gagal mengakses GPS: ' + err.message);
                        },
                        { timeout: 10000 }
                    );
                });
            }

            // ==========================================
            // INTERACTIVE GPX DROPZONE ENGINE
            // ==========================================
            const dropzone = document.getElementById('modal-gpx-dropzone');
            const emptyState = document.getElementById('dropzone-empty-state');
            const fileState = document.getElementById('dropzone-file-state');
            const dropFilename = document.getElementById('dropzone-filename');
            const dropFilesize = document.getElementById('dropzone-filesize');
            const btnRemoveFile = document.getElementById('btn-dropzone-remove');

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
                // Click to open file picker
                dropzone.addEventListener('click', function(e) {
                    if (e.target.closest('#btn-dropzone-remove')) {
                        e.stopPropagation();
                        resetDropzone();
                        return;
                    }
                    fileInput.click();
                });

                // Drag & Drop handlers
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.add('border-accent', 'bg-[#ccff00]/5', 'scale-[1.01]');
                        dropzone.classList.remove('border-slate-700');
                    }, false);
                });

                ['dragleave', 'dragend'].forEach(eventName => {
                    dropzone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.remove('border-accent', 'bg-[#ccff00]/5', 'scale-[1.01]');
                        dropzone.classList.add('border-slate-700');
                    }, false);
                });

                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-accent', 'bg-[#ccff00]/5', 'scale-[1.01]');
                    dropzone.classList.add('border-slate-700');

                    const dt = e.dataTransfer;
                    if (dt && dt.files && dt.files.length > 0) {
                        const droppedFile = dt.files[0];
                        if (!droppedFile.name.toLowerCase().endsWith('.gpx') && !droppedFile.type.includes('xml')) {
                            showAlert('Harap pilih file dengan format .gpx');
                            return;
                        }
                        
                        // Assign to hidden file input
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

                    // 1. Trigger dan update judul rute dari file GPX (name tag atau nama file)
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
                        let lat = pt.getAttribute('lat') || pt.getAttribute('latitude') || pt.getAttribute('LAT');
                        let lon = pt.getAttribute('lon') || pt.getAttribute('lng') || pt.getAttribute('longitude') || pt.getAttribute('LON');

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
                        showAlert('File GPX tidak valid atau tidak memiliki titik koordinat yang dapat diproses.');
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
                            
                            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                maxZoom: 19,
                                subdomains: 'abcd'
                            }).addTo(previewMap);
                        } else {
                            previewMap.setView([coords[0][0], coords[0][1]], 13);
                        }

                        if (previewPolyline) {
                            previewMap.removeLayer(previewPolyline);
                        }

                        previewPolyline = L.polyline(coords, {
                            color: '#FC4C02',
                            weight: 4,
                            opacity: 0.95,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(previewMap);

                        const bounds = previewPolyline.getBounds();
                        if (bounds.isValid()) {
                            previewMap.fitBounds(bounds, { padding: [15, 15] });
                        }
                    } catch(mapErr) {}

                    // 2. Trigger otomatis deteksi lokasi awal rute (reverse geocoding titik start)
                    if (coords.length > 0 && modalCityInput) {
                        reverseGeocodeModalCity(coords[0][0], coords[0][1]);
                    }

                    // 3. Auto-detect route type based on gradient density / filename
                    const gainPerKm = (totalDist > 0) ? (gainSum / (totalDist / 1000)) : 0;
                    const isTrailCandidate = (gainPerKm >= 35.0) || (file.name.toLowerCase().includes('trail'));
                    const targetType = isTrailCandidate ? 'trail' : 'road';
                    document.querySelectorAll('input[name="route_type"]').forEach(r => {
                        r.checked = (r.value === targetType);
                    });
                    syncRouteTypeButtons();

                    setTimeout(() => {
                        if (previewMap) previewMap.invalidateSize();
                    }, 250);
                };
                reader.readAsText(file);
            }

            // ==========================================
            // ROUTE TYPE SELECTOR (NEON LIME SYNC)
            // ==========================================
            function syncRouteTypeButtons() {
                document.querySelectorAll('input[name="route_type"]').forEach(r => {
                    const label = r.closest('.route-type-btn');
                    if (label) {
                        if (r.checked) {
                            label.classList.add('is-active');
                        } else {
                            label.classList.remove('is-active');
                        }
                    }
                });
            }

            document.querySelectorAll('input[name="route_type"]').forEach(radio => {
                radio.addEventListener('change', syncRouteTypeButtons);
            });

            document.querySelectorAll('.route-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.querySelector('input[name="route_type"]');
                    if (input) {
                        input.checked = true;
                        syncRouteTypeButtons();
                    }
                });
            });

            syncRouteTypeButtons();

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    hideAlert();

                    const submitBtn = document.getElementById('btn-submit-gpx-form');
                    const originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin text-xs"></i><span>Mengirim...</span>';

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
                        submitBtn.innerHTML = originalBtnHtml;

                        if (resObj.status === 200 && resObj.body.success) {
                            showAlert(resObj.body.message, true);
                            form.reset();
                            resetDropzone();
                            syncRouteTypeButtons();
                            setTimeout(() => {
                                closeGpxModal();
                                window.location.reload();
                            }, 2000);
                        } else {
                            showAlert(resObj.body.message || 'Gagal mengirim rute GPX. Periksa input Anda.');
                        }
                    })
                    .catch(err => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                        showAlert('Terjadi kesalahan koneksi saat mengirim rute GPX.');
                    });
                });
            }
        });
    </script>
@endpush