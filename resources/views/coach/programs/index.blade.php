@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Katalog Program Latihan')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-6">
            <div>
                <p class="text-xs text-slate-400 mb-1">Katalog & Training Plan</p>
                <h1 class="text-2xl font-bold text-white">Program Latihan</h1>
                <p class="text-xs text-slate-300 mt-1">Kelola silabus program lari dan status publikasi di marketplace.</p>
            </div>
            <a href="{{ route('coach.programs.create') }}" class="px-4 py-2 rounded-md bg-neon text-dark font-bold hover:brightness-110 transition text-xs">
                + Buat Program Baru
            </a>
        </div>

        @if(session('success'))
            <div class="p-3.5 rounded-md bg-emerald-900 border border-emerald-800 text-emerald-200 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
            @if($programs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-[10px] font-mono uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 px-3">Program</th>
                                <th class="pb-3 px-3">Target</th>
                                <th class="pb-3 px-3">Durasi</th>
                                <th class="pb-3 px-3">Tingkat</th>
                                <th class="pb-3 px-3">Status</th>
                                <th class="pb-3 px-3 text-center">Peserta</th>
                                <th class="pb-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-300 divide-y divide-slate-800 font-sans">
                            @foreach($programs as $program)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-3">
                                    <div class="font-semibold text-white">
                                        <a href="{{ route('programs.show', $program->slug) }}" class="hover:text-neon transition">{{ $program->title }}</a>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[240px] mt-0.5">{!! strip_tags($program->description) !!}</div>
                                </td>
                                <td class="py-3.5 px-3 font-mono text-xs text-white">
                                    <span class="bg-slate-800 border border-slate-700 px-2 py-0.5 rounded text-[10px] uppercase font-bold">{{ strtoupper($program->distance_target) }}</span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-300">
                                    {{ $program->duration_weeks }} Minggu
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-medium border
                                        {{ $program->difficulty === 'beginner' ? 'bg-emerald-900/60 text-emerald-300 border-emerald-800' : 
                                           ($program->difficulty === 'intermediate' ? 'bg-sky-900/60 text-sky-300 border-sky-800' : 'bg-purple-900/60 text-purple-300 border-purple-800') }}">
                                        {{ $program->difficulty }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-medium border
                                        {{ $program->is_published ? 'bg-emerald-900 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                        {{ $program->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono">
                                    <a href="{{ route('coach.athletes.index', ['program_id' => $program->id]) }}" class="text-neon hover:underline font-bold text-xs" title="Kelola Atlet">
                                        {{ $program->enrollments_count }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        @if($program->is_published)
                                            <form action="{{ route('coach.programs.unpublish', $program->id) }}" method="POST" onsubmit="return confirm('Unpublish program ini? Program akan disembunyikan dari katalog.')" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-medium transition" title="Unpublish">
                                                    Unpublish
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('coach.programs.publish', $program->id) }}" method="POST" onsubmit="return confirm('Publikasikan program ini ke katalog?')" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded bg-emerald-900 hover:bg-emerald-800 border border-emerald-800 text-emerald-200 text-xs font-medium transition" title="Publish">
                                                    Publish
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('coach.programs.edit', $program->id) }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-medium transition">
                                            Edit
                                        </a>

                                        <div class="inline-flex items-center rounded border border-slate-700 bg-slate-950 text-[10px] font-mono">
                                            <a href="{{ route('coach.programs.export-excel', $program->id) }}" class="px-1.5 py-0.5 text-slate-300 hover:text-emerald-400 transition" title="Export Excel">XLS</a>
                                            <span class="text-slate-700">|</span>
                                            <a href="{{ route('coach.programs.export-csv', $program->id) }}" class="px-1.5 py-0.5 text-slate-300 hover:text-sky-400 transition" title="Export CSV">CSV</a>
                                            <span class="text-slate-700">|</span>
                                            <a href="{{ route('coach.programs.export-json', $program->id) }}" class="px-1.5 py-0.5 text-slate-300 hover:text-amber-400 transition" title="Export JSON">JSON</a>
                                        </div>

                                        <form action="{{ route('coach.programs.destroy', $program->id) }}" method="POST" onsubmit="return confirm('Hapus program ini? Tindakan ini tidak dapat dibatalkan.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded bg-red-900/60 hover:bg-red-800 border border-red-800 text-red-200 text-xs font-medium transition" title="Hapus">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-800">
                    {{ $programs->links() }}
                </div>
            @else
                <div class="text-center py-10 border border-dashed border-slate-800 rounded-lg">
                    <p class="text-xs text-slate-400">Belum ada program latihan yang dibuat.</p>
                    <a href="{{ route('coach.programs.create') }}" class="inline-block mt-3 px-4 py-2 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">
                        Buat Program Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
