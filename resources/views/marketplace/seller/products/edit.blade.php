@extends('layouts.pacerhub')

@section('title', 'Edit Product - RuangLari Market')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 sm:px-6 lg:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark"
     x-data="productEditForm()">
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

                        <!-- Multi-Image Management (Dropzone Max 4 Photos) -->
                        <div class="space-y-4 pt-2 border-t border-slate-800">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200">
                                    Galeri Foto Produk (Maksimal 4 Foto)
                                </label>
                                <span class="text-[10px] font-mono text-slate-400 uppercase">
                                    <span x-text="totalImagesCount">0</span> / 4 Foto Total
                                </span>
                            </div>

                            <!-- Hidden inputs for delete_images -->
                            <template x-for="id in deletedImageIds" :key="id">
                                <input type="hidden" name="delete_images[]" :value="id">
                            </template>

                            <!-- Hidden input for new uploaded files -->
                            <input type="file" id="edit-images-input" name="images[]" multiple accept="image/*" class="hidden" @change="handleFilesFromInput($event)">

                            <!-- Existing Photos Section -->
                            <template x-if="existingImages.length > 0">
                                <div class="space-y-2">
                                    <span class="text-[11px] font-mono text-slate-400 uppercase font-bold">Foto Saat Ini:</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                                        <template x-for="img in existingImages" :key="img.id">
                                            <div class="relative aspect-square rounded-xl border transition-all overflow-hidden flex flex-col items-center justify-center text-center group shadow-md"
                                                 :class="isMarkedDeleted(img.id) ? 'border-rose-700/60 bg-rose-950/40 opacity-50 grayscale' : 'border-slate-700 bg-slate-950'">
                                                <img :src="img.url" class="w-full h-full object-cover">
                                                
                                                <template x-if="img.is_primary && !isMarkedDeleted(img.id)">
                                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-neon text-dark text-[8px] font-black uppercase font-mono shadow">
                                                        UTAMA
                                                    </span>
                                                </template>

                                                <template x-if="isMarkedDeleted(img.id)">
                                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-rose-600 text-white text-[8px] font-black uppercase font-mono shadow">
                                                        AKAN DIHAPUS
                                                    </span>
                                                </template>

                                                <button type="button" @click="toggleDeleteExisting(img.id)"
                                                        class="absolute top-1.5 right-1.5 px-2 py-1 rounded-md text-[10px] font-mono font-bold transition flex items-center gap-1 shadow cursor-pointer"
                                                        :class="isMarkedDeleted(img.id) ? 'bg-slate-800 hover:bg-slate-700 text-slate-200' : 'bg-rose-600 hover:bg-rose-500 text-white'"
                                                        :title="isMarkedDeleted(img.id) ? 'Batalkan hapus foto' : 'Hapus foto ini'">
                                                    <i class="fas" :class="isMarkedDeleted(img.id) ? 'fa-undo text-[9px]' : 'fa-trash-alt text-[9px]'"></i>
                                                    <span x-text="isMarkedDeleted(img.id) ? 'Batal' : 'Hapus'"></span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Newly Added Photos (Dropzone Preview) -->
                            <div class="space-y-2" x-show="newFileList.length > 0" x-cloak>
                                <span class="text-[11px] font-mono text-neon uppercase font-bold">Foto Baru Akan Diunggah:</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                                    <template x-for="(item, idx) in newFileList" :key="idx">
                                        <div class="relative aspect-square rounded-xl border-2 border-neon/60 bg-slate-950 overflow-hidden flex flex-col items-center justify-center text-center group shadow-md">
                                            <img :src="item.previewUrl" class="w-full h-full object-cover">
                                            
                                            <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-dark/85 backdrop-blur border border-slate-700 text-[8px] font-mono font-bold text-white uppercase"
                                                  x-text="'BARU ' + (idx + 1)"></span>
                                            
                                            <button type="button" @click.stop="removeNewFile(idx)" 
                                                    class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-xs shadow-md transition cursor-pointer"
                                                    title="Hapus foto baru ini">
                                                <i class="fas fa-times text-[10px]"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Dropzone for Adding More Photos -->
                            <div 
                                id="product-dropzone"
                                class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer bg-[#0a0e17] group select-none"
                                :class="isDragging ? 'border-neon bg-neon/5 scale-[1.01]' : 'border-slate-700 hover:border-slate-500 hover:bg-slate-900/60'"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleFilesDrop($event)"
                                @click="triggerFileInput()"
                                x-show="availableSlots > 0"
                            >
                                <div class="flex flex-col items-center justify-center space-y-2.5 pointer-events-none">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800/90 border border-slate-700 flex items-center justify-center text-slate-300 group-hover:text-neon group-hover:border-neon/50 transition shadow-inner">
                                        <i class="fas fa-cloud-arrow-up text-lg text-neon"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-white">
                                            Tarik &amp; letakkan foto baru di sini, atau <span class="text-neon underline">pilih dari galeri</span>
                                        </p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                            Tersisa <span x-text="availableSlots" class="text-white font-bold"></span> slot foto tambahan (JPEG, PNG, WEBP hingga 3MB)
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @error('images') <p class="text-rose-400 text-xs font-mono">{{ $message }}</p> @enderror
                            @error('images.*') <p class="text-rose-400 text-xs font-mono">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Submit Button Area -->
                    <div class="flex items-center justify-between pt-6 border-t border-slate-800">
                        <a href="{{ route('marketplace.seller.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 text-xs font-bold font-mono transition">
                            Batal
                        </a>

                        <button type="submit" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-200 text-slate-950 font-black text-xs uppercase tracking-wider transition-all flex items-center gap-2 shadow-lg shadow-white/5 cursor-pointer">
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
function productEditForm() {
    return {
        existingImages: @json($product->images->map(fn($img) => [
            'id' => $img->id,
            'url' => asset('storage/' . $img->image_path),
            'is_primary' => (bool)$img->is_primary
        ])),
        deletedImageIds: [],
        newFileList: [],
        isDragging: false,

        get activeExistingCount() {
            return this.existingImages.length - this.deletedImageIds.length;
        },

        get totalImagesCount() {
            return this.activeExistingCount + this.newFileList.length;
        },

        get availableSlots() {
            return Math.max(0, 4 - this.totalImagesCount);
        },

        toggleDeleteExisting(id) {
            const index = this.deletedImageIds.indexOf(id);
            if (index > -1) {
                this.deletedImageIds.splice(index, 1);
            } else {
                this.deletedImageIds.push(id);
            }
        },

        isMarkedDeleted(id) {
            return this.deletedImageIds.includes(id);
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
            if (e.target && e.target.files) {
                this.addNewFiles(Array.from(e.target.files));
                e.target.value = '';
            }
        },

        addNewFiles(files) {
            const validImageFiles = files.filter(f => f.type.startsWith('image/'));
            if (validImageFiles.length === 0) return;

            const slots = this.availableSlots;
            if (slots <= 0) return;

            const toAdd = validImageFiles.slice(0, slots);
            toAdd.forEach(file => {
                const previewUrl = URL.createObjectURL(file);
                this.newFileList.push({ file, previewUrl });
            });

            this.syncFileInput();
        },

        removeNewFile(index) {
            if (this.newFileList[index]) {
                URL.revokeObjectURL(this.newFileList[index].previewUrl);
            }
            this.newFileList.splice(index, 1);
            this.syncFileInput();
        },

        syncFileInput() {
            const input = document.getElementById('edit-images-input');
            if (!input) return;

            const dt = new DataTransfer();
            this.newFileList.forEach(item => {
                dt.items.add(item.file);
            });
            input.files = dt.files;
        }
    };
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
