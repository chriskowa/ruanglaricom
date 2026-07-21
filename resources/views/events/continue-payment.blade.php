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
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="mb-6">
            <a href="{{ route('events.show', $event->slug) }}" class="text-sm text-slate-400 hover:text-white flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke halaman event
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Event Info Column -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden sticky top-6">
                    <div class="relative aspect-[16/9] bg-slate-950">
                        <img src="{{ $event->getHeroImageUrl() ?: asset('images/hero/jadwal-lari.webp') }}" 
                             alt="{{ $event->name }}" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
                    </div>
                    <div class="p-6">
                        <h2 class="text-xl font-black text-white leading-tight">{{ $event->name }}</h2>
                        
                        <div class="mt-4 space-y-3 text-sm text-slate-400">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-neon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($event->start_at)->translatedFormat('l, d F Y') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-neon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="line-clamp-2">{{ $event->location_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Recovery Form Column -->
            <div class="lg:col-span-2">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <h1 class="text-2xl font-black text-white">Lanjutkan Pembayaran</h1>
                    <p class="text-sm text-slate-400 mt-2">
                        @if(auth()->check() && count($autoTransactions) > 0)
                            Kami menemukan transaksi pending Anda secara otomatis berdasarkan session login Anda.
                        @else
                            Masukkan nomor WhatsApp/HP PIC dan (opsional) ID registrasi untuk menemukan transaksi pending.
                        @endif
                    </p>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">No. HP PIC</label>
                            <input id="phone" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-colors" placeholder="contoh: 0812xxxxxxx">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">ID Registrasi (Opsional)</label>
                            <input id="transaction_id" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-colors" placeholder="contoh: 12345">
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button id="btnFind" class="bg-neon hover:bg-neon/90 text-black font-bold px-5 py-2.5 rounded-xl transition-colors">Cari Transaksi</button>
                        <button id="btnClear" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-5 py-2.5 rounded-xl transition-colors">Reset</button>
                    </div>

                    <div id="msg" class="mt-4 text-sm"></div>

                    <div id="results" class="mt-6 space-y-4 hidden"></div>
                </div>
            </div>
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
                    const txPhone = tx.pic_phone_raw || phone;
                    const card = document.createElement('div');
                    card.className = 'bg-slate-950 border border-slate-800 rounded-2xl p-5 flex flex-col gap-4';
                    
                    let statusBadge = '';
                    if (tx.payment_status === 'pending') {
                        statusBadge = `<span class="px-2 py-0.5 text-[10px] font-bold rounded bg-neon/10 text-neon border border-neon/20 uppercase">Pending</span>`;
                    } else if (tx.payment_status === 'paid') {
                        statusBadge = `<span class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-500/10 text-green-400 border border-green-500/20 uppercase">Paid</span>`;
                    } else {
                        statusBadge = `<span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-500/10 text-slate-400 border border-slate-500/20 uppercase">${tx.payment_status}</span>`;
                    }

                    const participantsHtml = tx.participants && tx.participants.length > 0
                        ? `<div class="pt-3 border-t border-slate-800/60">
                             <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Peserta Terdaftar:</div>
                             <div class="flex flex-wrap gap-2">
                                 ${tx.participants.map(p => `
                                     <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                                         <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                         </svg>
                                         <span class="font-medium">${p.name}</span>
                                         ${p.category ? `<span class="text-[10px] text-neon font-bold bg-neon/10 px-1.5 py-0.5 rounded border border-neon/20">${p.category}</span>` : ''}
                                     </span>
                                 `).join('')}
                             </div>
                           </div>`
                        : '';

                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-white font-bold flex items-center gap-2">
                                    <span>ID Registrasi: <span class="font-mono text-neon">${tx.public_ref || tx.id}</span></span>
                                    ${statusBadge}
                                </div>
                                <div class="text-xs text-slate-400 mt-1.5">${tx.pic_name ? 'PIC: ' + tx.pic_name + ' • ' : ''}HP: ${tx.pic_phone_masked || '-'}</div>
                                ${tx.midtrans_transaction_status ? `<div class="text-xs text-slate-500 mt-1">Midtrans: <span class="text-slate-300">${tx.midtrans_transaction_status}</span></div>` : ''}
                            </div>
                            <div class="text-right">
                                <div class="text-white font-black text-lg">Rp ${Number(tx.final_amount || 0).toLocaleString('id-ID')}</div>
                                <div class="text-[10px] text-slate-500 mt-1">${tx.created_at ? new Date(tx.created_at).toLocaleString('id-ID') : ''}</div>
                            </div>
                        </div>
                        ${participantsHtml}
                        <div class="flex flex-wrap gap-3 pt-2">
                            <button class="btnStatus bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-xl transition-colors">Cek Status</button>
                            <button class="btnResume bg-neon hover:bg-neon/90 text-black font-bold px-4 py-2 rounded-xl transition-colors">Lanjutkan Pembayaran</button>
                        </div>
                    `;
                    results.appendChild(card);

                    const btnStatus = card.querySelector('.btnStatus');
                    const btnResume = card.querySelector('.btnResume');

                    btnStatus.addEventListener('click', async () => {
                        btnStatus.disabled = true;
                        try {
                            const r = await fetch(routes.status.replace(':id', tx.id) + `?phone=${encodeURIComponent(txPhone)}`, {
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
                                    body: JSON.stringify({ phone: txPhone, transaction_id: String(tx.id) })
                                });
                                const data2 = await r2.json();
                                renderTransactions(data2.transactions || [], phone);
                            }
                        } catch (e) {
                            setMsg(e.message || 'Terjadi kesalahan', 'error');
                        } finally {
                            btnStatus.disabled = false;
                        }
                    });

                    btnResume.addEventListener('click', async () => {
                        btnResume.disabled = true;
                        try {
                            const statusResp = await fetch(routes.status.replace(':id', tx.id) + `?phone=${encodeURIComponent(txPhone)}`, {
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
                                    body: JSON.stringify({ phone: txPhone, transaction_id: String(tx.id) })
                                });
                                const data2 = await r2.json();
                                renderTransactions(data2.transactions || [], phone);
                                return;
                            }

                            const r = await fetch(routes.resume.replace(':id', tx.id), {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ phone: txPhone })
                            });
                            if (!r.ok) {
                                let msgText = 'Gagal melanjutkan pembayaran.';
                                try {
                                    const err = await r.json();
                                    if (err && err.message) msgText = err.message;
                                } catch (_) {}
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
                                        body: JSON.stringify({ phone: txPhone, transaction_id: String(tx.id) })
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
                                        await fetch(routes.status.replace(':id', tx.id) + `?phone=${encodeURIComponent(txPhone)}`, {
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
                            btnResume.disabled = false;
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

                    // Save to localStorage upon successful search
                    if (data.transactions && data.transactions.length > 0) {
                        localStorage.setItem('ruanglari_last_phone', phone);
                        localStorage.setItem('ruanglari_phone_event_' + "{{ $event->slug }}", phone);
                    }
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
                // Clear localStorage key for this event on reset
                localStorage.removeItem('ruanglari_phone_event_' + "{{ $event->slug }}");
            });

            // Auto-load if user is authenticated and has pending transactions
            const autoTransactions = @json($autoTransactions ?? []);
            const userPhone = @json($userPhone ?? '');

            if (autoTransactions && autoTransactions.length > 0) {
                if (phoneEl && userPhone) {
                    phoneEl.value = userPhone;
                }
                renderTransactions(autoTransactions, userPhone);
            } else {
                // For guest users, try loading from localStorage
                const localKey = 'ruanglari_phone_event_' + "{{ $event->slug }}";
                const savedPhone = localStorage.getItem(localKey) || localStorage.getItem('ruanglari_last_phone');
                if (savedPhone && phoneEl) {
                    phoneEl.value = savedPhone;
                    // Auto-trigger search for guest
                    btnFind.click();
                }
            }
        })();
    </script>
@endsection
