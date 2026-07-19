@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Detail Permintaan Analisis')

@section('content')
<div id="admin-analysis-request-show" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
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

    <div class="max-w-4xl mx-auto relative z-10">
        <a href="{{ route('admin.running-analysis.requests.index') }}" class="text-slate-400 hover:text-white text-sm flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Daftar
        </a>

        <div class="flex items-center gap-3 mb-6">
            <h1 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter">Permintaan Analisis</h1>
            <span class="text-xs px-3 py-1 rounded-full font-bold uppercase
                @switch($request->status)
                    @case('pending') bg-yellow-500/15 text-yellow-300 @break
                    @case('approved') bg-green-500/15 text-green-300 @break
                    @case('scheduled') bg-blue-500/15 text-blue-300 @break
                    @case('completed') bg-purple-500/15 text-purple-300 @break
                    @case('rejected') bg-red-500/15 text-red-300 @break
                @endswitch">{{ $request->statusLabel() }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Detail -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h2 class="text-white font-bold mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Data Runner
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <dt class="text-slate-400">Nama</dt>
                            <dd class="text-white font-semibold">{{ $request->runner_name }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <dt class="text-slate-400">Email</dt>
                            <dd class="text-white font-semibold">{{ $request->runner_email }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <dt class="text-slate-400">Fokus</dt>
                            <dd class="text-white font-semibold">{{ $request->focusAreaLabel() }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <dt class="text-slate-400">Lokasi Preferensi</dt>
                            <dd class="text-white font-semibold">{{ $request->preferred_location ?: '-' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <dt class="text-slate-400">Tanggal Preferensi</dt>
                            <dd class="text-white font-semibold">{{ optional($request->preferred_date)->format('d M Y') ?: '-' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <dt class="text-slate-400">Diajukan</dt>
                            <dd class="text-white font-semibold">{{ $request->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($request->goals)
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                        <h2 class="text-white font-bold mb-2">Tujuan</h2>
                        <p class="text-slate-300 text-sm whitespace-pre-line">{{ $request->goals }}</p>
                    </div>
                @endif

                @if ($request->notes)
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                        <h2 class="text-white font-bold mb-2">Catatan</h2>
                        <p class="text-slate-300 text-sm whitespace-pre-line">{{ $request->notes }}</p>
                    </div>
                @endif

                @if ($request->admin_notes)
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                        <h2 class="text-white font-bold mb-2">Catatan Admin</h2>
                        <p class="text-slate-300 text-sm whitespace-pre-line">{{ $request->admin_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                @if ($request->isPending())
                    <!-- Approve -->
                    <form method="POST" action="{{ route('admin.running-analysis.requests.approve', $request) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-3 rounded-xl bg-green-500/15 border border-green-500/30 text-green-300 font-black hover:bg-green-500/25 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui
                        </button>
                    </form>

                    <!-- Reject -->
                    <form method="POST" action="{{ route('admin.running-analysis.requests.reject', $request) }}">
                        @csrf
                        <textarea name="admin_notes" rows="2" required
                                  class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:border-red-500 focus:outline-none transition mb-2"
                                  placeholder="Alasan penolakan (wajib)..."></textarea>
                        <button type="submit"
                                class="w-full px-4 py-3 rounded-xl bg-red-500/15 border border-red-500/30 text-red-300 font-black hover:bg-red-500/25 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak
                        </button>
                    </form>
                @endif

                @if (in_array($request->status, ['approved', 'pending']))
                    <!-- Schedule -->
                    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-5">
                        <h2 class="text-white font-bold mb-3 text-sm">Jadwalkan ke Sesi</h2>
                        <form method="POST" action="{{ route('admin.running-analysis.requests.schedule', $request) }}">
                            @csrf
                            <select name="session_id" required
                                    class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:border-neon focus:outline-none transition mb-2">
                                <option value="">Pilih sesi...</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }} ({{ optional($session->session_date)->format('d M Y') }})</option>
                                @endforeach
                            </select>
                            <textarea name="admin_notes" rows="2"
                                      class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-3 py-2 text-sm focus:border-neon focus:outline-none transition mb-2"
                                      placeholder="Catatan (opsional)..."></textarea>
                            <button type="submit"
                                    class="w-full px-4 py-2.5 rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-300 font-black hover:bg-blue-500/25 transition">
                                Jadwalkan
                            </button>
                        </form>
                    </div>
                @endif

                @if ($request->status === 'scheduled')
                    <form method="POST" action="{{ route('admin.running-analysis.requests.complete', $request) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-3 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-300 font-black hover:bg-purple-500/25 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tandai Selesai
                        </button>
                    </form>
                @endif

                @if ($request->session)
                    <a href="{{ route('admin.running-analysis.sessions.show', $request->session) }}"
                       class="block w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white font-bold hover:border-neon hover:text-neon transition text-center">
                        Lihat Sesi
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
