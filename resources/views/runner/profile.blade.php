@extends('layouts.pacerhub')

@section('title', $user->name . ' - Runner Profile')

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
        <div class="glass-panel rounded-3xl overflow-hidden shadow-2xl mb-8 relative group">
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
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden border-4 border-slate-900 shadow-xl bg-slate-800">
                        <img loading="lazy" decoding="async" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : ($user->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg')) }}" class="w-full h-full object-cover">
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
                                @auth
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
                                    
                                    <a href="{{ route('chat.show', $user) }}" class="p-2.5 bg-slate-800 hover:bg-blue-500/20 hover:text-blue-400 border border-slate-700 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-neon hover:bg-white hover:text-dark text-dark rounded-xl text-sm font-black transition-all shadow-lg shadow-neon/20">
                                        Login to Follow
                                    </a>
                                @endauth
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
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Community Stats</h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-xl font-black text-white">{{ $user->posts()->count() }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">Posts</p>
                        </div>
                        <div>
                            <p class="text-xl font-black text-white">{{ $user->followers()->count() }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">Followers</p>
                        </div>
                        <div>
                            <p class="text-xl font-black text-white">{{ $user->following()->count() }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">Following</p>
                        </div>
                    </div>
                </div>

                <!-- Bio / Info -->
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">About</h3>
                    @if($user->bio)
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $user->bio }}</p>
                    @else
                        <p class="text-sm text-slate-500 italic">No bio added yet.</p>
                    @endif
                    
                    <div class="mt-4 pt-4 border-t border-slate-800 space-y-2">
                        <div class="flex items-center gap-2 text-sm text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Joined {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Gallery & Recent Activity -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Gallery Carousel -->
                @if($user->profile_images && count($user->profile_images) > 0)
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Gallery</h3>
                    <div class="flex overflow-x-auto gap-4 no-scrollbar snap-x snap-mandatory pb-2">
                        @foreach($user->profile_images as $image)
                            <div class="flex-none w-48 h-48 rounded-xl overflow-hidden border border-slate-700 snap-center">
                                <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Recent Posts -->
                <div>
                    <h3 class="text-lg font-bold text-white mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        @forelse($user->posts()->latest()->take(5)->get() as $post)
                            <div class="glass-panel rounded-2xl p-4">
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
