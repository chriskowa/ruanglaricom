@extends('layouts.pacerhub')

@php
    $formattedDist = number_format((float)($item->distance_km ?? 0), 2);
    $cityName = $item->city ?? 'Indonesia';
    $pageTitle = e($item->title) . ' (' . $formattedDist . ' km, ' . e($cityName) . ') - Download File GPX & Peta Rute Lari | RuangLari';
    $pageDesc = 'Unduh gratis file GPX rute lari ' . e($item->title) . ' di ' . e($cityName) . '. Jarak ' . $formattedDist . ' km, elevasi naik +' . round($item->elevation_gain_m ?? 0) . 'm. Kompatibel untuk Garmin, Coros, Suunto, dan Strava.';
    $coordsJson = json_encode($item->coordinates_json ?? []);
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)
@section('meta_keywords', 'rute gpx ' . strtolower($item->title) . ', download gpx ' . strtolower($cityName) . ', gpx rute lari ' . strtolower($cityName) . ', gpx garmin, gpx coros, strava route')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        #gpx-detail-map {
            height: 480px;
            width: 100%;
            background: #0c121e;
            border-radius: 1.25rem;
            overflow: hidden;
            z-index: 1;
        }
        .leaflet-tile {
            filter: brightness(0.85) contrast(1.15) saturate(0.8);
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
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Breadcrumb & Top Action Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/80 pb-4 text-xs font-mono">
            <nav class="flex items-center gap-2 text-slate-400">
                <a href="{{ route('gpx.index') }}" class="hover:text-white transition group flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[11px] group-hover:-translate-x-0.5 transition-transform"></i>
                    <span class="uppercase tracking-wider">Database GPX</span>
                </a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold truncate max-w-[200px] sm:max-w-md">{{ $item->title }}</span>
            </nav>

            <button type="button" onclick="shareGpxRoute()" class="px-3.5 py-1.5 rounded-xl bg-slate-850 hover:bg-slate-750 border border-slate-700 text-slate-300 hover:text-white text-xs font-mono font-bold uppercase tracking-wider transition flex items-center gap-2 shadow cursor-pointer">
                <i class="fa-solid fa-share-nodes text-neon text-xs"></i>
                <span>Bagikan Rute</span>
            </button>
        </div>

        <!-- Main Header Hero Box -->
        <div class="bg-[#0c121e] border border-slate-800/90 rounded-3xl p-6 md:p-10 relative overflow-hidden shadow-2xl">
            <!-- Background Radial Glow -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-neon/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 relative z-10">
                <div class="space-y-3.5 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-750 text-[11px] font-mono font-bold text-slate-300 uppercase tracking-wider">
                            <i class="fa-solid fa-location-dot text-neon text-[11px]"></i>
                            <span>{{ $cityName }}</span>
                        </span>
                        
                        @if($item->event)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-750 text-[11px] font-mono font-bold text-slate-300 uppercase tracking-wider">
                                <i class="fa-solid fa-trophy text-slate-400 text-[11px]"></i>
                                <span>{{ $item->event->title ?? $item->event->name ?? 'Race Event' }}</span>
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-750 text-[11px] font-mono font-bold text-neon uppercase tracking-wider">
                            <i class="fa-solid fa-circle-check text-[10px]"></i>
                            <span>Terverifikasi</span>
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black italic tracking-tight text-white uppercase font-sans">
                        {{ $item->title }}
                    </h1>

                    <p class="text-slate-400 text-sm leading-relaxed max-w-2xl font-sans">
                        {{ $item->description ?? $item->notes ?? 'File rute GPX lari terverifikasi dan siap diunduh untuk jam Garmin, Coros, Suunto, serta aplikasi Strava.' }}
                    </p>
                </div>

                <!-- Action CTA Buttons -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 shrink-0">
                    <a href="{{ route('gpx.download', $item->id) }}" class="w-full sm:w-auto px-6 py-4 rounded-2xl bg-neon text-dark font-mono font-black text-xs uppercase tracking-wider hover:bg-white transition-all shadow-xl shadow-neon/15 flex items-center justify-center gap-2.5">
                        <i class="fa-solid fa-download text-sm"></i>
                        <span>Download GPX</span>
                    </a>
                    
                    <a href="{{ route('tools.buat-rute-lari') }}" class="w-full sm:w-auto px-5 py-4 rounded-2xl bg-slate-850 hover:bg-slate-750 border border-slate-700 text-white font-mono font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow">
                        <i class="fa-solid fa-draw-polygon text-slate-400"></i>
                        <span>Buka Editor</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Metric Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-[#0c121e] border border-slate-800/90 rounded-2xl p-5 shadow flex flex-col justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-ruler-horizontal text-neon text-xs"></i> Jarak Total
                </span>
                <div class="mt-3">
                    <span class="text-3xl md:text-4xl font-mono font-black text-white tracking-tight">{{ $formattedDist }}</span>
                    <span class="text-xs font-mono font-bold text-slate-400 ml-1">KM</span>
                </div>
            </div>

            <div class="bg-[#0c121e] border border-slate-800/90 rounded-2xl p-5 shadow flex flex-col justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-trend-up text-neon text-xs"></i> Elevasi Naik
                </span>
                <div class="mt-3">
                    <span class="text-3xl md:text-4xl font-mono font-black text-white tracking-tight">{{ number_format((float)($item->elevation_gain_m ?? 0)) }}</span>
                    <span class="text-xs font-mono font-bold text-slate-400 ml-1">Meter</span>
                </div>
            </div>

            <div class="bg-[#0c121e] border border-slate-800/90 rounded-2xl p-5 shadow flex flex-col justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-trend-down text-slate-400 text-xs"></i> Elevasi Turun
                </span>
                <div class="mt-3">
                    <span class="text-3xl md:text-4xl font-mono font-black text-white tracking-tight">{{ number_format((float)($item->elevation_loss_m ?? 0)) }}</span>
                    <span class="text-xs font-mono font-bold text-slate-400 ml-1">Meter</span>
                </div>
            </div>

            <div class="bg-[#0c121e] border border-slate-800/90 rounded-2xl p-5 shadow flex flex-col justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-user text-slate-400 text-xs"></i> Kontributor
                </span>
                <div class="mt-3">
                    <span class="text-sm font-mono font-bold text-white block truncate">{{ $item->user?->name ?? 'RuangLari' }}</span>
                    <span class="text-[11px] font-mono text-slate-500 block mt-0.5">{{ $item->created_at ? $item->created_at->format('d M Y') : 'Terverifikasi' }}</span>
                </div>
            </div>
        </div>

        <!-- Interactive Route Map Stage -->
        <div class="bg-[#0c121e] border border-slate-800/90 rounded-3xl p-5 md:p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-neon text-sm"></i>
                    <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-white">Peta & Trase Lintasan GPS</h2>
                </div>
                <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
                    <span class="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-[10px]">
                        <span class="inline-block w-2 h-2 rounded-full bg-neon mr-1"></span> Start
                    </span>
                    <span class="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-[10px]">
                        <span class="inline-block w-2 h-2 rounded-full bg-white mr-1"></span> Finish
                    </span>
                </div>
            </div>

            <div id="gpx-detail-map" class="relative shadow-inner"></div>
        </div>

        <!-- Route Notes & Details -->
        @if($item->description || $item->notes)
            <div class="bg-[#0c121e] border border-slate-800/90 rounded-3xl p-6 md:p-8 space-y-4 shadow-xl">
                <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-neon text-xs"></i>
                    <span>Informasi & Catatan Rute</span>
                </h3>
                
                <div class="text-slate-300 text-sm leading-relaxed space-y-3 font-sans">
                    @if($item->description)
                        <p class="whitespace-pre-line">{{ $item->description }}</p>
                    @endif

                    @if($item->notes && $item->notes !== $item->description)
                        <div class="p-4 rounded-xl bg-[#090D16] border border-slate-800 text-xs text-slate-300 font-sans">
                            <strong class="text-white block font-mono uppercase tracking-wider mb-1">Catatan Tambahan:</strong>
                            <p class="whitespace-pre-line">{{ $item->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Smartwatch Sync Guide (Garmin, Coros, Strava) -->
        <section class="bg-[#0c121e] border border-slate-800/90 rounded-3xl p-6 md:p-8 space-y-6 shadow-xl"
                 x-data="{ activeTab: 'garmin' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-neon">Panduan Sinkronisasi</span>
                    <h3 class="text-base font-black text-white uppercase font-sans mt-0.5">CARA MEMASUKKAN GPX KE JAM TANGAN</h3>
                </div>

                <!-- Tab Switchers -->
                <div class="flex items-center gap-2 bg-[#090D16] p-1 rounded-xl border border-slate-800">
                    <button type="button" @click="activeTab = 'garmin'"
                            class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold uppercase tracking-wider transition cursor-pointer"
                            :class="activeTab === 'garmin' ? 'bg-slate-800 text-white shadow' : 'text-slate-400 hover:text-white'">
                        Garmin
                    </button>
                    <button type="button" @click="activeTab = 'coros'"
                            class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold uppercase tracking-wider transition cursor-pointer"
                            :class="activeTab === 'coros' ? 'bg-slate-800 text-white shadow' : 'text-slate-400 hover:text-white'">
                        Coros
                    </button>
                    <button type="button" @click="activeTab = 'strava'"
                            class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold uppercase tracking-wider transition cursor-pointer"
                            :class="activeTab === 'strava' ? 'bg-slate-800 text-white shadow' : 'text-slate-400 hover:text-white'">
                        Strava
                    </button>
                </div>
            </div>

            <!-- Garmin Guide -->
            <div x-show="activeTab === 'garmin'" class="space-y-3 font-sans text-xs text-slate-300 leading-relaxed">
                <ol class="space-y-2 list-decimal list-inside text-slate-400">
                    <li><strong class="text-white">Download file GPX</strong> rute ini dengan menekan tombol <em>Download GPX</em> di atas.</li>
                    <li>Buka aplikasi <strong class="text-white">Garmin Connect</strong> di smartphone Anda.</li>
                    <li>Pilih menu <strong>More (&hellip;) &rarr; Training & Planning &rarr; Courses</strong>.</li>
                    <li>Klik opsi <strong>Import Course / Import Course File</strong> dan pilih file GPX yang baru saja Anda download.</li>
                    <li>Pilih tipe aktivitas (contoh: <em>Running / Trail Running</em>), lalu simpan rute (<em>Save</em>).</li>
                    <li>Klik icon <strong>Send to Device</strong> di pojok kanan atas untuk menyinkronkan rute ke jam Garmin Anda.</li>
                </ol>
            </div>

            <!-- Coros Guide -->
            <div x-show="activeTab === 'coros'" class="space-y-3 font-sans text-xs text-slate-300 leading-relaxed">
                <ol class="space-y-2 list-decimal list-inside text-slate-400">
                    <li><strong class="text-white">Download file GPX</strong> rute ini ke smartphone Anda.</li>
                    <li>Buka file GPX yang terunduh dan pilih <strong>Open with COROS App</strong>.</li>
                    <li>Aplikasi COROS akan otomatis menampilkan preview rute dan peta. Klik <strong>Save to My Routes</strong>.</li>
                    <li>Buka menu <strong>Profile &rarr; Route Library</strong> di aplikasi COROS.</li>
                    <li>Pilih rute tersebut dan klik <strong>Sync with Watch</strong> untuk mengunggahnya langsung ke jam Coros.</li>
                </ol>
            </div>

            <!-- Strava Guide -->
            <div x-show="activeTab === 'strava'" class="space-y-3 font-sans text-xs text-slate-300 leading-relaxed">
                <ol class="space-y-2 list-decimal list-inside text-slate-400">
                    <li>Download file GPX di RuangLari.</li>
                    <li>Buka website <strong>Strava.com</strong> di browser komputer / HP.</li>
                    <li>Buka menu <strong>Dashboard &rarr; My Routes &rarr; Create New Route</strong> &rarr; Pilih <strong>Upload GPX</strong>.</li>
                    <li>Simpan rute di akun Strava Anda. Rute akan otomatis muncul di menu <em>Saved Routes</em> pada aplikasi mobile Strava Anda.</li>
                </ol>
            </div>
        </section>

        <!-- Related GPX Routes in the Same Region -->
        @if(isset($related) && $related->count() > 0)
            <div class="space-y-4 pt-4 border-t border-slate-800/80">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-neon">Eksplorasi Rute Lain</span>
                        <h3 class="text-lg font-black italic tracking-tight text-white uppercase font-sans mt-0.5">RUTE GPX SERUPA</h3>
                    </div>
                    <a href="{{ route('gpx.index') }}" class="text-xs font-mono text-slate-400 hover:text-white transition uppercase tracking-wider">
                        Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($related as $rel)
                        <a href="{{ route('gpx.show', $rel->slug ?: $rel->id) }}" class="group bg-[#0c121e] border border-slate-800/80 hover:border-slate-700 rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between hover:shadow-xl">
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between gap-2 text-[10px] font-mono font-bold text-slate-400 uppercase">
                                    <span class="text-neon"><i class="fa-solid fa-location-dot"></i> {{ $rel->city ?? 'Indonesia' }}</span>
                                    <span class="text-white">{{ number_format((float)($rel->distance_km ?? 0), 1) }} KM</span>
                                </div>

                                <h4 class="text-sm font-bold text-white group-hover:text-neon transition-colors line-clamp-1 font-sans">
                                    {{ $rel->title }}
                                </h4>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs font-mono text-slate-400">
                                <span><i class="fa-solid fa-arrow-trend-up text-slate-500 text-[10px]"></i> +{{ round($rel->elevation_gain_m ?? 0) }}m</span>
                                <span class="text-white font-bold group-hover:text-neon transition-colors">Detail &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const rawRouteCoords = {!! $coordsJson !!};
        
        document.addEventListener('DOMContentLoaded', function() {
            const tileUrl = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

            const map = L.map('gpx-detail-map', {
                zoomControl: true,
                scrollWheelZoom: false,
                dragging: true,
            }).setView([-6.2088, 106.8456], 12);

            L.tileLayer(tileUrl, {
                maxZoom: 19,
                subdomains: 'abcd'
            }).addTo(map);

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

            let validCoords = [];
            if (Array.isArray(rawRouteCoords)) {
                rawRouteCoords.forEach(pt => {
                    const parsed = parseCoordinatePair(pt);
                    if (parsed) validCoords.push(parsed);
                });
            }

            if (validCoords.length > 0) {
                const polyline = L.polyline(validCoords, {
                    color: '#ccff00', // Athletic Electric Neon Polyline
                    weight: 4.5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);

                // Custom Start & Finish Markers
                const startIcon = L.divIcon({
                    className: 'custom-start-marker',
                    html: '<div class="w-6 h-6 rounded-full bg-[#ccff00] border-2 border-black shadow-xl flex items-center justify-center text-[10px] font-mono font-black text-black">S</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const finishIcon = L.divIcon({
                    className: 'custom-finish-marker',
                    html: '<div class="w-6 h-6 rounded-full bg-white border-2 border-black shadow-xl flex items-center justify-center text-[10px] font-mono font-black text-black">F</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                L.marker(validCoords[0], { icon: startIcon }).addTo(map).bindPopup('<b style="color:#000;">Titik Awal (Start)</b>');
                L.marker(validCoords[validCoords.length - 1], { icon: finishIcon }).addTo(map).bindPopup('<b style="color:#000;">Titik Selesai (Finish)</b>');

                const bounds = polyline.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                } else {
                    map.setView(validCoords[0], 13);
                }
            }
        });

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
