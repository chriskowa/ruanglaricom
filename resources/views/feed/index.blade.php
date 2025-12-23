@extends('layouts.pacerhub')

@section('title', 'Community Feed')

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
    /* Hide scrollbar for gallery but keep functionality */
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
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200">
    <div class="max-w-7xl mx-auto">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Sidebar (Profile & Navigation) -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24 space-y-6">
                    <!-- Profile Card -->
                    <div class="glass-panel rounded-2xl p-6 text-center group">
                        <div class="relative w-24 h-24 mx-auto mb-4">
                            <div class="absolute inset-0 bg-neon rounded-full opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" 
                                 class="w-full h-full rounded-full object-cover border-2 border-slate-700 group-hover:border-neon transition-colors relative z-10" 
                                 alt="{{ auth()->user()->name }}">
                        </div>
                        <h3 class="text-xl font-bold text-white mb-1">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-neon font-medium uppercase tracking-wider mb-4">{{ auth()->user()->role }}</p>
                        
                        <div class="grid grid-cols-3 gap-2 border-t border-slate-800 pt-4 mb-4">
                            <div>
                                <span class="block text-lg font-black text-white">{{ auth()->user()->posts()->count() }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">Posts</span>
                            </div>
                            <div>
                                <span class="block text-lg font-black text-white">{{ auth()->user()->followers()->count() }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">Followers</span>
                            </div>
                            <div>
                                <span class="block text-lg font-black text-white">{{ auth()->user()->following()->count() }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">Following</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('profile.show') }}" class="block w-full py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold rounded-xl transition-colors">
                            View Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Feed -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- Create Post -->
                <div class="glass-panel rounded-2xl p-4">
                    <form action="{{ route('feed.store') }}" method="POST" enctype="multipart/form-data" id="post-form">
                        @csrf
                        <div class="flex gap-4 mb-4">
                            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" 
                                 class="w-10 h-10 rounded-full object-cover border border-slate-700 shrink-0">
                            <div class="flex-grow">
                                <textarea name="content" rows="2" class="w-full bg-slate-900/50 border-none rounded-xl p-3 text-white placeholder-slate-500 focus:ring-1 focus:ring-neon resize-none transition-all" placeholder="What's on your mind, {{ explode(' ', auth()->user()->name)[0] }}?" required></textarea>
                            </div>
                        </div>
                        
                        <!-- Image Preview -->
                        <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4 empty:hidden"></div>

                        <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                            <div class="flex gap-2">
                                <label class="cursor-pointer p-2 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-neon transition-colors" title="Add Photos">
                                    <input type="file" name="images[]" id="post-images" class="hidden" accept="image/*" multiple>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </label>
                                <!-- Additional attachments can go here -->
                            </div>
                            <button type="submit" class="px-6 py-2 bg-neon hover:bg-white hover:text-dark text-dark font-black rounded-lg text-sm transition-all shadow-lg shadow-neon/10">
                                POST
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Feed Posts -->
                <div id="posts-container" class="space-y-6">
                    @forelse($posts as $post)
                    <div class="glass-panel rounded-2xl p-0 overflow-hidden" id="post-{{ $post->id }}">
                        <!-- Post Header -->
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('profile.show', ['user' => $post->user->id]) }}">
                                    <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : asset('images/profile/17.jpg') }}" 
                                         class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                </a>
                                <div>
                                    <h4 class="font-bold text-white text-sm hover:text-neon transition-colors">
                                        <a href="{{ route('profile.show', ['user' => $post->user->id]) }}">{{ $post->user->name }}</a>
                                    </h4>
                                    <p class="text-xs text-slate-500">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            
                            @if($post->user_id === auth()->id() || auth()->user()->role === 'admin')
                            <div class="relative group">
                                <button class="p-2 text-slate-500 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-32 bg-slate-900 border border-slate-700 rounded-xl shadow-xl hidden group-hover:block z-20">
                                    <form action="{{ route('feed.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-800 rounded-xl transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="px-4 pb-2">
                            <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-wrap">{{ $post->content }}</p>
                        </div>

                        <!-- Images -->
                        @if($post->images && count($post->images) > 0)
                        <div class="mt-2 {{ count($post->images) > 1 ? 'grid grid-cols-2 gap-1' : '' }}">
                            @foreach($post->images as $index => $image)
                                <img src="{{ asset('storage/' . $image) }}" 
                                     class="w-full h-full object-cover {{ count($post->images) === 1 ? 'max-h-96' : 'aspect-square' }} hover:opacity-90 transition-opacity cursor-pointer" 
                                     alt="Post image">
                            @endforeach
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="px-4 py-3 flex items-center gap-6 border-t border-slate-800/50 mt-2">
                            <button class="flex items-center gap-2 text-sm text-slate-400 hover:text-red-500 transition-colors like-btn {{ $post->isLikedBy(auth()->user()) ? 'text-red-500' : '' }}" 
                                    data-post-id="{{ $post->id }}">
                                <svg class="w-5 h-5 {{ $post->isLikedBy(auth()->user()) ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                <span class="likes-count font-bold">{{ $post->likes_count }}</span>
                            </button>
                            
                            <button class="flex items-center gap-2 text-sm text-slate-400 hover:text-neon transition-colors" 
                                    onclick="document.getElementById('comments-{{ $post->id }}').classList.toggle('hidden')">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span class="font-bold">{{ $post->comments_count }}</span>
                            </button>
                        </div>

                        <!-- Comments Section -->
                        <div class="hidden bg-slate-900/30 border-t border-slate-800/50" id="comments-{{ $post->id }}">
                            <div class="p-4 space-y-4">
                                <!-- Comment List -->
                                @foreach($post->comments as $comment)
                                <div class="flex gap-3">
                                    <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : asset('images/profile/17.jpg') }}" 
                                         class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                    <div class="bg-slate-800/50 rounded-2xl rounded-tl-none px-4 py-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-white text-xs">{{ $comment->user->name }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-slate-300">{{ $comment->comment }}</p>
                                    </div>
                                </div>
                                @endforeach

                                <!-- Add Comment -->
                                <form action="{{ route('feed.comment', $post) }}" method="POST" class="flex gap-3 items-center mt-4">
                                    @csrf
                                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" 
                                         class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                    <div class="relative flex-grow">
                                        <input type="text" name="comment" class="w-full bg-slate-900 border border-slate-700 rounded-full px-4 py-2 text-sm text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Write a comment..." required>
                                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-neon hover:text-white transition-colors p-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="glass-panel rounded-2xl p-8 text-center">
                        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-600">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">No posts yet</h3>
                        <p class="text-slate-400">Be the first to share your running journey!</p>
                    </div>
                    @endforelse
                </div>

                @if($posts->hasPages())
                <div class="py-4 flex justify-center">
                    {{ $posts->links() }}
                </div>
                @endif
            </div>

            <!-- Right Sidebar (Suggestions) -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24">
                    <div class="glass-panel rounded-2xl p-6">
                        <h3 class="font-bold text-white mb-4 text-sm uppercase tracking-wider">Who to follow</h3>
                        <div class="space-y-4">
                            @php
                                $suggestions = \App\Models\User::whereNotIn('id', auth()->user()->following()->pluck('following_id')->merge([auth()->id()]))
                                    ->whereIn('role', ['runner', 'coach'])
                                    ->limit(5)
                                    ->get();
                            @endphp
                            
                            @forelse($suggestions as $user)
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/17.jpg') }}" 
                                         class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                    <div class="overflow-hidden">
                                        <h4 class="font-bold text-white text-sm truncate w-24">{{ $user->name }}</h4>
                                        <p class="text-[10px] text-slate-500 uppercase">{{ $user->role }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('follow', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg bg-slate-800 text-neon hover:bg-neon hover:text-dark transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    </button>
                                </form>
                            </div>
                            @empty
                            <p class="text-slate-500 text-sm">No new suggestions.</p>
                            @endforelse
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-slate-800">
                            <a href="{{ route('users.index') }}" class="text-xs text-neon hover:text-white font-bold transition-colors">
                                Find more runners &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Trending Topics (Placeholder) -->
                    <div class="glass-panel rounded-2xl p-6 mt-6">
                        <h3 class="font-bold text-white mb-4 text-sm uppercase tracking-wider">Trending</h3>
                        <div class="space-y-3">
                            <a href="#" class="block group">
                                <p class="text-xs text-slate-500 mb-0.5">Event • Live</p>
                                <h4 class="font-bold text-white text-sm group-hover:text-neon transition-colors">Jakarta Marathon 2024</h4>
                            </a>
                            <a href="#" class="block group">
                                <p class="text-xs text-slate-500 mb-0.5">Training</p>
                                <h4 class="font-bold text-white text-sm group-hover:text-neon transition-colors">#RoadToBaliMarathon</h4>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Image preview logic
    document.getElementById('post-images').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).slice(0, 4).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate-700 group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center">
                        <span class="text-white text-xs">Preview</span>
                    </div>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Like functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const isLiked = this.classList.contains('text-red-500');
            const icon = this.querySelector('svg');
            const countSpan = this.querySelector('.likes-count');
            
            // Optimistic UI update
            this.classList.toggle('text-red-500');
            if (isLiked) {
                icon.classList.remove('fill-current');
                icon.classList.add('fill-none');
                countSpan.textContent = parseInt(countSpan.textContent) - 1;
            } else {
                icon.classList.add('fill-current');
                icon.classList.remove('fill-none');
                countSpan.textContent = parseInt(countSpan.textContent) + 1;
            }
            
            fetch(isLiked ? `/feed/${postId}/unlike` : `/feed/${postId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Sync with server count just in case
                countSpan.textContent = data.likes_count;
            })
            .catch(err => {
                // Revert on error
                this.classList.toggle('text-red-500');
                if (isLiked) {
                    icon.classList.add('fill-current');
                    countSpan.textContent = parseInt(countSpan.textContent) + 1;
                } else {
                    icon.classList.remove('fill-current');
                    countSpan.textContent = parseInt(countSpan.textContent) - 1;
                }
            });
        });
    });
</script>
@endpush
