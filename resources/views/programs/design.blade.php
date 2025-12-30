@extends('layouts.pacerhub')

@section('title', 'Realistic Values - Design Program')

@push('styles')
    <script>
        // Extending existing Tailwind config from pacerhub layout
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
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
        /* Scoped styles for this page */
        .design-program-wrapper { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            position: relative;
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
        
        .fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
    </style>
@endpush

@section('content')
<div id="program-design-app" class="design-program-wrapper relative w-full h-full px-4 pb-4 pt-28 rounded-xl">
        
    <div class="absolute inset-0 z-0 pointer-events-none opacity-20" 
            style="background-image: linear-gradient(#334155 1px, transparent 1px), linear-gradient(to right, #334155 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <!-- Replaced Navbar with internal header -->
    <div class="relative z-10 w-full mb-8 flex justify-between items-center border-b border-slate-700 pb-4">
        <!--<div class="flex items-center cursor-pointer group" @click="resetForm">
            <div class="w-2 h-8 bg-cyan-400 mr-3 shadow-neon-cyan group-hover:h-10 transition-all duration-300"></div>
            <span class="font-bold text-xl tracking-tighter text-white">
                REALISTIC<span class="text-cyan-400">VALUES</span>
            </span>
        </div>-->
        <div class="hidden md:block font-mono text-xs text-cyan-400 border border-cyan-900 bg-cyan-900/20 px-3 py-1 rounded mx-auto">
            SYSTEM: ONLINE // VDOT: ACTIVE
        </div>
    </div>

    <!-- Register Modal -->
    <transition name="fade">
        <div v-if="showRegisterModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="!isSubmitting && (showRegisterModal = false)"></div>
            <div class="relative bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md w-full shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-4">Daftar Challenge</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-mono text-cyan-400 mb-1">Nama Lengkap</label>
                        <input v-model="registerForm.name" type="text" class="w-full p-3 rounded bg-slate-800 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-cyan-400 mb-1">Email</label>
                        <input v-model="registerForm.email" type="email" class="w-full p-3 rounded bg-slate-800 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-cyan-400 mb-1">No WhatsApp</label>
                        <input v-model="registerForm.whatsapp" type="tel" class="w-full p-3 rounded bg-slate-800 border border-slate-600 text-white focus:border-cyan-400 outline-none" placeholder="08...">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-cyan-400 mb-1">Password</label>
                        <input v-model="registerForm.password" type="password" class="w-full p-3 rounded bg-slate-800 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-mono text-cyan-400 mb-1">Avatar / Foto Profil</label>
                        <input type="file" @change="handleFileUpload" accept="image/*" class="w-full p-2 rounded bg-slate-800 border border-slate-600 text-white text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-cyan-500 file:text-slate-900 hover:file:bg-cyan-400">
                    </div>

                    <div>
                        <label class="block text-xs font-mono text-cyan-400 mb-1">Link Profil Strava (Opsional)</label>
                        <input v-model="registerForm.strava_url" type="url" placeholder="https://www.strava.com/athletes/..." class="w-full p-3 rounded bg-slate-800 border border-slate-600 text-white focus:border-cyan-400 outline-none">
                    </div>

                    <div class="flex items-start gap-2 pt-2">
                        <input v-model="registerForm.terms_agreed" type="checkbox" id="terms" class="mt-1">
                        <label for="terms" class="text-xs text-slate-400">
                            Saya menyetujui <a href="#" class="text-cyan-400 hover:underline">Syarat & Ketentuan</a> yang berlaku dalam 40 Days Challenge ini.
                        </label>
                    </div>

                    <button @click="submitRegister" :disabled="isSubmitting" class="animate-breath px-6 py-3 rounded-xl bg-neon text-white font-black hover:bg-white hover:text-slate-900 transition-all shadow-neon-cyan w-full disabled:opacity-50 disabled:cursor-not-allowed">
                        @{{ isSubmitting ? 'Memproses...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
    <transition name="fade">
        <div v-if="selectedWorkout" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="selectedWorkout = null"></div>
            <div class="relative bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md w-full shadow-2xl transform transition-all">
                <button @click="selectedWorkout = null" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                
                <div class="mb-4">
                    <span class="text-xs font-mono text-cyan-400 uppercase">@{{ selectedWorkout.day }} • @{{ selectedWorkout.phase }}</span>
                    <h3 class="text-2xl font-bold text-white mt-1">@{{ selectedWorkout.title }}</h3>
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-800/50 p-4 rounded border border-slate-700">
                        <span class="block text-xs text-slate-500 uppercase mb-1">Deskripsi Latihan</span>
                        <p class="text-slate-300">@{{ selectedWorkout.desc }}</p>
                    </div>

                    <div v-if="selectedWorkout.pace && selectedWorkout.pace !== '-'" class="flex items-center justify-between bg-slate-800/50 p-4 rounded border border-slate-700">
                        <span class="text-sm text-slate-400">Target Pace</span>
                        <span class="text-lg font-mono font-bold text-cyan-400">@{{ selectedWorkout.pace }}</span>
                    </div>

                    <div class="flex gap-2">
                        <span v-if="selectedWorkout.type === 'rest'" class="flex-1 py-2 text-center text-xs font-bold bg-slate-800 text-slate-400 rounded">RECOVERY</span>
                        <span v-else-if="selectedWorkout.type === 'easy'" class="flex-1 py-2 text-center text-xs font-bold bg-green-900/30 text-green-400 border border-green-900 rounded">AEROBIC</span>
                        <span v-else-if="selectedWorkout.type === 'hard'" class="flex-1 py-2 text-center text-xs font-bold bg-purple-900/30 text-purple-400 border border-purple-900 rounded">QUALITY</span>
                        <span v-else-if="selectedWorkout.type === 'long'" class="flex-1 py-2 text-center text-xs font-bold bg-yellow-900/30 text-yellow-400 border border-yellow-900 rounded">ENDURANCE</span>
                    </div>
                </div>
            </div>
        </div>
    </transition>



    <!-- OTP Modal -->
    <transition name="fade">
        <div v-if="showOtpModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
            <div class="relative bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-sm w-full shadow-2xl text-center">
                <h3 class="text-2xl font-bold text-white mb-2">Verifikasi OTP</h3>
                <p class="text-slate-400 text-sm mb-6">Masukkan 6 digit kode yang dikirim ke WhatsApp Anda.</p>
                
                <input v-model="otpCode" maxlength="6" class="w-full p-4 text-center text-2xl tracking-widest font-mono bg-slate-800 border border-slate-600 rounded-lg text-white mb-6 focus:border-cyan-400 outline-none">
                
                <button @click="submitOtp" :disabled="isSubmitting" class="w-full py-3 bg-cyan-500 text-slate-900 font-bold rounded-lg hover:bg-cyan-400 transition-colors disabled:opacity-50">
                    @{{ isSubmitting ? 'Verifikasi...' : 'Verifikasi & Join' }}
                </button>
            </div>
        </div>
    </transition>

    <main class="relative z-10 flex-grow flex flex-col justify-center items-center w-full">
        
        <transition name="fade" mode="out-in">
            
            <div v-if="step === 0" key="hero" class="text-center max-w-4xl py-10">
                <div class="inline-block mb-4 px-4 py-1 rounded-full bg-purple-900/30 border border-purple-500/50 text-purple-300 text-xs font-mono tracking-widest uppercase animate-pulse">
                    Algoritma Kepelatihan v2.0
                </div>
                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white via-cyan-100 to-cyan-400">
                    40 DAYS RUNNING <br> CHALLENGE 
                </h1>
                <h3 class="text-2xl md:text-3xl font-extrabold tracking-tight mb-6 text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-red-700 to-red-900">
                    DESIGNED BY 
                    <a href="{{ url('/runner/coach-budi') }}" target="_blank" class="text-red-300 hover:text-red-100">
                        RAKA WAHYU PRAYOGA
                    </a>
                </h3>


                <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Kami tidak menjual mimpi. Kami menghitung <span class="text-cyan-400 font-bold">Gap</span>, menganalisis <span class="text-cyan-400 font-bold">Realita</span>, dan membangun <span class="text-purple-400 font-bold">Tangga Progresi</span> (Ladder) khusus untuk fisiologis Anda.
                </p>
                <button @click="startAssessment" class="group relative px-8 py-4 bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-bold text-lg rounded-sm transition-all shadow-neon-cyan hover:scale-105">
                    <span class="relative z-10">AUDIT PERFORMA SEKARANG</span>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                </button>
                
                <div v-if="hasSavedProgram" class="mt-6">
                    <button @click="loadSavedProgram" class="text-cyan-400 hover:text-white underline text-sm flex items-center justify-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Lanjutkan Program Terakhir
                    </button>
                </div>
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
                                    <option value="3">3 Kilometer</option>
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
                            <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase">Target Race Distance</label>
                            <select v-model="form.targetDistance" class="w-full p-4 rounded bg-slate-800/50 border border-slate-600 text-white focus:border-cyan-400 outline-none appearance-none">
                                <option value="5k">5K (Program 8 Minggu)</option>
                                <option value="10k">10K (Program 10 Minggu)</option>
                                <option value="hm">Half Marathon (Program 12 Minggu)</option>
                                <option value="fm">Full Marathon (Program 16 Minggu)</option>
                            </select>
                        </div>

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

                <div class="relative">
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
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-slate-400 font-bold">Recovery</span>
                                            <span class="text-white font-mono">@{{ paces.recovery }}</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-slate-400 h-1 rounded" style="width: 30%"></div></div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-green-400 font-bold">Easy</span>
                                            <span class="text-white font-mono">@{{ paces.easy }}</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-green-500 h-1 rounded" style="width: 50%"></div></div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-blue-400 font-bold">Tempo (M)</span>
                                            <span class="text-white font-mono">@{{ paces.tempo }}</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-blue-500 h-1 rounded" style="width: 65%"></div></div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-yellow-400 font-bold">Threshold (T)</span>
                                            <span class="text-white font-mono">@{{ paces.threshold }}</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-yellow-500 h-1 rounded" style="width: 80%"></div></div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-purple-400 font-bold">Interval (I)</span>
                                            <span class="text-white font-mono">@{{ paces.interval }}</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-purple-500 h-1 rounded" style="width: 90%"></div></div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-red-400 font-bold">Repetition (R)</span>
                                            <span class="text-white font-mono">@{{ paces.repetition }}</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-1 rounded"><div class="bg-red-500 h-1 rounded" style="width: 100%"></div></div>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-3 text-center italic">
                                    *Pace dihitung berdasarkan estimasi VDOT dari hasil tes lari Anda.
                                </p>
                            </div>
                        </div>
                    
                        <div class="lg:col-span-2 relative">
                            
                            <div :class="CHALLENGE_MODE ? 'blur-sm select-none pointer-events-none' : ''">
                                <!-- View Toggle -->
                                <div class="flex justify-end mb-4 gap-2">
                                    <button @click="viewMode = 'calendar'" 
                                        class="px-3 py-1 text-xs rounded border transition-colors flex items-center gap-2"
                                        :class="viewMode === 'calendar' ? 'bg-cyan-900 text-cyan-300 border-cyan-700' : 'text-slate-400 border-slate-700 hover:text-white'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Calendar
                                    </button>
                                    <button @click="viewMode = 'list'" 
                                        class="px-3 py-1 text-xs rounded border transition-colors flex items-center gap-2"
                                        :class="viewMode === 'list' ? 'bg-cyan-900 text-cyan-300 border-cyan-700' : 'text-slate-400 border-slate-700 hover:text-white'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                        List
                                    </button>
                                </div>

                                <!-- Tampilkan Jadwal Sebagai Kalender -->
                                <div v-if="viewMode === 'calendar'" class="glass rounded-xl overflow-hidden border border-slate-700 mb-6 p-4">
                                    <h3 class="font-bold text-white text-lg mb-4 flex items-center gap-2">
                                        <span class="text-cyan-400">🗓</span> TRAINING CALENDAR
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-7 gap-2 mb-2 text-center text-xs font-mono text-slate-500 uppercase hidden md:grid">
                                        <div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
                                    </div>

                                    <div v-for="(week, wIndex) in programSchedule" :key="wIndex" class="mb-6">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-bold bg-slate-800 text-white px-2 py-1 rounded">W@{{ week.weekNum }}</span>
                                            <span class="text-xs text-cyan-400">@{{ week.phase }} (@{{ week.totalVolume }}KM)</span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-7 gap-2">
                                            <div v-for="(day, dIndex) in week.days" :key="dIndex" 
                                                @click="openWorkoutModal(day, week.phase)"
                                                class="p-2 rounded border transition-all h-full min-h-[80px] flex flex-col justify-between cursor-pointer"
                                                :class="{
                                                    'bg-slate-800/30 border-slate-700 hover:bg-slate-800': day.type === 'rest',
                                                    'bg-green-900/10 border-green-900/30 hover:bg-green-900/20': day.type === 'easy',
                                                    'bg-purple-900/10 border-purple-900/30 hover:bg-purple-900/20': day.type === 'hard',
                                                    'bg-yellow-900/10 border-yellow-900/30 hover:bg-yellow-900/20': day.type === 'long'
                                                }">
                                                
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="text-[10px] text-slate-500 font-mono md:hidden">@{{ day.day }}</span>
                                                    <span class="text-[10px] text-slate-500 font-mono hidden md:inline">@{{ dIndex + 1 }}</span>
                                                    
                                                    <span v-if="day.type === 'rest'" class="text-slate-600">☾</span>
                                                    <span v-else-if="day.type === 'easy'" class="text-green-500">♥</span>
                                                    <span v-else-if="day.type === 'hard'" class="text-purple-500">⚡</span>
                                                    <span v-else-if="day.type === 'long'" class="text-yellow-500">∞</span>
                                                </div>

                                                <div>
                                                    <h4 class="text-xs font-bold text-white leading-tight mb-1">@{{ day.title }}</h4>
                                                    <p v-if="day.pace && day.pace !== '-'" class="text-[10px] font-mono text-cyan-300">@{{ day.pace }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tampilkan Jadwal Sebagai List (Legacy View) -->
                                <div v-else class="space-y-6">
                                    <div v-for="(week, wIndex) in programSchedule" :key="wIndex" class="glass rounded-xl overflow-hidden border border-slate-700">
                                        <div class="bg-slate-800/50 p-4 border-b border-slate-700 flex justify-between items-center">
                                            <div>
                                                <h3 class="font-bold text-white text-lg">MINGGU @{{ week.weekNum }}</h3>
                                                <span class="text-xs text-slate-400">Total: @{{ week.totalVolume }}KM | Fokus: @{{ week.focus }}</span>
                                            </div>
                                            <span class="text-xs bg-cyan-900 text-cyan-300 px-3 py-1 rounded-full font-bold">@{{ week.phase }}</span>
                                        </div>
                                        
                                        <div class="divide-y divide-slate-700">
                                            <div v-for="(day, dIndex) in week.days" :key="dIndex" 
                                                @click="openWorkoutModal(day, week.phase)"
                                                class="p-4 hover:bg-slate-800/50 transition-colors group cursor-pointer">
                                                <div class="flex items-start">
                                                    <div class="w-16 flex-shrink-0 pt-1">
                                                        <span class="text-xs font-mono text-slate-500 uppercase">@{{ day.day }}</span>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <h4 class="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">@{{ day.title }}</h4>
                                                        <p class="text-sm text-slate-400 mt-1">@{{ day.desc }}</p>
                                                        <div v-if="day.pace && day.pace !== '-'" class="mt-2 inline-block px-2 py-1 bg-slate-900 rounded border border-slate-700 text-xs font-mono text-cyan-300">
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
                                </div>
                                
                                <div class="mt-6 flex justify-end gap-4">
                                    <button @click="resetForm" class="text-slate-400 hover:text-white text-sm underline">Reset Data</button>
                                    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded border border-slate-600 text-sm">
                                        Simpan PDF
                                    </button>
                                    <a href="https://wa.me/6285524807623?text=Halo%20Coach,%20saya%20sudah%20generate%20program." target="_blank" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded shadow-lg text-sm flex items-center">
                                        Konsultasi Coach
                                    </a>
                                </div>
                            </div>

                            <!-- Join Overlay -->
                            <div v-if="CHALLENGE_MODE" class="absolute inset-0 z-10 flex items-top justify-center">
                                <div class="fixed top-40 left-50 -translate-x-50 w-full z-50 bg-slate-900/90 backdrop-blur-xl border border-slate-700 h-[250px] rounded-2xl p-8 text-center max-w-md w-full mx-4 shadow-2xl">
                                    <h3 class="text-white font-black text-2xl mb-2">Join 40 Days Challenge</h3>
                                    <p class="text-slate-400 text-sm mb-6">Blueprint latihan disembunyikan. Gabung untuk mengakses kalender latihan.</p>

                                    <button @click="joinChallenge" class="animate-breath px-6 py-3 rounded-xl bg-neon text-white font-black hover:bg-white hover:text-slate-900 transition-all shadow-neon-cyan">
                                        JOIN CHALLENGE?
                                    </button><br/><br/>
                                    <button @click="resetForm" class="text-slate-400 hover:text-white text-sm underline">Reset Data</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

        </transition>
    </main>
</div>
@endsection

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
<script>
    // Using Vue from global scope (loaded in pacerhub layout)
    const { createApp, ref, reactive, computed, onMounted } = Vue;
    var CHALLENGE_MODE = {{ isset($challengeMode) && $challengeMode ? 'true' : 'false' }};
    var CHALLENGE_PROGRAM_ID = {{ isset($challengeProgramId) ? (int)$challengeProgramId : 'null' }};
    var IS_AUTHENTICATED = {{ auth()->check() ? 'true' : 'false' }};

    const firebaseConfig = {
        apiKey: "AIzaSyBVAEiYBFSt2ZYMIxbl7q-5kZvLH_dRLKU",
        authDomain: "ruanglari-8d041.firebaseapp.com",
        databaseURL: "https://ruanglari-8d041-default-rtdb.asia-southeast1.firebasedatabase.app",
        projectId: "ruanglari-8d041",
        storageBucket: "ruanglari-8d041.firebasestorage.app",
        messagingSenderId: "887605981752",
        appId: "1:887605981752:web:42d420fcddd861ba21eccd",
        measurementId: "G-9TXDKSXRR8"
    };

    let db;
    try {
        const app = firebase.initializeApp(firebaseConfig);
        db = firebase.firestore();
        console.log("Firebase initialized in Design Program");
    } catch(e) {
        console.error("Firebase init failed:", e);
    }

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
            const paces = reactive({ 
                recovery: '', 
                easy: '', 
                tempo: '', 
                threshold: '', 
                interval: '', 
                repetition: '' 
            });
            const programSchedule = ref([]); // Now stores array of weeks
            const formattedCurrentTime = ref('');
            const ladderTarget = ref('');
            
            // New State for View Mode & Modal
            const viewMode = ref('calendar'); // 'list' or 'calendar'
            const selectedWorkout = ref(null);
            const hasSavedProgram = ref(false);

            // Helper: Format Meni:Detik
            const formatTimeStr = (totalSeconds) => {
                const m = Math.floor(totalSeconds / 60);
                const s = Math.round(totalSeconds % 60);
                return `${m}:${s < 10 ? '0' + s : s}`;
            };
            
            onMounted(() => {
                // Check local storage
                const saved = localStorage.getItem('myRunningProgram');
                if (saved) {
                    hasSavedProgram.value = true;
                }
            });

            const loadSavedProgram = () => {
                const saved = localStorage.getItem('myRunningProgram');
                if (saved) {
                    const data = JSON.parse(saved);
                    // Restore state
                    Object.assign(form, data.form);
                    Object.assign(paces, data.paces);
                    programSchedule.value = data.schedule;
                    ladderTarget.value = data.target;
                    formattedCurrentTime.value = `${data.form.timeMin}:${data.form.timeSec || '00'}`;
                    
                    // Go to result
                    step.value = 5;
                }
            };
            
            const openWorkoutModal = (day, weekPhase) => {
                if (day.type === 'rest') return; // Optional: don't open for rest days
                selectedWorkout.value = { ...day, phase: weekPhase };
            };

            const startAssessment = () => { step.value = 1; window.scrollTo(0,0); };

            const saveToDatabase = async () => {
                if (!db) return;
                try {
                    console.log("Saving to Firebase...");
                    await db.collection("program_assessments").add({
                        ...form,
                        generatedPaces: paces,
                        ladderTarget: ladderTarget.value,
                        createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                        status: 'new' // Status for coach dashboard
                    });
                    console.log("Data saved successfully!");
                } catch (e) {
                    console.error("Error adding document: ", e);
                }
            };

            const submitAssessment = async () => {
                // Validasi Input
                if (step.value === 1) {
                    if (!form.name || !form.age || !form.gender) { alert("Lengkapi profil atlet (nama, usia, gender)."); return; }
                }
                if (step.value === 2) {
                    if (!form.childhood) { alert("Pilih latar masa kecil untuk audit historis."); return; }
                }
                if (step.value === 3) {
                    if (!form.latestDistance || !form.timeMin || (form.timeSec === '' || form.timeSec === null)) { alert("Isi jarak tes dan waktu (menit & detik)."); return; }
                    if (form.weeklyVolume === '' || form.weeklyVolume === null) { alert("Isi volume lari mingguan (KM)."); return; }
                }
                
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
                        saveToDatabase();  // Save to Firebase
                        // Persist to backend (challenge mode)
                        if (CHALLENGE_MODE && IS_AUTHENTICATED) {
                            fetch('{{ url("/challenge/40-days-challenge/assessment") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    ...form
                                })
                            }).catch(e => console.error("Persist error:", e));
                        }
                        setTimeout(() => { step.value = 5; }, 500);
                    }
                }, 200);
            };

            // --- LOGIC UTAMA: GENERATE PROGRAM (DINAMIS 8-16 MINGGU) ---
            const generateProgram = () => {
                // 1. Hitung Pace Saat Ini (min/km)
                const totalSeconds = (parseInt(form.timeMin) * 60) + (parseInt(form.timeSec) || 0);
                const distanceKm = parseFloat(form.latestDistance);
                const paceSecondsPerKm = totalSeconds / distanceKm;

                formattedCurrentTime.value = `${form.timeMin}:${form.timeSec || '00'}`;

                // 2. Tentukan Target Ladder
                const ladderSeconds = totalSeconds * 0.98; 
                const lMin = Math.floor(ladderSeconds / 60);
                const lSec = Math.round(ladderSeconds % 60);
                ladderTarget.value = `${lMin}:${lSec < 10 ? '0' + lSec : lSec} (Sub ${lMin + 1})`;

                // 3. Kalkulasi Zona Latihan (Approximation based on VDOT / Riegel Formula)
                // Ref: Daniels' Running Formula
                
                // Base: 10K Race Pace in sec/km
                let racePaceSec = paceSecondsPerKm; 
                
                // Adjust base pace if input distance is NOT 10k (normalize to 10k equivalent for calculation base)
                if (form.latestDistance == 5) racePaceSec = paceSecondsPerKm * 1.06; // 5k pace is faster, slow down for 10k base
                if (form.latestDistance == 21.1) racePaceSec = paceSecondsPerKm * 0.95; // HM pace is slower, speed up for 10k base

                // VDOT Multipliers (Approximation)
                const recoverySec = racePaceSec * 1.35;    // ~135% of 10k pace (Very Slow)
                const easySec = racePaceSec * 1.25;        // ~125% of 10k pace
                const tempoSec = racePaceSec * 1.10;       // ~110% (Marathon Pace range)
                const thresholdSec = racePaceSec * 1.00;   // ~100% (Threshold / 10k-15k pace) - Corrected logic: Threshold is roughly 15-20s slower than 5k, or close to 10k pace for amateurs
                const intervalSec = racePaceSec * 0.92;    // ~92% (5k pace or faster)
                const repetitionSec = racePaceSec * 0.88;  // ~88% (Mile pace)

                paces.recovery = formatTimeStr(recoverySec) + " /km";
                paces.easy = formatTimeStr(easySec) + " /km";
                paces.tempo = formatTimeStr(tempoSec) + " /km";
                paces.threshold = formatTimeStr(thresholdSec) + " /km";
                paces.interval = formatTimeStr(intervalSec) + " /km";
                paces.repetition = formatTimeStr(repetitionSec) + " /km";

                // 4. Susun Jadwal Dinamis (8-16 Minggu)
                const baseVol = form.weeklyVolume || 20;
                let durationWeeks = 8;
                let programType = "General Fitness";
                
                // Set durasi berdasarkan target
                switch(form.targetDistance) {
                    case '5k': durationWeeks = 8; programType = "5K Plan"; break;
                    case '10k': durationWeeks = 10; programType = "10K Plan"; break;
                    case 'hm': durationWeeks = 12; programType = "Half Marathon Plan"; break;
                    case 'fm': durationWeeks = 16; programType = "Marathon Plan"; break;
                    default: durationWeeks = 8;
                }

                const generatedWeeks = [];
                let currentVol = baseVol;

                for (let w = 1; w <= durationWeeks; w++) {
                    let phase = '';
                    let focus = '';
                    let weekVol = currentVol;

                    // Logika Fase & Volume (Simplified Periodization)
                    const isRecoveryWeek = w % 4 === 0; // Tiap 4 minggu recovery
                    const isTaper = w > durationWeeks - 2; // 2 minggu terakhir taper

                    if (isTaper) {
                        phase = 'Tapering';
                        focus = 'Recovery & Freshness';
                        weekVol = w === durationWeeks ? baseVol * 0.5 : baseVol * 0.7; // Drop volume drastically
                    } else if (isRecoveryWeek) {
                        phase = 'Recovery / Cut-back';
                        focus = 'Absorb Training';
                        weekVol = currentVol * 0.7; // Turun 30%
                    } else {
                        // Progressive Overload
                        if (w <= durationWeeks / 3) {
                            phase = 'Base Building';
                            focus = 'Endurance Foundation';
                            if (w > 1) currentVol = Math.round(currentVol * 1.1); // Naik 10%
                            weekVol = currentVol;
                        } else if (w <= (durationWeeks / 3) * 2) {
                            phase = 'Strength & Threshold';
                            focus = 'Lactate Threshold';
                            if (w > 1 && !isRecoveryWeek) currentVol = Math.round(currentVol * 1.05); // Naik 5%
                            weekVol = currentVol;
                        } else {
                            phase = 'Peak / Speed';
                            focus = 'Race Specific';
                            weekVol = currentVol; // Stabilize volume
                        }
                    }

                    // Tentukan Menu Harian
                    const longRunDist = Math.round(weekVol * 0.3);
                    const easyDist = Math.round(weekVol * 0.15);
                    
                    let keyWorkout = {};
                    
                    // Variasi Key Workout
                    if (phase.includes('Base')) {
                        keyWorkout = { title: 'Fartlek / Strides', desc: 'Lari santai diselingi lari cepat pendek (Repetition).', pace: paces.repetition };
                    } else if (phase.includes('Strength')) {
                        keyWorkout = { title: 'Threshold Run', desc: 'Lari di ambang laktat (Comfortably Hard).', pace: paces.threshold };
                    } else if (phase.includes('Peak')) {
                        keyWorkout = { title: 'Interval VO2Max', desc: '4-6 x 800m atau 1km repetitions.', pace: paces.interval };
                    } else { // Taper
                        keyWorkout = { title: 'Dress Rehearsal', desc: 'Lari pendek dengan pace target lomba (Tempo).', pace: paces.tempo };
                    }

                    generatedWeeks.push({
                        weekNum: w,
                        phase: phase,
                        focus: focus,
                        totalVolume: Math.round(weekVol),
                        days: [
                            { day: 'Senin', title: 'Rest', type: 'rest', desc: 'Istirahat total.', pace: '-' },
                            { day: 'Selasa', title: keyWorkout.title, type: 'hard', desc: keyWorkout.desc, pace: keyWorkout.pace },
                            { day: 'Rabu', title: 'Easy Run', type: 'easy', desc: `Jarak: ${easyDist}KM`, pace: paces.easy },
                            { day: 'Kamis', title: 'Tempo Run', type: 'hard', desc: 'Steady state run (Marathon Pace).', pace: paces.tempo },
                            { day: 'Jumat', title: 'Recovery Run', type: 'rest', desc: 'Lari sangat pelan untuk melancarkan darah.', pace: paces.recovery },
                            { day: 'Sabtu', title: 'Shakeout', type: 'easy', desc: 'Lari ringan 30 menit + Strides.', pace: paces.easy },
                            { day: 'Minggu', title: 'Long Run', type: 'long', desc: `Jarak: ${longRunDist}KM.`, pace: paces.easy },
                        ]
                    });
                }

                programSchedule.value = generatedWeeks;
                
                // Simpan ke LocalStorage
                localStorage.setItem('myRunningProgram', JSON.stringify({
                    timestamp: new Date().getTime(),
                    form: form,
                    schedule: generatedWeeks,
                    paces: paces,
                    target: ladderTarget.value
                }));
            };

            const resetForm = () => {
                step.value = 0;
                form.timeMin = '';
                form.timeSec = '';
            };
            
            const showRegisterModal = ref(false);
            const showOtpModal = ref(false);
            const isSubmitting = ref(false);
            const otpCode = ref('');
            const userId = ref(null);
            
            const registerForm = reactive({
                name: '',
                email: '',
                whatsapp: '',
                password: '',
                gender: '',
                pb_5km: '',
                strava_url: '',
                terms_agreed: false,
                avatar: null
            });

            const handleFileUpload = (event) => {
                const file = event.target.files[0];
                if (file) {
                    registerForm.avatar = file;
                }
            };

            const joinChallenge = async () => {
                const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
                if (isAuthenticated) {
                    // Existing logic for authenticated users
                    if (!CHALLENGE_PROGRAM_ID) {
                        alert('Program challenge tidak ditemukan.');
                        return;
                    }
                    try {
                        const resp = await fetch('{{ route("runner.programs.enroll-free", ["program" => 0]) }}'.replace('/0/', `/${CHALLENGE_PROGRAM_ID}/`), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });
                        if (resp.ok) {
                            window.location.href = '{{ route("runner.calendar") }}';
                        } else {
                            const j = await resp.json();
                            alert(j.message || 'Gagal join challenge.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan.');
                    }
                } else {
                    // Unauthenticated: Show Register Modal
                    // Prefill from form/localStorage
                    registerForm.name = form.name;
                    registerForm.gender = form.gender;
                    
                    // Try to estimate PB 5km from input
                    if (form.latestDistance == 5) {
                        registerForm.pb_5km = `${form.timeMin}:${form.timeSec}`;
                    } else if (paces.interval) {
                        // Rough estimate or leave blank
                        registerForm.pb_5km = ''; 
                    }
                    
                    showRegisterModal.value = true;
                }
            };

            const submitRegister = async () => {
                if(!registerForm.name || !registerForm.email || !registerForm.whatsapp || !registerForm.password || !registerForm.terms_agreed) {
                    alert('Mohon lengkapi semua data dan setujui syarat & ketentuan.');
                    return;
                }
                isSubmitting.value = true;
                try {
                    const formData = new FormData();
                    formData.append('name', registerForm.name);
                    formData.append('email', registerForm.email);
                    formData.append('whatsapp', registerForm.whatsapp);
                    formData.append('password', registerForm.password);
                    formData.append('gender', registerForm.gender);
                    formData.append('pb_5km', registerForm.pb_5km || '');
                    formData.append('strava_url', registerForm.strava_url || '');
                    formData.append('terms_agreed', registerForm.terms_agreed ? '1' : '0');
                    
                    if(registerForm.avatar) {
                        formData.append('avatar', registerForm.avatar);
                    }

                    const res = await fetch('{{ route("challenge.send-otp") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await res.json();
                    if(data.success) {
                        userId.value = data.user_id;
                        showRegisterModal.value = false;
                        showOtpModal.value = true;
                    } else {
                        alert(data.message || 'Gagal registrasi/kirim OTP');
                    }
                } catch(e) {
                    console.error(e);
                    alert('Terjadi kesalahan koneksi.');
                } finally {
                    isSubmitting.value = false;
                }
            };

            const submitOtp = async () => {
                if(!otpCode.value) return;
                isSubmitting.value = true;
                
                try {
                    const res = await fetch('{{ route("challenge.verify-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            otp: otpCode.value,
                            user_id: userId.value
                        })
                    });
                    const data = await res.json();
                    
                    if(data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'OTP Salah!');
                    }
                } catch(e) {
                    console.error(e);
                    alert('Terjadi kesalahan saat verifikasi.');
                } finally {
                    isSubmitting.value = false;
                }
            };

            return {
                step, form, loadingPercentage, loadingText,
                startAssessment, submitAssessment, resetForm,
                paces, programSchedule, formattedCurrentTime, ladderTarget,
                // Newly added functions and refs
                viewMode, selectedWorkout, hasSavedProgram,
                loadSavedProgram, openWorkoutModal, joinChallenge, CHALLENGE_MODE,
                // Registration
                showRegisterModal, showOtpModal, isSubmitting, otpCode, registerForm, submitRegister, submitOtp, userId, handleFileUpload
            }
        }
    }).mount('#program-design-app')
</script>
@endpush
