@extends('layouts.pacerhub')

@section('title', 'Generator Program Lari & Running Program Generator | Ruang Lari')

@section('meta_title', 'Generator Program Lari & Running Program Generator | Ruang Lari')
@section('meta_description', 'Hasilkan program latihan lari 5K, 10K, Half Marathon, dan Full Marathon yang dipersonalisasi secara gratis. Gunakan algoritma Jack Daniels\' VDOT untuk target waktu lari yang realistis dan ilmiah.')
@section('meta_keywords', 'generator program lari, running program generator, training plan lari, program latihan marathon, kalkulator vdot, rencana latihan lari gratis, jack daniels running formula, pacerhub, ruang lari')

@section('og_image', 'https://ruanglari.com/storage/blog/media/kP2oNYsx0wEzCGJMQYKN1xxUBW3oaUMTCfydDSig.webp')

@push('head')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Generator Program Lari Ruang Lari",
  "alternateName": "Running Program Generator",
  "url": "https://ruanglari.com/realistic-running-program",
  "description": "Platform pembuat program latihan lari otomatis berdasarkan tingkat kebugaran Anda menggunakan metode VDOT Jack Daniels.",
  "applicationCategory": "HealthApplication",
  "operatingSystem": "All",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Personalisasi Program Lari 5K - Full Marathon",
    "Kalkulator VDOT Jack Daniels",
    "Prediksi Target Waktu Lari Realistis",
    "Sinkronisasi Kalender Latihan"
  ]
}
</script>
@endverbatim
@endpush
@push('styles')       
    <script>
        // Extending existing Tailwind config from pacerhub layout
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...(tailwind.config.theme.extend.colors || {}),
                brand: {
                    50: '#f7fee7',
                    100: '#ecfccb',
                    200: '#d9f99d',
                    300: '#649af2ff',
                    400: '#3570e6ff',
                    500: '#007bffff',
                    600: '#0062f6ff',
                    700: '#020202ff',
                    800: '#3f6212',
                    900: '#365314',
                }
            }
        }
    </script>
    
    <style>
        .generator-v2-wrapper { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            position: relative;
        }
        
        .generator-v2-wrapper .card-dark {
            background: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        }

        .fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(8px); }

        .input-field {
            width: 100%;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #334155;
            background-color: #0f172a !important;
            color: #f8fafc !important;
            font-weight: 500;
            outline: none;
            transition: all 0.2s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* Select styling with custom arrow */
        select.input-field {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
            padding-right: 2.5rem !important;
        }

        /* Date input styling */
        input[type="date"].input-field {
            position: relative;
        }
        input[type="date"].input-field::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }
        input[type="date"].input-field::after {
            content: "📅";
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            font-size: 1rem;
        }

        .input-field:focus {
            border-color: #ccff00;
            box-shadow: 0 0 0 2px rgba(204, 255, 0, 0.1);
        }

        .label-text {
            display: block;
            font-size: 11px;
            font-weight: 900;
            color: #94a3b8;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Custom Scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div id="generator-v2-app" class="generator-v2-wrapper relative w-full h-full px-4 pb-12 pt-28 rounded-xl overflow-hidden">
    
    <!-- Notification Toast -->
    <transition name="fade">
        <div v-if="notification" class="fixed top-24 right-4 z-[100] max-w-sm w-full">
            <div :class="notification.type === 'error' ? 'bg-red-900/90 border-red-500/50 text-red-100' : 'bg-green-900/90 border-green-500/50 text-green-100'" 
                 class="p-4 rounded-xl border backdrop-blur-md shadow-2xl flex items-start gap-3">
                <span class="text-lg">@{{ notification.type === 'error' ? '⚠️' : '✅' }}</span>
                <div class="flex-1 text-sm font-medium">@{{ notification.message }}</div>
                <button @click="notification = null" class="text-slate-400 hover:text-slate-200">✕</button>
            </div>
        </div>
    </transition>

    <main class="relative z-10 max-w-7xl mx-auto">
        <transition name="fade" mode="out-in">
            
            <!-- Step 0: Hero / Start -->
            <div v-if="step === 0" key="hero" class="relative text-center py-24 md:py-32 rounded-3xl overflow-hidden mb-12">
                <!-- Background & Overlay -->
                <div class="absolute inset-0 z-0">
                    <img src="https://ruanglari.com/storage/blog/media/kP2oNYsx0wEzCGJMQYKN1xxUBW3oaUMTCfydDSig.webp" 
                         class="w-full h-full object-cover" alt="Hero Background">
                    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-[2px]"></div>
                </div>

                <div class="relative z-10 px-6">
                    <div class="inline-block mb-6 px-4 py-1.5 rounded-full bg-brand-500/20 text-brand-400 text-xs font-bold tracking-wide uppercase border border-brand-500/30 backdrop-blur-md">
                        Scientific Training Framework
                    </div>
                    <h1 class="text-4xl md:text-7xl font-black tracking-tight mb-6 text-white leading-tight">
                        Generator <span class="text-brand-500 italic">Program Lari</span> <br> Secara Realistis
                    </h1>
                    <p class="text-base md:text-xl text-slate-200 max-w-2xl mx-auto mb-10 leading-relaxed drop-shadow-lg">
                        Running Program Generator otomatis untuk 5K hingga Full Marathon yang dipersonalisasi menggunakan algoritma 
                        <span class="text-white font-bold">Jack Daniels' VDOT</span> yang teruji secara ilmiah.
                    </p>
                    
                    <div class="flex flex-col md:flex-row gap-4 justify-center">
                        <button @click="step = 1" class="px-10 py-4 bg-brand-600 hover:bg-brand-500 text-white font-bold text-lg rounded-xl transition-all shadow-xl shadow-brand-500/30 hover:scale-[1.02] active:scale-[0.98]">
                            Buat Program Saya
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 1: Input Form -->
            <div v-else-if="step === 1" key="form" class="max-w-2xl mx-auto">
                <div class="card-dark p-8 md:p-10 rounded-3xl">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-black text-white uppercase italic tracking-tight">Parameter Latihan</h2>
                        <button @click="step = 0" class="text-slate-500 hover:text-slate-300 text-sm font-bold flex items-center gap-1">
                            <span>←</span> Kembali
                        </button>
                    </div>

                    <div class="flex flex-col gap-8">
                        <!-- PB Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="label-text">Parameter Test/PB</label>
                                <div class="flex flex-col gap-1">
                                    <select v-model="form.pb_distance" class="input-field cursor-pointer">
                                        <option value="5k">5 Kilometer</option>
                                        <option value="10k">10 Kilometer</option>
                                        <option value="21k">Half Marathon</option>
                                        <option value="42k">Full Marathon</option>
                                    </select>
                                    <span class="text-[9px] opacity-0 font-bold uppercase tracking-widest select-none">Spacer</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="label-text">Waktu Parameter Test / PB</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="flex flex-col items-center gap-1">
                                        <input v-model="pb_hours" type="number" min="0" max="99" class="input-field text-center font-bold" placeholder="HH">
                                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Jam</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <input v-model="pb_minutes" type="number" min="0" max="59" class="input-field text-center font-bold" placeholder="MM">
                                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Menit</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <input v-model="pb_seconds" type="number" min="0" max="59" class="input-field text-center font-bold" placeholder="SS">
                                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Detik</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="label-text">Jenis Kelamin</label>
                                <div class="flex p-1 bg-slate-900 rounded-xl border border-slate-700">
                                    <button @click="form.gender = 'male'" :class="form.gender === 'male' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-500'" class="flex-1 py-2.5 rounded-lg font-bold text-[11px] transition-all uppercase tracking-wider">Laki-laki</button>
                                    <button @click="form.gender = 'female'" :class="form.gender === 'female' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-500'" class="flex-1 py-2.5 rounded-lg font-bold text-[11px] transition-all uppercase tracking-wider">Perempuan</button>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="label-text">Umur</label>
                                <input v-model="form.age" type="number" min="15" max="90" class="input-field font-bold">
                            </div>
                        </div>

                        <!-- Target Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="label-text">Target Jarak</label>
                                <select v-model="form.target_distance" @change="recommendMileage" class="input-field cursor-pointer">
                                    <option value="5k">5K</option>
                                    <option value="10k">10K</option>
                                    <option value="21k">Half Marathon</option>
                                    <option value="42k">Full Marathon</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="label-text">Target Tanggal Lomba</label>
                                <input v-model="form.target_date" type="date" class="input-field font-bold">
                            </div>
                        </div>

                        <!-- Goal Time Section -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="label-text mb-0">Target Waktu Lomba</label>
                                <div v-if="realism" :class="realism.color" class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider border">
                                    @{{ realism.label }}
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="flex flex-col items-center gap-1">
                                    <input v-model="goal_hours" type="number" min="0" max="99" class="input-field text-center font-bold" placeholder="HH">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Jam</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <input v-model="goal_minutes" type="number" min="0" max="59" class="input-field text-center font-bold" placeholder="MM">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Menit</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <input v-model="goal_seconds" type="number" min="0" max="59" class="input-field text-center font-bold" placeholder="SS">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Detik</span>
                                </div>
                            </div>
                            <p v-if="realism" class="text-[10px] text-slate-500 italic leading-tight mt-1">
                                @{{ realism.description }}
                            </p>
                        </div>

                        <!-- Training Load Section -->
                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="label-text">Mileage Mingguan (Km)</label>
                                    <span class="text-[10px] font-bold text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded-full border border-brand-500/20">Rekomendasi: @{{ idealMileage }} Km</span>
                                </div>
                                <div class="flex items-center gap-6">
                                    <input v-model="form.weekly_mileage" type="range" min="15" max="120" step="5" class="flex-1 h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-500">
                                    <span class="w-12 text-center font-black text-white text-xl">@{{ form.weekly_mileage }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="label-text">Frekuensi Latihan (Hari/Minggu)</label>
                                <div class="flex justify-between gap-3">
                                    <button v-for="f in [3,4,5,6,7]" :key="f" @click="form.frequency = f" 
                                            :class="form.frequency === f ? 'bg-brand-600 text-white shadow-lg border-brand-500' : 'bg-slate-900/50 text-slate-500 border border-slate-700 hover:border-slate-600'"
                                            class="w-full py-3 rounded-xl font-bold text-sm transition-all border">
                                        @{{ f }}
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="label-text">Level Pelari</label>
                                    <select v-model="form.runner_level" class="input-field cursor-pointer">
                                        <option value="beginner">Pemula</option>
                                        <option value="intermediate">Menengah</option>
                                        <option value="advanced">Mahir</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="label-text">Hari Long Run</label>
                                    <select v-model="form.long_run_day" class="input-field cursor-pointer">
                                        <option value="saturday">Sabtu</option>
                                        <option value="sunday">Minggu</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button @click="generateProgram" :disabled="loading" 
                                class="w-full py-5 bg-brand-600 hover:bg-brand-500 disabled:bg-slate-700 disabled:text-slate-500 text-white font-black text-lg rounded-2xl transition-all shadow-xl shadow-brand-500/10 flex items-center justify-center gap-3">
                            <span v-if="!loading">GENERATE PROGRAM</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                MEMPROSES...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Results Display -->
            <div v-else-if="step === 2" key="result" class="space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left: Summary Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="card-dark p-8 rounded-3xl border-t-4 border-t-brand-500">
                            <div class="text-center mb-6">
                                <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-1">Estimated VDOT Score</div>
                                <div class="text-6xl font-black text-white tracking-tighter">@{{ result?.vdot }}</div>
                            </div>
                            
                            <div class="space-y-2 py-4 border-y border-slate-700/50 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">Target Jarak</span>
                                    <span class="font-bold text-white uppercase">@{{ form.target_distance }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">Durasi Program</span>
                                    <span class="font-bold text-white">@{{ result?.weeks }} Minggu</span>
                                </div>
                            </div>

                            <button @click="saveAndOpenCalendar" :disabled="saving" class="w-full py-4 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl transition-all shadow-lg flex items-center justify-center gap-2 mb-3">
                                <span v-if="!saving">SIMPAN KE KALENDER</span>
                                <span v-else class="animate-spin text-lg">⌛</span>
                            </button>
                            <button @click="step = 1" class="w-full py-3 bg-slate-800 border border-slate-700 text-slate-400 font-bold rounded-xl hover:bg-slate-700 hover:text-white transition-all text-sm">
                                EDIT PARAMETER
                            </button>
                        </div>

                        <!-- Training Paces Card -->
                        <div class="card-dark p-8 rounded-3xl">
                            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-6">Training Paces (min/km)</h3>
                            <div class="space-y-4">
                                <div v-for="(pace, type) in (result?.paces || {})" :key="type" class="flex justify-between items-center p-3.5 rounded-xl bg-slate-900/50 border border-slate-700/50">
                                    <span class="font-bold text-xs uppercase" :class="getPaceColor(type)">@{{ getPaceLabel(type) }}</span>
                                    <span class="font-mono font-bold text-white">@{{ formatPace(pace) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Program Preview (Free Weeks) -->
                    <div class="lg:col-span-2 space-y-8">
                        <div v-for="(weekSessions, weekNum) in sessionsByWeek" :key="weekNum" class="card-dark p-8 md:p-10 rounded-3xl">
                            <div class="flex justify-between items-center mb-8">
                                <h3 class="text-2xl font-black text-white italic uppercase tracking-tight">
                                    Preview Minggu @{{ weekNum }}
                                </h3>
                                <span class="px-3 py-1 bg-brand-500/10 text-brand-400 text-[10px] font-black rounded-full border border-brand-500/20 uppercase tracking-wider">Akses Gratis</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-4">
                                <div v-for="day in weekSessions" :key="day.day" 
                                     class="p-4 rounded-2xl border min-h-[140px] flex flex-col justify-between transition-all hover:border-brand-500/50 group"
                                     :class="getSessionClass(day.type)">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Day @{{ day.day }}</span>
                                        <span class="text-xl group-hover:scale-125 transition-transform duration-300">@{{ getSessionIcon(day.type) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-white leading-tight mb-1 uppercase">@{{ day.type.replace('_', ' ') }}</h4>
                                        <p class="text-sm font-black text-white">@{{ day.distance }} <span class="text-[10px] font-normal text-slate-400">KM</span></p>
                                        <p v-if="day.target_pace" class="text-[10px] font-mono font-bold text-brand-400 mt-1">@{{ day.target_pace }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Overlay for remaining weeks -->
                        <div class="card-dark p-12 text-center rounded-3xl border-dashed bg-slate-900/30">
                            <div class="max-w-md mx-auto">
                                <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500 border border-slate-700">🔒</div>
                                <h4 class="text-white font-bold mb-2">Sisa Program Terkunci</h4>
                                <p class="text-slate-400 text-sm mb-6 leading-relaxed">
                                    Minggu @{{ freeWeeksCount + 1 }} sampai @{{ result?.weeks || '-' }} akan di-unlock secara otomatis setelah Anda menyimpannya ke kalender.
                                </p>
                                <button @click="saveAndOpenCalendar" class="text-brand-400 font-black text-xs uppercase tracking-widest hover:text-brand-300 transition-colors">Simpan Sekarang →</button>
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
    const { createApp, ref, reactive, computed, onMounted, watch } = Vue;

    createApp({
        setup() {
            const step = ref(0);
            const loading = ref(false);
            const saving = ref(false);
            const result = ref(null);
            const errors = ref(null);
            const notification = ref(null);

            const pb_hours = ref(0);
            const pb_minutes = ref(0);
            const pb_seconds = ref(0);

            const goal_hours = ref(0);
            const goal_minutes = ref(0);
            const goal_seconds = ref(0);

            const form = reactive({
                pb_distance: '5k',
                pb_time: '',
                target_distance: '10k',
                target_date: '',
                goal_time: '',
                weekly_mileage: 30,
                frequency: 4,
                gender: 'male',
                age: 25,
                runner_level: 'intermediate',
                long_run_day: 'sunday'
            });

            const showNotification = (message, type = 'success') => {
                notification.value = { message, type };
                setTimeout(() => {
                    notification.value = null;
                }, 5000);
            };

            const distanceKm = {
                '5k': 5,
                '10k': 10,
                '21k': 21.0975,
                '42k': 42.195
            };

            const distanceMeters = {
                '5k': 5000,
                '10k': 10000,
                '21k': 21097.5,
                '42k': 42195
            };

            const getRatioForDistance = (distanceKey, vdot) => {
                const ratios = {
                    '5k': 0.957,
                    '10k': 0.915,
                    '21k': 0.865,
                    '42k': 0.815
                };
                const base = ratios[distanceKey] ?? 0.957;
                return base + (vdot - 50) * 0.0005;
            };

            const vvo2FromVDOT = (vdot) => {
                const a = 0.000104;
                const b = 0.182258;
                const c = -4.6 - vdot;
                return (-b + Math.sqrt(b * b - 4 * a * c)) / (2 * a);
            };

            const calculateVDOTFromPerformance = (distanceKey, totalSeconds) => {
                if (!totalSeconds || totalSeconds < 600) return 0;
                const distMeters = distanceMeters[distanceKey];
                if (!distMeters) return 0;
                const velocityMin = (distMeters / totalSeconds) * 60;
                let vdot = 50;
                for (let i = 0; i < 5; i++) {
                    const ratio = Math.max(0.01, getRatioForDistance(distanceKey, vdot));
                    const vvo2max = velocityMin / ratio;
                    const newVdot = -4.6 + 0.182258 * vvo2max + 0.000104 * vvo2max * vvo2max;
                    if (Math.abs(newVdot - vdot) < 0.01) {
                        vdot = newVdot;
                        break;
                    }
                    vdot = newVdot;
                }
                return Math.max(10, Math.min(85, Number(vdot.toFixed(4))));
            };

            const predictRaceTimeSeconds = (vdot, distanceKey) => {
                if (!vdot || vdot <= 0) return 0;
                const distMeters = distanceMeters[distanceKey];
                if (!distMeters) return 0;
                const vvo2max = vvo2FromVDOT(vdot);
                const ratio = getRatioForDistance(distanceKey, vdot);
                const velocity = vvo2max * ratio;
                if (!velocity || velocity <= 0) return 0;
                return Math.round((distMeters / velocity) * 60);
            };

            const weeksUntilRace = computed(() => {
                if (!form.target_date) return 12;
                const target = new Date(form.target_date);
                if (Number.isNaN(target.getTime())) return 12;
                const diffDays = Math.ceil((target.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
                const weeks = Math.ceil(diffDays / 7);
                return Math.min(24, Math.max(8, weeks || 12));
            });

            const current_vdot = computed(() => {
                const t = (pb_hours.value * 3600) + (pb_minutes.value * 60) + pb_seconds.value;
                return calculateVDOTFromPerformance(form.pb_distance, t);
            });

            const target_vdot = computed(() => {
                const t = (goal_hours.value * 3600) + (goal_minutes.value * 60) + goal_seconds.value;
                return calculateVDOTFromPerformance(form.target_distance, t);
            });

            const recommendedImprovementPercent = computed(() => {
                const base = {
                    '5k': 0.06,
                    '10k': 0.05,
                    '21k': 0.04,
                    '42k': 0.03
                };
                const levelFactor = {
                    'beginner': 0.85,
                    'intermediate': 1,
                    'advanced': 1.1
                };
                const basePct = base[form.target_distance] ?? 0.04;
                const scale = Math.min(1.2, Math.max(0.4, weeksUntilRace.value / 16));
                const pct = basePct * scale * (levelFactor[form.runner_level] ?? 1);
                return Math.min(0.08, Math.max(0.015, pct));
            });

            const recommendedTargetVdot = computed(() => {
                const cv = current_vdot.value;
                if (!cv || cv <= 0) return 0;
                const target = cv * (1 + recommendedImprovementPercent.value);
                return Math.min(target, cv + 3.0);
            });

            const suggestGoalTime = () => {
                const cv = current_vdot.value;
                if (!cv || cv <= 0) return;
                const targetVdot = recommendedTargetVdot.value;
                const predictedSeconds = predictRaceTimeSeconds(targetVdot, form.target_distance);
                if (predictedSeconds > 0) {
                    goal_hours.value = Math.floor(predictedSeconds / 3600);
                    goal_minutes.value = Math.floor((predictedSeconds % 3600) / 60);
                    goal_seconds.value = Math.floor(predictedSeconds % 60);
                }
            };

            // Auto-suggest when PB or Target Distance changes
            watch([pb_hours, pb_minutes, pb_seconds, () => form.pb_distance, () => form.target_distance, () => form.target_date, () => form.runner_level], () => {
                suggestGoalTime();
                recommendMileage();
            });

            const realism = computed(() => {
                const cv = current_vdot.value;
                const tv = target_vdot.value;
                if (!cv || !tv) return null;
                const diff = tv - cv;
                const diffPercent = diff / cv;
                const rec = recommendedImprovementPercent.value;
                const diffLabel = Math.max(0, diffPercent) * 100;
                const recLabel = rec * 100;

                if (diff < 0) {
                    return { label: 'Mudah', color: 'bg-green-900/20 text-green-400 border-green-500/30', description: 'Target ini berada di bawah performa terbaik Anda saat ini.' };
                }
                if (diffPercent <= rec * 1.1) {
                    return { label: 'Realistis', color: 'bg-blue-900/20 text-blue-400 border-blue-500/30', description: `Target ini setara peningkatan sekitar ${diffLabel.toFixed(1)}% dari VDOT Anda. Rentang realistis saat ini ~${recLabel.toFixed(1)}%.` };
                }
                if (diffPercent <= rec * 1.6) {
                    return { label: 'Ambisius', color: 'bg-orange-900/20 text-orange-400 border-orange-500/30', description: `Peningkatan sekitar ${diffLabel.toFixed(1)}% tergolong menantang untuk jarak ${form.target_distance.toUpperCase()}.` };
                }
                return { label: 'Sangat Ambisius', color: 'bg-red-900/20 text-red-400 border-red-500/30', description: `Peningkatan sekitar ${diffLabel.toFixed(1)}% terlalu agresif untuk target ${form.target_distance.toUpperCase()}.` };
            });

            const idealMileage = computed(() => {
                const map = { '5k': 30, '10k': 45, '21k': 65, '42k': 85 };
                const levelFactor = {
                    'beginner': 0.9,
                    'intermediate': 1,
                    'advanced': 1.1
                };
                const base = map[form.target_distance] || 30;
                const adjusted = base * (levelFactor[form.runner_level] ?? 1);
                const rounded = Math.round(adjusted / 5) * 5;
                return Math.min(120, Math.max(15, rounded));
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
                
                // Format PB time
                const h = String(pb_hours.value || 0).padStart(2, '0');
                const m = String(pb_minutes.value || 0).padStart(2, '0');
                const s = String(pb_seconds.value || 0).padStart(2, '0');
                form.pb_time = `${h}:${m}:${s}`;

                // Format Goal time
                const gh = String(goal_hours.value || 0).padStart(2, '0');
                const gm = String(goal_minutes.value || 0).padStart(2, '0');
                const gs = String(goal_seconds.value || 0).padStart(2, '0');
                form.goal_time = `${gh}:${gm}:${gs}`;

                if (pb_hours.value === 0 && pb_minutes.value === 0 && pb_seconds.value === 0) {
                    showNotification('Harap isi waktu parameter test/PB!', 'error');
                    return;
                }

                if (goal_hours.value === 0 && goal_minutes.value === 0 && goal_seconds.value === 0) {
                    showNotification('Harap isi target waktu lomba!', 'error');
                    return;
                }

                if (!form.target_date) {
                    showNotification('Harap lengkapi target tanggal lomba!', 'error');
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
                    
                    if (data.success) {
                        result.value = data.data;
                        step.value = 2;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        errors.value = data.errors;
                        showNotification('Gagal memproses data. Silakan cek input Anda.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    showNotification('Terjadi kesalahan sistem.', 'error');
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
                        showNotification('Harap login terlebih dahulu.', 'error');
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
                            form: form,
                            result: result.value
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        window.location.href = '{{ route("runner.calendar") }}';
                    } else {
                        showNotification(data.message || 'Gagal menyimpan program.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    showNotification('Terjadi kesalahan sistem.', 'error');
                } finally {
                    saving.value = false;
                }
            };

            const getPaceLabel = (type) => {
                const labels = { 'E': 'Easy', 'M': 'Marathon', 'T': 'Threshold', 'I': 'Interval', 'R': 'Repetition' };
                return labels[type] || type;
            };

            const getPaceColor = (type) => {
                const colors = { 
                    'E': 'text-green-400', 
                    'M': 'text-blue-400', 
                    'T': 'text-orange-400', 
                    'I': 'text-red-400', 
                    'R': 'text-purple-400' 
                };
                return colors[type] || 'text-slate-400';
            };

            const formatPace = (pace) => {
                if (!pace || pace <= 0) return '-';
                const totalSeconds = Math.round(pace * 60);
                const m = Math.floor(totalSeconds / 60);
                const s = totalSeconds % 60;
                return `${m}:${String(s).padStart(2, '0')} /km`;
            };

            const getSessionClass = (type) => {
                const classes = {
                    'easy_run': 'bg-green-900/20 border-green-500/20',
                    'long_run': 'bg-blue-900/20 border-blue-500/20',
                    'marathon': 'bg-blue-900/20 border-blue-500/20',
                    'threshold': 'bg-orange-900/20 border-orange-500/20',
                    'interval': 'bg-red-900/20 border-red-500/20',
                    'repetition': 'bg-purple-900/20 border-purple-500/20',
                    'rest': 'bg-slate-900/40 border-slate-800 opacity-60'
                };
                return classes[type] || 'bg-slate-900/40 border-slate-800';
            };

            const getSessionIcon = (type) => {
                const icons = {
                    'easy_run': '🍃',
                    'rest': '🧘',
                    'long_run': '🔋',
                    'marathon': '🏁',
                    'threshold': '🔥',
                    'interval': '⚡',
                    'repetition': '🚀'
                };
                return icons[type] || '🏃';
            };

            return {
                step, form, loading, saving, result, freePreviewSessions, freeWeeksCount, sessionsByWeek, errors, notification,
                generateProgram, saveAndOpenCalendar,
                getPaceLabel, getPaceColor, formatPace, getSessionClass, getSessionIcon,
                pb_hours, pb_minutes, pb_seconds, 
                goal_hours, goal_minutes, goal_seconds,
                idealMileage, recommendMileage, realism,
                showNotification
            };
        }
    }).mount('#generator-v2-app');
</script>
@endpush
