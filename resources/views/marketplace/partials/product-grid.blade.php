@if($products->count() > 0)
    <div :class="sidebarOpen ? 'grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6' : 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6'">
        @foreach($products as $product)
        <div class="bg-slate-900/30 backdrop-blur-sm border border-slate-850 rounded-2xl overflow-hidden group hover:border-neon/45 transition-all duration-300 shadow-md shadow-slate-950/20 hover:shadow-xl hover:shadow-neon/5 flex flex-col h-full">
            
            <!-- Image Section -->
            <a href="{{ route('marketplace.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-slate-950/40">
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
                <div class="absolute top-2.5 right-2.5 flex flex-col gap-1 items-end">
                    <span class="bg-dark/80 backdrop-blur border border-slate-700/60 text-white text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                        {{ $product->condition == 'new' ? 'NEW' : 'USED' }}
                    </span>
                    @if($product->sale_type === 'auction')
                    <span class="bg-neon/15 backdrop-blur border border-neon/30 text-neon text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                        LELANG
                    </span>
                    @endif
                    @if($product->fulfillment_mode === 'consignment')
                    <span class="bg-cyan-500/15 backdrop-blur border border-cyan-400/30 text-cyan-300 text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                        TITIP JUAL
                    </span>
                    @endif
                    @if($product->size)
                    <span class="bg-dark/85 backdrop-blur border border-slate-700/60 text-slate-300 text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                        SZ {{ $product->size }}
                    </span>
                    @endif
                </div>
                
                <!-- Sold Out Overlay -->
                @if($product->stock < 1)
                <div class="absolute inset-0 bg-dark/80 flex items-center justify-center backdrop-blur-[2px]">
                    <span class="text-red-500 font-black text-sm tracking-widest border-2 border-red-550 px-3 py-1.5 rounded-lg rotate-[-12deg] uppercase">SOLD OUT</span>
                </div>
                @endif
            </a>
            
            <!-- Details Section -->
            <div class="p-4 flex flex-col flex-1">
                <div class="flex justify-between items-center mb-1.5">
                    <div class="text-[10px] text-neon font-mono uppercase tracking-wider">{{ $product->category->name }}</div>
                    @if($product->brand)
                        <div class="text-[10px] text-slate-400 font-mono uppercase tracking-wider">{{ $product->brand->name }}</div>
                    @endif
                </div>
                
                <h3 class="font-bold text-white text-sm md:text-base leading-snug mb-2 line-clamp-2 group-hover:text-neon transition-colors">
                    <a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->title }}</a>
                </h3>

                <!-- Price & Seller Meta -->
                <div class="mt-auto pt-3 border-t border-slate-800/80 flex justify-between items-end gap-2">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider font-mono mb-0.5">
                            {{ $product->sale_type === 'auction' ? 'Current Bid' : 'Price' }}
                        </div>
                        <div class="text-base md:text-lg font-black text-white italic tracking-tight">
                            Rp{{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end shrink-0 max-w-[100px]">
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-full bg-slate-850 border border-slate-700/60 overflow-hidden shrink-0">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($product->seller->name) }}&background=random" class="w-full h-full object-cover" alt="{{ $product->seller->name }}">
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium truncate">
                                {{ $product->seller->name }}
                            </span>
                        </div>
                        @if($product->seller->city)
                        <span class="text-[9px] text-slate-500 flex items-center gap-0.5 mt-0.5 truncate">
                            <svg class="w-2.5 h-2.5 text-slate-650 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            {{ $product->seller->city->name }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Action Button / CTA -->
                @if($product->stock < 1)
                    <button disabled class="mt-4 w-full py-2 bg-slate-805 border border-slate-800 text-slate-500 font-bold rounded-xl text-xs cursor-not-allowed">
                        Stok Habis
                    </button>
                @elseif($product->sale_type === 'auction')
                    <a href="{{ route('marketplace.show', $product->slug) }}" class="mt-4 w-full py-2 bg-slate-800 hover:bg-slate-750 text-white font-bold rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1.5 border border-slate-700/60">
                        <span>Ikut Lelang</span>
                        <svg class="w-3.5 h-3.5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </a>
                @else
                    @if(Auth::check() && (int) $product->user_id === (int) Auth::id())
                        <a href="{{ route('marketplace.show', $product->slug) }}" class="mt-4 w-full py-2 bg-slate-900/60 hover:bg-slate-850 text-slate-400 text-center text-xs font-bold rounded-xl transition-all border border-slate-800 flex items-center justify-center">
                            Detail Produk
                        </a>
                    @else
                        <form action="{{ route('marketplace.checkout.init') }}" method="POST" class="mt-4 w-full">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="w-full py-2 bg-neon hover:bg-white text-slate-950 font-black rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1.5 shadow-md shadow-neon/10 hover:shadow-white/10">
                                <span>Beli</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </button>
                        </form>
                    @endif
                @endif

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
