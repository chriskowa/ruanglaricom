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

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl text-blue-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Main Container -->
    <!-- Main Container -->
    <div x-data="{ tab: '{{ $cartItems->count() > 0 ? 'cart' : request()->query('tab', 'programs') }}' }" class="space-y-6">
        
        <!-- Tab Navigation -->
        <div class="bg-slate-900/50 backdrop-blur-md rounded-xl p-1 border border-slate-800 inline-flex flex-wrap gap-1">
            <button @click="tab = 'cart'" 
                class="px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300 flex items-center gap-2"
                :class="tab === 'cart' ? 'bg-neon text-slate-900 shadow-lg shadow-neon/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                <span>Program Cart</span>
                @if($cartItems->count() > 0)
                    <span class="px-2 py-0.5 text-xs font-black rounded-full bg-red-500 text-white animate-pulse">{{ $cartItems->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'programs'" 
                class="px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300"
                :class="tab === 'programs' ? 'bg-neon text-slate-900 shadow-lg shadow-neon/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Training Programs
            </button>
            <button @click="tab = 'purchases'" 
                class="px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300"
                :class="tab === 'purchases' ? 'bg-neon text-slate-900 shadow-lg shadow-neon/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Market Purchases
            </button>
            <button @click="tab = 'sales'" 
                class="px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300"
                :class="tab === 'sales' ? 'bg-neon text-slate-900 shadow-lg shadow-neon/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Market Sales
            </button>
        </div>

        <!-- Cart List Tab -->
        <div x-show="tab === 'cart'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
             
             @if($cartItems->count() > 0)
                 <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                     <!-- Cart Items -->
                     <div class="lg:col-span-2 space-y-4">
                         <div class="flex justify-end mb-2">
                             <form action="{{ route('marketplace.cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?');">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="text-xs text-red-400 hover:text-red-300 font-bold flex items-center gap-1 transition-colors">
                                     <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                     Empty Cart
                                 </button>
                             </form>
                         </div>

                         @foreach($cartItems as $item)
                             <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-4 md:p-6 hover:border-neon/30 transition-all group relative overflow-hidden shadow-xl" id="cart-row-{{ $item->id }}">
                                 <div class="absolute inset-0 bg-gradient-to-r from-neon/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                                 <div class="flex flex-col md:flex-row gap-6 relative z-10">
                                     <!-- Details -->
                                     <div class="flex-grow flex flex-col justify-between">
                                         <div>
                                             <div class="flex justify-between items-start">
                                                 <div>
                                                     <h3 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition-colors">
                                                         <a href="{{ route('programs.show', $item->program->slug) }}">{{ $item->program->title }}</a>
                                                     </h3>
                                                     <p class="text-sm text-slate-400 mb-2">Coach <span class="text-white font-bold">{{ $item->program->coach->name ?? 'Unknown' }}</span></p>
                                                 </div>
                                                 <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Remove Item">
                                                         <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                     </button>
                                                 </form>
                                             </div>
                                             
                                             <div class="flex flex-wrap gap-2 mt-2">
                                                 <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">{{ $item->program->distance_target }}</span>
                                                 <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->program->difficulty == 'beginner' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : ($item->program->difficulty == 'intermediate' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                                     {{ ucfirst($item->program->difficulty) }}
                                                 </span>
                                             </div>
                                         </div>

                                         <div class="flex justify-between items-end mt-4 md:mt-0">
                                             <div class="text-slate-500 text-xs font-mono">
                                                 Qty: {{ $item->quantity }}
                                             </div>
                                             <div class="text-right">
                                                 <p class="text-xs text-slate-500 mb-0.5">Price</p>
                                                 <p class="text-lg font-black text-neon">Rp <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span></p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         @endforeach
                     </div>

                     <!-- Summary -->
                     <div class="lg:col-span-1">
                         <div class="sticky top-24">
                             <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 shadow-xl">
                                 <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                     <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                     Order Summary
                                 </h3>
                                 
                                 <div class="space-y-4 mb-6">
                                     <div class="flex justify-between text-sm">
                                         <span class="text-slate-400">Subtotal</span>
                                         <span class="text-white font-medium">Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</span>
                                     </div>
                                     <div class="h-px bg-slate-700 my-2"></div>
                                     <div class="flex justify-between text-lg">
                                         <span class="text-white font-bold">Total</span>
                                         <span class="text-neon font-black">Rp <span>{{ number_format($cartTotal, 0, ',', '.') }}</span></span>
                                     </div>
                                 </div>

                                 <a href="{{ route('marketplace.checkout.index') }}" class="block w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-center rounded-xl transition-all shadow-lg shadow-neon/20 mb-3 uppercase tracking-wider">
                                     Checkout Now
                                 </a>
                                 <a href="{{ route('marketplace.index') }}" class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-center rounded-xl transition-colors text-sm">
                                     Browse More Programs
                                 </a>
                             </div>
                         </div>
                     </div>
                 </div>
             @else
                 <div class="text-center py-20 bg-slate-900/30 rounded-3xl border border-slate-800/50 border-dashed">
                     <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                         <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                     </div>
                     <h3 class="text-xl font-bold text-white mb-2">Keranjang belanja kosong</h3>
                     <p class="text-slate-400 max-w-sm mx-auto mb-8">Anda belum menambahkan program latihan apa pun ke keranjang belanja.</p>
                     <a href="{{ route('marketplace.index') }}" class="px-8 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform inline-flex items-center gap-2">
                         Cari Program Latihan
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                     </a>
                 </div>
             @endif
        </div>

        <!-- Programs List -->
        <div x-show="tab === 'programs'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
             
             @forelse($programOrders as $order)
                <div class="bg-slate-900/80 backdrop-blur-sm rounded-2xl border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Program Icon -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0 bg-slate-800 rounded-xl overflow-hidden border border-slate-700 relative flex items-center justify-center text-neon">
                             <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                             <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        </div>

                        <!-- Order Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                                            {{ $order->payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' }}">
                                            {{ $order->payment_status === 'paid' ? 'Paid' : 'Pending' }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-mono">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-slate-500">#{{ $order->order_number }}</span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-white mb-1 truncate">
                                    <?php $firstItem = $order->items->first(); ?>
                                    {{ $firstItem ? $firstItem->program_title : 'Program Purchase' }}
                                </h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-slate-400">+ {{ $order->items->count() - 1 }} other programs</p>
                                @endif
                                @if($firstItem && $firstItem->program && $firstItem->program->coach)
                                    <p class="text-xs text-slate-400">Coach: {{ $firstItem->program->coach->name }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 flex items-end justify-between">
                                <div>
                                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Amount</div>
                                    <div class="text-xl font-black text-neon font-mono">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    @if($order->payment_status == 'pending' && $order->payment_method === 'midtrans')
                                        <button onclick="payProgram({{ $order->id }}, this)" class="px-5 py-2 bg-neon text-slate-900 text-sm font-bold rounded-lg hover:bg-neon/90 hover:shadow-lg hover:shadow-neon/20 transition-all flex items-center gap-2">
                                            <span>Pay Now</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('marketplace.program-orders.show', $order->id) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-700 hover:text-white transition-all">
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
                        <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No program purchases yet</h3>
                    <p class="text-slate-400 max-w-sm mx-auto mb-8">Ready to start training? Explore professional programs designed by running coaches.</p>
                    <a href="{{ route('programs.index') }}" class="px-8 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform inline-flex items-center gap-2">
                        Browse Programs
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
             @endforelse
        </div>

        <!-- Purchases List -->
        <div x-show="tab === 'purchases'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4" style="display: none;">
             
             @forelse($purchases as $order)
                <div class="bg-slate-900/80 backdrop-blur-sm rounded-2xl border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Product Image (First Item) -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0 bg-slate-800 rounded-xl overflow-hidden border border-slate-700 relative">
                             <?php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             ?>
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
                                        @if(empty($order->shipping_address))
                                            <a href="{{ route('marketplace.checkout.show', $order->id) }}" class="px-5 py-2 bg-amber-500 text-slate-900 text-sm font-bold rounded-lg hover:bg-amber-400 hover:shadow-lg hover:shadow-amber-500/20 transition-all flex items-center gap-2">
                                                <span>Complete Shipping</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            </a>
                                        @else
                                            <button onclick="payPurchase({{ $order->id }}, this)" class="px-5 py-2 bg-neon text-slate-900 text-sm font-bold rounded-lg hover:bg-neon/90 hover:shadow-lg hover:shadow-neon/20 transition-all flex items-center gap-2">
                                                <span>Pay Now</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </button>
                                        @endif
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
                             <?php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             ?>
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

@push('scripts')
<script>
    function payProgram(orderId, button) {
        if (button) {
            button.disabled = true;
            button.innerHTML = 'Processing...';
        }
        
        fetch('{{ url("marketplace/checkout/program") }}/' + orderId + '/pay', {
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
                        window.location.href = "{{ route('marketplace.orders.index') }}?tab=programs&payment=success";
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
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '<span>Pay Now</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>';
                        }
                    }
                });
            } else {
                alert('Gagal mengambil token pembayaran.');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<span>Pay Now</span>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<span>Pay Now</span>';
            }
        });
    }

    function payPurchase(orderId, button) {
        if (button) {
            button.disabled = true;
            button.innerHTML = 'Processing...';
        }
        
        fetch('{{ url("marketplace/checkout") }}/' + orderId + '/pay', {
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
                        window.location.href = "{{ route('marketplace.orders.index') }}?tab=purchases&payment=success";
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
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '<span>Pay Now</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>';
                        }
                    }
                });
            } else {
                alert('Gagal mengambil token pembayaran.');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<span>Pay Now</span>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<span>Pay Now</span>';
            }
        });
    }
</script>
@endpush
@endsection