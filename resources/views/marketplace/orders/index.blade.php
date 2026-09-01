@extends('layouts.pacerhub')

@section('content')
@php($withSidebar = true)
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
             <a href="{{ route('marketplace.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 rounded-md text-xs font-bold transition-all">
                Browse Market
            </a>
            @if(Auth::user()->is_seller)
                 <a href="{{ route('marketplace.seller.products.index') }}" class="px-4 py-2 bg-slate-800 text-neon hover:bg-slate-700 rounded-md text-xs font-bold transition-all">
                    Seller Dashboard
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-slate-900 border border-slate-700 rounded-md text-slate-200 text-sm flex items-center gap-3">
            <span class="text-neon font-bold">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-950/80 border border-rose-800 rounded-md text-rose-200 text-sm flex items-center gap-3">
            <span class="text-rose-400 font-bold">✗</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 p-4 bg-slate-900 border border-slate-700 rounded-md text-slate-300 text-sm flex items-center gap-3">
            <span class="text-neon font-bold">ℹ</span>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Main Container -->
    <div x-data="{ tab: '{{ request()->query('tab', $cartItems->count() > 0 ? 'cart' : 'purchases') }}' }" class="space-y-6">
        
        <!-- Tab Navigation -->
        <div class="bg-slate-900/80 rounded-md p-1 border border-slate-800 inline-flex flex-wrap gap-1">
            <button @click="tab = 'cart'" 
                class="px-5 py-2 rounded-md text-xs font-bold uppercase tracking-wider transition-all duration-200 flex items-center gap-2"
                :class="tab === 'cart' ? 'bg-neon text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                <span>Program Cart</span>
                @if($cartItems->count() > 0)
                    <span class="px-1.5 py-0.2 text-[10px] font-bold rounded bg-rose-500 text-white">{{ $cartItems->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'programs'" 
                class="px-5 py-2 rounded-md text-xs font-bold uppercase tracking-wider transition-all duration-200"
                :class="tab === 'programs' ? 'bg-neon text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Training Programs
            </button>
            <button @click="tab = 'purchases'" 
                class="px-5 py-2 rounded-md text-xs font-bold uppercase tracking-wider transition-all duration-200"
                :class="tab === 'purchases' ? 'bg-neon text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
                Market Purchases
            </button>
            <button @click="tab = 'sales'" 
                class="px-5 py-2 rounded-md text-xs font-bold uppercase tracking-wider transition-all duration-200"
                :class="tab === 'sales' ? 'bg-neon text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'">
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
                             <?php
                                 $isProduct = (bool) $item->product_id;
                                 $title = $isProduct ? ($item->product->title ?? 'Produk Marketplace') : ($item->program->title ?? 'Program Latihan');
                                 $url = $isProduct ? route('marketplace.show', $item->product->slug ?? '#') : ($item->program ? route('programs.show', $item->program->slug) : '#');
                                 $subtitle = $isProduct ? ('Penjual: ' . ($item->product->seller->name ?? 'Seller')) : ('Coach: ' . ($item->program->coach->name ?? 'Coach'));
                             ?>
                             <div class="bg-slate-900/80 rounded-lg border border-slate-800 p-4 md:p-6 hover:border-slate-700 transition-all group relative overflow-hidden shadow-sm" id="cart-row-{{ $item->id }}">
                                 <div class="flex flex-col md:flex-row gap-6 relative z-10">
                                     <!-- Details -->
                                     <div class="flex-grow flex flex-col justify-between">
                                         <div>
                                             <div class="flex justify-between items-start">
                                                 <div>
                                                     <h3 class="text-lg font-bold text-white mb-1 group-hover:text-neon transition-colors">
                                                         <a href="{{ $url }}">{{ $title }}</a>
                                                     </h3>
                                                     <p class="text-xs text-slate-400 mb-2">{{ $subtitle }}</p>
                                                 </div>
                                                 <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-950/40 rounded-md transition-all" title="Remove Item">
                                                         <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                     </button>
                                                 </form>
                                             </div>
                                             
                                             @if($isProduct && $item->product)
                                                 <div class="flex flex-wrap gap-2 mt-2">
                                                     <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">Size: {{ $item->product->size ?: '-' }}</span>
                                                     <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-200 border border-slate-700">{{ $item->product->condition == 'new' ? 'Baru' : 'Bekas' }}</span>
                                                 </div>
                                             @elseif($item->program)
                                                 <div class="flex flex-wrap gap-2 mt-2">
                                                     <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">{{ $item->program->distance_target }}</span>
                                                     <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                                         {{ ucfirst($item->program->difficulty) }}
                                                     </span>
                                                 </div>
                                             @endif
                                         </div>

                                         <div class="flex justify-between items-end mt-4 md:mt-0">
                                             <div class="text-slate-500 text-xs font-medium">
                                                 Qty: {{ $item->quantity }}
                                             </div>
                                             <div class="text-right">
                                                 <p class="text-xs text-slate-500 mb-0.5">Price</p>
                                                 <p class="text-lg font-black text-neon font-mono">Rp <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span></p>
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
                             <div class="bg-slate-900/80 rounded-lg border border-slate-800 p-6 shadow-sm">
                                 <h3 class="text-sm font-bold text-white mb-6 uppercase tracking-wider">
                                     Order Summary
                                 </h3>
                                 
                                 <div class="space-y-4 mb-6">
                                     <div class="flex justify-between text-xs">
                                         <span class="text-slate-400">Subtotal</span>
                                         <span class="text-white font-mono font-medium">Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</span>
                                     </div>
                                     <div class="h-px bg-slate-800 my-2"></div>
                                     <div class="flex justify-between text-base">
                                         <span class="text-white font-bold">Total</span>
                                         <span class="text-neon font-mono font-black">Rp <span>{{ number_format($cartTotal, 0, ',', '.') }}</span></span>
                                     </div>
                                 </div>

                                  <?php
                                      $firstProductItem = $cartItems->first(function($it) {
                                          return !empty($it->product_id);
                                      });
                                  ?>
                                  @if($firstProductItem)
                                      <form action="{{ route('marketplace.checkout.init') }}" method="POST">
                                          @csrf
                                          <input type="hidden" name="product_id" value="{{ $firstProductItem->product_id }}">
                                          <button type="submit" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-center rounded-md transition-all shadow-sm mb-3 text-xs uppercase tracking-wider">
                                              Checkout Now
                                          </button>
                                      </form>
                                  @else
                                      <a href="{{ route('marketplace.checkout.index') }}" class="block w-full py-3 bg-neon hover:bg-white text-dark font-black text-center rounded-md transition-all shadow-sm mb-3 text-xs uppercase tracking-wider">
                                          Checkout Now
                                      </a>
                                  @endif
                                  <a href="{{ route('marketplace.index') }}" class="block w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-center rounded-md transition-colors text-xs">
                                      Browse Market
                                  </a>
                             </div>
                         </div>
                     </div>
                 </div>
             @else
                 <div class="text-center py-20 bg-slate-900/30 rounded-lg border border-slate-800/50">
                     <h3 class="text-lg font-bold text-white mb-2">Keranjang belanja kosong</h3>
                     <p class="text-slate-400 text-xs max-w-sm mx-auto mb-6">Anda belum menambahkan program latihan apa pun ke keranjang belanja.</p>
                     <a href="{{ route('marketplace.index') }}" class="px-6 py-2.5 bg-neon hover:bg-white text-slate-950 font-bold rounded-md text-xs uppercase transition inline-flex items-center">
                         Cari Program Latihan
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
                <div class="bg-slate-900/80 rounded-lg border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
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
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                            {{ $order->payment_status === 'paid' ? 'bg-slate-800 text-slate-200 border border-slate-700' : 'bg-amber-950/80 text-amber-300 border border-amber-600/40' }}">
                                            {{ $order->payment_status === 'paid' ? 'Paid' : 'Pending' }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">#{{ $order->order_number }}</span>
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
                                    @if($order->payment_status == 'pending')
                                        <form action="{{ route('marketplace.program-orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan program ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-4 py-2 bg-rose-950/80 text-rose-300 hover:bg-rose-900 text-xs font-bold rounded-md transition-all">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->payment_status == 'pending' && $order->payment_method === 'midtrans')
                                        <button onclick="payProgram({{ $order->id }}, this)" class="px-5 py-2 bg-neon text-slate-950 text-xs font-bold rounded-md hover:bg-white transition-all">
                                            Pay Now
                                        </button>
                                    @endif
                                    <a href="{{ route('marketplace.program-orders.show', $order->id) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-md hover:bg-slate-700 hover:text-white transition-all">
                                        Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty State -->
                <div class="text-center py-20 bg-slate-900/30 rounded-lg border border-slate-800/50">
                    <h3 class="text-lg font-bold text-white mb-2">No program purchases yet</h3>
                    <p class="text-slate-400 text-xs max-w-sm mx-auto mb-6">Ready to start training? Explore professional programs designed by running coaches.</p>
                    <a href="{{ route('programs.index') }}" class="px-6 py-2.5 bg-neon text-slate-950 font-bold rounded-md hover:bg-white text-xs uppercase transition inline-flex items-center">
                        Browse Programs
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
                <div class="bg-slate-900/80 rounded-lg border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Product Image (First Item) -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0 bg-slate-800 rounded-md overflow-hidden border border-slate-700 relative">
                             <?php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             ?>
                             @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Product">
                             @else
                                <div class="w-full h-full flex items-center justify-center text-slate-600 text-xs font-mono">
                                    NO IMAGE
                                </div>
                             @endif
                        </div>

                        <!-- Order Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                            {{ match($order->status) {
                                                'paid' => 'bg-slate-800 text-slate-200 border border-slate-700',
                                                'shipped' => 'bg-blue-950/80 text-blue-300 border border-blue-600/40',
                                                'completed' => 'bg-slate-800 text-slate-300 border border-slate-700',
                                                'cancelled' => 'bg-rose-950/80 text-rose-300 border border-rose-600/40',
                                                default => 'bg-amber-950/80 text-amber-300 border border-amber-600/40'
                                            } }}">
                                            {{ $order->status }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs text-slate-500 font-semibold">#{{ $order->invoice_number }}</span>
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
                                        <form action="{{ route('marketplace.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan produk ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-4 py-2 bg-rose-950/80 text-rose-300 hover:bg-rose-900 text-xs font-bold rounded-md transition-all">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->status == 'pending')
                                        @if(empty($order->shipping_address))
                                            <a href="{{ route('marketplace.checkout.show', $order->id) }}" class="px-5 py-2 bg-white hover:bg-slate-200 text-slate-950 text-xs font-black uppercase tracking-wider rounded-md transition-all shadow-sm">
                                                Complete Shipping
                                            </a>
                                        @else
                                            <button onclick="payPurchase({{ $order->id }}, this)" class="px-5 py-2 bg-neon hover:bg-white text-slate-950 text-xs font-black uppercase tracking-wider rounded-md transition-all shadow-sm">
                                                Pay Now
                                            </button>
                                        @endif
                                    @endif
                                    <a href="{{ route('marketplace.orders.show', $order->id) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-md hover:bg-slate-700 hover:text-white transition-all">
                                        Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty State -->
                <div class="text-center py-20 bg-slate-900/30 rounded-lg border border-slate-800/50">
                    <h3 class="text-lg font-bold text-white mb-2">No purchases yet</h3>
                    <p class="text-slate-400 text-xs max-w-sm mx-auto mb-6">Ready to gear up? Explore the marketplace for the best running equipment.</p>
                    <a href="{{ route('marketplace.index') }}" class="px-6 py-2.5 bg-neon hover:bg-white text-slate-950 font-bold rounded-md text-xs uppercase transition inline-flex items-center">
                        Start Shopping
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
                <div class="bg-slate-900/80 rounded-lg border border-slate-800 overflow-hidden hover:border-slate-700 transition-all group">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                         <!-- Product Image -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0 bg-slate-800 rounded-md overflow-hidden border border-slate-700 relative">
                             <?php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             ?>
                             @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Product">
                             @else
                                <div class="w-full h-full flex items-center justify-center text-slate-600 text-xs font-mono">
                                    NO IMAGE
                                </div>
                             @endif
                        </div>

                        <!-- Sale Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                            {{ match($order->status) {
                                                'paid' => 'bg-slate-800 text-slate-200 border border-slate-700',
                                                'shipped' => 'bg-blue-950/80 text-blue-300 border border-blue-600/40',
                                                'completed' => 'bg-slate-800 text-slate-300 border border-slate-700',
                                                'cancelled' => 'bg-rose-950/80 text-rose-300 border border-rose-600/40',
                                                default => 'bg-amber-950/80 text-amber-300 border border-amber-600/40'
                                            } }}">
                                            {{ $order->status }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-medium">Buyer: {{ $order->buyer->name }}</span>
                                    </div>
                                    <span class="text-xs text-slate-500 font-semibold">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-white mb-1 truncate">{{ $firstItem->product_title_snapshot }}</h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-slate-400">+ {{ $order->items->count() - 1 }} other items</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 flex items-end justify-between">
                                <div>
                                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Your Earnings</div>
                                    <div class="text-xl font-black text-neon font-mono">
                                        Rp {{ number_format($order->seller_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <a href="{{ route('marketplace.orders.show', $order->id) }}" class="px-4 py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-md hover:bg-slate-700 hover:text-white transition-all">
                                    Manage Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty Sales State -->
                <div class="text-center py-20 bg-slate-900/30 rounded-lg border border-slate-800/50">
                    <h3 class="text-lg font-bold text-white mb-2">No sales yet</h3>
                    <p class="text-slate-400 text-xs max-w-sm mx-auto mb-6">Turn your gear into cash. List your first item today!</p>
                     @if(Auth::user()->is_seller)
                        <a href="{{ route('marketplace.seller.products.create') }}" class="px-6 py-2.5 bg-neon text-slate-950 font-bold rounded-md hover:bg-white transition text-xs uppercase inline-flex items-center">
                            Sell Item
                        </a>
                    @else
                         <a href="{{ route('marketplace.seller.register') }}" class="px-6 py-2.5 bg-neon text-slate-950 font-bold rounded-md hover:bg-white transition text-xs uppercase inline-flex items-center">
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