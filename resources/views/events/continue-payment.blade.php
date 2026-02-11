@extends('layouts.pacerhub')

@php
    $hideChat = true;
    $withSidebar = false;
    $hideNav = false;
    $hideFooter = false;

    $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
    $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
@endphp

@section('title', 'Lanjutkan Pembayaran')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="mb-6">
            <a href="{{ route('events.show', $event->slug) }}" class="text-sm text-slate-400 hover:text-white">&larr; Kembali ke halaman event</a>
        </div>

        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-6">
            <h1 class="text-2xl font-black text-white">Lanjutkan Pembayaran</h1>
            <p class="text-sm text-slate-400 mt-2">Masukkan nomor WhatsApp/HP PIC dan (opsional) ID registrasi untuk menemukan transaksi pending.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">No. HP PIC</label>
                    <input id="phone" type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="contoh: 0812xxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">ID Registrasi (Opsional)</label>
                    <input id="transaction_id" type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="contoh: 12345">
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <button id="btnFind" class="bg-yellow-400 hover:bg-yellow-300 text-black font-bold px-4 py-2 rounded-xl">Cari Transaksi</button>
                <button id="btnClear" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-xl">Reset</button>
            </div>

            <div id="msg" class="mt-4 text-sm"></div>

            <div id="results" class="mt-6 space-y-3 hidden"></div>
        </div>
    </div>

    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    <script>
        (function () {
            const routes = {
                pending: "{{ route('api.events.payments.pending', $event->slug) }}",
                status: "{{ route('api.events.payments.status', ['slug' => $event->slug, 'transaction' => ':id']) }}",
                resume: "{{ route('api.events.payments.resume', ['slug' => $event->slug, 'transaction' => ':id']) }}"
            };
            const msg = document.getElementById('msg');
            const results = document.getElementById('results');
            const phoneEl = document.getElementById('phone');
            const txIdEl = document.getElementById('transaction_id');
            const btnFind = document.getElementById('btnFind');
            const btnClear = document.getElementById('btnClear');

            function setMsg(text, type) {
                msg.className = 'mt-4 text-sm';
                if (!text) {
                    msg.innerHTML = '';
                    return;
                }
                if (type === 'error') msg.className += ' text-red-400';
                else if (type === 'ok') msg.className += ' text-green-400';
                else msg.className += ' text-slate-300';
                msg.textContent = text;
            }

            function renderTransactions(transactions, phone) {
                results.innerHTML = '';
                if (!transactions || transactions.length === 0) {
                    results.classList.add('hidden');
                    setMsg('Tidak ada transaksi pending ditemukan.', 'info');
                    return;
                }
                results.classList.remove('hidden');
                setMsg('', 'info');

                transactions.forEach(tx => {
                    const card = document.createElement('div');
                    card.className = 'bg-slate-950 border border-slate-700 rounded-2xl p-4 flex flex-col gap-3';
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-white font-bold">ID Registrasi: <span class="font-mono">${tx.public_ref || tx.id}</span></div>
                                <div class="text-xs text-slate-400 mt-1">${tx.pic_name ? 'PIC: ' + tx.pic_name + ' • ' : ''}HP: ${tx.pic_phone_masked || '-'}</div>
                                <div class="text-xs text-slate-500 mt-1">Status: <span class="text-slate-300">${tx.payment_status}</span>${tx.midtrans_transaction_status ? ' • Midtrans: ' + tx.midtrans_transaction_status : ''}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-white font-bold">Rp ${Number(tx.final_amount || 0).toLocaleString('id-ID')}</div>
                                <div class="text-[10px] text-slate-500 mt-1">${tx.created_at ? new Date(tx.created_at).toLocaleString('id-ID') : ''}</div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button class="btnStatus bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-xl" data-id="${tx.id}">Cek Status</button>
                            <button class="btnResume bg-yellow-400 hover:bg-yellow-300 text-black font-bold px-4 py-2 rounded-xl" data-id="${tx.id}">Lanjutkan Pembayaran</button>
                        </div>
                    `;
                    results.appendChild(card);
                });

                results.querySelectorAll('.btnStatus').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const id = btn.getAttribute('data-id');
                        btn.disabled = true;
                        try {
                            const r = await fetch(routes.status.replace(':id', id) + `?phone=${encodeURIComponent(phone)}`, {
                                credentials: 'same-origin',
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await r.json();
                            if (!data.success) throw new Error(data.message || 'Gagal mengecek status');
                            setMsg('Status diperbarui.', 'ok');
                            if (data.transaction) {
                                const r2 = await fetch(routes.pending, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ phone, transaction_id: id })
                                });
                                const data2 = await r2.json();
                                renderTransactions(data2.transactions || [], phone);
                            }
                        } catch (e) {
                            setMsg(e.message || 'Terjadi kesalahan', 'error');
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });

                results.querySelectorAll('.btnResume').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const id = btn.getAttribute('data-id');
                        btn.disabled = true;
                        try {
                            const statusResp = await fetch(routes.status.replace(':id', id) + `?phone=${encodeURIComponent(phone)}`, {
                                credentials: 'same-origin',
                                headers: { 'Accept': 'application/json' }
                            });
                            const statusData = await statusResp.json();
                            if (!statusResp.ok || !statusData.success || !statusData.transaction || statusData.transaction.payment_status !== 'pending') {
                                setMsg(statusData.message || 'Transaksi tidak dalam status pending.', 'error');
                                const r2 = await fetch(routes.pending, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ phone, transaction_id: id })
                                });
                                const data2 = await r2.json();
                                renderTransactions(data2.transactions || [], phone);
                                return;
                            }

                            const r = await fetch(routes.resume.replace(':id', id), {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ phone })
                            });
                            // Handle 404/405 gracefully
                            if (!r.ok) {
                                let msgText = 'Gagal melanjutkan pembayaran.';
                                try {
                                    const err = await r.json();
                                    if (err && err.message) msgText = err.message;
                                } catch (_) {}
                                // 404: Not Found or invalid transaction for this event -> refresh pending list
                                if (r.status === 404) {
                                    setMsg(msgText, 'error');
                                    const r2 = await fetch(routes.pending, {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ phone, transaction_id: id })
                                    });
                                    const data2 = await r2.json();
                                    renderTransactions(data2.transactions || [], phone);
                                    return;
                                }
                                throw new Error(msgText);
                            }
                            const data = await r.json();
                            if (!data.success) throw new Error(data.message || 'Gagal melanjutkan pembayaran');
                            if (!data.snap_token) throw new Error(data.message || 'Token tidak tersedia');
                            if (typeof snap === 'undefined') throw new Error('Snap.js tidak termuat');

                            snap.pay(data.snap_token, {
                                onSuccess: async function (result) {
                                    setMsg('Pembayaran berhasil! Memverifikasi status...', 'ok');
                                    try {
                                        // Force update status on server
                                        await fetch(routes.status.replace(':id', id) + `?phone=${encodeURIComponent(phone)}`, {
                                            credentials: 'same-origin',
                                            headers: { 'Accept': 'application/json' }
                                        });
                                    } catch (e) {
                                        console.error('Auto-update status failed', e);
                                    }
                                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`;
                                },
                                onPending: function (result) {
                                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending`;
                                },
                                onError: function (result) {
                                    setMsg('Pembayaran gagal. Silakan coba lagi.', 'error');
                                },
                                onClose: function () {
                                    setMsg('Popup pembayaran ditutup.', 'info');
                                }
                            });
                        } catch (e) {
                            setMsg(e.message || 'Terjadi kesalahan', 'error');
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });
            }

            btnFind.addEventListener('click', async () => {
                const phone = (phoneEl.value || '').trim();
                const transaction_id = (txIdEl.value || '').trim();
                if (!phone) {
                    setMsg('No. HP wajib diisi.', 'error');
                    return;
                }

                btnFind.disabled = true;
                setMsg('Mencari transaksi pending...', 'info');
                results.classList.add('hidden');
                results.innerHTML = '';

                try {
                    const r = await fetch(routes.pending, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone, transaction_id: transaction_id || null })
                    });
                    const data = await r.json();
                    if (!data.success) throw new Error(data.message || 'Gagal mencari transaksi');
                    renderTransactions(data.transactions || [], phone);
                } catch (e) {
                    setMsg(e.message || 'Terjadi kesalahan', 'error');
                } finally {
                    btnFind.disabled = false;
                }
            });

            btnClear.addEventListener('click', () => {
                phoneEl.value = '';
                txIdEl.value = '';
                results.classList.add('hidden');
                results.innerHTML = '';
                setMsg('', 'info');
            });
        })();
    </script>
@endsection
