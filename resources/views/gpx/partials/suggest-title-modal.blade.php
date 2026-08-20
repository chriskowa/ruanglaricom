<!-- Modal: Sarankan Nama Rute GPX -->
<div id="modal-gpx-suggest-title" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/90 hidden" style="z-index: 99999 !important;">
    <div class="relative w-full max-w-lg bg-[#0c121e] border border-slate-700 rounded-2xl p-6 shadow-2xl overflow-hidden text-slate-200" style="background-color: #0c121e !important; opacity: 1 !important;">
        
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 text-white flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Sarankan Nama Rute</h3>
                    <p class="text-xs text-slate-400">Bantu komunitas dengan nama rute yang lebih akurat atau populer.</p>
                </div>
            </div>
            <button type="button" onclick="closeSuggestTitleModal()" class="text-slate-300 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition cursor-pointer" title="Tutup Modal">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="form-gpx-suggest-title" onsubmit="submitSuggestTitle(event)" class="mt-4 space-y-4">
            <input type="hidden" id="suggest-gpx-id" name="gpx_id" value="">

            <!-- Current Title (Read Only) -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Rute Saat Ini</label>
                <div id="suggest-current-title" class="px-3.5 py-2.5 rounded-xl bg-[#111724] border border-slate-700 text-xs font-bold text-white break-words" style="background-color: #111724 !important;">
                    -
                </div>
            </div>

            <!-- Proposed Title Input -->
            <div>
                <label for="suggest-proposed-title" class="block text-xs font-bold text-white mb-1">
                    Usulan Nama Rute Baru <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       id="suggest-proposed-title" 
                       name="proposed_title" 
                       required 
                       minlength="3" 
                       maxlength="255" 
                       placeholder="Contoh: Loop Senayan - Sudirman 10K" 
                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#111724] border border-slate-700 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition" style="background-color: #111724 !important;">
                <p class="text-[11px] text-slate-400 mt-1">Gunakan nama yang jelas, mudah dikenali, atau mencakup rute utama.</p>
            </div>

            <!-- Reason / Notes -->
            <div>
                <label for="suggest-reason" class="block text-xs font-semibold text-slate-300 mb-1">
                    Alasan / Catatan Tambahan <span class="text-slate-400 text-[11px]">(opsional)</span>
                </label>
                <textarea id="suggest-reason" 
                          name="reason" 
                          rows="3" 
                          maxlength="500" 
                          placeholder="Contoh: Rute ini lebih populer disebut Loop Senayan oleh komunitas lari setempat." 
                          class="w-full px-3.5 py-2.5 rounded-xl bg-[#111724] border border-slate-700 text-xs text-white placeholder:text-slate-400 focus:outline-none focus:border-slate-500 transition resize-none" style="background-color: #111724 !important;"></textarea>
            </div>

            <!-- Notice / Information -->
            <div class="p-3 rounded-xl bg-[#111724] border border-slate-800 flex items-start gap-2.5 text-xs text-slate-300" style="background-color: #111724 !important;">
                <i class="fa-solid fa-circle-info text-slate-400 mt-0.5 shrink-0 text-sm"></i>
                <p class="leading-relaxed">
                    Saran penamaan akan ditinjau oleh Admin RuangLari sebelum diterapkan secara resmi pada katalog rute GPX.
                </p>
            </div>

            <!-- Error Message Box -->
            <div id="suggest-error-box" class="hidden p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-medium"></div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeSuggestTitleModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-submit-suggest-title" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer border border-slate-700 shadow-md">
                    <span id="btn-submit-suggest-text">Kirim Saran</span>
                    <i class="fa-solid fa-paper-plane text-[11px]"></i>
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
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Saran Terkirim!',
                text: data.message || 'Terima kasih atas sarannya. Admin akan segera meninjau usulan nama rute ini.',
                background: '#0c121e',
                color: '#fff',
                confirmButtonColor: '#ccff00',
                customClass: {
                    confirmButton: 'text-slate-950 font-bold'
                }
            });
        } else {
            alert(data.message || 'Saran nama rute berhasil dikirim untuk ditinjau admin.');
        }
    })
    .catch(err => {
        if (errBox) {
            errBox.textContent = err.message;
            errBox.classList.remove('hidden');
        } else {
            alert(err.message);
        }
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnText.textContent = 'Kirim Saran';
    });
}
</script>
