<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PacerHub - Race Calendar & Analytics')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/duration.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
    <script>
        dayjs.extend(window.dayjs_plugin_duration);
        dayjs.extend(window.dayjs_plugin_relativeTime);
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
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
        
        @include('layouts.components.pacerhub-nav')

        @if(isset($withSidebar) && $withSidebar)
            <div id="ph-sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
            @include('layouts.components.pacerhub-sidebar')
        @endif

        <main class="flex-grow w-full">
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
</body>
</html>
