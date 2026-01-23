@extends('layouts.pacerhub')

@php
    $pacerName = $pacer->user->name ?? 'Pacer';
    $pacerCity = $pacer->user && $pacer->user->city ? $pacer->user->city->name : null;
    $highlightPbLabel = null;
    $highlightPbValue = null;
    if ($pacer->user) {
        if ($pacer->category === '5K') {
            $highlightPbLabel = '5K PB';
            $highlightPbValue = $pacer->user->pb_5k;
        } elseif ($pacer->category === '10K') {
            $highlightPbLabel = '10K PB';
            $highlightPbValue = $pacer->user->pb_10k;
        } elseif ($pacer->category === 'HM (21K)') {
            $highlightPbLabel = 'HM PB';
            $highlightPbValue = $pacer->user->pb_hm;
        } elseif ($pacer->category === 'FM (42K)') {
            $highlightPbLabel = 'FM PB';
            $highlightPbValue = $pacer->user->pb_fm;
        }
    }
@endphp

@section('title', $pacerName . ' - Pacer Profile')
@section('meta_title', $pacerName . ' - Pacer ' . ($pacer->category ?? '') . ' | RuangLari')
@section('meta_description', 'Hire pacer ' . $pacerName . ($pacerCity ? (' di ' . $pacerCity) : '') . ' untuk race ' . ($pacer->category ?? '') . '. Lihat pace, personal best, portfolio race, dan hubungi untuk booking.')

@push('styles')
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .safe-bottom {
        padding-bottom: env(safe-area-inset-bottom);
    }
</style>
@endpush

