@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title'){{ $seoTitle }}@endsection
@section('meta_title'){{ $seoTitle }}@endsection
@section('meta_description'){{ $seoDesc }}@endsection
@section('og_image'){{ $program->banner_url ?? $program->image_url ?? asset('images/product/program lari ruang lari.webp') }}@endsection

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
    /* Fix chat box overlap with mobile join bar */
    #chatbox-toggle {
        transition: bottom 0.3s ease-in-out, transform 0.3s ease;
    }
    @media (max-width: 1023px) {
        #chatbox-toggle {
            bottom: calc(1rem + env(safe-area-inset-bottom) + 4.5rem) !important;
            z-index: 40 !important;
        }
        #ph-chatbox {
            bottom: calc(1rem + env(safe-area-inset-bottom) + 8rem) !important;
            left: 1rem !important;
            right: 1rem !important;
            width: auto !important;
            z-index: 40 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen pt-0 pb-28 lg:pb-16 font-sans bg-dark text-slate-200">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-5 sm:py-6">
        
        <!-- Breadcrumbs Navigation -->
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-slate-400 mb-5">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a>
            <span class="text-slate-600">/</span>
            <a href="{{ url('/programs') }}" class="hover:text-white transition-colors">Programs</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-200 font-medium truncate max-w-[200px] sm:max-w-md">{{ $program->title }}</span>
        </nav>

        <!-- Main Product Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
            
            <!-- Left / Main Column (8 cols on lg) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Main Header & Feature Image Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 sm:p-6 space-y-4">
                    
                    <!-- Feature Image: Single, Crisp & Responsive -->
                    <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] lg:aspect-[16/9] rounded-md overflow-hidden bg-slate-950 border border-slate-800">
                        <img src="{{ $program->banner_url ?? $program->image_url ?? asset('images/product/program lari ruang lari.webp') }}" 
                             alt="{{ $program->title }}" 
                             class="w-full h-full object-cover">
                        
                        <!-- Top-Right Badges on Image -->
                        <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5 z-10">
                            <span class="px-2 py-0.5 rounded bg-slate-950/90 text-slate-200 text-[11px] font-semibold border border-slate-700">
                                {{ strtoupper($program->distance_target) }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-neon text-slate-950 text-[11px] font-semibold">
                                {{ ucfirst($program->difficulty) }}
                            </span>
                            @if($program->isFree())
                                <span class="px-2 py-0.5 rounded bg-emerald-500 text-slate-950 text-[11px] font-bold">
                                    GRATIS
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Program Title -->
                    <h1 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold text-white tracking-tight leading-snug">
                        {{ $program->title }}
                    </h1>

                    <!-- Rating & Metadata Row -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs sm:text-sm text-slate-300 pt-3 border-t border-slate-800">
                        
                        <!-- Coach Info -->
                        <div class="flex items-center gap-1.5">
                            <img src="{{ ($program->coach && $program->coach->avatar) ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" 
                                 class="w-5 h-5 rounded-full border border-slate-700 object-cover" 
                                 alt="Coach {{ $program->coach->name ?? 'Ruang Lari' }}">
                            <span>Coach <a href="{{ route('runner.profile.show', $program->coach->username ?? $program->coach->id) }}" class="text-white font-medium hover:text-neon transition-colors">{{ $program->coach->name ?? 'Unknown' }}</a></span>
                        </div>

                        <span class="text-slate-700 hidden sm:inline">•</span>

                        <!-- Duration -->
                        <div class="flex items-center gap-1 text-slate-300">
                            <svg class="w-3.5 h-3.5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $program->duration_weeks }} Minggu</span>
                        </div>

                        <span class="text-slate-700 hidden sm:inline">•</span>

                        <!-- Star Rating & Review Count -->
                        <div class="flex items-center gap-1.5">
                            @auth
                            @if(auth()->user()->role === 'runner')
                            <form id="rating-form" action="{{ route('runner.programs.reviews.store', $program->id) }}" method="POST" class="flex text-amber-400">
                                @csrf
                                <input type="hidden" name="rating" value="0">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg data-value="{{ $i }}" class="rating-star w-3.5 h-3.5 {{ $i <= round($program->average_rating) ? 'fill-current' : 'text-slate-700' }} cursor-pointer hover:scale-110 transition-transform" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </form>
                            @else
                            <div class="flex text-amber-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= round($program->average_rating) ? 'fill-current' : 'text-slate-700' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            @endif
                            @endauth
                            @guest
                            <div class="flex text-amber-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= round($program->average_rating) ? 'fill-current' : 'text-slate-700' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            @endguest
                            <span class="text-xs font-semibold text-white">{{ number_format($program->average_rating ?: 5, 1) }}</span>
                            <span id="program-review-count" class="text-xs text-slate-400 font-mono">({{ $program->total_reviews }})</span>
                        </div>
                    </div>

                    <!-- Quick Specs Strip -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-800">
                        <div class="p-2.5 rounded-md bg-slate-950/60 border border-slate-800 text-center">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide">Target</p>
                            <p class="text-xs sm:text-sm font-semibold text-white">{{ strtoupper($program->distance_target) }}</p>
                        </div>
                        <div class="p-2.5 rounded-md bg-slate-950/60 border border-slate-800 text-center">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide">Level</p>
                            <p class="text-xs sm:text-sm font-semibold text-white">{{ ucfirst($program->difficulty) }}</p>
                        </div>
                        <div class="p-2.5 rounded-md bg-slate-950/60 border border-slate-800 text-center">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide">Durasi</p>
                            <p class="text-xs sm:text-sm font-semibold text-white">{{ $program->duration_weeks }} Minggu</p>
                        </div>
                        <div class="p-2.5 rounded-md bg-slate-950/60 border border-slate-800 text-center">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide">Akses</p>
                            <p class="text-xs sm:text-sm font-semibold text-neon">{{ $program->isFree() ? 'Gratis' : 'Sekali Bayar' }}</p>
                        </div>
                    </div>

                </div>

                <!-- About the Program -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-white pb-3 border-b border-slate-800 mb-4">
                        Tentang Program
                    </h2>
                    <div class="prose prose-invert prose-sm max-w-none text-slate-300 leading-relaxed text-xs sm:text-sm">
                        {!! $program->description !!}
                    </div>
                </div>

                <!-- Preview Schedule -->
                @if($program->program_json && isset($program->program_json['sessions']) && count($program->program_json['sessions']) > 0)
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 sm:p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                        <h2 class="text-base sm:text-lg font-semibold text-white">
                            Pratinjau Jadwal Latihan
                        </h2>
                        <span class="text-[11px] font-medium text-slate-400 bg-slate-800 px-2 py-0.5 rounded">
                            Minggu 1
                        </span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach(array_slice($program->program_json['sessions'], 0, 7) as $session)
                        <div class="flex items-center gap-3 p-3 rounded-md bg-slate-950/60 border border-slate-800 hover:border-slate-700 transition-colors">
                            <div class="w-10 h-10 rounded bg-slate-900 flex flex-col items-center justify-center shrink-0 border border-slate-800">
                                <span class="text-[9px] text-slate-400 uppercase">Hari</span>
                                <span class="text-xs sm:text-sm font-bold text-white">{{ $session['day'] ?? '-' }}</span>
                            </div>
                            <div class="flex-grow min-w-0">
                                <h3 class="font-semibold text-white text-xs sm:text-sm truncate">{{ ucfirst(str_replace('_', ' ', $session['type'] ?? 'Run')) }}</h3>
                                <p class="text-[11px] sm:text-xs text-slate-400 line-clamp-1">{{ $session['description'] ?? 'Tidak ada deskripsi' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs font-semibold text-white">{{ $session['distance'] ?? '-' }} km</div>
                                <div class="text-[10px] text-slate-400">{{ $session['duration'] ?? '-' }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Reviews Section -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-white pb-3 border-b border-slate-800 mb-4">
                        Ulasan Pelari
                    </h2>
                    
                    @auth
                    @if(auth()->user()->role === 'runner')
                    <div class="mb-5 p-3.5 rounded-md bg-slate-950/60 border border-slate-800">
                        <form id="runner-review-form" action="{{ route('runner.programs.reviews.store', $program->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs font-medium text-slate-300">Beri Rating</label>
                                    <select name="rating" class="mt-1 w-full px-2.5 py-1.5 rounded-md bg-slate-900 border border-slate-700 text-slate-200 text-xs focus:border-neon focus:ring-1 focus:ring-neon focus:outline-none">
                                        @for($r = 5; $r >= 1; $r--)
                                            <option value="{{ $r }}">{{ $r }} Bintang</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-medium text-slate-300">Komentar / Pengalaman</label>
                                    <textarea name="review" rows="2" class="mt-1 w-full px-2.5 py-1.5 rounded-md bg-slate-900 border border-slate-700 text-slate-200 text-xs focus:border-neon focus:ring-1 focus:ring-neon focus:outline-none placeholder-slate-500" placeholder="Ceritakan pengalaman latihan Anda..."></textarea>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="px-4 py-1.5 rounded-md bg-neon hover:bg-neon/90 text-slate-950 font-semibold text-xs transition-colors">
                                    Kirim Ulasan
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                    @endauth

                    @if($reviews->count() > 0)
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                            <div class="flex gap-3 pb-3 border-b border-slate-800/80 last:border-b-0">
                                <img src="{{ ($review->runner && $review->runner->avatar) ? asset('storage/' . $review->runner->avatar) : asset('images/profile/17.jpg') }}" 
                                     class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0" 
                                     alt="{{ $review->runner->name ?? 'Runner' }}">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold text-white text-xs sm:text-sm">{{ $review->runner->name ?? 'Runner' }}</span>
                                        <span class="text-[11px] text-slate-500 font-mono">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex text-amber-400 text-xs mb-1.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-slate-700' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">{{ $review->review }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-400 text-xs sm:text-sm">
                            <p>Belum ada ulasan untuk program ini. Jadilah yang pertama memberikan ulasan!</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right / Sticky Sidebar (4 cols on lg) -->
            <div class="lg:col-span-4">
                <div class="sticky top-24 space-y-4">
                    
                    <!-- Pricing Card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <div class="mb-4">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Total Biaya Program</p>
                            @if($program->isFree())
                                <p class="text-2xl sm:text-3xl font-bold text-white">GRATIS</p>
                            @else
                                <p class="text-2xl sm:text-3xl font-bold text-white">Rp {{ number_format($program->price, 0, ',', '.') }}</p>
                            @endif
                        </div>

                        @if($isEnrolled)
                            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-md p-3 text-center mb-3">
                                <p class="text-emerald-400 font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Anda sudah terdaftar di program ini
                                </p>
                            </div>
                            <a href="{{ route('runner.calendar') }}" class="block w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-center rounded-md text-xs sm:text-sm transition-colors mb-3">
                                Buka Jadwal Latihan
                            </a>
                        @else
                            @if($program->isFree())
                                <form action="{{ route('runner.programs.enroll-free', $program->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3 bg-neon hover:bg-neon/90 text-slate-950 font-semibold text-sm rounded-md transition-colors mb-3">
                                        Ikuti Program Gratis
                                    </button>
                                </form>
                            @else
                                @auth
                                    @if(auth()->user()->role === 'runner')
                                        <form action="{{ route('marketplace.cart.add', $program->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full py-3 bg-neon hover:bg-neon/90 text-slate-950 font-semibold text-sm rounded-md transition-colors mb-3">
                                                Beli & Checkout
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-center p-3 bg-slate-800/80 border border-slate-700 rounded-md mb-3">
                                            <p class="text-xs text-slate-300">Silakan login sebagai Runner untuk membeli program.</p>
                                        </div>
                                    @endif
                                @endauth
                                @guest
                                    <button onclick="openLoginModal()" class="w-full py-3 bg-neon hover:bg-neon/90 text-slate-950 font-semibold text-sm rounded-md transition-colors mb-3 text-center">
                                        Login untuk Mengikuti
                                    </button>
                                @endguest
                            @endif
                        @endif

                        <div class="text-center pt-1 border-t border-slate-800">
                            <p class="text-[11px] text-slate-400">Akses langsung ke kalender & sinkronisasi latihan harian</p>
                        </div>
                    </div>

                    <!-- Coach Card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                            Pelatih Program
                        </h3>
                        <div class="flex items-center gap-3 mb-4">
                            <img src="{{ ($program->coach && $program->coach->avatar) ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" 
                                 class="w-12 h-12 rounded-full object-cover border border-slate-700" 
                                 alt="Coach {{ $program->coach->name ?? 'Ruang Lari' }}">
                            <div>
                                <h4 class="font-bold text-white text-sm sm:text-base">{{ $program->coach->name ?? 'Unknown' }}</h4>
                                <p class="text-xs text-neon font-medium">Coach Terverifikasi</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center border-y border-slate-800 py-3 mb-3">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $program->coach ? $program->coach->programs()->count() : 1 }}</p>
                                <p class="text-[10px] text-slate-400 uppercase">Program</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ $program->coach ? \App\Models\ProgramEnrollment::whereHas('program', fn($q)=>$q->where('coach_id', $program->coach->id))->count() : 0 }}</p>
                                <p class="text-[10px] text-slate-400 uppercase">Atlet</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">4.9</p>
                                <p class="text-[10px] text-slate-400 uppercase">Rating</p>
                            </div>
                        </div>
                        @if($program->coach)
                        <a href="{{ route('runner.profile.show', $program->coach->username ?? $program->coach->id) }}" 
                           class="block w-full py-2 border border-slate-700 hover:border-slate-500 text-slate-200 hover:text-white font-medium text-center rounded-md text-xs transition-colors">
                            Lihat Profil Coach
                        </a>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Mobile Bottom Action Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 w-full bg-slate-900/95 backdrop-blur-md border-t border-slate-800 px-4 py-3 z-40 pb-safe">
        <div class="flex items-center justify-between gap-3 max-w-lg mx-auto">
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Total Biaya</p>
                <p class="text-base font-bold text-white">
                    {{ $program->isFree() ? 'GRATIS' : 'Rp ' . number_format($program->price, 0, ',', '.') }}
                </p>
            </div>
            <div>
                @if($isEnrolled)
                    <a href="{{ route('runner.calendar') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-md text-xs sm:text-sm transition-colors">
                        Buka Program
                    </a>
                @else
                    @if($program->isFree())
                        <form action="{{ route('runner.programs.enroll-free', $program->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 bg-neon hover:bg-neon/90 text-slate-950 font-semibold rounded-md text-xs sm:text-sm transition-colors">
                                Ikuti Gratis
                            </button>
                        </form>
                    @else
                        @auth
                            @if(auth()->user()->role === 'runner')
                                <form action="{{ route('marketplace.cart.add', $program->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-5 py-2.5 bg-neon hover:bg-neon/90 text-slate-950 font-semibold rounded-md text-xs sm:text-sm transition-colors">
                                        Beli Sekarang
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400 font-medium px-2">Khusus Runner</span>
                            @endif
                        @endauth
                        @guest
                            <button onclick="openLoginModal()" class="px-5 py-2.5 bg-neon hover:bg-neon/90 text-slate-950 font-semibold rounded-md text-xs sm:text-sm transition-colors">
                                Login
                            </button>
                        @endguest
                    @endif
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('rating-form');
    if (!form) return;
    var stars = form.querySelectorAll('.rating-star');
    var input = form.querySelector('input[name="rating"]');
    var countEl = document.getElementById('program-review-count');
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    stars.forEach(function(star){
        star.addEventListener('click', function(){
            var value = parseInt(star.dataset.value);
            input.value = value;
            var fd = new FormData();
            fd.append('rating', value);
            if (token) fd.append('_token', token);
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd
            }).then(function(r){ return r.json(); }).then(function(data){
                if (data && data.success) {
                    var rounded = parseInt(data.average_rating_rounded || Math.round(data.average_rating || value));
                    stars.forEach(function(s){
                        var v = parseInt(s.dataset.value);
                        if (v <= rounded) { s.classList.add('fill-current'); s.classList.remove('text-slate-700'); }
                        else { s.classList.remove('fill-current'); s.classList.add('text-slate-700'); }
                    });
                    if (countEl && typeof data.total_reviews !== 'undefined') {
                        countEl.textContent = '(' + data.total_reviews + ')';
                    }
                } else {
                    alert((data && data.message) ? data.message : 'Gagal menyimpan rating');
                }
            }).catch(function(){ alert('Terjadi kesalahan'); });
        });
    });

    var reviewForm = document.getElementById('runner-review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e){
            e.preventDefault();
            var fd = new FormData(reviewForm);
            if (token && !fd.get('_token')) fd.append('_token', token);
            fetch(reviewForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd
            }).then(function(r){ return r.json(); }).then(function(data){
                if (data && data.success) {
                    if (countEl && typeof data.total_reviews !== 'undefined') {
                        countEl.textContent = '(' + data.total_reviews + ')';
                    }
                    alert('Review berhasil disimpan');
                } else {
                    alert((data && data.message) ? data.message : 'Gagal menyimpan review');
                }
            }).catch(function(){ alert('Terjadi kesalahan'); });
        });
    }
});
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "Product",
      "@@id": "{{ url('/programs/' . $program->slug) }}#product",
      "name": {!! json_encode($seoTitle) !!},
      "description": {!! json_encode(Str::limit(strip_tags($program->description), 200)) !!},
      "image": "{{ $program->image_url ?? $program->banner_url ?? asset('images/product/program lari ruang lari.webp') }}",
      "category": "Sports",
      "brand": {
        "@@type": "Brand",
        "name": "Ruang Lari"
      },
      "provider": {
        "@@type": "Person",
        "name": {!! json_encode($coachName) !!},
        "url": "{{ $program->coach ? route('runner.profile.show', $program->coach->username ?? $program->coach->id) : '#' }}"
      },
      "offers": {
        "@@type": "Offer",
        "url": "{{ url('/programs/' . $program->slug) }}",
        "priceCurrency": "IDR",
        "price": "{{ $program->price }}",
        "availability": "{{ $program->is_active ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
      }
      @if($program->average_rating && $program->total_reviews > 0)
      ,"aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "{{ $program->average_rating }}",
        "reviewCount": "{{ $program->total_reviews }}"
      }
      @endif
      @if($reviews->count() > 0)
      ,"review": [
        @foreach($reviews as $rev)
        {
          "@@type": "Review",
          "author": {
            "@@type": "Person",
            "name": {!! json_encode($rev->runner->name ?? 'Runner') !!}
          },
          "datePublished": "{{ $rev->created_at->toIso8601String() }}",
          "reviewBody": {!! json_encode($rev->review) !!},
          "reviewRating": {
            "@@type": "Rating",
            "ratingValue": "{{ $rev->rating }}"
          }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
      ]
      @endif
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/programs/' . $program->slug) }}#breadcrumb",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ url('/') }}"
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": "Programs",
          "item": "{{ url('/programs') }}"
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "name": {!! json_encode($program->title) !!},
          "item": "{{ url('/programs/' . $program->slug) }}"
        }
      ]
    }
  ]
}
</script>
@endpush
