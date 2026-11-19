@extends('layouts.pacerhub')
@php $hideFooter = true; @endphp

@section('title', 'Pesan — Ruang Lari')

@section('content')
<div class="fixed inset-x-0 top-20 bottom-0 z-20 bg-slate-950 flex overflow-hidden">
    @include('chat.partials.sidebar', ['conversations' => $conversations])

    <div class="hidden md:flex flex-1 flex-col items-center justify-center bg-slate-900 p-8 text-center">
        <div class="max-w-md bg-slate-950 border border-slate-800 rounded-lg p-6">
            <h2 class="text-xl font-bold text-white" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                Pilih Percakapan
            </h2>
            <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                Pilih percakapan di sebelah kiri untuk melanjutkan diskusi dengan pelatih atau Coach Ruang Lari.
            </p>
            @if(!empty($aiCoach))
                <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
                    <a href="{{ route('chat.show', $aiCoach) }}" class="px-4 min-h-[44px] inline-flex items-center justify-center rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition">
                        Chat Coach Ruang Lari
                    </a>
                    <a href="{{ route('programs.realistic') }}" class="px-4 min-h-[44px] inline-flex items-center justify-center rounded-md bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition">
                        Buat Program Lari
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
html, body { overflow: hidden; height: 100%; }
.chat-scrollbar::-webkit-scrollbar { width: 5px; }
.chat-scrollbar::-webkit-scrollbar-track { background: #020617; }
.chat-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
.chat-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>
@endsection
