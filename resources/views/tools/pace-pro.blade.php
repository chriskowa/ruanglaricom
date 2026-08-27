@extends('layouts.pacerhub')

@section('title', 'Ruang Lari Tools - PacePro Planner')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        .leaflet-control { display: none; }
        select, select option, select optgroup {
            background-color: #0c121e !important;
            color: #ffffff !important;
        }
        select option:hover, select option:focus, select option:checked {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }
        select option:disabled {
            color: #64748b !important;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen pt-16 sm:pt-20 pb-10 px-3.5 sm:px-6 md:px-8 bg-[#08111F] text-white">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight text-white uppercase">
                        PACE<span class="text-[#B8FF00]">PRO</span> PLANNER
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Rancang strategi split pace berbasis target waktu dan rute lomba (Manual, GPX Event, atau Gambar Rute).
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
                <!-- Sidebar Form Controls -->
                <div class="lg:col-span-4 space-y-4 sm:space-y-6">
                    <div class="bg-[#0E1A2D] border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl">
                        <h2 class="text-base sm:text-lg font-bold mb-4 text-white">
                            Konfigurasi Lomba
                        </h2>

                        <!-- Mode Switcher Tabs -->
                        <div class="flex p-1 bg-[#111724] border border-slate-800 rounded-xl mb-5">
                            <button type="button" data-pp-tab="manual" class="flex-1 py-2 text-xs sm:text-sm font-bold rounded-lg bg-slate-800 text-white shadow-sm transition-all text-center">Manual</button>
                            <button type="button" data-pp-tab="gpx" class="flex-1 py-2 text-xs sm:text-sm font-bold rounded-lg text-slate-400 hover:text-white transition-all text-center">GPX Event</button>
                            <button type="button" data-pp-tab="draw" class="flex-1 py-2 text-xs sm:text-sm font-bold rounded-lg text-slate-400 hover:text-white transition-all text-center">Gambar Rute</button>
                        </div>

                        <!-- 1. Manual Input Mode -->
                        <div id="pp-input-manual" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Jarak Lomba</label>
                                <select id="pp-distance-select" class="w-full bg-[#111724] border border-slate-700 rounded-xl p-2.5 sm:p-3 focus:border-[#B8FF00] focus:ring-1 focus:ring-[#B8FF00] outline-none text-white text-xs sm:text-sm transition cursor-pointer" style="background-color: #111724 !important; color: #ffffff !important;">
                                    <option value="5" style="background-color: #0c121e !important; color: #ffffff !important;">5K (5.0 km)</option>
                                    <option value="10" style="background-color: #0c121e !important; color: #ffffff !important;">10K (10.0 km)</option>
                                    <option value="21.0975" style="background-color: #0c121e !important; color: #ffffff !important;">Half Marathon (21.1 km)</option>
                                    <option value="42.195" style="background-color: #0c121e !important; color: #ffffff !important;">Full Marathon (42.2 km)</option>
                                    <option value="custom" style="background-color: #0c121e !important; color: #ffffff !important;">Custom...</option>
                                </select>
                                <input type="number" id="pp-custom-distance" placeholder="Masukkan jarak dalam km" class="hidden mt-2 w-full bg-[#111724] border border-slate-700 rounded-xl p-2.5 sm:p-3 text-white text-xs sm:text-sm placeholder:text-slate-400">
                            </div>
                        </div>

                        <!-- 2. GPX Library Mode -->
                        <div id="pp-input-gpx" class="space-y-4 hidden">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Pilih GPX Event</label>
                                <select id="pp-gpx-library" class="w-full bg-[#111724] border border-slate-700 rounded-xl p-2.5 sm:p-3 focus:border-[#B8FF00] focus:ring-1 focus:ring-[#B8FF00] outline-none text-white text-xs sm:text-sm transition cursor-pointer" style="background-color: #111724 !important; color: #ffffff !important;">
                                    <option value="" style="background-color: #0c121e !important; color: #94a3b8 !important;">Pilih GPX dari Event...</option>
                                </select>
                            </div>
                            <button id="pp-gpx-load" type="button" class="w-full bg-[#B8FF00] text-[#08111F] font-black py-2.5 sm:py-3 rounded-xl hover:bg-lime-300 transition text-xs sm:text-sm uppercase tracking-wider">
                                Load GPX
                            </button>

                            <div class="pt-3 border-t border-slate-800">
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Atau Upload GPX Sendiri</label>
                                <div class="border border-dashed border-slate-700 rounded-xl p-4 sm:p-6 text-center hover:border-slate-500 bg-[#111724] transition cursor-pointer relative">
                                    <input type="file" id="pp-gpx-file" accept=".gpx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="text-xs sm:text-sm text-slate-300 flex items-center justify-center gap-2" id="pp-file-name">
                                        <i class="fa-solid fa-cloud-arrow-up text-sm text-slate-400"></i>
                                        <span>Klik untuk upload GPX</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Draw Route Mode -->
                        <div id="pp-input-draw" class="space-y-4 hidden">
                            <!-- Search Location -->
                            <div class="flex gap-2">
                                <input type="text" id="pp-draw-search-input" placeholder="Cari lokasi / kota..." class="flex-1 bg-[#111724] border border-slate-700 rounded-xl p-2.5 text-white text-xs placeholder:text-slate-400 focus:border-[#B8FF00] outline-none">
                                <button id="pp-draw-search-btn" type="button" class="bg-slate-800 text-white hover:bg-slate-700 px-3.5 rounded-xl text-xs font-bold border border-slate-700 shrink-0">Cari</button>
                            </div>

                            <div class="p-3.5 bg-[#111724] border border-slate-800 rounded-xl space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-300 uppercase">Jarak Rute</label>
                                    <span id="pp-draw-dist" class="text-[#B8FF00] font-black text-base sm:text-lg font-mono">0.00 km</span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2">
                                     <button id="pp-draw-center" type="button" class="bg-slate-800 text-slate-200 hover:bg-slate-700 py-2 rounded-lg text-[11px] font-bold border border-slate-700 transition">Center Last</button>
                                     <button id="pp-draw-fit" type="button" class="bg-slate-800 text-slate-200 hover:bg-slate-700 py-2 rounded-lg text-[11px] font-bold border border-slate-700 transition">Fit Route</button>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                     <button id="pp-draw-undo" type="button" class="bg-slate-800 text-slate-200 hover:bg-slate-700 py-2 rounded-lg text-xs font-bold border border-slate-700 transition" title="Ctrl+Z">Undo</button>
                                     <button id="pp-draw-redo" type="button" class="bg-slate-800 text-slate-200 hover:bg-slate-700 py-2 rounded-lg text-xs font-bold border border-slate-700 transition" title="Ctrl+Y">Redo</button>
                                     <button id="pp-draw-clear" type="button" class="bg-slate-800 text-rose-400 hover:bg-slate-700 py-2 rounded-lg text-xs font-bold border border-slate-700 transition">Reset</button>
                                </div>
                                
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-200 cursor-pointer select-none pt-1">
                                    <input type="checkbox" id="pp-draw-road" class="accent-[#B8FF00] rounded" checked>
                                    <span>Ikuti Jalan (Snap to Road)</span>
                                </label>

                                <div class="flex flex-wrap gap-2.5 text-[10px] text-slate-400 justify-center pt-2 border-t border-slate-800">
                                     <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Start</span>
                                     <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Waypoint</span>
                                     <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Finish</span>
                                </div>
                            </div>
                        </div>

                        <!-- Target Time & Strategy Inputs -->
                        <div class="mt-4 space-y-4 pt-3 border-t border-slate-800">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Target Waktu (Jam : Menit : Detik)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="relative">
                                        <input type="number" id="pp-time-h" placeholder="00" min="0" class="w-full bg-[#111724] border border-slate-700 rounded-xl p-2.5 sm:p-3 text-center text-white text-xs sm:text-sm font-mono focus:border-[#B8FF00] outline-none">
                                        <span class="text-[9px] text-slate-400 block text-center mt-0.5">Jam</span>
                                    </div>
                                    <div class="relative">
                                        <input type="number" id="pp-time-m" placeholder="00" min="0" max="59" class="w-full bg-[#111724] border border-slate-700 rounded-xl p-2.5 sm:p-3 text-center text-white text-xs sm:text-sm font-mono focus:border-[#B8FF00] outline-none">
                                        <span class="text-[9px] text-slate-400 block text-center mt-0.5">Menit</span>
                                    </div>
                                    <div class="relative">
                                        <input type="number" id="pp-time-s" placeholder="00" min="0" max="59" class="w-full bg-[#111724] border border-slate-700 rounded-xl p-2.5 sm:p-3 text-center text-white text-xs sm:text-sm font-mono focus:border-[#B8FF00] outline-none">
                                        <span class="text-[9px] text-slate-400 block text-center mt-0.5">Detik</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Strategi Pacing</label>
                                    <span id="pp-strategy-label" class="text-xs font-bold text-[#B8FF00]">Even Split</span>
                                </div>
                                <input type="range" id="pp-strategy-slider" min="-10" max="10" value="0" step="1" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-[#B8FF00]">
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>Negative Split</span>
                                    <span>Positive Split</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Hill Strategy</label>
                                    <span id="pp-hill-label" class="text-xs font-bold text-[#B8FF00]">Normal</span>
                                </div>
                                <input type="range" id="pp-hill-slider" min="-10" max="10" value="0" step="1" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-[#B8FF00]">
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>Agresif di tanjakan</span>
                                    <span>Konservatif di tanjakan</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Map Style</label>
                                <div class="flex p-1 bg-[#111724] border border-slate-800 rounded-xl">
                                    <button type="button" id="pp-map-style-dark" class="flex-1 py-1.5 text-xs font-bold rounded-lg text-slate-400 hover:text-white transition-all">Dark</button>
                                    <button type="button" id="pp-map-style-light" class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-slate-800 text-white shadow-sm transition-all">Light</button>
                                </div>
                            </div>

                            <button id="pp-generate" type="button" class="w-full bg-[#B8FF00] text-[#08111F] font-black py-3.5 sm:py-4 rounded-xl hover:bg-lime-300 transition text-xs sm:text-sm uppercase tracking-wider shadow-lg shadow-[#B8FF00]/20 cursor-pointer">
                                Generate Strategy
                            </button>
                        </div>
                    </div>

                    <!-- Summary Stats Card -->
                    <div id="pp-stats-card" class="bg-[#0E1A2D] border border-slate-800 rounded-2xl p-4 sm:p-5 hidden shadow-xl">
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div class="bg-[#111724] border border-slate-800 p-3 rounded-xl">
                                <p class="text-[10px] text-slate-400 uppercase font-medium">Average Pace</p>
                                <p class="text-lg sm:text-xl font-bold font-mono text-white mt-0.5" id="pp-avg-pace">--:--</p>
                            </div>
                            <div class="bg-[#111724] border border-slate-800 p-3 rounded-xl">
                                <p class="text-[10px] text-slate-400 uppercase font-medium">Total Distance</p>
                                <p class="text-lg sm:text-xl font-bold font-mono text-[#B8FF00] mt-0.5" id="pp-total-dist">-- km</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content (Map & Strategy Table) -->
                <div class="lg:col-span-8 flex flex-col gap-5 sm:gap-6">
                    <!-- Map Box -->
                    <div class="bg-[#0E1A2D] border border-slate-800 rounded-2xl p-1.5 h-[280px] sm:h-[340px] md:h-[400px] relative overflow-hidden shadow-xl">
                        <div id="pp-map" class="w-full h-full rounded-xl bg-slate-900"></div>
                        <div class="absolute bottom-3 right-3 z-20 bg-slate-950/80 px-2.5 py-1 rounded-md text-[10px] text-slate-300 border border-slate-800">
                            © OpenStreetMap
                        </div>
                    </div>

                    <!-- Export Actions Buttons -->
                    <div class="flex flex-wrap sm:flex-nowrap justify-end gap-2.5 w-full" id="pp-export-actions">
                        <button id="pp-export-image" type="button" class="flex-1 sm:flex-none justify-center flex items-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white transition cursor-pointer">
                            <i class="fa-solid fa-image text-xs text-slate-400"></i>
                            <span>Simpan Gambar</span>
                        </button>
                        <button id="pp-export-csv" type="button" class="flex-1 sm:flex-none justify-center flex items-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white transition cursor-pointer">
                            <i class="fa-solid fa-file-csv text-xs text-slate-400"></i>
                            <span>Export CSV</span>
                        </button>
                    </div>

                    <!-- Split Table Container -->
                    <div id="pp-capture-area" class="bg-[#0E1A2D] border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                        <div class="p-3.5 sm:p-4 border-b border-slate-800 bg-[#111724] flex justify-between items-center">
                            <h3 class="font-bold text-sm sm:text-base text-white">Split Strategy</h3>
                            <div class="text-[11px] sm:text-xs text-[#B8FF00] font-mono font-semibold">PacePro Generated</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs sm:text-sm text-left" id="pp-pace-table">
                                <thead class="text-[10px] sm:text-xs text-slate-300 font-bold uppercase bg-[#08111F] border-b border-slate-800">
                                    <tr>
                                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Split (KM)</th>
                                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Target Pace</th>
                                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Split Time</th>
                                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Cumulative</th>
                                        <th class="px-3 sm:px-5 py-3 text-right whitespace-nowrap">Elev Gain</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 font-mono text-slate-300">
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-xs sm:text-sm text-slate-400">
                                            Silahkan input jarak dan waktu, lalu klik Generate Strategy.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.RL_MAPBOX_TOKEN = "{{ config('services.mapbox.token') }}";
    </script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        window.RL_PACEPRO_GPX_LIBRARY = @json($gpxFiles);
    </script>
    <script>
        (function () {
            var map;
            var polyline;
            var inputMode = 'manual';
            var gpxData = null;

            // Drawing State
            var drawPoints = [];     // Key points (markers)
            var drawRoutePts = [];   // Full polyline points (including OSRM geometry)
            var drawMarkers = [];
            var drawPolyline = null;
            var drawSeq = 0;
            
            // History State
            var drawHistory = [];
            var drawHistoryIndex = -1;

            var els = {
                tabButtons: document.querySelectorAll('[data-pp-tab]'),
                boxManual: document.getElementById('pp-input-manual'),
                boxGpx: document.getElementById('pp-input-gpx'),
                boxDraw: document.getElementById('pp-input-draw'),
                drawDist: document.getElementById('pp-draw-dist'),
                drawUndo: document.getElementById('pp-draw-undo'),
                drawRedo: document.getElementById('pp-draw-redo'),
                drawClear: document.getElementById('pp-draw-clear'),
                drawRoad: document.getElementById('pp-draw-road'),
                drawSearchInput: document.getElementById('pp-draw-search-input'),
                drawSearchBtn: document.getElementById('pp-draw-search-btn'),
                drawCenter: document.getElementById('pp-draw-center'),
                drawFit: document.getElementById('pp-draw-fit'),
                distanceSelect: document.getElementById('pp-distance-select'),
                customDistance: document.getElementById('pp-custom-distance'),
                gpxFile: document.getElementById('pp-gpx-file'),
                fileName: document.getElementById('pp-file-name'),
                gpxLibrary: document.getElementById('pp-gpx-library'),
                gpxLoad: document.getElementById('pp-gpx-load'),
                timeH: document.getElementById('pp-time-h'),
                timeM: document.getElementById('pp-time-m'),
                timeS: document.getElementById('pp-time-s'),
                strategySlider: document.getElementById('pp-strategy-slider'),
                strategyLabel: document.getElementById('pp-strategy-label'),
                hillSlider: document.getElementById('pp-hill-slider'),
                hillLabel: document.getElementById('pp-hill-label'),
                mapStyleDark: document.getElementById('pp-map-style-dark'),
                mapStyleLight: document.getElementById('pp-map-style-light'),
                generate: document.getElementById('pp-generate'),
                statsCard: document.getElementById('pp-stats-card'),
                avgPace: document.getElementById('pp-avg-pace'),
                totalDist: document.getElementById('pp-total-dist'),
                tableBody: document.querySelector('#pp-pace-table tbody'),
                exportCsv: document.getElementById('pp-export-csv'),
                exportImage: document.getElementById('pp-export-image'),
                captureArea: document.getElementById('pp-capture-area'),
            };

            function isMapStyleLight() {
                return els.mapStyleLight && els.mapStyleLight.classList.contains('bg-slate-600');
            }

            function setMapStyle(isLight) {
                if (isLight) {
                    els.mapStyleLight.className = 'flex-1 py-1.5 text-xs font-bold rounded-md bg-slate-600 text-white shadow-sm transition-all';
                    els.mapStyleDark.className = 'flex-1 py-1.5 text-xs font-bold rounded-md text-slate-400 hover:text-white transition-all';
                } else {
                    els.mapStyleDark.className = 'flex-1 py-1.5 text-xs font-bold rounded-md bg-slate-600 text-white shadow-sm transition-all';
                    els.mapStyleLight.className = 'flex-1 py-1.5 text-xs font-bold rounded-md text-slate-400 hover:text-white transition-all';
                }
                localStorage.setItem('paceProMapStyle', isLight ? 'light' : 'dark');
            }

            function switchMode(mode) {
                inputMode = mode;
                els.tabButtons.forEach(function (b) {
                    var active = b.getAttribute('data-pp-tab') === mode;
                    b.className = active
                        ? 'flex-1 py-2 text-xs sm:text-sm font-bold rounded-lg bg-slate-800 text-white shadow-sm transition-all text-center'
                        : 'flex-1 py-2 text-xs sm:text-sm font-bold rounded-lg text-slate-400 hover:text-white transition-all text-center';
                });
                els.boxManual.classList.add('hidden');
                els.boxGpx.classList.add('hidden');
                els.boxDraw.classList.add('hidden');

                if (mode === 'manual') els.boxManual.classList.remove('hidden');
                else if (mode === 'gpx') els.boxGpx.classList.remove('hidden');
                else if (mode === 'draw') els.boxDraw.classList.remove('hidden');
                
                // Clear map layers when switching modes to avoid confusion
                if (map) {
                    if (polyline) map.removeLayer(polyline);
                    if (drawPolyline) map.removeLayer(drawPolyline);
                    drawMarkers.forEach(function(m) { map.removeLayer(m); });
                    setTimeout(function() { map.invalidateSize(); }, 150);
                }
                
                // Restore logic
                if (mode === 'gpx' && gpxData) showGpxOnMap(gpxData.points);
                if (mode === 'draw') {
                    if (drawPolyline) drawPolyline.addTo(map);
                    drawMarkers.forEach(function(m) { m.addTo(map); });
                }
            }

            function haversineKm(p1, p2) {
                return getDistanceKm(p1.lat, p1.lng || p1.lon, p2.lat, p2.lng || p2.lon);
            }

            function osrmRoute(waypoints) {
                var coords = waypoints.map(function (p) { return (p.lng||p.lon).toFixed(6) + ',' + p.lat.toFixed(6); }).join(';');
                var token = window.RL_MAPBOX_TOKEN;
                if (token) {
                    var mapboxUrl = 'https://api.mapbox.com/directions/v5/mapbox/walking/' + coords + '?geometries=geojson&overview=full&steps=false&access_token=' + token;
                    return fetch(mapboxUrl, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) {
                            if (!r.ok) throw new Error('Mapbox route failed');
                            return r.json();
                        })
                        .then(function (data) {
                            if (!data.routes || !data.routes[0]) throw new Error('No route');
                            var coords = data.routes[0].geometry.coordinates;
                            return coords.map(function (c) { return { lat: c[1], lng: c[0] }; });
                        })
                        .catch(function (err) {
                            console.warn('Mapbox routing failed, falling back to OSRM:', err);
                            return fetchOsrmFallback(coords);
                        });
                } else {
                    return fetchOsrmFallback(coords);
                }
            }

            function fetchOsrmFallback(coords) {
                var url = 'https://router.project-osrm.org/route/v1/foot/' + coords + '?overview=full&geometries=geojson&steps=false';
                return fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (json) {
                        if (!json.routes || !json.routes[0]) throw new Error('No route');
                        var coords = json.routes[0].geometry.coordinates;
                        return coords.map(function (c) { return { lat: c[1], lng: c[0] }; });
                    });
            }

            function deg2rad(deg) { return deg * (Math.PI / 180); }
            function getDistanceKm(lat1, lon1, lat2, lon2) {
                var R = 6371;
                var dLat = deg2rad(lat2 - lat1);
                var dLon = deg2rad(lon2 - lon1);
                var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }

            function formatTime(seconds) {
                var h = Math.floor(seconds / 3600);
                var m = Math.floor((seconds % 3600) / 60);
                var s = Math.floor(seconds % 60);
                if (h > 0) return h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                return m + ':' + String(s).padStart(2, '0');
            }

            function strategyLabel(val) {
                if (val === 0) return 'Even Split (Steady)';
                if (val < 0) return 'Negative Split ' + val + '% (Start Slow)';
                return 'Positive Split +' + val + '% (Start Fast)';
            }

            function hillLabel(val) {
                if (val === 0) return 'Normal';
                if (val < 0) return 'Agresif ' + Math.abs(val) + '/10';
                return 'Konservatif ' + val + '/10';
            }

            function parseGpxText(gpxText) {
                var parser = new DOMParser();
                var xmlDoc = parser.parseFromString(gpxText, 'text/xml');
                var trkpts = xmlDoc.getElementsByTagName('trkpt');
                var points = [];
                var totalDist = 0;
                for (var i = 0; i < trkpts.length; i++) {
                    var lat = parseFloat(trkpts[i].getAttribute('lat'));
                    var lon = parseFloat(trkpts[i].getAttribute('lon'));
                    var eleNodes = trkpts[i].getElementsByTagName('ele');
                    var ele = eleNodes && eleNodes.length > 0 ? parseFloat(eleNodes[0].textContent) : 0;
                    if (i > 0) {
                        totalDist += getDistanceKm(points[i - 1].lat, points[i - 1].lon, lat, lon);
                    }
                    points.push({ lat: lat, lon: lon, ele: ele, distFromStart: totalDist });
                }
                return { points: points, totalDist: totalDist };
            }

            function showGpxOnMap(points) {
                if (!map) return;
                var latlngs = points.map(function (p) { return [p.lat, p.lon]; });
                if (polyline) map.removeLayer(polyline);
                var isLight = isMapStyleLight();
                var color = isLight ? '#E60000' : '#CCFF00';

                polyline = L.polyline(latlngs, { color: color, weight: 4 }).addTo(map);
                map.fitBounds(polyline.getBounds());
            }

            function handleGpxUpload(file) {
                if (!file) return;
                els.fileName.textContent = file.name;
                var reader = new FileReader();
                reader.onload = function (e) {
                    gpxData = parseGpxText(String(e.target.result || ''));
                    showGpxOnMap(gpxData.points);
                    alert('GPX Loaded! Total Distance: ' + gpxData.totalDist.toFixed(2) + ' km');
                };
                reader.readAsText(file);
            }

            function fillGpxLibrary() {
                if (!els.gpxLibrary) return;
                var items = Array.isArray(window.RL_PACEPRO_GPX_LIBRARY) ? window.RL_PACEPRO_GPX_LIBRARY : [];
                items.forEach(function (it) {
                    var opt = document.createElement('option');
                    opt.value = String(it.id);
                    opt.className = 'bg-[#0c121e] text-white';
                    opt.style.backgroundColor = '#0c121e';
                    opt.style.color = '#ffffff';
                    var left = it.event_name ? (it.event_name + ' - ') : '';
                    var d = it.distance_km ? (Number(it.distance_km).toFixed(2) + ' km') : '';
                    opt.textContent = left + (it.title || 'GPX') + (d ? (' (' + d + ')') : '');
                    els.gpxLibrary.appendChild(opt);
                });
            }

            function loadGpxFromLibrary() {
                var id = els.gpxLibrary.value;
                if (!id) return;
                var items = Array.isArray(window.RL_PACEPRO_GPX_LIBRARY) ? window.RL_PACEPRO_GPX_LIBRARY : [];
                var found = items.find(function (x) { return String(x.id) === String(id); });
                if (!found || !found.download_url) return;
                fetch(found.download_url, { headers: { 'Accept': 'application/gpx+xml,text/xml,text/plain' } })
                    .then(function (r) { return r.text(); })
                    .then(function (text) {
                        gpxData = parseGpxText(text);
                        showGpxOnMap(gpxData.points);
                        alert('GPX Loaded! Total Distance: ' + gpxData.totalDist.toFixed(2) + ' km');
                    })
                    .catch(function () {
                        alert('Gagal load GPX.');
                    });
            }

            function rebuildDrawLine() {
                if (drawPolyline) map.removeLayer(drawPolyline);
                drawMarkers.forEach(function(m) { map.removeLayer(m); });
                drawMarkers = [];

                if (drawRoutePts.length === 0) {
                    els.drawDist.textContent = '0.00 km';
                    return;
                }

                var latlngs = drawRoutePts.map(function(p) { return [p.lat, p.lng||p.lon]; });
                var isLight = isMapStyleLight();
                var color = isLight ? '#E60000' : '#CCFF00';
                
                drawPolyline = L.polyline(latlngs, { color: color, weight: 4 }).addTo(map);

                // Add markers
                drawPoints.forEach(function(p, idx) {
                    var color = '#3b82f6'; // Default Blue
                    var radius = 6;
                    if (idx === 0) { color = '#22c55e'; radius = 7; } // Start Green
                    else if (idx === drawPoints.length - 1) { color = '#ef4444'; radius = 7; } // End Red

                    var m = L.circleMarker([p.lat, p.lng||p.lon], {
                        radius: radius,
                        color: '#fff',
                        weight: 2,
                        fillColor: color,
                        fillOpacity: 1
                    }).addTo(map);
                    drawMarkers.push(m);
                });

                var dist = 0;
                for(var i=1; i<drawRoutePts.length; i++) {
                    dist += haversineKm(drawRoutePts[i-1], drawRoutePts[i]);
                }
                els.drawDist.textContent = dist.toFixed(2) + ' km';
            }

            function updateDrawRoute(skipRoute) {
                drawSeq++;
                var seq = drawSeq;
                
                if (drawPoints.length < 2) {
                    drawRoutePts = drawPoints.slice();
                    rebuildDrawLine();
                    return;
                }

                // Optimization: if simple mode or just restoring, maybe skip?
                // But for "Ikuti Jalan", we must route.
                if (els.drawRoad.checked && !skipRoute) {
                    els.drawDist.textContent = 'Routing...';
                    osrmRoute(drawPoints)
                        .then(function(pts) {
                            if (seq !== drawSeq) return;
                            drawRoutePts = pts;
                            rebuildDrawLine();
                        })
                        .catch(function() {
                            if (seq !== drawSeq) return;
                            drawRoutePts = drawPoints.slice(); // Fallback
                            rebuildDrawLine();
                        });
                } else {
                    // Direct line (Manual or Fallback)
                    // Note: If we just Undid, we might want to re-route if needed.
                    // But if 'skipRoute' is passed (e.g. strict manual), handle accordingly.
                    // Actually for Undo/Redo we should always re-route if 'drawRoad' is checked.
                    if (els.drawRoad.checked) {
                         // Copy-paste logic to force route
                        els.drawDist.textContent = 'Routing...';
                        osrmRoute(drawPoints)
                            .then(function(pts) {
                                if (seq !== drawSeq) return;
                                drawRoutePts = pts;
                                rebuildDrawLine();
                            })
                            .catch(function() {
                                if (seq !== drawSeq) return;
                                drawRoutePts = drawPoints.slice(); 
                                rebuildDrawLine();
                            });
                    } else {
                        drawRoutePts = drawPoints.slice();
                        rebuildDrawLine();
                    }
                }
            }

            // Undo/Redo & Search Functions
            function commitDrawState() {
                // Remove future history
                if (drawHistoryIndex < drawHistory.length - 1) {
                    drawHistory = drawHistory.slice(0, drawHistoryIndex + 1);
                }
                drawHistory.push(JSON.parse(JSON.stringify(drawPoints)));
                drawHistoryIndex++;
            }

            function performUndo() {
                if (drawHistoryIndex > 0) {
                    drawHistoryIndex--;
                    drawPoints = JSON.parse(JSON.stringify(drawHistory[drawHistoryIndex]));
                    updateDrawRoute();
                }
            }

            function performRedo() {
                if (drawHistoryIndex < drawHistory.length - 1) {
                    drawHistoryIndex++;
                    drawPoints = JSON.parse(JSON.stringify(drawHistory[drawHistoryIndex]));
                    updateDrawRoute();
                }
            }
            
            function searchLocation() {
                var q = els.drawSearchInput.value;
                if (!q) return;
                els.drawSearchBtn.textContent = '...';
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q))
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        els.drawSearchBtn.textContent = 'Cari';
                        if (data && data.length > 0) {
                            var lat = parseFloat(data[0].lat);
                            var lon = parseFloat(data[0].lon);
                            map.setView([lat, lon], 14);
                        } else {
                            alert('Lokasi tidak ditemukan');
                        }
                    })
                    .catch(function(){
                        els.drawSearchBtn.textContent = 'Cari';
                    });
            }

            function calculatePlan() {
                var h = parseInt(els.timeH.value || '0', 10) || 0;
                var m = parseInt(els.timeM.value || '0', 10) || 0;
                var s = parseInt(els.timeS.value || '0', 10) || 0;
                var totalTargetSeconds = (h * 3600) + (m * 60) + s;
                if (totalTargetSeconds === 0) {
                    alert('Mohon masukkan target waktu!');
                    return;
                }

                var totalDistance = 0;
                var elevationProfile = [];

                if (inputMode === 'manual') {
                    var selectVal = els.distanceSelect.value;
                    if (selectVal === 'custom') totalDistance = parseFloat(els.customDistance.value || '0');
                    else totalDistance = parseFloat(selectVal);
                    for (var i = 0; i < Math.ceil(totalDistance); i++) elevationProfile.push(0);
                } else if (inputMode === 'draw') {
                    if (drawRoutePts.length < 2) { alert('Gambar rute dulu.'); return; }
                    totalDistance = 0;
                    for(var i=1; i<drawRoutePts.length; i++) {
                        totalDistance += haversineKm(drawRoutePts[i-1], drawRoutePts[i]);
                    }
                    // Elevation for draw mode is flat for now
                    for (var i = 0; i < Math.ceil(totalDistance); i++) elevationProfile.push(0);
                } else {
                    if (!gpxData) { alert('Pilih GPX dulu.'); return; }
                    totalDistance = gpxData.totalDist;
                    var currentKm = 1;
                    var kmGain = 0;
                    var lastEle = gpxData.points[0] ? gpxData.points[0].ele : 0;
                    gpxData.points.forEach(function (p) {
                        if (p.distFromStart >= currentKm) {
                            elevationProfile.push(kmGain);
                            kmGain = 0;
                            currentKm += 1;
                        }
                        if (p.ele > lastEle) kmGain += (p.ele - lastEle);
                        lastEle = p.ele;
                    });
                    elevationProfile.push(kmGain);
                }

                var avgPaceSeconds = totalTargetSeconds / totalDistance;
                els.avgPace.textContent = formatTime(avgPaceSeconds) + ' /km';
                els.totalDist.textContent = totalDistance.toFixed(2) + ' km';
                els.statsCard.classList.remove('hidden');

                var strategyVal = parseInt(els.strategySlider.value || '0', 10) || 0;
                var intensity = strategyVal / 100;
                var startFactor = 1 - intensity;
                var endFactor = 1 + intensity;

                var hillVal = parseInt(els.hillSlider.value || '0', 10) || 0;
                var hillFactor = 1 + (hillVal / 20);

                els.tableBody.innerHTML = '';
                var tableData = [];
                
                // Pass 1: Calculate Raw Splits
                var rawSplits = [];
                var rawTotalSec = 0;

                for (var km = 1; km <= Math.ceil(totalDistance); km++) {
                    var dist = 1;
                    if (km > totalDistance) dist = totalDistance - (km - 1);

                    var progress = (km - 1) / (totalDistance - 1 || 1);
                    var currentFactor = startFactor + (endFactor - startFactor) * progress;
                    var targetPaceSec = avgPaceSeconds * currentFactor;

                    if (inputMode === 'gpx') {
                        var gain = elevationProfile[km - 1] || 0;
                        if (gain > 0) targetPaceSec += (gain / 10) * 2 * hillFactor;
                    }

                    var splitTimeSec = targetPaceSec * dist;
                    rawTotalSec += splitTimeSec;
                    
                    var elevDisplay = (inputMode === 'gpx') ? ('+' + (elevationProfile[km - 1] || 0).toFixed(0) + 'm') : '-';
                    rawSplits.push({ 
                        km: km, 
                        time: splitTimeSec, 
                        dist: dist, 
                        elev: elevDisplay 
                    });
                }
                
                // Pass 2: Normalize to Exact Target Time
                var diff = totalTargetSeconds - rawTotalSec;
                // Distribute difference proportionally to distance (longer splits absorb more adjustment)
                var adjPerKm = diff / totalDistance; 
                
                var cumulativeSeconds = 0;
                
                rawSplits.forEach(function(s) {
                    var adjustedTime = s.time + (adjPerKm * s.dist);
                    // Ensure time is not negative
                    if (adjustedTime < 1) adjustedTime = 1; 
                    
                    cumulativeSeconds += adjustedTime;
                    
                    // Display Pace = Adjusted Time / Distance
                    var displayPace = adjustedTime / s.dist;

                    var tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-800/60 transition border-b border-slate-800/80';
                    tr.innerHTML =
                        '<td class="px-3 sm:px-5 py-2.5 sm:py-3.5 font-bold text-white whitespace-nowrap">' + s.km + '</td>' +
                        '<td class="px-3 sm:px-5 py-2.5 sm:py-3.5 text-[#B8FF00] font-bold whitespace-nowrap">' + formatTime(displayPace) + '</td>' +
                        '<td class="px-3 sm:px-5 py-2.5 sm:py-3.5 text-slate-200 whitespace-nowrap">' + formatTime(adjustedTime) + '</td>' +
                        '<td class="px-3 sm:px-5 py-2.5 sm:py-3.5 text-slate-400 whitespace-nowrap">' + formatTime(cumulativeSeconds) + '</td>' +
                        '<td class="px-3 sm:px-5 py-2.5 sm:py-3.5 text-right text-slate-300 font-bold whitespace-nowrap">' + s.elev + '</td>';
                    els.tableBody.appendChild(tr);

                    tableData.push([s.km, formatTime(displayPace), formatTime(adjustedTime), formatTime(cumulativeSeconds), s.elev]);
                });
                
                window.lastTableData = tableData;
            }

            function exportCSV() {
                if (!window.lastTableData) { alert('Generate strategy dulu!'); return; }
                var csvContent = 'data:text/csv;charset=utf-8,';
                csvContent += 'KM,Target Pace,Split Time,Cumulative Time,Elevation Gain\n';
                window.lastTableData.forEach(function (rowArray) {
                    csvContent += rowArray.join(',') + '\r\n';
                });
                var encodedUri = encodeURI(csvContent);
                var link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'RuangLari_PacePro_Plan.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportImage() {
                if (!window.lastTableData) { alert('Generate strategy dulu!'); return; }
                html2canvas(els.captureArea, { backgroundColor: '#0F172A', scale: 2 })
                    .then(function (canvas) {
                        var link = document.createElement('a');
                        link.download = 'RuangLari_Strategy.png';
                        link.href = canvas.toDataURL();
                        link.click();
                    });
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Init Map Style from LocalStorage (default to light)
                var storedStyle = localStorage.getItem('paceProMapStyle') || 'light';
                var isLight = storedStyle === 'light';
                setMapStyle(isLight);

                map = L.map('pp-map').setView([-6.2088, 106.8456], 13);

                var mapboxToken = window.RL_MAPBOX_TOKEN;
                var getTileUrl = function(light) {
                    if (mapboxToken) {
                        return light 
                            ? 'https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/{z}/{x}/{y}?access_token=' + mapboxToken
                            : 'https://api.mapbox.com/styles/v1/mapbox/navigation-night-v1/tiles/{z}/{x}/{y}?access_token=' + mapboxToken;
                    }
                    return light 
                        ? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' 
                        : 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}';
                };

                var getTileOpts = function(light) {
                    var opts = {
                        maxZoom: 19,
                        attribution: mapboxToken ? '&copy; Mapbox &copy; OpenStreetMap' : '&copy; OpenStreetMap',
                    };
                    if (mapboxToken) {
                        opts.tileSize = 512;
                        opts.zoomOffset = -1;
                    } else {
                        opts.subdomains = 'abcd';
                    }
                    return opts;
                };
                
                var tileLayer = L.tileLayer(getTileUrl(isLight), getTileOpts(isLight)).addTo(map);

                if (els.mapStyleDark && els.mapStyleLight) {
                    var handleStyleChange = function(light) {
                        setMapStyle(light);
                        if (tileLayer) map.removeLayer(tileLayer);
                        tileLayer = L.tileLayer(getTileUrl(light), getTileOpts(light)).addTo(map);

                        if (polyline) {
                            polyline.setStyle({ color: light ? '#E60000' : '#CCFF00' });
                        }
                        if (drawPolyline) {
                            drawPolyline.setStyle({ color: light ? '#E60000' : '#CCFF00' });
                        }
                    };
                    els.mapStyleDark.addEventListener('click', function() { handleStyleChange(false); });
                    els.mapStyleLight.addEventListener('click', function() { handleStyleChange(true); });
                }

                fillGpxLibrary();

                els.tabButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        switchMode(btn.getAttribute('data-pp-tab'));
                    });
                });

                els.distanceSelect.addEventListener('change', function (e) {
                    if (e.target.value === 'custom') els.customDistance.classList.remove('hidden');
                    else els.customDistance.classList.add('hidden');
                });

                els.strategySlider.addEventListener('input', function () {
                    var val = parseInt(els.strategySlider.value || '0', 10) || 0;
                    els.strategyLabel.textContent = strategyLabel(val);
                });
                els.hillSlider.addEventListener('input', function () {
                    var val = parseInt(els.hillSlider.value || '0', 10) || 0;
                    els.hillLabel.textContent = hillLabel(val);
                });

                els.gpxFile.addEventListener('change', function (e) {
                    var f = e.target.files && e.target.files[0] ? e.target.files[0] : null;
                    handleGpxUpload(f);
                });

                // Drawing Listeners
                
                // Init history with empty state
                commitDrawState();

                map.on('click', function(e) {
                    if (inputMode !== 'draw') return;
                    drawPoints.push({ lat: e.latlng.lat, lng: e.latlng.lng });
                    commitDrawState();
                    updateDrawRoute();
                });
                
                if (els.drawUndo) els.drawUndo.addEventListener('click', performUndo);
                if (els.drawRedo) els.drawRedo.addEventListener('click', performRedo);
                
                if (els.drawClear) els.drawClear.addEventListener('click', function() {
                    drawPoints = [];
                    commitDrawState();
                    updateDrawRoute();
                });

                if (els.drawRoad) els.drawRoad.addEventListener('change', function() { updateDrawRoute(); });

                if (els.drawSearchBtn) els.drawSearchBtn.addEventListener('click', searchLocation);
                if (els.drawSearchInput) els.drawSearchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') searchLocation();
                });
                
                if (els.drawCenter) els.drawCenter.addEventListener('click', function() {
                    if (drawPoints.length > 0) {
                        var p = drawPoints[drawPoints.length-1];
                        map.panTo([p.lat, p.lng||p.lon]);
                    }
                });
                if (els.drawFit) els.drawFit.addEventListener('click', function() {
                    if (drawPolyline) map.fitBounds(drawPolyline.getBounds());
                });

                // Keyboard Shortcuts
                document.addEventListener('keydown', function(e) {
                    if (inputMode !== 'draw') return;
                    if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                        e.preventDefault();
                        performUndo();
                    }
                    if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
                        e.preventDefault();
                        performRedo();
                    }
                });

                els.gpxLoad.addEventListener('click', loadGpxFromLibrary);
                els.generate.addEventListener('click', calculatePlan);
                els.exportCsv.addEventListener('click', exportCSV);
                els.exportImage.addEventListener('click', exportImage);
            });
        })();
    </script>
@endpush

