@extends('layouts.coach')

@section('title', 'Create Program')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" id="program-builder-app" v-cloak>
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-8">
            <div>
                <a href="{{ route('coach.programs.index') }}" class="text-slate-400 hover:text-white text-xs mb-2 flex items-center gap-1">
                    ← Back to Programs
                </a>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Create New Program</h1>
                <p class="text-slate-400 text-sm mt-1">Design a world-class training plan for your athletes.</p>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="triggerImport" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import JSON
                </button>
                <input type="file" ref="fileInput" class="hidden" accept=".json" @change="handleImport">
                
                 <button type="button" @click="saveProgram" :disabled="saving" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20 flex items-center gap-2">
                    <span v-if="saving" class="animate-spin">⟳</span>
                    @{{ saving ? 'Saving...' : 'Publish Program' }}
                </button>
            </div>
        </div>

        <form @submit.prevent="saveProgram" class="grid grid-cols-1 gap-8">
            <!-- Program Header & Settings -->
            <div class="glass-panel rounded-2xl p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Program Title</label>
                            <input type="text" v-model="form.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition" placeholder="e.g. Couch to 5K">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Description</label>
                            <textarea v-model="form.description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon outline-none" placeholder="Short description..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Feature Image (Thumbnail)</label>
                            <input type="file" @change="handleFileChange" accept="image/*" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:border-neon outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-800 file:text-neon hover:file:bg-slate-700">
                        </div>
                    </div>
                    
                    <div class="w-full md:w-80 space-y-4 border-l border-slate-700/50 md:pl-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Target</label>
                                <select v-model="form.distance_target" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                    <option value="5k">5K</option>
                                    <option value="10k">10K</option>
                                    <option value="21k">Half</option>
                                    <option value="42k">Marathon</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Level</label>
                                <select v-model="form.difficulty" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:border-neon outline-none">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Inter.</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Price</label>
                                <input type="number" v-model="form.price" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:border-neon outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Weeks</label>
                                <input type="number" v-model="form.duration_weeks" min="1" max="52" @change="updateWeeks" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:border-neon outline-none">
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                             <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_published" id="is_published" class="rounded bg-slate-900 border-slate-700 text-neon focus:ring-neon">
                                <label for="is_published" class="text-xs text-white">Publish</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_challenge" id="is_challenge" class="rounded bg-slate-900 border-slate-700 text-neon focus:ring-neon">
                                <label for="is_challenge" class="text-xs text-white">Challenge Program</label>
                            </div>
                            <button type="button" @click="downloadTemplate" class="text-xs text-neon hover:underline">Download JSON</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horizontal Workout Toolbar -->
            <div class="glass-panel rounded-2xl p-6 sticky top-4 z-30 shadow-2xl shadow-black/50 border-t border-white/10 overflow-visible">
                <div class="flex flex-col gap-4">
                    <!-- Tabs Header -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar border-b border-white/10">
                        @foreach(['easy_run' => 'Easy', 'long_run' => 'Long', 'interval' => 'Speed', 'tempo' => 'Tempo', 'strength' => 'Strength', 'rest' => 'Rest', 'custom' => 'Custom'] as $type => $label)
                            <button type="button" 
                                @click="activeTab = '{{ $type }}'"
                                :class="{ 'bg-neon text-dark border-neon shadow-[0_0_15px_rgba(191,255,0,0.3)]': activeTab === '{{ $type }}', 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700 hover:text-slate-200': activeTab !== '{{ $type }}' }"
                                class="px-6 py-2.5 rounded-xl border font-bold text-sm whitespace-nowrap transition-all duration-300">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                
                    <!-- Tabs Content -->
                    <div class="min-h-[140px] bg-slate-900/50 rounded-xl p-4 border border-white/5">
                        @foreach(['easy_run' => 'Easy', 'long_run' => 'Long', 'interval' => 'Speed', 'tempo' => 'Tempo', 'strength' => 'Strength', 'rest' => 'Rest'] as $type => $label)
                             @php
                                $colors = [
                                    'easy_run' => '#4CAF50',
                                    'long_run' => '#2196F3',
                                    'interval' => '#F44336',
                                    'tempo' => '#FFC107',
                                    'strength' => '#9C27B0',
                                    'rest' => '#9E9E9E',
                                ];
                                $color = $colors[$type] ?? '#ccc';
                            @endphp
                            <div v-show="activeTab === '{{ $type }}'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 animate-fade-in">
                                <div v-for="workout in getWorkoutsByType('{{ $type }}')" 
                                     :key="workout.id || workout.title"
                                     class="fc-event p-3 rounded-xl bg-slate-800 border border-slate-700 cursor-move hover:bg-slate-700 hover:border-slate-500 transition flex flex-col gap-2 group relative overflow-hidden"
                                     draggable="true"
                                     @dragstart="handleDragStart($event, workout)">
                                     
                                     <div class="absolute left-0 top-0 bottom-0 w-1" :style="{ backgroundColor: getSessionColor(workout.type) }"></div>
                                     <div class="flex items-start justify-between gap-2 pl-2">
                                        <div class="text-xs font-bold text-white truncate w-full" :title="workout.title">@{{ workout.title }}</div>
                                        <i class="fa-solid fa-grip-vertical text-slate-600 group-hover:text-slate-400 text-[10px]"></i>
                                     </div>
                                     <div class="text-[10px] text-slate-400 pl-2">
                                        @{{ workout.default_distance > 0 ? workout.default_distance + ' km' : (workout.default_duration || 'Duration') }}
                                     </div>
                                </div>
                                
                                @if($type === 'rest')
                                     <div class="fc-event p-3 rounded-xl bg-slate-800 border border-slate-700 cursor-move hover:bg-slate-700 hover:border-slate-500 transition flex flex-col gap-2 group relative overflow-hidden"
                                         draggable="true"
                                         @dragstart="handleDragStart($event, { type: 'rest', title: 'Rest Day', description: 'Total recovery', distance: 0, duration: '' })">
                                         <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-500"></div>
                                         <div class="flex items-start justify-between gap-2 pl-2">
                                            <div class="text-xs font-bold text-white truncate w-full">Manual Rest</div>
                                            <i class="fa-solid fa-grip-vertical text-slate-600 group-hover:text-slate-400 text-[10px]"></i>
                                         </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <!-- Custom Tab Content -->
                        <div v-show="activeTab === 'custom'" class="animate-fade-in">
                            <div class="mb-3">
                                <button type="button" @click="showCustomModal = true" class="w-full py-2 rounded-xl border border-dashed border-slate-600 text-slate-400 hover:text-neon hover:border-neon hover:bg-slate-800 transition text-sm font-bold flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-plus"></i> Create New Custom Workout
                                </button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                <template v-for="(group, type) in masterWorkouts">
                                    <template v-for="workout in group" :key="workout.id">
                                        <div v-if="workout.coach_id"
                                             class="fc-event p-3 rounded-xl bg-slate-800 border border-slate-700 cursor-move hover:bg-slate-700 hover:border-slate-500 transition flex flex-col gap-2 group relative overflow-hidden"
                                             draggable="true"
                                             @dragstart="handleDragStart($event, workout)">
                                             <div class="absolute left-0 top-0 bottom-0 w-1 bg-pink-500"></div>
                                             <div class="flex items-start justify-between gap-2 pl-2">
                                                <div class="text-xs font-bold text-white truncate w-full" :title="workout.title">@{{ workout.title }}</div>
                                                <i class="fa-solid fa-grip-vertical text-slate-600 group-hover:text-slate-400 text-[10px]"></i>
                                             </div>
                                             <div class="text-[10px] text-slate-400 pl-2">
                                                @{{ workout.default_distance > 0 ? workout.default_distance + ' km' : (workout.default_duration || 'Duration') }}
                                             </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Visual Calendar Builder (Full Width) -->
            <div class="glass-panel rounded-2xl p-6 min-h-[600px]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">Program Schedule</h3>
                    <div class="text-xs text-slate-400">
                        Total Volume: <span class="text-neon font-bold">@{{ totalVolume }} km</span>
                    </div>
                </div>
                
                <!-- Week Navigation -->
                <div class="flex items-center justify-between mb-4 bg-slate-900/50 p-2 rounded-xl">
                    <button type="button" @click="currentWeek = Math.max(1, currentWeek - 1)" class="p-2 hover:text-white text-slate-400 transition" :disabled="currentWeek === 1">← Prev Week</button>
                    <span class="font-bold text-white">Week @{{ currentWeek }} of @{{ form.duration_weeks }}</span>
                    <button type="button" @click="currentWeek = Math.min(form.duration_weeks, currentWeek + 1)" class="p-2 hover:text-white text-slate-400 transition" :disabled="currentWeek === form.duration_weeks">Next Week →</button>
                </div>

                <!-- Day Grid for Current Week -->
                <div class="grid grid-cols-7 gap-2 mb-4">
                    <div v-for="day in 7" :key="day" class="text-center text-xs font-bold text-slate-500 uppercase py-2">
                        Day @{{ day }}
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-2 h-[500px]">
                    <div v-for="day in 7" :key="day" 
                         class="bg-slate-800/50 border border-slate-700 rounded-xl p-2 relative group min-h-[100px] transition-all hover:bg-slate-800 flex flex-col gap-2 overflow-y-auto no-scrollbar"
                         @dragover.prevent @drop="handleDrop($event, day)">
                        
                        <div class="absolute top-2 right-2 text-xs font-mono text-slate-600">@{{ day }}</div>
                        
                        <!-- Dropped Sessions -->
                        <template v-if="getSessions(currentWeek, day).length > 0">
                            <div v-for="session in getSessions(currentWeek, day)" 
                                :key="session._id"
                                class="p-2 rounded-lg cursor-pointer hover:brightness-110 transition text-xs shadow-lg relative z-10"
                                :style="{ backgroundColor: getSessionColor(session.type), borderLeft: '3px solid rgba(255,255,255,0.3)' }"
                                @click.stop="editSession(session)">
                                <div class="font-bold text-white truncate">@{{ session.title || session.type }}</div>
                                <div class="text-white/70">@{{ session.distance }} km</div>
                            </div>
                        </template>
                        
                        <div v-else class="h-full w-full flex items-center justify-center text-slate-600 text-xs opacity-0 group-hover:opacity-100 pointer-events-none absolute inset-0">
                            Drop here
                        </div>
                        
                        <button type="button" class="absolute bottom-2 right-2 px-2 py-1 rounded-lg bg-slate-700/60 text-white text-[10px] font-bold hover:bg-neon hover:text-dark transition"
                                @click.stop="openAddSession(day)">
                            Add
                        </button>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="button" @click="copyWeek" class="text-xs text-neon hover:underline">Copy this week to next week</button>
                </div>
            </div>
        </form>

        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="showAddModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Add Custom Workout</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Title</label>
                        <input type="text" v-model="newSession.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Workout Type</label>
                        <select v-model="newSession.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                            <option value="easy_run">Easy Run</option>
                            <option value="long_run">Long Run</option>
                            <option value="tempo">Tempo Run</option>
                            <option value="interval">Intervals</option>
                            <option value="strength">Strength</option>
                            <option value="rest">Rest</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Distance (km)</label>
                        <input type="number" step="0.1" v-model="newSession.distance" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Duration (Optional)</label>
                        <input type="text" v-model="newSession.duration" placeholder="00:30:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Description</label>
                        <textarea v-model="newSession.description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm">Cancel</button>
                        <button type="button" @click="createSession" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="showModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Edit Session</h3>
                
                <div class="space-y-4">
                     <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Title</label>
                        <input type="text" v-model="editingSession.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Workout Type</label>
                        <select v-model="editingSession.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                            <option value="easy_run">Easy Run</option>
                            <option value="long_run">Long Run</option>
                            <option value="tempo">Tempo Run</option>
                            <option value="interval">Intervals</option>
                            <option value="strength">Strength</option>
                            <option value="rest">Rest</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Distance (km)</label>
                        <input type="number" step="0.1" v-model="editingSession.distance" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Duration (Optional)</label>
                        <input type="text" v-model="editingSession.duration" placeholder="00:30:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Description</label>
                        <textarea v-model="editingSession.description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm"></textarea>
                    </div>
                    
                    <div class="flex justify-between pt-4">
                        <button type="button" @click="deleteSession" class="text-red-400 text-sm hover:underline">Delete Session</button>
                        <div class="flex gap-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm">Cancel</button>
                            <button type="button" @click="saveSession" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Workout Modal -->
        <div v-if="showCustomModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="showCustomModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">{{ __('Create Custom Workout') }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Title') }}</label>
                        <input type="text" v-model="customWorkout.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="{{ __('e.g. Hill Sprints') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Type') }}</label>
                        <select v-model="customWorkout.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                            <option v-for="(label, type) in workoutTypes" :value="type">@{{ label }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Distance (km)') }}</label>
                            <input type="number" step="0.1" v-model="customWorkout.default_distance" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Duration') }}</label>
                            <input type="text" v-model="customWorkout.default_duration" placeholder="00:00:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Description') }}</label>
                        <textarea v-model="customWorkout.description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="{{ __('Workout details...') }}"></textarea>
                    </div>
                    
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" v-model="customWorkout.is_public" id="cw_public" class="rounded bg-slate-900 border-slate-700 text-neon focus:ring-neon">
                        <div>
                            <label for="cw_public" class="text-sm font-bold text-white block">{{ __('Make Public') }}</label>
                            <p class="text-xs text-slate-400">{{ __('Visible to other coaches') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showCustomModal = false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm hover:bg-slate-700">{{ __('Cancel') }}</button>
                        <button type="button" @click="saveCustomWorkout" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm hover:bg-neon/90">{{ __('Create Workout') }}</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
const { createApp, ref, reactive, computed } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const saving = ref(false);
        const currentWeek = ref(1);
        const activeTab = ref('easy_run');
        const showModal = ref(false);
        const showCustomModal = ref(false);
        const showAddModal = ref(false);
        const editingSession = reactive({});
        const fileInput = ref(null);
        const addTargetDay = ref(1);
        
        // Master Workouts from Backend
        const initialMasterWorkouts = @json($masterWorkouts ?? []);
        const masterWorkouts = reactive(Array.isArray(initialMasterWorkouts) && initialMasterWorkouts.length === 0 ? {} : initialMasterWorkouts);
        
        const customWorkout = reactive({
            title: '',
            type: 'easy_run',
            description: '',
            default_distance: 0,
            default_duration: '',
            is_public: false
        });
        
        const newSession = reactive({
            title: '',
            type: 'easy_run',
            distance: 0,
            duration: '',
            description: ''
        });

        const workoutTypes = {
            easy_run: 'Easy',
            long_run: 'Long',
            interval: 'Speed',
            tempo: 'Tempo',
            strength: 'Strength',
            rest: 'Rest'
        };
        
        const form = reactive({
            title: '',
            description: '',
            distance_target: '5k',
            difficulty: 'beginner',
            price: 0,
            duration_weeks: 12,
            is_published: false,
            is_challenge: false,
            sessions: [] // Array of { day: 1, type: 'easy_run', distance: 5 ... }
        });

        // Calculate absolute day index: (week-1)*7 + day
        const getAbsDay = (week, day) => (week - 1) * 7 + day;

        const generateId = () => Date.now().toString(36) + Math.random().toString(36).substr(2);

        const getSessions = (week, day) => {
            const absDay = getAbsDay(week, day);
            return form.sessions.filter(s => s.day === absDay);
        };

        const getSessionColor = (type) => {
            const colors = {
                easy_run: '#4CAF50',
                long_run: '#2196F3',
                tempo: '#FFC107',
                interval: '#F44336',
                strength: '#9C27B0',
                rest: '#9E9E9E'
            };
            return colors[type] || '#9E9E9E';
        };

        const getWorkoutsByType = (type) => {
            try {
                if (!masterWorkouts || typeof masterWorkouts !== 'object') return [];
                return masterWorkouts[type] || [];
            } catch (e) {
                console.error('getWorkoutsByType error:', e);
                return [];
            }
        };

        const handleDragStart = (event, workout) => {
            const data = {
                type: workout.type,
                title: workout.title,
                description: workout.description,
                distance: workout.default_distance || 0,
                duration: workout.default_duration || ''
            };
            event.dataTransfer.setData('json', JSON.stringify(data));
            event.dataTransfer.effectAllowed = 'copy';
        };

        const openAddSession = (day) => {
            addTargetDay.value = day;
            Object.assign(newSession, { title: '', type: 'easy_run', distance: 0, duration: '', description: '' });
            showAddModal.value = true;
        };

        const createSession = () => {
            const absDay = getAbsDay(currentWeek.value, addTargetDay.value);
            const s = {
                _id: generateId(),
                day: absDay,
                type: newSession.type,
                title: newSession.title,
                distance: parseFloat(newSession.distance) || 0,
                duration: newSession.duration || '',
                description: newSession.description || ''
            };
            form.sessions.push(s);
            showAddModal.value = false;
        };

        const saveCustomWorkout = async () => {
            try {
                const res = await fetch('{{ route("coach.custom-workouts.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(customWorkout)
                });
                
                const data = await res.json();
                
                if (res.ok) {
                    // Add to local list
                    if (!masterWorkouts[customWorkout.type]) {
                        masterWorkouts[customWorkout.type] = [];
                    }
                    masterWorkouts[customWorkout.type].push(data.workout);
                    
                    showCustomModal.value = false;
                    
                    // Reset form
                    Object.assign(customWorkout, {
                        title: '',
                        type: 'easy_run',
                        description: '',
                        default_distance: 0,
                        default_duration: '',
                        is_public: false
                    });
                    
                    alert('Custom workout created!');
                } else {
                    alert(data.message || 'Failed to create workout');
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred');
            }
        };

        const editSession = (session) => {
            Object.assign(editingSession, JSON.parse(JSON.stringify(session))); // Deep copy
            showModal.value = true;
        };

        const saveSession = () => {
            const index = form.sessions.findIndex(s => s._id === editingSession._id);
            if (index !== -1) {
                form.sessions[index] = { ...editingSession };
            }
            showModal.value = false;
        };

        const deleteSession = () => {
            form.sessions = form.sessions.filter(s => s._id !== editingSession._id);
            showModal.value = false;
        };

        // Update handleDrop to read dataTransfer
        const handleDrop = (event, day) => {
            try {
                const jsonStr = event.dataTransfer.getData('json');
                if (jsonStr) {
                    const data = JSON.parse(jsonStr);
                    const absDay = getAbsDay(currentWeek.value, day);
                    
                    console.log('Dropping workout:', data, 'to day:', absDay);

                    // Add new (Allow multiple per day)
                    const newSession = {
                        _id: generateId(),
                        day: absDay,
                        type: data.type,
                        title: data.title,
                        distance: parseFloat(data.distance) || 0,
                        description: data.description || '',
                        duration: data.duration || ''
                    };
                    
                    form.sessions.push(newSession);
                    console.log('Session added. Total sessions:', form.sessions.length);
                }
            } catch (e) {
                console.error('Invalid drop data or error in handleDrop:', e);
            }
        };

        const totalVolume = computed(() => {
            return form.sessions.reduce((acc, s) => acc + (parseFloat(s.distance) || 0), 0).toFixed(1);
        });

        const copyWeek = () => {
            if (currentWeek.value >= form.duration_weeks) return;
            
            const nextWeek = currentWeek.value + 1;
            const startCurrent = (currentWeek.value - 1) * 7 + 1;
            const endCurrent = startCurrent + 6;
            
            // Get sessions from current week
            const currentSessions = form.sessions.filter(s => s.day >= startCurrent && s.day <= endCurrent);
            
            // Remove sessions from next week
            const startNext = (nextWeek - 1) * 7 + 1;
            const endNext = startNext + 6;
            form.sessions = form.sessions.filter(s => s.day < startNext || s.day > endNext);
            
            // Add copied sessions
            currentSessions.forEach(s => {
                const dayOffset = s.day - startCurrent; // 0 to 6
                const newDay = startNext + dayOffset;
                form.sessions.push({
                    ...s,
                    _id: generateId(),
                    day: newDay
                });
            });
            
            alert(`Week ${currentWeek.value} copied to Week ${nextWeek}`);
            currentWeek.value = nextWeek;
        };
        
        const updateWeeks = () => {
            // Trim sessions beyond new duration
            const maxDay = form.duration_weeks * 7;
            form.sessions = form.sessions.filter(s => s.day <= maxDay);
        };

        const downloadTemplate = async () => {
            try {
                const res = await fetch('{{ route("coach.programs.generate-template") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ duration_weeks: form.duration_weeks })
                });
                const data = await res.json();
                
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'program_template.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } catch (e) {
                alert('Failed to generate template');
            }
        };

        const triggerImport = () => {
            fileInput.value.click();
        };

        const handleImport = async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('json_file', file);

            try {
                const res = await fetch('{{ route("coach.programs.import-json") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
                    body: formData
                });
                
                let data;
                try {
                    data = await res.json();
                } catch (jsonError) {
                    console.error('Failed to parse JSON response:', jsonError);
                    alert('Server returned an invalid response. Please check your file.');
                    return;
                }
                
                if (res.ok) {
                    if (data.sessions && Array.isArray(data.sessions)) {
                        form.sessions = data.sessions.map(s => ({...s, _id: s._id || generateId()}));
                        if (data.duration_weeks) form.duration_weeks = data.duration_weeks;
                        alert('Program imported successfully!');
                    } else {
                        alert('Invalid JSON format: Missing "sessions" array.');
                    }
                } else {
                    alert(data.message || (data.errors && data.errors.json_file ? data.errors.json_file[0] : 'Import failed'));
                }
            } catch (e) {
                console.error('Import error:', e);
                alert('An error occurred during import. See console for details.');
            } finally {
                e.target.value = ''; // Reset input
            }
        };

        const saveProgram = async () => {
            if (saving.value) return;
            saving.value = true;

            try {
                const formData = new FormData();
                formData.append('title', form.title);
                formData.append('description', form.description || '');
                formData.append('distance_target', form.distance_target);
                formData.append('difficulty', form.difficulty);
                formData.append('price', form.price);
                formData.append('duration_weeks', form.duration_weeks);
                formData.append('is_published', form.is_published ? 1 : 0);
                formData.append('is_challenge', form.is_challenge ? 1 : 0);
                formData.append('program_json', JSON.stringify({ sessions: form.sessions }));

                if (form.thumbnail) {
                    formData.append('thumbnail', form.thumbnail);
                }

                const res = await fetch('{{ route("coach.programs.store") }}', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': csrf, 
                        'Accept':'application/json'
                        // Content-Type must be undefined for FormData to work correctly
                    },
                    body: formData
                });
                let data = null;
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    try {
                        data = await res.json();
                    } catch (parseErr) {
                        data = null;
                    }
                }
                
                if (res.ok) {
                    window.location.href = '{{ route("coach.programs.index") }}';
                } else {
                    alert((data && data.message) || 'Failed to save program');
                    if (data && data.errors) {
                        console.error(data.errors);
                    }
                }
            } catch (e) {
                alert('An error occurred');
                console.error(e);
            } finally {
                saving.value = false;
            }
        };

        const handleFileChange = (event) => {
            const file = event.target.files[0];
            if (file) {
                form.thumbnail = file;
            }
        };

        return { 
            form, saving, currentWeek, activeTab, totalVolume, 
            getSessions, getSessionColor, getWorkoutsByType, handleDrop, handleDragStart,
            editSession, showModal, editingSession, saveSession, deleteSession,
            copyWeek, updateWeeks, saveProgram, downloadTemplate, triggerImport, handleImport, fileInput,
            handleFileChange, showCustomModal, customWorkout, saveCustomWorkout, workoutTypes,
            showAddModal, newSession, openAddSession, createSession, addTargetDay
        };
    }
}).mount('#program-builder-app');
</script>
@endpush
