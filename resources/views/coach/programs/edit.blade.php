@extends('layouts.coach')
@php
    $withSidebar = true;
@endphp

@section('title', 'Edit Program')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" id="program-builder-app" v-cloak>
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-8">
            <div>
                <a href="{{ route('coach.programs.index') }}" class="text-slate-400 hover:text-white text-xs mb-2 flex items-center gap-1">
                    ← Back to Programs
                </a>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Edit Program</h1>
                <p class="text-slate-400 text-sm mt-1">Update your training plan.</p>
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
                    @{{ saving ? 'Saving...' : 'Update Program' }}
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
                            @if($program->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $program->thumbnail) }}" alt="Current Thumbnail" class="h-20 w-auto rounded-lg border border-slate-700 object-cover">
                                </div>
                            @endif
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
                                class="p-2 rounded-lg cursor-pointer hover:brightness-110 transition text-xs shadow-lg relative z-10 group"
                                :style="{ backgroundColor: getSessionColor(session.type), borderLeft: '3px solid rgba(255,255,255,0.3)' }"
                                draggable="true"
                                @dragstart="handleSessionDragStart($event, session)"
                                @click.stop="openBuilderEdit(session)">
                                <div class="flex justify-between items-start">
                                    <div class="font-bold text-white truncate pr-2">@{{ session.title || session.type }}</div>
                                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                        <button type="button" class="text-white/70 hover:text-white" 
                                                @click.stop="duplicateWorkout(session._id)" title="Duplicate">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                        <button type="button" class="text-white/70 hover:text-white" 
                                                @click.stop="deleteWorkout(session._id)" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-white/70">@{{ session.distance }} km</div>
                            </div>
                        </template>
                        
                        <div v-else class="h-full w-full flex items-center justify-center text-slate-600 text-xs opacity-0 group-hover:opacity-100 pointer-events-none absolute inset-0">
                            Drop here
                        </div>
                        
                        <button type="button" class="absolute bottom-2 right-2 px-2 py-1 rounded-lg bg-slate-700/60 text-white text-[10px] font-bold hover:bg-neon hover:text-dark transition"
                                @click.stop="openBuilderAdd(day)">
                            Add
                        </button>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="button" @click="copyWeek" class="text-xs text-neon hover:underline">Copy this week to next week</button>
                </div>
            </div>
        </form>

        <div v-if="builderVisible" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="closeBuilder"></div>
            <div class="relative z-10 max-w-2xl mx-auto my-10 glass-panel rounded-2xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">Advanced Workout Builder</h3>
                    <button class="text-slate-400 hover:text-white" @click="closeBuilder">×</button>
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
                <div class="mt-4 glass-panel rounded-xl p-4">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-2">Summary</div>
                    <div class="text-white text-sm">@{{ builderSummary }}</div>
                    <div class="text-slate-400 text-xs mt-1">Total Distance: @{{ builderTotalDistance }} km</div>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <div>
                         <button v-if="builderIsEditing" type="button" class="px-4 py-2 rounded-lg bg-red-500/10 text-red-500 border border-red-500/20 text-sm hover:bg-red-500/20 transition" @click="deleteWorkout(builderSessionId)">
                            Delete Workout
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm" @click="closeBuilder">Cancel</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm" @click="saveBuilder">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Workout Modal -->
        <div v-if="showCustomModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="showCustomModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Create Custom Workout</h3>
                
                <form @submit.prevent="saveCustomWorkout" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Title</label>
                        <input type="text" v-model="customWorkout.title" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:border-neon outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Type</label>
                        <select v-model="cwForm.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:border-neon outline-none">
                            <option v-for="(label, type) in workoutTypes" :key="type" :value="type">@{{ label }}</option>
                        </select>
                    </div>

                    <div class="glass-panel rounded-xl p-4">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2">Main Set</div>
                        <div v-if="cwForm.type==='easy_run' || cwForm.type==='long_run'">
                            <div class="grid grid-cols-3 gap-2">
                                <select v-model="cwForm.main.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                    <option value="distance">Distance</option>
                                    <option value="time">Time</option>
                                </select>
                                <input v-if="cwForm.main.by==='distance'" type="number" step="0.1" v-model.number="cwForm.main.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Distance (km)">
                                <input v-else type="text" v-model="cwForm.main.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:30:00">
                                <input type="text" v-model="cwForm.main.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                            </div>
                        </div>
                        <div v-else-if="cwForm.type==='tempo'">
                            <div class="grid grid-cols-4 gap-2">
                                <select v-model="cwForm.tempo.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                    <option value="distance">Distance</option>
                                    <option value="time">Time</option>
                                </select>
                                <input v-if="cwForm.tempo.by==='distance'" type="number" step="0.1" v-model.number="cwForm.tempo.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Distance (km)">
                                <input v-else type="text" v-model="cwForm.tempo.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:20:00">
                                <input type="text" v-model="cwForm.tempo.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                                <select v-model="cwForm.tempo.effort" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                    <option value="moderate">Moderate</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                        </div>
                        <div v-else-if="cwForm.type==='interval'">
                            <div class="grid grid-cols-5 gap-2">
                                <input type="number" v-model.number="cwForm.interval.reps" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Reps">
                                <select v-model="cwForm.interval.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                    <option value="distance">Distance</option>
                                    <option value="time">Time</option>
                                </select>
                                <input v-if="cwForm.interval.by==='distance'" type="number" step="0.1" v-model.number="cwForm.interval.repDistanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Rep km">
                                <input v-else type="text" v-model="cwForm.interval.repTime" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Rep 00:03:00">
                                <input type="text" v-model="cwForm.interval.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss)">
                                <input type="text" v-model="cwForm.interval.recovery" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Recovery">
                            </div>
                        </div>
                        <div v-else-if="cwForm.type==='rest'">
                            <div class="text-slate-400 text-sm">Rest Day</div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            Summary: <span class="text-white">@{{ cwSummary }}</span>
                            <div class="text-slate-400 mt-1">Total Distance: @{{ cwTotalDistance }} km</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Description</label>
                        <textarea v-model="customWorkout.description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:border-neon outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" v-model="customWorkout.is_public" id="cw_is_public" class="rounded bg-slate-900 border-slate-700 text-neon focus:ring-neon">
                        <label for="cw_is_public" class="text-xs text-white">Public (Visible to other coaches)</label>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Intensity</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="customWorkout.intensity" value="low" class="text-neon focus:ring-neon bg-slate-900 border-slate-700">
                                <span class="text-sm text-slate-300 capitalize">Low</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="customWorkout.intensity" value="medium" class="text-neon focus:ring-neon bg-slate-900 border-slate-700">
                                <span class="text-sm text-slate-300 capitalize">Medium</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="customWorkout.intensity" value="high" class="text-neon focus:ring-neon bg-slate-900 border-slate-700">
                                <span class="text-sm text-slate-300 capitalize">High</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showCustomModal = false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm hover:bg-neon/90">Create Workout</button>
                    </div>
                </form>
            </div>
        </div>

        

    </div>
