@if($products->count() > 0)
    <div :class="sidebarOpen ? 'grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6' : 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6'">
        @foreach($products as $product)
        @php
            $isWishlisted = false;
            if (auth()->check()) {
                $isWishlisted = \App\Models\Marketplace\MarketplaceWishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
            }
        @endphp
        <div class="bg-slate-900/40 backdrop-blur-sm border border-slate-850 rounded-2xl overflow-hidden group hover:border-neon/45 transition-all duration-300 shadow-md shadow-slate-950/20 hover:shadow-xl hover:shadow-neon/5 flex flex-col h-full relative">
            
            <!-- Image Section -->
            <a href="{{ route('marketplace.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-slate-950/60">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-650 bg-slate-950/50">
                        <svg class="w-10 h-10 mb-2 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-[10px] font-mono tracking-wider uppercase opacity-40">No Image</span>
                    </div>
                @endif
                
                <!-- Overlay Badges -->
                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start z-10">
                    <span class="bg-dark/80 backdrop-blur border border-slate-700/60 text-white text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                        {{ $product->condition == 'new' ? 'BARU' : 'BEKAS' }}
                    </span>
                    @if($product->sale_type === 'auction')
                    <span class="bg-neon/20 backdrop-blur border border-neon/40 text-neon text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                        LELANG
                    </span>
                    @endif
                    @if($product->fulfillment_mode === 'consignment')
                    <span class="bg-cyan-500/20 backdrop-blur border border-cyan-400/40 text-cyan-300 text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                        TITIP JUAL
                    </span>
                    @endif
                </div>

                <!-- Quick Action Buttons Top Right (Share & Wishlist) -->
                <div class="absolute top-2.5 right-2.5 z-20 flex items-center gap-1.5">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openShareModal('{{ e($product->title) }}', '{{ route('marketplace.show', $product->slug) }}')" class="w-8 h-8 rounded-full bg-dark/80 backdrop-blur border border-slate-700/60 flex items-center justify-center text-slate-400 hover:text-neon transition-transform active:scale-95" title="Bagikan Produk">
                        <i class="fa-solid fa-share-nodes text-xs"></i>
                    </button>
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); quickToggleWishlist({{ $product->id }}, this)" class="w-8 h-8 rounded-full bg-dark/80 backdrop-blur border border-slate-700/60 flex items-center justify-center transition-transform active:scale-95 {{ $isWishlisted ? 'text-rose-500 border-rose-500/50' : 'text-slate-400 hover:text-white' }}" title="Wishlist">
                        <svg class="w-3.5 h-3.5 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </button>
                </div>
                
                <!-- Sold Out / Terjual Overlay -->
                @if($product->is_sold || $product->stock < 1)
                <div class="absolute inset-0 bg-dark/80 flex flex-col items-center justify-center backdrop-blur-[2px] z-30">
                    <span class="text-rose-500 font-black text-sm tracking-widest border-2 border-rose-500 px-3 py-1.5 rounded-lg rotate-[-12deg] uppercase shadow-lg shadow-rose-500/20">
                        {{ $product->is_sold ? 'TERJUAL (SOLD)' : 'SOLD OUT' }}
                    </span>
                    @if($product->is_sold && $product->sold_channel)
                        <span class="text-[9px] text-slate-300 font-mono font-bold mt-2 uppercase bg-dark/90 px-2 py-0.5 rounded border border-slate-700">
                            via {{ strtoupper($product->sold_channel) }}
                        </span>
                    @endif
                </div>
                @endif
            </a>
            
            <!-- Details Section -->
            <div class="p-4 flex flex-col flex-1 min-w-0">
                <div class="flex justify-between items-center gap-2 mb-1.5 min-w-0">
                    <div class="text-[9px] text-neon font-mono uppercase tracking-wider truncate">{{ $product->category ? $product->category->name : 'Gear' }}</div>
                    @if($product->brand)
                        <div class="text-[9px] text-slate-500 font-mono uppercase tracking-wider truncate text-right">{{ $product->brand->name }}</div>
                    @endif
                </div>
                
                <h3 class="font-bold text-white text-xs md:text-sm leading-snug mb-2 line-clamp-2 group-hover:text-neon transition-colors">
                    <a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->title }}</a>
                </h3>

                <!-- Price Section with Space after Rp -->
                <div class="mt-auto pt-2.5 border-t border-slate-800/80">
                    <div class="text-[9px] text-slate-500 uppercase tracking-wider font-mono mb-0.5">
                        {{ $product->sale_type === 'auction' ? 'Current Bid' : 'Harga' }}
                    </div>
                    <div class="text-sm md:text-base font-black text-[#B8FF00] font-mono italic tracking-tight truncate">
                        Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                    </div>
                </div>
                
                <!-- Seller Meta -->
                <div class="pt-2 mt-1.5 border-t border-slate-850/40 flex items-center justify-between gap-1.5 min-w-0">
                    @if($product->seller)
                        <a href="{{ route('marketplace.seller.store', $product->seller->username ?: $product->seller->id) }}" class="flex items-center gap-1.5 min-w-0 hover:text-neon transition">
                            <div class="w-4 h-4 rounded-full bg-slate-850 border border-slate-700/60 overflow-hidden shrink-0">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($product->seller->name ?? 'Seller') }}&background=111F35&color=B8FF00" class="w-full h-full object-cover" alt="{{ $product->seller->name ?? 'Seller' }}">
                            </div>
                            <span class="text-[9px] text-slate-400 font-medium truncate group-hover:text-neon">
                                {{ $product->seller->name ?? 'Seller' }}
                            </span>
                        </a>
                    @else
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="text-[9px] text-slate-500 font-medium italic truncate">Seller</span>
                        </div>
                    @endif
                    @if($product->seller && $product->seller->city)
                    <span class="text-[9px] text-slate-500 flex items-center gap-0.5 shrink-0 max-w-[45%] truncate" title="{{ $product->seller->city->name }}">
                        <svg class="w-2.5 h-2.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        {{ $product->seller->city->name }}
                    </span>
                    @endif
                </div>

                <!-- Action Buttons: Add to Cart & Buy CTA -->
                <div class="mt-3 pt-2">
                    @if($product->stock < 1)
                        <button disabled class="w-full py-2 bg-slate-800 text-slate-500 font-bold rounded-xl text-xs cursor-not-allowed">
                            Stok Habis
                        </button>
                    @elseif($product->sale_type === 'auction')
                        <a href="{{ route('marketplace.show', $product->slug) }}" class="w-full py-2 bg-slate-800 hover:bg-slate-750 text-white font-bold rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1.5 border border-slate-700/60">
                            <span>Ikut Lelang</span>
                            <svg class="w-3.5 h-3.5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    @else
                        @if(Auth::check() && (int) $product->user_id === (int) Auth::id())
                            <a href="{{ route('marketplace.show', $product->slug) }}" class="w-full py-2 bg-slate-900/60 hover:bg-slate-850 text-slate-400 text-center text-xs font-bold rounded-xl transition-all border border-slate-800 flex items-center justify-center">
                                Detail Barang Saya
                            </a>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Add to Cart Button -->
                                @auth
                                    <form action="{{ route('marketplace.cart.add-product', $product->id) }}" method="POST" onsubmit="quickAddToCart(event, this)">
                                        @csrf
                                        <button type="submit" class="w-full py-2 bg-[#111F35] border border-[#1F2D44] hover:border-[#B8FF00] text-white font-bold rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1" title="Tambah ke Keranjang">
                                            <svg class="w-3.5 h-3.5 text-[#B8FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            <span>+ Cart</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" onclick="if (window.openLoginModal) window.openLoginModal(); else window.location='{{ route('login') }}';" class="w-full py-2 bg-[#111F35] border border-[#1F2D44] text-slate-300 font-bold rounded-xl text-center text-xs">
                                        + Cart
                                    </button>
                                @endauth

                                <!-- Direct Buy Button -->
                                @auth
                                    <form action="{{ route('marketplace.checkout.init') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full py-2 bg-[#B8FF00] hover:bg-[#9FE000] text-[#08111F] font-black rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1 shadow-md shadow-[#B8FF00]/10">
                                            <span>Beli</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" onclick="if (window.openLoginModal) window.openLoginModal(); else window.location='{{ route('login') }}';" class="w-full py-2 bg-[#B8FF00] hover:bg-[#9FE000] text-[#08111F] font-black rounded-xl text-center text-xs">
                                        Beli
                                    </button>
                                @endauth
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="mt-10 pagination-container">
        {{ $products->appends(request()->query())->links() }}
    </div>
@else
    <div class="bg-slate-900/20 backdrop-blur-sm border border-slate-800 rounded-2xl p-12 text-center">
        <div class="w-16 h-16 bg-slate-850 border border-slate-800 rounded-full flex items-center justify-center mx-auto mb-5 text-2xl">🛍️</div>
        <h3 class="text-xl font-bold text-white mb-1">Belum ada produk</h3>
        <p class="text-slate-400 text-xs mb-6 max-w-sm mx-auto leading-relaxed">Jadilah yang pertama menjual barang di kategori ini! Pasang iklanmu sekarang dan jangkau ribuan pelari.</p>
        <a href="{{ route('marketplace.seller.products.create') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-white transition-all shadow-md shadow-neon/10 text-xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Jual Barang
        </a>
    </div>
@endif

<script>
    async function quickToggleWishlist(productId, btn) {
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
                const svg = btn.querySelector('svg');
                if (data.in_wishlist) {
                    btn.classList.add('text-rose-500', 'border-rose-500/50');
                    btn.classList.remove('text-slate-400');
                    if (svg) svg.classList.add('fill-current');
                } else {
                    btn.classList.remove('text-rose-500', 'border-rose-500/50');
                    btn.classList.add('text-slate-400');
                    if (svg) svg.classList.remove('fill-current');
                }
            } else {
                alert(data.message || 'Silakan login terlebih dahulu.');
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function quickAddToCart(event, form) {
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
                alert(data.message || 'Berhasil ditambahkan ke keranjang!');
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang.');
            }
        } catch (e) {
            form.submit();
        }
    }
</script>
