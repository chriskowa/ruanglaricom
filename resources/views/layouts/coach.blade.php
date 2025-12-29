<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coach Dashboard - RuangLari')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    
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

    <main class="flex-grow w-full">
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
    </script>
</body>
</html>
