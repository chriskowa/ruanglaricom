@extends('layouts.pacerhub')

@section('title', 'Moderasi Catalog Produk Marketplace - Admin')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 font-sans bg-dark text-slate-200"
     x-data="{
        status: '{{ $status }}',
        q: '{{ $q }}',
        isFeatured: '{{ $isFeatured }}',
        loading: false,
        toast: { show: false, message: '', type: 'success' },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => this.toast.show = false, 4000);
        },

        fetchProducts(url = null) {
            this.loading = true;
            const targetUrl = url || '{{ route('admin.marketplace.products.index') }}';
            const params = new URLSearchParams({
                status: this.status,
                q: this.q,
                is_featured: this.isFeatured
            });

            fetch(targetUrl + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('product-rows-container').innerHTML = data.html;
                    document.getElementById('pagination-container').innerHTML = data.pagination;
                }
            })
            .catch(err => this.showToast('Gagal memuat produk.', 'error'))
            .finally(() => this.loading = false);
        },

        loadPage(url) {
            this.fetchProducts(url);
        },

        toggleFeatured(id) {
            fetch(`/admin/marketplace/products/${id}/toggle-featured`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    this.showToast(data.message, 'success');
                    const btn = document.getElementById(`btn-featured-${id}`);
                    const text = document.getElementById(`text-featured-${id}`);
                    if(data.is_featured) {
                        btn.setAttribute('data-featured', '1');
                        btn.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition border bg-amber-500/20 text-amber-300 border-amber-500/40';
                        text.innerText = 'Featured';
                    } else {
                        btn.setAttribute('data-featured', '0');
                        btn.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition border bg-slate-950 text-slate-500 border-slate-800 hover:text-amber-400';
                        text.innerText = '+ Featured';
                    }
                }
            });
        },

        toggleActive(id) {
            fetch(`/admin/marketplace/products/${id}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    this.showToast(data.message, 'success');
                    const btn = document.getElementById(`btn-active-${id}`);
                    const text = document.getElementById(`text-active-${id}`);
                    if(data.is_active) {
                        btn.setAttribute('data-active', '1');
                        btn.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition border bg-emerald-500/20 text-emerald-300 border-emerald-500/40';
                        text.innerText = 'Aktif';
                    } else {
                        btn.setAttribute('data-active', '0');
                        btn.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition border bg-rose-500/20 text-rose-300 border-rose-500/40';
                        text.innerText = 'Hidden';
                    }
                }
            });
        },

        deleteProduct(id) {
            if(!confirm('Apakah Anda yakin ingin menghapus produk ini dari katalog?')) return;
            fetch(`/admin/marketplace/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    this.showToast(data.message, 'success');
                    this.fetchProducts();
                }
            });
        }
     }">

    <!-- Toast Notification -->
    <div x-show="toast.show" 
         x-transition
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl border backdrop-blur-md shadow-2xl flex items-center gap-3 text-sm font-semibold max-w-sm"
         :class="toast.type === 'success' ? 'bg-emerald-950/90 border-emerald-500/30 text-emerald-400' : 'bg-red-950/90 border-red-500/30 text-red-400'"
         style="display: none;">
        <span x-text="toast.type === 'success' ? '✓' : '✗'" class="text-base font-bold"></span>
        <span x-text="toast.message"></span>
    </div>

    <div class="max-w-7xl mx-auto">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <p class="text-neon font-mono text-[10px] tracking-widest uppercase mb-1 font-bold">Admin Control Center</p>
                <h1 class="text-3xl font-black text-white italic tracking-tighter uppercase">
                    Moderasi <span class="text-neon">Catalog Produk</span>
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.marketplace.orders.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition">
                    <i class="fas fa-shopping-bag mr-1.5"></i> Pesanan & Dispute
                </a>
            </div>
        </div>

        <!-- Filter Controls (AJAX) -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 mb-6 space-y-4">
            
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none border-b border-slate-800/80">
                <button @click="status = ''; fetchProducts()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === '' ? 'bg-neon text-dark border-neon shadow-lg shadow-neon/15' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Semua Produk
                </button>
                <button @click="status = 'active'; fetchProducts()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'active' ? 'bg-emerald-500 text-dark border-emerald-400' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Aktif
                </button>
                <button @click="status = 'sold'; fetchProducts()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'sold' ? 'bg-cyan-500 text-dark border-cyan-400' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Terjual (Sold)
                </button>
                <button @click="status = 'hidden'; fetchProducts()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'hidden' ? 'bg-rose-500 text-white border-rose-400' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Disembunyikan (Hidden)
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-8 relative">
                    <input type="text" 
                           x-model="q" 
                           @input.debounce.300ms="fetchProducts()" 
                           placeholder="Cari Judul Produk, Slug, atau Nama Seller..." 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-neon transition">
                    <div x-show="loading" class="absolute right-3 top-2.5 text-neon">
                        <i class="fas fa-circle-notch fa-spin text-xs"></i>
                    </div>
                </div>
                <div class="md:col-span-4">
                    <select x-model="isFeatured" @change="fetchProducts()" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-slate-300 focus:outline-none focus:border-neon transition">
                        <option value="">Semua Featured</option>
                        <option value="1">Hanya Featured</option>
                        <option value="0">Bukan Featured</option>
                    </select>
                </div>
            </div>

        </div>

        <!-- Products Table Container -->
        <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850 rounded-2xl overflow-hidden shadow-2xl relative">
            
            <!-- Loading Overlay -->
            <div x-show="loading" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm z-20 flex items-center justify-center">
                <div class="flex items-center gap-3 bg-slate-900 border border-slate-800 px-5 py-3 rounded-2xl shadow-2xl">
                    <i class="fas fa-spinner fa-spin text-neon text-lg"></i>
                    <span class="text-xs font-bold text-white uppercase tracking-wider font-mono">Memuat Produk...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-slate-950/80 border-b border-slate-800">
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Produk / Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Penjual (Seller)</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Harga & Stok</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Views Stats</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Status Moderasi</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="product-rows-container" class="divide-y divide-slate-800/40">
                        @include('admin.marketplace.products.partials.product-rows', ['products' => $products])
                    </tbody>
                </table>
            </div>

            <div id="pagination-container">
                @include('admin.marketplace.products.partials.pagination', ['products' => $products])
            </div>
        </div>

    </div>

</div>
@endsection
