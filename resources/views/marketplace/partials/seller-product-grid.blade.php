@if($products->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        @foreach($products as $prod)
        @php
            $isWishlisted = false;
            if (auth()->check()) {
                $isWishlisted = \App\Models\Marketplace\MarketplaceWishlist::where('user_id', auth()->id())->where('product_id', $prod->id)->exists();
            }
        @endphp
        <div class="group bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl overflow-hidden hover:border-[#B8FF00]/50 transition duration-300 flex flex-col justify-between h-full relative">
            <div>
                <!-- Image -->
                <a href="{{ route('marketplace.show', $prod->slug) }}" class="block overflow-hidden relative aspect-square bg-[#08111F]">
                    @if($prod->primaryImage)
                        <img src="{{ asset('storage/' . $prod->primaryImage->image_path) }}" alt="{{ $prod->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                            <svg class="w-10 h-10 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    @endif
                    <div class="absolute top-2 left-2 flex flex-col gap-1 items-start z-10">
                        <span class="bg-[#08111F]/80 backdrop-blur border border-[#1F2D44] text-[10px] text-white font-bold px-2 py-0.5 rounded uppercase">
                            {{ $prod->condition == 'new' ? 'Baru' : 'Bekas' }}
                        </span>
                        @if($prod->sale_type === 'auction')
                        <span class="bg-[#B8FF00]/20 backdrop-blur border border-[#B8FF00]/40 text-[#B8FF00] text-[9px] font-black px-2 py-0.5 rounded uppercase">
                            Lelang
                        </span>
                        @endif
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="absolute top-2 right-2 z-20 flex items-center gap-1.5">
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openShareModal('{{ e($prod->title) }}', '{{ route('marketplace.show', $prod->slug) }}')" class="w-8 h-8 rounded-full bg-[#08111F]/80 backdrop-blur border border-[#1F2D44] flex items-center justify-center text-slate-300 hover:text-[#B8FF00] transition-all hover:scale-110 active:scale-95" title="Bagikan Produk">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13" />
                            </svg>
                        </button>
                    </div>
                </a>

                <!-- Body -->
                <div class="p-4">
                    <div class="flex justify-between items-center gap-2 mb-1.5 min-w-0">
                        <div class="text-[10px] text-[#B8FF00] font-bold uppercase tracking-wider truncate">
                            {{ $prod->brand ? $prod->brand->name : ($prod->category ? $prod->category->name : 'Gear') }}
                        </div>
                        <span class="text-[9px] text-slate-400 font-mono flex items-center gap-1 shrink-0" title="Dilihat {{ number_format($prod->views_count ?? 0) }} kali">
                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            {{ number_format($prod->views_count ?? 0) }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-white mb-2 line-clamp-2 leading-snug hover:text-[#B8FF00] transition">
                        <a href="{{ route('marketplace.show', $prod->slug) }}">{{ $prod->title }}</a>
                    </h3>

                    <div class="text-base font-black text-[#B8FF00] font-mono mb-3">
                        Rp {{ number_format($prod->price, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Footer CTA -->
            <div class="p-4 pt-0">
                <a href="{{ route('marketplace.show', $prod->slug) }}" class="w-full py-2.5 rounded-xl bg-[#111F35] border border-[#1F2D44] text-white hover:bg-[#B8FF00] hover:text-[#08111F] font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1">
                    <span>Lihat Detail</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-10 seller-pagination-wrapper">
        {{ $products->links() }}
    </div>
@else
    <div class="bg-[#0E1A2D] border border-dashed border-[#1F2D44] rounded-3xl p-12 text-center text-slate-400">
        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <p class="text-sm font-semibold text-slate-300">Tidak ada barang jualan yang sesuai kriteria pencarian/filter.</p>
        <p class="text-xs text-slate-500 mt-1">Coba ubah kata kunci atau kata pencarian Anda.</p>
    </div>
@endif
