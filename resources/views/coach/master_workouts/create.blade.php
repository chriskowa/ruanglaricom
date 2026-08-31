@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', isset($masterWorkout) ? 'Edit Template' : 'Create Template')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="border-b border-slate-800 pb-6">
            <a href="{{ route('coach.master-workouts.index') }}" class="text-slate-400 hover:text-white text-xs mb-2 inline-flex items-center gap-1">
                &larr; Kembali ke Library
            </a>
            <h1 class="text-2xl font-bold text-white mt-1">
                {{ isset($masterWorkout) ? 'Edit Template Workout' : 'Template Workout Baru' }}
            </h1>
            <p class="text-xs text-slate-300 mt-1">Simpan sesi latihan terstandarisasi untuk digunakan berulang pada program latihan.</p>
        </div>

        <form action="{{ isset($masterWorkout) ? route('coach.master-workouts.update', $masterWorkout->id) : route('coach.master-workouts.store') }}" method="POST" class="bg-slate-900 border border-slate-800 rounded-lg p-6 md:p-8 space-y-6">
            @csrf
            @if(isset($masterWorkout))
                @method('PUT')
            @endif

            <!-- Type Selection -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Tipe Workout</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @foreach(['easy_run', 'long_run', 'tempo', 'interval', 'strength', 'rest'] as $type)
                    <label class="cursor-pointer relative">
                        <input type="radio" name="type" value="{{ $type }}" class="peer sr-only" {{ (old('type', $masterWorkout->type ?? '') == $type) ? 'checked' : '' }}>
                        <div class="p-3 rounded-md bg-slate-800 border border-slate-700 text-center peer-checked:border-neon peer-checked:bg-slate-700 transition hover:bg-slate-700">
                            <div class="text-xs font-semibold text-white uppercase">{{ str_replace('_', ' ', $type) }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('type') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Basic Info -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Judul Template</label>
                    <input type="text" name="title" value="{{ old('title', $masterWorkout->title ?? '') }}" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-xs text-white focus:border-slate-600 outline-none" placeholder="Contoh: Morning Easy Run 5K">
                    @error('title') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Parameters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Jarak (km)</label>
                    <input type="number" step="0.1" name="default_distance" value="{{ old('default_distance', $masterWorkout->default_distance ?? 0) }}" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-xs text-white focus:border-slate-600 outline-none font-mono" placeholder="Contoh: 5.0">
                    <p class="text-xs text-slate-400 mt-1">Isi salah satu: Jarak atau Durasi</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Durasi Target</label>
                    <input type="text" name="default_duration" value="{{ old('default_duration', $masterWorkout->default_duration ?? '') }}" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-xs text-white focus:border-slate-600 outline-none font-mono" placeholder="Contoh: 00:45:00">
                    <p class="text-xs text-slate-400 mt-1">Format: HH:MM:SS (contoh 00:30:00)</p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Deskripsi / Instruksi Sesi</label>
                <textarea name="description" rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-xs text-white focus:border-slate-600 outline-none" placeholder="Tuliskan petunjuk warmup, main set, pace target, dan cooldown...">{{ old('description', $masterWorkout->description ?? '') }}</textarea>
                @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Tingkat Intensitas</label>
                <div class="flex gap-4">
                    @foreach(['low', 'medium', 'high'] as $intensity)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="intensity" value="{{ $intensity }}" class="text-neon focus:ring-neon bg-slate-950 border-slate-800" {{ (old('intensity', $masterWorkout->intensity ?? 'low') == $intensity) ? 'checked' : '' }}>
                        <span class="text-xs text-slate-300 capitalize font-medium">{{ $intensity }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-5 border-t border-slate-800 flex justify-end gap-2.5">
                <a href="{{ route('coach.master-workouts.index') }}" class="px-4 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">
                    {{ isset($masterWorkout) ? 'Simpan Perubahan' : 'Buat Template' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
