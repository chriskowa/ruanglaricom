@extends('layouts.pacerhub')

@section('title', 'Ruanglari - Race Calendar & Analytics')

@section('content')
        <header class="pt-32 pb-6 px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
                TRAINING & <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">RACE DAY</span>
            </h1>
            
            <div class="flex justify-center mb-6">
                <div class="bg-card p-1 rounded-xl border border-slate-700 inline-flex shadow-2xl">
                    <button @click="switchTab('calendar')" :class="activeTab === 'calendar' ? 'bg-slate-700 text-neon shadow-lg' : 'text-slate-400 hover:text-white'" class="px-6 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        RACE CALENDAR
                    </button>
                    <button @click="switchTab('strava')" :class="activeTab === 'strava' ? 'bg-[#FC4C02] text-white shadow-lg' : 'text-slate-400 hover:text-white'" class="px-6 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                        STRAVA ANALYTICS
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 pb-20">
            
            @if(session('error'))
                <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div v-if="loading" class="flex flex-col justify-center items-center py-20 gap-4">
                <div class="loader"></div>
                <p class="text-slate-500 text-sm animate-pulse">Syncing RuangLari & Strava...</p>
            </div>
            
            <div v-if="!loading && activeTab === 'strava' && !isStravaConnected" class="flex flex-col items-center justify-center py-20">
                <div class="bg-card border border-slate-700 p-8 rounded-2xl text-center max-w-md">
                    <svg class="w-16 h-16 text-[#FC4C02] mx-auto mb-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                    <h3 class="text-2xl font-bold text-white mb-2">Connect to Strava</h3>
                    <p class="text-slate-400 mb-6">Connect your Strava account to view your recent activities, stats, and shoe mileage directly on Ruanglari.</p>
                    <a href="{{ route('calendar.strava.connect') }}" class="bg-[#FC4C02] text-white font-bold py-3 px-6 rounded-lg hover:bg-[#E34402] transition inline-flex items-center gap-2">
                        Connect with Strava
                    </a>
                </div>
            </div>

            <div v-show="!loading && activeTab === 'calendar'">
                <div class="bg-card border border-slate-700 rounded-2xl p-4 md:p-6 shadow-2xl relative">
                    <div v-if="calendarLoading" class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/80 rounded-2xl">
                        <div class="loader"></div>
                    </div>
                    <div id='calendar' class="min-h-[600px] text-white"></div>
                </div>
            </div>

            <div v-if="!loading && activeTab === 'strava' && isStravaConnected" class="space-y-6">
                
                    <div class="flex justify-between items-center flex-wrap gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-white">Your Strava Dashboard</h3>
                            <p class="text-xs text-slate-400">Analysis Range: @{{ analysisWeeks }} Weeks</p>
                        </div>
                        <div class="flex items-center gap-4 flex-wrap">
                             <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400">1W</span>
                                <input type="range" min="1" max="12" v-model="analysisWeeks" @change="calculateWeeklyChart" class="w-24 md:w-32 h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-neon">
                                <span class="text-xs text-slate-400">12W</span>
                            </div>
                            <button @click="generateShareImage" class="bg-slate-700 hover:bg-slate-600 text-white p-2 rounded-lg transition" title="Share Stats">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                            </button>
                            <button @click="disconnectStrava" class="text-xs text-red-400 hover:text-red-300 underline">Disconnect</button>
                        </div>
                    </div>

                    <!-- Hidden Share Container -->
                    <div id="share-container" class="fixed -left-[9999px] top-0 w-[600px] bg-[#0f172a] p-8 text-white">
                         <div class="bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700 rounded-2xl p-6 shadow-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-6 opacity-10">
                                <svg class="w-40 h-40 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            
                            <!-- Header Profile -->
                            <div class="flex items-center gap-6 mb-8 relative z-10">
                                <!-- Shield/Badge Shape Profile -->
                                <div class="w-24 h-28 shrink-0 relative drop-shadow-2xl filter">
                                    <!-- Gradient Border/Background -->
                                    <div class="absolute inset-0 bg-gradient-to-b from-[#FC4C02] via-[#FC4C02] to-[#ccff00] rounded-t-2xl rounded-b-[4rem]"></div>
                                    <!-- Image Container (slightly smaller) -->
                                    <div class="absolute inset-[3px] bg-slate-900 rounded-t-[14px] rounded-b-[3.8rem] overflow-hidden">
                                        <img :src="getProxiedProfile()" class="w-full h-full object-cover" crossorigin="anonymous">
                                    </div>
                                    <!-- Shine Effect -->
                                    <div class="absolute inset-0 rounded-t-2xl rounded-b-[4rem] ring-1 ring-white/30 pointer-events-none"></div>
                                    <!-- Badge Icon/Star at bottom -->
                                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-[#0f172a] p-1.5 rounded-full border border-[#ccff00]">
                                        <svg class="w-4 h-4 text-[#ccff00]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-3xl font-bold text-white">@{{ athlete.firstname }} @{{ athlete.lastname }}</h2>
                                        <span class="bg-neon/10 text-neon text-[10px] font-bold px-2 py-1 rounded border border-neon/30 uppercase tracking-wider flex items-center gap-1 shadow-[0_0_10px_rgba(204,255,0,0.2)]">
                                            <span>@{{ filteredStats.archetypeIcon }}</span> @{{ filteredStats.archetype }}
                                        </span>
                                    </div>
                                    <p class="text-slate-400">@{{ athlete.city || 'Runner' }} • @{{ analysisWeeks }} Week Analysis</p>
                                </div>
                                <div class="ml-auto text-right">
                                    <p class="text-xs text-slate-500 uppercase tracking-wider">Powered by</p>
                                    <p class="text-lg font-bold text-neon">RUANG LARI</p>
                                </div>
                            </div>

                            <!-- Grid Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-6 relative z-10">
                                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                                    <p class="text-slate-400 text-xs uppercase">Total Distance</p>
                                    <p class="text-3xl font-mono font-bold text-white">@{{ filteredStats.distance.toFixed(1) }} <span class="text-sm">km</span></p>
                                </div>
                                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                                    <p class="text-slate-400 text-xs uppercase">Elevation Gain</p>
                                    <p class="text-3xl font-mono font-bold text-white">@{{ filteredStats.elevation_gain.toFixed(0) }} <span class="text-sm">m</span></p>
                                </div>
                                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                                    <p class="text-slate-400 text-xs uppercase">Longest Run</p>
                                    <p class="text-3xl font-mono font-bold text-white">@{{ filteredStats.longest_run.toFixed(1) }} <span class="text-sm">km</span></p>
                                </div>
                                <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                                    <p class="text-slate-400 text-xs uppercase">Total Runs</p>
                                    <p class="text-3xl font-mono font-bold text-white">@{{ filteredStats.count }}</p>
                                </div>
                            </div>
                            
                            <!-- Personal Records Badge -->
                            <div class="flex items-center gap-4 bg-gradient-to-r from-yellow-500/10 to-transparent border-l-2 border-yellow-500 p-4 rounded-r-xl mb-6 relative z-10">
                                <div class="bg-gradient-to-br from-yellow-300 to-yellow-600 p-3 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.6)] shrink-0">
                                     <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                     </svg>
                                </div>
                                <div class="w-full">
                                    <h3 class="text-yellow-400 font-bold uppercase tracking-wider text-[10px] mb-1">Period Records</h3>
                                    <div class="flex justify-between items-center w-full pr-4">
                                         <div class="text-center">
                                             <p class="text-[9px] text-slate-400 uppercase">Fastest Pace</p>
                                             <p class="text-lg font-mono font-bold text-white">@{{ filteredStats.best_pace }} <span class="text-[9px] font-sans text-slate-500">/km</span></p>
                                         </div>
                                         <div class="w-px h-6 bg-slate-700"></div>
                                         <div class="text-center">
                                             <p class="text-[9px] text-slate-400 uppercase">Longest Run</p>
                                             <p class="text-lg font-mono font-bold text-white">@{{ filteredStats.longest_run.toFixed(1) }} <span class="text-[9px] font-sans text-slate-500">km</span></p>
                                         </div>
                                         <div class="w-px h-6 bg-slate-700"></div>
                                         <div class="text-center">
                                             <p class="text-[9px] text-slate-400 uppercase">Max Elev</p>
                                             <p class="text-lg font-mono font-bold text-white">@{{ filteredStats.max_elevation.toFixed(0) }} <span class="text-[9px] font-sans text-slate-500">m</span></p>
                                         </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Weekly Chart Snapshot for Share -->
                            <div class="mb-6 relative z-10 bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                                <p class="text-slate-400 text-xs uppercase mb-2">Weekly Analysis</p>
                                <div id="share-chart-clone" class="h-32 w-full relative">
                                    <!-- Clone will be inserted here via JS -->
                                </div>
                            </div>

                            <!-- Shoe Rotation -->
                            <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700 relative z-10 mb-6">
                                <p class="text-slate-400 text-xs uppercase mb-3">Shoe Rotation</p>
                                <div class="space-y-2">
                                    <div v-for="shoe in athlete.shoes ? athlete.shoes.slice(0,3) : []" class="flex justify-between items-center text-sm">
                                        
                                        <span class="text-white truncate flex-1 min-w-0 pr-2 py-1 -my-1" :class="{'text-neon': shoe.primary}">
                                    @{{ shoe.name }}
                                </span>

                                        <span class="font-mono text-slate-400 shrink-0">
                                            @{{ Math.round(shoe.distance/1000) }}km
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Race Potential (Premium Feature) -->
                            <div class="relative z-10">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-slate-400 text-[10px] uppercase tracking-[0.2em] font-bold">Race Potential (Est)</p>
                                    <div class="h-px bg-slate-700 flex-1"></div>
                                </div>
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="bg-[#0f172a] p-3 rounded-xl border border-slate-700/50 text-center relative overflow-hidden group">
                                         <div class="absolute top-0 left-0 w-full h-0.5 bg-blue-500 shadow-[0_0_10px_#3b82f6]"></div>
                                         <p class="text-[9px] text-slate-500 mb-1 uppercase tracking-wider">5K</p>
                                         <p class="text-base font-bold text-white font-mono tracking-tight">@{{ filteredStats.predictions['5k'] }}</p>
                                    </div>
                                    <div class="bg-[#0f172a] p-3 rounded-xl border border-slate-700/50 text-center relative overflow-hidden group">
                                         <div class="absolute top-0 left-0 w-full h-0.5 bg-emerald-500 shadow-[0_0_10px_#10b981]"></div>
                                         <p class="text-[9px] text-slate-500 mb-1 uppercase tracking-wider">10K</p>
                                         <p class="text-base font-bold text-white font-mono tracking-tight">@{{ filteredStats.predictions['10k'] }}</p>
                                    </div>
                                    <div class="bg-[#0f172a] p-3 rounded-xl border border-slate-700/50 text-center relative overflow-hidden group">
                                         <div class="absolute top-0 left-0 w-full h-0.5 bg-amber-500 shadow-[0_0_10px_#f59e0b]"></div>
                                         <p class="text-[9px] text-slate-500 mb-1 uppercase tracking-wider">HM</p>
                                         <p class="text-base font-bold text-white font-mono tracking-tight">@{{ filteredStats.predictions['21k'] }}</p>
                                    </div>
                                    <div class="bg-[#0f172a] p-3 rounded-xl border border-slate-700/50 text-center relative overflow-hidden group">
                                         <div class="absolute top-0 left-0 w-full h-0.5 bg-rose-500 shadow-[0_0_10px_#f43f5e]"></div>
                                         <p class="text-[9px] text-slate-500 mb-1 uppercase tracking-wider">FM</p>
                                         <p class="text-base font-bold text-white font-mono tracking-tight">@{{ filteredStats.predictions['42k'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--
                    <div id="activity-poster-container" class="fixed -left-[9999px] top-0 w-[400px] h-[711px] bg-slate-900 text-white overflow-hidden font-sans">
                        <div class="relative w-full h-full flex flex-col justify-between">
                            
                            <div class="absolute inset-0 z-0">
                                <img v-if="posterData.bgImage" :src="posterData.bgImage" class="w-full h-full object-cover opacity-60" crossorigin="anonymous">
                                <div v-else class="w-full h-full bg-gradient-to-br from-slate-800 via-slate-900 to-black"></div>
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-slate-900/50"></div>
                            </div>

                            <div class="relative z-10 p-6 flex justify-between items-start">
                                <div>
                                    <h1 class="text-3xl font-extrabold italic uppercase tracking-tighter text-white drop-shadow-lg">
                                        @{{ posterData.type }}
                                    </h1>
                                    <p class="text-sm text-slate-300 font-mono">@{{ posterData.date }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-neon font-bold tracking-widest uppercase">Ruang Lari</p>
                                    <div class="w-8 h-1 bg-neon ml-auto mt-1"></div>
                                </div>
                            </div>

                            <div class="relative z-10 px-6 my-auto">
                                <div class="mb-2">
                                    <span class="text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 font-mono tracking-tighter">
                                        @{{ posterData.distance }}
                                    </span>
                                    <span class="text-xl font-bold text-neon uppercase ml-2">Km</span>
                                </div>
                                <h2 class="text-2xl font-bold text-white line-clamp-2 w-3/4 leading-tight mb-4">
                                    @{{ posterData.name }}
                                </h2>
                            </div>

                            <div class="relative z-10 bg-slate-900/90 backdrop-blur-md p-6 m-4 rounded-2xl border border-slate-700">
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Pace</p>
                                        <p class="text-2xl font-bold text-white font-mono">@{{ posterData.pace }}</p>
                                        <p class="text-[9px] text-slate-500">/km</p>
                                    </div>
                                    <div class="border-l border-slate-700">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Time</p>
                                        <p class="text-2xl font-bold text-white font-mono">@{{ posterData.time }}</p>
                                    </div>
                                    <div class="border-l border-slate-700">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Elev</p>
                                        <p class="text-2xl font-bold text-white font-mono">@{{ posterData.elev }}</p>
                                        <p class="text-[9px] text-slate-500">m</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-700">
                                    <img :src="athlete.profile" class="w-8 h-8 rounded-full border border-neon" crossorigin="anonymous">
                                    <div>
                                        <p class="text-xs font-bold text-white">@{{ athlete.firstname }} @{{ athlete.lastname }}</p>
                                        <p class="text-[10px] text-slate-400">@{{ athlete.city }}</p>
                                    </div>
                                    <div class="ml-auto">
                                        <svg class="w-6 h-6 text-[#FC4C02]" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    -->
                    <div id="activity-poster-container" class="fixed -left-[9999px] top-0 w-[400px] h-[711px] bg-[#0f172a] text-white overflow-hidden font-sans">
                        <div class="relative w-full h-full flex flex-col justify-between">
                            
                            <div class="absolute inset-0 z-0">
                                <div v-if="posterData.bgImage" class="absolute inset-0 w-full h-full opacity-60 grayscale brightness-75 bg-cover bg-center" :style="{ backgroundImage: 'url(' + posterData.bgImage + ')' }"></div>
                                <div v-else class="w-full h-full bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-950 to-black"></div>
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-[#0f172a]/80"></div>
                            </div>

                            <!-- Map Path - Placed above stats -->
                            <div class="absolute top-[20%] left-0 right-0 h-[30%] flex items-center justify-center pointer-events-none p-6 opacity-90 z-10">
                                <svg v-if="posterData.mapPath" viewBox="0 0 100 100" class="w-full h-full drop-shadow-[0_0_15px_rgba(204,255,0,0.8)]" preserveAspectRatio="xMidYMid meet">
                                    <path :d="posterData.mapPath" fill="none" stroke="#ccff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <!-- Splits Graphic - Right Side (Table Style) -->
                            <div v-if="posterData.splits && posterData.splits.length > 0" class="absolute right-4 top-[20%] bottom-[20%] w-[160px] flex flex-col justify-center gap-1 z-20 pointer-events-none bg-slate-900/40 p-3 rounded-lg backdrop-blur-[2px]">
                                <div v-for="(split, index) in posterData.splits" :key="index" class="grid grid-cols-[15px_1fr_35px] gap-2 items-center text-[8px] font-mono text-white/90">
                                    <!-- Col 1: Index (Left) -->
                                    <div class="text-left text-slate-400">@{{ index + 1 }}</div>
                                    
                                    <!-- Col 2: Bar (Left Aligned) -->
                                    <div class="h-1.5 bg-slate-700/50 rounded-full overflow-hidden w-full relative">
                                        <div class="absolute top-0 left-0 h-full bg-white/80 rounded-full shadow-[0_0_5px_rgba(255,255,255,0.5)]" 
                                             :style="{ width: split.percentage + '%', opacity: 0.6 + (split.percentage/200) }"></div>
                                    </div>
                                    
                                    <!-- Col 3: Pace (Far Right) -->
                                    <div class="text-right font-bold drop-shadow-md">@{{ split.pace }}</div>
                                </div>
                            </div>

                            <div class="relative z-20 h-full flex flex-col justify-between p-8">
                                
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ asset('images/logo ruang lari.png') }}" alt="RuangLari" class="h-6 w-auto">
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-lg font-bold uppercase tracking-tighter italic text-white">@{{ posterData.type }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono tracking-widest">@{{ posterData.date }}</p>
                                    </div>
                                </div>

                                <div class="mt-auto mb-5 relative z-30">
                                    <h1 class="font-black text-white uppercase italic tracking-tighter 
                                            text-2xl md:text-3xl 
                                            leading-none mb-2 
                                            [font-size:clamp(2rem,8vw,3.5rem)] 
                                            drop-shadow-2xl">
                                        @{{ posterData.name }}
                                    </h1>                                    
                                    <div class="flex items-baseline mb-2">
                                        <span class="text-[48px] leading-[0.85] font-black text-white tracking-tighter -ml-1 drop-shadow-lg" style="text-shadow: 0 0 20px rgba(255,255,255,0.5);">
                                            @{{ posterData.distance }}
                                        </span>
                                        <span class="text-xl font-bold text-[#ccff00] ml-2 uppercase tracking-widest drop-shadow-[0_0_10px_rgba(204,255,0,0.8)]">KM</span>
                                    </div>
                                </div>

                               <div class="relative z-30 bg-slate-900/80 backdrop-blur-md p-5 rounded-2xl border border-slate-700/50 shadow-xl">
                                    <div class="grid grid-cols-4 gap-4 text-center divide-x divide-slate-700">
                                        <div>
                                            <p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">Pace</p>
                                            <p class="text-lg font-bold text-white font-mono">@{{ posterData.pace }}</p>
                                            <p class="text-[8px] text-slate-500">/km</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">Time</p>
                                            <p class="text-lg font-bold text-white font-mono">@{{ posterData.time }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">Elev</p>
                                            <p class="text-lg font-bold text-white font-mono">@{{ posterData.elev }}</p>
                                            <p class="text-[8px] text-slate-500">m</p>
                                        </div>
                                        <div v-if="posterData.heart_rate && posterData.heart_rate !== '-'">
                                            <p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">HR Avg</p>
                                            <p class="text-lg font-bold text-rose-500 font-mono">@{{ posterData.heart_rate }}</p>
                                            <p class="text-[8px] text-slate-500">bpm</p>
                                        </div>
                                        <div v-else>
                                             <p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">HR</p>
                                             <p class="text-lg font-bold text-slate-600 font-mono">-</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3 mt-5 pt-4 border-t border-slate-700/50">
                                        <img :src="getProxiedProfile()" class="w-8 h-8 rounded-full border border-neon" crossorigin="anonymous">
                                        <div>
                                            <p class="text-xs font-bold text-white">@{{ athlete.firstname }} @{{ athlete.lastname }}</p>
                                            <p class="text-[9px] text-slate-400">@{{ athlete.city }}</p>
                                        </div>
                                        <div class="ml-auto text-right">
                                            <p class="text-[8px] text-slate-400 uppercase tracking-widest mb-1">Training Effect</p>
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-neon/10 border border-neon/20 rounded text-neon backdrop-blur-sm">
                                                 <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                 <span class="text-[10px] font-bold uppercase tracking-wider">@{{ posterData.training_effect }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="bg-card border border-slate-700 p-4 rounded-xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition"><svg class="w-16 h-16 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg></div>
                            <p class="text-slate-400 text-[10px] md:text-xs uppercase font-bold tracking-wider">Runs</p>
                            <h3 class="text-2xl md:text-3xl font-mono font-bold text-white mt-1">@{{ filteredStats.count }}</h3>
                        </div>
                        <div class="bg-card border border-slate-700 p-4 rounded-xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition"><svg class="w-16 h-16 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg></div>
                            <p class="text-slate-400 text-[10px] md:text-xs uppercase font-bold tracking-wider">Dist</p>
                            <h3 class="text-2xl md:text-3xl font-mono font-bold text-white mt-1">
                                @{{ filteredStats.distance.toFixed(0) }} <span class="text-sm text-slate-500">km</span>
                            </h3>
                        </div>
                        <div class="bg-card border border-slate-700 p-4 rounded-xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition"><svg class="w-16 h-16 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg></div>
                            <p class="text-slate-400 text-[10px] md:text-xs uppercase font-bold tracking-wider">Elev</p>
                            <h3 class="text-2xl md:text-3xl font-mono font-bold text-white mt-1">
                                @{{ filteredStats.elevation_gain ? filteredStats.elevation_gain.toFixed(0) : 0 }} <span class="text-sm text-slate-500">m</span>
                            </h3>
                        </div>
                        
                        <!-- Longest Run -->
                        <div class="bg-card border border-slate-700 p-4 rounded-xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition"><svg class="w-16 h-16 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                            <p class="text-slate-400 text-[10px] md:text-xs uppercase font-bold tracking-wider">Longest</p>
                            <h3 class="text-2xl md:text-3xl font-mono font-bold text-white mt-1">
                                @{{ filteredStats.longest_run.toFixed(1) }} <span class="text-sm text-slate-500">km</span>
                            </h3>
                        </div>

                         <!-- Shortest Run -->
                         <div class="bg-card border border-slate-700 p-4 rounded-xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition"><svg class="w-16 h-16 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                            <p class="text-slate-400 text-[10px] md:text-xs uppercase font-bold tracking-wider">Shortest</p>
                            <h3 class="text-2xl md:text-3xl font-mono font-bold text-white mt-1">
                                @{{ filteredStats.shortest_run.toFixed(1) }} <span class="text-sm text-slate-500">km</span>
                            </h3>
                        </div>

                        <!-- Shoe Rotation Card -->
                        <div class="bg-card border border-slate-700 p-4 rounded-xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition"><svg class="w-16 h-16 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" /></svg></div>
                            <p class="text-slate-400 text-[10px] md:text-xs uppercase font-bold tracking-wider mb-2">Shoes</p>
                            <div class="space-y-1 max-h-[50px] overflow-y-auto pr-1 custom-scrollbar">
                                <div v-for="shoe in athlete.shoes" :key="shoe.id" class="flex justify-between items-center text-[10px]">
                                    <span class="text-white truncate w-2/3" :class="{'text-neon font-bold': shoe.primary}">@{{ shoe.name }}</span>
                                    <span class="text-slate-400 font-mono">@{{ Math.round(shoe.distance / 1000) }}k</span>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-card border border-slate-700 rounded-xl p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-white">Weekly Activity Analysis</h3>
                            <div class="flex gap-2">
                                <span class="text-[10px] text-slate-400 uppercase border border-slate-600 px-2 py-1 rounded">Volume (km)</span>
                            </div>
                        </div>
                        
                        <!-- Line Chart SVG -->
                        <div class="h-48 relative w-full" id="weekly-chart-container">
                            <!-- We use a computed viewBox based on actual pixel width in JS, or fixed ratio -->
                            <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
                                <defs>
                                    <linearGradient id="lineGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FC4C02" stop-opacity="0.2"/>
                                        <stop offset="100%" stop-color="#FC4C02" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                <!-- Grid Lines -->
                                <line x1="0" y1="0%" x2="100%" y2="0%" stroke="#334155" stroke-dasharray="4" stroke-width="1" vector-effect="non-scaling-stroke" />
                                <line x1="0" y1="50%" x2="100%" y2="50%" stroke="#334155" stroke-dasharray="4" stroke-width="1" vector-effect="non-scaling-stroke" />
                                <line x1="0" y1="100%" x2="100%" y2="100%" stroke="#334155" stroke-dasharray="4" stroke-width="1" vector-effect="non-scaling-stroke" />
                                
                                <!-- Area Fill -->
                                <path :d="chartAreaPath" fill="url(#lineGradient)" />
                                <!-- Line -->
                                <path :d="chartLinePath" fill="none" stroke="#FC4C02" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                                
                                <!-- Points - Use separate SVG or overlay div to prevent stretching -->
                                <!-- We render points as separate SVG elements on top with vector-effect, but circles scale with coord system.
                                     Fix: Use a group with transform to counter-scale or use absolute divs. 
                                     Simpler fix: vector-effect="non-scaling-stroke" helps stroke, but not shape.
                                     Best Fix: Don't preserveAspectRatio="none" for circles. 
                                -->
                            </svg>
                            
                            <!-- Overlay Circles (To avoid stretch) -->
                            <div class="absolute inset-0 pointer-events-none">
                                <div v-for="(point, i) in chartPoints" 
                                     class="absolute w-3 h-3 bg-[#0f172a] border-2 border-[#FC4C02] rounded-full -ml-1.5 -mt-1.5 transition-all cursor-pointer pointer-events-auto group hover:w-4 hover:h-4 hover:-ml-2 hover:-mt-2"
                                     :style="{ left: point.x + '%', top: point.y + '%' }">
                                     <!-- Tooltip -->
                                     <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10 pointer-events-none border border-slate-600">
                                        @{{ weeklyChartData[i].label }}: @{{ weeklyChartData[i].km }} km
                                     </div>
                                </div>
                            </div>
                            
                            <!-- X Axis Labels -->
                            <div class="flex justify-between mt-2 text-[10px] text-slate-500 font-mono uppercase w-full">
                                <span v-for="(day, index) in weeklyChartData" :key="day.label" 
                                    :class="{
                                        'text-left': index === 0,
                                        'text-right': index === weeklyChartData.length - 1,
                                        'text-center': index > 0 && index < weeklyChartData.length - 1
                                    }"
                                    :style="{ width: (100 / weeklyChartData.length) + '%' }"
                                >
                                    @{{ day.label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border border-slate-700 rounded-xl p-6 flex flex-col items-center justify-center text-center">
                        <div class="w-20 h-20 rounded-full border-2 border-[#FC4C02] p-1 mb-3">
                            <img :src="athlete.profile || 'https://via.placeholder.com/150'" class="w-full h-full rounded-full object-cover bg-slate-800">
                        </div>
                        <h3 class="text-xl font-bold text-white">@{{ athlete.firstname }} @{{ athlete.lastname }}</h3>
                        <p class="text-slate-400 text-sm mb-4">@{{ athlete.city || 'Indonesia' }}</p>
                        <div class="grid grid-cols-2 gap-4 w-full text-sm border-t border-slate-700 pt-4">
                            <div>
                                <p class="text-slate-500 text-[10px] uppercase">Weight</p>
                                <p class="font-mono text-white">@{{ athlete.weight || '-' }} kg</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-[10px] uppercase">Followers</p>
                                <p class="font-mono text-white">@{{ athlete.follower_count || 0 }}</p>
                            </div>
                        </div>
                        <a :href="`https://www.strava.com/athletes/${athlete.id}`" target="_blank" class="mt-4 text-xs text-[#FC4C02] font-bold hover:underline">View on Strava ↗</a>
                    </div>
                </div>

                <!-- Performance Summary Card (Replaces AI) -->
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <h3 class="font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            Performance Summary
                        </h3>
                    </div>

                    <div class="relative z-10">
                        <div class="prose prose-invert prose-sm max-w-none">
                            <p class="text-slate-300">"@{{ insightText }}"</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                    <div>
                        <h3 class="font-bold text-white flex items-center gap-2">
                            Recent Activities 
                            <span class="text-xs font-normal text-slate-500 bg-slate-800 px-2 py-0.5 rounded-full">@{{ filteredActivities.length }}</span>
                        </h3>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 w-full md:w-auto">
                        <!-- Location Search -->
                        <div class="relative group">
                            <input type="text" v-model="filterLocation" placeholder="Filter location..." class="bg-slate-800 text-xs text-white px-3 py-2 pl-8 rounded-lg border border-slate-700 focus:border-neon outline-none w-32 focus:w-48 transition-all">
                            <svg class="w-3 h-3 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>

                        <!-- Year Filter -->
                        <select v-model="filterYear" class="bg-slate-800 text-xs text-white px-3 py-2 rounded-lg border border-slate-700 outline-none">
                            <option value="">All Years</option>
                            <option v-for="y in [2023, 2024, 2025]" :value="y">@{{ y }}</option>
                        </select>

                        <!-- Month Filter -->
                        <select v-model="filterMonth" class="bg-slate-800 text-xs text-white px-3 py-2 rounded-lg border border-slate-700 outline-none">
                            <option value="">All Months</option>
                            <option value="0">Jan</option><option value="1">Feb</option><option value="2">Mar</option>
                            <option value="3">Apr</option><option value="4">May</option><option value="5">Jun</option>
                            <option value="6">Jul</option><option value="7">Aug</option><option value="8">Sep</option>
                            <option value="9">Oct</option><option value="10">Nov</option><option value="11">Dec</option>
                        </select>

                        <!-- Distance Slider -->
                        <div class="flex items-center gap-2 bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
                            <span class="text-[10px] text-slate-400 uppercase">Min Dist</span>
                            <input type="range" min="0" max="42" v-model="filterDistance" class="w-20 h-1 bg-slate-600 rounded-lg appearance-none cursor-pointer accent-neon">
                            <span class="text-xs font-mono text-white w-8">@{{ filterDistance }}k</span>
                        </div>
                    </div>
                </div>

                    <div class="space-y-3 mb-6">                    
                    <div v-for="activity in paginatedActivities" :key="activity.id" class="bg-card border border-slate-700 rounded-xl p-4 flex flex-col md:flex-row items-center gap-6 hover:bg-slate-800 transition hover:border-[#FC4C02]/50">
                            <div class="flex items-center gap-4 w-full md:w-1/3">
                                <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center border border-slate-700 text-slate-400">
                                    <svg v-if="activity.type === 'Run'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white line-clamp-1">
                                        <a :href="`https://www.strava.com/activities/${activity.id}`" target="_blank" class="hover:text-[#FC4C02] hover:underline transition">
                                            @{{ activity.name }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-slate-500">@{{ formatDateFull(activity.start_date) }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 w-full md:w-1/2 md:pl-6 border-t md:border-t-0 md:border-l border-slate-700 pt-4 md:pt-0">
                                <div><p class="text-[10px] text-slate-500 uppercase">Dist</p><p class="text-lg font-mono font-bold">@{{ (activity.distance / 1000).toFixed(2) }} km</p></div>
                                <div><p class="text-[10px] text-slate-500 uppercase">Time</p><p class="text-lg font-mono font-bold">@{{ formatDuration(activity.moving_time) }}</p></div>
                                <div><p class="text-[10px] text-slate-500 uppercase">Pace</p><p class="text-lg font-mono font-bold text-[#FC4C02]">@{{ calculatePace(activity.moving_time, activity.distance) }}</p></div>
                            </div>
                            <div class="top-4 right-4 z-10">
                                <button @click="shareActivityPoster(activity)" :disabled="posterLoading" class="bg-slate-700/80 hover:bg-neon hover:text-slate-900 text-white p-2 rounded-lg transition shadow-lg group-hover:scale-105 backdrop-blur-sm" title="Generate Poster">
                                    <svg v-if="posterLoading && currentPosterId === activity.id" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div v-if="totalPages > 1" class="flex justify-center items-center gap-4 mt-8">
                    <button 
                        @click="prevPage" 
                        :disabled="currentPage === 1"
                        class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-700 transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Prev
                    </button>
                    
                    <span class="text-sm text-slate-400">
                        Page <span class="font-bold text-white">@{{ currentPage }}</span> of <span class="font-bold text-white">@{{ totalPages }}</span>
                    </span>

                    <button 
                        @click="nextPage" 
                        :disabled="currentPage === totalPages"
                        class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-700 transition flex items-center gap-2"
                    >
                        Next
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

            </div>
        </div>
@endsection

@push('styles')
    <style>
        /* Mobile Calendar Optimization */
        @media (max-width: 768px) {
            .fc-toolbar-title { font-size: 1.25rem !important; }
            .fc-header-toolbar { flex-wrap: wrap; gap: 0.5rem; }
            .fc-daygrid-day-number { font-size: 0.75rem; }
            .fc-col-header-cell-cushion { font-size: 0.8rem; }
            .fc-event-title { font-size: 0.7rem; }
        }
        
        /* Neon Event Title */
        .fc-event-title.text-neon { color: #ccff00 !important; font-weight: bold; }
        .fc-event { border: none !important; }
        
        /* Popover Dark Mode */
        .fc-popover { background-color: #1e293b !important; border: 1px solid #334155 !important; }
        .fc-popover-header { background-color: #0f172a !important; color: white !important; }
        .fc-popover-body { color: white !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    activeTab: 'calendar',
                    loading: true,
                    isStravaConnected: false,
                    calendarLoading: false,
                    calendarInstance: null,
                    // Data Containers
                    athlete: {},
                    stats: {},
                    stravaActivities: [],
                    allActivities: [],
                    primaryShoe: null,
                    weeklyChartData: [],
                    aiAnalysis: null,
                    analyzing: false,
                    
                    // Filter Config
                    analysisWeeks: 4,
                    filterYear: new Date().getFullYear(),
                    filterMonth: '',
                    filterDistance: 0,
                    filterLocation: '',
                    
                    // Pagination
                    currentPage: 1,
                    itemsPerPage: 10,

                    // Data baru untuk poster
                    posterLoading: false,
                    currentPosterId: null,
                    posterData: {
                        name: '',
                        distance: '',
                        time: '',
                        pace: '',
                        elev: '',
                        date: '',
                        type: '',
                        bgImage: null
                    },
                    
                    apiConfig: {
                        ruangLariUrl: '{{ route("calendar.events.proxy") }}',
                        ruangLariKey: 'Thinkpadx390', // Not used by proxy but kept for ref
                        stravaToken: null,
                    }
                }
            },
            computed: {
                shoePercentage() {
                    // Asumsi max umur sepatu lari 800km
                    if(!this.primaryShoe) return 0;
                    const km = this.primaryShoe.distance / 1000;
                    return Math.min((km / 800) * 100, 100);
                },
                
                paginatedActivities() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredActivities.slice(start, end);
                },

                totalPages() {
                    return Math.ceil(this.filteredActivities.length / this.itemsPerPage);
                },

                filteredActivities() {
                    if(!this.allActivities) return [];
                    
                    return this.allActivities.filter(act => {
                        // Base Filter: Run only
                        // if(act.type !== 'Run') return false; 
                        
                        // 1. Year Filter
                        if(this.filterYear) {
                            if(dayjs(act.start_date).year() !== parseInt(this.filterYear)) return false;
                        }
                        
                        // 2. Month Filter
                        if(this.filterMonth !== '') {
                            if(dayjs(act.start_date).month() !== parseInt(this.filterMonth)) return false;
                        }
                        
                        // 3. Distance Filter (Min Distance)
                        if(this.filterDistance > 0) {
                            if((act.distance / 1000) < this.filterDistance) return false;
                        }
                        
                        // 4. Location Filter (Search by name or timezone as proxy)
                        if(this.filterLocation) {
                            const query = this.filterLocation.toLowerCase();
                            const matchName = act.name.toLowerCase().includes(query);
                            // Strava API doesn't always return city/location directly in summary
                            // We can use timezone or name
                            if(!matchName) return false;
                        }
                        
                        return true;
                    });
                },
                
                filteredStats() {
                    if(!this.allActivities || this.allActivities.length === 0) {
                        return { count: 0, distance: 0, elevation_gain: 0, moving_time: 0, longest_run: 0, shortest_run: 0 };
                    }
                    
                    const cutoff = dayjs().subtract(this.analysisWeeks, 'week');
                    const filtered = this.allActivities.filter(act => 
                        act.type === 'Run' && dayjs(act.start_date).isAfter(cutoff)
                    );
                    
                    const totalDist = filtered.reduce((acc, curr) => acc + curr.distance, 0);
                    const totalElev = filtered.reduce((acc, curr) => acc + (curr.total_elevation_gain || 0), 0);
                    const totalTime = filtered.reduce((acc, curr) => acc + curr.moving_time, 0);
                    
                    const distances = filtered.map(a => a.distance / 1000);
                    const longest = distances.length > 0 ? Math.max(...distances) : 0;
                    const shortest = distances.length > 0 ? Math.min(...distances) : 0;

                    // Calculate Best Pace (min seconds/km)
                    let bestPaceVal = Infinity;
                    filtered.forEach(a => {
                        if(a.distance > 0 && a.moving_time > 0) {
                            const pace = a.moving_time / (a.distance / 1000); // seconds per km
                            if(pace < bestPaceVal) bestPaceVal = pace;
                        }
                    });
                    
                    let formattedBestPace = '-';
                    if(bestPaceVal !== Infinity) {
                         const min = Math.floor(bestPaceVal/60);
                         const sec = Math.floor(bestPaceVal%60);
                         formattedBestPace = `${min}:${sec<10?'0':''}${sec}`;
                    }

                    // Max Elevation
                    const elevs = filtered.map(a => a.total_elevation_gain || 0);
                    const maxElev = elevs.length > 0 ? Math.max(...elevs) : 0;

                    // --- 1. Archetype Calculation ---
                    let morning = 0, evening = 0, weekend = 0;
                    filtered.forEach(a => {
                        const h = dayjs(a.start_date).hour();
                        const d = dayjs(a.start_date).day(); // 0=Sun, 6=Sat
                        if(h >= 4 && h < 10) morning++;
                        if(h >= 16 && h < 23) evening++;
                        if(d === 0 || d === 6) weekend++;
                    });

                    let archetype = "Consistent Runner";
                    let archetypeIcon = "🏃";
                    
                    const avgPaceVal = totalDist > 0 ? (totalTime / (totalDist/1000)) : 0; // sec/km
                    const avgDistVal = filtered.length > 0 ? (totalDist / 1000 / filtered.length) : 0; // km

                    if (filtered.length > 0) {
                        if (avgPaceVal > 0 && avgPaceVal < 300) { // < 5:00/km
                            archetype = "Speedster";
                            archetypeIcon = "⚡";
                        } else if (avgDistVal > 15) {
                            archetype = "Endurance Master";
                            archetypeIcon = "🛡️";
                        } else if (morning / filtered.length > 0.5) {
                            archetype = "Morning Runner";
                            archetypeIcon = "🌅";
                        } else if (evening / filtered.length > 0.5) {
                            archetype = "Night Owl";
                            archetypeIcon = "🦉";
                        } else if (weekend / filtered.length > 0.7) {
                            archetype = "Weekend Warrior";
                            archetypeIcon = "🔥";
                        }
                    }

                    // --- 2. Race Predictor (Riegel Formula) ---
                    // Base pace: Best pace found in a run of at least 3km to be realistic
                    let basePace = avgPaceVal > 0 ? avgPaceVal : 360; // default 6:00/km
                    let best3kPace = Infinity;
                    filtered.forEach(a => {
                        if(a.distance >= 3000) {
                            const p = a.moving_time / (a.distance / 1000);
                            if(p < best3kPace) best3kPace = p;
                        }
                    });
                    if(best3kPace !== Infinity) basePace = best3kPace;

                    const formatTime = (seconds) => {
                        const h = Math.floor(seconds/3600);
                        const m = Math.floor((seconds%3600)/60);
                        const s = Math.floor(seconds%60);
                        if(h > 0) return `${h}:${m<10?'0':''}${m}:${s<10?'0':''}${s}`;
                        return `${m}:${s<10?'0':''}${s}`;
                    };

                    // Riegel: T2 = T1 * (D2/D1)^1.06
                    // Assume our basePace is valid for 5km effort (optimistic)
                    const t5k = basePace * 5;
                    
                    const predictions = {
                        '5k': formatTime(t5k),
                        '10k': formatTime(t5k * Math.pow((10/5), 1.06)),
                        '21k': formatTime(t5k * Math.pow((21.0975/5), 1.06)),
                        '42k': formatTime(t5k * Math.pow((42.195/5), 1.06))
                    };

                    return {
                        count: filtered.length,
                        distance: totalDist / 1000,
                        elevation_gain: totalElev,
                        moving_time: totalTime,
                        longest_run: longest,
                        shortest_run: shortest,
                        best_pace: formattedBestPace,
                        max_elevation: maxElev,
                        archetype,
                        archetypeIcon,
                        predictions
                    };
                },

                // Chart Paths for SVG
                chartPoints() {
                    const data = this.weeklyChartData;
                    if(!data || data.length === 0) return [];
                    
                    const width = 100; // viewbox units
                    const height = 100;
                    const max = Math.max(...data.map(d => parseFloat(d.km))) || 1;
                    
                    // If only one point, center it
                    if (data.length === 1) {
                        return [{ x: 50, y: height - ((data[0].km / max) * height * 0.8) }];
                    }

                    return data.map((d, i) => {
                        // Calculate X position as percentage of width
                        const x = (i / (data.length - 1)) * width;
                        const y = height - ((d.km / max) * height * 0.8);
                        return { x, y };
                    });
                },
                
                chartLinePath() {
                    const pts = this.chartPoints;
                    if(pts.length === 0) return '';
                    
                    // Simple line: M x0 y0 L x1 y1 ...
                    // return `M ${pts[0].x} ${pts[0].y} ` + pts.slice(1).map(p => `L ${p.x} ${p.y}`).join(' ');

                    // Smooth curve (Catmull-Rom or Bezier approx) - simple smoothing
                    if(pts.length < 2) return '';
                    let path = `M ${pts[0].x} ${pts[0].y}`;
                    for (let i = 0; i < pts.length - 1; i++) {
                        const p0 = pts[i];
                        const p1 = pts[i + 1];
                        // Control points for simple curve
                        const cp1x = p0.x + (p1.x - p0.x) / 2;
                        const cp1y = p0.y;
                        const cp2x = p0.x + (p1.x - p0.x) / 2;
                        const cp2y = p1.y;
                        path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p1.x} ${p1.y}`;
                    }
                    return path;
                },
                
                chartAreaPath() {
                    const pts = this.chartPoints;
                    if(pts.length === 0) return '';
                    const line = this.chartLinePath;
                    return `${line} L ${pts[pts.length-1].x} 100 L 0 100 Z`;
                },

                insightText() {
                    const s = this.filteredStats;
                    if (s.count === 0) return "No runs found in this period. Time to lace up!";
                    
                    const avgDist = s.count > 0 ? (s.distance / s.count).toFixed(1) : 0;
                    const avgPace = this.calculatePace(s.moving_time, s.distance * 1000);
                    
                    return `In the last ${this.analysisWeeks} weeks, you've completed ${s.count} runs covering ${s.distance.toFixed(1)} km. That's an average of ${avgDist} km per run with an overall pace of ${avgPace}. Keep pushing!`;
                }
            },
            mounted() {
                // Check if URL hash is #strava
                if(window.location.hash === '#strava') {
                    this.activeTab = 'strava';
                }

                // Check Strava Token
                const token = localStorage.getItem('strava_access_token');
                if(token) {
                    this.isStravaConnected = true;
                    this.apiConfig.stravaToken = token;
                }
                
                this.initData();
            },
            methods: {
                    getProxiedProfile() {
                        if (!this.athlete.profile) return 'https://via.placeholder.com/150';
                        // Use the image proxy for html2canvas compatibility
                        return '/image-proxy?url=' + encodeURIComponent(this.athlete.profile);
                    },

                    async initData() {
                    this.loading = true;
                    
                    const promises = [this.initCalendar()];
                    if(this.isStravaConnected) {
                        promises.push(this.fetchStravaData());
                    }
                    
                    await Promise.allSettled(promises);
                    this.loading = false;

                    // Fix: Force re-render/resize calendar after loading finishes and container is visible
                    this.$nextTick(() => {
                        if (this.calendarInstance) {
                            setTimeout(() => {
                                this.calendarInstance.render();
                                this.calendarInstance.updateSize();
                            }, 200);
                        }
                    });
                },

                disconnectStrava() {
                    if(confirm('Disconnect Strava account from this browser?')) {
                        localStorage.removeItem('strava_access_token');
                        localStorage.removeItem('strava_refresh_token');
                        localStorage.removeItem('strava_expires_at');
                        localStorage.removeItem('strava_athlete');
                        this.isStravaConnected = false;
                        this.apiConfig.stravaToken = null;
                        this.athlete = {};
                        this.stats = {};
                        this.stravaActivities = [];
                    }
                },

                switchTab(tab) {
                    this.activeTab = tab;
                    if(tab === 'calendar' && this.calendarInstance) {
                        setTimeout(() => this.calendarInstance.updateSize(), 50);
                    }
                },

                nextPage() {
                    if(this.currentPage < this.totalPages) this.currentPage++;
                },
                prevPage() {
                    if(this.currentPage > 1) this.currentPage--;
                },

                // --- 1. STRAVA LOGIC ---
                async fetchStravaData() {
                    if(!this.apiConfig.stravaToken) return;
                    
                    try {
                        const headers = { 'Authorization': `Bearer ${this.apiConfig.stravaToken}` };

                        // A. Get Athlete Profile (ID & Shoes)
                        const athleteRes = await fetch('https://www.strava.com/api/v3/athlete', { headers });
                        
                        if(athleteRes.status === 401) {
                            // Token expired or invalid
                            alert('Strava session expired. Please reconnect.');
                            this.disconnectStrava();
                            return;
                        }
                        
                        if(!athleteRes.ok) throw new Error("Strava API Error");
                        this.athlete = await athleteRes.json();
                        
                        // Set Primary Shoe
                        if(this.athlete.shoes && this.athlete.shoes.length > 0) {
                            this.primaryShoe = this.athlete.shoes.find(s => s.primary) || this.athlete.shoes[0];
                        }

                        // B. Get Stats (Totals)
                        const statsRes = await fetch(`https://www.strava.com/api/v3/athletes/${this.athlete.id}/stats`, { headers });
                        this.stats = await statsRes.json();

                        // C. Get Recent Activities (Fetch more for filtering)
                        const actRes = await fetch(`https://www.strava.com/api/v3/athlete/activities?per_page=200`, { headers });
                        const activities = await actRes.json();
                        this.allActivities = activities;
                        this.stravaActivities = activities.slice(0, 30); // Keep display limit for list

                        // D. Calculate Weekly Chart (Last 7 Days - fixed)
                        this.calculateWeeklyChart();

                    } catch (error) {
                        console.warn("Strava API Failed", error);
                    }
                },

                calculateWeeklyChart() {
                    const weeks = this.analysisWeeks;
                    const days = [];
                    
                    // If range > 4 weeks, switch to weekly bars instead of daily bars
                    if(weeks > 2) {
                         // Show Weekly Bars
                         for(let i=weeks-1; i>=0; i--) {
                            const startOfWeek = dayjs().subtract(i, 'week').startOf('week');
                            const endOfWeek = dayjs().subtract(i, 'week').endOf('week');
                            
                            days.push({
                                start: startOfWeek,
                                end: endOfWeek,
                                label: startOfWeek.format('D MMM'),
                                km: 0,
                                percentage: 0
                            });
                         }
                         
                         // Use allActivities instead of filteredActivities to show true volume
                         this.allActivities.forEach(act => {
                            if(act.type === 'Run') {
                                // Use local time to avoid timezone issues with buckets
                                const actDate = dayjs(act.start_date_local);
                                const weekObj = days.find(d => actDate.isAfter(d.start.subtract(1, 'second')) && actDate.isBefore(d.end.add(1, 'second')));
                                if(weekObj) {
                                    weekObj.km += (act.distance / 1000);
                                }
                            }
                         });
                    } else {
                        // Show Daily Bars (Last 14 days max)
                        const dayCount = weeks * 7;
                        for(let i=dayCount-1; i>=0; i--) {
                            days.push({ 
                                date: dayjs().subtract(i, 'day').format('YYYY-MM-DD'),
                                label: dayjs().subtract(i, 'day').format('dd'), 
                                km: 0,
                                percentage: 0 
                            });
                        }
                        
                        this.allActivities.forEach(act => {
                            if(act.type === 'Run') {
                                // Use local time string comparison for daily matching
                                const actDate = dayjs(act.start_date_local).format('YYYY-MM-DD');
                                const dayObj = days.find(d => d.date === actDate);
                                if(dayObj) {
                                    dayObj.km += (act.distance / 1000);
                                }
                            }
                        });
                    }

                    // Calculate bar height percentage
                    const maxKm = Math.max(...days.map(d => d.km));
                    days.forEach(d => {
                        d.km = d.km.toFixed(1);
                        d.percentage = maxKm > 0 ? (d.km / maxKm) * 100 : 0;
                        if(d.percentage < 5 && d.percentage > 0) d.percentage = 5; 
                    });

                    this.weeklyChartData = days;
                },

                loadDummyStrava() {
                    this.athlete = { id: 123, firstname: 'Runner', lastname: 'Demo', city: 'Jakarta', weight: 65, follower_count: 120 };
                    this.stats = {
                        recent_run_totals: { count: 12, distance: 85000, elevation_gain: 450 },
                        ytd_run_totals: { elevation_gain: 2500 }
                    };
                    this.primaryShoe = { name: 'Nike Pegasus 40 (Demo)', distance: 350000 };
                    this.stravaActivities = [
                        { id: 1, name: 'Morning Easy Run', type: 'Run', start_date: dayjs().toISOString(), distance: 5000, moving_time: 1800 },
                        { id: 2, name: 'Tempo Run', type: 'Run', start_date: dayjs().subtract(2, 'day').toISOString(), distance: 10000, moving_time: 3000 },
                        { id: 3, name: 'Long Run', type: 'Run', start_date: dayjs().subtract(5, 'day').toISOString(), distance: 21000, moving_time: 7200 },
                    ];
                    this.calculateWeeklyChart();
                },

                // --- 2. CALENDAR LOGIC ---
                async initCalendar() {
                    try {
                        this.calendarLoading = true;
                        const calendarEl = document.getElementById('calendar');
                        this.calendarInstance = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            initialDate: '2025-12-01', // Set default date to Dec 2025 based on event data
                            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
                            dayMaxEventRows: 2,
                            moreLinkClick: 'popover',
                            displayEventTime: false,
                            events: async (info, success, failure) => {
                                try {
                                    this.calendarLoading = true;
                                    // Use local proxy instead of direct call
                                    const res = await fetch(this.apiConfig.ruangLariUrl);
                                    if(!res.ok) throw new Error("Failed to fetch events");
                                    
                                    const data = await res.json();
                                    
                                    // Map data to FullCalendar format
                                    const events = Array.isArray(data) ? data.map(ev => {
                                        // Handle date format MM/DD/YYYY
                                        let start = ev.date;
                                        if (start && start.includes('/')) {
                                            const parts = start.split('/');
                                            if (parts.length === 3) {
                                                // Convert from MM/DD/YYYY to YYYY-MM-DD
                                                start = `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
                                            }
                                        }

                                        const internalUrl = ev.slug ? `/events/${ev.slug}` : null;
                                        return {
                                            title: ev.title.rendered || ev.title,
                                            start: start,
                                            url: internalUrl || ev.link,
                                            backgroundColor: '#ccff00',
                                            borderColor: '#ccff00',
                                            textColor: '#0f172a'
                                        };
                                    }) : [];
                                    
                                    success(events);
                                } catch (e) {
                                    console.error("Calendar fetch error:", e);
                                    failure(e);
                                } finally {
                                    this.calendarLoading = false;
                                }
                            },
                            eventClick: (info) => {
                                info.jsEvent.preventDefault();
                                if(info.event.url) window.location.href = info.event.url;
                            }
                        });
                        this.calendarInstance.render();
                    } catch (e) { 
                        console.error("Cal Error", e); 
                        this.calendarLoading = false;
                    }
                },

                // Helpers
                formatDateFull(d) { return dayjs(d).format('D MMM YYYY • HH:mm'); },
                formatDuration(s) {
                    const h = Math.floor(s/3600), m = Math.floor((s%3600)/60);
                    return h>0 ? `${h}h ${m}m` : `${m}m ${s%60}s`;
                },
                calculatePace(s, m) {
                    if(m===0) return '-';
                    const p = s / (m/1000);
                    const min = Math.floor(p/60), sec = Math.floor(p%60);
                    return `${min}:${sec<10?'0':''}${sec} /km`;
                },
                
                async generateShareImage() {
                    const container = document.getElementById('share-container');
                    const originalChart = document.getElementById('weekly-chart-container');
                    const cloneTarget = document.getElementById('share-chart-clone');
                    
                    // 1. Clone the SVG Chart into the Share Container
                    if(originalChart && cloneTarget) {
                        cloneTarget.innerHTML = originalChart.innerHTML;
                    }

                    // 2. Position it visible for screenshot but off-screen
                    container.style.display = 'block';
                    container.style.left = '0';
                    container.style.zIndex = '-9999'; // Behind everything
                    
                    await this.$nextTick();

                    try {
                        const canvas = await html2canvas(container, {
                            backgroundColor: '#0f172a',
                            scale: 2, // High Res
                            useCORS: true, // Allow cross-origin images (Strava profile)
                            allowTaint: true,
                            logging: false
                        });
                        
                        // 3. Download Image
                        const link = document.createElement('a');
                        link.download = `Ruanglari-Stats-${dayjs().format('YYYYMMDD')}.png`;
                        link.href = canvas.toDataURL();
                        link.click();
                        
                    } catch (e) {
                        console.error("Screenshot failed", e);
                        alert("Failed to generate image.");
                    } finally {
                        // 4. Hide again
                        container.style.left = '-9999px';
                        cloneTarget.innerHTML = ''; // Clean up
                    }
                },
                
                 // Helper untuk mengubah koordinat ke SVG Path
                generateSVGPath(polyline) {
                    if (!polyline) return '';
                    try {
                        const coords = decodePolyline(polyline);
                        if (coords.length === 0) return '';

                        // Cari batas bounding box (min/max lat lng)
                        let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
                        coords.forEach(c => {
                            // c[0] = lat (Y), c[1] = lng (X)
                            if (c[1] < minX) minX = c[1];
                            if (c[1] > maxX) maxX = c[1];
                            if (c[0] < minY) minY = c[0];
                            if (c[0] > maxY) maxY = c[0];
                        });

                        // Normalisasi ke viewbox 0-100
                        const rangeX = maxX - minX;
                        const rangeY = maxY - minY;
                        const padding = 0; // opsional

                        // Buat path string
                        // Kita balik Y karena SVG koordinat Y positif ke bawah, sedangkan Lat positif ke atas
                        const pathData = coords.map((c, i) => {
                            const x = ((c[1] - minX) / rangeX) * 100;
                            const y = 100 - ((c[0] - minY) / rangeY) * 100; // Flip Y
                            return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
                        }).join(' ');

                        return pathData;
                    } catch (e) {
                        console.error("Error decoding map", e);
                        return '';
                    }
                },

                async shareActivityPoster(activity) {
                    this.posterLoading = true;
                    this.currentPosterId = activity.id;
                    
                    try {
                        // 1. Fetch Detail untuk Photo & Map Polyline
                        const headers = { 'Authorization': `Bearer ${this.apiConfig.stravaToken}` };
                        const detailRes = await fetch(`https://www.strava.com/api/v3/activities/${activity.id}`, { headers });
                        const detail = await detailRes.json();

                        // 2. Ambil Foto (Jika ada)
                        let bgImage = null;
                        if (detail.photos && detail.photos.primary && detail.photos.primary.urls) {
                            const originalUrl = detail.photos.primary.urls['600'];
                            // Use Proxy to fix CORS
                            bgImage = '/image-proxy?url=' + encodeURIComponent(originalUrl);
                        }

                        // 3. Generate Map Path dari Polyline Strava
                    let mapPath = '';
                    if (detail.map && detail.map.summary_polyline) {
                        mapPath = this.generateSVGPath(detail.map.summary_polyline);
                    }

                    // 4. Generate Splits & Heart Rate
                    let splits = [];
                    let avgHeartRate = '-';

                    if (detail.splits_metric && detail.splits_metric.length > 0) {
                        // Max pace for bar scaling (avoid outliers like 0 or too high)
                        // Invert pace for bar width: Faster = Longer bar? Or Slower = Longer?
                        // Usually Pace Graph: Higher = Slower. Let's make Faster = Longer Bar for "Speed" visual.
                        // Or just visualize Intensity. Let's do: Faster = Longer.
                        
                        const validSplits = detail.splits_metric.filter(s => s.distance > 500); // Filter partial/short splits
                        if(validSplits.length > 0) {
                             const paces = validSplits.map(s => s.moving_time / (s.distance/1000));
                             const minPace = Math.min(...paces);
                             const maxPace = Math.max(...paces);
                             
                             splits = validSplits.map(s => {
                                 const paceSeconds = s.moving_time / (s.distance/1000);
                                 // Calculate percentage: Fastest (minPace) should be 100%, Slowest close to 20%
                                 // Invert: (Max - Current) / (Max - Min)
                                 let percentage = 0;
                                 if(maxPace !== minPace) {
                                     percentage = 30 + ((maxPace - paceSeconds) / (maxPace - minPace)) * 70;
                                 } else {
                                     percentage = 100;
                                 }
                                 
                                 return {
                                     pace: this.calculatePace(s.moving_time, s.distance).split(' ')[0], // "5:30"
                                     percentage: percentage
                                 };
                             }).slice(0, 15); // Limit to top 15 splits to fit poster
                        }
                    }

                    if (detail.average_heartrate) {
                        avgHeartRate = Math.round(detail.average_heartrate);
                    }

                    // Determine Training Effect (Simple Heuristic)
                    let trainingEffect = "Aerobic Base";
                    const distKm = activity.distance / 1000;
                    const paceSec = activity.moving_time / distKm; // sec/km
                    
                    if (avgHeartRate !== '-' && avgHeartRate > 165) {
                        trainingEffect = "VO2 Max";
                    } else if (avgHeartRate !== '-' && avgHeartRate > 152) {
                        trainingEffect = "Threshold";
                    } else if (avgHeartRate !== '-' && avgHeartRate > 142) {
                        trainingEffect = "Tempo";
                    } else if (paceSec < 300) { // < 5:00/km
                        trainingEffect = "Speed Workout";
                    } else if (distKm > 18) {
                        trainingEffect = "Long Run";
                    } else if (avgHeartRate !== '-' && avgHeartRate < 135) {
                        trainingEffect = "Recovery";
                    }

                    // 5. Set Data Poster
                    this.posterData = {
                        name: activity.name,
                        distance: (activity.distance / 1000).toFixed(2),
                        time: this.formatDuration(activity.moving_time),
                        pace: this.calculatePace(activity.moving_time, activity.distance).split(' ')[0],
                        elev: activity.total_elevation_gain ? activity.total_elevation_gain.toFixed(0) : '0',
                        date: dayjs(activity.start_date).format('D MMM YYYY'),
                        type: activity.type,
                        bgImage: bgImage,
                        mapPath: mapPath,
                        splits: splits,
                        heart_rate: avgHeartRate,
                        training_effect: trainingEffect
                    };

                        await this.$nextTick();
                        // Tunggu render sebentar
                        await new Promise(resolve => setTimeout(resolve, bgImage ? 1500 : 500));

                        const container = document.getElementById('activity-poster-container');
                        container.style.display = 'block';
                        container.style.left = '0';
                        
                        const canvas = await html2canvas(container, {
                            backgroundColor: '#0f172a',
                            scale: 2,
                            useCORS: true,
                            allowTaint: true
                        });

                        const link = document.createElement('a');
                        link.download = `Ruanglari-${activity.name}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();

                    } catch (e) {
                        console.error(e);
                        alert("Gagal generate poster.");
                    } finally {
                        const container = document.getElementById('activity-poster-container');
                        if(container) container.style.left = '-9999px';
                        this.posterLoading = false;
                        this.currentPosterId = null;
                    }
                }
                /*
                async analyzeDataWithGemini() {
                    if (!this.stravaActivities || this.stravaActivities.length === 0) return;
                    
                    this.analyzing = true;
                    try {
                        // Prepare data summary for AI
                        const summary = {
                            total_runs: this.stats.recent_run_totals?.count || 0,
                            total_distance: (this.stats.recent_run_totals?.distance / 1000).toFixed(1) + 'km',
                            recent_runs: this.stravaActivities.slice(0, 5).map(a => ({
                                name: a.name,
                                date: dayjs(a.start_date).format('YYYY-MM-DD'),
                                distance: (a.distance / 1000).toFixed(1) + 'km',
                                pace: this.calculatePace(a.moving_time, a.distance)
                            }))
                        };

                        const response = await fetch("{{ route('calendar.ai.analysis') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ data: summary })
                        });

                        const result = await response.json();
                        
                        if (result.candidates && result.candidates[0]?.content?.parts?.[0]?.text) {
                            this.aiAnalysis = result.candidates[0].content.parts[0].text;
                        } else {
                            this.aiAnalysis = "Coach sedang istirahat. Coba lagi nanti.";
                        }

                    } catch (error) {
                        console.error("AI Error:", error);
                        this.aiAnalysis = "Gagal menghubungi Coach AI.";
                    } finally {
                        this.analyzing = false;
                    }
                }
                */
            }
        }).mount('#app');

        function decodePolyline(str, precision) {
            var index = 0,
                lat = 0,
                lng = 0,
                coordinates = [],
                shift = 0,
                result = 0,
                byte = null,
                latitude_change,
                longitude_change,
                factor = Math.pow(10, precision || 5);

            while (index < str.length) {
                byte = null;
                shift = 0;
                result = 0;
                do {
                    byte = str.charCodeAt(index++) - 63;
                    result |= (byte & 0x1f) << shift;
                    shift += 5;
                } while (byte >= 0x20);
                latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));
                shift = result = 0;
                do {
                    byte = str.charCodeAt(index++) - 63;
                    result |= (byte & 0x1f) << shift;
                    shift += 5;
                } while (byte >= 0x20);
                longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));
                lat += latitude_change;
                lng += longitude_change;
                coordinates.push([lat / factor, lng / factor]);
            }
            return coordinates;
        }
    </script>
@endpush
