@extends('layouts.pacerhub')

@push('styles')
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')

    <main class="relative z-10 pt-24 pb-10 px-4 max-w-4xl mx-auto">
        <div class="bg-card border border-slate-700 rounded-3xl overflow-hidden shadow-2xl mb-6 relative group">
            <div class="h-48 md:h-64 bg-slate-800 relative overflow-hidden">
                @if($pacer->user->banner)
                    <img src="{{ asset('storage/' . $pacer->user->banner) }}" class="w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 opacity-30 bg-[url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg')] bg-cover bg-center"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-card to-transparent"></div>
                @endif
            </div>

            <div class="px-6 pb-6 relative -mt-16 flex flex-col md:flex-row items-start md:items-end gap-6">
                <div class="relative">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden border-4 border-card shadow-xl bg-slate-700">
                        <img loading="lazy" decoding="async" src="{{ $pacer->user->avatar ? asset('storage/' . $pacer->user->avatar) : ($pacer->user->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg')) }}" class="w-full h-full object-cover">
                    </div>
                </div>

                <div class="flex-grow pt-2 md:pt-0">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-neon/10 text-neon border border-neon/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">{{ $pacer->category }} Specialist</span>
                                @if($pacer->verified)
                                    <span class="text-blue-400" title="Verified Pacer">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-3xl font-black text-white leading-tight">{{ $pacer->user->name }}</h1>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-400 mt-1">
                                @if($pacer->nickname)
                                    <span class="font-mono">"{{ $pacer->nickname }}"</span>
                                @endif
                                @if($pacer->user->city)
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span>{{ $pacer->user->city->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="hidden md:flex gap-3 mt-4 md:mt-0">
                            <a href="{{ route('pacer.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg text-sm font-bold transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">About Me</h3>
                    <p class="text-slate-200 leading-relaxed text-sm md:text-base">{{ $pacer->bio }}</p>
                    @if($pacer->tags)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach($pacer->tags as $tag)
                                <span class="px-3 py-1 bg-slate-800 rounded-full text-xs text-slate-300 border border-slate-700">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($pacer->user->profile_images && count($pacer->user->profile_images) > 0)
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Gallery</h3>
                    <div class="flex overflow-x-auto gap-4 no-scrollbar snap-x snap-mandatory pb-2">
                        @foreach($pacer->user->profile_images as $image)
                            <div class="flex-none w-48 h-48 rounded-xl overflow-hidden border border-slate-700 snap-center">
                                <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700 text-center">
                        <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Pace</p>
                        <p class="text-xl font-mono font-bold text-white">{{ $pacer->pace }}</p>
                    </div>
                    <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700 text-center">
                        <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Total Races</p>
                        <p class="text-xl font-mono font-bold text-white">{{ $pacer->total_races }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Connect</h3>
                    <div class="space-y-3">
                        <a href="#" class="flex items-center justify-between p-3 bg-[#FC4C02]/10 border border-[#FC4C02]/30 rounded-xl text-[#FC4C02]">
                            <span class="font-bold text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                Strava
                            </span>
                            <span class="text-xs opacity-70">Follow ↗</span>
                        </a>
                        @if($pacer->whatsapp)
                        <a href="https://wa.me/{{ $pacer->whatsapp }}" class="flex items-center justify-between p-3 bg-green-500/10 border border-green-500/30 rounded-xl text-green-500">
                            <span class="font-bold text-sm flex items-center gap-2">WhatsApp</span>
                            <span class="text-xs opacity-70">Chat ↗</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        async function sharePacer() {
            const shareData = {
                title: '{{ $pacer->user->name }} - Pacer Profile',
                text: 'Check out this pacer profile on RuangLari!',
                url: window.location.href
            };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                } catch (err) {
                    console.log('Error sharing:', err);
                }
            } else {
                // Fallback for browsers that don't support Web Share API
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            }
        }
    </script>
@endsection
