@if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($products as $product)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden group hover:border-neon/50 transition-all duration-300 hover:shadow-lg hover:shadow-neon/10 flex flex-col h-full">
            <a href="{{ route('marketplace.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-slate-950">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-600 bg-slate-950">
                        <svg class="w-12 h-12 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span class="text-xs font-mono uppercase">No Image</span>
                    </div>
                @endif
                <div class="absolute top-3 right-3 flex flex-col gap-1 items-end">
                    <span class="bg-dark/80 backdrop-blur border border-slate-700 text-white text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">
                        {{ $product->condition == 'new' ? 'NEW' : 'USED' }}
                    </span>
                    @if($product->size)
                    <span class="bg-dark/80 backdrop-blur border border-slate-700 text-slate-300 text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">
                        {{ $product->size }}
                    </span>
                    @endif
                </div>
                @if($product->stock < 1)
                <div class="absolute inset-0 bg-dark/80 flex items-center justify-center">
                    <span class="text-red-500 font-black text-xl -rotate-12 border-4 border-red-500 px-4 py-2 rounded-lg">SOLD OUT</span>
                </div>
                @endif
            </a>
            <div class="p-5 flex flex-col flex-1">
                <div class="flex justify-between items-start mb-2">
                    <div class="text-xs text-neon font-mono uppercase tracking-wider">{{ $product->category->name }}</div>
                    @if($product->brand)
                        <div class="text-xs text-slate-400 font-mono uppercase tracking-wider">{{ $product->brand->name }}</div>
                    @endif
                </div>
                <h3 class="font-bold text-white text-lg leading-tight mb-3 line-clamp-2 group-hover:text-neon transition-colors">
                    <a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->title }}</a>
                </h3>
                <div class="mt-auto pt-4 border-t border-slate-800 flex justify-between items-end">
                    <div>
                        <div class="text-xs text-slate-500 mb-1">Price</div>
                        <div class="text-xl font-black text-white italic">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 overflow-hidden">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($product->seller->name) }}&background=random" class="w-full h-full object-cover">
                            </div>
                            <span class="text-xs text-slate-400 font-medium truncate max-w-[80px]">
                                {{ $product->seller->name }}
                            </span>
                        </div>
                        @if($product->seller->city)
                        <span class="text-[10px] text-slate-500 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $product->seller->city->name }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="mt-10 pagination-container">
        {{ $products->appends(request()->query())->links() }}
    </div>
@else
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">🛍️</div>
        <h3 class="text-2xl font-bold text-white mb-2">Belum ada produk</h3>
        <p class="text-slate-400 mb-8 max-w-md mx-auto">Jadilah yang pertama menjual barang di kategori ini! Pasang iklanmu sekarang dan jangkau ribuan pelari.</p>
        <a href="{{ route('marketplace.seller.products.create') }}" class="inline-block px-8 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
            Jual Barang
        </a>
    </div>
@endif
