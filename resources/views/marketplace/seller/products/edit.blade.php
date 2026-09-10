@extends('layouts.pacerhub')

@section('title', 'Edit Product - RuangLari Market')

@section('content')
<div class="min-h-screen pt-6 sm:pt-8 md:pt-10 pb-20 px-4 sm:px-6 lg:px-8 bg-slate-950 text-slate-200 font-sans selection:bg-neon selection:text-dark"
     x-data="productEditForm()">
    <div class="max-w-7xl 2xl:max-w-8xl mx-auto w-full">
        
        <!-- Breadcrumb & Top Action -->
        <nav class="flex items-center justify-between gap-3 mb-6 border-b border-slate-800 pb-4 text-xs font-medium">
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

        <!-- 2-Column Main Layout: Form (Left) + Live Preview (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- Left Column: Form (7 Columns) -->
            <div class="lg:col-span-7 space-y-6">
                
                <div class="rounded-xl bg-slate-900 border border-slate-800 shadow-2xl overflow-hidden">
                    
                    <!-- Card Header -->
                    <div class="p-6 md:p-8 border-b border-slate-800 bg-slate-950/60">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-neon"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-neon font-mono">SELLER DASHBOARD</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black text-white uppercase tracking-tight font-sans">
                            UPDATE <span class="text-neon">PRODUCT</span>
                        </h1>
                        <p class="text-slate-300 text-xs md:text-sm mt-1">
                            Perbarui informasi, harga, foto, atau stok running gear Anda.
                        </p>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        
                        <!-- Error Validation Box -->
                        @if ($errors->any())
                            <div class="p-4 bg-rose-950/80 border border-rose-700 rounded-md text-rose-200 text-xs">
                                <div class="flex items-center gap-2 font-bold mb-2 text-rose-300">
                                    <i class="fas fa-exclamation-circle text-sm"></i>
                                    <span>Mohon periksa kembali form berikut:</span>
                                </div>
                                <ul class="list-disc list-inside space-y-1 text-rose-200/90 font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $shoeSizes = $product->meta_data['shoe_sizes'] ?? [];
                            if (empty($shoeSizes) && !empty($product->size)) {
                                if (preg_match('/US\s*([\d\.]+)/i', $product->size, $m)) $shoeSizes['us'] = $m[1];
                                if (preg_match('/UK\s*([\d\.]+)/i', $product->size, $m)) $shoeSizes['uk'] = $m[1];
                                if (preg_match('/EU\s*([\d\.]+)/i', $product->size, $m)) $shoeSizes['eu'] = $m[1];
                                if (preg_match('/([\d\.]+)\s*CM/i', $product->size, $m)) $shoeSizes['cm'] = $m[1];
                            }
                            $isShoeCategory = false;
                            if ($product->category) {
                                $catSlug = strtolower($product->category->slug ?? '');
                                $catName = strtolower($product->category->name ?? '');
                                if (str_contains($catSlug, 'sepatu') || str_contains($catSlug, 'shoe') || str_contains($catName, 'sepatu') || str_contains($catName, 'shoe')) {
                                    $isShoeCategory = true;
                                }
                            }
                            if (!empty($shoeSizes)) {
                                $isShoeCategory = true;
                            }
                        @endphp

                        <form action="{{ route('marketplace.seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="syncFileInput()">
                            @csrf
                            @method('PUT')

                            <!-- 01. Informasi Dasar Produk -->
                            <div class="p-5 md:p-6 rounded-lg bg-slate-950/80 border border-slate-800 shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">01</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Informasi Dasar Gear</h2>
                                </div>

                                <!-- Product Title -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                        Judul Produk <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" name="title" x-model="title" required
                                        class="w-full bg-slate-900 border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-md px-4 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition"
                                        placeholder="Contoh: Nike Vaporfly 3 Ekiden Edition 2024">
                                    @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Category -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Kategori <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="category_id" id="category-select" @change="updateCategoryText($event)" required 
                                                class="w-full bg-slate-900 border @error('category_id') border-rose-500 @else border-slate-700 @enderror rounded-md px-4 py-2.5 text-xs text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition cursor-pointer [&>option]:bg-slate-900 [&>option]:text-white">
                                                <option value="" disabled class="text-slate-400">Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" data-slug="{{ $category->slug }}" data-name="{{ $category->name }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                        @error('category_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Brand -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Brand / Merek
                                        </label>
                                        <div class="relative">
                                            <select name="brand_id" id="brand-select" @change="updateBrandText($event)"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition cursor-pointer [&>option]:bg-slate-900 [&>option]:text-white">
                                                <option value="" selected class="text-slate-400">Pilih Brand (Opsional)</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" data-name="{{ $brand->name }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Size / Ukuran Section (Dynamic Shoe vs Standard) -->
                                <div class="p-4 bg-slate-900 border border-slate-800 rounded-md space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-bold uppercase tracking-wider text-slate-100">
                                                Ukuran / Size
                                            </label>
                                            <span x-show="isShoeFormat" class="text-[10px] font-bold text-neon bg-neon/10 px-2 py-0.5 rounded border border-neon/30 uppercase font-mono">Format Sepatu</span>
                                            <span x-show="!isShoeFormat" class="text-[10px] font-bold text-slate-300 bg-slate-950 px-2 py-0.5 rounded border border-slate-800 uppercase font-mono">Format Umum</span>
                                        </div>
                                        <button type="button" @click="toggleShoeFormat()" 
                                                class="text-[11px] text-slate-300 hover:text-neon underline font-semibold transition cursor-pointer">
                                            <span x-text="isShoeFormat ? 'Ubah ke format teks umum' : 'Gunakan format ukuran sepatu (US/UK/EU/CM)'"></span>
                                        </button>
                                    </div>

                                    <!-- Shoe Multi-system Input Panel -->
                                    <div x-show="isShoeFormat" class="space-y-3">
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">US (Men/Unisex)</label>
                                                <input type="text" name="shoe_sizes[us]" x-model="shoeSizeUs" @input="updateCombinedSize()" 
                                                       placeholder="9.5"
                                                       class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">UK</label>
                                                <input type="text" name="shoe_sizes[uk]" x-model="shoeSizeUk" @input="updateCombinedSize()" 
                                                       placeholder="8.5"
                                                       class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">EU</label>
                                                <input type="text" name="shoe_sizes[eu]" x-model="shoeSizeEu" @input="updateCombinedSize()" 
                                                       placeholder="43"
                                                       class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">CM (Panjang)</label>
                                                <input type="text" name="shoe_sizes[cm]" x-model="shoeSizeCm" @input="updateCombinedSize()" 
                                                       placeholder="27.5"
                                                       class="w-full bg-slate-950 border border-slate-700 rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                        </div>

                                        <!-- Live preview of combined size string -->
                                        <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-xs">
                                            <span class="text-[11px] text-slate-300">Ringkasan Ukuran Produk:</span>
                                            <span class="font-mono font-bold text-white bg-slate-950 px-3 py-1 rounded border border-slate-800 text-xs" 
                                                  x-text="size || 'Isi setidaknya satu ukuran di atas'"></span>
                                        </div>
                                    </div>

                                    <!-- Standard Single Size Input for Non-Shoe Products -->
                                    <div x-show="!isShoeFormat">
                                        <input type="text" x-model="size"
                                            class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition"
                                            :placeholder="sizePlaceholder">
                                    </div>

                                    <!-- Hidden input that actually carries the combined size to form submission -->
                                    <input type="hidden" name="size" :value="size">
                                </div>

                                <!-- Type & Condition Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Type (Read Only) -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                            Tipe Produk (Terkunci)
                                        </label>
                                        <input type="text" value="{{ $product->type === 'digital_slot' ? 'Slot Race / Tiket Lari' : 'Barang Fisik' }}" disabled 
                                            class="w-full bg-slate-900 border border-slate-800 rounded-md px-4 py-2.5 text-xs text-slate-400 cursor-not-allowed font-medium">
                                    </div>

                                    <!-- Condition -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Kondisi Barang <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="relative flex items-center justify-between p-3 rounded-md border cursor-pointer transition select-none"
                                                   :class="condition === 'new' ? 'bg-slate-800 border-neon ring-1 ring-neon/40 text-white' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-slate-500 hover:text-white'">
                                                <input type="radio" name="condition" value="new" x-model="condition" class="sr-only">
                                                <div class="flex flex-col pr-1 min-w-0">
                                                    <span class="text-xs font-black uppercase tracking-wider" :class="condition === 'new' ? 'text-white' : 'text-slate-200'">BARU (BNIB)</span>
                                                    <span class="text-[10px] mt-0.5 leading-tight truncate" :class="condition === 'new' ? 'text-slate-200' : 'text-slate-400'">Brand New In Box/Tag</span>
                                                </div>
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition"
                                                     :class="condition === 'new' ? 'border-neon bg-neon' : 'border-slate-500 bg-transparent'">
                                                    <div class="w-1.5 h-1.5 rounded-full" :class="condition === 'new' ? 'bg-slate-950' : 'bg-transparent'"></div>
                                                </div>
                                            </label>
                                            <label class="relative flex items-center justify-between p-3 rounded-md border cursor-pointer transition select-none"
                                                   :class="condition === 'used' ? 'bg-slate-800 border-neon ring-1 ring-neon/40 text-white' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-slate-500 hover:text-white'">
                                                <input type="radio" name="condition" value="used" x-model="condition" class="sr-only">
                                                <div class="flex flex-col pr-1 min-w-0">
                                                    <span class="text-xs font-black uppercase tracking-wider" :class="condition === 'used' ? 'text-white' : 'text-slate-200'">BEKAS (USED)</span>
                                                    <span class="text-[10px] mt-0.5 leading-tight truncate" :class="condition === 'used' ? 'text-slate-200' : 'text-slate-400'">Pernah dipakai, baik</span>
                                                </div>
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition"
                                                     :class="condition === 'used' ? 'border-neon bg-neon' : 'border-slate-500 bg-transparent'">
                                                    <div class="w-1.5 h-1.5 rounded-full" :class="condition === 'used' ? 'bg-slate-950' : 'bg-transparent'"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 02. Skema Jual & Harga -->
                            @php
                                $hasBids = $product->sale_type === 'auction' ? $product->bids()->exists() : false;
                                $canSwitchFromConsignment = in_array($product->consignment_status, ['none', 'requested'], true);
                            @endphp

                            <div class="p-5 md:p-6 rounded-lg bg-slate-950/80 border border-slate-800 shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">02</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Skema Jual & Harga</h2>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Sale Mode Display -->
                                    <div class="p-4 bg-slate-900 rounded-md border border-slate-800 space-y-1">
                                        <span class="text-[10px] uppercase tracking-wider text-slate-300 font-semibold">Mode Penjualan</span>
                                        <p class="text-sm font-bold text-white">
                                            {{ $product->sale_type === 'auction' ? 'LELANG (BIDDING)' : 'JUAL LANGSUNG' }}
                                        </p>
                                    </div>

                                    <!-- Fulfillment Mode Card -->
                                    <div class="p-4 bg-slate-900 rounded-md border border-slate-800 space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100">
                                                Metode Pengiriman
                                            </label>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-neon/15 text-neon border border-neon/30 uppercase font-mono">Verified & IG Promo</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="flex items-center justify-center p-2.5 rounded-md border border-slate-700 bg-slate-950 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/15">
                                                <input type="radio" name="fulfillment_mode" value="self_ship" class="sr-only"
                                                    {{ old('fulfillment_mode', $product->fulfillment_mode) === 'self_ship' ? 'checked' : '' }}
                                                    {{ ($product->fulfillment_mode === 'consignment' && ! $canSwitchFromConsignment) ? 'disabled' : '' }}>
                                                <span class="text-xs font-bold text-white">Kirim Sendiri</span>
                                            </label>
                                            <label class="flex items-center justify-center p-2.5 rounded-md border border-slate-700 bg-slate-950 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/15">
                                                <input type="radio" name="fulfillment_mode" value="consignment" class="sr-only"
                                                    {{ old('fulfillment_mode', $product->fulfillment_mode) === 'consignment' ? 'checked' : '' }}>
                                                <span class="text-xs font-bold text-white">Titip Jual</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kelebihan Titip Jual di RuangLari Callout Card -->
                                <div class="p-4 rounded-md bg-slate-900 border border-neon/30 space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-neon text-slate-950 font-mono">
                                                KELEBIHAN TITIP JUAL
                                            </span>
                                            <span class="text-xs font-bold text-white">Kenapa titip jual di RuangLari?</span>
                                        </div>
                                        <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">Layanan resmi komunitas lari</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        <div class="flex items-start gap-2.5 p-3 rounded-md bg-slate-950 border border-slate-800">
                                            <div class="w-6 h-6 rounded bg-neon/15 text-neon flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fab fa-instagram text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-white text-xs">Dibantu Promosi di IG @ruanglari</p>
                                                <p class="text-slate-300 text-[11px] leading-relaxed mt-0.5">
                                                    Produk Anda dibantu promosikan di feeds dan story Instagram resmi <strong>@ruanglari</strong> agar cepat terjual ke ribuan pelari aktif.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2.5 p-3 rounded-md bg-slate-950 border border-slate-800">
                                            <div class="w-6 h-6 rounded bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fas fa-shield-alt text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-white text-xs">Seller & Barang Pasti Terverifikasi</p>
                                                <p class="text-slate-300 text-[11px] leading-relaxed mt-0.5">
                                                    Seller dan kondisi fisik barang diverifikasi langsung oleh admin sehingga meminimalisir penipuan dan membuat pembeli jauh lebih percaya.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($product->sale_type === 'fixed')
                                    <!-- Fixed Price Fields -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Harga (Rp) <span class="text-rose-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                                <input type="number" name="price" x-model="price" min="0" 
                                                    class="w-full bg-slate-900 border @error('price') border-rose-500 @else border-slate-700 @enderror rounded-md pl-11 pr-4 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition font-mono font-bold">
                                            </div>
                                            @error('price') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Jumlah Stok <span class="text-rose-400">*</span>
                                            </label>
                                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" 
                                                class="w-full bg-slate-900 border @error('stock') border-rose-500 @else border-slate-700 @enderror rounded-md px-4 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition font-mono">
                                            @error('stock') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @else
                                    <!-- Auction Settings for Edit -->
                                    <div class="p-4 bg-slate-900 rounded-md border border-slate-800 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                                                <span class="text-[10px] text-slate-400 uppercase font-semibold">Starting Price</span>
                                                <p class="text-sm font-bold text-white font-mono">Rp {{ number_format($product->starting_price ?? $product->price, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                                                <span class="text-[10px] text-slate-400 uppercase font-semibold">Current Bid</span>
                                                <p class="text-sm font-bold text-neon font-mono">Rp {{ number_format($product->current_price ?? $product->starting_price ?? $product->price, 0, ',', '.') }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Lelang Berakhir Pada</label>
                                                <input type="datetime-local" name="auction_end_at"
                                                    value="{{ old('auction_end_at', optional($product->auction_end_at)->format('Y-m-d\\TH:i')) }}"
                                                    {{ $hasBids ? 'disabled' : '' }}
                                                    class="w-full bg-slate-950 border border-slate-700 rounded-md px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-neon transition {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Buy Now Price</label>
                                                <input type="number" name="buy_now_price" min="0" value="{{ old('buy_now_price', $product->buy_now_price) }}"
                                                    {{ $hasBids ? 'disabled' : '' }}
                                                    class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-sm text-white focus:outline-none focus:border-neon transition font-mono {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                                            </div>
                                        </div>
                                        @if($hasBids)
                                            <p class="text-[11px] text-amber-400 font-semibold">*Pengaturan lelang terkunci karena produk telah memiliki penawaran bid.</p>
                                        @endif
                                    </div>
                                @endif

                                <!-- Titip Jual Dropoff & Owner Contact Fields -->
                                <div id="consignment-fields" class="p-4 bg-slate-900 rounded-md border border-slate-800 {{ old('fulfillment_mode', $product->fulfillment_mode) === 'consignment' ? '' : 'hidden' }} space-y-4">
                                    <div class="border-b border-slate-800 pb-2">
                                        <p class="text-xs font-bold text-white uppercase tracking-wider">Informasi Pemilik & Penyerahan Barang</p>
                                        <p class="text-xs text-slate-300">Kontak pemilik asli jika Anda menjualkan barang orang lain. Admin akan memverifikasi sebelum status aktif.</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Nama Pemilik Asli (Opsional)</label>
                                            <input type="text" name="owner_name" value="{{ old('owner_name', optional($product->consignmentIntake)->owner_name) }}"
                                                class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Contoh: Budi (Teman) / FB Seller">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">No. WhatsApp Pemilik (Opsional)</label>
                                            <input type="text" name="owner_phone" value="{{ old('owner_phone', optional($product->consignmentIntake)->owner_phone) }}"
                                                class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Contoh: 08123456789">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Metode Serah Terima</label>
                                            <input type="text" name="dropoff_method" value="{{ old('dropoff_method', optional($product->consignmentIntake)->dropoff_method) }}"
                                                class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Kirim Ekspedisi / Dropoff Langsung">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Lokasi Pengirim / Kota</label>
                                            <input type="text" name="dropoff_location" value="{{ old('dropoff_location', optional($product->consignmentIntake)->dropoff_location) }}"
                                                class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Kota / Daerah Asal Barang">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 03. Detail Race Slot (Otomatis muncul jika digital_slot) -->
                            <div x-show="productType === 'digital_slot'" x-cloak class="p-5 md:p-6 rounded-lg bg-slate-950/80 border border-slate-800 shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">03</span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Detail Race Slot & BIB</h3>
                                </div>
                                <div class="p-4 bg-slate-900 rounded-md border border-slate-800 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Nama Race / Event</label>
                                            <input type="text" name="meta_data[race_name]" x-model="raceName" placeholder="Contoh: Borobudur Marathon 2024" 
                                                class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Tanggal Race</label>
                                            <input type="date" name="meta_data[race_date]" x-model="raceDate"
                                                class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white focus:outline-none focus:border-neon transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Kebijakan Transfer BIB / Nama</label>
                                        <input type="text" name="meta_data[transfer_policy]" x-model="transferPolicy" placeholder="Contoh: Bisa ganti nama resmi sampai H-14 race" 
                                            class="w-full bg-slate-950 border border-slate-700 rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition">
                                    </div>
                                </div>
                            </div>

                            <!-- 04. Deskripsi & Foto Produk -->
                            <div class="p-5 md:p-6 rounded-lg bg-slate-950/80 border border-slate-800 shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">04</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Deskripsi & Foto Gear</h2>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                        Deskripsi Lengkap Produk <span class="text-rose-400">*</span>
                                    </label>
                                    <textarea name="description" rows="5" required 
                                        class="w-full bg-slate-900 border @error('description') border-rose-500 @else border-slate-700 @enderror rounded-md px-4 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">{{ old('description', $product->description) }}</textarea>
                                    @error('description') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Multi-Image Management (Dropzone Max 4 Photos) -->
                                <div class="space-y-4 pt-2 border-t border-slate-800">
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100">
                                            Galeri Foto Produk (Maksimal 4 Foto)
                                        </label>
                                        <span class="text-xs text-slate-300 uppercase font-semibold">
                                            <span x-text="totalImagesCount" class="text-neon font-mono font-bold">0</span> / 4 Foto Aktif
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-400">
                                        Atur galeri foto produk. Tarik &amp; geser (drag and drop) posisi foto untuk menentukan urutan—foto di posisi pertama otomatis menjadi <strong>Foto Utama (Cover)</strong>.
                                    </p>

                                    <!-- Hidden inputs for primary image -->
                                    <input type="hidden" name="primary_image_id" :value="primaryImageId">
                                    <input type="hidden" name="primary_new_image_index" :value="primaryNewImageIndex">

                                    <!-- Hidden inputs for delete_images -->
                                    <template x-for="item in deletedExistingImages" :key="item.id">
                                        <input type="hidden" name="delete_images[]" :value="item.id">
                                    </template>

                                    <!-- Hidden input for new uploaded files -->
                                    <input type="file" id="edit-images-input" name="images[]" multiple accept="image/*" class="hidden" @change="handleFilesFromInput($event)">

                                    <!-- Error Alert Banner for Images (Client-Side Feedback) -->
                                    <div x-show="imageError" x-cloak class="p-3 rounded-md bg-rose-950/80 border border-rose-700 text-rose-200 text-xs flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-rose-400"></i>
                                            <span x-text="imageError"></span>
                                        </div>
                                        <button type="button" @click="imageError = null" class="text-rose-400 hover:text-white text-xs cursor-pointer ml-2">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Unified Active Photos Grid (Draggable) -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5" x-show="activeImages.length > 0" x-cloak>
                                        <template x-for="(item, idx) in activeImages" :key="item.key">
                                            <div 
                                                draggable="true"
                                                @dragstart="onDragStart($event, item.key)"
                                                @dragover.prevent="onDragOver($event, item.key)"
                                                @dragenter.prevent="onDragEnter($event, item.key)"
                                                @dragleave="onDragLeave($event, item.key)"
                                                @drop.prevent="onDrop($event, item.key)"
                                                @dragend="onDragEnd($event)"
                                                class="relative aspect-square rounded-md border bg-slate-950 overflow-hidden flex flex-col items-center justify-center text-center group shadow-md transition-all select-none cursor-grab active:cursor-grabbing"
                                                :class="{
                                                    'opacity-30 scale-95 border-dashed border-slate-500': draggedKey === item.key,
                                                    'border-neon ring-2 ring-neon/40 scale-[1.02] z-20': dragOverKey === item.key && draggedKey !== item.key,
                                                    'border-neon/80 ring-1 ring-neon/30': idx === 0 && draggedKey !== item.key && dragOverKey !== item.key,
                                                    'border-slate-700 hover:border-slate-500': idx !== 0 && draggedKey !== item.key && dragOverKey !== item.key
                                                }"
                                            >
                                                <img :src="item.url" class="w-full h-full object-cover pointer-events-none">
                                                
                                                <!-- Slot Badge -->
                                                <div class="absolute top-2 left-2 flex flex-col gap-1 pointer-events-none z-10">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider shadow"
                                                          :class="idx === 0 ? 'bg-neon text-dark font-black' : 'bg-slate-900 text-slate-200 border border-slate-700 font-bold'"
                                                          x-text="idx === 0 ? 'UTAMA' : 'FOTO ' + (idx + 1)"></span>
                                                    <template x-if="item.type === 'new'">
                                                        <span class="px-1.5 py-0.5 rounded bg-cyan-950/90 border border-cyan-700 text-cyan-300 text-[8px] font-bold uppercase tracking-wider shadow">
                                                            BARU
                                                        </span>
                                                    </template>
                                                </div>
                                                
                                                <!-- Remove Button -->
                                                <button type="button" @click.stop="removeImage(item.key)" 
                                                        class="absolute top-2 right-2 w-6 h-6 rounded-md bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-xs shadow-md transition cursor-pointer z-10"
                                                        :title="item.type === 'existing' ? 'Hapus foto dari produk' : 'Batalkan foto baru ini'">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>

                                                <!-- Bottom Bar: Drag Handle + Make Primary Button -->
                                                <div class="absolute bottom-2 inset-x-2 flex items-center justify-between pointer-events-none z-10">
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-900/90 border border-slate-700 text-slate-300 group-hover:text-white text-[10px] flex items-center gap-1 shadow">
                                                        <i class="fas fa-grip-vertical text-[9px]"></i>
                                                        <span class="text-[9px] font-medium hidden sm:inline">Geser</span>
                                                    </span>

                                                    <template x-if="idx > 0">
                                                        <button type="button" @click.stop="setAsPrimary(item.key)"
                                                                class="pointer-events-auto px-2 py-0.5 rounded bg-slate-900/90 hover:bg-slate-800 border border-slate-700 hover:border-neon text-slate-200 hover:text-neon text-[9px] font-bold transition shadow cursor-pointer">
                                                            Jadikan Utama
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Add more slot if less than 4 -->
                                        <template x-if="activeImages.length > 0 && availableSlots > 0">
                                            <div @click="triggerFileInput()"
                                                 @dragover.prevent="isAddDragging = true"
                                                 @dragleave.prevent="isAddDragging = false"
                                                 @drop.prevent="isAddDragging = false; handleFilesDrop($event)"
                                                 class="relative aspect-square rounded-md border-2 border-dashed border-slate-700 bg-slate-900 hover:border-slate-500 hover:bg-slate-800 cursor-pointer transition flex flex-col items-center justify-center text-center p-3 group select-none"
                                                 :class="isAddDragging ? 'border-neon bg-neon/10' : ''">
                                                <div class="w-8 h-8 rounded-md bg-slate-950 border border-slate-800 flex items-center justify-center text-slate-300 group-hover:text-white mb-1.5 transition">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </div>
                                                <span class="text-xs font-bold uppercase tracking-wider text-slate-200">Tambah Foto</span>
                                                <span class="text-xs text-slate-400 mt-0.5" x-text="'Tersisa ' + availableSlots + ' slot'"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Drag Instruction Helper -->
                                    <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-2" x-show="activeImages.length > 0" x-cloak>
                                        <i class="fas fa-arrows-alt text-slate-500 text-[11px]"></i>
                                        <span>Tarik dan geser kartu foto untuk mengatur urutan. Foto di urutan pertama otomatis menjadi <strong>Foto Utama (Cover)</strong>.</span>
                                    </div>

                                    <!-- Dropzone for Adding More Photos (shown when 0 active photos) -->
                                    <div 
                                        id="product-dropzone"
                                        class="relative border-2 border-dashed rounded-md p-6 sm:p-8 text-center transition-all cursor-pointer bg-slate-900 group select-none"
                                        :class="isDragging ? 'border-neon bg-neon/10 scale-[1.01]' : 'border-slate-700 hover:border-slate-500 hover:bg-slate-850'"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleFilesDrop($event)"
                                        @click="triggerFileInput()"
                                        x-show="activeImages.length === 0"
                                    >
                                        <div class="flex flex-col items-center justify-center space-y-2.5 pointer-events-none">
                                            <div class="w-10 h-10 rounded-md bg-slate-950 border border-slate-800 flex items-center justify-center text-slate-300 group-hover:text-neon group-hover:border-neon/50 transition shadow-inner">
                                                <i class="fas fa-cloud-arrow-up text-lg text-neon"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-white">
                                                    Tarik &amp; letakkan foto di sini, atau <span class="text-neon underline">pilih dari galeri</span>
                                                </p>
                                                <p class="text-xs text-slate-400 mt-0.5">
                                                    Maksimal 4 foto (JPEG, PNG, WEBP hingga 3MB)
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deleted Photos Tray -->
                                    <div class="mt-4 p-4 rounded-md bg-rose-950/40 border border-rose-800/60 space-y-3" x-show="deletedExistingImages.length > 0" x-cloak>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 text-rose-300">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                                <span class="text-xs font-bold uppercase tracking-wider">Foto yang Ditandai untuk Dihapus (<span x-text="deletedExistingImages.length"></span>)</span>
                                            </div>
                                            <span class="text-[11px] text-slate-400">Foto akan terhapus permanen saat produk diupdate.</span>
                                        </div>

                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                            <template x-for="item in deletedExistingImages" :key="item.key">
                                                <div class="relative aspect-square rounded-md border border-rose-800/60 bg-slate-950 overflow-hidden opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition flex flex-col items-center justify-center">
                                                    <img :src="item.url" class="w-full h-full object-cover">
                                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-rose-600 text-white text-[9px] font-black uppercase shadow">
                                                        AKAN DIHAPUS
                                                    </span>
                                                    <button type="button" @click="restoreDeleted(item.key)"
                                                            class="absolute bottom-2 inset-x-2 py-1 rounded-md bg-slate-900/90 hover:bg-slate-800 border border-slate-700 text-slate-200 text-[10px] font-bold flex items-center justify-center gap-1 shadow cursor-pointer transition">
                                                        <i class="fas fa-undo text-[9px]"></i>
                                                        <span>Batal Hapus</span>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    @error('images') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                    @error('images.*') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Submit Button Area -->
                            <div class="p-4 md:p-5 rounded-lg bg-slate-950/80 border border-slate-800 shadow-sm flex items-center justify-between">
                                <a href="{{ route('marketplace.seller.products.index') }}" class="px-5 py-2.5 rounded-md border border-slate-700 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 text-xs font-bold transition">
                                    Batal
                                </a>

                                <button type="submit" class="px-6 py-3 rounded-md bg-neon hover:bg-white text-slate-950 font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-2 shadow-md cursor-pointer">
                                    <i class="fas fa-check"></i>
                                    <span>Simpan Perubahan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Product Card Preview (5 Columns Sticky) -->
            <div class="lg:col-span-5 sticky top-28 space-y-4 hidden lg:block">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs uppercase font-bold text-slate-300 tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-neon animate-pulse"></span>
                        Live Preview Card
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Tampilan di Katalog Publik</span>
                </div>

                <!-- Card Replica (Standard RuangLari Product Card) -->
                <div class="rounded-xl bg-slate-900 border border-slate-800 p-5 space-y-4 shadow-xl">
                    
                    <!-- Preview Image Frame -->
                    <div class="aspect-square rounded-lg overflow-hidden bg-slate-950 relative flex items-center justify-center border border-slate-800">
                        <template x-if="primaryPreviewImage">
                            <img :src="primaryPreviewImage" alt="Preview Image" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!primaryPreviewImage">
                            <div class="flex flex-col items-center justify-center text-slate-600">
                                <i class="fas fa-image text-3xl mb-2"></i>
                                <span class="text-xs uppercase tracking-wider font-semibold">Preview Foto</span>
                            </div>
                        </template>

                        <!-- Top Badges -->
                        <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider shadow"
                                  :class="condition === 'new' ? 'bg-white text-slate-950 font-bold' : 'bg-slate-950/90 text-slate-200 border border-slate-700'"
                                  x-text="condition === 'new' ? 'BARU' : 'BEKAS'">
                            </span>
                            <template x-if="saleType === 'auction'">
                                <span class="px-2 py-0.5 rounded bg-amber-500 text-slate-950 text-[9px] font-black uppercase tracking-wider shadow">
                                    LELANG
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Mini Gallery Thumbnail Selector -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1" x-show="activeImages.length > 1" x-cloak>
                        <template x-for="(item, i) in activeImages" :key="item.key">
                            <button type="button" @click="activePreviewIndex = i"
                                    class="w-12 h-12 rounded-md overflow-hidden border transition shrink-0 cursor-pointer"
                                    :class="activePreviewIndex === i ? 'border-neon ring-1 ring-neon' : 'border-slate-800 opacity-60 hover:opacity-100'">
                                <img :src="item.url" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>

                    <!-- Meta Tags -->
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400 font-bold">
                            <span class="text-neon" x-text="brandText || 'BRAND'"></span>
                            <span>•</span>
                            <span x-text="categoryText || 'KATEGORI'"></span>
                            <template x-if="size">
                                <span class="text-slate-300">• SIZE <span x-text="size"></span></span>
                            </template>
                        </div>

                        <!-- Title -->
                        <h3 class="text-base font-bold text-white leading-snug line-clamp-2"
                            x-text="title || 'Judul Running Gear Anda'">
                        </h3>

                        <!-- Price -->
                        <div class="pt-1">
                            <template x-if="saleType === 'fixed'">
                                <p class="text-lg font-bold text-white font-mono"
                                   x-text="formattedPrice">
                                </p>
                            </template>
                            <template x-if="saleType === 'auction'">
                                <div class="space-y-0.5">
                                    <span class="text-[9px] font-bold text-amber-400 uppercase tracking-wider">STARTING BID</span>
                                    <p class="text-lg font-bold text-white font-mono" x-text="formattedStartingPrice"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Seller Card Preview -->
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-700 bg-slate-800 shrink-0">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Seller' }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold">
                                    <span>Verified Community Seller</span>
                                </p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded bg-slate-800 text-slate-300 text-xs font-medium">
                            {{ auth()->user()->city ?? 'Indonesia' }}
                        </span>
                    </div>

                    <!-- Simulated Buy Button -->
                    <div class="pt-1">
                        <button type="button" disabled class="w-full py-2.5 rounded-md bg-slate-800 text-slate-400 font-bold text-xs uppercase tracking-wider cursor-not-allowed">
                            Simulasi Beli Sekarang
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function productEditForm() {
    return {
        title: @json(old('title', $product->title)),
        price: @json(old('price', $product->price)),
        startingPrice: @json(old('starting_price', $product->starting_price ?? $product->price)),
        condition: @json(old('condition', $product->condition)),
        size: @json(old('size', $product->size)),
        saleType: @json($product->sale_type),
        productType: @json($product->type),
        brandId: @json(old('brand_id', $product->brand_id)),
        brandText: @json(optional($product->brand)->name ?? ''),
        categoryId: @json(old('category_id', $product->category_id)),
        categoryText: @json(optional($product->category)->name ?? ''),
        categorySlug: @json(optional($product->category)->slug ?? ''),

        isShoeFormat: {{ old('shoe_sizes.us') || old('shoe_sizes.uk') || old('shoe_sizes.eu') || old('shoe_sizes.cm') || $isShoeCategory ? 'true' : 'false' }},
        shoeSizeUs: @json(old('shoe_sizes.us', $shoeSizes['us'] ?? '')),
        shoeSizeUk: @json(old('shoe_sizes.uk', $shoeSizes['uk'] ?? '')),
        shoeSizeEu: @json(old('shoe_sizes.eu', $shoeSizes['eu'] ?? '')),
        shoeSizeCm: @json(old('shoe_sizes.cm', $shoeSizes['cm'] ?? '')),

        raceName: @json(old('meta_data.race_name', $product->meta_data['race_name'] ?? '')),
        raceDate: @json(old('meta_data.race_date', $product->meta_data['race_date'] ?? '')),
        transferPolicy: @json(old('meta_data.transfer_policy', $product->meta_data['transfer_policy'] ?? '')),

        activePreviewIndex: 0,

        imagesList: [
            @foreach($product->images as $img)
            {
                key: 'existing_{{ $img->id }}',
                type: 'existing',
                id: {{ $img->id }},
                url: '{{ asset('storage/' . $img->image_path) }}',
                is_deleted: false,
            },
            @endforeach
        ],
        imageError: null,
        isDragging: false,
        isAddDragging: false,
        draggedKey: null,
        dragOverKey: null,

        init() {
            this.$nextTick(() => {
                this.filterBrands();
            });
        },

        get formattedPrice() {
            if (!this.price || isNaN(this.price)) return 'Rp 0';
            return 'Rp ' + Number(this.price).toLocaleString('id-ID');
        },

        get formattedStartingPrice() {
            if (!this.startingPrice || isNaN(this.startingPrice)) return 'Rp 0';
            return 'Rp ' + Number(this.startingPrice).toLocaleString('id-ID');
        },

        get activeImages() {
            return this.imagesList.filter(item => !item.is_deleted);
        },

        get primaryPreviewImage() {
            if (this.activeImages.length === 0) return null;
            const idx = Math.min(this.activePreviewIndex, this.activeImages.length - 1);
            return this.activeImages[idx]?.url || this.activeImages[0]?.url || null;
        },

        get deletedExistingImages() {
            return this.imagesList.filter(item => item.type === 'existing' && item.is_deleted);
        },

        get totalImagesCount() {
            return this.activeImages.length;
        },

        get availableSlots() {
            return Math.max(0, 4 - this.totalImagesCount);
        },

        get primaryItem() {
            return this.activeImages.length > 0 ? this.activeImages[0] : null;
        },

        get primaryImageId() {
            const p = this.primaryItem;
            return (p && p.type === 'existing') ? p.id : '';
        },

        get primaryNewImageIndex() {
            const p = this.primaryItem;
            if (!p || p.type !== 'new') return '';
            const newItems = this.imagesList.filter(item => item.type === 'new' && !item.is_deleted);
            const idx = newItems.indexOf(p);
            return idx !== -1 ? idx : '';
        },

        onDragStart(e, key) {
            this.draggedKey = key;
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', key);
            }
        },

        onDragOver(e, key) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
            if (this.draggedKey !== null && this.draggedKey !== key) {
                this.dragOverKey = key;
            }
        },

        onDragEnter(e, key) {
            if (this.draggedKey !== null && this.draggedKey !== key) {
                this.dragOverKey = key;
            }
        },

        onDragLeave(e, key) {
            if (this.dragOverKey === key) {
                this.dragOverKey = null;
            }
        },

        onDrop(e, targetKey) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0 && this.draggedKey === null) {
                this.addNewFiles(Array.from(e.dataTransfer.files));
                this.dragOverKey = null;
                return;
            }
            if (!this.draggedKey || this.draggedKey === targetKey) {
                this.draggedKey = null;
                this.dragOverKey = null;
                return;
            }

            const fromIdx = this.imagesList.findIndex(i => i.key === this.draggedKey);
            const toIdx = this.imagesList.findIndex(i => i.key === targetKey);

            if (fromIdx !== -1 && toIdx !== -1) {
                const item = this.imagesList.splice(fromIdx, 1)[0];
                this.imagesList.splice(toIdx, 0, item);
                this.syncFileInput();
            }

            this.draggedKey = null;
            this.dragOverKey = null;
        },

        onDragEnd(e) {
            this.draggedKey = null;
            this.dragOverKey = null;
        },

        setAsPrimary(key) {
            const fromIdx = this.imagesList.findIndex(i => i.key === key);
            if (fromIdx === -1) return;
            const item = this.imagesList.splice(fromIdx, 1)[0];
            const firstActiveIdx = this.imagesList.findIndex(i => !i.is_deleted);
            if (firstActiveIdx !== -1) {
                this.imagesList.splice(firstActiveIdx, 0, item);
            } else {
                this.imagesList.unshift(item);
            }
            this.activePreviewIndex = 0;
            this.syncFileInput();
        },

        removeImage(key) {
            const item = this.imagesList.find(i => i.key === key);
            if (!item) return;

            if (item.type === 'existing') {
                item.is_deleted = true;
            } else {
                if (item.url) {
                    URL.revokeObjectURL(item.url);
                }
                const idx = this.imagesList.indexOf(item);
                if (idx !== -1) {
                    this.imagesList.splice(idx, 1);
                }
                this.syncFileInput();
            }
            if (this.activePreviewIndex >= this.activeImages.length) {
                this.activePreviewIndex = Math.max(0, this.activeImages.length - 1);
            }
        },

        restoreDeleted(key) {
            if (this.totalImagesCount >= 4) {
                alert('Maksimal 4 foto yang dapat aktif dalam satu listing.');
                return;
            }
            const item = this.imagesList.find(i => i.key === key);
            if (item && item.type === 'existing') {
                item.is_deleted = false;
            }
        },

        triggerFileInput() {
            const input = document.getElementById('edit-images-input');
            if (input) input.click();
        },

        handleFilesDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer && e.dataTransfer.files) {
                this.addNewFiles(Array.from(e.dataTransfer.files));
            }
        },

        handleFilesFromInput(e) {
            if (e.target && e.target.files && e.target.files.length > 0) {
                this.addNewFiles(Array.from(e.target.files));
            }
        },

        addNewFiles(files) {
            this.imageError = null;
            const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            const maxSizeBytes = 3 * 1024 * 1024; // 3MB

            const validFiles = [];
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const ext = (file.name || '').split('.').pop().toLowerCase();
                const isFormatValid = allowedExts.includes(ext) || allowedMimes.includes(file.type);

                if (!isFormatValid) {
                    this.imageError = `Format file "${file.name}" tidak didukung. Harap gunakan format JPG, JPEG, PNG, atau WEBP.`;
                    continue;
                }

                if (file.size > maxSizeBytes) {
                    const mbSize = (file.size / (1024 * 1024)).toFixed(1);
                    this.imageError = `Ukuran file "${file.name}" (${mbSize}MB) melebihi batas maksimal 3MB.`;
                    continue;
                }

                validFiles.push(file);
            }

            if (validFiles.length === 0) return;

            const slots = this.availableSlots;
            if (slots <= 0) {
                this.imageError = 'Maksimal 4 foto per produk tercapai.';
                return;
            }

            const toAdd = validFiles.slice(0, slots);
            toAdd.forEach(file => {
                const previewUrl = URL.createObjectURL(file);
                this.imagesList.push({
                    key: 'new_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9),
                    type: 'new',
                    id: null,
                    file: file,
                    url: previewUrl,
                    is_deleted: false
                });
            });

            this.syncFileInput();
        },

        syncFileInput() {
            const input = document.getElementById('edit-images-input');
            if (!input) return;

            try {
                const dt = new DataTransfer();
                const newItems = this.imagesList.filter(item => item.type === 'new' && !item.is_deleted && item.file);
                newItems.forEach(item => {
                    dt.items.add(item.file);
                });
                input.files = dt.files;
            } catch (err) {
                console.error('DataTransfer sync error in edit:', err);
            }
        },

        updateCombinedSize() {
            if (!this.isShoeFormat) return;
            const parts = [];
            if (this.shoeSizeUs && this.shoeSizeUs.trim()) parts.push('US ' + this.shoeSizeUs.trim());
            if (this.shoeSizeUk && this.shoeSizeUk.trim()) parts.push('UK ' + this.shoeSizeUk.trim());
            if (this.shoeSizeEu && this.shoeSizeEu.trim()) parts.push('EU ' + this.shoeSizeEu.trim());
            if (this.shoeSizeCm && this.shoeSizeCm.trim()) parts.push(this.shoeSizeCm.trim() + ' CM');
            this.size = parts.join(' / ');
        },

        toggleShoeFormat() {
            this.isShoeFormat = !this.isShoeFormat;
            if (this.isShoeFormat) {
                this.updateCombinedSize();
            }
        },

        get sizePlaceholder() {
            const text = (this.categoryText || '').toLowerCase();
            if (text.includes('pakaian') || text.includes('jersey') || text.includes('celana') || text.includes('singlet')) {
                return 'Contoh: S, M, L, XL, XXL';
            }
            if (text.includes('elektronik') || text.includes('jam')) {
                return 'Contoh: 42mm, 47mm, atau All Size';
            }
            return 'Contoh: S, M, L, XL, atau All Size';
        },

        updateCategoryText(e) {
            const opt = e.target.options[e.target.selectedIndex];
            const name = opt ? (opt.dataset.name || opt.text || '') : '';
            const slug = opt ? (opt.dataset.slug || '') : '';
            this.categoryText = name;
            this.categorySlug = slug;
            const isShoe = slug.includes('sepatu') || slug.includes('shoe') || 
                           name.toLowerCase().includes('sepatu') || 
                           name.toLowerCase().includes('shoe');
            if (isShoe) {
                this.isShoeFormat = true;
                this.updateCombinedSize();
            } else {
                if (this.isShoeFormat) {
                    this.isShoeFormat = false;
                    if (this.size && (this.size.includes('US ') || this.size.includes('EU ') || this.size.includes(' CM') || this.size.includes('UK '))) {
                        this.size = '';
                    }
                }
            }
            this.filterBrands();
        },

        updateBrandText(e) {
            const opt = e.target.options[e.target.selectedIndex];
            this.brandText = opt ? (opt.dataset.name || (opt.value ? opt.text : '')) : '';
            this.brandId = opt ? opt.value : '';
        },

        filterBrands() {
            const categorySelect = document.getElementById('category-select');
            const brandSelect = document.getElementById('brand-select');
            if (!categorySelect || !brandSelect) return;
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const selectedCategorySlug = selectedOption ? selectedOption.dataset.slug : null;
            const brandOptions = Array.from(brandSelect.querySelectorAll('option'));
            
            brandOptions.forEach(option => {
                if (option.value === "") return;
                const categories = JSON.parse(option.dataset.categories || '[]');
                const isMatch = !selectedCategorySlug || categories.includes(selectedCategorySlug);
                option.hidden = !isMatch;
                option.disabled = !isMatch;
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="fulfillment_mode"]');
    const fields = document.getElementById('consignment-fields');
    if (!radios || !fields) return;

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'consignment') {
                fields.classList.remove('hidden');
            } else {
                fields.classList.add('hidden');
            }
        });
    });
});
</script>
@endsection
