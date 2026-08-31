@extends('layouts.coach')

@section('title', 'Penarikan Dana Coach')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8 font-sans">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-6">
        <div>
            <p class="text-xs text-slate-400 mb-1">Payout Gateway</p>
            <h1 class="text-2xl font-bold text-white">Penarikan Dana (Withdrawal)</h1>
            <p class="text-xs text-slate-300 mt-1">
                Cairkan saldo pendapatan bimbingan dan program lari ke rekening bank Anda.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('coach.finance.index') }}" class="px-3.5 py-2 rounded-md bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition">
                &larr; Dashboard Keuangan
            </a>
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

    <!-- Balance Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Saldo Tersedia -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800">
            <div class="text-xs text-slate-400">Saldo Siap Ditarik</div>
            <div class="text-xl sm:text-2xl font-bold text-white font-mono mt-1">
                Rp {{ number_format($availableBalance, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-2">Akumulasi bersih dari seluruh tagihan atlet yang sudah berstatus LUNAS.</p>
        </div>

        <!-- 2. Sedang Diproses -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800">
            <div class="text-xs text-slate-400">Sedang Diproses (Pending)</div>
            <div class="text-xl sm:text-2xl font-bold text-amber-300 font-mono mt-1">
                Rp {{ number_format($pendingAmount, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-2">Permohonan penarikan yang sedang dalam proses verifikasi & transfer admin.</p>
        </div>

        <!-- 3. Total Telah Dicairkan -->
        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800">
            <div class="text-xs text-slate-400">Total Telah Dicairkan</div>
            <div class="text-xl sm:text-2xl font-bold text-slate-200 font-mono mt-1">
                Rp {{ number_format($completedAmount ?? 0, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-2">Total dana yang telah sukses ditransfer ke rekening bank coach.</p>
        </div>
    </div>

    <!-- Main Content: Form Request & History Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Withdrawal Form (1 col) -->
        <div class="rounded-lg bg-slate-900 border border-slate-800 p-5 sm:p-6 h-fit">
            <div class="pb-3 mb-4 border-b border-slate-800">
                <h2 class="text-base font-semibold text-white">Ajukan Penarikan Dana</h2>
                <p class="text-xs text-slate-400 mt-0.5">Proses pencairan diproses maksimal 1-2 hari kerja.</p>
            </div>

            <form method="POST" action="{{ route('coach.withdrawals.request') }}" class="space-y-4">
                @csrf

                <!-- Nominal Penarikan -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">
                        Nominal Penarikan (Rp) <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="amount" min="50000" max="{{ $availableBalance }}" step="10000" 
                        value="{{ old('amount') }}" placeholder="Min. Rp 50.000" required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none font-mono font-semibold">
                    <span class="text-xs text-slate-400 mt-1 block">Maksimal: Rp {{ number_format($availableBalance, 0, ',', '.') }}</span>
                </div>

                <!-- Bank Name -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">
                        Nama Bank <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', auth()->user()->bank_name ?? 'BCA') }}" 
                        placeholder="Contoh: BCA / Mandiri / BNI / BRI" required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">
                        Nomor Rekening <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', auth()->user()->bank_account_number) }}" 
                        placeholder="Nomor rekening bank..." required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none font-mono">
                </div>

                <!-- Account Holder -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">
                        Nama Pemilik Rekening <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', auth()->user()->bank_account_holder ?? auth()->user()->name) }}" 
                        placeholder="Nama sesuai buku rekening..." required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-slate-600 outline-none">
                </div>

                <div class="pt-2">
                    <button type="submit" 
                        {{ $availableBalance < 50000 ? 'disabled' : '' }}
                        class="w-full py-2.5 px-4 rounded-md {{ $availableBalance >= 50000 ? 'bg-neon text-dark hover:brightness-110' : 'bg-slate-800 text-slate-500 cursor-not-allowed' }} text-xs font-bold transition">
                        Ajukan Penarikan Sekarang
                    </button>
                    @if($availableBalance < 50000)
                        <p class="text-xs text-slate-400 text-center mt-2">Saldo minimum untuk penarikan adalah Rp 50.000</p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Withdrawal History Table (2 cols) -->
        <div class="lg:col-span-2 rounded-lg bg-slate-900 border border-slate-800 p-5 sm:p-6">
            <div class="pb-3 mb-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-white">Riwayat Penarikan Dana</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan pencairan saldo yang pernah Anda ajukan.</p>
                </div>
            </div>

            @if($withdrawals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="text-[11px] text-slate-400 uppercase bg-slate-950 border-b border-slate-800 font-medium">
                                <th class="py-3 px-3">Tanggal</th>
                                <th class="py-3 px-3">Rekening Tujuan</th>
                                <th class="py-3 px-3 text-right">Nominal</th>
                                <th class="py-3 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 font-sans">
                            @foreach($withdrawals as $wd)
                                <tr class="hover:bg-slate-800 transition">
                                    <td class="py-3 px-3 font-mono text-slate-300">
                                        {{ $wd->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-semibold text-white">{{ $wd->bank_name ?? 'BCA' }}</div>
                                        <div class="text-[11px] font-mono text-slate-400">{{ $wd->account_number ?? '-' }} a/n {{ $wd->account_holder ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-semibold text-white">
                                        Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if($wd->status === 'completed')
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 border border-emerald-800">Selesai</span>
                                        @elseif($wd->status === 'rejected')
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-red-900 text-red-300 border border-red-800">Ditolak</span>
                                        @else
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-900 text-amber-300 border border-amber-800">Menunggu Transfer</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($withdrawals->hasPages())
                    <div class="p-4 border-t border-slate-800 mt-4">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            @else
                <div class="py-16 text-center">
                    <p class="text-xs text-slate-400">Belum ada riwayat penarikan dana.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
