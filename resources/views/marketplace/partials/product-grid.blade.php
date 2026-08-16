@if($products->count() > 0)
    <div data-products-total="{{ $products->total() }}" :class="sidebarOpen ? 'grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6' : 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6'">
        @foreach($products as $product)
        @php
            $isWishlisted = false;
            if (auth()->check()) {
                $isWishlisted = \App\Models\Marketplace\MarketplaceWishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
            }
            $brandName = $product->brand ? $product->brand->name : '';
            $categoryName = $product->category ? $product->category->name : 'Gear';
            $metaHeader = $brandName ? ($brandName . ' • ' . $categoryName) : $categoryName;
        @endphp
        <div class="bg-[#0c121e] border border-slate-800/90 rounded-2xl overflow-hidden group hover:border-slate-600 transition-all duration-300 flex flex-col h-full relative shadow-md shadow-black/20">
            
            <!-- Image Section (Clean Athletic Framing) -->
            <a href="{{ route('marketplace.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-[#131b2c]">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-600 bg-slate-900/40">
                        <svg class="w-10 h-10 mb-1.5 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-[9px] font-mono tracking-widest uppercase opacity-30">No Image</span>
                    </div>
                @endif
                
                <!-- Floating Micro Badges (Top Left) -->
                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start z-10">
                    @if($product->condition == 'new')
                        <span class="bg-white text-slate-950 text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider shadow-sm">
                            BARU
                        </span>
                    @else
                        <span class="bg-black/70 backdrop-blur-md border border-white/10 text-slate-300 text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                            BEKAS
                        </span>
                    @endif

                    @if($product->sale_type === 'auction')
                    <span class="bg-amber-400 text-slate-950 text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider shadow-sm">
                        LELANG
                    </span>
                    @endif

                    @if($product->fulfillment_mode === 'consignment')
                    <span class="bg-cyan-500/20 backdrop-blur-md border border-cyan-400/40 text-cyan-300 text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                        TITIP JUAL
                    </span>
                    @endif
                </div>

                <!-- Floating Wishlist & Share Action (Top Right) -->
                <div class="absolute top-2.5 right-2.5 z-20 flex items-center gap-1.5">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openShareModal('{{ e($product->title) }}', '{{ route('marketplace.show', $product->slug) }}')" 
                            class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-md border border-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all active:scale-95" title="Bagikan Produk">
                        <i class="fa-solid fa-share-nodes text-[11px]"></i>
                    </button>
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); quickToggleWishlist({{ $product->id }}, this)" 
                            class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-md border border-white/10 flex items-center justify-center transition-all active:scale-95 {{ $isWishlisted ? 'text-rose-500 border-rose-500/50' : 'text-slate-400 hover:text-white' }}" title="Wishlist">
                        <svg class="w-3.5 h-3.5 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </button>
                </div>
                
                <!-- Sold Out Overlay -->
                @if($product->is_sold || $product->stock < 1)
                <div class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center backdrop-blur-[2px] z-30">
                    <span class="text-white font-black text-xs md:text-sm tracking-widest border border-white/40 bg-white/10 px-3 py-1 rounded-md uppercase">
                        {{ $product->is_sold ? 'TERJUAL' : 'HABIS' }}
                    </span>
                    @if($product->is_sold && $product->sold_channel)
                        <span class="text-[9px] text-slate-400 font-mono mt-1.5 uppercase">
                            via {{ strtoupper($product->sold_channel) }}
                        </span>
                    @endif
                </div>
                @endif
            </a>
            
            <!-- Card Details (Nike Editorial Layout) -->
            <div class="p-4 flex flex-col flex-1 min-w-0">
                
                <!-- Category / Brand Header -->
                <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-semibold mb-1 truncate">
                    {{ $metaHeader }}
                </div>
                
                <!-- Product Title -->
                <h3 class="font-bold text-white text-xs md:text-sm leading-snug line-clamp-2 group-hover:text-neon transition-colors mb-2.5">
                    <a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->title }}</a>
                </h3>

                <!-- Price Block -->
                <div class="mt-auto">
                    <div class="text-sm md:text-base font-black text-white font-mono tracking-tight truncate">
                        Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                    </div>
                </div>
                
                <!-- Seller & Location Meta -->
                <div class="pt-2.5 mt-2 border-t border-slate-800/80 flex items-center justify-between gap-2 min-w-0 text-[10px] text-slate-400">
                    @if($product->seller)
                        <a href="{{ route('marketplace.seller.store', $product->seller->username ?: $product->seller->id) }}" class="flex items-center gap-1.5 min-w-0 hover:text-white transition truncate">
                            <span class="truncate">{{ $product->seller->name ?? 'Seller' }}</span>
                        </a>
                    @else
                        <span class="italic truncate text-slate-500">Seller</span>
                    @endif

                    @if($product->seller && $product->seller->city)
                    <span class="text-slate-500 shrink-0 max-w-[45%] truncate text-right" title="{{ $product->seller->city->name }}">
                        {{ $product->seller->city->name }}
                    </span>
                    @endif
                </div>

                <!-- CTA Action Buttons -->
                <div class="mt-3 pt-1">
                    @if($product->stock < 1)
                        <button disabled class="w-full py-2 bg-slate-900 text-slate-600 font-bold rounded-xl text-xs cursor-not-allowed border border-slate-800">
                            Stok Habis
                        </button>
                    @elseif($product->sale_type === 'auction')
                        <a href="{{ route('marketplace.show', $product->slug) }}" class="w-full py-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1.5 shadow-sm">
                            <span>Ikut Lelang</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    @else
                        @if(Auth::check() && (int) $product->user_id === (int) Auth::id())
                            <a href="{{ route('marketplace.show', $product->slug) }}" class="w-full py-2 bg-slate-900 hover:bg-slate-850 text-slate-300 text-center text-xs font-bold rounded-xl transition-all border border-slate-800 flex items-center justify-center">
                                Kelola Produk Saya
                            </a>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Add to Cart Button -->
                                @auth
                                    <form action="{{ route('marketplace.cart.add-product', $product->id) }}" method="POST" onsubmit="quickAddToCart(event, this)">
                                        @csrf
                                        <button type="submit" class="w-full py-2 bg-slate-900 border border-slate-750 hover:border-white text-slate-200 hover:text-white font-bold rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1" title="Tambah ke Keranjang">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            <span>+ Cart</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" onclick="if (window.openLoginModal) window.openLoginModal(); else window.location='{{ route('login') }}';" class="w-full py-2 bg-slate-900 border border-slate-750 text-slate-300 font-bold rounded-xl text-center text-xs">
                                        + Cart
                                    </button>
                                @endauth

                                <!-- Direct Buy Button -->
                                @auth
                                    <form action="{{ route('marketplace.checkout.init') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full py-2 bg-white hover:bg-slate-200 text-slate-950 font-black rounded-xl text-center text-xs transition-all flex items-center justify-center shadow-md shadow-white/5">
                                            <span>Beli</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" onclick="if (window.openLoginModal) window.openLoginModal(); else window.location='{{ route('login') }}';" class="w-full py-2 bg-white hover:bg-slate-200 text-slate-950 font-black rounded-xl text-center text-xs">
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
    
    <!-- Pagination (Minimalist Athletic Styling) -->
    @if(method_exists($products, 'hasPages') && $products->hasPages())
        <div class="mt-12 pagination-container flex justify-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
@else
    <div class="border border-slate-800 bg-[#0c121e] rounded-2xl p-12 text-center max-w-md mx-auto my-8">
        <div class="w-14 h-14 bg-slate-900 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
        <h3 class="text-base font-bold text-white uppercase tracking-wider mb-1.5">Belum Ada Produk</h3>
        <p class="text-slate-400 text-xs mb-6 leading-relaxed">Produk dengan filter yang Anda pilih belum tersedia. Coba ubah pencarian atau pasang iklan gear Anda sekarang.</p>
        <a href="{{ auth()->check() ? route('marketplace.seller.products.create') : route('login', ['redirect' => route('marketplace.seller.products.create')]) }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-white hover:bg-slate-200 text-slate-950 font-black text-xs uppercase tracking-wider transition-all shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
            <span>Jual Gear Anda</span>
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
                // Refresh cart badge count
                if (typeof updateMarketCartBadge === 'function') updateMarketCartBadge();
                alert(data.message || 'Berhasil ditambahkan ke keranjang!');
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang.');
            }
        } catch (e) {
            form.submit();
        }
    }
</script>
