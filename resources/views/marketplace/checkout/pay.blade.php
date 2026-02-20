@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Complete Payment')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans flex items-center justify-center">
    
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8 text-center relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-neon via-cyan-400 to-purple-500"></div>
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
            <h2 class="text-2xl font-black text-white italic tracking-tighter mb-2">COMPLETE PAYMENT</h2>
            <p class="text-slate-400 text-sm mb-8 font-mono">Invoice: {{ $order->invoice_number }}</p>
            
            <div class="bg-slate-950 rounded-xl p-6 mb-8 border border-slate-800">
                <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Total Amount</div>
                <div class="text-4xl font-black text-neon italic tracking-tighter">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </div>
            </div>

            <button id="pay-button" class="w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] transition-all flex items-center justify-center gap-2 group">
                <span>PAY NOW</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </button>
            
            <p class="text-xs text-slate-500 mt-6 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                Secured by Midtrans
            </p>
        </div>
    </div>
</div>

<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function(){
        snap.pay('{{ $order->snap_token }}', {
            onSuccess: function(result){
                window.location.href = "{{ route('marketplace.orders.index') }}?payment=success";
            },
            onPending: function(result){
                alert("Waiting for your payment!");
            },
            onError: function(result){
                alert("Payment failed!");
            },
            onClose: function(){
                // Do nothing
            }
        });
    };
</script>
@endsection
