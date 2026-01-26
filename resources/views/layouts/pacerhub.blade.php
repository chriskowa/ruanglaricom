<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com https://www.googletagmanager.com https://www.google-analytics.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src 'self' data: blob: https:; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; connect-src 'self' https://www.google-analytics.com https://stats.g.doubleclick.net; frame-src 'self' https://www.google.com;">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $gsc = \App\Models\AppSettings::get('google_search_console');
        $bsc = \App\Models\AppSettings::get('bing_search_console');
        $ga = \App\Models\AppSettings::get('google_analytics');
        $gads = \App\Models\AppSettings::get('google_ads_tag');
    @endphp

    @if($gsc)
    <meta name="google-site-verification" content="{{ $gsc }}" />
    @endif
    @if($bsc)
    <meta name="msvalidate.01" content="{{ $bsc }}" />
    @endif

    @if($ga || $gads)
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga ?: $gads }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      @if($ga)
      gtag('config', '{{ $ga }}');
      @endif
      @if($gads)
      gtag('config', '{{ $gads }}');
      @endif
    </script>
    @endif

    <!-- Primary Meta Tags -->
    <title>@yield('title', 'Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans')</title>
    <meta name="title" content="@yield('meta_title', 'Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans')">
    <meta name="description" content="@yield('meta_description', 'Ruang Lari adalah platform komunitas lari terbesar di Indonesia. Temukan pacer, ikuti event, pantau progres, dan raih personal best Anda. Dapatkan rencana latihan eksklusif, analisis performa, dan diskon event.')">

    <!-- Keywords -->
    <meta name="keywords" content="@yield('meta_keywords', 'ruang lari, komunitas lari indonesia, pacer indonesia, event lari, kalender lari, training plan, analisis performa, strava indonesia, sepatu lari lokal, fotografer olahraga, running calculator, personal best, marathon indonesia, 5K, 10K, half marathon, full marathon')">

    <!-- Author -->
    <meta name="author" content="Ruang Lari Indonesia">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('meta_title', 'Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans')">
    <meta property="og:description" content="@yield('meta_description', 'Gabung dengan Ruang Lari, komunitas lari terbesar di Indonesia. Ikuti event, temukan pacer, dan pecahkan personal record Anda.')">
    <meta property="og:image" content="@yield('og_image', 'https://ruanglari.id/assets/images/ruanglari-cover.jpg')">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('meta_title', 'Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans')">
    <meta name="twitter:description" content="@yield('meta_description', 'Platform all-in-one untuk pelari, pacer, dan pelatih. Pantau progres, ikuti event, dan raih personal best Anda.')">
    <meta name="twitter:image" content="@yield('og_image', 'https://ruanglari.id/assets/images/ruanglari-cover.jpg')">

    <!-- Canonical -->
    <link rel="canonical" href="{{ url()->current() }}">   

    <!-- Favicon default -->
    <link rel="icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/green/favicon-32x32.png') }}" type="image/x-icon">

    <!-- Versi PNG -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">

    <!-- Versi Apple Touch -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/green/apple-touch-icon.png') }}">

    <!-- Versi Android/Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/android-icon-192x192.png">
    
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/duration.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
    <script>
        dayjs.extend(window.dayjs_plugin_duration);
        dayjs.extend(window.dayjs_plugin_relativeTime);
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: {
                        'xs': '360px',
                    },
                    colors: {
                        dark: '#0f172a',
                        card: '#1e293b',
                        neon: '#ccff00',
                        primary: '#ccff00',
                        strava: '#fc4c02',
                        accent: '#3b82f6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    
    <style>
        .loader-overlay { position: fixed; inset: 0; background: #0f172a; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s; }
        .animate-pulse { animation: loaderPulse 1.5s ease-in-out infinite; }
        @keyframes loaderPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(.98)} }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        
        /* FullCalendar Customization */
        :root {
            --fc-border-color: #334155;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #1e293b;
            --fc-list-event-hover-bg-color: #334155;
            --fc-today-bg-color: rgba(204, 255, 0, 0.05);
        }
        .fc-toolbar-title { color: white; font-family: 'Inter', sans-serif; font-weight: 800; }
        .fc-col-header-cell-cushion { color: #ccff00; text-decoration: none; }
        .fc-daygrid-day-number { color: #94a3b8; font-family: 'JetBrains Mono', monospace; text-decoration: none; }
        .fc-event { cursor: pointer; border: none !important; }
        .fc-button-primary { background-color: #1e293b !important; border-color: #475569 !important; color: #cbd5e1 !important; }
        .fc-button-active { background-color: #ccff00 !important; color: #0f172a !important; }

        .loader {
            border: 3px solid rgba(255,255,255,0.1);
            border-radius: 50%;
            border-top: 3px solid #ccff00;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Force White Calendar Icon */
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-dark text-white font-sans antialiased flex flex-col min-h-screen">

    <div id="loader" class="loader-overlay">
        <div class="text-4xl font-black italic tracking-tighter animate-pulse">
            RUANG<span class="text-primary">LARI</span>
        </div>
    </div>

    <div id="app" class="flex flex-col min-h-screen">
        
        @if(!isset($hideNav) || !$hideNav)
            @include('layouts.components.pacerhub-nav')
        @endif

        @if(isset($withSidebar) && $withSidebar && (!isset($hideSidebar) || !$hideSidebar))
            <div id="ph-sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
            @include('layouts.components.pacerhub-sidebar')
        @endif

        <main class="flex-grow w-full {{ (!isset($hideNav) || !$hideNav) ? 'pt-20' : '' }}">
            @yield('content')
        </main>

        @if(!isset($hideFooter) || !$hideFooter)
            @include('layouts.components.pacerhub-footer')
        @endif

    </div>

    @if(!isset($hideChat) || !$hideChat)
    <button id="chatbox-toggle" class="fixed bottom-5 right-6 z-50 w-14 h-14 rounded-full bg-neon text-dark font-black shadow-lg shadow-neon/30 flex items-center justify-center hover:bg-lime-400 transition">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-7 8l4-4h8a4 4 0 004-4V6a4 4 0 00-4-4H7a4 4 0 00-4 4v10a4 4 0 004 4z"/></svg>
        <span id="ph-chat-badge" class="hidden absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
    </button>
    <div id="ph-chatbox" class="fixed bottom-20 right-6 z-50 w-80 md:w-96 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl hidden">
        <div class="flex items-center justify-between p-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div id="ph-chat-avatar" class="w-8 h-8 rounded-full bg-slate-700 overflow-hidden"></div>
                <div>
                    <div id="ph-chat-title" class="text-sm font-bold text-white">Chat</div>
                    <div id="ph-chat-status" class="text-[10px] text-green-400">Online</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="ph-chat-back" class="px-2 py-1 rounded bg-slate-800 text-slate-300 text-xs hidden">Back</button>
                <button id="ph-chat-close" class="px-2 py-1 rounded bg-slate-800 text-slate-300 text-xs">Close</button>
            </div>
        </div>
        <div class="p-0">
            <div id="ph-chat-conv" class="max-h-80 overflow-y-auto divide-y divide-slate-800">
                <div class="p-4 text-center text-slate-400 text-sm">Memuat percakapan...</div>
            </div>
            <div id="ph-chat-msg" class="hidden">
                <div id="ph-chat-msg-body" class="max-h-64 overflow-y-auto p-3 space-y-3"></div>
                <div class="border-t border-slate-800 p-2">
                    <div class="flex items-end gap-2">
                        <textarea id="ph-chat-input" class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm" rows="2" placeholder="Tulis pesan..."></textarea>
                        <button id="ph-chat-send" class="px-3 py-2 rounded-xl bg-neon text-dark font-black text-sm">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @stack('scripts')
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        try {
            if (window.AOS && typeof window.AOS.init === 'function') {
                window.AOS.init({
                    duration: 800,
                    once: true,
                });
            }
        } catch (e) {}
        
        (function () {
            function hideLoader() {
                var loader = document.getElementById('loader');
                if (!loader) return;
                if (loader.dataset.hidden === '1') return;
                loader.style.opacity = '0';
                setTimeout(function () {
                    loader.dataset.hidden = '1';
                    try { loader.remove(); } catch (e) { loader.style.display = 'none'; }
                }, 500);
            }

            window.phHideLoader = hideLoader;
            window.addEventListener('load', hideLoader);
            document.addEventListener('DOMContentLoaded', hideLoader);
            window.addEventListener('pageshow', hideLoader);
        })();
        (function(){
            var btn = document.getElementById('ph-sidebar-toggle');
            var sidebar = document.getElementById('ph-sidebar');
            var backdrop = document.getElementById('ph-sidebar-backdrop');
            function openSidebar(){
                if(!sidebar) return;
                sidebar.classList.remove('-translate-x-full');
                if(backdrop){ backdrop.classList.remove('hidden'); }
            }
            function closeSidebar(){
                if(!sidebar) return;
                sidebar.classList.add('-translate-x-full');
                if(backdrop){ backdrop.classList.add('hidden'); }
            }
            if(btn){
                btn.addEventListener('click', function(){
                    if(sidebar && sidebar.classList.contains('-translate-x-full')){ openSidebar(); } else { closeSidebar(); }
                });
            }
            if(backdrop){
                backdrop.addEventListener('click', closeSidebar);
            }
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape'){ closeSidebar(); }
            });
        })();
    </script>
    <script>
        (function(){
            var toggle = document.getElementById('chatbox-toggle');
            if(!toggle) return;
            var box = document.getElementById('ph-chatbox');
            var convList = document.getElementById('ph-chat-conv');
            var msgPanel = document.getElementById('ph-chat-msg');
            var msgBody = document.getElementById('ph-chat-msg-body');
            var input = document.getElementById('ph-chat-input');
            var sendBtn = document.getElementById('ph-chat-send');
            var backBtn = document.getElementById('ph-chat-back');
            var closeBtn = document.getElementById('ph-chat-close');
            var titleEl = document.getElementById('ph-chat-title');
            var avatarEl = document.getElementById('ph-chat-avatar');
            var statusEl = document.getElementById('ph-chat-status');
            var metaCsrf = document.querySelector('meta[name="csrf-token"]');
            var csrf = metaCsrf ? metaCsrf.getAttribute('content') : '';
            var baseUrl = @json(url('/'));
            var storageBase = @json(asset('storage/'));
            var defaultAvatar17 = @json(asset('images/profile/17.jpg'));
            var authId = @json(auth()->id() ?: 0);
            var isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
            var poll;
            var currentUserId = null;
            function getAvatarUrl(path) {
                if (!path) return defaultAvatar17;
                if (path.indexOf('http') === 0) return path;
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
            function showBox(){ box.classList.remove('hidden'); }
            function hideBox(){ box.classList.add('hidden'); stopPoll(); }
            function showConversations(){ convList.classList.remove('hidden'); msgPanel.classList.add('hidden'); backBtn.classList.add('hidden'); }
            function showMessages(){ convList.classList.add('hidden'); msgPanel.classList.remove('hidden'); backBtn.classList.remove('hidden'); }
            function stopPoll(){ if(poll){ clearInterval(poll); poll=null; } }
            function formatTime(iso){ try{ return dayjs(iso).fromNow(); }catch(e){ return iso; } }
            function setDraft(userId, text){ try{ sessionStorage.setItem('phChatDraft:'+userId, text||''); }catch(e){} }
            function getDraft(userId){ try{ return sessionStorage.getItem('phChatDraft:'+userId)||''; }catch(e){ return ''; } }
            function setOpen(userId){ try{ sessionStorage.setItem('phChatOpen', String(userId||'')); }catch(e){} }
            function getOpen(){ try{ return sessionStorage.getItem('phChatOpen')||''; }catch(e){ return ''; } }
            function fetchConversations(){
                if(!isAuthenticated){
                    convList.innerHTML = '<div class="p-4 text-center text-slate-400 text-sm">Login untuk menggunakan chat</div>';
                    return;
                }
                fetch(@json(route('chat.conversations')), { headers:{'Accept':'application/json'} })
                .then(function(r){ if(!r.ok) throw new Error('err'); return r.json(); })
                .then(function(data){
                    convList.innerHTML = '';
                    var list = (data && data.conversations) ? data.conversations : [];
                    var badge = document.getElementById('ph-chat-badge');
                    var unread = 0;
                    for(var i=0;i<list.length;i++){ if(list[i] && list[i].unread_count) unread += Number(list[i].unread_count)||0; }
                    if(badge){ if(unread>0){ badge.textContent = unread>99 ? '99+' : String(unread); badge.classList.remove('hidden'); } else { badge.classList.add('hidden'); } }
                    if(!list.length){ convList.innerHTML = '<div class="p-4 text-center text-slate-400 text-sm">Belum ada percakapan</div>'; return; }
                    list.forEach(function(conv){
                        var li = document.createElement('div');
                        li.className = 'p-3 hover:bg-slate-800 cursor-pointer flex items-center gap-1';
                        var avatar = getAvatarUrl(conv.user_avatar);
                        li.innerHTML = '<img src="'+avatar+'" class="w-8 h-8 rounded-full border border-slate-700"><div class="flex-1"><div class="text-sm text-white font-bold">'+conv.user_name+'</div><div class="text-[11px] text-slate-400">'+(conv.last_message||'')+'</div></div>'+(conv.unread_count>0?'<span class="text-[10px] px-2 py-0.5 rounded bg-red-500 text-white">'+conv.unread_count+'</span>':'');
                        li.addEventListener('click', function(){ openChat(conv.user_id, conv.user_name, conv.user_avatar); });
                        convList.appendChild(li);
                    });
                })
                .catch(function(){ convList.innerHTML = '<div class="p-4 text-center text-red-400 text-sm">Gagal memuat percakapan</div>'; });
            }
            function loadMessages(userId, silent){
                if(!isAuthenticated) return;
                fetch(baseUrl+'/api/chat/'+userId+'/messages', { headers:{'Accept':'application/json'} })
                .then(function(r){ if(!r.ok) throw new Error('err'); return r.json(); })
                .then(function(data){
                    var msgs = (data && data.messages) ? data.messages : [];
                    msgBody.innerHTML = '';
                    msgs.forEach(function(m){
                        var own = Number(m.sender_id) === Number(authId);
                        var row = document.createElement('div');
                        row.className = 'flex '+(own?'justify-end':'justify-start');
                        var bubble = document.createElement('div');
                        bubble.className = 'max-w-[70%] px-3 py-2 rounded-xl text-sm '+(own?'bg-neon text-dark':'bg-slate-800 text-white');
                        bubble.innerHTML = m.message.replace(/\n/g,'<br>')+'<div class="text-[10px] mt-1 '+(own?'text-black/70':'text-slate-400')+'">'+formatTime(m.created_at)+'</div>';
                        row.appendChild(bubble);
                        msgBody.appendChild(row);
                    });
                    var saved = sessionStorage.getItem('phChatScroll:'+userId);
                    if(saved){ var v = Number(saved)||0; msgBody.scrollTop = v; } else { msgBody.scrollTop = msgBody.scrollHeight; }
                })
                .catch(function(){});
            }
            function sendMessage(){
                if(!isAuthenticated) return;
                if(!currentUserId) return;
                var text = (input.value||'').trim();
                if(!text) return;
                input.disabled = true; sendBtn.disabled = true;
                fetch(baseUrl+'/chat/'+currentUserId, {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN': csrf, 'Content-Type':'application/json', 'Accept':'application/json'},
                    body: JSON.stringify({ message: text })
                })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if(res && res.success){
                        input.value = '';
                        setDraft(currentUserId, '');
                        loadMessages(currentUserId);
                        fetchConversations();
                    }
                })
                .finally(function(){ input.disabled = false; sendBtn.disabled = false; input.focus(); });
            }
            function openChat(userId, name, avatar, initialMessage){
                currentUserId = userId;
                titleEl.textContent = name || 'Chat';
                statusEl.textContent = 'Online';
                var avatarUrl = getAvatarUrl(avatar);
                avatarEl.innerHTML = '<a href="'+avatarUrl+'" target="_blank" rel="noopener"><img src="'+avatarUrl+'" class="w-8 h-8"></a>';
                showMessages();
                showBox();
                loadMessages(userId);
                input.value = initialMessage || getDraft(userId);
                setOpen(userId);
                stopPoll();
                poll = setInterval(function(){ loadMessages(userId, true); }, 4000);
            }
            window.openChat = openChat;
            toggle.addEventListener('click', function(){
                if(box.classList.contains('hidden')){ showBox(); showConversations(); fetchConversations(); } else { hideBox(); }
            });
            backBtn.addEventListener('click', function(){ showConversations(); stopPoll(); currentUserId=null; setOpen(''); });
            closeBtn.addEventListener('click', function(){ hideBox(); });
            sendBtn.addEventListener('click', sendMessage);
            input.addEventListener('input', function(){ if(currentUserId) setDraft(currentUserId, input.value); });
            msgBody.addEventListener('scroll', function(){ if(currentUserId){ sessionStorage.setItem('phChatScroll:'+currentUserId, String(msgBody.scrollTop)); } });
            document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ hideBox(); } });
            var reopen = getOpen();
            if(reopen && isAuthenticated){ fetch(baseUrl+'/api/chat/'+reopen+'/messages', { headers:{'Accept':'application/json'} }).then(function(r){ if(r.ok){ r.json().then(function(j){ openChat(Number(reopen), (j && j.user && j.user.name) ? j.user.name : 'Chat', (j && j.user && j.user.avatar) ? j.user.avatar : null); }); } }); }
        })();
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
