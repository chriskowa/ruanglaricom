@extends('layouts.pacerhub')

@section('title', 'Edit Product - RuangLari Market')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 sm:px-6 lg:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark">
    <div class="max-w-4xl mx-auto">
        
        <!-- Breadcrumb & Top Action -->
        <nav class="flex items-center justify-between gap-3 mb-8 border-b border-slate-800/80 pb-4 text-xs font-mono">
            <div class="flex items-center gap-2 text-slate-400">
                <a href="{{ route('marketplace.index') }}" class="hover:text-white transition">Marketplace</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('marketplace.seller.products.index') }}" class="hover:text-white transition">My Products</a>
                <span class="text-slate-600">/</span>
                <span class="text-white font-bold">Edit Product</span>
            </div>
            <a href="{{ route('marketplace.seller.products.index') }}" class="text-slate-400 hover:text-white text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i>
                <span>Kembali</span>
            </a>
        </nav>

        <!-- Main Form Card -->
        <div class="rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-2xl overflow-hidden">
            
            <!-- Card Header -->
            <div class="p-6 md:p-8 border-b border-slate-800 bg-[#0c121e]">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-neon"></span>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-neon">EDIT GEAR</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white uppercase tracking-tight font-sans">
                    UPDATE <span class="text-neon">PRODUCT</span>
                </h1>
                <p class="text-slate-300 text-xs md:text-sm mt-1">
                    Perbarui informasi, harga, atau stok running gear Anda.
                </p>
            </div>

            <div class="p-6 md:p-8">
                
                <!-- Error Validation Box -->
                @if ($errors->any())
                    <div class="mb-8 p-4 bg-rose-950/60 border border-rose-700/80 rounded-xl text-rose-200 text-xs">
                        <div class="flex items-center gap-2 font-bold mb-2 text-rose-300">
                            <i class="fas fa-exclamation-circle text-sm"></i>
                            <span>Mohon periksa kembali form berikut:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-rose-200/90 font-mono">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('marketplace.seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- 1. Informasi Dasar Produk -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                            <span class="text-xs font-mono font-black text-neon uppercase tracking-wider">01.</span>
                            <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-white">Informasi Dasar Gear</h2>
                        </div>

                        <!-- Product Title -->
                        <div>
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                Judul Produk <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                                class="w-full bg-[#0a0e17] border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
                            @error('title') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Category -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Kategori <span class="text-rose-400">*</span>
                                </label>
                                <div class="relative">
                                    <select name="category_id" id="category-select" required 
                                        class="w-full bg-[#0a0e17] border @error('category_id') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                                        <option value="" disabled class="bg-[#0f172a] text-slate-400">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" data-slug="{{ $category->slug }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }} class="bg-[#0f172a] text-white">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                                @error('category_id') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                            </div>

                            <!-- Brand -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Brand / Merek
                                </label>
                                <div class="relative">
                                    <select name="brand_id" id="brand-select"
                                        class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                                        <option value="" selected class="bg-[#0f172a] text-slate-400">Pilih Brand (Opsional)</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }} class="bg-[#0f172a] text-white">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Size / Ukuran -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Ukuran / Size
                                </label>
                                <input type="text" name="size" value="{{ old('size', $product->size) }}"
                                    class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                    placeholder="Contoh: US 9 / EU 42.5 / Size M">
                            </div>
                        </div>

                        <!-- Type & Condition Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Type (Read Only) -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">
                                    Tipe Produk (Terkunci)
                                </label>
                                <input type="text" value="{{ $product->type === 'digital_slot' ? 'Slot Race / Tiket Lari' : 'Barang Fisik' }}" disabled 
                                    class="w-full bg-[#0a0e17]/60 border border-slate-800 rounded-xl px-4 py-3 text-xs text-slate-400 font-mono cursor-not-allowed">
                            </div>

                            <!-- Condition -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Kondisi Barang <span class="text-rose-400">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-[#0a0e17] cursor-pointer hover:border-slate-500 transition has-[:checked]:border-white has-[:checked]:bg-white/5">
                                        <input type="radio" name="condition" value="new" class="sr-only" {{ old('condition', $product->condition) == 'new' ? 'checked' : '' }}>
                                        <span class="text-xs font-mono font-bold text-white uppercase tracking-wider">BARU (BNIB / BNWT)</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-[#0a0e17] cursor-pointer hover:border-slate-500 transition has-[:checked]:border-white has-[:checked]:bg-white/5">
                                        <input type="radio" name="condition" value="used" class="sr-only" {{ old('condition', $product->condition) == 'used' ? 'checked' : '' }}>
                                        <span class="text-xs font-mono font-bold text-white uppercase tracking-wider">BEKAS (PRE-LOVED)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Skema Jual & Harga -->
                    @php
                        $hasBids = $product->sale_type === 'auction' ? $product->bids()->exists() : false;
                        $canSwitchFromConsignment = in_array($product->consignment_status, ['none', 'requested'], true);
                    @endphp

                    <div class="space-y-4 pt-4 border-t border-slate-800">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                            <span class="text-xs font-mono font-black text-neon uppercase tracking-wider">02.</span>
                            <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-white">Skema Jual & Harga</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Sale Mode Display -->
                            <div class="p-4 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-1">
                                <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Mode Penjualan</span>
                                <p class="text-sm font-bold text-white font-mono">
                                    {{ $product->sale_type === 'auction' ? 'LELANG (BIDDING)' : 'JUAL LANGSUNG' }}
                                </p>
                            </div>

                            <!-- Fulfillment Mode Card -->
                            <div class="p-4 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-2.5">
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-300">
                                    Metode Pengiriman (Fulfillment)
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                        <input type="radio" name="fulfillment_mode" value="self_ship" class="sr-only"
                                            {{ old('fulfillment_mode', $product->fulfillment_mode) === 'self_ship' ? 'checked' : '' }}
                                            {{ ($product->fulfillment_mode === 'consignment' && ! $canSwitchFromConsignment) ? 'disabled' : '' }}>
                                        <span class="text-xs font-bold text-white font-mono">Kirim Sendiri</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                        <input type="radio" name="fulfillment_mode" value="consignment" class="sr-only"
                                            {{ old('fulfillment_mode', $product->fulfillment_mode) === 'consignment' ? 'checked' : '' }}>
                                        <span class="text-xs font-bold text-white font-mono">Titip Jual</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if($product->sale_type === 'fixed')
                            <!-- Fixed Price Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Harga (Rp) <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-mono font-bold text-slate-400">Rp</span>
                                        <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" 
                                            class="w-full bg-[#0a0e17] border @error('price') border-rose-500 @else border-slate-700 @enderror rounded-xl pl-11 pr-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
                                    </div>
                                    @error('price') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Jumlah Stok <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" 
                                        class="w-full bg-[#0a0e17] border @error('stock') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
                                    @error('stock') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @else
                            <!-- Auction Settings for Edit -->
                            <div class="p-5 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-3 bg-slate-900 rounded-lg border border-slate-800">
                                        <span class="text-[10px] font-mono text-slate-400 uppercase">Starting Price</span>
                                        <p class="text-sm font-bold text-white font-mono">Rp {{ number_format($product->starting_price ?? $product->price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-slate-900 rounded-lg border border-slate-800">
                                        <span class="text-[10px] font-mono text-slate-400 uppercase">Current Bid</span>
                                        <p class="text-sm font-bold text-neon font-mono">Rp {{ number_format($product->current_price ?? $product->starting_price ?? $product->price, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">Lelang Berakhir Pada</label>
                                        <input type="datetime-local" name="auction_end_at"
                                            value="{{ old('auction_end_at', optional($product->auction_end_at)->format('Y-m-d\\TH:i')) }}"
                                            {{ $hasBids ? 'disabled' : '' }}
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-3 text-xs text-white focus:outline-none focus:border-white transition {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">Buy Now Price</label>
                                        <input type="number" name="buy_now_price" min="0" value="{{ old('buy_now_price', $product->buy_now_price) }}"
                                            {{ $hasBids ? 'disabled' : '' }}
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-white transition {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                                    </div>
                                </div>
                                @if($hasBids)
                                    <p class="text-[11px] font-mono text-amber-400">*Pengaturan lelang terkunci karena produk telah memiliki penawaran bid.</p>
                                @endif
                            </div>
                        @endif

                        <!-- Titip Jual Dropoff Fields -->
                        <div id="consignment-fields" class="p-5 bg-[#0a0e17] rounded-xl border border-slate-700 {{ old('fulfillment_mode', $product->fulfillment_mode) === 'consignment' ? '' : 'hidden' }} space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">Metode Serah Terima</label>
                                    <input type="text" name="dropoff_method" value="{{ old('dropoff_method', optional($product->consignmentIntake)->dropoff_method) }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                        placeholder="Kirim Ekspedisi / Dropoff Langsung">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">Lokasi Pengirim / Titik Temu</label>
                                    <input type="text" name="dropoff_location" value="{{ old('dropoff_location', optional($product->consignmentIntake)->dropoff_location) }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                        placeholder="Kota / Daerah Seller">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Deskripsi & Foto Produk -->
                    <div class="space-y-4 pt-4 border-t border-slate-800">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                            <span class="text-xs font-mono font-black text-neon uppercase tracking-wider">03.</span>
                            <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-white">Deskripsi & Foto Gear</h2>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                Deskripsi Lengkap Produk <span class="text-rose-400">*</span>
                            </label>
                            <textarea name="description" rows="5" required 
                                class="w-full bg-[#0a0e17] border @error('description') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">{{ old('description', $product->description) }}</textarea>
                            @error('description') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                        </div>

                        <!-- Multi-Image Management (Max 4 Photos) -->
                        <div class="space-y-3">
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200">
                                Galeri Foto Produk (Maksimal 4 Foto)
                            </label>
                            
                            <!-- Existing Photos -->
                            @if($product->images->count() > 0)
                                <div class="space-y-2 mb-4">
                                    <span class="text-[11px] font-mono text-slate-400 uppercase">Foto Saat Ini:</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                                        @foreach($product->images as $img)
                                            <div class="relative aspect-square rounded-xl border border-slate-700 bg-slate-950 overflow-hidden group">
                                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                                                
                                                @if($img->is_primary)
                                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-neon text-dark text-[8px] font-black uppercase font-mono shadow">
                                                        UTAMA
                                                    </span>
                                                @endif

                                                <label class="absolute inset-0 bg-rose-950/80 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center cursor-pointer">
                                                    <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="w-4 h-4 rounded border-rose-500 text-rose-600 focus:ring-rose-500 mb-1">
                                                    <span class="text-[10px] font-bold text-rose-200 uppercase font-mono">Hapus Foto</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-mono">*Centang foto yang ingin dihapus saat klik Update Product.</p>
                                </div>
                            @endif

                            @php($availableSlots = max(0, 4 - $product->images->count()))
                            @if($availableSlots > 0)
                                <div class="space-y-2">
                                    <span class="text-[11px] font-mono text-slate-300 uppercase font-bold">Tambah Foto Baru (Tersisa {{ $availableSlots }} Slot):</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                                        @for($i = 0; $i < $availableSlots; $i++)
                                            <div class="relative aspect-square rounded-xl border-2 border-dashed border-slate-700 bg-[#0a0e17] hover:border-slate-500 hover:bg-slate-900 transition overflow-hidden flex flex-col items-center justify-center text-center group">
                                                <label for="new-file-{{ $i }}" class="w-full h-full flex flex-col items-center justify-center p-3 cursor-pointer">
                                                    <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-white mb-1.5 transition">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </div>
                                                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-300">Tambah Foto</span>
                                                    <input id="new-file-{{ $i }}" type="file" name="images[]" accept="image/*" class="hidden">
                                                </label>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Submit Button Area -->
                    <div class="flex items-center justify-between pt-6 border-t border-slate-800">
                        <a href="{{ route('marketplace.seller.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 text-xs font-bold font-mono transition">
                            Batal
                        </a>

                        <button type="submit" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-200 text-slate-950 font-black text-xs uppercase tracking-wider transition-all flex items-center gap-2 shadow-lg shadow-white/5">
                            <i class="fas fa-check text-xs"></i>
                            <span>Update Product</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    let img = preview ? preview.querySelector('img') : null;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (!img) {
                img = document.createElement('img');
                img.className = "h-36 w-36 rounded-lg border border-slate-700 object-cover";
                preview.appendChild(img);
            }
            img.src = e.target.result;
            img.classList.remove('hidden');
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const brandOptions = brandSelect ? Array.from(brandSelect.querySelectorAll('option')) : [];

    function filterBrands() {
        if (!categorySelect || !brandSelect) return;
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const selectedCategorySlug = selectedOption ? selectedOption.dataset.slug : null;
        const currentBrand = brandSelect.value;
        let isCurrentValid = false;

        brandOptions.forEach(option => {
            if (option.value === "") return;
            const categories = JSON.parse(option.dataset.categories || '[]');
            const isMatch = !selectedCategorySlug || categories.includes(selectedCategorySlug);
            option.hidden = !isMatch;
            option.disabled = !isMatch;
            if (isMatch && option.value == currentBrand) {
                isCurrentValid = true;
            }
        });

        if (!isCurrentValid && currentBrand !== "") {
            brandSelect.value = "";
        }
    }

    if (categorySelect) categorySelect.addEventListener('change', filterBrands);
    filterBrands();

    const radios = document.querySelectorAll('input[name="fulfillment_mode"]');
    const consignmentFields = document.getElementById('consignment-fields');
    radios.forEach(r => {
        r.addEventListener('change', function() {
            if (consignmentFields) {
                consignmentFields.classList.toggle('hidden', this.value !== 'consignment');
            }
        });
    });
});
</script>
@endsection
