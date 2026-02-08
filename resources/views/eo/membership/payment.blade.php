@extends('layouts.pacerhub')

@section('title', 'Pembayaran Membership')

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
<div class="min-h-screen bg-dark text-white font-sans selection:bg-neon selection:text-dark pt-32 pb-20 px-4 sm:px-6 lg:px-8 relative overflow-hidden flex items-center justify-center">
    
    <!-- Background Accents -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-neon/10 rounded-full blur-[100px] -z-10"></div>

    <div class="max-w-xl w-full">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-2 h-6 bg-neon skew-x-[-12deg]"></div>
                <span class="text-neon font-mono text-sm tracking-widest uppercase">EO MEMBERSHIP</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter text-white uppercase mb-4">
                {{ $transaction->package->name }}
            </h1>
            <p class="text-slate-400 max-w-xl mx-auto">
                Selesaikan pembayaran untuk mengaktifkan fitur Event Organizer.
            </p>
        </div>

        <!-- Payment Card -->
        <div class="bg-card/50 backdrop-blur-sm rounded-3xl p-8 border border-slate-700 relative overflow-hidden group shadow-2xl">
            <div class="absolute top-0 right-0 w-32 h-32 bg-neon/5 rounded-full blur-2xl -mr-16 -mt-16 transition-all group-hover:bg-neon/10"></div>
            
            <div class="space-y-8 text-center">
                
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-2">Total Tagihan</span>
                    <div class="text-5xl font-black text-neon tracking-tighter drop-shadow-[0_0_15px_rgba(204,255,0,0.3)]">
                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                    </div>
                     <div class="mt-2 text-sm text-slate-400 font-mono">
                        Durasi: <span class="text-white">{{ $transaction->package->duration_days }} Hari</span>
                    </div>
                </div>

                <div class="bg-slate-900/50 rounded-xl p-4 border border-slate-700/50 text-left">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-neon">
                            <i class="fa-regular fa-file-lines"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 font-bold uppercase">Order ID</div>
                            <div class="text-white font-mono text-sm">MEMBERSHIP-{{ $transaction->id }}</div>
                        </div>
                    </div>
                     <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-neon">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 font-bold uppercase">Account</div>
                            <div class="text-white font-mono text-sm">{{ auth()->user()->name }}</div>
                        </div>
                    </div>
                </div>

                <button id="pay-button" class="w-full bg-neon text-dark font-black text-xl py-5 rounded-xl shadow-[0_0_20px_rgba(204,255,0,0.4)] hover:shadow-[0_0_30px_rgba(204,255,0,0.6)] hover:scale-[1.02] transition-all flex items-center justify-center gap-3 group relative overflow-hidden">
                    <span class="relative z-10">BAYAR SEKARANG</span>
                    <i class="fa-solid fa-arrow-right relative z-10 group-hover:translate-x-1 transition-transform"></i>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>

                <div class="flex items-center justify-center gap-2 text-xs text-slate-500">
                    <i class="fa-solid fa-lock text-green-500"></i>
                    <span>Pembayaran aman & terenkripsi oleh Midtrans</span>
                </div>

            </div>
        </div>
        
        <div class="text-center mt-8">
             <a href="{{ route('eo.dashboard') }}" class="text-slate-500 hover:text-white text-sm transition font-bold uppercase tracking-wider">
                Batalkan & Kembali
            </a>
        </div>

    </div>
</div>

@php
    $snapUrl = config('midtrans.is_production') 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp

<script src="{{ $snapUrl }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function(){
        snap.pay('{{ $transaction->snap_token }}', {
            onSuccess: function(result){
                window.location.href = "{{ route('eo.dashboard') }}?payment=success";
            },
            onPending: function(result){
                alert("Menunggu pembayaran Anda!");
            },
            onError: function(result){
                alert("Pembayaran gagal!");
            },
            onClose: function(){
                // Do nothing
            }
        });
    };
</script>
@endsection
