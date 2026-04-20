@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Event Finance Report')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8 relative z-10" data-aos="fade-up">
            <div class="flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <p class="text-red-500 font-mono text-xs tracking-widest uppercase mb-1">Admin Report</p>
                    <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">EVENT FINANCE</h1>
                    <div class="text-slate-400 text-sm mt-1">Rekap hak EO, platform fee, kupon, dan pencairan.</div>
                </div>
            </div>
        </div>

        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-slate-700">
                <form method="GET" action="{{ route('admin.reports.event-finance.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1 min-w-0">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Cari</label>
                        <input name="q" value="{{ $q }}" placeholder="Nama event / EO / email" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-red-500 focus:outline-none">
                    </div>
                    <button type="submit" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-black text-sm uppercase tracking-widest border border-slate-700 transition-colors">
                        Search
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-900/40">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">EO</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Start</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($events as $e)
                            <tr class="hover:bg-slate-900/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white">{{ $e->name }}</div>
                                    <div class="text-xs text-slate-500 mt-1">ID: {{ $e->id }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-200">{{ $e->user ? $e->user->name : '-' }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $e->user ? $e->user->email : '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-200">{{ $e->start_at ? $e->start_at->format('d M Y') : '-' }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $e->created_at ? $e->created_at->format('d M Y H:i') : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.reports.event-finance.show', $e) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white font-black text-xs uppercase tracking-widest hover:scale-105 transition-all shadow-lg shadow-red-500/20">
                                        Open
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">Tidak ada event.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-800">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

