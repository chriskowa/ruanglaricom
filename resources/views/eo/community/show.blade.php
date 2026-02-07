@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Community Participants')

@section('content')
<div class="min-h-screen pt-8 pb-10 px-4 md:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Event</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">COMMUNITY PARTICIPANTS</h1>
                <div class="text-slate-400 text-sm mt-1">{{ $event->name }}</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('eo.community.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">Kembali</a>
                <a href="{{ route('eo.events.participants', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">Participants</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-900/30 border border-green-500/30 text-green-200 rounded-2xl p-4 font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-900/30 border border-red-500/30 text-red-200 rounded-2xl p-4 font-bold">{{ session('error') }}</div>
        @endif

        <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-900/60 border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Komunitas</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">PIC</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Imported</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($registrations as $r)
                            @php
                                $inv = $r->invoices->sortByDesc('id')->first();
                                $tx = $inv?->transaction;
                                $pay = $tx?->payment_status ?: 'draft';
                                $badge = match ($pay) {
                                    'paid' => 'bg-green-500/15 text-green-200 border-green-500/30',
                                    'failed', 'expired' => 'bg-red-500/15 text-red-200 border-red-500/30',
                                    'pending' => 'bg-yellow-500/15 text-yellow-200 border-yellow-500/30',
                                    default => 'bg-slate-800 text-slate-200 border-slate-700',
                                };
                                $canImport = $pay === 'paid' && !$r->imported_at;
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-white font-bold">{{ $r->community_name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">#{{ $r->id }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-200 text-sm">
                                    <div class="font-bold text-white">{{ $r->pic_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $r->pic_email }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $r->pic_phone }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border bg-slate-800 text-slate-200 border-slate-700">{{ $r->participants_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-200 text-sm">
                                    @if($inv && $tx)
                                        <div class="font-bold text-white">{{ strtoupper($inv->payment_method) }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $tx->public_ref }}</div>
                                        <div class="text-xs text-slate-400">Rp {{ number_format((float) $tx->final_amount, 0, ',', '.') }}</div>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border {{ $badge }}">{{ strtoupper($pay) }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-200 text-sm font-mono">
                                    {{ $r->imported_at ? $r->imported_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($canImport)
                                        <form action="{{ route('eo.events.community.import', ['event' => $event->id, 'registration' => $r->id]) }}" method="POST" onsubmit="return confirm('Import peserta komunitas ini ke peserta event sebagai PAID?');">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">Import</button>
                                        </form>
                                    @else
                                        <span class="text-slate-500 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">Belum ada registrasi komunitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $registrations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

