@extends('layouts.pacerhub')

@section('title', 'Pembayaran - ' . $event->name)

@push('styles')
    <script>
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    DEFAULT: '#ccff00',
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    dark: '#0f172a',
                    card: '#1e293b'
                }
            }
        }
    </script>
@endpush

@section('content')
<div class="min-h-screen bg-dark text-white font-sans selection:bg-neon selection:text-dark pt-32 pb-20 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    
    <!-- Background Accents -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-neon/10 rounded-full blur-[100px] -z-10"></div>

    <div class="max-w-3xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-2 h-6 bg-neon skew-x-[-12deg]"></div>
                <span class="text-neon font-mono text-sm tracking-widest uppercase">PAYMENT GATEWAY</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter text-white uppercase mb-4">
                {{ $event->name }}
            </h1>
            <p class="text-slate-400 max-w-xl mx-auto">
                Selesaikan pembayaran Anda untuk mengamankan slot event.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Left Column: Instructions & Timer -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Timer Card -->
                <div class="bg-card/50 backdrop-blur-sm rounded-3xl p-6 border border-slate-700 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-neon/5 rounded-full blur-2xl -mr-16 -mt-16 transition-all group-hover:bg-neon/10"></div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fa-regular fa-clock"></i> Batas Waktu
                        </span>
                        <div class="w-2 h-2 rounded-full bg-neon animate-pulse"></div>
                    </div>
                    
                    <div id="timer" class="text-4xl md:text-5xl font-mono font-bold text-white tracking-tight" data-expires="{{ $transaction->created_at->addHours(24)->toISOString() }}">
                        --:--:--
                    </div>
                    <div class="mt-2 text-xs text-slate-500">
                        Segera lakukan pembayaran sebelum waktu habis.
                    </div>
                </div>

                <!-- Payment Details Card -->
                <div class="bg-card/50 backdrop-blur-sm rounded-3xl p-6 border border-slate-700">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-1 h-4 bg-neon rounded-full"></span>
                            Detail Pembayaran
                        </div>
                        <div id="payment-status-badge" class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-500 text-xs font-black uppercase tracking-wider border border-yellow-500/20 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                            PENDING
                        </div>
                    </h3>

                    <div class="space-y-4">
                        <!-- Total Amount -->
                        <div class="bg-slate-900/50 rounded-2xl p-5 border border-slate-700/50">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Total Transfer</span>
                            <div class="flex items-end justify-between">
                                <div class="text-3xl font-black text-neon tracking-tight">
                                    Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}
                                </div>
                                <button onclick="copyToClipboard('{{ $transaction->final_amount }}', 'Nominal')" class="text-slate-400 hover:text-white transition p-2">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-xs text-yellow-500 bg-yellow-500/10 p-2 rounded-lg">
                                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                <span>PENTING: Transfer tepat hingga 3 digit terakhir agar terverifikasi otomatis!</span>
                            </div>
                        </div>

                        <!-- Bank Accounts -->
                        <div class="space-y-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block">Rekening Tujuan</span>
                            @foreach($bankAccounts as $bank)
                            <div class="bg-slate-800/50 rounded-xl p-4 flex items-center justify-between border border-slate-700 hover:border-neon/50 transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center text-slate-900 font-black text-xs shadow-lg transform group-hover:scale-110 transition-transform">
                                        {{ strtoupper($bank['bank_type']) }}
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-400 font-bold uppercase">{{ $bank['name'] }}</div>
                                        <div class="text-lg font-bold text-white tracking-wide font-mono">{{ $bank['account_number'] }}</div>
                                    </div>
                                </div>
                                <button onclick="copyToClipboard('{{ $bank['account_number'] }}', 'No Rekening')" class="w-10 h-10 rounded-full bg-slate-700 text-neon hover:bg-neon hover:text-dark transition flex items-center justify-center">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Instructions -->
            <div class="md:col-span-1">
                <div class="bg-card/50 backdrop-blur-sm rounded-3xl p-6 border border-slate-700 h-full">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6 flex items-center gap-2">
                        <span class="w-1 h-4 bg-neon rounded-full"></span>
                        Panduan
                    </h3>
                    
                    <div class="relative pl-4 border-l-2 border-slate-800 space-y-6">
                        @if(!empty($instructions))
                            <div class="text-sm text-slate-400 leading-relaxed prose prose-invert prose-sm">
                                {!! nl2br(e($instructions)) !!}
                            </div>
                        @else
                            <div class="space-y-6">
                                <div class="relative">
                                    <span class="absolute -left-[25px] top-0 w-4 h-4 rounded-full bg-slate-800 border-2 border-slate-600"></span>
                                    <h4 class="text-white font-bold text-sm mb-1">Mobile Banking / ATM</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Buka aplikasi bank atau ATM Anda dan pilih menu Transfer.
                                    </p>
                                </div>
                                <div class="relative">
                                    <span class="absolute -left-[25px] top-0 w-4 h-4 rounded-full bg-slate-800 border-2 border-slate-600"></span>
                                    <h4 class="text-white font-bold text-sm mb-1">Input Rekening</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Masukkan salah satu nomor rekening tujuan yang tertera.
                                    </p>
                                </div>
                                <div class="relative">
                                    <span class="absolute -left-[25px] top-0 w-4 h-4 rounded-full bg-slate-800 border-2 border-neon shadow-[0_0_10px_rgba(204,255,0,0.3)]"></span>
                                    <h4 class="text-neon font-bold text-sm mb-1">Input Nominal</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Masukkan nominal <strong class="text-white">PERSIS</strong> sama dengan total tagihan (termasuk 3 digit terakhir).
                                    </p>
                                </div>
                                <div class="relative">
                                    <span class="absolute -left-[25px] top-0 w-4 h-4 rounded-full bg-slate-800 border-2 border-slate-600"></span>
                                    <h4 class="text-white font-bold text-sm mb-1">Selesai</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Sistem akan memverifikasi pembayaran Anda secara otomatis dalam 5-10 menit.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-800">
                        <a href="{{ route('events.show', $event->slug) }}" class="flex items-center justify-center gap-2 text-slate-500 hover:text-white text-sm transition font-bold uppercase tracking-wider group">
                            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                            Kembali ke Event
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Payment Status Polling
    (function() {
        const transactionId = "{{ $transaction->id }}";
        const statusUrl = "{{ route('api.events.payments.status', ['slug' => $event->slug, 'transaction' => $transaction->id]) }}";
        const successUrl = "{{ route('events.show', $event->slug) }}?success=true";
        const badgeEl = document.getElementById('payment-status-badge');
        let pollInterval;

        function checkStatus() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    const status = data.transaction ? data.transaction.payment_status : 'pending';
                    
                    if (status === 'paid') {
                        if (badgeEl) {
                            badgeEl.className = 'px-3 py-1 rounded-full bg-neon/10 text-neon text-xs font-black uppercase tracking-wider border border-neon/20 flex items-center gap-2';
                            badgeEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-neon"></span> PAID';
                        }
                        // Redirect to success page
                        window.location.href = successUrl;
                    } else if (['failed', 'expired', 'cancelled'].includes(status)) {
                        if (badgeEl) {
                            badgeEl.className = 'px-3 py-1 rounded-full bg-red-500/10 text-red-500 text-xs font-black uppercase tracking-wider border border-red-500/20 flex items-center gap-2';
                            badgeEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500"></span> ' + status.toUpperCase();
                        }
                        clearInterval(pollInterval);
                    }
                })
                .catch(e => console.error('Status check failed', e));
        }

        // Poll every 5 seconds
        pollInterval = setInterval(checkStatus, 5000);
        // Check immediately
        checkStatus();
    })();

    // Copy Clipboard
    function copyToClipboard(text, label) {
        navigator.clipboard.writeText(text).then(() => {
            // Optional: You could use a toast notification here
            alert(label + ' berhasil disalin!');
        });
    }

    // Timer Logic
    const timerEl = document.getElementById('timer');
    if (timerEl) {
        const expiresAt = new Date(timerEl.dataset.expires).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const distance = expiresAt - now;

            if (distance < 0) {
                timerEl.innerHTML = "EXPIRED";
                timerEl.classList.add('text-red-500');
                timerEl.classList.remove('text-white');
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerEl.innerHTML = 
                String(hours).padStart(2, '0') + ":" + 
                String(minutes).padStart(2, '0') + ":" + 
                String(seconds).padStart(2, '0');
        }

        setInterval(updateTimer, 1000);
        updateTimer();
    }
</script>
@endsection
