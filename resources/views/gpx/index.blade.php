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
                <button id="btn-open-submit-gpx-modal" type="button" class="btn-trigger-gpx-modal px-5 py-3 rounded-2xl bg-neon text-dark hover:bg-white font-black text-xs transition-all shadow-lg shadow-neon/15 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-up text-dark"></i>
                    <span>Submit GPX (+10 Poin)</span>
                </button>
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
                            <span>Deteksi</span>
                        </button>
                    </div>
                    <select id="select-filter-city" name="city" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Distance Range -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jarak (KM)</label>
                    <select name="distance" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="">Semua Jarak</option>
                        <option value="under_5k" {{ request('distance') == 'under_5k' ? 'selected' : '' }}>&lt; 5 KM (Short / Fun Run)</option>
                        <option value="5k_10k" {{ request('distance') == '5k_10k' ? 'selected' : '' }}>5 KM - 10 KM (Medium)</option>
                        <option value="10k_21k" {{ request('distance') == '10k_21k' ? 'selected' : '' }}>10 KM - 21.1 KM (Half Marathon)</option>
                        <option value="over_21k" {{ request('distance') == 'over_21k' ? 'selected' : '' }}>&gt; 21.1 KM (Long Run / Marathon)</option>
                    </select>
                </div>

                <!-- Elevation Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Elevasi</label>
                    <select name="elevation" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="">Semua Elevasi</option>
                        <option value="flat" {{ request('elevation') == 'flat' ? 'selected' : '' }}>Datar (&lt; 100m gain)</option>
                        <option value="hilly" {{ request('elevation') == 'hilly' ? 'selected' : '' }}>Rolling / Berbukit (100 - 300m)</option>
                        <option value="mountainous" {{ request('elevation') == 'mountainous' ? 'selected' : '' }}>Pegunungan / Trail (&gt; 300m)</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Urutan</label>
                    <select name="sort" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 transition">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="distance_desc" {{ request('sort') == 'distance_desc' ? 'selected' : '' }}>Jarak Terjauh</option>
                        <option value="distance_asc" {{ request('sort') == 'distance_asc' ? 'selected' : '' }}>Jarak Terdekat</option>
                        <option value="elevation_desc" {{ request('sort') == 'elevation_desc' ? 'selected' : '' }}>Elevasi Tertinggi</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>

            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                <div class="text-xs text-slate-400">
                    Menampilkan <strong class="text-white">{{ $items->total() }}</strong> rute GPX terverifikasi
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('gpx.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white text-xs font-semibold transition">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-sliders text-neon text-xs"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- GPX Grid -->
        @if($items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($items as $item)
                    <div class="group bg-slate-900/50 border border-slate-800/80 hover:border-slate-700 rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-neon/5 flex flex-col justify-between">
                        
                        <div>
                            <!-- Map Container -->
                            <div class="relative mb-3">
                                <div id="gpx-map-{{ $item->id }}" class="gpx-preview-map border border-slate-800" data-coords="{{ json_encode($item->coordinates_json ?? []) }}"></div>
                                
                                <!-- City Badge -->
                                @if($item->city)
                                    <div class="absolute top-2.5 left-2.5 z-10 px-2.5 py-1 rounded-md bg-slate-900/80 backdrop-blur-md border border-slate-700 text-[10px] font-bold text-slate-200 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-neon text-[10px]"></i>
                                        <span>{{ $item->city }}</span>
                                    </div>
                                @endif

                                <!-- Category / Verified Badge -->
                                <div class="absolute top-2.5 right-2.5 z-10 px-2 py-0.5 rounded-md bg-emerald-500/20 border border-emerald-500/40 text-[10px] font-bold text-emerald-300 flex items-center gap-1 backdrop-blur-md">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i>
                                    <span>Verified</span>
                                </div>
                            </div>

                            <!-- Route Title -->
                            <h3 class="text-base font-bold text-white group-hover:text-neon transition-colors line-clamp-1 mb-2">
                                <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}">
                                    {{ $item->title }}
                                </a>
                            </h3>

                            <!-- Stats Badges -->
                            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                                <div class="bg-slate-950/60 p-2 rounded-xl border border-slate-800/60">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Jarak</div>
                                    <div class="text-xs font-black text-white mt-0.5">
                                        {{ $item->distance_km ? number_format($item->distance_km, 2) . ' km' : '-' }}
                                    </div>
                                </div>
                                <div class="bg-slate-950/60 p-2 rounded-xl border border-slate-800/60">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Elev Gain</div>
                                    <div class="text-xs font-black text-emerald-400 mt-0.5">
                                        {{ $item->elevation_gain_m !== null ? '+' . round($item->elevation_gain_m) . 'm' : '-' }}
                                    </div>
                                </div>
                                <div class="bg-slate-950/60 p-2 rounded-xl border border-slate-800/60">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Elev Loss</div>
                                    <div class="text-xs font-black text-rose-400 mt-0.5">
                                        {{ $item->elevation_loss_m !== null ? '-' . round($item->elevation_loss_m) . 'm' : '-' }}
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
                <button type="button" class="btn-trigger-gpx-modal px-5 py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-white transition inline-flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-up text-dark"></i>
                    <span>Unggah Rute GPX Baru (+10 PTS)</span>
                </button>
            </div>
        @endif

    </div>
</div>

<!-- Modal Submit GPX Langsung di Halaman Ini -->
<div id="modal-submit-gpx" class="fixed inset-0 z-[110] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="relative w-full max-w-xl max-h-[90vh] bg-[#0b1220] border border-slate-800 rounded-3xl shadow-2xl flex flex-col overflow-hidden text-slate-100 font-sans">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between flex-shrink-0 bg-slate-900/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-neon/10 border border-neon/30 flex items-center justify-center text-neon">
                    <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white uppercase tracking-tight">Unggah Rute GPX Baru</h3>
                    <p class="text-[11px] text-slate-400">Dapatkan <span class="text-neon font-bold">+10 poin runner</span> untuk setiap rute GPX yang Anda submit</p>
                </div>
            </div>
            <button id="btn-close-submit-gpx-modal" type="button" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto flex-grow space-y-4">
            
            <div id="gpx-modal-alert" class="hidden p-3.5 rounded-2xl text-xs font-semibold"></div>

            <form id="form-submit-gpx-modal" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <!-- File Input -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pilih File GPX (.gpx / .xml)</label>
                    <input id="input-modal-gpx-file" type="file" name="gpx_file" accept=".gpx,.xml" required
                        class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 transition cursor-pointer">
                </div>

                <!-- Live Mapbox/Leaflet Preview Container -->
                <div id="modal-gpx-preview-container" class="hidden space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-400">
                        <span>Preview Jalur Rute (Map Preview)</span>
                        <span id="modal-gpx-point-count" class="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300">0 pts</span>
                    </div>

                    <div id="modal-gpx-preview-map" class="h-44 w-full rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden relative"></div>

                    <!-- Parsed Stats Badges -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-slate-950/70 border border-slate-800/80 p-2.5 rounded-xl text-center">
                            <div class="text-[10px] uppercase font-bold text-slate-500">Jarak</div>
                            <div id="modal-stat-distance" class="text-xs font-black text-white mt-0.5">0.00 km</div>
                        </div>
                        <div class="bg-slate-950/70 border border-slate-800/80 p-2.5 rounded-xl text-center">
                            <div class="text-[10px] uppercase font-bold text-slate-500">Elev Gain</div>
                            <div id="modal-stat-gain" class="text-xs font-black text-emerald-400 mt-0.5">+0m</div>
                        </div>
                        <div class="bg-slate-950/70 border border-slate-800/80 p-2.5 rounded-xl text-center">
                            <div class="text-[10px] uppercase font-bold text-slate-500">Elev Loss</div>
                            <div id="modal-stat-loss" class="text-xs font-black text-rose-400 mt-0.5">-0m</div>
                        </div>
                    </div>
                </div>

                <!-- Title & City -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama / Judul Rute</label>
                        <input id="input-modal-title" type="text" name="title" required placeholder="Mis. Loop GBK Senayan 5K" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 transition">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kota / Lokasi</label>
                            <button id="btn-modal-detect-location" type="button" class="text-[10px] text-neon hover:underline font-bold flex items-center gap-1">
                                <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                                <span>Deteksi GPS</span>
                            </button>
                        </div>
                        <div class="relative">
                            <input id="input-modal-city" type="text" name="city" required placeholder="Mis. Jakarta Pusat" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 transition">
                            <span id="modal-city-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                                <i class="fa-solid fa-spinner animate-spin"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notes / Description -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan / Deskripsi Rute (Opsional)</label>
                    <textarea id="input-modal-notes" name="notes" rows="3" placeholder="Informasi jenis permukaan (aspal/trail), fasilitas air minum, toilet, atau tips rute..." class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 transition"></textarea>
                </div>

                <input type="hidden" id="input-modal-dist" name="client_distance_km">
                <input type="hidden" id="input-modal-gain" name="client_elevation_gain">
                <input type="hidden" id="input-modal-loss" name="client_elevation_loss">
                <input type="hidden" id="input-modal-coords" name="coordinates_json">

                <div class="pt-2">
                    <button id="btn-submit-gpx-form" type="submit" class="w-full py-3 bg-neon text-dark font-black rounded-xl hover:bg-white transition text-xs flex items-center justify-center gap-2 shadow-lg shadow-neon/10 cursor-pointer">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Kirim Rute GPX (+10 PTS)</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render Grid Map Previews
            const maps = document.querySelectorAll('.gpx-preview-map');
            
            maps.forEach(function(el) {
                const mapId = el.id;
                let coordsData = [];
                
                try {
                    coordsData = JSON.parse(el.getAttribute('data-coords') || '[]');
                } catch(e) {
                    coordsData = [];
                }

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

                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 19,
                    subdomains: 'abcd'
                }).addTo(map);

                if (coordsData && coordsData.length > 1) {
                    const polyline = L.polyline(coordsData, {
                        color: '#ccff00',
                        weight: 3,
                        opacity: 0.9
                    }).addTo(map);

                    try {
                        map.fitBounds(polyline.getBounds(), { padding: [10, 10] });
                    } catch(err) {}
                }
            });

            // Geolocation Filter Helpers
            const detectCityBtn = document.getElementById('btn-detect-user-city');
            const applyCityBtn = document.getElementById('btn-apply-detected-city');
            const geoNoticeBar = document.getElementById('gpx-geo-notice-bar');
            const detectedCityNameEl = document.getElementById('gpx-detected-city-name');
            const selectCity = document.getElementById('select-filter-city');
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

            const currentUrlParams = new URLSearchParams(window.location.search);
            if (!currentUrlParams.has('city') || currentUrlParams.get('city') === '') {
                triggerGeoLocation(false);
            }

            // ================= SUBMIT GPX MODAL HANDLER =================
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
                    ? 'p-3.5 rounded-2xl text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-300'
                    : 'p-3.5 rounded-2xl text-xs font-semibold bg-rose-500/10 border border-rose-500/30 text-rose-300';
                alertBox.innerHTML = msg;
                alertBox.classList.remove('hidden');
            }

            function hideAlert() {
                if (alertBox) alertBox.classList.add('hidden');
            }

            function openGpxModal() {
                if (!modal) return;
                @guest
                    if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal();
                        return;
                    }
                    window.location.href = "{{ route('login') }}";
                    return;
                @endguest

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

            if (closeBtn) {
                closeBtn.addEventListener('click', closeGpxModal);
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeGpxModal();
                });
            }

            // Auto-open modal if URL query has open_gpx_modal=1 or action=submit
            if (currentUrlParams.get('open_gpx_modal') === '1' || currentUrlParams.get('action') === 'submit') {
                openGpxModal();
            }

            // Haversine distance calculator
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

            // Reverse Geocoding for Modal City
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
                            if (city) {
                                const cleanCity = city.replace(/^(Kota|Kabupaten)\s+/i, '').trim();
                                const cityInput = document.getElementById('input-modal-city');
                                if (cityInput) cityInput.value = cleanCity;
                            }
                        }
                    }
                } catch (e) {
                    console.error('Reverse geocode error:', e);
                } finally {
                    if (loader) loader.classList.add('hidden');
                }
            }

            // Detect GPS Button in Modal
            const detectModalGpsBtn = document.getElementById('btn-modal-detect-location');
            if (detectModalGpsBtn) {
                detectModalGpsBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        showAlert('Browser Anda tidak mendukung Geolocation.');
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
                            showAlert('Gagal mengakses lokasi GPS: ' + err.message);
                        },
                        { timeout: 10000 }
                    );
                });
            }

            // GPX File change event & live preview
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    hideAlert();
                    const file = e.target.files[0];
                    if (!file) return;

                    const titleInput = document.getElementById('input-modal-title');
                    if (titleInput && !titleInput.value) {
                        const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
                        titleInput.value = nameWithoutExt.replace(/[-_]/g, ' ');
                    }

                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        const xmlText = evt.target.result;
                        const parser = new DOMParser();
                        const xmlDoc = parser.parseFromString(xmlText, "text/xml");

                        let trkpts = xmlDoc.getElementsByTagName('trkpt');
                        if (trkpts.length === 0) {
                            trkpts = xmlDoc.getElementsByTagName('rtept');
                        }

                        if (trkpts.length < 2) {
                            showAlert('File GPX tidak valid atau tidak memiliki titik koordinat yang cukup.');
                            return;
                        }

                        const coords = [];
                        let prevLat = null, prevLon = null, prevEle = null;
                        let totalDist = 0, gainSum = 0, lossSum = 0;

                        for (let i = 0; i < trkpts.length; i++) {
                            const pt = trkpts[i];
                            const lat = parseFloat(pt.getAttribute('lat'));
                            const lon = parseFloat(pt.getAttribute('lon'));
                            if (isNaN(lat) || isNaN(lon)) continue;

                            coords.push([lat, lon]);

                            const eleEl = pt.getElementsByTagName('ele')[0];
                            const ele = eleEl ? parseFloat(eleEl.textContent) : null;

                            if (prevLat !== null) {
                                totalDist += haversineDistance(prevLat, prevLon, lat, lon);
                                if (prevEle !== null && ele !== null) {
                                    const diff = ele - prevEle;
                                    if (diff > 0) gainSum += diff;
                                    if (diff < 0) lossSum += Math.abs(diff);
                                }
                            }

                            prevLat = lat; prevLon = lon; prevEle = ele;
                        }

                        const distKm = (totalDist / 1000).toFixed(2);
                        document.getElementById('modal-stat-distance').textContent = distKm + ' km';
                        document.getElementById('modal-stat-gain').textContent = '+' + Math.round(gainSum) + 'm';
                        document.getElementById('modal-stat-loss').textContent = '-' + Math.round(lossSum) + 'm';
                        document.getElementById('modal-gpx-point-count').textContent = coords.length + ' pts';

                        document.getElementById('input-modal-dist').value = (totalDist / 1000).toFixed(3);
                        document.getElementById('input-modal-gain').value = Math.round(gainSum);
                        document.getElementById('input-modal-loss').value = Math.round(lossSum);
                        
                        const step = Math.max(1, Math.floor(coords.length / 200));
                        const sampledCoords = coords.filter((_, idx) => idx % step === 0);
                        document.getElementById('input-modal-coords').value = JSON.stringify(sampledCoords);

                        if (previewContainer) previewContainer.classList.remove('hidden');

                        if (!previewMap) {
                            previewMap = L.map('modal-gpx-preview-map', {
                                zoomControl: false,
                                attributionControl: false
                            });
                            
                            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                maxZoom: 19,
                                subdomains: 'abcd'
                            }).addTo(previewMap);
                        }

                        if (previewPolyline) {
                            previewMap.removeLayer(previewPolyline);
                        }

                        previewPolyline = L.polyline(coords, {
                            color: '#ccff00',
                            weight: 4,
                            opacity: 0.95
                        }).addTo(previewMap);

                        previewMap.fitBounds(previewPolyline.getBounds(), { padding: [15, 15] });

                        if (coords.length > 0) {
                            reverseGeocodeModalCity(coords[0][0], coords[0][1]);
                        }

                        setTimeout(() => {
                            if (previewMap) previewMap.invalidateSize();
                        }, 250);
                    };
                    reader.readAsText(file);
                });
            }

            // Form Submit handler
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
                        const data = resObj.body;
                        if (resObj.status === 401 && data.requires_login) {
                            closeGpxModal();
                            if (typeof window.openLoginModal === 'function') {
                                window.openLoginModal();
                            } else {
                                window.location.href = "{{ route('login') }}";
                            }
                            return;
                        }

                        if (data.success) {
                            showAlert(data.message, true);
                            form.reset();
                            if (previewContainer) previewContainer.classList.add('hidden');
                            setTimeout(() => {
                                closeGpxModal();
                                window.location.reload();
                            }, 2000);
                        } else {
                            showAlert(data.message || 'Gagal mengirim GPX. Silakan coba lagi.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('Terjadi kesalahan jaringan/sistem.');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    });
                });
            }
        });
    </script>
@endpush
