@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Email Blasts')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8 relative z-10" data-aos="fade-up">
            <div class="flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <div class="text-neon font-mono text-xs tracking-widest uppercase">EO Panel</div>
                    <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">EMAIL BLASTS</h1>
                    <div class="text-slate-400 text-sm mt-1">
                        Kirim email custom HTML ke perorangan atau list CSV.
                        @if($event)
                            <span class="text-slate-300">Event: {{ $event->name }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('eo.blasts.create', ['event' => $event ? $event->id : null]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-neon text-black font-bold hover:bg-neon/90 transition">
                        <i class="fa-solid fa-plus"></i>
                        Buat Blast
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/80 text-slate-300">
                        <tr>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Nama</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Sumber</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Status</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Progress</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Tanggal</th>
                            <th class="text-right px-4 py-3 font-black uppercase tracking-widest text-[10px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($blasts as $blast)
                            @php
                                $percent = $blast->target_count > 0 ? min(100, round((($blast->sent_count + $blast->failed_count) / $blast->target_count) * 100)) : 0;
                            @endphp
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-white">{{ $blast->name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($blast->source_type === 'csv')
                                        <div class="inline-flex items-center px-2 py-1 rounded-lg bg-cyan-500/15 text-cyan-200 border border-cyan-500/20 text-xs font-bold">CSV</div>
                                        <div class="text-slate-400 text-xs mt-1">{{ $blast->csv_original_name }}</div>
                                    @else
                                        <div class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-700/40 text-slate-200 border border-slate-700 text-xs font-bold">SINGLE</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($blast->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-500/15 text-green-200 border border-green-500/20 text-xs font-bold">COMPLETED</span>
                                    @elseif($blast->status === 'processing')
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-500/15 text-blue-200 border border-blue-500/20 text-xs font-bold">PROCESSING</span>
                                    @elseif($blast->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-red-500/15 text-red-200 border border-red-500/20 text-xs font-bold">FAILED</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-700/40 text-slate-200 border border-slate-700 text-xs font-bold">{{ strtoupper($blast->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-slate-200 text-xs mb-2">
                                        {{ $blast->sent_count }} / {{ $blast->target_count }} sent
                                        @if($blast->failed_count > 0)
                                            <span class="text-red-300">({{ $blast->failed_count }} failed)</span>
                                        @endif
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-2 bg-neon" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300 text-xs font-mono">{{ $blast->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('eo.blasts.show', ['blast' => $blast->id]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition text-xs font-bold">
                                        Detail
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                    Belum ada email blast.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($blasts->hasPages())
                <div class="px-4 py-4 border-t border-slate-800">
                    {{ $blasts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