</main>
@endsection

@push('scripts')
<script>
const { createApp, ref, reactive, computed, onMounted } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const saving = ref(false);
        const currentWeek = ref(1);
        const activeTab = ref('easy_run');
        const showCustomModal = ref(false);
        const fileInput = ref(null);
        const builderVisible = ref(false);
        const builderIsEditing = ref(false);
        const builderTargetDay = ref(1);
        const builderSessionId = ref(null);
        
        // Master Workouts from Backend
        const initialMasterWorkouts = @json($masterWorkouts ?? []);
        const masterWorkouts = reactive(Array.isArray(initialMasterWorkouts) && initialMasterWorkouts.length === 0 ? {} : initialMasterWorkouts);

        // Initial data from server
        const programData = @json($program);
        // Ensure ID is available even if not in JSON (though it should be)
        if (!programData.id) {
            programData.id = '{{ $program->id }}';
        }
        
        const generateId = () => Date.now().toString(36) + Math.random().toString(36).substr(2);

        const programSessions = (programData.program_json?.sessions || []).map(s => ({...s, _id: s._id || generateId()}));
        
        const customWorkout = reactive({
            title: '',
            type: 'easy_run',
            description: '',
            default_distance: 0,
            default_duration: '',
            is_public: false,
            intensity: 'low'
        });
        
        const cwForm = reactive({
            type: 'easy_run',
            main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
            tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
            interval: { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' },
            strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
        });
        
        const builderForm = reactive({
            type: 'easy_run',
            title: '',
            intensity: 'low',
            warmup: { enabled: false, by: 'distance', distanceKm: 0, duration: '' },
            cooldown: { enabled: false, by: 'distance', distanceKm: 0, duration: '' },
            main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
            longRun: { fastFinish: { enabled: false, distanceKm: 0, pace: '' } },
            tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
            interval: { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' },
            strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
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
            title: programData.title,
            description: programData.description,
            distance_target: programData.distance_target,
            difficulty: programData.difficulty,
            price: programData.price,
            duration_weeks: programData.duration_weeks,
            is_published: !!programData.is_published,
            is_challenge: !!programData.is_challenge,
            sessions: programSessions
        });

        // Calculate absolute day index: (week-1)*7 + day
        const getAbsDay = (week, day) => (week - 1) * 7 + day;

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
                rest: '#9E9E9E',
                custom: '#00BCD4'
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

        const openBuilderAdd = (day) => {
            builderIsEditing.value = false;
            builderTargetDay.value = day;
            builderSessionId.value = null;
            Object.assign(builderForm, { type: 'easy_run', title: '' });
            Object.assign(builderForm.warmup, { enabled: false, by: 'distance', distanceKm: 0, duration: '' });
            Object.assign(builderForm.cooldown, { enabled: false, by: 'distance', distanceKm: 0, duration: '' });
            Object.assign(builderForm.main, { by: 'distance', distanceKm: 0, duration: '', pace: '' });
            Object.assign(builderForm.longRun.fastFinish, { enabled: false, distanceKm: 0, pace: '' });
            Object.assign(builderForm.tempo, { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' });
            Object.assign(builderForm.interval, { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' });
            Object.assign(builderForm.strength, { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] });
            builderVisible.value = true;
        };

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

        const strengthOptions = computed(() => {
            const cat = builderForm.strength.category;
            const all = strengthData.strength_training;
            const list = cat && all[cat] ? all[cat] : [];
            return list;
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

        const openBuilderEdit = (session) => {
            builderIsEditing.value = true;
            builderSessionId.value = session._id;
            const abs = session.day;
            const week = Math.ceil(abs / 7);
            const day = abs - (week - 1) * 7;
            builderTargetDay.value = day;
            builderForm.type = session.type;
            builderForm.title = session.title || '';
            try {
                if (session.advanced_config) {
                    const cfg = typeof session.advanced_config === 'string' ? JSON.parse(session.advanced_config) : session.advanced_config;
                    Object.assign(builderForm, { ...builderForm, ...cfg });
                } else {
                    builderForm.main.distanceKm = session.distance || 0;
                }
            } catch(e){}
            builderVisible.value = true;
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
                    body: JSON.stringify({
                        title: customWorkout.title,
                        type: cwForm.type,
                        description: customWorkout.description || cwSummary.value,
                        default_distance: cwTotalDistance.value,
                        default_duration: (() => {
                            if (cwForm.type==='easy_run' || cwForm.type==='long_run') {
                                if (cwForm.main.by==='time') return cwForm.main.duration || '';
                                return '';
                            } else if (cwForm.type==='tempo') {
                                if (cwForm.tempo.by==='time') return cwForm.tempo.duration || '';
                                return '';
                            } else if (cwForm.type==='interval') {
                                if (cwForm.interval.by==='time') {
                                    const perRep = parseDurationMinutes(cwForm.interval.repTime);
                                    const total = (Number(cwForm.interval.reps)||0) * (isNaN(perRep)?0:perRep);
                                    const totalStr = minutesToHHMMSS(total);
                                    return totalStr;
                                }
                                return '';
                            }
                            return '';
                        })(),
                        is_public: customWorkout.is_public,
                        intensity: customWorkout.intensity || 'low'
                    })
                });
                
                const data = await res.json();
                
                if (res.ok) {
                    if (!masterWorkouts[cwForm.type]) {
                        masterWorkouts[cwForm.type] = [];
                    }
                    masterWorkouts[cwForm.type].push(data.workout);
                    
                    showCustomModal.value = false;
                    
                    Object.assign(customWorkout, {
                        title: '',
                        description: '',
                        default_distance: 0,
                        default_duration: '',
                        is_public: false,
                        intensity: 'low'
                    });
                    Object.assign(cwForm, {
                        type: 'easy_run',
                        main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
                        tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
                        interval: { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' },
                        strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
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

        const builderSummary = computed(() => {
            const parts = [];
            if (builderForm.warmup.enabled) {
                parts.push(`WU: ${builderForm.warmup.by==='distance' ? `${builderForm.warmup.distanceKm}km` : builderForm.warmup.duration}`);
            }
            if (builderForm.type==='interval') {
                if (builderForm.interval.by==='distance') {
                    parts.push(`${builderForm.interval.reps}x${builderForm.interval.repDistanceKm}km${builderForm.interval.pace ? ` @${builderForm.interval.pace}`:''}`);
                } else {
                    parts.push(`${builderForm.interval.reps}x${builderForm.interval.repTime}${builderForm.interval.pace ? ` @${builderForm.interval.pace}`:''}`);
                }
                parts.push(`Rec ${builderForm.interval.recovery}`);
            } else if (builderForm.type==='tempo') {
                if (builderForm.tempo.by==='distance') {
                    parts.push(`${builderForm.tempo.distanceKm}km @${builderForm.tempo.pace} ${builderForm.tempo.effort}`);
                } else {
                    parts.push(`${builderForm.tempo.duration} @${builderForm.tempo.pace} ${builderForm.tempo.effort}`);
                }
            } else if (builderForm.type==='long_run') {
                if (builderForm.main.by==='distance') {
                    parts.push(`${builderForm.main.distanceKm}km Long Run`);
                } else {
                    parts.push(`${builderForm.main.duration} Long Run${builderForm.main.pace ? ` @${builderForm.main.pace}`:''}`);
                }
                if (builderForm.longRun.fastFinish.enabled) {
                    parts.push(`FF ${builderForm.longRun.fastFinish.distanceKm}km @${builderForm.longRun.fastFinish.pace}`);
                }
            } else if (builderForm.type==='easy_run') {
                if (builderForm.main.by==='distance') {
                    parts.push(`${builderForm.main.distanceKm}km Easy${builderForm.main.pace ? ` @${builderForm.main.pace}`:''}`);
                } else {
                    parts.push(`${builderForm.main.duration} Easy${builderForm.main.pace ? ` @${builderForm.main.pace}`:''}`);
                }
            } else if (builderForm.type==='strength') {
                const cat = builderForm.strength.category ? builderForm.strength.category.replace('_',' ') : 'Strength';
                if (builderForm.strength.plan && builderForm.strength.plan.length) {
                    const items = builderForm.strength.plan.slice(0,3).map(i => i.name).join(', ');
                    parts.push(`${cat}: ${items}${builderForm.strength.plan.length>3 ? ', ...' : ''}`);
                } else {
                    parts.push(cat);
                }
            } else if (builderForm.type==='rest') {
                parts.push('Rest');
            }
            if (builderForm.cooldown.enabled) {
                parts.push(`CD: ${builderForm.cooldown.by==='distance' ? `${builderForm.cooldown.distanceKm}km` : builderForm.cooldown.duration}`);
            }
            const base = parts.join(' | ');
            return builderForm.intensity ? `${base} | Intensity: ${builderForm.intensity}` : base;
        });
        
        const minutesToHHMMSS = (min) => {
            const totalSec = Math.round((min || 0) * 60);
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = totalSec % 60;
            return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
        };

        const parsePaceMinPerKm = (s) => {
            if (!s) return NaN;
            const m = s.trim().match(/^(\d{1,2}):(\d{2})/);
            if (!m) return NaN;
            const min = parseInt(m[1], 10);
            const sec = parseInt(m[2], 10);
            return min + sec/60;
        };

        const parseDurationMinutes = (s) => {
            if (!s) return NaN;
            const parts = s.trim().split(':').map(x => parseInt(x,10));
            if (parts.some(isNaN)) return NaN;
            let h=0,m=0,sec=0;
            if (parts.length === 3) { h=parts[0]; m=parts[1]; sec=parts[2]; }
            else if (parts.length === 2) { m=parts[0]; sec=parts[1]; }
            else { m=parts[0]; }
            return h*60 + m + sec/60;
        };
        
        const cwSummary = computed(() => {
            const parts = [];
            if (cwForm.type==='interval') {
                if (cwForm.interval.by==='distance') {
                    parts.push(`${cwForm.interval.reps}x${cwForm.interval.repDistanceKm}km${cwForm.interval.pace ? ` @${cwForm.interval.pace}`:''}`);
                } else {
                    parts.push(`${cwForm.interval.reps}x${cwForm.interval.repTime}${cwForm.interval.pace ? ` @${cwForm.interval.pace}`:''}`);
                }
                parts.push(`Rec ${cwForm.interval.recovery}`);
            } else if (cwForm.type==='tempo') {
                if (cwForm.tempo.by==='distance') {
                    parts.push(`${cwForm.tempo.distanceKm}km${cwForm.tempo.pace ? ` @${cwForm.tempo.pace}`:''} ${cwForm.tempo.effort}`);
                } else {
                    parts.push(`${cwForm.tempo.duration}${cwForm.tempo.pace ? ` @${cwForm.tempo.pace}`:''} ${cwForm.tempo.effort}`);
                }
            } else if (cwForm.type==='long_run') {
                if (cwForm.main.by==='distance') {
                    parts.push(`${cwForm.main.distanceKm}km Long Run${cwForm.main.pace ? ` @${cwForm.main.pace}`:''}`);
                } else {
                    parts.push(`${cwForm.main.duration} Long Run${cwForm.main.pace ? ` @${cwForm.main.pace}`:''}`);
                }
            } else if (cwForm.type==='easy_run') {
                if (cwForm.main.by==='distance') {
                    parts.push(`${cwForm.main.distanceKm}km Easy${cwForm.main.pace ? ` @${cwForm.main.pace}`:''}`);
                } else {
                    parts.push(`${cwForm.main.duration} Easy${cwForm.main.pace ? ` @${cwForm.main.pace}`:''}`);
                }
            } else if (cwForm.type==='rest') {
                parts.push('Rest');
            }
            return parts.join(' | ');
        });
        
        const cwTotalDistance = computed(() => {
            let total = 0;
            if (cwForm.type==='interval') {
                if (cwForm.interval.by==='distance') {
                    total += (Number(cwForm.interval.reps)||0) * (Number(cwForm.interval.repDistanceKm)||0);
                } else {
                    const dMin = parseDurationMinutes(cwForm.interval.repTime);
                    const pMin = parsePaceMinPerKm(cwForm.interval.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += (Number(cwForm.interval.reps)||0) * dist;
                }
            } else if (cwForm.type==='tempo') {
                if (cwForm.tempo.by==='distance') total += Number(cwForm.tempo.distanceKm)||0;
                else {
                    const dMin = parseDurationMinutes(cwForm.tempo.duration);
                    const pMin = parsePaceMinPerKm(cwForm.tempo.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += dist;
                }
            } else if (cwForm.type==='long_run') {
                if (cwForm.main.by==='distance') total += Number(cwForm.main.distanceKm)||0;
                else {
                    const dMin = parseDurationMinutes(cwForm.main.duration);
                    const pMin = parsePaceMinPerKm(cwForm.main.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += dist;
                }
            } else if (cwForm.type==='easy_run') {
                if (cwForm.main.by==='distance') total += Number(cwForm.main.distanceKm)||0;
                else {
                    const dMin = parseDurationMinutes(cwForm.main.duration);
                    const pMin = parsePaceMinPerKm(cwForm.main.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += dist;
                }
            }
            return Number(total.toFixed(1));
        });

        const builderTotalDistance = computed(() => {
            let total = 0;
            if (builderForm.warmup.enabled && builderForm.warmup.by==='distance') total += Number(builderForm.warmup.distanceKm)||0;
            if (builderForm.cooldown.enabled && builderForm.cooldown.by==='distance') total += Number(builderForm.cooldown.distanceKm)||0;
            if (builderForm.type==='interval') {
                if (builderForm.interval.by==='distance') {
                    total += (Number(builderForm.interval.reps)||0) * (Number(builderForm.interval.repDistanceKm)||0);
                } else {
                    const dMin = parseDurationMinutes(builderForm.interval.repTime);
                    const pMin = parsePaceMinPerKm(builderForm.interval.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += (Number(builderForm.interval.reps)||0) * dist;
                }
            } else if (builderForm.type==='tempo') {
                if (builderForm.tempo.by==='distance') total += Number(builderForm.tempo.distanceKm)||0;
                else {
                    const dMin = parseDurationMinutes(builderForm.tempo.duration);
                    const pMin = parsePaceMinPerKm(builderForm.tempo.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += dist;
                }
            } else if (builderForm.type==='long_run') {
                if (builderForm.main.by==='distance') total += Number(builderForm.main.distanceKm)||0;
                else {
                    const dMin = parseDurationMinutes(builderForm.main.duration);
                    const pMin = parsePaceMinPerKm(builderForm.main.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += dist;
                }
            } else if (builderForm.type==='easy_run') {
                if (builderForm.main.by==='distance') total += Number(builderForm.main.distanceKm)||0;
                else {
                    const dMin = parseDurationMinutes(builderForm.main.duration);
                    const pMin = parsePaceMinPerKm(builderForm.main.pace);
                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                    total += dist;
                }
            }
            return Number(total.toFixed(1));
        });

        const closeBuilder = () => { builderVisible.value = false; };

        const saveBuilder = () => {
            const absDay = getAbsDay(currentWeek.value, builderTargetDay.value);
            const payload = {
                _id: builderSessionId.value || generateId(),
                day: absDay,
                type: builderForm.type,
                title: builderForm.title || builderSummary.value,
                distance: builderTotalDistance.value,
                description: builderSummary.value,
                duration: builderForm.main.duration || '',
                advanced_config: JSON.stringify(builderForm)
            };
            if (builderIsEditing.value) {
                const idx = form.sessions.findIndex(s => s._id === builderSessionId.value);
                if (idx !== -1) form.sessions[idx] = payload;
            } else {
                form.sessions.push(payload);
            }
            builderVisible.value = false;
        };

        // Update handleDrop to read dataTransfer
        const handleSessionDragStart = (event, session) => {
            const data = {
                mode: 'move',
                _id: session._id,
                type: session.type,
                title: session.title
            };
            event.dataTransfer.setData('json', JSON.stringify(data));
            event.dataTransfer.effectAllowed = 'move';
        };

        const handleDrop = (event, day) => {
            try {
                const jsonStr = event.dataTransfer.getData('json');
                if (jsonStr) {
                    const data = JSON.parse(jsonStr);
                    const absDay = getAbsDay(currentWeek.value, day);
                    
                    if (data.mode === 'move' && data._id) {
                        // Move existing session
                        const sessionIndex = form.sessions.findIndex(s => s._id === data._id);
                        if (sessionIndex !== -1) {
                            form.sessions[sessionIndex].day = absDay;
                            console.log('Session moved to day:', absDay);
                        }
                    } else {
                        // Add new session (existing logic)
                        console.log('Dropping new workout:', data, 'to day:', absDay);
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
                const dayOffset = s.day - startCurrent;
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

        const saveProgram = async (shouldRedirect = true) => {
            if (saving.value) return;
            saving.value = true;

            try {
                const formData = new FormData();
                formData.append('_method', 'PUT'); // Spoof PUT method
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

                // Use programData.id to ensure we have the ID in the URL
                // Fallback to PHP rendered ID if JS variable is missing
                const programId = programData.id || '{{ $program->id }}';
                const url = '{{ url("coach/programs") }}/' + programId;
                
                console.log('Updating program at:', url); // Debugging

                const res = await fetch(url, {
                    method: 'POST', // Must use POST for FormData with _method: PUT
                    headers: { 
                        'X-CSRF-TOKEN': csrf, 
                        'Accept':'application/json'
                        // No Content-Type
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
                    if (shouldRedirect) {
                        window.location.href = '{{ route("coach.programs.index") }}';
                    } else {
                        // Optional: Show a toast or small notification instead of full redirect
                        // alert('Program saved'); 
                    }
                } else {
                    alert((data && data.message) || 'Failed to update program');
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

        const duplicateWorkout = (sessionId) => {
            const session = form.sessions.find(s => s._id === sessionId);
            if (session) {
                const newSession = JSON.parse(JSON.stringify(session));
                newSession._id = generateId();
                form.sessions.push(newSession);
            }
        };

        const deleteWorkout = (sessionId) => {
            if (!confirm('Are you sure you want to delete this workout?')) {
                return;
            }
            
            const index = form.sessions.findIndex(s => s._id === sessionId);
            if (index !== -1) {
                form.sessions.splice(index, 1);
                builderVisible.value = false;
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
            getSessions, getSessionColor, getWorkoutsByType, handleDrop, 
            openBuilderAdd, openBuilderEdit, builderVisible, builderSummary, builderTotalDistance, saveBuilder, closeBuilder, builderForm,
            builderIsEditing, builderSessionId,
            copyWeek, updateWeeks, saveProgram, downloadTemplate, triggerImport, handleImport, fileInput,
            handleFileChange, showCustomModal, customWorkout, saveCustomWorkout, workoutTypes, masterWorkouts,
            cwForm, cwSummary, cwTotalDistance,
            handleDragStart, handleSessionDragStart, strengthOptions, addStrengthExercise, removeStrengthExercise, deleteWorkout, duplicateWorkout
        };
    }
}).mount('#program-builder-app');
</script>
@endpush
