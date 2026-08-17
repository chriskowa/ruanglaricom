@extends('layouts.pacerhub')

@php
    $formattedDist = number_format((float)($activity->distance_km ?? 0), 2);
    $pageTitle = e($activity->title) . ' (' . $formattedDist . ' km) oleh ' . e($activity->user?->name ?? 'Runner') . ' - Aktivitas Lari | RuangLari';
    $pageDesc = 'Aktivitas lari ' . e($activity->title) . '. Jarak: ' . $formattedDist . ' km, Waktu: ' . $activity->formatted_moving_time . ', Pace: ' . $activity->formatted_avg_pace . '.';
    $coordsJson = json_encode($activity->coordinates_json ?? []);
    $splitsJson = json_encode($activity->splits_json ?? []);
    $isOwner = Auth::check() && Auth::id() === $activity->user_id;
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        #activity-map {
            height: 460px;
            width: 100%;
            background: #0d121c;
            z-index: 1;
        }
        .leaflet-tile {
            filter: brightness(0.95) contrast(1.05);
        }
        .activity-table-wrap::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }
        .activity-table-wrap::-webkit-scrollbar-track {
            background: #0b0f17;
        }
        .activity-table-wrap::-webkit-scrollbar-thumb {
            background: #1e2638;
            border-radius: 2px;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen pt-20 sm:pt-24 pb-16 px-3 sm:px-4 md:px-8 bg-[#0B0F17] text-slate-200 font-sans overflow-x-hidden w-full max-w-full">
    <div class="max-w-5xl mx-auto space-y-4 sm:space-y-5">

        <!-- Breadcrumb & Utility Bar -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 text-xs text-slate-400">
            <nav class="flex items-center gap-1.5 font-medium truncate max-w-[240px] sm:max-w-none">
                <a href="{{ route('gpx.index') }}" class="hover:text-slate-200 transition shrink-0">Database GPX</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-400 shrink-0">Aktivitas</span>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 truncate">{{ $activity->title }}</span>
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                @if($isOwner)
                    <button type="button" onclick="deleteActivity()" class="px-2.5 py-1.5 rounded-md border border-red-900/60 hover:border-red-700 bg-red-950/30 text-red-400 hover:text-red-300 text-xs font-medium transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-regular fa-trash-can text-[11px]"></i>
                        <span>Hapus</span>
                    </button>
                @endif
                <button type="button" onclick="shareActivity()" class="px-2.5 py-1.5 rounded-md border border-slate-700 hover:border-slate-600 text-slate-300 hover:text-white text-xs font-medium transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-share-nodes text-[11px] text-slate-400"></i>
                    <span>Bagikan</span>
                </button>
            </div>
        </div>

        <!-- Activity Header (Professional & Compact) -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 pb-3 border-b border-slate-800/80">
            <div class="space-y-2 max-w-3xl">
                <!-- User Meta Row -->
                <div class="flex items-center gap-2.5">
                    <img src="{{ $activity->user?->avatar_url ?? ('https://ui-avatars.com/api/?name=' . urlencode($activity->user?->name ?? 'Runner') . '&background=1e293b&color=fc4c02&bold=true') }}" 
                         alt="{{ $activity->user?->name ?? 'Runner' }}" 
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Runner&background=1e293b&color=fc4c02&bold=true';"
                         class="w-9 h-9 rounded-full object-cover border border-slate-700 shrink-0 shadow-sm">
                    <div class="min-w-0">
                        <span class="text-xs sm:text-sm font-semibold text-white block truncate">{{ $activity->user?->name ?? 'RuangLari Runner' }}</span>
                        <span class="text-[11px] text-slate-400 block font-mono">
                            {{ $activity->start_time ? $activity->start_time->format('l, d M Y • H:i') : $activity->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>

                <h1 class="text-lg sm:text-xl md:text-2xl font-bold tracking-tight text-white break-words">
                    {{ $activity->title }}
                </h1>

                @if($activity->notes)
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl font-normal whitespace-pre-line bg-[#0D131F] border border-slate-800/80 rounded-lg p-3">
                        {{ $activity->notes }}
                    </p>
                @endif
            </div>

            <!-- Actions Bar -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('activities.download', $activity->id) }}" class="flex-1 sm:flex-initial px-3.5 py-2 rounded-lg bg-[#FC4C02] hover:bg-[#e04300] text-white font-semibold text-xs uppercase tracking-wide transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer text-center">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Export GPX</span>
                </a>

                @if($activity->masterGpx)
                    <a href="{{ route('gpx.show', $activity->masterGpx->slug ?: $activity->masterGpx->id) }}" class="flex-1 sm:flex-initial px-3.5 py-2 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-850 hover:bg-slate-800 text-slate-200 font-semibold text-xs transition flex items-center justify-center gap-1.5 text-center">
                        <i class="fa-solid fa-route text-xs text-[#FC4C02]"></i>
                        <span>Rute Asli</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Consolidated Metric Strip (Sleek Professional Font Sizes) -->
        <div class="bg-[#111724] border border-slate-800/80 rounded-lg grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-slate-800/80">
            <div class="p-3 sm:p-3.5">
                <span class="text-[11px] font-medium text-slate-400 block">Jarak</span>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold font-mono text-white tabular-nums">{{ $formattedDist }}</span>
                    <span class="text-xs text-slate-400 font-mono">km</span>
                </div>
            </div>

            <div class="p-3 sm:p-3.5">
                <span class="text-[11px] font-medium text-slate-400 block">Waktu Bergerak</span>
                <div class="mt-0.5">
                    <span class="text-xl sm:text-2xl font-bold font-mono text-white tabular-nums">{{ $activity->formatted_moving_time }}</span>
                </div>
            </div>

            <div class="p-3 sm:p-3.5">
                <span class="text-[11px] font-medium text-slate-400 block">Avg Pace</span>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold font-mono text-[#FC4C02] tabular-nums">{{ $activity->formatted_avg_pace }}</span>
                    <span class="text-xs text-slate-400 font-mono">/km</span>
                </div>
            </div>

            <div class="p-3 sm:p-3.5">
                <span class="text-[11px] font-medium text-slate-400 block">Elevasi Gain</span>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold font-mono text-white tabular-nums">+{{ round($activity->elevation_gain_m ?? 0) }}</span>
                    <span class="text-xs text-slate-400 font-mono">m</span>
                </div>
            </div>

            <div class="p-3 sm:p-3.5 col-span-2 sm:col-span-1 border-t sm:border-t-0 border-slate-800/80">
                <span class="text-[11px] font-medium text-slate-400 block">Kalori</span>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-xl sm:text-2xl font-bold font-mono text-white tabular-nums">{{ number_format($activity->calories ?? 0) }}</span>
                    <span class="text-xs text-slate-400 font-mono">kcal</span>
                </div>
            </div>
        </div>

        <!-- Recorded GPS Map & Integrated Elevation Stage -->
        <div class="bg-[#111724] border border-slate-800/80 rounded-lg overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b border-slate-800 flex items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FC4C02]"></span>
                    <h2 class="font-semibold text-slate-200 text-xs">Peta Jejak GPS</h2>
                </div>
                <div class="flex items-center gap-3 text-slate-400 font-medium text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Start</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#FC4C02]"></span> Finish</span>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="activity-map" class="relative w-full h-[280px] sm:h-[360px] md:h-[420px] bg-[#0d121c]"></div>

            <!-- Elevation Profile -->
            <div class="p-3.5 sm:p-4 bg-[#0D131F] border-t border-slate-800 space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
                    <span class="font-medium text-slate-300 text-xs">Profil Elevasi</span>
                    <div class="flex items-center gap-3 font-mono text-[11px]">
                        <span>Min: <strong id="act-min-elev" class="text-slate-200">0m</strong></span>
                        <span>Max: <strong id="act-max-elev" class="text-slate-200">0m</strong></span>
                        <span>Gain: <strong class="text-slate-200">+{{ round($activity->elevation_gain_m ?? 0) }}m</strong></span>
                    </div>
                </div>

                <div class="relative w-full h-20 sm:h-24 select-none" id="act-elev-container">
                    <svg id="act-elev-svg" class="w-full h-full block overflow-visible" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="actElevGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#FC4C02" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#FC4C02" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path id="act-elev-area" fill="url(#actElevGrad)" d="" />
                        <path id="act-elev-line" fill="none" stroke="#FC4C02" stroke-width="1.8" stroke-linecap="round" d="" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Splits Table Section -->
        @if(!empty($activity->splits_json) && is_array($activity->splits_json) && count($activity->splits_json) > 0)
            <div class="bg-[#111724] border border-slate-800/80 rounded-lg p-3.5 sm:p-5 space-y-3">
                <h3 class="text-xs sm:text-sm font-bold text-white uppercase">Rincian Split per Kilometer</h3>
                
                <div class="activity-table-wrap overflow-x-auto w-full">
                    <table class="w-full text-left text-xs min-w-[320px]">
                        <thead class="bg-[#0D131F] text-slate-400 uppercase text-[10px] font-mono border-b border-slate-800">
                            <tr>
                                <th class="py-2 px-3 font-semibold">KM</th>
                                <th class="py-2 px-3 font-semibold">Pace</th>
                                <th class="py-2 px-3 font-semibold">Waktu Split</th>
                                <th class="py-2 px-3 font-semibold">Kumulatif</th>
                                <th class="py-2 px-3 text-right font-semibold">Elevasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-mono text-slate-300">
                            @foreach($activity->splits_json as $s)
                                <tr class="hover:bg-slate-800/40">
                                    <td class="py-2 px-3 font-bold text-white">{{ $s['km'] ?? '-' }}</td>
                                    <td class="py-2 px-3 font-semibold text-[#FC4C02]">{{ $s['pace'] ?? '-' }}</td>
                                    <td class="py-2 px-3 text-slate-300">{{ $s['split'] ?? '-' }}</td>
                                    <td class="py-2 px-3 text-slate-400">{{ $s['cum'] ?? '-' }}</td>
                                    <td class="py-2 px-3 text-right text-slate-300">{{ $s['elev'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const activityCoords = {!! $coordsJson !!};
        const totalDistance = {{ (float)($activity->distance_km ?? 0) }};

        document.addEventListener('DOMContentLoaded', function() {
            initActivityMap();
            initActivityElevation();
        });

        function initActivityMap() {
            const tileUrl = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

            const map = L.map('activity-map', {
                zoomControl: true,
                scrollWheelZoom: false,
                dragging: true,
            }).setView([-6.2088, 106.8456], 13);

            L.tileLayer(tileUrl, { maxZoom: 19, subdomains: 'abcd' }).addTo(map);

            if (Array.isArray(activityCoords) && activityCoords.length > 0) {
                const latlngs = [];
                activityCoords.forEach(p => {
                    const lat = p.lat !== undefined ? p.lat : (Array.isArray(p) ? p[0] : null);
                    const lng = p.lng !== undefined ? p.lng : (Array.isArray(p) ? p[1] : null);
                    if (lat && lng) latlngs.push([lat, lng]);
                });

                if (latlngs.length > 0) {
                    const polyline = L.polyline(latlngs, {
                        color: '#FC4C02',
                        weight: 4.5,
                        opacity: 0.95,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(map);

                    const startIcon = L.divIcon({
                        className: 'act-start-marker',
                        html: '<div class="w-5 h-5 rounded-full bg-emerald-600 border border-white text-[9px] font-mono font-bold text-white flex items-center justify-center shadow">S</div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    const finishIcon = L.divIcon({
                        className: 'act-finish-marker',
                        html: '<div class="w-5 h-5 rounded-full bg-[#FC4C02] border border-white text-[9px] font-mono font-bold text-white flex items-center justify-center shadow">F</div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    L.marker(latlngs[0], { icon: startIcon }).addTo(map).bindPopup('<b>Start Lari</b>');
                    L.marker(latlngs[latlngs.length - 1], { icon: finishIcon }).addTo(map).bindPopup('<b>Finish Lari</b>');

                    map.fitBounds(polyline.getBounds(), { padding: [35, 35] });
                }
            }
        }

        function initActivityElevation() {
            const svg = document.getElementById('act-elev-svg');
            const areaPath = document.getElementById('act-elev-area');
            const linePath = document.getElementById('act-elev-line');
            const container = document.getElementById('act-elev-container');
            if (!container || !areaPath || !linePath || !Array.isArray(activityCoords) || activityCoords.length < 2) return;

            const elePoints = activityCoords.filter(p => p.ele !== undefined && p.ele !== null);
            if (elePoints.length < 2) return;

            const elevations = elePoints.map(p => parseFloat(p.ele));
            const minEl = Math.min(...elevations);
            const maxEl = Math.max(...elevations);
            const range = Math.max(maxEl - minEl, 5);

            document.getElementById('act-min-elev').textContent = Math.round(minEl) + 'm';
            document.getElementById('act-max-elev').textContent = Math.round(maxEl) + 'm';

            const width = container.clientWidth || 700;
            const height = container.clientHeight || 96;
            svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

            const pathData = [];
            elePoints.forEach((p, idx) => {
                const x = (idx / (elePoints.length - 1)) * width;
                const norm = (p.ele - minEl) / range;
                const y = height - 6 - (norm * (height - 12));
                pathData.push({ x, y });
            });

            let lineD = `M ${pathData[0].x.toFixed(1)} ${pathData[0].y.toFixed(1)}`;
            for (let i = 1; i < pathData.length; i++) {
                lineD += ` L ${pathData[i].x.toFixed(1)} ${pathData[i].y.toFixed(1)}`;
            }
            const areaD = lineD + ` L ${width} ${height} L 0 ${height} Z`;

            linePath.setAttribute('d', lineD);
            areaPath.setAttribute('d', areaD);
        }

        function shareActivity() {
            if (navigator.share) {
                navigator.share({
                    title: "{{ e($activity->title) }}",
                    text: "{{ e($pageDesc) }}",
                    url: window.location.href,
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link aktivitas lari berhasil disalin ke clipboard!');
                });
            }
        }

        function deleteActivity() {
            if (!confirm('Apakah Anda yakin ingin menghapus catatan aktivitas lari ini?')) return;

            fetch("{{ route('activities.destroy', $activity->id) }}", {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect_url || "{{ route('activities.index') }}";
                } else {
                    alert(data.message || 'Gagal menghapus aktivitas.');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'));
        }
    </script>
@endpush
