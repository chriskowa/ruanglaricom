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

        <div id="communityApp" class="space-y-8" data-locked="{{ $isLocked ? 1 : 0 }}">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left Column: Info & Actions -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Data PIC -->
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

                    <!-- Invoice / Payment Action -->
                    <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6">
                        <div class="flex flex-col gap-4">
                            <div>
                                <div class="text-sm font-black text-white">Selesai &amp; Generate Invoice</div>
                                <div class="text-xs text-slate-400">Metode pembayaran: Moota atau QRIS dinamis.</div>
                            </div>
                            <div class="space-y-3">
                                <select id="paymentMethod" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                    <option value="moota">Transfer Bank (Moota)</option>
                                    <option value="qris">QRIS Dinamis</option>
                                </select>
                                <button type="button" id="btnGenerateInvoice" class="w-full px-6 py-3 rounded-2xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                                    Generate Invoice
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 text-xs text-red-300 font-bold hidden" id="invoiceError"></div>

                        @if($latestInvoice && $latestInvoice->transaction)
                            <div class="mt-4 bg-slate-950/40 border border-slate-700 rounded-2xl p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm text-slate-200 font-bold">Invoice terakhir: {{ strtoupper($latestInvoice->payment_method) }}</div>
                                    <div class="flex items-center gap-2">
                                        @if($latestInvoice->status === 'pending' && $latestInvoice->transaction->payment_status === 'pending' && $isLocked)
                                            <button type="button" id="btnCancelInvoice" class="px-4 py-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 font-bold hover:bg-red-500/20 transition">Batalkan</button>
                                        @endif
                                        <button type="button" id="btnOpenExistingInvoice" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">Buka</button>
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-slate-400">ID: <span class="font-mono">{{ $latestInvoice->transaction->public_ref }}</span></div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Add Participant Form -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6 h-full">
                        <div class="flex items-center justify-between gap-3 mb-6">
                            <div>
                                <div id="participantFormTitle" class="text-lg font-black text-white">Tambah Peserta</div>
                                <div class="text-sm text-slate-400">Isi data peserta komunitas (Beli 10 gratis 1).</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btnCancelEditParticipant" class="hidden px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-bold hover:bg-slate-800 transition shadow-lg">
                                    Batal
                                </button>
                                <button type="button" id="btnAddParticipant" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition shadow-lg">
                                    <span class="mr-2">+</span> Tambah
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Peserta</label>
                                <input type="text" id="p_name" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon placeholder-slate-600" placeholder="Nama Lengkap">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Gender</label>
                                <select id="p_gender" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                    <option value="">- Pilih Gender -</option>
                                    <option value="male">Laki-laki (Male)</option>
                                    <option value="female">Perempuan (Female)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ukuran Jersey</label>
                                <select id="p_jersey_size" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                    <option value="">- Pilih Size -</option>
                                    @foreach(($event->jersey_sizes ?? ['XS','S','M','L','XL','XXL','3XL']) as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori Lari</label>
                                <select id="p_category" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                    <option value="">- Pilih Kategori -</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                                <input type="email" id="p_email" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon placeholder-slate-600" placeholder="email@address.com">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp</label>
                                <input type="text" id="p_phone" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon placeholder-slate-600" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nomor Identitas (KTP/SIM/Passport)</label>
                                <input type="text" id="p_id_card" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon placeholder-slate-600" placeholder="Nomor ID">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Alamat Lengkap</label>
                                <textarea id="p_address" rows="2" class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon placeholder-slate-600" placeholder="Alamat domisili peserta"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-red-300 font-bold hidden" id="participantError"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Participant List -->
            <div class="w-full">
                <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-700/60 flex items-center justify-between bg-slate-900/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base font-black text-white">Daftar Peserta Terdaftar</div>
                                <div class="text-xs text-slate-400">Total: <span id="participantCount" class="text-neon font-bold">{{ $participants->count() }} peserta</span></div>
                            </div>
                        </div>
                        <!-- Export/Import buttons could go here -->
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-950/60 border-b border-slate-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Nama Peserta</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Kategori & Gender</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Kontak</th>
                                    <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="participantsBody" class="divide-y divide-slate-800 bg-slate-900/20">
                                @foreach($participants as $p)
                                    <tr data-id="{{ $p->id }}" class="hover:bg-slate-800/50 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-white">{{ $p->name }}</div>
                                            <div class="text-xs text-slate-500 font-mono mt-1">{{ $p->id_card ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-200">{{ $p->category?->name ?? '-' }}</div>
                                            <div class="text-xs text-slate-500 capitalize">
                                                {{ $p->gender ?? '-' }}
                                                <span class="mx-1 text-slate-600">•</span>
                                                Size: <span class="font-mono text-slate-300">{{ $p->jersey_size ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-300">{{ $p->email ?? '-' }}</div>
                                            <div class="text-xs text-slate-500">{{ $p->phone ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="button" class="btnEditParticipant px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white font-bold text-xs hover:bg-slate-700 transition flex items-center gap-1" data-id="{{ $p->id }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button type="button" class="btnDeleteParticipant px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-bold text-xs hover:bg-red-500/20 transition flex items-center gap-1" data-id="{{ $p->id }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($participants->isEmpty())
                                    <tr id="emptyRow">
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                </svg>
                                                <div class="text-sm font-medium">Belum ada peserta yang didaftarkan.</div>
                                                <div class="text-xs mt-1">Silakan isi formulir di atas untuk menambah peserta.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('events.partials.community-payment-modal')

@php($deleteParticipantUrl = route('community.register.participants.delete', ['event' => $event->slug, 'community' => $community->slug, 'participant' => 0]))
@php($updateParticipantUrl = route('community.register.participants.update', ['event' => $event->slug, 'community' => $community->slug, 'participant' => 0]))
@php($cancelInvoiceUrl = route('community.register.invoice.cancel', ['event' => $event->slug, 'community' => $community->slug]))

<script>
    (function () {
        const app = document.getElementById('communityApp');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let locked = app.getAttribute('data-locked') === '1';
        let editingParticipantId = null;
        let participantsById = {};

        const urls = {
            savePic: @json(route('community.register.pic', ['event' => $event->slug, 'community' => $community->slug])),
            listParticipants: @json(route('community.register.participants', ['event' => $event->slug, 'community' => $community->slug])),
            addParticipant: @json(route('community.register.participants.store', ['event' => $event->slug, 'community' => $community->slug])),
            deleteParticipant: @json($deleteParticipantUrl),
            updateParticipant: @json($updateParticipantUrl),
            invoice: @json(route('community.register.invoice', ['event' => $event->slug, 'community' => $community->slug])),
            cancelInvoice: @json($cancelInvoiceUrl),
        };

        const els = {
            btnSavePic: document.getElementById('btnSavePic'),
            btnAdd: document.getElementById('btnAddParticipant'),
            btnCancelEdit: document.getElementById('btnCancelEditParticipant'),
            btnInvoice: document.getElementById('btnGenerateInvoice'),
            btnCancelInvoice: document.getElementById('btnCancelInvoice'),
            btnOpenExisting: document.getElementById('btnOpenExistingInvoice'),
            participantError: document.getElementById('participantError'),
            invoiceError: document.getElementById('invoiceError'),
            participantsBody: document.getElementById('participantsBody'),
            participantCount: document.getElementById('participantCount'),
            participantFormTitle: document.getElementById('participantFormTitle'),
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
                'p_name','p_gender','p_jersey_size','p_category','p_email','p_phone','p_id_card','p_address',
                'btnSavePic','btnAddParticipant','btnGenerateInvoice','paymentMethod'
            ];
            fields.forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.disabled = state;
                if (state) el.classList.add('opacity-60');
                else el.classList.remove('opacity-60');
            });

            const rowButtons = document.querySelectorAll('.btnDeleteParticipant, .btnEditParticipant');
            rowButtons.forEach(function (b) {
                if (state) {
                    b.classList.add('opacity-60');
                    b.classList.add('cursor-not-allowed');
                } else {
                    b.classList.remove('opacity-60');
                    b.classList.remove('cursor-not-allowed');
                }
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
            participantsById = {};

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
                participantsById[String(p.id)] = p;
                const tr = document.createElement('tr');
                tr.dataset.id = p.id;
                tr.className = 'hover:bg-slate-800/50 transition';
                tr.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="font-bold text-white">${escapeHtml(p.name || '-')}</div>
                        <div class="text-xs text-slate-500 font-mono mt-1">${escapeHtml(p.id_card || '-')}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-200">${escapeHtml(p.category_name || '-')}</div>
                        <div class="text-xs text-slate-500 capitalize">
                            ${escapeHtml(p.gender || '-')}
                            <span class="mx-1 text-slate-600">•</span>
                            Size: <span class="font-mono text-slate-300">${escapeHtml(p.jersey_size || '-')}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-300">${escapeHtml(p.email || '-')}</div>
                        <div class="text-xs text-slate-500">${escapeHtml(p.phone || '-')}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button type="button" class="btnEditParticipant px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white font-bold text-xs hover:bg-slate-700 transition flex items-center gap-1" data-id="${p.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </button>
                            <button type="button" class="btnDeleteParticipant px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-bold text-xs hover:bg-red-500/20 transition flex items-center gap-1" data-id="${p.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                `;
                els.participantsBody.appendChild(tr);
            });

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

        function setFormMode(mode) {
            if (mode === 'edit') {
                els.participantFormTitle.textContent = 'Edit Peserta';
                els.btnAdd.innerHTML = 'Simpan Perubahan';
                els.btnCancelEdit.classList.remove('hidden');
            } else {
                els.participantFormTitle.textContent = 'Tambah Peserta';
                els.btnAdd.innerHTML = '<span class="mr-2">+</span> Tambah';
                els.btnCancelEdit.classList.add('hidden');
            }
        }

        function participantUrl(baseUrl, id) {
            const safeId = encodeURIComponent(String(id));
            if (/\/0$/.test(baseUrl)) return baseUrl.replace(/\/0$/, '/' + safeId);
            return baseUrl.replace(/0$/, safeId);
        }

        function startEditParticipant(id) {
            const p = participantsById[String(id)];
            if (!p) return;
            document.getElementById('p_name').value = p.name || '';
            document.getElementById('p_gender').value = p.gender || '';
            document.getElementById('p_jersey_size').value = p.jersey_size || '';
            document.getElementById('p_category').value = p.category_id || '';
            document.getElementById('p_email').value = p.email || '';
            document.getElementById('p_phone').value = p.phone || '';
            document.getElementById('p_id_card').value = p.id_card || '';
            document.getElementById('p_address').value = p.address || '';
            editingParticipantId = String(id);
            setFormMode('edit');
        }

        function stopEditParticipant() {
            editingParticipantId = null;
            clearParticipantForm();
            setFormMode('add');
        }

        els.participantsBody.addEventListener('click', function (e) {
            const btnEdit = e.target.closest('.btnEditParticipant');
            if (btnEdit) {
                hideError(els.participantError);
                if (locked) {
                    showError(els.participantError, 'Registrasi sudah dikunci (invoice sudah dibuat).');
                    return;
                }
                const id = btnEdit.dataset.id;
                if (!id) return;
                if (!participantsById[String(id)]) {
                    refreshParticipants().then(() => startEditParticipant(id));
                    return;
                }
                startEditParticipant(id);
                return;
            }

            const btnDelete = e.target.closest('.btnDeleteParticipant');
            if (!btnDelete) return;

            hideError(els.participantError);
            if (locked) {
                showError(els.participantError, 'Registrasi sudah dikunci (invoice sudah dibuat).');
                return;
            }

            if (!confirm('Apakah anda yakin ingin menghapus peserta ini?')) return;

            const id = btnDelete.dataset.id;
            if (!id) return;

            fetch(participantUrl(urls.deleteParticipant, id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    if (!data || !data.success) {
                        showError(els.participantError, (data && data.message) ? data.message : 'Gagal menghapus peserta.');
                        return;
                    }
                    if (editingParticipantId && String(editingParticipantId) === String(id)) {
                        stopEditParticipant();
                    }
                    refreshParticipants();
                })
                .catch(() => showError(els.participantError, 'Gagal menghubungi server.'));
        });

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
                jersey_size: document.getElementById('p_jersey_size').value || null,
                email: document.getElementById('p_email').value || null,
                phone: document.getElementById('p_phone').value || null,
                id_card: document.getElementById('p_id_card').value || null,
                address: document.getElementById('p_address').value || null,
                race_category_id: document.getElementById('p_category').value,
            };
        }

        function clearParticipantForm() {
            ['p_name','p_gender','p_jersey_size','p_category','p_email','p_phone','p_id_card','p_address'].forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                if (el.tagName === 'SELECT') el.value = '';
                else el.value = '';
            });
        }

        els.btnCancelEdit.addEventListener('click', function () {
            hideError(els.participantError);
            stopEditParticipant();
        });

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
            hideError(els.participantError);
            if (locked) {
                showError(els.participantError, 'Registrasi sudah dikunci (invoice sudah dibuat).');
                return;
            }

            const isEditing = !!editingParticipantId;
            const url = isEditing ? participantUrl(urls.updateParticipant, editingParticipantId) : urls.addParticipant;
            const method = isEditing ? 'PUT' : 'POST';

            fetch(url, {
                method,
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
                        showError(
                            els.participantError,
                            (data && data.message) ? data.message : (isEditing ? 'Gagal mengubah peserta.' : 'Gagal menambah peserta.')
                        );
                        return;
                    }
                    stopEditParticipant();
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

        if (els.btnCancelInvoice) {
            els.btnCancelInvoice.addEventListener('click', function () {
                hideError(els.invoiceError);
                hideError(els.participantError);
                if (!confirm('Batalkan invoice dan buka kembali registrasi ke DRAFT?')) return;

                fetch(urls.cancelInvoice, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data || !data.success) {
                            showError(els.invoiceError, (data && data.message) ? data.message : 'Gagal membatalkan invoice.');
                            return;
                        }
                        locked = false;
                        app.setAttribute('data-locked', '0');
                        setDisabled(false);
                        stopEditParticipant();
                        refreshParticipants();
                        window.location.reload();
                    })
                    .catch(() => showError(els.invoiceError, 'Gagal menghubungi server.'));
            });
        }

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

        setFormMode('add');
        setDisabled(locked);
        refreshParticipants();
    })();
</script>
@endsection
