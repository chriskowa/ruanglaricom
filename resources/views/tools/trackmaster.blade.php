<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- SEO Halaman -->
    <title>TrackMaster | Stopwatch Interval dan Pace Tracker untuk Pelatih Lari</title>
    <meta name="description" content="Gunakan TrackMaster untuk mencatat split, pace, interval, target latihan, dan progres banyak atlet dalam satu sesi latihan lari.">
    <meta name="keywords" content="stopwatch lari, interval timer lari, pace tracker, split timer lari, tools pelatih lari, latihan interval lari, catatan latihan lari">
    <meta name="author" content="Ruang Lari">
    <meta name="robots" content="index, follow">

    <!-- Favicon -->
    <link class="favicon" rel="icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link class="favicon" rel="shortcut icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link class="favicon" rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link class="favicon" rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link class="favicon" rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/green/apple-touch-icon.png') }}">

    <meta name="theme-color" content="#08111F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="TrackMaster | Stopwatch Interval dan Pace Tracker untuk Pelatih Lari">
    <meta property="og:description" content="Gunakan TrackMaster untuk mencatat split, pace, interval, target latihan, dan progres banyak atlet dalam satu sesi latihan lari.">
    <meta property="og:image" content="{{ asset('images/logo-full.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="TrackMaster | Stopwatch Interval dan Pace Tracker">
    
    <!-- CDNs -->
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
                        tm: {
                            bg: 'var(--tm-bg)',
                            surface: 'var(--tm-surface)',
                            surface2: 'var(--tm-surface-2)',
                            border: 'var(--tm-border)',
                            text: 'var(--tm-text)',
                            muted: 'var(--tm-muted)',
                            primary: 'var(--tm-primary)',
                            primaryHover: 'var(--tm-primary-hover)',
                            primarySoft: 'var(--tm-primary-soft)',
                            warning: 'var(--tm-warning)',
                            danger: 'var(--tm-danger)',
                            success: 'var(--tm-success)'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* CSS Tokens System */
        :root {
            /* Light Theme Palette */
            --tm-bg: #F8FAFC;
            --tm-surface: #FFFFFF;
            --tm-surface-2: #F1F5F9;
            --tm-border: #CBD5E1;
            --tm-text: #0F172A;
            --tm-muted: #475569;
            --tm-primary: #10B981;
            --tm-primary-hover: #059669;
            --tm-primary-soft: #D1FAE5;
            --tm-warning: #F59E0B;
            --tm-danger: #EF4444;
            --tm-success: #10B981;
        }

        :root.dark {
            /* Dark Theme Palette */
            --tm-bg: #030712;
            --tm-surface: #080d1a;
            --tm-surface-2: #0f172a;
            --tm-border: #1e293b;
            --tm-text: #F8FAFC;
            --tm-muted: #94A3B8;
            --tm-primary: #10B981;
            --tm-primary-hover: #059669;
            --tm-primary-soft: rgba(16, 185, 129, 0.1);
            --tm-warning: #FACC15;
            --tm-danger: #EF4444;
            --tm-success: #10B981;
        }

        body { 
            background-color: var(--tm-bg); 
            color: var(--tm-text); 
            font-size: 16px;
            touch-action: manipulation; 
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        input, select, textarea {
            font-size: 16px !important;
        }
        [v-cloak] { display: none; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--tm-bg); }
        ::-webkit-scrollbar-thumb { background: var(--tm-border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--tm-primary); }

        /* Print Media Styles */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-card { background: white !important; border: 1px solid #ddd !important; color: black !important; box-shadow: none !important; }
            h1, h2, h3, h4, p, span, td, th { color: black !important; }
            canvas { max-height: 200px !important; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col font-sans bg-tm-bg text-tm-text">

<div id="app" v-cloak class="flex-grow flex flex-col min-h-screen max-w-5xl mx-auto w-full px-4 py-4 relative pb-28">

    <!-- Header Ringkas (no-print) -->
    <header class="no-print flex flex-col sm:flex-row items-stretch sm:items-center justify-between border-b border-tm-border pb-3 mb-5 gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('tools.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-tm-surface2 text-tm-muted hover:text-tm-primary border border-tm-border transition">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <div>
                <span class="text-lg font-black tracking-tight text-tm-text">TRACK<span class="text-tm-primary">MASTER</span></span>
                <p class="text-xs text-tm-muted leading-none">Stopwatch Interval Lari Mandiri</p>
            </div>
        </div>
        
        <div class="flex items-center justify-between sm:justify-end gap-1.5">
            <button @click="changeView('setup')" :class="view==='setup' ? 'bg-tm-primary text-tm-bg font-extrabold' : 'bg-tm-surface2 text-tm-muted border border-tm-border'" class="flex-1 sm:flex-none text-center px-3 py-2 rounded-xl text-xs font-bold uppercase transition">Setup</button>
            <button @click="changeView('track')" :class="view==='track' ? 'bg-tm-primary text-tm-bg font-extrabold' : 'bg-tm-surface2 text-tm-muted border border-tm-border'" class="flex-1 sm:flex-none text-center px-3 py-2 rounded-xl text-xs font-bold uppercase transition" :disabled="!hasStarted">Track</button>
            <button @click="changeView('summary')" :class="view==='summary' ? 'bg-tm-primary text-tm-bg font-extrabold' : 'bg-tm-surface2 text-tm-muted border border-tm-border'" class="flex-1 sm:flex-none text-center px-3 py-2 rounded-xl text-xs font-bold uppercase transition" :disabled="logs.length === 0">Summary</button>
            <button @click="changeView('history')" :class="view==='history' ? 'bg-tm-primary text-tm-bg font-extrabold' : 'bg-tm-surface2 text-tm-muted border border-tm-border'" class="flex-1 sm:flex-none text-center px-3 py-2 rounded-xl text-xs font-bold uppercase transition">History</button>
            
            <!-- Theme Switcher Toggle -->
            <button @click="toggleTheme" class="w-10 h-10 flex items-center justify-center rounded-xl bg-tm-surface2 text-tm-muted hover:text-tm-primary border border-tm-border transition" title="Toggle Theme">
                <i class="fa-solid" :class="activeTheme === 'dark' ? 'fa-moon' : 'fa-sun'"></i>
            </button>
        </div>
    </header>

    <!-- SECTION 1: SETUP SESSION -->
    <section v-if="view === 'setup'" class="flex-grow space-y-6 animate-[fadeIn_0.2s]">
        <div class="bg-tm-surface border border-tm-border p-5 rounded-2xl">
            <!-- H1 & Intro -->
            <div class="mb-5">
                <h1 class="text-xl font-extrabold text-tm-text tracking-tight">TrackMaster: Stopwatch Interval untuk Pelatih Lari</h1>
                <p class="text-sm text-tm-muted mt-1 leading-relaxed">
                    TrackMaster membantu pelatih dan komunitas lari mencatat split, pace, interval, target latihan, dan progres banyak atlet dalam satu sesi latihan lari.
                </p>
            </div>

            <!-- Step 1: Detail Sesi -->
            <div class="border-b border-tm-border pb-4 mb-4">
                <h2 class="text-sm font-bold text-tm-primary uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-tm-primary text-tm-bg flex items-center justify-center font-extrabold text-[10px]">1</span>
                    Konfigurasi Latihan & Sesi
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Nama Sesi Latihan</label>
                        <input v-model="program.name" type="text" class="w-full h-11 bg-tm-surface2 border border-tm-border text-base text-tm-text px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. Sesi Interval Selasa Sore">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Lokasi Latihan (Opsional)</label>
                        <input v-model="program.location" type="text" class="w-full h-11 bg-tm-surface2 border border-tm-border text-base text-tm-text px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. Stadion Lapangan Atletik">
                    </div>
                </div>
            </div>

            <!-- Step 2: Pilih Preset Interval -->
            <div class="border-b border-tm-border pb-4 mb-4">
                <h2 class="text-sm font-bold text-tm-primary uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-tm-primary text-tm-bg flex items-center justify-center font-extrabold text-[10px]">2</span>
                    Pilih Preset Interval
                </h2>
                
                <div class="flex gap-2 overflow-x-auto pb-2 scroll-smooth">
                    <button v-for="p in presets" :key="p.name" @click="selectPreset(p)"
                        :class="selectedPresetName === p.name ? 'border-tm-primary text-tm-primary bg-tm-surface2' : 'border-tm-border text-tm-muted bg-tm-bg'"
                        class="shrink-0 text-left p-3 rounded-xl border transition-all flex flex-col justify-between min-w-[130px]">
                        <span class="text-xs font-bold text-tm-text leading-tight mb-1">@{{ p.name }}</span>
                        <span class="text-[10px] font-mono opacity-85">@{{ p.reps }}x @{{ p.distance }}m</span>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Repetisi</label>
                        <input v-model.number="program.reps" type="number" min="1" class="w-full h-11 bg-tm-surface2 border border-tm-border text-center font-mono text-base text-tm-text rounded-xl focus:border-tm-primary focus:outline-none transition">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Jarak (m)</label>
                        <input v-model.number="program.distance" type="number" min="1" class="w-full h-11 bg-tm-surface2 border border-tm-border text-center font-mono text-base text-tm-text rounded-xl focus:border-tm-primary focus:outline-none transition" @change="recalculateAllTargetsFromPace">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Rest (detik)</label>
                        <input v-model.number="program.rest" type="number" min="0" class="w-full h-11 bg-tm-surface2 border border-tm-border text-center font-mono text-base text-tm-text rounded-xl focus:border-tm-primary focus:outline-none transition" @change="syncRestTimeToAthletes">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Target Pace (Min:Det /km)</label>
                        <input v-model="program.targetPace" type="text" class="w-full h-11 bg-tm-surface2 border border-tm-border text-center font-mono text-base text-tm-text rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. 04:00" @input="recalculateTargetTimeFromPace">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-tm-muted uppercase block mb-1">Target Lap (detik)</label>
                        <input v-model.number="program.targetTime" type="number" min="0" class="w-full h-11 bg-tm-surface2 border border-tm-border text-center font-mono text-base text-tm-text rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. 96" @input="recalculateTargetPaceFromTime">
                    </div>
                </div>
            </div>

            <!-- Step 3: Tambah Atlet -->
            <div class="pb-4">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-sm font-bold text-tm-primary uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-tm-primary text-tm-bg flex items-center justify-center font-extrabold text-[10px]">3</span>
                        Daftar Atlet Latihan
                    </h2>
                    <button @click="addSetupAthlete" class="text-tm-primary hover:text-tm-primaryHover text-xs font-bold flex items-center gap-1">
                        <i class="fa-solid fa-plus-circle"></i> Tambah Atlet
                    </button>
                </div>

                <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-1">
                    <div v-for="(a, i) in setupAthletes" :key="i" class="bg-tm-surface2 border border-tm-border p-3.5 rounded-xl relative hover:border-tm-primary/30 transition-all duration-200">
                        <!-- Absolute positioned delete button -->
                        <button @click="removeSetupAthlete(i)" class="absolute top-3.5 right-3.5 w-8 h-8 bg-tm-danger/10 hover:bg-tm-danger text-tm-danger hover:text-tm-bg rounded-xl transition border border-tm-danger/20 flex items-center justify-center z-10" title="Hapus Atlet">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pr-8 sm:pr-0">
                            <div class="sm:col-span-6">
                                <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Nama Atlet</label>
                                <input v-model="a.name" class="w-full h-10 bg-tm-surface2 border border-tm-border text-sm text-tm-text font-bold px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="Masukkan nama atlet">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Target Waktu (s)</label>
                                <input v-model.number="a.target" type="number" class="w-full h-10 bg-tm-surface2 border border-tm-border text-center font-mono text-sm text-tm-text px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" @input="onSetupAthleteTargetTimeChange(a)">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Kesiapan</label>
                                <select v-model="a.readiness" class="w-full h-10 bg-tm-surface2 border border-tm-border text-xs text-tm-text font-bold px-2 rounded-xl focus:border-tm-primary focus:outline-none transition">
                                    <option value="green">🟢 Siap</option>
                                    <option value="yellow">🟡 Lelah</option>
                                    <option value="red">🔴 Cedera</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voice cue toggle & start button -->
            <div class="mt-4 pt-4 border-t border-tm-border flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="ttsEnabled" class="sr-only peer">
                        <div class="w-10 h-6 bg-tm-surface2 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-tm-muted after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-tm-primary peer-checked:after:bg-tm-bg"></div>
                    </label>
                    <div>
                        <span class="text-xs font-bold text-tm-text block">Voice Assistant Suara</span>
                        <span class="text-[10px] text-tm-muted">Panduan rest & target selesai via browser audio</span>
                    </div>
                </div>

                <!-- Inline button for desktop -->
                <button @click="startSession" class="hidden sm:flex h-12 px-8 bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-extrabold rounded-xl transition transform active:scale-95 items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> MULAI SESI LATIHAN
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 2: LIVE SESSION SCREEN -->
    <section v-if="view === 'track'" class="flex-grow flex flex-col lg:grid lg:grid-cols-12 lg:gap-6 animate-[fadeIn_0.2s]">
        
        <!-- Main Panel: Giant Timer & Active Runner Cards -->
        <div class="lg:col-span-8 flex flex-col space-y-6">
            
            <!-- Outdoor High Contrast Timer Card -->
            <div @click="!hasStarted ? startTimerNow() : togglePause()" 
                class="bg-tm-surface border border-tm-border p-5 rounded-2xl text-center shadow-lg relative overflow-hidden cursor-pointer hover:border-tm-primary/50 transition">
                <span class="text-[10px] text-tm-muted font-mono uppercase tracking-widest block mb-1">Sesi: @{{ program.name }}</span>
                
                <div class="text-5xl sm:text-6xl font-black font-mono tracking-tight text-tm-text leading-none my-3" :class="{'text-tm-warning animate-pulse': isPaused}">
                    @{{ formatTime(elapsedTime) }}
                </div>

                <div class="flex items-center justify-center gap-3 mt-3">
                    <span class="text-xs text-tm-primary bg-tm-primarySoft border border-tm-primary/20 px-3 py-1 rounded font-mono font-bold">@{{ program.reps }}x @{{ program.distance }}m</span>
                    <span v-if="!hasStarted" class="text-xs text-tm-muted bg-tm-surface2 border border-tm-border px-3 py-1 rounded font-bold uppercase flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-tm-muted animate-ping"></span> READY
                    </span>
                    <span v-else-if="isPaused" class="text-xs text-tm-warning bg-tm-warning/10 border border-tm-warning/20 px-3 py-1 rounded font-bold uppercase flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-tm-warning"></span> PAUSED
                    </span>
                    <span v-else class="text-xs text-tm-primary bg-tm-primarySoft border border-tm-primary/20 px-3 py-1 rounded font-bold uppercase flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-tm-primary animate-pulse"></span> RUNNING
                    </span>
                </div>
            </div>

            <!-- List of Athletes in Mobile-optimized Card Form -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-tm-muted uppercase tracking-wider block">Catat Lap Atlet</h3>
                
                <div v-for="(a, idx) in athletes" :key="idx" class="bg-tm-surface border border-tm-border p-4 rounded-xl relative overflow-hidden transition-all duration-200"
                    :class="a.status === 'resting' ? 'border-tm-warning/30' : (a.status === 'completed' ? 'opacity-60' : 'hover:border-tm-primary/30')">
                    
                    <!-- Progress Bar at bottom of card -->
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-tm-surface2">
                        <div class="h-full bg-tm-primary transition-all duration-300" :style="{width: ((a.laps.length/program.reps)*100) + '%'}"></div>
                    </div>

                    <div class="flex items-center justify-between gap-4 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :class="{'bg-tm-primary': a.readiness==='green', 'bg-tm-warning': a.readiness==='yellow', 'bg-tm-danger': a.readiness==='red'}"></span>
                            <span class="font-extrabold text-base text-tm-text">@{{ a.name }}</span>
                        </div>

                        <!-- Mini status badge -->
                        <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded border" :class="getAthleteStatusBadgeClass(a)">
                            @{{ formatAthleteStatusText(a) }}
                        </span>
                    </div>

                    <!-- Lap & Split Info Grid -->
                    <div class="grid grid-cols-3 gap-2 bg-tm-bg/50 p-2.5 rounded-lg border border-tm-border text-center text-xs font-mono mb-3">
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Repetisi</span>
                            <span class="font-bold text-tm-text">@{{ a.status === 'completed' ? a.laps.length : a.laps.length + 1 }} / @{{ program.reps }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Split Terakhir</span>
                            <span class="font-bold text-tm-text">@{{ a.laps.length > 0 ? a.laps[a.laps.length-1].time.toFixed(1) + 's' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Delta</span>
                            <span class="font-bold" :class="a.laps.length > 0 ? getDeltaClass(a.laps[a.laps.length-1].diff) : 'text-tm-muted'">
                                @{{ a.laps.length > 0 && a.target > 0 ? (a.laps[a.laps.length-1].diff > 0 ? '+' : '') + a.laps[a.laps.length-1].diff.toFixed(1) + 's' : '-' }}
                            </span>
                        </div>
                    </div>

                    <!-- Individual Lap action button -->
                    <div class="flex gap-2">
                        <button v-if="a.status === 'active'" @click="recordAthleteLap(idx)" :disabled="isPaused || !hasStarted"
                            class="flex-grow h-12 bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-extrabold rounded-xl shadow-md transition active:scale-95 flex items-center justify-center gap-1.5 text-sm">
                            <i class="fa-solid fa-stopwatch"></i> CATAT LAP (@{{ getAthleteActiveTimerFormatted(a) }})
                        </button>
                        <button v-else-if="a.status === 'resting'" @click="skipRestForAthlete(idx)"
                            class="flex-grow h-12 bg-tm-warning hover:bg-yellow-600 text-tm-bg font-extrabold rounded-xl shadow-md transition active:scale-95 flex items-center justify-center gap-1 text-xs">
                            <i class="fa-solid fa-forward"></i> SKIP REST (@{{ Math.ceil(a.restCountdown) }}s)
                        </button>
                        <div v-else class="flex-grow h-12 bg-tm-surface2 text-tm-muted text-xs font-bold rounded-xl border border-tm-border flex items-center justify-center">
                            ✅ Sesi Selesai
                        </div>
                        
                        <button @click="openNotesModalForAthlete(a)" class="bg-tm-surface2 hover:bg-tm-surface border border-tm-border text-tm-muted px-4 rounded-xl transition flex items-center justify-center">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Live Log Feed -->
        <div class="lg:col-span-4 mt-6 lg:mt-0 flex flex-col space-y-6">
            <div class="bg-tm-surface border border-tm-border p-5 rounded-2xl">
                <h3 class="text-sm font-bold text-tm-muted uppercase tracking-wider mb-3">Live Split Feed</h3>
                
                <div class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
                    <div v-for="log in logs.slice().reverse()" :key="log.athleteName + log.rep" 
                        class="bg-tm-bg border border-tm-border p-3 rounded-xl flex items-center justify-between text-xs hover:border-tm-primary/20 transition">
                        <div>
                            <span class="font-extrabold text-tm-text">@{{ log.athleteName }}</span>
                            <span class="text-[10px] text-tm-muted block">Rep #@{{ log.rep }} · Pace: @{{ log.pace }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-tm-primary block">@{{ log.time.toFixed(1) }}s</span>
                            <span v-if="log.diff !== undefined" class="text-[10px] font-mono font-bold block" :class="getDeltaClass(log.diff)">
                                @{{ log.diff > 0 ? '+' : '' }}@{{ log.diff.toFixed(1) }}s
                            </span>
                        </div>
                    </div>
                    
                    <div v-if="logs.length === 0" class="text-center py-6 text-tm-muted text-xs">
                        Belum ada split tercatat
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ASSIGN OVERLAY DRAWER -->
        <div v-if="showLapAssignOverlay" class="no-print fixed inset-0 z-50 bg-black/80 flex items-end justify-center p-4" @click.self="showLapAssignOverlay = false">
            <div class="bg-tm-surface border border-tm-border w-full max-w-md rounded-t-2xl p-5 shadow-2xl animate-[slideUp_0.2s]">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-xs text-tm-muted uppercase font-mono">Lap: @{{ formatTime(pendingLapTime) }}</span>
                        <h4 class="text-sm font-extrabold text-tm-text">Pilih Atlet untuk Menyematkan Lap</h4>
                    </div>
                    <button @click="showLapAssignOverlay = false" class="text-tm-muted hover:text-tm-text text-lg"><i class="fa-solid fa-xmark"></i></button>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-2">
                    <button v-for="(a, idx) in athletes" :key="a.name" v-show="a.status === 'active'"
                        @click="assignPendingLapToAthlete(idx)"
                        class="h-14 bg-tm-surface2 hover:border-tm-primary text-tm-text font-bold rounded-xl border border-tm-border flex items-center justify-center gap-1.5 transition active:scale-95 text-base">
                        🏃 @{{ a.name }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: SESSION SUMMARY -->
    <section v-if="view === 'summary'" class="flex-grow max-w-3xl mx-auto w-full space-y-6 animate-[fadeIn_0.2s]">
        
        <div id="summary-print-container" class="bg-tm-surface border border-tm-border p-5 sm:p-7 rounded-2xl shadow-xl print-card">
            
            <div class="flex flex-col sm:flex-row items-center justify-between border-b border-tm-border pb-4 mb-4 gap-4">
                <div class="text-center sm:text-left">
                    <h2 class="text-xl font-black text-tm-text tracking-tight">Evaluasi Latihan Interval</h2>
                    <p class="text-xs text-tm-muted mt-0.5">Sesi: @{{ program.name }} · Lokasi: @{{ program.location || 'Stadion / Track' }}</p>
                </div>
                <div class="text-center sm:text-right font-mono text-xs text-tm-muted">
                    Tanggal: @{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </div>
            </div>

            <!-- Key metrics row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Total Atlet</span>
                    <span class="text-lg font-black text-tm-text font-mono">@{{ athleteAnalysis.length }}</span>
                </div>
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Total Reps</span>
                    <span class="text-lg font-black text-tm-text font-mono">@{{ logs.length }}</span>
                </div>
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Avg Pace</span>
                    <span class="text-lg font-black text-tm-primary font-mono">@{{ formatAvgPaceOfSession() }}</span>
                </div>
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Durasi Sesi</span>
                    <span class="text-lg font-black text-tm-text font-mono">@{{ formatTime(elapsedTime) }}</span>
                </div>
            </div>

            <!-- Athlete Performances Detail -->
            <div class="space-y-6">
                <div v-for="(a, index) in athleteAnalysis" :key="a.name" 
                    class="bg-tm-surface2 border border-tm-border p-4 rounded-xl space-y-3 print-card">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-tm-border/60 pb-2">
                        <div>
                            <span class="w-2.5 h-2.5 rounded-full inline-block mr-1.5" :class="{'bg-tm-primary': a.readiness==='green', 'bg-tm-warning': a.readiness==='yellow', 'bg-tm-danger': a.readiness==='red'}"></span>
                            <span class="text-base font-black text-tm-text inline-block">@{{ a.name }}</span>
                        </div>
                        <div class="flex gap-3 text-xs font-mono text-tm-muted">
                            <span>Avg Lap: <strong class="text-tm-primary">@{{ a.stats.avgTime.toFixed(1) }}s</strong></span>
                            <span>Consistency: <strong class="text-tm-primary">@{{ a.stats.consistency }}%</strong></span>
                        </div>
                    </div>

                    <!-- Performance Coach Insights -->
                    <div class="bg-tm-bg p-3 rounded-lg border border-tm-border/60 text-xs leading-relaxed print-card">
                        <span class="font-bold text-tm-primary block mb-1 font-mono uppercase text-[9px] tracking-wider">Sport Performance Analyst Insight</span>
                        @{{ a.feedback }}
                    </div>

                    <!-- Expandable Performance Graph Drawer -->
                    <div class="no-print border border-tm-border rounded-lg overflow-hidden">
                        <button @click="showGraphs = !showGraphs" class="w-full bg-tm-bg hover:bg-tm-surface px-4 py-2 text-xs font-bold text-tm-muted flex items-center justify-between transition">
                            <span>@{{ showGraphs ? 'Sembunyikan' : 'Tampilkan' }} Grafik Performa</span>
                            <i class="fa-solid" :class="showGraphs ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                        
                        <div v-show="showGraphs" class="p-3 bg-tm-bg h-40 w-full relative">
                            <canvas :id="'chart_' + index"></canvas>
                        </div>
                    </div>

                    <!-- Print-only chart space -->
                    <div class="hidden print-only h-40 w-full relative">
                        <canvas :id="'print_chart_' + index"></canvas>
                    </div>

                    <!-- Coach Notes Form -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 border-t border-tm-border/60 pt-3">
                        <div>
                            <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-0.5">Evaluasi Kondisi & Teknik</label>
                            <div class="flex gap-2">
                                <input v-model="a.notes.condition" type="text" placeholder="e.g. Lelah di lap akhir" 
                                    class="w-1/2 bg-tm-bg border border-tm-border text-xs text-tm-text p-2.5 rounded-lg focus:border-tm-primary focus:outline-none transition print-card">
                                <input v-model="a.notes.technique" type="text" placeholder="e.g. Arm swing berlebihan" 
                                    class="w-1/2 bg-tm-bg border border-tm-border text-xs text-tm-text p-2.5 rounded-lg focus:border-tm-primary focus:outline-none transition print-card">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-0.5">Rekomendasi Latihan</label>
                            <input v-model="a.notes.recommendation" type="text" placeholder="e.g. Fokus pada cadence di repetisi awal" 
                                class="w-full bg-tm-bg border border-tm-border text-xs text-tm-text p-2.5 rounded-lg focus:border-tm-primary focus:outline-none transition print-card">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2.5 flex-wrap mt-6 border-t border-tm-border pt-4 no-print">
                <button @click="copySummary(null)" class="flex-grow bg-tm-surface2 hover:bg-tm-surface text-tm-text border border-tm-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-copy"></i> SALIN RINGKASAN
                </button>
                <button @click="shareWhatsApp(null)" class="flex-grow bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-brands fa-whatsapp"></i> WA SHARE
                </button>
                <button @click="exportCSV(null)" class="flex-grow bg-tm-surface2 hover:bg-tm-surface text-tm-text border border-tm-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-csv"></i> CSV
                </button>
                <button @click="printPDF" class="flex-grow bg-tm-surface2 hover:bg-tm-surface text-tm-text border border-tm-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-pdf"></i> PRINT
                </button>
                <button @click="resetSession" class="w-full bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-extrabold py-3.5 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs mt-2">
                    <i class="fa-solid fa-rotate-right"></i> MULAI SESI BARU
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 4: SESSION HISTORY -->
    <section v-if="view === 'history'" class="flex-grow max-w-3xl mx-auto w-full space-y-6 animate-[fadeIn_0.2s]">
        <div class="bg-tm-surface border border-tm-border p-5 rounded-2xl">
            <div class="flex justify-between items-center mb-5 border-b border-tm-border pb-3">
                <div>
                    <h2 class="text-base font-bold text-tm-text">Riwayat Sesi Latihan</h2>
                    <p class="text-xs text-tm-muted">Tinjau kembali log latihan interval dan evaluasi atlet dari sesi sebelumnya.</p>
                </div>
                <button v-if="sessionHistory.length > 0" @click="clearAllHistory" class="text-xs font-bold text-tm-danger border border-tm-danger/30 bg-tm-danger/10 px-3 py-1.5 rounded-xl hover:bg-tm-danger hover:text-tm-text transition">
                    Hapus Semua
                </button>
            </div>

            <!-- List of past sessions -->
            <div v-if="sessionHistory.length === 0" class="text-center py-12 text-tm-muted">
                <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 block opacity-20"></i>
                <p class="text-sm">Belum ada riwayat sesi tersimpan.</p>
                <button @click="changeView('setup')" class="mt-3 bg-tm-primary text-tm-bg font-extrabold text-xs px-4 py-2 rounded-xl">Mulai Setup</button>
            </div>

            <div v-else class="space-y-3">
                <div v-for="s in sessionHistory" :key="s.id" class="bg-tm-surface2 border border-tm-border p-4 rounded-xl hover:border-tm-primary/30 transition-all duration-150">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div>
                            <h3 class="font-extrabold text-base text-tm-text">@{{ s.name }}</h3>
                            <div class="flex gap-3 text-xs text-tm-muted mt-1">
                                <span>📅 @{{ s.date }}</span>
                                <span>🏃 @{{ s.workoutType }}</span>
                                <span>📍 @{{ s.location }}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button @click="viewHistorySession(s)" class="bg-tm-primary text-tm-bg font-extrabold text-xs px-3.5 py-2 rounded-lg transition">
                                Lihat Detail
                            </button>
                            <button @click="deleteHistorySession(s.id)" class="bg-tm-danger/10 border border-tm-danger/20 text-tm-danger hover:bg-tm-danger hover:text-tm-text text-xs p-2 rounded-lg transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mini status grid -->
                    <div class="grid grid-cols-3 gap-2 bg-tm-bg/50 p-2.5 rounded-lg border border-tm-border text-center text-xs">
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Jumlah Atlet</span>
                            <span class="font-mono font-bold text-tm-text">@{{ s.athletes.length }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Total Split</span>
                            <span class="font-mono font-bold text-tm-text">@{{ s.logs.length }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Rata-rata Pace</span>
                            <span class="font-mono font-bold text-tm-primary">@{{ formatAvgPaceOfSession(s) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: HISTORICAL SESSION DETAIL VIEW -->
    <section v-if="view === 'history_detail' && selectedHistorySession" class="flex-grow max-w-3xl mx-auto w-full space-y-6 animate-[fadeIn_0.2s]">
        <div class="bg-tm-surface border border-tm-border p-5 sm:p-7 rounded-2xl shadow-xl print-card">
            
            <div class="flex flex-col sm:flex-row items-center justify-between border-b border-tm-border pb-4 mb-4 gap-4">
                <div class="text-center sm:text-left">
                    <button @click="changeView('history')" class="no-print bg-tm-surface2 hover:text-tm-primary border border-tm-border text-tm-muted text-xs font-bold px-3 py-1.5 rounded-lg transition mb-2 flex items-center gap-1">
                        <i class="fa-solid fa-chevron-left"></i> Kembali ke Riwayat
                    </button>
                    <h2 class="text-xl font-black text-tm-text tracking-tight">@{{ selectedHistorySession.name }}</h2>
                    <p class="text-xs text-tm-muted mt-0.5">📍 @{{ selectedHistorySession.location }} · Latihan: @{{ selectedHistorySession.workoutType }} (@{{ selectedHistorySession.program.reps }}x@{{ selectedHistorySession.program.distance }}m)</p>
                </div>
                <div class="text-center sm:text-right font-mono text-xs text-tm-muted">
                    Tanggal: @{{ selectedHistorySession.date }} - @{{ selectedHistorySession.time }}
                </div>
            </div>

            <!-- Key metrics row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Total Atlet</span>
                    <span class="text-lg font-black text-tm-text font-mono">@{{ selectedHistorySession.athletes.length }}</span>
                </div>
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Total Reps</span>
                    <span class="text-lg font-black text-tm-text font-mono">@{{ selectedHistorySession.logs.length }}</span>
                </div>
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Avg Pace</span>
                    <span class="text-lg font-black text-tm-primary font-mono">@{{ formatAvgPaceOfSession(selectedHistorySession) }}</span>
                </div>
                <div class="bg-tm-bg border border-tm-border p-3.5 rounded-xl text-center print-card">
                    <span class="text-[10px] text-tm-muted uppercase font-bold block mb-1">Total Durasi</span>
                    <span class="text-lg font-black text-tm-text font-mono">@{{ formatTime(selectedHistorySession.program.elapsedTime || 0) }}</span>
                </div>
            </div>

            <!-- Athlete Performances Detail -->
            <div class="space-y-6">
                <div v-for="(a, index) in selectedHistorySession.athletes" :key="a.name" 
                    class="bg-tm-surface2 border border-tm-border p-4 rounded-xl space-y-3 print-card">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-tm-border/60 pb-2">
                        <div>
                            <span class="w-2.5 h-2.5 rounded-full inline-block mr-1.5" :class="{'bg-tm-primary': a.readiness==='green', 'bg-tm-warning': a.readiness==='yellow', 'bg-tm-danger': a.readiness==='red'}"></span>
                            <span class="text-base font-black text-tm-text inline-block">@{{ a.name }}</span>
                        </div>
                        <div class="flex gap-3 text-xs font-mono text-tm-muted">
                            <span>Avg Lap: <strong class="text-tm-primary">@{{ getAthleteAvgTime(a).toFixed(1) }}s</strong></span>
                            <span>Consistency: <strong class="text-tm-primary">@{{ getAthleteConsistencyScore(a) }}%</strong></span>
                        </div>
                    </div>

                    <!-- Performance Coach Insights -->
                    <div class="bg-tm-bg p-3 rounded-lg border border-tm-border/60 text-xs leading-relaxed print-card">
                        <span class="font-bold text-tm-primary block mb-1 font-mono uppercase text-[9px] tracking-wider">Sport Performance Analyst Insight</span>
                        @{{ getHistoryAthleteFeedback(a, selectedHistorySession.program) }}
                    </div>

                    <!-- Expandable Performance Graph Drawer -->
                    <div class="no-print border border-tm-border rounded-lg overflow-hidden">
                        <button @click="showGraphs = !showGraphs" class="w-full bg-tm-bg hover:bg-tm-surface px-4 py-2 text-xs font-bold text-tm-muted flex items-center justify-between transition">
                            <span>@{{ showGraphs ? 'Sembunyikan' : 'Tampilkan' }} Grafik Performa</span>
                            <i class="fa-solid" :class="showGraphs ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                        
                        <div v-show="showGraphs" class="p-3 bg-tm-bg h-40 w-full relative">
                            <canvas :id="'history_chart_' + index"></canvas>
                        </div>
                    </div>

                    <!-- Print-only chart space -->
                    <div class="hidden print-only h-40 w-full relative">
                        <canvas :id="'print_history_chart_' + index"></canvas>
                    </div>

                    <!-- Coach Notes Display -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-tm-bg p-4 rounded-xl border border-tm-border/60 text-xs print-card">
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Kondisi Fisik</span>
                            <span class="text-tm-text font-semibold">@{{ a.notes.condition || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Evaluasi Teknik</span>
                            <span class="text-tm-text font-semibold">@{{ a.notes.technique || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Cedera Ringan</span>
                            <span class="text-tm-text font-semibold">@{{ a.notes.injury || 'Tidak Ada' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-tm-muted uppercase font-bold block mb-0.5">Rekomendasi</span>
                            <span class="text-tm-text font-semibold">@{{ a.notes.recommendation || 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2.5 flex-wrap mt-6 border-t border-tm-border pt-4 no-print">
                <button @click="copySummary(selectedHistorySession)" class="flex-grow bg-tm-surface2 hover:bg-tm-surface text-tm-text border border-tm-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-copy"></i> SALIN RINGKASAN
                </button>
                <button @click="shareWhatsApp(selectedHistorySession)" class="flex-grow bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-brands fa-whatsapp"></i> WA SHARE
                </button>
                <button @click="exportCSV(selectedHistorySession)" class="flex-grow bg-tm-surface2 hover:bg-tm-surface text-tm-text border border-tm-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-csv"></i> CSV
                </button>
                <button @click="printPDF" class="flex-grow bg-tm-surface2 hover:bg-tm-surface text-tm-text border border-tm-border font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-1.5 text-xs">
                    <i class="fa-solid fa-file-pdf"></i> PRINT
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 6: COACH NOTES MODAL -->
    <div v-if="showNotesModal && activeAthleteForNotes" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm no-print" @click.self="showNotesModal = false">
        <div class="bg-tm-surface border border-tm-border w-full max-w-md rounded-2xl p-5 shadow-2xl animate-[fadeIn_0.2s]">
            <div class="flex justify-between items-center mb-4 border-b border-tm-border pb-2">
                <div>
                    <h3 class="font-extrabold text-tm-text text-base">Evaluasi Atlet: @{{ activeAthleteForNotes.name }}</h3>
                    <p class="text-[10px] text-tm-muted uppercase font-mono">Input Catatan Pelatih Real-time</p>
                </div>
                <button @click="showNotesModal = false" class="text-tm-muted hover:text-tm-text text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-3 mb-5">
                <div>
                    <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Kondisi Atlet</label>
                    <input v-model="coachNotes.condition" type="text" class="w-full h-11 bg-tm-surface2 border border-tm-border text-base text-tm-text px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. Segar / Lelah / Kaki berat">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Evaluasi Teknik</label>
                    <textarea v-model="coachNotes.technique" class="w-full bg-tm-surface2 border border-tm-border text-base text-tm-text p-3 rounded-xl focus:border-tm-primary focus:outline-none transition h-20" placeholder="e.g. Langkah kaki stabil, pendaratan forefoot bagus"></textarea>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Rekomendasi Latihan</label>
                    <input v-model="coachNotes.recommendation" type="text" class="w-full h-11 bg-tm-surface2 border border-tm-border text-base text-tm-text px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. Kurangi pace di lap awal pada sesi berikutnya">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-tm-muted uppercase tracking-wider block mb-1">Catatan Cedera Ringan (Jika Ada)</label>
                    <input v-model="coachNotes.injury" type="text" class="w-full h-11 bg-tm-surface2 border border-tm-border text-base text-tm-text px-3 rounded-xl focus:border-tm-primary focus:outline-none transition" placeholder="e.g. Nyeri betis kanan ringan / Tidak ada">
                </div>
            </div>

            <button @click="saveCoachNotes" class="w-full h-12 bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-extrabold rounded-xl transition">SIMPAN CATATAN</button>
        </div>
    </div>

    <!-- STICKY ACTION BARS (PLACED OUTSIDE ANIMATED CONTAINER FOR TRUE POSITION:FIXED VIEWPORT STACKING CONTEXT) -->
    <!-- 1. Setup screen sticky start button -->
    <div v-if="view === 'setup'" class="no-print fixed bottom-0 inset-x-0 bg-tm-surface border-t border-tm-border p-3 shadow-2xl z-50 block sm:hidden" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
        <button @click="startSession" class="w-full h-14 bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-black rounded-xl text-base tracking-wide flex items-center justify-center gap-2 shadow-lg shadow-tm-primary/10 transition transform active:scale-[0.98]">
            <i class="fa-solid fa-play text-lg"></i> MULAI SESI LATIHAN
        </button>
    </div>

    <!-- 2. Live stopwatch/tracking screen sticky controls -->
    <div v-if="view === 'track'" class="no-print fixed bottom-0 inset-x-0 bg-tm-surface border-t border-tm-border p-3 shadow-2xl z-50 flex items-center gap-3" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
        <div class="flex-grow">
            <button v-if="!hasStarted" @click="startTimerNow"
                class="w-full h-14 bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-black rounded-xl text-base tracking-wide flex items-center justify-center gap-2 active:scale-95 transition shadow-lg shadow-tm-primary/10">
                <i class="fa-solid fa-play text-lg animate-pulse"></i> START STOPWATCH
            </button>
            <button v-else @click="triggerMainLapButton" :disabled="isPaused"
                class="w-full h-14 bg-tm-primary hover:bg-tm-primaryHover text-tm-bg font-black rounded-xl text-base tracking-wide flex items-center justify-center gap-2 active:scale-95 transition shadow-lg shadow-tm-primary/10">
                <i class="fa-solid fa-stopwatch text-lg"></i> LAP stopwatch
            </button>
        </div>
        
        <div class="flex gap-2">
            <button @click="togglePause" :disabled="!hasStarted"
                :class="isPaused ? 'bg-tm-primary/10 border-tm-primary text-tm-primary' : 'bg-tm-warning/10 border-tm-warning text-tm-warning'"
                class="w-12 h-14 rounded-xl border flex items-center justify-center transition active:scale-95">
                <i class="fa-solid text-base" :class="isPaused ? 'fa-play' : 'fa-pause'"></i>
            </button>
            <button @click="finishSession"
                class="w-12 h-14 bg-tm-danger/10 border border-tm-danger/30 text-tm-danger hover:bg-tm-danger hover:text-tm-text rounded-xl flex items-center justify-center transition active:scale-95">
                <i class="fa-solid fa-flag-checkered text-base"></i>
            </button>
        </div>
    </div>

</div>

<script>
    const { createApp, ref, reactive, computed, onMounted, nextTick } = Vue;

    createApp({
        setup() {
            // ----------------------------------------------------
            // HOISTED HELPER FUNCTIONS (To prevent ReferenceError)
            // ----------------------------------------------------
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

            const computeAthleteStats = (laps, targetVal) => {
                if (!laps || laps.length === 0) {
                    return { avgTime: 0, fastest: 0, slowest: 0, stdDev: 0, consistency: 0, avgPace: '-', totalTime: 0 };
                }
                const times = laps.map(l => l.time);
                const totalTime = times.reduce((acc, t) => acc + t, 0);
                const avgTime = totalTime / laps.length;
                const fastest = Math.min(...times);
                const slowest = Math.max(...times);

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
                    feedback += `${a.name} menunjukkan konsistensi pace sangat tinggi (skor ${stats.consistency}%). Menjaga tempo lap sangat stabil. `;
                } else if (stats.consistency >= 80) {
                    feedback += `Konsistensi pace ${a.name} stabil (skor ${stats.consistency}%). Ritme interval terjaga baik. `;
                } else {
                    feedback += `Pace ${a.name} berfluktuasi tinggi (skor ${stats.consistency}%). Kontrol ketat intensitas lari di lap awal. `;
                }

                if (a.laps.length >= 4) {
                    const half = Math.ceil(a.laps.length / 2);
                    const firstHalf = a.laps.slice(0, half).map(l => l.time);
                    const secondHalf = a.laps.slice(half).map(l => l.time);
                    const avgFirst = firstHalf.reduce((acc, t) => acc + t, 0) / firstHalf.length;
                    const avgSecond = secondHalf.reduce((acc, t) => acc + t, 0) / secondHalf.length;

                    if (avgSecond > avgFirst + 2.5) {
                        feedback += `Perlambatan pada lap-lap akhir terdeteksi. Pertimbangkan penambahan durasi rest atau evaluasi target pace berikutnya.`;
                    } else if (avgFirst > avgSecond + 2.5) {
                        feedback += `Peningkatan performa progresif di paruh akhir (negative split). Finishing sangat solid.`;
                    } else {
                        feedback += `Distribusi pace merata (even split). Kondisi fisik prima.`;
                    }
                }

                return feedback;
            };

            // States
            const view = ref('setup');
            const hasStarted = ref(false);
            const isPaused = ref(false);
            const ttsEnabled = ref(false);
            const elapsedTime = ref(0);
            const activeTheme = ref('dark'); // 'dark' or 'light'

            let timerInterval = null;
            let lastTime = 0;

            const selectedPresetName = ref('10 x 400 m');

            const presets = [
                { name: '10 x 400 m', reps: 10, distance: 400, rest: 60, pace: '04:00', target: 96 },
                { name: '6 x 800 m', reps: 6, distance: 800, rest: 90, pace: '04:00', target: 192 },
                { name: '5 h-1 km', reps: 5, distance: 1000, rest: 120, pace: '04:00', target: 240 },
                { name: '3 x 2 km', reps: 3, distance: 2000, rest: 180, pace: '04:00', target: 480 },
                { name: 'Tempo run 30 menit', reps: 1, distance: 6000, rest: 0, pace: '05:00', target: 1800 },
                { name: '5K time trial', reps: 1, distance: 5000, rest: 0, pace: '04:30', target: 1350 },
                { name: 'Cooper test 12 menit', reps: 1, distance: 3000, rest: 0, pace: '04:00', target: 720 },
                { name: 'Custom', reps: 5, distance: 400, rest: 60, pace: '04:00', target: 96 }
            ];

            const program = reactive({
                name: 'Sesi Latihan Interval',
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
                technique: 'Good running technique.',
                recommendation: 'Maintain target pace.',
                injury: 'Tidak ada'
            });

            // Graphs toggles
            const showGraphs = ref(false);

            // Quick assign lap overlay
            const showLapAssignOverlay = ref(false);
            const pendingLapTime = ref(null);

            // Voice Cues configurations
            const voiceCues = {
                start: 'Sesi latihan interval dimulai. Bersiaplah.',
                lapCompleted: (name, rep, time, status) => `${name} lap ${rep}, ${time} detik, ${status}`,
                restStarted: (name, restTime) => `${name} istirahat ${restTime} detik.`,
                restFinished: (name, rep) => `${name}, repetisi ${rep}. Mulai!`,
                completed: (name) => `${name} selesai.`
            };

            // Recalculations helpers
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

            // Session timer loops
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
                        condition: a.readiness === 'green' ? 'Fit' : (a.readiness === 'yellow' ? 'Lelah' : 'Cedera ringan'),
                        technique: 'Form lari terkendali.',
                        recommendation: 'Fokus menjaga ritme lap.',
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

                    // Rest Countdown logic
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

            // Main lap assignment overlay trigger
            const triggerMainLapButton = () => {
                if (!hasStarted.value || isPaused.value) return;
                const activeRunners = athletes.value.filter(a => a.status === 'active');
                if (activeRunners.length === 0) return;
                
                if (activeRunners.length === 1) {
                    const idx = athletes.value.findIndex(a => a.name === activeRunners[0].name);
                    recordAthleteLap(idx);
                } else {
                    pendingLapTime.value = elapsedTime.value;
                    showLapAssignOverlay.value = true;
                }
            };

            const assignPendingLapToAthlete = (index) => {
                const a = athletes.value[index];
                if (a.status !== 'active' || pendingLapTime.value === null) return;
                
                const nowTime = pendingLapTime.value;
                const splitVal = nowTime - a.lapStartElapsedTime;
                const cumulativeVal = a.laps.reduce((sum, l) => sum + l.time, 0) + splitVal;
                const repNum = a.laps.length + 1;
                const paceStr = calculatePace(splitVal, program.distance);
                
                let diffVal = 0;
                let statusVal = 'on target';
                let deltaText = 'sesuai target';
                
                if (a.target > 0) {
                    diffVal = splitVal - a.target;
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
                }
                
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

                showLapAssignOverlay.value = false;
                pendingLapTime.value = null;
                saveActiveSession();
            };

            // Direct athlete lap recording
            const recordAthleteLap = (index) => {
                if (!hasStarted.value || isPaused.value) return;
                const a = athletes.value[index];
                if (a.status !== 'active') return;

                const nowTime = elapsedTime.value;
                const splitVal = nowTime - a.lapStartElapsedTime;
                const cumulativeVal = a.laps.reduce((sum, l) => sum + l.time, 0) + splitVal;
                const repNum = a.laps.length + 1;
                const paceStr = calculatePace(splitVal, program.distance);
                
                let diffVal = 0;
                let statusVal = 'on target';
                let deltaText = 'sesuai target';
                
                if (a.target > 0) {
                    diffVal = splitVal - a.target;
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
                }
                
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

            // Math analysis statistics

            const generateAnalysis = () => {
                athleteAnalysis.value = athletes.value.map(a => {
                    const stats = computeAthleteStats(a.laps, a.target);
                    const safeNotes = a.notes ? { ...a.notes } : {};
                    return {
                        name: a.name,
                        readiness: a.readiness,
                        stats: stats,
                        laps: a.laps || [],
                        feedback: getAthleteFeedback(a, stats),
                        notes: {
                            condition: safeNotes.condition || (a.readiness === 'green' ? 'Fit' : (a.readiness === 'yellow' ? 'Lelah' : 'Cedera ringan')),
                            technique: safeNotes.technique || 'Form lari terkendali.',
                            recommendation: safeNotes.recommendation || 'Fokus menjaga ritme lap.',
                            injury: safeNotes.injury || 'Tidak ada'
                        }
                    };
                });
            };

            // Adaptive graph styling colors based on theme
            const getThemeColors = () => {
                if (activeTheme.value === 'dark') {
                    return {
                        grid: '#1F2D44',
                        label: '#94A3B8',
                        primary: '#B8FF00',
                        primarySoft: 'rgba(184, 255, 0, 0.05)',
                        target: '#EF4444'
                    };
                } else {
                    return {
                        grid: '#CBD5E1',
                        label: '#475569',
                        primary: '#65A30D',
                        primarySoft: 'rgba(101, 163, 13, 0.05)',
                        target: '#DC2626'
                    };
                }
            };

            const renderCharts = () => {
                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js is not loaded.');
                    return;
                }
                try {
                    const cColors = getThemeColors();
                    athleteAnalysis.value.forEach((a, index) => {
                        // Regular canvas
                        const canvasId = `chart_${index}`;
                        const ctx = document.getElementById(canvasId);
                        if (ctx) {
                            const existing = Chart.getChart(ctx);
                            if (existing) existing.destroy();

                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: (a.laps || []).map(l => `Rep ${l.rep}`),
                                    datasets: [
                                        {
                                            label: 'Split Time (s)',
                                            data: (a.laps || []).map(l => l.time),
                                            borderColor: cColors.primary,
                                            backgroundColor: cColors.primarySoft,
                                            borderWidth: 2,
                                            tension: 0.15,
                                            pointBackgroundColor: cColors.primary,
                                            pointRadius: 4,
                                            fill: true
                                        },
                                        {
                                            label: 'Target',
                                            data: Array((a.laps || []).length).fill(a.stats ? a.stats.avgTime : 0),
                                            borderColor: cColors.target,
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
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { grid: { color: cColors.grid }, ticks: { color: cColors.label } },
                                        x: { grid: { color: cColors.grid }, ticks: { color: cColors.label } }
                                    }
                                }
                            });
                        }

                        // Print canvas
                        const printCanvasId = `print_chart_${index}`;
                        const printCtx = document.getElementById(printCanvasId);
                        if (printCtx) {
                            const existing = Chart.getChart(printCtx);
                            if (existing) existing.destroy();

                            new Chart(printCtx, {
                                type: 'line',
                                data: {
                                    labels: (a.laps || []).map(l => `Rep ${l.rep}`),
                                    datasets: [
                                        {
                                            label: 'Split Time',
                                            data: (a.laps || []).map(l => l.time),
                                            borderColor: '#65A30D',
                                            borderWidth: 2,
                                            tension: 0.1,
                                            fill: false
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { ticks: { color: '#000000' } },
                                        x: { ticks: { color: '#000000' } }
                                    }
                                }
                            });
                        }
                    });
                } catch (e) {
                    console.error('Error rendering charts: ', e);
                }
            };

            const renderHistoryCharts = (session) => {
                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js is not loaded.');
                    return;
                }
                try {
                    const cColors = getThemeColors();
                    session.athletes.forEach((a, index) => {
                        const canvasId = `history_chart_${index}`;
                        const ctx = document.getElementById(canvasId);
                        if (ctx) {
                            const existing = Chart.getChart(ctx);
                            if (existing) existing.destroy();

                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: (a.laps || []).map(l => `Rep ${l.rep}`),
                                    datasets: [
                                        {
                                            label: 'Split Time',
                                            data: (a.laps || []).map(l => l.time),
                                            borderColor: cColors.primary,
                                            backgroundColor: cColors.primarySoft,
                                            borderWidth: 2,
                                            tension: 0.15,
                                            pointBackgroundColor: cColors.primary,
                                            pointRadius: 4,
                                            fill: true
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { grid: { color: cColors.grid }, ticks: { color: cColors.label } },
                                        x: { grid: { color: cColors.grid }, ticks: { color: cColors.label } }
                                    }
                                }
                            });
                        }

                        // Historical print chart
                        const printHistoryCanvasId = `print_history_chart_${index}`;
                        const printCtx = document.getElementById(printHistoryCanvasId);
                        if (printCtx) {
                            const existing = Chart.getChart(printCtx);
                            if (existing) existing.destroy();

                            new Chart(printCtx, {
                                type: 'line',
                                data: {
                                    labels: (a.laps || []).map(l => `Rep ${l.rep}`),
                                    datasets: [
                                        {
                                            label: 'Split Time',
                                            data: (a.laps || []).map(l => l.time),
                                            borderColor: '#65A30D',
                                            borderWidth: 2,
                                            tension: 0.1,
                                            fill: false
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { ticks: { color: '#000000' } },
                                        x: { ticks: { color: '#000000' } }
                                    }
                                }
                            });
                        }
                    });
                } catch (e) {
                    console.error('Error rendering history charts: ', e);
                }
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
                if (a.status === 'resting') return 'Rest';
                if (a.laps.length === 0) return 'Running';
                
                const lastLap = a.laps[a.laps.length - 1];
                if (lastLap.status === 'on target') return 'On Target';
                if (lastLap.status === 'too fast') return 'Too Fast';
                if (lastLap.status === 'too slow') return 'Too Slow';
                return 'Running';
            };

            const getAthleteStatusBadgeClass = (a) => {
                if (a.status === 'completed') return 'border-tm-border text-tm-muted bg-tm-surface2';
                if (a.status === 'resting') return 'border-tm-warning/30 text-tm-warning bg-tm-warning/10';
                if (a.laps.length === 0) return 'border-tm-primary/30 text-tm-primary bg-tm-primarySoft';
                
                const lastLap = a.laps[a.laps.length - 1];
                if (lastLap.status === 'on target') return 'border-tm-success/30 text-tm-success bg-tm-success/10';
                if (lastLap.status === 'too fast') return 'border-tm-warning/30 text-tm-warning bg-tm-warning/10';
                if (lastLap.status === 'too slow') return 'border-tm-danger/30 text-tm-danger bg-tm-danger/10';
                if (lastLap.status === 'fatigue risk') return 'border-tm-warning/30 text-tm-warning bg-tm-warning/10';
                return 'border-tm-border text-tm-text';
            };

            const getDeltaClass = (d) => {
                if (Math.abs(d) <= 1.5) return 'text-tm-primary';
                return d < -1.5 ? 'text-cyan-500' : 'text-tm-danger';
            };

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
                    athletes: athletes.value.map(a => {
                        const safeNotes = a.notes ? { ...a.notes } : {};
                        return {
                            name: a.name,
                            readiness: a.readiness,
                            target: a.target,
                            targetPace: a.targetPace,
                            restTime: a.restTime,
                            laps: a.laps || [],
                            notes: {
                                condition: safeNotes.condition || (a.readiness === 'green' ? 'Fit' : (a.readiness === 'yellow' ? 'Lelah' : 'Cedera ringan')),
                                technique: safeNotes.technique || 'Form lari terkendali.',
                                recommendation: safeNotes.recommendation || 'Fokus menjaga ritme lap.',
                                injury: safeNotes.injury || 'Tidak ada'
                            }
                        };
                    }),
                    logs: [...logs.value]
                };

                if (!Array.isArray(sessionHistory.value)) {
                    sessionHistory.value = [];
                }
                sessionHistory.value.unshift(sessionRecord);
                localStorage.setItem('trackmaster_history', JSON.stringify(sessionHistory.value));
            };

            const clearAllHistory = () => {
                if (confirm('Hapus seluruh riwayat sesi latihan?')) {
                    sessionHistory.value = [];
                    localStorage.removeItem('trackmaster_history');
                }
            };

            const deleteHistorySession = (id) => {
                if (confirm('Hapus sesi latihan ini?')) {
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
                const consistencyVal = getAthleteConsistencyScore(a);

                let feedback = "";
                if (consistencyVal > 92) {
                    feedback += `${a.name} berlari sangat stabil (Consistency: ${consistencyVal}%). Ritme interval terjaga sempurna. `;
                } else if (consistencyVal >= 80) {
                    feedback += `Transisi pace ${a.name} stabil (Consistency: ${consistencyVal}%). Distribusi energi terkontrol. `;
                } else {
                    feedback += `Terdeteksi fluktuasi pace yang tinggi (Consistency: ${consistencyVal}%). Disarankan menjaga tempo agar lebih tenang di repetisi awal. `;
                }

                return feedback;
            };

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

                text += `Detail lengkap di TrackMaster.`;
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

            // Theme Switching functionality
            const toggleTheme = () => {
                activeTheme.value = activeTheme.value === 'dark' ? 'light' : 'dark';
                applyTheme();
            };

            const applyTheme = () => {
                localStorage.setItem('tm_theme_v2', activeTheme.value);
                if (activeTheme.value === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.body.style.backgroundColor = '#08111F';
                } else {
                    document.documentElement.classList.remove('dark');
                    document.body.style.backgroundColor = '#F8FAFC';
                }
                
                // Refresh active charts with correct theme coloring
                nextTick(() => {
                    if (view.value === 'summary') {
                        renderCharts();
                    } else if (view.value === 'history_detail' && selectedHistorySession.value) {
                        renderHistoryCharts(selectedHistorySession.value);
                    }
                });
            };

            const initTheme = () => {
                const saved = localStorage.getItem('tm_theme_v2');
                if (saved === 'dark' || saved === 'light') {
                    activeTheme.value = saved;
                } else {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    activeTheme.value = prefersDark ? 'dark' : 'light';
                }
                applyTheme();
            };

            onMounted(() => {
                // Initialize theme first
                initTheme();

                const saved = localStorage.getItem('trackmaster_history');
                if (saved) {
                    try {
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed)) {
                            parsed.forEach(session => {
                                if (session.athletes) {
                                    session.athletes.forEach(a => {
                                        if (!a.notes) {
                                            a.notes = { condition: 'Fit', technique: 'Form lari terkendali.', recommendation: 'Fokus menjaga ritme lap.', injury: 'Tidak ada' };
                                        } else {
                                            a.notes.condition = a.notes.condition || 'Fit';
                                            a.notes.technique = a.notes.technique || 'Form lari terkendali.';
                                            a.notes.recommendation = a.notes.recommendation || 'Fokus menjaga ritme lap.';
                                            a.notes.injury = a.notes.injury || 'Tidak ada';
                                        }
                                    });
                                }
                            });
                            sessionHistory.value = parsed;
                        } else {
                            sessionHistory.value = [];
                        }
                    } catch (e) {
                        sessionHistory.value = [];
                    }
                }

                const active = localStorage.getItem('trackmaster_active_session');
                if (active) {
                    try {
                        const parsed = JSON.parse(active);
                        if (parsed.hasStarted && !parsed.completed) {
                            if (confirm('Lanjutkan sesi latihan sebelumnya?')) {
                                Object.assign(program, parsed.program);
                                if (parsed.athletes) {
                                    parsed.athletes.forEach(a => {
                                        if (!a.notes) {
                                            a.notes = { condition: 'Fit', technique: 'Form lari terkendali.', recommendation: 'Fokus menjaga ritme lap.', injury: 'Tidak ada' };
                                        } else {
                                            a.notes.condition = a.notes.condition || 'Fit';
                                            a.notes.technique = a.notes.technique || 'Form lari terkendali.';
                                            a.notes.recommendation = a.notes.recommendation || 'Fokus menjaga ritme lap.';
                                            a.notes.injury = a.notes.injury || 'Tidak ada';
                                        }
                                    });
                                }
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
                    } catch (e) {
                        console.error('Failed to parse active session:', e);
                        localStorage.removeItem('trackmaster_active_session');
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
                showGraphs,
                showLapAssignOverlay,
                pendingLapTime,
                activeTheme,
                
                selectPreset,
                addSetupAthlete,
                removeSetupAthlete,
                startSession,
                startTimerNow,
                togglePause,
                recordAthleteLap,
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
                triggerMainLapButton,
                assignPendingLapToAthlete,
                toggleTheme,
                
                // Formatter helpers
                formatTime,
                calculatePace,
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
