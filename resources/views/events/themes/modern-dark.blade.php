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
    <title>{{ $seo['title'] ?? ($event->name.' | RuangLari') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? strip_tags($event->short_description ?? $event->name) }}" />
    <meta name="keywords" content="{{ $seo['keywords'] ?? '' }}">
    <link rel="canonical" href="{{ $seo['url'] ?? route('events.show', $event->slug) }}">
    <meta name="theme-color" content="{{ $event->theme_colors['dark'] ?? '#0f172a' }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['title'] ?? ($event->name.' | RuangLari') }}">
    <meta property="og:description" content="{{ $seo['description'] ?? strip_tags($event->short_description ?? $event->name) }}">
    <meta property="og:url" content="{{ $seo['url'] ?? route('events.show', $event->slug) }}">
    <meta property="og:image" content="{{ $seo['image'] ?? ($event->getHeroImageUrl() ?? asset('images/ruanglari_green.png')) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] ?? ($event->name.' | RuangLari') }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? strip_tags($event->short_description ?? $event->name) }}">
    <meta name="twitter:image" content="{{ $seo['image'] ?? ($event->getHeroImageUrl() ?? asset('images/ruanglari_green.png')) }}">
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
    
    @php $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com'); @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '{{ $event->theme_colors["dark"] ?? "#0f172a" }}',
                        card: '{{ $event->theme_colors["card"] ?? "#1e293b" }}',
                        input: '{{ $event->theme_colors["input"] ?? "#020617" }}',
                        neon: '{{ $event->theme_colors["neon"] ?? "#ccff00" }}',
                        neonHover: '{{ $event->theme_colors["neonHover"] ?? "#b3e600" }}',
                        accent: '{{ $event->theme_colors["accent"] ?? "#3b82f6" }}',
                        danger: '{{ $event->theme_colors["danger"] ?? "#ef4444" }}',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .text-glow { text-shadow: 0 0 20px rgba(204, 255, 0, 0.4); }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        
        /* Animation Utility Classes */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.5, 0, 0, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* FAQ Transition */
        .accordion-content { transition: max-height 0.3s ease-out, padding 0.3s ease; max-height: 0; overflow: hidden; }
        .accordion-btn[aria-expanded="true"] svg { transform: rotate(180deg); }
        .accordion-btn[aria-expanded="true"] { color: #ccff00; }
    </style>
</head>
<body class="bg-dark text-slate-200 font-sans antialiased flex flex-col min-h-screen selection:bg-neon selection:text-dark">

    @php
        $pa = $event->premium_amenities ?? null;
        $hasPa = !is_null($pa);
        $showSection = function($key) use ($pa, $hasPa) {
            if (!$hasPa) return true;
            return isset($pa[$key]['enabled']) && $pa[$key]['enabled'];
        };
    @endphp

    <nav class="fixed w-full z-50 border-b border-white/5 bg-dark/80 backdrop-blur-md transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="#top" class="flex items-center gap-3 group">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" alt="{{ $event->name }}" class="h-9 w-auto rounded-md shadow-lg group-hover:scale-105 transition">
                    @else
                        <div class="w-3 h-8 bg-neon skew-x-[-12deg] group-hover:shadow-[0_0_15px_#ccff00] transition duration-300"></div>
                        <span class="text-xl font-bold tracking-tight text-white uppercase">{{ $event->name }}</span>
                    @endif                    
                </a>
                
                <div class="hidden md:flex items-center gap-8">
                    <a href="#fasilitas" class="text-sm font-medium text-slate-400 hover:text-white hover:underline decoration-neon decoration-2 underline-offset-4 transition-all">Fasilitas</a>
                    @if($showSection('gallery'))
                    <a href="#gallery" class="text-sm font-medium text-slate-400 hover:text-white hover:underline decoration-neon decoration-2 underline-offset-4 transition-all">Galeri</a>
                    @endif
                    @if($showSection('jersey') || $showSection('medal'))
                    <a href="#jersey" class="text-sm font-medium text-slate-400 hover:text-white hover:underline decoration-neon decoration-2 underline-offset-4 transition-all">Race Pack</a>
                    @endif
                    <a href="#lokasi" class="text-sm font-medium text-slate-400 hover:text-white hover:underline decoration-neon decoration-2 underline-offset-4 transition-all">Rute</a>
                    @if($showSection('faq'))
                    <a href="#faq" class="text-sm font-medium text-slate-400 hover:text-white hover:underline decoration-neon decoration-2 underline-offset-4 transition-all">FAQ</a>
                    @endif
                    
                    @php
                        $now = now();
                        $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at) && !($event->registration_close_at && $now > $event->registration_close_at);
                    @endphp

                    @if($isRegOpen)
                        <a href="#registrasi" class="px-6 py-2.5 bg-neon text-dark text-sm font-bold rounded-lg hover:bg-neonHover hover:-translate-y-0.5 transition shadow-[0_0_15px_rgba(204,255,0,0.3)]">
                            DAFTAR SEKARANG
                        </a>
                    @endif
                </div>

                <button id="navToggle" class="md:hidden text-white hover:text-neon p-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden bg-card border-t border-slate-700 absolute w-full left-0 px-4 py-4 space-y-3 shadow-2xl">
                <a href="#fasilitas" class="block py-2 text-slate-300 hover:text-neon font-medium">Fasilitas</a>
                @if($showSection('gallery'))
                <a href="#gallery" class="block py-2 text-slate-300 hover:text-neon font-medium">Galeri</a>
                @endif
                @if($showSection('jersey') || $showSection('medal'))
                <a href="#jersey" class="block py-2 text-slate-300 hover:text-neon font-medium">Race Pack</a>
                @endif
                <a href="#registrasi" class="block py-2 text-slate-300 hover:text-neon font-medium">Registrasi</a>
                @if($showSection('faq'))
                <a href="#faq" class="block py-2 text-slate-300 hover:text-neon font-medium">FAQ</a>
                @endif
            </div>
        </div>
    </nav>

    <main id="top" class="flex-grow">
        
        <section class="relative min-h-screen flex items-center pt-20 px-4 overflow-hidden">
            @php
                $heroBg = $event->hero_image ? asset('storage/' . $event->hero_image) : ($event->hero_image_url ?? '');
            @endphp
            @if($heroBg)
                <div class="absolute inset-0 z-0">
                    <img src="{{ $heroBg }}" class="w-full h-full object-cover opacity-50 scale-105 animate-[pulse_10s_ease-in-out_infinite]">
                    <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/80 to-dark/30"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-dark via-transparent to-transparent"></div>
                </div>
            @else
                <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-neon/10 rounded-full blur-[120px] -z-10 animate-float"></div>
                <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent/10 rounded-full blur-[100px] -z-10"></div>
            @endif

            <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-left reveal active">
                    <div class="inline-flex items-center gap-3 mb-8 bg-white/5 backdrop-blur-md border border-white/10 rounded-full pl-2 pr-4 py-1.5 hover:bg-white/10 transition cursor-default">
                        <span class="bg-neon text-dark text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Official Event</span>
                        <span class="text-slate-300 text-xs font-mono border-l border-white/20 pl-3">{{ $event->location_name }}</span>
                    </div>

                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black tracking-tighter mb-6 leading-[0.9] text-white">
                        {{ strtoupper($event->name) }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-emerald-400 block mt-2 text-glow">RACE {{ $event->start_at->format('Y') }}</span>
                    </h1>

                    <div class="text-slate-300 text-lg md:text-xl mb-10 leading-relaxed max-w-xl font-light border-l-4 border-neon pl-6">
                        {!! $event->short_description ?? 'Tantang dirimu di rute terbaik tahun ini. Atmosfer kompetitif, fasilitas premium, dan pengalaman lari yang tak terlupakan.' !!}
                    </div>

                    <div class="flex flex-wrap gap-4">
                        @if($isRegOpen)
                            <a href="#registrasi" class="px-8 py-4 bg-neon text-dark font-black text-lg rounded-xl hover:bg-white transition-all shadow-[0_0_30px_rgba(204,255,0,0.4)] flex items-center gap-2 group">
                                AMANKAN SLOT
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        @else
                             <button disabled class="px-8 py-4 bg-slate-800 text-slate-500 font-bold text-lg rounded-xl cursor-not-allowed border border-slate-700">
                                REGISTRASI DITUTUP
                            </button>
                        @endif
                        <a href="#lokasi" class="px-8 py-4 bg-transparent border border-slate-600 text-white font-bold text-lg rounded-xl hover:border-white hover:bg-white/5 transition">
                            LIHAT RUTE
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-6 mt-16 border-t border-slate-800 pt-8">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Date</p>
                            <p class="text-white text-lg font-mono font-bold">{{ $event->start_at->format('d M') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Category</p>
                            <p class="text-neon text-lg font-mono font-bold">{{ $categories->count() }} Classes</p>
                        </div>
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Start</p>
                            <p class="text-white text-lg font-mono font-bold">{{ $event->start_at->format('H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block relative reveal delay-200">
                    <div class="relative z-10 bg-card/40 backdrop-blur-xl border border-white/10 p-2 rounded-3xl transform rotate-3 hover:rotate-0 transition duration-500 shadow-2xl">
                        @if($event->hero_image)
                            <img src="{{ asset('storage/' . $event->hero_image) }}" class="rounded-2xl w-full object-cover h-[500px]">
                        @else
                            <div class="rounded-2xl w-full h-[500px] bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                                <span class="text-slate-600 font-mono">Visual Event</span>
                            </div>
                        @endif
                        
                        <div class="bg-card border-y border-slate-800 relative z-20 -mt-8 mx-4 md:mx-auto max-w-5xl rounded-2xl shadow-2xl overflow-hidden reveal">
                          <div class="p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 bg-slate-900/80 backdrop-blur">
                              <div class="flex items-center gap-4">
                                  <div class="bg-neon/10 p-3 rounded-full animate-pulse">
                                      <svg class="w-6 h-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                  </div>
                                  <div>
                                      <h3 class="text-white font-bold text-lg uppercase tracking-widest">Race Starts In</h3>
                                      <p class="text-slate-400 text-xs">Bersiaplah di garis start!</p>
                                  </div>
                              </div>
                              
                              <div class="grid grid-cols-4 gap-2 md:gap-4 text-center" id="countdown-timer">
                                  <div class="bg-dark border border-slate-700 rounded-lg p-2 md:p-3 min-w-[70px]">
                                      <span class="block text-2xl md:text-3xl font-mono font-bold text-white" id="cd-days">00</span>
                                      <span class="text-[10px] text-slate-500 uppercase font-bold">Days</span>
                                  </div>
                                  <div class="bg-dark border border-slate-700 rounded-lg p-2 md:p-3 min-w-[70px]">
                                      <span class="block text-2xl md:text-3xl font-mono font-bold text-white" id="cd-hours">00</span>
                                      <span class="text-[10px] text-slate-500 uppercase font-bold">Hours</span>
                                  </div>
                                  <div class="bg-dark border border-slate-700 rounded-lg p-2 md:p-3 min-w-[70px]">
                                      <span class="block text-2xl md:text-3xl font-mono font-bold text-white" id="cd-minutes">00</span>
                                      <span class="text-[10px] text-slate-500 uppercase font-bold">Mins</span>
                                  </div>
                                  <div class="bg-dark border border-slate-700 rounded-lg p-2 md:p-3 min-w-[70px]">
                                      <span class="block text-2xl md:text-3xl font-mono font-bold text-neon" id="cd-seconds">00</span>
                                      <span class="text-[10px] text-slate-500 uppercase font-bold">Secs</span>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <script>
                          // Countdown Logic
                          const eventDate = new Date("{{ $event->start_at->format('Y-m-d H:i:s') }}").getTime();
                          
                          const timerInterval = setInterval(function() {
                              const now = new Date().getTime();
                              const distance = eventDate - now;

                              if (distance < 0) {
                                  clearInterval(timerInterval);
                                  document.getElementById("countdown-timer").innerHTML = "<div class='col-span-4 text-neon font-bold text-xl uppercase tracking-widest'>RACE STARTED / FINISHED</div>";
                                  return;
                              }

                              document.getElementById("cd-days").innerText = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
                              document.getElementById("cd-hours").innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
                              document.getElementById("cd-minutes").innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
                              document.getElementById("cd-seconds").innerText = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');
                          }, 1000);
                      </script>
                    </div>
                </div>
                
            </div>
        </section>

        <section class="py-20 bg-dark border-b border-slate-800">
          <div class="max-w-7xl mx-auto px-4">
              <div class="text-center mb-12 reveal">
                  <span class="text-neon font-mono font-bold uppercase tracking-widest text-sm">Race Categories</span>
                  <h2 class="text-3xl md:text-5xl font-black text-white mb-4">CHOOSE YOUR <span class="text-slate-600">DISTANCE</span></h2>
                  
                  <p class="text-slate-400 mt-2">Detail teknis untuk setiap kategori lomba.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  @foreach($categories as $cat)
                  <div class="bg-card border border-slate-700 rounded-2xl p-6 hover:border-neon transition group reveal hover:-translate-y-1">
                      <div class="flex justify-between items-start mb-6">
                          <div>
                              <h3 class="text-3xl font-black text-white italic">{{ $cat->name }}</h3>
                              <p class="text-slate-500 text-xs font-mono mt-1">{{ $cat->distance_km ?? '?' }} KM Distance</p>
                          </div>
                          <div class="bg-slate-800 p-2 rounded-lg text-neon group-hover:bg-neon group-hover:text-dark transition">
                              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                          </div>
                      </div>
                      
                      <div class="space-y-3 font-mono text-sm border-t border-slate-700 pt-4">
                          <div class="flex justify-between">
                              <span class="text-slate-400">Flag Off</span>
                              <span class="text-white font-bold">{{ $cat->start_time ? \Carbon\Carbon::parse($cat->start_time)->format('H:i') : 'TBA' }} WIB</span>
                          </div>
                          <div class="flex justify-between">
                              <span class="text-slate-400">Cut Off Time (COT)</span>
                              <span class="text-neon font-bold">{{ $cat->cot_hours ?? '-' }} Jam</span>
                          </div>
                          <div class="flex justify-between">
                              <span class="text-slate-400">Min. Usia</span>
                              <span class="text-white">{{ $cat->min_age ?? 17 }} Tahun</span>
                          </div>
                      </div>
                  </div>
                  @endforeach
              </div>
          </div>
        </section>

        <section id="fasilitas" class="py-24 bg-dark relative">
          <div class="max-w-7xl mx-auto px-4">
              <div class="flex flex-col md:flex-row justify-between items-end mb-16 reveal">
                  <div>
                      <span class="text-neon font-mono font-bold uppercase tracking-widest text-sm">Premium Amenities</span>
                      <h2 class="text-3xl md:text-5xl font-black text-white mt-2">RUNNER <span class="text-slate-600">EXPERIENCE</span></h2>
                  </div>
                  <p class="text-slate-400 max-w-md mt-4 md:mt-0 text-right text-sm md:text-base">
                      Kami memastikan kenyamanan dan keamanan Anda dari garis start hingga finish line dengan standar internasional.
                  </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                  @php $facilities = $event->facilities ?? []; @endphp

                  {{-- KONDISI 1: JIKA ADA DATA DARI DATABASE --}}
                  @if(!empty($facilities) && is_array($facilities) && count($facilities) > 0)
                      
                      @foreach($facilities as $index => $facility)
                          {{-- Logic: Item Pertama jadi 'Hero Card' (Besar) --}}
                          @if($index === 0)
                              <div class="md:col-span-2 md:row-span-2 bg-card border border-slate-700 rounded-3xl p-8 relative overflow-hidden group reveal">
                                  <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/90 z-10"></div>
                                  {{-- Check for uploaded image --}}
                                  @if(isset($facility['image']) && $facility['image'])
                                      <img src="{{ asset('storage/' . $facility['image']) }}" class="absolute inset-0 w-full h-full object-cover -z-0 transition duration-700 group-hover:scale-110 opacity-60">
                                  @else
                                      <img src="https://images.unsplash.com/photo-1596727147705-0043c7576566?auto=format&fit=crop&q=80&w=800" class="absolute inset-0 w-full h-full object-cover -z-0 transition duration-700 group-hover:scale-110 opacity-60">
                                  @endif
                                  
                                  <div class="relative z-20 h-full flex flex-col justify-between">
                                      <div class="flex justify-between items-start">
                                          <div class="bg-slate-900/80 backdrop-blur p-3 rounded-2xl border border-slate-600 group-hover:border-neon transition shadow-lg">
                                              @if(isset($facility['image']) && $facility['image'])
                                                {{-- Small icon not needed if main bg is image, but let's keep it or hide it --}}
                                                <svg class="w-8 h-8 text-white group-hover:text-neon transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                              @else
                                                <svg class="w-8 h-8 text-white group-hover:text-neon transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                              @endif
                                          </div>
                                          <span class="px-3 py-1 bg-neon text-dark text-[10px] font-bold rounded-full uppercase tracking-wider">Main Facility</span>
                                      </div>
                                      <div>
                                          <h3 class="text-3xl font-bold text-white mb-2 group-hover:text-neon transition">{{ $facility['name'] }}</h3>
                                          <p class="text-slate-200 text-sm leading-relaxed max-w-sm">
                                              {{ $facility['description'] }}
                                          </p>
                                      </div>
                                  </div>
                              </div>
                          @else
                              {{-- Logic: Item Selanjutnya (Card Kecil) --}}
                              <div class="bg-card border border-slate-800 rounded-3xl p-6 hover:border-neon/50 hover:bg-slate-800/50 transition duration-300 group reveal relative overflow-hidden">
                                  @if(isset($facility['image']) && $facility['image'])
                                      <div class="absolute inset-0 z-0">
                                          <img src="{{ asset('storage/' . $facility['image']) }}" class="w-full h-full object-cover opacity-20 group-hover:opacity-40 transition duration-500 group-hover:scale-110">
                                          <div class="absolute inset-0 bg-gradient-to-t from-card via-card/80 to-transparent"></div>
                                      </div>
                                  @endif

                                  <div class="relative z-10">
                                      <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition border border-slate-700 group-hover:border-neon shadow-lg">
                                          {{-- Rotasi Icon Sederhana Berdasarkan Index --}}
                                          @if($index % 4 == 1)
                                              <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg> {{-- Air --}}
                                          @elseif($index % 4 == 2)
                                              <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg> {{-- Medis --}}
                                          @elseif($index % 4 == 3)
                                              <svg class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg> {{-- Lock --}}
                                          @else
                                              <svg class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /></svg> {{-- Camera --}}
                                          @endif
                                      </div>
                                      <h3 class="text-lg font-bold text-white mb-2">{{ $facility['name'] }}</h3>
                                      <p class="text-slate-400 text-xs leading-relaxed">
                                          {{ \Illuminate\Support\Str::limit($facility['description'], 80) }}
                                      </p>
                                  </div>
                              </div>
                          @endif
                      @endforeach

                  {{-- KONDISI 2: JIKA TIDAK ADA DATA (TAMPILKAN DEFAULT STATIC DESIGN) --}}
                  @else
                      
                      <div class="md:col-span-2 md:row-span-2 bg-card border border-slate-700 rounded-3xl p-8 relative overflow-hidden group reveal">
                          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/80 z-10"></div>
                          <img src="https://images.unsplash.com/photo-1596727147705-0043c7576566?auto=format&fit=crop&q=80&w=800" class="absolute inset-0 w-full h-full object-cover -z-0 transition duration-700 group-hover:scale-110 group-hover:rotate-1 opacity-60">
                          
                          <div class="relative z-20 h-full flex flex-col justify-between">
                              <div class="flex justify-between items-start">
                                  <div class="bg-slate-900/80 backdrop-blur p-3 rounded-2xl border border-slate-600 group-hover:border-neon transition shadow-lg">
                                      <svg class="w-8 h-8 text-white group-hover:text-neon transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                      </svg>
                                  </div>
                                  <span class="px-3 py-1 bg-neon text-dark text-[10px] font-bold rounded-full uppercase tracking-wider">All Runners</span>
                              </div>
                              
                              <div>
                                  <h3 class="text-3xl font-bold text-white mb-2 group-hover:text-neon transition">Complete Race Pack</h3>
                                  <p class="text-slate-200 text-sm leading-relaxed max-w-sm">
                                      Jersey lari premium (Dry-fit), Nomor BIB dengan Timing Chip terintegrasi, Tas Serut eksklusif, dan Panduan Lomba Digital.
                                  </p>
                              </div>
                          </div>
                      </div>

                      <div class="bg-card border border-slate-800 rounded-3xl p-6 hover:border-blue-400/50 hover:bg-slate-800/50 transition duration-300 group reveal">
                          <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition border border-slate-700 group-hover:border-blue-400">
                              <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                          </div>
                          <h3 class="text-lg font-bold text-white mb-2">Hydration Points</h3>
                          <p class="text-slate-400 text-xs leading-relaxed">
                              Water station setiap 2.5 KM. Tersedia air mineral dingin & minuman isotonik.
                          </p>
                      </div>

                      <div class="bg-card border border-slate-800 rounded-3xl p-6 hover:border-red-400/50 hover:bg-slate-800/50 transition duration-300 group reveal">
                          <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition border border-slate-700 group-hover:border-red-400">
                              <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                          </div>
                          <h3 class="text-lg font-bold text-white mb-2">Medical Care</h3>
                          <p class="text-slate-400 text-xs leading-relaxed">
                              Ambulans siaga, dokter, dan fisioterapi di garis finish. Keamanan prioritas kami.
                          </p>
                      </div>

                      <div class="md:col-span-2 bg-card border border-slate-800 rounded-3xl p-6 flex flex-col md:flex-row items-center gap-6 hover:border-neon/50 hover:bg-slate-800/50 transition duration-300 group reveal">
                          <div class="w-16 h-16 bg-slate-900 rounded-2xl flex-shrink-0 flex items-center justify-center border border-slate-700 group-hover:border-neon group-hover:text-neon text-white transition">
                              <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                          </div>
                          <div>
                              <h3 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition">Precise Timing System</h3>
                              <p class="text-slate-400 text-sm">
                                  Sistem pencatatan waktu standar internasional. Live Result dan E-Certificate langsung setelah finish.
                              </p>
                          </div>
                      </div>

                      <div class="bg-card border border-slate-800 rounded-3xl p-6 hover:border-purple-400/50 hover:bg-slate-800/50 transition duration-300 group reveal">
                          <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition border border-slate-700 group-hover:border-purple-400">
                              <svg class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                          </div>
                          <h3 class="text-lg font-bold text-white mb-2">Free Photos</h3>
                          <p class="text-slate-400 text-xs leading-relaxed">
                              Fotografer profesional di berbagai titik. Foto resolusi tinggi gratis.
                          </p>
                      </div>

                      <div class="bg-card border border-slate-800 rounded-3xl p-6 hover:border-orange-400/50 hover:bg-slate-800/50 transition duration-300 group reveal">
                          <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition border border-slate-700 group-hover:border-orange-400">
                              <svg class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                          </div>
                          <h3 class="text-lg font-bold text-white mb-2">Safe Bag Drop</h3>
                          <p class="text-slate-400 text-xs leading-relaxed">
                              Area penitipan tas yang aman dan terorganisir dengan sistem nomor BIB.
                          </p>
                      </div>

                  @endif
              </div>
          </div>
      </section>

      @if($showSection('gallery'))
      <section id="gallery" class="py-24 bg-dark relative border-t border-slate-800">
          <div class="max-w-7xl mx-auto px-4">
              <div class="text-center mb-16 reveal">
                   <span class="text-neon font-mono font-bold uppercase tracking-widest text-sm">Event Gallery</span>
                   <h2 class="text-3xl md:text-5xl font-black text-white mt-2">CAPTURE THE <span class="text-slate-600">MOMENT</span></h2>
              </div>

              @if($event->gallery && count($event->gallery) > 0)
                  <div class="relative group reveal">
                       <!-- Scroll Buttons -->
                      <button id="scrollLeft" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-dark/80 hover:bg-neon hover:text-dark text-white p-3 rounded-full border border-slate-700 transition shadow-lg opacity-0 group-hover:opacity-100 hidden md:block">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                      </button>
                      <button id="scrollRight" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-dark/80 hover:bg-neon hover:text-dark text-white p-3 rounded-full border border-slate-700 transition shadow-lg opacity-0 group-hover:opacity-100 hidden md:block">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                      </button>

                      <div id="galleryContainer" class="flex overflow-x-auto gap-6 snap-x snap-mandatory pb-8 hide-scrollbar" style="scroll-behavior: smooth;">
                          @foreach($event->gallery as $image)
                              <div class="snap-center shrink-0 w-[85vw] md:w-[600px] h-[300px] md:h-[400px] relative rounded-3xl overflow-hidden group/item border border-slate-800 shadow-2xl">
                                  <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover transition duration-700 group-hover/item:scale-110 cursor-pointer lightbox-trigger" alt="Gallery">
                                  <div class="absolute inset-0 bg-gradient-to-t from-dark/90 via-transparent to-transparent opacity-0 group-hover/item:opacity-100 transition duration-300 flex items-end p-6 pointer-events-none">
                                      <span class="text-neon font-mono text-sm uppercase tracking-wider">Event Moment</span>
                                  </div>
                              </div>
                          @endforeach
                      </div>
                  </div>
                  
                  <script>
                      document.getElementById('scrollLeft').addEventListener('click', () => {
                          document.getElementById('galleryContainer').scrollBy({ left: -600, behavior: 'smooth' });
                      });
                      document.getElementById('scrollRight').addEventListener('click', () => {
                          document.getElementById('galleryContainer').scrollBy({ left: 600, behavior: 'smooth' });
                      });
                  </script>
                  
                  <style>
                      .hide-scrollbar::-webkit-scrollbar { display: none; }
                      .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                  </style>
              @else
                  <div class="text-center py-20 border border-dashed border-slate-800 rounded-3xl bg-slate-900/50 reveal">
                      <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-600">
                          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                      </div>
                      <p class="text-slate-500 font-medium">Galeri foto akan segera hadir.</p>
                  </div>
              @endif
          </div>
      </section>
      @endif

      

        @if($showSection('jersey') || $showSection('medal'))
        <section id="jersey" class="py-24 bg-gradient-to-b from-dark to-card border-y border-slate-800">
            <div class="max-w-7xl mx-auto px-4">
                @if($showSection('jersey'))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center mb-24 reveal">
                    <div class="order-2 md:order-1 relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-neon via-white to-blue-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        <div class="relative bg-dark border border-slate-700 rounded-2xl overflow-hidden aspect-video shadow-2xl">
                             @if($event->jersey_image)
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" class="w-full h-full object-cover cursor-pointer lightbox-trigger" alt="Jersey">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500 font-mono">JERSEY DESIGN</div>
                            @endif
                        </div>
                    </div>
                    <div class="order-1 md:order-2">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="h-px w-10 bg-neon"></span>
                            <span class="text-neon font-bold uppercase tracking-widest text-xs">Apparel Partner</span>
                        </div>
                        <h3 class="text-4xl font-black text-white mb-6">OFFICIAL JERSEY</h3>
                        <p class="text-slate-400 text-lg leading-relaxed mb-8">
                            Didesain untuk performa. Material ultra-ringan dengan teknologi sirkulasi udara maksimal. Potongan atletis yang membuat Anda tampil cepat sebelum garis start.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-slate-300">
                                <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Quick-Dry Fabric Technology</span>
                            </li>
                            <li class="flex items-center gap-3 text-slate-300">
                                <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Anti-UV Protection</span>
                            </li>
                        </ul>
                    </div>
                </div>
                @endif

                @if($showSection('medal'))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center reveal">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="h-px w-10 bg-neon"></span>
                            <span class="text-neon font-bold uppercase tracking-widest text-xs">Finisher Reward</span>
                        </div>
                        <h3 class="text-4xl font-black text-white mb-6">THE MEDAL</h3>
                        <p class="text-slate-400 text-lg leading-relaxed mb-8">
                            Simbol ketangguhan Anda. Medali Heavy Zinc Alloy dengan ukiran 3D mendetail. Bukan sekadar kenang-kenangan, ini adalah bukti bahwa Anda menaklukkan tantangan.
                        </p>
                        <div class="inline-block border border-slate-700 bg-slate-800/50 px-6 py-3 rounded-lg">
                            <p class="text-xs text-slate-400 uppercase font-bold mb-1">Syarat Mendapatkan:</p>
                            <p class="text-white font-mono text-sm">Finish dibawah Cut Off Time (COT)</p>
                        </div>
                    </div>
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-yellow-500 via-orange-500 to-red-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        <div class="relative bg-dark border border-slate-700 rounded-2xl overflow-hidden aspect-video shadow-2xl">
                             @if($event->medal_image)
                                <img src="{{ asset('storage/' . $event->medal_image) }}" class="w-full h-full object-cover cursor-pointer lightbox-trigger" alt="Medal">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500 font-mono">MEDAL DESIGN</div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
        @endif

        @include('events.partials.prizes-section', ['categories' => $categories])

        <section id="registrasi" class="py-24 relative overflow-hidden">
             <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-neon/5 rounded-full blur-[100px] pointer-events-none -z-10"></div>

            <div class="max-w-5xl mx-auto px-4">
                <div class="text-center mb-12 reveal">
                    <h2 class="text-3xl md:text-5xl font-black text-white mb-4">SECURE YOUR <span class="text-neon">SLOT</span></h2>
                    <p class="text-slate-400">Bergabunglah dengan ribuan pelari lainnya.</p>
                </div>

                @if(!$isRegOpen)
                    <div class="bg-card border border-red-500/30 rounded-2xl p-8 text-center max-w-2xl mx-auto reveal">
                        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h3 class="text-2xl font-bold text-white mb-2">REGISTRASI DITUTUP</h3>
                        <p class="text-slate-400">
                             @if($event->registration_open_at && $now < $event->registration_open_at)
                                Pendaftaran akan dibuka pada: <span class="text-white font-mono">{{ $event->registration_open_at->format('d F Y, H:i') }} WIB</span>
                            @else
                                Pendaftaran telah ditutup. Sampai jumpa di event berikutnya.
                            @endif
                        </p>
                    </div>
                @else
                    <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="space-y-8 reveal">
                        @csrf
                        
                        @if(session('success') || session('snap_token'))
                            <div class="bg-green-500/10 border border-green-500/50 text-green-400 p-4 rounded-xl flex items-center gap-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <div><strong>Berhasil!</strong> Data tersimpan. Lanjutkan pembayaran.</div>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl">
                                <ul class="list-disc pl-5 text-sm">
                                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 space-y-8">
                                <div class="bg-card border border-slate-800 rounded-3xl p-6 md:p-8">
                                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-full bg-slate-700 text-neon flex items-center justify-center font-mono text-sm">01</span>
                                        Data Penanggung Jawab
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nama Lengkap</label>
                                            <input type="text" name="pic_name" value="{{ old('pic_name') }}" required class="w-full bg-input border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-bold text-slate-500 uppercase ml-1">Email</label>
                                            <input type="email" name="pic_email" value="{{ old('pic_email') }}" required class="w-full bg-input border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition">
                                        </div>
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-xs font-bold text-slate-500 uppercase ml-1">WhatsApp / No. HP</label>
                                            <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required class="w-full bg-input border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition">
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-card border border-slate-800 rounded-3xl p-6 md:p-8">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-lg font-bold text-white flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-full bg-slate-700 text-neon flex items-center justify-center font-mono text-sm">02</span>
                                            Data Peserta
                                        </h3>
                                        <button type="button" id="addParticipant" class="text-xs font-bold text-neon border border-neon px-4 py-2 rounded-lg hover:bg-neon hover:text-dark transition">
                                            + TAMBAH
                                        </button>
                                    </div>

                                    <div id="participantsWrapper" class="space-y-4">
                                        <div class="participant-item bg-slate-900/50 border border-slate-700 hover:border-slate-500 p-6 rounded-2xl transition relative group" data-index="0">
                                            <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-800">
                                                <strong class="text-neon font-mono text-sm uppercase">Peserta #1</strong>
                                                <button type="button" class="remove-participant hidden text-red-500 hover:text-red-400 text-xs font-bold uppercase">Hapus</button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Nama</label>
                                                    <input type="text" name="participants[0][name]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Email</label>
                                                    <input type="email" name="participants[0][email]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Gender</label>
                                                    <select name="participants[0][gender]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="male">Laki-laki</option>
                                                        <option value="female">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">No. HP</label>
                                                    <input type="text" name="participants[0][phone]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">No. ID (KTP)</label>
                                                    <input type="text" name="participants[0][id_card]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Nama Kontak Darurat</label>
                                                    <input type="text" name="participants[0][emergency_contact_name]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">No. Kontak Darurat</label>
                                                    <input type="text" name="participants[0][emergency_contact_number]" required class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                                <div class="md:col-span-2 space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Kategori</label>
                                                    <select name="participants[0][category_id]" class="category-select w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none" data-index="0" required>
                                                        <option value="">-- Pilih Kategori --</option>
                                                        @foreach($categories as $cat)
                                                            @php
                                                                $now = now();
                                                                $earlyValid = $cat->price_early > 0 && 
                                                                              (!$cat->early_bird_end_at || $now->lte($cat->early_bird_end_at)) &&
                                                                              (!$cat->early_bird_quota || ($cat->early_bird_sold_count ?? 0) < $cat->early_bird_quota);
                                                                $earlyPrice = $earlyValid ? $cat->price_early : 0;
                                                            @endphp
                                                            <option value="{{ $cat->id }}" 
                                                                data-price-early="{{ $earlyPrice }}"
                                                                data-price-regular="{{ $cat->price_regular }}"
                                                                data-price-late="{{ $cat->price_late }}"
                                                                data-reg-start="{{ $cat->reg_start_at }}"
                                                                data-reg-end="{{ $cat->reg_end_at }}"
                                                                data-quota="{{ $cat->quota }}">
                                                                {{ $cat->name }} ({{ $cat->distance_km ?? '?' }}K)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="category-info h-4 text-[10px] text-neon font-mono mt-1" data-index="0"></div>
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Jersey Size</label>
                                                    <select name="participants[0][jersey_size]" class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach(($event->jersey_sizes ?? ['S','M','L','XL']) as $size)
                                                            <option value="{{ $size }}">{{ $size }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-[10px] text-slate-500 uppercase font-bold">Target Waktu (Optional)</label>
                                                    <input type="text" name="participants[0][target_time]" placeholder="HH:MM:SS (Contoh: 00:55:00)" class="w-full bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-1">
                                <div class="sticky top-24 space-y-6">
                                    <div class="bg-card border border-slate-800 rounded-3xl p-6">
                                        <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Kode Promo</label>
                                        <div class="flex gap-2">
                                            <input type="text" id="coupon_code" value="{{ old('coupon_code') }}" placeholder="KODE..." class="flex-1 bg-input border border-slate-700 rounded-lg px-3 py-2 text-sm text-white uppercase focus:border-neon outline-none">
                                            <button type="button" id="applyCoupon" class="bg-slate-700 text-white px-4 rounded-lg text-xs font-bold hover:bg-white hover:text-dark transition">APPLY</button>
                                        </div>
                                        <div id="couponMessage" class="mt-2 text-xs"></div>
                                        <input type="hidden" id="coupon_code_hidden" name="coupon_code">
                                    </div>

                                    <div class="bg-gradient-to-b from-slate-800 to-slate-900 border border-slate-700 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
                                        <div class="absolute top-0 right-0 p-4 opacity-10">
                                            <svg class="w-24 h-24 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        
                                        <h3 class="text-lg font-bold text-white mb-6">RINGKASAN</h3>
                                        <div class="space-y-3 mb-6 border-b border-slate-700 pb-6">
                                        <div class="flex justify-between text-sm text-slate-400">
                                            <span>Subtotal</span>
                                            <span id="subtotal" class="font-mono text-white">Rp 0</span>
                                        </div>
                                        <div id="feeRow" class="flex justify-between text-sm text-slate-400 {{ $event->platform_fee > 0 ? '' : 'hidden' }}">
                                            <span>Biaya Admin</span>
                                            <span id="feeAmount" class="font-mono text-white">Rp 0</span>
                                        </div>
                                        <div id="discountRow" class="flex justify-between text-sm text-green-400 hidden">
                                            <span>Diskon</span>
                                            <span id="discountAmount" class="font-mono">-Rp 0</span>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <h4 class="text-sm font-bold text-slate-300 mb-3">METODE PEMBAYARAN</h4>
                                        <div class="space-y-3">
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

                                                // Default selection logic
                                                $defaultMidtrans = $showMidtrans ? 'checked' : '';
                                                $defaultMoota = (!$showMidtrans && $showMoota) ? 'checked' : '';
                                            @endphp

                                            @if($showMidtrans)
                                            <label class="flex items-center justify-between p-4 bg-slate-800/50 border border-slate-700 rounded-xl cursor-pointer hover:border-slate-500 transition-colors group">
                                                <div class="flex items-center gap-3">
                                                    <input type="radio" name="payment_method" value="midtrans" {{ $defaultMidtrans }} class="w-4 h-4 text-neon focus:ring-neon bg-slate-700 border-slate-600">
                                                    <div class="flex flex-col">
                                                        <span class="text-white text-sm font-bold">QRIS / E-Wallet / Virtual Account</span>
                                                        <span class="text-xs text-slate-400">Verifikasi Otomatis (Midtrans)</span>
                                                    </div>
                                                </div>
                                            </label>
                                            @endif
                                            
                                            @if($showMoota)
                                            <label class="flex items-center justify-between p-4 bg-slate-800/50 border border-slate-700 rounded-xl cursor-pointer hover:border-slate-500 transition-colors group">
                                                <div class="flex items-center gap-3">
                                                    <input type="radio" name="payment_method" value="moota" {{ $defaultMoota }} class="w-4 h-4 text-neon focus:ring-neon bg-slate-700 border-slate-600">
                                                    <div class="flex flex-col">
                                                        <span class="text-white text-sm font-bold">Transfer Bank (Moota)</span>
                                                        <span class="text-xs text-slate-400">Verifikasi Otomatis</span>
                                                    </div>
                                                </div>
                                            </label>
                                            @endif
                                        </div>
                                    </div>

                                    @if($event->terms_and_conditions)
                                    <div class="mb-6">
                                        <label class="flex items-start gap-3 cursor-pointer group">
                                            <input type="checkbox" name="terms_agreed" required class="mt-1 w-4 h-4 rounded border-slate-600 bg-slate-800 text-neon focus:ring-neon cursor-pointer">
                                            <span class="text-xs text-slate-400 group-hover:text-slate-300 transition-colors">
                                                Saya menyetujui <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="text-neon hover:underline font-bold">Syarat & Ketentuan</button> yang berlaku untuk event ini.
                                            </span>
                                        </label>
                                    </div>
                                    @endif

                                    @if(env('RECAPTCHA_SITE_KEY'))
                                        <div class="mb-6 flex justify-center">
                                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                                        </div>
                                    @endif

                                    <div class="flex justify-between items-end mb-6">
                                        <span class="text-slate-300 text-sm font-bold">TOTAL BAYAR</span>
                                        <span id="totalAmount" class="text-3xl font-mono font-bold text-white tracking-tight">Rp 0</span>
                                    </div>
                                    <button type="submit" id="submitBtn" class="w-full py-4 bg-neon text-dark font-black text-lg rounded-xl hover:bg-white hover:scale-[1.02] transition shadow-lg">
                                        BAYAR SEKARANG
                                    </button>
                                    <div class="text-center mt-4 flex items-center justify-center gap-2 opacity-50">
                                        <span class="text-[10px] text-slate-400">Powered by Midtrans</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <!-- Terms Modal -->
    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl relative">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">Syarat & Ketentuan</h3>
                    <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto prose prose-invert prose-sm max-w-none">
                    {!! $event->terms_and_conditions !!}
                </div>
                <div class="p-6 border-t border-slate-800 bg-slate-900/50 rounded-b-2xl">
                    <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

        <section class="py-24 bg-card relative overflow-hidden">
          <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(#ccff00 1px, transparent 1px); background-size: 30px 30px;"></div>

          <div class="max-w-7xl mx-auto px-4 relative z-10">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                  
                  @if($showSection('rpc'))
                  <div class="bg-dark border border-slate-700 rounded-3xl p-8 reveal">
                      <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-blue-400 mb-6 border border-slate-600">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                      </div>
                      <h3 class="text-xl font-bold text-white mb-4">RACE PACK COLLECTION</h3>
                      <div class="space-y-4 text-sm text-slate-300">
                          <div class="flex gap-3">
                              <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                              <div>
                                  <p class="font-bold text-white">Jadwal Pengambilan:</p>
                                  <p>H-3 s.d H-1 Sebelum Race Day</p>
                                  <p class="text-xs text-slate-500 mt-1">(Detail jam diinformasikan via email)</p>
                              </div>
                          </div>
                          <div class="flex gap-3">
                              <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                              <div>
                                  <p class="font-bold text-white">Lokasi RPC:</p>
                                  <p>{{ $event->rpc_location_name ?? $event->location_name . ' (Lobby Area)' }}</p>
                                  @if($event->rpc_location_address)
                                  <p class="text-xs text-slate-500 mt-1">{{ $event->rpc_location_address }}</p>
                                  @endif
                              </div>
                          </div>
                          <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700 mt-4">
                              <p class="text-xs text-slate-400">
                                  <strong class="text-neon block mb-1">Syarat Pengambilan:</strong>
                                  Wajib membawa E-Voucher (dari email) dan kartu identitas asli (KTP/SIM).
                              </p>
                          </div>
                      </div>
                  </div>
                  @endif

                  @if($showSection('venue'))
                  <div class="bg-dark border border-slate-700 rounded-3xl p-8 reveal delay-100">
                      <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-orange-400 mb-6 border border-slate-600">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      </div>
                      <h3 class="text-xl font-bold text-white mb-4">VENUE & PARKING INFO</h3>
                      <div class="space-y-4 text-sm text-slate-300">
                          <div class="flex gap-3">
                              <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                              <div>
                                  <p class="font-bold text-white">Akses Transportasi:</p>
                                  <p>Disarankan menggunakan transportasi umum (MRT/Transjakarta) untuk menghindari macet.</p>
                              </div>
                          </div>
                          <div class="flex gap-3">
                              <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                              <div>
                                  <p class="font-bold text-white">Area Parkir:</p>
                                  @if(isset($pa['venue']['parking_info']) && !empty($pa['venue']['parking_info']))
                                      <p>{{ $pa['venue']['parking_info'] }}</p>
                                  @else
                                      <p>Parkir tersedia terbatas di area venue.</p>
                                      <p class="text-xs text-red-400 mt-1">*Jangan parkir di bahu jalan raya.</p>
                                  @endif
                              </div>
                          </div>
                      </div>
                  </div>
                  @endif

                  @if($showSection('what_to_bring'))
                  <div class="bg-dark border border-slate-700 rounded-3xl p-8 reveal delay-200">
                      <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-neon mb-6 border border-slate-600">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                      </div>
                      <h3 class="text-xl font-bold text-white mb-4">WHAT TO BRING</h3>
                      <ul class="space-y-3">
                          @if(isset($pa['what_to_bring']['items']) && !empty($pa['what_to_bring']['items']))
                              @foreach($pa['what_to_bring']['items'] as $item)
                                  @if(!empty($item))
                                  <li class="flex items-center gap-3 text-sm text-slate-300">
                                      <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                      {{ $item }}
                                  </li>
                                  @endif
                              @endforeach
                          @else
                              <li class="flex items-center gap-3 text-sm text-slate-300">
                                  <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                  BIB Number (Wajib dipakai)
                              </li>
                              <li class="flex items-center gap-3 text-sm text-slate-300">
                                  <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                  Pakaian Lari Nyaman & Sepatu Lari
                              </li>
                              <li class="flex items-center gap-3 text-sm text-slate-300">
                                  <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                  Identitas Diri (KTP/SIM)
                              </li>
                              <li class="flex items-center gap-3 text-sm text-slate-300">
                                  <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                  Uang Tunai secukupnya (untuk F&B area)
                              </li>
                              <li class="flex items-center gap-3 text-sm text-slate-300">
                                  <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                  Obat-obatan Pribadi (Jika perlu)
                              </li>
                          @endif
                      </ul>
                  </div>
                  @endif

              </div>
          </div>
      </section>

        @if($showSection('faq'))
        <section id="faq" class="py-24 bg-card border-t border-slate-800">
            <div class="max-w-3xl mx-auto px-4">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-3xl font-black text-white mb-4">FREQUENTLY ASKED <span class="text-neon">QUESTIONS</span></h2>
                </div>

                <div class="space-y-4 reveal">
                    @if(isset($pa['faq']['items']) && count($pa['faq']['items']) > 0)
                        @foreach($pa['faq']['items'] as $faq)
                            @if(!empty($faq['question']) && !empty($faq['answer']))
                            <div class="bg-dark border border-slate-700 rounded-2xl overflow-hidden">
                                <button class="accordion-btn w-full px-6 py-5 text-left flex justify-between items-center bg-dark hover:bg-slate-800/50 transition" aria-expanded="false">
                                    <span class="font-bold text-slate-200">{{ $faq['question'] }}</span>
                                    <svg class="w-5 h-5 text-slate-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div class="accordion-content bg-slate-900/50">
                                    <div class="px-6 pb-6 text-slate-400 text-sm leading-relaxed border-t border-slate-800/50 pt-4">
                                        {!! nl2br(e($faq['answer'])) !!}
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <div class="bg-dark border border-slate-700 rounded-2xl overflow-hidden">
                            <button class="accordion-btn w-full px-6 py-5 text-left flex justify-between items-center bg-dark hover:bg-slate-800/50 transition" aria-expanded="false">
                                <span class="font-bold text-slate-200">Bagaimana cara mendaftar event ini?</span>
                                <svg class="w-5 h-5 text-slate-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="accordion-content bg-slate-900/50">
                                <div class="px-6 pb-6 text-slate-400 text-sm leading-relaxed border-t border-slate-800/50 pt-4">
                                    Pilih tombol "Daftar Sekarang", isi formulir data diri (PIC dan Peserta), lalu lakukan pembayaran melalui metode yang tersedia (QRIS, Virtual Account, dll). Konfirmasi akan dikirim ke email Anda.
                                </div>
                            </div>
                        </div>

                        <div class="bg-dark border border-slate-700 rounded-2xl overflow-hidden">
                            <button class="accordion-btn w-full px-6 py-5 text-left flex justify-between items-center bg-dark hover:bg-slate-800/50 transition" aria-expanded="false">
                                <span class="font-bold text-slate-200">Kapan pengambilan Race Pack?</span>
                                <svg class="w-5 h-5 text-slate-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="accordion-content bg-slate-900/50">
                                <div class="px-6 pb-6 text-slate-400 text-sm leading-relaxed border-t border-slate-800/50 pt-4">
                                    Pengambilan Race Pack (RPC) akan diinformasikan melalui email dan Instagram resmi H-7 acara. Biasanya dilakukan 2-3 hari sebelum hari lomba di lokasi yang ditentukan.
                                </div>
                            </div>
                        </div>

                        <div class="bg-dark border border-slate-700 rounded-2xl overflow-hidden">
                            <button class="accordion-btn w-full px-6 py-5 text-left flex justify-between items-center bg-dark hover:bg-slate-800/50 transition" aria-expanded="false">
                                <span class="font-bold text-slate-200">Apakah tiket bisa di-refund?</span>
                                <svg class="w-5 h-5 text-slate-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="accordion-content bg-slate-900/50">
                                <div class="px-6 pb-6 text-slate-400 text-sm leading-relaxed border-t border-slate-800/50 pt-4">
                                    Sesuai peraturan event, tiket yang sudah dibeli tidak dapat dikembalikan (non-refundable). Namun, nama peserta mungkin bisa diubah dalam periode tertentu dengan biaya administrasi.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        <section id="results" class="py-24 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-t border-slate-700 relative">
            <div class="max-w-7xl mx-auto px-4" id="resultsApp">
                
                <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-10 reveal">
                    <div>
                        <span class="text-neon font-mono font-bold uppercase tracking-widest text-sm">Leaderboard</span>
                        <h2 class="text-3xl md:text-4xl font-black text-white mt-2">RACE <span class="text-slate-300">RESULTS</span></h2>
                    </div>
                    
                    <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
                        <div class="relative group w-full md:w-auto">
                            <input type="text" v-model="search" placeholder="Cari Nama atau BIB..." 
                                class="w-full md:w-64 bg-slate-700 border-2 border-slate-500 rounded-xl py-2.5 pl-10 pr-4 text-sm text-white focus:border-neon focus:ring-2 focus:ring-neon/50 outline-none transition placeholder-slate-400">
                            <svg class="w-4 h-4 text-slate-300 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        
                        <select v-model="filterCategory" class="w-full md:w-auto bg-slate-700 border-2 border-slate-500 rounded-xl py-2.5 px-4 text-sm text-white focus:border-neon focus:ring-2 focus:ring-neon/50 outline-none cursor-pointer">
                            <option value="All">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->code ?? $cat->name }}">{{ $cat->name }} ({{ $cat->code ?? 'N/A' }})</option>
                            @endforeach
                        </select>

                        <select v-model="filterGender" class="w-full md:w-auto bg-slate-700 border-2 border-slate-500 rounded-xl py-2.5 px-4 text-sm text-white focus:border-neon focus:ring-2 focus:ring-neon/50 outline-none cursor-pointer">
                            <option value="All">Semua Gender</option>
                            <option value="M">Laki-laki (M)</option>
                            <option value="F">Perempuan (F)</option>
                        </select>
                    </div>
                </div>

                <div class="bg-slate-800 border-2 border-slate-500 rounded-3xl overflow-hidden shadow-2xl reveal delay-100 min-h-[400px]">
                    
                    <div v-if="loading" class="p-20 text-center flex flex-col items-center justify-center h-[400px]">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-neon mb-4"></div>
                        <p class="text-white animate-pulse font-semibold">Mengambil data hasil lomba...</p>
                    </div>

                    <div v-else-if="error" class="p-20 text-center flex flex-col items-center justify-center h-[400px] bg-slate-700/50 rounded-lg border-2 border-red-500/50">
                        <svg class="w-16 h-16 text-red-400 mb-4 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <p class="text-red-200 mb-2 font-semibold">@{{ error }}</p>
                        <button @click="fetchResults" class="mt-4 px-4 py-2 bg-slate-600 hover:bg-slate-500 rounded-lg text-white transition text-sm font-semibold">Coba Lagi</button>
                    </div>

                    <div v-else>
                        <!-- Debug warning if data exists but filtered is empty -->
                        <div v-if="results.length > 0 && filteredResults.length === 0" class="p-4 bg-yellow-500/20 border border-yellow-500/50 rounded-lg mb-4 mx-4 mt-4">
                            <p class="text-yellow-300 text-sm font-semibold mb-1">⚠️ Data ada tapi tidak tampil karena filter</p>
                            <p class="text-yellow-200 text-xs">
                                Total data: @{{ results.length }} | 
                                Filter: Category="@{{ filterCategory }}", Gender="@{{ filterGender }}", Search="@{{ search }}"
                            </p>
                            <p class="text-yellow-200 text-xs mt-1">
                                Categories available: @{{ [...new Set(results.map(r => r.category))].join(', ') }}
                            </p>
                            <button @click="resetFilters" class="mt-2 px-3 py-1 bg-yellow-500/30 hover:bg-yellow-500/50 text-white rounded text-xs font-semibold transition">Reset Filter</button>
                        </div>
                        
                        <!-- Desktop Table -->
                        <div v-if="filteredResults && filteredResults.length > 0" class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-700 border-b-2 border-slate-500 text-xs uppercase tracking-wider">
                                        <th class="p-5 font-bold text-center w-24 text-white">Rank</th>
                                        <th class="p-5 font-bold text-white">Runner Name</th>
                                        <th class="p-5 font-bold text-white">BIB</th>
                                        <th class="p-5 font-bold text-white">Category</th>
                                        <th class="p-5 font-bold text-right text-white">Gun Time</th>
                                        <th class="p-5 font-bold text-right text-neon">Chip Time</th>
                                        <th class="p-5 font-bold text-center text-white">Pace</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-600 text-sm">
                                    <tr v-for="(runner, index) in filteredResults" :key="`${runner.bib}-${index}`" class="hover:bg-slate-700/60 transition duration-150 group border-b border-slate-600/50 bg-slate-800/30">
                                        <td class="p-5 text-center font-mono font-bold text-white">
                                            <div v-if="runner.rank === 1" class="mx-auto flex items-center justify-center w-10 h-10 rounded-full bg-yellow-500/30 text-yellow-300 border-2 border-yellow-400 shadow-lg shadow-yellow-500/30 font-bold">1</div>
                                            <div v-else-if="runner.rank === 2" class="mx-auto flex items-center justify-center w-10 h-10 rounded-full bg-slate-300/30 text-white border-2 border-slate-300 shadow-lg shadow-slate-300/30 font-bold">2</div>
                                            <div v-else-if="runner.rank === 3" class="mx-auto flex items-center justify-center w-10 h-10 rounded-full bg-orange-500/30 text-orange-200 border-2 border-orange-400 shadow-lg shadow-orange-500/30 font-bold">3</div>
                                            <span v-else class="text-white font-semibold">#@{{ runner.rank }}</span>
                                        </td>
                                        
                                        <td class="p-5">
                                            <div class="font-bold text-white group-hover:text-neon transition cursor-pointer flex items-center gap-2" @click="openCertificate(runner)">
                                                @{{ runner.name }} 
                                                <svg class="w-4 h-4 text-yellow-500 opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                                            </div>
                                            <div class="text-[10px] text-slate-300 mt-0.5">@{{ runner.gender === 'M' ? 'Male' : 'Female' }} • @{{ runner.nationality }}</div>
                                        </td>
                                        
                                        <td class="p-5 font-mono text-white font-semibold">@{{ runner.bib }}</td>
                                        
                                        <td class="p-5">
                                            <span class="px-3 py-1.5 rounded-lg bg-slate-600 border border-slate-500 text-[10px] font-bold text-white uppercase tracking-wide">
                                                @{{ runner.category }}
                                            </span>
                                        </td>
                                        
                                        <td class="p-5 text-right font-mono text-white">@{{ runner.gunTime }}</td>
                                        <td class="p-5 text-right font-mono font-bold text-neon group-hover:text-white transition">@{{ runner.chipTime }}</td>
                                        <td class="p-5 text-center font-mono text-white">@{{ runner.pace }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile View -->
                        <div v-if="filteredResults && filteredResults.length > 0" class="md:hidden divide-y divide-slate-600">
                            <div v-for="(runner, index) in filteredResults" :key="`mobile-${runner.bib}-${index}`" class="p-5 hover:bg-slate-700/50 transition border-b border-slate-600 bg-slate-800/30">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="font-mono font-bold text-lg w-10 text-center">
                                            <div v-if="runner.rank === 1" class="w-8 h-8 rounded-full bg-yellow-500/30 text-yellow-300 border-2 border-yellow-400 flex items-center justify-center mx-auto font-bold">1</div>
                                            <div v-else-if="runner.rank === 2" class="w-8 h-8 rounded-full bg-slate-300/30 text-white border-2 border-slate-300 flex items-center justify-center mx-auto font-bold">2</div>
                                            <div v-else-if="runner.rank === 3" class="w-8 h-8 rounded-full bg-orange-500/30 text-orange-200 border-2 border-orange-400 flex items-center justify-center mx-auto font-bold">3</div>
                                            <span v-else class="text-white font-semibold">#@{{ runner.rank }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-white text-base cursor-pointer hover:text-neon flex items-center gap-2" @click="openCertificate(runner)">
                                                @{{ runner.name }}
                                                <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                                            </h4>
                                            <p class="text-xs text-slate-300 mt-0.5">@{{ runner.category }} • BIB @{{ runner.bib }} • @{{ runner.gender }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-2 bg-slate-700/60 p-3 rounded-xl border border-slate-500">
                                    <div class="text-center border-r border-slate-500">
                                        <p class="text-[9px] text-slate-300 uppercase font-bold mb-1">Gun Time</p>
                                        <p class="font-mono text-xs text-white">@{{ runner.gunTime }}</p>
                                    </div>
                                    <div class="text-center border-r border-slate-500">
                                        <p class="text-[9px] text-slate-300 uppercase font-bold mb-1">Chip Time</p>
                                        <p class="font-mono text-sm font-bold text-neon">@{{ runner.chipTime }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[9px] text-slate-300 uppercase font-bold mb-1">Pace</p>
                                        <p class="font-mono text-xs text-white">@{{ runner.pace }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty states -->
                        <div v-if="!loading && !error && filteredResults.length === 0 && results.length === 0" class="p-16 text-center">
                            <div class="bg-slate-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-slate-500">
                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-white font-bold mb-1">Belum Ada Race Results</h3>
                            <p class="text-slate-300 text-sm mb-4">Admin dapat mengupload data race results melalui halaman admin.</p>
                        </div>
                        <div v-else-if="!loading && !error && filteredResults.length === 0 && results.length > 0" class="p-16 text-center">
                            <div class="bg-slate-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-slate-500">
                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-white font-bold mb-1">Data Tidak Ditemukan</h3>
                            <p class="text-slate-300 text-sm mb-4">Coba sesuaikan kata kunci atau filter kategori Anda.</p>
                            <button @click="resetFilters" class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg text-sm font-semibold transition">Reset Filter</button>
                        </div>

                        <!-- Footer with results count -->
                        <div v-if="filteredResults.length > 0" class="p-4 bg-slate-700 border-t-2 border-slate-500 flex justify-between items-center text-xs">
                            <span class="text-white font-semibold">Menampilkan @{{ filteredResults.length }} hasil</span>
                            <div class="flex gap-2 opacity-50 cursor-not-allowed">
                                <span class="px-3 py-1.5 bg-slate-600 rounded-lg text-white">Prev</span>
                                <span class="px-3 py-1.5 bg-neon text-dark font-bold rounded-lg">1</span>
                                <span class="px-3 py-1.5 bg-slate-600 rounded-lg text-white">Next</span>
                            </div>
                        </div>
                        
                        <!-- Certificate Modal -->
                        <div v-if="showCertificate" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.8);">
                            <div class="bg-white text-dark rounded-lg shadow-2xl max-w-md w-full overflow-hidden relative animate-fade-in" @click.stop>
                                <button @click="showCertificate = false" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 transition">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                
                                <div class="p-8 text-center bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
                                    @if($event->logo_image)
                                        <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-16 mx-auto mb-6" alt="Event Logo">
                                    @else
                                        <h2 class="text-2xl font-black uppercase tracking-tighter mb-6 text-neon-600">{{ $event->name }}</h2>
                                    @endif
                                    
                                    <h3 class="text-xl font-bold text-gray-400 uppercase tracking-widest mb-2">CONGRATULATIONS</h3>
                                    
                                    <h1 class="text-3xl font-black text-slate-800 mb-4 break-words leading-tight">@{{ selectedRunner.name }}</h1>
                                    
                                    <p class="text-gray-500 text-sm mb-1">Telah menyelesaikan race kategori</p>
                                    <p class="text-xl font-bold text-neon-600 mb-6">@{{ selectedRunner.category }}</p>
                                    
                                    <div class="inline-block border-2 border-slate-800 rounded-lg px-6 py-3 bg-slate-50">
                                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Catatan Waktu</p>
                                        <p class="text-3xl font-mono font-bold text-slate-900">@{{ selectedRunner.chipTime }}</p>
                                    </div>
                                    
                                    <div class="mt-8 pt-6 border-t border-gray-200">
                                        <p class="text-xs text-gray-400 font-mono">Official E-Certificate • {{ $event->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <section id="lokasi" class="py-24 bg-dark border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-card border border-slate-700 rounded-3xl p-1 overflow-hidden relative reveal">
                    <div class="absolute top-6 left-6 z-10 bg-dark/90 backdrop-blur border border-slate-600 p-4 rounded-xl shadow-2xl">
                        <p class="text-xs text-slate-400 uppercase font-bold">Venue</p>
                        <p class="text-white font-bold text-lg">{{ $event->location_name }}</p>
                        <a href="https://maps.google.com/?q={{ $event->location_name }}" target="_blank" class="text-neon text-xs font-bold hover:underline mt-1 block">Buka di Google Maps &rarr;</a>
                    </div>
                    
                    @if($event->map_embed_url)
                        <iframe src="{{ $event->map_embed_url }}" width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy" class="rounded-2xl grayscale hover:grayscale-0 transition duration-700"></iframe>
                    @else
                        <div class="w-full h-[500px] bg-slate-800 flex items-center justify-center text-slate-600 rounded-2xl">
                            Peta tidak tersedia
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Sponsor Carousel -->
        @include('events.partials.sponsor-carousel', [
            'gradientFrom' => 'from-dark',
            'titleColor' => 'text-slate-400',
            'containerClass' => 'bg-slate-800/50 border border-slate-700/50',
            'sectionClass' => 'py-12 bg-dark border-t border-slate-800'
        ])

    </main>

    <footer class="bg-slate-950 border-t border-slate-900 py-12 text-sm text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <div>&copy; {{ date('Y') }} <strong class="text-white">{{ $event->name }}</strong>. Official Event.</div>
            <div class="flex gap-6">
                <a href="#" class="hover:text-neon transition">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-neon transition">Kebijakan Privasi</a>
                <a href="#" class="hover:text-neon transition">Kontak Panitia</a>
            </div>
        </div>
    </footer>

    <script>
        // --- 1. Reveal Animation on Scroll ---
        const observerOptions = { root: null, rootMargin: '0px', threshold: 0.1 };
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // --- 2. FAQ Accordion Logic ---
        document.querySelectorAll('.accordion-btn').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                const expanded = button.getAttribute('aria-expanded') === 'true';
                
                // Close all others
                document.querySelectorAll('.accordion-btn').forEach(b => {
                    b.setAttribute('aria-expanded', 'false');
                    b.nextElementSibling.style.maxHeight = null;
                });

                if (!expanded) {
                    button.setAttribute('aria-expanded', 'true');
                    content.style.maxHeight = content.scrollHeight + "px";
                }
            });
        });

        // --- 3. Navbar Scroll Effect ---
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if(window.scrollY > 50) {
                nav.classList.add('shadow-lg', 'bg-dark/95');
                nav.classList.remove('bg-dark/80');
            } else {
                nav.classList.remove('shadow-lg', 'bg-dark/95');
                nav.classList.add('bg-dark/80');
            }
        });

        // --- 4. Mobile Menu ---
        document.getElementById('navToggle').addEventListener('click', () => {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        // --- 5. Registration Form Logic (Preserved functionality with Tailwind classes) ---
        (function() {
            let participantIndex = 1;
            const participantsWrapper = document.getElementById('participantsWrapper');
            const addParticipantBtn = document.getElementById('addParticipant');
            // Clone template from the first item
            const template = participantsWrapper ? participantsWrapper.querySelector('.participant-item').cloneNode(true) : null;
            const eventId = {{ $event->id }};
            const eventSlug = '{{ $event->slug }}';
            const platformFee = {{ $event->platform_fee ?? 0 }};
            const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};
            let appliedCoupon = null;
            let discountAmount = 0;
            const formatCurrency = (num) => new Intl.NumberFormat('id-ID').format(Math.round(num));

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

            // Add Participant
            if(addParticipantBtn && template) {
                addParticipantBtn.addEventListener('click', () => {
                    resetCoupon();
                    const newItem = template.cloneNode(true);
                    const idx = participantIndex++;
                    newItem.setAttribute('data-index', idx);
                    newItem.querySelector('strong').textContent = `Peserta #${idx + 1}`;
                    newItem.querySelectorAll('input, select').forEach(el => {
                        const name = el.getAttribute('name');
                        if(name) {
                            el.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                            el.value = '';
                        }
                    });
                    const catInfo = newItem.querySelector('.category-info');
                    if(catInfo) { catInfo.textContent = ''; catInfo.setAttribute('data-index', idx); }
                    const catSelect = newItem.querySelector('.category-select');
                    catSelect.setAttribute('data-index', idx);
                    catSelect.addEventListener('change', handleCategoryChange);
                    newItem.querySelector('.remove-participant').classList.remove('hidden');
                    
                    // Show/Hide Copy Prev Button
                    const copyPrevBtn = newItem.querySelector('.copy-prev-btn');
                    if (idx > 0) {
                        copyPrevBtn.classList.remove('hidden');
                    } else {
                        copyPrevBtn.classList.add('hidden');
                    }

                    participantsWrapper.appendChild(newItem);
                    updatePriceSummary();
                });
            }

            // Remove Participant
            if(participantsWrapper) {
                participantsWrapper.addEventListener('click', (e) => {
                    if(e.target.classList.contains('remove-participant')) {
                        if(participantsWrapper.querySelectorAll('.participant-item').length > 1) {
                            e.target.closest('.participant-item').remove();
                            resetCoupon();
                            updatePriceSummary();
                        }
                    }
                });
            }

            // Category Change
            function handleCategoryChange(e) {
                resetCoupon();
                const select = e.target;
                const idx = select.getAttribute('data-index');
                const option = select.options[select.selectedIndex];
                const infoEl = document.querySelector(`.category-info[data-index="${idx}"]`);
                
                if(!option.value) {
                    if(infoEl) infoEl.textContent = '';
                    select.setAttribute('data-active-price', 0);
                    updatePriceSummary();
                    return;
                }

                const pEarly = parseFloat(option.dataset.priceEarly || 0);
                const pReg = parseFloat(option.dataset.priceRegular || 0);
                const pLate = parseFloat(option.dataset.priceLate || 0);

                let currentPrice = pReg;
                if (pEarly > 0) {
                    currentPrice = pEarly;
                } else if (pLate > 0) {
                    currentPrice = pLate;
                }

                if (infoEl) {
                    if (currentPrice !== pReg && pReg > 0) {
                        infoEl.innerHTML = `Harga: <span class="line-through opacity-70">Rp ${formatCurrency(pReg)}</span> <span class="font-bold">Rp ${formatCurrency(currentPrice)}</span> | Sisa Kuota: ${option.dataset.quota}`;
                    } else {
                        infoEl.textContent = `Harga: Rp ${formatCurrency(currentPrice)} | Sisa Kuota: ${option.dataset.quota}`;
                    }
                }
                select.setAttribute('data-active-price', currentPrice);
                updatePriceSummary();
            }

            // Attach initial listeners
            document.querySelectorAll('.category-select').forEach(el => el.addEventListener('change', handleCategoryChange));

            // Update Total
            function updatePriceSummary() {
                const categoryCounts = new Map();
                const categoryPrices = new Map();
                let count = 0;
                document.querySelectorAll('.category-select').forEach(el => {
                    if (!el.value) return;
                    count++;
                    const categoryId = String(el.value);
                    const price = parseFloat(el.getAttribute('data-active-price') || 0);
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
                document.getElementById('subtotal').textContent = `Rp ${formatCurrency(subtotal)}`;
                
                if (platformFee > 0) {
                    document.getElementById('feeAmount').textContent = `Rp ${formatCurrency(totalFee)}`;
                }

                let total = subtotal + totalFee - discountAmount;
                if (total < 0) total = 0;

                const discountRow = document.getElementById('discountRow');
                if (discountRow) {
                    if (discountAmount > 0) discountRow.classList.remove('hidden');
                    else discountRow.classList.add('hidden');
                }
                const discountAmountEl = document.getElementById('discountAmount');
                if (discountAmountEl) {
                    discountAmountEl.textContent = discountAmount > 0 ? `-Rp ${formatCurrency(discountAmount)}` : '-Rp 0';
                }

                document.getElementById('totalAmount').textContent = `Rp ${formatCurrency(total)}`;
            }

            // Coupon Logic (Frontend Trigger)
            const couponBtn = document.getElementById('applyCoupon');
            if(couponBtn) {
                couponBtn.addEventListener('click', () => {
                    const code = document.getElementById('coupon_code').value;
                    let subtotal = 0;
                    const categoryCounts = new Map();
                    const categoryPrices = new Map();
                    document.querySelectorAll('.category-select').forEach(el => {
                        if (!el.value) return;
                        const categoryId = String(el.value);
                        const price = parseFloat(el.getAttribute('data-active-price') || 0);
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

                    // AJAX Call to Laravel
                    fetch(`/events/${eventSlug}/register/coupon`, {
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
                            document.getElementById('discountRow').classList.remove('hidden');
                            document.getElementById('discountAmount').textContent = `-Rp ${formatCurrency(data.discount_amount)}`;
                            document.getElementById('couponMessage').innerHTML = '<span class="text-neon">Kupon berhasil digunakan!</span>';
                            updatePriceSummary();
                        } else {
                            appliedCoupon = null;
                            discountAmount = 0;
                            document.getElementById('coupon_code_hidden').value = '';
                            document.getElementById('couponMessage').innerHTML = `<span class="text-red-400">${data.message}</span>`;
                            updatePriceSummary();
                        }
                    });
                });
            }

            // Form Submit
        const form = document.getElementById('registrationForm');

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

        if(form) {
                // Disable browser validation
                form.setAttribute('novalidate', true);

                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    // --- Client-side Validation ---
                    let isValid = true;
                    let firstError = null;

                    // Helper to show error
                    const showError = (input, msg) => {
                        // Remove existing error
                        const existing = input.parentElement.querySelector('.text-red-500');
                        if(existing) existing.remove();
                        
                        // Add red border
                        input.classList.add('border-red-500');
                        input.classList.remove('border-slate-700');
                        
                        // Add message
                        const p = document.createElement('p');
                        p.className = 'text-xs text-red-500 mt-1 font-bold';
                        p.innerText = msg;
                        input.parentElement.appendChild(p);

                        if(!firstError) firstError = input;
                        isValid = false;
                    };

                    // Clear previous errors
                    form.querySelectorAll('.border-red-500').forEach(el => {
                        el.classList.remove('border-red-500');
                        el.classList.add('border-slate-700');
                    });
                    form.querySelectorAll('.text-red-500').forEach(el => el.remove());

                    // Validate PIC
                    const picName = form.querySelector('[name="pic_name"]');
                    if(!picName.value.trim()) showError(picName, 'Nama wajib diisi');

                    const picEmail = form.querySelector('[name="pic_email"]');
                    if(!picEmail.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(picEmail.value)) showError(picEmail, 'Email tidak valid');

                    const picPhone = form.querySelector('[name="pic_phone"]');
                    if(!picPhone.value.trim() || !/^\d{8,15}$/.test(picPhone.value.replace(/\D/g,''))) showError(picPhone, 'Nomor HP tidak valid (min 8 digit)');

                    // Validate Participants
                    form.querySelectorAll('.participant-item').forEach((item, idx) => {
                        const pName = item.querySelector(`[name="participants[${idx}][name]"]`);
                        if(pName && !pName.value.trim()) showError(pName, 'Nama peserta wajib diisi');

                        const pEmail = item.querySelector(`[name="participants[${idx}][email]"]`);
                        if(pEmail && (!pEmail.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(pEmail.value))) showError(pEmail, 'Email peserta tidak valid');

                        const pPhone = item.querySelector(`[name="participants[${idx}][phone]"]`);
                        if(pPhone && (!pPhone.value.trim() || !/^\d{8,15}$/.test(pPhone.value.replace(/\D/g,'')))) showError(pPhone, 'Nomor HP tidak valid');

                        const pId = item.querySelector(`[name="participants[${idx}][id_card]"]`);
                        if(pId && (!pId.value.trim() || !/^\d+$/.test(pId.value))) showError(pId, 'ID Card harus angka');
                        
                        const pEmergencyName = item.querySelector(`[name="participants[${idx}][emergency_contact_name]"]`);
                        if(pEmergencyName && !pEmergencyName.value.trim()) showError(pEmergencyName, 'Nama kontak darurat wajib diisi');

                        const pEmergencyNumber = item.querySelector(`[name="participants[${idx}][emergency_contact_number]"]`);
                        if(pEmergencyNumber && !pEmergencyNumber.value.trim()) showError(pEmergencyNumber, 'Nomor kontak darurat wajib diisi');

                        const pCat = item.querySelector(`[name="participants[${idx}][category_id]"]`);
                        if(pCat && !pCat.value) showError(pCat, 'Pilih kategori');
                    });

                    if(!isValid) {
                        if(firstError) firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
                        return;
                    }

                    if (typeof grecaptcha !== 'undefined') {
                        const recaptchaResponse = grecaptcha.getResponse();
                        if (!recaptchaResponse) {
                            alert('Silakan verifikasi reCAPTCHA terlebih dahulu.');
                            return;
                        }
                    }

                    // --- AJAX Submit ---
                    const btn = document.getElementById('submitBtn');
                    const originalText = btn.innerHTML;
                    
                    // Loading Spinner
                    btn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-dark inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        MEMPROSES...
                    `;
                    btn.disabled = true;

                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            if(data.snap_token) {
                                snap.pay(data.snap_token, {
                                    onSuccess: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`; },
                                    onPending: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; },
                                    onError: function(result){ alert("Pembayaran gagal"); btn.disabled=false; btn.innerHTML=originalText; },
                                    onClose: function(){ btn.disabled=false; btn.innerHTML=originalText; }
                                });
                            } else {
                                // COD or Free - Show Success Message immediately
                                const successDiv = document.createElement('div');
                                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-2xl z-50 flex items-center gap-3 animate-bounce';
                                successDiv.innerHTML = `
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <div>
                                        <h4 class="font-bold">Registrasi Berhasil!</h4>
                                        <p class="text-xs">Cek email Anda untuk konfirmasi.</p>
                                    </div>
                                `;
                                document.body.appendChild(successDiv);

                                setTimeout(() => {
                                    window.location.href = `{{ route("events.show", $event->slug) }}?success=true`;
                                }, 2000);
                            }
                        } else {
                            alert(data.message || 'Terjadi kesalahan');
                            btn.disabled=false; btn.innerHTML=originalText;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal menghubungi server');
                        btn.disabled=false; btn.innerHTML=originalText;
                    });
                });
            }
        })();

        // Race Results Vue App
        (function() {
            if (typeof Vue === 'undefined') {
                console.error('Vue.js is not loaded!');
                return;
            }

            const { createApp } = Vue;
            const resultsApp = createApp({
            data() {
                return {
                    search: '',
                    filterCategory: 'All',
                    filterGender: 'All',
                    results: [],
                    loading: true,
                    error: null,
                    showCertificate: false,
                    selectedRunner: {},
                    eventSlug: '{{ $event->slug }}',
                    baseUrl: '{{ url("/") }}'
                }
            },
            computed: {
                filteredResults() {
                    if (!this.results || !Array.isArray(this.results) || this.results.length === 0) {
                        console.log('No results to filter');
                        return [];
                    }
                    
                    const filtered = this.results.filter(r => {
                        if (!r) return false;
                        
                        // Filter Search (Name / BIB)
                        const s = (this.search || '').toLowerCase().trim();
                        const matchSearch = !s || 
                            (r.name && r.name.toLowerCase().includes(s)) || 
                            (r.bib && r.bib.toString().toLowerCase().includes(s));
                        
                        // Filter Category - check both category_code and category field
                        const categoryMatch = r.category || r.category_code || '';
                        const matchCat = this.filterCategory === 'All' || 
                            categoryMatch === this.filterCategory ||
                            categoryMatch.toLowerCase() === this.filterCategory.toLowerCase();
                        
                        // Filter Gender
                        const matchGender = this.filterGender === 'All' || 
                            (r.gender && r.gender.toUpperCase() === this.filterGender.toUpperCase());
                        
                        const passes = matchSearch && matchCat && matchGender;
                        if (!passes && s) {
                            console.log('Filtered out:', r.name, 'search:', matchSearch, 'cat:', matchCat, 'gender:', matchGender);
                        }
                        return passes;
                    });
                    
                    console.log('Filtered results:', filtered.length, 'from', this.results.length, 'filters:', {
                        category: this.filterCategory,
                        gender: this.filterGender,
                        search: this.search
                    });
                    return filtered;
                }
            },
            methods: {
                openCertificate(runner) {
                    this.selectedRunner = runner;
                    this.showCertificate = true;
                },
                resetFilters() {
                    this.search = '';
                    this.filterCategory = 'All';
                    this.filterGender = 'All';
                },
                async fetchResults() {
                    this.loading = true;
                    this.error = null;
                    try {
                        // Don't send filters in initial fetch, let computed property handle filtering
                        const params = new URLSearchParams();
                        
                        // Use Laravel url() helper to get correct base URL
                        const apiUrl = `${this.baseUrl}/api/events/${encodeURIComponent(this.eventSlug)}/results?${params}`;
                        console.log('Base URL:', this.baseUrl);
                        console.log('Fetching from:', apiUrl);
                        const response = await fetch(apiUrl);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.results = Array.isArray(data.data) ? data.data : [];
                            console.log('✅ Race results loaded:', this.results.length);
                            if (this.results.length > 0) {
                                console.log('✅ First result sample:', this.results[0]);
                                console.log('✅ Current filters - category:', this.filterCategory, 'gender:', this.filterGender, 'search:', this.search);
                                
                                // Use nextTick to ensure Vue reactivity
                                this.$nextTick(() => {
                                    console.log('✅ After nextTick - filteredResults:', this.filteredResults.length);
                                    if (this.filteredResults.length > 0) {
                                        console.log('✅ Filtered results sample:', this.filteredResults[0]);
                                    } else {
                                        console.warn('⚠️ Filtered results is empty! Check filters.');
                                        console.log('All results categories:', [...new Set(this.results.map(r => r.category))]);
                                    }
                                });
                            }
                        } else {
                            throw new Error(data.message || 'Failed to load results');
                        }
                    } catch (error) {
                        console.error('Error fetching results:', error);
                        this.error = error.message || 'Gagal memuat data race results. Silakan refresh halaman.';
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                }
            },
            watch: {
                // Remove watchers - filtering is handled by computed property
                // This prevents unnecessary API calls
            },
            mounted() {
                console.log('Vue app mounted, event slug:', this.eventSlug);
                console.log('Initial state - loading:', this.loading, 'results:', this.results.length);
                this.fetchResults();
                
                // Debug: log state changes
                this.$watch('loading', (newVal) => {
                    console.log('Loading changed to:', newVal);
                });
                this.$watch('results', (newVal) => {
                    console.log('Results changed, count:', newVal.length);
                }, { deep: true });
                this.$watch('filteredResults', (newVal) => {
                    console.log('Filtered results changed, count:', newVal.length);
                }, { deep: true });

                // FIX: Re-initialize observer for elements inside Vue because Vue replaces the DOM elements
                this.$nextTick(() => {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('active');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });

                    // Observe elements inside the Vue app root
                    const appRoot = document.getElementById('resultsApp');
                    if (appRoot) {
                        appRoot.querySelectorAll('.reveal').forEach(el => observer.observe(el));
                    }
                });
            }
        });
        
        // Mount Vue app
        const resultsContainer = document.getElementById('resultsApp');
        if (resultsContainer) {
            resultsApp.mount('#resultsApp');
            console.log('Race results Vue app initialized');
        } else {
            console.error('Results container #resultsApp not found!');
        }
        })();

        
    </script>
    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/95 backdrop-blur-sm transition-opacity opacity-0" id="lightboxBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                <!-- Close Button -->
                <button type="button" id="lightboxClose" class="absolute top-6 right-6 z-20 text-slate-400 hover:text-white transition focus:outline-none">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Image Container -->
                <div class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl opacity-0 scale-95" id="lightboxContent">
                    <img id="lightboxImage" src="" class="w-full h-auto max-h-[85vh] object-contain mx-auto rounded-lg shadow-2xl border border-white/10" alt="Full Preview">
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lightbox = document.getElementById('lightboxModal');
            const backdrop = document.getElementById('lightboxBackdrop');
            const content = document.getElementById('lightboxContent');
            const img = document.getElementById('lightboxImage');
            const closeBtn = document.getElementById('lightboxClose');
            const triggers = document.querySelectorAll('.lightbox-trigger');

            function openLightbox(src) {
                if(!src) return;
                img.src = src;
                lightbox.classList.remove('hidden');
                
                // Animation frame for transition
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    content.classList.remove('opacity-0', 'scale-95');
                });
                
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                backdrop.classList.add('opacity-0');
                content.classList.add('opacity-0', 'scale-95');
                
                setTimeout(() => {
                    lightbox.classList.add('hidden');
                    img.src = '';
                }, 300); // Match transition duration
                
                document.body.style.overflow = '';
            }

            // Event Listeners
            triggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent bubbling if needed
                    openLightbox(trigger.src);
                });
            });

            closeBtn.addEventListener('click', closeLightbox);
            
            // Close on click outside (backdrop)
            lightbox.addEventListener('click', (e) => {
                if (e.target.closest('#lightboxContent')) return;
                closeLightbox();
            });

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) {
                    closeLightbox();
                }
            });

            // Check for payment success from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('payment') === 'success' || urlParams.get('success') === 'true') {
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-2xl z-50 flex items-center gap-3 animate-bounce';
                successDiv.innerHTML = `
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div>
                        <h4 class="font-bold">Registrasi Berhasil!</h4>
                        <p class="text-xs">Cek email Anda untuk konfirmasi.</p>
                    </div>
                `;
                document.body.appendChild(successDiv);
                
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

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
