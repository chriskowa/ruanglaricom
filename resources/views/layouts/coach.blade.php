<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coach Dashboard - RuangLari')</title>

     <!-- Primary Meta Tags -->
    <title>Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans</title>
    <meta name="title" content="Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans">
    <meta name="description" content="Ruang Lari adalah platform komunitas lari terbesar di Indonesia. Temukan pacer, ikuti event, pantau progres, dan raih personal best Anda. Dapatkan rencana latihan eksklusif, analisis performa, dan diskon event.">

    <!-- Keywords -->
    <meta name="keywords" content="ruang lari, komunitas lari indonesia, pacer indonesia, event lari, kalender lari, training plan, analisis performa, strava indonesia, sepatu lari lokal, fotografer olahraga, running calculator, personal best, marathon indonesia, 5K, 10K, half marathon, full marathon">

    <!-- Author -->
    <meta name="author" content="Ruang Lari Indonesia">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ruanglari.id/">
    <meta property="og:title" content="Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans">
    <meta property="og:description" content="Gabung dengan Ruang Lari, komunitas lari terbesar di Indonesia. Ikuti event, temukan pacer, dan pecahkan personal record Anda.">
    <meta property="og:image" content="https://ruanglari.id/assets/images/ruanglari-cover.jpg">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://ruanglari.id/">
    <meta name="twitter:title" content="Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans">
    <meta name="twitter:description" content="Platform all-in-one untuk pelari, pacer, dan pelatih. Pantau progres, ikuti event, dan raih personal best Anda.">
    <meta name="twitter:image" content="https://ruanglari.id/assets/images/ruanglari-cover.jpg">

    <!-- Canonical -->
    <link rel="canonical" href="{{ url()->current() }}">   

    <!-- Favicon default -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Versi PNG -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">

    <!-- Versi Apple Touch -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/green/apple-touch-icon.png') }}">

    <!-- Versi Android/Manifest -->
    <link rel="manifest" href="{{ asset('images/green/site.webmanifest') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a',
                        card: '#1e293b',
                        neon: '#06b6d4', // Coach theme is Cyan/Purple
                        primary: '#06b6d4',
                        secondary: '#a855f7',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    
    <style>
        .loader-overlay { position: fixed; inset: 0; background: #0f172a; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s; }
        .animate-pulse { animation: loaderPulse 1.5s ease-in-out infinite; }
        @keyframes loaderPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(.98)} }
        
        .glass-panel{background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.05)}
        [v-cloak] { display: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-dark text-white font-sans antialiased flex flex-col min-h-screen">

    <div id="loader" class="loader-overlay">
        <div class="text-4xl font-black italic tracking-tighter animate-pulse">
            COACH<span class="text-primary">HUB</span>
        </div>
    </div>

    @include('layouts.components.pacerhub-nav')

@if(isset($withSidebar) && $withSidebar)
    <div id="ph-sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
    @include('layouts.components.pacerhub-sidebar')
@endif

<main class="flex-grow w-full">
    <div class="pt-20">
        <div class="max-w-7xl mx-auto px-6">
            <nav aria-label="Breadcrumb" class="text-xs font-mono text-slate-400">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <ol class="flex items-center gap-2">
                        <li><a href="{{ route('coach.dashboard') }}" class="hover:text-neon">Dashboard</a></li>
                        <li class="text-slate-600">/</li>
                        <li class="text-slate-300">@yield('title', 'Coach')</li>
                    </ol>
                @endif
            </nav>
        </div>
    </div>
    @yield('content')
</main>

@include('layouts.components.pacerhub-footer')

    @stack('scripts')
<script>
    window.addEventListener('load', function() {
        var loader = document.getElementById('loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(function(){ loader.style.display = 'none'; }, 500);
        }
    });
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
        if(backdrop){ backdrop.addEventListener('click', closeSidebar); }
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeSidebar(); } });
    })();
