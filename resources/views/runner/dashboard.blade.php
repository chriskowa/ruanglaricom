@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Runner Dashboard')

@section('content')
<div id="runner-dashboard-app" class="min-h-screen pt-20 pb-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">Good Morning, Athlete</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    {{ strtoupper(auth()->user()->name) }}
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('programs.realistic') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    New Program
                </a>
                <a href="{{ route('runner.calendar') }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Training Calendar
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 relative z-10">
        
        <!-- Wallet Card -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-neon/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-neon transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Wallet Balance</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-slate-400">Available for withdrawal</div>
        </div>

        <!-- Earnings Card -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Total Earnings</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($totalEarnings ?? 0, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-green-400 font-bold">+12% vs last month</div>
        </div>

        <!-- Active Programs -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-purple-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Active Programs</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ $activeEnrollments->count() }}</h3>
            <div class="mt-2 text-xs text-slate-400">Programs currently in progress</div>
        </div>

        <!-- Weekly Volume (Mockup) -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-cyan-400/50 transition-all group relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM9.8 8.9L7 23h2.1l1.8-8 2.1 2v6h2v-7.5l-2.1-2 .6-3C14.8 12 16.8 13 19 13v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.2L8 8v2h1.8z"/></svg>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-cyan-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Weekly Volume</span>
            </div>
            <h3 class="text-2xl font-bold text-white">42.5 <span class="text-sm font-normal text-slate-400">km</span></h3>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-4 overflow-hidden">
                <div class="bg-cyan-400 h-full rounded-full" style="width: 65%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Recent Activity / Programs -->
        <div class="lg:col-span-2 space-y-8">
            <div class="flex justify-between items-end">
                <h2 class="text-2xl font-bold text-white">Your Programs</h2>
                <a href="#" class="text-sm text-neon hover:underline">View All</a>
            </div>

            @if($activeEnrollments->count() > 0)
                <div class="space-y-4">
                    @foreach($activeEnrollments as $enrollment)
                    <div class="bg-card/30 border border-slate-700 rounded-xl p-4 flex flex-col md:flex-row gap-4 items-center hover:bg-slate-800/50 transition-colors cursor-pointer group">
                        <div class="w-full md:w-32 h-32 md:h-24 bg-slate-800 rounded-lg overflow-hidden shrink-0">
                            <!-- Placeholder Image -->
                            <img src="https://source.unsplash.com/random/200x200/?running" alt="Program" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-lg font-bold text-white group-hover:text-neon transition-colors">{{ $enrollment->program->title ?? 'Unknown Program' }}</h3>
                            <p class="text-sm text-slate-400 mb-2">Coach {{ $enrollment->program->coach->user->name ?? 'System' }}</p>
                            
                            <div class="flex items-center justify-center md:justify-start gap-4 text-xs font-mono text-slate-500">
                                <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Week 4/12</span>
                                <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> 85% Compliant</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <a href="#" class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm font-bold hover:bg-neon hover:text-dark transition-colors">Continue</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-card/30 border border-slate-700 border-dashed rounded-xl p-10 text-center">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="text-white font-bold mb-2">No Active Programs</h3>
                    <p class="text-slate-400 text-sm mb-6">Start your journey today with a personalized plan.</p>
                    <a href="{{ route('programs.realistic') }}" class="inline-block px-6 py-3 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90">Find a Program</a>
                </div>
            @endif
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Quick Access</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="#" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-purple-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Marketplace</span>
                    </a>
                    <a href="#" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-cyan-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">My Stats</span>
                    </a>
                    <a href="#" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-green-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Community</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-pink-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Settings</span>
                    </a>
                </div>
            </div>

            <!-- Weather Widget (Mockup) -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/20 rounded-full blur-xl"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-bold text-lg">Jakarta</h4>
                        <p class="text-sm opacity-80">Today, 06:30 AM</p>
                    </div>
                    <svg class="w-10 h-10 text-yellow-300 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <div class="mt-4">
                    <span class="text-4xl font-bold">28°C</span>
                    <p class="text-sm mt-1">Perfect for a morning run!</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
