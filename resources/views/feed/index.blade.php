@extends('layouts.app')

@section('title', 'Feed')

@section('page-title', 'Feed')

@push('styles')
<style>
    .content-body.default-height {
        overflow: visible !important;
        min-height: auto !important;
        height: auto !important;
    }
    .content-body .container-fluid {
        overflow: visible !important;
    }
    #posts-container {
        position: relative;
        z-index: 1;
        overflow: visible;
        margin-top: 0;
    }
    .feed-content-wrapper {
        position: relative;
        overflow: visible;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <!-- Create Post -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('feed.store') }}" method="POST" enctype="multipart/form-data" id="post-form">
                    @csrf
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" 
                             class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;" alt="{{ auth()->user()->name }}">
                        <div class="flex-grow-1">
                            <textarea name="content" class="form-control" rows="3" placeholder="Apa yang Anda pikirkan?" required></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="images[]" id="post-images" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Maksimal 5 gambar. Otomatis di-compress 75% dan convert ke WebP.</small>
                        <div id="image-preview" class="mt-2 row"></div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Post</button>
                    </div>
                </form>
                <!-- Posts List -->
                <div id="posts-container">
                @forelse($posts as $post)
                <div class="card mb-4" id="post-{{ $post->id }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : asset('images/profile/17.jpg') }}" 
                                class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;" alt="{{ $post->user->name }}">
                            <div class="flex-grow-1">
                                <h5 class="mb-0">
                                    <a href="{{ route('profile.show') }}?user={{ $post->user->id }}" class="text-dark">{{ $post->user->name }}</a>
                                </h5>
                                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                            @if($post->user_id === auth()->id() || auth()->user()->isAdmin())
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form action="{{ route('feed.destroy', $post) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus post ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fa fa-trash me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                        
                        <p class="mb-3">{{ $post->content }}</p>
                        
                        @if($post->images && count($post->images) > 0)
                        <div class="mb-3">
                            <div class="row g-2">
                                @foreach($post->images as $image)
                                <div class="col-md-{{ count($post->images) == 1 ? '12' : (count($post->images) == 2 ? '6' : '4') }}">
                                    <img src="{{ asset('storage/' . $image) }}" class="img-fluid rounded" alt="Post image" style="max-height: 400px; object-fit: cover; width: 100%;">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="d-flex align-items-center border-top pt-3">
                            <button type="button" class="btn btn-sm {{ $post->isLikedBy(auth()->user()) ? 'btn-primary' : 'btn-light' }} me-2 like-btn" 
                                    data-post-id="{{ $post->id }}">
                                <i class="fa fa-heart"></i> <span class="likes-count">{{ $post->likes_count }}</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-light me-2" data-bs-toggle="collapse" data-bs-target="#comments-{{ $post->id }}">
                                <i class="fa fa-comment"></i> <span>{{ $post->comments_count }}</span>
                            </button>
                        </div>
                        
                        <!-- Comments Section -->
                        <div class="collapse mt-3" id="comments-{{ $post->id }}">
                            <div class="border-top pt-3">
                                <form action="{{ route('feed.comment', $post) }}" method="POST" class="mb-3">
                                    @csrf
                                    <div class="input-group">
                                        <input type="text" name="comment" class="form-control" placeholder="Tulis komentar..." required>
                                        <button type="submit" class="btn btn-primary">Kirim</button>
                                    </div>
                                </form>
                                
                                @foreach($post->comments as $comment)
                                <div class="d-flex mb-2">
                                    <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : asset('images/profile/17.jpg') }}" 
                                        class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;" alt="{{ $comment->user->name }}">
                                    <div class="flex-grow-1">
                                        <div class="bg-light p-2 rounded">
                                            <strong>{{ $comment->user->name }}</strong>
                                            <p class="mb-0">{{ $comment->comment }}</p>
                                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted">Belum ada post. Mulai posting sekarang!</p>
                    </div>
                </div>
                @endforelse
                </div>

                @if($posts->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $posts->links() }}
                </div>
                @endif
            </div>
            </div>
        </div>
        
        
    
    <div class="col-xl-4 col-lg-5">
        <!-- Suggestions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Saran untuk Diikuti</h5>
            </div>
            <div class="card-body">
                @php
                    $suggestions = \App\Models\User::whereNotIn('id', auth()->user()->following()->pluck('following_id')->merge([auth()->id()]))
                        ->whereIn('role', ['runner', 'coach'])
                        ->limit(5)
                        ->get();
                @endphp
                @forelse($suggestions as $user)
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/17.jpg') }}" 
                         class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;" alt="{{ $user->name }}">
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ $user->name }}</h6>
                        <small class="text-muted">{{ ucfirst($user->role) }}</small>
                    </div>
                    @if(!auth()->user()->isFollowing($user))
                    <form action="{{ route('follow', $user) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Follow</button>
                    </form>
                    @endif
                </div>
                @empty
                <p class="text-muted mb-0">Tidak ada saran</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Image preview
    document.getElementById('post-images').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).slice(0, 5).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-2';
                col.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 150px; object-fit: cover;">`;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });

    // Form submission - reload page to show new post at top
    document.getElementById('post-form').addEventListener('submit', function(e) {
        // Form will submit normally, page will reload and new post will appear at top
    });

    // Like/Unlike functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const isLiked = this.classList.contains('btn-primary');
            
            fetch(isLiked ? `/feed/${postId}/unlike` : `/feed/${postId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                this.classList.toggle('btn-primary');
                this.classList.toggle('btn-light');
                this.querySelector('.likes-count').textContent = data.likes_count;
            });
        });
    });

    // Scroll to post when hash anchor is present
    window.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash) {
            const postId = window.location.hash.substring(1); // Remove #
            const postElement = document.getElementById(postId);
            if (postElement) {
                setTimeout(() => {
                    postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Highlight the post briefly
                    postElement.style.transition = 'box-shadow 0.3s';
                    postElement.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.5)';
                    setTimeout(() => {
                        postElement.style.boxShadow = '';
                    }, 2000);
                }, 100);
            }
        }
    });
</script>
@endpush
@endsection

