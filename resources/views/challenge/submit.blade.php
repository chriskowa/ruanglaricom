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
    <div id="submit-app" class="relative z-10 w-full min-h-screen pb-24 pt-20 px-4">
        
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
                                    <div class="text-xs text-slate-400 font-mono">{{ gmdate('H:i:s', $activity->duration_seconds) }}</div>
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
    </div>

@push('scripts')
    <script>
        const { createApp, ref, computed, watch } = Vue;

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
                    
                    try {
                        const response = await fetch("{{ route('login') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(loginForm.value)
                        });
                        
                        const data = await response.json();

                        if (response.ok || (data.success && !data.errors)) {
                             window.location.reload();
                        } else {
                             loginError.value = data.message || 'Login failed. Please check your credentials.';
                        }
                    } catch (e) {
                        loginError.value = 'An error occurred. Please try again.';
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
                    stravaLink: '',
                    image: null
                });

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
                        formData.append('strava_link', form.value.stravaLink);
                        formData.append('image', form.value.image);

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
                            form.value.stravaLink = '';
                            form.value.image = null;
                            previewImage.value = null;
                            
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
                    handleLogin
                };
            }
        }).mount('#submit-app');
    </script>
@endpush
@endsection