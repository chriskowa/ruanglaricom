@extends('layouts.pacerhub')

@php
    $pageTitle = e($item->title) . ' (' . number_format((float)($item->distance_km ?? 0), 1) . ' km, ' . e($item->city ?? 'Indonesia') . ') - Download File GPX & Peta Rute | RuangLari';
    $pageDesc = 'Unduh file GPX rute lari ' . e($item->title) . ' di ' . e($item->city ?? 'Indonesia') . '. Jarak ' . number_format((float)($item->distance_km ?? 0), 2) . ' km, elevasi naik ' . ($item->elevation_gain_m ?? 0) . 'm.';
    $coordsJson = json_encode($item->coordinates_json ?? []);
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDesc)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        #gpx-detail-map {
            height: 480px;
            width: 100%;
            background: #f8fafc;
            border-radius: 1.5rem;
            overflow: hidden;
            z-index: 1;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 bg-[#09090b] text-zinc-100 selection:bg-zinc-800 selection:text-white">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Breadcrumb & Top Bar -->
        <div class="flex items-center justify-between gap-4">
            <nav class="flex items-center gap-2 text-xs text-zinc-400">
                <a href="{{ route('gpx.index') }}" class="hover:text-white transition-colors flex items-center gap-1.5 font-medium">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Direktori GPX</span>
                </a>
                <span class="text-zinc-600">/</span>
                <span class="text-zinc-300 font-semibold truncate max-w-[200px] md:max-w-md">{{ $item->title }}</span>
            </nav>

            <button type="button" onclick="shareGpxRoute()" class="px-3.5 py-1.5 rounded-full bg-zinc-900 border border-zinc-800 text-xs font-bold text-zinc-300 hover:text-white hover:border-zinc-700 transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-share-nodes text-xs text-emerald-400"></i>
                <span>Bagikan Rute</span>
            </button>
        </div>

        <!-- Header Card: Minimalist Clean Aesthetic -->
        <div class="bg-zinc-900/70 border border-zinc-800/80 rounded-3xl p-6 md:p-8 backdrop-blur-md relative overflow-hidden shadow-2xl">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800/90 border border-zinc-700/60 text-xs font-semibold text-emerald-400">
                            <i class="fa-solid fa-location-dot text-[11px]"></i>
                            <span>{{ $item->city ?? 'Indonesia' }}</span>
                        </span>
                        @if($item->event)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-xs font-semibold text-blue-300">
                                <i class="fa-solid fa-trophy text-[11px]"></i>
                                <span>{{ $item->event->title ?? $item->event->name ?? 'Event' }}</span>
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800/90 border border-zinc-700/60 text-xs font-mono text-sky-400" title="Slug URL Otomatis">
                            <i class="fa-solid fa-link text-[10px]"></i>
                            <span>slug: {{ $item->slug }}</span>
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-5xl font-black tracking-tight text-white italic">
                        {{ $item->title }}
                    </h1>

                    <p class="text-zinc-400 text-sm max-w-3xl leading-relaxed">
                        {{ $item->description ?? $item->notes ?? 'Rute lari berkualitas tinggi yang telah diverifikasi dan siap diunduh untuk perangkat Garmin, Coros, Suunto, maupun aplikasi Strava.' }}
                    </p>
                </div>

                <!-- Action CTA Buttons -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 shrink-0">
                    <a href="{{ route('gpx.download', $item->id) }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-500 text-zinc-950 font-black text-xs uppercase tracking-wider hover:bg-white transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-download text-sm"></i>
                        <span>Download GPX</span>
                    </a>
                    <a href="{{ route('tools.buat-rute-lari') }}" class="w-full sm:w-auto px-5 py-3.5 rounded-2xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-route text-zinc-400"></i>
                        <span>Buka Editor Rute</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-zinc-900/60 border border-zinc-800/80 rounded-2xl p-5 backdrop-blur-sm flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-ruler-horizontal text-emerald-400 text-xs"></i> Jarak Total
                </span>
                <div class="mt-3">
                    <span class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ number_format((float)($item->distance_km ?? 0), 2) }}</span>
                    <span class="text-xs font-semibold text-zinc-400 ml-1">KM</span>
                </div>
            </div>

            <div class="bg-zinc-900/60 border border-zinc-800/80 rounded-2xl p-5 backdrop-blur-sm flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-trend-up text-amber-400 text-xs"></i> Elevasi Naik
                </span>
                <div class="mt-3">
                    <span class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ number_format((float)($item->elevation_gain_m ?? 0)) }}</span>
                    <span class="text-xs font-semibold text-zinc-400 ml-1">Meter</span>
                </div>
            </div>

            <div class="bg-zinc-900/60 border border-zinc-800/80 rounded-2xl p-5 backdrop-blur-sm flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-trend-down text-cyan-400 text-xs"></i> Elevasi Turun
                </span>
                <div class="mt-3">
                    <span class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ number_format((float)($item->elevation_loss_m ?? 0)) }}</span>
                    <span class="text-xs font-semibold text-zinc-400 ml-1">Meter</span>
                </div>
            </div>

            <div class="bg-zinc-900/60 border border-zinc-800/80 rounded-2xl p-5 backdrop-blur-sm flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-user text-indigo-400 text-xs"></i> Kontributor
                </span>
                <div class="mt-3">
                    <span class="text-base font-bold text-white block truncate">{{ $item->user?->name ?? 'RuangLari Official' }}</span>
                    <span class="text-[11px] font-medium text-zinc-400 block mt-0.5">{{ $item->created_at ? $item->created_at->format('d M Y') : 'Terverifikasi' }}</span>
                </div>
            </div>
        </div>

        <!-- Interactive Route Map Container -->
        <div class="bg-zinc-900/70 border border-zinc-800/80 rounded-3xl p-5 backdrop-blur-md space-y-4 shadow-xl">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-map text-emerald-400 text-sm"></i>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-200">Peta & Trase Rute</h3>
                </div>
                <span class="text-xs font-mono text-zinc-400 bg-zinc-800 px-3 py-1 rounded-full border border-zinc-700/60">GPS Track</span>
            </div>

            <div id="gpx-detail-map" class="relative"></div>
        </div>

        <!-- Route Description & Notes Section -->
        @if($item->description || $item->notes)
            <div class="bg-zinc-900/60 border border-zinc-800/80 rounded-3xl p-6 md:p-8 backdrop-blur-md space-y-4">
                <h3 class="text-base font-bold uppercase tracking-wider text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-zinc-400"></i>
                    <span>Informasi & Catatan Rute</span>
                </h3>
                
                <div class="text-zinc-300 text-sm leading-relaxed space-y-3 prose prose-invert max-w-none">
                    @if($item->description)
                        <p class="whitespace-pre-line">{{ $item->description }}</p>
                    @endif

                    @if($item->notes && $item->notes !== $item->description)
                        <div class="p-4 rounded-2xl bg-zinc-950/80 border border-zinc-800 text-xs text-zinc-300 font-mono">
                            <strong class="text-zinc-200 block mb-1">Catatan Tambahan:</strong>
                            <p class="whitespace-pre-line">{{ $item->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Related GPX Routes -->
        @if(isset($related) && $related->count() > 0)
            <div class="space-y-4 pt-4 border-t border-zinc-800/80">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-extrabold italic tracking-tight text-white">RUTE GPX LAINNYA</h3>
                    <a href="{{ route('gpx.index') }}" class="text-xs font-bold text-emerald-400 hover:underline">Lihat Semua →</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($related as $rel)
                        <a href="{{ route('gpx.show', $rel->slug ?: $rel->id) }}" class="group bg-zinc-900/60 border border-zinc-800/80 hover:border-zinc-700 rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between hover:shadow-lg">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2 text-[10px] font-bold text-zinc-400 uppercase">
                                    <span class="text-emerald-400"><i class="fa-solid fa-location-dot"></i> {{ $rel->city ?? 'Indonesia' }}</span>
                                    <span>{{ number_format((float)($rel->distance_km ?? 0), 1) }} KM</span>
                                </div>

                                <h4 class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors line-clamp-1">
                                    {{ $rel->title }}
                                </h4>
                            </div>

                            <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-center justify-between text-xs font-medium text-zinc-400">
                                <span><i class="fa-solid fa-arrow-trend-up text-amber-400/80 text-[10px]"></i> {{ number_format((float)($rel->elevation_gain_m ?? 0)) }}m</span>
                                <span class="text-emerald-400 font-bold group-hover:translate-x-1 transition-transform">Detail →</span>
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
            const mapboxToken = "{{ config('services.mapbox.token') }}";
            const tileUrl = mapboxToken
                ? 'https://api.mapbox.com/styles/v1/mapbox/light-v11/tiles/{z}/{x}/{y}?access_token=' + mapboxToken
                : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

            const tileAttr = mapboxToken
                ? '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                : '&copy; <a href="https://carto.com/">CARTO</a> &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';

            const map = L.map('gpx-detail-map', {
                zoomControl: true,
                scrollWheelZoom: false,
                dragging: true,
            }).setView([-6.2088, 106.8456], 12);

            L.tileLayer(tileUrl, {
                maxZoom: 19,
                tileSize: mapboxToken ? 512 : 256,
                zoomOffset: mapboxToken ? -1 : 0,
                attribution: tileAttr,
            }).addTo(map);

            function parseCoords(data) {
                if (!data) return [];
                if (typeof data === 'string') {
                    try { data = JSON.parse(data); } catch(e) { return []; }
                }
                if (!Array.isArray(data)) return [];

                return data.map(pt => {
                    if (Array.isArray(pt) && pt.length >= 2) {
                        return [parseFloat(pt[0]), parseFloat(pt[1])];
                    }
                    if (pt && typeof pt === 'object') {
                        const lat = parseFloat(pt.lat ?? pt.latitude);
                        const lng = parseFloat(pt.lng ?? pt.lon ?? pt.longitude);
                        if (!isNaN(lat) && !isNaN(lng)) {
                            return [lat, lng];
                        }
                    }
                    return null;
                }).filter(Boolean);
            }

            const latLngs = parseCoords(rawRouteCoords);

            if (latLngs.length > 0) {
                const polyline = L.polyline(latLngs, {
                    color: '#0284c7', // Sky Blue contrast on Mapbox Light
                    weight: 4.5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);

                // Custom Start & Finish Markers
                const startIcon = L.divIcon({
                    className: 'custom-start-marker',
                    html: '<div class="w-6 h-6 rounded-full bg-emerald-500 border-2 border-white shadow-lg flex items-center justify-center text-[10px] font-black text-white">S</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const finishIcon = L.divIcon({
                    className: 'custom-finish-marker',
                    html: '<div class="w-6 h-6 rounded-full bg-rose-500 border-2 border-white shadow-lg flex items-center justify-center text-[10px] font-black text-white">F</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                L.marker(latLngs[0], { icon: startIcon }).addTo(map).bindPopup('<b>Start Point</b>');
                L.marker(latLngs[latLngs.length - 1], { icon: finishIcon }).addTo(map).bindPopup('<b>Finish Point</b>');

                map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
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
                    alert('Link rute GPX berhasil disalin!');
                });
            }
        }
    </script>
@endpush
