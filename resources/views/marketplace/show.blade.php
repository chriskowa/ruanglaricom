@extends('layouts.pacerhub')

@section('title', $product->title . ' - Marketplace Ruang Lari')
@section('meta_title', $product->title . ' - Marketplace Ruang Lari')
@section('meta_description', Str::limit(strip_tags($product->description), 160))

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 font-sans bg-dark text-slate-200" x-data="{
    activeImage: '{{ $product->primaryImage ? asset('storage/' . $product->primaryImage->image_path) : '' }}',
    qty: 1
}">

    <div class="max-w-7xl mx-auto">
        
        <!-- Breadcrumb & Back -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-neon transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Marketplace</span>
            </a>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span class="hover:text-white">{{ $product->category ? $product->category->name : 'Gear' }}</span>
                <span>/</span>
                <span class="text-slate-200 font-bold truncate max-w-[200px]">{{ $product->title }}</span>
            </div>
        </div>

        <!-- Main Product Detail Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            
            <!-- Left Column: Gallery (5 Cols) -->
            <div class="lg:col-span-6 space-y-4">
                <!-- Primary Main Display Image -->
                <div class="relative aspect-square rounded-3xl overflow-hidden border border-slate-800 bg-slate-950 shadow-2xl group">
                    @if($product->primaryImage || $product->images->count() > 0)
                        <img :src="activeImage || '{{ asset('storage/' . ($product->primaryImage->image_path ?? $product->images->first()->image_path)) }}'" 
                             alt="{{ $product->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-700">
                            <i class="fas fa-image text-4xl mb-2"></i>
                            <span class="text-xs uppercase font-mono tracking-wider">No Image</span>
                        </div>
                    @endif

                    <!-- Badges Top Left -->
                    <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-slate-950/80 backdrop-blur border border-slate-700 text-white shadow-lg">
                            {{ $product->condition === 'new' ? 'BARU' : 'BEKAS' }}
                        </span>
                        @if($product->is_sold)
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-rose-500 text-white shadow-lg shadow-rose-500/20">
                                TERJUAL (SOLD)
                            </span>
                        @endif
                        @if($product->sale_type === 'auction')
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-neon text-dark shadow-lg shadow-neon/20">
                                LELANG
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Gallery Thumbnails -->
                @if($product->images && $product->images->count() > 1)
                    <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
                        @foreach($product->images as $img)
                            <button @click="activeImage = '{{ asset('storage/' . $img->image_path) }}'" 
                                    class="w-20 h-20 rounded-2xl overflow-hidden border-2 transition-all shrink-0 bg-slate-950"
                                    :class="activeImage === '{{ asset('storage/' . $img->image_path) }}' ? 'border-neon ring-2 ring-neon/30' : 'border-slate-800 hover:border-slate-600'">
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right Column: Info & Actions (6 Cols) -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- Category, Brand & Stats View Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-neon bg-neon/10 px-2.5 py-1 rounded-lg border border-neon/20">
                            {{ $product->category ? $product->category->name : 'General' }}
                        </span>
                        @if($product->brand)
                            <span class="text-xs font-mono text-slate-400 bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-800">
                                {{ $product->brand->name }}
                            </span>
                        @endif
                    </div>

                    <!-- STATS VIEW COUNT BADGE -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-slate-300 text-xs font-mono font-bold shadow-inner" title="Total dilihat oleh calon pembeli">
                        <i class="fas fa-eye text-neon"></i>
                        <span>{{ number_format($product->views_count ?? 0) }} Dilihat</span>
                    </div>
                </div>

                <!-- Product Title -->
                <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                    {{ $product->title }}
                </h1>

                <!-- Price Box -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                    <div class="text-xs text-slate-400 font-mono uppercase tracking-wider mb-1">
                        {{ $product->sale_type === 'auction' ? 'Current Bid (Penawaran Saat Ini)' : 'Harga Produk' }}
                    </div>
                    <div class="text-3xl sm:text-4xl font-black text-neon font-mono italic tracking-tight">
                        Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                    </div>

                    @if($product->is_sold)
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold">
                            <i class="fas fa-check-circle"></i>
                            <span>Produk ini telah TERJUAL {{ $product->sold_channel ? 'via ' . strtoupper($product->sold_channel) : '' }}</span>
                        </div>
                    @elseif($product->stock > 0)
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                            <i class="fas fa-box font-normal"></i>
                            <span>Stok Tersedia ({{ $product->stock }} Item)</span>
                        </div>
                    @endif
                </div>

                <!-- Seller Profile Card -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden border border-slate-700 bg-slate-950 shrink-0">
                            <img src="{{ $product->seller->avatar ? (Str::startsWith($product->seller->avatar, 'http') ? $product->seller->avatar : '/storage/'.$product->seller->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($product->seller->name ?? 'Seller').'&background=111F35&color=B8FF00' }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-mono uppercase tracking-wider">Penjual / Runner</p>
                            <p class="text-sm font-bold text-white">{{ $product->seller->name ?? 'Seller' }}</p>
                            @if($product->seller && $product->seller->city)
                                <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-map-marker-alt text-rose-400 text-[10px]"></i>
                                    <span>{{ $product->seller->city->name }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('marketplace.seller.store', $product->seller->username ?: $product->seller->id) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                        Kunjungi Toko
                    </a>
                </div>

                <!-- Actions: Buy Now / Add to Cart -->
                @if(!$product->is_sold && $product->stock > 0)
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        @auth
                            @if(auth()->id() !== $product->user_id)
                                <form method="POST" action="{{ route('marketplace.cart.add-product', $product->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm uppercase tracking-wider border border-slate-700 transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-cart-plus text-base"></i>
                                        <span>+ Keranjang</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('marketplace.checkout.init') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="w-full py-4 rounded-2xl bg-neon hover:bg-lime-400 text-dark font-black text-sm uppercase tracking-wider shadow-lg shadow-neon/20 transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-bolt text-base"></i>
                                        <span>Beli Langsung</span>
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="w-full py-4 rounded-2xl bg-neon hover:bg-lime-400 text-dark font-black text-sm uppercase tracking-wider shadow-lg shadow-neon/20 transition-all text-center">
                                Login untuk Membeli
                            </a>
                        @endauth
                    </div>
                @endif

                <!-- Description & Details -->
                <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h3 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Deskripsi Produk</h3>
                    <div class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">
                        {{ $product->description }}
                    </div>
                </div>

            </div>
        </div>

        <!-- Related Products Section -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="mt-16 pt-8 border-t border-slate-800">
                <h2 class="text-xl font-black text-white italic tracking-tight uppercase mb-6">
                    PRODUK TERKAIT LAINNYA
                </h2>
                @include('marketplace.partials.product-grid', ['products' => $relatedProducts, 'sidebarOpen' => false])
            </div>
        @endif

    </div>
</div>
@endsection
