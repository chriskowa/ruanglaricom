@extends('layouts.pacerhub')
@php
    $hideFooter = true;
    $isAiCoach = $user->email === 'ai-coach@ruanglari.com';
@endphp

@section('title', 'Chat dengan ' . $user->name)

@section('content')
<div class="fixed inset-x-0 top-20 bottom-0 z-20 bg-slate-950 flex overflow-hidden">
    <!-- Sidebar List (Hidden on Mobile) -->
    @include('chat.partials.sidebar', ['conversations' => $conversations, 'activeUserId' => $user->id])

    <!-- Chat Area -->
    <section class="flex-1 flex flex-col bg-slate-900 min-w-0 min-h-0 h-full">
        <!-- Chat Header -->
        <header class="h-16 px-3.5 md:px-6 border-b border-slate-800 flex items-center justify-between gap-3 bg-slate-950 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('chat.index') }}" class="md:hidden inline-flex items-center justify-center w-10 h-10 -ml-1 rounded-md text-slate-300 hover:text-white hover:bg-slate-900 transition" aria-label="Kembali ke daftar pesan">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <img src="{{ $user->avatar_url }}"
                     class="w-10 h-10 rounded-full object-cover border border-slate-800 shrink-0"
                     alt="{{ $user->name }}">
                <div class="min-w-0">
                    <h2 class="text-white font-bold text-sm md:text-base truncate" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                        {{ $user->name }}
                    </h2>
                    @if($isAiCoach)
                        <p class="text-lime-400 text-xs font-medium">Coach Ruang Lari &middot; Siap Membantu</p>
                    @else
                        <p class="text-slate-400 text-xs font-medium">{{ ucfirst($user->role) }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($isAiCoach)
                    @if(!empty($chatQuota))
                        <span id="chat-quota-badge"
                              class="px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-xs font-mono {{ $chatQuota['can_send'] ? 'text-slate-200' : 'text-amber-300' }}"
                              title="{{ $chatQuota['has_active_program'] ? 'Kuota harian pemilik program aktif' : 'Kuota gratis harian (buat program untuk 15 pesan/hari)' }}">
                            Sisa {{ $chatQuota['remaining'] }}/{{ $chatQuota['limit'] }}
                        </span>
                    @endif
                    <a href="{{ route('programs.realistic') }}" class="px-3 min-h-[38px] inline-flex items-center rounded-md bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-200 hover:text-white transition">
                        Buat Program
                    </a>
                @else
                    <a href="{{ route('profile.show') }}?user={{ $user->id }}" class="px-3 min-h-[38px] inline-flex items-center rounded-md bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-200 hover:text-white transition">
                        Profil
                    </a>
                @endif
            </div>
        </header>

        <!-- Messages -->
        <div id="chat-messages" class="flex-1 min-h-0 overflow-y-auto px-3.5 py-5 md:px-6 md:py-6 space-y-4 chat-scrollbar">
            <div id="chat-messages-inner" class="max-w-3xl mx-auto space-y-4">
                @forelse($messages as $message)
                    @php $isOwn = (int) $message->sender_id === (int) auth()->id(); @endphp
                    <div class="flex w-full {{ $isOwn ? 'justify-end' : 'justify-start' }}" id="msg-{{ $message->id }}">
                        <div class="max-w-[90%] md:max-w-[80%] flex {{ $isOwn ? 'flex-row-reverse' : 'flex-row' }} items-end gap-2.5">
                            @if(!$isOwn)
                                <img src="{{ $user->avatar_url }}"
                                     class="w-8 h-8 rounded-full object-cover border border-slate-800 hidden sm:block shrink-0"
                                     alt="{{ $user->name }}">
                            @endif

                            <div class="min-w-0">
                                <div class="px-4 py-3 rounded-lg break-words {{ $isOwn ? 'bg-lime-400 text-slate-950 font-medium text-sm md:text-[15px] leading-relaxed whitespace-pre-wrap' : 'bg-slate-950 text-slate-100 border border-slate-800 chat-prose' }}"
                                     @if(!$isOwn) data-raw-markdown="{{ e($message->message) }}" @endif>
                                    {{ $message->message }}
                                </div>
                                <div class="mt-1 flex items-center gap-1.5 {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                                    <span class="text-xs text-slate-400 font-mono">{{ $message->created_at->format('H:i') }}</span>
                                    @if($isOwn && $message->is_read)
                                        <span class="text-xs text-lime-400 font-medium">Dibaca</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div id="chat-empty-state" class="py-12 text-center">
                        <div class="max-w-md mx-auto bg-slate-950 border border-slate-800 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-white">Mulai percakapan dengan {{ $user->name }}</h3>
                            <p class="text-sm text-slate-300 mt-1.5 leading-relaxed">
                                @if($isAiCoach)
                                    Tanyakan evaluasi latihan minggu ini, penyesuaian pace, atau strategi pemulihan.
                                @else
                                    Kirim pesan pertama Anda di bawah ini.
                                @endif
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-bar px-3.5 pt-3 md:px-6 md:py-4 bg-slate-950 border-t border-slate-800 shrink-0">
            @if($isAiCoach)
                <div id="chat-quota-banner"
                     class="{{ !empty($chatQuota) && !$chatQuota['can_send'] ? 'flex' : 'hidden' }} max-w-3xl mx-auto mb-3 p-3 rounded-md bg-slate-900 border border-slate-800 flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <p id="chat-quota-banner-text" class="text-xs text-slate-200 leading-relaxed">
                        @if(!empty($chatQuota) && $chatQuota['has_active_program'])
                            Kuota konsultasi harian Anda ({{ $chatQuota['limit'] }} pesan/hari) sudah habis. Lanjutkan latihan sesuai Kalender Dashboard dan tanya Coach lagi besok.
                        @else
                            Kuota chat gratis harian Anda ({{ $chatQuota['limit'] ?? 3 }} pesan/hari) sudah habis. Buat program resmi untuk membuka 15 pesan konsultasi/hari.
                        @endif
                    </p>
                    @if(empty($chatQuota['has_active_program']))
                        <a href="{{ route('programs.realistic') }}" class="shrink-0 px-3.5 min-h-[38px] inline-flex items-center justify-center rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs transition">
                            Buat Program Lari
                        </a>
                    @endif
                </div>
            @endif

            <form id="chat-form" action="{{ route('chat.store', $user) }}" method="POST" class="flex items-end gap-2.5 max-w-3xl mx-auto w-full">
                @csrf
                <div class="flex-1">
                    <textarea
                        name="message"
                        id="message-input"
                        rows="1"
                        maxlength="2000"
                        @if($isAiCoach && !empty($chatQuota) && !$chatQuota['can_send']) disabled @endif
                        class="w-full min-h-[46px] max-h-36 bg-slate-900 border border-slate-800 text-white rounded-md px-3.5 py-3 focus:outline-none focus:border-lime-400 transition-colors resize-none placeholder-slate-400 text-sm md:text-[15px] leading-relaxed disabled:opacity-50 disabled:cursor-not-allowed"
                        placeholder="{{ $isAiCoach ? 'Tanya Coach Ruang Lari tentang latihan atau target Anda...' : 'Tulis pesan...' }}"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    id="send-btn"
                    @if($isAiCoach && !empty($chatQuota) && !$chatQuota['can_send']) disabled @endif
                    class="px-5 min-h-[46px] bg-lime-400 hover:bg-lime-300 text-slate-950 rounded-md font-bold text-sm transition disabled:opacity-50 disabled:cursor-not-allowed shrink-0 cursor-pointer"
                >
                    Kirim
                </button>
            </form>
        </div>
    </section>
</div>

<style>
html, body { overflow: hidden; height: 100%; }
.chat-input-bar { padding-bottom: max(0.75rem, env(safe-area-inset-bottom)); }
.chat-scrollbar::-webkit-scrollbar { width: 5px; }
.chat-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
.chat-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
.chat-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }

