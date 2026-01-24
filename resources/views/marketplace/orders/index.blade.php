@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('content')
<div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black italic text-white uppercase tracking-wider">
                My <span class="text-neon">Orders</span>
            </h1>
            <p class="text-slate-400 mt-1">Track your purchases and manage sales</p>
        </div>
        <div class="flex items-center gap-3">
             <a href="{{ route('marketplace.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg text-sm font-bold transition-all">
                Browse Market
            </a>
            @if(Auth::user()->is_seller)
                 <a href="{{ route('marketplace.seller.products.index') }}" class="px-4 py-2 bg-slate-800 text-neon hover:bg-slate-700 rounded-lg text-sm font-bold transition-all">
                    Seller Dashboard
                </a>
            @endif
        </div>
    </div>

    <!-- Main Container -->
    <div x-data="{ tab: 'purchases' }" class="space-y-6">
        
        <!-- Tab Navigation -->
        <div class="bg-slate-900/50 backdrop-blur-md rounded-xl p-1 border border-slate-800 inline-flex">
            <button @click="tab = 'purchases'" 
                class="px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300"
                :class="tab === 'purchases' ? 'bg-neon text-slate-900 shadow-lg shadow-neon/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Purchases
            </button>
            <button @click="tab = 'sales'" 
                class="px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300"
                :class="tab === 'sales' ? 'bg-neon text-slate-900 shadow-lg shadow-neon/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Sales
            </button>
        </div>

        <!-- Purchases List -->
        <div x-show="tab === 'purchases'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
             
             @forelse($purchases as $order)
                <div class="bg-slate-900/80 backdrop-blur-sm rounded-2xl border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Product Image (First Item) -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0 bg-slate-800 rounded-xl overflow-hidden border border-slate-700 relative">
                             @php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             @endphp
                             @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Product">
                             @else
                                <div class="w-full h-full flex items-center justify-center text-slate-600">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                             @endif
                             <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        </div>

                        <!-- Order Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                                            {{ match($order->status) {
                                                'paid' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                                'shipped' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                                                'completed' => 'bg-slate-700 text-slate-300 border border-slate-600',
                                                'cancelled' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                                                default => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20'
                                            } }}">
                                            {{ $order->status }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-mono">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-slate-500">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-white mb-1 truncate">{{ $firstItem->product_title_snapshot }}</h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-slate-400">+ {{ $order->items->count() - 1 }} other items</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 flex items-end justify-between">
                                <div>
                                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Amount</div>
                                    <div class="text-xl font-black text-neon font-mono">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    @if($order->status == 'pending')
                                        <a href="{{ route('marketplace.checkout.pay', $order->id) }}" class="px-5 py-2 bg-neon text-slate-900 text-sm font-bold rounded-lg hover:bg-neon/90 hover:shadow-lg hover:shadow-neon/20 transition-all flex items-center gap-2">
                                            <span>Pay Now</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('marketplace.orders.show', $order->id) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-700 hover:text-white transition-all">
                                        Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty State -->
                <div class="text-center py-20 bg-slate-900/30 rounded-3xl border border-slate-800/50 border-dashed">
                    <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No purchases yet</h3>
                    <p class="text-slate-400 max-w-sm mx-auto mb-8">Ready to gear up? Explore the marketplace for the best running equipment.</p>
                    <a href="{{ route('marketplace.index') }}" class="px-8 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform inline-flex items-center gap-2">
                        Start Shopping
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
             @endforelse
        </div>

        <!-- Sales List -->
        <div x-show="tab === 'sales'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4" style="display: none;">
             
             @forelse($sales as $order)
                <div class="bg-slate-900/80 backdrop-blur-sm rounded-2xl border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                         <!-- Product Image -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0 bg-slate-800 rounded-xl overflow-hidden border border-slate-700 relative">
                             @php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             @endphp
                             @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Product">
                             @else
                                <div class="w-full h-full flex items-center justify-center text-slate-600">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                             @endif
                        </div>

                        <!-- Sale Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                                            {{ match($order->status) {
                                                'paid' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                                'shipped' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                                                'completed' => 'bg-slate-700 text-slate-300 border border-slate-600',
                                                'cancelled' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                                                default => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20'
                                            } }}">
                                            {{ $order->status }}
                                        </span>
                                        <span class="text-xs font-mono text-slate-500">Buyer: {{ $order->buyer->name }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-slate-500">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-white mb-1 truncate">{{ $firstItem->product_title_snapshot }}</h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-slate-400">+ {{ $order->items->count() - 1 }} other items</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 flex items-end justify-between">
                                <div>
                                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Your Earnings</div>
                                    <div class="text-xl font-black text-emerald-400 font-mono">
                                        Rp {{ number_format($order->seller_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <a href="{{ route('marketplace.orders.show', $order->id) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-700 hover:text-white transition-all">
                                    Manage Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty Sales State -->
                <div class="text-center py-20 bg-slate-900/30 rounded-3xl border border-slate-800/50 border-dashed">
                    <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No sales yet</h3>
                    <p class="text-slate-400 max-w-sm mx-auto mb-8">Turn your gear into cash. List your first item today!</p>
                     @if(Auth::user()->is_seller)
                        <a href="{{ route('marketplace.seller.products.create') }}" class="px-8 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform inline-flex items-center gap-2">
                            Sell Item
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </a>
                    @else
                         <a href="{{ route('marketplace.seller.register') }}" class="px-8 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform inline-flex items-center gap-2">
                            Become a Seller
                        </a>
                    @endif
                </div>
             @endforelse
        </div>
    </div>
</div>
@endsection