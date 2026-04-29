@extends('layouts.pacerhub')

@section('title', $race->name)
@section('meta_title', $race->name.' | Ruang Lari')
@section('meta_description', $race->description ? \Illuminate\Support\Str::limit(strip_tags($race->description), 160) : ('Info race '.$race->name.' di Ruang Lari.'))

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 relative overflow-hidden font-sans">
    @php($gallery = is_array($race->gallery_paths) ? $race->gallery_paths : [])
    <div class="max-w-6xl mx-auto space-y-6">
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

        <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/50">
            @if ($race->banner_path)
                <img src="{{ Storage::disk('public')->url($race->banner_path) }}" alt="{{ $race->name }}" class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-slate-950/10"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-tr from-slate-950 via-slate-900 to-slate-950"></div>
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, rgba(239,68,68,0.35), transparent 45%), radial-gradient(circle at 80% 30%, rgba(34,211,238,0.25), transparent 40%);"></div>
            @endif

            <div class="relative p-6 md:p-10">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-950/70 border border-slate-800 overflow-hidden flex items-center justify-center text-slate-400 font-black flex-shrink-0">
                        @if ($race->logo_path)
                            <img src="{{ Storage::disk('public')->url($race->logo_path) }}" class="w-full h-full object-cover" alt="{{ $race->name }}">
                        @else
                            R
                        @endif
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter">{{ $race->name }}</h1>
                        <div class="text-sm text-slate-200/80 mt-3 flex flex-wrap gap-x-4 gap-y-1">
                            @if ($race->location_name)
                                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-location-dot"></i>{{ $race->location_name }}</span>
                            @endif
                            @if ($race->start_at)
                                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-calendar"></i>{{ $race->start_at->format('Y-m-d H:i') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 space-y-6">
                @if ($race->description)
                    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
                        <div class="p-6 md:p-8">
                            <h2 class="text-xl font-black text-white">Tentang Race</h2>
                            <div class="prose prose-invert max-w-none text-slate-200 mt-3">
                                {!! nl2br(e($race->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

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
                                        <button type="button" onclick="if (window.openLoginModal) window.openLoginModal(); else window.location='{{ route('login') }}';" class="px-4 py-2 rounded-xl bg-neon text-dark font-black">Login untuk Join</button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-slate-400">Belum ada kategori.</div>
                        @endforelse
                    </div>
                </div>

                @if ($race->prize_info)
                    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
                        <div class="p-6 md:p-8">
                            <h2 class="text-xl font-black text-white">Prize</h2>
                            <div class="prose prose-invert max-w-none text-slate-200 mt-3">
                                {!! nl2br(e($race->prize_info)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                @if (count($gallery))
                    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
                        <div class="p-6 md:p-8">
                            <h2 class="text-xl font-black text-white">Gallery</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-4">
                                @foreach ($gallery as $p)
                                    <a href="{{ Storage::disk('public')->url($p) }}" target="_blank" class="block rounded-2xl overflow-hidden border border-slate-800 bg-slate-950">
                                        <div class="aspect-square">
                                            <img src="{{ Storage::disk('public')->url($p) }}" class="w-full h-full object-cover" loading="lazy" alt="{{ $race->name }}" />
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-4 space-y-4 lg:sticky lg:top-24 self-start">
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-6">
                        <div class="text-white font-black">Lokasi & Waktu</div>
                        <div class="text-sm text-slate-300 mt-3 space-y-2">
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-location-dot mt-0.5 text-slate-400"></i>
                                <div>
                                    <div class="text-slate-400 text-xs">Lokasi</div>
                                    <div class="text-slate-200">{{ $race->location_name ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-calendar mt-0.5 text-slate-400"></i>
                                <div>
                                    <div class="text-slate-400 text-xs">Waktu</div>
                                    <div class="text-slate-200">
                                        @if ($race->start_at)
                                            {{ $race->start_at->format('Y-m-d H:i') }}
                                            @if ($race->end_at)
                                                <span class="text-slate-400">–</span> {{ $race->end_at->format('Y-m-d H:i') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-2">
                            <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(window.location.href);" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold">Copy Link</button>
                            @if (!auth()->check())
                                <button type="button" onclick="if (window.openLoginModal) window.openLoginModal(); else window.location='{{ route('login') }}';" class="px-4 py-2 rounded-xl bg-neon text-dark font-black">Login</button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-6">
                        <div class="text-white font-black">Quick Info</div>
                        <div class="text-sm text-slate-300 mt-3 space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-400">Kategori</div>
                                <div class="text-slate-200 font-bold">{{ $race->sessions->count() }}</div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-slate-400">Status</div>
                                <div class="text-slate-200 font-bold">{{ $race->is_published ? 'PUBLISHED' : 'DRAFT' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
