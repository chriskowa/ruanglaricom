@extends('layouts.coach')

@section('title', 'Keuangan & Saldo Coach')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8 font-sans">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-6">
        <div>
            <p class="text-xs text-slate-400 mb-1">Financial Desk</p>
            <h1 class="text-2xl font-bold text-white">Keuangan & Saldo Coach</h1>
            <p class="text-xs text-slate-300 mt-1">
                Pantau total pendapatan dari atlet, saldo tersedia, estimasi MRR langganan, dan riwayat penarikan dana.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('coach.invoices.index') }}" class="px-3.5 py-2 rounded-md bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition">
                Tagihan Atlet
            </a>
            <a href="{{ route('coach.withdrawals.index') }}" class="px-3.5 py-2 rounded-md bg-neon text-dark hover:brightness-110 text-xs font-bold transition">
                Tarik Dana
            </a>
        </div>
    </div>

    <!-- Alert / Notification -->
    @if(session('success'))
        <div class="p-3.5 rounded-md bg-emerald-900 border border-emerald-800 text-emerald-200 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Saldo Siap Ditarik -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Saldo Siap Ditarik</span>
                <div class="text-xl sm:text-2xl font-bold text-white font-mono mt-1">
                    Rp {{ number_format($availableBalance, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Menunggu: Rp {{ number_format($pendingWithdrawals, 0, ',', '.') }}</span>
                <a href="{{ route('coach.withdrawals.index') }}" class="text-neon hover:underline font-semibold text-xs">Tarik &rarr;</a>
            </div>
        </div>

        <!-- 2. Total Pendapatan Bersih -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Total Pendapatan</span>
                <div class="text-xl sm:text-2xl font-bold text-white font-mono mt-1">
                    Rp {{ number_format($totalNetRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                Total dicairkan: Rp {{ number_format($completedWithdrawals, 0, ',', '.') }}
            </div>
        </div>

        <!-- 3. Estimasi MRR (Langganan Bulanan/Mingguan) -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Estimasi MRR</span>
                <div class="text-xl sm:text-2xl font-bold text-white font-mono mt-1">
                    Rp {{ number_format($estimatedMRR, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                {{ $activeSubscriptionsCount }} atlet langganan aktif
            </div>
        </div>

        <!-- 4. Tagihan Belum Lunas -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Tagihan Menunggu</span>
                <div class="text-xl sm:text-2xl font-bold text-amber-300 font-mono mt-1">
                    Rp {{ number_format($unpaidInvoicesAmount, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">{{ $unpaidInvoicesCount }} invoice pending</span>
                <a href="{{ route('coach.invoices.index', ['status' => 'unpaid']) }}" class="text-amber-400 hover:underline font-semibold text-xs">Lihat &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown by Pricing Model -->
    <div class="rounded-lg bg-slate-900 border border-slate-800 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
            <div>
                <h2 class="text-base font-semibold text-white">Rincian Pendapatan per Model Pembayaran</h2>
                <p class="text-xs text-slate-400 mt-0.5">Distribusi penerimaan coach berdasarkan model tarif yang digunakan atlet.</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">Total Lunas</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <!-- Bulanan -->
            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 border-l-2 border-l-sky-500">
                <div class="text-xs text-slate-400">Bulanan (Retainer)</div>
                <div class="text-sm sm:text-base font-bold text-white font-mono mt-1">
                    Rp {{ number_format($revenueByPricingType['monthly'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- Per Jam / Sesi -->
            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 border-l-2 border-l-emerald-500">
                <div class="text-xs text-slate-400">Per Jam / Sesi</div>
                <div class="text-sm sm:text-base font-bold text-white font-mono mt-1">
                    Rp {{ number_format($revenueByPricingType['hourly'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- Mingguan -->
            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 border-l-2 border-l-indigo-500">
                <div class="text-xs text-slate-400">Mingguan</div>
                <div class="text-sm sm:text-base font-bold text-white font-mono mt-1">
                    Rp {{ number_format($revenueByPricingType['weekly'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- Harian -->
            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 border-l-2 border-l-amber-500">
                <div class="text-xs text-slate-400">Harian (Daily)</div>
                <div class="text-sm sm:text-base font-bold text-white font-mono mt-1">
                    Rp {{ number_format($revenueByPricingType['daily'] ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- One Time Program -->
            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 border-l-2 border-l-rose-500">
                <div class="text-xs text-slate-400">Paket Utuh</div>
                <div class="text-sm sm:text-base font-bold text-white font-mono mt-1">
                    Rp {{ number_format($revenueByPricingType['one_time'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Invoices & Recent Withdrawals -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Invoices (2 cols on desktop) -->
        <div class="lg:col-span-2 rounded-lg bg-slate-900 border border-slate-800 p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                    <div>
                        <h2 class="text-base font-semibold text-white">Tagihan Atlet Terbaru</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Riwayat pembuatan dan status pembayaran invoice atlet.</p>
                    </div>
                    <a href="{{ route('coach.invoices.index') }}" class="text-xs text-neon hover:underline font-semibold">
                        Kelola Semua &rarr;
                    </a>
                </div>

                @if($recentInvoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="text-[11px] text-slate-400 uppercase bg-slate-950 border-b border-slate-800 font-medium">
                                    <th class="py-2.5 px-3">No. Invoice</th>
                                    <th class="py-2.5 px-3">Atlet</th>
                                    <th class="py-2.5 px-3">Model</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 font-sans">
                                @foreach($recentInvoices as $inv)
                                    <tr class="hover:bg-slate-800 transition">
                                        <td class="py-2.5 px-3 font-mono font-semibold text-white">
                                            {{ $inv->invoice_number }}
                                        </td>
                                        <td class="py-2.5 px-3 font-medium text-slate-200">
                                            {{ $inv->runner->name ?? 'Atlet' }}
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-300">
                                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700">
                                                {{ $inv->pricing_label }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono font-semibold text-white">
                                            Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            @if($inv->payment_status === 'paid')
                                                <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 border border-emerald-800">Lunas</span>
                                            @elseif($inv->is_overdue)
                                                <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-red-900 text-red-300 border border-red-800">Jatuh Tempo</span>
                                            @elseif($inv->payment_status === 'cancelled')
                                                <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">Batal</span>
                                            @else
                                                <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-900 text-amber-300 border border-amber-800">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-10 text-center border border-dashed border-slate-800 rounded-md">
                        <p class="text-xs text-slate-400">Belum ada catatan tagihan atlet.</p>
                        <a href="{{ route('coach.invoices.index') }}" class="inline-block mt-3 px-3 py-1.5 rounded-md bg-slate-800 text-slate-200 hover:text-white text-xs font-semibold transition">
                            Buat Tagihan Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Withdrawals (1 col on desktop) -->
        <div class="rounded-lg bg-slate-900 border border-slate-800 p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                    <div>
                        <h2 class="text-base font-semibold text-white">Penarikan Dana</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Status penarikan ke rekening bank.</p>
                    </div>
                    <a href="{{ route('coach.withdrawals.index') }}" class="text-xs text-neon hover:underline font-semibold">
                        Form Payout &rarr;
                    </a>
                </div>

                @if($recentWithdrawals->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($recentWithdrawals as $wd)
                            <div class="p-3 rounded-md bg-slate-950 border border-slate-800 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold font-mono text-white">
                                        Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-mono">
                                        {{ $wd->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                                <div>
                                    @if($wd->status === 'completed')
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 border border-emerald-800">Selesai</span>
                                    @elseif($wd->status === 'rejected')
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-red-900 text-red-300 border border-red-800">Ditolak</span>
                                    @else
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-900 text-amber-300 border border-amber-800">Diproses</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center border border-dashed border-slate-800 rounded-md">
                        <p class="text-xs text-slate-400">Belum ada riwayat penarikan dana.</p>
                    </div>
                @endif
            </div>

            <div class="mt-5 pt-4 border-t border-slate-800 text-center">
                <a href="{{ route('coach.withdrawals.index') }}" class="w-full block py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition">
                    Pengaturan Rekening & Payout
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
