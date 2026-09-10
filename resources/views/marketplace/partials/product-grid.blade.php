@if($products->count() > 0)
    <div data-products-total="{{ method_exists($products, 'total') ? $products->total() : $products->count() }}"
         @if(isset($gridClass))
            class="{{ $gridClass }}"
         @else
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5"
            :class="typeof sidebarOpen !== 'undefined' && sidebarOpen ? 'grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5' : 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5'"
         @endif
    >
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
        <div class="product-card-item bg-[#0c121e] border border-slate-800 rounded-lg overflow-hidden group hover:border-slate-700 transition-all duration-200 flex flex-col h-full relative font-sans">
            
            <!-- Image Section -->
            <a href="{{ route('marketplace.show', $product->slug) }}" class="product-card-img block relative aspect-square overflow-hidden bg-slate-900">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-900">
                        <span class="text-xs font-semibold text-slate-400">No Image</span>
                    </div>
                @endif
                
                <!-- Single Status Tag (Only when needed) -->
                @if($product->condition == 'used')
                    <span class="absolute top-2.5 left-2.5 z-10 bg-slate-950/80 border border-slate-700 text-slate-200 text-[10px] font-semibold px-2 py-0.5 rounded">
                        Bekas
                    </span>
                @elseif($product->sale_type === 'auction')
                    <span class="absolute top-2.5 left-2.5 z-10 bg-amber-400 text-slate-950 text-[10px] font-bold px-2 py-0.5 rounded">
                        Lelang
                    </span>
                @endif

                <!-- Wishlist Action (Clean top-right) -->
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); quickToggleWishlist({{ $product->id }}, this)" 
                        class="absolute top-2.5 right-2.5 z-20 w-7 h-7 rounded-md bg-slate-950/70 hover:bg-slate-950 border border-slate-700/70 flex items-center justify-center transition active:scale-95 {{ $isWishlisted ? 'text-rose-500 border-rose-500/50' : 'text-slate-300 hover:text-white' }}" title="Simpan ke Wishlist">
                    <svg class="w-3.5 h-3.5 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
                
                <!-- Sold Out Overlay -->
                @if($product->is_sold || $product->stock < 1)
                <div class="absolute inset-0 bg-slate-950/80 flex flex-col items-center justify-center backdrop-blur-[2px] z-30">
                    <span class="text-white font-bold text-xs tracking-wider border border-white/40 bg-white/10 px-3 py-1 rounded uppercase">
                        {{ $product->is_sold ? 'Terjual' : 'Habis' }}
                    </span>
                </div>
                @endif
            </a>
            
            <!-- Card Details -->
            <div class="p-3.5 flex flex-col flex-1 min-w-0">
                <!-- Category / Brand Header -->
                <div class="product-card-meta text-[11px] uppercase tracking-wider text-slate-300 font-semibold mb-1 truncate">
                    {{ $metaHeader }}
                </div>
                
                <!-- Product Title -->
                <h3 class="product-card-title font-bold text-white text-sm leading-snug line-clamp-2 group-hover:text-slate-200 transition-colors mb-2">
                    <a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->title }}</a>
                </h3>

                <!-- Price & Location Meta -->
                <div class="mt-auto pt-2.5 border-t border-slate-800 flex items-baseline justify-between gap-2 min-w-0">
                    <div class="product-card-price text-sm md:text-base font-bold text-white font-sans tracking-tight truncate">
                        Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                    </div>

                    @if($product->seller && $product->seller->city)
                    <span class="text-xs text-slate-300 shrink-0 max-w-[50%] truncate text-right font-medium" title="{{ $product->seller->city->name }}">
                        {{ $product->seller->city->name }}
                    </span>
                    @endif
                </div>

                @if(Auth::check() && (int) $product->user_id === (int) Auth::id())
                    <div class="mt-2.5 pt-1">
                        <a href="{{ route('marketplace.show', $product->slug) }}" class="btn-manage-gear block w-full py-1.5 bg-slate-900 hover:bg-slate-800 text-slate-200 text-center text-xs font-semibold rounded-md transition-colors border border-slate-700">
                            Kelola Produk
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    @if(method_exists($products, 'hasPages') && $products->hasPages())
        <div class="mt-10 pagination-container flex justify-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
@else
    <div class="empty-state-box border border-slate-800 bg-[#0c121e] rounded-lg p-12 text-center max-w-md mx-auto my-8 font-sans">
        <h3 class="text-base font-bold text-white uppercase tracking-wider mb-1.5">Belum Ada Produk</h3>
        <p class="text-slate-300 text-xs mb-6 leading-relaxed">Produk dengan filter yang Anda pilih belum tersedia. Coba ubah pencarian atau pasang iklan gear Anda sekarang.</p>
        <a href="{{ auth()->check() ? route('marketplace.seller.products.create') : route('login', ['redirect' => route('marketplace.seller.products.create')]) }}" class="btn-empty-sell inline-flex items-center px-6 py-2.5 rounded-md bg-white hover:bg-slate-200 text-slate-950 font-bold text-xs uppercase tracking-wider transition-all shadow-sm">
            <span>+ Jual Gear Anda</span>
        </a>
    </div>
@endif
