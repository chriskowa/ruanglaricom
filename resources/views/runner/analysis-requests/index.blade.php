@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Permintaan Analisis Lari')

@push('styles')
<style>
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-pending   { background: rgba(250, 204, 21, 0.15); color: #facc15; }
    .status-approved  { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
    .status-scheduled { background: rgba(96, 165, 250, 0.15); color: #60a5fa; }
    .status-completed { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
    .status-rejected  { background: rgba(248, 113, 113, 0.15); color: #f87171; }
</style>
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17] bg-gradient-to-b from-[#060a17] via-[#0d162d] to-[#060a17]">
    <div class="max-w-5xl mx-auto">

        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <p class="text-neon font-mono text-xs tracking-widest uppercase mb-1">Running Analysis</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Permintaan Analisis Lari</h1>
                <p class="text-slate-400 text-sm mt-2">Ajukan analisis lari (gait, form, cedera) dan pantau status dari admin.</p>
            </div>
            <a href="{{ route('runner.analysis-requests.create') }}"
               class="px-5 py-3 rounded-xl bg-neon text-[#121212] font-black hover:scale-105 transition-all shadow-lg shadow-neon/20 flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajukan Analisis
            </a>
        </div>

        @if ($pendingCount > 0)
            <div class="mb-6 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-300 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Anda memiliki {{ $pendingCount }} permintaan yang sedang menunggu persetujuan admin.
            </div>
        @endif

        <!-- List -->
        @if ($requests->isEmpty())
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <p class="text-slate-300 font-bold mb-1">Belum ada permintaan</p>
                <p class="text-slate-500 text-sm mb-6">Anda belum pernah mengajukan analisis lari.</p>
                <a href="{{ route('runner.analysis-requests.create') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-neon text-[#121212] font-black hover:scale-105 transition-all">
                    Ajukan Sekarang
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($requests as $req)
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-5 hover:border-neon/40 transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h3 class="text-white font-bold">{{ $req->focusAreaLabel() }}</h3>
                                    <span class="status-badge status-{{ $req->status }}">{{ $req->statusLabel() }}</span>
                                </div>
                                <p class="text-slate-400 text-sm mt-1">
                                    Diajukan: {{ $req->created_at->format('d M Y, H:i') }}
                                </p>
                                @if ($req->goals)
                                    <p class="text-slate-300 text-sm mt-2 line-clamp-2">{{ Str::limit($req->goals, 120) }}</p>
                                @endif
                                @if ($req->session)
                                    <p class="text-blue-400 text-xs mt-2">
                                        Sesi: {{ $req->session->name }} ({{ optional($req->session->session_date)->format('d M Y') }})
                                    </p>
                                @endif
                            </div>
                            <div class="flex flex-col items-start sm:items-end gap-2 shrink-0">
                                @if ($req->status === 'completed')
                                    <span class="px-3 py-1.5 rounded-lg bg-purple-500/15 border border-purple-500/30 text-purple-300 text-xs font-bold">
                                        Hasil di menu Analisis
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
