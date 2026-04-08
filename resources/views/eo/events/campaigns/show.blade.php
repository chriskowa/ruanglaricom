@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Email Campaign - ' . $campaign->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Detail Campaign</h1>
            <p class="text-sm text-slate-400 mt-1">Status dan riwayat pengiriman email untuk campaign ini.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('eo.events.campaigns.index', $event) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Informasi Campaign</h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500 font-medium">Nama</dt>
                        <dd class="mt-1 text-slate-900 font-bold">{{ $campaign->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Tipe</dt>
                        <dd class="mt-1 text-slate-900 capitalize">{{ $campaign->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-slate-100 text-slate-800">
                                {{ $campaign->status }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Jadwal (Send At)</dt>
                        <dd class="mt-1 text-slate-900">{{ $campaign->send_at ? $campaign->send_at->format('d M Y H:i:s') : 'Segera' }}</dd>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <dt class="text-slate-500 font-medium">Progress</dt>
                        <dd class="mt-1 flex items-center justify-between text-slate-900">
                            <span>Terkirim: <span class="font-bold">{{ $campaign->sent_count }}</span></span>
                            <span>Target: <span class="font-bold">{{ $campaign->target_count }}</span></span>
                        </dd>
                        <div class="w-full bg-slate-100 rounded-full h-2 mt-2">
                            @php $percent = $campaign->target_count > 0 ? ($campaign->sent_count / $campaign->target_count) * 100 : 0; @endphp
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </dl>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Pengaturan Konten</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500 font-medium">Subjek Email</dt>
                        <dd class="mt-1 text-slate-900">{{ $campaign->subject }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Template Preset</dt>
                        <dd class="mt-1 text-slate-900 capitalize">{{ $campaign->preset_template }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Log Pengiriman</h3>
                </div>
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Penerima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sent At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($deliveries as $d)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $d->to_name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $d->to_email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $colors = [
                                            'pending' => 'bg-slate-100 text-slate-800',
                                            'queued' => 'bg-yellow-100 text-yellow-800',
                                            'sent' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                        ];
                                        $c = $colors[$d->status] ?? 'bg-slate-100 text-slate-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $c }}">
                                        {{ $d->status }}
                                    </span>
                                    @if($d->status === 'failed' && $d->error_message)
                                        <div class="text-[10px] text-red-500 mt-1 max-w-xs truncate" title="{{ $d->error_message }}">Error</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $d->sent_at ? $d->sent_at->format('d M Y H:i:s') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-500 text-sm">
                                    Belum ada log pengiriman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $deliveries->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
