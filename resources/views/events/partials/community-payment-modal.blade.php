@php
    $modalPanelClass = $modalPanelClass ?? 'bg-slate-900 text-slate-100 border border-slate-700';
    $modalTitleClass = $modalTitleClass ?? 'text-white';
    $modalAccentClass = $modalAccentClass ?? 'text-neon';
    $modalCloseClass = $modalCloseClass ?? 'bg-neon text-dark hover:bg-neon/90';
    $mootaBankAccounts = config('moota.bank_accounts') ?? [];
    $mootaInstructions = \App\Models\AppSettings::get('moota_instructions');
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div id="rl-community-payment-modal" class="fixed inset-0 z-[9999] hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/70"></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-4">
        <div class="w-full sm:max-w-lg rounded-2xl shadow-2xl {{ $modalPanelClass }}">
            <div class="p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold {{ $modalAccentClass }}" id="rl-community-payment-title">Pembayaran</div>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight {{ $modalTitleClass }}">Instruksi Pembayaran</h3>
                        <p class="mt-1 text-sm text-slate-400" id="rl-community-payment-subtitle">Status akan update otomatis setelah pembayaran terdeteksi.</p>
                    </div>
                    <button type="button" id="rl-community-payment-close-x" class="shrink-0 w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3">
                    <div class="rounded-xl border border-slate-700 bg-slate-950/40 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Yang Harus Dibayar</div>
                                <div class="mt-1 text-2xl font-black text-white" id="rl-community-payment-total">Rp0</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Unik</div>
                                <div class="mt-1 text-lg font-black text-white" id="rl-community-payment-unique">-</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            ID Registrasi: <span class="font-mono font-bold text-slate-200" id="rl-community-payment-public-ref">-</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-700 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-bold text-slate-200">Status Pembayaran</div>
                            <span id="rl-community-payment-status-badge" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-yellow-500/15 text-yellow-200 border border-yellow-500/30">
                                <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                                <span id="rl-community-payment-status-text">PENDING</span>
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-slate-400" id="rl-community-payment-status-help">Menunggu konfirmasi pembayaran.</div>
                        <div class="mt-3 hidden text-xs text-red-300 font-bold" id="rl-community-payment-status-error"></div>
                        <div class="mt-3 hidden" id="rl-community-payment-actions">
                            <button type="button" id="rl-community-payment-retry" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-bold">
                                Cek Lagi
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-700 p-4 hidden" id="rl-community-payment-qris">
                        <div class="text-sm font-black text-slate-200 mb-2">QRIS Dinamis</div>
                        <div class="bg-white rounded-xl p-3 flex items-center justify-center" id="rl-community-payment-qris-box">
                            <div id="rl-community-payment-qris-code"></div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400 break-all font-mono" id="rl-community-payment-qris-payload"></div>
                    </div>

                    <div class="rounded-xl border border-slate-700 p-4 hidden" id="rl-community-payment-moota">
                        <div class="text-sm font-black text-slate-200 mb-2">Rekening Tujuan</div>
                        <div class="space-y-2 text-sm" id="rl-community-payment-accounts"></div>
                        <div class="mt-3 text-xs text-slate-400">
                            {!! nl2br(e($mootaInstructions ?: '1) Transfer sesuai total (termasuk kode unik).'.PHP_EOL.'2) Gunakan salah satu rekening di atas.'.PHP_EOL.'3) Tunggu status berubah menjadi PAID.')) !!}
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" id="rl-community-payment-close" class="px-5 py-3 rounded-2xl font-black {{ $modalCloseClass }}">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.RuangLariCommunityPayment && typeof window.RuangLariCommunityPayment.open === 'function') {
            return;
        }

        const bankAccounts = @json($mootaBankAccounts);
        const statusUrlTemplate = @json(route('api.events.payments.status', ['slug' => $event->slug, 'transaction' => '__TX__']));

        const elModal = document.getElementById('rl-community-payment-modal');
        const elTitle = document.getElementById('rl-community-payment-title');
        const elTotal = document.getElementById('rl-community-payment-total');
        const elUnique = document.getElementById('rl-community-payment-unique');
        const elPublicRef = document.getElementById('rl-community-payment-public-ref');
        const elBadge = document.getElementById('rl-community-payment-status-badge');
        const elStatusHelp = document.getElementById('rl-community-payment-status-help');
        const elStatusError = document.getElementById('rl-community-payment-status-error');
        const elActions = document.getElementById('rl-community-payment-actions');
        const elRetry = document.getElementById('rl-community-payment-retry');

        const elMootaWrap = document.getElementById('rl-community-payment-moota');
        const elAccounts = document.getElementById('rl-community-payment-accounts');
        const elQrisWrap = document.getElementById('rl-community-payment-qris');
        const elQrisCode = document.getElementById('rl-community-payment-qris-code');
        const elQrisPayload = document.getElementById('rl-community-payment-qris-payload');

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
                elBadge.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-green-500/15 text-green-200 border border-green-500/30';
                elBadge.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-green-500"></span><span id="rl-community-payment-status-text">PAID</span>';
                elStatusHelp.textContent = 'Pembayaran terkonfirmasi. Silakan cek email untuk e-voucher.';
                current.lastStatus = 'paid';
                stopPolling();
                return;
            }

            if (s === 'failed' || s === 'expired') {
                elBadge.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-red-500/15 text-red-200 border border-red-500/30';
                elBadge.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-red-500"></span><span id="rl-community-payment-status-text">FAILED</span>';
                elStatusHelp.textContent = 'Pembayaran gagal atau tidak valid.';
                current.lastStatus = 'failed';
                stopPolling();
                return;
            }

            elBadge.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-yellow-500/15 text-yellow-200 border border-yellow-500/30';
            elBadge.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span><span id="rl-community-payment-status-text">PENDING</span>';
            elStatusHelp.textContent = 'Menunggu konfirmasi pembayaran. Status akan update otomatis.';
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
                row.className = 'flex items-center justify-between gap-4 rounded-xl bg-slate-950/40 border border-slate-700 p-3';
                row.innerHTML = `
                    <div class="min-w-0">
                        <div class="font-black text-slate-100">${bank}</div>
                        <div class="text-xs text-slate-400 truncate">${name || '-'}</div>
                    </div>
                    <div class="font-mono font-black text-slate-100">${number || '-'}</div>
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

        function renderQris(payload) {
            if (!elQrisCode) return;
            elQrisCode.innerHTML = '';
            if (!payload) return;
            if (typeof QRCode === 'undefined') return;
            new QRCode(elQrisCode, {
                text: payload,
                width: 280,
                height: 280,
                correctLevel: QRCode.CorrectLevel.M
            });
        }

        function openModal(payload) {
            const channel = payload && payload.payment_channel ? String(payload.payment_channel) : 'bank_transfer';

            elMootaWrap.classList.add('hidden');
            elQrisWrap.classList.add('hidden');

            if (channel === 'qris') {
                elTitle.textContent = 'Pembayaran QRIS';
                elQrisWrap.classList.remove('hidden');
                elQrisPayload.textContent = payload && payload.qris_payload ? String(payload.qris_payload) : '';
                renderQris(payload && payload.qris_payload ? String(payload.qris_payload) : '');
            } else {
                elTitle.textContent = 'Pembayaran Moota';
                elMootaWrap.classList.remove('hidden');
                renderAccounts();
            }

            const finalAmount = payload && payload.final_amount != null ? payload.final_amount : 0;
            const uniqueCode = payload && payload.unique_code != null ? payload.unique_code : 0;
            const publicRef = payload && payload.registration_id ? payload.registration_id : '-';
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

        document.getElementById('rl-community-payment-close').addEventListener('click', closeModal);
        document.getElementById('rl-community-payment-close-x').addEventListener('click', closeModal);
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

        window.RuangLariCommunityPayment = {
            open: openModal,
            close: closeModal,
        };
    })();
</script>

