@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('content')
<div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Marketplace
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
                    <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">#{{ $order->invoice_number }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Details (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Header -->
            <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
                <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-2xl font-black text-white italic">ORDER <span class="text-neon">DETAILS</span></h2>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border
                                {{ $order->status === 'completed' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                                  ($order->status === 'shipped' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 
                                  ($order->status === 'paid' ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' :
                                   'bg-slate-700 text-slate-300 border-slate-600')) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-sm">
                            Placed on <span class="text-white">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                         <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">Invoice Number</p>
                         <p class="text-white font-mono font-bold">{{ $order->invoice_number }}</p>
                    </div>
                </div>

                <!-- Items List -->
                <div class="p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        Items Ordered
                    </h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="flex gap-4 p-4 bg-slate-800/40 rounded-xl border border-slate-700 hover:border-slate-600 transition-colors">
                            <!-- Image -->
                            <div class="w-20 h-20 bg-slate-800 rounded-lg overflow-hidden flex-shrink-0 border border-slate-700">
                                @if($item->product && $item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover" alt="{{ $item->product_title_snapshot }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-600">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-white text-lg line-clamp-1">{{ $item->product_title_snapshot }}</h4>
                                    <p class="text-slate-400 text-sm">Quantity: <span class="text-white font-mono">{{ $item->quantity }}</span></p>
                                </div>
                                <div class="text-neon font-black italic text-lg">
                                    Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="bg-slate-800/30 p-6 border-t border-slate-800">
                    <div class="flex justify-between items-center text-xl">
                        <span class="font-bold text-white uppercase tracking-wider">Total Amount</span>
                        <span class="font-black text-neon italic text-2xl">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($order->shipping_name || $order->shipping_address)
                    <div class="bg-slate-900 p-6 border-t border-slate-800">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h3.586a1 1 0 01.707.293l2.414 2.414a1 1 0 001.414 0l2.414-2.414A1 1 0 0115.414 19H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z" /></svg>
                            Shipping Details
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Nama Penerima</span>
                                <span class="text-white font-medium">{{ $order->shipping_name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">No. HP</span>
                                <span class="text-white font-medium">{{ $order->shipping_phone ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Alamat</span>
                                <p class="text-white mt-1 text-sm">
                                    {{ $order->shipping_address ?? '-' }}<br>
                                    {{ $order->shipping_city ?? '' }}{{ $order->shipping_postal_code ? ', '.$order->shipping_postal_code : '' }}
                                </p>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Kurir</span>
                                <span class="text-white font-medium">
                                    {{ $order->shipping_courier ? strtoupper($order->shipping_courier) : '-' }}
                                    @if($order->shipping_cost !== null)
                                        • Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                            @if($order->shipping_note)
                                <div>
                                    <span class="text-slate-400 block">Catatan</span>
                                    <p class="text-white mt-1 text-sm">{{ $order->shipping_note }}</p>
                                </div>
                            @endif
                        </div>
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
                        Status & Actions
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    @if($order->status == 'pending')
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4 text-center">
                            <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-3 text-yellow-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="font-bold text-white mb-1">Waiting for Payment</h4>
                            <p class="text-sm text-slate-400">Please complete the payment to process your order.</p>
                        </div>

                        @if(Auth::id() == $order->buyer_id)
                             <a href="{{ route('marketplace.checkout.pay', $order->id) }}" class="w-full block bg-neon text-slate-900 font-black text-center py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] transition-all">
                                PAY NOW
                            </a>
                        @endif

                    @elseif($order->status == 'paid')
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 text-center">
                            <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-3 text-green-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="font-bold text-white mb-1">Payment Confirmed</h4>
                            <p class="text-sm text-slate-400">Waiting for seller to ship the items.</p>
                        </div>
                        
                        @if(Auth::id() == $order->seller_id)
                            <div class="mt-6 border-t border-slate-800 pt-6">
                                <h4 class="font-bold text-white mb-4">Update Shipping Info</h4>
                                <form action="{{ route('marketplace.orders.shipped', $order->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-slate-400 mb-2">Tracking Number / Resi</label>
                                        <input type="text" name="tracking_number" required 
                                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all" 
                                            placeholder="e.g. JNE123456 or Link">
                                    </div>
                                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-500 transition-colors shadow-lg shadow-blue-600/20">
                                        Mark as Shipped
                                    </button>
                                </form>
                            </div>
                        @endif

                    @elseif($order->status == 'shipped')
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 text-center">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                            </div>
                            <h4 class="font-bold text-white mb-1">Order Shipped</h4>
                            <p class="text-sm text-slate-400 mb-3">Your order is on the way.</p>
                            <div class="bg-slate-900 rounded-lg p-3 border border-slate-700 text-left">
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Tracking Info</p>
                                <p class="text-white font-mono break-all">{{ $order->shipping_tracking_number }}</p>
                            </div>
                        </div>

                        @if(Auth::id() == $order->buyer_id)
                            <div class="mt-6 border-t border-slate-800 pt-6">
                                <h4 class="font-bold text-white mb-2">Received the item?</h4>
                                <p class="text-sm text-slate-400 mb-4">Confirming receipt will release funds to the seller.</p>
                                <form action="{{ route('marketplace.orders.completed', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you have received the item? Funds will be released to the seller.')">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-xl hover:bg-green-500 transition-colors shadow-lg shadow-green-600/20 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Confirm Received
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="mt-4 text-center p-4 bg-slate-800/30 rounded-xl border border-slate-800">
                                <p class="text-sm text-slate-400 italic">Waiting for buyer confirmation to release funds.</p>
                            </div>
                        @endif

                    @elseif($order->status == 'completed')
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-6 text-center">
                            <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4 text-green-400">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="text-xl font-bold text-white mb-2">Transaction Completed</h4>
                            <p class="text-slate-400">This order has been fulfilled and completed successfully.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
