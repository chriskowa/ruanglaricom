@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Ajukan Analisis Lari')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17] bg-gradient-to-b from-[#060a17] via-[#0d162d] to-[#060a17]">
    <div class="max-w-2xl mx-auto">

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-6">
            <a href="{{ route('runner.analysis-requests.index') }}" class="text-slate-400 hover:text-white text-sm flex items-center gap-1 mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </a>
            <p class="text-neon font-mono text-xs tracking-widest uppercase mb-1">Running Analysis</p>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Ajukan Analisis Lari</h1>
            <p class="text-slate-400 text-sm mt-2">Isi form berikut. Admin akan meninjau dan menjadwalkan sesi pengambilan video untuk Anda.</p>
        </div>

        @if ($hasPending)
            <div class="mb-6 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-300 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Anda sudah memiliki permintaan yang menunggu persetujuan. Selesaikan atau tunggu proses sebelumnya sebelum mengajukan yang baru.
            </div>
        @endif

        <form method="POST" action="{{ route('runner.analysis-requests.store') }}"
              class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 space-y-5 {{ $hasPending ? 'opacity-60 pointer-events-none' : '' }}">
            @csrf

            <!-- Focus Area -->
            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Fokus Analisis <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($focusAreas as $key => $label)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-700 bg-slate-900/40 cursor-pointer hover:border-neon/50 transition">
                            <input type="radio" name="focus_area" value="{{ $key }}" required
                                   class="accent-neon w-4 h-4" {{ old('focus_area') === $key ? 'checked' : '' }}>
                            <span class="text-slate-200 text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('focus_area')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Goals -->
            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Tujuan / Harapan</label>
                <textarea name="goals" rows="3" maxlength="1000"
                          class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-4 py-3 text-sm focus:border-neon focus:outline-none transition"
                          placeholder="Contoh: ingin memperbaiki postur lari agar tidak cepat lelah...">{{ old('goals') }}</textarea>
                @error('goals')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Catatan Tambahan</label>
                <textarea name="notes" rows="2" maxlength="1000"
                          class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-4 py-3 text-sm focus:border-neon focus:outline-none transition"
                          placeholder="Riwayat cedera, kondisi khusus, dll.">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preferred Location -->
            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Lokasi Preferensi</label>
                <input type="text" name="preferred_location" maxlength="255" value="{{ old('preferred_location') }}"
                       class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-4 py-3 text-sm focus:border-neon focus:outline-none transition"
                       placeholder="Contoh: Ruang Lari Malang">
                @error('preferred_location')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preferred Date -->
            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Tanggal Preferensi</label>
                <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                       class="w-full rounded-xl bg-slate-900/60 border border-slate-700 text-slate-100 px-4 py-3 text-sm focus:border-neon focus:outline-none transition">
                @error('preferred_date')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full px-5 py-3.5 rounded-xl bg-neon text-[#121212] font-black hover:scale-[1.02] transition-all shadow-lg shadow-neon/20 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Permintaan
            </button>
        </form>

    </div>
</div>
@endsection
