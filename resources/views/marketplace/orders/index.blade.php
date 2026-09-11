@extends('layouts.pacerhub')

@section('title', 'Riwayat Pesanan & Penjualan - RuangLari')

@section('content')
@php($withSidebar = true)
<div class="pt-20 md:pt-6 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen font-sans bg-[#090A0E] text-slate-200">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 md:mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white mb-1">
                Riwayat Pesanan
            </h1>
            <p class="text-xs text-zinc-300">Pantau transaksi belanja barang, program latihan, dan kelola penjualan marketplace Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('marketplace.index') }}" class="px-4 py-2 bg-[#131722] border border-zinc-800 text-zinc-200 hover:text-white hover:bg-zinc-800 rounded-md text-xs font-semibold transition-colors">
                Katalog Market
            </a>
            @if(Auth::user()->is_seller)
                <a href="{{ route('marketplace.seller.products.index') }}" class="px-4 py-2 bg-lime-600 hover:bg-lime-500 text-black rounded-md text-xs font-bold transition-colors">
                    Dashboard Penjual
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-[#131722] border border-zinc-800 rounded-md text-zinc-200 text-xs flex items-center gap-3">
            <span class="text-lime-400 font-bold">✓</span>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-[#1c1317] border border-rose-900/60 rounded-md text-rose-200 text-xs flex items-center gap-3">
            <span class="text-rose-400 font-bold">✕</span>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 p-4 bg-[#131722] border border-zinc-800 rounded-md text-zinc-300 text-xs flex items-center gap-3">
            <span class="text-lime-400 font-bold">ℹ</span>
            <span class="font-medium">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Main Container -->
    <div x-data="{ tab: '{{ request()->query('tab', $cartItems->count() > 0 ? 'cart' : 'purchases') }}' }" class="space-y-6">
        
        <!-- Tab Navigation -->
        <div class="bg-[#131722] rounded-lg p-1.5 border border-zinc-800 inline-flex flex-wrap gap-1.5">
            <button @click="tab = 'cart'" 
                class="px-4 py-2 rounded-md text-xs font-semibold transition-colors flex items-center gap-2"
                :class="tab === 'cart' ? 'bg-lime-600 text-black font-bold' : 'text-zinc-300 hover:text-white hover:bg-zinc-800/60'">
                <span>Keranjang Program</span>
                @if($cartItems->count() > 0)
                    <span class="px-1.5 py-0.2 text-[10px] font-bold rounded" :class="tab === 'cart' ? 'bg-black text-white' : 'bg-rose-500 text-white'">{{ $cartItems->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'programs'" 
                class="px-4 py-2 rounded-md text-xs font-semibold transition-colors"
                :class="tab === 'programs' ? 'bg-lime-600 text-black font-bold' : 'text-zinc-300 hover:text-white hover:bg-zinc-800/60'">
                Program Latihan
            </button>
            <button @click="tab = 'purchases'" 
                class="px-4 py-2 rounded-md text-xs font-semibold transition-colors"
                :class="tab === 'purchases' ? 'bg-lime-600 text-black font-bold' : 'text-zinc-300 hover:text-white hover:bg-zinc-800/60'">
                Pembelian Produk
            </button>
            <button @click="tab = 'sales'" 
                class="px-4 py-2 rounded-md text-xs font-semibold transition-colors"
                :class="tab === 'sales' ? 'bg-lime-600 text-black font-bold' : 'text-zinc-300 hover:text-white hover:bg-zinc-800/60'">
                Penjualan Toko
            </button>
        </div>

        <!-- Cart List Tab -->
        <div x-show="tab === 'cart'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
             
             @if($cartItems->count() > 0)
                 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                     <!-- Cart Items -->
                     <div class="lg:col-span-2 space-y-4">
                         <div class="flex justify-end mb-2">
                             <form action="{{ route('marketplace.cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?');">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-semibold flex items-center gap-1.5 transition-colors">
                                     <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                     <span>Kosongkan Keranjang</span>
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
                             <div class="bg-[#131722] rounded-lg border border-zinc-800 p-5 md:p-6 shadow-sm hover:border-zinc-700 transition-colors group relative" id="cart-row-{{ $item->id }}">
                                 <div class="flex flex-col sm:flex-row gap-5">
                                     <!-- Details -->
                                     <div class="flex-grow flex flex-col justify-between">
                                         <div>
                                             <div class="flex justify-between items-start gap-4">
                                                 <div>
                                                     <h3 class="text-base font-bold text-white mb-1 hover:text-lime-400 transition-colors">
                                                         <a href="{{ $url }}">{{ $title }}</a>
                                                     </h3>
                                                     <p class="text-xs text-zinc-300 mb-2">{{ $subtitle }}</p>
                                                 </div>
                                                 <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="p-1.5 text-zinc-400 hover:text-rose-400 hover:bg-zinc-800 rounded-md transition-colors" title="Hapus Item">
                                                         <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                     </button>
                                                 </form>
                                             </div>
                                             
                                             @if($isProduct && $item->product)
                                                 <div class="flex flex-wrap gap-2 mt-2">
                                                     <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#0B0E14] text-zinc-200 border border-zinc-800">Size: {{ $item->product->size ?: '-' }}</span>
                                                     <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#0B0E14] text-zinc-200 border border-zinc-800">{{ $item->product->condition == 'new' ? 'Baru' : 'Bekas' }}</span>
                                                 </div>
                                             @elseif($item->program)
                                                 <div class="flex flex-wrap gap-2 mt-2">
                                                     <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#0B0E14] text-zinc-200 border border-zinc-800">{{ $item->program->distance_target }}</span>
                                                     <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#0B0E14] text-zinc-200 border border-zinc-800">
                                                         {{ ucfirst($item->program->difficulty) }}
                                                     </span>
                                                 </div>
                                             @endif
                                         </div>

                                         <div class="flex justify-between items-end mt-4 pt-3 border-t border-zinc-800/80">
                                             <div class="text-zinc-300 text-xs font-medium">
                                                 Jumlah: {{ $item->quantity }}
                                             </div>
                                             <div class="text-right">
                                                 <p class="text-[11px] text-zinc-400 mb-0.5">Harga Satuan / Subtotal</p>
                                                 <p class="text-base font-bold text-white font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
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
                             <div class="bg-[#131722] rounded-lg border border-zinc-800 p-6 shadow-sm">
                                 <h3 class="text-sm font-bold text-white mb-4 pb-2 border-b border-zinc-800">
                                     Ringkasan Pesanan
                                 </h3>
                                 
                                 <div class="space-y-3 mb-6">
                                     <div class="flex justify-between text-xs">
                                         <span class="text-zinc-300">Subtotal Item</span>
                                         <span class="text-white font-mono font-medium">Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</span>
                                     </div>
                                     <div class="h-px bg-zinc-800 my-2"></div>
                                     <div class="flex justify-between text-base">
                                         <span class="text-white font-bold">Total Pembayaran</span>
                                         <span class="text-lime-400 font-mono font-bold">Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
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
                                          <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-center rounded-md transition-colors shadow-sm mb-3 text-xs">
                                              Lanjut ke Pembayaran
                                          </button>
                                      </form>
                                  @else
                                      <a href="{{ route('marketplace.checkout.index') }}" class="block w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-center rounded-md transition-colors shadow-sm mb-3 text-xs">
                                          Lanjut ke Pembayaran
                                      </a>
                                  @endif
                                  <a href="{{ route('marketplace.index') }}" class="block w-full py-2.5 bg-[#0B0E14] hover:bg-zinc-800 text-zinc-300 hover:text-white font-semibold text-center rounded-md border border-zinc-800 transition-colors text-xs">
                                      Kembali ke Katalog
                                  </a>
                             </div>
                         </div>
                     </div>
                 </div>
             @else
                 <div class="text-center py-16 bg-[#131722] rounded-lg border border-zinc-800">
                     <h3 class="text-base font-bold text-white mb-2">Keranjang belanja kosong</h3>
                     <p class="text-zinc-300 text-xs max-w-sm mx-auto mb-6">Anda belum menambahkan program latihan atau barang apa pun ke keranjang belanja.</p>
                     <a href="{{ route('marketplace.index') }}" class="px-6 py-2.5 bg-lime-600 hover:bg-lime-500 text-black font-bold rounded-md text-xs transition-colors inline-flex items-center">
                         Eksplor Marketplace
                     </a>
                 </div>
             @endif
        </div>

        <!-- Programs List -->
        <div x-show="tab === 'programs'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
             
             @forelse($programOrders as $order)
                <div class="bg-[#131722] rounded-lg border border-zinc-800 overflow-hidden hover:border-zinc-700 transition-colors shadow-sm">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Program Icon -->
                        <div class="w-full sm:w-28 h-28 flex-shrink-0 bg-[#0B0E14] rounded-md overflow-hidden border border-zinc-800 relative flex items-center justify-center text-lime-400">
                             <svg class="w-10 h-10 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>

                        <!-- Order Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-0.5 rounded text-xs font-semibold
                                            {{ $order->payment_status === 'paid' ? 'bg-[#0B0E14] text-lime-400 border border-zinc-800' : 'bg-amber-950/40 text-amber-300 border border-amber-800/40' }}">
                                            {{ $order->payment_status === 'paid' ? 'Lunas' : 'Menunggu Bayar' }}
                                        </span>
                                        <span class="text-xs text-zinc-300 font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-zinc-300 font-medium">#{{ $order->order_number }}</span>
                                </div>
                                
                                <h3 class="text-base font-bold text-white mb-1">
                                    <?php $firstItem = $order->items->first(); ?>
                                    {{ $firstItem ? $firstItem->program_title : 'Pembelian Program Latihan' }}
                                </h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-zinc-300">+ {{ $order->items->count() - 1 }} program lainnya</p>
                                @endif
                                @if($firstItem && $firstItem->program && $firstItem->program->coach)
                                    <p class="text-xs text-zinc-300">Coach: {{ $firstItem->program->coach->name }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-end justify-between">
                                <div>
                                    <div class="text-[11px] text-zinc-400 mb-0.5">Total Pembayaran</div>
                                    <div class="text-lg font-bold text-white font-mono">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    @if($order->payment_status == 'pending')
                                        <form action="{{ route('marketplace.program-orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan program ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3.5 py-2 bg-[#1c1317] border border-rose-900/60 text-rose-300 hover:bg-rose-950 text-xs font-semibold rounded-md transition-colors">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->payment_status == 'pending' && $order->payment_method === 'midtrans')
                                        <button onclick="payProgram({{ $order->id }}, this)" class="px-4 py-2 bg-lime-600 hover:bg-lime-500 text-black text-xs font-bold rounded-md transition-colors">
                                            Bayar Sekarang
                                        </button>
                                    @endif
                                    <a href="{{ route('marketplace.program-orders.show', $order->id) }}" class="px-4 py-2 bg-[#0B0E14] border border-zinc-800 text-zinc-200 text-xs font-semibold rounded-md hover:bg-zinc-800 hover:text-white transition-colors">
                                        Rincian
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty State -->
                <div class="text-center py-16 bg-[#131722] rounded-lg border border-zinc-800">
                    <h3 class="text-base font-bold text-white mb-2">Belum ada pesanan program</h3>
                    <p class="text-zinc-300 text-xs max-w-sm mx-auto mb-6">Tingkatkan performa latihan Anda dengan program terstruktur dari pelatih lari berpengalaman.</p>
                    <a href="{{ route('programs.index') }}" class="px-6 py-2.5 bg-lime-600 hover:bg-lime-500 text-black font-bold rounded-md text-xs transition-colors inline-flex items-center">
                        Cari Program Latihan
                    </a>
                </div>
             @endforelse
        </div>

        <!-- Purchases List -->
        <div x-show="tab === 'purchases'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4" style="display: none;">
             
             @forelse($purchases as $order)
                <div class="bg-[#131722] rounded-lg border border-zinc-800 overflow-hidden hover:border-zinc-700 transition-colors shadow-sm">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Product Image (First Item) -->
                        <div class="w-full sm:w-28 h-28 flex-shrink-0 bg-[#0B0E14] rounded-md overflow-hidden border border-zinc-800 relative flex items-center justify-center p-2">
                             <?php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             ?>
                             @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" class="max-w-full max-h-full object-contain" alt="Product">
                             @else
                                <div class="text-zinc-500 text-xs font-mono font-medium">
                                    NO IMAGE
                                </div>
                             @endif
                        </div>

                        <!-- Order Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-0.5 rounded text-xs font-semibold
                                            {{ match($order->status) {
                                                'paid' => 'bg-[#0B0E14] text-lime-400 border border-zinc-800',
                                                'shipped' => 'bg-blue-950/50 text-blue-300 border border-blue-800/50',
                                                'completed' => 'bg-[#0B0E14] text-zinc-300 border border-zinc-800',
                                                'cancelled' => 'bg-rose-950/50 text-rose-300 border border-rose-800/50',
                                                default => 'bg-amber-950/50 text-amber-300 border border-amber-800/50'
                                            } }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        <span class="text-xs text-zinc-300 font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-zinc-300 font-medium">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <h3 class="text-base font-bold text-white mb-1">{{ $firstItem->product_title_snapshot }}</h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-zinc-300">+ {{ $order->items->count() - 1 }} barang lainnya</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-end justify-between">
                                <div>
                                    <div class="text-[11px] text-zinc-400 mb-0.5">Total Belanja</div>
                                    <div class="text-lg font-bold text-white font-mono">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    @if($order->status == 'pending')
                                        <form action="{{ route('marketplace.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan produk ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3.5 py-2 bg-[#1c1317] border border-rose-900/60 text-rose-300 hover:bg-rose-950 text-xs font-semibold rounded-md transition-colors">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->status == 'pending')
                                        @if(empty($order->shipping_address))
                                            <a href="{{ route('marketplace.checkout.show', $order->id) }}" class="px-4 py-2 bg-lime-600 hover:bg-lime-500 text-black text-xs font-bold rounded-md transition-colors shadow-sm">
                                                Lengkapi Pengiriman
                                            </a>
                                        @else
                                            <button onclick="payPurchase({{ $order->id }}, this)" class="px-4 py-2 bg-lime-600 hover:bg-lime-500 text-black text-xs font-bold rounded-md transition-colors shadow-sm">
                                                Bayar Sekarang
                                            </button>
                                        @endif
                                    @endif
                                    <a href="{{ route('marketplace.orders.show', $order->id) }}" class="px-4 py-2 bg-[#0B0E14] border border-zinc-800 text-zinc-200 text-xs font-semibold rounded-md hover:bg-zinc-800 hover:text-white transition-colors">
                                        Rincian
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty State -->
                <div class="text-center py-16 bg-[#131722] rounded-lg border border-zinc-800">
                    <h3 class="text-base font-bold text-white mb-2">Belum ada pembelian produk</h3>
                    <p class="text-zinc-300 text-xs max-w-sm mx-auto mb-6">Dapatkan sepatu lari, aksesoris, dan perlengkapan original dari sesama pelari.</p>
                    <a href="{{ route('marketplace.index') }}" class="px-6 py-2.5 bg-lime-600 hover:bg-lime-500 text-black font-bold rounded-md text-xs transition-colors inline-flex items-center">
                        Mulai Belanja
                    </a>
                </div>
             @endforelse
        </div>

        <!-- Sales List -->
        <div x-show="tab === 'sales'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4" style="display: none;">
             
             @forelse($sales as $order)
                <div class="bg-[#131722] rounded-lg border border-zinc-800 overflow-hidden hover:border-zinc-700 transition-colors shadow-sm">
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
                         <!-- Product Image -->
                        <div class="w-full sm:w-28 h-28 flex-shrink-0 bg-[#0B0E14] rounded-md overflow-hidden border border-zinc-800 relative flex items-center justify-center p-2">
                             <?php
                                $firstItem = $order->items->first();
                                $productImage = $firstItem->product?->primaryImage?->image_path;
                             ?>
                             @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" class="max-w-full max-h-full object-contain" alt="Product">
                             @else
                                <div class="text-zinc-500 text-xs font-mono font-medium">
                                    NO IMAGE
                                </div>
                             @endif
                        </div>

                        <!-- Sale Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-0.5 rounded text-xs font-semibold
                                            {{ match($order->status) {
                                                'paid' => 'bg-[#0B0E14] text-lime-400 border border-zinc-800',
                                                'shipped' => 'bg-blue-950/50 text-blue-300 border border-blue-800/50',
                                                'completed' => 'bg-[#0B0E14] text-zinc-300 border border-zinc-800',
                                                'cancelled' => 'bg-rose-950/50 text-rose-300 border border-rose-800/50',
                                                default => 'bg-amber-950/50 text-amber-300 border border-amber-800/50'
                                            } }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        <span class="text-xs text-zinc-300 font-medium">Pembeli: {{ $order->buyer->name }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-zinc-300 font-medium">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <h3 class="text-base font-bold text-white mb-1">{{ $firstItem->product_title_snapshot }}</h3>
                                @if($order->items->count() > 1)
                                    <p class="text-xs text-zinc-300">+ {{ $order->items->count() - 1 }} barang lainnya</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-end justify-between">
                                <div>
                                    <div class="text-[11px] text-zinc-400 mb-0.5">Pendapatan Bersih Anda</div>
                                    <div class="text-lg font-bold text-white font-mono">
                                        Rp {{ number_format($order->seller_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <a href="{{ route('marketplace.orders.show', $order->id) }}" class="px-4 py-2 bg-lime-600 hover:bg-lime-500 text-black text-xs font-bold rounded-md transition-colors">
                                    Kelola Pesanan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <!-- Empty Sales State -->
                <div class="text-center py-16 bg-[#131722] rounded-lg border border-zinc-800">
                    <h3 class="text-base font-bold text-white mb-2">Belum ada penjualan</h3>
                    <p class="text-zinc-300 text-xs max-w-sm mx-auto mb-6">Ubah perlengkapan lari Anda yang tidak terpakai menjadi penghasilan tambahan.</p>
                     @if(Auth::user()->is_seller)
                        <a href="{{ route('marketplace.seller.products.create') }}" class="px-6 py-2.5 bg-lime-600 hover:bg-lime-500 text-black font-bold rounded-md transition-colors text-xs inline-flex items-center">
                            Jual Barang Sekarang
                        </a>
                    @else
                         <a href="{{ route('marketplace.seller.register') }}" class="px-6 py-2.5 bg-lime-600 hover:bg-lime-500 text-black font-bold rounded-md transition-colors text-xs inline-flex items-center">
                            Daftar Sebagai Penjual
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
            button.innerHTML = 'Memproses...';
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
                        alert("Menunggu pembayaran Anda!");
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Pembayaran gagal!");
                        window.location.reload();
                    },
                    onClose: function(){
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = 'Bayar Sekarang';
                        }
                    }
                });
            } else {
                alert('Gagal mengambil token pembayaran.');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = 'Bayar Sekarang';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (button) {
                button.disabled = false;
                button.innerHTML = 'Bayar Sekarang';
            }
        });
    }

    function payPurchase(orderId, button) {
        if (button) {
            button.disabled = true;
            button.innerHTML = 'Memproses...';
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
                        alert("Menunggu pembayaran Anda!");
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Pembayaran gagal!");
                        window.location.reload();
                    },
                    onClose: function(){
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = 'Bayar Sekarang';
                        }
                    }
                });
            } else {
                alert('Gagal mengambil token pembayaran.');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = 'Bayar Sekarang';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (button) {
                button.disabled = false;
                button.innerHTML = 'Bayar Sekarang';
            }
        });
    }
</script>
@endpush
@endsection