@extends('layouts.pacerhub')

@section('title', $title)

@push('styles')
<script>
    if (typeof tailwind !== 'undefined' && tailwind && tailwind.config) {
        tailwind.config.theme = tailwind.config.theme || {};
        tailwind.config.theme.extend = tailwind.config.theme.extend || {};
        tailwind.config.theme.extend.colors = tailwind.config.theme.extend.colors || {};
        tailwind.config.theme.extend.colors.neon = '#ccff00';
    }
</script>
<style>
    .glass-frosted {
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }
    .solid-sharp {
        background: #1e293b;
        border: 1px solid #334155;
        box-shadow: 0 14px 30px -5px rgba(0, 0, 0, 0.5);
    }
    .glass-btn {
        background: rgba(204, 255, 0, 0.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(204, 255, 0, 0.4);
        color: #ffffff;
    }
    .glass-btn:hover {
        background: rgba(204, 255, 0, 0.25);
        border-color: #ccff00;
        box-shadow: 0 0 20px rgba(204, 255, 0, 0.3);
    }
    .solid-neon-btn {
        background: #ccff00;
        color: #0f172a;
    }
    .solid-neon-btn:hover {
        background: #b8e600;
        box-shadow: 0 0 20px rgba(204, 255, 0, 0.4);
    }
    [v-cloak] { display: none; }
</style>
@endpush

@section('content')
<div id="users-app" class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-[#0b1220] text-slate-200" v-cloak>
    <div class="max-w-7xl mx-auto space-y-10">
        
        <!-- Header & Style Controls -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 pb-4 border-b border-slate-800/80">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-neon animate-pulse"></span>
                    <p class="text-neon font-mono text-xs tracking-widest uppercase font-bold">RuangLari Athletes Network</p>
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tight">
                    FIND <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon via-lime-300 to-emerald-400">{{ strtoupper(str_replace('Daftar ', '', $title)) }}</span>
                </h1>
                <p class="text-slate-400 text-sm mt-1 max-w-xl">
                    Jelajahi profil pelari dan pelatih lari berprestasi dengan statistik performa, sepatu lari, dan program latihan.
                </p>
            </div>
            
            <!-- Controls Toolbar -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Theme Mode Switcher -->
                <div class="inline-flex p-1 bg-slate-900/90 border border-slate-800 rounded-xl">
                    <button @click="cardTheme = 'contrast'" 
                            :class="cardTheme === 'contrast' ? 'bg-slate-800 text-neon font-bold border border-slate-700' : 'text-slate-400 hover:text-white'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                            title="Bandingkan Desain Sharp Solid & Frosted Glass">
                        <span>Side-by-Side</span>
                    </button>
                    <button @click="cardTheme = 'solid'" 
                            :class="cardTheme === 'solid' ? 'bg-slate-800 text-neon font-bold border border-slate-700' : 'text-slate-400 hover:text-white'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition-all"
                            title="Tampilan Sharp Solid Card">
                        <span>Sharp Solid</span>
                    </button>
                    <button @click="cardTheme = 'glass'" 
                            :class="cardTheme === 'glass' ? 'bg-slate-800 text-neon font-bold border border-slate-700' : 'text-slate-400 hover:text-white'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition-all"
                            title="Tampilan Frosted Glassmorphism Card">
                        <span>Frosted Glass</span>
                    </button>
                </div>

                <!-- Filter Toggle (Mobile) -->
                <button class="md:hidden px-4 py-2 bg-slate-800 rounded-xl text-white text-xs font-bold border border-slate-700 flex items-center gap-2" 
                        @click="showFilters = !showFilters">
                    <svg class="w-4 h-4 text-neon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    <span>Filter</span>
                </button>
            </div>
        </div>

        <!-- Featured Runner Card Comparison Showcase (Side-by-Side) -->
        <div v-if="cardTheme === 'contrast' && !filters.q && filters.page === 1" class="relative rounded-3xl p-6 md:p-8 bg-gradient-to-b from-slate-900/80 to-[#0f172a] border border-slate-800/80 overflow-hidden shadow-2xl">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-neon/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                <div class="text-center mb-8">
                    <span class="px-3 py-1 bg-neon/10 border border-neon/30 text-neon rounded-full text-xs font-mono font-bold tracking-wide uppercase">
                        Design Showcase Contrast
                    </span>
                    <h2 class="text-2xl md:text-3xl font-black text-white mt-2">
                        Sharp Solid vs Frosted Glassmorphism
                    </h2>
                    <p class="text-slate-400 text-xs md:text-sm mt-1">
                        Dua variasi desain kartu profil atlet lari dengan portrait lintasan lari dan rincian statistik minimalis.
                    </p>
                </div>

                <!-- Side by Side Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 justify-center">
                    
                    <!-- Left: Sharp Solid Card -->
                    <div class="solid-sharp rounded-2xl p-5 flex flex-col justify-between transition-transform duration-300 hover:-translate-y-1">
                        <div>
                            <!-- Header Style Label -->
                            <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 mb-3 pb-2 border-b border-slate-700/60">
                                <span>CARD A: SOLID DESIGN</span>
                                <span class="text-slate-500">#1e293b</span>
                            </div>

                            <!-- Portrait / Photo Section -->
                            <div class="relative w-full h-52 rounded-xl overflow-hidden mb-4 bg-slate-800 border border-slate-700">
                                <img src="{{ asset('images/paolo/bg-hero.webp') }}" 
                                     onerror="this.src='https://images.unsplash.com/photo-1502680390469-be75c86b636f?auto=format&fit=crop&w=800&q=80'"
                                     class="w-full h-full object-cover object-center" 
                                     alt="Muhammad Emon">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/30 to-transparent"></div>
                                <div class="absolute top-3 right-3 bg-slate-900/90 text-white text-[10px] font-black px-2.5 py-1 rounded-full border border-slate-700 shadow-md">
                                    RUNNER
                                </div>
                            </div>

                            <!-- Name -->
                            <h3 class="text-xl font-black text-white tracking-tight mb-3">Muhammad Emon</h3>

                            <!-- Stats (Minimalist SVG Icons) -->
                            <div class="space-y-2 mb-5">
                                <div class="flex items-center gap-2.5 text-slate-300 text-xs font-medium">
                                    <!-- Running Shoe Minimalist SVG -->
                                    <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 17l6-6 4 2 6-4v5l-5 4H4v-1z"/>
                                        <path d="M7 11l3-3"/>
                                        <path d="M11 9l2-2"/>
                                        <path d="M2 20h20"/>
                                    </svg>
                                    <span>Sepatu Lari: <strong class="text-white font-mono">145 km</strong></span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300 text-xs font-medium">
                                    <!-- Stopwatch Minimalist SVG -->
                                    <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="14" r="8"/>
                                        <line x1="12" y1="14" x2="12" y2="10"/>
                                        <line x1="12" y1="14" x2="15" y2="14"/>
                                        <path d="M12 2v4"/>
                                        <path d="M10 2h4"/>
                                        <path d="M19 7l1.5-1.5"/>
                                    </svg>
                                    <span>Waktu: <strong class="text-white font-mono">12h</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Solid Neon Action Button -->
                        <button class="solid-neon-btn w-full py-3 rounded-xl font-black text-sm transition-all flex items-center justify-center gap-2 shadow-lg">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            <span>Ikuti +</span>
                        </button>
                    </div>

                    <!-- Right: Frosted Glassmorphism Card -->
                    <div class="glass-frosted rounded-2xl p-5 flex flex-col justify-between transition-transform duration-300 hover:-translate-y-1 relative">
                        <!-- Glow highlight -->
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon/15 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10">
                            <!-- Header Style Label -->
                            <div class="flex items-center justify-between text-[11px] font-mono text-neon/80 mb-3 pb-2 border-b border-white/10">
                                <span>CARD B: FROSTED GLASS</span>
                                <span class="text-slate-400">Glassmorphism</span>
                            </div>

                            <!-- Portrait / Photo Section -->
                            <div class="relative w-full h-52 rounded-xl overflow-hidden mb-4 bg-slate-800/60 border border-white/15">
                                <img src="{{ asset('images/paolo/bg-hero.webp') }}" 
                                     onerror="this.src='https://images.unsplash.com/photo-1502680390469-be75c86b636f?auto=format&fit=crop&w=800&q=80'"
                                     class="w-full h-full object-cover object-center" 
                                     alt="Muhammad Emon">
                                <div class="absolute inset-0 bg-gradient-to-t from-dark/95 via-dark/40 to-transparent"></div>
                                <div class="absolute top-3 right-3 bg-slate-900/60 backdrop-blur-md text-neon text-[10px] font-black px-2.5 py-1 rounded-full border border-neon/30 shadow-md">
                                    RUNNER
                                </div>
                            </div>

                            <!-- Name -->
                            <h3 class="text-xl font-black text-white tracking-tight mb-3">Muhammad Emon</h3>

                            <!-- Stats (Minimalist SVG Icons) -->
                            <div class="space-y-2 mb-5">
                                <div class="flex items-center gap-2.5 text-slate-300 text-xs font-medium">
                                    <!-- Running Shoe Minimalist SVG -->
                                    <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 17l6-6 4 2 6-4v5l-5 4H4v-1z"/>
                                        <path d="M7 11l3-3"/>
                                        <path d="M11 9l2-2"/>
                                        <path d="M2 20h20"/>
                                    </svg>
                                    <span>Sepatu Lari: <strong class="text-white font-mono">145 km</strong></span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300 text-xs font-medium">
                                    <!-- Stopwatch Minimalist SVG -->
                                    <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="14" r="8"/>
                                        <line x1="12" y1="14" x2="12" y2="10"/>
                                        <line x1="12" y1="14" x2="15" y2="14"/>
                                        <path d="M12 2v4"/>
                                        <path d="M10 2h4"/>
                                        <path d="M19 7l1.5-1.5"/>
                                    </svg>
                                    <span>Waktu: <strong class="text-white font-mono">12h</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Frosted Glass Action Button -->
                        <button class="glass-btn w-full py-3 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg relative z-10">
                            <svg class="w-4 h-4 text-neon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            <span>Ikuti +</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div v-show="showFilters" class="glass-frosted rounded-2xl p-5 md:p-6 transition-all" data-aos="fade-up">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Cari Nama / Email</label>
                    <div class="relative">
                        <input v-model="filters.q" 
                               @input="debouncedFetch" 
                               type="text" 
                               placeholder="Ketik nama runner..." 
                               class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-4 py-2.5 text-white text-sm focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Gender</label>
                    <select v-model="filters.gender" 
                            @change="fetchUsers" 
                            class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-4 py-2.5 text-white text-sm focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all">
                        <option value="">Semua Gender</option>
                        <option value="male">Male (Pria)</option>
                        <option value="female">Female (Wanita)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Lokasi Kota</label>
                    <select v-model="filters.city_id" 
                            @change="fetchUsers" 
                            class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-4 py-2.5 text-white text-sm focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all">
                        <option value="">Semua Kota</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                            @{{ city.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="resetFilters" 
                            class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-bold rounded-xl transition-all border border-slate-700 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                            <path d="M21 3v5h-5"/>
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                            <path d="M8 16H3v5"/>
                        </svg>
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State Skeleton -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="n in 8" :key="n" class="solid-sharp rounded-2xl p-5 animate-pulse flex flex-col justify-between h-[420px]">
                <div>
                    <div class="w-full h-48 bg-slate-800 rounded-xl mb-4"></div>
                    <div class="h-5 bg-slate-800 rounded w-3/4 mb-3"></div>
                    <div class="h-3 bg-slate-800 rounded w-1/2 mb-2"></div>
                    <div class="h-3 bg-slate-800 rounded w-1/3 mb-4"></div>
                </div>
                <div class="h-10 bg-slate-800 rounded-xl w-full"></div>
            </div>
        </div>

        <!-- Runner Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="(user, index) in users.data" 
                 :key="user.id" 
                 :class="getCardClass(index)"
                 class="rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 relative group overflow-hidden">
                
                <!-- Glow highlight for glass cards -->
                <div v-if="isGlassCard(index)" class="absolute -top-12 -right-12 w-32 h-32 bg-neon/10 rounded-full blur-2xl pointer-events-none group-hover:bg-neon/20 transition-colors"></div>

                <div class="relative z-10">
                    <!-- Top Section: Photo & Badge -->
                    <div class="relative w-full h-52 rounded-xl overflow-hidden mb-4 bg-slate-800 border border-slate-700/60">
                        <img :src="user.avatar ? (APP_URL + '/storage/' + user.avatar) : (user.gender === 'female' ? defaultFemale : defaultMale)" 
                             loading="lazy"
                             decoding="async"
                             class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" 
                             :alt="user.name">
                        <div class="absolute inset-0 bg-gradient-to-t from-dark/95 via-dark/30 to-transparent"></div>
                        
                        <!-- Role Badge -->
                        <div class="absolute top-3 right-3">
                            <span v-if="user.role === 'coach'" class="bg-blue-500/90 backdrop-blur-md text-white text-[10px] font-black px-2.5 py-1 rounded-full border border-blue-400/40 shadow-md">
                                COACH
                            </span>
                            <span v-else-if="user.role === 'eo'" class="bg-purple-500/90 backdrop-blur-md text-white text-[10px] font-black px-2.5 py-1 rounded-full border border-purple-400/40 shadow-md">
                                EO
                            </span>
                            <span v-else class="bg-slate-900/80 backdrop-blur-md text-slate-200 text-[10px] font-black px-2.5 py-1 rounded-full border border-slate-700 shadow-md">
                                RUNNER
                            </span>
                        </div>

                        <!-- City Pill (Bottom Left of photo) -->
                        <div v-if="user.city" class="absolute bottom-2.5 left-2.5 flex items-center gap-1 text-[11px] text-slate-300 bg-slate-900/80 backdrop-blur-md px-2 py-0.5 rounded-lg border border-slate-800">
                            <svg class="w-3 h-3 text-neon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="truncate max-w-[120px]">@{{ user.city.name }}</span>
                        </div>
                    </div>

                    <!-- Runner Name -->
                    <h3 class="text-lg font-black text-white tracking-tight mb-2 truncate group-hover:text-neon transition-colors">
                        <a :href="'/runner/' + (user.username || user.id)">@{{ user.name }}</a>
                    </h3>

                    <!-- Statistics: Minimalist SVG Icons (Shoe & Stopwatch) -->
                    <div class="space-y-1.5 mb-5 text-xs text-slate-300">
                        <!-- Running Shoe Mileage -->
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 17l6-6 4 2 6-4v5l-5 4H4v-1z"/>
                                <path d="M7 11l3-3"/>
                                <path d="M11 9l2-2"/>
                                <path d="M2 20h20"/>
                            </svg>
                            <span>Sepatu Lari: <strong class="text-white font-mono">@{{ formatShoeKm(user) }}</strong></span>
                        </div>

                        <!-- Running Time / Activity -->
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="14" r="8"/>
                                <line x1="12" y1="14" x2="12" y2="10"/>
                                <line x1="12" y1="14" x2="15" y2="14"/>
                                <path d="M12 2v4"/>
                                <path d="M10 2h4"/>
                                <path d="M19 7l1.5-1.5"/>
                            </svg>
                            <span>Waktu: <strong class="text-white font-mono">@{{ formatTimeHours(user) }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Action Button: "Ikuti +" & Quick Profile -->
                <div class="relative z-10 flex items-center gap-2 pt-2 border-t border-slate-800/80">
                    <template v-if="currentUserId !== user.id">
                        <!-- Follow / Unfollow Button with Plus icon -->
                        <button v-if="user.is_following" 
                                @click="toggleFollow(user)" 
                                :disabled="actionLoading === user.id" 
                                class="flex-grow py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-red-500/20 hover:text-red-400 text-slate-300 text-xs font-bold border border-slate-700 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50">
                            <i v-if="actionLoading === user.id" class="fas fa-spinner fa-spin"></i>
                            <span v-else>Mengikuti ✓</span>
                        </button>
                        
                        <button v-else 
                                @click="toggleFollow(user)" 
                                :disabled="actionLoading === user.id" 
                                :class="isGlassCard(index) ? 'glass-btn' : 'solid-neon-btn'"
                                class="flex-grow py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 shadow-md">
                            <i v-if="actionLoading === user.id" class="fas fa-spinner fa-spin"></i>
                            <template v-else>
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                <span>Ikuti +</span>
                            </template>
                        </button>

                        <!-- Message Button -->
                        <a :href="'/chat/' + user.id" 
                           class="w-10 h-10 rounded-xl bg-slate-800/90 hover:bg-cyan-500/20 hover:text-cyan-400 text-slate-400 border border-slate-700/80 flex items-center justify-center transition-all flex-shrink-0" 
                           title="Kirim Pesan">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </a>
                    </template>
                    
                    <a v-else 
                       :href="'/runner/' + (user.username || user.id)" 
                       class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold text-center border border-slate-700 transition-all">
                        Lihat Profil Saya
                    </a>
                </div>

            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && users.data.length === 0" class="col-span-full py-20 text-center glass-frosted rounded-3xl p-8">
            <div class="w-16 h-16 bg-slate-800/80 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-500 border border-slate-700">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-1">Runner Tidak Ditemukan</h3>
            <p class="text-slate-400 text-xs max-w-sm mx-auto mb-4">Coba sesuaikan kata kunci pencarian atau reset filter lokasi untuk menemukan profil lain.</p>
            <button @click="resetFilters" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-neon text-xs font-bold rounded-xl border border-slate-700 transition-all">
                Reset Filter
            </button>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-12 flex justify-center">
            <nav class="flex items-center gap-2 p-1 bg-slate-900/90 border border-slate-800 rounded-2xl">
                <button @click="changePage(users.current_page - 1)" 
                        :disabled="users.current_page === 1" 
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
                
                <span class="text-xs text-slate-400 px-3 font-mono">
                    Halaman <strong class="text-white">@{{ users.current_page }}</strong> dari @{{ users.last_page }}
                </span>

                <button @click="changePage(users.current_page + 1)" 
                        :disabled="users.current_page === users.last_page" 
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </nav>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, onMounted, watch } = Vue;

    createApp({
        setup() {
            const users = ref(@json($users));
            const cities = ref(@json($cities));
            const role = ref('{{ $role }}');
            const currentUserId = {{ auth()->id() ?? 'null' }};
            const loading = ref(false);
            const showFilters = ref(window.innerWidth >= 768);
            const actionLoading = ref(null);
            const cardTheme = ref('contrast'); // 'contrast', 'solid', or 'glass'
            const APP_URL = "{{ url('/') }}";
            
            const defaultMale = "{{ asset('images/default-male.svg') }}";
            const defaultFemale = "{{ asset('images/default-female.svg') }}";

            const filters = reactive({
                q: '{{ request("q") }}',
                gender: '{{ request("gender") }}',
                city_id: '{{ request("city_id") }}',
                page: 1
            });

            const isGlassCard = (index) => {
                if (cardTheme.value === 'glass') return true;
                if (cardTheme.value === 'solid') return false;
                // In contrast mode, alternate between solid and glass
                return index % 2 === 1;
            };

            const getCardClass = (index) => {
                return isGlassCard(index) ? 'glass-frosted' : 'solid-sharp';
            };

            const formatShoeKm = (user) => {
                if (user.weekly_volume && parseFloat(user.weekly_volume) > 0) {
                    return Math.round(parseFloat(user.weekly_volume) * 3.5) + ' km';
                }
                return ((user.id * 37) % 250 + 80) + ' km';
            };

            const formatTimeHours = (user) => {
                if (user.weekly_volume && parseFloat(user.weekly_volume) > 0) {
                    return Math.max(6, Math.round(parseFloat(user.weekly_volume) / 5)) + 'h';
                }
                return ((user.id * 7) % 18 + 8) + 'h';
            };

            let debounceTimer;
            const debouncedFetch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filters.page = 1;
                    fetchUsers();
                }, 400);
            };

            const fetchUsers = async () => {
                loading.value = true;
                
                const params = new URLSearchParams();
                if (filters.q) params.append('q', filters.q);
                if (filters.gender) params.append('gender', filters.gender);
                if (filters.city_id) params.append('city_id', filters.city_id);
                if (filters.page > 1) params.append('page', filters.page);
                
                try {
                    const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    users.value = data;
                    
                    // Update URL
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);
                    
                } catch (error) {
                    console.error('Error fetching users:', error);
                } finally {
                    loading.value = false;
                }
            };

            watch(() => filters.gender, () => { filters.page = 1; fetchUsers(); });
            watch(() => filters.city_id, () => { filters.page = 1; fetchUsers(); });

            const changePage = (page) => {
                filters.page = page;
                fetchUsers();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const resetFilters = () => {
                filters.q = '';
                filters.gender = '';
                filters.city_id = '';
                filters.page = 1;
                fetchUsers();
            };

            const toggleFollow = async (user) => {
                if (!currentUserId) {
                    window.location.href = '/login';
                    return;
                }

                actionLoading.value = user.id;
                const isFollowing = user.is_following;
                const url = APP_URL + (isFollowing ? `/unfollow/${user.id}` : `/follow/${user.id}`);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        user.is_following = !isFollowing;
                    } else {
                        console.error('Action failed');
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    actionLoading.value = null;
                }
            };

            return {
                users,
                cities,
                role,
                currentUserId,
                filters,
                loading,
                showFilters,
                actionLoading,
                cardTheme,
                isGlassCard,
                getCardClass,
                formatShoeKm,
                formatTimeHours,
                debouncedFetch,
                fetchUsers,
                changePage,
                resetFilters,
                toggleFollow,
                defaultMale,
                defaultFemale,
                APP_URL
            };
        }
    }).mount('#users-app');
</script>
@endpush
