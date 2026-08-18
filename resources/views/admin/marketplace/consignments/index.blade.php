@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Moderasi Titip Jual (Consignments) - Admin')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 font-sans bg-dark text-slate-200">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-xs text-neon uppercase tracking-wider font-bold mb-1">Marketplace Moderation</p>
                <h1 class="text-2xl font-black text-white italic tracking-tight uppercase">
                    Titip Jual <span class="text-neon">(Consignments)</span>
                </h1>
                <p class="text-xs text-slate-400 mt-1 font-medium">Daftar produk titip jual yang memerlukan pemeriksaan fisik sebelum listing.</p>
            </div>
            <div>
                <a href="{{ route('admin.marketplace.orders.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 transition">
                    Lihat Transaksi Pesanan
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-950/80 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-2xl text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Table Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-xs font-sans">
                    <thead class="bg-slate-950/80 uppercase text-slate-400">
                        <tr>
                            <th class="px-5 py-4 text-left font-bold">Produk</th>
                            <th class="px-5 py-4 text-left font-bold">Seller (Akun)</th>
                            <th class="px-5 py-4 text-left font-bold">Pemilik Asli / Kontak</th>
                            <th class="px-5 py-4 text-left font-bold">Metode Serah Terima</th>
                            <th class="px-5 py-4 text-left font-bold">Status</th>
                            <th class="px-5 py-4 text-right font-bold">Aksi Moderasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 bg-slate-900/30">
                        @forelse($intakes as $intake)
                            <tr class="hover:bg-slate-850/40 transition">
                                <!-- Product -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-slate-950 rounded-xl overflow-hidden border border-slate-800 shrink-0 flex items-center justify-center">
                                            @if(optional($intake->product->primaryImage)->image_path)
                                                <img src="{{ asset('storage/' . $intake->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-[10px] text-slate-700 font-bold">NO IMG</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('marketplace.show', $intake->product->slug) }}" target="_blank" class="font-bold text-white hover:text-neon transition truncate block max-w-xs">
                                                {{ $intake->product->title }}
                                            </a>
                                            <span class="text-xs text-neon font-black">
                                                Rp {{ number_format($intake->product->price, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Seller -->
                                <td class="px-5 py-4">
                                    <div class="font-bold text-white">{{ $intake->seller->name ?? 'Seller' }}</div>
                                    <div class="text-xs text-slate-400 font-medium">{{ $intake->seller->email ?? '-' }}</div>
                                    @if($intake->seller && $intake->seller->phone)
                                        <div class="text-xs text-slate-400 font-medium">{{ $intake->seller->phone }}</div>
                                    @endif
                                </td>

                                <!-- Owner Asli / WA -->
                                <td class="px-5 py-4">
                                    @if($intake->owner_name || $intake->owner_phone)
                                        <div class="font-bold text-white">{{ $intake->owner_name ?: '-' }}</div>
                                        @if($intake->owner_phone)
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-slate-400 font-medium">{{ $intake->owner_phone }}</span>
                                                @php
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $intake->owner_phone);
                                                    if (str_starts_with($cleanPhone, '0')) {
                                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                                    }
                                                @endphp
                                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Halo ' . ($intake->owner_name ?: 'Kak') . ', kami dari Admin RuangLari mengenai barang titip jual: ' . $intake->product->title) }}" 
                                                   target="_blank" 
                                                   class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-bold transition">
                                                    Chat WA
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-500 italic font-medium">Sama dengan Seller</span>
                                    @endif
                                </td>

                                <!-- Dropoff Info -->
                                <td class="px-5 py-4 text-xs text-slate-300">
                                    <span class="block font-semibold">{{ $intake->dropoff_method ?: 'Ekspedisi / COD' }}</span>
                                    <span class="text-slate-500 font-medium">{{ $intake->dropoff_location ?: '-' }}</span>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                        {{ $intake->status === 'listed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' :
                                          ($intake->status === 'received' ? 'bg-blue-500/10 text-blue-400 border-blue-500/30' :
                                          'bg-yellow-500/10 text-yellow-400 border-yellow-500/30') }}">
                                        {{ strtoupper($intake->status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                    @if($intake->status === 'requested')
                                        <form action="{{ route('admin.marketplace.consignments.received', $intake->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
                                                Tandai Diterima (Received)
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($intake->status, ['requested', 'received'], true))
                                        <form action="{{ route('admin.marketplace.consignments.listed', $intake->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Terbitkan produk titip jual ini ke katalog publik RuangLari?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-neon hover:bg-white text-dark font-black text-xs transition">
                                                Publish Listing (Listed)
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500 font-medium">
                                    Belum ada pengajuan produk titip jual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($intakes->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $intakes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
