@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Running Analysis Sessions')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17]">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black italic tracking-tighter text-white">
                    RUNNING ANALYSIS <span class="text-[#ccff00]">SESSIONS</span>
                </h1>
                <p class="text-slate-400 mt-2">Manage running form analysis sessions and participants.</p>
            </div>
            <button onclick="document.getElementById('createSessionModal').classList.remove('hidden')" class="px-6 py-3 bg-[#ccff00] text-black font-bold uppercase tracking-wider rounded-lg hover:bg-white transition-colors">
                <i class="fas fa-plus mr-2"></i> New Session
            </button>
        </div>

        @if(session('success'))
        <div class="bg-green-900/50 border border-green-500/50 text-green-400 px-4 py-3 rounded relative mb-6">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-[#0f172a] rounded-xl border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800 text-slate-300 text-sm uppercase tracking-wider">
                            <th class="px-6 py-4 font-semibold">Date & Name</th>
                            <th class="px-6 py-4 font-semibold">Location</th>
                            <th class="px-6 py-4 font-semibold">Runners</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Created By</th>
                            <th class="px-6 py-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300">
                        @forelse($sessions as $session)
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $session->name }}</div>
                                <div class="text-sm text-slate-400">{{ $session->session_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">{{ $session->location ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-800 text-slate-300 py-1 px-3 rounded-full text-xs font-semibold">
                                    {{ $session->runners_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($session->status === 'draft')
                                    <span class="bg-slate-500/20 text-slate-400 border border-slate-500/50 py-1 px-3 rounded text-xs font-semibold uppercase">Draft</span>
                                @elseif($session->status === 'active')
                                    <span class="bg-[#ccff00]/20 text-[#ccff00] border border-[#ccff00]/50 py-1 px-3 rounded text-xs font-semibold uppercase pulse-css">Active</span>
                                @elseif($session->status === 'completed')
                                    <span class="bg-green-500/20 text-green-400 border border-green-500/50 py-1 px-3 rounded text-xs font-semibold uppercase">Completed</span>
                                @else
                                    <span class="bg-slate-800 text-slate-500 border border-slate-700 py-1 px-3 rounded text-xs font-semibold uppercase">{{ $session->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $session->creator->name }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.running-analysis.sessions.show', $session) }}" class="text-[#ccff00] hover:text-white transition-colors">
                                    View <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <i class="fas fa-video text-4xl mb-3 opacity-20"></i>
                                <p>No analysis sessions found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sessions->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $sessions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Session Modal -->
<div id="createSessionModal" class="hidden fixed inset-0 z-[1100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-sm" onclick="document.getElementById('createSessionModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-[#0f172a] border border-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form action="{{ route('admin.running-analysis.sessions.store') }}" method="POST">
                @csrf
                <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white uppercase italic tracking-wider">New Session</h3>
                    <button type="button" class="text-slate-400 hover:text-white" onclick="document.getElementById('createSessionModal').classList.add('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Session Name</label>
                        <input type="text" name="name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-[#ccff00]" placeholder="e.g. GBK Sunday Long Run Analysis">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Location</label>
                        <input type="text" name="location" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-[#ccff00]" placeholder="e.g. Gelora Bung Karno">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Date</label>
                        <input type="date" name="session_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-[#ccff00]" style="color-scheme: dark;">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-900 border-t border-slate-800 flex justify-end">
                    <button type="button" class="px-4 py-2 text-slate-300 hover:text-white mr-4" onclick="document.getElementById('createSessionModal').classList.add('hidden')">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#ccff00] text-black font-bold uppercase rounded hover:bg-white transition-colors">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
