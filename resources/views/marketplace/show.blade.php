@extends('layouts.pacerhub')

@section('title', $product->title . ' - RuangLari Market')
@section('meta_title', $product->title . ' | Verified Running Gear')
@section('meta_description', Str::limit(strip_tags($product->description), 160))

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark"
     x-data="{
         activeImage: '{{ $product->primaryImage ? asset('storage/' . $product->primaryImage->image_path) : ($product->images->first() ? asset('storage/' . $product->images->first()->image_path) : '') }}',
         qty: 1,
         wishlisted: false
     }">

    <div class="max-w-7xl mx-auto">
        
        <!-- Breadcrumb & Top Bar -->
        <nav class="flex flex-wrap items-center justify-between gap-3 mb-8 border-b border-slate-800/80 pb-4 text-xs font-mono">
            <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition group">
                <i class="fas fa-arrow-left text-[11px] group-hover:-translate-x-0.5 transition-transform"></i>
                <span class="uppercase tracking-wider">Kembali ke Katalog</span>
            </a>
            
            <div class="flex items-center gap-2 text-slate-500 uppercase tracking-widest text-[11px]">
                <span>Market</span>
                <span>/</span>
                <a href="{{ route('marketplace.index', ['category' => optional($product->category)->slug]) }}" class="hover:text-neon transition">
                    {{ $product->category ? $product->category->name : 'Gear' }}
                </a>
                <span>/</span>
                <span class="text-slate-300 font-bold truncate max-w-[180px] sm:max-w-[280px]">{{ $product->title }}</span>
            </div>
        </nav>

        <!-- Main Product Layout Grid (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 mb-16">
            
            <!-- Left Column: Gallery & Images (6 Columns) -->
            <div class="lg:col-span-6 space-y-4">
                <div class="sticky top-28 space-y-4">
                    
                    <!-- Main Frame -->
                    <div class="relative aspect-square w-full rounded-2xl overflow-hidden bg-[#131b2c] border border-slate-800 shadow-2xl group flex items-center justify-center">
                        @if($product->primaryImage || $product->images->count() > 0)
                            <img :src="activeImage" 
                                 alt="{{ $product->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-600 bg-slate-900/60">
                                <i class="fas fa-running text-5xl mb-3 text-slate-700"></i>
                                <span class="text-xs uppercase font-mono tracking-widest text-slate-500">Gambar Tidak Tersedia</span>
                            </div>
                        @endif

                        <!-- Top Left Floating Badges (Nike-style Micro Badges) -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
                            @if($product->condition === 'new')
                                <span class="px-3 py-1 rounded-md bg-white text-dark text-[10px] font-black uppercase tracking-widest shadow-md">
                                    BARU
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-md bg-slate-950/80 backdrop-blur border border-slate-700 text-slate-200 text-[10px] font-black uppercase tracking-widest shadow-md">
                                    BEKAS
                                </span>
                            @endif

                            @if($product->is_sold)
                                <span class="px-3 py-1 rounded-md bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest shadow-md">
                                    TERJUAL (SOLD)
                                </span>
                            @endif

                            @if($product->sale_type === 'auction')
                                <span class="px-3 py-1 rounded-md bg-amber-400 text-dark text-[10px] font-black uppercase tracking-widest shadow-md">
                                    LELANG
                                </span>
                            @endif

                            @if($product->isFeaturedActive())
                                <span class="px-3 py-1 rounded-md bg-neon text-dark text-[10px] font-black uppercase tracking-widest shadow-md">
                                    FEATURED
                                </span>
                            @endif
                        </div>

                        <!-- Top Right Floating Action Buttons -->
                        <div class="absolute top-4 right-4 flex items-center gap-2 z-10">
                            <button type="button" 
                                    @click="window.openShareModal ? window.openShareModal('{{ route('marketplace.show', $product->slug) }}', '{{ addslashes($product->title) }}') : (navigator.clipboard.writeText(window.location.href) && alert('Link produk disalin!'))" 
                                    class="w-10 h-10 rounded-full bg-slate-900/80 backdrop-blur-md border border-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-800 flex items-center justify-center transition shadow-lg"
                                    title="Bagikan Produk">
                                <i class="fas fa-share-alt text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Thumbnails Carousel/Selector -->
                    @if($product->images && $product->images->count() > 1)
                        <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
                            @foreach($product->images as $img)
                                <button type="button" 
                                        @click="activeImage = '{{ asset('storage/' . $img->image_path) }}'" 
                                        class="w-20 h-20 rounded-xl overflow-hidden border-2 transition-all shrink-0 bg-[#131b2c]"
                                        :class="activeImage === '{{ asset('storage/' . $img->image_path) }}' ? 'border-neon ring-2 ring-neon/20' : 'border-slate-800 opacity-70 hover:opacity-100 hover:border-slate-600'">
                                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>

            <!-- Right Column: Specs, Seller & Purchase (6 Columns) -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- Category, Brand & Views Meta -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/80 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-widest text-slate-400 bg-slate-850 px-3 py-1 rounded-md border border-slate-750">
                            {{ $product->brand ? $product->brand->name : 'GENUINE GEAR' }} • {{ $product->category ? $product->category->name : 'RUNNING' }}
                        </span>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-slate-900 border border-slate-800 text-slate-400 text-[11px] font-mono">
                        <i class="fas fa-eye text-slate-500"></i>
                        <span>{{ number_format($product->views_count ?? 0) }} Dilihat</span>
                    </div>
                </div>

                <!-- Product Title -->
                <h1 class="text-2xl sm:text-4xl font-black text-white uppercase tracking-tight leading-tight font-sans">
                    {{ $product->title }}
                </h1>

                <!-- Price Box (Nike Athletic Style) -->
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-3">
                    <div class="text-[11px] text-slate-400 font-mono uppercase tracking-widest">
                        {{ $product->sale_type === 'auction' ? 'Current Bid (Penawaran Tertinggi)' : 'Harga' }}
                    </div>
                    
                    <div class="text-3xl sm:text-4xl font-black text-white font-mono tracking-tight flex items-baseline gap-2">
                        <span class="text-neon">Rp</span>
                        <span>{{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}</span>
                    </div>

                    @if($product->is_sold)
                        <div class="pt-2 border-t border-slate-800/80 flex items-center gap-2 text-rose-400 text-xs font-mono font-bold uppercase">
                            <i class="fas fa-times-circle"></i>
                            <span>Item ini sudah laku terjual {{ $product->sold_channel ? '(' . strtoupper($product->sold_channel) . ')' : '' }}</span>
                        </div>
                    @elseif($product->stock > 0)
                        <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs font-mono">
                            <span class="text-emerald-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Stok Siap Kirim ({{ $product->stock }} Unit)
                            </span>
                            @if(optional($product->seller)->city)
                                <span class="text-slate-400">
                                    <i class="fas fa-map-marker-alt text-slate-500 mr-1"></i>{{ $product->seller->city->name }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Auction Interface (if auction) -->
                @if($product->sale_type === 'auction' && !$product->is_sold)
                    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-3">
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-amber-400 uppercase font-bold tracking-wider">Status Lelang</span>
                            <span class="text-slate-300">Kelipatan Bid: Rp {{ number_format($product->min_increment ?? 50000, 0, ',', '.') }}</span>
                        </div>
                        @if($product->auction_end_at)
                            <p class="text-xs text-slate-400 font-mono">
                                Berakhir pada: <strong class="text-white">{{ $product->auction_end_at->format('d M Y, H:i') }} WIB</strong>
                            </p>
                        @endif
                    </div>
                @endif

                <!-- Key Specifications Metric Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="block text-[10px] font-mono uppercase text-slate-500 tracking-wider">Kondisi</span>
                        <span class="block text-sm font-bold text-white uppercase mt-0.5">{{ $product->condition === 'new' ? 'Baru' : 'Bekas' }}</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="block text-[10px] font-mono uppercase text-slate-500 tracking-wider">Ukuran / Size</span>
                        <span class="block text-sm font-bold text-white uppercase mt-0.5">{{ $product->size ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="block text-[10px] font-mono uppercase text-slate-500 tracking-wider">Brand</span>
                        <span class="block text-sm font-bold text-white uppercase mt-0.5">{{ optional($product->brand)->name ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="block text-[10px] font-mono uppercase text-slate-500 tracking-wider">Kategori</span>
                        <span class="block text-sm font-bold text-white uppercase mt-0.5">{{ optional($product->category)->name ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="block text-[10px] font-mono uppercase text-slate-500 tracking-wider">Tipe Gear</span>
                        <span class="block text-sm font-bold text-white uppercase mt-0.5">{{ $product->type ?: 'Running Gear' }}</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="block text-[10px] font-mono uppercase text-slate-500 tracking-wider">Lokasi Seller</span>
                        <span class="block text-sm font-bold text-white uppercase mt-0.5 truncate">{{ optional(optional($product->seller)->city)->name ?: 'Indonesia' }}</span>
                    </div>
                </div>

                <!-- Seller Profile Card -->
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-700 bg-slate-950 shrink-0">
                            <img src="{{ optional($product->seller)->avatar ? (Str::startsWith($product->seller->avatar, 'http') ? $product->seller->avatar : '/storage/'.$product->seller->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(optional($product->seller)->name ?? 'Seller').'&background=111F35&color=B8FF00' }}" 
                                 alt="Avatar" 
                                 class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-white">{{ optional($product->seller)->name ?? 'Runner' }}</p>
                                <span class="text-[10px] font-mono text-neon bg-neon/10 px-2 py-0.5 rounded border border-neon/20 uppercase">Verified</span>
                            </div>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">
                                {{ optional(optional($product->seller)->city)->name ?: 'Member Komunitas' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        @if($product->seller)
                            <a href="{{ route('marketplace.seller.store', $product->seller->username ?: $product->seller->id) }}" 
                               class="flex-1 sm:flex-none text-center px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-200 text-xs font-mono font-bold border border-slate-700 transition">
                                Profil Seller
                            </a>
                        @endif

                        @if(optional($product->seller)->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $product->seller->phone) }}?text={{ urlencode('Halo ' . optional($product->seller)->name . ', saya tertarik dengan produk lari Anda di RuangLari: ' . $product->title . ' (' . route('marketplace.show', $product->slug) . ')') }}" 
                               target="_blank"
                               class="flex-1 sm:flex-none text-center px-4 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 text-xs font-mono font-bold border border-emerald-500/30 transition flex items-center justify-center gap-1.5">
                                <i class="fab fa-whatsapp"></i>
                                <span>Tanya Seller</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Primary Action Buttons (Buy Now / Add to Cart) -->
                @if(!$product->is_sold && $product->stock > 0)
                    <div class="space-y-3 pt-2">
                        @auth
                            @if(auth()->id() !== $product->user_id)
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <form method="POST" action="{{ route('marketplace.cart.add-product', $product->id) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full py-4 rounded-xl bg-slate-850 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-widest border border-slate-700 transition flex items-center justify-center gap-2">
                                            <i class="fas fa-cart-plus text-sm"></i>
                                            <span>+ Keranjang</span>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('marketplace.checkout.init') }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full py-4 rounded-xl bg-white hover:bg-slate-200 text-dark font-black text-xs uppercase tracking-widest transition flex items-center justify-center gap-2 shadow-lg shadow-white/5">
                                            <span>Beli Sekarang</span>
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="p-4 rounded-xl bg-slate-850 border border-slate-750 text-center">
                                    <span class="text-xs font-mono text-slate-400">Ini adalah produk listing milik Anda sendiri.</span>
                                    <div class="mt-2 flex items-center justify-center gap-2">
                                        <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="text-xs font-mono text-neon hover:underline font-bold">
                                            Edit Produk Ini
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login', ['redirect' => route('marketplace.show', $product->slug)]) }}" class="block w-full py-4 rounded-xl bg-white hover:bg-slate-200 text-dark font-black text-xs uppercase tracking-widest text-center transition shadow-lg">
                                Login untuk Melakukan Pembelian
                            </a>
                        @endauth
                    </div>
                @endif

                <!-- Product Description Section -->
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
                    <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-white border-b border-slate-800/80 pb-3">
                        Deskripsi & Detail Produk
                    </h2>
                    <div class="text-slate-300 text-sm leading-relaxed whitespace-pre-line font-sans">
                        {{ $product->description }}
                    </div>
                </div>

                <!-- Guarantee & Shipping Assurance Note -->
                <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/80 flex items-start gap-3 text-xs text-slate-400 font-sans leading-relaxed">
                    <i class="fas fa-shield-alt text-neon text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="text-slate-200 block font-bold mb-0.5">Keamanan Transaksi RuangLari</strong>
                        Semua transaksi dilindungi sistem escrow rekening bersama platform. Dana seller baru diteruskan setelah pembeli menerima gear sesuai deskripsi.
                    </div>
                </div>

            </div>
        </div>

        <!-- Related Products Carousel/Grid -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="mt-20 pt-10 border-t border-slate-800/80">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-neon">Koleksi Terkait</span>
                        <h2 class="text-xl sm:text-2xl font-black text-white uppercase tracking-tight font-sans mt-1">
                            GEAR SERUPA LAINNYA
                        </h2>
                    </div>
                    <a href="{{ route('marketplace.index', ['category' => optional($product->category)->slug]) }}" class="text-xs font-mono text-slate-400 hover:text-white transition uppercase tracking-wider">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                @include('marketplace.partials.product-grid', ['products' => $relatedProducts, 'sidebarOpen' => false])
            </div>
        @endif

    </div>
</div>
@endsection
