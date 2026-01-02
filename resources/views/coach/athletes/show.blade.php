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
                    
                    <!-- Workout Builder -->
                    <div class="border-t border-slate-700 pt-4 mt-4">
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Workout Builder</label>
                        
                        <div class="space-y-2 mb-3">
                            <div v-for="(step, index) in form.workout_structure" :key="index" class="flex flex-col gap-2 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
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
                                    <select v-model="step.duration_type" @change="calculateTotalDistance" class="bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1 outline-none focus:border-neon">
                                        <option value="distance">Distance</option>
                                        <option value="time">Time</option>
                                    </select>
                                    <div class="flex gap-1 col-span-2">
                                        <input type="number" step="0.01" v-model="step.value" @change="calculateTotalDistance" class="w-full bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1 outline-none focus:border-neon" placeholder="Value">
                                        <select v-model="step.unit" @change="calculateTotalDistance" class="w-20 bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1 outline-none focus:border-neon">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                            <option value="min">min</option>
                                            <option value="sec">sec</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="text" v-model="step.notes" placeholder="Notes (e.g. @ 5:00 pace)" class="w-full bg-slate-900 border border-slate-700 rounded text-xs text-white px-2 py-1 outline-none focus:border-neon">
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

        <!-- Race Modal -->
        <div v-if="showRaceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 relative">
                <button @click="showRaceModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
                <h3 class="text-xl font-black text-white italic mb-6">🏆 Add Race Event</h3>
                
                <form @submit.prevent="saveRace" class="space-y-4">
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
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
const { createApp, ref, reactive, onMounted } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let calendar = null;
        const selectedSession = ref(null);
        const loading = ref(false);
        const trainingProfile = @json($trainingProfile);
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
            workout_structure: [] 
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

        const openForm = (dateStr) => {
            form.workout_id = '';
            form.workout_date = dateStr;
            form.type = 'run';
            form.difficulty = 'moderate';
            form.distance = '';
            form.duration = '';
            form.description = '';
            form.workout_structure = [];
            showFormModal.value = true;
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
                    workout_structure: form.workout_structure.length > 0 ? form.workout_structure : null,
                };
                
                const res = await fetch(`{{ route('coach.athletes.workout.store', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showFormModal.value = false;
                    alert('Workout added successfully');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to add workout');
                }
            } catch (e) {
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
            selectedSession, statusClass, formatDate, feedbackForm, saveFeedback, loading, getPaceInfo, 
            showRaceModal, raceForm, openRaceForm, saveRace,
            showFormModal, form, openForm, saveCustomWorkout, addStep, removeStep, moveStep, calculateTotalDistance
        };
    }
}).mount('#coach-monitor-app');
</script>
@endpush