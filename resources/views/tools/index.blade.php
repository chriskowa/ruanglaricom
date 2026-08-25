@extends('layouts.pacerhub')

@section('title', 'Ruang Lari Tools - Ekosistem & Alat Pelari Lengkap')

@section('content')
<div class="min-h-screen bg-[#070B12] pt-24 pb-20 px-4 md:px-8 relative font-sans text-slate-100">
    
    <!-- Hero Section -->
    <div class="max-w-4xl mx-auto text-center mb-14">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-slate-300 text-xs font-semibold uppercase tracking-wider mb-5">
            <i class="fa-solid fa-screwdriver-wrench text-white text-xs"></i>
            <span>Ruang Lari Tools &amp; Utilities</span>
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white uppercase tracking-tight leading-tight mb-4">
            Alat Lari &amp; Kalkulator Terintegrasi
        </h1>
        <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
            Koleksi tools presisi tinggi untuk mendukung persiapan lomba, pembuatan rute, kalkulasi pace, analisa performa lari, hingga manajemen event.
        </p>
    </div>

    <!-- Tools Grid -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        
        <!-- 1. Database GPX -->
        <a href="{{ route('gpx.index') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-map-location-dot text-lg text-white"></i>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-[#B8FF00] transition-colors">DATABASE GPX</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 uppercase border border-slate-700">Populer</span>
                </div>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Kumpulan rute lari dan file GPX terverifikasi di berbagai kota Indonesia. Download gratis untuk jam Garmin, Coros, Suunto, dan Strava.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Buka Database GPX</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 2. Route Builder -->
        <a href="{{ route('tools.buat-rute-lari') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-route text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">ROUTE BUILDER</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Buat dan gambar rute lari custom dengan snap to road otomatis. Cek profil elevasi, jarak akurat, estimasi waktu, dan export file GPX.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Buat Rute Baru</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 3. PacePro -->
        <a href="{{ route('tools.pace-pro') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-gauge-high text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">PACE PRO</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Kalkulator strategi pacing lomba tingkat lanjut. Hitung split interval, positive/negative split, dan jadwal strategi hidrasi/nutrisi lomba.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Buka Pace Pro</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 4. Race Predictor -->
        <a href="{{ route('calculator') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-calculator text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">RACE PREDICTOR</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Prediksi potensi catatan waktu race (5K, 10K, Half Marathon, Full Marathon) berdasarkan hasil latihan terakhir menggunakan formula Riegel.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Hitung Prediksi</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 5. Race Master Pro -->
        <a href="{{ route('tools.race-master') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-stopwatch-20 text-lg text-white"></i>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-[#B8FF00] transition-colors">RACE MASTER</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 uppercase border border-slate-700">Untuk EO &amp; Komunitas</span>
                </div>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Sistem manajemen race mandiri: BIB generator otomatis, pencatat waktu manual / lap timing, dan leaderboard real-time untuk event komunitas.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Kelola Race</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 6. TrackMaster Pro -->
        <a href="{{ route('tools.trackmaster') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-person-running text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">TRACKMASTER PRO</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Kelola sesi latihan interval multi-atlet di lintasan lari. Analisis pace putaran secara real-time dengan bantuan audio voice assistant.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Mulai Sesi Track</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 7. Form Analyzer (AI) -->
        <a href="{{ route('tools.form-analyzer') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-video text-lg text-white"></i>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-[#B8FF00] transition-colors">FORM ANALYZER</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 uppercase border border-slate-700">AI Beta</span>
                </div>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Analisis postur dan biomekanik lari menggunakan kecerdasan buatan. Upload rekaman video lari untuk evaluasi cadence, posture, dan foot strike.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Coba Form Analyzer</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 8. AI Training Analyzer (Strava) -->
        @auth
            @if(auth()->user()->role === 'runner')
            <a href="{{ route('runner.dashboard') }}#strava-analysis" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-chart-line text-lg text-white"></i>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-[#B8FF00] transition-colors">AI TRAINING ANALYZER</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 uppercase border border-slate-700">Strava AI</span>
                    </div>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                        Analisis data latihan Strava dengan AI Coach. Klasifikasi otomatis intensitas latihan, estimasi skor VDOT real-time, dan evaluasi beban latihan 80/20.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                    <span>Lihat Analisis Latihan</span>
                    <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
                </div>
            </a>
            @else
            <a href="{{ route('runner.dashboard') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-chart-line text-lg text-white"></i>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-[#B8FF00] transition-colors">AI TRAINING ANALYZER</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 uppercase border border-slate-700">Strava AI</span>
                    </div>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                        Analisis data latihan Strava dengan AI Coach. Klasifikasi otomatis intensitas latihan, estimasi skor VDOT real-time, dan evaluasi beban latihan 80/20.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                    <span>Buka Dashboard</span>
                    <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
                </div>
            </a>
            @endif
        @endauth

        @guest
        <a href="{{ route('login') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-chart-line text-lg text-white"></i>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-[#B8FF00] transition-colors">AI TRAINING ANALYZER</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 uppercase border border-slate-700">Strava AI</span>
                </div>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Analisis data latihan Strava dengan AI Coach. Klasifikasi otomatis intensitas latihan, estimasi skor VDOT real-time, dan evaluasi beban latihan 80/20.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Login &amp; Hubungkan Strava</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>
        @endguest

        <!-- 9. QR Code Generator -->
        <a href="{{ route('tools.qr-generator') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-qrcode text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">QR GENERATOR</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Buat QR Code resolusi tinggi untuk registrasi event lari, tautan rute, atau nomor kontak dengan download format PNG instan.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Generate QR Code</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 10. URL Shortener -->
        <a href="{{ route('tools.shortlink') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-link text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">URL SHORTENER</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Persingkat tautan rute lari, event, atau link sosial media dengan domain pendek RuangLari yang ringkas dan mudah dibagikan.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Perpendek Link</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 11. Kalender Lari -->
        <a href="{{ route('events.index') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-calendar-days text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">KALENDER LARI</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Direktori jadwal event dan race lari terlengkap di Indonesia. Filter berdasarkan kota, kategori jarak (5K/10K/HM/FM/Ultra), dan jenis rute.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Jelajahi Event</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

        <!-- 12. Program Lari -->
        <a href="{{ route('programs.realistic') }}" class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 shadow-xl hover:-translate-y-1">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center mb-5 group-hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-clipboard-list text-lg text-white"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover:text-[#B8FF00] transition-colors">PROGRAM LATIHAN</h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                    Program latihan terstruktur yang adaptif dengan target lomba dan kesibukan harian pelari, didesain berdasarkan metodologi VDOT ilmiah.
                </p>
            </div>
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                <span>Pilih Program</span>
                <i class="fa-solid fa-arrow-right text-xs text-white group-hover:translate-x-1.5 transition-transform"></i>
            </div>
        </a>

    </div>

</div>
@endsection
