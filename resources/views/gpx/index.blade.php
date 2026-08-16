@extends('layouts.pacerhub')

@section('title', 'Database Rute GPX Lari Indonesia - Download File GPX Garmin, Coros & Strava | RuangLari')
@section('meta_title', 'Database Rute GPX Lari Indonesia - Download File GPX Garmin, Coros, Suunto & Strava | RuangLari')
@section('meta_description', 'Direktori & database rute GPX lari terlengkap di Indonesia. Temukan rute lari terverifikasi di Jakarta, Bandung, Surabaya, Bali, Yogyakarta, dan kota lainnya. Unduh gratis file GPX untuk Garmin, Coros, Suunto, & Strava.')
@section('meta_keywords', 'database gpx, rute gpx lari, download file gpx, rute lari jakarta, gpx garmin, gpx coros, gpx suunto, gpx strava, rute lari bandung, rute lari indonesia')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
            display: none !important;
        }
        .gpx-svg-grid {
            background-color: #0c121e;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(204, 255, 0, 0.04) 0%, transparent 70%),
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 20px 20px, 20px 20px;
        }
    </style>

    <!-- OpenGraph & Twitter Meta Tags -->
    <meta property="og:title" content="Database Rute GPX Lari Indonesia - RuangLari">
    <meta property="og:description" content="Direktori & database rute GPX lari terlengkap di Indonesia. Temukan rute lari terverifikasi di Jakarta, Bandung, Surabaya, Bali, dan kota lainnya.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="RuangLari">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Database Rute GPX Lari Indonesia - RuangLari">
    <meta name="twitter:description" content="Unduh gratis kumpulan file GPX rute lari untuk jam Garmin, Coros, Suunto, dan Strava.">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "CollectionPage",
      "name": "Database Rute GPX Lari Indonesia",
      "description": "Direktori rute lari dan kumpulan file GPX terverifikasi untuk pelari di Indonesia.",
      "url": "{{ url()->current() }}",
      "publisher": {
        "@@type": "Organization",
        "name": "RuangLari",
        "url": "{{ url('/') }}"
      },
      "mainEntity": {
        "@@type": "ItemList",
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
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Top Hero & Directory Header -->
        <div class="bg-[#0c121e] border border-slate-800/90 p-6 md:p-10 rounded-3xl relative overflow-hidden shadow-2xl">
            <!-- Background Radial Glow -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-neon/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 relative z-10">
                <div class="space-y-3 max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-750 text-[11px] font-mono font-bold text-slate-300 uppercase tracking-widest">
                        <i class="fa-solid fa-map-location-dot text-neon"></i>
                        <span>Direktori Rute Terverifikasi</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black italic tracking-tight text-white uppercase">
                        DATABASE <span class="text-neon">RUTE GPX</span>
                    </h1>

                    <p class="text-slate-400 text-sm leading-relaxed max-w-2xl font-sans">
                        Eksplorasi dan unduh kumpulan file rute GPX lari terbaik di Indonesia. Kompatibel langsung untuk sinkronisasi ke jam Garmin, Coros, Suunto, dan aplikasi Strava.
                    </p>
                </div>
                
                <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap shrink-0">
                    <a href="{{ route('tools.buat-rute-lari') }}" class="w-full sm:w-auto px-4 py-3 rounded-xl bg-slate-850 hover:bg-slate-750 border border-slate-700 text-white font-mono font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow">
                        <i class="fa-solid fa-draw-polygon text-slate-400"></i>
                        <span>Buat Rute Baru</span>
                    </a>
                    
                    <button id="btn-open-submit-gpx-modal" type="button" class="btn-trigger-gpx-modal w-full sm:w-auto px-5 py-3 rounded-xl bg-neon text-dark hover:bg-white font-mono font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-neon/15 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-dark"></i>
                        <span>Submit GPX (+10 Poin)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Location Detection Banner -->
        <div id="gpx-geo-notice-bar" class="hidden p-4 rounded-2xl bg-slate-900/90 border border-neon/30 text-xs font-mono text-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-neon animate-ping"></span>
                <span>Lokasi Anda terdeteksi di kota: <strong id="gpx-detected-city-name" class="text-white font-bold">...</strong></span>
            </div>
            <button id="btn-apply-detected-city" type="button" class="px-3.5 py-1.5 bg-neon text-dark font-black rounded-lg text-xs hover:bg-white transition flex items-center justify-center gap-1.5 shadow">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter Rute Kota Ini</span>
            </button>
        </div>

        <!-- Search & Filter Form -->
        <form id="form-gpx-filter" method="GET" action="{{ route('gpx.index') }}" class="bg-[#0c121e] border border-slate-800/90 p-5 md:p-6 rounded-2xl space-y-4 shadow-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                
                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cari Rute</label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama rute..." 
                            class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl pl-9 pr-3 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    </div>
                </div>

                <!-- City Filter -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Kota</label>
                        <button id="btn-detect-user-city" type="button" class="text-[10px] font-mono text-neon hover:underline font-bold flex items-center gap-1">
                            <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                            <span>Deteksi GPS</span>
                        </button>
                    </div>
                    <div class="relative">
                        <select id="select-filter-city" name="city" 
                            class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                            <option value="" class="bg-[#0f172a] text-slate-400">Semua Kota</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }} class="bg-[#0f172a] text-white">{{ $c }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Distance Range -->
                <div>
                    <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1.5">Jarak (KM)</label>
                    <div class="relative">
                        <select name="distance" 
                            class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                            <option value="" class="bg-[#0f172a] text-slate-400">Semua Jarak</option>
                            <option value="under_5k" {{ request('distance') == 'under_5k' ? 'selected' : '' }} class="bg-[#0f172a] text-white">&lt; 5 KM (Short / Fun Run)</option>
                            <option value="5k_10k" {{ request('distance') == '5k_10k' ? 'selected' : '' }} class="bg-[#0f172a] text-white">5 KM - 10 KM (Medium)</option>
                            <option value="10k_21k" {{ request('distance') == '10k_21k' ? 'selected' : '' }} class="bg-[#0f172a] text-white">10 KM - 21.1 KM (Half Marathon)</option>
                            <option value="over_21k" {{ request('distance') == 'over_21k' ? 'selected' : '' }} class="bg-[#0f172a] text-white">&gt; 21.1 KM (Long Run / Marathon)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Elevation Filter -->
                <div>
                    <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1.5">Elevasi</label>
                    <div class="relative">
                        <select name="elevation" 
                            class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                            <option value="" class="bg-[#0f172a] text-slate-400">Semua Elevasi</option>
                            <option value="flat" {{ request('elevation') == 'flat' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Datar (&lt; 100m gain)</option>
                            <option value="hilly" {{ request('elevation') == 'hilly' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Rolling / Berbukit (100 - 300m)</option>
                            <option value="mountainous" {{ request('elevation') == 'mountainous' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Pegunungan / Trail (&gt; 300m)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Sort -->
                <div>
                    <label class="block text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1.5">Urutan</label>
                    <div class="relative">
                        <select name="sort" 
                            class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Terbaru</option>
                            <option value="distance_desc" {{ request('sort') == 'distance_desc' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Jarak Terjauh</option>
                            <option value="distance_asc" {{ request('sort') == 'distance_asc' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Jarak Terdekat</option>
                            <option value="elevation_desc" {{ request('sort') == 'elevation_desc' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Elevasi Tertinggi</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }} class="bg-[#0f172a] text-white">Terlama</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-800/80">
                <div class="text-xs font-mono text-slate-400">
                    Menampilkan <strong class="text-white">{{ $items->total() }}</strong> rute GPX
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('gpx.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-700 hover:border-slate-500 text-slate-300 hover:text-white text-xs font-mono font-bold uppercase tracking-wider transition">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-mono font-bold uppercase tracking-wider transition flex items-center gap-1.5 shadow">
                        <i class="fa-solid fa-sliders text-neon text-xs"></i>
                        <span>Terapkan</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- GPX Cards Grid -->
        @if($items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($items as $item)
                    <article class="group bg-[#0c121e] border border-slate-800/80 hover:border-slate-700 rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col justify-between">
                        
                        <div>
                            <!-- Route Visualizer (High Performance SVG Polyline) -->
                            <div class="relative mb-3.5 rounded-xl overflow-hidden border border-slate-800/90 aspect-[16/9] gpx-svg-grid flex items-center justify-center">
                                
                                <svg id="gpx-svg-{{ $item->id }}" 
                                     class="gpx-route-svg w-full h-full p-3 transition-transform duration-500 group-hover:scale-105" 
                                     viewBox="0 0 320 180" 
                                     data-coords="{{ json_encode($item->coordinates_json ?? []) }}">
                                    <!-- Fallback center radar line -->
                                    <circle cx="160" cy="90" r="45" fill="none" stroke="rgba(255,255,255,0.03)" stroke-dasharray="3 3"/>
                                </svg>
                                
                                <!-- City Badge -->
                                @if($item->city)
                                    <div class="absolute top-2.5 left-2.5 z-10 px-2.5 py-1 rounded-md bg-slate-950/80 backdrop-blur border border-slate-800 text-[10px] font-mono font-bold text-slate-200 flex items-center gap-1 shadow">
                                        <i class="fa-solid fa-location-dot text-neon text-[10px]"></i>
                                        <span>{{ $item->city }}</span>
                                    </div>
                                @endif

                                <!-- Verified Chip -->
                                <div class="absolute top-2.5 right-2.5 z-10 px-2 py-0.5 rounded-md bg-slate-950/80 backdrop-blur border border-slate-800 text-[9px] font-mono font-bold text-slate-300 flex items-center gap-1 shadow">
                                    <i class="fa-solid fa-check text-neon text-[9px]"></i>
                                    <span>GPX Track</span>
                                </div>
                            </div>

                            <!-- Route Title -->
                            <h2 class="text-base font-bold text-white group-hover:text-neon transition-colors line-clamp-1 mb-2 font-sans">
                                <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <!-- 3 Key Metric Stats -->
                            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                                <div class="bg-[#090D16] p-2.5 rounded-xl border border-slate-800/80">
                                    <div class="text-[9px] text-slate-400 font-mono uppercase tracking-widest font-bold">Jarak</div>
                                    <div class="text-xs font-mono font-black text-white mt-0.5">
                                        {{ $item->distance_km ? number_format($item->distance_km, 2) . ' km' : '-' }}
                                    </div>
                                </div>
                                <div class="bg-[#090D16] p-2.5 rounded-xl border border-slate-800/80">
                                    <div class="text-[9px] text-slate-400 font-mono uppercase tracking-widest font-bold">Elev Gain</div>
                                    <div class="text-xs font-mono font-black text-white mt-0.5">
                                        {{ $item->elevation_gain_m !== null ? '+' . round($item->elevation_gain_m) . 'm' : '-' }}
                                    </div>
                                </div>
                                <div class="bg-[#090D16] p-2.5 rounded-xl border border-slate-800/80">
                                    <div class="text-[9px] text-slate-400 font-mono uppercase tracking-widest font-bold">Elev Loss</div>
                                    <div class="text-xs font-mono font-black text-white mt-0.5">
                                        {{ $item->elevation_loss_m !== null ? '-' . round($item->elevation_loss_m) . 'm' : '-' }}
                                    </div>
                                </div>
                            </div>

                            @if($item->notes)
                                <p class="text-xs text-slate-400 line-clamp-2 mb-4 italic bg-[#090D16]/60 p-2.5 rounded-xl border border-slate-800/60 font-sans">
                                    "{{ $item->notes }}"
                                </p>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-mono font-bold text-slate-300">
                                    {{ strtoupper(substr($item->user?->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="text-[11px] font-mono">
                                    <div class="font-bold text-slate-300 line-clamp-1 max-w-[100px]">{{ $item->user?->name ?? 'Ruang Lari' }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-850 hover:bg-slate-750 border border-slate-700 text-white font-mono font-bold text-xs transition flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[10px]"></i>
                                    <span>Detail</span>
                                </a>
                                <a href="{{ route('gpx.download', $item) }}" class="px-2.5 py-1.5 rounded-lg bg-neon text-dark hover:bg-white font-mono font-bold text-xs transition flex items-center gap-1 shadow">
                                    <i class="fa-solid fa-download text-[10px]"></i>
                                    <span>Unduh</span>
                                </a>
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $items->links() }}
            </div>
        @else
            <div class="bg-[#0c121e] border border-slate-800 rounded-3xl p-12 text-center max-w-xl mx-auto shadow-2xl">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-850 border border-slate-750 flex items-center justify-center text-slate-400 mb-4 text-xl">
                    <i class="fa-solid fa-route"></i>
                </div>
                <h3 class="text-lg font-bold text-white uppercase font-sans">Tidak ada rute GPX yang ditemukan</h3>
                <p class="text-slate-400 text-xs mt-2 font-sans">
                    Coba sesuaikan filter pencarian kota atau jarak, atau jadilah yang pertama mengunggah rute di kategori ini.
                </p>
                <div class="mt-6">
                    <a href="{{ route('gpx.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-mono font-bold uppercase tracking-wider transition">
                        Reset Filter
                    </a>
                </div>
            </div>
        @endif

        <!-- Educational & SEO Runner FAQ Section -->
        <section class="mt-16 pt-12 border-t border-slate-800/80 space-y-6">
            <div class="max-w-2xl">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-neon">Panduan & Informasi</span>
                <h2 class="text-2xl font-black text-white uppercase tracking-tight font-sans mt-1">
                    PANDUAN LENGKAP PENGGUNAAN FILE GPX
                </h2>
                <p class="text-slate-400 text-xs leading-relaxed mt-1 font-sans">
                    Pelajari cara memanfaatkan file rute GPX untuk navigasi lari di smartwatch dan aplikasi training Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-neon text-xs"></i>
                        <span>Apa itu File GPX?</span>
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-sans">
                        GPX (GPS Exchange Format) adalah standar format file koordinat GPS yang menyimpan jalur lintasan, jarak, dan elevasi rute lari secara presisi untuk digunakan sebagai panduan navigasi (*turn-by-turn navigation*).
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                        <i class="fa-solid fa-watch text-neon text-xs"></i>
                        <span>Cara Import ke Garmin Watch</span>
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-sans">
                        Unduh file GPX di RuangLari &rarr; Buka aplikasi <strong>Garmin Connect</strong> di HP &rarr; Buka menu <em>Training & Planning &rarr; Courses &rarr; Import</em> &rarr; Pilih file GPX dan klik <em>Send to Device</em>.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                        <i class="fa-solid fa-mobile-screen text-neon text-xs"></i>
                        <span>Cara Import ke Coros Watch</span>
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-sans">
                        Unduh file GPX &rarr; Buka file dengan aplikasi <strong>COROS App</strong> di smartphone &rarr; Simpan rute ke <em>Route Library</em> &rarr; Pilih <em>Sync with Watch</em> untuk navigasi saat lari.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-[#0c121e] border border-slate-800 space-y-2">
                    <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                        <i class="fa-solid fa-award text-neon text-xs"></i>
                        <span>Dapatkan Poin dengan Berbagi Rute</span>
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-sans">
                        Setiap rute GPX yang Anda unggah memberikan <strong>+10 Run Points</strong> ke profil akun Anda yang dapat dikumpulkan dan digunakan untuk berbagai fitur platform RuangLari.
                    </p>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Submit GPX Modal -->
<div id="modal-submit-gpx" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
    <div class="bg-[#0c121e] border border-slate-800 rounded-3xl max-w-xl w-full p-6 md:p-8 space-y-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
            <div>
                <span class="text-[10px] font-mono font-bold text-neon uppercase tracking-widest">Kontribusi Komunitas</span>
                <h3 class="text-lg font-black text-white uppercase font-sans mt-0.5">SUBMIT RUTE GPX</h3>
            </div>
            <button id="btn-close-submit-gpx-modal" type="button" class="w-8 h-8 rounded-full bg-slate-850 border border-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-times text-xs"></i>
            </button>
        </div>

        <div id="gpx-modal-alert" class="hidden"></div>

        <form id="form-submit-gpx-modal" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- GPX File Input -->
            <div>
                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                    Pilih File GPX <span class="text-rose-400">*</span>
                </label>
                <label for="input-modal-gpx-file" class="flex flex-col items-center justify-center w-full min-h-[110px] border-2 border-dashed border-slate-700 hover:border-slate-500 rounded-xl cursor-pointer bg-[#090D16] transition p-4 group">
                    <i class="fa-solid fa-file-arrow-up text-xl text-slate-400 group-hover:text-neon transition mb-1.5"></i>
                    <span class="text-xs font-mono text-slate-300"><strong class="text-white">Klik untuk upload file GPX</strong></span>
                    <span class="text-[10px] font-mono text-slate-500 mt-0.5">Format .gpx (Maks. 10MB)</span>
                    <input id="input-modal-gpx-file" name="gpx_file" type="file" accept=".gpx,application/gpx+xml,text/xml" class="hidden" required>
                </label>
            </div>

            <!-- Route Preview Box -->
            <div id="modal-gpx-preview-container" class="hidden space-y-3 p-4 rounded-2xl bg-[#090D16] border border-slate-800">
                <div class="flex items-center justify-between text-xs font-mono">
                    <span class="text-slate-400">Preview Rute:</span>
                    <span id="modal-gpx-point-count" class="text-neon font-bold">0 pts</span>
                </div>
                <div id="modal-gpx-preview-map" class="h-44 w-full rounded-xl overflow-hidden border border-slate-800"></div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs font-mono">
                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-800">
                        <span class="text-[10px] text-slate-400 block">Jarak</span>
                        <strong id="modal-stat-distance" class="text-white">-</strong>
                    </div>
                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-800">
                        <span class="text-[10px] text-slate-400 block">Gain</span>
                        <strong id="modal-stat-gain" class="text-white">-</strong>
                    </div>
                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-800">
                        <span class="text-[10px] text-slate-400 block">Loss</span>
                        <strong id="modal-stat-loss" class="text-white">-</strong>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                    Judul Rute <span class="text-rose-400">*</span>
                </label>
                <input id="input-modal-title" type="text" name="title" required
                    class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                    placeholder="Contoh: Rute Car Free Day Sudirman - GBK Loop">
            </div>

            <!-- City -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200">
                        Kota Lokasi <span class="text-rose-400">*</span>
                    </label>
                    <button id="btn-modal-detect-location" type="button" class="text-[10px] font-mono text-neon hover:underline font-bold flex items-center gap-1">
                        <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                        <span>Deteksi Otomatis</span>
                    </button>
                </div>
                <div class="relative">
                    <input id="input-modal-city" type="text" name="city" required
                        class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                        placeholder="Contoh: Jakarta Pusat / Bandung">
                    <div id="modal-city-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-neon text-xs">
                        <i class="fa-solid fa-spinner animate-spin"></i>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-1.5">
                    Catatan Rute (Opsional)
                </label>
                <textarea name="notes" rows="2"
                    class="w-full bg-[#090D16] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                    placeholder="Contoh: Rute aspal mulus, cocok untuk long run pagi hari sebelum jam 7. Ada water station di km 5."></textarea>
            </div>

            <!-- Hidden Inputs -->
            <input type="hidden" id="input-modal-dist" name="client_distance_km">
            <input type="hidden" id="input-modal-gain" name="client_elevation_gain">
            <input type="hidden" id="input-modal-loss" name="client_elevation_loss">
            <input type="hidden" id="input-modal-coords" name="coordinates_json">

            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('btn-close-submit-gpx-modal').click()"
                    class="px-4 py-2.5 rounded-xl border border-slate-700 hover:border-slate-500 text-slate-300 hover:text-white text-xs font-mono font-bold uppercase tracking-wider transition">
                    Batal
                </button>
                <button id="btn-submit-gpx-form" type="submit"
                    class="px-5 py-2.5 rounded-xl bg-neon text-dark hover:bg-white text-xs font-mono font-black uppercase tracking-wider transition shadow-lg shadow-neon/10 flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Kirim Rute</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isUserLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

            // Coordinate extraction helper
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

            // ================= 1. RENDER VECTOR SVG POLYLINE THUMBNAILS =================
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
                    // Fallback visual icon if no coordinate trace is available
                    svg.innerHTML = `
                        <text x="160" y="95" fill="#64748b" font-family="monospace" font-size="10" text-anchor="middle" letter-spacing="1">GPS TRACK TERVERIFIKASI</text>
                    `;
                    return;
                }

                // Compute bounding box
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

                // Map coordinates into SVG viewBox (320x180) maintaining aspect ratio
                const scale = Math.min(drawW / lngSpan, drawH / latSpan);
                const offsetX = (width - (lngSpan * scale)) / 2;
                const offsetY = (height - (latSpan * scale)) / 2;

                const pointsStr = validCoords.map(([lat, lng]) => {
                    const x = offsetX + ((lng - minLng) * scale);
                    const y = height - (offsetY + ((lat - minLat) * scale)); // Invert Y
                    return `${x.toFixed(1)},${y.toFixed(1)}`;
                }).join(' ');

                const startPt = validCoords[0];
                const startX = (offsetX + ((startPt[1] - minLng) * scale)).toFixed(1);
                const startY = (height - (offsetY + ((startPt[0] - minLat) * scale))).toFixed(1);

                const endPt = validCoords[validCoords.length - 1];
                const endX = (offsetX + ((endPt[1] - minLng) * scale)).toFixed(1);
                const endY = (height - (offsetY + ((endPt[0] - minLat) * scale))).toFixed(1);

                svg.innerHTML = `
                    <defs>
                        <filter id="glow-${svg.id}" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="3" result="blur" />
                            <feMerge>
                                <feMergeNode in="blur" />
                                <feMergeNode in="SourceGraphic" />
                            </feMerge>
                        </filter>
                    </defs>
                    <polyline points="${pointsStr}" fill="none" stroke="#ccff00" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow-${svg.id})" />
                    <!-- Start Point -->
                    <circle cx="${startX}" cy="${startY}" r="4" fill="#ccff00" stroke="#000" stroke-width="1.5" />
                    <!-- Finish Point -->
                    <circle cx="${endX}" cy="${endY}" r="4" fill="#ffffff" stroke="#000" stroke-width="1.5" />
                `;
            });

            // ================= 2. GEOLOCATION FILTER HELPERS =================
            const detectCityBtn = document.getElementById('btn-detect-user-city');
            const applyCityBtn = document.getElementById('btn-apply-detected-city');
            const geoNoticeBar = document.getElementById('gpx-geo-notice-bar');
            const detectedCityNameEl = document.getElementById('gpx-detected-city-name');
            const selectCity = document.getElementById('select-filter-city');
            const filterForm = document.getElementById('form-gpx-filter');

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

            function handleCityDetection(city) {
                if (!city) return;
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
                    function(err) {},
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

            // ================= 3. SUBMIT GPX MODAL HANDLER =================
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
                    ? 'p-3.5 rounded-2xl text-xs font-mono font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-300'
                    : 'p-3.5 rounded-2xl text-xs font-mono font-semibold bg-rose-500/10 border border-rose-500/30 text-rose-300';
                alertBox.innerHTML = msg;
                alertBox.classList.remove('hidden');
            }

            function hideAlert() {
                if (alertBox) alertBox.classList.add('hidden');
            }

            function openGpxModal() {
                if (!modal) return;
                if (!isUserLoggedIn) {
                    if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal();
                        return;
                    }
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
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

            if (closeBtn) {
                closeBtn.addEventListener('click', closeGpxModal);
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeGpxModal();
                });
            }

            if (currentUrlParams.get('open_gpx_modal') === '1') {
                openGpxModal();
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
                            return;
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
                                color: '#ccff00',
                                weight: 4,
                                opacity: 0.95
                            }).addTo(previewMap);

                            const bounds = previewPolyline.getBounds();
                            if (bounds.isValid()) {
                                previewMap.fitBounds(bounds, { padding: [15, 15] });
                            }
                        } catch(mapErr) {}

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
                            if (previewContainer) previewContainer.classList.add('hidden');
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
