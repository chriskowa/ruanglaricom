@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'EO Dashboard')

@section('content')
<div id="eo-dashboard-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <p class="text-amber-400 font-mono text-xs tracking-wider uppercase mb-1">Event Operations Center</p>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">
                        {{ strtoupper(auth()->user()->name) }}
                    </h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-amber-300 bg-amber-400/10 border border-amber-400/25">
                        OFFICIAL EO
                    </span>
                    @if(!empty($tierBadgeText))
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tierBadgeClass }}">
                        {{ $tierBadgeText }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('eo.events.create') }}" class="px-4 py-2 rounded-md bg-amber-400 hover:bg-amber-300 text-slate-950 font-semibold text-xs flex items-center gap-1.5 transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Buat Event Baru
                </a>
                <a href="{{ route('eo.events.index') }}" class="px-4 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition-colors font-medium text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    Master Events
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 relative z-10">
        
        <!-- Wallet Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 hover:border-slate-700 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <div class="w-9 h-9 rounded-md bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <span class="text-[11px] font-mono text-slate-400 uppercase">Saldo Wallet</span>
            </div>
            <h3 class="text-2xl font-bold font-mono text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
            <div class="mt-1.5 text-xs text-slate-400">Siap ditarik</div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 hover:border-slate-700 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <div class="w-9 h-9 rounded-md bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[11px] font-mono text-slate-400 uppercase">Total Pendapatan</span>
            </div>
            <h3 class="text-2xl font-bold font-mono text-white">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
            <div class="mt-1.5 text-xs text-slate-400">Dari penjualan tiket lunas</div>
        </div>

        <!-- My Events -->
        <a href="{{ route('eo.events.index') }}" class="block bg-slate-900 border border-slate-800 rounded-lg p-5 hover:border-slate-700 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <div class="w-9 h-9 rounded-md bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="text-[11px] font-mono text-slate-400 uppercase">Total Event</span>
            </div>
            <h3 class="text-2xl font-bold font-mono text-white">{{ number_format($totalEvents) }}</h3>
            <div class="mt-1.5 text-xs text-slate-400 flex items-center justify-between">
                <span>Event diselenggarakan</span>
                <span class="text-slate-500 group-hover:text-amber-400 transition-colors text-xs font-medium">Buka &rarr;</span>
            </div>
        </a>

        <!-- Participants -->
        <a href="{{ route('eo.events.index') }}" class="block bg-slate-900 border border-slate-800 rounded-lg p-5 hover:border-slate-700 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <div class="w-9 h-9 rounded-md bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-[11px] font-mono text-slate-400 uppercase">Total Peserta</span>
            </div>
            <h3 class="text-2xl font-bold font-mono text-white">{{ number_format($totalParticipants) }}</h3>
            <div class="mt-1.5 text-xs text-slate-400 flex items-center justify-between">
                <span>Peserta terdaftar</span>
                <span class="text-slate-500 group-hover:text-amber-400 transition-colors text-xs font-medium">Buka &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Main Section: Event yang Dikelola & Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- List Event yang Dikelola -->
        @if($upcomingEvents->isNotEmpty())
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-3 border-b border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-white">Event yang Dikelola</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar event aktif dan terkini dalam operasional Anda</p>
                </div>
                <a href="{{ route('eo.events.index') }}" class="text-xs font-medium text-amber-400 hover:text-amber-300 transition-colors flex items-center gap-1 self-start sm:self-auto">
                    Lihat Semua ({{ $totalEvents }})
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>

            <div class="space-y-3">
                @foreach($upcomingEvents as $ev)
                <div class="p-4 rounded-lg bg-slate-950/60 border border-slate-800 hover:border-slate-700 transition-all flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <a href="{{ route('eo.events.show', $ev->id) }}" class="text-sm font-bold text-white hover:text-amber-400 transition-colors truncate max-w-sm sm:max-w-md">
                                {{ $ev->name }}
                            </a>
                            @php
                                $statusColors = [
                                    'published' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                    'draft' => 'bg-slate-800 text-slate-400 border border-slate-700/60',
                                    'completed' => 'bg-slate-800 text-slate-300 border border-slate-700/60',
                                    'cancelled' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                ];
                                $badgeClass = $statusColors[$ev->status] ?? 'bg-slate-800 text-slate-400 border border-slate-700/60';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider {{ $badgeClass }}">
                                {{ $ev->status }}
                            </span>
                        </div>

                        <div class="flex items-center gap-4 text-xs text-slate-400 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $ev->start_at ? \Carbon\Carbon::parse($ev->start_at)->translatedFormat('d M Y') : 'Jadwal belum ditentukan' }}
                            </span>

                            @if($ev->location_name)
                            <span class="inline-flex items-center gap-1.5 truncate max-w-[180px] text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ $ev->location_name }}
                            </span>
                            @endif

                            <span class="inline-flex items-center gap-1.5 text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <span class="font-mono text-white font-medium">{{ number_format($ev->participants_count) }}</span> peserta
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons: Public Link, Peserta, Edit, Kelola -->
                    <div class="flex flex-wrap items-center gap-2 self-start xl:self-center shrink-0">
                        <a href="{{ $ev->public_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 hover:text-white transition-colors border border-slate-700 flex items-center gap-1.5" title="Buka Halaman Publik Event">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            <span>Halaman Publik</span>
                        </a>

                        <a href="{{ route('eo.events.participants', $ev->id) }}" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 hover:text-white transition-colors border border-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <span>Peserta</span>
                        </a>

                        <a href="{{ route('eo.events.edit', $ev->id) }}" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 hover:text-white transition-colors border border-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span>Edit</span>
                        </a>

                        <a href="{{ route('eo.events.show', $ev->id) }}" class="px-3.5 py-1.5 rounded-md bg-amber-400 hover:bg-amber-300 text-xs font-semibold text-slate-950 transition-colors shadow-sm flex items-center gap-1.5">
                            <span>Kelola</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-lg p-6">
            <h2 class="text-base font-bold text-white mb-4">Event yang Dikelola</h2>
            <div class="h-60 flex items-center justify-center border border-dashed border-slate-800 rounded-md bg-slate-950/50">
                <div class="text-center">
                    <svg class="w-10 h-10 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <p class="text-slate-400 text-sm">Belum ada event yang dikelola</p>
                    <a href="{{ route('eo.events.create') }}" class="text-amber-400 hover:text-amber-300 text-xs font-semibold mt-2 inline-block">Buat event sekarang &rarr;</a>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h3 class="text-white font-bold text-sm mb-1.5">Operasional Event</h3>
                <p class="text-slate-400 text-xs mb-4 leading-relaxed">Kelola registrasi peserta, check-in, dan race results secara terpusat.</p>
                <a href="{{ route('eo.events.index') }}" class="block w-full py-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-semibold text-center rounded-md transition-colors text-xs shadow-sm">
                    Kelola Semua Event
                </a>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 mb-3 border-b border-slate-800 pb-2">Akses Cepat</h3>
                <div class="space-y-1.5">
                    <a href="{{ route('eo.events.create') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group text-xs">
                        <span class="text-slate-300 group-hover:text-white font-medium">Buat Event Baru</span>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </a>
                    <a href="{{ route('eo.events.index') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group text-xs">
                        <span class="text-slate-300 group-hover:text-white font-medium">Daftar Event & Peserta</span>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="{{ route('eo.email-campaigns.index') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group text-xs">
                        <span class="text-slate-300 group-hover:text-white font-medium">Email Campaigns</span>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18V8H3v8z" /></svg>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group text-xs">
                        <span class="text-slate-300 group-hover:text-white font-medium">Profil Penyelenggara</span>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
