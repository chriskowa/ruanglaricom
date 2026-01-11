<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $event->name }} - Official Event</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if(env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <meta name="description" content="{{ strip_tags($event->short_description ?? $event->name) }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com'); @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            600: '#1e40af', /* Royal Blue (Logo Base) */
                            700: '#1e3a8a',
                            900: '#0f172a', /* Dark Navy */
                        },
                        accent: {
                            500: '#f97316', /* Orange (Energy/Action) */
                            600: '#ea580c',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                        'card': '0 0 0 1px rgba(0,0,0,0.03), 0 2px 8px rgba(0,0,0,0.04)',
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Styles */
        body { background-color: #F8FAFC; color: #1e293b; }
        
        /* Shape Divider untuk Hero */
        .custom-shape-divider-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }
        .custom-shape-divider-bottom svg {
            position: relative;
            display: block;
            width: calc(130% + 1.3px);
            height: 100px;
        }
        .custom-shape-divider-bottom .shape-fill {
            fill: #FFFFFF;
        }

        /* Reveal Animation */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.5, 0, 0, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        
        /* Sticky Navigation Shadow transition */
        .nav-scrolled { background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen">

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

    <nav class="fixed w-full z-50 transition-all duration-300 bg-white/80 backdrop-blur-sm border-b border-slate-100" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="#top" class="flex items-center gap-3">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-10 w-auto hover:scale-105 transition">
                    @else
                        <div class="h-10 w-10 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-brand-600/30">P</div>
                        <span class="font-extrabold text-xl tracking-tight text-slate-900 uppercase">{{ $event->name }}</span>
                    @endif
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#categories" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Kategori</a>
                    <a href="#fasilitas" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Fasilitas</a>
                    <a href="#racepack" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Race Pack</a>
                    <a href="#rute" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Rute</a>
                    
                    @if($isRegOpen)
                        <a href="#register" class="bg-brand-600 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-brand-600/20 hover:bg-brand-700 hover:-translate-y-0.5 transition-all">
                            Daftar Sekarang
                        </a>
                    @else
                        <span class="bg-slate-100 text-slate-400 px-6 py-2.5 rounded-full text-sm font-bold cursor-not-allowed border border-slate-200">
                            Closed
                        </span>
                    @endif
                </div>

                <button id="mobileMenuBtn" class="md:hidden text-slate-800 p-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-slate-100 absolute w-full left-0 px-4 py-4 space-y-4 shadow-xl">
                <a href="#categories" class="block text-slate-700 font-medium">Kategori</a>
                <a href="#racepack" class="block text-slate-700 font-medium">Race Pack</a>
                <a href="#register" class="block text-brand-600 font-bold">Registrasi</a>
            </div>
        </div>
    </nav>

    <header id="top" class="relative pt-20 overflow-hidden bg-slate-50">
        <div class="absolute inset-0 z-0">
            @if($event->hero_image)
                <img src="{{ asset('storage/' . $event->hero_image) }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-white via-white/80 to-transparent"></div>
            @else
                <div class="w-full h-full bg-slate-50" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 30px 30px;"></div>
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-100/50 rounded-full blur-3xl"></div>
            @endif
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 pt-16 pb-32 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-12">
            <div class="flex-1 text-center md:text-left reveal active">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ $event->start_at->format('d F Y') }} • {{ $event->location_name }}</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight leading-[1.1] mb-6">
                    {{ strtoupper($event->name) }}
                    <span class="text-brand-600 block">RUN FEST.</span>
                </h1>
                <div class="mb-8">
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed max-w-lg mx-auto md:mx-0 font-medium">
                        {!! $event->short_description ?? 'Rasakan sensasi berlari dengan pemandangan terbaik. Rute aman, steril, dan atmosfer kompetitif yang menyenangkan.' !!}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start mb-8">
                    @if($isRegOpen)
                    <a href="#register" class="px-8 py-4 bg-accent-500 text-white font-bold rounded-xl shadow-lg shadow-accent-500/20 hover:bg-accent-600 transition hover:-translate-y-1">
                        Amankan Slot
                    </a>
                    @else
                    <button disabled class="px-8 py-4 bg-slate-200 text-slate-400 font-bold rounded-xl cursor-not-allowed">
                        Pendaftaran Ditutup
                    </button>
                    @endif
                    <a href="#rute" class="px-8 py-4 bg-white text-slate-700 border border-slate-200 font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">
                        Lihat Rute
                    </a>
                </div>

                <!-- Countdown -->
                <div class="mb-2 text-center md:text-left">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $countdownLabel }}</span>
                </div>
                <div id="hero-countdown" class="flex gap-6 justify-center md:justify-start">
                    <div class="text-center bg-white p-3 rounded-xl border border-slate-100 shadow-sm min-w-[70px]">
                        <span class="block text-2xl font-bold text-brand-600" id="cd-days">00</span>
                        <span class="text-[10px] text-slate-500 uppercase font-bold">Hari</span>
                    </div>
                    <div class="text-center bg-white p-3 rounded-xl border border-slate-100 shadow-sm min-w-[70px]">
                        <span class="block text-2xl font-bold text-brand-600" id="cd-hours">00</span>
                        <span class="text-[10px] text-slate-500 uppercase font-bold">Jam</span>
                    </div>
                    <div class="text-center bg-white p-3 rounded-xl border border-slate-100 shadow-sm min-w-[70px]">
                        <span class="block text-2xl font-bold text-brand-600" id="cd-minutes">00</span>
                        <span class="text-[10px] text-slate-500 uppercase font-bold">Menit</span>
                    </div>
                    <div class="text-center bg-white p-3 rounded-xl border border-slate-100 shadow-sm min-w-[70px]">
                        <span class="block text-2xl font-bold text-brand-600" id="cd-seconds">00</span>
                        <span class="text-[10px] text-slate-500 uppercase font-bold">Detik</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 w-full max-w-md mx-auto reveal delay-200">
                <div class="bg-white p-8 rounded-[2rem] shadow-2xl border border-slate-100 transform rotate-2 hover:rotate-0 transition duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-sm text-slate-400 font-bold uppercase">Event Status</p>
                            <p class="text-2xl font-bold text-slate-900">Ready to Race</p>
                        </div>
                        <div class="w-12 h-12 bg-brand-50 rounded-full flex items-center justify-center text-brand-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <span class="block text-3xl font-bold text-brand-600">{{ $categories->count() }}</span>
                            <span class="text-xs text-slate-500 font-bold uppercase">Kategori</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <span class="block text-3xl font-bold text-brand-600">{{ $event->start_at->format('H:i') }}</span>
                            <span class="text-xs text-slate-500 font-bold uppercase">Flag Off</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="custom-shape-divider-bottom">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
    </header>

    <section id="categories" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">Pilih Kategori</h2>
                <p class="mt-4 text-lg text-slate-600">Sesuaikan dengan target latihanmu.</p>
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
                <div class="relative flex flex-col bg-white border border-slate-200 rounded-3xl shadow-card hover:shadow-xl hover:border-brand-200 transition-all duration-300 group reveal">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-brand-600 to-accent-500 rounded-t-3xl"></div>
                    
                    <div class="p-8 flex-1">
                        <div class="flex justify-between items-start mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-brand-50 text-brand-700">
                                {{ $cat->distance_km ?? '?' }} KM
                            </span>
                            @if($cat->quota < 50 && $cat->quota > 0)
                                <span class="text-xs font-bold text-red-500 animate-pulse">Sisa Sedikit!</span>
                            @endif
                        </div>

                        <h3 class="text-2xl font-bold text-slate-900 mb-2">{{ $cat->name }}</h3>
                        <p class="text-slate-500 text-sm mb-6">Cut Off Time: <strong class="text-slate-900">{{ $cat->cot_hours ?? '-' }} Jam</strong></p>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-sm text-slate-600">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Official Jersey & BIB
                            </li>
                            <li class="flex items-center text-sm text-slate-600">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Finisher Medal (All Finishers)
                            </li>
                            <li class="flex items-center text-sm text-slate-600">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Refreshment & Medic
                            </li>
                        </ul>
                    </div>
                    
                    <div class="p-6 bg-slate-50 rounded-b-3xl border-t border-slate-100">
                        <div class="flex items-baseline justify-between mb-4">
                            <span class="text-sm text-slate-500 font-medium">Biaya Pendaftaran</span>
                            <span class="text-2xl font-bold text-slate-900">Rp {{ number_format($cat->price_regular/1000, 0) }}k</span>
                        </div>
                        <a href="#register" class="block w-full py-3 px-4 bg-white border-2 border-brand-600 text-brand-600 font-bold text-center rounded-xl hover:bg-brand-600 hover:text-white transition group-hover:shadow-md">
                            Daftar Kategori Ini
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="fasilitas" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24 reveal">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:-translate-y-1 transition">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Medical Safety</h3>
                    <p class="text-slate-500">Ambulans & Tim Medis profesional siaga di sepanjang rute.</p>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:-translate-y-1 transition">
                    <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Hydration Point</h3>
                    <p class="text-slate-500">Water station tersedia setiap 2.5 KM (Air & Isotonik).</p>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:-translate-y-1 transition">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Timing System</h3>
                    <p class="text-slate-500">Pencatatan waktu akurat menggunakan chip system.</p>
                </div>
            </div>

            @if($showSection('jersey'))
            <div id="racepack" class="bg-brand-900 rounded-[3rem] p-8 md:p-16 text-white overflow-hidden relative reveal">
                <div class="absolute inset-0 opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center relative z-10">
                    <div>
                        <div class="inline-block bg-accent-500 px-3 py-1 rounded text-xs font-bold uppercase tracking-widest mb-4">Premium Gear</div>
                        <h2 class="text-4xl md:text-5xl font-black mb-6">THE RACE PACK</h2>
                        <p class="text-slate-300 text-lg mb-8 leading-relaxed">
                            Kami menghadirkan jersey dengan material <em>Dry-Fit</em> kualitas ekspor yang ringan, menyerap keringat, dan anti-bakteri. Didesain untuk kenyamanan maksimal saat berlari.
                        </p>
                        <div class="flex gap-6">
                            <div class="border border-white/20 rounded-2xl p-4 bg-white/5 backdrop-blur-sm">
                                <p class="text-sm text-slate-400 uppercase font-bold">Material</p>
                                <p class="text-lg font-bold">Premium Dry-Fit</p>
                            </div>
                            <div class="border border-white/20 rounded-2xl p-4 bg-white/5 backdrop-blur-sm">
                                <p class="text-sm text-slate-400 uppercase font-bold">Medal</p>
                                <p class="text-lg font-bold">Zinc Alloy 3D</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="relative bg-gradient-to-tr from-slate-800 to-slate-700 border border-slate-600 rounded-3xl p-2 shadow-2xl transform rotate-3 hover:rotate-0 transition duration-500">
                             @if($event->jersey_image)
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" class="w-full rounded-2xl" alt="Jersey">
                             @else
                                <div class="aspect-video bg-slate-800 rounded-2xl flex items-center justify-center text-slate-500 font-bold">Jersey Preview</div>
                             @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>

    @include('events.partials.prizes-section', ['categories' => $categories])

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
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider participant-title">Peserta #1</span>
                                        <button type="button" class="remove-participant hidden text-red-500 hover:text-red-600 text-xs font-bold uppercase">Hapus</button>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori Lomba</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($categories as $cat)
                                                <label class="cursor-pointer relative">
                                                    <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="peer sr-only cat-radio" data-price="{{ $cat->price_regular }}" required>
                                                    <div class="p-3 bg-white border border-slate-300 rounded-xl peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600 transition hover:border-brand-400">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-bold text-slate-900 text-sm">{{ $cat->name }}</span>
                                                            <span class="text-xs font-bold text-brand-600">Rp {{ number_format($cat->price_regular/1000,0) }}k</span>
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
                                            <input type="text" name="participants[0][phone]" placeholder="No. HP Peserta" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
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
                                        <input type="text" name="participants[0][emergency_contact_number]" placeholder="No. Kontak Darurat" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-600 outline-none" required>
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
                    countdownContainer.innerHTML = '<div class="text-brand-600 font-bold text-xl bg-white px-6 py-3 rounded-xl border border-slate-100 shadow-sm">Event Telah Dimulai! 🏃💨</div>';
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
    </script>
</body>
</html>
