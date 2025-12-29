@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Coach Dashboard')

@push('styles')
    <script>
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                }
            }
        }
    </script>
@endpush

@section('content')
<div id="coach-dashboard-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-cyan-400 font-mono text-sm tracking-widest uppercase mb-1">Coach Command Center</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    {{ strtoupper(auth()->user()->name) }}
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('coach.programs.create') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-cyan-400 hover:text-cyan-400 transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Create Program
                </a>
                <a href="{{ route('coach.athletes.index') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-purple-600 text-white font-black hover:scale-105 transition-all shadow-lg shadow-cyan-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    Manage Athletes
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 relative z-10">
        
        <!-- Wallet Card -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-cyan-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-cyan-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Wallet Balance</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-slate-400">Ready for withdrawal</div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Lifetime Revenue</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-green-400 font-bold">Consistent Growth</div>
        </div>

        <!-- My Programs -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-purple-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Created Programs</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ auth()->user()->programs()->count() }}</h3>
            <div class="mt-2 text-xs text-slate-400">Programs in marketplace</div>
        </div>

        <!-- Total Students -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-yellow-400/50 transition-all group relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-yellow-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Total Students</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\ProgramEnrollment::whereHas('program', function($q) { $q->where('coach_id', auth()->id()); })->count() }}</h3>
            <div class="mt-2 text-xs text-slate-400">Active enrollments</div>
        </div>
    </div>

    <!-- Quick Links & Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Performance Overview (Mockup) -->
        <div class="lg:col-span-2 bg-card/30 border border-slate-700 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-6">Performance Overview</h3>
            <div class="h-64 flex items-center justify-center border-2 border-dashed border-slate-700 rounded-xl bg-slate-800/50">
                <div class="text-center">
                    <svg class="w-12 h-12 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                    <p class="text-slate-500 text-sm">Revenue Chart will appear here</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-purple-900/50 to-slate-900 border border-purple-500/30 rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl"></div>
                <h3 class="text-white font-bold text-lg mb-2">Coach Hub</h3>
                <p class="text-slate-400 text-sm mb-6">Manage your athletes, review training logs, and provide feedback.</p>
                <a href="{{ route('coach.athletes.index') }}" class="block w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold text-center rounded-xl transition-colors shadow-lg shadow-purple-500/25">
                    Open Hub
                </a>
            </div>

            <div class="bg-card/50 border border-slate-700 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Shortcuts</h3>
                <div class="space-y-2">
                    <a href="{{ route('coach.programs.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group">
                        <span class="text-slate-300 group-hover:text-white text-sm font-medium">My Programs List</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="{{ route('coach.withdrawals.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group">
                        <span class="text-slate-300 group-hover:text-white text-sm font-medium">Withdrawal History</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group">
                        <span class="text-slate-300 group-hover:text-white text-sm font-medium">Edit Profile</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
