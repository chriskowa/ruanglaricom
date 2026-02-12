@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manage Races')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Dashboard
                </a>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">RACE MANAGEMENT</h1>
                <p class="text-slate-400 mt-1">CRUD race, link hasil, dan manage participant.</p>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <a href="{{ route('admin.races.create') }}" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    New Race
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-emerald-500/10 border border-emerald-500/40 text-emerald-200 p-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/40 text-red-200 p-4 rounded-xl">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 mb-6">
            <form method="GET" action="{{ route('admin.races.index') }}" class="flex flex-col md:flex-row gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama race..." class="w-full md:flex-1 px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                <input type="number" name="created_by" value="{{ request('created_by') }}" placeholder="Created by (user id)" class="w-full md:w-56 px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                <button type="submit" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold">Filter</button>
                <a href="{{ route('admin.races.index') }}" class="px-5 py-3 rounded-xl bg-slate-950 hover:bg-slate-900 border border-slate-700 text-slate-200 font-bold text-center">Reset</a>
            </form>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Race</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Owner</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Participants</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Sessions</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($races as $race)
                            <tr class="hover:bg-slate-950/60">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-800 overflow-hidden flex items-center justify-center text-slate-400 font-black">
                                            @if ($race->logo_path)
                                                <img src="{{ Storage::disk('public')->url($race->logo_path) }}" class="w-full h-full object-cover" />
                                            @else
                                                R
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-white font-bold">{{ $race->name }}</div>
                                            <div class="text-xs text-slate-500">#{{ $race->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-300">
                                    @if ($race->creator)
                                        <div class="font-semibold text-white">{{ $race->creator->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $race->creator->email }}</div>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-300">{{ number_format($race->participants_count ?? 0) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-300">{{ number_format($race->sessions_count ?? 0) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-400">{{ $race->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.races.show', $race) }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-bold">Open</a>
                                        <a href="{{ route('admin.races.edit', $race) }}" class="px-3 py-2 rounded-lg bg-slate-950 hover:bg-slate-900 border border-slate-700 text-slate-200 text-sm font-bold">Edit</a>
                                        <form method="POST" action="{{ route('admin.races.destroy', $race) }}" onsubmit="return confirm('Hapus race ini? Semua participant, session, lap, dan certificate akan ikut terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-bold">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada race.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800">
                {{ $races->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

