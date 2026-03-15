@extends('layouts.pacerhub')

@section('title', 'AI Running Program Generator V2')

@push('styles')
    <script>
        // Extending existing Tailwind config from pacerhub layout
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    DEFAULT: '#ccff00',
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    dark: '#0f172a',
                    card: '#1e293b'
                }
            },
            boxShadow: {
                ...tailwind.config.theme.extend.boxShadow,
                'neon-cyan': '0 0 10px rgba(6, 182, 212, 0.5), 0 0 20px rgba(6, 182, 212, 0.3)',
                'neon-purple': '0 0 10px rgba(168, 85, 247, 0.5), 0 0 20px rgba(168, 85, 247, 0.3)',
            },
            animation: {
                ...tailwind.config.theme.extend.animation,
                'scan': 'scan 2s linear infinite',
            },
            keyframes: {
                ...tailwind.config.theme.extend.keyframes,
                scan: {
                    '0%': { transform: 'translateY(-100%)' },
                    '100%': { transform: 'translateY(100%)' },
                }
            }
        }
    </script>
    
    <style>
        .generator-v2-wrapper { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            position: relative;
        }
        
        .generator-v2-wrapper .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }

        .glow-pulse {
            animation: glowPulse 2.5s ease-in-out infinite;
        }

        @keyframes glowPulse {
            0% { box-shadow: 0 0 0px rgba(6,182,212,0.0); }
            50% { box-shadow: 0 0 20px rgba(6,182,212,0.35); }
            100% { box-shadow: 0 0 0px rgba(6,182,212,0.0); }
        }
    </style>
@endpush

