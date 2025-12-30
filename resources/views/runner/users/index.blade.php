@extends('layouts.app')

@section('title', 'Daftar Runner')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('runner.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Daftar Runner</a></li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    @forelse($users as $user)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="profile-photo mb-3">
                    <img src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : (str_starts_with($user->avatar, '/storage') ? asset(ltrim($user->avatar, '/')) : asset('storage/' . $user->avatar))) : asset('images/profile/17.jpg') }}" 
                         class="img-fluid rounded-circle" alt="{{ $user->name }}" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                @if($user->city)
                <p class="text-muted mb-3">
                    <i class="fa fa-map-marker"></i> {{ $user->city->name }}, {{ $user->city->province->name }}
                </p>
                @endif
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                    @if($user->package_tier)
                    <span class="badge badge-success">{{ ucfirst($user->package_tier) }}</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-center gap-2">
                    @if(auth()->id() !== $user->id)
                        @if(auth()->user()->isFollowing($user))
                            <form action="{{ route('unfollow', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-user-minus"></i> Unfollow
                                </button>
                            </form>
                        @else
                            <form action="{{ route('follow', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa fa-user-plus"></i> Follow
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('chat.show', $user) }}" class="btn btn-success btn-sm">
                            <i class="fa fa-comment"></i> Chat
                        </a>
                    @endif
                    
                    <a href="{{ route('profile.show') }}?user={{ $user->id }}" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i> Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted">Tidak ada runner ditemukan.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($users->hasPages())
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endif
@endsection












