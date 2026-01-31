@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Email Monitoring')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Email Monitoring</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    EMAIL <span class="text-yellow-400">MONITORING</span>
                </h1>
                <p class="text-slate-400 mt-2">Monitoring rate limit dan error email untuk event Anda.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('eo.email-reports.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition text-sm font-bold">
                    Email Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Rate Limit (Reservasi per Menit)</h2>
            <p class="text-slate-500 text-xs mt-1">Menampilkan window menit sekitar sekarang, untuk event milik Anda.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-2 pr-4">Minute</th>
                            <th class="text-left py-2 pr-4">Event</th>
                            <th class="text-left py-2 pr-4">Reserved</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-200">
                        @foreach($rateStats as $row)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $row->minute_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-2 pr-4">{{ $row->event?->name ?? ('Event #' . $row->event_id) }}</td>
                                <td class="py-2 pr-4 font-bold">{{ (int) $row->reserved_emails }}</td>
                            </tr>
                        @endforeach
                        @if($rateStats->count() === 0)
                            <tr><td colspan="3" class="py-4 text-center text-slate-500">Belum ada reservasi rate limit.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Email Gagal (Terbaru)</h2>
            <p class="text-slate-500 text-xs mt-1">Ticket/WA dan blast akan muncul di sini ketika gagal.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-2 pr-4">Waktu</th>
                            <th class="text-left py-2 pr-4">Event</th>
                            <th class="text-left py-2 pr-4">To</th>
                            <th class="text-left py-2 pr-4">Channel</th>
                            <th class="text-left py-2 pr-4">Error</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-200">
                        @foreach($recentFailures as $row)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-2 pr-4">{{ $row->event?->name ?? ('Event #' . $row->event_id) }}</td>
                                <td class="py-2 pr-4">{{ $row->to }}</td>
                                <td class="py-2 pr-4">{{ $row->channel }}</td>
                                <td class="py-2 pr-4 text-slate-400">{{ \Illuminate\Support\Str::limit($row->error_message, 80) }}</td>
                            </tr>
                        @endforeach
                        @if($recentFailures->count() === 0)
                            <tr><td colspan="5" class="py-4 text-center text-slate-500">Tidak ada error terbaru.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 relative z-10">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Email Laporan (Terbaru)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-2 pr-4">Waktu</th>
                            <th class="text-left py-2 pr-4">Event</th>
                            <th class="text-left py-2 pr-4">To</th>
                            <th class="text-left py-2 pr-4">Status</th>
                            <th class="text-left py-2 pr-4">Attempts</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-200">
                        @foreach($reportDeliveries as $row)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-2 pr-4">{{ $row->event?->name ?? ('Event #' . $row->event_id) }}</td>
                                <td class="py-2 pr-4">{{ $row->to_email }}</td>
                                <td class="py-2 pr-4 font-bold">{{ strtoupper($row->status) }}</td>
                                <td class="py-2 pr-4">{{ (int) $row->attempts }}</td>
                            </tr>
                        @endforeach
                        @if($reportDeliveries->count() === 0)
                            <tr><td colspan="5" class="py-4 text-center text-slate-500">Belum ada pengiriman email laporan.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

