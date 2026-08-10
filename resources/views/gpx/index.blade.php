@extends('layouts.pacerhub')

@section('title', 'Database Rute GPX - Ruang Lari')
@section('meta_description', 'Direktori & database rute GPX lari terlengkap di Indonesia. Temukan rute lari di Jakarta, Bandung, Surabaya, Bali, dan kota lainnya. Unduh gratis file GPX untuk Garmin, Coros, Suunto, & Strava.')
@section('meta_keywords', 'database gpx, rute gpx lari, download file gpx, rute lari jakarta, gpx garmin, gpx coros, gpx strava, gpx running indonesia')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        .gpx-preview-map {
            height: 180px;
            width: 100%;
            background: #090e17;
            border-radius: 12px;
            overflow: hidden;
            z-index: 1;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 bg-[#060a17] text-white">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 bg-slate-900/60 border border-slate-800 p-6 md:p-8 rounded-3xl backdrop-blur-md">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs font-bold text-slate-300 uppercase tracking-widest mb-3">
                    <i class="fa-solid fa-map-location-dot text-slate-400"></i>
                    <span>Direktori GPX Lari</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter text-white uppercase">
                    DATABASE <span class="text-neon">RUTE GPX</span>
                </h1>
                <p class="text-slate-400 mt-2 max-w-2xl text-sm leading-relaxed">
                    Eksplorasi dan unduh kumpulan file GPX rute lari dari berbagai kota di Indonesia. Dapatkan poin runner dengan mengunggah rute lari Anda sendiri!
                </p>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('tools.index') }}" class="px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs transition-all flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-slate-400"></i>
                    <span>Tools Lari</span>
                </a>
                <a href="{{ route('tools.buat-rute-lari') }}?open_gpx_modal=1" class="px-5 py-3 rounded-2xl bg-neon text-dark hover:bg-white font-black text-xs transition-all shadow-lg shadow-neon/15 flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-dark"></i>
                    <span>Submit GPX</span>
                </a>
            </div>
        </div>

        <!-- Location Detection Banner -->
        <div id="gpx-geo-notice-bar" class="hidden mb-6 p-4 rounded-2xl bg-neon/10 border border-neon/30 text-xs font-semibold text-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-neon text-sm"></i>
                <span>Lokasi Anda terdeteksi di <strong id="gpx-detected-city-name" class="text-white">...</strong></span>
            </div>
            <button id="btn-apply-detected-city" type="button" class="px-4 py-2 bg-neon text-dark font-black rounded-xl text-xs hover:bg-white transition flex items-center justify-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Filter Rute Kota Ini</span>
            </button>
        </div>

        <!-- Filter Bar -->
        <form id="form-gpx-filter" method="GET" action="{{ route('gpx.index') }}" class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl mb-8 space-y-4 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Rute</label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Judul rute / kata kunci..." class="w-full bg-slate-950/70 border border-slate-800 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 transition">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <!-- City Filter -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kota</label>
                        <button id="btn-detect-user-city" type="button" class="text-[10px] text-neon hover:underline font-bold flex items-center gap-1">
                            <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                            <span>Lokasi Saya</span>
                        </button>
                    </div>
                    <select id="select-gpx-city" name="city" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" @selected(request('city') == $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Distance Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jarak</label>
                    <select name="distance" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="">Semua Jarak</option>
                        <option value="under_5k" @selected(request('distance') == 'under_5k')>&lt; 5 KM</option>
                        <option value="5k_10k" @selected(request('distance') == '5k_10k')>5 KM - 10 KM</option>
                        <option value="10k_21k" @selected(request('distance') == '10k_21k')>10 KM - 21 KM (HM)</option>
                        <option value="over_21k" @selected(request('distance') == 'over_21k')>&gt; 21 KM (FM / Ultra)</option>
                    </select>
                </div>

                <!-- Elevation Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Elevasi (EG)</label>
                    <select name="elevation" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="">Semua Elevasi</option>
                        <option value="flat" @selected(request('elevation') == 'flat')>Datar (&lt; 100m)</option>
                        <option value="hilly" @selected(request('elevation') == 'hilly')>Sedang (100m - 300m)</option>
                        <option value="mountainous" @selected(request('elevation') == 'mountainous')>Tinggi (&gt; 300m)</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Urutkan</label>
                    <select name="sort" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="latest" @selected(request('sort', 'latest') == 'latest')>Terbaru</option>
                        <option value="distance_desc" @selected(request('sort') == 'distance_desc')>Jarak Terjauh</option>
                        <option value="distance_asc" @selected(request('sort') == 'distance_asc')>Jarak Terpendek</option>
                        <option value="elevation_desc" @selected(request('sort') == 'elevation_desc')>Elevasi Tertinggi</option>
                    </select>
                </div>

            </div>

            <!-- Submit Filter Action -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                <div class="text-xs text-slate-400 font-semibold">
                    Menampilkan <span class="text-white font-bold">{{ $items->total() }}</span> rute GPX dipublish
                </div>
                <div class="flex items-center gap-2">
                    @if(request()->anyFilled(['q', 'city', 'distance', 'elevation', 'sort']))
                        <a href="{{ route('gpx.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                            Reset Filter
                        </a>
                    @endif
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-neon text-dark font-bold text-xs hover:bg-white transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- GPX Grid -->
        @if($items->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($items as $item)
                    <div class="bg-slate-900/60 border border-slate-800 hover:border-slate-700 rounded-2xl p-4 flex flex-col justify-between transition-all group">
                        
                        <div>
                            <!-- Card Header: Title & City Tag -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="font-bold text-white text-base group-hover:text-neon transition-colors line-clamp-1">
                                        <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}">{{ $item->title }}</a>
                                    </h3>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-0.5">
                                        <i class="fa-solid fa-location-dot text-slate-500 text-[11px]"></i>
                                        <span>{{ $item->city ?? 'Indonesia' }}</span>
                                    </div>
                                </div>
                                @if($item->event)
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 border border-slate-700 text-[10px] text-slate-300 font-semibold whitespace-nowrap">
                                        {{ Str::limit($item->event->name, 16) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Map Preview Container -->
                            <div id="gpx-map-{{ $item->id }}" class="gpx-preview-map mb-4 border border-slate-800/80" data-coords='@json($item->coordinates_json)'></div>

                            <!-- Stats Badges -->
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <div class="bg-slate-950/60 border border-slate-800/80 p-2 rounded-xl text-center">
                                    <div class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Jarak</div>
                                    <div class="text-xs font-black text-white mt-0.5">
                                        {{ $item->distance_km ? number_format((float)$item->distance_km, 2) . ' km' : '-' }}
                                    </div>
                                </div>
                                <div class="bg-slate-950/60 border border-slate-800/80 p-2 rounded-xl text-center">
                                    <div class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Elev. Gain</div>
                                    <div class="text-xs font-black text-emerald-400 mt-0.5">
                                        {{ $item->elevation_gain_m !== null ? '+' . $item->elevation_gain_m . 'm' : '-' }}
                                    </div>
                                </div>
                                <div class="bg-slate-950/60 border border-slate-800/80 p-2 rounded-xl text-center">
                                    <div class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Elev. Loss</div>
                                    <div class="text-xs font-black text-rose-400 mt-0.5">
                                        {{ $item->elevation_loss_m !== null ? '-' . $item->elevation_loss_m . 'm' : '-' }}
                                    </div>
                                </div>
                            </div>

                            @if($item->notes)
                                <p class="text-xs text-slate-400 line-clamp-2 mb-4 italic bg-slate-950/30 p-2.5 rounded-xl border border-slate-800/40">
                                    "{{ $item->notes }}"
                                </p>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-300">
                                    {{ strtoupper(substr($item->user?->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="text-[11px]">
                                    <div class="font-bold text-slate-300 line-clamp-1">{{ $item->user?->name ?? 'Admin Ruang Lari' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs transition-all flex items-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-xs"></i>
                                    <span>Detail</span>
                                </a>
                                <a href="{{ route('gpx.download', $item) }}" class="px-2.5 py-1.5 rounded-xl bg-neon/10 hover:bg-neon border border-neon/30 text-neon hover:text-dark font-bold text-xs transition-all flex items-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-download text-xs"></i>
                                    <span>Unduh</span>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $items->links() }}
            </div>
        @else
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-12 text-center max-w-xl mx-auto">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 mb-4 text-xl">
                    <i class="fa-solid fa-route"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Tidak ada rute GPX yang ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1 mb-6">
                    Coba sesuaikan filter pencarian atau jadilah yang pertama mengunggah rute GPX untuk area ini!
                </p>
                <a href="{{ route('tools.buat-rute-lari') }}?open_gpx_modal=1" class="px-5 py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-white transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-dark"></i>
                    <span>Unggah Rute GPX Baru</span>
                </a>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maps = document.querySelectorAll('.gpx-preview-map');
            
            maps.forEach(function(el) {
                const mapId = el.id;
                let coordsData = [];
                
                try {
                    coordsData = JSON.parse(el.getAttribute('data-coords') || '[]');
                } catch(e) {
                    coordsData = [];
                }

                // Default location: Jakarta if no coordinates
                let defaultLat = -6.2088;
                let defaultLng = 106.8456;

                if (coordsData && coordsData.length > 0) {
                    defaultLat = coordsData[0][0];
                    defaultLng = coordsData[0][1];
                }

                const map = L.map(mapId, {
                    zoomControl: false,
                    attributionControl: false,
                    dragging: false,
                    scrollWheelZoom: false,
                    doubleClickZoom: false,
                    touchZoom: false
                }).setView([defaultLat, defaultLng], 12);

                // CartoDB Dark / Light layer clean
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 19,
                    subdomains: 'abcd'
                }).addTo(map);

                if (coordsData && coordsData.length > 1) {
                    const polyline = L.polyline(coordsData, {
                        color: '#ccff00',
                        weight: 4,
                        opacity: 0.9,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds(), { padding: [15, 15] });
                }
            });

            // Auto Geolocation Detector for GPX Database
            const detectCityBtn = document.getElementById('btn-detect-user-city');
            const geoNoticeBar = document.getElementById('gpx-geo-notice-bar');
            const detectedCityNameEl = document.getElementById('gpx-detected-city-name');
            const applyCityBtn = document.getElementById('btn-apply-detected-city');
            const selectCity = document.getElementById('select-gpx-city');
            const filterForm = document.getElementById('form-gpx-filter');

            let detectedCity = null;

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
                } catch(e) {
                    console.error('Geo error:', e);
                }
                return null;
            }

            function handleCityDetection(city) {
                if (!city) return;
                detectedCity = city;
                if (detectedCityNameEl) detectedCityNameEl.textContent = city;
                if (geoNoticeBar) geoNoticeBar.classList.remove('hidden');

                if (selectCity) {
                    for (let i = 0; i < selectCity.options.length; i++) {
                        const optVal = selectCity.options[i].value.toLowerCase();
                        if (optVal && (optVal.includes(city.toLowerCase()) || city.toLowerCase().includes(optVal))) {
                            selectCity.selectedIndex = i;
                            break;
                        }
                    }
                }
            }

            function triggerGeoLocation(forceFilter = false) {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(
                    async function(pos) {
                        const city = await reverseGeocodeUserLoc(pos.coords.latitude, pos.coords.longitude);
                        if (city) {
                            handleCityDetection(city);
                            if (forceFilter && filterForm) {
                                filterForm.submit();
                            }
                        }
                    },
                    function(err) {
                        console.warn('Geolocation warning:', err.message);
                    },
                    { timeout: 8000 }
                );
            }

            if (detectCityBtn) {
                detectCityBtn.addEventListener('click', function() {
                    triggerGeoLocation(true);
                });
            }

            if (applyCityBtn && filterForm) {
                applyCityBtn.addEventListener('click', function() {
                    if (filterForm) filterForm.submit();
                });
            }

            // Auto detect on load if city filter is empty
            const currentUrlParams = new URLSearchParams(window.location.search);
            if (!currentUrlParams.has('city') || currentUrlParams.get('city') === '') {
                triggerGeoLocation(false);
            }
        });
    </script>
@endpush
