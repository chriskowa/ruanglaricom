@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Booking Inbox')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Pacer</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Booking Inbox</h1>
                <div class="text-slate-400 text-sm mt-1">Booking yang masuk untuk kamu.</div>
            </div>
            <a href="{{ route('pacer.index') }}" class="hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:border-neon/40 hover:text-neon transition">
                Back to Pacers
            </a>
        </div>

        <div class="space-y-3">
            @forelse($bookings as $b)
                <div class="rounded-2xl bg-slate-900/60 border border-slate-800 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="text-white font-black truncate">{{ $b->invoice_number }}</div>
                                <span class="text-[10px] px-2 py-0.5 rounded-full border border-slate-700 text-slate-300 uppercase">
                                    {{ strtoupper($b->status) }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1">
                                Runner: <span class="text-slate-200 font-bold">{{ $b->runner->name ?? '-' }}</span>
                                @if($b->race_date)
                                    <span class="text-slate-600">•</span> {{ $b->race_date->format('Y-m-d') }}
                                @endif
                                @if($b->distance)
                                    <span class="text-slate-600">•</span> {{ $b->distance }}
                                @endif
                                @if($b->target_pace)
                                    <span class="text-slate-600">•</span> Target {{ $b->target_pace }}
                                @endif
                            </div>
                            @if($b->meeting_point || $b->notes)
                                <div class="mt-2 text-sm text-slate-200">
                                    @if($b->meeting_point)
                                        <div><span class="text-slate-400">Meeting:</span> {{ $b->meeting_point }}</div>
                                    @endif
                                    @if($b->notes)
                                        <div class="text-slate-400 text-xs mt-1">{{ $b->notes }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 flex-shrink-0">
                            @if($b->status === 'paid')
                                <form method="POST" action="{{ route('pacer.bookings.confirm', $b->id) }}">
                                    @csrf
                                    <button class="w-full px-4 py-2 rounded-xl bg-neon text-dark font-black text-sm hover:bg-neon/90 transition">Confirm</button>
                                </form>
                            @endif
                            <a href="{{ route('pacer.show', $b->pacer->seo_slug) }}" class="w-full px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold text-sm hover:border-neon/40 hover:text-neon transition text-center">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-slate-900/60 border border-slate-800 p-8 text-center text-slate-400">
                    Belum ada booking masuk.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

