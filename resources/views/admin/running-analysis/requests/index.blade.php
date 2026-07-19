@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Permintaan Analisis Lari')

@section('content')
<div id="admin-analysis-requests" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center gap-3 relative z-10">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center gap-3 relative z-10">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-8 relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-red-500 font-mono text-sm tracking-widest uppercase mb-1">Running Analysis</p>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Permintaan Analisis</h1>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6 relative z-10">
        @foreach ($statTabs as $key => $tab)
            <a href="{{ route('admin.running-analysis.requests.index', ['status' => $key]) }}"
               class="bg-card/50 backdrop-blur-md border rounded-2xl p-4 transition-all hover:scale-[1.02]
               {{ $status === $key ? 'border-neon/60 shadow-lg shadow-neon/10' : 'border-slate-700/50' }}">
                <div class="text-2xl font-black text-white">{{ $tab['count'] }}</div>
                <div class="text-xs text-slate-400 font-bold uppercase mt-1">{{ $tab['label'] }}</div>
            </a>
        @endforeach
    </div>

    <!-- List -->
    <div class="relative z-10 space-y-3">
        @forelse ($requests as $req)
            <a href="{{ route('admin.running-analysis.requests.show', $req) }}"
               class="block bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-5 hover:border-neon/40 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-bold text-white">{{ $req->runner_name }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase
                                @switch($req->status)
                                    @case('pending') bg-yellow-500/15 text-yellow-300 @break
                                    @case('approved') bg-green-500/15 text-green-300 @break
                                    @case('scheduled') bg-blue-500/15 text-blue-300 @break
                                    @case('completed') bg-purple-500/15 text-purple-300 @break
                                    @case('rejected') bg-red-500/15 text-red-300 @break
                                @endswitch">{{ $req->statusLabel() }}</span>
                        </div>
                        <p class="text-slate-400 text-sm mt-1">{{ $req->focusAreaLabel() }}</p>
                        <p class="text-slate-500 text-xs mt-1">{{ $req->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    @if ($req->isPending())
                        <span class="px-3 py-1.5 rounded-lg bg-neon/15 border border-neon/30 text-neon text-xs font-black uppercase shrink-0">
                            Perlu Tinjauan
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-12 text-center">
                <p class="text-slate-300 font-bold">Tidak ada permintaan</p>
                <p class="text-slate-500 text-sm mt-1">Belum ada permintaan analisis dengan filter ini.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6 relative z-10">
        {{ $requests->links() }}
    </div>
</div>
@endsection
