@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Admin Dashboard')

@section('content')
<div id="admin-dashboard-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-red-500 font-mono text-sm tracking-widest uppercase mb-1">System Administration</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    {{ strtoupper(auth()->user()->name) }}
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-red-500 hover:text-red-500 transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Manage Users
                </a>
                <a href="#" class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white font-black hover:scale-105 transition-all shadow-lg shadow-red-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    System Health
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 relative z-10">
        
        <!-- Total Users -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-blue-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Total Users</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\User::count() }}</h3>
            <div class="mt-2 text-xs text-blue-400 font-bold">Active Accounts</div>
        </div>

        <!-- Total Programs -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-purple-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Programs</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\Program::count() }}</h3>
            <div class="mt-2 text-xs text-purple-400 font-bold">Training Plans</div>
        </div>

        <!-- Total Events -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-yellow-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-yellow-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Events</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\Event::count() }}</h3>
            <div class="mt-2 text-xs text-yellow-400 font-bold">Races Organized</div>
        </div>

        <!-- System Status -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">System Status</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Online</h3>
            <div class="mt-2 text-xs text-green-400 font-bold">All services operational</div>
        </div>
    </div>

    <!-- Admin Tools -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Quick Actions -->
        <div class="lg:col-span-2 bg-card/30 border border-slate-700 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-6">Administrative Tools</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}" class="p-4 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-300">User Management</span>
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="p-4 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group text-center">
                    <div class="w-12 h-12 rounded-full bg-green-500/10 text-green-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-300">Transactions</span>
                </a>
                <a href="{{ route('admin.races.index') }}" class="p-4 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group text-center">
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 text-purple-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-300">Race Master</span>
                </a>
            </div>
        </div>

        <!-- System Logs -->
        <div class="space-y-6">
            <div class="bg-card/50 border border-slate-700 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Recent Logs</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-300">System backup completed successfully.</p>
                            <p class="text-[10px] text-slate-500">2 mins ago</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-yellow-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-300">High traffic detected on API endpoint.</p>
                            <p class="text-[10px] text-slate-500">15 mins ago</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-300">New user registration: John Doe</p>
                            <p class="text-[10px] text-slate-500">1 hour ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
