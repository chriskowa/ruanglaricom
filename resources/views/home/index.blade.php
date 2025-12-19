@extends('layouts.pacerhub')

@section('content')
    <div id="home-app" class="overflow-x-hidden">
        
        <!-- HERO SECTION -->
        <header class="relative min-h-screen flex items-center justify-center pt-20 md:pt-0">
            <!-- Dynamic Background -->
            <div class="absolute inset-0 bg-dark z-0">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-dark to-black opacity-80"></div>
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
                            LARI TANPA <br class="hidden md:block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon via-green-400 to-emerald-500">BATAS.</span>
                        </h1>
                        
                        <p class="text-slate-400 text-lg md:text-xl mb-10 max-w-lg mx-auto md:mx-0 leading-relaxed font-light">
                            Platform all-in-one untuk pelari, pacer, dan pelatih. Temukan event, pantau progres, dan raih personal best Anda bersama <span class="text-white font-bold">Ruang Lari</span>.
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
                                <p class="text-sm text-slate-400"><span class="text-white font-bold">2,000+</span> Pelari Bergabung</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Image -->
                    <div class="relative order-1 md:order-2" data-aos="fade-left" data-aos-delay="200">
                        <div class="relative z-10 rounded-[2.5rem] overflow-hidden border-8 border-slate-800/50 shadow-2xl rotate-3 hover:rotate-0 transition duration-700 group">
                            <div class="absolute inset-0 bg-gradient-to-t from-dark/80 via-transparent to-transparent z-10"></div>
                            <img src="https://res.cloudinary.com/dslfarxct/images/v1766050868/542301374_18517775974013478_1186867397282832240_n/542301374_18517775974013478_1186867397282832240_n.jpg" alt="Runner" class="w-full h-[500px] object-cover object-top transform group-hover:scale-110 transition duration-1000">
                            
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
                                <img src="https://images.unsplash.com/photo-1596727147705-0043c7576566?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent opacity-90"></div>
                                <div class="absolute bottom-4 left-4">
                                    <h4 class="text-2xl font-black text-white italic uppercase leading-none">Sarah <br>Jenner</h4>
                                </div>
                            </div>
                            <div class="p-6 pt-2 flex-grow flex flex-col justify-between">
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Total Dist</p>
                                        <p class="text-2xl font-mono font-bold text-white">2,450<span class="text-sm text-slate-500">km</span></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Avg Pace</p>
                                        <p class="text-2xl font-mono font-bold text-neon">4:15</p>
                                    </div>
                                </div>
                                <a href="{{ route('pacer.index') }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-neon hover:text-dark hover:border-neon transition uppercase text-xs tracking-wider">
                                    View Profile
                                </a>
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
                                <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent opacity-90"></div>
                                <div class="absolute bottom-4 left-4">
                                    <h4 class="text-2xl font-black text-white italic uppercase leading-none">Budi <br>Santoso</h4>
                                </div>
                            </div>
                            <div class="p-6 pt-2 flex-grow flex flex-col justify-between">
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Events</p>
                                        <p class="text-2xl font-mono font-bold text-white">15<span class="text-sm text-slate-500">races</span></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Accuracy</p>
                                        <p class="text-2xl font-mono font-bold text-blue-400">99.8%</p>
                                    </div>
                                </div>
                                <a href="{{ route('pacer.index') }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-blue-500 hover:text-white hover:border-blue-500 transition uppercase text-xs tracking-wider">
                                    Book Pacer
                                </a>
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
                                <img src="https://images.unsplash.com/photo-1571008887538-b36bb32f4571?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 scale-105 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent opacity-90"></div>
                                <div class="absolute bottom-4 left-4">
                                    <h4 class="text-2xl font-black text-white italic uppercase leading-none">Coach <br>Indra</h4>
                                </div>
                            </div>
                            <div class="p-6 pt-2 flex-grow flex flex-col justify-between">
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Students</p>
                                        <p class="text-2xl font-mono font-bold text-white">120+</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">PB Broken</p>
                                        <p class="text-2xl font-mono font-bold text-white">50+</p>
                                    </div>
                                </div>
                                <a href="{{ url('/users?role=coach') }}" class="w-full py-3 rounded-xl border border-slate-600 text-white font-bold text-center hover:bg-white hover:text-dark hover:border-white transition uppercase text-xs tracking-wider">
                                    Join Class
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING SECTION -->
        <section id="pricing" class="py-32 relative">
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
                    <a href="https://ruanglari.com/blog/" target="_blank" rel="noopener" class="text-sm font-bold text-white hover:text-neon transition flex items-center gap-2 border-b border-transparent hover:border-neon pb-1">
                        Read Our Blog <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
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
            const r=await fetch('https://ruanglari.com/wp-json/wp/v2/posts?per_page=3&_embed');
            const p=await r.json();
            c.innerHTML='';
            p.forEach((post, i)=>{
                const l=post.link;
                const t=post.title?.rendered||'Tanpa judul';
                const m=post._embedded?.['wp:featuredmedia']?.[0];
                const img=m?.source_url||'ruanglari.png';
                const date=new Date(post.date).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
                
                const card=document.createElement('div');
                card.className='group bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-600 transition duration-300 flex flex-col h-full';
                card.setAttribute('data-aos', 'fade-up');
                card.setAttribute('data-aos-delay', i * 100);
                
                card.innerHTML=`
                    <a href="${l}" target="_blank" rel="noopener" class="block overflow-hidden relative aspect-video">
                        <img src="${img}" alt="${t.replace(/<[^>]*>/g,'')}" class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-110" />
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition"></div>
                    </a>
                    <div class="p-6 flex-grow flex flex-col">
                        <div class="text-xs text-neon font-bold mb-2 uppercase tracking-wider">${date}</div>
                        <a href="${l}" target="_blank" rel="noopener" class="text-lg font-bold text-white mb-3 hover:text-neon transition line-clamp-2 leading-tight">
                            ${t}
                        </a>
                        <div class="mt-auto pt-4 border-t border-slate-800">
                            <a href="${l}" target="_blank" rel="noopener" class="text-sm text-slate-400 group-hover:text-white transition flex items-center gap-2">
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
                card.href='/events/'+ev.slug;
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
