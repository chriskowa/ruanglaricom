@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', isset($masterWorkout) ? 'Edit Template' : 'Create Template')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('coach.master-workouts.index') }}" class="text-slate-400 hover:text-white text-xs mb-2 flex items-center gap-1">
                ← Back to Library
            </a>
            <h1 class="text-3xl font-black text-white italic tracking-tighter">
                {{ isset($masterWorkout) ? 'Edit Template' : 'New Template' }}
            </h1>
        </div>

        <form action="{{ isset($masterWorkout) ? route('coach.master-workouts.update', $masterWorkout->id) : route('coach.master-workouts.store') }}" method="POST" class="glass-panel rounded-2xl p-6 md:p-8 space-y-6">
            @csrf
            @if(isset($masterWorkout))
                @method('PUT')
            @endif

            <!-- Type Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Workout Type</label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['easy_run', 'long_run', 'tempo', 'interval', 'strength', 'rest'] as $type)
                    <label class="cursor-pointer relative">
                        <input type="radio" name="type" value="{{ $type }}" class="peer sr-only" {{ (old('type', $masterWorkout->type ?? '') == $type) ? 'checked' : '' }}>
                        <div class="p-3 rounded-xl bg-slate-800 border border-slate-700 text-center peer-checked:border-neon peer-checked:bg-neon/10 transition hover:bg-slate-700">
                            <div class="text-xs font-bold text-white uppercase">{{ str_replace('_', ' ', $type) }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Basic Info -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $masterWorkout->title ?? '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon outline-none" placeholder="e.g. Morning Easy Run">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Parameters -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Distance (km)</label>
                    <input type="number" step="0.1" name="default_distance" value="{{ old('default_distance', $masterWorkout->default_distance ?? 0) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon outline-none" placeholder="e.g. 5.0">
                    <p class="text-xs text-slate-500 mt-1">Isi salah satu: Distance atau Duration</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Duration</label>
                    <input type="text" name="default_duration" value="{{ old('default_duration', $masterWorkout->default_duration ?? '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon outline-none" placeholder="e.g. 00:45:00">
                    <p class="text-xs text-slate-500 mt-1">Format: HH:MM:SS (contoh 00:30:00)</p>
                </div>
            </div>

            <!-- Description aligned with session modal -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Description / Instructions</label>
                <textarea name="description" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon outline-none" placeholder="Describe the workout...">{{ old('description', $masterWorkout->description ?? '') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Intensity Level</label>
                <div class="flex gap-4">
                    @foreach(['low', 'medium', 'high'] as $intensity)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="intensity" value="{{ $intensity }}" class="text-neon focus:ring-neon bg-slate-900 border-slate-700" {{ (old('intensity', $masterWorkout->intensity ?? 'low') == $intensity) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 capitalize">{{ $intensity }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t border-slate-700 flex justify-end gap-3">
                <a href="{{ route('coach.master-workouts.index') }}" class="px-6 py-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-700 transition">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20">
                    {{ isset($masterWorkout) ? 'Update Template' : 'Create Template' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
