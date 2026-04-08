@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Email Campaigns - ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Email Campaigns: {{ $event->name }}</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola email broadcast dan reminder terjadwal untuk peserta event ini.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('eo.events.show', $event) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
                Kembali ke Event
            </a>
            <a href="{{ route('eo.events.campaigns.create', $event) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Buat Campaign Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Campaign</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipe & Jadwal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($campaigns as $c)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-900">{{ $c->name }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $c->subject }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-slate-900 capitalize">{{ $c->type }}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                @if($c->type === 'absolute' && $c->send_at)
                                    {{ $c->send_at->format('d M Y H:i') }}
                                @elseif($c->type === 'instant')
                                    Immediate
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badgeColors = [
                                    'draft' => 'bg-slate-100 text-slate-800',
                                    'scheduled' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                ];
                                $color = $badgeColors[$c->status] ?? 'bg-slate-100 text-slate-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $color }}">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-900">{{ $c->sent_count }} / {{ $c->target_count }}</div>
                            <div class="text-xs text-slate-500 mt-1">Terkirim</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('eo.events.campaigns.show', [$event, $c]) }}" class="text-blue-600 hover:text-blue-900">Detail & Log</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">
                            Belum ada email campaign.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
</div>
@endsection
