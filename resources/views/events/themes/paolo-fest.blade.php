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

    <!-- Favicon default -->
    <link rel="icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/paolo/favicon-32x32.png') }}" type="image/x-icon">

    <!-- Versi PNG -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/paolo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/paolo/favicon-16x16.png') }}">

    <!-- Versi Apple Touch -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/paolo/apple-touch-icon.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="app-url" content="{{ url('/') }}" />
    <script>
        window.APP_URL = @json(url('/'));
        window.rlUrl = function(path) {
            var base = window.APP_URL || (window.location.origin || '');
            var normalizedBase = String(base).replace(/\/+$/, '') + '/';
            var normalizedPath = String(path || '').replace(/^\/+/, '');
            return new URL(normalizedPath, normalizedBase).toString();
        };
    </script>
    @if(env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php
        $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
        $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
    @endphp
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    
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
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
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
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Styles */
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .animate-heartbeat {
            animation: heartbeat 1.5s ease-in-out infinite;
            display: inline-block;
        }

        body { background-color: #F8FAFC; color: #1e293b; overflow-x: hidden; }

        /* Validation Styles */
        .input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            transition: all 0.3s ease;
        }
        .input-success {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
            transition: all 0.3s ease;
        }
        .validation-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            margin-top: 0.35rem;
            animation: fadeIn 300ms ease-in-out;
            font-weight: 600;
        }
        .validation-message.error {
            color: #ef4444;
        }
        .validation-message.success {
            color: #22c55e;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
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
    <!-- Custom Validation Styles -->
    <style>
        .input-error {
            border-color: #ef4444 !important; /* red-500 */
            background-color: #fef2f2 !important; /* red-50 */
            transition: all 0.3s ease;
        }
        .input-success {
            border-color: #22c55e !important; /* green-500 */
            transition: all 0.3s ease;
        }
        .validation-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem; /* text-xs */
            margin-top: 0.35rem;
            animation: fadeIn 300ms ease-in-out;
            font-weight: 600;
        }
        .validation-message.error {
            color: #ef4444; /* red-500 */
        }
        .validation-message.success {
            color: #22c55e; /* green-500 */
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
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

    @if(!$isRegOpen)
    <!-- Maintenance / Coming Soon Overlay -->
    <div class="fixed inset-0 z-[100] bg-slate-900 overflow-y-auto overflow-x-hidden custom-scrollbar">
        <!-- Background -->
        <div class="fixed inset-0 z-0 pointer-events-none">
             @if($event->hero_image)
                <img src="{{ asset('storage/' . $event->hero_image) }}" class="w-full h-full object-cover opacity-20 blur-sm scale-105 animate-pulse-slow">
            @else
                <div class="w-full h-full bg-slate-900"></div>
                <div class="absolute top-0 -left-4 w-96 h-96 bg-brand-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute bottom-0 -right-4 w-96 h-96 bg-accent-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-b from-slate-900/50 via-slate-900/90 to-slate-900"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center py-12 px-4 md:px-6 text-center">
            
            <!-- Logo/Name -->
            <div class="animate-fade-in-up">
                @if($event->logo_image)
                    <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-20 md:h-32 w-auto mx-auto mb-6 drop-shadow-2xl hover:scale-105 transition-transform duration-500">
                @else
                    <h1 class="text-4xl md:text-6xl font-black tracking-tighter mb-4 text-white">{{ $event->name }}</h1>
                @endif
            </div>

            <!-- Status Badge -->
            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/5 border border-white/10 backdrop-blur-md mb-8 animate-fade-in-up delay-100 shadow-lg hover:bg-white/10 transition">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accent-500"></span>
                </span>
                <span class="text-white font-bold uppercase tracking-widest text-xs md:text-sm">
                    @if($event->registration_open_at && $now < $event->registration_open_at)
                        COMING SOON
                    @else
                        REGISTRATION CLOSED
                    @endif
                </span>
            </div>

            <!-- Date & Location -->
             <div class="space-y-2 mb-10 animate-fade-in-up delay-200">
                <h2 class="text-2xl md:text-4xl font-black text-white drop-shadow-lg">
                    {{ $event->start_at->format('d F Y') }}
                </h2>
                <p class="text-lg md:text-xl text-slate-400 flex items-center justify-center gap-2 font-light">
                    <i class="fas fa-map-marker-alt text-accent-500"></i> {{ $event->location_name }}
                </p>
             </div>

            <!-- Countdown -->
            @if($event->registration_open_at && $now < $event->registration_open_at)
                <div class="mb-12 animate-fade-in-up delay-300 w-full max-w-3xl">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-6">Pendaftaran Dibuka Dalam</p>
                    <div class="grid grid-cols-4 gap-3 md:gap-6">
                        @foreach(['Hari' => 'days', 'Jam' => 'hours', 'Menit' => 'minutes', 'Detik' => 'seconds'] as $label => $id)
                        <div class="flex flex-col items-center">
                            <div class="w-full aspect-square max-w-[5rem] md:max-w-[6rem] rounded-2xl bg-slate-800/50 border border-white/10 backdrop-blur-md flex items-center justify-center mb-2 shadow-xl">
                                <span class="text-2xl md:text-4xl font-black text-white font-mono" id="m-{{ $id }}">00</span>
                            </div>
                            <span class="text-[10px] md:text-xs text-slate-500 uppercase font-bold tracking-widest">{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- NEW: Featured / Categories Section -->
            <div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-8 mb-12 animate-fade-in-up delay-400 text-left">
                
                <!-- Categories -->
                <div class="bg-slate-800/30 border border-white/5 rounded-3xl p-6 backdrop-blur-sm">
                    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fas fa-running text-brand-500"></i> Kategori Lomba
                    </h3>
                    <div class="space-y-3">
                        @foreach($categories as $cat)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/10 transition border border-white/5">
                            <span class="font-bold text-slate-200">{{ $cat->name }}</span>
                            <span class="text-xs font-mono text-white border border-white/20 px-2 py-1 rounded">
                                {{ $cat->distance_km ?? 0 }}K
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Featured / Highlights -->
                <div class="bg-slate-800/30 border border-white/5 rounded-3xl p-6 backdrop-blur-sm">
                    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fas fa-star text-yellow-500"></i> Featured
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                         <div class="aspect-video rounded-xl bg-gradient-to-br from-brand-900 to-slate-900 border border-white/5 flex flex-col items-center justify-center p-4 text-center group">
                            <i class="fas fa-tshirt text-3xl text-white/20 mb-2 group-hover:text-white/50 transition"></i>
                            <span class="text-xs font-bold text-slate-300">Exclusive Jersey</span>
                         </div>
                         <div class="aspect-video rounded-xl bg-gradient-to-br from-accent-900 to-slate-900 border border-white/5 flex flex-col items-center justify-center p-4 text-center group">
                            <i class="fas fa-medal text-3xl text-white/20 mb-2 group-hover:text-white/50 transition"></i>
                            <span class="text-xs font-bold text-slate-300">Finisher Medal</span>
                         </div>
                         <div class="col-span-2 p-3 rounded-xl bg-white/5 border border-white/5 text-xs text-slate-400 leading-relaxed">
                            Dapatkan pengalaman lari terbaik dengan rute menantang dan pemandangan spektakuler.
                         </div>
                    </div>
                </div>
            </div>

            <!-- Footer / Contact -->
             <div class="animate-fade-in-up delay-500 pb-8">
                <p class="text-slate-500 text-sm mb-4 font-medium">Ikuti kami untuk update terbaru</p>
                <div class="flex justify-center gap-4">
                    <a href="https://www.instagram.com/paolorunfest/" target="_blank" class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-gradient-to-r hover:from-purple-600 hover:to-pink-600 hover:border-transparent transition-all duration-300 shadow-lg hover:shadow-purple-500/20">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="http://wa.me/6287866950667" target="_blank" class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-green-600 hover:border-transparent transition-all duration-300 shadow-lg hover:shadow-green-500/20">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($event->registration_open_at && $now < $event->registration_open_at)
    <script>
        (function() {
            // Use server-side timestamp to avoid client-side timezone parsing issues
            const target = {{ $event->registration_open_at->timestamp * 1000 }};
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = target - now;
                
                if (distance < 0) {
                    location.reload(); 
                    return;
                }
                
                document.getElementById('m-days').innerText = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
                document.getElementById('m-hours').innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
                document.getElementById('m-minutes').innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
                document.getElementById('m-seconds').innerText = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');
            }
            
            setInterval(updateCountdown, 1000);
            updateCountdown();
        })();
    </script>
    @endif
    @endif

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
                    <a href="#participants-list" class="nav-link text-sm font-semibold text-white/90 hover:text-white transition">Daftar Peserta</a>
                    
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
                <a href="#participants-list" class="block text-slate-700 font-medium p-2 hover:bg-slate-50 rounded-lg">Daftar Peserta</a>                
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
                @foreach(array_slice($event->gallery, 1, 8) as $index => $img)
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

                        <h3 class="text-3xl font-bold text-slate-900 mb-2">{{ $cat->name }} Umum/Master</h3>
                        <p class="text-slate-500 text-sm mb-8 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Cut Off Time: <strong class="text-slate-900">{{ $cat->cutoff_minutes ? round($cat->cutoff_minutes/60, 1) : '-' }} Jam</strong>
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
                                Goodie Bag
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
    <section id="routes" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Race Course</span>
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl mt-2">Peta Rute</h2>
                <p class="text-slate-500 mt-4 max-w-2xl mx-auto">Jelajahi rute lari untuk setiap kategori. Klik tab kategori untuk melihat detail rute dan elevasi.</p>
                <div class="mt-6 flex justify-center">
                    <button type="button" id="openPredictorBtn" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-slate-900 text-white font-bold shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition">
                        Prediksi Waktu
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </div>
            </div>

            <div id="predictorModal" class="fixed inset-0 z-[70] hidden">
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
                <div class="relative h-full w-full flex items-center justify-center p-4">
                    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                        <div class="flex items-start justify-between gap-4 p-6 border-b border-slate-100">
                            <div>
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Prediksi Waktu</div>
                                <div class="text-2xl font-black text-slate-900">{{ $event->name }}</div>
                                <a href="{{ route('events.prediction', $event->slug) }}" class="inline-block mt-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                                    Buka Halaman Penuh →
                                </a>
                            </div>
                            <button type="button" id="closePredictorBtn" class="p-2 rounded-xl hover:bg-slate-100 text-slate-700">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-1">
                                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                        <div class="text-lg font-black text-slate-900">Input</div>

                                        <div class="mt-4">
                                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori</label>
                                            <select id="predictCategory" class="mt-2 w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-semibold">
                                                <option value="" disabled selected>Pilih kategori</option>
                                                @foreach($categoriesWithGpx as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ (float) ($cat->distance_km ?? 0) }} KM)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-5">
                                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cuaca</label>
                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="predictWeather" value="panas" class="peer sr-only" checked>
                                                    <div class="px-4 py-3 rounded-xl border border-slate-200 bg-white peer-checked:border-yellow-400 peer-checked:bg-yellow-500/10 font-bold text-slate-900">Cerah</div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="predictWeather" value="dingin" class="peer sr-only">
                                                    <div class="px-4 py-3 rounded-xl border border-slate-200 bg-white peer-checked:border-cyan-400 peer-checked:bg-cyan-500/10 font-bold text-slate-900">Dingin</div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="predictWeather" value="hujan" class="peer sr-only">
                                                    <div class="px-4 py-3 rounded-xl border border-slate-200 bg-white peer-checked:border-blue-400 peer-checked:bg-blue-500/10 font-bold text-slate-900">Hujan</div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="predictWeather" value="gerimis" class="peer sr-only">
                                                    <div class="px-4 py-3 rounded-xl border border-slate-200 bg-white peer-checked:border-sky-400 peer-checked:bg-sky-500/10 font-bold text-slate-900">Gerimis</div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mt-5">
                                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">PB (Jam / Menit / Detik)</label>
                                            <div class="mt-2 grid grid-cols-3 gap-2">
                                                <input id="predictPbH" type="number" min="0" max="23" value="0" class="bg-white border border-slate-200 rounded-xl px-3 py-3 text-slate-900 font-semibold" placeholder="Jam">
                                                <input id="predictPbM" type="number" min="0" max="59" value="0" class="bg-white border border-slate-200 rounded-xl px-3 py-3 text-slate-900 font-semibold" placeholder="Menit">
                                                <input id="predictPbS" type="number" min="0" max="59" value="0" class="bg-white border border-slate-200 rounded-xl px-3 py-3 text-slate-900 font-semibold" placeholder="Detik">
                                            </div>
                                        </div>

                                        <div class="mt-5">
                                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal PB (3 bulan terakhir)</label>
                                            <input id="predictPbDate" type="date" class="mt-2 w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-semibold">
                                        </div>

                                        <p id="predictModalError" class="mt-4 text-red-600 text-sm hidden"></p>

                                        <button id="predictModalBtn" type="button" class="mt-5 w-full px-5 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-black transition">
                                            Prediksi Waktu
                                        </button>
                                    </div>
                                </div>

                                <div class="lg:col-span-2 space-y-6">
                                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                        <div class="text-lg font-black text-slate-900">Hasil Prediksi</div>

                                        <div id="predictResultWrap" class="mt-4 hidden">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                                    <div class="text-slate-500 text-xs font-bold uppercase">Optimis</div>
                                                    <div class="text-slate-900 font-black mt-1 text-2xl" id="predictOptimistic">-</div>
                                                </div>
                                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                                    <div class="text-slate-500 text-xs font-bold uppercase">Realistis</div>
                                                    <div class="text-slate-900 font-black mt-1 text-2xl" id="predictRealistic">-</div>
                                                </div>
                                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                                    <div class="text-slate-500 text-xs font-bold uppercase">Pesimis</div>
                                                    <div class="text-slate-900 font-black mt-1 text-2xl" id="predictPessimistic">-</div>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                                    <div class="text-slate-500 text-xs font-bold uppercase">Confidence</div>
                                                    <div class="text-slate-900 font-black mt-1" id="predictConfidence">-</div>
                                                </div>
                                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                                    <div class="text-slate-500 text-xs font-bold uppercase">Jarak Rute</div>
                                                    <div class="text-slate-900 font-black mt-1" id="predictRouteDistance">-</div>
                                                </div>
                                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                                    <div class="text-slate-500 text-xs font-bold uppercase">Elev Gain</div>
                                                    <div class="text-slate-900 font-black mt-1" id="predictRouteGain">-</div>
                                                </div>
                                            </div>

                                            <div class="mt-4 bg-white border border-slate-200 rounded-xl p-4">
                                                <div class="text-slate-500 text-xs font-bold uppercase">Strategi</div>
                                                <div class="text-slate-800 font-semibold mt-2 leading-relaxed" id="predictStrategy">-</div>
                                            </div>
                                        </div>

                                        <div id="predictEmptyHint" class="mt-4 text-slate-500 font-semibold">
                                            Pilih kategori dan isi PB untuk melihat hasil prediksi.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" onclick="openLightbox('{{ asset('storage/' . $event->jersey_image) }}')" class="w-full h-auto object-cover hover:scale-105 transition duration-700 cursor-pointer" loading="lazy">
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
                            <p class="text-slate-500">Sistem pencatatan waktu tidak menggunakan chip, melainkan dilakukan secara manual oleh juri yang adil, berpengalaman, dan profesional untuk memastikan hasil podium runner akurat serta transparan.</p>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-start gap-6 hover:shadow-md transition reveal delay-200">
                        <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Water Station</h4>
                            <p class="text-slate-500">Setiap 2.5 KM tersedia pos hidrasi lengkap dengan air minum segar dan tim medis siaga, sehingga peserta tetap terjaga stamina dan keselamatannya sepanjang lomba.</p>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-start gap-6 hover:shadow-md transition reveal delay-300">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-bold text-slate-900 mb-4">Dokumen & Administrasi Peserta</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a href="https://res.cloudinary.com/dslfarxct/raw/upload/v1769990790/Surat-Izin-Orang-Tua_k4iavi.docx" class="group flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-100 hover:border-emerald-100 transition-all">
                                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-slate-400 group-hover:text-emerald-600 shadow-sm shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-700 group-hover:text-emerald-700 leading-tight">Surat Izin Orang Tua</div>
                                        <div class="text-[10px] text-slate-500 group-hover:text-emerald-600 mt-0.5">Download .docx</div>
                                    </div>
                                </a>
                                <a href="https://res.cloudinary.com/dslfarxct/raw/upload/v1769990790/Surat-Kuasa-Paolorunfest_thhu6w.docx" class="group flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-100 hover:border-emerald-100 transition-all">
                                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-slate-400 group-hover:text-emerald-600 shadow-sm shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-700 group-hover:text-emerald-700 leading-tight">Surat Kuasa RCP</div>
                                        <div class="text-[10px] text-slate-500 group-hover:text-emerald-600 mt-0.5">Download .docx</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-start gap-6 hover:shadow-md transition reveal delay-300 hidden">
                        <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Race Photography</h4>
                            <p class="text-slate-500">Momen terbaik Anda akan diabadikan oleh tim dokumentasi profesional dari fotoyu, menghadirkan foto berkualitas tinggi di berbagai titik sepanjang rute lomba.</p>
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

            <!-- Race Rules Trigger -->
            <div class="mt-20 text-center reveal delay-300">
                <button onclick="document.getElementById('rulesModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-full hover:bg-slate-800 transition font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Baca Peraturan Lomba Lengkap
                </button>
            </div>
        </div>
    </section>

    <!-- Race Rules Modal -->
    <div id="rulesModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" onclick="document.getElementById('rulesModal').classList.add('hidden')"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                    <div class="bg-slate-900 px-4 py-6 sm:px-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold leading-6 text-white flex items-center gap-2" id="modal-title">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Peraturan Lomba
                        </h3>
                        <button type="button" class="text-slate-400 hover:text-white transition" onclick="document.getElementById('rulesModal').classList.add('hidden')">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="px-4 py-6 sm:p-8 max-h-[70vh] overflow-y-auto bg-slate-50">
                        <div class="space-y-8">
                            <!-- Waktu Penyelesaian -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">01</span>
                                    Waktu Penyelesaian (Cut-off Time)
                                </h4>
                                <p class="text-slate-600 pl-10">Peserta wajib menyelesaikan rute 5K dalam waktu maksimal <strong>1,5 jam</strong>. Peserta yang melewati batas waktu tidak berhak atas medali penamat.</p>
                            </div>

                            <!-- DNS -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-sm">02</span>
                                    DNS (Did Not Start)
                                </h4>
                                <p class="text-slate-600 pl-10">Peserta yang tidak memulai perlombaan (DNS) tidak berhak menerima medali penamat.</p>
                            </div>

                            <!-- BIB -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">03</span>
                                    Nomor Peserta (BIB)
                                </h4>
                                <div class="text-slate-600 pl-10 space-y-2">
                                    <p>BIB adalah identitas resmi peserta lomba.</p>
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Dilarang memindahkan atau menjual BIB kepada orang lain; pelanggaran akan berakibat diskualifikasi.</li>
                                        <li>BIB harus selalu terlihat jelas oleh panitia dan marshal. Panitia berhak meminta peserta memperbaiki posisi BIB saat berlari.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Sportivitas -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-sm">04</span>
                                    Sportivitas & Kecurangan
                                </h4>
                                <div class="text-slate-600 pl-10 space-y-2">
                                    <p>Peserta yang mengganggu, menghadang, atau mendorong peserta lain akan langsung didiskualifikasi.</p>
                                    <p>Peserta yang memperpendek jarak, menggunakan kendaraan, atau melakukan pelanggaran lain terhadap ketentuan lomba akan didiskualifikasi.</p>
                                </div>
                            </div>

                            <!-- Water Station & Kendaraan -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                    <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                        <span class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">05</span>
                                        Water Station
                                    </h4>
                                    <p class="text-slate-600 pl-10">Pos minum tersedia di titik-titik yang telah ditentukan sepanjang rute.</p>
                                </div>
                                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                    <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm">06</span>
                                        Larangan Kendaraan
                                    </h4>
                                    <p class="text-slate-600 pl-10">Penggunaan kendaraan bermotor maupun sepeda di jalur lomba sangat dilarang, kecuali untuk kepentingan penyelenggaraan.</p>
                                </div>
                            </div>

                            <!-- Medali & Pemenang -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center text-sm">07</span>
                                    Medali & Pemenang
                                </h4>
                                <div class="text-slate-600 pl-10 space-y-2">
                                    <p><strong>Medali:</strong> Peserta yang melewati garis finis sebelum cut-off time berhak menerima medali penamat.</p>
                                    <p><strong>Pemenang:</strong> Ditentukan oleh peserta yang pertama menyentuh garis finis. Hadiah diserahkan setelah verifikasi syarat dan aturan.</p>
                                </div>
                            </div>

                            <!-- Keamanan -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h4 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-sm">08</span>
                                    Keamanan & Kesehatan
                                </h4>
                                <div class="text-slate-600 pl-10 space-y-2">
                                    <p>Keamanan jalan raya berada di bawah pengendalian pihak Kepolisian dan Dinas Perhubungan setempat.</p>
                                    <p>Layanan kesehatan disediakan panitia. Petugas kesehatan berhak menghentikan peserta dari lomba apabila kondisi kesehatan tidak memungkinkan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition" onclick="document.getElementById('rulesModal').classList.add('hidden')">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- What to Bring & FAQ -->
    <section id="faq" class="py-24 bg-slate-50">
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
                @php
                    $faqs = isset($event->premium_amenities['faq']['items']) ? $event->premium_amenities['faq']['items'] : [];
                    $isFaqEnabled = isset($event->premium_amenities['faq']['enabled']) ? $event->premium_amenities['faq']['enabled'] : false;
                @endphp

                @if($isFaqEnabled && !empty($faqs))
                <div class="reveal delay-200">
                    <h3 class="text-2xl font-bold text-slate-900 mb-8">FAQ</h3>
                    <div class="space-y-4">
                        @foreach($faqs as $faq)
                        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                            <button class="w-full px-6 py-4 text-left font-bold text-slate-900 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                {{ $faq['question'] }}
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="px-6 pb-4 text-sm text-slate-600 hidden">
                                {!! nl2br(e($faq['answer'])) !!}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>

    <section id="prizes" class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Hadiah Pemenang</h2>
                <p class="mt-4 text-slate-600">Total hadiah puluhan juta rupiah menanti para juara.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 reveal delay-100">
                <!-- Category 1 --> 
                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 text-center border-b border-slate-200 pb-4">
                        KATEGORI 5K UMUM <br>
                        <span class="text-brand-600 text-base font-medium">(PRIA & WANITA)</span>
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left">
                                    <th class="pb-3 text-sm font-semibold text-slate-500 uppercase tracking-wider">Peringkat</th>
                                    <th class="pb-3 text-sm font-semibold text-slate-500 uppercase tracking-wider text-right">Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-bold text-slate-900">Juara 1</td>
                                    <td class="py-3 font-bold text-brand-600 text-right">Rp 2.000.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-bold text-slate-900">Juara 2</td>
                                    <td class="py-3 font-bold text-brand-600 text-right">Rp 1.750.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-bold text-slate-900">Juara 3</td>
                                    <td class="py-3 font-bold text-brand-600 text-right">Rp 1.500.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-medium text-slate-700">Peringkat 4</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 1.250.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-medium text-slate-700">Peringkat 5</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 1.000.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-medium text-slate-700">Peringkat 6</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 500.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-medium text-slate-700">Peringkat 7</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 500.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-700">Peringkat 8</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 500.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Category 2 -->
                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 text-center border-b border-slate-200 pb-4">
                        KATEGORI 5K MASTER >45 TH <br>
                        <span class="text-brand-600 text-base font-medium">(PRIA & WANITA)</span>
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left">
                                    <th class="pb-3 text-sm font-semibold text-slate-500 uppercase tracking-wider">Peringkat</th>
                                    <th class="pb-3 text-sm font-semibold text-slate-500 uppercase tracking-wider text-right">Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-bold text-slate-900">Juara 1</td>
                                    <td class="py-3 font-bold text-brand-600 text-right">Rp 1.250.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-bold text-slate-900">Juara 2</td>
                                    <td class="py-3 font-bold text-brand-600 text-right">Rp 1.000.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-bold text-slate-900">Juara 3</td>
                                    <td class="py-3 font-bold text-brand-600 text-right">Rp 750.000</td>
                                </tr>
                                <tr class="border-b border-slate-200/60">
                                    <td class="py-3 font-medium text-slate-700">Peringkat 4</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 500.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-700">Peringkat 5</td>
                                    <td class="py-3 font-medium text-slate-700 text-right">Rp 500.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Participants Table Section -->
    @if($hasPaidParticipants ?? false)
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
                <p class="mt-4 text-slate-600">Pastikan data yang Anda masukkan sesuai dengan identitas (KTP/SIM). </p>
                <div class="flex justify-center">
              <button type="button" onclick="openLightbox('https://res.cloudinary.com/dslfarxct/image/upload/v1769987180/Panduan_trajpm.webp', true)" 
                    class="text-xs font-bold text-brand-600 hover:text-brand-700 mt-1.5 flex items-center gap-1 ml-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Panduan Registrasi
            </button>
            </div>

            </div>

            @if(!$isRegOpen)
                <div class="bg-slate-50 border-2 border-slate-200 rounded-3xl p-12 text-center max-w-2xl mx-auto">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Pendaftaran Ditutup</h3>
                    <p class="text-slate-500">Mohon maaf, pendaftaran saat ini tidak tersedia.</p>
                </div>
            @else
                @if(request('payment') === 'pending')
                    <div class="w-full mb-8 bg-yellow-50 border border-yellow-200 text-yellow-900 p-6 rounded-3xl reveal">
                        <div class="flex flex-col md:flex-row md:items-start gap-5">
                            <div class="shrink-0 text-yellow-600 bg-yellow-100 p-3 rounded-2xl">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-xl mb-1">Menunggu Pembayaran</h3>
                                <p class="text-slate-700 leading-relaxed">Anda memiliki transaksi yang belum diselesaikan. Jika popup pembayaran sebelumnya tertutup atau hilang, Anda dapat melanjutkannya sekarang tanpa perlu mengisi data ulang.</p>
                                <div class="mt-4">
                                    <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-300 text-black font-bold px-6 py-3 rounded-xl transition shadow-lg shadow-yellow-400/20 hover:shadow-yellow-400/40 hover:-translate-y-0.5">
                                        <span>Lanjutkan Pembayaran</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif(request('payment') === 'success')
                    <div class="w-full mb-8 bg-green-50 border border-green-200 text-green-900 p-6 rounded-3xl reveal">
                        <div class="flex items-center gap-5">
                            <div class="shrink-0 text-green-600 bg-green-100 p-3 rounded-2xl">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl mb-1">Pembayaran Berhasil!</h3>
                                <p class="text-slate-700">Terima kasih telah mendaftar. Silakan cek email Anda (inbox/spam) untuk melihat E-Ticket dan detail acara.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('events.register.store', ['slug' => $event->slug]) }}" method="POST" id="registrationForm" class="flex flex-col lg:flex-row gap-8 reveal">
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
                                            <button type="button" class="copy-pic-btn text-[10px] bg-brand-600 text-white hover:bg-brand-700 hover:shadow-brand-600/30 hover:-translate-y-0.5 transition-all duration-300 px-2 py-0.5 rounded transition" onclick="copyFromPic(this)">
                                                Isi Dengan Data PIC
                                            </button>
                                            <button type="button" class="copy-prev-btn text-[10px] bg-brand-600 text-white hover:bg-brand-700 hover:shadow-brand-600/30 hover:-translate-y-0.5 transition-all duration-300 px-2 py-0.5 rounded transition hidden" onclick="copyFromPrev(this)">
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
                                                    
                                                    // Priority: Early > Late > Regular
                                                    $displayPrice = $priceRegular;
                                                    if ($priceEarly > 0) {
                                                        $displayPrice = $priceEarly;
                                                    } elseif ($priceLate > 0) {
                                                        $displayPrice = $priceLate;
                                                    }
                                                @endphp
                                                <label class="cursor-pointer relative">
                                                    <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="peer sr-only cat-radio" data-price="{{ $displayPrice }}" required {{ $loop->first ? 'checked' : '' }}>
                                                    <div class="p-3 bg-white border border-slate-300 rounded-xl peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600 transition hover:border-brand-400">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-bold text-slate-900 text-sm">{{ $cat->name }} Umum/Master</span>
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
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                            <input type="text" name="participants[0][id_card]" placeholder="No. ID (KTP/SIM)" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>                                            
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                            <textarea name="participants[0][address]" placeholder="Alamat Peserta" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required maxlength="500" rows="3"></textarea>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">                                            
                                            <em class="md:col-span-1">Tanggal lahir</em>
                                            <input type="date" name="participants[0][date_of_birth]" placeholder="Tanggal Lahir" aria-label="Tanggal Lahir" class="w-full md:col-span-3 bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="text" name="participants[0][emergency_contact_name]" placeholder="Nama Kontak Darurat" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                            <input type="text" name="participants[0][emergency_contact_number]" placeholder="No. Kontak Darurat" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1 relative">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Ukuran Jersey</label>
                                                
                                            <select name="participants[0][jersey_size]" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                                <option value="">Ukuran Jersey</option>
                                                @foreach(['XS','S','M','L','XL','XXL'] as $size) <option value="{{ $size }}">{{ $size }}</option> @endforeach
                                            </select>
                                            <button type="button" onclick="openLightbox('https://res.cloudinary.com/dslfarxct/image/upload/v1769987967/size_jersey_ktpwwy.webp', true)" class="text-xs font-bold text-brand-600 hover:text-brand-700 mt-1.5 flex items-center gap-1 ml-1 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Panduan Ukuran
                                            </button>
                                            </div>
                                            <div class="space-y-1 relative hidden">
                                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Target Waktu (Jam:Mnt:Dtk)</label>
                                                <div class="flex gap-2">
                                                    <div class="relative flex-1">
                                                        <input type="number" min="0" max="23" placeholder="00" class="target-time-part target-time-h w-full bg-white border border-slate-300 rounded-xl px-3 py-3 text-center focus:ring-2 focus:ring-brand-600 outline-none font-mono font-bold" oninput="if(this.value.length>2) this.value=this.value.slice(0,2)">
                                                        <span class="absolute right-2 top-3.5 text-[10px] text-slate-400 font-bold pointer-events-none">JAM</span>
                                                    </div>
                                                    <div class="relative flex-1">
                                                        <input type="number" min="0" max="59" placeholder="00" class="target-time-part target-time-m w-full bg-white border border-slate-300 rounded-xl px-3 py-3 text-center focus:ring-2 focus:ring-brand-600 outline-none font-mono font-bold" oninput="if(this.value.length>2) this.value=this.value.slice(0,2)">
                                                        <span class="absolute right-2 top-3.5 text-[10px] text-slate-400 font-bold pointer-events-none">MNT</span>
                                                    </div>
                                                    <div class="relative flex-1">
                                                        <input type="number" min="0" max="59" placeholder="00" class="target-time-part target-time-s w-full bg-white border border-slate-300 rounded-xl px-3 py-3 text-center focus:ring-2 focus:ring-brand-600 outline-none font-mono font-bold" oninput="if(this.value.length>2) this.value=this.value.slice(0,2)">
                                                        <span class="absolute right-2 top-3.5 text-[10px] text-slate-400 font-bold pointer-events-none">DTK</span>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="participants[0][target_time]" class="target-time-input" value="{{ old('participants.0.target_time') }}">
                                                <p class="text-[10px] text-slate-400 ml-1">Est. waktu finish (Duration)</p>
                                                @error('participants.0.target_time') <span class="text-red-500 text-xs ml-1">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        @if(!empty($event->addons) && is_array($event->addons))
                                        <div class="mt-4 border-t border-slate-100 pt-4">
                                            <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                                <i class="fas fa-plus-circle text-accent-500"></i> Add-Ons (Opsional)
                                            </label>
                                            <div class="grid grid-cols-1 gap-3">
                                                @foreach($event->addons as $idx => $addon)
                                                    <label class="flex items-center justify-between p-3 bg-white border border-slate-300 rounded-xl hover:border-brand-400 transition cursor-pointer group">
                                                        <div class="flex items-center gap-3">
                                                             <div class="relative flex items-center">
                                                                <input type="checkbox" 
                                                                    name="participants[0][addons][{{ $idx }}][selected]" 
                                                                    value="1" 
                                                                    class="addon-checkbox w-5 h-5 rounded text-brand-600 focus:ring-brand-500 border-slate-400 bg-slate-50 group-hover:bg-white transition"
                                                                    data-price="{{ $addon['price'] ?? 0 }}">
                                                             </div>
                                                             <input type="hidden" name="participants[0][addons][{{ $idx }}][name]" value="{{ $addon['name'] }}">
                                                             <input type="hidden" name="participants[0][addons][{{ $idx }}][price]" value="{{ $addon['price'] ?? 0 }}">
                                                             
                                                             <div>
                                                                <span class="block text-sm font-bold text-slate-900 group-hover:text-brand-700 transition">{{ $addon['name'] }}</span>
                                                             </div>
                                                        </div>
                                                        <span class="text-sm font-bold text-accent-600">
                                                            +Rp {{ number_format($addon['price'] ?? 0, 0, ',', '.') }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif                                        
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
                                <input type="hidden" name="coupon_code" id="coupon_code_hidden">
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
                                            Saya setuju dengan <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="text-white underline font-bold animate-heartbeat hover:text-brand-400 transition-colors">Peraturan, Syarat & Ketentuan</button>.
                                        </span>
                                    </label>
                                </div>
                                @endif

                                <button type="submit" id="submitBtn" class="w-full py-4 bg-white text-brand-900 font-bold rounded-xl hover:bg-slate-100 transition shadow-lg relative z-10">
                                    Lanjut Pembayaran
                                </button>
                                <p class="text-[10px] text-center text-slate-500 mt-4 uppercase tracking-widest relative z-10">Secure Payment by Midtrans</p>
                                <div class="mt-4 text-center relative z-10">
                                    <button type="button" onclick="window.resetRegistrationForm()" class="text-xs text-slate-400 hover:text-brand-600 underline transition-colors flex items-center justify-center gap-1 mx-auto">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        Reset Formulir & Clear Cache
                                    </button>
                                </div>
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
                <a href="#faq" class="hover:text-white transition">FAQ</a>
                <a href="https://wa.link/aslic7" class="hover:text-white transition">Kontak</a>
                <a href="https://www.instagram.com/paolorunfest/" class="hover:text-white transition">Instagram</a>
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

        (function () {
            const modal = document.getElementById('predictorModal');
            const openBtn = document.getElementById('openPredictorBtn');
            const closeBtn = document.getElementById('closePredictorBtn');
            const overlay = modal ? modal.querySelector('.absolute.inset-0') : null;
            const categoryEl = document.getElementById('predictCategory');
            const pbH = document.getElementById('predictPbH');
            const pbM = document.getElementById('predictPbM');
            const pbS = document.getElementById('predictPbS');
            const pbDate = document.getElementById('predictPbDate');
            const errEl = document.getElementById('predictModalError');
            const btn = document.getElementById('predictModalBtn');
            const resultWrap = document.getElementById('predictResultWrap');
            const emptyHint = document.getElementById('predictEmptyHint');

            const optimisticEl = document.getElementById('predictOptimistic');
            const realisticEl = document.getElementById('predictRealistic');
            const pessimisticEl = document.getElementById('predictPessimistic');
            const confidenceEl = document.getElementById('predictConfidence');
            const routeDistanceEl = document.getElementById('predictRouteDistance');
            const routeGainEl = document.getElementById('predictRouteGain');
            const strategyEl = document.getElementById('predictStrategy');

            if (!modal || !openBtn || !closeBtn) return;

            const csrfToken = '{{ csrf_token() }}';
            const predictUrl = '{{ route('events.prediction.predict', $event->slug) }}';

            function showError(message) {
                if (!errEl) return;
                errEl.textContent = message || 'Terjadi error.';
                errEl.classList.remove('hidden');
            }

            function clearError() {
                if (!errEl) return;
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }

            function openModal() {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                clearError();
                if (resultWrap) resultWrap.classList.add('hidden');
                if (emptyHint) emptyHint.classList.remove('hidden');
                if (pbDate && !pbDate.value) {
                    const d = new Date();
                    pbDate.value = d.toISOString().slice(0, 10);
                }
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            if (overlay) overlay.addEventListener('click', closeModal);
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });

            btn.addEventListener('click', async () => {
                clearError();

                const categoryId = categoryEl ? categoryEl.value : '';
                const weatherEl = document.querySelector('input[name="predictWeather"]:checked');
                const weather = weatherEl ? weatherEl.value : '';

                if (!categoryId) {
                    showError('Pilih kategori terlebih dahulu.');
                    return;
                }

                const payload = {
                    category_id: parseInt(categoryId, 10),
                    weather,
                    pb_h: parseInt(pbH?.value || '0', 10),
                    pb_m: parseInt(pbM?.value || '0', 10),
                    pb_s: parseInt(pbS?.value || '0', 10),
                    pb_date: pbDate?.value || '',
                };

                btn.disabled = true;
                btn.textContent = 'Menghitung...';

                try {
                    const res = await fetch(predictUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json().catch(() => null);
                    if (!res.ok || !data || !data.ok) {
                        showError((data && data.message) ? data.message : 'Prediksi gagal.');
                        return;
                    }

                    const result = data.result || {};
                    if (optimisticEl) optimisticEl.textContent = result?.prediction?.optimistic || '-';
                    if (realisticEl) realisticEl.textContent = result?.prediction?.realistic || '-';
                    if (pessimisticEl) pessimisticEl.textContent = result?.prediction?.pessimistic || '-';
                    if (confidenceEl) confidenceEl.textContent = (result?.confidence !== undefined && result?.confidence !== null) ? `${Math.round(result.confidence * 100)}%` : '-';
                    if (routeDistanceEl) routeDistanceEl.textContent = (result?.route?.distance_km !== null && result?.route?.distance_km !== undefined) ? `${result.route.distance_km} km` : '-';
                    if (routeGainEl) routeGainEl.textContent = (result?.route?.elevation_gain_m !== null && result?.route?.elevation_gain_m !== undefined) ? `${result.route.elevation_gain_m} m` : '-';
                    if (strategyEl) strategyEl.textContent = result?.strategy || '-';

                    if (emptyHint) emptyHint.classList.add('hidden');
                    if (resultWrap) resultWrap.classList.remove('hidden');
                } catch (e) {
                    showError('Terjadi error saat menghitung prediksi.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Prediksi Waktu';
                }
            });
        })();

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
            const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};
            let participantCount = 1;
            let appliedCoupon = null;
            let discountAmount = 0;

            // Template for cloning (Take the first item)
            const template = participantsWrapper.querySelector('.participant-item').cloneNode(true);

            function resetCoupon() {
                if (appliedCoupon || discountAmount > 0) {
                    appliedCoupon = null;
                    discountAmount = 0;
                    const codeInput = document.getElementById('coupon_code');
                    const codeHidden = document.getElementById('coupon_code_hidden');
                    const couponMsg = document.getElementById('couponMessage');
                    if (codeInput) codeInput.value = '';
                    if (codeHidden) codeHidden.value = '';
                    if (couponMsg) couponMsg.innerHTML = '';
                }
            }

            function computePayableSubtotalAndCount() {
                const categoryCounts = new Map();
                const categoryPrices = new Map();
                let selectedCount = 0;

                document.querySelectorAll('.participant-item').forEach(item => {
                    const checkedRadio = item.querySelector('input[type="radio"]:checked');
                    if (!checkedRadio) return;
                    selectedCount++;
                    const categoryId = checkedRadio.value;
                    const price = parseFloat(checkedRadio.getAttribute('data-price') || 0);
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

                return { subtotal, selectedCount };
            }

            function calculateSubtotalWithAddons() {
                const { subtotal: categorySubtotal } = computePayableSubtotalAndCount();
                
                let addonTotal = 0;
                document.querySelectorAll('.participant-item').forEach(item => {
                    item.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
                        addonTotal += parseFloat(cb.getAttribute('data-price') || 0);
                    });
                });

                return categorySubtotal + addonTotal;
            }

            // 1. Calculate Price Function
            function calculateTotal() {
                const subtotal = calculateSubtotalWithAddons();
                const { selectedCount } = computePayableSubtotalAndCount();

                // Update Displays
                subtotalDisplay.textContent = formatCurrency(subtotal);
                
                const totalFee = selectedCount * platformFee;
                if(platformFeeDisplay) platformFeeDisplay.textContent = formatCurrency(totalFee);
                
                let grandTotal = subtotal + totalFee - discountAmount;
                if (grandTotal < 0) grandTotal = 0;
                totalDisplay.textContent = formatCurrency(grandTotal);

                const discountRow = document.getElementById('discountRow');
                const discountDisplay = document.getElementById('discountDisplay');
                if (discountRow && discountDisplay) {
                    if (discountAmount > 0) {
                        discountRow.classList.remove('hidden');
                        discountDisplay.textContent = '- ' + formatCurrency(discountAmount);
                    } else {
                        discountRow.classList.add('hidden');
                        discountDisplay.textContent = '-Rp 0';
                    }
                }
            }

            // 2. Add Participant
            addBtn.addEventListener('click', () => {
                resetCoupon();
                const newItem = template.cloneNode(true);
                const idx = participantCount++;
                
                // Update UI text
                newItem.querySelector('.participant-title').textContent = `Peserta #${idx + 1}`;
                newItem.setAttribute('data-index', idx);
                newItem.querySelector('.remove-participant').classList.remove('hidden');

                // Add Reset Button to the first participant or near form bottom? 
                // Actually let's add a global reset button to the footer later via HTML edit
                
                // Show/Hide Copy Prev Button
                const copyPrevBtn = newItem.querySelector('.copy-prev-btn');
                if (idx > 0) {
                    copyPrevBtn.classList.remove('hidden');
                } else {
                    copyPrevBtn.classList.add('hidden');
                }

                // Update Input Names
                newItem.querySelectorAll('input, select, textarea').forEach(input => {
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
                    resetCoupon();
                    calculateTotal();
                }
            });

            // 4. Attach Listeners to Radios and Checkboxes
            function attachListeners(context) {
                context.querySelectorAll('input[type="radio"], input.addon-checkbox').forEach(input => {
                    input.addEventListener('change', calculateTotal);
                    input.addEventListener('change', resetCoupon);
                });

                // Target Time Logic
                context.querySelectorAll('.target-time-part').forEach(input => {
                    input.addEventListener('input', function() {
                        updateTargetTime(this);
                    });
                    input.addEventListener('blur', function() {
                         if(this.value.length === 1) this.value = this.value.padStart(2, '0');
                         updateTargetTime(this);
                    });
                });

                // Initialize split fields from hidden input if exists (e.g. from old())
                context.querySelectorAll('.target-time-input').forEach(hidden => {
                    if (hidden.value && /^\d{2}:\d{2}:\d{2}$/.test(hidden.value)) {
                        const parts = hidden.value.split(':');
                        const container = hidden.closest('.space-y-1');
                        if (container) {
                            container.querySelector('.target-time-h').value = parts[0];
                            container.querySelector('.target-time-m').value = parts[1];
                            container.querySelector('.target-time-s').value = parts[2];
                        }
                    }
                });
            }

            function updateTargetTime(el) {
                const container = el.closest('.space-y-1');
                if (!container) return;
                
                const h = container.querySelector('.target-time-h').value.padStart(2, '0');
                const m = container.querySelector('.target-time-m').value.padStart(2, '0');
                const s = container.querySelector('.target-time-s').value.padStart(2, '0');
                const hidden = container.querySelector('.target-time-input');
                
                if (hidden) {
                    hidden.value = `${h}:${m}:${s}`;
                }
            }

            // 5. Form Submission Validation (Duplicate NIK Check)
            form.addEventListener('submit', function(e) {
                const idCards = [];
                const inputs = form.querySelectorAll('input[name$="[id_card]"]');
                let hasDuplicate = false;

                inputs.forEach(input => {
                    const val = input.value.trim();
                    if (val) {
                        if (idCards.includes(val)) {
                            hasDuplicate = true;
                            input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                            // Add error message if not exists
                            let err = input.nextElementSibling;
                            if (!err || !err.classList.contains('text-red-500')) {
                                err = document.createElement('p');
                                err.className = 'text-red-500 text-xs mt-1 duplicate-error';
                                err.innerText = 'NIK/ID ini sudah digunakan peserta lain dalam form ini.';
                                input.parentNode.insertBefore(err, input.nextSibling);
                            }
                        } else {
                            idCards.push(val);
                            input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                            const err = input.parentNode.querySelector('.duplicate-error');
                            if (err) err.remove();
                        }
                    }
                });

                if (hasDuplicate) {
                    e.preventDefault();
                    alert('Terdapat NIK/ID yang sama antar peserta. Mohon periksa kembali.');
                    // Scroll to first error
                    const firstError = form.querySelector('.border-red-500');
                    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

            // Initial attach
            attachListeners(participantsWrapper);
            calculateTotal();
            calculateTotal();

            const couponBtn = document.getElementById('applyCoupon');
            if (couponBtn) {
                couponBtn.addEventListener('click', () => {
                    const code = document.getElementById('coupon_code')?.value;
                    if (!code) {
                        alert('Masukkan kode kupon');
                        return;
                    }

                    // Use the helper to get correct subtotal including addons
                    let subtotal = calculateSubtotalWithAddons();
                    console.log('[Promo] Applying coupon:', code, 'Subtotal:', subtotal);

                    if (subtotal === 0) {
                        alert('Pilih kategori peserta terlebih dahulu');
                        return;
                    }

                    const originalText = couponBtn.innerHTML;
                    couponBtn.innerHTML = '...';
                    couponBtn.disabled = true;

                    fetch(`{{ route('events.register.coupon', ['slug' => $event->slug]) }}`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ event_id: {{ $event->id }}, coupon_code: code, total_amount: subtotal })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) {
                            throw new Error(data.message || 'Terjadi kesalahan pada server');
                        }
                        return data;
                    })
                    .then(data => {
                        console.log('[Promo] Response:', data);
                        if (data.success) {
                            appliedCoupon = data.coupon;
                            discountAmount = data.discount_amount;
                            const hidden = document.getElementById('coupon_code_hidden');
                            if (hidden) hidden.value = data.coupon.code;
                            document.getElementById('coupon_code').value = data.coupon.code;
                            document.getElementById('couponMessage').innerHTML = '<span class="text-green-600">Kupon berhasil digunakan!</span>';
                            calculateTotal();
                        } else {
                            // Logic fallback
                            throw new Error(data.message || 'Kupon gagal digunakan');
                        }
                    })
                    .catch(err => {
                        console.error('[Promo] Error:', err);
                        appliedCoupon = null;
                        discountAmount = 0;
                        const hidden = document.getElementById('coupon_code_hidden');
                        if (hidden) hidden.value = '';
                        document.getElementById('couponMessage').innerHTML = `<span class="text-red-500">${err.message}</span>`;
                        calculateTotal();
                    })
                    .finally(() => {
                        couponBtn.innerHTML = originalText;
                        couponBtn.disabled = false;
                    });
                });
            }

            // 5. Submit Handler (AJAX)
            
            // Add Copy Helpers to Global Scope so buttons can access them
            window.resetRegistrationForm = function() {
                if(!confirm('Apakah Anda yakin ingin mereset formulir? Data yang tersimpan (draft) akan dihapus.')) return;
                
                try {
                    // Clear Draft
                    if(window.clearAutoSave) window.clearAutoSave();
                    // Clear Session Retry Key
                    sessionStorage.removeItem('rl_registration_session_retry_v1');
                    // Clear other potentially problematic keys
                    localStorage.removeItem('midtrans_snap_token');
                } catch(e) { console.error(e); }

                window.location.reload();
            };

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
                const prevItem = currentItem.previousElementSibling;
                
                if (prevItem && prevItem.classList.contains('participant-item')) {
                    // 1. Copy Text, Email, Date, Number Inputs
                    const textFields = [
                        'name', 'email', 'phone', 'id_card', 'date_of_birth', 
                        'emergency_contact_name', 'emergency_contact_number'
                    ];
                    
                    textFields.forEach(field => {
                        const sourceInput = prevItem.querySelector(`input[name*="[${field}]"]`);
                        const targetInput = currentItem.querySelector(`input[name*="[${field}]"]`);
                        if (sourceInput && targetInput) {
                            targetInput.value = sourceInput.value;
                        }
                    });

                    // 2. Copy Select Dropdowns
                    const selectFields = ['gender', 'jersey_size'];
                    selectFields.forEach(field => {
                        const sourceSelect = prevItem.querySelector(`select[name*="[${field}]"]`);
                        const targetSelect = currentItem.querySelector(`select[name*="[${field}]"]`);
                        if (sourceSelect && targetSelect) {
                            targetSelect.value = sourceSelect.value;
                        }
                    });

                    // 3. Copy Category (Radio)
                    const sourceCategory = prevItem.querySelector(`input[name*="[category_id]"]:checked`);
                    if (sourceCategory) {
                        const val = sourceCategory.value;
                        const targetCategory = currentItem.querySelector(`input[name*="[category_id]"][value="${val}"]`);
                        if (targetCategory) {
                            targetCategory.checked = true;
                            targetCategory.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    // 4. Copy Addons (Checkboxes)
                    const sourceAddons = prevItem.querySelectorAll('input.addon-checkbox:checked');
                    // Uncheck all first
                    currentItem.querySelectorAll('input.addon-checkbox').forEach(cb => cb.checked = false);
                    
                    sourceAddons.forEach(sourceCb => {
                        // Find matching checkbox by partial name structure or index
                        // Since structure is identical, we can match by the addon index part of the name
                        // name="participants[X][addons][Y][selected]"
                        const nameParts = sourceCb.name.match(/\[addons\]\[(\d+)\]/);
                        if (nameParts && nameParts[1]) {
                            const addonIdx = nameParts[1];
                            const targetCb = currentItem.querySelector(`input[name*="[addons][${addonIdx}][selected]"]`);
                            if (targetCb) {
                                targetCb.checked = true;
                            }
                        }
                    });

                    // 5. Trigger Events for Auto-Save and UI updates
                    currentItem.querySelectorAll('input, select').forEach(el => {
                        if(el.type !== 'radio') { // Radios handled above
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });

                    //alert('Data berhasil disalin dari peserta sebelumnya.');

                } else {
                    alert('Tidak ada peserta sebelumnya untuk disalin.');
                }
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Client-side Validation
                if (window.formValidator && !window.formValidator.validateAll()) {
                    return;
                }

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

                const sessionRetryKey = 'rl_registration_session_retry_v1';

                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async response => {
                    // 1. Handle Session Timeout (419)
                    if (response.status === 419) {
                        throw new Error('SESSION_EXPIRED');
                    }
                    
                    // 2. Validate Content-Type (Ensure JSON)
                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        throw new Error('SERVER_ERROR_NON_JSON: ' + response.status);
                    }

                    // 3. Parse JSON
                    const data = await response.json();
                    
                    // 4. Check Logical Success (200 OK but maybe success: false logic handled later, or non-200 HTTP status)
                    if (!response.ok) {
                        // Reset reCAPTCHA on any error response
                        if (typeof grecaptcha !== 'undefined') grecaptcha.reset();

                        // Handle Validation Errors (422)
                        if (response.status === 422 && data.errors && window.formValidator) {
                            let firstErrorField = null;
                            Object.entries(data.errors).forEach(([key, messages]) => {
                                // Convert dot notation (participants.0.name) to bracket notation (participants[0][name])
                                const parts = key.split('.');
                                let fieldName = parts[0];
                                for (let i = 1; i < parts.length; i++) {
                                    fieldName += `[${parts[i]}]`;
                                }

                                // Try exact match first (e.g. participants[0][id_card])
                                let input = form.querySelector(`[name="${fieldName}"]`);
                                
                                // Fallback: Try matching regex logic if exact match fails (legacy behavior preserved just in case)
                                if (!input) {
                                     const legacyName = key.replace(/\.(\d+)\./, '[$1][');
                                     input = form.querySelector(`[name="${legacyName}"]`) || form.querySelector(`[name="${legacyName}]"]`);
                                }
                                
                                if (input) {
                                    window.formValidator.showError(input, messages[0]);
                                    if (!firstErrorField) firstErrorField = input;
                                }
                            });
                            
                            if (firstErrorField) {
                                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                firstErrorField.focus();
                                // Throw generic error to stop promise chain but don't show alert if we showed inline errors
                                throw new Error('VALIDATION_ERROR_HANDLED');
                            }
                        }

                        throw new Error(data.message || 'Terjadi kesalahan pada server');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        try { sessionStorage.removeItem(sessionRetryKey); } catch (e) {}
                        // Clear draft on successful submission
                        if(window.clearAutoSave) window.clearAutoSave();
                        
                        if (data.snap_token) {
                            snap.pay(data.snap_token, {
                                onSuccess: function(result) { window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`; },
                                onPending: function(result) { window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; },
                                onError: function(result) { alert("Pembayaran gagal"); btn.disabled = false; btn.innerHTML = originalText; },
                                onClose: function() { 
                                    // Redirect to pending page to avoid double submission error (422) if user tries to submit form again
                                    window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; 
                                }
                            });
                        } else if (data.payment_gateway === 'moota' || data.redirect_url) {
                            if (window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function' && data.transaction_id) {
                                btn.disabled = false;
                                btn.innerHTML = originalText;

                                const phoneEl = form.querySelector('[name="pic_phone"]');
                                const nameEl = form.querySelector('[name="pic_name"]');
                                window.RuangLariMoota.open({
                                    transaction_id: data.transaction_id,
                                    registration_id: data.registration_id,
                                    final_amount: data.final_amount,
                                    unique_code: data.unique_code,
                                    phone: phoneEl ? phoneEl.value : '',
                                    name: nameEl ? nameEl.value : '',
                                });
                            } else if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                alert('Registrasi berhasil, namun data pembayaran tidak lengkap.');
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                            }
                        } else {
                            // Free Event / Success direct
                            window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`;
                        }
                    } else {
                        if (window.openFailModal) {
                            window.openFailModal(data.message || 'Terjadi kesalahan. Periksa input Anda.');
                        } else {
                            alert(data.message || 'Terjadi kesalahan. Periksa input Anda.');
                        }
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    // Reset reCAPTCHA on catch error
                    if (typeof grecaptcha !== 'undefined') grecaptcha.reset();

                    // If validation error was handled inline, just return
                    if (err.message === 'VALIDATION_ERROR_HANDLED') {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        return;
                    }

                    console.error('[Registration] Error:', err);
                    
                    if (err.message === 'SESSION_EXPIRED') {
                        let alreadyRetried = false;
                        try {
                            alreadyRetried = sessionStorage.getItem(sessionRetryKey) === '1';
                        } catch (e) {
                            alreadyRetried = false;
                        }

                        if (!alreadyRetried) {
                            try { sessionStorage.setItem(sessionRetryKey, '1'); } catch (e) {}
                            
                            // Clear AutoSave Data on Session Expiry to prevent stale data issues
                            if(window.clearAutoSave) window.clearAutoSave();

                            alert('Maaf, sesi pendaftaran Anda telah berakhir. Halaman akan dimuat ulang otomatis untuk memperbarui sesi.');
                            window.location.reload();
                            return;
                        }

                        const msg = 'Sesi masih dianggap berakhir setelah muat ulang. Silakan tutup tab ini, buka ulang halaman event, atau hapus cookies site (localhost) lalu coba lagi.';
                        if (window.openFailModal) {
                            window.openFailModal(msg);
                        } else {
                            alert(msg);
                        }
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        return;
                    }

                    if (window.openFailModal) {
                        window.openFailModal(err.message || 'Gagal menghubungi server.');
                    } else {
                        alert(err.message || 'Gagal menghubungi server.');
                    }
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

        // H. Auto Save & Restore System (LocalStorage)
        (function() {
            const STORAGE_KEY = 'ruanglari_draft_{{ $event->id }}';
            const EXPIRY_HOURS = 24;
            const form = document.getElementById('registrationForm');
            if (!form) return;

            // Create Visual Indicator
            const indicator = document.createElement('div');
            indicator.className = 'fixed bottom-6 left-6 z-50 bg-white/90 backdrop-blur border border-slate-200 shadow-xl rounded-full px-4 py-2 text-xs font-bold text-slate-600 flex items-center gap-2 transform transition-all duration-500 translate-y-20 opacity-0 group cursor-pointer';
            indicator.innerHTML = `
                <div class="flex items-center gap-2" onclick="window.resetRegistrationForm()">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse indicator-dot"></span> 
                    <span class="indicator-text">Draf Disimpan</span>
                    <span class="hidden group-hover:inline-block text-red-500 ml-1 border-l pl-2 border-slate-300">Reset?</span>
                </div>
            `;
            document.body.appendChild(indicator);

            const showIndicator = (msg, type = 'success') => {
                const color = type === 'success' ? 'bg-emerald-500' : (type === 'process' ? 'bg-blue-500' : 'bg-red-500');
                const dot = indicator.querySelector('.indicator-dot');
                const text = indicator.querySelector('.indicator-text');
                
                if(dot) dot.className = `w-2 h-2 rounded-full ${color} ${type === 'process' ? 'animate-bounce' : ''} indicator-dot`;
                if(text) text.innerText = msg;

                indicator.classList.remove('translate-y-20', 'opacity-0');
                
                if (type !== 'process') {
                    setTimeout(() => {
                        indicator.classList.add('translate-y-20', 'opacity-0');
                    }, 3000);
                }
            };

            // Save Function
            const saveData = () => {
                try {
                    const formData = new FormData(form);
                    const data = {};
                    
                    // Handle standard inputs
                    for (let [key, val] of formData.entries()) {
                        data[key] = val;
                    }

                    // Special handling: store ALL radio states to ensure correct restoration
                    // (FormData only stores checked ones, which is actually fine, but we need to know what to check)
                    
                    const payload = {
                        timestamp: new Date().getTime(),
                        participantCount: document.querySelectorAll('.participant-item').length,
                        data: data
                    };

                    // Simple Obfuscation (Base64)
                    const encrypted = btoa(encodeURIComponent(JSON.stringify(payload)));
                    localStorage.setItem(STORAGE_KEY, encrypted);
                    
                    showIndicator('Progres tersimpan');
                } catch (e) {
                    console.error('Auto-save error:', e);
                }
            };

            // Debounce
            let timeout;
            const debouncedSave = () => {
                clearTimeout(timeout);
                timeout = setTimeout(saveData, 1000);
            };

            // Restore Function
            const restoreData = () => {
                const encrypted = localStorage.getItem(STORAGE_KEY);
                if (!encrypted) return;

                try {
                    const json = decodeURIComponent(atob(encrypted));
                    const payload = JSON.parse(json);

                    // Check Expiry
                    const now = new Date().getTime();
                    const hoursDiff = (now - payload.timestamp) / (1000 * 60 * 60);
                    if (hoursDiff > EXPIRY_HOURS) {
                        localStorage.removeItem(STORAGE_KEY);
                        return;
                    }

                    showIndicator('Memulihkan data...', 'process');

                    // 1. Restore Participant Count
                    const currentCount = document.querySelectorAll('.participant-item').length;
                    const targetCount = payload.participantCount || 1;
                    const addBtn = document.getElementById('addParticipant');

                    // Add needed participants
                    if (targetCount > currentCount) {
                        for (let i = 0; i < (targetCount - currentCount); i++) {
                            addBtn.click();
                        }
                    }

                    // 2. Fill Data
                    // Use setTimeout to allow DOM updates if any (though click() is sync mostly)
                    setTimeout(() => {
                        const data = payload.data;
                        
                        // Fill inputs
                        Array.from(form.elements).forEach(el => {
                            if (!el.name) return;
                            
                            // Check if this field exists in saved data
                            // Note: FormData keys for arrays are like "participants[0][name]"
                            if (data.hasOwnProperty(el.name)) {
                                const savedValue = data[el.name];
                                
                                if (el.type === 'radio') {
                                    if (el.value == savedValue) {
                                        el.checked = true;
                                        el.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                } else if (el.type === 'checkbox') {
                                    el.checked = true; // Presence in FormData usually means checked
                                    el.dispatchEvent(new Event('change', { bubbles: true }));
                                } else {
                                    el.value = savedValue;
                                    // Trigger input for any listeners
                                    el.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            }
                        });
                        
                        showIndicator('Data berhasil dipulihkan');
                    }, 100);

                } catch (e) {
                    console.error('Restore error:', e);
                    localStorage.removeItem(STORAGE_KEY);
                }
            };

            // Attach Listeners
            form.addEventListener('input', debouncedSave);
            form.addEventListener('change', debouncedSave);
            
            // Expose clear function
            window.clearAutoSave = () => {
                localStorage.removeItem(STORAGE_KEY);
                showIndicator('Data formulir dibersihkan');
            };

            // Initialize Restore
            // Wait a bit for other scripts
            setTimeout(restoreData, 500);
        })();

        // G. Lightbox Logic
        const galleryImages = @json(isset($event->gallery) ? array_map(fn($img) => asset('storage/'.$img), $event->gallery) : []);
        let currentImageIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');

        function openLightbox(source, hideNav = false) {
            const navButtons = document.querySelectorAll('#lightbox button[onclick*="Image"]');
            
            if (typeof source === 'number') {
                if (galleryImages.length === 0) return;
                currentImageIndex = source;
                lightboxImg.src = galleryImages[currentImageIndex];
                navButtons.forEach(btn => btn.classList.remove('hidden'));
            } else {
                lightboxImg.src = source;
                if (hideNav) {
                    navButtons.forEach(btn => btn.classList.add('hidden'));
                } else {
                    navButtons.forEach(btn => btn.classList.remove('hidden'));
                }
            }
            
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
            if (galleryImages.length === 0) return;
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            lightboxImg.src = galleryImages[currentImageIndex];
        }

        function prevImage() {
            if (galleryImages.length === 0) return;
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
    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Syarat & Ketentuan</h3>
                    <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center justify-center">✕</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto prose prose-sm max-w-none text-slate-700">
                    {!! $event->terms_and_conditions !!}
                </div>
                <div class="p-6 border-t border-slate-200">
                    <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden'); const cb = document.querySelector('input[name=terms_agreed]'); if(cb) { cb.checked = true; cb.dispatchEvent(new Event('change')); }" class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div id="registrationSuccessModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" id="registrationSuccessModalBackdrop"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-black text-slate-900">Registrasi Berhasil</h3>
                    <button type="button" id="registrationSuccessCloseBtn" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center justify-center">✕</button>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-100 flex items-center justify-center">
                            <div class="w-6 h-6 border-4 border-yellow-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <div class="flex-1">
                            <p class="text-slate-900 font-bold">Terima kasih sudah mendaftar.</p>
                            <p class="text-slate-600 text-sm mt-1">Tiket akan dikirim via email. Silakan cek inbox dan folder spam.</p>
                            <p class="text-slate-500 text-xs mt-3">Modal akan tertutup otomatis dalam <span class="font-black text-slate-900" id="registrationSuccessCountdown">5</span> detik.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="button" id="checkEmailBtn" class="flex-1 py-3 rounded-xl bg-slate-900 text-white font-black hover:bg-slate-800 transition">Cek Email Saya</button>
                        <button type="button" id="closeNowBtn" class="flex-1 py-3 rounded-xl border border-slate-300 text-slate-800 font-black hover:bg-slate-50 transition">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;
        const vueApp = createApp({});
        
        // Component is defined in the partial included above
        if (typeof ParticipantsTableComponent !== 'undefined') {
            vueApp.component('participants-table', ParticipantsTableComponent);
        }
        
        const mountEl = document.getElementById('vue-participants-app');
        if (mountEl) vueApp.mount(mountEl);
    </script>
    <script>
        (function () {
            const modal = document.getElementById('registrationSuccessModal');
            if (!modal) return;

            const backdrop = document.getElementById('registrationSuccessModalBackdrop');
            const closeBtn = document.getElementById('registrationSuccessCloseBtn');
            const closeNowBtn = document.getElementById('closeNowBtn');
            const checkEmailBtn = document.getElementById('checkEmailBtn');
            const countdownEl = document.getElementById('registrationSuccessCountdown');

            // Failure Modal Elements
            const failModal = document.getElementById('registrationFailureModal');
            const failBackdrop = document.getElementById('registrationFailureModalBackdrop');
            const failCloseBtn = document.getElementById('registrationFailureCloseBtn');
            const failCloseNowBtn = document.getElementById('closeFailureNowBtn');
            const failMessageEl = document.getElementById('registrationFailureMessage');

            let timer = null;
            let remaining = 5;

            function cleanupUrl() {
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('payment');
                    url.searchParams.delete('error_message');
                    window.history.replaceState({}, '', url.toString());
                } catch (e) {}
            }

            function closeModal() {
                modal.classList.add('hidden');
                if (failModal) failModal.classList.add('hidden');
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
                cleanupUrl();
            }

            function openModal() {
                remaining = 5;
                if (countdownEl) countdownEl.textContent = String(remaining);
                modal.classList.remove('hidden');
                if (timer) clearInterval(timer);
                timer = setInterval(() => {
                    remaining -= 1;
                    if (countdownEl) countdownEl.textContent = String(Math.max(0, remaining));
                    if (remaining <= 0) closeModal();
                }, 1000);
            }

            window.openFailModal = function(message) {
                if (failMessageEl && message) failMessageEl.textContent = message;
                if (failModal) failModal.classList.remove('hidden');
            }

            backdrop?.addEventListener('click', closeModal);
            closeBtn?.addEventListener('click', closeModal);
            closeNowBtn?.addEventListener('click', closeModal);
            
            failBackdrop?.addEventListener('click', closeModal);
            failCloseBtn?.addEventListener('click', closeModal);
            failCloseNowBtn?.addEventListener('click', closeModal);

            checkEmailBtn?.addEventListener('click', () => {
                window.location.href = 'mailto:';
            });

            const params = new URLSearchParams(window.location.search);
            if (params.get('payment') === 'success') {
                openModal();
            } else if (params.get('payment') === 'failed') {
                window.openFailModal(params.get('error_message'));
            }
        })();
    </script>
    <script>
        /**
         * Client-side Form Validation System
         * Handles real-time validation, visual feedback, and cross-browser compatibility
         */
        (function() {
            const VALIDATION_CONFIG = {
                debounceTime: 500,
                rules: {
                    name: {
                        required: true,
                        minLength: 3,
                        message: {
                            required: "Nama lengkap wajib diisi",
                            minLength: "Nama lengkap minimal 3 karakter"
                        }
                    },
                    email: {
                        required: true,
                        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                        message: {
                            required: "Email wajib diisi",
                            pattern: "Format email tidak valid (contoh: nama@domain.com)"
                        }
                    },
                    phone: {
                        required: true,
                        pattern: /^[0-9]{10,13}$/,
                        message: {
                            required: "Nomor WhatsApp wajib diisi",
                            pattern: "Nomor WhatsApp harus berupa angka 10-13 digit"
                        }
                    },
                    id_card: {
                        required: true,
                        pattern: /^[0-9]{8,16}$/,
                        minLength: 8,
                        custom: (value) => {
                            const form = document.getElementById('registrationForm');
                            if (!form) return true;
                            const inputs = form.querySelectorAll('input[name*="[id_card]"]');
                            let count = 0;
                            inputs.forEach(input => {
                                if (input.value.trim() === value) {
                                    count++;
                                }
                            });
                            return count <= 1;
                        },
                        message: {
                            required: "Nomor Identitas (NIK/KTP/Kartu Pelajar) wajib diisi",
                            pattern: "Nomor identitas harus berupa angka 8-16 digit",
                            custom: "NIK/ID ini sudah digunakan oleh peserta lain"
                        }
                    },
                    date_of_birth: {
                        required: true,
                        custom: (value) => {
                            const date = new Date(value);
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            return date < today;
                        },
                        message: {
                            required: "Tanggal lahir wajib diisi",
                            custom: "Tanggal lahir tidak valid (harus sebelum hari ini)"
                        }
                    },
                    emergency_contact_name: {
                        required: true,
                        minLength: 3,
                        message: {
                            required: "Nama kontak darurat wajib diisi",
                            minLength: "Nama kontak darurat minimal 3 karakter"
                        }
                    },
                    emergency_contact_number: {
                        required: true,
                        pattern: /^[0-9]{10,13}$/,
                        message: {
                            required: "Nomor kontak darurat wajib diisi",
                            pattern: "Nomor harus berupa angka 10-13 digit"
                        }
                    }
                }
            };

            class FormValidator {
                constructor(formId, config) {
                    this.form = document.getElementById(formId);
                    this.config = config;
                    this.timeouts = {};
                    
                    if (this.form) {
                        this.init();
                    }
                }

                init() {
                    // Delegate input events for dynamic fields (participants)
                    this.form.addEventListener('input', (e) => {
                        const target = e.target;
                        const fieldKey = this.getFieldKey(target.name);
                        
                        if (fieldKey && this.config.rules[fieldKey]) {
                            this.handleInput(target, fieldKey);
                        }
                    });

                    // Validate on blur immediately
                    this.form.addEventListener('focusout', (e) => {
                        const target = e.target;
                        const fieldKey = this.getFieldKey(target.name);
                        
                        if (fieldKey && this.config.rules[fieldKey]) {
                            this.validateField(target, fieldKey);
                        }
                    });
                }

                getFieldKey(name) {
                    if (!name) return null;
                    // Check for direct match first
                    if (this.config.rules[name]) return name;

                    // Check for pic_ prefix
                    if (name.startsWith('pic_')) {
                        const key = name.replace('pic_', '');
                        if (this.config.rules[key]) return key;
                    }

                    // Check for array notation: participants[0][name]
                    for (const key of Object.keys(this.config.rules)) {
                        // Regex to match ends with [key] or [key] with specific boundaries
                        if (name.includes(`[${key}]`)) {
                            return key;
                        }
                    }
                    return null;
                }

                handleInput(input, fieldKey) {
                    // Clear existing timeout for debounce
                    if (this.timeouts[input.name]) {
                        clearTimeout(this.timeouts[input.name]);
                    }

                    // Optional: Clear error state immediately on type if preferred
                    // this.clearMessage(input);

                    // Set new timeout
                    this.timeouts[input.name] = setTimeout(() => {
                        this.validateField(input, fieldKey);
                    }, this.config.debounceTime);
                }

                validateField(input, fieldKey) {
                    const rule = this.config.rules[fieldKey];
                    const value = input.value.trim();
                    let isValid = true;
                    let errorMessage = '';

                    // Required Check
                    if (rule.required && !value) {
                        isValid = false;
                        errorMessage = rule.message.required;
                    }
                    // Pattern Check
                    else if (value && rule.pattern && !rule.pattern.test(value)) {
                        isValid = false;
                        errorMessage = rule.message.pattern;
                    }
                    // MinLength Check
                    else if (value && rule.minLength && value.length < rule.minLength) {
                        isValid = false;
                        errorMessage = rule.message.minLength || `Minimal ${rule.minLength} karakter`;
                    }
                    // Custom Check
                    else if (value && rule.custom && !rule.custom(value)) {
                        isValid = false;
                        errorMessage = rule.message.custom || "Nilai tidak valid";
                    }

                    if (!isValid) {
                        this.showError(input, errorMessage);
                    } else {
                        this.showSuccess(input);
                    }
                    
                    return isValid;
                }

                showError(input, message) {
                    this.clearMessage(input);
                    input.classList.add('input-error');
                    input.classList.remove('input-success');

                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'validation-message error';
                    // Exclamation Circle Icon
                    msgDiv.innerHTML = `
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>${message}</span>
                    `;
                    
                    input.parentNode.appendChild(msgDiv);
                }

                showSuccess(input) {
                    this.clearMessage(input);
                    if (input.value.trim() !== '') {
                        input.classList.remove('input-error');
                        input.classList.add('input-success');
                        
                        // Optional: Show checkmark for positive reinforcement
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'validation-message success';
                        msgDiv.innerHTML = `
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Valid</span>
                        `;
                        input.parentNode.appendChild(msgDiv);
                    }
                }

                validateAll() {
                    let allValid = true;
                    // Find all inputs that match our rules
                    const inputs = this.form.querySelectorAll('input, select, textarea');
                    
                    inputs.forEach(input => {
                        const fieldKey = this.getFieldKey(input.name);
                        if (fieldKey && this.config.rules[fieldKey]) {
                            const valid = this.validateField(input, fieldKey);
                            if (!valid) allValid = false;
                        }
                    });

                    if (!allValid) {
                        const firstError = this.form.querySelector('.input-error');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                    }
                    return allValid;
                }

                clearMessage(input) {
                    const parent = input.parentNode;
                    const existingMsg = parent.querySelector('.validation-message');
                    if (existingMsg) {
                        existingMsg.remove();
                    }
                    input.classList.remove('input-error');
                    // Don't remove input-success here to keep green border until error
                }
            }

            // Initialize Validator
            document.addEventListener('DOMContentLoaded', () => {
                window.formValidator = new FormValidator('registrationForm', VALIDATION_CONFIG);
            });
            
            // Unit Tests (Exposed for console verification)
            window.runValidationTests = function() {
                console.group('Running Validation Unit Tests');
                const validator = new FormValidator('registrationForm', VALIDATION_CONFIG);
                
                const mockInput = document.createElement('input');
                // Needs parent for message appending
                const parent = document.createElement('div');
                parent.appendChild(mockInput);
                
                const testCases = [
                    { field: 'name', value: '', expected: false, desc: 'Empty Name (Required)' },
                    { field: 'name', value: 'Ab', expected: false, desc: 'Short Name (<3 chars)' },
                    { field: 'name', value: 'Budi Santoso', expected: true, desc: 'Valid Name' },
                    { field: 'email', value: 'invalid-email', expected: false, desc: 'Invalid Email Format' },
                    { field: 'email', value: 'test@example.com', expected: true, desc: 'Valid Email' },
                    { field: 'phone', value: '123', expected: false, desc: 'Short Phone' },
                    { field: 'phone', value: '081234567890', expected: true, desc: 'Valid Phone' },
                    { field: 'id_card', value: '123456789012345', expected: false, desc: 'NIK 15 digits (Fail)' },
                    { field: 'id_card', value: '1234567890123456', expected: true, desc: 'NIK 16 digits (Pass)' }
                ];

                let passed = 0;
                testCases.forEach(test => {
                    mockInput.value = test.value;
                    // Reset classes
                    mockInput.className = '';
                    const result = validator.validateField(mockInput, test.field);
                    const status = result === test.expected ? 'PASS' : 'FAIL';
                    const style = result === test.expected ? 'color: green; font-weight: bold' : 'color: red; font-weight: bold';
                    console.log(`%c[${status}] ${test.desc}`, style);
                    if (result === test.expected) passed++;
                });
                
                console.log(`Total: ${passed}/${testCases.length} Passed`);
                console.groupEnd();
                return passed === testCases.length;
            };
        })();
    </script>
    
    <script>
        window.resetRegistrationForm = function() {
            if(!confirm('Yakin ingin mereset formulir? Data yang sudah diisi akan hilang dan halaman akan dimuat ulang.')) return;
            try {
                // Try to find the storage key from existing script or reconstruct it
                const eventId = "{{ $event->id }}"; 
                const key = 'ruanglari_draft_' + eventId;
                localStorage.removeItem(key);
                sessionStorage.clear();
                
                // Also try to clear any other potential keys
                Object.keys(localStorage).forEach(k => {
                    if(k.startsWith('ruanglari_draft_')) localStorage.removeItem(k);
                });
                
                window.location.reload();
            } catch(e) {
                console.error('Reset failed', e);
                window.location.reload();
            }
        };
    </script>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.link/aslic7" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 z-50 bg-[#25D366] text-white p-3 rounded-full shadow-lg hover:bg-[#20bd5a] hover:scale-110 transition-all duration-300 flex items-center justify-center group">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <span class="absolute right-full mr-3 bg-white text-slate-800 text-xs font-bold px-2 py-1 rounded shadow-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
            Hubungi Kami
        </span>
    </a>

    @include('events.partials.moota-payment-modal', [
        'modalPanelClass' => 'bg-white text-slate-900 border border-slate-200',
        'modalTitleClass' => 'text-slate-900',
        'modalAccentClass' => 'text-brand-600',
        'modalCloseClass' => 'bg-brand-600 text-white hover:bg-brand-700',
    ])
</body>
</html>
