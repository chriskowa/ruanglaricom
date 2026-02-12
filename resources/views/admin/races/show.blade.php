@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Race Detail')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
            <div>
                <a href="{{ route('admin.races.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Race Management
                </a>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">{{ strtoupper($race->name) }}</h1>
                <div class="text-slate-400 text-sm mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    <span>#{{ $race->id }}</span>
                    <span>Participants: {{ number_format($race->participants_count ?? 0) }}</span>
                    <span>Sessions: {{ number_format($race->sessions_count ?? 0) }}</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <a href="{{ route('admin.races.edit', $race) }}" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-950 hover:bg-slate-900 border border-slate-700 text-slate-200 font-bold text-center">Edit</a>
                <form method="POST" action="{{ route('admin.races.destroy', $race) }}" onsubmit="return confirm('Hapus race ini? Semua participant, session, lap, dan certificate akan ikut terhapus.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black">Delete</button>
                </form>
            </div>
        </div>

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 lg:col-span-2">
                <h2 class="text-xl font-black text-white mb-4">Info</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                        <div class="text-xs text-slate-500 font-bold uppercase">Owner</div>
                        <div class="text-white font-bold mt-1">
                            @if ($race->creator)
                                {{ $race->creator->name }} <span class="text-slate-500 font-semibold">({{ $race->creator->email }})</span>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                        <div class="text-xs text-slate-500 font-bold uppercase">Created At</div>
                        <div class="text-white font-bold mt-1">{{ $race->created_at?->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-xl font-black text-white mb-4">Logo</h2>
                <div class="w-full aspect-square rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center">
                    @if ($race->logo_path)
                        <img src="{{ Storage::disk('public')->url($race->logo_path) }}" class="w-full h-full object-contain" />
                    @else
                        <div class="text-slate-500 font-bold">Tidak ada logo</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between gap-3">
                <h2 class="text-xl font-black text-white">Sessions</h2>
                <div class="text-sm text-slate-400">Menampilkan 50 terbaru</div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Distance</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Started</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Ended</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Result Link</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @if ($sessions->count() > 0)
                            @foreach ($sessions as $s)
                                <tr class="hover:bg-slate-950/60">
                                    <td class="px-4 py-3 text-sm text-slate-300">#{{ $s->id }}</td>
                                    <td class="px-4 py-3 text-sm text-white font-bold">{{ $s->category ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-300">{{ $s->distance_km !== null ? $s->distance_km.' km' : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-400">{{ $s->started_at?->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-400">{{ $s->ended_at?->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($s->slug)
                                            <div class="flex justify-end gap-2 items-center">
                                                <a href="{{ route('tools.race-master.results', ['slug' => $s->slug]) }}" target="_blank" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-bold">Open</a>
                                                <button type="button" class="px-3 py-2 rounded-lg bg-slate-950 hover:bg-slate-900 border border-slate-700 text-slate-200 text-sm font-bold" data-copy="{{ route('tools.race-master.results', ['slug' => $s->slug]) }}">Copy</button>
                                            </div>
                                        @else
                                            <span class="text-slate-500 text-sm">No slug</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada session.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-800">
                <h2 class="text-xl font-black text-white">Participants</h2>
                <div class="text-sm text-slate-400 mt-1">Format predicted time: mm:ss(.cc), hh:mm:ss(.cc), atau ms</div>
            </div>

            <div class="p-6 border-b border-slate-800">
                <form method="POST" action="{{ route('admin.races.participants.store', $race) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    @csrf
                    <input name="bib_number" value="{{ old('bib_number') }}" placeholder="BIB" class="px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                    <input name="name" value="{{ old('name') }}" placeholder="Nama" class="px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none md:col-span-2">
                    <div class="flex gap-2">
                        <input name="predicted_time" value="{{ old('predicted_time') }}" placeholder="Predicted (opsional)" class="flex-1 px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                        <button type="submit" class="px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black">Add</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">BIB</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Predicted</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @if ($participants->count() > 0)
                            @foreach ($participants as $p)
                                <tr class="hover:bg-slate-950/60">
                                    <td class="px-4 py-3">
                                        <input form="participant-update-{{ $p->id }}" name="bib_number" value="{{ old('bib_number_'.$p->id, $p->bib_number) }}" class="w-28 px-3 py-2 rounded-lg border border-slate-700 bg-slate-950 text-white focus:ring-2 focus:ring-red-500 outline-none">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input form="participant-update-{{ $p->id }}" name="name" value="{{ old('name_'.$p->id, $p->name) }}" class="w-full min-w-64 px-3 py-2 rounded-lg border border-slate-700 bg-slate-950 text-white focus:ring-2 focus:ring-red-500 outline-none">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input form="participant-update-{{ $p->id }}" name="predicted_time" value="{{ old('predicted_time_'.$p->id, $p->formatted_predicted_time) }}" class="w-40 px-3 py-2 rounded-lg border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                                    </td>
                                    <td class="px-4 py-3">
                                        <form id="participant-update-{{ $p->id }}" method="POST" action="{{ route('admin.races.participants.update', [$race, $p]) }}">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                        <div class="flex justify-end gap-2">
                                            <button type="submit" form="participant-update-{{ $p->id }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-bold">Save</button>
                                            <form method="POST" action="{{ route('admin.races.participants.destroy', [$race, $p]) }}" onsubmit="return confirm('Hapus participant ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-bold">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada participant.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800">
                {{ $participants->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;
        const text = btn.getAttribute('data-copy');
        try {
            await navigator.clipboard.writeText(text);
            btn.textContent = 'Copied';
            setTimeout(() => btn.textContent = 'Copy', 1000);
        } catch (err) {
            window.prompt('Copy link:', text);
        }
    });
</script>
@endsection
