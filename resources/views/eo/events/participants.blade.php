@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Participants - ' . $event->name)

@push('styles')
    <script>
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    ...tailwind.config.theme.extend.colors.neon,
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
<div id="eo-participants-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 relative z-10 print:hidden" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('eo.events.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Master Events</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Participants</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    EVENT <span class="text-yellow-400">PARTICIPANTS</span>
                </h1>
                <p class="text-slate-400 text-lg mt-1">{{ $event->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('eo.events.community.index', $event) }}" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Community
                </a>
                <button type="button" onclick="openAddParticipantModal()" class="px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-400 text-black font-bold flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Tambah Peserta
                </button>
                <button type="button" onclick="copyReportLink()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    Copy Report Link
                </button>
                <button type="button" onclick="sendBulkPendingReminder(this)" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-bold flex items-center gap-2 transition-colors" title="Kirim reminder ke peserta pending > 1 hari">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    Bulk Reminder
                </button>
                <a id="exportLink" href="{{ route('eo.events.participants.export', $event) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white font-bold flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Export CSV
                </a>
            </div>
        </div>
    </div>
    <input id="eoReportLink" type="hidden" value="{{ $reportLink }}">

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 relative z-10 print:hidden">
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-xl p-4">
            <p class="text-slate-400 text-xs font-bold uppercase mb-1">Total Registered</p>
            <h3 id="statTotalRegistered" class="text-2xl font-black text-white">{{ $participants->total() }}</h3>
        </div>
        <div class="bg-green-900/20 backdrop-blur border border-green-500/30 rounded-xl p-4">
            <p class="text-green-400 text-xs font-bold uppercase mb-1">Paid & Confirmed</p>
            <h3 id="statPaidConfirmed" class="text-2xl font-black text-white">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status', 'paid'); })->count() }}</h3>
        </div>
        <div class="bg-blue-900/20 backdrop-blur border border-blue-500/30 rounded-xl p-4">
            <p class="text-blue-400 text-xs font-bold uppercase mb-1">Race Pack Picked Up</p>
            <h3 id="statPickedUp" class="text-2xl font-black text-white">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id); })->where('is_picked_up', true)->count() }}</h3>
        </div>
        <div class="bg-yellow-900/20 backdrop-blur border border-yellow-500/30 rounded-xl p-4">
            <p class="text-yellow-400 text-xs font-bold uppercase mb-1">Pending Pickup</p>
            <h3 id="statPendingPickup" class="text-2xl font-black text-white">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status', 'paid'); })->where('is_picked_up', false)->count() }}</h3>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 relative z-10 print:hidden">
        <div class="bg-emerald-900/20 backdrop-blur border border-emerald-500/30 rounded-xl p-4">
            <p class="text-emerald-400 text-xs font-bold uppercase mb-1">Gross Revenue</p>
            <h3 class="text-2xl font-black text-white">IDR {{ number_format($financials['gross_revenue'], 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">Total Paid Transactions</p>
        </div>
        <div class="bg-red-900/20 backdrop-blur border border-red-500/30 rounded-xl p-4">
            <p class="text-red-400 text-xs font-bold uppercase mb-1">Platform Fee</p>
            <h3 class="text-2xl font-black text-white">IDR {{ number_format($financials['platform_fee'], 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">Total Admin Fees</p>
        </div>
        <div class="bg-indigo-900/20 backdrop-blur border border-indigo-500/30 rounded-xl p-4">
            <p class="text-indigo-400 text-xs font-bold uppercase mb-1">Net Revenue</p>
            <h3 class="text-2xl font-black text-white">IDR {{ number_format($financials['net_revenue'], 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">Total Earnings</p>
        </div>
    </div>

    <!-- Sales Report Card -->
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-8 relative z-10 print:border-0 print:p-0 print:mb-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 print:hidden">
            <div>
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-neon-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" /></svg>
                    Sales Performance Report
                </h3>
                <p class="text-slate-400 text-sm">Real-time slot usage and category breakdown</p>
            </div>
            <div class="flex flex-wrap gap-2">
                 <input type="date" id="reportStartDate" class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg p-2 focus:ring-neon-cyan focus:border-neon-cyan" placeholder="Start Date">
                 <input type="date" id="reportEndDate" class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg p-2 focus:ring-neon-cyan focus:border-neon-cyan" placeholder="End Date">
                 <select id="reportTicketType" class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg p-2 focus:ring-neon-cyan focus:border-neon-cyan">
                     <option value="">All Types</option>
                     <option value="early_bird">Early Bird</option>
                     <option value="regular">Regular</option>
                     <option value="late">Late</option>
                 </select>
                 <button onclick="refreshReport()" class="bg-blue-600 hover:bg-blue-500 text-white p-2 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                 <button onclick="exportReportCSV()" class="bg-green-600 hover:bg-green-500 text-white px-3 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg> Excel
                 </button>
                 <button onclick="window.print()" class="bg-slate-600 hover:bg-slate-500 text-white px-3 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg> PDF
                 </button>
            </div>
        </div>

        <!-- Print Header -->
        <div class="hidden print:block mb-6">
            <h1 class="text-2xl font-bold text-black">Event Report: {{ $event->name }}</h1>
            <p class="text-sm text-gray-600">Generated at: {{ now()->format('d M Y H:i') }}</p>
        </div>

        <div id="reportContent" class="grid grid-cols-1 md:grid-cols-2 gap-8 print:grid-cols-2 print:gap-4">
            <!-- Metrics -->
            <div class="space-y-4">
                 <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700 print:bg-white print:border-gray-300">
                     <h4 class="text-white text-sm font-bold mb-4 border-b border-slate-700 pb-2 print:text-black print:border-gray-300">Slot Utilization</h4>
                     <div class="flex justify-between items-center mb-2">
                         <span class="text-slate-400 text-sm print:text-gray-600">Total Slots</span>
                         <span class="text-white font-bold print:text-black" id="repTotalSlots">{{ $eventReport['total_slots'] }}</span>
                     </div>
                     <div class="flex justify-between items-center mb-2">
                         <span class="text-slate-400 text-sm print:text-gray-600">Sold Slots</span>
                         <span class="text-neon-green font-bold print:text-black" id="repSoldSlots">{{ $eventReport['sold_slots'] }}</span>
                     </div>
                     <div class="flex justify-between items-center mb-2">
                         <span class="text-slate-400 text-sm print:text-gray-600">Pending Slots</span>
                         <span class="text-yellow-400 font-bold print:text-black" id="repPendingSlots">{{ $eventReport['pending_slots'] ?? 0 }}</span>
                     </div>
                     
                     @php 
                        $used = ($eventReport['sold_slots'] + ($eventReport['pending_slots'] ?? 0));
                        $percent = $eventReport['is_unlimited'] ? 0 : ($eventReport['total_slots'] > 0 ? ($used / $eventReport['total_slots'] * 100) : 0);
                     @endphp
                     <div class="w-full bg-slate-700 rounded-full h-2.5 mb-1 print:bg-gray-200">
                        <div id="repProgressBar" class="bg-neon-green h-2.5 rounded-full print:bg-black" style="width: {{ $percent }}%"></div>
                     </div>
                     <div class="flex justify-between text-xs">
                         <span class="text-slate-500 print:text-gray-500">Usage</span>
                         <span class="text-white print:text-black" id="repProgressText">{{ round($percent, 1) }}%</span>
                     </div>
                     
                     <!-- Warning -->
                     <div id="repWarning" class="{{ $eventReport['show_warning'] ? '' : 'hidden' }} mt-3 bg-red-900/30 border border-red-500/50 p-2 rounded text-red-400 text-xs flex items-center gap-2 print:text-red-600 print:border-red-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Warning: Less than 10% slots remaining!
                     </div>
                 </div>
            </div>

            <!-- Breakdown -->
            <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700 print:bg-white print:border-gray-300">
                <h4 class="text-white text-sm font-bold mb-4 border-b border-slate-700 pb-2 print:text-black print:border-gray-300">Category Breakdown</h4>
                <div id="repBreakdown" class="space-y-3">
                    @foreach($eventReport['breakdown'] as $type => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 capitalize print:text-gray-600">{{ str_replace('_', ' ', $type) }}</span>
                        <div class="flex items-center gap-3">
                            <div class="w-24 bg-slate-700 rounded-full h-1.5 print:hidden">
                                <div class="bg-neon-cyan h-1.5 rounded-full" style="width: {{ $eventReport['percentages'][$type] ?? 0 }}%"></div>
                            </div>
                            <span class="text-white font-mono text-sm print:text-black">{{ $count }} ({{ $eventReport['percentages'][$type] ?? 0 }}%)</span>
                        </div>
                    </div>
                    @endforeach
                     <div class="flex items-center justify-between mt-4 pt-2 border-t border-slate-700 border-dashed print:border-gray-300">
                        <span class="text-slate-400 capitalize print:text-gray-600">Coupon Used</span>
                        <span class="text-yellow-400 font-mono text-sm print:text-black">{{ $eventReport['coupon_usage'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Table -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10 print:hidden">
        
        <!-- Filters -->
        <div class="p-4 border-b border-slate-700 bg-slate-900/30">
            <form id="filtersForm" method="GET" action="{{ route('eo.events.participants', $event) }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Payment Status</label>
                    <select name="payment_status" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="expired" {{ request('payment_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cod" {{ request('payment_status') == 'cod' ? 'selected' : '' }}>COD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Pickup Status</label>
                    <select name="is_picked_up" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="0" {{ request('is_picked_up') === '0' ? 'selected' : '' }}>Not Picked Up</option>
                        <option value="1" {{ request('is_picked_up') === '1' ? 'selected' : '' }}>Picked Up</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Gender</label>
                    <select name="gender" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Gender</option>
                        <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Category</label>
                    <select name="category_id" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Categories</option>
                        @foreach($event->categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Age Group</label>
                    <select name="age_group" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Groups</option>
                        <option value="Umum" {{ request('age_group') == 'Umum' ? 'selected' : '' }}>Umum (< 40)</option>
                        <option value="Master" {{ request('age_group') == 'Master' ? 'selected' : '' }}>Master (40-44)</option>
                        <option value="Master 45+" {{ request('age_group') == 'Master 45+' ? 'selected' : '' }}>Master 45+ (45-49)</option>
                        <option value="50+" {{ request('age_group') == '50+' ? 'selected' : '' }}>50+ (>= 50)</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, email, HP, BIB, Category, ID Card" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                </div>
                <div>
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-400 text-black font-bold py-2 px-4 rounded-lg transition-colors text-sm">
                        Filter
                    </button>
                    <a href="{{ route('eo.events.participants', $event) }}" class="ml-2 text-slate-400 hover:text-white text-sm">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-300">
                    <tr>
                        <th class="px-6 py-4 w-12">
                            <input type="checkbox" id="selectAll" class="rounded border-slate-600 bg-slate-800 text-yellow-500 focus:ring-yellow-500/50 cursor-pointer">
                        </th>
                        <th class="px-6 py-4">Participant</th>
                        <th class="px-6 py-4">ID Card</th>
                        <th class="px-6 py-4">PIC Info</th>
                        <th class="px-6 py-4">Jersey Size</th>
                        <th class="px-6 py-4">Category & BIB</th>
                        <th class="px-6 py-4">Age Group</th>                        
                        <th class="px-6 py-4">Payment</th>
                        <th class="px-6 py-4">Pickup Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="participantsTableBody" class="divide-y divide-slate-800">
                    @forelse($participants as $participant)
                    <tr class="hover:bg-slate-800/50 transition-colors cursor-pointer"
                        onclick="if(!event.target.closest('button') && !event.target.closest('a')) openDetailModalFromRow(this)"
                        data-json="{{ json_encode([
                            'id' => $participant->id,
                            'name' => $participant->name,
                            'gender' => $participant->gender,
                            'gender_label' => ucfirst($participant->gender),
                            'date_of_birth' => $participant->date_of_birth,
                            'email' => $participant->email,
                            'phone' => $participant->phone,
                            'id_card' => $participant->id_card,
                            'address' => $participant->address,
                            'city' => $participant->city,
                            'province' => $participant->province,
                            'postal_code' => $participant->postal_code,
                            'category' => $participant->category->name ?? '-',
                            'race_category_id' => $participant->race_category_id,
                            'bib_number' => $participant->bib_number,
                            'age_group' => $participant->getAgeGroup($event->start_at),
                            'jersey_size' => $participant->jersey_size,
                            'pic_name' => $participant->transaction->pic_data['name'] ?? '-',
                            'pic_phone' => $participant->transaction->pic_data['phone'] ?? '-',
                            'pic_email' => $participant->transaction->pic_data['email'] ?? '-',
                            'transaction_id' => $participant->transaction->id,
                            'transaction_date' => $participant->transaction->created_at ? $participant->transaction->created_at->format('Y-m-d H:i:s') : '-',
                            'payment_method' => $participant->transaction->payment_gateway ?? '-',
                            'payment_status' => $participant->transaction->payment_status ?? 'pending',
                            'is_picked_up' => $participant->is_picked_up,
                            'picked_up_by' => $participant->picked_up_by,
                            'coupon_code' => $participant->transaction->coupon->code ?? null,
                            'addons' => $participant->addons,
                        ]) }}">
                        <td class="px-6 py-4" onclick="event.stopPropagation()">
                            <input type="checkbox" class="participant-checkbox rounded border-slate-600 bg-slate-800 text-yellow-500 focus:ring-yellow-500/50 cursor-pointer" value="{{ $participant->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $participant->name }}</div>
                            <div class="text-xs text-slate-500 mb-1">
                                {{ ucfirst($participant->gender) }} • Reg: {{ $participant->created_at->format('d M') }}
                            </div>
                            <div class="text-xs text-slate-400">{{ $participant->email }}</div>
                            <div class="text-xs text-slate-400">{{ $participant->phone }}</div>
                        </td>
                        <td class="px-6 py-4 text-white font-mono text-xs">
                            {{ $participant->id_card ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @php $pic = $participant->transaction->pic_data ?? []; @endphp
                            <div class="text-sm text-white">{{ $pic['name'] ?? '-' }}</div>
                            <div class="text-xs text-slate-400">{{ $pic['phone'] ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-bold bg-slate-800 border border-slate-600 text-white">
                                {{ $participant->jersey_size ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-200 w-fit">
                                    {{ $participant->category->name ?? '-' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-900/30 text-yellow-400 border border-yellow-500/30 w-fit">
                                    BIB: {{ $participant->bib_number ?? 'N/A' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-200">
                                {{ $participant->getAgeGroup($event->start_at) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php $status = $participant->transaction->payment_status ?? 'pending'; @endphp
                            <div class="relative inline-block">
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                        data-dropdown="payment-{{ $participant->transaction->id }}"
                                        data-status="{{ $status }}"
                                        data-id="{{ $participant->transaction->id }}"
                                        onclick="togglePaymentDropdown(this)">
                                    <span class="status-label">{{ ucfirst($status) }}</span>
                                    <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div id="payment-{{ $participant->transaction->id }}" class="absolute mt-2 w-36 bg-slate-900 border border-slate-700 rounded-lg shadow-xl hidden z-20">
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'pending', this)">Pending</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'paid', this)">Paid</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'failed', this)">Failed</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'expired', this)">Expired</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'cod', this)">COD</button>
                                </div>
                            </div>
                            @if($status == 'pending' && $participant->transaction->created_at->diffInDays(now()) >= 1)
                                <div class="text-xs text-red-400 font-bold mt-1">
                                    Pending > 1 Hari
                                </div>
                            @endif
                            @if($participant->transaction->coupon)
                                <div class="mt-2 text-xs text-yellow-400 flex items-center gap-1" title="Coupon Used">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    <span class="font-mono font-bold">{{ $participant->transaction->coupon->code }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($status == 'paid')
                                <button onclick="openPickupModal({{ $participant->id }}, '{{ addslashes($participant->name) }}', {{ $participant->is_picked_up ? 'true' : 'false' }}, '{{ addslashes($participant->picked_up_by ?? '') }}')" class="hover:opacity-80 transition-opacity text-left">
                                    @if($participant->is_picked_up)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-500/30">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            Picked Up
                                        </span>
                                        <div class="text-xs text-slate-500 mt-1">By: {{ Str::limit($participant->picked_up_by, 15) }}</div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">
                                            Not Picked Up
                                        </span>
                                    @endif
                                </button>
                            @else
                                <span class="text-xs text-slate-500 italic">Payment required</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($status == 'pending' && $participant->transaction->created_at->diffInDays(now()) >= 1)
                                    <button onclick="sendPendingReminder(event, {{ $participant->transaction->id }})" class="p-2 bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded-lg transition-colors" title="Kirim Reminder Pembayaran">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @endif
                                <a href="mailto:{{ $participant->email }}" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Email">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $participant->phone)) }}" target="_blank" class="p-2 rounded-lg bg-slate-800 text-green-400 hover:bg-slate-700 hover:text-green-300 transition-colors" title="WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.536 0 1.52 1.115 2.988 1.264 3.186.149.198 2.19 3.361 5.27 4.69 2.151.928 2.988.94 3.518.865.592-.084 1.758-.717 2.006-1.41.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.381a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </a>
                                @if($status != 'paid')
                                <button onclick="deleteParticipant({{ $participant->id }})" class="p-2 rounded-lg bg-slate-800 text-red-400 hover:bg-red-900/50 hover:text-red-300 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <p class="text-slate-500">No participants found matching your criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($participants->hasPages())
        <div id="paginationContainer" class="px-6 py-4 border-t border-slate-800 bg-slate-900/30">
            {{ $participants->links() }}
        </div>
        @endif
        
        <!-- Bulk Action Toolbar -->
        <div id="bulkActionToolbar" class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl p-4 z-50 flex items-center gap-4 hidden transition-all duration-300">
            <div class="text-white font-bold">
                <span id="selectedCount">0</span> Selected
            </div>
            <div class="h-6 w-px bg-slate-600"></div>
            <button onclick="bulkDelete()" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-bold flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Delete
            </button>
            <button onclick="bulkRemind()" class="px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-500 text-white font-bold flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                Remind Pending
            </button>
        </div>
    </div>
</div>

<!-- Add Participant Modal -->
<div id="addParticipantModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeAddParticipantModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <form method="POST" action="{{ route('eo.events.participants.store', $event) }}">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white">Tambah Peserta Manual</h3>
                            <button type="button" onclick="closeAddParticipantModal()" class="text-slate-400 hover:text-white">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        @if ($errors->any())
                            <div class="mb-4 rounded-lg border border-red-500/40 bg-red-900/20 p-3 text-sm text-red-200">
                                <div class="font-bold mb-1">Periksa kembali input:</div>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Nama Lengkap</label>
                                <input name="name" value="{{ old('name') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="Nama peserta">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Gender</label>
                                <select name="gender" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                                    <option value="">-</option>
                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                                <input name="email" type="email" value="{{ old('email') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="email@domain.com">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Nomor Telepon</label>
                                <input name="phone" value="{{ old('phone') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="08xxxxxxxxxx">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">ID Card</label>
                                <input name="id_card" value="{{ old('id_card') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="Nomor identitas">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-400 mb-1">Alamat</label>
                                <textarea name="address" required maxlength="500" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="Alamat peserta">{{ old('address') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Kategori</label>
                                <select name="category_id" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                                    <option value="">Pilih kategori</option>
                                    @foreach($event->categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) old('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-medium text-slate-400">BIB Number</label>
                                    <div class="flex gap-2 text-xs">
                                        <button type="button" onclick="document.getElementById('add_bib_number').value='{{ $nextBibNumber }}'" class="text-yellow-500 hover:text-yellow-400 cursor-pointer">Auto</button>
                                        <button type="button" onclick="document.getElementById('add_bib_number').value=''" class="text-slate-500 hover:text-slate-300 cursor-pointer">Clear</button>
                                    </div>
                                </div>
                                <input id="add_bib_number" name="bib_number" value="{{ old('bib_number', $nextBibNumber) }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none font-mono text-yellow-400" placeholder="Auto (or manual)">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Kupon (Opsional)</label>
                                <select name="coupon_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                                    <option value="">-- Pilih Kupon --</option>
                                    @foreach($coupons as $coupon)
                                        <option value="{{ $coupon->id }}" {{ old('coupon_id') == $coupon->id ? 'selected' : '' }}>
                                            {{ $coupon->code }} ({{ $coupon->type == 'percent' ? (float)$coupon->value . '%' : 'Rp ' . number_format($coupon->value, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Tanggal Lahir</label>
                                <input name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Target Time (HH:MM:SS)</label>
                                <input name="target_time" value="{{ old('target_time') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="01:30:00">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Jersey Size</label>
                                <input name="jersey_size" value="{{ old('jersey_size') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="S / M / L / XL">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Emergency Contact Name</label>
                                <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-400 mb-1">Emergency Contact Number</label>
                                <input name="emergency_contact_number" value="{{ old('emergency_contact_number') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="md:col-span-2 space-y-3 border-t border-slate-700 pt-3 mt-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="use_queue" name="use_queue" type="checkbox" value="1" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-yellow-500 focus:ring-yellow-500 focus:ring-offset-slate-800">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="use_queue" class="font-medium text-white">Kirim Notifikasi via Queue (Background Process)</label>
                                        <p class="text-slate-400 text-xs">Centang jika ingin proses simpan lebih cepat. Email akan dikirim di latar belakang (pastikan worker aktif).</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="send_whatsapp" name="send_whatsapp" type="checkbox" value="1" checked class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-yellow-500 focus:ring-yellow-500 focus:ring-offset-slate-800">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="send_whatsapp" class="font-medium text-white">Kirim Notifikasi WhatsApp</label>
                                        <p class="text-slate-400 text-xs">Uncheck jika tidak ingin mengirim notifikasi WhatsApp ke peserta.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeAddParticipantModal()" class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-400 text-black text-sm font-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pickup Modal -->
<div id="pickupModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form id="pickupForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Race Pack Pickup</h3>
                        <input type="hidden" name="participant_id" id="participant_id">
                        
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Participant Name</label>
                            <input type="text" id="participant_name_display" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Status</label>
                            <select name="is_picked_up" id="pickup_status" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                                <option value="0">Not Picked Up</option>
                                <option value="1">Picked Up</option>
                            </select>
                        </div>

                        <div id="picked_by_container" class="mb-4 hidden">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Picked Up By (Name)</label>
                            <input type="text" name="picked_up_by" id="picked_up_by" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="e.g. Self or Delegate Name">
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closePickupModal()" class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-bold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-400 text-black text-sm font-bold">Save Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 text-left shadow-xl transition-all w-full max-w-2xl">
                <!-- Header -->
                <div class="bg-slate-900/50 px-6 py-4 border-b border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Participant Details</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" id="btn_edit_participant" onclick="toggleEditMode(true)" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit
                        </button>
                        <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-white ml-2">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
                
                <!-- Notification Area -->
                <div id="edit_notification" class="hidden px-6 pt-4"></div>

                <div class="p-6">
                    <form id="editParticipantForm">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Info -->
                            <div>
                                <h4 class="text-sm font-bold text-yellow-500 uppercase tracking-wider mb-3">Personal Info</h4>
                                <div class="space-y-3">
                                    <!-- Name -->
                                    <div>
                                        <div class="text-xs text-slate-500">Full Name</div>
                                        <div class="view-mode text-white font-medium" id="dm_name"></div>
                                        <input type="text" name="name" id="edit_name" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none placeholder-slate-500">
                                    </div>
                                    
                                    <!-- ID Card (Read Only) -->
                                    <div>
                                        <div class="text-xs text-slate-500">ID Card</div>
                                        <div class="text-white" id="dm_id_card"></div>
                                    </div>

                                    <!-- Gender -->
                                    <div>
                                        <div class="text-xs text-slate-500">Gender</div>
                                        <div class="view-mode text-white capitalize" id="dm_gender"></div>
                                        <select name="gender" id="edit_gender" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                    
                                    <!-- DOB -->
                                    <div>
                                        <div class="text-xs text-slate-500">Date of Birth</div>
                                        <div class="view-mode text-white" id="dm_dob"></div>
                                        <input type="date" name="date_of_birth" id="edit_date_of_birth" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none">
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <div class="text-xs text-slate-500">Email</div>
                                        <div class="view-mode text-white" id="dm_email"></div>
                                        <input type="email" name="email" id="edit_email" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none placeholder-slate-500">
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <div class="text-xs text-slate-500">Phone</div>
                                        <div class="view-mode text-white" id="dm_phone"></div>
                                        <input type="text" name="phone" id="edit_phone" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none placeholder-slate-500">
                                    </div>

                                    <!-- Address Info -->
                                    <div class="pt-2 border-t border-slate-700/50 mt-2">
                                        <div class="text-xs text-slate-500 mb-1">Full Address</div>
                                        
                                        <!-- Address -->
                                        <div class="view-mode text-white text-sm mb-1" id="dm_address"></div>
                                        <textarea name="address" id="edit_address" rows="2" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none mb-2 placeholder-slate-500" placeholder="Address"></textarea>
                                        
                                        <!-- City & Province -->
                                        <div class="view-mode text-xs text-slate-400">
                                            <span id="dm_city"></span>, <span id="dm_province"></span> <span id="dm_postal_code"></span>
                                        </div>
                                        <div class="edit-mode hidden grid grid-cols-2 gap-2">
                                            <input type="text" name="city" id="edit_city" placeholder="City" class="w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none placeholder-slate-500">
                                            <input type="text" name="province" id="edit_province" placeholder="Province" class="w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none placeholder-slate-500">
                                            <input type="text" name="postal_code" id="edit_postal_code" placeholder="Postal Code" class="w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none col-span-2 placeholder-slate-500">
                                        </div>
                                    </div>
                                    
                                    <!-- Attendance Status -->
                                    <div class="pt-2 border-t border-slate-700/50 mt-2">
                                        <div class="text-xs text-slate-500">Attendance (Race Pack)</div>
                                        <div class="view-mode" id="dm_attendance_badge"></div>
                                        <select name="is_picked_up" id="edit_is_picked_up" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none">
                                            <option value="0">Not Picked Up</option>
                                            <option value="1">Picked Up</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Race Info (Read Only) -->
                            <div>
                                <h4 class="text-sm font-bold text-neon-cyan uppercase tracking-wider mb-3">Race Info</h4>
                                <div class="space-y-3">
                                    <!-- Category -->
                                    <div>
                                        <div class="text-xs text-slate-500">Category</div>
                                        <div class="view-mode text-white font-bold" id="dm_category"></div>
                                        <select name="race_category_id" id="edit_race_category_id" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none">
                                            @foreach($event->categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- BIB -->
                                    <div>
                                        <div class="text-xs text-slate-500">BIB Number</div>
                                        <div class="view-mode text-white font-mono text-lg text-yellow-400" id="dm_bib"></div>
                                        <input type="text" name="bib_number" id="edit_bib_number" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none font-mono">
                                    </div>
                                    <!-- Jersey -->
                                    <div>
                                        <div class="text-xs text-slate-500">Jersey Size</div>
                                        <div class="view-mode text-white inline-flex items-center justify-center w-8 h-8 rounded bg-slate-700 border border-slate-600 font-bold" id="dm_jersey"></div>
                                        <input type="text" name="jersey_size" id="edit_jersey_size" class="edit-mode hidden w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-white text-sm focus:border-blue-500 focus:outline-none">
                                    </div>
                                    <!-- Age Group -->
                                    <div><div class="text-xs text-slate-500">Age Group</div><div class="text-white" id="dm_age_group"></div></div>
                                </div>

                                <h4 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-3 mt-6">PIC Info</h4>
                                <div class="space-y-3">
                                    <div><div class="text-xs text-slate-500">PIC Name</div><div class="text-white" id="dm_pic_name"></div></div>
                                    <div><div class="text-xs text-slate-500">PIC Phone</div><div class="text-white" id="dm_pic_phone"></div></div>
                                    <div><div class="text-xs text-slate-500">PIC Email</div><div class="text-white" id="dm_pic_email"></div></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-700">
                            <h4 class="text-sm font-bold text-green-400 uppercase tracking-wider mb-3">Transaction Info</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <div><div class="text-xs text-slate-500">Transaction Date</div><div class="text-white" id="dm_trx_date"></div></div>
                                    <div><div class="text-xs text-slate-500">Payment Method</div><div class="text-white uppercase" id="dm_payment_method"></div></div>
                                    <div><div class="text-xs text-slate-500">Coupon</div><div class="text-white font-mono" id="dm_coupon"></div></div>
                                </div>
                                <div class="space-y-3">
                                    <div><div class="text-xs text-slate-500">Status</div><div id="dm_payment_status"></div></div>
                                    <div><div class="text-xs text-slate-500">Addons</div><div id="dm_addons" class="space-y-1"></div></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Footer -->
                <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-2">
                    <div class="view-mode flex gap-2">
                        <button type="button" id="btn_resend_email" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold transition-colors">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Resend Email
                        </button>
                        <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold transition-colors">Close</button>
                    </div>
                    <div class="edit-mode hidden flex gap-2">
                         <button type="button" onclick="cancelEdit()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold transition-colors">Cancel</button>
                         <button type="button" onclick="saveParticipant()" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-bold transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Save Changes
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        var form = document.getElementById('filtersForm');
        var tbody = document.getElementById('participantsTableBody');
        var statTotal = document.getElementById('statTotalRegistered');
        var statPaid = document.getElementById('statPaidConfirmed');
        var statPicked = document.getElementById('statPickedUp');
        var statPending = document.getElementById('statPendingPickup');
        var exportLink = document.getElementById('exportLink');
        var pagination = document.getElementById('paginationContainer');
        var baseUrl = form.getAttribute('action');
        var eventId = {{ $event->id }};

        function qs(obj) {
            var params = new URLSearchParams();
            Object.keys(obj).forEach(function(k){
                if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') params.append(k, obj[k]);
            });
            return params.toString();
        }

        function serializeForm() {
            var data = {};
            var els = form.querySelectorAll('input, select');
            els.forEach(function(el){
                if (el.name) data[el.name] = el.value;
            });
            return data;
        }

        function phoneToWa(phone) {
            var digits = String(phone || '').replace(/[^0-9]/g, '');
            return digits.replace(/^0/, '62');
        }

        function renderRows(items) {
            if (!items || items.length === 0) {
                return '<tr><td colspan="9" class="px-6 py-12 text-center"><p class="text-slate-500">No participants found matching your criteria.</p></td></tr>';
            }
            var html = '';
            items.forEach(function(p){
                var status = p.payment_status || 'pending';
                var pickedBadge = '';
                if (status === 'paid') {
                    var badgeContent = '';
                    if (p.is_picked_up) {
                        badgeContent = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-500/30"><svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Picked Up</span><div class="text-xs text-slate-500 mt-1">By: ' + (p.picked_up_by ? p.picked_up_by.substring(0, 15) : '') + '</div>';
                    } else {
                        badgeContent = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Not Picked Up</span>';
                    }
                    var safeName = (p.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    var safePickedBy = (p.picked_up_by || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    pickedBadge = '<button onclick="openPickupModal('+ p.id +', \''+ safeName +'\', '+ (p.is_picked_up ? 'true' : 'false') +', \''+ safePickedBy +'\')" class="hover:opacity-80 transition-opacity text-left">' + badgeContent + '</button>';
                } else {
                    pickedBadge = '<span class="text-xs text-slate-500 italic">Payment required</span>';
                }
                var paymentBtn = '<button type="button" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border" data-dropdown="payment-'+ p.transaction_id +'" data-status="'+ status +'" data-id="'+ p.transaction_id +'" onclick="togglePaymentDropdown(this)"><span class="status-label">'+ (status.charAt(0).toUpperCase()+status.slice(1)) +'</span><svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>';
                var paymentDd = '<div id="payment-'+ p.transaction_id +'" class="absolute mt-2 w-36 bg-slate-900 border border-slate-700 rounded-lg shadow-xl hidden z-20">'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'pending\', this)">Pending</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'paid\', this)">Paid</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'failed\', this)">Failed</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'expired\', this)">Expired</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'cod\', this)">COD</button>'+
                '</div>';
                
                var couponHtml = '';
                if (p.coupon_code) {
                    couponHtml = '<div class="mt-2 text-xs text-yellow-400 flex items-center gap-1" title="Coupon Used">' +
                        '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>' +
                        '<span class="font-mono font-bold">' + p.coupon_code + '</span>' +
                        '</div>';
                }

                var genderLabel = p.gender ? (p.gender.charAt(0).toUpperCase() + p.gender.slice(1)) : '-';
                var regDate = p.created_at ? p.created_at.split(' ').slice(0, 2).join(' ') : '-';
                
                var dataJson = JSON.stringify({
                    id: p.id,
                    name: p.name,
                    gender: p.gender, // Raw value for edit
                    gender_label: genderLabel, // Label for display
                    date_of_birth: p.date_of_birth,
                    email: p.email,
                    phone: p.phone,
                    id_card: p.id_card,
                    address: p.address,
                    city: p.city,
                    province: p.province,
                    postal_code: p.postal_code,
                    category: p.category,
                    race_category_id: p.race_category_id,
                    bib_number: p.bib_number,
                    age_group: p.age_group,
                    jersey_size: p.jersey_size,
                    pic_name: p.pic_name,
                    pic_phone: p.pic_phone,
                    pic_email: p.pic_email,
                    transaction_date: p.transaction_date,
                    payment_method: p.payment_method,
                    payment_status: p.payment_status,
                    is_picked_up: p.is_picked_up,
                    picked_up_by: p.picked_up_by,
                    coupon_code: p.coupon_code,
                    addons: p.addons
                }).replace(/'/g, "&#39;");

                html += '<tr class="hover:bg-slate-800/50 transition-colors cursor-pointer" onclick="if(!event.target.closest(\'button\') && !event.target.closest(\'a\') && !event.target.closest(\'.no-click\')) openDetailModalFromRow(this)" data-json=\''+ dataJson +'\'>'+
                    '<td class="px-6 py-4"><div class="font-medium text-white">'+ p.name +'</div><div class="text-xs text-slate-500 mb-1">'+ genderLabel +' • Reg: '+ regDate +'</div><div class="text-xs text-slate-400">'+ (p.email || '') +'</div><div class="text-xs text-slate-400">'+ (p.phone || '') +'</div></td>'+
                    '<td class="px-6 py-4 text-white font-mono text-xs">'+ (p.id_card || '-') +'</td>'+
                    '<td class="px-6 py-4"><div class="text-sm text-white">'+ (p.pic_name || '-') +'</div><div class="text-xs text-slate-400">'+ (p.pic_phone || '-') +'</div></td>'+
                    '<td class="px-6 py-4"><span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-bold bg-slate-800 border border-slate-600 text-white">'+ (p.jersey_size || '-') +'</span></td>'+
                    '<td class="px-6 py-4"><div class="flex flex-col gap-1"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-200 w-fit">'+ (p.category || '-') +'</span><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-900/30 text-yellow-400 border border-yellow-500/30 w-fit">BIB: '+ (p.bib_number || 'N/A') +'</span></div></td>'+
                    '<td class="px-6 py-4"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-200">'+ (p.age_group || '-') +'</span></td>'+
                    '<td class="px-6 py-4"><div class="relative inline-block">'+ paymentBtn + paymentDd +'</div>'+ couponHtml +'</td>'+
                    '<td class="px-6 py-4">'+ pickedBadge +'</td>'+
                    '<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2">'+
                        '<a href="mailto:'+ (p.email || '') +'" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Email"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2 2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></a>'+
                        '<a href="https://wa.me/'+ phoneToWa(p.phone) +'" target="_blank" class="p-2 rounded-lg bg-slate-800 text-green-400 hover:bg-slate-700 hover:text-green-300 transition-colors" title="WhatsApp"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.536 0 1.52 1.115 2.988 1.264 3.186.149.198 2.19 3.361 5.27 4.69 2.151.928 2.988.94 3.518.865.592-.084 1.758-.717 2.006-1.41.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.381a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></a>'+
                        (status !== 'paid' ? '<button onclick="deleteParticipant('+ p.id +')" class="p-2 rounded-lg bg-slate-800 text-red-400 hover:bg-red-900/50 hover:text-red-300 transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' : '') +
                    '</div></td>'+
                '</tr>';
            });
            return html;
        }

        function updateStats(stats) {
            if (statTotal) statTotal.textContent = stats.total_registered;
            if (statPaid) statPaid.textContent = stats.paid_confirmed;
            if (statPicked) statPicked.textContent = stats.race_pack_picked_up;
            if (statPending) statPending.textContent = stats.pending_pickup;
        }

        function updateExportLink(data) {
            var q = qs(serializeForm());
            if (exportLink) exportLink.href = '{{ route('eo.events.participants.export', $event) }}' + (q ? ('?' + q) : '');
        }

        var debounceTimer;
        function debounce(fn, delay) {
            return function(){
                var args = arguments, ctx = this;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function(){ fn.apply(ctx, args); }, delay || 300);
            }
        }

        function fetchParticipants() {
            var q = qs(serializeForm());
            var url = baseUrl + (q ? ('?' + q) : '');
            fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (!res || !res.success) { alert('Gagal memuat data'); return; }
                tbody.innerHTML = renderRows(res.data || []);
                document.querySelectorAll('button[data-dropdown]').forEach(function(btn){
                    setStatusButtonStyle(btn, btn.dataset.status || 'pending');
                });
                updateStats(res.stats || {});
                updateExportLink();
            })
            .catch(function(){ alert('Terjadi kesalahan'); });
        }

        form.addEventListener('submit', function(e){ e.preventDefault(); fetchParticipants(); });
        form.querySelectorAll('select').forEach(function(sel){
            sel.addEventListener('change', fetchParticipants);
        });
        var searchInput = form.querySelector('input[name="search"]');
        if (searchInput) searchInput.addEventListener('input', debounce(fetchParticipants, 400));

        if (pagination) {
            pagination.addEventListener('click', function(e){
                var a = e.target.closest('a');
                if (!a) return;
                e.preventDefault();
                var url = a.getAttribute('href');
                if (!url) return;
                fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if (!res || !res.success) return;
                    tbody.innerHTML = renderRows(res.data || []);
                    document.querySelectorAll('button[data-dropdown]').forEach(function(btn){
                        setStatusButtonStyle(btn, btn.dataset.status || 'pending');
                    });
                    updateStats(res.stats || {});
                    updateExportLink();
                });
            });
        }

        // Report Functions
        window.refreshReport = function() {
            var start = document.getElementById('reportStartDate').value;
            var end = document.getElementById('reportEndDate').value;
            var type = document.getElementById('reportTicketType').value;
            
            var btn = document.querySelector('button[onclick="refreshReport()"]');
            var originalContent = btn.innerHTML;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6.366 2.634l-.707.707M20 12h-1m-2.634 6.366l-.707-.707M12 20v-1m-6.366-2.634l.707-.707M4 12H3m2.634-6.366l.707-.707" /></svg>';
            btn.disabled = true;

            var params = new URLSearchParams({
                action: 'get_report',
                report_start_date: start,
                report_end_date: end,
                report_ticket_type: type
            });

            fetch(window.location.pathname + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                if (data.success && data.report) {
                    updateReportUI(data.report);
                }
            })
            .catch(err => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                console.error(err);
            });
        };

        function updateReportUI(report) {
            document.getElementById('repTotalSlots').innerText = report.total_slots;
            document.getElementById('repSoldSlots').innerText = report.sold_slots;
            if(document.getElementById('repPendingSlots')) {
                document.getElementById('repPendingSlots').innerText = report.pending_slots || 0;
            }
            
            var used = report.sold_slots + (report.pending_slots || 0);
            var percent = report.is_unlimited ? 0 : (report.total_slots > 0 ? (used / report.total_slots * 100) : 0);
            document.getElementById('repProgressBar').style.width = percent + '%';
            document.getElementById('repProgressText').innerText = percent.toFixed(1) + '%';
            
            var warningEl = document.getElementById('repWarning');
            if (report.show_warning) warningEl.classList.remove('hidden');
            else warningEl.classList.add('hidden');

            var breakdownHtml = '';
            for (var type in report.breakdown) {
                 var count = report.breakdown[type];
                 var p = report.percentages[type] || 0;
                 var label = type.replace('_', ' ');
                 breakdownHtml += `
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 capitalize print:text-gray-600">${label}</span>
                        <div class="flex items-center gap-3">
                            <div class="w-24 bg-slate-700 rounded-full h-1.5 print:hidden">
                                <div class="bg-neon-cyan h-1.5 rounded-full" style="width: ${p}%"></div>
                            </div>
                            <span class="text-white font-mono text-sm print:text-black">${count} (${p}%)</span>
                        </div>
                    </div>`;
            }
            breakdownHtml += `
                 <div class="flex items-center justify-between mt-4 pt-2 border-t border-slate-700 border-dashed print:border-gray-300">
                    <span class="text-slate-400 capitalize print:text-gray-600">Coupon Used</span>
                    <span class="text-yellow-400 font-mono text-sm print:text-black">${report.coupon_usage}</span>
                </div>`;
            
            document.getElementById('repBreakdown').innerHTML = breakdownHtml;
        }

        window.exportReportCSV = function() {
            var rows = [
                ['Event Report', '{{ $event->name }}'],
                ['Generated At', new Date().toLocaleString()],
                ['', ''],
                ['Total Slots', document.getElementById('repTotalSlots').innerText],
                ['Sold Slots', document.getElementById('repSoldSlots').innerText],
                ['Pending Slots', document.getElementById('repPendingSlots') ? document.getElementById('repPendingSlots').innerText : '0'],
                ['Usage %', document.getElementById('repProgressText').innerText],
                ['', ''],
                ['Category Breakdown', '', '']
            ];
            
            var breakdownContainer = document.getElementById('repBreakdown');
            var items = breakdownContainer.querySelectorAll('.flex.items-center.justify-between');
            items.forEach(item => {
                var label = item.querySelector('span:first-child').innerText;
                var valText = item.querySelector('span:last-child').innerText;
                rows.push([label, valText]);
            });
            
            let csvContent = "data:text/csv;charset=utf-8," 
                + rows.map(e => e.join(",")).join("\n");
                
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "event_report_{{ $event->slug }}.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    })();
    
    // Global variable to store current participant data for edit/cancel
    var currentParticipantData = null;

    function setStatusButtonStyle(btn, status) {
        var map = {
            paid: {bg:'bg-green-900/30', text:'text-green-400', border:'border-green-500/30'},
            pending: {bg:'bg-yellow-900/30', text:'text-yellow-400', border:'border-yellow-500/30'},
            failed: {bg:'bg-red-900/30', text:'text-red-400', border:'border-red-500/30'},
            expired: {bg:'bg-red-900/30', text:'text-red-400', border:'border-red-500/30'},
            cod: {bg:'bg-blue-900/30', text:'text-blue-400', border:'border-blue-500/30'}
        };
        var cls = map[status] || map['pending'];
        btn.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border ' + cls.bg + ' ' + cls.text + ' ' + cls.border;
        var label = btn.querySelector('.status-label');
        if (label) label.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        btn.dataset.status = status;
    }

    document.querySelectorAll('button[data-dropdown]').forEach(function(btn){
        setStatusButtonStyle(btn, btn.dataset.status || 'pending');
    });

    function togglePaymentDropdown(btn) {
        var id = btn.getAttribute('data-dropdown');
        var dd = document.getElementById(id);
        if (!dd) return;
        document.querySelectorAll('[id^="payment-"]').forEach(function(el){ if(el !== dd) el.classList.add('hidden'); });
        dd.classList.toggle('hidden');
        document.addEventListener('click', function onDoc(e){
            if (!dd.contains(e.target) && e.target !== btn) {
                dd.classList.add('hidden');
                document.removeEventListener('click', onDoc);
            }
        });
    }

    function updatePaymentStatus(url, status, el) {
        var dd = el.parentElement;
        var btn = dd.previousElementSibling;
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';
        el.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
            body: JSON.stringify({ payment_status: status })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res && res.success) {
                setStatusButtonStyle(btn, status);
                dd.classList.add('hidden');
            } else {
                alert(res.message || 'Gagal mengupdate status');
            }
        })
        .catch(function(){ alert('Terjadi kesalahan'); })
        .finally(function(){ el.disabled = false; });
    }

    function openPickupModal(id, name, isPickedUp, pickedBy) {
        document.getElementById('pickupModal').classList.remove('hidden');
        document.getElementById('participant_id').value = id;
        document.getElementById('participant_name_display').value = name;
        document.getElementById('pickup_status').value = isPickedUp ? '1' : '0';
        document.getElementById('picked_up_by').value = pickedBy || '';
        
        const form = document.getElementById('pickupForm');
        form.action = `{{ url('/eo/events/' . $event->id . '/participants') }}/${id}/status`;
        
        togglePickedByField();
    }

    function closePickupModal() {
        document.getElementById('pickupModal').classList.add('hidden');
    }

        function populateEditForm() {
            
        if (!currentParticipantData) return;
        
        var d = currentParticipantData;
        console.log('Populating edit form with:', d);
        
        setValue('edit_name', d.name);
        setValue('edit_email', d.email);
        setValue('edit_phone', d.phone);
        setValue('edit_gender', d.gender || 'male');
        
        // Handle Date Format for Input (YYYY-MM-DD)
        var dob = d.date_of_birth;
        if (dob && dob.length > 10) dob = dob.substring(0, 10);
        setValue('edit_date_of_birth', dob);
        
        setValue('edit_address', d.address);
        setValue('edit_city', d.city);
        setValue('edit_province', d.province);
        setValue('edit_postal_code', d.postal_code);
        setValue('edit_is_picked_up', d.is_picked_up ? '1' : '0');
        
        // Explicitly set race category and log it
        var catId = d.race_category_id;
        
        console.log('Setting race category to:', catId);
        setValue('edit_race_category_id', catId);
        
        setValue('edit_bib_number', d.bib_number);
        setValue('edit_jersey_size', d.jersey_size);
        
        // Handle attendance badge update if needed or specific logic
        togglePickedByField();
    }

    function setValue(id, val) {
        var el = document.getElementById(id);
        if(el) el.value = (val === null || val === undefined) ? '' : val;
    }

    function cancelEdit() {
        toggleEditMode(false);
        if (currentParticipantData) {
            populateEditForm();
        }
    }

    function toggleEditMode(show) {
        var viewModes = document.querySelectorAll('.view-mode');
        var editModes = document.querySelectorAll('.edit-mode');
        
        if (show) {
            populateEditForm(); // Ensure data is loaded
            viewModes.forEach(el => el.classList.add('hidden'));
            editModes.forEach(el => el.classList.remove('hidden'));
            document.getElementById('btn_edit_participant').classList.add('hidden');
        } else {
            viewModes.forEach(el => el.classList.remove('hidden'));
            editModes.forEach(el => el.classList.add('hidden'));
            document.getElementById('btn_edit_participant').classList.remove('hidden');
            document.getElementById('edit_notification').innerHTML = '';
            document.getElementById('edit_notification').classList.add('hidden');
        }
    }

    function saveParticipant() {
        var id = document.getElementById('edit_id').value;
        if (!id) return;

        var notification = document.getElementById('edit_notification');
        notification.classList.add('hidden');
        notification.innerHTML = '';

        // Collect Form Data
        var formData = {
            name: document.getElementById('edit_name').value.trim(),
            email: document.getElementById('edit_email').value.trim(),
            phone: document.getElementById('edit_phone').value.trim(),
            gender: document.getElementById('edit_gender').value,
            date_of_birth: document.getElementById('edit_date_of_birth').value,
            address: document.getElementById('edit_address').value.trim(),
            city: document.getElementById('edit_city').value.trim(),
            province: document.getElementById('edit_province').value.trim(),
            postal_code: document.getElementById('edit_postal_code').value.trim(),
            is_picked_up: document.getElementById('edit_is_picked_up').value,
            race_category_id: document.getElementById('edit_race_category_id').value,
            bib_number: document.getElementById('edit_bib_number').value.trim(),
            jersey_size: document.getElementById('edit_jersey_size').value.trim()
        };

        // Client-side Validation
        var errors = [];
        if (!formData.name) errors.push('Nama wajib diisi');
        if (!formData.email) errors.push('Email wajib diisi');
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) errors.push('Format email tidak valid');
        
        if (!formData.phone) errors.push('Nomor telepon wajib diisi');
        else if (formData.phone.length < 8) errors.push('Nomor telepon minimal 8 digit');
        
        if (!formData.gender) errors.push('Jenis kelamin wajib dipilih');
        if (!formData.race_category_id) errors.push('Kategori lomba wajib dipilih');

        if (errors.length > 0) {
            notification.className = 'px-6 pt-4 text-sm text-red-400 font-bold';
            notification.innerHTML = '<div class="bg-red-900/30 border border-red-500/30 p-3 rounded-lg"><ul class="list-disc list-inside">' + 
                errors.map(e => '<li>' + e + '</li>').join('') + 
                '</ul></div>';
            notification.classList.remove('hidden');
            return;
        }

        var btn = document.querySelector('button[onclick="saveParticipant()"]');
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6.366 2.634l-.707.707M20 12h-1m-2.634 6.366l-.707-.707M12 20v-1m-6.366-2.634l.707-.707M4 12H3m2.634-6.366l.707-.707" /></svg> Saving...';

        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';
        var url = '{{ url("eo/events/" . $event->id . "/participants") }}/' + id;

        fetch(url, {
            method: 'PUT',
            headers: { 
                'X-CSRF-TOKEN': csrf, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) {
                // Update Current Data for Cancel/Restore
                if (currentParticipantData) {
                    Object.assign(currentParticipantData, {
                        name: res.data.name,
                        email: res.data.email,
                        phone: res.data.phone,
                        gender: res.data.gender,
                        date_of_birth: res.data.date_of_birth,
                        address: res.data.address,
                        city: res.data.city,
                        province: res.data.province,
                        postal_code: res.data.postal_code,
                        race_category_id: res.data.race_category_id,
                        category: res.data.category_name,
                        bib_number: res.data.bib_number,
                        jersey_size: res.data.jersey_size,
                        age_group: res.data.age_group,
                        is_picked_up: res.data.is_picked_up,
                        picked_up_by: res.data.picked_up_by
                    });
                }

                // Update View Mode Data
                document.getElementById('dm_name').textContent = res.data.name;
                document.getElementById('dm_email').textContent = res.data.email;
                document.getElementById('dm_phone').textContent = res.data.phone;
                document.getElementById('dm_gender').textContent = res.data.gender ? (res.data.gender.charAt(0).toUpperCase() + res.data.gender.slice(1)) : '-';
                document.getElementById('dm_dob').textContent = res.data.date_of_birth || '-';
                document.getElementById('dm_address').textContent = res.data.address || '-';
                document.getElementById('dm_city').textContent = res.data.city || '-';
                document.getElementById('dm_province').textContent = res.data.province || '-';
                document.getElementById('dm_postal_code').textContent = res.data.postal_code || '';
                
                // Race Info Updates
                document.getElementById('dm_category').textContent = res.data.category_name;
                document.getElementById('dm_bib').textContent = res.data.bib_number || '-';
                document.getElementById('dm_jersey').textContent = res.data.jersey_size || '-';
                document.getElementById('dm_age_group').textContent = res.data.age_group || '-';
                
                // Update Attendance Badge
                var attendanceBadge = document.getElementById('dm_attendance_badge');
                if (res.data.is_picked_up) {
                     attendanceBadge.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-500/30"><svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Picked Up</span>';
                } else {
                     attendanceBadge.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Not Picked Up</span>';
                }

                // Show Success Message
                notification.className = 'px-6 pt-4 text-sm text-green-400 font-bold';
                notification.innerHTML = '<div class="bg-green-900/30 border border-green-500/30 p-3 rounded-lg flex items-center gap-2"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> ' + res.message + '</div>';
                notification.classList.remove('hidden');

                // Switch back to view mode after delay
                setTimeout(function(){
                    toggleEditMode(false);
                    // Reload table data
                    fetchParticipants();
                }, 1500);

            } else {
                // Show Error Message
                notification.className = 'px-6 pt-4 text-sm text-red-400 font-bold';
                notification.innerHTML = '<div class="bg-red-900/30 border border-red-500/30 p-3 rounded-lg">' + (res.message || 'Gagal menyimpan perubahan') + '</div>';
                notification.classList.remove('hidden');
            }
        })
        .catch(function(err){ 
            console.error(err);
            notification.className = 'px-6 pt-4 text-sm text-red-400 font-bold';
            notification.innerHTML = '<div class="bg-red-900/30 border border-red-500/30 p-3 rounded-lg">Terjadi kesalahan sistem</div>';
            notification.classList.remove('hidden');
        })
        .finally(function(){
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function openDetailModalFromRow(tr) {
        var data = JSON.parse(tr.dataset.json);
        currentParticipantData = data; // Store for cancel restore
        
        // Reset Edit Mode
        toggleEditMode(false);
        document.getElementById('edit_notification').classList.add('hidden');
        document.getElementById('edit_id').value = data.id;

        // Populate Personal Info
        document.getElementById('dm_name').textContent = data.name;
        
        document.getElementById('dm_id_card').textContent = data.id_card || '-';
        
        document.getElementById('dm_gender').textContent = data.gender_label || (data.gender ? (data.gender.charAt(0).toUpperCase() + data.gender.slice(1)) : '-');
        
        document.getElementById('dm_dob').textContent = data.date_of_birth || '-';
        
        document.getElementById('dm_email').textContent = data.email;
        
        document.getElementById('dm_phone').textContent = data.phone;
        
        document.getElementById('dm_age_group').textContent = data.age_group || '-';

        // Populate Address Info
        document.getElementById('dm_address').textContent = data.address || '-';
        
        document.getElementById('dm_city').textContent = data.city || '-';
        
        document.getElementById('dm_province').textContent = data.province || '-';
        
        document.getElementById('dm_postal_code').textContent = data.postal_code || '';

        // Attendance / Picked Up Status
        var isPickedUp = data.is_picked_up ? '1' : '0';
        
        var attendanceBadge = document.getElementById('dm_attendance_badge');
        if (data.is_picked_up) {
             attendanceBadge.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-500/30"><svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Picked Up</span>';
        } else {
             attendanceBadge.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Not Picked Up</span>';
        }

        // Populate Race Info
        document.getElementById('dm_category').textContent = data.category;
        
        document.getElementById('dm_bib').textContent = data.bib_number || '-';
        
        document.getElementById('dm_jersey').textContent = data.jersey_size || '-';
        
        // Populate Inputs (Initial)
        populateEditForm();
        
        // Populate PIC Info
        document.getElementById('dm_pic_name').textContent = data.pic_name;
        document.getElementById('dm_pic_phone').textContent = data.pic_phone;
        document.getElementById('dm_pic_email').textContent = data.pic_email;

        // Populate Transaction Info
        document.getElementById('dm_trx_date').textContent = data.transaction_date;
        document.getElementById('dm_payment_method').textContent = data.payment_method;
        
        var couponEl = document.getElementById('dm_coupon');
        if (data.coupon_code) {
            couponEl.innerHTML = '<span class="text-yellow-400 font-bold">' + data.coupon_code + '</span>';
            couponEl.parentElement.classList.remove('hidden');
        } else {
            couponEl.textContent = '-';
            couponEl.parentElement.classList.add('hidden');
        }
        
        // Payment Status Badge
        var status = data.payment_status;
        var badge = document.getElementById('dm_payment_status');
        badge.className = 'px-2 py-1 rounded-full text-xs font-bold border';
        if(status == 'paid') {
            badge.classList.add('bg-green-900/30', 'text-green-400', 'border-green-500/30');
        } else if(status == 'pending') {
            badge.classList.add('bg-yellow-900/30', 'text-yellow-400', 'border-yellow-500/30');
        } else {
            badge.classList.add('bg-red-900/30', 'text-red-400', 'border-red-500/30');
        }
        badge.textContent = status.toUpperCase();

        // Populate Addons
        var addonsContainer = document.getElementById('dm_addons');
        if (data.addons && data.addons.length > 0) {
            addonsContainer.innerHTML = data.addons.map(function(a){
                return '<div class="flex justify-between text-sm"><span class="text-slate-400">'+a.name+'</span><span class="text-white">'+(a.value||'-')+'</span></div>';
            }).join('');
        } else {
            addonsContainer.innerHTML = '<div class="text-slate-500 text-sm italic">No additional data</div>';
        }

        // Set participant ID for resend email button
        var btnResend = document.getElementById('btn_resend_email');
        if(btnResend) {
            btnResend.dataset.participantId = data.id;
            btnResend.disabled = false;
            btnResend.innerHTML = '<svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg> Resend Email';
        }

        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    function togglePickedByField() {
        const status = document.getElementById('pickup_status').value;
        const container = document.getElementById('picked_by_container');
        const input = document.getElementById('picked_up_by');
        
        if (status === '1') {
            container.classList.remove('hidden');
            input.required = true;
        } else {
            container.classList.add('hidden');
            input.required = false;
        }
    }

    document.getElementById('pickup_status').addEventListener('change', togglePickedByField);

    function deleteParticipant(id) {
        if (!confirm('Apakah anda yakin ingin menghapus peserta ini?')) return;
        
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';
        var url = '{{ url('eo/events/' . $event->id . '/participants') }}/' + id;
        
        fetch(url, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': csrf, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) {
                alert(res.message);
                var btn = document.querySelector('#filtersForm button[type="submit"]');
                if(btn) btn.click();
                else window.location.reload();
            } else {
                alert(res.message || 'Gagal menghapus peserta');
            }
        })
        .catch(function(){ alert('Terjadi kesalahan saat menghapus'); });
    }

    window.openAddParticipantModal = function () {
        var modal = document.getElementById('addParticipantModal');
        if (modal) modal.classList.remove('hidden');
    }

    window.closeAddParticipantModal = function () {
        var modal = document.getElementById('addParticipantModal');
        if (modal) modal.classList.add('hidden');
    }

    window.copyReportLink = function () {
        var input = document.getElementById('eoReportLink');
        var link = input ? input.value : '';
        if (!link) {
            alert('Report link tidak tersedia');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link)
                .then(function () { alert('Report link berhasil dicopy'); })
                .catch(function () { fallbackCopy(link); });
            return;
        }

        fallbackCopy(link);
    }

    window.sendPendingReminder = function(e, transactionId) {
        e.preventDefault();
        e.stopPropagation();

        if (!confirm('Kirim reminder pembayaran ke peserta ini via Email & WhatsApp?')) {
            return;
        }

        var btn = e.currentTarget;
        var originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        fetch(`{{ url('eo/events/' . $event->id . '/transactions') }}/${transactionId}/remind-pending`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert('Gagal mengirim reminder: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengirim reminder.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }

    function fallbackCopy(text) {
        var temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        temp.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
            alert('Report link berhasil dicopy');
        } catch (e) {
            alert('Gagal copy. Link: ' + text);
        }
        document.body.removeChild(temp);
    }

    window.sendBulkPendingReminder = function(btn) {
        if (!confirm('Kirim reminder untuk SEMUA peserta dengan pembayaran pending > 1 hari?\n\nSistem hanya akan mengirim ke peserta yang belum menerima reminder dalam 24 jam terakhir.')) {
            return;
        }

        var originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        fetch(`{{ route('eo.events.remind-pending.bulk', $event) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Gagal mengirim bulk reminder: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengirim bulk reminder.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }

    if ({{ $errors->any() ? 'true' : 'false' }}) {
        window.openAddParticipantModal();
    }

    var btnResendEmail = document.getElementById('btn_resend_email');
    if(btnResendEmail) {
        btnResendEmail.addEventListener('click', function() {
            var btn = this;
            var participantId = btn.dataset.participantId;
            if(!participantId) return;

            if(!confirm('Kirim ulang email konfirmasi ke peserta ini?')) return;

            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';

            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';
            var url = '{{ route("eo.events.participants.resend-email", $event) }}';
            
            fetch(url, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': csrf, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ participant_id: participantId })
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    alert(res.message);
                } else {
                    alert(res.message || 'Gagal mengirim email');
                }
            })
            .catch(function(err){ 
                console.error(err);
                alert('Terjadi kesalahan saat mengirim email'); 
            })
            .finally(function(){
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg> Resend Email';
            });
        });
    }

    // Bulk Actions
    const selectAllCheckbox = document.getElementById('selectAll');
    const participantCheckboxes = document.querySelectorAll('.participant-checkbox');
    const bulkToolbar = document.getElementById('bulkActionToolbar');
    const selectedCountSpan = document.getElementById('selectedCount');

    function updateBulkToolbar() {
        const selected = document.querySelectorAll('.participant-checkbox:checked');
        const count = selected.length;
        if(selectedCountSpan) selectedCountSpan.textContent = count;
        
        if (bulkToolbar) {
            if (count > 0) {
                bulkToolbar.classList.remove('hidden');
            } else {
                bulkToolbar.classList.add('hidden');
            }
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checked = this.checked;
            participantCheckboxes.forEach(cb => {
                cb.checked = checked;
            });
            updateBulkToolbar();
        });
    }

    participantCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkToolbar);
        cb.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    window.bulkDelete = function() {
        const selected = Array.from(document.querySelectorAll('.participant-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return;

        if (!confirm(`Apakah anda yakin ingin menghapus ${selected.length} peserta terpilih?`)) return;

        fetch(`{{ route('eo.events.participants.bulk-delete', $event) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ participant_ids: selected })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Gagal menghapus peserta');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menghapus');
        });
    };

    window.bulkRemind = function() {
        const selected = Array.from(document.querySelectorAll('.participant-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return;

        if (!confirm(`Kirim reminder untuk ${selected.length} peserta terpilih?\n\nHanya peserta dengan status 'pending' yang akan diproses.`)) return;

        fetch(`{{ route('eo.events.remind-pending.bulk', $event) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ participant_ids: selected })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Gagal mengirim reminder');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat mengirim reminder');
        });
    };
</script>
@endsection
