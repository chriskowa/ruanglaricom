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
@section('canonical_url', $canonicalUrl)
@section('og_image', 'https://ruanglari.com/images/og/jadwal-lari-2026.jpg')

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
                        Kalender ini diperbarui secara berkala agar pelari dapat lebih mudah memilih event berdasarkan kota, bulan, jenis lomba, dan kategori jarak. Gunakan fitur pencarian dan filter untuk menemukan event lari terdekat di Jakarta, Bandung, Surabaya, Malang, Yogyakarta, Bali, Makassar, dan berbagai kota lainnya di Indonesia.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-3">
                    <a href="{{ url('/calendar') }}" class="px-8 py-3 rounded-full bg-slate-800 border border-slate-700 text-white font-bold hover:bg-neon hover:text-dark hover:border-neon transition-all inline-flex items-center justify-center gap-2 shadow-lg hover:shadow-neon/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Kelola Kalender Lari Saya
                    </a>
                    <button type="button" id="btn-open-submit-event" class="px-8 py-3 rounded-full bg-neon text-dark font-extrabold hover:bg-lime-300 transition-all inline-flex items-center justify-center gap-2 shadow-lg shadow-neon/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Submit Event Lari
                    </button>
                </div>
            </div>
            
            <!-- Hero Image/Banner -->
            <div class="lg:col-span-5 relative group" data-aos="zoom-in" data-aos-delay="100">
                <div class="absolute -inset-1.5 bg-gradient-to-r from-neon to-lime-500 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
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

<div id="submit-event-modal" class="fixed inset-0 z-[9999] hidden overflow-auto">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
    <div class="relative h-full w-full flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-auto h-screen">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Submit Event</div>
                    <div class="text-lg font-black text-white tracking-tighter">AJUKAN EVENT LARI</div>
                </div>
                <button type="button" id="btn-close-submit-event" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div id="submit-event-alert" class="hidden px-6 pt-4"></div>

            <form id="submit-event-form" class="px-6 py-5 space-y-5">
                <input type="text" name="website" id="submit_event_website" class="hidden" tabindex="-1" autocomplete="off">
                <input type="hidden" name="started_at" id="submit_event_started_at" value="0">
                <input type="hidden" name="otp_id" id="submit_event_otp_id" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Event</label>
                        <input type="text" name="event_name" id="submit_event_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Contoh: Jakarta City Run 2026" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Event</label>
                        <input type="date" name="event_date" id="submit_event_date" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" required>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Banner Event (Opsional)</label>
                        <input type="file" name="banner" id="submit_event_banner" accept="image/png, image/jpeg, image/jpg, image/webp" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-neon hover:file:bg-slate-700">
                        <div class="text-[11px] text-slate-500">Maksimal 2MB. Disarankan landscape.</div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jam Mulai (Opsional)</label>
                        <input type="time" name="start_time" id="submit_event_time" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota (Opsional)</label>
                        <select name="city_id" id="submit_event_city_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon">
                            <option value="">Pilih Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lokasi</label>
                        <input type="text" name="location_name" id="submit_event_location" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Contoh: Gelora Bung Karno" required>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat (Opsional)</label>
                        <input type="text" name="location_address" id="submit_event_address" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Alamat lengkap / titik kumpul">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Lomba (Opsional)</label>
                        <select name="race_type_id" id="submit_event_race_type_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon">
                            <option value="">Pilih Jenis</option>
                            @foreach($raceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori Jarak (Opsional)</label>
                        <select name="race_distance_ids" id="submit_event_race_distance_ids" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" multiple>
                            @foreach($raceDistances as $distance)
                                <option value="{{ $distance->id }}">{{ $distance->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-slate-500">Bisa pilih lebih dari satu.</div>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Pendaftaran (Opsional)</label>
                        <input type="url" name="registration_link" id="submit_event_registration_link" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="https://...">
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Media Sosial (Opsional)</label>
                        <input type="url" name="social_media_link" id="submit_event_social_media_link" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="https://instagram.com/...">
                    </div>
                </div>

                <div class="border-t border-slate-800 pt-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Penyelenggara (Opsional)</label>
                            <input type="text" name="organizer_name" id="submit_event_organizer_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Nama EO / komunitas">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kontak Penyelenggara (Opsional)</label>
                            <input type="text" name="organizer_contact" id="submit_event_organizer_contact" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="WA / Email">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kamu (Opsional)</label>
                            <input type="text" name="contributor_name" id="submit_event_contributor_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Nama pengaju">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email Kamu</label>
                            <input type="email" name="contributor_email" id="submit_event_contributor_email" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="email@kamu.com" required>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan (Opsional)</label>
                            <textarea name="notes" id="submit_event_notes" rows="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Info tambahan (mis: kuota, kategori, syarat, dll)"></textarea>
                        </div>
                    </div>



                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <div class="md:col-span-2 space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode OTP</label>
                            <input type="text" inputmode="numeric" maxlength="6" name="otp_code" id="submit_event_otp_code" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="6 digit">
                        </div>
                        <button type="button" id="btn-submit-event-send-otp" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">
                            Kirim OTP
                        </button>
                    </div>
                </div>
            </form>

            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" id="btn-submit-event-cancel" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-200 font-bold hover:bg-slate-700 transition">Batal</button>
                <button type="button" id="btn-submit-event-submit" class="px-5 py-2.5 rounded-xl bg-neon text-dark font-extrabold hover:bg-lime-300 transition">Submit Event</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@push('scripts')
@php
    $siteKeyV3 = env('RECAPTCHA_SITE_KEY_v3');
@endphp
@if($siteKeyV3)
    <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKeyV3 }}"></script>
@endif
<script>
    function getV3Token(action, callback) {
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

    document.addEventListener('DOMContentLoaded', function() {
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
            })
            .finally(() => {
                container.classList.remove('opacity-50');
                loading.classList.add('hidden');
            });
        }

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

        function showAlert(type, msg) {
            if (!alertBox) return;
            var cls = type === 'success'
                ? 'bg-green-900/30 border border-green-500/30 text-green-300'
                : 'bg-red-900/30 border border-red-500/30 text-red-300';
            alertBox.className = 'px-6 pt-4';
            alertBox.innerHTML = '<div class="'+cls+' rounded-xl p-3 text-sm font-bold">'+String(msg || '')+'</div>';
            alertBox.classList.remove('hidden');
        }

        function clearAlert() {
            if (!alertBox) return;
            alertBox.classList.add('hidden');
            alertBox.innerHTML = '';
        }

        function openModal() {
            if (!modal) return;
            clearAlert();
            otpIdEl.value = '';
            otpCodeEl.value = '';
            startedAtEl.value = String(Date.now());
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function getPayload() {
            var formData = new FormData();
            var els = form.querySelectorAll('input, select, textarea');
            
            els.forEach(function (el) {
                if (!el.name) return;

                if (el.type === 'file') {
                    if (el.files && el.files[0]) {
                        formData.append(el.name, el.files[0]);
                    }
                    return;
                }

                if (el.multiple) {
                    Array.prototype.forEach.call(el.selectedOptions || [], function (opt) {
                        formData.append(el.name + '[]', opt.value);
                    });
                    return;
                }

                if (el.type === 'radio' || el.type === 'checkbox') {
                    if (el.checked) {
                        formData.append(el.name, el.value);
                    }
                    return;
                }

                formData.append(el.name, el.value);
            });

            return formData;
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

        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                clearAlert();
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
                        setTimeout(closeModal, 800);
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
