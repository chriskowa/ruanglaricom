@extends('layouts.app')

@section('title', 'Chat dengan ' . $user->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('chat.index') }}">Chat</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">{{ $user->name }}</a></li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/17.jpg') }}" 
                         class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;" alt="{{ $user->name }}">
                    <div>
                        <h5 class="mb-0">{{ $user->name }}</h5>
                        <small class="text-muted">{{ ucfirst($user->role) }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body" style="height: 500px; overflow-y: auto;">
                <div class="chat-messages">
                    @forelse($messages as $message)
                        <div class="d-flex mb-3 {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="message-wrapper {{ $message->sender_id === auth()->id() ? 'text-end' : 'text-start' }}" style="max-width: 70%;">
                                @if($message->sender_id !== auth()->id())
                                    <div class="mb-1">
                                        <strong>{{ $message->sender->name }}</strong>
                                    </div>
                                @endif
                                <div class="message-bubble p-3 rounded {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                                    {{ $message->message }}
                                </div>
                                <small class="text-muted d-block mt-1">
                                    {{ $message->created_at->format('H:i') }}
                                    @if($message->sender_id === auth()->id() && $message->is_read)
                                        <i class="fa fa-check-double text-primary"></i>
                                    @endif
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <p class="text-muted">Belum ada pesan. Mulai percakapan sekarang!</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer">
                <form action="{{ route('chat.store', $user) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="message" class="form-control" placeholder="Ketik pesan..." required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto scroll to bottom
    document.addEventListener('DOMContentLoaded', function() {
        const chatBody = document.querySelector('.card-body');
        chatBody.scrollTop = chatBody.scrollHeight;
    });
</script>
@endpush
@endsection












