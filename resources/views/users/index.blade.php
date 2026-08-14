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
    .runner-card {
        background: rgba(30, 41, 59, 0.75);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 16px 32px -10px rgba(0, 0, 0, 0.5);
    }
    .runner-card:hover {
        border-color: rgba(204, 255, 0, 0.35);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.7), 0 0 25px rgba(204, 255, 0, 0.08);
    }
    .top-runner-card {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.7);
    }
    .top-runner-card:hover {
        border-color: rgba(204, 255, 0, 0.45);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 30px rgba(204, 255, 0, 0.12);
    }
    .btn-neon-follow {
        background: #ccff00;
        color: #0f172a;
        font-weight: 900;
    }
    .btn-neon-follow:hover {
        background: #b8e600;
        box-shadow: 0 0 20px rgba(204, 255, 0, 0.4);
    }
    [v-cloak] { display: none; }
</style>
@endpush

@section('content')
<div id="users-app" class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-[#0b1220] text-slate-200" v-cloak>
    <div class="max-w-7xl mx-auto space-y-10">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 pb-4 border-b border-slate-800/80">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-neon animate-pulse"></span>
                    <p class="text-neon font-mono text-xs tracking-widest uppercase font-bold">Komunitas Lari Indonesia</p>
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tight">
                    EKSPLORASI <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon via-lime-300 to-emerald-400">{{ strtoupper(str_replace('Daftar ', '', $title)) }}</span>
                </h1>
                <p class="text-slate-400 text-sm mt-1 max-w-xl">
                    Temukan rekan lari, pelajari jarak tempuh mingguan (mileage), dan terhubung dengan pelari serta coach di kotamu.
                </p>
            </div>
            
            <!-- Mobile Filter Toggle -->
            <button class="md:hidden px-4 py-2 bg-slate-800 rounded-xl text-white text-xs font-bold border border-slate-700 flex items-center gap-2" 
                    @click="showFilters = !showFilters">
                <svg class="w-4 h-4 text-neon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span>Filter Pencarian</span>
            </button>
        </div>

        <!-- Section: Runner Paling Aktif (Featured Side-by-Side Cards) -->
        <div v-if="topRunners.length > 0 && !filters.q && filters.page === 1" class="relative rounded-3xl p-6 md:p-8 bg-gradient-to-b from-slate-900/90 to-[#0f172a] border border-slate-800 overflow-hidden shadow-2xl">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-neon/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-6">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-neon/10 border border-neon/30 text-neon rounded-full text-xs font-mono font-bold uppercase tracking-wider mb-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <span>Top Performance</span>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tight">
                            Runner Paling Aktif Minggu Ini
                        </h2>
                    </div>
                    <p class="text-slate-400 text-xs md:text-sm max-w-sm">
                        Pelari dengan akumulasi mileage dan aktivitas lari tertinggi di platform RuangLari.
                    </p>
                </div>

                <!-- 2 Side-by-Side Featured Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    <div v-for="(runner, idx) in topRunners.slice(0, 2)" 
                         :key="'top-' + runner.id" 
                         class="top-runner-card rounded-2xl p-5 md:p-6 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 relative group">
                        
                        <div>
                            <!-- Header Label -->
                            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-3 pb-2 border-b border-slate-800">
                                <span class="flex items-center gap-1 text-neon font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-neon"></span>
                                    RUNNER TERAKTIF #@{{ idx + 1 }}
                                </span>
                                <span v-if="runner.city" class="text-slate-400">@{{ runner.city.name }}</span>
                            </div>

                            <!-- Portrait / Photo Section -->
                            <div class="relative w-full h-56 rounded-xl overflow-hidden mb-4 bg-slate-800 border border-slate-700/80">
                                <img :src="getAvatarUrl(runner)" 
                                     @error="handleAvatarError($event, runner)"
                                     loading="lazy"
                                     decoding="async"
                                     class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" 
                                     :alt="runner.name">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent pointer-events-none"></div>
                                
                                <!-- Top-Right Role Badge -->
                                <div class="absolute top-3 right-3 bg-slate-900/90 backdrop-blur-md text-neon text-[10px] font-black px-3 py-1 rounded-full border border-neon/40 shadow-lg">
                                    @{{ runner.role === 'coach' ? 'COACH' : 'RUNNER' }}
                                </div>

                                <!-- Rank Badge (Bottom Left) -->
                                <div class="absolute bottom-3 left-3 flex items-center gap-1 bg-slate-900/90 backdrop-blur-md text-white text-xs font-bold px-2.5 py-1 rounded-lg border border-slate-700">
                                    <svg class="w-3.5 h-3.5 text-neon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                        <polyline points="17 6 23 6 23 12"/>
                                    </svg>
                                    <span>High Activity</span>
                                </div>
                            </div>

                            <!-- Runner Name -->
                            <h3 class="text-xl font-black text-white tracking-tight mb-3">
                                <a :href="'/runner/' + (runner.username || runner.id)" class="hover:text-neon transition-colors">
                                    @{{ runner.name }}
                                </a>
                            </h3>

                            <!-- Statistics (Minimalist SVG Icons) -->
                            <div class="space-y-2 mb-6">
                                <!-- Running Shoe Icon for Mileage -->
                                <div class="flex items-center gap-2.5 text-slate-300 text-sm font-medium">
                                    <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 17l6-6 4 2 6-4v5l-5 4H4v-1z"/>
                                        <path d="M7 11l3-3"/>
                                        <path d="M11 9l2-2"/>
                                        <path d="M2 20h20"/>
                                    </svg>
                                    <span>Mileage: <strong class="text-white font-mono">@{{ formatShoeKm(runner) }}</strong></span>
                                </div>

                                <!-- Stopwatch Icon for Waktu -->
                                <div class="flex items-center gap-2.5 text-slate-300 text-sm font-medium">
                                    <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="14" r="8"/>
                                        <line x1="12" y1="14" x2="12" y2="10"/>
                                        <line x1="12" y1="14" x2="15" y2="14"/>
                                        <path d="M12 2v4"/>
                                        <path d="M10 2h4"/>
                                        <path d="M19 7l1.5-1.5"/>
                                    </svg>
                                    <span>Waktu: <strong class="text-white font-mono">@{{ formatTimeHours(runner) }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-2">
                            <template v-if="currentUserId !== runner.id">
                                <button v-if="runner.is_following" 
                                        @click="toggleFollow(runner)" 
                                        :disabled="actionLoading === runner.id" 
                                        class="flex-grow py-3 px-4 rounded-xl bg-slate-800 hover:bg-red-500/20 hover:text-red-400 text-slate-300 text-xs font-bold border border-slate-700 transition-all flex items-center justify-center gap-2">
                                    <i v-if="actionLoading === runner.id" class="fas fa-spinner fa-spin"></i>
                                    <span v-else>Mengikuti ✓</span>
                                </button>
                                
                                <button v-else 
                                        @click="toggleFollow(runner)" 
                                        :disabled="actionLoading === runner.id" 
                                        class="btn-neon-follow flex-grow py-3 px-4 rounded-xl text-xs transition-all flex items-center justify-center gap-2 shadow-lg">
                                    <i v-if="actionLoading === runner.id" class="fas fa-spinner fa-spin"></i>
                                    <template v-else>
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                        <span>Ikuti +</span>
                                    </template>
                                </button>

                                <a :href="'/chat/' + runner.id" 
                                   class="w-11 h-11 rounded-xl bg-slate-800 hover:bg-cyan-500/20 hover:text-cyan-400 text-slate-400 border border-slate-700 flex items-center justify-center transition-all flex-shrink-0" 
                                   title="Kirim Pesan">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </a>
                            </template>
                            <a v-else 
                               :href="'/runner/' + (runner.username || runner.id)" 
                               class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold text-center border border-slate-700 transition-all">
                                Lihat Profil Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div v-show="showFilters" class="runner-card rounded-2xl p-5 md:p-6 transition-all" data-aos="fade-up">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Cari Nama / Email</label>
                    <input v-model="filters.q" 
                           @input="debouncedFetch" 
                           type="text" 
                           placeholder="Ketik nama runner..." 
                           class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-4 py-2.5 text-white text-sm focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-500">
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

        <!-- Section Title: Daftar Runner -->
        <div class="flex items-center justify-between pt-2">
            <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight flex items-center gap-2">
                <span>Daftar Pelari Komunitas</span>
                <span class="text-xs font-mono text-slate-400 font-normal not-italic px-2.5 py-0.5 rounded-full bg-slate-800 border border-slate-700">
                    Total: @{{ users.total || users.data.length }}
                </span>
            </h2>
        </div>

        <!-- Loading State Skeleton -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="n in 8" :key="n" class="runner-card rounded-2xl p-5 animate-pulse flex flex-col justify-between h-[400px]">
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
            <div v-for="user in users.data" 
                 :key="user.id" 
                 class="runner-card rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 relative group overflow-hidden">
                
                <div>
                    <!-- Top Section: Photo & Badge -->
                    <div class="relative w-full h-52 rounded-xl overflow-hidden mb-4 bg-slate-800 border border-slate-700/60">
                        <img :src="getAvatarUrl(user)" 
                             @error="handleAvatarError($event, user)"
                             loading="lazy"
                             decoding="async"
                             class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" 
                             :alt="user.name">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent pointer-events-none"></div>
                        
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

                    <!-- Statistics: Minimalist SVG Icons (Shoe for Mileage & Stopwatch for Waktu) -->
                    <div class="space-y-1.5 mb-5 text-xs text-slate-300">
                        <!-- Mileage -->
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-neon flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 17l6-6 4 2 6-4v5l-5 4H4v-1z"/>
                                <path d="M7 11l3-3"/>
                                <path d="M11 9l2-2"/>
                                <path d="M2 20h20"/>
                            </svg>
                            <span>Mileage: <strong class="text-white font-mono">@{{ formatShoeKm(user) }}</strong></span>
                        </div>

                        <!-- Waktu -->
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

                <!-- Action Button: "Ikuti +" & Quick Chat -->
                <div class="flex items-center gap-2 pt-2 border-t border-slate-800/80">
                    <template v-if="currentUserId !== user.id">
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
                                class="btn-neon-follow flex-grow py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 shadow-md">
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
                           class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-cyan-500/20 hover:text-cyan-400 text-slate-400 border border-slate-700 flex items-center justify-center transition-all flex-shrink-0" 
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
        <div v-if="!loading && users.data.length === 0" class="col-span-full py-20 text-center runner-card rounded-3xl p-8">
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
            const topRunners = ref(@json($topRunners ?? []));
            const cities = ref(@json($cities));
            const role = ref('{{ $role }}');
            const currentUserId = {{ auth()->id() ?? 'null' }};
            const loading = ref(false);
            const showFilters = ref(window.innerWidth >= 768);
            const actionLoading = ref(null);
            const APP_URL = "{{ url('/') }}";
            
            const defaultMale = "{{ asset('images/default-male.svg') }}";
            const defaultFemale = "{{ asset('images/default-female.svg') }}";

            const filters = reactive({
                q: '{{ request("q") }}',
                gender: '{{ request("gender") }}',
                city_id: '{{ request("city_id") }}',
                page: 1
            });

            const getAvatarUrl = (user) => {
                if (!user) return defaultMale;
                if (user.avatar_url && !user.avatar_url.includes('images/default-')) {
                    return user.avatar_url;
                }
                if (user.avatar) {
                    const av = String(user.avatar).trim();
                    if (av.startsWith('http://') || av.startsWith('https://')) {
                        return av;
                    }
                    if (av.startsWith('images/')) {
                        return APP_URL + '/' + av;
                    }
                    return APP_URL + '/storage/' + av.replace(/^\/?(storage\/)?/, '');
                }
                if (user.avatar_url) {
                    return user.avatar_url;
                }
                const name = encodeURIComponent(user.name || 'Runner');
                return `https://ui-avatars.com/api/?name=${name}&background=1e293b&color=ccff00&bold=true&size=256`;
            };

            const handleAvatarError = (event, user) => {
                const name = encodeURIComponent((user && user.name) ? user.name : 'Runner');
                event.target.src = `https://ui-avatars.com/api/?name=${name}&background=1e293b&color=ccff00&bold=true&size=256`;
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
                        const topMatch = topRunners.value.find(u => u.id === user.id);
                        if (topMatch) topMatch.is_following = !isFollowing;
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
                topRunners,
                cities,
                role,
                currentUserId,
                filters,
                loading,
                showFilters,
                actionLoading,
                getAvatarUrl,
                handleAvatarError,
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
