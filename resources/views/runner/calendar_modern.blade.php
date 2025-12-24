@extends('layouts.pacerhub')

@section('title', 'Runner Calendar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
<style>
.glass-panel{background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.05)}
.fc .fc-toolbar-title{font-weight:800;color:#e2e8f0}
.fc .fc-button{background:#1e293b;border-color:#334155;color:#cbd5e1}
.fc .fc-button:hover{color:#ccff00;border-color:#ccff00}
.fc-event{background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:8px;padding:2px 6px}
.fc-event.difficulty-easy{border-left:4px solid #4CAF50}
.fc-event.difficulty-moderate{border-left:4px solid #FF9800}
.fc-event.difficulty-hard{border-left:4px solid #F44336}
</style>
@endpush

@section('content')
<main id="runner-calendar-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" v-cloak>
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">Training</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Runner Calendar</h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('programs.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition text-sm font-bold">Browse Programs</a>
                <button @click="openFormForToday" class="px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20 text-sm">Add Custom Workout</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="glass-panel rounded-2xl p-6">
                    <div id="calendar"></div>
                </div>

                <div class="glass-panel rounded-2xl p-6">
                    <div class="flex items-end justify-between mb-4">
                        <div>
                            <h3 class="text-white font-bold text-lg">Plan List</h3>
                            <p class="text-xs text-slate-500">Workout plans from your active programs</p>
                        </div>
                        <div class="flex gap-2">
                            <button :class="[filter==='unfinished'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold" @click="setFilter('unfinished')">Unfinished</button>
                            <button :class="[filter==='finished'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold" @click="setFilter('finished')">Finished</button>
                            <button :class="[filter==='all'?'bg-neon text-dark':'bg-slate-800 text-slate-300']" class="px-3 py-1 rounded-lg border border-slate-700 text-xs font-bold" @click="setFilter('all')">All</button>
                        </div>
                    </div>

                    <div v-if="plansLoading" class="p-6 text-center text-slate-400">Loading plans...</div>
                    <div v-else-if="plans.length === 0" class="p-6 text-center text-slate-400">No workout plans</div>
                    <div v-else class="space-y-3">
                        <div v-for="plan in plans" :key="plan.id || plan.date+plan.enrollment_id" class="flex items-center justify-between gap-4 p-4 rounded-xl bg-slate-800/40 border border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center text-white" :class="plan.status==='completed'?'bg-green-500/20 border border-green-500/30':'bg-blue-500/20 border border-blue-500/30'">
                                    <span class="text-lg font-black">@{{ plan.day_number }}</span>
                                    <span class="text-[10px] uppercase tracking-wider">@{{ dayName(plan.date) }}</span>
                                </div>
                                <div>
                                    <button class="text-white font-bold hover:text-neon transition text-left" @click="showPlanDetail(plan)">@{{ plan.description || plan.type || 'Workout' }}</button>
                                    <div class="text-xs mt-1" :class="statusClass(plan.status)">@{{ statusText(plan.status) }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-xs text-slate-400">
                                    <span class="px-2 py-1 rounded bg-slate-700">@{{ activityLabel(plan.type) }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <button v-if="plan.status==='pending'" class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-xs" @click="updateSessionStatus(plan,'started')">Start</button>
                                    <button v-if="plan.status==='started'" class="px-3 py-1 rounded-lg bg-neon text-dark text-xs font-black" @click="updateSessionStatus(plan,'completed')">Set Finish</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Active Programs</h3>
                    <div class="space-y-3" v-if="enrollments.length > 0">
                        <div v-for="en in enrollments" :key="en.id" class="p-4 rounded-xl bg-slate-800/40 border border-slate-700 flex items-center justify-between">
                            <div>
                                <div class="text-white font-bold">@{{ en.program.title }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">Start: @{{ formatDate(en.start_date) }} • End: @{{ formatDate(en.end_date) }}</div>
                            </div>
                            <button class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-xs" @click="deleteEnrollment(en.id)">Delete</button>
                        </div>
                    </div>
                    <div v-else class="text-slate-400 text-sm">No active programs. <a href="{{ route('programs.index') }}" class="text-neon hover:underline">Browse</a></div>
                </div>
            </div>
        </div>

        <div v-if="showDetailModal" class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-lg mx-auto mt-20 glass-panel rounded-2xl p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-white font-bold">Workout Detail</h3>
                    <button class="text-slate-400 hover:text-white" @click="closeDetail">×</button>
                </div>
                <div class="space-y-2 text-sm text-slate-300">
                    <div class="text-white font-bold">@{{ detailTitle }}</div>
                    <div><span class="text-slate-500">Date:</span> @{{ detail.date_formatted || formatDate(detail.date) }}</div>
                    <div><span class="text-slate-500">Type:</span> @{{ activityLabel(detail.type) }}</div>
                    <div v-if="detail.distance"><span class="text-slate-500">Distance:</span> @{{ detail.distance }} km</div>
                    <div v-if="detail.duration"><span class="text-slate-500">Duration:</span> @{{ detail.duration }}</div>
                    <div v-if="detail.program_difficulty || detail.difficulty"><span class="text-slate-500">Difficulty:</span> @{{ (detail.program_difficulty || detail.difficulty || '').toUpperCase() }}</div>
                    <div v-if="detail.description"><span class="text-slate-500">Description:</span> @{{ detail.description }}</div>
                    <div><span class="text-slate-500">Status:</span> @{{ detail.status || 'pending' }}</div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button v-if="detail.type === 'custom_workout' && detail.workout_id" class="px-3 py-2 rounded-lg bg-red-500 text-white text-sm" @click="deleteCustomWorkout(detail.workout_id)">Delete Workout</button>
                    <button class="px-3 py-2 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-sm" @click="closeDetail">Close</button>
                </div>
            </div>
        </div>

        <div v-if="showFormModal" class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-lg mx-auto mt-16 glass-panel rounded-2xl p-6">
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
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
const { createApp, ref, reactive, onMounted } = Vue;
(function(){
    const root = document.getElementById('runner-calendar-app');
    if (!root) { console.error('Runner calendar root not found'); return; }
})();
createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const filter = ref('unfinished');
        const plans = ref([]);
        const plansLoading = ref(false);
        const enrollments = ref(@json($enrollments));
        const showDetailModal = ref(false);
        const detail = reactive({});
        const detailTitle = ref('');
        const showFormModal = ref(false);
        const form = reactive({ workout_id:'', workout_date:'', type:'run', difficulty:'moderate', distance:'', duration:'', description:'' });
        let calendar = null;

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

        const initCalendar = () => {
            const el = document.getElementById('calendar');
            if (!el) return; // Guard against null element
            calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                events: '{{ route("runner.calendar.events") }}',
                locale: 'id',
                firstDay: 1,
                eventClassNames: (arg) => {
                    const cls = [];
                    const props = arg.event.extendedProps || {};
                    if (props.difficulty) cls.push('difficulty-' + props.difficulty);
                    if (props.phase) cls.push('phase-' + props.phase);
                    return cls;
                },
                dateClick: (info) => { openForm(info.dateStr); },
                eventClick: (info) => { showEventDetail(info); info.jsEvent.preventDefault(); },
                height: 'auto'
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
            showFormModal.value = true;
        };

        const openFormForToday = () => {
            const d = new Date();
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');
            openForm(`${yyyy}-${mm}-${dd}`);
        };

        const closeForm = () => { showFormModal.value = false; };

        const saveCustomWorkout = async () => {
            const payload = {
                workout_id: form.workout_id || '',
                workout_date: form.workout_date,
                type: form.type,
                difficulty: form.difficulty,
                distance: form.distance || null,
                duration: form.duration || null,
                description: form.description || null,
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

        const showEventDetail = (info) => {
            const props = info.event.extendedProps || {};
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
            } else if (props.type === 'custom_workout') {
                const w = props.workout || {};
                detailTitle.value = 'Custom Workout';
                detail.date = info.event.startStr;
                detail.type = w.type || 'run';
                detail.distance = w.distance || null;
                detail.duration = w.duration || null;
                detail.difficulty = w.difficulty || null;
                detail.description = w.description || null;
                detail.status = w.status || 'pending';
                detail.workout_id = w.id || props.workout_id || null;
            }
            showDetailModal.value = true;
        };

        const showPlanDetail = (plan) => {
            detailTitle.value = plan.program_title || 'Program Session';
            detail.date_formatted = plan.date_formatted || null;
            detail.type = plan.type;
            detail.distance = plan.distance || null;
            detail.duration = plan.duration || null;
            detail.program_difficulty = plan.program_difficulty || null;
            detail.description = plan.description || null;
            detail.status = plan.status || 'pending';
            showDetailModal.value = true;
        };

        const closeDetail = () => { showDetailModal.value = false; };

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

        const updateSessionStatus = async (plan, status) => {
            try {
                const res = await fetch(`{{ route('runner.calendar.update-session-status') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: plan.enrollment_id, session_day: plan.session_day, status })
                });
                const data = await res.json();
                if (data.success) await loadPlans();
            } catch {}
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
        const statusText = (s) => s==='completed'?'Finished':(s==='started'?'On Progress':'UNFINISHED');
        const statusClass = (s) => s==='completed'?'text-green-400':(s==='started'?'text-yellow-400':'text-red-400');
        const activityLabel = (t) => ({running:'Running',run:'Run',easy_run:'Easy Run',interval:'Interval',tempo:'Tempo',yoga:'Yoga',cycling:'Cycling',rest:'Rest'}[t] || 'Running');
        const formatDate = (d) => { try { const dt = new Date(d); return dt.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }); } catch { return d; } };

        setFilter('unfinished');
        loadPlans();
        
        onMounted(() => {
            initCalendar();
        });

        return { filter, plans, plansLoading, enrollments, setFilter, dayName, statusText, statusClass, activityLabel, formatDate,
            showDetailModal, detail, detailTitle, closeDetail, deleteCustomWorkout,
            showFormModal, form, openFormForToday, closeForm, saveCustomWorkout, showPlanDetail, updateSessionStatus, deleteEnrollment };
    }

}).mount('#runner-calendar-app');
</script>
@endpush
