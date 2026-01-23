@extends('layouts.pacerhub')

@section('title', '419 Page Expired')

@section('content')
<div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 bg-dark z-0">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-slate-800 via-dark to-black opacity-80"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-neon/5 rounded-full blur-[150px] animate-pulse-slow"></div>
    </div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-[150px] md:text-[200px] font-black text-transparent bg-clip-text bg-gradient-to-b from-neon to-transparent leading-none select-none opacity-50">
            419
        </h1>
        <div class="space-y-6 -mt-10 md:-mt-20">
            <h2 class="text-3xl md:text-5xl font-bold text-white tracking-tight">
                Session <span class="text-neon italic">Expired</span>
            </h2>
            <p class="text-slate-400 text-lg max-w-lg mx-auto">
                Maaf, sesi Anda telah berakhir karena tidak ada aktivitas. Silakan refresh halaman untuk melanjutkan.
            </p>
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 mt-8">
                <button onclick="window.location.reload()" class="px-8 py-4 bg-neon text-dark font-black rounded-xl hover:bg-white hover:shadow-[0_0_30px_rgba(204,255,0,0.4)] transition-all transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    REFRESH PAGE
                </button>
                <a href="{{ route('login') }}" class="px-8 py-4 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 border border-slate-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                    LOGIN AGAIN
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
