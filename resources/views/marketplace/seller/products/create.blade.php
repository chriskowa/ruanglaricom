@extends('layouts.pacerhub')

@section('title', 'Add Product - RuangLari Market')

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
                <span class="text-white font-bold">Add Product</span>
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
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-neon">SELLER DASHBOARD</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white uppercase tracking-tight font-sans">
                    ADD NEW <span class="text-neon">PRODUCT</span>
                </h1>
                <p class="text-slate-300 text-xs md:text-sm mt-1">
                    Pasang iklan gear original atau tiket race slot Anda untuk menjangkau ribuan pelari di komunitas.
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

                <form action="{{ route('marketplace.seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

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
                            <input type="text" name="title" required value="{{ old('title') }}"
                                class="w-full bg-[#0a0e17] border @error('title') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                placeholder="Contoh: Nike Vaporfly 3 Ekiden Edition 2024">
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
                                        class="w-full bg-[#0a0e17] border @error('category_id') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer">
                                        <option value="" disabled selected>Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} data-slug="{{ $category->slug }}">{{ $category->name }}</option>
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
                                        class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer">
                                        <option value="" selected>Pilih Brand (Opsional)</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}">{{ $brand->name }}</option>
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
                                <input type="text" name="size" value="{{ old('size') }}"
                                    class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                    placeholder="Contoh: US 9 / EU 42.5 / Size M">
                            </div>
                        </div>

                        <!-- Type & Condition Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Type -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Tipe Produk <span class="text-rose-400">*</span>
                                </label>
                                <div class="relative">
                                    <select name="type" id="type-select" onchange="toggleType(this.value)" 
                                        class="w-full bg-[#0a0e17] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white appearance-none focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition cursor-pointer">
                                        <option value="physical">Barang Fisik (Sepatu / Apparel / Gear)</option>
                                        <option value="digital_slot">Slot Race / Tiket Lari</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Condition -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Kondisi Barang <span class="text-rose-400">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-[#0a0e17] cursor-pointer hover:border-slate-500 transition has-[:checked]:border-white has-[:checked]:bg-white/5">
                                        <input type="radio" name="condition" value="new" class="sr-only" checked>
                                        <span class="text-xs font-mono font-bold text-white uppercase tracking-wider">BARU (BNIB / BNWT)</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-700 bg-[#0a0e17] cursor-pointer hover:border-slate-500 transition has-[:checked]:border-white has-[:checked]:bg-white/5">
                                        <input type="radio" name="condition" value="used" class="sr-only">
                                        <span class="text-xs font-mono font-bold text-white uppercase tracking-wider">BEKAS (PRE-LOVED)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Skema Jual & Harga -->
                    <div class="space-y-4 pt-4 border-t border-slate-800">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                            <span class="text-xs font-mono font-black text-neon uppercase tracking-wider">02.</span>
                            <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-white">Skema Jual & Harga</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Sale Mode Card -->
                            <div class="p-4 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-2.5">
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-300">
                                    Mode Penjualan
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                        <input type="radio" name="sale_type" value="fixed" class="sr-only" checked onchange="toggleSaleType(this.value)">
                                        <span class="text-xs font-bold text-white font-mono">Jual Langsung</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                        <input type="radio" name="sale_type" value="auction" class="sr-only" onchange="toggleSaleType(this.value)">
                                        <span class="text-xs font-bold text-white font-mono">Lelang (Bidding)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Fulfillment Mode Card -->
                            <div class="p-4 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-2.5">
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-300">
                                    Metode Pengiriman (Fulfillment)
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                        <input type="radio" name="fulfillment_mode" value="self_ship" class="sr-only" checked onchange="toggleFulfillment(this.value)">
                                        <span class="text-xs font-bold text-white font-mono">Kirim Sendiri</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2.5 rounded-lg border border-slate-700 bg-slate-900 cursor-pointer hover:border-slate-500 transition has-[:checked]:border-neon has-[:checked]:bg-neon/10">
                                        <input type="radio" name="fulfillment_mode" value="consignment" class="sr-only" onchange="toggleFulfillment(this.value)">
                                        <span class="text-xs font-bold text-white font-mono">Titip Jual</span>
                                    </label>
                                </div>
                                <div id="consignment-hint" class="text-[11px] text-slate-400 hidden pt-1">
                                    *Barang titip jual akan diverifikasi tim RuangLari sebelum dikirim ke pembeli.
                                </div>
                            </div>
                        </div>

                        <!-- Fixed Price Fields -->
                        <div id="fixed-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Harga (Rp) <span class="text-rose-400">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-mono font-bold text-slate-400">Rp</span>
                                    <input type="number" name="price" min="0" value="{{ old('price') }}"
                                        class="w-full bg-[#0a0e17] border @error('price') border-rose-500 @else border-slate-700 @enderror rounded-xl pl-11 pr-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                        placeholder="0">
                                </div>
                                @error('price') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                    Jumlah Stok <span class="text-rose-400">*</span>
                                </label>
                                <input type="number" name="stock" min="1" value="{{ old('stock', 1) }}"
                                    class="w-full bg-[#0a0e17] border @error('stock') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
                                @error('stock') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Auction Fields (Hidden by default) -->
                        <div id="auction-fields" class="hidden p-5 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Harga Awal Lelang (Starting Price) <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-mono font-bold text-slate-400">Rp</span>
                                        <input type="number" name="starting_price" min="0" value="{{ old('starting_price') }}"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white transition"
                                            placeholder="0">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Kelipatan Bid (Min Increment) <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-mono font-bold text-slate-400">Rp</span>
                                        <input type="number" name="min_increment" min="0" value="{{ old('min_increment') }}"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white transition"
                                            placeholder="Contoh: 25000">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Lelang Berakhir Pada <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="datetime-local" name="auction_end_at" value="{{ old('auction_end_at') }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-3 text-xs text-white focus:outline-none focus:border-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Reserve Price (Opsional)
                                    </label>
                                    <input type="number" name="reserve_price" min="0" value="{{ old('reserve_price') }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white transition"
                                        placeholder="Harga minimal deal">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                        Buy Now Price (Opsional)
                                    </label>
                                    <input type="number" name="buy_now_price" min="0" value="{{ old('buy_now_price') }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white font-mono placeholder-slate-500 focus:outline-none focus:border-white transition"
                                        placeholder="Beli langsung seketika">
                                </div>
                            </div>
                        </div>

                        <!-- Titip Jual Dropoff Fields -->
                        <div id="consignment-fields" class="hidden p-5 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">Metode Serah Terima</label>
                                    <input type="text" name="dropoff_method"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition"
                                        placeholder="Kirim Ekspedisi / Dropoff Langsung">
                                </div>
                                <div>
                                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">Lokasi Pengirim / Titik Temu</label>
                                    <input type="text" name="dropoff_location"
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
                                class="w-full bg-[#0a0e17] border @error('description') border-rose-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition"
                                placeholder="Jelaskan kondisi detail, riwayat pemakaian (mileage km), kelengkapan box, dan alasan jual...">{{ old('description') }}</textarea>
                            @error('description') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                        </div>

                        <!-- Primary Image Dropzone -->
                        <div>
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-200 mb-2">
                                Foto Utama Produk <span class="text-rose-400">*</span>
                            </label>
                            <div class="w-full">
                                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full min-h-[140px] border-2 @error('image') border-rose-500 @else border-slate-700 @enderror border-dashed rounded-xl cursor-pointer bg-[#0a0e17] hover:bg-slate-850 hover:border-white/40 transition group p-6">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-white mb-2 transition">
                                            <i class="fas fa-cloud-upload-alt text-base"></i>
                                        </div>
                                        <p class="text-xs text-slate-300 mb-1">
                                            <span class="font-bold text-white group-hover:underline">Klik untuk upload foto</span> atau drag & drop
                                        </p>
                                        <p class="text-[11px] font-mono text-slate-500">JPG, PNG, WEBP (Maks. 2MB)</p>
                                    </div>
                                    <input id="dropzone-file" name="image" type="file" required accept="image/*" class="hidden" onchange="previewImage(this)" />
                                </label>
                            </div>
                            @error('image') <p class="text-rose-400 text-xs mt-1 font-mono">{{ $message }}</p> @enderror
                            
                            <!-- Image Preview Box -->
                            <div id="image-preview" class="mt-4 hidden">
                                <div class="inline-block relative rounded-xl overflow-hidden border border-slate-700 bg-slate-950 p-1">
                                    <img src="" class="h-36 w-36 object-cover rounded-lg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Detail Khusus Tiket / Race Slot (Hidden if not digital_slot) -->
                    <div id="slot-fields" style="display: none;" class="p-6 bg-[#0a0e17] rounded-xl border border-slate-700 space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-800">
                            <i class="fas fa-ticket-alt text-neon"></i>
                            <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-white">Detail Race Slot & BIB</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Race / Event</label>
                                <input type="text" name="meta_data[race_name]" placeholder="Contoh: Borobudur Marathon 2024" 
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition">
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-300 mb-2">Tanggal Race</label>
                                <input type="date" name="meta_data[race_date]" 
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-white transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-300 mb-2">Kebijakan Transfer BIB / Nama</label>
                            <input type="text" name="meta_data[transfer_policy]" placeholder="Contoh: Bisa ganti nama resmi sampai H-14 race" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-white transition">
                        </div>
                    </div>

                    <!-- Submit Button Area (Well-proportioned Add Product CTA) -->
                    <div class="flex items-center justify-between pt-6 border-t border-slate-800">
                        <a href="{{ route('marketplace.seller.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 text-xs font-bold font-mono transition">
                            Batal
                        </a>

                        <button type="submit" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-200 text-slate-950 font-black text-xs uppercase tracking-wider transition-all flex items-center gap-2 shadow-lg shadow-white/5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Add Product</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleType(val) {
    const slotFields = document.getElementById('slot-fields');
    if (slotFields) slotFields.style.display = val === 'digital_slot' ? 'block' : 'none';
}

function toggleSaleType(val) {
    const fixed = document.getElementById('fixed-fields');
    const auction = document.getElementById('auction-fields');
    const isAuction = val === 'auction';
    if (fixed) fixed.classList.toggle('hidden', isAuction);
    if (auction) auction.classList.toggle('hidden', !isAuction);
}

function toggleFulfillment(val) {
    const consignmentHint = document.getElementById('consignment-hint');
    const consignmentFields = document.getElementById('consignment-fields');
    const isConsignment = val === 'consignment';
    if (consignmentHint) consignmentHint.classList.toggle('hidden', !isConsignment);
    if (consignmentFields) consignmentFields.classList.toggle('hidden', !isConsignment);
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = preview ? preview.querySelector('img') : null;
    
    if (input.files && input.files[0] && img && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const saleTypeInput = document.querySelector('input[name="sale_type"]:checked');
    const fulfillmentModeInput = document.querySelector('input[name="fulfillment_mode"]:checked');
    
    if (saleTypeInput) toggleSaleType(saleTypeInput.value);
    if (fulfillmentModeInput) toggleFulfillment(fulfillmentModeInput.value);

    // Dynamic brand filter based on category
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const brandOptions = brandSelect ? Array.from(brandSelect.querySelectorAll('option')) : [];

    function filterBrands() {
        if (!categorySelect || !brandSelect) return;
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const selectedCategorySlug = selectedOption ? selectedOption.dataset.slug : null;
        
        brandOptions.forEach(option => {
            if (option.value === "") return;
            const categories = JSON.parse(option.dataset.categories || '[]');
            const isMatch = !selectedCategorySlug || categories.includes(selectedCategorySlug);
            option.hidden = !isMatch;
            option.disabled = !isMatch;
        });
    }

    if (categorySelect) categorySelect.addEventListener('change', filterBrands);
    filterBrands();
});
</script>
@endsection
