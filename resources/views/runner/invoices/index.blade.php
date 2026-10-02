@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Tagihan Coach & Latihan | Ruang Lari')

@push('styles')
<style>
    .invoice-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
</style>
@endpush

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <p class="text-xs text-slate-400 mb-1">Keuangan & Pelatihan</p>
            <h1 class="invoice-heading text-2xl text-white">Tagihan Coach & Latihan</h1>
            <p class="text-xs text-slate-300 mt-1">
                Daftar invoice sesi personal, program pelatihan, dan retainer bulanan yang diterbitkan oleh pelatih Anda.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="px-3.5 py-2 rounded-md bg-slate-900 border border-slate-800 text-xs">
                <span class="text-slate-400 mr-1.5">Saldo Wallet:</span>
                <span class="font-mono font-bold text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</span>
            </div>
            <a href="{{ route('wallet.index', ['action' => 'deposit']) }}" class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-semibold transition">
                + Top Up Saldo
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-3.5 rounded-md bg-emerald-950 border border-emerald-800 text-emerald-200 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="p-3.5 rounded-md bg-red-950 border border-red-800 text-red-200 text-xs font-medium">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Menunggu Pembayaran -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Tagihan Belum Lunas</span>
            <div class="text-xl font-bold font-mono text-white">
                Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ $unpaidCount }} invoice perlu diselesaikan
            </p>
        </div>

        <!-- Card 2: Saldo Dompet Digital -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Saldo RuangLari Wallet</span>
            <div class="text-xl font-bold font-mono text-emerald-400">
                Rp {{ number_format($walletBalance, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Dapat digunakan untuk pelunasan instan 1-klik
            </p>
        </div>

        <!-- Card 3: Status Tagihan Terbayar -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Tagihan Lunas</span>
            <div class="text-xl font-bold font-mono text-white">
                {{ $counts['paid'] ?? 0 }} Invoice
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Riwayat pembayaran sesi dan paket selesai
            </p>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-800 text-xs">
        <a href="{{ route('runner.invoices.index', ['status' => 'all'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? 'all') === 'all' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Semua Tagihan ({{ $counts['all'] ?? 0 }})
        </a>
        <a href="{{ route('runner.invoices.index', ['status' => 'unpaid'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? '') === 'unpaid' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Menunggu Pembayaran ({{ $counts['unpaid'] ?? 0 }})
        </a>
        <a href="{{ route('runner.invoices.index', ['status' => 'overdue'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? '') === 'overdue' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Jatuh Tempo ({{ $counts['overdue'] ?? 0 }})
        </a>
        <a href="{{ route('runner.invoices.index', ['status' => 'paid'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? '') === 'paid' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Lunas ({{ $counts['paid'] ?? 0 }})
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="p-4 rounded-lg bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('runner.invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="status" value="{{ $status ?? 'all' }}">

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Cari No. Invoice / Coach</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nomor invoice atau nama..." class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder-slate-400 focus:border-slate-600 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Pelatih (Coach)</label>
                <select name="coach_id" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="">Semua Pelatih</option>
                    @foreach($coaches as $c)
                        <option value="{{ $c->id }}" {{ request('coach_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Model Tarif</label>
                <select name="pricing_type" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="">Semua Model</option>
                    <option value="monthly" {{ request('pricing_type') == 'monthly' ? 'selected' : '' }}>Bulanan (Monthly Retainer)</option>
                    <option value="hourly" {{ request('pricing_type') == 'hourly' ? 'selected' : '' }}>Per Jam / Sesi</option>
                    <option value="weekly" {{ request('pricing_type') == 'weekly' ? 'selected' : '' }}>Mingguan (Weekly)</option>
                    <option value="daily" {{ request('pricing_type') == 'daily' ? 'selected' : '' }}>Harian (Daily Pass)</option>
                    <option value="one_time" {{ request('pricing_type') == 'one_time' ? 'selected' : '' }}>Paket Sekali Bayar</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-md bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Terapkan Filter
                </button>
                @if(request()->anyFilled(['search', 'coach_id', 'pricing_type']))
                    <a href="{{ route('runner.invoices.index', ['status' => $status ?? 'all']) }}" class="py-2 px-3 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Invoices Table Section -->
    <div class="rounded-lg bg-slate-900 border border-slate-800 overflow-hidden">
        @if($invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="text-[11px] text-slate-400 uppercase bg-slate-950 border-b border-slate-800 font-medium">
                            <th class="py-3 px-4">No. Invoice</th>
                            <th class="py-3 px-4">Pelatih & Layanan</th>
                            <th class="py-3 px-4">Model & Qty</th>
                            <th class="py-3 px-4">Periode & Jatuh Tempo</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 font-sans">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-800 transition">
                                <!-- No Invoice -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-white">
                                    {{ $inv->invoice_number }}
                                    <div class="text-[10px] text-slate-400 font-normal font-sans">
                                        {{ $inv->created_at->format('d M Y') }}
                                    </div>
                                </td>

                                <!-- Coach & Program -->
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-100">{{ $inv->coach->name ?? 'Coach' }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $inv->program->title ?? 'Custom Coaching' }}
                                    </div>
                                </td>

                                <!-- Model & Qty -->
                                <td class="py-3.5 px-4">
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300">
                                        {{ $inv->pricing_label }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Qty: {{ $inv->quantity }} {{ $inv->pricing_type === 'hourly' ? 'Sesi' : 'Unit' }}
                                    </div>
                                </td>

                                <!-- Periode / Due Date -->
                                <td class="py-3.5 px-4 text-slate-300">
                                    @if($inv->period_start)
                                        <div>{{ \Carbon\Carbon::parse($inv->period_start)->format('d M Y') }}</div>
                                    @endif
                                    @if($inv->due_date)
                                        <div class="text-[10px] {{ $inv->is_overdue ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                                            Batas: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Nominal -->
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                                    Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($inv->payment_status === 'paid')
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-800">
                                            Lunas
                                        </span>
                                    @elseif($inv->is_overdue)
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-rose-950 text-rose-300 border border-rose-800">
                                            Jatuh Tempo
                                        </span>
                                    @elseif($inv->payment_status === 'cancelled')
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
                                            Dibatalkan
                                        </span>
                                    @elseif($inv->payment_proof)
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-sky-950 text-sky-300 border border-sky-800" title="Bukti transfer telah diunggah dan menunggu verifikasi pelatih">
                                            Menunggu Verifikasi
                                        </span>
                                    @else
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- Detail Button -->
                                        <button type="button" 
                                            onclick="openDetailModal({{ $inv->id }})"
                                            class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-[11px] font-medium transition">
                                            Rincian
                                        </button>

                                        @if($inv->payment_status !== 'paid' && $inv->payment_status !== 'cancelled')
                                            <!-- Pay with Wallet Button (if balance sufficient) -->
                                            @if($walletBalance >= (float) $inv->amount)
                                                <button type="button" 
                                                    onclick="openWalletPayModal({{ $inv->id }}, '{{ $inv->invoice_number }}', '{{ $inv->coach->name ?? 'Coach' }}', '{{ number_format($inv->amount, 0, ',', '.') }}', {{ (float) $inv->amount }})"
                                                    class="px-2.5 py-1 rounded-md bg-emerald-900 hover:bg-emerald-800 text-emerald-200 border border-emerald-800 text-[11px] font-medium transition">
                                                    Bayar Saldo
                                                </button>
                                            @endif

                                            <!-- Confirm / Upload Transfer Proof Button -->
                                            <button type="button" 
                                                onclick="openConfirmModal({{ $inv->id }}, '{{ $inv->invoice_number }}', '{{ $inv->coach->name ?? 'Coach' }}', '{{ number_format($inv->amount, 0, ',', '.') }}')"
                                                class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-sky-900 hover:border-sky-800 hover:text-sky-200 text-slate-300 border border-slate-700 text-[11px] font-medium transition">
                                                {{ $inv->payment_proof ? 'Ganti Bukti' : 'Konfirmasi Bayar' }}
                                            </button>
                                        @endif

                                        <!-- Print Button -->
                                        <a href="{{ route('runner.invoices.print', $inv->id) }}" target="_blank" class="px-2 py-1 rounded-md bg-slate-950 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 text-[11px] font-medium transition" title="Cetak Invoice">
                                            Cetak
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $invoices->links() }}
                </div>
            @endif
        @else
            <div class="py-16 text-center">
                <div class="text-sm font-semibold text-slate-300">Belum Ada Tagihan Ditemukan</div>
                <p class="text-xs text-slate-400 mt-1">
                    @if(request()->anyFilled(['search', 'coach_id', 'pricing_type', 'status']))
                        Tidak ada catatan tagihan yang sesuai dengan filter yang dipilih.
                    @else
                        Pelatih Anda belum menerbitkan invoice tagihan untuk Anda.
                    @endif
                </p>
                @if(request()->anyFilled(['search', 'coach_id', 'pricing_type', 'status']))
                    <a href="{{ route('runner.invoices.index') }}" class="inline-block mt-4 px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                        Reset Filter
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- MODAL 1: RINCIAN INVOICE -->
<div id="modal-invoice-detail" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg rounded-lg bg-slate-900 border border-slate-800 p-6 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <div>
                <h3 class="invoice-heading text-base text-white">Rincian Tagihan Coach</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="detail_inv_number">Memuat data invoice...</p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-white text-base font-bold">&times;</button>
        </div>

        <div id="detail_modal_body" class="space-y-4 text-xs">
            <div class="text-center py-8 text-slate-400">
                Memuat rincian invoice...
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex items-center justify-between gap-2 mt-4">
            <a id="detail_print_link" href="#" target="_blank" class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Cetak Invoice
            </a>
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL 2: KONFIRMASI PEMBAYARAN MANUAL (UPLOAD BUKTI) -->
<div id="modal-confirm-payment" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-lg bg-slate-900 border border-slate-800 p-6 relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <div>
                <h3 class="invoice-heading text-base text-white">Konfirmasi Pembayaran</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="confirm_modal_subtitle">Unggah bukti transfer manual</p>
            </div>
            <button type="button" onclick="closeConfirmModal()" class="text-slate-400 hover:text-white text-base font-bold">&times;</button>
        </div>

        <form id="confirm-payment-form" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Metode Pembayaran Dilakukan <span class="text-red-400">*</span></label>
                <select name="payment_method" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="bank_transfer">Transfer Bank Langsung</option>
                    <option value="cash">Tunai / Langsung ke Coach</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Unggah Bukti Transfer / Resi (JPG, PNG, PDF max 4MB)</label>
                <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp,application/pdf" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-slate-300 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 outline-none">
                <p class="text-[10px] text-slate-400 mt-1">Sertakan foto tangkapan layar m-banking atau resi ATM.</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Ditransfer dari Rekening BCA a/n Budi pada jam 14:00" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeConfirmModal()" class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-emerald-900 hover:bg-emerald-800 border border-emerald-800 text-emerald-200 text-xs font-semibold transition">
                    Kirim Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: BAYAR DENGAN SALDO WALLET -->
<div id="modal-pay-wallet" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-lg bg-slate-900 border border-slate-800 p-6 relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <div>
                <h3 class="invoice-heading text-base text-white">Pelunasan via Saldo Wallet</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="wallet_modal_subtitle">Pelunasan instan</p>
            </div>
            <button type="button" onclick="closeWalletPayModal()" class="text-slate-400 hover:text-white text-base font-bold">&times;</button>
        </div>

        <form id="wallet-pay-form" method="POST" action="" class="space-y-4">
            @csrf

            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between items-center text-slate-300">
                    <span>Nominal Tagihan:</span>
                    <span id="wallet_modal_amount" class="font-mono font-bold text-white">Rp 0</span>
                </div>
                <div class="flex justify-between items-center text-slate-300">
                    <span>Saldo Wallet Anda:</span>
                    <span class="font-mono font-bold text-emerald-400">Rp {{ number_format($walletBalance, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex justify-between items-center text-slate-300">
                    <span>Sisa Saldo Setelah Bayar:</span>
                    <span id="wallet_modal_remaining" class="font-mono font-bold text-white">Rp 0</span>
                </div>
            </div>

            <p class="text-xs text-slate-400">
                Saldo Anda akan langsung dipotong dan status tagihan otomatis menjadi <strong class="text-emerald-400">LUNAS</strong>. Sesi atau masa aktif program latihan Anda akan diperpanjang secara otomatis.
            </p>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeWalletPayModal()" class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-emerald-900 hover:bg-emerald-800 border border-emerald-800 text-emerald-200 text-xs font-semibold transition">
                    Konfirmasi Bayar Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const currentWalletBalance = {{ (float) $walletBalance }};

function openDetailModal(invoiceId) {
    const modal = document.getElementById('modal-invoice-detail');
    const body = document.getElementById('detail_modal_body');
    const titleNum = document.getElementById('detail_inv_number');
    const printLink = document.getElementById('detail_print_link');

    modal.classList.remove('hidden');
    body.innerHTML = '<div class="text-center py-8 text-slate-400">Memuat rincian invoice...</div>';
    titleNum.innerText = 'Memuat...';

    fetch(`/runner/invoices/${invoiceId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        titleNum.innerText = `${data.invoice_number} • ${data.created_at}`;
        printLink.href = `/runner/invoices/${data.id}/print`;

        let statusBadge = '';
        if (data.payment_status === 'paid') {
            statusBadge = '<span class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-800">Lunas</span>';
        } else if (data.is_overdue) {
            statusBadge = '<span class="text-[11px] font-medium px-2 py-0.5 rounded bg-rose-950 text-rose-300 border border-rose-800">Jatuh Tempo</span>';
        } else if (data.payment_proof_url) {
            statusBadge = '<span class="text-[11px] font-medium px-2 py-0.5 rounded bg-sky-950 text-sky-300 border border-sky-800">Menunggu Verifikasi Coach</span>';
        } else {
            statusBadge = '<span class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800">Pending</span>';
        }

        let proofHtml = '';
        if (data.payment_proof_url) {
            proofHtml = `
                <div class="mt-3 p-3 bg-slate-950 rounded-md border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-semibold block mb-1">Bukti Pembayaran Diunggah:</span>
                    <a href="${data.payment_proof_url}" target="_blank" class="text-xs text-sky-400 hover:underline font-mono">
                        Lihat Berkas Bukti Transfer &rarr;
                    </a>
                </div>
            `;
        }

        let notesHtml = '';
        if (data.notes) {
            notesHtml = `
                <div class="p-3 bg-slate-950 rounded-md border border-slate-800 text-slate-300">
                    <span class="text-[11px] text-slate-400 font-semibold block mb-1">Catatan & Rekening Pelatih:</span>
                    <div class="whitespace-pre-line text-xs">${data.notes}</div>
                </div>
            `;
        }

        body.innerHTML = `
            <div class="flex items-center justify-between p-3 rounded-md bg-slate-950 border border-slate-800">
                <div>
                    <span class="text-slate-400 text-[11px] block">Pelatih</span>
                    <div class="font-bold text-white text-sm">${data.coach_name}</div>
                    <div class="text-[11px] text-slate-400">${data.coach_email} ${data.coach_phone ? '• ' + data.coach_phone : ''}</div>
                </div>
                <div>${statusBadge}</div>
            </div>

            <div class="space-y-1.5 p-3 rounded-md bg-slate-950 border border-slate-800">
                <div class="flex justify-between">
                    <span class="text-slate-400">Layanan / Program:</span>
                    <span class="text-white font-semibold">${data.program_title}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Model Tarif:</span>
                    <span class="text-white font-mono">${data.pricing_label}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kuantitas:</span>
                    <span class="text-white font-mono">${data.quantity} ${data.pricing_type === 'hourly' ? 'Sesi' : 'Unit'}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Periode:</span>
                    <span class="text-slate-300">${data.period_start} s/d ${data.period_end}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Batas Jatuh Tempo:</span>
                    <span class="${data.is_overdue ? 'text-rose-400 font-bold' : 'text-slate-300'}">${data.due_date}</span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-200">Total Tagihan:</span>
                    <span class="text-white font-mono text-base">${data.amount_formatted}</span>
                </div>
            </div>

            ${notesHtml}
            ${proofHtml}
        `;
    })
    .catch(err => {
        body.innerHTML = '<div class="text-center py-8 text-rose-400">Gagal memuat rincian invoice.</div>';
    });
}

function closeDetailModal() {
    document.getElementById('modal-invoice-detail').classList.add('hidden');
}

function openConfirmModal(invoiceId, invoiceNumber, coachName, amountFormatted) {
    document.getElementById('confirm_modal_subtitle').innerText = `${invoiceNumber} • Pelatih: ${coachName} (Rp ${amountFormatted})`;
    document.getElementById('confirm-payment-form').action = `/runner/invoices/${invoiceId}/confirm-payment`;
    document.getElementById('modal-confirm-payment').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('modal-confirm-payment').classList.add('hidden');
}

function openWalletPayModal(invoiceId, invoiceNumber, coachName, amountFormatted, amountNumeric) {
    document.getElementById('wallet_modal_subtitle').innerText = `${invoiceNumber} • Pelatih: ${coachName}`;
    document.getElementById('wallet_modal_amount').innerText = `Rp ${amountFormatted}`;
    
    const remaining = currentWalletBalance - amountNumeric;
    const remainingFormatted = new Intl.NumberFormat('id-ID').format(Math.max(0, remaining));
    document.getElementById('wallet_modal_remaining').innerText = `Rp ${remainingFormatted}`;

    document.getElementById('wallet-pay-form').action = `/runner/invoices/${invoiceId}/pay-wallet`;
    document.getElementById('modal-pay-wallet').classList.remove('hidden');
}

function closeWalletPayModal() {
    document.getElementById('modal-pay-wallet').classList.add('hidden');
}

// Close modals on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailModal();
        closeConfirmModal();
        closeWalletPayModal();
    }
});
</script>
@endpush
@endsection
