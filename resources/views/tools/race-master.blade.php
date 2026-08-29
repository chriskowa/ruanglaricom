<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Race Master Pro - Ruang Lari</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.17.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd@2.2.3"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js"></script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&family=Oswald:wght@500;700;800&family=Orbitron:wght@700;800;900&display=swap');
        @import url('https://cdn.jsdelivr.net/npm/dseg@0.46.0/css/dseg.css');

        body { font-family: 'Inter', sans-serif; }
        .font-mono-numbers { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        .font-dseg { font-family: 'DSEG7-Classic', 'Orbitron', monospace !important; font-style: italic; letter-spacing: 4px; }
        .font-orbitron { font-family: 'Orbitron', monospace !important; letter-spacing: 2px; }

        /* A5 Landscape BIB Styles (210mm x 148mm / Ratio 21 x 14.5 cm) */
        .bib-card { 
            width: 210mm;
            max-width: 100%;
            height: 148mm;
            aspect-ratio: 210 / 148;
            box-sizing: border-box;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .bib-number-hero {
            font-size: 160px !important;
            line-height: 0.82 !important;
            font-family: 'Oswald', sans-serif !important;
            font-weight: 900 !important;
            color: #020617 !important;
            letter-spacing: -3px !important;
            text-align: center !important;
            display: inline-block !important;
        }

        /* Print Styles for A5 Landscape BIB */
        @media print {
            @page { size: A5 landscape; margin: 0; }
            body { background: white !important; color: black !important; margin: 0 !important; padding: 0 !important; }
            body * { visibility: hidden; }
            #bib-print-area, #bib-print-area * { visibility: visible; }
            #bib-print-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
            .bib-card { 
                width: 210mm !important; 
                height: 148mm !important; 
                margin: 0 !important; 
                border: none !important;
                box-shadow: none !important;
                page-break-after: always !important;
            }
            .no-print { display: none !important; }
        }

        .scanner-active { border: 4px solid #10B981; }
        
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        [v-cloak] { display: none !important; }

        /* Vue <transition name="fade"> for BIB detected overlay */
        .fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translate(-50%, -6px); }
        .fade-enter-to, .fade-leave-from { opacity: 1; transform: translate(-50%, 0); }
    </style>

</head>
<body class="bg-slate-100 text-slate-900 min-h-screen transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100">

<div id="app" :class="{'dark': isDarkMode}" v-cloak>

    <!-- TV / JUMBOTRON BROADCAST FULLSCREEN OVERLAY (ADMIN 1) -->
    <div v-if="tvDisplayOpen" class="fixed inset-0 z-[9999] bg-black text-white flex flex-col justify-between p-3.5 sm:p-6 md:p-10 select-none overflow-y-auto sm:overflow-hidden font-sans" style="background-color: #000000 !important; color: #ffffff !important;">
        <!-- Top Status Bar -->
        <div class="border-b border-slate-800 pb-2.5 sm:pb-5 space-y-2.5 sm:space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                <!-- Left: Logo, Race Title, Category -->
                <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                    <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center font-black text-xs sm:text-lg text-white shrink-0">
                        <span v-if="!raceLogoPreviewUrl">RL</span>
                        <img v-else :src="raceLogoPreviewUrl" class="w-full h-full object-contain rounded-xl sm:rounded-2xl" alt="Logo">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                            <span class="text-[9px] sm:text-xs font-bold uppercase tracking-widest text-slate-400">Live Race Display</span>
                            <!-- Room / Session Code for Assistant Admins -->
                            <div v-if="sessionSlug || currentSessionId" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-900 border border-slate-700 text-amber-400 font-mono text-[9px] sm:text-xs font-bold" title="Kode Sesi untuk Admin Satelit / Pembantu">                                
                                <span>KODE: <strong class="text-white tracking-wider">@{{ sessionSlug || currentSessionId }}</strong></span>
                            </div>
                        </div>
                        <div class="text-base sm:text-2xl md:text-3xl font-black text-white uppercase tracking-tight truncate max-w-xs sm:max-w-md md:max-w-xl">
                            @{{ raceName || 'Ruang Lari Official Race' }}
                        </div>
                    </div>
                    <div class="px-2 sm:px-3.5 py-0.5 sm:py-1 rounded-lg sm:rounded-xl bg-indigo-600 text-white font-oswald text-[11px] sm:text-lg font-black uppercase shrink-0">
                        @{{ raceCategory }}
                    </div>
                </div>

                <!-- Right: Action & Utility Buttons (Single Neat Row on Mobile) -->
                <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar py-0.5 w-full sm:w-auto justify-start sm:justify-end">
                    <!-- Host Quick Controls in TV Topbar -->
                    <div v-if="isSessionHost" class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                        <!-- Start / Resume -->
                        <button
                            v-if="!timer.running"
                            type="button"
                            @click="startRace"
                            class="h-8 sm:h-10 px-2.5 sm:px-4 rounded-lg bg-white hover:bg-slate-100 text-slate-950 border border-white font-semibold text-xs flex items-center gap-1.5 transition-colors"
                            :title="timer.elapsed > 0 ? 'Lanjutkan Timer Balapan' : 'Mulai Timer Balapan'"
                        >
                            <i class="fa-solid fa-play text-[10px]"></i>
                            <span>@{{ timer.elapsed > 0 ? 'Resume' : 'Start' }}</span>
                        </button>

                        <!-- Pause -->
                        <button
                            v-else
                            type="button"
                            @click="pauseRace"
                            class="h-8 sm:h-10 px-2.5 sm:px-4 rounded-lg bg-white hover:bg-slate-100 text-slate-950 border border-white font-semibold text-xs flex items-center gap-1.5 transition-colors"
                            title="Jeda Timer Balapan"
                        >
                            <i class="fa-solid fa-pause text-[10px]"></i>
                            <span>Pause</span>
                        </button>

                        <!-- Reset -->
                        <button
                            type="button"
                            @click="resetRace"
                            class="h-8 sm:h-10 px-2.5 sm:px-4 rounded-lg bg-transparent hover:bg-slate-900 text-slate-400 hover:text-white border border-slate-700 font-semibold text-xs flex items-center gap-1.5 transition-colors"
                            title="Reset Timer dan Sesi Balapan"
                        >
                            <i class="fa-solid fa-rotate-left text-[10px]"></i>
                            <span>Reset</span>
                        </button>

                        <!-- Finish Race -->
                        <button
                            type="button"
                            @click="finishRace"
                            class="h-8 sm:h-10 px-2.5 sm:px-4 rounded-lg bg-transparent hover:bg-red-950/40 text-red-400 hover:text-red-300 border border-red-900/70 hover:border-red-800 font-semibold text-xs flex items-center gap-1.5 transition-colors"
                            title="Selesaikan & Simpan Hasil Sesi"
                        >
                            <i class="fa-solid fa-flag-checkered text-[10px]"></i>
                            <span>Finish</span>
                        </button>
                    </div>

                    <!-- Mini Live Stats (Hidden on mobile to keep bar clean) -->
                    <div class="hidden lg:flex items-center border-l border-slate-800 ml-1 pl-3">
                        <!-- Peserta -->
                        <div class="px-3.5 border-r border-slate-800 text-left">
                            <div class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold">Peserta</div>
                            <div class="mt-0.5 font-mono text-base font-semibold text-white tabular-nums">@{{ participants.length }}</div>
                        </div>

                        <!-- Finish -->
                        <div class="px-3.5 border-r border-slate-800 text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <div class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold">Finish</div>
                            </div>
                            <div class="mt-0.5 font-mono text-base font-semibold text-white tabular-nums">@{{ finishedCount }}</div>
                        </div>

                        <!-- On Track -->
                        <div class="px-3.5 border-r border-slate-800 text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                <div class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold">On Track</div>
                            </div>
                            <div class="mt-0.5 font-mono text-base font-semibold text-white tabular-nums">@{{ runningCount }}</div>
                        </div>

                        <!-- DNF -->
                        <div v-if="dnfCount > 0" class="px-3.5 text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                <div class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold">DNF</div>
                            </div>
                            <div class="mt-0.5 font-mono text-base font-semibold text-white tabular-nums">@{{ dnfCount }}</div>
                        </div>
                    </div>

                    <!-- Settings Button -->
                    <button type="button" @click="tvSettingsOpen = !tvSettingsOpen"
                            class="h-8 sm:h-10 px-2.5 sm:px-3.5 rounded-lg font-bold text-xs flex items-center gap-1.5 border transition shrink-0"
                            :class="tvSettingsOpen ? 'bg-indigo-600 text-white border-indigo-500' : 'bg-slate-900 hover:bg-slate-800 text-slate-300 border-slate-700'"
                            title="Pengaturan Ukuran & Gaya Jam">
                        <i class="fa-solid fa-sliders text-[11px]"></i>
                        <span class="hidden sm:inline">Gaya</span>
                    </button>

                    <!-- Close TV Button -->
                    <button type="button" @click="closeTvDisplay" class="h-8 sm:h-10 px-2.5 sm:px-4 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white font-bold text-xs flex items-center gap-1.5 border border-slate-700 transition shrink-0" title="Tutup TV Mode (Esc)">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span class="hidden sm:inline">Tutup</span>
                    </button>
                </div>
            </div>

            <!-- Expandable Mobile-Responsive Settings Drawer for Size Slider, Font, & Color -->
            <div v-if="tvSettingsOpen" class="bg-slate-900 border border-slate-700 rounded-2xl p-3 sm:p-4 shadow-2xl space-y-3 animate-fade-in text-xs">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-bold text-white uppercase tracking-wider text-[11px] flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-400"></i>
                        Pengaturan Tampilan Jam TV
                    </span>
                    <button type="button" @click="tvSettingsOpen = false" class="text-slate-400 hover:text-white font-bold text-xs p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <!-- Slider Ukuran Font -->
                    <div class="space-y-1.5 bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                        <div class="flex justify-between items-center text-[11px] font-bold text-slate-300">
                            <span>Ukuran Jam Timer</span>
                            <span class="text-indigo-400 font-mono font-black text-xs">@{{ tvTimerSizeScale }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-500 font-mono">50%</span>
                            <input type="range" v-model.number="tvTimerSizeScale" min="50" max="180" step="5" class="w-full accent-indigo-500 h-2 bg-slate-800 rounded-lg cursor-pointer">
                            <span class="text-[10px] text-slate-500 font-mono">180%</span>
                        </div>
                    </div>

                    <!-- Jenis Font -->
                    <div class="space-y-1.5 bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                        <label class="block text-[11px] font-bold text-slate-300">Jenis Font Digital</label>
                        <select v-model="tvTimerFont" class="w-full bg-slate-900 text-white font-bold px-3 py-2 rounded-xl border border-slate-700 outline-none text-xs">
                            <option value="dseg">Digital 7-Segment (DSEG)</option>
                            <option value="orbitron">Digital Tech (Orbitron)</option>
                            <option value="mono">Standard Monospace</option>
                        </select>
                    </div>

                    <!-- Warna LED -->
                    <div class="space-y-1.5 bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                        <label class="block text-[11px] font-bold text-slate-300">Warna LED Digital</label>
                        <select v-model="tvTimerColor" class="w-full bg-slate-900 text-white font-bold px-3 py-2 rounded-xl border border-slate-700 outline-none text-xs">
                            <option value="amber">LED Amber (Kuning Emas)</option>
                            <option value="emerald">LED Emerald (Hijau Neon)</option>
                            <option value="red">LED Red (Merah)</option>
                            <option value="white">LED White (Putih Terang)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: Giant Race Clock + Live Finisher Hero Flash -->
        <div class="flex-1 flex flex-col items-center justify-center my-auto py-2 sm:py-6 relative w-full overflow-hidden">
            
            <!-- Main Giant Clock -->
            <div class="text-center w-full max-w-full px-1 sm:px-4">
                <div class="text-[9px] sm:text-xs md:text-sm font-bold uppercase tracking-[0.25em] text-slate-500 mb-2 sm:mb-4">
                    RUANG LARI RACE TIMER
                </div>
                <div class="font-bold tracking-tight leading-none select-none transition-all duration-150 inline-flex items-baseline justify-center max-w-full"
                     :class="{
                        'font-dseg': tvTimerFont === 'dseg',
                        'font-orbitron': tvTimerFont === 'orbitron',
                        'font-mono-numbers': tvTimerFont === 'mono',
                        'text-amber-400': tvTimerColor === 'amber',
                        'text-emerald-400': tvTimerColor === 'emerald',
                        'text-red-500': tvTimerColor === 'red',
                        'text-white': tvTimerColor === 'white',
                     }"
                     :style="{
                        fontSize: 'clamp(2.2rem, 11vw, ' + (15 * tvTimerSizeScale / 100) + 'rem)',
                        lineHeight: '0.95'
                     }">
                    <span class="tabular-nums font-bold">@{{ tvTimerParts.main }}</span>
                    <span class="opacity-70 font-semibold tracking-normal ml-1 sm:ml-2 font-mono-numbers tabular-nums"
                          style="font-size: clamp(1rem, 4.2vw, 4.5rem); line-height: 1;">
                        @{{ tvTimerParts.ms }}
                    </span>
                </div>

                <!-- Live Results QR Code Badge for Runners / Spectators -->
                <div class="mt-3.5 sm:mt-8 flex justify-center sm:justify-end">
                    <div class="flex items-center gap-2.5 sm:gap-3 p-1.5 sm:p-2.5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl" style="background-color: #0f172a !important;">
                        <div class="p-1 sm:p-1.5 bg-white rounded-xl shadow shrink-0 flex items-center justify-center">
                            <div id="tvResultsQrCode" class="w-14 h-14 sm:w-20 sm:h-20 flex items-center justify-center"></div>
                        </div>
                        <div class="text-left pr-2 min-w-0">
                            <div class="text-[11px] sm:text-sm font-bold text-white leading-tight">
                                Scan Live Results
                            </div>
                            <div class="text-[9px] sm:text-xs text-orange-400 font-mono font-bold mt-0.5 truncate max-w-[140px] sm:max-w-[240px]">
                                @{{ tvResultsShortUrl }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Realtime Finisher Flash Hero Overlay (appears on crossing) -->
            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                <div v-if="tvLatestFinisher" class="absolute inset-x-3 max-w-3xl mx-auto p-4 sm:p-6 rounded-3xl bg-slate-900 border-2 border-emerald-500 shadow-2xl text-center animate-fade-in z-20" style="background-color: #0f172a !important;">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-950 text-emerald-300 text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-2 border border-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        BARU SAJA FINISH
                    </div>
                    <div class="text-2xl sm:text-5xl font-black text-white uppercase tracking-tight">
                        <span class="text-indigo-400 font-oswald mr-1.5">#@{{ tvLatestFinisher.bib }}</span>
                        @{{ tvLatestFinisher.name }}
                    </div>
                    <div class="mt-2.5 flex items-center justify-center gap-4 sm:gap-6 text-xs sm:text-lg font-mono">
                        <span class="text-slate-200">Waktu: <strong class="text-emerald-400">@{{ tvLatestFinisher.time }}</strong></span>
                        <span v-if="tvLatestFinisher.rank" class="text-slate-200">Peringkat: <strong class="text-white">#@{{ tvLatestFinisher.rank }}</strong></span>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Bottom: Top 5 Live Leaderboard Strip -->
        <div class="border-t border-slate-800 pt-2.5 sm:pt-4 mt-2 sm:mt-0">
            <div class="flex items-center justify-between mb-1.5 sm:mb-3 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                <div class="flex items-center gap-2">
                    <span>Top 5 Finisher Leaderboard</span>
                </div>
                <div class="text-[9px] sm:text-[11px] font-mono text-slate-500">Live Auto-Update</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-1.5 sm:gap-3">
                <div v-for="(p, idx) in top5Results" :key="p.id" class="bg-slate-900 border border-slate-800 p-2 sm:p-3 rounded-xl sm:rounded-2xl flex items-center gap-2.5 sm:gap-3">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl flex items-center justify-center font-black text-xs sm:text-sm shrink-0"
                        :class="{
                            'bg-amber-400 text-slate-950': idx === 0,
                            'bg-slate-300 text-slate-950': idx === 1,
                            'bg-amber-700 text-white': idx === 2,
                            'bg-slate-800 text-slate-300': idx > 2
                        }">
                        @{{ idx + 1 }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-white text-[11px] sm:text-xs truncate">
                            <span class="text-indigo-400 font-oswald mr-1">#@{{ p.bib }}</span>
                            @{{ p.name }}
                        </div>
                        <div class="text-[10px] sm:text-[11px] font-mono text-emerald-400 font-bold mt-0.5">
                            @{{ formatTime(p.totalTime) }}
                        </div>
                    </div>
                </div>
                <!-- Empty Placeholder if < 5 finishers (on mobile show max 2 placeholders if 0 finishers to save vertical space) -->
                <div v-for="i in Math.max(0, 5 - top5Results.length)" :key="'empty-'+i" 
                     :class="{'hidden sm:flex': i > 2 && top5Results.length === 0}"
                     class="bg-slate-900 border border-slate-800 p-2 sm:p-3 rounded-xl sm:rounded-2xl flex items-center gap-2.5 sm:gap-3 opacity-40">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-[10px] sm:text-xs font-bold">
                        @{{ top5Results.length + i }}
                    </div>
                    <div class="text-[11px] sm:text-xs text-slate-400 font-mono">Menunggu Finisher...</div>
                </div>
            </div>
        </div>
    </div>

    <header class="bg-white shadow-sm sticky top-0 z-50 no-print dark:bg-slate-800 dark:border-b dark:border-slate-700 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center relative">
            <div class="flex items-center gap-2">
                <a href="{{ route('tools.index') }}" class="text-slate-400 hover:text-indigo-600 mr-2 dark:text-slate-500 dark:hover:text-indigo-400"><i class="fa-solid fa-arrow-left"></i></a>
                <i class="fa-solid fa-stopwatch text-indigo-600 text-2xl dark:text-indigo-400"></i>
                <h1 class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">Race Master <span class="text-indigo-600 dark:text-indigo-400">Pro</span></h1>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Desktop Nav -->
                <div class="hidden md:flex bg-slate-100 rounded-lg p-1 dark:bg-slate-700">
                    <button @click="currentView = 'setup'" :class="{'bg-white shadow-sm text-indigo-700 dark:bg-slate-600 dark:text-indigo-300': currentView==='setup', 'text-slate-500 dark:text-slate-400': currentView!=='setup'}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap">Setup</button>
                    <button @click="currentView = 'bibs'" :class="{'bg-white shadow-sm text-indigo-700 dark:bg-slate-600 dark:text-indigo-300': currentView==='bibs', 'text-slate-500 dark:text-slate-400': currentView!=='bibs'}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap">BIBs</button>
                    <button @click="currentView = 'race'" :class="{'bg-white shadow-sm text-indigo-700 dark:bg-slate-600 dark:text-indigo-300': currentView==='race', 'text-slate-500 dark:text-slate-400': currentView!=='race'}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap">Race</button>
                    <button @click="currentView = 'results'" :class="{'bg-white shadow-sm text-indigo-700 dark:bg-slate-600 dark:text-indigo-300': currentView==='results', 'text-slate-500 dark:text-slate-400': currentView!=='results'}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap">Results</button>
                    <button @click="openTvDisplay" class="px-3 py-1.5 rounded-md text-sm font-bold transition-all whitespace-nowrap bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-1.5 shadow-sm ml-1">
                        <i class="fa-solid fa-tv text-xs"></i>
                        <span>Layar TV</span>
                    </button>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="toggleDarkMode" class="text-slate-400 hover:text-indigo-600 dark:text-slate-500 dark:hover:text-indigo-400 transition-colors w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid" :class="isDarkMode ? 'fa-sun' : 'fa-moon'"></i>
                </button>

                <!-- Mobile Burger -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 w-10 h-10 flex items-center justify-center transition-colors rounded-full hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu Dropdown -->
        <div v-show="mobileMenuOpen" class="md:hidden border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg animate-fade-in">
             <div class="p-2 space-y-1">
                 <button @click="currentView = 'setup'; mobileMenuOpen = false" :class="currentView==='setup' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="block w-full text-left px-4 py-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-cog w-6"></i> Setup
                 </button>
                 <button @click="currentView = 'bibs'; mobileMenuOpen = false" :class="currentView==='bibs' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="block w-full text-left px-4 py-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-ticket w-6"></i> BIBs
                 </button>
                 <button @click="currentView = 'race'; mobileMenuOpen = false" :class="currentView==='race' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="block w-full text-left px-4 py-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-stopwatch w-6"></i> Race
                 </button>
                 <button @click="currentView = 'results'; mobileMenuOpen = false" :class="currentView==='results' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'" class="block w-full text-left px-4 py-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-list-ol w-6"></i> Results
                 </button>
                 <button @click="openTvDisplay(); mobileMenuOpen = false" class="block w-full text-left px-4 py-3 rounded-xl font-bold transition-colors text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30">
                    <i class="fa-solid fa-tv w-6"></i> Mode Layar TV (Fullscreen)
                 </button>
             </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-4">

        <div v-if="currentView === 'setup'" class="space-y-6 animate-fade-in">
            <!-- Multi-Admin Live Session / Room Sync Card -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-satellite-dish"></i></span>
                            Sinkronisasi Multi-Admin (Live Room Sync)
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Hubungkan Admin 1 (TV Display), Admin 2 (Kamera Gate), Admin 3 (Spotter Numpad), dan Admin 4 (Marshal) ke sesi balap yang sama dari laptop/HP masing-masing.
                        </p>
                    </div>
                    <div v-if="sessionSyncActive" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-xs font-bold border border-emerald-300 dark:border-emerald-800 self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Live Sync Aktif</span>
                        <span class="text-[10px] opacity-70">(@{{ sessionSyncLastUpdated }})</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left: Current Active Room & Share Link -->
                    <div class="p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Sesi Lomba Saat Ini:</div>
                            <button v-if="!sessionSlug && !currentSessionId" type="button" @click="initializeRaceSession(false)" :disabled="initializingSession"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i v-if="initializingSession" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                <i v-else class="fa-solid fa-play text-xs"></i>
                                <span>@{{ initializingSession ? 'Menyiapkan...' : 'Buat Sesi Sekarang' }}</span>
                            </button>
                        </div>

                        <div v-if="sessionSlug || currentSessionId" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <div class="flex-1 font-mono font-black text-sm sm:text-base px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white truncate">
                                @{{ sessionSlug || currentSessionId }}
                            </div>
                            <div class="grid grid-cols-2 sm:flex items-center gap-2">
                                <button type="button" @click="copySessionShareUrl" 
                                    class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5" title="Salin link untuk admin/spotter">
                                    <i class="fa-solid fa-copy"></i>
                                    <span>Salin Sesi</span>
                                </button>
                                <button type="button" @click="copyTvDisplayUrl" 
                                    class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5" title="Salin link yang otomatis fullscreen untuk layar TV">
                                    <i class="fa-solid fa-tv"></i>
                                    <span>URL TV Display</span>
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
                            <span>Sesi belum dibuat. Buat sesi terlebih dahulu untuk membagikan link ke Layar TV & Admin sebelum timer jalan.</span>
                            <button type="button" @click="initializeRaceSession(false)" :disabled="initializingSession" 
                                class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shrink-0">
                                Buat Sesi
                            </button>
                        </div>

                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                            <span>Link TV Display akan langsung membuka layar TV fullscreen saat dibuka.</span>
                            <button v-if="sessionSlug || currentSessionId" type="button" @click="openTvDisplay" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                Buka TV Sekarang &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Right: Join Other Session by Room Code / URL -->
                    <div class="p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Gabung ke Sesi Lain (Satellite Admin):</div>
                        <form @submit.prevent="joinLiveSession(sessionRoomInput)" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <input v-model="sessionRoomInput" type="text" placeholder="Masukkan Kode / Slug Sesi..." 
                                class="flex-1 px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl font-mono text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition shrink-0 flex items-center justify-center gap-1.5 border border-slate-700">
                                <i class="fa-solid fa-right-to-bracket"></i>
                                <span>Gabung Sesi</span>
                            </button>
                        </form>
                        <div v-if="sessionSyncError" class="text-xs text-red-500 font-bold">
                            @{{ sessionSyncError }}
                        </div>
                        <div v-else class="text-[11px] text-slate-500 dark:text-slate-400">
                            Untuk laptop TV atau admin kedua yang ingin bergabung ke sesi lomba yang sudah dibuat master.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hubungkan dengan Event EO RuangLari -->
            <div v-if="isAuthenticated" class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-calendar-check"></i></span>
                            Impor Peserta dari Event EO RuangLari
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Pilih event yang kamu kelola untuk mengimpor seluruh peserta resmi beserta nomor BIB secara otomatis ke Race Master.
                        </p>
                    </div>
                    <span v-if="eoEventsLoading" class="text-xs text-indigo-500 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-circle-notch fa-spin"></i> Memuat Event...
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2 relative" ref="eoAutocompleteContainer">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1 dark:text-slate-400">Pilih Event EO</label>
                        <div class="relative">
                            <div class="relative flex items-center">
                                <input 
                                    v-model="eoEventSearchQuery"
                                    @focus="eoDropdownOpen = true"
                                    @input="onEoSearchInput"
                                    type="text" 
                                    placeholder="Ketik untuk mencari event EO..." 
                                    class="w-full p-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors"
                                    autocomplete="off"
                                >
                                <button 
                                    v-if="selectedEoEventId || eoEventSearchQuery" 
                                    type="button" 
                                    @click="clearSelectedEoEvent"
                                    class="absolute right-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm p-1"
                                    title="Hapus pilihan"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <div v-else class="absolute right-3 text-slate-400 pointer-events-none text-xs">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>
                            </div>

                            <!-- Autocomplete Dropdown List -->
                            <div 
                                v-if="eoDropdownOpen" 
                                class="absolute z-50 left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <div 
                                    v-for="ev in filteredEoEvents" 
                                    :key="ev.id" 
                                    @click="selectEoEvent(ev)"
                                    class="p-3 hover:bg-indigo-50 dark:hover:bg-slate-800 cursor-pointer transition flex items-center justify-between gap-2"
                                    :class="{'bg-indigo-50 dark:bg-slate-800 font-bold text-indigo-700 dark:text-indigo-400': selectedEoEventId == ev.id}"
                                >
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                            @{{ ev.name }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                            <span>Tanggal: @{{ ev.start_at || 'Belum ada tanggal' }}</span>
                                            <span v-if="ev.location">• @{{ ev.location }}</span>
                                        </div>
                                    </div>
                                    <div v-if="ev.categories && ev.categories.length" class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                                        @{{ ev.categories.length }} Kategori
                                    </div>
                                </div>

                                <div v-if="filteredEoEvents.length === 0" class="p-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                    Tidak ada event yang sesuai dengan pencarian "@{{ eoEventSearchQuery }}".
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedEoEvent">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1 dark:text-slate-400">Kategori Event</label>
                        <select v-model="selectedEoCategoryId" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                            <option value="all">Semua Kategori</option>
                            <option v-for="cat in selectedEoEvent.categories" :key="cat.id" :value="cat.id">
                                @{{ cat.name }} (@{{ cat.distance_km ? cat.distance_km + 'KM' : '' }})
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="selectedEoEventId" class="mt-4 flex justify-end">
                    <button type="button" @click="importEoEventParticipants" :disabled="importingEoParticipants" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        <i v-if="importingEoParticipants" class="fa-solid fa-circle-notch fa-spin"></i>
                        <i v-else class="fa-solid fa-file-import"></i>
                        <span>Impor Peserta ke Race Master</span>
                    </button>
                </div>
            </div>

            <div v-if="existingRaces.length > 0" class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <h2 class="text-base sm:text-lg font-bold mb-3 text-slate-900 dark:text-white">Load Existing Race</h2>
                 <select @change="selectExistingRace($event.target.value)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                    <option value="">-- Pilih Race Sebelumnya --</option>
                    <option v-for="r in existingRaces" :value="r.id">@{{ r.name }} (@{{ r.created_at }})</option>
                </select>
            </div>

            <!-- 1. Konfigurasi Race -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <h2 class="text-base sm:text-lg font-bold mb-4 text-slate-900 dark:text-white">1. Konfigurasi Race</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Nama Race</label>
                        <input v-model="raceName" placeholder="Minimal 3 karakter" type="text" maxlength="100" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                        <div class="mt-1 text-[11px] text-slate-400">Disimpan ke database saat Start Race.</div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Logo Race (PNG/JPG, max 2MB)</label>
                        <input @change="onLogoChange" type="file" accept="image/png,image/jpeg" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-xs dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                        <div v-if="raceLogoPreviewUrl" class="mt-2 flex items-center gap-3">
                            <img :src="raceLogoPreviewUrl" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700" alt="Logo preview">
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">@{{ raceLogoFileName }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Kategori Jarak</label>
                        <select v-model="raceCategory" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                            <option v-for="cat in categories" :value="cat">@{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Jarak (KM)</label>
                        <input v-model="raceDistanceKm" placeholder="Contoh: 5, 10, 21.1, 42.195" type="number" step="0.001" min="0.1" max="999.999" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                        <div class="mt-1 text-[11px] text-slate-400">Dipakai untuk hitung pace/kecepatan pada poster.</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-sm text-slate-600 bg-slate-50 p-3.5 rounded-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 flex items-center justify-between">
                            <span class="font-medium">Total Peserta Terdaftar:</span>
                            <span class="font-black text-indigo-600 text-lg dark:text-indigo-400 font-mono">@{{ participants.length }} Peserta</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Tambah Peserta & Biometrik Wajah -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">2. Tambah Peserta & Biometrik Wajah</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan peserta secara manual satu per satu, impor massal via file CSV, atau gunakan biometrik wajah AI.</p>
                    </div>
                </div>

                <!-- CSV Batch Import Action Box -->
                <div class="p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-file-csv text-indigo-600 dark:text-indigo-400 text-base"></i>
                            <span>Impor Massal Peserta via CSV</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Punya daftar peserta di Excel / Spreadsheet? Download format CSV, isi kolomnya, lalu upload langsung ke sini.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:flex items-center gap-2 shrink-0">
                        <button type="button" @click="downloadCsvSample" class="px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center gap-1.5 transition border border-slate-300 dark:border-slate-700" title="Download Template CSV">
                            <i class="fa-solid fa-download text-xs"></i>
                            <span>Download CSV</span>
                        </button>
                        <label class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition shadow-sm" title="Upload File CSV Peserta">
                            <i class="fa-solid fa-file-import text-xs"></i>
                            <span>Upload CSV</span>
                            <input type="file" accept=".csv,text/csv" @change="onCsvUpload" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Face Photo Capture & Multi-Angle Biometric Enrollment Bar -->
                <div class="p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 mb-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full border-2 border-indigo-500 overflow-hidden bg-slate-200 dark:bg-slate-800 flex items-center justify-center shrink-0 shadow-inner relative">
                            <img v-if="newFacePhoto" :src="newFacePhoto" class="w-full h-full object-cover" alt="Avatar">
                            <i v-else class="fa-solid fa-user text-slate-400 text-base"></i>
                            <span v-if="enrolledAngleCount > 0" class="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-emerald-600 text-white text-[9px] font-black flex items-center justify-center border border-white dark:border-slate-900">
                                @{{ enrolledAngleCount }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                <span>Biometrik Wajah AI Multi-Angle</span>
                                <span v-if="enrolledAngleCount === 3" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    3 Sudut (Optimal)
                                </span>
                                <span v-else-if="enrolledAngleCount > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800">
                                    @{{ enrolledAngleCount }} Sudut
                                </span>
                            </div>
                            <div v-if="faceModelLoading" class="text-[11px] text-indigo-500 font-medium flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-circle-notch fa-spin"></i> Memuat AI Face Model...
                            </div>
                            <div v-else-if="newFaceProcessing" class="text-[11px] text-indigo-500 font-medium flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-circle-notch fa-spin"></i> Mengekstrak Biometrik Wajah...
                            </div>
                            <div v-else-if="enrolledAngleCount > 0" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-circle-check"></i> Terdaftar @{{ enrolledAngleCount }} Sudut (Depan, Kanan, Kiri)
                            </div>
                            <div v-else class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Ambil 3 sudut (depan, kanan, kiri) agar deteksi saat finish tetap akurat.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" @click="openFaceCaptureModal('front')" class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                            <i class="fa-solid fa-camera"></i> <span>Kamera (3 Sudut)</span>
                        </button>
                        <label class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition">
                            <i class="fa-solid fa-upload"></i> <span>Upload Foto</span>
                            <input type="file" accept="image/*" @change="onFacePhotoUpload" class="hidden">
                        </label>
                        <button v-if="newFacePhoto" type="button" @click="clearNewFacePhoto" class="px-3 py-2 rounded-xl text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 text-xs transition" title="Hapus Foto">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Single Participant Input Form -->
                <div class="space-y-3 mb-5 p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Nomor BIB</label>
                            <input v-model="newBib" @keyup.enter="focusName" ref="inputBib" placeholder="Contoh: 1001 atau M-1001" type="text" class="w-full p-2.5 sm:p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-base dark:bg-slate-900 dark:border-slate-700 dark:text-white transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Nama Lengkap Peserta</label>
                            <input v-model="newName" @keyup.enter="addParticipant" ref="inputName" placeholder="Ketik nama pelari..." type="text" class="w-full p-2.5 sm:p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-bold text-base dark:bg-slate-900 dark:border-slate-700 dark:text-white transition">
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Prediksi Waktu (Jam : Menit : Detik)</label>
                            <div class="flex gap-1.5 items-center">
                                <input v-model="newPredictedHH" @keyup.enter="addParticipant" placeholder="HH" type="number" min="0" max="99" class="w-full p-2.5 sm:p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-center dark:bg-slate-900 dark:border-slate-700 dark:text-white transition" title="Jam">
                                <span class="text-slate-400 font-bold">:</span>
                                <input v-model="newPredictedMM" @keyup.enter="addParticipant" placeholder="MM" type="number" min="0" max="59" class="w-full p-2.5 sm:p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-center dark:bg-slate-900 dark:border-slate-700 dark:text-white transition" title="Menit">
                                <span class="text-slate-400 font-bold">:</span>
                                <input v-model="newPredictedSS" @keyup.enter="addParticipant" placeholder="SS" type="number" min="0" max="59" class="w-full p-2.5 sm:p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-center dark:bg-slate-900 dark:border-slate-700 dark:text-white transition" title="Detik">
                            </div>
                        </div>

                        <button @click="addParticipant" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Peserta</span>
                        </button>
                    </div>
                </div>

                <!-- Participant Cards on Mobile -->
                <div class="sm:hidden space-y-2.5 max-h-96 overflow-y-auto pr-0.5">
                    <div v-for="(p, index) in participants" :key="p.id" class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-800 flex items-center justify-center border border-slate-300 dark:border-slate-600 shrink-0">
                                <img v-if="p.photoUrl" :src="p.photoUrl" class="w-full h-full object-cover" alt="Foto">
                                <i v-else class="fa-solid fa-user text-slate-400 text-xs"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-sm text-indigo-600 dark:text-indigo-400">#@{{ p.bib }}</span>
                                    <span class="font-bold text-slate-900 dark:text-white text-sm truncate">@{{ p.name }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span>Prediksi: @{{ p.predictedTimeMs ? formatTime(p.predictedTimeMs) : '-' }}</span>
                                    <span v-if="Array.isArray(p.faceDescriptors) && p.faceDescriptors.length >= 3" class="text-emerald-500 font-bold">• 3 Sudut AI</span>
                                    <span v-else-if="p.faceDescriptor" class="text-indigo-400 font-bold">• 1 Sudut AI</span>
                                </div>
                            </div>
                        </div>
                        <button @click="removeParticipant(index)" class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 flex items-center justify-center shrink-0 transition" title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                    <div v-if="participants.length === 0" class="p-8 text-center text-slate-400 text-xs">
                        Belum ada peserta terdaftar.
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-auto max-h-96 rounded-xl border border-slate-100 dark:border-slate-700">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0 z-10">
                            <tr class="text-slate-400 text-xs uppercase border-b border-slate-100 dark:border-slate-700">
                                <th class="p-3 font-bold">Foto / Face AI</th>
                                <th class="p-3 font-bold">BIB</th>
                                <th class="p-3 font-bold">Nama</th>
                                <th class="p-3 font-bold">Prediksi</th>
                                <th class="p-3 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                            <tr v-for="(p, index) in participants" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center border border-slate-300 dark:border-slate-600 shrink-0">
                                            <img v-if="p.photoUrl" :src="p.photoUrl" class="w-full h-full object-cover" alt="Foto">
                                            <i v-else class="fa-solid fa-user text-slate-400 text-xs"></i>
                                        </div>
                                        <span v-if="Array.isArray(p.faceDescriptors) && p.faceDescriptors.length >= 3" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            3 Sudut AI
                                        </span>
                                        <span v-else-if="p.faceDescriptor || (Array.isArray(p.faceDescriptors) && p.faceDescriptors.length > 0)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800">
                                            1 Sudut AI
                                        </span>
                                        <span v-else class="text-[10px] text-slate-400">
                                            Tanpa Wajah
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3 font-mono font-bold text-base dark:text-white">@{{ p.bib }}</td>
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-200">@{{ p.name }}</td>
                                <td class="p-3 font-mono text-xs text-slate-600 dark:text-slate-400">@{{ p.predictedTimeMs ? formatTime(p.predictedTimeMs) : '-' }}</td>
                                <td class="p-3 text-center">
                                    <button @click="removeParticipant(index)" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr v-if="participants.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-400">Belum ada peserta.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-end mt-4">
                <button @click="goToBibs" class="w-full sm:w-auto bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 px-6 py-3 rounded-xl font-bold hover:bg-black transition flex items-center justify-center gap-2">
                    <span>Lanjut: Generate BIB</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div v-show="currentView === 'bibs'" class="animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 no-print gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Preview BIB (A5 Landscape 21 x 14.5 cm)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Format standar nomor dada lomba lari horisontal siap cetak.</p>
                </div>
                <div class="flex gap-3 flex-wrap justify-center">
                    <button @click="currentView = 'setup'" class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium px-4 transition">Kembali</button>
                    <button @click="printBibs" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-200 dark:shadow-none hover:bg-indigo-700 transition">
                        <i class="fa-solid fa-print mr-2"></i> Print / Download PDF
                    </button>
                    <button @click="currentView = 'race'" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-200 dark:shadow-none hover:bg-emerald-700 transition">
                        Siap Race <i class="fa-solid fa-flag-checkered ml-2"></i>
                    </button>
                </div>
            </div>

            <div id="bib-print-area" class="grid grid-cols-1 gap-8 justify-items-center overflow-x-auto pb-12">
                <div v-for="p in participants" :key="p.id" class="bib-card bg-white border-2 border-slate-900 shadow-2xl rounded-2xl p-5 relative overflow-hidden flex flex-col justify-between select-none text-slate-900">
                    <!-- Corner Safety Pin Hole Guide Markers -->
                    <div class="absolute top-2 left-2 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-[10px] text-slate-400 font-mono pointer-events-none">+</div>
                    <div class="absolute top-2 right-2 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-[10px] text-slate-400 font-mono pointer-events-none">+</div>
                    <div class="absolute bottom-2 left-2 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-[10px] text-slate-400 font-mono pointer-events-none">+</div>
                    <div class="absolute bottom-2 right-2 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-[10px] text-slate-400 font-mono pointer-events-none">+</div>

                    <!-- Top Header: Race Name, Category Badge, and Logo -->
                    <div class="flex items-center justify-between border-b-2 border-slate-900 pb-2">
                        <div class="flex items-center gap-3.5">
                            <img v-if="raceLogoPreviewUrl" :src="raceLogoPreviewUrl" class="w-12 h-12 object-contain rounded-lg" alt="Logo">
                            <div v-else class="w-12 h-12 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-sm">RL</div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Official Race BIB</div>
                                <div class="text-xl sm:text-2xl font-black text-slate-900 uppercase truncate max-w-sm">@{{ raceName || 'RuangLari Race' }}</div>
                            </div>
                        </div>
                        <div class="px-6 py-2 rounded-xl bg-slate-900 text-white font-oswald text-2xl sm:text-3xl font-black uppercase tracking-wider shadow-sm">
                            @{{ raceCategory }}
                        </div>
                    </div>

                    <!-- Center Section: 50:50 Split - Massive BIB Number Left & Huge QR Code Right -->
                    <div class="grid grid-cols-2 items-center gap-6 flex-1 w-full my-auto px-2 py-0">
                        <div class="flex items-center justify-center text-center h-full">
                            <div class="bib-number-hero select-all">
                                @{{ p.bib }}
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-center h-full">
                            <div :id="'qrcode-' + p.bib" class="p-3.5 bg-white rounded-2xl border-2 border-slate-900 shadow-lg flex items-center justify-center"></div>
                            <div class="text-xs font-mono font-bold text-slate-700 uppercase mt-2 tracking-wider">Scan for Timing</div>
                        </div>
                    </div>

                    <!-- Bottom Footer: Runner Name & Verification Hash -->
                    <div class="border-t-2 border-slate-900 pt-2 flex items-center justify-between">
                        <div class="min-w-0 flex-1 pr-4">
                            <div class="text-[11px] text-slate-500 font-bold uppercase">Nama Peserta</div>
                            <div class="text-2xl sm:text-4xl font-black text-slate-900 uppercase truncate font-sans tracking-tight">@{{ p.name }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs font-mono text-slate-400">ruanglari.com</div>
                            <div class="text-sm font-mono font-bold text-slate-800">ID: @{{ String(p.id).substring(0, 8) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="currentView === 'race'" class="space-y-4">
            <!-- Race Timer & Control Console -->
            <div class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm sticky top-[68px] z-40 space-y-3 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-center sm:text-left">
                        <div class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-widest">Official Race Clock</div>
                        <div class="font-mono text-4xl sm:text-5xl md:text-6xl font-black text-slate-950 dark:text-white tracking-wider">
                            @{{ formattedTime }}
                        </div>
                    </div>

                    <!-- Action Controls -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <!-- Host / Satellite Status Badge -->
                        <div v-if="!isSessionHost" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[11px] font-bold text-center border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-satellite-dish text-indigo-500"></i>
                            <span>Station Satelit (Viewer)</span>
                        </div>

                        <!-- Primary Start/Pause Button (Host Only or Local) -->
                        <button v-if="!timer.running && isSessionHost" @click="startRace" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm" :title="timer.elapsed > 0 ? 'Resume Timer' : 'Start Timer'">
                            <i class="fa-solid fa-play text-xs"></i>
                            <span>@{{ timer.elapsed > 0 ? 'Resume Timer' : 'Start Timer' }}</span>
                        </button>
                        <button v-if="timer.running && isSessionHost" @click="pauseRace" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm" title="Pause Timer">
                            <i class="fa-solid fa-pause text-xs"></i>
                            <span>Pause</span>
                        </button>

                        <!-- Secondary Actions Grid -->
                        <div class="grid grid-cols-3 sm:flex items-center gap-1.5 w-full sm:w-auto">
                            <button v-if="isSessionHost" @click="resetRace" 
                                class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition" title="Reset Total Timer (Khusus Host)">
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                <span>Reset</span>
                            </button>
                            <button @click="toggleScanner" 
                                :class="camera.active ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'" 
                                class="px-3 py-2 rounded-xl border font-bold text-xs flex items-center justify-center gap-1.5 transition" title="Buka/Tutup Kamera AI">
                                <i class="fa-solid fa-camera text-xs"></i>
                                <span>Kamera</span>
                            </button>
                            <button @click="openTvDisplay" 
                                class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm" title="Tampilkan Layar TV Fullscreen (Admin 1)">
                                <i class="fa-solid fa-tv text-xs"></i>
                                <span>Layar</span>
                            </button>
                            <button type="button" @click="copyTvDisplayUrl" 
                                class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition" title="Salin URL Layar TV (Otomatis Fullscreen)">
                                <i class="fa-solid fa-share-nodes text-xs"></i>
                                <span>URL TV</span>
                            </button>
                        </div>
                        
                        <button v-if="timer.elapsed > 0 && isSessionHost" @click="finishRace" 
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm" title="Finish Sesi (Khusus Host)">
                            <i class="fa-solid fa-flag-checkered text-xs"></i>
                            <span>Finish Sesi</span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Instant Notification & Duplicate Validation Alert Banner -->
            <div v-if="manualValidationAlert.show" class="rounded-2xl p-4 transition-all shadow-xl flex items-start justify-between gap-3 animate-fade-in"
                :class="{
                    'bg-red-950 border-2 border-red-600 text-red-100': manualValidationAlert.type === 'error',
                    'bg-amber-950 border-2 border-amber-500 text-amber-100': manualValidationAlert.type === 'warning',
                    'bg-emerald-950 border-2 border-emerald-500 text-emerald-100': manualValidationAlert.type === 'success',
                    'bg-indigo-950 border-2 border-indigo-500 text-indigo-100': manualValidationAlert.type === 'info'
                }">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                        :class="{
                            'bg-red-600 text-white': manualValidationAlert.type === 'error',
                            'bg-amber-500 text-slate-950': manualValidationAlert.type === 'warning',
                            'bg-emerald-500 text-slate-950': manualValidationAlert.type === 'success',
                            'bg-indigo-500 text-white': manualValidationAlert.type === 'info'
                        }">
                        <i class="fa-solid" :class="{
                            'fa-triangle-exclamation': manualValidationAlert.type === 'warning' || manualValidationAlert.type === 'error',
                            'fa-check': manualValidationAlert.type === 'success',
                            'fa-info': manualValidationAlert.type === 'info'
                        }"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm sm:text-base leading-tight">
                            @{{ manualValidationAlert.message }}
                        </div>
                        <div v-if="manualValidationAlert.bib" class="text-xs opacity-80 mt-1 font-mono">
                            BIB: #@{{ manualValidationAlert.bib }} <span v-if="manualValidationAlert.name">• Nama: @{{ manualValidationAlert.name }}</span> <span v-if="manualValidationAlert.time">• Waktu: @{{ manualValidationAlert.time }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="dismissAlert" class="text-slate-400 hover:text-white p-1 text-base shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Quick Manual BIB Timing & Rapid Entry Engine Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm transition-colors space-y-3.5">
                
                <!-- Rapid Spotter Helper Controls: Prefix Lock & Auto-Submit (Swipeable on Mobile) -->
                <div class="space-y-2 pb-3 border-b border-slate-100 dark:border-slate-800 text-xs">
                    <!-- Prefix Selection Row -->
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase shrink-0">Prefix:</span>
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <button type="button" @click="lockedBibPrefix = ''"
                                :class="lockedBibPrefix === '' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-950 font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs transition shrink-0 border border-transparent dark:border-slate-700">
                                Tanpa Prefix
                            </button>
                            
                            <template v-if="detectedBibPrefixes.length > 0">
                                <button v-for="pf in detectedBibPrefixes" :key="pf" type="button" @click="lockedBibPrefix = (lockedBibPrefix === pf ? '' : pf)"
                                    :class="lockedBibPrefix === pf ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition shrink-0 border border-transparent dark:border-slate-700">
                                    @{{ pf }}
                                </button>
                            </template>
                            <template v-else>
                                <button v-for="pf in ['M', 'F', '1', '2']" :key="pf" type="button" @click="lockedBibPrefix = (lockedBibPrefix === pf ? '' : pf)"
                                    :class="lockedBibPrefix === pf ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition shrink-0 border border-transparent dark:border-slate-700">
                                    @{{ pf }}
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Auto-Submit Row -->
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase shrink-0">Auto-Enter:</span>
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <button type="button" @click="autoSubmitDigits = 0"
                                :class="autoSubmitDigits === 0 ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-950 font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs transition shrink-0 border border-transparent dark:border-slate-700">
                                Tekan Enter
                            </button>
                            <button v-for="d in [3, 4, 5]" :key="d" type="button" @click="autoSubmitDigits = (autoSubmitDigits === d ? 0 : d)"
                                :class="autoSubmitDigits === d ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition shrink-0 border border-transparent dark:border-slate-700">
                                @{{ d }} Digit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Manual BIB Input Form (Ergonomic Stack on Mobile, Inline on Desktop) -->
                <form @submit.prevent="recordManualBib" class="space-y-2.5">
                    <div class="flex items-center gap-2">
                        <!-- Compact Prefix Box -->
                        <div class="w-20 sm:w-28 shrink-0 relative">
                            <input 
                                v-model="lockedBibPrefix" 
                                @input="lockedBibPrefix = lockedBibPrefix.toUpperCase()"
                                type="text" 
                                placeholder="Prefix" 
                                maxlength="6"
                                title="Prefix BIB"
                                class="w-full px-2 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white font-black font-mono text-sm sm:text-base uppercase rounded-2xl border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none placeholder:text-slate-400 placeholder:font-sans placeholder:text-xs text-center transition"
                            >
                            <button 
                                v-if="lockedBibPrefix" 
                                type="button" 
                                @click="lockedBibPrefix = ''"
                                class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1"
                                title="Kosongkan prefix"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Main BIB Number Input with Large Touch Target & Mobile Numpad Keyboard -->
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-base font-bold">#</span>
                            <input id="manualBibInputEl" v-model="manualBibInput" @keyup="onManualBibKeyup" 
                                type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
                                :placeholder="lockedBibPrefix ? `Nomor BIB ${lockedBibPrefix}...` : 'Ketik No. BIB atau Nama...'" 
                                class="w-full pl-9 pr-4 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white font-black font-mono text-lg sm:text-xl rounded-2xl border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none placeholder:text-slate-400 placeholder:font-normal placeholder:font-sans placeholder:text-xs sm:placeholder:text-sm transition shadow-inner">
                        </div>

                        <!-- Desktop Inline Submit Button -->
                        <button type="submit" :disabled="!timer.running && timer.elapsed === 0" 
                            class="hidden sm:flex px-6 py-3.5 sm:py-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-sm rounded-2xl shadow transition shrink-0 items-center justify-center gap-2">
                            <span>Catat Finish (Enter)</span>
                        </button>
                    </div>

                    <!-- PREDICTIVE SUGGESTIONS BAR (Tepat di bawah input, langsung terlihat di atas keyboard HP) -->
                    <div v-if="quickBibSuggestions.length > 0" class="p-2.5 bg-slate-100 dark:bg-slate-950 rounded-2xl border border-indigo-500/50 dark:border-indigo-500/60 space-y-1.5 shadow-md">
                        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-1 flex items-center justify-between">
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">Pilih Pelari (@{{ quickBibSuggestions.length }}):</span>
                            <span class="text-[10px] text-slate-400">1-Tap untuk Langsung Catat</span>
                        </div>
                        <div class="flex flex-wrap sm:grid sm:grid-cols-2 gap-1.5 max-h-48 overflow-y-auto no-scrollbar">
                            <button v-for="p in quickBibSuggestions" :key="p.id" type="button" @click="selectSuggestion(p)"
                                class="flex-1 sm:flex-none flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition text-left text-xs shadow-sm active:scale-[0.98] min-h-[44px]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="font-mono font-black text-indigo-600 dark:text-indigo-400 text-sm">#@{{ p.bib }}</span>
                                    <span class="font-bold text-slate-900 dark:text-white truncate">@{{ p.name }}</span>
                                </div>
                                <span class="text-[10px] font-mono shrink-0 ml-2 font-bold px-2 py-0.5 rounded-lg"
                                    :class="p.status === 'finished' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800' : (p.status === 'dnf' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800')">
                                    @{{ p.status === 'finished' ? 'Finish ' + formatTime(p.totalTime) : (p.status === 'dnf' ? 'DNF' : 'Berlari') }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Single Live Runner Match Bar (Saat 1 pelari cocok persis) -->
                    <div v-else-if="liveMatchedRunner" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-indigo-500/40 flex items-center justify-between text-xs font-mono shadow-sm">
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="text-slate-400">Cocok:</span>
                            <span class="font-bold text-slate-900 dark:text-white truncate">#@{{ liveMatchedRunner.bib }} @{{ liveMatchedRunner.name }}</span>
                        </div>
                        <span :class="liveMatchedRunner.status === 'finished' ? 'text-amber-500 font-bold' : 'text-emerald-500 font-bold'" class="shrink-0 ml-2">
                            @{{ liveMatchedRunner.status === 'finished' ? 'Sudah Finish: ' + formatTime(liveMatchedRunner.totalTime) : 'Sedang Berlari' }}
                        </span>
                    </div>

                    <!-- Ambiguous Prefix Warning -->
                    <div v-else-if="ambiguousRunners && ambiguousRunners.length > 1" class="p-2.5 rounded-xl bg-amber-950/30 border border-amber-500/40 text-xs text-amber-400 font-mono space-y-1">
                        <div class="flex items-center gap-1.5 font-bold">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Ditemukan @{{ ambiguousRunners.length }} pelari dengan nomor tersebut:</span>
                        </div>
                        <div class="text-[11px] text-slate-300">
                            @{{ ambiguousRunners.map(r => '#' + r.bib + ' ' + r.name).join(', ') }}. Silakan pilih Prefix di atas.
                        </div>
                    </div>

                    <!-- Mobile Full-Width Submit Button (Terletak di bawah suggestions) -->
                    <button type="submit" :disabled="!timer.running && timer.elapsed === 0" 
                        class="sm:hidden w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-base rounded-2xl shadow transition flex items-center justify-center gap-2">
                        <span>Catat Finish (Enter)</span>
                    </button>
                </form>

                <!-- Quick Add Unregistered Participant Floating Bottom Sheet (Mobile First & Thumb Friendly) -->
                <transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="translate-y-4 opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-4 opacity-0"
>
    <div
        v-if="pendingUnregisteredBib.show"
        class="fixed inset-x-3 bottom-3 sm:bottom-6 sm:right-6 sm:left-auto
               sm:w-[380px] z-50
               bg-slate-950 border border-slate-700
               rounded-xl p-4 text-white shadow-lg"
    >

        <!-- Header -->
        <div class="flex items-start justify-between gap-4">

            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>

                    <span class="text-xs font-semibold text-slate-300">
                        BIB belum terdaftar
                    </span>
                </div>

                <div class="mt-1 font-mono text-2xl font-bold text-white">
                    #@{{ pendingUnregisteredBib.bib }}
                </div>
            </div>

            <div class="text-right">
                <div class="text-[10px] uppercase tracking-wide text-slate-500">
                    Waktu
                </div>

                <div class="mt-0.5 font-mono text-sm font-semibold text-white">
                    @{{ pendingUnregisteredBib.formattedTime }}
                </div>
            </div>

        </div>


        <!-- Message -->
        <div class="mt-3 pt-3 border-t border-slate-800">
            <p class="text-xs text-slate-400 leading-relaxed">
                Nomor ini tidak ditemukan. Tambahkan peserta dan simpan waktu tersebut?
            </p>
        </div>


        <!-- Actions -->
        <div class="mt-4 flex items-center justify-end gap-2">

            <button
                type="button"
                @click="cancelAddUnregisteredParticipant"
                class="h-9 px-3.5 rounded-lg
                       border border-slate-700
                       text-slate-400 hover:text-white
                       hover:bg-slate-900
                       text-xs font-semibold
                       transition-colors"
            >
                Batal
            </button>

            <button
                type="button"
                @click="confirmAddUnregisteredParticipant"
                class="h-9 px-4 rounded-lg
                       bg-white hover:bg-slate-200
                       text-slate-950
                       text-xs font-semibold
                       transition-colors"
            >
                Tambah peserta
            </button>

        </div>

    </div>
</transition>

                <!-- Collapsible Sensor & Scanner Drawer -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="sensorSettingsOpen = !sensorSettingsOpen" class="w-full flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400 py-1 hover:text-slate-800 dark:hover:text-slate-200 transition">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders text-indigo-500"></i>
                            <span>Pengaturan Sensor & Scanner</span>
                        </span>
                        <i class="fa-solid transition-transform text-slate-400" :class="sensorSettingsOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>

                    <div v-if="sensorSettingsOpen" class="mt-2.5 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2.5 text-xs">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold text-slate-400 uppercase text-[10px] mr-1">Preset:</span>
                            <button type="button" @click="setDetectorPreset('all')" 
                                :class="currentDetectorPreset === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'"
                                class="px-2.5 py-1 rounded-lg text-xs transition">
                                Semua (Hybrid)
                            </button>
                            <button type="button" @click="setDetectorPreset('qr')" 
                                :class="currentDetectorPreset === 'qr' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'"
                                class="px-2.5 py-1 rounded-lg text-xs transition">
                                QR Code
                            </button>
                            <button type="button" @click="setDetectorPreset('ocr')" 
                                :class="currentDetectorPreset === 'ocr' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'"
                                class="px-2.5 py-1 rounded-lg text-xs transition">
                                Nomor BIB
                            </button>
                            <button type="button" @click="setDetectorPreset('face')" 
                                :class="currentDetectorPreset === 'face' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'"
                                class="px-2.5 py-1 rounded-lg text-xs transition">
                                Face AI
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                            <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                                <input type="checkbox" v-model="raceSettings.enableQr" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>QR Scanner</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                                <input type="checkbox" v-model="raceSettings.enableOcr" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>OCR Angka BIB</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                                <input type="checkbox" v-model="raceSettings.enableFaceAi" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>Face AI</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                                <input type="checkbox" v-model="raceSettings.enableBeep" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>Audio Beep</span>
                            </label>
                        </div>

                        <!-- FAST GLOBAL OCR: fixed full-frame mode -->
                        <div v-if="raceSettings.enableOcr" class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <span class="font-bold text-slate-600 dark:text-slate-400">Mode OCR:</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold">
                                <i class="fa-solid fa-bolt"></i>
                                Global Full Frame • Fast Scan ~450 ms
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Line-Crossing & QR Camera Console -->
            <div v-show="camera.active" class="bg-slate-900 border border-slate-700 rounded-2xl overflow-hidden shadow-2xl relative w-full max-w-5xl mx-auto mb-6 text-white transition-colors">
                <!-- Header Toolbar: Multi-Device, Race Mode, Camera & AI Status -->
                <div class="p-3 sm:p-4 bg-slate-950 border-b border-slate-800 space-y-3 text-xs">
                    <!-- Row 1: Station Role, Camera Source, and Mode -->
                    <div class="flex flex-wrap items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Station Role (Multi-Device Gateway) -->
                            <select v-model="raceSettings.stationMode" class="bg-indigo-950 text-indigo-200 font-bold px-3 py-1.5 rounded-lg border border-indigo-700/60 focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="master">Station: Master Console</option>
                                <option value="satellite">Station: Satelit Kamera Pos</option>
                            </select>

                            <!-- Camera Device Switcher (Supports Osmo Pocket, Phone Webcam, USB cams) -->
                            <div class="relative" v-if="camera.devices.length > 0">
                                <select v-model="camera.selectedDeviceId" @change="switchCameraDevice" class="bg-slate-800 text-white font-medium px-3 py-1.5 rounded-lg border border-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option v-for="dev in camera.devices" :key="dev.deviceId" :value="dev.deviceId">
                                        @{{ dev.label || 'Kamera ' + dev.deviceId.substring(0, 5) }}
                                    </option>
                                </select>
                            </div>

                            <!-- Mode Selector -->
                            <select v-model="camera.mode" @change="switchCameraMode" class="bg-slate-800 text-white font-medium px-3 py-1.5 rounded-lg border border-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="ai_line">AI Line-Crossing & Dual Scan</option>
                                <option value="qr_only">Simple QR Scanner</option>
                                <option value="bib_scan">BIB Number Scan Only</option>
                            </select>

                            <!-- Local Referee Video Recorder Button -->
                            <button type="button" @click="toggleVideoRecording" 
                                class="px-3 py-1.5 rounded-lg font-bold flex items-center gap-1.5 transition-colors shadow-sm"
                                :class="isRecordingVideo ? 'bg-red-600 hover:bg-red-700 text-white animate-pulse' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700'"
                                :title="isRecordingVideo ? 'Hentikan Perekaman Video Lokal' : 'Mulai Rekam Video Lokal untuk Juri (Disimpan di Browser/Laptop)'">
                                <i class="fa-solid" :class="isRecordingVideo ? 'fa-stop' : 'fa-video'"></i>
                                <span v-if="isRecordingVideo">REC @{{ formatRecordingDuration }}</span>
                                <span v-else>Rekam Video Juri</span>
                            </button>

                            <!-- Review Video Juri Button -->
                            <button v-if="recordedVideoUrl && !isRecordingVideo" type="button" @click="openVideoReviewModal" 
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold flex items-center gap-1.5 transition-colors shadow-sm"
                                title="Buka Pemutar Video Slow-Motion Juri">
                                <i class="fa-solid fa-film text-amber-300"></i>
                                <span>Review Video Juri</span>
                            </button>

                            <!-- AI Status Pill -->
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 font-bold border border-slate-700">
                                <i class="fa-solid fa-microchip"></i>
                                <span v-if="camera.aiModelLoading">Memuat AI...</span>
                                <span v-else-if="camera.aiModelReady" class="text-emerald-400">AI Ready</span>
                                <span v-else>Standby</span>
                            </span>
                        </div>

                        <!-- Right Stats & Tuning Drawer Toggle -->
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-slate-400">@{{ camera.fps }} FPS</span>
                            <span class="font-mono text-emerald-400 font-bold">@{{ camera.crossingCount }} Crossings</span>
                            <button type="button" @click="cameraSettingsOpen = !cameraSettingsOpen" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-sliders"></i>
                                <span>Pengaturan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Row 2 (Optional Expandable Settings Panel) -->
                    <div v-if="cameraSettingsOpen" class="p-3 bg-slate-900 rounded-xl border border-slate-800 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-3">
                        <!-- Race Mode -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Mode Lomba</label>
                            <select v-model="raceSettings.raceMode" class="w-full bg-slate-950 text-white font-medium px-2.5 py-1.5 rounded border border-slate-700 outline-none text-xs">
                                <option value="single">Single Finish (1x Finish Road Race)</option>
                                <option value="multi_lap">Multi-Lap Circuit (Loop Putaran)</option>
                            </select>
                        </div>

                        <!-- Target Laps (if multi lap) -->
                        <div v-if="raceSettings.raceMode === 'multi_lap'">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Target Putaran (Laps)</label>
                            <input v-model.number="raceSettings.targetLaps" type="number" min="1" max="100" class="w-full bg-slate-950 text-white font-medium px-2.5 py-1.5 rounded border border-slate-700 outline-none text-xs">
                        </div>

                        <!-- Min-Lap Cooldown Filter -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">
                                Cooldown Anti-Double (@{{ raceSettings.minLapCooldownSec }}s)
                            </label>
                            <input v-model.number="raceSettings.minLapCooldownSec" type="range" min="5" max="120" step="5" class="w-full accent-indigo-500">
                        </div>

                        <!-- Line Direction -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Arah Gerakan Pelari</label>
                            <select v-model="camera.line.direction" class="w-full bg-slate-950 text-white font-medium px-2.5 py-1.5 rounded border border-slate-700 outline-none text-xs">
                                <option value="any">Bebas (Semua Arah)</option>
                                <option value="left_to_right">Kiri ke Kanan</option>
                                <option value="right_to_left">Kanan ke Kiri</option>
                                <option value="top_to_bottom">Atas ke Bawah</option>
                                <option value="bottom_to_top">Bawah ke Atas</option>
                            </select>
                        </div>

                        <!-- OCR, Face AI & Audio Toggles -->
                        <div class="sm:col-span-2 md:col-span-4 flex flex-wrap items-center gap-4 pt-1 border-t border-slate-800">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                <input type="checkbox" v-model="raceSettings.enableFaceAi" class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>AI Face Recognition (Biometrik)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                <input type="checkbox" v-model="raceSettings.enableOcr" class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>OCR Angka BIB</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                <input type="checkbox" v-model="raceSettings.enableBeep" class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>Audio Beep saat Crossing</span>
                            </label>
                            <!-- Line Preset Buttons -->
                            <div class="flex items-center gap-1.5 ml-auto">
                                <span class="text-slate-500 text-[11px]">Preset Garis:</span>
                                <button type="button" @click="setLinePreset('vertical')" class="px-2 py-1 rounded bg-slate-950 hover:bg-slate-800 text-slate-300 font-medium border border-slate-700">Tegak</button>
                                <button type="button" @click="setLinePreset('horizontal')" class="px-2 py-1 rounded bg-slate-950 hover:bg-slate-800 text-slate-300 font-medium border border-slate-700">Datar</button>
                            </div>
                        </div>

                        <!-- Camera Console FAST GLOBAL OCR -->
                        <div v-if="raceSettings.enableOcr" class="sm:col-span-2 md:col-span-4 flex items-center justify-between flex-wrap gap-2 text-xs pt-1 border-t border-slate-800">
                            <span class="text-slate-400 font-bold text-[11px]">OCR Scanner:</span>
                            <span class="text-emerald-400 font-bold text-[11px]">
                                Full Frame • Runtime Pattern • Fuzzy Top-1 • No Voting
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Video & Canvas Viewport -->
                <div class="relative bg-black aspect-[16/9] sm:aspect-[16/10] overflow-hidden flex items-center justify-center select-none">
                    <!-- Native AI Video Element -->
                    <video id="aiVideo" autoplay playsinline muted class="w-full h-full object-cover" :class="{'hidden': camera.mode === 'qr_only'}"></video>
                    
                    <!-- Interactive Canvas Overlay -->
                    <canvas id="aiCanvas" class="absolute inset-0 w-full h-full cursor-crosshair z-10" :class="{'hidden': camera.mode === 'qr_only'}"
                        @mousedown="handleCanvasMouseDown"
                        @mousemove="handleCanvasMouseMove"
                        @mouseup="handleCanvasMouseUp"
                        @mouseleave="handleCanvasMouseUp"
                        @touchstart.passive="handleCanvasTouchStart"
                        @touchmove.passive="handleCanvasTouchMove"
                        @touchend="handleCanvasTouchEnd">
                    </canvas>

                    <!-- HTML5 QR Container for QR only fallback mode -->
                    <div id="reader" class="w-full" :class="{'hidden': camera.mode !== 'qr_only'}"></div>

                    <!-- Line Dragging Tip Badge -->
                    <div v-if="camera.mode === 'ai_line'" class="absolute bottom-2 left-2 z-20 px-2.5 py-1 rounded bg-black/80 border border-slate-700 text-[11px] text-slate-300 font-medium pointer-events-none">
                        Tarik titik A atau B untuk menyesuaikan posisi Garis Finish
                    </div>

                    <!-- BIB Scan Mode: Detected BIB overlay -->
                    <transition name="fade">
                        <div v-if="camera.mode === 'bib_scan' && bibScan.lastDetected"
                             class="absolute top-3 left-1/2 -translate-x-1/2 z-30 px-5 py-3 rounded-2xl bg-slate-950 border-2 border-emerald-500 text-center shadow-2xl pointer-events-none">
                            <div class="font-oswald text-4xl font-black text-emerald-400 leading-none tracking-tight">
                                #@{{ bibScan.lastDetected.bib }}
                            </div>
                            <div class="text-slate-300 font-semibold text-xs mt-1 truncate max-w-[180px]">@{{ bibScan.lastDetected.name }}</div>
                            <div class="font-mono text-emerald-300 font-bold text-xs mt-0.5">@{{ bibScan.lastDetected.time }}</div>
                        </div>
                    </transition>

                    <!-- BIB Scan: Confirmation progress indicator -->
                    <div v-if="camera.mode === 'bib_scan' && bibScan.scanStatus === 'confirming'"
                         class="absolute top-3 left-1/2 -translate-x-1/2 z-30 px-4 py-2 rounded-xl bg-slate-950 border border-amber-500 text-center shadow-xl pointer-events-none">
                        <div class="text-amber-400 font-bold text-xs mb-1.5">Konfirmasi...</div>
                        <div class="flex gap-1 justify-center">
                            <span v-for="n in bibScan.confirmFrames" :key="n"
                                  class="w-3 h-3 rounded-full transition-all"
                                  :class="n <= bibScan.confirmCount ? 'bg-amber-400' : 'bg-slate-700'"></span>
                        </div>
                    </div>

                    <!-- BIB Scan: Scanning status pill -->
                    <div v-if="camera.mode === 'bib_scan' && camera.active && bibScan.scanStatus === 'scanning'"
                         class="absolute bottom-3 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 px-4 py-2 rounded-full bg-slate-950 border border-indigo-600 text-xs font-bold text-indigo-300 pointer-events-none">
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                        Mencari BIB...
                    </div>

                    <!-- BIB Scan: Locked/recorded flash -->
                    <div v-if="camera.mode === 'bib_scan' && bibScan.scanStatus === 'locked'"
                         class="absolute bottom-3 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-950 border border-emerald-500 text-xs font-bold text-emerald-300 pointer-events-none">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Waktu Tercatat
                    </div>

                    <!-- BIB Scan: Aggressive Sync Indicator -->
                    <div v-if="camera.mode === 'bib_scan' && bibScan.aggressiveSyncActive"
                         class="absolute top-3 right-3 z-30 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-950 border border-indigo-700 text-[10px] font-bold text-indigo-300 pointer-events-none">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                        Sync 500ms
                    </div>

                    <!-- Video Recording Active Indicator -->
                    <div v-if="isRecordingVideo" class="absolute top-3 left-3 z-30 flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-950/90 border border-red-500 text-red-300 text-xs font-bold font-mono shadow-lg">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                        <span>REC @{{ formatRecordingDuration }} (Lokal)</span>
                    </div>

                    <!-- Status Notification Message -->
                    <div v-if="camera.lastScanMsg" class="absolute top-2 right-2 z-20 px-3 py-1.5 rounded-lg bg-slate-950/90 border border-slate-700 text-emerald-400 font-mono text-xs font-bold shadow-lg">
                        @{{ camera.lastScanMsg }}
                    </div>
                </div>

                <!-- BIB Scan Mode: Settings & Pattern Training Panel -->
                <div v-if="camera.mode === 'bib_scan'" class="p-4 sm:p-5 bg-slate-950 border-t border-slate-800 space-y-4">

                    <!-- Header -->
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-200 uppercase tracking-wider">Pengaturan BIB Number Scan</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-950 border border-emerald-700/60 text-emerald-400 font-mono text-[10px] font-bold">
                                    Tahap 2: RoI Micro-OCR (~80ms)
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Kamera memindai zona target RoI nomor dada secara terfokus dengan kecepatan tinggi tanpa garis visual</div>
                        </div>
                        <div v-if="bibScan.aggressiveSyncActive" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-indigo-950 border border-indigo-700 text-[10px] font-bold text-indigo-300 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                            Sync Cepat Aktif (500ms)
                        </div>
                    </div>

                    <!-- Scan Parameters Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Konfirmasi Frame <span class="text-indigo-400">(@{{ bibScan.confirmFrames }}x)</span>
                            </label>
                            <input type="range" v-model.number="bibScan.confirmFrames" min="1" max="5" step="1" class="w-full accent-indigo-500">
                            <div class="text-slate-500 text-[10px] mt-1">1 = instan, 3-5 = lebih akurat</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Cooldown Anti-Duplikat <span class="text-indigo-400">(@{{ bibScan.cooldownSec }}s)</span>
                            </label>
                            <input type="range" v-model.number="bibScan.cooldownSec" min="2" max="30" step="1" class="w-full accent-indigo-500">
                            <div class="text-slate-500 text-[10px] mt-1">Jeda min. per BIB setelah dicatat</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Jarak Kamera
                            </label>
                            <select v-model="bibScan.distance" class="w-full bg-slate-900 text-white font-medium px-2.5 py-1.5 rounded border border-slate-700 outline-none text-xs">
                                <option value="close">Dekat (&lt; 2m, cepat)</option>
                                <option value="auto">Otomatis (2-5m)</option>
                                <option value="far">Jauh (&gt; 5m, akurat)</option>
                            </select>
                            <div class="text-slate-500 text-[10px] mt-1">Mempengaruhi resolusi OCR</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Min. Match Score <span class="text-indigo-400">(@{{ bibScan.minScore }})</span>
                            </label>
                            <input type="range" v-model.number="bibScan.minScore" min="40" max="95" step="5" class="w-full accent-indigo-500">
                            <div class="text-slate-500 text-[10px] mt-1">Threshold fuzzy match BIB</div>
                        </div>
                    </div>

                    <!-- Pattern Training & Registered BIB Profile -->
                    <div class="border border-slate-800 rounded-xl p-4 sm:p-5 space-y-4 bg-slate-900">
                        <!-- Active Pattern from Registered Participants (Baseline) -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div>
                                    <div class="text-xs font-bold text-slate-200 uppercase tracking-wider">Pola BIB Aktif (Dari Data Peserta)</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Sistem otomatis menggunakan struktur nomor peserta yang terdaftar sebagai basis deteksi OCR</div>
                                </div>
                                <span v-if="activeBibPattern.isReady" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-950 border border-emerald-700/60 text-emerald-400 text-xs font-bold shrink-0">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Pola Aktif (@{{ activeBibPattern.totalBibs }} Peserta)</span>
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-950 border border-amber-700/60 text-amber-400 text-xs font-bold shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Belum Ada Peserta</span>
                                </span>
                            </div>

                            <!-- Pattern Summary Chips -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Panjang Digit</div>
                                    <div class="text-sm font-bold text-white mt-1">@{{ activeBibPattern.digitLengthsText }}</div>
                                </div>
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Prefix Terdeteksi</div>
                                    <div class="text-sm font-bold text-indigo-400 mt-1 truncate">@{{ activeBibPattern.prefixesText }}</div>
                                </div>
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rentang Sampel</div>
                                    <div class="text-sm font-bold text-slate-300 mt-1 truncate">@{{ activeBibPattern.sampleRange }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional: Additional Image Verification -->
                        <div class="pt-3 border-t border-slate-800 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider">Verifikasi Sampel Foto Fisik BIB (Opsional)</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Unggah foto cetak BIB untuk menguji keterbacaan OCR terhadap font fisik sebelum start lomba</div>
                                </div>
                                <span v-if="bibScan.trainStatus === 'processing'" class="text-xs text-amber-400 font-bold animate-pulse shrink-0">Memproses...</span>
                                <span v-else-if="bibScan.trainStatus === 'done'" class="text-xs text-emerald-400 font-bold shrink-0">Selesai (@{{ bibScan.sampleImages.length }} Foto)</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl border border-dashed border-slate-700 hover:border-indigo-500 cursor-pointer text-xs text-slate-400 hover:text-indigo-300 transition-colors bg-slate-950">
                                    <i class="fa-solid fa-image text-sm"></i>
                                    <span>Pilih Sampel Foto BIB (Multi-file)</span>
                                    <input type="file" multiple accept="image/*" class="hidden" @change="onBibSampleUpload">
                                </label>
                                <button v-if="bibScan.sampleImages.length > 0" type="button"
                                    @click="bibScan.sampleImages = []; bibScan.trainStatus = ''"
                                    class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors shrink-0">
                                    Reset Foto
                                </button>
                            </div>

                            <!-- Uploaded Samples Preview -->
                            <div v-if="bibScan.sampleImages.length > 0" class="flex gap-2.5 flex-wrap pt-1">
                                <div v-for="(img, idx) in bibScan.sampleImages" :key="idx"
                                     class="relative w-16 h-16 rounded-lg overflow-hidden border border-slate-700 bg-slate-950 shrink-0">
                                    <img :src="img.src" class="w-full h-full object-cover">
                                    <div class="absolute bottom-0 left-0 right-0 bg-slate-950/95 text-[9px] font-bold text-center py-0.5 truncate px-0.5"
                                         :class="img.extractedBibs && img.extractedBibs.length > 0 ? 'text-emerald-400' : 'text-slate-500'">
                                        @{{ img.extractedBibs && img.extractedBibs.length > 0 ? img.extractedBibs.join(', ') : '...' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Finish Audit Stream Drawer -->
                <div v-if="liveFinishFeed.length > 0" class="p-3 sm:p-4 bg-slate-950 border-t border-slate-800">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Finish Feed & Snapshot Audit
                        </div>
                        <button type="button" @click="liveFinishFeed = []" class="text-[11px] text-slate-500 hover:text-slate-300 transition-colors">
                            Bersihkan Log
                        </button>
                    </div>

                    <div class="flex gap-2.5 overflow-x-auto pb-1 no-scrollbar">
                        <div v-for="item in liveFinishFeed" :key="item.id" class="flex-none w-56 p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2.5 text-xs">
                            <div class="w-12 h-12 rounded-lg bg-black overflow-hidden shrink-0 border border-slate-700 relative">
                                <img v-if="item.snapshot" :src="item.snapshot" class="w-full h-full object-cover" alt="Finish Snapshot">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-600"><i class="fa-solid fa-camera"></i></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-white truncate">
                                    <span v-if="item.bib" class="font-oswald text-sm text-indigo-400 mr-1">#@{{ item.bib }}</span>
                                    <span v-else class="text-amber-400 font-medium">Unassigned</span>
                                    <span v-if="item.name">@{{ item.name }}</span>
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5">@{{ item.timeFormatted }}</div>
                                <div class="mt-0.5 flex items-center gap-1 flex-wrap text-[9px] font-bold uppercase">
                                    <span v-if="item.matchMethod" class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">@{{ item.matchMethod }}</span>
                                    <span v-if="item.ocrConfidence > 0" class="px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-300">OCR @{{ Math.round(item.ocrConfidence) }}%</span>
                                    <span v-if="item.ambiguous" class="px-1.5 py-0.5 rounded bg-amber-950 text-amber-300">Ambigu</span>
                                    <span v-if="!item.participantId" class="px-1.5 py-0.5 rounded bg-red-950 text-red-300">Perlu Verifikasi</span>
                                </div>

                                <button v-if="!item.participantId" type="button" @click="openAssignBibModal(item)" class="mt-1 px-2 py-0.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] transition-colors w-full">
                                    Verifikasi / Tetapkan BIB
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Race Tab Quick Search, Status Filter & View Controls -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 transition-colors">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input 
                            v-model="raceSearchQuery" 
                            type="text" 
                            placeholder="Cari No. BIB atau Nama pelari..." 
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-indigo-500 transition"
                        >
                        <button 
                            v-if="raceSearchQuery" 
                            type="button" 
                            @click="raceSearchQuery = ''" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1"
                            title="Hapus pencarian"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Status Filter Tabs -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button 
                            type="button" 
                            @click="raceStatusFilter = 'on_track'"
                            :class="raceStatusFilter === 'on_track' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-3 py-2 rounded-xl text-xs transition border border-transparent dark:border-slate-700 flex items-center gap-1.5"
                        >
                            <span>On Track</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/20 font-mono">@{{ runningCount }}</span>
                        </button>

                        <button 
                            type="button" 
                            @click="raceStatusFilter = 'finished'"
                            :class="raceStatusFilter === 'finished' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-3 py-2 rounded-xl text-xs transition border border-transparent dark:border-slate-700 flex items-center gap-1.5"
                        >
                            <span>Sudah Finish</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/20 font-mono">@{{ finishedCount }}</span>
                        </button>

                        <button 
                            type="button" 
                            @click="raceStatusFilter = 'all'"
                            :class="raceStatusFilter === 'all' ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-950 font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-3 py-2 rounded-xl text-xs transition border border-transparent dark:border-slate-700 flex items-center gap-1.5"
                        >
                            <span>Semua</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/20 font-mono">@{{ participants.length }}</span>
                        </button>

                        <button 
                            v-if="dnfCount > 0"
                            type="button" 
                            @click="raceStatusFilter = 'dnf'"
                            :class="raceStatusFilter === 'dnf' ? 'bg-red-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-3 py-2 rounded-xl text-xs transition border border-transparent dark:border-slate-700 flex items-center gap-1.5"
                        >
                            <span>DNF</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/20 font-mono">@{{ dnfCount }}</span>
                        </button>
                    </div>

                    <!-- View Switcher & Page Size Selector -->
                    <div class="flex items-center gap-2">
                        <!-- Grid vs Table View -->
                        <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1 border border-slate-200 dark:border-slate-700">
                            <button 
                                type="button" 
                                @click="raceViewMode = 'grid'" 
                                :class="raceViewMode === 'grid' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" 
                                class="px-2.5 py-1 rounded-lg text-xs transition" 
                                title="Tampilan Kartu Grid"
                            >
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <button 
                                type="button" 
                                @click="raceViewMode = 'table'" 
                                :class="raceViewMode === 'table' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" 
                                class="px-2.5 py-1 rounded-lg text-xs transition" 
                                title="Tampilan Tabel Ringkas"
                            >
                                <i class="fa-solid fa-list"></i>
                            </button>
                        </div>

                        <!-- Per Page Selector -->
                        <select 
                            v-model.number="racePageSize" 
                            class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 outline-none"
                        >
                            <option :value="24">24 / hal</option>
                            <option :value="48">48 / hal</option>
                            <option :value="96">96 / hal</option>
                            <option :value="9999">Semua</option>
                        </select>
                    </div>
                </div>

                <!-- Info Subtext -->
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                    <div>
                        Menampilkan <strong class="text-slate-900 dark:text-white">@{{ paginatedRaceParticipants.length }}</strong> dari <strong class="text-slate-900 dark:text-white">@{{ filteredRaceParticipants.length }}</strong> pelari
                        <span v-if="raceSearchQuery"> (difilter dari "@{{ raceSearchQuery }}")</span>
                    </div>
                    <div v-if="raceTotalPages > 1" class="font-mono">
                        Hal @{{ raceCurrentPage }} / @{{ raceTotalPages }}
                    </div>
                </div>
            </div>

            <!-- Mode 1: Grid Cards -->
            <div v-if="raceViewMode === 'grid' && paginatedRaceParticipants.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 pt-2">
                <div v-for="p in paginatedRaceParticipants" :key="p.id" 
                     class="relative bg-white dark:bg-[#0c121e] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 transition-all duration-200 cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 group select-none overflow-hidden"
                     :class="{'border-indigo-500 ring-2 ring-indigo-500/30': p.recentlyScanned}"
                     @click="recordLap(p.id, 'manual')">
                    
                    <button @click.stop="markDNF(p.id)" class="absolute top-2 right-2 text-slate-400 hover:text-red-500 p-1 z-10 dark:text-slate-500 dark:hover:text-red-400" title="Tandai DNF (Did Not Finish)">
                        <i class="fa-solid fa-circle-xmark text-lg"></i>
                    </button>

                    <div class="p-4 flex flex-col items-center text-center h-full justify-between">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 font-black text-xs mb-2 group-hover:bg-indigo-600 group-hover:text-white transition">
                            @{{ getInitials(p.name) }}
                        </div>

                        <div class="font-oswald font-black text-3xl sm:text-4xl text-slate-900 dark:text-white tracking-tight">
                            #@{{ p.bib }}
                        </div>
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 line-clamp-1 w-full mt-1">
                            @{{ p.name }}
                        </div>

                        <div class="mt-3 w-full pt-3 border-t border-slate-100 dark:border-slate-800/80 grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Laps</div>
                                <div class="font-black text-indigo-600 dark:text-indigo-400 text-base">@{{ p.laps.length }}</div>
                            </div>
                            <div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Last Lap</div>
                                <div class="font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">
                                    @{{ getLastLapTime(p) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="p.recentlyScanned" class="absolute inset-0 bg-indigo-600/20 rounded-2xl pointer-events-none flex items-center justify-center">
                        <span class="bg-indigo-600 text-white text-[10px] px-2.5 py-1 rounded-lg font-black tracking-wider animate-pulse">RECORDED</span>
                    </div>
                </div>
            </div>

            <!-- Mode 2: Compact Table View (Super fast for 500+ runners) -->
            <div v-if="raceViewMode === 'table' && paginatedRaceParticipants.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm pt-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-bold text-[11px]">
                            <tr>
                                <th class="p-3">BIB</th>
                                <th class="p-3">Nama Pelari</th>
                                <th class="p-3 text-center">Laps</th>
                                <th class="p-3 text-right">Waktu Terakhir</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3 text-center">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="p in paginatedRaceParticipants" :key="p.id" 
                                class="hover:bg-indigo-50/40 dark:hover:bg-slate-800/60 transition-colors"
                                :class="{'bg-indigo-50/60 dark:bg-indigo-950/30': p.recentlyScanned}">
                                <td class="p-3 font-oswald font-bold text-base text-slate-900 dark:text-white">
                                    #@{{ p.bib }}
                                </td>
                                <td class="p-3 font-semibold text-slate-800 dark:text-slate-200">
                                    @{{ p.name }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300">
                                        @{{ p.laps.length }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                    @{{ getLastLapTime(p) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span v-if="p.status === 'dnf'" class="px-2 py-0.5 rounded-lg bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold text-[10px]">DNF</span>
                                    <span v-else-if="p.status === 'finished'" class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-bold text-[10px]">FINISH</span>
                                    <span v-else class="px-2 py-0.5 rounded-lg bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 font-bold text-[10px]">ON TRACK</span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="recordLap(p.id, 'manual')" 
                                            class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition flex items-center gap-1 shadow-sm"
                                            title="Catat Lap / Finish">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                            <span>Lap</span>
                                        </button>
                                        <button v-if="p.status !== 'dnf'" type="button" @click="markDNF(p.id)" 
                                            class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-950/40 text-slate-400 hover:text-red-500 transition text-xs"
                                            title="Tandai DNF">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="paginatedRaceParticipants.length === 0" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-400">
                <div class="text-sm font-semibold">Tidak ada pelari yang cocok dengan filter atau pencarian.</div>
                <button v-if="raceSearchQuery || raceStatusFilter !== 'all'" type="button" @click="raceSearchQuery = ''; raceStatusFilter = 'all'" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 font-bold underline">
                    Reset Filter & Pencarian
                </button>
            </div>

            <!-- Pagination Controls Bar -->
            <div v-if="raceTotalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan halaman <strong class="text-slate-900 dark:text-white">@{{ raceCurrentPage }}</strong> dari <strong class="text-slate-900 dark:text-white">@{{ raceTotalPages }}</strong>
                </div>

                <div class="flex items-center gap-1.5">
                    <button 
                        type="button" 
                        @click="prevRacePage" 
                        :disabled="raceCurrentPage <= 1" 
                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-1"
                    >
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        <span>Sebelumnya</span>
                    </button>

                    <!-- Page Number Buttons -->
                    <template v-for="page in raceTotalPages" :key="page">
                        <button 
                            v-if="page === 1 || page === raceTotalPages || (page >= raceCurrentPage - 2 && page <= raceCurrentPage + 2)"
                            type="button" 
                            @click="setRacePage(page)" 
                            :class="raceCurrentPage === page ? 'bg-indigo-600 text-white font-black' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'" 
                            class="w-8 h-8 rounded-xl text-xs font-bold transition flex items-center justify-center"
                        >
                            @{{ page }}
                        </button>
                        <span v-else-if="page === raceCurrentPage - 3 || page === raceCurrentPage + 3" class="px-1 text-slate-400 text-xs">...</span>
                    </template>

                    <button 
                        type="button" 
                        @click="nextRacePage" 
                        :disabled="raceCurrentPage >= raceTotalPages" 
                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-1"
                    >
                        <span>Berikutnya</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>

            <div class="mt-4 text-center text-slate-400 text-xs">
                Klik kartu atau tombol Lap untuk catat waktu manual, atau gunakan kamera Line-Crossing / Numpad Spotter.
            </div>
        </div>

        <div v-if="currentView === 'results'" class="space-y-4 animate-fade-in">
            <!-- Results Header & Actions Bar -->
            <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hasil Akhir Perlombaan</div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">@{{ raceName || 'Race Master Pro' }} • @{{ raceCategory }}</h2>
                </div>
                
                <div class="grid grid-cols-2 sm:flex items-center gap-2 no-print">
                    <button @click="currentView = 'race'" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center gap-1.5 transition border border-slate-300 dark:border-slate-700">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Timer</span>
                    </button>
                    <button v-if="publicResultsUrl || sessionSlug" @click="copyResultsLink" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-black dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>Share Link</span>
                    </button>
                    <button onclick="window.print()" class="col-span-2 sm:col-span-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                        <i class="fa-solid fa-print"></i>
                        <span>Print Hasil</span>
                    </button>
                </div>
            </div>

            <!-- Results Data Container -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors">
                <!-- Mobile Finisher Stack View -->
                <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-for="(p, idx) in sortedResults" :key="p.id" class="p-4 space-y-3" :class="{'bg-amber-50/30 dark:bg-amber-950/20': idx === 0 && p.status === 'finished'}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Finisher Rank Badge -->
                                <div v-if="p.status === 'finished'" class="w-8 h-8 rounded-xl font-bold flex items-center justify-center shrink-0 text-sm shadow-sm"
                                    :class="{
                                        'bg-amber-500 text-slate-950 font-black': idx === 0,
                                        'bg-slate-300 dark:bg-slate-700 text-slate-900 dark:text-white': idx === 1,
                                        'bg-amber-700 text-white': idx === 2,
                                        'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs': idx > 2
                                    }">
                                    @{{ idx + 1 }}
                                </div>
                                <div v-else class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold flex items-center justify-center shrink-0 text-xs">
                                    -
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-sm text-indigo-600 dark:text-indigo-400">#@{{ p.bib }}</span>
                                        <span class="font-bold text-base text-slate-900 dark:text-white truncate">@{{ p.name }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Total @{{ p.laps ? p.laps.length : 0 }} Putaran
                                    </div>
                                </div>
                            </div>

                            <span v-if="p.status === 'dnf'" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 border border-red-200 dark:border-red-800 shrink-0">
                                DNF
                            </span>
                            <span v-else-if="p.status === 'finished'" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
                                FINISH
                            </span>
                            <span v-else class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 shrink-0">
                                RUNNING
                            </span>
                        </div>

                        <!-- 3-Column Performance Stats -->
                        <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400">Waktu Finish</div>
                                <div class="font-mono font-black text-sm text-indigo-600 dark:text-indigo-400">
                                    @{{ p.totalTime ? formatTime(p.totalTime) : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400">Average Pace</div>
                                <div class="font-mono font-bold text-sm text-slate-800 dark:text-slate-200">
                                    @{{ formatPace(p) }}
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons for 4:5 Sports Finisher Card -->
                        <div v-if="p.status === 'finished'" class="pt-1 no-print">
                            <button @click="openMediaModal('card', p)" class="w-full py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                                <i class="fa-solid fa-award text-amber-300"></i>
                                <span>Kartu Finisher 4:5</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="sortedResults.length === 0" class="p-8 text-center text-slate-400 text-xs">
                        Belum ada data hasil perlombaan.
                    </div>
                </div>

                <!-- Desktop Table View -->
                <table class="w-full text-left hidden md:table">
                    <thead class="bg-slate-50 border-b border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                        <tr>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">Rank</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">BIB</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">Nama</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Laps</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Total Time</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Pace</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Status</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center no-print dark:text-slate-400">Kartu Finisher</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        <tr v-for="(p, idx) in sortedResults" :key="p.id" :class="{'bg-emerald-50/50 dark:bg-emerald-950/40': idx < 3 && p.status === 'finished'}" class="hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">
                            <td class="p-4 font-bold text-slate-400 dark:text-slate-500">
                                <span v-if="p.status === 'finished'" :class="{'text-amber-500 font-black text-xl': idx===0, 'text-slate-500 dark:text-slate-400': idx > 2}">#@{{ idx + 1 }}</span>
                                <span v-else>-</span>
                            </td>
                            <td class="p-4 font-mono font-bold text-base text-slate-900 dark:text-white">@{{ p.bib }}</td>
                            <td class="p-4 font-bold text-slate-800 dark:text-slate-200">@{{ p.name }}</td>
                            <td class="p-4 text-center">
                                <span class="bg-slate-100 px-2.5 py-1 rounded text-xs font-bold dark:bg-slate-800 dark:text-slate-300">@{{ p.laps.length }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-indigo-700 dark:text-indigo-400">
                                @{{ formatTime(p.totalTime) }}
                            </td>
                            <td class="p-4 text-right font-mono text-slate-700 dark:text-slate-300">@{{ formatPace(p) }}</td>
                            <td class="p-4 text-center">
                                <span v-if="p.status === 'dnf'" class="bg-red-100 text-red-700 text-xs px-2.5 py-1 rounded-lg font-bold dark:bg-red-950 dark:text-red-300 dark:border dark:border-red-800">DNF</span>
                                <span v-else-if="p.status === 'finished'" class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-lg font-bold dark:bg-emerald-950 dark:text-emerald-300 dark:border dark:border-emerald-800">FINISH</span>
                                <span v-else class="text-slate-400 text-xs dark:text-slate-500 font-bold">RUNNING</span>
                            </td>
                            <td class="p-4 text-center no-print flex justify-center gap-2">
                                <button v-if="p.status === 'finished'" @click="openMediaModal('card', p)" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm" title="Buka Kartu Finisher 4:5">
                                    <i class="fa-solid fa-award text-amber-300"></i>
                                    <span>Kartu 4:5</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
            
            <!-- DNF Section -->
            <div v-if="dnfParticipants.length > 0" class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 space-y-2">
                <h3 class="text-red-700 dark:text-red-400 font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Did Not Finish (DNF) - @{{ dnfParticipants.length }} Peserta</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                    <div v-for="p in dnfParticipants" :key="p.id" class="p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-red-200 dark:border-red-800 flex items-center gap-2 text-xs">
                        <span class="font-mono font-bold text-red-600 dark:text-red-400">#@{{ p.bib }}</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate">@{{ p.name }}</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Face Capture Webcam Modal for Multi-Angle Participant Enrollment -->
    <div v-if="faceCaptureModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80" @click.self="closeFaceCaptureModal">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5 w-full max-w-lg shadow-2xl relative text-white space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-base font-bold flex items-center gap-2">
                        <i class="fa-solid fa-camera text-indigo-400"></i> Perekaman Biometrik Multi-Angle
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Ambil 3 sudut wajah pelari agar akurat saat melewati finish gate.</p>
                </div>
                <button type="button" @click="closeFaceCaptureModal" class="text-slate-400 hover:text-white p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Angle Step Selector Tabs -->
            <div class="grid grid-cols-3 gap-2 text-xs">
                <button type="button" @click="faceEnrollStep = 'front'"
                    :class="faceEnrollStep === 'front' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-400' : (newFacePhotos.front ? 'bg-emerald-950 border border-emerald-600 text-emerald-300 font-bold' : 'bg-slate-800 text-slate-400')"
                    class="py-2 px-2 rounded-xl text-center transition flex flex-col items-center gap-1">
                    <div class="flex items-center gap-1 text-[11px]">
                        <i v-if="newFacePhotos.front" class="fa-solid fa-check text-emerald-400"></i>
                        <span>1. Depan (0°)</span>
                    </div>
                    <span class="text-[10px] opacity-75">Tatap Lurus</span>
                </button>

                <button type="button" @click="faceEnrollStep = 'right'"
                    :class="faceEnrollStep === 'right' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-400' : (newFacePhotos.right ? 'bg-emerald-950 border border-emerald-600 text-emerald-300 font-bold' : 'bg-slate-800 text-slate-400')"
                    class="py-2 px-2 rounded-xl text-center transition flex flex-col items-center gap-1">
                    <div class="flex items-center gap-1 text-[11px]">
                        <i v-if="newFacePhotos.right" class="fa-solid fa-check text-emerald-400"></i>
                        <span>2. Kanan (30°)</span>
                    </div>
                    <span class="text-[10px] opacity-75">Serong Kanan</span>
                </button>

                <button type="button" @click="faceEnrollStep = 'left'"
                    :class="faceEnrollStep === 'left' ? 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-400' : (newFacePhotos.left ? 'bg-emerald-950 border border-emerald-600 text-emerald-300 font-bold' : 'bg-slate-800 text-slate-400')"
                    class="py-2 px-2 rounded-xl text-center transition flex flex-col items-center gap-1">
                    <div class="flex items-center gap-1 text-[11px]">
                        <i v-if="newFacePhotos.left" class="fa-solid fa-check text-emerald-400"></i>
                        <span>3. Kiri (30°)</span>
                    </div>
                    <span class="text-[10px] opacity-75">Serong Kiri</span>
                </button>
            </div>
            
            <div class="relative bg-black aspect-video rounded-xl overflow-hidden flex items-center justify-center border border-slate-800">
                <video id="faceCaptureVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                
                <!-- Dynamic Angle Guide Visual Overlay -->
                <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center">
                    <div class="w-40 h-52 rounded-[50%] border-2 border-dashed border-indigo-400/80 bg-indigo-500/10 transition-all duration-300"
                        :class="{
                            'translate-x-0': faceEnrollStep === 'front',
                            'translate-x-6 rotate-6': faceEnrollStep === 'right',
                            '-translate-x-6 -rotate-6': faceEnrollStep === 'left'
                        }">
                    </div>
                    
                    <!-- Angle Instructions Tag -->
                    <div class="absolute bottom-3 px-3 py-1 rounded-full bg-black/80 border border-slate-700 text-xs font-bold text-slate-200">
                        <span v-if="faceEnrollStep === 'front'">Instruksi: Posisikan wajah lurus menatap kamera</span>
                        <span v-else-if="faceEnrollStep === 'right'">Instruksi: Tolehkan wajah sedikit ke kanan (30°)</span>
                        <span v-else-if="faceEnrollStep === 'left'">Instruksi: Tolehkan wajah sedikit ke kiri (30°)</span>
                    </div>
                </div>
            </div>

            <!-- Captured Angle Preview Strip -->
            <div class="grid grid-cols-3 gap-2">
                <div class="p-1.5 bg-slate-950 rounded-xl border border-slate-800 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-slate-800 overflow-hidden shrink-0 flex items-center justify-center">
                        <img v-if="newFacePhotos.front" :src="newFacePhotos.front" class="w-full h-full object-cover" alt="Depan">
                        <i v-else class="fa-solid fa-user text-slate-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-white truncate">Depan</div>
                        <div class="text-[10px]" :class="newFacePhotos.front ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                            @{{ newFacePhotos.front ? 'Tercatat' : 'Kosong' }}
                        </div>
                    </div>
                </div>

                <div class="p-1.5 bg-slate-950 rounded-xl border border-slate-800 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-slate-800 overflow-hidden shrink-0 flex items-center justify-center">
                        <img v-if="newFacePhotos.right" :src="newFacePhotos.right" class="w-full h-full object-cover" alt="Kanan">
                        <i v-else class="fa-solid fa-user text-slate-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-white truncate">Serong Kanan</div>
                        <div class="text-[10px]" :class="newFacePhotos.right ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                            @{{ newFacePhotos.right ? 'Tercatat' : 'Kosong' }}
                        </div>
                    </div>
                </div>

                <div class="p-1.5 bg-slate-950 rounded-xl border border-slate-800 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-slate-800 overflow-hidden shrink-0 flex items-center justify-center">
                        <img v-if="newFacePhotos.left" :src="newFacePhotos.left" class="w-full h-full object-cover" alt="Kiri">
                        <i v-else class="fa-solid fa-user text-slate-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-white truncate">Serong Kiri</div>
                        <div class="text-[10px]" :class="newFacePhotos.left ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                            @{{ newFacePhotos.left ? 'Tercatat' : 'Kosong' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
                <button type="button" @click="closeFaceCaptureModal" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                    Selesai
                </button>
                <button type="button" @click="snapFaceAngle" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-lg">
                    <i class="fa-solid fa-camera"></i>
                    <span>Jepret Sudut @{{ faceEnrollStep === 'front' ? 'Depan' : (faceEnrollStep === 'right' ? 'Kanan' : 'Kiri') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Assign BIB Modal for Unassigned Finish Crossing -->
    <div v-if="assignBibModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80" @click.self="assignBibModalOpen = false">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5 w-full max-w-md shadow-2xl relative text-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold">Tetapkan BIB untuk Hasil Finish Ini</h3>
                    <button type="button" @click="assignBibModalOpen = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>
                
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800 mb-4">
                    <img v-if="selectedUnassignedFinish?.snapshot" :src="selectedUnassignedFinish.snapshot" class="w-16 h-16 rounded-lg object-cover border border-slate-700" alt="Snapshot">
                    <div>
                        <div class="text-xs text-slate-400">Waktu Finish Terdeteksi:</div>
                        <div class="font-mono font-bold text-base text-indigo-400">@{{ selectedUnassignedFinish?.timeFormatted }}</div>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    <label class="block text-xs font-bold text-slate-400 uppercase">Pilih Peserta</label>
                    <div class="max-h-60 overflow-y-auto divide-y divide-slate-800 border border-slate-800 rounded-xl bg-slate-950">
                        <button v-for="p in activeParticipants" :key="p.id" type="button" @click="confirmAssignBib(p)" class="w-full p-3 text-left hover:bg-slate-800 transition-colors flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-sm text-white">@{{ p.name }}</div>
                                <div class="text-xs text-slate-400 font-oswald">BIB: @{{ p.bib }}</div>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white font-bold shrink-0">
                                Pilih
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4:5 Canvas Sports Finisher Card Modal -->
        <div v-if="mediaModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-sm animate-fade-in" @click.self="closeMediaModal">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl p-4 sm:p-6 w-full max-w-lg shadow-2xl relative text-white space-y-4 max-h-[95vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                            <i class="fa-solid fa-award text-amber-300"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Kartu Finisher & E-Poster 4:5</h3>
                            <p class="text-xs text-slate-400">Format Instagram Portrait (1080 x 1350 px)</p>
                        </div>
                    </div>
                    <button @click="closeMediaModal" class="text-slate-400 hover:text-white p-1 text-lg"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Photo Background Uploader -->
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                            Foto Lari Pelari (Opsional)
                        </label>
                        <input @change="onMediaBgChange" type="file" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                        <div class="text-[10px] text-slate-400">
                            Jika tidak ada foto, sistem otomatis merender desain <i>Energetic Sports Dark Theme</i> dengan logo resmi Ruang Lari.
                        </div>
                    </div>

                    <!-- Canvas Preview Box (4:5 Ratio) -->
                    <div class="relative w-full max-w-[340px] mx-auto aspect-[4/5] bg-slate-950 rounded-xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-lg">
                        <img v-if="mediaPreviewUrl" :src="mediaPreviewUrl" class="w-full h-full object-contain" alt="Finisher Card Preview">
                        <div v-else-if="mediaLoading" class="flex flex-col items-center gap-2 text-indigo-400">
                            <i class="fa-solid fa-circle-notch fa-spin text-2xl"></i>
                            <span class="text-xs font-bold text-slate-300">Merender Kartu Finisher...</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800">
                        <button @click="downloadMediaCard" :disabled="!mediaPreviewUrl || mediaLoading" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-white font-bold flex items-center justify-center gap-1.5 transition">
                            <i class="fa-solid fa-download"></i>
                            <span>Unduh PNG HD</span>
                        </button>
                        <button @click="shareMedia" :disabled="!mediaFile || mediaLoading" class="py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold flex items-center justify-center gap-1.5 transition shadow-sm">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span>Bagikan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Fullscreen Live TV Display Screen Overlay -->
    <div v-if="tvDisplayOpen" class="fixed inset-0 z-[200] bg-slate-950 text-white flex flex-col justify-between p-4 sm:p-8 select-none font-sans overflow-y-auto">
        <!-- Top TV Header Bar -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-stopwatch text-xl"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">RuangLari Race Master Live</div>
                    <div class="text-lg sm:text-2xl font-black text-white flex items-center gap-2">
                        <span>@{{ raceName || 'Race Master Pro' }}</span>
                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-950 text-indigo-300 border border-indigo-700/60 text-xs font-bold uppercase">
                            @{{ raceCategory }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- TV Screen Controls: Start, Pause, Finish, Reset, Fullscreen, Close -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Start / Resume Timer -->
                <button v-if="!timer.running" @click="startRace" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-lg" :title="timer.elapsed > 0 ? 'Resume Timer' : 'Start Timer'">
                    <i class="fa-solid fa-play text-xs"></i>
                    <span>@{{ timer.elapsed > 0 ? 'Resume' : 'Start Timer' }}</span>
                </button>

                <!-- Pause Timer -->
                <button v-if="timer.running" @click="pauseRace" 
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-lg" title="Pause Timer">
                    <i class="fa-solid fa-pause text-xs"></i>
                    <span>Pause</span>
                </button>

                <!-- Finish Sesi -->
                <button v-if="timer.elapsed > 0" @click="finishRace" 
                    class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-lg" title="Finish Sesi">
                    <i class="fa-solid fa-flag-checkered text-xs"></i>
                    <span>Finish Sesi</span>
                </button>

                <!-- Reset Timer -->
                <button @click="resetRace" 
                    class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center gap-1.5 transition border border-slate-700" title="Reset Total Timer">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span class="hidden sm:inline">Reset</span>
                </button>

                <!-- Toggle Fullscreen -->
                <button @click="toggleTvFullscreen" 
                    class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-1.5 transition border border-slate-700" title="Toggle Fullscreen">
                    <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                    <span class="hidden sm:inline">@{{ isFullscreen ? 'Keluar Fullscreen' : 'Fullscreen' }}</span>
                </button>

                <!-- Close TV Display -->
                <button @click="closeTvDisplay" 
                    class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-1.5 transition border border-slate-700" title="Tutup Layar TV">
                    <i class="fa-solid fa-xmark"></i>
                    <span class="hidden sm:inline">Tutup</span>
                </button>
            </div>
        </div>

        <!-- Center TV Hero: Massive Official Race Clock -->
        <div class="my-auto py-6 text-center">

    <!-- Race Clock -->
    <div class="mb-8 sm:mb-10">
        <div class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.22em] text-slate-500 mb-3">
            Official Race Clock
        </div>

        <div class="font-mono text-6xl sm:text-8xl md:text-9xl font-bold text-white tracking-tight leading-none tabular-nums inline-flex items-baseline justify-center">
            <span>@{{ tvTimerParts.main }}</span><span class="text-[0.45em] opacity-75 font-semibold ml-1">@{{ tvTimerParts.ms }}</span>
        </div>
    </div>


    <!-- Race Summary -->
    <div class="max-w-4xl mx-auto border-t border-slate-800">

        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-slate-800">

            <!-- Total Peserta -->
            <div class="px-4 py-4 sm:py-5 text-left">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                    <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Peserta
                    </span>
                </div>

                <div class="font-mono text-2xl sm:text-3xl font-semibold text-white tabular-nums">
                    @{{ participants.length }}
                </div>
            </div>


            <!-- Finish -->
            <div class="px-4 py-4 sm:py-5 text-left">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Finish
                    </span>
                </div>

                <div class="font-mono text-2xl sm:text-3xl font-semibold text-white tabular-nums">
                    @{{ finishedCount }}
                </div>
            </div>


            <!-- On Track -->
            <div class="px-4 py-4 sm:py-5 text-left">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        On Track
                    </span>
                </div>

                <div class="font-mono text-2xl sm:text-3xl font-semibold text-white tabular-nums">
                    @{{ runningCount }}
                </div>
            </div>


            <!-- DNF -->
            <div class="px-4 py-4 sm:py-5 text-left">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        DNF
                    </span>
                </div>

                <div class="font-mono text-2xl sm:text-3xl font-semibold text-white tabular-nums">
                    @{{ dnfCount }}
                </div>
            </div>

        </div>
    </div>

</div>

        <!-- Bottom TV Section: Latest Finisher + Top 5 Leaderboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-800/80">
            <!-- Left: Latest Finisher Live Alert -->
            <div class="p-4 sm:p-5 bg-slate-900 rounded-2xl border-2 border-emerald-500 shadow-xl space-y-2">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-black uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Finisher Terbaru (Live)</span>
                    </div>
                    <span v-if="tvLatestFinisher" class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/40">
                        Finish Berhasil
                    </span>
                </div>

                <div v-if="tvLatestFinisher" class="space-y-1.5 pt-1">
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-2xl sm:text-3xl font-black text-emerald-400">#@{{ tvLatestFinisher.bib }}</span>
                        <span class="text-xl sm:text-2xl font-black text-white truncate">@{{ tvLatestFinisher.name }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs sm:text-sm font-mono text-slate-300">
                        <span>Waktu: <strong class="text-white text-base">@{{ formatTime(tvLatestFinisher.totalTime) }}</strong></span>
                        <span>• Pace: <strong class="text-white">@{{ formatPace(tvLatestFinisher) }}</strong></span>
                    </div>
                </div>
                <div v-else class="text-slate-500 text-xs py-3 font-medium">
                    Menunggu pelari pertama melintasi garis finish...
                </div>
            </div>

            <!-- Right: Top 5 Leaderboard -->
            <div class="p-4 sm:p-5 bg-slate-900 rounded-2xl border border-slate-800 space-y-2">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Top 5 Leaderboard</div>
                <div class="space-y-1.5">
                    <div v-for="(p, idx) in top5Results" :key="p.id" class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-lg font-black flex items-center justify-center shrink-0 text-xs"
                                :class="{
                                    'bg-amber-500 text-slate-950': idx === 0,
                                    'bg-slate-300 text-slate-950': idx === 1,
                                    'bg-amber-700 text-white': idx === 2,
                                    'bg-slate-800 text-slate-400': idx > 2
                                }">
                                @{{ idx + 1 }}
                            </span>
                            <span class="font-mono font-bold text-indigo-400">#@{{ p.bib }}</span>
                            <span class="font-bold text-white truncate">@{{ p.name }}</span>
                        </div>
                        <div class="font-mono font-black text-emerald-400 shrink-0 ml-2">
                            @{{ formatTime(p.totalTime) }}
                        </div>
                    </div>
                    <div v-if="top5Results.length === 0" class="text-slate-500 text-xs py-3 font-medium">
                        Belum ada finisher di leaderboard.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referee Slow-Motion Video Review Modal -->
    <div v-if="videoReviewModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-sm" @click.self="closeVideoReviewModal">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-4 sm:p-6 w-full max-w-4xl shadow-2xl relative text-white space-y-4 max-h-[95vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                        <i class="fa-solid fa-film text-amber-300"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Review Video Finish Juri (Slow-Motion)</h3>
                        <p class="text-xs text-slate-400">Verifikasi visual urutan pelari garis finish</p>
                    </div>
                </div>
                <button type="button" @click="closeVideoReviewModal" class="text-slate-400 hover:text-white p-1 text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-4">
                <!-- Video Player Container -->
                <div class="relative bg-black rounded-xl overflow-hidden aspect-video border border-slate-800 shadow-inner flex items-center justify-center">
                    <video id="refereeVideoPlayer" :src="recordedVideoUrl" controls playsinline class="w-full h-full object-contain"></video>
                </div>

                <!-- Video Controls & Slow-Motion Speed Bar -->
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                        <!-- Playback Speed Controls -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-slate-400 font-bold uppercase tracking-wider mr-1">Kecepatan:</span>
                            <button type="button" @click="setVideoSpeed(0.25)" 
                                :class="videoPlaybackRate === 0.25 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-800'"
                                class="px-2.5 py-1 rounded-lg transition">
                                0.25x (Super Slow)
                            </button>
                            <button type="button" @click="setVideoSpeed(0.5)" 
                                :class="videoPlaybackRate === 0.5 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-800'"
                                class="px-2.5 py-1 rounded-lg transition">
                                0.5x (Slow)
                            </button>
                            <button type="button" @click="setVideoSpeed(0.75)" 
                                :class="videoPlaybackRate === 0.75 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-800'"
                                class="px-2.5 py-1 rounded-lg transition">
                                0.75x
                            </button>
                            <button type="button" @click="setVideoSpeed(1.0)" 
                                :class="videoPlaybackRate === 1.0 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-800'"
                                class="px-2.5 py-1 rounded-lg transition">
                                1.0x (Normal)
                            </button>
                            <button type="button" @click="setVideoSpeed(2.0)" 
                                :class="videoPlaybackRate === 2.0 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:text-white border border-slate-800'"
                                class="px-2.5 py-1 rounded-lg transition">
                                2.0x (Cepat)
                            </button>
                        </div>

                        <!-- Frame-by-Frame Precision Step Controls -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 font-bold uppercase tracking-wider mr-1">Frame Step:</span>
                            <button type="button" @click="stepVideoFrame(-0.05)" class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 font-mono transition" title="Mundur 1 Frame (50ms)">
                                <i class="fa-solid fa-backward-step mr-1"></i> -1 Frame
                            </button>
                            <button type="button" @click="stepVideoFrame(0.05)" class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 font-mono transition" title="Maju 1 Frame (50ms)">
                                +1 Frame <i class="fa-solid fa-forward-step ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Action Footer: Download Video -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                    <div class="text-xs text-slate-400">
                        File tersimpan di memori browser lokal (0% beban server).
                    </div>
                    <button type="button" @click="downloadRecordedVideo" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm border border-slate-700">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Video (.webm / .mp4)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const { createApp, ref, computed, onMounted, onBeforeUnmount, nextTick } = Vue;

    createApp({
        setup() {
            // Data
            // Tesseract / OCR State
            let ocrWorker = null;
            let ocrWorkerInitPromise = null;
            let ocrBusy = false;
            let ocrQueue = Promise.resolve();
            let ocrQueueDepth = 0;

            // FAST GLOBAL OCR ----------------------------------------------------------
            // OCR is intentionally independent from COCO-SSD/person tracking.
            // The whole camera frame is scanned on a global throttle. As soon as a BIB-like
            // pattern is found, the best participant match is processed immediately.
            const OCR_GLOBAL_INTERVAL_MS = 450; // target: 400-600 ms
            const OCR_GLOBAL_MAX_WIDTH = 960;   // speed-first full-frame OCR
            let globalOcrTimer = null;
            let globalOcrInFlight = false;
            let ocrWorkerProfileSignature = '';

            // BIB Number Scan Only Mode -----------------------------------------------
            // Reactive state for UI bindings
            const bibScan = ref({
                active: false,
                confirmFrames: 2,       // frames required before committing a record
                confirmCount: 0,        // current frames confirmed in the active buffer slot
                cooldownSec: 5,         // per-BIB anti-duplicate cooldown (seconds)
                minScore: 60,           // minimum fuzzy-match score to accept a candidate
                distance: 'auto',       // 'close' | 'auto' | 'far'
                sampleImages: [],       // [{ name, src, extractedBibs[] }]
                trainStatus: '',        // '' | 'processing' | 'done'
                confirmBuffer: {},      // { [bibKey]: { count, capturedAt, elapsedMs, participant, score } }
                lastDetected: null,     // { bib, name, time } — shown in overlay
                scanStatus: 'idle',     // 'idle' | 'scanning' | 'confirming' | 'locked'
                aggressiveSyncActive: false,
            });

            // Non-reactive: in-memory per-device lock — prevents double record on this device
            // even before pollLiveSync propagates from another device.
            const localRecordedBibs = new Set(); // key: "bib:lapIndex"
            let bibScanLoopTimer  = null;
            let bibScanInFlight   = false;
            let bibScanCanvasId   = null; // requestAnimationFrame handle
            const BIB_CONFIRM_TTL_MS   = 3000; // discard pending buffer entry after 3 s of no match

            // Tahap 2: Two-Stage RoI Accelerated Micro-Intervals & Canvas Max Widths
            const BIB_SCAN_INTERVALS   = { close: 140, auto: 180, far: 250 }; // ms (2x-3x accelerated)
            const BIB_SCAN_MAX_WIDTHS  = { close: 440, auto: 520, far: 640 }; // px (RoI micro-canvas)


            const currentView = ref('setup'); // setup, bibs, race, results
            const isSessionHost = ref(true); // only the session creator/host can finish or reset the session
            const raceName = ref('');
            const existingRaces = ref([]);
            const eoEvents = ref([]);
            const eoEventsLoading = ref(false);
            const selectedEoEventId = ref('');
            const selectedEoCategoryId = ref('all');
            const importingEoParticipants = ref(false);
            const raceCategory = ref('5K');
            const categories = ['400M','800M','1500M','1600M','3000M','3200M','5K','10K','HM','FM'];
            const currentRaceId = ref(null);
            const currentSessionId = ref(null);
            const raceDistanceKm = ref('');
            const publicResultsUrl = ref('');
            const sessionSlug = ref('');
            const raceLogoUrl = ref('');
            const raceLogoFile = ref(null);
            const raceLogoPreviewUrl = ref('');
            const raceLogoFileName = ref('');
            const newName = ref('');
            const newBib = ref('');
            // const newPredictedTime = ref(''); // Removed in favor of HH:MM:SS
            const newPredictedHH = ref('');
            const newPredictedMM = ref('');
            const newPredictedSS = ref('');
            const inputName = ref(null);
            const inputBib = ref(null);
            const mobileMenuOpen = ref(false);

            // Face Biometrics State (Multi-Angle)
            const newFacePhoto = ref('');
            const newFacePhotos = ref({ front: '', right: '', left: '' });
            const newFaceDescriptors = ref({ front: null, right: null, left: null });
            const faceEnrollStep = ref('front'); // 'front' | 'right' | 'left'
            const newFaceProcessing = ref(false);
            const faceModelLoading = ref(false);
            const faceCaptureModalOpen = ref(false);
            let faceCaptureStream = null;
            let faceModelsLoaded = false;

            const enrolledAngleCount = computed(() => {
                let c = 0;
                if (newFacePhotos.value.front) c++;
                if (newFacePhotos.value.right) c++;
                if (newFacePhotos.value.left) c++;
                return c;
            });
            
            // Core Data Structure
            const participants = ref([]); 
            // { id: 'uuid', bib: '101', name: 'Budi', photoUrl: '', faceDescriptor: [], laps: [timestamp, timestamp], status: 'ready', totalTime: 0, recentlyScanned: false, lastScanTime: 0 }

            // Timer
            const timer = ref({
                running: false,
                paused: false,
                startTime: null,
                elapsed: 0,
                interval: null
            });

            // Camera & AI Vision State
            let cocoModel = null;
            let aiStream = null;
            let aiAnimationId = null;
            let personTracks = new Map();
            let nextTrackId = 1;
            let lastFpsCalc = Date.now();
            let frameCount = 0;

            const cameraSettingsOpen = ref(false);
            const raceSettings = ref({
                raceMode: 'single', // 'single' | 'multi_lap'
                targetLaps: 1,
                minLapCooldownSec: 30,
                enableQr: true,
                enableOcr: true,
                enableFaceAi: true,
                enableBeep: true,
                stationMode: 'master', // 'master' | 'satellite'
                ocrScope: 'full_frame', // FAST GLOBAL OCR always scans the whole frame
            });

            // Manual BIB Rapid Entry & Validation State
            const manualBibInput = ref('');
            const lockedBibPrefix = ref('');
            const autoSubmitDigits = ref(0);
            const manualValidationAlert = ref({
                show: false,
                type: '', // 'error' | 'warning' | 'success' | 'info'
                message: '',
                bib: '',
                name: '',
                time: ''
            });

            // Quick Add Unregistered Participant State (Mobile Thumb-Friendly Bottom Sheet)
            const pendingUnregisteredBib = ref({
                show: false,
                bib: '',
                rawDigits: '',
                prefix: '',
                timing: null,
                formattedTime: ''
            });

            // Multi-Admin Live Room Sync State
            const sessionRoomInput = ref('');
            const sessionSyncActive = ref(false);
            const sessionSyncLastUpdated = ref(null);
            const sessionSyncError = ref('');
            let sessionSyncTimer = null;
            let lastSeenLapIdSet = new Set();

            // TV / Jumbotron Display State (Admin 1)
            const tvDisplayOpen = ref(false);
            const isFullscreen = ref(false);
            const tvLatestFinisher = ref(null);
            const tvTimerFont = ref('dseg'); // 'dseg' (7-Segment LED) | 'orbitron' (Digital Tech) | 'mono' (Standard Monospace)
            const tvTimerColor = ref('amber'); // 'amber' (Seiko Marathon Gold) | 'emerald' | 'red' | 'white'
            const tvTimerSizeScale = ref(100); // 50 to 180 (%)
            const tvSettingsOpen = ref(false);

            const finishedCount = computed(() => participants.value.filter(p => p.status === 'finished').length);
            const runningCount = computed(() => participants.value.filter(p => p.status === 'running' || p.status === 'ready' || !p.status).length);
            const dnfCount = computed(() => participants.value.filter(p => p.status === 'dnf').length);
            const top5Results = computed(() => sortedResults.value.filter(p => p.status === 'finished').slice(0, 5));

            const toggleTvFullscreen = () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().then(() => {
                        isFullscreen.value = true;
                    }).catch(err => {
                        console.log(err);
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().then(() => {
                            isFullscreen.value = false;
                        }).catch(err => {
                            console.log(err);
                        });
                    }
                }
            };

            const tvResultsShortUrl = computed(() => {
                const slug = sessionSlug.value || currentSessionId.value;
                if (!slug) return `${window.location.host}/r`;
                return `${window.location.host}/r/${slug}`;
            });

            const tvResultsQrUrl = computed(() => {
                const slug = sessionSlug.value || currentSessionId.value;
                if (!slug) return `${window.location.origin}/tools/race-master`;
                return `${window.location.origin}/r/${encodeURIComponent(slug)}`;
            });

            const generateTvResultsQrCode = () => {
                const el = document.getElementById('tvResultsQrCode');
                if (!el) return;
                el.innerHTML = '';
                const url = tvResultsQrUrl.value;
                if (!url) return;
                try {
                    new QRCode(el, {
                        text: url,
                        width: 96,
                        height: 96,
                        correctLevel: QRCode.CorrectLevel.M
                    });
                } catch (e) {}
            };

            const openTvDisplay = () => {
                tvDisplayOpen.value = true;
                nextTick(() => {
                    generateTvResultsQrCode();
                });
                try {
                    if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen().then(() => {
                            isFullscreen.value = true;
                        }).catch(() => {});
                    }
                } catch (e) {}
            };

            const closeTvDisplay = () => {
                tvDisplayOpen.value = false;
                try {
                    if (document.fullscreenElement && document.exitFullscreen) {
                        document.exitFullscreen().then(() => {
                            isFullscreen.value = false;
                        }).catch(() => {});
                    }
                } catch (e) {}
            };

            const copyTvDisplayUrl = async () => {
                const slug = sessionSlug.value || currentSessionId.value;
                if (!slug) {
                    alert('Belum ada sesi aktif. Start race atau buat sesi terlebih dahulu.');
                    return;
                }
                const origin = window.location.origin;
                const pathname = window.location.pathname;
                const tvUrl = `${origin}${pathname}?session=${encodeURIComponent(slug)}&mode=tv`;
                try {
                    await navigator.clipboard.writeText(tvUrl);
                    alert('URL Layar TV disalin!\nBuka link ini di laptop/layar TV untuk langsung masuk ke mode fullscreen otomatis.');
                } catch (e) {
                    prompt('Salin link Layar TV ini:', tvUrl);
                }
            };

            const triggerTvFinisherFlash = (participant) => {
                if (!participant) return;
                const rank = participants.value.filter(item => item.status === 'finished').length;
                tvLatestFinisher.value = {
                    bib: participant.bib,
                    name: participant.name,
                    time: formatTime(participant.totalTime),
                    rank: rank,
                    timestamp: Date.now()
                };
                setTimeout(() => {
                    if (tvLatestFinisher.value && Date.now() - tvLatestFinisher.value.timestamp >= 5000) {
                        tvLatestFinisher.value = null;
                    }
                }, 5500);
            };

            const setDetectorPreset = (preset) => {
                if (preset === 'all') {
                    raceSettings.value.enableQr = true;
                    raceSettings.value.enableOcr = true;
                    raceSettings.value.enableFaceAi = true;
                } else if (preset === 'qr') {
                    raceSettings.value.enableQr = true;
                    raceSettings.value.enableOcr = false;
                    raceSettings.value.enableFaceAi = false;
                } else if (preset === 'ocr') {
                    raceSettings.value.enableQr = false;
                    raceSettings.value.enableOcr = true;
                    raceSettings.value.enableFaceAi = false;
                } else if (preset === 'face') {
                    raceSettings.value.enableQr = false;
                    raceSettings.value.enableOcr = false;
                    raceSettings.value.enableFaceAi = true;
                }
                saveState();
            };

            const currentDetectorPreset = computed(() => {
                const { enableQr, enableOcr, enableFaceAi } = raceSettings.value;
                if (enableQr && enableOcr && enableFaceAi) return 'all';
                if (enableQr && !enableOcr && !enableFaceAi) return 'qr';
                if (!enableQr && enableOcr && !enableFaceAi) return 'ocr';
                if (!enableQr && !enableOcr && enableFaceAi) return 'face';
                return 'custom';
            });

            const camera = ref({
                active: false,
                scanner: null,
                lastScanMsg: '',
                busy: false,
                mode: 'ai_line', // 'ai_line' | 'qr_only'
                devices: [],
                selectedDeviceId: '',
                aiModelLoading: false,
                aiModelReady: false,
                fps: 0,
                crossingCount: 0,
                line: {
                    x1: 0.5, y1: 0.1,
                    x2: 0.5, y2: 0.9,
                    direction: 'any'
                },
                draggingPoint: null,
                lineFlash: false,
                minConfidence: 0.45,
            });

            const liveFinishFeed = ref([]);
            const assignBibModalOpen = ref(false);
            const selectedUnassignedFinish = ref(null);

            const certificatesByBib = ref({});
            const sensorSettingsOpen = ref(false);
            const posterBackgroundFile = ref(null);
            const posterUrl = ref('');
            const posterFile = ref(null);
            const canNativeShare = !!(navigator && navigator.share && navigator.canShare);

            // Local Referee Video Recording State (Client-Side, 0% Server Burden)
            let mediaRecorder = null;
            let recordingChunks = [];
            let recordingTimer = null;
            const isRecordingVideo = ref(false);
            const recordingDuration = ref(0);
            const recordedVideoUrl = ref('');
            const recordedVideoBlob = ref(null);
            const videoReviewModalOpen = ref(false);
            const videoPlaybackRate = ref(1.0);

            const formatRecordingDuration = computed(() => {
                const sec = recordingDuration.value;
                const h = Math.floor(sec / 3600);
                const m = Math.floor((sec % 3600) / 60);
                const s = sec % 60;
                return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            });

            const startVideoRecording = () => {
                const videoEl = getReaderVideo();
                const stream = aiStream || (videoEl && videoEl.srcObject);
                if (!stream) {
                    alert('Kamera belum aktif. Buka kamera terlebih dahulu untuk mulai merekam.');
                    return;
                }

                try {
                    recordingChunks = [];
                    let options = { mimeType: 'video/webm;codecs=vp9' };
                    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                        options = { mimeType: 'video/webm' };
                        if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                            options = { mimeType: 'video/mp4' };
                            if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                                options = undefined;
                            }
                        }
                    }

                    mediaRecorder = options ? new MediaRecorder(stream, options) : new MediaRecorder(stream);

                    mediaRecorder.ondataavailable = (e) => {
                        if (e.data && e.data.size > 0) {
                            recordingChunks.push(e.data);
                        }
                    };

                    mediaRecorder.onstop = () => {
                        if (recordingChunks.length > 0) {
                            const mime = mediaRecorder.mimeType || 'video/webm';
                            const blob = new Blob(recordingChunks, { type: mime });
                            if (recordedVideoUrl.value && recordedVideoUrl.value.startsWith('blob:')) {
                                try { URL.revokeObjectURL(recordedVideoUrl.value); } catch (_) {}
                            }
                            recordedVideoUrl.value = URL.createObjectURL(blob);
                            recordedVideoBlob.value = blob;
                        }
                        if (recordingTimer) {
                            clearInterval(recordingTimer);
                            recordingTimer = null;
                        }
                        isRecordingVideo.value = false;
                    };

                    mediaRecorder.start(1000);
                    isRecordingVideo.value = true;
                    recordingDuration.value = 0;
                    recordingTimer = setInterval(() => {
                        recordingDuration.value++;
                    }, 1000);
                } catch (e) {
                    console.error('Start recording error:', e);
                    alert('Gagal memulai perekaman video lokal: ' + (e?.message || e));
                    isRecordingVideo.value = false;
                }
            };

            const stopVideoRecording = () => {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                }
            };

            const toggleVideoRecording = () => {
                if (isRecordingVideo.value) {
                    stopVideoRecording();
                } else {
                    startVideoRecording();
                }
            };

            const openVideoReviewModal = () => {
                if (!recordedVideoUrl.value) return;
                videoReviewModalOpen.value = true;
                videoPlaybackRate.value = 1.0;
            };

            const closeVideoReviewModal = () => {
                videoReviewModalOpen.value = false;
                const player = document.getElementById('refereeVideoPlayer');
                if (player) player.pause();
            };

            const setVideoSpeed = (rate) => {
                videoPlaybackRate.value = rate;
                const player = document.getElementById('refereeVideoPlayer');
                if (player) player.playbackRate = rate;
            };

            const stepVideoFrame = (seconds) => {
                const player = document.getElementById('refereeVideoPlayer');
                if (player) {
                    player.pause();
                    player.currentTime = Math.max(0, Math.min(player.duration || 0, player.currentTime + seconds));
                }
            };

            const downloadRecordedVideo = () => {
                if (!recordedVideoUrl.value) return;
                const a = document.createElement('a');
                a.href = recordedVideoUrl.value;
                const ext = recordedVideoBlob.value?.type?.includes('mp4') ? 'mp4' : 'webm';
                a.download = `video-finish-juri-${raceName.value || 'race'}-${Date.now()}.${ext}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            };

            const STORAGE_KEY = 'race-master-pro:v1';
            let hydrating = false;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const isAuthenticated = @json(auth()->check());
            const apiBase = @json(url('api/tools/race-master'));
            const resultsBase = @json(url('tools/race-master/results'));

            const apiFetchJson = async (url, options = {}) => {
                const headers = options.headers ? { ...options.headers } : {};
                headers['Accept'] = 'application/json';
                if (csrf) headers['X-CSRF-TOKEN'] = csrf;
                
                // Ensure credentials are sent (cookies)
                const fetchOptions = { ...options, headers };
                fetchOptions.credentials = 'same-origin';

                const res = await fetch(url, fetchOptions);
                const data = await res.json().catch(() => null);
                if (!res.ok) {
                    const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Request gagal.';
                    throw new Error(msg);
                }
                return data;
            };

            const lapQueue = ref([]);
            const queueFlushBusy = ref(false);
            const queueFlushInterval = ref(null);
            const QUEUE_STORAGE_KEY = STORAGE_KEY + ':lap-queue';

            const queueLoad = () => {
                try {
                    const raw = localStorage.getItem(QUEUE_STORAGE_KEY);
                    if (!raw) return;
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) lapQueue.value = parsed;
                } catch (e) {}
            };

            const queueSave = () => {
                try { localStorage.setItem(QUEUE_STORAGE_KEY, JSON.stringify(lapQueue.value.slice(-2000))); } catch (e) {}
            };

            const queueEnqueueLap = (bib, totalTimeMs, recordedAtIso) => {
                lapQueue.value.push({
                    bib_number: String(bib ?? '').trim(),
                    total_time_ms: Math.max(0, Math.floor(totalTimeMs || 0)),
                    recorded_at: recordedAtIso || new Date().toISOString(),
                });
                queueSave();
            };

            const queueFlush = async () => {
                if (queueFlushBusy.value) return;
                const sess = sessionSlug.value || currentSessionId.value;
                if (!sess) return;
                if (!lapQueue.value.length) return;
                if (!navigator.onLine) return;

                queueFlushBusy.value = true;
                try {
                    const batch = lapQueue.value.slice(0, 50);
                    let res = null;
                    try {
                        res = await apiFetchJson(`${apiBase}/sessions/${encodeURIComponent(String(sess))}/laps/bulk`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ laps: batch }),
                        });
                    } catch (err) {
                        // Fallback to public endpoint for guest/assistant without login
                        res = await apiFetchJson(`${apiBase}/public/${encodeURIComponent(String(sess))}/laps/bulk`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ laps: batch }),
                        });
                    }
                    lapQueue.value = lapQueue.value.slice(batch.length);
                    queueSave();
                } catch (e) {
                } finally {
                    queueFlushBusy.value = false;
                }
            };

            const apiFetchBlob = async (url, options = {}) => {
                const headers = options.headers ? { ...options.headers } : {};
                if (csrf) headers['X-CSRF-TOKEN'] = csrf;
                const res = await fetch(url, { ...options, headers });
                if (!res.ok) {
                    const data = await res.json().catch(() => null);
                    const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Request gagal.';
                    throw new Error(msg);
                }
                return await res.blob();
            };

            const saveState = () => {
                if (hydrating) return;
                try {
                    const payload = {
                        raceName: raceName.value,
                        raceCategory: raceCategory.value,
                        raceDistanceKm: raceDistanceKm.value,
                        raceId: currentRaceId.value,
                        sessionId: currentSessionId.value,
                        publicResultsUrl: publicResultsUrl.value,
                        sessionSlug: sessionSlug.value,
                        raceLogoUrl: raceLogoUrl.value,
                        certificatesByBib: certificatesByBib.value,
                        participants: participants.value,
                        timerElapsed: timer.value.elapsed,
                        currentView: currentView.value,
                    };
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                } catch (e) {}
            };

            const clearState = () => {
                try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
            };

            const loadState = () => {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) return;
                    const parsed = JSON.parse(raw);
                    hydrating = true;
                    if (parsed && parsed.raceName) raceName.value = parsed.raceName;
                    if (parsed && parsed.raceCategory) raceCategory.value = parsed.raceCategory;
                    if (parsed && parsed.raceDistanceKm) raceDistanceKm.value = parsed.raceDistanceKm;
                    if (parsed && parsed.raceId) currentRaceId.value = parsed.raceId;
                    if (parsed && parsed.sessionId) currentSessionId.value = parsed.sessionId;
                    if (parsed && parsed.publicResultsUrl) publicResultsUrl.value = parsed.publicResultsUrl;
                    if (parsed && parsed.sessionSlug) sessionSlug.value = parsed.sessionSlug;
                    if (parsed && parsed.raceLogoUrl) raceLogoUrl.value = parsed.raceLogoUrl;
                    if (parsed && parsed.certificatesByBib) certificatesByBib.value = parsed.certificatesByBib;
                    if (raceLogoUrl.value) {
                        raceLogoPreviewUrl.value = raceLogoUrl.value;
                        raceLogoFileName.value = 'Logo tersimpan';
                    }
                    if (parsed && Array.isArray(parsed.participants)) {
                        participants.value = parsed.participants.map(p => ({
                            id: p.id || crypto.randomUUID(),
                            bib: normalizeBib(p.bib) || String(p.bib ?? '').trim().toUpperCase(),
                            name: String(p.name ?? ''),
                            predictedTimeMs: typeof p.predictedTimeMs === 'number' ? p.predictedTimeMs : null,
                            laps: Array.isArray(p.laps) ? p.laps : [],
                            status: p.status || 'ready',
                            totalTime: typeof p.totalTime === 'number' ? p.totalTime : 0,
                            recentlyScanned: false,
                            lastScanTime: typeof p.lastScanTime === 'number' ? p.lastScanTime : 0,
                        })).filter(p => p.bib !== '');
                    }
                    if (parsed && typeof parsed.timerElapsed === 'number') timer.value.elapsed = parsed.timerElapsed;
                    if (parsed && parsed.currentView) currentView.value = parsed.currentView;
                } catch (e) {} finally {
                    hydrating = false;
                }
            };

            // Auto-join session if session parameter exists in URL (e.g. /tools/race-master?session=vj9umczohn)
            const checkUrlSession = () => {
                try {
                    const params = new URLSearchParams(window.location.search);
                    const sess = params.get('session') || params.get('room');
                    if (sess) {
                        sessionRoomInput.value = sess;
                        joinLiveSession(sess);
                    }
                } catch (e) {}
            };

            // EO Events Integration & Autocomplete
            const eoEventSearchQuery = ref('');
            const eoDropdownOpen = ref(false);
            const eoAutocompleteContainer = ref(null);

            const filteredEoEvents = computed(() => {
                const q = (eoEventSearchQuery.value || '').trim().toLowerCase();
                if (!q) return eoEvents.value;
                return eoEvents.value.filter(e => {
                    const name = (e.name || '').toLowerCase();
                    const startAt = (e.start_at || '').toLowerCase();
                    const loc = (e.location || '').toLowerCase();
                    return name.includes(q) || startAt.includes(q) || loc.includes(q);
                });
            });

            const onEoSearchInput = () => {
                eoDropdownOpen.value = true;
                if (!eoEventSearchQuery.value) {
                    selectedEoEventId.value = '';
                }
            };

            const selectEoEvent = (ev) => {
                if (!ev) return;
                selectedEoEventId.value = ev.id;
                eoEventSearchQuery.value = `${ev.name}${ev.start_at ? ' (' + ev.start_at + ')' : ''}`;
                eoDropdownOpen.value = false;
                onEoEventChange();
            };

            const clearSelectedEoEvent = () => {
                selectedEoEventId.value = '';
                eoEventSearchQuery.value = '';
                selectedEoCategoryId.value = 'all';
                eoDropdownOpen.value = false;
            };

            const handleClickOutsideEo = (e) => {
                if (eoAutocompleteContainer.value && !eoAutocompleteContainer.value.contains(e.target)) {
                    eoDropdownOpen.value = false;
                }
            };

            const selectedEoEvent = computed(() => {
                return eoEvents.value.find(e => e.id == selectedEoEventId.value) || null;
            });

            const loadEoEvents = async () => {
                if (!isAuthenticated) return;
                eoEventsLoading.value = true;
                try {
                    const data = await apiFetchJson(`${apiBase}/eo-events`);
                    if (data && data.events) {
                        eoEvents.value = data.events;
                        if (selectedEoEventId.value) {
                            const found = data.events.find(e => e.id == selectedEoEventId.value);
                            if (found && !eoEventSearchQuery.value) {
                                eoEventSearchQuery.value = `${found.name}${found.start_at ? ' (' + found.start_at + ')' : ''}`;
                            }
                        }
                    }
                } catch (e) {
                    console.error('Failed to load EO events:', e);
                } finally {
                    eoEventsLoading.value = false;
                }
            };

            const onEoEventChange = () => {
                selectedEoCategoryId.value = 'all';
                const ev = selectedEoEvent.value;
                if (ev) {
                    if (!raceName.value) raceName.value = ev.name;
                    if (ev.logo_url && !raceLogoPreviewUrl.value) {
                        raceLogoUrl.value = ev.logo_url;
                        raceLogoPreviewUrl.value = ev.logo_url;
                    }
                }
            };

            const importEoEventParticipants = async () => {
                if (!selectedEoEventId.value) return;
                importingEoParticipants.value = true;
                try {
                    const url = `${apiBase}/eo-events/${selectedEoEventId.value}/participants?category_id=${selectedEoCategoryId.value}`;
                    const data = await apiFetchJson(url);
                    if (data && Array.isArray(data.participants)) {
                        if (data.participants.length === 0) {
                            alert('Tidak ada peserta terdaftar/terkonfirmasi pada event ini.');
                            return;
                        }

                        if (data.event) {
                            if (!raceName.value) raceName.value = data.event.name;
                            if (data.event.logo_url && !raceLogoPreviewUrl.value) {
                                raceLogoUrl.value = data.event.logo_url;
                                raceLogoPreviewUrl.value = data.event.logo_url;
                            }
                        }

                        const ev = selectedEoEvent.value;
                        if (ev && selectedEoCategoryId.value !== 'all') {
                            const cat = ev.categories.find(c => c.id == selectedEoCategoryId.value);
                            if (cat) {
                                raceCategory.value = cat.name;
                                if (cat.distance_km) raceDistanceKm.value = cat.distance_km;
                            }
                        }

                        participants.value = data.participants.map(p => ({
                            id: p.id || crypto.randomUUID(),
                            bib: normalizeBib(p.bib) || String(p.bib ?? '').trim().toUpperCase(),
                            name: String(p.name ?? '').trim(),
                            photoUrl: p.photo_url || '',
                            faceDescriptor: null,
                            predictedTimeMs: typeof p.predictedTimeMs === 'number' ? p.predictedTimeMs : null,
                            laps: [],
                            status: 'ready',
                            totalTime: 0,
                            recentlyScanned: false,
                            lastScanTime: 0,
                        }));

                        // Asynchronously extract face descriptors from imported participant photos
                        participants.value.forEach(async (part) => {
                            if (part.photoUrl) {
                                try {
                                    const img = new Image();
                                    img.crossOrigin = 'anonymous';
                                    img.src = part.photoUrl;
                                    img.onload = async () => {
                                        const desc = await extractFaceDescriptorFromImage(img);
                                        if (desc) {
                                            part.faceDescriptor = desc;
                                            saveState();
                                        }
                                    };
                                } catch (e) {}
                            }
                        });

                        saveState();
                        alert(`Berhasil mengimpor ${data.participants.length} peserta dari event ${data.event?.name || ''}!`);
                    }
                } catch (e) {
                    console.error(e);
                    alert(e?.message || 'Gagal mengimpor peserta.');
                } finally {
                    importingEoParticipants.value = false;
                }
            };

            // Methods
            const loadExistingRaces = async () => {
                if (!isAuthenticated) return;
                try {
                    const data = await apiFetchJson(`${apiBase}/races`);
                    if (data && data.success) {
                        existingRaces.value = data.races;
                    }
                } catch (e) {
                    console.error('Failed to load races', e);
                }
            };

            const selectExistingRace = async (raceId) => {
                if (!isAuthenticated || !raceId) return;
                try {
                    const data = await apiFetchJson(`${apiBase}/races/${raceId}`);
                    if (data && data.success) {
                        const r = data.race;
                        currentRaceId.value = r.id;
                        raceName.value = r.name;
                        raceLogoUrl.value = r.logo_url || '';
                        raceLogoPreviewUrl.value = r.logo_url || '';
                        if (r.category) raceCategory.value = r.category;
                        if (r.distance_km) raceDistanceKm.value = r.distance_km;

                        // Restore session if exists
                        if (data.session) {
                            currentSessionId.value = data.session.id;
                            sessionSlug.value = data.session.slug || '';
                            
                            // Timer logic
                            timer.value.elapsed = data.session.timer_elapsed || 0;
                            if (data.session.is_running) {
                                timer.value.running = true;
                                timer.value.startTime = Date.now() - timer.value.elapsed;
                                // Restart interval if needed
                                if (timer.value.interval) clearInterval(timer.value.interval);
                                timer.value.interval = setInterval(() => {
                                    timer.value.elapsed = Date.now() - timer.value.startTime;
                                }, 50);
                            } else {
                                timer.value.running = false;
                                if (timer.value.interval) clearInterval(timer.value.interval);
                            }
                        } else {
                            // Reset session if race has no active session
                            currentSessionId.value = null;
                            sessionSlug.value = '';
                            timer.value.running = false;
                            timer.value.elapsed = 0;
                            if (timer.value.interval) clearInterval(timer.value.interval);
                        }

                        // Load participants
                        if (data.participants) {
                            participants.value = data.participants.map(p => {
                                const hasLaps = Array.isArray(p.laps) && p.laps.length > 0;
                                let status = 'ready';
                                if (hasLaps) {
                                    // If they have laps, they are running or finished.
                                    // If session is ended, they are finished.
                                    // If session is running, they are running.
                                    status = (data.session?.ended_at) ? 'finished' : 'running';
                                }

                                return {
                                    id: p.id || crypto.randomUUID(),
                                    bib: normalizeBib(p.bib) || String(p.bib ?? '').trim().toUpperCase(),
                                    name: String(p.name ?? ''),
                                    predictedTimeMs: typeof p.predictedTimeMs === 'number' ? p.predictedTimeMs : null,
                                    laps: hasLaps ? p.laps : [],
                                    status: status,
                                    totalTime: typeof p.totalTime === 'number' ? p.totalTime : 0,
                                    recentlyScanned: false,
                                    lastScanTime: 0,
                                };
                            });
                        }
                        saveState();
                        alert('Race berhasil dimuat!');
                    }
                } catch (e) {
                    alert('Gagal memuat race.');
                    console.error(e);
                }
            };

            const focusName = () => { inputName.value.focus(); };

            const onLogoChange = (e) => {
                const file = e?.target?.files?.[0] || null;
                raceLogoFile.value = file;
                raceLogoFileName.value = file ? file.name : '';
                if (raceLogoPreviewUrl.value && raceLogoPreviewUrl.value.startsWith('blob:')) {
                    try { URL.revokeObjectURL(raceLogoPreviewUrl.value); } catch (err) {}
                }
                raceLogoPreviewUrl.value = file ? URL.createObjectURL(file) : (raceLogoUrl.value || '');
                saveState();
            };

            const onPosterBgChange = (e) => {
                const file = e?.target?.files?.[0] || null;
                posterBackgroundFile.value = file;
            };

            const generatePoster = async () => {
                if (!currentSessionId.value) {
                    alert('Mulai race dulu agar session tersimpan.');
                    return;
                }

                try {
                    const form = new FormData();
                    if (posterBackgroundFile.value) form.append('background', posterBackgroundFile.value);

                    const blob = await apiFetchBlob(`${apiBase}/sessions/${encodeURIComponent(String(currentSessionId.value))}/poster`, {
                        method: 'POST',
                        body: form,
                    });

                    if (posterUrl.value) {
                        try { URL.revokeObjectURL(posterUrl.value); } catch (e) {}
                    }

                    posterFile.value = new File([blob], 'poster.png', { type: 'image/png' });
                    posterUrl.value = URL.createObjectURL(blob);
                } catch (e) {
                    alert(e?.message || 'Gagal generate poster.');
                }
            };

            const downloadPoster = () => {
                if (!posterUrl.value) return;
                const a = document.createElement('a');
                a.href = posterUrl.value;
                a.download = 'poster.png';
                document.body.appendChild(a);
                a.click();
                a.remove();
            };

            const sharePosterNative = async () => {
                if (!posterFile.value) return;
                if (!navigator.share || !navigator.canShare || !navigator.canShare({ files: [posterFile.value] })) {
                    alert('Share tidak didukung. Silakan download lalu upload manual.');
                    return;
                }
                try {
                    await navigator.share({
                        title: raceName.value || 'Race Results',
                        text: 'Hasil race',
                        files: [posterFile.value],
                    });
                } catch (e) {}
            };

            const shareTwitter = () => {
                const text = encodeURIComponent(`Hasil race ${raceName.value || raceCategory.value}`);
                const url = encodeURIComponent(window.location.href);
                window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
            };

            const shareFacebook = () => {
                const url = encodeURIComponent(window.location.href);
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
            };

            const ensureRaceInDb = async () => {
                const name = String(raceName.value || '').trim();
                if (name.length < 3 || name.length > 100) {
                    throw new Error('Nama race harus 3–100 karakter.');
                }

                const form = new FormData();
                form.append('name', name);
                if (raceLogoFile.value) form.append('logo', raceLogoFile.value);

                if (!currentRaceId.value) {
                    const data = await apiFetchJson(`${apiBase}/races`, { method: 'POST', body: form });
                    currentRaceId.value = data?.race?.id || null;
                    raceLogoUrl.value = data?.race?.logo_url || '';
                } else {
                    form.append('_method', 'PUT');
                    const data = await apiFetchJson(`${apiBase}/races/${encodeURIComponent(String(currentRaceId.value))}`, { method: 'POST', body: form });
                    raceLogoUrl.value = data?.race?.logo_url || '';
                }

                if (raceLogoUrl.value) {
                    raceLogoPreviewUrl.value = raceLogoUrl.value;
                }

                saveState();
                return currentRaceId.value;
            };

            const syncParticipantsToDb = async () => {
                if (!currentRaceId.value) return;
                if (!participants.value.length) return;
                try {
                    const payload = {
                        participants: participants.value.map(p => ({
                            bib_number: String(p.bib ?? '').trim(),
                            name: String(p.name ?? '').trim(),
                            predicted_time_ms: typeof p.predictedTimeMs === 'number' ? p.predictedTimeMs : null,
                        })),
                    };
                    await apiFetchJson(`${apiBase}/races/${encodeURIComponent(String(currentRaceId.value))}/participants/bulk`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                } catch (e) {
                    console.warn('Sync participants notice:', e?.message);
                }
            };

            const ensureSessionInDb = async (startTimerNow = false) => {
                if (!currentRaceId.value) return null;
                if (currentSessionId.value) return currentSessionId.value;
                const payload = {
                    start_timer_now: startTimerNow
                };
                const category = String(raceCategory.value || '').trim();
                if (category) payload.category = category;
                const dist = String(raceDistanceKm.value || '').trim();
                if (dist) payload.distance_km = dist;

                const data = await apiFetchJson(`${apiBase}/races/${encodeURIComponent(String(currentRaceId.value))}/sessions`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                currentSessionId.value = data?.session?.id || null;
                sessionSlug.value = data?.session?.slug || sessionSlug.value;
                publicResultsUrl.value = data?.session?.public_results_url || publicResultsUrl.value;
                saveState();
                return currentSessionId.value;
            };

            const initializingSession = ref(false);

            const initializeRaceSession = async (startTimerNow = false) => {
                if (initializingSession.value) return;
                initializingSession.value = true;
                try {
                    const nameTrim = String(raceName.value || '').trim();
                    if (!nameTrim) raceName.value = `Race ${raceCategory.value}`;
                    await ensureRaceInDb();
                    await syncParticipantsToDb();
                    await ensureSessionInDb(startTimerNow);
                    startLiveSyncPolling();
                    if (!startTimerNow) {
                        alert('Sesi Balap Berhasil Dibuat!\n\nLink Sesi dan URL TV Display sekarang sudah aktif dan siap dibagikan.\nTimer belum berjalan (00:00:00). Anda dapat menekan "Start Timer" kapan pun lomba dimulai.');
                    }
                } catch (e) {
                    console.error('Initialize session error:', e);
                    alert(e?.message || 'Gagal menyiapkan sesi balap.');
                } finally {
                    initializingSession.value = false;
                }
            };

            const parseTimeInputToMs = (raw) => {
                const s = String(raw ?? '').trim();
                if (!s) return null;
                const mmss = s.match(/^(\d{1,3}):(\d{1,2})(?:\.(\d{1,2}))?$/);
                if (mmss) {
                    const m = parseInt(mmss[1], 10);
                    const sec = parseInt(mmss[2], 10);
                    const cs = mmss[3] ? parseInt(mmss[3].padEnd(2, '0').slice(0, 2), 10) : 0;
                    if (Number.isNaN(m) || Number.isNaN(sec) || sec >= 60) return null;
                    return (m * 60 * 1000) + (sec * 1000) + (cs * 10);
                }
                const minutesOnly = s.match(/^\d{1,3}$/);
                if (minutesOnly) {
                    const m = parseInt(s, 10);
                    if (Number.isNaN(m)) return null;
                    return m * 60 * 1000;
                }
                return null;
            };

            // Face API Loader and Extraction Methods
            const loadFaceApiModels = async () => {
                if (faceModelsLoaded) return true;
                if (typeof faceapi === 'undefined') return false;
                faceModelLoading.value = true;
                try {
                    const MODEL_URL = 'https://raw.githubusercontent.com/vladmandic/face-api/master/model/';
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                    ]);
                    faceModelsLoaded = true;
                    faceModelLoading.value = false;
                    return true;
                } catch (e) {
                    console.error('Failed to load Face-API models:', e);
                    faceModelLoading.value = false;
                    return false;
                }
            };

            const extractFaceDescriptorFromImage = async (imgOrCanvas) => {
                const loaded = await loadFaceApiModels();
                if (!loaded) return null;
                try {
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 });
                    const detection = await faceapi.detectSingleFace(imgOrCanvas, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    if (detection && detection.descriptor) {
                        return Array.from(detection.descriptor);
                    }
                } catch (e) {
                    console.error('Face descriptor extraction error:', e);
                }
                return null;
            };

            const openFaceCaptureModal = async (step = 'front') => {
                faceEnrollStep.value = step;
                faceCaptureModalOpen.value = true;
                await loadFaceApiModels();
                nextTick(async () => {
                    try {
                        const video = document.getElementById('faceCaptureVideo');
                        if (!video) return;
                        faceCaptureStream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } }
                        });
                        video.srcObject = faceCaptureStream;
                    } catch (e) {
                        alert('Tidak dapat mengakses kamera: ' + (e?.message || e));
                        faceCaptureModalOpen.value = false;
                    }
                });
            };

            const closeFaceCaptureModal = () => {
                if (faceCaptureStream) {
                    faceCaptureStream.getTracks().forEach(t => t.stop());
                    faceCaptureStream = null;
                }
                faceCaptureModalOpen.value = false;
            };

            const snapFaceAngle = async () => {
                const video = document.getElementById('faceCaptureVideo');
                if (!video) return;
                
                const canvas = document.createElement('canvas');
                canvas.width = 320;
                canvas.height = 320;
                const ctx = canvas.getContext('2d');
                
                // Crop center square
                const minSide = Math.min(video.videoWidth, video.videoHeight);
                const sx = (video.videoWidth - minSide) / 2;
                const sy = (video.videoHeight - minSide) / 2;
                ctx.drawImage(video, sx, sy, minSide, minSide, 0, 0, 320, 320);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                const currentStep = faceEnrollStep.value;

                newFacePhotos.value[currentStep] = dataUrl;
                if (currentStep === 'front' || !newFacePhoto.value) {
                    newFacePhoto.value = dataUrl;
                }

                newFaceProcessing.value = true;
                const desc = await extractFaceDescriptorFromImage(canvas);
                newFaceProcessing.value = false;
                if (desc) {
                    newFaceDescriptors.value[currentStep] = desc;
                }

                // Advance to next angle step
                if (currentStep === 'front') {
                    faceEnrollStep.value = 'right';
                } else if (currentStep === 'right') {
                    faceEnrollStep.value = 'left';
                } else if (currentStep === 'left') {
                    closeFaceCaptureModal();
                }
            };

            const onFacePhotoUpload = async (e) => {
                const file = e.target?.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = async (evt) => {
                    const dataUrl = evt.target.result;
                    newFacePhoto.value = dataUrl;
                    newFacePhotos.value.front = dataUrl;
                    newFaceProcessing.value = true;
                    
                    const img = new Image();
                    img.onload = async () => {
                        const desc = await extractFaceDescriptorFromImage(img);
                        newFaceProcessing.value = false;
                        if (desc) {
                            newFaceDescriptors.value.front = desc;
                        }
                    };
                    img.src = dataUrl;
                };
                reader.readAsDataURL(file);
            };

            const clearNewFacePhoto = () => {
                newFacePhoto.value = '';
                newFacePhotos.value = { front: '', right: '', left: '' };
                newFaceDescriptors.value = { front: null, right: null, left: null };
            };

            const addParticipant = () => {
                if (!newBib.value || !newName.value) return;

                const bibValue = normalizeBib(newBib.value);
                if (!bibValue) {
                    alert('Format BIB tidak valid. Gunakan 4 digit (contoh 1001) atau F/M + 4 digit (contoh F-1001, F 1001, M-1001).');
                    return;
                }

                // Duplicate check uses canonical BIB, so F 1001 / F1001 / F-1001 are identical.
                if (participants.value.find(p => normalizeBib(p.bib) === bibValue)) {
                    alert('Nomor BIB sudah ada!');
                    return;
                }
                
                // Parse HH:MM:SS
                const h = parseInt(newPredictedHH.value) || 0;
                const m = parseInt(newPredictedMM.value) || 0;
                const s = parseInt(newPredictedSS.value) || 0;
                let predictedMs = null;
                if (h > 0 || m > 0 || s > 0) {
                    predictedMs = (h * 3600 * 1000) + (m * 60 * 1000) + (s * 1000);
                }

                const descriptorsList = [
                    newFaceDescriptors.value.front,
                    newFaceDescriptors.value.right,
                    newFaceDescriptors.value.left
                ].filter(Boolean);

                participants.value.push({
                    id: crypto.randomUUID(),
                    bib: bibValue,
                    name: newName.value,
                    photoUrl: newFacePhoto.value || '',
                    facePhotos: { ...newFacePhotos.value },
                    faceDescriptor: newFaceDescriptors.value.front || (descriptorsList[0] || null),
                    faceDescriptors: descriptorsList,
                    predictedTimeMs: predictedMs,
                    laps: [],
                    status: 'ready',
                    totalTime: 0,
                    recentlyScanned: false
                });
                
                // Sort by BIB
                participants.value.sort(compareBibs);
                
                newBib.value = '';
                newName.value = '';
                newPredictedHH.value = '';
                newPredictedMM.value = '';
                newPredictedSS.value = '';
                clearNewFacePhoto();
                saveState();
                if (currentRaceId.value) {
                    syncParticipantsToDb();
                }
                nextTick(() => inputBib.value.focus());
            };

            const downloadCsvSample = () => {
                const csvContent = "bib,nama,prediksi_waktu\n1001,Budi Santoso,00:25:30\nF-1002,Siti Rahma,00:28:15\nM-1003,Ahmad Fauzi,00:22:40\nF-1004,Dewi Lestari,00:31:10\n1005,Rian Hidayat,00:19:50\n";
                const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'sample_peserta_racemaster.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            };

            const onCsvUpload = (e) => {
                const file = e?.target?.files?.[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (event) => {
                    const text = event.target.result;
                    importCsvText(text);
                    if (e.target) e.target.value = '';
                };
                reader.onerror = () => {
                    alert('Gagal membaca file CSV.');
                };
                reader.readAsText(file);
            };

            const importCsvText = (text) => {
                if (!text || !text.trim()) {
                    alert('File CSV kosong.');
                    return;
                }

                const lines = text.split(/\r\n|\n|\r/).map(l => l.trim()).filter(Boolean);
                if (lines.length <= 1) {
                    alert('File CSV tidak memiliki baris data peserta.');
                    return;
                }

                const firstLine = lines[0];

                // Auto-detect delimiter (Tab \t, Semicolon ;, Comma ,, Pipe |)
                const tabCount = (firstLine.match(/\t/g) || []).length;
                const semiCount = (firstLine.match(/;/g) || []).length;
                const commaCount = (firstLine.match(/,/g) || []).length;
                const pipeCount = (firstLine.match(/\|/g) || []).length;

                let delimiter = ',';
                const maxDelimCount = Math.max(tabCount, semiCount, commaCount, pipeCount);
                if (maxDelimCount > 0) {
                    if (maxDelimCount === tabCount) delimiter = '\t';
                    else if (maxDelimCount === semiCount) delimiter = ';';
                    else if (maxDelimCount === pipeCount) delimiter = '|';
                    else delimiter = ',';
                }

                const splitRow = (line) => {
                    if (delimiter === '\t') {
                        return line.split('\t').map(c => c.trim().replace(/^["']|["']$/g, ''));
                    }
                    if (delimiter === '|') {
                        return line.split('|').map(c => c.trim().replace(/^["']|["']$/g, ''));
                    }
                    const regex = new RegExp('(?:^|' + delimiter + ')(?:"([^"]*(?:""[^"]*)*)"|([^"' + delimiter + ']*))', 'g');
                    const row = [];
                    let match;
                    while ((match = regex.exec(line)) !== null) {
                        let value = match[1] !== undefined ? match[1].replace(/""/g, '"') : match[2];
                        row.push((value || '').trim());
                    }
                    if (row.length === 0) {
                        return line.split(delimiter).map(c => c.trim().replace(/^["']|["']$/g, ''));
                    }
                    return row;
                };

                const rawHeaders = splitRow(firstLine).map(h => h.toLowerCase().replace(/["'\s_]/g, ''));
                
                let bibIdx = rawHeaders.findIndex(h => h.includes('bib') || h === 'no' || h === 'nomor' || h.includes('dada') || h === 'id');
                let nameIdx = rawHeaders.findIndex(h => h.includes('nama') || h.includes('name') || h.includes('peserta') || h.includes('runner') || h.includes('atlet'));
                let predIdx = rawHeaders.findIndex(h => h.includes('prediksi') || h.includes('waktu') || h.includes('target') || h.includes('time') || h.includes('pace') || h.includes('estimasi'));

                if (bibIdx === -1) bibIdx = 0;
                if (nameIdx === -1) nameIdx = 1;
                if (predIdx === -1 && rawHeaders.length > 2) predIdx = 2;

                let addedCount = 0;
                let skippedDuplicateCount = 0;
                let invalidCount = 0;

                const existingBibSet = new Set(participants.value.map(p => normalizeBib(p.bib)).filter(Boolean));
                const newEntries = [];

                for (let i = 1; i < lines.length; i++) {
                    const row = splitRow(lines[i]);
                    if (row.length < 2) continue;

                    const rawBib = row[bibIdx] ?? '';
                    const rawName = row[nameIdx] ?? '';
                    const rawPred = predIdx !== -1 ? (row[predIdx] ?? '') : '';

                    const bib = normalizeBib(rawBib);
                    const name = String(rawName).trim();

                    if (!bib || !name) {
                        invalidCount++;
                        continue;
                    }

                    if (existingBibSet.has(bib)) {
                        skippedDuplicateCount++;
                        continue;
                    }

                    let predictedMs = null;
                    if (rawPred) {
                        const timeParts = rawPred.split(':').map(p => parseInt(p, 10));
                        if (timeParts.length === 3 && !timeParts.some(isNaN)) {
                            predictedMs = (timeParts[0] * 3600 * 1000) + (timeParts[1] * 60 * 1000) + (timeParts[2] * 1000);
                        } else if (timeParts.length === 2 && !timeParts.some(isNaN)) {
                            predictedMs = (timeParts[0] * 60 * 1000) + (timeParts[1] * 1000);
                        }
                    }

                    existingBibSet.add(bib);
                    newEntries.push({
                        id: crypto.randomUUID(),
                        bib: bib,
                        name: name,
                        photoUrl: '',
                        faceDescriptor: null,
                        predictedTimeMs: predictedMs,
                        laps: [],
                        status: 'ready',
                        totalTime: 0,
                        recentlyScanned: false,
                        lastScanTime: 0
                    });
                    addedCount++;
                }

                if (newEntries.length > 0) {
                    participants.value.push(...newEntries);
                    participants.value.sort(compareBibs);
                    saveState();
                    if (currentRaceId.value) {
                        syncParticipantsToDb();
                    }
                }

                let summaryMsg = `Berhasil mengimpor ${addedCount} peserta dari file CSV.`;
                if (skippedDuplicateCount > 0) {
                    summaryMsg += ` (${skippedDuplicateCount} nomor BIB dilewati karena sudah ada).`;
                }
                if (invalidCount > 0) {
                    summaryMsg += ` (${invalidCount} baris tidak valid dilewati).`;
                }
                alert(summaryMsg);
            };

            const removeParticipant = (index) => {
                if(confirm('Hapus peserta ini?')) {
                    participants.value.splice(index, 1);
                    saveState();
                }
            };

            const goToBibs = () => {
                currentView.value = 'bibs';
                saveState();
            };

            const printBibs = () => {
                window.print();
            };

            const getInitials = (name) => {
                return name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            };

            // QR Code Generation
            const generateQRCodes = () => {
                participants.value.forEach(p => {
                    const el = document.getElementById('qrcode-' + p.bib);
                    if (el) {
                        el.innerHTML = '';
                        new QRCode(el, {
                            text: p.bib, // Simple payload: just the BIB number
                            width: 230,
                            height: 230,
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }
                });
            };

            // Watch for view changes to generate QR codes
            Vue.watch(currentView, (val) => {
                if (val === 'bibs') {
                    nextTick(generateQRCodes);
                }
            });

            Vue.watch(tvDisplayOpen, (val) => {
                if (val) {
                    nextTick(generateTvResultsQrCode);
                }
            });

            Vue.watch(raceCategory, () => {
                saveState();
            });

            // Timer Logic - Starts INSTANTLY with zero delay (0ms)
            const startRace = () => {
                if (!timer.value.running) {
                    const now = Date.now();
                    timer.value.startTime = now - timer.value.elapsed;
                    timer.value.running = true;
                    timer.value.paused = false;
                    if (timer.value.interval) clearInterval(timer.value.interval);
                    timer.value.interval = setInterval(() => {
                        timer.value.elapsed = Date.now() - timer.value.startTime;
                    }, 50); // 50ms update rate

                    if (queueFlushInterval.value) clearInterval(queueFlushInterval.value);
                    queueFlushInterval.value = setInterval(() => {
                        queueFlush();
                    }, 2000);

                    participants.value.forEach(p => {
                        if (p.status === 'ready' || !p.status) p.status = 'running';
                    });
                    saveState();

                    // Background async sync (does NOT block or delay timer)
                    (async () => {
                        try {
                            const nameTrim = String(raceName.value || '').trim();
                            if (!nameTrim) raceName.value = `Race ${raceCategory.value}`;
                            await ensureRaceInDb();
                            await syncParticipantsToDb();
                            await ensureSessionInDb(true);

                            // If session already existed, tell backend timer has started
                            const slug = sessionSlug.value || currentSessionId.value;
                            if (slug) {
                                try {
                                    await apiFetchJson(`${apiBase}/sessions/${encodeURIComponent(String(slug))}/start-timer`, { method: 'POST' });
                                } catch (e) {
                                    try {
                                        await apiFetchJson(`${apiBase}/public/${encodeURIComponent(String(slug))}/start-timer`, { method: 'POST' });
                                    } catch (_) {}
                                }
                            }
                            startLiveSyncPolling();
                        } catch (e) {
                            console.warn('Background sync on timer start:', e);
                        }
                    })();
                }
            };

            const copySessionShareUrl = () => {
                const slug = sessionSlug.value || currentSessionId.value;
                if (!slug) {
                    alert('Sesi belum dibuat / dimulai. Silakan mulai sesi lomba terlebih dahulu.');
                    return;
                }
                const url = `${window.location.origin}${window.location.pathname}?session=${encodeURIComponent(slug)}`;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link Sesi berhasil disalin!\nBuka link ini di Laptop TV (Admin 1), Kamera Gate (Admin 2), Spotter (Admin 3) untuk sinkronisasi otomatis.');
                    }).catch(() => {
                        prompt('Salin Link Sesi untuk Admin lain:', url);
                    });
                } else {
                    prompt('Salin Link Sesi untuk Admin lain:', url);
                }
            };

            const maxSyncedLapId = ref(0);

            const joinLiveSession = async (slugOrId, retryAttempt = 0) => {
                const target = String(slugOrId || sessionRoomInput.value || '').trim();
                if (!target) return;

                // Joined from room code or URL parameter -> satellite viewer station
                isSessionHost.value = false;

                sessionSyncError.value = '';
                try {
                    let endpoint = isAuthenticated 
                        ? `${apiBase}/sessions/${encodeURIComponent(target)}/live-sync`
                        : `${apiBase}/public/${encodeURIComponent(target)}/live-sync`;
                    let res = await fetch(endpoint, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok && isAuthenticated) {
                        endpoint = `${apiBase}/public/${encodeURIComponent(target)}/live-sync`;
                        res = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                    }

                    if (!res.ok) throw new Error('Sesi tidak ditemukan di server.');
                    const data = await res.json();
                    if (!data.success) throw new Error(data.message || 'Gagal sinkronisasi sesi.');
                    
                    if (typeof data.is_host === 'boolean') {
                        isSessionHost.value = data.is_host;
                    } else if (!isAuthenticated) {
                        isSessionHost.value = false;
                    }

                    // Track maximum synced lap ID for subsequent lightweight delta syncs
                    maxSyncedLapId.value = data.max_lap_id || 0;

                    // Apply Race & Session Info
                    currentSessionId.value = data.session.id;
                    sessionSlug.value = data.session.slug || sessionSlug.value;
                    currentRaceId.value = data.session.race_id;
                    if (data.race?.name) raceName.value = data.race.name;
                    if (data.session.category) raceCategory.value = data.session.category;
                    if (data.session.distance_km) raceDistanceKm.value = data.session.distance_km;
                    if (data.session.public_results_url) publicResultsUrl.value = data.session.public_results_url;

                    // Sync Participants from Server
                    if (Array.isArray(data.participants) && data.participants.length > 0) {
                        const existingMap = new Map(participants.value.map(p => [normalizeBib(p.bib) || String(p.bib).trim().toUpperCase(), p]));
                        const serverBibSet = new Set();
                        const merged = data.participants.map(sp => {
                            const serverBib = normalizeBib(sp.bib) || String(sp.bib).trim().toUpperCase();
                            serverBibSet.add(serverBib);
                            const local = existingMap.get(serverBib);
                            return {
                                id: local ? local.id : crypto.randomUUID(),
                                bib: serverBib,
                                name: sp.name || (local ? local.name : `Runner ${sp.bib}`),
                                photoUrl: local ? local.photoUrl : '',
                                faceDescriptor: local ? local.faceDescriptor : null,
                                predictedTimeMs: local ? local.predictedTimeMs : null,
                                laps: Array.isArray(sp.laps) ? sp.laps : (local ? local.laps : []),
                                status: sp.status || 'ready',
                                totalTime: sp.totalTime || 0,
                                recentlyScanned: false,
                                lastScanTime: 0
                            };
                        });
                        const localOnly = participants.value.filter(p => {
                            const b = normalizeBib(p.bib) || String(p.bib).trim().toUpperCase();
                            return !serverBibSet.has(b);
                        });
                        participants.value = [...merged, ...localOnly].sort(compareBibs);
                    }

                    // Sync Server Clock Time
                    if (data.session.is_running && data.session.started_at_ms) {
                        const now = Date.now();
                        timer.value.startTime = data.session.started_at_ms;
                        timer.value.elapsed = Math.max(0, now - data.session.started_at_ms);
                        timer.value.paused = false;
                        if (!timer.value.running) {
                            timer.value.running = true;
                            if (timer.value.interval) clearInterval(timer.value.interval);
                            timer.value.interval = setInterval(() => {
                                timer.value.elapsed = Date.now() - timer.value.startTime;
                            }, 50);
                        }
                    } else if (data.session.ended_at) {
                        timer.value.running = false;
                        timer.value.paused = true;
                        if (timer.value.interval) clearInterval(timer.value.interval);
                        timer.value.interval = null;
                        timer.value.elapsed = data.session.elapsed_ms || 0;
                    }

                    sessionSyncActive.value = true;
                    sessionSyncLastUpdated.value = new Date().toLocaleTimeString();

                    // Automatically switch view from empty setup to active race or results!
                    if (data.session.ended_at) {
                        currentView.value = 'results';
                    } else if (data.session.is_running || (Array.isArray(data.participants) && data.participants.length > 0)) {
                        if (currentView.value === 'setup') {
                            currentView.value = 'race';
                        }
                    }

                    startLiveSyncPolling();
                    saveState();
                } catch (e) {
                    sessionSyncError.value = e.message || 'Gagal terhubung ke sesi.';
                    console.error('Session sync error:', e);

                    // Automatic retry with exponential backoff on network delay (up to 4 retries)
                    if (retryAttempt < 4) {
                        const delay = (retryAttempt + 1) * 800;
                        setTimeout(() => {
                            joinLiveSession(slugOrId, retryAttempt + 1);
                        }, delay);
                    }
                }
            };

            const pollLiveSync = async () => {
                const target = sessionSlug.value || currentSessionId.value;
                if (!target || !sessionSyncActive.value) return;

                try {
                    // If local participants is empty, force full sync (since_id = 0) so participants are populated immediately!
                    const sinceParam = participants.value.length === 0 ? 0 : maxSyncedLapId.value;
                    let endpoint = isAuthenticated
                        ? `${apiBase}/sessions/${encodeURIComponent(String(target))}/live-sync?since_id=${sinceParam}`
                        : `${apiBase}/public/${encodeURIComponent(String(target))}/live-sync?since_id=${sinceParam}`;
                    let res = await fetch(endpoint, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok && isAuthenticated) {
                        endpoint = `${apiBase}/public/${encodeURIComponent(String(target))}/live-sync?since_id=${sinceParam}`;
                        res = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                    }

                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.success) {
                            sessionSyncLastUpdated.value = new Date().toLocaleTimeString();
                            if (typeof data.is_host === 'boolean') {
                                isSessionHost.value = data.is_host;
                            }
                            
                            // Detect if session was reset on host / server
                            if (data.is_reset || (!data.session.is_running && !data.session.started_at_ms && !data.session.ended_at)) {
                                const hasLocalProgress = timer.value.running || timer.value.elapsed > 0 || participants.value.some(p => p.laps && p.laps.length > 0) || maxSyncedLapId.value > 0;
                                if (hasLocalProgress) {
                                    if (timer.value.interval) clearInterval(timer.value.interval);
                                    timer.value.interval = null;
                                    timer.value.running = false;
                                    timer.value.paused = false;
                                    timer.value.elapsed = 0;
                                    timer.value.startTime = null;

                                    participants.value.forEach(p => {
                                        p.laps = [];
                                        p.status = 'ready';
                                        p.totalTime = 0;
                                        p.recentlyScanned = false;
                                        p.lastScanTime = 0;
                                    });
                                    maxSyncedLapId.value = 0;
                                    lastSeenLapIdSet.clear();
                                    if (currentView.value === 'results') {
                                        currentView.value = 'race';
                                    }
                                    saveState();
                                }
                            }

                            // If full sync returned (e.g. initial empty participant population)
                            if (!data.is_delta && Array.isArray(data.participants) && data.participants.length > 0) {
                                if (data.max_lap_id) maxSyncedLapId.value = data.max_lap_id;
                                const existingMap = new Map(participants.value.map(p => [normalizeBib(p.bib) || String(p.bib).trim().toUpperCase(), p]));
                                const serverBibSet = new Set();
                                const merged = data.participants.map(sp => {
                                    const serverBib = normalizeBib(sp.bib) || String(sp.bib).trim().toUpperCase();
                                    serverBibSet.add(serverBib);
                                    const local = existingMap.get(serverBib);
                                    return {
                                        id: local ? local.id : crypto.randomUUID(),
                                        bib: serverBib,
                                        name: sp.name || (local ? local.name : `Runner ${sp.bib}`),
                                        photoUrl: local ? local.photoUrl : '',
                                        faceDescriptor: local ? local.faceDescriptor : null,
                                        predictedTimeMs: local ? local.predictedTimeMs : null,
                                        laps: Array.isArray(sp.laps) ? sp.laps : (local ? local.laps : []),
                                        status: sp.status || 'ready',
                                        totalTime: sp.totalTime || 0,
                                        recentlyScanned: false,
                                        lastScanTime: 0
                                    };
                                });
                                const localOnly = participants.value.filter(p => {
                                    const b = normalizeBib(p.bib) || String(p.bib).trim().toUpperCase();
                                    return !serverBibSet.has(b);
                                });
                                participants.value = [...merged, ...localOnly].sort(compareBibs);
                            }

                            // Clock Sync (Only start if not running and not paused)
                            if (data.session.is_running && data.session.started_at_ms) {
                                if (!timer.value.running && !timer.value.paused) {
                                    timer.value.startTime = data.session.started_at_ms;
                                    timer.value.running = true;
                                    if (timer.value.interval) clearInterval(timer.value.interval);
                                    timer.value.interval = setInterval(() => {
                                        timer.value.elapsed = Date.now() - timer.value.startTime;
                                    }, 50);
                                }
                            } else if (data.session.ended_at && timer.value.running) {
                                timer.value.running = false;
                                timer.value.paused = true;
                                if (timer.value.interval) clearInterval(timer.value.interval);
                                timer.value.interval = null;
                                timer.value.elapsed = data.session.elapsed_ms;
                            }

                            // Process Delta New Laps
                            if (data.is_delta && Array.isArray(data.new_laps)) {
                                if (data.max_lap_id) {
                                    maxSyncedLapId.value = Math.max(maxSyncedLapId.value, data.max_lap_id);
                                }

                                data.new_laps.forEach(lap => {
                                    if (!lastSeenLapIdSet.has(lap.id)) {
                                        lastSeenLapIdSet.add(lap.id);
                                        const p = participants.value.find(item => String(item.bib).trim() === String(lap.bib).trim());
                                        if (p) {
                                            p.status = 'finished';
                                            p.totalTime = lap.total_time_ms;
                                            if (!p.laps.some(l => l.lap === lap.lap_number)) {
                                                p.laps.push({
                                                    lap: lap.lap_number,
                                                    time: lap.lap_time_ms,
                                                    totalTime: lap.total_time_ms
                                                });
                                            }
                                            triggerTvFinisherFlash(p);
                                        }
                                    }
                                });
                            }
                        }
                    }
                } catch (e) {
                    console.warn('Background sync poll error:', e);
                }
            };

            const startLiveSyncPolling = () => {
                sessionSyncActive.value = true;
                if (sessionSyncTimer) clearInterval(sessionSyncTimer);
                sessionSyncTimer = setInterval(pollLiveSync, 1500);
            };

            const pauseRace = () => {
                if (timer.value.running || !timer.value.paused) {
                    if (timer.value.interval) clearInterval(timer.value.interval);
                    timer.value.interval = null;
                    timer.value.running = false;
                    timer.value.paused = true;
                    if (queueFlushInterval.value) {
                        clearInterval(queueFlushInterval.value);
                        queueFlushInterval.value = null;
                    }
                    saveState();
                }
            };

            const finishRace = () => {
                if (!isSessionHost.value) {
                    alert('Hanya Host pembuat sesi yang berhak menyelesaikan sesi balapan.');
                    return;
                }
                if (!confirm('Selesaikan sesi balapan ini? Aksi ini akan menyimpan hasil akhir dan tidak dapat dilanjutkan.')) return;
                
                pauseRace(); // Stop timer first

                queueFlush();

                if (currentSessionId.value) {
                    apiFetchJson(`${apiBase}/sessions/${encodeURIComponent(String(currentSessionId.value))}/finish`, { method: 'POST' })
                        .then((data) => {
                            publicResultsUrl.value = data?.session?.public_results_url || publicResultsUrl.value;
                            sessionSlug.value = data?.session?.slug || sessionSlug.value;
                            if (data && Array.isArray(data.certificates)) {
                                const map = {};
                                data.certificates.forEach((c) => {
                                    if (c && c.bib_number && c.download_url) map[String(c.bib_number)] = c.download_url;
                                });
                                certificatesByBib.value = map;
                            }

                            participants.value.forEach(p => {
                                if (p.status === 'dnf') return;
                                if (Array.isArray(p.laps) && p.laps.length > 0) p.status = 'finished';
                                else p.status = 'dnf';
                            });
                            // Move to results
                            currentView.value = 'results';
                            saveState();
                        })
                        .catch((e) => alert('Gagal finish session: ' + (e.message || 'Unknown error')));
                } else {
                    alert('Session belum tersimpan. Tekan Start lagi lalu coba Finish.');
                }
            };

            const resetRace = async () => {
                if (!isSessionHost.value) {
                    alert('Hanya Host pembuat sesi yang berhak mereset perlombaan.');
                    return;
                }
                if (!confirm('Reset timer dan semua hasil untuk sesi ini? Sesi di semua perangkat lain juga akan otomatis kereset.')) return;

                const target = sessionSlug.value || currentSessionId.value;
                if (target) {
                    try {
                        await apiFetchJson(`${apiBase}/sessions/${encodeURIComponent(String(target))}/reset`, { method: 'POST' });
                    } catch (e) {
                        try {
                            await apiFetchJson(`${apiBase}/public/${encodeURIComponent(String(target))}/reset`, { method: 'POST' });
                        } catch (_) {}
                    }
                }

                if (timer.value.interval) clearInterval(timer.value.interval);
                timer.value.interval = null;
                timer.value.running = false;
                timer.value.paused = false;
                timer.value.elapsed = 0;
                timer.value.startTime = null;

                if (queueFlushInterval.value) {
                    clearInterval(queueFlushInterval.value);
                    queueFlushInterval.value = null;
                }

                maxSyncedLapId.value = 0;
                lastSeenLapIdSet.clear();
                participants.value.forEach(p => {
                    p.laps = [];
                    p.status = 'ready';
                    p.totalTime = 0;
                    p.recentlyScanned = false;
                    p.lastScanTime = 0;
                });
                if (currentView.value === 'results') {
                    currentView.value = 'race';
                }
                saveState();
            };

            const formattedTime = computed(() => {
                return formatTime(timer.value.elapsed);
            });

            const tvTimerParts = computed(() => {
                const raw = formattedTime.value || '00:00:00.00';
                const dotIdx = raw.lastIndexOf('.');
                if (dotIdx === -1) {
                    return { main: raw, ms: '.00' };
                }
                return {
                    main: raw.substring(0, dotIdx),
                    ms: raw.substring(dotIdx)
                };
            });

            const formatTime = (ms) => {
                if (!ms || ms < 0 || isNaN(ms)) return '00:00:00.00';
                const totalSeconds = Math.floor(ms / 1000);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                const centiseconds = Math.floor((ms % 1000) / 10);

                const hStr = hours.toString().padStart(2, '0');
                const mStr = minutes.toString().padStart(2, '0');
                const sStr = seconds.toString().padStart(2, '0');
                const csStr = centiseconds.toString().padStart(2, '0');

                return `${hStr}:${mStr}:${sStr}.${csStr}`;
            };

            const formatPace = (p) => {
                const dist = parseFloat(String(raceDistanceKm.value || ''));
                if (!Number.isFinite(dist) || dist <= 0) return '-';
                if (!p || typeof p.totalTime !== 'number' || p.totalTime <= 0) return '-';
                const totalSec = Math.max(1, Math.floor(p.totalTime / 1000));
                const paceSec = Math.round(totalSec / dist);
                const m = Math.floor(paceSec / 60);
                const s = paceSec % 60;
                return `${m}:${String(s).padStart(2, '0')}/km`;
            };

            // Race Logic -------------------------------------------------------------
            // Race Logic -------------------------------------------------------------
            // Canonical BIB format:
            //   101                   -> 101
            //   1001                  -> 1001
            //   F1001 / F 1001 / F-1001 -> F-1001
            //   M1003 / M 1003 / M-1003 -> M-1003
            // Supports 1 to 6 digits with optional prefix (e.g. F-1001, M-101, HM-501, 1001, 101)
            const canonicalizeBibToken = (value) => {
                let s = String(value ?? '')
                    .toUpperCase()
                    .replace(/[–—−_]/g, '-')
                    .replace(/^#/, '')
                    .trim();
                if (!s) return '';

                // Remove separators between prefix and digits (e.g. F-1001 or HM 501)
                const prefixed = s.match(/^([A-Z]{1,3})\s*-?\s*(\d{1,6})$/i);
                if (prefixed) return `${prefixed[1].toUpperCase()}-${prefixed[2]}`;

                const plain = s.match(/^(\d{1,6})$/);
                if (plain) return plain[1];

                if (/^[A-Z0-9-]{1,10}$/.test(s)) return s;
                return '';
            };

            const normalizeBib = (raw) => {
                let s = String(raw ?? '').trim();
                if (!s) return '';

                // QR can contain JSON such as {"bib":"F-1001"}.
                try {
                    const obj = JSON.parse(s);
                    if (obj && (obj.bib !== undefined || obj.BIB !== undefined || obj.number !== undefined)) {
                        const v = obj.bib ?? obj.BIB ?? obj.number;
                        return normalizeBib(v);
                    }
                } catch (e) {}

                // QR can also contain a URL with ?bib=F-1001 or /F-1001.
                try {
                    if (/^https?:\/\//i.test(s)) {
                        const u = new URL(s);
                        const qp = u.searchParams.get('bib') || u.searchParams.get('BIB');
                        if (qp) return normalizeBib(qp);
                        const last = u.pathname.split('/').filter(Boolean).pop();
                        if (last) {
                            const fromPath = normalizeBib(decodeURIComponent(last));
                            if (fromPath) return fromPath;
                        }
                    }
                } catch (e) {}

                const direct = canonicalizeBibToken(s);
                if (direct) return direct;

                // Tolerate surrounding text from QR payload
                const normalizedText = s.toUpperCase().replace(/[–—−_]/g, '-');
                const prefixed = normalizedText.match(/(?:^|[^A-Z0-9])([A-Z]{1,3})\s*-?\s*(\d{1,6})(?=$|[^0-9])/i);
                if (prefixed) return `${prefixed[1].toUpperCase()}-${prefixed[2]}`;

                const plain = normalizedText.match(/(?:^|[^0-9])(\d{1,6})(?=$|[^0-9])/);
                if (plain) return plain[1];
                return '';
            };

            const compareBibs = (a, b) => {
                const aa = normalizeBib(a?.bib ?? a) || String(a?.bib ?? a ?? '');
                const bb = normalizeBib(b?.bib ?? b) || String(b?.bib ?? b ?? '');
                const an = parseInt((aa.match(/(\d+)$/) || [0, '999999'])[1], 10);
                const bn = parseInt((bb.match(/(\d+)$/) || [0, '999999'])[1], 10);
                if (an !== bn) return an - bn;
                return aa.localeCompare(bb);
            };

            // Resolve a normalized BIB against participant data.
            const resolveBibCandidate = (raw) => {
                const bib = normalizeBib(raw);
                if (!bib) return { bib: '', participant: null, ambiguous: false, candidates: [] };

                const exact = participants.value.find(p => normalizeBib(p.bib) === bib);
                if (exact) {
                    return { bib: normalizeBib(exact.bib) || exact.bib, participant: exact, ambiguous: false, candidates: [exact] };
                }

                const digits = bib.replace(/\D/g, '');
                if (digits) {
                    const suffixMatches = participants.value.filter(p => {
                        const pb = normalizeBib(p.bib);
                        const pbDigits = pb.replace(/\D/g, '');
                        return pbDigits === digits;
                    });
                    if (suffixMatches.length === 1) {
                        const participant = suffixMatches[0];
                        return { bib: normalizeBib(participant.bib) || participant.bib, participant, ambiguous: false, candidates: suffixMatches };
                    }
                    if (suffixMatches.length > 1) {
                        return { bib, participant: null, ambiguous: true, candidates: suffixMatches };
                    }
                }

                return { bib, participant: null, ambiguous: false, candidates: [] };
            };

            const findParticipantByBib = (raw) => resolveBibCandidate(raw).participant;

            // Parse common OCR confusions in digits.
            const ocrCharToDigit = (ch) => {
                const c = String(ch || '').toUpperCase();
                if (/\d/.test(c)) return c;
                if (['O', 'Q', 'D'].includes(c)) return '0';
                if (['I', 'L', '|'].includes(c)) return '1';
                if (c === 'Z') return '2';
                if (c === 'S') return '5';
                if (c === 'G') return '6';
                if (c === 'B') return '8';
                return '';
            };

            const normalizeOcrPrefix = (value) => String(value ?? '')
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '');

            const getBibRuntimeProfile = () => {
                const entries = [];

                participants.value.forEach((participant, index) => {
                    const rawBib = String(participant?.bib ?? '')
                        .toUpperCase()
                        .replace(/[–—−_]/g, '-')
                        .trim();

                    if (!rawBib) return;

                    // Derive structure from the ACTUAL participant data:
                    // trailing digits = race number, everything before it = optional prefix/separator.
                    const match = rawBib.match(/^(.*?)(\d+)$/);
                    if (!match) return;

                    const digits = match[2];
                    const left = match[1] || '';
                    const sepMatch = left.match(/([-\s]+)$/);
                    const separatorRaw = sepMatch ? sepMatch[1] : '';
                    const prefixRaw = sepMatch ? left.slice(0, -separatorRaw.length) : left;
                    const prefix = normalizeOcrPrefix(prefixRaw);

                    let separator = '';
                    if (separatorRaw.includes('-')) separator = '-';
                    else if (/\s/.test(separatorRaw)) separator = ' ';

                    entries.push({
                        participant,
                        index,
                        rawBib,
                        prefix,
                        digits,
                        digitLength: digits.length,
                        separator,
                        key: `${prefix}${digits}`,
                    });
                });

                const digitLengths = [...new Set(entries.map(e => e.digitLength))]
                    .filter(n => n >= 1 && n <= 8)
                    .sort((a, b) => a - b);

                const prefixes = [...new Set(entries.map(e => e.prefix).filter(Boolean))]
                    .sort((a, b) => a.length - b.length || a.localeCompare(b));

                const separators = [...new Set(entries
                    .filter(e => e.prefix)
                    .map(e => e.separator || 'attached'))];

                const signature = entries
                    .map(e => `${e.prefix}:${e.digits}:${e.separator}`)
                    .join('|');

                return {
                    entries,
                    digitLengths,
                    prefixes,
                    separators,
                    signature,
                    maxPrefixLength: Math.max(1, ...prefixes.map(p => p.length)),
                };
            };

            const levenshteinDistance = (a, b) => {
                const s = String(a ?? '');
                const t = String(b ?? '');
                if (s === t) return 0;
                if (!s.length) return t.length;
                if (!t.length) return s.length;

                let prev = new Array(t.length + 1);
                let curr = new Array(t.length + 1);
                for (let j = 0; j <= t.length; j++) prev[j] = j;

                for (let i = 1; i <= s.length; i++) {
                    curr[0] = i;
                    for (let j = 1; j <= t.length; j++) {
                        const cost = s[i - 1] === t[j - 1] ? 0 : 1;
                        curr[j] = Math.min(
                            curr[j - 1] + 1,
                            prev[j] + 1,
                            prev[j - 1] + cost
                        );
                    }
                    [prev, curr] = [curr, prev];
                }
                return prev[t.length];
            };

            const translateOcrDigits = (value) => {
                const chars = [...String(value ?? '').toUpperCase()];
                const out = chars.map(ocrCharToDigit).join('');
                return out;
            };

            const extractRuntimeOcrCandidates = (rawText, profile = getBibRuntimeProfile()) => {
                let text = String(rawText ?? '')
                    .toUpperCase()
                    .replace(/[–—−_]/g, '-')
                    .replace(/[\r\n\t]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();

                if (!text || !profile.entries.length || !profile.digitLengths.length) return [];

                // OCR confusable characters allowed in numeric positions.
                const digitChars = '0-9OQDIL|ZSGB';
                const results = [];
                const seen = new Set();

                const addCandidate = (prefixRaw, digitsRaw, source, separatorRaw = '') => {
                    const digits = translateOcrDigits(digitsRaw);
                    if (!digits || digits.length !== String(digitsRaw).length) return;
                    if (!profile.digitLengths.includes(digits.length)) return;

                    const prefix = normalizeOcrPrefix(prefixRaw);
                    let separator = '';
                    if (String(separatorRaw).includes('-')) separator = '-';
                    else if (/\s/.test(String(separatorRaw))) separator = ' ';
                    const key = `${prefix}|${digits}|${separator}|${source}`;
                    if (seen.has(key)) return;
                    seen.add(key);

                    results.push({
                        prefix,
                        digits,
                        separator,
                        source,
                        raw: prefix ? `${prefixRaw}-${digitsRaw}` : String(digitsRaw),
                    });
                };

                for (const len of profile.digitLengths) {
                    // Prefix + number. Prefix expression is intentionally permissive;
                    // the fuzzy matcher below decides which ACTUAL participant is closest.
                    if (profile.prefixes.length) {
                        const maxPf = Math.min(8, Math.max(1, profile.maxPrefixLength + 1));
                        const prefixedRe = new RegExp(
                            `(?:^|[^A-Z0-9])([A-Z0-9]{1,${maxPf}})([\\s-]*)([${digitChars}]{${len}})(?=$|[^A-Z0-9])`,
                            'g'
                        );
                        let m;
                        while ((m = prefixedRe.exec(text)) !== null) {
                            addCandidate(m[1], m[3], 'prefixed', m[2]);
                            if (m.index === prefixedRe.lastIndex) prefixedRe.lastIndex++;
                        }
                    }

                    // Plain numeric BIB. This remains valid even when the database also has prefixes:
                    // fuzzy ranking will choose the closest participant.
                    const plainRe = new RegExp(
                        `(?:^|[^A-Z0-9])([${digitChars}]{${len}})(?=$|[^A-Z0-9])`,
                        'g'
                    );
                    let p;
                    while ((p = plainRe.exec(text)) !== null) {
                        addCandidate('', p[1], 'plain');
                        if (p.index === plainRe.lastIndex) plainRe.lastIndex++;
                    }
                }

                return results;
            };

            const scoreOcrCandidateAgainstEntry = (candidate, entry) => {
                const digitDistance = levenshteinDistance(candidate.digits, entry.digits);
                const prefixDistance = candidate.prefix || entry.prefix
                    ? levenshteinDistance(candidate.prefix, entry.prefix)
                    : 0;

                let score = 100;
                score -= digitDistance * 26;
                score -= Math.abs(candidate.digits.length - entry.digits.length) * 18;
                score -= prefixDistance * 9;

                if (candidate.digits === entry.digits) score += 35;
                if (candidate.prefix && candidate.prefix === entry.prefix) score += 20;
                if (!candidate.prefix && !entry.prefix) score += 12;
                if (!candidate.prefix && entry.prefix) score -= 4;
                if (candidate.prefix && !entry.prefix) score -= 10;

                if (candidate.prefix && entry.prefix) {
                    const candSep = candidate.separator || 'attached';
                    const entrySep = entry.separator || 'attached';
                    if (candSep === entrySep) score += 4;
                }

                // "Search-like" bonus: compact OCR key occurs inside the participant key or vice versa.
                const candidateKey = `${candidate.prefix}${candidate.digits}`;
                const entryKey = `${entry.prefix}${entry.digits}`;
                if (candidateKey && entryKey &&
                    (candidateKey.includes(entryKey) || entryKey.includes(candidateKey))) {
                    score += 12;
                }

                return score;
            };

            // OCR-specific "search like" matching.
            // No exact-match requirement and no multi-frame voting: top-1 wins immediately.
            const findBestOcrParticipantMatch = (rawText) => {
                const profile = getBibRuntimeProfile();
                if (!profile.entries.length) return null;

                const candidates = extractRuntimeOcrCandidates(rawText, profile);
                if (!candidates.length) return null;

                let best = null;

                for (const candidate of candidates) {
                    for (const entry of profile.entries) {
                        // Comparing same digit-length first keeps the search fast and avoids absurd matches.
                        if (candidate.digits.length !== entry.digitLength) continue;

                        const score = scoreOcrCandidateAgainstEntry(candidate, entry);
                        if (!best || score > best.score ||
                            (score === best.score && entry.index < best.entry.index)) {
                            best = { candidate, entry, score };
                        }
                    }
                }

                if (!best) return null;

                return {
                    participant: best.entry.participant,
                    bib: String(best.entry.participant.bib ?? best.entry.rawBib),
                    score: best.score,
                    candidateBib: best.candidate.prefix
                        ? `${best.candidate.prefix}-${best.candidate.digits}`
                        : best.candidate.digits,
                    candidate: best.candidate,
                    profile,
                };
            };

            // Kept as a compact compatibility wrapper for any existing OCR call sites.
            const parseOcrBibText = (rawText) => {
                const best = findBestOcrParticipantMatch(rawText);
                return best?.bib || '';
            };

            const playBeep = (() => {
                let ctx = null;
                return () => {
                    try {
                        if (!ctx) {
                            const AudioCtx = window.AudioContext || window.webkitAudioContext;
                            if (!AudioCtx) return;
                            ctx = new AudioCtx();
                        }
                        if (ctx.state === 'suspended') ctx.resume();
                        const o = ctx.createOscillator();
                        const g = ctx.createGain();
                        o.type = 'sine';
                        o.frequency.value = 880;
                        g.gain.setValueAtTime(0.0001, ctx.currentTime);
                        g.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + 0.01);
                        g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.08);
                        o.connect(g);
                        g.connect(ctx.destination);
                        o.start();
                        o.stop(ctx.currentTime + 0.09);
                    } catch (e) {}
                };
            })();

            const playBuzzer = (() => {
                let ctx = null;
                return () => {
                    try {
                        if (!ctx) {
                            const AudioCtx = window.AudioContext || window.webkitAudioContext;
                            if (!AudioCtx) return;
                            ctx = new AudioCtx();
                        }
                        if (ctx.state === 'suspended') ctx.resume();
                        const o = ctx.createOscillator();
                        const g = ctx.createGain();
                        o.type = 'sawtooth';
                        o.frequency.value = 220;
                        g.gain.setValueAtTime(0.0001, ctx.currentTime);
                        g.gain.exponentialRampToValueAtTime(0.3, ctx.currentTime + 0.02);
                        g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.25);
                        o.connect(g);
                        g.connect(ctx.destination);
                        o.start();
                        o.stop(ctx.currentTime + 0.26);
                    } catch (e) {}
                };
            })();

            const showDuplicateAlert = (type, message, participant = null) => {
                manualValidationAlert.value = {
                    show: true,
                    type: type,
                    message: message,
                    bib: participant ? participant.bib : '',
                    name: participant ? participant.name : '',
                    time: participant && participant.totalTime ? formatTime(participant.totalTime) : ''
                };
                if (type === 'warning' || type === 'error') {
                    playBuzzer();
                } else if (type === 'success') {
                    playBeep();
                }
                setTimeout(() => {
                    if (manualValidationAlert.value.message === message) {
                        manualValidationAlert.value.show = false;
                    }
                }, 6000);
            };

            const dismissAlert = () => {
                manualValidationAlert.value.show = false;
            };

            // Auto-detected unique prefixes from registered participants (e.g. M1001 -> 'M', F1001 -> 'F', 10K-001 -> '10K')
            const detectedBibPrefixes = computed(() => {
                const prefixSet = new Set();
                participants.value.forEach(p => {
                    const bib = String(p.bib ?? '').trim();
                    if (!bib) return;

                    // Match leading letters/categories before numbers
                    const matchCategory = bib.match(/^(\d+[kK][-_]?)/);
                    if (matchCategory && matchCategory[1]) {
                        const clean = matchCategory[1].replace(/[-_]$/, '').toUpperCase();
                        prefixSet.add(clean);
                    } else {
                        const matchLetter = bib.match(/^([A-Za-z]+[-_]?)/);
                        if (matchLetter && matchLetter[1]) {
                            const clean = matchLetter[1].replace(/[-_]$/, '').toUpperCase();
                            if (clean.length >= 1 && clean.length <= 6) {
                                prefixSet.add(clean);
                            }
                        }
                    }
                });

                return Array.from(prefixSet).sort();
            });

            // Auto-extracted BIB Pattern profile based on existing registered participants
            const activeBibPattern = computed(() => {
                const profile = getBibRuntimeProfile();
                const count = profile.entries.length;
                const minBib = count > 0 ? profile.entries[0].rawBib : '-';
                const maxBib = count > 0 ? profile.entries[count - 1].rawBib : '-';

                return {
                    totalBibs: count,
                    digitLengths: profile.digitLengths,
                    digitLengthsText: profile.digitLengths.length > 0
                        ? profile.digitLengths.map(l => `${l} Digit`).join(', ')
                        : '0 Digit',
                    prefixes: profile.prefixes,
                    prefixesText: profile.prefixes.length > 0
                        ? profile.prefixes.join(', ')
                        : 'Tanpa Prefix (Angka Murni)',
                    sampleRange: count > 0 ? `#${minBib} — #${maxBib}` : 'Belum ada peserta terdaftar',
                    isReady: count > 0,
                };
            });

            // Smart BIB Resolution Engine (supports prefix matching, casing, separators, and suffix fallback)

            const resolveParticipantByBib = (rawQuery, activePrefix = '') => {
                const cleanRaw = String(rawQuery || '').trim();
                if (!cleanRaw) return null;
                const cleanPrefix = String(activePrefix || '').trim().toUpperCase().replace(/[-_]$/, '');

                // 1. Exact match on raw query directly (e.g. typed 'F-1001', 'F1001', or '1001')
                let foundExact = participants.value.find(p => String(p.bib).trim().toUpperCase() === cleanRaw.toUpperCase());
                if (foundExact) return foundExact;

                // 2. Normalized match (ignoring hyphens, underscores, spaces)
                const rawNorm = cleanRaw.toUpperCase().replace(/[-_\s]/g, '');
                let foundNorm = participants.value.find(p => String(p.bib).trim().toUpperCase().replace(/[-_\s]/g, '') === rawNorm);
                if (foundNorm) return foundNorm;

                // 3. If prefix is active
                if (cleanPrefix) {
                    const targetNorm = (cleanPrefix + rawNorm).toUpperCase();
                    let foundPrefixed = participants.value.find(p => {
                        const bNorm = String(p.bib).trim().toUpperCase().replace(/[-_\s]/g, '');
                        return bNorm === targetNorm;
                    });
                    if (foundPrefixed) return foundPrefixed;
                }

                // 4. Flexible candidate matching (starts with, includes digits, or prefix)
                const rawDigits = cleanRaw.replace(/\D/g, '');
                const candidates = participants.value.filter(p => {
                    const bibUpper = String(p.bib).trim().toUpperCase();
                    if (cleanPrefix && !bibUpper.replace(/[-_\s]/g, '').startsWith(cleanPrefix)) return false;
                    const bDigits = bibUpper.replace(/\D/g, '');
                    if (rawDigits && (bDigits === rawDigits || bDigits.startsWith(rawDigits) || bDigits.endsWith(rawDigits))) return true;
                    return bibUpper.startsWith(cleanRaw.toUpperCase()) || bibUpper.includes(cleanRaw.toUpperCase());
                });

                if (candidates.length === 1) return candidates[0];

                // If multiple candidates, check for exact digit match
                if (rawDigits) {
                    const exactDigit = candidates.find(p => String(p.bib).trim().replace(/\D/g, '') === rawDigits);
                    if (exactDigit) return exactDigit;
                }

                return null;
            };

            const quickBibSuggestions = computed(() => {
                const raw = String(manualBibInput.value || '').trim();
                if (!raw) return [];
                const cleanPrefix = String(lockedBibPrefix.value || '').trim().toUpperCase();
                const queryUpper = raw.toUpperCase();
                const digitsOnly = raw.replace(/\D/g, '');

                return participants.value.filter(p => {
                    const bUpper = String(p.bib).trim().toUpperCase();
                    const nUpper = String(p.name).toUpperCase();
                    const bDigits = bUpper.replace(/\D/g, '');

                    if (cleanPrefix && !bUpper.replace(/[-_\s]/g, '').startsWith(cleanPrefix)) {
                        return false;
                    }

                    return bUpper === queryUpper ||
                           bUpper.startsWith(queryUpper) ||
                           (digitsOnly && bDigits.startsWith(digitsOnly)) ||
                           (digitsOnly && bDigits.includes(digitsOnly)) ||
                           bUpper.includes(queryUpper) ||
                           nUpper.includes(queryUpper);
                }).slice(0, 6);
            });

            const selectSuggestion = (p) => {
                if (!p) return;
                manualBibInput.value = p.bib;
                recordManualBib();
            };

            const liveMatchedRunner = computed(() => {
                const raw = String(manualBibInput.value || '').trim();
                if (!raw || raw.includes(' ') || raw.includes(',')) return null;

                return resolveParticipantByBib(raw, lockedBibPrefix.value);
            });

            const ambiguousRunners = computed(() => {
                const raw = String(manualBibInput.value || '').trim();
                if (!raw || lockedBibPrefix.value || raw.includes(' ') || raw.includes(',')) return [];
                if (raw.length < 2) return [];

                return participants.value.filter(p => {
                    const b = String(p.bib).trim().toUpperCase();
                    return b === raw.toUpperCase() || b.endsWith(raw.toUpperCase());
                });
            });

            const onManualBibKeyup = (e) => {
                if (e.key === 'Enter') return;
                const raw = String(manualBibInput.value || '').trim();
                if (!raw || raw.includes(' ') || raw.includes(',')) return;

                if (autoSubmitDigits.value > 0) {
                    const digits = raw.replace(/\D/g, '');
                    if (digits.length === autoSubmitDigits.value) {
                        recordManualBib();
                    }
                }
            };

            const recordManualBib = () => {
                const raw = String(manualBibInput.value || '').trim();
                if (!raw) return;

                if (!timer.value.running && timer.value.elapsed === 0) {
                    showDuplicateAlert('error', 'Timer belum berjalan! Silakan mulai timer race terlebih dahulu.');
                    return;
                }

                // Support batch rombongan separated by space or comma (e.g. "1001 1002 1003" or "F-1001 M-1001")
                const tokens = raw.split(/[\s,]+/).map(t => t.trim()).filter(Boolean);
                if (!tokens.length) return;

                tokens.forEach(token => {
                    const p = resolveParticipantByBib(token, lockedBibPrefix.value);

                    if (!p) {
                        // Check prefix handling
                        let targetBib = token.toUpperCase();
                        if (lockedBibPrefix.value && !targetBib.startsWith(lockedBibPrefix.value.toUpperCase())) {
                            targetBib = `${lockedBibPrefix.value.toUpperCase()}-${targetBib.replace(/^[-_\s]+/, '')}`;
                        } else {
                            targetBib = canonicalizeBibToken(targetBib) || targetBib;
                        }

                        const digits = targetBib.replace(/\D/g, '');

                        // Check digit rule if autoSubmitDigits is configured
                        if (autoSubmitDigits.value > 0 && digits.length !== autoSubmitDigits.value) {
                            showDuplicateAlert('error', `Nomor BIB #${targetBib} harus ${autoSubmitDigits.value} digit (sesuai aturan auto-submit)!`);
                            return;
                        }

                        // Lock timing immediately at this exact moment
                        const capturedAt = Date.now();
                        const elapsedMs = timer.value.running && timer.value.startTime
                            ? Math.max(0, capturedAt - timer.value.startTime)
                            : Math.max(0, timer.value.elapsed || 0);
                        const timing = { capturedAt, elapsedMs };

                        // Trigger Mobile Bottom Sheet for Quick Add confirmation
                        pendingUnregisteredBib.value = {
                            show: true,
                            bib: targetBib,
                            rawDigits: digits,
                            prefix: lockedBibPrefix.value || '',
                            timing: timing,
                            formattedTime: formatTime(elapsedMs)
                        };
                        return;
                    }

                    // Anti-Duplicate Check
                    if (p.status === 'finished') {
                        showDuplicateAlert('warning', `DUPLIKAT DITOLAK: BIB #${p.bib} (${p.name}) SUDAH FINISH sebelumnya pada ${formatTime(p.totalTime)}!`, p);
                        return;
                    }

                    if (p.status === 'dnf') {
                        showDuplicateAlert('error', `BIB #${p.bib} (${p.name}) berstatus DNF (Did Not Finish)!`, p);
                        return;
                    }

                    recordLap(p.id, 'manual_input');

                    if (p.status === 'finished') {
                        triggerTvFinisherFlash(p);
                        showDuplicateAlert('success', `FINISH TERCATAT: BIB #${p.bib} (${p.name}) • Waktu: ${formatTime(p.totalTime)}`, p);
                    } else {
                        showDuplicateAlert('info', `LAP ${p.laps.length}/${raceSettings.value.targetLaps} TERCATAT: BIB #${p.bib} (${p.name}) • ${formatTime(p.totalTime)}`, p);
                    }
                });

                manualBibInput.value = '';
                setTimeout(() => {
                    const el = document.getElementById('manualBibInputEl');
                    if (el) el.focus();
                }, 30);
            };

            const confirmAddUnregisteredParticipant = () => {
                if (!pendingUnregisteredBib.value.show || !pendingUnregisteredBib.value.bib) return;
                const targetBib = pendingUnregisteredBib.value.bib;
                const timing = pendingUnregisteredBib.value.timing;

                // 1. Create new participant
                const newParticipant = {
                    id: Date.now(),
                    bib: targetBib,
                    name: `Runner #${targetBib}`,
                    category: raceCategory.value || 'General',
                    predictedTime: '00:00:00',
                    predictedTimeMs: 0,
                    laps: [],
                    status: 'running',
                    totalTime: 0
                };

                participants.value.push(newParticipant);

                // 2. Record lap with the locked timestamp immediately
                recordLap(newParticipant.id, 'manual_input', timing);

                if (newParticipant.status === 'finished') {
                    triggerTvFinisherFlash(newParticipant);
                    showDuplicateAlert('success', `PESERTA BARU DITAMBAHKAN & FINISH: BIB #${newParticipant.bib} • Waktu: ${formatTime(newParticipant.totalTime)}`, newParticipant);
                } else {
                    showDuplicateAlert('info', `PESERTA BARU DITAMBAHKAN: BIB #${newParticipant.bib} (${newParticipant.name})`, newParticipant);
                }

                // 3. Reset and close modal
                pendingUnregisteredBib.value.show = false;
                pendingUnregisteredBib.value.bib = '';

                setTimeout(() => {
                    const el = document.getElementById('manualBibInputEl');
                    if (el) el.focus();
                }, 50);
            };

            const cancelAddUnregisteredParticipant = () => {
                pendingUnregisteredBib.value.show = false;
                pendingUnregisteredBib.value.bib = '';
                setTimeout(() => {
                    const el = document.getElementById('manualBibInputEl');
                    if (el) el.focus();
                }, 50);
            };

            const recordLap = (id, source = 'manual', timing = null) => {
                const p = participants.value.find(p => p.id === id);
                if (!p) return false;

                if (!timer.value.running && timer.value.elapsed === 0) {
                    showDuplicateAlert('error', 'Timer belum berjalan! Silakan mulai timer race terlebih dahulu.');
                    return false;
                }

                if (p.status === 'finished') {
                    showDuplicateAlert('warning', `DUPLIKAT: BIB #${p.bib} (${p.name}) SUDAH FINISH sebelumnya pada ${formatTime(p.totalTime)}!`, p);
                    return false;
                }

                if (p.status === 'dnf') {
                    showDuplicateAlert('error', `BIB #${p.bib} (${p.name}) tercatat DNF!`, p);
                    return false;
                }

                // IMPORTANT: use the actual crossing timestamp, not the time after OCR/Face AI completes.
                const now = Number.isFinite(timing?.capturedAt) ? timing.capturedAt : Date.now();
                const elapsedMs = Number.isFinite(timing?.elapsedMs)
                    ? Math.max(0, timing.elapsedMs)
                    : Math.max(0, timer.value.elapsed || 0);

                // Debounce / Cooldown to prevent double scan
                const cooldownMs = (raceSettings.value.raceMode === 'multi_lap' ? (raceSettings.value.minLapCooldownSec || 15) : 3) * 1000;
                if (p.lastScanTime && (now - p.lastScanTime < cooldownMs)) {
                    if (raceSettings.value.raceMode === 'multi_lap') {
                        const rem = Math.ceil((cooldownMs - (now - p.lastScanTime)) / 1000);
                        showDuplicateAlert('warning', `BIB #${p.bib} dalam masa cooldown lap (${rem}s tersisa)!`, p);
                    }
                    console.log('Debounced/Cooldown scan for ' + p.bib);
                    return false;
                }

                playBeep();

                if (!Array.isArray(p.laps)) p.laps = [];
                p.laps.push(now);
                p.lastScanTime = now;
                p.lastScanSource = source;
                p.totalTime = elapsedMs;

                if (raceSettings.value.raceMode === 'single' || p.laps.length >= (raceSettings.value.targetLaps || 1)) {
                    p.status = 'finished';
                } else {
                    p.status = 'running';
                }

                if (currentSessionId.value) {
                    queueEnqueueLap(String(p.bib ?? '').trim(), Math.max(0, Math.floor(p.totalTime || 0)), new Date(now).toISOString());
                    queueFlush();
                }

                p.recentlyScanned = true;
                setTimeout(() => p.recentlyScanned = false, 2000);

                if (source === 'scanner' || source === 'ai_vision' || source === 'ocr_global' || source === 'manual_input' || source === 'manual_assign') {
                    camera.value.lastScanMsg = `Tercatat: #${p.bib} (${p.name}) • ${formatTime(p.totalTime)}`;
                }
                saveState();
                return true;
            };

            const getLastLapTime = (p) => {
                if (!p) return '-';
                if (typeof p.totalTime === 'number' && p.totalTime > 0) {
                    return formatTime(p.totalTime);
                }
                if (Array.isArray(p.laps) && p.laps.length > 0) {
                    const lastLap = p.laps[p.laps.length - 1];
                    if (typeof lastLap === 'object' && lastLap !== null && typeof lastLap.totalTime === 'number') {
                        return formatTime(lastLap.totalTime);
                    }
                    if (typeof lastLap === 'number') {
                        if (timer.value.startTime && lastLap >= timer.value.startTime) {
                            return formatTime(lastLap - timer.value.startTime);
                        }
                    }
                }
                return '-';
            };

            const markDNF = (id) => {
                if (confirm('Tandai peserta ini sebagai DNF (Did Not Finish)?')) {
                    const p = participants.value.find(p => p.id === id);
                    if (p) p.status = 'dnf';
                    saveState();
                }
            };

            const getDeltaMs = (p) => {
                if (!p || typeof p.predictedTimeMs !== 'number' || !Number.isFinite(p.predictedTimeMs)) return null;
                if (typeof p.totalTime !== 'number' || !Number.isFinite(p.totalTime)) return null;
                return p.totalTime - p.predictedTimeMs;
            };

            const formatDelta = (p) => {
                const delta = getDeltaMs(p);
                if (delta === null) return '-';
                const sign = delta < 0 ? '-' : '+';
                return sign + formatTime(Math.abs(delta));
            };

            const getPointsInfo = (p) => {
                if (raceCategory.value !== '5K') return null;
                if (!p || typeof p.totalTime !== 'number' || p.totalTime <= 0) return null;

                const minutes = p.totalTime / 60000;
                let multiplier = 1;
                let difficulty = 'Beginner';

                if (minutes < 20) {
                    multiplier = 4;
                    difficulty = 'Hard';
                } else if (minutes < 25) {
                    multiplier = 2;
                    difficulty = 'Medium';
                } else if (minutes < 30) {
                    multiplier = 1.2;
                    difficulty = 'Normal';
                } else {
                    multiplier = 1;
                    difficulty = 'Beginner';
                }

                return {
                    multiplier,
                    difficulty,
                    label: `x ${multiplier} • ${difficulty}`
                };
            };

            // Camera Devices Enumeration (Supports Osmo Pocket, Phone via DroidCam/Camo, USB Cams)
            const refreshCameraDevices = async () => {
                try {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(d => d.kind === 'videoinput');
                    camera.value.devices = videoDevices.map(d => ({
                        deviceId: d.deviceId,
                        label: d.label || `Kamera ${d.deviceId.substring(0, 5)}`
                    }));
                    if (videoDevices.length > 0 && !camera.value.selectedDeviceId) {
                        camera.value.selectedDeviceId = videoDevices[0].deviceId;
                    }
                } catch (e) {
                    console.error('Error querying media devices:', e);
                }
            };

            // AI Model Loader (COCO-SSD with MobileNet & TensorFlow ready check)
            const loadAiModel = async () => {
                if (cocoModel) {
                    camera.value.aiModelReady = true;
                    return cocoModel;
                }
                if (typeof cocoSsd === 'undefined') return null;
                camera.value.aiModelLoading = true;
                try {
                    if (typeof tf !== 'undefined') {
                        if (typeof tf.setBackend === 'function') {
                            await tf.setBackend('webgl').catch(async () => {
                                await tf.setBackend('cpu').catch(() => {});
                            });
                        }
                        if (typeof tf.ready === 'function') {
                            await tf.ready();
                        }
                    }
                    cocoModel = await cocoSsd.load({ base: 'mobilenet_v2' });
                    camera.value.aiModelReady = true;
                    camera.value.aiModelLoading = false;
                    return cocoModel;
                } catch (e) {
                    console.warn('First attempt to load COCO-SSD failed, trying fallback:', e);
                    try {
                        if (typeof tf !== 'undefined') {
                            if (typeof tf.setBackend === 'function') await tf.setBackend('cpu').catch(() => {});
                            if (typeof tf.ready === 'function') await tf.ready();
                        }
                        cocoModel = await cocoSsd.load();
                        camera.value.aiModelReady = true;
                        camera.value.aiModelLoading = false;
                        return cocoModel;
                    } catch (err2) {
                        console.error('Failed to load COCO-SSD:', err2);
                        camera.value.aiModelLoading = false;
                        return null;
                    }
                }
            };

            // Canvas Mouse & Touch Dragging for Finish Line (A & B Points)
            const getCanvasCoords = (e, canvas) => {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.clientX ?? e.touches?.[0]?.clientX ?? 0;
                const clientY = e.clientY ?? e.touches?.[0]?.clientY ?? 0;
                return {
                    x: Math.max(0, Math.min(1, (clientX - rect.left) / rect.width)),
                    y: Math.max(0, Math.min(1, (clientY - rect.top) / rect.height)),
                    pxX: clientX - rect.left,
                    pxY: clientY - rect.top,
                    width: rect.width,
                    height: rect.height
                };
            };

            const handleCanvasMouseDown = (e) => {
                const canvas = document.getElementById('aiCanvas');
                if (!canvas) return;
                const c = getCanvasCoords(e, canvas);
                const p1Px = { x: camera.value.line.x1 * c.width, y: camera.value.line.y1 * c.height };
                const p2Px = { x: camera.value.line.x2 * c.width, y: camera.value.line.y2 * c.height };

                const d1 = Math.hypot(c.pxX - p1Px.x, c.pxY - p1Px.y);
                const d2 = Math.hypot(c.pxX - p2Px.x, c.pxY - p2Px.y);

                if (d1 < 35) {
                    camera.value.draggingPoint = 'p1';
                } else if (d2 < 35) {
                    camera.value.draggingPoint = 'p2';
                }
            };

            const handleCanvasMouseMove = (e) => {
                if (!camera.value.draggingPoint) return;
                const canvas = document.getElementById('aiCanvas');
                if (!canvas) return;
                const c = getCanvasCoords(e, canvas);
                if (camera.value.draggingPoint === 'p1') {
                    camera.value.line.x1 = c.x;
                    camera.value.line.y1 = c.y;
                } else if (camera.value.draggingPoint === 'p2') {
                    camera.value.line.x2 = c.x;
                    camera.value.line.y2 = c.y;
                }
                saveState();
            };

            const handleCanvasMouseUp = () => {
                camera.value.draggingPoint = null;
                saveState();
            };

            const handleCanvasTouchStart = (e) => handleCanvasMouseDown(e);
            const handleCanvasTouchMove = (e) => handleCanvasMouseMove(e);
            const handleCanvasTouchEnd = () => handleCanvasMouseUp();

            const setLinePreset = (preset) => {
                if (preset === 'vertical') {
                    camera.value.line.x1 = 0.5;
                    camera.value.line.y1 = 0.05;
                    camera.value.line.x2 = 0.5;
                    camera.value.line.y2 = 0.95;
                } else if (preset === 'horizontal') {
                    camera.value.line.x1 = 0.05;
                    camera.value.line.y1 = 0.5;
                    camera.value.line.x2 = 0.95;
                    camera.value.line.y2 = 0.5;
                }
                saveState();
            };

            // Geometry & Line Crossing Intersection Algorithm
            const checkIntersection = (p1, p2, p3, p4) => {
                const ccw = (A, B, C) => (C.y - A.y) * (B.x - A.x) > (B.y - A.y) * (C.x - A.x);
                return (ccw(p1, p3, p4) !== ccw(p2, p3, p4)) && (ccw(p1, p2, p3) !== ccw(p1, p2, p4));
            };

            const checkDirectionValid = (prev, curr, line) => {
                const dir = line.direction;
                if (dir === 'any') return true;
                const dx = curr.x - prev.x;
                const dy = curr.y - prev.y;
                if (dir === 'left_to_right') return dx > 0.003;
                if (dir === 'right_to_left') return dx < -0.003;
                if (dir === 'top_to_bottom') return dy > 0.003;
                if (dir === 'bottom_to_top') return dy < -0.003;
                return true;
            };

            // Strong OCR Scanner for Race BIB ---------------------------------------
            const buildRuntimeOcrWhitelist = (profile = getBibRuntimeProfile()) => {
                const prefixChars = [...new Set(profile.prefixes.join('').split(''))].join('');
                // Keep common OCR-confusion characters available because the fuzzy parser
                // intentionally converts O/I/L/Q/etc. in numeric positions.
                return `0123456789${prefixChars}OQDILZSGB-| `;
            };

            const ensureOcrWorker = async () => {
                if (ocrWorker) return ocrWorker;
                if (ocrWorkerInitPromise) return ocrWorkerInitPromise;

                ocrWorkerInitPromise = (async () => {
                    const worker = await Tesseract.createWorker('eng');
                    const profile = getBibRuntimeProfile();
                    await worker.setParameters({
                        tessedit_char_whitelist: buildRuntimeOcrWhitelist(profile),
                        tessedit_pageseg_mode: '11', // Sparse text: scan BIB anywhere in the full frame
                        preserve_interword_spaces: '1',
                        user_defined_dpi: '220',
                    });
                    ocrWorkerProfileSignature = profile.signature;
                    ocrWorker = worker;
                    return worker;
                })();

                try {
                    return await ocrWorkerInitPromise;
                } finally {
                    ocrWorkerInitPromise = null;
                }
            };

            const syncOcrWorkerToParticipantData = async (worker) => {
                const profile = getBibRuntimeProfile();
                if (profile.signature !== ocrWorkerProfileSignature) {
                    await worker.setParameters({
                        tessedit_char_whitelist: buildRuntimeOcrWhitelist(profile),
                        tessedit_pageseg_mode: '11',
                        preserve_interword_spaces: '1',
                        user_defined_dpi: '220',
                    });
                    ocrWorkerProfileSignature = profile.signature;
                }
                return profile;
            };

            // Global Otsu Threshold Calculation (legacy helper for non-global OCR modes)
            const computeOtsuThreshold = (grayArray, totalPixels) => {
                const hist = new Int32Array(256);
                for (let i = 0; i < totalPixels; i++) {
                    hist[grayArray[i]]++;
                }

                let sum = 0;
                for (let i = 0; i < 256; i++) sum += i * hist[i];

                let sumB = 0;
                let wB = 0;
                let wF = 0;
                let maxVar = 0;
                let threshold = 128;

                for (let t = 0; t < 256; t++) {
                    wB += hist[t];
                    if (wB === 0) continue;
                    wF = totalPixels - wB;
                    if (wF === 0) break;

                    sumB += t * hist[t];
                    const mB = sumB / wB;
                    const mF = (sum - sumB) / wF;
                    const varBetween = wB * wF * (mB - mF) * (mB - mF);

                    if (varBetween > maxVar) {
                        maxVar = varBetween;
                        threshold = t;
                    }
                }
                return threshold;
            };

            // Capture torso / full body / full frame area, upscale, and apply solid Otsu thresholding.
            const prepareBibOcrCanvas = (video, bbox, variant = 'otsu') => {
                if (!video?.videoWidth || !video?.videoHeight) return null;

                let cropX = 0, cropY = 0, cropW = video.videoWidth, cropH = video.videoHeight;
                const scope = raceSettings.value.ocrScope || 'torso';

                if (scope === 'full_frame' || !Array.isArray(bbox)) {
                    cropX = 0;
                    cropY = 0;
                    cropW = video.videoWidth;
                    cropH = video.videoHeight;
                } else if (scope === 'full_body') {
                    const [bx, by, bw, bh] = bbox;
                    cropX = Math.max(0, bx - bw * 0.1);
                    cropY = Math.max(0, by - bh * 0.05);
                    cropW = Math.max(1, Math.min(video.videoWidth - cropX, bw * 1.2));
                    cropH = Math.max(1, Math.min(video.videoHeight - cropY, bh * 1.1));
                } else {
                    // Torso / Chest area with generous coverage (middle of runner body)
                    const [bx, by, bw, bh] = bbox;
                    cropX = Math.max(0, bx - bw * 0.05);
                    cropY = Math.max(0, by + bh * 0.10);
                    cropW = Math.max(1, Math.min(video.videoWidth - cropX, bw * 1.10));
                    cropH = Math.max(1, Math.min(video.videoHeight - cropY, bh * 0.65));
                }

                const targetW = 720;
                const targetH = 480;
                const canvas = document.createElement('canvas');
                canvas.width = targetW;
                canvas.height = targetH;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                // Fill white background padding so numbers don't clip at edges
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, targetW, targetH);

                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(video, cropX, cropY, cropW, cropH, 20, 20, targetW - 40, targetH - 40);

                if (variant === 'raw') return canvas;

                const imgData = ctx.getImageData(0, 0, targetW, targetH);
                const data = imgData.data;
                const numPixels = targetW * targetH;
                const gray = new Uint8Array(numPixels);

                for (let i = 0, px = 0; i < data.length; i += 4, px++) {
                    gray[px] = Math.round(data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
                }

                if (variant === 'gray') {
                    // Contrast-stretched grayscale
                    let minG = 255, maxG = 0;
                    for (let px = 0; px < numPixels; px++) {
                        if (gray[px] < minG) minG = gray[px];
                        if (gray[px] > maxG) maxG = gray[px];
                    }
                    const range = Math.max(1, maxG - minG);
                    for (let px = 0, i = 0; px < numPixels; px++, i += 4) {
                        const normalized = Math.round(((gray[px] - minG) / range) * 255);
                        data[i] = data[i + 1] = data[i + 2] = normalized;
                        data[i + 3] = 255;
                    }
                } else {
                    // Global Otsu thresholding: preserves thick black solid strokes without hollowing!
                    const threshold = computeOtsuThreshold(gray, numPixels);
                    for (let px = 0, i = 0; px < numPixels; px++, i += 4) {
                        const val = gray[px] < threshold ? 0 : 255;
                        data[i] = data[i + 1] = data[i + 2] = val;
                        data[i + 3] = 255;
                    }
                }

                ctx.putImageData(imgData, 0, 0);
                return canvas;
            };

            const prepareGlobalOcrCanvas = (video) => {
                if (!video?.videoWidth || !video?.videoHeight) return null;

                const scale = Math.min(1, OCR_GLOBAL_MAX_WIDTH / video.videoWidth);
                const targetW = Math.max(320, Math.round(video.videoWidth * scale));
                const targetH = Math.max(180, Math.round(video.videoHeight * scale));

                const canvas = document.createElement('canvas');
                canvas.width = targetW;
                canvas.height = targetH;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'medium';
                ctx.drawImage(video, 0, 0, video.videoWidth, video.videoHeight, 0, 0, targetW, targetH);

                // One fast contrast-stretched grayscale pass. No dual-pass OCR and no per-person crop.
                const imgData = ctx.getImageData(0, 0, targetW, targetH);
                const data = imgData.data;
                let minG = 255;
                let maxG = 0;

                for (let i = 0; i < data.length; i += 4) {
                    const g = Math.round(data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
                    if (g < minG) minG = g;
                    if (g > maxG) maxG = g;
                    data[i] = g; // temporarily store gray in R channel
                }

                const range = Math.max(24, maxG - minG);
                for (let i = 0; i < data.length; i += 4) {
                    const g = data[i];
                    let v = Math.round(((g - minG) / range) * 255);
                    // Mild contrast boost, intentionally cheaper than adaptive thresholding.
                    v = Math.max(0, Math.min(255, Math.round((v - 128) * 1.18 + 128)));
                    data[i] = data[i + 1] = data[i + 2] = v;
                    data[i + 3] = 255;
                }

                ctx.putImageData(imgData, 0, 0);
                return canvas;
            };

            // Serialize access to the single Tesseract worker. Crop is captured before entering this queue,
            // so queued OCR still processes the correct runner/frame.
            const runOcrQueued = async (task, force = false) => {
                if (!force && ocrQueueDepth >= 2) return null;
                ocrQueueDepth++;
                const run = ocrQueue.then(async () => {
                    ocrBusy = true;
                    try {
                        return await task();
                    } finally {
                        ocrBusy = false;
                    }
                });
                ocrQueue = run.catch(() => null);
                try {
                    return await run;
                } finally {
                    ocrQueueDepth = Math.max(0, ocrQueueDepth - 1);
                }
            };

            const captureGlobalOcrSnapshot = (video) => {
                try {
                    if (!video?.videoWidth || !video?.videoHeight) return '';
                    const canvas = document.createElement('canvas');
                    const w = 320;
                    const h = Math.max(180, Math.round(w * video.videoHeight / video.videoWidth));
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, video.videoWidth, video.videoHeight, 0, 0, w, h);
                    return canvas.toDataURL('image/jpeg', 0.72);
                } catch (e) {
                    return '';
                }
            };

            const scanFullFrameOcr = async (video) => {
                if (!raceSettings.value.enableOcr) return null;
                if (typeof Tesseract === 'undefined') return null;
                if (!video?.videoWidth || !video?.videoHeight) return null;

                // Lock the time of the FRAME before Tesseract starts.
                const capturedAt = Date.now();
                const elapsedMs = timer.value.running && timer.value.startTime
                    ? Math.max(0, capturedAt - timer.value.startTime)
                    : Math.max(0, timer.value.elapsed || 0);

                const frameCanvas = prepareGlobalOcrCanvas(video);
                if (!frameCanvas) return null;

                return runOcrQueued(async () => {
                    try {
                        const worker = await ensureOcrWorker();
                        await syncOcrWorkerToParticipantData(worker);

                        // ONE OCR pass only. Speed is preferred over multi-pass accuracy.
                        const res = await worker.recognize(frameCanvas);
                        const rawText = String(res?.data?.text || '').trim();
                        if (!rawText) return null;

                        const best = findBestOcrParticipantMatch(rawText);
                        if (!best?.participant) return null;

                        return {
                            ...best,
                            rawText,
                            confidence: Number(res?.data?.confidence || 0),
                            capturedAt,
                            elapsedMs,
                        };
                    } catch (e) {
                        console.warn('Global OCR BIB error:', e);
                        return null;
                    }
                }, false);
            };

            const processGlobalOcrResult = (video, result) => {
                const participant = result?.participant;
                if (!participant) return false;

                const capturedAt = Number.isFinite(result.capturedAt) ? result.capturedAt : Date.now();
                const elapsedMs = Number.isFinite(result.elapsedMs)
                    ? result.elapsedMs
                    : Math.max(0, timer.value.elapsed || 0);

                // Silent anti-duplicate guard because the global OCR may see the same BIB
                // on several consecutive frames.
                if (raceSettings.value.raceMode === 'single' && participant.status === 'finished') {
                    return false;
                }

                if (raceSettings.value.raceMode === 'multi_lap') {
                    const cooldownMs = (raceSettings.value.minLapCooldownSec || 15) * 1000;
                    if (participant.lastScanTime && capturedAt - participant.lastScanTime < cooldownMs) {
                        return false;
                    }
                }

                const timingLock = { capturedAt, elapsedMs };
                const recorded = recordLap(participant.id, 'ocr_global', timingLock);
                if (!recorded) return false;

                const scoreText = Number.isFinite(result.score) ? ` • match ${Math.round(result.score)}` : '';
                const confidenceText = Number.isFinite(result.confidence)
                    ? ` • OCR ${Math.round(result.confidence)}%`
                    : '';

                if (participant.status === 'finished') {
                    triggerTvFinisherFlash(participant);
                    camera.value.lastScanMsg =
                        `FINISH [OCR FAST]: BIB #${participant.bib} (${participant.name}) • ${formatTime(participant.totalTime)}${confidenceText}${scoreText}`;
                    showDuplicateAlert(
                        'success',
                        `FINISH [OCR FAST]: BIB #${participant.bib} (${participant.name}) • ${formatTime(participant.totalTime)}`,
                        participant
                    );
                } else {
                    camera.value.lastScanMsg =
                        `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} [OCR FAST]: BIB #${participant.bib} • ${formatTime(participant.totalTime)}${confidenceText}${scoreText}`;
                    showDuplicateAlert(
                        'info',
                        `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} [OCR FAST]: BIB #${participant.bib}`,
                        participant
                    );
                }

                liveFinishFeed.value.unshift({
                    id: crypto.randomUUID(),
                    capturedAt,
                    timestampMs: elapsedMs,
                    timeFormatted: formatTime(elapsedMs),
                    bib: participant.bib,
                    name: participant.name,
                    snapshot: captureGlobalOcrSnapshot(video),
                    participantId: participant.id,
                    matchMethod: 'ocr_global',
                    ocrConfidence: Number(result.confidence || 0),
                    ocrMatchScore: Number(result.score || 0),
                    ocrRawText: result.rawText || '',
                    ocrCandidate: result.candidateBib || '',
                    ambiguous: false,
                });

                if (liveFinishFeed.value.length > 30) liveFinishFeed.value.pop();
                return true;
            };

            const runGlobalOcrTick = async () => {
                if (globalOcrInFlight) return;
                if (!camera.value.active || !raceSettings.value.enableOcr) return;
                if (!timer.value.running) return;
                if (!participants.value.length) return;

                const video = getReaderVideo();
                if (!video || video.readyState < 2 || !video.videoWidth) return;

                globalOcrInFlight = true;
                try {
                    const result = await scanFullFrameOcr(video);
                    if (!camera.value.active || !timer.value.running) return;
                    if (result?.participant) {
                        // Requirement: first valid pattern -> top-1 fuzzy match -> process immediately.
                        processGlobalOcrResult(video, result);
                    }
                } catch (e) {
                    console.warn('Global OCR tick failed:', e);
                } finally {
                    globalOcrInFlight = false;
                }
            };

            const startGlobalOcrScanner = () => {
                stopGlobalOcrScanner();
                globalOcrTimer = setInterval(() => {
                    // Do not queue overlapping Tesseract work. If one scan is still running,
                    // this interval is simply skipped.
                    runGlobalOcrTick();
                }, OCR_GLOBAL_INTERVAL_MS);

                // First scan quickly after camera startup.
                setTimeout(() => runGlobalOcrTick(), 120);
            };

            const stopGlobalOcrScanner = () => {
                if (globalOcrTimer) {
                    clearInterval(globalOcrTimer);
                    globalOcrTimer = null;
                }
                globalOcrInFlight = false;
            };

            // =========================================================================
            // BIB Number Scan Only — Core Logic (Tahap 2: Two-Stage RoI Pipeline)
            // =========================================================================

            /**
             * Tahap 2: Two-Stage RoI Accelerated Micro-OCR Canvas Preparation
             * Stage 1: Extracts only the Region of Interest (RoI) matching the scan zone (~480x240px).
             * Stage 2: Applies focused contrast normalization / Otsu thresholding on the micro-canvas.
             * Result: 85%+ pixel reduction, latency drops from ~450ms to ~80ms.
             */
            const prepareDistanceAwareOcrCanvas = (video, distance = 'auto') => {
                if (!video?.videoWidth || !video?.videoHeight) return null;

                const vw = video.videoWidth;
                const vh = video.videoHeight;

                // Stage 1: RoI Crop Geometry (with safety padding)
                const wRatio = distance === 'close' ? 0.55 : distance === 'far' ? 0.80 : 0.65;
                const hRatio = distance === 'close' ? 0.35 : distance === 'far' ? 0.50 : 0.40;
                const cropW  = Math.max(160, Math.min(vw, Math.round(vw * wRatio)));
                const cropH  = Math.max(90, Math.min(vh, Math.round(vh * hRatio)));
                const cropX  = Math.max(0, Math.round((vw - cropW) / 2));
                const cropY  = Math.max(0, Math.round((vh - cropH) / 2));

                // Micro-Canvas Target Dimensions (440 - 640 px max width)
                const maxW    = BIB_SCAN_MAX_WIDTHS[distance] || 520;
                const targetW = Math.max(320, Math.min(maxW, cropW));
                const targetH = Math.max(160, Math.round(targetW * (cropH / cropW)));

                const canvas = document.createElement('canvas');
                canvas.width  = targetW;
                canvas.height = targetH;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                // Fill clean white background margin
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, targetW, targetH);

                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = distance === 'far' ? 'high' : 'medium';
                // Draw ONLY the cropped RoI into the micro-canvas with small internal padding
                ctx.drawImage(video, cropX, cropY, cropW, cropH, 10, 10, targetW - 20, targetH - 20);

                const imgData = ctx.getImageData(0, 0, targetW, targetH);
                const data    = imgData.data;
                const numPx   = targetW * targetH;
                let minG = 255, maxG = 0;

                // Grayscale pass — store gray in R channel
                for (let i = 0; i < data.length; i += 4) {
                    const g = Math.round(data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
                    if (g < minG) minG = g;
                    if (g > maxG) maxG = g;
                    data[i] = g;
                }

                if (distance === 'far') {
                    // Otsu thresholding — strong binarization for distant/smaller BIB digits
                    const gray = new Uint8Array(numPx);
                    for (let i = 0, px = 0; i < data.length; i += 4, px++) gray[px] = data[i];
                    const threshold = computeOtsuThreshold(gray, numPx);
                    for (let i = 0, px = 0; i < data.length; i += 4, px++) {
                        const v = gray[px] < threshold ? 0 : 255;
                        data[i] = data[i + 1] = data[i + 2] = v;
                        data[i + 3] = 255;
                    }
                } else {
                    // Adaptive Contrast-stretch grayscale with dynamic range expansion
                    const range = Math.max(24, maxG - minG);
                    for (let i = 0; i < data.length; i += 4) {
                        let v = Math.round(((data[i] - minG) / range) * 255);
                        v = Math.max(0, Math.min(255, Math.round((v - 128) * 1.25 + 128)));
                        data[i] = data[i + 1] = data[i + 2] = v;
                        data[i + 3] = 255;
                    }
                }

                ctx.putImageData(imgData, 0, 0);
                return canvas;
            };

            /**
             * Lightweight canvas loop: draws corner-bracket scan frame guide over the
             * aiVideo element when bib_scan mode is active.
             */
            const runBibScanCanvas = () => {
                const video  = document.getElementById('aiVideo');
                const canvas = document.getElementById('aiCanvas');
                if (!canvas || !video) return;

                if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                    canvas.width  = video.videoWidth  || 640;
                    canvas.height = video.videoHeight || 480;
                }

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                const w  = canvas.width;
                const h  = canvas.height;
                const dist = bibScan.value.distance || 'auto';
                const wRatio = dist === 'close' ? 0.50 : dist === 'far' ? 0.75 : 0.60;
                const hRatio = dist === 'close' ? 0.30 : dist === 'far' ? 0.45 : 0.35;
                const bw = Math.round(w * wRatio); // guide box width matching RoI
                const bh = Math.round(h * hRatio); // guide box height matching RoI
                const bx = Math.round((w - bw) / 2);
                const by = Math.round((h - bh) / 2);
                const cs = 32; // corner bracket size

                const scanning = bibScan.value.scanStatus === 'scanning';
                const confirming = bibScan.value.scanStatus === 'confirming';
                const locked = bibScan.value.scanStatus === 'locked';

                // Dim overlay outside guide box
                ctx.fillStyle = 'rgba(0,0,0,0.32)';
                ctx.fillRect(0, 0, w, by);
                ctx.fillRect(0, by + bh, w, h - by - bh);
                ctx.fillRect(0, by, bx, bh);
                ctx.fillRect(bx + bw, by, w - bx - bw, bh);

                // Guide box border
                const borderColor = locked ? '#10b981' : confirming ? '#f59e0b' : '#6366f1';
                ctx.strokeStyle = borderColor;
                ctx.lineWidth   = 2;
                ctx.setLineDash(locked ? [] : [6, 4]);
                ctx.strokeRect(bx, by, bw, bh);
                ctx.setLineDash([]);

                // Corner brackets
                ctx.strokeStyle = borderColor;
                ctx.lineWidth   = 3.5;
                const corners = [
                    [bx, by, bx + cs, by, bx, by + cs],
                    [bx + bw - cs, by, bx + bw, by, bx + bw, by + cs],
                    [bx, by + bh - cs, bx, by + bh, bx + cs, by + bh],
                    [bx + bw, by + bh - cs, bx + bw, by + bh, bx + bw - cs, by + bh],
                ];
                corners.forEach(([x1, y1, x2, y2, x3, y3]) => {
                    ctx.beginPath();
                    ctx.moveTo(x1, y1);
                    ctx.lineTo(x2, y2);
                    ctx.lineTo(x3, y3);
                    ctx.stroke();
                });

                // Scan label inside box
                ctx.font      = `bold 12px Inter, sans-serif`;
                ctx.fillStyle = scanning ? '#a5b4fc' : confirming ? '#fcd34d' : locked ? '#34d399' : '#a5b4fc';
                ctx.textAlign = 'center';
                const label   = locked ? 'TERCATAT' : confirming ? 'KONFIRMASI...' : 'ARAHKAN BIB KE AREA INI';
                ctx.fillText(label, bx + bw / 2, by - 10);

                bibScanCanvasId = requestAnimationFrame(runBibScanCanvas);
            };

            const stopBibScanCanvas = () => {
                if (bibScanCanvasId) {
                    cancelAnimationFrame(bibScanCanvasId);
                    bibScanCanvasId = null;
                }
                const canvas = document.getElementById('aiCanvas');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
            };

            /**
             * Main BIB scan tick — runs on setInterval at BIB_SCAN_INTERVALS[distance].
             *
             * Anti-duplikat layers:
             *   1. localRecordedBibs (in-memory, this device, instant)
             *   2. p.status === 'finished' check (synced via aggressive 500ms pollLiveSync)
             *   3. Server-side idempotency on /laps/bulk (handled by existing queueFlush)
             */
            const runBibScanTick = async () => {
                if (bibScanInFlight) return;
                if (!bibScan.value.active || !camera.value.active) return;
                if (!timer.value.running) return;
                if (!participants.value.length) return;

                const video = document.getElementById('aiVideo');
                if (!video || video.readyState < 2 || !video.videoWidth) return;

                // TTL cleanup: remove pending buffer entries that haven't been confirmed
                // within BIB_CONFIRM_TTL_MS (false-positive suppression)
                const nowTs = Date.now();
                const buf   = bibScan.value.confirmBuffer;
                for (const k of Object.keys(buf)) {
                    if (nowTs - buf[k].capturedAt > BIB_CONFIRM_TTL_MS) {
                        delete buf[k];
                    }
                }

                bibScanInFlight = true;
                bibScan.value.scanStatus = 'scanning';

                try {
                    // Capture timing BEFORE OCR (accurate timestamp regardless of processing delay)
                    const capturedAt = Date.now();
                    const elapsedMs  = timer.value.running && timer.value.startTime
                        ? Math.max(0, capturedAt - timer.value.startTime)
                        : Math.max(0, timer.value.elapsed || 0);

                    // Prepare adaptive-resolution canvas
                    const frameCanvas = prepareDistanceAwareOcrCanvas(video, bibScan.value.distance);
                    if (!frameCanvas) return;

                    const result = await runOcrQueued(async () => {
                        try {
                            const worker = await ensureOcrWorker();
                            await syncOcrWorkerToParticipantData(worker);
                            const res     = await worker.recognize(frameCanvas);
                            const rawText = String(res?.data?.text || '').trim();
                            if (!rawText) return null;

                            const best = findBestOcrParticipantMatch(rawText);
                            if (!best?.participant) return null;
                            if (best.score < bibScan.value.minScore) return null;

                            return { ...best, rawText, confidence: Number(res?.data?.confidence || 0), capturedAt, elapsedMs };
                        } catch (e) {
                            console.warn('[BibScan] OCR error:', e);
                            return null;
                        }
                    }, false);

                    // Camera or timer may have been stopped while OCR was running
                    if (!bibScan.value.active || !camera.value.active || !timer.value.running) return;

                    if (!result?.participant) {
                        // No confident match → clear confirming state if nothing pending
                        if (!Object.keys(buf).length) bibScan.value.scanStatus = 'scanning';
                        return;
                    }

                    const participant = result.participant;
                    const bibKey      = String(participant.bib);
                    const lapIdx      = Array.isArray(participant.laps) ? participant.laps.length : 0;
                    const lockKey     = `${bibKey}:${lapIdx}`;

                    // ── Layer 1: in-memory per-device lock ──────────────────────────
                    if (localRecordedBibs.has(lockKey)) {
                        bibScan.value.scanStatus = 'scanning';
                        return;
                    }

                    // ── Layer 2: participant status check (synced via 500ms poll) ──
                    if (participant.status === 'finished' && raceSettings.value.raceMode === 'single') {
                        bibScan.value.scanStatus = 'scanning';
                        return;
                    }

                    // Cooldown check per BIB
                    const cooldownMs = bibScan.value.cooldownSec * 1000;
                    if (participant.lastScanTime && (capturedAt - participant.lastScanTime < cooldownMs)) {
                        bibScan.value.scanStatus = 'scanning';
                        return;
                    }

                    // ── Confirmation buffer ─────────────────────────────────────────
                    if (!buf[bibKey]) {
                        // Frame 1: lock time immediately here — do NOT wait for confirmations
                        buf[bibKey] = { count: 1, capturedAt, elapsedMs, participant, score: result.score };
                    } else {
                        // Frame 2+: increment count; preserve ORIGINAL timestamps
                        buf[bibKey].count++;
                        if (result.score > buf[bibKey].score) buf[bibKey].score = result.score;
                    }

                    bibScan.value.confirmCount  = buf[bibKey].count;
                    const needed                = bibScan.value.confirmFrames;

                    if (buf[bibKey].count < needed) {
                        // Not yet confirmed — show progress
                        bibScan.value.scanStatus = 'confirming';
                        return;
                    }

                    // ── Confirmed: commit record ────────────────────────────────────
                    const confirmed = buf[bibKey];
                    delete buf[bibKey];
                    bibScan.value.confirmCount = 0;

                    // Final guard: re-check lock + status after buffer drain
                    if (localRecordedBibs.has(lockKey)) return;
                    if (participant.status === 'finished' && raceSettings.value.raceMode === 'single') return;

                    const timingLock = { capturedAt: confirmed.capturedAt, elapsedMs: confirmed.elapsedMs };
                    const recorded   = recordLap(participant.id, 'bib_scan', timingLock);

                    if (recorded) {
                        // Layer 1 lock — immediately prevent double record on this device
                        const newLapIdx = Array.isArray(participant.laps) ? participant.laps.length - 1 : 0;
                        localRecordedBibs.add(`${bibKey}:${newLapIdx}`);

                        bibScan.value.scanStatus  = 'locked';
                        bibScan.value.lastDetected = {
                            bib:  participant.bib,
                            name: participant.name,
                            time: formatTime(confirmed.elapsedMs),
                        };

                        // Feedback
                        playBeep();
                        if (participant.status === 'finished') {
                            triggerTvFinisherFlash(participant);
                            showDuplicateAlert('success',
                                `FINISH [BIB SCAN]: #${participant.bib} (${participant.name}) • ${formatTime(participant.totalTime)}`,
                                participant
                            );
                        } else {
                            showDuplicateAlert('info',
                                `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} [BIB SCAN]: #${participant.bib} • ${formatTime(confirmed.elapsedMs)}`,
                                participant
                            );
                        }

                        // Force an immediate sync so other devices see this record ASAP
                        if (currentSessionId.value) queueFlush();

                        // Reset to scanning after brief locked display
                        setTimeout(() => {
                            if (bibScan.value.scanStatus === 'locked') {
                                bibScan.value.scanStatus  = 'scanning';
                                bibScan.value.lastDetected = null;
                            }
                        }, 2500);
                    }
                } catch (e) {
                    console.warn('[BibScan] tick error:', e);
                } finally {
                    bibScanInFlight = false;
                }
            };

            const stopBibScanLoop = () => {
                if (bibScanLoopTimer) {
                    clearInterval(bibScanLoopTimer);
                    bibScanLoopTimer = null;
                }
                bibScanInFlight = false;
                bibScan.value.active        = false;
                bibScan.value.scanStatus    = 'idle';
                bibScan.value.confirmCount  = 0;
                bibScan.value.confirmBuffer = {};
                bibScan.value.lastDetected  = null;
                stopBibScanCanvas();

                // Restore normal 1500ms sync interval
                if (bibScan.value.aggressiveSyncActive) {
                    bibScan.value.aggressiveSyncActive = false;
                    if (sessionSyncTimer) {
                        clearInterval(sessionSyncTimer);
                        sessionSyncTimer = setInterval(pollLiveSync, 1500);
                    }
                }
            };

            const startBibScanLoop = () => {
                stopBibScanLoop();

                bibScan.value.active     = true;
                bibScan.value.scanStatus = 'scanning';

                const intervalMs = BIB_SCAN_INTERVALS[bibScan.value.distance] || 350;
                bibScanLoopTimer = setInterval(() => {
                    runBibScanTick();
                }, intervalMs);

                // First tick immediately after startup
                setTimeout(() => runBibScanTick(), 150);

                // Upgrade to aggressive 500ms sync for multi-device anti-duplikat (Layer 2)
                if (sessionSyncActive.value && !bibScan.value.aggressiveSyncActive) {
                    if (sessionSyncTimer) clearInterval(sessionSyncTimer);
                    sessionSyncTimer = setInterval(pollLiveSync, 500);
                    bibScan.value.aggressiveSyncActive = true;
                }

                // Draw scan guide canvas
                runBibScanCanvas();
            };

            const startBibScanCamera = async (deviceId) => {
                try {
                    const constraints = {
                        video: {
                            deviceId: deviceId ? { exact: deviceId } : undefined,
                            width:  { ideal: 1280 },
                            height: { ideal: 720 },
                            facingMode: 'environment',
                        }
                    };
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);

                    const video = document.getElementById('aiVideo');
                    if (!video) { stream.getTracks().forEach(t => t.stop()); return; }

                    video.srcObject = stream;
                    camera.value.stream = stream;

                    await new Promise(resolve => { video.onloadedmetadata = resolve; });
                    await video.play();

                    camera.value.active = true;
                    camera.value.lastScanMsg = 'BIB Scan aktif — Arahkan kamera ke nomor BIB pelari';

                    // Pre-warm Tesseract in background (don't await — UI should not block)
                    ensureOcrWorker().catch(() => {});

                    startBibScanLoop();
                } catch (err) {
                    console.error('[BibScan] Camera error:', err);
                    camera.value.lastScanMsg = 'Gagal membuka kamera: ' + (err.message || err);
                }
            };

            const stopBibScanCamera = () => {
                stopBibScanLoop();
                const video = document.getElementById('aiVideo');
                if (video) {
                    video.srcObject = null;
                    video.pause();
                }
                if (camera.value.stream) {
                    camera.value.stream.getTracks().forEach(t => t.stop());
                    camera.value.stream = null;
                }
                camera.value.active = false;
                camera.value.lastScanMsg = '';
                localRecordedBibs.clear();
            };

            /**
             * Pattern Training: reads uploaded BIB sample images through OCR and
             * displays the extracted digit patterns below each thumbnail.
             * The actual participant profile used at runtime comes from getBibRuntimeProfile()
             * (participant data), but this feature helps users verify scanner readiness.
             */
            const onBibSampleUpload = async (event) => {
                const files = Array.from(event.target.files || []);
                if (!files.length) return;

                bibScan.value.trainStatus = 'processing';

                for (const file of files) {
                    const src = await new Promise(resolve => {
                        const reader = new FileReader();
                        reader.onload = e => resolve(e.target.result);
                        reader.readAsDataURL(file);
                    });

                    const entry = { name: file.name, src, extractedBibs: [] };
                    bibScan.value.sampleImages.push(entry);

                    try {
                        const img = new Image();
                        img.src   = src;
                        await new Promise(resolve => { img.onload = resolve; });

                        const canvas  = document.createElement('canvas');
                        canvas.width  = Math.min(960, img.naturalWidth);
                        canvas.height = Math.round(img.naturalHeight * (canvas.width / img.naturalWidth));
                        const ctx     = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                        const worker = await ensureOcrWorker();
                        await syncOcrWorkerToParticipantData(worker);
                        const res    = await worker.recognize(canvas);
                        const text   = String(res?.data?.text || '').trim();

                        if (text) {
                            const best = findBestOcrParticipantMatch(text);
                            if (best?.bib) entry.extractedBibs = [best.bib];
                            else {
                                // Fallback: extract any numeric sequences
                                const nums = text.match(/\d{1,6}/g) || [];
                                entry.extractedBibs = [...new Set(nums)].slice(0, 3);
                            }
                        }
                    } catch (e) {
                        console.warn('[BibScan] Training OCR error:', e);
                    }
                }

                bibScan.value.trainStatus = 'done';
                // Reset file input so user can re-upload same file if needed
                event.target.value = '';
            };

            const pointToFinishLineDistance = (point, line) => {
                const x1 = line.x1, y1 = line.y1, x2 = line.x2, y2 = line.y2;
                const dx = x2 - x1;
                const dy = y2 - y1;
                const denom = Math.hypot(dx, dy) || 1;
                return Math.abs(dy * point.x - dx * point.y + x2 * y1 - y2 * x1) / denom;
            };


            // Runner Crossing Event Handler
            const handleRunnerCrossing = async (video, bbox, trackId = null, crossingTiming = null) => {
                camera.value.crossingCount++;
                camera.value.lineFlash = true;
                setTimeout(() => { camera.value.lineFlash = false; }, 450);
                if (raceSettings.value.enableBeep) playBeep();

                // Lock timing IMMEDIATELY at the physical crossing. OCR/Face AI may finish later.
                const capturedAt = Number.isFinite(crossingTiming?.capturedAt) ? crossingTiming.capturedAt : Date.now();
                const recordedTimeMs = Number.isFinite(crossingTiming?.elapsedMs)
                    ? crossingTiming.elapsedMs
                    : (timer.value.running && timer.value.startTime ? Math.max(0, capturedAt - timer.value.startTime) : Math.max(0, timer.value.elapsed || 0));
                const timingLock = { capturedAt, elapsedMs: recordedTimeMs };
                const formattedTimeStr = formatTime(recordedTimeMs);

                // Create snapshot thumbnail immediately from the crossing frame.
                let snapshotDataUrl = '';
                try {
                    const snapCanvas = document.createElement('canvas');
                    snapCanvas.width = 240;
                    snapCanvas.height = 240;
                    const snapCtx = snapCanvas.getContext('2d');

                    const [bx, by, bw, bh] = bbox;
                    const padX = Math.max(0, bx - 14);
                    const padY = Math.max(0, by - 14);
                    const padW = Math.min(video.videoWidth - padX, bw + 28);
                    const padH = Math.min(video.videoHeight - padY, bh + 28);
                    snapCtx.drawImage(video, padX, padY, padW, padH, 0, 0, 240, 240);
                    snapshotDataUrl = snapCanvas.toDataURL('image/jpeg', 0.86);
                } catch (e) {}

                let detectedBib = '';
                let matchMethod = '';
                let participant = null;
                let weakCandidate = null;

                // Engine 1: QR. Accept only if it resolves to a participant; otherwise continue OCR/Face.
                if (raceSettings.value.enableQr) {
                    try {
                        const bibs = await decodeMultipleFromVideoFrame(video);
                        for (const qrBib of bibs || []) {
                            const resolved = resolveBibCandidate(qrBib);
                            if (resolved.participant) {
                                participant = resolved.participant;
                                detectedBib = normalizeBib(participant.bib) || participant.bib;
                                matchMethod = 'qr';
                                break;
                            }
                            if (!weakCandidate && resolved.bib) weakCandidate = { ...resolved, method: 'qr' };
                        }
                    } catch (e) {}
                }

                // OCR is handled by the independent GLOBAL full-frame scanner.
                // Do not block crossing handling with Tesseract or per-person OCR here.

                // Engine 3: Face Recognition fallback.
                if (!participant && raceSettings.value.enableFaceAi && faceModelsLoaded) {
                    try {
                        const [bx, by, bw, bh] = bbox;
                        const headY = Math.max(0, by);
                        const headH = Math.min(video.videoHeight - headY, bh * 0.45);
                        const headX = Math.max(0, bx + bw * 0.1);
                        const headW = Math.min(video.videoWidth - headX, bw * 0.8);

                        const faceCanvas = document.createElement('canvas');
                        faceCanvas.width = 160;
                        faceCanvas.height = 160;
                        const fCtx = faceCanvas.getContext('2d');
                        fCtx.drawImage(video, headX, headY, headW, headH, 0, 0, 160, 160);

                        const liveDesc = await extractFaceDescriptorFromImage(faceCanvas);
                        if (liveDesc) {
                            let bestMatch = null;
                            let minDistance = 0.54;

                            participants.value.forEach(p => {
                                const targetDescriptors = Array.isArray(p.faceDescriptors) && p.faceDescriptors.length > 0
                                    ? p.faceDescriptors
                                    : (p.faceDescriptor ? [p.faceDescriptor] : []);

                                targetDescriptors.forEach(desc => {
                                    if (Array.isArray(desc)) {
                                        const dist = faceapi.euclideanDistance(liveDesc, desc);
                                        if (dist < minDistance) {
                                            minDistance = dist;
                                            bestMatch = p;
                                        }
                                    }
                                });
                            });

                            if (bestMatch) {
                                participant = bestMatch;
                                detectedBib = normalizeBib(bestMatch.bib) || bestMatch.bib;
                                matchMethod = 'face_ai';
                            }
                        }
                    } catch (e) {}
                }

                // Preserve the best readable text for audit/manual assignment when automatic identity failed.
                if (!participant && weakCandidate?.bib) detectedBib = weakCandidate.bib;

                if (participant && timer.value.running) {
                    const tag = matchMethod === 'face_ai' ? '[Face AI]' : '[QR]';

                    if (raceSettings.value.raceMode === 'single') {
                        if (participant.status === 'finished') {
                            camera.value.lastScanMsg = `BIB #${participant.bib} sudah Finish sebelumnya (${formatTime(participant.totalTime)})`;
                            showDuplicateAlert('warning', `DUPLIKAT: BIB #${participant.bib} (${participant.name}) SUDAH FINISH sebelumnya pada ${formatTime(participant.totalTime)}!`, participant);
                        } else {
                            const recorded = recordLap(participant.id, 'ai_vision', timingLock);
                            if (recorded) {
                                triggerTvFinisherFlash(participant);
                                camera.value.lastScanMsg = `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`;
                                showDuplicateAlert('success', `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`, participant);
                            }
                        }
                    } else {
                        const cooldownMs = raceSettings.value.minLapCooldownSec * 1000;
                        if (participant.lastScanTime && (capturedAt - participant.lastScanTime < cooldownMs)) {
                            const rem = Math.ceil((cooldownMs - (capturedAt - participant.lastScanTime)) / 1000);
                            camera.value.lastScanMsg = `BIB #${participant.bib} dalam masa cooldown putaran`;
                            showDuplicateAlert('warning', `BIB #${participant.bib} (${participant.name}) masih dalam cooldown lap (${rem}s tersisa)!`, participant);
                        } else {
                            const recorded = recordLap(participant.id, 'ai_vision', timingLock);
                            if (recorded) {
                                if (participant.laps.length >= raceSettings.value.targetLaps) {
                                    triggerTvFinisherFlash(participant);
                                    camera.value.lastScanMsg = `FINISH ${tag} (Lap ${participant.laps.length}): BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`;
                                    showDuplicateAlert('success', `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`, participant);
                                } else {
                                    camera.value.lastScanMsg = `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} ${tag}: BIB #${participant.bib} • ${formattedTimeStr}`;
                                    showDuplicateAlert('info', `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} ${tag}: BIB #${participant.bib} • ${formattedTimeStr}`, participant);
                                }
                            }
                        }
                    }
                } else if (weakCandidate?.ambiguous) {
                    const names = (weakCandidate.candidates || []).map(p => p.bib).join(', ');
                    camera.value.lastScanMsg = `OCR ambigu: ${weakCandidate.bib} → ${names || 'beberapa peserta'} • ${formattedTimeStr}`;
                    showDuplicateAlert('warning', `BIB ${weakCandidate.bib} terbaca tetapi ambigu (${names}). Gunakan snapshot / Face AI / assign manual.`);
                } else if (detectedBib) {
                    camera.value.lastScanMsg = `Crossing: BIB terbaca #${detectedBib}, belum cocok ke peserta • ${formattedTimeStr}`;
                    showDuplicateAlert('error', `Crossing terdeteksi BIB #${detectedBib}, namun identitas belum dapat dipastikan.`);
                } else {
                    camera.value.lastScanMsg = `Crossing terdeteksi • Waktu: ${formattedTimeStr} • Perlu verifikasi BIB`;
                }

                liveFinishFeed.value.unshift({
                    id: crypto.randomUUID(),
                    capturedAt,
                    timestampMs: recordedTimeMs,
                    timeFormatted: formattedTimeStr,
                    bib: participant ? participant.bib : (detectedBib || ''),
                    name: participant ? participant.name : '',
                    snapshot: snapshotDataUrl,
                    participantId: participant ? participant.id : null,
                    matchMethod: matchMethod || weakCandidate?.method || '',
                    ocrConfidence: Number(weakCandidate?.confidence || weakCandidate?.avg || 0),
                    ambiguous: Boolean(weakCandidate?.ambiguous),
                });

                if (liveFinishFeed.value.length > 30) liveFinishFeed.value.pop();
            };

            // AI Detection & Canvas Rendering Loop
            const runAiDetectionLoop = async () => {
                if (!camera.value.active || camera.value.mode !== 'ai_line') return;
                const video = document.getElementById('aiVideo');
                const canvas = document.getElementById('aiCanvas');
                if (!video || !canvas || video.readyState < 2) {
                    aiAnimationId = requestAnimationFrame(runAiDetectionLoop);
                    return;
                }

                if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                }

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Calculate FPS
                frameCount++;
                const now = Date.now();
                if (now - lastFpsCalc >= 1000) {
                    camera.value.fps = frameCount;
                    frameCount = 0;
                    lastFpsCalc = now;
                }

                const vw = canvas.width;
                const vh = canvas.height;

                // Draw Finish Line
                const lx1 = camera.value.line.x1 * vw;
                const ly1 = camera.value.line.y1 * vh;
                const lx2 = camera.value.line.x2 * vw;
                const ly2 = camera.value.line.y2 * vh;

                ctx.save();
                ctx.lineWidth = camera.value.lineFlash ? 8 : 4;
                ctx.strokeStyle = camera.value.lineFlash ? '#10B981' : '#6366F1';
                ctx.setLineDash([8, 6]);
                ctx.beginPath();
                ctx.moveTo(lx1, ly1);
                ctx.lineTo(lx2, ly2);
                ctx.stroke();
                ctx.setLineDash([]);

                // Draw Endpoint handles
                const drawHandle = (x, y, label) => {
                    ctx.beginPath();
                    ctx.arc(x, y, 14, 0, Math.PI * 2);
                    ctx.fillStyle = '#6366F1';
                    ctx.fill();
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = '#FFFFFF';
                    ctx.stroke();

                    ctx.fillStyle = '#FFFFFF';
                    ctx.font = 'bold 11px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(label, x, y);
                };

                drawHandle(lx1, ly1, 'A');
                drawHandle(lx2, ly2, 'B');
                ctx.restore();

                // Detect Person with COCO-SSD
                if (cocoModel && !camera.value.busy) {
                    try {
                        const predictions = await cocoModel.detect(video);
                        const people = predictions.filter(p => p.class === 'person' && p.score >= camera.value.minConfidence);

                        for (const person of people) {
                            const [bx, by, bw, bh] = person.bbox;
                            const normCentroid = {
                                x: (bx + bw / 2) / vw,
                                y: (by + bh * 0.82) / vh
                            };

                            // Draw Person Bounding Box
                            ctx.save();
                            ctx.strokeStyle = '#38BDF8';
                            ctx.lineWidth = 2;
                            ctx.strokeRect(bx, by, bw, bh);

                            ctx.fillStyle = '#38BDF8';
                            ctx.font = 'bold 11px Inter, sans-serif';
                            ctx.fillText(`Runner ${Math.round(person.score * 100)}%`, bx + 4, by > 16 ? by - 4 : by + 14);
                            ctx.restore();

                            // Track ID association
                            let matchedTrackId = null;
                            let minDistance = 0.16;

                            for (const [tId, tData] of personTracks.entries()) {
                                const dist = Math.hypot(normCentroid.x - tData.lastX, normCentroid.y - tData.lastY);
                                if (dist < minDistance && now - tData.lastTime < 1500) {
                                    minDistance = dist;
                                    matchedTrackId = tId;
                                }
                            }

                            if (!matchedTrackId) {
                                matchedTrackId = nextTrackId++;
                                personTracks.set(matchedTrackId, {
                                    lastX: normCentroid.x,
                                    lastY: normCentroid.y,
                                    lastTime: now,
                                    crossedAt: 0,
                                });
                            } else {
                                const track = personTracks.get(matchedTrackId);
                                const prevPoint = { x: track.lastX, y: track.lastY };
                                const currPoint = normCentroid;

                                const lineP1 = { x: camera.value.line.x1, y: camera.value.line.y1 };
                                const lineP2 = { x: camera.value.line.x2, y: camera.value.line.y2 };

                                const isCrossing = checkIntersection(prevPoint, currPoint, lineP1, lineP2);
                                const isValidDir = checkDirectionValid(prevPoint, currPoint, camera.value.line);

                                if (isCrossing && isValidDir && (now - track.crossedAt > 3500)) {
                                    track.crossedAt = now;

                                    // Lock official time here, before any async OCR/Face processing.
                                    const crossingCapturedAt = Date.now();
                                    const crossingElapsedMs = timer.value.running && timer.value.startTime
                                        ? Math.max(0, crossingCapturedAt - timer.value.startTime)
                                        : Math.max(0, timer.value.elapsed || 0);

                                    handleRunnerCrossing(video, person.bbox, matchedTrackId, {
                                        capturedAt: crossingCapturedAt,
                                        elapsedMs: crossingElapsedMs,
                                    });
                                }

                                track.lastX = normCentroid.x;
                                track.lastY = normCentroid.y;
                                track.lastTime = now;
                            }
                        }

                        // Prune old tracks
                        for (const [tId, tData] of personTracks.entries()) {
                            if (now - tData.lastTime > 2500) {
                                personTracks.delete(tId);
                            }
                        }
                    } catch (err) {
                        console.error('Detection error:', err);
                    }
                }

                aiAnimationId = requestAnimationFrame(runAiDetectionLoop);
            };

            // Start & Stop AI Camera
            const startAiCamera = async (deviceId = null) => {
                stopAiCamera();
                try {
                    // Do not block OCR/camera startup on COCO-SSD. Person detection may load in parallel.
                    loadAiModel().catch(() => {});
                    const constraints = {
                        video: {
                            deviceId: deviceId ? { exact: deviceId } : undefined,
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
                            facingMode: deviceId ? undefined : { ideal: 'environment' }
                        }
                    };

                    aiStream = await navigator.mediaDevices.getUserMedia(constraints);
                    const video = document.getElementById('aiVideo');
                    if (video) {
                        video.srcObject = aiStream;
                        await video.play();
                        camera.value.active = true;

                        // FAST OCR starts immediately and is independent from COCO/person tracking.
                        startGlobalOcrScanner();

                        // Keep the existing line/person pipeline only for line crossing / Face AI / visuals.
                        runAiDetectionLoop();
                    }
                } catch (e) {
                    console.error('Failed to start AI Camera:', e);
                    alert('Gagal membuka stream kamera AI.');
                }
            };

            const stopAiCamera = () => {
                stopGlobalOcrScanner();
                stopBibScanCamera();
                if (aiAnimationId) {
                    cancelAnimationFrame(aiAnimationId);
                    aiAnimationId = null;
                }
                if (aiStream) {
                    aiStream.getTracks().forEach(t => t.stop());
                    aiStream = null;
                }
                const video = document.getElementById('aiVideo');
                if (video) video.srcObject = null;
                camera.value.active = false;
            };


            const switchCameraDevice = () => {
                if (!camera.value.active) return;
                if (camera.value.mode === 'ai_line') {
                    startAiCamera(camera.value.selectedDeviceId);
                } else if (camera.value.mode === 'bib_scan') {
                    stopBibScanCamera();
                    nextTick(() => startBibScanCamera(camera.value.selectedDeviceId));
                }
            };


            const switchCameraMode = () => {
                if (!camera.value.active) return;
                const mode = camera.value.mode;
                if (mode === 'ai_line') {
                    // Switching TO ai_line: stop QR or bib_scan first
                    if (camera.value.scanner) {
                        camera.value.scanner.stop().catch(() => {}).then(() => {
                            camera.value.scanner = null;
                            startAiCamera(camera.value.selectedDeviceId);
                        });
                    } else {
                        stopBibScanCamera();
                        startAiCamera(camera.value.selectedDeviceId);
                    }
                } else if (mode === 'bib_scan') {
                    // Switching TO bib_scan
                    stopAiCamera();
                    if (camera.value.scanner) {
                        camera.value.scanner.stop().catch(() => {}).finally(() => {
                            camera.value.scanner = null;
                            nextTick(() => startBibScanCamera(camera.value.selectedDeviceId));
                        });
                    } else {
                        nextTick(() => startBibScanCamera(camera.value.selectedDeviceId));
                    }
                } else {
                    // Switching TO qr_only
                    stopAiCamera();
                    stopBibScanCamera();
                    toggleScanner();
                }
            };


            // Toggle Camera / Scanner
            const toggleScanner = () => {
                if (camera.value.active) {
                    // ── OFF: stop whichever mode is running ──────────────────
                    if (camera.value.mode === 'bib_scan') {
                        stopBibScanCamera();
                    } else {
                        stopAiCamera();
                        if (camera.value.scanner) {
                            camera.value.scanner.stop().catch(() => {}).then(() => {
                                camera.value.active = false;
                                camera.value.lastScanMsg = '';
                                stopAutoMultiScan();
                                camera.value.scanner = null;
                            });
                        } else {
                            camera.value.active = false;
                        }
                    }
                } else {
                    // ── ON: start the selected mode ──────────────────────────
                    refreshCameraDevices();
                    if (camera.value.mode === 'ai_line') {
                        camera.value.active = true;
                        nextTick(() => {
                            startAiCamera(camera.value.selectedDeviceId);
                        });
                    } else if (camera.value.mode === 'bib_scan') {
                        nextTick(() => {
                            startBibScanCamera(camera.value.selectedDeviceId);
                        });
                    } else {
                        camera.value.active = true;
                        nextTick(() => {
                            const html5QrCode = new Html5Qrcode("reader");
                            camera.value.scanner = html5QrCode;
                            
                            html5QrCode.start(
                                { facingMode: "environment" },
                                { fps: 20, qrbox: { width: 320, height: 320 } },
                                (decodedText) => {
                                    const bib = normalizeBib(decodedText);
                                    if (!bib) return;
                                    const p = findParticipantByBib(bib);
                                    if (p) {
                                        recordLap(p.id, 'scanner');
                                    } else {
                                        camera.value.lastScanMsg = `Unknown BIB: ${bib}`;
                                    }
                                },
                                () => {}
                            ).then(() => {
                                if (getBarcodeDetector()) {
                                    startAutoMultiScan();
                                }

                                // Full-frame OCR also works in QR-only camera mode.
                                startGlobalOcrScanner();

                                setTimeout(tryImproveVideoTrack, 500);
                                saveState();
                            }).catch(err => {
                                console.error(err);
                                alert('Gagal membuka kamera');
                                camera.value.active = false;
                                stopAutoMultiScan();
                            });
                        });
                    }
                }
            };

            // Manual Assign for Unassigned Finish item
            const openAssignBibModal = (item) => {
                selectedUnassignedFinish.value = item;
                assignBibModalOpen.value = true;
            };

            const confirmAssignBib = (p) => {
                if (!selectedUnassignedFinish.value) return;
                const item = selectedUnassignedFinish.value;
                item.bib = p.bib;
                item.name = p.name;
                item.participantId = p.id;

                const originalTiming = {
                    capturedAt: Number.isFinite(item.capturedAt) ? item.capturedAt : Date.now(),
                    elapsedMs: Number.isFinite(item.timestampMs) ? item.timestampMs : Math.max(0, timer.value.elapsed || 0),
                };

                if (timer.value.running || timer.value.elapsed > 0) {
                    recordLap(p.id, 'manual_assign', originalTiming);
                } else {
                    if (!Array.isArray(p.laps)) p.laps = [];
                    p.totalTime = originalTiming.elapsedMs;
                    p.laps.push(originalTiming.capturedAt);
                    p.lastScanTime = originalTiming.capturedAt;
                    p.status = 'finished';
                    saveState();
                }

                assignBibModalOpen.value = false;
                selectedUnassignedFinish.value = null;
                alert(`Hasil finish ${item.timeFormatted} berhasil ditetapkan untuk ${p.name} (BIB #${p.bib})`);
            };

            const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

            const getReaderVideo = () => {
                const videoEl = document.getElementById('aiVideo');
                if (videoEl && videoEl.srcObject) return videoEl;
                const reader = document.getElementById('reader');
                if (!reader) return null;
                return reader.querySelector('video');
            };

            const tryImproveVideoTrack = () => {
                try {
                    const video = getReaderVideo();
                    if (!video || !video.srcObject) return;
                    const tracks = video.srcObject.getVideoTracks ? video.srcObject.getVideoTracks() : [];
                    const track = tracks && tracks[0] ? tracks[0] : null;
                    if (!track || !track.getCapabilities || !track.applyConstraints) return;
                    const caps = track.getCapabilities();
                    const advanced = [];

                    if (caps && Array.isArray(caps.focusMode) && caps.focusMode.includes('continuous')) {
                        advanced.push({ focusMode: 'continuous' });
                    }
                    if (caps && Array.isArray(caps.exposureMode) && caps.exposureMode.includes('continuous')) {
                        advanced.push({ exposureMode: 'continuous' });
                    }
                    if (caps && Array.isArray(caps.whiteBalanceMode) && caps.whiteBalanceMode.includes('continuous')) {
                        advanced.push({ whiteBalanceMode: 'continuous' });
                    }

                    if (advanced.length === 0) return;
                    track.applyConstraints({ advanced }).catch(() => {});
                } catch (e) {}
            };

            let captureCanvas = null;
            let captureCtx = null;

            const decodeImageData = (imageData) => {
                if (typeof jsQR !== 'function') return null;
                return jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });
            };

            const decodeFromVideoFrame = (video) => {
                if (!video || !video.videoWidth || !video.videoHeight) return null;

                const w = video.videoWidth;
                const h = video.videoHeight;
                if (!captureCanvas) {
                    captureCanvas = document.createElement('canvas');
                }
                if (captureCanvas.width !== w) captureCanvas.width = w;
                if (captureCanvas.height !== h) captureCanvas.height = h;

                if (!captureCtx) {
                    captureCtx = captureCanvas.getContext('2d', { willReadFrequently: true });
                }

                captureCtx.drawImage(video, 0, 0, w, h);

                const size = Math.floor(Math.min(w, h) * 0.75);
                const x = Math.floor((w - size) / 2);
                const y = Math.floor((h - size) / 2);

                const centerData = captureCtx.getImageData(x, y, size, size);
                const centerCode = decodeImageData(centerData);
                if (centerCode && centerCode.data) return String(centerCode.data).trim();

                const fullData = captureCtx.getImageData(0, 0, w, h);
                const fullCode = decodeImageData(fullData);
                if (fullCode && fullCode.data) return String(fullCode.data).trim();

                return null;
            };

            let barcodeDetector = null;
            let autoMultiInterval = null;

            const getBarcodeDetector = () => {
                if (barcodeDetector) return barcodeDetector;
                try {
                    if (!('BarcodeDetector' in window)) return null;
                    barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
                    return barcodeDetector;
                } catch (e) {
                    return null;
                }
            };

            const drawVideoToCanvas = (video) => {
                const w = video.videoWidth;
                const h = video.videoHeight;
                if (!captureCanvas) captureCanvas = document.createElement('canvas');
                if (captureCanvas.width !== w) captureCanvas.width = w;
                if (captureCanvas.height !== h) captureCanvas.height = h;
                if (!captureCtx) captureCtx = captureCanvas.getContext('2d', { willReadFrequently: true });
                captureCtx.drawImage(video, 0, 0, w, h);
                return { w, h };
            };

            const decodeMultipleFromCanvasWithJsQR = (w, h) => {
                if (typeof jsQR !== 'function') return [];
                const out = new Set();
                const min = Math.min(w, h);
                const size = Math.floor(min * 0.6);
                const positions = [
                    [Math.floor((w - size) / 2), Math.floor((h - size) / 2)],
                    [0, 0],
                    [w - size, 0],
                    [0, h - size],
                    [w - size, h - size],
                ];

                for (const [x, y] of positions) {
                    if (x < 0 || y < 0) continue;
                    try {
                        const data = captureCtx.getImageData(x, y, size, size);
                        const code = decodeImageData(data);
                        if (code && code.data) {
                            const bib = normalizeBib(code.data);
                            if (bib) out.add(bib);
                        }
                    } catch (e) {}
                }

                return Array.from(out);
            };

            const decodeMultipleFromVideoFrame = async (video) => {
                if (!video || !video.videoWidth || !video.videoHeight) return [];
                const { w, h } = drawVideoToCanvas(video);

                const detector = getBarcodeDetector();
                if (detector) {
                    try {
                        const detections = await detector.detect(captureCanvas);
                        const out = new Set();
                        for (const d of detections || []) {
                            const bib = normalizeBib(d.rawValue || '');
                            if (bib) out.add(bib);
                        }
                        if (out.size > 0) return Array.from(out);
                    } catch (e) {}
                }

                return decodeMultipleFromCanvasWithJsQR(w, h);
            };

            const recordMultipleBibs = (bibs, source = 'scanner') => {
                if (!Array.isArray(bibs) || bibs.length === 0) return;

                const matched = [];
                const unknown = [];
                for (const bib of bibs) {
                    const p = findParticipantByBib(bib);
                    if (p) {
                        recordLap(p.id, source);
                        matched.push(bib);
                    } else {
                        unknown.push(bib);
                    }
                }

                if (matched.length > 0) {
                    camera.value.lastScanMsg = `Scanned: ${matched.join(', ')}${unknown.length ? ` • Unknown: ${unknown.join(', ')}` : ''}`;
                } else if (unknown.length > 0) {
                    camera.value.lastScanMsg = `Unknown BIB: ${unknown.join(', ')}`;
                }
            };

            const startAutoMultiScan = () => {
                if (autoMultiInterval) return;
                autoMultiInterval = setInterval(async () => {
                    if (!camera.value.active) return;
                    if (camera.value.busy) return;
                    if (!timer.value.running) return;
                    const video = getReaderVideo();
                    if (!video || !video.videoWidth) return;

                    camera.value.busy = true;
                    try {
                        const bibs = await decodeMultipleFromVideoFrame(video);
                        if (bibs.length > 0) recordMultipleBibs(bibs, 'scanner');
                    } finally {
                        camera.value.busy = false;
                    }
                }, 160);
            };

            const stopAutoMultiScan = () => {
                if (!autoMultiInterval) return;
                clearInterval(autoMultiInterval);
                autoMultiInterval = null;
            };

            const captureScan = async () => {
                if (camera.value.busy) return;
                if (!camera.value.active) return;

                if (typeof jsQR !== 'function') {
                    camera.value.lastScanMsg = 'Decoder QR belum siap.';
                    return;
                }

                if (!timer.value.running) {
                    camera.value.lastScanMsg = 'Timer belum start.';
                    return;
                }

                const video = getReaderVideo();
                if (!video) {
                    camera.value.lastScanMsg = 'Kamera belum siap.';
                    return;
                }

                camera.value.busy = true;
                try {
                    camera.value.lastScanMsg = 'Capturing...';

                    const found = new Set();
                    const attempts = 4;
                    for (let i = 0; i < attempts; i++) {
                        const bibs = await decodeMultipleFromVideoFrame(video);
                        for (const b of bibs) found.add(b);
                        if (found.size >= 2) break;
                        if (found.size >= 1 && i >= 1) break;
                        await sleep(60);
                    }

                    const bibList = Array.from(found);
                    if (bibList.length === 0) {
                        camera.value.lastScanMsg = 'QR tidak terbaca, coba lagi.';
                        return;
                    }
                    recordMultipleBibs(bibList, 'scanner');
                } finally {
                    camera.value.busy = false;
                }
            };

            const onKeydown = (e) => {
                if (e.key === 'Escape' && tvDisplayOpen.value) {
                    closeTvDisplay();
                    return;
                }
                if (currentView.value !== 'race') return;
                if (!camera.value.active) return;
                if (camera.value.busy) return;

                if (e.code === 'Space') {
                    e.preventDefault();
                    captureScan();
                }
            };

            onMounted(async () => {
                loadState();
                queueLoad();
                await loadExistingRaces();
                await loadEoEvents();
                refreshCameraDevices();
                window.addEventListener('keydown', onKeydown);
                window.addEventListener('click', handleClickOutsideEo);
                document.addEventListener('fullscreenchange', () => {
                    isFullscreen.value = !!document.fullscreenElement;
                });
                loadAiModel();

                // Check URL parameter ?session=XXXX for multi-device sync
                const urlParams = new URLSearchParams(window.location.search);
                const roomSession = urlParams.get('session');
                const modeParam = urlParams.get('mode') || urlParams.get('view');
                if (roomSession) {
                    sessionSlug.value = roomSession;
                    sessionRoomInput.value = roomSession;
                    await joinLiveSession(roomSession);
                } else if (sessionSlug.value || currentSessionId.value) {
                    startLiveSyncPolling();
                }

                if (modeParam === 'tv') {
                    // Otomatis buka Layar TV fullscreen saat URL mode=tv diakses
                    openTvDisplay();
                }
            });

            onBeforeUnmount(() => {
                stopAiCamera();
                stopAutoMultiScan();
                window.removeEventListener('keydown', onKeydown);
                window.removeEventListener('click', handleClickOutsideEo);
                if (queueFlushInterval.value) clearInterval(queueFlushInterval.value);
                if (sessionSyncTimer) clearInterval(sessionSyncTimer);
                if (ocrWorker) {
                    ocrWorker.terminate();
                    ocrWorker = null;
                }
                ocrWorkerInitPromise = null;
                ocrQueue = Promise.resolve();
                ocrQueueDepth = 0;
                ocrWorkerProfileSignature = '';
            });

            // Race Tab Pagination & Fast Filter State
            const raceSearchQuery = ref('');
            const raceStatusFilter = ref('on_track'); // 'on_track' | 'all' | 'finished' | 'dnf'
            const raceViewMode = ref('grid'); // 'grid' | 'table'
            const racePageSize = ref(24);
            const raceCurrentPage = ref(1);

            const filteredRaceParticipants = computed(() => {
                let list = participants.value;

                // Status filter
                if (raceStatusFilter.value === 'on_track') {
                    list = list.filter(p => p.status === 'running' || p.status === 'ready' || !p.status);
                } else if (raceStatusFilter.value === 'finished') {
                    list = list.filter(p => p.status === 'finished');
                } else if (raceStatusFilter.value === 'dnf') {
                    list = list.filter(p => p.status === 'dnf');
                }

                // Search query filter (BIB or Name)
                const q = (raceSearchQuery.value || '').trim().toLowerCase();
                if (q) {
                    list = list.filter(p => {
                        const bib = String(p.bib ?? '').toLowerCase();
                        const name = String(p.name ?? '').toLowerCase();
                        return bib.includes(q) || name.includes(q);
                    });
                }

                return list;
            });

            const raceTotalPages = computed(() => {
                if (racePageSize.value >= 9999) return 1;
                return Math.max(1, Math.ceil(filteredRaceParticipants.value.length / racePageSize.value));
            });

            const paginatedRaceParticipants = computed(() => {
                if (racePageSize.value >= 9999) return filteredRaceParticipants.value;
                const start = (raceCurrentPage.value - 1) * racePageSize.value;
                return filteredRaceParticipants.value.slice(start, start + racePageSize.value);
            });

            const prevRacePage = () => {
                if (raceCurrentPage.value > 1) raceCurrentPage.value--;
            };

            const nextRacePage = () => {
                if (raceCurrentPage.value < raceTotalPages.value) raceCurrentPage.value++;
            };

            const setRacePage = (page) => {
                raceCurrentPage.value = Math.max(1, Math.min(page, raceTotalPages.value));
            };

            Vue.watch([raceSearchQuery, raceStatusFilter, racePageSize], () => {
                raceCurrentPage.value = 1;
            });

            // Computed
            const activeParticipants = computed(() => {
                return participants.value.filter(p => p.status === 'running' || p.status === 'ready');
            });

            const sortedResults = computed(() => {
                // Sort by: Finished > Laps Desc > Time Asc
                return [...participants.value].sort((a, b) => {
                    if (a.status === 'dnf' && b.status !== 'dnf') return 1;
                    if (a.status !== 'dnf' && b.status === 'dnf') return -1;
                    
                    // Both active/finished
                    const aLaps = a.laps.length;
                    const bLaps = b.laps.length;
                    
                    if (aLaps !== bLaps) return bLaps - aLaps; // More laps first
                    return a.totalTime - b.totalTime; // Faster time first
                });
            });
            
            const dnfParticipants = computed(() => {
                return participants.value.filter(p => p.status === 'dnf');
            });

            // Dark Mode
            const isDarkMode = ref(localStorage.getItem('race-master-theme') === 'dark');
            const toggleDarkMode = () => {
                isDarkMode.value = !isDarkMode.value;
                localStorage.setItem('race-master-theme', isDarkMode.value ? 'dark' : 'light');
                if (isDarkMode.value) document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            };
            // Init dark mode
            if (isDarkMode.value) document.documentElement.classList.add('dark');

            const buildPublicResultsUrl = () => {
                if (publicResultsUrl.value) return publicResultsUrl.value;
                const slug = String(sessionSlug.value || '').trim();
                if (!slug) return '';
                return `${resultsBase}/${encodeURIComponent(slug)}`;
            };

            const ensureSlug = () => {
                if (sessionSlug.value) return sessionSlug.value;
                const u = String(publicResultsUrl.value || '');
                const m = u.match(/\/results\/([^\/\?#]+)/);
                if (m && m[1]) sessionSlug.value = m[1];
                return sessionSlug.value;
            };

            const copyResultsLink = async () => {
                const url = buildPublicResultsUrl();
                if (!url) {
                    alert('Link results belum tersedia. Finish sesi dulu.');
                    return;
                }
                publicResultsUrl.value = url;
                try {
                    await navigator.clipboard.writeText(url);
                    alert('Link results disalin.');
                } catch (e) {
                    alert(url);
                }
            };

            // 4:5 HTML5 Canvas Sports Finisher Card Generator
            const mediaModalOpen = ref(false);
            const mediaType = ref('card'); // 'card'
            const mediaParticipant = ref(null);
            const mediaBgFile = ref(null);
            const mediaBgImage = ref(null);
            const mediaPreviewUrl = ref('');
            const mediaFile = ref(null);
            const mediaLoading = ref(false);
            const mediaError = ref('');

            const formatTimeHms = (ms) => {
                if (!ms || ms < 0 || isNaN(ms)) return '00:00:00';
                const totalSeconds = Math.floor(ms / 1000);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            };

            const renderFinisherCard = async () => {
                const p = mediaParticipant.value;
                if (!p) return;

                mediaLoading.value = true;
                mediaError.value = '';

                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 1080;
                    canvas.height = 1350;
                    const ctx = canvas.getContext('2d');

                    // 1. Background Fill
                    if (mediaBgImage.value) {
                        const bg = mediaBgImage.value;
                        const imgRatio = bg.width / bg.height;
                        const canvasRatio = 1080 / 1350;
                        let drawW = 1080, drawH = 1350, drawX = 0, drawY = 0;
                        if (imgRatio > canvasRatio) {
                            drawH = 1350;
                            drawW = 1350 * imgRatio;
                            drawX = (1080 - drawW) / 2;
                        } else {
                            drawW = 1080;
                            drawH = 1080 / imgRatio;
                            drawY = (1350 - drawH) / 2;
                        }
                        ctx.drawImage(bg, drawX, drawY, drawW, drawH);

                        const bgGrad = ctx.createLinearGradient(0, 0, 0, 1350);
                        bgGrad.addColorStop(0, 'rgba(15, 23, 42, 0.85)');
                        bgGrad.addColorStop(0.4, 'rgba(15, 23, 42, 0.72)');
                        bgGrad.addColorStop(1, 'rgba(2, 6, 23, 0.96)');
                        ctx.fillStyle = bgGrad;
                        ctx.fillRect(0, 0, 1080, 1350);
                    } else {
                        // Default Energetic Sports Dark Solid Palette
                        const bgGrad = ctx.createLinearGradient(0, 0, 1080, 1350);
                        bgGrad.addColorStop(0, '#090d16');
                        bgGrad.addColorStop(0.5, '#0f172a');
                        bgGrad.addColorStop(1, '#020617');
                        ctx.fillStyle = bgGrad;
                        ctx.fillRect(0, 0, 1080, 1350);

                        // Sport angle geometry grid lines
                        ctx.save();
                        ctx.strokeStyle = 'rgba(79, 70, 229, 0.16)';
                        ctx.lineWidth = 2;
                        for (let x = -500; x < 1500; x += 60) {
                            ctx.beginPath();
                            ctx.moveTo(x, 0);
                            ctx.lineTo(x + 500, 1350);
                            ctx.stroke();
                        }
                        ctx.restore();

                        // Sports ambient lights
                        ctx.save();
                        const glow1 = ctx.createRadialGradient(200, 250, 20, 200, 250, 450);
                        glow1.addColorStop(0, 'rgba(99, 102, 241, 0.28)');
                        glow1.addColorStop(1, 'rgba(99, 102, 241, 0)');
                        ctx.fillStyle = glow1;
                        ctx.fillRect(0, 0, 1080, 700);

                        const glow2 = ctx.createRadialGradient(880, 1100, 20, 880, 1100, 400);
                        glow2.addColorStop(0, 'rgba(16, 185, 129, 0.20)');
                        glow2.addColorStop(1, 'rgba(16, 185, 129, 0)');
                        ctx.fillStyle = glow2;
                        ctx.fillRect(0, 600, 1080, 750);
                        ctx.restore();
                    }

                    // 2. Official Logo Ruang Lari (Top Left)
                    try {
                        const logoImg = new Image();
                        logoImg.crossOrigin = 'anonymous';
                        await new Promise((resolve) => {
                            logoImg.onload = resolve;
                            logoImg.onerror = resolve;
                            logoImg.src = 'https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp';
                        });
                        if (logoImg.width > 0) {
                            const logoH = 75;
                            const logoW = (logoImg.width / logoImg.height) * logoH;
                            ctx.drawImage(logoImg, 60, 60, logoW, logoH);
                        }
                    } catch (err) {}

                    // Top Category Pill (Top Right)
                    const catText = `${raceCategory.value || 'RACE'} • OFFICIAL FINISHER`.toUpperCase();
                    ctx.save();
                    ctx.font = 'bold 22px Inter, sans-serif';
                    const catWidth = ctx.measureText(catText).width + 48;
                    const catX = 1080 - 60 - catWidth;
                    ctx.fillStyle = 'rgba(30, 41, 59, 0.9)';
                    ctx.strokeStyle = '#4f46e5';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.roundRect(catX, 68, catWidth, 52, 14);
                    ctx.fill();
                    ctx.stroke();

                    ctx.fillStyle = '#818cf8';
                    ctx.fillText(catText, catX + 24, 102);
                    ctx.restore();

                    // 3. Race Event Title
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = 'bold 20px Inter, sans-serif';
                    ctx.fillText('OFFICIAL RACE RESULT', 60, 190);

                    ctx.fillStyle = '#ffffff';
                    ctx.font = '900 42px Inter, sans-serif';
                    const displayRaceTitle = raceName.value || 'RUANG LARI RACE CHAMPIONSHIP';
                    ctx.fillText(displayRaceTitle.toUpperCase().slice(0, 32), 60, 245);

                    // Divider
                    const divGrad = ctx.createLinearGradient(60, 0, 1020, 0);
                    divGrad.addColorStop(0, '#4f46e5');
                    divGrad.addColorStop(0.5, '#10b981');
                    divGrad.addColorStop(1, 'transparent');
                    ctx.fillStyle = divGrad;
                    ctx.fillRect(60, 270, 960, 4);

                    // 4. Hero Finisher Card
                    ctx.save();
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.82)';
                    ctx.strokeStyle = 'rgba(71, 85, 105, 0.8)';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.roundRect(60, 310, 960, 425, 24);
                    ctx.fill();
                    ctx.stroke();

                    // BIB Tag
                    ctx.fillStyle = '#4f46e5';
                    ctx.beginPath();
                    ctx.roundRect(100, 350, 200, 56, 12);
                    ctx.fill();
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '900 28px Orbitron, monospace';
                    ctx.textAlign = 'center';
                    ctx.fillText(`BIB #${p.bib}`, 200, 388);

                    // Runner Full Name
                    ctx.textAlign = 'left';
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '900 46px Inter, sans-serif';
                    ctx.fillText(p.name.toUpperCase().slice(0, 26), 100, 465);

                    // Subtitle Official Time
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = 'bold 22px Inter, sans-serif';
                    ctx.fillText('OFFICIAL FINISH TIME (HH:MM:SS)', 100, 520);

                    // TIME ONLY HH:MM:SS (NO MILLISECONDS)
                    const timeHms = formatTimeHms(p.totalTime);
                    ctx.fillStyle = '#f59e0b';
                    ctx.font = '900 84px Orbitron, monospace';
                    ctx.fillText(timeHms, 100, 620);
                    ctx.restore();

                    // 5. 3-Stat Metric Cards Grid
                    const cardY = 765;
                    const cardW = 300;
                    const cardH = 220;
                    const rankIndex = sortedResults.value.findIndex(r => String(r.bib) === String(p.bib));
                    const rankStr = rankIndex >= 0 ? `#${rankIndex + 1}` : '-';
                    const paceStr = formatPace(p);
                    const lapsCount = p.laps ? p.laps.length : 0;
                    const distStr = raceDistanceKm.value ? `${raceDistanceKm.value} KM` : `${lapsCount} Laps`;

                    const metrics = [
                        { label: 'PERINGKAT', val: rankStr, color: '#f59e0b', sub: `DARI ${participants.value.length} PELARI` },
                        { label: 'AVERAGE PACE', val: paceStr, color: '#10b981', sub: 'MENIT / KM' },
                        { label: 'TOTAL PUTARAN', val: `${lapsCount} Laps`, color: '#6366f1', sub: distStr },
                    ];

                    metrics.forEach((m, i) => {
                        const cX = 60 + i * (cardW + 30);
                        ctx.save();
                        ctx.fillStyle = 'rgba(15, 23, 42, 0.82)';
                        ctx.strokeStyle = 'rgba(71, 85, 105, 0.8)';
                        ctx.lineWidth = 2;
                        ctx.beginPath();
                        ctx.roundRect(cX, cardY, cardW, cardH, 20);
                        ctx.fill();
                        ctx.stroke();

                        ctx.fillStyle = '#94a3b8';
                        ctx.font = 'bold 20px Inter, sans-serif';
                        ctx.fillText(m.label, cX + 24, cardY + 46);

                        ctx.fillStyle = m.color;
                        ctx.font = '900 48px Orbitron, monospace';
                        ctx.fillText(m.val, cX + 24, cardY + 118);

                        ctx.fillStyle = '#64748b';
                        ctx.font = 'bold 18px Inter, sans-serif';
                        ctx.fillText(m.sub, cX + 24, cardY + 172);
                        ctx.restore();
                    });

                    // 6. Footer Verified Badge
                    const footY = 1025;
                    ctx.save();
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.65)';
                    ctx.strokeStyle = 'rgba(51, 65, 85, 0.6)';
                    ctx.beginPath();
                    ctx.roundRect(60, footY, 960, 250, 20);
                    ctx.fill();
                    ctx.stroke();

                    ctx.fillStyle = '#e2e8f0';
                    ctx.font = 'bold 26px Inter, sans-serif';
                    ctx.fillText('RUANG LARI RACE TIMING SYSTEM', 100, footY + 55);

                    ctx.fillStyle = '#94a3b8';
                    ctx.font = '19px Inter, sans-serif';
                    ctx.fillText('Hasil ini diverifikasi secara otomatis oleh sistem AI & Digital Timing Gate Ruang Lari.', 100, footY + 95);
                    ctx.fillText(`ID Sesi: ${sessionSlug.value || currentSessionId.value || 'LOCAL-TIMING'} • Tanggal: ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`, 100, footY + 130);

                    ctx.fillStyle = '#10b981';
                    ctx.font = 'bold 22px Inter, sans-serif';
                    ctx.fillText('✓ VERIFIED OFFICIAL FINISHER RESULT • RUANGLARI.COM', 100, footY + 185);
                    ctx.restore();

                    // Convert to Blob & Preview Data URL
                    canvas.toBlob((blob) => {
                        if (!blob) return;
                        if (mediaPreviewUrl.value && mediaPreviewUrl.value.startsWith('blob:')) {
                            try { URL.revokeObjectURL(mediaPreviewUrl.value); } catch (_) {}
                        }
                        const url = URL.createObjectURL(blob);
                        mediaPreviewUrl.value = url;
                        mediaFile.value = new File([blob], `finisher-card-BIB-${p.bib}.png`, { type: 'image/png' });
                    }, 'image/png');
                } catch (e) {
                    mediaError.value = e?.message || 'Gagal merender kartu finisher.';
                } finally {
                    mediaLoading.value = false;
                }
            };

            const openMediaModal = (type, p) => {
                mediaType.value = type || 'card';
                mediaParticipant.value = p;
                mediaBgFile.value = null;
                mediaBgImage.value = null;
                mediaError.value = '';
                mediaFile.value = null;
                if (mediaPreviewUrl.value && mediaPreviewUrl.value.startsWith('blob:')) {
                    try { URL.revokeObjectURL(mediaPreviewUrl.value); } catch (e) {}
                }
                mediaPreviewUrl.value = '';
                mediaModalOpen.value = true;
                nextTick(renderFinisherCard);
            };

            const closeMediaModal = () => {
                mediaModalOpen.value = false;
                if (mediaPreviewUrl.value && mediaPreviewUrl.value.startsWith('blob:')) {
                    try { URL.revokeObjectURL(mediaPreviewUrl.value); } catch (e) {}
                }
                mediaPreviewUrl.value = '';
                mediaBgImage.value = null;
                mediaBgFile.value = null;
            };

            const onMediaBgChange = (e) => {
                const file = e?.target?.files?.[0];
                if (!file) {
                    mediaBgImage.value = null;
                    renderFinisherCard();
                    return;
                }
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = new Image();
                    img.onload = () => {
                        mediaBgImage.value = img;
                        renderFinisherCard();
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            };

            const downloadMediaCard = () => {
                if (!mediaPreviewUrl.value) return;
                const p = mediaParticipant.value;
                const a = document.createElement('a');
                a.href = mediaPreviewUrl.value;
                a.download = `finisher-card-BIB-${p?.bib || 'runner'}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            };

            const shareMedia = async () => {
                if (!mediaFile.value) return;
                if (!navigator.share || !navigator.canShare || !navigator.canShare({ files: [mediaFile.value] })) {
                    downloadMediaCard();
                    return;
                }
                try {
                    await navigator.share({
                        title: `${raceName.value || 'Ruang Lari Race'} - Finisher Card`,
                        text: `Hasil lari resmi ${mediaParticipant.value?.name || ''} (BIB #${mediaParticipant.value?.bib || ''})`,
                        files: [mediaFile.value],
                    });
                } catch (e) {}
            };




            return {
                currentView, isSessionHost, raceName, existingRaces, selectExistingRace, raceLogoPreviewUrl, raceLogoFileName, onLogoChange,
                raceCategory, categories, raceDistanceKm, publicResultsUrl, sessionSlug, newName, newBib, newPredictedHH, newPredictedMM, newPredictedSS, mobileMenuOpen, inputName, inputBib,
                participants, timer, camera, formattedTime, certificatesByBib,
                focusName, addParticipant, removeParticipant, goToBibs, printBibs,
                downloadCsvSample, onCsvUpload, importCsvText,
                startRace, pauseRace, finishRace, resetRace, toggleScanner, captureScan,
                activeParticipants, sortedResults, dnfParticipants,
                raceSearchQuery, raceStatusFilter, raceViewMode, racePageSize, raceCurrentPage,
                filteredRaceParticipants, raceTotalPages, paginatedRaceParticipants,
                prevRacePage, nextRacePage, setRacePage,
                recordLap, markDNF, getLastLapTime, getInitials, formatTime, formatTimeHms,
                formatPace, getDeltaMs, formatDelta, getPointsInfo,
                isAuthenticated,
                isDarkMode, toggleDarkMode, playBeep,
                copyResultsLink,
                mediaModalOpen, mediaType, mediaParticipant, mediaLoading, mediaError, mediaPreviewUrl, mediaFile,
                openMediaModal, closeMediaModal, onMediaBgChange, renderFinisherCard, downloadMediaCard, shareMedia,
                canNativeShare,
                eoEvents, eoEventsLoading, selectedEoEventId, selectedEoCategoryId, importingEoParticipants, selectedEoEvent,
                eoEventSearchQuery, eoDropdownOpen, eoAutocompleteContainer, filteredEoEvents, onEoSearchInput, selectEoEvent, clearSelectedEoEvent,
                onEoEventChange, importEoEventParticipants,
                cameraSettingsOpen, raceSettings, sensorSettingsOpen,
                manualBibInput, lockedBibPrefix, detectedBibPrefixes, ambiguousRunners, autoSubmitDigits, liveMatchedRunner, onManualBibKeyup,
                quickBibSuggestions, selectSuggestion,
                manualValidationAlert, recordManualBib, dismissAlert, setDetectorPreset, currentDetectorPreset, showDuplicateAlert, playBuzzer,
                pendingUnregisteredBib, confirmAddUnregisteredParticipant, cancelAddUnregisteredParticipant,
                tvDisplayOpen, tvLatestFinisher, finishedCount, runningCount, dnfCount, top5Results, openTvDisplay, closeTvDisplay, toggleTvFullscreen, isFullscreen, copyTvDisplayUrl,
                tvTimerFont, tvTimerColor, tvTimerSizeScale, tvTimerParts, tvSettingsOpen, tvResultsQrUrl, tvResultsShortUrl, generateTvResultsQrCode,
                newFacePhoto, newFacePhotos, newFaceDescriptors, faceEnrollStep, enrolledAngleCount,
                newFaceProcessing, faceModelLoading, faceCaptureModalOpen,
                openFaceCaptureModal, closeFaceCaptureModal, snapFaceAngle, onFacePhotoUpload, clearNewFacePhoto,
                liveFinishFeed, assignBibModalOpen, selectedUnassignedFinish,
                handleCanvasMouseDown, handleCanvasMouseMove, handleCanvasMouseUp,
                handleCanvasTouchStart, handleCanvasTouchMove, handleCanvasTouchEnd,
                setLinePreset, switchCameraDevice, switchCameraMode,
                sessionRoomInput, sessionSyncActive, sessionSyncLastUpdated, sessionSyncError,
                copySessionShareUrl, joinLiveSession, startLiveSyncPolling,
                initializingSession, initializeRaceSession,
                openAssignBibModal, confirmAssignBib,
                bibScan, onBibSampleUpload, getBibRuntimeProfile, activeBibPattern,
                isRecordingVideo, recordingDuration, formatRecordingDuration, recordedVideoUrl, recordedVideoBlob,
                videoReviewModalOpen, videoPlaybackRate,
                startVideoRecording, stopVideoRecording, toggleVideoRecording,
                openVideoReviewModal, closeVideoReviewModal, setVideoSpeed, stepVideoFrame, downloadRecordedVideo,
            };


        }
    }).mount('#app');
</script>
</body>
</html>
