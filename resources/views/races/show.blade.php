@extends('layouts.pacerhub')

@section('title', $race->name)
@section('meta_title', $race->name.' | Ruang Lari')
@section('meta_description', $race->description ? \Illuminate\Support\Str::limit(strip_tags($race->description), 160) : ('Info race '.$race->name.' di Ruang Lari.'))

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-5xl mx-auto space-y-6">
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/40 text-emerald-200 p-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/40 text-red-200 p-4 rounded-xl">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="p-6 md:p-8 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center text-slate-400 font-black flex-shrink-0">
                        @if ($race->logo_path)
                            <img src="{{ Storage::disk('public')->url($race->logo_path) }}" class="w-full h-full object-cover" alt="{{ $race->name }}">
                        @else
                            R
                        @endif
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">{{ $race->name }}</h1>
                        <div class="text-sm text-slate-400 mt-2 flex flex-wrap gap-x-4 gap-y-1">
                            @if ($race->location_name)
                                <span><i class="fa-solid fa-location-dot mr-1"></i>{{ $race->location_name }}</span>
                            @endif
                            @if ($race->start_at)
                                <span><i class="fa-solid fa-calendar mr-1"></i>{{ $race->start_at->format('Y-m-d H:i') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($race->description)
                    <div class="prose prose-invert max-w-none text-slate-200">
                        {!! nl2br(e($race->description)) !!}
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between gap-3">
                <h2 class="text-xl font-black text-white">Kategori</h2>
                @if (!empty($joinedBySession))
                    <div class="text-sm text-slate-300">Kamu sudah join.</div>
                @endif
            </div>
            <div class="p-6 space-y-3">
                @forelse ($race->sessions as $s)
                    @php($joined = $joinedBySession[$s->id] ?? null)
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <div class="text-white font-black">{{ $s->category ?: 'Kategori' }}</div>
                            <div class="text-xs text-slate-400 mt-1">
                                @if ($s->distance_km !== null)
                                    {{ $s->distance_km }} km
                                @else
                                    -
                                @endif
                                @if ($joined)
                                    • BIB: <span class="text-neon font-black">{{ $joined->bib_number }}</span>
                                @endif
                                @if ($s->started_at && ! $s->ended_at)
                                    • <span class="text-emerald-300 font-bold">RUNNING</span>
                                @elseif ($s->ended_at)
                                    • <span class="text-slate-300 font-bold">FINISHED</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 justify-end">
                            @if ($s->slug)
                                <a href="{{ route('tools.race-master.results', ['slug' => $s->slug]) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold">Leaderboard</a>
                            @endif
                            @if (auth()->check() && auth()->user()->isRunner())
                                @if ($joined)
                                    <span class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 font-bold">Sudah Join</span>
                                @else
                                    <form method="POST" action="{{ route('races.join', ['slug' => $race->slug, 'session' => $s->id]) }}">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-black">Join</button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-neon text-dark font-black">Login untuk Join</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-slate-400">Belum ada kategori.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
