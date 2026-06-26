@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Seller Dashboard - Marketplace')

@section('content')
<div class="min-h-screen pt-24 pb-12 px-4 md:px-8 font-sans" x-data="{
    activeTab: 'products',
    toast: { show: false, message: '', type: 'success' },
    showToast(msg, type = 'success') {
        this.toast.message = msg;
        this.toast.type = type;
        this.toast.show = true;
        setTimeout(() => { this.toast.show = false; }, 3000);
    },
    async processOrder(orderId, trackingNo) {
        try {
            let res = await fetch(`/marketplace/seller/orders/${orderId}/process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tracking_number: trackingNo })
            });
            let data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                this.showToast(data.message || 'Gagal memproses order', 'error');
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
    },
    async cancelOrder(orderId) {
        if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok produk akan dikembalikan.')) return;
        try {
            let res = await fetch(`/marketplace/seller/orders/${orderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            let data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                this.showToast(data.message || 'Gagal membatalkan order', 'error');
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
    }
}">
    <!-- Elegant Glassmorphic Toast -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl border backdrop-blur-md shadow-2xl flex items-center gap-3 text-sm font-semibold max-w-sm"
         :class="toast.type === 'success' ? 'bg-emerald-950/90 border-emerald-500/30 text-emerald-400' : 'bg-red-950/90 border-red-500/30 text-red-400'"
         style="display: none;">
        <span x-text="toast.type === 'success' ? '✓' : '✗'" class="text-base font-bold"></span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 mb-8">
        <div>
            <p class="text-neon font-mono text-[10px] tracking-widest uppercase mb-1.5 font-bold">Seller Area</p>
            <h1 class="text-3xl font-black text-white italic tracking-tighter uppercase">
                Manage <span class="text-neon">Marketplace</span>
            </h1>
        </div>
        <a href="{{ route('marketplace.seller.products.create') }}" 
           class="w-full sm:w-auto px-5 py-3 rounded-xl bg-neon text-dark font-black hover:bg-white hover:text-dark transition-all duration-300 shadow-lg shadow-neon/15 flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
            <svg class="w-4 h-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Product
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Cyberpunk Tab Switchers -->
    <div class="flex border-b border-slate-800 mb-6 gap-2 md:gap-4 overflow-x-auto scrollbar-none">
        <button @click="activeTab = 'products'" 
                class="px-5 py-3 text-xs md:text-sm font-bold tracking-wider uppercase border-b-2 transition-all duration-200 whitespace-nowrap"
                :class="activeTab === 'products' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Produk Saya ({{ $products->total() }})
        </button>
        <button @click="activeTab = 'orders'" 
                class="px-5 py-3 text-xs md:text-sm font-bold tracking-wider uppercase border-b-2 transition-all duration-200 whitespace-nowrap flex items-center gap-2"
                :class="activeTab === 'orders' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Pesanan Masuk
            @if($activeOrders->count() > 0)
                <span class="bg-neon text-dark font-mono text-[9px] px-1.5 py-0.5 rounded font-black">{{ $activeOrders->count() }}</span>
            @endif
        </button>
        <button @click="activeTab = 'history'" 
                class="px-5 py-3 text-xs md:text-sm font-bold tracking-wider uppercase border-b-2 transition-all duration-200 whitespace-nowrap"
                :class="activeTab === 'history' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Riwayat Penjualan ({{ $salesHistory->count() }})
        </button>
    </div>

    <!-- TABS CONTAINER -->
    <div class="space-y-6">

        <!-- TAB 1: PRODUCTS LIST -->
        <div x-show="activeTab === 'products'">
            <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850/80 rounded-2xl overflow-hidden shadow-xl">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-slate-950/60">
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Product</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Price</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Stock Status</th>
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 bg-transparent">
                            @forelse($products as $product)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-xl overflow-hidden border border-slate-800 bg-slate-950 flex items-center justify-center">
                                            @if($product->primaryImage)
                                                <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" />
                                            @else
                                                <svg class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-white font-bold text-sm tracking-tight leading-snug">{{ $product->title }}</p>
                                            <p class="text-slate-500 text-[10px] mt-0.5 uppercase tracking-wider font-mono font-semibold">{{ $product->type }}</p>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @if($product->sale_type === 'auction')
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400">Lelang</span>
                                                @endif
                                                @if($product->fulfillment_mode === 'consignment')
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-cyan-500/10 border border-cyan-400/20 text-cyan-400">Titip Jual</span>
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-850 text-slate-400">{{ $product->consignment_status }}</span>
                                                @endif
                                                @if(!$product->is_active)
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/10 border border-rose-500/20 text-rose-400">Hidden</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-white font-black font-mono text-sm">
                                        Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                                    </p>
                                    @if($product->sale_type === 'auction' && $product->auction_end_at)
                                        <p class="text-slate-500 text-[10px] mt-1 font-mono">End: {{ $product->auction_end_at->format('d M H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($product->fulfillment_mode === 'consignment' && $product->consignment_status === 'requested')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-yellow-500/10 text-yellow-400 border border-yellow-500/25">
                                            Pending Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $product->stock > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/25' : 'bg-rose-500/10 text-rose-400 border border-rose-500/25' }}">
                                            {{ $product->stock }} Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" class="p-1.5 rounded bg-slate-950 border border-slate-800 text-slate-400 hover:text-neon transition-colors" title="View Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="p-1.5 rounded bg-slate-950 border border-slate-800 text-slate-400 hover:text-neon transition-colors" title="Edit Product">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <form action="{{ route('marketplace.seller.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded bg-slate-950 border border-slate-800 text-slate-400 hover:text-rose-500 transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-xs uppercase tracking-wider">
                                    Belum ada produk yang diposting.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="block md:hidden divide-y divide-slate-800/60">
                    @forelse($products as $product)
                    <div class="p-4 space-y-4">
                        <div class="flex gap-3">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border border-slate-800 bg-slate-950 flex-shrink-0 flex items-center justify-center">
                                @if($product->primaryImage)
                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" />
                                @else
                                    <svg class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <h3 class="text-white font-bold text-sm truncate leading-snug">{{ $product->title }}</h3>
                                <p class="text-slate-500 text-[10px] uppercase font-mono font-semibold mt-0.5">{{ $product->type }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @if($product->sale_type === 'auction')
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400">Lelang</span>
                                    @endif
                                    @if($product->fulfillment_mode === 'consignment')
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-cyan-500/10 border border-cyan-400/20 text-cyan-400">Titip Jual</span>
                                    @endif
                                    @if(!$product->is_active)
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/10 border border-rose-500/20 text-rose-400">Hidden</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-850/40">
                            <div>
                                <span class="text-[10px] text-slate-550 block font-mono">HARGA</span>
                                <span class="text-white font-black font-mono text-sm">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-555 block font-mono text-right">STOK</span>
                                <span class="text-xs font-bold text-slate-300">
                                    {{ $product->stock }} Stock
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" class="flex-1 text-center bg-slate-950 border border-slate-800 text-slate-300 font-bold py-2 rounded-xl text-xs hover:text-white transition-all">Lihat</a>
                            <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="flex-1 text-center bg-slate-950 border border-slate-850 text-neon font-bold py-2 rounded-xl text-xs hover:bg-neon hover:text-dark transition-all">Edit</a>
                            <form action="{{ route('marketplace.seller.products.destroy', $product->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-center bg-rose-950/20 border border-rose-900/30 text-rose-400 font-bold py-2 rounded-xl text-xs hover:bg-rose-500 hover:text-white transition-all">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 text-xs uppercase tracking-wider">
                        Belum ada produk yang diposting.
                    </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/25">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: INCOMING ORDERS -->
        <div x-show="activeTab === 'orders'" style="display: none;">
            <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850/80 rounded-2xl overflow-hidden shadow-xl p-4 md:p-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-neon animate-pulse"></span>
                    Pesanan Masuk Aktif
                </h2>

                <div class="space-y-4">
                    @forelse($activeOrders as $order)
                    <div class="border border-slate-800 rounded-xl bg-slate-950/30 overflow-hidden" x-data="{ trackingNo: '' }">
                        <!-- Card Header -->
                        <div class="bg-slate-950/60 px-4 py-3 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-mono text-slate-500 uppercase">INVOICE</span>
                                <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-1.5">
                                    {{ $order->invoice_number }}
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded font-black uppercase tracking-wider
                                        @if($order->status === 'paid') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($order->status === 'shipped') bg-cyan-500/10 text-cyan-400 border border-cyan-500/20
                                        @elseif($order->status === 'pending') bg-yellow-500/10 text-yellow-400 border border-yellow-500/20
                                        @else bg-slate-800 text-slate-400 @endif">
                                        {{ $order->status }}
                                    </span>
                                </h3>
                            </div>
                            <div class="text-left md:text-right">
                                <span class="text-[10px] font-mono text-slate-550 block uppercase">WAKTU PEMBELIAN</span>
                                <span class="text-xs text-slate-300 font-mono">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <!-- Buyer & Items Details -->
                            <div class="space-y-3 flex-grow">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-semibold">Pembeli:</span>
                                    <span class="text-xs text-white font-bold">{{ $order->buyer->name }}</span>
                                    <span class="text-slate-600">|</span>
                                    <span class="text-xs text-slate-450 font-mono">{{ $order->buyer->email }}</span>
                                </div>
                                <div class="space-y-2 border-t border-slate-850/50 pt-2">
                                    @foreach($order->items as $item)
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg border border-slate-850 overflow-hidden bg-slate-950 flex-shrink-0 flex items-center justify-center">
                                                @if($item->product && $item->product->primaryImage)
                                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" alt="" />
                                                @else
                                                    <svg class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-white leading-tight">{{ $item->product_title_snapshot }}</p>
                                                <p class="text-[10px] text-slate-500 font-mono">{{ $item->quantity }}x @ Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Payment & Actions Panel -->
                            <div class="flex flex-col sm:flex-row lg:flex-col justify-end items-start sm:items-center lg:items-end gap-4 border-t lg:border-t-0 border-slate-850 pt-4 lg:pt-0 shrink-0">
                                <div class="text-left lg:text-right">
                                    <span class="text-[10px] text-slate-500 block font-mono">TOTAL PENDAPATAN</span>
                                    <span class="text-neon font-black font-mono text-base">
                                        Rp {{ number_format($order->seller_amount, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($order->status === 'paid' || $order->status === 'pending')
                                        <div class="flex items-center gap-2 bg-slate-950 border border-slate-800 rounded-xl p-1">
                                            <input type="text" x-model="trackingNo" placeholder="Nomor Resi (Opsional)" 
                                                   class="bg-transparent text-white px-3 py-1.5 text-xs focus:outline-none w-36 placeholder-slate-600">
                                            <button @click="processOrder({{ $order->id }}, trackingNo)" 
                                                    class="bg-neon text-dark font-black px-4 py-1.5 rounded-lg text-xs hover:bg-white transition-all uppercase tracking-wider">
                                                Proses & Kirim
                                            </button>
                                        </div>
                                        <button @click="cancelOrder({{ $order->id }})" 
                                                class="px-4 py-2 border border-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl text-xs font-bold transition-all uppercase tracking-wider">
                                            Batalkan
                                        </button>
                                    @elseif($order->status === 'shipped')
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-550 block font-mono">RESI PENGIRIMAN</span>
                                            <span class="text-xs text-cyan-400 font-mono font-bold">{{ $order->shipping_tracking_number ?: '-' }}</span>
                                            <p class="text-[10px] text-slate-500 mt-1 italic">Menunggu konfirmasi penerimaan pembeli</p>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500 italic">Tidak ada aksi tersedia</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 border border-dashed border-slate-800 rounded-xl">
                        <svg class="w-8 h-8 text-slate-650 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <p class="text-slate-500 text-xs uppercase tracking-wider">Tidak ada pesanan masuk aktif saat ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 3: SALES HISTORY -->
        <div x-show="activeTab === 'history'" style="display: none;">
            <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850/80 rounded-2xl overflow-hidden shadow-xl p-4 md:p-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                    Riwayat Transaksi Penjualan
                </h2>

                <div class="space-y-4">
                    @forelse($salesHistory as $order)
                    <div class="border border-slate-800/80 rounded-xl bg-slate-950/20 overflow-hidden">
                        <div class="bg-slate-950/40 px-4 py-3 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-mono text-slate-500 uppercase">INVOICE</span>
                                <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                                    {{ $order->invoice_number }}
                                    <span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase tracking-wider
                                        @if($order->status === 'completed') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @else bg-rose-500/10 text-rose-400 border border-rose-500/20 @endif">
                                        {{ $order->status }}
                                    </span>
                                </h3>
                            </div>
                            <div class="text-left md:text-right font-mono">
                                <span class="text-[10px] text-slate-500 block uppercase">SELESAI PADA</span>
                                <span class="text-xs text-slate-400">{{ $order->updated_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="p-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="space-y-1.5 flex-grow">
                                <p class="text-xs text-slate-400">Pembeli: <span class="text-white font-bold">{{ $order->buyer->name }}</span></p>
                                <div class="space-y-1 pl-1">
                                    @foreach($order->items as $item)
                                    <p class="text-xs text-slate-300 font-medium">
                                        {{ $item->product_title_snapshot }} <span class="text-slate-500 text-[10px] font-mono">({{ $item->quantity }}x)</span>
                                    </p>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-left md:text-right shrink-0">
                                <span class="text-[10px] text-slate-500 block font-mono">PENDAPATAN BERSIH</span>
                                <span class="text-white font-bold font-mono text-sm">
                                    Rp {{ number_format($order->seller_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 border border-dashed border-slate-800 rounded-xl">
                        <p class="text-slate-500 text-xs uppercase tracking-wider">Belum ada riwayat transaksi penjualan.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
