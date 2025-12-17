@extends('layouts.app')

@section('title', 'Notifikasi')

@section('page-title', 'Notifikasi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Notifikasi</h4>
                <button type="button" id="mark-all-read-btn" class="btn btn-sm btn-primary">Tandai Semua Sudah Dibaca</button>
            </div>
            <div class="card-body">
                @forelse($notifications as $notification)
                <div class="d-flex align-items-start mb-3 p-3 {{ !$notification->is_read ? 'bg-light' : '' }} rounded">
                    <div class="me-3">
                        @if($notification->type === 'like')
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-heart"></i>
                            </div>
                        @elseif($notification->type === 'comment')
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-comment"></i>
                            </div>
                        @elseif($notification->type === 'follow')
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-user-plus"></i>
                            </div>
                        @else
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-bell"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            @if($notification->reference_type === 'Post' && $notification->reference_id)
                                <a href="{{ route('feed.index') }}#post-{{ $notification->reference_id }}" 
                                   class="text-dark text-decoration-none notification-link" 
                                   data-notification-id="{{ $notification->id }}">
                                    {{ $notification->title }}
                                </a>
                            @else
                                <a href="javascript:void(0)" 
                                   class="text-dark text-decoration-none notification-link" 
                                   data-notification-id="{{ $notification->id }}">
                                    {{ $notification->title }}
                                </a>
                            @endif
                        </h6>
                        <p class="mb-1">
                            @if($notification->reference_type === 'Post' && $notification->reference_id)
                                <a href="{{ route('feed.index') }}#post-{{ $notification->reference_id }}" 
                                   class="text-dark text-decoration-none notification-link" 
                                   data-notification-id="{{ $notification->id }}">
                                    {{ $notification->message }}
                                </a>
                            @else
                                <a href="javascript:void(0)" 
                                   class="text-dark text-decoration-none notification-link" 
                                   data-notification-id="{{ $notification->id }}">
                                    {{ $notification->message }}
                                </a>
                            @endif
                        </p>
                        <small class="text-muted">{{ $notification->created_at->format('d/m/Y, H.i.s') }}</small>
                    </div>
                    @if(!$notification->is_read)
                    <form action="{{ route('notifications.read', $notification) }}" method="POST" class="ms-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light">
                            <i class="fa fa-check"></i>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="text-center p-5">
                    <p class="text-muted">Tidak ada notifikasi</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle notification click - mark as read
    document.addEventListener('click', function(e) {
        if (e.target.closest('.notification-link')) {
            const link = e.target.closest('.notification-link');
            const notificationId = link.dataset.notificationId;
            
            if (notificationId) {
                e.preventDefault(); // Prevent default link behavior
                
                // Mark as read via AJAX first
                fetch(`{{ route('notifications.read', ':id') }}`.replace(':id', notificationId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove unread styling
                        const notificationItem = link.closest('.d-flex');
                        if (notificationItem) {
                            notificationItem.classList.remove('bg-light');
                            // Remove the check button if exists
                            const checkButton = notificationItem.querySelector('form[action*="/notifications/"]');
                            if (checkButton) {
                                checkButton.remove();
                            }
                        }
                        
                        // Navigate to the link if it's not javascript:void(0)
                        if (link.href && link.href !== 'javascript:void(0)') {
                            window.location.href = link.href;
                        }
                    } else {
                        // Navigate anyway if response is not successful
                        if (link.href && link.href !== 'javascript:void(0)') {
                            window.location.href = link.href;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    // Navigate anyway if there's an error
                    if (link.href && link.href !== 'javascript:void(0)') {
                        window.location.href = link.href;
                    }
                });
            }
        }
    });

    // Mark all as read dengan AJAX
    document.getElementById('mark-all-read-btn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Memproses...';
        
        fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI - hapus background highlight dari notifikasi yang belum dibaca
                document.querySelectorAll('.bg-light').forEach(el => {
                    el.classList.remove('bg-light');
                });
                
                // Hapus tombol check dari notifikasi yang belum dibaca
                document.querySelectorAll('form[action*="/notifications/"]').forEach(form => {
                    form.remove();
                });
                
                // Update badge count di header jika ada
                const notificationCount = document.querySelector('.notification-count');
                if (notificationCount) {
                    notificationCount.textContent = '0';
                    notificationCount.classList.add('d-none');
                }
                
                btn.textContent = 'Semua Sudah Dibaca';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.textContent = originalText;
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    });
</script>
@endpush
@endsection



