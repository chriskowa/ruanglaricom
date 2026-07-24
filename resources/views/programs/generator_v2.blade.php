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
                    50: '#fcfef0',
                    100: '#f5facc',
                    200: '#ebf699',
                    300: '#def066',
                    400: '#d1e833',
                    500: '#ccff00',  // Neon Yellow/Green
                    600: '#b8e600',
                    700: '#94bf00',
                    800: '#719900',
                    900: '#4e7300',
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
            padding: 0.625rem 0.875rem;
            font-size: 0.8125rem;
            border-radius: 0.5rem;
            border: 1px solid #334155;
            background-color: #0b0f19 !important;
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
            font-size: 0.875rem;
        }

        .input-field:focus {
            border-color: #ccff00;
            box-shadow: 0 0 0 2px rgba(204, 255, 0, 0.1);
        }

        .label-text {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 0.375rem;
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

    <!-- Active Program Conflict Modal -->
    <transition name="fade">
        <div v-if="conflictModal.show" class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="card-dark max-w-md w-full p-6 rounded-2xl border border-amber-500/40 shadow-2xl space-y-5 relative">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Program Aktif Terdeteksi</h3>
                        <p class="text-xs text-slate-400">Kalender Anda sudah memiliki program aktif saat ini.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 space-y-1">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Program Aktif Saat Ini:</div>
                    <div class="text-sm font-bold text-white">@{{ conflictModal.activeTitle }}</div>
                    <div v-if="conflictModal.activeStartDate" class="text-[11px] text-slate-400">
                        Periode: @{{ conflictModal.activeStartDate }} - @{{ conflictModal.activeEndDate }}
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Apakah Anda ingin mengganti program lama dengan program baru ini, atau tetap menambahkan program baru ini ke kalender?
                </p>

                <div class="flex flex-col gap-2.5">
                    <button @click="confirmConflictAction('replace')" :disabled="saving" 
                            class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-dark font-black text-xs uppercase tracking-wider rounded-xl transition-all shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Ganti Program Aktif (Replace)</span>
                    </button>
                    <button @click="confirmConflictAction('add')" :disabled="saving" 
                            class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 font-bold text-xs uppercase tracking-wider rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambahkan Saja (Add)</span>
                    </button>
                    <button @click="conflictModal.show = false" :disabled="saving" 
                            class="w-full py-2 text-slate-500 hover:text-slate-300 text-xs font-bold transition-all cursor-pointer">
                        Batal
                    </button>
                </div>
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
                        <button @click="step = 1" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-dark font-black text-sm rounded-xl transition-all shadow-lg shadow-brand-500/20 hover:scale-[1.02] active:scale-[0.98] uppercase tracking-wider">
                            Buat Program Saya
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 1: Input Form -->
            <div v-else-if="step === 1" key="form" class="max-w-xl mx-auto">
                <div class="card-dark p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-black text-white uppercase tracking-tight">Parameter Latihan</h2>
                        <button @click="step = 0" class="text-slate-500 hover:text-slate-300 text-xs font-bold flex items-center gap-1">
                            <span>←</span> Kembali
                        </button>
                    </div>

                    <div class="flex flex-col gap-5">
                        <!-- PB Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="label-text">Parameter Test/PB</label>
                                <div class="flex flex-col gap-1">
                                    <select v-model="form.pb_distance" class="input-field cursor-pointer">
                                        <option value="5k">5 Kilometer</option>
                                        <option value="10k">10 Kilometer</option>
                                        <option value="21k">Half Marathon</option>
                                        <option value="42k">Full Marathon</option>
                                        <option value="cooper12">Cooper Test (12 Menit)</option>
                                        <option value="balke15">Balke Test (15 Menit)</option>
                                    </select>
                                    <span class="text-[9px] opacity-0 font-bold uppercase tracking-widest select-none">Spacer</span>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="label-text">@{{ (form.pb_distance === 'cooper12' || form.pb_distance === 'balke15') ? 'Jarak Parameter Test (Meter)' : 'Waktu Parameter Test / PB' }}</label>
                                
                                <!-- Standard Time Inputs -->
                                <div v-if="form.pb_distance !== 'cooper12' && form.pb_distance !== 'balke15'" class="grid grid-cols-3 gap-2">
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

                                <!-- Cooper/Balke Distance Input -->
                                <div v-else class="flex flex-col gap-1">
                                    <input v-model="pb_distance_meters" type="number" min="100" max="9999" class="input-field font-bold" placeholder="Contoh: 2800">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest select-none">Meter</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="label-text">Jenis Kelamin</label>
                                <div class="flex p-0.5 bg-slate-900 rounded-lg border border-slate-700">
                                    <button @click="form.gender = 'male'" :class="form.gender === 'male' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-500'" class="flex-1 py-1.5 rounded-md font-bold text-[10px] transition-all uppercase tracking-wider">Laki-laki</button>
                                    <button @click="form.gender = 'female'" :class="form.gender === 'female' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-500'" class="flex-1 py-1.5 rounded-md font-bold text-[10px] transition-all uppercase tracking-wider">Perempuan</button>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="label-text">Umur</label>
                                <input v-model="form.age" type="number" min="15" max="90" class="input-field font-bold">
                            </div>
                        </div>

                        <!-- Target Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="space-y-1.5">
                                <label class="label-text">Target Jarak</label>
                                <select v-model="form.target_distance" @change="recommendMileage" class="input-field cursor-pointer">
                                    <option value="5k">5K</option>
                                    <option value="10k">10K</option>
                                    <option value="21k">Half Marathon</option>
                                    <option value="42k">Full Marathon</option>
                                    <option value="cooper12">Cooper 12 Min</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="label-text">Tanggal Mulai Latihan</label>
                                <input v-model="form.start_date" type="date" class="input-field font-bold">
                            </div>
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <label class="label-text mb-0">Target Tanggal Lomba</label>
                                    <button v-if="recommendedTargetDate && form.target_date !== recommendedTargetDate" 
                                            @click="applyRecommendedTargetDate" 
                                            type="button" 
                                            class="text-[9px] font-extrabold text-neon bg-neon/10 hover:bg-neon/20 px-1.5 py-0.5 rounded border border-neon/30 transition">
                                        Pakai Rekomendasi (@{{ recommendedWeeks }} Wk)
                                    </button>
                                </div>
                                <input v-model="form.target_date" type="date" class="input-field font-bold">
                            </div>
                        </div>

                        <!-- Goal Time Section -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="label-text mb-0">Target Waktu Lomba</label>
                                <div v-if="realism" :class="realism.color" class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border">
                                    @{{ realism.label }}
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
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
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="label-text">Mileage Mingguan (Km)</label>
                                    <span class="text-[9px] font-bold text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded-full border border-brand-500/20">Rekomendasi: @{{ idealMileage }} Km</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <input v-model="form.weekly_mileage" type="range" min="15" max="120" step="5" class="flex-1 h-1 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-500">
                                    <span class="w-10 text-center font-black text-white text-base">@{{ form.weekly_mileage }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="label-text">Frekuensi Latihan (Hari/Minggu)</label>
                                <div class="flex justify-between gap-2">
                                    <button v-for="f in [3,4,5,6,7]" :key="f" @click="form.frequency = f" 
                                             :class="form.frequency === f ? 'bg-brand-500 text-dark border-brand-500' : 'bg-slate-900/50 text-slate-500 border border-slate-700 hover:border-slate-600'"
                                             class="w-full py-1.5 rounded-lg font-bold text-xs transition-all border">
                                        @{{ f }}
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between items-center">
                                        <label class="label-text mb-0">Level Pelari</label>
                                        <span class="text-[9px] font-bold text-cyan-400 bg-cyan-500/10 px-1.5 py-0.5 rounded border border-cyan-500/20">Auto (VDOT: @{{ current_vdot ? current_vdot.toFixed(1) : '-' }})</span>
                                    </div>
                                    <select v-model="form.runner_level" class="input-field cursor-pointer font-bold">
                                        <option value="beginner">Pemula (Beginner - VDOT &lt; 35)</option>
                                        <option value="intermediate">Menengah (Intermediate - VDOT 35-48)</option>
                                        <option value="advanced">Mahir / Elite (Advanced - VDOT &gt; 48)</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="label-text">Hari Long Run</label>
                                    <select v-model="form.long_run_day" class="input-field cursor-pointer">
                                        <option value="saturday">Sabtu</option>
                                        <option value="sunday">Minggu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="label-text">Penyesuaian Suhu Tropis (Indonesia)</label>
                                <div class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-xl border border-slate-700/50">
                                    <input type="checkbox" v-model="form.is_tropical" id="is_tropical" class="w-4 h-4 accent-brand-500 cursor-pointer rounded border-slate-600 bg-slate-800">
                                    <label for="is_tropical" class="text-[11px] text-slate-300 font-bold cursor-pointer select-none">
                                        Aktifkan penyesuaian pace untuk cuaca panas (+10-15s/km untuk menjaga beban kardio/HR stabil)
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="label-text flex items-center gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles text-white-400 text-xs"></i>
                                    <span>AI Narration Refinement (Opsional)</span>
                                </label>
                                <div class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-xl border border-slate-700/50">
                                    <input type="checkbox" v-model="form.use_ai" id="use_ai" class="w-4 h-4 accent-white-500 cursor-pointer rounded border-slate-600 bg-slate-800">
                                    <label for="use_ai" class="text-[11px] text-slate-300 font-bold cursor-pointer select-none">
                                        Sempurnakan narasi deskripsi tiap sesi menggunakan OpenAI <span class="text-white-400 font-normal">(Proses butuh +5–10 detik)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button @click="generateProgram" :disabled="loading" 
                                class="w-full py-3 bg-brand-500 hover:bg-brand-600 disabled:bg-slate-700 disabled:text-slate-500 text-dark font-black text-sm rounded-xl transition-all shadow-md shadow-brand-500/10 flex items-center justify-center gap-3">
                            <span v-if="!loading">GENERATE PROGRAM</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-5 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left: Summary Sidebar -->
                    <div class="lg:col-span-1 space-y-5">
                        <div class="card-dark p-6 rounded-2xl border-t-4 border-t-brand-500 shadow-xl relative">
                            <div class="text-center mb-5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-gauge-high text-brand-400"></i>
                                    <span>ESTIMASI SKOR VDOT</span>
                                </div>
                                <div class="text-4xl font-extrabold text-white tracking-tight">@{{ result?.vdot }}</div>
                            </div>
                            
                            <div class="space-y-2.5 py-3 border-y border-slate-700/60 mb-5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-bullseye text-slate-500 text-[10px]"></i> Target Jarak
                                    </span>
                                    <span class="font-extrabold text-white uppercase">@{{ form.target_distance }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar-days text-slate-500 text-[10px]"></i> Durasi Program
                                    </span>
                                    <span class="font-extrabold text-white">@{{ result?.weeks }} Minggu</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-person-running text-slate-500 text-[10px]"></i> Frekuensi Latihan
                                    </span>
                                    <span class="font-extrabold text-white">@{{ form.frequency }}x / minggu</span>
                                </div>
                            </div>

                            <!-- Highlighted Save to Calendar Button -->
                            <button @click="saveAndOpenCalendar()" :disabled="saving" class="w-full py-3.5 px-4 bg-gradient-to-r from-neon via-lime-400 to-emerald-400 hover:from-white hover:to-neon text-dark font-black text-xs uppercase tracking-wider rounded-xl transition-all duration-300 shadow-lg shadow-neon/20 hover:shadow-neon/40 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 mb-2.5 border border-neon/40">
                                <i v-if="!saving" class="fa-solid fa-calendar-check text-sm"></i>
                                <i v-else class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                                <span>@{{ saving ? 'MENYIMPAN...' : 'SIMPAN KE KALENDER' }}</span>
                            </button>
                            <button @click="step = 1" class="w-full py-2.5 bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white font-bold rounded-xl transition-all text-xs flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-sliders text-slate-400 text-[11px]"></i>
                                <span>EDIT PARAMETER</span>
                            </button>
                        </div>

                        <!-- Training Paces & Heart Rate Zones Card -->
                        <div class="card-dark p-5 rounded-2xl border border-slate-800 shadow-lg">
                            <div class="flex items-center gap-2 mb-4 pb-2.5 border-b border-slate-800">
                                <i class="fa-solid fa-heart-pulse text-brand-400 text-sm"></i>
                                <h3 class="text-xs font-extrabold text-slate-300 uppercase tracking-wider">Target Pace & Zona HR</h3>
                            </div>
                            <div class="space-y-2">
                                <div v-for="(pace, type) in (result?.paces || {})" :key="type" class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 space-y-1 hover:border-slate-700 transition">
                                    <div class="flex justify-between items-center">
                                        <span class="font-extrabold text-xs uppercase flex items-center gap-1.5" :class="getPaceColor(type)">
                                            <span v-html="getSessionIcon(type)" class="text-xs"></span>
                                            <span>@{{ getPaceLabel(type) }}</span>
                                        </span>
                                        <span class="font-mono font-extrabold text-xs text-white">@{{ formatPace(pace) }}</span>
                                    </div>
                                    <div v-if="result?.hr_zones && result.hr_zones[type]" class="flex justify-between items-center text-[10px] text-slate-400 pt-1 border-t border-slate-800/50">
                                        <span>Target HR</span>
                                        <span class="font-bold text-slate-300">@{{ result.hr_zones[type].min }}-@{{ result.hr_zones[type].max }} BPM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Program Preview (Free Weeks) -->
                    <div class="lg:col-span-2 space-y-5">
                        <div v-for="(weekSessions, weekNum) in sessionsByWeek" :key="weekNum" class="card-dark p-5 rounded-2xl border border-slate-800 shadow-xl">
                            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800/80">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-brand-400 text-xs font-bold">
                                        <i class="fa-solid fa-calendar-week"></i>
                                    </div>
                                    <h3 class="text-sm font-extrabold text-white uppercase tracking-tight">
                                        Preview Minggu @{{ weekNum }}
                                    </h3>
                                    <span v-if="weekSessions && weekSessions.length > 0 && weekSessions[0].is_deload" 
                                          class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold rounded-md border border-emerald-500/20 uppercase tracking-wider flex items-center gap-1">
                                        <i class="fa-solid fa-battery-half text-emerald-400 text-[10px]"></i> De-load / Pemulihan
                                    </span>
                                </div>
                                <span class="px-2.5 py-1 bg-brand-500/10 text-brand-400 text-[10px] font-extrabold rounded-lg border border-brand-500/20 uppercase tracking-wider">Akses Gratis</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-2.5">
                                <div v-for="day in weekSessions" :key="day.day" 
                                     class="p-3 rounded-xl border min-h-[120px] flex flex-col justify-between transition-all hover:border-brand-500/50 group"
                                     :class="getSessionClass(day.type)">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Hari @{{ day.day }}</span>
                                        <span class="text-sm text-slate-300 group-hover:scale-110 transition-transform duration-200" v-html="getSessionIcon(day.type)"></span>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-extrabold text-white leading-tight mb-1 uppercase tracking-tight">@{{ day.type.replace('_', ' ') }}</h4>
                                        <p class="text-xs font-extrabold text-white">@{{ day.distance }} <span class="text-[9px] font-normal text-slate-400">KM</span></p>
                                        <p v-if="day.target_pace" class="text-[9px] font-mono font-bold text-brand-400 mt-0.5">@{{ day.target_pace }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Program Conflict Modal -->
        <div v-if="conflictModal.show" class="fixed inset-0 z-[1050] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="conflictModal.show = false"></div>
            <div class="relative z-10 flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-5 text-left">
                    <!-- Header -->
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white uppercase tracking-tight">Program Aktif Terdeteksi</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Anda memiliki program aktif di kalender</p>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400 font-mono">Program Aktif:</span>
                            <span class="font-extrabold text-amber-400">@{{ conflictModal.activeTitle }}</span>
                        </div>
                        <div v-if="conflictModal.activeStartDate" class="flex justify-between items-center text-xs">
                            <span class="text-slate-400 font-mono">Periode:</span>
                            <span class="font-bold text-slate-200">@{{ conflictModal.activeStartDate }} - @{{ conflictModal.activeEndDate }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-300 leading-relaxed">
                        Menyimpan program baru ini akan <strong>menonaktifkan (replace)</strong> program aktif lama Anda di kalender. Apakah Anda yakin ingin melanjutkan?
                    </p>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="conflictModal.show = false" 
                                class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 font-bold rounded-xl text-xs transition">
                            Batal
                        </button>
                        <button type="button" @click="confirmConflictAction('replace')" 
                                class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black rounded-xl text-xs uppercase tracking-wider transition shadow-lg shadow-amber-500/20">
                            Ganti Program
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

            // Default standard benchmark PB times (5K 30m, 10K 1h, 21K 2h15m, 42K 4h30m)
            const defaultPbTimes = {
                '5k':       { h: 0, m: 30, s: 0 },
                '10k':      { h: 1, m: 0,  s: 0 },
                '21k':      { h: 2, m: 15, s: 0 },
                '42k':      { h: 4, m: 30, s: 0 },
                'cooper12': { meters: 2400 },
                'balke15':  { meters: 3000 }
            };

            const pb_hours = ref(0);
            const pb_minutes = ref(30);
            const pb_seconds = ref(0);
            const pb_distance_meters = ref(2400);

            const goal_hours = ref(0);
            const goal_minutes = ref(0);
            const goal_seconds = ref(0);

            const todayStr = new Date().toISOString().split('T')[0];

            const conflictModal = reactive({
                show: false,
                activeTitle: '',
                activeStartDate: '',
                activeEndDate: ''
            });

            const form = reactive({
                pb_distance: '5k',
                pb_time: '',
                target_distance: '10k',
                start_date: todayStr,
                target_date: '',
                goal_time: '',
                weekly_mileage: 50,
                frequency: 4,
                gender: 'male',
                age: 25,
                runner_level: 'intermediate',
                long_run_day: 'sunday',
                is_tropical: false,
                use_ai: false
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
                '42k': 42.195,
                'cooper12': 3.2
            };

            const distanceMeters = {
                '5k': 5000,
                '10k': 10000,
                '21k': 21097.5,
                '42k': 42195,
                'cooper12': 3200
            };

            const getRatioForDistance = (distanceKey, vdot) => {
                const ratios = {
                    '5k': 0.957,
                    '10k': 0.915,
                    '21k': 0.865,
                    '42k': 0.815,
                    'cooper12': 0.99
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

                let start = Date.now();
                if (form.start_date) {
                    const parsedStart = new Date(form.start_date);
                    if (!Number.isNaN(parsedStart.getTime())) {
                        start = parsedStart.getTime();
                    }
                }

                const diffDays = Math.ceil((target.getTime() - start) / (1000 * 60 * 60 * 24));
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
                    '42k': 0.03,
                    'cooper12': 0.07
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

            const applyDefaultPbTime = (distKey) => {
                const defaults = defaultPbTimes[distKey] || defaultPbTimes['5k'];
                if (distKey === 'cooper12' || distKey === 'balke15') {
                    pb_distance_meters.value = defaults.meters || 2400;
                } else {
                    pb_hours.value = defaults.h;
                    pb_minutes.value = defaults.m;
                    pb_seconds.value = defaults.s;
                }
            };

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

            const recommendedDurationWeeksMap = {
                '5k': 10,
                '10k': 12,
                '21k': 14,
                '42k': 16,
                'cooper12': 8,
                'balke15': 8
            };

            const recommendedWeeks = computed(() => {
                return recommendedDurationWeeksMap[form.target_distance] || 12;
            });

            const recommendedTargetDate = computed(() => {
                if (!form.start_date) return '';
                const startDateObj = new Date(form.start_date);
                if (isNaN(startDateObj.getTime())) return '';
                
                const recWeeks = recommendedWeeks.value;
                const targetDateObj = new Date(startDateObj.getTime() + (recWeeks * 7 - 1) * 24 * 60 * 60 * 1000);
                
                const year = targetDateObj.getFullYear();
                const month = String(targetDateObj.getMonth() + 1).padStart(2, '0');
                const day = String(targetDateObj.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            });

            const applyRecommendedTargetDate = () => {
                if (recommendedTargetDate.value) {
                    form.target_date = recommendedTargetDate.value;
                }
            };

            const autoDetermineRunnerLevel = (vdot) => {
                if (!vdot || vdot <= 0) return 'intermediate';
                if (vdot < 40) return 'beginner';
                if (vdot < 55) return 'intermediate';
                return 'advanced';
            };

            watch(current_vdot, (newVdot) => {
                if (newVdot && newVdot > 0) {
                    form.runner_level = autoDetermineRunnerLevel(newVdot);
                }
            }, { immediate: true });

            watch([() => form.start_date, () => form.target_distance], ([newStartDate, newDist], [oldStartDate, oldDist]) => {
                if (recommendedTargetDate.value && (!form.target_date || newDist !== oldDist)) {
                    applyRecommendedTargetDate();
                }
            });

            // Watch PB distance change to auto fill default standard PB time
            watch(() => form.pb_distance, (newDist) => {
                applyDefaultPbTime(newDist);
            });

            // Auto-suggest when PB or Target Distance or Runner Level changes
            watch([pb_hours, pb_minutes, pb_seconds, pb_distance_meters, () => form.pb_distance, () => form.target_distance, () => form.start_date, () => form.target_date, () => form.runner_level], () => {
                suggestGoalTime();
                recommendMileage();
            });

            onMounted(() => {
                applyDefaultPbTime(form.pb_distance);
                if (!form.target_date) {
                    applyRecommendedTargetDate();
                }
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
                // Realistic Daniels / Pfitzinger Recreational Coach Weekly Mileage Standards
                const baseMileageMap = {
                    '5k':       { beginner: 25, intermediate: 35, advanced: 50 },
                    '10k':      { beginner: 30, intermediate: 45, advanced: 60 },
                    '21k':      { beginner: 35, intermediate: 50, advanced: 70 },
                    '42k':      { beginner: 45, intermediate: 65, advanced: 85 },
                    'cooper12': { beginner: 25, intermediate: 35, advanced: 50 },
                    'balke15':  { beginner: 25, intermediate: 35, advanced: 50 }
                };

                const level = form.runner_level || 'intermediate';
                const dist = form.target_distance || '10k';
                let base = baseMileageMap[dist]?.[level] || 45;

                const rounded = Math.round(base / 5) * 5;
                return Math.min(120, Math.max(20, rounded));
            });

            const recommendMileage = () => {
                form.weekly_mileage = idealMileage.value;
            };

            const freePreviewSessions = computed(() => {
                if (!result.value) return [];
                return result.value.sessions; // Show all sessions
            });

            const freeWeeksCount = computed(() => {
                if (!result.value) return 0;
                return result.value.weeks; // Show full program in preview
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
                if (form.pb_distance === 'cooper12' || form.pb_distance === 'balke15') {
                    form.pb_time = String(pb_distance_meters.value || 0);
                } else {
                    const h = String(pb_hours.value || 0).padStart(2, '0');
                    const m = String(pb_minutes.value || 0).padStart(2, '0');
                    const s = String(pb_seconds.value || 0).padStart(2, '0');
                    form.pb_time = `${h}:${m}:${s}`;
                }

                // Format Goal time
                const gh = String(goal_hours.value || 0).padStart(2, '0');
                const gm = String(goal_minutes.value || 0).padStart(2, '0');
                const gs = String(goal_seconds.value || 0).padStart(2, '0');
                form.goal_time = `${gh}:${gm}:${gs}`;

                if (form.pb_distance !== 'cooper12' && form.pb_distance !== 'balke15') {
                    if (pb_hours.value === 0 && pb_minutes.value === 0 && pb_seconds.value === 0) {
                        showNotification('Harap isi waktu parameter test/PB!', 'error');
                        return;
                    }
                } else {
                    if (!pb_distance_meters.value || pb_distance_meters.value <= 0) {
                        showNotification('Harap isi jarak hasil tes parameter!', 'error');
                        return;
                    }
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
                    const response = await fetch('{{ route("generator.generate", [], false) }}', {
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

            const saveAndOpenCalendar = async (overrideAction = null) => {
                const actionParam = (typeof overrideAction === 'string' && (overrideAction === 'replace' || overrideAction === 'add')) ? overrideAction : null;

                @guest
                    // Store state in session before showing login modal
                    try {
                        await fetch('{{ route("generator.store-pending", [], false) }}', {
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
                    const payload = {
                        form: form,
                        result: result.value
                    };
                    if (actionParam) {
                        payload.action = actionParam;
                    }

                    const response = await fetch('{{ route("generator.save", [], false) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (data.has_active_program && !actionParam) {
                        conflictModal.activeTitle = data.active_program_title || 'Program Aktif';
                        conflictModal.activeStartDate = data.active_start_date || '';
                        conflictModal.activeEndDate = data.active_end_date || '';
                        conflictModal.show = true;
                        return;
                    }

                    if (data.success) {
                        window.location.href = '{{ route("runner.calendar", [], false) }}';
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

            const confirmConflictAction = (actionType) => {
                conflictModal.show = false;
                saveAndOpenCalendar(actionType);
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
                    'easy_run': 'bg-green-900/20 border-green-500/20 text-green-400',
                    'long_run': 'bg-blue-900/20 border-blue-500/20 text-blue-400',
                    'marathon': 'bg-cyan-900/20 border-cyan-500/20 text-cyan-400',
                    'tempo': 'bg-orange-900/20 border-orange-500/20 text-orange-400',
                    'threshold': 'bg-orange-900/20 border-orange-500/20 text-orange-400',
                    'interval': 'bg-red-900/20 border-red-500/20 text-red-400',
                    'repetition': 'bg-fuchsia-900/20 border-fuchsia-500/20 text-fuchsia-400',
                    'hill': 'bg-sky-900/20 border-sky-500/20 text-sky-400',
                    'rest': 'bg-slate-900/40 border-slate-800 opacity-60 text-slate-400'
                };
                return classes[type] || 'bg-slate-900/40 border-slate-800';
            };

            const getSessionIcon = (type) => {
                const icons = {
                    'easy_run': '<i class="fa-solid fa-leaf"></i>',
                    'rest': '<i class="fa-solid fa-bed"></i>',
                    'long_run': '<i class="fa-solid fa-battery-full"></i>',
                    'marathon': '<i class="fa-solid fa-flag-checkered"></i>',
                    'tempo': '<i class="fa-solid fa-fire"></i>',
                    'threshold': '<i class="fa-solid fa-fire"></i>',
                    'interval': '<i class="fa-solid fa-bolt"></i>',
                    'repetition': '<i class="fa-solid fa-rocket"></i>',
                    'hill': '<i class="fa-solid fa-mountain"></i>'
                };
                return icons[type] || '<i class="fa-solid fa-person-running"></i>';
            };

            return {
                step, form, loading, saving, result, freePreviewSessions, freeWeeksCount, sessionsByWeek, errors, notification,
                conflictModal, confirmConflictAction,
                generateProgram, saveAndOpenCalendar,
                getPaceLabel, getPaceColor, formatPace, getSessionClass, getSessionIcon,
                pb_hours, pb_minutes, pb_seconds, pb_distance_meters,
                goal_hours, goal_minutes, goal_seconds,
                idealMileage, recommendMileage, realism,
                showNotification
            };
        }
    }).mount('#generator-v2-app');
</script>
@endpush
