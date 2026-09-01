@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Pesanan #' . $order->invoice_number . ' - Marketplace')

@section('content')
<div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto min-h-screen font-sans text-slate-200">
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-xs text-slate-400" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('marketplace.index') }}" class="hover:text-white transition">Marketplace</a>
            </li>
            <li>/</li>
            <li>
                <a href="{{ route('marketplace.orders.index') }}" class="hover:text-white transition">Daftar Pesanan</a>
            </li>
            <li>/</li>
            <li class="text-white font-bold">#{{ $order->invoice_number }}</li>
        </ol>
    </nav>

    <!-- Session Alerts -->
    @if(session('success'))
        <div class="bg-slate-900 border border-slate-700 text-slate-200 px-4 py-3 rounded-md mb-6 text-xs font-semibold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="text-neon font-bold">✓</span>
                {{ session('success') }}
            </span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-950/80 border border-rose-800 text-rose-200 px-4 py-3 rounded-md mb-6 text-xs font-semibold flex items-center gap-2">
            <span class="text-rose-400 font-bold">✗</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Order Items & Shipping (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Order Header Card -->
            <div class="bg-slate-900/60 rounded-lg border border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-800">
                    <div>
                        <p class="text-xs text-neon uppercase tracking-wider font-bold mb-1">Invoice</p>
                        <h1 class="text-xl md:text-2xl font-black text-white italic tracking-tight uppercase">
                            #{{ $order->invoice_number }}
                        </h1>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Dibuat: {{ $order->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div>
                        <span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider border
                            {{ in_array($order->status, ['completed']) ? 'bg-slate-800 text-slate-200 border-slate-700' : 
                              (in_array($order->status, ['shipped', 'delivered']) ? 'bg-blue-950/80 text-blue-300 border-blue-600/40' : 
                              (in_array($order->status, ['paid', 'processing', 'packing']) ? 'bg-amber-950/80 text-amber-300 border-amber-600/40' :
                              (in_array($order->status, ['disputed', 'return_in_progress']) ? 'bg-rose-950/80 text-rose-300 border-rose-600/40' :
                              'bg-slate-800 text-slate-300 border-slate-700'))) }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Transaction Stepper -->
                <div class="pt-2">
                    <p class="text-xs text-slate-400 uppercase font-bold mb-3 tracking-wider">Tahapan Pesanan</p>
                    <div class="grid grid-cols-5 gap-1.5 text-center text-xs">
                        @php
                            $stepOrder = ['paid' => 1, 'processing' => 2, 'packing' => 3, 'shipped' => 4, 'delivered' => 5, 'completed' => 6];
                            $currentStep = $stepOrder[$order->status] ?? 0;
                            if ($order->status === 'disputed' || $order->status === 'return_in_progress' || $order->status === 'refunded') {
                                $currentStep = 99;
                            }
                        @endphp

                        <div class="p-2 rounded-md border {{ $currentStep >= 1 ? 'border-neon/60 bg-neon/10 text-neon font-bold' : 'border-slate-800 text-slate-600' }}">
                            1. Dibayar
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 2 ? 'border-neon/60 bg-neon/10 text-neon font-bold' : 'border-slate-800 text-slate-600' }}">
                            2. Diproses
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 3 ? 'border-neon/60 bg-neon/10 text-neon font-bold' : 'border-slate-800 text-slate-600' }}">
                            3. Dikemas
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 4 ? 'border-neon/60 bg-neon/10 text-neon font-bold' : 'border-slate-800 text-slate-600' }}">
                            4. Dikirim
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 6 ? 'border-slate-700 bg-slate-800 text-white font-bold' : ($currentStep == 5 ? 'border-blue-500/60 bg-blue-500/10 text-blue-400 font-bold' : 'border-slate-800 text-slate-600') }}">
                            5. Selesai
                        </div>
                    </div>
                </div>

                <!-- Dispute / Retur Alert Banner -->
                @if(in_array($order->status, ['disputed', 'return_in_progress', 'refunded']))
                    <div class="mt-4 p-4 rounded-md border {{ $order->status === 'refunded' ? 'border-slate-700 bg-slate-900 text-slate-200' : 'border-rose-800 bg-rose-950/40 text-rose-300' }} space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold uppercase tracking-wider">
                                @if($order->status === 'disputed') Status: Komplain Diajukan Pembeli
                                @elseif($order->status === 'return_in_progress') Status: Pengembalian Barang (Retur)
                                @elseif($order->status === 'refunded') Status: Transaksi Dibatalkan & Refund Selesai
                                @endif
                            </span>
                            @if($order->disputed_at)
                                <span class="text-xs text-slate-400">{{ $order->disputed_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <p class="text-slate-300">
                            <strong>Alasan:</strong> {{ ucfirst(str_replace('_', ' ', $order->dispute_reason ?: 'Barang tidak sesuai')) }} - "{{ $order->dispute_notes }}"
                        </p>

                        @if(!empty($order->dispute_proof_images) && is_array($order->dispute_proof_images))
                            <div class="pt-2">
                                <p class="text-xs text-slate-400 uppercase font-semibold mb-1.5">Foto Bukti Fisik:</p>
                                <div class="flex gap-2">
                                    @foreach($order->dispute_proof_images as $proof)
                                        <a href="{{ asset('storage/' . $proof) }}" target="_blank" class="w-16 h-16 rounded-md overflow-hidden border border-slate-700 bg-slate-900 block shrink-0 hover:border-neon transition">
                                            <img src="{{ asset('storage/' . $proof) }}" class="w-full h-full object-cover">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($order->return_tracking_number)
                            <div class="pt-2 border-t border-slate-800/80">
                                <p class="text-xs text-white">
                                    <strong>Resi Retur:</strong> <span class="font-bold text-neon">{{ $order->return_tracking_number }}</span> ({{ strtoupper($order->return_courier ?: 'Ekspedisi') }})
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Items List Card -->
            <div class="bg-slate-900/60 rounded-lg border border-slate-800 p-6 space-y-4 shadow-sm">
                <h2 class="text-xs font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Daftar Produk</h2>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 p-3.5 rounded-md bg-slate-950 border border-slate-800">
                            <div class="w-16 h-16 rounded-md overflow-hidden bg-slate-900 border border-slate-800 shrink-0 flex items-center justify-center">
                                @if($item->product && $item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover" alt="{{ $item->product_title_snapshot }}">
                                @else
                                    <span class="text-xs text-slate-600 font-mono font-semibold">NO IMG</span>
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="text-sm font-bold text-white truncate">{{ $item->product_title_snapshot }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">Jumlah: {{ $item->quantity }} x Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right font-black text-neon font-mono text-sm">
                                Rp {{ number_format($item->price_snapshot * $item->quantity, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipping Details Card -->
            <div class="bg-slate-900/60 rounded-lg border border-slate-800 p-6 space-y-3 shadow-sm text-xs">
                <h2 class="text-xs font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Informasi Pengiriman</h2>
                <div class="space-y-2 text-slate-300">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Penerima:</span>
                        <span class="text-white font-bold">{{ $order->shipping_name ?? '-' }} ({{ $order->shipping_phone ?? '-' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Alamat Tujuan:</span>
                        <p class="text-white bg-slate-950 p-2.5 rounded-md border border-slate-800">
                            {{ $order->shipping_address ?? '-' }}<br>
                            {{ $order->shipping_city ?? '' }}{{ $order->shipping_postal_code ? ' ' . $order->shipping_postal_code : '' }}
                        </p>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-400">Layanan Kurir:</span>
                        <span class="text-white font-bold uppercase">
                            {{ $order->shipping_courier ?: '-' }} {{ $order->shipping_service_name ? '(' . $order->shipping_service_name . ')' : '' }}
                        </span>
                    </div>
                    @if($order->shipping_tracking_number)
                        <div class="flex justify-between pt-1 items-center">
                            <span class="text-slate-400">Nomor Resi:</span>
                            <span class="text-neon font-mono font-bold text-sm bg-slate-950 px-2.5 py-1 rounded-md border border-slate-800">
                                {{ $order->shipping_tracking_number }}
                            </span>
                        </div>
                    @endif
                    @if($order->shipping_note)
                        <div class="pt-1">
                            <span class="text-slate-400 block mb-0.5">Catatan:</span>
                            <p class="text-slate-300 italic">"{{ $order->shipping_note }}"</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Status Actions & Parties (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Actions Panel (Sticky) -->
            <div class="bg-slate-900/60 rounded-lg border border-slate-800 p-6 shadow-sm space-y-5 sticky top-24">
                <h3 class="text-xs font-black text-white uppercase tracking-wider pb-3 border-b border-slate-800">
                    Aksi & Pengelolaan Pesanan
                </h3>

                @php
                    $isBuyer = (auth()->id() == $order->buyer_id);
                    $isSeller = (auth()->id() == $order->seller_id);
                    $isAdmin = auth()->user()?->isAdmin();
                @endphp

                <!-- 1. PENDING (Buyer Bayar) -->
                @if($order->status === 'pending')
                    <div class="space-y-3">
                        <p class="text-xs text-amber-300">Menunggu pembayaran pesanan.</p>
                        @if($isBuyer)
                            <button id="pay-now-btn" onclick="payPurchase({{ $order->id }})" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider rounded-md transition shadow-sm">
                                Bayar Sekarang
                            </button>
                        @endif
                    </div>

                <!-- 2. PAID (Seller Proses) -->
                @elseif($order->status === 'paid')
                    <div class="space-y-3">
                        <p class="text-xs text-slate-300">Pembayaran telah dikonfirmasi dan ditampung aman di rekening bersama RuangLari.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.seller.orders.process', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider rounded-md transition shadow-sm">
                                    Proses Pesanan
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-slate-500 italic">Menunggu penjual memproses pesanan.</p>
                        @endif
                    </div>

                <!-- 3. PROCESSING (Seller Kemas atau Kirim) -->
                @elseif($order->status === 'processing')
                    <div class="space-y-4">
                        <p class="text-xs text-slate-300">Penjual sedang menyiapkan barang pesanan.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.seller.orders.pack', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider rounded-md transition">
                                    Tandai Sedang Dikemas
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-slate-500 italic">Barang sedang disiapkan oleh penjual.</p>
                        @endif
                    </div>

                <!-- 4. PACKING (Seller Input Resi Pengiriman) -->
                @elseif($order->status === 'packing')
                    <div class="space-y-4">
                        <p class="text-xs text-slate-300">Barang selesai dikemas dan siap dikirim.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.seller.orders.ship', $order->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs text-slate-400 uppercase font-bold mb-1">Kurir Pengiriman</label>
                                    <input type="text" name="shipping_courier" value="{{ $order->shipping_courier }}" required placeholder="Contoh: JNE / SiCepat" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 uppercase font-bold mb-1">Nomor Resi Pengiriman</label>
                                    <input type="text" name="shipping_tracking_number" required placeholder="Nomor Resi / AWB" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon">
                                </div>
                                <button type="submit" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider rounded-md transition shadow-sm">
                                    Kirim Pesanan & Input Resi
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-slate-500 italic">Menunggu penjual menyerahkan paket ke kurir.</p>
                        @endif
                    </div>

                <!-- 5. SHIPPED (Dalam Pengiriman Kurir) -->
                @elseif($order->status === 'shipped')
                    <div class="space-y-4">
                        <p class="text-xs text-slate-300">Barang dalam proses pengantaran oleh pihak ekspedisi.</p>
                        
                        @if($isBuyer || $isAdmin)
                            <form action="{{ route('marketplace.orders.delivered', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider rounded-md transition">
                                    Konfirmasi Barang Sudah Tiba
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-slate-500 italic">Pesanan sedang dalam pengiriman ke alamat pembeli.</p>
                        @endif
                    </div>

                <!-- 6. DELIVERED (Barang Sampai, Buyer Verifikasi / Komplain) -->
                @elseif($order->status === 'delivered')
                    <div class="space-y-4">
                        <p class="text-xs text-slate-300">Barang telah sampai. Silakan periksa kondisi fisik dan kelayakan barang sebelum menyelesaikan transaksi.</p>
                        
                        @if($isBuyer || $isAdmin)
                            <!-- Approve & Release Funds -->
                            <form action="{{ route('marketplace.orders.completed', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sesuai dan ingin menyelesaikan pesanan? Dana akan dicairkan ke penjual.')">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider rounded-md transition shadow-sm">
                                    Setujui & Selesaikan Pesanan
                                </button>
                            </form>

                            <!-- Dispute Button -->
                            <button type="button" onclick="document.getElementById('dispute-modal').classList.remove('hidden')" class="w-full py-2.5 bg-slate-800 hover:bg-rose-950 hover:text-rose-400 text-slate-300 font-bold text-xs uppercase tracking-wider rounded-md border border-slate-700 transition">
                                Barang Tidak Sesuai (Ajukan Komplain)
                            </button>
                        @else
                            <p class="text-xs text-slate-500 italic">Menunggu pembeli memeriksa barang dan menyelesaikan transaksi.</p>
                        @endif
                    </div>

                <!-- 7. DISPUTED (Seller Setujui Retur / Admin Mediasi) -->
                @elseif($order->status === 'disputed')
                    <div class="space-y-4">
                        <p class="text-xs text-rose-300">Komplain sedang berlangsung. Dana rekening bersama ditahan sementara.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.orders.accept-return', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider rounded-md transition">
                                    Setujui Pengembalian Barang (Retur)
                                </button>
                            </form>
                        @endif
                    </div>

                <!-- 8. RETURN IN PROGRESS (Buyer Input Resi Retur / Seller Konfirmasi Terima Retur) -->
                @elseif($order->status === 'return_in_progress')
                    <div class="space-y-4">
                        @if($isBuyer && !$order->return_tracking_number)
                            <form action="{{ route('marketplace.orders.return-tracking', $order->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <p class="text-xs text-slate-300">Pengajuan retur disetujui. Silakan kirim barang kembali dan input nomor resi di bawah:</p>
                                <div>
                                    <label class="block text-xs text-slate-400 uppercase font-bold mb-1">Kurir Retur</label>
                                    <input type="text" name="return_courier" required placeholder="Contoh: JNE / J&T" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 uppercase font-bold mb-1">Nomor Resi Retur</label>
                                    <input type="text" name="return_tracking_number" required placeholder="Nomor Resi Pengiriman Retur" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon">
                                </div>
                                <button type="submit" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider rounded-md transition shadow-sm">
                                    Simpan Resi Retur
                                </button>
                            </form>
                        @elseif(($isSeller || $isAdmin) && $order->return_tracking_number)
                            <p class="text-xs text-slate-300">Barang retur sedang dikirim kembali oleh pembeli.</p>
                            <form action="{{ route('marketplace.orders.confirm-return', $order->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa barang retur telah Anda terima dalam kondisi aman? Dana 100% akan direfund ke pembeli.')">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider rounded-md transition shadow-sm">
                                    Konfirmasi Barang Retur Diterima (Refund)
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-slate-400 italic">Menunggu proses pengiriman barang retur.</p>
                        @endif
                    </div>

                <!-- 9. COMPLETED (Selesai) -->
                @elseif($order->status === 'completed')
                    <div class="text-center py-2 space-y-2">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-neon flex items-center justify-center mx-auto font-black">✓</div>
                        <p class="text-xs font-bold text-white uppercase tracking-wider">Transaksi Selesai</p>
                        <p class="text-xs text-slate-400">Dana telah dicairkan ke saldo dompet penjual.</p>
                    </div>

                <!-- 10. REFUNDED (Dana Kembali) -->
                @elseif($order->status === 'refunded')
                    <div class="text-center py-2 space-y-2">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-blue-400 flex items-center justify-center mx-auto font-black">✓</div>
                        <p class="text-xs font-bold text-white uppercase tracking-wider">Dana Dikembalikan (Refund)</p>
                        <p class="text-xs text-slate-400">Dana 100% telah dikembalikan ke saldo dompet pembeli.</p>
                    </div>
                @endif

                <!-- Financial Breakdown -->
                <div class="pt-4 border-t border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal Produk:</span>
                        <span class="text-white font-mono font-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Ongkos Kirim:</span>
                        <span class="text-white font-mono font-semibold">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-slate-800 pt-2 flex justify-between font-bold">
                        <span class="text-white">Total Pembayaran:</span>
                        <span class="text-neon font-mono font-black text-sm">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Parties Info -->
                <div class="pt-4 border-t border-slate-800 space-y-3 text-xs">
                    <div>
                        <span class="text-xs text-slate-400 uppercase font-bold block">Pembeli (Buyer):</span>
                        <span class="text-white font-bold">{{ $order->buyer->name ?? 'Buyer' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 uppercase font-bold block">Penjual (Seller):</span>
                        <span class="text-white font-bold">{{ $order->seller->name ?? 'Seller' }}</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<!-- Dispute Modal (Komplain Barang Tidak Sesuai) -->
<div id="dispute-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 max-w-lg w-full shadow-2xl space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-black text-white uppercase tracking-wider">Ajukan Komplain Barang</h3>
            <button type="button" onclick="document.getElementById('dispute-modal').classList.add('hidden')" class="text-slate-400 hover:text-white text-base">&times;</button>
        </div>

        <form action="{{ route('marketplace.orders.dispute', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 uppercase font-bold mb-1.5">Jenis Masalah</label>
                <select name="dispute_reason" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-neon">
                    <option value="not_as_described">Barang Tidak Sesuai Foto / Deskripsi Listing</option>
                    <option value="damaged">Barang Rusak / Cacat Fisik</option>
                    <option value="wrong_item">Salah Kirim Barang / Salah Ukuran</option>
                    <option value="fake">Barang Palsu / Tidak Original</option>
                    <option value="other">Masalah Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 uppercase font-bold mb-1.5">Rincian Penjelasan Masalah</label>
                <textarea name="dispute_notes" rows="3" required placeholder="Jelaskan ketidaksesuaian barang secara jelas..." class="w-full bg-slate-950 border border-slate-800 rounded-md p-3 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 uppercase font-bold mb-1.5">Upload Foto Bukti Fisik (Maksimal 3 Foto)</label>
                <input type="file" name="dispute_proofs[]" multiple accept="image/*" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2 text-xs text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-white hover:file:bg-slate-700">
            </div>

            <div class="flex gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('dispute-modal').classList.add('hidden')" class="flex-1 py-2.5 rounded-md border border-slate-700 text-slate-300 font-bold hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-md bg-rose-600 hover:bg-rose-500 text-white font-bold transition">
                    Kirim Komplain
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@if($order->status == 'pending')
@push('scripts')
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    function payPurchase(orderId) {
        const btn = document.getElementById('pay-now-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Memproses...';
        }
        
        fetch('{{ route("marketplace.checkout.pay", $order->id) }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.snap_token) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result){
                        window.location.href = "{{ route('marketplace.orders.show', $order->id) }}?payment=success";
                    },
                    onPending: function(result){
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Pembayaran gagal.");
                        window.location.reload();
                    },
                    onClose: function(){
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = 'Bayar Sekarang';
                        }
                    }
                });
            } else {
                alert('Gagal mengambil sesi pembayaran.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Bayar Sekarang';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Bayar Sekarang';
            }
        });
    }
</script>
@endpush
@endif
