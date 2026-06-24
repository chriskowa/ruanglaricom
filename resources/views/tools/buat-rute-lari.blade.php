@extends('layouts.pacerhub')

@section('title', 'Ruang Lari Tools - Buat Rute Lari')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .rl-custom-icon-btn.active {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: #ffffff !important;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.1) !important;
        }
        .leaflet-control{display: none;}
        #rl-route-map { height: calc(100vh - 190px); min-height: 520px; }
        @media (max-width: 1024px) { #rl-route-map { height: calc(100vh - 260px); min-height: 520px; } }
        @media (max-width: 640px) { #rl-route-map { height: calc(100vh - 250px); min-height: 420px; } }
        .leaflet-control-attribution { font-size: 10px; opacity: .85; }
        .leaflet-container { background: #0b1220; }
        svg { height: auto; }
        @media (max-width: 1024px) {
            .rl-mobile-sticky-ringkasan {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 9999 !important;
                border-top: 1px solid rgba(51, 65, 85, 0.5) !important;
                border-radius: 24px 24px 0 0 !important;
                background-color: rgba(15, 23, 42, 0.98) !important;
                backdrop-filter: blur(12px) !important;
                box-shadow: 0 -10px 25px -5px rgba(0, 0, 0, 0.3), 0 -8px 10px -6px rgba(0, 0, 0, 0.3) !important;
                padding: 12px 16px !important;
                padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px)) !important;
                max-height: 85vh !important;
                overflow-y: auto !important;
                transition: max-height 0.25s cubic-bezier(0.4, 0, 0.2, 1), padding 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            .rl-mobile-sticky-ringkasan.rl-minimized {
                max-height: 60px !important;
                padding-top: 6px !important;
                padding-bottom: calc(6px + env(safe-area-inset-bottom, 0px)) !important;
                overflow: hidden !important;
                border-radius: 16px 16px 0 0 !important;
            }
            #chatbox-toggle {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black italic tracking-tighter text-white">
                            BUAT <span class="text-neon">RUTE LARI</span>
                        </h1>
                        <p class="text-slate-400 mt-1 max-w-2xl">
                            Tap peta untuk bikin rute. Simpan, share link, atau export GPX buat dipakai di jam/aplikasi favoritmu.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('calculator') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:border-slate-500 transition text-sm font-bold">
                            Tools Lain
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-5">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-black tracking-wider text-slate-200 uppercase">Setup</div>
                            <div id="rl-route-status" class="text-[11px] text-slate-500 font-bold">Siap</div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Rute</label>
                                <input id="rl-route-name" type="text" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500" placeholder="Contoh: Long Run Minggu Pagi">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cari Lokasi</label>
                                <div class="mt-1 relative group">
                                    <input id="rl-search-q" type="text" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-4 pr-12 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 transition" placeholder="Ketik kota / landmark...">
                                    <button id="rl-search-btn" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </button>
                                </div>
                                <div id="rl-search-results" class="mt-2 hidden"></div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Target Pace</label>
                            <div class="mt-1 flex items-center gap-2">
            <input id="rl-pace-min" inputmode="numeric" type="number" min="0" 
                class="w-20 bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-3 text-white font-bold text-center focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500" 
                value="6" placeholder="00">
            
            <span class="text-slate-500 font-bold">:</span>
            
            <input id="rl-pace-sec" inputmode="numeric" type="number" min="0" max="59" 
                class="w-20 bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-3 text-white font-bold text-center focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500" 
                value="0" placeholder="00">
            
            <span class="text-xs text-slate-500 font-bold whitespace-nowrap">/km</span>
        </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jam Start Aktivitas</label>
                                <input id="rl-start-time" type="datetime-local" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500">
                                <div id="rl-start-time-error" class="text-xs text-red-500 mt-1.5 hidden"></div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mode</label>
                                <select id="rl-mode" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500">
                                    <option value="tap">Tap titik di peta</option>
                                    <option value="freehand">Freehand (beta)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-800 rounded-xl px-4 py-3 text-sm font-bold text-slate-200">
                                    <input id="rl-follow-road" type="checkbox" class="accent-white" checked>
                                    Ikuti jalan (OSRM)
                                </label>
                                <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-800 rounded-xl px-4 py-3 text-sm font-bold text-slate-200">
                                    <input id="rl-show-directions" type="checkbox" class="accent-white" checked>
                                    Tampilkan arah rute
                                </label>
                            </div>

                            <!-- Custom Markers Block -->
                            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4">
                                <div class="text-xs font-black tracking-wider text-slate-200 uppercase">Tambah Marker Rute</div>
                                <div class="mt-2 text-xs text-slate-400 leading-relaxed">Pilih marker di bawah, lalu klik/tap pada peta untuk menaruhnya. Klik marker di peta untuk menambah keterangan/hapus.</div>
                                <div class="mt-3 grid grid-cols-6 gap-2">
                                    <button type="button" class="rl-custom-icon-btn p-2.5 rounded-xl bg-slate-950/40 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-center transition relative group" title="Water Station" data-type="water">
                                        <i class="fa-solid fa-droplet text-lg"></i>
                                    </button>
                                    <button type="button" class="rl-custom-icon-btn p-2.5 rounded-xl bg-slate-950/40 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-center transition relative group" title="Bahaya / Warning" data-type="warning">
                                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                                    </button>
                                    <button type="button" class="rl-custom-icon-btn p-2.5 rounded-xl bg-slate-950/40 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-center transition relative group" title="Perempatan / Intersection" data-type="intersection">
                                        <i class="fa-solid fa-arrows-split-up-and-left text-lg"></i>
                                    </button>
                                    <button type="button" class="rl-custom-icon-btn p-2.5 rounded-xl bg-slate-950/40 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-center transition relative group" title="Foto Spot" data-type="photo">
                                        <i class="fa-solid fa-camera text-lg"></i>
                                    </button>
                                    <button type="button" class="rl-custom-icon-btn p-2.5 rounded-xl bg-slate-950/40 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-center transition relative group" title="Rest Stop / Cafe" data-type="rest">
                                        <i class="fa-solid fa-mug-hot text-lg"></i>
                                    </button>
                                    <button type="button" class="rl-custom-icon-btn p-2.5 rounded-xl bg-slate-950/40 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white text-center transition relative group" title="Toilet" data-type="toilet">
                                        <i class="fa-solid fa-toilet text-lg"></i>
                                    </button>
                                </div>
                                <div id="rl-custom-marker-hint" class="mt-3 text-[10px] font-black text-slate-500 tracking-wide uppercase hidden">
                                    <span class="text-white animate-pulse">●</span> Tap peta untuk menaruh marker
                                </div>
                            </div>

                            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4">
                                <div class="text-xs font-black tracking-wider text-slate-200 uppercase">Tampilan</div>
                                <div class="mt-3 grid grid-cols-2 gap-3">
                                    <div class="flex items-center justify-between gap-3 bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-3">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Route</div>
                                        <input id="rl-color-route" type="color" value="#FC4C02" class="w-10 h-8 bg-transparent">
                                    </div>
                                    <div class="flex items-center justify-between gap-3 bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-3">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Marker</div>
                                        <input id="rl-color-marker" type="color" value="#60a5fa" class="w-10 h-8 bg-transparent">
                                    </div>
                                    <div class="flex items-center justify-between gap-3 bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-3">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Start</div>
                                        <input id="rl-color-start" type="color" value="#22c55e" class="w-10 h-8 bg-transparent">
                                    </div>
                                    <div class="flex items-center justify-between gap-3 bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-3">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Finish</div>
                                        <input id="rl-color-finish" type="color" value="#ef4444" class="w-10 h-8 bg-transparent">
                                    </div>
                                </div>
                                <div class="mt-3 bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Interval Arah</div>
                                        <div id="rl-arrow-interval-label" class="text-xs font-black text-slate-200">80m</div>
                                    </div>
                                    <input id="rl-arrow-interval" type="range" min="30" max="300" step="10" value="80" class="mt-2 w-full">
                                </div>
                            </div>
                        </div>
                    </div>                    <!-- AI Route Generator -->
                    <div class="bg-slate-900/50 border border-slate-700/60 rounded-2xl p-4 md:p-5 relative overflow-hidden group">
                        
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold tracking-wider text-slate-200 uppercase flex items-center gap-2">
                                <i class="fa-solid fa-route text-slate-400 text-xs"></i>
                                AI Route Generator
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 px-2 py-0.5 rounded border border-slate-700">BETA</span>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="text-[11px] text-slate-500 bg-slate-900/30 border border-slate-800/40 rounded-xl p-3">
                                <strong>Tips:</strong> Klik peta 1x terlebih dahulu jika ingin menentukan lokasi Start sendiri. Jika tidak, AI akan menggunakan titik tengah peta saat ini.
                            </div>
                            
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jarak Target (km)</label>
                                <div class="mt-1 grid grid-cols-4 gap-2">
                                    <button type="button" class="rl-ai-dist-btn py-2 rounded-xl bg-slate-700 border border-slate-500 text-white font-bold transition text-sm active" data-dist="5">5K</button>
                                    <button type="button" class="rl-ai-dist-btn py-2 rounded-xl bg-transparent border border-slate-700 text-slate-400 font-bold hover:border-slate-500 hover:text-white transition text-sm" data-dist="10">10K</button>
                                    <button type="button" class="rl-ai-dist-btn py-2 rounded-xl bg-transparent border border-slate-700 text-slate-400 font-bold hover:border-slate-500 hover:text-white transition text-sm" data-dist="21">21K</button>
                                    <div class="relative">
                                        <input id="rl-ai-custom-dist" type="number" step="0.5" min="1" max="100" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-2 py-2 text-white font-bold text-center text-sm focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500" placeholder="Lainnya">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Arah Rute</label>
                                <select id="rl-ai-direction" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-xs font-bold focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500">
                                    <option value="random">Acak / Sembarang Arah</option>
                                    <option value="north">Utara</option>
                                    <option value="east">Timur</option>
                                    <option value="south">Selatan</option>
                                    <option value="west">Barat</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Opsi Rute & Larangan</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-700/60 rounded-xl px-3 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-600 transition">
                                        <input id="rl-ai-loop" type="checkbox" class="accent-white" checked>
                                        Kembali ke Start
                                    </label>
                                    <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-700/60 rounded-xl px-3 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-600 transition">
                                        <input id="rl-ai-avoid-toll" type="checkbox" class="accent-white" checked disabled>
                                        Hindari Jalan Tol ⚠️
                                    </label>
                                    <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-700/60 rounded-xl px-3 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-600 transition">
                                        <input id="rl-ai-avoid-main" type="checkbox" class="accent-white" checked>
                                        Hindari Jalan Besar
                                    </label>
                                    <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-700/60 rounded-xl px-3 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-600 transition">
                                        <input id="rl-ai-avoid-rail" type="checkbox" class="accent-white" checked>
                                        Hindari Rel Kereta
                                    </label>
                                    <label class="flex items-center gap-2 bg-slate-900/40 border border-slate-700/60 rounded-xl px-3 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer col-span-2 hover:border-slate-600 transition">
                                        <input id="rl-ai-avoid-intersection" type="checkbox" class="accent-white">
                                        Hindari Perempatan Utama (Simpang)
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <button id="rl-ai-generate-btn" type="button" class="py-3 rounded-xl bg-slate-100 text-slate-900 font-semibold text-xs hover:bg-white transition flex items-center justify-center gap-1.5 duration-200">
                                    <i class="fa-solid fa-route text-xs"></i>
                                    Gambar Rute
                                </button>
                                <button id="rl-ai-regenerate-btn" type="button" class="py-3 rounded-xl bg-transparent border border-slate-600 text-slate-400 hover:border-slate-400 hover:text-white font-semibold text-xs transition flex items-center justify-center gap-1.5 duration-200">
                                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                    Reset & Ulang
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-8 space-y-4">
                    <div id="rl-summary-panel" class="rl-mobile-sticky-ringkasan lg:relative lg:bottom-auto lg:left-auto lg:right-auto lg:z-10 lg:rounded-2xl lg:border lg:border-slate-700/50 lg:bg-card/90 lg:p-5 lg:shadow-none transition-all duration-300">
                        <!-- Mobile Sheet Toggle / Compact Info Header -->
                        <div id="rl-mobile-sheet-toggle" class="py-1 cursor-pointer lg:hidden flex flex-col items-center justify-center border-b border-slate-800/40 pb-2">
                            <div class="w-12 h-1 bg-slate-700/60 rounded-full mb-2"></div>
                            
                            <!-- Compact View Info (shown when minimized) -->
                            <div id="rl-compact-stats" class="hidden w-full flex items-center justify-between px-2">
                                <div class="text-xs font-black text-slate-200 flex items-center gap-2.5">
                                    <span>Jarak: <span id="rl-distance-km-compact" class="text-neon">0.00</span> km</span>
                                    <span class="text-slate-700">|</span>
                                    <span>Waktu: <span id="rl-est-time-compact" class="text-white">00:00:00</span></span>
                                </div>
                                <div class="text-[10px] font-black text-neon flex items-center gap-1 uppercase tracking-wider">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg>
                                </div>
                            </div>
                            
                            <!-- Action hint when expanded -->
                            <span id="rl-mobile-toggle-hint" class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Tap to Minimize</span>
                        </div>

                        <div id="rl-expanded-content" class="space-y-3 md:space-y-4">
                            <div class="text-sm font-black tracking-wider text-slate-200 uppercase mb-3 hidden lg:block">Ringkasan</div>
                        <div class="grid grid-cols-4 gap-1.5 md:gap-3">
                            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-1.5 md:p-3 text-center md:text-left">
                                <div class="text-[9px] md:text-[11px] text-slate-500 font-bold uppercase tracking-wider">Jarak</div>
                                <div class="mt-0.5 md:mt-1 text-base sm:text-lg md:text-2xl font-black text-white"><span id="rl-distance-km">0.00</span><span class="text-xs md:text-sm text-slate-400 font-bold ml-0.5 md:ml-1">km</span></div>
                            </div>
                            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-1.5 md:p-3 text-center md:text-left">
                                <div class="text-[9px] md:text-[11px] text-slate-500 font-bold uppercase tracking-wider">Estimasi</div>
                                <div class="mt-0.5 md:mt-1 text-base sm:text-lg md:text-2xl font-black text-white"><span id="rl-est-time">00:00:00</span></div>
                            </div>
                            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-1.5 md:p-3 text-center md:text-left">
                                <div class="text-[9px] md:text-[11px] text-slate-500 font-bold uppercase tracking-wider">Titik</div>
                                <div class="mt-0.5 md:mt-1 text-base sm:text-lg md:text-2xl font-black text-white"><span id="rl-points-count">0</span></div>
                            </div>
                            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-1.5 md:p-3 text-center md:text-left">
                                <div class="text-[9px] md:text-[11px] text-slate-500 font-bold uppercase tracking-wider">Rata-rata</div>
                                <div class="mt-0.5 md:mt-1 text-base sm:text-lg md:text-2xl font-black text-white"><span id="rl-avg-seg">0.00</span><span class="text-xs md:text-sm text-slate-400 font-bold ml-0.5 md:ml-1">km/seg</span></div>
                            </div>
                        </div>

                        <div class="mt-3 md:mt-4 grid grid-cols-3 md:grid-cols-4 gap-2">
                            <button id="rl-undo" type="button" class="px-3 py-2.5 md:px-4 md:py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:border-slate-500 hover:bg-slate-700 transition text-sm">
                                Undo
                            </button>
                            <button id="rl-clear" type="button" class="px-3 py-2.5 md:px-4 md:py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:border-red-500/60 hover:text-red-300 transition text-sm">
                                Reset
                            </button>
                            
                            <!-- Toggle Button for Mobile Menu -->
                            <button id="rl-toggle-menu" type="button" class="md:hidden px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:bg-slate-700 transition text-sm flex items-center justify-center gap-1.5">
                                <span>Menu</span>
                                <svg id="rl-toggle-menu-chevron" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <!-- Collapsible buttons container -->
                            <div id="rl-collapsible-buttons" class="hidden md:contents col-span-3 md:col-span-4 grid grid-cols-2 md:grid-cols-4 gap-2 mt-2 md:mt-0">
                                <button id="rl-save" type="button" class="col-span-1 px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition text-sm">
                                    Simpan
                                </button>
                                <button id="rl-load" type="button" class="col-span-1 px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:border-slate-500 hover:bg-slate-700 transition text-sm">
                                    Muat
                                </button>
                                <button id="rl-share" type="button" class="col-span-2 px-4 py-3 rounded-xl bg-indigo-600 text-white font-black hover:bg-indigo-500 transition text-sm">
                                    Share Link
                                </button>
                                <button id="rl-export-image" type="button" class="col-span-2 md:col-span-1 px-4 py-3 rounded-xl bg-pink-600 text-white font-black hover:bg-pink-500 transition text-sm">
                                    Export IMG
                                </button>
                                <button id="rl-export-gpx" type="button" class="col-span-2 md:col-span-1 px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:border-slate-500 hover:bg-slate-700 transition text-sm">
                                    Export GPX
                                </button>
                                <button id="rl-import-gpx" type="button" class="col-span-2 md:col-span-1 px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:border-slate-500 hover:bg-slate-700 transition text-sm">
                                    Import GPX
                                </button>
                            </div>
                            <input id="rl-import-gpx-file" type="file" accept=".gpx" class="hidden">
                        </div>

                        <section class="mt-3 bg-slate-900/40 border border-slate-800 rounded-2xl p-4" id="strava-form-panel">
                            @php($hasStrava = auth()->check() && auth()->user() && auth()->user()->strava_access_token && auth()->user()->strava_refresh_token)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-9 h-9 rounded-xl bg-[#FC4C02]/15 border border-[#FC4C02]/40 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-[#FC4C02]" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white">Export ke Strava</div>
                                        <div class="text-xs text-slate-500 font-semibold">Isi form lalu post activity otomatis setelah authorize.</div>
                                    </div>
                                </div>
                                <button id="rl-strava-toggle" type="button" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:bg-slate-700 transition text-sm">
                                    Buka
                                </button>
                            </div>

                            <div id="rl-strava-panel-body" class="hidden mt-4 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Aktivitas</label>
                                        <input id="rl-strava-name" type="text" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60" placeholder="Mis. Easy Run">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal & Waktu</label>
                                        <input id="rl-strava-start" type="datetime-local" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Device</label>
                                        <select id="rl-strava-device" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60">
                                            <option value="">Pilih device</option>
                                            <option>Garmin</option>
                                            <option>Coros</option>
                                            <option>Polar</option>
                                            <option>Suunto</option>
                                            <option>Apple Watch</option>
                                            <option>Android Phone</option>
                                            <option>iPhone</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pace (menit/km)</label>
                                        <input id="rl-strava-pace" type="text" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60" placeholder="Contoh: 4:30">
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg HR</label>
                                        <input id="rl-strava-hr" type="number" min="30" max="250" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60" placeholder="BPM">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg Cadence</label>
                                        <input id="rl-strava-cadence" type="number" min="60" max="300" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60" placeholder="SPM">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg Power</label>
                                        <input id="rl-strava-power" type="number" min="0" max="2000" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#FC4C02]/25 focus:border-[#FC4C02]/60" placeholder="W">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-300">
                                        <input id="rl-strava-private" type="checkbox" class="accent-[#FC4C02]">
                                        Private
                                    </label>
                                    <div class="text-xs text-slate-500 font-semibold">Rute diubah jadi GPX lalu diupload ke Strava.</div>
                                </div>

                                @auth
                                    @if($hasStrava)
                                        <form id="rl-strava-direct-form" method="POST" action="{{ route('tools.buat-rute-lari.strava-upload') }}">
                                            @csrf
                                            <input type="hidden" name="points_json" id="rl-strava-points-json-direct">
                                            <input type="hidden" name="name" id="rl-strava-name-direct">
                                            <input type="hidden" name="start_at" id="rl-strava-start-direct">
                                            <input type="hidden" name="device" id="rl-strava-device-direct">
                                            <input type="hidden" name="pace_text" id="rl-strava-pace-direct">
                                            <input type="hidden" name="hr" id="rl-strava-hr-direct">
                                            <input type="hidden" name="cadence" id="rl-strava-cadence-direct">
                                            <input type="hidden" name="power" id="rl-strava-power-direct">
                                            <input type="hidden" name="private" id="rl-strava-private-direct" value="0">
                                            <button id="rl-strava-submit-direct" type="submit" class="w-full px-4 py-3 rounded-xl bg-[#FC4C02] text-white font-black hover:bg-[#E34402] transition">
                                                Export ke Strava
                                            </button>
                                        </form>
                                    @else
                                        <form id="rl-strava-authorize-form" method="POST" action="{{ route('tools.buat-rute-lari.strava-authorize-and-post') }}">
                                            @csrf
                                            <input type="hidden" name="points_json" id="rl-strava-points-json-auth">
                                            <input type="hidden" name="name" id="rl-strava-name-auth">
                                            <input type="hidden" name="start_at" id="rl-strava-start-auth">
                                            <input type="hidden" name="device" id="rl-strava-device-auth">
                                            <input type="hidden" name="pace_text" id="rl-strava-pace-auth">
                                            <input type="hidden" name="hr" id="rl-strava-hr-auth">
                                            <input type="hidden" name="cadence" id="rl-strava-cadence-auth">
                                            <input type="hidden" name="power" id="rl-strava-power-auth">
                                            <input type="hidden" name="private" id="rl-strava-private-auth" value="0">
                                            <button id="rl-strava-submit-auth" type="submit" class="w-full px-4 py-3 rounded-xl bg-[#FC4C02] text-white font-black hover:bg-[#E34402] transition">
                                                Authorize & Post
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <a href="{{ route('login') }}" class="w-full px-4 py-3 rounded-xl bg-[#FC4C02] text-white font-black hover:bg-[#E34402] transition inline-flex items-center justify-center">
                                            Login untuk Post
                                        </a>
                                        <a href="{{ route('register') }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white font-black hover:bg-slate-700 transition inline-flex items-center justify-center">
                                            Daftar Akun
                                        </a>
                                    </div>
                                @endauth
                            </div>
                        </section>

                        <div class="mt-4 text-xs text-slate-500 leading-relaxed">
                            Tips: titik bisa di-drag buat rapihin rute. Kalau mau cepat, zoom-in dulu baru tap.
                        </div>
                        </div> <!-- End of rl-expanded-content -->
                    </div>
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative">
                        <div class="absolute top-3 right-3 z-[500] flex flex-col gap-2">
                            <button id="rl-center" type="button" class="w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-white hover:border-neon transition flex items-center justify-center shadow-lg" title="Center Map">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </button>
                            <button id="rl-fit" type="button" class="w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-white hover:border-neon transition flex items-center justify-center shadow-lg" title="Fit Route">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                            </button>
                        </div>
                        <div id="rl-route-map"></div>
                    </div>

                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-5">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <div class="text-sm font-black tracking-wider text-slate-200 uppercase">Profil Elevasi</div>
                                <div id="rl-elev-sub" class="text-xs text-slate-500 font-semibold mt-1">Buat rute dulu untuk lihat grafik.</div>
                            </div>
                            <div id="rl-elev-meta" class="text-xs text-slate-500 font-bold"></div>
                        </div>
                        <div class="mt-3 bg-slate-950/40 border border-slate-800 rounded-2xl overflow-hidden">
                            <svg id="rl-elev-svg" viewBox="0 0 1000 220" preserveAspectRatio="none" class="w-full h-[220px] block"></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="rl-modal" class="fixed inset-0 z-[9999] hidden">
            <div class="absolute inset-0 bg-black/60"></div>
            <div class="absolute inset-x-0 top-20 md:top-24 mx-auto max-w-xl px-4">
                <div class="bg-slate-900 border border-slate-700 rounded-2xl overflow-hidden shadow-2xl">
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                        <div id="rl-modal-title" class="text-sm font-black tracking-wider text-white uppercase">Muat Rute</div>
                        <button id="rl-modal-close" type="button" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-black hover:bg-slate-700 transition">
                            Tutup
                        </button>
                    </div>
                    <div id="rl-modal-body" class="p-4 max-h-[65vh] overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

    <!-- Hidden Export Card -->
    <div id="rl-export-card" style="position: fixed; left: -9999px; top: 0; width: 800px; height: 1000px; background: #0f172a; font-family: 'Inter', sans-serif; overflow: hidden;">
        <!-- Background Gradient -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-800 via-slate-950 to-black opacity-80"></div>
        
        <!-- Route SVG Container -->
        <div class="absolute inset-0 flex items-center justify-center p-20 pb-64">
            <svg id="rl-export-svg" width="100%" height="100%" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid meet" style="filter: drop-shadow(0 0 15px rgba(204, 255, 0, 0.4));">
                <!-- Path will be injected here -->
            </svg>
        </div>

        <!-- Header -->
        <div class="absolute top-0 left-0 right-0 p-12 flex justify-between items-start z-10">
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter text-white">RUANG <span class="text-[#ccff00]">LARI</span></h1>
                <p class="text-slate-400 font-bold tracking-widest text-sm mt-1 uppercase">Route Builder</p>
            </div>
            <div class="text-right">
                <div class="text-5xl font-black text-white tracking-tighter leading-snug" id="rl-export-dist">0.00</div>
                <div class="text-xl font-bold text-slate-400 uppercase tracking-wider leading-snug mt-2">Kilometers</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-12 bg-gradient-to-t from-black via-black/90 to-transparent pt-32 z-10">
            <div class="grid grid-cols-2 gap-12">
                <div class="space-y-6">
                    <div class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Elevation Profile</div>
                    <!-- Mini Elevation Graph -->
                    <div class="h-24 w-full relative">
                        <svg id="rl-export-elev-svg" width="100%" height="100%" viewBox="0 0 1000 220" preserveAspectRatio="none">
                            <!-- Elev Path -->
                        </svg>
                    </div>
                    <div class="flex justify-between mt-2 text-sm font-bold text-slate-400">
                        <span id="rl-export-min-elev">0m</span>
                        <span id="rl-export-max-elev">0m</span>
                    </div>

                    <div>
                        <div class="text-xs font-bold text-slate-600 uppercase tracking-widest mb-2">Scan Start</div>
                        <div id="rl-export-qr" class="p-1 bg-white inline-block rounded-lg"></div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <div class="text-xs font-bold text-slate-600 uppercase tracking-widest">Route Name</div>
                        <div class="mt-2 text-2xl font-black text-white leading-[1.35] line-clamp-3 break-words min-h-[6.5rem] py-0.5" id="rl-export-name"> Untitled Route </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <div class="text-xs font-bold text-slate-600 uppercase tracking-widest">Est. Time</div>
                            <div class="text-xl font-bold text-white" id="rl-export-time">00:00:00</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-600 uppercase tracking-widest">Elev Gain</div>
                            <div class="text-xl font-bold text-[#ccff00]" id="rl-export-gain">-</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-[10px] font-mono text-slate-500 border-t border-slate-800 pt-4 mt-2">
                        <div>
                            <span id="rl-export-start-label" class="font-bold">START</span> <span id="rl-export-start">0,0</span>
                        </div>
                        <div>
                            <span id="rl-export-finish-label" class="font-bold">FINISH</span> <span id="rl-export-finish">0,0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        window.RL_MAPBOX_TOKEN = "{{ env('MAPBOX_TOKEN') }}";
    </script>
    <script>
        (function () {
            var elMap = document.getElementById('rl-route-map');
            if (!elMap || !window.L) return;

            var STORAGE_KEY = 'rl.routeBuilder.v1.saved';
            var STYLE_KEY = 'rl.routeBuilder.v1.style';
            var timeIsValid = true;

            var els = {
                status: document.getElementById('rl-route-status'),
                name: document.getElementById('rl-route-name'),
                q: document.getElementById('rl-search-q'),
                searchBtn: document.getElementById('rl-search-btn'),
                results: document.getElementById('rl-search-results'),
                paceMin: document.getElementById('rl-pace-min'),
                paceSec: document.getElementById('rl-pace-sec'),
                startTime: document.getElementById('rl-start-time'),
                startTimeError: document.getElementById('rl-start-time-error'),
                mode: document.getElementById('rl-mode'),
                followRoad: document.getElementById('rl-follow-road'),
                showDirections: document.getElementById('rl-show-directions'),
                colorRoute: document.getElementById('rl-color-route'),
                colorMarker: document.getElementById('rl-color-marker'),
                colorStart: document.getElementById('rl-color-start'),
                colorFinish: document.getElementById('rl-color-finish'),
                arrowInterval: document.getElementById('rl-arrow-interval'),
                arrowIntervalLabel: document.getElementById('rl-arrow-interval-label'),
                distanceKm: document.getElementById('rl-distance-km'),
                estTime: document.getElementById('rl-est-time'),
                distanceKmCompact: document.getElementById('rl-distance-km-compact'),
                estTimeCompact: document.getElementById('rl-est-time-compact'),
                pointsCount: document.getElementById('rl-points-count'),
                avgSeg: document.getElementById('rl-avg-seg'),
                undo: document.getElementById('rl-undo'),
                clear: document.getElementById('rl-clear'),
                save: document.getElementById('rl-save'),
                load: document.getElementById('rl-load'),
                share: document.getElementById('rl-share'),
                exportGpx: document.getElementById('rl-export-gpx'),
                importGpx: document.getElementById('rl-import-gpx'),
                importGpxFile: document.getElementById('rl-import-gpx-file'),
                stravaToggle: document.getElementById('rl-strava-toggle'),
                stravaBody: document.getElementById('rl-strava-panel-body'),
                stravaName: document.getElementById('rl-strava-name'),
                stravaStart: document.getElementById('rl-strava-start'),
                stravaDevice: document.getElementById('rl-strava-device'),
                stravaPace: document.getElementById('rl-strava-pace'),
                stravaHr: document.getElementById('rl-strava-hr'),
                stravaCadence: document.getElementById('rl-strava-cadence'),
                stravaPower: document.getElementById('rl-strava-power'),
                stravaPrivate: document.getElementById('rl-strava-private'),
                stravaDirectForm: document.getElementById('rl-strava-direct-form'),
                stravaAuthorizeForm: document.getElementById('rl-strava-authorize-form'),
                stravaPointsJsonDirect: document.getElementById('rl-strava-points-json-direct'),
                stravaNameDirect: document.getElementById('rl-strava-name-direct'),
                stravaStartDirect: document.getElementById('rl-strava-start-direct'),
                stravaDeviceDirect: document.getElementById('rl-strava-device-direct'),
                stravaPaceDirect: document.getElementById('rl-strava-pace-direct'),
                stravaHrDirect: document.getElementById('rl-strava-hr-direct'),
                stravaCadenceDirect: document.getElementById('rl-strava-cadence-direct'),
                stravaPowerDirect: document.getElementById('rl-strava-power-direct'),
                stravaPrivateDirect: document.getElementById('rl-strava-private-direct'),
                stravaPointsJsonAuth: document.getElementById('rl-strava-points-json-auth'),
                stravaNameAuth: document.getElementById('rl-strava-name-auth'),
                stravaStartAuth: document.getElementById('rl-strava-start-auth'),
                stravaDeviceAuth: document.getElementById('rl-strava-device-auth'),
                stravaPaceAuth: document.getElementById('rl-strava-pace-auth'),
                stravaHrAuth: document.getElementById('rl-strava-hr-auth'),
                stravaCadenceAuth: document.getElementById('rl-strava-cadence-auth'),
                stravaPowerAuth: document.getElementById('rl-strava-power-auth'),
                stravaPrivateAuth: document.getElementById('rl-strava-private-auth'),
                center: document.getElementById('rl-center'),
                fit: document.getElementById('rl-fit'),
                modal: document.getElementById('rl-modal'),
                modalTitle: document.getElementById('rl-modal-title'),
                modalBody: document.getElementById('rl-modal-body'),
                modalClose: document.getElementById('rl-modal-close'),
                elevSub: document.getElementById('rl-elev-sub'),
                elevMeta: document.getElementById('rl-elev-meta'),
                elevSvg: document.getElementById('rl-elev-svg'),
                toggleMenu: document.getElementById('rl-toggle-menu'),
                collapsibleButtons: document.getElementById('rl-collapsible-buttons'),
                toggleChevron: document.getElementById('rl-toggle-menu-chevron'),
                aiCustomDist: document.getElementById('rl-ai-custom-dist'),
                aiDirection: document.getElementById('rl-ai-direction'),
                aiGenerateBtn: document.getElementById('rl-ai-generate-btn'),
                aiRegenerateBtn: document.getElementById('rl-ai-regenerate-btn'),
                aiLoop: document.getElementById('rl-ai-loop'),
                aiAvoidToll: document.getElementById('rl-ai-avoid-toll'),
                aiAvoidMain: document.getElementById('rl-ai-avoid-main'),
                aiAvoidRail: document.getElementById('rl-ai-avoid-rail'),
                aiAvoidIntersection: document.getElementById('rl-ai-avoid-intersection'),
            };

            function setStatus(text) {
                if (els.status) els.status.textContent = text;
            }

            function clamp(n, min, max) {
                if (Number.isNaN(n)) return min;
                return Math.max(min, Math.min(max, n));
            }

            function getStyle() {
                try {
                    var raw = localStorage.getItem(STYLE_KEY);
                    var data = raw ? JSON.parse(raw) : null;
                    if (!data || typeof data !== 'object') data = {};
                    return {
                        route: (data.route && /^#[0-9a-f]{6}$/i.test(data.route)) ? data.route : '#FC4C02',
                        marker: (data.marker && /^#[0-9a-f]{6}$/i.test(data.marker)) ? data.marker : '#60a5fa',
                        start: (data.start && /^#[0-9a-f]{6}$/i.test(data.start)) ? data.start : '#22c55e',
                        finish: (data.finish && /^#[0-9a-f]{6}$/i.test(data.finish)) ? data.finish : '#ef4444',
                        arrowIntervalM: (typeof data.arrowIntervalM === 'number' && data.arrowIntervalM >= 30 && data.arrowIntervalM <= 300) ? data.arrowIntervalM : 80,
                    };
                } catch (e) {
                    return { route: '#FC4C02', marker: '#60a5fa', start: '#22c55e', finish: '#ef4444', arrowIntervalM: 80 };
                }
            }

            function setStyle(next) {
                var cur = getStyle();
                var merged = {
                    route: next.route ?? cur.route,
                    marker: next.marker ?? cur.marker,
                    start: next.start ?? cur.start,
                    finish: next.finish ?? cur.finish,
                    arrowIntervalM: typeof next.arrowIntervalM === 'number' ? next.arrowIntervalM : cur.arrowIntervalM,
                };
                localStorage.setItem(STYLE_KEY, JSON.stringify(merged));
                applyStyleFromState(merged);
            }

            function applyStyleFromState(style) {
                if (els.colorRoute) els.colorRoute.value = style.route;
                if (els.colorMarker) els.colorMarker.value = style.marker;
                if (els.colorStart) els.colorStart.value = style.start;
                if (els.colorFinish) els.colorFinish.value = style.finish;
                if (els.arrowInterval) els.arrowInterval.value = String(style.arrowIntervalM);
                if (els.arrowIntervalLabel) els.arrowIntervalLabel.textContent = String(style.arrowIntervalM) + 'm';
                if (typeof routeLayer !== 'undefined' && routeLayer) {
                    routeLayer.setStyle({ color: style.route });
                }
                rebuildMarkers();
                updateDirections();
            }

            function fmt2(n) {
                return (Math.round(n * 100) / 100).toFixed(2);
            }

            function pad2(n) {
                n = Math.floor(Math.max(0, n));
                return String(n).padStart(2, '0');
            }

            function haversineKm(a, b) {
                var R = 6371;
                var toRad = function (d) { return d * Math.PI / 180; };
                var dLat = toRad(b.lat - a.lat);
                var dLon = toRad(b.lng - a.lng);
                var lat1 = toRad(a.lat);
                var lat2 = toRad(b.lat);
                var s = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
                var c = 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
                return R * c;
            }

            function bearingDeg(a, b) {
                var toRad = function (d) { return d * Math.PI / 180; };
                var toDeg = function (r) { return r * 180 / Math.PI; };
                var lat1 = toRad(a.lat);
                var lat2 = toRad(b.lat);
                var dLon = toRad(b.lng - a.lng);
                var y = Math.sin(dLon) * Math.cos(lat2);
                var x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
                var brng = toDeg(Math.atan2(y, x));
                brng = (brng + 360) % 360;
                return brng;
            }

            function makeDotIcon(color, label) {
                var hasLabel = !!label;
                var html =
                    '<div style="width:18px;height:18px;border-radius:999px;background:' + color + ';border:2px solid #0b1220;box-shadow:0 10px 22px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;">'
                    + (hasLabel ? '<div style="font-size:10px;line-height:10px;font-weight:900;color:#0b1220;">' + label + '</div>' : '')
                    + '</div>';
                return L.divIcon({
                    className: '',
                    html: html,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                });
            }

            function makeFaIcon(color, faClass) {
                var extraStyle = '';
                if (faClass.indexOf('fa-play') !== -1) {
                    extraStyle = 'margin-left:2px;';
                }
                var html =
                    '<div style="width:24px;height:24px;border-radius:999px;background:' + color + ';border:2px solid #0b1220;box-shadow:0 4px 10px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;color:#0b1220;font-size:11px;font-weight:900;">'
                    + '<i class="' + faClass + '" style="' + extraStyle + '"></i>'
                    + '</div>';
                return L.divIcon({
                    className: '',
                    html: html,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                });
            }

            function parsePaceSecPerKm() {
                var m = clamp(parseInt(els.paceMin.value || '0', 10), 0, 59);
                var s = clamp(parseInt(els.paceSec.value || '0', 10), 0, 59);
                return (m * 60) + s;
            }

            function fmtHMS(totalSec) {
                totalSec = Math.max(0, Math.round(totalSec));
                var h = Math.floor(totalSec / 3600);
                var m = Math.floor((totalSec % 3600) / 60);
                var s = totalSec % 60;
                return pad2(h) + ':' + pad2(m) + ':' + pad2(s);
            }

            function toLatLngArray(points) {
                return points.map(function (p) { return [p.lat, p.lng]; });
            }

            function getSaved() {
                try {
                    var raw = localStorage.getItem(STORAGE_KEY);
                    var data = raw ? JSON.parse(raw) : [];
                    return Array.isArray(data) ? data : [];
                } catch (e) {
                    return [];
                }
            }

            function setSaved(items) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            }

            function openModal(title, bodyElBuilder) {
                els.modalTitle.textContent = title;
                els.modalBody.innerHTML = '';
                bodyElBuilder(els.modalBody);
                els.modal.classList.remove('hidden');
            }

            function closeModal() {
                els.modal.classList.add('hidden');
            }

            els.modalClose.addEventListener('click', closeModal);
            els.modal.addEventListener('click', function (e) {
                if (e.target === els.modal || e.target === els.modal.firstElementChild) closeModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeModal();
            });

            var map = L.map('rl-route-map', {
                zoomControl: true,
                attributionControl: true,
            }).setView([-6.200000, 106.816666], 12);

            var mapboxToken = window.RL_MAPBOX_TOKEN;
            
            var getMapboxUrl = function(style) {
                return 'https://api.mapbox.com/styles/v1/mapbox/' + style + '/tiles/{z}/{x}/{y}?access_token=' + mapboxToken;
            };

            var getMapboxOpts = function() {
                return {
                    maxZoom: 19,
                    tileSize: 512,
                    zoomOffset: -1,
                    attribution: '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                };
            };

            var baseLayers = {};
            
            if (mapboxToken) {
                var outdoors = L.tileLayer(getMapboxUrl('outdoors-v12'), getMapboxOpts());
                var satellite = L.tileLayer(getMapboxUrl('satellite-streets-v12'), getMapboxOpts());
                var dark = L.tileLayer(getMapboxUrl('navigation-night-v1'), getMapboxOpts());

                baseLayers = {
                    "Peta Lari (Outdoors)": outdoors,
                    "Satelit": satellite,
                    "Mode Gelap": dark
                };

                outdoors.addTo(map);
            } else {
                var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap',
                });
                baseLayers = { "OpenStreetMap": osm };
                osm.addTo(map);
            }

            if (Object.keys(baseLayers).length > 1) {
                L.control.layers(baseLayers, null, { position: 'bottomright' }).addTo(map);
            }

            var routeLayer = L.featureGroup().addTo(map);

            var points = [];
            var routePoints = []; // Flattened array of {lat, lng} for stats/export
            var markers = [];
            var freehandActive = false;
            var directionLayer = L.layerGroup().addTo(map);
            var kmLayer = L.layerGroup().addTo(map);
            var routingSeq = 0;
            var elevSeq = 0;

            // History State
            var historyStack = [];
            var historyIndex = -1;
            var isUndoing = false;

            var customMarkers = []; // Array of {lat, lng, type, label, markerInstance}

            function pushState() {
                if (isUndoing) return;
                
                var stateObj = {
                    points: points,
                    customMarkers: customMarkers.map(function(cm) {
                        return {
                            lat: cm.lat,
                            lng: cm.lng,
                            type: cm.type,
                            label: cm.label
                        };
                    })
                };
                
                var state = JSON.stringify(stateObj);
                if (historyIndex < historyStack.length - 1) {
                    historyStack = historyStack.slice(0, historyIndex + 1);
                }
                if (historyStack.length === 0 || historyStack[historyStack.length - 1] !== state) {
                    historyStack.push(state);
                    historyIndex = historyStack.length - 1;
                }

                // AUTO-SAVE DRAFT TO LOCALSTORAGE
                try {
                    localStorage.setItem('rl.routeBuilder.v1.draft', state);
                } catch(e) {
                    console.error('Failed to auto-save draft:', e);
                }
            }

            function loadState(idx) {
                if (idx < 0 || idx >= historyStack.length) return;
                isUndoing = true;
                try {
                    var parsed = JSON.parse(historyStack[idx]);
                    var loadedPoints = [];
                    var loadedCustom = [];
                    
                    if (Array.isArray(parsed)) {
                        loadedPoints = parsed;
                    } else if (parsed && parsed.points) {
                        loadedPoints = parsed.points;
                        loadedCustom = parsed.customMarkers || [];
                    }
                    
                    points = loadedPoints.map(function(p) {
                        if (typeof p.mode === 'undefined') {
                            return {
                                lat: p.lat,
                                lng: p.lng,
                                mode: (els.followRoad && els.followRoad.checked) ? 'osrm' : 'direct',
                                segment: null,
                                iconType: p.iconType || ''
                            };
                        }
                        return p;
                    });
                    
                    // Clear existing custom markers
                    customMarkers.forEach(function(cm) {
                        if (cm.markerInstance) map.removeLayer(cm.markerInstance);
                    });
                    customMarkers = [];
                    
                    // Re-render custom markers
                    loadedCustom.forEach(function(cm) {
                        addCustomMarker(cm, cm.type, cm.label);
                    });
                    
                    processLoadedPoints().then(function() {
                        rebuildLine();
                        rebuildMarkers();
                        updateStats();
                        updateElevation();
                        historyIndex = idx;
                        setStatus(idx === 0 ? 'Awal' : 'History ' + (idx + 1));
                    });

                } catch(e) {
                    console.error('LoadState Error:', e);
                }
                isUndoing = false;
            }

            function processLoadedPoints() {
                // Ensure all points have valid segments
                // This mimics "updateRouteFromWaypoints" but per segment
                var promises = [];
                for (var i = 1; i < points.length; i++) {
                    var prev = points[i-1];
                    var curr = points[i];
                    if (!curr.segment || curr.segment.length === 0) {
                        if (curr.mode === 'osrm') {
                            // Fetch OSRM
                            (function(index, p1, p2) {
                                promises.push(osrmRoute([p1, p2]).then(function(seg) {
                                    points[index].segment = seg;
                                }).catch(function() {
                                    points[index].segment = [p1, p2]; // Fallback
                                }));
                            })(i, prev, curr);
                        } else {
                            curr.segment = [prev, curr];
                        }
                    }
                }
                return Promise.all(promises);
            }

            function rebuildLine() {
                routeLayer.clearLayers();
                routePoints = [];
                
                if (points.length > 0) {
                    routePoints.push({ lat: points[0].lat, lng: points[0].lng });
                }

                for (var i = 1; i < points.length; i++) {
                    var seg = points[i].segment;
                    if (!seg || seg.length < 2) {
                        // Fallback if segment missing
                        seg = [points[i-1], points[i]];
                    }
                    
                    // Add to flat routePoints (skip first point of segment as it duplicates prev point)
                    // OSRM usually returns [start, ..., end]. Start is same as prev point.
                    for (var j = 1; j < seg.length; j++) {
                        routePoints.push(seg[j]);
                    }

                    // Visual Style
                    var color = getStyle().route;
                    var dashArray = null;
                    if (points[i].mode === 'direct') {
                        // Dashed for manual
                        dashArray = '10, 10'; 
                    }

                    L.polyline(seg, {
                        color: color,
                        weight: 4,
                        opacity: 0.9,
                        dashArray: dashArray
                    }).addTo(routeLayer);
                }
                
                updateDirections();
                updateKmMarkers();
            }

            function rebuildMarkers() {
                var style = getStyle();
                markers.forEach(function (m) { map.removeLayer(m); });
                markers = [];
                points.forEach(function (p, idx) {
                    var icon = makeDotIcon(style.marker, '');
                    if (idx === 0) {
                        icon = makeFaIcon(style.start, 'fa-solid fa-play');
                    } else if (idx === points.length - 1) {
                        icon = makeFaIcon(style.finish, 'fa-solid fa-flag-checkered');
                    } else if (p.iconType) {
                        var faClass = 'fa-droplet';
                        var iconColor = '#60a5fa';
                        if (p.iconType === 'water') {
                            faClass = 'fa-droplet';
                            iconColor = '#38bdf8';
                        } else if (p.iconType === 'warning') {
                            faClass = 'fa-triangle-exclamation';
                            iconColor = '#fb923c';
                        } else if (p.iconType === 'intersection') {
                            faClass = 'fa-arrows-split-up-and-left';
                            iconColor = '#a3e635';
                        } else if (p.iconType === 'photo') {
                            faClass = 'fa-camera';
                            iconColor = '#f472b6';
                        } else if (p.iconType === 'rest') {
                            faClass = 'fa-mug-hot';
                            iconColor = '#fbbf24';
                        } else if (p.iconType === 'toilet') {
                            faClass = 'fa-toilet';
                            iconColor = '#c084fc';
                        }
                        icon = makeFaIcon(iconColor, 'fa-solid ' + faClass);
                    }
                    var m = L.marker([p.lat, p.lng], { draggable: true, icon: icon });
                    
                    m.on('drag', function (ev) {
                        var ll = ev.target.getLatLng();
                        points[idx].lat = ll.lat;
                        points[idx].lng = ll.lng;
                        
                        // Update adjacent segments to direct lines during drag for performance
                        if (idx > 0) {
                            points[idx].segment = [points[idx-1], points[idx]];
                        }
                        if (idx < points.length - 1) {
                            points[idx+1].segment = [points[idx], points[idx+1]];
                        }
                        
                        rebuildLine();
                        updateStats();
                    });

                    m.on('dragend', function () {
                        // Recalculate OSRM if needed
                        recalculateAdjacentSegments(idx).then(function() {
                            rebuildLine();
                            updateStats();
                            updateElevation();
                            pushState();
                        });
                    });

                    m.on('click', function () {
                        setStatus('Titik #' + (idx + 1) + ' (' + p.mode + ')');
                    });
                    
                    // Popup delete
                    var container = document.createElement('div');
                    container.className = 'text-center p-1 space-y-2';
                    
                    // Delete Button
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full px-3 py-1.5 bg-red-500 text-white rounded-lg font-bold text-[11px] hover:bg-red-600 transition shadow-lg shadow-red-500/20';
                    btn.textContent = 'Hapus Titik';
                    btn.onclick = function() {
                        map.closePopup();
                        removePoint(idx);
                    };
                    container.appendChild(btn);

                    // Mode Toggle for this segment (if not start)
                    if (idx > 0) {
                        var btnMode = document.createElement('button');
                        btnMode.type = 'button';
                        btnMode.className = 'w-full px-3 py-1.5 bg-slate-700 text-white rounded-lg font-bold text-[11px] hover:bg-slate-600 transition';
                        btnMode.textContent = p.mode === 'osrm' ? 'Ubah ke Manual' : 'Ubah ke OSRM';
                        btnMode.onclick = function() {
                            map.closePopup();
                            togglePointMode(idx);
                        };
                        container.appendChild(btnMode);
                    }

                    // Icon selector (only for intermediate points)
                    if (idx > 0 && idx < points.length - 1) {
                        var selectDiv = document.createElement('div');
                        selectDiv.className = 'mt-2 text-left';
                        
                        var selectLabel = document.createElement('label');
                        selectLabel.className = 'text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1';
                        selectLabel.textContent = 'Ganti Icon Titik';
                        selectDiv.appendChild(selectLabel);
                        
                        var select = document.createElement('select');
                        select.className = 'w-full bg-slate-900 border border-slate-700 text-white text-[11px] px-2 py-1.5 rounded focus:outline-none focus:ring-1 focus:ring-neon';
                        
                        var options = [
                            { value: '', text: '● Dot Biasa' },
                            { value: 'water', text: '💧 Water Station' },
                            { value: 'warning', text: '⚠️ Warning' },
                            { value: 'intersection', text: '🔀 Intersection' },
                            { value: 'photo', text: '📷 Foto Spot' },
                            { value: 'rest', text: '☕ Rest Stop' },
                            { value: 'toilet', text: '🚽 Toilet' }
                        ];
                        
                        options.forEach(function(opt) {
                            var o = document.createElement('option');
                            o.value = opt.value;
                            o.textContent = opt.text;
                            if (p.iconType === opt.value) {
                                o.selected = true;
                            }
                            select.appendChild(o);
                        });
                        
                        select.onchange = function() {
                            points[idx].iconType = select.value;
                            rebuildMarkers();
                            pushState();
                        };
                        
                        selectDiv.appendChild(select);
                        container.appendChild(selectDiv);
                    }

                    m.bindPopup(container, { minWidth: 130 });

                    m.addTo(map);
                    markers.push(m);
                });
            }

            function recalculateAdjacentSegments(idx) {
                var promises = [];
                // Update segment leading TO this point
                if (idx > 0 && points[idx].mode === 'osrm') {
                    promises.push(osrmRoute([points[idx-1], points[idx]]).then(function(seg) {
                        points[idx].segment = seg;
                    }).catch(function() {
                        points[idx].segment = [points[idx-1], points[idx]];
                    }));
                } else if (idx > 0) {
                    points[idx].segment = [points[idx-1], points[idx]];
                }

                // Update segment leading FROM this point
                if (idx < points.length - 1 && points[idx+1].mode === 'osrm') {
                    promises.push(osrmRoute([points[idx], points[idx+1]]).then(function(seg) {
                        points[idx+1].segment = seg;
                    }).catch(function() {
                        points[idx+1].segment = [points[idx], points[idx+1]];
                    }));
                } else if (idx < points.length - 1) {
                    points[idx+1].segment = [points[idx], points[idx+1]];
                }
                
                return Promise.all(promises);
            }

            function togglePointMode(idx) {
                if (idx <= 0 || idx >= points.length) return;
                var p = points[idx];
                p.mode = p.mode === 'osrm' ? 'direct' : 'osrm';
                setStatus('Mode titik #' + (idx+1) + ' diubah ke ' + p.mode);
                recalculateAdjacentSegments(idx).then(function() {
                    rebuildLine();
                    rebuildMarkers(); // Rebuild markers to update popup text
                    updateStats();
                    updateElevation();
                    pushState();
                });
            }

            function removePoint(idx) {
                if (idx < 0 || idx >= points.length) return;
                points.splice(idx, 1);
                
                // If we removed a point, the point that was at idx+1 (now at idx) 
                // needs to connect to idx-1.
                if (idx > 0 && idx < points.length) {
                    // Recalculate segment for points[idx] (which connects to points[idx-1])
                    recalculateAdjacentSegments(idx).then(done);
                } else {
                    done();
                }

                function done() {
                    rebuildLine();
                    rebuildMarkers();
                    updateStats();
                    updateElevation();
                    setStatus('Titik dihapus');
                    pushState();
                }
            }

            function addPoint(latlng) {
                var mode = (els.followRoad && els.followRoad.checked) ? 'osrm' : 'direct';
                var newPoint = { lat: latlng.lat, lng: latlng.lng, mode: mode, segment: [] };
                
                if (points.length > 0) {
                    var prev = points[points.length - 1];
                    // Initial direct line for instant feedback
                    newPoint.segment = [prev, newPoint];
                }

                points.push(newPoint);
                var idx = points.length - 1;
                
                rebuildLine();
                rebuildMarkers();
                updateStats();
                setStatus('Titik ditambahkan (' + points.length + ')');
                
                if (idx > 0 && mode === 'osrm') {
                    setStatus('Routing...');
                    recalculateAdjacentSegments(idx).then(function() {
                        rebuildLine();
                        updateStats();
                        updateElevation();
                        setStatus('Titik ditambahkan (OSRM)');
                        pushState();
                    }).catch(function() {
                        setStatus('Routing gagal, gunakan garis lurus');
                        pushState();
                    });
                } else {
                    pushState();
                    updateElevation();
                }
            }

            function totalDistanceKm() {
                if (routePoints.length < 2) return 0;
                var total = 0;
                for (var i = 1; i < routePoints.length; i++) {
                    total += haversineKm(routePoints[i - 1], routePoints[i]);
                }
                return total;
            }

            function formatDateTimeDisplay(d) {
                var pad = function (n) { return String(n).padStart(2, '0'); };
                return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            }

            function updateStats() {
                var dist = totalDistanceKm();
                var segAvg = points.length > 1 ? (dist / (points.length - 1)) : 0;
                var pace = parsePaceSecPerKm();
                var est = dist * pace;

                els.distanceKm.textContent = fmt2(dist);
                els.pointsCount.textContent = String(points.length);
                els.avgSeg.textContent = fmt2(segAvg);
                els.estTime.textContent = fmtHMS(est);
                
                if (els.distanceKmCompact) els.distanceKmCompact.textContent = fmt2(dist);
                if (els.estTimeCompact) els.estTimeCompact.textContent = fmtHMS(est);

                // Start time & Finish time validation
                if (els.startTime) {
                    var startTimeVal = els.startTime.value;
                    var startDt = startTimeVal ? new Date(startTimeVal) : null;
                    if (startDt && !isNaN(startDt.getTime())) {
                        var now = new Date();
                        var durationMs = est * 1000;
                        var finishDt = new Date(startDt.getTime() + durationMs);
                        var isStartInFuture = startDt > now;
                        var isFinishInFuture = finishDt > now;
                        var msg = '';
                        if (isStartInFuture) {
                            msg = 'Waktu start tidak boleh melebihi waktu sekarang!';
                        } else if (isFinishInFuture) {
                            msg = 'Waktu finish (' + formatDateTimeDisplay(finishDt) + ') tidak boleh melebihi waktu sekarang!';
                        }

                        if (msg) {
                            if (els.startTimeError) {
                                els.startTimeError.textContent = msg;
                                els.startTimeError.classList.remove('hidden');
                            }
                            els.startTime.classList.add('border-red-500', 'text-red-400');
                            els.startTime.classList.remove('border-slate-700');
                            timeIsValid = false;
                        } else {
                            if (els.startTimeError) {
                                els.startTimeError.classList.add('hidden');
                            }
                            els.startTime.classList.remove('border-red-500', 'text-red-400');
                            els.startTime.classList.add('border-slate-700');
                            timeIsValid = true;
                        }
                    } else {
                        if (els.startTimeError) {
                            els.startTimeError.classList.add('hidden');
                        }
                        els.startTime.classList.remove('border-red-500', 'text-red-400');
                        els.startTime.classList.add('border-slate-700');
                        timeIsValid = true;
                    }
                }
            }

            // [Duplicate addPoint removed]

            function undo() {
                if (historyIndex > 0) {
                    loadState(historyIndex - 1);
                }
            }
            
            function redo() {
                if (historyIndex < historyStack.length - 1) {
                    loadState(historyIndex + 1);
                }
            }

            function clearAll() {
                points = [];
                routePoints = [];
                customMarkers.forEach(function(cm) {
                    if (cm.markerInstance) map.removeLayer(cm.markerInstance);
                });
                customMarkers = [];
                
                rebuildLine();
                rebuildMarkers();
                updateStats();
                updateElevation();
                setStatus('Reset');
                pushState();
            }
            
            // Initial state
            pushState();

            // Keyboard Shortcuts
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                    e.preventDefault();
                    undo();
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'Z'))) {
                    e.preventDefault();
                    redo();
                }
            });

            function fitRoute() {
                if (routePoints.length < 2) return;
                if (typeof routeLayer !== 'undefined' && routeLayer.getLayers().length > 0) {
                    map.fitBounds(routeLayer.getBounds().pad(0.18));
                }
            }

            function centerToUser() {
                if (points.length > 0) {
                    map.setView([points[0].lat, points[0].lng], 16);
                    setStatus('Peta dipusatkan ke Start');
                    return;
                }

                if (!navigator.geolocation) {
                    setStatus('GPS tidak tersedia. Mencari via IP...');
                    getIpLocation();
                    return;
                }
                setStatus('Mencari lokasi...');
                navigator.geolocation.getCurrentPosition(function (pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    map.setView([lat, lng], 16);
                    setStatus('Lokasi ditemukan');
                }, function (err) {
                    console.warn('Geolocation failed:', err);
                    setStatus('Gagal akses GPS. Mencari via IP...');
                    getIpLocation();
                }, { enableHighAccuracy: false, timeout: 5000 });
            }

            function getIpLocation() {
                fetch('https://ipapi.co/json/')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data && data.latitude && data.longitude) {
                            map.setView([data.latitude, data.longitude], 13);
                            setStatus('Lokasi IP ditemukan: ' + (data.city || ''));
                        } else {
                            throw new Error('invalid_data');
                        }
                    })
                    .catch(function(err) {
                        console.error('IP Geolocation failed:', err);
                        map.setView([-6.200000, 106.816666], 12);
                        setStatus('Lokasi default (Jakarta)');
                    });
            }

            function buildShareUrl() {
                if (points.length === 0) return null;
                var base = window.location.origin + window.location.pathname;
                var pts = points.map(function (p) { return p.lat.toFixed(6) + ',' + p.lng.toFixed(6); }).join(';');
                var params = new URLSearchParams();
                if (els.name.value.trim() !== '') params.set('name', els.name.value.trim());
                params.set('pts', pts);
                params.set('pm', clamp(parseInt(els.paceMin.value || '0', 10), 0, 59));
                params.set('ps', clamp(parseInt(els.paceSec.value || '0', 10), 0, 59));
                if (els.followRoad) params.set('snap', els.followRoad.checked ? '1' : '0');
                if (els.showDirections && els.showDirections.checked) params.set('dir', '1');
                var modeStr = points.map(function (p) { return (p && p.mode === 'osrm') ? 'o' : 'd'; }).join('');
                if (modeStr) params.set('m', modeStr);
                if (routePoints.length >= 2) {
                    params.set('rp', encodePolyline(routePoints));
                }
                var style = getStyle();
                params.set('rc', style.route.replace('#', ''));
                params.set('mc', style.marker.replace('#', ''));
                params.set('sc', style.start.replace('#', ''));
                params.set('fc', style.finish.replace('#', ''));
                params.set('ai', String(style.arrowIntervalM));
                return base + '?' + params.toString();
            }

            function encodePolyline(pts) {
                var lastLat = 0;
                var lastLng = 0;
                var res = '';
                for (var i = 0; i < pts.length; i++) {
                    var lat = Math.round(pts[i].lat * 1e5);
                    var lng = Math.round(pts[i].lng * 1e5);
                    res += encodeSigned(lat - lastLat) + encodeSigned(lng - lastLng);
                    lastLat = lat;
                    lastLng = lng;
                }
                return res;
            }

            function encodeSigned(num) {
                var sgn = num << 1;
                if (num < 0) sgn = ~sgn;
                return encodeChunks(sgn);
            }

            function encodeChunks(num) {
                var out = '';
                while (num >= 0x20) {
                    out += String.fromCharCode((0x20 | (num & 0x1f)) + 63);
                    num >>= 5;
                }
                out += String.fromCharCode(num + 63);
                return out;
            }

            function decodePolyline(str) {
                var index = 0;
                var lat = 0;
                var lng = 0;
                var coordinates = [];
                while (index < str.length) {
                    var result = 1;
                    var shift = 0;
                    var b;
                    do {
                        b = str.charCodeAt(index++) - 63 - 1;
                        result += b << shift;
                        shift += 5;
                    } while (b >= 0x1f);
                    lat += (result & 1) ? ~(result >> 1) : (result >> 1);

                    result = 1;
                    shift = 0;
                    do {
                        b = str.charCodeAt(index++) - 63 - 1;
                        result += b << shift;
                        shift += 5;
                    } while (b >= 0x1f);
                    lng += (result & 1) ? ~(result >> 1) : (result >> 1);

                    coordinates.push({ lat: lat / 1e5, lng: lng / 1e5 });
                }
                return coordinates;
            }

            function assignSegmentsFromRoutePoints(routePts) {
                if (!Array.isArray(routePts) || routePts.length < 2) return;
                if (!Array.isArray(points) || points.length < 2) return;
                var startIdx = 0;
                for (var i = 1; i < points.length; i++) {
                    var prev = points[i - 1];
                    var curr = points[i];
                    var prevIdx = findNearestIndex(routePts, prev, startIdx);
                    var currIdx = findNearestIndex(routePts, curr, Math.min(routePts.length - 1, prevIdx + 1));
                    if (currIdx <= prevIdx) currIdx = Math.min(routePts.length - 1, prevIdx + 1);
                    var seg = routePts.slice(prevIdx, currIdx + 1);
                    if (!seg || seg.length < 2) seg = [prev, curr];
                    seg[0] = { lat: prev.lat, lng: prev.lng };
                    seg[seg.length - 1] = { lat: curr.lat, lng: curr.lng };
                    points[i].segment = seg;
                    startIdx = currIdx;
                }
            }

            function findNearestIndex(routePts, target, fromIdx) {
                var bestIdx = fromIdx || 0;
                var best = Infinity;
                var start = clamp(parseInt(fromIdx || 0, 10), 0, routePts.length - 1);
                for (var i = start; i < routePts.length; i++) {
                    var d = sqDist(routePts[i], target);
                    if (d < best) {
                        best = d;
                        bestIdx = i;
                        if (best < 1e-10) break;
                    }
                    if (i > start + 5000 && best < 1e-8) break;
                }
                return bestIdx;
            }

            function sqDist(a, b) {
                var dx = (a.lat - b.lat);
                var dy = (a.lng - b.lng);
                return dx * dx + dy * dy;
            }

            function copyText(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    return navigator.clipboard.writeText(text);
                }
                return new Promise(function (resolve, reject) {
                    try {
                        var ta = document.createElement('textarea');
                        ta.value = text;
                        ta.style.position = 'fixed';
                        ta.style.left = '-9999px';
                        document.body.appendChild(ta);
                        ta.focus();
                        ta.select();
                        var ok = document.execCommand('copy');
                        document.body.removeChild(ta);
                        ok ? resolve() : reject(new Error('copy failed'));
                    } catch (e) {
                        reject(e);
                    }
                });
            }

            function exportGpx() {
                if (!timeIsValid) {
                    setStatus('Error: Waktu tidak valid');
                    showInfoModal('Waktu Tidak Valid', 'Waktu start atau finish aktivitas melebihi waktu sekarang. Harap sesuaikan.');
                    return;
                }
                if (routePoints.length < 2) {
                    setStatus('Minimal 2 titik');
                    return;
                }
                var name = (els.name.value || 'rute-lari').trim() || 'rute-lari';
                var now = new Date().toISOString();
                var seg = routePoints.map(function (p) {
                    return '<trkpt lat="' + p.lat.toFixed(6) + '" lon="' + p.lng.toFixed(6) + '"><time>' + now + '</time></trkpt>';
                }).join('');

                var wpts = customMarkers.map(function(cm) {
                    var sym = 'Waypoint';
                    if (cm.type === 'water') sym = 'Water Source';
                    else if (cm.type === 'warning') sym = 'Danger Area';
                    else if (cm.type === 'intersection') sym = 'Rally Point';
                    else if (cm.type === 'rest') sym = 'Rest Area';
                    else if (cm.type === 'toilet') sym = 'Restroom';
                    
                    var desc = cm.label ? '<desc>' + escapeXml(cm.label) + '</desc>' : '';
                    return '<wpt lat="' + cm.lat.toFixed(6) + '" lon="' + cm.lng.toFixed(6) + '">' +
                           '<name>' + escapeXml(cm.type.toUpperCase() + (cm.label ? ': ' + cm.label : '')) + '</name>' +
                           desc +
                           '<sym>' + sym + '</sym>' +
                           '</wpt>';
                }).join('');

                var gpx = '<?xml version="1.0" encoding="UTF-8"?>' +
                    '<gpx version="1.1" creator="RuangLari" xmlns="http://www.topografix.com/GPX/1/1">' +
                    '<metadata><name>' + escapeXml(name) + '</name><time>' + now + '</time></metadata>' +
                    wpts +
                    '<trk><name>' + escapeXml(name) + '</name><trkseg>' + seg + '</trkseg></trk>' +
                    '</gpx>';

                var blob = new Blob([gpx], { type: 'application/gpx+xml' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = safeFilename(name) + '.gpx';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                setStatus('GPX diunduh');
            }

            function escapeXml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&apos;');
            }

            function safeFilename(str) {
                return String(str).toLowerCase().replace(/[^a-z0-9\-_]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
            }

            function saveCurrent() {
                if (!timeIsValid) {
                    setStatus('Error: Waktu tidak valid');
                    showInfoModal('Waktu Tidak Valid', 'Waktu start atau finish aktivitas melebihi waktu sekarang. Harap sesuaikan.');
                    return;
                }
                if (points.length < 2) {
                    setStatus('Minimal 2 titik');
                    return;
                }
                var name = (els.name.value || '').trim() || ('Rute ' + new Date().toLocaleString('id-ID'));
                var entry = {
                    id: 'rt_' + Date.now() + '_' + Math.random().toString(16).slice(2),
                    name: name,
                    createdAt: new Date().toISOString(),
                    paceMin: clamp(parseInt(els.paceMin.value || '0', 10), 0, 59),
                    paceSec: clamp(parseInt(els.paceSec.value || '0', 10), 0, 59),
                    snap: !!(els.followRoad && els.followRoad.checked),
                    dir: !!(els.showDirections && els.showDirections.checked),
                    points: points.slice(),
                };
                var items = getSaved();
                items.unshift(entry);
                items = items.slice(0, 30);
                setSaved(items);
                setStatus('Tersimpan');
            }

            function loadEntry(entry) {
                points = (entry.points || []).map(function (p) {
                    return {
                        lat: p.lat,
                        lng: p.lng,
                        mode: p.mode || (entry.snap ? 'osrm' : 'direct'),
                        segment: p.segment || [],
                        iconType: p.iconType || ''
                    };
                });
                els.name.value = entry.name || '';
                els.paceMin.value = String(entry.paceMin ?? 6);
                els.paceSec.value = String(entry.paceSec ?? 0);
                if (els.followRoad && typeof entry.snap !== 'undefined') els.followRoad.checked = !!entry.snap;
                if (els.showDirections && typeof entry.dir !== 'undefined') els.showDirections.checked = !!entry.dir;
                
                processLoadedPoints().then(function() {
                    rebuildLine();
                    rebuildMarkers();
                    updateStats();
                    updateElevation();
                    if (points.length >= 2) fitRoute();
                    setStatus('Dimuat');
                    pushState();
                });
            }

            function showLoadModal() {
                var items = getSaved();
                openModal('Muat Rute', function (container) {
                    if (items.length === 0) {
                        var empty = document.createElement('div');
                        empty.className = 'text-sm text-slate-400';
                        empty.textContent = 'Belum ada rute tersimpan.';
                        container.appendChild(empty);
                        return;
                    }
                    var list = document.createElement('div');
                    list.className = 'space-y-2';
                    items.forEach(function (it) {
                        var row = document.createElement('div');
                        row.className = 'flex items-center justify-between gap-3 bg-slate-800/40 border border-slate-700 rounded-xl p-3';
                        var left = document.createElement('div');
                        left.className = 'min-w-0';
                        var t = document.createElement('div');
                        t.className = 'font-black text-white truncate';
                        t.textContent = it.name || 'Untitled';
                        var meta = document.createElement('div');
                        meta.className = 'text-[11px] text-slate-500 font-bold';
                        meta.textContent = (it.points ? it.points.length : 0) + ' titik • ' + (new Date(it.createdAt || Date.now())).toLocaleString('id-ID');
                        left.appendChild(t);
                        left.appendChild(meta);

                        var actions = document.createElement('div');
                        actions.className = 'flex items-center gap-2 shrink-0';
                        var btnLoad = document.createElement('button');
                        btnLoad.type = 'button';
                        btnLoad.className = 'px-3 py-2 rounded-xl bg-neon text-dark font-black';
                        btnLoad.textContent = 'Pakai';
                        btnLoad.addEventListener('click', function () {
                            loadEntry(it);
                            closeModal();
                        });
                        var btnDel = document.createElement('button');
                        btnDel.type = 'button';
                        btnDel.className = 'px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-red-300 font-black hover:border-red-500/60 transition';
                        btnDel.textContent = 'Hapus';
                        btnDel.addEventListener('click', function () {
                            var next = getSaved().filter(function (x) { return x.id !== it.id; });
                            setSaved(next);
                            showLoadModal();
                        });
                        actions.appendChild(btnLoad);
                        actions.appendChild(btnDel);

                        row.appendChild(left);
                        row.appendChild(actions);
                        list.appendChild(row);
                    });
                    container.appendChild(list);
                });
            }

            function showShareModal(url) {
                openModal('Share Link', function (container) {
                    var wrap = document.createElement('div');
                    wrap.className = 'space-y-3';
                    var p = document.createElement('div');
                    p.className = 'text-sm text-slate-300';
                    p.textContent = 'Link ini akan buka rute yang sama (titik + pace).';
                    var input = document.createElement('input');
                    input.type = 'text';
                    input.value = url;
                    input.readOnly = true;
                    input.className = 'w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white font-bold';
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full px-4 py-3 rounded-xl bg-neon text-dark font-black';
                    btn.textContent = 'Copy Link';
                    btn.addEventListener('click', function () {
                        copyText(url).then(function () {
                            setStatus('Link disalin');
                            closeModal();
                        }).catch(function () {
                            setStatus('Gagal copy');
                        });
                    });
                    wrap.appendChild(p);
                    wrap.appendChild(input);
                    wrap.appendChild(btn);
                    container.appendChild(wrap);
                });
            }

            function showInfoModal(title, message) {
                openModal(title, function (container) {
                    var wrap = document.createElement('div');
                    wrap.className = 'space-y-3';
                    var p = document.createElement('div');
                    p.className = 'text-sm text-slate-300 leading-relaxed';
                    p.textContent = message;
                    wrap.appendChild(p);
                    container.appendChild(wrap);
                });
            }

            function toDatetimeLocalValue(d) {
                var pad = function (n) { return String(n).padStart(2, '0'); };
                return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            }

            function syncStravaDefaults() {
                if (!els.stravaName || !els.stravaStart || !els.stravaPace) return;
                if ((els.stravaName.value || '').trim() === '') {
                    var baseName = (els.name.value || '').trim();
                    els.stravaName.value = baseName !== '' ? baseName : 'Easy Run';
                }
                if (els.startTime && els.startTime.value) {
                    els.stravaStart.value = els.startTime.value;
                } else if ((els.stravaStart.value || '').trim() === '') {
                    els.stravaStart.value = toDatetimeLocalValue(new Date());
                }
                if ((els.stravaPace.value || '').trim() === '') {
                    var paceM = clamp(parseInt(els.paceMin.value || '0', 10), 0, 59);
                    var paceS = clamp(parseInt(els.paceSec.value || '0', 10), 0, 59);
                    els.stravaPace.value = paceM + ':' + String(paceS).padStart(2, '0');
                }
            }

            function fillHiddenStravaFields(kind) {
                if (!timeIsValid) {
                    setStatus('Error: Waktu tidak valid');
                    showInfoModal('Waktu Tidak Valid', 'Waktu start atau finish aktivitas melebihi waktu sekarang. Harap sesuaikan.');
                    return false;
                }
                if (points.length < 2) {
                    showInfoModal('Belum ada rute', 'Tambahkan minimal 2 titik di peta sebelum export ke Strava.');
                    return false;
                }

                syncStravaDefaults();

                var name = (els.stravaName && els.stravaName.value ? els.stravaName.value : '').trim();
                var startAt = (els.stravaStart && els.stravaStart.value ? els.stravaStart.value : '').trim();
                var device = (els.stravaDevice && els.stravaDevice.value ? els.stravaDevice.value : '').trim();
                var paceText = (els.stravaPace && els.stravaPace.value ? els.stravaPace.value : '').trim();
                var hr = (els.stravaHr && els.stravaHr.value ? els.stravaHr.value : '').trim();
                var cadence = (els.stravaCadence && els.stravaCadence.value ? els.stravaCadence.value : '').trim();
                var power = (els.stravaPower && els.stravaPower.value ? els.stravaPower.value : '').trim();
                var isPrivate = !!(els.stravaPrivate && els.stravaPrivate.checked);

                var gpxSource = (els.followRoad && els.followRoad.checked && routePoints.length >= 2) ? routePoints : points;
                var json = JSON.stringify(gpxSource);
                if (kind === 'direct') {
                    if (els.stravaPointsJsonDirect) els.stravaPointsJsonDirect.value = json;
                    if (els.stravaNameDirect) els.stravaNameDirect.value = name;
                    if (els.stravaStartDirect) els.stravaStartDirect.value = startAt;
                    if (els.stravaDeviceDirect) els.stravaDeviceDirect.value = device;
                    if (els.stravaPaceDirect) els.stravaPaceDirect.value = paceText;
                    if (els.stravaHrDirect) els.stravaHrDirect.value = hr;
                    if (els.stravaCadenceDirect) els.stravaCadenceDirect.value = cadence;
                    if (els.stravaPowerDirect) els.stravaPowerDirect.value = power;
                    if (els.stravaPrivateDirect) els.stravaPrivateDirect.value = isPrivate ? '1' : '0';
                } else {
                    if (els.stravaPointsJsonAuth) els.stravaPointsJsonAuth.value = json;
                    if (els.stravaNameAuth) els.stravaNameAuth.value = name;
                    if (els.stravaStartAuth) els.stravaStartAuth.value = startAt;
                    if (els.stravaDeviceAuth) els.stravaDeviceAuth.value = device;
                    if (els.stravaPaceAuth) els.stravaPaceAuth.value = paceText;
                    if (els.stravaHrAuth) els.stravaHrAuth.value = hr;
                    if (els.stravaCadenceAuth) els.stravaCadenceAuth.value = cadence;
                    if (els.stravaPowerAuth) els.stravaPowerAuth.value = power;
                    if (els.stravaPrivateAuth) els.stravaPrivateAuth.value = isPrivate ? '1' : '0';
                }

                return true;
            }

            function applyFromQuery() {
                var qs = new URLSearchParams(window.location.search || '');
                function scrollToMap() {
                    if (window.matchMedia && !window.matchMedia('(max-width: 1023px)').matches) return;
                    var el = document.getElementById('rl-route-map');
                    if (!el) return;
                    var y = el.getBoundingClientRect().top + window.pageYOffset - 90;
                    if (!Number.isFinite(y)) return;
                    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
                }

                if (els.followRoad) {
                    if (qs.has('snap')) {
                        els.followRoad.checked = qs.get('snap') === '1';
                    }
                }
                if (els.showDirections) {
                    els.showDirections.checked = qs.get('dir') !== '0';
                }
                var rc = qs.get('rc');
                var mc = qs.get('mc');
                var sc = qs.get('sc');
                var fc = qs.get('fc');
                var ai = qs.get('ai');
                var nextStyle = {};
                if (rc && /^[0-9a-f]{6}$/i.test(rc)) nextStyle.route = '#' + rc;
                if (mc && /^[0-9a-f]{6}$/i.test(mc)) nextStyle.marker = '#' + mc;
                if (sc && /^[0-9a-f]{6}$/i.test(sc)) nextStyle.start = '#' + sc;
                if (fc && /^[0-9a-f]{6}$/i.test(fc)) nextStyle.finish = '#' + fc;
                if (ai && /^\d+$/.test(ai)) nextStyle.arrowIntervalM = clamp(parseInt(ai, 10), 30, 300);
                if (Object.keys(nextStyle).length > 0) {
                    setStyle(nextStyle);
                } else {
                    applyStyleFromState(getStyle());
                }
                var pts = qs.get('pts');
                var modes = qs.get('m') || '';
                var routePolyline = qs.get('rp') || '';
                if (pts) {
                    var parsed = pts.split(';').map(function (pair) {
                        var parts = pair.split(',');
                        if (parts.length !== 2) return null;
                        var lat = parseFloat(parts[0]);
                        var lng = parseFloat(parts[1]);
                        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
                        return { lat: lat, lng: lng };
                    }).filter(Boolean);
                    if (parsed.length > 0) {
                        var snap = qs.get('snap') === '1';
                        points = parsed.map(function(p, idx) {
                            var ch = modes && modes.length === parsed.length ? modes.charAt(idx) : '';
                            var mode = ch === 'o' ? 'osrm' : (ch === 'd' ? 'direct' : (snap ? 'osrm' : 'direct'));
                            return { lat: p.lat, lng: p.lng, mode: mode, segment: [] };
                        });

                        if (routePolyline) {
                            try {
                                var decoded = decodePolyline(routePolyline);
                                if (decoded && decoded.length >= 2) {
                                    assignSegmentsFromRoutePoints(decoded);
                                }
                            } catch (e) {}
                        }

                        processLoadedPoints().then(function() {
                            rebuildLine();
                            rebuildMarkers();
                            updateStats();
                            updateElevation();
                            setStatus('Rute dari link');
                            setTimeout(function () {
                                fitRoute();
                                scrollToMap();
                            }, 250);
                            pushState();
                        });
                }
                }
                var name = qs.get('name');
                if (name) els.name.value = name;
                var pm = qs.get('pm');
                var ps = qs.get('ps');
                if (pm !== null) els.paceMin.value = String(clamp(parseInt(pm, 10), 0, 59));
                if (ps !== null) els.paceSec.value = String(clamp(parseInt(ps, 10), 0, 59));
            }

            function updateDirections() {
                var on = !!(els.showDirections && els.showDirections.checked);
                directionLayer.clearLayers();
                if (!on || routePoints.length < 2) return;

                var style = getStyle();
                var stepKm = (style.arrowIntervalM || 80) / 1000;
                var acc = 0;
                for (var i = 1; i < routePoints.length; i++) {
                    var a = routePoints[i - 1];
                    var b = routePoints[i];
                    acc += haversineKm(a, b);
                    if (acc < stepKm) continue;
                    acc = 0;
                    var angle = bearingDeg(a, b);
                    var icon = L.divIcon({
                        className: '',
                        html: '<div style="transform:rotate(' + (angle - 90).toFixed(1) + 'deg);color:' + style.route + ';font-weight:900;font-size:16px;line-height:16px;text-shadow:0 0 10px rgba(0,0,0,.55)">➤</div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                    });
                    L.marker([b.lat, b.lng], { icon: icon, interactive: false }).addTo(directionLayer);
                }
            }

            function parseGpxText(text) {
                try {
                    var parser = new DOMParser();
                    var xml = parser.parseFromString(text, 'application/xml');
                    var bad = xml.getElementsByTagName('parsererror');
                    if (bad && bad.length > 0) return [];
                    var pts = [];
                    var nodes = xml.getElementsByTagName('trkpt');
                    if (!nodes || nodes.length === 0) nodes = xml.getElementsByTagName('rtept');
                    for (var i = 0; i < nodes.length; i++) {
                        var lat = parseFloat(nodes[i].getAttribute('lat'));
                        var lng = parseFloat(nodes[i].getAttribute('lon'));
                        if (!Number.isFinite(lat) || !Number.isFinite(lng)) continue;
                        pts.push({ lat: lat, lng: lng });
                    }
                    return pts;
                } catch (e) {
                    return [];
                }
            }

            function decimatePoints(arr, maxCount) {
                if (!Array.isArray(arr)) return [];
                if (arr.length <= maxCount) return arr.slice();
                var step = Math.ceil(arr.length / maxCount);
                var out = [];
                for (var i = 0; i < arr.length; i += step) {
                    out.push(arr[i]);
                }
                if (out.length > 0 && out[0] !== arr[0]) out.unshift(arr[0]);
                var last = arr[arr.length - 1];
                if (out[out.length - 1] !== last) out.push(last);
                return out;
            }

            function importGpxFile(file) {
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function () {
                    var text = String(reader.result || '');
                    var parsed = parseGpxText(text);
                    if (!parsed || parsed.length < 2) {
                        showInfoModal('Import gagal', 'File GPX tidak berisi track/route points.');
                        return;
                    }
                    routePoints = decimatePoints(parsed, 3000);
                    points = decimatePoints(routePoints, 60);

                    // Reconstruct segments from routePoints to preserve geometry
                    for (var i = 0; i < points.length; i++) {
                        points[i].mode = 'direct';
                        if (i > 0) {
                            var startIdx = routePoints.indexOf(points[i-1]);
                            var endIdx = routePoints.indexOf(points[i]);
                            // Ensure we search forward from startIdx
                            if (endIdx < startIdx) {
                                endIdx = routePoints.indexOf(points[i], startIdx);
                            }
                            
                            if (startIdx !== -1 && endIdx !== -1) {
                                points[i].segment = routePoints.slice(startIdx, endIdx + 1);
                            } else {
                                points[i].segment = [points[i-1], points[i]];
                            }
                        } else {
                            points[i].segment = [];
                        }
                    }

                    rebuildLine();
                    rebuildMarkers();
                    updateStats();
                    updateElevation();
                    setTimeout(fitRoute, 80);
                    setStatus('GPX dimuat');
                    pushState();
                };
                reader.onerror = function () {
                    showInfoModal('Import gagal', 'Gagal membaca file GPX.');
                };
                reader.readAsText(file);
            }

            function updateKmMarkers() {
                kmLayer.clearLayers();
                if (routePoints.length < 2) return;
                
                var acc = 0;
                var nextKm = 1;
                var style = getStyle();
                
                for (var i = 1; i < routePoints.length; i++) {
                    var dist = haversineKm(routePoints[i - 1], routePoints[i]);
                    if (acc + dist >= nextKm) {
                        // Interpolasi posisi KM
                        var remain = nextKm - acc;
                        var ratio = remain / dist;
                        var lat = routePoints[i-1].lat + (routePoints[i].lat - routePoints[i-1].lat) * ratio;
                        var lng = routePoints[i-1].lng + (routePoints[i].lng - routePoints[i-1].lng) * ratio;
                        
                        var icon = L.divIcon({
                            className: '',
                            html: '<div style="background:#fff;border:2px solid '+style.route+';color:#000;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.3)">'+nextKm+'</div>',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });
                        L.marker([lat, lng], { icon: icon, interactive: false }).addTo(kmLayer);
                        nextKm++;
                    }
                    acc += dist;
                }
            }

            function osrmRoute(waypoints, profile, excludes) {
                profile = profile || 'driving';
                var coords = waypoints.map(function (p) { return p.lng.toFixed(6) + ',' + p.lat.toFixed(6); }).join(';');
                var url = 'https://router.project-osrm.org/route/v1/' + profile + '/' + coords + '?overview=full&geometries=geojson&steps=false';
                if (Array.isArray(excludes) && excludes.length > 0) {
                    url += '&exclude=' + excludes.join(',');
                }
                return fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { 
                        if (!r.ok && excludes && excludes.length > 0) {
                            var fallbackUrl = 'https://router.project-osrm.org/route/v1/' + profile + '/' + coords + '?overview=full&geometries=geojson&steps=false';
                            return fetch(fallbackUrl, { headers: { 'Accept': 'application/json' } })
                                .then(function(fallbackR) {
                                    return fallbackR.json().then(function(j) { return { ok: fallbackR.ok, json: j }; });
                                });
                        }
                        return r.json().then(function (j) { return { ok: r.ok, status: r.status, json: j }; }); 
                    })
                    .then(function (res) {
                        if (!res.ok || !res.json || res.json.code !== 'Ok') {
                            throw new Error('osrm_failed');
                        }
                        var coords = res.json.routes && res.json.routes[0] && res.json.routes[0].geometry && res.json.routes[0].geometry.coordinates;
                        if (!Array.isArray(coords) || coords.length < 2) {
                            throw new Error('osrm_no_geometry');
                        }
                        return coords.map(function (c) { return { lat: c[1], lng: c[0] }; });
                    });
            }



            var elevTimer = null;
            var elevAbortController = null;
            var elevationHoverMarker = null;
            function updateElevation() {
                if (elevTimer) clearTimeout(elevTimer);
                if (els.elevSub) els.elevSub.textContent = 'Menunggu update elevasi...';
                
                elevTimer = setTimeout(function() {
                    _performUpdateElevation();
                }, 1000);
            }

            function _performUpdateElevation() {
                if (!els.elevSvg || !els.elevSub || !els.elevMeta) return;

                // Cancel previous running request if any
                if (elevAbortController) {
                    elevAbortController.abort();
                }
                elevAbortController = new AbortController();
                var signal = elevAbortController.signal;

                if (routePoints.length < 2) {
                    els.elevSub.textContent = 'Buat rute dulu untuk lihat grafik.';
                    els.elevMeta.textContent = '';
                    els.elevSvg.innerHTML = '';
                    if (elevationHoverMarker) {
                        map.removeLayer(elevationHoverMarker);
                        elevationHoverMarker = null;
                    }
                    return;
                }
                elevSeq += 1;
                var seq = elevSeq;
                els.elevSub.textContent = 'Mengambil elevasi...';

                var samples = [];
                var maxSamples = 150; // Increased limit, handled by chunking
                if (routePoints.length <= maxSamples) {
                    samples = routePoints.slice();
                } else {
                    for (var i = 0; i < maxSamples; i++) {
                        var idx = Math.round((i * (routePoints.length - 1)) / (maxSamples - 1));
                        samples.push(routePoints[idx]);
                    }
                }

                var latArray = samples.map(function(p) { return p.lat.toFixed(6); });
                var lngArray = samples.map(function(p) { return p.lng.toFixed(6); });

                fetchElevationBatched(latArray, lngArray, signal)
                    .then(function(elevations) {
                        if (signal.aborted) return;
                        if (seq !== elevSeq) return;
                        if (!elevations || elevations.length !== samples.length) {
                            throw new Error('elev_mismatch');
                        }

                        var elev = elevations.map(function (v) { return (typeof v === 'number' ? v : null); });

                        var dists = [0];
                        var total = 0;
                        for (var i = 1; i < samples.length; i++) {
                            total += haversineKm(samples[i - 1], samples[i]);
                            dists.push(total);
                        }

                        renderElevation(samples, dists, elev);
                        els.elevSub.textContent = 'Hover untuk lihat elevasi per titik.';
                    })
                    .catch(function (err) {
                        if (err.name === 'AbortError') return;
                        if (seq !== elevSeq) return;
                        console.error('Elevation Error:', err);
                        if (err.message === 'rate_limit') {
                            els.elevSub.textContent = 'Terlalu banyak request (429). Coba lagi nanti.';
                        } else {
                            els.elevSub.textContent = 'Gagal mengambil elevasi.';
                        }
                        els.elevMeta.textContent = '';
                        els.elevSvg.innerHTML = '';
                    });
            }

            function fetchElevationBatched(lats, lngs, signal) {
                var chunkSize = 50; // Reduced for safety
                var chunks = [];
                for (var i = 0; i < lats.length; i += chunkSize) {
                    chunks.push({
                        lats: lats.slice(i, i + chunkSize),
                        lngs: lngs.slice(i, i + chunkSize)
                    });
                }
                
                var results = [];
                var p = Promise.resolve();
                
                chunks.forEach(function(chunk) {
                    p = p.then(function() {
                        if (signal && signal.aborted) throw new DOMException('Aborted', 'AbortError');

                        var url = 'https://api.open-meteo.com/v1/elevation?latitude=' + 
                                  encodeURIComponent(chunk.lats.join(',')) + 
                                  '&longitude=' + 
                                  encodeURIComponent(chunk.lngs.join(','));
                        
                        return fetch(url, { headers: { 'Accept': 'application/json' }, signal: signal })
                            .then(function(r) {
                                if (r.status === 429) throw new Error('rate_limit');
                                if (!r.ok) throw new Error('elev_api_error: ' + r.status);
                                return r.json();
                            })
                            .then(function(data) {
                                if (data && data.elevation) {
                                    results = results.concat(data.elevation);
                                }
                                // Delay 500ms between chunks to be nice to API
                                return new Promise(function(resolve) { setTimeout(resolve, 500); });
                            });
                    });
                });
                
                return p.then(function() { return results; });
            }

            function renderElevation(samples, distsKm, elevM) {
                var minE = Infinity;
                var maxE = -Infinity;
                elevM.forEach(function (v) {
                    if (typeof v !== 'number') return;
                    minE = Math.min(minE, v);
                    maxE = Math.max(maxE, v);
                });
                if (!Number.isFinite(minE) || !Number.isFinite(maxE)) {
                    els.elevMeta.textContent = '';
                    els.elevSvg.innerHTML = '';
                    els.elevSub.textContent = 'Tidak ada data elevasi.';
                    return;
                }

                var pad = 18;
                var w = 1000;
                var h = 220;
                var innerW = w - pad * 2;
                var innerH = h - pad * 2;
                var totalDist = distsKm[distsKm.length - 1] || 0;
                var range = Math.max(1, maxE - minE);

                var pts = samples.map(function (_, i) {
                    var x = pad + (totalDist > 0 ? (distsKm[i] / totalDist) * innerW : 0);
                    var y = pad + (1 - ((elevM[i] - minE) / range)) * innerH;
                    return { x: x, y: y, d: distsKm[i], e: elevM[i] };
                });

                var line = 'M ' + pts.map(function (p) { return p.x.toFixed(2) + ' ' + p.y.toFixed(2); }).join(' L ');
                var area = line + ' L ' + (pad + innerW).toFixed(2) + ' ' + (pad + innerH).toFixed(2) + ' L ' + pad.toFixed(2) + ' ' + (pad + innerH).toFixed(2) + ' Z';

                els.elevMeta.textContent = 'Min ' + Math.round(minE) + ' m • Max ' + Math.round(maxE) + ' m • ' + fmt2(totalDist) + ' km';
                els.elevSvg.innerHTML = ''
                    + '<defs>'
                    + '<linearGradient id="rlElevFill" x1="0" x2="0" y1="0" y2="1">'
                    + '<stop offset="0%" stop-color="#ccff00" stop-opacity="0.35"></stop>'
                    + '<stop offset="100%" stop-color="#ccff00" stop-opacity="0.05"></stop>'
                    + '</linearGradient>'
                    + '</defs>'
                    + '<rect x="0" y="0" width="' + w + '" height="' + h + '" fill="transparent"></rect>'
                    + '<path d="' + area + '" fill="url(#rlElevFill)"></path>'
                    + '<path d="' + line + '" fill="none" stroke="#ccff00" stroke-width="2"></path>'
                    + '<line id="rlElevX" x1="0" y1="' + pad + '" x2="0" y2="' + (pad + innerH) + '" stroke="#94a3b8" stroke-width="1" opacity="0.6" style="display:none"></line>'
                    + '<circle id="rlElevDot" cx="0" cy="0" r="4" fill="#ccff00" stroke="#0b1220" stroke-width="2" style="display:none"></circle>'
                    + '<text id="rlElevTip" x="' + pad + '" y="' + (pad + 14) + '" fill="#e2e8f0" font-size="12" font-weight="700" style="display:none"></text>';

                var elX = els.elevSvg.querySelector('#rlElevX');
                var elDot = els.elevSvg.querySelector('#rlElevDot');
                var elTip = els.elevSvg.querySelector('#rlElevTip');

                function pickIndex(xView) {
                    var target = (xView - pad) / innerW;
                    target = Math.max(0, Math.min(1, target));
                    var dist = target * totalDist;
                    var best = 0;
                    var bestErr = Infinity;
                    for (var i = 0; i < pts.length; i++) {
                        var err = Math.abs(pts[i].d - dist);
                        if (err < bestErr) {
                            bestErr = err;
                            best = i;
                        }
                    }
                    return best;
                }

                function onMove(e) {
                    var rect = els.elevSvg.getBoundingClientRect();
                    var x = e.clientX - rect.left;
                    var xView = (x / rect.width) * w;
                    var idx = pickIndex(xView);
                    var p = pts[idx];
                    elX.style.display = '';
                    elDot.style.display = '';
                    elTip.style.display = '';
                    elX.setAttribute('x1', p.x.toFixed(2));
                    elX.setAttribute('x2', p.x.toFixed(2));
                    elDot.setAttribute('cx', p.x.toFixed(2));
                    elDot.setAttribute('cy', p.y.toFixed(2));

                    // Calculate slope / gradient %
                    var gradient = 0;
                    if (idx > 0) {
                        var distChangeM = (pts[idx].d - pts[idx - 1].d) * 1000;
                        var elevChangeM = pts[idx].e - pts[idx - 1].e;
                        if (distChangeM > 0.1) {
                            gradient = (elevChangeM / distChangeM) * 100;
                        }
                    } else if (idx < pts.length - 1) {
                        var distChangeM = (pts[idx + 1].d - pts[idx].d) * 1000;
                        var elevChangeM = pts[idx + 1].e - pts[idx].e;
                        if (distChangeM > 0.1) {
                            gradient = (elevChangeM / distChangeM) * 100;
                        }
                    }

                    var gradText = ' (' + (gradient >= 0 ? '+' : '') + gradient.toFixed(1) + '%)';
                    elTip.textContent = fmt2(p.d) + ' km • ' + Math.round(p.e) + ' m' + gradText;

                    // Update synchronized hover marker on Leaflet map
                    var samplePoint = samples[idx];
                    if (samplePoint) {
                        var style = getStyle();
                        var hoverHtml = '<div style="width: 16px; height: 16px; border-radius: 999px; background: ' + style.route + '; border: 3px solid #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.5); transform: scale(1.1);"></div>';
                        if (!elevationHoverMarker) {
                            var hoverIcon = L.divIcon({
                                className: '',
                                html: hoverHtml,
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });
                            elevationHoverMarker = L.marker([samplePoint.lat, samplePoint.lng], { icon: hoverIcon, zIndexOffset: 1000 }).addTo(map);
                        } else {
                            elevationHoverMarker.setLatLng([samplePoint.lat, samplePoint.lng]);
                            elevationHoverMarker.setIcon(L.divIcon({
                                className: '',
                                html: hoverHtml,
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            }));
                        }
                    }
                }

                function onLeave() {
                    elX.style.display = 'none';
                    elDot.style.display = 'none';
                    elTip.style.display = 'none';
                    if (elevationHoverMarker) {
                        map.removeLayer(elevationHoverMarker);
                        elevationHoverMarker = null;
                    }
                }

                els.elevSvg.onmousemove = onMove;
                els.elevSvg.onmouseleave = onLeave;
                els.elevSvg.ontouchmove = function (ev) {
                    if (!ev.touches || ev.touches.length === 0) return;
                    onMove({ clientX: ev.touches[0].clientX });
                };
                els.elevSvg.ontouchend = onLeave;
            }

            function showSearchResults(items) {
                els.results.innerHTML = '';
                if (!items || items.length === 0) {
                    els.results.className = 'mt-2';
                    els.results.innerHTML = '<div class="text-xs text-slate-500 font-bold">Tidak ada hasil.</div>';
                    els.results.classList.remove('hidden');
                    return;
                }
                var wrap = document.createElement('div');
                wrap.className = 'bg-slate-900/60 border border-slate-700 rounded-xl overflow-hidden';
                items.slice(0, 6).forEach(function (it) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full text-left px-4 py-3 hover:bg-slate-800 transition border-b border-slate-800 last:border-b-0';
                    var title = document.createElement('div');
                    title.className = 'text-sm font-black text-white';
                    title.textContent = it.display_name || 'Lokasi';
                    var sub = document.createElement('div');
                    sub.className = 'text-[11px] text-slate-500 font-bold';
                    sub.textContent = (parseFloat(it.lat).toFixed(5) + ', ' + parseFloat(it.lon).toFixed(5));
                    btn.appendChild(title);
                    btn.appendChild(sub);
                    btn.addEventListener('click', function () {
                        els.results.classList.add('hidden');
                        map.setView([parseFloat(it.lat), parseFloat(it.lon)], 15);
                        setStatus('Lokasi dipilih');
                    });
                    wrap.appendChild(btn);
                });
                els.results.appendChild(wrap);
                els.results.classList.remove('hidden');
            }

            function searchLocation() {
                var q = (els.q.value || '').trim();
                if (q === '') return;
                setStatus('Mencari...');
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        showSearchResults(Array.isArray(data) ? data : []);
                        setStatus('Hasil pencarian');
                    })
                    .catch(function () {
                        setStatus('Gagal mencari');
                    });
            }

            els.searchBtn.addEventListener('click', searchLocation);
            els.q.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchLocation();
                }
            });

            els.paceMin.addEventListener('input', updateStats);
            els.paceSec.addEventListener('input', updateStats);

            if (els.startTime) {
                var oneHourAgo = new Date(Date.now() - 3600 * 1000);
                els.startTime.value = toDatetimeLocalValue(oneHourAgo);
                els.startTime.addEventListener('input', updateStats);
            }

            els.undo.addEventListener('click', undo);
            els.clear.addEventListener('click', clearAll);
            
            if (els.toggleMenu && els.collapsibleButtons) {
                els.toggleMenu.addEventListener('click', function () {
                    var isHidden = els.collapsibleButtons.classList.contains('hidden');
                    if (isHidden) {
                        els.collapsibleButtons.classList.remove('hidden');
                        if (els.toggleChevron) els.toggleChevron.classList.add('rotate-180');
                    } else {
                        els.collapsibleButtons.classList.add('hidden');
                        if (els.toggleChevron) els.toggleChevron.classList.remove('rotate-180');
                    }
                });
            }

            // Mobile sheet collapse/expand toggle
            var isMinimized = false;
            var sheetToggle = document.getElementById('rl-mobile-sheet-toggle');
            var summaryPanel = document.getElementById('rl-summary-panel');
            var compactStats = document.getElementById('rl-compact-stats');
            var toggleHint = document.getElementById('rl-mobile-toggle-hint');
            var expandedContent = document.getElementById('rl-expanded-content');

            if (sheetToggle && summaryPanel && compactStats && toggleHint && expandedContent) {
                sheetToggle.addEventListener('click', function () {
                    isMinimized = !isMinimized;
                    if (isMinimized) {
                        summaryPanel.classList.add('rl-minimized');
                        compactStats.classList.remove('hidden');
                        toggleHint.classList.add('hidden');
                        expandedContent.classList.add('hidden');
                    } else {
                        summaryPanel.classList.remove('rl-minimized');
                        compactStats.classList.add('hidden');
                        toggleHint.classList.remove('hidden');
                        expandedContent.classList.remove('hidden');
                    }
                });
            }

            els.save.addEventListener('click', saveCurrent);
            els.load.addEventListener('click', showLoadModal);
            els.center.addEventListener('click', centerToUser);
            els.fit.addEventListener('click', fitRoute);
            els.exportGpx.addEventListener('click', exportGpx);
            els.share.addEventListener('click', function () {
                var longUrl = buildShareUrl();
                if (!longUrl) {
                    setStatus('Minimal 1 titik');
                    return;
                }
                
                setStatus('Membuat link pendek...');
                
                var csrf = document.querySelector('meta[name="csrf-token"]');
                var token = csrf ? csrf.content : '';

                fetch('/tools/shortlink', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ url: longUrl })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.short_url) {
                        showShareModal(data.short_url);
                        setStatus('Link siap');
                    } else {
                        showShareModal(longUrl);
                        setStatus('Gagal memendekkan link');
                    }
                })
                .catch(function() {
                    showShareModal(longUrl);
                    setStatus('Gagal memendekkan link');
                });
            });

            els.mode.addEventListener('change', function () {
                freehandActive = (els.mode.value === 'freehand');
                setStatus(freehandActive ? 'Freehand aktif' : 'Tap mode');
            });

            if (els.followRoad) {
                els.followRoad.addEventListener('change', function () {
                    // Hanya ubah mode untuk titik baru, jangan update rute yang sudah ada
                    setStatus('Mode ' + (els.followRoad.checked ? 'OSRM' : 'Manual') + ' aktif');
                });
            }
            if (els.showDirections) {
                els.showDirections.addEventListener('change', function () {
                    updateDirections();
                });
            }
            if (els.colorRoute) {
                els.colorRoute.addEventListener('input', function () { setStyle({ route: els.colorRoute.value }); });
            }
            if (els.colorMarker) {
                els.colorMarker.addEventListener('input', function () { setStyle({ marker: els.colorMarker.value }); });
            }
            if (els.colorStart) {
                els.colorStart.addEventListener('input', function () { setStyle({ start: els.colorStart.value }); });
            }
            if (els.colorFinish) {
                els.colorFinish.addEventListener('input', function () { setStyle({ finish: els.colorFinish.value }); });
            }
            if (els.arrowInterval) {
                els.arrowInterval.addEventListener('input', function () {
                    var v = clamp(parseInt(els.arrowInterval.value || '80', 10), 30, 300);
                    setStyle({ arrowIntervalM: v });
                });
            }
            if (els.importGpx && els.importGpxFile) {
                els.importGpx.addEventListener('click', function () { els.importGpxFile.click(); });
                els.importGpxFile.addEventListener('change', function () {
                    importGpxFile(els.importGpxFile.files && els.importGpxFile.files[0] ? els.importGpxFile.files[0] : null);
                    els.importGpxFile.value = '';
                });
            }

            if (els.stravaToggle && els.stravaBody) {
                els.stravaToggle.addEventListener('click', function () {
                    var isHidden = els.stravaBody.classList.contains('hidden');
                    if (isHidden) {
                        els.stravaBody.classList.remove('hidden');
                        els.stravaToggle.textContent = 'Tutup';
                        syncStravaDefaults();
                        var panel = document.getElementById('strava-form-panel');
                        if (panel) {
                            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    } else {
                        els.stravaBody.classList.add('hidden');
                        els.stravaToggle.textContent = 'Buka';
                    }
                });
            }

            if (els.stravaDirectForm) {
                els.stravaDirectForm.addEventListener('submit', function (e) {
                    var ok = fillHiddenStravaFields('direct');
                    if (!ok) e.preventDefault();
                });
            }
            if (els.stravaAuthorizeForm) {
                els.stravaAuthorizeForm.addEventListener('submit', function (e) {
                    var ok = fillHiddenStravaFields('auth');
                    if (!ok) e.preventDefault();
                });
            }

            if (els.name) {
                els.name.addEventListener('input', function () {
                    if (!els.stravaName) return;
                    if ((els.stravaName.value || '').trim() === '') {
                        var v = (els.name.value || '').trim();
                        if (v !== '') els.stravaName.value = v;
                    }
                });
            }

            var freehandPoints = [];
            function onFreehandStart(e) {
                if (!freehandActive) return;
                freehandPoints = [];
                map.dragging.disable();
                freehandPoints.push(e.latlng);
                setStatus('Freehand...');
            }
            function onFreehandMove(e) {
                if (!freehandActive) return;
                if (freehandPoints.length === 0) return;
                freehandPoints.push(e.latlng);
                if (freehandPoints.length % 4 === 0) {
                    var ll = freehandPoints[freehandPoints.length - 1];
                    var newP = { lat: ll.lat, lng: ll.lng, mode: 'direct', segment: [] };
                    if (points.length > 0) {
                        var prev = points[points.length-1];
                        newP.segment = [prev, newP];
                    }
                    points.push(newP);
                    rebuildLine();
                    updateStats();
                }
            }
            function onFreehandEnd() {
                if (!freehandActive) return;
                map.dragging.enable();
                rebuildMarkers();
                updateStats();
                updateElevation();
                setStatus('Freehand selesai');
                pushState();
            }

            // Custom Marker placement logic
            var customMarkerMode = false;
            var selectedCustomIcon = 'water';

            var customBtns = document.querySelectorAll('.rl-custom-icon-btn');
            var customHint = document.getElementById('rl-custom-marker-hint');

            customBtns.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var type = btn.getAttribute('data-type');
                    
                    if (customMarkerMode && selectedCustomIcon === type) {
                        deactivateCustomMarkerMode();
                    } else {
                        customBtns.forEach(function (b) { b.classList.remove('active'); });
                        btn.classList.add('active');
                        selectedCustomIcon = type;
                        customMarkerMode = true;
                        if (customHint) customHint.classList.remove('hidden');
                        setStatus('Klik pada peta untuk menaruh marker ' + type);
                    }
                });
            });

            function deactivateCustomMarkerMode() {
                customMarkerMode = false;
                customBtns.forEach(function (b) { b.classList.remove('active'); });
                if (customHint) customHint.classList.add('hidden');
                setStatus('Mode gambar rute aktif');
            }

            function addCustomMarker(latlng, type, label) {
                label = label || '';
                var faIcon = 'fa-droplet';
                var colorClass = 'text-sky-400 border-sky-500 bg-sky-950/90';
                
                if (type === 'warning') {
                    faIcon = 'fa-triangle-exclamation';
                    colorClass = 'text-orange-400 border-orange-500 bg-orange-950/90';
                } else if (type === 'intersection') {
                    faIcon = 'fa-arrows-split-up-and-left';
                    colorClass = 'text-lime-400 border-lime-500 bg-lime-950/90';
                } else if (type === 'photo') {
                    faIcon = 'fa-camera';
                    colorClass = 'text-pink-400 border-pink-500 bg-pink-950/90';
                } else if (type === 'rest') {
                    faIcon = 'fa-mug-hot';
                    colorClass = 'text-amber-400 border-amber-500 bg-amber-950/90';
                } else if (type === 'toilet') {
                    faIcon = 'fa-toilet';
                    colorClass = 'text-purple-400 border-purple-500 bg-purple-950/90';
                }

                var html = '<div class="w-8 h-8 rounded-full border-2 flex items-center justify-center shadow-lg transition-transform duration-200 hover:scale-110 ' + colorClass + '">' +
                           '<i class="fa-solid ' + faIcon + ' text-sm"></i>' +
                           '</div>';
                           
                var customIcon = L.divIcon({
                    html: html,
                    className: '',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -16]
                });

                var m = L.marker([latlng.lat, latlng.lng], { icon: customIcon, draggable: true });
                
                var popupContent = document.createElement('div');
                popupContent.className = 'p-2 text-center space-y-2';
                
                var title = document.createElement('div');
                title.className = 'text-xs font-black text-white capitalize';
                title.textContent = type + ' Marker';
                popupContent.appendChild(title);
                
                var input = document.createElement('input');
                input.type = 'text';
                input.placeholder = 'Keterangan...';
                input.value = label;
                input.className = 'w-full bg-slate-900 border border-slate-700 text-white text-[11px] px-2 py-1 rounded focus:outline-none focus:ring-1 focus:ring-neon';
                input.addEventListener('input', function() {
                    var idx = findCustomMarkerIndex(m);
                    if (idx !== -1) {
                        customMarkers[idx].label = input.value;
                        pushState();
                    }
                });
                popupContent.appendChild(input);
                
                var delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'w-full px-2 py-1 bg-red-500 text-white rounded text-[10px] font-bold hover:bg-red-600 transition';
                delBtn.textContent = 'Hapus';
                delBtn.addEventListener('click', function() {
                    map.closePopup();
                    removeCustomMarker(m);
                });
                popupContent.appendChild(delBtn);

                m.bindPopup(popupContent, { minWidth: 140 });
                
                m.on('dragend', function() {
                    var ll = m.getLatLng();
                    var idx = findCustomMarkerIndex(m);
                    if (idx !== -1) {
                        customMarkers[idx].lat = ll.lat;
                        customMarkers[idx].lng = ll.lng;
                        pushState();
                    }
                });

                m.addTo(map);

                customMarkers.push({
                    lat: latlng.lat,
                    lng: latlng.lng,
                    type: type,
                    label: label,
                    markerInstance: m
                });

                pushState();
            }

            function findCustomMarkerIndex(markerInstance) {
                for (var i = 0; i < customMarkers.length; i++) {
                    if (customMarkers[i].markerInstance === markerInstance) return i;
                }
                return -1;
            }

            function removeCustomMarker(markerInstance) {
                var idx = findCustomMarkerIndex(markerInstance);
                if (idx !== -1) {
                    map.removeLayer(markerInstance);
                    customMarkers.splice(idx, 1);
                    pushState();
                }
            }

            // --- AI Route Generator Logic ---
            var aiBtns = document.querySelectorAll('.rl-ai-dist-btn');
            var activeAiDist = 5;

            aiBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    aiBtns.forEach(function (b) {
                        b.classList.remove('bg-slate-700', 'border-slate-500', 'text-white', 'active');
                        b.classList.add('bg-transparent', 'border-slate-700', 'text-slate-400');
                    });
                    btn.classList.add('bg-slate-700', 'border-slate-500', 'text-white', 'active');
                    btn.classList.remove('bg-transparent', 'border-slate-700', 'text-slate-400');
                    activeAiDist = parseFloat(btn.getAttribute('data-dist'));
                    if (els.aiCustomDist) els.aiCustomDist.value = '';
                });
            });

            if (els.aiCustomDist) {
                els.aiCustomDist.addEventListener('input', function () {
                    var val = parseFloat(els.aiCustomDist.value);
                    if (Number.isFinite(val) && val > 0) {
                        aiBtns.forEach(function (b) {
                            b.classList.remove('bg-slate-700', 'border-slate-500', 'text-white', 'active');
                            b.classList.add('bg-transparent', 'border-slate-700', 'text-slate-400');
                        });
                        activeAiDist = val;
                    }
                });
            }

            if (els.aiGenerateBtn) {
                els.aiGenerateBtn.addEventListener('click', function () {
                    generateAiRoute(false);
                });
            }

            if (els.aiRegenerateBtn) {
                els.aiRegenerateBtn.addEventListener('click', function () {
                    clearAll();
                    generateAiRoute(true);
                });
            }

            function getOffsetLatLng(base, distKm, angleRad) {
                var RE = 6371;
                var dLat = (distKm * Math.sin(angleRad) / RE) * (180 / Math.PI);
                var dLng = (distKm * Math.cos(angleRad) / (RE * Math.cos(base.lat * Math.PI / 180))) * (180 / Math.PI);
                return {
                    lat: base.lat + dLat,
                    lng: base.lng + dLng
                };
            }

            function getAiWaypoints(start, targetDist, angleRad, isLoop, avoidIntersections) {
                if (avoidIntersections) {
                    angleRad += (Math.random() * 0.15 - 0.075);
                }
                if (isLoop) {
                    var side = targetDist / 5.6;
                    var p0 = start;
                    var p1 = getOffsetLatLng(p0, side, angleRad - Math.PI / 4);
                    var p2 = getOffsetLatLng(p0, side * Math.sqrt(2), angleRad);
                    var p3 = getOffsetLatLng(p0, side, angleRad + Math.PI / 4);
                    return [p0, p1, p2, p3, p0];
                } else {
                    var side = targetDist / 1.35;
                    var p0 = start;
                    var p1 = getOffsetLatLng(p0, side / 3, angleRad + (Math.random() * 0.1 - 0.05));
                    var p2 = getOffsetLatLng(p0, side * 2 / 3, angleRad + (Math.random() * 0.1 - 0.05));
                    var p3 = getOffsetLatLng(p0, side, angleRad + (Math.random() * 0.1 - 0.05));
                    return [p0, p1, p2, p3];
                }
            }

            function fetchLoopRoute(wpts, profile, excludes) {
                var segmentPromises = [];
                for (var i = 1; i < wpts.length; i++) {
                    (function(idx) {
                        var pPrev = wpts[idx-1];
                        var pCurr = wpts[idx];
                        segmentPromises.push(
                            osrmRoute([pPrev, pCurr], profile, excludes)
                                .then(function(coords) {
                                    return { mode: 'osrm', segment: coords };
                                })
                                .catch(function() {
                                    return { mode: 'direct', segment: [pPrev, pCurr] };
                                })
                        );
                    })(i);
                }
                return Promise.all(segmentPromises).then(function(segments) {
                    var totalDist = 0;
                    var processedPoints = [];
                    
                    processedPoints.push({
                        lat: wpts[0].lat,
                        lng: wpts[0].lng,
                        mode: 'osrm',
                        segment: []
                    });
                    
                    for (var i = 0; i < segments.length; i++) {
                        var w = wpts[i+1];
                        processedPoints.push({
                            lat: w.lat,
                            lng: w.lng,
                            mode: segments[i].mode,
                            segment: segments[i].segment
                        });
                        
                        var seg = segments[i].segment;
                        for (var j = 1; j < seg.length; j++) {
                            totalDist += haversineKm(seg[j-1], seg[j]);
                        }
                    }
                    
                    return {
                        points: processedPoints,
                        distance: totalDist
                    };
                });
            }

            function generateAiRoute(forceCenter) {
                var startLatLng;
                if (!forceCenter && points.length > 0) {
                    startLatLng = { lat: points[0].lat, lng: points[0].lng };
                } else {
                    var center = map.getCenter();
                    startLatLng = { lat: center.lat, lng: center.lng };
                }

                var targetDist = activeAiDist;
                if (!targetDist || targetDist <= 0) {
                    setStatus('Jarak target tidak valid');
                    return;
                }

                setStatus('Menghitung rute AI...');
                
                var savedStart = { lat: startLatLng.lat, lng: startLatLng.lng };
                
                clearAll();

                var dir = els.aiDirection ? els.aiDirection.value : 'random';
                var angleRad;
                if (dir === 'north') angleRad = Math.PI / 2;
                else if (dir === 'east') angleRad = 0;
                else if (dir === 'south') angleRad = -Math.PI / 2;
                else if (dir === 'west') angleRad = Math.PI;
                else angleRad = Math.random() * Math.PI * 2;

                var isLoop = els.aiLoop ? els.aiLoop.checked : true;
                var avoidIntersections = els.aiAvoidIntersection ? els.aiAvoidIntersection.checked : false;

                // Setup routing parameters
                var profile = 'foot'; // Pedestrian profile for running to avoid highways/train tracks naturally
                var excludes = [];
                excludes.push('toll'); // Always avoid toll roads
                
                if (els.aiAvoidMain && els.aiAvoidMain.checked) {
                    excludes.push('motorway', 'trunk');
                }

                var initialWpts = getAiWaypoints(savedStart, targetDist, angleRad, isLoop, avoidIntersections);

                setStatus('Menganalisis jalan terdekat...');

                fetchLoopRoute(initialWpts, profile, excludes)
                    .then(function(res1) {
                        var actualDist = res1.distance;
                        if (actualDist <= 0) {
                            throw new Error('distance_zero');
                        }

                        var factor = targetDist / actualDist;
                        if (factor >= 0.5 && factor <= 2.0 && Math.abs(actualDist - targetDist) > 0.3) {
                            setStatus('Menyesuaikan presisi rute...');
                            var adjustedDist = targetDist * factor;
                            var adjustedWpts = getAiWaypoints(savedStart, adjustedDist, angleRad, isLoop, avoidIntersections);
                            return fetchLoopRoute(adjustedWpts, profile, excludes).catch(function() {
                                return res1;
                            });
                        }
                        return res1;
                    })
                    .then(function(finalRes) {
                        points = finalRes.points;
                        
                        rebuildLine();
                        rebuildMarkers();
                        updateStats();
                        updateElevation();
                        
                        setTimeout(fitRoute, 200);
                        
                        setStatus('Rute AI berhasil dibuat (' + fmt2(finalRes.distance) + ' km)');
                        pushState();
                    })
                    .catch(function(err) {
                        console.error('AI Generation failed:', err);
                        setStatus('AI gagal membuat rute. Silakan coba lagi.');
                    });
            }


            map.on('click', function (e) {
                if (freehandActive) return;
                if (customMarkerMode) {
                    addCustomMarker(e.latlng, selectedCustomIcon);
                    deactivateCustomMarkerMode();
                } else {
                    addPoint(e.latlng);
                }
            });
            map.on('mousedown', onFreehandStart);
            map.on('mousemove', onFreehandMove);
            map.on('mouseup', onFreehandEnd);
            map.on('touchstart', function (e) {
                if (!freehandActive) return;
                if (!e.latlng) return;
                onFreehandStart(e);
            });
            map.on('touchmove', function (e) {
                if (!freehandActive) return;
                if (!e.latlng) return;
                onFreehandMove(e);
            });
            map.on('touchend', onFreehandEnd);

            updateStats();
            rebuildLine();

            // Restore draft if present
            try {
                var draftRaw = localStorage.getItem('rl.routeBuilder.v1.draft');
                if (draftRaw) {
                    var parsedDraft = JSON.parse(draftRaw);
                    var draftPoints = [];
                    var draftCustom = [];
                    
                    if (Array.isArray(parsedDraft)) {
                        draftPoints = parsedDraft;
                    } else if (parsedDraft && parsedDraft.points) {
                        draftPoints = parsedDraft.points;
                        draftCustom = parsedDraft.customMarkers || [];
                    }
                    
                    if (draftPoints.length > 0 || draftCustom.length > 0) {
                        points = draftPoints;
                        
                        // Clear existing custom markers
                        customMarkers.forEach(function(cm) {
                            if (cm.markerInstance) map.removeLayer(cm.markerInstance);
                        });
                        customMarkers = [];
                        
                        // Re-render custom markers
                        draftCustom.forEach(function(cm) {
                            addCustomMarker(cm, cm.type, cm.label);
                        });
                        
                        processLoadedPoints().then(function() {
                            rebuildLine();
                            rebuildMarkers();
                            updateStats();
                            updateElevation();
                            pushState();
                        });
                    }
                }
            } catch(e) {
                console.error('Failed to restore draft:', e);
            }

            applyFromQuery();

            // --- TEST SUITE ---
            window.runRouteTests = async function() {
                console.log('Running Route Builder Tests...');
                var passed = 0, failed = 0;
                
                function assert(cond, msg) {
                    if (cond) {
                        console.log('%c✅ PASS: ' + msg, 'color:green');
                        passed++;
                    } else {
                        console.error('❌ FAIL: ' + msg);
                        failed++;
                    }
                }
                
                function wait(ms) { return new Promise(r => setTimeout(r, ms)); }

                async function testUndo() {
                    console.group('Test Undo');
                    clearAll();
                    await wait(100);
                    
                    // Add 3 points
                    addPoint({lat: -6.2, lng: 106.8});
                    addPoint({lat: -6.21, lng: 106.81});
                    addPoint({lat: -6.22, lng: 106.82});
                    
                    assert(points.length === 3, 'Added 3 points');
                    
                    undo();
                    await wait(100);
                    assert(points.length === 2, 'Undo removed 1 point');
                    assert(markers.length === 2, 'Markers updated');
                    
                    redo();
                    await wait(100);
                    assert(points.length === 3, 'Redo restored point');
                    
                    console.groupEnd();
                }

                async function testModeSwitch() {
                    console.group('Test Mode Switch');
                    clearAll();
                    addPoint({lat: -6.2, lng: 106.8});
                    // Enable OSRM
                    if (els.followRoad) els.followRoad.checked = true;
                    addPoint({lat: -6.21, lng: 106.81}); // Should be OSRM
                    
                    await wait(500); // Wait for OSRM
                    // Note: If OSRM fails (network), it might fallback to direct but mode stays osrm
                    assert(points[1].mode === 'osrm', 'Point 1 mode is OSRM');
                    
                    // Toggle to Manual
                    togglePointMode(1);
                    await wait(500);
                    assert(points[1].mode === 'direct', 'Point 1 mode toggled to direct');
                    
                    console.groupEnd();
                }
                
                async function testPerformance() {
                    console.group('Test Performance (50 points)');
                    clearAll();
                    var start = performance.now();
                    for(var i=0; i<50; i++) {
                        addPoint({lat: -6.2 + (i*0.001), lng: 106.8 + (i*0.001)});
                    }
                    var end = performance.now();
                    assert(points.length === 50, 'Added 50 points');
                    console.log('Time taken: ' + (end - start).toFixed(2) + 'ms');
                    assert((end - start) < 3000, 'Performance OK (<3s)');
                    console.groupEnd();
                }

                try {
                    await testUndo();
                    await testModeSwitch();
                    await testPerformance();
                    console.log(`Tests Completed. Passed: ${passed}, Failed: ${failed}`);
                    alert(`Tests Completed.\nPassed: ${passed}\nFailed: ${failed}`);
                } catch (e) {
                    console.error('Test Suite Error:', e);
                    alert('Test Suite Error: ' + e.message);
                }
            };

            // Export Image Logic
            els.exportImage = document.getElementById('rl-export-image');
            els.exportImage.addEventListener('click', function() {
                if (routePoints.length < 2) {
                    setStatus('Minimal 2 titik untuk export image');
                    return;
                }
                
                setStatus('Generating image...');

                var style = getStyle();

                document.getElementById('rl-export-dist').textContent = document.getElementById('rl-distance-km').textContent;
                document.getElementById('rl-export-time').textContent = document.getElementById('rl-est-time').textContent;
                document.getElementById('rl-export-name').textContent = els.name.value || 'Untitled Route';

                var startLabel = document.getElementById('rl-export-start-label');
                var finishLabel = document.getElementById('rl-export-finish-label');
                if (startLabel) startLabel.style.color = style.start;
                if (finishLabel) finishLabel.style.color = style.finish;

                var exportSvgEl = document.getElementById('rl-export-svg');
                if (exportSvgEl) {
                    function hexToRgba(hex, a) {
                        var m = String(hex || '').match(/^#([0-9a-f]{6})$/i);
                        if (!m) return 'rgba(204,255,0,' + (a || 0.4) + ')';
                        var h = m[1];
                        var r = parseInt(h.substring(0, 2), 16);
                        var g = parseInt(h.substring(2, 4), 16);
                        var b = parseInt(h.substring(4, 6), 16);
                        return 'rgba(' + r + ',' + g + ',' + b + ',' + (a || 0.4) + ')';
                    }
                    exportSvgEl.style.filter = 'drop-shadow(0 0 15px ' + hexToRgba(style.route, 0.4) + ')';
                }

                var mainElevSvg = els.elevSvg.querySelector('path[fill="url(#rlElevFill)"]');
                var mainElevLine = els.elevSvg.querySelector('path[stroke="' + style.route + '"]') || els.elevSvg.querySelector('path[stroke="#ccff00"]');
                var exportElevSvg = document.getElementById('rl-export-elev-svg');
                exportElevSvg.innerHTML = '';
                
                if (mainElevSvg && mainElevLine) {
                    exportElevSvg.innerHTML += '<defs><linearGradient id="rlExportElevFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#ccff00" stop-opacity="0.5"></stop><stop offset="100%" stop-color="#ccff00" stop-opacity="0.1"></stop></linearGradient></defs>';
                    exportElevSvg.innerHTML += mainElevSvg.outerHTML.replace('url(#rlElevFill)', 'url(#rlExportElevFill)');
                    exportElevSvg.innerHTML += mainElevLine.outerHTML;
                    
                    var metaText = els.elevMeta.textContent;
                    var minM = metaText.match(/Min\s+(\d+)/);
                    var maxM = metaText.match(/Max\s+(\d+)/);
                    if (minM) document.getElementById('rl-export-min-elev').textContent = minM[0] + 'm';
                    if (maxM) document.getElementById('rl-export-max-elev').textContent = maxM[0] + 'm';
                    
                    var gain = (maxM ? parseInt(maxM[1]) : 0) - (minM ? parseInt(minM[1]) : 0);
                    document.getElementById('rl-export-gain').textContent = '+' + gain + 'm';
                }

                var startP = routePoints[0];
                var endP = routePoints[routePoints.length-1];
                document.getElementById('rl-export-start').textContent = startP.lat.toFixed(4) + ', ' + startP.lng.toFixed(4);

                var qrContainer = document.getElementById('rl-export-qr');
                qrContainer.innerHTML = '';
                qrContainer.style.width = '72px';
                qrContainer.style.height = '72px';

                function canvasHasInk(c) {
                    if (!c || !c.getContext || c.width < 10 || c.height < 10) return false;
                    try {
                        var ctx = c.getContext('2d');
                        if (!ctx) return false;
                        var w = c.width;
                        var h = c.height;
                        var sample = ctx.getImageData(0, 0, w, h).data;
                        for (var i = 0; i < 25; i++) {
                            var x = Math.floor((i % 5) * (w / 5) + (w / 10));
                            var y = Math.floor(Math.floor(i / 5) * (h / 5) + (h / 10));
                            var idx = (y * w + x) * 4;
                            var r = sample[idx];
                            var g = sample[idx + 1];
                            var b = sample[idx + 2];
                            var a = sample[idx + 3];
                            if (a > 0 && (r < 240 || g < 240 || b < 240)) return true;
                        }
                        return false;
                    } catch (e) {
                        return true;
                    }
                }

                function normalizeQrToImage() {
                    var qrCanvas = qrContainer.querySelector('canvas');
                    if (qrCanvas && qrCanvas.toDataURL && canvasHasInk(qrCanvas)) {
                        try {
                            var url = qrCanvas.toDataURL('image/png');
                            if (typeof url === 'string' && url.indexOf('data:image') === 0) {
                                qrContainer.innerHTML = '';
                                var img = document.createElement('img');
                                img.width = 70;
                                img.height = 70;
                                img.src = url;
                                img.style.display = 'block';
                                img.style.imageRendering = 'pixelated';
                                qrContainer.appendChild(img);
                                return true;
                            }
                        } catch (e) {}
                    }
                    var qrImg = qrContainer.querySelector('img');
                    if (qrImg) {
                        qrImg.style.display = 'block';
                        qrImg.style.imageRendering = 'pixelated';
                        return (qrImg.complete && qrImg.naturalWidth > 0);
                    }
                    return false;
                }

                function waitForQrReady(done) {
                    var started = Date.now();
                    (function tick() {
                        if (normalizeQrToImage()) {
                            done();
                            return;
                        }
                        if (Date.now() - started > 3000) {
                            done();
                            return;
                        }
                        setTimeout(tick, 80);
                    })();
                }

                try {
                    new QRCode(qrContainer, {
                        text: "https://www.google.com/maps/search/?api=1&query=" + startP.lat + "," + startP.lng,
                        width: 70,
                        height: 70,
                        colorDark : "#0f172a",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.L
                    });
                    setTimeout(normalizeQrToImage, 120);
                } catch(e) {
                    console.error("QR Code Error:", e);
                }

                document.getElementById('rl-export-finish').textContent = endP.lat.toFixed(4) + ', ' + endP.lng.toFixed(4);

                var svg = document.getElementById('rl-export-svg');
                svg.innerHTML = '';
                
                var minLat = Infinity, maxLat = -Infinity, minLng = Infinity, maxLng = -Infinity;
                routePoints.forEach(function(p) {
                    minLat = Math.min(minLat, p.lat);
                    maxLat = Math.max(maxLat, p.lat);
                    minLng = Math.min(minLng, p.lng);
                    maxLng = Math.max(maxLng, p.lng);
                });
                
                var latSpan = maxLat - minLat;
                var lngSpan = maxLng - minLng;
                var padLat = latSpan * 0.1;
                var padLng = lngSpan * 0.1;
                minLat -= padLat; maxLat += padLat;
                minLng -= padLng; maxLng += padLng;
                
                var w = 800;
                var h = 1000;
                
                // Fix Aspect Ratio: Use uniform scaling based on Mercator projection approximation
                var centerLat = (minLat + maxLat) / 2;
                var cosLat = Math.cos(centerLat * Math.PI / 180);
                var degW = (maxLng - minLng) * cosLat;
                var degH = (maxLat - minLat);
                var scale = Math.min(w / degW, h / degH);
                var drawW = degW * scale;
                var drawH = degH * scale;
                var offX = (w - drawW) / 2;
                var offY = (h - drawH) / 2;

                function getXY(lat, lng) {
                    return {
                        x: offX + (lng - minLng) * cosLat * scale,
                        y: offY + (maxLat - lat) * scale
                    };
                }

                var pathData = 'M';
                routePoints.forEach(function(p, i) {
                    var pos = getXY(p.lat, p.lng);
                    pathData += ' ' + pos.x.toFixed(1) + ' ' + pos.y.toFixed(1);
                    if (i === 0) pathData += ' L';
                });
                
                var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                path.setAttribute("d", pathData);
                path.setAttribute("fill", "none");
                path.setAttribute("stroke", style.route);
                path.setAttribute("stroke-width", "8");
                path.setAttribute("stroke-linecap", "round");
                path.setAttribute("stroke-linejoin", "round");
                svg.appendChild(path);

                // Add direction arrows
                var totalPoints = routePoints.length;
                if (totalPoints > 5) {
                    var arrowIndices = [
                        Math.floor(totalPoints * 0.25),
                        Math.floor(totalPoints * 0.5),
                        Math.floor(totalPoints * 0.75)
                    ];
                    
                    arrowIndices.forEach(function(idx) {
                        if (idx < totalPoints - 1) {
                            var p1 = routePoints[idx];
                            var p2 = routePoints[idx + 1];
                            
                            var pos1 = getXY(p1.lat, p1.lng);
                            var pos2 = getXY(p2.lat, p2.lng);
                            
                            var angle = Math.atan2(pos2.y - pos1.y, pos2.x - pos1.x) * 180 / Math.PI;
                            
                            var arrow = document.createElementNS("http://www.w3.org/2000/svg", "path");
                            arrow.setAttribute("d", "M-6,-6 L6,0 L-6,6"); // Simple arrowhead
                            arrow.setAttribute("fill", "none");
                            arrow.setAttribute("stroke", style.route);
                            arrow.setAttribute("stroke-width", "3");
                            arrow.setAttribute("stroke-linecap", "round");
                            arrow.setAttribute("stroke-linejoin", "round");
                            arrow.setAttribute("transform", "translate(" + pos1.x + "," + pos1.y + ") rotate(" + angle + ")");
                            svg.appendChild(arrow);
                        }
                    });
                }
                
                var startPos = getXY(routePoints[0].lat, routePoints[0].lng);
                var startX = startPos.x;
                var startY = startPos.y;
                
                var endPos = getXY(routePoints[routePoints.length-1].lat, routePoints[routePoints.length-1].lng);
                var endX = endPos.x;
                var endY = endPos.y;
                
                // Start Marker (Green Circle with S)
                var startG = document.createElementNS("http://www.w3.org/2000/svg", "g");
                var startDot = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                startDot.setAttribute("cx", startX); startDot.setAttribute("cy", startY); startDot.setAttribute("r", "14"); 
                startDot.setAttribute("fill", style.start); startDot.setAttribute("stroke", style.marker); startDot.setAttribute("stroke-width", "3");
                
                var startText = document.createElementNS("http://www.w3.org/2000/svg", "text");
                startText.setAttribute("x", startX); startText.setAttribute("y", startY); startText.setAttribute("dy", "5");
                startText.setAttribute("text-anchor", "middle"); startText.setAttribute("fill", "#ffffff"); 
                startText.setAttribute("font-family", "Arial, sans-serif"); startText.setAttribute("font-weight", "bold"); startText.setAttribute("font-size", "14");
                startText.textContent = "S";
                
                startG.appendChild(startDot);
                startG.appendChild(startText);
                svg.appendChild(startG);

                // Finish Marker (Red Circle with F)
                var endG = document.createElementNS("http://www.w3.org/2000/svg", "g");
                var endDot = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                endDot.setAttribute("cx", endX); endDot.setAttribute("cy", endY); endDot.setAttribute("r", "14"); 
                endDot.setAttribute("fill", style.finish); endDot.setAttribute("stroke", style.marker); endDot.setAttribute("stroke-width", "3");
                
                var endText = document.createElementNS("http://www.w3.org/2000/svg", "text");
                endText.setAttribute("x", endX); endText.setAttribute("y", endY); endText.setAttribute("dy", "5");
                endText.setAttribute("text-anchor", "middle"); endText.setAttribute("fill", "#ffffff"); 
                endText.setAttribute("font-family", "Arial, sans-serif"); startText.setAttribute("font-weight", "bold"); endText.setAttribute("font-size", "14");
                endText.textContent = "F";
                
                endG.appendChild(endDot);
                endG.appendChild(endText);
                svg.appendChild(endG);
                
                var card = document.getElementById('rl-export-card');

                setTimeout(function() {
                    var _ = card.offsetHeight;

                    waitForQrReady(function () {
                        requestAnimationFrame(function () {
                            requestAnimationFrame(function () {
                                html2canvas(card, {
                                    scale: 2,
                                    backgroundColor: '#0f172a',
                                    useCORS: true,
                                    allowTaint: true
                                }).then(function(canvas) {
                                    var link = document.createElement('a');
                                    link.download = 'ruanglari-route-' + Date.now() + '.png';
                                    link.href = canvas.toDataURL('image/png');
                                    link.click();
                                    setStatus('Export selesai');
                                }).catch(function(err) {
                                    console.error(err);
                                    setStatus('Export gagal');
                                });
                            });
                        });
                    });
                }, 250);
            });

        })();
    </script>
@endpush
