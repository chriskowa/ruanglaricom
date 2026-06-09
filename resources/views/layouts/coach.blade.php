<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $isDashboard = true; @endphp
    <script>
        (function() {
            var state = localStorage.getItem('sidebar_expanded');
            if (state === 'false') {
                document.documentElement.classList.add('sidebar-collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
            }
        })();
    </script>
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

        /* Fixed full-height sidebar layout */
        #ph-sidebar {
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 16rem !important;
            height: 100vh !important;
            z-index: 50 !important;
            transform: translateX(-100%);
            transition: transform 0.2s ease-in-out, width 0.2s ease-in-out;
        }
        #ph-sidebar.show {
            transform: translateX(0) !important;
        }
        #pacerhub-nav {
            transition: left 0.2s ease-in-out, width 0.2s ease-in-out;
        }
        #main-content-wrapper {
            transition: padding-left 0.2s ease-in-out;
        }

        @media (min-width: 1024px) {
            #ph-sidebar {
                transform: translateX(0) !important;
            }
            html.sidebar-collapsed #ph-sidebar {
                transform: translateX(-100%) !important;
            }
            html:not(.sidebar-collapsed) #main-content-wrapper {
                padding-left: 16rem !important;
            }
            html:not(.sidebar-collapsed) #pacerhub-nav {
                left: 16rem !important;
                width: calc(100% - 16rem) !important;
            }
            html:not(.sidebar-collapsed) #pacerhub-nav .nav-logo {
                opacity: 0 !important;
                visibility: hidden !important;
                width: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-dark text-white font-sans antialiased flex flex-col min-h-screen">

    <div id="loader" class="loader-overlay">
        <div class="text-4xl font-black italic tracking-tighter animate-pulse">
            COACH<span class="text-primary">HUB</span>
        </div>
    </div>


    <!-- Sidebar backdrop for mobile -->
    <div id="ph-sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"></div>
    @include('layouts.components.pacerhub-sidebar')

    <div id="main-content-wrapper" class="flex flex-col min-h-screen flex-grow">
        @include('layouts.components.pacerhub-nav', ['isDashboard' => true])

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
    </div>

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
        
        function toggleSidebar() {
            if (window.innerWidth >= 1024) {
                var isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_expanded', isCollapsed ? 'false' : 'true');
            } else {
                if (sidebar) {
                    if (sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        if (backdrop) backdrop.classList.add('hidden');
                    } else {
                        sidebar.classList.add('show');
                        if (backdrop) backdrop.classList.remove('hidden');
                    }
                }
            }
        }

        function closeMobileSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.add('hidden');
        }

        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        }
        if (backdrop) {
            backdrop.addEventListener('click', closeMobileSidebar);
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
            }
        });
    })();
</script>
</body>
</html>
