@extends('layouts.coach')

@section('title', 'Master Workouts')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-white italic tracking-tighter">Master Workouts</h1>
                <p class="text-slate-400 text-sm mt-1">Manage your workout templates library.</p>
            </div>
            <a href="{{ route('coach.master-workouts.create') }}" class="px-4 py-2 bg-neon text-dark font-bold rounded-xl hover:bg-neon/90 transition shadow-lg shadow-neon/20 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Create New Template
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($workouts as $workout)
            <div class="glass-panel rounded-2xl p-6 border border-slate-700/50 hover:border-neon/50 transition group">
                <div class="flex justify-between items-start mb-4">
                    <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wider
                        {{ $workout->type === 'easy_run' ? 'bg-green-500/20 text-green-400' : '' }}
                        {{ $workout->type === 'long_run' ? 'bg-blue-500/20 text-blue-400' : '' }}
                        {{ $workout->type === 'tempo' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                        {{ $workout->type === 'interval' ? 'bg-red-500/20 text-red-400' : '' }}
                        {{ $workout->type === 'strength' ? 'bg-purple-500/20 text-purple-400' : '' }}
                        {{ $workout->type === 'rest' ? 'bg-slate-500/20 text-slate-400' : '' }}
                    ">
                        {{ str_replace('_', ' ', $workout->type) }}
                    </span>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                        <a href="{{ route('coach.master-workouts.edit', $workout->id) }}" class="p-1 hover:text-white text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        <form action="{{ route('coach.master-workouts.destroy', $workout->id) }}" method="POST" onsubmit="return confirm('Delete this template?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 hover:text-red-400 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-white mb-2">{{ $workout->title }}</h3>
                <p class="text-slate-400 text-sm mb-4 line-clamp-2">{{ $workout->description }}</p>

                <div class="flex items-center gap-4 text-xs text-slate-500 border-t border-slate-700/50 pt-4">
                    @if($workout->default_distance > 0)
                    <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        {{ $workout->default_distance }} km
                    </div>
                    @endif
                    
                    @if($workout->default_duration)
                    <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $workout->default_duration }}
                    </div>
                    @endif

                    <div class="ml-auto capitalize {{ $workout->intensity === 'high' ? 'text-red-400' : ($workout->intensity === 'medium' ? 'text-yellow-400' : 'text-green-400') }}">
                        {{ $workout->intensity }} Int.
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-slate-800/30 rounded-3xl border border-dashed border-slate-700">
                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-white font-bold mb-2">No Templates Yet</h3>
                <p class="text-slate-400 text-sm mb-6">Create your first workout template to speed up program design.</p>
                <a href="{{ route('coach.master-workouts.create') }}" class="inline-flex px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition text-sm">
                    Create Template
                </a>
            </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
