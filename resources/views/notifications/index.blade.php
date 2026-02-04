@extends('layouts.pacerhub')

@section('title', 'Notifications')

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">Center</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Notifications</h1>
            </div>
            <button id="mark-all-read-btn" class="px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20 text-sm">Mark All Read</button>
        </div>

        <div class="space-y-3">
            @forelse($notifications as $notification)
                <div class="flex items-start gap-4 glass-panel rounded-2xl p-4 border {{ !$notification->is_read ? 'border-neon/30 bg-slate-800/40' : 'border-slate-700' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                        @if($notification->type === 'like') bg-red-500/20 text-red-400 border border-red-500/30
                        @elseif($notification->type === 'comment') bg-blue-500/20 text-blue-400 border border-blue-500/30
                        @elseif($notification->type === 'follow') bg-green-500/20 text-green-400 border border-green-500/30
                        @else bg-slate-700 text-slate-300 border border-slate-600
                        @endif">
                        @if($notification->type === 'like')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 6 4 4 6.5 4c1.54 0 3.04.99 3.57 2.36h.87C14.46 4.99 15.96 4 17.5 4 20 4 22 6 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        @elseif($notification->type === 'comment')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21 6h-2v9H7l-4 4V6a2 2 0 012-2h16a2 2 0 012 2z"/></svg>
                        @elseif($notification->type === 'follow')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.89 1.97 3.45V20h6v-3.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a2 2 0 002-2H10a2 2 0 002 2zm6.32-6a1 1 0 00.68-.94V11a6.002 6.002 0 00-5-5.91V4a1 1 0 10-2 0v1.09A6.002 6.002 0 006 11v4.06a1 1 0 00.68.94L8 17v1h8v-1l2.32-1z"/></svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                @if($notification->reference_type === 'Post' && $notification->reference_id)
                                    <a href="{{ route('feed.index') }}#post-{{ $notification->reference_id }}" class="notification-link text-white font-bold hover:text-neon transition" data-notification-id="{{ $notification->id }}">{{ $notification->title }}</a>
                                @elseif($notification->reference_type === 'EventSubmission' && $notification->reference_id && auth()->user()?->role === 'admin')
                                    <a href="{{ route('admin.event-submissions.show', $notification->reference_id) }}" class="notification-link text-white font-bold hover:text-neon transition" data-notification-id="{{ $notification->id }}">{{ $notification->title }}</a>
                                @else
                                    <a href="javascript:void(0)" class="notification-link text-white font-bold hover:text-neon transition" data-notification-id="{{ $notification->id }}">{{ $notification->title }}</a>
                                @endif
                                @if($notification->reference_type === 'Post' && $notification->reference_id)
                                    <a href="{{ route('feed.index') }}#post-{{ $notification->reference_id }}" class="notification-link block text-slate-300 text-sm hover:text-white transition" data-notification-id="{{ $notification->id }}">{{ $notification->message }}</a>
                                @elseif($notification->reference_type === 'EventSubmission' && $notification->reference_id && auth()->user()?->role === 'admin')
                                    <a href="{{ route('admin.event-submissions.show', $notification->reference_id) }}" class="notification-link block text-slate-300 text-sm hover:text-white transition" data-notification-id="{{ $notification->id }}">{{ $notification->message }}</a>
                                @else
                                    <a href="javascript:void(0)" class="notification-link block text-slate-300 text-sm hover:text-white transition" data-notification-id="{{ $notification->id }}">{{ $notification->message }}</a>
                                @endif
                                <div class="text-[11px] text-slate-500 font-mono">{{ $notification->created_at->format('d/m/Y, H.i.s') }}</div>
                            </div>
                            @if(!$notification->is_read)
                                <form action="{{ route('notifications.read', $notification) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-800 text-slate-300 text-xs font-bold hover:bg-neon hover:text-dark transition">Mark</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="glass-panel rounded-2xl p-10 text-center">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h9M3 20h9M12 4a8 8 0 110 16 8 8 0 010-16z" /></svg>
                    </div>
                    <h3 class="text-white font-bold mb-2">No Notifications</h3>
                    <p class="text-slate-400 text-sm">You’re all caught up.</p>
                </div>
            @endforelse
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        var linkEl = e.target.closest('.notification-link');
        if (!linkEl) return;
        var notificationId = linkEl.dataset.notificationId;
        if (!notificationId) return;
        e.preventDefault();
        fetch(`{{ route('notifications.read', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(function(res){ return res.json(); })
        .then(function(data){
            var item = linkEl.closest('.glass-panel') || linkEl.closest('.flex');
            if (item) {
                item.classList.remove('border-neon/30','bg-slate-800/40');
                item.classList.add('border-slate-700');
                var form = item.querySelector('form[action*="/notifications/"]');
                if (form) form.remove();
            }
            if (linkEl.href && linkEl.href !== 'javascript:void(0)') {
                window.location.href = linkEl.href;
            }
        })
        .catch(function(){
            if (linkEl.href && linkEl.href !== 'javascript:void(0)') {
                window.location.href = linkEl.href;
            }
        });
    });

    document.getElementById('mark-all-read-btn').addEventListener('click', function() {
        var btn = this;
        var original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Processing...';
        fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(function(res){ return res.json(); })
        .then(function(data){
            document.querySelectorAll('.border-neon/30').forEach(function(el){
                el.classList.remove('border-neon/30','bg-slate-800/40');
                el.classList.add('border-slate-700');
            });
            document.querySelectorAll('form[action*="/notifications/"]').forEach(function(f){ f.remove(); });
            var badge = document.querySelector('.notification-count');
            if (badge) { badge.textContent = '0'; badge.classList.add('d-none'); }
            btn.textContent = 'All Read';
            btn.classList.remove('bg-neon','text-dark');
            btn.classList.add('bg-slate-800','text-slate-300');
        })
        .catch(function(){
            btn.disabled = false;
            btn.textContent = original;
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    });
</script>
@endpush

