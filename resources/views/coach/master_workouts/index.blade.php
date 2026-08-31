@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Master Workout Templates')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-6">
            <div>
                <p class="text-xs text-slate-400 mb-1">Library & Modul Latihan</p>
                <h1 class="text-2xl font-bold text-white">Master Workout Templates</h1>
                <p class="text-xs text-slate-300 mt-1">Kelola template sesi lari untuk mempermudah penyusunan program latihan.</p>
            </div>
            <a href="{{ route('coach.master-workouts.create') }}" class="px-4 py-2 bg-neon text-dark font-bold text-xs rounded-md hover:brightness-110 transition">
                + Buat Template Baru
            </a>
        </div>

        @if(session('success'))
            <div class="p-3.5 rounded-md bg-emerald-900 border border-emerald-800 text-emerald-200 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($workouts as $workout)
            <div class="bg-slate-900 rounded-lg p-5 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-medium border
                            {{ $workout->type === 'easy_run' ? 'bg-emerald-900 text-emerald-300 border-emerald-800' : '' }}
                            {{ $workout->type === 'long_run' ? 'bg-sky-900 text-sky-300 border-sky-800' : '' }}
                            {{ $workout->type === 'tempo' ? 'bg-amber-900 text-amber-300 border-amber-800' : '' }}
                            {{ $workout->type === 'interval' ? 'bg-rose-900 text-rose-300 border-rose-800' : '' }}
                            {{ $workout->type === 'strength' ? 'bg-purple-900 text-purple-300 border-purple-800' : '' }}
                            {{ $workout->type === 'rest' ? 'bg-slate-800 text-slate-400 border-slate-700' : '' }}
                        ">
                            {{ str_replace('_', ' ', $workout->type) }}
                        </span>
                        
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="{{ route('coach.master-workouts.edit', $workout->id) }}" class="px-2 py-1 rounded bg-slate-800 border border-slate-700 text-slate-300 hover:text-white transition">
                                Edit
                            </a>
                            <form action="{{ route('coach.master-workouts.destroy', $workout->id) }}" method="POST" onsubmit="return confirm('Hapus template ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 rounded bg-rose-950 hover:bg-rose-900 border border-rose-800 text-rose-300 transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <h3 class="text-sm font-semibold text-white mb-1.5">{{ $workout->title }}</h3>
                    <p class="text-slate-400 text-xs line-clamp-2 leading-relaxed">{{ $workout->description }}</p>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-800 pt-3">
                    <div class="flex items-center gap-3">
                        @if($workout->default_distance > 0)
                            <span class="font-mono text-slate-300">{{ $workout->default_distance }} km</span>
                        @endif
                        
                        @if($workout->default_duration)
                            <span class="font-mono text-slate-300">{{ $workout->default_duration }}</span>
                        @endif
                    </div>

                    <div class="capitalize text-[11px] font-medium {{ $workout->intensity === 'high' ? 'text-rose-400' : ($workout->intensity === 'medium' ? 'text-amber-400' : 'text-emerald-400') }}">
                        {{ $workout->intensity }} Intensity
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 bg-slate-900 rounded-lg border border-dashed border-slate-800">
                <h3 class="text-white font-semibold text-sm mb-1">Belum Ada Template Workout</h3>
                <p class="text-slate-400 text-xs mb-4">Buat template sesi latihan pertama untuk mempercepat penyusunan silabus.</p>
                <a href="{{ route('coach.master-workouts.create') }}" class="inline-block px-4 py-2 bg-neon text-dark font-bold rounded-md hover:brightness-110 transition text-xs">
                    + Buat Template Pertama
                </a>
            </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
