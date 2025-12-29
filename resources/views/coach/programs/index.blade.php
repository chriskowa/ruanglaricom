@extends('layouts.coach')

@section('title', 'My Programs')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">Training Plans</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">My Programs</h1>
            </div>
            <a href="{{ route('coach.programs.create') }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20 flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Program
            </a>
        </div>

        <div class="glass-panel rounded-2xl p-6">
            @if($programs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-slate-500 text-xs uppercase border-b border-slate-700">
                                <th class="pb-3 pl-4">Program</th>
                                <th class="pb-3">Distance</th>
                                <th class="pb-3">Duration</th>
                                <th class="pb-3">Difficulty</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Enrolled</th>
                                <th class="pb-3 text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-300">
                            @foreach($programs as $program)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition">
                                <td class="py-4 pl-4">
                                    <div class="font-bold text-white">{{ $program->title }}</div>
                                    <div class="text-xs text-slate-500 truncate max-w-[200px]">{{ $program->description }}</div>
                                </td>
                                <td class="py-4 font-mono text-sm">
                                    <span class="bg-slate-800 px-2 py-1 rounded text-xs">{{ strtoupper($program->distance_target) }}</span>
                                </td>
                                <td class="py-4 text-sm">
                                    {{ $program->duration_weeks }} Weeks
                                </td>
                                <td class="py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold 
                                        {{ $program->difficulty === 'beginner' ? 'bg-green-500/20 text-green-500' : 
                                           ($program->difficulty === 'intermediate' ? 'bg-yellow-500/20 text-yellow-500' : 'bg-red-500/20 text-red-500') }}">
                                        {{ $program->difficulty }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold 
                                        {{ $program->is_published ? 'bg-neon/20 text-neon' : 'bg-slate-700 text-slate-400' }}">
                                        {{ $program->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="py-4 text-sm font-mono pl-4">
                                    {{ $program->enrollments()->count() }}
                                </td>
                                <td class="py-4 text-right pr-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('coach.programs.edit', $program->id) }}" class="p-2 rounded-lg bg-slate-800 text-white hover:bg-slate-700 transition" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        
                                        @if($program->is_published)
                                            <form action="{{ route('coach.programs.unpublish', $program->id) }}" method="POST" onsubmit="return confirm('Unpublish this program? It will be hidden from the marketplace.')">
                                                @csrf
                                                <button type="submit" class="p-2 rounded-lg bg-yellow-600/20 text-yellow-500 hover:bg-yellow-600/30 transition" title="Unpublish">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @ else
                                            <form action="{{ route('coach.programs.publish', $program->id) }}" method="POST" onsubmit="return confirm('Publish this program? It will be visible in the marketplace.')">
                                                @csrf
                                                <button type="submit" class="p-2 rounded-lg bg-green-600/20 text-green-500 hover:bg-green-600/30 transition" title="Publish">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('coach.programs.destroy', $program->id) }}" method="POST" onsubmit="return confirm('Delete this program? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg bg-red-600/20 text-red-500 hover:bg-red-600/30 transition" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $programs->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="bg-slate-800 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-white font-bold text-lg mb-2">No Programs Yet</h3>
                    <p class="text-slate-400 mb-6">Create your first training program to start selling.</p>
                    <a href="{{ route('coach.programs.create') }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition inline-flex items-center gap-2">
                        Start Creating
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
