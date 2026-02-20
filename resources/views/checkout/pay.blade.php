@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Pembayaran Program')

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('marketplace.orders.show', $order->id) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Kembali ke detail order
            </a>
        </div>

        <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 shadow-2xl p-6 md:p-8">
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <div class="text-xs tracking-[0.2em] uppercase text-neon/80 mb-1">Checkout Program</div>
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                        Pembayaran via Midtrans
                    </h1>
                    <p class="text-slate-400 text-sm mt-1">
                        Order #{{ $order->order_number }} • Total tagihan
                        <span class="text-neon font-bold">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <div class="mb-6 border border-slate-800 rounded-xl bg-slate-900/60 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-1">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="text-sm text-slate-300">
                        Klik tombol di bawah untuk membuka halaman pembayaran Midtrans. Setelah pembayaran berhasil, order akan otomatis terupdate dan program akan masuk ke kalender Anda.
                    </div>
                </div>
            </div>

            <button id="pay-button" class="w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] transition-all flex items-center justify-center gap-2 group">
                <span>BAYAR SEKARANG</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </button>

            <p class="text-xs text-slate-500 mt-6 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                Pembayaran aman dan terenkripsi oleh Midtrans
            </p>
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
    document.getElementById('pay-button').onclick = function () {
        snap.pay(@json($snapToken), {
            onSuccess: function (result) {
                window.location.href = "{{ route('marketplace.orders.show', $order->id) }}?payment=success";
            },
            onPending: function (result) {
                alert("Pembayaran kamu masih pending. Silakan selesaikan pembayaran di Midtrans.");
            },
            onError: function (result) {
                alert("Pembayaran gagal. Silakan coba lagi.");
            },
            onClose: function () {
                console.log('Midtrans popup closed');
            }
        });
    };
</script>
@endsection

