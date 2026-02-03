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
    <meta name="theme-color" content="#111827">
    
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
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php
        $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
        $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
    @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>

    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                },
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Primary Blue
                            900: '#1e3a8a',
                        },
                        action: {
                            500: '#f97316', // Orange for buttons
                            600: '#ea580c',
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'card': '0 0 0 1px rgba(0,0,0,0.03), 0 2px 8px rgba(0,0,0,0.04)',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #F8FAFC; color: #0F172A; }
        .clip-hero { clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
    </style>
</head>
<body class="antialiased">

    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex-shrink-0 flex items-center gap-3">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-10 w-auto">
                    @else
                        <div class="h-10 w-10 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">R</div>
                    @endif
                    <span class="font-bold text-xl tracking-tight text-slate-900 hidden md:block">{{ $event->name }}</span>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#about" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Tentang</a>
                    <a href="#categories" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Kategori</a>
                    <a href="#racepack" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Race Pack</a>
                    <a href="#rute" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Rute</a>
                </div>

                <div>
                    @if(!($event->registration_open_at && now() < $event->registration_open_at) && !($event->registration_close_at && now() > $event->registration_close_at))
                    <a href="#register" class="bg-brand-900 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-brand-900/20 hover:bg-brand-800 hover:-translate-y-0.5 transition-all">
                        Daftar Sekarang
                    </a>
                    @else
                    <button disabled class="bg-gray-200 text-gray-400 px-6 py-2.5 rounded-full text-sm font-bold cursor-not-allowed">
                        Ditutup
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <header class="relative bg-white pt-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white -z-10"></div>
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-brand-100 rounded-full blur-3xl opacity-50 -z-10"></div>
        
        <div class="max-w-7xl mx-auto px-4 pt-16 pb-24 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-12">
            
            <div class="flex-1 text-center md:text-left z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-100 text-brand-600 text-xs font-bold uppercase tracking-wider mb-6">
                    <span class="w-2 h-2 rounded-full bg-action-500 animate-pulse"></span>
                    {{ $event->start_at->format('d F Y') }}
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight leading-[1.1] mb-6">
                    Run Your <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-action-500">Best Race.</span>
                </h1>
                
                <p class="text-lg text-slate-600 mb-8 leading-relaxed max-w-lg mx-auto md:mx-0">
                    {{ $event->short_description ?? 'Tantang batas kemampuanmu di event lari paling bergengsi tahun ini. Rute aman, sterilisasi penuh, dan atmosfer yang luar biasa.' }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                    <a href="#register" class="px-8 py-4 bg-action-500 text-white font-bold rounded-xl shadow-xl shadow-action-500/20 hover:bg-action-600 transition text-center">
                        Amankan Slot
                    </a>
                    <a href="#about" class="px-8 py-4 bg-white text-slate-700 border border-slate-200 font-bold rounded-xl hover:bg-gray-50 transition text-center">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
                
                <div class="mt-12 grid grid-cols-3 gap-6 border-t border-gray-100 pt-8">
                    <div>
                        <p class="text-3xl font-bold text-slate-900">{{ $categories->count() }}</p>
                        <p class="text-xs text-slate-500 font-semibold uppercase">Kategori</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-slate-900">{{ $event->start_at->format('H:i') }}</p>
                        <p class="text-xs text-slate-500 font-semibold uppercase">Start Time</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-slate-900">Map</p>
                        <p class="text-xs text-slate-500 font-semibold uppercase">{{ $event->location_name }}</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 relative w-full max-w-lg mx-auto">
                <div class="absolute inset-0 bg-brand-600 rounded-[2rem] rotate-6 opacity-10"></div>
                @if($event->hero_image)
                    <img src="{{ asset('storage/' . $event->hero_image) }}" class="relative rounded-[2rem] shadow-2xl w-full object-cover aspect-[4/5] transform hover:-rotate-1 transition duration-500">
                @else
                    <img src="https://images.unsplash.com/photo-1552674605-46d531d0646c?auto=format&fit=crop&q=80&w=800" class="relative rounded-[2rem] shadow-2xl w-full object-cover aspect-[4/5]">
                @endif
                
                <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl border border-gray-50 flex items-center gap-4 animate-bounce" style="animation-duration: 3s;">
                    <div class="bg-green-100 p-3 rounded-full text-green-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900">Certified Course</p>
                        <p class="text-xs text-slate-500">AIMS Measured</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="categories" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 max-w-2xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Pilih Tantanganmu</h2>
                <p class="text-slate-600">Setiap jarak memiliki tantangannya sendiri. Pilih kategori yang sesuai dengan target latihanmu.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
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
                <div class="group bg-white rounded-3xl border border-gray-100 shadow-card hover:shadow-xl hover:border-brand-200 transition-all duration-300 relative overflow-hidden flex flex-col">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-action-500"></div>
                    
                    <div class="p-8 flex-grow">
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-brand-50 text-brand-700 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">{{ $cat->distance_km }} KM</span>
                            @if($cat->quota < 50 && $cat->quota > 0)
                                <span class="text-xs text-action-600 font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"/></svg>
                                    Segera Habis
                                </span>
                            @endif
                        </div>

                        <h3 class="text-3xl font-bold text-slate-900 mb-2">{{ $cat->name }}</h3>
                        <p class="text-slate-500 text-sm mb-6">Cut Off Time: <span class="font-bold text-slate-900">{{ $cat->cot_hours }} Jam</span></p>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Jersey & BIB
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Finisher Medal
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Refreshment & Medic
                            </li>
                        </ul>
                    </div>

                    <div class="p-8 bg-gray-50 border-t border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-slate-500 text-sm">Biaya Pendaftaran</span>
                            <div class="text-right">
                                @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                    <div class="text-xs font-bold text-slate-400 line-through">Rp {{ number_format($priceRegular/1000, 0) }}k</div>
                                @endif
                                <div class="text-2xl font-bold text-brand-900">Rp {{ number_format($displayPrice/1000, 0) }}k</div>
                            </div>
                        </div>
                        <a href="#register" class="block w-full py-3 bg-white border-2 border-brand-600 text-brand-600 font-bold text-center rounded-xl hover:bg-brand-600 hover:text-white transition group-hover:shadow-lg">
                            Daftar Kategori Ini
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="racepack" class="py-24 bg-slate-900 text-white overflow-hidden relative">
        <div class="absolute top-0 right-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                
                <div>
                    <span class="text-action-500 font-bold uppercase tracking-widest text-sm">Event Merch</span>
                    <h2 class="text-4xl md:text-5xl font-extrabold mt-2 mb-6">Premium Race Pack</h2>
                    <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                        Kami bekerja sama dengan apparel olahraga terkemuka untuk menghadirkan jersey yang nyaman, ringan, dan *breathable*.
                    </p>

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-action-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-xl">High Performance Jersey</h4>
                                <p class="text-slate-400 text-sm mt-1">Material Dry-Fit premium anti bakteri.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-action-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-xl">Exclusive Finisher Medal</h4>
                                <p class="text-slate-400 text-sm mt-1">Desain 3D Zinc Alloy yang solid dan berat.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-brand-600 to-blue-500 rounded-3xl transform rotate-6 opacity-30 blur-lg"></div>
                    <div class="relative bg-slate-800 border border-slate-700 rounded-3xl p-2 overflow-hidden shadow-2xl">
                         @if($event->jersey_image)
                             <img src="{{ asset('storage/' . $event->jersey_image) }}" class="rounded-2xl w-full">
                         @else
                             <div class="bg-slate-700 aspect-video rounded-2xl flex items-center justify-center text-slate-500">Jersey Preview</div>
                         @endif
                    </div>
                    <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-white p-2 rounded-full shadow-2xl border-4 border-slate-900 hidden md:block">
                         @if($event->medal_image)
                             <img src="{{ asset('storage/' . $event->medal_image) }}" class="rounded-full w-full h-full object-cover">
                         @else
                             <div class="bg-slate-200 w-full h-full rounded-full flex items-center justify-center text-xs text-slate-500 font-bold text-center">Medal<br>Preview</div>
                         @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    @include('events.partials.sponsor-carousel', [
        'gradientFrom' => 'from-white',
        'titleColor' => 'text-slate-400',
        'containerClass' => 'bg-white border border-slate-100 shadow-sm',
        'sectionClass' => 'py-20 bg-white'
    ])

    @include('events.partials.prizes-section', ['categories' => $categories])

    <section id="register" class="py-24 bg-brand-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @php
                $now = now();
                $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at) && !($event->registration_close_at && $now > $event->registration_close_at);

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
            @endphp

            @if(!$isRegOpen)
                <div class="text-center bg-white p-12 rounded-3xl shadow-sm max-w-2xl mx-auto">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-400">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Pendaftaran Ditutup</h3>
                    <p class="text-slate-500">Mohon maaf, pendaftaran untuk event ini belum dibuka atau sudah berakhir.</p>
                </div>
            @else
                
                <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="flex flex-col lg:flex-row gap-8">
                    @csrf

                    @if(request('payment') === 'pending')
                        <div class="w-full bg-yellow-50 border border-yellow-200 text-yellow-900 p-5 rounded-2xl">
                            <div class="font-bold">Pembayaran masih pending</div>
                            <div class="text-sm text-slate-700 mt-1">Jika popup Midtrans tertutup/refresh, Anda bisa melanjutkan tanpa registrasi ulang.</div>
                            <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-block mt-3 bg-yellow-400 hover:bg-yellow-300 text-black font-bold px-4 py-2 rounded-xl">Lanjutkan Pembayaran</a>
                        </div>
                    @elseif(request('payment') === 'success')
                        <div class="w-full bg-green-50 border border-green-200 text-green-900 p-5 rounded-2xl">
                            <div class="font-bold">Pembayaran berhasil</div>
                            <div class="text-sm text-slate-700 mt-1">Jika belum menerima konfirmasi, coba refresh beberapa saat lagi.</div>
                        </div>
                    @endif
                    
                    <div class="flex-1 space-y-6">
                        <div class="bg-white p-8 rounded-3xl shadow-soft">
                            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">1</span>
                                Penanggung Jawab (PIC)
                            </h3>
                            <div class="grid gap-5">
                                <input type="text" name="pic_name" id="pic_name" placeholder="Nama Lengkap PIC" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none" required>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <input type="email" name="pic_email" id="pic_email" placeholder="Email Aktif" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none" required>
                                    <input type="text" name="pic_phone" id="pic_phone" placeholder="Nomor WhatsApp" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-8 rounded-3xl shadow-soft">
                            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">2</span>
                                Data Peserta Lari
                            </h3>
                            
                            <div id="participantsWrapper">
                                <div class="participant-item p-6 bg-slate-50 rounded-2xl border border-slate-100 mb-4 relative group" data-index="0">
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-bold text-slate-400 uppercase participant-title">Peserta #1</p>
                                            <button type="button" class="copy-pic-btn text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-0.5 rounded transition" onclick="copyFromPic(this)">
                                                Isi Data PIC
                                            </button>
                                            <button type="button" class="copy-prev-btn text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-0.5 rounded transition hidden" onclick="copyFromPrev(this)">
                                                Salin Peserta Sebelumnya
                                            </button>
                                        </div>
                                        <button type="button" class="text-xs text-red-500 hover:text-red-700 font-medium remove-participant hidden">Hapus</button>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kategori</label>
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
                                                    <div class="p-4 bg-white border border-slate-200 rounded-xl peer-checked:border-brand-500 peer-checked:ring-2 peer-checked:ring-brand-500/20 transition hover:border-brand-300">
                                                        <div class="flex justify-between items-center mb-1">
                                                            <span class="font-bold text-slate-900">{{ $cat->name }}</span>
                                                            <span class="text-sm font-bold text-brand-600">
                                                                @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                    <span class="text-xs text-slate-400 line-through mr-1">Rp {{ number_format($priceRegular/1000,0) }}k</span>
                                                                @endif
                                                                Rp {{ number_format($displayPrice/1000,0) }}k
                                                            </span>
                                                        </div>
                                                        <p class="text-xs text-slate-500">{{ $cat->distance_km }}KM • {{ $cat->quota }} Slot</p>
                                                    </div>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="text" name="participants[0][name]" placeholder="Nama Peserta (di BIB)" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                            <select name="participants[0][gender]" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                                <option value="">Pilih Gender</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="email" name="participants[0][email]" placeholder="Email Peserta" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                            <input type="date" name="participants[0][date_of_birth]" placeholder="Tanggal Lahir" aria-label="Tanggal Lahir" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="text" name="participants[0][phone]" placeholder="No. HP / WA" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                            <input type="text" name="participants[0][id_card]" placeholder="No. KTP/SIM/Kartu Pelajar" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4">
                                            <textarea name="participants[0][address]" placeholder="Alamat Peserta" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required maxlength="500" rows="3"></textarea>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="text" name="participants[0][emergency_contact_name]" placeholder="Nama Kontak Darurat" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                            <input type="text" name="participants[0][emergency_contact_number]" placeholder="No. Kontak Darurat" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                             <select name="participants[0][jersey_size]" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none" required>
                                                <option value="">Ukuran Jersey</option>
                                                @foreach(['S','M','L','XL','XXL'] as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                            </select>
                                            <input type="text" name="participants[0][target_time]" placeholder="Target Waktu (Opsional)" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-brand-500 outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addParticipantBtn" class="w-full py-4 border-2 border-dashed border-slate-200 rounded-2xl text-slate-500 font-bold hover:border-brand-500 hover:text-brand-600 transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Peserta Lain
                            </button>
                        </div>
                    </div>

                    <div class="lg:w-96">
                        <div class="sticky top-24 space-y-4">
                            <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200 border border-slate-100">
                                <h4 class="font-bold text-lg text-slate-900 mb-4">Ringkasan Biaya</h4>
                                
                                <!-- Coupon Section -->
                                <div class="mb-6">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Kode Promo</label>
                                    <div class="flex gap-2">
                                        <input type="text" id="coupon_code" placeholder="KODE..." class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-sm focus:bg-white focus:border-brand-500 outline-none transition uppercase font-bold text-brand-900">
                                        <button type="button" id="applyCouponBtn" class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-600/20">Pakai</button>
                                    </div>
                                    <div id="couponMessage" class="mt-2 text-xs font-medium"></div>
                                    <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                                </div>

                                <div class="space-y-3 mb-6">
                                    <div class="flex justify-between text-sm text-slate-600">
                                        <span>Subtotal</span>
                                        <span id="subtotalDisplay" class="font-semibold text-slate-900">Rp 0</span>
                                    </div>
                                    
                                    <div class="flex justify-between text-sm text-green-600 hidden" id="discountRow">
                                        <span>Diskon</span>
                                        <span class="font-bold" id="discountDisplay">-Rp 0</span>
                                    </div>

                                    <div class="flex justify-between text-sm text-slate-600">
                                        <span>Biaya Admin</span>
                                        <span class="font-semibold text-slate-900" id="feeDisplay">Rp 0</span>
                                    </div>
                                    <div class="border-t border-dashed border-slate-200 pt-3 flex justify-between items-center">
                                        <span class="font-bold text-slate-900">Total</span>
                                        <span id="totalDisplay" class="text-2xl font-extrabold text-brand-600">Rp 0</span>
                                    </div>
                                </div>

                                @if($event->terms_and_conditions)
                                <div class="mb-6">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="terms_agreed" required class="mt-1 w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                                        <span class="text-xs text-slate-500 leading-snug">
                                            Saya setuju dengan <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="text-brand-600 underline font-bold">Syarat & Ketentuan</button> event ini.
                                        </span>
                                    </label>
                                </div>
                                @endif

                                <div class="mb-6">
                                    <label class="text-sm font-bold text-slate-700 mb-2 block">Metode Pembayaran</label>
                                    <div class="space-y-2">
                                        @if($showMidtrans)
                                        <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-brand-600 transition bg-slate-50">
                                            <input type="radio" name="payment_method" value="midtrans" class="w-4 h-4 text-brand-600 focus:ring-brand-600" {{ $showMidtrans && !$showMoota ? 'checked' : '' }} required>
                                            <div>
                                                <span class="block text-sm font-bold text-slate-900">Otomatis (Midtrans)</span>
                                                <span class="text-xs text-slate-500">QRIS, VA, E-Wallet</span>
                                            </div>
                                        </label>
                                        @endif

                                        @if($showMoota)
                                        <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-brand-600 transition bg-slate-50">
                                            <input type="radio" name="payment_method" value="moota" class="w-4 h-4 text-brand-600 focus:ring-brand-600" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                            <div>
                                                <span class="block text-sm font-bold text-slate-900">Transfer Bank (Moota)</span>
                                                <span class="text-xs text-slate-500">Verifikasi Otomatis</span>
                                            </div>
                                        </label>
                                        @endif
                                    </div>
                                </div>

                                @if(env('RECAPTCHA_SITE_KEY'))
                                    <div class="mb-6 flex justify-center">
                                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="light"></div>
                                    </div>
                                @endif

                                <button type="submit" id="submitBtn" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-slate-800 hover:shadow-lg transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Lanjut Pembayaran
                                </button>
                                
                                <div class="mt-4 text-center">
                                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Secure Payment by Midtrans</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="font-bold text-slate-900 text-lg mb-2">{{ $event->name }}</p>
            <p class="text-slate-500 text-sm">&copy; {{ date('Y') }} Official Event. All rights reserved.</p>
        </div>
    </footer>

    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Syarat & Ketentuan</h3>
                    <button onclick="document.getElementById('termsModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500">✕</button>
                </div>
                <div class="p-8 overflow-y-auto prose prose-sm max-w-none text-slate-600">
                    {!! $event->terms_and_conditions !!}
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Formatter Currency
        const formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        const eventId = {{ $event->id }};
        const eventSlug = "{{ $event->slug }}";
        const platformFee = {{ $event->platform_fee ?? 0 }};
        const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};

        // --- Coupon Variables ---
        let appliedCoupon = null;
        let discountAmount = 0;

        // Update Price Logic
        const wrapper = document.getElementById('participantsWrapper');
        const addBtn = document.getElementById('addParticipantBtn');
        const subDisplay = document.getElementById('subtotalDisplay');
        const totalDisplay = document.getElementById('totalDisplay');
        const feeDisplay = document.getElementById('feeDisplay');
        
        // Template initialization
        let template;
        // Ensure template is captured after DOM is ready or immediately if at end of body
        const firstItem = wrapper.querySelector('.participant-item');
        if(firstItem) {
            template = firstItem.cloneNode(true);
        }
        
        let participantIndex = 1;

        function computePayableSubtotalAndCount() {
            const categoryCounts = new Map();
            const categoryPrices = new Map();
            let selectedCount = 0;

            document.querySelectorAll('.participant-item').forEach(item => {
                const checkedRadio = item.querySelector('.cat-radio:checked');
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

        function updatePrice() {
            const { subtotal, selectedCount } = computePayableSubtotalAndCount();
            
            const fee = selectedCount * platformFee;
            let total = subtotal + fee - discountAmount;
            if(total < 0) total = 0;

            if(subDisplay) subDisplay.textContent = formatRupiah(subtotal);
            if(feeDisplay) feeDisplay.textContent = formatRupiah(fee);
            
            if(discountAmount > 0) {
                document.getElementById('discountRow').classList.remove('hidden');
                document.getElementById('discountDisplay').textContent = '- ' + formatRupiah(discountAmount);
            } else {
                document.getElementById('discountRow').classList.add('hidden');
            }

            if(totalDisplay) totalDisplay.textContent = formatRupiah(total);
        }
        
        function resetCoupon() {
            if (appliedCoupon || discountAmount > 0) {
                appliedCoupon = null;
                discountAmount = 0;
                document.getElementById('coupon_code').value = '';
                document.getElementById('coupon_code_hidden').value = '';
                document.getElementById('couponMessage').innerHTML = '';
            }
        }

        // Add Participant
        if(addBtn) {
            addBtn.addEventListener('click', () => {
                resetCoupon();
                if(!template) return;
                
                const newItem = template.cloneNode(true);
                const idx = participantIndex++;
                
                newItem.setAttribute('data-index', idx);
                newItem.querySelector('.participant-title').textContent = `Peserta #${wrapper.children.length + 1}`;

                // Show/Hide Copy Prev Button
                const copyPrevBtn = newItem.querySelector('.copy-prev-btn');
                if (copyPrevBtn) {
                    if (idx > 0) {
                        copyPrevBtn.classList.remove('hidden');
                    } else {
                        copyPrevBtn.classList.add('hidden');
                    }
                }
                
                newItem.querySelectorAll('input, select').forEach(el => {
                    const name = el.getAttribute('name');
                    if(name) {
                        el.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                        if(el.type !== 'radio' && el.type !== 'hidden') {
                            el.value = '';
                        }
                        if(el.type === 'radio') {
                            el.checked = false;
                        }
                    }
                });

                const removeBtn = newItem.querySelector('.remove-participant');
                if(removeBtn) removeBtn.classList.remove('hidden');
                
                wrapper.appendChild(newItem);
                
                newItem.querySelectorAll('.cat-radio').forEach(r => {
                    r.addEventListener('change', updatePrice);
                });
            });
        }

        // Remove Participant
        if(wrapper) {
            wrapper.addEventListener('click', (e) => {
                if(e.target.classList.contains('remove-participant')) {
                    e.target.closest('.participant-item').remove();
                    
                    wrapper.querySelectorAll('.participant-item').forEach((item, i) => {
                        item.querySelector('.participant-title').textContent = `Peserta #${i + 1}`;
                    });
                    
                    updatePrice();
                }
            });
        }

        document.querySelectorAll('.cat-radio').forEach(r => r.addEventListener('change', () => {
            resetCoupon();
            updatePrice();
        }));

        // Coupon Logic
        const couponBtn = document.getElementById('applyCouponBtn');
        if(couponBtn) {
            couponBtn.addEventListener('click', () => {
                const code = document.getElementById('coupon_code').value;
                if(!code) { alert('Masukkan kode kupon'); return; }

                let subtotal = 0;
                subtotal = computePayableSubtotalAndCount().subtotal;

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
                        document.getElementById('couponMessage').innerHTML = '<span class="text-green-600">Kupon berhasil digunakan!</span>';
                        updatePrice();
                    } else {
                        document.getElementById('couponMessage').innerHTML = `<span class="text-red-500">${data.message}</span>`;
                        discountAmount = 0;
                        document.getElementById('coupon_code_hidden').value = '';
                        updatePrice();
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

        // Form Submit
        const form = document.getElementById('registrationForm');

        function copyFromPic(btn) {
            const participantItem = btn.closest('.participant-item');
            const picName = document.getElementById('pic_name').value;
            const picEmail = document.getElementById('pic_email').value;
            const picPhone = document.getElementById('pic_phone').value;

            if (picName) participantItem.querySelector('input[name*="[name]"]').value = picName;
            if (picEmail) participantItem.querySelector('input[name*="[email]"]').value = picEmail;
            if (picPhone) participantItem.querySelector('input[name*="[phone]"]').value = picPhone;
        }

        function copyFromPrev(btn) {
            const currentItem = btn.closest('.participant-item');
            const currentIndex = parseInt(currentItem.dataset.index);
            
            if (currentIndex > 0) {
                const prevItem = document.querySelector(`.participant-item[data-index="${currentIndex - 1}"]`);
                if (prevItem) {
                    const fields = ['emergency_contact_name', 'emergency_contact_number']; // Fields to copy
                    
                    fields.forEach(field => {
                        const prevValue = prevItem.querySelector(`input[name*="[${field}]"]`).value;
                        if (prevValue) {
                            currentItem.querySelector(`input[name*="[${field}]"]`).value = prevValue;
                        }
                    });
                }
            }
        }

        if(form) {
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
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success && data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`; },
                            onPending: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; },
                            onError: function(result){ alert("Pembayaran gagal"); btn.disabled=false; btn.innerHTML=originalText; },
                            onClose: function(){ btn.disabled=false; btn.innerHTML=originalText; }
                        });
                    } else if(data.success) {
                         window.location.href = `{{ route("events.show", $event->slug) }}?success=true`;
                    } else {
                        alert(data.message || 'Terjadi kesalahan validasi.');
                        btn.disabled=false; btn.innerHTML=originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal menghubungi server.');
                    btn.disabled=false; btn.innerHTML=originalText;
                });
            });
        }

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
