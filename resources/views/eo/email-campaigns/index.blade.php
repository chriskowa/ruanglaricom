@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Email Campaigns')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">EO Panel</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">EMAIL CAMPAIGNS</h1>
                <div class="text-slate-400 text-sm mt-1">Daftar semua campaign email dari seluruh event yang kamu kelola.</div>
            </div>

            <div class="w-full md:w-auto">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                    <div class="min-w-0 sm:min-w-[320px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pilih Event</label>
                        <select id="campaignEventSelect" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none">
                            <option value="">Pilih event untuk membuat campaign</option>
                            @foreach(($events ?? []) as $ev)
                                <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-transparent uppercase tracking-widest mb-1">Action</label>
                        <a id="btnCreateCampaign" href="#" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-3 rounded-xl bg-neon text-dark font-black text-sm uppercase tracking-widest opacity-50 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Add Campaign
                        </a>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2">Campaign dibuat per-event. Pilih event dulu, lalu klik Add Campaign.</div>
            </div>
        </div>
    </div>

    <div class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-900/60 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($campaigns as $c)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ $c->event ? $c->event->name : '-' }}</div>
                                <div class="text-xs text-slate-400 mt-1">{{ optional($c->created_at)->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ $c->name }}</div>
                                <div class="text-xs text-slate-400 mt-1">{{ $c->subject }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-200 capitalize">{{ $c->type }}</div>
                                <div class="text-xs text-slate-500 mt-1">
                                    @if($c->type === 'absolute' && $c->send_at)
                                        {{ $c->send_at->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php($color = 'bg-slate-700/60 text-slate-200')
                                @if($c->status === 'scheduled')
                                    @php($color = 'bg-yellow-900/40 text-yellow-200')
                                @elseif($c->status === 'processing')
                                    @php($color = 'bg-blue-900/40 text-blue-200')
                                @elseif($c->status === 'completed')
                                    @php($color = 'bg-green-900/40 text-green-200')
                                @elseif($c->status === 'failed')
                                    @php($color = 'bg-red-900/40 text-red-200')
                                @endif
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $color }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white font-bold">{{ $c->sent_count }} / {{ $c->target_count }}</div>
                                <div class="text-xs text-slate-500 mt-1">Terkirim</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($c->event)
                                    <a href="{{ route('eo.events.campaigns.show', [$c->event, $c]) }}" class="inline-flex items-center px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-black uppercase tracking-widest transition-colors">
                                        Detail
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                Belum ada campaign.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-800">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var select = document.getElementById('campaignEventSelect');
        var btn = document.getElementById('btnCreateCampaign');
        if (!select || !btn) return;

        function setBtn() {
            var val = select.value;
            if (!val) {
                btn.href = '#';
                btn.classList.add('opacity-50', 'pointer-events-none');
                return;
            }

            btn.href = '{{ url('/eo/events') }}/' + encodeURIComponent(val) + '/campaigns/create';
            btn.classList.remove('opacity-50', 'pointer-events-none');
        }

        select.addEventListener('change', setBtn);
        setBtn();
    })();
</script>
@endpush
