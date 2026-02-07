@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Community Participants')

@section('content')
<div class="min-h-screen pt-8 pb-10 px-4 md:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">EO Panel</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">COMMUNITY PARTICIPANTS</h1>
                <div class="text-slate-400 text-sm mt-1">Kelola peserta komunitas dan import ke peserta event.</div>
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
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Komunitas</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Paid</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($events as $e)
                            @php
                                $c = $counts[$e->id] ?? null;
                                $total = (int) ($c->total ?? 0);
                                $paid = (int) ($c->paid ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-white font-bold">{{ $e->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $e->slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-200 text-sm font-mono">
                                    {{ $e->start_at ? $e->start_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border bg-slate-800 text-slate-200 border-slate-700">{{ $total }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border bg-green-500/15 text-green-200 border-green-500/30">{{ $paid }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('eo.events.community.index', $e) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">
                                        Manage
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">Belum ada event.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
