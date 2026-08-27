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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&family=Oswald:wght@500;700;800&display=swap');

        body { font-family: 'Inter', sans-serif; }
        .font-mono-numbers { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
        .font-oswald { font-family: 'Oswald', sans-serif; }

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
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100">

<div id="app" :class="{'dark': isDarkMode}" v-cloak>

    <!-- TV / JUMBOTRON BROADCAST FULLSCREEN OVERLAY (ADMIN 1) -->
    <div v-if="tvDisplayOpen" class="fixed inset-0 z-[9999] bg-black text-white flex flex-col justify-between p-6 sm:p-10 select-none overflow-hidden font-sans" style="background-color: #000000 !important; color: #ffffff !important;">
        <!-- Top Status Bar -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700 flex items-center justify-center font-black text-lg text-white">
                    <span v-if="!raceLogoPreviewUrl">RL</span>
                    <img v-else :src="raceLogoPreviewUrl" class="w-full h-full object-contain rounded-2xl" alt="Logo">
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Live Race Display</div>
                    <div class="text-2xl sm:text-3xl font-black text-white uppercase tracking-tight truncate max-w-xl">
                        @{{ raceName || 'Ruang Lari Official Race' }}
                    </div>
                </div>
                <div class="ml-2 px-3.5 py-1 rounded-xl bg-indigo-600 text-white font-oswald text-lg font-black uppercase">
                    @{{ raceCategory }}
                </div>
            </div>

            <!-- Mini Live Stat Pills -->
            <div class="flex items-center gap-3">
                <div class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-center">
                    <div class="text-[10px] font-bold uppercase text-slate-400">Total Peserta</div>
                    <div class="text-xl font-black text-white">@{{ participants.length }}</div>
                </div>
                <div class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-center">
                    <div class="text-[10px] font-bold uppercase text-emerald-400">Sudah Finish</div>
                    <div class="text-xl font-black text-emerald-400">@{{ finishedCount }}</div>
                </div>
                <div class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-center">
                    <div class="text-[10px] font-bold uppercase text-indigo-400">On Track</div>
                    <div class="text-xl font-black text-indigo-400">@{{ runningCount }}</div>
                </div>
                <div v-if="dnfCount > 0" class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-center">
                    <div class="text-[10px] font-bold uppercase text-red-400">DNF</div>
                    <div class="text-xl font-black text-red-400">@{{ dnfCount }}</div>
                </div>
                <button type="button" @click="closeTvDisplay" class="ml-3 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-2 border border-slate-700 transition" title="Tutup TV Mode (Esc)">
                    <i class="fa-solid fa-compress text-sm"></i>
                    <span>Tutup (Esc)</span>
                </button>
            </div>
        </div>

        <!-- Center: Giant Race Clock + Live Finisher Hero Flash -->
        <div class="flex-1 flex flex-col items-center justify-center my-auto py-4 sm:py-6 relative w-full overflow-hidden">
            
            <!-- Main Giant Clock -->
            <div class="text-center w-full max-w-full px-2 sm:px-4">
                <div class="text-xs sm:text-sm md:text-base font-bold uppercase tracking-[0.35em] text-slate-400 mb-2 sm:mb-4">
                    OFFICIAL RACE TIME
                </div>
                <div class="font-mono-numbers font-black text-white tracking-tight sm:tracking-normal md:tracking-wider leading-none select-none" style="font-size: clamp(4.2rem, 13vw, 17rem); line-height: 0.95;">
                    @{{ formattedTime }}
                </div>
            </div>

            <!-- Realtime Finisher Flash Hero Overlay (appears on crossing) -->
            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                <div v-if="tvLatestFinisher" class="absolute inset-x-4 max-w-3xl mx-auto p-6 rounded-3xl bg-slate-900 border-2 border-emerald-500 shadow-2xl text-center animate-fade-in z-20" style="background-color: #0f172a !important;">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2 border border-emerald-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        BARU SAJA FINISH
                    </div>
                    <div class="text-3xl sm:text-5xl font-black text-white uppercase tracking-tight">
                        <span class="text-indigo-400 font-oswald mr-2">#@{{ tvLatestFinisher.bib }}</span>
                        @{{ tvLatestFinisher.name }}
                    </div>
                    <div class="mt-3 flex items-center justify-center gap-6 text-sm sm:text-lg font-mono">
                        <span class="text-slate-200">Waktu: <strong class="text-emerald-400">@{{ tvLatestFinisher.time }}</strong></span>
                        <span v-if="tvLatestFinisher.rank" class="text-slate-200">Peringkat: <strong class="text-white">#@{{ tvLatestFinisher.rank }}</strong></span>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Bottom: Top 5 Live Leaderboard Strip -->
        <div class="border-t border-slate-800 pt-5">
            <div class="flex items-center justify-between mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                <div class="flex items-center gap-2">
                    <span>Top 5 Finisher Leaderboard</span>
                </div>
                <div class="text-[11px] font-mono text-slate-400">Live Auto-Update</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <div v-for="(p, idx) in top5Results" :key="p.id" class="bg-slate-900 border border-slate-800 p-3 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm shrink-0"
                        :class="{
                            'bg-amber-400 text-slate-950': idx === 0,
                            'bg-slate-300 text-slate-950': idx === 1,
                            'bg-amber-700 text-white': idx === 2,
                            'bg-slate-800 text-slate-300': idx > 2
                        }">
                        @{{ idx + 1 }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-white text-xs truncate">
                            <span class="text-indigo-400 font-oswald mr-1">#@{{ p.bib }}</span>
                            @{{ p.name }}
                        </div>
                        <div class="text-[11px] font-mono text-emerald-400 font-bold mt-0.5">
                            @{{ formatTime(p.totalTime) }}
                        </div>
                    </div>
                </div>
                <!-- Empty Placeholder if < 5 finishers -->
                <div v-for="i in Math.max(0, 5 - top5Results.length)" :key="'empty-'+i" class="bg-slate-900 border border-slate-800 p-3 rounded-2xl flex items-center gap-3 opacity-40">
                    <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-bold">
                        @{{ top5Results.length + i }}
                    </div>
                    <div class="text-xs text-slate-400 font-mono">Menunggu Finisher...</div>
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
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
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
                    <div v-if="sessionSyncActive" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-xs font-bold border border-emerald-300 dark:border-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Live Sync Aktif</span>
                        <span class="text-[10px] opacity-70">(@{{ sessionSyncLastUpdated }})</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left: Current Active Room & Share Link -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Sesi Lomba Saat Ini:</div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 font-mono font-black text-base px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white truncate">
                                @{{ sessionSlug || currentSessionId || 'Belum ada sesi aktif' }}
                            </div>
                            <button type="button" @click="copySessionShareUrl" :disabled="!sessionSlug && !currentSessionId" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-sm transition shrink-0 flex items-center gap-1.5">
                                <i class="fa-solid fa-copy"></i>
                                <span>Salin Link Sesi</span>
                            </button>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            Bagikan link sesi ini ke laptop TV atau admin lain agar jam dan data finisher tersinkronisasi otomatis.
                        </div>
                    </div>

                    <!-- Right: Join Other Session by Room Code / URL -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Gabung ke Sesi Lain (Satellite Admin):</div>
                        <form @submit.prevent="joinLiveSession(sessionRoomInput)" class="flex items-center gap-2">
                            <input v-model="sessionRoomInput" type="text" placeholder="Masukkan Kode / Slug Sesi..." 
                                class="flex-1 px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl font-mono text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition shrink-0 flex items-center gap-1.5 border border-slate-700">
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
            <div v-if="isAuthenticated" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
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
                    <button type="button" @click="importEoEventParticipants" :disabled="importingEoParticipants" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold text-xs flex items-center gap-2 transition-colors">
                        <i v-if="importingEoParticipants" class="fa-solid fa-circle-notch fa-spin"></i>
                        <i v-else class="fa-solid fa-file-import"></i>
                        Impor Peserta ke Race Master
                    </button>
                </div>
            </div>

            <div v-if="existingRaces.length > 0" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <h2 class="text-lg font-bold mb-4 text-slate-900 dark:text-white">Load Existing Race</h2>
                 <select @change="selectExistingRace($event.target.value)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                    <option value="">-- Pilih Race Sebelumnya --</option>
                    <option v-for="r in existingRaces" :value="r.id">@{{ r.name }} (@{{ r.created_at }})</option>
                </select>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <h2 class="text-lg font-bold mb-4 text-slate-900 dark:text-white">1. Konfigurasi Race</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Nama Race</label>
                        <input v-model="raceName" placeholder="Minimal 3 karakter" type="text" maxlength="100" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                        <div class="mt-1 text-xs text-slate-400">Disimpan ke database saat Start Race.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Logo Race (PNG/JPG, max 2MB, min 200x200)</label>
                        <input @change="onLogoChange" type="file" accept="image/png,image/jpeg" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                        <div v-if="raceLogoPreviewUrl" class="mt-2 flex items-center gap-3">
                            <img :src="raceLogoPreviewUrl" class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700" alt="Logo preview">
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">@{{ raceLogoFileName }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Kategori Jarak</label>
                        <select v-model="raceCategory" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                            <option v-for="cat in categories" :value="cat">@{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1 dark:text-slate-400">Jarak (KM)</label>
                        <input v-model="raceDistanceKm" placeholder="Contoh: 5, 10, 21.1, 42.195" type="number" step="0.001" min="0.1" max="999.999" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                        <div class="mt-1 text-xs text-slate-400">Dipakai untuk hitung pace/kecepatan pada poster.</div>
                    </div>
                    <div class="flex items-end">
                        <div class="text-sm text-slate-500 bg-slate-50 p-3 rounded-xl w-full dark:bg-slate-900 dark:text-slate-400 transition-colors">
                            Total Peserta: <span class="font-bold text-indigo-600 text-lg dark:text-indigo-400">@{{ participants.length }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">2. Tambah Peserta & Biometrik Wajah</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan peserta secara manual satu per satu, impor massal via file CSV, atau gunakan biometrik wajah AI.</p>
                    </div>
                </div>

                <!-- CSV Batch Import Action Box -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-file-csv text-indigo-600 dark:text-indigo-400 text-base"></i>
                            <span>Impor Massal Peserta via CSV</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Punya daftar peserta di Excel / Spreadsheet? Download format CSV, isi kolomnya, lalu upload langsung ke sini.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <button type="button" @click="downloadCsvSample" class="px-3.5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 transition border border-slate-300 dark:border-slate-700" title="Download Template CSV">
                            <i class="fa-solid fa-download text-xs"></i>
                            <span>Download Sample CSV</span>
                        </button>
                        <label class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-1.5 cursor-pointer transition shadow-sm" title="Upload File CSV Peserta">
                            <i class="fa-solid fa-file-import text-xs"></i>
                            <span>Upload File CSV</span>
                            <input type="file" accept=".csv,text/csv" @change="onCsvUpload" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Face Photo Capture & Multi-Angle Biometric Enrollment Bar -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-full border-2 border-indigo-500 overflow-hidden bg-slate-200 dark:bg-slate-800 flex items-center justify-center shrink-0 shadow-inner relative">
                            <img v-if="newFacePhoto" :src="newFacePhoto" class="w-full h-full object-cover" alt="Avatar">
                            <i v-else class="fa-solid fa-user text-slate-400 text-xl"></i>
                            <span v-if="enrolledAngleCount > 0" class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] font-black flex items-center justify-center border border-white dark:border-slate-900">
                                @{{ enrolledAngleCount }}
                            </span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
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
                                <i class="fa-solid fa-circle-check"></i> Terdaftar @{{ enrolledAngleCount }} Sudut (Depan, Kanan, Kiri) - Siap Deteksi
                            </div>
                            <div v-else class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Ambil 3 sudut (depan, serong kanan, serong kiri) agar deteksi saat finish tetap akurat dari samping.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="openFaceCaptureModal('front')" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fa-solid fa-camera"></i> Ambil 3 Sudut (Kamera)
                        </button>
                        <label class="px-3.5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs flex items-center gap-1.5 cursor-pointer transition">
                            <i class="fa-solid fa-upload"></i> Upload
                            <input type="file" accept="image/*" @change="onFacePhotoUpload" class="hidden">
                        </label>
                        <button v-if="newFacePhoto" type="button" @click="clearNewFacePhoto" class="px-2.5 py-2 rounded-xl text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 text-xs transition" title="Hapus Foto">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-3 mb-6">
                    <input v-model="newBib" @keyup.enter="focusName" ref="inputBib" placeholder="No. BIB (Contoh: 101)" type="number" class="w-full md:w-1/4 p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono-numbers text-lg dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-colors">
                    <input v-model="newName" @keyup.enter="addParticipant" ref="inputName" placeholder="Nama Peserta" type="text" class="w-full md:w-2/4 p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-lg dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-colors">
                    <div class="w-full md:w-1/4 flex gap-1 items-center">
                        <input v-model="newPredictedHH" @keyup.enter="addParticipant" placeholder="HH" type="number" min="0" max="99" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono-numbers text-lg text-center dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-colors" title="Jam">
                        <span class="text-slate-400 font-bold">:</span>
                        <input v-model="newPredictedMM" @keyup.enter="addParticipant" placeholder="MM" type="number" min="0" max="59" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono-numbers text-lg text-center dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-colors" title="Menit">
                        <span class="text-slate-400 font-bold">:</span>
                        <input v-model="newPredictedSS" @keyup.enter="addParticipant" placeholder="SS" type="number" min="0" max="59" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono-numbers text-lg text-center dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-colors" title="Detik">
                    </div>
                    <button @click="addParticipant" class="w-full md:w-1/4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-200 dark:shadow-none">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah
                    </button>
                </div>

                <div class="overflow-auto max-h-96 rounded-xl border border-slate-100 dark:border-slate-700">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0 z-10">
                            <tr class="text-slate-400 text-sm border-b border-slate-100 dark:border-slate-700">
                                <th class="p-3 font-medium">Foto / Face AI</th>
                                <th class="p-3 font-medium">BIB</th>
                                <th class="p-3 font-medium">Nama</th>
                                <th class="p-3 font-medium">Prediksi</th>
                                <th class="p-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
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
                                <td class="p-3 font-oswald font-bold text-xl dark:text-white">@{{ p.bib }}</td>
                                <td class="p-3 font-medium dark:text-slate-200">@{{ p.name }}</td>
                                <td class="p-3 font-mono text-sm text-slate-600 dark:text-slate-400">@{{ p.predictedTimeMs ? formatTime(p.predictedTimeMs) : '-' }}</td>
                                <td class="p-3">
                                    <button @click="removeParticipant(index)" class="text-red-400 hover:text-red-600 transition-colors"><i class="fa-solid fa-trash"></i></button>
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
                <button @click="goToBibs" class="bg-slate-800 text-white px-6 py-3 rounded-xl font-medium hover:bg-black transition">
                    Lanjut: Generate BIB <i class="fa-solid fa-arrow-right ml-2"></i>
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
            <div class="bg-white dark:bg-[#0c121e] text-slate-900 dark:text-white rounded-2xl p-5 border-2 border-slate-200 dark:border-slate-800 shadow-sm sticky top-[70px] z-40 flex flex-col md:flex-row justify-between items-center gap-4 transition-colors">
                <div class="text-center md:text-left">
                    <div class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">Official Race Clock</div>
                    <div class="font-mono-numbers text-5xl md:text-6xl font-black text-slate-950 dark:text-white tracking-wider">
                        @{{ formattedTime }}
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap justify-center md:justify-end">
                    <button v-if="!timer.running" @click="startRace" 
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-sm" :title="timer.elapsed > 0 ? 'Resume Timer' : 'Start Timer'">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>@{{ timer.elapsed > 0 ? 'Resume Timer' : 'Start Timer' }}</span>
                    </button>
                    <button v-if="timer.running" @click="pauseRace" 
                        class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-sm" title="Pause Timer">
                        <i class="fa-solid fa-pause text-xs"></i>
                        <span>Pause</span>
                    </button>
                    <button v-if="timer.elapsed > 0" @click="finishRace" 
                        class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-sm" title="Finish Sesi">
                        <i class="fa-solid fa-flag-checkered text-xs"></i>
                        <span>Finish Sesi</span>
                    </button>
                    <button @click="resetRace" 
                        class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 font-bold text-xs flex items-center gap-1.5 transition" title="Reset Total Timer">
                        <i class="fa-solid fa-rotate-right text-xs"></i>
                        <span>Reset</span>
                    </button>
                    <button @click="toggleScanner" 
                        :class="camera.active ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'" 
                        class="px-3.5 py-2.5 rounded-xl border font-bold text-xs flex items-center gap-1.5 transition" title="Buka/Tutup Kamera AI">
                        <i class="fa-solid fa-camera text-xs"></i>
                        <span>@{{ camera.active ? 'Tutup Kamera' : 'Buka Kamera' }}</span>
                    </button>
                    <button @click="openTvDisplay" 
                        class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm flex items-center gap-2 transition shadow-sm" title="Tampilkan Layar TV Fullscreen (Admin 1)">
                        <i class="fa-solid fa-tv text-xs"></i>
                        <span>Layar TV</span>
                    </button>
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
                            BIB: #@{{ manualValidationAlert.bib }} <span v-if="manualValidationAlert.name">• Nama: @{{ manualValidationAlert.name }}</span> <span v-if="manualValidationAlert.time">• Waktu Tercatat: @{{ manualValidationAlert.time }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="dismissAlert" class="text-slate-400 hover:text-white p-1 text-base shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Quick Manual BIB Timing & Rapid Entry Engine Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm transition-colors space-y-3">
                
                <!-- Rapid Spotter Helper Controls: Prefix Lock & Auto-Submit -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100 dark:border-slate-800 text-xs">
                    <!-- Prefix Quick Pills (Auto-detected & Manual) -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-slate-500 dark:text-slate-400 uppercase text-[11px]">Kunci Prefix BIB:</span>
                        <button type="button" @click="lockedBibPrefix = ''"
                            :class="lockedBibPrefix === '' ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-950 font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700">
                            Tanpa Prefix
                        </button>
                        
                        <!-- Auto-detected Prefixes from Registered Participants (e.g. M, F, 10K) -->
                        <template v-if="detectedBibPrefixes.length > 0">
                            <button v-for="pf in detectedBibPrefixes" :key="pf" type="button" @click="lockedBibPrefix = (lockedBibPrefix === pf ? '' : pf)"
                                :class="lockedBibPrefix === pf ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700 font-mono font-bold">
                                Prefix @{{ pf }}
                            </button>
                        </template>

                        <!-- Fallback Standard Category Pills if no alphabetic prefixes detected -->
                        <template v-else>
                            <button v-for="pf in ['M', 'F', '1', '2']" :key="pf" type="button" @click="lockedBibPrefix = (lockedBibPrefix === pf ? '' : pf)"
                                :class="lockedBibPrefix === pf ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700 font-mono font-bold">
                                Prefix @{{ pf }}
                            </button>
                        </template>
                    </div>

                    <!-- Auto-Submit on Fixed Digits -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-slate-500 dark:text-slate-400 uppercase text-[11px]">Auto-Enter Saat:</span>
                        <button type="button" @click="autoSubmitDigits = 0"
                            :class="autoSubmitDigits === 0 ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-950 font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700">
                            Tekan Enter
                        </button>
                        <button type="button" @click="autoSubmitDigits = 3"
                            :class="autoSubmitDigits === 3 ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700 font-mono font-bold" title="Langsung catat saat digit ke-3 terketik (Zero-Enter)">
                            3 Digit
                        </button>
                        <button type="button" @click="autoSubmitDigits = 4"
                            :class="autoSubmitDigits === 4 ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700 font-mono font-bold" title="Langsung catat saat digit ke-4 terketik (Zero-Enter)">
                            4 Digit
                        </button>
                        <button type="button" @click="autoSubmitDigits = 5"
                            :class="autoSubmitDigits === 5 ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition border border-transparent dark:border-slate-700 font-mono font-bold" title="Langsung catat saat digit ke-5 terketik (Zero-Enter)">
                            5 Digit
                        </button>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <!-- Left: Manual BIB Entry Input with Inline Compact Prefix -->
                    <div class="flex-1">
                        <form @submit.prevent="recordManualBib" class="flex items-center gap-2">
                            
                            <!-- Compact Inline Prefix Input -->
                            <div class="w-20 sm:w-24 shrink-0 relative">
                                <input 
                                    v-model="lockedBibPrefix" 
                                    @input="lockedBibPrefix = lockedBibPrefix.toUpperCase()"
                                    type="text" 
                                    placeholder="Prefix" 
                                    maxlength="6"
                                    title="Prefix BIB (misal M, F, 10K, dll)"
                                    class="w-full px-2.5 py-2.5 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white font-bold font-mono text-sm uppercase rounded-xl border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none placeholder:text-slate-400 placeholder:font-sans placeholder:text-xs text-center transition"
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

                            <!-- Main BIB Number Input -->
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-bold">#</span>
                                <input id="manualBibInputEl" v-model="manualBibInput" @keyup="onManualBibKeyup" type="text" autocomplete="off"
                                    :placeholder="lockedBibPrefix ? `Ketik nomor saja (contoh: 1001 untuk #${lockedBibPrefix}-1001)...` : 'Ketik No. BIB (bisa rombongan: 1001 1002 1003)...'" 
                                    class="w-full pl-8 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white font-bold font-mono text-base rounded-xl border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none placeholder:text-slate-400 placeholder:font-normal placeholder:font-sans transition">
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" :disabled="!timer.running && timer.elapsed === 0" 
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow transition shrink-0 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-stopwatch"></i>
                                <span>Catat Finish (Enter)</span>
                            </button>
                        </form>

                        <!-- Live Runner Match Preview & Ambiguity Warning -->
                        <div v-if="liveMatchedRunner" class="mt-1.5 flex items-center gap-2 text-xs font-mono">
                            <span class="text-slate-400">Cocok:</span>
                            <span class="font-bold text-slate-900 dark:text-white">#@{{ liveMatchedRunner.bib }} @{{ liveMatchedRunner.name }}</span>
                            <span :class="liveMatchedRunner.status === 'finished' ? 'text-amber-500 font-bold' : 'text-emerald-500 font-bold'">
                                (@{{ liveMatchedRunner.status === 'finished' ? 'Sudah Finish: ' + formatTime(liveMatchedRunner.totalTime) : 'Sedang Berlari' }})
                            </span>
                        </div>
                        <div v-else-if="ambiguousRunners && ambiguousRunners.length > 1" class="mt-1.5 flex items-center gap-2 text-xs text-amber-500 font-mono">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Ditemukan @{{ ambiguousRunners.length }} pelari dengan nomor tersebut (@{{ ambiguousRunners.map(r => '#' + r.bib + ' ' + r.name).join(', ') }}). Silakan pilih Prefix di sebelah kiri.</span>
                        </div>
                    </div>

                    <!-- Right: Detector Method Quick Preset Switcher -->
                    <div class="flex items-center gap-1.5 flex-wrap shrink-0">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mr-1">Metode Sensor:</span>
                        <button type="button" @click="setDetectorPreset('all')" 
                            :class="currentDetectorPreset === 'all' ? 'bg-indigo-600 text-white font-bold shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs transition border border-transparent dark:border-slate-700">
                            Semua (Hybrid)
                        </button>
                        <button type="button" @click="setDetectorPreset('qr')" 
                            :class="currentDetectorPreset === 'qr' ? 'bg-indigo-600 text-white font-bold shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs transition border border-transparent dark:border-slate-700">
                            QR Code
                        </button>
                        <button type="button" @click="setDetectorPreset('ocr')" 
                            :class="currentDetectorPreset === 'ocr' ? 'bg-indigo-600 text-white font-bold shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs transition border border-transparent dark:border-slate-700">
                            Nomor BIB
                        </button>
                        <button type="button" @click="setDetectorPreset('face')" 
                            :class="currentDetectorPreset === 'face' ? 'bg-indigo-600 text-white font-bold shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs transition border border-transparent dark:border-slate-700">
                            Face AI
                        </button>
                    </div>
                </div>

                <!-- Custom Checkboxes Row for Granular Control -->
                <div class="flex items-center gap-4 flex-wrap pt-2 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                    <span class="font-bold text-[11px] uppercase text-slate-400">Aktifkan Sensor:</span>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium">
                        <input type="checkbox" v-model="raceSettings.enableQr" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>QR Code Scanner</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium">
                        <input type="checkbox" v-model="raceSettings.enableOcr" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>OCR Angka BIB Dada</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium">
                        <input type="checkbox" v-model="raceSettings.enableFaceAi" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>Face AI Recognition</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium ml-auto">
                        <input type="checkbox" v-model="raceSettings.enableBeep" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>Audio Beep</span>
                    </label>
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
                            </select>

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
                                <span>AI Face Recognition (Biometrik Wajah)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                <input type="checkbox" v-model="raceSettings.enableOcr" class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span>OCR Deteksi Angka BIB Dada</span>
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
                    <div id="reader" class="w-full" :class="{'hidden': camera.mode === 'ai_line'}"></div>

                    <!-- Line Dragging Tip Badge -->
                    <div v-if="camera.mode === 'ai_line'" class="absolute bottom-2 left-2 z-20 px-2.5 py-1 rounded bg-black/80 border border-slate-700 text-[11px] text-slate-300 font-medium pointer-events-none">
                        Tarik titik A atau B untuk menyesuaikan posisi Garis Finish
                    </div>

                    <!-- Status Notification Message -->
                    <div v-if="camera.lastScanMsg" class="absolute top-2 right-2 z-20 px-3 py-1.5 rounded-lg bg-slate-950/90 border border-slate-700 text-emerald-400 font-mono text-xs font-bold shadow-lg">
                        @{{ camera.lastScanMsg }}
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
                                
                                <button v-if="!item.bib" type="button" @click="openAssignBibModal(item)" class="mt-1 px-2 py-0.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] transition-colors w-full">
                                    Tetapkan BIB
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

        <div v-if="currentView === 'results'" class="animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold dark:text-white">Hasil Race: @{{ raceCategory }}</h2>
                <div class="flex gap-2 no-print flex-wrap justify-center">
                    <button @click="currentView = 'race'" class="bg-slate-200 text-slate-800 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600 transition-colors">Kembali ke Timer</button>
                    <button v-if="publicResultsUrl || sessionSlug" @click="copyResultsLink" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-black dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">Share Link</button>
                    <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">Print Hasil</button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden dark:bg-slate-800 dark:border-slate-700 transition-colors">
                <div class="overflow-x-auto">
                    <!-- Mobile Stack View -->
                    <div class="md:hidden space-y-4 p-4">
                        <div v-for="(p, idx) in sortedResults" :key="p.id" class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                     <span v-if="p.status === 'finished'" :class="{'text-amber-500 font-black': idx===0, 'text-slate-500 dark:text-slate-400': idx > 0}" class="font-bold text-lg">#@{{ idx + 1 }}</span>
                                     <span v-else class="text-slate-400">-</span>
                                     <div>
                                         <div class="font-oswald font-bold text-xl text-slate-900 dark:text-white">@{{ p.bib }}</div>
                                         <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">@{{ p.name }}</div>
                                     </div>
                                </div>
                                <div class="text-right">
                                    <span v-if="p.status === 'dnf'" class="bg-red-100 text-red-700 text-xs px-2.5 py-1 rounded-lg font-bold dark:bg-red-950 dark:text-red-300 dark:border dark:border-red-800">DNF</span>
                                    <span v-else-if="p.status === 'finished'" class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-lg font-bold dark:bg-emerald-950 dark:text-emerald-300 dark:border dark:border-emerald-800">FINISH</span>
                                    <span v-else class="text-slate-400 text-xs dark:text-slate-500 font-bold">RUNNING</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-2 py-3 border-t border-slate-200 dark:border-slate-800 mt-2">
                                <div class="text-center">
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">Laps</div>
                                    <div class="font-bold text-slate-800 dark:text-slate-200">@{{ p.laps.length }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">Total Time</div>
                                    <div class="font-mono font-bold text-indigo-700 dark:text-indigo-400">@{{ formatTime(p.totalTime) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">Pace</div>
                                    <div class="font-mono text-slate-700 dark:text-slate-300">@{{ formatPace(p) }}</div>
                                </div>
                            </div>

                            <div v-if="p.status === 'finished'" class="flex gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-800 justify-end no-print">
                                <button @click="openMediaModal('certificate', p)" class="flex-1 bg-slate-900 text-white py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 hover:bg-black transition-colors">
                                    <i class="fa-solid fa-file-pdf"></i> Cert
                                </button>
                                <button @click="openMediaModal('poster', p)" class="flex-1 bg-pink-600 text-white py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 hover:bg-pink-700 transition-colors">
                                    <i class="fa-brands fa-instagram"></i> Poster
                                </button>
                            </div>
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
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center no-print dark:text-slate-400">Media</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="(p, idx) in sortedResults" :key="p.id" :class="{'bg-emerald-50/50 dark:bg-emerald-950/40': idx < 3 && p.status === 'finished'}" class="hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">
                                <td class="p-4 font-bold text-slate-400 dark:text-slate-500">
                                    <span v-if="p.status === 'finished'" :class="{'text-amber-500 font-black text-xl': idx===0, 'text-slate-500 dark:text-slate-400': idx > 2}">#@{{ idx + 1 }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="p-4 font-oswald font-bold text-lg text-slate-900 dark:text-white">@{{ p.bib }}</td>
                                <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">@{{ p.name }}</td>
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
                                    <button v-if="p.status === 'finished'" @click="openMediaModal('certificate', p)" class="w-8 h-8 rounded-lg bg-slate-900 hover:bg-black text-white flex items-center justify-center transition shadow-sm dark:bg-slate-800 dark:hover:bg-slate-700" title="Generate E-Certificate">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </button>
                                    <button v-if="p.status === 'finished'" @click="openMediaModal('poster', p)" class="w-8 h-8 rounded-lg bg-pink-600 hover:bg-pink-700 text-white flex items-center justify-center transition shadow-sm" title="Generate Poster IG Story">
                                        <i class="fa-brands fa-instagram"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-if="dnfParticipants.length > 0" class="mt-8">
                <h3 class="text-red-600 dark:text-red-400 font-bold mb-2">Did Not Finish (DNF)</h3>
                <div class="bg-red-50 rounded-xl p-4 border border-red-200 dark:bg-red-950 dark:border-red-800">
                    <div v-for="p in dnfParticipants" class="text-red-800 text-sm flex gap-4 dark:text-red-300 font-semibold">
                        <span class="font-bold w-12 font-oswald">#@{{ p.bib }}</span>
                        <span>@{{ p.name }}</span>
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

        <!-- Media Modal -->
        <div v-if="mediaModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fade-in" @click.self="closeMediaModal">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md shadow-2xl relative">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold dark:text-white">@{{ mediaType === 'poster' ? 'Poster IG Story' : 'E-Certificate' }}</h3>
                <button @click="closeMediaModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-times text-xl"></i></button>
            </div>

            <div class="space-y-3">
                <div v-if="mediaType === 'poster'">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Background Image (opsional)</label>
                    <input @change="onMediaBgChange" type="file" accept="image/png,image/jpeg" class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700">
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jika kosong, pakai background default.</div>
                </div>

                <button @click="generateMedia" :disabled="mediaLoading" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold transition-colors">
                    <i v-if="mediaLoading" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    Generate
                </button>

                <div v-if="mediaError" class="text-sm text-red-600 dark:text-red-400">@{{ mediaError }}</div>

                <div v-if="mediaType === 'poster' && mediaPreviewUrl" class="aspect-[9/16] bg-slate-100 dark:bg-slate-900 rounded-xl overflow-hidden flex items-center justify-center relative border border-slate-200 dark:border-slate-700">
                    <img :src="mediaPreviewUrl" class="w-full h-full object-contain">
                </div>

                <div v-if="mediaDownloadUrl" class="flex gap-2">
                    <a :href="mediaDownloadUrl" target="_blank" class="flex-1 text-center py-3 rounded-xl bg-slate-900 hover:bg-black text-white font-bold transition-colors dark:bg-slate-700 dark:hover:bg-slate-600">
                        Download
                    </a>
                    <button v-if="mediaFile && canNativeShare" @click="shareMedia" class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-colors">
                        Share
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
            let ocrBusy = false;

            const currentView = ref('setup'); // setup, bibs, race, results
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

            // Multi-Admin Live Room Sync State
            const sessionRoomInput = ref('');
            const sessionSyncActive = ref(false);
            const sessionSyncLastUpdated = ref(null);
            const sessionSyncError = ref('');
            let sessionSyncTimer = null;
            let lastSeenLapIdSet = new Set();

            // TV / Jumbotron Display State (Admin 1)
            const tvDisplayOpen = ref(false);
            const tvLatestFinisher = ref(null);

            const finishedCount = computed(() => participants.value.filter(p => p.status === 'finished').length);
            const runningCount = computed(() => participants.value.filter(p => p.status === 'running' || p.status === 'ready' || !p.status).length);
            const dnfCount = computed(() => participants.value.filter(p => p.status === 'dnf').length);
            const top5Results = computed(() => sortedResults.value.filter(p => p.status === 'finished').slice(0, 5));

            const openTvDisplay = () => {
                tvDisplayOpen.value = true;
                try {
                    if (document.documentElement && document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    }
                } catch (e) {}
            };

            const closeTvDisplay = () => {
                tvDisplayOpen.value = false;
                try {
                    if (document.fullscreenElement && document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    }
                } catch (e) {}
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
            const posterBackgroundFile = ref(null);
            const posterUrl = ref('');
            const posterFile = ref(null);
            const canNativeShare = !!(navigator && navigator.share && navigator.canShare);

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
                if (!currentSessionId.value) return;
                if (!lapQueue.value.length) return;
                if (!navigator.onLine) return;

                queueFlushBusy.value = true;
                try {
                    const batch = lapQueue.value.slice(0, 50);
                    await apiFetchJson(`${apiBase}/sessions/${encodeURIComponent(String(currentSessionId.value))}/laps/bulk`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ laps: batch }),
                    });
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
                            bib: String(p.bib ?? '').trim(),
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
                            bib: String(p.bib ?? '').trim(),
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
                if (!raceId) return;
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
                                    bib: String(p.bib ?? '').trim(),
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
            };

            const ensureSessionInDb = async () => {
                if (!currentRaceId.value) return null;
                if (currentSessionId.value) return currentSessionId.value;
                const payload = {};
                const category = String(raceCategory.value || '').trim();
                if (category) payload.category = category;
                const dist = String(raceDistanceKm.value || '').trim();
                if (dist) payload.distance_km = dist;

                const data = await apiFetchJson(`${apiBase}/races/${encodeURIComponent(String(currentRaceId.value))}/sessions`, {
                    method: 'POST',
                    headers: Object.keys(payload).length ? { 'Content-Type': 'application/json' } : undefined,
                    body: Object.keys(payload).length ? JSON.stringify(payload) : undefined,
                });
                currentSessionId.value = data?.session?.id || null;
                sessionSlug.value = data?.session?.slug || sessionSlug.value;
                publicResultsUrl.value = data?.session?.public_results_url || publicResultsUrl.value;
                saveState();
                return currentSessionId.value;
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
                // Check duplicate BIB
                if (participants.value.find(p => p.bib == newBib.value)) {
                    alert('Nomor BIB sudah ada!');
                    return;
                }
                
                const bibValue = String(newBib.value).trim();
                
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
                participants.value.sort((a,b) => parseInt(a.bib) - parseInt(b.bib));
                
                newBib.value = '';
                newName.value = '';
                newPredictedHH.value = '';
                newPredictedMM.value = '';
                newPredictedSS.value = '';
                clearNewFacePhoto();
                saveState();
                nextTick(() => inputBib.value.focus());
            };

            const downloadCsvSample = () => {
                const csvContent = "bib,nama,prediksi_waktu\n101,Budi Santoso,00:25:30\n102,Siti Rahma,00:28:15\n103,Ahmad Fauzi,00:22:40\n104,Dewi Lestari,00:31:10\n105,Rian Hidayat,00:19:50\n";
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

                const existingBibSet = new Set(participants.value.map(p => String(p.bib).trim().toUpperCase()));
                const newEntries = [];

                for (let i = 1; i < lines.length; i++) {
                    const row = splitRow(lines[i]);
                    if (row.length < 2) continue;

                    const rawBib = row[bibIdx] ?? '';
                    const rawName = row[nameIdx] ?? '';
                    const rawPred = predIdx !== -1 ? (row[predIdx] ?? '') : '';

                    const bib = String(rawBib).trim();
                    const name = String(rawName).trim();

                    if (!bib || !name) {
                        invalidCount++;
                        continue;
                    }

                    if (existingBibSet.has(bib.toUpperCase())) {
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

                    existingBibSet.add(bib.toUpperCase());
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
                    participants.value.sort((a, b) => {
                        return String(a.bib).localeCompare(String(b.bib), undefined, { numeric: true, sensitivity: 'base' });
                    });
                    saveState();
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

            Vue.watch(raceCategory, () => {
                saveState();
            });

            // Timer Logic
            const startRace = () => {
                if (!timer.value.running) {
                    Promise.resolve()
                        .then(async () => {
                            const nameTrim = String(raceName.value || '').trim();
                            if (!nameTrim) raceName.value = `Race ${raceCategory.value}`;
                            await ensureRaceInDb();
                            await syncParticipantsToDb();
                            await ensureSessionInDb();
                        })
                        .then(() => {
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
                                if (p.status === 'ready') p.status = 'running';
                            });
                            saveState();
                            startLiveSyncPolling();
                        })
                        .catch((e) => alert(e?.message || 'Gagal simpan race ke database.'));
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

            const joinLiveSession = async (slugOrId) => {
                const target = String(slugOrId || sessionRoomInput.value || '').trim();
                if (!target) return;

                sessionSyncError.value = '';
                try {
                    let endpoint = `${apiBase}/sessions/${encodeURIComponent(target)}/live-sync`;
                    let res = await fetch(endpoint, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) {
                        endpoint = `${apiBase}/public/${encodeURIComponent(target)}/live-sync`;
                        res = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                    }

                    if (!res.ok) throw new Error('Sesi tidak ditemukan di server.');
                    const data = await res.json();
                    if (!data.success) throw new Error(data.message || 'Gagal sinkronisasi sesi.');

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
                        const existingMap = new Map(participants.value.map(p => [String(p.bib).trim(), p]));
                        const merged = data.participants.map(sp => {
                            const local = existingMap.get(String(sp.bib).trim());
                            return {
                                id: local ? local.id : crypto.randomUUID(),
                                bib: String(sp.bib).trim(),
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
                        participants.value = merged;
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

                    startLiveSyncPolling();
                    saveState();
                } catch (e) {
                    sessionSyncError.value = e.message || 'Gagal terhubung ke sesi.';
                    console.error('Session sync error:', e);
                }
            };

            const pollLiveSync = async () => {
                const target = sessionSlug.value || currentSessionId.value;
                if (!target || !sessionSyncActive.value) return;

                try {
                    // Send since_id parameter for ultra-lightweight delta sync (< 1ms, < 1KB)
                    let endpoint = `${apiBase}/sessions/${encodeURIComponent(String(target))}/live-sync?since_id=${maxSyncedLapId.value}`;
                    let res = await fetch(endpoint, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) {
                        endpoint = `${apiBase}/public/${encodeURIComponent(String(target))}/live-sync?since_id=${maxSyncedLapId.value}`;
                        res = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                    }

                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.success) {
                            sessionSyncLastUpdated.value = new Date().toLocaleTimeString();
                            
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

            const resetRace = () => {
                if (confirm('Reset timer dan semua hasil?')) {
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

                    if (sessionSyncTimer) {
                        clearInterval(sessionSyncTimer);
                        sessionSyncTimer = null;
                    }
                    sessionSyncActive.value = false;
                    sessionSlug.value = '';
                    currentSessionId.value = null;
                    currentRaceId.value = null;
                    publicResultsUrl.value = '';
                    sessionRoomInput.value = '';

                    maxSyncedLapId.value = 0;
                    lastSeenLapIdSet.clear();
                    participants.value.forEach(p => {
                        p.laps = [];
                        p.status = 'ready';
                        p.totalTime = 0;
                        p.recentlyScanned = false;
                        p.lastScanTime = 0;
                    });
                    raceLogoUrl.value = '';
                    certificatesByBib.value = {};
                    if (raceLogoPreviewUrl.value && raceLogoPreviewUrl.value.startsWith('blob:')) {
                        try { URL.revokeObjectURL(raceLogoPreviewUrl.value); } catch (err) {}
                    }
                    raceLogoPreviewUrl.value = '';
                    raceLogoFile.value = null;
                    raceLogoFileName.value = '';
                    if (posterUrl.value) {
                        try { URL.revokeObjectURL(posterUrl.value); } catch (err) {}
                    }
                    posterUrl.value = '';
                    posterFile.value = null;
                    posterBackgroundFile.value = null;
                    clearState();
                }
            };

            const formattedTime = computed(() => {
                return formatTime(timer.value.elapsed);
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

            // Race Logic
            const normalizeBib = (raw) => {
                const s = String(raw ?? '').trim();
                if (!s) return '';
                try {
                    const obj = JSON.parse(s);
                    if (obj && (obj.bib || obj.BIB || obj.number)) {
                        const v = obj.bib ?? obj.BIB ?? obj.number;
                        return String(v ?? '').trim();
                    }
                } catch (e) {}

                try {
                    if (s.startsWith('http://') || s.startsWith('https://')) {
                        const u = new URL(s);
                        const qp = u.searchParams.get('bib') || u.searchParams.get('BIB');
                        if (qp) return String(qp).trim();
                        const last = u.pathname.split('/').filter(Boolean).pop();
                        if (last) return String(last).trim();
                    }
                } catch (e) {}

                const m = s.match(/\d{1,10}/);
                if (m) return m[0];
                return s;
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

                // 4. Suffix / Number match fallback
                const rawDigits = cleanRaw.replace(/\D/g, '');
                if (rawDigits.length >= 1) {
                    const candidates = participants.value.filter(p => {
                        const bibUpper = String(p.bib).trim().toUpperCase();
                        if (cleanPrefix && !bibUpper.replace(/[-_\s]/g, '').startsWith(cleanPrefix)) return false;
                        const bDigits = bibUpper.replace(/\D/g, '');
                        return bDigits === rawDigits || bDigits.endsWith(rawDigits);
                    });
                    if (candidates.length === 1) return candidates[0];
                }

                return null;
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
                        const searchAttempt = lockedBibPrefix.value && !token.toUpperCase().startsWith(lockedBibPrefix.value.toUpperCase())
                            ? `${lockedBibPrefix.value}-${token}`
                            : token;
                        showDuplicateAlert('error', `Nomor BIB #${searchAttempt} tidak ditemukan dalam daftar peserta!`);
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

            const recordLap = (id, source = 'manual') => {
                const p = participants.value.find(p => p.id === id);
                if (!p) return;

                if (!timer.value.running && timer.value.elapsed === 0) {
                    showDuplicateAlert('error', 'Timer belum berjalan! Silakan mulai timer race terlebih dahulu.');
                    return;
                }

                if (p.status === 'finished') {
                    showDuplicateAlert('warning', `DUPLIKAT: BIB #${p.bib} (${p.name}) SUDAH FINISH sebelumnya pada ${formatTime(p.totalTime)}!`, p);
                    return;
                }

                if (p.status === 'dnf') {
                    showDuplicateAlert('error', `BIB #${p.bib} (${p.name}) tercatat DNF!`, p);
                    return;
                }

                // Debounce / Cooldown to prevent double scan
                const now = Date.now();
                const cooldownMs = (raceSettings.value.raceMode === 'multi_lap' ? (raceSettings.value.minLapCooldownSec || 15) : 3) * 1000;
                if (p.lastScanTime && (now - p.lastScanTime < cooldownMs)) {
                    if (raceSettings.value.raceMode === 'multi_lap') {
                        const rem = Math.ceil((cooldownMs - (now - p.lastScanTime)) / 1000);
                        showDuplicateAlert('warning', `BIB #${p.bib} dalam masa cooldown lap (${rem}s tersisa)!`, p);
                    }
                    console.log('Debounced/Cooldown scan for ' + p.bib);
                    return;
                }
                
                playBeep();

                if (!Array.isArray(p.laps)) p.laps = [];
                p.laps.push(now);
                p.lastScanTime = now;
                p.totalTime = timer.value.elapsed;

                if (raceSettings.value.raceMode === 'single' || p.laps.length >= (raceSettings.value.targetLaps || 1)) {
                    p.status = 'finished';
                } else {
                    p.status = 'running';
                }

                if (currentSessionId.value) {
                    queueEnqueueLap(String(p.bib ?? '').trim(), Math.max(0, Math.floor(p.totalTime || 0)), new Date(now).toISOString());
                    queueFlush();
                }
                
                // Highlight effect
                p.recentlyScanned = true;
                setTimeout(() => p.recentlyScanned = false, 2000);

                if (source === 'scanner' || source === 'ai_vision' || source === 'manual_input') {
                    camera.value.lastScanMsg = `Tercatat: #${p.bib} (${p.name}) • ${formatTime(p.totalTime)}`;
                }
                saveState();
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

            // High-Performance OCR Digit Matcher for Torso/BIB Numbers
            const ocrScanTorso = async (video, bbox) => {
                if (!raceSettings.value.enableOcr) return null;
                if (typeof Tesseract === 'undefined') return null;
                
                try {
                    const [bx, by, bw, bh] = bbox;
                    const cropCanvas = document.createElement('canvas');
                    const torsoY = Math.max(0, by + bh * 0.22);
                    const torsoH = Math.min(video.videoHeight - torsoY, bh * 0.48);
                    const torsoX = Math.max(0, bx + bw * 0.1);
                    const torsoW = Math.min(video.videoWidth - torsoX, bw * 0.8);

                    cropCanvas.width = 240;
                    cropCanvas.height = 160;
                    const ctx = cropCanvas.getContext('2d');

                    ctx.drawImage(video, torsoX, torsoY, torsoW, torsoH, 0, 0, 240, 160);

                    // High contrast binarization
                    const imgData = ctx.getImageData(0, 0, 240, 160);
                    const d = imgData.data;
                    for (let i = 0; i < d.length; i += 4) {
                        const v = (d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114);
                        const bin = v > 125 ? 255 : 0;
                        d[i] = bin;
                        d[i + 1] = bin;
                        d[i + 2] = bin;
                    }
                    ctx.putImageData(imgData, 0, 0);

                    if (!ocrWorker) {
                        ocrWorker = await Tesseract.createWorker('eng');
                        await ocrWorker.setParameters({
                            tessedit_char_whitelist: '0123456789',
                        });
                    }

                    const res = await ocrWorker.recognize(cropCanvas);
                    const digits = (res?.data?.text || '').replace(/\D/g, '').trim();
                    if (!digits) return null;

                    const matched = participants.value.find(p => p.bib === digits || p.bib.endsWith(digits));
                    if (matched) return matched.bib;

                    if (digits.length >= 1 && digits.length <= 5) return digits;
                } catch (e) {
                    // ignore OCR error
                }
                return null;
            };

            // Runner Crossing Event Handler
            const handleRunnerCrossing = async (video, bbox) => {
                camera.value.crossingCount++;
                camera.value.lineFlash = true;
                setTimeout(() => { camera.value.lineFlash = false; }, 450);
                if (raceSettings.value.enableBeep) playBeep();

                const recordedTimeMs = timer.value.running ? timer.value.elapsed : 0;
                const formattedTimeStr = formatTime(recordedTimeMs);

                // Create snapshot thumbnail
                let snapshotDataUrl = '';
                try {
                    const snapCanvas = document.createElement('canvas');
                    snapCanvas.width = 160;
                    snapCanvas.height = 160;
                    const snapCtx = snapCanvas.getContext('2d');
                    
                    const [bx, by, bw, bh] = bbox;
                    const padX = Math.max(0, bx - 10);
                    const padY = Math.max(0, by - 10);
                    const padW = Math.min(video.videoWidth - padX, bw + 20);
                    const padH = Math.min(video.videoHeight - padY, bh + 20);

                    snapCtx.drawImage(video, padX, padY, padW, padH, 0, 0, 160, 160);
                    snapshotDataUrl = snapCanvas.toDataURL('image/jpeg', 0.8);
                } catch (e) {}

                // Triple Recognition Pipeline: Engine 1 (QR Code), Engine 2 (OCR Digits), Engine 3 (Face AI)
                let detectedBib = '';
                let matchMethod = '';

                // Engine 1: QR Code Scanner
                if (raceSettings.value.enableQr) {
                    try {
                        const bibs = await decodeMultipleFromVideoFrame(video);
                        if (bibs && bibs.length > 0) {
                            detectedBib = bibs[0];
                            matchMethod = 'qr';
                        }
                    } catch (e) {}
                }

                // Engine 2: OCR Digit Matcher
                if (!detectedBib && raceSettings.value.enableOcr) {
                    try {
                        const ocrBib = await ocrScanTorso(video, bbox);
                        if (ocrBib) {
                            detectedBib = ocrBib;
                            matchMethod = 'ocr';
                        }
                    } catch (e) {}
                }

                // Engine 3: Face Recognition AI (Biometrics with Multi-Angle Support)
                if (!detectedBib && raceSettings.value.enableFaceAi && faceModelsLoaded) {
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
                            let minDistance = 0.54; // Euclidean distance threshold
                            
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
                                detectedBib = bestMatch.bib;
                                matchMethod = 'face_ai';
                            }
                        }
                    } catch (e) {}
                }

                let participant = null;
                if (detectedBib) {
                    participant = participants.value.find(p => p.bib == detectedBib);
                }

                if (participant && timer.value.running) {
                    const tag = matchMethod === 'face_ai' ? '[Face AI]' : (matchMethod === 'ocr' ? '[OCR]' : '[QR]');
                    // Check Race Mode & Anti-duplicate Guard
                    if (raceSettings.value.raceMode === 'single') {
                        if (participant.status === 'finished') {
                            camera.value.lastScanMsg = `BIB #${participant.bib} sudah Finish sebelumnya (${formatTime(participant.totalTime)})`;
                            showDuplicateAlert('warning', `DUPLIKAT: BIB #${participant.bib} (${participant.name}) SUDAH FINISH sebelumnya pada ${formatTime(participant.totalTime)}!`, participant);
                            return;
                        }
                        recordLap(participant.id, 'ai_vision');
                        participant.status = 'finished';
                        triggerTvFinisherFlash(participant);
                        camera.value.lastScanMsg = `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`;
                        showDuplicateAlert('success', `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`, participant);
                    } else {
                        // Multi-Lap Mode with Cooldown Filter
                        const now = Date.now();
                        const cooldownMs = raceSettings.value.minLapCooldownSec * 1000;
                        if (participant.lastScanTime && (now - participant.lastScanTime < cooldownMs)) {
                            const rem = Math.ceil((cooldownMs - (now - participant.lastScanTime)) / 1000);
                            camera.value.lastScanMsg = `BIB #${participant.bib} dalam masa cooldown putaran`;
                            showDuplicateAlert('warning', `BIB #${participant.bib} (${participant.name}) masih dalam cooldown lap (${rem}s tersisa)!`, participant);
                            return;
                        }
                        recordLap(participant.id, 'ai_vision');
                        if (participant.laps.length >= raceSettings.value.targetLaps) {
                            participant.status = 'finished';
                            triggerTvFinisherFlash(participant);
                            camera.value.lastScanMsg = `FINISH ${tag} (Lap ${participant.laps.length}): BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`;
                            showDuplicateAlert('success', `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`, participant);
                        } else {
                            camera.value.lastScanMsg = `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} ${tag}: BIB #${participant.bib} • ${formattedTimeStr}`;
                            showDuplicateAlert('info', `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} ${tag}: BIB #${participant.bib} • ${formattedTimeStr}`, participant);
                        }
                    }
                } else if (detectedBib) {
                    camera.value.lastScanMsg = `Crossing: Unknown BIB #${detectedBib} • ${formattedTimeStr}`;
                    showDuplicateAlert('error', `Crossing terdeteksi BIB #${detectedBib}, namun tidak ada di daftar peserta!`);
                } else {
                    camera.value.lastScanMsg = `Crossing terdeteksi • Waktu: ${formattedTimeStr}`;
                }

                liveFinishFeed.value.unshift({
                    id: crypto.randomUUID(),
                    timestampMs: recordedTimeMs,
                    timeFormatted: formattedTimeStr,
                    bib: participant ? participant.bib : (detectedBib || ''),
                    name: participant ? participant.name : '',
                    snapshot: snapshotDataUrl,
                    participantId: participant ? participant.id : null
                });

                if (liveFinishFeed.value.length > 30) {
                    liveFinishFeed.value.pop();
                }
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
                                    crossedAt: 0
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
                                    handleRunnerCrossing(video, person.bbox);
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
                    await loadAiModel();
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
                        runAiDetectionLoop();
                    }
                } catch (e) {
                    console.error('Failed to start AI Camera:', e);
                    alert('Gagal membuka stream kamera AI.');
                }
            };

            const stopAiCamera = () => {
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
            };

            const switchCameraDevice = () => {
                if (camera.value.active && camera.value.mode === 'ai_line') {
                    startAiCamera(camera.value.selectedDeviceId);
                }
            };

            const switchCameraMode = () => {
                if (!camera.value.active) return;
                if (camera.value.mode === 'ai_line') {
                    if (camera.value.scanner) {
                        camera.value.scanner.stop().catch(() => {}).then(() => {
                            camera.value.scanner = null;
                            startAiCamera(camera.value.selectedDeviceId);
                        });
                    } else {
                        startAiCamera(camera.value.selectedDeviceId);
                    }
                } else {
                    stopAiCamera();
                    toggleScanner();
                }
            };

            // Toggle Camera / Scanner
            const toggleScanner = () => {
                if (camera.value.active) {
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
                } else {
                    camera.value.active = true;
                    refreshCameraDevices();
                    if (camera.value.mode === 'ai_line') {
                        nextTick(() => {
                            startAiCamera(camera.value.selectedDeviceId);
                        });
                    } else {
                        nextTick(() => {
                            const html5QrCode = new Html5Qrcode("reader");
                            camera.value.scanner = html5QrCode;
                            
                            html5QrCode.start(
                                { facingMode: "environment" },
                                { fps: 20, qrbox: { width: 320, height: 320 } },
                                (decodedText) => {
                                    const bib = normalizeBib(decodedText);
                                    if (!bib) return;
                                    const p = participants.value.find(p => p.bib == bib);
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

                if (timer.value.running) {
                    recordLap(p.id, 'manual_assign');
                } else {
                    p.totalTime = item.timestampMs;
                    p.laps.push(Date.now());
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
                    const p = participants.value.find(p => p.bib == bib);
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

            onMounted(() => {
                loadState();
                queueLoad();
                loadExistingRaces();
                loadEoEvents();
                refreshCameraDevices();
                window.addEventListener('keydown', onKeydown);
                window.addEventListener('click', handleClickOutsideEo);
                loadAiModel();

                // Check URL parameter ?session=XXXX for multi-device sync
                const urlParams = new URLSearchParams(window.location.search);
                const roomSession = urlParams.get('session');
                if (roomSession) {
                    sessionSlug.value = roomSession;
                    sessionRoomInput.value = roomSession;
                    joinLiveSession(roomSession);
                } else if (sessionSlug.value || currentSessionId.value) {
                    startLiveSyncPolling();
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

            // Media Modal
            const mediaModalOpen = ref(false);
            const mediaType = ref('poster'); // poster | certificate
            const mediaParticipant = ref(null);
            const mediaBgFile = ref(null);
            const mediaPreviewUrl = ref('');
            const mediaDownloadUrl = ref('');
            const mediaFile = ref(null);
            const mediaLoading = ref(false);
            const mediaError = ref('');

            const openMediaModal = (type, p) => {
                mediaType.value = type;
                mediaParticipant.value = p;
                mediaBgFile.value = null;
                mediaError.value = '';
                mediaDownloadUrl.value = '';
                mediaFile.value = null;
                if (mediaPreviewUrl.value && mediaPreviewUrl.value.startsWith('blob:')) {
                    try { URL.revokeObjectURL(mediaPreviewUrl.value); } catch (e) {}
                }
                mediaPreviewUrl.value = '';
                mediaModalOpen.value = true;
            };

            const closeMediaModal = () => {
                mediaModalOpen.value = false;
                if (mediaPreviewUrl.value && mediaPreviewUrl.value.startsWith('blob:')) {
                    try { URL.revokeObjectURL(mediaPreviewUrl.value); } catch (e) {}
                }
                mediaPreviewUrl.value = '';
            };

            const onMediaBgChange = (e) => {
                mediaBgFile.value = e?.target?.files?.[0] || null;
            };

            const generateMedia = async () => {
                const p = mediaParticipant.value;
                if (!p) return;
                const slug = ensureSlug();
                if (!slug) {
                    alert('Slug results belum tersedia. Finish sesi dulu.');
                    return;
                }

                mediaLoading.value = true;
                mediaError.value = '';
                mediaDownloadUrl.value = '';
                mediaFile.value = null;

                try {
                    if (mediaType.value === 'certificate') {
                        const res = await apiFetchJson(`${apiBase}/public/${encodeURIComponent(slug)}/participants/${encodeURIComponent(String(p.bib))}/certificate`, { method: 'POST' });
                        mediaDownloadUrl.value = res?.certificate?.download_url || '';
                    } else {
                        const form = new FormData();
                        if (mediaBgFile.value) form.append('background', mediaBgFile.value);
                        const blob = await apiFetchBlob(`${apiBase}/public/${encodeURIComponent(slug)}/participants/${encodeURIComponent(String(p.bib))}/poster`, { method: 'POST', body: form });
                        const url = URL.createObjectURL(blob);
                        mediaPreviewUrl.value = url;
                        mediaDownloadUrl.value = url;
                        mediaFile.value = new File([blob], `poster-${p.bib}.png`, { type: 'image/png' });
                    }
                } catch (e) {
                    mediaError.value = e?.message || 'Gagal generate media.';
                } finally {
                    mediaLoading.value = false;
                }
            };

            const shareMedia = async () => {
                if (!mediaFile.value) return;
                if (!navigator.share || !navigator.canShare || !navigator.canShare({ files: [mediaFile.value] })) {
                    return;
                }
                try {
                    await navigator.share({
                        title: raceName.value || 'Race Results',
                        text: 'Hasil race',
                        files: [mediaFile.value],
                    });
                } catch (e) {}
            };

            return {
                currentView, raceName, existingRaces, selectExistingRace, raceLogoPreviewUrl, raceLogoFileName, onLogoChange,
                raceCategory, categories, raceDistanceKm, publicResultsUrl, sessionSlug, newName, newBib, newPredictedHH, newPredictedMM, newPredictedSS, mobileMenuOpen, inputName, inputBib,
                participants, timer, camera, formattedTime, certificatesByBib,
                focusName, addParticipant, removeParticipant, goToBibs, printBibs,
                downloadCsvSample, onCsvUpload, importCsvText,
                startRace, pauseRace, finishRace, resetRace, toggleScanner, captureScan,
                activeParticipants, sortedResults, dnfParticipants,
                raceSearchQuery, raceStatusFilter, raceViewMode, racePageSize, raceCurrentPage,
                filteredRaceParticipants, raceTotalPages, paginatedRaceParticipants,
                prevRacePage, nextRacePage, setRacePage,
                recordLap, markDNF, getLastLapTime, getInitials, formatTime,
                formatPace, getDeltaMs, formatDelta, getPointsInfo,
                isAuthenticated,
                isDarkMode, toggleDarkMode, playBeep,
                copyResultsLink,
                mediaModalOpen, mediaType, mediaParticipant, mediaLoading, mediaError, mediaPreviewUrl, mediaDownloadUrl, mediaFile,
                openMediaModal, closeMediaModal, onMediaBgChange, generateMedia, shareMedia,
                canNativeShare,
                eoEvents, eoEventsLoading, selectedEoEventId, selectedEoCategoryId, importingEoParticipants, selectedEoEvent,
                eoEventSearchQuery, eoDropdownOpen, eoAutocompleteContainer, filteredEoEvents, onEoSearchInput, selectEoEvent, clearSelectedEoEvent,
                onEoEventChange, importEoEventParticipants,
                cameraSettingsOpen, raceSettings,
                manualBibInput, lockedBibPrefix, detectedBibPrefixes, ambiguousRunners, autoSubmitDigits, liveMatchedRunner, onManualBibKeyup,
                manualValidationAlert, recordManualBib, dismissAlert, setDetectorPreset, currentDetectorPreset, showDuplicateAlert, playBuzzer,
                tvDisplayOpen, tvLatestFinisher, finishedCount, runningCount, dnfCount, top5Results, openTvDisplay, closeTvDisplay,
                newFacePhoto, newFacePhotos, newFaceDescriptors, faceEnrollStep, enrolledAngleCount,
                newFaceProcessing, faceModelLoading, faceCaptureModalOpen,
                openFaceCaptureModal, closeFaceCaptureModal, snapFaceAngle, onFacePhotoUpload, clearNewFacePhoto,
                liveFinishFeed, assignBibModalOpen, selectedUnassignedFinish,
                handleCanvasMouseDown, handleCanvasMouseMove, handleCanvasMouseUp,
                handleCanvasTouchStart, handleCanvasTouchMove, handleCanvasTouchEnd,
                setLinePreset, switchCameraDevice, switchCameraMode,
                sessionRoomInput, sessionSyncActive, sessionSyncLastUpdated, sessionSyncError,
                copySessionShareUrl, joinLiveSession, startLiveSyncPolling,
                openAssignBibModal, confirmAssignBib
            };
        }
    }).mount('#app');
</script>
</body>
</html>
