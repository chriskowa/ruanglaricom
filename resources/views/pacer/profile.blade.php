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

    <div class="relative h-64 md:h-80 w-full rounded-b-3xl overflow-hidden group">
        <div class="absolute inset-0 bg-slate-900">
            @if($pacer->user->banner)
                <img src="{{ asset('storage/' . $pacer->user->banner) }}" alt="Banner" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700">
            @else
                <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent"></div>
        </div>
        <div class="absolute top-6 left-6 md:left-10 z-10">
            <nav class="flex text-sm font-medium text-slate-400 mb-2">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('pacer.index') }}" class="hover:text-neon transition-colors">Pacers</a></li>
                    <li><span class="text-slate-600">/</span></li>
                    <li class="text-neon">Profile</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="relative z-10 pt-24 pb-10 px-4 max-w-4xl mx-auto">
        <div class="bg-card border border-slate-700 rounded-3xl overflow-hidden shadow-2xl mb-6 relative group">
            <div class="h-32 bg-slate-800 relative overflow-hidden"><div class="absolute inset-0 opacity-30 bg-[url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg')] bg-cover bg-center"></div><div class="absolute inset-0 bg-gradient-to-t from-card to-transparent"></div></div>
            <div class="px-6 pb-6 relative flex flex-col md:flex-row items-start md:items-end gap-6">
                <div class="relative">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden border-4 border-card shadow-xl bg-slate-700">
                        <img loading="lazy" decoding="async" src="{{ $pacer->user->avatar ? (str_starts_with($pacer->user->avatar, 'http') ? $pacer->user->avatar : (str_starts_with($pacer->user->avatar, '/storage') ? asset(ltrim($pacer->user->avatar, '/')) : asset('storage/' . $pacer->user->avatar))) : ($pacer->user->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg')) }}" class="w-full h-full object-cover">
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
                            <button id="btnSaveContact" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg text-sm font-bold transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Save Contact
                            </button>
                            <button id="btnShareProfile" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg text-sm font-bold transition">
                                Share
                            </button>
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

                @if($pacer->user && ($pacer->user->pb_5k || $pacer->user->pb_10k || $pacer->user->pb_hm || $pacer->user->pb_fm))
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Personal Best</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
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
                @endif

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
                @if($pacer->race_portfolio && is_array($pacer->race_portfolio) && count($pacer->race_portfolio) > 0)
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Race Portfolio</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($pacer->race_portfolio as $race)
                            <span class="px-3 py-1 rounded-full bg-slate-900 border border-slate-700 text-xs text-slate-300 hover:border-neon hover:text-white transition-colors">{{ $race }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="bg-card border border-slate-700 rounded-3xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Connect</h3>
                    @auth
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
                    @endauth
                    @guest
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
                            <a href="{{ route('login') }}" class="text-xs text-neon hover:underline">Login untuk melihat link</a>
                        </div>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
    </main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var btnSave = document.getElementById('btnSaveContact');
    var btnShare = document.getElementById('btnShareProfile');
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
            text: 'Check out this pacer profile on RuangLari!',
            url: window.location.href
        };
        if (navigator.share) {
            try { await navigator.share(shareData); } catch(e){}
        } else {
            try { await navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!'); } catch(e){}
        }
    }
    if(btnSave) btnSave.addEventListener('click', downloadVCard);
    if(btnShare) btnShare.addEventListener('click', shareProfile);
});
</script>
@endpush
@endsection
