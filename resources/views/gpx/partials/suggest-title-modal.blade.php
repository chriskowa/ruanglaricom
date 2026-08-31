<!-- Modal: Sarankan Nama Rute GPX -->
<div id="modal-gpx-suggest-title" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/70 hidden">
    <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-2xl text-slate-200">
        
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-800">
            <div>
                <h3 class="text-base font-semibold text-white">Sarankan Nama Rute</h3>
                <p class="text-xs text-slate-400 mt-0.5">Bantu komunitas dengan nama rute yang lebih akurat atau populer.</p>
            </div>
            <button type="button" onclick="closeSuggestTitleModal()" class="text-slate-400 hover:text-white transition text-lg leading-none" title="Tutup Modal">
                &times;
            </button>
        </div>

        <!-- Form Body -->
        <form id="form-gpx-suggest-title" onsubmit="submitSuggestTitle(event)" class="mt-4 space-y-4">
            <input type="hidden" id="suggest-gpx-id" name="gpx_id" value="">

            <!-- Current Title (Read Only) -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Nama Rute Saat Ini</label>
                <div id="suggest-current-title" class="px-3 py-2 rounded-md bg-slate-950 border border-slate-800 text-xs font-medium text-white break-words">
                    -
                </div>
            </div>

            <!-- Proposed Title Input -->
            <div>
                <label for="suggest-proposed-title" class="block text-xs font-medium text-slate-300 mb-1">
                    Usulan Nama Rute Baru <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       id="suggest-proposed-title" 
                       name="proposed_title" 
                       required 
                       minlength="3" 
                       maxlength="255" 
                       placeholder="Contoh: Loop Senayan - Sudirman 10K" 
                       class="w-full px-3 py-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition">
                <p class="text-xs text-slate-400 mt-1">Gunakan nama yang jelas, mudah dikenali, atau mencakup rute utama.</p>
            </div>

            <!-- Reason / Notes -->
            <div>
                <label for="suggest-reason" class="block text-xs font-medium text-slate-300 mb-1">
                    Alasan / Catatan Tambahan <span class="text-slate-400">(opsional)</span>
                </label>
                <textarea id="suggest-reason" 
                          name="reason" 
                          rows="3" 
                          maxlength="500" 
                          placeholder="Contoh: Rute ini lebih populer disebut Loop Senayan oleh komunitas lari setempat." 
                          class="w-full px-3 py-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white placeholder:text-slate-400 focus:ring-1 focus:ring-slate-500 outline-none transition resize-none leading-relaxed"></textarea>
            </div>

            <!-- Notice -->
            <div class="p-3 rounded-md bg-slate-950 border border-slate-800 text-xs text-slate-300 leading-relaxed">
                Saran penamaan akan ditinjau oleh Admin RuangLari sebelum diterapkan secara resmi pada katalog rute GPX.
            </div>

            <!-- Error Message Box -->
            <div id="suggest-error-box" class="hidden p-3 rounded-md bg-rose-950 border border-rose-800 text-rose-300 text-xs font-medium"></div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeSuggestTitleModal()" class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-suggest-title" class="px-4 py-2 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition">
                    <span id="btn-submit-suggest-text">Kirim Saran</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentSuggestGpxId = null;

function openSuggestTitleModal(gpxId, currentTitle, cityName) {
    const isAuth = {{ auth()->check() ? 'true' : 'false' }};
    if (!isAuth) {
        if (typeof openLoginModal === 'function') {
            openLoginModal();
        } else {
            window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
        }
        return;
    }

    currentSuggestGpxId = gpxId;
    document.getElementById('suggest-gpx-id').value = gpxId;
    document.getElementById('suggest-current-title').textContent = currentTitle + (cityName ? ' (' + cityName + ')' : '');
    document.getElementById('suggest-proposed-title').value = '';
    document.getElementById('suggest-reason').value = '';
    
    const errBox = document.getElementById('suggest-error-box');
    if (errBox) {
        errBox.classList.add('hidden');
        errBox.textContent = '';
    }

    const modal = document.getElementById('modal-gpx-suggest-title');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => document.getElementById('suggest-proposed-title')?.focus(), 100);
    }
}

function closeSuggestTitleModal() {
    const modal = document.getElementById('modal-gpx-suggest-title');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function submitSuggestTitle(e) {
    e.preventDefault();
    if (!currentSuggestGpxId) return;

    const proposedTitle = document.getElementById('suggest-proposed-title').value.trim();
    const reason = document.getElementById('suggest-reason').value.trim();
    const btnSubmit = document.getElementById('btn-submit-suggest-title');
    const btnText = document.getElementById('btn-submit-suggest-text');
    const errBox = document.getElementById('suggest-error-box');

    if (!proposedTitle) {
        if (errBox) {
            errBox.textContent = 'Nama rute usulan wajib diisi.';
            errBox.classList.remove('hidden');
        }
        return;
    }

    if (errBox) errBox.classList.add('hidden');
    btnSubmit.disabled = true;
    btnText.textContent = 'Mengirim...';

    const url = "{{ url('/gpx') }}/" + currentSuggestGpxId + "/suggest-title";

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            proposed_title: proposedTitle,
            reason: reason
        })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Terjadi kesalahan saat mengirim saran.');
        }
        return data;
    })
    .then(data => {
        closeSuggestTitleModal();
        if (typeof showGpxToast === 'function') {
            showGpxToast(data.message || 'Saran nama rute berhasil dikirim untuk ditinjau.', 'success');
        } else {
            alert(data.message || 'Saran nama rute berhasil dikirim untuk ditinjau.');
        }
    })
    .catch(err => {
        if (errBox) {
            errBox.textContent = err.message || 'Gagal mengirim saran.';
            errBox.classList.remove('hidden');
        }
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnText.textContent = 'Kirim Saran';
    });
}
</script>
