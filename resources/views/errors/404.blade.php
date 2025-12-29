@extends('layouts.pacerhub')

@section('title', '404 Not Found')

@section('content')
<div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 bg-dark z-0">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-slate-800 via-dark to-black opacity-80"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-neon/5 rounded-full blur-[150px] animate-pulse-slow"></div>
    </div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-[150px] md:text-[200px] font-black text-transparent bg-clip-text bg-gradient-to-b from-neon to-transparent leading-none select-none opacity-50">
            404
        </h1>
        <div class="space-y-6 -mt-10 md:-mt-20">
            <h2 class="text-3xl md:text-5xl font-bold text-white tracking-tight">
                Lost Your <span class="text-neon italic">Pace</span>?
            </h2>
            <p class="text-slate-400 text-lg max-w-lg mx-auto">
                The track you are looking for doesn't exist. You might have taken a wrong turn or the finish line has moved.
            </p>
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 mt-8">
                <a href="{{ route('home') }}" class="px-8 py-4 bg-neon text-dark font-black rounded-xl hover:bg-white hover:shadow-[0_0_30px_rgba(204,255,0,0.4)] transition-all transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    BACK TO HOME
                </a>
                <a href="javascript:history.back()" class="px-8 py-4 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 border border-slate-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    GO BACK
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
