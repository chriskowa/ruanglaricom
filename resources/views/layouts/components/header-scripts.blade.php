@push('scripts')
<script>
    // Authentication status
    const isAuthenticated = @json(auth()->check());
    const baseUrl = @json(url('/'));
    const storageBase = @json(asset('storage/'));
    const defaultAvatar = @json(asset('images/avatar/1.jpg'));
    const authId = @json(auth()->id() ?: 0);
    const authAvatar = @json(auth()->user() && auth()->user()->avatar ? asset('storage/' . preg_replace('/^(\/)?storage\//', '', auth()->user()->avatar)) : asset('images/avatar/1.jpg'));
    const authName = @json(auth()->user() ? auth()->user()->name : '');

    // Chatbox toggle
    document.getElementById('chatbox-toggle')?.addEventListener('click', function() {
        document.querySelector('.chatbox').classList.toggle('active');
    });

    // Notification realtime polling
    let notificationCheckInterval;
    
    function formatNotificationDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        return `${day}/${month}/${year}, ${hours}.${minutes}.${seconds}`;
    }
    
    function getNotificationUrl(notif) {
        if (notif.reference_type === 'Post' && notif.reference_id) {
            return @json(route("feed.index")) + '#post-' + notif.reference_id;
        }
        return @json(route("notifications.index"));
    }
    
    function checkNotifications() {
        if (!isAuthenticated) return;

        fetch(@json(route("notifications.unread")))
            .then(response => {
                if (response.status === 401) return null;
                return response.json();
            })
            .then(data => {
                if (!data) return;

                const countBadge = document.querySelector('.notification-count');
                const notificationList = document.getElementById('notification-list');
                
                if (data.count > 0) {
                    countBadge.textContent = data.count;
                    countBadge.classList.remove('d-none');
                    
                    // Update notification list
                    if (notificationList && data.notifications && data.notifications.length > 0) {
                        notificationList.innerHTML = '';
                        data.notifications.forEach(notif => {
                            const li = document.createElement('li');
                            const notificationUrl = getNotificationUrl(notif);
                            li.innerHTML = `
                                <div class="timeline-panel">
                                    <div class="media-body">
                                        <h6 class="mb-1">
                                            <a href="${notificationUrl}" class="text-dark text-decoration-none notification-link" data-notification-id="${notif.id}">
                                                ${notif.title}
                                            </a>
                                        </h6>
                                        <small class="d-block">
                                            <a href="${notificationUrl}" class="text-dark text-decoration-none notification-link" data-notification-id="${notif.id}">
                                                ${notif.message}
                                            </a>
                                        </small>
                                        <small class="d-block text-muted">${formatNotificationDate(notif.created_at)}</small>
                                    </div>
                                </div>
                            `;
                            notificationList.appendChild(li);
                        });
                    }
                } else {
                    countBadge.classList.add('d-none');
                    if (notificationList) {
                        notificationList.innerHTML = '<li class="text-center p-3"><p class="text-muted mb-0">Tidak ada notifikasi</p></li>';
                    }
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    }
    
    // Check notifications every 30 seconds (only when authenticated)
    if (document.getElementById('notification-dropdown')) {
        if (isAuthenticated) {
            checkNotifications();
            notificationCheckInterval = setInterval(checkNotifications, 30000);
        }
    }
    
    // Handle notification click - mark as read
    document.addEventListener('click', function(e) {
        if (e.target.closest('.notification-link')) {
            const link = e.target.closest('.notification-link');
            const notificationId = link.dataset.notificationId;
            
            if (notificationId) {
                e.preventDefault(); // Prevent default link behavior
                
                // Mark as read via AJAX first
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update count and notification list
                        checkNotifications();
                        // Now navigate to the link
                        window.location.href = link.href;
                    } else {
                        // Navigate anyway if response is not successful
                        window.location.href = link.href;
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    // Navigate anyway if there's an error
                    window.location.href = link.href;
                });
            } else {
                // If no notification ID, just navigate normally
                window.location.href = link.href;
            }
        }
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (notificationCheckInterval) {
            clearInterval(notificationCheckInterval);
        }
        if (chatMessageInterval) {
            clearInterval(chatMessageInterval);
        }
    });

    // ==================== CHAT FUNCTIONALITY ====================
    let currentChatUserId = null;
    let chatMessageInterval = null;
    let lastMessageId = null;

    // Format time for messages
    function formatMessageTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins} menit yang lalu`;
        if (diffHours < 24) return `${diffHours} jam yang lalu`;
        if (diffDays < 7) return `${diffDays} hari yang lalu`;
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    // Load conversations
    function loadConversations() {
        fetch(@json(route("chat.conversations")))
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const conversationsList = document.getElementById('chat-conversations-list');
                if (!conversationsList) return;

                if (data.conversations && data.conversations.length > 0) {
                    conversationsList.innerHTML = '';
                    data.conversations.forEach(conv => {
                        const li = document.createElement('li');
                        li.className = 'dz-chat-user';
                        li.dataset.userId = conv.user_id;
                        const avatarUrl = conv.user_avatar ? (storageBase + conv.user_avatar) : defaultAvatar;
                        li.innerHTML = `
                            <div class="d-flex bd-highlight">
                                <div class="img_cont">
                                    <img src="${avatarUrl}" 
                                         class="rounded-circle user_img" alt="${conv.user_name}">
                                    <span class="online_icon offline"></span>
                                </div>
                                <div class="user_info">
                                    <span>${conv.user_name} ${conv.unread_count > 0 ? '<span class="badge badge-danger">' + conv.unread_count + '</span>' : ''}</span>
                                    <p>${conv.last_message ? conv.last_message.substring(0, 30) + (conv.last_message.length > 30 ? '...' : '') : 'Tidak ada pesan'}</p>
                                </div>
                            </div>
                        `;
                        li.addEventListener('click', function() {
                            openChat(conv.user_id, conv.user_name, conv.user_avatar);
                        });
                        conversationsList.appendChild(li);
                    });
                } else {
                    conversationsList.innerHTML = '<li class="text-center p-3"><p class="text-muted mb-0">Belum ada percakapan</p></li>';
                }
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
                const conversationsList = document.getElementById('chat-conversations-list');
                if (conversationsList) {
                    conversationsList.innerHTML = '<li class="text-center p-3"><p class="text-danger mb-0">Gagal memuat percakapan. Silakan refresh halaman.</p></li>';
                }
            });
    }

    // Open chat with user
    function openChat(userId, userName, userAvatar) {
        currentChatUserId = userId;
        lastMessageId = null;

        // Update chat header
        document.getElementById('chat-with-name').textContent = userName;
        document.getElementById('chat-view-profile').href = @json(route("profile.show")) + '?user=' + userId;

        // Show chat history box, hide contacts box
        document.querySelector('.dz-chat-user-box').classList.add('d-none');
        document.querySelector('.dz-chat-history-box').classList.remove('d-none');

        // Load messages
        loadMessages(userId);

        // Start polling for new messages
        if (chatMessageInterval) {
            clearInterval(chatMessageInterval);
        }
        chatMessageInterval = setInterval(() => {
            loadMessages(userId, true);
        }, 3000); // Poll every 3 seconds
    }

    // Load messages
    function loadMessages(userId, silent = false) {
        fetch(`${baseUrl}/api/chat/${userId}/messages`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const messagesContainer = document.getElementById('chat-messages-container');
                if (!messagesContainer) return;

                if (data.messages && data.messages.length > 0) {
                    // Only update if there are new messages
                    const latestMessageId = data.messages[data.messages.length - 1].id;
                    if (silent && lastMessageId === latestMessageId) {
                        return; // No new messages
                    }
                    lastMessageId = latestMessageId;

                    messagesContainer.innerHTML = '';
                    data.messages.forEach(msg => {
                        const isOwnMessage = String(msg.sender_id) === String(authId);
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `d-flex ${isOwnMessage ? 'justify-content-end' : 'justify-content-start'} mb-4`;
                        const senderAvatarUrl = msg.sender.avatar ? (storageBase + msg.sender.avatar) : defaultAvatar;
                        const currentUserAvatarUrl = authAvatar;
                        messageDiv.innerHTML = `
                            ${!isOwnMessage ? `
                                <div class="img_cont_msg">
                                    <img src="${senderAvatarUrl}" 
                                         class="rounded-circle user_img_msg" alt="${msg.sender.name}" style="width: 40px; height: 40px; object-fit: cover;">
                                </div>
                            ` : ''}
                            <div class="${isOwnMessage ? 'msg_cotainer_send' : 'msg_cotainer'}">
                                ${msg.message.replace(/\n/g, '<br>')}
                                <span class="${isOwnMessage ? 'msg_time_send' : 'msg_time'}">${formatMessageTime(msg.created_at)}</span>
                            </div>
                            ${isOwnMessage ? `
                                <div class="img_cont_msg">
                                    <img src="${currentUserAvatarUrl}" 
                                         class="rounded-circle user_img_msg" alt="${authName}" style="width: 40px; height: 40px; object-fit: cover;">
                                </div>
                            ` : ''}
                        `;
                        messagesContainer.appendChild(messageDiv);
                    });

                    // Scroll to bottom
                    const msgBody = document.getElementById('DZ_W_Contacts_Body3');
                    if (msgBody) {
                        msgBody.scrollTop = msgBody.scrollHeight;
                    }
                } else {
                    messagesContainer.innerHTML = '<div class="text-center p-3"><p class="text-muted mb-0">Belum ada pesan. Mulai percakapan!</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                const messagesContainer = document.getElementById('chat-messages-container');
                if (messagesContainer && !silent) {
                    messagesContainer.innerHTML = '<div class="text-center p-3"><p class="text-danger mb-0">Gagal memuat pesan. Silakan refresh halaman.</p></div>';
                }
            });
    }

    // Send message
    function sendMessage() {
        if (!currentChatUserId) {
            alert('Pilih percakapan terlebih dahulu');
            return;
        }

        const messageInput = document.getElementById('chat-message-input');
        const message = messageInput.value.trim();

        if (!message) {
            return;
        }

        // Disable input and button
        messageInput.disabled = true;
        const sendBtn = document.getElementById('chat-send-btn');
        sendBtn.disabled = true;

        fetch(`${baseUrl}/chat/${currentChatUserId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                // Reload messages
                loadMessages(currentChatUserId);
                // Reload conversations to update last message
                loadConversations();
            } else {
                alert('Gagal mengirim pesan');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Terjadi kesalahan saat mengirim pesan');
        })
        .finally(() => {
            messageInput.disabled = false;
            sendBtn.disabled = false;
            messageInput.focus();
        });
    }

    // Chat back button
    document.querySelector('.dz-chat-history-back')?.addEventListener('click', function(e) {
        e.preventDefault();
        // Stop polling
        if (chatMessageInterval) {
            clearInterval(chatMessageInterval);
            chatMessageInterval = null;
        }
        currentChatUserId = null;
        lastMessageId = null;

        // Show contacts box, hide chat history box
        document.querySelector('.dz-chat-user-box').classList.remove('d-none');
        document.querySelector('.dz-chat-history-box').classList.add('d-none');

        // Reload conversations
        loadConversations();
    });

    // Send message button
    document.getElementById('chat-send-btn')?.addEventListener('click', sendMessage);

    // Send message on Enter key
    document.getElementById('chat-message-input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Chatbox close button
    document.querySelector('.chatbox-close')?.addEventListener('click', function() {
        document.querySelector('.chatbox').classList.remove('active');
        // Stop polling when chatbox is closed
        if (chatMessageInterval) {
            clearInterval(chatMessageInterval);
            chatMessageInterval = null;
        }
    });

    // Load conversations when chatbox is opened
    document.getElementById('chatbox-toggle')?.addEventListener('click', function() {
        setTimeout(() => {
            if (document.querySelector('.chatbox').classList.contains('active')) {
                loadConversations();
            }
        }, 100);
    });

    // Initial load (always load to update badge)
    loadConversations();
    
    let gPressedAt = 0;
    document.addEventListener('keydown', function(e) {
        const tag = e.target.tagName.toLowerCase();
        const editable = e.target.isContentEditable;
        if (e.key === '/' && tag !== 'input' && tag !== 'textarea' && !editable) {
            e.preventDefault();
            document.getElementById('global-search')?.focus();
            return;
        }
        if (e.key.toLowerCase() === 'g') {
            gPressedAt = Date.now();
            return;
        }
        if (gPressedAt && Date.now() - gPressedAt < 1000) {
            if (e.key.toLowerCase() === 'c') {
                e.preventDefault();
                @auth
                    @if(auth()->user()->isRunner())
                        window.location.href = @json(route('runner.calendar'));
                    @else
                        window.location.href = @json(route('calendar.public'));
                    @endif
                @else
                    window.location.href = @json(route('calendar.public'));
                @endauth
                gPressedAt = 0;
                return;
            }
            if (e.key.toLowerCase() === 'm') {
                e.preventDefault();
                window.location.href = @json(route('chat.index'));
                gPressedAt = 0;
                return;
            }
        }
    });
</script>
@endpush
