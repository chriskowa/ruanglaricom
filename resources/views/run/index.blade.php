@extends('layouts.pacerhub')

@php
    $pageTitle = 'Mulai Lari (Live GPS Running Tracker) | RuangLari';
    $pageDesc = 'Lacak aktivitas lari Anda secara live dengan GPS akurat, pantau pace, split kilometer, elevasi, dan simpan aktivitas lari Anda ke profil RuangLari.';
    $hideNav = true;
    $hideFooter = true;
    $hideChat = true;
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        #pacerhub-nav, #main-content-wrapper > nav, #ph-sidebar, #ph-sidebar-backdrop, #pacerhub-footer, footer, #chatbox-toggle, #ph-chatbox, .crisp-client, #crisp-chatbox {
            display: none !important;
        }
        #main-content-wrapper, #main-content-wrapper main {
            padding: 0 !important;
            margin: 0 !important;
            min-height: 100vh !important;
        }
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        #free-run-map {
            height: 100%;
            width: 100%;
            background: #0B0F17;
            z-index: 1;
        }
        .leaflet-tile {
            filter: brightness(0.92) contrast(1.08);
        }
        .free-run-solid-header {
            background-color: #070B12 !important;
            background: #070B12 !important;
            border-bottom: 1px solid #1E293B !important;
            z-index: 30 !important;
        }
        .free-run-solid-bottom {
            background-color: #0B0F17 !important;
            background: #0B0F17 !important;
            border-top: 1px solid #1E293B !important;
            z-index: 30 !important;
            opacity: 1 !important;
        }
        .free-run-metrics-box {
            background-color: #111724 !important;
            background: #111724 !important;
            border: 1px solid #1E293B !important;
        }
    </style>
@endpush

