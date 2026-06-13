@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('content')
<div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('programs.index') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Programs
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('marketplace.orders.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">My Orders</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">#{{ $order->order_number }}</span>
                </div>
            </li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/50 rounded-xl p-4 mb-6 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h4 class="font-bold text-emerald-400 text-sm">Pembayaran Berhasil!</h4>
                <p class="text-sm text-emerald-300/80 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Details (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Header -->
            <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
                <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-2xl font-black text-white italic">PROGRAM <span class="text-neon">ORDER</span></h2>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border
                                {{ $order->payment_status === 'paid' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                                   ($order->payment_status === 'failed' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 
                                   'bg-yellow-500/10 text-yellow-400 border-yellow-500/20') }}">
                                {{ $order->payment_status === 'paid' ? 'Paid' : 'Pending' }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-sm">
                            Purchased on <span class="text-white">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                         <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">Order Number</p>
                         <p class="text-white font-mono font-bold">{{ $order->order_number }}</p>
                    </div>
                </div>

                <!-- Items List -->
                <div class="p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4 1.253" /></svg>
                        Training Programs
                    </h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="flex gap-4 p-4 bg-slate-800/40 rounded-xl border border-slate-700 hover:border-slate-600 transition-colors">
                            <!-- Program Icon -->
                            <div class="w-20 h-20 bg-slate-800 rounded-lg overflow-hidden flex-shrink-0 border border-slate-700 flex items-center justify-center text-neon">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-white text-lg line-clamp-1">{{ $item->program_title }}</h4>
                                    @if($item->program && $item->program->coach)
                                        <p class="text-slate-400 text-sm">Coach: <span class="text-white">{{ $item->program->coach->name }}</span></p>
                                    @else
                                        <p class="text-slate-500 text-sm">Program no longer active</p>
                                    @endif
                                </div>
                                <div class="text-neon font-black italic text-lg">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="bg-slate-800/30 p-6 border-t border-slate-800">
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Subtotal</span>
                            <span class="text-white font-medium">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Tax</span>
                            <span class="text-white font-medium">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-xl border-t border-slate-800/50 pt-4">
                        <span class="font-bold text-white uppercase tracking-wider">Total Amount</span>
                        <span class="font-black text-neon italic text-2xl">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Notes -->
                @if($order->notes)
                    <div class="p-6 border-t border-slate-800">
                        <h3 class="text-base font-bold text-white mb-2">Catatan Tambahan</h3>
                        <p class="text-slate-400 text-sm bg-slate-800/20 p-4 rounded-xl border border-slate-850">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status & Actions (Right Column) -->
        <div class="lg:col-span-1">
            <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 overflow-hidden shadow-xl sticky top-24">
                <div class="p-6 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Transaction Status
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    @if($order->payment_status == 'pending')
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4 text-center">
                            <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-3 text-yellow-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="font-bold text-white mb-1">Waiting for Payment</h4>
                            <p class="text-sm text-slate-400">Metode: {{ ucfirst($order->payment_method) }}</p>
                        </div>

                        @if($order->payment_method === 'midtrans')
                             <button id="pay-now-btn" onclick="payProgram({{ $order->id }})" class="w-full block bg-neon text-slate-900 font-black text-center py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] transition-all">
                                PAY NOW
                             </button>
                        @endif

                    @elseif($order->payment_status == 'paid')
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-6 text-center">
                            <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4 text-green-400">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="text-xl font-bold text-white mb-2">Payment Completed</h4>
                            <p class="text-slate-450 text-sm mb-4">Program lari sudah aktif dan telah ditambahkan ke kalender Anda.</p>
                            
                            <a href="{{ route('runner.calendar') }}" class="w-full inline-flex items-center justify-center gap-2 bg-neon text-slate-900 font-black py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-white transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                LIHAT KALENDER
                            </a>
                        </div>
                    @else
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
                            <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-3 text-red-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <h4 class="font-bold text-white mb-1">Transaction Failed</h4>
                            <p class="text-sm text-slate-400">This transaction could not be processed successfully.</p>
                        </div>
                    @endif

                    <div class="border-t border-slate-800 pt-6">
                        <a href="{{ route('marketplace.program-orders.invoice', $order->id) }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 py-3 rounded-xl text-sm font-bold transition-all mb-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Print Invoice
                        </a>
                        <a href="{{ route('marketplace.orders.index') }}" class="w-full flex items-center justify-center gap-2 bg-slate-800/50 hover:bg-slate-800 text-slate-400 hover:text-slate-200 py-3 rounded-xl text-sm font-bold transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Kembali ke Daftar Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($order->payment_status == 'pending' && $order->payment_method === 'midtrans')
@push('scripts')
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    function payProgram(orderId) {
        const btn = document.getElementById('pay-now-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
        }
        
        fetch('{{ route("marketplace.checkout.program.pay", $order->id) }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.snap_token) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result){
                        window.location.href = "{{ route('marketplace.program-orders.show', $order->id) }}?payment=success";
                    },
                    onPending: function(result){
                        alert("Waiting for your payment!");
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Payment failed!");
                        window.location.reload();
                    },
                    onClose: function(){
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = 'PAY NOW';
                        }
                    }
                });
            } else {
                alert('Gagal mengambil token pembayaran.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'PAY NOW';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'PAY NOW';
            }
        });
    }
</script>
@endpush
@endif
