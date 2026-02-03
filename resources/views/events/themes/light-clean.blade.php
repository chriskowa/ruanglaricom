<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <script>
        // Nominatim CORS Proxy Interceptor
        (function() {
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    var proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
                    return originalFetch(proxyUrl, options);
                }
                return originalFetch(url, options);
            };
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @php
        $seoTitle = isset($seo['title']) && $seo['title'] ? $seo['title'] : $event->name . ' - ' . ($event->location_name ?? 'Official Event');
        $seoDesc = isset($seo['description']) && $seo['description'] ? $seo['description'] : Str::limit(strip_tags($event->short_description ?? $event->description), 155);
        $seoKeywords = isset($seo['keywords']) && $seo['keywords'] ? $seo['keywords'] : 'lari, event lari, ' . $event->name . ', ' . ($event->location_name ?? '') . ', pendaftaran lari, ruanglari';
        $seoUrl = isset($seo['url']) && $seo['url'] ? $seo['url'] : route('events.show', $event->slug);
        $seoImage = isset($seo['image']) && $seo['image'] ? $seo['image'] : ($event->hero_image ? asset('storage/' . $event->hero_image) : asset('images/ruanglari_green.png'));
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}" />
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $seoUrl }}">
    <meta name="theme-color" content="#ffffff">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="event" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ $seoDesc }}" />
    <meta property="og:url" content="{{ $seoUrl }}" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="RuangLari" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ $seoDesc }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Event",
      "name": "{{ $event->name }}",
      "description": "{{ $seoDesc }}",
      "image": "{{ $seoImage }}",
      "startDate": "{{ $event->start_at ? $event->start_at->toIso8601String() : '' }}",
      "endDate": "{{ $event->end_at ? $event->end_at->toIso8601String() : ($event->start_at ? $event->start_at->addHours(4)->toIso8601String() : '') }}",
      "eventStatus": "https://schema.org/EventScheduled",
      "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
      "location": {
        "@@type": "Place",
        "name": "{{ $event->location_name ?? 'TBA' }}",
        "address": {
          "@@type": "PostalAddress",
          "addressLocality": "{{ $event->city ?? '' }}",
          "addressCountry": "ID"
        }
      },
      "organizer": {
        "@@type": "Organization",
        "name": "RuangLari",
        "url": "{{ url('/') }}"
      },
      "offers": {
        "@@type": "Offer",
        "url": "{{ $seoUrl }}",
        "price": "0",
        "priceCurrency": "IDR",
        "availability": "{{ (isset($isRegOpen) && $isRegOpen) ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut' }}",
        "validFrom": "{{ $event->registration_open_at ? $event->registration_open_at->toIso8601String() : '' }}"
      }
    }
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/green/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/green/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @if(env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    @php
        $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
        $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
    @endphp
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Primary Blue
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                        slate: {
                            850: '#1e293b', // Deep Text
                        }
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                        'glow': '0 0 20px rgba(37, 99, 235, 0.2)',
                        'card': '0 10px 40px -10px rgba(0,0,0,0.05)',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
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
        body { 
            background-color: #fafafa;
            color: #0f172a;
        }
        
        /* Premium Background Mesh Gradient */
        .bg-mesh {
            background-color: #ffffff;
            background-image: 
                radial-gradient(at 0% 0%, hsla(217,91%,93%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(200,98%,95%,1) 0, transparent 50%), 
                radial-gradient(at 100% 100%, hsla(217,91%,93%,1) 0, transparent 50%);
            background-attachment: fixed;
        }

        /* Glassmorphism Classes */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }

        /* Form Styling */
        .input-premium {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .input-premium:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            transform: translateY(-1px);
        }

        /* Animation Utilities */
        .reveal-up { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-up.active { opacity: 1; transform: translateY(0); }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-mesh font-sans antialiased selection:bg-brand-600 selection:text-white flex flex-col min-h-screen">

    @php
        $paymentConfig = $event->payment_config ?? [];
        $showMidtrans = $paymentConfig['midtrans'] ?? true;
        $showMoota = $paymentConfig['moota'] ?? false;
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
    @endphp

    <nav class="fixed w-full z-50 top-0 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="glass-nav rounded-2xl shadow-glass px-6 h-16 flex items-center justify-between transition-all duration-300" id="nav-container">
                <a href="#top" class="flex items-center gap-2 group">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" alt="{{ $event->name }}" class="h-8 w-auto">
                    @else
                        <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-glow">EV</div>
                        <span class="text-lg font-extrabold tracking-tight text-slate-900 group-hover:text-brand-600 transition">{{ $event->name }}</span>
                    @endif
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <a href="#fasilitas" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-brand-600 rounded-full hover:bg-brand-50 transition">Fasilitas</a>
                    <a href="#lokasi" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-brand-600 rounded-full hover:bg-brand-50 transition">Rute</a>
                    @if($isRegOpen)
                    <a href="#registrasi" class="ml-4 px-6 py-2 bg-slate-900 text-white text-sm font-bold rounded-full hover:bg-brand-600 hover:shadow-glow hover:-translate-y-0.5 transition-all duration-300">
                        Daftar
                    </a>
                    @endif
                </div>

                <button id="navToggle" class="md:hidden p-2 text-slate-600 hover:text-brand-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
        
        <div id="mobileMenu" class="hidden absolute top-24 left-4 right-4 bg-white rounded-2xl shadow-xl p-4 border border-slate-100 flex flex-col gap-2 origin-top transform transition">
            <a href="#fasilitas" class="p-3 text-slate-600 font-medium hover:bg-brand-50 rounded-xl">Fasilitas</a>
            <a href="#lokasi" class="p-3 text-slate-600 font-medium hover:bg-brand-50 rounded-xl">Rute</a>
            <a href="#registrasi" class="p-3 text-brand-600 font-bold bg-brand-50 rounded-xl text-center">Daftar Sekarang</a>
        </div>
    </nav>

    <main id="top" class="flex-grow pt-28 pb-12">
        
        <section class="relative px-4 mb-24">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full -z-10 pointer-events-none overflow-hidden">
                <div class="absolute top-0 right-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
                <div class="absolute top-0 left-0 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            </div>

            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-7 reveal-up active">
                    <div class="inline-flex items-center gap-2 bg-white border border-blue-100 rounded-full pl-1 pr-4 py-1 mb-8 shadow-sm hover:shadow-md transition cursor-default">
                        <span class="bg-brand-600 text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">New</span>
                        <span class="text-slate-600 text-xs font-semibold">{{ $event->location_name }}</span>
                    </div>

                    <h1 class="text-6xl md:text-8xl font-black tracking-tighter text-slate-900 leading-[0.95] mb-6">
                        {{ strtoupper($event->name) }} <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-purple-500">RUN {{ $event->start_at->format('Y') }}</span>
                    </h1>

                    <p class="text-lg md:text-xl text-slate-500 font-medium leading-relaxed max-w-xl mb-10">
                        {{ strip_tags($event->short_description) }}
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        @if($isRegOpen)
                        <a href="#registrasi" class="group relative px-8 py-4 bg-slate-900 rounded-full text-white font-bold text-lg shadow-xl shadow-slate-900/20 hover:shadow-2xl hover:bg-brand-600 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <span class="relative z-10 flex items-center gap-2">
                                Amankan Slot
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                        </a>
                        @else
                        <button disabled class="px-8 py-4 bg-slate-200 text-slate-400 font-bold text-lg rounded-full cursor-not-allowed">Sold Out / Closed</button>
                        @endif
                        
                        <div class="flex -space-x-3 pl-4">
                           <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-200"></div>
                           <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-300"></div>
                           <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-400 flex items-center justify-center text-[10px] font-bold text-slate-600">+500</div>
                        </div>
                        <span class="text-sm font-semibold text-slate-500">Runners Joined</span>
                    </div>

                    <div class="grid grid-cols-3 gap-6 mt-16 border-t border-slate-200/60 pt-8">
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Date</p>
                            <p class="text-2xl font-black text-slate-900">{{ $event->start_at->format('d M') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Start</p>
                            <p class="text-2xl font-black text-brand-600">{{ $event->start_at->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Category</p>
                            <p class="text-2xl font-black text-slate-900">{{ $categories->count() }} <span class="text-base font-medium text-slate-400">Classes</span></p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 relative lg:h-[600px] flex items-center justify-center reveal-up delay-100">
                    <div class="relative w-full h-[500px] lg:h-full rounded-[2.5rem] overflow-hidden shadow-2xl shadow-blue-900/10 rotate-2 hover:rotate-0 transition duration-700 ease-out group">
                        @if($event->hero_image)
                            <img src="{{ asset('storage/' . $event->hero_image) }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                                <span class="text-slate-400 font-bold">Event Image</span>
                            </div>
                        @endif
                        
                        <div class="absolute bottom-6 left-6 right-6 bg-white/95 backdrop-blur-md p-6 rounded-3xl shadow-glass border border-white/50">
                             <p class="text-center text-xs font-bold uppercase text-slate-400 tracking-widest mb-3">Countdown to Race</p>
                             <div class="flex justify-between text-center" id="hero-countdown">
                                 <div><span class="text-2xl font-black text-slate-900 block leading-none" id="hc-days">00</span><span class="text-[10px] text-slate-500 font-bold uppercase">Days</span></div>
                                 <div class="text-slate-300 text-2xl font-light">:</div>
                                 <div><span class="text-2xl font-black text-slate-900 block leading-none" id="hc-hours">00</span><span class="text-[10px] text-slate-500 font-bold uppercase">Hrs</span></div>
                                 <div class="text-slate-300 text-2xl font-light">:</div>
                                 <div><span class="text-2xl font-black text-brand-600 block leading-none" id="hc-mins">00</span><span class="text-[10px] text-slate-500 font-bold uppercase">Min</span></div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 max-w-7xl mx-auto px-4">
             <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4 reveal-up">
                 <div>
                     <h2 class="text-4xl font-black text-slate-900">RACE <span class="text-brand-600">CATEGORIES</span></h2>
                     <p class="text-slate-500 mt-2 font-medium">Pilih tantangan lari Anda.</p>
                 </div>
                 <div class="hidden md:block h-px bg-slate-200 flex-grow ml-8 mb-4"></div>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 @foreach($categories as $cat)
                 <div class="group relative bg-white rounded-3xl p-1 shadow-card hover:shadow-xl transition-all duration-300 hover:-translate-y-1 reveal-up">
                     <div class="absolute inset-0 bg-gradient-to-br from-brand-50 to-white rounded-3xl transform rotate-1 group-hover:rotate-2 transition opacity-0 group-hover:opacity-100 -z-10"></div>
                     <div class="bg-white rounded-[1.3rem] p-8 h-full flex flex-col justify-between border border-slate-100 relative z-10">
                         <div>
                             <div class="flex justify-between items-start mb-4">
                                 <span class="bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-lg">{{ $cat->distance_km }}K</span>
                                 <span class="text-slate-300 group-hover:text-brand-600 transition">
                                     <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                 </span>
                             </div>
                             <h3 class="text-2xl font-black text-slate-900 mb-2">{{ $cat->name }}</h3>
                             <p class="text-sm text-slate-500 font-medium">Start: {{ $cat->start_time ? \Carbon\Carbon::parse($cat->start_time)->format('H:i') : 'TBA' }} WIB</p>
                         </div>
                         
                         <div class="mt-8 pt-6 border-t border-slate-50 space-y-3">
                             <div class="flex justify-between text-sm">
                                 <span class="text-slate-500">Cut Off Time</span>
                                 <span class="font-bold text-slate-900">{{ $cat->cot_hours }} Jam</span>
                             </div>
                             <div class="flex justify-between text-sm">
                                 <span class="text-slate-500">Min. Usia</span>
                                 <span class="font-bold text-slate-900">{{ $cat->min_age }} Thn</span>
                             </div>
                         </div>
                     </div>
                 </div>
                 @endforeach
             </div>
        </section>

        <section id="fasilitas" class="py-24 relative overflow-hidden">
            <div class="absolute inset-0 bg-slate-900 -z-20"></div>
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand-900 rounded-full blur-[100px] opacity-40 -z-10"></div>

            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center mb-16 reveal-up">
                    <span class="text-brand-500 font-bold tracking-widest text-xs uppercase">Official Facilities</span>
                    <h2 class="text-4xl md:text-5xl font-black text-white mt-3">PREMIUM AMENITIES</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    @php 
                        $facilities = $event->facilities ?? [];
                        if(empty($facilities)) {
                            $facilities = [
                                ['name' => 'Jersey', 'desc' => 'High quality dri-fit.', 'icon' => 'shirt'],
                                ['name' => 'Medal', 'desc' => 'Finisher exclusive.', 'icon' => 'medal'],
                                ['name' => 'Medic', 'desc' => 'Professional support.', 'icon' => 'heart'],
                                ['name' => 'Water', 'desc' => 'Hydration points.', 'icon' => 'water'],
                            ];
                        }
                    @endphp

                    @foreach($facilities as $f)
                    <div class="bg-white/5 backdrop-blur-lg border border-white/10 p-8 rounded-3xl hover:bg-white/10 transition duration-300 reveal-up text-center group">
                        <div class="w-14 h-14 mx-auto bg-brand-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-glow group-hover:scale-110 transition">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">{{ $f['name'] }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">{{ $f['description'] ?? $f['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        @include('events.partials.prizes-section', ['categories' => $categories])

        <section id="registrasi" class="py-24 bg-white relative">
            <div class="max-w-6xl mx-auto px-4">
                
                @if(!$isRegOpen)
                    <div class="text-center py-20 bg-slate-50 rounded-3xl border border-slate-200">
                         <h2 class="text-3xl font-bold text-slate-800">Registrasi Ditutup</h2>
                         <p class="text-slate-500 mt-2">Sampai jumpa di event berikutnya.</p>
                    </div>
                @else
                    <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="reveal-up">
                        @csrf

                        @if(request('payment') === 'pending')
                            <div class="mb-8 p-5 rounded-2xl bg-yellow-50 border border-yellow-200 text-yellow-900">
                                <div class="font-bold">Pembayaran masih pending</div>
                                <div class="text-sm text-slate-700 mt-1">Jika popup Midtrans tertutup/refresh, Anda bisa melanjutkan tanpa registrasi ulang.</div>
                                <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-block mt-3 bg-yellow-400 hover:bg-yellow-300 text-black font-bold px-4 py-2 rounded-xl">Lanjutkan Pembayaran</a>
                            </div>
                        @elseif(request('payment') === 'success')
                            <div class="mb-8 p-5 rounded-2xl bg-green-50 border border-green-200 text-green-900">
                                <div class="font-bold">Pembayaran berhasil</div>
                                <div class="text-sm text-slate-700 mt-1">Jika belum menerima konfirmasi, coba refresh beberapa saat lagi.</div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mb-8 p-5 rounded-2xl bg-red-50 border border-red-200 text-red-700">
                                <ul class="list-disc pl-5 space-y-1 text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                            
                            <div class="lg:col-span-8 space-y-10">
                                <div>
                                    <h2 class="text-3xl font-black text-slate-900 mb-2">Formulir Pendaftaran</h2>
                                    <p class="text-slate-500">Lengkapi data diri penanggung jawab dan peserta.</p>
                                </div>

                                <div class="bg-white rounded-3xl p-1">
                                    <div class="border-l-4 border-brand-600 pl-6 py-2 mb-6">
                                        <h3 class="text-lg font-bold text-slate-900">1. Data Penanggung Jawab</h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="group">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap</label>
                                            <input type="text" name="pic_name" value="{{ old('pic_name') }}" required class="input-premium w-full rounded-xl px-4 py-3 text-slate-900 outline-none" placeholder="Isi nama sesuai KTP">
                                        </div>
                                        <div class="group">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email</label>
                                            <input type="email" name="pic_email" value="{{ old('pic_email') }}" required class="input-premium w-full rounded-xl px-4 py-3 text-slate-900 outline-none" placeholder="email@contoh.com">
                                        </div>
                                        <div class="group md:col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor WhatsApp</label>
                                            <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required class="input-premium w-full rounded-xl px-4 py-3 text-slate-900 outline-none" placeholder="0812...">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-6 pl-6 border-l-4 border-brand-600 py-2">
                                        <h3 class="text-lg font-bold text-slate-900">2. Data Peserta</h3>
                                        <button type="button" id="addParticipant" class="text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 px-4 py-2 rounded-lg transition flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            TAMBAH PESERTA
                                        </button>
                                    </div>

                                    <div id="participantsWrapper" class="space-y-6">
                                        <div class="participant-item bg-white border border-slate-200 shadow-sm rounded-2xl p-6 relative hover:shadow-md transition duration-300" data-index="0">
                                            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="bg-slate-900 text-white text-xs font-bold px-2 py-1 rounded">PESERTA #1</span>
                                                    <button type="button" class="copy-pic-btn text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-1 rounded transition font-bold" onclick="copyFromPic(this)">
                                                        Isi Data PIC
                                                    </button>
                                                    <button type="button" class="copy-prev-btn text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-1 rounded transition font-bold hidden" onclick="copyFromPrev(this)">
                                                        Salin Peserta Sebelumnya
                                                    </button>
                                                </div>
                                                <button type="button" class="remove-participant hidden text-red-500 hover:text-red-700 text-xs font-bold">HAPUS</button>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kategori Lomba</label>
                                                    <select name="participants[0][category_id]" class="category-select input-premium w-full rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-900 outline-none cursor-pointer" data-index="0" required>
                                                        <option value="">-- Pilih Kategori --</option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}" data-price="{{ $cat->price_regular }}" data-price-regular="{{ $cat->price_regular }}">{{ $cat->name }} ({{ $cat->distance_km }}K)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Ukuran Jersey</label>
                                                    <select name="participants[0][jersey_size]" class="input-premium w-full rounded-lg px-3 py-2.5 text-sm text-slate-900 outline-none">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach(['XS','S','M','L','XL','XXL'] as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                                    </select>
                                                </div>
                                                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama</label>
                                                        <input type="text" name="participants[0][name]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Email</label>
                                                        <input type="email" name="participants[0][email]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. HP</label>
                                                    <input type="text" name="participants[0][phone]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900" minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ID Card (KTP/SIM)</label>
                                                    <input type="text" name="participants[0][id_card]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alamat</label>
                                                    <textarea name="participants[0][address]" required maxlength="500" rows="3" class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900"></textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Kontak Darurat</label>
                                                    <input type="text" name="participants[0][emergency_contact_name]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. Kontak Darurat</label>
                                                    <input type="text" name="participants[0][emergency_contact_number]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900" minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Gender</label>
                                                    <select name="participants[0][gender]" required class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900">
                                                        <option value="male">Laki-laki</option>
                                                        <option value="female">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Target Waktu (Opt)</label>
                                                    <input type="text" name="participants[0][target_time]" placeholder="J:M:D" class="input-premium w-full rounded-lg px-3 py-2 text-sm text-slate-900">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-4">
                                <div class="sticky top-28">
                                    <div class="bg-slate-900 text-white rounded-3xl p-6 shadow-2xl shadow-slate-900/30 relative overflow-hidden">
                                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>

                                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            Ringkasan Order
                                        </h3>

                                        <div class="space-y-4 mb-8 text-sm">
                                            <!-- Coupon Section -->
                                            <div class="mb-4">
                                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Kode Promo</label>
                                                <div class="flex gap-2">
                                                    <input type="text" id="coupon_code" placeholder="KODE..." class="flex-1 bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-sm text-white focus:bg-white/20 outline-none transition uppercase font-bold placeholder-slate-500">
                                                    <button type="button" id="applyCouponBtn" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-brand-600 transition shadow-glow">Pakai</button>
                                                </div>
                                                <div id="couponMessage" class="mt-2 text-xs font-medium"></div>
                                                <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                                            </div>

                                            <div class="flex justify-between text-slate-400">
                                                <span>Subtotal</span>
                                                <span id="subtotal" class="text-white font-mono">Rp 0</span>
                                            </div>
                                            
                                            <div id="discountRow" class="flex justify-between text-green-400 hidden">
                                                <span>Diskon</span>
                                                <span id="discountAmount" class="font-mono">-Rp 0</span>
                                            </div>

                                            <div id="feeRow" class="flex justify-between text-slate-400 {{ $event->platform_fee > 0 ? '' : 'hidden' }}">
                                                <span>Biaya Layanan</span>
                                                <span id="feeAmount" class="text-white font-mono">Rp 0</span>
                                            </div>

                                            <div class="h-px bg-white/10 w-full"></div>
                                            <div class="flex justify-between items-end">
                                                <span class="font-bold text-lg">Total</span>
                                                <span id="totalAmount" class="font-black text-2xl text-brand-400">Rp 0</span>
                                            </div>
                                        </div>

                                        @if($event->terms_and_conditions)
                                        <div class="mb-6">
                                            <label class="flex items-start gap-3 cursor-pointer group">
                                                <input type="checkbox" name="terms_agreed" required class="mt-1 w-4 h-4 rounded bg-white/10 border-white/20 text-brand-600 focus:ring-offset-slate-900 cursor-pointer">
                                                <span class="text-xs text-slate-400 group-hover:text-white transition">
                                                    Saya setuju dengan <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="text-brand-400 hover:underline">Syarat & Ketentuan</button>.
                                                </span>
                                            </label>
                                        </div>
                                        @endif

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
                @endphp

                <div class="mb-6 space-y-3">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Metode Pembayaran</label>
                                            
                                            @if($showMidtrans)
                                            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-brand-600 transition bg-white">
                                                <input type="radio" name="payment_method" value="midtrans" class="w-4 h-4 text-brand-600 focus:ring-brand-600" {{ $showMidtrans && !$showMoota ? 'checked' : '' }} required>
                                                <div class="flex-1">
                                                    <span class="block text-sm font-bold text-slate-900">Otomatis (QRIS, VA, E-Wallet)</span>
                                                    <span class="text-[10px] text-slate-500">Verifikasi instan via Midtrans</span>
                                                </div>
                                            </label>
                                            @endif

                                            @if($showMoota)
                                            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-brand-600 transition bg-white">
                                                <input type="radio" name="payment_method" value="moota" class="w-4 h-4 text-brand-600 focus:ring-brand-600" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                                <div class="flex-1">
                                                    <span class="block text-sm font-bold text-slate-900">Transfer Bank (Moota)</span>
                                                    <span class="text-[10px] text-slate-500">Verifikasi Otomatis</span>
                                                </div>
                                            </label>
                                            @endif
                                        </div>

                                        @if(env('RECAPTCHA_SITE_KEY'))
                                            <div class="mb-6 flex justify-center">
                                                <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                                            </div>
                                        @endif

                                        <button type="submit" id="submitBtn" class="w-full py-4 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-2xl transition-all shadow-glow flex justify-center items-center gap-2 group">
                                            <span>Bayar Sekarang</span>
                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </button>
                                        
                                        <p class="text-[10px] text-center text-slate-500 mt-4 flex justify-center gap-2">
                                            <span>🔒 Secure Payment</span>
                                            <span>•</span>
                                            <span>Instant Confirm</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                @endif
            </div>
        </section>

        @if($event->terms_and_conditions)
        <div id="termsModal" class="fixed inset-0 z-[100] hidden">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl transform scale-100 transition-transform">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl">
                        <h3 class="text-lg font-bold text-slate-900">Syarat & Ketentuan</h3>
                        <button onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center transition">✕</button>
                    </div>
                    <div class="p-8 overflow-y-auto prose prose-slate prose-sm max-w-none">
                        {!! $event->terms_and_conditions !!}
                    </div>
                </div>
            </div>
        </div>
        @endif

        @include('events.partials.sponsor-carousel', [
            'gradientFrom' => 'from-[#fafafa]',
            'titleColor' => 'text-slate-400',
            'containerClass' => 'bg-white/60 backdrop-blur-md border border-white/50 shadow-glass',
            'sectionClass' => 'py-20 relative z-10'
        ])

    </main>

    <footer class="bg-white border-t border-slate-200 py-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="font-black text-slate-900 text-xl tracking-tighter">{{ strtoupper($event->name) }}</span>
                <span class="text-slate-400 text-sm">© {{ date('Y') }}</span>
            </div>
            <div class="flex gap-6 text-sm text-slate-500 font-medium">
                <a href="#" class="hover:text-brand-600 transition">Contact Support</a>
                <a href="#" class="hover:text-brand-600 transition">Privacy Policy</a>
            </div>
        </div>
    </footer>

    <script>
        // Navbar Float Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('nav-container');
            if(window.scrollY > 20) {
                nav.classList.add('bg-white/95', 'shadow-lg');
                nav.classList.remove('h-16');
                nav.classList.add('h-14'); // Shrink slightly
            } else {
                nav.classList.remove('bg-white/95', 'shadow-lg', 'h-14');
            }
        });

        // Add Copy Helpers to Global Scope
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

        document.getElementById('navToggle').addEventListener('click', () => {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        // Countdown
        const eventDateStr = "{{ $event->start_at->format('Y-m-d H:i:s') }}";
        if(eventDateStr) {
            const eventDate = new Date(eventDateStr).getTime();
            setInterval(() => {
                const now = new Date().getTime();
                const dist = eventDate - now;
                if(dist < 0) return;
                document.getElementById("hc-days").innerText = Math.floor(dist / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
                document.getElementById("hc-hours").innerText = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
                document.getElementById("hc-mins").innerText = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            }, 1000);
        }

        // Form Logic (Simplified from previous)
        const formatCurrency = (num) => new Intl.NumberFormat('id-ID').format(Math.round(num));
        const platformFee = {{ $event->platform_fee ?? 0 }};
        const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};
        const eventId = {{ $event->id }};
        const eventSlug = "{{ $event->slug }}";
        
        // --- Coupon Variables ---
        let appliedCoupon = null;
        let discountAmount = 0;
        
        // Add Participant & Calc logic here (Clone from previous snippet, but ensure classes match new UI)
        // ... [Insert JS Logic for Form Clone and Calc here same as before] ...
        
        // Quick Fix for Add Participant to use styling
        const wrapper = document.getElementById('participantsWrapper');
        const btnAdd = document.getElementById('addParticipant');
        
        function resetCoupon() {
            if (appliedCoupon || discountAmount > 0) {
                appliedCoupon = null;
                discountAmount = 0;
                document.getElementById('coupon_code').value = '';
                document.getElementById('coupon_code_hidden').value = '';
                document.getElementById('couponMessage').innerHTML = '';
            }
        }

        if(wrapper && btnAdd) {
            const template = wrapper.querySelector('.participant-item').cloneNode(true);
            let pIndex = 1;

            btnAdd.addEventListener('click', () => {
                resetCoupon();
                const clone = template.cloneNode(true);
                clone.setAttribute('data-index', pIndex);
                clone.querySelector('span').innerText = `PESERTA #${pIndex + 1}`;

                // Show/Hide Copy Prev Button
                const copyPrevBtn = clone.querySelector('.copy-prev-btn');
                if (copyPrevBtn) {
                    if (pIndex > 0) {
                        copyPrevBtn.classList.remove('hidden');
                    } else {
                        copyPrevBtn.classList.add('hidden');
                    }
                }
                
                // Reset inputs
                clone.querySelectorAll('input').forEach(i => i.value = '');
                clone.querySelectorAll('select').forEach(s => {
                    s.selectedIndex = 0;
                    if(s.classList.contains('category-select')) {
                        s.setAttribute('data-index', pIndex);
                        s.addEventListener('change', updateCalc);
                    }
                });
                
                // Update names
                clone.querySelectorAll('[name]').forEach(el => {
                    const name = el.getAttribute('name');
                    el.setAttribute('name', name.replace(/\[\d+\]/, `[${pIndex}]`));
                });

                clone.querySelector('.remove-participant').classList.remove('hidden');
                clone.querySelector('.remove-participant').addEventListener('click', function(){
                    this.closest('.participant-item').remove();
                    updateCalc();
                });

                wrapper.appendChild(clone);
                pIndex++;
            });

            // Initial Listener
            document.querySelectorAll('.category-select').forEach(el => el.addEventListener('change', () => {
                resetCoupon();
                updateCalc();
            }));
        }

        function updateCalc() {
            const categoryCounts = new Map();
            const categoryPrices = new Map();
            let count = 0;

            document.querySelectorAll('.category-select').forEach(el => {
                const opt = el.options[el.selectedIndex];
                if (!opt.value) return;
                count++;
                const categoryId = String(opt.value);
                const price = parseFloat(opt.dataset.price || 0);
                categoryCounts.set(categoryId, (categoryCounts.get(categoryId) || 0) + 1);
                categoryPrices.set(categoryId, price);
            });

            let subtotal = 0;
            categoryCounts.forEach((qty, categoryId) => {
                const price = categoryPrices.get(categoryId) || 0;
                let paidQty = qty;
                if (promoBuyX > 0) {
                    const bundleSize = promoBuyX + 1;
                    const freeCount = Math.floor(qty / bundleSize);
                    paidQty = qty - freeCount;
                }
                subtotal += price * paidQty;
            });

            const totalFee = count * platformFee;
            let finalTotal = subtotal + totalFee - discountAmount;
            if(finalTotal < 0) finalTotal = 0;

            document.getElementById('subtotal').innerText = 'Rp ' + formatCurrency(subtotal);
            
            if(discountAmount > 0) {
                document.getElementById('discountRow').classList.remove('hidden');
                document.getElementById('discountAmount').innerText = '- Rp ' + formatCurrency(discountAmount);
            } else {
                document.getElementById('discountRow').classList.add('hidden');
            }
            
            if (platformFee > 0) {
                document.getElementById('feeAmount').innerText = 'Rp ' + formatCurrency(totalFee);
            }
            
            document.getElementById('totalAmount').innerText = 'Rp ' + formatCurrency(finalTotal);
        }

        // Coupon Logic
        const couponBtn = document.getElementById('applyCouponBtn');
        if(couponBtn) {
            couponBtn.addEventListener('click', () => {
                const code = document.getElementById('coupon_code').value;
                if(!code) { alert('Masukkan kode kupon'); return; }

                let subtotal = 0;
                const categoryCounts = new Map();
                const categoryPrices = new Map();
                document.querySelectorAll('.category-select').forEach(el => {
                    const opt = el.options[el.selectedIndex];
                    if (!opt.value) return;
                    const categoryId = String(opt.value);
                    const price = parseFloat(opt.dataset.price || 0);
                    categoryCounts.set(categoryId, (categoryCounts.get(categoryId) || 0) + 1);
                    categoryPrices.set(categoryId, price);
                });
                categoryCounts.forEach((qty, categoryId) => {
                    const price = categoryPrices.get(categoryId) || 0;
                    let paidQty = qty;
                    if (promoBuyX > 0) {
                        const bundleSize = promoBuyX + 1;
                        const freeCount = Math.floor(qty / bundleSize);
                        paidQty = qty - freeCount;
                    }
                    subtotal += price * paidQty;
                });

                if(subtotal === 0) {
                    alert("Pilih kategori peserta terlebih dahulu"); return;
                }

                const originalText = couponBtn.innerHTML;
                couponBtn.innerHTML = '...';
                couponBtn.disabled = true;

                fetch(`{{ route('events.register.coupon', $event->slug) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ event_id: eventId, coupon_code: code, total_amount: subtotal })
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        appliedCoupon = data.coupon;
                        discountAmount = data.discount_amount;
                        document.getElementById('coupon_code_hidden').value = data.coupon.code;
                        document.getElementById('coupon_code').value = data.coupon.code;
                        document.getElementById('couponMessage').innerHTML = '<span class="text-green-400">Kupon berhasil digunakan!</span>';
                        updateCalc();
                    } else {
                        document.getElementById('couponMessage').innerHTML = `<span class="text-red-400">${data.message}</span>`;
                        discountAmount = 0;
                        document.getElementById('coupon_code_hidden').value = '';
                        updateCalc();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal memproses kupon');
                })
                .finally(() => {
                    couponBtn.innerHTML = originalText;
                    couponBtn.disabled = false;
                });
            });
        }

        // Intersection Observer for Reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('active');
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));

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
</body>
</html>
