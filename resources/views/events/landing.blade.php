@php
    $pageSuffix = request()->has('page') && request('page') > 1 ? ' - Halaman ' . request('page') : '';
    $title = 'Jadwal Lari 2026 Indonesia | Event 5K, 10K & Marathon' . $pageSuffix;
    $metaDescription = 'Temukan jadwal lari 2026 di Indonesia mulai dari fun run, 5K, 10K, half marathon, marathon, trail run, hingga virtual run. Cek tanggal, lokasi, kategori, dan link pendaftaran event lari terbaru di Ruang Lari.';
    
    $canonicalUrl = request()->url();
    if (request()->has('page') && request('page') > 1) {
        $canonicalUrl .= '?page=' . request('page');
    }
@endphp

@extends('layouts.pacerhub')

@section('title', $title)
@section('meta_title', $title)
@section('meta_description', $metaDescription)
@section('meta_keywords', 'jadwal lari 2026, submit event lari gratis, kalender event lari indonesia, event lari 5k 10k, marathon indonesia, publikasi event lari gratis, registration page event lari, ruang lari eo')
@section('canonical_url', $canonicalUrl)
@section('og_image', 'https://ruanglari.com/storage/blog/media/012cce42-9e25-41b8-9e03-dc3a177fd595.webp')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 bg-dark relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-12 relative z-10" data-aos="fade-down">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Hero Text -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tighter uppercase leading-none">
                    Jadwal Lari <span class="text-neon">2026</span> Indonesia
                </h1>
                <div class="text-slate-400 text-base md:text-lg space-y-4 leading-relaxed max-w-3xl mx-auto lg:mx-0">
                    <p>
                        Temukan jadwal lari 2026 di Indonesia dalam satu kalender lengkap. Ruang Lari menyajikan informasi event lari terbaru, mulai dari fun run, 5K, 10K, half marathon, marathon, trail run, ultra run, hingga virtual run. Setiap event dilengkapi dengan tanggal pelaksanaan, lokasi, kategori jarak, status pendaftaran, dan tautan menuju halaman resmi pendaftaran.
                    </p>
                    <p class="text-xs md:text-sm text-slate-500">
                        Punya event lari sendiri? Anda dapat memilih fitur <strong class="text-white">Submit Event Lari Gratis</strong> atau membuat <strong class="text-white">Registration Page Event Lari</strong> otomatis tanpa biaya awal di Ruang Lari.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-3">
                    <button type="button" id="btn-open-submit-event" class="px-8 py-3 rounded-full bg-neon text-dark font-extrabold hover:bg-lime-300 transition-all inline-flex items-center justify-center gap-2 shadow-lg shadow-neon/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Submit Event Lari Gratis
                    </button>
                    <a href="{{ route('eo.landing') }}" class="px-6 py-3 rounded-full bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider transition-all inline-flex items-center justify-center gap-1.5 border border-slate-700">
                        Buat Registration Page EO
                    </a>
                </div>
            </div>
            
            <!-- Hero Image/Banner / Slider Featured Event -->
            <div class="lg:col-span-5 relative group" data-aos="zoom-in" data-aos-delay="100">
                <div class="absolute -inset-1.5 bg-gradient-to-r from-neon to-lime-500 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                
                @if(isset($featuredEvents) && $featuredEvents->isNotEmpty())
                    <!-- Slider Container -->
                    <div 
                        class="relative bg-slate-900 border border-slate-700/50 rounded-2xl overflow-hidden shadow-2xl"
                        x-data="{ 
                            activeIndex: 0, 
                            total: {{ $featuredEvents->count() }}, 
                            timer: null,
                            touchStartX: 0,
                            startTimer() {
                                this.timer = setInterval(() => {
                                    this.activeIndex = (this.activeIndex + 1) % this.total;
                                }, 5000);
                            },
                            stopTimer() {
                                clearInterval(this.timer);
                            }
                        }"
                        x-init="startTimer()"
                        @mouseenter="stopTimer()"
                        @mouseleave="startTimer()"
                        @touchstart="touchStartX = $event.touches[0].clientX"
                        @touchend="
                            let diffX = $event.changedTouches[0].clientX - touchStartX;
                            if (Math.abs(diffX) > 35) {
                                if (diffX < 0) { activeIndex = (activeIndex + 1) % total; }
                                else { activeIndex = (activeIndex - 1 + total) % total; }
                            }
                        "
                    >
                        <!-- Slides Wrapper -->
                        <div class="flex transition-transform duration-700 ease-out" :style="`transform: translateX(-${activeIndex * 100}%)`">
                            @foreach($featuredEvents as $event)
                                <a href="{{ $event->public_url }}" class="block w-full flex-shrink-0 relative aspect-video overflow-hidden group/slide">
                                    <!-- Event Image -->
                                    <img 
                                        src="{{ $event->getHeroImageUrl() ?: asset('images/hero/jadwal-lari.webp') }}" 
                                        alt="{{ $event->name }}" 
                                        class="w-full h-full object-cover transform group-hover/slide:scale-105 transition-transform duration-700 ease-out"
                                        width="800"
                                        height="450"
                                        loading="eager"
                                    >
                                    
                                    <!-- Gradients for readability -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/25 to-transparent opacity-10"></div>
                                    
                                    <!-- Content Overlay -->
                                    <div class="absolute bottom-0 inset-x-0 p-4 sm:p-5 flex flex-col justify-end bg-gradient-to-t from-slate-950/90 via-slate-950/50 to-transparent">
                                        <!-- Badges -->
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="px-2 py-0.5 text-[9px] font-extrabold tracking-wider text-neon bg-neon/10 border border-neon/30 rounded uppercase">
                                                {{ $event->is_featured ? 'Featured Event' : 'Event Pilihan' }}
                                            </span>
                                            @if($event->raceType)
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                                                    {{ $event->raceType->name }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Title -->
                                        <h3 class="text-white text-base sm:text-lg font-black tracking-tight line-clamp-1 uppercase group-hover/slide:text-neon transition-colors duration-300">
                                            {{ $event->name }}
                                        </h3>

                                        <!-- Meta details (Date & Location) -->
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-slate-350 text-xs mt-1">
                                            <div class="flex items-center gap-1 font-semibold">
                                                <svg class="w-3.5 h-3.5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>{{ $event->start_at ? $event->start_at->translatedFormat('d M Y') : '' }}</span>
                                            </div>
                                            <span class="text-slate-650 hidden sm:inline">•</span>
                                            <div class="flex items-center gap-1 truncate max-w-[200px] text-slate-400">
                                                <svg class="w-3.5 h-3.5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span class="truncate">{{ $event->city ? $event->city->name : $event->location_name }}</span>
                                            </div>
                                        </div>

                                        <!-- Distances/Categories Badges -->
                                        @if($event->distances->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 mt-2.5">
                                                @foreach($event->distances as $distance)
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-950/80 border border-slate-800 text-[8px] font-black text-slate-300 uppercase tracking-tight">
                                                        {{ $distance->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <!-- Arrow Navigation Controls -->
                        <button 
                            type="button" 
                            @click.prevent="activeIndex = (activeIndex - 1 + total) % total" 
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-950/70 hover:bg-neon hover:text-dark text-white border border-slate-800/50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 z-20 focus:outline-none"
                            aria-label="Previous event"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button 
                            type="button" 
                            @click.prevent="activeIndex = (activeIndex + 1) % total" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-950/70 hover:bg-neon hover:text-dark text-white border border-slate-800/50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 z-20 focus:outline-none"
                            aria-label="Next event"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Dots Progress Indicators -->
                        <div class="absolute bottom-4 right-4 flex gap-1.5 z-20">
                            <template x-for="(event, idx) in total" :key="idx">
                                <button 
                                    type="button" 
                                    @click.prevent="activeIndex = idx" 
                                    class="h-1.5 rounded-full transition-all duration-300 focus:outline-none"
                                    :class="activeIndex === idx ? 'w-5 bg-neon' : 'w-1.5 bg-slate-500 hover:bg-slate-400'"
                                    :aria-label="'Go to slide ' + (idx + 1)"
                                ></button>
                            </template>
                        </div>
                    </div>
                @else
                    <!-- Fallback: Original static hero image -->
                    <div class="relative bg-slate-900 border border-slate-700/50 rounded-2xl overflow-hidden shadow-2xl">
                        <img 
                            src="{{ asset('images/hero/jadwal-lari.webp') }}" 
                            alt="Jadwal Lari 2026 Indonesia - Kalender Event Lari Ruang Lari" 
                            class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out"
                            width="800"
                            height="450"
                            loading="eager"
                            fetchpriority="high"
                        >
                        <!-- Glassmorphism overlay card at the bottom of the image -->
                        <div class="absolute bottom-0 inset-x-0 bg-slate-950/70 backdrop-blur-md border-t border-slate-800/80 p-4 flex justify-between items-center">
                            <div class="text-left">
                                <p class="text-[10px] font-bold text-neon uppercase tracking-widest">Update Berkala</p>
                                <p class="text-xs font-black text-white tracking-tight uppercase">Kalender Lari Indonesia</p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-[9px] font-bold text-slate-300 uppercase">
                                2026 Edition
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    <!-- EO Callout Banner: Submit Event Lari Gratis & Registration Page -->
    <div class="max-w-7xl mx-auto mt-12 mb-8 relative z-10" data-aos="fade-up">
        <div class="bg-gradient-to-r from-[#0E1A2D] via-[#111F35] to-[#0E1A2D] border border-neon/30 rounded-3xl p-6 md:p-7 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-1.5 text-center md:text-left max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neon/10 text-neon text-xs font-black uppercase tracking-wider">
                    Event Organizer (EO) &amp; Komunitas Lari
                </div>
                <h3 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    Punya Event Lari?<br/><span class="text-neon">Publikasikan Event &amp; Buat Registration Page Gratis!</span>
                </h3>
                <p class="text-slate-300 text-xs md:text-sm leading-relaxed">
                    Jangkau puluhan ribu pelari di seluruh Indonesia. Dapatkan fitur <strong class="text-white">Submit Event Lari Gratis</strong>, landing page pendaftaran otomatis, Notifikasi WA, E-Ticket QR, &amp; pembayaran online tanpa biaya awal.
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-2.5 shrink-0 w-full md:w-auto">
                <a href="{{ route('eo.landing') }}" class="px-6 py-3 rounded-xl bg-neon hover:bg-lime-300 text-dark font-black text-xs uppercase tracking-wider transition-all transform hover:scale-105 text-center shadow-lg shadow-neon/10">
                    Buat Registration Page
                </a>
                <button type="button" onclick="document.getElementById('btn-open-submit-event').click()" class="px-6 py-3 rounded-xl bg-dark hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 text-center">
                    Submit Event Gratis
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive Explorer Map Section (Minimizable, Marker Clustering, RuangLari Palette) -->
    <div id="events-explorer-map-section" class="max-w-7xl mx-auto mb-8 relative z-10" data-aos="fade-up">
        <div class="bg-[#0c121e] border border-slate-800 rounded-2xl overflow-hidden shadow-2xl transition-all duration-300">
            <!-- Header bar with Minimize/Expand toggle -->
            <div class="px-4 sm:px-5 py-3.5 bg-[#090D16] border-b border-slate-800 flex items-center justify-between gap-3 cursor-pointer select-none" id="btn-toggle-events-map">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-neon shadow-[0_0_10px_#ccff00] animate-pulse shrink-0"></span>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm sm:text-base font-black text-white tracking-tight uppercase">Peta Sebaran Event Lari Indonesia</h2>
                            <span id="events-map-count" class="px-2 py-0.5 rounded-md bg-neon/15 text-neon border border-neon/30 text-[11px] font-mono font-bold">
                                {{ count($mapEvents ?? []) }} Event Terdaftar
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 hidden sm:block">Jelajahi lokasi event lari di seluruh Indonesia. Klik marker untuk melihat jadwal & detail pendaftaran.</p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0" onclick="event.stopPropagation()">
                    <!-- Map Theme / Layer Switcher Dropdown -->
                    <div class="relative inline-block z-50" id="events-map-layer-dropdown-wrap">
                        <button type="button" id="btn-toggle-events-map-layer" onclick="toggleEventsMapLayerMenu()" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                            <i class="fa-solid fa-layer-group text-[11px] text-neon"></i>
                            <span id="label-events-active-layer" class="hidden xs:inline">Voyager</span>
                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-400"></i>
                        </button>
                        <div id="events-map-layer-menu" class="hidden absolute right-0 top-full mt-1.5 bg-[#0c121e] border border-slate-700 rounded-xl shadow-2xl py-1.5 z-[99999] min-w-[150px] text-xs divide-y divide-slate-800" style="background-color: #0c121e !important; opacity: 1 !important;">
                            <button type="button" onclick="setEventsMapLayer('voyager')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>Voyager</span>
                                <span class="events-layer-check-voyager text-neon text-[11px] font-bold"><i class="fa-solid fa-check"></i></span>
                            </button>
                            <button type="button" onclick="setEventsMapLayer('osm')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>OSM Street</span>
                                <span class="events-layer-check-osm text-neon text-[11px] font-bold hidden"><i class="fa-solid fa-check"></i></span>
                            </button>
                            <button type="button" onclick="setEventsMapLayer('dark')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>Dark Tactical</span>
                                <span class="events-layer-check-dark text-neon text-[11px] font-bold hidden"><i class="fa-solid fa-check"></i></span>
                            </button>
                            <button type="button" onclick="setEventsMapLayer('satellite')" class="w-full px-3.5 py-2 text-left text-slate-200 hover:bg-slate-800 hover:text-white flex items-center justify-between cursor-pointer transition">
                                <span>Satelit Esri</span>
                                <span class="events-layer-check-satellite text-neon text-[11px] font-bold hidden"><i class="fa-solid fa-check"></i></span>
                            </button>
                        </div>
                    </div>

                    <button type="button" id="btn-events-map-locate-me" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm" title="Deteksi Lokasi Saya">
                        <i class="fa-solid fa-location-crosshairs text-[11px] text-neon"></i>
                        <span class="hidden sm:inline">Lokasi Saya</span>
                    </button>
                    <button type="button" id="btn-events-map-recenter" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm" title="Pusatkan Peta ke Seluruh Event">
                        <i class="fa-solid fa-expand text-[11px]"></i>
                        <span class="hidden sm:inline">Pusatkan</span>
                    </button>
                    <button type="button" id="btn-events-map-minimize-toggle" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm" title="Sembunyikan / Tampilkan Peta">
                        <span id="label-events-map-toggle" class="text-xs font-medium hidden xs:inline">Sembunyikan</span>
                        <i id="icon-events-map-toggle" class="fa-solid fa-chevron-up transition-transform duration-300 text-[10px]"></i>
                    </button>
                </div>
            </div>

            <!-- Map Container Wrapper -->
            <div id="events-map-collapse-wrap" class="relative transition-all duration-300">
                <div id="events-explorer-map" class="w-full h-[360px] sm:h-[420px] md:h-[480px] z-0 bg-[#090D16]"></div>

                <!-- Interactive Map Legend & Quick Type Filter Overlay -->
                <div id="events-map-type-filter-overlay" class="absolute bottom-3 left-3 z-[400] bg-[#0c121e] border border-slate-700 rounded-xl p-1.5 text-xs text-slate-300 shadow-2xl pointer-events-auto flex items-center gap-1.5 overflow-x-auto max-w-[calc(100%-24px)] no-scrollbar" style="background-color: #0c121e !important;">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider px-1 shrink-0">Filter:</span>
                    <button type="button" onclick="setEventsMapTypeFilter('')" class="btn-event-map-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border bg-slate-800 text-white font-bold border-slate-600 shrink-0" data-map-type="">
                        Semua
                    </button>
                    @if(isset($raceTypes) && $raceTypes->isNotEmpty())
                        @foreach($raceTypes as $rType)
                            @php
                                $isTrail = str_contains(strtolower($rType->name), 'trail');
                                $isUltra = str_contains(strtolower($rType->name), 'ultra') || str_contains(strtolower($rType->name), 'marathon');
                                $dotColor = $isTrail ? 'bg-emerald-400' : ($isUltra ? 'bg-orange-500' : 'bg-neon');
                            @endphp
                            <button type="button" onclick="setEventsMapTypeFilter('{{ $rType->id }}')" class="btn-event-map-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border text-slate-300 hover:text-white border-transparent shrink-0 flex items-center gap-1.5" data-map-type="{{ $rType->id }}">
                                <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                <span>{{ $rType->name }}</span>
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="max-w-7xl mx-auto mb-8 relative z-10" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-6 shadow-xl">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Hidden fields for homepage filters -->
                @if(request('city'))
                    <input type="hidden" name="city" value="{{ request('city') }}">
                @endif
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                
                <!-- Search -->
                <div class="lg:col-span-1">
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Cari Event</label>
                        <button type="button" id="mobile-filter-toggle" class="md:hidden text-neon text-xs font-bold flex items-center gap-1 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            Filter
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama event atau lokasi..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pl-10 text-white focus:outline-none focus:border-neon transition-colors">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <!-- Month & Year -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Waktu</label>
                    <div class="flex gap-2">
                        @php
                            $selectedMonth = request('month');
                            $selectedYear = request('year');
                            
                            // Map homepage month (Y-m) if present
                            if (request('month') && preg_match('/^\d{4}-\d{2}$/', request('month'))) {
                                $parts = explode('-', request('month'));
                                $selectedYear = $parts[0];
                                $selectedMonth = (int)$parts[1];
                            }
                        @endphp
                        <select name="month" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($selectedMonth == $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                        <select name="year" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Tahun</option>
                            @foreach(range(date('Y'), date('Y') + 1) as $y)
                                <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- City -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi</label>
                    <select name="city_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Race Type -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Lomba</label>
                    <select name="race_type_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Jenis</option>
                        @foreach($raceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Distance -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori Jarak</label>
                    <select name="race_distance_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Jarak</option>
                        @foreach($raceDistances as $distance)
                            <option value="{{ $distance->id }}">{{ $distance->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Horizontal Scroll Quick Filters -->
    <div class="max-w-7xl mx-auto mb-6 relative z-10 overflow-x-auto no-scrollbar py-2">
        <div class="flex gap-2 min-w-max pb-1">
            <button type="button" class="quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-neon text-dark border-neon shadow-sm shadow-neon/10" data-filter-type="distance" data-value="">
                Semua Jarak
            </button>
            @foreach($raceDistances as $distance)
                <button type="button" class="quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-600 hover:text-white" data-filter-type="distance" data-value="{{ $distance->id }}">
                    {{ $distance->name }}
                </button>
            @endforeach
            
            <div class="h-6 w-px bg-slate-800 self-center mx-2"></div>
            
            <button type="button" class="quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-600 hover:text-white" data-filter-type="type" data-value="">
                Semua Jenis
            </button>
            @foreach($raceTypes as $type)
                <button type="button" class="quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-600 hover:text-white" data-filter-type="type" data-value="{{ $type->id }}">
                    {{ $type->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Event List -->
    <div class="max-w-7xl mx-auto relative z-10">
        <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter mb-6 uppercase">
            Kalender Event Lari Terbaru
        </h2>

        <div id="events-container" class="space-y-4">
            @include('events.partials.list', ['events' => $events])
        </div>
        
        <div id="pagination-container" class="mt-8">
            {{ $events->links() }}
        </div>

        <!-- Loading State -->
        <div id="loading-indicator" class="hidden py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-neon"></div>
            <p class="mt-2 text-slate-400 text-sm">Memuat jadwal...</p>
        </div>
    </div>

    <!-- SEO Content Sections -->
    <div class="max-w-7xl mx-auto mt-16 pt-16 border-t border-slate-800 relative z-10 space-y-12">
        
        <!-- Jadwal Lari Berdasarkan Bulan -->
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter uppercase">
                Jadwal Lari Berdasarkan Bulan
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @php
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp
                @foreach($months as $num => $monthName)
                    @php
                        $mVal = sprintf('%04d-%02d', 2026, $num);
                    @endphp
                    <a href="{{ route('events.index') }}?month={{ $mVal }}" class="p-4 bg-slate-900 border border-slate-800 hover:border-neon hover:text-neon rounded-xl text-center transition group">
                        <h3 class="font-bold text-slate-300 group-hover:text-neon text-sm transition">Jadwal Lari {{ $monthName }} 2026</h3>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Jadwal Lari Berdasarkan Kota -->
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter uppercase">
                Jadwal Lari Berdasarkan Kota
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @php
                    $seoCities = [
                        'jakarta' => 'Jakarta',
                        'bandung' => 'Bandung',
                        'surabaya' => 'Surabaya',
                        'yogyakarta' => 'Yogyakarta',
                        'malang' => 'Malang',
                        'bali' => 'Bali',
                        'semarang' => 'Semarang',
                        'bogor' => 'Bogor',
                        'makassar' => 'Makassar',
                        'medan' => 'Medan',
                        'balikpapan' => 'Balikpapan',
                        'batam' => 'Batam'
                    ];
                @endphp
                @foreach($seoCities as $slug => $cityName)
                    <a href="/event-lari-di-{{ $slug }}" class="p-4 bg-slate-900 border border-slate-800 hover:border-neon hover:text-neon rounded-xl text-center transition group">
                        <h3 class="font-bold text-slate-300 group-hover:text-neon text-sm transition">
                            @if($slug == 'surabaya')
                                Jadwal lari di Surabaya 2026
                            @elseif($slug == 'jakarta' || $slug == 'semarang' || $slug == 'bogor' || $slug == 'makassar' || $slug == 'medan' || $slug == 'balikpapan' || $slug == 'batam' || $slug == 'yogyakarta')
                                Jadwal lari di {{ $cityName }}
                            @else
                                Event lari di {{ $cityName }}
                            @endif
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Jadwal Lari Berdasarkan Kategori -->
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter uppercase">
                Jadwal Lari Berdasarkan Kategori
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @php
                    $seoCategories = [
                        '5k' => 'Jadwal lari 5K',
                        '10k' => 'Jadwal lari 10K',
                        'half-marathon' => 'Jadwal half marathon',
                        'marathon' => 'Jadwal marathon',
                        'trail-run' => 'Jadwal trail run',
                        'fun-run' => 'Jadwal fun run',
                        'virtual-run' => 'Jadwal virtual run',
                        'ultra-marathon' => 'Jadwal ultra marathon'
                    ];
                @endphp
                @foreach($seoCategories as $slug => $label)
                    <a href="/jadwal-{{ $slug }}" class="p-4 bg-slate-900 border border-slate-800 hover:border-neon hover:text-neon rounded-xl text-center transition group">
                        <h3 class="font-bold text-slate-300 group-hover:text-neon text-sm transition">{{ $label }}</h3>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Cara Memilih Event Lari -->
        <div class="bg-card/40 border border-slate-800/80 rounded-2xl p-6 md:p-8 space-y-4">
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter uppercase">
                Cara Memilih Event Lari yang Tepat
            </h2>
            <div class="text-slate-300 space-y-4 leading-relaxed">
                <p>Sebelum mendaftar event lari, pelari sebaiknya menyesuaikan pilihan lomba dengan tingkat kebugaran, target latihan, jarak tempuh, lokasi, dan waktu persiapan. Pelari pemula dapat memulai dari fun run atau 5K, sedangkan pelari yang sudah terbiasa dapat memilih 10K, half marathon, marathon, atau trail run.</p>
                <p>Perhatikan juga informasi teknis seperti cut-off time, rute, elevasi, fasilitas race pack, medali, hidrasi, dan reputasi penyelenggara. Dengan memilih event yang sesuai, pengalaman mengikuti lomba akan lebih aman, nyaman, dan menyenangkan.</p>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tighter uppercase">
                Pertanyaan Umum tentang Jadwal Lari
            </h2>
            <div class="space-y-4" x-data="{ active: null }">
                @php
                    $faqs = [
                        [
                            'q' => 'Apa itu jadwal lari?',
                            'a' => 'Jadwal lari adalah daftar event lari yang disusun berdasarkan tanggal, lokasi, kategori jarak, dan jenis lomba seperti fun run, 5K, 10K, half marathon, marathon, trail run, dan virtual run.'
                        ],
                        [
                            'q' => 'Bagaimana cara mencari event lari terdekat?',
                            'a' => 'Gunakan filter kota, bulan, dan kategori jarak pada kalender Ruang Lari untuk menemukan event lari yang sesuai dengan lokasi dan target latihan Anda.'
                        ],
                        [
                            'q' => 'Apa saja kategori event lari yang tersedia?',
                            'a' => 'Kategori event lari yang umum tersedia meliputi 5K, 10K, half marathon, marathon, ultra marathon, trail run, fun run, charity run, dan virtual run.'
                        ],
                        [
                            'q' => 'Apakah jadwal lari di Ruang Lari diperbarui?',
                            'a' => 'Ya, kalender event lari di Ruang Lari diperbarui secara berkala berdasarkan informasi terbaru dari penyelenggara event dan kanal pendaftaran resmi.'
                        ],
                        [
                            'q' => 'Bagaimana cara mendaftarkan event lari ke Ruang Lari?',
                            'a' => 'Penyelenggara dapat menghubungi tim Ruang Lari untuk mengirimkan informasi event, seperti nama event, tanggal, lokasi, kategori jarak, poster, dan tautan pendaftaran resmi.'
                        ]
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                    <div class="border border-slate-800 rounded-2xl bg-slate-900/50 overflow-hidden">
                        <button @click="active = active === {{ $i }} ? null : {{ $i }}" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-slate-800/40 transition">
                            <span class="font-bold text-white text-base md:text-lg">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 text-neon transition-transform" :class="active === {{ $i }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="active === {{ $i }}" x-collapse x-cloak class="px-6 pb-5 pt-2 text-slate-400 border-t border-slate-800/50 leading-relaxed text-sm md:text-base">
                            <p>{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

<div id="submit-event-modal" class="fixed inset-0 z-[9999] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md"></div>
    <div class="relative min-h-screen w-full flex items-center justify-center p-3 sm:p-6">
        <div class="w-full max-w-5xl bg-[#0B1526] border border-slate-700/70 rounded-3xl shadow-2xl overflow-hidden flex flex-col my-auto max-h-[92vh] text-slate-200">
            <!-- Modal Header -->
            <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-slate-800 bg-[#08111F] flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3.5">
                    
                    <div>
                        <div class="text-[10px] sm:text-xs font-mono font-bold text-neon uppercase tracking-widest">Submit Event Ruang Lari</div>
                        <h3 class="text-lg sm:text-2xl font-black text-white tracking-tight">AJUKAN EVENT LARI BARU</h3>
                    </div>
                </div>
                <button type="button" id="btn-close-submit-event" class="w-9 h-9 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div id="submit-event-alert" class="hidden px-4 sm:px-8 pt-4"></div>

            <!-- Modal Form Body (Scrollable 2-Column Grid) -->
            <form id="submit-event-form" class="flex-1 overflow-y-auto px-4 sm:px-8 py-5 sm:py-6 space-y-6 custom-scrollbar">
                <input type="text" name="website" id="submit_event_website" class="hidden" tabindex="-1" autocomplete="off">
                <input type="hidden" name="started_at" id="submit_event_started_at" value="0">
                <input type="hidden" name="otp_id" id="submit_event_otp_id" value="">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    
                    <!-- LEFT COLUMN: Event Info & Media -->
                    <div class="space-y-6">
                        <!-- Section: Detail Event -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                <i class="fas fa-info-circle text-neon text-sm"></i>
                                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Informasi Utama Event</h4>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Event <span class="text-neon">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                                        <i class="fas fa-flag"></i>
                                    </div>
                                    <input type="text" name="event_name" id="submit_event_name" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Contoh: Jakarta City Run 2026" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Event <span class="text-neon">*</span></label>
                                    <input type="date" name="event_date" id="submit_event_date" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all" required>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jam Mulai</label>
                                    <input type="time" value="05:00" name="start_time" id="submit_event_time" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Lomba</label>
                                <select name="race_type_id" id="submit_event_race_type_id" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                    <option value="">Pilih Jenis Lomba</option>
                                    @foreach($raceTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Section: Dropzone Upload Banner -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-image text-neon text-sm"></i>
                                    <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Banner Event</h4>
                                </div>
                                <span class="text-[10px] font-mono text-slate-500">Maksimal 2MB (Landscape)</span>
                            </div>

                            <div id="banner-dropzone" class="border-2 border-dashed border-slate-700/80 hover:border-neon bg-slate-950/80 rounded-2xl p-6 text-center cursor-pointer transition-all duration-300 group relative">
                                <input type="file" name="banner" id="submit_event_banner" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden">
                                
                                <div id="banner-dropzone-default">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700 text-neon flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform shadow-md">
                                        <i class="fas fa-cloud-upload-alt text-xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-white">Drag & drop banner event di sini</p>
                                    <p class="text-xs text-slate-400 mt-1">atau <span class="text-neon font-semibold underline">klik untuk menelusuri file</span></p>
                                </div>

                                <div id="banner-dropzone-preview" class="hidden">
                                    <img id="banner-preview-img" src="" alt="Banner Preview" class="max-h-40 rounded-xl mx-auto border border-slate-700 object-cover shadow-lg">
                                    <span id="banner-filename" class="text-xs font-mono text-neon block mt-2 font-bold truncate"></span>
                                    <button type="button" id="btn-remove-banner" class="mt-2 px-3 py-1 rounded-lg bg-red-950/60 border border-red-500/30 text-red-300 hover:bg-red-900/60 text-xs font-bold transition-all inline-flex items-center gap-1.5">
                                        <i class="fas fa-trash-alt"></i> Ganti Banner
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Kategori Jarak Checkboxes -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-ruler-horizontal text-neon text-sm"></i>
                                    <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Kategori Jarak Lomba</h4>
                                </div>
                                <span class="text-[10px] font-mono text-slate-500">Pilih satu / lebih</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($raceDistances as $distance)
                                    <label class="cursor-pointer flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-800 bg-slate-950/90 hover:border-slate-700 text-xs font-bold text-slate-300 transition-all select-none has-[:checked]:border-neon/60 has-[:checked]:bg-neon/10 has-[:checked]:text-neon">
                                        <input type="checkbox" name="race_distance_ids[]" value="{{ $distance->id }}" class="hidden race-distance-cb">
                                        <span class="w-4 h-4 rounded border border-slate-700 flex items-center justify-center text-[9px] font-black text-dark check-box transition-all">
                                            <i class="fas fa-check opacity-0 check-mark"></i>
                                        </span>
                                        <span class="truncate">{{ $distance->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="pt-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tambah Jarak Custom (jika belum ada)</label>
                                <input type="text" name="custom_distances" id="submit_event_custom_distances" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-neon placeholder-slate-600" placeholder="Contoh: 7K, 100K, 50 mil (pisahkan koma)">
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Location Map, Links & Contact -->
                    <div class="space-y-6">
                        <!-- Section: Geolocation & Map -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-3.5">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marked-alt text-neon text-sm"></i>
                                    <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Lokasi Event</h4>
                                </div>
                                <button type="button" id="btn-geolocation" class="px-3 py-1 rounded-xl bg-neon/15 hover:bg-neon/25 text-neon border border-neon/30 text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fas fa-crosshairs"></i> Lokasi Saya
                                </button>
                            </div>

                            <!-- Search Location Input on Top of Map -->
                            <div class="relative">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                                        <i class="fas fa-search" id="map-search-icon"></i>
                                    </div>
                                    <input type="text" id="submit_map_search_input" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-9 py-2.5 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-neon transition-all" placeholder="Cari nama tempat, gedung, atau venue..." autocomplete="off">
                                    <button type="button" id="btn-clear-map-search" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-white text-xs hidden" title="Hapus pencarian">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <!-- Autocomplete Results Dropdown -->
                                <div id="map-search-results" class="absolute left-0 right-0 top-full mt-1.5 bg-black border border-slate-700 rounded-xl shadow-2xl z-50 max-h-52 overflow-y-auto hidden divide-y divide-slate-800"></div>
                            </div>

                            <!-- Leaflet Map Container -->
                            <div class="relative">
                                <div id="event-map" class="w-full h-44 rounded-xl border border-slate-700/80 bg-slate-950 z-0"></div>
                                <div id="map-geocoding-status" class="absolute bottom-2 left-2 bg-[#0c121e] text-[10px] font-mono text-neon px-2.5 py-1 rounded-lg border border-slate-800 z-10 empty:hidden"></div>
                            </div>
                            <p class="text-[11px] text-slate-400">Ketik pencarian lokasi di atas atau klik langsung pada peta untuk mengisi otomatis kota dan alamat.</p>

                            <div class="space-y-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota / Kabupaten</label>
                                    <select name="city_id" id="submit_event_city_id" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                        <option value="">Pilih Kota</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lokasi / Venue <span class="text-neon">*</span></label>
                                    <input type="text" name="location_name" id="submit_event_location" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Contoh: Gelora Bung Karno" required>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</label>
                                    <input type="text" name="location_address" id="submit_event_address" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Alamat / titik kumpul">
                                </div>
                            </div>
                        </div>

                        <!-- Section: Link Pendaftaran & Sosmed -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                <i class="fas fa-link text-neon text-sm"></i>
                                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Tautan & Media Sosial</h4>
                            </div>

                            <div class="space-y-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Pendaftaran Resmi</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs font-mono">
                                            <i class="fas fa-link"></i>
                                        </div>
                                        <input type="text" inputmode="url" name="registration_link" id="submit_event_registration_link" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all placeholder:text-slate-500" placeholder="https://... atau website pendaftaran">
                                    </div>
                                    <p class="text-[11px] text-slate-500">Otomatis ditambahkan https:// jika belum ada.</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Media Sosial / Instagram</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                                            <i class="fab fa-instagram"></i>
                                        </div>
                                        <input type="text" inputmode="url" name="social_media_link" id="submit_event_social_media_link" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all placeholder:text-slate-500" placeholder="https://instagram.com/... atau @username">
                                    </div>
                                    <p class="text-[11px] text-slate-500">Bisa isi username (@akun) atau link Instagram/sosmed.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Penyelenggara, Email & OTP Verification -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                <i class="fas fa-user-shield text-neon text-sm"></i>
                                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Kontak & Verifikasi Email</h4>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama EO / Penyelenggara</label>
                                    <input type="text" name="organizer_name" id="submit_event_organizer_name" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all" placeholder="Komunitas / EO">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kontak Penyelenggara</label>
                                    <input type="text" name="organizer_contact" id="submit_event_organizer_contact" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all" placeholder="WA / Email Kontak">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kamu</label>
                                    <input type="text" name="contributor_name" id="submit_event_contributor_name" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all" placeholder="Nama Pengaju">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email Kamu <span class="text-neon">*</span></label>
                                    <input type="email" name="contributor_email" id="submit_event_contributor_email" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all" placeholder="email@kamu.com" required>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan Tambahan</label>
                                <textarea name="notes" id="submit_event_notes" rows="2" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition-all" placeholder="Info kuota, syarat pendaftaran, dll"></textarea>
                            </div>

                            <!-- OTP Box -->
                            <div class="p-3.5 sm:p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Verifikasi OTP Email <span class="text-neon">*</span></label>
                                    <span class="text-[10px] text-slate-500 font-mono whitespace-nowrap">Kode 6 digit</span>
                                </div>
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                    <div class="relative flex-1 min-w-0">
                                        <input type="text" inputmode="numeric" maxlength="6" name="otp_code" id="submit_event_otp_code" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 sm:px-4 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-neon font-mono tracking-widest text-center placeholder:text-xs placeholder:tracking-normal placeholder:font-sans placeholder:text-slate-500 transition-all" placeholder="Kode OTP (6 digit)">
                                    </div>
                                    <button type="button" id="btn-submit-event-send-otp" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 active:bg-slate-600 border border-slate-700 text-white font-bold text-xs transition-all whitespace-nowrap text-center flex items-center justify-center shrink-0 min-h-[42px] shadow-sm cursor-pointer">
                                        Kirim OTP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="px-4 sm:px-8 py-3.5 sm:py-4 border-t border-slate-800 bg-[#08111F] flex flex-col sm:flex-row justify-between items-center gap-3 shrink-0">
                <div class="text-xs text-slate-400 font-mono flex items-center gap-1.5">
                    <i class="fas fa-shield-alt text-neon"></i>
                    <span>Verifikasi OTP wajib sebelum pengajuan event.</span>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button type="button" id="btn-submit-event-cancel" class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-bold hover:bg-slate-700 transition text-sm">Batal</button>
                    <button type="button" id="btn-submit-event-submit" class="flex-1 sm:flex-initial px-6 py-2.5 rounded-xl bg-neon text-dark font-extrabold hover:bg-lime-300 transition text-sm shadow-lg shadow-neon/20">Submit Event</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.6);
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(51, 65, 85, 0.8);
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #B8FF00;
    }
    .leaflet-control-attribution, .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo {
        display: none !important;
    }
    /* Marker Cluster Styling */
    .events-cluster-wrapper {
        background: transparent !important;
        border: none !important;
    }
    .events-map-cluster {
        width: 100%;
        height: 100%;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-family: monospace;
        font-size: 12px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.6);
        border: 2.5px solid #0c121e;
        transition: transform 0.15s ease;
    }
    .events-map-cluster:hover {
        transform: scale(1.1);
    }
    .events-cluster-small {
        background: #ccff00;
        color: #020617;
    }
    .events-cluster-medium {
        background: #38bdf8;
        color: #020617;
    }
    .events-cluster-large {
        background: #f97316;
        color: #ffffff;
    }

    /* Custom Leaflet Event Popup Styling */
    .events-custom-leaflet-popup .leaflet-popup-content-wrapper {
        background: #0c121e !important;
        color: #f1f5f9 !important;
        border-radius: 1rem !important;
        border: 1px solid #334155 !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8) !important;
        padding: 0 !important;
        overflow: hidden;
    }
    .events-custom-leaflet-popup .leaflet-popup-content {
        margin: 0 !important;
        line-height: 1.4 !important;
        min-width: 250px;
        max-width: 290px;
    }
    .events-custom-leaflet-popup .leaflet-popup-tip {
        background: #0c121e !important;
        border: 1px solid #334155 !important;
    }
    .events-custom-leaflet-popup a.leaflet-popup-close-button {
        color: #ffffff !important;
        background: rgba(12, 18, 30, 0.8) !important;
        border-radius: 9999px !important;
        width: 24px !important;
        height: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        top: 8px !important;
        right: 8px !important;
        z-index: 20 !important;
        border: 1px solid #334155 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    window.initialMapEvents = {!! json_encode($mapEvents ?? []) !!};

    function getV3Token(action, callback) {
        if (window.loadRecaptcha) {
            window.loadRecaptcha(action).then(function(token) {
                callback(token || '');
            }).catch(function() {
                callback('');
            });
            return;
        }

        const siteKey = "{{ env('RECAPTCHA_SITE_KEY_v3') }}";
        if (!siteKey || typeof grecaptcha === 'undefined') {
            callback('');
            return;
        }
        grecaptcha.ready(function() {
            grecaptcha.execute(siteKey, { action: action }).then(function(token) {
                callback(token);
            }).catch(function(err) {
                console.error('reCAPTCHA v3 error:', err);
                callback('');
            });
        });
    }

    // ==========================================
    // INTERACTIVE EVENTS EXPLORER MAP ENGINE
    // ==========================================
    let eventsMap = null;
    let eventsClusterGroup = null;
    let eventsUserLocationMarker = null;
    let currentMapEventsData = window.initialMapEvents || [];
    let eventsBaseTileLayers = {};
    let eventsActiveTileLayer = null;

    function initEventsExplorerMap() {
        const mapContainer = document.getElementById('events-explorer-map');
        if (!mapContainer || eventsMap) return;

        // Base Layers definitions (Google Maps-like styles)
        eventsBaseTileLayers = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }),
            dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }),
            voyager: L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            })
        };

        eventsMap = L.map('events-explorer-map', {
            zoomControl: true,
            scrollWheelZoom: false,
            dragging: true,
        }).setView([-2.5489, 118.0149], 5); // Center of Indonesia

        eventsActiveTileLayer = eventsBaseTileLayers.voyager;
        eventsActiveTileLayer.addTo(eventsMap);

        eventsClusterGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 45,
            spiderfyOnMaxZoom: true,
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                let clusterClass = 'events-cluster-small';
                if (count > 20) clusterClass = 'events-cluster-large';
                else if (count > 8) clusterClass = 'events-cluster-medium';

                return L.divIcon({
                    html: `<div class="events-map-cluster ${clusterClass}"><span>${count}</span></div>`,
                    className: 'events-cluster-wrapper',
                    iconSize: L.point(36, 36)
                });
            }
        });

        eventsMap.addLayer(eventsClusterGroup);
        renderEventsOnMap(currentMapEventsData);

        // Map Minimizer Toggle
        const toggleBtn = document.getElementById('btn-toggle-events-map');
        const minimizeBtn = document.getElementById('btn-events-map-minimize-toggle');
        const collapseWrap = document.getElementById('events-map-collapse-wrap');
        const labelToggle = document.getElementById('label-events-map-toggle');
        const iconToggle = document.getElementById('icon-events-map-toggle');

        function toggleMapVisibility() {
            if (!collapseWrap) return;
            const isHidden = collapseWrap.classList.contains('hidden');
            if (isHidden) {
                collapseWrap.classList.remove('hidden');
                if (labelToggle) labelToggle.textContent = 'Sembunyikan';
                if (iconToggle) iconToggle.className = 'fa-solid fa-chevron-up transition-transform duration-300 text-[10px]';
                setTimeout(() => { if (eventsMap) eventsMap.invalidateSize(); }, 200);
            } else {
                collapseWrap.classList.add('hidden');
                if (labelToggle) labelToggle.textContent = 'Tampilkan Peta';
                if (iconToggle) iconToggle.className = 'fa-solid fa-chevron-down transition-transform duration-300 text-[10px]';
            }
        }

        if (minimizeBtn) minimizeBtn.addEventListener('click', toggleMapVisibility);
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                if (e.target.closest('button')) return;
                toggleMapVisibility();
            });
        }

        // Recenter Button
        const recenterBtn = document.getElementById('btn-events-map-recenter');
        if (recenterBtn) {
            recenterBtn.addEventListener('click', function() {
                fitEventsMapBounds();
            });
        }

        // Locate Me Button
        const locateBtn = document.getElementById('btn-events-map-locate-me');
        if (locateBtn) {
            locateBtn.addEventListener('click', function() {
                locateUserOnEventsMap();
            });
        }
    }

    function toggleEventsMapLayerMenu() {
        document.getElementById('events-map-layer-menu')?.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('events-map-layer-dropdown-wrap');
        const menu = document.getElementById('events-map-layer-menu');
        if (wrap && menu && !wrap.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    function setEventsMapLayer(type) {
        if (!eventsMap || !eventsBaseTileLayers[type]) return;
        if (eventsActiveTileLayer) eventsMap.removeLayer(eventsActiveTileLayer);
        eventsActiveTileLayer = eventsBaseTileLayers[type];
        eventsActiveTileLayer.addTo(eventsMap);

        const labelMap = {
            'osm': 'OSM Street',
            'dark': 'Dark Tactical',
            'voyager': 'Voyager',
            'satellite': 'Satelit Esri'
        };
        const labelEl = document.getElementById('label-events-active-layer');
        if (labelEl) labelEl.textContent = labelMap[type] || type;

        document.querySelectorAll('.events-layer-check-osm, .events-layer-check-dark, .events-layer-check-voyager, .events-layer-check-satellite').forEach(el => el.classList.add('hidden'));
        document.querySelector('.events-layer-check-' + type)?.classList.remove('hidden');
        document.getElementById('events-map-layer-menu')?.classList.add('hidden');
    }

    function renderEventsOnMap(eventsList) {
        if (!eventsClusterGroup) return;
        eventsClusterGroup.clearLayers();

        const countBadge = document.getElementById('events-map-count');
        if (countBadge) {
            countBadge.textContent = `${eventsList.length} Event Terdaftar`;
        }

        if (!eventsList || eventsList.length === 0) {
            return;
        }

        const validMarkers = [];

        eventsList.forEach(event => {
            if (!event.lat || !event.lng) return;

            const isTrail = event.race_type && event.race_type.toLowerCase().includes('trail');
            const isUltra = event.race_type && (event.race_type.toLowerCase().includes('ultra') || event.race_type.toLowerCase().includes('marathon'));
            const pinBg = isTrail ? '#10b981' : (isUltra ? '#f97316' : '#ccff00');
            const pinColor = isTrail || isUltra ? '#ffffff' : '#020617';

            const customPinIcon = L.divIcon({
                className: 'border-0 bg-transparent',
                html: `
                    <div class="relative group cursor-pointer">
                        <div class="w-8 h-8 rounded-full border-2 border-[#0c121e] shadow-xl flex items-center justify-center font-black text-xs transition-transform transform group-hover:scale-110" style="background-color: ${pinBg}; color: ${pinColor};">
                            <i class="fa-solid fa-person-running text-xs"></i>
                        </div>
                        <div class="w-2 h-2 rounded-full mx-auto -mt-1 shadow-md" style="background-color: ${pinBg};"></div>
                    </div>
                `,
                iconSize: [32, 36],
                iconAnchor: [16, 36],
                popupAnchor: [0, -36]
            });

            const marker = L.marker([event.lat, event.lng], { icon: customPinIcon });

            let distanceBadgesHtml = '';
            if (Array.isArray(event.distances) && event.distances.length > 0) {
                distanceBadgesHtml = '<div class="flex flex-wrap gap-1 mt-2">' + 
                    event.distances.map(d => `<span class="px-1.5 py-0.5 rounded bg-slate-900 border border-slate-750 text-[9px] font-mono font-bold text-slate-200 uppercase">${d}</span>`).join('') +
                    '</div>';
            }

            const popupHtml = `
                <div class="events-custom-leaflet-popup font-sans text-xs">
                    <div class="relative aspect-video overflow-hidden bg-slate-900">
                        <img src="${event.hero_image}" alt="${event.name}" class="w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0c121e] via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-[#0c121e]/90 text-neon border border-neon/30">
                            ${event.race_type || 'Event Lari'}
                        </span>
                    </div>
                    <div class="p-3.5 space-y-2">
                        <h4 class="font-black text-white text-sm tracking-tight leading-tight line-clamp-2 hover:text-neon transition">
                            <a href="${event.url}">${event.name}</a>
                        </h4>
                        <div class="space-y-1 text-slate-300 text-[11px] font-medium">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-neon text-[10px]"></i>
                                <span>${event.start_at || 'Segera Hadir'}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-location-dot text-neon text-[10px]"></i>
                                <span class="truncate">${event.location_name || event.city}</span>
                            </div>
                        </div>
                        ${distanceBadgesHtml}
                        <div class="pt-2">
                            <a href="${event.url}" class="w-full py-2 px-3 rounded-lg bg-neon hover:bg-lime-300 text-dark font-black text-xs uppercase tracking-wider text-center block transition shadow-sm">
                                Lihat Detail Event
                            </a>
                        </div>
                    </div>
                </div>
            `;

            marker.bindPopup(popupHtml, { maxWidth: 300, minWidth: 260, className: 'events-custom-leaflet-popup' });
            eventsClusterGroup.addLayer(marker);
            validMarkers.push(marker);
        });

        if (validMarkers.length > 0) {
            fitEventsMapBounds();
        }
    }

    function fitEventsMapBounds() {
        if (!eventsMap || !eventsClusterGroup) return;
        const bounds = eventsClusterGroup.getBounds();
        if (bounds.isValid()) {
            eventsMap.fitBounds(bounds, { padding: [35, 35], maxZoom: 14 });
        } else {
            eventsMap.setView([-2.5489, 118.0149], 5);
        }
    }

    function setEventsMapTypeFilter(typeId) {
        document.querySelectorAll('.btn-event-map-pill').forEach(btn => {
            const bType = btn.getAttribute('data-map-type');
            if (bType === String(typeId)) {
                btn.className = 'btn-event-map-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border bg-slate-800 text-white font-bold border-slate-600 shrink-0 flex items-center gap-1.5';
            } else {
                btn.className = 'btn-event-map-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border text-slate-300 hover:text-white border-transparent shrink-0 flex items-center gap-1.5';
            }
        });

        const form = document.getElementById('filter-form');
        if (form) {
            const select = form.querySelector('select[name="race_type_id"]');
            if (select) {
                select.value = typeId;
            }
        }

        // Sync quick filter buttons
        document.querySelectorAll('.quick-filter-btn[data-filter-type="type"]').forEach(b => {
            if (b.getAttribute('data-value') === String(typeId)) {
                b.className = "quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-neon text-dark border-neon shadow-sm shadow-neon/10";
            } else {
                b.className = "quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-600 hover:text-white";
            }
        });

        // Trigger AJAX fetch
        if (window.fetchEventsGlobal) {
            window.fetchEventsGlobal();
        }
    }

    function locateUserOnEventsMap() {
        if (!navigator.geolocation) {
            alert('Geolocation tidak didukung oleh browser Anda.');
            return;
        }

        const locateBtn = document.getElementById('btn-events-map-locate-me');
        const origHtml = locateBtn ? locateBtn.innerHTML : '';
        if (locateBtn) {
            locateBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin text-[10px] text-neon"></i><span class="hidden sm:inline">Mencari...</span>';
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                if (locateBtn) locateBtn.innerHTML = origHtml;
                const uLat = pos.coords.latitude;
                const uLng = pos.coords.longitude;

                if (eventsUserLocationMarker && eventsMap.hasLayer(eventsUserLocationMarker)) {
                    eventsMap.removeLayer(eventsUserLocationMarker);
                }

                const userPin = L.divIcon({
                    className: 'border-0 bg-transparent',
                    html: '<div class="relative flex items-center justify-center w-7 h-7"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span><span class="relative inline-flex rounded-full h-4 w-4 bg-sky-500 border-2 border-white shadow-xl"></span></div>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                eventsUserLocationMarker = L.marker([uLat, uLng], { icon: userPin, zIndexOffset: 3000 }).addTo(eventsMap);
                eventsUserLocationMarker.bindPopup('<strong class="text-sky-400">Lokasi Anda Saat Ini</strong>').openPopup();

                eventsMap.setView([uLat, uLng], 11, { animate: true });
            },
            function(err) {
                if (locateBtn) locateBtn.innerHTML = origHtml;
                alert('Gagal mendeteksi lokasi GPS: ' + err.message);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    document.addEventListener('DOMContentLoaded', function() {
        initEventsExplorerMap();

        const form = document.getElementById('filter-form');
        const container = document.getElementById('events-container');
        const paginationContainer = document.getElementById('pagination-container');
        const loading = document.getElementById('loading-indicator');
        let timeout = null;

        function fetchEvents(url = "{{ route('events.index') }}") {
            container.classList.add('opacity-50');
            loading.classList.remove('hidden');

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            if (url.includes('?')) {
                const [baseUrl, existingQuery] = url.split('?');
                const existingParams = new URLSearchParams(existingQuery);
                for(let [key, value] of existingParams) {
                    if(key === 'page') params.set('page', value);
                }
            }

            fetch(`${url.split('?')[0]}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                attachPaginationListeners();

                // Dynamically refresh map events
                if (data.mapEvents) {
                    currentMapEventsData = data.mapEvents;
                    renderEventsOnMap(data.mapEvents);
                }
            })
            .finally(() => {
                container.classList.remove('opacity-50');
                loading.classList.add('hidden');
            });
        }

        window.fetchEventsGlobal = fetchEvents;

        function attachPaginationListeners() {
            document.querySelectorAll('#pagination-container a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchEvents(this.href);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        form.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => fetchEvents());
        });

        form.querySelector('input[name="search"]').addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fetchEvents(), 500);
        });

        const mobileToggle = document.getElementById('mobile-filter-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                document.querySelectorAll('.mobile-filter-item').forEach(el => {
                    el.classList.toggle('hidden');
                });
                this.classList.toggle('text-neon');
                this.classList.toggle('text-white');
            });
        }

        // Quick Filters Handling
        const quickBtns = document.querySelectorAll('.quick-filter-btn');
        quickBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-filter-type');
                const value = this.getAttribute('data-value');
                
                if (type === 'distance') {
                    const select = form.querySelector('select[name="race_distance_id"]');
                    if (select) {
                        select.value = value;
                    }
                    document.querySelectorAll('.quick-filter-btn[data-filter-type="distance"]').forEach(b => {
                        b.className = "quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-600 hover:text-white";
                    });
                } else if (type === 'type') {
                    const select = form.querySelector('select[name="race_type_id"]');
                    if (select) {
                        select.value = value;
                    }
                    document.querySelectorAll('.quick-filter-btn[data-filter-type="type"]').forEach(b => {
                        b.className = "quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-slate-900 border-slate-850 text-slate-400 hover:border-slate-600 hover:text-white";
                    });

                    // Sync map filter pills
                    document.querySelectorAll('.btn-event-map-pill').forEach(mp => {
                        if (mp.getAttribute('data-map-type') === String(value)) {
                            mp.className = 'btn-event-map-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border bg-slate-800 text-white font-bold border-slate-600 shrink-0 flex items-center gap-1.5';
                        } else {
                            mp.className = 'btn-event-map-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer border text-slate-300 hover:text-white border-transparent shrink-0 flex items-center gap-1.5';
                        }
                    });
                }
                
                this.className = "quick-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200 bg-neon text-dark border-neon shadow-sm shadow-neon/10";
                fetchEvents();
            });
        });

        attachPaginationListeners();

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('city') || urlParams.has('month') || urlParams.has('category') || urlParams.has('search') || urlParams.has('city_id')) {
            fetchEvents();
        }
    });
</script>

<script>
    (function () {
        var modal = document.getElementById('submit-event-modal');
        var openBtn = document.getElementById('btn-open-submit-event');
        var closeBtn = document.getElementById('btn-close-submit-event');
        var cancelBtn = document.getElementById('btn-submit-event-cancel');
        var sendOtpBtn = document.getElementById('btn-submit-event-send-otp');
        var submitBtn = document.getElementById('btn-submit-event-submit');
        var alertBox = document.getElementById('submit-event-alert');
        var form = document.getElementById('submit-event-form');
        var startedAtEl = document.getElementById('submit_event_started_at');
        var otpIdEl = document.getElementById('submit_event_otp_id');
        var otpCodeEl = document.getElementById('submit_event_otp_code');
        var emailEl = document.getElementById('submit_event_contributor_email');

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

        var mapboxToken = @json(config('services.mapbox.token'));
        var submitMap = null;
        var submitMarker = null;

        function showAlert(type, msg) {
            if (!alertBox) return;
            var cls = type === 'success'
                ? 'bg-green-900/30 border border-green-500/30 text-green-300'
                : 'bg-red-900/30 border border-red-500/30 text-red-300';
            alertBox.className = 'px-6 sm:px-8 pt-4';
            alertBox.innerHTML = '<div class="'+cls+' rounded-xl p-3.5 text-sm font-bold flex items-center gap-2"><i class="fas '+(type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle')+' text-base"></i><span>'+String(msg || '')+'</span></div>';
            alertBox.classList.remove('hidden');
        }

        function clearAlert() {
            if (!alertBox) return;
            alertBox.classList.add('hidden');
            alertBox.innerHTML = '';
        }

        function initSubmitEventMap() {
            var mapEl = document.getElementById('event-map');
            if (!mapEl || submitMap || typeof L === 'undefined') return;

            try {
                submitMap = L.map('event-map', { attributionControl: false }).setView([-6.2088, 106.8456], 11);

                var tileUrl = mapboxToken
                    ? 'https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/{z}/{x}/{y}?access_token=' + mapboxToken
                    : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

                var tileAttr = mapboxToken
                    ? '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    : '&copy; OpenStreetMap';

                L.tileLayer(tileUrl, {
                    maxZoom: 19,
                    tileSize: mapboxToken ? 512 : 256,
                    zoomOffset: mapboxToken ? -1 : 0,
                    attribution: tileAttr
                }).addTo(submitMap);

                submitMap.on('click', function (e) {
                    setMapLocation(e.latlng.lat, e.latlng.lng, true);
                });
            } catch (e) {
                console.error('Leaflet Map init error:', e);
            }
        }

        function setMapLocation(lat, lng, fetchAddress, overrideVenue, overrideAddr) {
            if (!submitMap) return;
            if (submitMarker) {
                submitMarker.setLatLng([lat, lng]);
            } else {
                submitMarker = L.marker([lat, lng]).addTo(submitMap);
            }
            submitMap.panTo([lat, lng]);

            var locInput = document.getElementById('submit_event_location');
            var addrInput = document.getElementById('submit_event_address');

            if (overrideVenue && locInput) {
                locInput.value = overrideVenue;
            }
            if (overrideAddr && addrInput) {
                addrInput.value = overrideAddr;
            }

            if (fetchAddress) {
                var statusEl = document.getElementById('map-geocoding-status');
                if (statusEl) statusEl.textContent = 'Mendeteksi alamat...';

                if (mapboxToken) {
                    var mapboxGeocodeUrl = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?access_token=' + mapboxToken + '&types=place,locality,neighborhood,address,poi';
                    fetch(mapboxGeocodeUrl)
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (statusEl) statusEl.textContent = '';
                            if (data && data.features && data.features.length > 0) {
                                var feature = data.features[0];
                                var placeName = feature.place_name || '';
                                var text = feature.text || '';

                                var cityStr = '';
                                if (feature.place_type && feature.place_type.indexOf('place') !== -1) {
                                    cityStr = feature.text;
                                } else if (feature.context) {
                                    feature.context.forEach(function (ctx) {
                                        if (ctx.id.indexOf('place.') === 0 || ctx.id.indexOf('district.') === 0) {
                                            cityStr = ctx.text;
                                        }
                                    });
                                }
                                if (cityStr) autoMatchCity(cityStr);

                                if (addrInput && (!addrInput.value.trim() || !overrideAddr)) {
                                    addrInput.value = placeName;
                                }
                                if (locInput && (!locInput.value.trim() || !overrideVenue)) {
                                    locInput.value = text;
                                }
                            }
                        })
                        .catch(function () {
                            fallbackNominatimGeocode(lat, lng, statusEl, overrideVenue, overrideAddr);
                        });
                } else {
                    fallbackNominatimGeocode(lat, lng, statusEl, overrideVenue, overrideAddr);
                }
            }
        }

        function fallbackNominatimGeocode(lat, lng, statusEl, overrideVenue, overrideAddr) {
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (statusEl) statusEl.textContent = '';
                    if (data && data.address) {
                        var addr = data.address;
                        var rawCity = addr.city || addr.town || addr.city_district || addr.county || addr.state_district || '';
                        if (rawCity) autoMatchCity(rawCity);

                        var road = addr.road || addr.pedestrian || addr.suburb || '';
                        var display = data.display_name || '';
                        var addrInput = document.getElementById('submit_event_address');
                        if (addrInput && (!addrInput.value.trim() || !overrideAddr)) {
                            addrInput.value = road ? (road + (rawCity ? ', ' + rawCity : '')) : display;
                        }
                        var locInput = document.getElementById('submit_event_location');
                        if (locInput && (!locInput.value.trim() || !overrideVenue) && (addr.building || addr.amenity || addr.leisure || addr.shop)) {
                            locInput.value = addr.building || addr.amenity || addr.leisure || addr.shop || '';
                        }
                    }
                })
                .catch(function () {
                    if (statusEl) statusEl.textContent = '';
                });
        }

        function autoMatchCity(cityStr) {
            var select = document.getElementById('submit_event_city_id');
            if (!select) return;
            var cleanSearch = cityStr.toLowerCase().replace(/kota|kabupaten|kab\./gi, '').trim();

            for (var i = 0; i < select.options.length; i++) {
                var opt = select.options[i];
                if (!opt.value) continue;
                var optText = opt.text.toLowerCase().replace(/kota|kabupaten|kab\./gi, '').trim();
                if (optText && (optText.includes(cleanSearch) || cleanSearch.includes(optText))) {
                    select.value = opt.value;
                    break;
                }
            }
        }

        // Location Search Input above Map Handler
        var searchInput = document.getElementById('submit_map_search_input');
        var searchResults = document.getElementById('map-search-results');
        var clearSearchBtn = document.getElementById('btn-clear-map-search');
        var searchIcon = document.getElementById('map-search-icon');
        var searchDebounceTimer = null;

        function setSearching(isSearching) {
            if (!searchIcon) return;
            if (isSearching) {
                searchIcon.className = 'fas fa-spinner fa-spin text-neon';
            } else {
                searchIcon.className = 'fas fa-search text-slate-500';
            }
        }

        function hideSearchResults() {
            if (searchResults) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
            }
        }

        function escapeHtml(text) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text || '').replace(/[&<>"']/g, function (m) { return map[m]; });
        }

        function renderSearchResults(items) {
            if (!searchResults) return;
            if (!items || items.length === 0) {
                searchResults.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400 text-center font-medium">Lokasi tidak ditemukan. Coba ketik nama lain.</div>';
                searchResults.classList.remove('hidden');
                return;
            }

            var html = '';
            items.forEach(function (item, idx) {
                var nameEsc = escapeHtml(item.name);
                var detailEsc = escapeHtml(item.detail || '');
                html += '<button type="button" class="w-full text-left px-3.5 py-2.5 hover:bg-slate-800 transition-colors flex items-start gap-2.5 group cursor-pointer location-search-item" data-index="' + idx + '">';
                html += '<i class="fas fa-map-marker-alt text-neon text-xs mt-1 shrink-0"></i>';
                html += '<div class="min-w-0 flex-1">';
                html += '<div class="text-xs font-bold text-white truncate">' + nameEsc + '</div>';
                if (detailEsc) {
                    html += '<div class="text-[11px] text-slate-400 truncate mt-0.5">' + detailEsc + '</div>';
                }
                html += '</div>';
                html += '</button>';
            });

            searchResults.innerHTML = html;
            searchResults.classList.remove('hidden');

            var buttons = searchResults.querySelectorAll('.location-search-item');
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var idx = parseInt(this.getAttribute('data-index'), 10);
                    var selected = items[idx];
                    if (selected) {
                        selectLocationResult(selected);
                    }
                });
            });
        }

        function selectLocationResult(item) {
            initSubmitEventMap();
            hideSearchResults();
            if (searchInput) {
                searchInput.value = item.name;
                if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
            }

            var lat = parseFloat(item.lat);
            var lng = parseFloat(item.lng);

            if (submitMap && !isNaN(lat) && !isNaN(lng)) {
                if (submitMarker) {
                    submitMarker.setLatLng([lat, lng]);
                } else {
                    submitMarker = L.marker([lat, lng]).addTo(submitMap);
                }
                submitMap.setView([lat, lng], 16);
            }

            var locInput = document.getElementById('submit_event_location');
            if (locInput) {
                locInput.value = item.name;
            }

            var addrInput = document.getElementById('submit_event_address');
            if (addrInput && item.detail) {
                addrInput.value = item.detail;
            }

            if (item.city) {
                autoMatchCity(item.city);
            }

            if (!isNaN(lat) && !isNaN(lng)) {
                setMapLocation(lat, lng, true, item.name, item.detail);
            }
        }

        function queryLocationSearch(query) {
            query = (query || '').trim();
            if (!query || query.length < 2) {
                hideSearchResults();
                setSearching(false);
                return;
            }

            setSearching(true);

            if (mapboxToken) {
                var mapboxUrl = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?access_token=' + mapboxToken + '&country=id&autocomplete=true&limit=5&types=poi,address,place,locality,neighborhood';
                fetch(mapboxUrl)
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        setSearching(false);
                        if (data && data.features && data.features.length > 0) {
                            var items = data.features.map(function (f) {
                                var cityStr = '';
                                if (f.context) {
                                    f.context.forEach(function (ctx) {
                                        if (ctx.id.indexOf('place.') === 0 || ctx.id.indexOf('district.') === 0) {
                                            cityStr = ctx.text;
                                        }
                                    });
                                }
                                return {
                                    name: f.text || f.place_name,
                                    detail: f.place_name,
                                    lat: f.center[1],
                                    lng: f.center[0],
                                    city: cityStr
                                };
                            });
                            renderSearchResults(items);
                        } else {
                            fallbackNominatimSearch(query);
                        }
                    })
                    .catch(function () {
                        fallbackNominatimSearch(query);
                    });
            } else {
                fallbackNominatimSearch(query);
            }
        }

        function fallbackNominatimSearch(query) {
            var url = 'https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=id&limit=5&q=' + encodeURIComponent(query);
            fetch(url)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    setSearching(false);
                    if (data && data.length > 0) {
                        var items = data.map(function (d) {
                            var rawCity = '';
                            if (d.address) {
                                rawCity = d.address.city || d.address.town || d.address.city_district || d.address.county || d.address.state_district || '';
                            }
                            var name = d.name || (d.display_name ? d.display_name.split(',')[0] : 'Lokasi');
                            return {
                                name: name,
                                detail: d.display_name,
                                lat: parseFloat(d.lat),
                                lng: parseFloat(d.lon),
                                city: rawCity
                            };
                        });
                        renderSearchResults(items);
                    } else {
                        renderSearchResults([]);
                    }
                })
                .catch(function () {
                    setSearching(false);
                    renderSearchResults([]);
                });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var val = this.value;
                if (clearSearchBtn) {
                    if (val) clearSearchBtn.classList.remove('hidden');
                    else clearSearchBtn.classList.add('hidden');
                }
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function () {
                    queryLocationSearch(val);
                }, 350);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    var firstItem = searchResults ? searchResults.querySelector('.location-search-item') : null;
                    if (firstItem) {
                        firstItem.click();
                    } else if (this.value.trim()) {
                        queryLocationSearch(this.value.trim());
                    }
                } else if (e.key === 'Escape') {
                    hideSearchResults();
                }
            });
        }

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                hideSearchResults();
                clearSearchBtn.classList.add('hidden');
            });
        }

        document.addEventListener('click', function (e) {
            if (searchInput && searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Geolocation Button Handler
        var geoBtn = document.getElementById('btn-geolocation');
        if (geoBtn) {
            geoBtn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Browser kamu tidak mendukung Geolocation.');
                    return;
                }
                geoBtn.disabled = true;
                geoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendeteksi...';

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        geoBtn.disabled = false;
                        geoBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Deteksi Lokasi Saya';
                        initSubmitEventMap();
                        setMapLocation(pos.coords.latitude, pos.coords.longitude, true);
                        if (submitMap) submitMap.setZoom(15);
                    },
                    function (err) {
                        geoBtn.disabled = false;
                        geoBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Deteksi Lokasi Saya';
                        alert('Gagal mendeteksi lokasi: ' + (err.message || 'Izin ditolak.'));
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }

        // Dropzone Handler
        var dropzone = document.getElementById('banner-dropzone');
        var fileInput = document.getElementById('submit_event_banner');
        var previewImg = document.getElementById('banner-preview-img');
        var dropzoneDefault = document.getElementById('banner-dropzone-default');
        var dropzonePreview = document.getElementById('banner-dropzone-preview');
        var fileNameEl = document.getElementById('banner-filename');
        var removeBtn = document.getElementById('btn-remove-banner');

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function (e) {
                if (e.target.closest('#btn-remove-banner')) return;
                fileInput.click();
            });

            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-neon', 'bg-neon/10');
                }, false);
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-neon', 'bg-neon/10');
                }, false);
            });

            dropzone.addEventListener('drop', function (e) {
                var dt = e.dataTransfer;
                var files = dt ? dt.files : null;
                if (files && files.length > 0) {
                    fileInput.files = files;
                    handleBannerPreview(files[0]);
                }
            });

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    handleBannerPreview(fileInput.files[0]);
                }
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    fileInput.value = '';
                    dropzoneDefault.classList.remove('hidden');
                    dropzonePreview.classList.add('hidden');
                    previewImg.src = '';
                    if (fileNameEl) fileNameEl.textContent = '';
                });
            }
        }

        function handleBannerPreview(file) {
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                showAlert('error', 'Ukuran banner maksimal 2MB.');
                fileInput.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                dropzoneDefault.classList.add('hidden');
                dropzonePreview.classList.remove('hidden');
                if (fileNameEl) fileNameEl.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
            };
            reader.readAsDataURL(file);
        }

        function openModal() {
            if (!modal) return;
            if (window.loadRecaptcha) {
                window.loadRecaptcha();
            }
            clearAlert();
            otpIdEl.value = '';
            otpCodeEl.value = '';
            startedAtEl.value = String(Date.now());
            if (searchInput) searchInput.value = '';
            if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
            hideSearchResults();
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            setTimeout(function() {
                initSubmitEventMap();
                if (submitMap) submitMap.invalidateSize();
            }, 250);
        }

        function closeModal() {
            if (!modal) return;
            if (searchInput) searchInput.value = '';
            if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
            hideSearchResults();
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function getPayload() {
            return new FormData(form);
        }

        function setBusy(btn, busy, text) {
            if (!btn) return;
            btn.disabled = !!busy;
            if (text) btn.textContent = text;
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        }

        try {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('submit') === '1' || window.location.hash === '#submit') {
                openModal();
            }
        } catch (_) {}

        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', function () {
                clearAlert();
                var email = String(emailEl.value || '').trim();
                if (!email) {
                    showAlert('error', 'Email wajib diisi untuk kirim OTP.');
                    return;
                }

                setBusy(sendOtpBtn, true, 'Mengirim...');

                getV3Token('request_otp', function(token) {
                    if ({{ env('RECAPTCHA_SITE_KEY_v3') ? 'true' : 'false' }} && !token) {
                        showAlert('error', 'Gagal memverifikasi reCAPTCHA. Silakan muat ulang halaman.');
                        setBusy(sendOtpBtn, false, 'Kirim OTP');
                        return;
                    }

                    fetch(@json(route('events.submissions.request-otp')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email,
                            website: '',
                            'g-recaptcha-response': token
                        })
                    })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
                    .then(function (res) {
                        if (!res.ok || !res.data || !res.data.success) {
                            showAlert('error', (res.data && res.data.message) ? res.data.message : 'Gagal mengirim OTP.');
                            return;
                        }
                        otpIdEl.value = res.data.otp_id || '';
                        showAlert('success', res.data.message || 'OTP terkirim. Cek email kamu.');
                    })
                    .catch(function () {
                        showAlert('error', 'Terjadi kesalahan saat mengirim OTP.');
                    })
                    .finally(function () {
                        setBusy(sendOtpBtn, false, 'Kirim OTP');
                    });
                });
            });
        }

        var regLinkEl = document.getElementById('submit_event_registration_link');
        var socLinkEl = document.getElementById('submit_event_social_media_link');

        function normalizeRegistrationUrl(val) {
            if (!val) return '';
            val = String(val).trim();
            if (!val) return '';
            val = val.replace(/^https?:?\/{0,2}/i, '');
            return 'https://' + val.replace(/^\/+/, '');
        }

        function normalizeInstagramUrl(val) {
            if (!val) return '';
            val = String(val).trim();
            if (!val) return '';
            if (val.startsWith('@')) {
                return 'https://instagram.com/' + val.substring(1).trim();
            }
            var igMatch = val.match(/^(?:https?:?\/{0,2})?(?:www\.)?instagram\.com\/(.+)$/i);
            if (igMatch) {
                return 'https://instagram.com/' + igMatch[1].replace(/^\/+/, '');
            }
            if (!/^https?:\/\//i.test(val)) {
                if (val.includes('.') || val.includes('/')) {
                    return 'https://' + val;
                }
                return 'https://instagram.com/' + val;
            }
            return val;
        }

        if (regLinkEl) {
            regLinkEl.addEventListener('blur', function () {
                if (this.value) {
                    this.value = normalizeRegistrationUrl(this.value);
                }
            });
        }

        if (socLinkEl) {
            socLinkEl.addEventListener('blur', function () {
                if (this.value) {
                    this.value = normalizeInstagramUrl(this.value);
                }
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                clearAlert();

                if (regLinkEl && regLinkEl.value) {
                    regLinkEl.value = normalizeRegistrationUrl(regLinkEl.value);
                }
                if (socLinkEl && socLinkEl.value) {
                    socLinkEl.value = normalizeInstagramUrl(socLinkEl.value);
                }

                var payload = getPayload();

                if (!payload.get('otp_id')) {
                    showAlert('error', 'Klik “Kirim OTP” dulu sebelum submit.');
                    return;
                }
                
                var otpCode = payload.get('otp_code');
                if (!otpCode || String(otpCode).length !== 6) {
                    showAlert('error', 'Masukkan OTP 6 digit.');
                    return;
                }

                setBusy(submitBtn, true, 'Memproses...');

                getV3Token('submit_event', function(token) {
                    if ({{ env('RECAPTCHA_SITE_KEY_v3') ? 'true' : 'false' }} && !token) {
                        showAlert('error', 'Gagal memverifikasi reCAPTCHA. Silakan muat ulang halaman.');
                        setBusy(submitBtn, false, 'Submit Event');
                        return;
                    }

                    if (token) {
                        payload.set('g-recaptcha-response', token);
                    }

                    fetch(@json(route('events.submissions.store')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: payload
                    })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
                    .then(function (res) {
                        if (!res.ok || !res.data || !res.data.success) {
                            showAlert('error', (res.data && res.data.message) ? res.data.message : 'Submit gagal.');
                            return;
                        }
                        showAlert('success', res.data.message || 'Submit berhasil.');
                        form.reset();
                        otpIdEl.value = '';
                        otpCodeEl.value = '';
                        startedAtEl.value = String(Date.now());
                        if (removeBtn) removeBtn.click();
                        setTimeout(closeModal, 1500);
                    })
                    .catch(function () {
                        showAlert('error', 'Terjadi kesalahan saat submit.');
                    })
                    .finally(function () {
                        setBusy(submitBtn, false, 'Submit Event');
                    });
                });
            });
        }
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // Auto open modal if URL has submit=1 or open_submit=1
        try {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('submit') === '1' || urlParams.get('open_submit') === '1') {
                setTimeout(openModal, 200);
            }
        } catch (e) {}
    })();
</script>
@endpush

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Jadwal Lari 2026 Indonesia",
  "description": "Kalender event lari Indonesia 2026 berisi jadwal fun run, 5K, 10K, half marathon, marathon, trail run, dan virtual run.",
  "url": "https://ruanglari.com/jadwal-lari",
  "isPartOf": {
    "@type": "WebSite",
    "name": "Ruang Lari",
    "url": "https://ruanglari.com"
  }
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Daftar Jadwal Lari 2026 Indonesia",
  "itemListElement": [
    @foreach($events as $index => $event)
    {
      "@type": "ListItem",
      "position": {{ $index + 1 }},
      "url": "{{ $event->public_url }}",
      "name": "{{ e($event->name) }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Beranda",
      "item": "https://ruanglari.com"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Jadwal Lari",
      "item": "https://ruanglari.com/jadwal-lari"
    }
  ]
}
</script>
@endpush

@endsection
