<aside class="{{ isset($activeUserId) ? 'hidden md:flex md:w-80 lg:w-96' : 'flex w-full md:w-80 lg:w-96' }} border-r border-slate-800 flex-col bg-slate-950 shrink-0" id="chat-sidebar">
    <!-- Header Sidebar -->
    <div class="h-16 px-4 border-b border-slate-800 flex items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-2.5 min-w-0">
            <h1 class="text-lg font-bold text-white tracking-tight truncate" style="font-family: 'Inter Tight', 'Sora', sans-serif;">Pesan</h1>
            <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-xs text-slate-300 font-mono">{{ $conversations->count() }}</span>
        </div>
        @if(auth()->user()?->isRunner())
            <a href="{{ route('runner.dashboard') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition whitespace-nowrap">
                Dashboard &rarr;
            </a>
        @endif
    </div>

    <!-- Search -->
    <div class="p-3 border-b border-slate-800 shrink-0">
        <div class="relative">
            <input
                type="text"
                id="chat-sidebar-search"
                placeholder="Cari nama atau pesan..."
                class="w-full min-h-[42px] bg-slate-900 border border-slate-800 rounded-md pl-3.5 pr-9 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-lime-400 transition-colors"
            >
            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    <!-- Conversation List -->
    <div class="flex-1 overflow-y-auto chat-scrollbar divide-y divide-slate-800/70" id="conversations-list">
        @forelse($conversations as $item)
            @php
                $otherUser = $item['user'];
                $lastMessage = $item['last_message'];
                $unreadCount = (int) ($item['unread_count'] ?? 0);
                $isAiCoach = (bool) ($item['is_ai_coach'] ?? false);
                $isActive = isset($activeUserId) && (int) $otherUser->id === (int) $activeUserId;
            @endphp
            <a href="{{ route('chat.show', $otherUser) }}"
               class="chat-conv-item block px-4 py-3.5 transition-colors {{ $isActive ? 'bg-slate-900' : 'hover:bg-slate-900/70' }}">
                <div class="flex items-start gap-3">
                    <div class="relative shrink-0">
                        <img src="{{ $otherUser->avatar_url }}"
                             class="w-11 h-11 rounded-full object-cover border {{ $isActive ? 'border-lime-400' : 'border-slate-800' }}"
                             alt="{{ $otherUser->name }}">
                        @if($isAiCoach)
                            <span class="absolute -bottom-0.5 -right-0.5 bg-lime-400 text-slate-950 text-[10px] font-bold px-1 rounded">AI</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <h2 class="chat-conv-name text-sm font-semibold {{ $isActive ? 'text-lime-400' : 'text-white' }} truncate">
                                {{ $otherUser->name }}
                            </h2>
                            <span class="text-xs text-slate-400 font-mono shrink-0">
                                {{ $lastMessage ? $lastMessage->created_at->diffForHumans(null, true, true) : 'Online' }}
                            </span>
                        </div>
                        <p class="chat-conv-preview text-xs truncate {{ $unreadCount > 0 ? 'font-semibold text-white' : 'text-slate-300' }}">
                            @if($lastMessage)
                                @if((int) $lastMessage->sender_id === (int) auth()->id())
                                    <span class="text-slate-400">Anda:</span>
                                @endif
                                {{ \Illuminate\Support\Str::limit(strip_tags($lastMessage->message), 48) }}
                            @else
                                <span class="text-lime-400">Siap membantu program lari Anda</span>
                            @endif
                        </p>
                    </div>
                    @if($unreadCount > 0)
                        <span class="shrink-0 self-center px-2 py-0.5 rounded bg-lime-400 text-slate-950 text-xs font-bold font-mono">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                Belum ada percakapan.
            </div>
        @endforelse
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('chat-sidebar-search');
        const list = document.getElementById('conversations-list');
        if (!searchInput || !list) return;

        searchInput.addEventListener('input', function(e) {
            const term = (e.target.value || '').trim().toLowerCase();
            list.querySelectorAll('.chat-conv-item').forEach(function(item) {
                const name = (item.querySelector('.chat-conv-name')?.textContent || '').toLowerCase();
                const preview = (item.querySelector('.chat-conv-preview')?.textContent || '').toLowerCase();
                item.style.display = (name.includes(term) || preview.includes(term)) ? 'block' : 'none';
            });
        });
    });
</script>
