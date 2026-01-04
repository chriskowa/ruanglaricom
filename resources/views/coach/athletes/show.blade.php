@extends('layouts.coach')
@php
    $withSidebar = true;
@endphp

@section('title', 'Monitor Athlete')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
<style>
.glass-panel{background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.05)}
.fc .fc-toolbar-title{font-size: medium;font-weight:800;color:#e2e8f0}
.fc .fc-button{background:#1e293b;border-color:#334155;color:#cbd5e1}
.fc .fc-button:hover{color:#ccff00;border-color:#ccff00}
.fc-event{border:none;border-radius:4px;cursor:pointer;}
/* Calendar Dark Mode Overrides */
.fc-theme-standard .fc-scrollgrid { border-color: #334155; }
.fc-theme-standard td, .fc-theme-standard th { border-color: #334155; }
.fc .fc-daygrid-day-number { color: #94a3b8; text-decoration: none; }
.fc .fc-col-header-cell-cushion { color: #94a3b8; text-decoration: none; }
.fc-day-today { background-color: rgba(204, 255, 0, 0.05) !important; }
.fc-daygrid-day-frame { min-height: 100px; }
.fc .fc-daygrid-day.fc-day-other { background-color: rgba(0,0,0,0.2); }
</style>
@endpush

@section('content')
<main id="coach-monitor-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" v-cloak>
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
            <div>
                <a href="{{ route('coach.athletes.index') }}" class="text-slate-400 hover:text-white text-xs mb-2 flex items-center gap-1">
                    ← Back to Athletes
                </a>
                <h1 class="text-3xl font-black text-white italic tracking-tighter">{{ $enrollment->runner->name }}</h1>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">{{ $enrollment->program->title }}</p>
            </div>
            
            <!-- Runner Stats Summary -->
            <div class="flex gap-4">
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center min-w-[80px]">
                    <div class="text-[10px] text-slate-400 uppercase">Gender</div>
                    <div class="text-lg font-black text-white capitalize">{{ $enrollment->runner->gender ?? '-' }}</div>
                </div>
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center min-w-[80px]">
                    <div class="text-[10px] text-slate-400 uppercase">Age</div>
                    <div class="text-lg font-black text-white">{{ $enrollment->runner->date_of_birth ? \Carbon\Carbon::parse($enrollment->runner->date_of_birth)->age : '-' }}</div>
                </div>
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center min-w-[80px]">
                    <div class="text-[10px] text-slate-400 uppercase">Weight</div>
                    <div class="text-lg font-black text-white">{{ $enrollment->runner->weight ? $enrollment->runner->weight.' kg' : '-' }}</div>
                </div>
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center min-w-[80px]">
                    <div class="text-[10px] text-slate-400 uppercase">Height</div>
                    <div class="text-lg font-black text-white">{{ $enrollment->runner->height ? $enrollment->runner->height.' cm' : '-' }}</div>
                </div>
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center min-w-[80px]">
                    <div class="text-[10px] text-slate-400 uppercase">VDOT</div>
                    <div class="text-lg font-black text-neon">{{ $trainingProfile['vdot'] ?? '-' }}</div>
                </div>
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center min-w-[80px]">
                    <div class="text-[10px] text-slate-400 uppercase">Weekly Target (km)</div>
                    <div class="text-lg font-black text-white">{{ isset($trainingProfile['weekly_km_target']) && $trainingProfile['weekly_km_target'] !== null ? number_format($trainingProfile['weekly_km_target'], 1) : '-' }}</div>
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
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    Training Profile
                                </h3>
                                <p class="text-xs text-slate-400">Based on Athlete's Personal Best (PB)</p>
                            </div>
                        </div>

                        <!-- VDOT Score -->
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center mb-6">
                            <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">VDOT Score</div>
                            <div class="text-4xl font-black text-white">@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                            <div class="text-[10px] text-slate-500 mt-1">VO2Max Approx: @{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
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
                    </div>
                </div>

                <div class="glass-panel rounded-2xl p-4 md:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-white font-bold">Training Calendar</h3>
                        <button @click="openRaceForm" class="px-3 py-2 rounded-xl bg-yellow-500 text-black font-black hover:bg-yellow-400 transition shadow-lg shadow-yellow-500/20 text-xs flex items-center gap-1">
                            <span>🏆</span> Add Race
                        </button>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Detail & Feedback Column -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-2xl p-6 sticky top-24">
                    <div v-if="selectedSession">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="text-xs text-slate-400 uppercase font-bold">@{{ formatDate(selectedSession.start) }}</div>
                                <h3 class="text-xl font-black text-white">@{{ selectedSession.title }}</h3>
                            </div>
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase" 
                                :class="statusClass(selectedSession.extendedProps.status)">
                                @{{ selectedSession.extendedProps.status }}
                            </span>
                        </div>

                        <!-- Edit Button -->
                        <div class="mb-4">
                            <button @click="openForm(null, selectedSession)" class="w-full text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition border border-slate-700">
                                <i class="fa-solid fa-pen"></i> @{{ selectedSession.extendedProps.is_custom ? 'Edit Custom Workout' : 'Customize / Edit Workout' }}
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
                                <span class="text-slate-500">Description:</span>
                                <p class="mt-1 p-2 bg-slate-800 rounded text-xs">@{{ selectedSession.extendedProps.description }}</p>
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
                        <div v-else class="mb-6 border-t border-slate-700 pt-4 text-center text-slate-500 text-xs italic">
                            Athlete has not completed this session yet.
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
                    <h3 class="text-white font-bold text-xl italic">Add Workout</h3>
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
                        <input v-if="builderForm.warmup.by==='distance'" type="number" step="0.1" v-model.number="builderForm.warmup.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="km">
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
                        <input v-if="builderForm.cooldown.by==='distance'" type="number" step="0.1" v-model.number="builderForm.cooldown.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="km">
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
                        <input v-if="builderForm.main.by==='distance'" type="number" step="0.1" v-model.number="builderForm.main.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Distance (km)">
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
                        <input v-if="builderForm.main.by==='distance'" type="number" step="0.1" v-model.number="builderForm.main.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Total Distance (km)">
                        <input v-else type="text" v-model="builderForm.main.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:30:00">
                        <input type="text" v-model="builderForm.main.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                            <input type="checkbox" v-model="builderForm.longRun.fastFinish.enabled" class="rounded bg-slate-900 border-slate-700 text-neon">
                            Fast Finish
                        </label>
                        <div class="grid grid-cols-2 gap-2" v-if="builderForm.longRun.fastFinish.enabled">
                            <input type="number" step="0.1" v-model.number="builderForm.longRun.fastFinish.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="km">
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
                        <input v-if="builderForm.tempo.by==='distance'" type="number" step="0.1" v-model.number="builderForm.tempo.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Distance (km)">
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
                        <input v-if="builderForm.interval.by==='distance'" type="number" step="0.1" v-model.number="builderForm.interval.repDistanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Rep km">
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

    <!-- Race Modal -->
        <div v-if="showRaceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 relative">
                <button @click="showRaceModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
                <h3 class="text-xl font-black text-white italic mb-6">🏆 Add Race Event</h3>
                
                <form @submit.prevent="saveRace" class="space-y-4">
                    <!-- RuangLari Import -->
                    <div class="mb-4 bg-slate-800/50 p-3 rounded-xl border border-slate-700 relative">
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Import from RuangLari</label>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="eventSearchQuery"
                                @focus="showEventDropdown = true"
                                placeholder="Type to search event..."
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
                                    <div class="text-sm font-bold text-white">@{{ event.title }}</div>
                                    <div class="text-xs text-slate-400 flex justify-between mt-1">
                                        <span>📅 @{{ event.date }}</span>
                                        <span>📍 @{{ event.location }}</span>
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
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Event Name</label>
                        <input v-model="raceForm.name" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-yellow-500 outline-none" placeholder="e.g. Jakarta Marathon" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Date</label>
                            <input v-model="raceForm.date" type="date" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-yellow-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Distance</label>
                            <select v-model="raceForm.distance" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-yellow-500 outline-none">
                                <option value="5k">5K</option>
                                <option value="10k">10K</option>
                                <option value="21k">Half Marathon</option>
                                <option value="42k">Full Marathon</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Goal Time (Optional)</label>
                        <input v-model="raceForm.goal_time" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-yellow-500 outline-none" placeholder="hh:mm:ss">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Notes</label>
                        <textarea v-model="raceForm.notes" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-yellow-500 outline-none" placeholder="Target pace strategy, etc."></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="loading" class="w-full py-3 rounded-xl bg-yellow-500 text-black font-black hover:bg-yellow-400 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            @{{ loading ? 'Saving...' : 'Add to Calendar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
@include('layouts.components.advanced-builder-utils')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
const { createApp, ref, reactive, onMounted, watch, computed } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let calendar = null;
        const selectedSession = ref(null);
        const loading = ref(false);
        const trainingProfile = @json($trainingProfile);
        const profileTab = ref('training');
        const feedbackForm = reactive({
            coach_rating: 0,
            coach_feedback: ''
        });


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
            warmup: { enabled: false, by: 'distance', distanceKm: 0, duration: '' },
            cooldown: { enabled: false, by: 'distance', distanceKm: 0, duration: '' },
            main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
            longRun: { fastFinish: { enabled: false, distanceKm: 0, pace: '' } },
            tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
            interval: { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' },
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
                warmup: { enabled: false, by: 'distance', distanceKm: 0, duration: '', pace: '' },
                cooldown: { enabled: false, by: 'distance', distanceKm: 0, duration: '', pace: '' },
                main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
                longRun: { fastFinish: { enabled: false, distanceKm: 0, pace: '' } },
                tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
                interval: { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' },
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
                                builderForm.main.distanceKm = form.distance;
                            } else if (form.duration) {
                                builderForm.main.by = 'time';
                                builderForm.main.duration = form.duration;
                            }
                        } else if (targetType === 'tempo') {
                            if (form.distance) {
                                builderForm.tempo.by = 'distance';
                                builderForm.tempo.distanceKm = form.distance;
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
        
        const filteredEvents = computed(() => {
            if (!eventSearchQuery.value) return ruangLariEvents.value;
            const query = eventSearchQuery.value.toLowerCase();
            return ruangLariEvents.value.filter(e => 
                e.title.toLowerCase().includes(query) || 
                (e.location && e.location.toLowerCase().includes(query))
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
            eventSearchQuery.value = event.title;
            showEventDropdown.value = false;
            onSelectRuangLariEvent(event);
        };

        const onSelectRuangLariEvent = (event) => {
            if (!event) return;
            raceForm.name = event.title;
            // Parse date mm/dd/yyyy to yyyy-mm-dd
            if (event.date) {
                const parts = event.date.split('/');
                if (parts.length === 3) {
                    const mm = parts[0].padStart(2, '0');
                    const dd = parts[1].padStart(2, '0');
                    const yyyy = parts[2];
                    raceForm.date = `${yyyy}-${mm}-${dd}`;
                }
            }
            // Guess distance
            const titleLower = event.title.toLowerCase();
            if (titleLower.includes('marathon') && !titleLower.includes('half')) {
                raceForm.distance = '42k';
            } else if (titleLower.includes('half') || titleLower.includes('hm')) {
                raceForm.distance = '21k';
            } else if (titleLower.includes('10k')) {
                raceForm.distance = '10k';
            } else if (titleLower.includes('5k')) {
                raceForm.distance = '5k';
            }
            
            raceForm.notes = `Event Link: ${event.link}\nLocation: ${event.location}`;
        };

        // Watch modal open to fetch events
        watch(showRaceModal, (val) => {
            if (val) {
                fetchRuangLariEvents();
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

        const formatDate = (d) => {
            return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
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

        onMounted(() => {
            const el = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
                events: '{{ route("coach.athletes.events", $enrollment->id) }}',
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
                },
                height: 'auto'
            });
            calendar.render();
        });

        return { 
            trainingProfile, profileTab, formatPace,
            selectedSession, statusClass, formatDate, feedbackForm, saveFeedback, loading, getPaceInfo, 
            showRaceModal, raceForm, openRaceForm, saveRace, ruangLariEvents, loadingEvents, onSelectRuangLariEvent, fetchRuangLariEvents, eventSearchQuery, showEventDropdown, filteredEvents, selectRuangLariEvent,
            showFormModal, form, openForm, saveCustomWorkout, addStep, removeStep, moveStep, calculateTotalDistance, deleteCustomWorkout,
            // Advanced Builder
            builderVisible, builderForm, openBuilder, submitBuilder, builderSummary, builderTotalDistance, strengthOptions, addStrengthExercise, removeStrengthExercise
        };
    }
}).mount('#coach-monitor-app');
</script>
@endpush
