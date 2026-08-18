@forelse($products as $product)
<tr class="hover:bg-slate-800/40 transition-colors border-b border-slate-850/60 font-sans" id="product-row-{{ $product->id }}">
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-950 border border-slate-800 shrink-0">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-700"><i class="fas fa-image"></i></div>
                @endif
            </div>
            <div class="min-w-0">
                <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" class="text-white font-bold text-sm hover:text-neon transition truncate block">
                    {{ $product->title }}
                </a>
                <p class="text-slate-500 text-xs uppercase font-medium mt-0.5">{{ optional($product->category)->name ?? 'General' }} | {{ optional($product->brand)->name ?? 'No Brand' }}</p>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="space-y-1">
            <p class="text-white font-bold text-xs">{{ optional($product->seller)->name ?? 'Seller' }}</p>
            <p class="text-slate-400 text-xs font-medium">{{ optional($product->seller)->email ?? '-' }}</p>
        </div>
    </td>
    <td class="px-6 py-4">
        <p class="text-neon font-black text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
        <p class="text-slate-400 text-xs font-semibold">Stok: {{ $product->stock }}</p>
    </td>
    <td class="px-6 py-4">
        @if($product->approval_status === 'pending')
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1 text-[10px] uppercase font-bold text-amber-300 bg-amber-500/20 border border-amber-500/30 px-2.5 py-1 rounded-md">
                    <i class="fas fa-clock text-[9px]"></i> Menunggu Approval
                </span>
                <div class="flex items-center gap-1.5 pt-1">
                    <button type="button" 
                            @click="approveProduct({{ $product->id }})" 
                            class="px-2 py-1 rounded bg-emerald-500 hover:bg-emerald-400 text-dark font-black text-[10px] uppercase transition shadow">
                        Setujui
                    </button>
                    <button type="button" 
                            @click="rejectProduct({{ $product->id }})" 
                            class="px-2 py-1 rounded bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-500/40 text-[10px] uppercase transition">
                        Tolak
                    </button>
                </div>
            </div>
        @elseif($product->approval_status === 'rejected')
            <div>
                <span class="inline-flex items-center gap-1 text-[10px] uppercase font-bold text-rose-300 bg-rose-500/20 border border-rose-500/30 px-2.5 py-1 rounded-md" title="{{ $product->rejection_reason }}">
                    <i class="fas fa-times-circle text-[9px]"></i> Ditolak
                </span>
                @if($product->rejection_reason)
                    <p class="text-xs text-slate-500 font-medium mt-1 truncate max-w-[150px]" title="{{ $product->rejection_reason }}">
                        {{ $product->rejection_reason }}
                    </p>
                @endif
            </div>
        @else
            <span class="inline-flex items-center gap-1 text-[10px] uppercase font-bold text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-1 rounded-md">
                <i class="fas fa-check-circle text-[9px]"></i> Disetujui
            </span>
        @endif
    </td>
    <td class="px-6 py-4">
        <div class="flex items-center gap-2">
            <!-- Featured Toggle -->
            <button @click="toggleFeatured({{ $product->id }})" 
                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition border"
                    :class="$el.getAttribute('data-featured') === '1' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-slate-950 text-slate-500 border-slate-800 hover:text-amber-400'"
                    data-featured="{{ $product->is_featured ? '1' : '0' }}"
                    id="btn-featured-{{ $product->id }}">
                <i class="fas fa-star text-[9px]"></i>
                <span id="text-featured-{{ $product->id }}">{{ $product->is_featured ? 'Featured' : '+ Featured' }}</span>
            </button>

            <!-- Active / Hide Toggle -->
            <button @click="toggleActive({{ $product->id }})" 
                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition border"
                    :class="$el.getAttribute('data-active') === '1' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border-rose-500/40'"
                    data-active="{{ $product->is_active ? '1' : '0' }}"
                    id="btn-active-{{ $product->id }}">
                <span id="text-active-{{ $product->id }}">{{ $product->is_active ? 'Aktif' : 'Hidden' }}</span>
            </button>
        </div>
    </td>
    <td class="px-6 py-4 text-right">
        <button @click="deleteProduct({{ $product->id }})" class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 hover:bg-rose-500 hover:text-white transition text-xs" title="Hapus Produk">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm font-medium uppercase tracking-wider">
        Tidak ada produk marketplace yang sesuai dengan filter.
    </td>
</tr>
@endforelse
