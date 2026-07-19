@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Auto Blog Topics')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">

    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">AUTO BLOG TOPICS</h1>
            <p class="text-slate-400 mt-1">Topik untuk generator artikel otomatis harian (draft). Dijalankan tiap 01:00 via scheduler.</p>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('blog.ai-topics.seed') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm">
                    Seed Topik Lari
                </button>
            </form>
            <a href="{{ route('blog.ai-topics.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark hover:bg-neon/90 transition-all font-bold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Topik Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-500/20 border border-green-500/40 text-green-300 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 font-bold">
                        <th class="px-6 py-4">Topik</th>
                        <th class="px-6 py-4">Referensi URL</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($topics as $t)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">{{ $t->topic }}</td>
                        <td class="px-6 py-4 text-xs text-slate-400">{{ $t->url ?: '-' }}</td>
                        <td class="px-6 py-4">
                            @if($t->is_active)
                                <span class="px-2 py-1 rounded-full bg-green-500/20 text-green-300 text-xs font-bold">Aktif</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-slate-600/30 text-slate-400 text-xs font-bold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('blog.ai-topics.edit', $t) }}" class="text-fuchsia-400 hover:text-fuchsia-300 text-sm font-bold mr-3">Edit</a>
                            <form action="{{ route('blog.ai-topics.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Hapus topik ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada topik. Klik "Seed Topik Lari" atau tambah manual.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $topics->links() }}
        </div>
    </div>
</div>
@endsection
