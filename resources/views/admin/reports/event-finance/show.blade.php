@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Event Finance - ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto" id="finance-report-capture">
        <div class="mb-8 relative z-10" data-aos="fade-up">
            <nav class="flex mb-2" aria-label="Breadcrumb" data-html2canvas-ignore="true">
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
                <div class="flex flex-wrap gap-2" data-html2canvas-ignore="true">
                    <a href="{{ route('admin.reports.event-finance.export-excel', $event) }}" class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-black uppercase tracking-widest transition-colors flex items-center gap-1.5 shadow-lg shadow-green-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </a>
                    <button type="button" onclick="exportReportAsImage()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-black uppercase tracking-widest transition-colors flex items-center gap-1.5 shadow-lg shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Export Gambar
                    </button>
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
                <div class="text-2xl font-black text-white mt-2">{{ $paid['eo_amount'] < 0 ? '- Rp ' . number_format(abs($paid['eo_amount']), 0, ',', '.') : 'Rp ' . number_format($paid['eo_amount'], 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-2">{{ number_format($paid['participants_count']) }} peserta · {{ number_format($paid['tx_count']) }} transaksi</div>
            </div>
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="text-xs font-mono text-slate-500 uppercase">Sudah Dibayar</div>
                <div class="text-2xl font-black text-white mt-2">Rp {{ number_format((float) $settled_amount, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-2">{{ number_format($payouts->where('status','completed')->count()) }} payout</div>
            </div>
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="text-xs font-mono text-slate-500 uppercase">Sisa Harus Dibayar</div>
                <div class="text-2xl font-black text-white mt-2">{{ $remaining_amount < 0 ? '- Rp ' . number_format(abs($remaining_amount), 0, ',', '.') : 'Rp ' . number_format($remaining_amount, 0, ',', '.') }}</div>
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
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden" data-html2canvas-ignore="true">
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
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider" data-html2canvas-ignore="true">Aksi</th>
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
                                        @if($p->notes)
                                            <div class="text-xs text-slate-400 mt-1 italic border-l-2 border-slate-600 pl-2 bg-slate-900/40 p-1.5 rounded-r">{{ $p->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm text-white font-black">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right" data-html2canvas-ignore="true">
                                        @if($p->status === 'completed')
                                            <button type="button" onclick="toggleEdit({{ $p->id }})" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-black text-xs uppercase tracking-widest border border-slate-700 transition-colors mr-2">
                                                Edit
                                            </button>
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
                                @if($p->status === 'completed')
                                    <tr id="edit-row-{{ $p->id }}" class="hidden bg-slate-900/80 border-b border-slate-700/50" data-html2canvas-ignore="true">
                                        <td colspan="4" class="px-6 py-4">
                                            <form method="POST" action="{{ route('admin.reports.event-finance.payouts.update', $p) }}" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div>
                                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal</label>
                                                        <input type="datetime-local" name="paid_at" value="{{ $p->paid_at ? $p->paid_at->format('Y-m-d\TH:i') : '' }}" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:border-red-500 focus:outline-none">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Metode</label>
                                                        <input type="text" name="method" value="{{ $p->method }}" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:border-red-500 focus:outline-none">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nominal (Rp)</label>
                                                        <input type="number" step="0.01" name="amount" value="{{ $p->amount }}" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:border-red-500 focus:outline-none">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Peserta (Opsional)</label>
                                                        <input type="number" name="participants_count" value="{{ $p->participants_count }}" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:border-red-500 focus:outline-none">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Catatan</label>
                                                    <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:border-red-500 focus:outline-none">{{ $p->notes }}</textarea>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="toggleEdit({{ $p->id }})" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-black uppercase tracking-widest border border-slate-700">Batal</button>
                                                    <button type="submit" class="px-3 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-black uppercase tracking-widest">Simpan</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
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
                <div class="text-white font-black uppercase tracking-widest text-sm">Breakdown Tipe Pendaftaran (Lunas)</div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-900/40">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Tipe Pendaftaran</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Tx</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Gross</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Diskon</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Platform Fee</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Hak EO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($registration_breakdown as $type => $r)
                            <tr class="hover:bg-slate-900/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white">{{ $r['name'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">{{ number_format($r['tx_count']) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">{{ number_format($r['participants_count']) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">Rp {{ number_format((float) $r['total_original'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">Rp {{ number_format((float) $r['discount_amount'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-200">Rp {{ number_format((float) $r['admin_fee'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-white font-black">{{ $r['eo_amount'] < 0 ? '- Rp ' . number_format(abs($r['eo_amount']), 0, ',', '.') : 'Rp ' . number_format($r['eo_amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @php
                        $totTx = array_sum(array_column($registration_breakdown, 'tx_count'));
                        $totPart = array_sum(array_column($registration_breakdown, 'participants_count'));
                        $totGross = array_sum(array_column($registration_breakdown, 'total_original'));
                        $totDisc = array_sum(array_column($registration_breakdown, 'discount_amount'));
                        $totFee = array_sum(array_column($registration_breakdown, 'admin_fee'));
                        $totEo = array_sum(array_column($registration_breakdown, 'eo_amount'));
                    @endphp
                    <tfoot class="bg-slate-900/60 border-t border-slate-700">
                        <tr class="font-bold text-white">
                            <td class="px-6 py-4 text-xs font-black text-slate-300 uppercase">Total</td>
                            <td class="px-6 py-4 text-right text-sm">{{ number_format($totTx) }}</td>
                            <td class="px-6 py-4 text-right text-sm">{{ number_format($totPart) }}</td>
                            <td class="px-6 py-4 text-right text-sm">Rp {{ number_format((float) $totGross, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-sm text-yellow-400">Rp {{ number_format((float) $totDisc, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-sm">Rp {{ number_format((float) $totFee, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-sm text-green-400 font-black">{{ $totEo < 0 ? '- Rp ' . number_format(abs($totEo), 0, ',', '.') : 'Rp ' . number_format($totEo, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
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
                                <td class="px-6 py-4 text-right text-sm text-white font-black">{{ $r['eo_amount'] < 0 ? '- Rp ' . number_format(abs($r['eo_amount']), 0, ',', '.') : 'Rp ' . number_format($r['eo_amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">Tidak ada transaksi dengan kupon.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($coupon_rows) > 0)
                        @php
                            $totalTx = array_sum(array_column($coupon_rows, 'tx_count'));
                            $totalParticipants = array_sum(array_column($coupon_rows, 'participants_count'));
                            $totalDiscount = array_sum(array_column($coupon_rows, 'discount_amount'));
                            $totalFee = array_sum(array_column($coupon_rows, 'admin_fee'));
                            $totalEo = array_sum(array_column($coupon_rows, 'eo_amount'));
                        @endphp
                        <tfoot class="bg-slate-900/60 border-t border-slate-700">
                            <tr class="font-bold text-white">
                                <td class="px-6 py-4 text-xs font-black text-slate-300 uppercase">Total</td>
                                <td class="px-6 py-4 text-right text-sm">{{ number_format($totalTx) }}</td>
                                <td class="px-6 py-4 text-right text-sm">{{ number_format($totalParticipants) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-yellow-400">Rp {{ number_format((float) $totalDiscount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm">Rp {{ number_format((float) $totalFee, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-green-400 font-black">{{ $totalEo < 0 ? '- Rp ' . number_format(abs($totalEo), 0, ',', '.') : 'Rp ' . number_format($totalEo, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function toggleEdit(id) {
        const row = document.getElementById('edit-row-' + id);
        if (row) {
            row.classList.toggle('hidden');
        }
    }

    function exportReportAsImage() {
        const target = document.getElementById('finance-report-capture');
        if (!target) return;

        const btn = document.querySelector('button[onclick="exportReportAsImage()"]');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generating Image...
        `;
        btn.disabled = true;

        html2canvas(target, {
            backgroundColor: '#0b0f19',
            scale: 2,
            useCORS: true,
            logging: false
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'finance_report_{{ $event->slug }}_' + new Date().toISOString().slice(0, 10) + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }).catch(err => {
            console.error('Html2canvas capture error:', err);
            alert('Gagal membuat gambar laporan.');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
</script>
@endpush
@endsection
