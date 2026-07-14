@extends('layouts.pacerhub')

@section('title', 'RuangLari | Kalender Event Lari, Komunitas, dan Tools Pelari Indonesia')
@section('meta_title', 'RuangLari | Kalender Event Lari, Komunitas, dan Tools Pelari Indonesia')
@section('meta_description', 'Temukan event lari terbaru, kalender race, komunitas pelari, leaderboard, dan tools latihan dalam satu platform RuangLari.')
@section('canonical_url', url('/'))

@section('content')
    <div id="home-app" class="overflow-x-hidden bg-[#08111F]">
        
        <!-- HERO SECTION -->
        <header class="relative min-h-screen flex items-center justify-center pt-24 md:pt-0">
            <!-- Dynamic Background -->
            <div class="absolute inset-0 z-0 overflow-hidden">
                <div class="absolute inset-0 bg-[#08111F]"></div>
                <!-- Subtle Radial Glow -->
                <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-[#B8FF00]/5 rounded-full blur-[120px] animate-pulse-slow"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[150px] animate-pulse-slow" style="animation-delay: 2s"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-12">
                <div class="grid md:grid-cols-2 gap-12 md:gap-16 items-center">
                    
                    <!-- Hero Text -->
                    <div class="text-center md:text-left order-2 md:order-1" data-aos="fade-up">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#B8FF00]/20 bg-[#B8FF00]/5 backdrop-blur-md text-[#B8FF00] text-xs font-bold uppercase tracking-wider mb-8">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#B8FF00] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#B8FF00]"></span>
                            </span>
                            Dari Pelari Untuk Pelari
                        </div>
                        
                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-black leading-none mb-6 text-[#F8FAFC] tracking-tighter uppercase">
                            LARI TANPA <br class="hidden md:block">
                            <span class="text-[#B8FF00]">BATAS.</span>
                        </h1>
                        
                        <p class="text-[#94A3B8] text-lg md:text-xl mb-10 max-w-lg mx-auto md:mx-0 leading-relaxed font-normal">
                            Temukan event lari, kelola progres latihan, dan terhubung dengan komunitas pelari di Indonesia. Untuk event organizer, RuangLari membantu publikasi, ticketing, dan manajemen peserta dalam satu sistem.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                            <a href="#events" class="px-8 py-4 bg-[#B8FF00] text-[#08111F] font-black rounded-xl hover:bg-[#9FE000] hover:scale-105 transition transform text-center shadow-lg shadow-[#B8FF00]/20 uppercase tracking-wider text-sm">
                                Lihat Event
                            </a>
                            <a href="{{ route('eo.landing') }}" class="px-8 py-4 border border-[#1F2D44] bg-[#0E1A2D]/50 text-[#F8FAFC] font-black rounded-xl hover:bg-[#111F35] hover:border-[#94A3B8] hover:scale-105 transition transform text-center uppercase tracking-wider text-sm">
                                Untuk Event Organizer
                            </a>
                        </div>

                        <div class="mt-12 flex items-center justify-center md:justify-start gap-4">
                            <div class="text-left">
                                <div class="flex text-white text-xs mb-1">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                                <p class="text-sm text-[#94A3B8]"><span class="text-white font-bold">1000+</span> Pelari Telah Bergabung</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Featured Slider -->
                    <div class="relative order-1 md:order-2" data-aos="fade-left" data-aos-delay="200">
                        @php
                            $slides = collect();
                            $fallbackHero = $homepageContent && $homepageContent->floating_image
                                ? asset($homepageContent->floating_image)
                                : 'https://res.cloudinary.com/dslfarxct/images/v1766050868/542301374_18517775974013478_1186867397282832240_n/542301374_18517775974013478_1186867397282832240_n.jpg';

                            if (isset($featuredEvents) && $featuredEvents) {
                                foreach ($featuredEvents as $ev) {
                                    $slides->push([
                                        'type' => 'event',
                                        'title' => $ev->name,
                                        'href' => $ev->public_url,
                                        'image' => $ev->getHeroImageUrl(),
                                        'eyebrow' => 'Featured Event',
                                        'meta_1' => optional($ev->start_at)->translatedFormat('d M Y') ?: null,
                                        'meta_2' => $ev->location_name ?: null,
                                    ]);
                                }
                            }

                            if (isset($featuredArticles) && $featuredArticles) {
                                foreach ($featuredArticles as $a) {
                                    $img = null;
                                    if ($a->featured_image) {
                                        if (Str::startsWith($a->featured_image, ['http://', 'https://'])) {
                                            $img = $a->featured_image;
                                        } else {
                                            $img = asset('storage/' . $a->featured_image);
                                        }
                                    }
                                    $slides->push([
                                        'type' => 'article',
                                        'title' => $a->title,
                                        'href' => route('blog.show', $a->slug),
                                        'image' => $img ?: asset('ruanglari.webp'),
                                        'eyebrow' => 'Featured Artikel',
                                        'meta_1' => optional($a->published_at ?: $a->created_at)->translatedFormat('d M Y') ?: null,
                                        'meta_2' => optional($a->category)->name ?: null,
                                    ]);
                                }
                            }

                            $slides = $slides->filter(fn ($s) => ! empty($s['href']))->values()->take(6);
                        @endphp

                        <div class="relative z-10 rounded-[2rem] overflow-hidden border border-[#1F2D44] shadow-2xl transition duration-700 group">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#08111F]/90 via-[#08111F]/30 to-transparent z-10 pointer-events-none"></div>

                            <div class="relative">
                                <div id="heroFeaturedTrack" class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory scroll-smooth">
                                    @forelse($slides as $i => $s)
                                        <a href="{{ $s['href'] }}" class="snap-center flex-none w-full relative block" aria-label="{{ $s['eyebrow'] }}: {{ $s['title'] }}">
                                            <img src="{{ $s['image'] ?: $fallbackHero }}" alt="{{ $s['title'] }}" class="w-full h-[400px] md:h-[480px] object-cover object-center transform transition duration-1000 group-hover:scale-105" @if($i === 0) fetchpriority="high" @else loading="lazy" @endif onerror="this.onerror=null; this.src='{{ $fallbackHero }}';">

                                            <div class="absolute bottom-6 left-6 right-6 z-20 bg-[#0E1A2D]/90 hover:bg-[#0E1A2D] backdrop-blur-md p-5 rounded-2xl border border-[#1F2D44] transition">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-12 h-12 rounded-xl bg-[#B8FF00]/10 flex items-center justify-center text-[#B8FF00] flex-shrink-0 border border-[#B8FF00]/20">
                                                        @if($s['type'] === 'event')
                                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                        @else
                                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-[10px] text-[#B8FF00] uppercase tracking-widest font-black mb-1">{{ $s['eyebrow'] }}</p>
                                                        <h3 class="text-base md:text-lg font-bold text-white leading-tight line-clamp-2">{{ $s['title'] }}</h3>
                                                        <div class="mt-2 text-xs text-[#94A3B8] space-y-1">
                                                            @if(!empty($s['meta_1']))
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-slate-500">{{ $s['type'] === 'event' ? 'Tanggal:' : 'Terbit:' }}</span>
                                                                    <span class="font-bold text-[#F8FAFC]">{{ $s['meta_1'] }}</span>
                                                                </div>
                                                            @endif
                                                            @if(!empty($s['meta_2']))
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-slate-500">{{ $s['type'] === 'event' ? 'Lokasi:' : 'Kategori:' }}</span>
                                                                    <span class="font-bold text-[#F8FAFC] truncate">{{ $s['meta_2'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="hidden sm:flex items-center self-center">
                                                        <span class="px-4 py-2 rounded-xl bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider transition hover:bg-[#9FE000]">{{ $s['type'] === 'event' ? 'Ikuti' : 'Baca' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="snap-center flex-none w-full relative">
                                             <img src="{{ $featuredEvent ? $featuredEvent->getHeroImageUrl() : $fallbackHero }}" alt="Hero" class="w-full h-[400px] md:h-[480px] object-cover object-center" onerror="this.onerror=null; this.src='{{ $fallbackHero }}';">
                                        </div>
                                    @endforelse
                                </div>

                                @if($slides->count() > 1)
                                    <button type="button" id="heroFeaturedPrev" class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-2xl bg-[#0E1A2D]/80 hover:bg-[#0E1A2D] border border-[#1F2D44] backdrop-blur items-center justify-center text-white transition">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                    </button>
                                    <button type="button" id="heroFeaturedNext" class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-2xl bg-[#0E1A2D]/80 hover:bg-[#0E1A2D] border border-[#1F2D44] backdrop-blur items-center justify-center text-white transition">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>

                                    <div id="heroFeaturedDots" class="absolute bottom-3 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 bg-[#0E1A2D]/80 border border-[#1F2D44] backdrop-blur px-3 py-2 rounded-full"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- UPCOMING EVENTS SECTION -->
        <section id="events" class="py-24 relative overflow-hidden bg-[#08111F] border-t border-[#1F2D44]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
                    <div data-aos="fade-right">
                        <span class="text-[#B8FF00] font-bold tracking-widest uppercase text-sm mb-2 block">Kalender Race</span>
                        <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight">Event Lari Mendatang</h2>
                        <p class="text-[#94A3B8] mt-2 max-w-xl text-base">Jelajahi kalender race lari terbaru di Indonesia dan daftar langsung secara online.</p>
                    </div>
                    <a href="/jadwal-lari" class="group flex items-center gap-2 text-[#94A3B8] hover:text-white transition font-bold border-b border-[#1F2D44] hover:border-white pb-1" data-aos="fade-left">
                        Lihat Semua Event 
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>

                <div id="homeEvents" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Events Loaded via JS -->
                    <div class="col-span-1 md:col-span-2 flex flex-col items-center justify-center py-20 text-slate-500">
                        <div class="w-10 h-10 border-4 border-[#1F2D44] border-t-[#B8FF00] rounded-full animate-spin mb-4"></div>
                        <p class="text-sm">Memuat jadwal lari...</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SATU EKOSISTEM UNTUK PELARI -->
        <section class="py-24 relative overflow-hidden bg-[#08111F] border-t border-[#1F2D44]">
            <!-- Subtle glow -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-[#B8FF00]/5 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16" data-aos="fade-up">
                    <span class="text-[#B8FF00] font-bold tracking-widest uppercase text-sm mb-2 block">Ekosistem Ruang Lari</span>
                    <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight">Satu Ekosistem untuk Pelari</h2>
                    <p class="text-[#94A3B8] mt-3 max-w-2xl mx-auto text-base">Kami mengintegrasikan seluruh kebutuhan pelari mulai dari tools latihan, kalender event, komunitas, hingga gear pendukung.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Card 1: Running Tools & Performance -->
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/50 transition duration-300 h-full flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="0">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700 group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 leading-tight">Pantau & Tingkatkan Latihan</h3>
                            <p class="text-[#94A3B8] text-sm leading-relaxed mb-6">Tingkatkan performa larimu dengan kalkulator pace, training plan custom, serta bimbingan dari coach TrackMaster Pro.</p>
                        </div>
                        <div class="pt-6 border-t border-[#1F2D44] space-y-2">
                            <a href="{{ route('calendar.public') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Kalender Lari</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('calculator') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Pace Calculator</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('programs.index') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Program Latihan</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Community & Network -->
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/50 transition duration-300 h-full flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="100">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700 group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 leading-tight">Terhubung dengan Komunitas</h3>
                            <p class="text-[#94A3B8] text-sm leading-relaxed mb-6">Terhubung dengan ribuan pelari se-Indonesia, temukan profil rekan pelari, dan booking pacer resmi untuk target race Anda.</p>
                        </div>
                        <div class="pt-6 border-t border-[#1F2D44] space-y-2">
                            <a href="{{ route('users.runners') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Profil Pelari</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('pacer.index') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Booking Pacers</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Event Ticketing -->
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/50 transition duration-300 h-full flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="200">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700 group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 leading-tight">Pendaftaran Event Lari</h3>
                            <p class="text-[#94A3B8] text-sm leading-relaxed mb-6">Nikmati kemudahan registrasi event lari nasional yang aman, praktis, dengan konfirmasi e-ticket instan via e-mail.</p>
                        </div>
                        <div class="pt-6 border-t border-[#1F2D44] space-y-2">
                            <a href="{{ route('events.index') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Cari Event Lari</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 4: Gear Marketplace -->
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/50 transition duration-300 h-full flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="300">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700 group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 leading-tight">Pasar Perlengkapan Lari</h3>
                            <p class="text-[#94A3B8] text-sm leading-relaxed mb-6">Temukan perlengkapan lari berkualitas dari brand terpercaya, atau titipkan gear lari pre-loved Anda lewat sistem konsinyasi kami.</p>
                        </div>
                        <div class="pt-6 border-t border-[#1F2D44] space-y-2">
                            <a href="{{ route('marketplace.index') }}" class="flex items-center justify-between text-xs text-[#94A3B8] hover:text-[#B8FF00] font-bold transition">
                                <span>Jual Beli Gear</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- CARI TEMAN LARI (RUN CONNECT) INTRO SECTION -->
        <section class="py-24 relative overflow-hidden bg-[#08111F] border-t border-[#1F2D44]">
            <!-- Subtle Glows -->
            <div class="absolute top-[20%] left-[-10%] w-[450px] h-[450px] bg-blue-600/5 rounded-full blur-[130px] pointer-events-none"></div>
            <div class="absolute bottom-[20%] right-[-10%] w-[450px] h-[450px] bg-[#B8FF00]/5 rounded-full blur-[130px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid md:grid-cols-2 gap-12 md:gap-16 items-center">
                    
                    <!-- Text Content -->
                    <div data-aos="fade-right">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#B8FF00]/25 bg-[#B8FF00]/5 backdrop-blur-md text-[#B8FF00] text-xs font-bold uppercase tracking-wider mb-6">
                            Fitur Baru
                        </div>
                        <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight uppercase leading-none mb-6">
                            Temukan Teman Lari <br>
                            <span class="text-[#B8FF00]">Terdekat Anda!</span>
                        </h2>
                        
                        <p class="text-[#94A3B8] text-base md:text-lg mb-8 leading-relaxed font-normal">
                            Ingin lari pagi di GBK atau sore hari di Malang Kayutangan tapi tidak ada teman? Dengan fitur <strong>Cari Teman Lari</strong>, Anda bisa menemukan pelari terdekat di kota Anda, membuat jadwal lari bersama, dan mengobrol secara langsung dalam obrolan grup. 
                        </p>

                        <!-- Key Points -->
                        <div class="space-y-4 mb-10">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-800/80 flex items-center justify-center text-white shrink-0 mt-0.5 border border-slate-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white mb-0.5">Pemetaan Real-time</h4>
                                    <p class="text-xs text-[#94A3B8]">Lihat rute lari aktif dan lokasi pelari lain di kota Anda secara interaktif di atas peta.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-800/80 flex items-center justify-center text-white shrink-0 mt-0.5 border border-slate-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white mb-0.5">Jodoh Lari (Match Buddy)</h4>
                                    <p class="text-xs text-[#94A3B8]">Masukkan preferensi target jarak dan pace Anda untuk dicocokkan otomatis dengan teman lari yang sesuai.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-800/80 flex items-center justify-center text-white shrink-0 mt-0.5 border border-slate-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white mb-0.5">Obrolan Grup Lari</h4>
                                    <p class="text-xs text-[#94A3B8]">Koordinasikan tempat berkumpul, barang bawaan, dan obrolan persiapan lari secara langsung di dalam thread.</p>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-start">
                            <a href="{{ route('run-connect.index') }}" class="px-8 py-4 bg-[#B8FF00] text-[#08111F] font-black rounded-xl hover:bg-[#9FE000] hover:scale-105 transition transform text-center shadow-lg shadow-[#B8FF00]/20 uppercase tracking-wider text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Cari Teman Lari Sekarang
                            </a>
                        </div>
                    </div>

                    <!-- Image / Preview Content -->
                    <div class="relative" data-aos="fade-left" data-aos-delay="200">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-[2rem] transform rotate-3 scale-95 opacity-25 blur-sm z-0"></div>
                        <div class="relative z-10 rounded-[2rem] overflow-hidden border border-[#1F2D44] shadow-2xl bg-[#0E1A2D]">
                            <img src="https://ruanglari.com/storage/blog/media/Eg6tJAZfqg7uRUqFufYDcPdFzd1uCJy1Uad4A2xg.webp" alt="Cari Teman Lari Ruang Lari" class="w-full h-[380px] md:h-[420px] object-cover object-center">
                            <!-- Overlay Badge -->
                            <div class="absolute bottom-6 left-6 right-6 z-20 bg-[#0E1A2D]/95 backdrop-blur-md p-4 rounded-xl border border-[#1F2D44]">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-800/80 flex items-center justify-center text-white border border-slate-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h5 class="text-xs font-bold text-white">100% Aman & Terverifikasi</h5>
                                        <p class="text-[10px] text-[#94A3B8] mt-0.5">Gabung dengan komunitas pelari terverifikasi di wilayah sekitar Anda.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- KELOLA EVENT LARI TANPA RIBET (EO SECTION) -->
        <section class="py-24 bg-[#08111F] relative overflow-hidden border-t border-[#1F2D44]">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] bg-blue-600/5 rounded-full blur-[140px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-6 mb-14">
                    <div data-aos="fade-right" class="max-w-2xl">
                        <span class="text-[#B8FF00] font-bold tracking-widest uppercase text-sm mb-2 block">Solusi Untuk Event Organizer</span>
                        <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight">
                            Kelola Event Lari Tanpa Ribet
                        </h2>
                        <p class="text-[#94A3B8] mt-4 text-base leading-relaxed">
                            Sistem ticketing dan registrasi advanced yang dibuat khusus untuk event lari. Dilengkapi manajemen kuota real-time, kode promo fleksibel, add-on jersey, serta integrasi e-ticket dan WhatsApp blast.
                        </p>
                    </div>
                    <div class="flex gap-3 shrink-0" data-aos="fade-left">
                        <a href="{{ route('eo.landing') }}" class="px-6 py-3 rounded-xl bg-[#B8FF00] text-[#08111F] font-black hover:bg-[#9FE000] hover:scale-105 transition transform text-sm uppercase tracking-wider">
                            Lihat Detail
                        </a>
                        <a href="{{ route('events.index') }}" class="px-6 py-3 rounded-xl border border-[#1F2D44] bg-[#0E1A2D] text-white font-bold hover:bg-[#111F35] transition text-sm uppercase tracking-wider">
                            Contoh Event
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/30 transition" data-aos="fade-up" data-aos-delay="0">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Kuota & Kategori Real-time</h3>
                        <p class="text-[#94A3B8] text-sm leading-relaxed">Atur kuota, early bird, slot per kategori secara dinamis dengan status real-time untuk menghindari over-booking.</p>
                    </div>

                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/30 transition" data-aos="fade-up" data-aos-delay="100">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5 5.5L4 19l5.5-5.5M14 9l5.5-5.5L20 4l-5.5 5.5M6 6l12 12" /></svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Promo, Kupon, & Jersey Add-on</h3>
                        <p class="text-[#94A3B8] text-sm leading-relaxed">Kelola kode promo komunitas, diskon progresif, ukuran jersey, bib number custom, hingga opsi merchandise dalam satu formulir.</p>
                    </div>

                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/30 transition" data-aos="fade-up" data-aos-delay="200">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2v1c0 1.105 1.343 2 3 2s3 .895 3 2v1c0 1.105-1.343 2-3 2m0-14v2m0 16v2m9-10a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Pembayaran Online Otomatis</h3>
                        <p class="text-[#94A3B8] text-sm leading-relaxed">Integrasi Virtual Account, E-Wallet (Gopay/QRIS), dan Kartu Kredit dengan verifikasi instan tanpa perlu konfirmasi manual.</p>
                    </div>

                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 hover:border-[#B8FF00]/30 transition" data-aos="fade-up" data-aos-delay="300">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 flex items-center justify-center text-white mb-6 border border-slate-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h2m-6 6h6m2 0a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2h2" /></svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Dashboard Rekap & Race Day Ops</h3>
                        <p class="text-[#94A3B8] text-sm leading-relaxed">Ekspor data peserta berformat Excel untuk tim timer (BIB tagging) dan kelola check-in kehadiran secara live saat race day.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- WEEKLY LEADERBOARD SECTION -->
        <section id="leaderboard" class="py-24 bg-[#08111F] relative overflow-hidden border-t border-[#1F2D44]">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#B8FF00]/5 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16" data-aos="fade-up">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-700 bg-slate-800/80 text-white text-xs font-bold uppercase tracking-wider mb-6">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                        Powered by Strava
                    </div>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('calendar.strava.connect', ['return_to' => '/#leaderboard']) }}" class="text-[10px] text-slate-500 hover:text-white ml-2">[Admin: Re-Connect Strava]</a>
                        @endif
                    @endauth
                    <h2 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-4">
                        Weekly <span class="text-[#B8FF00]">Leaderboard</span>
                    </h2>
                    <p class="text-[#94A3B8] max-w-2xl mx-auto text-base">
                        Pantau performa terbaik minggu ini dari member <a href="https://www.strava.com/clubs/1859982" target="_blank" class="text-[#B8FF00] hover:underline font-bold">Ruang Lari Club</a> di Strava. Gabungkan catatan larimu dan tantang dirimu untuk masuk papan peringkat teratas!
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8" id="leaderboardGrid">
                    <!-- Skeleton Loaders (shown while AJAX loads) -->
                    <div class="leaderboard-skeleton group relative" data-aos="fade-up" data-aos-delay="0">
                        <div class="relative bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 overflow-hidden h-full">
                            <div class="absolute top-0 right-0 bg-slate-800 border-l border-b border-[#1F2D44] px-4 py-2 rounded-bl-2xl">
                                <div class="h-3 w-20 bg-slate-700 rounded animate-pulse"></div>
                            </div>
                            <div class="pt-6 flex items-center gap-4 mb-6">
                                <div class="w-16 h-16 rounded-full bg-slate-700 animate-pulse flex-shrink-0"></div>
                                <div class="space-y-2 flex-1">
                                    <div class="h-4 bg-slate-700 rounded animate-pulse w-3/4"></div>
                                    <div class="h-3 bg-slate-800 rounded animate-pulse w-1/2"></div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="h-3 bg-slate-800 rounded animate-pulse w-1/3"></div>
                                <div class="h-8 bg-slate-700 rounded animate-pulse w-1/2 ml-auto"></div>
                                <div class="h-2 bg-slate-800 rounded-full animate-pulse w-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="leaderboard-skeleton group relative transform md:-translate-y-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="relative bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 overflow-hidden h-full">
                            <div class="absolute top-0 right-0 bg-slate-800 border-l border-b border-[#1F2D44] px-4 py-2 rounded-bl-2xl">
                                <div class="h-3 w-20 bg-slate-700 rounded animate-pulse"></div>
                            </div>
                            <div class="pt-6 flex items-center gap-4 mb-6">
                                <div class="w-20 h-20 rounded-full bg-slate-700 animate-pulse flex-shrink-0"></div>
                                <div class="space-y-2 flex-1">
                                    <div class="h-4 bg-slate-700 rounded animate-pulse w-3/4"></div>
                                    <div class="h-3 bg-slate-800 rounded animate-pulse w-1/2"></div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="h-3 bg-slate-800 rounded animate-pulse w-1/3"></div>
                                <div class="h-10 bg-slate-700 rounded animate-pulse w-2/3 ml-auto"></div>
                                <div class="h-3 bg-slate-800 rounded-full animate-pulse w-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="leaderboard-skeleton group relative" data-aos="fade-up" data-aos-delay="200">
                        <div class="relative bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 overflow-hidden h-full">
                            <div class="absolute top-0 right-0 bg-slate-800 border-l border-b border-[#1F2D44] px-4 py-2 rounded-bl-2xl">
                                <div class="h-3 w-20 bg-slate-700 rounded animate-pulse"></div>
                            </div>
                            <div class="pt-6 flex items-center gap-4 mb-6">
                                <div class="w-16 h-16 rounded-full bg-slate-700 animate-pulse flex-shrink-0"></div>
                                <div class="space-y-2 flex-1">
                                    <div class="h-4 bg-slate-700 rounded animate-pulse w-3/4"></div>
                                    <div class="h-3 bg-slate-800 rounded animate-pulse w-1/2"></div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="h-3 bg-slate-800 rounded animate-pulse w-1/3"></div>
                                <div class="h-8 bg-slate-700 rounded animate-pulse w-1/2 ml-auto"></div>
                                <div class="h-2 bg-slate-800 rounded-full animate-pulse w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <a href="https://www.strava.com/clubs/1859982" target="_blank" class="inline-flex items-center gap-2 px-8 py-4 bg-[#FC4C02] text-white font-bold rounded-xl hover:bg-[#E34402] hover:scale-105 transition transform shadow-lg shadow-orange-500/20 text-sm uppercase tracking-wider">
                        <span>Gabung Klub Strava RuangLari</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>
            </div>
        </section>

        <script>
        (function () {
            function buildLeaderboardCard(data, label, color, badge, badgeColor, barWidth, isCenter) {
                const avatar = data.avatar || 'https://www.gravatar.com/avatar/?d=mp';
                const name   = data.name   || '—';
                const value  = data.value  || '—';
                const unit   = data.unit   || '';

                const borderClass  = isCenter ? `border-[#B8FF00]/50 shadow-[0_0_30px_rgba(184,255,0,0.05)]` : `border-[#1F2D44] hover:border-${color}-500/50`;
                const ringClass    = isCenter ? `bg-gradient-to-br from-[#B8FF00] to-emerald-400 w-20 h-20 p-1 border-4` : `bg-${color}-500 w-16 h-16 p-0.5 border-2`;
                const badgeBg      = isCenter ? `bg-[#B8FF00] text-[#08111F]` : `bg-${color}-600 text-white`;
                const badgeSz      = isCenter ? `text-xs px-2 py-0.5` : `text-[9px] px-1.5 py-0.5`;
                const badgeText    = isCenter ? 'MVP' : '#1';
                const nameSize     = isCenter ? `text-lg` : `text-base`;
                const subColor     = isCenter ? `text-[#B8FF00]` : `text-[#94A3B8]`;
                const statSize     = isCenter ? `text-4xl` : `text-3xl`;
                const barColor     = isCenter ? `bg-gradient-to-r from-[#B8FF00] to-emerald-400` : `bg-${color}-500`;
                const barH         = isCenter ? `h-3` : `h-2`;

                const topBadgeBg   = isCenter ? `bg-[#FC4C02] text-white` : `bg-slate-800 text-white border-l border-b border-[#1F2D44]`;
                const translateY   = isCenter ? `transform md:-translate-y-4` : ``;

                return `
                <div class="group relative ${translateY}">
                    <div class="relative bg-[#0E1A2D] border ${borderClass} rounded-3xl p-6 overflow-hidden transition duration-300 h-full flex flex-col justify-between">
                        <div class="absolute top-0 right-0 ${topBadgeBg} text-xs font-black px-4 py-2 rounded-bl-2xl uppercase tracking-widest flex items-center gap-1.5">
                            ${badge}
                        </div>
                        <div class="pt-6">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="relative flex-shrink-0">
                                    <div class="${ringClass} rounded-full border-[#0E1A2D] flex items-center justify-center overflow-hidden">
                                        <img src="${avatar}" loading="lazy" class="w-full h-full rounded-full object-cover">
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 ${badgeBg} ${badgeSz} font-black rounded-full border-2 border-[#0E1A2D]">${badgeText}</div>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold ${nameSize} leading-tight">${name}</h4>
                                    <p class="${subColor} text-xs uppercase tracking-wider font-semibold">${label}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-end justify-between mb-2">
                                <span class="text-[#94A3B8] text-xs uppercase tracking-wider font-bold">${label.split(' ')[0]}</span>
                                <span class="${statSize} font-black text-white">${value} <span class="text-xs text-slate-500 font-normal">${unit}</span></span>
                            </div>
                            <div class="w-full ${barH} bg-[#111F35] rounded-full overflow-hidden">
                                <div class="h-full ${barColor}" style="width:${barWidth}"></div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            fetch('{{ route("api.strava.leaderboard") }}')
                .then(r => r.json())
                .then(res => {
                    const grid = document.getElementById('leaderboardGrid');
                    if (!res.ok || !res.data) {
                        grid.innerHTML = `
                            <div class="col-span-3 text-center py-10">
                                <div class="inline-block p-8 rounded-3xl bg-[#0E1A2D] border border-[#1F2D44] max-w-md">
                                    <p class="text-[#94A3B8] mb-6 leading-relaxed">Belum ada data aktivitas lari minggu ini dari klub Strava. Jadilah yang pertama!</p>
                                    <a href="{{ route('calendar.strava.connect', ['return_to' => '/#leaderboard']) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#FC4C02] text-white font-bold rounded-xl hover:bg-[#E34402] transition uppercase text-xs tracking-wider">Hubungkan Strava</a>
                                </div>
                            </div>`;
                        return;
                    }
                    const d = res.data;
                    let html = '';
                    if (d.fastest)   html += buildLeaderboardCard(d.fastest,   'Fastest Pace',      'blue',   '<i class="fas fa-bolt"></i> Speed Demon',      'blue',   '95%', false);
                    if (d.distance)  html += buildLeaderboardCard(d.distance,  'Distance Leader',   'neon',   '<i class="fas fa-crown"></i> MVP Leader',       'neon',   '88%', true);
                    if (d.elevation) html += buildLeaderboardCard(d.elevation, 'Highest Climb',     'purple', '<i class="fas fa-mountain"></i> Elevation King', 'purple', '75%', false);
                    if (html) grid.innerHTML = html;
                })
                .catch(() => {});
        })();
        </script>

        <!-- NEWS & ARTICLES SECTION -->
        <section id="blog" class="py-24 bg-[#08111F] border-t border-[#1F2D44]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
                    <div>
                        <span class="text-[#B8FF00] font-bold tracking-widest uppercase text-sm mb-2 block">Tips & Edukasi</span>
                        <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight">News & Articles</h2>
                        <p class="text-[#94A3B8] mt-2 max-w-xl text-base">Ikuti tips latihan lari terbaru, review sepatu, nutrisi olahraga, dan cerita inspiratif dari pelari Indonesia.</p>
                    </div>
                    <a href="{{ url('/blog') }}" class="text-sm font-bold text-[#94A3B8] hover:text-white transition flex items-center gap-2 border-b border-transparent hover:border-white pb-1 shrink-0">
                        Lihat Artikel Terbaru <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
                <div id="blogCards" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </section>

    </div>
@endsection

@push('styles')
<style>
    :root {
        --color-bg: #08111F;
        --color-surface: #0E1A2D;
        --color-surface-2: #111F35;
        --color-border: #1F2D44;
        --color-text-primary: #F8FAFC;
        --color-text-secondary: #94A3B8;
        --color-lime: #B8FF00;
        --color-lime-hover: #9FE000;
    }
    
    body {
        background-color: var(--color-bg) !important;
        color: var(--color-text-primary) !important;
    }
    
    .bg-dark {
        background-color: var(--color-bg) !important;
    }
    
    .bg-card, .bg-slate-900 {
        background-color: var(--color-surface) !important;
    }
    
    .bg-slate-950, .bg-slate-950\/50, .bg-slate-900\/30, .bg-slate-900\/50, .bg-slate-950\/50 {
        background-color: var(--color-surface-2) !important;
    }
    
    .border-slate-800, .border-slate-800\/50, .border-slate-700, .border-slate-600 {
        border-color: var(--color-border) !important;
    }
    
    .text-slate-400, .text-slate-300 {
        color: var(--color-text-secondary) !important;
    }
    
    .text-neon, .text-primary {
        color: var(--color-lime) !important;
    }
    
    .bg-neon, .bg-primary {
        background-color: var(--color-lime) !important;
        color: var(--color-bg) !important;
    }

    .bg-neon\/10 {
        background-color: rgba(184, 255, 0, 0.1) !important;
    }

    .bg-neon\/5 {
        background-color: rgba(184, 255, 0, 0.05) !important;
    }

    .border-neon\/20 {
        border-color: rgba(184, 255, 0, 0.2) !important;
    }

    .border-neon\/50 {
        border-color: rgba(184, 255, 0, 0.5) !important;
    }

    .hover\:bg-white:hover {
        background-color: var(--color-text-primary) !important;
        color: var(--color-bg) !important;
    }
    
    .animate-pulse-slow { 
        animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite; 
    }
    
    .no-scrollbar::-webkit-scrollbar { 
        display: none; 
    }
    
    .no-scrollbar { 
        -ms-overflow-style: none; 
        scrollbar-width: none; 
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            'name' => 'Ruang Lari',
            'url' => url('/'),
            'logo' => asset('images/ruanglari_green.png'),
        ],
        [
            '@type' => 'WebSite',
            'name' => 'Ruang Lari',
            'url' => url('/'),
        ],
        [
            '@type' => 'WebPage',
            'name' => 'Ruang Lari | Kalender Event Lari, Komunitas, dan Tools Pelari Indonesia',
            'description' => 'Temukan event lari terbaru, kalender race, komunitas pelari, leaderboard, dan tools latihan dalam satu platform RuangLari.',
            'url' => url('/'),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') AOS.init({duration:800, once:true, offset:50});
    });

    (function () {
        var track = document.getElementById('heroFeaturedTrack');
        var dots = document.getElementById('heroFeaturedDots');
        var btnPrev = document.getElementById('heroFeaturedPrev');
        var btnNext = document.getElementById('heroFeaturedNext');
        if (!track) return;

        var slides = Array.prototype.slice.call(track.children || []);
        if (!slides.length) return;

        var prefersReduced = false;
        try {
            prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        } catch (e) {}

        function scrollToIndex(idx) {
            idx = Math.max(0, Math.min(slides.length - 1, idx));
            var el = slides[idx];
            if (!el) return;
            track.scrollTo({ left: el.offsetLeft, behavior: prefersReduced ? 'auto' : 'smooth' });
        }

        function getActiveIndex() {
            var left = track.scrollLeft;
            var best = 0;
            var bestDist = Infinity;
            for (var i = 0; i < slides.length; i++) {
                var dist = Math.abs(slides[i].offsetLeft - left);
                if (dist < bestDist) {
                    bestDist = dist;
                    best = i;
                }
            }
            return best;
        }

        function renderDots() {
            if (!dots) return;
            dots.innerHTML = '';
            for (var i = 0; i < slides.length; i++) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'w-2 h-2 rounded-full bg-slate-500/70 hover:bg-white transition';
                b.setAttribute('aria-label', 'Slide ' + (i + 1));
                (function (idx) {
                    b.addEventListener('click', function () {
                        scrollToIndex(idx);
                        stopAuto();
                    });
                })(i);
                dots.appendChild(b);
            }
        }

        function setActiveDot(idx) {
            if (!dots) return;
            var children = dots.children;
            for (var i = 0; i < children.length; i++) {
                children[i].className = i === idx
                    ? 'w-2 h-2 rounded-full bg-[#B8FF00] transition'
                    : 'w-2 h-2 rounded-full bg-slate-500/70 hover:bg-white transition';
            }
        }

        if (slides.length > 1) {
            renderDots();
            setActiveDot(0);
        }

        try {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var idx = slides.indexOf(entry.target);
                        if (idx >= 0) setActiveDot(idx);
                    }
                });
            }, { root: track, threshold: 0.6 });

            slides.forEach(function (s) { io.observe(s); });
        } catch (e) {
            track.addEventListener('scroll', function () {
                setActiveDot(getActiveIndex());
            }, { passive: true });
        }

        if (btnPrev) {
            btnPrev.addEventListener('click', function () {
                var i = getActiveIndex();
                scrollToIndex(i - 1);
                stopAuto();
            });
        }
        if (btnNext) {
            btnNext.addEventListener('click', function () {
                var i = getActiveIndex();
                scrollToIndex(i + 1);
                stopAuto();
            });
        }

        var timer = null;
        function startAuto() {
            if (prefersReduced) return;
            if (slides.length <= 1) return;
            if (timer) return;
            timer = setInterval(function () {
                var i = getActiveIndex();
                scrollToIndex((i + 1) % slides.length);
            }, 6500);
        }

        function stopAuto() {
            if (!timer) return;
            clearInterval(timer);
            timer = null;
        }

        track.addEventListener('pointerdown', stopAuto, { passive: true });
        track.addEventListener('mouseenter', stopAuto, { passive: true });
        track.addEventListener('mouseleave', startAuto, { passive: true });

        startAuto();
    })();

    async function loadLatestBlogs(){
        const c=document.getElementById('blogCards');
        if(!c)return;
        c.innerHTML='<div class="col-span-3 text-center text-slate-500 animate-pulse py-10">Memuat artikel...</div>';
        try{
            const r=await fetch('{{ route("api.blog.latest") }}');
            const p=await r.json();
            c.innerHTML='';
            if(!p || p.length===0){
                c.innerHTML='<div class="col-span-3 text-center text-slate-500 py-10 border border-dashed border-[#1F2D44] rounded-3xl bg-[#0E1A2D]">Belum ada artikel yang dipublikasikan.</div>';
                return;
            }
            p.forEach((post, i)=>{
                const l=post.url||'#';
                const t=post.title||'Tanpa judul';
                const img=post.image||"{{ asset('ruanglari.webp') }}";
                const date=new Date(post.date).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
                
                const card=document.createElement('div');
                card.className='group bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl overflow-hidden hover:border-[#B8FF00]/50 transition duration-300 flex flex-col h-full justify-between';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', i * 100);
                
                card.innerHTML=`
                    <div>
                        <a href="${l}" target="${l === '#' ? '_self' : '_blank'}" rel="noopener" class="block overflow-hidden relative aspect-video">
                            <img src="${img}" alt="${t.replace(/<[^>]*>/g,'')}" loading="lazy" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105" onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';" />
                            <div class="absolute inset-0 bg-[#08111F]/20 group-hover:bg-transparent transition"></div>
                        </a>
                        <div class="p-6">
                            <div class="text-xs text-[#B8FF00] font-bold mb-2 uppercase tracking-wider">${date}</div>
                            <h3 class="text-lg font-bold text-white mb-4 hover:text-[#B8FF00] transition line-clamp-2 leading-snug">
                                <a href="${l}" target="${l === '#' ? '_self' : '_blank'}" rel="noopener">
                                    ${t}
                                </a>
                            </h3>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-4 border-t border-[#1F2D44] flex items-center justify-between">
                        <a href="${l}" target="${l === '#' ? '_self' : '_blank'}" rel="noopener" class="text-xs text-[#94A3B8] group-hover:text-white font-bold uppercase tracking-wider transition flex items-center gap-2">
                            Baca Selengkapnya
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                `;
                c.appendChild(card);
            });
        }catch(e){
            c.innerHTML='<div class="col-span-3 text-center text-red-500 py-10 bg-[#0E1A2D] rounded-3xl border border-red-900/50">Gagal memuat artikel. Silakan coba lagi nanti.</div>';
        }
    }

    async function loadUpcomingEvents(){
        const c=document.getElementById('homeEvents');
        if(!c)return;
        try{
            const r=await fetch('{{ route("api.events.upcoming") }}');
            const events=await r.json();
            c.innerHTML='';
            
            if(!events||events.length===0){
                c.innerHTML='<div class="col-span-2 text-center text-[#94A3B8] py-16 border border-dashed border-[#1F2D44] rounded-3xl bg-[#0E1A2D]">Belum ada event mendatang.</div>';
                return;
            }
            
            events.forEach((ev, i)=>{
                const d=new Date(ev.date+'T'+(ev.time||'00:00'));
                const m=d.toLocaleString('id-ID',{month:'short'}).toUpperCase();
                const day=String(d.getDate()).padStart(2,'0');
                const year=d.getFullYear();
                
                const card=document.createElement('div');
                card.className='group bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl overflow-hidden hover:border-[#B8FF00]/50 transition duration-300 flex flex-col justify-between p-6 h-full relative';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', i * 100);
                
                // Construct distance badges
                let distanceBadges = '';
                if (ev.distances && ev.distances.length > 0) {
                    ev.distances.forEach(dist => {
                        distanceBadges += `<span class="bg-[#111F35] text-[#B8FF00] border border-[#1F2D44] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">${dist}</span>`;
                    });
                } else {
                    distanceBadges = `<span class="bg-[#111F35] text-[#94A3B8] border border-[#1F2D44] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Run</span>`;
                }

                card.innerHTML=`
                    <div>
                        <!-- Header: Date & Status -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-[#111F35] border border-[#1F2D44] rounded-2xl px-4 py-2 text-center flex flex-col justify-center min-w-[64px]">
                                    <span class="text-[10px] font-black text-[#B8FF00] uppercase tracking-wider">${m}</span>
                                    <span class="text-2xl font-black text-white leading-none">${day}</span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase">${year}</p>
                                    <p class="text-[10px] text-[#B8FF00] font-bold uppercase tracking-wider">Mendatang</p>
                                </div>
                            </div>
                            <!-- Category Badge -->
                            <div class="flex flex-wrap gap-1 max-w-[150px] justify-end">
                                ${distanceBadges}
                            </div>
                        </div>

                        <!-- Content -->
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-[#B8FF00] transition line-clamp-2 leading-snug">${ev.name}</h3>
                        
                        <div class="space-y-2 mb-6">
                            <div class="flex items-center gap-2 text-sm text-[#94A3B8]">
                                <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="truncate">${ev.location||'TBA'}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-[#94A3B8]">
                                <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>${ev.time||'TBA'} WIB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Action button -->
                    <div class="pt-4 border-t border-[#1F2D44] flex items-center justify-between">
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">${ev.is_eo ? 'Managed by EO' : 'RuangLari Listing'}</span>
                        <a href="${ev.url}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#111F35] text-white hover:bg-[#B8FF00] hover:text-[#08111F] font-bold text-xs uppercase tracking-wider transition duration-300">
                            Lihat Detail
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                `;
                c.appendChild(card);
            });
        }catch(e){
            c.innerHTML='<div class="col-span-2 text-center text-red-400 py-4">Gagal memuat event.</div>';
        }
    }

    document.addEventListener('DOMContentLoaded',()=>{
        loadLatestBlogs();
        loadUpcomingEvents();
    });
    // In case DOMContentLoaded already fired (script loaded late)
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        loadLatestBlogs();
        loadUpcomingEvents();
    }
</script>
@endpush
