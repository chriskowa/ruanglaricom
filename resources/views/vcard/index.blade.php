<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        dark: '{{ $bgColor }}',
                        'main-text': '{{ $textColor }}',
                        card: '#1e293b',
                        neon: '{{ $accentColor }}',
                        primary: '{{ $accentColor }}',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-glow': 'pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.5s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        'pulse-glow': {
                            '0%, 100%': { opacity: '1', transform: 'scale(1)' },
                            '50%': { opacity: '.8', transform: 'scale(1.02)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: {{ $bgColor }}; color: {{ $textColor }}; -webkit-tap-highlight-color: transparent; }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass:hover {
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(204, 255, 0, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -10px rgba(204, 255, 0, 0.15);
        }
        .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center relative overflow-x-hidden selection:bg-neon selection:text-dark">

    <!-- Background -->
    <div class="fixed inset-0 z-[-1]">
        @if($bgImageUrl)
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-40 blur-sm scale-105" style="background-image: url('{{ $bgImageUrl }}');"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-b from-dark/80 via-dark/95 to-dark"></div>
        <div class="absolute inset-0 bg-grid-pattern opacity-20 mask-image-gradient"></div>
    </div>

    <!-- Main Content -->
    <main class="w-full max-w-md mx-auto px-5 py-10 flex flex-col items-center relative z-10 min-h-screen">
        
        <!-- Header Profile -->
        <div class="flex flex-col items-center text-center mb-8 w-full animate-slide-up" style="animation-delay: 0.1s;">
            <div class="relative group cursor-pointer mb-4">
                <div class="absolute -inset-1 bg-neon rounded-full blur opacity-20 group-hover:opacity-50 transition duration-500"></div>
                <div class="relative w-28 h-28 rounded-full bg-dark p-1 border border-slate-700 overflow-hidden shadow-2xl">
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-contain rounded-full bg-slate-900">
                </div>
                <div class="absolute bottom-1 right-1 bg-blue-500 text-white p-1 rounded-full border-2 border-dark shadow-sm" title="Verified Community">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                </div>
            </div>
            
            <h1 class="text-2xl font-black tracking-tight text-main-text mb-1 flex items-center justify-center gap-2">
                RUANG<span class="text-neon">LARI</span>
            </h1>
            <p class="text-main-text/60 text-sm font-medium max-w-xs leading-relaxed">{{ $description }}</p>

            <!-- Mini Stats/Badges (Static for now, can be dynamic later) -->
            <div class="flex items-center gap-3 mt-4 text-[10px] font-mono font-bold uppercase tracking-wider text-main-text/50">
                <span class="flex items-center gap-1 bg-slate-800/50 px-2 py-1 rounded border border-slate-700">
                    <i class="fas fa-users text-neon"></i> 50K+ Runners
                </span>
                <span class="flex items-center gap-1 bg-slate-800/50 px-2 py-1 rounded border border-slate-700">
                    <i class="fas fa-calendar-check text-neon"></i> 100+ Events
                </span>
            </div>
        </div>

        <!-- Featured Links (Big Cards) -->
        <div class="w-full space-y-4 mb-6 animate-slide-up" style="animation-delay: 0.2s;">
            @foreach($featuredLinks as $link)
            @php
                $hasCustomBg = !empty($link['bg_color']);
                $hasCustomText = !empty($link['text_color']);

                // Background Logic
                $bgStyle = $hasCustomBg ? "background-color: {$link['bg_color']}; border-color: rgba(255,255,255,0.1);" : '';
                $bgClass = $hasCustomBg ? 'shadow-xl' : (isset($link['color']) && str_contains($link['color'], 'from-') ? 'bg-gradient-to-br ' . $link['color'] : 'bg-gradient-to-br from-slate-800 to-slate-900 border-slate-700');

                // Glow Logic
                $glowStyle = $hasCustomBg ? "background-color: {$link['bg_color']};" : '';
                $glowClass = $hasCustomBg ? '' : (isset($link['color']) ? $link['color'] : 'bg-neon');

                // Text Logic
                $textStyle = $hasCustomText ? "color: {$link['text_color']} !important;" : '';
                $textClass = $hasCustomText ? '' : (isset($link['color']) && str_contains($link['color'], 'text-slate-900') ? 'text-slate-900' : 'text-main-text');
                
                // Secondary Elements Logic
                $iconBg = isset($link['color']) && str_contains($link['color'], 'text-slate-900') ? 'bg-black/10' : 'bg-white/10';
                $badgeBg = isset($link['color']) && str_contains($link['color'], 'text-slate-900') ? 'bg-black/10' : 'bg-white/10';
            @endphp
            <a href="{{ $link['url'] }}" class="block w-full group relative transform transition-all hover:scale-[1.02] active:scale-[0.98]">
                <div class="absolute -inset-0.5 rounded-2xl blur opacity-30 group-hover:opacity-60 transition duration-500 {{ $glowClass }}" style="{{ $glowStyle }}"></div>
                <div class="relative {{ $bgClass }} rounded-xl p-5 border border-white/10 shadow-xl flex items-center justify-between overflow-hidden" style="{{ $bgStyle }}">
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-12 h-12 rounded-full {{ $iconBg }} backdrop-blur-sm flex items-center justify-center text-2xl shadow-inner">
                            <i class="fas fa-{{ $link['icon'] ?? 'star' }} {{ $textClass }}" style="{{ $textStyle }}"></i>
                        </div>
                        <div class="text-left">
                            @if(isset($link['badge']))
                            <div class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest mb-1 {{ $badgeBg }}">
                                {{ $link['badge'] }}
                            </div>
                            @endif
                            <div class="font-bold text-lg leading-tight {{ $textClass }}" style="{{ $textStyle }}">{{ $link['title'] }}</div>
                        </div>
                    </div>
                    <div class="relative z-10 w-8 h-8 rounded-full {{ $iconBg }} flex items-center justify-center group-hover:translate-x-1 transition-transform">
                        <i class="fas fa-arrow-right text-xs {{ $textClass }}" style="{{ $textStyle }}"></i>
                    </div>
                    
                    <!-- Decorative BG Pattern -->
                    <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-4 translate-y-4">
                        <i class="fas fa-{{ $link['icon'] ?? 'star' }} text-9xl"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- Grid Links (Small Cards) -->
        <div class="grid grid-cols-2 gap-3 w-full mb-8 animate-slide-up" style="animation-delay: 0.3s;">
            @foreach($links as $link)
            <a href="{{ $link['url'] }}" {{ isset($link['external']) && $link['external'] ? 'target="_blank"' : '' }} class="glass rounded-2xl p-4 flex flex-col items-center justify-center text-center group h-32 transition-all relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="w-10 h-10 mb-3 rounded-full bg-slate-800/80 flex items-center justify-center border border-slate-700 group-hover:border-neon/50 group-hover:scale-110 transition-all duration-300 shadow-lg">
                    <i class="fas fa-{{ $link['icon'] ?? 'link' }} text-main-text/80 group-hover:text-neon text-lg transition-colors"></i>
                </div>
                <span class="text-xs font-bold text-main-text/80 group-hover:text-main-text leading-tight px-1 relative z-10">
                    {{ $link['title'] }}
                </span>
            </a>
            @endforeach
        </div>

        <!-- Ads Slot -->
        @if($adsUrl)
        <div class="w-full mb-8 animate-slide-up" style="animation-delay: 0.4s;">
            <a href="{{ $adsUrl }}" target="_blank" class="block relative group overflow-hidden rounded-2xl border border-dashed border-slate-700 hover:border-neon/50 bg-slate-900/30 transition-all">
                <div class="absolute inset-0 bg-repeat opacity-5" style="background-image: radial-gradient(#ccff00 1px, transparent 1px); background-size: 10px 10px;"></div>
                <div class="p-4 flex items-center gap-4 relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0 group-hover:bg-neon group-hover:text-dark transition-colors">
                        <i class="fas fa-ad text-main-text/60 group-hover:text-dark"></i>
                    </div>
                    <div class="flex-grow text-left">
                        <h3 class="text-sm font-bold text-main-text/90 group-hover:text-main-text">{{ $adsTitle }}</h3>
                        <p class="text-xs text-main-text/50 group-hover:text-main-text/60">{{ $adsDescription }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-main-text/40 group-hover:text-neon"></i>
                </div>
            </a>
        </div>
        @endif

        <!-- Social Footer -->
        <footer class="mt-auto animate-slide-up" style="animation-delay: 0.5s;">
            <div class="flex justify-center gap-4 mb-6">
                @foreach($socialLinks as $social)
                <a href="{{ $social['url'] }}" target="_blank" class="w-12 h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-main-text/60 hover:text-main-text hover:border-slate-500 hover:bg-slate-700 hover:-translate-y-1 transition-all duration-300 shadow-lg" title="{{ $social['title'] }}">
                    <i class="fab fa-{{ $social['icon'] ?? 'link' }} text-xl"></i>
                </a>
                @endforeach
            </div>
            
            <div class="text-center space-y-2">
                <p class="text-main-text/40 text-[10px] font-mono uppercase tracking-widest">Powered by RuangLari Platform</p>
                <div class="h-1 w-10 bg-slate-800 mx-auto rounded-full"></div>
                <p class="text-main-text/30 text-[10px]">&copy; {{ date('Y') }} Ruang Lari Indonesia</p>
            </div>
        </footer>

    </main>

    <!-- Floating Action Button (Optional, maybe for Home) -->
    <a href="{{ route('home') }}" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-neon rounded-full shadow-lg shadow-neon/20 flex items-center justify-center text-slate-900 hover:scale-110 transition-transform md:hidden">
        <i class="fas fa-home"></i>
    </a>

</body>
</html>
