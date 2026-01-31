@extends('layouts.pacerhub')

@section('title', 'Admin - Email Monitoring')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    EMAIL <span class="text-yellow-400">MONITORING</span>
                </h1>
                <p class="text-slate-400 mt-2">Monitoring queue email, rate limit instant, dan error prediksi.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.email-reports.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition text-sm font-bold">
                    Email Report
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Queue Email (DB Jobs)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-2 pr-4">Queue</th>
                            <th class="text-left py-2 pr-4">Total</th>
                            <th class="text-left py-2 pr-4">Oldest Available</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-200">
                        @foreach($queueStats as $row)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2 pr-4">{{ $row->queue }}</td>
                                <td class="py-2 pr-4 font-bold">{{ $row->total }}</td>
                                <td class="py-2 pr-4 text-slate-400">
                                    @if($row->oldest_available_at)
                                        {{ \Carbon\Carbon::createFromTimestamp($row->oldest_available_at)->format('Y-m-d H:i:s') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if(count($queueStats) === 0)
                            <tr><td colspan="3" class="py-4 text-center text-slate-500">Tidak ada job email pending.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Rate Limit Instant (5 email/menit)</h2>
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
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 relative z-10">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Email Ticket Gagal (Terbaru)</h2>
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

        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-white font-black text-lg">Error Prediksi (Terbaru)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-2 pr-4">Waktu</th>
                            <th class="text-left py-2 pr-4">Event</th>
                            <th class="text-left py-2 pr-4">Kategori</th>
                            <th class="text-left py-2 pr-4">Error</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-200">
                        @foreach($predictionErrors as $row)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-2 pr-4">{{ $row->event?->name ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $row->raceCategory?->name ?? '-' }}</td>
                                <td class="py-2 pr-4 text-slate-400">{{ \Illuminate\Support\Str::limit($row->error_message, 90) }}</td>
                            </tr>
                        @endforeach
                        @if($predictionErrors->count() === 0)
                            <tr><td colspan="4" class="py-4 text-center text-slate-500">Tidak ada error terbaru.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

