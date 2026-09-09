@extends('layouts.pacerhub')

@section('title', 'Add Product - RuangLari Market')

@section('content')
<div class="min-h-screen pt-0 pb-20 px-4 sm:px-6 lg:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark"
     x-data="productCreateForm()">
    <div class="max-w-7xl mx-auto">
        
        <!-- Breadcrumb & Top Bar -->
        <nav class="flex items-center justify-between gap-3 mb-8 border-b border-slate-800/80 pb-4 text-xs">
            <div class="flex items-center gap-2 text-slate-400 font-medium">
                <a href="{{ route('marketplace.index') }}" class="hover:text-white transition">Marketplace</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('marketplace.seller.products.index') }}" class="hover:text-white transition">My Products</a>
                <span class="text-slate-600">/</span>
                <span class="text-white font-bold">Add Product</span>
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
                
                <div class="rounded-lg bg-[#0e1726] border border-[#1e2d42] shadow-2xl overflow-hidden">
                    
                    <!-- Card Header -->
                    <div class="p-6 md:p-8 border-b border-[#1e2d42] bg-[#121d30]">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-neon"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-neon">SELLER DASHBOARD</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black text-white uppercase tracking-tight font-sans">
                            ADD NEW <span class="text-neon">PRODUCT</span>
                        </h1>
                        <p class="text-slate-300 text-xs md:text-sm mt-1">
                            Lengkapi informasi produk running gear atau tiket race slot Anda di bawah ini.
                        </p>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        
                        <!-- Error Validation Box -->
                        @if ($errors->any())
                            <div class="p-4 bg-rose-950/80 border border-rose-700 rounded-md text-rose-200 text-xs">
                                <div class="flex items-center gap-2 font-bold mb-2 text-rose-300">
                                    <i class="fas fa-exclamation-circle text-sm"></i>
                                    <span>Mohon periksa kembali input berikut:</span>
                                </div>
                                <ul class="list-disc list-inside space-y-1 text-rose-200/90 font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Draft Restored Banner -->
                        <div x-show="draftRestored" x-cloak class="p-4 bg-[#152238] border border-slate-700 rounded-md text-xs flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2.5 text-slate-200">
                                <i class="fas fa-history text-neon text-sm"></i>
                                <span>Draft formulir produk sebelumnya berhasil dipulihkan dari penyimpanan browser.</span>
                            </div>
                            <button type="button" @click="resetForm()" class="px-3 py-1.5 rounded-md bg-[#0d1624] hover:bg-rose-900/60 hover:text-rose-200 text-slate-300 text-xs font-bold transition cursor-pointer shrink-0">
                                Bersihkan Draft
                            </button>
                        </div>

                        <form action="{{ route('marketplace.seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="product-create-form" @submit="syncFileInput()">
                            @csrf

                            <!-- 01. Informasi Dasar Produk -->
                            <div class="p-5 md:p-6 rounded-lg bg-[#152238] border border-[#253752] shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-[#253752]">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">01</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Informasi Dasar Gear</h2>
                                </div>

                                <!-- Product Title -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                        Judul Produk <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" name="title" x-model="title" required
                                        class="w-full bg-[#0d1624] border @error('title') border-rose-500 @else border-[#2e4263] @enderror rounded-md px-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition"
                                        placeholder="Contoh: Nike Vaporfly 3 Ekiden Edition 2024">
                                    @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Category -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Kategori <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="category_id" id="category-select" x-model="categoryId" @change="updateCategoryText($event)" required 
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition cursor-pointer [&>option]:bg-[#111927] [&>option]:text-white">
                                                <option value="" disabled selected class="bg-[#111927] text-slate-400">Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} data-slug="{{ $category->slug }}" data-name="{{ $category->name }}" class="bg-[#111927] text-white">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Brand -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Brand / Merek
                                        </label>
                                        <div class="relative">
                                            <select name="brand_id" id="brand-select" x-model="brandId" @change="updateBrandText($event)"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition cursor-pointer [&>option]:bg-[#111927] [&>option]:text-white">
                                                <option value="" selected class="bg-[#111927] text-slate-400">Pilih Brand (Opsional)</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" data-name="{{ $brand->name }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" class="bg-[#111927] text-white">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Size / Ukuran Section (Dynamic Shoe vs Standard) -->
                                <div class="p-4 bg-[#1c2c46] border border-[#2e4263] rounded-md space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-bold uppercase tracking-wider text-slate-100">
                                                Ukuran / Size
                                            </label>
                                            <span x-show="isShoeFormat" class="text-[10px] font-bold text-neon bg-neon/10 px-2 py-0.5 rounded border border-neon/30 uppercase font-mono">Format Sepatu</span>
                                            <span x-show="!isShoeFormat" class="text-[10px] font-bold text-slate-300 bg-[#0d1624] px-2 py-0.5 rounded border border-[#2e4263] uppercase font-mono">Format Umum</span>
                                        </div>
                                        <button type="button" @click="toggleShoeFormat()" 
                                                class="text-[11px] text-slate-300 hover:text-neon underline font-semibold transition">
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
                                                       class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">UK</label>
                                                <input type="text" name="shoe_sizes[uk]" x-model="shoeSizeUk" @input="updateCombinedSize()" 
                                                       placeholder="8.5"
                                                       class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">EU</label>
                                                <input type="text" name="shoe_sizes[eu]" x-model="shoeSizeEu" @input="updateCombinedSize()" 
                                                       placeholder="43"
                                                       class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">CM (Panjang)</label>
                                                <input type="text" name="shoe_sizes[cm]" x-model="shoeSizeCm" @input="updateCombinedSize()" 
                                                       placeholder="27.5"
                                                       class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-3 py-2 text-xs font-mono font-bold text-white text-center focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                            </div>
                                        </div>

                                        <!-- Live preview of combined size string -->
                                        <div class="flex items-center justify-between pt-2 border-t border-[#253752] text-xs">
                                            <span class="text-[11px] text-slate-300">Ringkasan Ukuran Produk:</span>
                                            <span class="font-mono font-bold text-white bg-[#0d1624] px-3 py-1 rounded border border-[#2e4263] text-xs" 
                                                  x-text="size || 'Isi setidaknya satu ukuran di atas'"></span>
                                        </div>
                                    </div>

                                    <!-- Standard Single Size Input for Non-Shoe Products -->
                                    <div x-show="!isShoeFormat">
                                        <input type="text" x-model="size"
                                            class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition"
                                            :placeholder="sizePlaceholder">
                                    </div>

                                    <!-- Hidden input that actually carries the combined size to form submission -->
                                    <input type="hidden" name="size" :value="size">
                                </div>

                                <!-- Type & Condition Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Type -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Tipe Produk <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="type" id="type-select" x-model="productType" 
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition cursor-pointer [&>option]:bg-[#111927] [&>option]:text-white">
                                                <option value="physical" class="bg-[#111927] text-white">Barang Fisik (Sepatu / Apparel / Aksesoris)</option>
                                                <option value="digital_slot" class="bg-[#111927] text-white">Slot Race / Tiket Lari</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Condition -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Kondisi Barang <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="relative flex items-center justify-between p-3 rounded-md border cursor-pointer transition select-none"
                                                   :class="condition === 'new' ? 'bg-[#223656] border-neon ring-1 ring-neon/40 text-white' : 'bg-[#1c2c46] border-[#2e4263] text-slate-300 hover:border-slate-400 hover:text-white'">
                                                <input type="radio" name="condition" value="new" x-model="condition" class="sr-only">
                                                <div class="flex flex-col pr-1 min-w-0">
                                                    <span class="text-xs font-black uppercase tracking-wider" :class="condition === 'new' ? 'text-white' : 'text-slate-200'">BARU (BNIB)</span>
                                                    <span class="text-[10px] mt-0.5 leading-tight truncate" :class="condition === 'new' ? 'text-slate-200' : 'text-slate-400'">Brand New In Box/Tag</span>
                                                </div>
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition"
                                                     :class="condition === 'new' ? 'border-neon bg-neon' : 'border-slate-500 bg-transparent'">
                                                    <div class="w-1.5 h-1.5 rounded-full" :class="condition === 'new' ? 'bg-[#0e1726]' : 'bg-transparent'"></div>
                                                </div>
                                            </label>
                                            <label class="relative flex items-center justify-between p-3 rounded-md border cursor-pointer transition select-none"
                                                   :class="condition === 'used' ? 'bg-[#223656] border-neon ring-1 ring-neon/40 text-white' : 'bg-[#1c2c46] border-[#2e4263] text-slate-300 hover:border-slate-400 hover:text-white'">
                                                <input type="radio" name="condition" value="used" x-model="condition" class="sr-only">
                                                <div class="flex flex-col pr-1 min-w-0">
                                                    <span class="text-xs font-black uppercase tracking-wider" :class="condition === 'used' ? 'text-white' : 'text-slate-200'">BEKAS (USED)</span>
                                                    <span class="text-[10px] mt-0.5 leading-tight truncate" :class="condition === 'used' ? 'text-slate-200' : 'text-slate-400'">Pernah dipakai, baik</span>
                                                </div>
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition"
                                                     :class="condition === 'used' ? 'border-neon bg-neon' : 'border-slate-500 bg-transparent'">
                                                    <div class="w-1.5 h-1.5 rounded-full" :class="condition === 'used' ? 'bg-[#0e1726]' : 'bg-transparent'"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 02. Skema Jual & Harga -->
                            <div class="p-5 md:p-6 rounded-lg bg-[#152238] border border-[#253752] shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-[#253752]">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">02</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Skema Jual & Harga</h2>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Sale Mode Card -->
                                    <div class="p-4 bg-[#1c2c46] rounded-md border border-[#2e4263] space-y-2.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100">
                                            Mode Penjualan
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="flex items-center justify-center p-2.5 rounded-md border border-[#2e4263] bg-[#0d1624] cursor-pointer hover:border-slate-400 transition has-[:checked]:border-neon has-[:checked]:bg-neon/15">
                                                <input type="radio" name="sale_type" value="fixed" x-model="saleType" class="sr-only">
                                                <span class="text-xs font-bold text-white">Jual Langsung</span>
                                            </label>
                                            <label class="flex items-center justify-center p-2.5 rounded-md border border-[#2e4263] bg-[#0d1624] cursor-pointer hover:border-slate-400 transition has-[:checked]:border-neon has-[:checked]:bg-neon/15">
                                                <input type="radio" name="sale_type" value="auction" x-model="saleType" class="sr-only">
                                                <span class="text-xs font-bold text-white">Lelang (Bid)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Fulfillment Mode Card -->
                                    <div class="p-4 bg-[#1c2c46] rounded-md border border-[#2e4263] space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100">
                                                Metode Pengiriman
                                            </label>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-neon/20 text-neon border border-neon/30 uppercase font-mono">Verified & IG Promo</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="flex items-center justify-center p-2.5 rounded-md border border-[#2e4263] bg-[#0d1624] cursor-pointer hover:border-slate-400 transition has-[:checked]:border-neon has-[:checked]:bg-neon/15">
                                                <input type="radio" name="fulfillment_mode" value="self_ship" x-model="fulfillmentMode" class="sr-only">
                                                <span class="text-xs font-bold text-white">Kirim Sendiri</span>
                                            </label>
                                            <label class="flex items-center justify-center p-2.5 rounded-md border border-[#2e4263] bg-[#0d1624] cursor-pointer hover:border-slate-400 transition has-[:checked]:border-neon has-[:checked]:bg-neon/15">
                                                <input type="radio" name="fulfillment_mode" value="consignment" x-model="fulfillmentMode" class="sr-only">
                                                <span class="text-xs font-bold text-white">Titip Jual</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kelebihan Titip Jual di RuangLari Callout Card -->
                                <div class="p-4 rounded-md bg-[#132238] border border-neon/30 space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-neon text-slate-950 font-mono">
                                                KELEBIHAN TITIP JUAL
                                            </span>
                                            <span class="text-xs font-bold text-white">Kenapa titip jual di RuangLari?</span>
                                        </div>
                                        <span class="text-[11px] text-slate-300 font-medium hidden sm:inline">Layanan resmi komunitas lari</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        <div class="flex items-start gap-2.5 p-3 rounded-md bg-[#0d1624] border border-[#23344d]">
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
                                        <div class="flex items-start gap-2.5 p-3 rounded-md bg-[#0d1624] border border-[#23344d]">
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

                                <!-- Fixed Price Fields -->
                                <div x-show="saleType === 'fixed'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Harga (Rp) <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                            <input type="number" name="price" min="0" x-model="price"
                                                class="w-full bg-[#0d1624] border @error('price') border-rose-500 @else border-[#2e4263] @enderror rounded-md pl-11 pr-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition"
                                                placeholder="0">
                                        </div>
                                        @error('price') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                            Jumlah Stok <span class="text-rose-400">*</span>
                                        </label>
                                        <input type="number" name="stock" min="1" x-model="stock"
                                            class="w-full bg-[#0d1624] border @error('stock') border-rose-500 @else border-[#2e4263] @enderror rounded-md px-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition">
                                        @error('stock') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Auction Fields -->
                                <div x-show="saleType === 'auction'" x-cloak class="p-5 bg-[#1c2c46] rounded-md border border-[#2e4263] space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Harga Awal Lelang (Starting) <span class="text-rose-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                                <input type="number" name="starting_price" min="0" x-model="startingPrice"
                                                    class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md pl-11 pr-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Kelipatan Bid (Increment) <span class="text-rose-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                                <input type="number" name="min_increment" min="0"
                                                    class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md pl-11 pr-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                    placeholder="Contoh: 25000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Lelang Berakhir Pada
                                            </label>
                                            <input type="datetime-local" name="auction_end_at"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-3.5 py-3 text-xs text-white focus:outline-none focus:border-neon transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Reserve Price (Opsional)
                                            </label>
                                            <input type="number" name="reserve_price" min="0"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Harga minimal deal">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                                Buy Now Price (Opsional)
                                            </label>
                                            <input type="number" name="buy_now_price" min="0"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Beli langsung">
                                        </div>
                                    </div>
                                </div>

                                <!-- Titip Jual Dropoff & Owner Contact Fields -->
                                <div x-show="fulfillmentMode === 'consignment'" x-cloak class="p-5 bg-[#1c2c46] rounded-md border border-[#2e4263] space-y-4">
                                    <div class="border-b border-[#253752] pb-2">
                                        <p class="text-xs font-bold text-white uppercase tracking-wider">Informasi Pemilik & Penyerahan Barang</p>
                                        <p class="text-xs text-slate-300">Masukkan kontak pemilik asli (teman / penjual sumber) jika Anda menjualkan barang orang lain. Admin akan memverifikasi sebelum produk tayang.</p>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Nama Pemilik Asli (Opsional)</label>
                                            <input type="text" name="owner_name" x-model="ownerName"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Contoh: Budi (Teman) / FB Seller">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">No. WhatsApp Pemilik (Opsional)</label>
                                            <input type="text" name="owner_phone" x-model="ownerPhone"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Contoh: 08123456789">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Metode Serah Terima</label>
                                            <input type="text" name="dropoff_method" x-model="dropoffMethod"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Kirim Ekspedisi / Dropoff Langsung">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Lokasi Seller / Kota</label>
                                            <input type="text" name="dropoff_location" x-model="dropoffLocation"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition"
                                                placeholder="Kota / Daerah Asal Barang">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 03. Foto Produk (Dropzone Maksimal 4 Foto) -->
                            <div class="p-5 md:p-6 rounded-lg bg-[#152238] border border-[#253752] shadow-sm space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-[#253752]">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">03</span>
                                        <h2 class="text-xs font-bold uppercase tracking-wider text-white">Foto Produk (Maksimal 4 Foto) <span class="text-rose-400">*</span></h2>
                                    </div>
                                    <span class="text-xs text-slate-300 font-semibold uppercase">
                                        <span x-text="fileList.length" class="text-neon font-mono font-bold">0</span> / 4 Foto Terpilih
                                    </span>
                                </div>

                                <p class="text-xs text-slate-300">
                                    Unggah hingga 4 foto produk (foto pertama menjadi foto utama/sampul). Mendukung format JPG, PNG, WEBP hingga 3MB per file.
                                </p>

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

                                <!-- Hidden File Input that holds the actual DataTransfer files -->
                                <input type="file" id="product-images-input" name="images[]" multiple accept="image/*" class="hidden" @change="handleFilesFromInput($event)">

                                <!-- Dropzone Drag & Drop Area (Shown when 0 photos selected) -->
                                <div 
                                    id="product-dropzone"
                                    class="relative border-2 border-dashed rounded-md p-6 sm:p-8 text-center transition-all cursor-pointer bg-[#1c2c46] group select-none"
                                    :class="isDragging ? 'border-neon bg-neon/10 scale-[1.01]' : 'border-[#2e4263] hover:border-slate-400 hover:bg-[#20324e]'"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleFilesDrop($event)"
                                    @click="triggerFileInput()"
                                    x-show="fileList.length === 0"
                                >
                                    <div class="flex flex-col items-center justify-center space-y-3 pointer-events-none">
                                        <div class="w-12 h-12 rounded-md bg-[#0d1624] border border-[#2e4263] flex items-center justify-center text-slate-300 group-hover:text-neon group-hover:border-neon/50 transition shadow-inner">
                                            <i class="fas fa-cloud-arrow-up text-xl text-neon"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-white">
                                                Tarik &amp; letakkan foto di sini, atau <span class="text-neon underline">pilih dari galeri</span>
                                            </p>
                                            <p class="text-xs text-slate-300 mt-1">
                                                Maksimal 4 foto (JPEG, PNG, WEBP hingga 3MB)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Thumbnails Grid (when files are selected) -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5" x-show="fileList.length > 0" x-cloak>
                                    <template x-for="(item, idx) in fileList" :key="item.previewUrl">
                                        <div 
                                            draggable="true"
                                            @dragstart="onDragStart($event, idx)"
                                            @dragover.prevent="onDragOver($event, idx)"
                                            @dragenter.prevent="onDragEnter($event, idx)"
                                            @dragleave="onDragLeave($event, idx)"
                                            @drop.prevent="onDrop($event, idx)"
                                            @dragend="onDragEnd($event)"
                                            class="relative aspect-square rounded-md border bg-[#0d1624] overflow-hidden flex flex-col items-center justify-center text-center group shadow-md transition-all select-none cursor-grab active:cursor-grabbing"
                                            :class="{
                                                'opacity-30 scale-95 border-dashed border-slate-500': draggedIndex === idx,
                                                'border-neon ring-2 ring-neon/40 scale-[1.02] z-20': dragOverIndex === idx && draggedIndex !== idx,
                                                'border-neon/80 ring-1 ring-neon/30': idx === 0 && draggedIndex !== idx && dragOverIndex !== idx,
                                                'border-[#2e4263] hover:border-slate-400': idx !== 0 && draggedIndex !== idx && dragOverIndex !== idx
                                            }"
                                        >
                                            <img :src="item.previewUrl" class="w-full h-full object-cover pointer-events-none">
                                            
                                            <!-- Slot Badge -->
                                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider shadow pointer-events-none"
                                                  :class="idx === 0 ? 'bg-neon text-dark font-black' : 'bg-[#152238] text-slate-200 border border-[#2e4263] font-bold'"
                                                  x-text="idx === 0 ? 'UTAMA' : 'FOTO ' + (idx + 1)"></span>
                                            
                                            <!-- Remove Button -->
                                            <button type="button" @click.stop="removeImage(idx)" 
                                                    class="absolute top-2 right-2 w-6 h-6 rounded-md bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-xs shadow-md transition cursor-pointer z-10"
                                                    title="Hapus foto ini">
                                                <i class="fas fa-times text-[10px]"></i>
                                            </button>

                                            <!-- Bottom Bar: Drag Handle + Make Primary Button -->
                                            <div class="absolute bottom-2 inset-x-2 flex items-center justify-between pointer-events-none">
                                                <span class="px-1.5 py-0.5 rounded bg-[#152238]/90 border border-[#2e4263] text-slate-300 group-hover:text-white text-[10px] flex items-center gap-1 shadow">
                                                    <i class="fas fa-grip-vertical text-[9px]"></i>
                                                    <span class="text-[9px] font-medium hidden sm:inline">Geser</span>
                                                </span>

                                                <template x-if="idx > 0">
                                                    <button type="button" @click.stop="setAsPrimary(idx)"
                                                            class="pointer-events-auto px-2 py-0.5 rounded bg-[#152238]/90 hover:bg-slate-800 border border-[#2e4263] hover:border-neon text-slate-200 hover:text-neon text-[9px] font-bold transition shadow cursor-pointer">
                                                        Jadikan Utama
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Add more slot if less than 4 -->
                                    <template x-if="fileList.length > 0 && fileList.length < 4">
                                        <div @click="triggerFileInput()"
                                             @dragover.prevent="isAddDragging = true"
                                             @dragleave.prevent="isAddDragging = false"
                                             @drop.prevent="isAddDragging = false; handleFilesDrop($event)"
                                             class="relative aspect-square rounded-md border-2 border-dashed border-[#2e4263] bg-[#1c2c46] hover:border-slate-400 hover:bg-[#20324e] cursor-pointer transition flex flex-col items-center justify-center text-center p-3 group select-none"
                                             :class="isAddDragging ? 'border-neon bg-neon/10' : ''">
                                            <div class="w-8 h-8 rounded-md bg-[#0d1624] border border-[#2e4263] flex items-center justify-center text-slate-300 group-hover:text-white mb-1.5 transition">
                                                <i class="fas fa-plus text-xs"></i>
                                            </div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-200">Tambah Foto</span>
                                            <span class="text-xs text-slate-400 mt-0.5" x-text="'Slot ' + (fileList.length + 1) + '/4'"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Drag Instruction Helper -->
                                <div class="flex items-center gap-1.5 text-xs text-slate-300 mt-2" x-show="fileList.length > 0" x-cloak>
                                    <i class="fas fa-arrows-alt text-slate-400 text-[11px]"></i>
                                    <span>Tarik dan geser kartu foto untuk mengatur urutan. Foto di urutan pertama otomatis menjadi <strong>Foto Utama (Cover)</strong>.</span>
                                </div>

                                @error('images') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                @error('images.*') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                            </div>

                            <!-- 04. Deskripsi Lengkap Produk -->
                            <div class="p-5 md:p-6 rounded-lg bg-[#152238] border border-[#253752] shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-[#253752]">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">04</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Deskripsi Lengkap Gear</h2>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">
                                        Deskripsi & Riwayat Penggunaan <span class="text-rose-400">*</span>
                                    </label>
                                    <textarea name="description" rows="5" required x-model="description"
                                        class="w-full bg-[#0d1624] border @error('description') border-rose-500 @else border-[#2e4263] @enderror rounded-md px-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition"
                                        placeholder="Jelaskan kondisi detail, perkiraan mileage (km pemakaian), kelengkapan box/tag, serta alasan jual..."></textarea>
                                    @error('description') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- 05. Detail Race Slot (Otomatis muncul jika type digital_slot) -->
                            <div x-show="productType === 'digital_slot'" x-cloak class="p-5 md:p-6 rounded-lg bg-[#152238] border border-[#253752] shadow-sm space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-[#253752]">
                                    <span class="px-2 py-0.5 rounded bg-neon/15 text-neon font-black text-xs font-mono">05</span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Detail Race Slot & BIB</h3>
                                </div>
                                <div class="p-4 bg-[#1c2c46] rounded-md border border-[#2e4263] space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Nama Race / Event</label>
                                            <input type="text" name="meta_data[race_name]" x-model="raceName" placeholder="Contoh: Borobudur Marathon 2024" 
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Tanggal Race</label>
                                            <input type="date" name="meta_data[race_date]" x-model="raceDate"
                                                class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white focus:outline-none focus:border-neon transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-100 mb-2">Kebijakan Transfer BIB / Nama</label>
                                        <input type="text" name="meta_data[transfer_policy]" x-model="transferPolicy" placeholder="Contoh: Bisa ganti nama resmi sampai H-14 race" 
                                            class="w-full bg-[#0d1624] border border-[#2e4263] rounded-md px-4 py-3 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-neon transition">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="p-4 md:p-5 rounded-lg bg-[#152238] border border-[#253752] shadow-sm flex items-center justify-end gap-3">
                                <a href="{{ route('marketplace.seller.products.index') }}" 
                                    class="px-6 py-3 rounded-md border border-[#2e4263] bg-[#0d1624] text-slate-300 hover:text-white hover:bg-slate-800 text-xs font-bold transition">
                                    Batal
                                </a>
                                <button type="submit" 
                                    class="px-8 py-3.5 rounded-md bg-neon hover:bg-white hover:text-dark text-dark font-black text-xs uppercase tracking-wider transition shadow-lg shadow-neon/15 flex items-center gap-2 cursor-pointer">
                                    <i class="fas fa-check"></i>
                                    <span>Simpan & Pasang Iklan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Product Card Preview (5 Columns Sticky) -->
            <div class="lg:col-span-5 sticky top-28 space-y-4 hidden lg:block">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-neon"></span>
                        Live Preview Card
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Tampilan di Katalog Publik</span>
                </div>

                <!-- Card Replica (Standard RuangLari Product Card) -->
                <div class="rounded-lg bg-[#152238] border border-[#253752] p-5 space-y-4 shadow-xl">
                    
                    <!-- Preview Image Frame -->
                    <div class="aspect-square rounded-md overflow-hidden bg-[#0d1624] relative flex items-center justify-center border border-[#253752]">
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
                                  :class="condition === 'new' ? 'bg-white text-dark' : 'bg-slate-950/90 text-slate-200 border border-slate-700'"
                                  x-text="condition === 'new' ? 'BARU' : 'BEKAS'">
                            </span>
                            <template x-if="saleType === 'auction'">
                                <span class="px-2 py-0.5 rounded bg-amber-500 text-dark text-[9px] font-black uppercase tracking-wider shadow">
                                    LELANG
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Mini Gallery Thumbnail Selector -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1" x-show="fileList.length > 1" x-cloak>
                        <template x-for="(item, i) in fileList" :key="i">
                            <button type="button" @click="activePreviewIndex = i"
                                    class="w-12 h-12 rounded-md overflow-hidden border transition shrink-0 cursor-pointer"
                                    :class="activePreviewIndex === i ? 'border-white ring-1 ring-white' : 'border-slate-800 opacity-60 hover:opacity-100'">
                                <img :src="item.previewUrl" class="w-full h-full object-cover">
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
                        <h3 class="text-base font-black text-white leading-snug line-clamp-2"
                            x-text="title || 'Judul Running Gear Anda'">
                        </h3>

                        <!-- Price -->
                        <div class="pt-1">
                            <template x-if="saleType === 'fixed'">
                                <p class="text-lg font-black text-white font-mono"
                                   x-text="formattedPrice">
                                </p>
                            </template>
                            <template x-if="saleType === 'auction'">
                                <div class="space-y-0.5">
                                    <span class="text-[9px] font-bold text-amber-400 uppercase tracking-wider">STARTING BID</span>
                                    <p class="text-lg font-black text-white font-mono" x-text="formattedStartingPrice"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Seller Card Preview -->
                    <div class="p-3 rounded-md bg-[#0d1624] border border-[#253752] flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-700 bg-slate-800 shrink-0">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Seller Anda' }}</p>
                                <p class="text-[10px] text-slate-400 flex items-center gap-1 font-semibold">
                                    <span>Verified Community Seller</span>
                                </p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded bg-slate-800 text-slate-400 text-xs font-medium">
                            {{ auth()->user()->city ?? 'Indonesia' }}
                        </span>
                    </div>

                    <!-- Simulated Buy Button -->
                    <div class="pt-1">
                        <button type="button" disabled class="w-full py-2.5 rounded-md bg-white/20 text-slate-400 font-black text-xs uppercase tracking-wider cursor-not-allowed">
                            Simulasi Beli Sekarang
                        </button>
                    </div>
                </div>

                <div class="p-3 rounded-md bg-slate-900/50 border border-slate-800/60 text-slate-400 text-xs leading-relaxed">
                    <i class="fas fa-info-circle text-neon mr-1"></i>
                    Foto akan otomatis diproses dalam format WebP resolusi tinggi dan cepat dimuat di seluruh perangkat.
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function productCreateForm() {
    return {
        title: @json(old('title', '')),
        categoryId: @json(old('category_id', '')),
        categoryText: '',
        categorySlug: '',
        brandId: @json(old('brand_id', '')),
        brandText: '',
        size: @json(old('size', '')),
        isShoeFormat: {{ old('shoe_sizes.us') || old('shoe_sizes.uk') || old('shoe_sizes.eu') || old('shoe_sizes.cm') ? 'true' : 'false' }},
        shoeSizeUs: @json(old('shoe_sizes.us', '')),
        shoeSizeUk: @json(old('shoe_sizes.uk', '')),
        shoeSizeEu: @json(old('shoe_sizes.eu', '')),
        shoeSizeCm: @json(old('shoe_sizes.cm', '')),
        productType: @json(old('type', 'physical')),
        condition: @json(old('condition', 'new')),
        saleType: @json(old('sale_type', 'fixed')),
        fulfillmentMode: @json(old('fulfillment_mode', 'self_ship')),
        price: @json(old('price', '')),
        stock: @json(old('stock', 1)),
        startingPrice: @json(old('starting_price', '')),
        description: @json(old('description', '')),
        raceName: @json(old('meta_data.race_name', '')),
        raceDate: @json(old('meta_data.race_date', '')),
        transferPolicy: @json(old('meta_data.transfer_policy', '')),
        ownerName: @json(old('owner_name', '')),
        ownerPhone: @json(old('owner_phone', '')),
        dropoffMethod: @json(old('dropoff_method', '')),
        dropoffLocation: @json(old('dropoff_location', '')),
        
        isDragging: false,
        isAddDragging: false,
        draggedIndex: null,
        dragOverIndex: null,
        fileList: [],
        activePreviewIndex: 0,
        imageError: null,
        draftRestored: false,
        draftKey: 'ruanglari_product_create_draft',

        init() {
            this.loadDraft();

            const categorySelect = document.getElementById('category-select');
            if (categorySelect && categorySelect.selectedIndex > 0) {
                const opt = categorySelect.options[categorySelect.selectedIndex];
                if (opt && opt.value) {
                    this.categoryText = opt.dataset.name || opt.text;
                    this.categorySlug = opt.dataset.slug || '';
                    const isShoe = this.categorySlug.includes('sepatu') || 
                                   this.categorySlug.includes('shoe') || 
                                   this.categoryText.toLowerCase().includes('sepatu') || 
                                   this.categoryText.toLowerCase().includes('shoe');
                    if (isShoe) {
                        this.isShoeFormat = true;
                    }
                }
            }
            if (this.isShoeFormat && (this.shoeSizeUs || this.shoeSizeUk || this.shoeSizeEu || this.shoeSizeCm)) {
                this.updateCombinedSize();
            }
            this.filterBrands();
            this.$nextTick(() => {
                this.filterBrands();
            });

            // Auto-save draft on every field change
            const fieldsToWatch = [
                'title', 'categoryId', 'brandId', 'price', 'stock', 'startingPrice',
                'description', 'condition', 'saleType', 'fulfillmentMode', 'size',
                'shoeSizeUs', 'shoeSizeUk', 'shoeSizeEu', 'shoeSizeCm',
                'raceName', 'raceDate', 'transferPolicy',
                'ownerName', 'ownerPhone', 'dropoffMethod', 'dropoffLocation'
            ];
            fieldsToWatch.forEach(field => {
                this.$watch(field, () => this.saveDraft());
            });
        },

        saveDraft() {
            try {
                const draft = {
                    title: this.title || '',
                    categoryId: this.categoryId || '',
                    categoryText: this.categoryText || '',
                    categorySlug: this.categorySlug || '',
                    brandId: this.brandId || '',
                    brandText: this.brandText || '',
                    size: this.size || '',
                    isShoeFormat: this.isShoeFormat,
                    shoeSizeUs: this.shoeSizeUs || '',
                    shoeSizeUk: this.shoeSizeUk || '',
                    shoeSizeEu: this.shoeSizeEu || '',
                    shoeSizeCm: this.shoeSizeCm || '',
                    productType: this.productType || 'physical',
                    condition: this.condition || 'new',
                    saleType: this.saleType || 'fixed',
                    fulfillmentMode: this.fulfillmentMode || 'self_ship',
                    price: this.price || '',
                    stock: this.stock || 1,
                    startingPrice: this.startingPrice || '',
                    description: this.description || '',
                    raceName: this.raceName || '',
                    raceDate: this.raceDate || '',
                    transferPolicy: this.transferPolicy || '',
                    ownerName: this.ownerName || '',
                    ownerPhone: this.ownerPhone || '',
                    dropoffMethod: this.dropoffMethod || '',
                    dropoffLocation: this.dropoffLocation || '',
                    savedAt: new Date().toISOString()
                };
                localStorage.setItem(this.draftKey, JSON.stringify(draft));
            } catch (e) {
                console.warn('Gagal menyimpan draft:', e);
            }
        },

        loadDraft() {
            try {
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return;

                // Hanya isi dari draft jika tidak ada data dari flash server old()
                const hasServerOld = Boolean(
                    @json(old('title')) || 
                    @json(old('description')) || 
                    @json(old('price')) || 
                    @json(old('category_id'))
                );

                if (!hasServerOld) {
                    if (draft.title && !this.title) this.title = draft.title;
                    if (draft.categoryId && !this.categoryId) this.categoryId = draft.categoryId;
                    if (draft.categoryText) this.categoryText = draft.categoryText;
                    if (draft.categorySlug) this.categorySlug = draft.categorySlug;
                    if (draft.brandId && !this.brandId) this.brandId = draft.brandId;
                    if (draft.brandText) this.brandText = draft.brandText;
                    if (draft.isShoeFormat !== undefined) this.isShoeFormat = draft.isShoeFormat;
                    if (draft.shoeSizeUs) this.shoeSizeUs = draft.shoeSizeUs;
                    if (draft.shoeSizeUk) this.shoeSizeUk = draft.shoeSizeUk;
                    if (draft.shoeSizeEu) this.shoeSizeEu = draft.shoeSizeEu;
                    if (draft.shoeSizeCm) this.shoeSizeCm = draft.shoeSizeCm;
                    if (draft.size && !this.size) this.size = draft.size;
                    if (draft.productType) this.productType = draft.productType;
                    if (draft.condition) this.condition = draft.condition;
                    if (draft.saleType) this.saleType = draft.saleType;
                    if (draft.fulfillmentMode) this.fulfillmentMode = draft.fulfillmentMode;
                    if (draft.price && !this.price) this.price = draft.price;
                    if (draft.stock) this.stock = draft.stock;
                    if (draft.startingPrice && !this.startingPrice) this.startingPrice = draft.startingPrice;
                    if (draft.description && !this.description) this.description = draft.description;
                    if (draft.raceName) this.raceName = draft.raceName;
                    if (draft.raceDate) this.raceDate = draft.raceDate;
                    if (draft.transferPolicy) this.transferPolicy = draft.transferPolicy;
                    if (draft.ownerName) this.ownerName = draft.ownerName;
                    if (draft.ownerPhone) this.ownerPhone = draft.ownerPhone;
                    if (draft.dropoffMethod) this.dropoffMethod = draft.dropoffMethod;
                    if (draft.dropoffLocation) this.dropoffLocation = draft.dropoffLocation;

                    if (draft.title || draft.description || draft.price) {
                        this.draftRestored = true;
                    }
                }
            } catch (e) {
                console.warn('Gagal membaca draft:', e);
            }
        },

        clearDraft() {
            try {
                localStorage.removeItem(this.draftKey);
                this.draftRestored = false;
            } catch (e) {}
        },

        resetForm() {
            this.clearDraft();
            window.location.reload();
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

        get primaryPreviewImage() {
            if (this.fileList[this.activePreviewIndex]) {
                return this.fileList[this.activePreviewIndex].previewUrl;
            }
            if (this.fileList.length > 0) {
                return this.fileList[0].previewUrl;
            }
            return null;
        },

        get formattedPrice() {
            if (!this.price) return 'Rp 0';
            return 'Rp ' + Number(this.price).toLocaleString('id-ID');
        },

        get formattedStartingPrice() {
            if (!this.startingPrice) return 'Rp 0';
            return 'Rp ' + Number(this.startingPrice).toLocaleString('id-ID');
        },

        triggerFileInput() {
            const input = document.getElementById('product-images-input');
            if (input) input.click();
        },

        onDragStart(e, index) {
            this.draggedIndex = index;
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', index.toString());
            }
        },

        onDragOver(e, index) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
            if (this.draggedIndex !== null && this.draggedIndex !== index) {
                this.dragOverIndex = index;
            }
        },

        onDragEnter(e, index) {
            if (this.draggedIndex !== null && this.draggedIndex !== index) {
                this.dragOverIndex = index;
            }
        },

        onDragLeave(e, index) {
            if (this.dragOverIndex === index) {
                this.dragOverIndex = null;
            }
        },

        onDrop(e, targetIndex) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0 && this.draggedIndex === null) {
                this.addFiles(Array.from(e.dataTransfer.files));
                this.dragOverIndex = null;
                return;
            }
            if (this.draggedIndex === null || this.draggedIndex === targetIndex) {
                this.draggedIndex = null;
                this.dragOverIndex = null;
                return;
            }
            const item = this.fileList.splice(this.draggedIndex, 1)[0];
            this.fileList.splice(targetIndex, 0, item);
            this.draggedIndex = null;
            this.dragOverIndex = null;
            this.syncFileInput();
            this.activePreviewIndex = 0;
        },

        onDragEnd(e) {
            this.draggedIndex = null;
            this.dragOverIndex = null;
        },

        setAsPrimary(index) {
            if (index <= 0 || index >= this.fileList.length) return;
            const item = this.fileList.splice(index, 1)[0];
            this.fileList.unshift(item);
            this.syncFileInput();
            this.activePreviewIndex = 0;
        },

        handleFilesDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer && e.dataTransfer.files) {
                this.addFiles(Array.from(e.dataTransfer.files));
            }
        },

        handleFilesFromInput(e) {
            if (e.target && e.target.files && e.target.files.length > 0) {
                this.addFiles(Array.from(e.target.files));
            }
        },

        addFiles(newFiles) {
            this.imageError = null;
            const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            const maxSizeBytes = 3 * 1024 * 1024; // 3MB

            const validFiles = [];
            for (let i = 0; i < newFiles.length; i++) {
                const file = newFiles[i];
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

            const availableSlots = 4 - this.fileList.length;
            if (availableSlots <= 0) {
                this.imageError = 'Maksimal 4 foto per produk tercapai.';
                return;
            }

            const toAdd = validFiles.slice(0, availableSlots);

            toAdd.forEach(file => {
                const previewUrl = URL.createObjectURL(file);
                this.fileList.push({ file, previewUrl });
            });

            this.syncFileInput();
            this.activePreviewIndex = 0;
        },

        removeImage(index) {
            if (this.fileList[index]) {
                URL.revokeObjectURL(this.fileList[index].previewUrl);
            }
            this.fileList.splice(index, 1);
            if (this.activePreviewIndex >= this.fileList.length) {
                this.activePreviewIndex = Math.max(0, this.fileList.length - 1);
            }
            this.syncFileInput();
        },

        syncFileInput() {
            const input = document.getElementById('product-images-input');
            if (!input) return;

            try {
                const dt = new DataTransfer();
                this.fileList.forEach(item => {
                    if (item.file) {
                        dt.items.add(item.file);
                    }
                });
                input.files = dt.files;
            } catch (err) {
                console.error('DataTransfer sync error:', err);
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
            this.categoryText = opt ? (opt.dataset.name || opt.text) : '';
            this.categorySlug = opt ? (opt.dataset.slug || '') : '';
            const isShoe = this.categorySlug.includes('sepatu') || 
                           this.categorySlug.includes('shoe') || 
                           this.categoryText.toLowerCase().includes('sepatu') || 
                           this.categoryText.toLowerCase().includes('shoe');
            if (isShoe) {
                this.isShoeFormat = true;
                this.updateCombinedSize();
            } else {
                if (this.isShoeFormat) {
                    this.isShoeFormat = false;
                    // Automatically clear shoe size string when switching away to non-shoe category
                    if (this.size && (this.size.includes('US ') || this.size.includes('EU ') || this.size.includes(' CM') || this.size.includes('UK '))) {
                        this.size = '';
                    }
                }
            }
            this.filterBrands();
        },

        updateBrandText(e) {
            const opt = e.target.options[e.target.selectedIndex];
            this.brandText = opt && opt.value !== "" ? (opt.dataset.name || opt.text) : '';
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
</script>
@endsection
