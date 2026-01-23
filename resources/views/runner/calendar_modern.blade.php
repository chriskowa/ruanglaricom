@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Runner Calendar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
<style>
.glass-panel{background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.05)}
.fc .fc-toolbar-title{font-size: medium;font-weight:800;color:#e2e8f0}
#loader[data-hidden="1"] { pointer-events: none !important; }
#ph-sidebar-backdrop.hidden { display: none !important; }
[v-cloak]{display:none !important;}
.fc .fc-button{background:#1e293b;border-color:#334155;color:#cbd5e1}
.fc .fc-button:hover{color:#ccff00;border-color:#ccff00}
.fc-event{background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:8px;padding:2px 6px}
.fc-event.difficulty-easy{border-left:4px solid #4CAF50}
.fc-event.difficulty-moderate{border-left:4px solid #FF9800}
.fc-event.difficulty-hard{border-left:4px solid #F44336}

/* Workout Type Color Coding */
/*.fc-event.workout-easy_run{border-left:4px solid #22c55e;background:linear-gradient(90deg, rgba(34,197,94,0.08) 0%, rgba(30,41,59,1) 100%)}
.fc-event.workout-tempo{border-left:4px solid #f59e0b;background:linear-gradient(90deg, rgba(245,158,11,0.08) 0%, rgba(30,41,59,1) 100%)}
.fc-event.workout-interval{border-left:4px solid #a855f7;background:linear-gradient(90deg, rgba(168,85,247,0.08) 0%, rgba(30,41,59,1) 100%)}
.fc-event.workout-long_run{border-left:4px solid #eab308;background:linear-gradient(90deg, rgba(234,179,8,0.08) 0%, rgba(30,41,59,1) 100%)}
.fc-event.workout-recovery{border-left:4px solid #06b6d4;background:linear-gradient(90deg, rgba(6,182,212,0.08) 0%, rgba(30,41,59,1) 100%)}
.fc-event.workout-rest{border-left:4px solid #64748b;background:linear-gradient(90deg, rgba(100,116,139,0.08) 0%, rgba(30,41,59,1) 100%)}*/
/* Workout Type Color Coding (Solid Background) */
/* Workout Type Color Coding (Solid + Font White for Easy & Strength) */
.fc-event.workout-easy_run {
  border-left: 4px solid #4CAF50; /* Hijau segar */
  background-color: #4CAF50;
  color: #ffffff; /* Font putih */
}

.fc-event.workout-long_run {
  border-left: 4px solid #2196F3; /* Biru stabil */
  background-color: #2196F3;
  color: #ffffff; /* Font putih agar tetap kontras */
}

.fc-event.workout-interval {
  border-left: 4px solid #F44336; /* Merah intens */
  background-color: #F44336;
  color: #ffffff;
}

.fc-event.workout-tempo {
  border-left: 4px solid #FFC107; /* Kuning energi */
  background-color: #FFC107;
  color: #000000; /* Font hitam biar lebih terbaca di kuning */
}

.fc-event.workout-strength {
  border-left: 4px solid #9C27B0; /* Ungu power */
  background-color: #9C27B0;
  color: #ffffff; /* Font putih */
}

.fc-event.workout-rest {
  border-left: 4px solid #9E9E9E; /* Abu netral */
  background-color: #9E9E9E;
  color: #000000; /* Font hitam agar jelas */
}

.fc-event.workout-race {
  border-left: 4px solid #FFD700; /* Gold */
  background-color: #FFD700;
  color: #000000;
  font-weight: 900;
  text-transform: uppercase;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

.fc-event.workout-race {
  border-left: 4px solid #FFD700; /* Gold */
  background-color: #FFD700;
  color: #000000;
  font-weight: 900;
  text-transform: uppercase;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

.fc-event.workout-race {
  border-left: 4px solid #FFD700; /* Gold */
  background-color: #FFD700;
  color: #000000;
  font-weight: 900;
  text-transform: uppercase;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

.fc-event.workout-race {
  border-left: 4px solid #FFD700; /* Gold highlight */
  background-color: #FFD700;
  color: #000000; /* Black text for contrast */
}


/* Mobile List View Styling (Card Style) */
.fc-list { border: none !important; }
.fc-list-day-cushion { background-color: transparent !important; }
.fc-list-day-text, .fc-list-day-side-text { font-size: 1.2rem; font-weight: 900; color: #fff; text-transform: uppercase; }
.fc-list-event td { border: none !important; }
.fc-list-event { 
    background-color: #1e293b !important; 
    border-radius: 12px; 
    margin-bottom: 8px; 
    display: block; /* Make rows block to allow spacing */
    position: relative;
    border: 1px solid #334155;
}
/* Hack to make table rows look like cards with spacing */
.fc-list-table { border-collapse: separate; border-spacing: 0 8px; }
.fc-list-event:hover td { background-color: transparent !important; }
.fc-list-event-graphic { display: none; } /* Hide the little dot */
.fc-list-event-time { color: #94a3b8; font-size: 0.8rem; padding: 12px 0 12px 16px !important; width: 10%; }
.fc-list-event-title { color: #fff; font-weight: 700; padding: 12px 16px !important; }

/* Color coding for list view cards based on difficulty class injected via JS */
.fc-list-event.difficulty-easy { background: linear-gradient(90deg, rgba(76, 175, 80, 0.1) 0%, rgba(30, 41, 59, 1) 100%) !important; border-left: 4px solid #4CAF50 !important; }
.fc-list-event.difficulty-moderate { background: linear-gradient(90deg, rgba(255, 152, 0, 0.1) 0%, rgba(30, 41, 59, 1) 100%) !important; border-left: 4px solid #FF9800 !important; }
.fc-list-event.difficulty-hard { background: linear-gradient(90deg, rgba(244, 67, 54, 0.1) 0%, rgba(30, 41, 59, 1) 100%) !important; border-left: 4px solid #F44336 !important; }

/* Color coding for list view by workout type */
.fc-list-event.workout-easy_run { background: linear-gradient(90deg, rgba(34,197,94,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #22c55e !important; }
.fc-list-event.workout-tempo { background: linear-gradient(90deg, rgba(245,158,11,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #f59e0b !important; }
.fc-list-event.workout-interval { background: linear-gradient(90deg, rgba(168,85,247,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #a855f7 !important; }
.fc-list-event.workout-long_run { background: linear-gradient(90deg, rgba(234,179,8,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #eab308 !important; }
.fc-list-event.workout-recovery { background: linear-gradient(90deg, rgba(6,182,212,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #06b6d4 !important; }
.fc-list-event.workout-race { background: linear-gradient(90deg, rgba(255,215,0,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #FFD700 !important; }
.fc-list-event.workout-rest { background: linear-gradient(90deg, rgba(100,116,139,0.1) 0%, rgba(30,41,59,1) 100%) !important; border-left: 4px solid #64748b !important; }*/

</style>
@endpush

@section('content')
<main id="runner-calendar-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" v-cloak>
    <div class="max-w-7xl mx-auto pt-10">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">Training</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Runner Calendar</h1>
            </div>
            <div class="flex gap-2 md:gap-3 flex-wrap justify-end relative z-[60] pointer-events-auto">
                <button type="button" @click="openVdotModal" class="cursor-pointer px-3 py-1.5 md:px-4 md:py-2 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-500 transition text-xs md:text-sm shadow-lg shadow-purple-600/20">Generate VDOT</button>
                @if($isEnrolled40Days)
                <a href="{{ route('challenge.create') }}" class="px-3 py-1.5 md:px-4 md:py-2 rounded-xl bg-orange-600 text-white font-bold hover:bg-orange-500 transition text-xs md:text-sm shadow-lg shadow-orange-600/20">Lapor Aktivitas</a>
                @endif
                <a href="{{ route('programs.index') }}" class="px-3 py-1.5 md:px-4 md:py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition text-xs md:text-sm font-bold">Browse Programs</a>
                <button type="button" @click="openFormForToday" class="cursor-pointer px-3 py-1.5 md:px-4 md:py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20 text-xs md:text-sm">Add Custom Workout</button>
                <button type="button" @click="openRaceForm" class="cursor-pointer px-3 py-1.5 md:px-4 md:py-2 rounded-xl bg-yellow-500 text-black font-black hover:bg-yellow-400 transition shadow-lg shadow-yellow-500/20 text-xs md:text-sm">Add Race</button>
            </div>
        </div>

        <!-- Weekly Volume Chart -->
        <div class="glass-panel rounded-2xl p-6 mb-8" v-if="weeklyVolume.length > 0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-white font-bold text-lg">Weekly Volume Analysis</h3>
                <div class="flex gap-4 text-xs">
                    <div class="flex items-center gap-1"><div class="w-3 h-3 bg-slate-700 rounded-sm"></div> <span class="text-slate-400">Planned</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 bg-neon rounded-sm"></div> <span class="text-slate-400">Actual (Plan + Strava)</span></div>
                </div>
            </div>
            
            <div class="h-40 flex items-end gap-2 overflow-x-auto pb-2 no-scrollbar">
                <div v-for="week in weeklyVolume" :key="week.full_date" class="flex-1 min-w-[30px] flex flex-col items-center gap-2 group relative">
                    <!-- Tooltip -->
                    <div class="absolute bottom-full mb-2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded border border-slate-700 opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10 pointer-events-none shadow-xl">
                        <div class="font-bold text-neon">Week of @{{ week.week_label }}</div>
                        <div>Plan: <span class="font-mono">@{{ week.planned.toFixed(1) }}</span> km</div>
                        <div>Act: <span class="font-mono">@{{ week.actual.toFixed(1) }}</span> km</div>
                        <div class="text-slate-400">• Plan done: <span class="font-mono">@{{ (week.actual_plan || 0).toFixed(1) }}</span> km</div>
                        <div class="text-slate-400">• Strava extra: <span class="font-mono">@{{ (week.actual_strava_unplanned || 0).toFixed(1) }}</span> km</div>
                    </div>
                    
                    <div class="w-full flex items-end justify-center h-full relative">
                        <!-- Planned Bar (Background) -->
                        <div class="w-1.5 md:w-3 bg-slate-700 rounded-t-sm absolute bottom-0 transition-all duration-500" :style="{height: (maxVolume > 0 ? (week.planned / maxVolume * 100) : 0) + '%'}"></div>
                        <!-- Actual Bar (Foreground) -->
                        <div class="w-1.5 md:w-3 bg-neon rounded-t-sm absolute bottom-0 z-10 opacity-80 transition-all duration-500" :style="{height: (maxVolume > 0 ? (week.actual / maxVolume * 100) : 0) + '%'}"></div>
                    </div>
                    <div class="text-[9px] md:text-[10px] text-slate-500 font-mono rotate-0 md:rotate-0 whitespace-nowrap overflow-hidden text-ellipsis w-full text-center">@{{ week.week_label }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="glass-panel rounded-2xl p-4 md:p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    Training Profile
                                </h3>
                                <p class="text-xs text-slate-400">Based on your Personal Best (PB)</p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="syncTraining" class="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded-lg border border-slate-700 hover:text-white hover:border-slate-500 transition flex items-center gap-1" :disabled="syncLoading">
                                    <span v-if="syncLoading" class="animate-spin">⟳</span>
                                    <span v-else>⟳</span>
                                    Sync Training
                                </button>
                                <button @click="showPbModal = true" class="text-xs text-neon hover:underline">Update PB</button>
                            </div>
                        </div>

                        <!-- VDOT & Weekly Target -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center">
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">VDOT Score</div>
                                <div class="text-4xl font-black text-white">@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">VO2Max Approx: @{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center relative group">
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                                    <button @click="showWeeklyTargetModal = true" class="text-xs text-neon hover:text-white bg-slate-700/50 p-1 rounded">Edit</button>
                                </div>
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Weekly Target (km)</div>
                                <div class="text-4xl font-black text-white cursor-pointer" @click="showWeeklyTargetModal = true">@{{ trainingProfile.weekly_km_target ? Number(trainingProfile.weekly_km_target).toFixed(1) : '-' }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">Target mingguan pengguna</div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="flex gap-4 border-b border-slate-700 mb-4">
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'training' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'training'">
                                Training
                            </button>
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'equivalent' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'equivalent'">
                                Equivalent
                            </button>
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'track' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'track'">
                                Track
                            </button>
                        </div>

                        <!-- Training Tab -->
                        <div v-if="profileTab === 'training'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="text-xs text-slate-500 uppercase border-b border-slate-700">
                                            <th class="py-2">Type</th>
                                            <th class="py-2 text-right">1 Km</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-300">
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-green-400 font-bold" id="pace-easy-label">Easy (E)</td>
                                            <td class="py-2 text-right" id="pace-easy-value">@{{ formatPace(trainingProfile.paces?.E) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-blue-400 font-bold" id="pace-marathon-label">Marathon (M)</td>
                                            <td class="py-2 text-right" id="pace-marathon-value">@{{ formatPace(trainingProfile.paces?.M) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-yellow-400 font-bold" id="pace-threshold-label">Threshold (T)</td>
                                            <td class="py-2 text-right" id="pace-threshold-value">@{{ formatPace(trainingProfile.paces?.T) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-orange-400 font-bold" id="pace-interval-label">Interval (I)</td>
                                            <td class="py-2 text-right" id="pace-interval-value">@{{ formatPace(trainingProfile.paces?.I) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-red-400 font-bold" id="pace-repetition-label">Repetition (R)</td>
                                            <td class="py-2 text-right" id="pace-repetition-value">@{{ formatPace(trainingProfile.paces?.R) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Equivalent Tab -->
                        <div v-if="profileTab === 'equivalent'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="text-xs text-slate-500 uppercase border-b border-slate-700">
                                            <th class="py-2">Race</th>
                                            <th class="py-2 text-right">Time</th>
                                            <th class="py-2 text-right">Pace</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-300">
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">5K</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-5k">@{{ trainingProfile.equivalent_race_times?.['5k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['5k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">10K</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-10k">@{{ trainingProfile.equivalent_race_times?.['10k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['10k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">Half Marathon</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-hm">@{{ trainingProfile.equivalent_race_times?.['21k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['21k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-bold">Marathon</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-fm">@{{ trainingProfile.equivalent_race_times?.['42k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['42k']?.pace || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Track Tab -->
                        <div v-if="profileTab === 'track'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="text-xs text-slate-500 uppercase border-b border-slate-700">
                                            <th class="py-2">Distance</th>
                                            <th class="py-2 text-right text-red-400">Rep (R)</th>
                                            <th class="py-2 text-right text-orange-400">Int (I)</th>
                                            <th class="py-2 text-right text-yellow-400">Thr (T)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-300">
                                        <tr v-for="(times, dist) in trainingProfile.track_times" :key="dist" class="border-b border-slate-800 last:border-0">
                                            <td class="py-2 font-bold text-white">@{{ dist }}</td>
                                            <td class="py-2 text-right font-mono">
                                                <div class="text-white">@{{ times.R }}</div>
                                                <div class="text-[10px] text-slate-500">@{{ times.pace_R }}/km</div>
                                            </td>
                                            <td class="py-2 text-right font-mono">
                                                <div class="text-white">@{{ times.I }}</div>
                                                <div class="text-[10px] text-slate-500">@{{ times.pace_I }}/km</div>
                                            </td>
                                            <td class="py-2 text-right font-mono">
                                                <div class="text-white">@{{ times.T }}</div>
                                                <div class="text-[10px] text-slate-500">@{{ times.pace_T }}/km</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl p-4 md:p-6">
                    <div id="calendar"></div>
                </div>

                <div class="glass-panel rounded-2xl p-4 md:p-6">
                    <div class="flex flex-col sm:flex-row items-end justify-between mb-4 gap-4">
                        <div>
                            <h3 class="text-white font-bold text-lg">Plan List</h3>
                            <p class="text-xs text-slate-500">Workout plans from your active programs</p>
                        </div>
                        <div class="flex gap-2 overflow-x-auto w-full sm:w-auto pb-1">
                            <button :class="[filter==='unfinished'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold whitespace-nowrap" @click="setFilter('unfinished')">Unfinished</button>
                            <button :class="[filter==='in_progress'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold whitespace-nowrap" @click="setFilter('in_progress')">In Progress</button>
                            <button :class="[filter==='finished'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold whitespace-nowrap" @click="setFilter('finished')">Finished</button>
                            <button :class="[filter==='all'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold whitespace-nowrap" @click="setFilter('all')">All</button>
                        </div>
                    </div>
                    <div class="flex justify-end mb-4">
                        <button class="text-xs text-red-400 hover:text-red-300 flex items-center gap-1" @click="resetPlanList">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset Plan List
                        </button>
                    </div>

                    <div v-if="plansLoading" class="p-6 text-center text-slate-400">Loading plans...</div>
                    <div v-else-if="plans.length === 0" class="p-6 text-center text-slate-400">No workout plans</div>
                    <div v-else class="space-y-4">
                        <div v-for="plan in plans" :key="plan.id || plan.date+plan.enrollment_id" class="p-4 rounded-2xl bg-slate-800/40 border border-slate-700 flex flex-col gap-3 relative overflow-hidden group hover:border-slate-600 transition">
                            <!-- Status Indicator Strip -->
                            <div class="absolute left-0 top-0 bottom-0 w-1" :class="plan.status==='completed'?'bg-green-500':(plan.status==='started'?'bg-blue-500':'bg-slate-600')"></div>
                            
                            <div class="pl-3 flex flex-col gap-3">
                                <!-- Top: Date & Description -->
                                <div class="w-full">
                                     <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[11px] text-neon font-bold uppercase tracking-wider">@{{ dayName(plan.date) }}, @{{ plan.date_formatted ? plan.date_formatted.split(' ')[1] : '' }}</span>
                                        <span v-if="plan.day_number" class="text-[10px] text-slate-500 font-mono">Day @{{ plan.day_number }}</span>
                                        <span v-if="plan.status!=='pending'" class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1" :class="statusClass(plan.status)">
                                            @{{ statusText(plan.status) }}
                                        </span>
                                     </div>
                                     <h4 class="text-white font-bold text-lg leading-tight cursor-pointer hover:text-neon transition" @click="showPlanDetail(plan)">
                                        @{{ plan.description ? plan.description.split('\n')[0] : (plan.program_title || 'Workout Session') }}
                                     </h4>
                                </div>

                                <!-- Middle: Type & Stats -->
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                         <span class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider bg-slate-700 text-slate-300 border border-slate-600">
                                            @{{ plan.type === 'custom_workout' ? (plan.activity_type || 'Custom') : (plan.type || 'Workout') }}
                                        </span>
                                         <span class="text-slate-300 text-sm font-mono">@{{ plan.distance ? plan.distance + ' km' : (plan.duration || '-') }}</span>
                                    </div>
                                     <div class="text-xs text-slate-400">
                                        @{{ plan.program_title || 'Custom Workout' }}
                                    </div>
                                </div>
                                
                                <!-- Bottom: Actions -->
                                <div class="pt-3 mt-1 border-t border-slate-700/50">
                                    <button v-if="plan.status==='pending'" class="w-full px-4 py-3 rounded-xl bg-neon text-dark text-sm font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20 flex items-center justify-center gap-2" @click.stop="updateSessionStatus(plan,'started')">
                                        <span>▶</span> Start Activity
                                    </button>
                                    <button v-if="plan.status==='started'" class="w-full px-4 py-3 rounded-xl bg-blue-500 text-white text-sm font-bold hover:bg-blue-600 transition shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2" @click.stop="showPlanDetail(plan)">
                                        <span>✓</span> Finish Activity
                                    </button>
                                    <button v-if="plan.status==='completed'" class="w-full px-4 py-3 rounded-xl bg-slate-700/50 text-slate-400 text-sm cursor-default border border-slate-700 flex items-center justify-center gap-2" @click.stop="showPlanDetail(plan)">
                                        <span>👁</span> View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-panel rounded-2xl p-4 md:p-6">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Active Programs</h3>
                    <div class="space-y-3" v-if="enrollments.length > 0">
                        <div v-for="en in enrollments" :key="en.id" class="p-4 rounded-xl bg-slate-800/40 border border-slate-700 flex flex-col gap-3">
                            <div>
                                <div class="text-white font-bold">@{{ en.program.title }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">Start: @{{ formatDate(en.start_date) }} • End: @{{ formatDate(en.end_date) }}</div>
                                <div v-if="en.program && en.program.coach" class="mt-2 flex items-center gap-3">
                                    <img :src="en.program.coach.avatar ? (assetStorage + '/' + en.program.coach.avatar) : assetProfile" class="w-8 h-8 rounded-full border border-slate-600" :alt="en.program.coach.name">
                                    <div class="flex-1">
                                        <div class="text-slate-300 text-sm">@{{ en.program.coach.name }}</div>
                                        <div class="flex gap-2 mt-1">
                                            <a :href="runnerUrl + '/' + (en.program.coach.username || en.program.coach.id)" class="text-xs px-2 py-1 rounded bg-slate-700 text-white hover:bg-slate-600">Profile</a>
                                            <a :href="chatUrl + '/' + en.program.coach.id" @click.prevent="chatCoach(en.program.coach)" class="text-xs px-2 py-1 rounded bg-neon text-dark font-black hover:bg-neon/90">Chat Coach</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button class="px-3 py-1 rounded-lg bg-blue-600/20 text-blue-500 border border-blue-600/30 text-xs w-full hover:bg-blue-600/30 transition" @click="openRescheduleModal(en)">Reschedule</button>
                                <button class="px-3 py-1 rounded-lg bg-yellow-600/20 text-yellow-500 border border-yellow-600/30 text-xs w-full hover:bg-yellow-600/30 transition" @click="resetPlan(en.id)">Reset to Bag</button>
                                <button class="px-3 py-1 rounded-lg bg-red-600/20 text-red-500 border border-red-600/30 text-xs w-full hover:bg-red-600/30 transition" @click="deleteEnrollment(en.id)">Delete</button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-slate-400 text-sm">No active programs.</div>
                </div>

                <div class="glass-panel rounded-2xl p-4 md:p-6">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Program Bag</h3>
                    
                    <!-- Tabs for Bag -->
                    <div class="flex gap-2 mb-4 border-b border-slate-700 pb-2">
                        <button 
                            class="text-xs font-bold px-3 py-1 rounded-lg transition" 
                            :class="bagTab === 'available' ? 'bg-neon text-dark' : 'text-slate-400 hover:text-white'"
                            @click="bagTab = 'available'">
                            Available
                        </button>
                        <button 
                            class="text-xs font-bold px-3 py-1 rounded-lg transition" 
                            :class="bagTab === 'cancelled' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white'"
                            @click="bagTab = 'cancelled'">
                            History / Cancelled
                        </button>
                    </div>

                    <!-- Available Programs -->
                    <div v-if="bagTab === 'available'">
                        <div class="space-y-3" v-if="programBag.length > 0">
                            <div v-for="bg in programBag" :key="bg.id" class="p-4 rounded-xl bg-slate-800/40 border border-slate-700 flex flex-col gap-3">
                                <div>
                                    <div class="text-white font-bold">@{{ bg.program.title }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">Purchased: @{{ formatDate(bg.created_at) }}</div>
                                </div>
                                <button class="px-3 py-2 rounded-lg bg-neon text-dark font-black text-xs w-full hover:bg-neon/90 transition" @click="applyProgram(bg.id)">Apply to Calendar</button>
                            </div>
                        </div>
                        <div v-else class="text-slate-400 text-sm">
                            Your bag is empty. <a href="{{ route('programs.index') }}" class="text-neon hover:underline">Browse Programs</a>
                        </div>
                    </div>

                    <!-- Cancelled/History Programs -->
                    <div v-if="bagTab === 'cancelled'">
                        <div class="space-y-3" v-if="cancelledPrograms.length > 0">
                            <div v-for="bg in cancelledPrograms" :key="bg.id" class="p-4 rounded-xl bg-slate-800/40 border border-slate-700 flex flex-col gap-3 opacity-75">
                                <div>
                                    <div class="text-slate-300 font-bold">@{{ bg.program.title }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">Cancelled: @{{ formatDate(bg.updated_at) }}</div>
                                </div>
                                <button class="px-3 py-2 rounded-lg bg-slate-700 text-white font-bold text-xs w-full hover:bg-slate-600 transition" @click="restoreProgram(bg.id)">Restore to Bag</button>
                            </div>
                        </div>
                        <div v-else class="text-slate-400 text-sm">
                            No cancelled programs history.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showDetailModal" class="fixed inset-0 z-[300] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            <!-- Dynamic Modal Width based on Type -->
            <div class="relative z-10 mx-auto my-10 glass-panel rounded-3xl p-0 border border-slate-700 overflow-hidden transition-all duration-300"
                 :class="detail.type === 'strength' ? 'max-w-2xl' : 'max-w-sm md:max-w-lg'">
                
                <!-- STRENGTH TRAINING UI -->
                <div v-if="detail.type === 'strength'" class="flex flex-col h-full max-h-[85vh]">
                    <!-- Header with Hero Image/Gradient -->
                    <div class="relative h-40 bg-gradient-to-br from-slate-800 to-slate-900 flex items-end p-6 overflow-hidden">
                        <div class="absolute inset-0 opacity-30 bg-[url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80')] bg-cover bg-center"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent"></div>
                        
                        <div class="relative z-10 w-full flex justify-between items-end">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded-full bg-purple-500/20 border border-purple-500/50 text-[10px] text-purple-300 uppercase tracking-wide font-bold">Strength</span>
                                    <span class="text-xs text-slate-400">@{{ detail.duration || '45 min' }}</span>
                                </div>
                                <h2 class="text-2xl font-black text-white leading-tight">@{{ detailTitle }}</h2>
                            </div>
                            <button class="w-8 h-8 rounded-full bg-black/40 hover:bg-white/10 flex items-center justify-center text-white backdrop-blur-md transition" @click="closeDetail">✕</button>
                        </div>
                    </div>

                    <!-- Body: Scrollable Content -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <!-- Overview Stats -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700/50">
                                <div class="text-[10px] text-slate-400 uppercase">Focus</div>
                                <div class="text-sm font-bold text-white">@{{ detail.strength?.category || 'Full Body' }}</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700/50">
                                <div class="text-[10px] text-slate-400 uppercase">Difficulty</div>
                                <div class="text-sm font-bold text-white capitalize">@{{ detail.difficulty || 'Moderate' }}</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700/50">
                                <div class="text-[10px] text-slate-400 uppercase">Exercises</div>
                                <div class="text-sm font-bold text-white">@{{ countExercises(detail) }} Moves</div>
                            </div>
                        </div>

                        <!-- Exercise List (Playlist) -->
                        <div>
                            <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                                <span>📋</span> Workout Plan
                            </h3>
                            <div class="space-y-3">
                                <div v-for="(exercise, idx) in parseStrengthExercises(detail)" :key="idx" 
                                     class="group flex items-center gap-4 p-3 rounded-xl bg-slate-800/40 border border-slate-700 hover:bg-slate-800 transition cursor-pointer"
                                     @click="previewExercise(exercise)">
                                    <!-- Thumbnail Placeholder -->
                                    <div class="w-16 h-16 rounded-lg bg-slate-700 flex-shrink-0 overflow-hidden relative">
                                        <div class="absolute inset-0 flex items-center justify-center text-2xl group-hover:scale-110 transition">
                                            @{{ getExerciseIcon(exercise.name) }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow">
                                        <div class="text-sm font-bold text-white">@{{ exercise.name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">@{{ exercise.sets }} Sets • @{{ exercise.reps }} Reps</div>
                                        <div v-if="exercise.notes" class="text-[10px] text-slate-500 italic mt-1">"@{{ exercise.notes }}"</div>
                                    </div>

                                    <div class="w-8 h-8 rounded-full border border-slate-600 flex items-center justify-center text-slate-400 group-hover:border-purple-500 group-hover:text-purple-500 transition">
                                        ▶
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipment -->
                        <div v-if="detail.strength?.equipment || detail.description?.includes('Equipment')" class="text-xs text-slate-400 bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                            <span class="font-bold text-slate-300">Equipment Needed:</span> @{{ detail.strength?.equipment || 'Dumbbells, Mat' }}
                        </div>
                    </div>

                    <!-- Footer: Action -->
                    <div class="p-4 border-t border-slate-800 bg-slate-900/80 backdrop-blur-md">
                        <button class="w-full py-4 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-lg shadow-lg shadow-purple-900/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-3"
                                @click="startGuidedWorkout(detail)">
                            <span>Start Guided Workout</span>
                            <span class="bg-white/20 px-2 py-0.5 rounded text-xs">Beta</span>
                        </button>
                    </div>
                </div>

                <!-- STANDARD RUNNING/OTHER UI (Existing) -->
                <div v-else class="p-6">
                <div class="relative mb-4">
                    <div class="absolute right-0 top-0 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="statusDotClass(detail.status)"></span>
                        <span class="text-[11px] text-slate-400">@{{ statusText(detail.status) }}</span>
                        <button class="text-slate-400 hover:text-white ml-2" @click="closeDetail">×</button>
                    </div>
                    <div class="flex flex-col items-center text-center mt-2">
                        <div class="mb-2">
                            <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-[11px] text-slate-300 uppercase tracking-wide">@{{ activityLabel(detail.type) }}</span>
                        </div>
                        <div class="text-5xl md:text-6xl font-black text-white leading-none tracking-tight">@{{ primaryMetricValue }}</div>
                        <div class="text-slate-500 text-xs mt-1">@{{ primaryMetricUnit }}</div>
                    </div>
                </div>
                <div>
                    <div class="text-center text-white font-bold mb-2">@{{ detailTitle }}</div>
                    <div class="text-center text-[12px] text-slate-400 mb-4">@{{ detail.date_formatted || formatDate(detail.date) }}</div>

                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                            <svg class="w-5 h-5 text-slate-400 mb-1" viewBox="0 0 24 24" fill="none"><path d="M4 12h16M8 16h8M10 8h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <div class="text-[11px] text-slate-400">Pace</div>
                            <div class="text-neon font-black text-sm">@{{ displayPace || '-' }}</div>
                        </div>
                        <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                            <svg class="w-5 h-5 text-slate-400 mb-1" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <div class="text-[11px] text-slate-400">Time</div>
                            <div class="text-white font-bold text-sm">@{{ detail.duration || '-' }}</div>
                        </div>
                        <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                            <svg class="w-5 h-5 text-slate-400 mb-1" viewBox="0 0 24 24" fill="none"><path d="M12 3l3 6 6 .5-4.5 4 1.5 6-6-3.5L6 19.5l1.5-6L3 9.5 9 9l3-6z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                            <div class="text-[11px] text-slate-400">Difficulty</div>
                            <div class="text-white font-bold text-sm">@{{ (detail.program_difficulty || detail.difficulty || '').toUpperCase() || '-' }}</div>
                        </div>
                    </div>

                    <div v-if="stravaDetailsLoading" class="mb-4 bg-slate-900/40 border border-slate-700/60 rounded-xl p-3 text-xs text-slate-300">
                        Fetching Strava details…
                    </div>
                    <div v-else-if="stravaDetailsError" class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl p-3 text-xs text-red-200">
                        @{{ stravaDetailsError }}
                    </div>
                    <div v-else-if="detail.strava_metrics" class="mb-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                                <div class="text-[11px] text-slate-400">Heart Rate</div>
                                <div class="text-white font-black text-sm">
                                    @{{ detail.strava_metrics.average_heartrate ? Math.round(detail.strava_metrics.average_heartrate) : '-' }}
                                    <span class="text-[10px] text-slate-500">avg</span>
                                </div>
                                <div class="text-[10px] text-slate-500">
                                    max @{{ detail.strava_metrics.max_heartrate ? Math.round(detail.strava_metrics.max_heartrate) : '-' }}
                                </div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                                <div class="text-[11px] text-slate-400">Cadence</div>
                                <div class="text-white font-black text-sm">
                                    @{{ detail.strava_metrics.average_cadence ? Math.round(detail.strava_metrics.average_cadence) : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-500">spm</div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                                <div class="text-[11px] text-slate-400">Avg Pace</div>
                                <div class="text-neon font-black text-sm">@{{ detail.strava_metrics.pace ? (detail.strava_metrics.pace + ' /km') : '-' }}</div>
                                <div class="text-[10px] text-slate-500">&nbsp;</div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-xl bg-slate-800/60 border border-slate-700 p-3">
                                <div class="text-[11px] text-slate-400">Power</div>
                                <div class="text-white font-black text-sm">
                                    @{{ detail.strava_metrics.average_watts ? Math.round(detail.strava_metrics.average_watts) : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-500">watts</div>
                            </div>
                        </div>

                        <!-- Analysis & Suggestion Section -->
                        <div v-if="detail.analysis" class="mt-4 p-4 bg-slate-800/40 border border-slate-700 rounded-xl space-y-3">
                            <div>
                                <h4 class="text-neon font-bold text-xs uppercase tracking-wider mb-1 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                    Analisis Singkat
                                </h4>
                                <p class="text-slate-300 text-sm leading-relaxed">@{{ detail.analysis }}</p>
                            </div>
                            <div class="pt-3 border-t border-slate-700/50">
                                <h4 class="text-yellow-500 font-bold text-xs uppercase tracking-wider mb-1 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    Saran Next Workout
                                </h4>
                                <p class="text-white font-medium text-sm">@{{ detail.suggestion }}</p>
                            </div>
                        </div>

                        <div v-if="detail.strava_streams && detail.strava_streams.time && detail.strava_streams.time.length > 0" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Performance Chart</div>
                            <div class="h-44 bg-slate-900/30 border border-slate-700 rounded-xl p-2 relative group">
                                <button @click="showStravaGraphModal = true" class="absolute top-2 right-2 p-1.5 bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg opacity-0 group-hover:opacity-100 transition z-10" title="Expand Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                                <canvas id="stravaMetricsChart" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <div v-if="detail.strava_splits && detail.strava_splits.length > 0" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Splits</div>
                            <div class="max-h-40 overflow-y-auto space-y-1">
                                <div v-for="s in detail.strava_splits" :key="s.split" class="flex justify-between items-center text-xs p-2 rounded-xl bg-slate-800 border border-slate-700">
                                    <div class="text-slate-300 font-bold">KM @{{ s.split || '-' }}</div>
                                    <div class="text-right">
                                        <div class="text-white font-mono">@{{ s.pace || '-' }}</div>
                                        <div class="text-[10px] text-slate-500">@{{ formatSeconds(s.moving_time_s) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="detail.strava_laps && detail.strava_laps.length > 0" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Laps</div>
                            <div class="max-h-40 overflow-y-auto space-y-1">
                                <div v-for="(l, idx) in detail.strava_laps" :key="idx" class="flex justify-between items-center text-xs p-2 rounded-xl bg-slate-800 border border-slate-700">
                                    <div class="min-w-0">
                                        <div class="text-slate-300 font-bold truncate">@{{ l.name || ('Lap ' + (idx + 1)) }}</div>
                                        <div class="text-[10px] text-slate-500">
                                            @{{ l.distance_m ? (Math.round(l.distance_m) + ' m') : '-' }} • @{{ formatSeconds(l.moving_time_s) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-white font-mono">@{{ l.pace || '-' }}</div>
                                        <div class="text-[10px] text-slate-500">@{{ l.average_heartrate ? (Math.round(l.average_heartrate) + ' bpm') : '' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="detail.workout_structure && detail.workout_structure.length > 0" class="mt-2 border-t border-slate-700 pt-3">
                        <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Workout Steps</div>
                        <div class="space-y-1">
                            <div v-for="(step, idx) in detail.workout_structure" :key="idx" class="flex justify-between items-center text-xs p-2 rounded-xl bg-slate-800 border border-slate-700">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" :class="{'bg-green-400': step.type==='warmup', 'bg-blue-400': step.type==='run', 'bg-orange-400': step.type==='interval', 'bg-yellow-400': step.type==='recovery', 'bg-purple-400': step.type==='cool_down'}"></span>
                                    <span class="font-bold uppercase text-slate-300">@{{ step.type.replace('_', ' ') }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-white font-mono">@{{ step.value }} @{{ step.unit }}</div>
                                    <div v-if="step.notes" class="text-[10px] text-slate-500">@{{ step.notes }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="detail.description" class="mt-3 text-sm text-slate-300">
                        <div class="text-[11px] text-slate-400 uppercase font-bold mb-1">Description</div>
                        <div>@{{ detail.description }}</div>
                    </div>
                    <div v-if="detail.strava_link" class="mt-3 text-sm">
                        <a :href="detail.strava_link" target="_blank" class="text-neon hover:underline">View Strava Activity</a>
                    </div>
                    <div v-if="detail.notes" class="mt-3 p-3 bg-yellow-500/10 border-l-4 border-yellow-500 rounded-r-xl">
                        <div class="text-[11px] text-yellow-500 uppercase font-black mb-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Notes
                        </div>
                        <div class="font-bold text-white text-sm">@{{ detail.notes }}</div>
                    </div>
                </div>

                <!-- Coach Feedback Display -->
                <div v-if="detail.coach_feedback || detail.coach_rating" class="mt-4 bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-full bg-neon flex items-center justify-center text-dark font-bold text-xs">C</div>
                        <span class="text-sm font-bold text-neon">Coach Feedback</span>
                    </div>
                    <div class="space-y-2">
                        <div v-if="detail.coach_rating" class="flex items-center gap-1">
                            <span class="text-xs text-slate-400">Rating:</span>
                            <div class="flex text-yellow-400 text-sm">
                                <span v-for="i in 5" :key="i">@{{ i <= detail.coach_rating ? '★' : '☆' }}</span>
                            </div>
                        </div>
                        <div v-if="detail.coach_feedback" class="text-sm text-slate-300 italic">"@{{ detail.coach_feedback }}"</div>
                    </div>
                </div>

                <!-- Action Buttons for Program Session -->
                <div v-if="detail.type === 'run' || detail.type === 'easy_run' || detail.type === 'interval' || detail.type === 'tempo' || detail.type === 'repetition' || detail.type === 'program_session' || detail.type === 'yoga' || detail.type === 'cycling' || detail.type === 'rest'" class="mt-4 border-t border-slate-700 pt-4">
                    <div v-if="detail.status === 'pending' || !detail.status">
                        <button class="w-full py-3 rounded-xl bg-neon text-dark font-black text-sm hover:bg-neon/90 transition" @click="updateSessionStatus(detail, 'started')">Start Activity</button>
                    </div>
                    <div v-else-if="detail.status === 'started'">
                        <div class="space-y-3">
                             <div>
                                <label class="text-xs text-slate-400 block mb-1">Strava Activity Link (Required)</label>
                                <input type="url" v-model="stravaLinkInput" placeholder="https://www.strava.com/activities/..." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                            
                            <!-- RPE & Feeling Input -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">RPE (1-10)</label>
                                    <select v-model="rpeInput" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                        <option value="">Select Effort</option>
                                        <option value="1">1 - Very Easy</option>
                                        <option value="3">3 - Moderate</option>
                                        <option value="5">5 - Hard</option>
                                        <option value="7">7 - Very Hard</option>
                                        <option value="9">9 - Extremely Hard</option>
                                        <option value="10">10 - Max Effort</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Feeling</label>
                                    <select v-model="feelingInput" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                        <option value="">Select Feeling</option>
                                        <option value="strong">💪 Strong</option>
                                        <option value="good">😊 Good</option>
                                        <option value="average">😐 Average</option>
                                        <option value="weak">😫 Weak</option>
                                        <option value="terrible">💀 Terrible</option>
                                    </select>
                                </div>
                            </div>

                             <div>
                                <label class="text-xs text-slate-400 block mb-1">Notes for Coach (Optional)</label>
                                <textarea v-model="notesInput" rows="2" placeholder="How was your run? Any pain or issues?" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm"></textarea>
                            </div>
                            <button class="w-full py-2 rounded-lg bg-green-500 text-white font-black text-sm hover:bg-green-600 transition" @click="finishActivityWithLink">Finish Activity</button>
                        </div>
                    </div>
                    <div v-else-if="detail.status === 'completed'" class="text-center text-xs text-slate-500">
                        Activity completed on @{{ formatDate(detail.completed_at || new Date()) }}
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <button v-if="detail.source === 'custom' || detail.workout_id" class="text-[12px] text-slate-400 hover:text-red-400" @click="deleteCustomWorkout(detail.workout_id)">Delete</button>
                    <button class="px-3 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm ml-auto" @click="closeDetail">Close</button>
                </div>
            </div>
        </div>

        <div v-if="showPbModal" class="fixed inset-0 z-[250] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-white font-bold text-lg">Update Personal Best</h3>
                        <p class="text-xs text-slate-400">Update your PBs to recalculate VDOT</p>
                    </div>
                    <button class="text-slate-400 hover:text-white" @click="showPbModal = false">×</button>
                </div>
                
                <form @submit.prevent="updatePb" class="space-y-4">
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">5K (HH:MM:SS)</label>
                        <input type="text" v-model="pbForm.pb_5k" placeholder="00:25:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">10K (HH:MM:SS)</label>
                        <input type="text" v-model="pbForm.pb_10k" placeholder="00:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">Half Marathon (HH:MM:SS)</label>
                        <input type="text" v-model="pbForm.pb_hm" placeholder="01:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">Full Marathon (HH:MM:SS)</label>
                        <input type="text" v-model="pbForm.pb_fm" placeholder="03:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                    </div>

                    <div class="pt-4 border-t border-slate-700 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm" @click="showPbModal = false">Cancel</button>
                        <button type="submit" :disabled="pbLoading" class="px-6 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition text-sm disabled:opacity-50">
                            @{{ pbLoading ? 'Updating...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showVdotModal" class="fixed inset-0 z-[300] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-2xl mx-auto my-10 glass-panel rounded-2xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-white font-bold text-xl">Generate VDOT Program</h3>
                        <p class="text-xs text-slate-400">Based on Jack Daniels' Running Formula</p>
                    </div>
                    <button class="text-slate-400 hover:text-white" @click="showVdotModal = false">×</button>
                </div>
                
                <form @submit.prevent="generateVdot" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Personal Info -->
                        <div class="space-y-3">
                            <h4 class="text-neon font-bold text-xs uppercase tracking-wider">Profile</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Age</label>
                                    <input type="number" v-model="vdotForm.age" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Gender</label>
                                    <select v-model="vdotForm.gender" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Current Fitness -->
                        <div class="space-y-3">
                            <h4 class="text-neon font-bold text-xs uppercase tracking-wider">Current Fitness</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Recent Race Dist.</label>
                                    <select v-model="vdotForm.race_distance" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon</option>
                                        <option value="42k">Marathon</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Recent Time (HH:MM:SS)</label>
                                    <input type="text" v-model="vdotForm.race_time" placeholder="00:25:00" required pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 block mb-1">Race Date</label>
                                <input type="date" v-model="vdotForm.race_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                        </div>

                        <!-- Training History -->
                        <div class="space-y-3">
                            <h4 class="text-neon font-bold text-xs uppercase tracking-wider">Training Volume</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Avg Weekly (km)</label>
                                    <input type="number" v-model="vdotForm.weekly_mileage" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Peak Weekly (km)</label>
                                    <input type="number" v-model="vdotForm.peak_mileage" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 block mb-1">Training Days/Week</label>
                                <select v-model="vdotForm.training_frequency" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                    <option value="3">3 Days</option>
                                    <option value="4">4 Days</option>
                                    <option value="5">5 Days</option>
                                    <option value="6">6 Days</option>
                                    <option value="7">7 Days</option>
                                </select>
                            </div>
                        </div>

                        <!-- Goal -->
                        <div class="space-y-3">
                            <h4 class="text-neon font-bold text-xs uppercase tracking-wider">Goal</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Target Race</label>
                                    <select v-model="vdotForm.goal_distance" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon</option>
                                        <option value="42k">Marathon</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 block mb-1">Goal Time (Optional)</label>
                                    <input type="text" v-model="vdotForm.goal_time" placeholder="00:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 block mb-1">Target Race Date</label>
                                <input type="date" v-model="vdotForm.goal_race_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                <p class="text-[10px] text-slate-500 mt-1">Recommended: 8-16 weeks from today for optimal results.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-700 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm" @click="showVdotModal = false">Cancel</button>
                        <button type="submit" :disabled="vdotLoading" class="px-6 py-2 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-500 transition text-sm disabled:opacity-50">
                            @{{ vdotLoading ? 'Generating...' : 'Generate Program' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Weekly Target Modal -->
        <div v-if="showWeeklyTargetModal" class="fixed inset-0 z-[250] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6 border-neon/30 shadow-2xl shadow-neon/10">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-white font-black text-xl flex items-center gap-2">
                        <span>🎯</span> Update Weekly Target
                    </h3>
                    <button @click="showWeeklyTargetModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Weekly Target (km)</label>
                        <input type="number" step="0.1" v-model="weeklyTargetForm.weekly_km_target" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        <p class="text-[10px] text-slate-500 mt-1">Set your weekly running distance goal.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-700">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm hover:text-white" @click="showWeeklyTargetModal = false">Cancel</button>
                        <button type="button" @click="updateWeeklyTarget" class="px-6 py-2 rounded-xl bg-neon text-dark font-black text-sm hover:bg-neon/90 shadow-lg shadow-neon/20 flex items-center gap-2" :disabled="weeklyTargetLoading">
                            <span v-if="weeklyTargetLoading" class="animate-spin">⟳</span>
                            Save Target
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showFormModal" class="fixed inset-0 z-[300] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-lg mx-auto my-10 glass-panel rounded-2xl p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-white font-bold">Add Workout</h3>
                    <button class="text-slate-400 hover:text-white" @click="closeForm">×</button>
                </div>
                <form @submit.prevent="saveCustomWorkout" class="space-y-3">
                    <input type="hidden" v-model="form.workout_id">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Date</label>
                        <input type="date" v-model="form.workout_date" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Type</label>
                        <select v-model="form.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="run">Run</option>
                            <option value="easy_run">Easy Run</option>
                            <option value="interval">Interval</option>
                            <option value="tempo">Tempo</option>
                            <option value="yoga">Yoga</option>
                            <option value="cycling">Cycling</option>
                            <option value="rest">Rest</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Difficulty</label>
                        <select v-model="form.difficulty" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="easy">Mudah</option>
                            <option value="moderate">Sedang</option>
                            <option value="hard">Sulit</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Distance (km)</label>
                            <input type="number" step="0.1" v-model="form.distance" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Duration</label>
                            <input type="text" v-model="form.duration" placeholder="00:30:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        </div>
                    </div>
                    
                    <!-- Workout Builder -->
                    <div class="border-t border-slate-700 pt-4 mt-4">
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Workout Builder</label>
                        
                        <div class="space-y-2 mb-3">
                            <div v-for="(step, index) in form.workout_structure" :key="index" class="flex flex-col gap-2 p-3 bg-slate-800 rounded-lg border border-slate-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold uppercase px-2 py-1 rounded bg-slate-700" :class="{'text-green-400': step.type==='warmup', 'text-blue-400': step.type==='run', 'text-orange-400': step.type==='interval', 'text-yellow-400': step.type==='recovery', 'text-purple-400': step.type==='cool_down'}">
                                        @{{ step.type.replace('_', ' ') }}
                                    </span>
                                    <div class="flex gap-1">
                                        <button type="button" class="text-slate-400 hover:text-white" @click="moveStep(index, -1)" v-if="index > 0">↑</button>
                                        <button type="button" class="text-slate-400 hover:text-white" @click="moveStep(index, 1)" v-if="index < form.workout_structure.length - 1">↓</button>
                                        <button type="button" class="text-red-400 hover:text-red-300 ml-2" @click="removeStep(index)">×</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <select v-model="step.duration_type" @change="calculateTotalDistance" class="bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1">
                                        <option value="distance">Distance</option>
                                        <option value="time">Time</option>
                                    </select>
                                    <div class="flex gap-1 col-span-2">
                                        <input type="number" step="0.1" v-model="step.value" @change="calculateTotalDistance" class="w-full bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1" placeholder="Value">
                                        <select v-model="step.unit" @change="calculateTotalDistance" class="w-20 bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                            <option value="min">min</option>
                                            <option value="sec">sec</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="text" v-model="step.notes" placeholder="Notes (e.g. @ 5:00 pace)" class="w-full bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1">
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="addStep('warmup')" class="px-2 py-1 rounded bg-green-500/20 text-green-400 border border-green-500/30 text-xs font-bold hover:bg-green-500/30">+ Warmup</button>
                            <button type="button" @click="addStep('run')" class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 text-xs font-bold hover:bg-blue-500/30">+ Run</button>
                            <button type="button" @click="addStep('interval')" class="px-2 py-1 rounded bg-orange-500/20 text-orange-400 border border-orange-500/30 text-xs font-bold hover:bg-orange-500/30">+ Interval</button>
                            <button type="button" @click="addStep('recovery')" class="px-2 py-1 rounded bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 text-xs font-bold hover:bg-yellow-500/30">+ Recovery</button>
                            <button type="button" @click="addStep('cool_down')" class="px-2 py-1 rounded bg-purple-500/20 text-purple-400 border border-purple-500/30 text-xs font-bold hover:bg-purple-500/30">+ Cool Down</button>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm" @click="closeForm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-black text-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>



        <div v-if="showRaceModal" class="fixed inset-0 z-[300] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-lg mx-auto my-10 glass-panel rounded-2xl p-6 border-yellow-500/30 shadow-2xl shadow-yellow-500/10">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-white font-black text-xl flex items-center gap-2">
                        <span class="text-2xl">🏆</span> Add Race Event
                    </h3>
                    <button class="text-slate-400 hover:text-white" @click="showRaceModal = false">×</button>
                </div>
                <form @submit.prevent="saveRace" class="space-y-4">
                    <div class="mb-4 bg-slate-800/50 p-3 rounded-xl border border-slate-700 relative">
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Select Official Event</label>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="eventSearchQuery"
                                @focus="showEventDropdown = true"
                                placeholder="Search events..."
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:border-yellow-500 focus:outline-none pl-8"
                            >
                            <span class="absolute left-3 top-2.5 text-slate-500">🔍</span>
                            <button v-if="eventSearchQuery" @click="eventSearchQuery = ''; showEventDropdown = false" class="absolute right-3 top-2.5 text-slate-500 hover:text-white">✕</button>
                        </div>

                        <!-- Dropdown List -->
                        <div v-if="showEventDropdown && filteredEvents.length > 0" 
                            class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto">
                            <ul>
                                <li v-for="event in filteredEvents" :key="event.id"
                                    @click="selectRuangLariEvent(event)"
                                    class="px-4 py-3 hover:bg-slate-800 cursor-pointer border-b border-slate-800 last:border-0"
                                >
                                    <div class="text-sm font-bold text-white">@{{ event.name }}</div>
                                    <div class="text-xs text-slate-400 flex justify-between mt-1">
                                        <span>📅 @{{ formatDate(event.start_at) }}</span>
                                        <span>📍 @{{ event.location_name }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="showEventDropdown && filteredEvents.length === 0 && !loadingEvents" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-xl p-4 text-center text-slate-500 text-sm z-50">
                            No events found.
                        </div>

                        <div v-if="loadingEvents" class="text-xs text-yellow-500 mt-1 italic">Loading events...</div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-yellow-500 uppercase">Race Name</label>
                        <input type="text" v-model="raceForm.name" required placeholder="e.g. Jakarta Marathon 2025" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:border-yellow-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Date</label>
                        <input type="date" v-model="raceForm.date" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:border-yellow-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Distance</label>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <button type="button" @click="setRaceDist(5, '5K')" class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='5K'?'border-yellow-500 text-yellow-500':''">5K</button>
                            <button type="button" @click="setRaceDist(10, '10K')" class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='10K'?'border-yellow-500 text-yellow-500':''">10K</button>
                            <button type="button" @click="setRaceDist(21.1, 'HM')" class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='HM'?'border-yellow-500 text-yellow-500':''">HM</button>
                            <button type="button" @click="setRaceDist(42.2, 'FM')" class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='FM'?'border-yellow-500 text-yellow-500':''">FM</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" v-model="raceForm.distance" placeholder="Custom" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                            <span class="text-slate-400 text-sm">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Goal Time (Optional)</label>
                        <input type="text" v-model="raceForm.goal_time" placeholder="hh:mm:ss" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Notes / Website</label>
                        <textarea v-model="raceForm.notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-700">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm hover:text-white" @click="showRaceModal = false">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-xl bg-yellow-500 text-black font-black text-sm hover:bg-yellow-400 shadow-lg shadow-yellow-500/20">Save Race</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div v-if="showRescheduleModal" class="fixed inset-0 z-[250] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6 border-blue-500/30 shadow-2xl shadow-blue-500/10">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-white font-black text-xl flex items-center gap-2">
                        <span class="text-2xl">📅</span> Reschedule Program
                    </h3>
                    <button class="text-slate-400 hover:text-white" @click="showRescheduleModal = false">×</button>
                </div>
                <div class="mb-4">
                    <p class="text-slate-300 text-sm mb-2">Shift your entire program to a new start date. All future sessions will be moved accordingly.</p>
                    <div class="bg-blue-900/20 border border-blue-800 rounded-lg p-3">
                        <p class="text-xs text-blue-300">Program: <span class="font-bold text-white">@{{ rescheduleTarget?.program?.title }}</span></p>
                        <p class="text-xs text-blue-300">Current Start: <span class="font-bold text-white">@{{ formatDate(rescheduleTarget?.start_date) }}</span></p>
                    </div>
                </div>
                <form @submit.prevent="submitReschedule" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-blue-400 uppercase">New Start Date</label>
                        <input type="date" v-model="rescheduleForm.new_start_date" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:border-blue-500 focus:outline-none">
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-700">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm hover:text-white" @click="showRescheduleModal = false">Cancel</button>
                        <button type="submit" :disabled="rescheduleLoading" class="px-6 py-2 rounded-xl bg-blue-500 text-white font-bold text-sm hover:bg-blue-400 shadow-lg shadow-blue-500/20 flex items-center gap-2">
                            <span v-if="rescheduleLoading" class="animate-spin">⌛</span>
                            <span>Confirm Reschedule</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Strava Graph Modal -->
        <div v-if="showStravaGraphModal" class="fixed inset-0 z-[350] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md">
            <div class="w-full max-w-5xl h-[80vh] bg-slate-900 border border-slate-700 rounded-2xl p-6 relative flex flex-col shadow-2xl shadow-neon/10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-black text-[#FC4C02] italic uppercase flex items-center gap-2">
                        <svg role="img" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                        Strava Analysis
                    </h3>
                    <button @click="showStravaGraphModal = false" class="text-slate-400 hover:text-white bg-slate-800 p-2 rounded-lg transition">✕</button>
                </div>
                <div class="flex-grow relative bg-slate-900/50 rounded-xl border border-slate-800 p-4">
                    <canvas id="stravaMetricsChartFullscreen" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
@include('layouts.components.advanced-builder-utils')
<script src="{{ asset('vendor/chart-js/chart.bundle.min.js') }}"></script>
<script>
const { createApp, ref, reactive, onMounted, computed, watch, nextTick } = Vue;
(function(){
    const root = document.getElementById('runner-calendar-app');
    if (!root) { console.error('Runner calendar root not found'); return; }
})();
createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const filter = ref('unfinished');
        const plans = ref([]);
        const weeklyVolume = ref([]);
        const maxVolume = computed(() => {
            if (weeklyVolume.value.length === 0) return 0;
            return Math.max(...weeklyVolume.value.map(w => Math.max(w.planned, w.actual))) * 1.1; // Add 10% headroom
        });
        const plansLoading = ref(false);
        const enrollments = ref(@json($enrollments));
        const programBag = ref(@json($programBag));
        const cancelledPrograms = ref(@json($cancelledPrograms ?? []));
        const bagTab = ref('available');
        const profileTab = ref('training');
        const trainingProfile = ref(@json($trainingProfile ?? []));
        const showDetailModal = ref(false);
        const syncLoading = ref(false);
        const detail = reactive({});
        const detailTitle = ref('');
        const stravaLinkInput = ref('');
        const notesInput = ref('');
        const rpeInput = ref('');
        const feelingInput = ref('');
        const stravaDetailsLoading = ref(false);
        const stravaDetailsError = ref('');
        const stravaStreamsLoading = ref(false);
        const stravaStreamsError = ref('');
        let stravaChart = null;
        const displayPace = computed(() => detail.actual_pace || detail.target_pace || detail.recommended_pace || null);
        const primaryMetricValue = computed(() => {
            try {
                if (detail.distance) {
                    const num = parseFloat(detail.distance);
                    return isNaN(num) ? detail.distance : num.toFixed(2);
                }
                if (detail.duration) return detail.duration;
            } catch(e){}
            return '--';
        });
        const primaryMetricUnit = computed(() => detail.distance ? 'km' : (detail.duration ? '' : ''));
        const statusDotClass = (s) => (s==='completed' || s==='imported') ? 'bg-green-400' : (s==='started' ? 'bg-yellow-400' : 'bg-red-400');
        const showFormModal = ref(false);
        const form = reactive({ 
            workout_id:'', 
            workout_date:'', 
            type:'run', 
            difficulty:'moderate', 
            distance:'', 
            duration:'', 
            description:'',
            workout_structure: [] // Array of steps
        });
        
        // Weekly Target State
        const showWeeklyTargetModal = ref(false);
        const weeklyTargetLoading = ref(false);
        const weeklyTargetForm = reactive({
            weekly_km_target: trainingProfile.value.weekly_km_target || ''
        });

        // Race Form State
        const showRaceModal = ref(false);
        const raceForm = reactive({
            name: '',
            date: '',
            distance: '',
            distLabel: '', // 5K, 10K, etc.
            goal_time: '',
            notes: ''
        });
        
        // Workout Builder Helper Methods
        const addStep = (type) => {
            form.workout_structure.push({
                type: type, // warmup, run, interval, recovery, rest, cool_down
                duration_type: 'distance', // distance, time
                value: '',
                unit: 'km', // km, min, m, sec
                notes: ''
            });
        };

        const removeStep = (index) => {
            form.workout_structure.splice(index, 1);
        };

        const moveStep = (index, direction) => {
            if (direction === -1 && index > 0) {
                const temp = form.workout_structure[index];
                form.workout_structure[index] = form.workout_structure[index - 1];
                form.workout_structure[index - 1] = temp;
            } else if (direction === 1 && index < form.workout_structure.length - 1) {
                const temp = form.workout_structure[index];
                form.workout_structure[index] = form.workout_structure[index + 1];
                form.workout_structure[index + 1] = temp;
            }
        };

        const calculateTotalDistance = () => {
            // Auto-calculate total distance from structure if possible
            let total = 0;
            form.workout_structure.forEach(step => {
                if (step.duration_type === 'distance' && step.value) {
                    let val = parseFloat(step.value);
                    if (step.unit === 'm') val /= 1000;
                    total += val;
                }
            });
            if (total > 0) form.distance = total.toFixed(2);
        };

        // RuangLari Events Integration
        const ruangLariEvents = ref([]);
        const loadingEvents = ref(false);
        const eventSearchQuery = ref('');
        const showEventDropdown = ref(false);
        const showStravaGraphModal = ref(false);
        let stravaFullscreenChart = null;
        
        const filteredEvents = computed(() => {
            if (!eventSearchQuery.value) return ruangLariEvents.value;
            const query = eventSearchQuery.value.toLowerCase();
            return ruangLariEvents.value.filter(e => 
                (e.name || '').toLowerCase().includes(query) || 
                (e.location_name || '').toLowerCase().includes(query)
            );
        });

        const fetchRuangLariEvents = async () => {
            if (ruangLariEvents.value.length > 0) return;
            loadingEvents.value = true;
            try {
                // Use proxy to avoid CORS
                const res = await fetch('{{ route("calendar.events.proxy") }}');
                const data = await res.json();
                ruangLariEvents.value = Array.isArray(data) ? data : [];
            } catch (e) {
                console.error('Failed to fetch events', e);
            } finally {
                loadingEvents.value = false;
            }
        };

        const selectRuangLariEvent = (event) => {
            eventSearchQuery.value = event.name;
            showEventDropdown.value = false;
            onSelectRuangLariEvent(event);
        };

        const onSelectRuangLariEvent = (event) => {
            if (!event) return;
            // API Format: {"id":...,"name":"...","start_at":"yyyy-mm-dd hh:mm:ss",...}
            raceForm.name = event.name;
            
            // Construct link from slug if available
            const link = event.slug ? `/event-lari/${event.slug}` : '';
            raceForm.notes = link ? `Official Event: ${event.name}\nLink: ${link}` : `Official Event: ${event.name}`;
            
            // Parse date
            let dateStr = event.start_at;
            if (dateStr) {
                // If it's already YYYY-MM-DD (start_at usually is datetime string)
                if (dateStr.includes('-')) {
                     raceForm.date = dateStr.split(' ')[0];
                } 
            }
            
            // Guess distance
            const title = (event.name || '').toLowerCase();
            if (title.includes('marathon') && !title.includes('half')) {
                setRaceDist(42.2, 'FM');
            } else if (title.includes('half') || title.includes('hm') || title.includes('21k')) {
                setRaceDist(21.1, 'HM');
            } else if (title.includes('10k')) {
                setRaceDist(10, '10K');
            } else if (title.includes('5k')) {
                setRaceDist(5, '5K');
            }
        };

        watch(showRaceModal, (val) => {
            if (val) fetchRuangLariEvents();
        });

        watch(showStravaGraphModal, (val) => {
            if (val && detail.strava_streams) {
                setTimeout(() => {
                    if (stravaFullscreenChart) stravaFullscreenChart.destroy();
                    stravaFullscreenChart = renderChartToCanvas(detail.strava_streams, 'stravaMetricsChartFullscreen');
                }, 100);
            } else {
                if (stravaFullscreenChart) {
                    stravaFullscreenChart.destroy();
                    stravaFullscreenChart = null;
                }
            }
        });

        const showPbModal = ref(false);
        const pbLoading = ref(false);
        const pbForm = reactive({
            pb_5k: trainingProfile.value.pb?.['5k'] || '',
            pb_10k: trainingProfile.value.pb?.['10k'] || '',
            pb_hm: trainingProfile.value.pb?.hm || '',
            pb_fm: trainingProfile.value.pb?.fm || '',
        });

        // VDOT Form State
        const showVdotModal = ref(false);
        const vdotLoading = ref(false);
        const vdotForm = reactive({
            age: 25,
            gender: 'male',
            race_distance: '5k',
            race_time: '00:25:00',
            race_date: new Date().toISOString().slice(0,10),
            weekly_mileage: 20,
            peak_mileage: 30,
            training_frequency: 3,
            goal_distance: '10k',
            goal_race_date: new Date(Date.now() + 120 * 24 * 60 * 60 * 1000).toISOString().slice(0,10), // ~4 months from now
            goal_time: ''
        });

        const openVdotModal = async () => {
            try {
                console.log('[RunnerCalendar] openVdotModal');
                showDetailModal.value = false;
                showFormModal.value = false;
                showRaceModal.value = false;
                // force close first so Vue applies DOM updates before re-opening
                showVdotModal.value = false;
                await nextTick();
                showVdotModal.value = true;
            } catch (e) {
                console.error('[RunnerCalendar] openVdotModal failed', e);
                // fallback: try to show anyway
                showVdotModal.value = true;
            }
        };

        let calendar = null;

        // ... existing methods ...

        const syncTraining = async () => {
            syncLoading.value = true;
            try {
                // Refresh plans and calendar events
                await loadPlans();
                if (calendar) calendar.refetchEvents();
                // Optionally refresh profile if needed, but usually profile drives the sync
                alert('Training paces synced with current profile!');
            } catch (e) {
                alert('Failed to sync training.');
            } finally {
                syncLoading.value = false;
            }
        };

        const resetPlan = async (enrollmentId) => {
            if(!confirm('Are you sure you want to reset this plan? Progress will be lost and it will be moved back to your Program Bag.')) return;
            
            try {
                const res = await fetch(`{{ route('runner.calendar.reset-plan') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId })
                });
                const data = await res.json();
                if (data.success) {
                    // Refresh page to sync everything properly
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reset plan');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        const applyProgram = async (enrollmentId) => {
            const startDate = prompt('Enter Start Date (YYYY-MM-DD):', new Date().toISOString().slice(0,10));
            if(!startDate) return;

            try {
                const res = await fetch(`{{ route('runner.calendar.apply-program') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId, start_date: startDate })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to apply program');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        const restoreProgram = async (enrollmentId) => {
            if(!confirm('Restore this program to your Available Bag?')) return;
            
            try {
                const res = await fetch(`{{ route('runner.calendar.restore-program') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to restore program');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        // Reschedule Logic
        const showRescheduleModal = ref(false);
        const rescheduleLoading = ref(false);
        const rescheduleTarget = ref(null);
        const rescheduleForm = reactive({
            new_start_date: ''
        });

        const openRescheduleModal = (enrollment) => {
            rescheduleTarget.value = enrollment;
            rescheduleForm.new_start_date = enrollment.start_date ? enrollment.start_date.slice(0,10) : new Date().toISOString().slice(0,10);
            showRescheduleModal.value = true;
        };

        const submitReschedule = async () => {
            if (!rescheduleForm.new_start_date) {
                alert('Please select a new start date');
                return;
            }
            
            rescheduleLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.reschedule-program') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ 
                        enrollment_id: rescheduleTarget.value.id,
                        new_start_date: rescheduleForm.new_start_date
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Program rescheduled successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reschedule program');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                rescheduleLoading.value = false;
            }
        };



        // GUIDED WORKOUT STATE
        const showGuidedPlayer = ref(false);
        const guidedExercises = ref([]);
        const currentExerciseIndex = ref(0);
        const currentExercise = computed(() => guidedExercises.value[currentExerciseIndex.value]);
        const isPlaying = ref(false);
        const timerSeconds = ref(0);
        let timerInterval = null;

        const countExercises = (d) => {
            if (d.strength?.plan && Array.isArray(d.strength.plan)) return d.strength.plan.length;
            const parsed = parseStrengthExercises(d);
            return parsed.length;
        };

        const parseStrengthExercises = (d) => {
            // 1. Try structured plan
            if (d.strength?.plan && Array.isArray(d.strength.plan)) {
                return d.strength.plan.map(ex => ({
                    name: ex.name || 'Exercise',
                    sets: ex.sets || '3',
                    reps: ex.reps || '10',
                    notes: ex.notes || ''
                }));
            }
            
            // 2. Try parsing description text (Simple Heuristic)
            // Assumes lines like: "3x15 Squats" or "Pushups: 3 sets of 10"
            if (d.description) {
                const lines = d.description.split('\n').filter(l => l.trim().length > 0);
                const exercises = [];
                
                lines.forEach(line => {
                    // Very basic parser: look for digits
                    const hasNumbers = /\d/.test(line);
                    if (hasNumbers) {
                        exercises.push({
                            name: line.replace(/^\d+x\d+\s*/, '').trim(), // Remove "3x10 " prefix if present
                            sets: (line.match(/(\d+)\s*(?:sets|x)/i) || ['','3'])[1],
                            reps: (line.match(/(?:x|of)\s*(\d+)/i) || ['','10'])[1],
                            notes: line
                        });
                    }
                });
                
                if (exercises.length > 0) return exercises;
            }

            // 3. Fallback
            return [
                { name: 'Warm Up', sets: '1', reps: '5 min', notes: 'General mobility' },
                { name: 'Main Circuit', sets: '3', reps: '12', notes: 'Check description for details' },
                { name: 'Cool Down', sets: '1', reps: '5 min', notes: 'Stretching' }
            ];
        };

        const getExerciseIcon = (name) => {
            const n = name.toLowerCase();
            if (n.includes('squat')) return '🏋️';
            if (n.includes('push')) return '💪';
            if (n.includes('plank') || n.includes('core')) return '🧱';
            if (n.includes('lunge')) return '🦵';
            if (n.includes('run') || n.includes('warm')) return '🏃';
            if (n.includes('yoga') || n.includes('stretch')) return '🧘';
            return '⚡';
        };

        const startGuidedWorkout = (d) => {
            guidedExercises.value = parseStrengthExercises(d);
            currentExerciseIndex.value = 0;
            showGuidedPlayer.value = true;
            isPlaying.value = false;
            timerSeconds.value = 0;
        };

        const togglePlay = () => {
            isPlaying.value = !isPlaying.value;
            if (isPlaying.value) {
                timerInterval = setInterval(() => {
                    timerSeconds.value++;
                }, 1000);
            } else {
                clearInterval(timerInterval);
            }
        };

        const nextExercise = () => {
            if (currentExerciseIndex.value < guidedExercises.value.length - 1) {
                currentExerciseIndex.value++;
                resetTimer();
            }
        };

        const prevExercise = () => {
            if (currentExerciseIndex.value > 0) {
                currentExerciseIndex.value--;
                resetTimer();
            }
        };

        const resetTimer = () => {
            isPlaying.value = false;
            clearInterval(timerInterval);
            timerSeconds.value = 0;
        };

        const previewExercise = (ex) => {
            // In future: Show modal preview. For now, we rely on the list view.
            console.log('Preview', ex);
        };

        const updatePb = async () => {
            pbLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.update-pb') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(pbForm)
                });
                const data = await res.json();
                if (data.success) {
                    trainingProfile.value.vdot = data.vdot;
                    trainingProfile.value.paces = data.paces;
                    if(data.equivalent_race_times) {
                        trainingProfile.value.equivalent_race_times = data.equivalent_race_times;
                    }
                    showPbModal.value = false;
                    alert('PB updated! Training paces and equivalent race times recalculated.');
                } else {
                    alert(data.message || 'Failed to update PB');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                pbLoading.value = false;
            }
        };

        const updateWeeklyTarget = async () => {
            weeklyTargetLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.update-weekly-target') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(weeklyTargetForm)
                });
                const data = await res.json();
                if (data.success) {
                    trainingProfile.value.weekly_km_target = data.weekly_km_target;
                    showWeeklyTargetModal.value = false;
                    alert('Weekly target updated!');
                } else {
                    alert(data.message || 'Failed to update weekly target');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                weeklyTargetLoading.value = false;
            }
        };

        const generateVdot = async () => {
            vdotLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.programs.generate') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(vdotForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert('Program generated successfully! It has been added to your Program Bag.');
                    showVdotModal.value = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to generate program');
                }
            } catch (e) {
                alert('An error occurred while generating program');
            } finally {
                vdotLoading.value = false;
            }
        };


        const resetPlanList = async () => {
            if(!confirm('Are you sure? This will STOP all active programs and move them back to your Program Bag. All progress will be reset.')) return;
            
            try {
                const res = await fetch(`{{ route('runner.calendar.reset-plan-list') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reset plans');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        const formatPace = (minPerKm) => {
            if (!minPerKm) return '-';
            const mins = Math.floor(minPerKm);
            const secs = Math.round((minPerKm - mins) * 60);
            return `${mins}:${secs.toString().padStart(2,'0')}`;
        };

        const formatSeconds = (s) => {
            const sec = parseInt(s || 0, 10);
            if (!sec || sec < 0) return '-';
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const ss = sec % 60;
            if (h > 0) return `${h}:${String(m).padStart(2,'0')}:${String(ss).padStart(2,'0')}`;
            return `${m}:${String(ss).padStart(2,'0')}`;
        };

        const extractStravaActivityId = (url) => {
            const m = String(url || '').match(/strava\.com\/activities\/(\d+)/i);
            return m ? parseInt(m[1], 10) : null;
        };

        const destroyStravaChart = () => {
            try {
                if (stravaChart) {
                    stravaChart.destroy();
                }
            } catch (e) {}
            stravaChart = null;
        };

        const toPaceSecPerKm = (mps) => {
            const v = parseFloat(mps || 0);
            if (!v || v <= 0) return null;
            return 1000 / v;
        };

        const formatPaceFromSec = (secPerKm) => {
            const s = parseFloat(secPerKm || 0);
            if (!s || s <= 0) return '-';
            const mins = Math.floor(s / 60);
            const secs = Math.round(s - (mins * 60));
            return `${mins}:${String(secs).padStart(2,'0')}`;
        };

        const renderChartToCanvas = (streams, canvasId) => {
            if (!streams || !streams.time || !window.Chart) return null;
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;

            const time = Array.isArray(streams.time) ? streams.time : [];
            const hr = Array.isArray(streams.heartrate) ? streams.heartrate : [];
            const cad = Array.isArray(streams.cadence) ? streams.cadence : [];
            const vel = Array.isArray(streams.velocity_smooth) ? streams.velocity_smooth : [];
            const watts = Array.isArray(streams.watts) ? streams.watts : [];

            const n = time.length;
            if (n === 0) return null;

            const maxPoints = 320;
            const step = n > maxPoints ? Math.ceil(n / maxPoints) : 1;

            const labels = [];
            const pace = [];
            const hrS = [];
            const cadS = [];
            const wattsS = [];

            for (let i = 0; i < n; i += step) {
                labels.push(formatSeconds(time[i]));
                pace.push(toPaceSecPerKm(vel[i]));
                hrS.push(typeof hr[i] === 'number' ? hr[i] : (hr[i] ? parseFloat(hr[i]) : null));
                cadS.push(typeof cad[i] === 'number' ? cad[i] : (cad[i] ? parseFloat(cad[i]) : null));
                wattsS.push(typeof watts[i] === 'number' ? watts[i] : (watts[i] ? parseFloat(watts[i]) : null));
            }

            const datasets = [
                {
                    label: 'Pace',
                    data: pace,
                    borderColor: '#06B6D4',
                    backgroundColor: 'rgba(6,182,212,0.08)',
                    yAxisID: 'yPace',
                    pointRadius: 0,
                    borderWidth: 2,
                    spanGaps: true,
                },
            ];

            if (hrS.some(v => v !== null && !Number.isNaN(v))) {
                datasets.push({
                    label: 'Heart Rate',
                    data: hrS,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239,68,68,0.08)',
                    yAxisID: 'yMetric',
                    pointRadius: 0,
                    borderWidth: 1.5,
                    spanGaps: true,
                });
            }
            if (cadS.some(v => v !== null && !Number.isNaN(v))) {
                datasets.push({
                    label: 'Cadence',
                    data: cadS,
                    borderColor: '#A855F7',
                    backgroundColor: 'rgba(168,85,247,0.08)',
                    yAxisID: 'yMetric',
                    pointRadius: 0,
                    borderWidth: 1.5,
                    spanGaps: true,
                });
            }
            if (wattsS.some(v => v !== null && !Number.isNaN(v))) {
                datasets.push({
                    label: 'Power',
                    data: wattsS,
                    borderColor: '#22C55E',
                    backgroundColor: 'rgba(34,197,94,0.08)',
                    yAxisID: 'yMetric',
                    pointRadius: 0,
                    borderWidth: 1.5,
                    spanGaps: true,
                });
            }

            return new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: { color: '#CBD5E1', boxWidth: 10 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const ds = context.dataset;
                                    const val = context.parsed.y;
                                    if (ds.label === 'Pace') return ` Pace: ${formatPaceFromSec(val)} /km`;
                                    if (ds.label === 'Heart Rate') return ` HR: ${Math.round(val)} bpm`;
                                    if (ds.label === 'Cadence') return ` Cadence: ${Math.round(val)} spm`;
                                    if (ds.label === 'Power') return ` Power: ${Math.round(val)} w`;
                                    return ` ${ds.label}: ${val}`;
                                }
                            }
                        }
                    },
                    elements: { line: { tension: 0.25 } },
                    scales: {
                        x: {
                            ticks: { color: '#64748B', maxTicksLimit: 6 },
                            grid: { color: 'rgba(51,65,85,0.35)' }
                        },
                        yPace: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            reverse: true,
                            ticks: {
                                color: '#94A3B8',
                                callback: function (v) { return formatPaceFromSec(v); }
                            },
                            grid: { color: 'rgba(51,65,85,0.35)' }
                        },
                        yMetric: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            ticks: { color: '#94A3B8' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        };

        const renderStravaChart = (streams) => {
            destroyStravaChart();
            stravaChart = renderChartToCanvas(streams, 'stravaMetricsChart');
        };

        const loadStravaStreams = async (activityId) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;

            stravaStreamsLoading.value = true;
            stravaStreamsError.value = '';
            detail.strava_streams = null;
            destroyStravaChart();

            try {
                const res = await fetch(`${runnerUrl}/strava/activities/${id}/streams`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data && data.success) {
                    detail.strava_streams = data.streams || null;
                    setTimeout(() => renderStravaChart(detail.strava_streams), 50);
                } else {
                    stravaStreamsError.value = (data && data.message) ? data.message : 'Gagal mengambil streams Strava.';
                }
            } catch (e) {
                stravaStreamsError.value = 'Gagal mengambil streams Strava.';
            } finally {
                stravaStreamsLoading.value = false;
            }
        };

        const generateStravaAnalysis = (metrics) => {
            if (!metrics) return { analysis: 'Data tidak cukup untuk analisis.', suggestion: 'Lanjutkan latihan sesuai rencana.' };

            const avgHr = metrics.average_heartrate;
            const dist = metrics.distance_m ? metrics.distance_m / 1000 : 0;
            const pace = metrics.pace; // string "5:30"
            
            let analysis = [];
            let suggestion = "";

            // Intensity Analysis based on HR (Simple heuristic)
            let intensity = 'moderate';
            if (avgHr) {
                if (avgHr < 140) {
                    analysis.push("Lari ini berada di zona aerobik ringan, bagus untuk membangun base endurance tanpa kelelahan berlebih.");
                    intensity = 'easy';
                } else if (avgHr >= 140 && avgHr < 160) {
                    analysis.push("Usaha yang solid di zona aerobik/steady. Jantung bekerja efisien.");
                    intensity = 'moderate';
                } else {
                    analysis.push("Intensitas tinggi terdeteksi. Latihan ini melatih ambang laktat dan VO2Max.");
                    intensity = 'hard';
                }
            } else {
                analysis.push("Data detak jantung tidak tersedia, namun berdasarkan pace, usaha terlihat konsisten.");
            }

            // Distance Context
            if (dist > 15) {
                analysis.push("Long run yang hebat! Ketahanan otot sedang diuji.");
                intensity = 'hard'; // Long runs are hard on the body
            } else if (dist < 5) {
                analysis.push("Lari jarak pendek yang baik untuk recovery atau speed work.");
            }

            // Suggestion
            if (intensity === 'hard') {
                suggestion = "Tubuh Anda butuh pemulihan. Saran: Besok ambil Rest Day atau Recovery Run santai (30-45 menit Zone 1-2). Fokus pada hidrasi dan tidur.";
            } else if (intensity === 'moderate') {
                suggestion = "Kondisi masih oke. Next workout bisa berupa Easy Run atau Cross Training ringan.";
            } else {
                suggestion = "Anda masih segar. Next workout siap untuk sesi kualitas (Interval/Tempo) atau Long Run jika jadwal memungkinkan.";
            }

            return {
                analysis: analysis.join(' '),
                suggestion: suggestion
            };
        };

        const loadStravaDetails = async (activityId) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;

            stravaDetailsLoading.value = true;
            stravaDetailsError.value = '';
            detail.strava_metrics = null;
            detail.strava_splits = [];
            detail.strava_laps = [];
            detail.actual_pace = null;
            detail.strava_streams = null;
            detail.analysis = null;
            detail.suggestion = null;
            stravaStreamsError.value = '';
            destroyStravaChart();

            try {
                const res = await fetch(`${runnerUrl}/strava/activities/${id}/details`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data && data.success) {
                    const a = data.activity || {};
                    detail.strava_metrics = a;
                    detail.actual_pace = a.pace ? `${a.pace} /km` : null;
                    detail.strava_splits = Array.isArray(a.splits_metric) ? a.splits_metric : [];
                    detail.strava_laps = Array.isArray(a.laps) ? a.laps : [];
                    
                    // Generate Analysis
                    const ana = generateStravaAnalysis(a);
                    detail.analysis = ana.analysis;
                    detail.suggestion = ana.suggestion;

                    loadStravaStreams(id);
                } else {
                    stravaDetailsError.value = (data && data.message) ? data.message : 'Gagal mengambil detail Strava.';
                }
            } catch (e) {
                stravaDetailsError.value = 'Gagal mengambil detail Strava.';
            } finally {
                stravaDetailsLoading.value = false;
            }
        };

        const setFilter = async (f) => {
            filter.value = f;
            await loadPlans();
        };

        const loadPlans = async () => {
            plansLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.workout-plans') }}?filter=${filter.value}`, { headers: { 'Accept':'application/json' }});
                const data = await res.json();
                plans.value = Array.isArray(data) ? data : [];
            } catch (e) {
                plans.value = [];
            } finally {
                plansLoading.value = false;
            }
        };

        const loadWeeklyVolume = async () => {
            try {
                const res = await fetch(`{{ route('runner.calendar.weekly-volume') }}`, { headers: { 'Accept':'application/json' }});
                const data = await res.json();
                weeklyVolume.value = Array.isArray(data) ? data : [];
            } catch (e) {
                weeklyVolume.value = [];
            }
        };

        const handleEventDrop = async (info) => {
            if (!confirm(`Reschedule ${info.event.title} to ${info.event.startStr}?`)) {
                info.revert();
                return;
            }
            
            const props = info.event.extendedProps;
            const payload = {
                type: props.type,
                new_date: info.event.startStr,
            };
            
            if (props.type === 'custom_workout') {
                payload.workout_id = props.workout_id;
            } else {
                payload.enrollment_id = props.enrollment_id;
                payload.session_day = props.session.day;
            }
            
            try {
                const res = await fetch(`{{ route('runner.calendar.reschedule') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    if (data.message && data.message.includes('ditukar')) {
                         alert(data.message);
                    }
                    // Update plans list as well
                    await loadPlans();
                    await loadWeeklyVolume();
                } else {
                    alert(data.message || 'Failed to reschedule');
                    info.revert();
                }
            } catch (e) {
                alert('An error occurred');
                info.revert();
            }
        };

        const initCalendar = () => {
            const el = document.getElementById('calendar');
            if (!el) {
                console.error('[RunnerCalendar] Calendar element not found');
                return; // Guard against null element
            }
            
            const isMobile = window.innerWidth < 768;
            const initialView = isMobile ? 'listMonth' : 'dayGridMonth';
            const headerToolbar = isMobile 
                ? { left: 'prev,next', center: 'title', right: 'listMonth' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' };

            calendar = new FullCalendar.Calendar(el, {
                initialView: initialView,
                headerToolbar: headerToolbar,
                events: '{{ route("runner.calendar.events") }}',
                locale: 'id',
                firstDay: 1,
                editable: true, // Enable drag & drop
                eventDrop: handleEventDrop, // Handle drop
                eventClassNames: (arg) => {
                    const cls = [];
                    const props = arg.event.extendedProps || {};
                    if (props.difficulty) cls.push('difficulty-' + props.difficulty);
                    if (props.phase) cls.push('phase-' + props.phase);
                    const t = (props.session && props.session.type) || (props.workout && props.workout.type) || props.activity_type || props.type;
                    if (t) cls.push('workout-' + t);
                    return cls;
                },
                dateClick: (info) => { openForm(info.dateStr); },
                eventClick: (info) => { showEventDetail(info); info.jsEvent.preventDefault(); },
                height: 'auto',
                listDayFormat: { month: 'short', day: 'numeric', weekday: 'short' }, // For list view header
                listDaySideFormat: false // Hide the side text
            });
            calendar.render();
        };

        const openForm = (dateStr) => {
            form.workout_id = '';
            form.workout_date = dateStr;
            form.type = 'run';
            form.difficulty = 'moderate';
            form.distance = '';
            form.duration = '';
            form.description = '';
            form.workout_structure = [];
            showVdotModal.value = false;
            showRaceModal.value = false;
            showFormModal.value = true;
        };

        const openFormForToday = async () => { 
            try {
                console.log('[RunnerCalendar] openFormForToday');
                // ensure other modals are closed first
                showDetailModal.value = false;
                showVdotModal.value = false;
                showRaceModal.value = false;
                showFormModal.value = false;
                await nextTick();

                const d = new Date();
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                openForm(`${yyyy}-${mm}-${dd}`);
            } catch (e) {
                console.error('[RunnerCalendar] openFormForToday failed', e);
            }
        };

        const closeForm = () => { showFormModal.value = false; };

        // Race helpers
        const setRaceDist = (val, label) => {
            raceForm.distance = val;
            raceForm.distLabel = label;
        };

        const openRaceForm = async () => {
            try {
                console.log('[RunnerCalendar] openRaceForm');
                raceForm.name = '';
                raceForm.date = new Date().toISOString().slice(0,10);
                raceForm.distance = '10';
                raceForm.distLabel = '10K';
                raceForm.goal_time = '';
                raceForm.notes = '';

                // ensure other modals are closed first
                showDetailModal.value = false;
                showVdotModal.value = false;
                showFormModal.value = false;
                showRaceModal.value = false;
                await nextTick();

                showRaceModal.value = true;
            } catch (e) {
                console.error('[RunnerCalendar] openRaceForm failed', e);
                // fallback
                showRaceModal.value = true;
            }
        };
        const saveRace = async () => {
            try {
                const payload = {
                    workout_date: raceForm.date,
                    type: 'race',
                    difficulty: 'hard',
                    distance: raceForm.distance || null,
                    description: raceForm.notes || null,
                    workout_structure: {
                        race_name: raceForm.name,
                        goal_time: raceForm.goal_time,
                        dist_label: raceForm.distLabel
                    }
                };
                const res = await fetch(`{{ route('runner.calendar.custom-workout.store') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showRaceModal.value = false;
                    if (calendar) calendar.refetchEvents();
                    await loadPlans();
                } else {
                    alert(data.message || 'Failed to save race');
                }
            } catch (e) {
                alert('An error occurred while saving race');
            }
        };

        const saveCustomWorkout = async () => {
            const payload = {
                workout_id: form.workout_id || '',
                workout_date: form.workout_date,
                type: form.type,
                difficulty: form.difficulty,
                distance: form.distance || null,
                duration: form.duration || null,
                description: form.description || null,
                workout_structure: form.workout_structure.length > 0 ? form.workout_structure : null,
            };
            try {
                const res = await fetch(`{{ route('runner.calendar.custom-workout.store') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showFormModal.value = false;
                    if (calendar) calendar.refetchEvents();
                    await loadPlans();
                }
            } catch {}
        };

        // Helper to calculate recommended pace
        const calculateRecommendedPace = (type, distance = null) => {
            if (!type) return null;
            const t = type.toLowerCase();
            const map = { 
                easy_run: 'E', recovery: 'E', run: 'E', 
                long_run: 'M', 
                tempo: 'T', threshold: 'T', 
                interval: 'I', vo2max: 'I',
                repetition: 'R', speed: 'R',
                strength: null, rest: null, yoga: null, cycling: null
            };
            
            const key = map[t]; 
            if (!key) return null;

            // Check for specific Track times (Interval, Repetition, Threshold)
            // User requested specific logic for 0.1-2km to take from track tab
            if (['I', 'R', 'T'].includes(key) && distance && trainingProfile.value?.track_times) {
                const distKm = parseFloat(distance);
                // Check if distance is between 0.1 and 2.0 km (approx)
                if (distKm >= 0.1 && distKm <= 2.0) {
                    // Logic override: If Interval (I) and distance 100-400m, use Repetition (R) pace
                    let useKey = key;
                    if (key === 'I' && distKm >= 0.1 && distKm <= 0.405) { // 0.405 to cover slightly over 400m due to float precision
                        useKey = 'R';
                    }

                    // Try to match standard track distances
                    const m = Math.round(distKm * 1000);
                    
                    // We check if exact key exists
                    const trackKey = m + 'm';
                    
                    if (trainingProfile.value.track_times[trackKey]) {
                        const trackData = trainingProfile.value.track_times[trackKey];
                        // Get the specific pace type from trackData (I, R, or T)
                        const splitTime = trackData[useKey]; 
                        const pacePerKm = trackData['pace_' + useKey];
                        
                        if (splitTime) {
                            return `${splitTime} (${pacePerKm} /km)`;
                        }
                    }
                }
            }

            let val = trainingProfile.value?.paces?.[key];
            if (!val && key === 'M') val = trainingProfile.value?.paces?.['E'];
            
            return val ? (formatPace(val) + ' /km') : null;
        };

        const showEventDetail = (info) => {
            showVdotModal.value = false;
            showFormModal.value = false;
            showRaceModal.value = false;
            const props = info.event.extendedProps || {};
            stravaDetailsLoading.value = false;
            stravaDetailsError.value = '';
            detail.strava_metrics = null;
            detail.strava_splits = [];
            detail.strava_laps = [];
            detail.actual_pace = null;
            if (props.type === 'program_session') {
                const s = props.session || {};
                detailTitle.value = props.program_title || 'Program Session';
                detail.date = info.event.startStr;
                detail.date_formatted = null;
                detail.type = s.type || 'run';
                detail.distance = s.distance || null;
                detail.duration = s.duration || null;
                detail.program_difficulty = props.difficulty || null;
                detail.description = s.description || null;
                detail.status = s.status || 'pending';
                detail.enrollment_id = props.enrollment_id;
                detail.session_day = s.day;
                detail.target_pace = props.target_pace || null;
                
                // Hide target pace for non-running activities
                if (['strength', 'rest', 'yoga', 'cycling'].includes(detail.type)) {
                    detail.target_pace = null;
                }

                detail.recommended_pace = calculateRecommendedPace(detail.type, detail.distance);
                
                // Fetch tracking data if available in session object or need separate call
                // Assuming session object might have tracking info if passed from backend
                // But fullcalendar events usually minimal. 
                // We might need to check tracking status from extendedProps if available
                
                stravaLinkInput.value = '';
                notesInput.value = '';
            } else if (props.type === 'custom_workout') {
                const w = props.workout || {};
                detailTitle.value = w.type === 'race' ? (w.workout_structure?.race_name || 'Race Event') : 'Custom Workout';
                detail.date = info.event.startStr;
                detail.type = w.type || 'run';
                detail.distance = w.distance || null;
                detail.duration = w.duration || null;
                detail.difficulty = w.difficulty || null;
                detail.description = w.description || null;
                detail.status = w.status || 'pending';
                detail.workout_id = w.id || props.workout_id || null;
                detail.workout_structure = w.workout_structure || null;
                detail.source = 'custom';
                
                // Hide target pace for non-running activities
                if (['strength', 'rest', 'yoga', 'cycling'].includes(detail.type)) {
                    detail.target_pace = null;
                }

                detail.recommended_pace = calculateRecommendedPace(detail.type, detail.distance);
            } else if (props.type === 'strava_activity') {
                detailTitle.value = 'Strava Activity';
                detail.date = info.event.startStr;
                detail.type = props.activity_type || 'run';
                detail.distance = props.distance_km || null;
                detail.duration = props.moving_time_s ? Math.round(props.moving_time_s / 60) + ' min' : null;
                detail.difficulty = null;
                detail.description = props.name || null;
                detail.status = 'imported';
                detail.source = 'strava';
                detail.target_pace = null;
                detail.recommended_pace = null;

                stravaLinkInput.value = props.strava_url || '';
                notesInput.value = 'Imported from Strava sync';

                if (props.strava_activity_id) {
                    loadStravaDetails(props.strava_activity_id);
                }
            }
            showDetailModal.value = true;
        };

        const showPlanDetail = (plan) => {
            detailTitle.value = plan.program_title || 'Program Session';
            detail.date_formatted = plan.date_formatted || null;
            detail.type = plan.activity_type || plan.type; // Use activity_type for custom workouts if available
            detail.distance = plan.distance || null;
            detail.duration = plan.duration || null;
            detail.program_difficulty = plan.program_difficulty || null;
            detail.difficulty = plan.difficulty || null;
            detail.description = plan.description || null;
            detail.status = plan.status || 'pending';
            detail.enrollment_id = plan.enrollment_id;
            detail.session_day = plan.session_day;
            detail.strava_link = plan.strava_link || null;
            detail.notes = plan.notes || null;
            detail.workout_id = plan.workout_id || null;
            detail.workout_structure = plan.workout_structure || null;
            detail.source = plan.source || (plan.enrollment_id ? 'program' : null);
            detail.target_pace = plan.target_pace || null;
            
            // Hide target pace for non-running activities
            if (['strength', 'rest', 'yoga', 'cycling'].includes(detail.type)) {
                detail.target_pace = null;
            }

            detail.recommended_pace = calculateRecommendedPace(detail.type, detail.distance);

            detail.coach_feedback = plan.coach_feedback || null;
            detail.coach_rating = plan.coach_rating || null;
            
            // Reset form
            stravaLinkInput.value = plan.strava_link || '';
            notesInput.value = plan.notes || '';
            rpeInput.value = plan.rpe || '';
            feelingInput.value = plan.feeling || '';

            stravaDetailsLoading.value = false;
            stravaDetailsError.value = '';
            detail.strava_metrics = null;
            detail.strava_splits = [];
            detail.strava_laps = [];
            detail.actual_pace = null;

            const stravaId = extractStravaActivityId(detail.strava_link || stravaLinkInput.value);
            if (stravaId) {
                loadStravaDetails(stravaId);
            }

            showDetailModal.value = true;
        };

        const closeDetail = () => {
            destroyStravaChart();
            showDetailModal.value = false;
        };

        const deleteCustomWorkout = async (workoutId) => {
            try {
                const res = await fetch(`{{ url('/runner/calendar/custom-workout') }}/${workoutId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ _method:'DELETE' })
                });
                const data = await res.json();
                if (data.success) {
                    showDetailModal.value = false;
                    if (calendar) calendar.refetchEvents();
                    await loadPlans();
                }
            } catch {}
        };

        const updateSessionStatus = async (plan, status, stravaLink = null, notes = null, rpe = null, feeling = null) => {
            console.log('updateSessionStatus plan:', plan);
            try {
                const payload = { status };
                
                // Enhanced logic for identifying custom vs program session
                if (plan.enrollment_id) {
                     payload.enrollment_id = plan.enrollment_id;
                     payload.session_day = plan.session_day;
                } else if (plan.type === 'custom_workout' || (plan.id && String(plan.id).startsWith('custom_')) || plan.workout_id) {
                    payload.workout_id = plan.workout_id || (plan.id ? String(plan.id).replace('custom_', '') : null);
                }

                if (stravaLink && stravaLink.trim()) payload.strava_link = stravaLink.trim();
                if (notes && notes.trim()) payload.notes = notes.trim();
                if (rpe) payload.rpe = rpe;
                if (feeling) payload.feeling = feeling;
                
                console.log('Sending payload:', payload);

                const res = await fetch(`{{ route('runner.calendar.update-session-status') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await res.json();
                
                if (!res.ok) {
                    console.error('Error response:', data);
                    let errorMsg = data.message || 'Validation failed';
                    if (data.errors) {
                        errorMsg += '\n' + Object.values(data.errors).flat().join('\n');
                    }
                    alert('Error: ' + errorMsg);
                    return false;
                }

                if (data.success) {
                    // Update local state if detail modal is open
                    if (showDetailModal.value) {
                        // Check match (simple check since we often operate on detail itself)
                        const isSamePlan = (plan.enrollment_id && detail.enrollment_id === plan.enrollment_id && detail.session_day === plan.session_day) ||
                                         (plan.workout_id && detail.workout_id === plan.workout_id) ||
                                         (plan.id === detail.id);

                        if (isSamePlan) {
                            detail.status = status;
                            if (stravaLink) detail.strava_link = stravaLink;
                            if (notes) detail.notes = notes;
                            if (status === 'completed') showDetailModal.value = false;
                        }
                    }
                    await loadPlans();
                    if (calendar) calendar.refetchEvents();
                    return true;
                } else {
                     alert(data.message || 'Failed to update status');
                     return false;
                }
            } catch (e) {
                console.error('Exception:', e);
                alert('An error occurred: ' + e.message);
                return false;
            }
        };

        const finishActivityWithLink = async () => {
            await updateSessionStatus(detail, 'completed', stravaLinkInput.value, notesInput.value, rpeInput.value, feelingInput.value);
            // Close modal after success or update UI
        };

        const deleteEnrollment = async (enrollmentId) => {
            try {
                const res = await fetch(`{{ url('/runner/calendar/enrollment') }}/${enrollmentId}/delete`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    enrollments.value = enrollments.value.filter(e => e.id !== enrollmentId);
                    await loadPlans();
                    if (calendar) calendar.refetchEvents();
                }
            } catch {}
        };

        const dayName = (d) => {
            const names = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            const idx = new Date(d).getDay();
            return names[idx] || 'Day';
        };
        const statusText = (s) => (s==='completed' || s==='imported') ? 'Finished' : (s==='started' ? 'On Progress' : 'UNFINISHED');
        const statusClass = (s) => (s==='completed' || s==='imported') ? 'text-green-400' : (s==='started' ? 'text-yellow-400' : 'text-red-400');
        const activityLabel = (t) => ({
            running:'Running', run:'Run', easy_run:'Easy Run', 
            interval:'Interval', tempo:'Tempo', long_run:'Long Run', recovery:'Recovery',
            yoga:'Yoga', cycling:'Cycling', rest:'Rest', strength:'Strength'
        }[t] || 'Running');
        const formatDate = (d) => { try { const dt = new Date(d); return dt.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }); } catch { return d; } };

        const assetStorage = @json(asset('storage'));
        const assetProfile = @json(asset('images/profile/profile.png'));
        const runnerUrl = @json(url('/runner'));
        const chatUrl = @json(url('/chat'));

        const chatCoach = (coach) => {
            try {
                if (window.openChat && coach) {
                    window.openChat(coach.id, coach.name || 'Coach', coach.avatar || null, "Hai Coach saya butuh bantuan");
                    return;
                }
            } catch(e){}
            if (coach) {
                window.location.href = `{{ url('/chat') }}/${coach.id}`;
            }
        };

        setFilter('unfinished');
        loadPlans();
        loadWeeklyVolume();
        
        onMounted(() => {
            initCalendar();
        });

        return { filter, plans, plansLoading, enrollments, programBag, setFilter, dayName, statusText, statusClass, activityLabel, formatDate,
            showDetailModal, detail, detailTitle, closeDetail, deleteCustomWorkout,
            showFormModal, form, openFormForToday, closeForm, saveCustomWorkout, showPlanDetail, updateSessionStatus, deleteEnrollment,
            resetPlan, applyProgram, showVdotModal, openVdotModal, vdotForm, vdotLoading, generateVdot, resetPlanList,
            trainingProfile, formatPace, showPbModal, pbForm, pbLoading, updatePb, bagTab, cancelledPrograms, restoreProgram,
            stravaLinkInput, notesInput, rpeInput, feelingInput, finishActivityWithLink, profileTab, chatCoach,
            addStep, removeStep, moveStep, calculateTotalDistance, syncTraining, syncLoading, weeklyVolume, maxVolume,
            assetStorage, assetProfile, runnerUrl, chatUrl, 
            showRaceModal, raceForm, openRaceForm, saveRace, setRaceDist,
            showWeeklyTargetModal, weeklyTargetForm, weeklyTargetLoading, updateWeeklyTarget,
            ruangLariEvents, loadingEvents, onSelectRuangLariEvent, eventSearchQuery, showEventDropdown, filteredEvents, selectRuangLariEvent,
            stravaDetailsLoading, stravaDetailsError, formatSeconds, displayPace, primaryMetricValue, primaryMetricUnit, statusDotClass,
            showRescheduleModal, rescheduleTarget, rescheduleForm, rescheduleLoading, openRescheduleModal, submitReschedule,
            showStravaGraphModal
        };
    }

}).mount('#runner-calendar-app');
</script>
@endpush