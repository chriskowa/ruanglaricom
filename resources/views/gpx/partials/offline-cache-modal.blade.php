<!-- Modal: Simpan Peta & Rute Offline (Cache Storage) -->
<div id="modal-gpx-offline-cache" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/70 hidden">
    <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-2xl overflow-hidden text-slate-200 font-sans">
        
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-800">
            <div>
                <h3 class="text-base font-semibold text-white">Simpan Peta Offline</h3>
                <p class="text-xs text-slate-400 mt-0.5">Gunakan navigasi GPS di gunung atau blind-spot tanpa kuota internet.</p>
            </div>
            <button type="button" onclick="closeOfflineCacheModal()" class="text-slate-400 hover:text-white transition text-lg leading-none" title="Tutup Modal">
                &times;
            </button>
        </div>

        <!-- Body Content -->
        <div class="mt-4 space-y-4">
            
            <!-- Dynamic Status Banner -->
            <div id="offline-status-banner" class="p-3 rounded-md border flex items-start gap-3 text-xs bg-slate-950 border-slate-800">
                <div id="offline-status-icon" class="mt-0.5 text-sm shrink-0 text-amber-400 font-bold">&#9432;</div>
                <div class="flex-1 min-w-0">
                    <div id="offline-status-title" class="font-semibold text-white">Status: Belum Tersimpan Offline</div>
                    <div id="offline-status-desc" class="text-slate-300 mt-0.5 leading-relaxed text-xs">
                        Unduh data lintasan & tile peta resolusi tinggi saat masih ada Wi-Fi/sinyal di basecamp.
                    </div>
                </div>
            </div>

            <!-- Features Checklist -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-md bg-slate-950 border border-slate-800 text-slate-300">
                    Peta Layer Offline (Zoom 13-16)
                </div>
                <div class="p-2.5 rounded-md bg-slate-950 border border-slate-800 text-slate-300">
                    Live GPS & Off-Course Voice
                </div>
                <div class="p-2.5 rounded-md bg-slate-950 border border-slate-800 text-slate-300">
                    Elevasi Profil & Target Pace
                </div>
                <div class="p-2.5 rounded-md bg-slate-950 border border-slate-800 text-slate-300">
                    Ukuran Hemat (~1 - 3 MB)
                </div>
            </div>

            <!-- Download Progress Bar -->
            <div id="offline-progress-wrap" class="hidden space-y-2 p-3 rounded-md bg-slate-950 border border-slate-800">
                <div class="flex justify-between items-center text-xs">
                    <span id="offline-progress-label" class="font-medium text-white">Mengunduh gambar peta offline...</span>
                    <span id="offline-progress-pct" class="font-mono font-semibold text-white">0%</span>
                </div>
                <div class="w-full h-2 rounded-sm bg-slate-800 overflow-hidden">
                    <div id="offline-progress-bar" class="h-full bg-neon transition-all duration-150 rounded-sm" style="width: 0%;"></div>
                </div>
            </div>

            <!-- Action Area / Buttons -->
            <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-3">
                <button type="button" id="btn-delete-offline-cache" onclick="deleteOfflineCache()" class="hidden px-3 py-1.5 rounded-md bg-rose-950 hover:bg-rose-900 border border-rose-800 text-rose-300 text-xs font-medium transition">
                    Hapus Peta Tersimpan
                </button>
                <div class="flex-1"></div>
                <button type="button" onclick="closeOfflineCacheModal()" class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition">
                    Tutup
                </button>
                <button type="button" id="btn-start-offline-cache" onclick="startOfflineCache()" class="px-4 py-2 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition">
                    <span id="btn-cache-label">Mulai Unduh Offline</span>
                </button>
            </div>
        </div>
    </div>
</div>
