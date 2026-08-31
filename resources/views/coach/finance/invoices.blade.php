@extends('layouts.coach')

@section('title', 'Tagihan & Langganan Atlet')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6 font-sans">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-6">
        <div>
            <p class="text-xs text-slate-400 mb-1">Billing & Invoicing</p>
            <h1 class="text-2xl font-bold text-white">Manajemen Tagihan Atlet</h1>
            <p class="text-xs text-slate-300 mt-1">
                Kelola invoice per jam/sesi, harian, mingguan, maupun langganan bulanan atlet secara terpusat.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('coach.finance.index') }}" class="px-3.5 py-2 rounded-md bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition">
                Ringkasan Saldo
            </a>
            <button type="button" onclick="openCreateInvoiceModal()" class="px-4 py-2 rounded-md bg-neon text-dark hover:brightness-110 text-xs font-bold transition">
                + Buat Tagihan Baru
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-3.5 rounded-md bg-emerald-900 border border-emerald-800 text-emerald-200 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-3.5 rounded-md bg-red-900 border border-red-800 text-red-200 text-xs font-medium">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-800 text-xs">
        <a href="{{ route('coach.invoices.index', ['status' => 'all'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? 'all') === 'all' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Semua Tagihan ({{ $counts['all'] ?? 0 }})
        </a>
        <a href="{{ route('coach.invoices.index', ['status' => 'unpaid'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? '') === 'unpaid' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Menunggu Pembayaran ({{ $counts['unpaid'] ?? 0 }})
        </a>
        <a href="{{ route('coach.invoices.index', ['status' => 'overdue'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? '') === 'overdue' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Jatuh Tempo ({{ $counts['overdue'] ?? 0 }})
        </a>
        <a href="{{ route('coach.invoices.index', ['status' => 'paid'] + request()->except('status', 'page')) }}" 
            class="px-3.5 py-2 rounded-md font-semibold transition whitespace-nowrap {{ ($status ?? '') === 'paid' ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            Lunas ({{ $counts['paid'] ?? 0 }})
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-lg bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('coach.invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="status" value="{{ $status ?? 'all' }}">

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Cari Atlet / No. Invoice</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau invoice..." class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white placeholder-slate-400 focus:border-slate-600 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Program / Layanan</label>
                <select name="program_id" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="">Semua Program</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Model Tarif</label>
                <select name="pricing_type" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="">Semua Model</option>
                    <option value="monthly" {{ request('pricing_type') == 'monthly' ? 'selected' : '' }}>Bulanan (Retainer)</option>
                    <option value="hourly" {{ request('pricing_type') == 'hourly' ? 'selected' : '' }}>Per Jam / Sesi</option>
                    <option value="weekly" {{ request('pricing_type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="daily" {{ request('pricing_type') == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="one_time" {{ request('pricing_type') == 'one_time' ? 'selected' : '' }}>Paket Utuh</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-md bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'program_id', 'pricing_type']))
                    <a href="{{ route('coach.invoices.index', ['status' => $status ?? 'all']) }}" class="py-2 px-3 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white text-xs font-semibold transition">
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
                            <th class="py-3 px-4">Atlet & Program</th>
                            <th class="py-3 px-4">Model & Qty</th>
                            <th class="py-3 px-4">Periode</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 font-sans">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-800 transition">
                                <!-- No Invoice -->
                                <td class="py-3 px-4 font-mono font-semibold text-white">
                                    {{ $inv->invoice_number }}
                                    <div class="text-[10px] text-slate-400 font-normal font-sans">
                                        {{ $inv->created_at->format('d M Y') }}
                                    </div>
                                </td>

                                <!-- Atlet & Program -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-100">{{ $inv->runner->name ?? 'Atlet' }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $inv->program->title ?? 'Custom Coaching' }}
                                    </div>
                                </td>

                                <!-- Model & Qty -->
                                <td class="py-3 px-4">
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 border border-slate-700">
                                        {{ $inv->pricing_label }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Qty: {{ $inv->quantity }} {{ $inv->pricing_type === 'hourly' ? 'Sesi' : 'Unit' }}
                                    </div>
                                </td>

                                <!-- Periode / Due Date -->
                                <td class="py-3 px-4 text-slate-300">
                                    @if($inv->period_start)
                                        <div>{{ \Carbon\Carbon::parse($inv->period_start)->format('d M Y') }}</div>
                                    @endif
                                    @if($inv->due_date)
                                        <div class="text-[10px] {{ $inv->is_overdue ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                                            Tempo: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Nominal -->
                                <td class="py-3 px-4 text-right font-mono font-semibold text-white">
                                    Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4 text-center">
                                    @if($inv->payment_status === 'paid')
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 border border-emerald-800">
                                            Lunas
                                        </span>
                                    @elseif($inv->is_overdue)
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-red-900 text-red-300 border border-red-800">
                                            Jatuh Tempo
                                        </span>
                                    @elseif($inv->payment_status === 'cancelled')
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-900 text-amber-300 border border-amber-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($inv->payment_status !== 'paid' && $inv->payment_status !== 'cancelled')
                                            <!-- Mark Paid Button -->
                                            <button type="button" 
                                                onclick="openMarkPaidModal('{{ $inv->id }}', '{{ $inv->invoice_number }}', '{{ $inv->runner->name ?? 'Atlet' }}', '{{ number_format($inv->amount, 0, ',', '.') }}')"
                                                class="px-2.5 py-1 rounded-md bg-emerald-900 hover:bg-emerald-800 text-emerald-200 border border-emerald-800 text-[11px] font-medium transition">
                                                Tandai Lunas
                                            </button>

                                            <!-- Cancel Button -->
                                            <form method="POST" action="{{ route('coach.invoices.cancel', $inv->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan tagihan ini?');" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded-md bg-slate-800 hover:bg-red-900 hover:border-red-800 hover:text-red-200 text-slate-300 border border-slate-700 text-[11px] font-medium transition">
                                                    Batal
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] font-mono text-slate-400">{{ $inv->payment_method ?? 'Verified' }}</span>
                                        @endif
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
                <p class="text-xs text-slate-400 mt-1">Tidak ada catatan tagihan atlet yang sesuai dengan filter ini.</p>
                <button type="button" onclick="openCreateInvoiceModal()" class="mt-4 px-4 py-2 rounded-md bg-neon text-dark text-xs font-bold hover:brightness-110 transition">
                    + Buat Tagihan Baru
                </button>
            </div>
        @endif
    </div>
</div>

<!-- MODAL 1: BUAT TAGIHAN BARU -->
<div id="modal-create-invoice" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg rounded-lg bg-slate-900 border border-slate-800 p-6 relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <div>
                <h3 class="text-base font-semibold text-white">Buat Tagihan Baru untuk Atlet</h3>
                <p class="text-xs text-slate-400 mt-0.5">Tentukan model tarif, kuantitas sesi/durasi, dan nominal tagihan.</p>
            </div>
            <button type="button" onclick="closeCreateInvoiceModal()" class="text-slate-400 hover:text-white text-base font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('coach.invoices.store') }}" class="space-y-4">
            @csrf

            <!-- 1. Pilih Atlet -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Pilih Atlet <span class="text-red-400">*</span></label>
                <select name="runner_id" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="">-- Pilih Atlet Bimbingan --</option>
                    @foreach($athletes as $athlete)
                        <option value="{{ $athlete->id }}">{{ $athlete->name }} ({{ $athlete->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Pilih Program -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Program / Layanan</label>
                <select name="program_id" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="">-- Layanan Umum / Custom Coaching --</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->display_price_formatted }})</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Model Tarif & Kuantitas -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Model Tarif <span class="text-red-400">*</span></label>
                    <select name="pricing_type" id="invoice_pricing_type" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                        <option value="monthly">Bulanan (Retainer)</option>
                        <option value="hourly">Per Jam / Sesi</option>
                        <option value="weekly">Mingguan</option>
                        <option value="daily">Harian (Daily Pass)</option>
                        <option value="one_time">Sekali Bayar (Paket Utuh)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kuantitas / Sesi <span class="text-red-400">*</span></label>
                    <input type="number" name="quantity" min="1" value="1" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none font-mono">
                </div>
            </div>

            <!-- 4. Nominal Tagihan -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Total Nominal Tagihan (Rp) <span class="text-red-400">*</span></label>
                <input type="number" name="amount" min="0" step="1000" placeholder="Contoh: 250000" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none font-mono font-semibold">
            </div>

            <!-- 5. Periode & Jatuh Tempo -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Periode Mulai</label>
                    <input type="date" name="period_start" value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Batas Pembayaran</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                </div>
            </div>

            <!-- 6. Catatan -->
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan / Rekening Pembayaran</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Transfer ke BCA 1234567890 a/n Coach Name" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none"></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCreateInvoiceModal()" class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-neon text-dark hover:brightness-110 text-xs font-bold transition">
                    Simpan & Terbitkan Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: KONFIRMASI LUNAS MANUAL -->
<div id="modal-mark-paid" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-lg bg-slate-900 border border-slate-800 p-6 relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <div>
                <h3 class="text-base font-semibold text-white">Konfirmasi Pelunasan Tagihan</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="paid_modal_subtitle">Invoice Pelunasan</p>
            </div>
            <button type="button" onclick="closeMarkPaidModal()" class="text-slate-400 hover:text-white text-base font-bold">&times;</button>
        </div>

        <form id="mark-paid-form" method="POST" action="" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Metode Pembayaran</label>
                <select name="payment_method" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                    <option value="bank_transfer">Transfer Bank Langsung</option>
                    <option value="cash">Tunai / Cash</option>
                    <option value="wallet">Dompet / Saldo Digital</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Diterima transfer via BCA tgl 31/08" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeMarkPaidModal()" class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-emerald-900 hover:bg-emerald-800 border border-emerald-800 text-emerald-200 text-xs font-semibold transition">
                    Konfirmasi Lunas
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateInvoiceModal() {
    document.getElementById('modal-create-invoice').classList.remove('hidden');
}
function closeCreateInvoiceModal() {
    document.getElementById('modal-create-invoice').classList.add('hidden');
}
function openMarkPaidModal(invoiceId, invoiceNumber, athleteName, amount) {
    document.getElementById('paid_modal_subtitle').innerText = `${invoiceNumber} - ${athleteName} (Rp ${amount})`;
    document.getElementById('mark-paid-form').action = `/coach/invoices/${invoiceId}/mark-paid`;
    document.getElementById('modal-mark-paid').classList.remove('hidden');
}
function closeMarkPaidModal() {
    document.getElementById('modal-mark-paid').classList.add('hidden');
}
</script>
@endsection
