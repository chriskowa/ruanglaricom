@extends('layouts.pacerhub')

@section('title', $user->name . ' - Runner Profile')
@section('meta_title', $user->name . ' (@' . ($user->username ?? $user->id) . ') - Profile Pelari & Coach')
@section('meta_description', 'Profil dan performa lari ' . $user->name . ' (@' . ($user->username ?? $user->id) . ') di Ruang Lari. Lihat riwayat aktivitas, statistik komunitas, dan program latihan lari.')
@section('meta_keywords', $user->name . ', pelari indonesia, profil pelari, coach lari, ruang lari')

@push('styles')
<script>
    if (typeof tailwind !== 'undefined' && tailwind && tailwind.config) {
        tailwind.config.theme = tailwind.config.theme || {};
        tailwind.config.theme.extend = tailwind.config.theme.extend || {};
        tailwind.config.theme.extend.colors = tailwind.config.theme.extend.colors || {};
        tailwind.config.theme.extend.colors.neon = '#ccff00';
    }
</script>
<style>
    .glass-panel {
        background: #0f172a;
        border: 1px solid #1e293b;
    }
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
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans bg-dark text-slate-200">
    <div class="max-w-5xl mx-auto">
        
        <!-- Header / Banner -->
        <div class="glass-panel rounded-lg overflow-hidden shadow-xl mb-8 relative group">
            <div class="h-48 md:h-64 bg-slate-800 relative overflow-hidden">
                @if($user->banner)
                    <img src="{{ asset('storage/' . $user->banner) }}" class="w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 opacity-30 bg-[url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg')] bg-cover bg-center"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent"></div>
                @endif
            </div>

            <div class="px-6 pb-6 relative -mt-20 flex flex-col md:flex-row items-end gap-6">
                <div class="relative group">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-lg overflow-hidden border-4 border-slate-900 shadow-xl bg-slate-800">
                        <img loading="lazy" decoding="async" src="{{ $user->avatar_url }}" class="w-full h-full object-cover">
                    </div>
                    @if($user->role === 'coach')
                        <div class="absolute -bottom-2 -right-2 bg-blue-500 text-white text-xs font-black px-3 py-1 rounded-full border-2 border-slate-900 shadow-lg">
                            COACH
                        </div>
                    @elseif($user->role === 'eo')
                        <div class="absolute -bottom-2 -right-2 bg-purple-500 text-white text-xs font-black px-3 py-1 rounded-full border-2 border-slate-900 shadow-lg">
                            EO
                        </div>
                    @else
                        <div class="absolute -bottom-2 -right-2 bg-slate-700 text-slate-300 text-xs font-black px-3 py-1 rounded-full border-2 border-slate-900 shadow-lg">
                            RUNNER
                        </div>
                    @endif
                </div>

                <div class="flex-grow pb-2 w-full">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-black text-white leading-tight mb-1">{{ $user->name }}</h1>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                @if($user->username)
                                    <span class="text-neon font-mono">@</span><span class="text-slate-400 font-mono">{{ $user->username }}</span>
                                @endif
                                
                                @if($user->city)
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span class="text-slate-300 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ $user->city->name }}
                                    </span>
                                @endif

                                @if($user->gender)
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span class="text-slate-300 capitalize">{{ $user->gender }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if(auth()->id() !== $user->id)
                                @if(auth()->check())
                                    @if(auth()->user()->isFollowing($user))
                                        <form action="{{ route('unfollow', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-red-500/20 hover:text-red-500 border border-slate-700 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
                                                Unfollow
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('follow', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-5 py-2.5 bg-neon hover:bg-white hover:text-dark text-dark rounded-xl text-sm font-black transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                                Follow
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(auth()->user()->role !== 'eo')
                                        <a href="{{ route('chat.show', $user) }}" class="p-2.5 bg-slate-800 hover:bg-blue-500/20 hover:text-blue-400 border border-slate-700 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-neon hover:bg-white hover:text-dark text-dark rounded-xl text-sm font-black transition-all shadow-lg shadow-neon/20">
                                        Login to Follow
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('profile.show') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                                    Edit Profile
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: About & Stats -->
            <div class="space-y-6">
                <!-- Stats -->
                <div class="glass-panel rounded-lg p-6">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Statistik Komunitas</h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-xl font-bold font-mono text-white">{{ $user->posts()->count() }}</p>
                            <p class="text-[10px] text-slate-400 uppercase">Posts</p>
                        </div>
                        <div>
                            <p class="text-xl font-bold font-mono text-white">{{ $user->followers()->count() }}</p>
                            <p class="text-[10px] text-slate-400 uppercase">Followers</p>
                        </div>
                        <div>
                            <p class="text-xl font-bold font-mono text-white">{{ $user->following()->count() }}</p>
                            <p class="text-[10px] text-slate-400 uppercase">Following</p>
                        </div>
                    </div>
                </div>

                @if($user->role === 'coach')
                @php
                    $coachPublishedPrograms = $user->programs()
                        ->where('is_published', true)
                        ->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('is_self_generated')->orWhere('is_self_generated', false);
                        })
                        ->where(function ($q) {
                            $q->whereNull('is_vdot_generated')->orWhere('is_vdot_generated', false);
                        })
                        ->latest()
                        ->get();
                @endphp
                <div class="glass-panel rounded-lg p-6">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Statistik Pelatih</h3>
                    <div class="text-center">
                        <p class="text-xl font-bold font-mono text-white">{{ $coachPublishedPrograms->count() }}</p>
                        <p class="text-[10px] text-slate-400 uppercase">Program Terbit</p>
                    </div>
                    @if($coachPublishedPrograms->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Program Unggulan</h4>
                        <div class="space-y-2">
                            @foreach($coachPublishedPrograms->take(3) as $program)
                            <a href="{{ route('programs.show', $program->slug) }}" class="block p-2.5 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-800 transition-colors">
                                <p class="text-sm font-semibold text-white">{{ $program->title }}</p>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ strtoupper($program->distance_target ?: 'General') }} • {{ ucfirst($program->difficulty) }}</p>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Bio / Info -->
                <div class="glass-panel rounded-lg p-6">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Tentang</h3>
                    @if($user->bio)
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $user->bio }}</p>
                    @else
                        <p class="text-sm text-slate-400 italic">Belum ada bio yang ditambahkan.</p>
                    @endif
                    
                    <div class="mt-4 pt-4 border-t border-slate-800 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Bergabung {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Gallery & Recent Activity -->
            <div class="lg:col-span-2 space-y-6">
                
                @if($user->role === 'coach')
                @php
                    // Dynamic certifications from user profile (never hardcoded)
                    $rawCerts = $user->certifications;
                    if (is_string($rawCerts)) {
                        $certs = json_decode($rawCerts, true) ?: array_filter(array_map('trim', explode(',', $rawCerts)));
                    } elseif (is_array($rawCerts)) {
                        $certs = $rawCerts;
                    } else {
                        $certs = [];
                    }
                    $certs = array_values(array_filter($certs));

                    // Dynamic non-AI published programs
                    $coachPrograms = $user->programs()
                        ->where('is_published', true)
                        ->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('is_self_generated')->orWhere('is_self_generated', false);
                        })
                        ->where(function ($q) {
                            $q->whereNull('is_vdot_generated')->orWhere('is_vdot_generated', false);
                        })
                        ->latest()
                        ->get();

                    // Derive real specialties from published programs
                    $programSpecialties = $coachPrograms->pluck('distance_target')->filter()->unique()->map(function($dt) {
                        $dt = strtolower($dt);
                        if (in_array($dt, ['21k', 'hm', 'half_marathon'])) return 'Half Marathon Preparation';
                        if (in_array($dt, ['42k', 'fm', 'marathon'])) return 'Marathon Preparation';
                        if ($dt === '5k') return '5K / Speed Development';
                        if ($dt === '10k') return '10K Endurance';
                        return ucfirst($dt) . ' Training';
                    })->values();

                    // Real verified reviews from platform
                    $realReviews = \App\Models\ProgramReview::whereIn('program_id', $coachPrograms->pluck('id'))
                        ->with('runner')
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp

                <!-- Coach Professional Profile Details -->
                <div class="glass-panel rounded-lg p-6 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div>
                            <h2 class="text-lg font-bold text-white tracking-tight">
                                Profil Pelatih Profesional
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Kualifikasi resmi, spesialisasi, dan program latihan</p>
                        </div>
                        @if(auth()->id() === $user->id)
                            <a href="{{ route('profile.show') }}" class="text-xs text-[#CCFF00] hover:underline font-semibold flex items-center gap-1">
                                <i class="fas fa-edit"></i> Edit Lisensi
                            </a>
                        @endif
                    </div>
                    
                    <!-- Certifications -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Sertifikasi & Lisensi Resmi</h4>
                        @if(!empty($certs))
                            <div class="flex flex-wrap gap-2">
                                @foreach($certs as $cert)
                                    <span class="px-3 py-1.5 bg-slate-900 text-white border border-slate-800 rounded-md text-xs font-mono font-medium flex items-center gap-1.5 shadow-sm">
                                        <i class="fas fa-certificate text-[#CCFF00]"></i> {{ $cert }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 bg-slate-900/60 border border-slate-800 rounded-md">
                                <p class="text-xs text-slate-400">
                                    Belum ada sertifikasi resmi yang dilampirkan pada profil ini.
                                    @if(auth()->id() === $user->id)
                                        <a href="{{ route('profile.show') }}" class="text-[#CCFF00] hover:underline ml-1 font-semibold">Tambahkan di Pengaturan Profil &rarr;</a>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Specialties -->
                    @if($programSpecialties->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Spesialisasi Program Latihan</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($programSpecialties as $spec)
                                <span class="px-3 py-1.5 bg-slate-900 text-[#CCFF00] border border-slate-800 rounded-md text-xs font-medium">
                                    {{ $spec }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Experience & Coaching Bio -->
                    @if(!empty($user->coaching_experience) || !empty($user->bio))
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Pengalaman & Pendekatan Melatih</h4>
                        <p class="text-sm text-slate-200 leading-relaxed">
                            {{ $user->coaching_experience ?? $user->bio }}
                        </p>
                    </div>
                    @endif

                    <!-- Race Portfolio (Real only, if available) -->
                    @if($user->is_pacer && $user->pacerProfile && !empty($user->pacerProfile->race_portfolio))
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Portofolio Race & Pacing</h4>
                        <div class="space-y-1.5">
                            @foreach($user->pacerProfile->race_portfolio as $race)
                                <div class="flex items-center gap-2 text-sm text-slate-300">
                                    <i class="fas fa-running text-[#CCFF00]"></i>
                                    <span>{{ $race }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Personal Bests (PB) -->
                    @if($user->pb_5k || $user->pb_10k || $user->pb_hm || $user->pb_fm)
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Personal Bests (PB)</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            @if($user->pb_5k)
                            <div class="bg-slate-900 rounded-md p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase">5K</p>
                                <p class="text-sm font-mono font-bold text-white mt-0.5">{{ $user->pb_5k }}</p>
                            </div>
                            @endif
                            @if($user->pb_10k)
                            <div class="bg-slate-900 rounded-md p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase">10K</p>
                                <p class="text-sm font-mono font-bold text-white mt-0.5">{{ $user->pb_10k }}</p>
                            </div>
                            @endif
                            @if($user->pb_hm)
                            <div class="bg-slate-900 rounded-md p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase">Half Marathon (21K)</p>
                                <p class="text-sm font-mono font-bold text-white mt-0.5">{{ $user->pb_hm }}</p>
                            </div>
                            @endif
                            @if($user->pb_fm)
                            <div class="bg-slate-900 rounded-md p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase">Full Marathon (42K)</p>
                                <p class="text-sm font-mono font-bold text-white mt-0.5">{{ $user->pb_fm }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Real Verified Reviews -->
                    @if($realReviews->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Ulasan Peserta Program</h4>
                        <div class="space-y-3">
                            @foreach($realReviews as $rev)
                                <div class="bg-slate-900 p-4 border border-slate-800 rounded-md space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-white">{{ $rev->runner?->name ?? 'Pelari' }}</span>
                                        <div class="flex text-amber-400 text-xs">
                                            @for($i=1; $i<=(int)$rev->rating; $i++)
                                                <i class="fas fa-star"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-300 leading-relaxed italic">
                                        "{{ $rev->review }}"
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Active Created Programs -->
                    @if($coachPrograms->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Daftar Program Latihan yang Dibuat</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            @foreach($coachPrograms as $p)
                                <div class="bg-slate-900/90 hover:bg-slate-900 p-4 border border-slate-800 hover:border-slate-700 rounded-md flex flex-col justify-between transition-colors group">
                                    <div>
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] font-mono font-semibold text-slate-300 border border-slate-700">
                                            {{ strtoupper($p->distance_target ?: 'GENERAL') }}
                                        </span>
                                        <h5 class="text-sm font-bold text-white mt-2 mb-1 group-hover:text-[#CCFF00] transition-colors">
                                            <a href="{{ route('programs.show', $p->slug) }}">{{ $p->title }}</a>
                                        </h5>
                                        <p class="text-xs text-slate-400 mb-3">{{ $p->duration_weeks }} Minggu • {{ $p->sessions_per_week }} Sesi/Minggu</p>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-800">
                                        <span class="text-sm font-bold font-mono text-white">
                                            {{ $p->price > 0 ? 'Rp ' . number_format($p->price, 0, ',', '.') : 'GRATIS' }}
                                        </span>
                                        <a href="{{ route('programs.show', $p->slug) }}" class="text-xs text-[#CCFF00] font-semibold hover:underline flex items-center gap-1">
                                            Detail Program &rarr;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
                @endif

                <!-- Gallery Carousel -->
                @if($user->profile_images && count($user->profile_images) > 0)
                <div class="glass-panel rounded-lg p-6">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Galeri Foto</h3>
                    <div class="flex overflow-x-auto gap-4 no-scrollbar snap-x snap-mandatory pb-2">
                        @foreach($user->profile_images as $image)
                            <div class="flex-none w-48 h-48 rounded-md overflow-hidden border border-slate-800 snap-center">
                                <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Recent Posts -->
                <div>
                    <h3 class="text-base font-bold text-white mb-4">Aktivitas Terbaru</h3>
                    <div class="space-y-4">
                        @forelse($user->posts()->latest()->take(5)->get() as $post)
                            <div class="glass-panel rounded-lg p-4">
                                <div class="flex items-start gap-4">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : ($user->gender === 'female' ? 'https://avatar.iran.liara.run/public/girl' : 'https://avatar.iran.liara.run/public/boy') }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                    <div class="flex-grow">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-sm font-bold text-white">{{ $user->name }}</h4>
                                                <p class="text-xs text-slate-500">{{ $post->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <p class="text-sm text-slate-300 mt-2">{{ $post->content }}</p>
                                        @if($post->images)
                                            <div class="mt-3 grid grid-cols-3 gap-2">
                                                @foreach(array_slice($post->images, 0, 3) as $img)
                                                    <img src="{{ asset('storage/' . $img) }}" class="rounded-lg h-20 w-full object-cover">
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                                            <span class="flex items-center gap-1"><i class="fas fa-heart"></i> {{ $post->likes_count }}</span>
                                            <span class="flex items-center gap-1"><i class="fas fa-comment"></i> {{ $post->comments_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-slate-500">
                                <p>No recent activity.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>
@endsection

@push('scripts')
@php
    $schemaPerson = [
        "@type" => "Person",
        "name" => $user->name,
        "identifier" => (string)($user->username ?: $user->id),
        "image" => $user->avatar_url,
        "description" => $user->bio ?: ($user->role === 'coach' ? 'Coach Lari Komunitas Ruang Lari' : 'Pelari Komunitas Ruang Lari'),
        "jobTitle" => ($user->role === 'coach' ? 'Running Coach' : 'Athlete / Runner'),
    ];
    if ($user->city) {
        $schemaPerson["address"] = [
            "@type" => "PostalAddress",
            "addressLocality" => $user->city->name,
            "addressCountry" => "ID"
        ];
    }
    $schema = [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "ProfilePage",
                "@id" => route('runner.profile.show', $user->username ?: $user->id) . '#webpage',
                "url" => route('runner.profile.show', $user->username ?: $user->id),
                "name" => $user->name . ' - Profil Pelari & Atlet | Ruang Lari',
                "description" => $user->bio ?: ('Profil performa lari ' . $user->name . ' di platform Ruang Lari.'),
                "mainEntity" => $schemaPerson
            ]
        ]
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
