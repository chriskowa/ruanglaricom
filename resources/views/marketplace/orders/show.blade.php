@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Pesanan #' . $order->invoice_number . ' - RuangLari Marketplace')

@section('content')
<div class="pt-0 pt-4 md:pt-20 pb-20 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto min-h-screen font-sans bg-[#090A0E] text-slate-200">
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-xs text-zinc-300" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('marketplace.index') }}" class="hover:text-white transition-colors">Marketplace</a>
            </li>
            <li class="text-zinc-500">/</li>
            <li>
                <a href="{{ route('marketplace.orders.index') }}" class="hover:text-white transition-colors">Daftar Pesanan</a>
            </li>
            <li class="text-zinc-500">/</li>
            <li class="text-white font-semibold">#{{ $order->invoice_number }}</li>
        </ol>
    </nav>

    <!-- Session Alerts -->
    @if(session('success'))
        <div class="bg-[#131722] border border-zinc-800 text-zinc-200 px-4 py-3 rounded-md mb-6 text-xs font-semibold flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2">
                <span class="text-lime-400 font-bold">✓</span>
                {{ session('success') }}
            </span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-[#1c1317] border border-rose-900/60 text-rose-200 px-4 py-3 rounded-md mb-6 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span class="text-rose-400 font-bold">✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
        
        <!-- Left Column: Order Items & Shipping (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Order Header Card -->
            <div class="bg-[#131722] rounded-lg border border-zinc-800 p-6 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-zinc-800">
                    <div>
                        <p class="text-xs text-lime-400 font-semibold mb-1">Nomor Transaksi</p>
                        <h1 class="text-xl md:text-2xl font-bold text-white tracking-tight font-mono">
                            #{{ $order->invoice_number }}
                        </h1>
                        <p class="text-xs text-zinc-300 mt-1">
                            Dibuat: {{ $order->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded text-xs font-semibold border
                            {{ in_array($order->status, ['completed']) ? 'bg-[#0B0E14] text-zinc-200 border-zinc-700' : 
                              (in_array($order->status, ['shipped', 'delivered']) ? 'bg-blue-950/50 text-blue-300 border-blue-800/50' : 
                              (in_array($order->status, ['paid', 'processing', 'packing']) ? 'bg-[#0B0E14] text-lime-400 border-zinc-800' : 
                              (in_array($order->status, ['disputed', 'return_in_progress']) ? 'bg-rose-950/50 text-rose-300 border-rose-800/50' : 
                              'bg-amber-950/50 text-amber-300 border-amber-800/50'))) }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Transaction Stepper -->
                <div class="pt-1">
                    <p class="text-xs text-zinc-300 font-semibold mb-3">Tahapan Status Pesanan</p>
                    <div class="grid grid-cols-5 gap-2 text-center text-xs">
                        @php
                            $stepOrder = ['paid' => 1, 'processing' => 2, 'packing' => 3, 'shipped' => 4, 'delivered' => 5, 'completed' => 6];
                            $currentStep = $stepOrder[$order->status] ?? 0;
                            if ($order->status === 'disputed' || $order->status === 'return_in_progress' || $order->status === 'refunded') {
                                $currentStep = 99;
                            }
                        @endphp

                        <div class="p-2 rounded-md border {{ $currentStep >= 1 ? 'border-lime-500/50 bg-[#0B0E14] text-lime-400 font-bold' : 'border-zinc-800 bg-[#0B0E14] text-zinc-500' }}">
                            1. Dibayar
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 2 ? 'border-lime-500/50 bg-[#0B0E14] text-lime-400 font-bold' : 'border-zinc-800 bg-[#0B0E14] text-zinc-500' }}">
                            2. Diproses
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 3 ? 'border-lime-500/50 bg-[#0B0E14] text-lime-400 font-bold' : 'border-zinc-800 bg-[#0B0E14] text-zinc-500' }}">
                            3. Dikemas
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 4 ? 'border-lime-500/50 bg-[#0B0E14] text-lime-400 font-bold' : 'border-zinc-800 bg-[#0B0E14] text-zinc-500' }}">
                            4. Dikirim
                        </div>
                        <div class="p-2 rounded-md border {{ $currentStep >= 6 ? 'border-zinc-700 bg-zinc-800 text-white font-bold' : ($currentStep == 5 ? 'border-blue-500/60 bg-blue-950/40 text-blue-300 font-bold' : 'border-zinc-800 bg-[#0B0E14] text-zinc-500') }}">
                            5. Selesai
                        </div>
                    </div>
                </div>

                <!-- Dispute / Retur Alert Banner -->
                @if(in_array($order->status, ['disputed', 'return_in_progress', 'refunded']))
                    <div class="mt-4 p-4 rounded-md border {{ $order->status === 'refunded' ? 'border-zinc-700 bg-[#0B0E14] text-zinc-200' : 'border-rose-900/60 bg-[#1c1317] text-rose-200' }} space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">
                                @if($order->status === 'disputed') Status: Komplain Diajukan Pembeli
                                @elseif($order->status === 'return_in_progress') Status: Pengembalian Barang (Retur)
                                @elseif($order->status === 'refunded') Status: Transaksi Dibatalkan & Refund Selesai
                                @endif
                            </span>
                            @if($order->disputed_at)
                                <span class="text-xs text-zinc-400">{{ $order->disputed_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <p class="text-zinc-300">
                            <strong>Alasan:</strong> {{ ucfirst(str_replace('_', ' ', $order->dispute_reason ?: 'Barang tidak sesuai')) }} - "{{ $order->dispute_notes }}"
                        </p>

                        @if(!empty($order->dispute_proof_images) && is_array($order->dispute_proof_images))
                            <div class="pt-2">
                                <p class="text-xs text-zinc-300 font-semibold mb-1.5">Foto Bukti Fisik:</p>
                                <div class="flex gap-2">
                                    @foreach($order->dispute_proof_images as $proof)
                                        <a href="{{ asset('storage/' . $proof) }}" target="_blank" class="w-16 h-16 rounded-md overflow-hidden border border-zinc-800 bg-[#0B0E14] block shrink-0 hover:border-lime-500 transition-colors">
                                            <img src="{{ asset('storage/' . $proof) }}" class="w-full h-full object-cover">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($order->return_tracking_number)
                            <div class="pt-2 border-t border-zinc-800">
                                <p class="text-xs text-white">
                                    <strong>Resi Retur:</strong> <span class="font-bold text-lime-400 font-mono">{{ $order->return_tracking_number }}</span> ({{ strtoupper($order->return_courier ?: 'Ekspedisi') }})
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Items List Card -->
            <div class="bg-[#131722] rounded-lg border border-zinc-800 p-6 space-y-4 shadow-sm">
                <h2 class="text-sm font-bold text-white border-b border-zinc-800 pb-3">Daftar Produk Dipesan</h2>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 p-4 rounded-md bg-[#0B0E14] border border-zinc-800">
                            <div class="w-16 h-16 rounded-md overflow-hidden bg-[#131722] border border-zinc-800 shrink-0 flex items-center justify-center p-1">
                                @if($item->product && $item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="max-h-full max-w-full object-contain" alt="{{ $item->product_title_snapshot }}">
                                @else
                                    <span class="text-[10px] text-zinc-500 font-mono">NO IMG</span>
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="text-sm font-bold text-white truncate">{{ $item->product_title_snapshot }}</p>
                                <p class="text-xs text-zinc-300 mt-0.5">Jumlah: {{ $item->quantity }} x Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right font-bold text-white font-mono text-sm">
                                Rp {{ number_format($item->price_snapshot * $item->quantity, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipping Details Card -->
            <div class="bg-[#131722] rounded-lg border border-zinc-800 p-6 space-y-3 shadow-sm text-xs">
                <h2 class="text-sm font-bold text-white border-b border-zinc-800 pb-3">Informasi Pengiriman</h2>
                <div class="space-y-3 text-zinc-300">
                    <div class="flex justify-between">
                        <span class="text-zinc-400">Penerima Paket:</span>
                        <span class="text-white font-bold">{{ $order->shipping_name ?? '-' }} ({{ $order->shipping_phone ?? '-' }})</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block mb-1">Alamat Tujuan:</span>
                        <p class="text-zinc-200 bg-[#0B0E14] p-3 rounded-md border border-zinc-800 leading-relaxed">
                            {{ $order->shipping_address ?? '-' }}<br>
                            {{ $order->shipping_city ?? '' }}{{ $order->shipping_postal_code ? ' ' . $order->shipping_postal_code : '' }}
                        </p>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-zinc-400">Layanan Kurir:</span>
                        <span class="text-white font-bold uppercase">
                            {{ $order->shipping_courier ?: '-' }} {{ $order->shipping_service_name ? '(' . $order->shipping_service_name . ')' : '' }}
                        </span>
                    </div>
                    @if($order->shipping_tracking_number)
                        <div class="flex justify-between pt-1 items-center">
                            <span class="text-zinc-400">Nomor Resi:</span>
                            <span class="text-lime-400 font-mono font-bold text-xs bg-[#0B0E14] px-2.5 py-1 rounded-md border border-zinc-800">
                                {{ $order->shipping_tracking_number }}
                            </span>
                        </div>
                    @endif
                    @if($order->shipping_note)
                        <div class="pt-1">
                            <span class="text-zinc-400 block mb-1">Catatan Pengiriman:</span>
                            <p class="text-zinc-300 italic bg-[#0B0E14] p-2.5 rounded-md border border-zinc-800">"{{ $order->shipping_note }}"</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Status Actions & Parties (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Actions Panel (Sticky) -->
            <div class="bg-[#131722] rounded-lg border border-zinc-800 p-6 shadow-sm space-y-5 sticky top-24">
                <h3 class="text-sm font-bold text-white pb-3 border-b border-zinc-800">
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
                        <p class="text-xs text-amber-300">Menunggu penyelesaian pembayaran oleh pembeli.</p>
                        @if($isBuyer)
                            <button id="pay-now-btn" onclick="payPurchase({{ $order->id }})" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors shadow-sm">
                                Bayar Sekarang
                            </button>
                        @endif
                    </div>

                <!-- 2. PAID (Seller Proses) -->
                @elseif($order->status === 'paid')
                    <div class="space-y-3">
                        <p class="text-xs text-zinc-300">Pembayaran telah dikonfirmasi dan ditampung aman di rekening bersama RuangLari.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.seller.orders.process', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors shadow-sm">
                                    Proses Pesanan
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-zinc-400 italic">Menunggu penjual memproses pesanan.</p>
                        @endif
                    </div>

                <!-- 3. PROCESSING (Seller Kemas atau Kirim) -->
                @elseif($order->status === 'processing')
                    <div class="space-y-4">
                        <p class="text-xs text-zinc-300">Penjual sedang menyiapkan barang pesanan.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.seller.orders.pack', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-zinc-800 hover:bg-zinc-700 text-white font-semibold text-xs rounded-md transition-colors">
                                    Tandai Sedang Dikemas
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-zinc-400 italic">Barang sedang disiapkan oleh penjual.</p>
                        @endif
                    </div>

                <!-- 4. PACKING (Seller Input Resi Pengiriman) -->
                @elseif($order->status === 'packing')
                    <div class="space-y-4">
                        <p class="text-xs text-zinc-300">Barang selesai dikemas dan siap dikirim.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.seller.orders.ship', $order->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs text-zinc-300 font-semibold mb-1">Kurir Pengiriman</label>
                                    <input type="text" name="shipping_courier" value="{{ $order->shipping_courier }}" required placeholder="Contoh: JNE / SiCepat" class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
                                </div>
                                <div>
                                    <label class="block text-xs text-zinc-300 font-semibold mb-1">Nomor Resi Pengiriman</label>
                                    <input type="text" name="shipping_tracking_number" required placeholder="Nomor Resi / AWB" class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
                                </div>
                                <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors shadow-sm">
                                    Kirim Pesanan & Input Resi
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-zinc-400 italic">Menunggu penjual menyerahkan paket ke kurir.</p>
                        @endif
                    </div>

                <!-- 5. SHIPPED (Dalam Pengiriman Kurir) -->
                @elseif($order->status === 'shipped')
                    <div class="space-y-4">
                        <p class="text-xs text-zinc-300">Barang dalam proses pengantaran oleh pihak ekspedisi.</p>
                        
                        @if($isBuyer || $isAdmin)
                            <form action="{{ route('marketplace.orders.delivered', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors">
                                    Konfirmasi Barang Sudah Tiba
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-zinc-400 italic">Pesanan sedang dalam pengiriman ke alamat pembeli.</p>
                        @endif
                    </div>

                <!-- 6. DELIVERED (Barang Sampai, Buyer Verifikasi / Komplain) -->
                @elseif($order->status === 'delivered')
                    <div class="space-y-4">
                        <p class="text-xs text-zinc-300 leading-relaxed">Barang telah sampai. Silakan periksa kondisi fisik barang sebelum menyelesaikan transaksi.</p>
                        
                        @if($isBuyer || $isAdmin)
                            <!-- Approve & Release Funds -->
                            <form action="{{ route('marketplace.orders.completed', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sesuai dan ingin menyelesaikan pesanan? Dana akan dicairkan ke penjual.')">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors shadow-sm">
                                    Setujui & Selesaikan Pesanan
                                </button>
                            </form>

                            <!-- Dispute Button -->
                            <button type="button" onclick="document.getElementById('dispute-modal').classList.remove('hidden')" class="w-full py-2.5 bg-[#0B0E14] hover:bg-[#1c1317] hover:text-rose-300 text-zinc-300 font-semibold text-xs rounded-md border border-zinc-800 transition-colors">
                                Barang Tidak Sesuai (Ajukan Komplain)
                            </button>
                        @else
                            <p class="text-xs text-zinc-400 italic">Menunggu pembeli memeriksa barang dan menyelesaikan transaksi.</p>
                        @endif
                    </div>

                <!-- 7. DISPUTED (Seller Setujui Retur / Admin Mediasi) -->
                @elseif($order->status === 'disputed')
                    <div class="space-y-4">
                        <p class="text-xs text-rose-300">Komplain sedang berlangsung. Dana rekening bersama ditahan sementara oleh sistem.</p>
                        
                        @if($isSeller || $isAdmin)
                            <form action="{{ route('marketplace.orders.accept-return', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-zinc-800 hover:bg-zinc-700 text-white font-semibold text-xs rounded-md transition-colors">
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
                                <p class="text-xs text-zinc-300">Pengajuan retur disetujui. Silakan kirim barang kembali dan input nomor resi di bawah:</p>
                                <div>
                                    <label class="block text-xs text-zinc-300 font-semibold mb-1">Kurir Retur</label>
                                    <input type="text" name="return_courier" required placeholder="Contoh: JNE / J&T" class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
                                </div>
                                <div>
                                    <label class="block text-xs text-zinc-300 font-semibold mb-1">Nomor Resi Retur</label>
                                    <input type="text" name="return_tracking_number" required placeholder="Nomor Resi Pengiriman Retur" class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
                                </div>
                                <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors shadow-sm">
                                    Simpan Resi Retur
                                </button>
                            </form>
                        @elseif(($isSeller || $isAdmin) && $order->return_tracking_number)
                            <p class="text-xs text-zinc-300">Barang retur sedang dikirim kembali oleh pembeli.</p>
                            <form action="{{ route('marketplace.orders.confirm-return', $order->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa barang retur telah Anda terima dalam kondisi aman? Dana 100% akan direfund ke pembeli.')">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-lime-600 hover:bg-lime-500 text-black font-bold text-xs rounded-md transition-colors shadow-sm">
                                    Konfirmasi Barang Retur Diterima (Refund)
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-zinc-400 italic">Menunggu proses pengiriman barang retur.</p>
                        @endif
                    </div>

                <!-- 9. COMPLETED (Selesai) -->
                @elseif($order->status === 'completed')
                    <div class="text-center py-3 space-y-2">
                        <div class="w-8 h-8 rounded-full bg-[#0B0E14] border border-zinc-800 text-lime-400 flex items-center justify-center mx-auto font-bold text-sm">✓</div>
                        <p class="text-xs font-bold text-white">Transaksi Selesai</p>
                        <p class="text-xs text-zinc-300">Dana telah dicairkan ke saldo dompet penjual.</p>
                    </div>

                <!-- 10. REFUNDED (Dana Kembali) -->
                @elseif($order->status === 'refunded')
                    <div class="text-center py-3 space-y-2">
                        <div class="w-8 h-8 rounded-full bg-[#0B0E14] border border-zinc-800 text-blue-400 flex items-center justify-center mx-auto font-bold text-sm">✓</div>
                        <p class="text-xs font-bold text-white">Dana Dikembalikan (Refund)</p>
                        <p class="text-xs text-zinc-300">Dana 100% telah dikembalikan ke saldo dompet pembeli.</p>
                    </div>
                @endif

                <!-- Financial Breakdown -->
                <div class="pt-4 border-t border-zinc-800 space-y-2 text-xs">
                    <div class="flex justify-between text-zinc-300">
                        <span>Subtotal Produk:</span>
                        <span class="text-white font-mono font-medium">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-zinc-300">
                        <span>Ongkos Kirim:</span>
                        <span class="text-white font-mono font-medium">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-zinc-800 pt-3 flex justify-between font-bold">
                        <span class="text-white">Total Pembayaran:</span>
                        <span class="text-lime-400 font-mono text-base">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Parties Info -->
                <div class="pt-4 border-t border-zinc-800 space-y-3 text-xs">
                    <div>
                        <span class="text-zinc-400 block mb-0.5">Pembeli (Buyer):</span>
                        <span class="text-white font-semibold">{{ $order->buyer->name ?? 'Buyer' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block mb-0.5">Penjual (Seller):</span>
                        <span class="text-white font-semibold">{{ $order->seller->name ?? 'Seller' }}</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<!-- Dispute Modal (Komplain Barang Tidak Sesuai) -->
<div id="dispute-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-[#131722] border border-zinc-800 rounded-lg p-6 max-w-lg w-full shadow-2xl space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
            <h3 class="text-sm font-bold text-white">Ajukan Komplain Barang</h3>
            <button type="button" onclick="document.getElementById('dispute-modal').classList.add('hidden')" class="text-zinc-400 hover:text-white text-base">&times;</button>
        </div>

        <form action="{{ route('marketplace.orders.dispute', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-xs text-zinc-300 font-semibold mb-1.5">Jenis Masalah</label>
                <select name="dispute_reason" required class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-zinc-600">
                    <option value="not_as_described">Barang Tidak Sesuai Foto / Deskripsi Listing</option>
                    <option value="damaged">Barang Rusak / Cacat Fisik</option>
                    <option value="wrong_item">Salah Kirim Barang / Salah Ukuran</option>
                    <option value="fake">Barang Palsu / Tidak Original</option>
                    <option value="other">Masalah Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-zinc-300 font-semibold mb-1.5">Rincian Penjelasan Masalah</label>
                <textarea name="dispute_notes" rows="3" required placeholder="Jelaskan ketidaksesuaian barang secara jelas..." class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md p-3 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs text-zinc-300 font-semibold mb-1.5">Upload Foto Bukti Fisik (Maksimal 3 Foto)</label>
                <input type="file" name="dispute_proofs[]" multiple accept="image/*" class="w-full bg-[#0B0E14] border border-zinc-800 rounded-md p-2 text-xs text-zinc-300 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
            </div>

            <div class="flex gap-3 pt-3 border-t border-zinc-800">
                <button type="button" onclick="document.getElementById('dispute-modal').classList.add('hidden')" class="flex-1 py-2.5 rounded-md border border-zinc-700 text-zinc-300 font-semibold hover:bg-zinc-800 transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-md bg-rose-600 hover:bg-rose-500 text-white font-bold transition-colors">
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
