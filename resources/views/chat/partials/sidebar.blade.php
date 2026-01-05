<div class="{{ isset($activeUserId) ? 'hidden md:flex w-80 lg:w-96' : 'w-full md:w-80 lg:w-96' }} border-r border-slate-800 flex flex-col bg-card" id="chat-sidebar">
    <!-- Header Sidebar -->
    <div class="p-4 border-b border-slate-800 flex justify-between items-center">
        <h2 class="text-xl font-black text-white italic tracking-tighter">MESSAGES</h2>
        @if(isset($activeUserId))
            <a href="{{ route('chat.index') }}" class="text-slate-400 hover:text-white transition md:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </a>
        @endif
        <span class="px-2 py-1 rounded bg-slate-800 text-xs text-slate-400 font-mono hidden md:inline-block">{{ $conversations->count() }} chats</span>
    </div>

    <!-- Search -->
    <div class="p-3 border-b border-slate-800">
        <div class="relative">
            <input type="text" placeholder="Search messages..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-neon transition-colors">
            <svg class="w-4 h-4 text-slate-500 absolute right-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    <!-- Conversation List -->
    <div class="flex-1 overflow-y-auto custom-scrollbar" id="conversations-list">
        @forelse($conversations as $userId => $msgs)
            @php
                $lastMessage = $msgs->first();
                $otherUser = $lastMessage->sender_id === auth()->id() 
                    ? $lastMessage->receiver 
                    : $lastMessage->sender;
                $isActive = isset($activeUserId) && $otherUser->id === $activeUserId;
                $unreadCount = $msgs->where('receiver_id', auth()->id())->where('is_read', false)->count();
            @endphp
            <a href="{{ route('chat.show', $otherUser) }}" class="block p-4 border-b border-slate-800/50 transition-colors group {{ $isActive ? 'bg-slate-800 border-l-2 border-l-neon' : 'hover:bg-slate-800/50' }}">
                <div class="flex items-start gap-3">
                    <div class="relative">
                        <img src="/images/profile/17.jpg"
                             data-avatar="{{ $otherUser->avatar }}"
                             class="sidebar-avatar w-12 h-12 rounded-full object-cover border-2 {{ $isActive ? 'border-neon' : 'border-slate-700 group-hover:border-neon' }} transition-colors" 
                             alt="{{ $otherUser->name }}">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-sm font-bold {{ $isActive ? 'text-white' : 'text-slate-300 group-hover:text-white' }} truncate">{{ $otherUser->name }}</h3>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $lastMessage->created_at->diffForHumans(null, true, true) }}</span>
                        </div>
                        <p class="text-xs truncate {{ $isActive ? 'text-slate-300' : 'text-slate-500' }} {{ $unreadCount > 0 ? 'font-bold text-white' : '' }}">
                            @if($lastMessage->sender_id === auth()->id())
                                <span class="text-neon">You:</span>
                            @endif
                            {{ Str::limit($lastMessage->message, 40) }}
                        </p>
                    </div>
                    @if($unreadCount > 0)
                        <div class="ml-2 flex-shrink-0">
                            <span class="px-2 py-0.5 rounded-full bg-neon text-dark text-[10px] font-black">{{ $unreadCount }}</span>
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-slate-500 text-sm">No conversations yet.</div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[placeholder="Search messages..."]');
        const list = document.getElementById('conversations-list');
        if(searchInput && list) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const items = list.querySelectorAll('a');
                items.forEach(item => {
                    const name = item.querySelector('h3').textContent.toLowerCase();
                    const msg = item.querySelector('p').textContent.toLowerCase();
                    if(name.includes(term) || msg.includes(term)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });
</script>