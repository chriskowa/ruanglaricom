@extends('layouts.app')

@section('title', 'Chat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->role . '.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Chat</a></li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Percakapan</h4>
            </div>
            <div class="card-body">
                @forelse($conversations as $userId => $messages)
                    @php
                        $lastMessage = $messages->first();
                        $otherUser = $lastMessage->sender_id === auth()->id() 
                            ? $lastMessage->receiver 
                            : $lastMessage->sender;
                    @endphp
                    <a href="{{ route('chat.show', $otherUser) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none">
                        <div class="me-3">
                            <img src="{{ $otherUser->avatar ? asset('storage/' . $otherUser->avatar) : asset('images/profile/17.jpg') }}" 
                                 class="rounded-circle" width="50" height="50" style="object-fit: cover;" alt="{{ $otherUser->name }}">
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 text-dark">{{ $otherUser->name }}</h5>
                            <p class="mb-0 text-muted">{{ Str::limit($lastMessage->message, 50) }}</p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">{{ $lastMessage->created_at->diffForHumans() }}</small>
                            @if($lastMessage->receiver_id === auth()->id() && !$lastMessage->is_read)
                                <span class="badge badge-primary">New</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center p-5">
                        <p class="text-muted">Belum ada percakapan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection












