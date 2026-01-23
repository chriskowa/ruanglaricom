@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'My Pacer Bookings')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <div class="text-neon font-mono text-xs tracking-widest uppercase">Runner</div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">My Pacer Bookings</h1>
            <div class="text-slate-400 text-sm mt-1">Booking yang kamu buat di platform.</div>
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
                                Pacer: <span class="text-slate-200 font-bold">{{ $b->pacer->user->name ?? '-' }}</span>
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
                            <div class="mt-2 text-sm text-slate-200">
                                <span class="text-slate-400">Total:</span>
                                <span class="font-mono font-black text-white">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 flex-shrink-0">
                            @if($b->status === 'pending')
                                <a href="{{ route('pacer.bookings.pay', $b->id) }}" class="w-full px-4 py-2 rounded-xl bg-neon text-dark font-black text-sm hover:bg-neon/90 transition text-center">
                                    Pay
                                </a>
                            @endif
                            @if(in_array($b->status, ['paid','confirmed'], true))
                                <form method="POST" action="{{ route('pacer.bookings.complete', $b->id) }}">
                                    @csrf
                                    <button class="w-full px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-black text-sm hover:border-neon/40 hover:text-neon transition">
                                        Mark Completed
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('pacer.show', $b->pacer->seo_slug) }}" class="w-full px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold text-sm hover:border-neon/40 hover:text-neon transition text-center">
                                View Pacer
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-slate-900/60 border border-slate-800 p-8 text-center text-slate-400">
                    Belum ada booking. Cari pacer di halaman Pacers.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

