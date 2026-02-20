@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Scheduled Popups')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-xs font-mono text-slate-400 uppercase tracking-widest">Popup Management</div>
            <h1 class="text-3xl font-black text-white">Scheduled Popups</h1>
        </div>
        <a href="{{ route('admin.popups.create') }}" class="px-5 py-2 rounded-xl bg-primary text-slate-900 font-bold">Create New Popup</a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-slate-900/60 border border-slate-700 rounded-2xl">
        <table class="w-full text-sm text-slate-200">
            <thead class="text-xs uppercase text-slate-400 border-b border-slate-700">
                <tr>
                    <th class="p-3 text-left">Popup</th>
                    <th class="p-3 text-left">Schedule</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($popups as $popup)
                    <tr class="border-b border-slate-800 hover:bg-slate-800/40">
                        <td class="p-3">
                            <div class="font-bold text-white">{{ $popup->name }}</div>
                            <div class="text-xs text-slate-400">{{ $popup->slug }}</div>
                        </td>
                        <td class="p-3 text-xs text-slate-300">
                            <div>{{ optional($popup->starts_at)->format('d M Y H:i') ?? '—' }}</div>
                            <div class="text-slate-500">{{ optional($popup->ends_at)->format('d M Y H:i') ?? '—' }}</div>
                        </td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.popups.edit', $popup) }}" class="px-3 py-1 rounded-lg bg-slate-800 border border-slate-600 text-white text-xs">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-4 text-center text-slate-400" colspan="3">No scheduled popups.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $popups->links() }}</div>
</div>
@endsection
