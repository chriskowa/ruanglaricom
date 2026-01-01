@extends('layouts.coach')

@section('title', 'Monitor Athlete')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
<style>
.glass-panel{background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.05)}
.fc .fc-toolbar-title{font-size: medium;font-weight:800;color:#e2e8f0}
.fc .fc-button{background:#1e293b;border-color:#334155;color:#cbd5e1}
.fc .fc-button:hover{color:#ccff00;border-color:#ccff00}
.fc-event{border:none;border-radius:4px;cursor:pointer;}
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
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center">
                    <div class="text-[10px] text-slate-400 uppercase">VDOT</div>
                    <div class="text-xl font-black text-white">{{ $trainingProfile['vdot'] ?? '-' }}</div>
                </div>
                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700 text-center">
                    <div class="text-[10px] text-slate-400 uppercase">Start Date</div>
                    <div class="text-xl font-black text-white">{{ $enrollment->start_date ? $enrollment->start_date->format('d M') : '-' }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar Column -->
            <div class="lg:col-span-2">
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
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
const { createApp, ref, reactive, onMounted } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const selectedSession = ref(null);
        const loading = ref(false);
        const trainingProfile = @json($trainingProfile);
        const feedbackForm = reactive({
            coach_rating: 0,
            coach_feedback: ''
        });

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
                    const calendarEl = document.getElementById('calendar');
                    // We need access to calendar instance. 
                    // Since it's local in onMounted, we might need to reload or expose it.
                    // Easiest is reload for now as calendar instance isn't globally exposed
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
            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
                events: '{{ route("coach.athletes.events", $enrollment->id) }}',
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

        return { selectedSession, statusClass, formatDate, feedbackForm, saveFeedback, loading, getPaceInfo, showRaceModal, raceForm, openRaceForm, saveRace };
    }
}).mount('#coach-monitor-app');
</script>
@endpush