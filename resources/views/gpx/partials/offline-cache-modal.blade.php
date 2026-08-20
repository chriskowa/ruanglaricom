<!-- Modal: Simpan Peta & Rute Offline (Cache Storage) -->
<div id="modal-gpx-offline-cache" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md hidden" style="z-index: 999999 !important;">
    <div class="relative w-full max-w-lg bg-[#0c121e] border border-slate-700 rounded-2xl p-6 shadow-2xl overflow-hidden text-slate-200 font-sans" style="background-color: #0c121e !important; opacity: 1 !important;">
        
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-lg shrink-0" style="background-color: #064e3b !important; color: #34d399 !important;">
                    <i class="fa-solid fa-mountain-sun"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Simpan Peta untuk Offline</h3>
                    <p class="text-xs text-slate-300">Gunakan navigasi GPS di gunung atau blind-spot tanpa kuota internet.</p>
                </div>
            </div>
            <button type="button" onclick="closeOfflineCacheModal()" class="text-slate-300 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition cursor-pointer" title="Tutup Modal">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Body Content -->
        <div class="mt-4 space-y-4">
            
            <!-- Dynamic Status Banner -->
            <div id="offline-status-banner" class="p-3.5 rounded-xl border flex items-start gap-3 text-xs bg-[#111724] border-slate-800" style="background-color: #111724 !important;">
                <div id="offline-status-icon" class="mt-0.5 text-base shrink-0 text-amber-400">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div id="offline-status-title" class="font-bold text-white">Status: Belum Tersimpan Offline</div>
                    <div id="offline-status-desc" class="text-slate-300 mt-0.5 leading-relaxed">
                        Unduh data lintasan & tile peta resolusi tinggi saat masih ada Wi-Fi/sinyal di basecamp.
                    </div>
                </div>
            </div>

            <!-- Features Checklist -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-xl bg-[#111724] border border-slate-800 flex items-center gap-2" style="background-color: #111724 !important;">
                    <i class="fa-solid fa-check text-emerald-400 text-xs"></i>
                    <span class="text-slate-200">Peta Layer Offline (Zoom 13-16)</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#111724] border border-slate-800 flex items-center gap-2" style="background-color: #111724 !important;">
                    <i class="fa-solid fa-check text-emerald-400 text-xs"></i>
                    <span class="text-slate-200">Live GPS & Off-Course Voice</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#111724] border border-slate-800 flex items-center gap-2" style="background-color: #111724 !important;">
                    <i class="fa-solid fa-check text-emerald-400 text-xs"></i>
                    <span class="text-slate-200">Elevasi Profil & Target Pace</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#111724] border border-slate-800 flex items-center gap-2" style="background-color: #111724 !important;">
                    <i class="fa-solid fa-check text-emerald-400 text-xs"></i>
                    <span class="text-slate-200">Ukuran Hemat (~1 - 3 MB)</span>
                </div>
            </div>

            <!-- Download Progress Bar (Hidden by default) -->
            <div id="offline-progress-wrap" class="hidden space-y-2 p-3.5 rounded-xl bg-[#06090e] border border-slate-800" style="background-color: #06090e !important;">
                <div class="flex justify-between items-center text-xs">
                    <span id="offline-progress-label" class="font-semibold text-white">Mengunduh gambar peta offline...</span>
                    <span id="offline-progress-percent" class="font-mono font-bold text-emerald-400">0%</span>
                </div>
                <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                    <div id="offline-progress-bar" class="h-full bg-emerald-500 transition-all duration-150 rounded-full" style="width: 0%;"></div>
                </div>
                <div class="flex justify-between text-[11px] text-slate-400 font-mono">
                    <span id="offline-progress-detail">Menyiapkan koordinat rute...</span>
                    <span id="offline-progress-count">0 / 0</span>
                </div>
            </div>

            <!-- Tips Banner for Trail Runners -->
            <div class="p-3 rounded-xl bg-[#111724] border border-slate-800 text-[11px] text-slate-300 flex items-start gap-2.5" style="background-color: #111724 !important;">
                <i class="fa-solid fa-compass text-[#FC4C02] text-xs mt-0.5 shrink-0"></i>
                <p class="leading-relaxed">
                    <strong class="text-white">Tips di Gunung:</strong> Nyalakan mode <em>Airplane Mode</em> untuk menghemat baterai HP hingga 3x lipat saat menggunakan navigasi GPS ini di jalur pendakian.
                </p>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between gap-3 pt-4 mt-5 border-t border-slate-800">
            <button type="button" 
                    id="btn-delete-offline-cache" 
                    onclick="deleteOfflineRouteCache()" 
                    class="hidden px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 border border-rose-500/30 transition cursor-pointer">
                <i class="fa-solid fa-trash-can mr-1"></i> Hapus Offline
            </button>
            <div class="flex items-center gap-2 ml-auto">
                <button type="button" onclick="closeOfflineCacheModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition cursor-pointer">
                    Tutup
                </button>
                <button type="button" 
                        id="btn-start-offline-download" 
                        onclick="startOfflineRouteCaching()" 
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition flex items-center gap-1.5 cursor-pointer border border-slate-700 shadow-md">
                    <i class="fa-solid fa-cloud-arrow-down text-xs"></i>
                    <span id="btn-start-offline-text">Simpan Rute Sekarang</span>
                </button>
            </div>
        </div>

    </div>
</div>
