@php
    $modalPanelClass = $modalPanelClass ?? 'bg-white text-slate-900 border border-slate-200';
    $modalTitleClass = $modalTitleClass ?? 'text-slate-900';
    $modalAccentClass = $modalAccentClass ?? 'text-slate-900';
    $modalCloseClass = $modalCloseClass ?? 'bg-slate-900 text-white hover:bg-slate-800';

    $mootaBankAccounts = config('moota.bank_accounts') ?? [];
    $mootaInstructions = \App\Models\AppSettings::get('moota_instructions');
@endphp

<div id="rl-moota-modal" class="fixed inset-0 z-[9999] hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-4">
        <div class="w-full sm:max-w-lg rounded-2xl shadow-2xl {{ $modalPanelClass }}">
            <div class="p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold {{ $modalAccentClass }}">Pembayaran Moota</div>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight {{ $modalTitleClass }}">Instruksi Pembayaran</h3>
                        <p class="mt-1 text-sm text-slate-500" id="rl-moota-subtitle">Transfer tepat sesuai total agar terverifikasi otomatis.</p>
                    </div>
                    <button type="button" id="rl-moota-close-x" class="shrink-0 w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Yang Harus Dibayar</div>
                                <div class="mt-1 text-2xl font-black text-slate-900" id="rl-moota-total">Rp0</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kode Unik</div>
                                <div class="mt-1 text-lg font-black text-slate-900" id="rl-moota-unique">-</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-600">
                            ID Registrasi: <span class="font-mono font-bold text-slate-900" id="rl-moota-public-ref">-</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-bold text-slate-900">Status Pembayaran</div>
                            <span id="rl-moota-status-badge" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-yellow-100 text-yellow-800">
                                <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                                <span id="rl-moota-status-text">PENDING</span>
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-slate-600" id="rl-moota-status-help">Kami akan update otomatis saat transfer masuk.</div>
                        <div class="mt-3 hidden text-xs text-red-600 font-bold" id="rl-moota-status-error"></div>
                        <div class="mt-3 hidden" id="rl-moota-actions">
                            <button type="button" id="rl-moota-retry" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-bold">
                                Cek Lagi
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-sm font-black text-slate-900 mb-2">Rekening Tujuan</div>
                        <div class="space-y-2 text-sm" id="rl-moota-accounts"></div>
                        <div class="mt-3 text-xs text-slate-600">
                            {!! nl2br(e($mootaInstructions ?: '1) Transfer sesuai total (termasuk kode unik).'.PHP_EOL.'2) Gunakan salah satu rekening di atas.'.PHP_EOL.'3) Tunggu status berubah menjadi PAID.')) !!}
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" id="rl-moota-close" class="px-5 py-3 rounded-2xl font-black {{ $modalCloseClass }}">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function') {
            return;
        }

        const bankAccounts = @json($mootaBankAccounts);
        const statusUrlTemplate = @json(route('api.events.payments.status', ['slug' => $event->slug, 'transaction' => '__TX__']));

        const elModal = document.getElementById('rl-moota-modal');
        const elTotal = document.getElementById('rl-moota-total');
        const elUnique = document.getElementById('rl-moota-unique');
        const elPublicRef = document.getElementById('rl-moota-public-ref');
        const elAccounts = document.getElementById('rl-moota-accounts');
        const elBadge = document.getElementById('rl-moota-status-badge');
        const elStatusText = document.getElementById('rl-moota-status-text');
        const elStatusHelp = document.getElementById('rl-moota-status-help');
        const elStatusError = document.getElementById('rl-moota-status-error');
        const elActions = document.getElementById('rl-moota-actions');
        const elRetry = document.getElementById('rl-moota-retry');

        let pollTimer = null;
        let current = {
            transactionId: null,
            phone: null,
            startedAt: null,
            attempt: 0,
            lastStatus: null,
        };

        function formatIdr(amount) {
            const n = Number(amount || 0);
            try {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
            } catch (e) {
                return 'Rp' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        }

        function setBadge(status) {
            const s = String(status || '').toLowerCase().trim();

            elStatusError.classList.add('hidden');
            elActions.classList.add('hidden');

            if (s === 'paid') {
                elBadge.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-green-100 text-green-800';
                elBadge.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-green-500"></span><span id="rl-moota-status-text">PAID</span>';
                elStatusHelp.textContent = 'Pembayaran terkonfirmasi. Silakan cek email untuk e-voucher.';
                current.lastStatus = 'paid';
                stopPolling();
                return;
            }

            if (s === 'failed' || s === 'expired' || s === 'cancelled' || s === 'canceled') {
                elBadge.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-800';
                elBadge.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-red-500"></span><span id="rl-moota-status-text">FAILED</span>';
                elStatusHelp.textContent = 'Pembayaran gagal atau tidak valid.';
                current.lastStatus = 'failed';
                stopPolling();
                return;
            }

            elBadge.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-yellow-100 text-yellow-800';
            elBadge.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span><span id="rl-moota-status-text">PENDING</span>';
            elStatusHelp.textContent = 'Menunggu konfirmasi transfer. Status akan update otomatis.';
            current.lastStatus = 'pending';
        }

        function renderAccounts() {
            if (!elAccounts) return;
            elAccounts.innerHTML = '';

            (bankAccounts || []).forEach(function (acc) {
                const bank = (acc && acc.bank_type) ? String(acc.bank_type).toUpperCase() : 'BANK';
                const number = (acc && acc.account_number) ? String(acc.account_number) : '';
                const name = (acc && acc.name) ? String(acc.name) : '';

                const row = document.createElement('div');
                row.className = 'flex items-center justify-between gap-4 rounded-xl bg-slate-50 border border-slate-200 p-3';
                row.innerHTML = `
                    <div class="min-w-0">
                        <div class="font-black text-slate-900">${bank}</div>
                        <div class="text-xs text-slate-600 truncate">${name || '-'}</div>
                    </div>
                    <div class="font-mono font-black text-slate-900">${number || '-'}</div>
                `;

                elAccounts.appendChild(row);
            });
        }

        function stopPolling() {
            if (pollTimer) {
                clearTimeout(pollTimer);
                pollTimer = null;
            }
        }

        function scheduleNextPoll(ms) {
            stopPolling();
            pollTimer = setTimeout(pollOnce, ms);
        }

        function pollOnce() {
            if (!current.transactionId || !current.phone) {
                return;
            }

            const elapsedMs = Date.now() - (current.startedAt || Date.now());
            if (elapsedMs >= 5 * 60 * 1000) {
                elStatusError.textContent = 'Timeout: belum ada konfirmasi pembayaran. Kamu bisa cek lagi.';
                elStatusError.classList.remove('hidden');
                elActions.classList.remove('hidden');
                stopPolling();
                return;
            }

            current.attempt += 1;
            const url = statusUrlTemplate.replace('__TX__', encodeURIComponent(String(current.transactionId))) + '?phone=' + encodeURIComponent(String(current.phone));

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) {
                    if (!r.ok) {
                        return r.json().catch(function () {
                            throw new Error('HTTP ' + r.status);
                        }).then(function (j) {
                            throw new Error(j && j.message ? j.message : ('HTTP ' + r.status));
                        });
                    }
                    return r.json();
                })
                .then(function (data) {
                    const status = data && data.transaction ? data.transaction.payment_status : null;
                    setBadge(status);
                    if (current.lastStatus === 'pending') {
                        scheduleNextPoll(5000);
                    }
                })
                .catch(function (err) {
                    elStatusError.textContent = 'Gagal mengecek status: ' + (err && err.message ? err.message : 'koneksi terputus');
                    elStatusError.classList.remove('hidden');

                    const backoff = Math.min(30000, 5000 + (current.attempt * 1500));
                    scheduleNextPoll(backoff);
                });
        }

        function openModal(payload) {
            renderAccounts();

            const finalAmount = payload && payload.final_amount != null ? payload.final_amount : 0;
            const uniqueCode = payload && payload.unique_code != null ? payload.unique_code : 0;
            const publicRef = payload && payload.registration_id ? payload.registration_id : (payload && payload.public_ref ? payload.public_ref : '-');
            const phone = payload && payload.phone ? payload.phone : null;
            const transactionId = payload && payload.transaction_id ? payload.transaction_id : null;

            elTotal.textContent = formatIdr(finalAmount);
            elUnique.textContent = uniqueCode ? String(uniqueCode) : '-';
            elPublicRef.textContent = publicRef || '-';

            current.transactionId = transactionId;
            current.phone = phone;
            current.startedAt = Date.now();
            current.attempt = 0;
            current.lastStatus = null;

            setBadge('pending');

            elModal.classList.remove('hidden');
            elModal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('overflow-hidden');

            pollOnce();
        }

        function closeModal() {
            stopPolling();
            elModal.classList.add('hidden');
            elModal.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('overflow-hidden');
        }

        document.getElementById('rl-moota-close').addEventListener('click', closeModal);
        document.getElementById('rl-moota-close-x').addEventListener('click', closeModal);
        elModal.addEventListener('click', function (e) {
            if (e.target === elModal || e.target === elModal.firstElementChild) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !elModal.classList.contains('hidden')) {
                closeModal();
            }
        });
        elRetry.addEventListener('click', function () {
            elStatusError.classList.add('hidden');
            elActions.classList.add('hidden');
            current.startedAt = Date.now();
            current.attempt = 0;
            pollOnce();
        });

        window.RuangLariMoota = {
            open: openModal,
            close: closeModal,
        };
    })();
</script>
