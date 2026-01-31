<!doctype html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prediksi Waktu - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <a href="{{ route('events.show', $event->slug) }}" class="text-slate-400 hover:text-white text-sm font-semibold">← Kembali ke Event</a>
                <h1 class="text-3xl md:text-4xl font-black mt-2">Prediksi Waktu</h1>
                <p class="text-slate-400 mt-1">{{ $event->name }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
            <div class="lg:col-span-1">
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5">
                    <h2 class="text-lg font-black">Input</h2>

                    <div class="mt-4">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori</label>
                        <select id="categorySelect" class="mt-2 w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-semibold">
                            <option value="" disabled selected>Pilih kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-gpx="{{ $cat->masterGpx ? route('tools.pace-pro.gpx', $cat->masterGpx) : '' }}" data-distance="{{ (float) ($cat->distance_km ?? 0) }}">
                                    {{ $cat->name }} ({{ (float) ($cat->distance_km ?? 0) }} KM)
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-2">GPX harus terhubung ke kategori untuk analisis rute.</p>
                    </div>

                    <div class="mt-5">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cuaca</label>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="weather" value="panas" class="peer sr-only" checked>
                                <div class="px-4 py-3 rounded-xl border border-slate-800 bg-slate-950 peer-checked:border-yellow-400 peer-checked:bg-yellow-500/10 font-bold">Panas ☀️</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="weather" value="dingin" class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border border-slate-800 bg-slate-950 peer-checked:border-cyan-400 peer-checked:bg-cyan-500/10 font-bold">Dingin ❄️</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="weather" value="hujan" class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border border-slate-800 bg-slate-950 peer-checked:border-blue-400 peer-checked:bg-blue-500/10 font-bold">Hujan 🌧️</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="weather" value="gerimis" class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border border-slate-800 bg-slate-950 peer-checked:border-sky-400 peer-checked:bg-sky-500/10 font-bold">Gerimis 🌦️</div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">PB (Jam / Menit / Detik)</label>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <input id="pbH" type="number" min="0" max="23" value="0" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-3 text-white font-semibold" placeholder="Jam">
                            <input id="pbM" type="number" min="0" max="59" value="0" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-3 text-white font-semibold" placeholder="Menit">
                            <input id="pbS" type="number" min="0" max="59" value="0" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-3 text-white font-semibold" placeholder="Detik">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal PB (3 bulan terakhir)</label>
                        <input id="pbDate" type="date" class="mt-2 w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-semibold">
                    </div>

                    <p id="predictError" class="mt-4 text-red-300 text-sm hidden"></p>

                    <button id="predictBtn" type="button" class="mt-5 w-full px-5 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black transition">
                        Prediksi Waktu
                    </button>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5">
                    <h2 class="text-lg font-black">Peta Rute</h2>
                    <div id="routeMap" class="mt-4 w-full h-[360px] rounded-xl overflow-hidden border border-slate-800"></div>
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">Jarak</div>
                            <div class="text-white font-black mt-1" id="routeDistance">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">Elev Gain</div>
                            <div class="text-white font-black mt-1" id="routeGain">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">Min/Max</div>
                            <div class="text-white font-black mt-1" id="routeMinMax">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">Terrain</div>
                            <div class="text-white font-black mt-1" id="routeTerrain">-</div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5">
                    <h2 class="text-lg font-black">Profil Elevasi</h2>
                    <div class="mt-4">
                        <canvas id="elevChart" height="120"></canvas>
                    </div>
                </div>

                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5">
                    <h2 class="text-lg font-black">Hasil Prediksi</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                            <div class="text-slate-400 text-xs font-bold uppercase">Optimis</div>
                            <div class="text-white font-black text-2xl mt-2" id="predOptimistic">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                            <div class="text-slate-400 text-xs font-bold uppercase">Realistis</div>
                            <div class="text-white font-black text-2xl mt-2" id="predRealistic">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                            <div class="text-slate-400 text-xs font-bold uppercase">Pesimis</div>
                            <div class="text-white font-black text-2xl mt-2" id="predPessimistic">-</div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">VDOT</div>
                            <div class="text-white font-black mt-1" id="vdotVal">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">Confidence</div>
                            <div class="text-white font-black mt-1" id="confidenceVal">-</div>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-bold uppercase">Penalty</div>
                            <div class="text-white font-black mt-1" id="penaltyVal">-</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-slate-400 text-xs font-bold uppercase">Saran Pacing</div>
                        <div class="mt-2 text-slate-200 font-semibold" id="strategyText">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const predictUrl = @json(route('events.prediction.predict', $event->slug));

            const categorySelect = document.getElementById('categorySelect');
            const predictBtn = document.getElementById('predictBtn');
            const errEl = document.getElementById('predictError');

            const routeDistanceEl = document.getElementById('routeDistance');
            const routeGainEl = document.getElementById('routeGain');
            const routeMinMaxEl = document.getElementById('routeMinMax');
            const routeTerrainEl = document.getElementById('routeTerrain');

            const predOptimistic = document.getElementById('predOptimistic');
            const predRealistic = document.getElementById('predRealistic');
            const predPessimistic = document.getElementById('predPessimistic');
            const vdotVal = document.getElementById('vdotVal');
            const confidenceVal = document.getElementById('confidenceVal');
            const penaltyVal = document.getElementById('penaltyVal');
            const strategyText = document.getElementById('strategyText');

            const map = L.map('routeMap', { zoomControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            map.setView([-6.2, 106.8], 11);

            let poly = null;
            let chart = null;

            function showError(msg) {
                if (!errEl) return;
                errEl.textContent = msg;
                errEl.classList.remove('hidden');
            }

            function clearError() {
                if (!errEl) return;
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }

            function haversineKm(lat1, lon1, lat2, lon2) {
                const R = 6371;
                const toRad = (v) => v * Math.PI / 180;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            function parseGpxText(gpxText) {
                const parser = new DOMParser();
                const xml = parser.parseFromString(gpxText, 'text/xml');
                const trkpts = Array.from(xml.getElementsByTagName('trkpt'));
                const pts = (trkpts.length > 1 ? trkpts : Array.from(xml.getElementsByTagName('rtept')))
                    .map((pt) => {
                        const lat = parseFloat(pt.getAttribute('lat'));
                        const lon = parseFloat(pt.getAttribute('lon'));
                        const eleNode = pt.getElementsByTagName('ele')[0];
                        const ele = eleNode ? parseFloat(eleNode.textContent) : null;
                        return { lat, lon, ele };
                    })
                    .filter(p => Number.isFinite(p.lat) && Number.isFinite(p.lon));

                let dist = 0;
                let gain = 0;
                let loss = 0;
                let minEle = null;
                let maxEle = null;

                const samples = [];
                for (let i = 0; i < pts.length; i++) {
                    const p = pts[i];
                    if (i > 0) {
                        const prev = pts[i-1];
                        dist += haversineKm(prev.lat, prev.lon, p.lat, p.lon);
                        if (p.ele !== null && prev.ele !== null) {
                            const d = p.ele - prev.ele;
                            if (d > 0) gain += d;
                            if (d < 0) loss += Math.abs(d);
                        }
                    }
                    if (p.ele !== null) {
                        minEle = (minEle === null) ? p.ele : Math.min(minEle, p.ele);
                        maxEle = (maxEle === null) ? p.ele : Math.max(maxEle, p.ele);
                    }
                    samples.push({ km: dist, ele: p.ele, lat: p.lat, lon: p.lon });
                }

                return {
                    points: pts,
                    samples,
                    distanceKm: dist,
                    gainM: Math.round(gain),
                    lossM: Math.round(loss),
                    minEle: (minEle === null ? null : Math.round(minEle)),
                    maxEle: (maxEle === null ? null : Math.round(maxEle)),
                };
            }

            function downsample(arr, maxN) {
                if (arr.length <= maxN) return arr;
                const step = Math.ceil(arr.length / maxN);
                const out = [];
                for (let i = 0; i < arr.length; i += step) out.push(arr[i]);
                if (out[out.length - 1] !== arr[arr.length - 1]) out.push(arr[arr.length - 1]);
                return out;
            }

            function renderRoute(data) {
                if (poly) map.removeLayer(poly);
                const latlngs = data.points.map(p => [p.lat, p.lon]);
                if (latlngs.length > 1) {
                    poly = L.polyline(latlngs, { color: '#facc15', weight: 4 }).addTo(map);
                    map.fitBounds(poly.getBounds(), { padding: [20, 20] });
                }

                if (routeDistanceEl) routeDistanceEl.textContent = data.distanceKm ? data.distanceKm.toFixed(2) + ' km' : '-';
                if (routeGainEl) routeGainEl.textContent = data.gainM ? '+' + data.gainM + ' m' : '-';
                if (routeMinMaxEl) routeMinMaxEl.textContent = (data.minEle !== null && data.maxEle !== null) ? (data.minEle + ' / ' + data.maxEle + ' m') : '-';

                const gainPerKm = data.distanceKm > 0 ? (data.gainM / data.distanceKm) : 0;
                const terrain = gainPerKm > 35 ? 'Berbukit' : (gainPerKm > 20 ? 'Rolling' : 'Datar');
                if (routeTerrainEl) routeTerrainEl.textContent = terrain;
            }

            function renderChart(data) {
                const canvas = document.getElementById('elevChart');
                if (!canvas) return;

                const sample = downsample(data.samples.filter(s => s.ele !== null), 1000);
                const labels = sample.map(s => s.km.toFixed(2));
                const elev = sample.map(s => s.ele);

                if (chart) chart.destroy();
                chart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Elevasi (m)',
                            data: elev,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.15)',
                            fill: true,
                            pointRadius: 0,
                            tension: 0.25,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: '#94a3b8', maxTicksLimit: 10 }, grid: { color: 'rgba(148,163,184,0.1)' } },
                            y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.1)' } }
                        }
                    }
                });
            }

            async function loadGpx(url) {
                if (!url) {
                    showError('Kategori ini belum memiliki GPX.');
                    return;
                }
                clearError();
                const res = await fetch(url, { headers: { 'Accept': 'application/xml' } });
                const text = await res.text();
                const parsed = parseGpxText(text);
                renderRoute(parsed);
                renderChart(parsed);
            }

            categorySelect?.addEventListener('change', async () => {
                const opt = categorySelect.options[categorySelect.selectedIndex];
                const gpxUrl = opt?.dataset?.gpx || '';
                await loadGpx(gpxUrl);
            });

            predictBtn?.addEventListener('click', async () => {
                clearError();
                const categoryId = categorySelect?.value;
                if (!categoryId) {
                    showError('Pilih kategori terlebih dahulu.');
                    return;
                }

                const weather = document.querySelector('input[name=\"weather\"]:checked')?.value || 'panas';
                const pbH = document.getElementById('pbH')?.value || '0';
                const pbM = document.getElementById('pbM')?.value || '0';
                const pbS = document.getElementById('pbS')?.value || '0';
                const pbDate = document.getElementById('pbDate')?.value || '';

                if (!pbDate) {
                    showError('Tanggal PB wajib diisi (maksimal 3 bulan terakhir).');
                    return;
                }

                const fd = new FormData();
                fd.append('category_id', categoryId);
                fd.append('weather', weather);
                fd.append('pb_h', pbH);
                fd.append('pb_m', pbM);
                fd.append('pb_s', pbS);
                fd.append('pb_date', pbDate);

                try {
                    const res = await fetch(predictUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: fd,
                        credentials: 'same-origin'
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) {
                        const msg = (data && data.message) ? data.message : 'Gagal menghitung prediksi.';
                        showError(msg);
                        return;
                    }

                    const r = data.result;
                    predOptimistic.textContent = r.prediction.optimistic;
                    predRealistic.textContent = r.prediction.realistic;
                    predPessimistic.textContent = r.prediction.pessimistic;
                    vdotVal.textContent = String(r.vdot);
                    confidenceVal.textContent = String(r.confidence);
                    penaltyVal.textContent = Math.round((r.penalties.total || 0) * 100) + '%';
                    strategyText.textContent = r.strategy || '-';
                } catch (e) {
                    showError('Gagal memproses prediksi. Coba lagi.');
                }
            });
        })();
    </script>
</body>
</html>

