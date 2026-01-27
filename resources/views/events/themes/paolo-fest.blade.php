<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <script>
        // Nominatim CORS Proxy Interceptor (Fetch & XHR)
        (function() {
            // 1. Fetch Interceptor
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    var proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
                    return originalFetch(proxyUrl, options);
                }
                return originalFetch(url, options);
            };

            // 2. XHR Interceptor
            var originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    url = '/image-proxy?url=' + encodeURIComponent(url);
                }
                return originalOpen.apply(this, arguments);
            };
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $event->name }} - Official Event</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if(env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <meta name="description" content="{{ strip_tags($event->short_description ?? $event->name) }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com'); @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#1e40af', /* Royal Blue */
                            700: '#1d4ed8',
                            800: '#1e3a8a',
                            900: '#172554',
                        },
                        accent: {
                            500: '#f97316', /* Orange */
                            600: '#ea580c',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                        'glow': '0 0 20px rgba(59, 130, 246, 0.5)',
                        'card': '0 0 0 1px rgba(0,0,0,0.03), 0 4px 20px rgba(0,0,0,0.08)',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Styles */
        body { background-color: #F8FAFC; color: #1e293b; overflow-x: hidden; }
        
        /* Shape Divider */
        .custom-shape-divider-bottom {
            position: absolute;
            bottom: -1px; /* Fix gap */
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            transform: rotate(180deg);
            z-index: 2;
        }
        .custom-shape-divider-bottom svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 120px;
        }
        .custom-shape-divider-bottom .shape-fill {
            fill: #FFFFFF;
        }

        /* Reveal Animation */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.5, 0, 0, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        
        /* Sticky Navigation */
        .nav-scrolled { background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.1); }
        .nav-scrolled .nav-link { color: #475569 !important; }
        .nav-scrolled .nav-link:hover { color: #1e40af !important; }
        .nav-scrolled .nav-brand { color: #0f172a !important; }
        .nav-scrolled .mobile-toggle { color: #1e293b !important; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen">

    @php
        $paymentConfig = $event->payment_config ?? [];
                
                // Support both new (allowed_methods) and legacy (direct keys) structures
                if (isset($paymentConfig['allowed_methods']) && is_array($paymentConfig['allowed_methods'])) {
                    $allowed = $paymentConfig['allowed_methods'];
                    $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
                    $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
                } else {
                    $showMidtrans = $paymentConfig['midtrans'] ?? true;
                    $showMoota = $paymentConfig['moota'] ?? false;
                }

                if (!$showMidtrans && !$showMoota) {
                    $showMidtrans = true;
                }

        $pa = $event->premium_amenities ?? null;
        $hasPa = !is_null($pa);
        $showSection = function($key) use ($pa, $hasPa) {
            if (!$hasPa) return true;
            return isset($pa[$key]['enabled']) && $pa[$key]['enabled'];
        };

        $now = now();
        $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at) && !($event->registration_close_at && $now > $event->registration_close_at);

        $countdownTarget = $event->start_at;
        $countdownLabel = 'Event Dimulai Dalam';

        if ($event->registration_open_at && $now < $event->registration_open_at) {
            $countdownTarget = $event->registration_open_at;
            $countdownLabel = 'Pendaftaran Dibuka Dalam';
        } elseif ($isRegOpen && $event->registration_close_at) {
            $countdownTarget = $event->registration_close_at;
            $countdownLabel = 'Pendaftaran Ditutup Dalam';
        }
    @endphp

    <nav class="fixed w-full z-50 transition-all duration-300 bg-white/0 border-b border-transparent" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="#top" class="flex items-center gap-3 group">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-10 w-auto group-hover:scale-105 transition duration-300">
                    @else
                        <div class="h-10 w-10 bg-brand-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-brand-600/30 group-hover:rotate-6 transition duration-300">
                            {{ substr($event->name, 0, 1) }}
                        </div>
                        <span class="nav-brand font-extrabold text-xl tracking-tight text-white uppercase group-hover:text-brand-600 transition">{{ $event->name }}</span>
                    @endif
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#about" class="nav-link text-sm font-semibold text-white/90 hover:text-white transition">Tentang</a>
                    <a href="#categories" class="nav-link text-sm font-semibold text-white/90 hover:text-white transition">Kategori</a>
                    <a href="#venue" class="nav-link text-sm font-semibold text-white/90 hover:text-white transition">Lokasi</a>
                    <a href="#racepack" class="nav-link text-sm font-semibold text-white/90 hover:text-white transition">Race Pack</a>
                    <a href="#info" class="nav-link text-sm font-semibold text-white/90 hover:text-white transition">Info</a>
                    
                    @if($isRegOpen)
                        <a href="#register" class="bg-brand-600 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-brand-600/20 hover:bg-brand-700 hover:shadow-brand-600/30 hover:-translate-y-0.5 transition-all duration-300">
                            Daftar Sekarang
                        </a>
                    @else
                        <span class="bg-slate-100 text-slate-400 px-6 py-2.5 rounded-full text-sm font-bold cursor-not-allowed border border-slate-200">
                            Closed
                        </span>
                    @endif
                </div>

                <button id="mobileMenuBtn" class="mobile-toggle md:hidden text-white p-2 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-slate-100 absolute inset-x-0 left-0 px-4 py-4 space-y-4 shadow-xl rounded-b-2xl">
                <a href="#about" class="block text-slate-700 font-medium p-2 hover:bg-slate-50 rounded-lg">Tentang</a>
                <a href="#categories" class="block text-slate-700 font-medium p-2 hover:bg-slate-50 rounded-lg">Kategori</a>
                <a href="#venue" class="block text-slate-700 font-medium p-2 hover:bg-slate-50 rounded-lg">Lokasi</a>
                <a href="#racepack" class="block text-slate-700 font-medium p-2 hover:bg-slate-50 rounded-lg">Race Pack</a>
                <a href="#info" class="block text-slate-700 font-medium p-2 hover:bg-slate-50 rounded-lg">Info Event</a>
                <a href="#register" class="block text-center bg-brand-600 text-white font-bold p-3 rounded-xl">Registrasi</a>
            </div>
        </div>
    </nav>

    <header id="top" class="relative pt-24 pb-12 overflow-hidden bg-slate-900 min-h-[85vh] flex items-center">
        <!-- Dynamic Background -->
        <div class="absolute inset-0 z-0">
            @if($event->hero_image)
                <img src="{{ asset('storage/' . $event->hero_image) }}" class="w-full h-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/40 to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-slate-900"></div>
                <div class="absolute top-0 -left-4 w-72 h-72 bg-brand-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 bg-accent-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            @endif
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="flex flex-col md:flex-row items-center gap-12 lg:gap-20">
                <div class="flex-1 text-center md:text-left reveal active">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md shadow-lg mb-8 hover:bg-white/20 transition cursor-default">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-accent-500"></span>
                        </span>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">{{ $event->start_at->format('d F Y') }} • {{ $event->location_name }}</span>
                    </div>
                    
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white tracking-tight leading-none mb-6 drop-shadow-lg">
                        {{ strtoupper($event->name) }}
                    </h1>
                    <div class="text-white mb-10">
                        <p class="text-lg md:text-xl text-slate-300 mb-10 leading-relaxed max-w-lg mx-auto md:mx-0 font-light">
                            {!! $event->short_description ?? 'Rasakan sensasi berlari dengan atmosfer kompetitif yang menyenangkan.' !!}
                        </p>
                    </div>                    

                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start mb-12">
                        @if($isRegOpen)
                        <a href="#register" class="group px-8 py-4 bg-brand-600 text-white font-bold rounded-2xl shadow-lg shadow-brand-600/30 hover:bg-brand-500 hover:shadow-brand-500/50 transition-all duration-300 hover:-translate-y-1 flex items-center justify-center gap-2">
                            Amankan Slot
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @else
                        <button disabled class="px-8 py-4 bg-slate-700 text-slate-400 font-bold rounded-2xl cursor-not-allowed border border-slate-600">
                            Pendaftaran Ditutup
                        </button>
                        @endif
                        <a href="#about" class="px-8 py-4 bg-white/10 text-white border border-white/20 backdrop-blur-sm font-bold rounded-2xl hover:bg-white/20 transition flex items-center justify-center">
                            Explore Event
                        </a>
                    </div>

                    <!-- Countdown -->
                    <div class="flex flex-col items-center md:items-start">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">{{ $countdownLabel }}</span>
                        <div id="hero-countdown" class="flex gap-4">
                            <div class="text-center">
                                <div class="bg-white/10 backdrop-blur-md border border-white/20 w-16 h-16 rounded-xl flex items-center justify-center mb-1">
                                    <span class="text-2xl font-bold text-white" id="cd-days">00</span>
                                </div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Hari</span>
                            </div>
                            <div class="text-center">
                                <div class="bg-white/10 backdrop-blur-md border border-white/20 w-16 h-16 rounded-xl flex items-center justify-center mb-1">
                                    <span class="text-2xl font-bold text-white" id="cd-hours">00</span>
                                </div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Jam</span>
                            </div>
                            <div class="text-center">
                                <div class="bg-white/10 backdrop-blur-md border border-white/20 w-16 h-16 rounded-xl flex items-center justify-center mb-1">
                                    <span class="text-2xl font-bold text-white" id="cd-minutes">00</span>
                                </div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Menit</span>
                            </div>
                            <div class="text-center">
                                <div class="bg-white/10 backdrop-blur-md border border-white/20 w-16 h-16 rounded-xl flex items-center justify-center mb-1">
                                    <span class="text-2xl font-bold text-white" id="cd-seconds">00</span>
                                </div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Detik</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Info Card -->
                <div class="flex-1 w-full max-w-md mx-auto reveal delay-200 hidden lg:block">
                    <div class="bg-white/10 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/20 shadow-2xl animate-float">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <p class="text-xs text-brand-300 font-bold uppercase tracking-wider mb-1">EVENT STATUS</p>
                                <p class="text-3xl font-black text-white">READY TO RACE</p>
                            </div>
                            <div class="w-14 h-14 bg-brand-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-brand-500/40">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-slate-900/50 p-5 rounded-2xl border border-white/5 flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-accent-500/20 flex items-center justify-center text-accent-500">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-white font-bold">{{ $event->location_name }}</span>
                                    <span class="text-xs text-slate-400">Venue Location</span>
                                </div>
                            </div>
                            <div class="bg-slate-900/50 p-5 rounded-2xl border border-white/5 flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-white font-bold">{{ $event->start_at->format('H:i') }} WIB</span>
                                    <span class="text-xs text-slate-400">Flag Off Time</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-8 border-t border-white/10 grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="block text-2xl font-bold text-white">{{ $categories->count() }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Kategori</span>
                            </div>
                            <div>
                                <span class="block text-2xl font-bold text-white">1000+</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Runners</span>
                            </div>
                            <div>
                                <span class="block text-2xl font-bold text-white">5+</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Sponsor</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="custom-shape-divider-bottom hidden md:block">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
    </header>

    <!-- About Section -->
    <section id="about" class="py-24 bg-white relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal">
                    <span class="text-brand-600 font-bold uppercase tracking-widest text-sm mb-2 block">Tentang Event</span>
                    <h2 class="text-4xl font-extrabold text-slate-900 mb-6 leading-tight">Berlari Melampaui Batas,<br>Nikmati Setiap Langkah</h2>
                    <div class="prose prose-lg text-slate-600">
                        {!! $event->full_description ?? $event->short_description !!}
                    </div>
                    
                    <div class="mt-8 grid grid-cols-2 gap-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">Rute Steril</h4>
                                <p class="text-sm text-slate-500 mt-1">Keamanan pelari adalah prioritas utama kami dengan rute bebas kendaraan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">Refreshment</h4>
                                <p class="text-sm text-slate-500 mt-1">Water station tiap 2.5KM dengan air mineral dan isotonik.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative reveal delay-200">
                    <div class="absolute -top-10 -right-10 w-64 h-64 bg-brand-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-accent-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl rotate-2 hover:rotate-0 transition duration-500">
                        @php
                            // Logic: Use First Gallery Image, fallback to Hero Image
                            $aboutImgSrc = null;
                            if (isset($event->gallery) && is_array($event->gallery) && count($event->gallery) > 0) {
                                $aboutImgSrc = $event->gallery[0];
                            } elseif ($event->hero_image) {
                                $aboutImgSrc = $event->hero_image;
                            }
                        @endphp
                        
                        @if($aboutImgSrc)
                            <img src="{{ asset('storage/' . $aboutImgSrc) }}" class="w-full h-auto object-cover">
                        @else
                            <div class="bg-slate-200 w-full aspect-[4/3] flex items-center justify-center text-slate-400 font-bold">Event Image</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section (New) -->
    @if(isset($event->gallery) && count($event->gallery) > 0)
    <section id="gallery" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Dokumentasi</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-2">Event Gallery</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 reveal">
                @foreach(array_slice($event->gallery, 0, 8) as $index => $img)
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-md cursor-zoom-in" onclick="openLightbox({{ $index }})">
                    <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition duration-300"></div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                        <svg class="w-10 h-10 text-white drop-shadow-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Categories Section -->
    <section id="categories" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Kategori Lomba</span>
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl mt-2">Pilih Tantanganmu</h2>
                <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">Tersedia berbagai kategori jarak yang sesuai dengan target latihanmu.</p>
            </div>

            @php
                $catCount = $categories->count();
                $gridClass = match($catCount) {
                    1 => 'lg:grid-cols-1 max-w-xl mx-auto',
                    2 => 'lg:grid-cols-2 max-w-5xl mx-auto',
                    default => 'lg:grid-cols-3'
                };
            @endphp

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 {{ $gridClass }}">
                @foreach($categories as $cat)
                @php
                    $priceRegular = (int) ($cat->price_regular ?? 0);
                    $priceEarly = (int) ($cat->price_early ?? 0);
                    $priceLate = (int) ($cat->price_late ?? 0);
                    $displayPrice = $priceRegular;
                    if ($priceEarly > 0) {
                        $displayPrice = $priceEarly;
                    } elseif ($priceLate > 0) {
                        $displayPrice = $priceLate;
                    }
                @endphp
                <div class="relative flex flex-col bg-white rounded-[2rem] border border-slate-100 shadow-card hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group reveal overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-brand-500 to-accent-500"></div>
                    
                    <div class="p-8 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-lg font-black bg-slate-50 text-slate-900 border border-slate-100">
                                {{ $cat->distance_km ?? '?' }}K
                            </span>
                            @if($cat->quota < 50 && $cat->quota > 0)
                                <span class="px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-bold animate-pulse">
                                    🔥 Sisa Sedikit
                                </span>
                            @endif
                        </div>

                        <h3 class="text-3xl font-bold text-slate-900 mb-2">{{ $cat->name }}</h3>
                        <p class="text-slate-500 text-sm mb-8 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Cut Off Time: <strong class="text-slate-900">{{ $cat->cot_hours ?? '-' }} Jam</strong>
                        </p>

                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center text-sm font-medium text-slate-700 bg-slate-50 p-3 rounded-xl">
                                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Official Jersey & BIB
                            </li>
                            <li class="flex items-center text-sm font-medium text-slate-700 bg-slate-50 p-3 rounded-xl">
                                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Finisher Medal
                            </li>
                            <li class="flex items-center text-sm font-medium text-slate-700 bg-slate-50 p-3 rounded-xl">
                                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Refreshment & Medic
                            </li>
                        </ul>
                    </div>
                    
                    <div class="p-6 bg-slate-50 border-t border-slate-100">
                        <div class="flex items-baseline justify-between mb-4">
                            <span class="text-sm text-slate-500 font-bold uppercase">Registrasi</span>
                            <div class="text-right">
                                @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                    <div class="text-xs font-bold text-slate-400 line-through">Rp {{ number_format($priceRegular/1000, 0) }}k</div>
                                @endif
                                <div class="text-3xl font-black text-brand-600">Rp {{ number_format($displayPrice/1000, 0) }}k</div>
                            </div>
                        </div>
                        <a href="#register" class="block w-full py-4 px-4 bg-brand-600 text-white font-bold text-center rounded-xl shadow-lg shadow-brand-600/20 hover:bg-brand-700 hover:shadow-brand-600/40 transition-all duration-300">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Route Map Section (New) -->
    @php
        $categoriesWithGpx = $event->categories->filter(function($cat) {
            return $cat->master_gpx_id && $cat->masterGpx;
        });
    @endphp

    @if($categoriesWithGpx->count() > 0)
    <section id="routes" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Race Course</span>
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl mt-2">Peta Rute</h2>
                <p class="text-slate-500 mt-4 max-w-2xl mx-auto">Jelajahi rute lari untuk setiap kategori. Klik tab kategori untuk melihat detail rute dan elevasi.</p>
            </div>

            <div x-data="{ activeTab: '{{ $categoriesWithGpx->first()->id }}' }">
                <!-- Tabs -->
                <div class="flex flex-wrap justify-center gap-4 mb-8">
                    @foreach($categoriesWithGpx as $category)
                        <button 
                            @click="activeTab = '{{ $category->id }}'; setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 100);" 
                            :class="activeTab === '{{ $category->id }}' ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-6 py-3 rounded-full font-bold transition-all text-sm uppercase tracking-wide">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <!-- Maps -->
                <div class="relative bg-slate-50 rounded-3xl overflow-hidden border border-slate-200 shadow-inner h-[500px]">
                    @foreach($categoriesWithGpx as $category)
                        <div x-show="activeTab === '{{ $category->id }}'" 
                                class="absolute inset-0 w-full h-full"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100">
                            <div id="map-{{ $category->id }}" class="w-full h-full z-10"></div>
                            
                            <!-- Stats Overlay -->
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-lg border border-slate-100 z-20 max-w-xs">
                                <h4 class="font-bold text-slate-900 mb-2">{{ $category->name }}</h4>
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div>
                                        <span class="block text-slate-400">Distance</span>
                                        <span class="block font-bold text-slate-800 text-lg">{{ $category->masterGpx->distance_km }} KM</span>
                                    </div>
                                    <div>
                                        <span class="block text-slate-400">Elev. Gain</span>
                                        <span class="block font-bold text-slate-800 text-lg">{{ $category->masterGpx->elevation_gain_m }} m</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Medal Section (New) -->
    @if($event->medal_image)
    <section id="medal" class="py-24 bg-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-16">
                <div class="flex-1 text-center md:text-left reveal">
                    <span class="text-accent-500 font-bold uppercase tracking-widest text-sm mb-2 block">Finisher Reward</span>
                    <h2 class="text-4xl md:text-5xl font-black text-white mb-6">THE MEDAL</h2>
                    <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                        Simbol pencapaian Anda menaklukkan rute ini. Didesain eksklusif dengan material Zinc Alloy 3D berkualitas tinggi, berat, dan solid.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-white/10 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                            <span class="block text-2xl font-bold text-white">3D</span>
                            <span class="text-xs text-slate-400 uppercase">Design</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                            <span class="block text-2xl font-bold text-white">High</span>
                            <span class="text-xs text-slate-400 uppercase">Quality</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 reveal delay-200">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-accent-500 rounded-full blur-3xl opacity-20 group-hover:opacity-40 transition duration-500"></div>
                        <img src="{{ asset('storage/' . $event->medal_image) }}" class="relative w-full max-w-md mx-auto drop-shadow-2xl transform group-hover:scale-105 transition duration-500">
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Race Pack & Facilities -->
    <section id="racepack" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Race Entitlements</span>
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl mt-2">Fasilitas & Race Pack</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
                <!-- Jersey Card -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-100 overflow-hidden relative group reveal">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-brand-50 rounded-full -mr-16 -mt-16 transition group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-brand-100 rounded-2xl flex items-center justify-center text-brand-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-900">Official Jersey</h3>
                        </div>
                        <p class="text-slate-600 mb-8">Jersey lari eksklusif dengan bahan Dry-Fit premium yang ringan dan menyerap keringat.</p>
                        <div class="rounded-3xl overflow-hidden bg-slate-100 border border-slate-200">
                             @if($event->jersey_image)
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" class="w-full h-auto object-cover hover:scale-105 transition duration-700">
                             @else
                                <div class="aspect-video flex items-center justify-center text-slate-400 font-bold">Preview Jersey</div>
                             @endif
                        </div>
                    </div>
                </div>

                <!-- Facilities Grid -->
                <div class="grid grid-cols-1 gap-6">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-start gap-6 hover:shadow-md transition reveal delay-100">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Timing System</h4>
                            <p class="text-slate-500">Pencatatan waktu akurat menggunakan chip timing system berstandar nasional.</p>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-start gap-6 hover:shadow-md transition reveal delay-200">
                        <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Water Station</h4>
                            <p class="text-slate-500">Pos hidrasi tersedia setiap 2.5 KM dilengkapi tim medis.</p>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-start gap-6 hover:shadow-md transition reveal delay-300">
                        <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Race Photography</h4>
                            <p class="text-slate-500">Dokumentasi foto berkualitas tinggi di sepanjang rute untuk Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Section (RPC & Venue) -->
    <section id="info" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- RPC Info -->
                <div class="reveal">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-slate-900 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900">Race Pack Collection</h3>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100">
                        <p class="text-slate-600 mb-6">Pengambilan race pack akan dilakukan pada:</p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 text-brand-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <div>
                                    <strong class="block text-slate-900">Tanggal</strong>
                                    <span class="text-slate-600">H-2 & H-1 Sebelum Race Day</span>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 text-brand-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <strong class="block text-slate-900">Waktu</strong>
                                    <span class="text-slate-600">10:00 - 20:00 WIB</span>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 text-brand-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <div>
                                    <strong class="block text-slate-900">Lokasi</strong>
                                    <span class="text-slate-600">{{ $event->rpc_location_name ?? 'To Be Announced' }}</span>
                                    <p class="text-sm text-slate-500 mt-1">{{ $event->rpc_location_address }}</p>
                                </div>
                            </li>
                        </ul>
                        @if($event->rpc_latitude && $event->rpc_longitude)
                        <a href="https://maps.google.com/?q={{ $event->rpc_latitude }},{{ $event->rpc_longitude }}" target="_blank" class="inline-flex items-center text-sm font-bold text-brand-600 hover:text-brand-700">
                            Buka Google Maps
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Venue Info -->
                <div class="reveal delay-200" id="venue">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-accent-500 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900">Venue & Parking</h3>
                    </div>
                    <div class="bg-white border border-slate-200 shadow-lg rounded-3xl overflow-hidden h-full min-h-[300px] relative group">
                         @if($event->map_embed_url)
                            <iframe src="{{ $event->map_embed_url }}" class="w-full h-full min-h-[300px] border-0" allowfullscreen="" loading="lazy"></iframe>
                         @elseif($event->location_lat && $event->location_lng)
                            <iframe src="https://maps.google.com/maps?q={{ $event->location_lat }},{{ $event->location_lng }}&hl=id&z=14&output=embed" class="w-full h-full min-h-[300px] border-0" allowfullscreen="" loading="lazy"></iframe>
                         @else
                            <div class="absolute inset-0 bg-slate-100 flex items-center justify-center text-slate-400 font-bold">Map Loading...</div>
                         @endif
                         <div class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-sm p-4 rounded-xl border border-white/50 shadow-sm flex justify-between items-center gap-4">
                             <div class="min-w-0">
                                <strong class="block text-slate-900 truncate">{{ $event->location_name }}</strong>
                                <p class="text-xs text-slate-500 truncate">{{ $event->location_address }}</p>
                             </div>
                             @if($event->location_lat && $event->location_lng)
                             <a href="https://www.google.com/maps/dir/?api=1&destination={{ $event->location_lat }},{{ $event->location_lng }}" target="_blank" class="shrink-0 bg-brand-600 text-white p-2.5 rounded-lg hover:bg-brand-700 transition shadow-lg shadow-brand-600/20" title="Get Directions">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                             </a>
                             @endif
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What to Bring & FAQ -->
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                <!-- What to Bring -->
                <div class="reveal">
                    <h3 class="text-2xl font-bold text-slate-900 mb-8">What To Bring</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">Running Shoes</span>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">Jersey & BIB</span>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">ID Card</span>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">Sunscreen</span>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="reveal delay-200">
                    <h3 class="text-2xl font-bold text-slate-900 mb-8">FAQ</h3>
                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                            <button class="w-full px-6 py-4 text-left font-bold text-slate-900 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                Apakah tiket bisa di-refund?
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="px-6 pb-4 text-sm text-slate-600 hidden">
                                Tiket yang sudah dibeli tidak dapat dikembalikan (non-refundable), namun dapat dipindahtangankan sesuai syarat dan ketentuan yang berlaku.
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                            <button class="w-full px-6 py-4 text-left font-bold text-slate-900 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                Kapan batas akhir pendaftaran?
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="px-6 pb-4 text-sm text-slate-600 hidden">
                                Pendaftaran ditutup pada {{ $event->registration_close_at ? $event->registration_close_at->format('d F Y') : 'H-7 sebelum acara' }} atau saat kuota terpenuhi.
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                            <button class="w-full px-6 py-4 text-left font-bold text-slate-900 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                Apakah ada penitipan barang?
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="px-6 pb-4 text-sm text-slate-600 hidden">
                                Ya, kami menyediakan area deposit bag (penitipan tas) di area Race Village bagi peserta.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('events.partials.prizes-section', ['categories' => $categories])

    <!-- Participants Table Section -->
    @if($event->participants()->exists())
    <section id="participants-list" class="py-24 bg-slate-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white">Daftar Peserta</h2>
                <p class="mt-4 text-slate-400">Cek siapa saja yang sudah bergabung.</p>
            </div>
            
            <!-- Vue App Container -->
            <div id="vue-participants-app">
                @include('events.partials.participants-table')
            </div>
        </div>
    </section>
    @endif

    <section id="register" class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Registrasi Peserta</h2>
                <p class="mt-4 text-slate-600">Pastikan data yang Anda masukkan sesuai dengan identitas (KTP/SIM).</p>
            </div>

            @if(!$isRegOpen)
                <div class="bg-slate-50 border-2 border-slate-200 rounded-3xl p-12 text-center max-w-2xl mx-auto">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Pendaftaran Ditutup</h3>
                    <p class="text-slate-500">Mohon maaf, pendaftaran saat ini tidak tersedia.</p>
                </div>
            @else
                <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="flex flex-col lg:flex-row gap-8 reveal">
                    @csrf
                    
                    <div class="flex-1 space-y-8">
                        
                        <div class="bg-white border border-slate-200 shadow-card rounded-3xl p-8">
                            <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">1</span>
                                Data Penanggung Jawab
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                                    <input type="text" name="pic_name" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 focus:border-transparent outline-none transition" placeholder="Sesuai KTP" required>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                                        <input type="email" name="pic_email" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 focus:border-transparent outline-none transition" placeholder="email@contoh.com" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                                        <input type="text" name="pic_phone" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 focus:border-transparent outline-none transition" placeholder="0812xxxx" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 shadow-card rounded-3xl p-8">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">2</span>
                                    Data Peserta
                                </h3>
                                <button type="button" id="addParticipant" class="text-sm font-bold text-brand-600 border border-brand-600 px-4 py-2 rounded-full hover:bg-brand-50 transition">
                                    + Tambah Peserta
                                </button>
                            </div>

                            <div id="participantsWrapper" class="space-y-6">
                                <div class="participant-item bg-slate-50 border border-slate-200 p-6 rounded-2xl relative" data-index="0">
                                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider participant-title">Peserta #1</span>
                                            <button type="button" class="copy-pic-btn text-[10px] bg-slate-200 hover:bg-slate-300 text-slate-700 px-2 py-0.5 rounded transition" onclick="copyFromPic(this)">
                                                Isi Data PIC
                                            </button>
                                            <button type="button" class="copy-prev-btn text-[10px] bg-slate-200 hover:bg-slate-300 text-slate-700 px-2 py-0.5 rounded transition hidden" onclick="copyFromPrev(this)">
                                                Salin Peserta Sebelumnya
                                            </button>
                                        </div>
                                        <button type="button" class="remove-participant hidden text-red-500 hover:text-red-600 text-xs font-bold uppercase">Hapus</button>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori Lomba</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($categories as $cat)
                                                @php
                                                    $priceRegular = (int) ($cat->price_regular ?? 0);
                                                    $priceEarly = (int) ($cat->price_early ?? 0);
                                                    $priceLate = (int) ($cat->price_late ?? 0);
                                                    $displayPrice = $priceRegular;
                                                    if ($priceEarly > 0) {
                                                        $displayPrice = $priceEarly;
                                                    } elseif ($priceLate > 0) {
                                                        $displayPrice = $priceLate;
                                                    }
                                                @endphp
                                                <label class="cursor-pointer relative">
                                                    <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="peer sr-only cat-radio" data-price="{{ $displayPrice }}" required>
                                                    <div class="p-3 bg-white border border-slate-300 rounded-xl peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600 transition hover:border-brand-400">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-bold text-slate-900 text-sm">{{ $cat->name }}</span>
                                                            <span class="text-xs font-bold text-brand-600">
                                                                @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                    <span class="text-slate-400 line-through mr-1">Rp {{ number_format($priceRegular/1000,0) }}k</span>
                                                                @endif
                                                                Rp {{ number_format($displayPrice/1000,0) }}k
                                                            </span>
                                                        </div>
                                                    </div>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="text" name="participants[0][name]" placeholder="Nama Peserta (BIB Name)" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                            <select name="participants[0][gender]" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                                <option value="">Pilih Gender</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="email" name="participants[0][email]" placeholder="Email Peserta" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                            <input type="text" name="participants[0][phone]" placeholder="No. HP Peserta" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <input type="text" name="participants[0][id_card]" placeholder="No. ID (KTP/SIM)" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                        <select name="participants[0][jersey_size]" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                            <option value="">Ukuran Jersey</option>
                                            @foreach(['XS','S','M','L','XL','XXL'] as $size) <option value="{{ $size }}">{{ $size }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <input type="text" name="participants[0][emergency_contact_name]" placeholder="Nama Kontak Darurat" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                        <input type="text" name="participants[0][emergency_contact_number]" placeholder="No. Kontak Darurat" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:w-96 flex-shrink-0">
                        <div class="sticky top-28 space-y-6">
                            
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                                <label class="text-sm font-bold text-slate-700 mb-2 block">Kode Promo</label>
                                <div class="flex gap-2">
                                    <input type="text" id="coupon_code" placeholder="KODE..." class="flex-1 bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm uppercase font-bold outline-none focus:border-brand-600">
                                    <button type="button" id="applyCoupon" class="bg-slate-800 text-white px-4 rounded-lg text-sm font-bold hover:bg-slate-900 transition">Pakai</button>
                                </div>
                                <div id="couponMessage" class="mt-2 text-xs font-medium"></div>
                            </div>

                            <div class="bg-brand-900 text-white rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-brand-700 rounded-full opacity-50 blur-2xl"></div>

                                <h3 class="text-lg font-bold mb-6 relative z-10">Ringkasan Biaya</h3>
                                
                                <div class="space-y-3 mb-6 border-b border-brand-700 pb-6 relative z-10 text-sm">
                                    <div class="flex justify-between text-slate-300">
                                        <span>Subtotal</span>
                                        <span id="subtotalDisplay" class="font-mono text-white">Rp 0</span>
                                    </div>
                                    <div id="discountRow" class="flex justify-between text-green-400 hidden">
                                        <span>Diskon</span>
                                        <span id="discountDisplay" class="font-mono">-Rp 0</span>
                                    </div>
                                    <div class="flex justify-between text-slate-300">
                                        <span>Platform Fee</span>
                                        <span id="platformFeeDisplay" class="font-mono text-white">Rp 0</span>
                                    </div>
                                </div>

                                <div class="mb-6 relative z-10 space-y-3">
                                    <label class="block text-sm font-bold text-slate-300 mb-2">Metode Pembayaran</label>
                                    
                                    @if($showMidtrans)
                                    <label class="flex items-center gap-3 p-3 bg-white/5 border border-white/10 rounded-xl cursor-pointer hover:bg-white/10 transition">
                                        <input type="radio" name="payment_method" value="midtrans" class="w-4 h-4 text-brand-600 focus:ring-brand-500 bg-slate-800 border-slate-500" {{ $showMidtrans && !$showMoota ? 'checked' : '' }} required>
                                        <div class="flex-1">
                                            <span class="block text-sm font-bold text-white">Otomatis (Midtrans)</span>
                                            <span class="text-xs text-slate-400">QRIS, VA, E-Wallet (Instant)</span>
                                        </div>
                                    </label>
                                    @endif

                                    @if($showMoota)
                                    <label class="flex items-center gap-3 p-3 bg-white/5 border border-white/10 rounded-xl cursor-pointer hover:bg-white/10 transition">
                                        <input type="radio" name="payment_method" value="moota" class="w-4 h-4 text-brand-600 focus:ring-brand-500 bg-slate-800 border-slate-500" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                        <div class="flex-1">
                                            <span class="block text-sm font-bold text-white">Transfer Bank (Moota)</span>
                                            <span class="text-xs text-slate-400">Verifikasi Otomatis</span>
                                        </div>
                                    </label>
                                    @endif
                                </div>

                                @if(env('RECAPTCHA_SITE_KEY'))
                                <div class="mb-6 flex justify-center relative z-10">
                                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                </div>
                                @endif

                                <div class="flex justify-between items-end mb-8 relative z-10">
                                    <span class="text-slate-300 font-bold text-sm">TOTAL</span>
                                    <span id="totalDisplay" class="text-3xl font-bold text-accent-500 tracking-tight">Rp 0</span>
                                </div>

                                @if($event->terms_and_conditions)
                                <div class="mb-6 relative z-10">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="terms_agreed" required class="mt-1 w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-500 bg-brand-800">
                                        <span class="text-xs text-slate-400">
                                            Saya setuju dengan <a href="#" class="text-white underline font-bold">Syarat & Ketentuan</a>.
                                        </span>
                                    </label>
                                </div>
                                @endif

                                <button type="submit" id="submitBtn" class="w-full py-4 bg-white text-brand-900 font-bold rounded-xl hover:bg-slate-100 transition shadow-lg relative z-10">
                                    Lanjut Pembayaran
                                </button>
                                <p class="text-[10px] text-center text-slate-500 mt-4 uppercase tracking-widest relative z-10">Secure Payment by Midtrans</p>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <!-- Sponsor Carousel -->
    @include('events.partials.sponsor-carousel', [
        'gradientFrom' => 'from-white',
        'titleColor' => 'text-slate-400',
        'containerClass' => 'bg-white/50 grayscale hover:grayscale-0 transition-all duration-500 border border-slate-100',
        'sectionClass' => 'py-16 bg-slate-50 border-t border-slate-200'
    ])

    <footer class="bg-slate-900 border-t border-slate-800 py-12 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-left">
                <strong class="text-white text-lg block mb-1">{{ $event->name }}</strong>
                &copy; {{ date('Y') }} Official Race Event.
            </div>
            <div class="flex gap-6 font-medium">
                <a href="#" class="hover:text-white transition">FAQ</a>
                <a href="#" class="hover:text-white transition">Kontak</a>
                <a href="#" class="hover:text-white transition">Instagram</a>
            </div>
        </div>
    </footer>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 z-[60] bg-black/95 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0">
        <button class="absolute top-4 right-4 text-white hover:text-gray-300 z-50 p-2" onclick="closeLightbox()">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        
        <button class="absolute top-1/2 left-4 -translate-y-1/2 text-white hover:text-gray-300 z-50 p-4 bg-black/20 rounded-full hover:bg-black/50 transition" onclick="prevImage()">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <button class="absolute top-1/2 right-4 -translate-y-1/2 text-white hover:text-gray-300 z-50 p-4 bg-black/20 rounded-full hover:bg-black/50 transition" onclick="nextImage()">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="flex items-center justify-center h-full p-4 md:p-12">
            <img id="lightbox-img" src="" class="max-h-full max-w-full object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    <!-- Leaflet GPX -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/gpx.min.js"></script>

    <script>
        // Initialize Maps
        document.addEventListener('DOMContentLoaded', function() {
            const categoriesWithGpx = @json($categoriesWithGpx ?? []);
            
            // Initialize maps for each category
            Object.values(categoriesWithGpx).forEach(category => {
                if (category.master_gpx_id && category.master_gpx) {
                    const mapId = 'map-' + category.id;
                    const gpxUrl = "{{ asset('storage') }}/" + category.master_gpx.gpx_path;
                    
                    // Check if element exists (it might be in a hidden tab)
                    const mapEl = document.getElementById(mapId);
                    if (mapEl) {
                        const map = L.map(mapId);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);

                        new L.GPX(gpxUrl, {
                            async: true,
                            marker_options: {
                                startIconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/pin-icon-start.png',
                                endIconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/pin-icon-end.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/pin-shadow.png'
                            }
                        }).on('loaded', function(e) {
                            map.fitBounds(e.target.getBounds());
                        }).addTo(map);

                        // Fix map rendering issues when tab changes
                        window.addEventListener('resize', function() {
                            map.invalidateSize();
                        });
                    }
                }
            });
        });

        // A. Utilities
        const formatCurrency = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

        // B. Animation on Scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // C. Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 20) nav.classList.add('nav-scrolled');
            else nav.classList.remove('nav-scrolled');
        });

        // D. Mobile Menu
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        if(mobileBtn) {
            mobileBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // E. Form Logic (Add Participant & Price Calculation)
        (function() {
            const form = document.getElementById('registrationForm');
            if (!form) return;

            const participantsWrapper = document.getElementById('participantsWrapper');
            const addBtn = document.getElementById('addParticipant');
            const subtotalDisplay = document.getElementById('subtotalDisplay');
            const totalDisplay = document.getElementById('totalDisplay');
            const platformFeeDisplay = document.getElementById('platformFeeDisplay');
            const platformFee = {{ $event->platform_fee ?? 0 }};
            let participantCount = 1;

            // Template for cloning (Take the first item)
            const template = participantsWrapper.querySelector('.participant-item').cloneNode(true);

            // 1. Calculate Price Function
            function calculateTotal() {
                let total = 0;
                let count = 0;
                document.querySelectorAll('.participant-item').forEach(item => {
                    const checkedRadio = item.querySelector('input[type="radio"]:checked');
                    if (checkedRadio) {
                        total += parseFloat(checkedRadio.getAttribute('data-price') || 0);
                        count++;
                    }
                });
                
                // Update Displays
                subtotalDisplay.textContent = formatCurrency(total);
                
                const totalFee = count * platformFee;
                if(platformFeeDisplay) platformFeeDisplay.textContent = formatCurrency(totalFee);
                
                const grandTotal = total + totalFee;
                totalDisplay.textContent = formatCurrency(grandTotal);
            }

            // 2. Add Participant
            addBtn.addEventListener('click', () => {
                const newItem = template.cloneNode(true);
                const idx = participantCount++;
                
                // Update UI text
                newItem.querySelector('.participant-title').textContent = `Peserta #${idx + 1}`;
                newItem.setAttribute('data-index', idx);
                newItem.querySelector('.remove-participant').classList.remove('hidden');

                // Show/Hide Copy Prev Button
                const copyPrevBtn = newItem.querySelector('.copy-prev-btn');
                if (idx > 0) {
                    copyPrevBtn.classList.remove('hidden');
                } else {
                    copyPrevBtn.classList.add('hidden');
                }

                // Update Input Names
                newItem.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        // Replace index in name: participants[0][name] -> participants[1][name]
                        input.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                        
                        // Handle Radios explicitly (need unique names per group)
                        if(input.type === 'radio') {
                            input.checked = false; 
                        } else {
                            input.value = '';
                        }
                    }
                });

                participantsWrapper.appendChild(newItem);
                attachListeners(newItem); // Re-attach change events
            });

            // 3. Remove Participant
            participantsWrapper.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-participant')) {
                    e.target.closest('.participant-item').remove();
                    calculateTotal();
                }
            });

            // 4. Attach Listeners to Radios
            function attachListeners(context) {
                context.querySelectorAll('input[type="radio"]').forEach(radio => {
                    radio.addEventListener('change', calculateTotal);
                });
            }

            // Initial attach
            attachListeners(participantsWrapper);

            // 5. Submit Handler (AJAX)
            const form = document.getElementById('registrationForm');
            
            // Add Copy Helpers to Global Scope so buttons can access them
            window.copyFromPic = function(btn) {
                const participantItem = btn.closest('.participant-item');
                const picName = document.querySelector('input[name="pic_name"]').value;
                const picEmail = document.querySelector('input[name="pic_email"]').value;
                const picPhone = document.querySelector('input[name="pic_phone"]').value;

                if (picName) participantItem.querySelector('input[name*="[name]"]').value = picName;
                if (picEmail) participantItem.querySelector('input[name*="[email]"]').value = picEmail;
                if (picPhone) participantItem.querySelector('input[name*="[phone]"]').value = picPhone;
            };

            window.copyFromPrev = function(btn) {
                const currentItem = btn.closest('.participant-item');
                const currentIndex = parseInt(currentItem.dataset.index);
                
                if (currentIndex > 0) {
                    const prevItem = document.querySelector(`.participant-item[data-index="${currentIndex - 1}"]`);
                    if (prevItem) {
                        const fields = ['emergency_contact_name', 'emergency_contact_number']; 
                        
                        fields.forEach(field => {
                            const prevValue = prevItem.querySelector(`input[name*="[${field}]"]`).value;
                            if (prevValue) {
                                currentItem.querySelector(`input[name*="[${field}]"]`).value = prevValue;
                            }
                        });
                    }
                }
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                if (typeof grecaptcha !== 'undefined') {
                    const recaptchaResponse = grecaptcha.getResponse();
                    if (!recaptchaResponse) {
                        alert('Silakan verifikasi reCAPTCHA terlebih dahulu.');
                        return;
                    }
                }
                const btn = document.getElementById('submitBtn');
                const originalText = btn.innerHTML;

                btn.innerHTML = 'Memproses...';
                btn.disabled = true;

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.snap_token) {
                            snap.pay(data.snap_token, {
                                onSuccess: function(result) { window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`; },
                                onPending: function(result) { window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; },
                                onError: function(result) { alert("Pembayaran gagal"); btn.disabled = false; btn.innerHTML = originalText; },
                                onClose: function() { btn.disabled = false; btn.innerHTML = originalText; }
                            });
                        } else {
                            // Free Event / Success direct
                            alert('Pendaftaran Berhasil!');
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Terjadi kesalahan. Periksa input Anda.');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal menghubungi server.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
        })();

        // F. Countdown Timer
        (function() {
            const targetDateStr = "{{ $countdownTarget ? $countdownTarget->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s') }}"; 
            // Replace space with T for ISO format compatibility
            const targetDate = new Date(targetDateStr.replace(' ', 'T')).getTime();  

            const daysEl = document.getElementById('cd-days');
            const hoursEl = document.getElementById('cd-hours');
            const minutesEl = document.getElementById('cd-minutes');
            const secondsEl = document.getElementById('cd-seconds');
            const countdownContainer = document.getElementById('hero-countdown');
            
            if(!daysEl || !countdownContainer) return;

            const updateCountdown = () => {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    countdownContainer.innerHTML = '<div class="text-brand-400 font-bold text-xl bg-white/10 px-6 py-3 rounded-xl border border-white/20">Event Telah Dimulai! 🏃💨</div>';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysEl.innerText = days.toString().padStart(2, '0');
                hoursEl.innerText = hours.toString().padStart(2, '0');
                minutesEl.innerText = minutes.toString().padStart(2, '0');
                secondsEl.innerText = seconds.toString().padStart(2, '0');
            };

            setInterval(updateCountdown, 1000);
            updateCountdown();
        })();

        // G. Lightbox Logic
        const galleryImages = @json(isset($event->gallery) ? array_map(fn($img) => asset('storage/'.$img), $event->gallery) : []);
        let currentImageIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');

        function openLightbox(index) {
            if (galleryImages.length === 0) return;
            currentImageIndex = index;
            lightboxImg.src = galleryImages[currentImageIndex];
            lightbox.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => lightbox.classList.remove('opacity-0'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                lightboxImg.src = '';
            }, 300);
            document.body.style.overflow = 'auto';
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            lightboxImg.src = galleryImages[currentImageIndex];
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            lightboxImg.src = galleryImages[currentImageIndex];
        }

        // Close on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeLightbox();
            }
            if (event.key === "ArrowRight") {
                nextImage();
            }
            if (event.key === "ArrowLeft") {
                prevImage();
            }
        });

        // Close when clicking outside image
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    </script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;
        const vueApp = createApp({});
        
        // Component is defined in the partial included above
        if (typeof ParticipantsTableComponent !== 'undefined') {
            vueApp.component('participants-table', ParticipantsTableComponent);
        }
        
        vueApp.mount('#vue-participants-app');
    </script>
</body>
</html>
