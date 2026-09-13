@extends('layouts.pacerhub')

@section('title', 'Program Lari Terstruktur: 5K, 10K & Half Marathon | Ruang Lari')
@section('meta_title', 'Program Lari Terstruktur: 5K, 10K & Half Marathon | Ruang Lari')
@section('meta_description', 'Pilihan program lari terstruktur dari coach profesional Ruang Lari untuk 5K, 10K, Half Marathon hingga Marathon. Jadwal latihan lari pemula terukur bebas cedera.')
@section('meta_keywords', 'program lari, program lari pemula, program lari 5k, program lari 10k, program lari half marathon, jadwal latihan lari, coaching lari online, training plan lari')
@section('canonical_url', route('programs.index'))
@section('og_image', asset('images/hero/indonesian-runner-sunrise.jpg'))

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,600;0,700;0,800;0,900;1,700;1,800&family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* 4-Tier Ergonomic Dark Mode Palette (Comfortable, Warm Slate, Anti-Glare) */
            --rl-canvas: #0b111e;              /* Deep athletic midnight slate (not pitch black) */
            --rl-panel: #131b2c;               /* Tier 1: Elevated card/container surface */
            --rl-panel-raised: #182338;        /* Tier 2: Sub-cards, inner telemetry & filter boxes */
            --rl-input-bg: #151f33;            /* Tier 2b: Form inputs & selects */
            --rl-border: #1e293b;              /* Dark slate-800 border (soft, comfortable) */
            --rl-border-field: #1e293b;        /* Dark field border */
            --rl-border-hover: #334155;        /* Subtle hover boundary */
            --rl-accent: #FF5A1F;              /* Athletic performance orange */
            --rl-accent-hover: #e04a12;
            --rl-volt: #CCFF00;                /* Elite athletic marathon volt / neon green */
            --rl-volt-hover: #b8e600;
            --rl-warm-white: #FAF8F3;
            --rl-text-muted: #94a3b8;          /* Slate 400 */
        }

        /* Dark mode border hygiene: Never allow bright white outlines */
        [class*="border-white"],
        .border-white,
        [class*="border-white/"] {
            border-color: #1e293b !important;
        }
        [class*="divide-white"] {
            border-color: #1e293b !important;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Editorial Athletic Headings (Inter Tight / Sora / Sans-Serif - No Monospace) */
        .font-editorial-heading {
            font-family: 'Inter Tight', 'Sora', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            letter-spacing: -0.025em;
        }

        .font-sub-heading {
            font-family: 'Sora', 'Inter Tight', -apple-system, sans-serif;
            letter-spacing: -0.015em;
        }

        .font-body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .font-numeric {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        /* Subtle athletic surface background */
        .bg-athletic-surface {
            background-color: var(--rl-canvas);
            background-image: radial-gradient(circle at 50% 0%, rgba(255, 90, 31, 0.05) 0%, transparent 60%);
        }

        /* Clean sports cards */
        .athletic-card {
            background: var(--rl-panel);
            border: 1px solid var(--rl-border);
            border-radius: 8px;
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .athletic-card:hover {
            border-color: var(--rl-border-hover);
        }

        /* Ergonomic Form Fields (Zero harsh white wireframes) */
        .athletic-field {
            background-color: var(--rl-input-bg);
            border: 1px solid var(--rl-border-field);
            color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .athletic-field:focus {
            outline: none;
            border-color: var(--rl-accent);
            box-shadow: 0 0 0 1px rgba(255, 90, 31, 0.35);
        }

        .athletic-field option {
            background-color: #131b2c;
            color: #ffffff;
        }

        /* Journey Map Active Highlights */
        .journey-step-btn {
            background: var(--rl-panel);
            border: 1px solid var(--rl-border);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .journey-step-btn:hover {
            border-color: var(--rl-border-hover);
            background: var(--rl-panel-raised);
        }

        .journey-step-btn.is-active {
            border-color: var(--rl-accent);
            background: var(--rl-panel-raised);
        }

        /* Direct CSS Fallbacks for Volt Neon #CCFF00 & Dark #080A0D (Bypasses unbuilt Vite bundle) */
        [class*="text-[#CCFF00]"],
        .text-volt {
            color: #CCFF00 !important;
        }

        [class*="bg-[#CCFF00]"],
        .bg-volt {
            background-color: #CCFF00 !important;
        }

        [class*="border-[#CCFF00]"],
        .border-volt {
            border-color: #CCFF00 !important;
        }

        [class*="text-[#080A0D]"] {
            color: #080A0D !important;
        }

        /* Hero & CTA Athletic Neon Volt Action Buttons */
        .btn-volt-hero {
            background-color: #CCFF00 !important;
            color: #080A0D !important;
            border: 1px solid #CCFF00 !important;
            box-shadow: 0 10px 25px -5px rgba(204, 255, 0, 0.25) !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
        }

        .btn-volt-hero:hover {
            background-color: #080A0D !important;
            color: #ffffff !important;
            border-color: #CCFF00 !important;
        }

        .btn-volt-hero svg {
            color: #080A0D !important;
            stroke: currentColor !important;
            transition: color 0.2s ease !important;
        }

        .btn-volt-hero:hover svg {
            color: #ffffff !important;
            stroke: currentColor !important;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen pt-0 pb-20 font-body bg-athletic-surface text-slate-200">

    <!-- ====================================================================
         SECTION 1: HERO SECTION (WITH SVG RUNNING ROUTE TRACK BACKGROUND)
         ==================================================================== -->
    <section class="border-b border-slate-800 bg-[#0b111e] relative overflow-hidden">
        
        <!-- SVG Running Route Track Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-0 select-none opacity-20 sm:opacity-30" aria-hidden="true">
            <svg class="w-full h-full object-cover min-w-[1100px]" viewBox="0 0 1440 720" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="heroRouteTrackGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FF5A1F" stop-opacity="0.85"/>
                        <stop offset="50%" stop-color="#FF5A1F" stop-opacity="0.45"/>
                        <stop offset="100%" stop-color="#FAF8F3" stop-opacity="0.25"/>
                    </linearGradient>
                    <pattern id="athleticGridLines" width="48" height="48" patternUnits="userSpaceOnUse">
                        <path d="M 48 0 L 0 0 0 48" fill="none" stroke="rgba(255,255,255,0.025)" stroke-width="1"/>
                    </pattern>
                </defs>

                <!-- Athletic Grid Canvas -->
                <rect width="1440" height="720" fill="url(#athleticGridLines)" />

                <!-- Topographic Elevation Contours -->
                <path d="M-80 260 C 220 140, 480 370, 820 220 C 1160 80, 1340 310, 1560 190" stroke="rgba(255, 255, 255, 0.04)" stroke-width="1.5" stroke-dasharray="6 6"/>
                <path d="M-80 340 C 240 220, 500 450, 840 300 C 1180 160, 1360 390, 1560 270" stroke="rgba(255, 255, 255, 0.035)" stroke-width="1.5"/>
                <path d="M-80 420 C 260 300, 520 530, 860 380 C 1200 240, 1380 470, 1560 350" stroke="rgba(255, 255, 255, 0.03)" stroke-width="1.5"/>

                <!-- Secondary Track Loops (Athletic Stadium Oval) -->
                <ellipse cx="260" cy="460" rx="140" ry="70" stroke="rgba(255, 255, 255, 0.05)" stroke-width="1.5" stroke-dasharray="4 4" transform="rotate(-12 260 460)"/>
                <ellipse cx="260" cy="460" rx="120" ry="55" stroke="rgba(255, 90, 31, 0.15)" stroke-width="1.5" transform="rotate(-12 260 460)"/>

                <!-- Primary GPS Running Route Track -->
                <path d="M 60 520 C 160 450, 240 540, 380 480 C 500 420, 560 260, 700 280 C 840 300, 890 460, 1020 400 C 1150 340, 1200 180, 1340 220 C 1410 240, 1460 210, 1500 190" 
                      stroke="url(#heroRouteTrackGrad)" stroke-width="4" stroke-linecap="round"/>

                <!-- Running Route Dashed Pacing Line -->
                <path d="M 60 520 C 160 450, 240 540, 380 480 C 500 420, 560 260, 700 280 C 840 300, 890 460, 1020 400 C 1150 340, 1200 180, 1340 220 C 1410 240, 1460 210, 1500 190" 
                      stroke="#FF5A1F" stroke-width="1.5" stroke-dasharray="8 12" stroke-linecap="round"/>

                <!-- Waypoint 0K START -->
                <circle cx="60" cy="520" r="14" stroke="#22C55E" stroke-width="2" stroke-opacity="0.4" fill="none"/>
                <circle cx="60" cy="520" r="6" fill="#22C55E"/>
                <rect x="25" y="542" width="70" height="20" rx="4" fill="#0b111e" stroke="#1e293b"/>
                <text x="60" y="556" fill="#22C55E" font-size="10" font-weight="700" text-anchor="middle" font-family="'Inter Tight', sans-serif" letter-spacing="1">START 0K</text>

                <!-- Waypoint 5K -->
                <circle cx="380" cy="480" r="4" fill="#FAF8F3"/>
                <rect x="350" y="502" width="60" height="18" rx="4" fill="#0b111e" stroke="#1e293b"/>
                <text x="380" y="515" fill="#FAF8F3" font-size="9" font-weight="600" text-anchor="middle" font-family="'Inter', sans-serif">5.0 KM</text>

                <!-- Waypoint 10K -->
                <circle cx="700" cy="280" r="4" fill="#FAF8F3"/>
                <rect x="670" y="250" width="60" height="18" rx="4" fill="#0b111e" stroke="#1e293b"/>
                <text x="700" y="263" fill="#FAF8F3" font-size="9" font-weight="600" text-anchor="middle" font-family="'Inter', sans-serif">10.0 KM</text>

                <!-- Waypoint 15K -->
                <circle cx="1020" cy="400" r="4" fill="#FAF8F3"/>
                <rect x="990" y="420" width="60" height="18" rx="4" fill="#0b111e" stroke="#1e293b"/>
                <text x="1020" y="433" fill="#FAF8F3" font-size="9" font-weight="600" text-anchor="middle" font-family="'Inter', sans-serif">15.0 KM</text>

                <!-- Waypoint 21.1K FINISH -->
                <circle cx="1340" cy="220" r="16" stroke="#FF5A1F" stroke-width="2" stroke-opacity="0.5" fill="none"/>
                <circle cx="1340" cy="220" r="7" fill="#FF5A1F"/>
                <rect x="1295" y="244" width="90" height="20" rx="4" fill="#0b111e" stroke="#FF5A1F" stroke-opacity="0.5"/>
                <text x="1340" y="258" fill="#FF5A1F" font-size="10" font-weight="800" text-anchor="middle" font-family="'Inter Tight', sans-serif" letter-spacing="1">FINISH 21.1K</text>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-24 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                
                <!-- Left Side: Editorial Athletic Statement & CTAs -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-[#182338] text-xs font-semibold text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-[#CCFF00] animate-pulse"></span>
                        <span class="tracking-wide uppercase">Program Lari & Coaching Terverifikasi</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-editorial-heading text-white leading-[1.04] tracking-tight">
                        Program Lari Terstruktur.<br>
                        <span class="text-[#CCFF00]">Capai Finish & Waktu Terbaikmu.</span>
                    </h1>

                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl font-body">
                        Temukan <strong>program lari</strong> yang dirancang oleh pelatih berlisensi untuk menyelesaikan 5K, 10K, Half Marathon hingga Marathon. Didukung jadwal latihan lari terukur, panduan pace VDOT akurat, dan metode perlindungan cedera.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <a href="#katalog-program-coach" 
                           class="btn-volt-hero group px-6 py-3.5 rounded-md font-black text-xs sm:text-sm tracking-wider uppercase flex items-center gap-2 cursor-pointer">
                            <span>Pilih Program Coach</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                        <a href="#running-levels" 
                           class="btn-volt-hero group px-6 py-3.5 rounded-md font-black text-xs sm:text-sm tracking-wide uppercase flex items-center gap-2 cursor-pointer">
                            <span>Panduan Program Lari</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </a>
                    </div>

                    <!-- Trust Pillars -->
                    <div class="grid grid-cols-3 gap-3 pt-6 border-t border-slate-800 font-numeric">
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Program Terverifikasi</div>
                            <div class="text-base sm:text-lg font-bold text-white mt-0.5">{{ $totalPrograms ?? 12 }}+ Program</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Pelatih Berlisensi</div>
                            <div class="text-base sm:text-lg font-bold text-white mt-0.5">{{ $totalCoaches ?? 6 }}+ Coach Aktif</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Kepuasan Pelari</div>
                            <div class="text-base sm:text-lg font-bold text-[#FF5A1F] mt-0.5">★ {{ $averageRating ?? 4.9 }} / 5.0</div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Authentic Runner Photo with Performance Metrics -->
                <div class="lg:col-span-5">
                    <div class="relative athletic-card overflow-hidden group shadow-2xl">
                        <!-- Documentary Athlete Image -->
                        <div class="aspect-[4/3] sm:aspect-[1/1] lg:aspect-[4/5] relative bg-[#0b111e] overflow-hidden">
                            <img src="{{ asset('images/hero/indonesian-runner-sunrise.jpg') }}" 
                                 alt="Indonesian endurance runner training at sunrise" 
                                 class="w-full h-full object-cover object-center transform group-hover:scale-[1.02] transition duration-700"
                                 loading="eager">
                            
                            <!-- Subtle Gradient Vignette for Legibility -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0b111e] via-transparent to-transparent opacity-90"></div>

                            <!-- Photo Meta Credit / Authentic Tag -->
                            <div class="absolute top-3 left-3 px-2.5 py-1 rounded bg-[#0b111e]/90 text-[11px] font-semibold text-slate-300">
                                Sesi Pagi • 05:30 WIB
                            </div>
                        </div>

                        <!-- Performance Metrics Strip -->
                        <div class="p-4 bg-[#182338] border-t border-slate-800 space-y-2.5 font-numeric">
                            <div class="text-xs font-bold text-slate-400 tracking-wide uppercase">
                                Jalur Peningkatan Bertahap
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-[#131b2c] p-2 rounded-md border border-slate-800">
                                    <div class="text-xs font-bold text-white font-editorial-heading">5K Beginner</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-normal">Fondasi & Habit</div>
                                </div>
                                <div class="bg-[#131b2c] p-2 rounded-md border border-slate-800">
                                    <div class="text-xs font-bold text-white font-editorial-heading">10K Improvement</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-normal">Pace & Threshold</div>
                                </div>
                                <div class="bg-[#131b2c] p-2 rounded-md border border-slate-800">
                                    <div class="text-xs font-bold text-[#FF5A1F] font-editorial-heading">Half Marathon</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-normal">Finish Strong</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ====================================================================
         SECTION 2: KATALOG PROGRAM COACH (THE PRIMARY FLAGSHIP SHOWCASE)
         ==================================================================== -->
    <section id="katalog-program-coach" class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800 relative overflow-hidden">
        
        <!-- Running Course Splits & Topo Grid Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-20 -z-0" aria-hidden="true">
            <svg class="w-full h-full object-cover min-w-[1000px]" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="catalogGridPattern" width="120" height="60" patternUnits="userSpaceOnUse">
                        <path d="M 120 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.75"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#catalogGridPattern)" />
                <!-- Subtle Marathon Elevation Topo Line -->
                <path d="M -50 220 C 200 150, 450 280, 750 180 C 950 110, 1150 240, 1300 190" stroke="rgba(204, 255, 0, 0.12)" stroke-width="1.5" stroke-dasharray="8 8"/>
                <path d="M -50 260 C 200 190, 450 320, 750 220 C 950 150, 1150 280, 1300 230" stroke="rgba(255, 255, 255, 0.04)" stroke-width="1"/>
            </svg>
        </div>

        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 relative z-10">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-[#182338] border border-slate-800 text-xs font-bold text-slate-300 uppercase tracking-wider mb-3 font-numeric">
                    <span class="w-2 h-2 rounded-full bg-[#CCFF00] animate-pulse"></span>
                    <span>Katalog Program Lari Resmi</span>
                    <span class="text-slate-600">•</span>
                    <span class="text-[#CCFF00]">Verified Plans</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                    Katalog Program Lari Terverifikasi dari Coach Berlisensi
                </h2>
                <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                    Pilihan <strong>program lari terstruktur</strong> untuk target 5K, 10K, Half Marathon, dan Marathon. Setiap program lari dilengkapi menu latihan harian, target pace spesifik, dan panduan pencegahan cedera.
                </p>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <span class="text-xs text-slate-400 font-numeric font-medium">
                    Total <strong class="text-white font-bold">{{ $programs->total() ?? $programs->count() }}</strong> Program Lari Tersedia
                </span>
            </div>
        </div>

        <!-- Filter & Sort Control Bar Form (Server-Side Driven + URL Friendly) -->
        <form method="GET" action="{{ route('programs.index') }}#katalog-program-coach" id="catalog-filter-form" class="mb-10 space-y-4">
            <!-- Hidden input to preserve category selection across form submits -->
            <input type="hidden" name="category" id="catalog-category-input" value="{{ request('category') }}">

            <div class="athletic-card p-4 sm:p-5 bg-[#131b2c] space-y-4">
                
                <!-- Row 1: Category Filter Tabs -->
                <div class="flex items-center justify-between gap-3 overflow-x-auto pb-1 scrollbar-none">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 font-medium whitespace-nowrap hidden sm:inline">Jarak:</span>
                        @php
                            $curCat = strtolower(request('category', ''));
                        @endphp
                        <button type="button" 
                                onclick="setCatalogCategory('')"
                                class="px-3.5 py-2 rounded-md text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $curCat === '' || $curCat === 'all' ? 'bg-[#FF5A1F] text-white shadow-sm font-bold' : 'bg-[#182338] text-slate-300 hover:text-white hover:bg-[#1f2d48] border border-slate-800 hover:border-slate-700' }}">
                            Semua Jarak
                        </button>
                        <button type="button" 
                                onclick="setCatalogCategory('5k')"
                                class="px-3.5 py-2 rounded-md text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $curCat === '5k' ? 'bg-[#FF5A1F] text-white shadow-sm font-bold' : 'bg-[#182338] text-slate-300 hover:text-white hover:bg-[#1f2d48] border border-slate-800 hover:border-slate-700' }}">
                            5K
                        </button>
                        <button type="button" 
                                onclick="setCatalogCategory('10k')"
                                class="px-3.5 py-2 rounded-md text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $curCat === '10k' ? 'bg-[#FF5A1F] text-white shadow-sm font-bold' : 'bg-[#182338] text-slate-300 hover:text-white hover:bg-[#1f2d48] border border-slate-800 hover:border-slate-700' }}">
                            10K
                        </button>
                        <button type="button" 
                                onclick="setCatalogCategory('21k')"
                                class="px-3.5 py-2 rounded-md text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $curCat === '21k' || $curCat === 'hm' ? 'bg-[#FF5A1F] text-white shadow-sm font-bold' : 'bg-[#182338] text-slate-300 hover:text-white hover:bg-[#1f2d48] border border-slate-800 hover:border-slate-700' }}">
                            Half Marathon (21K)
                        </button>
                        <button type="button" 
                                onclick="setCatalogCategory('42k')"
                                class="px-3.5 py-2 rounded-md text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $curCat === '42k' || $curCat === 'fm' ? 'bg-[#FF5A1F] text-white shadow-sm font-bold' : 'bg-[#182338] text-slate-300 hover:text-white hover:bg-[#1f2d48] border border-slate-800 hover:border-slate-700' }}">
                            Marathon (42K)
                        </button>
                    </div>
                    
                    <div class="hidden lg:block text-xs text-slate-400 font-numeric shrink-0">
                        Menampilkan <span class="text-white font-bold">{{ $programs->count() }}</span> dari <span class="text-white font-bold">{{ $programs->total() }}</span> Program
                    </div>
                </div>

                <!-- Row 2: Search, Level, Price & Sort Controls -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-3 border-t border-slate-800">
                    
                    <!-- Search Input -->
                    <div class="lg:col-span-4 relative">
                        <input type="text" 
                               name="search" 
                               id="catalog-search-input"
                               value="{{ request('search') }}"
                               placeholder="Cari nama program atau nama coach..." 
                               class="w-full pl-9 pr-8 py-2.5 rounded-md athletic-field text-xs placeholder-slate-400">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        @if(request('search'))
                            <button type="button" 
                                    onclick="clearCatalogSearch()" 
                                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-white transition text-xs" 
                                    title="Hapus pencarian">
                                ✕
                            </button>
                        @endif
                    </div>

                    <!-- Difficulty Level -->
                    <div class="lg:col-span-3">
                        <select name="difficulty" 
                                onchange="document.getElementById('catalog-filter-form').submit()"
                                class="w-full py-2.5 px-3 rounded-md athletic-field text-xs cursor-pointer">
                            <option value="" {{ !request('difficulty') ? 'selected' : '' }}>Semua Tingkat (Level)</option>
                            <option value="beginner" {{ request('difficulty') === 'beginner' ? 'selected' : '' }}>Pemula (Beginner)</option>
                            <option value="intermediate" {{ request('difficulty') === 'intermediate' ? 'selected' : '' }}>Menengah (Intermediate)</option>
                            <option value="advanced" {{ request('difficulty') === 'advanced' ? 'selected' : '' }}>Lanjutan (Advanced)</option>
                        </select>
                    </div>

                    <!-- Price Type (Gratis / Berbayar) -->
                    <div class="lg:col-span-2">
                        <select name="price_type" 
                                onchange="document.getElementById('catalog-filter-form').submit()"
                                class="w-full py-2.5 px-3 rounded-md athletic-field text-xs cursor-pointer">
                            <option value="" {{ !request('price_type') ? 'selected' : '' }}>Semua Biaya</option>
                            <option value="free" {{ request('price_type') === 'free' ? 'selected' : '' }}>Program Gratis</option>
                            <option value="paid" {{ request('price_type') === 'paid' ? 'selected' : '' }}>Program Berbayar</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div class="lg:col-span-3">
                        <select name="sort" 
                                onchange="document.getElementById('catalog-filter-form').submit()"
                                class="w-full py-2.5 px-3 rounded-md athletic-field text-xs cursor-pointer">
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Urutkan: Paling Populer</option>
                            <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Urutkan: Program Terbaru</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Urutkan: Rating Tertinggi</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Urutkan: Harga Terendah</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Urutkan: Harga Tertinggi</option>
                        </select>
                    </div>

                </div>

                <!-- Active Filter Badges Bar -->
                @if(request()->hasAny(['search', 'category', 'difficulty', 'price_type']) || (request('sort') && request('sort') !== 'newest'))
                    <div class="pt-3 border-t border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-400 mr-1">Filter Aktif:</span>
                            @if(request('search'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#182338] text-slate-200">
                                    Pencarian: "{{ request('search') }}"
                                    <a href="{{ route('programs.index', array_merge(request()->except(['search', 'page']))) }}#katalog-program-coach" class="hover:text-red-400">✕</a>
                                </span>
                            @endif
                            @if(request('category'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#FF5A1F]/20 text-[#FF5A1F] font-bold uppercase">
                                    Jarak: {{ request('category') }}
                                    <a href="{{ route('programs.index', array_merge(request()->except(['category', 'page']))) }}#katalog-program-coach" class="hover:text-white">✕</a>
                                </span>
                            @endif
                            @if(request('difficulty'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#182338] text-slate-200 capitalize">
                                    Tingkat: {{ request('difficulty') }}
                                    <a href="{{ route('programs.index', array_merge(request()->except(['difficulty', 'page']))) }}#katalog-program-coach" class="hover:text-red-400">✕</a>
                                </span>
                            @endif
                            @if(request('price_type'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#182338] text-slate-200 capitalize">
                                    Biaya: {{ request('price_type') === 'free' ? 'Gratis' : 'Berbayar' }}
                                    <a href="{{ route('programs.index', array_merge(request()->except(['price_type', 'page']))) }}#katalog-program-coach" class="hover:text-red-400">✕</a>
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('programs.index') }}#katalog-program-coach" class="text-[#FF5A1F] hover:text-white font-semibold transition">
                            Reset Semua Filter
                        </a>
                    </div>
                @endif

            </div>
        </form>

        <!-- Pure Blade Coach Program Cards Grid -->
        @if($programs->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($programs as $program)
                    @php
                        // Distance Resolver
                        $dist = strtolower($program->distance_target ?? '5k');
                        $defaultFallback = match($dist) {
                            '10k' => asset('images/hero/indonesian-runner-sunrise.jpg'),
                            '21k', 'hm' => asset('images/hero/marathon-hero-cinematic.jpg'),
                            '42k', 'fm' => asset('images/hero/jadwal-lari.webp'),
                            default => asset('images/hero/runner-hero.jpg'),
                        };
                        
                        // Featured image resolution with verified asset fallbacks
                        $featuredImg = $program->thumbnail_url ?: ($program->banner_url ?: $defaultFallback);

                        // Coach Avatar Resolver
                        $coachAvatar = ($program->coach && $program->coach->avatar)
                            ? (str_starts_with($program->coach->avatar, 'http') 
                                ? $program->coach->avatar 
                                : asset('storage/' . ltrim($program->coach->avatar, '/')))
                            : asset('images/profile/17.jpg');

                        // Excerpt text cleanup (strips HTML, figure, table tags)
                        $cleanDesc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['<br>', '</p>', '</li>', '</td>'], ' ', $program->description ?? '')))), 115);

                        // Frequency calculation from program JSON schedule
                        $weeklySessions = 4;
                        if (!empty($program->program_json['sessions']) && is_array($program->program_json['sessions'])) {
                            $activeSess = array_filter($program->program_json['sessions'], function($s) {
                                return isset($s['type']) && $s['type'] !== 'rest';
                            });
                            $durationWeeks = max(1, (int)($program->duration_weeks ?? 8));
                            $computed = (int) round(count($activeSess) / $durationWeeks);
                            if ($computed > 0) {
                                $weeklySessions = $computed;
                            }
                        }

                        // Difficulty badge styling
                        $diff = strtolower($program->difficulty ?? 'beginner');
                        $diffClass = match($diff) {
                            'advanced', 'lanjutan' => 'bg-rose-950/80 text-rose-300 border-rose-800/70',
                            'intermediate', 'menengah' => 'bg-amber-950/80 text-amber-300 border-amber-800/70',
                            default => 'bg-emerald-950/80 text-emerald-300 border-emerald-800/70',
                        };
                        $diffLabel = match($diff) {
                            'advanced', 'lanjutan' => 'Lanjutan',
                            'intermediate', 'menengah' => 'Menengah',
                            default => 'Pemula',
                        };

                        // Distance label
                        $distBadgeText = match($dist) {
                            '10k' => '10K',
                            '21k', 'hm' => '21.1K HM',
                            '42k', 'fm' => '42.2K MARATHON',
                            default => '5K',
                        };
                    @endphp

                    <article class="athletic-card group overflow-hidden flex flex-col justify-between hover:-translate-y-1 transition duration-200">
                        <div>
                            <!-- Featured Image with Category, Level & Duration Badges -->
                            <div class="aspect-[16/10] relative bg-[#0b111e] overflow-hidden border-b border-slate-800">
                                <img src="{{ $featuredImg }}" 
                                     alt="{{ $program->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                     onerror="this.src='{{ asset('images/hero/runner-hero.jpg') }}'"
                                     loading="lazy">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-[#131b2c] via-[#131b2c]/30 to-black/40"></div>

                                <!-- Distance Target Badge (Top Left) -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded bg-[#0b111e]/95 border border-[#FF5A1F] text-white text-[11px] font-bold tracking-wider uppercase font-numeric">
                                        {{ $distBadgeText }}
                                    </span>
                                </div>

                                <!-- Difficulty Badge (Top Right) -->
                                <div class="absolute top-3 right-3">
                                    <span class="px-2.5 py-1 rounded border text-[11px] font-semibold tracking-wide font-numeric {{ $diffClass }}">
                                        {{ $diffLabel }}
                                    </span>
                                </div>

                                <!-- Duration Pill (Bottom Left) -->
                                <div class="absolute bottom-3 left-3">
                                    <span class="px-2 py-0.5 rounded bg-[#0b111e]/90 text-slate-300 text-[11px] font-numeric font-medium">
                                        {{ $program->duration_weeks ?: 8 }} Minggu
                                    </span>
                                </div>

                                <!-- Free / Featured Pill (Bottom Right) -->
                                <div class="absolute bottom-3 right-3">
                                    @if($program->isFree())
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/90 text-white text-[11px] font-bold uppercase tracking-wider font-numeric">
                                            Gratis
                                        </span>
                                    @elseif($program->is_featured)
                                        <span class="px-2 py-0.5 rounded bg-[#FF5A1F] text-white text-[11px] font-bold uppercase tracking-wider font-numeric">
                                            ★ Pilihan Coach
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Program Content -->
                            <div class="p-5 space-y-4">
                                
                                <!-- Coach Info Bar -->
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $coachAvatar }}" 
                                         alt="{{ $program->coach->name ?? 'Coach' }}" 
                                         class="w-7 h-7 rounded-full object-cover border border-slate-700 shrink-0"
                                         onerror="this.src='{{ asset('images/profile/17.jpg') }}'">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs font-semibold text-white truncate">
                                                {{ $program->coach->name ?? 'Coach Ruang Lari' }}
                                            </span>
                                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="text-[11px] text-slate-400 truncate">
                                            {{ $program->city->name ?? ($program->coach->city->name ?? 'Coach Terverifikasi') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Program Title -->
                                <h3 class="text-base sm:text-lg font-bold font-editorial-heading text-white group-hover:text-[#FF5A1F] transition leading-snug line-clamp-2">
                                    <a href="{{ url('/programs/' . $program->slug) }}">{{ $program->title }}</a>
                                </h3>

                                <!-- Short Description (Sanitized Excerpt) -->
                                <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed font-body">
                                    {{ $cleanDesc ?: 'Program latihan lari bertahap dengan menu terstruktur untuk mencapai target waktu terbaik tanpa risiko cedera.' }}
                                </p>

                                <!-- Key Stats Strip -->
                                <div class="grid grid-cols-3 gap-2 p-2.5 rounded-md bg-[#182338] border border-slate-800 text-center font-numeric">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Frekuensi</span>
                                        <span class="text-xs font-bold text-white">{{ $weeklySessions }} Sesi/Mgg</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Rating</span>
                                        <span class="text-xs font-bold text-[#FF5A1F]">★ {{ number_format($program->average_rating ?: 4.9, 1) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Peserta</span>
                                        <span class="text-xs font-bold text-white">{{ $program->enrolled_count ?: 12 }}+ Pelari</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Card Footer (Price & Action) -->
                        <div class="px-5 pb-5 pt-3 border-t border-slate-800 flex items-center justify-between gap-3 font-numeric">
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase font-normal">Biaya Program</span>
                                <div class="text-base font-bold text-white">
                                    {{ $program->isFree() ? 'Gratis' : 'Rp ' . number_format($program->price, 0, ',', '.') }}
                                </div>
                            </div>
                            <a href="{{ url('/programs/' . $program->slug) }}" 
                               class="px-4 py-2.5 rounded-md bg-[#FF5A1F] hover:bg-[#e04a12] text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-[#FF5A1F]/10">
                                Lihat Program
                            </a>
                        </div>

                    </article>
                @endforeach
            </div>

            <!-- Pagination Links -->
            @if($programs->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $programs->appends(request()->query())->fragment('katalog-program-coach')->links() }}
                </div>
            @endif

        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4 athletic-card max-w-lg mx-auto space-y-4">
                <div class="w-12 h-12 rounded-full bg-[#182338] border border-slate-800 mx-auto flex items-center justify-center text-slate-400 text-lg font-bold">
                    ✕
                </div>
                <h3 class="text-lg font-bold font-editorial-heading text-white">Tidak Ada Program yang Cocok</h3>
                <p class="text-xs text-slate-300 max-w-sm mx-auto leading-relaxed font-body">
                    Tidak ditemukan program lari untuk kriteria filter atau kata kunci pencarian yang kamu pilih saat ini.
                </p>
                <a href="{{ route('programs.index') }}#katalog-program-coach" 
                   class="inline-block px-5 py-2.5 rounded-md bg-[#182338] hover:bg-[#1f2d48] border border-slate-800 hover:border-slate-700 text-white font-semibold text-xs transition uppercase">
                    Reset Semua Filter
                </a>
            </div>
        @endif

    </section>

    <!-- ====================================================================
         SECTION 3: FIND YOUR RUNNING LEVEL (INTERACTIVE JOURNEY MAP)
         ==================================================================== -->
    <section id="running-levels" class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800 relative overflow-hidden">
        
        <!-- Athletic Running Route Contours Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-20" aria-hidden="true">
            <svg class="w-full h-full object-cover" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M-50 150 C 200 50, 450 250, 750 120 C 1050 -10, 1150 220, 1300 160" stroke="#FF5A1F" stroke-width="1.5" stroke-dasharray="8 10" stroke-opacity="0.3"/>
                <path d="M-50 230 C 220 130, 470 330, 770 200 C 1070 70, 1170 300, 1300 240" stroke="rgba(255, 255, 255, 0.04)" stroke-width="1.5"/>
                <path d="M-50 310 C 240 210, 490 410, 790 280 C 1090 150, 1190 380, 1300 320" stroke="rgba(255, 255, 255, 0.03)" stroke-width="1"/>
            </svg>
        </div>

        <div class="relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block mb-2">
                    Panduan Level Program Lari
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                    Pilih Level Program Lari Sesuai Kemampuanmu
                </h2>
                <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                    Pilih titik awal <strong>program lari</strong> yang sesuai dengan kebugaran saat ini. Dari program lari pemula (5K), program lari 10K untuk melatih ketahanan laktat, hingga program lari half marathon 21.1K.
                </p>
            </div>

            <!-- Athletic Elevation & Training Volume Profile SVG -->
            <div class="mb-10 athletic-card p-5 sm:p-6 relative overflow-hidden">
                <!-- Header with Telemetry Stats -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-800 text-xs font-numeric">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#CCFF00]"></span>
                        <span class="text-white font-bold tracking-wide uppercase">Profil Elevasi & Progresi Jarak Pelari</span>
                    </div>
                    <div class="flex items-center gap-4 text-slate-400">
                        <span>Elevasi Akumulasi: <strong class="text-white">+280m</strong></span>
                        <span>Jarak Puncak: <strong class="text-[#CCFF00]">21.1 KM</strong></span>
                        <span class="hidden sm:inline">Metode: <strong class="text-white">Daniels' Running Formula</strong></span>
                    </div>
                </div>

                <!-- SVG Elevation Graph -->
                <div class="w-full overflow-x-auto">
                    <svg class="w-full min-w-[640px] h-48 sm:h-56" viewBox="0 0 900 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="elevGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#CCFF00" stop-opacity="0.25"/>
                                <stop offset="45%" stop-color="#FF5A1F" stop-opacity="0.12"/>
                                <stop offset="100%" stop-color="#0b111e" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="elevStroke" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%" stop-color="#22C55E"/>
                                <stop offset="35%" stop-color="#FF5A1F"/>
                                <stop offset="75%" stop-color="#CCFF00"/>
                                <stop offset="100%" stop-color="#FF5A1F"/>
                            </linearGradient>
                            <pattern id="elevGrid" width="60" height="30" patternUnits="userSpaceOnUse">
                                <path d="M 60 0 L 0 0 0 30" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                            </pattern>
                        </defs>

                        <!-- Background Grid -->
                        <rect width="900" height="175" fill="url(#elevGrid)"/>

                        <!-- Y-Axis Elevation Markers -->
                        <line x1="40" y1="25" x2="880" y2="25" stroke="rgba(255,255,255,0.06)" stroke-dasharray="3 3"/>
                        <text x="32" y="29" fill="#64748b" font-size="9" font-family="'JetBrains Mono', monospace" text-anchor="end">200m</text>

                        <line x1="40" y1="70" x2="880" y2="70" stroke="rgba(255,255,255,0.06)" stroke-dasharray="3 3"/>
                        <text x="32" y="74" fill="#64748b" font-size="9" font-family="'JetBrains Mono', monospace" text-anchor="end">120m</text>

                        <line x1="40" y1="115" x2="880" y2="115" stroke="rgba(255,255,255,0.06)" stroke-dasharray="3 3"/>
                        <text x="32" y="119" fill="#64748b" font-size="9" font-family="'JetBrains Mono', monospace" text-anchor="end">50m</text>

                        <line x1="40" y1="160" x2="880" y2="160" stroke="rgba(255,255,255,0.06)" stroke-dasharray="3 3"/>
                        <text x="32" y="164" fill="#64748b" font-size="9" font-family="'JetBrains Mono', monospace" text-anchor="end">0m</text>

                        <!-- Elevation Shaded Area -->
                        <path d="M 50 160 
                                 C 120 155, 180 145, 260 140 
                                 C 340 135, 400 110, 520 95 
                                 C 620 80, 700 40, 800 35 
                                 L 850 40 L 850 160 L 50 160 Z" 
                              fill="url(#elevGrad)"/>

                        <!-- Elevation Profile Stroke Line -->
                        <path d="M 50 160 
                                 C 120 155, 180 145, 260 140 
                                 C 340 135, 400 110, 520 95 
                                 C 620 80, 700 40, 800 35 
                                 L 850 40" 
                              stroke="url(#elevStroke)" stroke-width="3" stroke-linecap="round"/>

                        <!-- Stage 1 Marker: 5K Foundation -->
                        <line x1="260" y1="25" x2="260" y2="160" stroke="#22C55E" stroke-opacity="0.3" stroke-dasharray="2 4"/>
                        <circle cx="260" cy="140" r="5" fill="#22C55E"/>
                        <circle cx="260" cy="140" r="9" stroke="#22C55E" stroke-opacity="0.4" stroke-width="2"/>
                        <rect x="215" y="100" width="90" height="28" rx="4" fill="#0e1626" stroke="#1e293b"/>
                        <text x="260" y="113" fill="#22C55E" font-size="9" font-weight="700" text-anchor="middle" font-family="'Inter', sans-serif">5K FINISHER</text>
                        <text x="260" y="123" fill="#94a3b8" font-size="8" text-anchor="middle" font-family="'JetBrains Mono', monospace">Aerobic Base +25m</text>

                        <!-- Stage 2 Marker: 10K Builder -->
                        <line x1="520" y1="25" x2="520" y2="160" stroke="#FF5A1F" stroke-opacity="0.3" stroke-dasharray="2 4"/>
                        <circle cx="520" cy="95" r="5" fill="#FF5A1F"/>
                        <circle cx="520" cy="95" r="9" stroke="#FF5A1F" stroke-opacity="0.4" stroke-width="2"/>
                        <rect x="475" y="55" width="90" height="28" rx="4" fill="#0e1626" stroke="#1e293b"/>
                        <text x="520" y="68" fill="#FF5A1F" font-size="9" font-weight="700" text-anchor="middle" font-family="'Inter', sans-serif">10K BUILDER</text>
                        <text x="520" y="78" fill="#94a3b8" font-size="8" text-anchor="middle" font-family="'JetBrains Mono', monospace">Threshold +75m</text>

                        <!-- Stage 3 Marker: 21.1K Half Marathon -->
                        <line x1="800" y1="15" x2="800" y2="160" stroke="#CCFF00" stroke-opacity="0.4" stroke-dasharray="2 4"/>
                        <circle cx="800" cy="35" r="6" fill="#CCFF00"/>
                        <circle cx="800" cy="35" r="11" stroke="#CCFF00" stroke-opacity="0.5" stroke-width="2"/>
                        <rect x="740" y="5" width="120" height="26" rx="4" fill="#0e1626" stroke="#CCFF00" stroke-opacity="0.4"/>
                        <text x="800" y="17" fill="#CCFF00" font-size="9" font-weight="800" text-anchor="middle" font-family="'Inter Tight', sans-serif">21.1K HALF MARATHON</text>
                        <text x="800" y="26" fill="#94a3b8" font-size="8" text-anchor="middle" font-family="'JetBrains Mono', monospace">Peak Incline +180m</text>

                        <!-- X-Axis Distance Labels -->
                        <text x="50" y="190" fill="#64748b" font-size="9" font-family="'JetBrains Mono', monospace">0 KM</text>
                        <text x="260" y="190" fill="#22C55E" font-size="9" font-weight="bold" font-family="'JetBrains Mono', monospace" text-anchor="middle">5 KM</text>
                        <text x="520" y="190" fill="#FF5A1F" font-size="9" font-weight="bold" font-family="'JetBrains Mono', monospace" text-anchor="middle">10 KM</text>
                        <text x="800" y="190" fill="#CCFF00" font-size="9" font-weight="bold" font-family="'JetBrains Mono', monospace" text-anchor="middle">21.1 KM</text>
                    </svg>
                </div>
            </div>

            <!-- Timeline Progression Bar (Start -> Build -> Conquer) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <button type="button" 
                        onclick="setRunningLevel('starter')" 
                        id="level-btn-starter"
                        class="journey-step-btn is-active ring-1 ring-[#FF5A1F] p-5 text-left flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between mb-3 font-numeric">
                            <span class="text-xs font-bold text-slate-400 uppercase">Tahap 01 • Start</span>
                            <span class="level-tag px-2 py-0.5 rounded text-[11px] font-bold bg-[#FF5A1F] text-white">5K</span>
                        </div>
                        <div class="text-2xl font-bold font-editorial-heading text-white">STARTER</div>
                        <div class="text-xs text-slate-300 mt-1">5K Foundation Program</div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between font-numeric">
                        <span>Goal: <strong class="text-white font-medium">Rutinitas Konsisten</strong></span>
                        <span class="text-emerald-400 font-mono text-[11px]">3x / Mgg</span>
                    </div>
                </button>

                <button type="button" 
                        onclick="setRunningLevel('builder')" 
                        id="level-btn-builder"
                        class="journey-step-btn p-5 text-left flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between mb-3 font-numeric">
                            <span class="text-xs font-bold text-slate-400 uppercase">Tahap 02 • Build</span>
                            <span class="level-tag px-2 py-0.5 rounded text-[11px] font-bold bg-[#182338] text-slate-400">10K</span>
                        </div>
                        <div class="text-2xl font-bold font-editorial-heading text-white">BUILDER</div>
                        <div class="text-xs text-slate-300 mt-1">10K Performance Program</div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between font-numeric">
                        <span>Goal: <strong class="text-white font-medium">Endurance & Tempo</strong></span>
                        <span class="text-[#FF5A1F] font-mono text-[11px]">4x / Mgg</span>
                    </div>
                </button>

                <button type="button" 
                        onclick="setRunningLevel('challenger')" 
                        id="level-btn-challenger"
                        class="journey-step-btn p-5 text-left flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between mb-3 font-numeric">
                            <span class="text-xs font-bold text-slate-400 uppercase">Tahap 03 • Conquer</span>
                            <span class="level-tag px-2 py-0.5 rounded text-[11px] font-bold bg-[#182338] text-slate-400">21.1K</span>
                        </div>
                        <div class="text-2xl font-bold font-editorial-heading text-white">CHALLENGER</div>
                        <div class="text-xs text-slate-300 mt-1">Half Marathon Program</div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between font-numeric">
                        <span>Goal: <strong class="text-white font-medium">Race 21.1K Finish</strong></span>
                        <span class="text-[#CCFF00] font-mono text-[11px]">4-5x / Mgg</span>
                    </div>
                </button>
            </div>

            <!-- Dynamic Level Detail Card -->
            <div class="athletic-card p-6 sm:p-8">
                <!-- Level 1: STARTER -->
                <div id="level-pane-starter" class="level-detail-pane space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-4">
                        <div>
                            <div class="text-xs text-[#FF5A1F] uppercase font-bold tracking-wider">Fokus Program: Fondasi & Konsistensi</div>
                            <h3 class="text-2xl font-bold font-editorial-heading text-white mt-1">Starter: 5K Foundation Program</h3>
                        </div>
                        <a href="{{ route('programs.index', ['category' => '5k']) }}#katalog-program-coach" 
                           class="px-5 py-2.5 bg-[#FF5A1F] hover:bg-[#e04a12] text-white font-bold rounded-md text-xs uppercase tracking-wider text-center shrink-0">
                            Lihat Program Coach 5K
                        </a>
                    </div>

                    <!-- Telemetry Target Bar Starter -->
                    <div class="p-3 bg-[#141d2e] rounded-md border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs font-numeric">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-white font-bold uppercase tracking-wider">Target Metrik Starter</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[11px] text-slate-300">
                            <span>Jarak: <strong class="text-white">5.0 KM Non-stop</strong></span>
                            <span class="text-slate-600 hidden sm:inline">•</span>
                            <span>Cadence: <strong class="text-white">165-172 SPM</strong></span>
                            <span class="text-slate-600 hidden sm:inline">•</span>
                            <span>Zona HR: <strong class="text-emerald-400">Zone 2 Aerobic (65-75%)</strong></span>
                        </div>
                    </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">01. Run Consistency</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Mengubah lari dari siklus "seminggu semangat lalu sebulan berhenti" menjadi kebiasaan tetap 3 kali per minggu melalui metode run-walk terukur.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">02. Basic Technique</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Pembiasaan cadence 170 SPM, posisi postur tegak rileks, dan efisiensi ayunan tangan agar paru-paru tidak cepat kehabisan oksigen.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">03. Injury Prevention</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Pengenalan latihan penguatan betis, lutut, dan pergelangan kaki untuk mencegah keluhan umum pemula seperti shin splints dan nyeri sendi.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-[#182338] rounded-md border border-slate-800 font-numeric">
                    <div class="text-xs font-bold text-slate-400 mb-3 uppercase">Contoh Jadwal Mingguan Starter</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Selasa</span>
                            <span class="font-bold text-white">25m Run-Walk Interval</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Kamis</span>
                            <span class="font-bold text-white">20m Easy Aerobic Run</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Sabtu</span>
                            <span class="font-bold text-white">Core & Mobility Drills</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Minggu</span>
                            <span class="font-bold text-[#FF5A1F]">3.5 KM Continuous Run</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Level 2: BUILDER -->
            <div id="level-pane-builder" class="level-detail-pane hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-4">
                    <div>
                        <div class="text-xs text-[#FF5A1F] uppercase font-bold tracking-wider">Fokus Program: Kapasitas Laktat & Kecepatan</div>
                        <h3 class="text-2xl font-bold font-editorial-heading text-white mt-1">Builder: 10K Performance Program</h3>
                    </div>
                    <a href="{{ route('programs.index', ['category' => '10k']) }}#katalog-program-coach" 
                       class="px-5 py-2.5 bg-[#FF5A1F] hover:bg-[#e04a12] text-white font-bold rounded-md text-xs uppercase tracking-wider text-center shrink-0">
                        Lihat Program Coach 10K
                    </a>
                </div>

                <!-- Telemetry Target Bar Builder -->
                <div class="p-3 bg-[#141d2e] rounded-md border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs font-numeric">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#FF5A1F]"></span>
                        <span class="text-white font-bold uppercase tracking-wider">Target Metrik Builder</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[11px] text-slate-300">
                        <span>Jarak: <strong class="text-white">10.0 KM Race</strong></span>
                        <span class="text-slate-600 hidden sm:inline">•</span>
                        <span>Cadence: <strong class="text-white">172-178 SPM</strong></span>
                        <span class="text-slate-600 hidden sm:inline">•</span>
                        <span>Zona HR: <strong class="text-[#FF5A1F]">Zone 3-4 Threshold (75-88%)</strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">01. Tempo Training</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Menaikkan ambang batas laktat (lactate threshold) agar kamu dapat mempertahankan pace target secara stabil dalam durasi 45-60 menit.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">02. Interval Speed</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Sesi VO2 Max terarah (400m-800m repeats) untuk menstimulasi sistem kardiovaskular dan mempercepat pemulihan detak jantung saat intensitas tinggi.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">03. Better Pacing Strategy</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Menghilangkan kebiasaan "lari terlalu cepat di awal lalu kehabisan tenaga" melalui disiplin pacing zona 2 dan pembacaan metrik lari yang tepat.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-[#182338] rounded-md border border-slate-800 font-numeric">
                    <div class="text-xs font-bold text-slate-400 mb-3 uppercase">Contoh Jadwal Mingguan Builder</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Selasa</span>
                            <span class="font-bold text-white">5 KM Easy Recovery Run</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Kamis</span>
                            <span class="font-bold text-white">6x400m Track Intervals</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Jumat</span>
                            <span class="font-bold text-white">4 KM Threshold Tempo</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Minggu</span>
                            <span class="font-bold text-[#FF5A1F]">8-10 KM Aerobic Long Run</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Level 3: CHALLENGER -->
            <div id="level-pane-challenger" class="level-detail-pane hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-4">
                    <div>
                        <div class="text-xs text-[#FF5A1F] uppercase font-bold tracking-wider">Fokus Program: Ketahanan Jarak & Manajemen Race</div>
                        <h3 class="text-2xl font-bold font-editorial-heading text-white mt-1">Challenger: Half Marathon 21.1K Preparation</h3>
                    </div>
                    <a href="{{ route('programs.index', ['category' => '21k']) }}#katalog-program-coach" 
                       class="px-5 py-2.5 bg-[#FF5A1F] hover:bg-[#e04a12] text-white font-bold rounded-md text-xs uppercase tracking-wider text-center shrink-0">
                        Lihat Program Coach 21K
                    </a>
                </div>

                <!-- Telemetry Target Bar Challenger -->
                <div class="p-3 bg-[#141d2e] rounded-md border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs font-numeric">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#CCFF00]"></span>
                        <span class="text-white font-bold uppercase tracking-wider">Target Metrik Challenger</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[11px] text-slate-300">
                        <span>Jarak: <strong class="text-white">21.1 KM Half Marathon</strong></span>
                        <span class="text-slate-600 hidden sm:inline">•</span>
                        <span>Cadence: <strong class="text-white">175-182 SPM</strong></span>
                        <span class="text-slate-600 hidden sm:inline">•</span>
                        <span>Zona HR: <strong class="text-[#CCFF00]">Aerobic Engine & Race Pace</strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">01. Long Run Strategy</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Progresi jarak akhir pekan berkala dari 12K hingga 18K untuk melatih efisiensi oksidasi lemak dan daya tahan tendon terhadap benturan berulang.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">02. Race Preparation & Fueling</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Simulasi asupan energi (energy gel tiap 45 menit), manajemen hidrasi elektrolit, dan protokol tapering 2 minggu sebelum perlombaan.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase">03. Mental Endurance</div>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                            Teknik pembagian kilometer (segmented pacing) untuk mengatasi fase kelelahan di KM 16-19 sehingga dapat menembus garis finish dalam kondisi prima.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-[#182338] rounded-md border border-slate-800 font-numeric">
                    <div class="text-xs font-bold text-slate-400 mb-3 uppercase">Contoh Jadwal Mingguan Challenger</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Selasa</span>
                            <span class="font-bold text-white">7 KM Easy Recovery Run</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Rabu</span>
                            <span class="font-bold text-white">5x1000m Threshold Repeats</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Jumat</span>
                            <span class="font-bold text-white">6 KM Progressive Pace</span>
                        </div>
                        <div class="p-2.5 bg-[#131b2c] rounded border border-slate-800">
                            <span class="text-slate-400 block">Minggu</span>
                            <span class="font-bold text-[#FF5A1F]">14-16 KM Long Run Aerobik</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    <!-- ====================================================================
         SECTION 4: HOW THE PROGRAM WORKS (4-STEP COACHING PROCESS)
         ==================================================================== -->
    <section id="assessment-section" class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800 relative overflow-hidden">
        
        <!-- Athletic Running Cadence & Track Curves Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-15" aria-hidden="true">
            <svg class="w-full h-full object-cover" viewBox="0 0 1200 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="600" cy="520" rx="800" ry="240" stroke="#FF5A1F" stroke-width="1.5" stroke-dasharray="6 8" stroke-opacity="0.3"/>
                <ellipse cx="600" cy="520" rx="720" ry="200" stroke="rgba(255,255,255,0.04)" stroke-width="1.5"/>
                <ellipse cx="600" cy="520" rx="640" ry="160" stroke="#CCFF00" stroke-width="1" stroke-dasharray="4 6" stroke-opacity="0.25"/>
            </svg>
        </div>

        <div class="relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block mb-2 font-numeric">
                    Metodologi Pelatihan
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                    Bagaimana Program Lari Ini Membangun Performa Kamu
                </h2>
                <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                    Setiap pelari memiliki titik awal yang unik. Alur 4 tahap dalam setiap <strong>program lari</strong> Ruang Lari memastikan jadwal latihan lari selaras dengan kapasitas kardiovaskular dan target perlombaanmu.
                </p>
            </div>

            <!-- Athletic Cadence & Telemetry Process Ribbon -->
            <div class="hidden lg:flex items-center justify-between mb-8 p-4 rounded-lg bg-[#0e1626] border border-slate-800 font-numeric text-xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5A1F] animate-pulse"></span>
                    <span class="text-white font-bold uppercase tracking-wider">Siklus Periodisasi Terukur</span>
                </div>
                
                <!-- Cadence Waveform & Track Line -->
                <div class="flex items-center gap-3 flex-1 max-w-md mx-6">
                    <span class="text-[10px] text-slate-400 font-mono">160 SPM</span>
                    <svg class="h-6 flex-1 text-[#FF5A1F]" viewBox="0 0 220 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 12 Q 12 3, 24 12 T 48 12 T 72 12 T 96 7 T 120 17 T 144 5 T 168 19 T 192 4 T 220 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="218" cy="12" r="3" fill="#CCFF00"/>
                    </svg>
                    <span class="text-[10px] text-[#CCFF00] font-mono font-bold">180 SPM</span>
                </div>

                <div class="flex items-center gap-3 text-slate-400 text-[11px]">
                    <span class="px-2.5 py-0.5 rounded bg-[#182338] text-slate-200">Siklus 8 - 16 Minggu</span>
                    <span class="text-white font-bold">100% Berbasis Progres</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Step 01 -->
                <div class="athletic-card p-6 flex flex-col justify-between relative">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-2xl font-bold font-editorial-heading text-[#FF5A1F]">01</div>
                            <div class="w-10 h-10 rounded-md bg-[#182338] border border-slate-800 flex items-center justify-center text-[#FF5A1F]">
                                <!-- Biometric Assessment Radar SVG -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.75"/><circle cx="12" cy="12" r="4" stroke-width="1.75"/><path stroke-linecap="round" stroke-width="2" d="M12 3v4m0 10v4m-9-9h4m10 0h4"/></svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-editorial-heading text-white mb-2">Assessment</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4 font-body">
                            Analisis komprehensif profil pelari:
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Current ability</strong> (Pace & jarak terjauh)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Running history</strong> & riwayat cedera</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Target race</strong> & tanggal perlombaan</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-3 border-t border-slate-800 text-[11px] text-slate-400">
                        Fondasi evaluasi awal
                    </div>
                </div>

                <!-- Step 02 -->
                <div class="athletic-card p-6 flex flex-col justify-between relative">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-2xl font-bold font-editorial-heading text-[#FF5A1F]">02</div>
                            <div class="w-10 h-10 rounded-md bg-[#182338] border border-slate-800 flex items-center justify-center text-[#FF5A1F]">
                                <!-- Pacing Matrix Calendar SVG -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/><path stroke-linecap="round" stroke-width="2" d="M8 15h2m4 0h2"/></svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-editorial-heading text-white mb-2">Training Plan</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4 font-body">
                            Jadwal mingguan yang dipersonalisasi:
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Easy run</strong> zona aerobik untuk pemulihan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Interval speed</strong> untuk kapasitas paru</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Tempo run</strong> untuk ketahanan laktat</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Long run</strong> terstruktur akhir pekan</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-3 border-t border-slate-800 text-[11px] text-slate-400">
                        Kombinasi intensitas seimbang
                    </div>
                </div>

                <!-- Step 03 -->
                <div class="athletic-card p-6 flex flex-col justify-between relative">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-2xl font-bold font-editorial-heading text-[#FF5A1F]">03</div>
                            <div class="w-10 h-10 rounded-md bg-[#182338] border border-slate-800 flex items-center justify-center text-[#FF5A1F]">
                                <!-- Heart Rate & Cadence Pulse SVG -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 13h4l3-7 4 14 3-7h4"/></svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-editorial-heading text-white mb-2">Progress Tracking</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4 font-body">
                            Pemantauan parameter fisik secara objektif:
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Distance improvement</strong> & volume mingguan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Pace development</strong> pada HR stabil</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Training consistency</strong> streak & feedback</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-3 border-t border-slate-800 text-[11px] text-slate-400">
                        Bukti telemetri nyata
                    </div>
                </div>

                <!-- Step 04 -->
                <div class="athletic-card p-6 flex flex-col justify-between relative">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-2xl font-bold font-editorial-heading text-[#CCFF00]">04</div>
                            <div class="w-10 h-10 rounded-md bg-[#182338] border border-slate-800 flex items-center justify-center text-[#CCFF00]">
                                <!-- Finish Line Ribbon / Timing Gate Flag SVG -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V14"/></svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-editorial-heading text-white mb-2">Race Preparation</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4 font-body">
                            Kesiapan total menuju garis start:
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300">
                            <li class="flex items-start gap-2">
                                <span class="text-[#CCFF00]">•</span>
                                <span><strong>Strategy</strong> pacing kilometer-demi-kilometer</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#CCFF00]">•</span>
                                <span><strong>Nutrition</strong> & hydration intake protocol</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#CCFF00]">•</span>
                                <span><strong>Recovery</strong>, deload, & peak taper</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-3 border-t border-slate-800 text-[11px] text-slate-400">
                        Eksekusi lomba tanpa panik
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         SECTION 5: TRAINING DASHBOARD PREVIEW
         ==================================================================== -->
    <section class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block mb-2">
                Pelacakan Sesi & Telemetri
            </span>
            <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                Pantau Progres Program Lari dalam Satu Dashboard
            </h2>
            <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                Setiap peserta <strong>program lari</strong> mendapatkan akses dashboard telemetri terintegrasi untuk mencatat jarak mingguan, distribusi detak jantung zona aerobik, serta evaluasi kemajuan jadwal latihan lari secara presisi.
            </p>
        </div>

        <!-- Realistic Athlete App Window -->
        <div class="athletic-card overflow-hidden shadow-2xl max-w-5xl mx-auto">
            <!-- App Navigation Bar Header -->
            <div class="px-5 py-3.5 bg-[#0b111e] border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 font-numeric">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="font-editorial-heading font-bold text-sm text-white tracking-wide">Ruang Lari Athlete Lab</span>
                    <span class="text-slate-400 hidden sm:inline">• Siklus Minggu 6 dari 12</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded bg-[#182338] text-slate-300 text-[11px] font-semibold">
                        Target: Jakarta Half Marathon
                    </span>
                </div>
            </div>

            <!-- Dashboard Main Screen -->
            <div class="p-6 sm:p-8 bg-[#0e1626] space-y-6">
                <!-- 4 Core Required Telemetry Indicators -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 font-numeric">
                    <div class="p-4 rounded-md bg-[#141d2e] border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Weekly Mileage</div>
                        <div class="text-2xl sm:text-3xl font-bold text-white mt-1">32.5 <span class="text-xs font-normal text-slate-400">KM</span></div>
                        <div class="text-[11px] text-emerald-400 mt-1">+2.8 KM dari minggu lalu</div>
                    </div>

                    <div class="p-4 rounded-md bg-[#141d2e] border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Current Pace</div>
                        <div class="text-2xl sm:text-3xl font-bold text-white mt-1">6:05 <span class="text-xs font-normal text-slate-400">/km</span></div>
                        <div class="text-[11px] text-slate-400 mt-1">Zona Aerobik Terjaga</div>
                    </div>

                    <div class="p-4 rounded-md bg-[#141d2e] border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Training Status</div>
                        <div class="text-base sm:text-lg font-bold text-[#FF5A1F] font-editorial-heading mt-1.5 uppercase">Building Endurance</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Fase Base Periodization</div>
                    </div>

                    <div class="p-4 rounded-md bg-[#141d2e] border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Next Session</div>
                        <div class="text-base sm:text-lg font-bold text-white font-editorial-heading mt-1.5">Long Run — 12 KM</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Minggu • 05:45 WIB</div>
                    </div>
                </div>

                <!-- Structured Sessions Telemetry Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left 2 Cols: Weekly Training Log -->
                    <div class="lg:col-span-2 p-5 rounded-md bg-[#141d2e] border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-400 pb-2 border-b border-slate-800 font-numeric">
                            <span class="font-bold text-white">Distribusi Sesi Minggu Ini</span>
                            <span>4 Sesi • 1 Rest • 2 Cross-Training</span>
                        </div>

                        <div class="space-y-2 text-xs font-numeric">
                            <div class="p-2.5 rounded bg-[#182338] border border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-1.5 h-6 rounded-sm bg-emerald-500"></span>
                                    <div>
                                        <div class="font-bold text-white">Easy Base Run • 6.0 KM</div>
                                        <div class="text-slate-400 text-[11px]">Selesai • Avg Pace 6:18/km • Avg HR 138 bpm</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-emerald-950/80 border border-emerald-700/60 text-emerald-300 text-[10px] font-bold">Tuntas</span>
                            </div>

                            <div class="p-2.5 rounded bg-[#182338] border border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-1.5 h-6 rounded-sm bg-emerald-500"></span>
                                    <div>
                                        <div class="font-bold text-white">Interval Speed • 5x800m @ 5:10/km</div>
                                        <div class="text-slate-400 text-[11px]">Selesai • Recovery Jog 200m • Cadence 178 spm</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-emerald-950/80 border border-emerald-700/60 text-emerald-300 text-[10px] font-bold">Tuntas</span>
                            </div>

                            <div class="p-2.5 rounded bg-[#182338] border border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-1.5 h-6 rounded-sm bg-amber-500"></span>
                                    <div>
                                        <div class="font-bold text-white">Tempo Threshold • 6.0 KM</div>
                                        <div class="text-slate-400 text-[11px]">Selesai • Constant Pace 5:40/km • RPE 7/10</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-emerald-950/80 border border-emerald-700/60 text-emerald-300 text-[10px] font-bold">Tuntas</span>
                            </div>

                            <div class="p-2.5 rounded bg-[#1c2942] border border-[#FF5A1F]/30 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-1.5 h-6 rounded-sm bg-[#FF5A1F]"></span>
                                    <div>
                                        <div class="font-bold text-white">Sunday Long Run • 12.0 KM</div>
                                        <div class="text-slate-300 text-[11px]">Target Pace 6:25/km • Fokus Fueling KM 6 & 10</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-[#FF5A1F]/20 border border-[#FF5A1F]/40 text-[#FF5A1F] text-[10px] font-bold">Besok</span>
                            </div>
                        </div>

                        <!-- Mini Session Telemetry Analysis Split Bars -->
                        <div class="pt-3 border-t border-slate-800">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 mb-2 font-numeric">
                                <span class="text-white font-semibold">Analisis Split Sesi Kemarin (6.0 KM)</span>
                                <span class="text-emerald-400 font-bold">Negative Split (-28 dtk)</span>
                            </div>
                            <div class="grid grid-cols-6 gap-1.5 font-numeric text-center">
                                <div class="p-1.5 rounded bg-[#0b111e] border border-slate-800">
                                    <span class="text-[9px] text-slate-400 block">KM 1</span>
                                    <span class="text-[11px] font-bold text-slate-200">6:18</span>
                                    <div class="w-full bg-[#1e293b] h-1 rounded-sm mt-1 overflow-hidden"><div class="bg-slate-400 h-full" style="width: 55%"></div></div>
                                </div>
                                <div class="p-1.5 rounded bg-[#0b111e] border border-slate-800">
                                    <span class="text-[9px] text-slate-400 block">KM 2</span>
                                    <span class="text-[11px] font-bold text-slate-200">6:12</span>
                                    <div class="w-full bg-[#1e293b] h-1 rounded-sm mt-1 overflow-hidden"><div class="bg-slate-400 h-full" style="width: 62%"></div></div>
                                </div>
                                <div class="p-1.5 rounded bg-[#0b111e] border border-slate-800">
                                    <span class="text-[9px] text-slate-400 block">KM 3</span>
                                    <span class="text-[11px] font-bold text-white">6:05</span>
                                    <div class="w-full bg-[#1e293b] h-1 rounded-sm mt-1 overflow-hidden"><div class="bg-emerald-500 h-full" style="width: 70%"></div></div>
                                </div>
                                <div class="p-1.5 rounded bg-[#0b111e] border border-slate-800">
                                    <span class="text-[9px] text-slate-400 block">KM 4</span>
                                    <span class="text-[11px] font-bold text-white">6:02</span>
                                    <div class="w-full bg-[#1e293b] h-1 rounded-sm mt-1 overflow-hidden"><div class="bg-emerald-500 h-full" style="width: 74%"></div></div>
                                </div>
                                <div class="p-1.5 rounded bg-[#0b111e] border border-slate-800">
                                    <span class="text-[9px] text-slate-400 block">KM 5</span>
                                    <span class="text-[11px] font-bold text-[#CCFF00]">5:58</span>
                                    <div class="w-full bg-[#1e293b] h-1 rounded-sm mt-1 overflow-hidden"><div class="bg-[#CCFF00] h-full" style="width: 82%"></div></div>
                                </div>
                                <div class="p-1.5 rounded bg-[#0b111e] border border-slate-800">
                                    <span class="text-[9px] text-slate-400 block">KM 6</span>
                                    <span class="text-[11px] font-bold text-[#CCFF00]">5:50</span>
                                    <div class="w-full bg-[#1e293b] h-1 rounded-sm mt-1 overflow-hidden"><div class="bg-[#CCFF00] h-full" style="width: 92%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Col: Heart Rate Zone Summary -->
                    <div class="p-5 rounded-md bg-[#141d2e] border border-slate-800 space-y-4">
                        <div class="text-xs font-bold text-white pb-2 border-b border-slate-800">
                            Intensitas Aerobik (HR Zone)
                        </div>

                        <div class="space-y-3 text-xs font-numeric">
                            <div>
                                <div class="flex justify-between text-slate-300 mb-1">
                                    <span>Zone 2 (Aerobic Base)</span>
                                    <span class="text-white font-bold">72%</span>
                                </div>
                                <div class="w-full bg-[#0b111e] h-2 rounded-sm overflow-hidden">
                                    <div class="bg-emerald-500 h-full" style="width: 72%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-slate-300 mb-1">
                                    <span>Zone 3 (Tempo Threshold)</span>
                                    <span class="text-white font-bold">18%</span>
                                </div>
                                <div class="w-full bg-[#0b111e] h-2 rounded-sm overflow-hidden">
                                    <div class="bg-amber-500 h-full" style="width: 18%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-slate-300 mb-1">
                                    <span>Zone 4/5 (Speed Peak)</span>
                                    <span class="text-white font-bold">10%</span>
                                </div>
                                <div class="w-full bg-[#0b111e] h-2 rounded-sm overflow-hidden">
                                    <div class="bg-[#FF5A1F] h-full" style="width: 10%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 text-[11px] text-slate-400 border-t border-slate-800">
                            Volume dasar aerobik berada pada standar optimal 80/20.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         SECTION 6: COACHING PHILOSOPHY (EDITORIAL SPORTS SCIENCE)
         ==================================================================== -->
    <section class="py-16 sm:py-24 bg-[#0e1524] border-b border-slate-800 relative overflow-hidden">
        
        <!-- Stadium Track Curves & Technical Typography Watermark -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-20" aria-hidden="true">
            <svg class="w-full h-full object-cover" viewBox="0 0 1200 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="600" cy="500" rx="720" ry="260" stroke="rgba(255,255,255,0.05)" stroke-width="2"/>
                <ellipse cx="600" cy="500" rx="620" ry="210" stroke="#FF5A1F" stroke-width="1.5" stroke-dasharray="8 10" stroke-opacity="0.3"/>
                <ellipse cx="600" cy="500" rx="520" ry="160" stroke="rgba(255,255,255,0.03)" stroke-width="1.5"/>
                <text x="600" y="210" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="2" font-size="64" font-weight="900" font-family="'Inter Tight', sans-serif" text-anchor="middle" letter-spacing="14">
                    ENDURANCE • SCIENCE • RECOVERY
                </text>
            </svg>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative z-10">
            
            <div class="text-center space-y-4">
                <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block">
                    Filosofi & Metode Pelatihan
                </span>
                
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-editorial-heading text-white leading-tight">
                    Prinsip Sports Science di Balik <span class="text-[#FF5A1F]">Program Lari</span> Kami
                </h2>

                <blockquote class="text-xl sm:text-2xl font-semibold text-slate-200 italic max-w-2xl mx-auto pt-1 font-editorial-heading">
                    "Pelari tangguh bukan dibangun dalam sehari, melainkan melalui volume yang konsisten dan pemulihan terukur."
                </blockquote>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-3xl mx-auto pt-2 font-body">
                    Kami menolak anggapan bahwa jadwal latihan lari harus selalu memicu kelelahan ekstrem atau cedera. Melalui metodologi <strong>program lari</strong> berbasis sains olahraga, peningkatan daya tahan dibangun dari penguasaan zona aerobik, beban progresif, dan fase pemulihan yang tepat.
                </p>
            </div>

            <!-- 4 Science-Based Editorial Pillars -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                <div class="athletic-card p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wide">01. Fisiologi Aerobik yang Terukur</div>
                        <div class="w-8 h-8 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-[#FF5A1F]">
                            <!-- Mitochondria / Cellular Respiration Icon -->
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold font-editorial-heading text-white">Science-Based Training</h4>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                        Porsi lari lambat zona 2 bukan lari santai tanpa arti. Ini merangsang proliferasi mitokondria dan kepadatan kapiler darah agar tubuh makin efisien membakar energi pada jarak jauh.
                    </p>
                </div>

                <div class="athletic-card p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wide">02. Aturan Kenaikan Maksimal 10%</div>
                        <div class="w-8 h-8 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-[#FF5A1F]">
                            <!-- Progressive Incline Overload Icon -->
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold font-editorial-heading text-white">Progressive Overload</h4>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                        Volume mingguan dinaikkan secara gradual untuk memberi kesempatan bagi tendon, ligamen, dan struktur tulang beradaptasi dengan impak lari tanpa risiko fraktur stres.
                    </p>
                </div>

                <div class="athletic-card p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wide">03. Adaptasi Terjadi Saat Istirahat</div>
                        <div class="w-8 h-8 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-[#FF5A1F]">
                            <!-- Deload & Muscle Recovery Icon -->
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold font-editorial-heading text-white">Recovery Management</h4>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                        Rest day dan siklus deload week terjadwal adalah bagian aktif dari program latihan, bukan hari membolos. Otot dibangun kembali saat tidur dan asupan nutrisi terjaga.
                    </p>
                </div>

                <div class="athletic-card p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-[#CCFF00] uppercase tracking-wide">04. Bebas Cedera Adalah Kunci Konsistensi</div>
                        <div class="w-8 h-8 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-[#CCFF00]">
                            <!-- Biomechanics Protection Shield Icon -->
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold font-editorial-heading text-white">Injury Prevention</h4>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-body">
                        Pelari terbaik adalah pelari yang tetap bisa berdiri di garis start. Latihan cadence tinggi, mobilitas panggul, dan penguatan otot penopang menjadi menu wajib setiap minggu.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- ====================================================================
         SECTION 7: SUCCESS STORIES
         ==================================================================== -->
    <section class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800 relative overflow-hidden">
        
        <!-- Athletic Running Track & Split Marker Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-15" aria-hidden="true">
            <svg class="w-full h-full object-cover" viewBox="0 0 1200 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M-40 280 C 260 160, 620 340, 920 220 C 1120 140, 1220 260, 1300 200" stroke="#FF5A1F" stroke-width="2" stroke-dasharray="10 12" stroke-opacity="0.35"/>
                <path d="M-40 340 C 280 220, 640 400, 940 280 C 1140 200, 1240 320, 1300 260" stroke="rgba(255,255,255,0.04)" stroke-width="1.5"/>
            </svg>
        </div>

        <div class="relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block mb-2">
                    Testimoni & Kisah Sukses
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                    Kisah Nyata Pelari Menyelesaikan Program Lari
                </h2>
                <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                    Bukan testimoni rekayasa. Ini adalah perjalanan nyata para pelari yang memulai dari nol dan berhasil menaklukkan target 5K, 10K, hingga Half Marathon dengan panduan <strong>program lari</strong> Ruang Lari.
                </p>
            </div>

            <!-- Featured Case Study with Real Finisher Photo -->
            <div class="athletic-card overflow-hidden mb-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">
                    <!-- Authentic Post-Race Photo -->
                    <div class="lg:col-span-5 relative bg-[#0b111e]">
                        <img src="{{ asset('images/testimonial/runner-finish-emotion.jpg') }}" 
                             alt="Authentic Indonesian runner catching breath after finishing race" 
                             class="w-full h-full object-cover min-h-[320px] lg:min-h-[440px]"
                             loading="lazy">
                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded bg-[#0b111e]/90 border border-slate-800 text-[10px] font-bold text-[#CCFF00] font-numeric tracking-wider">
                            BIB #1042 • FINISHER
                        </div>
                        <div class="absolute bottom-3 left-3 px-2.5 py-1 rounded bg-[#0b111e]/90 text-[11px] font-semibold text-slate-300 font-numeric">
                            Finisher 21K • Jakarta Half Marathon
                        </div>
                    </div>

                    <!-- Runner Story Breakdown -->
                    <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between space-y-6">
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <span class="px-2.5 py-0.5 rounded bg-[#182338] text-xs font-medium text-slate-300">
                                    Challenger Program Alumni • 12 Minggu Latihan
                                </span>
                                <span class="px-2 py-0.5 rounded bg-emerald-950/80 border border-emerald-700/60 text-emerald-300 text-[10px] font-bold uppercase font-numeric">
                                    Verified Finisher
                                </span>
                            </div>

                            <!-- Official Split Timing Strip -->
                            <div class="mb-4 p-3 bg-[#131b2c] rounded-md border border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs font-numeric">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-white font-bold uppercase tracking-wider text-[10px]">Official Split Telemetry</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2.5 text-[11px] text-slate-300">
                                    <span>5K: <strong class="text-white">28:10</strong></span>
                                    <span class="text-slate-700">•</span>
                                    <span>10K: <strong class="text-white">57:40</strong></span>
                                    <span class="text-slate-700">•</span>
                                    <span>15K: <strong class="text-white">1:28:15</strong></span>
                                    <span class="text-slate-700">•</span>
                                    <span>Finish: <strong class="text-[#CCFF00]">2:08:45</strong></span>
                                </div>
                            </div>

                            <blockquote class="text-xl sm:text-2xl font-bold font-editorial-heading text-white leading-snug mb-5">
                                "Dulu berhenti di kilometer 3. Sekarang berhasil finish Half Marathon pertama."
                            </blockquote>

                            <div class="space-y-3.5 text-xs sm:text-sm">
                                <div class="p-3.5 bg-[#182338] rounded-md border border-slate-800">
                                    <span class="font-bold text-slate-400 block text-xs uppercase mb-1">Before:</span>
                                    <p class="text-slate-300 leading-relaxed font-body">
                                        Napas selalu tersengal di KM 3, sering terserang shin splints karena memaksakan pace kencang, dan tidak punya rencana latihan yang terstruktur.
                                    </p>
                                </div>

                                <div class="p-3.5 bg-[#182338] rounded-md border border-slate-800">
                                    <span class="font-bold text-slate-400 block text-xs uppercase mb-1">Training Journey:</span>
                                    <p class="text-slate-300 leading-relaxed font-body">
                                        Disiplin menjalani 12 minggu Half Marathon Program. 80% lari di zona 2, membenahi cadence lari menjadi 174 SPM, serta simulasi nutrisi energi saat long run 16K.
                                    </p>
                                </div>

                                <div class="p-3.5 bg-[#182338] rounded-md border border-slate-800 font-numeric">
                                    <span class="font-bold text-[#FF5A1F] block text-xs uppercase mb-1">Achievement:</span>
                                    <p class="text-white font-semibold leading-relaxed">
                                        Berhasil finish 21.1K dengan catatan waktu resmi 2:08:45 dalam kondisi bugar tanpa kram dan tanpa cedera.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs font-numeric">
                            <div>
                                <div class="font-bold text-white text-sm">Aditya Pratama</div>
                                <div class="text-slate-400">Pegawai Swasta • Jakarta Selatan</div>
                            </div>
                            <div class="text-[#FF5A1F] font-bold text-sm">PB: 2:08:45</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2 Supporting Real Stories -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="athletic-card p-6 space-y-4">
                    <div class="flex items-center justify-between font-numeric">
                        <span class="px-2.5 py-0.5 rounded bg-[#182338] text-xs text-slate-300 font-medium">Starter 5K Program</span>
                        <span class="text-xs text-emerald-400 font-bold">5K Non-Stop • Pace 6:16</span>
                    </div>
                    <p class="text-sm text-slate-300 leading-relaxed italic font-body">
                        "Awalnya ragu apakah bisa lari 5K tanpa jalan kaki. Program run-walk di tahap Starter melatih mental dan napas saya pelan-pelan. Sekarang 5K sudah jadi rutinitas pagi yang menyenangkan."
                    </p>
                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs font-numeric">
                        <div>
                            <div class="font-bold text-white">Riana Kusuma</div>
                            <div class="text-slate-400">Ibu Rumah Tangga • Surabaya</div>
                        </div>
                        <div class="text-white font-bold">Catatan 5K: 31:20</div>
                    </div>
                </div>

                <div class="athletic-card p-6 space-y-4">
                    <div class="flex items-center justify-between font-numeric">
                        <span class="px-2.5 py-0.5 rounded bg-[#182338] text-xs text-slate-300 font-medium">Builder 10K Program</span>
                        <span class="text-xs text-emerald-400 font-bold">Pace Sub-55 • 5:18 /km</span>
                    </div>
                    <p class="text-sm text-slate-300 leading-relaxed italic font-body">
                        "Pace lari saya mentok di 6:40/km selama setahun. Lewat menu tempo run dan interval terarah dari coach, saya menembus waktu 53 menit di Pocari Sweat Run Bandung."
                    </p>
                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs font-numeric">
                        <div>
                            <div class="font-bold text-white">Dimas Satrio</div>
                            <div class="text-slate-400">Software Engineer • Bandung</div>
                        </div>
                        <div class="text-white font-bold">Pace 5:18 /km</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         SECTION 8: TIER REKOMENDASI & PERBANDINGAN FITUR
         ==================================================================== -->
    <section id="pricing-section" class="py-16 sm:py-20 bg-[#0e1524] border-b border-slate-800 relative overflow-hidden">
        
        <!-- Athletic Stadium Curves Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-15" aria-hidden="true">
            <svg class="w-full h-full object-cover" viewBox="0 0 1200 450" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="600" cy="450" rx="650" ry="220" stroke="rgba(255,255,255,0.04)" stroke-width="1.5"/>
                <ellipse cx="600" cy="450" rx="550" ry="170" stroke="#CCFF00" stroke-width="1" stroke-dasharray="8 8" stroke-opacity="0.25"/>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl mx-auto text-center mb-14">
                <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block mb-2">
                    Pilihan Paket Pelatihan
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                    Perbandingan Paket & Fitur Program Lari
                </h2>
                <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                    Pilih <strong>program lari</strong> yang paling sesuai dengan tingkat kebugaran dan target perlombaanmu. Dapatkan jadwal latihan lari personal, pemantauan pace VDOT, dan bimbingan langsung dari pelatih berlisensi.
                </p>
            </div>

            <!-- Comparison Table -->
            <div class="athletic-card overflow-hidden">
                <div class="p-4 bg-[#151f33] border-b border-slate-800 text-xs font-bold text-white uppercase tracking-wider flex items-center justify-between">
                    <span>Tabel Perbandingan Fitur Paket</span>
                    <span class="text-[11px] text-slate-400 font-normal">Garansi Periodisasi Terukur</span>
                </div>
                <div class="overflow-x-auto font-numeric">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 bg-[#182338] text-slate-300 font-medium">
                                <th class="p-4">Fitur Program</th>
                                <th class="p-4">Start (5K Foundation)</th>
                                <th class="p-4 text-[#CCFF00] font-bold">
                                    <div class="flex items-center gap-2">
                                        <span>Build (10K Performance)</span>
                                        <span class="px-2 py-0.5 rounded bg-[#CCFF00] text-[#080A0D] text-[10px] font-black uppercase tracking-wider">Populer</span>
                                    </div>
                                </th>
                                <th class="p-4">Advance (Half Marathon)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                            <tr>
                                <td class="p-4 font-medium text-white">Durasi Program</td>
                                <td class="p-4">8 Minggu</td>
                                <td class="p-4 text-white font-bold">10 Minggu</td>
                                <td class="p-4">12-16 Minggu</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-medium text-white">Frekuensi Sesi Mingguan</td>
                                <td class="p-4">3 Sesi / Minggu</td>
                                <td class="p-4 text-white font-bold">4 Sesi / Minggu</td>
                                <td class="p-4">4-5 Sesi / Minggu</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-medium text-white">Fokus Utama</td>
                                <td class="p-4">Konsistensi & Form Dasar</td>
                                <td class="p-4 text-white font-bold">Ambang Laktat & Kecepatan</td>
                                <td class="p-4">Ketahanan 21.1K & Mental Lomba</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-medium text-white">Kalkulator Pace & VDOT</td>
                                <td class="p-4">Dasar Aerobik</td>
                                <td class="p-4 text-white font-bold">Personalized VDOT</td>
                                <td class="p-4">Dynamic Periodization</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-medium text-white">Simulasi Nutrisi & Hidrasi</td>
                                <td class="p-4 text-slate-500">—</td>
                                <td class="p-4">Panduan Umum</td>
                                <td class="p-4 text-white font-bold">Simulasi Race Lengkap</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-medium text-white">Aksi Rekomendasi</td>
                                <td class="p-4">
                                    <a href="{{ route('programs.index', ['category' => '5k']) }}#katalog-program-coach" class="inline-block px-3 py-1.5 rounded-md bg-[#182338] hover:bg-[#1f2d48] border border-slate-800 text-white font-bold text-xs uppercase tracking-wide transition">
                                        Pilih 5K
                                    </a>
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('programs.index', ['category' => '10k']) }}#katalog-program-coach" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-[#CCFF00] hover:bg-[#b8e600] text-[#080A0D] font-black text-xs uppercase tracking-wide transition shadow-sm shadow-[#CCFF00]/20">
                                        <span>Pilih 10K</span>
                                        <svg class="w-3.5 h-3.5 text-[#080A0D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('programs.index', ['category' => '21k']) }}#katalog-program-coach" class="inline-block px-3 py-1.5 rounded-md bg-[#182338] hover:bg-[#1f2d48] border border-slate-800 text-white font-bold text-xs uppercase tracking-wide transition">
                                        Pilih 21K
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    <!-- ====================================================================
         SECTION 8.5: FAQ PROGRAM LARI (GOOGLE SEO & HELPFUL CONTENT)
         ==================================================================== -->
    <section id="faq-program-lari" class="py-16 sm:py-20 bg-[#0e1626] border-b border-slate-800 relative overflow-hidden">
        <!-- Subtle Track Oval Background Line -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-10" aria-hidden="true">
            <svg class="w-full h-full object-cover" viewBox="0 0 1200 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="600" cy="200" rx="600" ry="180" stroke="#CCFF00" stroke-width="1.5" stroke-dasharray="6 6"/>
                <ellipse cx="600" cy="200" rx="500" ry="140" stroke="rgba(255,255,255,0.05)" stroke-width="1.5"/>
            </svg>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold text-[#FF5A1F] uppercase tracking-wider block mb-2 font-numeric">
                    Pertanyaan Umum
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold font-editorial-heading text-white tracking-tight">
                    Tanya Jawab Seputar Program Lari
                </h2>
                <p class="text-slate-300 text-sm mt-2 leading-relaxed font-body">
                    Hal-hal penting yang sering ditanyakan seputar pemilihan <strong>program lari</strong>, metode latihan zona aerobik, serta pendampingan bersama coach.
                </p>
            </div>

            <!-- FAQ List using Semantic Accordion (Details / Summary) -->
            <div class="space-y-4">
                <!-- Q1 -->
                <details class="group athletic-card p-5 cursor-pointer open:bg-[#121c2e] transition-colors duration-150">
                    <summary class="flex items-center justify-between text-base font-bold text-white list-none font-editorial-heading select-none">
                        <span>Apa itu program lari terstruktur dan mengapa pelari membutuhkannya?</span>
                        <span class="w-7 h-7 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-slate-300 group-open:rotate-180 group-open:text-[#CCFF00] transition-transform duration-200 ml-3 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed font-body space-y-2">
                        <p>
                            <strong>Program lari</strong> terstruktur adalah panduan latihan periodik yang mengatur komposisi intensitas secara bertahap: easy run zona aerobik, interval speed, tempo run, dan long run.
                        </p>
                        <p>
                            Banyak pelari terjebak dalam perangkap beban berlebih karena selalu memaksakan pace kencang di setiap sesi lari, yang berujung pada cedera lutut atau kelelahan kronis. Program lari membantu Anda membangun kapasitas jantung dan ketahanan otot secara aman tanpa risiko cedera.
                        </p>
                    </div>
                </details>

                <!-- Q2 -->
                <details class="group athletic-card p-5 cursor-pointer open:bg-[#121c2e] transition-colors duration-150">
                    <summary class="flex items-center justify-between text-base font-bold text-white list-none font-editorial-heading select-none">
                        <span>Berapa lama durasi program lari untuk pemula (5K)?</span>
                        <span class="w-7 h-7 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-slate-300 group-open:rotate-180 group-open:text-[#CCFF00] transition-transform duration-200 ml-3 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed font-body space-y-2">
                        <p>
                            Program lari pemula (5K Foundation) biasanya berlangsung selama <strong>6 hingga 8 minggu</strong> dengan jadwal 3 sesi lari per minggu.
                        </p>
                        <p>
                            Pada minggu-minggu awal, latihan menggunakan kombinasi lari-jalan (metode run-walk) agar sistem kardiovaskular dan persendian beradaptasi sebelum bertransisi menuju lari 5 km non-stop tanpa jalan kaki.
                        </p>
                    </div>
                </details>

                <!-- Q3 -->
                <details class="group athletic-card p-5 cursor-pointer open:bg-[#121c2e] transition-colors duration-150">
                    <summary class="flex items-center justify-between text-base font-bold text-white list-none font-editorial-heading select-none">
                        <span>Bagaimana cara memilih program lari antara 5K, 10K, atau Half Marathon?</span>
                        <span class="w-7 h-7 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-slate-300 group-open:rotate-180 group-open:text-[#CCFF00] transition-transform duration-200 ml-3 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed font-body space-y-2">
                        <p>
                            Pilihlah program berdasarkan jarak tempuh mingguan saat ini:
                        </p>
                        <ul class="space-y-1.5 text-slate-300">
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Program Lari 5K:</strong> Jika Anda baru mulai berlari atau belum mampu berlari 20 menit terus-menerus.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Program Lari 10K:</strong> Jika Anda sudah mampu berlari 5K non-stop dan ingin menaikkan ambang batas laktat serta memangkas waktu (target sub-60 atau sub-50 menit).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#FF5A1F]">•</span>
                                <span><strong>Program Lari Half Marathon (21.1K):</strong> Jika Anda memiliki target finis race 21K dalam 12–16 minggu ke depan dengan fondasi lari mingguan minimal 15–20 KM.</span>
                            </li>
                        </ul>
                    </div>
                </details>

                <!-- Q4 -->
                <details class="group athletic-card p-5 cursor-pointer open:bg-[#121c2e] transition-colors duration-150">
                    <summary class="flex items-center justify-between text-base font-bold text-white list-none font-editorial-heading select-none">
                        <span>Apakah program lari ini aman untuk pelari dengan riwayat cedera?</span>
                        <span class="w-7 h-7 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-slate-300 group-open:rotate-180 group-open:text-[#CCFF00] transition-transform duration-200 ml-3 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed font-body space-y-2">
                        <p>
                            Ya, sangat aman. Semua program lari Ruang Lari menerapkan <strong>aturan kenaikan maksimal 10% per minggu</strong> dan alokasi 70-80% sesi pada zona aerobik ringan (Zone 2).
                        </p>
                        <p>
                            Coach juga memberikan menu mobilitas panggul, penguatan betis, dan latihan core untuk melindungi sendi dari benturan berulang.
                        </p>
                    </div>
                </details>

                <!-- Q5 -->
                <details class="group athletic-card p-5 cursor-pointer open:bg-[#121c2e] transition-colors duration-150">
                    <summary class="flex items-center justify-between text-base font-bold text-white list-none font-editorial-heading select-none">
                        <span>Berapa hari dalam seminggu jadwal latihan lari dijalankan?</span>
                        <span class="w-7 h-7 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-slate-300 group-open:rotate-180 group-open:text-[#CCFF00] transition-transform duration-200 ml-3 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed font-body space-y-2">
                        <p>
                            Jadwal bervariasi antara <strong>3 hingga 4 hari sesi lari aktif per minggu</strong>, diselingi 1 hari latihan kekuatan ringan, dan minimal 2 hari istirahat penuh.
                        </p>
                        <p>
                            Struktur jadwal dirancang fleksibel sehingga tetap dapat disesuaikan dengan kesibukan kerja atau rutinitas keluarga Anda.
                        </p>
                    </div>
                </details>

                <!-- Q6 -->
                <details class="group athletic-card p-5 cursor-pointer open:bg-[#121c2e] transition-colors duration-150">
                    <summary class="flex items-center justify-between text-base font-bold text-white list-none font-editorial-heading select-none">
                        <span>Apakah saya wajib memiliki jam tangan GPS pintar?</span>
                        <span class="w-7 h-7 rounded bg-[#182338] border border-slate-800 flex items-center justify-center text-slate-300 group-open:rotate-180 group-open:text-[#CCFF00] transition-transform duration-200 ml-3 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed font-body space-y-2">
                        <p>
                            Tidak wajib. Anda dapat memulai program lari hanya dengan smartphone dan aplikasi lari gratis (seperti Strava).
                        </p>
                        <p>
                            Namun jika Anda memiliki jam tangan dengan sensor detak jantung, Anda akan mendapatkan manfaat lebih optimal dalam memantau zona aerobik dan cadence lari yang direkomendasikan coach.
                        </p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         SECTION 9: FINAL CTA (HERO BAWAH)
         ==================================================================== -->
    <section class="py-20 sm:py-28 bg-[#0b111e] text-center relative overflow-hidden border-t border-slate-800">
        
        <!-- Athletic Stadium Track Oval Curves Graphic -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-25" aria-hidden="true">
            <svg class="w-full h-full object-cover min-w-[1000px]" viewBox="0 0 1200 450" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Stadium Track Curves -->
                <path d="M-100 240 C 250 80, 600 80, 950 240 C 1100 310, 1250 310, 1350 240" stroke="#CCFF00" stroke-width="2" stroke-dasharray="10 14"/>
                <path d="M-100 280 C 250 120, 600 120, 950 280 C 1100 350, 1250 350, 1350 280" stroke="rgba(255,255,255,0.06)" stroke-width="1.5"/>
                <path d="M-100 320 C 250 160, 600 160, 950 320 C 1100 390, 1250 390, 1350 320" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                <!-- Finish Line Chequered Motif (Subtle Minimalist) -->
                <g opacity="0.35">
                    <rect x="570" y="35" width="14" height="14" fill="#CCFF00" />
                    <rect x="598" y="35" width="14" height="14" fill="#CCFF00" />
                    <rect x="584" y="49" width="14" height="14" fill="#CCFF00" />
                    <rect x="612" y="49" width="14" height="14" fill="#CCFF00" />
                    <rect x="570" y="63" width="14" height="14" fill="#CCFF00" />
                    <rect x="598" y="63" width="14" height="14" fill="#CCFF00" />
                </g>
            </svg>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
            <span class="text-xs font-bold text-[#CCFF00] uppercase tracking-wider block font-numeric">
                Mulai Latihan Hari Ini
            </span>

            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-editorial-heading text-white leading-tight">
                Siap Memulai Program Lari Pertamamu?<br>
                <span class="text-[#CCFF00]">Capai Garis Finish dengan Percaya Diri.</span>
            </h2>

            <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto font-body">
                Langkah pertama menuju personal best dimulai dari <strong>program lari</strong> yang teruji secara sports science. Pilih jadwal latihan lari yang sesuai dengan targetmu sekarang dan capai performa lari optimal bersama coach Ruang Lari.
            </p>

            <!-- Hero Bawah Action Buttons: Default Neon Green, Hover Dark BG & White Text -->
            <div class="pt-4 flex flex-wrap items-center justify-center gap-4 font-numeric">
                <a href="#katalog-program-coach" 
                   class="btn-volt-hero group px-8 py-4 rounded-md font-black text-sm tracking-wider uppercase flex items-center gap-2 cursor-pointer">
                    <span>Mulai Program Lari</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="{{ route('calculator') }}" 
                   class="btn-volt-hero group px-6 py-4 rounded-md font-black text-sm tracking-wide uppercase flex items-center gap-2 cursor-pointer">
                    <span>Hitung Pace VDOT Gratis</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<!-- Structured Data (JSON-LD) for Google SEO Rich Snippets -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": "Beranda",
          "item": "{{ url('/') }}"
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": "Program Lari",
          "item": "{{ route('programs.index') }}"
        }
      ]
    },
    {
      "@@type": "CollectionPage",
      "@@id": "{{ route('programs.index') }}#webpage",
      "url": "{{ route('programs.index') }}",
      "name": "Program Lari Terstruktur: 5K, 10K & Half Marathon",
      "description": "Temukan program lari terstruktur dari coach berlisensi untuk pemula hingga pelari maraton. Dilengkapi jadwal latihan lari, kalkulator pace VDOT, dan panduan nutrisi.",
      "inLanguage": "id-ID"
    },
    {
      "@@type": "FAQPage",
      "mainEntity": [
        {
          "@@type": "Question",
          "name": "Apa itu program lari terstruktur dan mengapa pelari membutuhkannya?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Program lari terstruktur adalah panduan latihan periodik yang mengatur komposisi intensitas secara bertahap: easy run zona aerobik, interval speed, tempo run, dan long run untuk membangun ketahanan tanpa risiko cedera."
          }
        },
        {
          "@@type": "Question",
          "name": "Berapa lama durasi program lari untuk pemula (5K)?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Program lari 5K untuk pemula di Ruang Lari berlangsung 6 hingga 8 minggu dengan 3 sesi per minggu menggunakan metode kombinasi lari-jalan (run-walk method)."
          }
        },
        {
          "@@type": "Question",
          "name": "Bagaimana cara memilih program lari antara 5K, 10K, atau Half Marathon?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Pilih program 5K jika baru mulai lari, pilih program 10K jika sudah bisa lari 5K non-stop untuk meningkatkan kecepatan dan ambang laktat, dan pilih program Half Marathon jika menargetkan lomba 21.1K dalam 12-16 minggu."
          }
        },
        {
          "@@type": "Question",
          "name": "Apakah program lari ini aman untuk pelari dengan riwayat cedera?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Sangat aman. Setiap program mematuhi aturan kenaikan volume maksimal 10% per minggu dan alokasi 70-80% sesi pada zona aerobik ringan (Zone 2)."
          }
        },
        {
          "@@type": "Question",
          "name": "Berapa hari dalam seminggu jadwal latihan lari dijalankan?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Jadwal latihan dijalankan 3 hingga 4 hari sesi lari aktif per minggu diselingi 1 hari latihan kekuatan dan minimal 2 hari istirahat penuh."
          }
        },
        {
          "@@type": "Question",
          "name": "Apakah saya wajib memiliki jam tangan GPS pintar?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Tidak wajib. Anda dapat memulai program lari hanya dengan smartphone dan aplikasi lari gratis seperti Strava."
          }
        }
      ]
    }
  ]
}
</script>

<script>
    // Catalog Filter Helpers (Pure Vanilla JS)
    function setCatalogCategory(cat) {
        var input = document.getElementById('catalog-category-input');
        if (input) {
            input.value = cat;
        }
        var form = document.getElementById('catalog-filter-form');
        if (form) {
            form.submit();
        }
    }

    function clearCatalogSearch() {
        var input = document.getElementById('catalog-search-input');
        if (input) {
            input.value = '';
        }
        var form = document.getElementById('catalog-filter-form');
        if (form) {
            form.submit();
        }
    }

    // Pure Vanilla JS for Journey Map Tab Switcher (Zero external library dependencies)
    function setRunningLevel(level) {
        // Reset all buttons
        document.querySelectorAll('.journey-step-btn').forEach(function(btn) {
            btn.classList.remove('is-active', 'ring-1', 'ring-[#FF5A1F]');
            var tag = btn.querySelector('.level-tag');
            if (tag) {
                tag.classList.remove('bg-[#FF5A1F]', 'text-white');
                tag.classList.add('bg-[#182338]', 'text-slate-400');
            }
        });

        // Hide all detail panes
        document.querySelectorAll('.level-detail-pane').forEach(function(pane) {
            pane.classList.add('hidden');
        });

        // Activate selected button & pane
        var activeBtn = document.getElementById('level-btn-' + level);
        var activePane = document.getElementById('level-pane-' + level);

        if (activeBtn) {
            activeBtn.classList.add('is-active', 'ring-1', 'ring-[#FF5A1F]');
            var activeTag = activeBtn.querySelector('.level-tag');
            if (activeTag) {
                activeTag.classList.remove('bg-[#182338]', 'text-slate-400');
                activeTag.classList.add('bg-[#FF5A1F]', 'text-white');
            }
        }

        if (activePane) {
            activePane.classList.remove('hidden');
        }
    }
</script>
<!-- Blade View Compiled Fresh -->
@endpush
