@extends('layouts.pacerhub')

@section('title', 'Seller Dashboard - Marketplace')

@section('content')
@php
    $withSidebar = true;
@endphp
<div class="min-h-screen pt-24 pb-12 px-4 md:px-8 font-sans" x-data="{
    activeTab: 'products',
    toast: { show: false, message: '', type: 'success' },
    showSoldModal: false,
    selectedProduct: null,
    soldForm: {
        sold_channel: 'ruanglari',
        sold_channel_note: '',
        sold_to_buyer_name: ''
    },
    showToast(msg, type = 'success') {
        this.toast.message = msg;
        this.toast.type = type;
        this.toast.show = true;
        setTimeout(() => { this.toast.show = false; }, 3000);
    },
    openSoldModal(prod) {
        this.selectedProduct = prod;
        this.soldForm.sold_channel = 'ruanglari';
        this.soldForm.sold_channel_note = '';
        this.soldForm.sold_to_buyer_name = '';
        this.showSoldModal = true;
    },
    async submitMarkSold() {
        if (!this.selectedProduct) return;
        try {
            let res = await fetch(`/marketplace/seller/products/${this.selectedProduct.id}/mark-sold`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.soldForm)
            });
            let data = await res.json();
            if (data.success) {
                this.showSoldModal = false;
                this.showToast(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                this.showToast(data.message || 'Gagal menandai produk sold', 'error');
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
    },
    async markUnsold(productId) {
        if (!confirm('Apakah Anda yakin ingin membatalkan status Terjual (Sold) pada produk ini?')) return;
        try {
            let res = await fetch(`/marketplace/seller/products/${productId}/mark-unsold`, {
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
                setTimeout(() => location.reload(), 800);
            } else {
                this.showToast(data.message || 'Gagal mengubah status', 'error');
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
    },
    async toggleArchive(productId) {
        try {
            let res = await fetch(`/marketplace/seller/products/${productId}/toggle-archive`, {
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
                setTimeout(() => location.reload(), 800);
            } else {
                this.showToast(data.message || 'Gagal memperbarui arsip', 'error');
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
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
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
    },
    async boostProduct(productId) {
        try {
            let res = await fetch(`/marketplace/seller/products/${productId}/boost`, {
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
                setTimeout(() => location.reload(), 900);
            } else {
                this.showToast(data.message || 'Gagal melakukan boost', 'error');
            }
        } catch (e) {
            this.showToast('Koneksi bermasalah', 'error');
        }
    },
    async requestFeatured(productId) {
        if (!confirm('Jadikan produk ini sebagai Featured Product selama 3 hari (Rp 25.000)?')) return;
        try {
            let res = await fetch(`/marketplace/seller/products/${productId}/featured`, {
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
                setTimeout(() => location.reload(), 900);
            } else {
                this.showToast(data.message || 'Gagal mengaktifkan featured', 'error');
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
           class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white hover:bg-slate-200 text-slate-950 font-black transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-wider shadow-lg shadow-white/5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add Product</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Summary Stats Banner -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs text-slate-400 font-medium mb-1">Total Produk</div>
            <div class="text-xl md:text-2xl font-black text-white font-mono">{{ number_format($totalProductsCount ?? 0) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs text-slate-400 font-medium mb-1">Produk Aktif</div>
            <div class="text-xl md:text-2xl font-black text-neon font-mono">{{ number_format($activeProductsCount ?? 0) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs text-slate-400 font-medium mb-1">Produk Terjual</div>
            <div class="text-xl md:text-2xl font-black text-emerald-400 font-mono">{{ number_format($soldProductsCount ?? 0) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-2xl">
            <div class="text-xs text-slate-400 font-medium mb-1 flex items-center gap-1.5">
                <i class="fas fa-eye text-neon text-xs"></i>
                <span>Total Dilihat (Views)</span>
            </div>
            <div class="text-xl md:text-2xl font-black text-white font-mono flex items-center gap-1.5">
                <span>{{ number_format($totalViewsCount ?? 0) }}</span>
                <span class="text-xs font-sans font-bold text-slate-400">kali</span>
            </div>
        </div>
    </div>

    <!-- Cyberpunk Tab Switchers -->
    <div class="flex border-b border-slate-800 mb-6 gap-2 md:gap-4 overflow-x-auto scrollbar-none">
        <button @click="activeTab = 'products'" 
                class="px-5 py-3 text-xs md:text-sm font-bold tracking-wider uppercase border-b-2 transition-all duration-200 whitespace-nowrap"
                :class="activeTab === 'products' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Produk Saya ({{ $totalProductsCount ?? $products->total() }})
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
                :class="activeTab === 'history' ? 'border-transparent text-slate-400 hover:text-white' : 'border-transparent text-slate-400 hover:text-white'">
            Riwayat Penjualan ({{ $salesHistory->count() }})
        </button>
    </div>

    <!-- TABS CONTAINER -->
    <div class="space-y-6">

        <!-- TAB 1: PRODUCTS LIST -->
        <div x-show="activeTab === 'products'">

            <!-- Sub-Filter Pills for Products -->
            <div class="flex flex-wrap items-center gap-2 mb-5">
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'all']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border {{ ($productFilter ?? 'all') === 'all' ? 'bg-neon text-dark border-neon shadow-lg shadow-neon/15' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                    Semua ({{ $totalProductsCount ?? 0 }})
                </a>
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'active']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border {{ ($productFilter ?? '') === 'active' ? 'bg-neon text-dark border-neon shadow-lg shadow-neon/15' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                    Aktif ({{ $activeProductsCount ?? 0 }})
                </a>
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'sold']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border {{ ($productFilter ?? '') === 'sold' ? 'bg-emerald-500 text-dark border-emerald-400 shadow-lg shadow-emerald-500/15' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700 hover:text-emerald-400' }}">
                    Terjual / Sold ({{ $soldProductsCount ?? 0 }})
                </a>
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'archived']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border {{ ($productFilter ?? '') === 'archived' ? 'bg-amber-500 text-dark border-amber-400 shadow-lg shadow-amber-500/15' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700 hover:text-amber-400' }}">
                    Diarsipkan ({{ $archivedProductsCount ?? 0 }})
                </a>
            </div>

            <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850/80 rounded-2xl overflow-hidden shadow-xl">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-slate-950/60">
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Product</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Price</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Status & Sales Info</th>
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 bg-transparent">
                            @forelse($products as $product)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-xl overflow-hidden border border-slate-800 bg-slate-950 flex items-center justify-center relative">
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
                                                @if($product->approval_status === 'pending')
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-300 flex items-center gap-1">
                                                        <i class="fas fa-clock text-[8px]"></i> Menunggu Review
                                                    </span>
                                                @elseif($product->approval_status === 'rejected')
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/20 border border-rose-500/40 text-rose-300" title="{{ $product->rejection_reason }}">
                                                        Ditolak Admin
                                                    </span>
                                                @endif
                                                @if($product->is_sold)
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-500/20 border border-emerald-500/40 text-emerald-400">
                                                        ✓ SOLD (TERJUAL)
                                                    </span>
                                                @endif
                                                @if($product->is_archived)
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-400">
                                                        📁 DIARSIPKAN
                                                    </span>
                                                @endif
                                                @if($product->isFeaturedActive())
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-neon/20 border border-neon/40 text-neon flex items-center gap-1">
                                                        <i class="fas fa-bolt text-[8px]"></i> Featured s/d {{ $product->featured_until->format('d M') }}
                                                    </span>
                                                @endif
                                                @if($product->sale_type === 'auction')
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400">Lelang</span>
                                                @endif
                                                @if($product->fulfillment_mode === 'consignment')
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-white text-slate-950">Titip Jual</span>
                                                @endif
                                                @if(!$product->is_active && !$product->is_sold && !$product->is_archived)
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/10 border border-rose-500/20 text-rose-400">Hidden</span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 text-[9px] font-mono text-slate-300 font-bold bg-slate-950 border border-slate-800 px-2 py-0.5 rounded-md" title="Total Dilihat Calon Pembeli">
                                                    <i class="fas fa-eye text-neon text-[8px]"></i> {{ number_format($product->views_count ?? 0) }} Views
                                                </span>
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
                                    @if($product->is_sold)
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                <i class="fas fa-check-double text-[9px]"></i> Terjual via {{ strtoupper($product->sold_channel ?: 'RuangLari') }}
                                            </span>
                                            @if($product->sold_to_buyer_name)
                                                <div class="text-[11px] text-slate-300 font-medium">Pembeli: <span class="text-white font-bold">{{ $product->sold_to_buyer_name }}</span></div>
                                            @endif
                                            @if($product->sold_channel_note)
                                                <div class="text-[10px] text-slate-400 italic">"{{ $product->sold_channel_note }}"</div>
                                            @endif
                                        </div>
                                    @elseif($product->is_archived)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/25">
                                            Archived
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $product->stock > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/25' : 'bg-rose-500/10 text-rose-400 border border-rose-500/25' }}">
                                            {{ $product->stock }} Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- Boost & Featured Promotions -->
                                        @if(!$product->is_sold && !$product->is_archived && $product->is_active)
                                            <button @click="boostProduct({{ $product->id }})" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-neon hover:border-neon/40 text-xs font-bold transition-all flex items-center gap-1" title="Boost ke Peringkat Teratas">
                                                <i class="fas fa-rocket text-[10px] text-neon"></i>
                                                <span class="text-[10px] font-mono">Boost</span>
                                            </button>

                                            <button @click="requestFeatured({{ $product->id }})" class="p-2 rounded-xl {{ $product->isFeaturedActive() ? 'bg-neon/20 border-neon/40 text-neon' : 'bg-slate-950 border-slate-800 text-slate-300 hover:text-amber-400 hover:border-amber-400/40' }} text-xs font-bold transition-all flex items-center gap-1 border" title="Pasang Featured Product 3 Hari (Rp 25.000)">
                                                <i class="fas fa-star text-[10px] text-amber-400"></i>
                                                <span class="text-[10px] font-mono">{{ $product->isFeaturedActive() ? 'Featured' : '+Featured' }}</span>
                                            </button>
                                        @endif

                                        <!-- Mark Sold / Unsold -->
                                        @if($product->is_sold)
                                            <button @click="markUnsold({{ $product->id }})" class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 text-xs font-bold transition-all flex items-center gap-1" title="Batalkan Status Terjual">
                                                <i class="fas fa-undo text-[10px]"></i>
                                                <span class="text-[10px]">Batal Sold</span>
                                            </button>
                                        @else
                                            <button @click="openSoldModal({{ json_encode($product) }})" class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-dark text-xs font-bold transition-all flex items-center gap-1" title="Tandai Terjual (Sold)">
                                                <i class="fas fa-check-circle text-[10px]"></i>
                                                <span class="text-[10px]">Sold</span>
                                            </button>
                                        @endif

                                        <!-- Archive / Unarchive Button -->
                                        <button @click="toggleArchive({{ $product->id }})" class="p-2 rounded-xl border text-xs font-bold transition-all flex items-center gap-1 {{ $product->is_archived ? 'bg-amber-500/20 border-amber-500/40 text-amber-300 hover:bg-amber-500/30' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-amber-400 hover:border-amber-500/30' }}" title="{{ $product->is_archived ? 'Keluarkan dari Arsip' : 'Arsipkan Produk' }}">
                                            <i class="fas fa-archive text-[10px]"></i>
                                            <span class="text-[10px]">{{ $product->is_archived ? 'Unarchive' : 'Arsip' }}</span>
                                        </button>

                                        <!-- View Detail -->
                                        <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 hover:text-neon transition-colors" title="View Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 hover:text-neon transition-colors" title="Edit Product">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('marketplace.seller.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 hover:text-rose-500 transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-xs uppercase tracking-wider font-mono">
                                    Belum ada produk ditemukan.
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
                                    @if($product->approval_status === 'pending')
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/30 text-amber-300">Menunggu Review</span>
                                    @elseif($product->approval_status === 'rejected')
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/20 border border-rose-500/30 text-rose-300">Ditolak</span>
                                    @endif
                                    @if($product->is_sold)
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-500/20 border border-emerald-500/30 text-emerald-400">SOLD</span>
                                    @endif
                                    @if($product->is_archived)
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/30 text-amber-400">ARCHIVED</span>
                                    @endif
                                    @if($product->isFeaturedActive())
                                        <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-neon/20 border border-neon/30 text-neon">FEATURED</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-850/40">
                            <div>
                                <span class="text-[10px] text-slate-500 block font-mono">HARGA</span>
                                <span class="text-white font-black font-mono text-sm">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block font-mono text-right">STOK & VIEWS</span>
                                <span class="text-xs font-bold text-slate-300 block text-right">
                                    {{ $product->stock }} Stock
                                </span>
                                <span class="text-[10px] font-mono text-neon font-bold flex items-center justify-end gap-1 mt-0.5">
                                    <i class="fas fa-eye text-[9px]"></i> {{ number_format($product->views_count ?? 0) }} Views
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-1">
                            @if(!$product->is_sold && !$product->is_archived && $product->is_active)
                                <button @click="boostProduct({{ $product->id }})" class="flex-1 bg-slate-950 border border-slate-800 text-neon font-bold py-2 rounded-xl text-xs hover:bg-neon hover:text-dark transition-all">Boost</button>
                                <button @click="requestFeatured({{ $product->id }})" class="flex-1 bg-slate-950 border border-slate-800 text-amber-400 font-bold py-2 rounded-xl text-xs hover:bg-amber-400 hover:text-dark transition-all">Featured</button>
                            @endif
                            @if($product->is_sold)
                                <button @click="markUnsold({{ $product->id }})" class="flex-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold py-2 rounded-xl text-xs hover:bg-emerald-500 hover:text-dark transition-all">Batal Sold</button>
                            @else
                                <button @click="openSoldModal({{ json_encode($product) }})" class="flex-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold py-2 rounded-xl text-xs hover:bg-emerald-500 hover:text-dark transition-all">Mark Sold</button>
                            @endif
                            <button @click="toggleArchive({{ $product->id }})" class="flex-1 bg-slate-950 border border-slate-800 text-amber-400 font-bold py-2 rounded-xl text-xs hover:bg-amber-500 hover:text-dark transition-all">{{ $product->is_archived ? 'Unarchive' : 'Arsip' }}</button>
                            <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="flex-1 text-center bg-slate-950 border border-slate-850 text-white font-bold py-2 rounded-xl text-xs hover:bg-slate-800 transition-all">Edit</a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 text-xs uppercase tracking-wider font-mono">
                        Belum ada produk ditemukan.
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
                        <div class="bg-slate-950/60 px-4 py-3 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-mono text-slate-500 uppercase">INVOICE</span>
                                <span class="text-xs font-bold text-white ml-2 font-mono">#{{ $order->order_number }}</span>
                                <span class="text-[10px] text-slate-400 ml-2">({{ $order->created_at->format('d M Y H:i') }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                                    Status: {{ $order->status }}
                                </span>
                                <span class="text-xs font-black text-neon font-mono">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-800 bg-slate-950 shrink-0">
                                    @if($order->items->first() && $order->items->first()->product && $order->items->first()->product->primaryImage)
                                        <img class="w-full h-full object-cover" src="{{ asset('storage/' . $order->items->first()->product->primaryImage->image_path) }}" alt="" />
                                    @endif
                                </div>
                                <div>
                                    <p class="text-white font-bold text-xs">{{ $order->items->first()->product_name ?? 'Produk' }}</p>
                                    <p class="text-[11px] text-slate-400">Pembeli: <span class="text-slate-200 font-bold">{{ $order->buyer->name ?? 'Buyer' }}</span></p>
                                </div>
                            </div>
                            @if($order->status === 'paid')
                                <div class="pt-2 border-t border-slate-800 flex flex-col sm:flex-row gap-2 items-center">
                                    <input type="text" x-model="trackingNo" placeholder="Nomor Resi Pengiriman (Opsional)" class="w-full sm:flex-1 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-neon outline-none">
                                    <button @click="processOrder({{ $order->id }}, trackingNo)" class="w-full sm:w-auto px-4 py-2 bg-neon text-dark font-black rounded-xl text-xs uppercase tracking-wider hover:bg-lime-400 transition-all">
                                        Proses & Kirim
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 text-xs uppercase tracking-wider">
                        Belum ada pesanan masuk aktif.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 3: SALES HISTORY -->
        <div x-show="activeTab === 'history'" style="display: none;">
            <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850/80 rounded-2xl overflow-hidden shadow-xl p-4 md:p-6">
                <h2 class="text-lg font-bold text-white mb-4">Riwayat Penjualan Selesai</h2>
                <div class="space-y-3">
                    @forelse($salesHistory as $order)
                    <div class="p-4 border border-slate-800/80 rounded-xl bg-slate-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold text-white font-mono">#{{ $order->order_number }}</span>
                            <span class="text-xs text-slate-400 ml-2">Pembeli: {{ $order->buyer->name ?? 'Buyer' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-black text-neon font-mono">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-500 text-xs uppercase tracking-wider">
                        Belum ada riwayat penjualan.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- MARK AS SOLD MODAL -->
    <div x-show="showSoldModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl relative" @click.away="showSoldModal = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                <h3 class="text-lg font-black text-white italic tracking-tight">TANDAI PRODUK TERJUAL (SOLD)</h3>
                <button @click="showSoldModal = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>

            <template x-if="selectedProduct">
                <div class="mb-4 bg-slate-950 p-3 rounded-2xl border border-slate-800 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center shrink-0 overflow-hidden">
                        <i class="fas fa-box text-neon"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] text-slate-400 font-mono">PRODUK</p>
                        <p class="text-xs font-bold text-white truncate" x-text="selectedProduct.title"></p>
                    </div>
                </div>
            </template>

            <form @submit.prevent="submitMarkSold" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1.5">Terjual Dimana? (Saluran Penjualan)</label>
                    <select x-model="soldForm.sold_channel" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-neon outline-none">
                        <option value="ruanglari">RuangLari Marketplace</option>
                        <option value="tokopedia">Tokopedia</option>
                        <option value="shopee">Shopee</option>
                        <option value="instagram_wa">Instagram / WhatsApp Direct</option>
                        <option value="cod_offline">COD / Event Lari Offline</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1.5">Catatan / Detail Lokasi (Opsional)</label>
                    <input type="text" x-model="soldForm.sold_channel_note" placeholder="Contoh: Tokopedia Store / COD GBK Senayan" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-neon outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1.5">Dijual Kepada Siapa? (Nama Pembeli - Opsional)</label>
                    <input type="text" x-model="soldForm.sold_to_buyer_name" placeholder="Contoh: Budi Santoso (@budi_runner)" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-neon outline-none">
                </div>

                <div class="pt-3 flex gap-3">
                    <button type="button" @click="showSoldModal = false" class="flex-1 py-3 rounded-xl bg-slate-800 text-slate-300 font-bold text-xs uppercase hover:bg-slate-700 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-neon text-dark font-black text-xs uppercase hover:bg-lime-400 transition-all shadow-lg shadow-neon/20">
                        Simpan Status Sold
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