@section('content')

    <div class="absolute h-72 md:h-96 w-full rounded-b-3xl overflow-hidden group pt-20">
        <div class="absolute inset-0 bg-slate-900">
            @if($pacer->user->banner)
                <img src="{{ asset('storage/' . $pacer->user->banner) }}" alt="Banner" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700">
            @else
                <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent"></div>
        </div>
        <div class="absolute top-5 left-4 right-4 md:left-10 md:right-10 z-10 flex items-center justify-between">
            <a href="{{ route('pacer.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900/60 border border-slate-700 text-slate-200 hover:border-neon/40 hover:text-neon transition backdrop-blur-md">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="text-sm font-bold">Pacers</span>
            </a>
            <div class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-300">
                <span class="text-slate-500">PacerHub</span>
                <span class="text-slate-700">•</span>
                <span class="text-neon font-bold">Profile</span>
            </div>
        </div>
    </div>

    <main class="relative z-10 pt-28 pb-28 md:pb-10 px-4 max-w-4xl mx-auto">
        <div class="bg-card border border-slate-700 rounded-3xl overflow-hidden shadow-2xl mb-6 relative group">
            <div class="h-36 w-full bg-slate-800 absolute overflow-hidden">
                <div class="absolute inset-0 opacity-30 bg-[url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg')] bg-cover bg-center"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-card to-transparent"></div>
            </div>
            <div class="px-6 pb-6 pt-6 relative flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="relative">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden border-4 border-card shadow-xl bg-slate-700">
                        <img loading="lazy" decoding="async" src="{{ $pacer->user->avatar ? (str_starts_with($pacer->user->avatar, 'http') ? $pacer->user->avatar : (str_starts_with($pacer->user->avatar, '/storage') ? asset(ltrim($pacer->user->avatar, '/')) : asset('storage/' . $pacer->user->avatar))) : ($pacer->user->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg')) }}" class="w-full h-full object-cover">
                    </div>
                </div>

                <div class="flex-grow pt-2 md:pt-0">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-neon/10 text-neon border border-neon/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">{{ $pacer->category }} Specialist</span>
                                @if($pacer->verified)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-[10px] font-bold text-blue-300 uppercase tracking-wider">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                        Verified
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-3xl md:text-4xl font-black text-white leading-tight">{{ $pacerName }}</h1>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-400 mt-1">
                                @if($pacer->nickname)
                                    <span class="font-mono">"{{ $pacer->nickname }}"</span>
                                @endif
                                @if($pacerCity)
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span>{{ $pacerCity }}</span>
                                @endif
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-2xl bg-slate-900/40 border border-slate-700/70 p-3">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Pace</div>
                                    <div class="text-white font-mono font-black text-base mt-0.5">{{ $pacer->pace }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-900/40 border border-slate-700/70 p-3">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">{{ $highlightPbLabel ?? 'PB' }}</div>
                                    <div class="text-white font-mono font-black text-base mt-0.5">{{ $highlightPbValue ?? '-' }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-900/40 border border-slate-700/70 p-3">
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Races</div>
                                    <div class="text-white font-mono font-black text-base mt-0.5">{{ $pacer->total_races }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-auto flex flex-col gap-2 mt-2 md:mt-0">
                            <button id="btnHirePacerTop" class="w-full md:w-auto px-5 py-3 rounded-2xl bg-neon text-dark font-black text-sm hover:bg-neon/90 transition shadow-lg shadow-neon/20 flex items-center justify-center gap-2">
                                <span>Hire Pacer</span>
                                <span class="text-[10px] bg-dark/10 px-2 py-0.5 rounded-full">Fast</span>
                            </button>
                            <div class="grid grid-cols-2 gap-2">
                            <button id="btnChatWhatsAppTop" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-600 text-sm font-bold transition flex items-center justify-center gap-2 text-white">
                                <span>{{ $contactUnlocked ? 'Chat' : 'Unlock' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $contactUnlocked ? 'WA' : 'Pay' }}</span>
                            </button>
                                <button id="btnShareProfile" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-600 text-sm font-bold transition flex items-center justify-center gap-2 text-white">
                                    <i class="fas fa-share-alt"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                            <button id="btnSaveContact" class="hidden md:flex px-4 py-2 rounded-2xl bg-slate-900/40 hover:bg-slate-800 border border-slate-700 text-sm font-bold transition items-center justify-center gap-2 text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Save Contact</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <div x-data="{open:true}" class="bg-card border border-slate-700 rounded-3xl overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-5 flex items-center justify-between">
                        <div class="text-left">
                            <div class="text-sm font-black text-white">About</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Kenal pacer ini dalam 30 detik</div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300">
                            <span x-text="open ? '−' : '+'"></span>
                        </div>
                    </button>
                    <div x-show="open" x-transition class="px-6 pb-6">
                        <p class="text-slate-200 leading-relaxed text-sm md:text-base">{{ $pacer->bio }}</p>
                        @if($pacer->tags)
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach($pacer->tags as $tag)
                                    <span class="px-3 py-1 bg-slate-800 rounded-full text-xs text-slate-300 border border-slate-700">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-900/40 border border-slate-700 p-4">
                                <div class="text-[11px] text-slate-400 font-bold uppercase">What you get</div>
                                <div class="mt-2 space-y-2 text-sm text-slate-200">
                                    <div class="flex items-start gap-2"><span class="text-neon font-black">•</span><span>Pace guidance + race strategy</span></div>
                                    <div class="flex items-start gap-2"><span class="text-neon font-black">•</span><span>Warmup, fueling, dan reminders</span></div>
                                    <div class="flex items-start gap-2"><span class="text-neon font-black">•</span><span>Support sampai finish</span></div>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-900/40 border border-slate-700 p-4">
                                <div class="text-[11px] text-slate-400 font-bold uppercase">Availability</div>
                                <div class="mt-2 text-sm text-slate-200">
                                    <div class="font-bold text-white">By request</div>
                                    <div class="text-xs text-slate-400 mt-1">Klik Hire Pacer untuk cek slot dan detail kebutuhanmu.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($pacer->user && ($pacer->user->pb_5k || $pacer->user->pb_10k || $pacer->user->pb_hm || $pacer->user->pb_fm))
                <div x-data="{open:true}" class="bg-card border border-slate-700 rounded-3xl overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-5 flex items-center justify-between">
                        <div class="text-left">
                            <div class="text-sm font-black text-white">Personal Best</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Performance highlights</div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300">
                            <span x-text="open ? '−' : '+'"></span>
                        </div>
                    </button>
                    <div x-show="open" x-transition class="px-6 pb-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">5K PB</p>
                            <p class="text-xl font-mono font-bold {{ $pacer->category === '5K' ? 'text-neon' : 'text-white' }}">{{ $pacer->user->pb_5k ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">10K PB</p>
                            <p class="text-xl font-mono font-bold {{ $pacer->category === '10K' ? 'text-neon' : 'text-white' }}">{{ $pacer->user->pb_10k ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">HM PB</p>
                            <p class="text-xl font-mono font-bold {{ $pacer->category === 'HM (21K)' ? 'text-neon' : 'text-white' }}">{{ $pacer->user->pb_hm ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">FM PB</p>
                            <p class="text-xl font-mono font-bold {{ $pacer->category === 'FM (42K)' ? 'text-neon' : 'text-white' }}">{{ $pacer->user->pb_fm ?? '-' }}</p>
                        </div>
                    </div>
                    </div>
                </div>
                @endif

                @if($pacer->user->profile_images && count($pacer->user->profile_images) > 0)
                <div x-data="{open:true}" class="bg-card border border-slate-700 rounded-3xl overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-5 flex items-center justify-between">
                        <div class="text-left">
                            <div class="text-sm font-black text-white">Gallery</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Swipe untuk lihat foto</div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300">
                            <span x-text="open ? '−' : '+'"></span>
                        </div>
                    </button>
                    <div x-show="open" x-transition class="px-6 pb-6">
                    <div class="flex overflow-x-auto gap-4 no-scrollbar snap-x snap-mandatory pb-2">
                        @foreach($pacer->user->profile_images as $image)
                            <button type="button" class="flex-none w-44 h-44 rounded-2xl overflow-hidden border border-slate-700 snap-center bg-slate-900/30" data-gallery-src="{{ asset('storage/' . $image) }}">
                                <img loading="lazy" src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                            </button>
                        @endforeach
                    </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Hire This Pacer</h3>
                    <div class="rounded-2xl bg-slate-900/40 border border-slate-700 p-4">
                        <div class="text-white font-black text-lg">Ready for race day?</div>
                        <div class="text-xs text-slate-400 mt-1">Isi kebutuhanmu, lanjut bayar di platform, lalu chat pacer akan terbuka.</div>
                        <button id="btnHirePacerSide" class="mt-4 w-full py-3 rounded-2xl bg-neon text-dark font-black text-sm hover:bg-neon/90 transition shadow-lg shadow-neon/20">Hire Pacer</button>
                        <button id="btnChatWhatsAppSide" class="mt-2 w-full py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-black text-sm transition">Chat WhatsApp</button>
                        <div class="mt-3 text-[11px] text-slate-500">Tips: tambah tanggal race + target pace biar cepat deal.</div>
                    </div>
                </div>
                @if($pacer->race_portfolio && is_array($pacer->race_portfolio) && count($pacer->race_portfolio) > 0)
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Race Portfolio</h3>
                    @php($neonColors = ['#ccff00','#22d3ee','#a78bfa','#f472b6','#f59e0b','#10b981','#ef4444','#14b8a6'])
                    <div class="flex flex-wrap gap-2">
                        @foreach($pacer->race_portfolio as $race)
                            @php($c = $neonColors[crc32($race) % count($neonColors)])
                            <span class="relative inline-flex items-center px-3 py-1 rounded-full border text-xs font-bold transition-all duration-300 bg-slate-900/60 group hover:scale-[1.03] hover:shadow-lg group-hover:bg-white group-hover:text-dark" style="--c: {{ $c }}; border-color: var(--c); color: var(--c);">
                                <span class="absolute inset-0 rounded-full blur-md opacity-0 group-hover:opacity-100" style="background: var(--c);"></span>
                                <span class="relative z-10">{{ $race }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Connect</h3>
                    @if($contactUnlocked)
                        <div class="space-y-3">
                            @if($pacer->user->strava_url)
                            <a href="{{ $pacer->user->strava_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3 bg-[#FC4C02]/10 border border-[#FC4C02]/30 rounded-xl text-[#FC4C02] hover:bg-[#FC4C02] hover:text-white transition">
                                <span class="font-bold text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                    Strava
                                </span>
                                <span class="text-xs opacity-70">Follow ↗</span>
                            </a>
                            @endif
                            @if($pacer->user->instagram_url)
                            <a href="{{ $pacer->user->instagram_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3 bg-pink-500/10 border border-pink-500/30 rounded-xl text-pink-500 hover:bg-pink-600 hover:text-white transition">
                                <span class="font-bold text-sm flex items-center gap-2">Instagram</span>
                                <span class="text-xs opacity-70">Follow ↗</span>
                            </a>
                            @endif
                            @if($pacer->user->facebook_url)
                            <a href="{{ $pacer->user->facebook_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl text-blue-500 hover:bg-blue-600 hover:text-white transition">
                                <span class="font-bold text-sm flex items-center gap-2">Facebook</span>
                                <span class="text-xs opacity-70">Follow ↗</span>
                            </a>
                            @endif
                            @if($pacer->user->tiktok_url)
                            <a href="{{ $pacer->user->tiktok_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3 bg-gray-500/10 border border-gray-500/30 rounded-xl text-white hover:bg-gray-600 transition">
                                <span class="font-bold text-sm flex items-center gap-2">TikTok</span>
                                <span class="text-xs opacity-70">Follow ↗</span>
                            </a>
                            @endif
                            @if($pacer->whatsapp)
                            <a href="https://wa.me/{{ $pacer->whatsapp }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3 bg-green-500/10 border border-green-500/30 rounded-xl text-green-500 hover:bg-green-600 hover:text-white transition">
                                <span class="font-bold text-sm flex items-center gap-2">WhatsApp</span>
                                <span class="text-xs opacity-70">Chat ↗</span>
                            </a>
                            @endif
                        </div>
                    @else
                        <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-[#FC4C02]/10 border border-[#FC4C02]/30 rounded-xl text-[#FC4C02] opacity-70 filter blur-[2px] pointer-events-none">
                            <span class="font-bold text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                                Strava
                            </span>
                            <span class="text-xs opacity-70">Follow ↗</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-pink-500/10 border border-pink-500/30 rounded-xl text-pink-500 opacity-70 filter blur-[2px] pointer-events-none">
                            <span class="font-bold text-sm flex items-center gap-2">Instagram</span>
                            <span class="text-xs opacity-70">Follow ↗</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl text-blue-500 opacity-70 filter blur-[2px] pointer-events-none">
                            <span class="font-bold text-sm flex items-center gap-2">Facebook</span>
                            <span class="text-xs opacity-70">Follow ↗</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-500/10 border border-gray-500/30 rounded-xl text-white opacity-70 filter blur-[2px] pointer-events-none">
                            <span class="font-bold text-sm flex items-center gap-2">TikTok</span>
                            <span class="text-xs opacity-70">Follow ↗</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-500/10 border border-green-500/30 rounded-xl text-green-500 opacity-70 filter blur-[2px] pointer-events-none">
                            <span class="font-bold text-sm flex items-center gap-2">WhatsApp</span>
                            <span class="text-xs opacity-70">Chat ↗</span>
                        </div>
                        <div class="mt-3 text-center">
                            @auth
                                <button id="btnUnlockContacts" class="text-xs text-neon hover:underline">Bayar booking untuk membuka kontak</button>
                            @endauth
                            @guest
                                <a href="{{ route('login') }}" class="text-xs text-neon hover:underline">Login untuk booking</a>
                            @endguest
                        </div>
                    </div>
                    @endif
                    <div class="mt-6">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Share QR</h4>
                        <div class="flex items-center justify-center">
                            <div class="rounded-2xl p-3 border border-neon/30 bg-slate-900 relative">
                                <div class="absolute inset-0 rounded-2xl pointer-events-none" style="box-shadow: 0 0 40px rgba(204,255,0,0.15);"></div>
                                <div id="pacer-profile-qr-inner" class="bg-white rounded-xl"></div>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500 text-center mt-2">Scan untuk membuka profil</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="fixed bottom-0 left-0 right-0 z-40 md:hidden safe-bottom">
        <div class="mx-auto max-w-4xl px-4 pb-4">
            <div class="rounded-3xl bg-slate-900/80 border border-slate-700 backdrop-blur-md shadow-2xl p-3">
                <div class="grid grid-cols-3 gap-2">
                    <button id="btnHirePacerSticky" class="py-3 rounded-2xl bg-neon text-dark font-black text-sm">Hire</button>
                    <button id="btnChatWhatsAppSticky" class="py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white font-black text-sm">Chat WA</button>
                    <button id="btnSaveContactSticky" class="py-3 rounded-2xl bg-slate-800 border border-slate-700 text-white font-black text-sm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div id="hire-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div id="hire-modal-overlay" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div class="relative z-10 w-full max-w-lg max-h-[90vh] flex flex-col">
            <div class="bg-card border border-slate-700 rounded-3xl shadow-2xl flex flex-col overflow-hidden max-h-full">
                <div class="p-6 border-b border-slate-800 flex items-start justify-between gap-4 flex-shrink-0">
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-bold">Hire Pacer</div>
                        <div class="text-white font-black text-xl mt-1">{{ $pacerName }}</div>
                        <div class="text-xs text-slate-400 mt-1">Isi detail, lanjut bayar di platform. Kontak pacer akan terbuka setelah paid.</div>
                    </div>
                    <button id="btnCloseHireModal" class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-white flex items-center justify-center">✕</button>
                </div>
                <form id="hireBookingForm" method="POST" action="{{ route('pacer.bookings.store', $pacer->seo_slug) }}" class="p-6 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-400 font-bold uppercase">Race Name</label>
                            <input id="hireRaceName" name="event_name" type="text" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="CTH: Borobudur Marathon">
                        </div>
                        <div>
                            <label class="text-xs text-slate-400 font-bold uppercase">Race Date</label>
                            <input id="hireRaceDate" name="race_date" type="date" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-400 font-bold uppercase">Distance</label>
                            <input id="hireDistance" name="distance" type="text" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="CTH: 10K / HM / FM">
                        </div>
                        <div>
                            <label class="text-xs text-slate-400 font-bold uppercase">Target Pace</label>
                            <input id="hireTargetPace" name="target_pace" type="text" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="CTH: 6:00/km">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 font-bold uppercase">Meeting Point</label>
                        <input id="hireMeetPoint" name="meeting_point" type="text" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="CTH: Gate start line / area pacer">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 font-bold uppercase">Notes</label>
                        <textarea id="hireNotes" name="notes" rows="3" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" placeholder="CTH: butuh bantuan strategi negative split, fueling, dsb"></textarea>
                    </div>
                    <div class="rounded-2xl bg-slate-900/40 border border-slate-700 p-4">
                        <div class="text-xs text-slate-400 font-bold uppercase">Preview Message</div>
                        <pre id="hirePreview" class="mt-2 whitespace-pre-wrap text-sm text-slate-200 font-mono"></pre>
                    </div>
                </form>
                <div class="p-6 border-t border-slate-800 space-y-2 flex-shrink-0">
                    <button id="btnProceedPayment" class="w-full py-4 rounded-2xl bg-neon text-dark font-black text-base hover:bg-neon/90 transition">Proceed to Payment</button>
                    <div class="text-xs text-slate-500 text-center">Pembayaran aman via Midtrans. Kontak pacer terbuka setelah paid.</div>
                </div>
            </div>
        </div>
    </div>

    <div id="gallery-modal" class="fixed inset-0 z-50 hidden">
        <div id="gallery-modal-overlay" class="absolute inset-0 bg-black/90"></div>
        <div class="relative z-10 h-full w-full flex items-center justify-center p-4">
            <button id="btnCloseGallery" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-slate-900/70 border border-slate-700 text-white flex items-center justify-center">✕</button>
            <button id="btnPrevGallery" class="absolute left-3 md:left-8 w-11 h-11 rounded-full bg-slate-900/70 border border-slate-700 text-white flex items-center justify-center">◀</button>
            <button id="btnNextGallery" class="absolute right-3 md:right-8 w-11 h-11 rounded-full bg-slate-900/70 border border-slate-700 text-white flex items-center justify-center">▶</button>
            <img id="gallery-image" src="" class="max-h-[85vh] max-w-[92vw] rounded-2xl border border-slate-700 object-contain">
        </div>
    </div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var btnSave = document.getElementById('btnSaveContact');
    var btnShare = document.getElementById('btnShareProfile');
    var btnSaveSticky = document.getElementById('btnSaveContactSticky');
    var btnHireTop = document.getElementById('btnHirePacerTop');
    var btnHireSide = document.getElementById('btnHirePacerSide');
    var btnHireSticky = document.getElementById('btnHirePacerSticky');
    var btnChatTop = document.getElementById('btnChatWhatsAppTop');
    var btnChatSide = document.getElementById('btnChatWhatsAppSide');
    var btnChatSticky = document.getElementById('btnChatWhatsAppSticky');

    var hireModal = document.getElementById('hire-modal');
    var hireOverlay = document.getElementById('hire-modal-overlay');
    var btnCloseHireModal = document.getElementById('btnCloseHireModal');
    var btnProceedPayment = document.getElementById('btnProceedPayment');
    var hireBookingForm = document.getElementById('hireBookingForm');
    var hirePreview = document.getElementById('hirePreview');
    var hireRaceName = document.getElementById('hireRaceName');
    var hireRaceDate = document.getElementById('hireRaceDate');
    var hireDistance = document.getElementById('hireDistance');
    var hireTargetPace = document.getElementById('hireTargetPace');
    var hireMeetPoint = document.getElementById('hireMeetPoint');
    var hireNotes = document.getElementById('hireNotes');
    var btnUnlockContacts = document.getElementById('btnUnlockContacts');

    var pacerName = @json($pacerName);
    var pacerCategory = @json($pacer->category ?? '');
    var pacerPace = @json($pacer->pace ?? '');
    var contactUnlocked = @json((bool) ($contactUnlocked ?? false));
    var pacerWhatsapp = @json($pacer->whatsapp ?? '');
    var waNumber = contactUnlocked ? String(pacerWhatsapp || '').replace(/\D/g, '') : '';

    function downloadVCard(){
        var name = @json($pacer->user->name);
        var nickname = @json($pacer->nickname ?? '');
        var category = @json($pacer->category);
        var whatsapp = @json($pacer->whatsapp ?? '');
        var bio = @json($pacer->bio ?? '');
        var url = window.location.href;
        var parts = String(name).split(' ');
        var nField = parts.length > 1 ? (parts.slice(1).join(';') + ';' + parts[0]) : name + ';;;;';
        var vCardData = 'BEGIN:VCARD\\n'
            + 'VERSION:3.0\\n'
            + 'FN:' + name + '\\n'
            + 'N:' + nField + '\\n'
            + (nickname ? ('NICKNAME:' + nickname + '\\n') : '')
            + 'ORG:Ruang Lari Indonesia\\n'
            + 'TITLE:Professional Pacer (' + category + ')\\n'
            + (whatsapp ? ('TEL;TYPE=CELL:' + whatsapp + '\\n') : '')
            + 'URL:' + url + '\\n'
            + (bio ? ('NOTE:' + bio + '\\n') : '')
            + 'END:VCARD';
        var blob = new Blob([vCardData], { type: 'text/vcard' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = String(name).replace(/\\s+/g, '_') + '.vcf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    async function shareProfile(){
        var shareData = {
            title: @json($pacer->user->name) + ' - Pacer Profile',
            text: 'Lihat profil pacer ini di RuangLari dan hire untuk race day.',
            url: window.location.href
        };
        if (navigator.share) {
            try { await navigator.share(shareData); } catch(e){}
        } else {
            try { await navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!'); } catch(e){}
        }
    }
    if(btnSave) btnSave.addEventListener('click', downloadVCard);
    if(btnSaveSticky) btnSaveSticky.addEventListener('click', downloadVCard);
    if(btnShare) btnShare.addEventListener('click', shareProfile);
    var qrContainer = document.getElementById('pacer-profile-qr-inner');
    if (qrContainer && typeof QRCode !== 'undefined') {
        var profileUrl = window.location.href;
        new QRCode(qrContainer, {
            text: profileUrl,
            width: 180,
            height: 180,
            colorDark: "#1f2937",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    function buildHireMessage(){
        var raceName = (hireRaceName && hireRaceName.value || '').trim();
        var raceDate = (hireRaceDate && hireRaceDate.value || '').trim();
        var distance = (hireDistance && hireDistance.value || '').trim();
        var targetPace = (hireTargetPace && hireTargetPace.value || '').trim();
        var meetPoint = (hireMeetPoint && hireMeetPoint.value || '').trim();
        var notes = (hireNotes && hireNotes.value || '').trim();

        var lines = [];
        lines.push('Hi ' + pacerName + ', saya mau hire pacer 🙏');
        if (pacerCategory) lines.push('Kategori pacer: ' + pacerCategory);
        if (pacerPace) lines.push('Pace pacer: ' + pacerPace);
        lines.push('');
        if (raceName) lines.push('Race: ' + raceName);
        if (raceDate) lines.push('Tanggal: ' + raceDate);
        if (distance) lines.push('Jarak: ' + distance);
        if (targetPace) lines.push('Target pace: ' + targetPace);
        if (meetPoint) lines.push('Meeting point: ' + meetPoint);
        if (notes) lines.push('Catatan: ' + notes);
        lines.push('');
        lines.push('Link profil: ' + window.location.href);
        return lines.join('\n');
    }

    function updateHirePreview(){
        if (!hirePreview) return;
        hirePreview.textContent = buildHireMessage();
    }

    function openHireModal(){
        if (!hireModal) return;
        hireModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        updateHirePreview();
    }

    function closeHireModal(){
        if (!hireModal) return;
        hireModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function chatWhatsAppQuick(){
        if (!contactUnlocked) {
            openHireModal();
            return;
        }
        if (!waNumber) {
            alert('Pacer belum menambahkan WhatsApp.');
            return;
        }
        var msg = encodeURIComponent('Hi ' + pacerName + ', saya tertarik hire pacer untuk race day. Boleh info availability?\\n\\nLink profil: ' + window.location.href);
        window.open('https://wa.me/' + waNumber + '?text=' + msg, '_blank', 'noopener');
    }

    if(btnHireTop) btnHireTop.addEventListener('click', openHireModal);
    if(btnHireSide) btnHireSide.addEventListener('click', openHireModal);
    if(btnHireSticky) btnHireSticky.addEventListener('click', openHireModal);
    if(btnUnlockContacts) btnUnlockContacts.addEventListener('click', openHireModal);
    if(btnCloseHireModal) btnCloseHireModal.addEventListener('click', closeHireModal);
    if(hireOverlay) hireOverlay.addEventListener('click', closeHireModal);

    if(btnChatTop) btnChatTop.addEventListener('click', chatWhatsAppQuick);
    if(btnChatSide) btnChatSide.addEventListener('click', chatWhatsAppQuick);
    if(btnChatSticky) btnChatSticky.addEventListener('click', chatWhatsAppQuick);

    [hireRaceName, hireRaceDate, hireDistance, hireTargetPace, hireMeetPoint, hireNotes].forEach(function(el){
        if(el) el.addEventListener('input', updateHirePreview);
    });

    if(btnProceedPayment) btnProceedPayment.addEventListener('click', function(){
        if (!hireBookingForm) return;
        hireBookingForm.submit();
    });

    var galleryButtons = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-src]'));
    var galleryModal = document.getElementById('gallery-modal');
    var galleryOverlay = document.getElementById('gallery-modal-overlay');
    var galleryImage = document.getElementById('gallery-image');
    var btnCloseGallery = document.getElementById('btnCloseGallery');
    var btnPrevGallery = document.getElementById('btnPrevGallery');
    var btnNextGallery = document.getElementById('btnNextGallery');
    var galleryIndex = 0;

    function openGalleryAt(idx){
        if (!galleryModal || !galleryImage || galleryButtons.length === 0) return;
        galleryIndex = Math.max(0, Math.min(idx, galleryButtons.length - 1));
        galleryImage.src = galleryButtons[galleryIndex].getAttribute('data-gallery-src');
        galleryModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function closeGallery(){
        if (!galleryModal) return;
        galleryModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    function prevGallery(){
        openGalleryAt((galleryIndex - 1 + galleryButtons.length) % galleryButtons.length);
    }
    function nextGallery(){
        openGalleryAt((galleryIndex + 1) % galleryButtons.length);
    }

    galleryButtons.forEach(function(btn, idx){
        btn.addEventListener('click', function(){ openGalleryAt(idx); });
    });
    if(btnCloseGallery) btnCloseGallery.addEventListener('click', closeGallery);
    if(btnPrevGallery) btnPrevGallery.addEventListener('click', prevGallery);
    if(btnNextGallery) btnNextGallery.addEventListener('click', nextGallery);
    if(galleryOverlay) galleryOverlay.addEventListener('click', closeGallery);
});
</script>
@endpush
@endsection
