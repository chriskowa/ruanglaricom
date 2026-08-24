@auth
@if(auth()->user()->isRunner() || auth()->user()->isCoach())
<div id="global-pb-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 hidden">
    <div class="bg-slate-900 border border-slate-700 w-11/12 max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col my-8">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-slate-950">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-neon">
                    <i class="fas fa-stopwatch text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Update Personal Best</h3>
                    <p class="text-[11px] text-slate-400">Perbarui rekor waktu lari & kalkulasi otomatis VDOT</p>
                </div>
            </div>
            <button type="button" onclick="closeGlobalPbModal()" class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Body -->
        <form id="global-pb-form" onsubmit="submitGlobalPb(event)" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
            @csrf

            <div id="global-pb-alert" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- 5K PB -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">5K PB (HH:MM:SS)</label>
                    <input type="text" name="pb_5k" id="global_pb_5k" value="{{ auth()->user()->pb_5k }}" placeholder="00:25:00" pattern="^([0-9]{2}:)?[0-5]?[0-9]:[0-5][0-9]$" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600">
                    <span class="text-[10px] text-slate-500 mt-1 block">Contoh: 00:24:30</span>
                </div>

                <!-- 10K PB -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">10K PB (HH:MM:SS)</label>
                    <input type="text" name="pb_10k" id="global_pb_10k" value="{{ auth()->user()->pb_10k }}" placeholder="00:52:00" pattern="^([0-9]{2}:)?[0-5]?[0-9]:[0-5][0-9]$" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600">
                    <span class="text-[10px] text-slate-500 mt-1 block">Contoh: 00:52:15</span>
                </div>

                <!-- Half Marathon (21K) PB -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Half Marathon / 21K</label>
                    <input type="text" name="pb_hm" id="global_pb_hm" value="{{ auth()->user()->pb_hm }}" placeholder="01:55:00" pattern="^([0-9]{2}:)?[0-5]?[0-9]:[0-5][0-9]$" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600">
                    <span class="text-[10px] text-slate-500 mt-1 block">Contoh: 01:55:00</span>
                </div>

                <!-- Full Marathon (42K) PB -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Full Marathon / 42K</label>
                    <input type="text" name="pb_fm" id="global_pb_fm" value="{{ auth()->user()->pb_fm }}" placeholder="04:15:00" pattern="^([0-9]{2}:)?[0-5]?[0-9]:[0-5][0-9]$" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600">
                    <span class="text-[10px] text-slate-500 mt-1 block">Contoh: 04:15:00</span>
                </div>
            </div>

            <!-- Balke Test (15 Min) -->
            <div class="border-t border-slate-800 pt-3">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Balke Test 15-Menit (Jarak dalam Meter)</label>
                <input type="number" name="pb_balke" id="global_pb_balke" value="{{ auth()->user()->pb_balke }}" min="0" max="10000" placeholder="e.g. 3200" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600">
                <span class="text-[10px] text-slate-500 mt-1 block">Opsional: Masukkan total jarak tempuh lari dalam 15 menit</span>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <button type="button" onclick="closeGlobalPbModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors text-xs font-bold">
                    Batal
                </button>
                <button type="submit" id="global-pb-submit-btn" class="px-5 py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all text-xs flex items-center gap-2 shadow-lg shadow-neon/10">
                    <i class="fas fa-save text-xs"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openGlobalPbModal() {
        const modal = document.getElementById('global-pb-modal');
        if (modal) {
            modal.classList.remove('hidden');
            const alertBox = document.getElementById('global-pb-alert');
            if (alertBox) alertBox.classList.add('hidden');
        }
    }

    function closeGlobalPbModal() {
        const modal = document.getElementById('global-pb-modal');
        if (modal) modal.classList.add('hidden');
    }

    // Close on click outside modal content
    document.getElementById('global-pb-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeGlobalPbModal();
        }
    });

    // Helper to format MM:SS or H:MM:SS to HH:MM:SS
    function normalizeTimeFormat(val) {
        if (!val) return null;
        val = val.trim();
        if (!val) return null;
        const parts = val.split(':');
        if (parts.length === 2) {
            return `00:${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
        } else if (parts.length === 3) {
            return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}:${parts[2].padStart(2, '0')}`;
        }
        return val;
    }

    async function submitGlobalPb(e) {
        e.preventDefault();
        const btn = document.getElementById('global-pb-submit-btn');
        const alertBox = document.getElementById('global-pb-alert');
        
        const pb5k = normalizeTimeFormat(document.getElementById('global_pb_5k')?.value);
        const pb10k = normalizeTimeFormat(document.getElementById('global_pb_10k')?.value);
        const pbHm = normalizeTimeFormat(document.getElementById('global_pb_hm')?.value);
        const pbFm = normalizeTimeFormat(document.getElementById('global_pb_fm')?.value);
        const pbBalke = document.getElementById('global_pb_balke')?.value ? parseInt(document.getElementById('global_pb_balke').value) : null;

        const payload = {};
        if (pb5k) payload.pb_5k = pb5k;
        if (pb10k) payload.pb_10k = pb10k;
        if (pbHm) payload.pb_hm = pbHm;
        if (pbFm) payload.pb_fm = pbFm;
        if (pbBalke) payload.pb_balke = pbBalke;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Menyimpan...';
        alertBox.className = 'hidden';

        try {
            const resp = await fetch('{{ route("runner.calendar.update-pb") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const res = await resp.json();

            if (res.success || resp.ok) {
                alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-950 border border-emerald-800 text-emerald-400 block';
                alertBox.textContent = 'Personal Best berhasil diperbarui! VDOT otomatis dihitung ulang.';
                
                setTimeout(() => {
                    closeGlobalPbModal();
                    if (window.location.pathname.includes('/runner/dashboard') || window.location.pathname.includes('/runner/calendar') || window.location.pathname.includes('/profile')) {
                        window.location.reload();
                    }
                }, 1000);
            } else {
                alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-red-950 border border-red-800 text-red-400 block';
                alertBox.textContent = res.message || 'Gagal memperbarui Personal Best. Periksa format waktu (HH:MM:SS).';
            }
        } catch (err) {
            alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-red-950 border border-red-800 text-red-400 block';
            alertBox.textContent = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save text-xs"></i> <span>Simpan Perubahan</span>';
        }
    }
</script>
@endif
@endauth
