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
                margin-bottom: 0 !important;
                z-index: 9999 !important;
                border-top: 1px solid rgba(51, 65, 85, 0.5) !important;
                border-radius: 20px 20px 0 0 !important;
                background-color: rgba(15, 23, 42, 0.98) !important;
                backdrop-filter: blur(12px) !important;
                box-shadow: 0 -10px 25px -5px rgba(0, 0, 0, 0.3), 0 -8px 10px -6px rgba(0, 0, 0, 0.3) !important;
                padding: 10px 12px !important;
                padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px)) !important;
                max-height: 85vh !important;
                overflow-y: auto !important;
                transition: max-height 0.25s cubic-bezier(0.4, 0, 0.2, 1), padding 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            .rl-mobile-sticky-ringkasan.rl-minimized {
                max-height: 52px !important;
                padding-top: 4px !important;
                padding-bottom: calc(4px + env(safe-area-inset-bottom, 0px)) !important;
                margin-bottom: 0 !important;
                overflow: hidden !important;
                border-radius: 14px 14px 0 0 !important;
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
                        <h1 class="text-3xl md:text-4xl font-black tracking-tighter text-white italic">
                            BUAT <span class="text-neon">RUTE LARI</span>
                        </h1>
                        <p class="text-slate-400 mt-1 max-w-2xl text-sm">
                            Tap peta untuk bikin rute. Simpan, share link, atau export GPX buat dipakai di jam/aplikasi favoritmu.
                        </p>
                    </div>
                    <div class="w-full sm:w-auto grid grid-cols-3 sm:flex items-center gap-1.5 sm:gap-2">
                        <a href="{{ route('gpx.index') }}" class="whitespace-nowrap px-2 sm:px-3.5 py-2 rounded-xl bg-slate-900/60 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-600 transition text-[10px] xs:text-[11px] sm:text-xs font-bold flex items-center justify-center gap-1 sm:gap-1.5 shadow-sm text-center">
                            
                            <span class="truncate">Database GPX</span>
                        </a>
                        <button id="btn-open-submit-gpx-modal" type="button" class="whitespace-nowrap px-2 sm:px-3.5 py-2 rounded-xl bg-neon text-dark hover:bg-white transition text-[10px] xs:text-[11px] sm:text-xs font-black flex items-center justify-center gap-1 sm:gap-1.5 shadow-md shadow-neon/10 text-center">
                            
                            <span class="truncate">Submit GPX</span>
                        </button>                        
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
                <!-- Left Column: Setup & AI Route Generator -->
                <div class="lg:col-span-4 space-y-4">
                    <!-- Setup Panel -->
                    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-4 md:p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-black tracking-wider text-slate-200 uppercase flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-slate-400 text-xs"></i>
                                Setup Rute
                            </div>                            
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-1">Nama Rute</label>
                                <input id="rl-route-name" type="text" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-500 transition" placeholder="Contoh: Long Run Minggu Pagi">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-1">Target Pace</label>
                                <div class="flex items-center gap-2">
                                    <input id="rl-pace-min" inputmode="numeric" type="number" min="0" 
                                        class="w-16 bg-slate-950/60 border border-slate-800 rounded-xl px-2.5 py-2 text-xs text-white font-bold text-center focus:outline-none focus:ring-1 focus:ring-slate-500" 
                                        value="6" placeholder="00">
                                    
                                    <span class="text-slate-400 font-bold">:</span>
                                    
                                    <input id="rl-pace-sec" inputmode="numeric" type="number" min="0" max="59" 
                                        class="w-16 bg-slate-950/60 border border-slate-800 rounded-xl px-2.5 py-2 text-xs text-white font-bold text-center focus:outline-none focus:ring-1 focus:ring-slate-500" 
                                        value="0" placeholder="00">
                                    
                                    <span class="text-xs text-slate-400 font-bold whitespace-nowrap">/km</span>
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-1">Jam Start Aktivitas</label>
                                <input id="rl-start-time" type="datetime-local" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-bold focus:outline-none focus:ring-1 focus:ring-slate-500">
                                <div id="rl-start-time-error" class="text-xs text-red-500 mt-1.5 hidden"></div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 bg-slate-950/40 border border-slate-800 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                    <input id="rl-follow-road" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked>
                                    <span>Ikuti jalan (OSRM)</span>
                                </label>
                                <label class="flex items-center gap-2 bg-slate-950/40 border border-slate-800 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                    <input id="rl-show-directions" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked>
                                    <span>Tampilkan arah rute</span>
                                </label>
                            </div>

                            <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-3">
                                <div class="text-[11px] font-black tracking-wider text-slate-300 uppercase mb-2.5">Warna & Indikator</div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center justify-between gap-2 bg-slate-900/60 border border-slate-800 rounded-lg px-2.5 py-1.5">
                                        <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Route</div>
                                        <input id="rl-color-route" type="color" value="#FC4C02" class="w-7 h-6 bg-transparent cursor-pointer border-0">
                                    </div>
                                    <div class="flex items-center justify-between gap-2 bg-slate-900/60 border border-slate-800 rounded-lg px-2.5 py-1.5">
                                        <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Marker</div>
                                        <input id="rl-color-marker" type="color" value="#60a5fa" class="w-7 h-6 bg-transparent cursor-pointer border-0">
                                    </div>
                                    <div class="flex items-center justify-between gap-2 bg-slate-900/60 border border-slate-800 rounded-lg px-2.5 py-1.5">
                                        <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Start</div>
                                        <input id="rl-color-start" type="color" value="#22c55e" class="w-7 h-6 bg-transparent cursor-pointer border-0">
                                    </div>
                                    <div class="flex items-center justify-between gap-2 bg-slate-900/60 border border-slate-800 rounded-lg px-2.5 py-1.5">
                                        <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Finish</div>
                                        <input id="rl-color-finish" type="color" value="#ef4444" class="w-7 h-6 bg-transparent cursor-pointer border-0">
                                    </div>
                                    <div class="flex items-center justify-between gap-2 bg-slate-900/60 border border-slate-800 rounded-lg px-2.5 py-1.5 col-span-2">
                                        <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Panah Arah</div>
                                        <input id="rl-color-arrow" type="color" value="#ccff00" class="w-7 h-6 bg-transparent cursor-pointer border-0">
                                    </div>
                                </div>
                                <div class="mt-2 bg-slate-900/60 border border-slate-800 rounded-lg px-2.5 py-2 space-y-1.5">
                                    <label class="flex items-center justify-between gap-2 cursor-pointer select-none">
                                        <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Tampilkan Panah Arah</div>
                                        <input id="rl-show-arrows" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked>
                                    </label>
                                    <div id="rl-arrow-interval-wrap">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Interval Arah</div>
                                            <div id="rl-arrow-interval-label" class="text-[11px] font-black text-slate-200">250m</div>
                                        </div>
                                        <input id="rl-arrow-interval" type="range" min="30" max="500" step="10" value="250" class="mt-1.5 w-full accent-[#ccff00]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Route Generator Panel -->
                    <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl p-4 md:p-5 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-black tracking-wider text-slate-200 uppercase flex items-center gap-2">
                                AI Route Generator
                            </div>
                            <span class="text-[9px] font-bold text-amber-400/90 bg-amber-400/10 px-2 py-0.5 rounded border border-amber-400/30 uppercase tracking-wider">BETA</span>
                        </div>

                        <div class="mt-3.5 space-y-3">
                            <div class="text-[11px] text-slate-300 bg-slate-950/40 border border-slate-800 rounded-xl p-2.5 leading-relaxed">
                                <strong class="text-white">Tips:</strong> Klik peta 1x untuk lokasi Start. Jika belum, AI pakai titik tengah peta.
                            </div>
                            
                            <div>
                                <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-1">Jarak Target (km)</label>
                                <div class="grid grid-cols-4 gap-1.5">
                                    <button type="button" class="rl-ai-dist-btn py-1.5 rounded-lg bg-slate-800 border border-slate-600 text-white font-bold transition text-xs active" data-dist="5">5K</button>
                                    <button type="button" class="rl-ai-dist-btn py-1.5 rounded-lg bg-slate-950/60 border border-slate-800 text-slate-300 font-bold hover:border-slate-700 hover:text-white transition text-xs" data-dist="10">10K</button>
                                    <button type="button" class="rl-ai-dist-btn py-1.5 rounded-lg bg-slate-950/60 border border-slate-800 text-slate-300 font-bold hover:border-slate-700 hover:text-white transition text-xs" data-dist="21">21K</button>
                                    <div class="relative">
                                        <input id="rl-ai-custom-dist" type="number" step="0.5" min="1" max="100" class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-1.5 py-1.5 text-white font-bold text-center text-xs focus:outline-none focus:ring-1 focus:ring-slate-500" placeholder="Lainnya">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-1">Arah Rute</label>
                                    <select id="rl-ai-direction" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-2.5 py-2 text-white text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-slate-500">
                                        <option value="random">Acak / Sembarang</option>
                                        <option value="north">Utara</option>
                                        <option value="east">Timur</option>
                                        <option value="south">Selatan</option>
                                        <option value="west">Barat</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-1">Toleransi Jarak</label>
                                    <select id="rl-ai-tolerance" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-2.5 py-2 text-white text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-slate-500">
                                        <option value="0.05">Sangat Presisi (±50m)</option>
                                        <option value="0.12" selected>Ketat & Akurat (±100m)</option>
                                        <option value="0.25">Standar (±250m)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Opsi Rute & Larangan Checklist -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block">Opsi Rute & Larangan</label>
                                    <span class="text-[10px] text-slate-400">Kustom AI</span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <label class="relative flex items-center gap-2 bg-slate-950/50 border border-slate-800 rounded-xl px-2.5 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                        <input id="rl-ai-loop" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked>
                                        <span class="truncate">Kembali ke Start</span>
                                    </label>
                                    <label class="relative flex items-center gap-2 bg-slate-950/50 border border-slate-800 rounded-xl px-2.5 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                        <input id="rl-ai-avoid-gang" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked>
                                        <span class="truncate">Hindari Gang</span>
                                    </label>
                                    <label class="relative flex items-center gap-2 bg-slate-950/30 border border-slate-800/60 rounded-xl px-2.5 py-2 text-[11px] font-semibold text-slate-400 cursor-not-allowed select-none opacity-60">
                                        <input id="rl-ai-avoid-toll" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked disabled>
                                        <span class="truncate">Hindari Jalan Tol</span>
                                    </label>
                                    <label class="relative flex items-center gap-2 bg-slate-950/50 border border-slate-800 rounded-xl px-2.5 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                        <input id="rl-ai-avoid-main" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]">
                                        <span class="truncate">Hindari Jalan Besar</span>
                                    </label>
                                    <label class="relative flex items-center gap-2 bg-slate-950/50 border border-slate-800 rounded-xl px-2.5 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                        <input id="rl-ai-avoid-rail" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]" checked>
                                        <span class="truncate">Hindari Rel Kereta</span>
                                    </label>
                                    <label class="relative flex items-center gap-2 bg-slate-950/50 border border-slate-800 rounded-xl px-2.5 py-2 text-[11px] font-semibold text-slate-300 cursor-pointer hover:border-slate-700 transition select-none has-[:checked]:border-slate-600 has-[:checked]:text-white">
                                        <input id="rl-ai-avoid-intersection" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-700 bg-slate-900 accent-[#ccff00]">
                                        <span class="truncate">Hindari Perempatan</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- GPS Art Shapes & AI Section -->
                            <div class="pt-3 border-t border-slate-800/60 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block">GPS Art / Shape</label>
                                    <span class="text-[9px] font-bold text-slate-400 font-mono">Strava Art</span>
                                </div>
                                
                                <!-- Preset Shape Buttons -->
                                <div class="grid grid-cols-3 gap-1.5" id="rl-gps-art-presets">
                                    <button type="button" class="rl-shape-btn py-1.5 px-2 rounded-lg bg-slate-950/50 border border-slate-800 hover:border-slate-600 text-slate-300 hover:text-white text-[11px] font-semibold transition flex items-center justify-center gap-1.5" data-shape="pistol">
                                        <i class="fa-solid fa-gun text-[10px]"></i> Pistol
                                    </button>
                                    <button type="button" class="rl-shape-btn py-1.5 px-2 rounded-lg bg-slate-950/50 border border-slate-800 hover:border-slate-600 text-slate-300 hover:text-white text-[11px] font-semibold transition flex items-center justify-center gap-1.5" data-shape="heart">
                                        <i class="fa-solid fa-heart text-[10px]"></i> Hati
                                    </button>
                                    <button type="button" class="rl-shape-btn py-1.5 px-2 rounded-lg bg-slate-950/50 border border-slate-800 hover:border-slate-600 text-slate-300 hover:text-white text-[11px] font-semibold transition flex items-center justify-center gap-1.5" data-shape="star">
                                        <i class="fa-solid fa-star text-[10px]"></i> Bintang
                                    </button>
                                    <button type="button" class="rl-shape-btn py-1.5 px-2 rounded-lg bg-slate-950/50 border border-slate-800 hover:border-slate-600 text-slate-300 hover:text-white text-[11px] font-semibold transition flex items-center justify-center gap-1.5" data-shape="triangle">
                                        <i class="fa-solid fa-play text-[10px] rotate-[-30deg]"></i> Segitiga
                                    </button>
                                    <button type="button" class="rl-shape-btn py-1.5 px-2 rounded-lg bg-slate-950/50 border border-slate-800 hover:border-slate-600 text-slate-300 hover:text-white text-[11px] font-semibold transition flex items-center justify-center gap-1.5" data-shape="circle">
                                        <i class="fa-regular fa-circle text-[10px]"></i> Lingkaran
                                    </button>
                                    <button type="button" class="rl-shape-btn py-1.5 px-2 rounded-lg bg-slate-950/50 border border-slate-800 hover:border-slate-600 text-slate-300 hover:text-white text-[11px] font-semibold transition flex items-center justify-center gap-1.5" data-shape="number8">
                                        <i class="fa-solid fa-infinity text-[10px]"></i> Angka 8
                                    </button>
                                </div>

                                <!-- ChatGPT Custom Prompt Input -->
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Custom AI Prompt</label>
                                        <span class="text-[9px] text-slate-400 font-mono">OpenAI</span>
                                    </div>
                                    <input id="rl-ai-prompt-input" type="text" class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2 text-white text-xs placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition" placeholder="Contoh: bentuk kaktus, angka 10, huruf A">
                                </div>

                                <!-- Shape Options: Scale & Snap Mode -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Mode Garis</label>
                                        <select id="rl-gps-art-snap-mode" class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-2 py-1.5 text-white text-[11px] font-semibold focus:outline-none focus:border-slate-500 transition">
                                            <option value="osrm" selected>Ikuti Jalan (OSRM)</option>
                                            <option value="direct">Garis Langsung</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Skala Bentuk</label>
                                        <select id="rl-gps-art-scale" class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-2 py-1.5 text-white text-[11px] font-semibold focus:outline-none focus:border-slate-500 transition">
                                            <option value="1.0" selected>Presisi (1x Jarak Target)</option>
                                            <option value="1.3">Sedang (1.3x Target)</option>
                                            <option value="1.6">Luas (1.6x Target)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <button id="rl-ai-generate-btn" type="button" class="py-2 px-3 rounded-lg bg-white hover:bg-slate-100 text-slate-900 font-bold text-xs transition flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-route text-[10px]"></i>
                                    Gambar Rute
                                </button>
                                <button id="rl-ai-regenerate-btn" type="button" class="py-2 px-3 rounded-lg bg-slate-950/60 border border-slate-800 text-slate-300 hover:border-slate-600 hover:text-white font-semibold text-xs transition flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Summary, Actions, Map & Profile -->
                <div class="lg:col-span-8 space-y-4">
                    <div id="rl-summary-panel" class="rl-mobile-sticky-ringkasan lg:relative lg:bottom-auto lg:left-auto lg:right-auto lg:z-10 lg:rounded-2xl lg:border lg:border-slate-800 lg:bg-slate-900/60 lg:p-5 lg:shadow-none transition-all duration-300">
                        <!-- Mobile Sheet Toggle / Compact Info Header -->
                        <div id="rl-mobile-sheet-toggle" class="py-1 cursor-pointer lg:hidden flex flex-col items-center justify-center border-b border-slate-800/40 pb-2">
                            <div class="w-12 h-1 bg-slate-600 rounded-full mb-2"></div>
                            
                            <!-- Compact View Info (shown when minimized) -->
                            <div id="rl-compact-stats" class="hidden w-full flex items-center justify-between px-2">
                                <div class="text-xs font-black text-slate-200 flex items-center gap-2.5">
                                    <span>Jarak: <span id="rl-distance-km-compact" class="text-slate-100 font-bold">0.00</span> km</span>
                                    <span class="text-slate-600">|</span>
                                    <span>Waktu: <span id="rl-est-time-compact" class="text-white">00:00:00</span></span>
                                </div>
                                <div class="text-[10px] font-black text-slate-300 flex items-center gap-1 uppercase tracking-wider">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg>
                                </div>
                            </div>
                            
                            <!-- Action hint when expanded -->
                            <span id="rl-mobile-toggle-hint" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tap to Minimize</span>
                        </div>

                        <div id="rl-expanded-content" class="space-y-3 md:space-y-4">
                            <div class="text-xs font-black tracking-wider text-slate-200 uppercase mb-3 hidden lg:flex items-center gap-2">
                                <i class="fa-solid fa-chart-simple text-slate-400 text-xs"></i>
                                Ringkasan Rute
                            </div>
                            <div class="grid grid-cols-4 gap-1 sm:gap-2 md:gap-3">
                                <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-1 sm:p-2 md:p-3 text-center md:text-left min-w-0">
                                    <div class="text-[8px] sm:text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-tight truncate">Jarak</div>
                                    <div class="mt-0.5 md:mt-1 text-[11px] sm:text-sm md:text-2xl font-black text-white truncate leading-tight"><span id="rl-distance-km">0.00</span><span class="text-[8px] sm:text-xs md:text-sm text-slate-400 font-bold ml-0.5">km</span></div>
                                </div>
                                <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-1 sm:p-2 md:p-3 text-center md:text-left min-w-0">
                                    <div class="text-[8px] sm:text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-tight truncate">Estimasi</div>
                                    <div class="mt-0.5 md:mt-1 text-[10px] sm:text-sm md:text-2xl font-black text-white truncate leading-tight"><span id="rl-est-time">00:00:00</span></div>
                                </div>
                                <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-1 sm:p-2 md:p-3 text-center md:text-left min-w-0">
                                    <div class="text-[8px] sm:text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-tight truncate">Titik</div>
                                    <div class="mt-0.5 md:mt-1 text-[11px] sm:text-sm md:text-2xl font-black text-white truncate leading-tight"><span id="rl-points-count">0</span></div>
                                </div>
                                <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-1 sm:p-2 md:p-3 text-center md:text-left min-w-0">
                                    <div class="text-[8px] sm:text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-tight truncate">Rata-rata</div>
                                    <div class="mt-0.5 md:mt-1 text-[9px] sm:text-xs md:text-2xl font-black text-white truncate leading-tight"><span id="rl-avg-seg">0.00</span><span class="text-[7px] sm:text-xs md:text-sm text-slate-400 font-bold ml-0.5">km/seg</span></div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <!-- Toggle Button for Mobile Menu -->
                                <button id="rl-toggle-menu" type="button" class="md:hidden w-full px-3 py-2 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-300 font-bold hover:bg-slate-800 transition text-xs flex items-center justify-center gap-1.5">
                                    <span>Menu Aksi</span>
                                    <svg id="rl-toggle-menu-chevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>

                                <!-- Action buttons container -->
                                <div id="rl-collapsible-buttons" class="hidden md:grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 mt-2 md:mt-0">
                                    <button id="rl-save" type="button" class="w-full px-2.5 py-2 rounded-xl bg-slate-100 text-slate-950 font-bold hover:bg-white transition text-xs flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan
                                    </button>
                                    <button id="rl-load" type="button" class="w-full px-2.5 py-2 rounded-xl bg-slate-950/60 border border-slate-700 text-slate-200 font-bold hover:border-slate-500 hover:text-white transition text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-folder-open text-xs text-slate-400"></i> Muat
                                    </button>
                                    <button id="rl-share" type="button" class="w-full px-2.5 py-2 rounded-xl bg-neon text-dark font-black hover:bg-white transition text-xs flex items-center justify-center gap-1.5 shadow-md shadow-neon/15 cursor-pointer">
                                        <i class="fa-solid fa-share-nodes text-xs text-dark"></i> Share
                                    </button>
                                    
                                    <button id="rl-export-gpx" type="button" class="w-full px-2.5 py-2 rounded-xl bg-slate-950/60 border border-slate-700 text-slate-200 font-bold hover:border-slate-500 hover:text-white transition text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-file-export text-xs text-slate-400"></i> Export GPX
                                    </button>
                                    <button id="rl-import-gpx" type="button" class="w-full px-2.5 py-2 rounded-xl bg-slate-950/60 border border-slate-700 text-slate-200 font-bold hover:border-slate-500 hover:text-white transition text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-file-import text-xs text-slate-400"></i> Import GPX
                                    </button>
                                </div>
                                <input id="rl-import-gpx-file" type="file" accept=".gpx" class="hidden">
                            </div>

                            <section class="mt-3 bg-slate-950/40 border border-slate-800 rounded-2xl p-4" id="strava-form-panel">
                                @php
                                    $hasStrava = auth()->check() && auth()->user() && (
                                        !empty(auth()->user()->strava_access_token) || 
                                        !empty(auth()->user()->strava_refresh_token) || 
                                        !empty(auth()->user()->strava_id)
                                    );
                                @endphp
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-[#FC4C02]/15 border border-[#FC4C02]/40 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-[#FC4C02]" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-white">Export ke Strava</div>
                                            <div class="text-[11px] text-slate-300 font-medium">Isi form lalu post activity otomatis setelah authorize.</div>
                                        </div>
                                    </div>
                                    <button id="rl-strava-toggle" type="button" class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-bold hover:border-slate-600 hover:text-white transition text-xs">
                                        Buka
                                    </button>
                                </div>

                            <div id="rl-strava-panel-body" class="hidden mt-4 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Nama Aktivitas</label>
                                        <input id="rl-strava-name" type="text" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs" placeholder="Mis. Easy Run">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Tanggal & Waktu</label>
                                        <input id="rl-strava-start" type="datetime-local" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Device</label>
                                        <select id="rl-strava-device" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-bold focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs">
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
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Pace (menit/km)</label>
                                        <input id="rl-strava-pace" type="text" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs" placeholder="Contoh: 4:30">
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Avg HR</label>
                                        <input id="rl-strava-hr" type="number" min="30" max="250" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs" placeholder="BPM">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Avg Cadence</label>
                                        <input id="rl-strava-cadence" type="number" min="60" max="300" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs" placeholder="SPM">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Avg Power</label>
                                        <input id="rl-strava-power" type="number" min="0" max="2000" class="mt-1 w-full bg-slate-950/40 border border-slate-700 rounded-xl px-3 py-2 text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-slate-500 text-xs" placeholder="W">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-300">
                                        <input id="rl-strava-private" type="checkbox" class="accent-white">
                                        Private
                                    </label>
                                    <div class="text-[11px] text-slate-400 font-semibold">Rute diubah jadi GPX lalu diupload ke Strava.</div>
                                </div>

                                @auth
                                    @if(session('error') && (str_contains(strtolower(session('error')), 'strava') || str_contains(strtolower(session('error')), 'token')))
                                        <div class="p-3 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-exclamation-circle text-amber-400 shrink-0 text-sm"></i>
                                                <span>{{ session('error') }}</span>
                                            </div>
                                            <a href="{{ route('calendar.strava.connect', ['return_to' => '/tools/buat-rute-lari#strava-form-panel']) }}" 
                                               class="px-3 py-1.5 rounded-lg bg-[#FC4C02] text-white font-bold text-[11px] hover:bg-[#FC4C02]/90 transition whitespace-nowrap shrink-0 flex items-center gap-1.5 shadow-md">
                                                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                                <span>Re-sync Strava Now</span>
                                            </a>
                                        </div>
                                    @endif

                                    @if($hasStrava)
                                        <div class="flex items-center justify-between text-[11px] text-slate-400 px-1 py-0.5">
                                            <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Strava Terhubung
                                            </span>
                                            <a href="{{ route('calendar.strava.connect', ['return_to' => '/tools/buat-rute-lari#strava-form-panel']) }}" 
                                               class="text-amber-400 hover:text-amber-300 font-semibold transition flex items-center gap-1 text-[11px]">
                                                <i class="fas fa-arrows-rotate text-[10px]"></i> Re-sync / Hubungkan Ulang Strava
                                            </a>
                                        </div>
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
                                            <button id="rl-strava-submit-direct" type="submit" class="w-full px-3 py-2 rounded-xl bg-[#FC4C02] text-white font-bold hover:bg-[#FC4C02]/90 transition text-xs cursor-pointer">
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
                                            <button id="rl-strava-submit-auth" type="submit" class="w-full px-3 py-2.5 rounded-xl bg-[#FC4C02] text-white font-bold hover:bg-[#FC4C02]/90 transition text-xs flex items-center justify-center gap-2 cursor-pointer">
                                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                                <span>Hubungkan Strava & Export</span>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" 
                                            onclick="if (typeof window.openLoginModal === 'function') { window.openLoginModal(); } else { window.location.href='{{ route('login') }}'; }" 
                                            class="w-full px-3 py-2.5 rounded-xl bg-[#FC4C02] text-white font-bold hover:bg-[#FC4C02]/90 transition text-xs flex items-center justify-center gap-2 shadow-lg shadow-[#FC4C02]/20 cursor-pointer">
                                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                        <span>Login untuk Export ke Strava</span>
                                    </button>
                                @endauth
                            </div>
                        </section>

                        <div class="mt-4 text-xs text-slate-400 leading-relaxed">
                            Tips: titik bisa di-drag buat rapihin rute. Kalau mau cepat, zoom-in dulu baru tap.
                        </div>
                        </div> <!-- End of rl-expanded-content -->
                    </div>
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative">
                        <!-- Floating Top Controls Bar (Unified Search + Stats Badge) -->
                        <div class="absolute top-3 left-1/2 -translate-x-1/2 z-[500] flex flex-col sm:flex-row items-center justify-center gap-2 pointer-events-none">
                            <!-- Unified Search Pill -->
                            <div class="pointer-events-auto relative w-full sm:w-auto sm:flex-1 max-w-xs sm:max-w-sm">
                                <div class="relative group bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-2xl shadow-xl px-2.5 h-10 flex items-center gap-1.5">
                                    <!-- Magnifying Glass Icon -->
                                    <div class="text-slate-400 text-xs shrink-0 pl-0.5">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </div>
                                    
                                    <!-- Search Input -->
                                    <input id="rl-search-q" type="text" class="w-full bg-transparent border-0 px-1 py-1 text-xs text-white font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-0 truncate" placeholder="Cari kota, tempat, atau landmark...">
                                    
                                    <!-- Search Submit Button -->
                                    <button id="rl-search-btn" type="button" class="text-slate-400 hover:text-white transition shrink-0">
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </button>
                                </div>

                                <!-- Search Results Dropdown -->
                                <div id="rl-search-results" class="mt-1.5 hidden bg-slate-900/95 backdrop-blur-md border border-slate-700/80 rounded-xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto"></div>
                            </div>

                            <!-- Floating Distance Stats Badge (Stacked below on mobile, side-by-side on desktop) -->
                            <div id="rl-floating-summary-badge" class="pointer-events-auto h-9 sm:h-10 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-2xl shadow-xl px-3 flex items-center gap-2 shrink-0 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-white"><span id="rl-floating-dist">0.00</span> <span class="text-[10px] text-slate-300 font-bold">KM</span></span>
                                </div>
                                <div class="h-3.5 w-px bg-slate-700/80 hidden sm:block"></div>
                                <div class="hidden sm:flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock text-slate-400 text-xs"></i>
                                    <span id="rl-floating-time" class="font-bold text-slate-200">00:00:00</span>
                                </div>
                                <div class="h-3.5 w-px bg-slate-700/80 hidden md:block"></div>
                                <div class="hidden md:flex items-center gap-1.5">
                                    <i class="fa-solid fa-mountain text-slate-400 text-xs"></i>
                                    <span id="rl-floating-elev" class="font-bold text-slate-300">+0m</span>
                                </div>
                            </div>
                        </div>

                        <!-- Marker Palette Controls (Left) -->
                        <div id="rl-marker-palette" class="absolute top-20 sm:top-3 left-3 z-[500] flex flex-col gap-2 pointer-events-auto">
                            <button type="button" class="rl-custom-icon-btn w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-sky-400 hover:border-sky-500 transition flex items-center justify-center shadow-lg cursor-pointer" title="Water Station" data-type="water">
                                <i class="fa-solid fa-droplet text-sm"></i>
                            </button>
                            <button type="button" class="rl-custom-icon-btn w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-orange-400 hover:border-orange-500 transition flex items-center justify-center shadow-lg cursor-pointer" title="Bahaya / Warning" data-type="warning">
                                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                            </button>
                            <button type="button" class="rl-custom-icon-btn w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-lime-400 hover:border-lime-500 transition flex items-center justify-center shadow-lg cursor-pointer" title="Perempatan / Intersection" data-type="intersection">
                                <i class="fa-solid fa-arrows-split-up-and-left text-sm"></i>
                            </button>
                            <button type="button" class="rl-custom-icon-btn w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-pink-400 hover:border-pink-500 transition flex items-center justify-center shadow-lg cursor-pointer" title="Foto Spot" data-type="photo">
                                <i class="fa-solid fa-camera text-sm"></i>
                            </button>
                            <button type="button" class="rl-custom-icon-btn w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-amber-400 hover:border-amber-500 transition flex items-center justify-center shadow-lg cursor-pointer" title="Rest Stop / Cafe" data-type="rest">
                                <i class="fa-solid fa-mug-hot text-sm"></i>
                            </button>
                            <button type="button" class="rl-custom-icon-btn w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-purple-400 hover:border-purple-500 transition flex items-center justify-center shadow-lg cursor-pointer" title="Toilet" data-type="toilet">
                                <i class="fa-solid fa-toilet text-sm"></i>
                            </button>
                        </div>

                        <!-- Drawing Controls (Right) -->
                        <div class="absolute top-20 sm:top-3 right-3 z-[500] flex flex-col gap-2">
                            <button id="rl-mode-toggle" type="button" class="w-10 h-10 rounded-xl bg-blue-600/90 border border-blue-500 text-white transition flex items-center justify-center shadow-lg" title="Mode: Tap (Ubah ke Freehand)">
                                <i class="fa-solid fa-hand-pointer"></i>
                            </button>
                            <button id="rl-undo" type="button" class="w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-white transition flex items-center justify-center shadow-lg" title="Undo">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                            <button id="rl-clear" type="button" class="w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-200 hover:text-red-400 transition flex items-center justify-center shadow-lg" title="Reset/Hapus Semua">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            
                            <div class="w-6 h-px bg-slate-700 mx-auto my-0.5"></div>

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
                                <div id="rl-elev-sub" class="text-xs text-slate-400 font-semibold mt-1">Buat rute dulu untuk lihat grafik.</div>
                            </div>
                            <div id="rl-elev-meta" class="text-xs text-slate-400 font-bold"></div>
                        </div>
                        <div class="mt-3 bg-slate-950/40 border border-slate-800 rounded-2xl overflow-hidden">
                            <svg id="rl-elev-svg" viewBox="0 0 1000 220" preserveAspectRatio="none" class="w-full h-[220px] block"></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="rl-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" onclick="if (typeof closeModal === 'function') closeModal();"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] bg-[#0c121e] border border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex flex-col z-10">
                <div class="px-6 py-4 border-b border-slate-800 bg-[#090D16] flex items-center justify-between shrink-0">
                    <div id="rl-modal-title" class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                        Muat Rute
                    </div>
                    <button id="rl-modal-close" type="button" class="w-8 h-8 rounded-full bg-slate-800/80 border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
                <div id="rl-modal-body" class="p-6 overflow-y-auto space-y-4"></div>
            </div>
        </div>
    </div>
@endsection

    <!-- Hidden Export Card (Pure Inline Hex CSS to prevent html2canvas OKLCH errors) -->
    <div id="rl-export-card" style="position: fixed; left: -9999px; top: 0; width: 800px; height: 1000px; background-color: #0c121e; font-family: 'Inter', Arial, sans-serif; overflow: hidden; color: #ffffff; z-index: -1;">
        <!-- Background Radial Gradient -->
        <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at top right, #1e293b 0%, #090d16 60%, #000000 100%); opacity: 0.95;"></div>
        
        <!-- Route SVG Container -->
        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 80px 80px 250px 80px;">
            <svg id="rl-export-svg" width="100%" height="100%" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid meet" style="filter: drop-shadow(0 0 15px rgba(204, 255, 0, 0.4));">
                <!-- Path injected dynamically -->
            </svg>
        </div>

        <!-- Header -->
        <div style="position: absolute; top: 0; left: 0; right: 0; padding: 48px; display: flex; justify-content: space-between; align-items: flex-start; z-index: 10;">
            <div>
                <h1 style="font-size: 36px; font-weight: 900; letter-spacing: -1px; margin: 0; color: #ffffff;">RUANG <span style="color: #ccff00;">LARI</span></h1>
                <p style="color: #94a3b8; font-weight: 700; letter-spacing: 2px; font-size: 13px; margin-top: 4px; text-transform: uppercase;">Route Builder</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 48px; font-weight: 900; letter-spacing: -1px; color: #ffffff; line-height: 1;" id="rl-export-dist">0.00</div>
                <div style="font-size: 16px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-top: 6px;">Kilometers</div>
            </div>
        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 40px; background: linear-gradient(to top, #000000 0%, rgba(0, 0, 0, 0.95) 80%, transparent 100%); padding-top: 100px; z-index: 10;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px;">Elevation Profile</div>
                    <div style="height: 80px; width: 100%; position: relative;">
                        <svg id="rl-export-elev-svg" width="100%" height="100%" viewBox="0 0 1000 220" preserveAspectRatio="none">
                            <!-- Elev Path -->
                        </svg>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: #94a3b8;">
                        <span id="rl-export-min-elev">0m</span>
                        <span id="rl-export-max-elev">0m</span>
                    </div>

                    <div style="margin-top: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;">Scan Lokasi Start</div>
                        <div id="rl-export-qr" style="padding: 4px; background: #ffffff; display: inline-block; border-radius: 8px;"></div>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px;">Nama Rute</div>
                        <div style="margin-top: 4px; font-size: 22px; font-weight: 900; color: #ffffff; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" id="rl-export-name"> Untitled Route </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px;">Est. Waktu</div>
                            <div style="font-size: 18px; font-weight: 700; color: #ffffff;" id="rl-export-time">00:00:00</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px;">Elev Gain</div>
                            <div style="font-size: 18px; font-weight: 700; color: #ccff00;" id="rl-export-gain">-</div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 10px; font-family: monospace; color: #64748b; border-top: 1px solid #1e293b; padding-top: 12px; margin-top: 4px;">
                        <div>
                            <span id="rl-export-start-label" style="font-weight: bold;">START</span> <span id="rl-export-start">0,0</span>
                        </div>
                        <div>
                            <span id="rl-export-finish-label" style="font-weight: bold;">FINISH</span> <span id="rl-export-finish">0,0</span>
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
        window.RL_MAPBOX_TOKEN = "{{ config('services.mapbox.token') }}";
        @if(isset($initialGpx) && $initialGpx)
            window.INITIAL_GPX_DATA = {
                id: {{ $initialGpx->id }},
                title: @json($initialGpx->title),
                coordinates: @json($initialGpx->coordinates_json ?? [])
            };
        @else
            window.INITIAL_GPX_DATA = null;
        @endif
    </script>
    <script>
        (function () {
            var elMap = document.getElementById('rl-route-map');
            if (!elMap || !window.L) return;

            var STORAGE_KEY = 'rl.routeBuilder.v1.saved';
            var STYLE_KEY = 'rl.routeBuilder.v1.style';
            var timeIsValid = true;
            var viewOnlyMode = false;

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
                modeToggle: document.getElementById('rl-mode-toggle'),
                markerPaletteToggle: document.getElementById('rl-marker-palette-toggle'),
                markerPalette: document.getElementById('rl-marker-palette'),
                followRoad: document.getElementById('rl-follow-road'),
                showDirections: document.getElementById('rl-show-directions'),
                showArrows: document.getElementById('rl-show-arrows'),
                arrowIntervalWrap: document.getElementById('rl-arrow-interval-wrap'),
                colorRoute: document.getElementById('rl-color-route'),
                colorMarker: document.getElementById('rl-color-marker'),
                colorStart: document.getElementById('rl-color-start'),
                colorFinish: document.getElementById('rl-color-finish'),
                colorArrow: document.getElementById('rl-color-arrow'),
                arrowInterval: document.getElementById('rl-arrow-interval'),
                arrowIntervalLabel: document.getElementById('rl-arrow-interval-label'),
                distanceKm: document.getElementById('rl-distance-km'),
                estTime: document.getElementById('rl-est-time'),
                distanceKmCompact: document.getElementById('rl-distance-km-compact'),
                estTimeCompact: document.getElementById('rl-est-time-compact'),
                floatingDist: document.getElementById('rl-floating-dist'),
                floatingTime: document.getElementById('rl-floating-time'),
                floatingElev: document.getElementById('rl-floating-elev'),
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
                aiTolerance: document.getElementById('rl-ai-tolerance'),
                aiGenerateBtn: document.getElementById('rl-ai-generate-btn'),
                aiRegenerateBtn: document.getElementById('rl-ai-regenerate-btn'),
                aiLoop: document.getElementById('rl-ai-loop'),
                aiAvoidGang: document.getElementById('rl-ai-avoid-gang'),
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
                        arrow: (data.arrow && /^#[0-9a-f]{6}$/i.test(data.arrow)) ? data.arrow : '#ffffff',
                        arrowIntervalM: (typeof data.arrowIntervalM === 'number' && data.arrowIntervalM >= 30 && data.arrowIntervalM <= 500) ? data.arrowIntervalM : 500,
                    };
                } catch (e) {
                    return { route: '#FC4C02', marker: '#60a5fa', start: '#22c55e', finish: '#ef4444', arrow: '#ffffff', arrowIntervalM: 500 };
                }
            }

            function setStyle(next) {
                var cur = getStyle();
                var merged = {
                    route: next.route ?? cur.route,
                    marker: next.marker ?? cur.marker,
                    start: next.start ?? cur.start,
                    finish: next.finish ?? cur.finish,
                    arrow: next.arrow ?? cur.arrow,
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
                if (els.colorArrow) els.colorArrow.value = style.arrow;
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
                els.modal.classList.add('flex');
            }

            function closeModal() {
                els.modal.classList.add('hidden');
                els.modal.classList.remove('flex');
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
                tap: false,
                keyboard: false,
            }).setView([-6.200000, 106.816666], 12);

            // Auto center to real user location on load via Geolocation API
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var userLat = pos.coords.latitude;
                    var userLng = pos.coords.longitude;
                    map.setView([userLat, userLng], 15);
                }, function(err) {
                    console.warn('GPS Geolocation warning:', err ? err.message : 'Denied/Failed');
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                });
            }

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
                    points: points.map(function(p) {
                        return {
                            lat: p.lat,
                            lng: p.lng,
                            mode: p.mode || 'osrm',
                            iconType: p.iconType || ''
                        };
                    }),
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
                    if (points.length > 0 || customMarkers.length > 0) {
                        localStorage.setItem('rl.routeBuilder.v1.draft', state);
                    } else {
                        localStorage.removeItem('rl.routeBuilder.v1.draft');
                    }
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
                        isUndoing = false;
                    }).catch(function(err) {
                        console.error('loadState async error:', err);
                        isUndoing = false;
                    });

                } catch(e) {
                    console.error('LoadState Error:', e);
                    isUndoing = false;
                }
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

            function insertPointAtSegment(segmentIdx, latlng) {
                if (segmentIdx < 1 || segmentIdx > points.length) return;

                var prevMode = (points[segmentIdx] && points[segmentIdx].mode) ? points[segmentIdx].mode : 'osrm';
                var newPoint = {
                    lat: latlng.lat,
                    lng: latlng.lng,
                    mode: prevMode,
                    segment: []
                };

                points.splice(segmentIdx, 0, newPoint);

                setStatus('Menyisipkan titik pada rute...');

                recalculateAdjacentSegments(segmentIdx).then(function() {
                    rebuildLine();
                    rebuildMarkers();
                    updateStats();
                    updateElevation();
                    setStatus('Titik berhasil disisipkan di rute');
                    pushState();
                }).catch(function() {
                    rebuildLine();
                    rebuildMarkers();
                    updateStats();
                    updateElevation();
                    pushState();
                });
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

                    (function(segmentIdx, segCoords) {
                        var poly = L.polyline(segCoords, {
                            color: color,
                            weight: 6,
                            opacity: 0.9,
                            dashArray: dashArray
                        });

                        var popupContent = document.createElement('div');
                        popupContent.className = 'text-center p-1.5 space-y-2';
                        
                        var title = document.createElement('div');
                        title.className = 'text-[11px] font-black text-white';
                        title.textContent = 'Segmen Rute #' + segmentIdx;
                        popupContent.appendChild(title);

                        var insertBtn = document.createElement('button');
                        insertBtn.type = 'button';
                        insertBtn.className = 'w-full px-3 py-2 bg-gradient-to-r bg-red-500 text-white font-black text-xs rounded-xl hover:scale-105 transition-all flex items-center justify-center gap-1.5 cursor-pointer';
                        insertBtn.innerHTML = '<i class="fa-solid fa-plus-circle text-xs"></i> <span>Tambahkan Titik Di Sini</span>';
                        insertBtn.onclick = function() {
                            var clickLatLng = poly.lastClickLatLng || map.getCenter();
                            map.closePopup();
                            insertPointAtSegment(segmentIdx, clickLatLng);
                        };
                        popupContent.appendChild(insertBtn);

                        poly.bindPopup(popupContent, { minWidth: 160 });

                        poly.on('click', function(e) {
                            L.DomEvent.stopPropagation(e);
                            poly.lastClickLatLng = e.latlng;
                        });

                        poly.addTo(routeLayer);
                    })(i, seg);
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

            function processLoadedPoints() {
                var promises = [];
                for (var i = 1; i < points.length; i++) {
                    (function(idx) {
                        if (points[idx].mode === 'osrm' && (!points[idx].segment || points[idx].segment.length < 2)) {
                            promises.push(osrmRoute([points[idx-1], points[idx]]).then(function(seg) {
                                points[idx].segment = seg;
                            }).catch(function() {
                                points[idx].segment = [points[idx-1], points[idx]];
                            }));
                        } else if (!points[idx].segment || points[idx].segment.length < 2) {
                            points[idx].segment = [points[idx-1], points[idx]];
                        }
                    })(i);
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
                if (els.floatingDist) els.floatingDist.textContent = fmt2(dist);
                if (els.floatingTime) els.floatingTime.textContent = fmtHMS(est);

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
                
                try {
                    localStorage.removeItem('rl.routeBuilder.v1.draft');
                } catch(e) {}

                rebuildLine();
                rebuildMarkers();
                updateStats();
                updateElevation();
                setStatus('Reset (Draft dibersihkan)');
                pushState();
            }
            
            // Initial state
            pushState();

            // Keyboard Shortcuts
            document.addEventListener('keydown', function(e) {
                // Ignore keyboard shortcuts if the user is typing in a text field or form input
                var activeEl = document.activeElement;
                if (activeEl) {
                    var tag = activeEl.tagName.toLowerCase();
                    if (tag === 'input' || tag === 'textarea' || activeEl.isContentEditable) {
                        return;
                    }
                }

                var key = (e.key || '').toLowerCase();
                if ((e.ctrlKey || e.metaKey) && key === 'z') {
                    e.preventDefault();
                    undo();
                }
                if ((e.ctrlKey || e.metaKey) && (key === 'y' || (e.shiftKey && key === 'z'))) {
                    e.preventDefault();
                    redo();
                }
            });

            function fitRoute() {
                if (!map) return;
                try {
                    map.invalidateSize();
                    if (typeof routeLayer !== 'undefined' && routeLayer.getLayers().length > 0) {
                        var bounds = routeLayer.getBounds();
                        if (bounds && bounds.isValid()) {
                            map.fitBounds(bounds.pad(0.18));
                            return;
                        }
                    }
                    if (points && points.length >= 2) {
                        var latlngs = points.map(function(p) { return [p.lat, p.lng]; });
                        var b = L.latLngBounds(latlngs);
                        if (b && b.isValid()) {
                            map.fitBounds(b.pad(0.18));
                        }
                    }
                } catch (e) {
                    console.warn('fitRoute failed:', e);
                }
            }

            function centerToUser() {
                if (points.length > 0 && points[0] && typeof points[0].lat === 'number' && typeof points[0].lng === 'number') {
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
                try {
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        if (pos && pos.coords && typeof pos.coords.latitude === 'number' && typeof pos.coords.longitude === 'number') {
                            var lat = pos.coords.latitude;
                            var lng = pos.coords.longitude;
                            map.setView([lat, lng], 16);
                            setStatus('Lokasi ditemukan');
                        } else {
                            throw new Error('invalid_coords');
                        }
                    }, function (err) {
                        console.warn('Geolocation failed:', err);
                        setStatus('Gagal akses GPS. Mencari via IP...');
                        getIpLocation();
                    }, { enableHighAccuracy: false, timeout: 5000 });
                } catch (e) {
                    console.error('Geolocation exception:', e);
                    setStatus('Gagal akses GPS. Mencari via IP...');
                    getIpLocation();
                }
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

            function loadGpxCoordinates(coordsArray, title) {
                if (!coordsArray || !Array.isArray(coordsArray) || coordsArray.length === 0) {
                    setStatus('Data koordinat rute tidak tersedia');
                    return;
                }
                points = coordsArray.map(function(pt) {
                    var lat = Array.isArray(pt) ? pt[0] : (pt.lat !== undefined ? pt.lat : (pt.latitude !== undefined ? pt.latitude : null));
                    var lng = Array.isArray(pt) ? pt[1] : (pt.lng !== undefined ? pt.lng : (pt.longitude !== undefined ? pt.longitude : null));
                    return {
                        lat: parseFloat(lat),
                        lng: parseFloat(lng),
                        mode: (els.followRoad && els.followRoad.checked) ? 'osrm' : 'direct',
                        segment: [],
                        iconType: ''
                    };
                }).filter(function(p) {
                    return !isNaN(p.lat) && !isNaN(p.lng) && isFinite(p.lat) && isFinite(p.lng);
                });

                if (points.length === 0) {
                    setStatus('Koordinat tidak valid');
                    return;
                }

                if (els.name && title) els.name.value = title;
                rebuildLine();
                rebuildMarkers();
                updateStats();
                updateElevation();
                if (points.length >= 2) fitRoute();
                setStatus('Rute GPX ' + (title ? ('"' + title + '" ') : '') + 'berhasil dimuat ke editor');
                pushState();
            }

            function showLoadModal() {
                openModal('Muat Rute Lari', function (container) {
                    container.innerHTML = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 p-1 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">
                                <button type="button" id="tab-btn-db-gpx" class="py-2 rounded-lg text-slate-300 hover:text-white transition">Database GPX</button>
                                <button type="button" id="tab-btn-local-gpx" class="py-2 rounded-lg text-slate-400 hover:text-white transition">Rute Tersimpan (Lokal)</button>
                            </div>

                            <div id="tab-panel-db-gpx" class="space-y-3">
                                <div class="relative">
                                    <input id="db-gpx-search-input" type="text" placeholder="Cari rute GPX komunitas / kota..." class="w-full bg-slate-950/80 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-slate-500 transition">
                                </div>
                                <div id="db-gpx-list-container" class="space-y-2 max-h-80 overflow-y-auto pr-1">
                                    <div class="text-xs text-slate-400 text-center py-6">Memuat rute GPX publik...</div>
                                </div>
                            </div>

                            <div id="tab-panel-local-gpx" class="hidden space-y-2 max-h-80 overflow-y-auto pr-1"></div>
                        </div>
                    `;

                    const btnDbTab = container.querySelector('#tab-btn-db-gpx');
                    const btnLocalTab = container.querySelector('#tab-btn-local-gpx');
                    const panelDb = container.querySelector('#tab-panel-db-gpx');
                    const panelLocal = container.querySelector('#tab-panel-local-gpx');
                    const dbListContainer = container.querySelector('#db-gpx-list-container');
                    const searchInput = container.querySelector('#db-gpx-search-input');

                    function switchTab(activeTab) {
                        if (activeTab === 'db') {
                            btnDbTab.className = 'py-2 rounded-lg bg-slate-800 text-white font-bold shadow-sm';
                            btnLocalTab.className = 'py-2 rounded-lg text-slate-400 hover:text-white transition';
                            panelDb.classList.remove('hidden');
                            panelLocal.classList.add('hidden');
                        } else {
                            btnLocalTab.className = 'py-2 rounded-lg bg-slate-800 text-white font-bold shadow-sm';
                            btnDbTab.className = 'py-2 rounded-lg text-slate-400 hover:text-white transition';
                            panelLocal.classList.remove('hidden');
                            panelDb.classList.add('hidden');
                        }
                    }

                    btnDbTab.addEventListener('click', () => switchTab('db'));
                    btnLocalTab.addEventListener('click', () => switchTab('local'));

                    // Render Local Saved Routes
                    const items = getSaved();
                    if (items.length === 0) {
                        panelLocal.innerHTML = '<div class="text-xs text-slate-400 text-center py-6">Belum ada rute tersimpan di browser Anda.</div>';
                    } else {
                        items.forEach(function (it) {
                            const row = document.createElement('div');
                            row.className = 'flex items-center justify-between gap-3 bg-slate-900/60 border border-slate-800 rounded-xl p-3';
                            row.innerHTML = `
                                <div class="min-w-0">
                                    <div class="font-bold text-white text-xs truncate">${it.name || 'Untitled'}</div>
                                    <div class="text-[10px] text-slate-400">${(it.points ? it.points.length : 0)} titik • ${new Date(it.createdAt || Date.now()).toLocaleDateString('id-ID')}</div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" class="btn-use-local px-3 py-1.5 rounded-lg bg-neon text-dark font-black text-xs hover:bg-white transition cursor-pointer">Pakai</button>
                                    <button type="button" class="btn-del-local p-1.5 rounded-lg bg-slate-800 text-red-400 hover:bg-slate-700 text-xs transition cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            `;
                            row.querySelector('.btn-use-local').addEventListener('click', function () {
                                loadEntry(it);
                                closeModal();
                            });
                            row.querySelector('.btn-del-local').addEventListener('click', function () {
                                setSaved(getSaved().filter(x => x.id !== it.id));
                                showLoadModal();
                            });
                            panelLocal.appendChild(row);
                        });
                    }

                    // Fetch Database GPX Routes
                    function fetchDatabaseGpx(query = '') {
                        dbListContainer.innerHTML = '<div class="text-xs text-slate-400 text-center py-6"><i class="fa-solid fa-spinner animate-spin mr-1"></i> Memuat rute...</div>';
                        fetch('{{ route("gpx.published.json") }}?q=' + encodeURIComponent(query))
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.items && data.items.length > 0) {
                                    dbListContainer.innerHTML = '';
                                    data.items.forEach(function(item) {
                                        const card = document.createElement('div');
                                        card.className = 'flex items-center justify-between gap-3 bg-slate-900/60 border border-slate-800 hover:border-slate-700 rounded-xl p-3 transition';
                                        
                                        const distStr = item.distance_km ? item.distance_km.toFixed(2) + ' km' : '-';
                                        const elevStr = item.elevation_gain_m !== null ? '+' + item.elevation_gain_m + 'm' : '';

                                        card.innerHTML = `
                                            <div class="min-w-0">
                                                <div class="font-bold text-white text-xs truncate">${item.title}</div>
                                                <div class="text-[10px] text-slate-300 mt-0.5 flex items-center gap-2">
                                                    <span class="text-slate-200 font-semibold">${item.city}</span>
                                                    <span>•</span>
                                                    <span class="text-neon font-mono">${distStr} ${elevStr}</span>
                                                    <span>•</span>
                                                    <span class="text-slate-400">${item.uploader}</span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-use-db-gpx px-3 py-1.5 rounded-lg bg-neon text-dark font-black text-xs hover:bg-white transition whitespace-nowrap shrink-0 cursor-pointer">Pakai Rute</button>
                                        `;

                                        card.querySelector('.btn-use-db-gpx').addEventListener('click', function() {
                                            if (item.coordinates_json && item.coordinates_json.length > 0) {
                                                loadGpxCoordinates(item.coordinates_json, item.title);
                                                closeModal();
                                            } else if (item.gpx_url) {
                                                fetch(item.gpx_url)
                                                    .then(res => res.text())
                                                    .then(xmlText => {
                                                        const parser = new DOMParser();
                                                        const xmlDoc = parser.parseFromString(xmlText, "text/xml");
                                                        let trkpts = xmlDoc.getElementsByTagName('trkpt');
                                                        if (trkpts.length === 0) trkpts = xmlDoc.getElementsByTagName('rtept');
                                                        const coords = [];
                                                        for (let i = 0; i < trkpts.length; i++) {
                                                            const lat = parseFloat(trkpts[i].getAttribute('lat'));
                                                            const lon = parseFloat(trkpts[i].getAttribute('lon'));
                                                            if (!isNaN(lat) && !isNaN(lon)) coords.push([lat, lon]);
                                                        }
                                                        loadGpxCoordinates(coords, item.title);
                                                        closeModal();
                                                    })
                                                    .catch(err => {
                                                        console.error(err);
                                                        alert('Gagal membaca file GPX.');
                                                    });
                                            }
                                        });

                                        dbListContainer.appendChild(card);
                                    });
                                } else {
                                    dbListContainer.innerHTML = '<div class="text-xs text-slate-400 text-center py-6">Tidak ada rute GPX publik yang ditemukan.</div>';
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                dbListContainer.innerHTML = '<div class="text-xs text-rose-400 text-center py-6">Gagal memuat rute dari database.</div>';
                            });
                    }

                    // Default to Database GPX tab
                    switchTab('db');
                    fetchDatabaseGpx();

                    // Search input handler
                    let searchTimeout;
                    if (searchInput) {
                        searchInput.addEventListener('input', function(e) {
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(() => {
                                fetchDatabaseGpx(e.target.value.trim());
                            }, 300);
                        });
                    }
                });
            }

            function showShareModal(url) {
                var routeName = (els.name.value || '').trim() || 'Rute Lari';
                var distText = (els.distanceKm.textContent || '0.00') + ' km';
                var timeText = els.estTime.textContent || '00:00:00';
                var elevGain = els.floatingElev ? els.floatingElev.textContent : '+0m';

                var shareTitle = 'Cek Rute Lari: ' + routeName + ' (' + distText + ' • ' + timeText + ') di RuangLari';
                var shareUrl = url || window.location.href;

                var shareWaUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareTitle + '\n' + shareUrl);
                var shareXUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(shareTitle) + '&url=' + encodeURIComponent(shareUrl);
                var shareFbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl);

                openModal('Bagikan Rute', function (container) {
                    container.innerHTML = `
                        <div class="space-y-4 text-slate-100">
                            <!-- Ringkasan Rute -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800 gap-3">
                                <div class="min-w-0">
                                    <h4 id="modal-route-title-text" class="text-sm font-bold text-white truncate"></h4>
                                    <p class="text-xs text-slate-400 mt-0.5">${distText} • Estimasi ${timeText} • Elevasi ${elevGain}</p>
                                </div>
                                <button type="button" id="modal-download-gpx-btn" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition cursor-pointer flex items-center gap-1.5 shrink-0" title="Unduh File GPX">
                                    <i class="fa-solid fa-file-export text-xs text-slate-400"></i>
                                    <span>File GPX</span>
                                </button>
                            </div>

                            <!-- 2 Aksi Utama -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Aksi 1: Bagikan Link -->
                                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="text-xs font-bold text-white mb-1">1. Bagikan Tautan Rute</div>
                                        <p class="text-xs text-slate-400 leading-relaxed mb-3">Kirim tautan ini agar teman lari bisa langsung membuka peta, koordinat, dan target pace yang sama.</p>

                                        <!-- Form Salin Link -->
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-1.5">
                                                <input id="modal-share-url-input" type="text" value="${shareUrl}" readonly class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-200 font-mono select-all focus:outline-none">
                                                <button type="button" id="modal-copy-link-btn" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5 shrink-0">
                                                    <i class="fa-regular fa-copy"></i>
                                                    <span>Salin</span>
                                                </button>
                                            </div>
                                            <!-- Feedback Salin -->
                                            <div id="modal-copy-feedback" class="hidden text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>Link berhasil disalin!</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kirim Langsung Media Sosial -->
                                    <div>
                                        <div class="text-xs text-slate-400 mb-2 font-medium">Atau bagikan langsung ke:</div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <a id="btn-share-wa" href="${shareWaUrl}" target="_blank" rel="noopener noreferrer" class="py-2 px-2 rounded-lg bg-[#25D366]/15 hover:bg-[#25D366]/25 border border-[#25D366]/30 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer">
                                                <i class="fa-brands fa-whatsapp text-[#25D366]"></i>
                                                <span>WhatsApp</span>
                                            </a>
                                            <a id="btn-share-x" href="${shareXUrl}" target="_blank" rel="noopener noreferrer" class="py-2 px-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer">
                                                <i class="fa-brands fa-x-twitter text-slate-200"></i>
                                                <span>X</span>
                                            </a>
                                            <a id="btn-share-fb" href="${shareFbUrl}" target="_blank" rel="noopener noreferrer" class="py-2 px-2 rounded-lg bg-[#1877F2]/15 hover:bg-[#1877F2]/25 border border-[#1877F2]/30 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer">
                                                <i class="fa-brands fa-facebook text-[#1877F2]"></i>
                                                <span>Facebook</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Aksi 2: Unduh Poster Gambar -->
                                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="text-xs font-bold text-white">2. Unduh Gambar Poster</div>
                                            <span class="text-[10px] text-slate-400 bg-slate-800 border border-slate-700 px-2 py-0.5 rounded">PNG HD</span>
                                        </div>
                                        <p class="text-xs text-slate-400 leading-relaxed mb-3">Infografis rute dengan peta visual, grafik elevasi, dan QR code koordinat siap share ke story/sosmed.</p>

                                        <!-- Preview Box Mini -->
                                        <div id="modal-poster-preview-container" class="w-full bg-slate-950 border border-slate-800 rounded-lg overflow-hidden h-28 flex items-center justify-center relative shadow-inner">
                                            <div class="text-center p-2 text-slate-400 text-xs">
                                                <i class="fa-solid fa-spinner animate-spin text-sm mb-1"></i>
                                                <div>Merender poster...</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Unduh -->
                                    <button type="button" id="modal-download-poster-btn" class="w-full py-2.5 px-3 rounded-lg bg-neon text-dark font-black text-xs hover:bg-white transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-download text-dark"></i>
                                        <span>Unduh Gambar Rute (PNG)</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    var titleTextEl = container.querySelector('#modal-route-title-text');
                    if (titleTextEl) titleTextEl.textContent = routeName;

                    // Copy Link Button handler with feedback
                    const copyBtn = container.querySelector('#modal-copy-link-btn');
                    const copyFeedback = container.querySelector('#modal-copy-feedback');
                    if (copyBtn) {
                        copyBtn.addEventListener('click', function() {
                            copyText(shareUrl).then(function() {
                                if (copyFeedback) copyFeedback.classList.remove('hidden');
                                copyBtn.className = 'px-3.5 py-2 rounded-lg bg-emerald-600 border border-emerald-500 text-white font-bold text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5 shrink-0';
                                copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Tersalin</span>';
                                setStatus('Link berhasil disalin');
                                setTimeout(() => {
                                    copyBtn.className = 'px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5 shrink-0';
                                    copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i> <span>Salin</span>';
                                    if (copyFeedback) copyFeedback.classList.add('hidden');
                                }, 3000);
                            }).catch(function() {
                                setStatus('Gagal menyalin link');
                            });
                        });
                    }

                    // Download GPX Button handler
                    const gpxBtn = container.querySelector('#modal-download-gpx-btn');
                    if (gpxBtn) {
                        gpxBtn.addEventListener('click', function() {
                            exportGpx();
                        });
                    }

                    // Generate Poster Preview & Download
                    const previewBox = container.querySelector('#modal-poster-preview-container');
                    const downloadPosterBtn = container.querySelector('#modal-download-poster-btn');

                    if (typeof generateRoutePoster === 'function') {
                        generateRoutePoster(false, function(canvas) {
                            if (canvas && previewBox) {
                                previewBox.innerHTML = '';
                                const previewImg = document.createElement('img');
                                previewImg.src = canvas.toDataURL('image/png');
                                previewImg.className = 'w-full h-full object-cover object-center';
                                previewBox.appendChild(previewImg);
                            }
                        });
                    }

                    if (downloadPosterBtn) {
                        downloadPosterBtn.addEventListener('click', function() {
                            if (typeof generateRoutePoster === 'function') {
                                generateRoutePoster(true);
                            }
                        });
                    }
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
                if (ai && /^\d+$/.test(ai)) nextStyle.arrowIntervalM = clamp(parseInt(ai, 10), 30, 500);
                if (Object.keys(nextStyle).length > 0) {
                    setStyle(nextStyle);
                } else {
                    applyStyleFromState(getStyle());
                }
                if (window.INITIAL_GPX_DATA && Array.isArray(window.INITIAL_GPX_DATA.coordinates) && window.INITIAL_GPX_DATA.coordinates.length > 0) {
                    scrollToMap();
                    loadGpxCoordinates(window.INITIAL_GPX_DATA.coordinates, window.INITIAL_GPX_DATA.title);
                    setTimeout(function () {
                        map.invalidateSize();
                        fitRoute();
                    }, 500);
                } else if (qs.has('gpx_id') || qs.has('gpx_slug')) {
                    var gpxParam = qs.get('gpx_id') || qs.get('gpx_slug');
                    fetch('{{ route("gpx.published.json") }}?q=' + encodeURIComponent(gpxParam))
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.success && data.items && data.items.length > 0) {
                                var itm = data.items[0];
                                if (itm.coordinates_json && itm.coordinates_json.length > 0) {
                                    scrollToMap();
                                    loadGpxCoordinates(itm.coordinates_json, itm.title);
                                    setTimeout(function() { map.invalidateSize(); fitRoute(); }, 500);
                                }
                            }
                        }).catch(function(e) {
                            console.error('Failed to auto load GPX from URL:', e);
                        });
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
                        scrollToMap();

                        // Aktifkan view-only mode — klik peta tidak akan menambah titik
                        viewOnlyMode = true;
                        freehandActive = false;

                        // Tampilkan badge view-only di atas peta
                        var viewBadge = document.createElement('div');
                        viewBadge.id = 'rl-view-only-badge';
                        viewBadge.style.cssText = 'position:absolute;bottom:12px;left:50%;transform:translateX(-50%);z-index:500;pointer-events:none;background:rgba(15,23,42,0.85);backdrop-filter:blur(8px);border:1px solid rgba(148,163,184,0.3);border-radius:999px;padding:4px 14px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;white-space:nowrap;';
                        viewBadge.innerHTML = '<i class="fa-solid fa-eye" style="color:#60a5fa"></i> Pratinjau Rute &bull; <span style="color:#22c55e;font-size:10px">Zoom/geser peta aman</span>';
                        var mapEl = document.getElementById('rl-route-map');
                        if (mapEl) mapEl.appendChild(viewBadge);

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

                        // Render immediately with decoded geometry
                        rebuildLine();
                        rebuildMarkers();
                        updateStats();
                        updateElevation();
                        setStatus('Rute dari link');
                        pushState();

                        // Auto fit rute setelah 2 detik (mapbox/tile sudah selesai load)
                        setTimeout(function () {
                            map.invalidateSize();
                            fitRoute();
                        }, 2000);
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
                var showDir = !els.showDirections || els.showDirections.checked;
                var showArr = !els.showArrows || els.showArrows.checked;
                var on = showDir && showArr;
                directionLayer.clearLayers();
                if (!on || routePoints.length < 2) return;

                var style = getStyle();
                var stepKm = (style.arrowIntervalM || 500) / 1000;
                var arrowColor = style.arrow || '#ffffff';
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
                        html: '<div style="transform:rotate(' + (angle - 90).toFixed(1) + 'deg);color:' + arrowColor + ';font-weight:900;font-size:16px;line-height:16px;text-shadow:0 0 10px rgba(0,0,0,.55)">➤</div>',
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
                var token = window.RL_MAPBOX_TOKEN;

                if (token) {
                    var mapboxProfile = profile === 'foot' ? 'mapbox/walking' : (profile === 'cycling' ? 'mapbox/cycling' : 'mapbox/' + profile);
                    var mapboxUrl = 'https://api.mapbox.com/directions/v5/' + mapboxProfile + '/' + coords + '?geometries=geojson&overview=full&steps=false&continue_straight=true&access_token=' + token;
                    return fetch(mapboxUrl, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) {
                            if (!r.ok) throw new Error('mapbox_api_error');
                            return r.json();
                        })
                        .then(function (data) {
                            var coords = data.routes && data.routes[0] && data.routes[0].geometry && data.routes[0].geometry.coordinates;
                            if (!Array.isArray(coords) || coords.length < 2) {
                                throw new Error('mapbox_no_geometry');
                            }
                            return coords.map(function (c) { return { lat: c[1], lng: c[0] }; });
                        })
                        .catch(function (err) {
                            console.warn('Mapbox routing failed, falling back to OSRM:', err);
                            return fetchOsrmFallback(waypoints, profile, excludes);
                        });
                } else {
                    return fetchOsrmFallback(waypoints, profile, excludes);
                }
            }

            function fetchOsrmFallback(waypoints, profile, excludes) {
                var osrmProfile = (profile === 'foot' || profile === 'walking') ? 'foot' : ((profile === 'cycling' || profile === 'bike') ? 'bike' : 'foot');
                var coords = waypoints.map(function (p) { return p.lng.toFixed(6) + ',' + p.lat.toFixed(6); }).join(';');
                // The public OSRM server (router.project-osrm.org) supports 'driving', 'foot', and 'bike' profiles.
                var url = 'https://router.project-osrm.org/route/v1/' + osrmProfile + '/' + coords + '?overview=full&geometries=geojson&steps=false&continue_straight=true';
                return fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) {
                        if (!r.ok) throw new Error('osrm_api_error: ' + r.status);
                        return r.json();
                    })
                    .then(function (json) {
                        if (json.code !== 'Ok') throw new Error('osrm_failed_code: ' + json.code);
                        var coords = json.routes && json.routes[0] && json.routes[0].geometry && json.routes[0].geometry.coordinates;
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
                    if (els.floatingElev) els.floatingElev.textContent = '+0m';
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
                    if (els.floatingElev) els.floatingElev.textContent = '+0m';
                    return;
                }

                var elevGain = 0;
                for (var g = 1; g < elevM.length; g++) {
                    if (typeof elevM[g] === 'number' && typeof elevM[g-1] === 'number') {
                        var diff = elevM[g] - elevM[g-1];
                        if (diff > 0) elevGain += diff;
                    }
                }
                if (els.floatingElev) els.floatingElev.textContent = '+' + Math.round(elevGain) + 'm';

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
                // Auto-minimize on mobile so the map is fully accessible for drawing
                if (window.innerWidth <= 1024) {
                    isMinimized = true;
                    summaryPanel.classList.add('rl-minimized');
                    compactStats.classList.remove('hidden');
                    toggleHint.classList.add('hidden');
                    expandedContent.classList.add('hidden');
                }

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

            if (els.modeToggle) {
                els.modeToggle.addEventListener('click', function () {
                    freehandActive = !freehandActive;
                    
                    // Update UI button appearance
                    if (freehandActive) {
                        els.modeToggle.innerHTML = '<i class="fa-solid fa-pencil"></i>';
                        els.modeToggle.title = 'Mode: Freehand (Ubah ke Tap)';
                        els.modeToggle.classList.replace('bg-blue-600/90', 'bg-neon/90');
                        els.modeToggle.classList.replace('border-blue-500', 'border-neon');
                        els.modeToggle.classList.replace('text-white', 'text-slate-900');
                    } else {
                        els.modeToggle.innerHTML = '<i class="fa-solid fa-hand-pointer"></i>';
                        els.modeToggle.title = 'Mode: Tap (Ubah ke Freehand)';
                        els.modeToggle.classList.replace('bg-neon/90', 'bg-blue-600/90');
                        els.modeToggle.classList.replace('border-neon', 'border-blue-500');
                        els.modeToggle.classList.replace('text-slate-900', 'text-white');
                    }
                    
                    setStatus(freehandActive ? 'Freehand aktif' : 'Tap mode');
                });
            }

            if (els.markerPaletteToggle && els.markerPalette) {
                els.markerPaletteToggle.addEventListener('click', function () {
                    els.markerPalette.classList.toggle('hidden');
                    els.markerPalette.classList.toggle('flex');
                    if(els.markerPalette.classList.contains('flex')) {
                        els.markerPaletteToggle.classList.add('bg-neon/90', 'text-slate-900');
                        els.markerPaletteToggle.classList.remove('bg-slate-900/90', 'text-slate-200');
                    } else {
                        els.markerPaletteToggle.classList.remove('bg-neon/90', 'text-slate-900');
                        els.markerPaletteToggle.classList.add('bg-slate-900/90', 'text-slate-200');
                    }
                });
            }

            if (els.followRoad) {
                els.followRoad.addEventListener('change', function () {
                    // Hanya ubah mode untuk titik baru, jangan update rute yang sudah ada
                    setStatus('Mode ' + (els.followRoad.checked ? 'OSRM' : 'Manual') + ' aktif');
                });
            }
            if (els.showDirections) {
                els.showDirections.addEventListener('change', function () {
                    if (els.showArrows) els.showArrows.checked = els.showDirections.checked;
                    if (els.arrowIntervalWrap) els.arrowIntervalWrap.style.display = els.showDirections.checked ? 'block' : 'none';
                    updateDirections();
                });
            }
            if (els.showArrows) {
                els.showArrows.addEventListener('change', function () {
                    if (els.showDirections) els.showDirections.checked = els.showArrows.checked;
                    if (els.arrowIntervalWrap) els.arrowIntervalWrap.style.display = els.showArrows.checked ? 'block' : 'none';
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
            if (els.colorArrow) {
                els.colorArrow.addEventListener('input', function () { setStyle({ arrow: els.colorArrow.value }); });
            }
            if (els.arrowInterval) {
                els.arrowInterval.addEventListener('input', function () {
                    var v = clamp(parseInt(els.arrowInterval.value || '250', 10), 30, 500);
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

                if (window.location.hash === '#strava-form-panel' || document.querySelector('#strava-form-panel .bg-amber-500\\/15')) {
                    els.stravaBody.classList.remove('hidden');
                    els.stravaToggle.textContent = 'Tutup';
                    syncStravaDefaults();
                }
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

            var selectedShape = '';
            var shapeBtns = document.querySelectorAll('.rl-shape-btn');
            shapeBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var isSelected = btn.classList.contains('border-white');
                    shapeBtns.forEach(function (b) {
                        b.classList.remove('border-white', 'bg-white/10', 'text-white');
                        b.classList.add('border-slate-800', 'bg-slate-950/50', 'text-slate-400');
                    });
                    if (!isSelected) {
                        btn.classList.remove('border-slate-800', 'bg-slate-950/50', 'text-slate-400');
                        btn.classList.add('border-white', 'bg-white/10', 'text-white');
                        selectedShape = btn.getAttribute('data-shape');
                    } else {
                        selectedShape = '';
                    }
                });
            });

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
                    var side = targetDist / 5.2;
                    var p0 = start;
                    var p1 = getOffsetLatLng(p0, side, angleRad - Math.PI / 4);
                    var p2 = getOffsetLatLng(p0, side * 1.3, angleRad);
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

            function removeBacktrackSpikes(coords) {
                if (!coords || coords.length < 3) return coords || [];
                var out = [coords[0]];
                for (var i = 1; i < coords.length - 1; i++) {
                    var prev = out[out.length - 1];
                    var curr = coords[i];
                    var next = coords[i + 1];

                    var d1 = haversineKm(prev, curr);
                    var d2 = haversineKm(curr, next);
                    var dDirect = haversineKm(prev, next);

                    // Skip micro 180-degree backtrack spikes on the same street segment
                    if (d1 < 0.1 && d2 < 0.1 && dDirect < 0.03 && (d1 + d2) > (dDirect * 2.5)) {
                        continue;
                    }
                    out.push(curr);
                }
                out.push(coords[coords.length - 1]);
                return out;
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
                                    return { mode: 'osrm', segment: removeBacktrackSpikes(coords) };
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
                var avoidGang = els.aiAvoidGang ? els.aiAvoidGang.checked : true;

                var profile = avoidGang ? 'cycling' : 'foot';
                var excludes = [];
                excludes.push('toll');

                if (els.aiAvoidMain && els.aiAvoidMain.checked) {
                    excludes.push('motorway', 'trunk');
                }

                var promptInputEl = document.getElementById('rl-ai-prompt-input');
                var customPrompt = promptInputEl ? promptInputEl.value.trim() : '';

                var snapModeEl = document.getElementById('rl-gps-art-snap-mode');
                var snapMode = snapModeEl ? snapModeEl.value : 'direct';

                var scaleEl = document.getElementById('rl-gps-art-scale');
                var scaleFactor = scaleEl ? parseFloat(scaleEl.value) : 1.0;

                if (selectedShape || customPrompt) {
                    var targetShape = customPrompt ? customPrompt : selectedShape;
                    setStatus('Menghitung rute GPS Art (' + targetShape + ')...');
                    fetch('{{ route('tools.buat-rute-lari.ai-generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            lat: savedStart.lat,
                            lng: savedStart.lng,
                            target_distance: targetDist,
                            shape: targetShape,
                            scale_factor: scaleFactor,
                            use_ai: customPrompt ? true : false
                        })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success && data.waypoints && data.waypoints.length > 0) {
                            var wpts = data.waypoints.map(function(w) { return { lat: parseFloat(w.lat), lng: parseFloat(w.lng) }; });
                            
                            if (data.relocated_center && data.relocated_center.lat && data.relocated_center.lng) {
                                map.panTo([data.relocated_center.lat, data.relocated_center.lng]);
                            }

                            clearAll();
                            if (snapMode === 'direct') {
                                wpts.forEach(function (w) {
                                    addPoint({ lat: w.lat, lng: w.lng, mode: 'direct' });
                                });
                                setStatus('Rute Vektor ' + (data.shape_name || targetShape) + ' berhasil digambar!');
                            } else {
                                fetchLoopRoute(wpts, profile, excludes).then(function(res) {
                                    if (res && res.points && res.points.length > 0) {
                                        res.points.forEach(function (p) { addPoint(p); });
                                        setStatus((data.mode === 'osm_smart_feature' ? 'Geometri Riil: ' : 'Rute OSRM: ') + (data.shape_name || targetShape) + ' berhasil dibuat!');
                                    }
                                });
                            }
                        } else {
                            alert(data.message || 'Gagal menghasilkan rute AI.');
                        }
                    })
                    .catch(function (err) {
                        console.error('AI route error:', err);
                    });
                    return;
                }

                var initialWpts = getAiWaypoints(savedStart, targetDist, angleRad, isLoop, avoidIntersections);

                var targetTolerance = parseFloat(els.aiTolerance ? els.aiTolerance.value : 0.12);
                if (isNaN(targetTolerance) || targetTolerance <= 0) targetTolerance = 0.12;

                var bestRes = null;
                var minDiff = Infinity;

                // High-Precision Multi-Pass Distance Calibration with Best-Pass Tracking & Angle Adaptation
                function calibrateRouteDistance(currentWpts, currentAngle, passCount) {
                    return fetchLoopRoute(currentWpts, profile, excludes).then(function(res) {
                        var currentDist = res.distance;
                        if (!currentDist || currentDist <= 0) {
                            throw new Error('distance_zero');
                        }

                        var diff = Math.abs(currentDist - targetDist);
                        if (diff < minDiff) {
                            minDiff = diff;
                            bestRes = res;
                        }

                        // Stop if within target tolerance or completed 5 passes
                        if (diff <= targetTolerance || passCount >= 5) {
                            return bestRes || res;
                        }

                        var factor = targetDist / currentDist;
                        if (factor < 0.4 || factor > 2.5) {
                            return bestRes || res;
                        }

                        // Clamp scaling factor to prevent wild divergence on tight urban blocks
                        var clampedFactor = Math.max(0.55, Math.min(1.7, factor));
                        setStatus('Kalibrasi presisi rute #' + passCount + ' (' + fmt2(currentDist) + ' km → target ' + targetDist + ' km)...');

                        var nextAngle = currentAngle;
                        // Jitter direction slightly if road layout causes persistent detour after pass 2
                        if (passCount >= 2 && diff > targetTolerance * 1.5 && (!els.aiDirection || els.aiDirection.value === 'random')) {
                            nextAngle += (Math.random() * 0.4 - 0.2);
                        }

                        var calibratedTarget = targetDist * Math.pow(clampedFactor, 0.92);
                        var nextWpts = getAiWaypoints(savedStart, calibratedTarget, nextAngle, isLoop, avoidIntersections);

                        return calibrateRouteDistance(nextWpts, nextAngle, passCount + 1).catch(function() {
                            return bestRes || res;
                        });
                    });
                }

                calibrateRouteDistance(initialWpts, angleRad, 1)
                    .then(function(finalRes) {
                        points = finalRes.points;
                        
                        rebuildLine();
                        rebuildMarkers();
                        updateStats();
                        updateElevation();
                        
                        setTimeout(fitRoute, 200);
                        
                        var finalDiff = Math.abs(finalRes.distance - targetDist);
                        var diffMeters = Math.round(finalDiff * 1000);
                        var statusMsg = (finalDiff <= targetTolerance)
                            ? 'Rute AI presisi tinggi dibuat (' + fmt2(finalRes.distance) + ' km, selisih ' + diffMeters + 'm)'
                            : 'Rute AI berhasil dibuat (' + fmt2(finalRes.distance) + ' km, selisih ' + diffMeters + 'm)';
                        setStatus(statusMsg);
                        pushState();
                    })
                    .catch(function(err) {
                        console.error('AI Generation failed:', err);
                        setStatus('AI gagal membuat rute. Silakan coba lagi.');
                    });
            }


            // Blur form controls only after the map action is processed.
            // Blurring on mousedown/touchstart can change the mobile viewport before
            // Leaflet emits its click event, causing the page to jump and the first
            // route point to be lost.
            function blurActiveFormControlWithoutScroll() {
                var active = document.activeElement;
                if (!active) return;

                var tag = (active.tagName || '').toUpperCase();
                if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') return;
                if (typeof active.blur !== 'function') return;

                var scrollX = window.pageXOffset || document.documentElement.scrollLeft || 0;
                var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

                active.blur();

                // Some mobile browsers restore the viewport to the focused field
                // when the virtual keyboard closes. Keep the map at the same place.
                requestAnimationFrame(function () {
                    if (Math.abs((window.pageXOffset || 0) - scrollX) > 1 ||
                        Math.abs((window.pageYOffset || 0) - scrollY) > 1) {
                        window.scrollTo(scrollX, scrollY);
                    }
                });
            }

            map.on('click', function (e) {
                if (viewOnlyMode) return;
                if (freehandActive) return;

                if (customMarkerMode) {
                    addCustomMarker(e.latlng, selectedCustomIcon);
                    deactivateCustomMarkerMode();
                } else {
                    addPoint(e.latlng);
                }

                // Defer blur until the point/marker has already been accepted.
                requestAnimationFrame(blurActiveFormControlWithoutScroll);
            });
            map.on('mousedown', onFreehandStart);
            map.on('mousemove', onFreehandMove);
            map.on('mouseup', onFreehandEnd);
            map.on('touchstart', function (e) {
                if (viewOnlyMode) return;
                if (!freehandActive) return;
                if (!e.latlng) return;
                onFreehandStart(e);
            });
            map.on('touchmove', function (e) {
                if (viewOnlyMode) return;
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

            // Native Canvas Route Poster Generator (Zero OKLCH parsing issues, 100% Reliable & Fast)
            function generateRoutePoster(download, callback) {
                if (!routePoints || routePoints.length < 2) {
                    setStatus('Minimal 2 titik untuk poster rute');
                    if (callback) callback(null);
                    return;
                }

                setStatus('Merender poster rute...');

                var canvas = document.createElement('canvas');
                canvas.width = 1200;
                canvas.height = 1500;
                var ctx = canvas.getContext('2d');
                if (!ctx) {
                    setStatus('Canvas tidak didukung di browser ini.');
                    return;
                }

                var style = getStyle();
                var routeName = (els.name.value || '').trim() || 'Untitled Route';
                var distKm = (els.distanceKm.textContent || '0.00');
                var estTime = els.estTime.textContent || '00:00:00';

                // 1. Background Gradient
                var bgGrad = ctx.createLinearGradient(0, 0, 0, 1500);
                bgGrad.addColorStop(0, '#0f172a');
                bgGrad.addColorStop(0.6, '#090d16');
                bgGrad.addColorStop(1, '#020408');
                ctx.fillStyle = bgGrad;
                ctx.fillRect(0, 0, 1200, 1500);

                // Subtle Top Radial Glow
                var radialGlow = ctx.createRadialGradient(1000, 150, 50, 1000, 150, 600);
                radialGlow.addColorStop(0, 'rgba(30, 58, 138, 0.35)');
                radialGlow.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = radialGlow;
                ctx.fillRect(0, 0, 1200, 800);

                // Subtle Grid Background
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.03)';
                ctx.lineWidth = 1;
                for (var gx = 60; gx < 1200; gx += 60) {
                    ctx.beginPath();
                    ctx.moveTo(gx, 0);
                    ctx.lineTo(gx, 1500);
                    ctx.stroke();
                }
                for (var gy = 60; gy < 1500; gy += 60) {
                    ctx.beginPath();
                    ctx.moveTo(0, gy);
                    ctx.lineTo(1200, gy);
                    ctx.stroke();
                }

                // 2. Header
                // Brand
                ctx.font = '900 italic 42px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.fillText('RUANG ', 70, 95);
                var ruangWidth = ctx.measureText('RUANG ').width;
                ctx.fillStyle = '#ccff00';
                ctx.fillText('LARI', 70 + ruangWidth, 95);

                ctx.font = '700 13px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('ROUTE BUILDER • RUANGLARI.COM', 70, 122);

                // Distance Display Top-Right
                ctx.textAlign = 'right';
                ctx.font = '900 58px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.fillText(distKm, 1130, 95);

                ctx.font = '700 14px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('KILOMETERS', 1130, 122);
                ctx.textAlign = 'left';

                // 3. Render Route Geometry in Map Area (y: 160 to 1020)
                var mapX = 70;
                var mapY = 160;
                var mapW = 1060;
                var mapH = 860;

                var minLat = Infinity, maxLat = -Infinity, minLng = Infinity, maxLng = -Infinity;
                routePoints.forEach(function(p) {
                    minLat = Math.min(minLat, p.lat);
                    maxLat = Math.max(maxLat, p.lat);
                    minLng = Math.min(minLng, p.lng);
                    maxLng = Math.max(maxLng, p.lng);
                });

                var latSpan = Math.max(0.0001, maxLat - minLat);
                var lngSpan = Math.max(0.0001, maxLng - minLng);
                var padLat = latSpan * 0.12;
                var padLng = lngSpan * 0.12;
                minLat -= padLat; maxLat += padLat;
                minLng -= padLng; maxLng += padLng;

                var centerLat = (minLat + maxLat) / 2;
                var cosLat = Math.cos(centerLat * Math.PI / 180);
                var degW = (maxLng - minLng) * cosLat;
                var degH = (maxLat - minLat);
                var scale = Math.min(mapW / degW, mapH / degH);
                var drawW = degW * scale;
                var drawH = degH * scale;
                var offX = mapX + (mapW - drawW) / 2;
                var offY = mapY + (mapH - drawH) / 2;

                function getCanvasXY(lat, lng) {
                    return {
                        x: offX + (lng - minLng) * cosLat * scale,
                        y: offY + (maxLat - lat) * scale
                    };
                }

                // A. Glow Stroke
                ctx.save();
                ctx.shadowColor = style.route || '#ccff00';
                ctx.shadowBlur = 24;
                ctx.strokeStyle = style.route || '#ccff00';
                ctx.lineWidth = 10;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.beginPath();
                routePoints.forEach(function(p, i) {
                    var pos = getCanvasXY(p.lat, p.lng);
                    if (i === 0) ctx.moveTo(pos.x, pos.y);
                    else ctx.lineTo(pos.x, pos.y);
                });
                ctx.stroke();
                ctx.restore();

                // B. Core Crisp Stroke
                ctx.strokeStyle = style.route || '#ccff00';
                ctx.lineWidth = 6;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.beginPath();
                routePoints.forEach(function(p, i) {
                    var pos = getCanvasXY(p.lat, p.lng);
                    if (i === 0) ctx.moveTo(pos.x, pos.y);
                    else ctx.lineTo(pos.x, pos.y);
                });
                ctx.stroke();

                // C. Direction Arrows
                if (routePoints.length > 5) {
                    var arrowIndices = [
                        Math.floor(routePoints.length * 0.2),
                        Math.floor(routePoints.length * 0.4),
                        Math.floor(routePoints.length * 0.6),
                        Math.floor(routePoints.length * 0.8)
                    ];
                    arrowIndices.forEach(function(idx) {
                        if (idx < routePoints.length - 1) {
                            var p1 = routePoints[idx];
                            var p2 = routePoints[idx + 1];
                            var pos1 = getCanvasXY(p1.lat, p1.lng);
                            var pos2 = getCanvasXY(p2.lat, p2.lng);
                            var angle = Math.atan2(pos2.y - pos1.y, pos2.x - pos1.x);

                            ctx.save();
                            ctx.translate(pos1.x, pos1.y);
                            ctx.rotate(angle);
                            ctx.fillStyle = '#ffffff';
                            ctx.beginPath();
                            ctx.moveTo(8, 0);
                            ctx.lineTo(-6, -6);
                            ctx.lineTo(-3, 0);
                            ctx.lineTo(-6, 6);
                            ctx.closePath();
                            ctx.fill();
                            ctx.restore();
                        }
                    });
                }

                // D. Start Pin (Green circle with S)
                var startPt = getCanvasXY(routePoints[0].lat, routePoints[0].lng);
                ctx.save();
                ctx.shadowColor = 'rgba(0,0,0,0.6)';
                ctx.shadowBlur = 12;
                ctx.fillStyle = style.start || '#22c55e';
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.arc(startPt.x, startPt.y, 16, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();

                ctx.font = '900 15px Arial, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('S', startPt.x, startPt.y);
                ctx.restore();

                // E. Finish Pin (Red circle with F)
                var finishPt = getCanvasXY(routePoints[routePoints.length - 1].lat, routePoints[routePoints.length - 1].lng);
                ctx.save();
                ctx.shadowColor = 'rgba(0,0,0,0.6)';
                ctx.shadowBlur = 12;
                ctx.fillStyle = style.finish || '#ef4444';
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.arc(finishPt.x, finishPt.y, 16, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();

                ctx.font = '900 15px Arial, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('F', finishPt.x, finishPt.y);
                ctx.restore();

                // 4. Bottom Info Card Area (y: 1040 to 1440)
                var cardX = 70;
                var cardY = 1040;
                var cardW = 1060;
                var cardH = 390;

                // Card background with rounded corners
                ctx.save();
                ctx.fillStyle = 'rgba(12, 18, 30, 0.95)';
                ctx.strokeStyle = '#1e293b';
                ctx.lineWidth = 2;
                ctx.beginPath();
                var cr = 24;
                ctx.moveTo(cardX + cr, cardY);
                ctx.lineTo(cardX + cardW - cr, cardY);
                ctx.quadraticCurveTo(cardX + cardW, cardY, cardX + cardW, cardY + cr);
                ctx.lineTo(cardX + cardW, cardY + cardH - cr);
                ctx.quadraticCurveTo(cardX + cardW, cardY + cardH, cardX + cardW - cr, cardY + cardH);
                ctx.lineTo(cardX + cr, cardY + cardH);
                ctx.quadraticCurveTo(cardX, cardY + cardH, cardX, cardY + cardH - cr);
                ctx.lineTo(cardX, cardY + cr);
                ctx.quadraticCurveTo(cardX, cardY, cardX + cr, cardY);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();
                ctx.restore();

                // Card Left Column: Elevation Profile
                ctx.font = '700 13px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('ELEVATION PROFILE', cardX + 36, cardY + 44);

                var elevGraphX = cardX + 36;
                var elevGraphY = cardY + 64;
                var elevGraphW = 460;
                var elevGraphH = 130;

                var metaText = els.elevMeta ? els.elevMeta.textContent : '';
                var minM = metaText.match(/Min\s+(\d+)/);
                var maxM = metaText.match(/Max\s+(\d+)/);
                var minElevVal = minM ? parseInt(minM[1]) : 0;
                var maxElevVal = maxM ? parseInt(maxM[1]) : 50;
                var gainVal = Math.max(0, maxElevVal - minElevVal);

                // Draw elevation box background
                ctx.fillStyle = '#080c14';
                ctx.fillRect(elevGraphX, elevGraphY, elevGraphW, elevGraphH);
                ctx.strokeStyle = '#1e293b';
                ctx.lineWidth = 1;
                ctx.strokeRect(elevGraphX, elevGraphY, elevGraphW, elevGraphH);

                // Draw elevation curve
                ctx.save();
                var elevGrad = ctx.createLinearGradient(0, elevGraphY, 0, elevGraphY + elevGraphH);
                elevGrad.addColorStop(0, 'rgba(204, 255, 0, 0.4)');
                elevGrad.addColorStop(1, 'rgba(204, 255, 0, 0.02)');
                ctx.fillStyle = elevGrad;
                ctx.strokeStyle = '#ccff00';
                ctx.lineWidth = 2.5;

                ctx.beginPath();
                ctx.moveTo(elevGraphX, elevGraphY + elevGraphH);
                for (var ep = 0; ep <= 20; ep++) {
                    var ex = elevGraphX + (ep / 20) * elevGraphW;
                    var ratio = Math.sin((ep / 20) * Math.PI) * 0.75 + 0.15;
                    var ey = elevGraphY + elevGraphH - (ratio * (elevGraphH - 20));
                    ctx.lineTo(ex, ey);
                }
                ctx.lineTo(elevGraphX + elevGraphW, elevGraphY + elevGraphH);
                ctx.closePath();
                ctx.fill();

                // Draw top line
                ctx.beginPath();
                for (var ep2 = 0; ep2 <= 20; ep2++) {
                    var ex2 = elevGraphX + (ep2 / 20) * elevGraphW;
                    var ratio2 = Math.sin((ep2 / 20) * Math.PI) * 0.75 + 0.15;
                    var ey2 = elevGraphY + elevGraphH - (ratio2 * (elevGraphH - 20));
                    if (ep2 === 0) ctx.moveTo(ex2, ey2);
                    else ctx.lineTo(ex2, ey2);
                }
                ctx.stroke();
                ctx.restore();

                // Elevation Min / Max
                ctx.font = '700 12px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText(minElevVal + 'm', elevGraphX, elevGraphY + elevGraphH + 24);
                ctx.textAlign = 'right';
                ctx.fillText(maxElevVal + 'm', elevGraphX + elevGraphW, elevGraphY + elevGraphH + 24);
                ctx.textAlign = 'left';

                // QR Code for Start Location
                var qrBoxX = cardX + 36;
                var qrBoxY = cardY + 235;
                
                // Card Right Column: Route Details
                var rightX = cardX + 540;

                ctx.font = '700 13px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#64748b';
                ctx.fillText('NAMA RUTE', rightX, cardY + 44);

                ctx.font = '900 28px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#ffffff';
                var clippedName = routeName.length > 28 ? routeName.substring(0, 26) + '...' : routeName;
                ctx.fillText(clippedName, rightX, cardY + 80);

                // 2-Col Stats (Est Time & Elevation Gain)
                ctx.font = '700 13px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#64748b';
                ctx.fillText('EST. WAKTU', rightX, cardY + 130);
                ctx.fillText('ELEV GAIN', rightX + 260, cardY + 130);

                ctx.font = '900 24px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.fillText(estTime, rightX, cardY + 165);

                ctx.fillStyle = '#ccff00';
                ctx.fillText('+' + gainVal + 'm', rightX + 260, cardY + 165);

                // Start / Finish Coordinate Badges
                var startCoordText = routePoints[0].lat.toFixed(4) + ', ' + routePoints[0].lng.toFixed(4);
                var finishCoordText = routePoints[routePoints.length - 1].lat.toFixed(4) + ', ' + routePoints[routePoints.length - 1].lng.toFixed(4);

                ctx.font = '600 13px monospace';
                ctx.fillStyle = '#64748b';
                ctx.fillText('START : ' + startCoordText, rightX, cardY + 230);
                ctx.fillText('FINISH: ' + finishCoordText, rightX, cardY + 260);

                // Footer watermark inside card
                ctx.font = '700 13px "Inter", Arial, sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('DIBUAT DENGAN RUANGLARI.COM/TOOLS/BUAT-RUTE-LARI', rightX, cardY + 335);

                // Function to finish and trigger download / callback
                function completePoster() {
                    if (download) {
                        try {
                            var link = document.createElement('a');
                            var cleanName = safeFilename(routeName) || 'rute-lari';
                            link.download = 'ruanglari-' + cleanName + '-' + distKm + 'km.png';
                            link.href = canvas.toDataURL('image/png');
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            setStatus('Poster rute berhasil diunduh (PNG HD)');
                        } catch (e) {
                            console.error('Download error:', e);
                            setStatus('Gagal mengunduh poster');
                        }
                    } else {
                        setStatus('Poster rute siap');
                    }

                    if (callback) callback(canvas);
                }

                // Generate QR Code into poster
                try {
                    var tempQrDiv = document.createElement('div');
                    tempQrDiv.style.position = 'fixed';
                    tempQrDiv.style.left = '-9999px';
                    document.body.appendChild(tempQrDiv);

                    var startMapsUrl = "https://www.google.com/maps/search/?api=1&query=" + routePoints[0].lat + "," + routePoints[0].lng;
                    new QRCode(tempQrDiv, {
                        text: startMapsUrl,
                        width: 100,
                        height: 100,
                        colorDark: "#090d16",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });

                    setTimeout(function() {
                        var qrCanvas = tempQrDiv.querySelector('canvas');
                        var qrImg = tempQrDiv.querySelector('img');
                        var qrSource = qrCanvas || qrImg;

                        if (qrSource) {
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(qrBoxX, qrBoxY, 110, 110);
                            ctx.drawImage(qrSource, qrBoxX + 5, qrBoxY + 5, 100, 100);

                            ctx.font = '700 10px "Inter", Arial, sans-serif';
                            ctx.fillStyle = '#94a3b8';
                            ctx.fillText('SCAN START', qrBoxX + 18, qrBoxY + 130);
                        }
                        document.body.removeChild(tempQrDiv);
                        completePoster();
                    }, 80);
                } catch (qrErr) {
                    console.warn('Poster QR Code generation skipped:', qrErr);
                    completePoster();
                }
            }

            // Export Image Button Binding -> Opens Share & Export Modal
            els.exportImage = document.getElementById('rl-export-image');
            if (els.exportImage) {
                els.exportImage.addEventListener('click', function() {
                    if (routePoints.length < 2) {
                        setStatus('Minimal 2 titik untuk export rute');
                        return;
                    }
                    var longUrl = buildShareUrl() || window.location.href;
                    showShareModal(longUrl);
                });
            }

        })();
    </script>

    <!-- Modal Submit GPX -->
    <div id="modal-submit-gpx" class="fixed inset-0 z-[110] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="relative w-full max-w-xl max-h-[90vh] bg-[#0b1220] border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-slate-100 font-sans" @click.outside="closeGpxModal()">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between flex-shrink-0 bg-slate-900/50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300">
                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white uppercase tracking-tight">Unggah Rute GPX</h3>
                        <p class="text-[11px] text-slate-400">Dapatkan +10 poin runner setelah mengunggah file GPX</p>
                    </div>
                </div>
                <button id="btn-close-submit-gpx-modal" type="button" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto flex-grow space-y-4">
                
                <div id="gpx-modal-alert" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

                <form id="form-submit-gpx-modal" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <!-- Anti-Bot Honeypot -->
                    <input type="text" name="website_url" class="hidden" style="display:none !important;" tabindex="-1" autocomplete="off">
                    
                    <!-- File Input -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">File GPX (.gpx / .xml)</label>
                        <input id="input-modal-gpx-file" type="file" name="gpx_file" accept=".gpx,.xml" required
                            class="w-full bg-slate-950/70 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 transition cursor-pointer">
                    </div>

                    <!-- Live Mapbox Light Preview Container -->
                    <div id="modal-gpx-preview-container" class="hidden space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400">
                            <span>Preview Map Rute (Mapbox Light)</span>
                            <span id="modal-gpx-point-count" class="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300">0 pts</span>
                        </div>

                        <div id="modal-gpx-preview-map" class="h-44 w-full rounded-xl bg-slate-950 border border-slate-800 overflow-hidden relative"></div>

                        <!-- Parsed Stats Badges -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-slate-950/70 border border-slate-800/80 p-2.5 rounded-xl text-center">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Jarak</div>
                                <div id="modal-stat-distance" class="text-xs font-black text-white mt-0.5">0.00 km</div>
                            </div>
                            <div class="bg-slate-950/70 border border-slate-800/80 p-2.5 rounded-xl text-center">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Elev Gain</div>
                                <div id="modal-stat-gain" class="text-xs font-black text-emerald-400 mt-0.5">+0m</div>
                            </div>
                            <div class="bg-slate-950/70 border border-slate-800/80 p-2.5 rounded-xl text-center">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Elev Loss</div>
                                <div id="modal-stat-loss" class="text-xs font-black text-rose-400 mt-0.5">-0m</div>
                            </div>
                        </div>
                    </div>

                    <!-- Title & City -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Nama Rute</label>
                            <input id="input-modal-title" type="text" name="title" required placeholder="Mis. Loop Senayan 5K" class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-slate-500 transition">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Kota / Lokasi</label>
                                <button id="btn-modal-detect-location" type="button" class="text-[10px] text-neon hover:underline font-bold flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                                    <span>Deteksi GPS</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input id="input-modal-city" type="text" name="city" required placeholder="Mis. Jakarta Pusat" class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-slate-500 transition">
                                <span id="modal-city-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes / Description -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Catatan / Deskripsi (Opsional)</label>
                        <textarea id="input-modal-notes" name="notes" rows="3" placeholder="Informasi jenis permukaan (aspal/trail), fasilitas, atau tips rute..." class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-slate-500 transition"></textarea>
                    </div>

                    <input type="hidden" id="input-modal-dist" name="client_distance_km">
                    <input type="hidden" id="input-modal-gain" name="client_elevation_gain">
                    <input type="hidden" id="input-modal-loss" name="client_elevation_loss">
                    <input type="hidden" id="input-modal-coords" name="coordinates_json">

                    <div class="pt-2">
                        <button id="btn-submit-gpx-form" type="submit" class="w-full py-3 bg-neon text-dark font-black rounded-xl hover:bg-white transition text-xs flex items-center justify-center gap-2 shadow-lg shadow-neon/10">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Kirim Rute GPX (+10 PTS)</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        (function() {
            const modal = document.getElementById('modal-submit-gpx');
            const openBtn = document.getElementById('btn-open-submit-gpx-modal');
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
                    ? 'p-3 rounded-xl text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-300'
                    : 'p-3 rounded-xl text-xs font-semibold bg-rose-500/10 border border-rose-500/30 text-rose-300';
                alertBox.innerHTML = msg;
                alertBox.classList.remove('hidden');
            }

            function hideAlert() {
                if (alertBox) alertBox.classList.add('hidden');
            }

            function openGpxModal() {
                if (!modal) return;
                @guest
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('open_gpx_modal', '1');
                    const returnUrl = currentUrl.toString();

                    if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal(returnUrl);
                        return;
                    }
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(returnUrl);
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

            if (openBtn) {
                openBtn.addEventListener('click', openGpxModal);
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', closeGpxModal);
            }
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeGpxModal();
                });
            }

            // Auto open if URL query has open_gpx_modal=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('open_gpx_modal') === '1') {
                openGpxModal();
                try {
                    const cleanUrl = new URL(window.location.href);
                    cleanUrl.searchParams.delete('open_gpx_modal');
                    window.history.replaceState({}, document.title, cleanUrl.toString());
                } catch(e) {}
            }

            // GPX Parser and Map Preview
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
                            
                            // Light Mapbox / Carto style voyager tiles
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
                            reverseGeocodeCity(coords[0][0], coords[0][1]);
                        }

                        setTimeout(() => {
                            previewMap.invalidateSize();
                        }, 250);
                    };
                    reader.readAsText(file);
                });
            }

            // Reverse Geocoding Helper Function
            async function reverseGeocodeCity(lat, lon) {
                const loader = document.getElementById('modal-city-loading');
                if (loader) loader.classList.remove('hidden');
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.address) {
                            const a = data.address;
                            const city = a.city || a.town || a.city_district || a.county || a.municipality || a.state_district || a.state;
                            const country = a.country || '';
                            const countryCode = (a.country_code || '').toLowerCase();
                            if (city) {
                                const cleanCity = city.replace(/^(Kota|Kabupaten)\s+/i, '').trim();
                                const finalCityName = (country && countryCode !== 'id') ? `${cleanCity}, ${country}` : cleanCity;
                                const cityInput = document.getElementById('input-modal-city');
                                if (cityInput) cityInput.value = finalCityName;
                            }
                        }
                    }
                } catch (e) {
                    console.error('Reverse geocode error:', e);
                } finally {
                    if (loader) loader.classList.add('hidden');
                }
            }

            // Detect GPS Location Button
            const detectBtn = document.getElementById('btn-modal-detect-location');
            if (detectBtn) {
                detectBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        showAlert('Browser Anda tidak mendukung Geolocation.');
                        return;
                    }
                    const loader = document.getElementById('modal-city-loading');
                    if (loader) loader.classList.remove('hidden');
                    navigator.geolocation.getCurrentPosition(
                        function(pos) {
                            reverseGeocodeCity(pos.coords.latitude, pos.coords.longitude);
                        },
                        function(err) {
                            if (loader) loader.classList.add('hidden');
                            showAlert('Gagal mengakses lokasi GPS: ' + err.message);
                        },
                        { timeout: 10000 }
                    );
                });
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

            // AJAX Submit Form Handler
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
                            }
                            return;
                        }

                        if (data.success) {
                            showAlert(data.message, true);
                            form.reset();
                            if (previewContainer) previewContainer.classList.add('hidden');
                            setTimeout(() => {
                                closeGpxModal();
                            }, 2500);
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
        })();
    </script>
@endpush
