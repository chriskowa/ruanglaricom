@extends('layouts.pacerhub')

@section('title', 'Messages')

@section('content')
<div class="flex h-[calc(100vh-64px)] overflow-hidden bg-dark pt-20">
    <!-- Sidebar List -->
    @include('chat.partials.sidebar', ['conversations' => $conversations])

    <!-- Empty State (Hidden on mobile if listing is shown, but here we just hide on mobile for now as index is list-only on mobile) -->
    <div class="hidden md:flex flex-1 flex-col items-center justify-center bg-slate-900/30 text-center p-8">
        <div class="w-24 h-24 bg-slate-800 rounded-full flex items-center justify-center mb-6 shadow-2xl shadow-black/50">
            <svg class="w-10 h-10 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
        <h2 class="text-2xl font-black text-white mb-2">Select a Conversation</h2>
        <p class="text-slate-400 max-w-sm">Choose a person from the left sidebar to start chatting or continue your conversation.</p>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ccff00; }
</style>
@push('scripts')
<script>
    function getAvatarUrl(path) {
        if (!path) return '/images/profile/17.jpg';
        if (path.indexOf('http') === 0) return path;
        
        var baseUrl = "{{ url('/') }}";
        var storageBase = "{{ asset('storage') }}";
        
        // If path already contains storage/, use baseUrl instead of storageBase
        if (path.indexOf('storage/') === 0 || path.indexOf('/storage/') === 0) {
             var cleanPath = path.indexOf('/') === 0 ? path.substring(1) : path;
             return baseUrl + '/' + cleanPath;
        }
        
        // Ensure slash between storageBase and path
        var prefix = storageBase;
        if (prefix.slice(-1) !== '/') prefix += '/';
        var cleanPath = path.indexOf('/') === 0 ? path.substring(1) : path;
        
        return prefix + cleanPath;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Update sidebar avatars
        document.querySelectorAll('img.sidebar-avatar').forEach(img => {
            img.src = getAvatarUrl(img.dataset.avatar);
        });
    });
</script>
@endpush
@endsection
