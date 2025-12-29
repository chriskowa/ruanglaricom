@extends('layouts.pacerhub')

@section('title', 'Chat with ' . $user->name)

@section('content')
<div class="flex h-[calc(100vh-64px)] overflow-hidden bg-dark pt-20">
    <!-- Sidebar List (Hidden on Mobile) -->
    @include('chat.partials.sidebar', ['conversations' => $conversations, 'activeUserId' => $user->id])

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col bg-slate-900 relative">
        <!-- Chat Header -->
        <div class="h-16 px-4 md:px-6 border-b border-slate-800 flex items-center justify-between bg-slate-900/95 backdrop-blur z-10">
            <div class="flex items-center gap-3">
                <a href="{{ route('chat.index') }}" class="md:hidden p-2 -ml-2 text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <a href="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/17.jpg') }}" target="_blank" rel="noopener">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/17.jpg') }}" 
                         class="w-10 h-10 rounded-full object-cover border border-slate-700" alt="{{ $user->name }}">
                </a>
                <div>
                    <h3 class="text-white font-bold text-sm md:text-base">{{ $user->name }}</h3>
                    <p class="text-neon text-[10px] md:text-xs font-mono uppercase tracking-wider">{{ ucfirst($user->role) }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('profile.show') }}?user={{ $user->id }}" class="p-2 text-slate-400 hover:text-neon transition" title="View Profile">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Messages -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 custom-scrollbar scroll-smooth">
            @forelse($messages as $message)
                @php $isOwn = $message->sender_id === auth()->id(); @endphp
                <div class="flex w-full {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] md:max-w-[70%] flex {{ $isOwn ? 'flex-row-reverse' : 'flex-row' }} gap-2">
                        @if(!$isOwn)
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/17.jpg') }}" 
                                 class="w-8 h-8 rounded-full object-cover self-end mb-1 hidden md:block border border-slate-700">
                        @endif
                        
                        <div>
                            <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm break-words whitespace-pre-wrap {{ $isOwn ? 'bg-neon text-dark rounded-br-none font-medium' : 'bg-slate-800 text-slate-200 rounded-bl-none border border-slate-700' }}">
                                {{ $message->message }}
                            </div>
                            <div class="mt-1 flex items-center gap-1 {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                                <span class="text-[10px] text-slate-500 font-mono">{{ $message->created_at->format('H:i') }}</span>
                                @if($isOwn && $message->is_read)
                                    <svg class="w-3 h-3 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex h-full items-center justify-center">
                    <div class="text-center p-8 bg-slate-800/30 rounded-3xl border border-slate-800 border-dashed">
                        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-slate-400 font-medium">Start a conversation with {{ $user->name }}</p>
                        <p class="text-slate-600 text-xs mt-1">Say hello!</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-slate-900 border-t border-slate-800">
            <form id="chat-form" action="{{ route('chat.store', $user) }}" method="POST" class="flex items-end gap-2 max-w-4xl mx-auto w-full">
                @csrf
                <div class="flex-1 relative">
                    <textarea name="message" id="message-input" rows="1" class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all resize-none max-h-32 placeholder-slate-500 text-sm" placeholder="Type a message..."></textarea>
                    <button type="button" class="absolute right-2 bottom-2 p-1.5 text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
                <button type="submit" id="send-btn" class="p-3 bg-neon text-dark rounded-xl font-black shadow-lg shadow-neon/20 hover:bg-lime-400 hover:shadow-neon/40 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.getElementById('chat-messages');
        const form = document.getElementById('chat-form');
        const input = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        
        // Messages Data
        let lastMessageId = {{ $messages->count() > 0 ? $messages->last()->id : 0 }};
        const userId = {{ $user->id }};
        const messagesUrl = "{{ route('chat.messages', $user->id) }}";
        const currentUserId = {{ auth()->id() }};

        // Scroll to bottom
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        scrollToBottom();

        // Auto-resize textarea
        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if(this.value.trim() === '') this.style.height = 'auto';
        });

        // Handle Enter key
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if(this.value.trim()) sendMessage();
            }
        });

        // Helper: Create Message Bubble HTML
        function createMessageBubble(msg, isOwn) {
            const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const avatarHtml = !isOwn 
                ? `<img src="${msg.sender.avatar ? '/storage/' + msg.sender.avatar : '/images/profile/17.jpg'}" class="w-8 h-8 rounded-full object-cover self-end mb-1 hidden md:block border border-slate-700">`
                : '';
            
            const readReceipt = (isOwn && msg.is_read) 
                ? `<svg class="w-3 h-3 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`
                : '';

            return `
                <div class="flex w-full ${isOwn ? 'justify-end' : 'justify-start'}" id="msg-${msg.id}">
                    <div class="max-w-[85%] md:max-w-[70%] flex ${isOwn ? 'flex-row-reverse' : 'flex-row'} gap-2">
                        ${avatarHtml}
                        <div>
                            <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm break-words whitespace-pre-wrap ${isOwn ? 'bg-neon text-dark rounded-br-none font-medium' : 'bg-slate-800 text-slate-200 rounded-bl-none border border-slate-700'}">
                                ${msg.message}
                            </div>
                            <div class="mt-1 flex items-center gap-1 ${isOwn ? 'justify-end' : 'justify-start'}">
                                <span class="text-[10px] text-slate-500 font-mono">${time}</span>
                                ${readReceipt}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Send Message Function
        function sendMessage() {
            const message = input.value.trim();
            if (!message) return;

            // Optimistic UI update
            const tempId = 'temp-' + Date.now();
            const tempBubble = `
                <div class="flex w-full justify-end" id="${tempId}">
                    <div class="max-w-[85%] md:max-w-[70%] flex flex-row-reverse gap-2">
                        <div>
                            <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm break-words whitespace-pre-wrap bg-neon text-dark rounded-br-none font-medium opacity-70">
                                ${message.replace(/</g, "&lt;").replace(/>/g, "&gt;")}
                            </div>
                            <div class="mt-1 flex items-center gap-1 justify-end">
                                <span class="text-[10px] text-slate-500 font-mono">Sending...</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', tempBubble);
            scrollToBottom();
            
            input.value = '';
            input.style.height = 'auto';
            sendBtn.disabled = true;

            // Send Request
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tempEl = document.getElementById(tempId);
                    if(tempEl) {
                        tempEl.remove(); // Remove temp, add real to ensure correct ID/State
                    }
                    // Add real message if not already added by polling
                    if (data.message.id > lastMessageId) {
                        messagesContainer.insertAdjacentHTML('beforeend', createMessageBubble(data.message, true));
                        lastMessageId = data.message.id;
                        scrollToBottom();
                    }
                } else {
                    alert('Failed to send message');
                    document.getElementById(tempId).remove();
                    input.value = message; // Restore message
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error sending message');
                document.getElementById(tempId)?.remove();
                input.value = message;
            })
            .finally(() => {
                sendBtn.disabled = false;
                input.focus();
            });
        }

        // Form Submit
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });

        // Polling for new messages
        setInterval(() => {
            fetch(messagesUrl)
                .then(res => res.json())
                .then(data => {
                    if(data.messages && data.messages.length > 0) {
                        let added = false;
                        data.messages.forEach(msg => {
                            if (msg.id > lastMessageId) {
                                // Check if this is a message we just sent but hasn't been replaced yet?
                                // No, sendMessage removes temp and adds real.
                                // So we just add.
                                messagesContainer.insertAdjacentHTML('beforeend', createMessageBubble(msg, msg.sender_id === currentUserId));
                                lastMessageId = msg.id;
                                added = true;
                            }
                        });
                        if(added) scrollToBottom();
                    }
                })
                .catch(console.error);
        }, 3000);
    });
</script>
@endpush
@endsection
