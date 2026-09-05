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
    <!-- Elegant Toast -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-md border shadow-xl flex items-center gap-3 text-sm font-semibold max-w-sm"
         :class="toast.type === 'success' ? 'bg-slate-900 border-slate-700 text-white' : 'bg-rose-950 border-rose-800 text-rose-200'"
         style="display: none;">
        <span x-text="toast.type === 'success' ? '✓' : '✗'" class="text-base font-bold"></span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 mb-6">
        <div>
            <p class="text-neon text-[11px] font-semibold uppercase tracking-wider mb-1">Seller Area</p>
            <h1 class="text-xl sm:text-2xl font-bold text-white uppercase tracking-tight">
                Kelola <span class="text-neon">Marketplace</span>
            </h1>
        </div>
        <a href="{{ route('marketplace.seller.products.create') }}" 
           class="w-full sm:w-auto px-4 py-2 rounded-md bg-neon hover:bg-white text-slate-950 font-bold transition-all text-xs uppercase tracking-wider shadow-sm text-center">
            <span>+ Tambah Produk</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-slate-900 border border-slate-700 text-slate-200 p-3 rounded-md mb-5 flex items-center gap-2 text-xs">
            <span class="font-bold text-neon">✓</span>
            {{ session('success') }}
        </div>
    @endif

    <!-- Summary Stats Banner -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 mb-6">
        <div class="bg-slate-900 border border-slate-800 p-3 sm:p-4 rounded-lg">
            <div class="text-[11px] text-slate-400 font-medium mb-1">Total Produk</div>
            <div class="text-base sm:text-lg font-bold text-white">{{ number_format($totalProductsCount ?? 0) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-3 sm:p-4 rounded-lg">
            <div class="text-[11px] text-slate-400 font-medium mb-1">Produk Aktif</div>
            <div class="text-base sm:text-lg font-bold text-neon">{{ number_format($activeProductsCount ?? 0) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-3 sm:p-4 rounded-lg">
            <div class="text-[11px] text-slate-400 font-medium mb-1">Produk Terjual</div>
            <div class="text-base sm:text-lg font-bold text-white">{{ number_format($soldProductsCount ?? 0) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-3 sm:p-4 rounded-lg">
            <div class="text-[11px] text-slate-400 font-medium mb-1">Total Views</div>
            <div class="text-base sm:text-lg font-bold text-white flex items-center gap-1">
                <span>{{ number_format($totalViewsCount ?? 0) }}</span>
                <span class="text-[11px] font-normal text-slate-400">kali</span>
            </div>
        </div>
    </div>

    <!-- Tab Switchers -->
    <div class="flex border-b border-slate-800 mb-5 gap-1 sm:gap-3 overflow-x-auto scrollbar-none">
        <button @click="activeTab = 'products'" 
                class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider border-b-2 transition-all duration-200 whitespace-nowrap"
                :class="activeTab === 'products' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Produk Saya ({{ $totalProductsCount ?? $products->total() }})
        </button>
        <button @click="activeTab = 'orders'" 
                class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider border-b-2 transition-all duration-200 whitespace-nowrap"
                :class="activeTab === 'orders' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Pesanan Masuk ({{ $activeOrders->count() }})
        </button>
        <button @click="activeTab = 'history'" 
                class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider border-b-2 transition-all duration-200 whitespace-nowrap"
                :class="activeTab === 'history' ? 'border-neon text-neon' : 'border-transparent text-slate-400 hover:text-white'">
            Riwayat Penjualan ({{ $salesHistory->count() }})
        </button>
    </div>

    <!-- TABS CONTAINER -->
    <div class="space-y-6">

        <!-- TAB 1: PRODUCTS LIST -->
        <div x-show="activeTab === 'products'">

            <!-- Sub-Filter Pills for Products -->
            <div class="flex flex-wrap items-center gap-1.5 mb-4">
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'all']) }}"
                   class="px-3 py-1 rounded-md text-[11px] font-semibold transition-all border {{ ($productFilter ?? 'all') === 'all' ? 'bg-neon text-dark border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                    Semua ({{ $totalProductsCount ?? 0 }})
                </a>
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'active']) }}"
                   class="px-3 py-1 rounded-md text-[11px] font-semibold transition-all border {{ ($productFilter ?? '') === 'active' ? 'bg-neon text-dark border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                    Aktif ({{ $activeProductsCount ?? 0 }})
                </a>
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'sold']) }}"
                   class="px-3 py-1 rounded-md text-[11px] font-semibold transition-all border {{ ($productFilter ?? '') === 'sold' ? 'bg-slate-800 text-white border-slate-600' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                    Terjual ({{ $soldProductsCount ?? 0 }})
                </a>
                <a href="{{ route('marketplace.seller.products.index', ['product_filter' => 'archived']) }}"
                   class="px-3 py-1 rounded-md text-[11px] font-semibold transition-all border {{ ($productFilter ?? '') === 'archived' ? 'bg-amber-500 text-dark border-amber-400' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700' }}">
                    Diarsipkan ({{ $archivedProductsCount ?? 0 }})
                </a>
            </div>

            <div class="bg-slate-900/60 border border-slate-800 rounded-lg overflow-hidden shadow-sm">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-slate-950/60">
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Produk</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Harga</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Status & Info</th>
                                <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 bg-transparent">
                            @forelse($products as $product)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden border border-slate-800 bg-slate-950 flex items-center justify-center relative">
                                            @if($product->primaryImage)
                                                <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" />
                                            @else
                                                <div class="text-[10px] text-slate-600 font-bold">NO IMG</div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-white font-bold text-sm tracking-tight leading-snug">{{ $product->title }}</p>
                                            <p class="text-slate-400 text-xs mt-0.5 uppercase tracking-wider font-semibold">{{ $product->type }}</p>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @if($product->approval_status === 'pending')
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-300">
                                                        Menunggu Review
                                                    </span>
                                                @elseif($product->approval_status === 'rejected')
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/20 border border-rose-500/40 text-rose-300" title="{{ $product->rejection_reason }}">
                                                        Ditolak Admin
                                                    </span>
                                                @endif
                                                @if($product->is_sold)
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-200">
                                                        SOLD
                                                    </span>
                                                @endif
                                                @if($product->is_archived)
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-300">
                                                        DIARSIPKAN
                                                    </span>
                                                @endif
                                                @if($product->isFeaturedActive())
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-neon/20 border border-neon/40 text-neon">
                                                        Featured s/d {{ $product->featured_until->format('d M') }}
                                                    </span>
                                                @endif
                                                @if($product->sale_type === 'auction')
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-300">Lelang</span>
                                                @endif
                                                @if($product->fulfillment_mode === 'consignment')
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-white text-slate-950">Titip Jual</span>
                                                @endif
                                                @if(!$product->is_active && !$product->is_sold && !$product->is_archived)
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/10 border border-rose-500/20 text-rose-400">Hidden</span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 text-[9px] text-slate-300 font-bold bg-slate-950 border border-slate-800 px-2 py-0.5 rounded" title="Total Dilihat Calon Pembeli">
                                                    {{ number_format($product->views_count ?? 0) }} Views
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-white font-bold text-sm">
                                        Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                                    </p>
                                    @if($product->sale_type === 'auction' && $product->auction_end_at)
                                        <p class="text-slate-400 text-xs mt-1">End: {{ $product->auction_end_at->format('d M H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($product->is_sold)
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-slate-800 text-slate-200 border border-slate-700">
                                                Terjual via {{ strtoupper($product->sold_channel ?: 'RuangLari') }}
                                            </span>
                                            @if($product->sold_to_buyer_name)
                                                <div class="text-xs text-slate-300 font-medium">Pembeli: <span class="text-white font-bold">{{ $product->sold_to_buyer_name }}</span></div>
                                            @endif
                                            @if($product->sold_channel_note)
                                                <div class="text-xs text-slate-400">"{{ $product->sold_channel_note }}"</div>
                                            @endif
                                        </div>
                                    @elseif($product->is_archived)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-amber-500/10 text-amber-300 border border-amber-500/25">
                                            Archived
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $product->stock > 0 ? 'bg-slate-800 text-slate-200 border border-slate-700' : 'bg-rose-500/10 text-rose-300 border border-rose-500/25' }}">
                                            {{ $product->stock }} Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-xs">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(!$product->is_sold && !$product->is_archived && $product->is_active)
                                            <button @click="boostProduct({{ $product->id }})" class="px-2.5 py-1 rounded-md bg-slate-950 border border-slate-800 text-slate-300 hover:text-neon hover:border-neon/40 font-semibold transition-all" title="Boost ke Peringkat Teratas">
                                                Boost
                                            </button>

                                            <button @click="requestFeatured({{ $product->id }})" class="px-2.5 py-1 rounded-md {{ $product->isFeaturedActive() ? 'bg-neon/20 border-neon/40 text-neon' : 'bg-slate-950 border-slate-800 text-slate-300 hover:text-amber-300 hover:border-amber-400/40' }} font-semibold transition-all border" title="Pasang Featured Product 3 Hari (Rp 25.000)">
                                                {{ $product->isFeaturedActive() ? 'Featured' : '+Featured' }}
                                            </button>
                                        @endif

                                        @if($product->is_sold)
                                            <button @click="markUnsold({{ $product->id }})" class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-300 hover:text-white font-semibold transition-all" title="Batalkan Status Terjual">
                                                Batal Sold
                                            </button>
                                        @else
                                            <button @click="openSoldModal({{ json_encode($product) }})" class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:bg-white hover:text-slate-950 font-semibold transition-all" title="Tandai Terjual (Sold)">
                                                Sold
                                            </button>
                                        @endif

                                        <button @click="toggleArchive({{ $product->id }})" class="px-2.5 py-1 rounded-md border font-semibold transition-all {{ $product->is_archived ? 'bg-amber-500/20 border-amber-500/40 text-amber-300 hover:bg-amber-500/30' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-amber-300 hover:border-amber-500/30' }}">
                                            {{ $product->is_archived ? 'Unarchive' : 'Arsip' }}
                                        </button>

                                        <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" class="px-2.5 py-1 rounded-md bg-slate-950 border border-slate-800 text-slate-300 hover:text-neon transition-colors font-semibold">
                                            Lihat
                                        </a>

                                        <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="px-2.5 py-1 rounded-md bg-slate-950 border border-slate-800 text-slate-300 hover:text-white transition-colors font-semibold">
                                            Edit
                                        </a>

                                        <form action="{{ route('marketplace.seller.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-md bg-slate-950 border border-slate-800 text-rose-400 hover:bg-rose-500/20 transition-colors font-semibold">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-xs uppercase tracking-wider font-medium">
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
                    <div class="p-3.5 space-y-3">
                        <div class="flex gap-2.5">
                            <div class="w-14 h-14 rounded-md overflow-hidden border border-slate-800 bg-slate-950 flex-shrink-0 flex items-center justify-center">
                                @if($product->primaryImage)
                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" />
                                @else
                                    <span class="text-[9px] text-slate-600 font-bold">NO IMG</span>
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <h3 class="text-white font-bold text-xs leading-snug line-clamp-2">{{ $product->title }}</h3>
                                <p class="text-slate-400 text-[11px] uppercase font-medium mt-0.5">{{ $product->type }}</p>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @if($product->approval_status === 'pending')
                                        <span class="text-[8px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/30 text-amber-300">Review</span>
                                    @elseif($product->approval_status === 'rejected')
                                        <span class="text-[8px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-500/20 border border-rose-500/30 text-rose-300">Ditolak</span>
                                    @endif
                                    @if($product->is_sold)
                                        <span class="text-[8px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-200">SOLD</span>
                                    @endif
                                    @if($product->is_archived)
                                        <span class="text-[8px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 border border-amber-500/30 text-amber-300">ARSIP</span>
                                    @endif
                                    @if($product->isFeaturedActive())
                                        <span class="text-[8px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-neon/20 border border-neon/30 text-neon">FEATURED</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Price & Stock Row -->
                        <div class="flex justify-between items-center pt-2 border-t border-slate-800/80">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">HARGA</span>
                                <span class="text-xs sm:text-sm font-bold text-[#B8FF00]">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-medium">STATUS</span>
                                <span class="text-[11px] font-semibold text-slate-300">
                                    @if($product->is_sold)
                                        Terjual
                                    @else
                                        {{ $product->stock }} Stok &bull; {{ number_format($product->views_count ?? 0) }} Views
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Structured Action Buttons Grid (No Icons, Clean & Compact for 360px) -->
                        <div class="grid grid-cols-3 gap-1.5 pt-2 border-t border-slate-800/80">
                            <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" 
                               class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border text-slate-200 bg-slate-950 border-slate-800 hover:border-slate-700">
                                Edit
                            </a>
                            <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" 
                               class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border text-slate-200 bg-slate-950 border-slate-800 hover:border-slate-700">
                                Lihat
                            </a>
                            @if($product->is_sold)
                                <button @click="markUnsold({{ $product->id }})" 
                                        class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border text-slate-300 bg-slate-800 border-slate-700 hover:text-white">
                                    Batal Sold
                                </button>
                            @else
                                <button @click="openSoldModal({{ json_encode($product) }})" 
                                        class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border text-slate-200 bg-slate-800 border-slate-700 hover:bg-white hover:text-slate-950">
                                    Sold
                                </button>
                            @endif
                            <button @click="toggleArchive({{ $product->id }})" 
                                    class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border {{ $product->is_archived ? 'text-amber-300 bg-amber-500/10 border-amber-500/30' : 'text-slate-300 bg-slate-950 border-slate-800 hover:border-slate-700' }}">
                                {{ $product->is_archived ? 'Unarchive' : 'Arsip' }}
                            </button>
                            @if(!$product->is_sold && !$product->is_archived && $product->is_active)
                                <button @click="boostProduct({{ $product->id }})" 
                                        class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border text-neon bg-slate-950 border-slate-800 hover:border-neon/40">
                                    Boost
                                </button>
                                <button @click="requestFeatured({{ $product->id }})" 
                                        class="py-1.5 px-2 text-center text-[11px] font-semibold rounded-md border {{ $product->isFeaturedActive() ? 'text-neon bg-neon/10 border-neon/30' : 'text-amber-300 bg-slate-950 border-slate-800 hover:border-amber-400/40' }}">
                                    {{ $product->isFeaturedActive() ? 'Featured' : '+Featured' }}
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs uppercase tracking-wider font-medium">
                        Belum ada produk ditemukan.
                    </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                <div class="px-4 py-3 border-t border-slate-800 bg-slate-950/25">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: INCOMING ORDERS -->
        <div x-show="activeTab === 'orders'" style="display: none;">
            <div class="bg-slate-900/60 border border-slate-800 rounded-lg overflow-hidden shadow-sm p-4 md:p-6">
                <h2 class="text-sm sm:text-base font-bold text-white mb-3">
                    Pesanan Masuk Aktif
                </h2>

                <div class="space-y-3">
                    @forelse($activeOrders as $order)
                    <div class="border border-slate-800 rounded-lg bg-slate-950/30 overflow-hidden" x-data="{ trackingNo: '' }">
                        <div class="bg-slate-950/60 px-3.5 py-2.5 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-medium">INVOICE</span>
                                <span class="text-xs font-bold text-white ml-2">#{{ $order->order_number }}</span>
                                <span class="text-xs text-slate-400 ml-2">({{ $order->created_at->format('d M Y H:i') }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-slate-800 border border-slate-700 text-slate-200">
                                    {{ $order->status }}
                                </span>
                                <span class="text-xs font-bold text-[#B8FF00]">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3.5 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-md overflow-hidden border border-slate-800 bg-slate-950 shrink-0">
                                    @if($order->items->first() && $order->items->first()->product && $order->items->first()->product->primaryImage)
                                        <img class="w-full h-full object-cover" src="{{ asset('storage/' . $order->items->first()->product->primaryImage->image_path) }}" alt="" />
                                    @endif
                                </div>
                                <div>
                                    <p class="text-white font-bold text-xs">{{ $order->items->first()->product_name ?? 'Produk' }}</p>
                                    <p class="text-[11px] text-slate-400">Pembeli: <span class="text-slate-200 font-medium">{{ $order->buyer->name ?? 'Buyer' }}</span></p>
                                </div>
                            </div>
                            @if($order->status === 'paid')
                                <div class="pt-2 border-t border-slate-800 flex flex-col sm:flex-row gap-2 items-center">
                                    <input type="text" x-model="trackingNo" placeholder="Nomor Resi Pengiriman (Opsional)" class="w-full sm:flex-1 bg-slate-950 border border-slate-700 rounded-md px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:border-neon outline-none">
                                    <button @click="processOrder({{ $order->id }}, trackingNo)" class="w-full sm:w-auto px-4 py-1.5 bg-neon text-dark font-bold rounded-md text-xs uppercase tracking-wider hover:bg-lime-400 transition-all">
                                        Proses & Kirim
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs uppercase tracking-wider">
                        Belum ada pesanan masuk aktif.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 3: SALES HISTORY -->
        <div x-show="activeTab === 'history'" style="display: none;">
            <div class="bg-slate-900/60 border border-slate-800 rounded-lg overflow-hidden shadow-sm p-4 md:p-6">
                <h2 class="text-sm sm:text-base font-bold text-white mb-3">Riwayat Penjualan Selesai</h2>
                <div class="space-y-2.5">
                    @forelse($salesHistory as $order)
                    <div class="p-3.5 border border-slate-800/80 rounded-lg bg-slate-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div>
                            <span class="text-xs font-bold text-white">#{{ $order->order_number }}</span>
                            <span class="text-xs text-slate-400 ml-2 font-medium">Pembeli: {{ $order->buyer->name ?? 'Buyer' }}</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-bold text-slate-200">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs uppercase tracking-wider">
                        Belum ada riwayat penjualan.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- MARK AS SOLD MODAL -->
    <div x-show="showSoldModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 w-full max-w-md shadow-2xl relative" @click.away="showSoldModal = false">
            <div class="flex items-center justify-between mb-4 pb-2.5 border-b border-slate-800">
                <h3 class="text-sm sm:text-base font-bold text-white uppercase tracking-tight">Tandai Produk Terjual (Sold)</h3>
                <button @click="showSoldModal = false" class="text-slate-400 hover:text-white text-base">&times;</button>
            </div>

            <template x-if="selectedProduct">
                <div class="mb-4 bg-slate-950 p-2.5 rounded-md border border-slate-800 flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center shrink-0 overflow-hidden text-[10px] text-neon font-bold">
                        GEAR
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] text-slate-400 font-medium">PRODUK</p>
                        <p class="text-xs font-bold text-white truncate" x-text="selectedProduct.title"></p>
                    </div>
                </div>
            </template>

            <form @submit.prevent="submitMarkSold" class="space-y-3.5">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Terjual Dimana? (Saluran Penjualan)</label>
                    <select x-model="soldForm.sold_channel" required class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs text-white focus:border-neon outline-none">
                        <option value="ruanglari">RuangLari Marketplace</option>
                        <option value="tokopedia">Tokopedia</option>
                        <option value="shopee">Shopee</option>
                        <option value="instagram_wa">Instagram / WhatsApp Direct</option>
                        <option value="cod_offline">COD / Event Lari Offline</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Catatan / Detail Lokasi (Opsional)</label>
                    <input type="text" x-model="soldForm.sold_channel_note" placeholder="Contoh: Tokopedia Store / COD GBK Senayan" class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-neon outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Dijual Kepada Siapa? (Nama Pembeli - Opsional)</label>
                    <input type="text" x-model="soldForm.sold_to_buyer_name" placeholder="Contoh: Budi Santoso (@budi_runner)" class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-neon outline-none">
                </div>

                <div class="pt-2 flex gap-2.5">
                    <button type="button" @click="showSoldModal = false" class="flex-1 py-2 rounded-md bg-slate-800 text-slate-300 font-semibold text-xs uppercase hover:bg-slate-700 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-md bg-neon text-dark font-bold text-xs uppercase hover:bg-white transition-all shadow-sm">
                        Simpan Status Sold
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
