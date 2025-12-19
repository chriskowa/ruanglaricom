<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ruang Lari')</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/chartist/css/chartist.min.css') }}">
    <link href="{{ asset('vendor/bootstrap-select/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/owl-carousel/owl.carousel.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    
    @stack('styles')
    <style>
        .loader-overlay { position: fixed; inset: 0; background: #0f172a; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s; }
        .text-primary { color: #ccff00 !important; }
        .animate-pulse { animation: loaderPulse 1.5s ease-in-out infinite; }
        @keyframes loaderPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.98); }
        }
    </style>
</head>
<body>
    <div id="loader" class="loader-overlay">
        <div class="text-4xl font-black italic tracking-tighter animate-pulse">
            RUANG<span class="text-primary">LARI</span>
        </div>
    </div>

    <div id="main-wrapper">
        <!-- Nav header start -->
        <div class="nav-header">
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="brand-logo" aria-label="Ruang Lari">
                <img class="logo-abbr" src="{{ asset('images/logo.png') }}" alt="">
                <img class="logo-compact" src="{{ asset('images/logo-text.png') }}" alt="">
                <img class="brand-title" src="{{ asset('images/logo-text.png') }}" alt="">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!-- Nav header end -->
        
        <!-- Chat box start -->
        @include('layouts.components.chatbox')
        <!-- Chat box end -->
        
        <!-- Header start -->
        @include('layouts.components.header')
        <!-- Header end -->
        
        <!-- Sidebar start -->
        @include('layouts.components.sidebar')
        <!-- Sidebar end -->
        
        <!-- Content body start -->
        <div class="content-body default-height">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
        <!-- Content body end -->
        
        <!-- Footer start -->
        @include('layouts.components.footer')
        <!-- Footer end -->
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('vendor/chart-js/chart.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/owl-carousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    
    @include('layouts.components.header-scripts')
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
