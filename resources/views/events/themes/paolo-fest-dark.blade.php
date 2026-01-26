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
    <meta name="keywords" content="{{ $seo['keywords'] ?? '' }}">
    <link rel="canonical" href="{{ $seo['url'] ?? route('events.show', $event->slug) }}">
    <meta name="theme-color" content="#0f172a">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['title'] ?? ($event->name.' | RuangLari') }}">
    <meta property="og:description" content="{{ $seo['description'] ?? strip_tags($event->short_description ?? $event->name) }}">
    <meta property="og:url" content="{{ $seo['url'] ?? route('events.show', $event->slug) }}">
    <meta property="og:image" content="{{ $seo['image'] ?? ($event->getHeroImageUrl() ?? asset('images/ruanglari_green.png')) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] ?? ($event->name.' | RuangLari') }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? strip_tags($event->short_description ?? $event->name) }}">
    <meta name="twitter:image" content="{{ $seo['image'] ?? ($event->getHeroImageUrl() ?? asset('images/ruanglari_green.png')) }}">
    @if(env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <meta name="description" content="{{ strip_tags($event->short_description ?? $event->name) }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com'); @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <!-- Custom Neon Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/themes/paolo-fest-neon.css') }}">

    @php
        $demoImages = [];
        if (! empty($event->jersey_image)) {
            $demoImages[] = asset('storage/'.$event->jersey_image);
        }
        if (! empty($event->medal_image)) {
            $demoImages[] = asset('storage/'.$event->medal_image);
        }
        if (count($demoImages) < 2 && isset($event->gallery) && is_array($event->gallery)) {
            foreach ($event->gallery as $img) {
                $url = asset('storage/'.$img);
                if (! in_array($url, $demoImages, true)) {
                    $demoImages[] = $url;
                }
                if (count($demoImages) >= 2) {
                    break;
                }
            }
        }
        $demoImages = array_slice($demoImages, 0, 2);
    @endphp

    <script>
        tailwind.config = {
            darkMode: 'class',
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
                            600: '#1e40af', /* Royal Blue Main */
                            700: '#1d4ed8',
                            800: '#1e3a8a',
                            900: '#172554',
                        },
                        neon: {
                            blue: '#3b82f6',
                            light: '#60a5fa',
                            glow: 'rgba(59, 130, 246, 0.5)'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                        'neon': '0 0 10px rgba(59, 130, 246, 0.5), 0 0 20px rgba(59, 130, 246, 0.3)',
                        'neon-hover': '0 0 15px rgba(59, 130, 246, 0.6), 0 0 30px rgba(59, 130, 246, 0.4)',
                        'card': '0 0 0 1px rgba(255,255,255,0.05), 0 4px 20px rgba(0,0,0,0.2)',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'blob': 'blob 7s infinite',
                        'pulse-neon': 'pulse-neon 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
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
                        },
                        'pulse-neon': {
                            '0%, 100%': { opacity: 1, boxShadow: '0 0 10px rgba(59, 130, 246, 0.5)' },
                            '50%': { opacity: .8, boxShadow: '0 0 20px rgba(59, 130, 246, 0.8)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased flex flex-col min-h-screen bg-slate-900 text-slate-200">

    @php
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

    <nav class="fixed w-full z-50 pr-5 transition-all duration-300 bg-slate-900/0 border-b border-transparent" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="#top" class="flex items-center gap-3 group">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-[75px] w-auto group-hover:scale-105 transition duration-300 drop-shadow-[0_0_10px_rgba(59,130,246,0.5)]">
                    @else
                        <div class="h-10 w-10 bg-brand-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-neon group-hover:rotate-6 transition duration-300">
                            {{ substr($event->name, 0, 1) }}
                        </div>
                        <span class="nav-brand font-extrabold text-xl tracking-tight text-white uppercase group-hover:text-neon-light transition drop-shadow-md">{{ $event->name }}</span>
                    @endif
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#about" class="nav-link text-base font-semibold text-white/80 hover:text-neon-light transition">Tentang</a>
                    <a href="#categories" class="nav-link text-base font-semibold text-white/80 hover:text-neon-light transition">Kategori</a>
                    <a href="#venue" class="nav-link text-base font-semibold text-white/80 hover:text-neon-light transition">Lokasi</a>
                    <a href="#racepack" class="nav-link text-base font-semibold text-white/80 hover:text-neon-light transition">Race Pack</a>
                    <a href="#info" class="nav-link text-base font-semibold text-white/80 hover:text-neon-light transition">Info</a>
                    
                    @if($isRegOpen)
                        <a href="#register" class="bg-brand-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-neon hover:shadow-neon-hover hover:-translate-y-0.5 transition-all duration-300 border border-brand-500">
                            Daftar Sekarang
                        </a>
                    @else
                        <span class="bg-slate-800 text-slate-500 px-6 py-2.5 rounded-lg text-sm font-bold cursor-not-allowed border border-slate-700">
                            Closed
                        </span>
                    @endif
                </div>

                <button id="mobileMenuBtn" class="mobile-toggle md:hidden text-white p-2 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden bg-slate-900/95 backdrop-blur-xl border-t border-slate-800 absolute inset-x-0 left-0 px-4 py-4 space-y-4 shadow-2xl rounded-b-2xl border-b border-brand-500/30">
                <a href="#about" class="block text-slate-300 font-medium p-3 hover:bg-slate-800 rounded-lg text-lg">Tentang</a>
                <a href="#categories" class="block text-slate-300 font-medium p-3 hover:bg-slate-800 rounded-lg text-lg">Kategori</a>
                <a href="#venue" class="block text-slate-300 font-medium p-3 hover:bg-slate-800 rounded-lg text-lg">Lokasi</a>
                <a href="#racepack" class="block text-slate-300 font-medium p-3 hover:bg-slate-800 rounded-lg text-lg">Race Pack</a>
                <a href="#info" class="block text-slate-300 font-medium p-3 hover:bg-slate-800 rounded-lg text-lg">Info Event</a>
                <a href="#register" class="block text-center bg-brand-600 text-white font-bold p-4 rounded-xl shadow-neon text-lg mt-4">Registrasi</a>
            </div>
        </div>
    </nav>

    <header id="top" class="relative pt-32 pb-20 overflow-hidden min-h-[90vh] flex items-center">
        <!-- Dynamic Background -->
        <div class="absolute inset-0 z-0">
            @if($event->hero_image)
                <img src="{{ asset('storage/' . $event->hero_image) }}" class="w-full h-full object-cover opacity-40 mix-blend-overlay">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-slate-900/30"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-slate-900"></div>
                <!-- Neon Blobs -->
                <div class="absolute top-0 -left-4 w-96 h-96 bg-brand-600 rounded-full mix-blend-screen filter blur-[100px] opacity-30 animate-blob"></div>
                <div class="absolute top-0 -right-4 w-96 h-96 bg-neon-blue rounded-full mix-blend-screen filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-30 animate-blob animation-delay-4000"></div>
            @endif
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="flex flex-col md:flex-row items-center gap-12 lg:gap-20">
                <div class="flex-1 text-center md:text-left reveal active">
                    <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-slate-800/50 border border-brand-500/30 backdrop-blur-md shadow-neon mb-8 hover:bg-slate-800/80 transition cursor-default group">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neon-light opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-neon-blue"></span>
                        </span>
                        <span class="text-sm font-bold text-neon-light uppercase tracking-wider group-hover:text-white transition">{{ $event->start_at->format('d F Y') }} • {{ $event->location_name }}</span>
                    </div>
                    
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-blue-100 to-brand-300 tracking-tight leading-none mb-6 drop-shadow-[0_0_15px_rgba(59,130,246,0.3)]">
                        {{ strtoupper($event->name) }}
                    </h1>
                    <div class="mb-10">
                        <p class="text-lg md:text-xl text-slate-300 mb-10 leading-relaxed max-w-lg mx-auto md:mx-0 font-light">
                            {!! $event->short_description ?? 'Rasakan sensasi berlari dengan atmosfer kompetitif yang menyenangkan.' !!}
                        </p>
                    </div>                    

                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start mb-12">
                        @if($isRegOpen)
                        <a href="#register" class="group px-8 py-4 bg-brand-600 text-white font-bold rounded-xl shadow-neon hover:shadow-neon-hover transition-all duration-300 hover:-translate-y-1 flex items-center justify-center gap-2 border border-brand-500">
                            Amankan Slot
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @else
                        <button disabled class="px-8 py-4 bg-slate-800 text-slate-500 font-bold rounded-xl cursor-not-allowed border border-slate-700">
                            Pendaftaran Ditutup
                        </button>
                        @endif
                        <a href="#about" class="px-8 py-4 bg-slate-800/50 text-white border border-white/10 backdrop-blur-sm font-bold rounded-xl hover:bg-slate-800/80 hover:border-brand-500/50 transition flex items-center justify-center">
                            Explore Event
                        </a>
                    </div>

                    <!-- Countdown -->
                    <div class="flex flex-col items-center md:items-start">
                        <span class="text-xs font-bold text-neon-light uppercase tracking-widest mb-4 drop-shadow-sm">{{ $countdownLabel }}</span>
                        <div id="hero-countdown" class="flex gap-4">
                            @foreach(['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $id => $label)
                            <div class="text-center">
                                <div class="bg-slate-800/60 backdrop-blur-md border border-brand-500/30 w-16 h-16 rounded-xl flex items-center justify-center mb-1 shadow-[0_0_15px_rgba(59,130,246,0.1)]">
                                    <span class="text-2xl font-bold text-white" id="cd-{{ $id }}">00</span>
                                </div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">{{ $label }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Floating Info Card -->
                <div class="flex-1 w-full max-w-md mx-auto reveal delay-200 hidden lg:block">
                    <div class="glass-card p-8 rounded-[2rem] animate-float">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <p class="text-xs text-neon-light font-bold uppercase tracking-wider mb-1">EVENT STATUS</p>
                                <p class="text-3xl font-black text-white drop-shadow-md">READY TO RACE</p>
                            </div>
                            <div class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center text-white shadow-neon">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-slate-900/50 p-5 rounded-2xl border border-white/5 flex items-center gap-4 hover:border-brand-500/30 transition">
                                <div class="w-10 h-10 rounded-full bg-brand-500/20 flex items-center justify-center text-brand-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-white font-bold">{{ $event->location_name }}</span>
                                    <span class="text-xs text-slate-400">Venue Location</span>
                                </div>
                            </div>
                            <div class="bg-slate-900/50 p-5 rounded-2xl border border-white/5 flex items-center gap-4 hover:border-brand-500/30 transition">
                                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400">
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
                                <span class="block text-2xl font-bold text-white">1K+</span>
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
    <section id="about" class="py-24 bg-slate-900 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal">
                    <span class="text-neon-light font-bold uppercase tracking-widest text-sm mb-2 block">Tentang Event</span>
                    <h2 class="text-4xl font-extrabold text-white mb-6 leading-tight drop-shadow-sm">Berlari Melampaui Batas,<br>Nikmati Setiap Langkah</h2>
                    <div class="prose prose-lg text-slate-300">
                        {!! $event->full_description ?? $event->short_description !!}
                    </div>
                    
                    <div class="mt-8 grid grid-cols-2 gap-6">
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-slate-800 transition border border-transparent hover:border-slate-700">
                            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-green-400 shrink-0 border border-slate-700 shadow-lg">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Rute Steril</h4>
                                <p class="text-sm text-slate-400 mt-1">Keamanan pelari adalah prioritas utama kami dengan rute bebas kendaraan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-slate-800 transition border border-transparent hover:border-slate-700">
                            <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-blue-400 shrink-0 border border-slate-700 shadow-lg">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Refreshment</h4>
                                <p class="text-sm text-slate-400 mt-1">Water station tiap 2.5KM dengan air mineral dan isotonik.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative reveal delay-200">
                    <div class="absolute -top-10 -right-10 w-64 h-64 bg-brand-600 rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>
                    <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-purple-600 rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl rotate-2 hover:rotate-0 transition duration-500 border border-slate-700/50">
                        @php
                            $aboutImgSrc = null;
                            if (isset($event->gallery) && is_array($event->gallery) && count($event->gallery) > 0) {
                                $aboutImgSrc = $event->gallery[0];
                            } elseif ($event->hero_image) {
                                $aboutImgSrc = $event->hero_image;
                            }
                        @endphp
                        
                        @if($aboutImgSrc)
                            <img src="{{ asset('storage/' . $aboutImgSrc) }}" class="w-full h-auto object-cover grayscale hover:grayscale-0 transition duration-700">
                        @else
                            <div class="bg-slate-800 w-full aspect-[4/3] flex items-center justify-center text-slate-500 font-bold">Event Image</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section (New) -->
    @if(isset($event->gallery) && count($event->gallery) > 0)
    <section id="gallery" class="py-24 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-neon-light font-bold uppercase tracking-widest text-sm">Dokumentasi</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mt-2">Event Gallery</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 reveal">
                @foreach(array_slice($event->gallery, 0, 8) as $index => $img)
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-md cursor-zoom-in border border-slate-700 hover:border-brand-500 transition" onclick="openLightbox({{ $index }})">
                    <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-110" loading="lazy">
                    <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition duration-300"></div>
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
    <section id="categories" class="py-24 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-neon-light font-bold uppercase tracking-widest text-sm">Kategori Lomba</span>
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl mt-2">Pilih Tantanganmu</h2>
                <p class="mt-4 text-lg text-slate-400 max-w-2xl mx-auto">Tersedia berbagai kategori jarak yang sesuai dengan target latihanmu.</p>
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
                <div class="relative flex flex-col glass-card rounded-[2rem] hover:-translate-y-2 transition-all duration-300 group reveal overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-neon-light shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
                    
                    <div class="p-8 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-lg font-black bg-slate-800 text-white border border-slate-700 shadow-inner">
                                {{ $cat->distance_km ?? '?' }}K
                            </span>
                            @if($cat->quota < 50 && $cat->quota > 0)
                                <span class="px-3 py-1 rounded-full bg-red-900/50 text-red-400 text-xs font-bold animate-pulse border border-red-500/30">
                                    🔥 Sisa Sedikit
                                </span>
                            @endif
                        </div>

                        <h3 class="text-3xl font-bold text-white mb-2">{{ $cat->name }}</h3>
                        <p class="text-slate-400 text-sm mb-8 flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Cut Off Time: <strong class="text-white">{{ $cat->cot_hours ?? '-' }} Jam</strong>
                        </p>

                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center text-sm font-medium text-slate-300 bg-slate-800/50 p-3 rounded-xl border border-slate-700/50">
                                <div class="w-6 h-6 rounded-full bg-green-900/50 text-green-400 flex items-center justify-center mr-3 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Official Jersey & BIB
                            </li>
                            <li class="flex items-center text-sm font-medium text-slate-300 bg-slate-800/50 p-3 rounded-xl border border-slate-700/50">
                                <div class="w-6 h-6 rounded-full bg-green-900/50 text-green-400 flex items-center justify-center mr-3 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Finisher Medal
                            </li>
                            <li class="flex items-center text-sm font-medium text-slate-300 bg-slate-800/50 p-3 rounded-xl border border-slate-700/50">
                                <div class="w-6 h-6 rounded-full bg-green-900/50 text-green-400 flex items-center justify-center mr-3 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Refreshment & Medic
                            </li>
                        </ul>
                    </div>
                    
                    <div class="p-6 bg-slate-800/80 border-t border-slate-700">
                        <div class="flex items-baseline justify-between mb-4">
                            <span class="text-sm text-slate-400 font-bold uppercase">Registrasi</span>
                            <div class="text-right">
                                @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                    <div class="text-xs font-bold text-slate-500 line-through">Rp {{ number_format($priceRegular/1000, 0) }}k</div>
                                @endif
                                <div class="text-3xl font-black text-neon-light drop-shadow-[0_0_5px_rgba(59,130,246,0.8)]">Rp {{ number_format($displayPrice/1000, 0) }}k</div>
                            </div>
                        </div>
                        <a href="#register" class="block w-full py-4 px-4 bg-brand-600 text-white font-bold text-center rounded-xl shadow-neon hover:shadow-neon-hover transition-all duration-300 border border-brand-500">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Medal Section -->
    @if($event->medal_image)
    <section id="medal" class="py-24 bg-slate-950 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-16">
                <div class="flex-1 text-center md:text-left reveal">
                    <span class="text-neon-light font-bold uppercase tracking-widest text-sm mb-2 block">Finisher Reward</span>
                    <h2 class="text-4xl md:text-5xl font-black text-white mb-6">THE MEDAL</h2>
                    <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                        Simbol pencapaian Anda menaklukkan rute ini. Didesain eksklusif dengan material Zinc Alloy 3D berkualitas tinggi, berat, dan solid.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10 hover:border-brand-500/50 transition">
                            <span class="block text-2xl font-bold text-white">3D</span>
                            <span class="text-xs text-slate-400 uppercase">Design</span>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10 hover:border-brand-500/50 transition">
                            <span class="block text-2xl font-bold text-white">High</span>
                            <span class="text-xs text-slate-400 uppercase">Quality</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 reveal delay-200">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-brand-600 rounded-full blur-[80px] opacity-20 group-hover:opacity-40 transition duration-500"></div>
                        <img src="{{ asset('storage/' . $event->medal_image) }}" class="relative w-full max-w-md mx-auto drop-shadow-2xl transform group-hover:scale-105 transition duration-500" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Race Pack & Facilities -->
    <section id="racepack" class="py-24 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-neon-light font-bold uppercase tracking-widest text-sm">Race Entitlements</span>
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl mt-2">Fasilitas & Race Pack</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
                <!-- Jersey Card -->
                <div class="glass-card rounded-[2.5rem] p-8 shadow-xl overflow-hidden relative group reveal">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-brand-600 rounded-full -mr-16 -mt-16 transition group-hover:scale-110 opacity-20 filter blur-2xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-brand-400 border border-slate-700">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Official Jersey</h3>
                        </div>
                        <p class="text-slate-400 mb-8">Jersey lari eksklusif dengan bahan Dry-Fit premium yang ringan dan menyerap keringat.</p>
                        <div class="rounded-3xl overflow-hidden bg-slate-900 border border-slate-700">
                             @if($event->jersey_image)
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" class="w-full h-auto object-cover hover:scale-105 transition duration-700" loading="lazy">
                             @else
                                <div class="aspect-video flex items-center justify-center text-slate-500 font-bold">Preview Jersey</div>
                             @endif
                        </div>
                    </div>
                </div>

                <!-- Facilities Grid -->
                <div class="grid grid-cols-1 gap-6">
                    <div class="glass-card p-8 rounded-3xl flex items-start gap-6 hover:bg-slate-800 transition reveal delay-100">
                        <div class="w-14 h-14 bg-blue-900/30 text-blue-400 rounded-2xl flex items-center justify-center shrink-0 border border-blue-500/20">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white mb-2">Timing System</h4>
                            <p class="text-slate-400">Pencatatan waktu akurat menggunakan chip timing system berstandar nasional.</p>
                        </div>
                    </div>
                    <div class="glass-card p-8 rounded-3xl flex items-start gap-6 hover:bg-slate-800 transition reveal delay-200">
                        <div class="w-14 h-14 bg-orange-900/30 text-orange-400 rounded-2xl flex items-center justify-center shrink-0 border border-orange-500/20">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white mb-2">Water Station</h4>
                            <p class="text-slate-400">Pos hidrasi tersedia setiap 2.5 KM dilengkapi tim medis.</p>
                        </div>
                    </div>
                    <div class="glass-card p-8 rounded-3xl flex items-start gap-6 hover:bg-slate-800 transition reveal delay-300">
                        <div class="w-14 h-14 bg-purple-900/30 text-purple-400 rounded-2xl flex items-center justify-center shrink-0 border border-purple-500/20">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white mb-2">Race Photography</h4>
                            <p class="text-slate-400">Dokumentasi foto berkualitas tinggi di sepanjang rute untuk Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="demo" class="py-24 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <span class="text-neon-light font-bold uppercase tracking-widest text-sm">Demo</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mt-2">Preview Race Pack</h2>
                <p class="mt-4 text-slate-400 max-w-2xl mx-auto">Klik gambar untuk melihat detail lebih besar.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 reveal">
                <div class="glass-card rounded-3xl overflow-hidden border border-slate-700/60 group">
                    @if(!empty($demoImages[0]))
                        <button type="button" class="w-full text-left cursor-zoom-in" onclick="openLightbox(0, 'demo')">
                            <div class="relative aspect-[4/3] bg-slate-900">
                                <img src="{{ $demoImages[0] }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" alt="Demo Image 1">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-70"></div>
                                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3">
                                    <div class="text-white font-bold">Demo 1</div>
                                    <div class="text-xs text-white/80 font-bold bg-black/40 border border-white/10 rounded-full px-3 py-1">Klik untuk zoom</div>
                                </div>
                            </div>
                        </button>
                    @else
                        <div class="aspect-[4/3] bg-slate-900 flex items-center justify-center text-slate-500 font-bold">Demo Image</div>
                    @endif
                </div>

                <div class="glass-card rounded-3xl overflow-hidden border border-slate-700/60 group">
                    @if(!empty($demoImages[1]))
                        <button type="button" class="w-full text-left cursor-zoom-in" onclick="openLightbox(1, 'demo')">
                            <div class="relative aspect-[4/3] bg-slate-900">
                                <img src="{{ $demoImages[1] }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" alt="Demo Image 2">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-70"></div>
                                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3">
                                    <div class="text-white font-bold">Demo 2</div>
                                    <div class="text-xs text-white/80 font-bold bg-black/40 border border-white/10 rounded-full px-3 py-1">Klik untuk zoom</div>
                                </div>
                            </div>
                        </button>
                    @else
                        <div class="aspect-[4/3] bg-slate-900 flex items-center justify-center text-slate-500 font-bold">Demo Image</div>
                    @endif
                </div>
            </div>

            <div class="mt-10 flex justify-center reveal">
                <a href="#register" class="inline-flex items-center justify-center px-8 py-4 bg-brand-600 text-white font-bold rounded-2xl shadow-neon hover:shadow-neon-hover transition-all duration-300 border border-brand-500">
                    Daftar Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Info Section (RPC & Venue) -->
    <section id="info" class="py-24 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- RPC Info -->
                <div class="reveal">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-white border border-slate-700">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Race Pack Collection</h3>
                    </div>
                    <div class="glass-card rounded-3xl p-8">
                        <p class="text-slate-300 mb-6">Pengambilan race pack akan dilakukan pada:</p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 text-brand-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <div>
                                    <strong class="block text-white">Tanggal</strong>
                                    <span class="text-slate-400">H-2 & H-1 Sebelum Race Day</span>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 text-brand-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <strong class="block text-white">Waktu</strong>
                                    <span class="text-slate-400">10:00 - 20:00 WIB</span>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <svg class="w-5 h-5 text-brand-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <div>
                                    <strong class="block text-white">Lokasi</strong>
                                    <span class="text-slate-400">{{ $event->rpc_location_name ?? 'To Be Announced' }}</span>
                                    <p class="text-sm text-slate-500 mt-1">{{ $event->rpc_location_address }}</p>
                                </div>
                            </li>
                        </ul>
                        @if($event->rpc_latitude && $event->rpc_longitude)
                        <a href="https://maps.google.com/?q={{ $event->rpc_latitude }},{{ $event->rpc_longitude }}" target="_blank" class="inline-flex items-center text-sm font-bold text-neon-light hover:text-white transition">
                            Buka Google Maps
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Venue Info -->
                <div class="reveal delay-200" id="venue">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-brand-600 rounded-full flex items-center justify-center text-white shadow-neon">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Venue & Parking</h3>
                    </div>
                    <div class="bg-slate-800 border border-slate-700 shadow-lg rounded-3xl overflow-hidden h-full min-h-[300px] relative group">
                         @if($event->map_embed_url)
                            <iframe src="{{ $event->map_embed_url }}" class="w-full h-full min-h-[300px] border-0 opacity-80 group-hover:opacity-100 transition" allowfullscreen="" loading="lazy"></iframe>
                         @elseif($event->location_lat && $event->location_lng)
                            <iframe src="https://maps.google.com/maps?q={{ $event->location_lat }},{{ $event->location_lng }}&hl=id&z=14&output=embed" class="w-full h-full min-h-[300px] border-0 opacity-80 group-hover:opacity-100 transition" allowfullscreen="" loading="lazy"></iframe>
                         @else
                            <div class="absolute inset-0 bg-slate-800 flex items-center justify-center text-slate-500 font-bold">Map Loading...</div>
                         @endif
                         <div class="absolute bottom-4 left-4 right-4 bg-slate-900/90 backdrop-blur-sm p-4 rounded-xl border border-white/10 shadow-sm flex justify-between items-center gap-4">
                             <div class="min-w-0">
                                <strong class="block text-white truncate">{{ $event->location_name }}</strong>
                                <p class="text-xs text-slate-400 truncate">{{ $event->location_address }}</p>
                             </div>
                             @if($event->location_lat && $event->location_lng)
                             <a href="https://www.google.com/maps/dir/?api=1&destination={{ $event->location_lat }},{{ $event->location_lng }}" target="_blank" class="shrink-0 bg-brand-600 text-white p-2.5 rounded-lg hover:bg-brand-700 transition shadow-neon" title="Get Directions">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                             </a>
                             @endif
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-24 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal">
                <h3 class="text-2xl font-bold text-white mb-8 text-center">Frequently Asked Questions</h3>
                <div class="space-y-4 max-w-3xl mx-auto">
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <button class="w-full px-6 py-4 text-left font-bold text-white flex justify-between items-center hover:bg-slate-700/50 transition" onclick="this.nextElementSibling.classList.toggle('hidden')">
                            Apakah tiket bisa di-refund?
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="px-6 pb-4 text-sm text-slate-400 hidden">
                            Tiket yang sudah dibeli tidak dapat dikembalikan (non-refundable), namun dapat dipindahtangankan sesuai syarat dan ketentuan yang berlaku.
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <button class="w-full px-6 py-4 text-left font-bold text-white flex justify-between items-center hover:bg-slate-700/50 transition" onclick="this.nextElementSibling.classList.toggle('hidden')">
                            Kapan batas akhir pendaftaran?
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="px-6 pb-4 text-sm text-slate-400 hidden">
                            Pendaftaran ditutup pada {{ $event->registration_close_at ? $event->registration_close_at->format('d F Y') : 'H-7 sebelum acara' }} atau saat kuota terpenuhi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('events.partials.prizes-section', ['categories' => $categories])

    <!-- Participants Table Section -->
    @if($event->participants()->exists())
    <section id="participants-list" class="py-24 bg-slate-900 relative">
        <div class="absolute inset-0 bg-brand-900/5"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
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

    <section id="register" class="py-24 bg-slate-900 relative">
        <div class="absolute inset-0 bg-brand-900/10"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white">Registrasi Peserta</h2>
                <p class="mt-4 text-slate-400">Pastikan data yang Anda masukkan sesuai dengan identitas (KTP/SIM).</p>
            </div>

            @if(!$isRegOpen)
                <div class="bg-slate-800 border-2 border-slate-700 rounded-3xl p-12 text-center max-w-2xl mx-auto">
                    <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <h3 class="text-2xl font-bold text-white mb-2">Pendaftaran Ditutup</h3>
                    <p class="text-slate-400">Mohon maaf, pendaftaran saat ini tidak tersedia.</p>
                </div>
            @else
                <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="flex flex-col lg:flex-row gap-8 reveal">
                    @csrf
                    
                    <div class="flex-1 space-y-8">
                        
                        <div class="glass-card rounded-3xl p-8">
                            <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-sm shadow-neon">1</span>
                                Data Penanggung Jawab
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1">Nama Lengkap</label>
                                    <input type="text" name="pic_name" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue focus:border-neon-blue outline-none transition text-white placeholder-slate-500" placeholder="Sesuai KTP" required>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300 mb-1">Email</label>
                                        <input type="email" name="pic_email" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue focus:border-neon-blue outline-none transition text-white placeholder-slate-500" placeholder="email@contoh.com" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300 mb-1">No. WhatsApp</label>
                                        <input type="text" name="pic_phone" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue focus:border-neon-blue outline-none transition text-white placeholder-slate-500" placeholder="0812xxxx" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card rounded-3xl p-8">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-sm shadow-neon">2</span>
                                    Data Peserta
                                </h3>
                                <button type="button" id="addParticipant" class="text-sm font-bold text-neon-light border border-neon-light px-4 py-2 rounded-full hover:bg-brand-900/50 transition">
                                    + Tambah Peserta
                                </button>
                            </div>

                            <div id="participantsWrapper" class="space-y-6">
                                <div class="participant-item bg-slate-800/50 border border-slate-700 p-6 rounded-2xl relative" data-index="0">
                                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-700">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider participant-title">Peserta #1</span>
                                        <button type="button" class="remove-participant hidden text-red-400 hover:text-red-500 text-xs font-bold uppercase">Hapus</button>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-300 mb-1">Kategori Lomba</label>
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
                                                    <div class="p-3 bg-slate-800 border border-slate-600 rounded-xl peer-checked:border-neon-blue peer-checked:bg-brand-900/30 peer-checked:ring-1 peer-checked:ring-neon-blue transition hover:border-brand-500">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-bold text-white text-sm">{{ $cat->name }}</span>
                                                            <span class="text-xs font-bold text-neon-light">
                                                                @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                    <span class="text-slate-500 line-through mr-1">Rp {{ number_format($priceRegular/1000,0) }}k</span>
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
                                            <input type="text" name="participants[0][name]" placeholder="Nama Peserta (BIB Name)" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white placeholder-slate-500" required>
                                            <select name="participants[0][gender]" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white" required>
                                                <option value="" class="bg-slate-800">Pilih Gender</option>
                                                <option value="male" class="bg-slate-800">Laki-laki</option>
                                                <option value="female" class="bg-slate-800">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="email" name="participants[0][email]" placeholder="Email Peserta" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white placeholder-slate-500" required>
                                            <input type="text" name="participants[0][phone]" placeholder="No. HP Peserta" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white placeholder-slate-500" required>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <input type="text" name="participants[0][id_card]" placeholder="No. ID (KTP/SIM)" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white placeholder-slate-500" required>
                                        <select name="participants[0][jersey_size]" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white" required>
                                            <option value="" class="bg-slate-800">Ukuran Jersey</option>
                                            @foreach(['XS','S','M','L','XL','XXL'] as $size) <option value="{{ $size }}" class="bg-slate-800">{{ $size }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <input type="text" name="participants[0][emergency_contact_name]" placeholder="Nama Kontak Darurat" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white placeholder-slate-500" required>
                                        <input type="text" name="participants[0][emergency_contact_number]" placeholder="No. Kontak Darurat" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-4 focus:ring-2 focus:ring-neon-blue outline-none text-white placeholder-slate-500" required>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:w-96 flex-shrink-0">
                        <div class="sticky top-28 space-y-6">
                            
                            <div class="glass-card rounded-2xl p-6">
                                <label class="text-sm font-bold text-slate-300 mb-2 block">Kode Promo</label>
                                <div class="flex gap-2">
                                    <input type="text" id="coupon_code" placeholder="KODE..." class="flex-1 bg-slate-800 border border-slate-600 rounded-lg px-3 py-3 text-sm uppercase font-bold outline-none focus:border-neon-blue text-white">
                                    <button type="button" id="applyCoupon" class="bg-brand-600 text-white px-4 rounded-lg text-sm font-bold hover:bg-brand-700 transition shadow-neon">Pakai</button>
                                </div>
                                <div id="couponMessage" class="mt-2 text-xs font-medium"></div>
                            </div>

                            <div class="bg-brand-900 text-white rounded-3xl p-8 shadow-2xl relative overflow-hidden border border-brand-700">
                                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-brand-600 rounded-full opacity-50 blur-2xl animate-pulse"></div>

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

                                @if(env('RECAPTCHA_SITE_KEY'))
                                <div class="mb-6 flex justify-center relative z-10">
                                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                </div>
                                @endif

                                <div class="flex justify-between items-end mb-8 relative z-10">
                                    <span class="text-slate-300 font-bold text-sm">TOTAL</span>
                                    <span id="totalDisplay" class="text-3xl font-bold text-neon-light tracking-tight drop-shadow-md">Rp 0</span>
                                </div>

                                @if($event->terms_and_conditions)
                                <div class="mb-6 relative z-10">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="terms_agreed" required class="mt-1 w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-500 bg-slate-800">
                                        <span class="text-xs text-slate-400">
                                            Saya setuju dengan <a href="#" class="text-white underline font-bold">Syarat & Ketentuan</a>.
                                        </span>
                                    </label>
                                </div>
                                @endif

                                <button type="submit" id="submitBtn" class="w-full py-4 bg-white text-brand-900 font-bold rounded-xl hover:bg-slate-100 transition shadow-neon hover:shadow-neon-hover relative z-10">
                                    Lanjut Pembayaran
                                </button>
                                <p class="text-[10px] text-center text-slate-400 mt-4 uppercase tracking-widest relative z-10">Secure Payment by Midtrans</p>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <!-- Sponsor Carousel -->
    @include('events.partials.sponsor-carousel', [
        'gradientFrom' => 'from-slate-900',
        'titleColor' => 'text-slate-500',
        'containerClass' => 'bg-slate-800/50 grayscale hover:grayscale-0 transition-all duration-500 border border-slate-800',
        'sectionClass' => 'hidden md:block py-16 bg-slate-900 border-t border-slate-800'
    ])

    <footer class="bg-slate-950 border-t border-slate-900 py-12 text-slate-500 text-sm">
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
        
        <button id="lightbox-prev" class="absolute top-1/2 left-4 -translate-y-1/2 text-white hover:text-gray-300 z-50 p-4 bg-black/20 rounded-full hover:bg-black/50 transition" onclick="prevImage()">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <button id="lightbox-next" class="absolute top-1/2 right-4 -translate-y-1/2 text-white hover:text-gray-300 z-50 p-4 bg-black/20 rounded-full hover:bg-black/50 transition" onclick="nextImage()">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="flex items-center justify-center h-full p-4 md:p-12">
            <img id="lightbox-img" src="" class="max-h-full max-w-full object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <script>
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
                    countdownContainer.innerHTML = '<div class="text-neon-light font-bold text-xl bg-slate-800/50 px-6 py-3 rounded-xl border border-white/10">Event Telah Dimulai! 🏃💨</div>';
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
        const demoImages = @json($demoImages);
        const lightboxLists = { gallery: galleryImages, demo: demoImages };
        let currentLightboxList = 'gallery';
        let currentImageIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxPrevBtn = document.getElementById('lightbox-prev');
        const lightboxNextBtn = document.getElementById('lightbox-next');

        function updateLightboxNav() {
            const list = lightboxLists[currentLightboxList] || [];
            const shouldShowNav = list.length > 1;
            if (lightboxPrevBtn) lightboxPrevBtn.classList.toggle('hidden', !shouldShowNav);
            if (lightboxNextBtn) lightboxNextBtn.classList.toggle('hidden', !shouldShowNav);
        }

        function openLightbox(index, listName = 'gallery') {
            const list = lightboxLists[listName] || [];
            if (list.length === 0) return;
            currentLightboxList = listName;
            currentImageIndex = Math.max(0, Math.min(index, list.length - 1));
            lightboxImg.src = list[currentImageIndex];
            lightbox.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => lightbox.classList.remove('opacity-0'), 10);
            document.body.style.overflow = 'hidden';
            updateLightboxNav();
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
            const list = lightboxLists[currentLightboxList] || [];
            if (list.length === 0) return;
            currentImageIndex = (currentImageIndex + 1) % list.length;
            lightboxImg.src = list[currentImageIndex];
        }

        function prevImage() {
            const list = lightboxLists[currentLightboxList] || [];
            if (list.length === 0) return;
            currentImageIndex = (currentImageIndex - 1 + list.length) % list.length;
            lightboxImg.src = list[currentImageIndex];
        }

        // Close on escape key
        document.addEventListener('keydown', function(event) {
            if (lightbox.classList.contains('hidden')) return;
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
