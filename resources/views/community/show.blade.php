@extends('layouts.pacerhub')
@php($withSidebar = false)

@section('title', 'Registrasi Komunitas')

@section('content')
<div class="min-h-screen px-4 md:px-8 py-10">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Community Registration</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">{{ $registration->community_name }}</h1>
                <div class="text-slate-400 text-sm mt-1">{{ $event->name }}</div>
            </div>
            <div class="flex items-center gap-2">
                <?php
                    $isLocked = $registration->status !== 'draft';

                    $existingPayment = null;
                    if ($latestInvoice && $latestInvoice->transaction) {
                        $existingPayment = [
                            'transaction_id' => $latestInvoice->transaction->id,
                            'registration_id' => $latestInvoice->transaction->public_ref,
                            'final_amount' => (float) $latestInvoice->transaction->final_amount,
                            'unique_code' => (int) $latestInvoice->transaction->unique_code,
                            'payment_channel' => (string) ($latestInvoice->transaction->payment_channel ?? 'bank_transfer'),
                            'qris_payload' => $latestInvoice->qris_payload,
                        ];
                    }
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border {{ $isLocked ? 'bg-yellow-500/15 text-yellow-200 border-yellow-500/30' : 'bg-green-500/15 text-green-200 border-green-500/30' }}">
                    {{ $isLocked ? strtoupper($registration->status) : 'DRAFT' }}
                </span>
                <a href="{{ route('community.register.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">Ganti Event</a>
            </div>
        </div>

        <div id="communityApp" class="grid grid-cols-1 lg:grid-cols-12 gap-6" data-locked="{{ $isLocked ? 1 : 0 }}">
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-black text-white">Data PIC</div>
                            <div class="text-xs text-slate-400">PIC bisa diubah selama masih draft.</div>
                        </div>
                        <button type="button" id="btnSavePic" class="px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                            Simpan
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Komunitas</label>
                            <input type="text" id="community_name" value="{{ $registration->community_name }}" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama PIC</label>
                            <input type="text" id="pic_name" value="{{ $registration->pic_name }}" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email PIC</label>
                            <input type="email" id="pic_email" value="{{ $registration->pic_email }}" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp PIC</label>
                            <input type="text" id="pic_phone" value="{{ $registration->pic_phone }}" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                    </div>
                </div>

                <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-black text-white">Tambah Peserta</div>
                            <div class="text-xs text-slate-400">Beli 10 gratis 1 (termurah).</div>
                        </div>
                        <button type="button" id="btnAddParticipant" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">
                            Tambah
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama</label>
                            <input type="text" id="p_name" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Gender</label>
                            <select id="p_gender" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                <option value="">-</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori</label>
                            <select id="p_category" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                <option value="">-- pilih --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                            <input type="email" id="p_email" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp</label>
                            <input type="text" id="p_phone" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">ID Card</label>
                            <input type="text" id="p_id_card" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Alamat</label>
                            <textarea id="p_address" rows="3" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-xs text-red-300 font-bold hidden" id="participantError"></div>
                </div>
            </div>

            <div class="lg:col-span-7 space-y-6">
                <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-700/60 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-black text-white">Daftar Peserta</div>
                            <div class="text-xs text-slate-400">Hanya peserta komunitas ini.</div>
                        </div>
                        <div class="text-xs text-slate-400 font-mono" id="participantCount">{{ $participants->count() }} peserta</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-900/60 border-b border-slate-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-black text-slate-400 uppercase tracking-wider">ID Card</th>
                                    <th class="px-6 py-3 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="participantsBody" class="divide-y divide-slate-800">
                                @foreach($participants as $p)
                                    <tr data-id="{{ $p->id }}">
                                        <td class="px-6 py-3 text-white font-bold">{{ $p->name }}</td>
                                        <td class="px-6 py-3 text-slate-200 text-sm">{{ $p->category?->name ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-200 text-sm font-mono">{{ $p->id_card ?? '-' }}</td>
                                        <td class="px-6 py-3 text-right">
                                            <button type="button" class="btnDeleteParticipant px-3 py-1 rounded-xl bg-red-500/15 border border-red-500/30 text-red-200 font-bold text-xs hover:bg-red-500/20 transition" data-id="{{ $p->id }}">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($participants->isEmpty())
                                    <tr id="emptyRow">
                                        <td colspan="4" class="px-6 py-10 text-center text-slate-500">Belum ada peserta.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div class="text-sm font-black text-white">Selesai &amp; Generate Invoice</div>
                            <div class="text-xs text-slate-400">Metode pembayaran: Moota atau QRIS dinamis.</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <select id="paymentMethod" class="px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                <option value="moota">Transfer Bank (Moota)</option>
                                <option value="qris">QRIS Dinamis</option>
                            </select>
                            <button type="button" id="btnGenerateInvoice" class="px-6 py-3 rounded-2xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                                Generate
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-red-300 font-bold hidden" id="invoiceError"></div>

                    @if($latestInvoice && $latestInvoice->transaction)
                        <div class="mt-4 bg-slate-950/40 border border-slate-700 rounded-2xl p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm text-slate-200 font-bold">Invoice terakhir: {{ strtoupper($latestInvoice->payment_method) }}</div>
                                <button type="button" id="btnOpenExistingInvoice" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">Buka</button>
                            </div>
                            <div class="mt-1 text-xs text-slate-400">ID: <span class="font-mono">{{ $latestInvoice->transaction->public_ref }}</span></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('events.partials.community-payment-modal')

<script>
    (function () {
        const app = document.getElementById('communityApp');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let locked = app.getAttribute('data-locked') === '1';

        const urls = {
            savePic: @json(route('community.register.pic', $registration->id)),
            listParticipants: @json(route('community.register.participants', $registration->id)),
            addParticipant: @json(route('community.register.participants.store', $registration->id)),
            deleteParticipant: @json(route('community.register.participants.delete', ['registration' => $registration->id, 'participant' => '__ID__'])),
            invoice: @json(route('community.register.invoice', ['event' => $event->slug, 'registration' => $registration->id])),
        };

        const els = {
            btnSavePic: document.getElementById('btnSavePic'),
            btnAdd: document.getElementById('btnAddParticipant'),
            btnInvoice: document.getElementById('btnGenerateInvoice'),
            btnOpenExisting: document.getElementById('btnOpenExistingInvoice'),
            participantError: document.getElementById('participantError'),
            invoiceError: document.getElementById('invoiceError'),
            participantsBody: document.getElementById('participantsBody'),
            participantCount: document.getElementById('participantCount'),
        };

        function setDisabled(state) {
            const inputs = app.querySelectorAll('input, select, button');
            inputs.forEach(function (el) {
                if (el.id === 'btnOpenExistingInvoice') return;
                if (el.closest('#rl-community-payment-modal')) return;
                if (el.type === 'button' || el.tagName === 'BUTTON' || el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                    if (el.id === 'btnOpenExistingInvoice') return;
                }
            });

            const fields = [
                'community_name','pic_name','pic_email','pic_phone',
                'p_name','p_gender','p_category','p_email','p_phone','p_id_card','p_address',
                'btnSavePic','btnAddParticipant','btnGenerateInvoice','paymentMethod'
            ];
            fields.forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.disabled = state;
                if (state) el.classList.add('opacity-60');
                else el.classList.remove('opacity-60');
            });

            const delButtons = document.querySelectorAll('.btnDeleteParticipant');
            delButtons.forEach(function (b) {
                b.disabled = state;
                if (state) b.classList.add('opacity-60');
                else b.classList.remove('opacity-60');
            });
        }

        function showError(el, msg) {
            el.textContent = msg;
            el.classList.remove('hidden');
        }

        function hideError(el) {
            el.textContent = '';
            el.classList.add('hidden');
        }

        function renderParticipants(items) {
            els.participantsBody.innerHTML = '';

            if (!items || !items.length) {
                const tr = document.createElement('tr');
                tr.id = 'emptyRow';
                tr.innerHTML = '<td colspan="4" class="px-6 py-10 text-center text-slate-500">Belum ada peserta.</td>';
                els.participantsBody.appendChild(tr);
                els.participantCount.textContent = '0 peserta';
                return;
            }

            els.participantCount.textContent = items.length + ' peserta';

            items.forEach(function (p) {
                const tr = document.createElement('tr');
                tr.dataset.id = p.id;
                tr.innerHTML = `
                    <td class="px-6 py-3 text-white font-bold">${escapeHtml(p.name || '-')}</td>
                    <td class="px-6 py-3 text-slate-200 text-sm">${escapeHtml(p.category_name || '-')}</td>
                    <td class="px-6 py-3 text-slate-200 text-sm font-mono">${escapeHtml(p.id_card || '-')}</td>
                    <td class="px-6 py-3 text-right">
                        <button type="button" class="btnDeleteParticipant px-3 py-1 rounded-xl bg-red-500/15 border border-red-500/30 text-red-200 font-bold text-xs hover:bg-red-500/20 transition" data-id="${p.id}">Hapus</button>
                    </td>
                `;
                els.participantsBody.appendChild(tr);
            });

            bindDeleteButtons();
            setDisabled(locked);
        }

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
            });
        }

        function refreshParticipants() {
            return fetch(urls.listParticipants, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        locked = !!data.locked;
                        app.setAttribute('data-locked', locked ? '1' : '0');
                        renderParticipants(data.participants || []);
                    }
                });
        }

        function bindDeleteButtons() {
            document.querySelectorAll('.btnDeleteParticipant').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (locked) return;
                    const id = btn.dataset.id;
                    if (!id) return;
                    hideError(els.participantError);

                    fetch(urls.deleteParticipant.replace('__ID__', encodeURIComponent(String(id))), {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data || !data.success) {
                            showError(els.participantError, (data && data.message) ? data.message : 'Gagal menghapus peserta.');
                            return;
                        }
                        refreshParticipants();
                    })
                    .catch(() => showError(els.participantError, 'Gagal menghubungi server.'));
                });
            });
        }

        function getPicPayload() {
            return {
                community_name: document.getElementById('community_name').value,
                pic_name: document.getElementById('pic_name').value,
                pic_email: document.getElementById('pic_email').value,
                pic_phone: document.getElementById('pic_phone').value,
            };
        }

        function getParticipantPayload() {
            return {
                name: document.getElementById('p_name').value,
                gender: document.getElementById('p_gender').value || null,
                email: document.getElementById('p_email').value || null,
                phone: document.getElementById('p_phone').value || null,
                id_card: document.getElementById('p_id_card').value || null,
                address: document.getElementById('p_address').value || null,
                race_category_id: document.getElementById('p_category').value,
            };
        }

        function clearParticipantForm() {
            ['p_name','p_gender','p_category','p_email','p_phone','p_id_card','p_address'].forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                if (el.tagName === 'SELECT') el.value = '';
                else el.value = '';
            });
        }

        els.btnSavePic.addEventListener('click', function () {
            if (locked) return;
            hideError(els.invoiceError);
            fetch(urls.savePic, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(getPicPayload())
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    showError(els.invoiceError, (data && data.message) ? data.message : 'Gagal menyimpan PIC.');
                }
            })
            .catch(() => showError(els.invoiceError, 'Gagal menghubungi server.'));
        });

        els.btnAdd.addEventListener('click', function () {
            if (locked) return;
            hideError(els.participantError);
            fetch(urls.addParticipant, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(getParticipantPayload())
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    showError(els.participantError, (data && data.message) ? data.message : 'Gagal menambah peserta.');
                    return;
                }
                clearParticipantForm();
                refreshParticipants();
            })
            .catch(() => showError(els.participantError, 'Gagal menghubungi server.'));
        });

        els.btnInvoice.addEventListener('click', function () {
            if (locked) return;
            hideError(els.invoiceError);
            const method = document.getElementById('paymentMethod').value || 'moota';
            const payload = { payment_method: method };
            fetch(urls.invoice, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    showError(els.invoiceError, (data && data.message) ? data.message : 'Gagal membuat invoice.');
                    return;
                }
                locked = true;
                app.setAttribute('data-locked', '1');
                setDisabled(true);

                window.RuangLariCommunityPayment.open({
                    transaction_id: data.transaction_id,
                    registration_id: data.registration_id,
                    final_amount: data.final_amount,
                    unique_code: data.unique_code,
                    payment_channel: data.payment_channel,
                    qris_payload: data.qris_payload,
                    phone: document.getElementById('pic_phone').value || '',
                });
            })
            .catch(() => showError(els.invoiceError, 'Gagal menghubungi server.'));
        });

        if (els.btnOpenExisting) {
            const existing = @json($existingPayment);

            els.btnOpenExisting.addEventListener('click', function () {
                if (!existing) return;
                locked = true;
                app.setAttribute('data-locked', '1');
                setDisabled(true);
                window.RuangLariCommunityPayment.open({
                    transaction_id: existing.transaction_id,
                    registration_id: existing.registration_id,
                    final_amount: existing.final_amount,
                    unique_code: existing.unique_code,
                    payment_channel: existing.payment_channel,
                    qris_payload: existing.qris_payload,
                    phone: document.getElementById('pic_phone').value || '',
                });
            });
        }

        bindDeleteButtons();
        setDisabled(locked);
        refreshParticipants();
    })();
</script>
@endsection
