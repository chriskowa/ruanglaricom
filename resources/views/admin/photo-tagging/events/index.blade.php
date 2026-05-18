@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Photo Tagging Events')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative font-sans">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-white italic tracking-tighter">Photo Tagging Events</h1>
            <p class="text-slate-400">Manage running events for photo tagging</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors">
            + Buat Event Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 text-green-400 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-card/50 border border-slate-700 rounded-2xl overflow-hidden">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-800/50 text-xs uppercase font-bold text-slate-400 border-b border-slate-700">
                <tr>
                    <th class="px-6 py-4">Event</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Photos</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                @forelse($events as $event)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                @if($event->getHeroImageUrl())
                                    <img src="{{ $event->getHeroImageUrl() }}" alt="Cover" class="w-12 h-12 rounded object-cover">
                                @else
                                    <div class="w-12 h-12 rounded bg-slate-800 flex items-center justify-center text-slate-500">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-white">{{ $event->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $event->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($event->status === 'published')
                                <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded-full border border-green-500/30">Published</span>
                            @elseif($event->status === 'archived')
                                <span class="px-2 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">Archived</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs rounded-full border border-yellow-500/30">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $event->event_date ? $event->event_date->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-4 font-mono">{{ $event->photo_tagging_photos_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.photo-tagging.photos.index', $event->id) }}" class="p-2 bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 rounded-lg transition-colors" title="Manage Photos">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </a>
                                <a href="{{ route('admin.events.edit', $event->id) }}" class="p-2 bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20 rounded-lg transition-colors" title="Edit Event">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            Belum ada event photo tagging. <a href="{{ route('admin.events.create') }}" class="text-blue-400 hover:underline">Buat sekarang</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())
            <div class="p-4 border-t border-slate-700">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
