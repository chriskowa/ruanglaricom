<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Ruang Lari') }} - Temukan Buddy Lari Terdekat | Run Connect</title>
    <meta name="description" content="Cari teman lari (running buddy) terdekat, buat running thread, dan bergabung dengan komunitas pelari di sekitar Anda secara real-time melalui Run Connect Ruang Lari.">
    <meta name="keywords" content="teman lari, running buddy, komunitas lari, run connect, ruang lari, cari teman lari, peta pelari, jadwal lari">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/logo.png">
    
    <!-- PWA / Web App Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e293b">
    
    <!-- iOS / Safari Web App Settings -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="RuangLari">
    <link rel="apple-touch-icon" href="/images/ruanglari-512x512.png">
    
    <!-- OpenGraph (OG) Meta Tags -->
    <meta property="og:title" content="{{ $meta_title ?? 'Ruang Lari - Temukan Buddy Lari Terdekat | Run Connect' }}">
    <meta property="og:description" content="{{ $meta_description ?? 'Cari teman lari (running buddy) terdekat, buat running thread, dan bergabung dengan komunitas pelari di sekitar Anda secara real-time melalui Run Connect Ruang Lari.' }}">
    <meta property="og:image" content="{{ $meta_image ?? url('/images/ruanglari-512x512.png') }}">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Ruang Lari - Temukan Buddy Lari Terdekat | Run Connect">
    <meta name="twitter:description" content="Cari teman lari terdekat dan buat running thread melalui platform Run Connect Ruang Lari.">
    <meta name="twitter:image" content="/images/ruanglari-512x512.png">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebApplication",
      "name": "Run Connect - Ruang Lari",
      "url": "{{ url()->current() }}",
      "description": "Temukan buddy lari terdekat, buat running thread, dan bergabung dengan komunitas pelari di sekitar Anda secara real-time.",
      "applicationCategory": "HealthApplication",
      "operatingSystem": "All",
      "browserRequirements": "Requires JavaScript. Requires HTML5."
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <style>
        .loader-overlay { position: fixed; inset: 0; background: #0f172a; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s; }
        .loader-text { font-family: 'Inter', sans-serif; font-weight: 800; font-style: italic; letter-spacing: -0.05em; }
        .text-primary { color: #ccff00; }
        .animate-pulse { animation: loaderPulse 1.5s ease-in-out infinite; }
        @keyframes loaderPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(.98)} }
    </style>
</head>
<body class="font-sans antialiased">
    <div id="loader" class="loader-overlay">
        <div class="text-4xl loader-text animate-pulse text-white">
            RUANG<span class="text-primary">LARI</span>
        </div>
    </div>
    @inertia
    <script>
        (function() {
            function hideLoader() {
                var loader = document.getElementById('loader');
                if (!loader) return;
                if (loader.dataset.hidden === '1') return;
                loader.style.opacity = '0';
                setTimeout(function(){
                    loader.dataset.hidden = '1';
                    try { loader.remove(); } catch (e) { loader.style.display = 'none'; }
                }, 500);
            }
            window.phHideLoader = hideLoader;
            window.addEventListener('load', hideLoader);
            document.addEventListener('DOMContentLoaded', hideLoader);
            window.addEventListener('pageshow', hideLoader);
        })();
    </script>
</body>
</html>









