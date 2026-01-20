@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manajemen Transaksi')

@section('content')
<div x-data="{ showActionModal: false, actionUrl: '', actionLabel: '', notes: '', amountLabel: '', userLabel: '' }" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">MANAJEMEN TRANSAKSI</h1>
            <p class="text-slate-400 mt-1">Monitor deposit (topup) dan withdraw, lengkap dengan status dan riwayat.</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-800/50 border border-slate-700 rounded-2xl px-4 py-3">
                <div class="text-xs text-slate-400">Withdraw Pending</div>
                <div class="text-2xl font-black text-white">{{ number_format($counts['withdrawals_pending'] ?? 0) }}</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-700 rounded-2xl px-4 py-3">
                <div class="text-xs text-slate-400">Topup Pending</div>
                <div class="text-2xl font-black text-white">{{ number_format($counts['topups_pending'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl relative z-10">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl relative z-10">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="font-bold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="mb-6 flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.transactions.index', ['tab' => 'withdrawals']) }}" class="px-4 py-2 rounded-xl border {{ $tab === 'withdrawals' ? 'bg-blue-600 text-white border-blue-500' : 'bg-slate-900/40 text-slate-200 border-slate-700 hover:border-slate-500' }} transition">
                Withdraw
            </a>
            <a href="{{ route('admin.transactions.index', ['tab' => 'topups']) }}" class="px-4 py-2 rounded-xl border {{ $tab === 'topups' ? 'bg-blue-600 text-white border-blue-500' : 'bg-slate-900/40 text-slate-200 border-slate-700 hover:border-slate-500' }} transition">
                Deposit (Topup)
            </a>
            <a href="{{ route('admin.transactions.index', ['tab' => 'ledger']) }}" class="px-4 py-2 rounded-xl border {{ $tab === 'ledger' ? 'bg-blue-600 text-white border-blue-500' : 'bg-slate-900/40 text-slate-200 border-slate-700 hover:border-slate-500' }} transition">
                Ledger Wallet
            </a>
        </div>

        <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <select name="status" class="w-full sm:w-56 rounded-xl border-slate-700 bg-slate-800/50 text-white px-3 py-2.5 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Status</option>
                @if($tab === 'withdrawals')
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>rejected</option>
                @elseif($tab === 'topups')
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>pending</option>
                    <option value="success" {{ $status === 'success' ? 'selected' : '' }}>success</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>failed</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>expired</option>
                @else
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>pending</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>completed</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>failed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>cancelled</option>
                @endif
            </select>

            <input type="text" name="q" value="{{ $q }}" placeholder="Cari user / order_id / deskripsi" class="w-full sm:w-72 rounded-xl border-slate-700 bg-slate-800/50 text-white px-3 py-2.5 focus:border-blue-500 focus:ring-blue-500 placeholder-slate-500">
            <button class="inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
    </div>

    <div class="bg-slate-800/30 border border-slate-700/60 rounded-2xl overflow-hidden">
        @if($tab === 'topups')
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-200">
                    <thead class="bg-slate-900/50 text-slate-300">
                        <tr>
                            <th class="text-left px-5 py-4">Waktu</th>
                            <th class="text-left px-5 py-4">User</th>
                            <th class="text-left px-5 py-4">Amount</th>
                            <th class="text-left px-5 py-4">Order ID</th>
                            <th class="text-left px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @forelse($topups as $topup)
                            <tr class="hover:bg-slate-900/30 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ $topup->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-white">{{ $topup->user?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $topup->user?->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold">Rp {{ number_format((float) $topup->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-slate-300">{{ $topup->midtrans_order_id ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold border {{ $topup->status === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-300' : ($topup->status === 'pending' ? 'bg-yellow-500/10 border-yellow-500/30 text-yellow-300' : 'bg-red-500/10 border-red-500/30 text-red-300') }}">
                                        {{ $topup->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400">Tidak ada data topup.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5">{{ $topups?->links() }}</div>
        @elseif($tab === 'ledger')
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-200">
                    <thead class="bg-slate-900/50 text-slate-300">
                        <tr>
                            <th class="text-left px-5 py-4">Waktu</th>
                            <th class="text-left px-5 py-4">User</th>
                            <th class="text-left px-5 py-4">Type</th>
                            <th class="text-left px-5 py-4">Amount</th>
                            <th class="text-left px-5 py-4">Status</th>
                            <th class="text-left px-5 py-4">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @forelse($transactions as $txn)
                            <tr class="hover:bg-slate-900/30 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ $txn->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-white">{{ $txn->wallet?->user?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $txn->wallet?->user?->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold">{{ $txn->type }}</td>
                                <td class="px-5 py-4 font-semibold">Rp {{ number_format((float) $txn->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold border {{ $txn->status === 'completed' ? 'bg-green-500/10 border-green-500/30 text-green-300' : ($txn->status === 'pending' ? 'bg-yellow-500/10 border-yellow-500/30 text-yellow-300' : 'bg-red-500/10 border-red-500/30 text-red-300') }}">
                                        {{ $txn->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-300">{{ $txn->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada data transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5">{{ $transactions?->links() }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-200">
                    <thead class="bg-slate-900/50 text-slate-300">
                        <tr>
                            <th class="text-left px-5 py-4">Waktu</th>
                            <th class="text-left px-5 py-4">User</th>
                            <th class="text-left px-5 py-4">Amount</th>
                            <th class="text-left px-5 py-4">Bank</th>
                            <th class="text-left px-5 py-4">Status</th>
                            <th class="text-left px-5 py-4">Notes</th>
                            <th class="text-left px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @forelse($withdrawals as $wd)
                            <tr class="hover:bg-slate-900/30 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ $wd->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-white">{{ $wd->user?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $wd->user?->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold">Rp {{ number_format((float) $wd->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-slate-300">
                                    <div class="font-semibold">{{ $wd->bank_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $wd->bank_account_name }} • {{ $wd->bank_account_number }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold border {{ $wd->status === 'approved' ? 'bg-green-500/10 border-green-500/30 text-green-300' : ($wd->status === 'pending' ? 'bg-yellow-500/10 border-yellow-500/30 text-yellow-300' : 'bg-red-500/10 border-red-500/30 text-red-300') }}">
                                        {{ $wd->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-300">{{ $wd->notes ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    @if($wd->status === 'pending')
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                data-url="{{ route('admin.transactions.withdrawals.approve', $wd) }}"
                                                @click="actionUrl = $el.dataset.url; actionLabel = 'Approve'; notes = ''; amountLabel = 'Rp {{ number_format((float) $wd->amount, 0, ',', '.') }}'; userLabel = '{{ addslashes($wd->user?->name ?? '-') }}'; showActionModal = true"
                                                class="px-3 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold transition"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                type="button"
                                                data-url="{{ route('admin.transactions.withdrawals.reject', $wd) }}"
                                                @click="actionUrl = $el.dataset.url; actionLabel = 'Reject'; notes = ''; amountLabel = 'Rp {{ number_format((float) $wd->amount, 0, ',', '.') }}'; userLabel = '{{ addslashes($wd->user?->name ?? '-') }}'; showActionModal = true"
                                                class="px-3 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada data withdraw.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5">{{ $withdrawals?->links() }}</div>
        @endif
    </div>

    <div x-show="showActionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/70" @click="showActionModal = false"></div>
        <div class="relative w-full max-w-lg mx-4 bg-slate-900 border border-slate-700 rounded-2xl p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-400">Konfirmasi</div>
                    <div class="text-xl font-black text-white" x-text="actionLabel + ' Withdraw'"></div>
                    <div class="text-sm text-slate-300 mt-1">
                        <span x-text="userLabel"></span>
                        <span class="text-slate-500">•</span>
                        <span x-text="amountLabel"></span>
                    </div>
                </div>
                <button type="button" class="text-slate-400 hover:text-white" @click="showActionModal = false">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form :action="actionUrl" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Catatan (opsional)</label>
                    <textarea name="notes" x-model="notes" rows="3" class="w-full rounded-xl border-slate-700 bg-slate-800/50 text-white px-3 py-2.5 focus:border-blue-500 focus:ring-blue-500" placeholder="Misalnya: bukti transfer / alasan reject"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showActionModal = false" class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-200 hover:border-slate-500 transition">Batal</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition">Lanjut</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
