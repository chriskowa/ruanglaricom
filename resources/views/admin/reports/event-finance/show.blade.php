@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Event Finance - ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8 relative z-10" data-aos="fade-up">
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="{{ route('admin.reports.event-finance.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Event Finance</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="ml-1 text-sm font-medium text-white md:ml-2">Detail</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <p class="text-red-500 font-mono text-xs tracking-widest uppercase mb-1">Admin Report</p>
                    <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                        EVENT <span class="text-yellow-400">FINANCE</span>
                    </h1>
                    <div class="text-slate-400 text-sm mt-1">{{ $event->name }} · EO: {{ $event->user ? $event->user->name : '-' }}</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.events.edit', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-black uppercase tracking-widest border border-slate-700 transition-colors">
                        Edit Event
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-5 rounded-2xl bg-green-900/30 border border-green-500/30 text-green-200">
                <div class="text-sm font-bold">{{ session('success') }}</div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="text-xs font-mono text-slate-500 uppercase">Hak EO (Accrued)</div>
                <div class="text-2xl font-black text-white mt-2">Rp {{ number_format((float) $paid['eo_amount'], 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-2">{{ number_format($paid['participants_count']) }} peserta · {{ number_format($paid['tx_count']) }} transaksi</div>
            </div>
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="text-xs font-mono text-slate-500 uppercase">Sudah Dibayar</div>
                <div class="text-2xl font-black text-white mt-2">Rp {{ number_format((float) $settled_amount, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-2">{{ number_format($payouts->where('status','completed')->count()) }} payout</div>
            </div>
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="text-xs font-mono text-slate-500 uppercase">Sisa Harus Dibayar</div>
                <div class="text-2xl font-black text-white mt-2">Rp {{ number_format((float) $remaining_amount, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-2">Platform fee: Rp {{ number_format((float) $paid['admin_fee'], 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <div class="text-white font-black uppercase tracking-widest text-sm">Ringkasan Lunas</div>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Gross (Termasuk Addons)</span>
                        <span class="text-white font-bold">Rp {{ number_format((float) $paid['total_original'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400">Total Addons</span>
                        <span class="text-yellow-400 font-bold">Rp {{ number_format((float) $addons['total_amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-slate-400">Net Tiket (Tanpa Addons)</span>
                        <span class="text-white font-bold">Rp {{ number_format((float) ($paid['total_original'] - $addons['total_amount']), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Diskon Kupon</span>
                        <span class="text-white font-bold">Rp {{ number_format((float) $paid['discount_amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Platform Fee</span>
                        <span class="text-white font-bold">Rp {{ number_format((float) $paid['admin_fee'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-800 pt-2">
                        <span class="text-slate-400">Dibayar Peserta</span>
                        <span class="text-white font-black text-base">Rp {{ number_format((float) $paid['final_amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Unique Code</span>
                        <span class="text-white font-bold">Rp {{ number_format((float) $paid['unique_code'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <div class="text-white font-black uppercase tracking-widest text-sm">Pending</div>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Peserta Pending</span>
                        <span class="text-white font-bold">{{ number_format($pending['participants_count']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Transaksi Pending</span>
                        <span class="text-white font-bold">{{ number_format($pending['tx_count']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Nominal Pending</span>
                        <span class="text-white font-bold">Rp {{ number_format((float) $pending['final_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <div class="text-white font-black uppercase tracking-widest text-sm">Breakdown Addons (Lunas)</div>
                </div>
                <div class="p-6 space-y-3 text-sm max-h-[240px] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2 mb-2">
                        <span class="text-slate-300 font-bold">Nama Addon</span>
                        <span class="text-slate-300 font-bold">Total</span>
                    </div>
                    @forelse($addons['by_name'] as $name => $info)
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-slate-400 break-words leading-tight">{{ $name }} <span class="text-[10px] text-slate-500 font-mono">({{ $info['count'] }}x)</span></span>
                            <span class="text-white font-bold whitespace-nowrap">Rp {{ number_format((float) $info['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="text-slate-500 italic text-center py-4">Belum ada addons terjual</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <div class="text-white font-black uppercase tracking-widest text-sm">Catat Payout</div>
                </div>
                <form method="POST" action="{{ route('admin.reports.event-finance.payouts.store', $event) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nominal Dibayar ke EO</label>
                        <input name="amount" required inputmode="decimal" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-red-500 focus:outline-none" placeholder="Mis: 12500000">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jumlah Peserta (opsional)</label>
                            <input name="participants_count" inputmode="numeric" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-red-500 focus:outline-none" placeholder="Mis: 133">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal Bayar</label>
                            <input name="paid_at" type="datetime-local" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white focus:border-red-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Metode (opsional)</label>
                            <input name="method" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-red-500 focus:outline-none" placeholder="Transfer/Manual">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-3 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white font-black text-sm uppercase tracking-widest hover:scale-[1.02] transition-all shadow-lg shadow-red-500/20">
                                Simpan
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Catatan (opsional)</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-red-500 focus:outline-none"></textarea>
                    </div>
                </form>
            </div>

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="text-white font-black uppercase tracking-widest text-sm">Riwayat Payout</div>
                    <div class="text-xs text-slate-400 font-mono">{{ $payouts->count() }} item</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-800">
                        <thead class="bg-slate-900/40">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Info</th>
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Nominal</th>
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($payouts as $p)
                                <tr class="hover:bg-slate-900/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-200">{{ $p->paid_at ? $p->paid_at->format('d M Y H:i') : ($p->created_at ? $p->created_at->format('d M Y H:i') : '-') }}</div>
                                        <div class="text-xs text-slate-500 mt-1">{{ strtoupper($p->status) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-white font-bold">{{ $p->method ?: '-' }}</div>
                                        <div class="text-xs text-slate-500 mt-1">{{ $p->participants_count ? number_format($p->participants_count) . ' peserta' : '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm text-white font-black">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($p->status === 'completed')
                                            <form method="POST" action="{{ route('admin.reports.event-finance.payouts.destroy', $p) }}" onsubmit="return confirm('Batalkan payout ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-red-900/40 text-red-200 font-black text-xs uppercase tracking-widest border border-red-500/30 transition-colors">
                                                    Cancel
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-500">Belum ada payout.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700">
                <div class="text-white font-black uppercase tracking-widest text-sm">Breakdown Kupon (Lunas)</div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-900/40">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Coupon</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Tx</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Diskon</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Fee</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Hak EO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($coupon_rows as $r)
                            <tr class="hover:bg-slate-900/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white">{{ $r['coupon_code'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">{{ number_format($r['tx_count']) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">{{ number_format($r['participants_count']) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">Rp {{ number_format((float) $r['discount_amount'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">Rp {{ number_format((float) $r['admin_fee'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-white font-black">Rp {{ number_format((float) $r['eo_amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">Tidak ada transaksi dengan kupon.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
