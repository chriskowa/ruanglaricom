@extends('layouts.pacerhub')

@section('title', 'Ruang Lari Tools - PacePro Planner')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@section('content')
    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8 bg-dark text-white">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-3xl md:text-4xl font-black italic tracking-tighter text-white">
                        PACE<span class="text-neon">PRO</span> PLANNER
                    </h1>
                    <p class="text-slate-400 mt-1 max-w-2xl">
                        Generate split strategy berdasarkan target waktu dan rute (manual / GPX event).
                    </p>
                </div>
            </div>

            <div class="grid lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-card/70 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="text-neon">⚙</span>
                            Konfigurasi Lomba
                        </h2>

                        <div class="flex p-1 bg-slate-800 rounded-lg mb-6">
                            <button type="button" data-pp-tab="manual" class="flex-1 py-2 text-sm font-black rounded-md bg-slate-600 text-white shadow-sm transition-all">Manual Distance</button>
                            <button type="button" data-pp-tab="gpx" class="flex-1 py-2 text-sm font-black rounded-md text-slate-400 hover:text-white transition-all">GPX Event</button>
                        </div>

                        <div id="pp-input-manual" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Jarak Lomba</label>
                                <select id="pp-distance-select" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none text-white">
                                    <option value="5">5K (5.0 km)</option>
                                    <option value="10">10K (10.0 km)</option>
                                    <option value="21.0975">Half Marathon (21.1 km)</option>
                                    <option value="42.195">Full Marathon (42.2 km)</option>
                                    <option value="custom">Custom...</option>
                                </select>
                                <input type="number" id="pp-custom-distance" placeholder="Masukkan km" class="hidden mt-2 w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-white">
                            </div>
                        </div>

                        <div id="pp-input-gpx" class="space-y-4 hidden">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Pilih GPX Event</label>
                            <select id="pp-gpx-library" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none text-white">
                                <option value="">Pilih GPX dari Event...</option>
                            </select>
                            <button id="pp-gpx-load" type="button" class="w-full bg-neon text-dark font-black py-3 rounded-xl hover:bg-white transition">
                                LOAD GPX
                            </button>

                            <div class="pt-2 border-t border-slate-800">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Atau Upload GPX Sendiri</label>
                                <div class="border-2 border-dashed border-slate-700 rounded-lg p-6 text-center hover:border-neon transition cursor-pointer relative">
                                    <input type="file" id="pp-gpx-file" accept=".gpx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="text-sm text-slate-400" id="pp-file-name">Klik untuk upload GPX</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Target Waktu (Jam:Menit:Detik)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="number" id="pp-time-h" placeholder="00" min="0" class="bg-slate-900 border border-slate-700 rounded-lg p-3 text-center text-white focus:border-neon outline-none">
                                    <input type="number" id="pp-time-m" placeholder="00" min="0" max="59" class="bg-slate-900 border border-slate-700 rounded-lg p-3 text-center text-white focus:border-neon outline-none">
                                    <input type="number" id="pp-time-s" placeholder="00" min="0" max="59" class="bg-slate-900 border border-slate-700 rounded-lg p-3 text-center text-white focus:border-neon outline-none">
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between mb-1">
                                    <label class="block text-xs font-bold text-slate-400 uppercase">Strategi Pacing</label>
                                    <span id="pp-strategy-label" class="text-xs font-bold text-neon">Even Split</span>
                                </div>
                                <input type="range" id="pp-strategy-slider" min="-10" max="10" value="0" step="1" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-neon">
                                <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                    <span>Negative Split</span>
                                    <span>Positive Split</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between mb-1">
                                    <label class="block text-xs font-bold text-slate-400 uppercase">Hill Strategy</label>
                                    <span id="pp-hill-label" class="text-xs font-bold text-neon">Normal</span>
                                </div>
                                <input type="range" id="pp-hill-slider" min="-10" max="10" value="0" step="1" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-neon">
                                <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                    <span>Agresif di tanjakan</span>
                                    <span>Konservatif di tanjakan</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between mb-1">
                                    <label class="block text-xs font-bold text-slate-400 uppercase">Map Style</label>
                                    <span id="pp-map-style-label" class="text-xs font-bold text-neon">Dark</span>
                                </div>
                                <input type="range" id="pp-map-style-slider" min="0" max="1" value="0" step="1" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-neon">
                                <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                    <span>Dark</span>
                                    <span>Light</span>
                                </div>
                            </div>

                            <button id="pp-generate" type="button" class="w-full bg-neon text-dark font-black py-4 rounded-xl hover:bg-white transition shadow-[0_0_15px_rgba(204,255,0,0.3)] mt-4">
                                GENERATE STRATEGY ⚡
                            </button>
                        </div>
                    </div>

                    <div id="pp-stats-card" class="bg-card/70 backdrop-blur border border-slate-700/50 rounded-2xl p-6 hidden">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-slate-900 p-3 rounded-lg">
                                <p class="text-xs text-slate-400">Average Pace</p>
                                <p class="text-xl font-black text-white" id="pp-avg-pace">--:--</p>
                            </div>
                            <div class="bg-slate-900 p-3 rounded-lg">
                                <p class="text-xs text-slate-400">Total Distance</p>
                                <p class="text-xl font-black text-white" id="pp-total-dist">-- km</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 flex flex-col gap-6">
                    <div class="bg-card/70 backdrop-blur border border-slate-700/50 rounded-2xl p-1 h-[320px] relative overflow-hidden">
                        <div id="pp-map" class="w-full h-full rounded-xl bg-slate-800"></div>
                        <div class="absolute bottom-2 right-2 z-20 bg-black/50 px-2 py-1 rounded text-[10px] text-white backdrop-blur">
                            © OpenStreetMap
                        </div>
                    </div>

                    <div class="flex justify-end gap-3" id="pp-export-actions">
                        <button id="pp-export-image" type="button" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm font-bold transition">
                            Simpan Gambar
                        </button>
                        <button id="pp-export-csv" type="button" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm font-bold transition">
                            Export CSV
                        </button>
                    </div>

                    <div id="pp-capture-area" class="bg-card/70 backdrop-blur border border-slate-700/50 rounded-2xl p-0 overflow-hidden bg-slate-900">
                        <div class="p-4 border-b border-slate-800 bg-slate-800 flex justify-between items-center">
                            <h3 class="font-black text-white">Split Strategy</h3>
                            <div class="text-xs text-neon font-mono">PacePro Generated</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left" id="pp-pace-table">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                    <tr>
                                        <th class="px-6 py-3">Split (KM)</th>
                                        <th class="px-6 py-3">Target Pace</th>
                                        <th class="px-6 py-3">Split Time</th>
                                        <th class="px-6 py-3">Cumulative</th>
                                        <th class="px-6 py-3 text-right">Elev Gain</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 font-mono text-slate-300">
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
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

            var els = {
                tabButtons: document.querySelectorAll('[data-pp-tab]'),
                boxManual: document.getElementById('pp-input-manual'),
                boxGpx: document.getElementById('pp-input-gpx'),
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
                mapStyleSlider: document.getElementById('pp-map-style-slider'),
                mapStyleLabel: document.getElementById('pp-map-style-label'),
                generate: document.getElementById('pp-generate'),
                statsCard: document.getElementById('pp-stats-card'),
                avgPace: document.getElementById('pp-avg-pace'),
                totalDist: document.getElementById('pp-total-dist'),
                tableBody: document.querySelector('#pp-pace-table tbody'),
                exportCsv: document.getElementById('pp-export-csv'),
                exportImage: document.getElementById('pp-export-image'),
                captureArea: document.getElementById('pp-capture-area'),
            };

            function switchMode(mode) {
                inputMode = mode;
                els.tabButtons.forEach(function (b) {
                    var active = b.getAttribute('data-pp-tab') === mode;
                    b.className = active
                        ? 'flex-1 py-2 text-sm font-black rounded-md bg-slate-600 text-white shadow-sm transition-all'
                        : 'flex-1 py-2 text-sm font-black rounded-md text-slate-400 hover:text-white transition-all';
                });
                if (mode === 'manual') {
                    els.boxManual.classList.remove('hidden');
                    els.boxGpx.classList.add('hidden');
                } else {
                    els.boxGpx.classList.remove('hidden');
                    els.boxManual.classList.add('hidden');
                }
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
                
                var isLight = els.mapStyleSlider && parseInt(els.mapStyleSlider.value) === 1;
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
                var cumulativeSeconds = 0;
                var tableData = [];

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
                    cumulativeSeconds += splitTimeSec;

                    var elevDisplay = (inputMode === 'gpx') ? ('+' + (elevationProfile[km - 1] || 0).toFixed(0) + 'm') : '-';
                    var tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-800 transition border-b border-slate-800';
                    tr.innerHTML =
                        '<td class="px-6 py-4 font-bold text-white">' + km + '</td>' +
                        '<td class="px-6 py-4 text-neon">' + formatTime(targetPaceSec) + '</td>' +
                        '<td class="px-6 py-4">' + formatTime(splitTimeSec) + '</td>' +
                        '<td class="px-6 py-4 text-slate-400">' + formatTime(cumulativeSeconds) + '</td>' +
                        '<td class="px-6 py-4 text-right text-slate-500">' + elevDisplay + '</td>';
                    els.tableBody.appendChild(tr);

                    tableData.push([km, formatTime(targetPaceSec), formatTime(splitTimeSec), formatTime(cumulativeSeconds), elevDisplay]);
                }
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
                // Init Map Style from LocalStorage
                var storedStyle = localStorage.getItem('paceProMapStyle') || 'dark';
                var isLight = storedStyle === 'light';
                
                if (els.mapStyleSlider) {
                    els.mapStyleSlider.value = isLight ? '1' : '0';
                    els.mapStyleLabel.textContent = isLight ? 'Light' : 'Dark';
                }

                map = L.map('pp-map').setView([-6.2088, 106.8456], 13);

                var getTileUrl = function(light) {
                    return light 
                        ? 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png' 
                        : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
                };
                
                var tileLayer = L.tileLayer(getTileUrl(isLight), {
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                    subdomains: 'abcd',
                    maxZoom: 19
                }).addTo(map);

                if (els.mapStyleSlider) {
                    els.mapStyleSlider.addEventListener('input', function (e) {
                        var isLight = parseInt(e.target.value) === 1;
                        els.mapStyleLabel.textContent = isLight ? 'Light' : 'Dark';
                        localStorage.setItem('paceProMapStyle', isLight ? 'light' : 'dark');

                        if (tileLayer) map.removeLayer(tileLayer);
                        tileLayer = L.tileLayer(getTileUrl(isLight), {
                            attribution: '&copy; OpenStreetMap &copy; CARTO',
                            subdomains: 'abcd',
                            maxZoom: 19
                        }).addTo(map);

                        if (polyline) {
                            polyline.setStyle({ color: isLight ? '#E60000' : '#CCFF00' });
                        }
                    });
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
                els.gpxLoad.addEventListener('click', loadGpxFromLibrary);
                els.generate.addEventListener('click', calculatePlan);
                els.exportCsv.addEventListener('click', exportCSV);
                els.exportImage.addEventListener('click', exportImage);
            });
        })();
    </script>
@endpush

