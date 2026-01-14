@extends('layouts.pacerhub')

@section('title', 'Setor Aktivitas - Ruang Lari')

@push('styles')
    <style>
        .bg-fixed-image {
            background-image: url('https://res.cloudinary.com/dslfarxct/images/v1760944069/pelari-kece/pelari-kece.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        /* Custom File Input */
        input[type="file"]::file-selector-button {
            display: none;
        }
        /* Hide number input arrows */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
@endpush

@section('content')
    <div id="submit-app" class="relative z-10 w-full min-h-screen pb-24 pt-20 px-4" v-cloak>
        
        @guest
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md"></div>
            <div class="relative bg-slate-800 border border-slate-700 rounded-3xl p-8 w-full max-w-sm shadow-2xl z-10 transform transition-all scale-100 opacity-100">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-slate-700 mx-auto flex items-center justify-center mb-4 ring-4 ring-slate-800">
                        <i class="fas fa-lock text-neon text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-white italic tracking-tighter">LOGIN REQUIRED</h2>
                    <p class="text-slate-400 text-sm mt-2">Silakan login untuk melaporkan aktivitas.</p>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Email</label>
                        <input type="email" v-model="loginForm.email" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="your@email.com">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Password</label>
                        <input type="password" v-model="loginForm.password" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="••••••••">
                    </div>
                    
                    <div v-if="loginError" class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs text-center font-bold p-3 rounded-lg">
                        @{{ loginError }}
                    </div>

                    <div class="flex justify-center my-2">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                    </div>

                    <button type="submit" :disabled="isLoggingIn" class="w-full bg-neon text-slate-900 font-black text-lg py-3 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 mt-2">
                        <span v-if="!isLoggingIn">LOGIN SEKARANG</span>
                        <span v-else><i class="fas fa-circle-notch fa-spin"></i> VERIFYING...</span>
                    </button>
                </form>
                
                <div class="mt-6 text-center space-y-3">
                    <p class="text-slate-500 text-xs">
                        Belum punya akun? <a href="{{ route('register') }}" class="text-neon hover:text-white font-bold transition-colors">Daftar di sini</a>
                    </p>
                </div>
            </div>
        </div>
        @endguest

        <div class="fixed inset-0 z-[-1] bg-fixed-image"></div>
        <div class="fixed inset-0 z-[-1] bg-slate-900/90"></div>

        <div class="max-w-md mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-white italic tracking-tighter">SUBMIT RUN</h1>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">40 Days Challenge</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center border border-slate-700 shadow-lg">
                    <i class="fas fa-running text-neon text-lg"></i>
                </div>
            </div>

            <!-- Form Card -->
            <div class="glass-panel rounded-3xl p-1 shadow-2xl overflow-hidden">
                <form @submit.prevent="submitActivity" class="bg-slate-900/50 p-5 space-y-6">
                    
                    <!-- Advanced Builder Button -->
                    <div class="flex justify-end">
                        <button type="button" @click="openBuilder" class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-xs font-bold text-neon hover:bg-slate-700 transition flex items-center gap-2">
                            <i class="fas fa-layer-group"></i> Workout Builder
                        </button>
                    </div>

                    <!-- Distance Input (Hero) -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Distance (KM)</label>
                        <div class="relative">
                            <input type="number" step="0.01" v-model="form.distance" placeholder="0.00" required inputmode="decimal"
                                class="w-full bg-transparent border-b-2 border-slate-700 text-5xl font-black text-white placeholder-slate-700 focus:outline-none focus:border-neon transition-colors py-2 px-1 text-center font-mono">
                        </div>
                    </div>

                    <!-- Duration & Pace Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Duration -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Duration</label>
                            <div class="flex gap-1 items-center bg-slate-800/80 rounded-xl p-2 border border-slate-700/50">
                                <div class="flex-1">
                                    <input type="number" v-model="form.duration_hours" placeholder="00" min="0" inputmode="numeric"
                                        class="w-full bg-transparent text-center text-lg font-bold text-white focus:outline-none placeholder-slate-600">
                                    <div class="text-[9px] text-center text-slate-500 uppercase">Hr</div>
                                </div>
                                <span class="text-slate-500 font-bold">:</span>
                                <div class="flex-1">
                                    <input type="number" v-model="form.duration_minutes" placeholder="00" min="0" max="59" inputmode="numeric"
                                        class="w-full bg-transparent text-center text-lg font-bold text-white focus:outline-none placeholder-slate-600">
                                    <div class="text-[9px] text-center text-slate-500 uppercase">Min</div>
                                </div>
                                <span class="text-slate-500 font-bold">:</span>
                                <div class="flex-1">
                                    <input type="number" v-model="form.duration_seconds" placeholder="00" min="0" max="59" inputmode="numeric"
                                        class="w-full bg-transparent text-center text-lg font-bold text-white focus:outline-none placeholder-slate-600">
                                    <div class="text-[9px] text-center text-slate-500 uppercase">Sec</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pace (Calculated) -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Avg Pace</label>
                            <div class="h-[62px] flex items-center justify-center bg-slate-800/40 rounded-xl border border-dashed border-slate-700">
                                <div class="text-center">
                                    <div class="text-xl font-black text-neon font-mono">@{{ calculatedPace }}</div>
                                    <div class="text-[9px] text-slate-500 uppercase">/km</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Strava -->
                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Date</label>
                            <input type="date" v-model="form.date" required
                                class="w-full bg-slate-800/80 border border-slate-700/50 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Time</label>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" v-model="form.time_hour" placeholder="HH" min="1" max="12" inputmode="numeric"
                                    class="w-full bg-slate-800/80 border border-slate-700/50 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors text-center">
                                <input type="number" v-model="form.time_minute" placeholder="MM" min="0" max="59" inputmode="numeric"
                                    class="w-full bg-slate-800/80 border border-slate-700/50 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors text-center">
                                <select v-model="form.time_ampm" class="w-full bg-slate-800/80 border border-slate-700/50 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Strava / Activity Link</label>
                            <div class="relative">
                                <i class="fab fa-strava absolute left-4 top-3.5 text-orange-500"></i>
                                <input type="url" v-model="form.stravaLink" placeholder="Paste link activity..."
                                    class="w-full bg-slate-800/80 border border-slate-700/50 rounded-xl pl-10 pr-4 py-3 text-white text-sm focus:outline-none focus:border-orange-500 transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Proof Screenshot</label>
                        <div class="relative group cursor-pointer">
                            <input type="file" @change="handleFileUpload" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" :required="!previewImage">
                            
                            <div class="relative overflow-hidden border-2 border-dashed border-slate-700 bg-slate-800/30 rounded-2xl h-40 flex flex-col items-center justify-center transition-all group-hover:border-neon group-hover:bg-slate-800/50">
                                <div v-if="!previewImage" class="text-center p-4">
                                    <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-3 shadow-lg group-hover:scale-110 transition-transform">
                                        <i class="fas fa-camera text-slate-400 group-hover:text-neon text-lg"></i>
                                    </div>
                                    <p class="text-xs text-slate-300 font-medium">Tap to upload screenshot</p>
                                    <p class="text-[10px] text-slate-500 mt-1">Supports JPG, PNG</p>
                                </div>
                                <img v-else :src="previewImage" class="absolute inset-0 w-full h-full object-cover">
                                
                                <div v-if="previewImage" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-white text-xs font-bold bg-black/50 px-3 py-1 rounded-full backdrop-blur-sm">Change Photo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="isSubmitting"
                        class="w-full bg-neon text-slate-900 font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-4">
                        <span v-if="!isSubmitting">SUBMIT ACTIVITY</span>
                        <span v-else><i class="fas fa-circle-notch fa-spin"></i> PROCESSING...</span>
                    </button>
                    
                    <div v-if="message" :class="messageType === 'success' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'" class="text-xs text-center font-bold p-3 rounded-lg border">
                        @{{ message }}
                    </div>

                </form>
            </div>

            <!-- History Section (Accordion Style for Mobile) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Recent Activities</h3>
                    <a href="{{ route('runner.calendar') }}" class="text-xs text-neon hover:text-white transition-colors font-medium">View Calendar</a>
                </div>

                <div class="space-y-3">
                    @forelse($activities->take(5) as $activity)
                        <div class="bg-slate-800/60 backdrop-blur-md rounded-xl p-4 border border-slate-700/50 flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center text-slate-300 font-bold text-xs flex-shrink-0">
                                    {{ \Carbon\Carbon::parse($activity->date)->format('d') }}<br>
                                    {{ \Carbon\Carbon::parse($activity->date)->format('M') }}
                                </div>
                                <div>
                                    <div class="text-white font-bold text-base">{{ $activity->distance }} KM</div>
                                    <div class="text-xs text-slate-400 font-mono">
                                        {{ gmdate('H:i:s', $activity->duration_seconds) }}
                                        @if(!empty($activity->activity_time))
                                            • {{ \Carbon\Carbon::parse($activity->date.' '.$activity->activity_time)->format('h:i A') }}
                                        @endif
                                    </div>
                                    @if($activity->type && $activity->type != 'easy_run')
                                        <div class="text-[10px] text-neon uppercase mt-1">{{ str_replace('_', ' ', $activity->type) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                @if($activity->status == 'approved')
                                    <div class="w-8 h-8 rounded-full bg-green-500/20 text-green-400 flex items-center justify-center border border-green-500/30">
                                        <i class="fas fa-check text-xs"></i>
                                    </div>
                                @elseif($activity->status == 'rejected')
                                    <div class="w-8 h-8 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center border border-red-500/30">
                                        <i class="fas fa-times text-xs"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center border border-yellow-500/30">
                                        <i class="fas fa-hourglass-half text-xs"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 bg-slate-800/30 rounded-xl border border-dashed border-slate-700">
                            <p class="text-slate-500 text-xs">No activities submitted yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- WORKOUT BUILDER MODAL -->
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
                            <option value="custom">Custom / Mixed</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Title</label>
                        <input v-model="builderForm.title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white" placeholder="Optional">
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" v-if="builderForm.type !== 'custom' && builderForm.type !== 'strength'">
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
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4" v-if="builderForm.type !== 'custom'">
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
                        <div class="space-y-3">
                            <div class="text-xs text-slate-400 uppercase font-bold">Add Interval Set</div>
                            <div class="grid grid-cols-6 gap-2 bg-slate-800/50 p-3 rounded-xl border border-slate-700/50 items-end">
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase block mb-1">Reps</label>
                                    <input type="number" v-model.number="builderForm.interval.newReps" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-white text-sm" placeholder="6">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase block mb-1">Type</label>
                                    <select v-model="builderForm.interval.newBy" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-1 py-2 text-white text-xs">
                                        <option value="distance">Dist</option>
                                        <option value="time">Time</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-[10px] text-slate-400 uppercase block mb-1">Value</label>
                                    <input v-if="builderForm.interval.newBy==='distance'" type="number" step="0.1" v-model.number="builderForm.interval.newDist" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-white text-sm" placeholder="km">
                                    <input v-else type="text" v-model="builderForm.interval.newTime" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-white text-sm" placeholder="00:03:00">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase block mb-1">Pace</label>
                                    <input type="text" v-model="builderForm.interval.newPace" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-white text-sm" placeholder="mm:ss">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase block mb-1">Rec</label>
                                    <input type="text" v-model="builderForm.interval.newRec" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-white text-sm" placeholder="2:00">
                                </div>
                            </div>
                            <button type="button" class="w-full py-2 bg-slate-700 hover:bg-neon hover:text-dark text-white text-xs font-bold rounded-lg transition" @click="addIntervalSet">
                                <i class="fas fa-plus mr-1"></i> Add Set
                            </button>

                            <!-- List of Sets -->
                            <div class="space-y-2" v-if="builderForm.interval.sets && builderForm.interval.sets.length">
                                <div v-for="(set, idx) in builderForm.interval.sets" :key="idx" class="bg-slate-800 border border-slate-700 rounded-lg p-3 flex justify-between items-center group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-400">@{{ idx + 1 }}</div>
                                        <div>
                                            <div class="text-white text-sm font-bold">
                                                @{{ set.reps }} x @{{ set.by === 'distance' ? set.dist + ' km' : set.time }}
                                                <span v-if="set.pace" class="text-neon ml-1">@@{{ set.pace }}</span>
                                            </div>
                                            <div class="text-xs text-slate-500">Recovery: @{{ set.rec }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="text-slate-500 hover:text-red-400 opacity-0 group-hover:opacity-100 transition" @click="removeIntervalSet(idx)">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-4 text-xs text-slate-500 border border-dashed border-slate-700 rounded-lg">
                                No interval sets added yet.
                            </div>
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
                    <div v-else-if="builderForm.type==='custom'">
                        <div class="space-y-3">
                            <div class="text-xs text-slate-400">Add dynamic workout segments (e.g. Run 5k, Swim 20m, Plank 2min)</div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-slate-800/50 p-3 rounded-xl border border-slate-700/50">
                                <div class="md:col-span-2">
                                    <input type="text" v-model="builderForm.custom.segmentName" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Segment Name (e.g. Warm up Run)">
                                </div>
                                <input type="number" step="0.01" v-model.number="builderForm.custom.segmentDistance" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Dist (km)">
                                <input type="text" v-model="builderForm.custom.segmentDuration" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Duration (e.g. 15:00)">
                                <div class="md:col-span-3">
                                    <input type="text" v-model="builderForm.custom.segmentNote" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white text-sm" placeholder="Notes (Pace, Intensity, etc)">
                                </div>
                                <button type="button" class="w-full bg-neon text-dark font-bold text-xs rounded-xl hover:bg-neon/90 transition" @click="addCustomSegment">ADD</button>
                            </div>

                            <div class="space-y-2" v-if="builderForm.custom.segments && builderForm.custom.segments.length">
                                <div v-for="(seg, idx) in builderForm.custom.segments" :key="idx" class="bg-slate-800 border border-slate-700 rounded-lg p-3 flex justify-between items-start">
                                    <div>
                                        <div class="font-bold text-white text-sm">@{{ seg.name }}</div>
                                        <div class="text-xs text-slate-400">
                                            <span v-if="seg.distance > 0">@{{ seg.distance }} km</span>
                                            <span v-if="seg.distance > 0 && seg.duration"> • </span>
                                            <span v-if="seg.duration">@{{ seg.duration }}</span>
                                        </div>
                                        <div v-if="seg.note" class="text-xs text-slate-500 italic mt-1">"@{{ seg.note }}"</div>
                                    </div>
                                    <button type="button" class="text-slate-400 hover:text-red-400 transition" @click="removeCustomSegment(idx)">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 glass-panel rounded-xl p-4">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-2">Summary</div>
                    <div class="text-white text-sm">@{{ builderSummary }}</div>
                    <div class="text-slate-400 text-xs mt-1">Total Distance: @{{ builderTotalDistance }} km</div>
                </div>
                <div class="flex justify-end items-center mt-4 gap-2">
                    <button type="button" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm" @click="closeBuilder">Cancel</button>
                    <button type="button" class="px-4 py-2 rounded-lg bg-neon text-dark font-bold text-sm" @click="saveBuilderFromModal">Save & Apply</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        const { createApp, ref, reactive, computed, watch } = Vue;

        createApp({
            setup() {
                const isSubmitting = ref(false);
                const previewImage = ref(null);
                const message = ref('');
                const messageType = ref('success');
                
                // Login State
                const loginForm = ref({ email: '', password: '' });
                const isLoggingIn = ref(false);
                const loginError = ref('');

                const handleLogin = async () => {
                    isLoggingIn.value = true;
                    loginError.value = '';
                    
                    const recaptchaResponse = grecaptcha.getResponse();
                    if (!recaptchaResponse) {
                        loginError.value = 'Mohon selesaikan verifikasi reCAPTCHA.';
                        isLoggingIn.value = false;
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('login') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                ...loginForm.value,
                                'g-recaptcha-response': recaptchaResponse
                            })
                        });
                        
                        const data = await response.json();

                        if (response.ok || (data.success && !data.errors)) {
                             window.location.reload();
                        } else {
                             loginError.value = data.message || 'Login failed. Please check your credentials.';
                             grecaptcha.reset();
                        }
                    } catch (e) {
                        loginError.value = 'An error occurred. Please try again.';
                        grecaptcha.reset();
                    } finally {
                        isLoggingIn.value = false;
                    }
                };

                const form = ref({
                    date: new Date().toISOString().substr(0, 10),
                    distance: '',
                    duration_hours: '',
                    duration_minutes: '',
                    duration_seconds: '',
                    time_hour: 7,
                    time_minute: 0,
                    time_ampm: 'AM',
                    stravaLink: '',
                    image: null,
                    type: 'easy_run',
                    advanced_config: null
                });

                // --- BUILDER LOGIC START ---
                const builderVisible = ref(false);
                const builderForm = reactive({
                    type: 'easy_run',
                    title: '',
                    intensity: 'low',
                    warmup: { enabled: false, by: 'distance', distanceKm: 0, duration: '' },
                    cooldown: { enabled: false, by: 'distance', distanceKm: 0, duration: '' },
                    main: { by: 'distance', distanceKm: 0, duration: '', pace: '' },
                    longRun: { fastFinish: { enabled: false, distanceKm: 0, pace: '' } },
                    tempo: { by: 'distance', distanceKm: 0, duration: '', pace: '', effort: 'moderate' },
                    interval: { 
                        sets: [], // Array of { reps, by, distance/time, pace, recovery }
                        // Temporary inputs for adding new set
                        newReps: 6, newBy: 'distance', newDist: 0.8, newTime: '', newPace: '', newRec: 'Jog 2:00' 
                    },
                    strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] },
                    custom: { segmentName: '', segmentDistance: '', segmentDuration: '', segmentNote: '', segments: [] }
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

                const addIntervalSet = () => {
                    // Access builderForm.interval directly
                    const i = builderForm.interval;
                    
                    // Ensure defaults if undefined
                    if (!i.newReps) i.newReps = 1;
                    if (!i.newBy) i.newBy = 'distance';
                    
                    const set = {
                        reps: parseInt(i.newReps) || 1,
                        by: i.newBy,
                        dist: parseFloat(i.newDist) || 0,
                        time: i.newTime || '',
                        pace: i.newPace || '',
                        rec: i.newRec || ''
                    };
                    
                    // Basic validation
                    if (set.reps <= 0) return;
                    if (set.by === 'distance' && set.dist <= 0) {
                        alert('Please enter a valid distance');
                        return;
                    }
                    if (set.by === 'time' && !set.time) {
                        alert('Please enter a valid duration');
                        return;
                    }
                    
                    if (!i.sets) i.sets = [];
                    i.sets.push(set);
                    
                    // Reset input defaults
                    i.newReps = 6;
                    i.newDist = 0.8;
                    i.newTime = '';
                    // Keep pace/rec as they are often same
                };

                const removeIntervalSet = (idx) => {
                    if (!builderForm.interval.sets) return;
                    builderForm.interval.sets.splice(idx, 1);
                };

                const addCustomSegment = () => {
                    if (!builderForm.custom.segmentName) return;
                    
                    const segment = {
                        name: builderForm.custom.segmentName,
                        distance: parseFloat(builderForm.custom.segmentDistance) || 0,
                        duration: builderForm.custom.segmentDuration || '',
                        note: builderForm.custom.segmentNote || ''
                    };
                    
                    if (!builderForm.custom.segments) builderForm.custom.segments = [];
                    builderForm.custom.segments.push(segment);
                    
                    // Reset inputs
                    builderForm.custom.segmentName = '';
                    builderForm.custom.segmentDistance = '';
                    builderForm.custom.segmentDuration = '';
                    builderForm.custom.segmentNote = '';
                };

                const removeCustomSegment = (idx) => {
                    if (!builderForm.custom.segments) return;
                    builderForm.custom.segments.splice(idx, 1);
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

                const builderSummary = computed(() => {
                    const parts = [];
                    if (builderForm.warmup.enabled) {
                        parts.push(`WU: ${builderForm.warmup.by==='distance' ? `${builderForm.warmup.distanceKm}km` : builderForm.warmup.duration}`);
                    }
                    if (builderForm.type==='interval') {
                        if (builderForm.interval.sets && builderForm.interval.sets.length) {
                            const first = builderForm.interval.sets[0];
                            const desc = first.by==='distance' 
                                ? `${first.reps}x${first.dist}km` 
                                : `${first.reps}x${first.time}`;
                            parts.push(`${desc}${builderForm.interval.sets.length > 1 ? '...' : ''}`);
                        } else {
                            parts.push('Interval Session');
                        }
                    } else if (builderForm.type==='tempo') {
                        if (builderForm.tempo.by==='distance') {
                            parts.push(`${builderForm.tempo.distanceKm}km${builderForm.tempo.pace ? ` @${builderForm.tempo.pace}`:''} ${builderForm.tempo.effort}`);
                        } else {
                            parts.push(`${builderForm.tempo.duration}${builderForm.tempo.pace ? ` @${builderForm.tempo.pace}`:''} ${builderForm.tempo.effort}`);
                        }
                    } else if (builderForm.type==='long_run') {
                        if (builderForm.main.by==='distance') {
                            parts.push(`${builderForm.main.distanceKm}km Long Run${builderForm.main.pace ? ` @${builderForm.main.pace}`:''}`);
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
                    } else if (builderForm.type==='custom') {
                        if (builderForm.custom.segments && builderForm.custom.segments.length) {
                            const count = builderForm.custom.segments.length;
                            const first = builderForm.custom.segments[0].name;
                            parts.push(`${count} Segment(s): ${first}${count > 1 ? ', ...' : ''}`);
                        } else {
                            parts.push('Custom Workout');
                        }
                    }
                    if (builderForm.cooldown.enabled) {
                        parts.push(`CD: ${builderForm.cooldown.by==='distance' ? `${builderForm.cooldown.distanceKm}km` : builderForm.cooldown.duration}`);
                    }
                    const base = parts.join(' | ');
                    return builderForm.intensity ? `${base} | Intensity: ${builderForm.intensity}` : base;
                });

                const builderTotalDistance = computed(() => {
                    let total = 0;
                    if (builderForm.warmup.enabled && builderForm.warmup.by==='distance') total += Number(builderForm.warmup.distanceKm)||0;
                    if (builderForm.cooldown.enabled && builderForm.cooldown.by==='distance') total += Number(builderForm.cooldown.distanceKm)||0;
                    if (builderForm.type==='interval') {
                        if (builderForm.interval.sets) {
                            builderForm.interval.sets.forEach(set => {
                                if (set.by==='distance') {
                                    total += (Number(set.reps)||0) * (Number(set.dist)||0);
                                } else {
                                    const dMin = parseDurationMinutes(set.time);
                                    const pMin = parsePaceMinPerKm(set.pace);
                                    const dist = !isNaN(dMin) && !isNaN(pMin) && pMin>0 ? dMin/pMin : 0;
                                    total += (Number(set.reps)||0) * dist;
                                }
                            });
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
                    } else if (builderForm.type==='custom') {
                        if (builderForm.custom.segments) {
                            builderForm.custom.segments.forEach(seg => {
                                total += Number(seg.distance) || 0;
                            });
                        }
                    }
                    return Number(total.toFixed(1));
                });

                const openBuilder = () => {
                    builderVisible.value = true;
                };

                const closeBuilder = () => {
                    builderVisible.value = false;
                };

                const saveBuilderFromModal = () => {
                    form.value.type = builderForm.type;
                    form.value.distance = builderTotalDistance.value;
                    form.value.advanced_config = JSON.stringify(builderForm);
                    
                    // Close
                    builderVisible.value = false;
                    
                    message.value = 'Workout applied! Please verify distance/duration and upload proof.';
                    messageType.value = 'success';
                };
                // --- BUILDER LOGIC END ---


                // Calculate Pace Automatically
                const calculatedPace = computed(() => {
                    const dist = parseFloat(form.value.distance);
                    const h = parseInt(form.value.duration_hours) || 0;
                    const m = parseInt(form.value.duration_minutes) || 0;
                    const s = parseInt(form.value.duration_seconds) || 0;

                    if (!dist || dist <= 0) return '-:--';
                    
                    const totalMinutes = (h * 60) + m + (s / 60);
                    if (totalMinutes <= 0) return '-:--';

                    const paceDecimal = totalMinutes / dist;
                    const paceMin = Math.floor(paceDecimal);
                    const paceSec = Math.round((paceDecimal - paceMin) * 60);

                    const formattedSec = paceSec < 10 ? '0' + paceSec : paceSec;
                    return `${paceMin}:${formattedSec}`;
                });

                const handleFileUpload = (event) => {
                    const file = event.target.files[0];
                    if (file) {
                        form.value.image = file;
                        previewImage.value = URL.createObjectURL(file);
                    }
                };

                const submitActivity = async () => {
                    if (!form.value.image) {
                        message.value = 'Mohon upload bukti screenshot lari.';
                        messageType.value = 'error';
                        return;
                    }

                    if (!form.value.distance || form.value.distance <= 0) {
                        message.value = 'Jarak harus diisi.';
                        messageType.value = 'error';
                        return;
                    }

                    isSubmitting.value = true;
                    message.value = '';

                    try {
                        const formData = new FormData();
                        formData.append('date', form.value.date);
                        formData.append('distance', form.value.distance);
                        formData.append('duration_hours', form.value.duration_hours || 0);
                        formData.append('duration_minutes', form.value.duration_minutes || 0);
                        formData.append('duration_seconds', form.value.duration_seconds || 0);
                        formData.append('time_hour', form.value.time_hour || 7);
                        formData.append('time_minute', form.value.time_minute || 0);
                        formData.append('time_ampm', form.value.time_ampm || 'AM');
                        formData.append('strava_link', form.value.stravaLink);
                        formData.append('image', form.value.image);
                        
                        // New fields
                        if (form.value.type) {
                            formData.append('type', form.value.type);
                        }
                        if (form.value.advanced_config) {
                            formData.append('advanced_config', form.value.advanced_config);
                        }


                        // CSRF Token
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        const response = await fetch("{{ route('challenge.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            message.value = result.message;
                            messageType.value = 'success';
                            // Reset form
                            form.value.distance = '';
                            form.value.duration_hours = '';
                            form.value.duration_minutes = '';
                            form.value.duration_seconds = '';
                            form.value.time_hour = 7;
                            form.value.time_minute = 0;
                            form.value.time_ampm = 'AM';
                            form.value.stravaLink = '';
                            form.value.image = null;
                            previewImage.value = null;
                            form.value.advanced_config = null;
                            form.value.type = 'easy_run';
                            
                            // Reload page after delay to update history
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            message.value = result.message || 'Terjadi kesalahan saat mengirim data.';
                            messageType.value = 'error';
                        }
                    } catch (error) {
                        console.error(error);
                        message.value = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                        messageType.value = 'error';
                    } finally {
                        isSubmitting.value = false;
                    }
                };

                return {
                    form,
                    isSubmitting,
                    previewImage,
                    message,
                    messageType,
                    handleFileUpload,
                    submitActivity,
                    calculatedPace,
                    // Login related
                    loginForm,
                    isLoggingIn,
                    loginError,
                    handleLogin,
                    // Builder
                    builderVisible, builderForm, openBuilder, closeBuilder, saveBuilderFromModal, builderSummary, builderTotalDistance,
                    strengthOptions, addStrengthExercise, removeStrengthExercise,
                    addIntervalSet, removeIntervalSet, addCustomSegment, removeCustomSegment
                };
            }
        }).mount('#submit-app');
    </script>
@endpush
@endsection
