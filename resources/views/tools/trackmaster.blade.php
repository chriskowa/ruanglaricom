<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- SEO & Performance Meta -->
    <title>TrackMaster | Stopwatch Interval & Telemetri Lari Pelatih</title>
    <meta name="description" content="Instrumen stopwatch interval, split timing, pace telemetri, dan analisis performa lari presisi untuk pelatih dan komunitas atletik.">
    <meta name="keywords" content="stopwatch lari, interval timer lari, pace tracker, split timer lari, tools pelatih lari, latihan interval lari, track coach timing">
    <meta name="author" content="Ruang Lari">
    <meta name="robots" content="index, follow">

    <!-- Favicon -->
    <link class="favicon" rel="icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link class="favicon" rel="shortcut icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link class="favicon" rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link class="favicon" rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link class="favicon" rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/green/apple-touch-icon.png') }}">

    <meta name="theme-color" content="#080A0D">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- CDNs & Sports Typography -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Dual Theme CSS Variables (Dark & Light) */
        :root, html.dark {
            --tm-bg: #080A0D;
            --tm-surface: #12161D;
            --tm-surface-2: #191F28;
            --tm-surface-hover: #1E2532;
            --tm-border: rgba(255, 255, 255, 0.09);
            --tm-border-hover: rgba(255, 255, 255, 0.20);
            --tm-text: #FFFFFF;
            --tm-muted: #8E95A5;
            --tm-orange: #FF6B00;
            --tm-green: #22C55E;
            --tm-warning: #FACC15;
            --tm-danger: #EF4444;
            --tm-input-bg: #080A0D;
            --tm-nav-active-bg: #FF6B00;
            --tm-nav-active-text: #000000;
        }

        html.light {
            --tm-bg: #F1F4F9;
            --tm-surface: #FFFFFF;
            --tm-surface-2: #E5E9F0;
            --tm-surface-hover: #DDE2EC;
            --tm-border: #CBD5E1;
            --tm-border-hover: #94A3B8;
            --tm-text: #0F172A;
            --tm-muted: #475569;
            --tm-orange: #EA580C;
            --tm-green: #16A34A;
            --tm-warning: #CA8A04;
            --tm-danger: #DC2626;
            --tm-input-bg: #F8FAFC;
            --tm-nav-active-bg: #EA580C;
            --tm-nav-active-text: #FFFFFF;
        }

        body {
            background-color: var(--tm-bg);
            color: var(--tm-text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .font-sports-head {
            font-family: 'Oswald', 'Bebas Neue', sans-serif;
            letter-spacing: 0.04em;
        }

        .font-sports-condensed {
            font-family: 'Bebas Neue', 'Oswald', sans-serif;
            letter-spacing: 0.05em;
        }

        .font-telemetry {
            font-family: 'JetBrains Mono', monospace;
        }

        [v-cloak] { display: none; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--tm-bg); }
        ::-webkit-scrollbar-thumb { background: var(--tm-border); border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--tm-orange); }

        /* Theme tokens helper classes */
        .tm-bg { background-color: var(--tm-bg); }
        .tm-surface { background-color: var(--tm-surface); }
        .tm-surface-2 { background-color: var(--tm-surface-2); }
        .tm-border { border-color: var(--tm-border); }
        .tm-text { color: var(--tm-text); }
        .tm-muted { color: var(--tm-muted); }
        .tm-orange { color: var(--tm-orange); }
        .tm-green { color: var(--tm-green); }
        .tm-warning { color: var(--tm-warning); }
        .tm-danger { color: var(--tm-danger); }

        /* Navigation Tab Active/Inactive Styling */
        .nav-tab-btn {
            background-color: transparent;
            color: var(--tm-muted);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        .nav-tab-btn:hover {
            color: var(--tm-text);
            background-color: var(--tm-surface-hover);
        }
        .nav-tab-btn.is-active {
            background-color: var(--tm-nav-active-bg) !important;
            color: var(--tm-nav-active-text) !important;
            border-color: var(--tm-nav-active-bg) !important;
            font-weight: 800 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        /* Print Media Styles */
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; color: #000000 !important; }
            .print-card { background: #ffffff !important; border: 1px solid #cccccc !important; color: #000000 !important; }
            h1, h2, h3, h4, p, span, td, th { color: #000000 !important; }
            canvas { max-height: 180px !important; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

<div id="app" v-cloak class="flex-grow flex flex-col min-h-screen max-w-5xl mx-auto w-full px-3.5 sm:px-6 py-4 pb-28 relative">

    <!-- COMPACT SPORTS NAVIGATION HEADER (no-print) -->
    <header class="no-print flex flex-col sm:flex-row items-stretch sm:items-center justify-between border-b tm-border pb-3.5 mb-5 gap-3.5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('tools.index') }}" 
                    class="w-10 h-10 flex items-center justify-center rounded-lg tm-surface tm-muted hover:tm-text border tm-border transition shrink-0" 
                    title="Kembali ke Dashboard Tools">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-sports-head text-xl font-bold tracking-wider tm-text">
                            TRACK<span class="tm-orange">MASTER</span>
                        </span>
                        <span class="px-1.5 py-0.5 rounded tm-surface-2 border tm-border text-[10px] font-telemetry tm-muted uppercase font-bold">
                            PRO
                        </span>
                    </div>
                    <p class="text-[11px] tm-muted leading-none mt-0.5 font-medium">Interval Coach Instrument</p>
                </div>
            </div>

            <!-- Mobile Theme Switcher -->
            <button @click="toggleTheme" 
                class="sm:hidden w-10 h-10 rounded-lg tm-surface border tm-border flex items-center justify-center tm-muted hover:tm-text transition"
                title="Ganti Mode Terang / Gelap">
                <i class="fa-solid" :class="activeTheme === 'dark' ? 'fa-sun text-amber-400' : 'fa-moon text-slate-700'"></i>
            </button>
        </div>
        
        <!-- Sports Tab Navigation Bar with Theme Switcher (Desktop) -->
        <div class="flex items-center gap-2">
            <nav class="flex-1 sm:flex-none flex items-center tm-surface p-1 rounded-lg border tm-border gap-1 text-xs">
                <button type="button" @click="changeView('setup')" 
                    :class="{'is-active': view === 'setup'}"
                    class="nav-tab-btn flex-1 sm:flex-none px-3.5 py-2 rounded-md font-bold uppercase tracking-wider text-xs">
                    Setup
                </button>
                <button type="button" @click="changeView('track')" 
                    :class="{'is-active': view === 'track'}"
                    class="nav-tab-btn flex-1 sm:flex-none px-3.5 py-2 rounded-md font-bold uppercase tracking-wider text-xs">
                    Live
                </button>
                <button type="button" @click="changeView('summary')" 
                    :class="{'is-active': view === 'summary'}"
                    class="nav-tab-btn flex-1 sm:flex-none px-3.5 py-2 rounded-md font-bold uppercase tracking-wider text-xs">
                    Results
                </button>
                <button type="button" @click="changeView('history')" 
                    :class="{'is-active': view === 'history' || view === 'history_detail'}"
                    class="nav-tab-btn flex-1 sm:flex-none px-3.5 py-2 rounded-md font-bold uppercase tracking-wider text-xs">
                    History
                </button>
            </nav>

            <!-- Desktop Theme Toggle Button -->
            <button @click="toggleTheme" 
                class="hidden sm:flex w-10 h-10 rounded-lg tm-surface border tm-border items-center justify-center tm-muted hover:tm-text transition shrink-0" 
                title="Ganti Mode Terang / Gelap">
                <i class="fa-solid" :class="activeTheme === 'dark' ? 'fa-sun text-amber-400' : 'fa-moon text-slate-700'"></i>
            </button>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- SECTION 1: SETUP SCREEN                                                   -->
    <!-- ========================================================================= -->
    <section v-if="view === 'setup'" class="flex-grow space-y-5">
        
        <!-- Hero Header Box -->
        <div class="tm-surface border tm-border p-5 sm:p-6 rounded-xl relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1.5" style="background-color: var(--tm-orange);"></div>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-telemetry uppercase tracking-widest tm-orange font-bold block mb-1">
                        TRAINING INSTRUMENT CONFIGURATION
                    </span>
                    <h1 class="font-sports-head text-2xl sm:text-3xl font-bold uppercase tm-text tracking-wide">
                        CREATE TRAINING SESSION
                    </h1>
                    <p class="text-xs tm-muted mt-1 max-w-xl leading-relaxed">
                        Konfigurasikan jarak repetisi, target waktu lap, durasi pemulihan (rest), dan daftar atlet yang akan dimonitor secara bersamaan.
                    </p>
                </div>

                <div class="hidden sm:flex items-center gap-3 shrink-0">
                    <button @click="startSession" 
                        class="h-12 px-6 font-sports-head text-base font-bold uppercase rounded-lg transition active:scale-95 flex items-center gap-2 shadow-lg"
                        style="background-color: var(--tm-orange); color: #000000;">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Start Session</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 1. WORKOUT SECTION -->
        <div class="tm-surface border tm-border p-5 rounded-xl space-y-4">
            <div class="flex items-center justify-between border-b tm-border pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded tm-surface-2 border tm-border tm-text font-telemetry font-bold text-xs flex items-center justify-center">1</span>
                    <h2 class="font-sports-head text-base font-bold uppercase tracking-wider tm-text">WORKOUT SPECIFICATION</h2>
                </div>
                <span class="text-[11px] font-telemetry tm-muted">Interval Parameters</span>
            </div>

            <!-- Session Name & Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Nama Sesi Latihan</label>
                    <input v-model="program.name" type="text" 
                        class="w-full h-11 border tm-border text-sm tm-text px-3.5 rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="Contoh: 10 x 400m VO2Max Track Session">
                </div>
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Lokasi Track (Opsional)</label>
                    <input v-model="program.location" type="text" 
                        class="w-full h-11 border tm-border text-sm tm-text px-3.5 rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="Contoh: Stadion Atletik Madya GBK">
                </div>
            </div>

            <!-- Workout Presets (Sports Program Style) -->
            <div>
                <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-2">Preset Program Latihan</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button v-for="p in presets" :key="p.name" @click="selectPreset(p)"
                        :class="selectedPresetName === p.name ? 'border-2 tm-surface-2 tm-text font-bold' : 'border tm-border tm-muted hover:tm-text'"
                        :style="selectedPresetName === p.name ? 'border-color: var(--tm-orange);' : ''"
                        class="p-3 rounded-lg text-left transition flex flex-col justify-between min-h-[64px]"
                        style="background-color: var(--tm-input-bg);">
                        <div class="font-sports-head text-xs font-bold uppercase tracking-wide truncate">
                            @{{ p.name }}
                        </div>
                        <div class="text-[10px] font-telemetry tm-muted mt-1">
                            @{{ p.reps }}x @{{ p.distance }}m &bull; @{{ p.rest }}s rest
                        </div>
                    </button>
                </div>
            </div>

            <!-- Reps, Distance, Rest Inputs -->
            <div class="grid grid-cols-3 gap-3 pt-2">
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Repetisi</label>
                    <input v-model.number="program.reps" type="number" min="1" 
                        class="w-full h-11 border tm-border text-center font-telemetry text-base font-bold tm-text rounded-lg focus:outline-none transition"
                        style="background-color: var(--tm-input-bg);">
                </div>
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Jarak per Lap (m)</label>
                    <input v-model.number="program.distance" type="number" min="1" 
                        class="w-full h-11 border tm-border text-center font-telemetry text-base font-bold tm-text rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        @change="recalculateAllTargetsFromPace">
                </div>
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Rest Pemulihan (s)</label>
                    <input v-model.number="program.rest" type="number" min="0" 
                        class="w-full h-11 border tm-border text-center font-telemetry text-base font-bold tm-orange rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        @change="syncRestTimeToAthletes">
                </div>
            </div>
        </div>

        <!-- 2. TARGET PACE SECTION -->
        <div class="tm-surface border tm-border p-5 rounded-xl space-y-3.5">
            <div class="flex items-center justify-between border-b tm-border pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded tm-surface-2 border tm-border tm-text font-telemetry font-bold text-xs flex items-center justify-center">2</span>
                    <h2 class="font-sports-head text-base font-bold uppercase tracking-wider tm-text">TARGET PACING & TIMING</h2>
                </div>
                <span class="text-[11px] font-telemetry tm-muted">Pace Calculator</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Target Pace (Min:Det /km)</label>
                    <input v-model="program.targetPace" type="text" 
                        class="w-full h-11 border tm-border text-center font-telemetry text-base font-bold tm-green rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="04:00" @input="recalculateTargetTimeFromPace">
                </div>
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wider tm-muted block mb-1.5">Target Waktu per Lap (detik)</label>
                    <input v-model.number="program.targetTime" type="number" min="0" 
                        class="w-full h-11 border tm-border text-center font-telemetry text-base font-bold tm-text rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="96" @input="recalculateTargetPaceFromTime">
                </div>
            </div>
        </div>

        <!-- 3. ATHLETES SECTION -->
        <div class="tm-surface border tm-border p-5 rounded-xl space-y-4">
            <div class="flex items-center justify-between border-b tm-border pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded tm-surface-2 border tm-border tm-text font-telemetry font-bold text-xs flex items-center justify-center">3</span>
                    <h2 class="font-sports-head text-base font-bold uppercase tracking-wider tm-text">ATHLETE ROSTER</h2>
                </div>
                <button @click="addSetupAthlete" 
                    class="px-3 py-1.5 rounded-md tm-surface-2 hover:bg-slate-700/20 border tm-border tm-text text-xs font-semibold uppercase tracking-wider transition flex items-center gap-1.5">
                    <span>+ Tambah Atlet</span>
                </button>
            </div>

            <!-- Athlete Cards List in Setup -->
            <div class="space-y-2.5 max-h-[50vh] overflow-y-auto pr-1">
                <div v-for="(a, i) in setupAthletes" :key="i" 
                    class="border tm-border p-3.5 rounded-lg flex flex-col sm:flex-row items-stretch sm:items-center gap-3 transition"
                    style="background-color: var(--tm-input-bg);">
                    
                    <div class="flex-1">
                        <label class="text-[10px] font-semibold uppercase tracking-wider tm-muted block mb-1">Nama Atlet #@{{ i + 1 }}</label>
                        <input v-model="a.name" 
                            class="w-full h-10 tm-surface border tm-border text-sm tm-text font-bold px-3 rounded-md focus:outline-none transition" 
                            placeholder="Ketik nama atlet...">
                    </div>

                    <div class="w-full sm:w-36">
                        <label class="text-[10px] font-semibold uppercase tracking-wider tm-muted block mb-1">Target Lap (s)</label>
                        <input v-model.number="a.target" type="number" 
                            class="w-full h-10 tm-surface border tm-border text-center font-telemetry text-sm font-bold tm-text px-2 rounded-md focus:outline-none transition" 
                            @input="onSetupAthleteTargetTimeChange(a)">
                    </div>

                    <div class="w-full sm:w-36">
                        <label class="text-[10px] font-semibold uppercase tracking-wider tm-muted block mb-1">Status Kesiapan</label>
                        <select v-model="a.readiness" 
                            class="w-full h-10 tm-surface border tm-border text-xs font-semibold tm-text px-2.5 rounded-md focus:outline-none transition">
                            <option value="green">🟢 Siap</option>
                            <option value="yellow">🟡 Lelah</option>
                            <option value="red">🔴 Cedera</option>
                        </select>
                    </div>

                    <button @click="removeSetupAthlete(i)" 
                        class="w-10 h-10 self-end sm:self-auto rounded-md tm-surface-2 hover:bg-red-500/20 border tm-border tm-muted hover:tm-danger transition flex items-center justify-center shrink-0" 
                        title="Hapus Atlet">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Voice Cue Assistant & Global Setting -->
            <div class="pt-3 border-t tm-border flex items-center justify-between">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" v-model="ttsEnabled" class="w-4 h-4 rounded tm-surface border tm-border text-orange-500 focus:ring-0">
                    <div>
                        <span class="text-xs font-semibold tm-text block">Voice Telemetry Audio</span>
                        <span class="text-[10px] tm-muted">Panduan suara otomatis saat lap & rest selesai</span>
                    </div>
                </label>

                <span class="text-xs font-telemetry tm-muted">@{{ setupAthletes.length }} Atlet Terdaftar</span>
            </div>
        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- SECTION 2: LIVE TRACK SCREEN (HERO STOPWATCH SCREEN)                      -->
    <!-- ========================================================================= -->
    <section v-if="view === 'track'" class="flex-grow flex flex-col lg:grid lg:grid-cols-12 lg:gap-6 space-y-5 lg:space-y-0">
        
        <!-- Left: Stopwatch Hero & Athlete Cards -->
        <div class="lg:col-span-8 flex flex-col space-y-4">
            
            <!-- MASTER STOPWATCH INSTRUMENT (HERO DISPLAY) -->
            <div @click="!hasStarted ? startTimerNow() : togglePause()" 
                class="tm-surface border tm-border p-6 rounded-xl text-center shadow-xl relative overflow-hidden cursor-pointer hover:opacity-95 transition select-none group">
                
                <!-- Status Top Bar -->
                <div class="flex items-center justify-between gap-3 text-xs border-b tm-border pb-3 mb-2">
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-[11px] font-sports-head tm-muted uppercase tracking-wider">SESSION</span>
                        <span class="text-xs font-bold tm-text truncate">@{{ program.name }}</span>
                    </div>

                    <div class="shrink-0 flex items-center gap-2 font-telemetry text-xs">
                        <span v-if="!hasStarted" class="px-2 py-0.5 rounded tm-surface-2 border tm-border tm-muted font-bold uppercase flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> READY
                        </span>
                        <span v-else-if="isPaused" class="px-2 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-500 font-bold uppercase flex items-center gap-1.5 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> PAUSED
                        </span>
                        <span v-else class="px-2 py-0.5 rounded bg-emerald-500/20 border border-emerald-500/40 text-emerald-500 font-bold uppercase flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> RUNNING
                        </span>
                    </div>
                </div>

                <!-- Giant Digital Stopwatch Display -->
                <div class="my-2 py-1">
                    <div class="text-6xl sm:text-7xl md:text-8xl font-black font-telemetry tracking-tight tm-text leading-none" 
                        :class="{'text-amber-500': isPaused}">
                        @{{ formatTime(elapsedTime) }}
                    </div>
                </div>

                <!-- Parameters Badge Info -->
                <div class="flex items-center justify-center gap-3 pt-2">
                    <span class="px-3 py-1 rounded tm-surface-2 border tm-border font-telemetry text-xs font-semibold tm-text">
                        @{{ program.reps }}x @{{ program.distance }}m
                    </span>
                    <span class="px-3 py-1 rounded tm-surface-2 border tm-border font-telemetry text-xs font-semibold tm-orange">
                        REST @{{ program.rest }}s
                    </span>
                    <span class="px-3 py-1 rounded tm-surface-2 border tm-border font-telemetry text-xs font-semibold tm-green">
                        PACE @{{ program.targetPace }}/km
                    </span>
                </div>
            </div>

            <!-- ATHLETE PERFORMANCE CARDS -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-sports-head text-base font-bold uppercase tracking-wider tm-text">
                        ATHLETE TELEMETRY & SPLITS
                    </h3>
                    <span class="text-xs font-telemetry tm-muted">@{{ athletes.length }} Aktif</span>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <div v-for="(a, idx) in athletes" :key="idx" 
                        class="tm-surface border tm-border p-4 rounded-xl relative overflow-hidden transition"
                        :class="a.status === 'resting' ? 'border-amber-500/40' : (a.status === 'completed' ? 'opacity-50' : 'hover:border-slate-500')">
                        
                        <!-- Top Progress Bar -->
                        <div class="absolute inset-x-0 top-0 h-1 tm-surface-2">
                            <div class="h-full transition-all duration-300" 
                                style="background-color: var(--tm-orange);"
                                :style="{width: ((a.laps.length/program.reps)*100) + '%'}"></div>
                        </div>

                        <!-- Card Header -->
                        <div class="flex items-center justify-between gap-3 mb-3 pt-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" 
                                    :class="{'bg-emerald-500': a.readiness==='green', 'bg-amber-500': a.readiness==='yellow', 'bg-rose-500': a.readiness==='red'}"></span>
                                <span class="font-sports-head text-xl font-bold uppercase tm-text tracking-wide">
                                    @{{ a.name }}
                                </span>
                            </div>

                            <span class="text-[11px] font-telemetry font-bold uppercase px-2 py-0.5 rounded border" 
                                :class="getAthleteStatusBadgeClass(a)">
                                @{{ formatAthleteStatusText(a) }}
                            </span>
                        </div>

                        <!-- Telemetry Grid (Rep, Last Split, Delta) -->
                        <div class="grid grid-cols-3 gap-2 p-3 rounded-lg border tm-border text-center font-telemetry mb-3"
                            style="background-color: var(--tm-input-bg);">
                            <div>
                                <span class="text-[10px] tm-muted uppercase font-bold block mb-0.5">Repetisi</span>
                                <span class="text-sm font-bold tm-text">
                                    @{{ a.status === 'completed' ? a.laps.length : a.laps.length + 1 }} / @{{ program.reps }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] tm-muted uppercase font-bold block mb-0.5">Split Terakhir</span>
                                <span class="text-sm font-bold tm-text">
                                    @{{ a.laps.length > 0 ? a.laps[a.laps.length-1].time.toFixed(1) + 's' : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] tm-muted uppercase font-bold block mb-0.5">Delta Target</span>
                                <span class="text-sm font-bold" 
                                    :class="a.laps.length > 0 ? getDeltaClass(a.laps[a.laps.length-1].diff) : 'tm-muted'">
                                    @{{ a.laps.length > 0 && a.target > 0 ? (a.laps[a.laps.length-1].diff > 0 ? '+' : '') + a.laps[a.laps.length-1].diff.toFixed(1) + 's' : '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Button per Athlete -->
                        <div class="flex gap-2">
                            <button v-if="a.status === 'active'" @click="recordAthleteLap(idx)" :disabled="isPaused || !hasStarted"
                                class="flex-grow h-12 font-sports-head text-base font-bold uppercase rounded-lg shadow-md transition active:scale-[0.98] flex items-center justify-center gap-2"
                                style="background-color: var(--tm-orange); color: #000000;">
                                <i class="fa-solid fa-stopwatch text-sm"></i>
                                <span>Catat Lap (@{{ getAthleteActiveTimerFormatted(a) }})</span>
                            </button>
                            <button v-else-if="a.status === 'resting'" @click="skipRestForAthlete(idx)"
                                class="flex-grow h-12 bg-amber-500 hover:bg-amber-600 text-black font-sports-head text-sm font-bold uppercase rounded-lg shadow-md transition active:scale-[0.98] flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-forward text-xs"></i>
                                <span>Skip Rest (@{{ Math.ceil(a.restCountdown) }}s)</span>
                            </button>
                            <div v-else 
                                class="flex-grow h-12 tm-surface-2 tm-muted text-xs font-bold rounded-lg border tm-border flex items-center justify-center uppercase tracking-wider">
                                Selesai Seluruh Repetisi
                            </div>
                            
                            <button @click="openNotesModalForAthlete(a)" 
                                class="h-12 w-12 tm-surface-2 hover:bg-slate-700/20 border tm-border tm-muted hover:tm-text rounded-lg transition flex items-center justify-center shrink-0" 
                                title="Catat Evaluasi Pelatih">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Telemetry Live Split Feed -->
        <div class="lg:col-span-4 flex flex-col space-y-4">
            <div class="tm-surface border tm-border p-5 rounded-xl">
                <div class="flex items-center justify-between border-b tm-border pb-3 mb-3">
                    <h3 class="font-sports-head text-base font-bold uppercase tracking-wider tm-text">
                        RACE TELEMETRY STREAM
                    </h3>
                    <span class="text-[10px] font-telemetry px-1.5 py-0.5 rounded tm-surface-2 border tm-border tm-green font-bold">
                        LIVE
                    </span>
                </div>
                
                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1 font-telemetry">
                    <div v-for="log in logs.slice().reverse()" :key="log.athleteName + log.rep" 
                        class="border tm-border p-3 rounded-lg flex items-center justify-between text-xs transition"
                        style="background-color: var(--tm-input-bg);">
                        <div>
                            <div class="font-bold tm-text">@{{ log.athleteName }}</div>
                            <div class="text-[10px] tm-muted mt-0.5">
                                REP #@{{ log.rep }} &bull; Pace @{{ log.pace }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold tm-text text-sm">@{{ log.time.toFixed(1) }}s</div>
                            <div v-if="log.diff !== undefined" class="text-[10px] font-bold" :class="getDeltaClass(log.diff)">
                                @{{ log.diff > 0 ? '+' : '' }}@{{ log.diff.toFixed(1) }}s
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="logs.length === 0" class="text-center py-12 tm-muted text-xs">
                        <i class="fa-solid fa-stopwatch text-2xl mb-2 opacity-20 block"></i>
                        Belum ada split lap tercatat
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ASSIGN OVERLAY DRAWER -->
        <div v-if="showLapAssignOverlay" class="no-print fixed inset-0 z-50 bg-black/80 flex items-end justify-center p-4" @click.self="showLapAssignOverlay = false">
            <div class="tm-surface border tm-border w-full max-w-md rounded-t-2xl p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-4 border-b tm-border pb-3">
                    <div>
                        <span class="text-xs tm-orange font-telemetry uppercase font-bold">WAKTU LAP: @{{ formatTime(pendingLapTime) }}</span>
                        <h4 class="font-sports-head text-base font-bold uppercase tm-text">PILIH ATLET UNTUK LAP INI</h4>
                    </div>
                    <button @click="showLapAssignOverlay = false" class="tm-muted hover:tm-text text-lg">&times;</button>
                </div>
                
                <div class="grid grid-cols-2 gap-2.5 mb-2">
                    <button v-for="(a, idx) in athletes" :key="a.name" v-show="a.status === 'active'"
                        @click="assignPendingLapToAthlete(idx)"
                        class="h-14 tm-surface-2 hover:border-orange-500 tm-text font-sports-head text-base font-bold uppercase rounded-lg border tm-border flex items-center justify-center transition active:scale-95">
                        @{{ a.name }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- SECTION 3: SESSION RESULTS & SUMMARY (COACH REPORT)                       -->
    <!-- ========================================================================= -->
    <section v-if="view === 'summary'" class="flex-grow max-w-3xl mx-auto w-full space-y-5">
        
        <div id="summary-print-container" class="tm-surface border tm-border p-5 sm:p-7 rounded-xl shadow-xl print-card">
            
            <!-- Header Report -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b tm-border pb-4 mb-5 gap-3">
                <div>
                    <span class="text-[10px] font-telemetry uppercase tracking-widest tm-orange font-bold block mb-1">
                        PERFORMANCE EVALUATION REPORT
                    </span>
                    <h2 class="font-sports-head text-2xl font-bold uppercase tm-text tracking-wide">
                        @{{ program.name }}
                    </h2>
                    <p class="text-xs tm-muted mt-0.5">Lokasi: @{{ program.location || 'Stadion / Track' }} &bull; Latihan: @{{ program.reps }}x@{{ program.distance }}m</p>
                </div>
                <div class="text-left sm:text-right font-telemetry text-xs tm-muted">
                    @{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </div>
            </div>

            <!-- Key Performance Metrics Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="border tm-border p-3.5 rounded-lg text-center print-card font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Total Atlet</span>
                    <span class="text-xl font-bold tm-text">@{{ athleteAnalysis.length }}</span>
                </div>
                <div class="border tm-border p-3.5 rounded-lg text-center print-card font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Total Reps</span>
                    <span class="text-xl font-bold tm-text">@{{ logs.length }}</span>
                </div>
                <div class="border tm-border p-3.5 rounded-lg text-center print-card font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Avg Pace</span>
                    <span class="text-xl font-bold tm-green">@{{ formatAvgPaceOfSession() }}</span>
                </div>
                <div class="border tm-border p-3.5 rounded-lg text-center print-card font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Total Durasi</span>
                    <span class="text-xl font-bold tm-text">@{{ formatTime(elapsedTime) }}</span>
                </div>
            </div>

            <!-- Athlete Performance Details -->
            <div class="space-y-4">
                <div v-for="(a, index) in athleteAnalysis" :key="a.name" 
                    class="border tm-border p-4 rounded-xl space-y-3 print-card" style="background-color: var(--tm-input-bg);">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b tm-border pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" 
                                :class="{'bg-emerald-500': a.readiness==='green', 'bg-amber-500': a.readiness==='yellow', 'bg-rose-500': a.readiness==='red'}"></span>
                            <span class="font-sports-head text-lg font-bold uppercase tm-text">@{{ a.name }}</span>
                        </div>
                        <div class="flex gap-4 text-xs font-telemetry tm-muted">
                            <span>Avg Lap: <strong class="tm-text">@{{ a.stats.avgTime.toFixed(1) }}s</strong></span>
                            <span>Consistency: <strong class="tm-green">@{{ a.stats.consistency }}%</strong></span>
                        </div>
                    </div>

                    <!-- Coach Performance Insight Box -->
                    <div class="tm-surface p-3 rounded-lg border tm-border text-xs leading-relaxed print-card">
                        <span class="font-sports-head text-xs font-bold uppercase tm-orange block mb-1">ANALYST INSIGHT</span>
                        <p class="tm-text">@{{ a.feedback }}</p>
                    </div>

                    <!-- Expandable Performance Graph Drawer -->
                    <div class="no-print border tm-border rounded-lg overflow-hidden">
                        <button @click="showGraphs = !showGraphs" class="w-full tm-surface hover:bg-slate-700/10 px-4 py-2 text-xs font-semibold tm-muted flex items-center justify-between transition">
                            <span>@{{ showGraphs ? 'Sembunyikan' : 'Tampilkan' }} Grafik Laju Repetisi</span>
                            <i class="fa-solid" :class="showGraphs ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                        
                        <div v-show="showGraphs" class="p-3 tm-surface-2 h-40 w-full relative">
                            <canvas :id="'chart_' + index"></canvas>
                        </div>
                    </div>

                    <!-- Coach Notes Evaluation Form -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 border-t tm-border pt-3">
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider tm-muted block mb-1">Evaluasi Teknik</label>
                            <input v-model="a.notes.technique" type="text" placeholder="Contoh: Pendaratan kaki stabil" 
                                class="w-full tm-surface border tm-border text-xs tm-text p-2.5 rounded-lg focus:outline-none transition print-card">
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider tm-muted block mb-1">Rekomendasi Latihan</label>
                            <input v-model="a.notes.recommendation" type="text" placeholder="Contoh: Pertahankan ritme lap awal" 
                                class="w-full tm-surface border tm-border text-xs tm-text p-2.5 rounded-lg focus:outline-none transition print-card">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Grid -->
            <div class="flex gap-2 flex-wrap mt-6 border-t tm-border pt-4 no-print">
                <button @click="copySummary(null)" class="flex-grow tm-surface-2 hover:bg-slate-700/20 tm-text border tm-border font-semibold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-copy"></i>
                    <span>Salin Laporan</span>
                </button>
                <button @click="shareWhatsApp(null)" class="flex-grow bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Kirim WhatsApp</span>
                </button>
                <button @click="exportCSV(null)" class="flex-grow tm-surface-2 hover:bg-slate-700/20 tm-text border tm-border font-semibold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Export CSV</span>
                </button>
                <button @click="printPDF" class="flex-grow tm-surface-2 hover:bg-slate-700/20 tm-text border tm-border font-semibold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Print PDF</span>
                </button>
                <button @click="resetSession" 
                    class="w-full font-sports-head text-sm font-bold uppercase py-3 px-4 rounded-lg transition text-center mt-2"
                    style="background-color: var(--tm-orange); color: #000000;">
                    Mulai Sesi Latihan Baru
                </button>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- SECTION 4: TRAINING ARCHIVE (HISTORY)                                     -->
    <!-- ========================================================================= -->
    <section v-if="view === 'history'" class="flex-grow max-w-3xl mx-auto w-full space-y-4">
        <div class="tm-surface border tm-border p-5 rounded-xl">
            <div class="flex justify-between items-center mb-4 border-b tm-border pb-3">
                <div>
                    <h2 class="font-sports-head text-xl font-bold uppercase tm-text tracking-wide">TRAINING SESSION ARCHIVE</h2>
                    <p class="text-xs tm-muted">Log rekaman sesi interval dan telemetri atlet terdahulu.</p>
                </div>
                <button v-if="sessionHistory.length > 0" @click="clearAllHistory" 
                    class="text-xs font-semibold text-rose-500 border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 rounded-md hover:bg-rose-500 hover:text-white transition">
                    Hapus Semua
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="sessionHistory.length === 0" class="text-center py-12 tm-muted">
                <i class="fa-solid fa-clock-rotate-left text-3xl mb-3 block opacity-20"></i>
                <p class="text-xs">Belum ada riwayat sesi tersimpan di peramban ini.</p>
                <button @click="changeView('setup')" 
                    class="mt-3 font-sports-head text-xs font-bold uppercase px-4 py-2 rounded-lg"
                    style="background-color: var(--tm-orange); color: #000000;">
                    Buat Sesi Latihan
                </button>
            </div>

            <!-- Archive Sessions List -->
            <div v-else class="space-y-3">
                <div v-for="s in sessionHistory" :key="s.id" 
                    class="border tm-border p-4 rounded-xl hover:border-slate-500 transition"
                    style="background-color: var(--tm-input-bg);">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div>
                            <h3 class="font-sports-head text-base font-bold uppercase tm-text">@{{ s.name }}</h3>
                            <div class="flex flex-wrap gap-3 text-xs font-telemetry tm-muted mt-1">
                                <span>📅 @{{ s.date }}</span>
                                <span>🏃 @{{ s.workoutType }}</span>
                                <span>📍 @{{ s.location }}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button @click="viewHistorySession(s)" 
                                class="font-sports-head text-xs font-bold uppercase px-3.5 py-1.5 rounded-md transition"
                                style="background-color: var(--tm-orange); color: #000000;">
                                Buka Detail
                            </button>
                            <button @click="deleteHistorySession(s.id)" 
                                class="tm-surface-2 hover:bg-rose-500/20 border tm-border tm-muted hover:text-rose-500 text-xs p-2 rounded-md transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 tm-surface p-2.5 rounded-lg border tm-border text-center font-telemetry text-xs">
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Atlet</span>
                            <span class="font-bold tm-text">@{{ s.athletes.length }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Total Split</span>
                            <span class="font-bold tm-text">@{{ s.logs.length }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Avg Pace</span>
                            <span class="font-bold tm-green">@{{ formatAvgPaceOfSession(s) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- SECTION 5: HISTORICAL SESSION DETAIL                                      -->
    <!-- ========================================================================= -->
    <section v-if="view === 'history_detail' && selectedHistorySession" class="flex-grow max-w-3xl mx-auto w-full space-y-5">
        <div class="tm-surface border tm-border p-5 sm:p-7 rounded-xl shadow-xl print-card">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b tm-border pb-4 mb-4 gap-3">
                <div>
                    <button @click="changeView('history')" class="no-print tm-surface-2 hover:bg-slate-700/20 border tm-border tm-muted hover:tm-text text-xs font-semibold px-3 py-1.5 rounded-md transition mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        <span>Kembali ke Arsip</span>
                    </button>
                    <h2 class="font-sports-head text-2xl font-bold uppercase tm-text">@{{ selectedHistorySession.name }}</h2>
                    <p class="text-xs tm-muted mt-0.5">📍 @{{ selectedHistorySession.location }} &bull; @{{ selectedHistorySession.workoutType }} (@{{ selectedHistorySession.program.reps }}x@{{ selectedHistorySession.program.distance }}m)</p>
                </div>
                <div class="font-telemetry text-xs tm-muted">
                    @{{ selectedHistorySession.date }} &bull; @{{ selectedHistorySession.time }}
                </div>
            </div>

            <!-- Key metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="border tm-border p-3.5 rounded-lg text-center font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Atlet</span>
                    <span class="text-xl font-bold tm-text">@{{ selectedHistorySession.athletes.length }}</span>
                </div>
                <div class="border tm-border p-3.5 rounded-lg text-center font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Total Reps</span>
                    <span class="text-xl font-bold tm-text">@{{ selectedHistorySession.logs.length }}</span>
                </div>
                <div class="border tm-border p-3.5 rounded-lg text-center font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Avg Pace</span>
                    <span class="text-xl font-bold tm-green">@{{ formatAvgPaceOfSession(selectedHistorySession) }}</span>
                </div>
                <div class="border tm-border p-3.5 rounded-lg text-center font-telemetry" style="background-color: var(--tm-input-bg);">
                    <span class="text-[10px] tm-muted uppercase font-bold block mb-1">Durasi</span>
                    <span class="text-xl font-bold tm-text">@{{ formatTime(selectedHistorySession.program.elapsedTime || 0) }}</span>
                </div>
            </div>

            <!-- Athletes list -->
            <div class="space-y-4">
                <div v-for="(a, index) in selectedHistorySession.athletes" :key="a.name" 
                    class="border tm-border p-4 rounded-xl space-y-3 print-card" style="background-color: var(--tm-input-bg);">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b tm-border pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" 
                                :class="{'bg-emerald-500': a.readiness==='green', 'bg-amber-500': a.readiness==='yellow', 'bg-rose-500': a.readiness==='red'}"></span>
                            <span class="font-sports-head text-lg font-bold uppercase tm-text">@{{ a.name }}</span>
                        </div>
                        <div class="flex gap-4 text-xs font-telemetry tm-muted">
                            <span>Avg Lap: <strong class="tm-text">@{{ getAthleteAvgTime(a).toFixed(1) }}s</strong></span>
                            <span>Consistency: <strong class="tm-green">@{{ getAthleteConsistencyScore(a) }}%</strong></span>
                        </div>
                    </div>

                    <!-- Coach notes review -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 tm-surface p-3 rounded-lg border tm-border text-xs">
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Kondisi</span>
                            <span class="tm-text font-medium">@{{ a.notes.condition || 'Fit' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Teknik</span>
                            <span class="tm-text font-medium">@{{ a.notes.technique || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Cedera</span>
                            <span class="tm-text font-medium">@{{ a.notes.injury || 'Tidak ada' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] tm-muted uppercase font-bold block mb-0.5">Rekomendasi</span>
                            <span class="tm-text font-medium">@{{ a.notes.recommendation || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-2 flex-wrap mt-6 border-t tm-border pt-4 no-print">
                <button @click="copySummary(selectedHistorySession)" class="flex-grow tm-surface-2 hover:bg-slate-700/20 tm-text border tm-border font-semibold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-copy"></i>
                    <span>Salin Laporan</span>
                </button>
                <button @click="shareWhatsApp(selectedHistorySession)" class="flex-grow bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </button>
                <button @click="exportCSV(selectedHistorySession)" class="flex-grow tm-surface-2 hover:bg-slate-700/20 tm-text border tm-border font-semibold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Export CSV</span>
                </button>
                <button @click="printPDF" class="flex-grow tm-surface-2 hover:bg-slate-700/20 tm-text border tm-border font-semibold py-2.5 px-3 rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Print PDF</span>
                </button>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- COACH EVALUATION MODAL                                                    -->
    <!-- ========================================================================= -->
    <div v-if="showNotesModal && activeAthleteForNotes" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 no-print" 
        @click.self="showNotesModal = false">
        <div class="tm-surface border tm-border w-full max-w-md rounded-xl p-5 shadow-2xl space-y-4">
            <div class="flex justify-between items-center border-b tm-border pb-3">
                <div>
                    <span class="text-[10px] font-telemetry uppercase tm-orange font-bold">COACH EVALUATION</span>
                    <h3 class="font-sports-head text-lg font-bold uppercase tm-text">@{{ activeAthleteForNotes.name }}</h3>
                </div>
                <button @click="showNotesModal = false" class="tm-muted hover:tm-text text-xl leading-none">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-semibold uppercase tracking-wider tm-muted block mb-1">Kondisi Atlet</label>
                    <input v-model="coachNotes.condition" type="text" 
                        class="w-full h-10 border tm-border tm-text px-3 rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="e.g. Segar / Lelah / Kaki berat">
                </div>
                <div>
                    <label class="font-semibold uppercase tracking-wider tm-muted block mb-1">Evaluasi Teknik</label>
                    <textarea v-model="coachNotes.technique" rows="2" 
                        class="w-full border tm-border tm-text p-3 rounded-lg focus:outline-none transition resize-none" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="e.g. Langkah kaki stabil, pendaratan forefoot bagus"></textarea>
                </div>
                <div>
                    <label class="font-semibold uppercase tracking-wider tm-muted block mb-1">Rekomendasi Pelatih</label>
                    <input v-model="coachNotes.recommendation" type="text" 
                        class="w-full h-10 border tm-border tm-text px-3 rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="e.g. Pertahankan cadence di paruh akhir">
                </div>
                <div>
                    <label class="font-semibold uppercase tracking-wider tm-muted block mb-1">Catatan Cedera (Jika Ada)</label>
                    <input v-model="coachNotes.injury" type="text" 
                        class="w-full h-10 border tm-border tm-text px-3 rounded-lg focus:outline-none transition" 
                        style="background-color: var(--tm-input-bg);"
                        placeholder="e.g. Nyeri betis kanan ringan / Tidak ada">
                </div>
            </div>

            <button @click="saveCoachNotes" 
                class="w-full h-11 font-sports-head text-sm font-bold uppercase rounded-lg transition"
                style="background-color: var(--tm-orange); color: #000000;">
                Simpan Catatan Pelatih
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STICKY ACTION BARS (LARGE OUTDOOR TOUCH TARGETS - MIN 80PX FOR HERO LAP)  -->
    <!-- ========================================================================= -->
    
    <!-- 1. Setup screen sticky start button (mobile) -->
    <div v-if="view === 'setup'" 
        class="no-print fixed bottom-0 inset-x-0 tm-surface border-t tm-border p-3 shadow-2xl z-50 block sm:hidden">
        <button @click="startSession" 
            class="w-full h-14 font-sports-head text-lg font-bold uppercase rounded-lg flex items-center justify-center gap-2 active:scale-[0.98] transition shadow-lg"
            style="background-color: var(--tm-orange); color: #000000;">
            <i class="fa-solid fa-play text-sm"></i>
            <span>Mulai Sesi Latihan</span>
        </button>
    </div>

    <!-- 2. Live tracking giant outdoor sticky controls -->
    <div v-if="view === 'track'" 
        class="no-print fixed bottom-0 inset-x-0 tm-surface border-t tm-border p-3 shadow-2xl z-50 flex items-center gap-2.5">
        
        <!-- Giant LAP Button (min 80px height for rapid outdoor track tapping) -->
        <div class="flex-grow">
            <button v-if="!hasStarted" @click="startTimerNow"
                class="w-full h-20 bg-emerald-500 hover:bg-emerald-600 text-black font-sports-head text-2xl font-bold uppercase rounded-xl flex items-center justify-center gap-3 active:scale-[0.98] transition shadow-2xl">
                <i class="fa-solid fa-play text-xl"></i>
                <span>START STOPWATCH</span>
            </button>
            <button v-else @click="triggerMainLapButton" :disabled="isPaused"
                class="w-full h-20 font-sports-head text-3xl font-bold uppercase tracking-wider rounded-xl flex items-center justify-center gap-3 active:scale-[0.97] transition shadow-2xl"
                style="background-color: var(--tm-orange); color: #000000;">
                <i class="fa-solid fa-stopwatch text-2xl"></i>
                <span>RECORD LAP</span>
            </button>
        </div>
        
        <!-- Pause / Resume button -->
        <button @click="togglePause" :disabled="!hasStarted"
            :class="isPaused ? 'bg-emerald-500 text-black' : 'tm-surface-2 tm-warning hover:bg-slate-700/20'"
            class="w-16 h-20 rounded-xl border tm-border flex flex-col items-center justify-center gap-1 transition active:scale-95 shrink-0" 
            title="Pause / Resume Timer">
            <i class="fa-solid text-lg" :class="isPaused ? 'fa-play' : 'fa-pause'"></i>
            <span class="text-[9px] font-telemetry uppercase font-bold">@{{ isPaused ? 'RESUME' : 'PAUSE' }}</span>
        </button>

        <!-- Finish Session button -->
        <button @click="finishSession"
            class="w-16 h-20 tm-surface-2 hover:bg-rose-600 hover:text-white border tm-border text-rose-500 rounded-xl flex flex-col items-center justify-center gap-1 transition active:scale-95 shrink-0" 
            title="Selesaikan Sesi Latihan">
            <i class="fa-solid fa-flag-checkered text-lg"></i>
            <span class="text-[9px] font-telemetry uppercase font-bold">FINISH</span>
        </button>
    </div>

</div>

<!-- Vue 3 Engine Code -->
<script>
    const { createApp, ref, reactive, computed, onMounted, nextTick } = Vue;

    createApp({
        setup() {
            // Helper Formatter Functions
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
            const activeTheme = ref('dark');

            let timerInterval = null;
            let lastTime = 0;

            const selectedPresetName = ref('10 x 400 m');

            const presets = [
                { name: '10 x 400 m', reps: 10, distance: 400, rest: 60, pace: '04:00', target: 96 },
                { name: '6 x 800 m', reps: 6, distance: 800, rest: 90, pace: '04:00', target: 192 },
                { name: '5 x 1000 m', reps: 5, distance: 1000, rest: 120, pace: '04:00', target: 240 },
                { name: '3 x 2000 m', reps: 3, distance: 2000, rest: 180, pace: '04:00', target: 480 },
                { name: 'Tempo 30m', reps: 1, distance: 6000, rest: 0, pace: '05:00', target: 1800 },
                { name: '5K Time Trial', reps: 1, distance: 5000, rest: 0, pace: '04:30', target: 1350 },
                { name: 'Cooper 12m', reps: 1, distance: 3000, rest: 0, pace: '04:00', target: 720 },
                { name: 'Custom Run', reps: 5, distance: 400, rest: 60, pace: '04:00', target: 96 }
            ];

            const program = reactive({
                name: 'Sesi Interval Pagi',
                location: '',
                weather: 'Cerah',
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
                condition: 'Fit',
                technique: 'Teknik lari stabil.',
                recommendation: 'Jaga ritme lap awal.',
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
                const valid = setupAthletes.value.filter(a => a.name && a.name.trim() !== '');
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
                        technique: 'Teknik lari terkendali.',
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
                            technique: safeNotes.technique || 'Teknik lari terkendali.',
                            recommendation: safeNotes.recommendation || 'Fokus menjaga ritme lap.',
                            injury: safeNotes.injury || 'Tidak ada'
                        }
                    };
                });
            };

            const renderCharts = () => {
                if (typeof Chart === 'undefined') return;
                try {
                    const isDark = activeTheme.value === 'dark';
                    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
                    const labelColor = isDark ? '#8E95A5' : '#475569';
                    const orangeColor = isDark ? '#FF6B00' : '#EA580C';

                    athleteAnalysis.value.forEach((a, index) => {
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
                                            borderColor: orangeColor,
                                            backgroundColor: isDark ? 'rgba(255, 107, 0, 0.1)' : 'rgba(234, 88, 12, 0.1)',
                                            borderWidth: 2.5,
                                            tension: 0.15,
                                            pointBackgroundColor: orangeColor,
                                            pointRadius: 4,
                                            fill: true
                                        },
                                        {
                                            label: 'Target',
                                            data: Array((a.laps || []).length).fill(a.stats ? a.stats.avgTime : 0),
                                            borderColor: '#EF4444',
                                            borderWidth: 1.5,
                                            borderDash: [4, 4],
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
                                        y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { family: 'JetBrains Mono' } } },
                                        x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { family: 'JetBrains Mono' } } }
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
                if (typeof Chart === 'undefined') return;
                try {
                    const isDark = activeTheme.value === 'dark';
                    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
                    const labelColor = isDark ? '#8E95A5' : '#475569';
                    const orangeColor = isDark ? '#FF6B00' : '#EA580C';

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
                                            borderColor: orangeColor,
                                            backgroundColor: isDark ? 'rgba(255, 107, 0, 0.1)' : 'rgba(234, 88, 12, 0.1)',
                                            borderWidth: 2,
                                            tension: 0.15,
                                            pointBackgroundColor: orangeColor,
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
                                        y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { family: 'JetBrains Mono' } } },
                                        x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { family: 'JetBrains Mono' } } }
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
                if (a.status === 'resting') return `Resting (${Math.ceil(a.restCountdown)}s)`;
                if (a.laps.length === 0) return 'Running';
                
                const lastLap = a.laps[a.laps.length - 1];
                if (lastLap.status === 'on target') return 'On Target';
                if (lastLap.status === 'too fast') return 'Fast Lap';
                if (lastLap.status === 'too slow') return 'Slow Lap';
                return 'Running';
            };

            const getAthleteStatusBadgeClass = (a) => {
                if (a.status === 'completed') return 'border tm-border tm-muted tm-surface-2';
                if (a.status === 'resting') return 'border-amber-500/30 text-amber-500 bg-amber-500/10';
                if (a.laps.length === 0) return 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10';
                
                const lastLap = a.laps[a.laps.length - 1];
                if (lastLap.status === 'on target') return 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10';
                if (lastLap.status === 'too fast') return 'border-orange-500/30 text-orange-500 bg-orange-500/10';
                if (lastLap.status === 'too slow') return 'border-rose-500/30 text-rose-500 bg-rose-500/10';
                return 'border tm-border tm-text tm-surface-2';
            };

            const getDeltaClass = (d) => {
                if (Math.abs(d) <= 1.5) return 'tm-green font-bold';
                return d < -1.5 ? 'tm-orange font-bold' : 'tm-danger font-bold';
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
                                technique: safeNotes.technique || 'Teknik lari terkendali.',
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
                    text += `- Status: ${a.readiness === 'green' ? '🟢 Siap' : (a.readiness === 'yellow' ? '🟡 Lelah' : '🔴 Cedera')}\n`;
                    text += `- Avg Pace: ${stats.avgPace} / km\n`;
                    text += `- Avg Time: ${stats.avgTime.toFixed(1)}s\n`;
                    text += `- Konsistensi: ${stats.consistency}%\n`;
                    text += `- Evaluasi: ${a.notes.technique || '-'}\n`;
                    text += `- Rekomendasi: ${a.notes.recommendation || '-'}\n\n`;
                });

                navigator.clipboard.writeText(text).then(() => {
                    alert('Laporan sesi disalin ke clipboard!');
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
                    text += `Rekomendasi: ${a.notes.recommendation || '-'}\n\n`;
                });

                text += `Detail lengkap via TrackMaster RuangLari.`;
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
                // If user clicks on track but athletes not initialized yet, initialize from setup
                if (newView === 'track' && (!athletes.value || athletes.value.length === 0)) {
                    startSession();
                    return;
                }
                // If user clicks on summary but no analysis generated yet, generate from current state
                if (newView === 'summary') {
                    generateAnalysis();
                    view.value = newView;
                    nextTick(() => {
                        renderCharts();
                    });
                    return;
                }
                view.value = newView;
            };

            // Theme Switching functionality (Light / Dark mode)
            const toggleTheme = () => {
                activeTheme.value = activeTheme.value === 'dark' ? 'light' : 'dark';
                applyTheme();
            };

            const applyTheme = () => {
                localStorage.setItem('tm_theme_v2', activeTheme.value);
                const htmlEl = document.documentElement;
                if (activeTheme.value === 'light') {
                    htmlEl.classList.remove('dark');
                    htmlEl.classList.add('light');
                    document.body.style.backgroundColor = '#F1F4F9';
                } else {
                    htmlEl.classList.remove('light');
                    htmlEl.classList.add('dark');
                    document.body.style.backgroundColor = '#080A0D';
                }
                
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
                // Initialize theme
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
                                            a.notes = { condition: 'Fit', technique: 'Teknik lari terkendali.', recommendation: 'Fokus menjaga ritme lap.', injury: 'Tidak ada' };
                                        }
                                    });
                                }
                            });
                            sessionHistory.value = parsed;
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
                            if (confirm('Lanjutkan sesi latihan interval sebelumnya?')) {
                                Object.assign(program, parsed.program);
                                if (parsed.athletes) {
                                    parsed.athletes.forEach(a => {
                                        if (!a.notes) {
                                            a.notes = { condition: 'Fit', technique: 'Teknik lari terkendali.', recommendation: 'Fokus menjaga ritme lap.', injury: 'Tidak ada' };
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
