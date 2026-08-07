@extends('layouts.pacerhub')

@section('title', ($product->title ?? 'Product') . ' - Marketplace RuangLari')
@section('meta_title', ($product->title ?? 'Product') . ' - Beli Gear & Sepatu Lari | RuangLari Marketplace')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? ($product->title . ' - Beli gear dan sepatu lari berkualitas di RuangLari Marketplace.')), 155))
@section('meta_keywords', 'ruanglari, marketplace lari, ' . ($product->brand ? $product->brand->name . ', ' : '') . ($product->category ? $product->category->name . ', ' : '') . ($product->title ?? 'product') . ', sepatu lari, gear lari indonesia')
@section('og_type', 'product')
@section('og_url', route('marketplace.show', $product->slug))
@section('og_image', $product->primaryImage ? asset('storage/' . $product->primaryImage->image_path) : 'https://ruanglari.id/assets/images/ruanglari-cover.jpg')
@section('canonical_url', route('marketplace.show', $product->slug))

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 relative overflow-hidden font-sans bg-[#08111F]">
    @php
        $isAuction = $product->sale_type === 'auction';
        $now = now();
        $auctionRunning = $isAuction
            && $product->auction_status === 'running'
            && (! $product->auction_start_at || $now->gte($product->auction_start_at))
            && (! $product->auction_end_at || $now->lt($product->auction_end_at));
        $auctionEnded = $isAuction && ($product->auction_status === 'ended' || ($product->auction_end_at && $now->gte($product->auction_end_at)));
        $currentBid = $product->current_price ?? $product->starting_price ?? $product->price;
        $minIncrement = $product->min_increment ?? 0;
        $minAllowedBid = ($recentBids->count() > 0) ? ($currentBid + $minIncrement) : ($product->starting_price ?? $product->price);

        $isWishlisted = false;
        if (auth()->check()) {
            $isWishlisted = \App\Models\Marketplace\MarketplaceWishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
        }
    @endphp

    <div class="max-w-7xl mx-auto">
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-5 py-3 rounded-2xl text-xs md:text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-rose-500/10 border border-rose-500/30 text-rose-400 px-5 py-3 rounded-2xl text-xs md:text-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Breadcrumb -->
        <div class="mb-6 flex flex-wrap items-center gap-2 text-xs text-slate-400 font-mono">
            <a href="{{ route('marketplace.index') }}" class="hover:text-[#B8FF00] transition-colors">MARKETPLACE</a>
            <span>/</span>
            @if($product->category)
                <a href="{{ route('marketplace.index', ['category' => $product->category->slug]) }}" class="hover:text-[#B8FF00] transition-colors uppercase">{{ $product->category->name }}</a>
                <span>/</span>
            @endif
            <span class="text-white truncate max-w-[250px]">{{ $product->title }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Interactive Image Gallery (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="aspect-square bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl overflow-hidden relative group shadow-2xl flex items-center justify-center p-4">
                    @if($product->primaryImage)
                        <img id="mainProductImage" src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" class="max-h-full max-w-full object-contain transition duration-300">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-600">
                            <svg class="w-16 h-16 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="text-xs font-mono uppercase">Tidak Ada Gambar</span>
                        </div>
                    @endif

                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <span class="bg-[#08111F]/90 backdrop-blur border border-[#1F2D44] text-white text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow">
                            {{ $product->condition == 'new' ? 'Baru (Brand New)' : 'Bekas (Second)' }}
                        </span>
                        @if($product->fulfillment_mode === 'consignment')
                            <span class="bg-cyan-500/20 border border-cyan-400/40 text-cyan-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                Titip Jual
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Gallery Thumbnails Switcher -->
                @if($product->images->count() > 1)
                    <div class="grid grid-cols-5 gap-3">
                        @foreach($product->images as $idx => $img)
                            <button type="button" onclick="switchProductImage('{{ asset('storage/' . $img->image_path) }}', this)" 
                                class="thumb-btn aspect-square bg-[#0E1A2D] border {{ $idx === 0 ? 'border-[#B8FF00]' : 'border-[#1F2D44]' }} rounded-2xl overflow-hidden cursor-pointer hover:border-[#B8FF00] transition-all p-1">
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover rounded-xl">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right Column: Compact Product Specs & Order Panel (7 cols) -->
            <div class="lg:col-span-7 flex flex-col space-y-6">
                <!-- Brand & Category Badges -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        @if($product->brand)
                            <span class="bg-[#111F35] border border-[#1F2D44] text-[#B8FF00] text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                {{ $product->brand->name }}
                            </span>
                        @endif
                        @if($product->category)
                            <span class="bg-[#111F35] border border-[#1F2D44] text-slate-300 text-xs font-medium px-3 py-1 rounded-full uppercase tracking-wider">
                                {{ $product->category->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Wishlist Button -->
                    <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-[#1F2D44] bg-[#0E1A2D] text-xs font-bold transition {{ $isWishlisted ? 'text-rose-500 border-rose-500/40 bg-rose-500/10' : 'text-slate-400 hover:text-white hover:border-slate-500' }}">
                        <svg class="w-4 h-4 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span class="wishlist-label">{{ $isWishlisted ? 'Wishlisted' : 'Wishlist' }}</span>
                    </button>
                </div>

                <!-- Product Title -->
                <h1 class="text-2xl md:text-3xl font-black text-white leading-snug tracking-tight">
                    {{ $product->title }}
                </h1>

                <!-- Price Box -->
                <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-5 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-mono mb-1">
                            {{ $isAuction ? 'Penawaran Tertinggi (Current Bid)' : 'Harga Barang' }}
                        </div>
                        <div class="text-3xl font-black text-[#B8FF00] font-mono">
                            Rp {{ number_format($currentBid, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="text-right border-l border-[#1F2D44] pl-6">
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-mono mb-1">Ketersediaan</div>
                        <div class="text-xs font-bold font-mono {{ $product->stock > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $product->stock > 0 ? 'Stok: ' . $product->stock . ' Unit' : 'Stok Habis (Sold Out)' }}
                        </div>
                    </div>
                </div>

                <!-- Specifications Chips Grid -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-3">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Ukuran / Size</div>
                        <div class="text-xs font-bold text-white uppercase">{{ $product->size ?: '-' }}</div>
                    </div>
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-3">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Kondisi</div>
                        <div class="text-xs font-bold text-white uppercase">{{ $product->condition == 'new' ? 'Baru' : 'Bekas' }}</div>
                    </div>
                    <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-3">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Lokasi Seller</div>
                        <div class="text-xs font-bold text-white uppercase truncate">{{ $product->seller && $product->seller->city ? $product->seller->city->name : 'Indonesia' }}</div>
                    </div>
                </div>

                <!-- Seller Profile Card with Clickable Storefront Link -->
                <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-full bg-[#111F35] border border-[#1F2D44] overflow-hidden flex-shrink-0">
                            @if($product->seller && $product->seller->avatar_url)
                                <img src="{{ $product->seller->avatar_url }}" class="w-full h-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($product->seller->name ?? 'Seller') }}&background=111F35&color=B8FF00" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider">Penjual Terverifikasi</div>
                            <div class="font-bold text-white text-sm truncate">{{ $product->seller->name ?? 'RuangLari Seller' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('marketplace.seller.store', $product->seller->username ?: $product->seller->id) }}" class="px-3.5 py-2 rounded-xl bg-[#111F35] border border-[#1F2D44] text-xs font-bold text-slate-300 hover:text-white hover:border-[#B8FF00] transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#B8FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            <span>Lihat Toko Penjual</span>
                        </a>

                        @auth
                            @if(auth()->id() !== $product->seller->id && auth()->user()->role !== 'eo')
                                <a href="{{ route('chat.show', $product->seller->id) }}" class="px-3.5 py-2 rounded-xl border border-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all flex items-center gap-1">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    <span>Chat</span>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Description Block -->
                <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-5">
                    <h3 class="font-bold text-white text-sm mb-3 flex items-center gap-2 uppercase tracking-wider">
                        <svg class="w-4 h-4 text-[#B8FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                        Deskripsi Barang
                    </h3>
                    <div class="text-xs text-slate-300 leading-relaxed space-y-2">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>

                <!-- Actions: Dual Buttons (Add to Cart & Buy Now) -->
                <div class="pt-2">
                    @if($isAuction)
                        @if($auctionRunning)
                            @auth
                                <form action="{{ route('marketplace.auction.bid', $product->slug) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div class="flex gap-3">
                                        <input type="number" name="amount" min="{{ $minAllowedBid }}" step="1000" required
                                            class="flex-1 bg-[#0E1A2D] border border-[#1F2D44] rounded-xl px-4 py-3 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-[#B8FF00]"
                                            placeholder="Nominal Bid (Min: Rp {{ number_format($minAllowedBid, 0, ',', '.') }})">
                                        <button type="submit" class="px-6 py-3 bg-[#B8FF00] text-[#08111F] font-black text-sm rounded-xl hover:bg-[#9FE000] transition">
                                            KIRIM BID
                                        </button>
                                    </div>
                                </form>
                            @else
                                <a href="javascript:void(0)" onclick="openLoginModal()" class="w-full py-3.5 bg-[#B8FF00] text-[#08111F] font-black text-sm rounded-xl text-center block uppercase tracking-wider">
                                    LOGIN UNTUK IKUT LELANG
                                </a>
                            @endauth
                        @endif
                    @else
                        @if($product->stock > 0)
                            @auth
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Add to Cart Form -->
                                    <form action="{{ route('marketplace.cart.add-product', $product->id) }}" method="POST" onsubmit="handleAddToCart(event, this)">
                                        @csrf
                                        <button type="submit" class="w-full py-3.5 px-4 bg-[#111F35] border border-[#1F2D44] text-white hover:border-[#B8FF00] font-bold text-xs uppercase tracking-wider rounded-xl transition flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4 text-[#B8FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                            <span>+ Keranjang</span>
                                        </button>
                                    </form>

                                    <!-- Buy Now Form -->
                                    <form action="{{ route('marketplace.checkout.init') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full py-3.5 px-4 bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider rounded-xl hover:bg-[#9FE000] hover:scale-[1.01] transition flex items-center justify-center gap-2 shadow-lg shadow-[#B8FF00]/20">
                                            <span>Beli Sekarang</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="javascript:void(0)" onclick="openLoginModal()" class="w-full py-3.5 bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider rounded-xl text-center block">
                                    LOGIN UNTUK MEMBELI
                                </a>
                            @endauth
                        @else
                            <button disabled class="w-full py-3.5 bg-slate-800 text-slate-500 font-bold text-xs uppercase rounded-xl cursor-not-allowed">
                                STOK HABIS (SOLD OUT)
                            </button>
                        @endif
                    @endif

                    <div class="flex items-center justify-center gap-2 text-[11px] text-slate-400 mt-4 font-mono">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Transaksi aman & terjamin dengan Rekening Bersama RuangLari Escrow.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="mt-16 pt-10 border-t border-[#1F2D44]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-[10px] text-[#B8FF00] font-mono font-bold uppercase tracking-widest block mb-1">REKOMENDASI UNTUK ANDA</span>
                        <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tight uppercase">
                            PRODUK <span class="text-[#B8FF00]">TERKAIT</span>
                        </h2>
                    </div>
                    @if($product->category)
                        <a href="{{ route('marketplace.index', ['category' => $product->category->slug]) }}" class="text-xs text-slate-400 hover:text-[#B8FF00] font-mono font-bold transition flex items-center gap-1 group">
                            <span>LIHAT SEMUA</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    @foreach($relatedProducts as $relProd)
                        <div class="group bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl overflow-hidden hover:border-[#B8FF00]/50 transition duration-300 flex flex-col justify-between h-full relative">
                            <div>
                                <!-- Image -->
                                <a href="{{ route('marketplace.show', $relProd->slug) }}" class="block overflow-hidden relative aspect-square bg-[#08111F]">
                                    @if($relProd->primaryImage)
                                        <img src="{{ asset('storage/' . $relProd->primaryImage->image_path) }}" alt="{{ $relProd->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg class="w-10 h-10 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-2 left-2 flex flex-col gap-1 items-start z-10">
                                        <span class="bg-[#08111F]/80 backdrop-blur border border-[#1F2D44] text-[10px] text-white font-bold px-2 py-0.5 rounded uppercase">
                                            {{ $relProd->condition == 'new' ? 'Baru' : 'Bekas' }}
                                        </span>
                                    </div>
                                </a>

                                <!-- Body -->
                                <div class="p-4">
                                    <div class="text-[10px] text-[#B8FF00] font-bold uppercase tracking-wider mb-1 truncate">
                                        {{ $relProd->brand ? $relProd->brand->name : ($relProd->category ? $relProd->category->name : 'Gear') }}
                                    </div>
                                    <h3 class="text-sm font-bold text-white mb-2 line-clamp-2 leading-snug hover:text-[#B8FF00] transition">
                                        <a href="{{ route('marketplace.show', $relProd->slug) }}">{{ $relProd->title }}</a>
                                    </h3>
                                    <div class="text-base font-black text-[#B8FF00] font-mono mb-1">
                                        Rp {{ number_format($relProd->price, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Footer CTA -->
                            <div class="p-4 pt-0">
                                <a href="{{ route('marketplace.show', $relProd->slug) }}" class="w-full py-2.5 rounded-xl bg-[#111F35] border border-[#1F2D44] text-white hover:bg-[#B8FF00] hover:text-[#08111F] font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1">
                                    <span>Lihat Detail</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function switchProductImage(src, btn) {
        var mainImg = document.getElementById('mainProductImage');
        if (mainImg) mainImg.src = src;
        
        var btns = document.querySelectorAll('.thumb-btn');
        btns.forEach(function(b) {
            b.classList.remove('border-[#B8FF00]');
            b.classList.add('border-[#1F2D44]');
        });
        if (btn) {
            btn.classList.remove('border-[#1F2D44]');
            btn.classList.add('border-[#B8FF00]');
        }
    }

    async function toggleWishlist(productId, btn) {
        @guest
            if (window.openLoginModal) {
                window.openLoginModal();
            } else {
                window.location.href = '{{ route('login') }}';
            }
            return;
        @endguest

        try {
            const res = await fetch(`/marketplace/wishlist/toggle/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            if (res.status === 401) {
                if (window.openLoginModal) window.openLoginModal(); else window.location.href = '{{ route('login') }}';
                return;
            }
            const data = await res.json();
            if (data.success) {
                const label = btn.querySelector('.wishlist-label');
                const svg = btn.querySelector('svg');
                if (data.in_wishlist) {
                    btn.classList.add('text-rose-500', 'border-rose-500/40', 'bg-rose-500/10');
                    btn.classList.remove('text-slate-400');
                    if (svg) svg.classList.add('fill-current');
                    if (label) label.innerText = 'Wishlisted';
                } else {
                    btn.classList.remove('text-rose-500', 'border-rose-500/40', 'bg-rose-500/10');
                    btn.classList.add('text-slate-400');
                    if (svg) svg.classList.remove('fill-current');
                    if (label) label.innerText = 'Wishlist';
                }
            } else {
                alert(data.message || 'Silakan login terlebih dahulu.');
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function handleAddToCart(event, form) {
        event.preventDefault();
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message || 'Berhasil masuk keranjang!');
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang.');
            }
        } catch (e) {
            form.submit();
        }
    }
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@@type": "Product",
  "name": "{{ e($product->title) }}",
  "image": [
    @if($product->images && $product->images->count() > 0)
        @foreach($product->images as $img)
            "{{ asset('storage/' . $img->image_path) }}"{{ !$loop->last ? ',' : '' }}
        @endforeach
    @else
        "https://ruanglari.id/assets/images/ruanglari-cover.jpg"
    @endif
  ],
  "description": "{{ e(\Illuminate\Support\Str::limit(strip_tags($product->description ?? $product->title), 250)) }}",
  "sku": "RL-MP-{{ $product->id }}",
  "itemCondition": "{{ $product->condition === 'new' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition' }}",
  @if($product->brand)
  "brand": {
    "@@type": "Brand",
    "name": "{{ e($product->brand->name) }}"
  },
  @endif
  "offers": {
    "@@type": "Offer",
    "url": "{{ route('marketplace.show', $product->slug) }}",
    "priceCurrency": "IDR",
    "price": "{{ (float) ($product->price ?? $product->starting_price ?? 0) }}",
    "itemCondition": "{{ $product->condition === 'new' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition' }}",
    "availability": "{{ ($product->stock ?? 1) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "seller": {
      "@@type": "Person",
      "name": "{{ e($product->seller ? $product->seller->name : 'RuangLari Seller') }}"
    }
  }
}
</script>
@endsection
