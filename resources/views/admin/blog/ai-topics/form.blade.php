@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', $topic ? 'Edit Topik' : 'Topik Baru')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-2xl mx-auto relative z-10">
        <h1 class="text-3xl font-black text-white italic tracking-tighter mb-6">{{ $topic ? 'Edit Topik' : 'Topik Baru' }}</h1>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-green-500/20 border border-green-500/40 text-green-300 text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ $topic ? route('blog.ai-topics.update', $topic) : route('blog.ai-topics.store') }}" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 space-y-5">
            @csrf
            @if($topic) @method('PUT') @endif

            <div>
                <label class="block text-sm font-bold text-white mb-2">Topik Artikel</label>
                <input type="text" name="topic" value="{{ old('topic', $topic->topic ?? '') }}" required
                    class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:border-fuchsia-500 outline-none"
                    placeholder="Contoh: Tips latihan lari 10K untuk pemula">
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">URL Referensi (opsional)</label>
                <input type="url" name="url" value="{{ old('url', $topic->url ?? '') }}"
                    class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:border-fuchsia-500 outline-none"
                    placeholder="https://www.runnersworld.com/">
                <p class="text-xs text-slate-500 mt-1">URL digunakan sebagai konteks riset oleh AI saat generate draft.</p>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $topic ? $topic->is_active : true) ? 'checked' : '' }}
                    class="w-5 h-5 rounded bg-slate-900 border-slate-700 text-fuchsia-600">
                <label class="text-sm text-white">Aktif (ikut diproses scheduler harian)</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all">
                    {{ $topic ? 'Simpan' : 'Tambah' }}
                </button>
                <a href="{{ route('blog.ai-topics.index') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