.chat-prose {
    font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.72;
    color: #f1f5f9;
}
.chat-prose p { margin: 0 0 0.65rem 0; }
.chat-prose p:last-child { margin-bottom: 0; }
.chat-prose strong { color: #ffffff; font-weight: 700; }
.chat-prose ul, .chat-prose ol { margin: 0.4rem 0 0.7rem 1.25rem; padding: 0; }
.chat-prose ul { list-style-type: disc; }
.chat-prose ol { list-style-type: decimal; }
.chat-prose li { margin-bottom: 0.3rem; }
.chat-prose h3, .chat-prose h4 {
    font-family: 'Inter Tight', 'Sora', sans-serif;
    font-weight: 700;
    color: #ccff00;
    margin: 0.75rem 0 0.35rem 0;
    font-size: 1rem;
}
.chat-prose code {
    background: #1e293b;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    font-size: 0.875em;
    color: #e2e8f0;
}
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.getElementById('chat-messages');
        const messagesInner = document.getElementById('chat-messages-inner');
        const form = document.getElementById('chat-form');
        const input = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');

        let lastMessageId = {{ $messages->count() > 0 ? $messages->last()->id : 0 }};
        const messagesUrl = "{{ route('chat.messages', $user->id) }}";
        const currentUserId = {{ (int) auth()->id() }};
        const partnerAvatarUrl = @json($user->avatar_url);
        const partnerName = @json($user->name);
        const isAiCoach = @json($isAiCoach);

        let isSending = false;
        let isPolling = false;

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatInlineMarkdown(text) {
            return text
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/`([^`]+)`/g, '<code>$1</code>');
        }

        function renderChatMarkdown(raw) {
            const safe = escapeHtml(raw);
            const lines = safe.split(/\r?\n/);
            let html = '';
            let inUl = false;
            let inOl = false;

            const closeLists = () => {
                if (inUl) { html += '</ul>'; inUl = false; }
                if (inOl) { html += '</ol>'; inOl = false; }
            };

            lines.forEach(function(line) {
                const trimmed = line.trim();
                if (!trimmed) {
                    closeLists();
                    return;
                }

                if (/^#{1,4}\s+/.test(trimmed)) {
                    closeLists();
                    html += '<h4>' + formatInlineMarkdown(trimmed.replace(/^#{1,4}\s+/, '')) + '</h4>';
                    return;
                }

                if (/^[-*•]\s+/.test(trimmed)) {
                    if (inOl) { html += '</ol>'; inOl = false; }
                    if (!inUl) { html += '<ul>'; inUl = true; }
                    html += '<li>' + formatInlineMarkdown(trimmed.replace(/^[-*•]\s+/, '')) + '</li>';
                    return;
                }

                if (/^\d+\.\s+/.test(trimmed)) {
                    if (inUl) { html += '</ul>'; inUl = false; }
                    if (!inOl) { html += '<ol>'; inOl = true; }
                    html += '<li>' + formatInlineMarkdown(trimmed.replace(/^\d+\.\s+/, '')) + '</li>';
                    return;
                }

                closeLists();
                html += '<p>' + formatInlineMarkdown(trimmed) + '</p>';
            });

            closeLists();
            return html || '<p>' + safe + '</p>';
        }

        // Format existing server-rendered incoming messages cleanly
        document.querySelectorAll('[data-raw-markdown]').forEach(function(el) {
            const text = el.textContent || '';
            el.innerHTML = renderChatMarkdown(text);
        });

        function scrollToBottom(smooth = false) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
        scrollToBottom(false);

        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 144) + 'px';
            if (this.value.trim() === '') this.style.height = 'auto';
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() && !isSending) sendMessage();
            }
        });

        function createMessageBubble(msg, isOwn) {
            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const avatarUrl = !isOwn ? (msg.sender?.avatar || partnerAvatarUrl) : '';
            const avatarHtml = !isOwn
                ? `<img src="${escapeHtml(avatarUrl)}" class="w-8 h-8 rounded-full object-cover border border-slate-800 hidden sm:block shrink-0" alt="${escapeHtml(partnerName)}">`
                : '';

            const bodyHtml = isOwn
                ? escapeHtml(msg.message)
                : renderChatMarkdown(msg.message);

            const bubbleClasses = isOwn
                ? 'bg-lime-400 text-slate-950 font-medium text-sm md:text-[15px] leading-relaxed whitespace-pre-wrap'
                : 'bg-slate-950 text-slate-100 border border-slate-800 chat-prose';

            const readReceipt = (isOwn && msg.is_read)
                ? `<span class="text-xs text-lime-400 font-medium">Dibaca</span>`
                : '';

            return `
                <div class="flex w-full ${isOwn ? 'justify-end' : 'justify-start'}" id="msg-${msg.id}">
                    <div class="max-w-[90%] md:max-w-[80%] flex ${isOwn ? 'flex-row-reverse' : 'flex-row'} items-end gap-2.5">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="px-4 py-3 rounded-lg break-words ${bubbleClasses}">
                                ${bodyHtml}
                            </div>
                            <div class="mt-1 flex items-center gap-1.5 ${isOwn ? 'justify-end' : 'justify-start'}">
                                <span class="text-xs text-slate-400 font-mono">${time}</span>
                                ${readReceipt}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function appendIfNew(msg) {
            if (!msg || !msg.id) return false;
            if (document.getElementById('msg-' + msg.id)) return false;

            const emptyState = document.getElementById('chat-empty-state');
            if (emptyState) emptyState.remove();

            const isOwn = Number(msg.sender_id) === currentUserId;
            messagesInner.insertAdjacentHTML('beforeend', createMessageBubble(msg, isOwn));
            if (msg.id > lastMessageId) {
                lastMessageId = msg.id;
            }
            return true;
        }

        function showCoachTyping() {
            if (!isAiCoach || document.getElementById('coach-typing-indicator')) return;
            const html = `
                <div class="flex w-full justify-start" id="coach-typing-indicator">
                    <div class="max-w-[85%] flex flex-row items-end gap-2.5">
                        <img src="${escapeHtml(partnerAvatarUrl)}" class="w-8 h-8 rounded-full object-cover border border-slate-800 hidden sm:block shrink-0" alt="${escapeHtml(partnerName)}">
                        <div class="px-4 py-3 rounded-lg bg-slate-950 border border-slate-800 text-sm text-slate-300">
                            Coach Ruang Lari sedang menyusun jawaban...
                        </div>
                    </div>
                </div>
            `;
            messagesInner.insertAdjacentHTML('beforeend', html);
            scrollToBottom(true);
        }

        function hideCoachTyping() {
            document.getElementById('coach-typing-indicator')?.remove();
        }

        function sendMessage() {
            const message = input.value.trim();
            if (!message || isSending) return;

            isSending = true;
            sendBtn.disabled = true;

            const emptyState = document.getElementById('chat-empty-state');
            if (emptyState) emptyState.remove();

            const tempId = 'temp-' + Date.now();
            const tempBubble = `
                <div class="flex w-full justify-end" id="${tempId}">
                    <div class="max-w-[90%] md:max-w-[80%] flex flex-row-reverse items-end gap-2.5">
                        <div class="min-w-0">
                            <div class="px-4 py-3 rounded-lg break-words bg-lime-400 text-slate-950 font-medium text-sm md:text-[15px] leading-relaxed whitespace-pre-wrap opacity-75">
                                ${escapeHtml(message)}
                            </div>
                            <div class="mt-1 flex items-center gap-1.5 justify-end">
                                <span class="text-xs text-slate-400 font-mono">Mengirim...</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            messagesInner.insertAdjacentHTML('beforeend', tempBubble);
            scrollToBottom(true);

            if (isAiCoach) {
                showCoachTyping();
            }

            input.value = '';
            input.style.height = 'auto';

            function applyQuotaState(quota, customMsg) {
                if (!quota) return;
                const badge = document.getElementById('chat-quota-badge');
                if (badge) {
                    badge.textContent = 'Sisa ' + quota.remaining + '/' + quota.limit;
                    badge.classList.toggle('text-amber-300', !quota.can_send);
                    badge.classList.toggle('text-slate-200', !!quota.can_send);
                }
                const banner = document.getElementById('chat-quota-banner');
                const bannerText = document.getElementById('chat-quota-banner-text');
                if (!quota.can_send) {
                    if (banner) {
                        banner.classList.remove('hidden');
                        banner.classList.add('flex');
                    }
                    if (bannerText && customMsg) {
                        bannerText.textContent = customMsg;
                    }
                    input.disabled = true;
                    sendBtn.disabled = true;
                }
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: message })
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { ok: response.ok, status: response.status, data: data };
                });
            })
            .then(function(res) {
                const data = res.data || {};
                document.getElementById(tempId)?.remove();
                hideCoachTyping();

                if (res.ok && data.success) {
                    const addedUser = appendIfNew(data.message);
                    const addedAi = data.ai_reply ? appendIfNew(data.ai_reply) : false;
                    if (addedUser || addedAi) {
                        scrollToBottom(true);
                    }
                    if (data.chat_quota) {
                        applyQuotaState(data.chat_quota);
                    }
                } else if (res.status === 429 && data.quota_exceeded) {
                    applyQuotaState(data.chat_quota, data.message);
                } else {
                    input.value = message;
                    alert(data.message || 'Gagal mengirim pesan.');
                }
            })
            .catch(function(err) {
                console.error(err);
                document.getElementById(tempId)?.remove();
                hideCoachTyping();
                input.value = message;
                alert('Terjadi gangguan jaringan saat mengirim pesan. Silakan coba lagi.');
            })
            .finally(function() {
                isSending = false;
                if (!input.disabled) {
                    sendBtn.disabled = false;
                    input.focus();
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });

        // Lightweight delta polling: paused when tab hidden or while sending
        setInterval(function() {
            if (document.hidden || isSending || isPolling) return;
            isPolling = true;

            fetch(messagesUrl + '?after_id=' + encodeURIComponent(lastMessageId), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.messages && data.messages.length > 0) {
                    let added = false;
                    data.messages.forEach(function(msg) {
                        if (appendIfNew(msg)) {
                            added = true;
                        }
                    });
                    if (added) scrollToBottom(true);
                }
            })
            .catch(function() {})
            .finally(function() {
                isPolling = false;
            });
        }, 4000);
    });
</script>
@endpush
@endsection