@section('content')
<!-- Fullscreen Free Run App Interface -->
<div class="fixed inset-0 z-[99990] bg-[#070B12] text-white flex flex-col select-none font-sans overflow-hidden" style="background-color: #070B12 !important;">
    
    <!-- Top Opaque Header Bar -->
    <div class="free-run-solid-header absolute top-0 left-0 right-0 z-30 px-3 py-2.5 sm:px-4 sm:py-3 bg-[#070B12] border-b border-slate-800 flex items-center justify-between gap-3 shadow-2xl" style="background-color: #070B12 !important;">
        <!-- Status & GPS Indicator -->
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <div id="run-gps-indicator" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></div>
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 block truncate" id="run-status-label">
                    Mencari GPS...
                </span>
                <h1 class="text-xs sm:text-sm font-bold text-white truncate" id="run-mode-title">
                    Lari Bebas (Free Run)
                </h1>
            </div>
        </div>

        <!-- Exit Button in Header -->
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('dashboard') }}" onclick="return confirmExitRun()" class="w-8 h-8 rounded-lg bg-[#111724] border border-slate-700 text-slate-300 hover:text-red-400 flex items-center justify-center transition cursor-pointer shadow" style="background-color: #111724 !important;" title="Keluar Lari">
                <i class="fa-solid fa-xmark text-sm"></i>
            </a>
        </div>
    </div>

    <!-- Fullscreen Map Canvas -->
    <div id="free-run-map" class="flex-1 w-full h-full relative z-10 pt-14 pb-64"></div>

    <!-- Floating Map Action Column (Mepet Kanan, Vertikal Turun ke Bawah, Top 120px) -->
    <div class="fixed z-40 flex flex-col gap-2.5 items-end pointer-events-auto" style="top: 120px !important; right: 12px !important; z-index: 99999 !important;">
        <!-- Return to Start Toggle Button -->
        <button type="button" id="btn-run-return-start" onclick="toggleRunReturnToStart()" class="w-10 h-10 rounded-xl bg-[#0B0F17] border border-slate-700 text-slate-300 hover:text-[#38bdf8] flex items-center justify-center transition cursor-pointer shadow-2xl" style="background-color: #0B0F17 !important;" title="Navigasi Kembali ke Start">
            <i id="run-return-icon" class="fa-solid fa-arrow-rotate-left text-sm"></i>
        </button>

        <!-- Recenter / Focus Button -->
        <button type="button" onclick="recenterRunMap()" class="w-10 h-10 rounded-xl bg-[#0B0F17] border border-slate-700 text-[#FC4C02] hover:text-white flex items-center justify-center transition cursor-pointer shadow-2xl" style="background-color: #0B0F17 !important;" title="Fokus Lokasi Saya">
            <i class="fa-solid fa-crosshairs text-base"></i>
        </button>

        <!-- Audio Toggle Button -->
        <button type="button" id="btn-audio-toggle" onclick="toggleRunAudio()" class="w-10 h-10 rounded-xl bg-[#0B0F17] border border-slate-700 text-slate-200 hover:text-white flex items-center justify-center transition cursor-pointer shadow-2xl" style="background-color: #0B0F17 !important;" title="Suara Navigasi">
            <i id="audio-icon" class="fa-solid fa-volume-high text-xs text-[#FC4C02]"></i>
        </button>

        <!-- Pick GPX Route Button -->
        <a href="{{ route('gpx.index') }}" class="w-10 h-10 rounded-xl bg-[#0B0F17] border border-slate-700 hover:border-slate-600 text-slate-300 hover:text-white flex items-center justify-center transition shadow-2xl" style="background-color: #0B0F17 !important;" title="Pilih Rute GPX">
            <i class="fa-solid fa-route text-xs text-[#FC4C02]"></i>
        </a>
    </div>

    <!-- Bottom Running Dashboard Panel (SOLID OPAQUE CHARCOAL) -->
    <div class="free-run-solid-bottom absolute bottom-0 left-0 right-0 z-30 bg-[#0B0F17] border-t border-slate-800 p-4 sm:p-5 shadow-2xl" style="background-color: #0B0F17 !important; opacity: 1 !important;">
        <div class="max-w-xl mx-auto space-y-4">
            
            <!-- Primary Giant Metrics Display -->
            <div class="free-run-metrics-box grid grid-cols-3 gap-3 text-center divide-x divide-slate-800 bg-[#111724] border border-slate-800 rounded-xl p-3.5 shadow-inner" style="background-color: #111724 !important;">
                <!-- Distance -->
                <div>
                    <span class="text-[10px] font-mono uppercase font-semibold text-slate-400 block tracking-wider">Jarak</span>
                    <div class="mt-0.5 flex items-baseline justify-center gap-1">
                        <span id="run-metric-dist" class="text-2xl sm:text-3xl font-black font-mono text-white tabular-nums">0.00</span>
                        <span class="text-xs font-mono text-slate-400">km</span>
                    </div>
                </div>

                <!-- Duration -->
                <div>
                    <span class="text-[10px] font-mono uppercase font-semibold text-slate-400 block tracking-wider">Waktu</span>
                    <div class="mt-0.5">
                        <span id="run-metric-time" class="text-2xl sm:text-3xl font-black font-mono text-white tabular-nums">00:00</span>
                    </div>
                </div>

                <!-- Pace -->
                <div>
                    <span class="text-[10px] font-mono uppercase font-semibold text-slate-400 block tracking-wider">Avg Pace</span>
                    <div class="mt-0.5 flex items-baseline justify-center gap-1">
                        <span id="run-metric-pace" class="text-2xl sm:text-3xl font-black font-mono text-[#FC4C02] tabular-nums">--:--</span>
                        <span class="text-xs font-mono text-slate-400">/km</span>
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics (Current Pace, Elevation, Calories) -->
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                <div class="bg-[#111724] border border-slate-800/80 rounded-lg py-2 px-1">
                    <span class="text-[10px] text-slate-400 font-mono block">Pace Live</span>
                    <span id="run-metric-live-pace" class="text-sm font-bold font-mono text-slate-200 mt-0.5 block">--:--</span>
                </div>
                <div class="bg-[#111724] border border-slate-800/80 rounded-lg py-2 px-1">
                    <span class="text-[10px] text-slate-400 font-mono block">Elevasi Gain</span>
                    <span id="run-metric-gain" class="text-sm font-bold font-mono text-slate-200 mt-0.5 block">+0m</span>
                </div>
                <div class="bg-[#111724] border border-slate-800/80 rounded-lg py-2 px-1">
                    <span class="text-[10px] text-slate-400 font-mono block">Kalori</span>
                    <span id="run-metric-cal" class="text-sm font-bold font-mono text-slate-200 mt-0.5 block">0 kcal</span>
                </div>
            </div>

            <!-- Action Controls (Start / Pause / Finish) -->
            <div class="flex items-center gap-3 pt-1">
                <!-- Pause / Resume Button -->
                <button type="button" id="btn-run-toggle-pause" onclick="toggleRunPause()" class="flex-1 py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 border border-slate-700 text-white font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow">
                    <i id="pause-icon" class="fa-solid fa-pause text-xs text-amber-400"></i>
                    <span id="pause-label">Jeda (Pause)</span>
                </button>

                <!-- Finish Run Button -->
                <button type="button" onclick="finishFreeRun()" class="flex-1 py-3 px-4 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-[#FC4C02]/20">
                    <i class="fa-solid fa-flag-checkered text-xs"></i>
                    <span>Selesai Lari</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- SOLID HIGH-CONTRAST POST-RUN SAVE MODAL -->
<!-- ========================================================================= -->
<div id="free-run-save-modal" class="fixed inset-0 z-[999999] bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 hidden select-none">
    <div class="bg-[#0F172A] border border-slate-700 rounded-2xl max-w-md w-full p-4 sm:p-5 space-y-3.5 shadow-2xl text-slate-200">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-white">Ringkasan Aktivitas Lari</h3>
                <span class="text-[11px] text-slate-400">Sesi lari Anda telah selesai direkam.</span>
            </div>
            <button type="button" onclick="closeSaveModal()" class="text-slate-400 hover:text-white p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Solid Metrics Strip -->
        <div class="bg-[#1E293B] border border-slate-700 rounded-xl p-2.5 sm:p-3 grid grid-cols-4 divide-x divide-slate-700 text-center">
            <div class="px-1">
                <span class="text-[10px] text-slate-400 font-mono font-medium block">Jarak</span>
                <span id="modal-dist" class="text-xs sm:text-sm font-bold font-mono text-white mt-0.5 block truncate">0.00 km</span>
            </div>
            <div class="px-1">
                <span class="text-[10px] text-slate-400 font-mono font-medium block">Waktu</span>
                <span id="modal-time" class="text-xs sm:text-sm font-bold font-mono text-white mt-0.5 block truncate">00:00</span>
            </div>
            <div class="px-1">
                <span class="text-[10px] text-slate-400 font-mono font-medium block">Avg Pace</span>
                <span id="modal-pace" class="text-xs sm:text-sm font-bold font-mono text-[#FC4C02] mt-0.5 block truncate">--:--</span>
            </div>
            <div class="px-1">
                <span class="text-[10px] text-slate-400 font-mono font-medium block">Gain</span>
                <span id="modal-gain" class="text-xs sm:text-sm font-bold font-mono text-white mt-0.5 block truncate">+0m</span>
            </div>
        </div>

        @auth
            <!-- Save Activity Form -->
            <form id="free-run-form" onsubmit="submitFreeRunActivity(event)" class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Aktivitas</label>
                    <input type="text" id="free-run-title" required class="w-full bg-[#1E293B] border border-slate-700 focus:border-[#FC4C02] rounded-lg p-2 text-xs text-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Lari (Opsional)</label>
                    <textarea id="free-run-notes" rows="2" placeholder="Bagaimana kondisi rute dan fisik Anda hari ini?" class="w-full bg-[#1E293B] border border-slate-700 focus:border-[#FC4C02] rounded-lg p-2 text-xs text-white outline-none resize-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="free-run-public" checked class="rounded border-slate-700 bg-slate-800 text-[#FC4C02] focus:ring-0 cursor-pointer">
                    <label for="free-run-public" class="text-xs text-slate-300 cursor-pointer">Tampilkan aktivitas ini di profil publik</label>
                </div>

                <button type="submit" id="btn-save-free-run" class="w-full py-2.5 px-4 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-[#FC4C02]/20">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                    <span>Simpan Aktivitas ke Profil</span>
                </button>
            </form>
        @else
            <!-- Guest Prompt -->
            <div class="p-3.5 rounded-xl bg-[#1E293B] border border-slate-700 space-y-2.5 text-center">
                <p class="text-xs text-slate-300 leading-relaxed">
                    Masuk ke akun RuangLari Anda untuk menyimpan aktivitas ini ke profil, menghitung total KM mingguan, dan melihat analisis split.
                </p>
                <div class="flex items-center justify-center gap-2 pt-1">
                    <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-lg bg-[#FC4C02] text-white text-xs font-bold hover:bg-[#e04300] transition">
                        Masuk / Login
                    </a>
                    <button type="button" onclick="exportRecordedTrackGpxDirectly()" class="px-3 py-2 rounded-lg border border-slate-600 text-slate-300 text-xs font-semibold hover:text-white transition cursor-pointer">
                        Download GPX Saja
                    </button>
                </div>
            </div>
        @endauth

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let runMap = null;
        let runPolyline = null;
        let runUserMarker = null;
        let runAccuracyCircle = null;
        let runWatchId = null;
        let runWakeLock = null;

        let runIsPaused = false;
        let runStartTime = null;
        let runElapsedSec = 0;
        let runTimerInterval = null;
        let runAudioEnabled = true;
        let runLastPassedKm = 0;
        let runLastSpokenTime = 0;

        let runReturnMode = false;
        let runReturnPolyline = null;
        let runBreadcrumbs = []; // [{lat, lng, ele, dist, time}]
        let runTotalDistanceKm = 0;
        let runTotalElevationGainM = 0;
        let runSplits = [];
        let runCurrentUserPos = null;

        document.addEventListener('DOMContentLoaded', function() {
            initFreeRunMap();
            startFreeRunTracking();
        });

        function initFreeRunMap() {
            const tileUrl = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

            runMap = L.map('free-run-map', {
                zoomControl: false,
                scrollWheelZoom: true,
                dragging: true,
            }).setView([-6.2088, 106.8456], 16);

            L.tileLayer(tileUrl, { maxZoom: 19, subdomains: 'abcd' }).addTo(runMap);

            // Breadcrumb Polyline (#FC4C02)
            runPolyline = L.polyline([], {
                color: '#FC4C02',
                weight: 5,
                opacity: 0.95,
                lineCap: 'round',
                lineJoin: 'round',
            }).addTo(runMap);

            // User Marker (Blue Pulse)
            const userIcon = L.divIcon({
                className: 'free-run-dot',
                html: `
                    <div class="relative flex items-center justify-center w-7 h-7">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-60"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow-lg"></span>
                    </div>
                `,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            runUserMarker = L.marker([-6.2088, 106.8456], { icon: userIcon, zIndexOffset: 2000 }).addTo(runMap);
            runAccuracyCircle = L.circle([-6.2088, 106.8456], { radius: 15, color: '#3b82f6', fillOpacity: 0.12, weight: 1 }).addTo(runMap);
        }

        function startFreeRunTracking() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung sensor GPS.');
                return;
            }

            requestWakeLock();
            runStartTime = Date.now();
            runIsPaused = false;

            if (runTimerInterval) clearInterval(runTimerInterval);
            runTimerInterval = setInterval(updateRunTimer, 1000);

            speakRunVoice('Sesi lari bebas dimulai. Selamat berlari!');

            runWatchId = navigator.geolocation.watchPosition(
                handleRunGpsSuccess,
                handleRunGpsError,
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 10000
                }
            );
        }

        // GPS Kalman Filter for smooth trajectory without spikes
        class GpsKalmanFilter {
            constructor(decay = 3) {
                this.variance = -1;
                this.minAccuracy = 1;
                this.decay = decay;
                this.lat = 0;
                this.lng = 0;
                this.timestamp = 0;
            }

            process(rawLat, rawLng, accuracy, timestamp) {
                if (accuracy < this.minAccuracy) accuracy = this.minAccuracy;

                if (this.variance < 0) {
                    this.timestamp = timestamp;
                    this.lat = rawLat;
                    this.lng = rawLng;
                    this.variance = accuracy * accuracy;
                    return { lat: rawLat, lng: rawLng };
                }

                const durationMs = timestamp - this.timestamp;
                if (durationMs > 0) {
                    this.variance += (durationMs / 1000) * this.decay * this.decay;
                    this.timestamp = timestamp;
                }

                const k = this.variance / (this.variance + accuracy * accuracy);
                this.lat += k * (rawLat - this.lat);
                this.lng += k * (rawLng - this.lng);
                this.variance = (1 - k) * this.variance;

                return { lat: this.lat, lng: this.lng };
            }
        }

        const runKalmanFilter = new GpsKalmanFilter(3);

        function handleRunGpsSuccess(pos) {
            if (runIsPaused) return;

            const rawLat = pos.coords.latitude;
            const rawLng = pos.coords.longitude;
            const ele = pos.coords.altitude || null;
            const accuracy = pos.coords.accuracy || 10;
            const speedMps = pos.coords.speed;
            const now = Date.now();

            // Reject poor accuracy GPS points (> 30 meters) when tracking already started
            if (accuracy > 30 && runBreadcrumbs.length > 0) {
                return;
            }

            // Apply Kalman smoothing
            const smoothed = runKalmanFilter.process(rawLat, rawLng, accuracy, now);
            const lat = smoothed.lat;
            const lng = smoothed.lng;

            runCurrentUserPos = { lat, lng };

            // Update GPS indicator
            const gpsInd = document.getElementById('run-gps-indicator');
            const statusLbl = document.getElementById('run-status-label');
            if (gpsInd) {
                gpsInd.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shrink-0';
            }
            if (statusLbl) {
                statusLbl.textContent = 'GPS Terhubung (Akurat)';
                statusLbl.className = 'text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-400 block truncate';
            }

            // Update Marker & Map
            if (runUserMarker) runUserMarker.setLatLng([lat, lng]);
            if (runAccuracyCircle) {
                runAccuracyCircle.setLatLng([lat, lng]);
                runAccuracyCircle.setRadius(Math.min(accuracy, 25));
            }

            // Record Breadcrumb Point with Anti-Drift & Speed Rejection Filter
            if (runBreadcrumbs.length > 0) {
                const prev = runBreadcrumbs[runBreadcrumbs.length - 1];
                const segmentDist = calculateHaversine(prev.lat, prev.lng, lat, lng);
                const timeDiffSec = (now - prev.time) / 1000;
                
                // Speed anomaly check: reject if speed > 40 km/h (teleportation/glitch)
                const segmentSpeedKmh = timeDiffSec > 0 ? (segmentDist / (timeDiffSec / 3600)) : 0;
                if (segmentSpeedKmh > 40) {
                    return; // Ignore glitch jump
                }

                // Only record if moved at least 2.5 meters
                if (segmentDist > 0.0025) {
                    runTotalDistanceKm += segmentDist;
                    if (ele && prev.ele && ele > prev.ele) {
                        runTotalElevationGainM += (ele - prev.ele);
                    }
                    runBreadcrumbs.push({ lat, lng, ele, dist: runTotalDistanceKm, time: now });
                    if (runPolyline) runPolyline.addLatLng([lat, lng]);

                    // Check for KM Split Milestone Announcement
                    const curKmWhole = Math.floor(runTotalDistanceKm);
                    if (curKmWhole > runLastPassedKm && curKmWhole > 0) {
                        runLastPassedKm = curKmWhole;
                        const avgPace = runElapsedSec / runTotalDistanceKm;
                        speakRunVoice(`Kilometer ${curKmWhole}. Pace rata-rata ${formatPaceTime(avgPace)} per kilometer.`);
                        vibrateRun([150, 100]);
                    }
                }
            } else {
                runBreadcrumbs.push({ lat, lng, ele, dist: 0, time: now });
                if (runPolyline) runPolyline.addLatLng([lat, lng]);
                if (runMap) runMap.setView([lat, lng], 17);
            }

            // Handle Return to Start guideline polyline & distance announcement
            if (runReturnMode && runBreadcrumbs.length > 0) {
                const startPt = runBreadcrumbs[0];
                const distToStartKm = calculateHaversine(lat, lng, startPt.lat, startPt.lng);
                const distToStartM = distToStartKm * 1000;

                if (!runReturnPolyline && runMap) {
                    runReturnPolyline = L.polyline([[lat, lng], [startPt.lat, startPt.lng]], {
                        color: '#38bdf8',
                        dashArray: '8, 8',
                        weight: 4,
                        opacity: 0.95
                    }).addTo(runMap);
                } else if (runReturnPolyline) {
                    runReturnPolyline.setLatLngs([[lat, lng], [startPt.lat, startPt.lng]]);
                }

                if (statusLbl) {
                    statusLbl.textContent = `Menuju Titik Start • ${distToStartKm.toFixed(2)} km`;
                    statusLbl.className = 'text-[10px] font-mono font-bold uppercase tracking-wider text-[#38bdf8] block';
                }

                if (distToStartM <= 30) {
                    speakRunVoice('Anda telah tiba kembali di titik start.');
                    vibrateRun([200, 100, 200]);
                    toggleRunReturnToStart();
                }
            }

            // Update UI Metrics
            updateDashboardMetrics(speedMps);

            if (runMap) {
                runMap.panTo([lat, lng], { animate: true, duration: 0.8 });
            }
        }

        function toggleRunReturnToStart() {
            if (runBreadcrumbs.length === 0) {
                alert('Belum ada titik start yang terekam.');
                return;
            }

            runReturnMode = !runReturnMode;
            const btn = document.getElementById('btn-run-return-start');
            const icon = document.getElementById('run-return-icon');

            if (runReturnMode) {
                if (btn) btn.classList.add('text-[#38bdf8]', 'border-[#38bdf8]');
                const startPt = runBreadcrumbs[0];
                const cur = runCurrentUserPos || startPt;
                const dKm = calculateHaversine(cur.lat, cur.lng, startPt.lat, startPt.lng);
                speakRunVoice(`Navigasi kembali ke titik start diaktifkan. Jarak ${dKm.toFixed(1)} kilometer.`);
                if (runCurrentUserPos) {
                    handleRunGpsSuccess({ coords: { latitude: runCurrentUserPos.lat, longitude: runCurrentUserPos.lng } });
                }
            } else {
                if (btn) btn.classList.remove('text-[#38bdf8]', 'border-[#38bdf8]');
                if (runReturnPolyline && runMap) {
                    runMap.removeLayer(runReturnPolyline);
                    runReturnPolyline = null;
                }
                const statusLbl = document.getElementById('run-status-label');
                if (statusLbl) {
                    statusLbl.textContent = 'GPS Terhubung (Akurat)';
                    statusLbl.className = 'text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-400 block';
                }
                speakRunVoice('Navigasi kembali ke start dinonaktifkan.');
            }
        }

        function handleRunGpsError(err) {
            console.warn('GPS Error:', err);
            const gpsInd = document.getElementById('run-gps-indicator');
            const statusLbl = document.getElementById('run-status-label');
            if (gpsInd) gpsInd.className = 'w-3 h-3 rounded-full bg-amber-500 animate-pulse shrink-0';
            if (statusLbl) {
                statusLbl.textContent = 'Mencari Sinyal GPS...';
                statusLbl.className = 'text-[10px] font-mono font-bold uppercase tracking-wider text-amber-400 block';
            }
        }

        function updateRunTimer() {
            if (runIsPaused || !runStartTime) return;
            runElapsedSec++;
            const timeEl = document.getElementById('run-metric-time');
            if (timeEl) timeEl.textContent = formatDurationTime(runElapsedSec);
            updateDashboardMetrics();
        }

        function updateDashboardMetrics(speedMps) {
            const distEl = document.getElementById('run-metric-dist');
            const paceEl = document.getElementById('run-metric-pace');
            const livePaceEl = document.getElementById('run-metric-live-pace');
            const gainEl = document.getElementById('run-metric-gain');
            const calEl = document.getElementById('run-metric-cal');

            if (distEl) distEl.textContent = runTotalDistanceKm.toFixed(2);

            const avgPaceSec = runTotalDistanceKm > 0 && runElapsedSec > 0 ? (runElapsedSec / runTotalDistanceKm) : 0;
            if (paceEl) paceEl.textContent = formatPaceTime(avgPaceSec);

            if (livePaceEl) {
                if (speedMps && speedMps > 0.5) {
                    livePaceEl.textContent = formatPaceTime(1000 / speedMps) + ' /km';
                } else if (avgPaceSec > 0) {
                    livePaceEl.textContent = formatPaceTime(avgPaceSec) + ' /km';
                } else {
                    livePaceEl.textContent = '--:--';
                }
            }

            if (gainEl) gainEl.textContent = '+' + Math.round(runTotalElevationGainM) + 'm';
            if (calEl) calEl.textContent = Math.round(runTotalDistanceKm * 62) + ' kcal';
        }

        function toggleRunPause() {
            runIsPaused = !runIsPaused;
            const icon = document.getElementById('pause-icon');
            const label = document.getElementById('pause-label');

            if (runIsPaused) {
                if (icon) icon.className = 'fa-solid fa-play text-xs text-emerald-400';
                if (label) label.textContent = 'Lanjut (Resume)';
                speakRunVoice('Lari dijeda.');
            } else {
                if (icon) icon.className = 'fa-solid fa-pause text-xs text-amber-400';
                if (label) label.textContent = 'Jeda (Pause)';
                speakRunVoice('Melanjutkan lari.');
            }
        }

        function recenterRunMap() {
            if (runCurrentUserPos && runMap) {
                runMap.setView([runCurrentUserPos.lat, runCurrentUserPos.lng], 17, { animate: true });
            }
        }

        function finishFreeRun() {
            if (!confirm('Apakah Anda ingin menyelesaikan sesi lari dan menyimpan aktivitas?')) return;

            runIsPaused = true;
            if (runWatchId) {
                navigator.geolocation.clearWatch(runWatchId);
                runWatchId = null;
            }
            if (runTimerInterval) {
                clearInterval(runTimerInterval);
                runTimerInterval = null;
            }
            releaseWakeLock();

            // Calculate Final Metrics
            const finalDist = Math.max(runTotalDistanceKm, 0.01);
            const avgPaceSec = finalDist > 0 && runElapsedSec > 0 ? Math.round(runElapsedSec / finalDist) : 0;

            // Generate Splits
            runSplits = [];
            const wholeKm = Math.ceil(finalDist);
            let cumSec = 0;
            for (let k = 1; k <= wholeKm; k++) {
                const segDist = k > finalDist ? (finalDist - (k - 1)) : 1;
                const splitSec = Math.round(avgPaceSec * segDist);
                cumSec += splitSec;
                runSplits.push({
                    km: k,
                    pace: formatPaceTime(avgPaceSec) + ' /km',
                    split: formatDurationTime(splitSec),
                    cum: formatDurationTime(cumSec),
                    elev: '-'
                });
            }

            // Populate Modal Fields
            document.getElementById('modal-dist').textContent = finalDist.toFixed(2) + ' km';
            document.getElementById('modal-time').textContent = formatDurationTime(runElapsedSec);
            document.getElementById('modal-pace').textContent = formatPaceTime(avgPaceSec) + ' /km';
            document.getElementById('modal-gain').textContent = '+' + Math.round(runTotalElevationGainM) + 'm';

            const defaultTitle = 'Lari ' + getDayTimeGreeting() + ' (' + finalDist.toFixed(2) + ' km)';
            const titleInput = document.getElementById('free-run-title');
            if (titleInput) titleInput.value = defaultTitle;

            // Show Modal with top z-index
            const modal = document.getElementById('free-run-save-modal');
            if (modal) modal.classList.remove('hidden');

            speakRunVoice('Lari selesai. Kerja bagus!');
        }

        function closeSaveModal() {
            const modal = document.getElementById('free-run-save-modal');
            if (modal) modal.classList.add('hidden');
        }

        function submitFreeRunActivity(e) {
            e.preventDefault();

            const title = document.getElementById('free-run-title')?.value || 'Lari Bebas';
            const notes = document.getElementById('free-run-notes')?.value || '';
            const isPublic = document.getElementById('free-run-public')?.checked ?? true;
            const finalDist = Math.max(runTotalDistanceKm, 0.01);
            const avgPaceSec = finalDist > 0 && runElapsedSec > 0 ? Math.round(runElapsedSec / finalDist) : 0;

            const submitBtn = document.getElementById('btn-save-free-run');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Menyimpan...';
            }

            const payload = {
                title: title,
                distance_km: finalDist,
                moving_time_s: Math.max(runElapsedSec, 1),
                elapsed_time_s: Math.max(runElapsedSec, 1),
                avg_pace_sec: avgPaceSec,
                elevation_gain_m: runTotalElevationGainM,
                coordinates_json: runBreadcrumbs,
                splits_json: runSplits,
                notes: notes,
                is_public: isPublic ? 1 : 0
            };

            fetch("{{ route('activities.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Gagal menyimpan aktivitas.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up text-xs"></i> Simpan Aktivitas ke Profil';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan aktivitas.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up text-xs"></i> Simpan Aktivitas ke Profil';
                }
            });
        }

        function exportRecordedTrackGpxDirectly() {
            let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<gpx version="1.1" creator="RuangLari.com"><trk><name>Lari RuangLari</name><trkseg>\n';
            runBreadcrumbs.forEach(p => {
                xml += `  <trkpt lat="${p.lat}" lon="${p.lng}"><ele>${p.ele || 0}</ele></trkpt>\n`;
            });
            xml += '</trkseg></trk></gpx>';

            const blob = new Blob([xml], { type: 'application/gpx+xml;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Aktivitas_Lari_${Date.now()}.gpx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function confirmExitRun() {
            if (runTotalDistanceKm > 0.05) {
                return confirm('Sesi lari sedang berlangsung. Apakah Anda yakin ingin keluar tanpa menyimpan?');
            }
            return true;
        }

        function toggleRunAudio() {
            runAudioEnabled = !runAudioEnabled;
            const icon = document.getElementById('audio-icon');
            if (icon) {
                if (runAudioEnabled) {
                    icon.className = 'fa-solid fa-volume-high text-xs text-[#FC4C02]';
                    speakRunVoice('Suara aktif.');
                } else {
                    icon.className = 'fa-solid fa-volume-xmark text-xs text-slate-500';
                    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                }
            }
        }

        function speakRunVoice(text) {
            if (!runAudioEnabled) return;
            const now = Date.now();
            if (now - runLastSpokenTime < 2000) return;
            runLastSpokenTime = now;

            try {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    utterance.rate = 1.05;
                    window.speechSynthesis.speak(utterance);
                }
            } catch (e) {}
        }

        function vibrateRun(pattern) {
            try {
                if (navigator.vibrate) navigator.vibrate(pattern);
            } catch (e) {}
        }

        async function requestWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    runWakeLock = await navigator.wakeLock.request('screen');
                }
            } catch (e) {}
        }

        function releaseWakeLock() {
            if (runWakeLock) {
                runWakeLock.release().catch(() => {});
                runWakeLock = null;
            }
        }

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

        function getDayTimeGreeting() {
            const hr = new Date().getHours();
            if (hr >= 4 && hr < 11) return 'Pagi';
            if (hr >= 11 && hr < 15) return 'Siang';
            if (hr >= 15 && hr < 18) return 'Sore';
            return 'Malam';
        }
    </script>
@endpush