</script>
<script>
    (function () {
        var isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        var authRole = @json(auth()->user() ? auth()->user()->role : '');
        var navBtn = document.getElementById('nav-bell-btn');
        var dropdown = document.getElementById('nav-bell-dropdown');
        var badge = document.getElementById('notification-badge');
        var list = document.getElementById('notification-list');
        var markAll = document.getElementById('mark-all-read');
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

        function getUrl(notif) {
            if (notif.reference_type === 'Post' && notif.reference_id) {
                return @json(route('feed.index')) + '#post-' + notif.reference_id;
            }
            if (notif.reference_type === 'EventSubmission' && notif.reference_id && authRole === 'admin') {
                return @json(route('admin.event-submissions.show', ':id')).replace(':id', notif.reference_id);
            }
            return @json(route('notifications.index'));
        }

        function setBadge(count) {
            if (!badge) return;
            var num = Number(count) || 0;
            if (num > 0) {
                badge.textContent = num > 99 ? '99+' : String(num);
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }
        }

        function render(items) {
            if (!list) return;
            if (!items || !items.length) {
                list.innerHTML = '<div class="p-8 text-center text-slate-500 text-sm">Tidak ada notifikasi</div>';
                return;
            }
            list.innerHTML = '';
            items.forEach(function (n) {
                var wrap = document.createElement('a');
                var url = getUrl(n);
                wrap.href = url;
                wrap.className = 'block px-4 py-3 border-b border-slate-800 hover:bg-slate-800/60 transition';
                wrap.dataset.notificationId = n.id;
                wrap.innerHTML =
                    '<div class="text-white font-bold text-sm">'+String(n.title || '')+'</div>' +
                    '<div class="text-slate-300 text-xs mt-0.5">'+String(n.message || '')+'</div>' +
                    '<div class="text-[10px] text-slate-500 font-mono mt-1">'+(n.created_at ? dayjs(n.created_at).format('DD/MM/YYYY, HH.mm.ss') : '')+'</div>';
                list.appendChild(wrap);
            });
        }

        function fetchUnread() {
            if (!isAuthenticated) return;
            fetch(@json(route('notifications.unread', [], false)), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error('err'); return r.json(); })
                .then(function (data) {
                    if (!data) return;
                    setBadge(data.count);
                    if (dropdown && !dropdown.classList.contains('hidden')) {
                        render(data.notifications || []);
                    }
                })
                .catch(function () {});
        }

        function markRead(id) {
            return fetch(@json(route('notifications.read', ':id', false)).replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function (r) { return r.json(); });
        }

        function markAllRead() {
            return fetch(@json(route('notifications.read-all', [], false)), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function (r) { return r.json(); });
        }

        if (navBtn && dropdown) {
            navBtn.addEventListener('click', function () {
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    if (list) list.innerHTML = '<div class="p-8 text-center text-slate-500 text-sm">Loading...</div>';
                    fetchUnread();
                }
            });

            document.addEventListener('click', function (e) {
                if (!dropdown) return;
                if (dropdown.classList.contains('hidden')) return;
                if (e.target.closest('#nav-bell-dropdown') || e.target.closest('#nav-bell-btn')) return;
                dropdown.classList.add('hidden');
            });
        }

        if (list) {
            list.addEventListener('click', function (e) {
                var a = e.target.closest('a[data-notification-id]');
                if (!a) return;
                var id = a.dataset.notificationId;
                if (!id) return;
                e.preventDefault();
                markRead(id)
                    .catch(function () {})
                    .finally(function () {
                        dropdown && dropdown.classList.add('hidden');
                        fetchUnread();
                        window.location.href = a.href;
                    });
            });
        }

        if (markAll) {
            markAll.addEventListener('click', function () {
                markAllRead()
                    .catch(function () {})
                    .finally(function () {
                        fetchUnread();
                        if (list) list.innerHTML = '<div class="p-8 text-center text-slate-500 text-sm">Tidak ada notifikasi</div>';
                    });
            });
        }

        fetchUnread();
        setInterval(fetchUnread, 30000);
    })();
</script>
</body>
</html>
