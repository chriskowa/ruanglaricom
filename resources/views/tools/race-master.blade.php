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

        /* Custom UI Tweaks */
        .glass-panel { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.05); }
        .dark .glass-panel { background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.05); }
        .scanner-active { border: 4px solid #10B981; }
        
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        [v-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen transition-colors duration-300 dark:bg-slate-900 dark:text-slate-100">

<div id="app" :class="{'dark': isDarkMode}" v-cloak>
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
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1 dark:text-slate-400">Pilih Event EO</label>
                        <select v-model="selectedEoEventId" @change="onEoEventChange" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-sm dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors">
                            <option value="">-- Pilih Event Anda --</option>
                            <option v-for="ev in eoEvents" :key="ev.id" :value="ev.id">
                                @{{ ev.name }} (@{{ ev.start_at || 'Belum ada tanggal' }})
                            </option>
                        </select>
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
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan peserta beserta foto wajah untuk deteksi AI otomatis saat melewati garis finish.</p>
                    </div>
                </div>

                <!-- Face Photo Capture & Biometric Enrollment Bar -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/90 rounded-2xl border border-slate-200 dark:border-slate-700 mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full border-2 border-indigo-500 overflow-hidden bg-slate-200 dark:bg-slate-800 flex items-center justify-center shrink-0 shadow-inner">
                            <img v-if="newFacePhoto" :src="newFacePhoto" class="w-full h-full object-cover" alt="Avatar">
                            <i v-else class="fa-solid fa-user text-slate-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Foto Wajah Peserta</div>
                            <div v-if="faceModelLoading" class="text-[11px] text-indigo-500 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-circle-notch fa-spin"></i> Memuat AI Face Model...
                            </div>
                            <div v-else-if="newFaceProcessing" class="text-[11px] text-indigo-500 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-circle-notch fa-spin"></i> Mengekstrak Biometrik Wajah...
                            </div>
                            <div v-else-if="newFaceDescriptor" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> Biometrik Wajah Terdaftar (AI Ready)
                            </div>
                            <div v-else class="text-[11px] text-slate-500 dark:text-slate-400">
                                Opsional: Foto wajah untuk pengenalan otomatis saat finish
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="openFaceCaptureModal" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fa-solid fa-camera"></i> Ambil Foto (Kamera)
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
                                        <span v-if="p.faceDescriptor" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            Face AI
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

    <!-- Face Capture Webcam Modal for Participant Enrollment -->
    <div v-if="faceCaptureModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80" @click.self="closeFaceCaptureModal">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5 w-full max-w-md shadow-2xl relative text-white">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="fa-solid fa-camera text-indigo-400"></i> Ambil Foto Wajah Peserta
                </h3>
                <button type="button" @click="closeFaceCaptureModal" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            
            <div class="relative bg-black aspect-square rounded-xl overflow-hidden mb-4 flex items-center justify-center border border-slate-800">
                <video id="faceCaptureVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <!-- Oval Face Guide Marker -->
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-48 h-60 rounded-[50%] border-2 border-dashed border-indigo-400/80 bg-indigo-500/10"></div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" @click="closeFaceCaptureModal" class="flex-1 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                    Batal
                </button>
                <button type="button" @click="snapFacePhoto" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-lg">
                    <i class="fa-solid fa-camera"></i> Jepret Foto
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

            // Face Biometrics State
            const newFacePhoto = ref('');
            const newFaceDescriptor = ref(null);
            const newFaceProcessing = ref(false);
            const faceModelLoading = ref(false);
            const faceCaptureModalOpen = ref(false);
            let faceCaptureStream = null;
            let faceModelsLoaded = false;
            
            // Core Data Structure
            const participants = ref([]); 
            // { id: 'uuid', bib: '101', name: 'Budi', photoUrl: '', faceDescriptor: [], laps: [timestamp, timestamp], status: 'ready', totalTime: 0, recentlyScanned: false, lastScanTime: 0 }

            // Timer
            const timer = ref({
                running: false,
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
                enableFaceAi: true,
                enableOcr: true,
                enableBeep: true,
                stationMode: 'master', // 'master' | 'satellite'
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

            // EO Events Integration
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

            const openFaceCaptureModal = async () => {
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

            const snapFacePhoto = async () => {
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
                newFacePhoto.value = dataUrl;
                closeFaceCaptureModal();

                newFaceProcessing.value = true;
                const desc = await extractFaceDescriptorFromImage(canvas);
                newFaceProcessing.value = false;
                if (desc) {
                    newFaceDescriptor.value = desc;
                }
            };

            const onFacePhotoUpload = async (e) => {
                const file = e.target?.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = async (evt) => {
                    const dataUrl = evt.target.result;
                    newFacePhoto.value = dataUrl;
                    newFaceProcessing.value = true;
                    
                    const img = new Image();
                    img.onload = async () => {
                        const desc = await extractFaceDescriptorFromImage(img);
                        newFaceProcessing.value = false;
                        if (desc) {
                            newFaceDescriptor.value = desc;
                        }
                    };
                    img.src = dataUrl;
                };
                reader.readAsDataURL(file);
            };

            const clearNewFacePhoto = () => {
                newFacePhoto.value = '';
                newFaceDescriptor.value = null;
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
                    photoUrl: newFacePhoto.value || '',
                    faceDescriptor: newFaceDescriptor.value || null,
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
                newFacePhoto.value = '';
                newFaceDescriptor.value = null;
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
                        })
                        .catch((e) => alert(e?.message || 'Gagal simpan race ke database.'));
                }
            };

            const pauseRace = () => {
                if (timer.value.running) {
                    clearInterval(timer.value.interval);
                    timer.value.running = false;
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
                    queueEnqueueLap(String(p.bib ?? '').trim(), Math.max(0, Math.floor(p.totalTime || 0)), new Date(now).toISOString());
                    queueFlush();
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

            // AI Model Loader (COCO-SSD with MobileNet)
            const loadAiModel = async () => {
                if (cocoModel) {
                    camera.value.aiModelReady = true;
                    return cocoModel;
                }
                if (typeof cocoSsd === 'undefined') return null;
                camera.value.aiModelLoading = true;
                try {
                    cocoModel = await cocoSsd.load({ base: 'mobilenet_v2' });
                    camera.value.aiModelReady = true;
                    camera.value.aiModelLoading = false;
                    return cocoModel;
                } catch (e) {
                    console.error('Failed to load COCO-SSD:', e);
                    camera.value.aiModelLoading = false;
                    return null;
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
                try {
                    const bibs = await decodeMultipleFromVideoFrame(video);
                    if (bibs && bibs.length > 0) {
                        detectedBib = bibs[0];
                        matchMethod = 'qr';
                    }
                } catch (e) {}

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

                // Engine 3: Face Recognition AI (Biometrics)
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
                                if (p.faceDescriptor && Array.isArray(p.faceDescriptor)) {
                                    const dist = faceapi.euclideanDistance(liveDesc, p.faceDescriptor);
                                    if (dist < minDistance) {
                                        minDistance = dist;
                                        bestMatch = p;
                                    }
                                }
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
                            return;
                        }
                        recordLap(participant.id, 'ai_vision');
                        participant.status = 'finished';
                        camera.value.lastScanMsg = `FINISH ${tag}: BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`;
                    } else {
                        // Multi-Lap Mode with Cooldown Filter
                        const now = Date.now();
                        const cooldownMs = raceSettings.value.minLapCooldownSec * 1000;
                        if (participant.lastScanTime && (now - participant.lastScanTime < cooldownMs)) {
                            camera.value.lastScanMsg = `BIB #${participant.bib} dalam masa cooldown putaran`;
                            return;
                        }
                        recordLap(participant.id, 'ai_vision');
                        if (participant.laps.length >= raceSettings.value.targetLaps) {
                            participant.status = 'finished';
                            camera.value.lastScanMsg = `FINISH ${tag} (Lap ${participant.laps.length}): BIB #${participant.bib} (${participant.name}) • ${formattedTimeStr}`;
                        } else {
                            camera.value.lastScanMsg = `LAP ${participant.laps.length}/${raceSettings.value.targetLaps} ${tag}: BIB #${participant.bib} • ${formattedTimeStr}`;
                        }
                    }
                } else if (detectedBib) {
                    camera.value.lastScanMsg = `Crossing: Unknown BIB #${detectedBib} • ${formattedTimeStr}`;
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
                // Preload AI Model in background
                loadAiModel();
            });

            onBeforeUnmount(() => {
                stopAiCamera();
                stopAutoMultiScan();
                window.removeEventListener('keydown', onKeydown);
                if (queueFlushInterval.value) clearInterval(queueFlushInterval.value);
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
                canNativeShare,
                eoEvents, eoEventsLoading, selectedEoEventId, selectedEoCategoryId, importingEoParticipants, selectedEoEvent,
                onEoEventChange, importEoEventParticipants,
                cameraSettingsOpen, raceSettings,
                newFacePhoto, newFaceDescriptor, newFaceProcessing, faceModelLoading, faceCaptureModalOpen,
                openFaceCaptureModal, closeFaceCaptureModal, snapFacePhoto, onFacePhotoUpload, clearNewFacePhoto,
                liveFinishFeed, assignBibModalOpen, selectedUnassignedFinish,
                handleCanvasMouseDown, handleCanvasMouseMove, handleCanvasMouseUp,
                handleCanvasTouchStart, handleCanvasTouchMove, handleCanvasTouchEnd,
                setLinePreset, switchCameraDevice, switchCameraMode,
                openAssignBibModal, confirmAssignBib
            };
        }
    }).mount('#app');
</script>
</body>
</html>
