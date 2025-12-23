@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', $program->title)

@push('styles')
<script>
    tailwind.config.theme.extend.colors.neon = '#ccff00';
</script>
<style>
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .text-stroke {
        -webkit-text-stroke: 1px rgba(255, 255, 255, 0.1);
        color: transparent;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-20 font-sans text-slate-200">
    
    <!-- Hero Banner -->
    <div class="relative h-[50vh] min-h-[400px] w-full overflow-hidden">
        <!-- Background Image -->
        @if($program->banner)
            <img src="{{ $program->banner_url }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $program->title }}">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900 to-purple-900">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
            </div>
        @endif
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/60 to-transparent"></div>

        <!-- Content -->
        <div class="absolute bottom-0 left-0 w-full p-6 md:p-12">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap gap-3 mb-4" data-aos="fade-up">
                    <span class="px-3 py-1 rounded-full bg-neon text-dark text-xs font-black uppercase tracking-wider">
                        {{ $program->distance_target }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-white/10 backdrop-blur text-white text-xs font-bold uppercase tracking-wider border border-white/20">
                        {{ ucfirst($program->difficulty) }}
                    </span>
                </div>
                
                <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-4 max-w-4xl leading-tight" data-aos="fade-up" data-aos-delay="100">
                    {{ strtoupper($program->title) }}
                </h1>

                <div class="flex items-center gap-6 text-sm md:text-base text-slate-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center gap-2">
                        <img src="{{ ($program->coach && $program->coach->avatar) ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" class="w-8 h-8 rounded-full border border-slate-500">
                        <span>Coach <span class="text-white font-bold">{{ $program->coach->name ?? 'Unknown' }}</span></span>
                    </div>
                    <div class="w-1 h-1 bg-slate-600 rounded-full"></div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ $program->duration_weeks }} Weeks</span>
                    </div>
                    <div class="w-1 h-1 bg-slate-600 rounded-full"></div>
                    <div class="flex items-center gap-1">
                        <div class="flex text-yellow-500">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3 h-3 {{ $i <= round($program->average_rating) ? 'fill-current' : 'text-slate-600' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <span>({{ $program->reviews_count }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 -mt-10 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- About -->
                <div class="glass-panel rounded-2xl p-8" data-aos="fade-up">
                    <h3 class="text-xl font-bold text-white mb-4 border-b border-slate-800 pb-4">About the Program</h3>
                    <div class="prose prose-invert prose-sm max-w-none text-slate-300">
                        {!! nl2br(e($program->description)) !!}
                    </div>
                </div>

                <!-- Preview Schedule -->
                @if($program->program_json && isset($program->program_json['sessions']))
                <div class="glass-panel rounded-2xl p-8" data-aos="fade-up">
                    <h3 class="text-xl font-bold text-white mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
                        <span>Sample Schedule</span>
                        <span class="text-xs font-normal text-slate-500 bg-slate-800 px-3 py-1 rounded-full">Week 1 Preview</span>
                    </h3>
                    <div class="space-y-3">
                        @foreach(array_slice($program->program_json['sessions'], 0, 7) as $session)
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-slate-600 transition-colors group">
                            <div class="w-12 h-12 rounded-lg bg-slate-900 flex flex-col items-center justify-center shrink-0 border border-slate-700 group-hover:border-neon/50 transition-colors">
                                <span class="text-[10px] text-slate-500 uppercase">Day</span>
                                <span class="text-lg font-black text-white group-hover:text-neon">{{ $session['day'] ?? '-' }}</span>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-white group-hover:text-neon transition-colors">{{ ucfirst(str_replace('_', ' ', $session['type'] ?? 'Run')) }}</h4>
                                <p class="text-sm text-slate-400">{{ $session['description'] ?? 'No description' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-sm font-bold text-white">{{ $session['distance'] ?? '-' }} km</div>
                                <div class="text-xs text-slate-500">{{ $session['duration'] ?? '-' }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Reviews -->
                <div class="glass-panel rounded-2xl p-8" data-aos="fade-up">
                    <h3 class="text-xl font-bold text-white mb-6 border-b border-slate-800 pb-4">Runner Reviews</h3>
                    @if($reviews->count() > 0)
                        <div class="space-y-6">
                            @foreach($reviews as $review)
                            <div class="flex gap-4">
                                <img src="{{ ($review->runner && $review->runner->avatar) ? asset('storage/' . $review->runner->avatar) : asset('images/profile/17.jpg') }}" class="w-10 h-10 rounded-full object-cover border border-slate-600">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h5 class="font-bold text-white text-sm">{{ $review->runner->name ?? 'Runner' }}</h5>
                                        <span class="text-xs text-slate-500">• {{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex text-yellow-500 text-xs mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-slate-700' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="text-sm text-slate-300">{{ $review->review }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-500">
                            <p>No reviews yet. Be the first to review!</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    
                    <!-- Pricing Card -->
                    <div class="glass-panel rounded-2xl p-6 border-t-4 border-neon shadow-2xl shadow-neon/10">
                        <div class="text-center mb-6">
                            <p class="text-slate-400 text-sm mb-1 uppercase tracking-wider">Total Price</p>
                            @if($program->isFree())
                                <h2 class="text-4xl font-black text-white">FREE</h2>
                            @else
                                <h2 class="text-4xl font-black text-white">Rp {{ number_format($program->price, 0, ',', '.') }}</h2>
                            @endif
                        </div>

                        @if($isEnrolled)
                            <div class="bg-green-500/20 border border-green-500/50 rounded-xl p-4 text-center mb-4">
                                <p class="text-green-400 font-bold flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    You are enrolled
                                </p>
                            </div>
                            <a href="{{ route('runner.calendar') }}" class="block w-full py-4 bg-slate-800 hover:bg-slate-700 text-white font-bold text-center rounded-xl transition-all mb-3">
                                Go to Calendar
                            </a>
                        @else
                            @if($program->isFree())
                                <form action="{{ route('runner.programs.enroll-free', $program->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-lg rounded-xl transition-all shadow-lg shadow-neon/20 mb-3">
                                        JOIN NOW
                                    </button>
                                </form>
                            @else
                                @auth
                                    @if(auth()->user()->role === 'runner')
                                        <form action="{{ route('marketplace.cart.add', $program->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-lg rounded-xl transition-all shadow-lg shadow-neon/20 mb-3">
                                                ADD TO CART
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-center p-4 bg-slate-800 rounded-xl mb-3">
                                            <p class="text-sm text-slate-400">Please login as a Runner to purchase.</p>
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="block w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-lg rounded-xl transition-all shadow-lg shadow-neon/20 mb-3 text-center">
                                        LOGIN TO JOIN
                                    </a>
                                @endauth
                            @endif
                        @endif

                        <div class="text-center">
                            <p class="text-xs text-slate-500">100% Satisfaction Guarantee</p>
                        </div>
                    </div>

                    <!-- Coach Card -->
                    <div class="glass-panel rounded-2xl p-6">
                        <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Your Coach</h4>
                        <div class="flex items-center gap-4 mb-4">
                            <img src="{{ ($program->coach && $program->coach->avatar) ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" class="w-16 h-16 rounded-full object-cover border-2 border-slate-600">
                            <div>
                                <h5 class="font-bold text-white text-lg">{{ $program->coach->name ?? 'Unknown' }}</h5>
                                <p class="text-xs text-neon">Certified Coach</p>
                            </div>
                        </div>
                        <div class="flex justify-between text-center border-t border-slate-800 pt-4">
                            <div>
                                <p class="text-lg font-bold text-white">{{ $program->coach->programs()->count() }}</p>
                                <p class="text-[10px] text-slate-500 uppercase">Programs</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-white">{{ \App\Models\ProgramEnrollment::whereHas('program', fn($q)=>$q->where('coach_id', $program->coach->id))->count() }}</p>
                                <p class="text-[10px] text-slate-500 uppercase">Students</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-white">4.9</p>
                                <p class="text-[10px] text-slate-500 uppercase">Rating</p>
                            </div>
                        </div>
                        <a href="#" class="block w-full py-2 mt-4 border border-slate-600 hover:border-white text-slate-300 hover:text-white font-bold text-center rounded-lg text-sm transition-all">
                            View Profile
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Mobile Bottom Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 w-full bg-slate-900 border-t border-slate-800 p-4 z-50 pb-safe">
        <div class="flex items-center gap-4">
            <div class="flex-grow">
                <p class="text-xs text-slate-400">Total Price</p>
                <p class="text-xl font-black text-white">{{ $program->isFree() ? 'FREE' : 'Rp ' . number_format($program->price, 0, ',', '.') }}</p>
            </div>
            <div class="flex-grow-0">
                @if($isEnrolled)
                    <a href="{{ route('runner.calendar') }}" class="px-6 py-3 bg-green-600 text-white font-bold rounded-xl text-sm">Open</a>
                @else
                    @if($program->isFree())
                        <form action="{{ route('runner.programs.enroll-free', $program->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-3 bg-neon text-dark font-black rounded-xl text-sm shadow-lg shadow-neon/20">JOIN</button>
                        </form>
                    @else
                        @auth
                            <form action="{{ route('marketplace.cart.add', $program->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-neon text-dark font-black rounded-xl text-sm shadow-lg shadow-neon/20">BUY</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3 bg-neon text-dark font-black rounded-xl text-sm shadow-lg shadow-neon/20">LOGIN</a>
                        @endauth
                    @endif
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
