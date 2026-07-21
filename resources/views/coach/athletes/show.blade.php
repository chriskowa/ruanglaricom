@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Monitor Athlete')

@push('styles')
<style>
.glass-panel{background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.05)}
.fc .fc-toolbar-title{font-size: 1rem;font-weight:800;color:#e2e8f0}
.fc .fc-button{background:#1e293b;border-color:#334155;color:#cbd5e1}
.fc .fc-button:hover{color:#ccff00;border-color:#ccff00}
.fc-event{border:none;border-radius:6px;cursor:pointer;padding:2px 4px;font-size:0.75rem;}
/* Calendar Dark Mode Overrides */
.fc-theme-standard .fc-scrollgrid { border-color: #334155; }
.fc-theme-standard td, .fc-theme-standard th { border-color: #334155; }
.fc .fc-daygrid-day-number { color: #94a3b8; text-decoration: none; }
.fc .fc-col-header-cell-cushion { color: #94a3b8; text-decoration: none; }
.fc-day-today { background-color: rgba(204, 255, 0, 0.05) !important; }
.fc-daygrid-day-frame { min-height: 80px; }
.fc .fc-daygrid-day.fc-day-other { background-color: rgba(0,0,0,0.2); }

/* Mobile List View Styling */
.fc-list { border: none !important; background: transparent !important; }
.fc-list-day-cushion { background-color: transparent !important; }
.fc-list-day-text, .fc-list-day-side-text { font-size: 1rem; font-weight: 900; color: #fff; text-transform: uppercase; }
.fc-list-event td { border: none !important; }
.fc-list-event { 
    background-color: #1e293b !important; 
    border-radius: 12px; 
    margin-bottom: 8px; 
    display: block;
    position: relative;
    border: 1px solid #334155;
}
.fc-list-table { border-collapse: separate; border-spacing: 0 8px; }
.fc-list-event:hover td { background-color: transparent !important; }
.fc-list-event-graphic { display: none; }
.fc-list-event-time { color: #94a3b8; font-size: 0.75rem; padding: 12px 0 12px 16px !important; width: 20%; }
.fc-list-event-title { color: #fff; font-weight: 700; padding: 12px 16px !important; }

/* Workout Colors */
.fc-event.workout-easy_run, .fc-list-event.workout-easy_run { border-left: 4px solid #4CAF50 !important; }
.fc-event.workout-long_run, .fc-list-event.workout-long_run { border-left: 4px solid #2196F3 !important; }
.fc-event.workout-interval, .fc-list-event.workout-interval { border-left: 4px solid #F44336 !important; }
.fc-event.workout-tempo, .fc-list-event.workout-tempo { border-left: 4px solid #FFC107 !important; }
.fc-event.workout-strength, .fc-list-event.workout-strength { border-left: 4px solid #9C27B0 !important; }
.fc-event.workout-rest, .fc-list-event.workout-rest { border-left: 4px solid #9E9E9E !important; }
.fc-event.workout-race, .fc-list-event.workout-race { border-left: 4px solid #FFD700 !important; }
.fc-event.workout-threshold, .fc-event.workout-treshold, .fc-list-event.workout-threshold, .fc-list-event.workout-treshold { border-left: 4px solid #E91E63 !important; }
.fc-event.workout-recovery_run, .fc-list-event.workout-recovery_run { border-left: 4px solid #00BCD4 !important; }
.fc-event.workout-time_trial, .fc-list-event.workout-time_trial { border-left: 4px solid #FF5722 !important; }

@media (max-width: 640px) {
    .fc .fc-header-toolbar { margin-bottom: 1rem; flex-direction: column; gap: 0.5rem; }
    .fc .fc-toolbar-title { font-size: 0.9rem; }
    .fc .fc-button { padding: 0.25rem 0.5rem; font-size: 0.7rem; }
}
</style>
@if(request()->has('embed'))
<style>
    #ph-sidebar { display: none !important; }
    #main-content-wrapper { padding-left: 0 !important; }
    #pacerhub-nav { display: none !important; }
    #chatbox-toggle { display: none !important; }
    body { padding-top: 0 !important; }
    #coach-monitor-app { padding-top: 1rem !important; }
    /* Hide back link */
    a[href*="athletes"] { display: none !important; }
</style>
@endif
@endpush

@section('content')
<main id="coach-monitor-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" v-cloak>
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8">
            <div class="w-full md:w-auto">
                <a href="{{ route('coach.athletes.index') }}" class="text-slate-400 hover:text-white text-xs mb-3 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back to Athletes
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-black text-2xl shadow-xl italic">
                        {{ substr($enrollment->runner->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-3xl font-black text-white italic tracking-tighter leading-none">@{{ trainingProfile.name }}</h1>
                            <!-- Strava Connected indicator and sync button -->
                            <div v-if="trainingProfile.strava_connected" class="flex items-center gap-1.5 bg-[#FC4C02]/10 border border-[#FC4C02]/30 px-2.5 py-1 rounded-full text-[10px] font-black text-[#FC4C02] transition-all">
                                <svg role="img" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                <span>STRAVA CONNECTED</span>
                                <button type="button" @click="syncStrava" :disabled="loading" class="ml-1 px-2.5 py-0.5 rounded-lg bg-[#FC4C02] text-white hover:bg-[#e34402] transition font-bold disabled:opacity-50 text-[9px] flex items-center gap-1 shadow">
                                    <span v-if="loading">Syncing...</span>
                                    <span v-else>Sync Now</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-neon font-mono text-sm tracking-widest uppercase">{{ $enrollment->program->title }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold border
                                @if($enrollment->status === 'active') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                @elseif($enrollment->status === 'inactive') bg-rose-500/10 text-rose-400 border-rose-500/20
                                @elseif($enrollment->status === 'completed') bg-blue-500/10 text-blue-400 border-blue-500/20
                                @else bg-amber-500/10 text-amber-400 border-amber-500/20 @endif">
                                {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Program Bag' : $enrollment->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Runner Stats Summary - Mobile Optimized -->
            <div class="w-full overflow-x-auto no-scrollbar">
                <div class="flex gap-3 pb-2 min-w-max md:min-w-0">
                    <div class="bg-slate-800/40 backdrop-blur-md rounded-2xl p-4 border border-slate-700/50 text-center min-w-[100px]">
                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">VDOT</div>
                        <div class="text-2xl font-black text-neon italic">@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                    </div>
                    <div class="bg-slate-800/40 backdrop-blur-md rounded-2xl p-4 border border-slate-700/50 text-center min-w-[100px]">
                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">Target</div>
                        <div class="text-2xl font-black text-white italic">@{{ trainingProfile.weekly_km_target ? Number(trainingProfile.weekly_km_target).toFixed(0) : '-' }}<span class="text-xs font-normal text-slate-400 ml-1">km</span></div>
                    </div>
                    <div class="bg-slate-800/40 backdrop-blur-md rounded-2xl p-4 border border-slate-700/50 text-center min-w-[100px]">
                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">Age</div>
                        <div class="text-2xl font-black text-white italic">{{ $enrollment->runner->date_of_birth ? \Carbon\Carbon::parse($enrollment->runner->date_of_birth)->age : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar Column -->
            <div class="lg:col-span-2">
                <!-- Training Profile Panel -->
                <div class="glass-panel rounded-2xl p-4 md:p-6 mb-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-white font-black text-xl italic tracking-tight">Athlete Profile</h3>
                                <p class="text-[10px] font-mono text-slate-500 uppercase tracking-widest">Based on VDOT Analytics</p>
                            </div>
                            <button @click="showWeeklyTargetModal = true" class="p-2 rounded-xl bg-slate-800 border border-slate-700 text-neon hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </div>

                        <!-- VDOT Score -->
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center mb-6">
                            <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">VDOT Score</div>
                            <div class="text-4xl font-black text-white">@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                            <div class="text-[10px] text-slate-500 mt-1">VO2Max Approx: @{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                        </div>
                        
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center mb-6 relative group">
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                                <button @click="showWeeklyTargetModal = true" class="text-xs text-neon hover:text-white bg-slate-700/50 p-1 rounded">Edit</button>
                            </div>
                            <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Weekly Target (km)</div>
                            <div class="text-4xl font-black text-white cursor-pointer" @click="showWeeklyTargetModal = true">@{{ trainingProfile.weekly_km_target ? Number(trainingProfile.weekly_km_target).toFixed(1) : '-' }}</div>
                            <div class="text-[10px] text-slate-500 mt-1">Target mingguan atlet</div>
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
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'analytics' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'analytics'">
                                Analytics
                            </button>
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'predictions' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'predictions'">
                                Race Predictor
                            </button>
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2 whitespace-nowrap"
                                :class="profileTab === 'weekly_report' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'weekly_report'">
                                Weekly Report
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
                                            <td class="py-2 text-green-400 font-bold">Easy (E)</td>
                                            <td class="py-2 text-right">@{{ formatPace(trainingProfile.paces?.E) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-blue-400 font-bold">Marathon (M)</td>
                                            <td class="py-2 text-right">@{{ formatPace(trainingProfile.paces?.M) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-yellow-400 font-bold">Threshold (T)</td>
                                            <td class="py-2 text-right">@{{ formatPace(trainingProfile.paces?.T) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-orange-400 font-bold">Interval (I)</td>
                                            <td class="py-2 text-right">@{{ formatPace(trainingProfile.paces?.I) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-red-400 font-bold">Repetition (R)</td>
                                            <td class="py-2 text-right">@{{ formatPace(trainingProfile.paces?.R) }}</td>
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
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['5k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['5k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">10K</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['10k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['10k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">Half Marathon</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['21k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['21k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-bold">Marathon</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['42k']?.time || '-' }}</td>
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

                        <!-- Analytics Tab -->
                        <div v-if="profileTab === 'analytics'" class="space-y-6">
                            <!-- Fatigue & Health Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-slate-800/60 rounded-2xl p-4 border border-slate-700/50 shadow-lg">
                                    <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2 font-mono">Fatigue & Recovery</div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold shadow-md"
                                             :class="healthSummary.fatigueLevel === 'High Fatigue' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : (healthSummary.fatigueLevel === 'Moderate Fatigue' ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30')">
                                            @{{ healthSummary.fatigueEmoji }}
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-white italic leading-tight">@{{ healthSummary.fatigueLevel }}</div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">Avg RPE: <span class="text-white font-bold">@{{ healthSummary.avgRpe }}</span> (Last 5 runs)</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs text-slate-300 bg-slate-900/40 p-2.5 rounded-xl border border-slate-800/80">
                                        @{{ healthSummary.advice }}
                                    </div>
                                </div>

                                <div class="bg-slate-800/60 rounded-2xl p-4 border border-slate-700/50 shadow-lg">
                                    <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2 font-mono">Injury & Burnout Risk</div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold shadow-md"
                                             :class="healthSummary.riskLevel === 'HIGH RISK' ? 'bg-red-600/30 text-red-500 border border-red-500/50 animate-pulse' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'">
                                            ⚠
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-white italic leading-tight" :class="healthSummary.riskLevel === 'HIGH RISK' ? 'text-red-400' : 'text-emerald-400'">@{{ healthSummary.riskLevel }}</div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">Subjective Feeling: <span class="text-white font-bold capitalize">@{{ healthSummary.feelingStatus }}</span></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs text-slate-300 bg-slate-900/40 p-2.5 rounded-xl border border-slate-800/80">
                                        @{{ healthSummary.riskMessage }}
                                    </div>
                                </div>
                            </div>

                            <!-- Weekly Mileage Chart -->
                            <div class="bg-slate-800/40 rounded-2xl p-4 border border-slate-700/50 shadow-inner">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase font-mono tracking-wider">Weekly Mileage (Target vs Actual)</div>
                                    <div class="flex gap-4 text-[9px] font-mono">
                                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-slate-600 rounded"></span> Target</span>
                                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-neon rounded"></span> Completed</span>
                                    </div>
                                </div>
                                <div class="h-44 relative">
                                    <canvas id="weeklyVolumeChart"></canvas>
                                </div>
                            </div>

                            <!-- Pace Compliance Analytics -->
                            <div class="bg-slate-800/40 rounded-2xl p-4 border border-slate-700/50">
                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-3 font-mono tracking-wider">Pace Compliance & Accuracy</div>
                                <div v-if="paceComplianceList.length === 0" class="text-center py-6 text-xs text-slate-500 italic">
                                    No completed workouts found in calendar events to analyze.
                                </div>
                                <div v-else class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                                    <div v-for="item in paceComplianceList" :key="item.date" class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 flex flex-col md:flex-row justify-between md:items-center gap-2">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-white font-mono">@{{ item.dateFormatted }}</span>
                                                <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase" :class="item.typeClass">@{{ item.typeName }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-1">
                                                Target: <span class="text-white font-mono">@{{ item.targetPace }}</span> • Actual: <span class="text-neon font-mono font-bold">@{{ item.actualPace }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-left md:text-right">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider" :class="item.complianceClass">
                                                    @{{ item.complianceStatus }}
                                                </span>
                                                <div class="text-[9px] text-slate-500 mt-1 font-mono">@{{ item.diffText }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Predictions Tab -->
                        <div v-if="profileTab === 'predictions'" class="space-y-6">
                            <div class="bg-slate-800/60 rounded-2xl p-4 border border-slate-700/50">
                                <h4 class="text-sm font-black text-white italic tracking-tight mb-1 flex items-center gap-1.5">
                                    <span>🏆</span> Race Finish Time Predictor
                                </h4>
                                <p class="text-[10px] text-slate-400 leading-normal mb-4">
                                    Predict finish times for custom distances using standard scaling formulas (Riegel's formula) based on current VDOT.
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Target Distance (KM)</label>
                                        <div class="flex gap-2">
                                            <input type="number" step="0.1" v-model.number="predictor.distance" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon outline-none" placeholder="e.g. 15">
                                            <button type="button" @click="predictor.distance = 15" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-[10px] font-black text-slate-300 rounded-xl">15K</button>
                                            <button type="button" @click="predictor.distance = 30" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-[10px] font-black text-slate-300 rounded-xl">30K</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Reference VDOT Score</label>
                                        <input type="number" step="0.1" v-model.number="predictor.vdot" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon outline-none">
                                    </div>
                                </div>

                                <div class="mt-5 p-4 bg-slate-950/40 rounded-xl border border-slate-850 text-center">
                                    <div class="text-[9px] text-slate-500 uppercase tracking-widest font-mono">Predicted Finish Time</div>
                                    <div class="text-3xl font-black text-neon italic mt-1 font-mono">@{{ predictedTime.time }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Target Pace: <span class="text-white font-bold font-mono">@{{ predictedTime.pace }} /km</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Report Tab -->
                        <div v-if="profileTab === 'weekly_report'" class="space-y-6">
                            <!-- Nudge Strava Banner inside tab if not connected -->
                            <div v-if="!trainingProfile.strava_connected" class="p-4 rounded-2xl bg-amber-950/20 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-lg">
                                <div class="flex items-center gap-2.5">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                    <div class="text-left">
                                        <div class="text-xs font-bold text-white">Strava Belum Terhubung</div>
                                        <div class="text-[10px] text-slate-400">Atlet ini belum menyinkronkan Strava. Anda dapat meminta mereka menyinkronkan token di dashboard mereka.</div>
                                    </div>
                                </div>
                                <button type="button" @click="nudgeStrava" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition whitespace-nowrap">
                                    Nudge in App
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Report Form (Col-span-2) -->
                                <div class="md:col-span-2 space-y-4">
                                    <div class="bg-slate-800/40 p-5 rounded-2xl border border-slate-700/50">
                                        <h4 class="text-sm font-black text-white tracking-tight mb-4">
                                            Tulis Laporan Mingguan
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Minggu Ke</label>
                                                <input type="number" min="1" max="52" v-model.number="weeklyReportForm.week_number" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon outline-none font-bold">
                                            </div>
                                            <div class="flex items-end">
                                                <button type="button" @click="generateWeeklyReport" :disabled="weeklyReportLoading" class="w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                                                    <span v-if="!weeklyReportLoading">Hasilkan AI Draft</span>
                                                    <span v-else class="flex items-center gap-1">
                                                        <svg class="animate-spin h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Menganalisis...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="space-y-1">
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Konten Laporan</label>
                                            <textarea rows="10" v-model="weeklyReportForm.report_text" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white text-xs focus:ring-1 focus:ring-neon outline-none leading-relaxed font-sans" placeholder="Tulis analisis mingguan di sini atau klik 'Hasilkan AI Draft' untuk mendraft secara otomatis..."></textarea>
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <button type="button" @click="publishWeeklyReport" :disabled="weeklyReportPublishing" class="px-5 py-2.5 bg-neon text-dark font-black text-xs rounded-xl hover:bg-neon/90 transition shadow-lg shadow-neon/15 flex items-center gap-1.5">
                                                <span v-if="!weeklyReportPublishing">Terbitkan Rapor Mingguan</span>
                                                <span v-else class="w-3.5 h-3.5 border-2 border-dark border-t-transparent rounded-full animate-spin"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- History List (Col-span-1) -->
                                <div class="md:col-span-1 space-y-4">
                                    <div class="bg-slate-800/40 p-5 rounded-2xl border border-slate-700/50">
                                        <h4 class="text-sm font-black text-white tracking-tight mb-4">
                                            Riwayat Rapor Mingguan
                                        </h4>
                                        <div v-if="weeklyReportsList.length === 0" class="text-center py-6 text-xs text-slate-500">
                                            Belum ada laporan mingguan yang diterbitkan.
                                        </div>
                                        <div v-else class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
                                            <div v-for="rep in weeklyReportsList" :key="rep.id" @click="selectWeeklyReport(rep)" class="p-3 bg-slate-900/50 hover:bg-slate-900 border border-slate-800 rounded-xl cursor-pointer transition">
                                                <div class="flex justify-between items-center mb-1">
                                                     <span class="text-xs font-black text-white">Minggu ke-@{{ rep.week_number }}</span>
                                                     <span class="text-[9px] text-slate-500 font-mono">@{{ formatDateShort(rep.created_at) }}</span>
                                                </div>
                                                <p class="text-[10px] text-slate-400 line-clamp-2 leading-relaxed">@{{ rep.report_text }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="glass-panel rounded-[2.5rem] p-4 md:p-8" id="coach-calendar-section">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                        <div>
                            <h3 class="text-white font-black text-2xl italic tracking-tight">Training Calendar</h3>
                            <p class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mt-1">Review & Plan workouts</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                            <!-- Export Buttons -->
                            <button @click="exportCalendar('image')" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export Image
                            </button>
                            <button @click="exportCalendar('pdf')" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Export PDF
                            </button>
                            <button @click="openRaceForm" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow">
                                Add Race Event
                            </button>
                        </div>
                    </div>
                    <div id="calendar" class="fc-modern"></div>
                </div>
            </div>

            <!-- Detail & Feedback Column - Mobile Sheet Style -->
            <div class="lg:col-span-1">
                <div v-if="selectedSession" class="lg:sticky lg:top-24 space-y-6">
                    <div class="glass-panel rounded-[2.5rem] p-6 border-neon/20 shadow-xl shadow-neon/5">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">@{{ formatDate(selectedSession.start) }}</div>
                                <h3 class="text-2xl font-black text-white italic tracking-tight truncate">@{{ selectedSession.title }}</h3>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm" 
                                :class="statusClass(selectedSession.extendedProps.status)">
                                @{{ selectedSession.extendedProps.status }}
                            </span>
                        </div>

                        <!-- Edit Button -->
                        <div class="mb-2" v-if="!selectedSession.extendedProps.is_strava">
                            <button @click="openForm(null, selectedSession)" class="w-full text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition border border-slate-700">
                                <i class="fa-solid fa-pen"></i> @{{ selectedSession.extendedProps.is_custom ? 'Edit Custom Workout' : 'Customize / Edit Workout' }}
                            </button>
                        </div>

                        <!-- Reschedule Program Button -->
                        <div class="mb-2">
                            <button @click="openRescheduleModal()" class="w-full text-xs bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 hover:text-blue-300 px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition border border-blue-500/30">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Reschedule Program
                            </button>
                        </div>

                        <!-- Send Reminder Button -->
                        <div class="mb-4" v-if="!selectedSession.extendedProps.is_strava && selectedSession.extendedProps.status !== 'completed'">
                            <button @click="openReminderModal()" class="w-full text-xs bg-neon/10 hover:bg-neon/20 text-neon hover:text-neon/80 px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition border border-neon/30">
                                <i class="fa-solid fa-paper-plane"></i>
                                Send Program Reminder
                            </button>
                        </div>

                        <!-- Session Detail -->
                        <div class="space-y-3 mb-6 text-sm text-slate-300">
                            <div v-if="selectedSession.extendedProps.distance">
                                <span class="text-slate-500">Target Distance:</span> @{{ selectedSession.extendedProps.distance }} km
                                <div v-if="getPaceInfo(selectedSession.extendedProps.type, selectedSession.extendedProps.distance)" class="mt-2 p-2 bg-slate-800/80 border border-slate-700 rounded text-neon font-mono text-xs">
                                    @{{ getPaceInfo(selectedSession.extendedProps.type, selectedSession.extendedProps.distance) }}
                                </div>
                            </div>
                            <div v-if="selectedSession.extendedProps.description">
                                <span class="text-slate-500 text-xs uppercase font-bold">Description / Notes:</span>
                                <div class="mt-1 p-3 bg-slate-800 border-l-4 border-neon rounded-r-xl text-white font-bold text-sm shadow-lg">
                                    @{{ selectedSession.extendedProps.description }}
                                </div>
                            </div>
                            <div v-if="selectedSession.extendedProps.notes">
                                <span class="text-slate-500 text-xs uppercase font-bold mt-3 block">Additional Notes:</span>
                                <div class="mt-1 p-3 bg-yellow-500/10 border-l-4 border-yellow-500 rounded-r-xl text-white font-bold text-sm shadow-lg">
                                    @{{ selectedSession.extendedProps.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Athlete Report -->
                        <div v-if="selectedSession.extendedProps.tracking" class="mb-6 border-t border-slate-700 pt-4">
                            <h4 class="text-neon font-bold text-xs uppercase mb-3">Athlete Report</h4>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-slate-800 p-2 rounded">
                                        <div class="text-[10px] text-slate-500">RPE (1-10)</div>
                                        <div class="font-bold text-white">@{{ selectedSession.extendedProps.tracking.rpe || '-' }}</div>
                                    </div>
                                    <div class="bg-slate-800 p-2 rounded">
                                        <div class="text-[10px] text-slate-500">Feeling</div>
                                        <div class="font-bold text-white capitalize">@{{ selectedSession.extendedProps.tracking.feeling || '-' }}</div>
                                    </div>
                                </div>
                                
                                <div v-if="selectedSession.extendedProps.tracking.notes">
                                    <div class="text-[10px] text-slate-500 mb-1">Athlete Notes</div>
                                    <p class="text-xs text-white bg-slate-800 p-2 rounded italic">"@{{ selectedSession.extendedProps.tracking.notes }}"</p>
                                </div>
                                
                                <div v-if="selectedSession.extendedProps.tracking.strava_link">
                                    <a :href="selectedSession.extendedProps.tracking.strava_link" target="_blank" class="block w-full text-center py-2 rounded bg-[#FC4C02]/20 text-[#FC4C02] text-xs font-bold hover:bg-[#FC4C02]/30 transition border border-[#FC4C02]/30">
                                        View on Strava
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!selectedSession.extendedProps.is_strava" class="mb-6 border-t border-slate-700 pt-4 text-center text-slate-500 text-xs italic">
                            Athlete has not completed this session yet.
                        </div>

                        <div v-if="stravaDetailsLoading" class="mb-6 border-t border-slate-700 pt-4">
                            <div class="text-xs text-slate-400">Fetching Strava details…</div>
                        </div>
                        <div v-else-if="stravaDetailsError" class="mb-6 border-t border-slate-700 pt-4">
                            <div class="text-xs text-red-300">@{{ stravaDetailsError }}</div>
                        </div>
                        <div v-else-if="stravaMetrics" class="mb-6 border-t border-slate-700 pt-4">
                            <h4 class="text-[#FC4C02] font-black text-xs uppercase mb-3">Strava Details</h4>

                            <!-- Workout Classification Summary -->
                            <div v-if="stravaWorkoutClassification" class="mb-3 p-3.5 rounded-xl border flex flex-col gap-1.5" :class="stravaWorkoutClassification.colorClass">
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-70">Kesimpulan Sesi (@{{ stravaWorkoutClassification.source }})</span>
                                    <span class="text-[9px] font-bold bg-white/10 px-2 py-0.5 rounded uppercase">Classified</span>
                                </div>
                                <div class="text-sm font-black italic uppercase tracking-tight">
                                    @{{ stravaWorkoutClassification.type }}
                                </div>
                                <div v-if="stravaWorkoutClassification.evidence.length" class="mt-1 space-y-0.5 border-t border-white/10 pt-1.5">
                                    <div v-for="(ev, idx) in stravaWorkoutClassification.evidence" :key="idx" class="text-[10px] opacity-85 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span>@{{ ev }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Distance</div>
                                    <div class="font-black text-white">@{{ stravaMetrics.distance_m ? (stravaMetrics.distance_m / 1000).toFixed(2) : '-' }} km</div>
                                </div>
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Avg Pace</div>
                                    <div class="font-black text-neon">@{{ stravaMetrics.pace ? (stravaMetrics.pace + ' /km') : '-' }}</div>
                                </div>
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Heart Rate</div>
                                    <div class="font-black text-white">@{{ stravaMetrics.average_heartrate ? Math.round(stravaMetrics.average_heartrate) : '-' }} <span class="text-slate-500 text-[10px]">avg</span></div>
                                    <div class="text-[10px] text-slate-500">max @{{ stravaMetrics.max_heartrate ? Math.round(stravaMetrics.max_heartrate) : '-' }}</div>
                                </div>
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Cadence</div>
                                    <div class="font-black text-white">@{{ stravaMetrics.average_cadence ? Math.round(stravaMetrics.average_cadence) : '-' }} <span class="text-slate-500 text-[10px]">spm</span></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Total Time</div>
                                    <div class="font-black text-white">@{{ stravaMetrics.total_time_s ? formatSeconds(stravaMetrics.total_time_s) : '-' }}</div>
                                </div>
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Moving</div>
                                    <div class="font-black text-white">@{{ stravaMetrics.moving_time_s ? formatSeconds(stravaMetrics.moving_time_s) : '-' }}</div>
                                </div>
                                <div class="bg-slate-800 p-2 rounded">
                                    <div class="text-[10px] text-slate-500">Paused</div>
                                    <div class="font-black text-white">@{{ stravaMetrics.pause_time_s ? formatSeconds(stravaMetrics.pause_time_s) : '-' }}</div>
                                </div>
                            </div>

                            <div v-if="stravaPaceZones || stravaHrZones || stravaZoneAnalysis" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                <div v-if="stravaPaceZones" class="bg-slate-800/40 border border-slate-700 rounded-xl p-3">
                                    <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Pace Distribution</div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Easy</div>
                                            <div class="text-white font-black text-sm">@{{ stravaPaceZones.summary.easy }}%</div>
                                        </div>
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Tempo</div>
                                            <div class="text-white font-black text-sm">@{{ stravaPaceZones.summary.tempo }}%</div>
                                        </div>
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Speed</div>
                                            <div class="text-white font-black text-sm">@{{ stravaPaceZones.summary.speed }}%</div>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-2">
                                        E @{{ stravaPaceZones.zones.E }}% • M @{{ stravaPaceZones.zones.M }}% • T @{{ stravaPaceZones.zones.T }}% • I @{{ stravaPaceZones.zones.I }}% • R @{{ stravaPaceZones.zones.R }}%
                                    </div>
                                </div>

                                <div v-if="stravaHrZones" class="bg-slate-800/40 border border-slate-700 rounded-xl p-3">
                                    <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Heart Rate Distribution</div>
                                    <div class="grid grid-cols-5 gap-2">
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Z1</div>
                                            <div class="text-white font-black text-xs">@{{ stravaHrZones.Z1 }}%</div>
                                        </div>
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Z2</div>
                                            <div class="text-white font-black text-xs">@{{ stravaHrZones.Z2 }}%</div>
                                        </div>
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Z3</div>
                                            <div class="text-white font-black text-xs">@{{ stravaHrZones.Z3 }}%</div>
                                        </div>
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Z4</div>
                                            <div class="text-white font-black text-xs">@{{ stravaHrZones.Z4 }}%</div>
                                        </div>
                                        <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-2 text-center">
                                            <div class="text-[10px] text-slate-400">Z5</div>
                                            <div class="text-white font-black text-xs">@{{ stravaHrZones.Z5 }}%</div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="stravaZoneAnalysis" class="bg-slate-800/40 border border-slate-700 rounded-xl p-3 space-y-2">
                                    <div class="text-[11px] font-bold text-slate-400 uppercase">Analisis Zona & Efek</div>
                                    <div class="text-slate-300 text-sm">@{{ stravaZoneAnalysis }}</div>
                                    <div v-if="stravaZoneEffect" class="text-[11px] text-neon font-bold uppercase">Efek Latihan</div>
                                    <div v-if="stravaZoneEffect" class="text-white text-sm font-medium">@{{ stravaZoneEffect }}</div>
                                    <div v-if="stravaZoneSuggestion" class="pt-2 border-t border-slate-700/50">
                                        <div class="text-[11px] text-yellow-500 font-bold uppercase">Saran</div>
                                        <div class="text-white text-sm font-medium">@{{ stravaZoneSuggestion }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Workout Analysis (Aligned with Runner View) -->
                            <div v-if="stravaMetrics" class="mb-3 bg-slate-800/40 border border-slate-700 rounded-xl p-4">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-[4px] bg-purple-400 flex items-center justify-center text-dark font-bold text-[10px]">AI</div>
                                        <span class="text-xs font-bold text-purple-300">AI Workout Analysis</span>
                                    </div>
                                    <button class="px-2.5 py-1 rounded-[4px] bg-purple-500/20 text-purple-200 border border-purple-500/30 text-[10px] font-bold hover:bg-purple-500/30 transition disabled:opacity-50"
                                            :disabled="stravaAiAnalysisLoading"
                                            @click="loadStravaForActivity(stravaMetrics.strava_activity_id, true)">
                                        @{{ stravaAiAnalysisLoading ? 'Analyzing...' : 'Refresh AI' }}
                                    </button>
                                </div>

                                <div v-if="stravaAiAnalysisLoading" class="text-xs text-slate-400">AI sedang menganalisis workout dan konteks latihan atlet...</div>
                                <div v-else-if="!stravaAiAnalysis" class="text-xs text-slate-550 italic">Analisis AI belum tersedia. Klik Refresh AI untuk memicu analisis baru.</div>
                                <div v-else class="space-y-3 text-xs">
                                    <div v-if="stravaAiAnalysis.summary" class="text-slate-200 leading-relaxed text-sm">@{{ stravaAiAnalysis.summary }}</div>
                                    <div class="text-xs text-slate-350">
                                        <span class="text-slate-400 font-mono text-[10px] uppercase">Junk Miles Risk:</span>
                                        <span class="font-bold text-white capitalize">@{{ stravaAiAnalysis.junk_miles_risk?.level || 'unknown' }}</span>
                                    </div>

                                    <div v-if="stravaAiAnalysis.what_went_well?.length">
                                        <div class="text-[10px] font-bold text-green-300 uppercase mb-1">Yang Sudah Bagus</div>
                                        <ul class="space-y-0.5 text-slate-300">
                                            <li v-for="(item, idx) in stravaAiAnalysis.what_went_well" :key="'well-' + idx">• @{{ item }}</li>
                                        </ul>
                                    </div>

                                    <div v-if="stravaAiAnalysis.what_to_improve?.length">
                                        <div class="text-[10px] font-bold text-amber-300 uppercase mb-1">Yang Perlu Ditingkatkan</div>
                                        <ul class="space-y-0.5 text-slate-300">
                                            <li v-for="(item, idx) in stravaAiAnalysis.what_to_improve" :key="'improve-' + idx">• @{{ item }}</li>
                                        </ul>
                                    </div>

                                    <div v-if="stravaAiAnalysis.next_workout_suggestion?.type || stravaAiAnalysis.next_workout_suggestion?.reason" class="rounded-[6px] bg-slate-900/80 border border-slate-700 p-2.5">
                                        <div class="text-[10px] font-bold text-neon uppercase mb-1">Saran Workout Berikutnya</div>
                                        <div class="text-white font-bold text-xs">@{{ stravaAiAnalysis.next_workout_suggestion.type || '-' }}</div>
                                        <div v-if="stravaAiAnalysis.next_workout_suggestion.duration" class="text-[10px] text-slate-400 mt-0.5">Durasi: @{{ stravaAiAnalysis.next_workout_suggestion.duration }}</div>
                                        <div v-if="stravaAiAnalysis.next_workout_suggestion.target" class="text-[10px] text-slate-400">Target: @{{ stravaAiAnalysis.next_workout_suggestion.target }}</div>
                                        <div v-if="stravaAiAnalysis.next_workout_suggestion.reason" class="text-xs text-slate-300 mt-1 leading-relaxed">@{{ stravaAiAnalysis.next_workout_suggestion.reason }}</div>
                                    </div>

                                    <div v-if="stravaAiAnalysis.recovery_advice?.length">
                                        <div class="text-[10px] font-bold text-sky-300 uppercase mb-1">Recovery Advice</div>
                                        <ul class="space-y-0.5 text-slate-300">
                                            <li v-for="(item, idx) in stravaAiAnalysis.recovery_advice" :key="'recovery-' + idx">• @{{ item }}</li>
                                        </ul>
                                    </div>

                                    <div v-if="stravaAiAnalysis.improve_next_time?.length">
                                        <div class="text-[10px] font-bold text-purple-300 uppercase mb-1">Improve Next Time</div>
                                        <ul class="space-y-0.5 text-slate-300">
                                            <li v-for="(item, idx) in stravaAiAnalysis.improve_next_time" :key="'next-' + idx">• @{{ item }}</li>
                                        </ul>
                                    </div>

                                    <div v-if="stravaAiAnalysis.risk_flags?.length" class="rounded-[6px] bg-red-500/10 border border-red-500/20 p-2.5">
                                        <div class="text-[10px] font-bold text-red-300 uppercase mb-1">Risk Flags</div>
                                        <ul class="space-y-0.5 text-red-100">
                                            <li v-for="(item, idx) in stravaAiAnalysis.risk_flags" :key="'risk-' + idx">• @{{ item }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div v-if="stravaMetrics.media && stravaMetrics.media.length" class="mb-3 border-t border-slate-700 pt-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Media</div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    <a v-for="(m, idx) in stravaMetrics.media" :key="idx" :href="m" target="_blank" class="block">
                                        <img :src="m" class="w-full h-24 object-cover rounded-lg border border-slate-700 bg-slate-900" loading="lazy">
                                    </a>
                                </div>
                            </div>

                            <div v-if="stravaStreams && stravaStreams.time && stravaStreams.time.length > 0" class="bg-slate-900/30 border border-slate-700 rounded-xl p-2 mb-3 h-44 relative group">
                                <button @click="showStravaGraphModal = true" class="absolute top-2 right-2 p-1.5 bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg opacity-0 group-hover:opacity-100 transition z-10" title="Expand Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                                <canvas id="coachStravaMetricsChart" class="w-full h-full"></canvas>
                            </div>

                            <div v-if="stravaSplits.length > 0" class="mb-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Splits (per km)</div>
                                <div class="max-h-40 overflow-y-auto space-y-1">
                                    <div v-for="s in stravaSplits" :key="s.split" class="flex justify-between items-center text-xs p-2 rounded-xl bg-slate-800 border border-slate-700">
                                        <div class="text-slate-300 font-bold">KM @{{ s.split || '-' }}</div>
                                        <div class="text-right">
                                            <div class="text-white font-mono">@{{ s.pace || '-' }}</div>
                                            <div class="text-[10px] text-slate-500">@{{ formatSeconds(s.moving_time_s) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="stravaLaps.length > 0" class="mb-1">
                                <div class="text-[11px] font-bold text-slate-400 uppercase mb-2">Laps</div>
                                <div class="max-h-40 overflow-y-auto space-y-1">
                                    <div v-for="(l, idx) in stravaLaps" :key="idx" class="flex justify-between items-center text-xs p-2 rounded-xl bg-slate-800 border border-slate-700">
                                        <div class="min-w-0">
                                            <div class="text-slate-300 font-bold truncate">@{{ l.name || ('Lap ' + (idx + 1)) }}</div>
                                            <div class="text-[10px] text-slate-500">@{{ l.distance_m ? (Math.round(l.distance_m) + ' m') : '-' }} • @{{ formatSeconds(l.moving_time_s) }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-white font-mono">@{{ l.pace || '-' }}</div>
                                            <div class="text-[10px] text-slate-500">@{{ l.average_heartrate ? (Math.round(l.average_heartrate) + ' bpm') : '' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coach Feedback Form -->
                        <div v-if="selectedSession.extendedProps.tracking" class="border-t border-slate-700 pt-4">
                            <h4 class="text-neon font-bold text-xs uppercase mb-3">Coach Feedback</h4>
                            <form @submit.prevent="saveFeedback">
                                <div class="mb-3">
                                    <label class="block text-xs text-slate-400 mb-1">Rating</label>
                                    <div class="flex gap-2">
                                        <button type="button" v-for="i in 5" :key="i" 
                                            @click="feedbackForm.coach_rating = i"
                                            class="text-lg transition hover:scale-110"
                                            :class="i <= feedbackForm.coach_rating ? 'text-yellow-400' : 'text-slate-600'">
                                            ★
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-xs text-slate-400 mb-1">Feedback</label>
                                    <textarea v-model="feedbackForm.coach_feedback" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-white text-sm" placeholder="Great job! Keep it up..."></textarea>
                                </div>
                                <button type="submit" :disabled="loading" class="w-full py-2 rounded-lg bg-neon text-dark font-black text-sm hover:bg-neon/90 transition disabled:opacity-50">
                                    @{{ loading ? 'Saving...' : 'Save Feedback' }}
                                </button>
                            </form>
                        </div>

                    </div>
                    <div v-else class="text-center py-12 text-slate-500">
                        <p>Select a session from the calendar to view details and give feedback.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workout Modal -->
        <div v-if="showFormModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showFormModal = false"></div>
            <div class="relative z-10 max-w-lg mx-auto my-10 bg-slate-900 border border-slate-700 rounded-2xl p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-white font-bold text-xl">Add Workout</h3>
                    <button class="text-slate-400 hover:text-white" @click="showFormModal = false">×</button>
                </div>
                <form @submit.prevent="saveCustomWorkout" class="space-y-3">
                    <input type="hidden" v-model="form.workout_id">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Date</label>
                        <input type="date" v-model="form.workout_date" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-2 focus:ring-neon outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Type</label>
                        <select v-model="form.type" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-2 focus:ring-neon outline-none">
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
                        <select v-model="form.difficulty" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-2 focus:ring-neon outline-none">
                            <option value="easy">Easy</option>
                            <option value="moderate">Moderate</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Distance (km)</label>
                            <input type="number" step="0.01" v-model="form.distance" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-2 focus:ring-neon outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Duration</label>
                            <input type="text" v-model="form.duration" placeholder="00:30:00" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-2 focus:ring-neon outline-none">
                        </div>
                    </div>
                    
                    <!-- Advanced Workout Builder -->
                    <div class="border-t border-slate-700 pt-4 mt-4">
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Workout Configuration</label>
                        
                        <div v-if="form.workout_structure && form.workout_structure.advanced" class="bg-slate-800 p-3 rounded-xl border border-slate-700 mb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-xs text-neon font-bold uppercase mb-1">Advanced Config</div>
                                    <div class="text-sm text-white">@{{ form.description }}</div>
                                    <div class="text-xs text-slate-400 mt-1">Total: @{{ form.distance }} km</div>
                                </div>
                                <button type="button" @click="openBuilder(true)" class="text-xs text-slate-400 hover:text-white">Edit</button>
                            </div>
                        </div>

                        <button type="button" @click="openBuilder(!!(form.workout_structure && form.workout_structure.advanced))" class="w-full py-3 rounded-xl border border-dashed border-slate-600 text-slate-400 hover:text-neon hover:border-neon hover:bg-slate-800 transition text-sm font-bold flex items-center justify-center gap-2">
                            <i class="fa-solid fa-layer-group"></i> @{{ form.workout_structure && form.workout_structure.advanced ? 'Open Builder to Edit' : 'Open Advanced Builder' }}
                        </button>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-2 focus:ring-neon outline-none" placeholder="Workout details..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-sm hover:bg-slate-700" @click="showFormModal = false">Cancel</button>
                        <button type="submit" :disabled="loading" class="px-6 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition text-sm disabled:opacity-50">
                            @{{ loading ? 'Saving...' : 'Save Workout' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reschedule Program Modal -->
        <div v-if="showRescheduleModal" class="fixed inset-0 z-[200] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showRescheduleModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-10 mx-4 bg-slate-900 border border-slate-700 rounded-2xl p-6 shadow-2xl">

                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-base font-bold text-white uppercase tracking-tight">Reschedule Program</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Jadwalkan ulang seluruh program latihan</p>
                    </div>
                    <button @click="showRescheduleModal = false" class="text-slate-500 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Program Info Card -->
                <div class="bg-blue-900/20 border border-blue-700/40 rounded-xl p-4 mb-5 space-y-1.5">
                    <div class="text-xs text-blue-300">Program: <span class="font-bold text-white">{{ $enrollment->program->title }}</span></div>
                    <div class="text-xs text-blue-300">
                        Tanggal Aktif:
                        <span class="font-bold text-white">
                            {{ $enrollment->start_date ? \Carbon\Carbon::parse($enrollment->start_date)->format('d M Y') : '-' }}
                            →
                            {{ $enrollment->end_date ? \Carbon\Carbon::parse($enrollment->end_date)->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div class="text-xs text-blue-300">Durasi: <span class="font-bold text-white">{{ $enrollment->program->duration_weeks ?? 12 }} minggu</span></div>
                </div>

                <p class="text-slate-400 text-xs mb-4">
                    Semua sesi latihan akan digeser sesuai tanggal mulai baru. Reschedule per-sesi yang ada sebelumnya akan dihapus otomatis.
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Tanggal Mulai Baru</label>
                        <input type="date" v-model="rescheduleForm.new_start_date"
                            class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none">
                    </div>

                    <!-- Preview end date -->
                    <div v-if="rescheduleForm.new_start_date" class="bg-slate-800/60 border border-slate-700 rounded-xl p-3 text-xs flex justify-between items-center">
                        <span class="text-slate-400">Estimasi Selesai:</span>
                        <span class="text-white font-bold">@{{ previewRescheduleEndDate }}</span>
                    </div>

                    <div v-if="rescheduleError" class="text-red-400 text-xs bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                        @{{ rescheduleError }}
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showRescheduleModal = false"
                        class="flex-1 py-2.5 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 transition border border-slate-700">
                        Batal
                    </button>
                    <button type="button" @click="submitReschedule" :disabled="rescheduleLoading"
                        class="flex-1 py-2.5 text-sm font-black text-white bg-blue-600 rounded-xl hover:bg-blue-500 transition disabled:opacity-50">
                        @{{ rescheduleLoading ? 'Menyimpan...' : 'Shift Calendar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Send Program Reminder Modal -->
        <div v-if="showReminderModal" class="fixed inset-0 z-[200] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showReminderModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-10 mx-4 bg-slate-900 border border-slate-700 rounded-2xl p-6 shadow-2xl">

                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-base font-bold text-white uppercase tracking-tight">Kirim Pengingat Program</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Kirim pengingat sesi latihan ke atlet</p>
                    </div>
                    <button @click="showReminderModal = false" class="text-slate-500 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Session Info Card -->
                <div class="bg-neon/10 border border-neon/20 rounded-xl p-4 mb-5 space-y-1.5" v-if="selectedSession">
                    <div class="text-xs text-neon">Sesi: <span class="font-bold text-white">@{{ selectedSession.title }}</span></div>
                    <div class="text-xs text-neon" v-if="selectedSession.extendedProps.distance">Jarak: <span class="font-bold text-white">@{{ selectedSession.extendedProps.distance }} km</span></div>
                    <div class="text-xs text-neon" v-if="selectedSession.extendedProps.description">Deskripsi: <span class="font-bold text-white">@{{ selectedSession.extendedProps.description }}</span></div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Saluran Pengiriman (Channel)</label>
                        <select v-model="reminderForm.channel" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-1 focus:ring-neon focus:border-neon outline-none">
                            <option value="both">WhatsApp & Email</option>
                            <option value="wa">WhatsApp Saja</option>
                            <option value="email">Email Saja</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Pesan Kustom (Opsional)</label>
                        <textarea v-model="reminderForm.custom_message" rows="4" placeholder="Tulis pesan kustom di sini... (Kosongkan untuk menggunakan pesan otomatis AI)"
                            class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-1 focus:ring-neon focus:border-neon outline-none resize-none"></textarea>
                        <p class="text-[10px] text-slate-500 mt-1">Jika dikosongkan, sistem akan otomatis membuat pesan pengingat yang dipersonalisasi menggunakan AI.</p>
                    </div>

                    <div v-if="reminderError" class="text-red-400 text-xs bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                        @{{ reminderError }}
                    </div>

                    <div v-if="reminderSuccess" class="text-neon text-xs bg-neon/10 border border-neon/20 rounded-xl p-3">
                        @{{ reminderSuccess }}
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showReminderModal = false"
                        class="flex-1 py-2.5 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 transition border border-slate-700">
                        Batal
                    </button>
                    <button type="button" @click="submitReminder" :disabled="reminderLoading"
                        class="flex-1 py-2.5 text-sm font-black text-black bg-neon rounded-xl hover:bg-neon/90 transition disabled:opacity-50">
                        @{{ reminderLoading ? 'Mengirim...' : 'Kirim Pengingat' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Advanced Workout Builder Modal -->
    <div v-if="builderVisible" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/80" @click="builderVisible = false"></div>
        <div class="relative z-10 max-w-2xl mx-auto my-10 glass-panel rounded-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-white font-bold text-lg">Advanced Workout Builder</h3>
                <button class="text-slate-400 hover:text-white" @click="builderVisible = false">×</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase">Type</label>
                    <select v-model="builderForm.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        <option value="easy_run">Easy Run</option>
                        <option value="long_run">Long Run</option>
                        <option value="tempo">Tempo</option>
                        <option value="interval">Intervals</option>
                        <option value="strength">Strength</option>
                        <option value="rest">Rest</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase">Title</label>
                    <input v-model="builderForm.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white" placeholder="Optional">
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="glass-panel rounded-xl p-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-slate-400 uppercase">Warm Up</div>
                        <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                            <input type="checkbox" v-model="builderForm.warmup.enabled" class="rounded bg-slate-900 border-slate-700 text-neon">
                            Enable
                        </label>
                    </div>
                    <div v-if="builderForm.warmup.enabled" class="mt-3 grid grid-cols-2 gap-2">
                        <select v-model="builderForm.warmup.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="distance">Distance</option>
                            <option value="time">Time</option>
                        </select>
                        <div v-if="builderForm.warmup.by==='distance'" class="flex gap-1">
                            <input type="number" step="any" v-model.number="builderForm.warmup.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                            <select v-model="builderForm.warmup.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                        </div>
                        <input v-else type="text" v-model="builderForm.warmup.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:10:00">
                    </div>
                </div>
                <div class="glass-panel rounded-xl p-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-slate-400 uppercase">Cool Down</div>
                        <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                            <input type="checkbox" v-model="builderForm.cooldown.enabled" class="rounded bg-slate-900 border-slate-700 text-neon">
                            Enable
                        </label>
                    </div>
                    <div v-if="builderForm.cooldown.enabled" class="mt-3 grid grid-cols-2 gap-2">
                        <select v-model="builderForm.cooldown.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="distance">Distance</option>
                            <option value="time">Time</option>
                        </select>
                        <div v-if="builderForm.cooldown.by==='distance'" class="flex gap-1">
                            <input type="number" step="any" v-model.number="builderForm.cooldown.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                            <select v-model="builderForm.cooldown.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                        </div>
                        <input v-else type="text" v-model="builderForm.cooldown.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:10:00">
                    </div>
                </div>
            </div>
            <div class="mt-4 glass-panel rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase mb-2">Main</div>
                <div v-if="builderForm.type==='easy_run'">
                    <div class="grid grid-cols-3 gap-2">
                        <select v-model="builderForm.main.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="distance">Distance</option>
                            <option value="time">Time</option>
                        </select>
                        <div v-if="builderForm.main.by==='distance'" class="flex gap-1">
                            <input type="number" step="any" v-model.number="builderForm.main.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                            <select v-model="builderForm.main.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                        </div>
                        <input v-else type="text" v-model="builderForm.main.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:30:00">
                        <input type="text" v-model="builderForm.main.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                    </div>
                </div>
                <div v-else-if="builderForm.type==='long_run'">
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <select v-model="builderForm.main.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="distance">Distance</option>
                            <option value="time">Time</option>
                        </select>
                        <div v-if="builderForm.main.by==='distance'" class="flex gap-1">
                            <input type="number" step="any" v-model.number="builderForm.main.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                            <select v-model="builderForm.main.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                        </div>
                        <input v-else type="text" v-model="builderForm.main.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:30:00">
                        <input type="text" v-model="builderForm.main.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                            <input type="checkbox" v-model="builderForm.longRun.fastFinish.enabled" class="rounded bg-slate-900 border-slate-700 text-neon">
                            Fast Finish
                        </label>
                        <div class="grid grid-cols-3 gap-1" v-if="builderForm.longRun.fastFinish.enabled">
                            <input type="number" step="any" v-model.number="builderForm.longRun.fastFinish.distance" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                            <select v-model="builderForm.longRun.fastFinish.unit" class="bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                            <input type="text" v-model="builderForm.longRun.fastFinish.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace">
                        </div>
                    </div>
                </div>
                <div v-else-if="builderForm.type==='tempo'">
                    <div class="grid grid-cols-4 gap-2">
                        <select v-model="builderForm.tempo.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="distance">Distance</option>
                            <option value="time">Time</option>
                        </select>
                        <div v-if="builderForm.tempo.by==='distance'" class="flex gap-1">
                            <input type="number" step="any" v-model.number="builderForm.tempo.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                            <select v-model="builderForm.tempo.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                        </div>
                        <input v-else type="text" v-model="builderForm.tempo.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:20:00">
                        <input type="text" v-model="builderForm.tempo.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                        <select v-model="builderForm.tempo.effort" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="moderate">Moderate</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
                <div v-else-if="builderForm.type==='interval'">
                    <div class="grid grid-cols-5 gap-2">
                        <input type="number" v-model.number="builderForm.interval.reps" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Reps">
                        <select v-model="builderForm.interval.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                            <option value="distance">Distance</option>
                            <option value="time">Time</option>
                        </select>
                        <div v-if="builderForm.interval.by==='distance'" class="flex gap-1">
                            <input type="number" step="any" v-model.number="builderForm.interval.repDistance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Rep dist">
                            <select v-model="builderForm.interval.repDistanceUnit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                <option value="km">km</option>
                                <option value="m">m</option>
                            </select>
                        </div>
                        <input v-else type="text" v-model="builderForm.interval.repTime" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Rep 00:03:00">
                        <input type="text" v-model="builderForm.interval.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                        <input type="text" v-model="builderForm.interval.recovery" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Recovery">
                    </div>
                </div>
                <div v-else-if="builderForm.type==='strength'">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <select v-model="builderForm.strength.category" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                <option value="">Select Category</option>
                                <option value="full_body">Full Body</option>
                                <option value="legs_lower_body">Legs/Lower Body</option>
                                <option value="core">Core</option>
                                <option value="upper_body">Upper Body</option>
                            </select>
                            <select v-model="builderForm.strength.exercise" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                <option value="">Select Exercise</option>
                                <option v-for="ex in strengthOptions" :key="ex.name" :value="ex.name">@{{ ex.name }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" v-model="builderForm.strength.sets" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Sets">
                            <input type="text" v-model="builderForm.strength.reps" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Reps/Dur">
                            <input type="text" v-model="builderForm.strength.equipment" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Equipment">
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="px-3 py-2 rounded-lg bg-slate-800 text-white text-xs" @click="addStrengthExercise">Add Exercise</button>
                        </div>
                        <div class="space-y-2" v-if="builderForm.strength.plan && builderForm.strength.plan.length">
                            <div v-for="(item, idx) in builderForm.strength.plan" :key="idx" class="flex items-center justify-between bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white">
                                <div>@{{ item.name }} — @{{ item.sets }} x @{{ item.reps }} (@{{ item.equipment }})</div>
                                <button type="button" class="text-slate-300 hover:text-white" @click="removeStrengthExercise(idx)">×</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="builderForm.type==='rest'">
                    <div class="text-slate-400 text-sm">Rest Day</div>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase">Intensity</label>
                    <select v-model="builderForm.intensity" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Notes Workout</label>
                <textarea v-model="builderForm.notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="Add notes for the athlete..."></textarea>
            </div>
            <div class="mt-4 glass-panel rounded-xl p-4">
                <div class="text-xs font-bold text-slate-400 uppercase mb-2">Summary</div>
                <div class="text-white text-sm">@{{ builderSummary }}</div>
                <div class="text-slate-400 text-xs mt-1">Total Distance: @{{ builderTotalDistance }} km</div>
            </div>
            <div class="flex justify-end items-center mt-4 gap-2">
                <button v-if="form.workout_id" type="button" class="px-4 py-2 rounded-lg bg-red-500/10 text-red-500 border border-red-500/20 text-sm hover:bg-red-500/20 mr-auto" @click="deleteCustomWorkout">Delete</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm" @click="builderVisible = false">Cancel</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm" @click="submitBuilder">
                    @{{ loading ? 'Saving...' : 'Save Workout' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="showWeeklyTargetModal" class="fixed inset-0 z-50 overflow-y-auto">
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
                    <p class="text-[10px] text-slate-500 mt-1">Set target jarak lari mingguan.</p>
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

    <!-- Race Modal -->
        <div v-if="showRaceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-sm p-5 relative">
                <button @click="showRaceModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
                <h3 class="text-lg font-black text-white mb-4">Add Race Event</h3>
                
                <form @submit.prevent="saveRace" class="space-y-3">
                    <!-- RuangLari Import -->
                    <div class="mb-3 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800 relative">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Import from RuangLari</label>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="eventSearchQuery"
                                @focus="showEventDropdown = true"
                                @blur="hideEventDropdown"
                                placeholder="Type to search event..."
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon focus:outline-none pl-8"
                            >
                            <span class="absolute left-3 top-2.5 text-slate-500"><i class="fa-solid fa-search text-[10px]"></i></span>
                            <button v-if="eventSearchQuery" @click="eventSearchQuery = ''; showEventDropdown = false" class="absolute right-3 top-2.5 text-slate-500 hover:text-white text-xs">✕</button>
                        </div>

                        <!-- Dropdown List -->
                        <div v-if="showEventDropdown && filteredEvents.length > 0" 
                            class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto">
                            <ul>
                                <li v-for="event in filteredEvents" :key="event.id"
                                    @click="selectRuangLariEvent(event)"
                                    class="px-4 py-3 hover:bg-slate-800 cursor-pointer border-b border-slate-800 last:border-0 text-slate-200"
                                >
                                    <div class="text-xs font-bold text-white">@{{ event.name || event.title }}</div>
                                    <div class="text-[10px] text-slate-300 flex justify-between mt-1">
                                        <span><i class="fa-solid fa-calendar text-[10px] text-slate-400 mr-1"></i>@{{ event.date || event.start_at }}</span>
                                        <span><i class="fa-solid fa-location-dot text-[10px] text-slate-400 mr-1"></i>@{{ event.location || event.location_name }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="showEventDropdown && filteredEvents.length === 0 && !loadingEvents" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-700 rounded-xl p-4 text-center text-slate-500 text-sm z-50">
                            No events found.
                        </div>

                        <div v-if="loadingEvents" class="text-[10px] text-cyan-400 mt-1 italic">Loading events...</div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Event Name</label>
                        <input v-model="raceForm.name" type="text" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon outline-none" placeholder="e.g. Jakarta Marathon" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Date</label>
                            <input v-model="raceForm.date" type="date" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon outline-none" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Distance</label>
                            <select v-model="raceForm.distance" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon outline-none">
                                <option value="5k">5K</option>
                                <option value="10k">10K</option>
                                <option value="21k">Half Marathon</option>
                                <option value="42k">Full Marathon</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Goal Time (Optional)</label>
                        <input v-model="raceForm.goal_time" type="text" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon outline-none" placeholder="hh:mm:ss">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Notes</label>
                        <textarea v-model="raceForm.notes" rows="2" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl p-2.5 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon outline-none" placeholder="Target pace strategy, etc."></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="loading" class="w-full py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition shadow-lg shadow-neon/15 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                            @{{ loading ? 'Saving...' : 'Add to Calendar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Strava Graph Modal -->
    <div v-if="showStravaGraphModal" class="fixed inset-0 z-[1200] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md">
        <div class="w-full max-w-5xl h-[80vh] bg-slate-900 border border-slate-700 rounded-2xl p-6 relative flex flex-col shadow-2xl shadow-neon/10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-black text-[#FC4C02] italic uppercase flex items-center gap-2">
                    <svg role="img" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                    Strava Analysis
                </h3>
                <button @click="showStravaGraphModal = false" class="text-slate-400 hover:text-white bg-slate-800 p-2 rounded-lg transition">✕</button>
            </div>
            <div class="flex-grow relative bg-slate-900/50 rounded-xl border border-slate-800 p-4">
                <canvas id="coachStravaMetricsChartFullscreen" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Floating Chat Widget -->
    <div class="fixed bottom-6 right-6 z-[1000] font-sans">
        <!-- Chat Bubble Button -->
        <button @click="toggleChatDrawer" class="w-14 h-14 rounded-full bg-neon text-dark font-black flex items-center justify-center shadow-2xl hover:scale-110 active:scale-95 transition-transform duration-200 relative border-2 border-slate-900">
            <span class="text-xl">💬</span>
            <!-- Unread Badge if any -->
            <span v-if="chatState.unreadCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center animate-bounce">
                @{{ chatState.unreadCount }}
            </span>
        </button>

        <!-- Chat Drawer/Panel -->
        <div v-if="chatState.isOpen" class="fixed bottom-24 right-6 w-[330px] sm:w-[380px] h-[480px] bg-slate-900/95 backdrop-blur-xl border border-slate-700/60 rounded-3xl flex flex-col shadow-2xl shadow-neon/10 overflow-hidden transition-all duration-300">
            <!-- Header -->
            <div class="bg-slate-800/80 border-b border-slate-700/50 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-slate-700 border border-slate-600 flex items-center justify-center font-bold text-white text-xs">
                        {{ substr($enrollment->runner->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-xs font-black text-white truncate max-w-[180px]">{{ $enrollment->runner->name }}</div>
                        <div class="text-[9px] text-neon flex items-center gap-1 font-mono uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-neon rounded-full animate-ping"></span> Active Chat
                        </div>
                    </div>
                </div>
                <button @click="chatState.isOpen = false" class="text-slate-400 hover:text-white font-bold text-sm bg-slate-800/50 p-1.5 rounded-lg">✕</button>
            </div>

            <!-- Messages Area -->
            <div ref="chatContainer" class="flex-grow p-4 overflow-y-auto space-y-3 bg-slate-950/20">
                <div v-if="chatState.loading" class="text-center text-xs text-slate-500 py-16">
                    <i class="fa-solid fa-circle-notch animate-spin text-neon mr-2"></i> Loading conversation…
                </div>
                <div v-else-if="chatState.messages.length === 0" class="text-center text-xs text-slate-500 py-16">
                    No messages yet. Send a message below to start coaching!
                </div>
                <div v-else v-for="msg in chatState.messages" :key="msg.id" class="flex flex-col"
                     :class="msg.sender_id === {{ auth()->id() }} ? 'items-end' : 'items-start'">
                    <div class="max-w-[80%] rounded-2xl px-3.5 py-2 text-xs leading-relaxed shadow"
                         :class="msg.sender_id === {{ auth()->id() }} ? 'bg-neon text-slate-950 rounded-tr-none font-bold' : 'bg-slate-800 text-white rounded-tl-none'">
                        @{{ msg.message }}
                    </div>
                    <span class="text-[8px] text-slate-500 mt-1 font-mono">@{{ formatChatTime(msg.created_at) }}</span>
                </div>
            </div>

            <!-- Footer / Input Form -->
            <form @submit.prevent="sendChatMessage" class="bg-slate-800/80 border-t border-slate-700/50 p-2.5 flex items-center gap-2">
                <input v-model="chatState.inputMessage" type="text" class="flex-grow bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:ring-1 focus:ring-neon outline-none" placeholder="Type a message…">
                <button type="submit" :disabled="!chatState.inputMessage.trim() || chatState.sending" class="p-2.5 rounded-xl bg-neon text-dark hover:bg-neon/90 transition disabled:opacity-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 fill-current transform rotate-45" viewBox="0 0 24 24">
                        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</main>
@endsection

@push('scripts')
@include('layouts.components.advanced-builder-utils')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="{{ asset('vendor/chart-js/chart.bundle.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
const { createApp, ref, reactive, onMounted, watch, computed } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let calendar = null;
        const selectedSession = ref(null);
        const loading = ref(false);
        const trainingProfile = reactive(@json($trainingProfile) || {});
        if (!trainingProfile.name) {
            trainingProfile.name = @json($enrollment->runner->name);
        }
        const profileTab = ref('training');
        const feedbackForm = reactive({
            coach_rating: 0,
            coach_feedback: ''
        });
        const coachUrl = @json(url('/coach'));
        const enrollmentId = @json($enrollment->id);
        const stravaDetailsLoading = ref(false);
        const stravaDetailsError = ref('');
        const stravaMetrics = ref(null);
        const stravaSplits = ref([]);
        const stravaLaps = ref([]);
        const stravaStreams = ref(null);
        const stravaPaceZones = ref(null);
        const stravaHrZones = ref(null);
        const stravaZoneAnalysis = ref('');
        const stravaZoneEffect = ref('');
        const stravaZoneSuggestion = ref('');
        const stravaAiAnalysis = ref(null);
        const stravaAiAnalysisLoading = ref(false);

        const stravaWorkoutClassification = computed(() => {
            // If AI analysis is loaded, use it as primary
            if (stravaAiAnalysis.value && stravaAiAnalysis.value.workout_classification) {
                const type = stravaAiAnalysis.value.workout_classification.type || 'unknown';
                const evidence = stravaAiAnalysis.value.workout_classification.evidence || [];
                return {
                    source: 'AI Coach',
                    type: type.toUpperCase(),
                    evidence: evidence,
                    colorClass: type === 'easy' ? 'text-green-400 border-green-500/30 bg-green-500/10' :
                                type === 'tempo' || type === 'threshold' ? 'text-yellow-400 border-yellow-500/30 bg-yellow-500/10' :
                                type === 'interval' || type === 'speed' ? 'text-orange-400 border-orange-500/30 bg-orange-500/10' :
                                'text-blue-400 border-blue-500/30 bg-blue-500/10'
                };
            }

            // Fallback: Rules-based using pace distribution & heart rate zones
            if (!stravaPaceZones.value && !stravaHrZones.value) return null;

            let type = 'EASY RUN / RECOVERY';
            const evidence = [];
            let colorClass = 'text-green-400 border-green-500/30 bg-green-500/10';

            const easyPace = parseFloat(stravaPaceZones.value?.summary?.easy || 0);
            const tempoPace = parseFloat(stravaPaceZones.value?.summary?.tempo || 0);
            const speedPace = parseFloat(stravaPaceZones.value?.summary?.speed || 0);

            const z1 = parseFloat(stravaHrZones.value?.Z1 || 0);
            const z2 = parseFloat(stravaHrZones.value?.Z2 || 0);
            const z3 = parseFloat(stravaHrZones.value?.Z3 || 0);
            const z4 = parseFloat(stravaHrZones.value?.Z4 || 0);
            const z5 = parseFloat(stravaHrZones.value?.Z5 || 0);

            // Classification logic
            if (speedPace > 15 || (z4 + z5) > 20) {
                type = 'INTERVAL / SPEED SESSION';
                colorClass = 'text-orange-400 border-orange-500/30 bg-orange-500/10';
                if (speedPace > 15) evidence.push(`Proporsi pace Speed/Interval tinggi (${speedPace.toFixed(0)}%).`);
                if ((z4 + z5) > 20) evidence.push(`Detak jantung di Zone 4 & 5 dominan (${(z4 + z5).toFixed(0)}%).`);
            } else if (tempoPace > 25 || z3 > 30) {
                type = 'TEMPO / THRESHOLD RUN';
                colorClass = 'text-yellow-400 border-yellow-500/30 bg-yellow-500/10';
                if (tempoPace > 25) evidence.push(`Pace Tempo mendominasi latihan (${tempoPace.toFixed(0)}%).`);
                if (z3 > 30) evidence.push(`Detak jantung berada di Zone 3 (Aerobic/Tempo) cukup lama (${z3.toFixed(0)}%).`);
            } else {
                type = 'EASY RUN / RECOVERY';
                colorClass = 'text-green-400 border-green-500/30 bg-green-500/10';
                evidence.push(`Mayoritas pace di zona Easy/Moderate (${easyPace.toFixed(0)}%).`);
                evidence.push(`Detak jantung stabil di Zone 1 & Zone 2 (${(z1 + z2).toFixed(0)}%).`);
            }

            return {
                source: 'Aturan Sistem',
                type: type,
                evidence: evidence,
                colorClass: colorClass
            };
        });

        let stravaChart = null;

        // Weekly Target State
        const showWeeklyTargetModal = ref(false);
        const weeklyTargetLoading = ref(false);
        const weeklyTargetForm = reactive({
            weekly_km_target: trainingProfile.weekly_km_target || ''
        });

        // Weekly Report State
        const weeklyReportLoading = ref(false);
        const weeklyReportPublishing = ref(false);
        const weeklyReportsList = ref(@json($enrollment->weeklyReports()->orderBy('week_number', 'desc')->get()) || []);
        const weeklyReportForm = reactive({
            week_number: weeklyReportsList.value.length > 0 ? Math.max(...weeklyReportsList.value.map(r => r.week_number)) + 1 : 1,
            report_text: ''
        });

        const updateWeeklyTarget = async () => {
            weeklyTargetLoading.value = true;
            try {
                // Assuming route is defined in blade or we construct it
                const res = await fetch(`{{ route('coach.athletes.update-weekly-target', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(weeklyTargetForm)
                });
                const data = await res.json();
                if (data.success) {
                    trainingProfile.weekly_km_target = data.weekly_km_target;
                    showWeeklyTargetModal.value = false;
                    alert('Weekly target updated and runner notified!');
                } else {
                    alert(data.message || 'Failed to update weekly target');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                weeklyTargetLoading.value = false;
            }
        };


        // ─── Reschedule Program State & Methods ───────────────────────
        const showRescheduleModal = ref(false);
        const rescheduleForm      = reactive({ new_start_date: '' });
        const rescheduleLoading   = ref(false);
        const rescheduleError     = ref('');
        const programDurationWeeks = {{ $enrollment->program->duration_weeks ?? 12 }};

        const previewRescheduleEndDate = Vue.computed(() => {
            if (!rescheduleForm.new_start_date) return '-';
            const d = new Date(rescheduleForm.new_start_date);
            d.setDate(d.getDate() + programDurationWeeks * 7);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        });

        const openRescheduleModal = () => {
            // Pre-fill with existing start date if available
            const existing = '{{ $enrollment->start_date ? \Carbon\Carbon::parse($enrollment->start_date)->format("Y-m-d") : "" }}';
            rescheduleForm.new_start_date = existing || new Date().toISOString().slice(0, 10);
            rescheduleError.value = '';
            showRescheduleModal.value = true;
        };

        const submitReschedule = async () => {
            if (!rescheduleForm.new_start_date) {
                rescheduleError.value = 'Pilih tanggal mulai baru terlebih dahulu.';
                return;
            }
            rescheduleLoading.value = true;
            rescheduleError.value = '';
            try {
                const url = `{{ route('coach.athletes.reschedule', $enrollment->id) }}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ new_start_date: rescheduleForm.new_start_date }),
                });
                const data = await res.json();
                if (data.success) {
                    showRescheduleModal.value = false;
                    alert(data.message || 'Program berhasil dijadwalkan ulang!');
                    window.location.reload();
                } else {
                    rescheduleError.value = data.message || 'Gagal mengubah jadwal.';
                }
            } catch (e) {
                rescheduleError.value = 'Terjadi kesalahan. Silakan coba lagi.';
            } finally {
                rescheduleLoading.value = false;
            }
        };

        // ─── Send Program Reminder State & Methods ───────────────────
        const showReminderModal = ref(false);
        const reminderForm      = reactive({ channel: 'both', custom_message: '' });
        const reminderLoading   = ref(false);
        const reminderError     = ref('');
        const reminderSuccess   = ref('');

        const openReminderModal = () => {
            reminderForm.channel = 'both';
            reminderForm.custom_message = '';
            reminderError.value = '';
            reminderSuccess.value = '';
            showReminderModal.value = true;
        };

        const submitReminder = async () => {
            if (!selectedSession.value) return;
            reminderLoading.value = true;
            reminderError.value = '';
            reminderSuccess.value = '';
            try {
                const url = `{{ route('coach.athletes.send-reminder', $enrollment->id) }}`;
                const payload = {
                    channel: reminderForm.channel,
                    custom_message: reminderForm.custom_message,
                };
                if (selectedSession.value.extendedProps.is_custom) {
                    payload.custom_workout_id = selectedSession.value.extendedProps.id;
                } else {
                    payload.session_day = selectedSession.value.extendedProps.session_day;
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (data.success) {
                    reminderSuccess.value = data.message || 'Pengingat berhasil dikirim!';
                    setTimeout(() => {
                        showReminderModal.value = false;
                    }, 1500);
                } else {
                    reminderError.value = data.message || 'Gagal mengirim pengingat.';
                }
            } catch (e) {
                reminderError.value = 'Terjadi kesalahan. Silakan coba lagi.';
            } finally {
                reminderLoading.value = false;
            }
        };
        // ─────────────────────────────────────────────────────────────

        // Workout Form State
        const showFormModal = ref(false);
        const form = reactive({ 
            workout_id:'', 
            workout_date:'', 
            type:'run', 
            difficulty:'moderate', 
            distance:'', 
            duration:'', 
            description:'',
            notes:'',
            workout_structure: [] 
        });

        // Advanced Builder State
        const builderVisible = ref(false);
        const builderForm = reactive({
            type: 'easy_run',
            title: '',
            notes: '',
            intensity: 'low',
            warmup: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' },
            cooldown: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' },
            main: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
            longRun: { fastFinish: { enabled: false, distance: 0, unit: 'km', pace: '' } },
            tempo: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' },
            interval: { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' },
            strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
        });

        const strengthData = {
            strength_training: {
                full_body: [
                    { name: 'Burpees', sets: '3', reps: '12-15', equipment: 'Bodyweight' },
                    { name: 'Kettlebell Swing', sets: '3', reps: '15-20', equipment: 'Kettlebell' },
                    { name: 'Clean and Press', sets: '4', reps: '8-10', equipment: 'Barbell/Dumbbell' },
                    { name: 'Thrusters', sets: '3', reps: '10-12', equipment: 'Dumbbell/Barbell' }
                ],
                legs_lower_body: [
                    { name: 'Squats', sets: '4', reps: '8-12', equipment: 'Barbell/Bodyweight' },
                    { name: 'Lunges', sets: '3', reps: '10 each leg', equipment: 'Bodyweight/Dumbbell' },
                    { name: 'Deadlifts', sets: '4', reps: '6-10', equipment: 'Barbell' },
                    { name: 'Glute Bridge / Hip Thrust', sets: '3', reps: '12-15', equipment: 'Bodyweight/Barbell' },
                    { name: 'Calf Raises', sets: '3', reps: '15-20', equipment: 'Bodyweight/Dumbbell' }
                ],
                core: [
                    { name: 'Plank', sets: '3', duration: '45-60s', equipment: 'Bodyweight' },
                    { name: 'Russian Twist', sets: '3', reps: '20 (10 each side)', equipment: 'Bodyweight/Medicine Ball' },
                    { name: 'Leg Raises', sets: '3', reps: '12-15', equipment: 'Bodyweight' },
                    { name: 'Bicycle Crunch', sets: '3', reps: '20 (10 each side)', equipment: 'Bodyweight' },
                    { name: 'Ab Rollout', sets: '3', reps: '8-12', equipment: 'Ab Wheel/Barbell' }
                ],
                upper_body: [
                    { name: 'Push-Ups', sets: '3', reps: '12-20', equipment: 'Bodyweight' },
                    { name: 'Bench Press', sets: '4', reps: '6-10', equipment: 'Barbell/Dumbbell' },
                    { name: 'Pull-Ups / Chin-Ups', sets: '3', reps: '8-12', equipment: 'Bodyweight' },
                    { name: 'Overhead Press', sets: '4', reps: '8-10', equipment: 'Barbell/Dumbbell' },
                    { name: 'Bent Over Row', sets: '4', reps: '8-12', equipment: 'Barbell/Dumbbell' },
                    { name: 'Bicep Curl', sets: '3', reps: '12-15', equipment: 'Dumbbell/Barbell' },
                    { name: 'Tricep Dips', sets: '3', reps: '10-12', equipment: 'Bodyweight/Bench' }
                ]
            }
        };

        const strengthOptions = Vue.computed(() => {
            const cat = builderForm.strength.category;
            const all = strengthData.strength_training;
            return (cat && all[cat]) ? all[cat] : [];
        });

        const addStrengthExercise = () => {
            const ex = builderForm.strength.exercise;
            const cat = builderForm.strength.category;
            const list = strengthData.strength_training[cat] || [];
            const found = list.find(i => i.name === ex);
            const item = {
                name: ex || '',
                sets: builderForm.strength.sets || (found ? found.sets : ''),
                reps: builderForm.strength.reps || (found ? found.reps : ''),
                equipment: builderForm.strength.equipment || (found ? found.equipment : '')
            };
            if (!builderForm.strength.plan) builderForm.strength.plan = [];
            builderForm.strength.plan.push(item);
            builderForm.strength.exercise = '';
            builderForm.strength.sets = '';
            builderForm.strength.reps = '';
            builderForm.strength.equipment = '';
        };

        const removeStrengthExercise = (idx) => {
            if (!builderForm.strength.plan) return;
            builderForm.strength.plan.splice(idx, 1);
        };

        const builderSummary = Vue.computed(() => RLBuilderUtils.buildSummary(builderForm));

        const parseDurationMinutes = (str) => {
            if (!str) return 0;
            // Handle number input (already minutes)
            if (typeof str === 'number') return str;
            
            const parts = str.toString().split(':').map(Number);
            if (parts.length === 1) return parts[0]; // "30" -> 30 mins
            if (parts.length === 2) return parts[0] + parts[1]/60;
            if (parts.length === 3) return parts[0]*60 + parts[1] + parts[2]/60;
            return 0;
        };

        const minutesToHHMMSS = (mins) => {
            const h = Math.floor(mins / 60);
            const m = Math.floor(mins % 60);
            const s = Math.round((mins * 60) % 60);
            return `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        };

        const builderTotalDistance = Vue.computed(() => RLBuilderUtils.computeTotalDistance(builderForm));

        const openBuilder = (isEditing) => {
            // Always reset to defaults first
            Object.assign(builderForm, {
                type: 'easy_run',
                title: '',
                notes: '',
                intensity: 'low',
                warmup: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
                cooldown: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
                main: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
                longRun: { fastFinish: { enabled: false, distance: 0, unit: 'km', pace: '' } },
                tempo: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' },
                interval: { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' },
                strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
            });

            if (isEditing) {
                // Try to load existing advanced config
                let config = null;
                if (form.workout_structure && form.workout_structure.advanced) {
                    config = form.workout_structure.advanced;
                }
                
                if (config) {
                    Object.assign(builderForm, config);
                    // Ensure notes are synced if present in main form but not in advanced config (legacy support)
                    if (!builderForm.notes && form.notes) builderForm.notes = form.notes;
                    
                    // Backward compatibility: map old distanceKm / repDistanceKm properties to new distance / unit
                    if (builderForm.warmup && builderForm.warmup.distanceKm !== undefined) {
                        builderForm.warmup.distance = builderForm.warmup.distanceKm;
                        builderForm.warmup.unit = 'km';
                    }
                    if (builderForm.cooldown && builderForm.cooldown.distanceKm !== undefined) {
                        builderForm.cooldown.distance = builderForm.cooldown.distanceKm;
                        builderForm.cooldown.unit = 'km';
                    }
                    if (builderForm.main && builderForm.main.distanceKm !== undefined) {
                        builderForm.main.distance = builderForm.main.distanceKm;
                        builderForm.main.unit = 'km';
                    }
                    if (builderForm.longRun && builderForm.longRun.fastFinish && builderForm.longRun.fastFinish.distanceKm !== undefined) {
                        builderForm.longRun.fastFinish.distance = builderForm.longRun.fastFinish.distanceKm;
                        builderForm.longRun.fastFinish.unit = 'km';
                    }
                    if (builderForm.tempo && builderForm.tempo.distanceKm !== undefined) {
                        builderForm.tempo.distance = builderForm.tempo.distanceKm;
                        builderForm.tempo.unit = 'km';
                    }
                    if (builderForm.interval && builderForm.interval.repDistanceKm !== undefined) {
                        builderForm.interval.repDistance = builderForm.interval.repDistanceKm;
                        builderForm.interval.repDistanceUnit = 'km';
                    }
                } else {
                    // If no advanced config, try to match type and pre-fill from basic form
                    // Map legacy/simple types to builder types
                    let targetType = RLBuilderUtils.normalizeType(form.type);
                    
                    if (['easy_run', 'long_run', 'tempo', 'interval', 'strength', 'rest'].includes(targetType)) {
                        builderForm.type = targetType;
                        builderForm.notes = form.notes || '';
                        
                        // Attempt to pre-fill main values from basic form
                        if (['easy_run', 'long_run'].includes(targetType)) {
                            if (form.distance) {
                                builderForm.main.by = 'distance';
                                builderForm.main.distance = form.distance;
                                builderForm.main.unit = 'km';
                            } else if (form.duration) {
                                builderForm.main.by = 'time';
                                builderForm.main.duration = form.duration;
                            }
                        } else if (targetType === 'tempo') {
                            if (form.distance) {
                                builderForm.tempo.by = 'distance';
                                builderForm.tempo.distance = form.distance;
                                builderForm.tempo.unit = 'km';
                            } else if (form.duration) {
                                builderForm.tempo.by = 'time';
                                builderForm.tempo.duration = form.duration;
                            }
                        }
                    }
                }
            }
            builderVisible.value = true;
        };

        const submitBuilder = () => {
            // Update the main form with builder data
            const advancedConfig = JSON.parse(JSON.stringify(builderForm));
            form.workout_structure = { advanced: advancedConfig };
            form.description = builderSummary.value;
            form.notes = builderForm.notes;
            form.distance = builderTotalDistance.value;
            form.type = builderForm.type; // Sync type
            
            // Try to set duration if possible
            if (['easy_run', 'long_run'].includes(builderForm.type) && builderForm.main.by === 'time') {
                form.duration = builderForm.main.duration;
            } else if (builderForm.type === 'tempo' && builderForm.tempo.by === 'time') {
                form.duration = builderForm.tempo.duration;
            } else if (builderForm.type === 'interval' && builderForm.interval.by === 'time') {
                const perRep = parseDurationMinutes(builderForm.interval.repTime);
                const total = (Number(builderForm.interval.reps)||0) * (isNaN(perRep)?0:perRep);
                form.duration = minutesToHHMMSS(total);
            }

            // Submit to server
            saveCustomWorkout();
        };

        // Workout Builder Helper Methods
        const addStep = (type) => {
            if (!Array.isArray(form.workout_structure)) form.workout_structure = [];
            form.workout_structure.push({
                type: type, // warmup, run, interval, recovery, rest, cool_down
                duration_type: 'distance', // distance, time
                value: '',
                unit: 'km', // km, min, m, sec
                notes: ''
            });
        };

        const removeStep = (index) => {
            if (Array.isArray(form.workout_structure)) {
                form.workout_structure.splice(index, 1);
            }
        };

        const moveStep = (index, direction) => {
            if (!Array.isArray(form.workout_structure)) return;
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
            if (!Array.isArray(form.workout_structure)) return;
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

        const openForm = (dateStr, session = null) => {
            if (session) {
                // Edit Mode
                if (session.extendedProps.is_custom) {
                    form.workout_id = session.extendedProps.id;
                    form.workout_structure = session.extendedProps.workout_structure || [];
                    
                    // Auto-open builder for editing
                    openBuilder(true);
                } else {
                    form.workout_id = '';
                    form.workout_structure = [];
                }
                
                form.workout_date = session.startStr.split('T')[0];
                form.type = session.extendedProps.type;
                form.difficulty = session.extendedProps.difficulty || 'moderate';
                form.distance = session.extendedProps.distance;
                form.duration = session.extendedProps.duration || '';
                form.description = session.extendedProps.description;
                form.notes = session.extendedProps.notes || ''; // Add notes

                // If it was not custom (standard program workout), we now open builder in edit mode (prefilled)
                if (!session.extendedProps.is_custom) {
                    openBuilder(true);
                }
            } else {
                // Create Mode
                form.workout_id = '';
                form.workout_date = dateStr;
                form.type = 'run';
                form.difficulty = 'moderate';
                form.distance = '';
                form.duration = '';
                form.description = '';
                form.notes = '';
                form.workout_structure = [];
                
                // Auto-open builder for creating
                openBuilder(false);
            }
        };

        const deleteCustomWorkout = async () => {
            if(!confirm('Are you sure you want to delete this workout?')) return;
            
            loading.value = true;
            try {
                const url = `{{ route('coach.athletes.workout.destroy', ['enrollment' => $enrollment->id, 'customWorkout' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', form.workout_id);
                
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    builderVisible.value = false;
                    alert('Workout deleted');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete');
                }
            } catch(e) {
                alert('Error deleting workout');
            } finally {
                loading.value = false;
            }
        };

        const saveCustomWorkout = async () => {
            loading.value = true;
            try {
                const payload = {
                    workout_date: form.workout_date,
                    type: form.type,
                    difficulty: form.difficulty,
                    distance: form.distance || null,
                    duration: form.duration || null,
                    description: form.description || null,
                    notes: form.notes || null,
                    workout_structure: form.workout_structure,
                };
                
                let url = `{{ route('coach.athletes.workout.store', $enrollment->id) }}`;
                let method = 'POST';
                
                if (form.workout_id) {
                    url = `{{ route('coach.athletes.workout.update', ['enrollment' => $enrollment->id, 'customWorkout' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', form.workout_id);
                    method = 'PUT';
                }

                const res = await fetch(url, {
                    method: method,
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showFormModal.value = false;
                    alert('Workout saved successfully');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to save workout');
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred');
            } finally {
                loading.value = false;
            }
        };

        // Race State
        const showRaceModal = ref(false);
        const raceForm = reactive({
            name: '',
            date: new Date().toISOString().slice(0,10),
            distance: '10k',
            goal_time: '',
            notes: '',
            distLabel: ''
        });

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
                (e.name || e.title || '').toLowerCase().includes(query) || 
                ((e.location_name || e.location || '').toLowerCase().includes(query))
            );
        });

        const fetchRuangLariEvents = async () => {
            if (ruangLariEvents.value.length > 0) return;
            loadingEvents.value = true;
            try {
                // Use the proxy route to avoid CORS on localhost
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
            eventSearchQuery.value = event.name || event.title;
            showEventDropdown.value = false;
            onSelectRuangLariEvent(event);
        };

        const hideEventDropdown = () => {
            setTimeout(() => {
                showEventDropdown.value = false;
            }, 200);
        };

        const onSelectRuangLariEvent = (event) => {
            if (!event) return;
            raceForm.name = event.name || event.title;
            
            // Parse date
            let dateStr = event.start_at || event.date;
            if (dateStr) {
                if (dateStr.includes('-')) {
                     raceForm.date = dateStr.split(' ')[0];
                } 
                else if (dateStr.includes('/')) {
                    const parts = dateStr.split('/');
                    if (parts.length === 3) {
                        const mm = parts[0].padStart(2, '0');
                        const dd = parts[1].padStart(2, '0');
                        const yyyy = parts[2];
                        raceForm.date = `${yyyy}-${mm}-${dd}`;
                    }
                }
            }
            
            // Guess distance
            const titleLower = (event.name || event.title || '').toLowerCase();
            if (titleLower.includes('marathon') && !titleLower.includes('half')) {
                raceForm.distance = '42k';
            } else if (titleLower.includes('half') || titleLower.includes('hm')) {
                raceForm.distance = '21k';
            } else if (titleLower.includes('10k')) {
                raceForm.distance = '10k';
            } else if (titleLower.includes('5k')) {
                raceForm.distance = '5k';
            }
            
            const link = event.slug ? `/event-lari/${event.slug}` : (event.link || '');
            const loc = event.location_name || event.location || '';
            raceForm.notes = `Event Link: ${link}\nLocation: ${loc}`;
        };

        // Watch modal open to fetch events
        watch(showRaceModal, (val) => {
            if (val) {
                fetchRuangLariEvents();
            }
        });

        watch(showStravaGraphModal, (val) => {
            if (val && stravaStreams.value) {
                setTimeout(() => {
                    if (stravaFullscreenChart) stravaFullscreenChart.destroy();
                    stravaFullscreenChart = renderChartToCanvas(stravaStreams.value, 'coachStravaMetricsChartFullscreen');
                }, 100);
            }
        });

        const openRaceForm = () => {
            raceForm.name = '';
            raceForm.date = new Date().toISOString().slice(0,10);
            raceForm.distance = '10k';
            raceForm.goal_time = '';
            raceForm.notes = '';
            showRaceModal.value = true;
        };

        const saveRace = async () => {
            loading.value = true;
            try {
                // Determine distLabel
                let label = '';
                if(raceForm.distance === '5k') label = '5K';
                else if(raceForm.distance === '10k') label = '10K';
                else if(raceForm.distance === '21k') label = 'HM';
                else if(raceForm.distance === '42k') label = 'FM';

                const res = await fetch(`{{ route('coach.athletes.race.store', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        workout_date: raceForm.date,
                        race_name: raceForm.name,
                        distance: label ? parseFloat(raceForm.distance) : null, // This might need parsing logic if value is string like '5k'
                        dist_label: label,
                        goal_time: raceForm.goal_time,
                        notes: raceForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showRaceModal.value = false;
                    alert('Race added successfully');
                    // Refresh calendar
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to add race');
                }
            } catch(e) {
                alert('An error occurred');
            } finally {
                loading.value = false;
            }
        };

        const formatPace = (minPerKm) => {
            if (!minPerKm) return '-';
            const mins = Math.floor(minPerKm);
            const secs = Math.round((minPerKm - mins) * 60);
            return `${mins}:${secs.toString().padStart(2,'0')}`;
        };

        const getPaceInfo = (type, distance) => {
            if (!type || !trainingProfile) return null;

            const tLower = type.toLowerCase();
            const map = { 
                easy_run: 'E', recovery: 'E', run: 'E', 
                long_run: 'M', 
                tempo: 'T', threshold: 'T', 
                interval: 'I', vo2max: 'I',
                repetition: 'R', speed: 'R'
            };
            const typeKey = map[tLower];

            // Check for track distance logic (0.1km - 2.0km)
            if (distance && trainingProfile.track_times) {
                const dist = parseFloat(distance);
                
                if (dist >= 0.1 && dist <= 2.0) {
                    const m = Math.round(dist * 1000);
                    const key = m + 'm';
                    
                    // If exact track distance found, return split times
                    if (trainingProfile.track_times[key]) {
                        const t = trainingProfile.track_times[key];
                        
                        let targetInfo = '';
                        if (typeKey) {
                            let useKey = typeKey;
                            // Logic override: If Interval (I) and distance 100-400m, use Repetition (R) pace
                            if (typeKey === 'I' && dist >= 0.1 && dist <= 0.405) {
                                useKey = 'R';
                            }
                            
                            if (['I','R','T'].includes(useKey) && t[useKey]) {
                                targetInfo = ` | Target: ${t[useKey]}`;
                            }
                        }

                        // Return all 3 relevant paces/splits for context
                        return `Split Times (${key}): Rep=${t.R} | Int=${t.I} | Thr=${t.T}${targetInfo}`;
                    }
                }
            }

            if (!typeKey) return null;

            let val = trainingProfile.paces?.[typeKey];
            if (!val && typeKey === 'M') val = trainingProfile.paces?.['E']; // Fallback M -> E
            
            return val ? `Target Pace: ${formatPace(val)} /km` : null;
        };

        const statusClass = (s) => {
            if(s === 'completed') return 'bg-green-500/20 text-green-500';
            if(s === 'started') return 'bg-yellow-500/20 text-yellow-500';
            return 'bg-slate-700 text-slate-400';
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

        const destroyStravaChart = () => {
            try {
                if (stravaChart) stravaChart.destroy();
                if (stravaFullscreenChart) stravaFullscreenChart.destroy();
            } catch (e) {}
            stravaChart = null;
            stravaFullscreenChart = null;
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
            if (stravaChart) stravaChart.destroy();
            stravaChart = renderChartToCanvas(streams, 'coachStravaMetricsChart');
        };

        const extractStravaActivityId = (url) => {
            const m = String(url || '').match(/strava\.com\/activities\/(\d+)/i);
            return m ? parseInt(m[1], 10) : null;
        };

        const resetStravaState = () => {
            stravaDetailsLoading.value = false;
            stravaDetailsError.value = '';
            stravaMetrics.value = null;
            stravaSplits.value = [];
            stravaLaps.value = [];
            stravaStreams.value = null;
            stravaPaceZones.value = null;
            stravaHrZones.value = null;
            stravaZoneAnalysis.value = '';
            stravaZoneEffect.value = '';
            stravaZoneSuggestion.value = '';
            stravaAiAnalysis.value = null;
            stravaAiAnalysisLoading.value = false;
            destroyStravaChart();
        };

        const loadStravaForActivity = async (activityId, force = false) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;

            resetStravaState();
            stravaDetailsLoading.value = true;
            try {
                const detailRes = await fetch(`${coachUrl}/athletes/${enrollmentId}/strava/activities/${id}/details`, { headers: { 'Accept': 'application/json' } });
                const detailJson = await detailRes.json();
                if (detailRes.ok && detailJson && detailJson.success) {
                    stravaMetrics.value = detailJson.activity || null;
                    stravaSplits.value = Array.isArray(detailJson.activity?.splits_metric) ? detailJson.activity.splits_metric : [];
                    stravaLaps.value = Array.isArray(detailJson.activity?.laps) ? detailJson.activity.laps : [];
                } else {
                    stravaDetailsError.value = detailJson?.message || 'Gagal mengambil detail Strava.';
                    return;
                }

                const streamsRes = await fetch(`${coachUrl}/athletes/${enrollmentId}/strava/activities/${id}/streams`, { headers: { 'Accept': 'application/json' } });
                const streamsJson = await streamsRes.json();
                if (streamsRes.ok && streamsJson && streamsJson.success) {
                    stravaStreams.value = streamsJson.streams || null;
                    setTimeout(() => renderStravaChart(stravaStreams.value), 50);
                    stravaPaceZones.value = buildPaceZones(stravaStreams.value, trainingProfile.paces || {});
                    stravaHrZones.value = buildHrZones(stravaStreams.value, stravaMetrics.value?.max_heartrate);
                    const zoneInsight = buildZoneAnalysis(stravaPaceZones.value, stravaHrZones.value, stravaMetrics.value);
                    stravaZoneAnalysis.value = zoneInsight?.analysis || '';
                    stravaZoneEffect.value = zoneInsight?.effect || '';
                    stravaZoneSuggestion.value = zoneInsight?.suggestion || '';
                }

                // Fetch AI analysis asynchronously
                stravaAiAnalysisLoading.value = true;
                const aiUrl = new URL(`${coachUrl}/athletes/${enrollmentId}/strava/activities/${id}/ai-analysis`, window.location.origin);
                if (force) {
                    aiUrl.searchParams.set('force', '1');
                }
                fetch(aiUrl.toString(), { headers: { 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(aiJson => {
                        if (aiJson && aiJson.success) {
                            stravaAiAnalysis.value = aiJson.analysis || null;
                        }
                    })
                    .catch(e => console.error('Error fetching Strava AI Analysis:', e))
                    .finally(() => {
                        stravaAiAnalysisLoading.value = false;
                    });

            } catch (e) {
                stravaDetailsError.value = 'Gagal mengambil detail Strava.';
            } finally {
                stravaDetailsLoading.value = false;
            }
        };

        watch(showStravaGraphModal, (val) => {
            if (val && stravaStreams.value) {
                setTimeout(() => {
                    if (stravaFullscreenChart) stravaFullscreenChart.destroy();
                    stravaFullscreenChart = renderChartToCanvas(stravaStreams.value, 'coachStravaMetricsChartFullscreen');
                }, 100);
            } else {
                if (stravaFullscreenChart) {
                    stravaFullscreenChart.destroy();
                    stravaFullscreenChart = null;
                }
            }
        });

        const formatDate = (d) => {
            return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
        };

        const paceMinToSec = (minPerKm) => {
            const v = parseFloat(minPerKm || 0);
            if (!v || v <= 0) return null;
            return v * 60;
        };

        const buildPaceZones = (streams, paces) => {
            if (!streams || !Array.isArray(streams.velocity_smooth)) return null;
            const thresholds = {
                E: paceMinToSec(paces?.E),
                M: paceMinToSec(paces?.M),
                T: paceMinToSec(paces?.T),
                I: paceMinToSec(paces?.I),
                R: paceMinToSec(paces?.R)
            };
            if (!thresholds.E || !thresholds.M || !thresholds.T || !thresholds.I || !thresholds.R) return null;
            const counts = { E: 0, M: 0, T: 0, I: 0, R: 0 };
            let total = 0;
            streams.velocity_smooth.forEach((v) => {
                const paceSec = toPaceSecPerKm(v);
                if (!paceSec) return;
                total += 1;
                if (paceSec >= thresholds.E) counts.E += 1;
                else if (paceSec >= thresholds.M) counts.M += 1;
                else if (paceSec >= thresholds.T) counts.T += 1;
                else if (paceSec >= thresholds.I) counts.I += 1;
                else counts.R += 1;
            });
            if (!total) return null;
            const toPct = (v) => Math.round((v / total) * 100);
            const zones = {
                E: toPct(counts.E),
                M: toPct(counts.M),
                T: toPct(counts.T),
                I: toPct(counts.I),
                R: toPct(counts.R)
            };
            return {
                zones,
                summary: {
                    easy: Math.round(zones.E + zones.M),
                    tempo: Math.round(zones.T),
                    speed: Math.round(zones.I + zones.R)
                }
            };
        };

        const buildHrZones = (streams, maxHrValue) => {
            if (!streams || !Array.isArray(streams.heartrate)) return null;
            const maxFromStream = Math.max(...streams.heartrate.filter(v => typeof v === 'number' && !isNaN(v)));
            const maxHr = parseFloat(maxHrValue || maxFromStream || 0);
            if (!maxHr || maxHr <= 0) return null;
            const counts = { Z1: 0, Z2: 0, Z3: 0, Z4: 0, Z5: 0 };
            let total = 0;
            streams.heartrate.forEach((v) => {
                const hr = typeof v === 'number' ? v : parseFloat(v);
                if (!hr || hr <= 0) return;
                const ratio = hr / maxHr;
                total += 1;
                if (ratio < 0.6) counts.Z1 += 1;
                else if (ratio < 0.7) counts.Z2 += 1;
                else if (ratio < 0.8) counts.Z3 += 1;
                else if (ratio < 0.9) counts.Z4 += 1;
                else counts.Z5 += 1;
            });
            if (!total) return null;
            const toPct = (v) => Math.round((v / total) * 100);
            return {
                Z1: toPct(counts.Z1),
                Z2: toPct(counts.Z2),
                Z3: toPct(counts.Z3),
                Z4: toPct(counts.Z4),
                Z5: toPct(counts.Z5)
            };
        };

        const buildZoneAnalysis = (paceZones, hrZones, metrics) => {
            if (!paceZones && !hrZones) return null;
            const analysis = [];
            const effects = [];
            const suggestions = [];

            const easy = paceZones?.summary?.easy ?? null;
            const tempo = paceZones?.summary?.tempo ?? null;
            const speed = paceZones?.summary?.speed ?? null;

            const z1 = hrZones?.Z1 ?? null;
            const z2 = hrZones?.Z2 ?? null;
            const z3 = hrZones?.Z3 ?? null;
            const z4 = hrZones?.Z4 ?? null;
            const z5 = hrZones?.Z5 ?? null;

            const total = metrics?.total_time_s || 0;
            const pause = metrics?.pause_time_s || 0;
            const pauseRatio = total ? pause / total : 0;

            if (easy !== null && easy >= 70) {
                analysis.push('Distribusi pace dominan Easy, fokus utama ada di base aerobik dan efisiensi.');
            } else if (speed !== null && speed >= 30) {
                analysis.push('Porsi speed cukup besar, sesi ini tergolong intens dan menstimulasi VO2Max/kecepatan.');
            } else if (tempo !== null && tempo >= 30) {
                analysis.push('Tempo cukup dominan, latihan mengarah ke penguatan threshold dan ketahanan pace.');
            } else if (easy !== null || tempo !== null || speed !== null) {
                analysis.push('Distribusi pace cukup seimbang, efeknya campuran antara aerobik dan kualitas.');
            }

            if (z1 !== null && z2 !== null && (z1 + z2) >= 70) {
                analysis.push('Mayoritas detak jantung berada di Z1–Z2, menunjukkan sesi aerobik atau recovery.');
            } else if (z4 !== null && z5 !== null && (z4 + z5) >= 30) {
                analysis.push('Zona detak jantung Z4–Z5 cukup tinggi, beban latihan berat dan menstimulasi adaptasi intensitas.');
            } else if (z3 !== null && z4 !== null && (z3 + z4) >= 40) {
                analysis.push('Banyak waktu di Z3–Z4, cocok untuk tempo/threshold dan peningkatan stamina pace.');
            }

            if (pauseRatio > 0.18) {
                analysis.push('Proporsi pause cukup besar, artinya banyak berhenti sehingga efek intensitas berkurang.');
            } else if (pauseRatio > 0.08) {
                analysis.push('Terdapat pause moderat, kemungkinan karena interval atau kondisi rute.');
            }

            if (speed !== null && speed >= 25) {
                effects.push('VO2Max & kecepatan');
            } else if (tempo !== null && tempo >= 25) {
                effects.push('Threshold & tempo endurance');
            } else if (easy !== null && easy >= 60) {
                effects.push('Base aerobik & recovery');
            } else if (z4 !== null && z5 !== null && (z4 + z5) >= 25) {
                effects.push('Kualitas intensitas tinggi');
            } else if (z1 !== null && z2 !== null && (z1 + z2) >= 60) {
                effects.push('Aerobic maintenance');
            } else {
                effects.push('Mixed aerobic & quality');
            }

            if (speed !== null && speed >= 30) {
                suggestions.push('Prioritaskan recovery run 24–48 jam ke depan agar adaptasi optimal.');
            } else if (tempo !== null && tempo >= 30) {
                suggestions.push('Pertahankan volume easy berikutnya, lalu sisipkan interval ringan jika tubuh segar.');
            } else if (easy !== null && easy >= 70) {
                suggestions.push('Kondisi cocok untuk sesi kualitas berikutnya (tempo/interval) jika rencana mengizinkan.');
            } else {
                suggestions.push('Jaga keseimbangan easy dan kualitas untuk mencegah overtraining.');
            }

            if (pauseRatio > 0.18) {
                suggestions.push('Jika targetnya steady run, kurangi pause agar stimulus lebih konsisten.');
            }

            return {
                analysis: analysis.join(' '),
                effect: effects.join(' • '),
                suggestion: suggestions.join(' ')
            };
        };

        const saveFeedback = async () => {
            if (!selectedSession.value) return;
            
            loading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.feedback', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        session_day: selectedSession.value.extendedProps.session_day,
                        coach_rating: feedbackForm.coach_rating,
                        coach_feedback: feedbackForm.coach_feedback
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Feedback saved!');
                    // Update local state
                    if (selectedSession.value.extendedProps.tracking) {
                        selectedSession.value.extendedProps.tracking.coach_rating = feedbackForm.coach_rating;
                        selectedSession.value.extendedProps.tracking.coach_feedback = feedbackForm.coach_feedback;
                    } else {
                        // In case tracking was created on the fly (unlikely in this flow but good safety)
                        window.location.reload(); 
                    }
                }
            } catch (e) {
                alert('Failed to save feedback');
            } finally {
                loading.value = false;
            }
        };

        // Race Predictor State & Logic
        const predictor = reactive({
            distance: 15,
            vdot: trainingProfile.vdot || 40
        });

        const predictedTime = computed(() => {
            const timeStr = trainingProfile.equivalent_race_times?.['10k']?.time || '00:50:00';
            const parts = timeStr.split(':');
            let baselineSeconds = 0;
            if (parts.length === 3) {
                baselineSeconds = (+parts[0]) * 3600 + (+parts[1]) * 60 + (+parts[2]);
            } else if (parts.length === 2) {
                baselineSeconds = (+parts[0]) * 60 + (+parts[1]);
            }
            if (!baselineSeconds) baselineSeconds = 3000;
            
            const baselineVdot = trainingProfile.vdot || 40;
            const chosenVdot = predictor.vdot || 40;
            const adjustedBaselineSeconds = baselineSeconds * (baselineVdot / chosenVdot);

            const predictedSeconds = adjustedBaselineSeconds * Math.pow(predictor.distance / 10, 1.06);
            
            const hours = Math.floor(predictedSeconds / 3600);
            const minutes = Math.floor((predictedSeconds % 3600) / 60);
            const seconds = Math.floor(predictedSeconds % 60);
            
            let timeFormatted = '';
            if (hours > 0) {
                timeFormatted += String(hours).padStart(2, '0') + ':';
            }
            timeFormatted += String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            const paceMin = (predictedSeconds / 60) / predictor.distance;
            const paceM = Math.floor(paceMin);
            const paceS = Math.floor((paceMin - paceM) * 60);
            const paceFormatted = String(paceM).padStart(2, '0') + ':' + String(paceS).padStart(2, '0');

            return { time: timeFormatted, pace: paceFormatted };
        });

        // Chat Widget State & Logic
        const chatContainer = ref(null);
        const chatState = reactive({
            isOpen: false,
            loading: false,
            messages: [],
            unreadCount: 0,
            inputMessage: '',
            sending: false
        });

        const toggleChatDrawer = () => {
            chatState.isOpen = !chatState.isOpen;
            if (chatState.isOpen) {
                loadChatMessages();
            }
        };

        const loadChatMessages = async () => {
            chatState.loading = true;
            try {
                const runnerId = trainingProfile.user_id || @json($enrollment->runner_id);
                const res = await fetch(`/api/chat/${runnerId}/messages`);
                const data = await res.json();
                chatState.messages = data.messages || [];
                chatState.unreadCount = 0;
                scrollToBottom();
            } catch (e) {
                console.error('Failed to load chat messages:', e);
            } finally {
                chatState.loading = false;
            }
        };

        const sendChatMessage = async () => {
            if (!chatState.inputMessage.trim() || chatState.sending) return;
            chatState.sending = true;
            try {
                const runnerId = trainingProfile.user_id || @json($enrollment->runner_id);
                const res = await fetch(`/chat/${runnerId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: chatState.inputMessage })
                });
                const data = await res.json();
                if (data.success) {
                    chatState.messages.push(data.message);
                    chatState.inputMessage = '';
                    scrollToBottom();
                }
            } catch (e) {
                console.error('Failed to send chat message:', e);
            } finally {
                chatState.sending = false;
            }
        };

        const scrollToBottom = () => {
            Vue.nextTick(() => {
                if (chatContainer.value) {
                    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
                }
            });
        };

        const formatChatTime = (isoStr) => {
            if (!isoStr) return '';
            const d = new Date(isoStr);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        };

        // Analytics State & Logic
        const paceComplianceList = ref([]);
        const healthSummary = reactive({
            fatigueLevel: 'Healthy / Low Fatigue',
            fatigueEmoji: '🟢',
            avgRpe: '-',
            advice: 'Excellent recovery. Body is fully adapting. Athlete is ready for mileage or intensity progression.',
            riskLevel: 'Low Risk',
            feelingStatus: 'Stable',
            riskMessage: 'Runner shows stable wellness responses. Continue current progression.'
        });

        let weeklyVolumeChartInstance = null;

        const initWeeklyVolumeChart = (labels, targetData, completedData) => {
            Vue.nextTick(() => {
                const canvasEl = document.getElementById('weeklyVolumeChart');
                if (!canvasEl) return;
                const ctx = canvasEl.getContext('2d');
                if (weeklyVolumeChartInstance) {
                    weeklyVolumeChartInstance.destroy();
                }
                weeklyVolumeChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Target',
                                data: targetData,
                                backgroundColor: '#475569',
                                borderRadius: 4,
                                barThickness: 12
                            },
                            {
                                label: 'Completed',
                                data: completedData,
                                backgroundColor: '#ccff00',
                                borderRadius: 4,
                                barThickness: 12
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                grid: { color: '#334155' },
                                ticks: { color: '#94a3b8', font: { family: 'monospace', size: 9 } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { family: 'monospace', size: 9 } }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            });
        };

        const calculateAnalytics = (events) => {
            const weeks = {};
            const getMonday = (dStr) => {
                const date = new Date(dStr);
                const day = date.getDay();
                const diff = date.getDate() - day + (day === 0 ? -6 : 1);
                const monday = new Date(date.setDate(diff));
                return monday.toISOString().split('T')[0];
            };

            events.forEach(ev => {
                if (!ev.start) return;
                const monday = getMonday(ev.start);
                if (!weeks[monday]) {
                    weeks[monday] = { target: 0, completed: 0 };
                }
                const dist = parseFloat(ev.extendedProps?.distance || ev.distance || 0);
                const isCustom = ev.extendedProps?.is_custom;
                const isStrava = ev.extendedProps?.is_strava;
                const status = ev.extendedProps?.status;
                const type = ev.extendedProps?.type;

                if (isStrava || status === 'completed') {
                    weeks[monday].completed += dist;
                } else if (!isCustom && type !== 'rest' && status !== 'missed') {
                    weeks[monday].target += dist;
                }
            });

            const sortedMondays = Object.keys(weeks).sort();
            const labels = sortedMondays.map(m => {
                const d = new Date(m);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            });
            const targetData = sortedMondays.map(m => weeks[m].target);
            const completedData = sortedMondays.map(m => weeks[m].completed);

            initWeeklyVolumeChart(labels, targetData, completedData);

            // Compute Pace Compliance
            const complianceList = [];
            events.forEach(ev => {
                const isCompleted = ev.extendedProps?.status === 'completed' || ev.extendedProps?.event_type === 'strava_activity';
                if (!isCompleted) return;

                const type = ev.extendedProps?.type;
                if (!type || type === 'rest' || type === 'strength' || type === 'yoga') return;

                const actualPaceStr = ev.extendedProps?.pace || (ev.extendedProps?.tracking?.strava_link ? '05:00' : null);
                if (!actualPaceStr) return;

                let targetPaceStr = '-';
                if (type === 'easy_run' && trainingProfile.paces?.E) targetPaceStr = trainingProfile.paces.E;
                else if (type === 'tempo' && trainingProfile.paces?.T) targetPaceStr = trainingProfile.paces.T;
                else if (type === 'interval' && trainingProfile.paces?.I) targetPaceStr = trainingProfile.paces.I;
                else if (type === 'long_run' && trainingProfile.paces?.M) targetPaceStr = trainingProfile.paces.M;

                if (targetPaceStr === '-') return;

                const paceToSeconds = (str) => {
                    const p = str.split(':');
                    return p.length === 2 ? (+p[0]) * 60 + (+p[1]) : 0;
                };

                const actSec = paceToSeconds(actualPaceStr);
                const tgtSec = paceToSeconds(targetPaceStr);

                if (!actSec || !tgtSec) return;

                const diff = tgtSec - actSec; // positive means actual is faster
                let complianceStatus = 'On Target';
                let complianceClass = 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                let diffText = 'Perfect pace match';

                if (diff > 15) {
                    complianceStatus = 'Too Fast';
                    complianceClass = 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30';
                    diffText = `${Math.abs(diff)}s faster than target`;
                } else if (diff < -15) {
                    complianceStatus = 'Too Slow';
                    complianceClass = 'bg-red-500/20 text-red-400 border border-red-500/30';
                    diffText = `${Math.abs(diff)}s slower than target`;
                }

                complianceList.push({
                    date: ev.start,
                    dateFormatted: new Date(ev.start).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }),
                    typeName: type.replace('_', ' '),
                    typeClass: type === 'interval' ? 'bg-red-500/20 text-red-400' : (type === 'tempo' ? 'bg-orange-500/20 text-orange-400' : 'bg-green-500/20 text-green-400'),
                    targetPace: targetPaceStr,
                    actualPace: actualPaceStr,
                    complianceStatus,
                    complianceClass,
                    diffText
                });
            });

            paceComplianceList.value = complianceList.slice(0, 5);

            // Compute Fatigue & Health Summary
            const rpes = [];
            const feelings = [];
            events.forEach(ev => {
                const tracking = ev.extendedProps?.tracking;
                if (tracking) {
                    if (tracking.rpe) rpes.push(parseInt(tracking.rpe));
                    if (tracking.feeling) feelings.push(tracking.feeling.toLowerCase());
                }
            });

            const avgRpeVal = rpes.length > 0 ? (rpes.reduce((a, b) => a + b, 0) / rpes.length).toFixed(1) : '-';
            healthSummary.avgRpe = avgRpeVal;

            if (avgRpeVal !== '-') {
                const avg = parseFloat(avgRpeVal);
                if (avg >= 7.5) {
                    healthSummary.fatigueLevel = 'High Fatigue';
                    healthSummary.fatigueEmoji = '🥵';
                    healthSummary.advice = 'Athlete shows signs of high strain. Advise reduced training volume by 20% or add an extra rest day.';
                } else if (avg >= 5.0) {
                    healthSummary.fatigueLevel = 'Moderate Fatigue';
                    healthSummary.fatigueEmoji = '🏃';
                    healthSummary.advice = 'Normal training strain. Athlete is responding well. Maintain current calendar parameters.';
                } else {
                    healthSummary.fatigueLevel = 'Healthy / Low Fatigue';
                    healthSummary.fatigueEmoji = '🟢';
                    healthSummary.advice = 'Excellent recovery. Body is fully adapting. Athlete is ready for mileage or intensity progression.';
                }
            }

            if (feelings.length > 0) {
                const lastFeeling = feelings[feelings.length - 1];
                healthSummary.feelingStatus = lastFeeling;
                if (lastFeeling === 'terrible' || lastFeeling === 'weak' || (rpes.length > 0 && rpes[rpes.length - 1] >= 9)) {
                    healthSummary.riskLevel = 'HIGH RISK';
                    healthSummary.riskMessage = 'WARNING: Last run logged high exertion or poor wellness. High risk of overtraining.';
                } else {
                    healthSummary.riskLevel = 'Low Risk';
                    healthSummary.riskMessage = 'Wellness metrics stable. Runner shows normal adaptive responses.';
                }
            }
        };

        const fetchAnalyticsData = async () => {
            try {
                const res = await fetch(`{{ route("coach.athletes.events", $enrollment->id) }}`);
                const data = await res.json();
                calculateAnalytics(data);
            } catch (e) {
                console.error('Error fetching calendar events for analytics:', e);
            }
        };

        watch(profileTab, (newTab) => {
            if (newTab === 'analytics') {
                fetchAnalyticsData();
            }
        });

        watch(selectedSession, (ev) => {
            resetStravaState();
            if (!ev || !ev.extendedProps) {
                return;
            }

            const props = ev.extendedProps || {};
            let id = null;
            if (props.is_strava && props.strava_activity_id) {
                id = props.strava_activity_id;
            } else if (props.tracking && props.tracking.strava_link) {
                id = extractStravaActivityId(props.tracking.strava_link);
            }
            if (id) {
                loadStravaForActivity(id);
            }
        });

        const handleEventDrop = async (info) => {
            if (!confirm(`Reschedule ${info.event.title} ke ${info.event.startStr}?`)) {
                info.revert();
                return;
            }

            const props = info.event.extendedProps;
            const payload = {
                type: props.is_custom ? 'custom_workout' : 'program_session',
                new_date: info.event.startStr,
            };

            if (payload.type === 'custom_workout') {
                payload.workout_id = props.id;
            } else {
                payload.session_day = props.session_day;
            }

            try {
                const res = await fetch(`${coachUrl}/athletes/${enrollmentId}/reschedule`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    if (data.message && data.message.includes('ditukar')) {
                        alert(data.message);
                    }
                    calendar.refetchEvents();
                    fetchAnalyticsData();
                } else {
                    alert(data.message || 'Failed to reschedule');
                    info.revert();
                }
            } catch (e) {
                console.error(e);
                alert('Connection error');
                info.revert();
            }
        };

        onMounted(() => {
            const el = document.getElementById('calendar');
            const isMobile = window.innerWidth < 768;
            const initialView = isMobile ? 'listWeek' : 'dayGridMonth';
            const headerToolbar = isMobile 
                ? { left: 'prev,next', center: 'title', right: '' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' };

            calendar = new FullCalendar.Calendar(el, {
                initialView: initialView,
                headerToolbar: headerToolbar,
                events: '{{ route("coach.athletes.events", $enrollment->id) }}',
                locale: 'id',
                firstDay: 1,
                editable: true,
                eventDrop: handleEventDrop,
                fixedMirrorParent: document.body,
                eventClassNames: (arg) => {
                    const cls = [];
                    const props = arg.event.extendedProps || {};
                    const type = props.type || (props.session ? props.session.type : 'run');
                    if (type) cls.push('workout-' + type);
                    return cls;
                },
                eventContent: (arg) => {
                    const props = arg.event.extendedProps || {};
                    const isStrava = props.type === 'strava_activity' || props.event_type === 'strava_activity' || props.is_strava;
                    
                    const container = document.createElement('div');
                    container.style.display = 'flex';
                    container.style.alignItems = 'center';
                    container.style.gap = '4px';
                    container.style.overflow = 'hidden';
                    container.style.textOverflow = 'ellipsis';
                    container.style.whiteSpace = 'nowrap';
                    container.style.width = '100%';

                    if (!arg.event.allDay && arg.timeText) {
                        const timeEl = document.createElement('span');
                        timeEl.className = 'fc-event-time';
                        timeEl.style.fontWeight = 'bold';
                        timeEl.style.marginRight = '2px';
                        timeEl.style.flexShrink = '0';
                        timeEl.innerText = arg.timeText;
                        container.appendChild(timeEl);
                    }

                    if (isStrava) {
                        const svgSpan = document.createElement('span');
                        svgSpan.style.display = 'inline-flex';
                        svgSpan.style.alignItems = 'center';
                        svgSpan.style.flexShrink = '0';
                        svgSpan.innerHTML = `
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="#FC4C02">
                                <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-3.756l3.52 6.948h4.636L11.529 0 4 14.808h4.637l2.871-5.62z"/>
                            </svg>
                        `;
                        container.appendChild(svgSpan);
                    }

                    const titleEl = document.createElement('span');
                    titleEl.className = 'fc-event-title';
                    titleEl.innerText = arg.event.title;
                    container.appendChild(titleEl);

                    return { domNodes: [container] };
                },
                dateClick: (info) => {
                    openForm(info.dateStr);
                },
                eventClick: (info) => {
                    selectedSession.value = info.event;
                    // Pre-fill form
                    const tracking = info.event.extendedProps.tracking;
                    if (tracking) {
                        feedbackForm.coach_rating = tracking.coach_rating || 0;
                        feedbackForm.coach_feedback = tracking.coach_feedback || '';
                    } else {
                        feedbackForm.coach_rating = 0;
                        feedbackForm.coach_feedback = '';
                    }

                    // Scroll to detail on mobile
                    if (window.innerWidth < 1024) {
                        setTimeout(() => {
                            const detailEl = document.querySelector('.lg\\:col-span-1');
                            if (detailEl) detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 100);
                    }
                },
                height: 'auto',
                listDayFormat: { month: 'short', day: 'numeric', weekday: 'short' },
                listDaySideFormat: false
            });
            calendar.render();

            setInterval(() => {
                try {
                    if (calendar) {
                        calendar.refetchEvents();
                    }
                } catch (e) {}
            }, 60_000);
        });

        // Weekly Report Actions
        const nudgeStrava = async () => {
            if (confirm("Kirim notifikasi in-app ke atlet untuk menghubungkan akun Strava?")) {
                try {
                    const res = await fetch(`{{ route('coach.athletes.nudge-strava', $enrollment->id) }}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert("Gagal mengirim notifikasi.");
                    }
                } catch (e) {
                    alert("Terjadi kesalahan.");
                }
            }
        };

        const syncStrava = async () => {
            loading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.sync-strava', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    if (calendar) {
                        calendar.refetchEvents();
                    }
                } else {
                    alert(data.message || "Gagal sinkronisasi.");
                }
            } catch (e) {
                alert("Terjadi kesalahan saat sinkronisasi.");
            } finally {
                loading.value = false;
            }
        };

        const generateWeeklyReport = async () => {
            weeklyReportLoading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.generate-weekly-report', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    weeklyReportForm.report_text = data.draft;
                } else {
                    alert(data.message || "Gagal menghasilkan draf laporan.");
                }
            } catch (e) {
                alert("Terjadi kesalahan saat menghubungi OpenAI.");
            } finally {
                weeklyReportLoading.value = false;
            }
        };

        const publishWeeklyReport = async () => {
            if (!weeklyReportForm.report_text.trim()) {
                alert("Konten laporan tidak boleh kosong.");
                return;
            }
            weeklyReportPublishing.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.store-weekly-report', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': csrf, 
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(weeklyReportForm)
                });
                const data = await res.json();
                if (data.success) {
                    // Update lists
                    const index = weeklyReportsList.value.findIndex(r => r.week_number === data.report.week_number);
                    if (index !== -1) {
                        weeklyReportsList.value[index] = data.report;
                    } else {
                        weeklyReportsList.value.unshift(data.report);
                    }
                    // increment suggested week
                    weeklyReportForm.week_number = Math.max(...weeklyReportsList.value.map(r => r.week_number)) + 1;
                    weeklyReportForm.report_text = '';
                    alert(data.message);
                } else {
                    alert(data.message || "Gagal menyimpan laporan.");
                }
            } catch (e) {
                alert("Terjadi kesalahan saat menyimpan laporan.");
            } finally {
                weeklyReportPublishing.value = false;
            }
        };

        const selectWeeklyReport = (rep) => {
            weeklyReportForm.week_number = rep.week_number;
            weeklyReportForm.report_text = rep.report_text;
        };

        const formatDateShort = (dateStr) => {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        };

        const exportCalendar = async (type) => {
            try {
                const response = await fetch('{{ route("coach.athletes.events", $enrollment->id) }}');
                const list = await response.json();
                if (!list || list.length === 0) {
                    alert('Tidak ada data program aktif untuk diekspor.');
                    return;
                }

                const programTitle = @json($enrollment->program->title);
                const runnerName = @json($enrollment->runner->name);
                const sortedPlans = [...list].sort((a, b) => new Date(a.start) - new Date(b.start));

                const opt = {
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#0b1220',
                    scale: 2,
                    logging: false
                };

                const getRowHtml = (plan, globalIdx) => {
                    const dateObj = new Date(plan.start);
                    const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                    const dayStr = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                    
                    const props = plan.extendedProps || {};
                    const desc = props.description || plan.title;
                    const title = desc ? desc.split('\n')[0].replace(/^✅\s*|^❌\s*/, '') : 'Sesi Latihan';
                    const wType = props.type || 'Workout';
                    const target = props.distance ? `${props.distance} km` : (props.duration || '-');
                    
                    let statusTextStr = 'Pending';
                    let statusBg = '#33415515';
                    let statusColor = '#94a3b8';
                    if (props.status === 'completed' || props.status === 'imported') {
                        statusTextStr = 'Selesai';
                        statusBg = '#10b98115';
                        statusColor = '#10b981';
                    } else if (props.status === 'started') {
                        statusTextStr = 'Mulai';
                        statusBg = '#eab30815';
                        statusColor = '#eab308';
                    } else if (props.status === 'missed') {
                        statusTextStr = 'Missed';
                        statusBg = '#ef444415';
                        statusColor = '#ef4444';
                    }

                    let typeColor = '#3b82f6';
                    if (wType.toLowerCase().includes('easy') || wType.toLowerCase().includes('recovery')) typeColor = '#10b981';
                    else if (wType.toLowerCase().includes('tempo') || wType.toLowerCase().includes('threshold')) typeColor = '#f97316';
                    else if (wType.toLowerCase().includes('interval') || wType.toLowerCase().includes('speed')) typeColor = '#ef4444';
                    else if (wType.toLowerCase().includes('long')) typeColor = '#6366f1';
                    else if (wType.toLowerCase().includes('rest')) typeColor = '#64748b';

                    return `
                        <tr style="border-bottom: 1px solid #1e293b;">
                            <td style="padding: 12px 8px; font-size: 12px; color: #64748b; font-family: monospace;">${globalIdx + 1}</td>
                            <td style="padding: 12px 8px; font-size: 12px; font-weight: bold; color: #ccff00;">${dayStr}</td>
                            <td style="padding: 12px 8px; font-size: 12px; color: #cbd5e1;">${dateStr}</td>
                            <td style="padding: 12px 8px; font-size: 12px; font-weight: bold; color: #ffffff;">
                                ${title}
                                <span style="display: inline-block; font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; background-color: ${typeColor}15; color: ${typeColor}; border: 1px solid ${typeColor}30; margin-left: 8px;">
                                    ${wType}
                                </span>
                            </td>
                            <td style="padding: 12px 8px; font-size: 12px; font-weight: bold; color: #ccff00; text-align: center;">${target}</td>
                            <td style="padding: 12px 8px; text-align: center;">
                                <span style="display: inline-block; font-size: 10px; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background-color: ${statusBg}; color: ${statusColor}; border: 1px solid ${statusColor}30;">
                                    ${statusTextStr}
                                </span>
                            </td>
                        </tr>
                    `;
                };

                const getTableHeaderHtml = () => `
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #334155; color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                <th style="padding: 10px 8px; width: 40px;">No</th>
                                <th style="padding: 10px 8px; width: 100px;">Hari</th>
                                <th style="padding: 10px 8px; width: 110px;">Tanggal</th>
                                <th style="padding: 10px 8px;">Detail Latihan</th>
                                <th style="padding: 10px 8px; width: 90px; text-align: center;">Target</th>
                                <th style="padding: 10px 8px; width: 90px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                if (type === 'image') {
                    // Image option renders everything in a single long scrollable canvas
                    const container = document.createElement('div');
                    container.style.position = 'absolute';
                    container.style.left = '-9999px';
                    container.style.top = '-9999px';
                    container.style.width = '800px';
                    container.style.padding = '40px';
                    container.style.backgroundColor = '#0b1220';
                    container.style.color = '#e2e8f0';
                    container.style.fontFamily = 'system-ui, -apple-system, sans-serif';

                    const headerHtml = `
                        <div style="border-bottom: 2px solid #ccff00; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <h1 style="color: #ffffff; font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; font-style: italic;">Ruang Lari</h1>
                                <p style="color: #94a3b8; font-size: 11px; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 2px;">Rencana Program Latihan (Coach Monitor)</p>
                            </div>
                            <div style="text-align: right;">
                                <h3 style="color: #ccff00; font-size: 14px; font-weight: 800; margin: 0;">${programTitle}</h3>
                                <p style="color: #94a3b8; font-size: 11px; margin: 3px 0 0 0;">Runner: ${runnerName}</p>
                            </div>
                        </div>
                    `;

                    let tableRows = '';
                    sortedPlans.forEach((plan, idx) => {
                        tableRows += getRowHtml(plan, idx);
                    });

                    container.innerHTML = headerHtml + getTableHeaderHtml() + tableRows + '</tbody></table>';
                    document.body.appendChild(container);

                    html2canvas(container, opt).then(canvas => {
                        document.body.removeChild(container);
                        const imgData = canvas.toDataURL('image/png');
                        const link = document.createElement('a');
                        link.download = `kalender-latihan-${programTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.png`;
                        link.href = imgData;
                        link.click();
                    }).catch(err => {
                        if (document.body.contains(container)) document.body.removeChild(container);
                        console.error('Export image failed:', err);
                        alert('Gagal mengekspor gambar.');
                    });

                } else if (type === 'pdf') {
                    // PDF option renders page-by-page (15 rows per page) to prevent row splitting across pages
                    const rowsPerPage = 15;
                    const totalPages = Math.ceil(sortedPlans.length / rowsPerPage);
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    const imgWidth = 190;

                    const renderPage = async (pageIdx) => {
                        const startIdx = pageIdx * rowsPerPage;
                        const endIdx = Math.min(startIdx + rowsPerPage, sortedPlans.length);
                        const pagePlans = sortedPlans.slice(startIdx, endIdx);

                        const container = document.createElement('div');
                        container.style.position = 'absolute';
                        container.style.left = '-9999px';
                        container.style.top = '-9999px';
                        container.style.width = '800px';
                        container.style.height = '1120px'; // Fixed A4 proportional height
                        container.style.padding = '40px';
                        container.style.backgroundColor = '#0b1220';
                        container.style.color = '#e2e8f0';
                        container.style.fontFamily = 'system-ui, -apple-system, sans-serif';
                        container.style.boxSizing = 'border-box';
                        container.style.display = 'flex';
                        container.style.flexDirection = 'column';

                        const headerHtml = `
                            <div style="border-bottom: 2px solid #ccff00; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
                                <div>
                                    <h1 style="color: #ffffff; font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; font-style: italic;">Ruang Lari</h1>
                                    <p style="color: #94a3b8; font-size: 11px; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 2px;">Rencana Program Latihan (Coach Monitor)</p>
                                </div>
                                <div style="text-align: right;">
                                    <h3 style="color: #ccff00; font-size: 14px; font-weight: 800; margin: 0;">${programTitle}</h3>
                                    <p style="color: #94a3b8; font-size: 11px; margin: 3px 0 0 0;">Halaman ${pageIdx + 1} dari ${totalPages}</p>
                                </div>
                            </div>
                        `;

                        let tableRows = '';
                        pagePlans.forEach((plan, localIdx) => {
                            tableRows += getRowHtml(plan, startIdx + localIdx);
                        });

                        // Add content
                        container.innerHTML = headerHtml + getTableHeaderHtml() + tableRows + '</tbody></table>';
                        
                        // Add footer pushing it to bottom of A4
                        const footerDiv = document.createElement('div');
                        footerDiv.style.marginTop = 'auto';
                        footerDiv.style.paddingTop = '15px';
                        footerDiv.style.borderTop = '1px solid #1e293b';
                        footerDiv.style.display = 'flex';
                        footerDiv.style.justifyContent = 'space-between';
                        footerDiv.style.fontSize = '10px';
                        footerDiv.style.color = '#64748b';
                        footerDiv.innerHTML = `
                            <span>Runner: ${runnerName}</span>
                            <span>Generated via Ruang Lari</span>
                        `;
                        container.appendChild(footerDiv);

                        document.body.appendChild(container);
                        const canvas = await html2canvas(container, opt);
                        document.body.removeChild(container);
                        return canvas.toDataURL('image/png');
                    };

                    // Sequential rendering of pages
                    (async () => {
                        for (let i = 0; i < totalPages; i++) {
                            if (i > 0) pdf.addPage();
                            const imgData = await renderPage(i);
                            pdf.addImage(imgData, 'PNG', 10, 8.5, imgWidth, 280);
                        }
                        pdf.save(`kalender-latihan-${programTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.pdf`);
                    })().catch(err => {
                        console.error('PDF generation failed:', err);
                        alert('Gagal mengekspor PDF.');
                    });
                }

            } catch (e) {
                console.error(e);
                alert('Gagal mengambil data dari server untuk ekspor.');
            }
        };

        return { 
            trainingProfile, profileTab, formatPace,
            showWeeklyTargetModal, weeklyTargetForm, weeklyTargetLoading, updateWeeklyTarget,
            selectedSession, statusClass, formatDate, feedbackForm, saveFeedback, loading, getPaceInfo, 
            exportCalendar,
            stravaDetailsLoading, stravaDetailsError, stravaMetrics, stravaSplits, stravaLaps, stravaStreams, formatSeconds,
            stravaAiAnalysis, stravaAiAnalysisLoading, stravaWorkoutClassification,
            showRaceModal, raceForm, openRaceForm, saveRace, ruangLariEvents, loadingEvents, onSelectRuangLariEvent, fetchRuangLariEvents, eventSearchQuery, showEventDropdown, filteredEvents, selectRuangLariEvent, hideEventDropdown,
            showFormModal, form, openForm, saveCustomWorkout, addStep, removeStep, moveStep, calculateTotalDistance, deleteCustomWorkout,
            // Advanced Builder
            builderVisible, builderForm, openBuilder, submitBuilder, builderSummary, builderTotalDistance, strengthOptions, addStrengthExercise, removeStrengthExercise,
            showStravaGraphModal,
            // New Features
            predictor, predictedTime,
            chatContainer, chatState, toggleChatDrawer, loadChatMessages, sendChatMessage, formatChatTime,
            paceComplianceList, healthSummary,
            // Weekly Reports
            nudgeStrava, syncStrava, generateWeeklyReport, publishWeeklyReport, selectWeeklyReport, formatDateShort,
            // Reschedule Program
            showRescheduleModal, rescheduleForm, rescheduleLoading, rescheduleError,
            openRescheduleModal, submitReschedule, previewRescheduleEndDate,
            // Send Program Reminder
            showReminderModal, reminderForm, reminderLoading, reminderError, reminderSuccess,
            openReminderModal, submitReminder,
            weeklyReportLoading, weeklyReportPublishing, weeklyReportsList, weeklyReportForm
        };
    }
}).mount('#coach-monitor-app');
</script>
@endpush
