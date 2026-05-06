@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Email Blast - ' . ($blast->name ?? 'Detail'))

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('eo.blasts.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white font-bold">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        @php
            $percent = $blast->target_count > 0 ? min(100, round((($blast->sent_count + $blast->failed_count) / $blast->target_count) * 100)) : 0;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Email Blast</div>
                <h1 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter mt-1">{{ $blast->name }}</h1>
                <div class="text-slate-400 text-sm mt-2">
                    Dibuat {{ $blast->created_at->format('d M Y, H:i') }} • Source: <span class="text-slate-200 font-bold">{{ strtoupper($blast->source_type) }}</span>
                    @if($blast->source_type === 'csv')
                        <span class="text-slate-400">({{ $blast->csv_original_name }})</span>
                    @endif
                </div>
                <div class="mt-4">
                    @if($blast->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-xl bg-green-500/15 text-green-200 border border-green-500/20 text-xs font-black tracking-widest">COMPLETED</span>
                    @elseif($blast->status === 'processing')
                        <span class="inline-flex items-center px-3 py-1 rounded-xl bg-blue-500/15 text-blue-200 border border-blue-500/20 text-xs font-black tracking-widest">PROCESSING</span>
                    @elseif($blast->status === 'failed')
                        <span class="inline-flex items-center px-3 py-1 rounded-xl bg-red-500/15 text-red-200 border border-red-500/20 text-xs font-black tracking-widest">FAILED</span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-xl bg-slate-700/40 text-slate-200 border border-slate-700 text-xs font-black tracking-widest">{{ strtoupper($blast->status) }}</span>
                    @endif
                </div>
            </div>

            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
                <div class="text-slate-400 text-xs font-black uppercase tracking-widest">Progress</div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-slate-200 text-sm">Target</div>
                    <div class="text-white font-black text-lg font-mono">{{ $blast->target_count }}</div>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <div class="text-slate-200 text-sm">Sent</div>
                    <div class="text-green-200 font-black text-lg font-mono">{{ $blast->sent_count }}</div>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <div class="text-slate-200 text-sm">Failed</div>
                    <div class="text-red-200 font-black text-lg font-mono">{{ $blast->failed_count }}</div>
                </div>
                <div class="mt-4 w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-2 bg-neon" style="width: {{ $percent }}%"></div>
                </div>
                <div class="mt-2 text-slate-400 text-xs font-mono">{{ $percent }}%</div>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800">
                <div class="text-white font-black tracking-tight">Deliveries</div>
                <div class="text-slate-400 text-sm">Status pengiriman untuk setiap penerima.</div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/80 text-slate-300">
                        <tr>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Email</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Nama</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Subject</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Status</th>
                            <th class="text-left px-4 py-3 font-black uppercase tracking-widest text-[10px]">Sent At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($deliveries as $delivery)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-4 py-3 text-slate-200 font-mono">{{ $delivery->to_email }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $delivery->to_name ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ \Illuminate\Support\Str::limit($delivery->rendered_subject, 60) ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($delivery->status === 'sent')
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-500/15 text-green-200 border border-green-500/20 text-xs font-bold">SENT</span>
                                    @elseif($delivery->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-red-500/15 text-red-200 border border-red-500/20 text-xs font-bold" title="{{ $delivery->error_message }}">FAILED</span>
                                    @elseif($delivery->status === 'queued')
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-yellow-500/15 text-yellow-200 border border-yellow-500/20 text-xs font-bold">QUEUED</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-700/40 text-slate-200 border border-slate-700 text-xs font-bold">PENDING</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $delivery->sent_at ? $delivery->sent_at->format('d M, H:i:s') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada delivery atau masih proses generate list.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deliveries->hasPages())
                <div class="px-6 py-4 border-t border-slate-800">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
