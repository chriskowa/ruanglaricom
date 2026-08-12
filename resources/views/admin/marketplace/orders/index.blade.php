@extends('layouts.pacerhub')

@section('title', 'Marketplace Orders & Dispute Resolution - Admin')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 font-sans bg-dark text-slate-200" 
     x-data="{
        status: '{{ $status }}',
        q: '{{ $q }}',
        dateFrom: '{{ $dateFrom }}',
        dateTo: '{{ $dateTo }}',
        loading: false,
        activeOrder: null,
        showDisputeModal: false,
        adminNotes: '',
        submittingAction: false,
        toast: { show: false, message: '', type: 'success' },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => this.toast.show = false, 4000);
        },

        fetchOrders(url = null) {
            this.loading = true;
            const targetUrl = url || '{{ route('admin.marketplace.orders.index') }}';
            const params = new URLSearchParams({
                status: this.status,
                q: this.q,
                date_from: this.dateFrom,
                date_to: this.dateTo
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
                    document.getElementById('order-rows-container').innerHTML = data.html;
                    document.getElementById('pagination-container').innerHTML = data.pagination;
                    if(data.metrics) {
                        document.getElementById('metric-gmv').innerText = 'Rp ' + Number(data.metrics.total_gmv).toLocaleString('id-ID');
                        document.getElementById('metric-income').innerText = 'Rp ' + Number(data.metrics.total_platform_income).toLocaleString('id-ID');
                        document.getElementById('metric-escrow').innerText = 'Rp ' + Number(data.metrics.total_escrow_funds).toLocaleString('id-ID');
                        document.getElementById('metric-disputed').innerText = data.metrics.disputed_count;
                    }
                }
            })
            .catch(err => {
                this.showToast('Gagal memuat data pesanan.', 'error');
            })
            .finally(() => this.loading = false);
        },

        loadPage(url) {
            this.fetchOrders(url);
        },

        openDisputeModal(order) {
            this.activeOrder = order;
            this.adminNotes = '';
            this.showDisputeModal = true;
        },

        resolveDispute(actionType) {
            if(!this.activeOrder) return;
            this.submittingAction = true;
            const endpoint = actionType === 'release' 
                ? `/admin/marketplace/orders/${this.activeOrder.id}/resolve-release`
                : `/admin/marketplace/orders/${this.activeOrder.id}/resolve-refund`;

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ admin_notes: this.adminNotes })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    this.showToast(data.message, 'success');
                    this.showDisputeModal = false;
                    this.fetchOrders();
                } else {
                    this.showToast(data.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(err => this.showToast('Terjadi kesalahan jaringan.', 'error'))
            .finally(() => this.submittingAction = false);
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
                    Marketplace <span class="text-neon">Orders & Dispute</span>
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.marketplace.products.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition">
                    <i class="fas fa-boxes mr-1.5"></i> Moderasi Produk
                </a>
                <a href="{{ route('admin.transactions.index', ['tab' => 'withdrawals']) }}" class="px-4 py-2.5 rounded-xl bg-neon text-dark hover:bg-white font-black text-xs transition shadow-lg shadow-neon/15">
                    <i class="fas fa-wallet mr-1.5"></i> Kelola Withdraw (WD)
                </a>
            </div>
        </div>

        <!-- Financial Metrics Banner -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl relative overflow-hidden">
                <div class="text-xs text-slate-400 font-mono uppercase tracking-wider mb-1">Total GMV Penjualan</div>
                <div class="text-xl md:text-2xl font-black text-white font-mono" id="metric-gmv">
                    Rp {{ number_format($metrics['total_gmv'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl relative overflow-hidden">
                <div class="text-xs text-slate-400 font-mono uppercase tracking-wider mb-1">Pendapatan Fee Platform</div>
                <div class="text-xl md:text-2xl font-black text-neon font-mono" id="metric-income">
                    Rp {{ number_format($metrics['total_platform_income'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl relative overflow-hidden">
                <div class="text-xs text-slate-400 font-mono uppercase tracking-wider mb-1">Dana Escrow (Mengendap)</div>
                <div class="text-xl md:text-2xl font-black text-cyan-400 font-mono" id="metric-escrow">
                    Rp {{ number_format($metrics['total_escrow_funds'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl relative overflow-hidden">
                <div class="text-xs text-slate-400 font-mono uppercase tracking-wider mb-1">Pesanan Berkendala</div>
                <div class="text-xl md:text-2xl font-black text-rose-400 font-mono flex items-center gap-2">
                    <span id="metric-disputed">{{ $metrics['disputed_count'] }}</span>
                    <span class="text-xs font-sans font-bold text-slate-400">Kasus</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls (Full AJAX) -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 mb-6 space-y-4">
            
            <!-- Status Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none border-b border-slate-800/80">
                <button @click="status = ''; fetchOrders()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === '' ? 'bg-neon text-dark border-neon shadow-lg shadow-neon/15' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Semua Status
                </button>
                <button @click="status = 'disputed'; fetchOrders()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap flex items-center gap-1.5"
                        :class="status === 'disputed' ? 'bg-rose-500 text-white border-rose-500 shadow-lg shadow-rose-500/20' : 'bg-slate-950 text-rose-400 border-slate-800 hover:border-rose-500/40'">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Berkendala (Disputed)</span>
                </button>
                <button @click="status = 'paid'; fetchOrders()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'paid' ? 'bg-cyan-500 text-dark border-cyan-400' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Paid (Sudah Dibayar)
                </button>
                <button @click="status = 'shipped'; fetchOrders()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'shipped' ? 'bg-blue-500 text-white border-blue-400' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Shipped (Dikirim)
                </button>
                <button @click="status = 'completed'; fetchOrders()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'completed' ? 'bg-emerald-500 text-dark border-emerald-400' : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700'">
                    Completed (Selesai)
                </button>
                <button @click="status = 'cancelled'; fetchOrders()" 
                        class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border whitespace-nowrap"
                        :class="status === 'cancelled' ? 'bg-slate-800 text-white border-slate-700' : 'bg-slate-950 text-slate-400 border-slate-800 hover:border-slate-700'">
                    Cancelled
                </button>
            </div>

            <!-- Search & Date Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6 relative">
                    <input type="text" 
                           x-model="q" 
                           @input.debounce.300ms="fetchOrders()" 
                           placeholder="Cari Invoice, Resi, Nama Buyer/Seller..." 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-neon transition">
                    <div x-show="loading" class="absolute right-3 top-2.5 text-neon">
                        <i class="fas fa-circle-notch fa-spin text-xs"></i>
                    </div>
                </div>
                <div class="md:col-span-3">
                    <input type="date" 
                           x-model="dateFrom" 
                           @change="fetchOrders()" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-slate-300 focus:outline-none focus:border-neon transition">
                </div>
                <div class="md:col-span-3">
                    <input type="date" 
                           x-model="dateTo" 
                           @change="fetchOrders()" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-slate-300 focus:outline-none focus:border-neon transition">
                </div>
            </div>

        </div>

        <!-- Orders Table Container -->
        <div class="bg-slate-900/40 backdrop-blur-md border border-slate-850 rounded-2xl overflow-hidden shadow-2xl relative">
            
            <!-- Loading Overlay -->
            <div x-show="loading" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm z-20 flex items-center justify-center">
                <div class="flex items-center gap-3 bg-slate-900 border border-slate-800 px-5 py-3 rounded-2xl shadow-2xl">
                    <i class="fas fa-spinner fa-spin text-neon text-lg"></i>
                    <span class="text-xs font-bold text-white uppercase tracking-wider font-mono">Memuat Data Transaksi...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-slate-950/80 border-b border-slate-800">
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Invoice / Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Pembeli (Buyer)</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Penjual (Seller)</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Total & Fee</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Status / Resi</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="order-rows-container" class="divide-y divide-slate-800/40">
                        @include('admin.marketplace.orders.partials.order-rows', ['orders' => $orders])
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div id="pagination-container">
                @include('admin.marketplace.orders.partials.pagination', ['orders' => $orders])
            </div>
        </div>

    </div>

    <!-- Dispute Resolution Modal (AJAX) -->
    <div x-show="showDisputeModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl relative" @click.outside="showDisputeModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-sm">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h3 class="text-base font-black text-white uppercase tracking-wider">Intervensi Dispute Admin</h3>
                </div>
                <button @click="showDisputeModal = false" class="text-slate-500 hover:text-white transition">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <template x-if="activeOrder">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Invoice:</span>
                            <span class="font-mono font-bold text-white" x-text="'#' + activeOrder.invoice_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Total Transaksi:</span>
                            <span class="font-mono font-bold text-neon" x-text="'Rp ' + Number(activeOrder.grand_total).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status Saat Ini:</span>
                            <span class="font-mono uppercase font-bold text-amber-400" x-text="activeOrder.status"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">Catatan Keputusan Admin / Alasan Dispute:</label>
                        <textarea x-model="adminNotes" 
                                  rows="3" 
                                  placeholder="Masukkan penjelasan komplain atau alasan keputusan..."
                                  class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon transition"></textarea>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button @click="resolveDispute('release')" 
                                :disabled="submittingAction"
                                class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-dark font-black uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                            <i class="fas fa-check-double"></i>
                            <span>Opsi A: Cairkan Saldo ke Seller (Release)</span>
                        </button>
                        <button @click="resolveDispute('refund')" 
                                :disabled="submittingAction"
                                class="w-full py-3 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-black uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20">
                            <i class="fas fa-undo"></i>
                            <span>Opsi B: Refund Saldo 100% ke Buyer (Batalkan)</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
