<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- SEO Meta Tags -->
    <title>TrackMaster | Stopwatch Interval dan Pace Tracker untuk Pelatih Lari</title>
    <meta name="description" content="Gunakan TrackMaster untuk mencatat split, pace, interval, target latihan, dan progres banyak atlet dalam satu sesi. Tools latihan lari yang lebih lengkap dari stopwatch biasa.">
    <meta name="keywords" content="stopwatch lari, interval timer lari, pace tracker, split timer lari, tools pelatih lari, latihan interval lari, catatan latihan lari">
    <meta name="author" content="Ruang Lari">
    <meta name="robots" content="index, follow">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/green/apple-touch-icon.png') }}">

    <meta name="theme-color" content="#08111F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="TrackMaster | Stopwatch Interval dan Pace Tracker untuk Pelatih Lari">
    <meta property="og:description" content="Gunakan TrackMaster untuk mencatat split, pace, interval, target latihan, dan progres banyak atlet dalam satu sesi.">
    <meta property="og:image" content="{{ asset('images/logo-full.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="TrackMaster | Stopwatch Interval dan Pace Tracker">
    <meta property="twitter:description" content="Stopwatch multi-atlet cerdas dengan analisa performa dan voice assistant.">
    <meta property="twitter:image" content="{{ asset('images/logo-full.png') }}">
    
    <!-- Scripts & CSS CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                fontFamily: { 
                    sans: ['Inter', 'sans-serif'], 
                    mono: ['JetBrains Mono', 'monospace'] 
                },
                extend: {
                    colors: {
                        rl: {
                            bg: '#08111F',
                            card: '#0E1A2D',
                            card2: '#111F35',
                            border: '#1F2D44',
                            text: '#F8FAFC',
                            textMuted: '#94A3B8',
                            lime: '#B8FF00',
                            warning: '#FACC15',
                            danger: '#EF4444'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { 
            background-color: #08111F; 
            color: #F8FAFC; 
            touch-action: manipulation; 
        }
        [v-cloak] { display: none; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #08111F; }
        ::-webkit-scrollbar-thumb { background: #1F2D44; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #B8FF00; }

        /* Print styling */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-card { background: white !important; border: 1px solid #ddd !important; color: black !important; box-shadow: none !important; }
            h1, h2, h3, h4, p, span, td, th { color: black !important; }
            canvas { max-height: 250px !important; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col font-sans">

<div id="app" v-cloak class="flex-grow flex flex-col min-h-screen max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-4 relative">

    <!-- Navbar / Header -->
    <nav class="no-print bg-rl-card border border-rl-border rounded-2xl p-4 mb-6 sticky top-4 z-40 shadow-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('tools.index') }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rl-card2 text-rl-textMuted hover:text-rl-lime border border-rl-border transition">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </a>
                <div>
                    <span class="text-xl font-extrabold text-rl-text tracking-tighter">TRACK<span class="text-rl-lime">MASTER</span></span>
                    <p class="text-[9px] text-rl-textMuted font-mono uppercase tracking-widest leading-none mt-0.5">Professional Interval Assistant</p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button @click="changeView('setup')" :class="view==='setup' ? 'bg-rl-lime text-rl-bg font-extrabold shadow-lg shadow-rl-lime/20' : 'bg-rl-card2 text-rl-textMuted hover:text-rl-text border border-rl-border'" class="px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all duration-200">Setup</button>
                <button @click="changeView('track')" :class="view==='track' ? 'bg-rl-lime text-rl-bg font-extrabold shadow-lg shadow-rl-lime/20' : 'bg-rl-card2 text-rl-textMuted hover:text-rl-text border border-rl-border'" class="px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all duration-200" :disabled="!hasStarted">Track</button>
                <button @click="changeView('summary')" :class="view==='summary' ? 'bg-rl-lime text-rl-bg font-extrabold shadow-lg shadow-rl-lime/20' : 'bg-rl-card2 text-rl-textMuted hover:text-rl-text border border-rl-border'" class="px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all duration-200" :disabled="logs.length === 0">Summary</button>
                <button @click="changeView('history')" :class="view==='history' ? 'bg-rl-lime text-rl-bg font-extrabold shadow-lg shadow-rl-lime/20' : 'bg-rl-card2 text-rl-textMuted hover:text-rl-text border border-rl-border'" class="px-4 py-2 rounded-xl text-xs font-bold uppercase transition-all duration-200">History</button>
            </div>
        </div>
    </nav>

    <!-- SECTION 1: SETUP SESSION -->
    <section v-if="view === 'setup'" class="flex-grow grid grid-cols-1 lg:grid-cols-12 gap-6 items-start animate-[fadeIn_0.3s]">
        
        <!-- Left: Preset & Config -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-rl-card border border-rl-border p-6 rounded-3xl shadow-2xl">
                <h1 class="text-2xl font-black text-rl-text mb-1 tracking-tight">TrackMaster: Stopwatch Interval untuk Pelatih Lari</h1>
                <p class="text-xs text-rl-textMuted mb-6">TrackMaster membantu pelatih dan komunitas lari mencatat split, pace, interval, dan progres banyak atlet dalam satu sesi latihan.</p>

                <!-- Preset Selection -->
                <div class="space-y-3 mb-6">
                    <label class="text-[11px] font-bold text-rl-textMuted uppercase tracking-wider block">Pilih Preset Latihan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="p in presets" :key="p.name" @click="selectPreset(p)"
                            :class="selectedPresetName === p.name ? 'border-rl-lime text-rl-lime bg-rl-card2' : 'border-rl-border text-rl-textMuted bg-rl-bg'"
                            class="text-left p-3 rounded-xl border hover:border-rl-lime/50 transition duration-150 flex flex-col justify-between">
                            <span class="text-xs font-bold text-rl-text leading-tight mb-1">@{{ p.name }}</span>
                            <span class="text-[10px] font-mono opacity-80">@{{ p.reps }}x @{{ p.distance }}m (Rest @{{ p.rest }}s)</span>
                        </button>
                    </div>
                </div>

                <!-- Session Params -->
                <div class="space-y-4 border-t border-rl-border pt-4">
                    <div>
                        <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Nama Sesi Latihan</label>
                        <input v-model="program.name" type="text" class="w-full bg-rl-bg border border-rl-border text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="e.g. Speed Session Selasa Sore">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Lokasi Latihan (Opsional)</label>
                            <input v-model="program.location" type="text" class="w-full bg-rl-bg border border-rl-border text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="e.g. Gelora Bung Karno">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Weather / Cuaca</label>
                            <select v-model="program.weather" class="w-full bg-rl-bg border border-rl-border text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition">
                                <option value="Cerah">☀️ Cerah / Panas</option>
                                <option value="Berawan">☁️ Berawan</option>
                                <option value="Hujan">🌧️ Hujan Ringan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Repetisi</label>
                            <input v-model.number="program.reps" type="number" min="1" class="w-full bg-rl-bg border border-rl-border text-center font-mono text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Jarak (m)</label>
                            <input v-model.number="program.distance" type="number" min="1" class="w-full bg-rl-bg border border-rl-border text-center font-mono text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" @change="recalculateAllTargetsFromPace">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Rest (detik)</label>
                            <input v-model.number="program.rest" type="number" min="0" class="w-full bg-rl-bg border border-rl-border text-center font-mono text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" @change="syncRestTimeToAthletes">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Target Pace (Min:Det /km)</label>
                            <input v-model="program.targetPace" type="text" class="w-full bg-rl-bg border border-rl-border text-center font-mono text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="04:00" @input="recalculateTargetTimeFromPace">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Target Waktu / Lap (detik)</label>
                            <input v-model.number="program.targetTime" type="number" min="1" class="w-full bg-rl-bg border border-rl-border text-center font-mono text-sm text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="96" @input="recalculateTargetPaceFromTime">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voice Cue Settings Optional -->
            <div class="bg-rl-card border border-rl-border p-5 rounded-3xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-extrabold text-rl-text uppercase tracking-wider">Voice Cues & Audio Assistant</h4>
                        <p class="text-[11px] text-rl-textMuted leading-tight mt-0.5">Gunakan panduan suara otomatis selama latihan berlangsung.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="ttsEnabled" class="sr-only peer">
                        <div class="w-9 h-5 bg-rl-card2 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-rl-textMuted after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-rl-lime peer-checked:after:bg-rl-bg"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right: Athletes Selection -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-rl-card border border-rl-border p-6 rounded-3xl shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-rl-text tracking-tight">Daftar Atlet Latihan</h3>
                        <p class="text-xs text-rl-textMuted">Tentukan target pace personal dan kesiapan fisik masing-masing atlet.</p>
                    </div>
                    <button @click="addSetupAthlete" class="bg-rl-lime/10 hover:bg-rl-lime text-rl-lime hover:text-rl-bg border border-rl-lime/30 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus-circle"></i> Tambah Atlet
                    </button>
                </div>

                <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                    <div v-for="(a, i) in setupAthletes" :key="i" class="bg-rl-card2 border border-rl-border p-4 rounded-2xl relative group hover:border-rl-lime/30 transition-all duration-200">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <!-- Name input -->
                            <div class="md:col-span-5">
                                <label class="text-[9px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Nama Atlet</label>
                                <input v-model="a.name" class="w-full bg-rl-bg border border-rl-border text-sm text-rl-text font-semibold p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="Nama Atlet">
                            </div>

                            <!-- Target Time -->
                            <div class="md:col-span-2">
                                <label class="text-[9px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Target (s)</label>
                                <input v-model.number="a.target" type="number" class="w-full bg-rl-bg border border-rl-border text-center font-mono text-sm text-rl-text p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition" @input="onSetupAthleteTargetTimeChange(a)">
                            </div>

                            <!-- Target Pace -->
                            <div class="md:col-span-2 text-center">
                                <label class="text-[9px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Pace</label>
                                <div class="text-xs font-mono font-bold text-rl-lime bg-rl-bg/50 py-2.5 border border-rl-border rounded-xl">
                                    @{{ a.targetPace || '-' }}
                                </div>
                            </div>

                            <!-- Readiness state -->
                            <div class="md:col-span-2 text-center">
                                <label class="text-[9px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Readiness</label>
                                <select v-model="a.readiness" class="w-full bg-rl-bg border border-rl-border text-xs text-rl-text font-bold p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition">
                                    <option value="green">🟢 Siap</option>
                                    <option value="yellow">🟡 Lelah</option>
                                    <option value="red">🔴 Cedera</option>
                                </select>
                            </div>

                            <!-- Delete -->
                            <div class="md:col-span-1 text-right">
                                <button @click="removeSetupAthlete(i)" class="w-full bg-rl-danger/10 hover:bg-rl-danger text-rl-danger hover:text-rl-text p-2.5 rounded-xl transition border border-rl-danger/20 flex items-center justify-center">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="startSession" class="w-full bg-rl-lime hover:bg-lime-400 text-rl-bg font-extrabold py-4 rounded-2xl shadow-lg shadow-rl-lime/10 transition transform active:scale-98 flex items-center justify-center gap-2 mt-6">
                    <i class="fa-solid fa-play"></i> MULAI SESI LATIHAN INTERVAL
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 2: LIVE SESSION TRACKING -->
    <section v-if="view === 'track'" class="flex-grow flex flex-col lg:grid lg:grid-cols-12 lg:gap-6 animate-[fadeIn_0.3s]">
        
        <!-- Center/Main: Large timer & controls (lg:col-span-8) -->
        <div class="lg:col-span-8 flex flex-col space-y-6">
            
            <!-- Large Stopwatch Display -->
            <div class="bg-rl-card border border-rl-border p-6 rounded-3xl shadow-xl relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-rl-card2 px-5 py-3 rounded-2xl border border-rl-border w-44 sm:w-52 text-center shadow-inner">
                        <div class="text-4xl sm:text-5xl font-black font-mono tracking-tighter text-rl-text" :class="{'text-rl-warning animate-pulse': isPaused}">
                            @{{ formatTime(elapsedTime) }}
                        </div>
                        <span class="text-[9px] text-rl-textMuted font-mono uppercase tracking-widest block mt-0.5">Waktu Utama</span>
                    </div>

                    <div class="leading-tight">
                        <h2 class="text-sm font-extrabold text-rl-text">@{{ program.name }}</h2>
                        <span class="text-xs text-rl-textMuted font-mono block">@{{ program.reps }}x @{{ program.distance }}m · Rest @{{ program.rest }}s</span>
                        <div class="flex gap-2 mt-1">
                            <span class="text-[10px] text-rl-lime bg-rl-lime/10 border border-rl-lime/20 px-2 py-0.5 rounded font-mono">Pace Tgt: @{{ program.targetPace }}</span>
                            <span class="text-[10px] text-rl-textMuted bg-rl-card2 border border-rl-border px-2 py-0.5 rounded font-mono">Cuaca: @{{ program.weather }}</span>
                        </div>
                    </div>
                </div>

                <!-- Main Action Buttons -->
                <div class="flex gap-2 w-full sm:w-auto">
                    <button v-if="!hasStarted" @click="startTimerNow" class="flex-1 sm:flex-initial bg-rl-lime hover:bg-lime-400 text-rl-bg font-black px-6 py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-rl-lime/10">
                        <i class="fa-solid fa-play"></i> START TIMER
                    </button>
                    <template v-else>
                        <button @click="togglePause" class="flex-1 sm:flex-initial px-5 py-3 rounded-xl font-bold text-xs uppercase flex items-center justify-center gap-2 border transition-all duration-200"
                            :class="isPaused ? 'bg-rl-lime/10 border-rl-lime text-rl-lime' : 'bg-rl-warning/10 border-rl-warning text-rl-warning'">
                            <i class="fa-solid" :class="isPaused ? 'fa-play' : 'fa-pause'"></i>
                            <span>@{{ isPaused ? 'RESUME' : 'PAUSE' }}</span>
                        </button>
                        <button @click="finishSession" class="bg-rl-danger/10 border border-rl-danger/40 text-rl-danger hover:bg-rl-danger hover:text-rl-text px-5 py-3 rounded-xl transition flex items-center justify-center gap-1.5 font-bold text-xs">
                            <i class="fa-solid fa-flag-checkered"></i> SELESAI Sesi
                        </button>
                    </template>
                </div>
            </div>

            <!-- Active Athletes Cards (Mobile-first, layout grid, large lap buttons) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(a, idx) in athletes" :key="idx" class="bg-rl-card border p-5 rounded-3xl relative overflow-hidden transition-all duration-300"
                    :class="a.status === 'resting' ? 'border-rl-warning/40 shadow-rl-warning/5' : (a.status === 'completed' ? 'border-rl-border opacity-70' : 'border-rl-border hover:border-rl-lime/40')">
                    
                    <!-- Background status pulse -->
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-rl-card2">
                        <div class="h-full bg-rl-lime transition-all duration-500 shadow-[0_0_8px_rgba(184,255,0,0.5)]" :style="{width: ((a.laps.length/program.reps)*100) + '%'}"></div>
                    </div>

                    <!-- Header Card -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full" :class="{'bg-rl-lime': a.readiness==='green', 'bg-rl-warning': a.readiness==='yellow', 'bg-rl-danger': a.readiness==='red'}"></span>
                                <h3 class="font-extrabold text-rl-text text-sm">@{{ a.name }}</h3>
                            </div>
                            <span class="text-[10px] text-rl-textMuted font-mono">Target: @{{ a.target }}s (@{{ a.targetPace }} pace)</span>
                        </div>

                        <!-- Status badge -->
                        <span class="text-[9px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
                            :class="getAthleteStatusBadgeClass(a)">
                            @{{ formatAthleteStatusText(a) }}
                        </span>
                    </div>

                    <!-- Big lap display / Active counter -->
                    <div class="grid grid-cols-3 gap-3 mb-4 items-center">
                        <div class="bg-rl-bg/60 p-2 border border-rl-border rounded-xl text-center">
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block leading-none mb-1">Repetisi</span>
                            <span class="text-lg font-black font-mono text-rl-text">@{{ a.status === 'completed' ? a.laps.length : a.laps.length + 1 }} / @{{ program.reps }}</span>
                        </div>
                        <div class="bg-rl-bg/60 p-2 border border-rl-border rounded-xl text-center col-span-2">
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block leading-none mb-1">Stopwatch Lari</span>
                            <span class="text-xl font-bold font-mono text-rl-lime leading-none">
                                @{{ getAthleteActiveTimerFormatted(a) }}
                            </span>
                        </div>
                    </div>

                    <!-- Big Lap Button -->
                    <div class="flex gap-2">
                        <button v-if="a.status === 'active'" @click="recordLap(idx)" :disabled="isPaused || !hasStarted"
                            class="flex-grow bg-rl-lime hover:bg-lime-400 text-rl-bg font-extrabold py-3.5 px-4 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-1.5 text-sm">
                            <i class="fa-solid fa-stopwatch"></i> RECORD LAP / SPLIT
                        </button>
                        <button v-else-if="a.status === 'resting'" @click="skipRestForAthlete(idx)"
                            class="flex-grow bg-rl-warning hover:bg-yellow-400 text-rl-bg font-extrabold py-3.5 px-4 rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-1 text-xs">
                            <i class="fa-solid fa-forward"></i> SKIP REST (@{{ Math.ceil(a.restCountdown) }}s)
                        </button>
                        <div v-else class="flex-grow bg-rl-card2 text-rl-textMuted text-xs font-bold py-3.5 rounded-2xl border border-rl-border text-center">
                            ✅ Sesi Selesai
                        </div>
                        
                        <!-- Quick notes trigger -->
                        <button @click="openNotesModalForAthlete(a)" class="bg-rl-card2 hover:bg-rl-card hover:text-rl-lime border border-rl-border text-rl-textMuted px-3.5 rounded-2xl transition">
                            <i class="fa-solid fa-notes-medical"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Active Logs Feed & Sesi summary (lg:col-span-4) -->
        <div class="lg:col-span-4 mt-6 lg:mt-0 flex flex-col space-y-6">
            
            <!-- Session Stats Panel -->
            <div class="bg-rl-card border border-rl-border p-5 rounded-3xl shadow-xl">
                <h3 class="text-sm font-extrabold text-rl-text uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-rl-lime"></i> Live Sesi Analitik
                </h3>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-rl-card2 border border-rl-border p-3 rounded-xl text-center">
                        <span class="text-[9px] text-rl-textMuted uppercase font-bold block leading-none">Total Laps</span>
                        <span class="text-xl font-bold font-mono text-rl-text">@{{ logs.length }}</span>
                    </div>
                    <div class="bg-rl-card2 border border-rl-border p-3 rounded-xl text-center">
                        <span class="text-[9px] text-rl-textMuted uppercase font-bold block leading-none">Avg Lap Time</span>
                        <span class="text-xl font-bold font-mono text-rl-lime">@{{ calculateAvg() }}s</span>
                    </div>
                </div>

                <!-- List of last logs -->
                <div class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
                    <div class="text-[10px] text-rl-textMuted uppercase font-bold block mb-1">Riwayat Split Terbaru</div>
                    
                    <div v-for="log in logs.slice().reverse()" :key="log.athleteName + log.rep" 
                        class="bg-rl-bg border border-rl-border p-3 rounded-xl flex items-center justify-between text-xs hover:border-rl-lime/20 transition">
                        <div>
                            <span class="font-extrabold text-rl-text">@{{ log.athleteName }}</span>
                            <span class="text-[10px] text-rl-textMuted block">Rep #@{{ log.rep }} · Pace: @{{ log.pace }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-rl-lime block">@{{ log.time.toFixed(1) }}s</span>
                            <span class="text-[10px] font-mono font-bold block" :class="getDeltaClass(log.diff)">
                                @{{ log.diff > 0 ? '+' : '' }}@{{ log.diff.toFixed(1) }}s
                            </span>
                        </div>
                    </div>
                    
                    <div v-if="logs.length === 0" class="text-center py-6 text-rl-textMuted text-xs">
                        Belum ada split tercatat
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: SUMMARY SESSION -->
    <section v-if="view === 'summary'" class="flex-grow max-w-4xl mx-auto w-full space-y-6 animate-[fadeIn_0.3s]">
        
        <!-- Summary Dashboard Card -->
        <div id="summary-print-container" class="bg-rl-card border border-rl-border p-6 sm:p-8 rounded-3xl shadow-2xl relative overflow-hidden print-card">
            
            <div class="flex flex-col sm:flex-row items-center justify-between border-b border-rl-border pb-6 mb-6 gap-4">
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-black text-rl-text tracking-tight">Ringkasan Sesi Latihan</h2>
                    <p class="text-xs text-rl-textMuted mt-0.5">Sesi: @{{ program.name }} · Lokasi: @{{ program.location || 'Stadion / Track' }}</p>
                </div>
                <div class="text-center sm:text-right font-mono text-xs text-rl-textMuted">
                    Tanggal: @{{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                </div>
            </div>

            <!-- Key metrics row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Total Atlet</span>
                    <span class="text-2xl font-black text-rl-text font-mono">@{{ athleteAnalysis.length }}</span>
                </div>
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Total Reps</span>
                    <span class="text-2xl font-black text-rl-text font-mono">@{{ logs.length }}</span>
                </div>
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Avg Pace Sesi</span>
                    <span class="text-2xl font-black text-rl-lime font-mono">@{{ formatAvgPaceOfSession() }}</span>
                </div>
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Durasi Latihan</span>
                    <span class="text-2xl font-black text-rl-text font-mono">@{{ formatTime(elapsedTime) }}</span>
                </div>
            </div>

            <!-- Athlete Performances Detail -->
            <div class="space-y-6">
                <h3 class="text-base font-extrabold text-rl-text border-b border-rl-border pb-2">Analisa Performa Atlet</h3>

                <div v-for="(a, index) in athleteAnalysis" :key="a.name" 
                    class="bg-rl-card2 border border-rl-border p-5 rounded-2xl space-y-4 print-card">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-rl-border/60 pb-3">
                        <div>
                            <span class="w-2 h-2 rounded-full inline-block mr-1.5" :class="{'bg-rl-lime': a.readiness==='green', 'bg-rl-warning': a.readiness==='yellow', 'bg-rl-danger': a.readiness==='red'}"></span>
                            <h4 class="text-base font-black text-rl-text inline-block">@{{ a.name }}</h4>
                        </div>
                        <div class="flex gap-4 text-xs font-mono text-rl-textMuted">
                            <span>Avg Lap: <strong class="text-rl-lime">@{{ a.stats.avgTime.toFixed(1) }}s</strong></span>
                            <span>Avg Pace: <strong class="text-rl-lime">@{{ a.stats.avgPace }}</strong></span>
                            <span>Konsistensi: <strong class="text-rl-lime">@{{ a.stats.consistency }}%</strong></span>
                        </div>
                    </div>

                    <!-- Performance Coach Insights -->
                    <div class="bg-rl-bg p-3.5 rounded-xl border border-rl-border/60 text-xs leading-relaxed print-card">
                        <span class="font-bold text-rl-lime block mb-1 font-mono uppercase text-[9px] tracking-wider">Sport Performance Analyst Insight</span>
                        @{{ a.feedback }}
                    </div>

                    <!-- Graph Container -->
                    <div class="h-44 w-full relative">
                        <canvas :id="'chart_' + index"></canvas>
                    </div>

                    <!-- Coach Notes Input / Show -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-rl-border/60 pt-4">
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Evaluasi Teknik & Kondisi</label>
                            <div class="flex gap-2">
                                <input v-model="a.notes.condition" type="text" placeholder="Kondisi (e.g. Lelah di rep 7)" 
                                    class="w-1/2 bg-rl-bg border border-rl-border text-xs text-rl-text p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition print-card">
                                <input v-model="a.notes.technique" type="text" placeholder="Teknik (e.g. Overstriding)" 
                                    class="w-1/2 bg-rl-bg border border-rl-border text-xs text-rl-text p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition print-card">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Rekomendasi & Injury Note</label>
                            <div class="flex gap-2">
                                <input v-model="a.notes.recommendation" type="text" placeholder="Rekomendasi Latihan" 
                                    class="w-1/2 bg-rl-bg border border-rl-border text-xs text-rl-text p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition print-card">
                                <input v-model="a.notes.injury" type="text" placeholder="Cedera Ringan (Jika Ada)" 
                                    class="w-1/2 bg-rl-bg border border-rl-border text-xs text-rl-text p-2.5 rounded-xl focus:border-rl-lime focus:outline-none transition print-card">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3 flex-wrap mt-8 border-t border-rl-border pt-6 no-print">
                <button @click="copySummary(null)" class="flex-1 bg-rl-card2 hover:bg-rl-card text-rl-text border border-rl-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-copy"></i> SALIN RINGKASAN
                </button>
                <button @click="shareWhatsApp(null)" class="flex-1 bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-brands fa-whatsapp"></i> WA SHARE
                </button>
                <button @click="exportCSV(null)" class="flex-1 bg-rl-card2 hover:bg-rl-card text-rl-text border border-rl-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-csv"></i> EXPORT CSV
                </button>
                <button @click="printPDF" class="flex-1 bg-rl-card2 hover:bg-rl-card text-rl-text border border-rl-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-pdf"></i> PRINT PDF
                </button>
                <button @click="resetSession" class="flex-1 bg-rl-lime hover:bg-lime-400 text-rl-bg font-extrabold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs shadow-md">
                    <i class="fa-solid fa-rotate-right"></i> MULAI SESI BARU
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 4: SESSION HISTORY -->
    <section v-if="view === 'history'" class="flex-grow max-w-4xl mx-auto w-full space-y-6 animate-[fadeIn_0.3s]">
        <div class="bg-rl-card border border-rl-border p-6 rounded-3xl shadow-xl">
            <div class="flex justify-between items-center mb-6 border-b border-rl-border pb-4">
                <div>
                    <h2 class="text-xl font-bold text-rl-text">Riwayat Sesi Latihan</h2>
                    <p class="text-xs text-rl-textMuted">Tinjau kembali log latihan interval, evaluasi atlet, dan statistik sesi sebelumnya.</p>
                </div>
                <button v-if="sessionHistory.length > 0" @click="clearAllHistory" class="text-xs font-bold text-rl-danger border border-rl-danger/30 bg-rl-danger/10 px-3 py-1.5 rounded-xl hover:bg-rl-danger hover:text-rl-text transition">
                    Hapus Semua Riwayat
                </button>
            </div>

            <!-- List of past sessions -->
            <div v-if="sessionHistory.length === 0" class="text-center py-12 text-rl-textMuted">
                <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 block opacity-30"></i>
                <p class="text-sm">Belum ada riwayat sesi tersimpan.</p>
                <button @click="changeView('setup')" class="mt-4 bg-rl-lime text-rl-bg font-extrabold text-xs px-4 py-2 rounded-xl">Mulai Sesi Setup</button>
            </div>

            <div v-else class="space-y-4">
                <div v-for="s in sessionHistory" :key="s.id" class="bg-rl-card2 border border-rl-border p-5 rounded-2xl hover:border-rl-lime/30 transition-all duration-150">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                        <div>
                            <h3 class="font-extrabold text-base text-rl-text">@{{ s.name }}</h3>
                            <div class="flex gap-3 text-[11px] text-rl-textMuted mt-1">
                                <span>📅 @{{ s.date }} - @{{ s.time }}</span>
                                <span>📍 @{{ s.location }}</span>
                                <span>🏃 @{{ s.workoutType }}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button @click="viewHistorySession(s)" class="bg-rl-lime text-rl-bg font-extrabold text-xs px-3.5 py-2 rounded-xl transition">
                                Lihat Detail
                            </button>
                            <button @click="deleteHistorySession(s.id)" class="bg-rl-danger/10 border border-rl-danger/20 text-rl-danger hover:bg-rl-danger hover:text-rl-text text-xs p-2 rounded-xl transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mini status grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-rl-bg/50 p-3 rounded-xl border border-rl-border text-center text-xs">
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Jumlah Atlet</span>
                            <span class="font-mono font-bold text-rl-text">@{{ s.athletes.length }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Total Split</span>
                            <span class="font-mono font-bold text-rl-text">@{{ s.logs.length }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Rata-rata Pace</span>
                            <span class="font-mono font-bold text-rl-lime">@{{ formatAvgPaceOfSession(s) }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Metode Latihan</span>
                            <span class="font-mono font-bold text-rl-text">@{{ s.program.reps }}x@{{ s.program.distance }}m</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: DETAILED HISTORICAL SESSION VIEW -->
    <section v-if="view === 'history_detail' && selectedHistorySession" class="flex-grow max-w-4xl mx-auto w-full space-y-6 animate-[fadeIn_0.3s]">
        <div class="bg-rl-card border border-rl-border p-6 sm:p-8 rounded-3xl shadow-xl print-card">
            
            <div class="flex flex-col sm:flex-row items-center justify-between border-b border-rl-border pb-6 mb-6 gap-4">
                <div class="text-center sm:text-left">
                    <button @click="changeView('history')" class="no-print bg-rl-card2 hover:text-rl-lime border border-rl-border text-rl-textMuted text-xs font-bold px-3 py-1.5 rounded-xl transition mb-2 flex items-center gap-1">
                        <i class="fa-solid fa-chevron-left"></i> Kembali ke Riwayat
                    </button>
                    <h2 class="text-2xl font-black text-rl-text tracking-tight">@{{ selectedHistorySession.name }}</h2>
                    <p class="text-xs text-rl-textMuted mt-0.5">📍 @{{ selectedHistorySession.location }} · Latihan: @{{ selectedHistorySession.workoutType }} (@{{ selectedHistorySession.program.reps }}x@{{ selectedHistorySession.program.distance }}m)</p>
                </div>
                <div class="text-center sm:text-right font-mono text-xs text-rl-textMuted">
                    Tanggal: @{{ selectedHistorySession.date }} - @{{ selectedHistorySession.time }}
                </div>
            </div>

            <!-- Key metrics row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Total Atlet</span>
                    <span class="text-2xl font-black text-rl-text font-mono">@{{ selectedHistorySession.athletes.length }}</span>
                </div>
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Total Reps</span>
                    <span class="text-2xl font-black text-rl-text font-mono">@{{ selectedHistorySession.logs.length }}</span>
                </div>
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Avg Pace Sesi</span>
                    <span class="text-2xl font-black text-rl-lime font-mono">@{{ formatAvgPaceOfSession(selectedHistorySession) }}</span>
                </div>
                <div class="bg-rl-bg border border-rl-border p-4 rounded-2xl text-center print-card">
                    <span class="text-[9px] text-rl-textMuted uppercase font-bold tracking-wider block mb-1">Total Durasi</span>
                    <span class="text-2xl font-black text-rl-text font-mono">@{{ formatTime(selectedHistorySession.program.elapsedTime || 0) }}</span>
                </div>
            </div>

            <!-- Athlete Performances Detail -->
            <div class="space-y-6">
                <h3 class="text-base font-extrabold text-rl-text border-b border-rl-border pb-2">Analisa Performa Atlet</h3>

                <div v-for="(a, index) in selectedHistorySession.athletes" :key="a.name" 
                    class="bg-rl-card2 border border-rl-border p-5 rounded-2xl space-y-4 print-card">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-rl-border/60 pb-3">
                        <div>
                            <span class="w-2 h-2 rounded-full inline-block mr-1.5" :class="{'bg-rl-lime': a.readiness==='green', 'bg-rl-warning': a.readiness==='yellow', 'bg-rl-danger': a.readiness==='red'}"></span>
                            <h4 class="text-base font-black text-rl-text inline-block">@{{ a.name }}</h4>
                        </div>
                        <div class="flex gap-4 text-xs font-mono text-rl-textMuted">
                            <span>Avg Lap: <strong class="text-rl-lime">@{{ getAthleteAvgTime(a).toFixed(1) }}s</strong></span>
                            <span>Avg Pace: <strong class="text-rl-lime">@{{ getAthleteAvgPace(a) }}</strong></span>
                            <span>Konsistensi: <strong class="text-rl-lime">@{{ getAthleteConsistencyScore(a) }}%</strong></span>
                        </div>
                    </div>

                    <!-- Performance Coach Insights -->
                    <div class="bg-rl-bg p-3.5 rounded-xl border border-rl-border/60 text-xs leading-relaxed print-card">
                        <span class="font-bold text-rl-lime block mb-1 font-mono uppercase text-[9px] tracking-wider">Sport Performance Analyst Insight</span>
                        @{{ getHistoryAthleteFeedback(a, selectedHistorySession.program) }}
                    </div>

                    <!-- Graph Container -->
                    <div class="h-44 w-full relative">
                        <canvas :id="'history_chart_' + index"></canvas>
                    </div>

                    <!-- Coach Notes Display -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-rl-bg p-4 rounded-xl border border-rl-border/60 text-xs print-card">
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Kondisi Fisik</span>
                            <span class="text-rl-text font-semibold">@{{ a.notes.condition || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Evaluasi Teknik</span>
                            <span class="text-rl-text font-semibold">@{{ a.notes.technique || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Cedera Ringan</span>
                            <span class="text-rl-text font-semibold">@{{ a.notes.injury || 'Tidak Ada' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-rl-textMuted uppercase font-bold block mb-0.5">Rekomendasi</span>
                            <span class="text-rl-text font-semibold">@{{ a.notes.recommendation || 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3 flex-wrap mt-8 border-t border-rl-border pt-6 no-print">
                <button @click="copySummary(selectedHistorySession)" class="flex-1 bg-rl-card2 hover:bg-rl-card text-rl-text border border-rl-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-copy"></i> SALIN RINGKASAN
                </button>
                <button @click="shareWhatsApp(selectedHistorySession)" class="flex-1 bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-brands fa-whatsapp"></i> WA SHARE
                </button>
                <button @click="exportCSV(selectedHistorySession)" class="flex-1 bg-rl-card2 hover:bg-rl-card text-rl-text border border-rl-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-csv"></i> EXPORT CSV
                </button>
                <button @click="printPDF" class="flex-1 bg-rl-card2 hover:bg-rl-card text-rl-text border border-rl-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-pdf"></i> PRINT PDF
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 6: COACH NOTES MODAL (DURING ACTIVE RUN) -->
    <div v-if="showNotesModal && activeAthleteForNotes" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm no-print" @click.self="showNotesModal = false">
        <div class="bg-rl-card border border-rl-border w-full max-w-md rounded-3xl p-6 shadow-2xl animate-[fadeIn_0.2s]">
            <div class="flex justify-between items-center mb-5 border-b border-rl-border pb-3">
                <div>
                    <h3 class="font-extrabold text-rl-text text-base">Evaluasi Atlet: @{{ activeAthleteForNotes.name }}</h3>
                    <p class="text-[10px] text-rl-textMuted uppercase font-mono">Input Catatan Pelatih Real-time</p>
                </div>
                <button @click="showNotesModal = false" class="bg-rl-card2 w-8 h-8 rounded-full text-rl-textMuted hover:text-rl-lime border border-rl-border flex items-center justify-center transition"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Kondisi Atlet</label>
                    <input v-model="coachNotes.condition" type="text" class="w-full bg-rl-bg border border-rl-border text-xs text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="e.g. Segar / Lelah / Kram ringan">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Evaluasi Teknik</label>
                    <textarea v-model="coachNotes.technique" class="w-full bg-rl-bg border border-rl-border text-xs text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition h-20" placeholder="e.g. Langkah kaki terlalu lebar, ayunan lengan stabil"></textarea>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Rekomendasi Latihan Berikutnya</label>
                    <input v-model="coachNotes.recommendation" type="text" class="w-full bg-rl-bg border border-rl-border text-xs text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="e.g. Fokus pada cadence di repetisi awal">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-rl-textMuted uppercase tracking-wider block mb-1">Catatan Cedera Ringan (Jika Ada)</label>
                    <input v-model="coachNotes.injury" type="text" class="w-full bg-rl-bg border border-rl-border text-xs text-rl-text p-3 rounded-xl focus:border-rl-lime focus:outline-none transition" placeholder="e.g. Nyeri lutut kanan ringan / Tidak ada">
                </div>
            </div>

            <button @click="saveCoachNotes" class="w-full bg-rl-lime hover:bg-lime-400 text-rl-bg font-extrabold py-3.5 rounded-2xl transition">SIMPAN CATATAN</button>
        </div>
    </div>

</div>

<script>
    const { createApp, ref, reactive, computed, onMounted, nextTick } = Vue;

    createApp({
        setup() {
            // Views
            const view = ref('setup');
            const hasStarted = ref(false);
            const isPaused = ref(false);
            const ttsEnabled = ref(false);
            const elapsedTime = ref(0);

            let timerInterval = null;
            let lastTime = 0;

            const selectedPresetName = ref('10 x 400 m');

            const presets = [
                { name: '10 x 400 m', reps: 10, distance: 400, rest: 60, pace: '04:00', target: 96 },
                { name: '6 x 800 m', reps: 6, distance: 800, rest: 90, pace: '04:00', target: 192 },
                { name: '5 x 1 km', reps: 5, distance: 1000, rest: 120, pace: '04:00', target: 240 },
                { name: '3 x 2 km', reps: 3, distance: 2000, rest: 180, pace: '04:00', target: 480 },
                { name: '30 menit tempo run', reps: 1, distance: 6000, rest: 0, pace: '05:00', target: 1800 },
                { name: '5K time trial', reps: 1, distance: 5000, rest: 0, pace: '04:30', target: 1350 },
                { name: 'Cooper test 12 menit', reps: 1, distance: 3000, rest: 0, pace: '04:00', target: 720 },
                { name: 'Custom interval', reps: 5, distance: 400, rest: 60, pace: '04:00', target: 96 }
            ];

            const program = reactive({
                name: 'Sesi Latihan Interval Sore',
                location: '',
                weather: 'Berawan',
                reps: 10,
                distance: 400,
                rest: 60,
                targetPace: '04:00',
                targetTime: 96
            });

            // Initial setup athletes
            const setupAthletes = ref([
                { name: 'Dika', readiness: 'green', target: 96, targetPace: '04:00', rest: 60 },
                { name: 'Rian', readiness: 'green', target: 96, targetPace: '04:00', rest: 60 }
            ]);

            // Running session state
            const athletes = ref([]);
            const logs = ref([]);
            const athleteAnalysis = ref([]);

            // Historical data
            const sessionHistory = ref([]);
            const selectedHistorySession = ref(null);

            // Coach notes modal
            const showNotesModal = ref(false);
            const activeAthleteForNotes = ref(null);
            const coachNotes = reactive({
                condition: 'Fresh',
                technique: 'Good alignment and running posture.',
                recommendation: 'Keep standard target pace.',
                injury: 'Tidak ada'
            });

            // Voice cues assistant
            const voiceCues = {
                start: 'Sesi latihan dimulai. Bersiaplah.',
                lapCompleted: (name, rep, time, status) => `${name} lap ${rep}, ${time} detik, ${status}`,
                restStarted: (name, restTime) => `${name} istirahat ${restTime} detik.`,
                restFinished: (name, rep) => `${name}, repetisi ${rep}. Mulai!`,
                completed: (name) => `${name} selesai.`
            };

            // Recalculations helpers
            const paceToSeconds = (paceStr) => {
                if (!paceStr || !paceStr.includes(':')) return 0;
                const parts = paceStr.split(':');
                return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
            };

            const secondsToPace = (seconds) => {
                const m = Math.floor(seconds / 60);
                const s = Math.round(seconds % 60);
                return `${m}:${s.toString().padStart(2, '0')}`;
            };

            const recalculateTargetTimeFromPace = () => {
                const secs = paceToSeconds(program.targetPace);
                if (secs > 0 && program.distance > 0) {
                    program.targetTime = Math.round((secs / 1000) * program.distance);
                }
                syncTargetTimeToAthletes();
            };

            const recalculateTargetPaceFromTime = () => {
                if (program.targetTime > 0 && program.distance > 0) {
                    const paceSecs = (program.targetTime / program.distance) * 1000;
                    program.targetPace = secondsToPace(paceSecs);
                }
                syncTargetTimeToAthletes();
            };

            const recalculateAllTargetsFromPace = () => {
                recalculateTargetTimeFromPace();
            };

            const syncTargetTimeToAthletes = () => {
                setupAthletes.value.forEach(a => {
                    a.target = program.targetTime;
                    a.targetPace = program.targetPace;
                });
            };

            const syncRestTimeToAthletes = () => {
                setupAthletes.value.forEach(a => {
                    a.rest = program.rest;
                });
            };

            const onSetupAthleteTargetTimeChange = (a) => {
                if (a.target > 0 && program.distance > 0) {
                    const paceSecs = (a.target / program.distance) * 1000;
                    a.targetPace = secondsToPace(paceSecs);
                }
            };

            // Preset Selection Handler
            const selectPreset = (p) => {
                selectedPresetName.value = p.name;
                program.reps = p.reps;
                program.distance = p.distance;
                program.rest = p.rest;
                program.targetPace = p.pace;
                program.targetTime = p.target;
                
                syncTargetTimeToAthletes();
                syncRestTimeToAthletes();
            };

            const addSetupAthlete = () => {
                setupAthletes.value.push({
                    name: '',
                    readiness: 'green',
                    target: program.targetTime,
                    targetPace: program.targetPace,
                    rest: program.rest
                });
            };

            const removeSetupAthlete = (index) => {
                setupAthletes.value.splice(index, 1);
            };

            // Timer Loop Controls
            const startSession = () => {
                const valid = setupAthletes.value.filter(a => a.name.trim() !== '');
                if (valid.length === 0) {
                    alert('Masukkan minimal satu nama atlet untuk memulai.');
                    return;
                }

                athletes.value = valid.map(a => ({
                    name: a.name,
                    readiness: a.readiness,
                    target: a.target || program.targetTime,
                    targetPace: a.targetPace || program.targetPace,
                    restTime: a.rest !== undefined ? a.rest : program.rest,
                    status: 'active',
                    lapStartElapsedTime: 0,
                    restCountdown: 0,
                    laps: [],
                    notes: {
                        condition: a.readiness === 'green' ? 'Prima' : (a.readiness === 'yellow' ? 'Lelah' : 'Cedera ringan'),
                        technique: 'Langkah kaki berirama, posisi lengan rileks.',
                        recommendation: 'Pertahankan pace target.',
                        injury: 'Tidak ada'
                    }
                }));

                logs.value = [];
                elapsedTime.value = 0;
                hasStarted.value = false;
                isPaused.value = false;
                view.value = 'track';

                saveActiveSession();
            };

            const startTimerNow = () => {
                hasStarted.value = true;
                athletes.value.forEach(a => {
                    a.lapStartElapsedTime = 0;
                });
                runTimer();
                speak(voiceCues.start);
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

                    // Check rest timer count downs
                    athletes.value.forEach(a => {
                        if (a.status === 'resting') {
                            a.restCountdown -= delta;
                            if (a.restCountdown <= 0) {
                                a.status = 'active';
                                a.lapStartElapsedTime = elapsedTime.value;
                                a.restCountdown = 0;
                                speak(voiceCues.restFinished(a.name, a.laps.length + 1));
                            }
                        }
                    });
                }, 50);
            };

            const togglePause = () => {
                if (!hasStarted.value) return;
                isPaused.value = !isPaused.value;
                speak(isPaused.value ? 'Pause' : 'Mulai kembali');
                saveActiveSession();
            };

            // Lap/Split recorder
            const recordLap = (index) => {
                if (!hasStarted.value || isPaused.value) return;
                const a = athletes.value[index];
                if (a.status !== 'active') return;

                const nowTime = elapsedTime.value;
                const splitVal = nowTime - a.lapStartElapsedTime;
                const cumulativeVal = a.laps.reduce((sum, l) => sum + l.time, 0) + splitVal;
                const repNum = a.laps.length + 1;
                const paceStr = calculatePace(splitVal, program.distance);
                const diffVal = splitVal - a.target;

                // Delta Status
                let statusVal = 'on target';
                let deltaText = 'sesuai target';
                if (Math.abs(diffVal) <= 1.5) {
                    statusVal = 'on target';
                    deltaText = 'sesuai target';
                } else if (diffVal < -1.5) {
                    statusVal = 'too fast';
                    deltaText = `${Math.abs(Math.round(diffVal))} detik lebih cepat`;
                } else {
                    statusVal = 'too slow';
                    deltaText = `${Math.abs(Math.round(diffVal))} detik lebih lambat`;
                }

                // Fatigue Risk check (if split drops more than 5s compared to first two)
                if (a.laps.length >= 3) {
                    const baseAvg = (a.laps[0].time + a.laps[1].time) / 2;
                    if (splitVal > baseAvg + 5.0) {
                        statusVal = 'fatigue risk';
                        deltaText += ' (fatigue risk)';
                    }
                }

                const newLap = {
                    rep: repNum,
                    time: splitVal,
                    cumulative: cumulativeVal,
                    pace: paceStr,
                    diff: diffVal,
                    status: statusVal,
                    deltaText: deltaText
                };

                a.laps.push(newLap);

                logs.value.push({
                    athleteName: a.name,
                    rep: repNum,
                    time: splitVal,
                    pace: paceStr,
                    diff: diffVal,
                    status: statusVal,
                    cumulative: cumulativeVal
                });

                speak(voiceCues.lapCompleted(a.name, repNum, Math.round(splitVal), deltaText));

                if (repNum >= program.reps) {
                    a.status = 'completed';
                    speak(voiceCues.completed(a.name));
                } else {
                    a.status = 'resting';
                    a.restCountdown = a.restTime;
                }

                saveActiveSession();
            };

            const skipRestForAthlete = (index) => {
                const a = athletes.value[index];
                if (a.status !== 'resting') return;
                a.status = 'active';
                a.lapStartElapsedTime = elapsedTime.value;
                a.restCountdown = 0;
                speak(`Mulai repetisi ${a.laps.length + 1}`);
            };

            const finishSession = () => {
                if (confirm('Selesaikan sesi latihan ini dan buat analisis performa?')) {
                    clearInterval(timerInterval);
                    generateAnalysis();
                    view.value = 'summary';
                    saveActiveSession(true);
                    saveSessionToHistory();
                    
                    nextTick(() => {
                        renderCharts();
                    });
                }
            };

            const resetSession = () => {
                if (confirm('Mulai sesi latihan baru? Data aktif saat ini akan dibersihkan.')) {
                    clearInterval(timerInterval);
                    elapsedTime.value = 0;
                    athletes.value = [];
                    logs.value = [];
                    athleteAnalysis.value = [];
                    hasStarted.value = false;
                    isPaused.value = false;
                    localStorage.removeItem('trackmaster_active_session');
                    view.value = 'setup';
                }
            };

            // Audio Web Speech Helper
            const speak = (txt) => {
                if (!ttsEnabled.value) return;
                try {
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                        const u = new SpeechSynthesisUtterance(txt);
                        u.lang = 'id-ID';
                        u.rate = 1.15;
                        window.speechSynthesis.speak(u);
                    }
                } catch (e) {
                    console.error('TTS Error: ', e);
                }
            };

            // Performance Analytics & Insights
            const computeAthleteStats = (laps, targetVal) => {
                if (!laps || laps.length === 0) {
                    return { avgTime: 0, fastest: 0, slowest: 0, stdDev: 0, consistency: 0, avgPace: '-', totalTime: 0 };
                }
                const times = laps.map(l => l.time);
                const totalTime = times.reduce((acc, t) => acc + t, 0);
                const avgTime = totalTime / laps.length;
                const fastest = Math.min(...times);
                const slowest = Math.max(...times);

                // Standard deviation
                const mean = avgTime;
                const variance = times.reduce((acc, t) => acc + Math.pow(t - mean, 2), 0) / times.length;
                const stdDev = Math.sqrt(variance);
                const cv = mean > 0 ? (stdDev / mean) * 100 : 0;
                const consistency = Math.max(0, Math.min(100, Math.round(100 - cv)));

                return {
                    avgTime,
                    fastest,
                    slowest,
                    stdDev,
                    consistency,
                    avgPace: calculatePace(avgTime, program.distance),
                    totalTime
                };
            };

            const getAthleteFeedback = (a, stats) => {
                if (!a.laps || a.laps.length === 0) return "Tidak ada repetisi yang diselesaikan.";
                let feedback = "";

                if (stats.consistency > 92) {
                    feedback += `${a.name} menunjukkan konsistensi pace sangat tinggi (skor ${stats.consistency}%). Strategi distribusi energi sangat baik dari awal hingga akhir. `;
                } else if (stats.consistency >= 80) {
                    feedback += `Konsistensi pace ${a.name} tergolong baik (skor ${stats.consistency}%). Transisi antar repetisi stabil. `;
                } else {
                    feedback += `Pace ${a.name} memiliki tingkat fluktuasi tinggi (skor ${stats.consistency}%). Disarankan menjaga tempo agar lebih tenang di repetisi awal. `;
                }

                if (a.laps.length >= 4) {
                    const half = Math.ceil(a.laps.length / 2);
                    const firstHalf = a.laps.slice(0, half).map(l => l.time);
                    const secondHalf = a.laps.slice(half).map(l => l.time);
                    const avgFirst = firstHalf.reduce((acc, t) => acc + t, 0) / firstHalf.length;
                    const avgSecond = secondHalf.reduce((acc, t) => acc + t, 0) / secondHalf.length;

                    if (avgSecond > avgFirst + 2.5) {
                        feedback += `Terdapat gejala penurunan ketahanan (fatigue) signifikan di lap akhir. Evaluasi durasi istirahat (rest) atau kurangi intensitas target pace sesi berikutnya.`;
                    } else if (avgFirst > avgSecond + 2.5) {
                        feedback += `Grafik berprogres naik (negative split). Start terkontrol dengan penyelesaian lap akhir yang eksplosif.`;
                    } else {
                        feedback += `Pacing merata sepanjang interval (even split). Beban kerja optimal.`;
                    }
                }

                return feedback;
            };

            const generateAnalysis = () => {
                athleteAnalysis.value = athletes.value.map(a => {
                    const stats = computeAthleteStats(a.laps, a.target);
                    return {
                        name: a.name,
                        readiness: a.readiness,
                        stats: stats,
                        laps: a.laps,
                        feedback: getAthleteFeedback(a, stats),
                        notes: { ...a.notes }
                    };
                });
            };

            const renderCharts = () => {
                athleteAnalysis.value.forEach((a, index) => {
                    const canvasId = `chart_${index}`;
                    const ctx = document.getElementById(canvasId);
                    if (ctx) {
                        const existing = Chart.getChart(ctx);
                        if (existing) existing.destroy();

                        const athleteObj = athletes.value.find(x => x.name === a.name);
                        const targetVal = athleteObj ? athleteObj.target : program.targetTime;

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: a.laps.map(l => `Rep ${l.rep}`),
                                datasets: [
                                    {
                                        label: 'Split Time (detik)',
                                        data: a.laps.map(l => l.time),
                                        borderColor: '#B8FF00',
                                        backgroundColor: 'rgba(184, 255, 0, 0.08)',
                                        borderWidth: 2,
                                        tension: 0.15,
                                        pointBackgroundColor: '#B8FF00',
                                        pointRadius: 4,
                                        fill: true
                                    },
                                    {
                                        label: 'Target',
                                        data: Array(a.laps.length).fill(targetVal),
                                        borderColor: '#EF4444',
                                        borderWidth: 1.5,
                                        borderDash: [5, 5],
                                        pointRadius: 0,
                                        fill: false
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        grid: { color: '#1F2D44' },
                                        ticks: { color: '#94A3B8', font: { family: 'JetBrains Mono' } }
                                    },
                                    x: {
                                        grid: { color: '#1F2D44' },
                                        ticks: { color: '#94A3B8' }
                                    }
                                }
                            }
                        });
                    }
                });
            };

            // History detail chart rendering helper
            const renderHistoryCharts = (session) => {
                session.athletes.forEach((a, index) => {
                    const canvasId = `history_chart_${index}`;
                    const ctx = document.getElementById(canvasId);
                    if (ctx) {
                        const existing = Chart.getChart(ctx);
                        if (existing) existing.destroy();

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: a.laps.map(l => `Rep ${l.rep}`),
                                datasets: [
                                    {
                                        label: 'Split Time',
                                        data: a.laps.map(l => l.time),
                                        borderColor: '#B8FF00',
                                        backgroundColor: 'rgba(184, 255, 0, 0.08)',
                                        borderWidth: 2,
                                        tension: 0.15,
                                        pointBackgroundColor: '#B8FF00',
                                        pointRadius: 4,
                                        fill: true
                                    },
                                    {
                                        label: 'Target',
                                        data: Array(a.laps.length).fill(a.target),
                                        borderColor: '#EF4444',
                                        borderWidth: 1.5,
                                        borderDash: [5, 5],
                                        pointRadius: 0,
                                        fill: false
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        grid: { color: '#1F2D44' },
                                        ticks: { color: '#94A3B8' }
                                    },
                                    x: {
                                        grid: { color: '#1F2D44' },
                                        ticks: { color: '#94A3B8' }
                                    }
                                }
                            }
                        });
                    }
                });
            };

            // Formatting helpers
            const formatTime = (s) => {
                const m = Math.floor(s / 60);
                const sec = Math.floor(s % 60);
                const ms = Math.floor((s % 1) * 10);
                return `${m.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}.${ms}`;
            };

            const calculatePace = (timeSecs, distanceMeters) => {
                if (!timeSecs || !distanceMeters) return '-';
                const totalMinutes = (timeSecs / distanceMeters) * 1000 / 60;
                const mins = Math.floor(totalMinutes);
                const secs = Math.round((totalMinutes - mins) * 60);
                return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            };

            const calculateAvg = () => {
                if (logs.value.length === 0) return '0.0';
                return (logs.value.reduce((acc, l) => acc + l.time, 0) / logs.value.length).toFixed(1);
            };

            const formatAvgPaceOfSession = (session = null) => {
                const targetLogs = session ? session.logs : logs.value;
                const distanceVal = session ? session.program.distance : program.distance;
                if (targetLogs.length === 0) return '-';
                const total = targetLogs.reduce((acc, l) => acc + l.time, 0);
                const avg = total / targetLogs.length;
                return calculatePace(avg, distanceVal) + ' / km';
            };

            const getAthleteActiveTimerFormatted = (a) => {
                if (a.status === 'resting') return `Rest: ${Math.ceil(a.restCountdown)}s`;
                if (a.status === 'completed') return 'Selesai';
                const elapsed = elapsedTime.value - a.lapStartElapsedTime;
                return formatTime(elapsed > 0 ? elapsed : 0);
            };

            const formatAthleteStatusText = (a) => {
                if (a.status === 'completed') return 'Selesai';
                if (a.status === 'resting') return 'Istirahat';
                if (a.laps.length === 0) return 'Running';
                
                const lastLap = a.laps[a.laps.length - 1];
                if (lastLap.status === 'on target') return 'On Target';
                if (lastLap.status === 'too fast') return 'Too Fast';
                if (lastLap.status === 'too slow') return 'Too Slow';
                return 'Running';
            };

            const getAthleteStatusBadgeClass = (a) => {
                if (a.status === 'completed') return 'border-rl-border text-rl-textMuted bg-rl-bg/40';
                if (a.status === 'resting') return 'border-rl-warning/30 text-rl-warning bg-rl-warning/10';
                if (a.laps.length === 0) return 'border-rl-lime/30 text-rl-lime bg-rl-lime/10';
                
                const lastLap = a.laps[a.laps.length - 1];
                if (lastLap.status === 'on target') return 'border-rl-lime/30 text-rl-lime bg-rl-lime/10';
                if (lastLap.status === 'too fast') return 'border-rl-lime/30 text-cyan-400 bg-cyan-400/10';
                if (lastLap.status === 'too slow') return 'border-rl-danger/30 text-rl-danger bg-rl-danger/10';
                return 'border-rl-border text-rl-text';
            };

            const getDeltaClass = (d) => {
                if (Math.abs(d) <= 1.5) return 'text-rl-lime';
                return d < -1.5 ? 'text-cyan-400' : 'text-rl-danger';
            };

            // Coach notes edit
            const openNotesModalForAthlete = (athlete) => {
                activeAthleteForNotes.value = athlete;
                Object.assign(coachNotes, { ...athlete.notes });
                showNotesModal.value = true;
            };

            const saveCoachNotes = () => {
                if (activeAthleteForNotes.value) {
                    Object.assign(activeAthleteForNotes.value.notes, { ...coachNotes });
                    showNotesModal.value = false;
                    saveActiveSession();
                    if (view.value === 'summary') {
                        generateAnalysis();
                    }
                }
            };

            // LocalStorage Active Session Persistence
            const saveActiveSession = (isCompleted = false) => {
                const activeSession = {
                    hasStarted: hasStarted.value,
                    completed: isCompleted,
                    program: { ...program },
                    athletes: athletes.value.map(a => ({
                        name: a.name,
                        readiness: a.readiness,
                        target: a.target,
                        targetPace: a.targetPace,
                        restTime: a.restTime,
                        status: a.status,
                        lapStartElapsedTime: a.lapStartElapsedTime,
                        restCountdown: a.restCountdown,
                        laps: a.laps,
                        notes: a.notes
                    })),
                    logs: logs.value,
                    elapsedTime: elapsedTime.value
                };
                localStorage.setItem('trackmaster_active_session', JSON.stringify(activeSession));
            };

            // Session History Persistence
            const saveSessionToHistory = () => {
                const dateObj = new Date();
                const sessionRecord = {
                    id: Date.now(),
                    name: program.name || `Sesi Interval`,
                    date: dateObj.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
                    time: dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                    location: program.location || 'Stadion / Track',
                    workoutType: selectedPresetName.value,
                    program: { ...program, elapsedTime: elapsedTime.value },
                    athletes: athletes.value.map(a => ({
                        name: a.name,
                        readiness: a.readiness,
                        target: a.target,
                        targetPace: a.targetPace,
                        restTime: a.restTime,
                        laps: a.laps,
                        notes: { ...a.notes }
                    })),
                    logs: [...logs.value]
                };

                sessionHistory.value.unshift(sessionRecord);
                localStorage.setItem('trackmaster_history', JSON.stringify(sessionHistory.value));
            };

            const clearAllHistory = () => {
                if (confirm('Hapus seluruh riwayat sesi latihan? Tindakan ini tidak dapat dibatalkan.')) {
                    sessionHistory.value = [];
                    localStorage.removeItem('trackmaster_history');
                }
            };

            const deleteHistorySession = (id) => {
                if (confirm('Hapus sesi latihan ini dari riwayat?')) {
                    sessionHistory.value = sessionHistory.value.filter(s => s.id !== id);
                    localStorage.setItem('trackmaster_history', JSON.stringify(sessionHistory.value));
                }
            };

            const viewHistorySession = (session) => {
                selectedHistorySession.value = session;
                view.value = 'history_detail';
                nextTick(() => {
                    renderHistoryCharts(session);
                });
            };

            // History stats helpers
            const getAthleteAvgTime = (a) => {
                if (!a.laps || a.laps.length === 0) return 0;
                return a.laps.reduce((acc, l) => acc + l.time, 0) / a.laps.length;
            };

            const getAthleteAvgPace = (a) => {
                const avg = getAthleteAvgTime(a);
                return calculatePace(avg, program.distance);
            };

            const getAthleteConsistencyScore = (a) => {
                if (!a.laps || a.laps.length <= 1) return 100;
                const times = a.laps.map(l => l.time);
                const mean = times.reduce((acc, t) => acc + t, 0) / times.length;
                const variance = times.reduce((acc, t) => acc + Math.pow(t - mean, 2), 0) / times.length;
                const stdDev = Math.sqrt(variance);
                const cv = mean > 0 ? (stdDev / mean) * 100 : 0;
                return Math.max(0, Math.min(100, Math.round(100 - cv)));
            };

            const getHistoryAthleteFeedback = (a, prog) => {
                const avgVal = getAthleteAvgTime(a);
                const consistencyVal = getAthleteConsistencyScore(a);
                const distanceVal = prog ? prog.distance : program.distance;

                let feedback = "";
                if (consistencyVal > 92) {
                    feedback += `${a.name} berlari sangat stabil (Consistency: ${consistencyVal}%). Ritme terjaga sempurna. `;
                } else if (consistencyVal >= 80) {
                    feedback += `Transisi pace ${a.name} stabil (Consistency: ${consistencyVal}%). Distribusi energi terkontrol. `;
                } else {
                    feedback += `Terdeteksi fluktuasi ritme yang tinggi (Consistency: ${consistencyVal}%). Perlu perbaikan kontrol pacing di lap awal. `;
                }

                if (a.laps && a.laps.length >= 4) {
                    const half = Math.ceil(a.laps.length / 2);
                    const first = a.laps.slice(0, half).map(l => l.time);
                    const second = a.laps.slice(half).map(l => l.time);
                    const avgFirst = first.reduce((acc, t) => acc + t, 0) / first.length;
                    const avgSecond = second.reduce((acc, t) => acc + t, 0) / second.length;

                    if (avgSecond > avgFirst + 2.5) {
                        feedback += `Terdapat penurunan daya tahan pada repetisi akhir. Evaluasi ulang kesiapan fisik atau tambahkan interval rest.`;
                    } else if (avgFirst > avgSecond + 2.5) {
                        feedback += `Berhasil melakukan negative split. Lap akhir diselesaikan dengan peningkatan pace.`;
                    } else {
                        feedback += `Kecepatan relatif merata (even split). Transisi tenaga optimal.`;
                    }
                }

                return feedback;
            };

            // Clipboard Share and Export options
            const copySummary = (session = null) => {
                const target = session || {
                    name: program.name,
                    date: new Date().toLocaleDateString('id-ID'),
                    location: program.location || 'Stadion / Track',
                    workoutType: selectedPresetName.value,
                    program: { ...program },
                    athletes: athletes.value.map(a => ({
                        name: a.name,
                        target: a.target,
                        laps: a.laps,
                        notes: a.notes,
                        readiness: a.readiness
                    }))
                };

                let text = `*TRACKMASTER TRAINING REPORT*\n`;
                text += `Sesi: ${target.name}\n`;
                text += `Tanggal: ${target.date}\n`;
                text += `Lokasi: ${target.location}\n`;
                text += `Latihan: ${target.workoutType} (${target.program.reps}x${target.program.distance}m)\n\n`;

                target.athletes.forEach(a => {
                    const stats = computeAthleteStats(a.laps, a.target);
                    text += `*Atlet: ${a.name}*\n`;
                    text += `- Kesiapan: ${a.readiness === 'green' ? '🟢 Siap' : (a.readiness === 'yellow' ? '🟡 Lelah' : '🔴 Cedera')}\n`;
                    text += `- Avg Pace: ${stats.avgPace} / km\n`;
                    text += `- Avg Time: ${stats.avgTime.toFixed(1)}s\n`;
                    text += `- Konsistensi: ${stats.consistency}%\n`;
                    text += `- Evaluasi: ${a.notes.technique || 'N/A'}\n`;
                    text += `- Rekomendasi: ${a.notes.recommendation || 'N/A'}\n\n`;
                });

                navigator.clipboard.writeText(text).then(() => {
                    alert('Ringkasan sesi disalin ke clipboard!');
                });
            };

            const shareWhatsApp = (session = null) => {
                const target = session || {
                    name: program.name,
                    date: new Date().toLocaleDateString('id-ID'),
                    workoutType: selectedPresetName.value,
                    program: { ...program },
                    athletes: athletes.value.map(a => ({
                        name: a.name,
                        target: a.target,
                        laps: a.laps,
                        notes: a.notes
                    }))
                };

                let text = `*TRACKMASTER REPORT - ${target.name}*\n`;
                text += `Tanggal: ${target.date}\n`;
                text += `Tipe: ${target.workoutType} (${target.program.reps}x${target.program.distance}m)\n\n`;

                target.athletes.forEach(a => {
                    const stats = computeAthleteStats(a.laps, a.target);
                    text += `*${a.name}* -> Avg Pace: ${stats.avgPace} (Consistency: ${stats.consistency}%)\n`;
                    text += `Rekomendasi: ${a.notes.recommendation || 'N/A'}\n\n`;
                });

                text += `Detail lengkap latihan lari mandiri di TrackMaster.`;
                const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
                window.open(url, '_blank');
            };

            const exportCSV = (session = null) => {
                const target = session || {
                    name: program.name,
                    athletes: athletes.value.map(a => ({
                        name: a.name,
                        target: a.target,
                        laps: a.laps,
                        notes: a.notes
                    }))
                };

                let csv = "Atlet,Rep,Waktu Lap (s),Target (s),Pace,Selisih (s),Status,Kondisi,Teknik,Rekomendasi\n";
                target.athletes.forEach(a => {
                    a.laps.forEach(l => {
                        csv += `"${a.name}",${l.rep},${l.time.toFixed(2)},${a.target},"${l.pace}",${l.diff.toFixed(2)},"${l.status}","${a.notes.condition}","${a.notes.technique}","${a.notes.recommendation}"\n`;
                    });
                });

                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.setAttribute("download", `trackmaster_${target.name.toLowerCase().replace(/\s+/g, '_')}_${Date.now()}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            const printPDF = () => {
                window.print();
            };

            const changeView = (newView) => {
                view.value = newView;
                if (newView === 'summary') {
                    nextTick(() => {
                        renderCharts();
                    });
                }
            };

            onMounted(() => {
                // Load history list from localstorage
                const saved = localStorage.getItem('trackmaster_history');
                if (saved) {
                    sessionHistory.value = JSON.parse(saved);
                }

                // Check active session
                const active = localStorage.getItem('trackmaster_active_session');
                if (active) {
                    const parsed = JSON.parse(active);
                    if (parsed.hasStarted && !parsed.completed) {
                        if (confirm('Ditemukan sesi latihan aktif yang belum selesai. Lanjutkan sesi?')) {
                            Object.assign(program, parsed.program);
                            athletes.value = parsed.athletes;
                            logs.value = parsed.logs;
                            elapsedTime.value = parsed.elapsedTime;
                            isPaused.value = true;
                            hasStarted.value = true;
                            view.value = 'track';
                            runTimer();
                        } else {
                            localStorage.removeItem('trackmaster_active_session');
                        }
                    }
                }
            });

            return {
                view,
                hasStarted,
                isPaused,
                ttsEnabled,
                elapsedTime,
                selectedPresetName,
                presets,
                program,
                setupAthletes,
                athletes,
                logs,
                athleteAnalysis,
                sessionHistory,
                selectedHistorySession,
                showNotesModal,
                activeAthleteForNotes,
                coachNotes,
                
                selectPreset,
                addSetupAthlete,
                removeSetupAthlete,
                startSession,
                startTimerNow,
                togglePause,
                recordLap,
                skipRestForAthlete,
                finishSession,
                resetSession,
                openNotesModalForAthlete,
                saveCoachNotes,
                clearAllHistory,
                deleteHistorySession,
                viewHistorySession,
                copySummary,
                shareWhatsApp,
                exportCSV,
                printPDF,
                changeView,
                
                // Formatter helpers
                formatTime,
                calculatePace,
                calculateAvg,
                formatAvgPaceOfSession,
                getAthleteActiveTimerFormatted,
                formatAthleteStatusText,
                getAthleteStatusBadgeClass,
                getDeltaClass,
                getAthleteAvgTime,
                getAthleteAvgPace,
                getAthleteConsistencyScore,
                getHistoryAthleteFeedback,
                recalculateTargetTimeFromPace,
                recalculateTargetPaceFromTime,
                recalculateAllTargetsFromPace,
                syncRestTimeToAthletes,
                onSetupAthleteTargetTimeChange
            };
        }
    }).mount('#app');
</script>
</body>
</html>
