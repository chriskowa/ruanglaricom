@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Master Kupon')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-white md:ml-2">Master Kupon</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    MASTER <span class="text-yellow-400">KUPON</span>
                </h1>
            </div>
            <div>
                <a href="{{ route('eo.coupons.create') }}" class="px-6 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black transition-all shadow-lg shadow-yellow-500/20 flex items-center gap-2 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Buat Kupon Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-slate-700 rounded-xl p-4 relative z-10">
        <form action="{{ route('eo.coupons.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Cari Kode</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: DISKON50" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-yellow-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Filter Event</label>
                <select name="event_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                    <option value="">Semua Event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-300">
                    <tr>
                        <th class="px-6 py-4">Kode Kupon</th>
                        <th class="px-6 py-4">Tipe & Nilai</th>
                        <th class="px-6 py-4">Event</th>
                        <th class="px-6 py-4">Penggunaan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-black text-white font-mono text-base">
                            {{ $coupon->code }}
                            <div class="text-xs font-normal text-slate-500 font-sans mt-1">
                                Exp: {{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'Selamanya' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($coupon->type == 'percent')
                                <span class="text-yellow-400 font-bold">{{ $coupon->value }}%</span> OFF
                            @else
                                <span class="text-green-400 font-bold">Rp {{ number_format($coupon->value, 0, ',', '.') }}</span> OFF
                            @endif
                            @if($coupon->min_transaction_amount > 0)
                                <div class="text-xs text-slate-500 mt-1">Min. Tx: Rp {{ number_format($coupon->min_transaction_amount, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $coupon->event->name ?? 'Global / Deleted Event' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden max-w-[100px]">
                                    <div class="h-full bg-blue-500" style="width: {{ $coupon->max_uses > 0 ? min(100, ($coupon->used_count / $coupon->max_uses) * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-xs font-mono">{{ $coupon->used_count }} / {{ $coupon->max_uses ?: '∞' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if(!$coupon->is_active)
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-400 border border-red-500/30">Non-Aktif</span>
                            @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                                <span class="px-2 py-1 rounded text-xs bg-orange-500/20 text-orange-400 border border-orange-500/30">Kedaluwarsa</span>
                            @elseif($coupon->max_uses && $coupon->used_count >= $coupon->max_uses)
                                <span class="px-2 py-1 rounded text-xs bg-slate-500/20 text-slate-400 border border-slate-500/30">Habis</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400 border border-green-500/30">Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('eo.coupons.edit', $coupon) }}" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('eo.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-slate-800 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-1">Belum Ada Kupon</h3>
                                <p class="text-slate-500 text-sm mb-4">Buat kupon pertama Anda untuk menarik peserta!</p>
                                <a href="{{ route('eo.coupons.create') }}" class="text-yellow-400 hover:text-yellow-300 font-bold text-sm">Buat Kupon Sekarang &rarr;</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($coupons->hasPages())
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/30">
            {{ $coupons->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
