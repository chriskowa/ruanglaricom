<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- SEO Meta Tags -->
    <title>TrackMaster Pro | Tools Pelatih Lari & Analisa Pace - Ruang Lari</title>
    <meta name="description" content="Aplikasi web profesional untuk pelatih lari. Kelola sesi interval multi-atlet, analisa pace real-time, grafik performa, dan voice assistant mata elang. Gratis di Ruang Lari.">
    <meta name="keywords" content="trackmaster, pelatih lari, stopwatch lari, interval timer, running coach tools, analisa pace, grafik lari, ruang lari">
    <meta name="author" content="Ruang Lari">
    <meta name="robots" content="index, follow">

    <!-- Favicon default -->
    <link rel="icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">

    <!-- Versi PNG -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">

    <!-- Versi Apple Touch -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/green/apple-touch-icon.png') }}">

    <!-- Versi Android/Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/android-icon-192x192.png">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="TrackMaster Pro | Tools Pelatih Lari & Analisa Pace">
    <meta property="og:description" content="Stopwatch multi-atlet cerdas dengan analisa performa dan voice assistant. Tingkatkan kualitas latihan atlet Anda.">
    <meta property="og:image" content="{{ asset('images/logo-full.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="TrackMaster Pro | Tools Pelatih Lari">
    <meta property="twitter:description" content="Stopwatch multi-atlet cerdas dengan analisa performa dan voice assistant.">
    <meta property="twitter:image" content="{{ asset('images/logo-full.png') }}">
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
                extend: {
                    colors: {
                        neon: { green: '#10b981', blue: '#06b6d4', purple: '#8b5cf6', red: '#f43f5e', bg: '#0f172a', card: '#1e293b' }
                    },
                    animation: { 'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite' }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0f172a; color: #e2e8f0; touch-action: manipulation; }
        [v-cloak] { display: none; }
        
        /* Glassmorphism & Neon Effects */
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); }
        .neon-input { background: #0f172a; border: 1px solid #334155; transition: 0.3s; }
        .neon-input:focus { border-color: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2); outline: none; }
        
        /* Balloon States */
        .b-active { border: 2px solid #10b981; box-shadow: 0 0 15px rgba(16, 185, 129, 0.15); color: #10b981; }
        .b-resting { border: 2px solid #eab308; background: rgba(234, 179, 8, 0.05); color: #eab308; }
        .b-finished { border: 2px solid #64748b; background: #0f172a; color: #64748b; opacity: 0.8; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col overflow-hidden">

<div id="app" v-cloak class="flex-grow flex flex-col h-screen max-w-5xl mx-auto w-full relative">

    <section v-if="view === 'setup'" class="flex-grow flex flex-col justify-center p-6 overflow-y-auto">
        <div class="glass p-8 rounded-3xl shadow-2xl w-full max-w-lg mx-auto border-t-4 border-neon-green">
            <h1 class="text-3xl font-black text-white mb-1 tracking-tighter">TRACK<span class="text-neon-green">MASTER</span></h1>
            <p class="text-xs text-neon-blue font-mono tracking-widest mb-6">PRO COACHING TOOLS v2.1</p>

            <div class="space-y-5">
                <!-- Context Toggles -->
                <div class="flex gap-4">
                    <div class="flex-1 bg-slate-800 p-1 rounded-xl flex text-[10px] font-bold">
                        <button @click="program.weather = 'sunny'" :class="program.weather === 'sunny' ? 'bg-yellow-500 text-black' : 'text-slate-500'" class="flex-1 rounded-lg transition py-2"><i class="fa-solid fa-sun text-lg"></i></button>
                        <button @click="program.weather = 'cloudy'" :class="program.weather === 'cloudy' ? 'bg-slate-500 text-white' : 'text-slate-500'" class="flex-1 rounded-lg transition py-2"><i class="fa-solid fa-cloud text-lg"></i></button>
                        <button @click="program.weather = 'rainy'" :class="program.weather === 'rainy' ? 'bg-blue-500 text-white' : 'text-slate-500'" class="flex-1 rounded-lg transition py-2"><i class="fa-solid fa-cloud-showers-heavy text-lg"></i></button>
                    </div>
                    <div class="flex-1 bg-slate-800 p-1 rounded-xl flex text-[10px] font-bold">
                        <button @click="program.workoutType = 'race'" :class="program.workoutType === 'race' ? 'bg-neon-red text-white' : 'text-slate-500'" class="flex-1 rounded-lg transition py-2">INTERVAL</button>
                        <button @click="program.workoutType = 'easy'" :class="program.workoutType === 'easy' ? 'bg-neon-green text-black' : 'text-slate-500'" class="flex-1 rounded-lg transition py-2">EASY</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Repetisi</label>
                        <input v-model.number="program.reps" type="number" class="neon-input w-full rounded-xl p-3 text-center font-mono text-lg text-white" placeholder="10">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Jarak (m)</label>
                        <input v-model.number="program.distance" type="number" class="neon-input w-full rounded-xl p-3 text-center font-mono text-lg text-white" placeholder="400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Target (detik)</label>
                        <input v-model.number="program.targetTime" type="number" class="neon-input w-full rounded-xl p-3 text-center font-mono text-lg text-white" placeholder="80">
                        <div class="text-center text-[10px] text-neon-green mt-1 font-mono">@{{ calculatePace(program.targetTime, program.distance) }} /km</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Rest (detik)</label>
                        <input v-model.number="program.restTime" type="number" class="neon-input w-full rounded-xl p-3 text-center font-mono text-lg text-white" placeholder="60">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block">Daftar Atlet & Readiness</label>
                        <button @click="addSetupAthlete" class="text-neon-green text-xs font-bold hover:text-emerald-400"><i class="fa-solid fa-plus-circle"></i> ADD</button>
                    </div>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1 custom-scroll">
                        <div v-for="(a, i) in setupAthletes" :key="i" class="flex gap-2 items-center bg-slate-800/50 p-2 rounded-xl border border-slate-700">
                            <input v-model="a.name" class="bg-transparent w-full text-sm text-white font-mono outline-none placeholder-slate-600" placeholder="Nama Atlet...">
                            
                            <!-- Readiness Selector -->
                            <div class="flex bg-slate-900 rounded-lg p-1 gap-1 shrink-0">
                                <button @click="a.readiness = 'green'" :class="a.readiness === 'green' ? 'bg-green-500 scale-110' : 'bg-slate-700 opacity-50'" class="w-5 h-5 rounded-full transition"></button>
                                <button @click="a.readiness = 'yellow'" :class="a.readiness === 'yellow' ? 'bg-yellow-500 scale-110' : 'bg-slate-700 opacity-50'" class="w-5 h-5 rounded-full transition"></button>
                                <button @click="a.readiness = 'red'" :class="a.readiness === 'red' ? 'bg-red-500 scale-110' : 'bg-slate-700 opacity-50'" class="w-5 h-5 rounded-full transition"></button>
                            </div>

                            <button @click="removeSetupAthlete(i)" class="text-slate-600 hover:text-red-500 px-1"><i class="fa-solid fa-trash text-xs"></i></button>
                        </div>
                    </div>
                </div>

                <button @click="startSession" class="w-full bg-neon-green hover:bg-emerald-400 text-slate-900 font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] transition transform active:scale-95 flex items-center justify-center gap-2">
                    START SESSION <i class="fa-solid fa-play"></i>
                </button>
                
                <div v-if="hasSavedData" class="text-center">
                    <button @click="loadLastSession" class="text-xs text-slate-500 hover:text-white underline decoration-dashed">Lanjutkan sesi sebelumnya</button>
                </div>
            </div>
        </div>
    </section>

    <section v-if="view === 'track'" class="flex flex-col h-full relative">
        
        <header class="p-3 sm:p-4 glass z-20 shrink-0">
            <div class="flex justify-between items-center max-w-4xl mx-auto">
                <div class="flex items-center gap-3">
                    <div class="bg-black/50 px-3 py-2 rounded-lg border border-slate-700 w-32 sm:w-40 text-center">
                        <div class="text-3xl sm:text-4xl font-black font-mono tracking-tighter text-white" :class="{'text-yellow-400 animate-pulse': isPaused}">
                            @{{ formatTime(elapsedTime) }}
                        </div>
                    </div>
                    <div class="hidden sm:block leading-tight">
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Elapsed Time</div>
                        <div class="text-xs text-slate-300 font-mono">@{{ program.reps }}x@{{ program.distance }}m @ @{{ program.targetTime }}s</div>
                        <div class="flex gap-2 mt-1">
                            <span v-if="program.weather === 'sunny'" class="text-yellow-500 text-[10px]"><i class="fa-solid fa-sun"></i> Panas</span>
                            <span v-if="program.weather === 'cloudy'" class="text-slate-400 text-[10px]"><i class="fa-solid fa-cloud"></i> Berawan</span>
                            <span v-if="program.weather === 'rainy'" class="text-blue-400 text-[10px]"><i class="fa-solid fa-cloud-showers-heavy"></i> Hujan</span>
                            <span class="text-slate-600 text-[10px]">|</span>
                            <span class="text-[10px] font-bold uppercase" :class="program.workoutType === 'race' ? 'text-neon-red' : 'text-neon-green'">@{{ program.workoutType }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button @click="toggleTTS" class="w-10 h-10 rounded-full flex items-center justify-center border transition" 
                        :class="ttsEnabled ? 'bg-neon-blue/20 border-neon-blue text-neon-blue' : 'bg-slate-800 border-slate-700 text-slate-600'">
                        <i class="fa-solid" :class="ttsEnabled ? 'fa-volume-high' : 'fa-volume-xmark'"></i>
                    </button>
                    
                    <button @click="togglePause" class="h-10 px-4 rounded-full font-bold text-xs uppercase flex items-center gap-2 border transition"
                        :class="isPaused ? 'bg-neon-green/10 border-neon-green text-neon-green' : 'bg-yellow-500/10 border-yellow-500 text-yellow-500'">
                        <i class="fa-solid" :class="isPaused ? 'fa-play' : 'fa-pause'"></i>
                        <span class="hidden sm:inline">@{{ isPaused ? 'RESUME' : 'PAUSE' }}</span>
                    </button>
                    
                    <button @click="finishSession" class="w-10 h-10 rounded-full bg-red-500/10 border border-red-500/50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </button>
                </div>
            </div>
        </header>

        <div class="p-3 sm:p-4 overflow-y-auto shrink-0 max-h-[40vh]">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 max-w-4xl mx-auto">
                <div v-for="(a, idx) in athletes" :key="idx" @click="handleBalloon(idx)"
                    :class="getBalloonClass(a)"
                    class="aspect-square rounded-2xl flex flex-col items-center justify-center relative cursor-pointer transition-transform active:scale-95 select-none bg-slate-800">
                    
                    <!-- Readiness Indicator -->
                    <div class="absolute top-2 right-2 w-2 h-2 rounded-full shadow-[0_0_5px_currentColor]"
                        :class="{'bg-green-500 text-green-500': a.readiness === 'green', 'bg-yellow-500 text-yellow-500': a.readiness === 'yellow', 'bg-red-500 text-red-500': a.readiness === 'red'}">
                    </div>

                    <div v-if="isPaused" class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center rounded-2xl"><i class="fa-solid fa-pause text-white/30 text-2xl"></i></div>

                    <div class="text-xs font-bold uppercase truncate px-2 w-full text-center">@{{ a.name }}</div>
                    
                    <div v-if="a.status === 'active'" class="text-center mt-1">
                        <span class="text-2xl sm:text-3xl font-black font-mono">@{{ a.currentRep + 1 }}</span>
                        <span class="text-[10px] text-slate-500 font-bold block -mt-1">/@{{ program.reps }}</span>
                    </div>

                    <div v-else-if="a.status === 'resting'" class="text-center mt-1">
                        <span class="text-2xl sm:text-3xl font-black font-mono">@{{ Math.ceil(a.restCountdown) }}</span>
                        <span class="text-[9px] uppercase tracking-widest block -mt-1 opacity-70">REST</span>
                    </div>

                    <div v-else class="text-center mt-1">
                        <i class="fa-solid fa-check text-2xl"></i>
                        <span class="text-[9px] block mt-1">DONE</span>
                    </div>

                    <div class="absolute bottom-0 left-0 h-1 bg-white/10 w-full rounded-b-2xl overflow-hidden">
                        <div class="h-full bg-current transition-all duration-500" :style="{width: ((a.currentRep/program.reps)*100) + '%'}"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-grow glass border-t border-slate-700/50 flex flex-col overflow-hidden relative">
            <div class="overflow-auto flex-grow p-0" @click="activeTagLog = null">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-800/90 text-[10px] uppercase text-slate-400 font-bold sticky top-0 z-10 backdrop-blur">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">Atlet</th>
                            <th class="p-3 text-right">Waktu</th>
                            <th class="p-3 text-right hidden sm:table-cell">Pace</th>
                            <th class="p-3 text-right">Delta</th>
                            <th class="p-3 text-center">Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr v-for="log in logs.slice().reverse()" :key="log.id" class="hover:bg-slate-800/50 transition-colors">
                            <td class="p-3 font-mono text-slate-500 text-xs">@{{ log.rep }}</td>
                            <td class="p-3 font-bold text-white flex items-center gap-2">
                                @{{ log.name }}
                                <span v-if="log.rpe" class="w-2 h-2 rounded-full bg-neon-purple"></span>
                                <i v-if="log.notes && log.notes.includes('WARNING')" class="fa-solid fa-triangle-exclamation text-red-500 animate-pulse text-xs"></i>
                            </td>
                            <td class="p-3 text-right font-mono text-neon-green">@{{ log.time.toFixed(2) }}</td>
                            <td class="p-3 text-right font-mono text-slate-400 text-xs hidden sm:table-cell">@{{ log.pace }}</td>
                            <td class="p-3 text-right font-mono font-bold text-xs" :class="getDeltaClass(log.diff)">
                                @{{ log.diff > 0 ? '+' : '' }}@{{ log.diff.toFixed(2) }}
                            </td>
                            <td class="p-3 text-center relative">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click.stop="openModal(log)" class="text-slate-400 hover:text-white"><i class="fa-solid fa-pen"></i></button>
                                    <button @click.stop="activeTagLog = (activeTagLog === log.id ? null : log.id)" class="bg-slate-700 hover:bg-neon-blue text-white text-[10px] px-2 py-0.5 rounded font-bold transition">[+]</button>
                                </div>
                                
                                <!-- Quick Tags Popover -->
                                <div v-if="activeTagLog === log.id" class="absolute right-10 top-1/2 -translate-y-1/2 bg-slate-900 border border-slate-700 p-2 rounded-xl shadow-2xl z-50 flex flex-col gap-1 w-32 animate-[fadeIn_0.2s]" @click.stop>
                                    <div class="text-[9px] text-slate-500 uppercase font-bold mb-1 text-left px-1">Quick Tag</div>
                                    <button @click="addTag(log, 'Overstride')" class="bg-slate-800 hover:bg-neon-red/20 hover:text-neon-red text-left px-2 py-1.5 rounded text-[10px] text-slate-300 transition">Overstride</button>
                                    <button @click="addTag(log, 'Good Form')" class="bg-slate-800 hover:bg-neon-green/20 hover:text-neon-green text-left px-2 py-1.5 rounded text-[10px] text-slate-300 transition">Good Form</button>
                                    <button @click="addTag(log, 'Tegang')" class="bg-slate-800 hover:bg-yellow-500/20 hover:text-yellow-500 text-left px-2 py-1.5 rounded text-[10px] text-slate-300 transition">Tegang</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="logs.length === 0" class="flex flex-col items-center justify-center py-10 text-slate-600">
                    <p class="text-xs uppercase tracking-widest">Belum ada data</p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="view === 'summary'" class="flex-grow flex flex-col p-6 animate-[fadeIn_0.5s] overflow-y-auto">
        <div id="summary-capture" class="glass p-8 rounded-3xl shadow-2xl max-w-2xl mx-auto text-center border border-slate-700 bg-slate-900/90">
                <i class="fa-solid fa-trophy text-5xl text-yellow-400 mb-4 drop-shadow-[0_0_15px_rgba(250,204,21,0.5)]"></i>
                <h2 class="text-2xl font-bold text-white mb-1">Sesi Selesai!</h2>
                <p class="text-slate-400 text-xs uppercase mb-8">@{{ new Date().toLocaleString() }}</p>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-slate-800 p-4 rounded-xl">
                        <div class="text-[10px] text-slate-400 uppercase">Total Lap</div>
                        <div class="text-2xl font-mono font-bold text-white">@{{ logs.length }}</div>
                    </div>
                    <div class="bg-slate-800 p-4 rounded-xl">
                        <div class="text-[10px] text-slate-400 uppercase">Avg Time</div>
                        <div class="text-2xl font-mono font-bold text-neon-green">@{{ calculateAvg() }}s</div>
                    </div>
                </div>

                <div class="text-left space-y-6 mb-8">
                    <div v-for="a in athleteAnalysis" :key="a.name" class="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                        <h3 class="font-bold text-lg text-white mb-1 flex justify-between">
                            @{{ a.name }}
                            <span class="text-xs font-mono text-neon-green self-center">Avg: @{{ a.avg }}s</span>
                        </h3>
                        <p class="text-xs text-slate-400 italic mb-3">"@{{ a.feedback }}"</p>
                        <div class="h-40 w-full relative">
                            <canvas :id="'chart_' + a.safeName"></canvas>
                        </div>
                    </div>
                </div>

            <div class="flex gap-3 flex-wrap" data-html2canvas-ignore>
                <button @click="exportImage" class="flex-1 bg-neon-blue hover:bg-cyan-600 text-white font-bold py-3 rounded-xl transition whitespace-nowrap">
                    <i class="fa-solid fa-image mr-1"></i> IMG
                </button>
                <button @click="downloadCSV" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 rounded-xl transition whitespace-nowrap">
                    <i class="fa-solid fa-download mr-1"></i> CSV
                </button>
                <button @click="resetSession" class="flex-1 bg-neon-green hover:bg-emerald-400 text-slate-900 font-bold py-3 rounded-xl transition whitespace-nowrap">
                    <i class="fa-solid fa-rotate-right mr-1"></i> Baru
                </button>
            </div>
        </div>
    </section>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="showModal = false">
        <div class="bg-slate-900 border border-slate-700 w-full max-w-sm rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl animate-[slideUp_0.3s]">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-white text-lg">@{{ modalData.name }}</h3>
                    <p class="text-xs text-slate-400 font-mono">Rep #@{{ modalData.rep }} | @{{ modalData.time.toFixed(2) }}s</p>
                </div>
                <button @click="showModal = false" class="bg-slate-800 w-8 h-8 rounded-full text-slate-400"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-5">
                <label class="text-[10px] uppercase font-bold text-slate-400 mb-2 block">RPE (Usaha 1-10)</label>
                <div class="flex justify-between gap-1">
                    <button v-for="n in 10" :key="n" @click="modalData.rpe = n"
                        :class="modalData.rpe === n ? 'bg-gradient-to-t from-neon-green to-emerald-400 text-black scale-110' : 'bg-slate-800 text-slate-500'"
                        class="w-8 h-10 rounded font-bold text-xs transition-all">@{{ n }}</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 mb-1 block">Heart Rate</label>
                    <div class="relative">
                        <i class="fa-solid fa-heart absolute left-3 top-3 text-red-500 text-xs"></i>
                        <input v-model="modalData.hr" type="number" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-8 text-white focus:border-neon-green outline-none">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 mb-1 block">Cadence (SPM)</label>
                    <div class="relative flex gap-2">
                        <input v-model="modalData.cadence" type="number" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-2 text-center text-white focus:border-neon-green outline-none">
                        <button @click="toggleTapper" class="bg-slate-700 px-3 rounded-lg text-neon-blue hover:bg-slate-600"><i class="fa-solid fa-drum"></i></button>
                    </div>
                </div>
            </div>

            <div v-if="showTapper" class="mb-5 bg-slate-800/50 p-4 rounded-xl border border-dashed border-slate-600 text-center">
                <p class="text-[10px] text-slate-400 mb-2">Tap tombol mengikuti langkah kaki</p>
                <button @click="recordTap" class="w-full h-14 bg-slate-700 hover:bg-slate-600 active:bg-neon-green active:text-black rounded-lg font-bold text-slate-300 transition-colors">TAP HERE</button>
                <p class="mt-2 font-mono text-neon-green text-lg">@{{ tapperSpm }} <span class="text-xs text-slate-500">SPM</span></p>
            </div>

            <textarea v-model="modalData.notes" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-sm text-white focus:border-neon-green outline-none mb-4 h-20" placeholder="Catatan teknis..."></textarea>
            
            <button @click="saveModal" class="w-full bg-neon-green hover:bg-emerald-400 text-black font-bold py-3 rounded-xl transition">SIMPAN DATA</button>
        </div>
    </div>

</div>

<script>
    const { createApp, ref, reactive, computed, onMounted, nextTick } = Vue;

    createApp({
        setup() {
            // State
            const view = ref('setup');
            const hasSavedData = ref(false);
            const setupAthletes = ref([{name: '', readiness: 'green'}]);
            const activeTagLog = ref(null);
            
            const program = reactive({ 
                reps: 8, distance: 400, targetTime: 80, restTime: 60,
                weather: 'cloudy', workoutType: 'race'
            });
            
            // Engine
            const athletes = ref([]);
            const logs = ref([]);
            const athleteAnalysis = ref([]);
            const elapsedTime = ref(0);
            const isPaused = ref(false);
            const ttsEnabled = ref(true);
            let timerInterval = null;
            let lastTime = 0;

            // Modal
            const showModal = ref(false);
            const showTapper = ref(false);
            const modalData = reactive({});
            const tapTimes = ref([]);

            // -- CORE LOGIC --

            const addSetupAthlete = () => setupAthletes.value.push({name: '', readiness: 'green'});
            const removeSetupAthlete = (i) => setupAthletes.value.splice(i, 1);

            const startSession = () => {
                const valid = setupAthletes.value.filter(a => a.name.trim());
                if (!valid.length) return alert('Masukkan minimal 1 nama atlet');
                
                athletes.value = valid.map(n => ({
                    name: n.name, 
                    readiness: n.readiness,
                    status: 'active', currentRep: 0,
                    lapStart: 0, restCountdown: 0
                }));
                
                logs.value = [];
                elapsedTime.value = 0;
                view.value = 'track';
                runTimer();
                speak(`Sesi dimulai. Cuaca ${program.weather}. Mode ${program.workoutType}`);
                saveState();
            };

            const runTimer = () => {
                lastTime = Date.now();
                timerInterval = setInterval(() => {
                    if (isPaused.value) {
                        lastTime = Date.now(); 
                        return;
                    }
                    const now = Date.now();
                    const delta = (now - lastTime) / 1000;
                    lastTime = now;
                    elapsedTime.value += delta;

                    // Rest Logic
                    athletes.value.forEach(a => {
                        if (a.status === 'resting') {
                            a.restCountdown -= delta;
                            if (a.restCountdown <= 0) {
                                a.status = 'active';
                                a.lapStart = elapsedTime.value;
                                speak(`${a.name}, Jalan!`);
                            }
                        }
                    });
                }, 100);
            };

            // -- ACTIONS --
            const toggleTTS = () => {
                ttsEnabled.value = !ttsEnabled.value;
                if(ttsEnabled.value) speak("Suara aktif");
            };

            const togglePause = () => {
                isPaused.value = !isPaused.value;
                speak(isPaused.value ? "Pause" : "Resume");
            };

            const handleBalloon = (idx) => {
                if (isPaused.value) return;
                const a = athletes.value[idx];

                if (a.status === 'active') {
                    // Record Lap
                    a.currentRep++;
                    const lapTime = elapsedTime.value - a.lapStart;
                    const diff = lapTime - program.targetTime;
                    
                    // Warning Logic
                    let warning = false;
                    if(a.readiness === 'red' && diff < -2) {
                        speak(`Warning! ${a.name} terlalu cepat!`);
                        warning = true;
                    }

                    logs.value.push({
                        id: Date.now() + Math.random(),
                        name: a.name, rep: a.currentRep, time: lapTime, diff: diff,
                        pace: calculatePace(lapTime, program.distance),
                        rpe: null, hr: null, cadence: null, 
                        notes: warning ? '⚠️ PACE WARNING' : '',
                        readiness: a.readiness
                    });

                    // TTS Feedback
                    const diffText = diff > 0 ? `plus ${diff.toFixed(1)}` : `minus ${Math.abs(diff).toFixed(1)}`;
                    speak(`${a.name}, ${Math.floor(lapTime)} poin ${Math.floor((lapTime%1)*10)}, ${diffText}`);

                    // Next State
                    if (a.currentRep >= program.reps) {
                        a.status = 'finished';
                        speak(`${a.name} selesai`);
                    } else {
                        a.status = 'resting';
                        a.restCountdown = program.restTime;
                    }
                    saveState();
                } 
                else if (a.status === 'resting') {
                    // Manual Override (Skip rest)
                    a.status = 'active';
                    a.lapStart = elapsedTime.value;
                    a.restCountdown = 0;
                }
            };

            const finishSession = () => {
                if(confirm("Selesaikan sesi?")) {
                    clearInterval(timerInterval);
                    generateAnalysis();
                    view.value = 'summary';
                    saveState(true); 
                    nextTick(() => renderCharts());
                }
            };

            const resetSession = () => {
                view.value = 'setup';
            };

            const addTag = (log, tag) => {
                const current = log.notes ? log.notes + ', ' : '';
                // Avoid duplicates if simple check
                if(!current.includes(tag)) {
                    log.notes = current + tag;
                }
                activeTagLog.value = null;
                saveState();
            };

            // -- MODAL TOOLS --
            const openModal = (log) => {
                Object.assign(modalData, JSON.parse(JSON.stringify(log)));
                showModal.value = true;
                showTapper.value = false;
                tapTimes.value = [];
            };

            const saveModal = () => {
                const idx = logs.value.findIndex(l => l.id === modalData.id);
                if (idx !== -1) logs.value[idx] = {...logs.value[idx], ...modalData};
                showModal.value = false;
                saveState();
            };

            const toggleTapper = () => { showTapper.value = !showTapper.value; tapTimes.value = []; };
            const recordTap = () => {
                tapTimes.value.push(Date.now());
                if (tapTimes.value.length > 5) tapTimes.value.shift();
                if (tapTimes.value.length > 1) {
                    const durationMin = (tapTimes.value[tapTimes.value.length-1] - tapTimes.value[0]) / 60000;
                    modalData.cadence = Math.round((tapTimes.value.length - 1) / durationMin);
                }
            };

            const tapperSpm = computed(() => modalData.cadence || 0);

            // -- UTILS --
            const formatTime = (s) => {
                const m = Math.floor(s / 60);
                const sec = Math.floor(s % 60);
                const ms = Math.floor((s % 1) * 10);
                return `${m}:${sec.toString().padStart(2,'0')}.${ms}`;
            };

            const calculatePace = (t, d) => {
                if (!t || !d) return '-';
                const s = (t / d) * 1000;
                return `${Math.floor(s/60)}:${Math.floor(s%60).toString().padStart(2,'0')}`;
            };

            const calculateAvg = () => logs.value.length ? (logs.value.reduce((a,b)=>a+b.time,0)/logs.value.length).toFixed(1) : 0;

            const speak = (txt) => {
                if (!ttsEnabled.value) return;
                window.speechSynthesis.cancel();
                const u = new SpeechSynthesisUtterance(txt);
                u.lang = 'id-ID'; u.rate = 1.2;
                window.speechSynthesis.speak(u);
            };

            const getBalloonClass = (a) => {
                if (a.status === 'active') return 'b-active bg-slate-800';
                if (a.status === 'resting') return 'b-resting';
                return 'b-finished';
            };

            const getDeltaClass = (d) => Math.abs(d) <= 1 ? 'text-neon-green' : (d < -1 ? 'text-neon-blue' : 'text-neon-red');

            // -- STORAGE --
            const saveState = (isFinal = false) => {
                const data = { program, athletes: athletes.value, logs: logs.value, time: elapsedTime.value, active: !isFinal, date: new Date().toLocaleString() };
                localStorage.setItem('tm_pro_v2', JSON.stringify(data));
            };

            const loadLastSession = () => {
                const d = JSON.parse(localStorage.getItem('tm_pro_v2'));
                if (d) {
                    Object.assign(program, d.program);
                    if (d.active) {
                        athletes.value = d.athletes;
                        logs.value = d.logs;
                        elapsedTime.value = d.time;
                        view.value = 'track';
                        runTimer();
                    }
                    if (d.athletes && d.athletes.length > 0) {
                        setupAthletes.value = d.athletes.map(a => ({
                            name: a.name, 
                            readiness: a.readiness || 'green'
                        }));
                    }
                }
            };

            const downloadCSV = () => {
                let c = "Atlet,Rep,Waktu,Pace,Delta,RPE,HR,Cadence,Note\n";
                logs.value.forEach(l => c += `${l.name},${l.rep},${l.time.toFixed(2)},${l.pace},${l.diff.toFixed(2)},${l.rpe||''},${l.hr||''},${l.cadence||''},"${l.notes||''}"\n`);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(new Blob([c], {type: "text/csv"}));
                link.download = `Latihan_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            };

            const generateAnalysis = () => {
                const grouped = {};
                logs.value.forEach(l => {
                    if(!grouped[l.name]) grouped[l.name] = [];
                    grouped[l.name].push(l.time);
                });

                athleteAnalysis.value = Object.keys(grouped).map(name => {
                    const times = grouped[name];
                    const avg = times.reduce((a,b)=>a+b,0) / times.length;
                    
                    let feedback = "Performa stabil.";
                    if(times.length > 1) {
                        const half = Math.ceil(times.length/2);
                        const first = times.slice(0, half).reduce((a,b)=>a+b,0) / half;
                        const second = times.slice(half).reduce((a,b)=>a+b,0) / (times.length - half);
                        
                        if(second > first + 1) feedback = "Pace menurun di akhir (Positive Split).";
                        else if(first > second + 1) feedback = "Finishing kuat! (Negative Split).";
                        else feedback = "Pace sangat konsisten (Even Split).";
                    }

                    return {
                        name, 
                        safeName: name.replace(/\s+/g, '_'),
                        times, 
                        avg: avg.toFixed(2),
                        feedback
                    };
                });
            };

            const renderCharts = () => {
                athleteAnalysis.value.forEach(a => {
                    const ctx = document.getElementById('chart_' + a.safeName);
                    if(ctx) {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: a.times.map((_, i) => `Rep ${i+1}`),
                                datasets: [{
                                    label: 'Waktu',
                                    data: a.times,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    borderWidth: 2,
                                    tension: 0.3,
                                    pointBackgroundColor: '#fff',
                                    fill: true
                                }, {
                                    label: 'Target',
                                    data: Array(a.times.length).fill(program.targetTime),
                                    borderColor: '#f43f5e',
                                    borderWidth: 1,
                                    borderDash: [5, 5],
                                    pointRadius: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { 
                                        grid: { color: 'rgba(255,255,255,0.1)' },
                                        ticks: { color: '#94a3b8' }
                                    },
                                    x: { display: false }
                                }
                            }
                        });
                    }
                });
            };

            const exportImage = () => {
                const el = document.getElementById('summary-capture');
                html2canvas(el, { backgroundColor: '#0f172a' }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = `Summary_${new Date().toISOString().slice(0,10)}.png`;
                    link.href = canvas.toDataURL();
                    link.click();
                });
            };

            onMounted(() => {
                if (localStorage.getItem('tm_pro_v2')) hasSavedData.value = true;
            });

            return {
                view, program, setupAthletes, athletes, logs, elapsedTime, isPaused, ttsEnabled,
                showModal, showTapper, modalData, hasSavedData, tapperSpm, athleteAnalysis, activeTagLog,
                startSession, togglePause, handleBalloon, finishSession, toggleTTS, resetSession,
                openModal, saveModal, toggleTapper, recordTap, loadLastSession,
                formatTime, calculatePace, calculateAvg, getBalloonClass, getDeltaClass, downloadCSV, exportImage,
                addSetupAthlete, removeSetupAthlete, addTag
            };
        }
    }).mount('#app');
</script>
</body>
</html>