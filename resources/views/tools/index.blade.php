@extends('layouts.pacerhub')

@section('title', 'Tools & Calculators')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="relative z-10 max-w-5xl mx-auto text-center mb-16">
        <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-4 leading-tight">
            RUN SMARTER.<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">
                DATA DRIVEN.
            </span>
        </h1>
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
            Kumpulan tools canggih untuk membantu pelari mencapai potensi maksimal. 
            Dari perencanaan rute hingga analisis performa.
        </p>
    </div>

    <!-- Tools Grid -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
        
        <!-- PacePro -->
        <a href="{{ route('tools.pace-pro') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-blue-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-blue-400 transition-colors">PACE PRO</h3>
                <p class="text-slate-400 mb-6 flex-grow">
                    Kalkulator strategi lomba paling advanced. Hitung split, positive/negative split, dan strategi nutrisi berdasarkan target waktu Anda.
                </p>
                <div class="flex items-center text-blue-400 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Try Pace Pro <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

        <!-- Route Builder -->
        <a href="{{ route('tools.buat-rute-lari') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-emerald-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-emerald-400 transition-colors">ROUTE BUILDER</h3>
                <p class="text-slate-400 mb-6 flex-grow">
                    Buat rute lari Anda sendiri dengan mudah. Cek elevasi, jarak, dan estimasi waktu. Export ke GPX atau sync langsung ke Strava.
                </p>
                <div class="flex items-center text-emerald-400 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Create Route <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

        <!-- Race Calculator -->
        <a href="{{ route('calculator') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-amber-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-amber-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-amber-400 transition-colors">RACE PREDICTOR</h3>
                <p class="text-slate-400 mb-6 flex-grow">
                    Prediksi waktu lomba Anda (5K, 10K, HM, FM) berdasarkan hasil lari terakhir. Menggunakan formula Riegel yang akurat.
                </p>
                <div class="flex items-center text-amber-400 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Calculate Now <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

        <!-- V-Card -->
        <a href="{{ route('vcard.index') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-purple-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .883-.393 1.73-1 2.293m0 6l-4 4m0 0l-4-4m4 4V12" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-purple-400 transition-colors">V-CARD</h3>
                <p class="text-slate-400 mb-6 flex-grow">
                    Kartu identitas pelari digital Anda. Tampilkan statistik, PB, dan achievement dalam satu kartu yang keren untuk dibagikan.
                </p>
                <div class="flex items-center text-purple-400 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Get V-Card <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

        <!-- Calendar -->
        <a href="{{ route('calendar.public') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-cyan-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-cyan-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-cyan-400 transition-colors">RACE CALENDAR</h3>
                <p class="text-slate-400 mb-6 flex-grow">
                    Jadwal event lari terlengkap di Indonesia. Temukan race berikutnya, simpan ke kalender pribadi, dan atur strategi latihan.
                </p>
                <div class="flex items-center text-cyan-400 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Explore Events <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

        <!-- Form Analyzer (Beta) -->
        <a href="{{ route('tools.form-analyzer') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-slate-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-slate-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-slate-700/50 text-slate-300 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex justify-between items-start">
                    <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-slate-300 transition-colors">FORM ANALYZER</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700 text-slate-300 uppercase">Beta</span>
                </div>
                <p class="text-slate-400 mb-6 flex-grow">
                    Analisis teknik lari Anda menggunakan AI. Upload video lari Anda dan dapatkan feedback instan tentang cadence, posture, dan foot strike.
                </p>
                <div class="flex items-center text-slate-300 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Try Beta <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

        <!-- Realistic Program -->
        <a href="{{ route('programs.realistic') }}" class="group relative overflow-hidden rounded-3xl bg-slate-800/50 border border-slate-700 hover:border-pink-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-pink-500/10 hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="p-8 h-full flex flex-col">
                <div class="w-14 h-14 rounded-2xl bg-pink-500/20 text-pink-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 group-hover:text-pink-400 transition-colors">REALISTIC PROGRAM</h3>
                <p class="text-slate-400 mb-6 flex-grow">
                    Program latihan lari yang disesuaikan dengan kemampuan dan jadwal harian Anda. Realistis, adaptif, dan efektif.
                </p>
                <div class="flex items-center text-pink-400 font-bold text-sm uppercase tracking-wider group-hover:translate-x-2 transition-transform">
                    Start Training <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </div>
            </div>
        </a>

    </div>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-600/10 rounded-full blur-[100px]"></div>
    </div>

</div>
@endsection
