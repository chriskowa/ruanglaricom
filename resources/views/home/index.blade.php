@extends('layouts.pacerhub')

@section('content')
    <div id="home-app" class="overflow-x-hidden">
        
        <!-- HERO SECTION -->
        <header class="relative min-h-screen flex items-center justify-center pt-20 md:pt-0">
            <!-- Dynamic Background -->
            <div class="absolute inset-0 bg-dark z-0 overflow-hidden">
                <!-- Mobile Parallax Background -->
                <div class="absolute inset-0 md:hidden">
                    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-fixed scale-110" 
                         style="background-image: url('https://res.cloudinary.com/dslfarxct/images/v1766050868/542301374_18517775974013478_1186867397282832240_n/542301374_18517775974013478_1186867397282832240_n.jpg');">
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/80 to-slate-900/60"></div>
                </div>

                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-dark to-black opacity-80 hidden md:block"></div>
                <!-- Animated Blobs -->
                <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-neon/10 rounded-full blur-[120px] animate-pulse-slow"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[150px] animate-pulse-slow" style="animation-delay: 2s"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                <div class="grid md:grid-cols-2 gap-12 md:gap-20 items-center">
                    
                    <!-- Hero Text -->
                    <div class="text-center md:text-left order-2 md:order-1" data-aos="fade-up">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neon/20 bg-neon/5 backdrop-blur-md text-neon text-xs font-bold uppercase tracking-wider mb-8">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neon opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-neon"></span>
                            </span>
                            Dari Pelari Untuk Pelari
                        </div>
                        
                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-black leading-tight mb-6 text-white tracking-tighter">
                            @if($homepageContent && $homepageContent->headline)
                                {!! $homepageContent->headline !!}
                            @else
                                LARI TANPA <br class="hidden md:block">
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon via-green-400 to-emerald-500">BATAS.</span>
                            @endif
                        </h1>
                        
                        <p class="text-slate-400 text-lg md:text-xl mb-10 max-w-lg mx-auto md:mx-0 leading-relaxed font-light">
                            @if($homepageContent && $homepageContent->subheadline)
                                {!! $homepageContent->subheadline !!}
                            @else
                                Platform all-in-one untuk pelari, pacer, dan pelatih. Temukan event, pantau progres, dan raih personal best Anda bersama <span class="text-white font-bold">Ruang Lari</span>.
                            @endif
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                            <a href="/register" class="px-8 py-4 bg-neon text-dark font-black rounded-xl hover:bg-white hover:scale-105 transition transform shadow-[0_0_30px_rgba(204,255,0,0.3)] text-center">
                                MULAI SEKARANG
                            </a>
                            <a href="#events" class="px-8 py-4 border border-slate-700 text-white font-bold rounded-xl hover:bg-slate-800 hover:border-slate-500 transition flex items-center justify-center gap-2 group">
                                <svg class="w-5 h-5 text-neon group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                LIHAT EVENT
                            </a>
                        </div>

                        <!-- Social Proof -->
                        <div class="mt-12 flex items-center justify-center md:justify-start gap-4">
                            <div class="flex -space-x-4">
                                <img class="w-12 h-12 rounded-full border-4 border-dark object-cover" src="https://i.pravatar.cc/100?img=11" alt="User">
                                <img class="w-12 h-12 rounded-full border-4 border-dark object-cover" src="https://i.pravatar.cc/100?img=33" alt="User">
                                <img class="w-12 h-12 rounded-full border-4 border-dark object-cover" src="https://i.pravatar.cc/100?img=12" alt="User">
                                <div class="w-12 h-12 rounded-full border-4 border-dark bg-slate-800 flex items-center justify-center text-xs font-bold text-white">+2k</div>
                            </div>
                            <div class="text-left">
                                <div class="flex text-yellow-400 text-xs mb-1">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                                <p class="text-sm text-slate-400"><span class="text-white font-bold">{{ number_format(\App\Models\User::whereIn('role', ['runner','coach'])->count()) }}</span> Pelari Bergabung</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Image -->
                    <div class="relative order-1 md:order-2" data-aos="fade-left" data-aos-delay="200">
                        <div class="relative z-10 rounded-[2.5rem] overflow-hidden border-8 border-slate-800/50 shadow-2xl rotate-3 hover:rotate-0 transition duration-700 group">
                            <div class="absolute inset-0 bg-gradient-to-t from-dark/80 via-transparent to-transparent z-10"></div>
                            <img src="{{ $homepageContent && $homepageContent->floating_image ? asset($homepageContent->floating_image) : 'https://res.cloudinary.com/dslfarxct/images/v1766050868/542301374_18517775974013478_1186867397282832240_n/542301374_18517775974013478_1186867397282832240_n.jpg' }}" alt="Runner" class="w-full h-[500px] object-cover object-top transform group-hover:scale-110 transition duration-1000">
                            
                            <!-- Floating Card Inside Image -->
                            <div class="absolute bottom-8 left-8 z-20 bg-dark/80 backdrop-blur-md p-4 rounded-2xl border border-white/10 flex items-center gap-4 pr-8">
                                <div class="w-12 h-12 rounded-full bg-neon/20 flex items-center justify-center text-neon">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Avg Pace</p>
                                    <p class="text-xl font-black text-white">4:35 <span class="text-xs text-slate-500 font-normal">/km</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Join Button -->
                        <div class="absolute top-1/2 -translate-y-1/2 -right-6 md:-right-12 z-30">
                            <a href="/register" class="flex flex-col items-center justify-center w-24 h-24 md:w-32 md:h-32 bg-neon rounded-full text-dark font-black text-xs md:text-sm text-center p-2 rotate-12 hover:rotate-0 hover:scale-110 transition duration-300 shadow-[0_0_40px_rgba(204,255,0,0.6)] animate-float border-4 border-dark">
                                <span>JOIN</span>
                                <span class="text-lg md:text-2xl leading-none">NOW</span>
                            </a>
                        </div>

                        <!-- Decor Elements -->
                        <div class="absolute -top-10 -right-10 text-[200px] leading-none font-black text-slate-800/30 select-none pointer-events-none z-0">01</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- BRANDS SECTION -->
        <section class="py-10 border-y border-slate-800/50 bg-slate-900/30">
            <div class="max-w-7xl mx-auto px-4 overflow-hidden">
                <div class="flex flex-wrap justify-center md:justify-between items-center gap-8 md:gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ea/New_Balance_logo.svg/2560px-New_Balance_logo.svg.png" class="h-6 md:h-8 invert hover:scale-110 transition">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Logo_NIKE.svg/1200px-Logo_NIKE.svg.png" class="h-6 md:h-8 invert hover:scale-110 transition">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/20/Adidas_Logo.svg/2560px-Adidas_Logo.svg.png" class="h-8 md:h-10 invert hover:scale-110 transition">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Under_armour_logo.svg/2560px-Under_armour_logo.svg.png" class="h-6 md:h-8 invert hover:scale-110 transition">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b8/Skechers.svg" class="h-6 md:h-8 invert hover:scale-110 transition">
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION -->
        <section class="py-24 relative overflow-hidden bg-dark">
            <div class="absolute inset-0 bg-slate-900/20"></div>
            <!-- Glow Effect -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-neon/5 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16" data-aos="fade-up">
                    <span class="text-neon font-bold tracking-widest uppercase text-sm mb-2 block">Ekosistem Ruang Lari</span>
                    <h2 class="text-3xl md:text-5xl font-black text-white">SOLUSI TERINTEGRASI <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-500">UNTUK PELARI</span></h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Card 1: Running Tools & Performance -->
                    <div x-data="{ open: false }" class="group" data-aos="fade-up" data-aos-delay="0">
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:shadow-neon/10 hover:border-neon/50 transition duration-300 h-full flex flex-col">
                        <button type="button" @click="open = !open" class="w-full text-left cursor-pointer p-6 flex flex-col">
                            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 mb-4 group-hover:scale-110 transition duration-300 border border-blue-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 leading-tight">Running Tools & Performance</h3>
                            <p class="text-slate-400 text-sm mb-4 flex-grow leading-relaxed">Tingkatkan performa larimu dengan teknologi dan program latihan terukur.</p>
                            
                            <div class="mt-auto flex items-center justify-between text-blue-400 text-sm font-bold pt-4 border-t border-slate-800/50">
                                <span x-text="open ? 'Tutup Menu' : 'Jelajahi Fitur'">Jelajahi Fitur</span>
                                <svg :class="{'rotate-180': open}" class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </button>

                        <!-- Accordion Content -->
                        <div x-show="open" x-collapse class="bg-slate-950 border-t border-slate-800 px-2 pb-2">
                            <ul class="space-y-1 pt-2">
                                <li>
                                    <a href="{{ route('calendar.public') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-blue-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-calendar-alt text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Kalender Lari</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('calculator') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-blue-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-calculator text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Pace Calculator</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('programs.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-blue-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-running text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Running Program</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('coaches.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-blue-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-user-stopwatch text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Coach Marketplace</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        </div>
                    </div>

                    <!-- Card 2: Community & Network -->
                    <div x-data="{ open: false }" class="group" data-aos="fade-up" data-aos-delay="100">
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:shadow-neon/10 hover:border-neon/50 transition duration-300 h-full flex flex-col">
                        <button type="button" @click="open = !open" class="w-full text-left cursor-pointer p-6 flex flex-col">
                            <div class="w-12 h-12 rounded-xl bg-neon/10 flex items-center justify-center text-neon mb-4 group-hover:scale-110 transition duration-300 border border-neon/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 leading-tight">Community & Network</h3>
                            <p class="text-slate-400 text-sm mb-4 flex-grow leading-relaxed">Terhubung dengan ribuan pelari, pacer, dan komunitas di seluruh Indonesia.</p>
                            
                            <div class="mt-auto flex items-center justify-between text-neon text-sm font-bold pt-4 border-t border-slate-800/50">
                                <span x-text="open ? 'Tutup Menu' : 'Jelajahi Fitur'">Jelajahi Fitur</span>
                                <svg :class="{'rotate-180': open}" class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="bg-slate-950 border-t border-slate-800 px-2 pb-2">
                            <ul class="space-y-1 pt-2">
                                <li>
                                    <a href="{{ route('users.runners') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-neon group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-users text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Runner Profiles</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('pacer.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-neon group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-flag-checkered text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Pacers</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link opacity-50 cursor-not-allowed" title="Coming Soon">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-neon group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-users-rectangle text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Community (Soon)</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('events.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-neon group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-calendar-check text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Events</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link opacity-50 cursor-not-allowed" title="Coming Soon">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-neon group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-handshake text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Collaborations (Soon)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        </div>
                    </div>

                    <!-- Card 3: Event Solutions -->
                    <div x-data="{ open: false }" class="group" data-aos="fade-up" data-aos-delay="200">
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:shadow-neon/10 hover:border-neon/50 transition duration-300 h-full flex flex-col">
                        <button type="button" @click="open = !open" class="w-full text-left cursor-pointer p-6 flex flex-col">
                            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500 mb-4 group-hover:scale-110 transition duration-300 border border-purple-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 leading-tight">Event Solutions</h3>
                            <p class="text-slate-400 text-sm mb-4 flex-grow leading-relaxed">Platform all-in-one untuk manajemen registrasi event lari yang aman.</p>
                            
                            <div class="mt-auto flex items-center justify-between text-purple-400 text-sm font-bold pt-4 border-t border-slate-800/50">
                                <span x-text="open ? 'Tutup Menu' : 'Jelajahi Fitur'">Jelajahi Fitur</span>
                                <svg :class="{'rotate-180': open}" class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="bg-slate-950 border-t border-slate-800 px-2 pb-2">
                            <ul class="space-y-1 pt-2">
                                <li>
                                    <a href="{{ route('eo.landing') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-purple-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-clipboard-check text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Registration System</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('eo.landing') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-purple-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-chart-pie text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Analitik & Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('eo.landing') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-purple-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-shield-alt text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Secured Payment</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        </div>
                    </div>

                    <!-- Card 4: Gear Marketplace -->
                    <div x-data="{ open: false }" class="group" data-aos="fade-up" data-aos-delay="300">
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:shadow-neon/10 hover:border-neon/50 transition duration-300 h-full flex flex-col">
                        <button type="button" @click="open = !open" class="w-full text-left cursor-pointer p-6 flex flex-col">
                            <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 mb-4 group-hover:scale-110 transition duration-300 border border-orange-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 leading-tight">Gear Marketplace</h3>
                            <p class="text-slate-400 text-sm mb-4 flex-grow leading-relaxed">Temukan atau jual perlengkapan lari berkualitas, baru maupun pre-loved.</p>
                            
                            <div class="mt-auto flex items-center justify-between text-orange-400 text-sm font-bold pt-4 border-t border-slate-800/50">
                                <span x-text="open ? 'Tutup Menu' : 'Jelajahi Fitur'">Jelajahi Fitur</span>
                                <svg :class="{'rotate-180': open}" class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="bg-slate-950 border-t border-slate-800 px-2 pb-2">
                            <ul class="space-y-1 pt-2">
                                <li>
                                    <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white transition group/link">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover/link:text-orange-400 group-hover/link:bg-slate-700 transition">
                                            <i class="fas fa-tag text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium">Jual Beli Gear (Konsinasi)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- EVENTS SECTION -->
        <section id="events" class="py-24 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-neon/50 to-transparent"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
                    <div data-aos="fade-right">
                        <span class="text-neon font-bold tracking-widest uppercase text-sm mb-2 block">Race Calendar</span>
                        <h2 class="text-4xl md:text-5xl font-black text-white">UPCOMING <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-500">EVENTS</span></h2>
                    </div>
                    <a href="/events" class="group flex items-center gap-2 text-slate-400 hover:text-white transition font-bold border-b border-slate-700 hover:border-white pb-1" data-aos="fade-left">
                        Lihat Semua Event 
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>

                <div id="homeEvents" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Events Loaded via JS -->
                    <div class="col-span-1 lg:col-span-2 flex flex-col items-center justify-center py-20 text-slate-500">
                        <div class="w-10 h-10 border-4 border-slate-800 border-t-neon rounded-full animate-spin mb-4"></div>
                        <p>Memuat jadwal lari...</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- LEADERBOARD SECTION -->
        <section id="leaderboard" class="py-24 bg-dark relative overflow-hidden">
            <!-- Background Elements -->
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-neon/5 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16" data-aos="fade-up">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-slate-700 bg-slate-800/50 backdrop-blur-sm text-slate-300 text-xs font-bold uppercase tracking-wider mb-4">
                        <svg class="w-4 h-4 text-[#fc4c02]" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                        Powered by Strava
                    </div>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('calendar.strava.connect', ['return_to' => '/#leaderboard']) }}" class="text-[10px] text-slate-500 hover:text-white ml-2">[Admin: Re-Connect Strava]</a>
                        @endif
                    @endauth
                    <h2 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter mb-4">
                        WEEKLY <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400 pr-2">LEADERBOARD</span>
                    </h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Pantau performa terbaik minggu ini dari member <a href="https://www.strava.com/clubs/1859982" target="_blank" class="text-neon hover:underline font-bold">Ruang Lari Club</a> di Strava.
                        Apakah namamu ada di sini?
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @if(isset($leaderboard) && ($leaderboard['fastest'] || $leaderboard['distance'] || $leaderboard['elevation']))
                        
                        <!-- 1. Fastest Pace (Speed Demon) -->
                        @if($leaderboard['fastest'])
                        <div class="group relative" data-aos="fade-up" data-aos-delay="0">
                            <div class="absolute inset-0 bg-gradient-to-b from-blue-500/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 rounded-3xl"></div>
                            <div class="relative bg-slate-900 border border-slate-800 rounded-3xl p-1 overflow-hidden hover:border-blue-500/50 transition duration-300 h-full">
                                <div class="bg-slate-950/50 rounded-[1.3rem] p-6 h-full flex flex-col relative overflow-hidden">
                                    <!-- Rank Badge -->
                                    <div class="absolute top-0 right-0 p-4">
                                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Profile -->
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="relative">
                                            <div class="w-16 h-16 rounded-full p-1 bg-gradient-to-br from-blue-500 to-cyan-400">
                                                <img src="{{ $leaderboard['fastest']['avatar'] }}" class="w-full h-full rounded-full object-cover border-2 border-slate-900">
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full border-2 border-slate-900">#1</div>
                                        </div>
                                        <div>
                                            <h4 class="text-white font-bold text-lg leading-tight">{{ $leaderboard['fastest']['name'] }}</h4>
                                            <p class="text-slate-500 text-xs uppercase tracking-wider font-bold">The Flash</p>
                                        </div>
                                    </div>

                                    <!-- Stats -->
                                    <div class="mt-auto">
                                        <div class="flex items-end justify-between mb-2">
                                            <span class="text-slate-400 text-sm font-medium">Fastest Pace</span>
                                            <span class="text-3xl font-black text-white italic">{{ $leaderboard['fastest']['value'] }} <span class="text-sm text-slate-500 font-normal not-italic">{{ $leaderboard['fastest']['unit'] }}</span></span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-blue-600 to-cyan-400 w-[95%]"></div>
                                        </div>
                                        <p class="text-right text-xs text-blue-400 mt-2 font-mono">5k Time Trial</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 2. Longest Distance (Distance Monster) -->
                        @if($leaderboard['distance'])
                        <div class="group relative transform md:-translate-y-4" data-aos="fade-up" data-aos-delay="100">
                            <!-- Crown/Winner Effect -->
                            <div class="absolute -top-12 left-1/2 -translate-x-1/2 text-4xl animate-bounce">👑</div>
                            <div class="absolute inset-0 bg-gradient-to-b from-neon/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 rounded-3xl"></div>
                            <div class="relative bg-slate-900 border border-slate-800 rounded-3xl p-1 overflow-hidden hover:border-neon/50 transition duration-300 h-full shadow-[0_0_50px_rgba(204,255,0,0.1)]">
                                <div class="bg-slate-950/50 rounded-[1.3rem] p-8 h-full flex flex-col relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-4">
                                        <div class="w-12 h-12 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" /></svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 mb-8">
                                        <div class="relative">
                                            <div class="w-20 h-20 rounded-full p-1 bg-gradient-to-br from-neon to-lime-500 shadow-lg shadow-neon/20">
                                                <img src="{{ $leaderboard['distance']['avatar'] }}" class="w-full h-full rounded-full object-cover border-4 border-slate-900">
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 bg-neon text-dark text-xs font-black px-2 py-0.5 rounded-full border-2 border-slate-900">MVP</div>
                                        </div>
                                        <div>
                                            <h4 class="text-white font-bold text-xl leading-tight">{{ $leaderboard['distance']['name'] }}</h4>
                                            <p class="text-neon text-xs uppercase tracking-wider font-bold">Ultra Runner</p>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <div class="flex items-end justify-between mb-2">
                                            <span class="text-slate-400 text-sm font-medium">Total Distance</span>
                                            <span class="text-4xl font-black text-white italic">{{ $leaderboard['distance']['value'] }} <span class="text-sm text-slate-500 font-normal not-italic">{{ $leaderboard['distance']['unit'] }}</span></span>
                                        </div>
                                        <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-neon to-lime-500 w-[88%]"></div>
                                        </div>
                                        <p class="text-right text-xs text-neon mt-2 font-mono">Week 42 Leader</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 3. Highest Elevation (Mountain Goat) -->
                        @if($leaderboard['elevation'])
                        <div class="group relative" data-aos="fade-up" data-aos-delay="200">
                            <div class="absolute inset-0 bg-gradient-to-b from-purple-500/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 rounded-3xl"></div>
                            <div class="relative bg-slate-900 border border-slate-800 rounded-3xl p-1 overflow-hidden hover:border-purple-500/50 transition duration-300 h-full">
                                <div class="bg-slate-950/50 rounded-[1.3rem] p-6 h-full flex flex-col relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-4">
                                        <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="relative">
                                            <div class="w-16 h-16 rounded-full p-1 bg-gradient-to-br from-purple-500 to-pink-500">
                                                <img src="{{ $leaderboard['elevation']['avatar'] }}" class="w-full h-full rounded-full object-cover border-2 border-slate-900">
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 bg-purple-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full border-2 border-slate-900">#1</div>
                                        </div>
                                        <div>
                                            <h4 class="text-white font-bold text-lg leading-tight">{{ $leaderboard['elevation']['name'] }}</h4>
                                            <p class="text-slate-500 text-xs uppercase tracking-wider font-bold">Mountain Goat</p>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <div class="flex items-end justify-between mb-2">
                                            <span class="text-slate-400 text-sm font-medium">Elevation Gain</span>
                                            <span class="text-3xl font-black text-white italic">{{ $leaderboard['elevation']['value'] }} <span class="text-sm text-slate-500 font-normal not-italic">{{ $leaderboard['elevation']['unit'] }}</span></span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-purple-600 to-pink-500 w-[75%]"></div>
                                        </div>
                                        <p class="text-right text-xs text-purple-400 mt-2 font-mono">Bromo Tengger Semeru</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    @else
                        <!-- Mock Data / Empty State if no real data yet -->
                        <div class="col-span-3 text-center py-10">
                            <div class="inline-block p-6 rounded-2xl bg-slate-800/50 border border-slate-700">
                                <p class="text-slate-400 mb-4">Belum ada data leaderboard minggu ini.</p>
                                <a href="{{ route('calendar.strava.connect', ['return_to' => '/#leaderboard']) }}" class="text-neon font-bold hover:underline">Hubungkan Strava untuk memulai tracking &rarr;</a>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-12 text-center">
                    <a href="https://www.strava.com/clubs/1859982" target="_blank" class="inline-flex items-center gap-2 px-8 py-4 bg-[#fc4c02] text-white font-bold rounded-xl hover:bg-[#e34402] hover:scale-105 transition transform shadow-lg shadow-orange-500/20">
                        <span>GABUNG KLUB STRAVA</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- COMMUNITY / HALL OF FAME -->
        <section id="community" class="py-24 bg-slate-900/50 relative">
            <!-- Skewed Background -->
            <div class="absolute inset-0 transform -skew-y-2 bg-slate-900 z-0 origin-top-left scale-110"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-20" data-aos="fade-up">
                    <h2 class="text-neon font-mono font-bold tracking-[0.2em] uppercase mb-3">Community Elite</h2>
                    <h3 class="text-4xl md:text-6xl font-black text-white uppercase italic tracking-tighter">
                        Hall of <span class="text-stroke">FAME</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Top Runner -->
                    <div class="group relative" data-aos="fade-up" data-aos-delay="100">
                        <div class="absolute inset-0 bg-neon/20 blur-xl opacity-0 group-hover:opacity-100 transition duration-500 rounded-3xl"></div>
                        <div class="relative bg-card border border-slate-700 rounded-3xl overflow-hidden hover:-translate-y-2 transition duration-300 h-full flex flex-col">
                            <div class="absolute top-4 left-4 z-20">
                                <span class="bg-neon text-dark text-xs font-black px-3 py-1 rounded uppercase tracking-wider">Top Runner</span>
                            </div>
                            <div class="h-64 overflow-hidden relative">
                                @if(isset($topRunner))
                                    <img src="{{ $topRunner->banner ? asset('storage/' . $topRunner->banner) : ($topRunner->avatar ? asset('storage/' . $topRunner->avatar) : ($topRunner->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg'))) }}" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                @else
                                    <img src="https://images.unsplash.com/photo-1596727147705-0043c7576566?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent opacity-90"></div>
                                <div class="absolute bottom-4 left-4">
                                    @if(isset($topRunner))
                                        <h4 class="text-2xl font-black text-white italic uppercase leading-none">{{ $topRunner->name }}</h4>
                                    @else
                                        <h4 class="text-2xl font-black text-white italic uppercase leading-none">Top Runner</h4>
                                    @endif
                                </div>
                            </div>
                            <div class="p-6 pt-2 flex-grow flex flex-col justify-between">
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Followers</p>
                                        <p class="text-2xl font-mono font-bold text-white">{{ isset($topRunner) ? number_format($topRunner->followers_count) : '—' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Posts</p>
                                        <p class="text-2xl font-mono font-bold text-neon">{{ isset($topRunner) ? number_format($topRunner->posts_count) : '—' }}</p>
                                    </div>
                                </div>
                                @if(isset($topRunner))
                                <a href="{{ route('runner.profile.show', $topRunner->username ?? $topRunner->id) }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-neon hover:text-dark hover:border-neon transition uppercase text-xs tracking-wider">
                                    View Profile
                                </a>
                                @else
                                <a href="{{ route('users.runners') }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-neon hover:text-dark hover:border-neon transition uppercase text-xs tracking-wider">
                                    View Profile
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Top Pacer -->
                    <div class="group relative" data-aos="fade-up" data-aos-delay="200">
                        <div class="absolute inset-0 bg-blue-500/20 blur-xl opacity-0 group-hover:opacity-100 transition duration-500 rounded-3xl"></div>
                        <div class="relative bg-card border border-slate-700 rounded-3xl overflow-hidden hover:-translate-y-2 transition duration-300 h-full flex flex-col">
                            <div class="absolute top-4 left-4 z-20">
                                <span class="bg-blue-500 text-white text-xs font-black px-3 py-1 rounded uppercase tracking-wider">Top Pacer</span>
                            </div>
                            <div class="h-64 overflow-hidden relative">
                                @if(isset($topPacer))
                                    <img src="{{ $topPacer->image_url ?? ($topPacer->user && $topPacer->user->avatar ? asset('storage/' . $topPacer->user->avatar) : asset('images/default-male.svg')) }}" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                @else
                                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent opacity-90"></div>
                                <div class="absolute bottom-4 left-4">
                                    @if(isset($topPacer))
                                        <h4 class="text-2xl font-black text-white italic uppercase leading-none">{{ $topPacer->nickname ?? ($topPacer->user->name ?? 'Top Pacer') }}</h4>
                                    @else
                                        <h4 class="text-2xl font-black text-white italic uppercase leading-none">Top Pacer</h4>
                                    @endif
                                </div>
                            </div>
                            <div class="p-6 pt-2 flex-grow flex flex-col justify-between">
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Events</p>
                                        <p class="text-2xl font-mono font-bold text-white">{{ isset($topPacer) ? number_format($topPacer->total_races) : '—' }}<span class="text-sm text-slate-500">races</span></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Verified</p>
                                        <p class="text-2xl font-mono font-bold text-blue-400">{{ isset($topPacer) ? ($topPacer->verified ? 'Yes' : 'No') : '—' }}</p>
                                    </div>
                                </div>
                                @if(isset($topPacer))
                                <a href="{{ route('pacer.show', $topPacer->seo_slug ?? $topPacer->id) }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-blue-500 hover:text-white hover:border-blue-500 transition uppercase text-xs tracking-wider">
                                    Book Pacer
                                </a>
                                @else
                                <a href="{{ route('pacer.index') }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-blue-500 hover:text-white hover:border-blue-500 transition uppercase text-xs tracking-wider">
                                    Book Pacer
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Top Coach -->
                    <div class="group relative" data-aos="fade-up" data-aos-delay="300">
                        <div class="absolute inset-0 bg-purple-500/20 blur-xl opacity-0 group-hover:opacity-100 transition duration-500 rounded-3xl"></div>
                        <div class="relative bg-card border border-slate-700 rounded-3xl overflow-hidden hover:-translate-y-2 transition duration-300 h-full flex flex-col">
                            <div class="absolute top-4 left-4 z-20">
                                <span class="bg-white text-dark text-xs font-black px-3 py-1 rounded uppercase tracking-wider">Top Coach</span>
                            </div>
                            <div class="h-64 overflow-hidden relative">
                                @if(isset($topCoach))
                                    <img src="{{ $topCoach->banner ? asset('storage/' . $topCoach->banner) : ($topCoach->avatar ? asset('storage/' . $topCoach->avatar) : asset('images/default-male.svg')) }}" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                @else
                                    <img src="https://images.unsplash.com/photo-1571008887538-b36bb32f4571?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent opacity-90"></div>
                                <div class="absolute bottom-4 left-4">
                                    @if(isset($topCoach))
                                        <h4 class="text-2xl font-black text-white italic uppercase leading-none">{{ $topCoach->name }}</h4>
                                    @else
                                        <h4 class="text-2xl font-black text-white italic uppercase leading-none">Top Coach</h4>
                                    @endif
                                </div>
                            </div>
                            <div class="p-6 pt-2 flex-grow flex flex-col justify-between">
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Students</p>
                                        <p class="text-2xl font-mono font-bold text-white">{{ isset($topCoachData) ? number_format($topCoachData->students_count) : '—' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Programs</p>
                                        <p class="text-2xl font-mono font-bold text-white">{{ isset($topCoach) ? number_format($topCoach->programs()->count()) : '—' }}</p>
                                    </div>
                                </div>
                                @if(isset($topCoach))
                                <a href="{{ route('runner.profile.show', $topCoach->username ?? $topCoach->id) }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-white hover:text-dark hover:border-white transition uppercase text-xs tracking-wider">
                                    Join Class
                                </a>
                                @else
                                <a href="{{ url('/users?role=coach') }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-white hover:text-dark hover:border-white transition uppercase text-xs tracking-wider">
                                    Join Class
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING SECTION -->
        <section id="pricing" class="py-32 relative hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-br from-neon via-lime-500 to-emerald-600 rounded-[3rem] p-8 md:p-20 text-center relative overflow-hidden shadow-[0_20px_100px_-20px_rgba(204,255,0,0.3)]">
                    
                    <!-- Decorative Circles -->
                    <div class="absolute top-0 left-0 w-96 h-96 bg-white/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-black/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

                    <div class="relative z-10 max-w-5xl mx-auto">
                        <h2 class="text-3xl md:text-6xl font-black text-dark mb-6 tracking-tight">UNLOCK PREMIUM FEATURES</h2>
                        <p class="text-dark/80 text-lg md:text-xl mb-12 max-w-2xl mx-auto font-medium leading-relaxed">
                            Dapatkan akses ke rencana latihan eksklusif, analisis performa mendalam, dan diskon pendaftaran event.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                            <!-- Free Plan -->
                            <div class="bg-dark/90 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-white/30 transition group h-full flex flex-col">
                                <div class="mb-auto">
                                    <h3 class="text-lg font-bold text-white mb-2">Starter</h3>
                                    <div class="flex items-baseline mb-6">
                                        <span class="text-4xl font-black text-white">Rp 0</span>
                                    </div>
                                    <ul class="space-y-4 text-sm text-slate-300 mb-8">
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Basic Tracking</li>
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Community Access</li>
                                        <li class="flex gap-3 text-slate-600"><svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Advanced Stats</li>
                                    </ul>
                                </div>
                                <a href="{{ url('/register') }}" class="w-full py-4 rounded-xl bg-slate-800 hover:bg-white hover:text-dark font-bold transition text-white text-center text-sm uppercase tracking-wider">Start Free</a>
                            </div>

                            <!-- Pro Plan -->
                            <div class="bg-white text-dark p-8 rounded-3xl shadow-2xl relative transform md:-translate-y-4 h-full flex flex-col border-4 border-white/50">
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-dark text-neon border border-neon text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest">Most Popular</div>
                                <div class="mb-auto">
                                    <h3 class="text-lg font-bold mb-2">Pro Runner</h3>
                                    <div class="flex items-baseline mb-6">
                                        <span class="text-4xl font-black">Rp 49k</span>
                                        <span class="text-sm font-medium text-slate-500 ml-1">/bln</span>
                                    </div>
                                    <ul class="space-y-4 text-sm font-medium mb-8 text-slate-700">
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Advanced Analytics</li>
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Training Plans</li>
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Event Discounts</li>
                                    </ul>
                                </div>
                                <a href="{{ url('/membership') }}" class="w-full py-4 rounded-xl bg-dark text-neon hover:bg-slate-800 font-black transition text-center text-sm uppercase tracking-wider shadow-lg">Upgrade Now</a>
                            </div>

                            <!-- Coach Plan -->
                            <div class="bg-dark/90 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-white/30 transition h-full flex flex-col">
                                <div class="mb-auto">
                                    <h3 class="text-lg font-bold text-white mb-2">Elite Coach</h3>
                                    <div class="flex items-baseline mb-6">
                                        <span class="text-4xl font-black text-white">Rp 199k</span>
                                        <span class="text-sm text-slate-400 ml-1">/bln</span>
                                    </div>
                                    <ul class="space-y-4 text-sm text-slate-300 mb-8">
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> All Pro Features</li>
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Manage Students</li>
                                        <li class="flex gap-3"><svg class="w-5 h-5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Verified Badge</li>
                                    </ul>
                                </div>
                                <a href="{{ url('/contact') }}" class="w-full py-4 rounded-xl bg-slate-800 hover:bg-white hover:text-dark font-bold transition text-white text-center text-sm uppercase tracking-wider">Contact Sales</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BLOG SECTION -->
        <section id="blog" class="py-24 bg-dark">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
                    <div>
                        <span class="text-neon font-bold tracking-widest uppercase text-sm mb-2 block">Latest Updates</span>
                        <h2 class="text-4xl font-black text-white">NEWS & <span class="text-stroke">ARTICLES</span></h2>
                    </div>
                    <a href="{{ url('/#blog') }}" class="text-sm font-bold text-white hover:text-neon transition flex items-center gap-2 border-b border-transparent hover:border-neon pb-1">
                        Lihat Artikel Terbaru <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
                <div id="blogCards" class="grid grid-cols-1 md:grid-cols-3 gap-8"></div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .text-stroke { -webkit-text-stroke: 1px rgba(255,255,255,0.2); color: transparent; }
    .bg-hero-glow { background-image: radial-gradient(circle at center, rgba(204,255,0,0.1) 0%, transparent 60%); }
    .animate-float { animation: floaty 3s ease-in-out infinite; }
    .animate-pulse-slow { animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes floaty { 
        0%, 100% { transform: translateY(0) rotate(12deg); } 
        50% { transform: translateY(-10px) rotate(12deg); } 
    }
</style>
@endpush

@push('scripts')
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({duration:800, once:true, offset:50});

    async function loadLatestBlogs(){
        const c=document.getElementById('blogCards');
        if(!c)return;
        c.innerHTML='<div class="col-span-3 text-center text-slate-500 animate-pulse">Memuat artikel...</div>';
        try{
            const base=(window.location.pathname.indexOf('/ruanglari/public')!==-1)?'/ruanglari/public':'';
            const r=await fetch(base+'/api/blog/latest');
            const p=await r.json();
            c.innerHTML='';
            if(!p || p.length===0){
                c.innerHTML='<div class="col-span-3 text-center text-slate-500 py-10 border border-dashed border-slate-800 rounded-2xl bg-slate-900/40">Belum ada artikel yang dipublikasikan.</div>';
                return;
            }
            p.forEach((post, i)=>{
                const l=post.url||'#';
                const t=post.title||'Tanpa judul';
                const img=post.image||'ruanglari.png';
                const date=new Date(post.date).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
                
                const card=document.createElement('div');
                card.className='group bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-600 transition duration-300 flex flex-col h-full';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', i * 100);
                
                card.innerHTML=`
                    <a href="${l}" target="${l === '#' ? '_self' : '_blank'}" rel="noopener" class="block overflow-hidden relative aspect-video">
                        <img src="${img}" alt="${t.replace(/<[^>]*>/g,'')}" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-110" />
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition"></div>
                    </a>
                    <div class="p-6 flex-grow flex flex-col">
                        <div class="text-xs text-neon font-bold mb-2 uppercase tracking-wider">${date}</div>
                        <a href="${l}" target="${l === '#' ? '_self' : '_blank'}" rel="noopener" class="text-lg font-bold text-white mb-3 hover:text-neon transition line-clamp-2 leading-tight">
                            ${t}
                        </a>
                        <div class="mt-auto pt-4 border-t border-slate-800">
                            <a href="${l}" target="${l === '#' ? '_self' : '_blank'}" rel="noopener" class="text-sm text-slate-400 group-hover:text-white transition flex items-center gap-2">
                                Baca Selengkapnya <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                            </a>
                        </div>
                    </div>
                `;
                c.appendChild(card);
            });
        }catch(e){
            c.innerHTML='<div class="col-span-3 text-center text-red-500 py-10 bg-slate-900 rounded-xl border border-red-900/50">Gagal memuat artikel. Silakan coba lagi nanti.</div>';
        }
    }

    async function loadUpcomingEvents(){
        const c=document.getElementById('homeEvents');
        if(!c)return;
        try{
            const base=(window.location.pathname.indexOf('/ruanglari/public')!==-1)?'/ruanglari/public':'';
            const r=await fetch(base+'/api/events/upcoming');
            const events=await r.json();
            c.innerHTML='';
            
            if(!events||events.length===0){
                c.innerHTML='<div class="col-span-2 text-center text-slate-500 py-10 border border-dashed border-slate-800 rounded-2xl">Belum ada event mendatang.</div>';
                return;
            }
            
            events.forEach((ev, i)=>{
                const d=new Date(ev.date+'T'+(ev.time||'00:00'));
                const m=d.toLocaleString('id-ID',{month:'short'}).toUpperCase();
                const day=String(d.getDate()).padStart(2,'0');
                
                const card=document.createElement('a');
                card.href = ev.url;
                card.className='group relative bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-neon/50 transition duration-300 flex';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', i * 100);
                
                card.innerHTML=`
                    <div class="absolute inset-0 bg-gradient-to-r from-neon/5 to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
                    <div class="w-24 md:w-32 bg-slate-950 flex flex-col items-center justify-center text-center p-4 border-r border-slate-800 group-hover:border-neon/30 transition z-10">
                        <span class="text-xs md:text-sm font-bold text-neon uppercase tracking-wider mb-1">${m}</span>
                        <span class="text-3xl md:text-4xl font-black text-white">${day}</span>
                    </div>
                    <div class="p-5 flex-grow flex flex-col justify-center relative z-10">
                        <h3 class="text-lg md:text-xl font-bold text-white mb-2 group-hover:text-neon transition line-clamp-1">${ev.name}</h3>
                        <div class="flex items-center gap-4 text-xs md:text-sm text-slate-500">
                            <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> ${ev.location||'TBA'}</span>
                            <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> ${ev.time||'TBA'} WIB</span>
                        </div>
                    </div>
                    <div class="w-12 md:w-16 flex items-center justify-center border-l border-slate-800 bg-slate-900/50 group-hover:bg-neon group-hover:text-dark transition z-10">
                        <svg class="w-6 h-6 transform -rotate-45 group-hover:rotate-0 transition duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
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
</script>
@endpush
