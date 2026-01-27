@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-slate-800 rounded-3xl p-8 shadow-2xl border border-slate-700">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-white uppercase tracking-wider mb-2">Instruksi Pembayaran</h2>
                <p class="text-slate-400">Silakan selesaikan pembayaran Anda sebelum batas waktu berakhir.</p>
            </div>

            <!-- Timer -->
            <div class="bg-slate-900/50 rounded-2xl p-6 mb-8 text-center border border-slate-700">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Batas Waktu</span>
                <div id="timer" class="text-3xl font-mono font-bold text-neon" data-expires="{{ $transaction->created_at->addHours(24)->toISOString() }}">
                    --:--:--
                </div>
            </div>

            <!-- Total Amount -->
            <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl p-6 mb-8 text-center border border-slate-600 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <svg class="w-16 h-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-xs font-bold text-slate-300 uppercase tracking-widest block mb-2">Total Transfer Wajib (Hingga 3 digit terakhir)</span>
                <div class="text-4xl font-black text-white tracking-tight flex items-center justify-center gap-2">
                    Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}
                    <button onclick="navigator.clipboard.writeText('{{ $transaction->final_amount }}'); alert('Nominal disalin!')" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                </div>
                <div class="mt-2 text-xs text-yellow-400 font-bold bg-yellow-400/10 inline-block px-3 py-1 rounded-full">
                    PENTING: Transfer tepat hingga 3 digit terakhir agar terverifikasi otomatis!
                </div>
            </div>

            <!-- Bank Accounts -->
            <div class="space-y-4 mb-8">
                <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Rekening Tujuan</h3>
                @foreach($bankAccounts as $bank)
                <div class="bg-slate-700/30 rounded-xl p-4 flex items-center justify-between border border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center text-slate-900 font-black text-xs">
                            {{ strtoupper($bank['bank_type']) }}
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">{{ $bank['name'] }}</div>
                            <div class="text-lg font-bold text-white tracking-wide font-mono">{{ $bank['account_number'] }}</div>
                        </div>
                    </div>
                    <button onclick="navigator.clipboard.writeText('{{ $bank['account_number'] }}'); alert('No Rekening disalin!')" class="text-neon hover:text-white transition font-bold text-sm">
                        SALIN
                    </button>
                </div>
                @endforeach
            </div>

            <!-- Steps -->
            <div class="border-t border-slate-700 pt-8">
                <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Cara Pembayaran</h3>
                
                @if(!empty($instructions))
                    <div class="prose prose-sm prose-invert text-slate-400 mb-4">
                        {!! nl2br(e($instructions)) !!}
                    </div>
                @endif

                <ol class="list-decimal list-inside space-y-2 text-sm text-slate-400">
                    <li>Buka aplikasi Mobile Banking atau ATM Anda.</li>
                    <li>Pilih menu Transfer.</li>
                    <li>Masukkan nomor rekening tujuan di atas.</li>
                    <li>Masukkan nominal transfer <strong>TEPAT</strong> sebesar <strong class="text-white">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</strong> (Jangan dibulatkan).</li>
                    <li>Selesaikan transaksi.</li>
                    <li>Sistem akan memverifikasi pembayaran Anda secara otomatis dalam 5-10 menit.</li>
                </ol>
            </div>
            
            <div class="mt-8 text-center">
                <a href="{{ route('events.show', $event->slug) }}" class="text-slate-500 hover:text-white text-sm transition">Kembali ke Halaman Event</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple Timer
    const expiresAt = new Date(document.getElementById('timer').dataset.expires).getTime();
    const timerEl = document.getElementById('timer');

    function updateTimer() {
        const now = new Date().getTime();
        const distance = expiresAt - now;

        if (distance < 0) {
            timerEl.innerHTML = "EXPIRED";
            timerEl.classList.add('text-red-500');
            timerEl.classList.remove('text-neon');
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        timerEl.innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
    }

    setInterval(updateTimer, 1000);
    updateTimer();
</script>
@endsection
