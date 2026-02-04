@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Event Submissions')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">EVENT SUBMISSIONS</h1>
            <p class="text-slate-400 mt-1">Antrian submit event dari publik yang menunggu review.</p>
        </div>

        <form action="{{ route('admin.event-submissions.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama event / lokasi / email..." class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 w-64 focus:outline-none focus:border-neon">
            <select name="status" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-neon">
                <option value="">Semua status</option>
                <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">Filter</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-900/30 border border-green-500/30 text-green-300 rounded-2xl p-4 font-bold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900/30 border border-red-500/30 text-red-300 rounded-2xl p-4 font-bold">{{ session('error') }}</div>
    @endif

    <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-900/60 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Kota</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Pengaju</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($submissions as $s)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-white font-bold">{{ $s->event_name }}</div>
                                <div class="text-xs text-slate-400">{{ $s->location_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-200 font-mono text-sm">
                                {{ optional($s->event_date)->format('d M Y') }}
                                @if($s->start_time)
                                    <div class="text-[11px] text-slate-500">{{ $s->start_time }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-200 text-sm">
                                {{ $s->city?->name ?? ($s->city_text ?: '-') }}
                            </td>
                            <td class="px-6 py-4 text-slate-200 text-sm">
                                <div class="font-bold text-white">{{ $s->contributor_name ?: '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $s->contributor_email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border {{ $s->badge_class }}">{{ strtoupper($s->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.event-submissions.show', $s) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">
                                    Review
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Tidak ada submission.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-800">
            {{ $submissions->links() }}
        </div>
    </div>
</div>
@endsection