@section('content')
<div id="generator-v2-app" class="generator-v2-wrapper relative w-full h-full px-4 pb-12 pt-28 rounded-xl overflow-hidden">
    
    <!-- Background Grid -->
    <div class="absolute inset-0 z-0 pointer-events-none opacity-10" 
            style="background-image: linear-gradient(#334155 1px, transparent 1px), linear-gradient(to right, #334155 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <main class="relative z-10 max-w-6xl mx-auto">
        <transition name="fade" mode="out-in">
            
            <!-- Step 0: Hero / Start -->
            <div v-if="step === 0" key="hero" class="text-center py-12">
                <div class="inline-block mb-4 px-4 py-1 rounded-full bg-cyan-900/30 border border-cyan-500/50 text-cyan-300 text-xs font-mono tracking-widest uppercase animate-pulse">
                    Professional Training Algorithm v2.0
                </div>
                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white via-cyan-100 to-cyan-400">
                    SMART RUNNING <br> GENERATOR
                </h1>
                <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Hasilkan program latihan lari 5K hingga Full Marathon yang dipersonalisasi menggunakan algoritma 
                    <span class="text-cyan-400 font-bold">Jack Daniels' VDOT</span> dan sistem periodisasi elit.
                </p>
                
                <div class="flex flex-col md:flex-row gap-4 justify-center">
                    <button @click="step = 1" class="px-8 py-4 bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-bold text-lg rounded-xl transition-all shadow-neon-cyan hover:scale-105">
                        MULAI GENERATE SEKARANG
                    </button>
                </div>
            </div>

            <!-- Step 1: Input Form -->
            <div v-else-if="step === 1" key="form" class="max-w-2xl mx-auto">
                <div class="glass rounded-2xl p-8 md:p-10 shadow-2xl relative overflow-hidden">
                    <h2 class="text-3xl font-bold text-white mb-8 flex items-center gap-3">
                        <span class="p-2 bg-cyan-500/20 rounded-lg text-cyan-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </span>
                        Parameter Program
                    </h2>

                    <div class="space-y-6">
                        <!-- Validation Errors -->
                        <div v-if="errors" class="p-4 rounded-xl bg-red-900/30 border border-red-500/50 text-red-200 text-sm">
                            <p class="font-bold mb-2">Harap perbaiki kesalahan berikut:</p>
                            <ul class="list-disc list-inside">
                                <li v-for="(msg, field) in errors" :key="field">@{{ msg[0] }}</li>
                            </ul>
                        </div>

                        <!-- PB Section -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Jarak Parameter Test / PB (1 Bulan Terakhir)</label>
                                <select v-model="form.pb_distance" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none appearance-none">
                                    <option value="5k">5 Kilometer</option>
                                    <option value="10k">10 Kilometer</option>
                                    <option value="21k">Half Marathon</option>
                                    <option value="42k">Full Marathon</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Waktu Parameter Test / PB (Jam : Menit : Detik)</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="relative">
                                        <input v-model="pb_hours" type="number" min="0" max="99" placeholder="HH" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none font-mono text-center">
                                        <span class="absolute right-[-8px] top-1/2 -translate-y-1/2 text-slate-500">:</span>
                                    </div>
                                    <div class="relative">
                                        <input v-model="pb_minutes" type="number" min="0" max="59" placeholder="MM" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none font-mono text-center">
                                        <span class="absolute right-[-8px] top-1/2 -translate-y-1/2 text-slate-500">:</span>
                                    </div>
                                    <input v-model="pb_seconds" type="number" min="0" max="59" placeholder="SS" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none font-mono text-center">
                                </div>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Jenis Kelamin</label>
                                <div class="flex gap-2">
                                    <button @click="form.gender = 'male'" :class="form.gender === 'male' ? 'bg-cyan-500 text-slate-900' : 'bg-slate-800/50 text-slate-400 border border-slate-600'" class="flex-1 py-3 rounded-xl font-bold transition-all">Laki-laki</button>
                                    <button @click="form.gender = 'female'" :class="form.gender === 'female' ? 'bg-cyan-500 text-slate-900' : 'bg-slate-800/50 text-slate-400 border border-slate-600'" class="flex-1 py-3 rounded-xl font-bold transition-all">Perempuan</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Umur</label>
                                <input v-model="form.age" type="number" min="15" max="90" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none font-mono">
                            </div>
                        </div>

                        <!-- Target Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Target Jarak</label>
                                <select v-model="form.target_distance" @change="recommendMileage" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none appearance-none">
                                    <option value="5k">5K</option>
                                    <option value="10k">10K</option>
                                    <option value="21k">Half Marathon</option>
                                    <option value="42k">Full Marathon</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Target Tanggal Lomba</label>
                                <input v-model="form.target_date" type="date" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                            </div>
                        </div>

                        <!-- Training Load Section -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-xs font-mono text-cyan-400 uppercase">Mileage Mingguan (Km)</label>
                                    <span class="text-xs font-mono text-slate-400">Rekomendasi: @{{ idealMileage }} Km</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <input v-model="form.weekly_mileage" type="range" min="15" max="120" step="5" class="flex-1 h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-cyan-500">
                                    <span class="w-12 text-center font-mono text-white text-xl">@{{ form.weekly_mileage }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Frekuensi Latihan (Hari/Minggu)</label>
                                <div class="flex justify-between gap-2">
                                    <button v-for="f in [3,4,5,6,7]" :key="f" @click="form.frequency = f"
                                            :class="form.frequency === f ? 'bg-cyan-500 text-slate-900 scale-110 shadow-neon-cyan' : 'bg-slate-800/50 text-slate-400 border border-slate-600'"
                                            class="w-full py-3 rounded-xl font-bold transition-all">
                                        @{{ f }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <button @click="generateProgram" :disabled="loading" class="w-full py-4 bg-gradient-to-r from-cyan-600 to-cyan-400 text-slate-900 font-bold rounded-xl shadow-neon-cyan transform hover:scale-[1.02] transition-all disabled:opacity-50">
                                <span v-if="loading">MENGANALISIS...</span>
                                <span v-else>GENERATE PROGRAM</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Result Preview -->
            <div v-else-if="step === 2" key="result" class="space-y-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-800/50 p-6 rounded-2xl border border-slate-700">
                    <div class="flex items-center gap-6">
                        <div class="text-center px-6 py-2 border-r border-slate-700">
                            <span class="block text-[10px] font-mono text-cyan-400 uppercase">VDOT Score</span>
                            <span class="text-3xl font-black text-white">@{{ result.vdot }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">@{{ result.summary.target }} Program</h3>
                            <p class="text-slate-400 text-sm">Durasi: @{{ result.weeks }} Minggu Latihan</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="step = 1" class="px-6 py-2 rounded-lg border border-slate-600 text-white hover:bg-slate-700 transition-all">
                            EDIT DATA
                        </button>
                        <button @click="saveAndOpenCalendar" :disabled="saving" class="px-6 py-2 rounded-lg bg-cyan-500 text-slate-900 font-bold hover:bg-cyan-400 transition-all shadow-neon-cyan">
                            @{{ saving ? 'MENYIMPAN...' : 'SIMPAN KE KALENDER' }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Paces Card -->
                    <div class="glass p-8 rounded-2xl border border-cyan-900/30">
                        <h3 class="text-xs font-mono text-cyan-400 uppercase mb-6 border-b border-cyan-900 pb-2">Training Paces (min/km)</h3>
                        <div class="space-y-4">
                            <div v-for="(pace, type) in result.paces" :key="type" class="flex justify-between items-center p-3 rounded-lg bg-slate-900/50 border border-slate-800">
                                <span class="font-bold text-sm" :class="getPaceColor(type)">@{{ getPaceLabel(type) }}</span>
                                <span class="font-mono text-white">@{{ formatPace(pace) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program Preview (Free Weeks) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div v-for="(weekSessions, weekNum) in sessionsByWeek" :key="weekNum" class="glass p-8 rounded-2xl border border-slate-700 relative overflow-hidden">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                    <span class="text-cyan-400">📅</span> Preview Minggu @{{ weekNum }}
                                </h3>
                                <span class="px-3 py-1 bg-green-900/30 text-green-400 text-xs font-bold rounded-full border border-green-900/50">FREE PREVIEW</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                                <div v-for="day in weekSessions" :key="day.day" 
                                     class="p-4 rounded-xl border min-h-[120px] flex flex-col justify-between transition-all hover:scale-105"
                                     :class="getSessionClass(day.type)">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[10px] font-mono text-slate-500 uppercase">Day @{{ day.day }}</span>
                                        <span class="text-lg">@{{ getSessionIcon(day.type) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-white leading-tight mb-1 uppercase">@{{ day.type.replace('_', ' ') }}</h4>
                                        <p class="text-[10px] text-slate-400">@{{ day.distance }} KM</p>
                                        <p v-if="day.target_pace" class="text-[10px] font-mono text-cyan-300">@{{ day.target_pace }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blur Overlay for remaining weeks -->
                        <div class="mt-8 relative p-12 text-center rounded-xl border border-dashed border-slate-700 bg-slate-900/50">
                            <div class="absolute inset-0 backdrop-blur-[2px] opacity-50 pointer-events-none"></div>
                            <div class="relative z-10">
                                <p class="text-slate-400 text-sm mb-4">Minggu @{{ freeWeeksCount + 1 }} sampai @{{ result.weeks }} akan di-unlock setelah Anda menyimpannya ke kalender.</p>
                                <div class="flex justify-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 animate-pulse"></div>
                                    <div class="w-8 h-8 rounded-full bg-slate-800 animate-pulse"></div>
                                    <div class="w-8 h-8 rounded-full bg-slate-800 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </transition>
    </main>

    <!-- Auth Modal Integration (handled by layout pacerhub) -->
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, computed, onMounted } = Vue;

    createApp({
        setup() {
            const step = ref(0);
            const loading = ref(false);
            const saving = ref(false);
            const result = ref(null);
            const errors = ref(null);

            const pb_hours = ref(0);
            const pb_minutes = ref(0);
            const pb_seconds = ref(0);

            const form = reactive({
                pb_distance: '5k',
                pb_time: '',
                target_distance: '10k',
                target_date: '',
                weekly_mileage: 30,
                frequency: 4,
                gender: 'male',
                age: 25
            });

            const idealMileage = computed(() => {
                const map = { '5k': 30, '10k': 45, '21k': 65, '42k': 85 };
                return map[form.target_distance] || 30;
            });

            const recommendMileage = () => {
                form.weekly_mileage = idealMileage.value;
            };

            const freePreviewSessions = computed(() => {
                if (!result.value) return [];
                const totalWeeks = result.value.weeks || 8;
                const freeWeeks = Math.max(1, Math.floor(totalWeeks / 2));
                return result.value.sessions.filter(s => s.week <= freeWeeks);
            });

            const freeWeeksCount = computed(() => {
                if (!result.value) return 0;
                return Math.max(1, Math.floor(result.value.weeks / 2));
            });

            const sessionsByWeek = computed(() => {
                const weeks = {};
                freePreviewSessions.value.forEach(s => {
                    if (!weeks[s.week]) weeks[s.week] = [];
                    weeks[s.week].push(s);
                });
                return weeks;
            });

            const generateProgram = async () => {
                errors.value = null;
                
                // Format PB time from 3 fields
                const h = String(pb_hours.value).padStart(2, '0');
                const m = String(pb_minutes.value).padStart(2, '0');
                const s = String(pb_seconds.value).padStart(2, '0');
                form.pb_time = `${h}:${m}:${s}`;

                if (pb_hours.value === 0 && pb_minutes.value === 0 && pb_seconds.value === 0) {
                    alert('Harap isi waktu parameter test/PB!');
                    return;
                }

                if (!form.target_date) {
                    alert('Harap lengkapi semua data!');
                    return;
                }

                loading.value = true;
                try {
                    const response = await fetch('{{ route("generator.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(form)
                    });

                    const data = await response.json();
                    if (response.ok) {
                        result.value = data;
                        step.value = 2;
                    } else if (response.status === 422) {
                        errors.value = data.errors;
                    } else {
                        alert(data.message || 'Gagal generate program.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Terjadi kesalahan sistem.');
                } finally {
                    loading.value = false;
                }
            };

            const saveAndOpenCalendar = async () => {
                @guest
                    // Store state in session before showing login modal
                    try {
                        await fetch('{{ route("generator.store-pending") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                form: form,
                                result: result.value
                            })
                        });
                    } catch (e) {
                        console.error('Failed to store pending program:', e);
                    }

                    if (window.openLoginModal) {
                        window.openLoginModal();
                    } else {
                        alert('Harap login terlebih dahulu.');
                    }
                    return;
                @endguest

                saving.value = true;
                try {
                    const response = await fetch('{{ route("generator.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: `AI ${form.target_distance.toUpperCase()} Plan (${result.value.vdot})`,
                            target_distance: form.target_distance,
                            target_date: form.target_date,
                            program_json: {
                                sessions: result.value.sessions,
                                summary: result.value.summary
                            },
                            vdot: result.value.vdot,
                            paces: result.value.paces
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        window.location.href = '{{ route("runner.calendar") }}';
                    } else {
                        alert(data.message || 'Gagal menyimpan program.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Terjadi kesalahan saat menyimpan.');
                } finally {
                    saving.value = false;
                }
            };

            const getPaceLabel = (type) => {
                const labels = {
                    'E': 'Easy Pace',
                    'M': 'Marathon Pace',
                    'T': 'Threshold Pace',
                    'I': 'Interval Pace',
                    'R': 'Repetition Pace'
                };
                return labels[type] || type;
            };

            const getPaceColor = (type) => {
                const colors = {
                    'E': 'text-green-400',
                    'M': 'text-blue-400',
                    'T': 'text-yellow-400',
                    'I': 'text-purple-400',
                    'R': 'text-red-400'
                };
                return colors[type] || 'text-slate-400';
            };

            const formatPace = (minPerKm) => {
                const m = Math.floor(minPerKm);
                const s = Math.round((minPerKm - m) * 60);
                return `${m}:${s < 10 ? '0' + s : s}`;
            };

            const getSessionClass = (type) => {
                const classes = {
                    'rest': 'bg-slate-800/30 border-slate-700',
                    'easy_run': 'bg-green-900/10 border-green-900/30',
                    'long_run': 'bg-yellow-900/10 border-yellow-900/30',
                    'threshold': 'bg-blue-900/10 border-blue-900/30',
                    'interval': 'bg-purple-900/10 border-purple-900/30'
                };
                return classes[type] || 'bg-slate-800/30 border-slate-700';
            };

            const getSessionIcon = (type) => {
                const icons = {
                    'rest': '😴',
                    'easy_run': '🏃',
                    'long_run': '🔋',
                    'threshold': '🔥',
                    'interval': '⚡'
                };
                return icons[type] || '🏃';
            };

            return {
                step, form, loading, saving, result, freePreviewSessions, freeWeeksCount, sessionsByWeek, errors,
                generateProgram, saveAndOpenCalendar,
                getPaceLabel, getPaceColor, formatPace, getSessionClass, getSessionIcon,
                pb_hours, pb_minutes, pb_seconds, idealMileage, recommendMileage
            };
        }
    }).mount('#generator-v2-app');
</script>
@endpush
