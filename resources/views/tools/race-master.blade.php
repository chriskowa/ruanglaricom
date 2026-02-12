<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Race Master Pro - Ruang Lari</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Oswald:wght@500;700&display=swap');

        body { font-family: 'Inter', sans-serif; }
        .font-mono-numbers { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
        .font-oswald { font-family: 'Oswald', sans-serif; }

        /* Print Styles for A5 Landscape BIB */
        @media print {
            @page { size: A5 landscape; margin: 0; }
            body * { visibility: hidden; }
            #bib-print-area, #bib-print-area * { visibility: visible; }
            #bib-print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .bib-card { 
                width: 210mm; height: 148mm; 
                page-break-after: always; 
                display: flex; flex-direction: column; 
                align-items: center; justify-content: center;
                border: none !important;
            }
            .no-print { display: none !important; }
        }

        /* Custom UI Tweaks */
        .glass-panel { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.05); }
        .dark .glass-panel { background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.05); }
        .scanner-active { border: 4px solid #10B981; }
        
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen transition-colors duration-300 dark:bg-slate-900 dark:text-slate-100">

<div id="app" :class="{'dark': isDarkMode}">
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
             </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-4">

        <div v-if="currentView === 'setup'" class="space-y-6 animate-fade-in">
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
                <h2 class="text-lg font-bold mb-4 text-slate-900 dark:text-white">2. Tambah Peserta</h2>
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
                                <th class="p-3 font-medium">BIB</th>
                                <th class="p-3 font-medium">Nama</th>
                                <th class="p-3 font-medium">Prediksi</th>
                                <th class="p-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="(p, index) in participants" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="p-3 font-oswald font-bold text-xl dark:text-white">@{{ p.bib }}</td>
                                <td class="p-3 font-medium dark:text-slate-200">@{{ p.name }}</td>
                                <td class="p-3 font-mono text-sm text-slate-600 dark:text-slate-400">@{{ p.predictedTimeMs ? formatTime(p.predictedTimeMs) : '-' }}</td>
                                <td class="p-3">
                                    <button @click="removeParticipant(index)" class="text-red-400 hover:text-red-600 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr v-if="participants.length === 0">
                                <td colspan="4" class="p-8 text-center text-slate-400">Belum ada peserta.</td>
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
                <h2 class="text-2xl font-bold">Preview BIB (A5 Landscape)</h2>
                <div class="flex gap-3 flex-wrap justify-center">
                    <button @click="currentView = 'setup'" class="text-slate-500 font-medium px-4">Kembali</button>
                    <button @click="printBibs" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700">
                        <i class="fa-solid fa-print mr-2"></i> Print / Download PDF
                    </button>
                    <button @click="currentView = 'race'" class="bg-green-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-green-200 hover:bg-green-700">
                        Siap Race <i class="fa-solid fa-flag-checkered ml-2"></i>
                    </button>
                </div>
            </div>

            <div id="bib-print-area" class="grid grid-cols-1 gap-8 justify-items-center overflow-x-auto pb-8">
                <div v-for="p in participants" :key="p.id" class="bib-card bg-white border border-slate-200 shadow-sm relative overflow-hidden flex-shrink-0">
                    <div class="absolute top-0 left-0 w-full h-4 bg-indigo-600"></div>
                    <div class="absolute bottom-0 left-0 w-full h-4 bg-indigo-600"></div>
                    
                    <div class="flex flex-col items-center justify-between h-full py-12 w-full text-center z-10">
                        <div class="w-full">
                            <div class="text-xl font-bold text-slate-400 uppercase tracking-widest">@{{ raceCategory }}</div>
                            <div class="text-[120px] leading-none font-oswald font-bold text-slate-900 mt-2">@{{ p.bib }}</div>
                        </div>

                        <div :id="'qrcode-' + p.bib" class="my-4 p-2 bg-white rounded-lg"></div>

                        <div class="w-full px-8">
                            <div class="text-4xl font-bold text-slate-800 truncate uppercase">@{{ p.name }}</div>
                            <div class="text-sm text-slate-400 mt-2 font-mono">ID: @{{ p.id }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="currentView === 'race'" class="space-y-4">
            
            <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-xl sticky top-[70px] z-40 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <div class="text-slate-400 text-xs font-bold uppercase tracking-widest">Race Timer</div>
                    <div class="font-mono-numbers text-5xl md:text-6xl font-black text-white tracking-wider">
                        @{{ formattedTime }}
                    </div>
                </div>

                <div class="flex gap-3 flex-wrap justify-center md:justify-end">
                    <button v-if="!timer.running" @click="startRace" class="bg-green-500 hover:bg-green-600 text-white w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center shadow-lg shadow-green-900/50 transition transform hover:scale-105 flex-shrink-0" title="Start/Resume">
                        <i class="fa-solid fa-play text-xl md:text-2xl"></i>
                    </button>
                    <button v-if="timer.running" @click="pauseRace" class="bg-yellow-500 hover:bg-yellow-600 text-white w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center shadow-lg shadow-yellow-900/50 transition transform hover:scale-105 flex-shrink-0" title="Pause Timer">
                        <i class="fa-solid fa-pause text-xl md:text-2xl"></i>
                    </button>
                    <button v-if="timer.elapsed > 0" @click="finishRace" class="bg-red-600 hover:bg-red-700 text-white w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center shadow-lg shadow-red-900/50 transition transform hover:scale-105 flex-shrink-0" title="Finish Sesi">
                        <i class="fa-solid fa-flag-checkered text-xl md:text-2xl"></i>
                    </button>
                    <button @click="resetRace" class="bg-slate-700 hover:bg-slate-600 text-white w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition flex-shrink-0" title="Reset Total">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <button v-show="camera.active" @click="captureScan" :disabled="camera.busy" :class="camera.busy ? 'bg-slate-600' : 'bg-yellow-500 hover:bg-yellow-600'" class="text-white w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center shadow-lg transition transform hover:scale-105 flex-shrink-0" title="Capture (Spasi)">
                        <i class="fa-solid fa-camera text-xl md:text-2xl"></i>
                    </button>
                    <button @click="toggleScanner" :class="camera.active ? 'bg-indigo-500 ring-2 ring-white' : 'bg-slate-700'" class="md:ml-4 hover:bg-indigo-600 text-white w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition flex-shrink-0" title="Toggle Camera">
                        <i class="fa-solid fa-qrcode"></i>
                    </button>
                </div>
            </div>

            <div v-show="camera.active" class="bg-black rounded-2xl overflow-hidden shadow-2xl relative w-full max-w-lg mx-auto border-4 border-indigo-500 mb-6">
                <div id="reader" class="w-full"></div>
                <div class="absolute top-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded">Camera ON • SPACE = CAPTURE</div>
                <div class="p-2 bg-slate-900 text-center text-green-400 font-mono text-sm" v-if="camera.lastScanMsg">
                    @{{ camera.lastScanMsg }}
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 pt-4">
                <div v-for="p in activeParticipants" :key="p.id" 
                     class="relative bg-white rounded-xl shadow-sm border-2 transition-all duration-200 cursor-pointer hover:border-indigo-400 group select-none dark:bg-slate-800 dark:border-slate-700 dark:hover:border-indigo-500"
                     :class="{'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900': p.recentlyScanned, 'border-slate-200 dark:border-slate-700': !p.recentlyScanned}"
                     @click="recordLap(p.id, 'manual')">
                    
                    <button @click.stop="markDNF(p.id)" class="absolute top-2 right-2 text-slate-300 hover:text-red-500 p-1 z-10 dark:text-slate-600 dark:hover:text-red-400" title="DNF (Did Not Finish)">
                        <i class="fa-solid fa-circle-xmark text-xl"></i>
                    </button>

                    <div class="p-4 flex flex-col items-center text-center h-full justify-between">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold mb-2 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition dark:bg-slate-700 dark:text-slate-400 dark:group-hover:bg-indigo-900/50 dark:group-hover:text-indigo-300">
                            @{{ getInitials(p.name) }}
                        </div>

                        <div class="font-oswald font-bold text-4xl text-slate-800 dark:text-white">@{{ p.bib }}</div>
                        <div class="text-sm font-medium text-slate-600 line-clamp-1 w-full dark:text-slate-300">@{{ p.name }}</div>

                        <div class="mt-3 w-full pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center dark:border-slate-700">
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase">Laps</div>
                                <div class="font-bold text-indigo-600 text-lg dark:text-indigo-400">@{{ p.laps.length }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase">Last</div>
                                <div class="font-mono text-xs font-medium text-slate-600 mt-1">
                                    @{{ getLastLapTime(p) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="p.recentlyScanned" class="absolute inset-0 bg-indigo-500/10 rounded-xl pointer-events-none flex items-center justify-center">
                        <span class="bg-indigo-600 text-white text-xs px-2 py-1 rounded font-bold animate-ping">RECORDED</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center text-slate-400 text-sm">
                Klik kartu untuk tambah lap manual, atau gunakan kamera QR Scanner.
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
                        <div v-for="(p, idx) in sortedResults" :key="p.id" class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                     <span v-if="p.status === 'finished'" :class="{'text-yellow-500': idx===0, 'text-slate-500 dark:text-slate-400': idx > 0}" class="font-bold text-lg">#@{{ idx + 1 }}</span>
                                     <span v-else class="text-slate-400">-</span>
                                     <div>
                                         <div class="font-oswald font-bold text-xl dark:text-white">@{{ p.bib }}</div>
                                         <div class="text-sm font-medium text-slate-600 dark:text-slate-300">@{{ p.name }}</div>
                                     </div>
                                </div>
                                <div class="text-right">
                                    <span v-if="p.status === 'dnf'" class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded font-bold dark:bg-red-900/30 dark:text-red-400">DNF</span>
                                    <span v-else-if="p.status === 'finished'" class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold dark:bg-green-900/30 dark:text-green-400">FINISH</span>
                                    <span v-else class="text-slate-400 text-xs dark:text-slate-500">RUNNING</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-2 py-3 border-t border-slate-200 dark:border-slate-600 mt-2">
                                <div class="text-center">
                                    <div class="text-[10px] text-slate-400 uppercase">Laps</div>
                                    <div class="font-bold text-slate-700 dark:text-slate-200">@{{ p.laps.length }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-[10px] text-slate-400 uppercase">Total Time</div>
                                    <div class="font-mono font-bold text-indigo-600 dark:text-indigo-400">@{{ formatTime(p.totalTime) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-[10px] text-slate-400 uppercase">Pace</div>
                                    <div class="font-mono text-slate-600 dark:text-slate-400">@{{ formatPace(p) }}</div>
                                </div>
                            </div>

                            <div v-if="p.status === 'finished'" class="flex gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-600 justify-end no-print">
                                <button @click="openMediaModal('certificate', p)" class="flex-1 bg-slate-800 text-white py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2 hover:bg-slate-900 transition-colors">
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
                        <thead class="bg-slate-50 border-b border-slate-200 dark:bg-slate-900/50 dark:border-slate-700">
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
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="(p, idx) in sortedResults" :key="p.id" :class="{'bg-green-50 dark:bg-green-900/20': idx < 3 && p.status === 'finished'}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="p-4 font-bold text-slate-400 dark:text-slate-500">
                                    <span v-if="p.status === 'finished'" :class="{'text-yellow-500 text-xl': idx===0, 'text-slate-500 dark:text-slate-400': idx > 2}">#@{{ idx + 1 }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="p-4 font-oswald font-bold text-lg dark:text-white">@{{ p.bib }}</td>
                                <td class="p-4 font-medium dark:text-slate-200">@{{ p.name }}</td>
                                <td class="p-4 text-center">
                                    <span class="bg-slate-100 px-2 py-1 rounded text-xs font-bold dark:bg-slate-700 dark:text-slate-300">@{{ p.laps.length }}</span>
                                </td>
                                <td class="p-4 text-right font-mono font-bold text-indigo-700 dark:text-indigo-400">
                                    @{{ formatTime(p.totalTime) }}
                                </td>
                                <td class="p-4 text-right font-mono text-slate-700 dark:text-slate-400">@{{ formatPace(p) }}</td>
                                <td class="p-4 text-center">
                                    <span v-if="p.status === 'dnf'" class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded font-bold dark:bg-red-900/30 dark:text-red-400">DNF</span>
                                    <span v-else-if="p.status === 'finished'" class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold dark:bg-green-900/30 dark:text-green-400">FINISH</span>
                                    <span v-else class="text-slate-400 text-xs dark:text-slate-500">RUNNING</span>
                                </td>
                                <td class="p-4 text-center no-print flex justify-center gap-2">
                                    <button v-if="p.status === 'finished'" @click="openMediaModal('certificate', p)" class="w-8 h-8 rounded-full bg-slate-900 hover:bg-black text-white flex items-center justify-center transition shadow-sm dark:bg-slate-700 dark:hover:bg-slate-600" title="Generate E-Certificate">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </button>
                                    <button v-if="p.status === 'finished'" @click="openMediaModal('poster', p)" class="w-8 h-8 rounded-full bg-pink-600 hover:bg-pink-700 text-white flex items-center justify-center transition shadow-sm" title="Generate Poster IG Story">
                                        <i class="fa-brands fa-instagram"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-if="dnfParticipants.length > 0" class="mt-8">
                <h3 class="text-red-500 font-bold mb-2">Did Not Finish (DNF)</h3>
                <div class="bg-red-50 rounded-xl p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-900/30">
                    <div v-for="p in dnfParticipants" class="text-red-700 text-sm flex gap-4 dark:text-red-400">
                        <span class="font-bold w-12">@{{ p.bib }}</span>
                        <span>@{{ p.name }}</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

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
            
            // Core Data Structure
            const participants = ref([]); 
            // { id: 'uuid', bib: '101', name: 'Budi', laps: [timestamp, timestamp], status: 'ready', totalTime: 0, recentlyScanned: false, lastScanTime: 0 }

            // Timer
            const timer = ref({
                running: false,
                startTime: null,
                elapsed: 0,
                interval: null
            });

            // Camera
            const camera = ref({
                active: false,
                scanner: null,
                lastScanMsg: '',
                busy: false
            });

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
                const res = await fetch(url, { ...options, headers });
                const data = await res.json().catch(() => null);
                if (!res.ok) {
                    const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Request gagal.';
                    throw new Error(msg);
                }
                return data;
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
                    const data = await apiFetchJson(`${apiBase}/races/${encodeURIComponent(String(currentRaceId.value))}`, { method: 'PUT', body: form });
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

                participants.value.push({
                    id: crypto.randomUUID(),
                    bib: bibValue,
                    name: newName.value,
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
                saveState();
                nextTick(() => inputBib.value.focus());
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
                            width: 128,
                            height: 128
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
                            timer.value.interval = setInterval(() => {
                                timer.value.elapsed = Date.now() - timer.value.startTime;
                            }, 50); // 50ms update rate

                            participants.value.forEach(p => {
                                if (p.status === 'ready') p.status = 'running';
                            });
                            saveState();
                        })
                        .catch((e) => alert(e?.message || 'Gagal simpan race ke database.'));
                }
            };

            const pauseRace = () => {
                if (timer.value.running) {
                    clearInterval(timer.value.interval);
                    timer.value.running = false;
                    saveState();
                }
            };

            const finishRace = () => {
                if (!confirm('Selesaikan sesi balapan ini? Aksi ini akan menyimpan hasil akhir dan tidak dapat dilanjutkan.')) return;
                
                pauseRace(); // Stop timer first

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
                    pauseRace();
                    timer.value.elapsed = 0;
                    participants.value.forEach(p => {
                        p.laps = [];
                        p.status = 'ready';
                        p.totalTime = 0;
                    });
                    currentRaceId.value = null;
                    currentSessionId.value = null;
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
                const date = new Date(ms);
                const m = date.getUTCHours() * 60 + date.getUTCMinutes();
                const s = date.getUTCSeconds();
                const cs = Math.floor(date.getUTCMilliseconds() / 10);
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}.${cs.toString().padStart(2, '0')}`;
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

            const recordLap = (id, source = 'manual') => {
                if (!timer.value.running) {
                    // alert('Start timer dulu!');
                    return;
                }

                const p = participants.value.find(p => p.id === id);
                if (!p || p.status !== 'running') return;

                // Debounce 3s to prevent double scan
                const now = Date.now();
                if (now - (p.lastScanTime || 0) < 3000) {
                    console.log('Debounced scan for ' + p.bib);
                    return;
                }
                
                playBeep();

                p.laps.push(now);
                p.lastScanTime = now;
                p.totalTime = timer.value.elapsed;

                if (currentSessionId.value) {
                    apiFetchJson(`${apiBase}/sessions/${encodeURIComponent(String(currentSessionId.value))}/laps`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            bib_number: String(p.bib ?? '').trim(),
                            total_time_ms: Math.max(0, Math.floor(p.totalTime || 0)),
                            recorded_at: new Date(now).toISOString(),
                        }),
                    }).catch(() => {});
                }
                
                // Highlight effect
                p.recentlyScanned = true;
                setTimeout(() => p.recentlyScanned = false, 2000);

                if (source === 'scanner') {
                    camera.value.lastScanMsg = `Scanned: ${p.bib} (${p.name})`;
                }
                saveState();
            };

            const getLastLapTime = (p) => {
                if (p.laps.length === 0) return '-';
                // Calculate split from last lap
                // Simple version: just show total time of last lap
                // Or: show split time? Let's show split.
                const lastLapTimestamp = p.laps[p.laps.length - 1];
                // Find lap time relative to start
                const relativeTime = lastLapTimestamp - timer.value.startTime;
                return formatTime(relativeTime);
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

            // QR Scanner
            const toggleScanner = () => {
                if (camera.value.active) {
                    if (camera.value.scanner) {
                        camera.value.scanner.stop().then(() => {
                            camera.value.active = false;
                            camera.value.lastScanMsg = '';
                            stopAutoMultiScan();
                            camera.value.scanner = null;
                        });
                    }
                } else {
                    camera.value.active = true;
                    nextTick(() => {
                        const html5QrCode = new Html5Qrcode("reader");
                        camera.value.scanner = html5QrCode;
                        
                        html5QrCode.start(
                            { facingMode: "environment" },
                            { fps: 20, qrbox: { width: 320, height: 320 } },
                            (decodedText, decodedResult) => {
                                // Handle scan
                                // Assuming text is BIB number
                                const bib = normalizeBib(decodedText);
                                if (!bib) return;
                                const p = participants.value.find(p => p.bib == bib);
                                if (p) {
                                    recordLap(p.id, 'scanner');
                                } else {
                                    camera.value.lastScanMsg = `Unknown BIB: ${bib}`;
                                }
                            },
                            (errorMessage) => {
                                // parse error, ignore
                            }
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
            };

            const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

            const getReaderVideo = () => {
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
                loadExistingRaces();
                window.addEventListener('keydown', onKeydown);
                // initTesseract(); // Tesseract not defined
            });

            onBeforeUnmount(() => {
                stopAutoMultiScan();
                window.removeEventListener('keydown', onKeydown);
                if (ocrWorker) {
                    ocrWorker.terminate();
                    ocrWorker = null;
                }
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
                startRace, pauseRace, finishRace, resetRace, toggleScanner, captureScan,
                activeParticipants, sortedResults, dnfParticipants,
                recordLap, markDNF, getLastLapTime, getInitials, formatTime,
                formatPace, getDeltaMs, formatDelta, getPointsInfo,
                isAuthenticated,
                isDarkMode, toggleDarkMode, playBeep,
                copyResultsLink,
                mediaModalOpen, mediaType, mediaParticipant, mediaLoading, mediaError, mediaPreviewUrl, mediaDownloadUrl, mediaFile,
                openMediaModal, closeMediaModal, onMediaBgChange, generateMedia, shareMedia,
                canNativeShare
            };
        }
    }).mount('#app');
</script>
</body>
</html>
