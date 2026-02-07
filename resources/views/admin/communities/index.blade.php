@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Community Management')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                COMMUNITY MANAGEMENT
            </h1>
            <p class="text-slate-400 mt-1">Kelola data master komunitas.</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-end w-full md:w-auto">
            <a href="{{ route('admin.communities.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark hover:bg-neon/90 transition-all font-bold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                New Community
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase text-slate-400 border-b border-slate-700 bg-slate-800/50">
                        <th class="px-6 py-4 font-bold tracking-wider">Name</th>
                        <th class="px-6 py-4 font-bold tracking-wider">City</th>
                        <th class="px-6 py-4 font-bold tracking-wider">PIC Info</th>
                        <th class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($communities as $community)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 text-white font-medium">
                                {{ $community->name }}
                                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $community->slug }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                {{ $community->city->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-300 text-sm">
                                <div>{{ $community->pic_name }}</div>
                                <div class="text-slate-500">{{ $community->pic_email }}</div>
                                <div class="text-slate-500">{{ $community->pic_phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('community.profile', $community->slug) }}" target="_blank" class="p-2 rounded-lg bg-teal-600/20 text-teal-400 hover:bg-teal-600 hover:text-white transition-all" title="View">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    <a href="{{ route('admin.communities.edit', $community) }}" class="p-2 rounded-lg bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <form action="{{ route('admin.communities.destroy', $community) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-rose-600/20 text-rose-400 hover:bg-rose-600 hover:text-white transition-all" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                No communities found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-700 bg-slate-800/30">
            {{ $communities->links() }}
        </div>
    </div>
</div>
@endsection
