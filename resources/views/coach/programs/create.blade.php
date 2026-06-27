@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Create Program')

@section('content')
<style>
.ck-editor__editable_inline {
    min-height: 280px !important;
    background-color: #0f172a !important; /* matches dark theme input */
    color: #ffffff !important;
    border-color: #334155 !important;
}
.ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
    border-color: #334155 !important;
}
.ck.ck-editor__main>.ck-editor__editable.ck-focused {
    border-color: #befd00 !important; /* neon border on focus */
}
.ck-toolbar {
    background-color: #1e293b !important;
    border-color: #334155 !important;
}
.ck.ck-button {
    color: #cbd5e1 !important;
}
.ck.ck-button:hover {
    background-color: #334155 !important;
    color: #ffffff !important;
}
.ck.ck-button.ck-on {
    background-color: #befd00 !important;
    color: #0f172a !important;
}
.ck.ck-dropdown .ck-dropdown__panel {
    background-color: #1e293b !important;
}
.ck-list {
    background-color: #1e293b !important;
}
.ck-list__item:hover {
    background-color: #334155 !important;
}
.ck-list__item button {
    color: #ffffff !important;
}
</style>
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
            <div class="flex gap-3 flex-wrap">
                <button type="button" @click="triggerImport" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import JSON
                </button>
                <input type="file" ref="fileInput" class="hidden" accept=".json" @change="handleImport">
                
                <button type="button" @click="triggerCsvImport" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Import CSV
                </button>
                <input type="file" ref="csvFileInput" class="hidden" accept=".csv" @change="handleCsvImport">

                <button type="button" @click="downloadCsvTemplate" class="text-xs text-neon hover:underline flex items-center gap-1 self-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    CSV Template
                </button>

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
                            <div class="w-full rounded-xl overflow-hidden text-white">
                                <div id="program_description_editor" class="min-h-[120px] px-4 py-3 text-sm"></div>
                            </div>
                            <textarea v-model="form.description" id="program_description" class="hidden"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Feature Image (Thumbnail)</label>
                            <div 
                                @dragover.prevent="dragOverThumbnail = true"
                                @dragleave.prevent="dragOverThumbnail = false"
                                @drop.prevent="handleThumbnailDrop"
                                @click="triggerThumbnailSelect"
                                :class="{'border-neon bg-neon/5': dragOverThumbnail, 'border-slate-700 bg-slate-900/40 hover:bg-slate-900/80': !dragOverThumbnail}"
                                class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all duration-300 flex flex-col items-center justify-center min-h-[140px] group relative overflow-hidden">
                                
                                <input type="file" ref="thumbnailInput" class="hidden" accept="image/*" @change="handleFileChange">
                                
                                <div v-if="!thumbnailPreview" class="space-y-2">
                                    <div class="p-3 bg-slate-800/80 rounded-full inline-block group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-bold text-white">Drag & drop your image here, or <span class="text-neon underline">browse</span></div>
                                    <div class="text-xs text-slate-400">Supports PNG, JPG, JPEG up to 2MB</div>
                                </div>
                                <div v-else class="relative w-full h-full min-h-[120px] flex items-center justify-center">
                                    <img :src="thumbnailPreview" v-on:error="thumbnailPreview = ''" class="max-h-[140px] rounded-lg object-cover border border-slate-700" />
                                    <div class="absolute inset-0 bg-black/60 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center rounded-lg gap-2">
                                        <span class="text-xs font-bold text-white bg-slate-900/80 px-2 py-1.5 rounded-lg border border-white/10">Click to change image</span>
                                        <button type="button" @click.stop="removeThumbnail" class="p-1.5 bg-red-500 hover:bg-red-600 rounded-lg text-white transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sticky top-4 z-30 shadow-2xl shadow-black/80 overflow-visible">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar border-b border-white/10">
                        @foreach(['easy_run' => 'Easy', 'long_run' => 'Long', 'interval' => 'Speed', 'tempo' => 'Tempo', 'time_trial' => 'Time Trial', 'strength' => 'Strength', 'rest' => 'Rest', 'custom' => 'Custom'] as $type => $label)
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
                        @foreach(['easy_run' => 'Easy', 'long_run' => 'Long', 'interval' => 'Speed', 'tempo' => 'Tempo', 'time_trial' => 'Time Trial', 'strength' => 'Strength', 'rest' => 'Rest'] as $type => $label)
                             @php
                                $colors = [
                                    'easy_run' => '#4CAF50',
                                    'long_run' => '#2196F3',
                                    'interval' => '#F44336',
                                    'tempo' => '#FFC107',
                                    'time_trial' => '#FF5722',
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
                                     @dragstart="handleDragStart($event, workout)"
                                     @click="handleSourceClick(workout)">
                                     
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
                                         @dragstart="handleDragStart($event, { type: 'rest', title: 'Rest Day', description: 'Total recovery', distance: 0, duration: '' })"
                                         @click="handleSourceClick({ type: 'rest', title: 'Rest Day', description: 'Total recovery', distance: 0, duration: '' })">
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
                                             @dragstart="handleDragStart($event, workout)"
                                             @click="handleSourceClick(workout)">
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
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-white">Week @{{ currentWeek }} of @{{ form.duration_weeks }}</span>
                        <button type="button" @click="addWeek" class="px-3 py-1 rounded-lg bg-neon text-dark font-black text-xs hover:bg-[#b3e600] transition-colors shadow-md shadow-neon/10">
                            + Add Week
                        </button>
                    </div>
                    <button type="button" @click="currentWeek = Math.min(form.duration_weeks, currentWeek + 1)" class="p-2 hover:text-white text-slate-400 transition" :disabled="currentWeek === form.duration_weeks">Next Week →</button>
                </div>

                <!-- Responsive Day Container -->
                <!-- Mobile Day List (visible only on mobile) -->
                <div class="block md:hidden space-y-3">
                    <div v-for="day in 7" :key="day" 
                         class="bg-slate-800/50 border border-slate-700 rounded-xl p-3 relative group transition-all flex flex-col gap-2"
                         @dragover.prevent @drop="handleDrop($event, day)">
                         
                         <div class="flex justify-between items-center border-b border-slate-700/40 pb-1.5 mb-1">
                             <div class="text-xs font-black text-slate-400 uppercase">Day @{{ day }}</div>
                             <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-neon hover:text-dark text-[10px] font-black text-slate-300 transition"
                                     @click.stop="openBuilderAdd(day)">
                                 + Add Workout
                             </button>
                         </div>
                         
                         <!-- Dropped Sessions (Mobile) -->
                         <div class="space-y-2">
                             <template v-if="getSessions(currentWeek, day).length > 0">
                                 <div v-for="(session, sIdx) in getSessions(currentWeek, day)" 
                                     :key="session._id"
                                     class="p-3 rounded-xl cursor-pointer hover:brightness-110 transition text-xs shadow-lg relative group flex flex-col gap-2"
                                     :style="{ backgroundColor: getSessionColor(session.type), borderLeft: '3px solid rgba(255,255,255,0.3)' }"
                                     draggable="true"
                                     @dragstart="handleSessionDragStart($event, session)"
                                     @dragover.prevent
                                     @drop.stop="handleSessionDrop($event, session)"
                                     @click.stop="openBuilderEdit(session)">
                                     
                                     <div class="flex justify-between items-start">
                                         <div class="font-bold text-white truncate pr-2">@{{ session.title || session.type }}</div>
                                         <div class="flex gap-2">
                                             <!-- Swap buttons -->
                                             <button type="button" class="text-white/80 hover:text-white text-[10px]" 
                                                     @click.stop="moveWorkoutUp(session)" title="Move Up">
                                                 <i class="fa-solid fa-arrow-up"></i>
                                             </button>
                                             <button type="button" class="text-white/80 hover:text-white text-[10px]" 
                                                     @click.stop="moveWorkoutDown(session)" title="Move Down">
                                                 <i class="fa-solid fa-arrow-down"></i>
                                             </button>
                                             <button type="button" class="text-white/80 hover:text-white" 
                                                     @click.stop="duplicateWorkout(session._id)" title="Duplicate">
                                                 <i class="fa-solid fa-copy"></i>
                                             </button>
                                             <button type="button" class="text-white/80 hover:text-white" 
                                                     @click.stop="deleteWorkout(session._id)" title="Delete">
                                                 <i class="fa-solid fa-trash-can"></i>
                                             </button>
                                         </div>
                                     </div>
                                     <div class="text-white/70 font-semibold">@{{ session.distance }} km</div>
                                 </div>
                             </template>
                             <div v-else class="text-slate-500 text-xs py-2 italic text-center">
                                 No workouts scheduled
                             </div>
                         </div>
                    </div>
                </div>

                <!-- Desktop Day Grid (visible on desktop) -->
                <div class="hidden md:block">
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
                                    @dragover.prevent
                                    @drop.stop="handleSessionDrop($event, session)"
                                    @click.stop="openBuilderEdit(session)">
                                    <div class="flex justify-between items-start">
                                        <div class="font-bold text-white truncate pr-2">@{{ session.title || session.type }}</div>
                                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                            <button type="button" class="text-white/70 hover:text-white" 
                                                    @click.stop="moveWorkoutUp(session)" title="Move Up">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </button>
                                            <button type="button" class="text-white/70 hover:text-white" 
                                                    @click.stop="moveWorkoutDown(session)" title="Move Down">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </button>
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
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="button" @click="copyWeek" class="text-xs text-neon hover:underline">Copy this week to next week</button>
                </div>
            </div>
        </form>

        <div v-if="builderVisible" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="closeBuilder"></div>
            <div class="relative z-10 max-w-2xl mx-auto my-10 rounded-2xl p-6 bg-slate-950 border border-slate-800 shadow-2xl shadow-black/70">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">Advanced Workout Builder</h3>
                    <button class="text-slate-400 hover:text-white" @click="closeBuilder">×</button>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Target Week</label>
                        <select v-model="builderTargetWeek" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:border-neon outline-none">
                            <option v-for="w in form.duration_weeks" :key="w" :value="w">Week @{{ w }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Target Day</label>
                        <select v-model="builderTargetDay" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:border-neon outline-none">
                            <option v-for="d in 7" :key="d" :value="d">Day @{{ d }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Type</label>
                        <select v-model="builderForm.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="easy_run">Easy Run</option>
                            <option value="long_run">Long Run</option>
                            <option value="tempo">Tempo</option>
                            <option value="interval">Intervals</option>
                            <option value="time_trial">Time Trial</option>
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
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Intensity</label>
                        <select v-model="builderForm.intensity" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase">Keterangan / Notes</label>
                        <textarea v-model="builderForm.description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-white text-sm focus:border-neon outline-none" placeholder="Lari santai, jaga HR tetap di zona 2..."></textarea>
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
                    <div v-else-if="builderForm.type==='time_trial'">
                        <div class="grid grid-cols-4 gap-2">
                            <select v-model="builderForm.timeTrial.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                <option value="distance">Distance</option>
                                <option value="time">Time</option>
                            </select>
                            <div v-if="builderForm.timeTrial.by==='distance'" class="flex gap-1">
                                <input type="number" step="any" v-model.number="builderForm.timeTrial.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist">
                                <select v-model="builderForm.timeTrial.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-xl px-1 py-2 text-white text-xs">
                                    <option value="km">km</option>
                                    <option value="m">m</option>
                                </select>
                            </div>
                            <input v-else type="text" v-model="builderForm.timeTrial.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:20:00">
                            <input type="text" v-model="builderForm.timeTrial.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss) / Optional">
                            <select v-model="builderForm.timeTrial.effort" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                <option value="max_effort">Max Effort</option>
                                <option value="race_pace">Race Pace</option>
                            </select>
                        </div>
                    </div>
                    <div v-else-if="builderForm.type==='rest'">
                        <div class="text-slate-400 text-sm">Rest Day</div>
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
                <h3 class="text-white font-bold text-lg mb-4">{{ __('Create Custom Workout') }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Title') }}</label>
                        <input type="text" v-model="customWorkout.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="{{ __('e.g. Hill Sprints') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Type') }}</label>
                        <select v-model="cwForm.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                            <option v-for="(label, type) in workoutTypes" :value="type">@{{ label }}</option>
                        </select>
                    </div>
                    <div class="glass-panel rounded-xl p-4">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2">{{ __('Main Set') }}</div>
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
                        <div v-else-if="cwForm.type==='time_trial'">
                            <div class="grid grid-cols-4 gap-2">
                                <select v-model="cwForm.timeTrial.by" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                    <option value="distance">Distance</option>
                                    <option value="time">Time</option>
                                </select>
                                <input v-if="cwForm.timeTrial.by==='distance'" type="number" step="0.1" v-model.number="cwForm.timeTrial.distanceKm" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Distance (km)">
                                <input v-else type="text" v-model="cwForm.timeTrial.duration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="00:20:00">
                                <input type="text" v-model="cwForm.timeTrial.pace" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Pace (mm:ss) / Optional">
                                <select v-model="cwForm.timeTrial.effort" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm">
                                    <option value="max_effort">Max Effort</option>
                                    <option value="race_pace">Race Pace</option>
                                </select>
                            </div>
                        </div>
                        <div v-else-if="cwForm.type==='rest'">
                            <div class="text-slate-400 text-sm">{{ __('Rest Day') }}</div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            {{ __('Summary') }}: <span class="text-white">@{{ cwSummary }}</span>
                            <div class="text-slate-400 mt-1">{{ __('Total Distance') }}: @{{ cwTotalDistance }} km</div>
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
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">{{ __('Intensity') }}</label>
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
                        <button type="button" @click="showCustomModal = false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm hover:bg-slate-700">{{ __('Cancel') }}</button>
                        <button type="button" @click="saveCustomWorkout" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm hover:bg-neon/90">{{ __('Create Workout') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Add Target Day Modal (Mobile Friendly) -->
        <div v-if="quickAddWorkout" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 animate-fade-in">
            <div class="relative max-w-sm w-full bg-slate-900 border border-slate-700 rounded-2xl p-6 shadow-2xl">
                <h3 class="text-white font-bold text-base mb-2">Add to Week @{{ currentWeek }}</h3>
                <p class="text-slate-400 text-xs mb-4">
                    Select which day to add <span class="text-neon font-semibold">"@{{ quickAddWorkout.title }}"</span>:
                </p>
                
                <div class="grid grid-cols-2 gap-2 mb-6">
                    <button v-for="day in 7" :key="day" type="button"
                            @click="confirmQuickAdd(day)"
                            class="py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold hover:bg-neon hover:text-dark hover:border-neon transition-all">
                        Day @{{ day }}
                    </button>
                </div>
                
                <div class="flex justify-between items-center border-t border-slate-800 pt-4">
                    <button type="button" @click="openInBuilderFromQuickAdd"
                            class="text-xs text-neon font-semibold hover:underline">
                        Edit Details First
                    </button>
                    <button type="button" @click="quickAddWorkout = null"
                            class="px-4 py-2 rounded-lg bg-slate-800 text-slate-400 text-xs hover:text-white transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
@include('layouts.components.advanced-builder-utils')
<script>
const { createApp, ref, reactive, computed, onMounted } = Vue;

createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const saving = ref(false);
        const currentWeek = ref(1);
        const activeTab = ref('easy_run');
        const showCustomModal = ref(false);
        const builderVisible = ref(false);
        const builderIsEditing = ref(false);
        const builderTargetDay = ref(1);
        const builderTargetWeek = ref(1);
        const builderSessionId = ref(null);
        const fileInput = ref(null);
        const csvFileInput = ref(null);
        const quickAddWorkout = ref(null);
        const dragOverThumbnail = ref(false);
        const thumbnailPreview = ref(null);
        const thumbnailInput = ref(null);
        
        // Master Workouts from Backend
        const initialMasterWorkouts = @json($masterWorkouts ?? []);
        const masterWorkouts = reactive(Array.isArray(initialMasterWorkouts) && initialMasterWorkouts.length === 0 ? {} : initialMasterWorkouts);
        
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
            timeTrial: { by: 'distance', distanceKm: 5, duration: '', pace: '', effort: 'max_effort' },
            interval: { reps: 6, by: 'distance', repDistanceKm: 0.8, repTime: '', pace: '', recovery: 'Jog 2:00' },
            strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
        });
        
        const builderForm = reactive({
            type: 'easy_run',
            title: '',
            intensity: 'low',
            description: '',
            warmup: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' },
            cooldown: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' },
            main: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
            longRun: { fastFinish: { enabled: false, distance: 0, unit: 'km', pace: '' } },
            tempo: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' },
            timeTrial: { by: 'distance', distance: 5, unit: 'km', duration: '', pace: '', effort: 'max_effort' },
            interval: { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' },
            strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
        });

        const workoutTypes = {
            easy_run: 'Easy',
            long_run: 'Long',
            interval: 'Speed',
            tempo: 'Tempo',
            time_trial: 'Time Trial',
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

        onMounted(() => {
            if (window.ClassicEditor && document.querySelector('#program_description_editor')) {
                ClassicEditor
                    .create(document.querySelector('#program_description_editor'), {
                        toolbar: [
                            'heading',
                            '|',
                            'bold', 'italic', 'underline', 'link',
                            'fontColor', 'fontBackgroundColor',
                            '|',
                            'bulletedList', 'numberedList',
                            'blockQuote',
                            '|',
                            'insertTable',
                            'imageUpload', 'imageInsert',
                            '|',
                            'alignment',
                            'removeFormat',
                            '|',
                            'undo', 'redo'
                        ]
                    })
                    .then(editor => {
                        editor.setData(form.description || '');
                        editor.model.document.on('change:data', () => {
                            form.description = editor.getData();
                        });
                    })
                    .catch(error => console.error(error));
            }
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
                time_trial: '#FF5722',
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
                    { name: 'Plank', sets: '3', reps: '45-60s', equipment: 'Bodyweight' },
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

        const openBuilderAdd = (day) => {
            builderIsEditing.value = false;
            builderTargetWeek.value = currentWeek.value;
            builderTargetDay.value = day;
            builderSessionId.value = null;
            Object.assign(builderForm, { type: 'easy_run', title: '', description: '' });
            Object.assign(builderForm.warmup, { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' });
            Object.assign(builderForm.cooldown, { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' });
            Object.assign(builderForm.main, { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' });
            Object.assign(builderForm.longRun.fastFinish, { enabled: false, distance: 0, unit: 'km', pace: '' });
            Object.assign(builderForm.tempo, { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' });
            Object.assign(builderForm.timeTrial, { by: 'distance', distance: 5, unit: 'km', duration: '', pace: '', effort: 'max_effort' });
            Object.assign(builderForm.interval, { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' });
            Object.assign(builderForm.strength, { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] });
            builderVisible.value = true;
        };

        const openBuilderEdit = (session) => {
            builderIsEditing.value = true;
            builderSessionId.value = session._id;
            const abs = session.day;
            const week = Math.ceil(abs / 7);
            const day = abs - (week - 1) * 7;
            builderTargetWeek.value = week;
            builderTargetDay.value = day;
            builderForm.type = session.type;
            builderForm.title = session.title || '';
            builderForm.description = session.description || '';
            try {
                if (session.advanced_config) {
                    const cfg = typeof session.advanced_config === 'string' ? JSON.parse(session.advanced_config) : session.advanced_config;
                    Object.assign(builderForm, { ...builderForm, ...cfg });
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
                    if (builderForm.timeTrial && builderForm.timeTrial.distanceKm !== undefined) {
                        builderForm.timeTrial.distance = builderForm.timeTrial.distanceKm;
                        builderForm.timeTrial.unit = 'km';
                    }
                    if (builderForm.interval && builderForm.interval.repDistanceKm !== undefined) {
                        builderForm.interval.repDistance = builderForm.interval.repDistanceKm;
                        builderForm.interval.repDistanceUnit = 'km';
                    }
                } else {
                    builderForm.main.distance = session.distance || 0;
                    builderForm.main.unit = 'km';
                }
            } catch(e){}
            builderVisible.value = true;
        };

        const builderSummary = computed(() => RLBuilderUtils.buildSummary(builderForm));

        const builderTotalDistance = computed(() => RLBuilderUtils.computeTotalDistance(builderForm));

        const closeBuilder = () => { builderVisible.value = false; };

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

        const handleSourceClick = (workout) => {
            quickAddWorkout.value = workout;
        };

        const confirmQuickAdd = (day) => {
            if (!quickAddWorkout.value) return;
            const absDay = getAbsDay(currentWeek.value, day);
            const newSession = {
                _id: generateId(),
                day: absDay,
                type: quickAddWorkout.value.type,
                title: quickAddWorkout.value.title || (quickAddWorkout.value.type === 'rest' ? 'Rest Day' : 'Workout'),
                distance: parseFloat(quickAddWorkout.value.default_distance) || 0,
                description: quickAddWorkout.value.description || '',
                duration: quickAddWorkout.value.default_duration || ''
            };
            form.sessions.push(newSession);
            quickAddWorkout.value = null;
        };

        const openInBuilderFromQuickAdd = () => {
            if (!quickAddWorkout.value) return;
            const wo = quickAddWorkout.value;
            quickAddWorkout.value = null;
            
            builderIsEditing.value = false;
            builderTargetWeek.value = currentWeek.value;
            builderTargetDay.value = 1;
            builderSessionId.value = null;
            
            // Populate builderForm
            Object.assign(builderForm, { 
                type: wo.type, 
                title: wo.title || '', 
                description: wo.description || '',
                intensity: 'low'
            });
            Object.assign(builderForm.warmup, { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' });
            Object.assign(builderForm.cooldown, { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' });
            Object.assign(builderForm.main, { by: 'distance', distance: wo.default_distance || 0, unit: 'km', duration: wo.default_duration || '', pace: '' });
            Object.assign(builderForm.longRun.fastFinish, { enabled: false, distance: 0, unit: 'km', pace: '' });
            Object.assign(builderForm.tempo, { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' });
            Object.assign(builderForm.timeTrial, { by: 'distance', distance: 5, unit: 'km', duration: '', pace: '', effort: 'max_effort' });
            Object.assign(builderForm.interval, { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' });
            Object.assign(builderForm.strength, { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] });
            
            builderVisible.value = true;
        };

        const saveBuilder = () => {
            const absDay = getAbsDay(builderTargetWeek.value, builderTargetDay.value);
            const payload = {
                _id: builderSessionId.value || generateId(),
                day: absDay,
                type: builderForm.type,
                title: builderForm.title || builderSummary.value,
                distance: builderTotalDistance.value,
                description: builderForm.description || builderSummary.value,
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

        const minutesToHHMMSS = (min) => {
            const totalSec = Math.round((min || 0) * 60);
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = totalSec % 60;
            return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
        };

        const cwSummary = computed(() => RLBuilderUtils.buildSummary(cwForm));

        const cwTotalDistance = computed(() => RLBuilderUtils.computeTotalDistance(cwForm));

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
                            } else if (cwForm.type==='time_trial') {
                                if (cwForm.timeTrial.by==='time') return cwForm.timeTrial.duration || '';
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
                    // Add to local list
                    if (!masterWorkouts[cwForm.type]) {
                        masterWorkouts[cwForm.type] = [];
                    }
                    masterWorkouts[cwForm.type].push(data.workout);
                    
                    showCustomModal.value = false;
                    
                    // Reset form
                    Object.assign(customWorkout, {
                        title: '',
                        description: '',
                        default_distance: 0,
                        default_duration: '',
                        is_public: false
                    });
                    Object.assign(cwForm, {
                        type: 'easy_run',
                        main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
                        tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
                        timeTrial: { by: 'distance', distanceKm: 5, duration: '', pace: '', effort: 'max_effort' },
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
        
        const addWeek = () => {
            form.duration_weeks = Number(form.duration_weeks) + 1;
            updateWeeks();
            currentWeek.value = form.duration_weeks;
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

        const moveWorkoutUp = (session) => {
            const idx = form.sessions.findIndex(s => s._id === session._id);
            if (idx === -1) return;
            
            const daySessions = form.sessions.filter(s => s.day === session.day);
            const subIdx = daySessions.findIndex(s => s._id === session._id);
            if (subIdx <= 0) return; // Already at the top of the day
            
            const prevSession = daySessions[subIdx - 1];
            const prevIdx = form.sessions.findIndex(s => s._id === prevSession._id);
            
            // Swap in form.sessions
            const temp = form.sessions[idx];
            form.sessions[idx] = form.sessions[prevIdx];
            form.sessions[prevIdx] = temp;
        };

        const moveWorkoutDown = (session) => {
            const idx = form.sessions.findIndex(s => s._id === session._id);
            if (idx === -1) return;
            
            const daySessions = form.sessions.filter(s => s.day === session.day);
            const subIdx = daySessions.findIndex(s => s._id === session._id);
            if (subIdx === -1 || subIdx >= daySessions.length - 1) return; // Already at the bottom of the day
            
            const nextSession = daySessions[subIdx + 1];
            const nextIdx = form.sessions.findIndex(s => s._id === nextSession._id);
            
            // Swap in form.sessions
            const temp = form.sessions[idx];
            form.sessions[idx] = form.sessions[nextIdx];
            form.sessions[nextIdx] = temp;
        };

        const handleSessionDrop = (event, targetSession) => {
            event.preventDefault();
            event.stopPropagation();
            try {
                const jsonStr = event.dataTransfer.getData('json');
                if (jsonStr) {
                    const data = JSON.parse(jsonStr);
                    
                    if (data.mode === 'move' && data._id) {
                        const draggedId = data._id;
                        if (draggedId === targetSession._id) return;
                        
                        const draggedIdx = form.sessions.findIndex(s => s._id === draggedId);
                        if (draggedIdx === -1) return;
                        
                        const [draggedSession] = form.sessions.splice(draggedIdx, 1);
                        draggedSession.day = targetSession.day;
                        
                        const targetIdx = form.sessions.findIndex(s => s._id === targetSession._id);
                        if (targetIdx !== -1) {
                            form.sessions.splice(targetIdx, 0, draggedSession);
                        } else {
                            form.sessions.push(draggedSession);
                        }
                    } else {
                        // Drop new workout on top of target session
                        const targetIdx = form.sessions.findIndex(s => s._id === targetSession._id);
                        if (targetIdx !== -1) {
                            const newSession = {
                                _id: generateId(),
                                day: targetSession.day,
                                type: data.type,
                                title: data.title,
                                distance: parseFloat(data.distance) || 0,
                                description: data.description || '',
                                duration: data.duration || ''
                            };
                            form.sessions.splice(targetIdx, 0, newSession);
                        }
                    }
                }
            } catch (e) {
                console.error('Session drop error:', e);
            }
        };

        const triggerCsvImport = () => {
            csvFileInput.value.click();
        };

        const handleCsvImport = async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('csv_file', file);

            try {
                const res = await fetch('{{ route("coach.programs.import-csv") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
                    body: formData
                });
                
                let data;
                try {
                    data = await res.json();
                } catch (jsonError) {
                    console.error('Failed to parse CSV response:', jsonError);
                    alert('Server returned an invalid response. Please check your file.');
                    return;
                }
                
                if (res.ok) {
                    if (data.sessions && Array.isArray(data.sessions)) {
                        form.sessions = data.sessions.map(s => ({...s, _id: s._id || generateId()}));
                        if (data.duration_weeks) form.duration_weeks = data.duration_weeks;
                        alert('CSV Program imported successfully!');
                    } else {
                        alert('Invalid CSV structure: Missing "sessions" array.');
                    }
                } else {
                    alert(data.message || (data.errors && data.errors.csv_file ? data.errors.csv_file[0] : 'Import failed'));
                }
            } catch (e) {
                console.error('Import error:', e);
                alert('An error occurred during import. See console for details.');
            } finally {
                e.target.value = ''; // Reset input
            }
        };

        const downloadCsvTemplate = () => {
            const csvRows = [
                ['day', 'type', 'distance', 'duration', 'description'],
                [1, 'easy_run', 5, '00:35:00', 'Easy introductory run'],
                [2, 'interval', 0, '00:45:00', 'Speed intervals: 6x800m'],
                [3, 'easy_run', 5, '00:35:00', 'Recovery run'],
                [4, 'tempo', 8, '00:45:00', 'Tempo run at target pace'],
                [5, 'strength', 0, '00:30:00', 'Core and leg strength'],
                [6, 'long_run', 12, '01:10:00', 'Long endurance run'],
                [7, 'rest', 0, '', 'Rest and recovery'],
                [25, 'time_trial', 5, '00:20:00', 'Time Trial 5K Max Effort']
            ];

            const csvContent = "data:text/csv;charset=utf-8," 
                + csvRows.map(e => e.map(val => `"${val}"`).join(",")).join("\n");
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "ruanglari_program_template.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        const setThumbnailFile = (file) => {
            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file.');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size exceeds 2MB limit.');
                return;
            }
            form.thumbnail = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                thumbnailPreview.value = e.target.result;
            };
            reader.readAsDataURL(file);
        };

        const handleFileChange = (event) => {
            const file = event.target.files[0];
            if (file) {
                setThumbnailFile(file);
            }
        };

        const handleThumbnailDrop = (event) => {
            dragOverThumbnail.value = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                setThumbnailFile(file);
            }
        };

        const triggerThumbnailSelect = () => {
            thumbnailInput.value.click();
        };

        const removeThumbnail = () => {
            form.thumbnail = null;
            thumbnailPreview.value = null;
            if (thumbnailInput.value) {
                thumbnailInput.value.value = '';
            }
        };

        return { 
            form, saving, currentWeek, activeTab, totalVolume, 
            getSessions, getSessionColor, getWorkoutsByType, handleDrop, handleSessionDrop, handleDragStart, handleSessionDragStart,
            openBuilderAdd, openBuilderEdit, builderVisible, builderSummary, builderTotalDistance, saveBuilder, closeBuilder, builderForm,
            deleteWorkout, duplicateWorkout, builderIsEditing, builderSessionId, builderTargetWeek, builderTargetDay,
            copyWeek, updateWeeks, addWeek, saveProgram, downloadTemplate, triggerImport, handleImport, fileInput,
            csvFileInput, triggerCsvImport, handleCsvImport, downloadCsvTemplate,
            dragOverThumbnail, thumbnailPreview, thumbnailInput, handleThumbnailDrop, triggerThumbnailSelect, removeThumbnail,
            handleFileChange, showCustomModal, customWorkout, saveCustomWorkout, workoutTypes,
            masterWorkouts, cwForm, cwSummary, cwTotalDistance,
            strengthOptions, addStrengthExercise, removeStrengthExercise,
            moveWorkoutUp, moveWorkoutDown, quickAddWorkout, handleSourceClick, confirmQuickAdd, openInBuilderFromQuickAdd
        };
    }
}).mount('#program-builder-app');
</script>
@endpush
