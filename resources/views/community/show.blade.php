@extends('layouts.pacerhub', ['withSidebar' => false])

@section('title', 'Registrasi Komunitas - ' . ($registration->community_name ?? 'Ruang Lari'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .kp-community-show {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .kp-community-show h1,
    .kp-community-show h2,
    .kp-community-show h3 {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .kp-mono {
        font-family: 'JetBrains Mono', monospace;
        font-variant-numeric: tabular-nums;
    }
</style>
@if(in_array('midtrans', ($allowedPaymentMethods ?? ['midtrans']), true) && !empty($midtransClientKey))
<script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}" defer></script>
@endif
@endpush

@section('content')
@php
    $isLocked = $registration->status !== 'draft';
    $promoBuyX = (int) ($event->promo_buy_x ?? 0);
    $allowedMethods = $allowedPaymentMethods ?? ['midtrans'];
    $paymentLabels = [
        'midtrans' => 'Midtrans (VA / QRIS / E-Wallet / Kartu)',
        'moota' => 'Transfer Bank Otomatis (Moota)',
        'manual_transfer' => 'Transfer Bank Manual (Upload Bukti)',
        'cod' => 'Bayar di Tempat (COD)',
    ];

    $existingPayment = null;
    if ($latestInvoice && $latestInvoice->transaction) {
        $existingPayment = [
            'transaction_id' => $latestInvoice->transaction->id,
            'registration_id' => $latestInvoice->transaction->public_ref,
            'payment_gateway' => $latestInvoice->transaction->payment_gateway,
            'snap_token' => $latestInvoice->transaction->snap_token,
            'redirect_url' => route('events.payment', ['slug' => $event->slug, 'transaction' => $latestInvoice->transaction->id]),
            'final_amount' => (float) $latestInvoice->transaction->final_amount,
            'unique_code' => (int) $latestInvoice->transaction->unique_code,
            'payment_channel' => (string) ($latestInvoice->transaction->payment_channel ?? 'bank_transfer'),
            'qris_payload' => $latestInvoice->qris_payload,
        ];
    }

    $statusBadgeClass = $isLocked ? 'bg-amber-400 text-slate-950' : 'bg-neon text-slate-950';
    $statusLabel = $isLocked ? strtoupper($registration->status) : 'DRAFT';
@endphp

<div class="kp-community-show min-h-screen bg-slate-950 text-slate-100 pt-10 sm:pt-12 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                <a href="{{ route('community.register.index') }}" class="hover:text-white transition duration-150">Registrasi Komunitas</a>
                <span aria-hidden="true">/</span>
                <span class="text-slate-200 truncate">{{ $event->name }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl sm:text-3xl text-white truncate">{{ $registration->community_name }}</h1>
                    <p class="text-sm text-slate-300 mt-1">{{ $event->name }}</p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <span class="kp-mono inline-flex items-center h-9 px-3 rounded-md text-xs font-bold {{ $statusBadgeClass }}">
                        {{ $statusLabel }}
                    </span>
                    <a href="{{ route('community.register.index') }}"
                       class="inline-flex items-center justify-center h-9 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs sm:text-sm font-semibold text-white transition duration-150 whitespace-nowrap">
                        Ganti Event
                    </a>
                </div>
            </div>
        </div>

        <div id="communityApp" class="space-y-6" data-locked="{{ $isLocked ? 1 : 0 }}">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Right on Desktop, First on Mobile: Participant Form --}}
                <div class="lg:col-span-7 lg:order-2">
                    <section id="participantFormCard" class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-5 border-b border-slate-800">
                            <div>
                                <h2 id="participantFormTitle" class="text-base sm:text-lg text-white">Tambah Peserta</h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Lengkapi data pelari untuk dimasukkan ke daftar komunitas{{ $promoBuyX > 0 ? " (Promo Beli {$promoBuyX} Gratis 1)" : '' }}.
                                </p>
                            </div>
                            <span class="kp-mono text-xs text-slate-400 shrink-0">Step 01 · Input Anggota</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="p_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Peserta (BIB Name)</label>
                                <input type="text" id="p_name"
                                       class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon"
                                       placeholder="Nama lengkap peserta">
                            </div>

                            <div>
                                <label for="p_category" class="block text-xs font-semibold text-slate-300 mb-1.5">Kategori Lari</label>
                                <select id="p_category"
                                        class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="p_gender" class="block text-xs font-semibold text-slate-300 mb-1.5">Jenis Kelamin</label>
                                <select id="p_gender"
                                        class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                                    <option value="">-- Pilih Gender --</option>
                                    <option value="male">Laki-laki (Male)</option>
                                    <option value="female">Perempuan (Female)</option>
                                </select>
                            </div>

                            <div>
                                <label for="p_jersey_size" class="block text-xs font-semibold text-slate-300 mb-1.5">Ukuran Jersey</label>
                                <select id="p_jersey_size"
                                        class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                                    <option value="">-- Pilih Size --</option>
                                    @foreach(($event->jersey_sizes ?? ['XS','S','M','L','XL','XXL','3XL']) as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="p_id_card" class="block text-xs font-semibold text-slate-300 mb-1.5">No. Identitas (KTP/SIM)</label>
                                <input type="text" id="p_id_card"
                                       class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon"
                                       placeholder="Nomor KTP / SIM / Passport">
                            </div>

                            <div>
                                <label for="p_email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email Peserta</label>
                                <input type="email" id="p_email"
                                       class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon"
                                       placeholder="email@peserta.com">
                            </div>

                            <div>
                                <label for="p_phone" class="block text-xs font-semibold text-slate-300 mb-1.5">No. WhatsApp</label>
                                <input type="text" id="p_phone" inputmode="numeric"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon"
                                       placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="p_address" class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Lengkap</label>
                                <textarea id="p_address" rows="2"
                                          class="w-full px-3.5 py-2.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon"
                                          placeholder="Alamat domisili peserta"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-red-200 bg-slate-950 border border-red-800 rounded-md px-3.5 py-2.5 hidden" id="participantError"></div>
                        <div class="mt-4 text-xs text-slate-100 bg-slate-950 border border-slate-700 rounded-md px-3.5 py-2.5 hidden" id="participantSuccess"></div>

                        {{-- Bottom Form Action Bar (Ergonomic for Mobile & Desktop) --}}
                        <div class="mt-5 pt-4 border-t border-slate-800 flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5">
                            <button type="button" id="btnCancelEditParticipant"
                                    class="hidden w-full sm:w-auto inline-flex items-center justify-center h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-sm font-semibold text-white transition duration-150">
                                Batal Edit
                            </button>
                            <button type="button" id="btnAddParticipant"
                                    class="w-full sm:w-auto inline-flex items-center justify-center h-10 px-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                                + Tambah Peserta
                            </button>
                        </div>
                    </section>
                </div>

                {{-- Left on Desktop, Second on Mobile: PIC & Checkout Invoice --}}
                <div class="lg:col-span-5 lg:order-1 space-y-5">
                    {{-- Data PIC --}}
                    <section class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3 pb-3.5 mb-4 border-b border-slate-800">
                            <div>
                                <h2 class="text-base text-white">Data PIC Komunitas</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Dapat diubah selama status masih draft.</p>
                            </div>
                            <button type="button" id="btnSavePic"
                                    class="inline-flex items-center justify-center h-9 px-3.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150 whitespace-nowrap">
                                Simpan PIC
                            </button>
                        </div>

                        <div class="space-y-3.5">
                            <div>
                                <label for="community_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Komunitas</label>
                                <input type="text" id="community_name" value="{{ $registration->community_name }}"
                                       class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                            </div>
                            <div>
                                <label for="pic_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama PIC</label>
                                <input type="text" id="pic_name" value="{{ $registration->pic_name }}"
                                       class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="pic_email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email PIC</label>
                                    <input type="email" id="pic_email" value="{{ $registration->pic_email }}"
                                           class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                                </div>
                                <div>
                                    <label for="pic_phone" class="block text-xs font-semibold text-slate-300 mb-1.5">WhatsApp PIC</label>
                                    <input type="text" id="pic_phone" value="{{ $registration->pic_phone }}" inputmode="numeric"
                                           class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-slate-100 bg-slate-950 border border-slate-700 rounded-md px-3 py-2 hidden" id="picSuccess"></div>
                    </section>

                    {{-- Generate Invoice --}}
                    <section class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                        <div class="pb-3.5 mb-4 border-b border-slate-800">
                            <h2 class="text-base text-white">Finalisasi &amp; Pembayaran Kolektif</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Buat 1 tagihan untuk seluruh peserta di daftar bawah.</p>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label for="paymentMethod" class="block text-xs font-semibold text-slate-300 mb-1.5">Metode Pembayaran</label>
                                <select id="paymentMethod"
                                        class="w-full h-10 px-3 rounded-md bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-neon">
                                    @foreach($allowedMethods as $m)
                                        <option value="{{ $m }}">{{ $paymentLabels[$m] ?? strtoupper($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" id="btnGenerateInvoice"
                                    class="w-full inline-flex items-center justify-center h-11 px-5 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150">
                                Generate Invoice
                            </button>
                        </div>

                        <div class="mt-3 text-xs text-red-200 bg-slate-950 border border-red-800 rounded-md px-3.5 py-2.5 hidden" id="invoiceError"></div>

                        @if($latestInvoice && $latestInvoice->transaction)
                            <div class="mt-4 bg-slate-950 border border-slate-800 rounded-md p-3.5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold text-white">Invoice Terakhir ({{ strtoupper($latestInvoice->payment_method) }})</div>
                                        <div class="kp-mono text-[11px] text-slate-400 mt-0.5">{{ $latestInvoice->transaction->public_ref }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($latestInvoice->status === 'pending' && $latestInvoice->transaction->payment_status === 'pending' && $isLocked)
                                            <button type="button" id="btnCancelInvoice"
                                                    class="inline-flex items-center justify-center h-9 px-3 rounded-md bg-slate-900 hover:bg-red-950 border border-slate-700 hover:border-red-900 text-xs font-semibold text-red-300 transition duration-150">
                                                Batalkan
                                            </button>
                                        @endif
                                        <button type="button" id="btnOpenExistingInvoice"
                                                class="inline-flex items-center justify-center h-9 px-3.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition duration-150">
                                            Lihat Tagihan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </section>
                </div>
            </div>

            {{-- Bottom Section: Participant List (Desktop Table + Mobile Cards) --}}
            <section class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base sm:text-lg text-white">Daftar Peserta Komunitas</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Periksa kembali data sebelum membuat invoice.</p>
                    </div>
                    <span id="participantCount" class="kp-mono inline-flex items-center h-8 px-3 rounded-md bg-slate-950 border border-slate-800 text-xs font-bold text-neon self-start sm:self-auto">
                        {{ $participants->count() }} peserta
                    </span>
                </div>

                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-950 border-b border-slate-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Peserta</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Kategori &amp; Jersey</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Kontak</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="participantsBody" class="divide-y divide-slate-800">
                            @forelse($participants as $p)
                                <tr data-id="{{ $p->id }}" class="hover:bg-slate-800/50 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="text-sm font-semibold text-white">{{ $p->name }}</div>
                                        <div class="kp-mono text-xs text-slate-400 mt-0.5">{{ $p->id_card ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="text-sm text-slate-200">{{ $p->category?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400 capitalize mt-0.5">
                                            {{ $p->gender ?? '-' }} · Size <span class="kp-mono text-slate-200">{{ $p->jersey_size ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="text-sm text-slate-200">{{ $p->email ?? '-' }}</div>
                                        <div class="kp-mono text-xs text-slate-400 mt-0.5">{{ $p->phone ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" class="btnEditParticipant h-8 px-3 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition" data-id="{{ $p->id }}">
                                                Edit
                                            </button>
                                            <button type="button" class="btnDeleteParticipant h-8 px-3 rounded-md bg-slate-950 hover:bg-red-950 border border-slate-800 hover:border-red-900 text-xs font-medium text-red-300 transition" data-id="{{ $p->id }}">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">
                                        Belum ada peserta yang didaftarkan. Isi formulir di atas untuk menambah peserta pertama.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div id="participantsMobileList" class="sm:hidden divide-y divide-slate-800">
                    @forelse($participants as $p)
                        <div data-id="{{ $p->id }}" class="p-4 space-y-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-white truncate">{{ $p->name }}</div>
                                    <div class="kp-mono text-xs text-slate-400 mt-0.5">{{ $p->id_card ?? '-' }}</div>
                                </div>
                                <span class="kp-mono text-[11px] px-2 py-0.5 rounded bg-slate-800 text-slate-200 shrink-0">{{ $p->jersey_size ?? '-' }}</span>
                            </div>
                            <div class="text-xs text-slate-300">
                                {{ $p->category?->name ?? '-' }} · <span class="capitalize">{{ $p->gender ?? '-' }}</span>
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $p->email ?? '-' }} · <span class="kp-mono">{{ $p->phone ?? '-' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <button type="button" class="btnEditParticipant h-9 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition" data-id="{{ $p->id }}">
                                    Edit
                                </button>
                                <button type="button" class="btnDeleteParticipant h-9 rounded-md bg-slate-950 hover:bg-red-950 border border-slate-800 hover:border-red-900 text-xs font-medium text-red-300 transition" data-id="{{ $p->id }}">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-slate-400">
                            Belum ada peserta yang didaftarkan.
                        </div>
                    @endforelse
                </div>
            </section>
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
            participantSuccess: document.getElementById('participantSuccess'),
            picSuccess: document.getElementById('picSuccess'),
            invoiceError: document.getElementById('invoiceError'),
            participantsBody: document.getElementById('participantsBody'),
            participantsMobileList: document.getElementById('participantsMobileList'),
            participantCount: document.getElementById('participantCount'),
            participantFormTitle: document.getElementById('participantFormTitle'),
            participantFormCard: document.getElementById('participantFormCard'),
        };

        function setDisabled(state) {
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

            document.querySelectorAll('.btnDeleteParticipant, .btnEditParticipant').forEach(function (b) {
                if (state) {
                    b.classList.add('opacity-60', 'cursor-not-allowed');
                } else {
                    b.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            });
        }

        function showMsg(el, msg, autoHideMs) {
            if (!el) return;
            el.textContent = msg;
            el.classList.remove('hidden');
            if (autoHideMs) {
                setTimeout(function () { el.classList.add('hidden'); }, autoHideMs);
            }
        }

        function hideMsg(el) {
            if (!el) return;
            el.textContent = '';
            el.classList.add('hidden');
        }

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
            });
        }

        function renderParticipants(items) {
            els.participantsBody.innerHTML = '';
            if (els.participantsMobileList) els.participantsMobileList.innerHTML = '';
            participantsById = {};

            if (!items || !items.length) {
                const tr = document.createElement('tr');
                tr.id = 'emptyRow';
                tr.innerHTML = '<td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada peserta yang didaftarkan. Isi formulir di atas untuk menambah peserta pertama.</td>';
                els.participantsBody.appendChild(tr);

                if (els.participantsMobileList) {
                    els.participantsMobileList.innerHTML = '<div class="p-8 text-center text-sm text-slate-400">Belum ada peserta yang didaftarkan.</div>';
                }
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
                    <td class="px-5 py-3.5">
                        <div class="text-sm font-semibold text-white">${escapeHtml(p.name || '-')}</div>
                        <div class="kp-mono text-xs text-slate-400 mt-0.5">${escapeHtml(p.id_card || '-')}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="text-sm text-slate-200">${escapeHtml(p.category_name || '-')}</div>
                        <div class="text-xs text-slate-400 capitalize mt-0.5">
                            ${escapeHtml(p.gender || '-')} · Size <span class="kp-mono text-slate-200">${escapeHtml(p.jersey_size || '-')}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="text-sm text-slate-200">${escapeHtml(p.email || '-')}</div>
                        <div class="kp-mono text-xs text-slate-400 mt-0.5">${escapeHtml(p.phone || '-')}</div>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="btnEditParticipant h-8 px-3 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition" data-id="${p.id}">Edit</button>
                            <button type="button" class="btnDeleteParticipant h-8 px-3 rounded-md bg-slate-950 hover:bg-red-950 border border-slate-800 hover:border-red-900 text-xs font-medium text-red-300 transition" data-id="${p.id}">Hapus</button>
                        </div>
                    </td>
                `;
                els.participantsBody.appendChild(tr);

                if (els.participantsMobileList) {
                    const card = document.createElement('div');
                    card.dataset.id = p.id;
                    card.className = 'p-4 space-y-2.5';
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-white truncate">${escapeHtml(p.name || '-')}</div>
                                <div class="kp-mono text-xs text-slate-400 mt-0.5">${escapeHtml(p.id_card || '-')}</div>
                            </div>
                            <span class="kp-mono text-[11px] px-2 py-0.5 rounded bg-slate-800 text-slate-200 shrink-0">${escapeHtml(p.jersey_size || '-')}</span>
                        </div>
                        <div class="text-xs text-slate-300">${escapeHtml(p.category_name || '-')} · <span class="capitalize">${escapeHtml(p.gender || '-')}</span></div>
                        <div class="text-xs text-slate-400">${escapeHtml(p.email || '-')} · <span class="kp-mono">${escapeHtml(p.phone || '-')}</span></div>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" class="btnEditParticipant h-9 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition" data-id="${p.id}">Edit</button>
                            <button type="button" class="btnDeleteParticipant h-9 rounded-md bg-slate-950 hover:bg-red-950 border border-slate-800 hover:border-red-900 text-xs font-medium text-red-300 transition" data-id="${p.id}">Hapus</button>
                        </div>
                    `;
                    els.participantsMobileList.appendChild(card);
                }
            });

            setDisabled(locked);
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
                els.btnAdd.textContent = 'Simpan Perubahan';
                els.btnCancelEdit.classList.remove('hidden');
            } else {
                els.participantFormTitle.textContent = 'Tambah Peserta';
                els.btnAdd.textContent = '+ Tambah Peserta';
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
            if (els.participantFormCard) {
                els.participantFormCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function stopEditParticipant() {
            editingParticipantId = null;
            clearParticipantForm();
            setFormMode('add');
        }

        function handleListClick(e) {
            const btnEdit = e.target.closest('.btnEditParticipant');
            if (btnEdit) {
                hideMsg(els.participantError);
                if (locked) {
                    showMsg(els.participantError, 'Registrasi sudah dikunci (invoice sudah dibuat).');
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

            hideMsg(els.participantError);
            if (locked) {
                showMsg(els.participantError, 'Registrasi sudah dikunci (invoice sudah dibuat).');
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
                        showMsg(els.participantError, (data && data.message) ? data.message : 'Gagal menghapus peserta.');
                        return;
                    }
                    if (editingParticipantId && String(editingParticipantId) === String(id)) {
                        stopEditParticipant();
                    }
                    refreshParticipants();
                })
                .catch(() => showMsg(els.participantError, 'Gagal menghubungi server.'));
        }

        els.participantsBody.addEventListener('click', handleListClick);
        if (els.participantsMobileList) {
            els.participantsMobileList.addEventListener('click', handleListClick);
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
                if (el) el.value = '';
            });
        }

        els.btnCancelEdit.addEventListener('click', function () {
            hideMsg(els.participantError);
            stopEditParticipant();
        });

        els.btnSavePic.addEventListener('click', function () {
            if (locked) return;
            hideMsg(els.invoiceError);
            hideMsg(els.picSuccess);
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
                    showMsg(els.invoiceError, (data && data.message) ? data.message : 'Gagal menyimpan PIC.');
                    return;
                }
                showMsg(els.picSuccess, 'Data PIC berhasil disimpan.', 3000);
            })
            .catch(() => showMsg(els.invoiceError, 'Gagal menghubungi server.'));
        });

        els.btnAdd.addEventListener('click', function () {
            hideMsg(els.participantError);
            hideMsg(els.participantSuccess);
            if (locked) {
                showMsg(els.participantError, 'Registrasi sudah dikunci (invoice sudah dibuat).');
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
                        showMsg(
                            els.participantError,
                            (data && data.message) ? data.message : (isEditing ? 'Gagal mengubah peserta.' : 'Gagal menambah peserta.')
                        );
                        return;
                    }
                    stopEditParticipant();
                    refreshParticipants();
                    showMsg(els.participantSuccess, isEditing ? 'Data peserta diperbarui.' : 'Peserta ditambahkan ke daftar.', 3000);
                })
                .catch(() => showMsg(els.participantError, 'Gagal menghubungi server.'));
        });

        function openInvoicePayment(payload) {
            if (!payload) return;
            if (payload.payment_gateway === 'midtrans' && payload.snap_token && typeof window.snap !== 'undefined') {
                window.snap.pay(payload.snap_token, {
                    onSuccess: function () { window.location.reload(); },
                    onPending: function () { window.location.reload(); },
                    onError: function () { window.location.reload(); },
                    onClose: function () { window.location.reload(); },
                });
                return;
            }
            if (payload.payment_gateway === 'moota' && window.RuangLariCommunityPayment) {
                window.RuangLariCommunityPayment.open({
                    transaction_id: payload.transaction_id,
                    registration_id: payload.registration_id,
                    final_amount: payload.final_amount,
                    unique_code: payload.unique_code,
                    payment_channel: payload.payment_channel,
                    qris_payload: payload.qris_payload,
                    phone: document.getElementById('pic_phone').value || '',
                });
                return;
            }
            if (payload.redirect_url) {
                window.location.href = payload.redirect_url;
            }
        }

        els.btnInvoice.addEventListener('click', function () {
            if (locked) return;
            hideMsg(els.invoiceError);
            const method = document.getElementById('paymentMethod').value || @json($allowedMethods[0] ?? 'midtrans');
            fetch(urls.invoice, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ payment_method: method })
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    showMsg(els.invoiceError, (data && data.message) ? data.message : 'Gagal membuat invoice.');
                    return;
                }
                locked = true;
                app.setAttribute('data-locked', '1');
                setDisabled(true);
                openInvoicePayment(data);
            })
            .catch(() => showMsg(els.invoiceError, 'Gagal menghubungi server.'));
        });

        if (els.btnCancelInvoice) {
            els.btnCancelInvoice.addEventListener('click', function () {
                hideMsg(els.invoiceError);
                hideMsg(els.participantError);
                if (!confirm('Batalkan invoice dan buka kembali registrasi ke DRAFT?')) return;

                fetch(urls.cancelInvoice, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data || !data.success) {
                            showMsg(els.invoiceError, (data && data.message) ? data.message : 'Gagal membatalkan invoice.');
                            return;
                        }
                        locked = false;
                        app.setAttribute('data-locked', '0');
                        setDisabled(false);
                        stopEditParticipant();
                        refreshParticipants();
                        window.location.reload();
                    })
                    .catch(() => showMsg(els.invoiceError, 'Gagal menghubungi server.'));
            });
        }

        if (els.btnOpenExisting) {
            const existing = @json($existingPayment);

            els.btnOpenExisting.addEventListener('click', function () {
                if (!existing) return;
                locked = true;
                app.setAttribute('data-locked', '1');
                setDisabled(true);
                openInvoicePayment(existing);
            });
        }

        setFormMode('add');
        setDisabled(locked);
        refreshParticipants();
    })();
</script>
@endsection
