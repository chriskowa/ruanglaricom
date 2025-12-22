@extends('layouts.app')

@section('title', 'Realistic Values - Design Program')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        neon: {
                            cyan: '#06b6d4',
                            purple: '#a855f7',
                            green: '#22c55e',
                            dark: '#0f172a',
                            card: '#1e293b'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        'neon-cyan': '0 0 10px rgba(6, 182, 212, 0.5), 0 0 20px rgba(6, 182, 212, 0.3)',
                        'neon-purple': '0 0 10px rgba(168, 85, 247, 0.5), 0 0 20px rgba(168, 85, 247, 0.3)',
                    },
                    animation: {
                        'pulse-fast': 'pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'scan': 'scan 2s linear infinite',
                    },
                    keyframes: {
                        scan: {
                            '0%': { transform: 'translateY(-100%)' },
                            '100%': { transform: 'translateY(100%)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* Scoped styles for this page */
        .design-program-wrapper { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            position: relative;
            /* Override bootstrap defaults if necessary */
        }
        
        .design-program-wrapper .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .design-program-wrapper .input-neon {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid #334155;
            color: white;
            transition: all 0.3s ease;
        }
        .design-program-wrapper .input-neon:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
            outline: none;
        }
        
        .design-program-wrapper ::-webkit-scrollbar { width: 8px; }
        .design-program-wrapper ::-webkit-scrollbar-track { background: #0f172a; }
        .design-program-wrapper ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .design-program-wrapper ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }

        /* Ensure Tailwind preflight doesn't break everything else, or accept it */
    </style>
@endpush

@section('content')
<div id="app" class="design-program-wrapper relative w-full h-full px-4 pb-4 pt-28 rounded-xl">
        
    <div class="absolute inset-0 z-0 pointer-events-none opacity-20" 
            style="background-image: linear-gradient(#334155 1px, transparent 1px), linear-gradient(to right, #334155 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <!-- Replaced Navbar with internal header -->
    <div class="relative z-10 w-full mb-8 flex justify-between items-center border-b border-slate-700 pb-4">
        <div class="flex items-center cursor-pointer group" @click="resetForm">
            <div class="w-2 h-8 bg-cyan-400 mr-3 shadow-neon-cyan group-hover:h-10 transition-all duration-300"></div>
            <span class="font-bold text-xl tracking-tighter text-white">
                REALISTIC<span class="text-cyan-400">VALUES</span>
            </span>
        </div>
        <div class="hidden md:block font-mono text-xs text-cyan-400 border border-cyan-900 bg-cyan-900/20 px-3 py-1 rounded">
            SYSTEM: ONLINE // VDOT: ACTIVE
        </div>
    </div>

    <main class="relative z-10 flex-grow flex flex-col justify-center items-center w-full">
        
        <transition name="fade" mode="out-in">
            
            <div v-if="step === 0" key="hero" class="text-center max-w-4xl py-10">
                <div class="inline-block mb-4 px-4 py-1 rounded-full bg-purple-900/30 border border-purple-500/50 text-purple-300 text-xs font-mono tracking-widest uppercase animate-pulse">
                    Algoritma Kepelatihan v2.0
                </div>
                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white via-cyan-100 to-cyan-400">
                    DESIGNING BY <br> RECOVERY
                </h1>
                <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Kami tidak menjual mimpi. Kami menghitung <span class="text-cyan-400 font-bold">Gap</span>, menganalisis <span class="text-cyan-400 font-bold">Realita</span>, dan membangun <span class="text-purple-400 font-bold">Tangga Progresi</span> (Ladder) khusus untuk fisiologis Anda.
                </p>
                <button @click="startAssessment" class="group relative px-8 py-4 bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-bold text-lg rounded-sm transition-all shadow-neon-cyan hover:scale-105">
                    <span class="relative z-10">AUDIT PERFORMA SEKARANG</span>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                </button>
            </div>

            <div v-else-if="step === 99" key="loading" class="text-center py-20">
                <div class="relative w-24 h-24 mx-auto mb-8 border-2 border-slate-700 rounded-full flex items-center justify-center overflow-hidden bg-slate-800">
                    <div class="absolute inset-0 bg-cyan-500/20 animate-scan w-full h-full border-b-2 border-cyan-400"></div>
                    <span class="font-mono text-2xl text-cyan-400 font-bold">@{{ loadingPercentage }}%</span>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Menganalisis Biometrik</h2>
                <p class="text-slate-400 font-mono text-sm animate-pulse">@{{ loadingText }}</p>
            </div>

            <div v-else-if="step > 0 && step < 5" key="form" class="w-full max-w-2xl">
                <div class="flex justify-between mb-8 px-2">
                    <div v-for="i in 4" :key="i" class="h-1 flex-1 mx-1 rounded-full transition-all duration-500" 
                            :class="i <= step ? 'bg-cyan-500 shadow-neon-cyan' : 'bg-slate-700'"></div>
                </div>

                <div class="glass rounded-xl p-8 md:p-10 shadow-2xl relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>

                    <div v-if="step === 1" class="space-y-6 relative z-10">
                        <h2 class="text-3xl font-bold text-white">Profil Atlet</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Nama Lengkap</label>
                                <input v-model="form.name" type="text" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 focus:shadow-neon-cyan outline-none transition-all placeholder-slate-500" placeholder="Ketik nama Anda...">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Usia</label>
                                    <input v-model.number="form.age" type="number" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Gender</label>
                                    <select v-model="form.gender" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none appearance-none">
                                        <option>Pria</option>
                                        <option>Wanita</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="step === 2" class="space-y-6 relative z-10">
                        <h2 class="text-3xl font-bold text-white">Audit Historis</h2>
                        <p class="text-slate-400 text-sm">Faktor genetik dan masa kecil ("God's Gift") mempengaruhi daya tahan.</p>
                        
                        <div class="space-y-4">
                            <label class="block text-xs font-mono text-cyan-400 uppercase">Masa Kecil (5-15 Tahun)</label>
                            <div class="grid gap-3">
                                <button @click="form.childhood = 'active'" 
                                    :class="form.childhood === 'active' ? 'border-cyan-500 bg-cyan-900/30 text-cyan-300' : 'border-slate-700 hover:border-slate-500 text-slate-400'"
                                    class="p-4 border rounded text-left transition-all">
                                    <span class="font-bold block">Aktif Outdoor / Alam</span>
                                    <span class="text-xs opacity-70">Sering berenang, lari di pantai/bukit. Kapasitas paru tinggi.</span>
                                </button>
                                <button @click="form.childhood = 'labor'" 
                                    :class="form.childhood === 'labor' ? 'border-purple-500 bg-purple-900/30 text-purple-300' : 'border-slate-700 hover:border-slate-500 text-slate-400'"
                                    class="p-4 border rounded text-left transition-all">
                                    <span class="font-bold block">Fisik Keras / Survival</span>
                                    <span class="text-xs opacity-70">Membantu orang tua bekerja, jalan kaki jauh. Mental baja.</span>
                                </button>
                                <button @click="form.childhood = 'sedentary'" 
                                    :class="form.childhood === 'sedentary' ? 'border-slate-500 bg-slate-800 text-slate-300' : 'border-slate-700 hover:border-slate-500 text-slate-400'"
                                    class="p-4 border rounded text-left transition-all">
                                    <span class="font-bold block">Normal / Sedenter</span>
                                    <span class="text-xs opacity-70">Gaya hidup rumahan atau perkotaan standar.</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="step === 3" class="space-y-6 relative z-10">
                        <h2 class="text-3xl font-bold text-white">Cek Realita</h2>
                        <p class="text-slate-400 text-sm">Masukkan data tes lari terakhir yang <b>jujur</b>.</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Jarak Tes</label>
                                <select v-model="form.latestDistance" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                                    <option value="5">5 Kilometer</option>
                                    <option value="10">10 Kilometer</option>
                                    <option value="21.1">Half Marathon</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Waktu Tempuh (MM:SS)</label>
                                <div class="flex gap-2">
                                    <input v-model="form.timeMin" type="number" placeholder="Menit (cth: 52)" class="w-1/2 p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 focus:shadow-neon-cyan outline-none text-center font-mono text-lg">
                                    <span class="text-2xl text-slate-500 self-center">:</span>
                                    <input v-model="form.timeSec" type="number" placeholder="Detik (cth: 30)" class="w-1/2 p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 focus:shadow-neon-cyan outline-none text-center font-mono text-lg">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Volume Lari Mingguan (KM)</label>
                                <input v-model.number="form.weeklyVolume" type="number" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none" placeholder="Rata-rata sebulan terakhir">
                            </div>
                        </div>
                    </div>

                    <div v-if="step === 4" class="space-y-6 relative z-10">
                        <h2 class="text-3xl font-bold text-white">Target & Komitmen</h2>
                        
                        <div>
                            <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Target Waktu (Opsional)</label>
                            <input v-model="form.goalDescription" type="text" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none" placeholder="Contoh: Ingin Sub 45 menit">
                        </div>

                        <div class="p-4 bg-cyan-900/20 border border-cyan-800 rounded-lg">
                            <h4 class="text-cyan-400 font-bold text-sm mb-1 flex items-center">
                                <span class="mr-2">⚠</span> PERINGATAN ALGORITMA
                            </h4>
                            <p class="text-xs text-slate-300">
                                Sistem akan menghitung "Gap" Anda. Jika target Anda terlalu jauh (misal saat ini 52 menit ingin ke 45 menit), kami akan mereset target Anda ke "Anak Tangga" terdekat (misal: 51 menit) untuk mencegah cedera.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between relative z-10">
                        <button v-if="step > 1" @click="step--" class="text-slate-400 hover:text-white font-mono text-sm px-4 py-2">
                            [ KEMBALI ]
                        </button>
                        <div v-else></div>

                        <button v-if="step < 4" @click="step++" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2 rounded font-bold transition-colors">
                            LANJUT >
                        </button>
                        <button v-else @click="submitAssessment" class="bg-gradient-to-r from-cyan-600 to-cyan-400 hover:from-cyan-500 hover:to-cyan-300 text-slate-900 font-bold px-8 py-3 rounded shadow-neon-cyan transform hover:scale-105 transition-all">
                            GENERATE PROGRAM
                        </button>
                    </div>
                </div>
            </div>

            <div v-else-if="step === 5" key="result" class="w-full max-w-5xl">
                
                <div class="text-center mb-10">
                    <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-purple-400 mb-2">
                        BLUEPRINT LATIHAN ANDA
                    </h2>
                    <p class="text-slate-400 font-mono">Berdasarkan VDOT Calculation & The Ladder Philosophy</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="space-y-6">
                        <div class="glass p-6 rounded-xl border border-slate-700">
                            <h3 class="text-xs font-mono text-slate-400 uppercase mb-4 border-b border-slate-700 pb-2">Analisis Gap</h3>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-slate-400">Saat Ini</span>
                                <span class="text-2xl font-bold text-white">@{{ formattedCurrentTime }}</span>
                            </div>
                            <div class="flex justify-between items-end mb-4">
                                <span class="text-slate-400">Target Ladder</span>
                                <span class="text-2xl font-bold text-neon-green text-green-400">@{{ ladderTarget }}</span>
                            </div>
                            <div class="text-xs text-slate-500 italic">
                                *Target jangka panjang disembunyikan. Fokus pada target ladder minggu ini.
                            </div>
                        </div>

                        <div class="glass p-6 rounded-xl border border-cyan-900 shadow-neon-cyan">
                            <h3 class="text-xs font-mono text-cyan-400 uppercase mb-4 border-b border-cyan-900 pb-2">Zona Pace (Min/Km)</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-green-400 font-bold">Easy / Recovery</span>
                                        <span class="text-white font-mono">@{{ paces.easy }}</span>
                                    </div>
                                    <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-green-500 h-1 rounded" style="width: 50%"></div></div>
                                    <p class="text-[10px] text-slate-400 mt-1">Lari santai, bisa ngobrol. Wajib disiplin pelan.</p>
                                </div>

                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-yellow-400 font-bold">Tempo / Threshold</span>
                                        <span class="text-white font-mono">@{{ paces.tempo }}</span>
                                    </div>
                                    <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-yellow-500 h-1 rounded" style="width: 75%"></div></div>
                                    <p class="text-[10px] text-slate-400 mt-1">Tidak nyaman tapi terkontrol (Sakit yang enak).</p>
                                </div>

                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-purple-400 font-bold">Interval / VO2Max</span>
                                        <span class="text-white font-mono">@{{ paces.interval }}</span>
                                    </div>
                                    <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-purple-500 h-1 rounded" style="width: 90%"></div></div>
                                    <p class="text-[10px] text-slate-400 mt-1">Gas pol. Nafas berat.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="glass rounded-xl overflow-hidden border border-slate-700">
                            <div class="bg-slate-800/50 p-4 border-b border-slate-700 flex justify-between items-center">
                                <h3 class="font-bold text-white">Jadwal Minggu 1</h3>
                                <span class="text-xs bg-cyan-900 text-cyan-300 px-2 py-1 rounded">Fase: Conditioning</span>
                            </div>
                            
                            <div class="divide-y divide-slate-700">
                                <div v-for="(day, index) in programSchedule" :key="index" class="p-4 hover:bg-slate-800/50 transition-colors group">
                                    <div class="flex items-start">
                                        <div class="w-16 flex-shrink-0 pt-1">
                                            <span class="text-xs font-mono text-slate-500 uppercase">@{{ day.day }}</span>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">@{{ day.title }}</h4>
                                            <p class="text-sm text-slate-400 mt-1">@{{ day.desc }}</p>
                                            <div v-if="day.pace" class="mt-2 inline-block px-2 py-1 bg-slate-900 rounded border border-slate-700 text-xs font-mono text-cyan-300">
                                                Target Pace: @{{ day.pace }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <span v-if="day.type === 'rest'" class="text-slate-600 text-xl">☾</span>
                                            <span v-else-if="day.type === 'easy'" class="text-green-500 text-xl">♥</span>
                                            <span v-else-if="day.type === 'hard'" class="text-purple-500 text-xl">⚡</span>
                                            <span v-else-if="day.type === 'long'" class="text-yellow-500 text-xl">∞</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-4">
                            <button @click="resetForm" class="text-slate-400 hover:text-white text-sm underline">Reset Data</button>
                            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded border border-slate-600 text-sm">
                                Simpan PDF
                            </button>
                            <a href="https://wa.me/?text=Halo%20Coach,%20saya%20sudah%20generate%20program." target="_blank" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded shadow-lg text-sm flex items-center">
                                Konsultasi Coach
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </transition>
    </main>
</div>
@endsection

@push('scripts')
<script type="module">
    import { createApp, ref, reactive, computed } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'
    // Firebase imports removed for this implementation as it requires config which is in the original file
    // We can add it back if needed, but for now we focus on the logic working in Laravel

    createApp({
        setup() {
            const step = ref(0);
            const loadingPercentage = ref(0);
            const loadingText = ref('Connecting to server...');
            
            const form = reactive({
                name: '{{ auth()->user()->name ?? "" }}',
                age: '',
                gender: 'Pria',
                childhood: '',
                latestDistance: '10', // Default 10K
                timeMin: '',
                timeSec: '',
                weeklyVolume: '',
                goalDescription: ''
            });

            // Hasil Analisis
            const paces = reactive({ easy: '', tempo: '', interval: '' });
            const programSchedule = ref([]);
            const formattedCurrentTime = ref('');
            const ladderTarget = ref('');

            // Helper: Format Meni:Detik
            const formatTimeStr = (totalSeconds) => {
                const m = Math.floor(totalSeconds / 60);
                const s = Math.round(totalSeconds % 60);
                return `${m}:${s < 10 ? '0' + s : s}`;
            };

            const startAssessment = () => { step.value = 1; window.scrollTo(0,0); };

            const submitAssessment = async () => {
                // Validasi Input
                if (!form.timeMin) { alert("Mohon isi waktu tes lari Anda."); return; }
                
                // Masuk ke Loading Animation
                step.value = 99;
                window.scrollTo(0,0);

                // Simulasi Proses Analisis
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.floor(Math.random() * 10) + 5;
                    if (progress > 100) progress = 100;
                    loadingPercentage.value = progress;

                    if (progress < 30) loadingText.value = "Calculating VDOT...";
                    else if (progress < 60) loadingText.value = "Analyzing Biological Gap...";
                    else if (progress < 90) loadingText.value = "Generating Ladder Progression...";
                    else loadingText.value = "Finalizing Blueprint...";

                    if (progress === 100) {
                        clearInterval(interval);
                        generateProgram(); // Jalankan Logic
                        // saveToDatabase();  // Disabled for now
                        setTimeout(() => { step.value = 5; }, 500);
                    }
                }, 200);
            };

            // --- LOGIC UTAMA: GENERATE PROGRAM ---
            const generateProgram = () => {
                // 1. Hitung Pace Saat Ini (min/km)
                const totalSeconds = (parseInt(form.timeMin) * 60) + (parseInt(form.timeSec) || 0);
                const distanceKm = parseFloat(form.latestDistance);
                const paceSecondsPerKm = totalSeconds / distanceKm;

                formattedCurrentTime.value = `${form.timeMin}:${form.timeSec || '00'}`;

                // 2. Tentukan Target Ladder (Realistic Goal)
                // Logika: Kurangi ~2% dari waktu saat ini sebagai target mikro
                const ladderSeconds = totalSeconds * 0.98; 
                const lMin = Math.floor(ladderSeconds / 60);
                const lSec = Math.round(ladderSeconds % 60);
                ladderTarget.value = `${lMin}:${lSec < 10 ? '0' + lSec : lSec} (Sub ${lMin + 1})`;

                // 3. Kalkulasi Zona Latihan (Approximation based on Jack Daniels logic)
                // Easy: Current Pace + 60-90s
                // Tempo: Current Pace - 10-20s (from 10k pace) roughly
                // Interval: Current Pace - 30-45s
                
                const easyPaceSec = paceSecondsPerKm + 75; // + 1 min 15s pelan
                const tempoPaceSec = paceSecondsPerKm - 10; // sedikit lebih cepat dari 10k pace
                const intervalPaceSec = paceSecondsPerKm - 35; // jauh lebih cepat

                paces.easy = formatTimeStr(easyPaceSec) + " /km";
                paces.tempo = formatTimeStr(tempoPaceSec) + " /km";
                paces.interval = formatTimeStr(intervalPaceSec) + " /km";

                // 4. Susun Jadwal Mingguan (Based on "Designing by Recovery")
                const vol = form.weeklyVolume || 20;
                const longRunDist = Math.round(vol * 0.3); // 30% weekly vol
                const easyDist = Math.round(vol * 0.15);
                
                programSchedule.value = [
                    { day: 'Senin', title: 'Recovery Run / Rest', type: 'rest', desc: 'Lari sangat ringan atau istirahat total. Fokus pemulihan.', pace: '-' },
                    { day: 'Selasa', title: 'Quality: Interval', type: 'hard', desc: 'Pemanasan 2km, Inti: 5 x 400m, Pendinginan 2km.', pace: paces.interval },
                    { day: 'Rabu', title: 'Easy Run (Foundation)', type: 'easy', desc: `Jaga detak jantung rendah. Jarak: ${easyDist}KM`, pace: paces.easy },
                    { day: 'Kamis', title: 'Tempo Run', type: 'hard', desc: 'Lari "Uncomfortably Hard". 20 menit konstan.', pace: paces.tempo },
                    { day: 'Jumat', title: 'Rest / Strength', type: 'rest', desc: 'Latihan penguatan otot kaki (Lunges, Squat) tanpa beban berat.', pace: '-' },
                    { day: 'Sabtu', title: 'Easy Run', type: 'easy', desc: 'Lari santai sore untuk melemaskan kaki.', pace: paces.easy },
                    { day: 'Minggu', title: 'Long Run', type: 'long', desc: `Lari durasi panjang. Jarak: ${longRunDist}KM. Mental endurance.`, pace: paces.easy + ' + 15s' },
                ];
            };

            const resetForm = () => {
                step.value = 0;
                form.timeMin = '';
                form.timeSec = '';
            };

            return {
                step, form, loadingPercentage, loadingText,
                startAssessment, submitAssessment, resetForm,
                paces, programSchedule, formattedCurrentTime, ladderTarget
            }
        }
    }).mount('#app')
</script>
@endpush