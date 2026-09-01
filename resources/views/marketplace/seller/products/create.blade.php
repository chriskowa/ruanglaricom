@extends('layouts.pacerhub')

@section('title', 'Add Product - RuangLari Market')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 sm:px-6 lg:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark"
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
                
                <div class="rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-2xl overflow-hidden">
                    
                    <!-- Card Header -->
                    <div class="p-6 md:p-8 border-b border-slate-800 bg-[#0c121e]">
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

                    <div class="p-6 md:p-8">
                        
                        <!-- Error Validation Box -->
                        @if ($errors->any())
                            <div class="mb-8 p-4 bg-rose-950/60 border border-rose-700/80 rounded-xl text-rose-200 text-xs">
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

                        <form action="{{ route('marketplace.seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="product-create-form" @submit="syncFileInput()">
                            @csrf

                            <!-- 01. Informasi Dasar Produk -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                    <span class="text-xs font-black text-neon uppercase tracking-wider">01.</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Informasi Dasar Gear</h2>
                                </div>

                                <!-- Product Title -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Judul Produk <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" name="title" x-model="title" required
                                        class="w-full bg-[#0a0e17] border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                        placeholder="Contoh: Nike Vaporfly 3 Ekiden Edition 2024">
                                    @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <!-- Category -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Kategori <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="category_id" id="category-select" x-model="categoryId" @change="updateCategoryText($event)" required 
                                                class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                                                <option value="" disabled selected class="bg-[#0f172a] text-slate-400">Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} data-slug="{{ $category->slug }}" data-name="{{ $category->name }}" class="bg-[#0f172a] text-white">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Brand -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Brand / Merek
                                        </label>
                                        <div class="relative">
                                            <select name="brand_id" id="brand-select" x-model="brandId" @change="updateBrandText($event)"
                                                class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                                                <option value="" selected class="bg-[#0f172a] text-slate-400">Pilih Brand (Opsional)</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" data-name="{{ $brand->name }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" class="bg-[#0f172a] text-white">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Size / Ukuran -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Ukuran / Size
                                        </label>
                                        <input type="text" name="size" x-model="size"
                                            class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                            placeholder="Contoh: US 9 / EU 42.5 / Size M">
                                    </div>
                                </div>

                                <!-- Type & Condition Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Type -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Tipe Produk <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="type" id="type-select" x-model="productType" 
                                                class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer [&>option]:bg-[#0f172a] [&>option]:text-white">
                                                <option value="physical" class="bg-[#0f172a] text-white">Barang Fisik (Sepatu / Apparel / Aksesoris)</option>
                                                <option value="digital_slot" class="bg-[#0f172a] text-white">Slot Race / Tiket Lari</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Condition -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Kondisi Barang <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-[#0a0e17] cursor-pointer hover:border-slate-500 transition has-[:checked]:border-white has-[:checked]:bg-white/5">
                                                <input type="radio" name="condition" value="new" x-model="condition" class="sr-only">
                                                <span class="text-xs font-bold text-white uppercase tracking-wider">BARU (BNIB)</span>
                                            </label>
                                            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-[#0a0e17] cursor-pointer hover:border-slate-500 transition has-[:checked]:border-white has-[:checked]:bg-white/5">
                                                <input type="radio" name="condition" value="used" x-model="condition" class="sr-only">
                                                <span class="text-xs font-bold text-white uppercase tracking-wider">BEKAS (USED)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 02. Skema Jual & Harga -->
                            <div class="space-y-4 pt-4 border-t border-slate-800">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                    <span class="text-xs font-black text-neon uppercase tracking-wider">02.</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Skema Jual & Harga</h2>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Sale Mode Card -->
                                    <div class="p-4 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-2.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                            Mode Penjualan
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                                <input type="radio" name="sale_type" value="fixed" x-model="saleType" class="sr-only">
                                                <span class="text-xs font-bold text-white">Jual Langsung</span>
                                            </label>
                                            <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                                <input type="radio" name="sale_type" value="auction" x-model="saleType" class="sr-only">
                                                <span class="text-xs font-bold text-white">Lelang (Bid)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Fulfillment Mode Card -->
                                    <div class="p-4 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-2.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                            Metode Pengiriman
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                                <input type="radio" name="fulfillment_mode" value="self_ship" x-model="fulfillmentMode" class="sr-only">
                                                <span class="text-xs font-bold text-white">Kirim Sendiri</span>
                                            </label>
                                            <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                                <input type="radio" name="fulfillment_mode" value="consignment" x-model="fulfillmentMode" class="sr-only">
                                                <span class="text-xs font-bold text-white">Titip Jual</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fixed Price Fields -->
                                <div x-show="saleType === 'fixed'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Harga (Rp) <span class="text-rose-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                            <input type="number" name="price" min="0" x-model="price"
                                                class="w-full bg-[#0a0e17] border @error('price') border-rose-500 @else border-slate-700 @enderror rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                                placeholder="0">
                                        </div>
                                        @error('price') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                            Jumlah Stok <span class="text-rose-400">*</span>
                                        </label>
                                        <input type="number" name="stock" min="1" x-model="stock"
                                            class="w-full bg-[#0a0e17] border @error('stock') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
                                        @error('stock') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Auction Fields -->
                                <div x-show="saleType === 'auction'" x-cloak class="p-5 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                                Harga Awal Lelang (Starting) <span class="text-rose-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                                <input type="number" name="starting_price" min="0" x-model="startingPrice"
                                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                                Kelipatan Bid (Increment) <span class="text-rose-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                                <input type="number" name="min_increment" min="0"
                                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                    placeholder="Contoh: 25000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                                Lelang Berakhir Pada
                                            </label>
                                            <input type="datetime-local" name="auction_end_at"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-3 text-xs text-white focus:outline-none focus:border-white transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                                Reserve Price (Opsional)
                                            </label>
                                            <input type="number" name="reserve_price" min="0"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                placeholder="Harga minimal deal">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                                Buy Now Price (Opsional)
                                            </label>
                                            <input type="number" name="buy_now_price" min="0"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                placeholder="Beli langsung">
                                        </div>
                                    </div>
                                </div>

                                <!-- Titip Jual Dropoff & Owner Contact Fields -->
                                <div x-show="fulfillmentMode === 'consignment'" x-cloak class="p-5 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                                    <div class="border-b border-slate-800 pb-2">
                                        <p class="text-xs font-bold text-white uppercase tracking-wider">Informasi Pemilik & Penyerahan Barang</p>
                                        <p class="text-xs text-slate-400">Masukkan kontak pemilik asli (teman / penjual sumber) jika Anda menjualkan barang orang lain.</p>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">Nama Pemilik Asli (Opsional)</label>
                                            <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                placeholder="Contoh: Budi (Teman) / FB Seller">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">No. WhatsApp Pemilik (Opsional)</label>
                                            <input type="text" name="owner_phone" value="{{ old('owner_phone') }}"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                placeholder="Contoh: 08123456789">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">Metode Serah Terima</label>
                                            <input type="text" name="dropoff_method" value="{{ old('dropoff_method') }}"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                placeholder="Kirim Ekspedisi / Dropoff Langsung">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">Lokasi Seller / Kota</label>
                                            <input type="text" name="dropoff_location" value="{{ old('dropoff_location') }}"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                                placeholder="Kota / Daerah Asal Barang">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 03. Foto Produk (Dropzone Maksimal 4 Foto) -->
                            <div class="space-y-4 pt-4 border-t border-slate-800">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-neon uppercase tracking-wider">03.</span>
                                        <h2 class="text-xs font-bold uppercase tracking-wider text-white">Foto Produk (Maksimal 4 Foto) <span class="text-rose-400">*</span></h2>
                                    </div>
                                    <span class="text-xs text-slate-400 font-semibold uppercase">
                                        <span x-text="fileList.length">0</span> / 4 Foto Terpilih
                                    </span>
                                </div>

                                <p class="text-xs text-slate-300">
                                    Unggah hingga 4 foto produk (foto pertama menjadi foto utama/sampul). Mendukung format JPG, PNG, WEBP hingga 3MB per file.
                                </p>

                                <!-- Hidden File Input that holds the actual DataTransfer files -->
                                <input type="file" id="product-images-input" name="images[]" multiple accept="image/*" class="hidden" @change="handleFilesFromInput($event)">

                                <!-- Dropzone Drag & Drop Area -->
                                <div 
                                    id="product-dropzone"
                                    class="relative border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer bg-[#0a0e17] group select-none"
                                    :class="isDragging ? 'border-neon bg-neon/5 scale-[1.01]' : 'border-slate-700 hover:border-slate-500 hover:bg-slate-900/60'"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleFilesDrop($event)"
                                    @click="triggerFileInput()"
                                    x-show="fileList.length < 4"
                                >
                                    <div class="flex flex-col items-center justify-center space-y-3 pointer-events-none">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-800/90 border border-slate-700 flex items-center justify-center text-slate-300 group-hover:text-neon group-hover:border-neon/50 transition shadow-inner">
                                            <i class="fas fa-cloud-arrow-up text-xl text-neon"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-white">
                                                Tarik &amp; letakkan foto di sini, atau <span class="text-neon underline">pilih dari galeri</span>
                                            </p>
                                            <p class="text-xs text-slate-400 mt-1">
                                                Maksimal 4 foto (JPEG, PNG, WEBP hingga 3MB)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Thumbnails Grid (when files are selected) -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5" x-show="fileList.length > 0" x-cloak>
                                    <template x-for="(item, idx) in fileList" :key="idx">
                                        <div class="relative aspect-square rounded-xl border-2 border-neon/60 bg-slate-950 overflow-hidden flex flex-col items-center justify-center text-center group shadow-md">
                                            <img :src="item.previewUrl" class="w-full h-full object-cover">
                                            
                                            <!-- Slot Badge -->
                                            <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-dark/85 backdrop-blur border border-slate-700 text-[9px] font-bold text-white uppercase"
                                                  x-text="idx === 0 ? 'UTAMA' : 'FOTO ' + (idx + 1)"></span>
                                            
                                            <!-- Remove Button -->
                                            <button type="button" @click.stop="removeImage(idx)" 
                                                    class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-xs shadow-md transition cursor-pointer"
                                                    title="Hapus foto ini">
                                                <i class="fas fa-times text-[10px]"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Add more slot if less than 4 -->
                                    <template x-if="fileList.length > 0 && fileList.length < 4">
                                        <div @click="triggerFileInput()"
                                             class="relative aspect-square rounded-xl border-2 border-dashed border-slate-700 bg-[#0a0e17] hover:border-slate-500 hover:bg-slate-900/80 cursor-pointer transition flex flex-col items-center justify-center text-center p-3 group select-none">
                                            <div class="w-8 h-8 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-400 group-hover:text-white mb-1.5 transition">
                                                <i class="fas fa-plus text-xs"></i>
                                            </div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Tambah Foto</span>
                                            <span class="text-xs text-slate-500 mt-0.5" x-text="'Slot ' + (fileList.length + 1) + '/4'"></span>
                                        </div>
                                    </template>
                                </div>

                                @error('images') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                @error('images.*') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                            </div>

                            <!-- 04. Deskripsi Lengkap Produk -->
                            <div class="space-y-4 pt-4 border-t border-slate-800">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                    <span class="text-xs font-black text-neon uppercase tracking-wider">04.</span>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Deskripsi Lengkap Gear</h2>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Deskripsi & Riwayat Penggunaan <span class="text-rose-400">*</span>
                                    </label>
                                    <textarea name="description" rows="5" required x-model="description"
                                        class="w-full bg-[#0a0e17] border @error('description') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                        placeholder="Jelaskan kondisi detail, perkiraan mileage (km pemakaian), kelengkapan box/tag, serta alasan jual..."></textarea>
                                    @error('description') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- 05. Detail Race Slot (Otomatis muncul jika type digital_slot) -->
                            <div x-show="productType === 'digital_slot'" x-cloak class="p-6 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                                    <i class="fas fa-ticket-alt text-neon"></i>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Detail Race Slot & BIB</h3>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Race / Event</label>
                                        <input type="text" name="meta_data[race_name]" placeholder="Contoh: Borobudur Marathon 2024" 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Tanggal Race</label>
                                        <input type="date" name="meta_data[race_date]" 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-white transition">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kebijakan Transfer BIB / Nama</label>
                                    <input type="text" name="meta_data[transfer_policy]" placeholder="Contoh: Bisa ganti nama resmi sampai H-14 race" 
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition">
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="pt-6 border-t border-slate-800 flex items-center justify-end gap-3">
                                <a href="{{ route('marketplace.seller.products.index') }}" 
                                    class="px-6 py-3 rounded-xl border border-slate-700 bg-slate-900 text-slate-300 hover:text-white hover:bg-slate-800 text-xs font-bold transition">
                                    Batal
                                </a>
                                <button type="submit" 
                                    class="px-8 py-3.5 rounded-xl bg-neon hover:bg-white hover:text-dark text-dark font-black text-xs uppercase tracking-wider transition shadow-lg shadow-neon/15 flex items-center gap-2">
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
                <div class="rounded-lg bg-[#0c121e] border border-slate-700/80 p-5 space-y-4 shadow-xl">
                    
                    <!-- Preview Image Frame -->
                    <div class="aspect-square rounded-md overflow-hidden bg-[#131b2c] relative flex items-center justify-center border border-slate-800">
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
                    <div class="p-3 rounded-md bg-[#0a0e17] border border-slate-800/80 flex items-center justify-between gap-3">
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
        title: '{{ old('title', '') }}',
        categoryId: '{{ old('category_id', '') }}',
        categoryText: '',
        brandId: '{{ old('brand_id', '') }}',
        brandText: '',
        size: '{{ old('size', '') }}',
        productType: '{{ old('type', 'physical') }}',
        condition: '{{ old('condition', 'new') }}',
        saleType: '{{ old('sale_type', 'fixed') }}',
        fulfillmentMode: '{{ old('fulfillment_mode', 'self_ship') }}',
        price: '{{ old('price', '') }}',
        stock: '{{ old('stock', 1) }}',
        startingPrice: '{{ old('starting_price', '') }}',
        description: '{{ old('description', '') }}',
        
        isDragging: false,
        fileList: [],
        activePreviewIndex: 0,

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
            const validImageFiles = newFiles.filter(f => f.type.startsWith('image/'));
            if (validImageFiles.length === 0) return;

            const availableSlots = 4 - this.fileList.length;
            if (availableSlots <= 0) return;

            const toAdd = validImageFiles.slice(0, availableSlots);

            toAdd.forEach(file => {
                const previewUrl = URL.createObjectURL(file);
                this.fileList.push({ file, previewUrl });
            });

            this.syncFileInput();
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

        updateCategoryText(e) {
            const opt = e.target.options[e.target.selectedIndex];
            this.categoryText = opt ? (opt.dataset.name || opt.text) : '';
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
