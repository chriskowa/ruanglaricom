@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Wallet')

@php
    $snapUrl = config('midtrans.is_production')
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $snapCssUrl = config('midtrans.is_production')
        ? 'https://app.midtrans.com/snap/snap.css'
        : 'https://app.sandbox.midtrans.com/snap/snap.css';
    $snapClientKey = config('midtrans.is_production')
        ? config('midtrans.client_key')
        : config('midtrans.client_key_sandbox');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ $snapCssUrl }}" />
@endpush

@section('content')
<div class="min-h-screen text-slate-200 pt-24 pb-16">
    <div class="container mx-auto px-4 md:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-white">Wallet</h1>
            <p class="text-slate-400 text-sm mt-1">Kelola saldo, riwayat transaksi, dan top-up dengan aman</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="glass rounded-2xl p-6 md:p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Saldo Wallet</p>
                            <p class="text-4xl md:text-5xl font-black text-white mt-1">Rp {{ number_format($wallet->balance ?? 0, 0, ',', '.') }}</p>
                            <p class="text-slate-500 text-xs mt-2">Saldo tersedia untuk pembayaran</p>
                        </div>
                        <div class="shrink-0 w-16 h-16 rounded-2xl bg-neon/10 border border-neon/30 flex items-center justify-center">
                            <svg class="w-8 h-8 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M5 7h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" /></svg>
                        </div>
                    </div>
                </div>

                <div class="glass rounded-2xl p-6 md:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">Top-up Wallet</h2>
                        <div class="flex gap-2">
                            <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:bg-slate-700 transition quick-topup" data-amount="50000">Rp 50.000</button>
                            <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:bg-slate-700 transition quick-topup" data-amount="100000">Rp 100.000</button>
                            <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:bg-slate-700 transition quick-topup" data-amount="200000">Rp 200.000</button>
                            <button type="button" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:bg-slate-700 transition quick-topup" data-amount="500000">Rp 500.000</button>
                        </div>
                    </div>

                    <form id="topup-form" action="{{ route('wallet.topup') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nominal Top-up</label>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300">Rp</span>
                                <input type="number" name="amount" id="topup-amount" min="10000" max="10000000" required
                                       class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition"
                                       placeholder="Masukkan nominal (min Rp 10.000)">
                            </div>
                            <p class="text-[10px] text-slate-500 mt-2">Minimum: Rp 10.000, Maximum: Rp 10.000.000</p>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-lime-400 transition shadow-lg shadow-neon/20" id="topup-submit">
                            Top-up Sekarang
                        </button>
                    </form>
                </div>

                <div class="glass rounded-2xl p-6 md:p-8" id="withdraw-form">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">Withdraw</h2>
                        <span class="text-xs text-slate-500">Saldo akan dikunci saat permintaan dibuat</span>
                    </div>
                    <form id="withdraw-form-el" action="{{ route('wallet.withdraw') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nominal Withdraw</label>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300">Rp</span>
                                    <input type="number" name="amount" id="withdraw-amount" min="50000" required
                                           class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition"
                                           placeholder="Minimal Rp 50.000">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase">Info Bank</label>
                                <div class="text-sm text-slate-400">
                                    <p>{{ auth()->user()->bank_name ?: 'Bank belum diisi' }}</p>
                                    <p>{{ auth()->user()->bank_account_name ?: '-' }} • {{ auth()->user()->bank_account_number ?: '-' }}</p>
                                </div>
                                <a href="{{ route('profile.show') }}" class="text-[10px] text-neon">Ubah data bank di Profile</a>
                            </div>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-700 transition" id="withdraw-submit">
                            Ajukan Withdraw
                        </button>
                    </form>
                </div>

                <div class="glass rounded-2xl p-6 md:p-8">
                    <h2 class="text-xl font-bold text-white mb-4">Riwayat Transaksi</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                    <th class="py-3 px-2">Tanggal</th>
                                    <th class="py-3 px-2">Jenis</th>
                                    <th class="py-3 px-2">Deskripsi</th>
                                    <th class="py-3 px-2">Jumlah</th>
                                    <th class="py-3 px-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @forelse($transactions as $transaction)
                                    <tr class="border-b border-slate-800">
                                        <td class="py-3 px-2 text-slate-300">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                        <td class="py-3 px-2">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold {{ $transaction->type == 'deposit' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30' }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-slate-300">{{ $transaction->description }}</td>
                                        <td class="py-3 px-2 {{ $transaction->type == 'deposit' ? 'text-green-300' : 'text-red-300' }}">
                                            {{ $transaction->type == 'deposit' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-2">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold {{ $transaction->status == 'completed' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : ($transaction->status == 'pending' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-500">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $transactions->links() }}</div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass rounded-2xl p-6 md:p-8">
                    <h2 class="text-xl font-bold text-white mb-4">Riwayat Top-up</h2>
                    <div class="space-y-3">
                        @forelse($topups as $topup)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-700">
                                <div>
                                    <p class="text-white font-bold">Rp {{ number_format($topup->amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500">{{ $topup->created_at->format('d M Y H:i') }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-[10px] font-bold {{ $topup->status == 'success' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : ($topup->status == 'pending' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30') }}">
                                    {{ ucfirst($topup->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm">Belum ada top-up</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $topups->links() }}</div>
                </div>

                <div class="glass rounded-2xl p-6 md:p-8">
                    <h2 class="text-xl font-bold text-white mb-4">Riwayat Withdraw</h2>
                    <div class="space-y-3">
                        @forelse($withdrawals as $wd)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-700">
                                <div>
                                    <p class="text-white font-bold">Rp {{ number_format($wd->amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500">{{ $wd->created_at->format('d M Y H:i') }}</p>
                                    <p class="text-xs text-slate-500">{{ $wd->bank_name }} • {{ $wd->bank_account_name }} • {{ $wd->bank_account_number }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-[10px] font-bold {{ $wd->status == 'approved' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : ($wd->status == 'pending' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30') }}">
                                    {{ ucfirst($wd->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm">Belum ada withdraw</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $withdrawals->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="snap-container"></div>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $snapClientKey }}"></script>
<script>
    document.querySelectorAll('.quick-topup').forEach(btn => {
        btn.addEventListener('click', () => {
            const amount = btn.getAttribute('data-amount');
            const input = document.getElementById('topup-amount');
            input.value = amount;
            input.focus();
        });
    });

    const form = document.getElementById('topup-form');
    const submitBtn = document.getElementById('topup-submit');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const amount = document.getElementById('topup-amount').value;
        if (!amount || amount < 10000) return;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses...';
        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: new URLSearchParams(new FormData(form))
            });
            const data = await resp.json();
            if (!resp.ok) {
                const errors = data && data.errors ? Object.values(data.errors).flat().join('\\n') : (data && data.message) || 'Terjadi kesalahan';
                alert(errors);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Top-up Sekarang';
                return;
            }

            if (data.testing_mode) {
                alert(data.message || 'Top-up berhasil! (Testing Mode)');
                window.location.reload();
                return;
            }

            if (typeof snap === 'undefined') {
                alert('Midtrans Snap tidak tersedia.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Top-up Sekarang';
                return;
            }

            if (data.success && data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: function () { window.location.href = '{{ route("wallet.index") }}?success=1'; },
                    onPending: function () { window.location.href = '{{ route("wallet.index") }}?pending=1'; },
                    onError: function () {
                        alert('Top-up gagal. Silakan coba lagi.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Top-up Sekarang';
                    },
                    onClose: function () {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Top-up Sekarang';
                    }
                });
            } else {
                alert((data && data.message) || 'Gagal membuat transaksi top-up.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Top-up Sekarang';
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Top-up Sekarang';
        }
    });

    @if(request('success'))
        alert('Top-up berhasil!');
    @endif

    const withdrawForm = document.getElementById('withdraw-form-el');
    const withdrawSubmit = document.getElementById('withdraw-submit');
    withdrawForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const amount = document.getElementById('withdraw-amount').value;
        if (!amount || amount < 50000) return;
        withdrawSubmit.disabled = true;
        withdrawSubmit.textContent = 'Memproses...';
        try {
            const resp = await fetch(withdrawForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': withdrawForm.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: new URLSearchParams(new FormData(withdrawForm))
            });
            const data = await resp.json();
            if (!resp.ok) {
                const errors = data && data.errors ? Object.values(data.errors).flat().join('\\n') : (data && data.message) || 'Terjadi kesalahan';
                alert(errors);
                withdrawSubmit.disabled = false;
                withdrawSubmit.textContent = 'Ajukan Withdraw';
                return;
            }
            alert(data.message || 'Withdraw berhasil dibuat');
            window.location.reload();
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
            withdrawSubmit.disabled = false;
            withdrawSubmit.textContent = 'Ajukan Withdraw';
        }
    });
</script>
@endpush
