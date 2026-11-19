@php $lightMode = false; @endphp
<style>
[x-cloak]{display:none !important;}
.rl-search-btn {
    width: 36px;
    height: 36px;
    padding: 0;
    overflow: hidden;
    transition: width 220ms cubic-bezier(0.22, 1, 0.36, 1), padding 220ms ease, border-color 150ms ease, background-color 150ms ease;
}
.rl-search-label {
    display: none;
    max-width: 0;
    opacity: 0;
    overflow: hidden;
    white-space: nowrap;
    transition: max-width 220ms cubic-bezier(0.22, 1, 0.36, 1), opacity 180ms ease, margin-left 220ms ease;
}
@media (min-width: 768px) {
    .rl-search-btn {
        width: 38px;
        height: 38px;
    }
    .rl-search-label {
        display: inline-block;
    }
    .rl-search-btn:hover,
    .rl-search-btn:focus-visible {
        width: 90px;
        padding: 0 12px;
        border-color: #334155;
        background-color: #1e293b;
    }
    .rl-search-btn:hover .rl-search-label,
    .rl-search-btn:focus-visible .rl-search-label {
        max-width: 48px;
        opacity: 1;
        margin-left: 6px;
    }
}
</style>
<nav id="pacerhub-nav" class="border-b border-slate-800 bg-slate-950 fixed w-full z-40">
    @php
        $headerMenu = \Illuminate\Support\Facades\Cache::remember('nav.header_menu_v1', 3600, function () {
            try {
                return \App\Models\Menu::where('location', 'header')
                    ->where('is_active', true)
                    ->with(['items' => function($q) {
                        $q->where(function($sub) {
                            $sub->whereNull('parent_id')->orWhere('parent_id', 0);
                        })
                        ->where('is_active', true)
                        ->with(['children' => function($cq) {
                            $cq->where('is_active', true)->orderBy('order');
                        }])
                        ->orderBy('order');
                    }])
                    ->first();
            } catch (\Throwable $e) {
                return null;
            }
        });

        $hasNavItems = $headerMenu && $headerMenu->items && $headerMenu->items->where('is_active', true)->count() > 0;
    @endphp
    <div class="max-w-7xl mx-auto px-3 sm:px-6">
        <div class="flex items-center justify-between h-16 gap-2">
            <!-- Left Side: Logo & Sidebar Toggle -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 min-w-0 shrink-0">
                @if(isset($isDashboard) && $isDashboard)
                <button id="ph-sidebar-toggle" class="inline-flex items-center justify-center w-9 h-9 rounded-md hover:bg-slate-900 text-slate-200 transition-colors cursor-pointer shrink-0" title="Toggle Sidebar" aria-label="Toggle Sidebar">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @endif
                <div class="flex items-center gap-1 nav-logo shrink-0">
                    <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-6 sm:h-8 w-auto">
                    <a href="{{ url('/') }}"
                        class="text-sm sm:text-lg md:text-xl font-black italic tracking-tighter flex items-center whitespace-nowrap {{ $lightMode ? 'text-slate-900' : 'text-white' }}"
                        title="RuangLari Utama">
                        RUANG<span class="pl-0.5 sm:pl-1" style="{{ $lightMode ? 'color: #000000ff;' : 'color: #ccff00;' }}">LARI</span>
                    </a>
                </div>
            </div>
       
            <div class="flex-1 hidden md:flex items-center justify-center gap-1">
                @if($hasNavItems)
                    @foreach($headerMenu->items as $item)
                        @if($item->children && $item->children->count() > 0)
                            <!-- Dropdown for {{ $item->title }} -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @keydown.escape.window="open = false" @click.outside="open = false">
                                <button id="nav-{{ Str::slug($item->title) }}-btn" type="button" class="flex items-center gap-1 px-3 py-2 text-sm font-bold {{ $lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon' }} transition-colors focus:outline-none" :class="{ '{{ $lightMode ? 'text-slate-900' : 'text-neon' }}': open }" @click="open = !open">
                                    {{ $item->title }}
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     x-cloak
                                     style="display: none;"
                                     class="absolute left-0 mt-2 w-52 bg-slate-900 border border-slate-800 rounded-lg shadow-lg origin-top-left z-50">
                                    <div class="p-1.5 space-y-0.5">
                                        @foreach($item->children as $child)
                                            <a href="{{ url($child->url) }}" target="{{ $child->target ?: '_self' }}" class="block px-3.5 py-2 text-sm text-slate-200 hover:bg-slate-800 hover:text-white rounded-md transition-colors">
                                                {{ $child->title }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Single Link {{ $item->title }} -->
                            <a href="{{ url($item->url) }}" target="{{ $item->target ?: '_self' }}" class="px-3 py-2 text-sm font-bold {{ request()->is(trim($item->url, '/') . '*') ? ($lightMode ? 'text-slate-900 border-b-2 border-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <!-- Fallback if no dynamic menu items -->
                    <a href="{{ route('events.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('events.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Event</a>
                    <a href="{{ route('programs.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('programs.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Program Lari</a>
                    <a href="{{ route('gpx.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('gpx.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Rute GPX</a>
                    <a href="{{ route('marketplace.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('marketplace.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Marketplace</a>
                    <a href="{{ route('calendar.public') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('calendar.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Calendar</a>
                    <a href="{{ route('run-connect.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('run-connect.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Run Connect</a>
                    <a href="{{ route('eo.landing') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('eo.landing*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Untuk EO</a>
                    <a href="{{ route('blog.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('blog.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:text-neon') }} transition-colors">Journal</a>
                @endif
            </div>
         
            
            <!-- Right Side: Navigation & Actions -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <button
                    type="button"
                    id="nav-quick-search-btn"
                    class="rl-search-btn group inline-flex items-center justify-center rounded-md bg-slate-900 border border-slate-800 text-slate-200 hover:text-white cursor-pointer shrink-0"
                    title="Cari Tools, Event, Program, Blog (Ctrl+K)"
                    aria-label="Cari Tools, Event, Program, Blog"
                >
                    <svg class="w-4 h-4 shrink-0 text-slate-300 group-hover:text-lime-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                    </svg>
                    <span class="rl-search-label text-xs font-semibold text-slate-200">
                        Cari
                    </span>
                </button>

                <!-- Cart Icon -->
                @auth
                <a href="{{ route('marketplace.cart.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-md hover:bg-slate-900 text-slate-200 hover:text-white transition-colors relative shrink-0" title="Cart" aria-label="Cart">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span id="nav-cart-count" class="absolute top-0.5 right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                </a>
                @endauth

                <!-- Notifications -->
                @auth
                <div class="relative shrink-0" id="notification-container">
                    <button id="nav-bell-btn" class="inline-flex items-center justify-center w-9 h-9 rounded-md hover:bg-slate-900 text-slate-200 hover:text-white transition-colors relative cursor-pointer" title="Notifications" aria-label="Notifications">
                        @include('layouts.components.svg-bell')
                        <span id="notification-badge" class="absolute top-0.5 right-0.5 min-w-[16px] h-[16px] px-1 bg-red-500 text-[10px] font-bold text-white rounded-full flex items-center justify-center border border-slate-950 hidden"></span>
                    </button>
                    <div id="nav-bell-dropdown" class="fixed sm:absolute left-3 right-3 sm:left-auto sm:right-0 top-16 sm:top-auto sm:mt-3 sm:w-96 bg-slate-900 border border-slate-800 shadow-xl rounded-lg hidden overflow-hidden origin-top-right z-50">
                        <div class="p-4 border-b border-slate-800 flex justify-between items-center">
                            <h3 class="font-bold text-white text-sm">Notifications</h3>
                            <button id="mark-all-read" class="text-xs text-lime-400 hover:text-lime-300 transition-colors cursor-pointer">Mark all read</button>
                        </div>
                        <div id="notification-list" class="max-h-[60vh] sm:max-h-[400px] overflow-y-auto">
                            <div class="p-8 text-center text-slate-400 text-sm">Loading...</div>
                        </div>
                        <div class="p-3 border-t border-slate-800 bg-slate-950">
                            <a href="{{ route('notifications.index') }}" class="block w-full text-center py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>
                @endauth
                
                @auth
                <!-- User Profile Dropdown -->
                <div class="relative shrink-0" id="user-menu-container">
                    <button id="user-menu-btn" class="inline-flex items-center gap-1.5 h-9 px-1 sm:px-2 rounded-md hover:bg-slate-900 transition-colors cursor-pointer">
                        <img class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover border border-slate-700" src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ auth()->user()->name }}">
                        @php
                            $name = auth()->user()->name;
                            $initials = collect(explode(' ', $name))
                                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                ->implode('');
                        @endphp                            
                        <span class="hidden md:block text-sm font-medium text-slate-200">{{ $initials }}</span>
                        <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    
                    <div id="user-menu-dropdown" class="absolute right-0 mt-3 w-56 bg-slate-900 border border-slate-800 shadow-xl rounded-lg hidden origin-top-right z-50">
                        <div class="p-4 border-b {{ $lightMode ? 'border-slate-100' : 'border-slate-800' }}">
                            <p class="text-sm font-bold {{ $lightMode ? 'text-slate-900' : 'text-white' }}">{{ auth()->user()->name }}</p>
                            <p class="text-xs {{ $lightMode ? 'text-slate-500' : 'text-primary' }} font-medium uppercase tracking-wider mt-0.5">{{ auth()->user()->role }}</p>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm {{ $lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }} rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                Dashboard
                            </a>
                            <a href="{{ Route::has(auth()->user()->role . '.profile') ? route(auth()->user()->role . '.profile') : route('profile.show') }}" class="flex items-center gap-3 px-3 py-2 text-sm {{ $lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }} rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profile
                            </a>
                            @if(auth()->user()->isRunner() || auth()->user()->isAdmin())
                            <a href="{{ route('runner.integrations.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm {{ $lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }} rounded-lg transition-colors">
                                <i class="fas fa-plug-circle-check w-4 text-center text-xs"></i>
                                Integrasi Akun
                            </a>
                            @endif
                            @if(auth()->user()->role !== 'eo')
                            <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm {{ $lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }} rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Chat
                            </a>
                            @endif
                            <a href="{{ route('notifications.index') }}" class="flex items-center gap-1 px-3 py-2 text-sm {{ $lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }} rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                Notifications
                            </a>
                            <div class="h-px bg-slate-800 my-1"></div>
                            <a href="{{ route('pacer.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-200 hover:bg-slate-800 hover:text-white rounded-md transition-colors">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Direktori Pacers
                            </a>
                            <a href="{{ route('pacer.register') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-semibold text-lime-400 hover:bg-slate-800 rounded-md transition-colors">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                Join Pacer
                            </a>
                            <div class="h-px {{ $lightMode ? 'bg-slate-100' : 'bg-slate-800' }} my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
                
                @guest
                <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                    <button onclick="openLoginModal()" class="px-2 sm:px-3 h-9 rounded-md text-xs sm:text-sm font-semibold text-slate-200 hover:text-white hover:bg-slate-900 transition-colors cursor-pointer whitespace-nowrap">Login</button>
                    <button onclick="openRegisterModal()" class="px-2.5 sm:px-4 h-9 text-xs sm:text-sm font-bold bg-lime-400 hover:bg-lime-300 text-slate-950 rounded-md transition-colors cursor-pointer whitespace-nowrap">Register</button>
                </div>
                @endguest

                @if(!isset($isDashboard) || !$isDashboard)
                <button id="mobile-menu-toggle" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-200 hover:text-white transition-colors cursor-pointer shrink-0" title="Menu" aria-label="Buka Menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @endif
            </div>
        </div>
    </div>
</nav>

<div id="mobile-menu-panel" class="md:hidden hidden fixed top-16 left-0 right-0 z-40 bg-slate-950 border-b border-slate-800 rounded-b-lg shadow-xl max-h-[80vh] overflow-y-auto">
    <div class="p-3 grid grid-cols-1 gap-1">
        @if($hasNavItems)
            @foreach($headerMenu->items as $item)
                @if($item->children && $item->children->count() > 0)
                    <!-- Submenu for {{ $item->title }} -->
                    <div class="px-3 py-2">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2">{{ $item->title }}</div>
                        @foreach($item->children as $child)
                            <a href="{{ url($child->url) }}" target="{{ $child->target ?: '_self' }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">
                                {{ $child->title }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- Single Link {{ $item->title }} -->
                    <a href="{{ url($item->url) }}" target="{{ $item->target ?: '_self' }}" class="px-3 py-3 rounded-lg {{ request()->is(trim($item->url, '/') . '*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">
                        {{ $item->title }}
                    </a>
                @endif
            @endforeach
        @else
            <!-- Fallback -->
            <a href="{{ route('events.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('events.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Event</a>
            <a href="{{ route('programs.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('programs.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Program Lari</a>
            <a href="{{ route('gpx.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('gpx.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Rute GPX</a>
            <a href="{{ route('marketplace.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('marketplace.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Marketplace</a>
            <a href="{{ route('calendar.public') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('calendar.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Calendar</a>
            <a href="{{ route('run-connect.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('run-connect.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Run Connect</a>
            <a href="{{ route('eo.landing') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('eo.landing*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Untuk EO</a>
            <a href="{{ route('blog.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('blog.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-200 hover:bg-slate-800 hover:text-white') }} transition-colors font-bold">Journal</a>
        @endif


        @auth
        <div class="px-3 py-2 border-t {{ $lightMode ? 'border-slate-100' : 'border-slate-800' }} mt-2">
            <div class="flex items-center gap-3 mb-4 px-2 py-2 {{ $lightMode ? 'bg-slate-50' : 'bg-slate-800/50' }} rounded-lg">
                 <img class="w-10 h-10 rounded-full object-cover border {{ $lightMode ? 'border-slate-200' : 'border-slate-600' }}" src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ auth()->user()->name }}">
                 <div>
                     <div class="font-bold {{ $lightMode ? 'text-slate-900' : 'text-white' }} text-sm">{{ auth()->user()->name }}</div>
                     <div class="text-xs text-primary font-medium uppercase tracking-wider">{{ auth()->user()->role }}</div>
                 </div>
            </div>

            <div class="text-xs font-bold text-slate-400 uppercase mb-2">Account</div>
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">
                Dashboard
            </a>
            <a href="{{ Route::has(auth()->user()->role . '.profile') ? route(auth()->user()->role . '.profile') : route('profile.show') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">
                Profile
            </a>
            <a href="{{ route('marketplace.cart.index') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">
                Cart
                <span id="mobile-cart-count" class="ml-2 bg-neon text-dark text-[10px] font-bold px-1.5 rounded-full hidden">0</span>
            </a>
            <a href="{{ route('chat.index') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">
                Messages
            </a>
            <a href="{{ route('notifications.index') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">
                Notifications
                <span id="mobile-notif-count" class="ml-2 bg-red-500 text-white text-[10px] font-bold px-1.5 rounded-full hidden">0</span>
            </a>
            
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-left block py-2 text-red-400 hover:text-red-300 pl-4 border-l border-transparent hover:border-red-500 transition-colors">
                    Logout
                </button>
            </form>
        </div>
        @endauth

        @guest
        <div class="px-3 py-2 border-t {{ $lightMode ? 'border-slate-100' : 'border-slate-800' }} mt-2">
             <div class="text-xs font-bold text-slate-400 uppercase mb-2">Account</div>
             <a href="{{ route('login') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">Login</a>
             <a href="{{ route('register') }}" class="block py-2 {{ $lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-200 hover:text-white' }} pl-4 border-l {{ $lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon' }} transition-colors">Register</a>
        </div>
        @endguest
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    const userRole = {!! json_encode(auth()->check() ? auth()->user()->role : null) !!};
    const supportsCart = ['runner', 'coach', 'eo', 'admin'].includes(userRole);
    const supportsNotif = isAuthenticated;

    // Dropdown Toggles
    const toggles = [
        { btn: 'nav-bell-btn', menu: 'nav-bell-dropdown' },
        { btn: 'user-menu-btn', menu: 'user-menu-dropdown' }
    ];

    toggles.forEach(toggle => {
        const btn = document.getElementById(toggle.btn);
        const menu = document.getElementById(toggle.menu);
        
        if (btn && menu) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = menu.classList.contains('hidden');
                // Close all first
                toggles.forEach(t => document.getElementById(t.menu)?.classList.add('hidden'));
                
                if (isHidden) {
                    menu.classList.remove('hidden');
                    // Lazy-load notifications when the bell is opened
                    if (toggle.menu === 'nav-bell-dropdown') {
                        fetchNotifications(true);
                    }
                }
            });
        }
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        toggles.forEach(toggle => {
            const menu = document.getElementById(toggle.menu);
            const btn = document.getElementById(toggle.btn);
            if (menu && !menu.classList.contains('hidden') && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    });

    const notifBadge = document.getElementById('notification-badge');
    const mobileNotifBadge = document.getElementById('mobile-notif-count');
    const notifList = document.getElementById('notification-list');

    function getNotificationUrl(notif) {
        if (notif.reference_type === 'Post' && notif.reference_id) {
            return @json(route('feed.index')) + '#post-' + notif.reference_id;
        }
        if (notif.reference_type === 'EventSubmission' && notif.reference_id && userRole === 'admin') {
            return @json(route('admin.event-submissions.show', ':id')).replace(':id', notif.reference_id);
        }
        if ((notif.reference_type === 'MasterGpx' || notif.type === 'gpx_submission' || notif.type === 'gpx_published' || notif.type === 'gpx_approved' || notif.type === 'gpx_title_suggestion') && notif.reference_id) {
            if (userRole === 'admin') {
                if (notif.type === 'gpx_title_suggestion') {
                    return @json(route('admin.master-gpx.index', ['tab' => 'suggestions']));
                }
                return @json(route('admin.master-gpx.index'));
            }
            return @json(route('gpx.show', ':id')).replace(':id', notif.reference_id);
        }
        if (notif.reference_type === 'App\\Models\\Marketplace\\MarketplaceOrder' || notif.reference_type === 'MarketplaceOrder' || notif.type === 'marketplace_order_paid' || notif.type === 'marketplace_sale') {
            if (userRole === 'admin' && notif.reference_id) {
                return @json(route('admin.marketplace.orders.show', ':id')).replace(':id', notif.reference_id);
            }
            if (notif.reference_id) {
                return @json(route('marketplace.orders.show', ':id')).replace(':id', notif.reference_id);
            }
        }
        if (notif.reference_type === 'App\\Models\\RunningAnalysis\\AnalysisRequest') {
            if (userRole === 'admin' && notif.reference_id) {
                return @json(route('admin.running-analysis.requests.show', ':id')).replace(':id', notif.reference_id);
            }
            if (notif.reference_id) {
                return @json(route('runner.analysis-requests.index'));
            }
        }
        if (notif.reference_type === 'CoachInvoice' || notif.reference_type === 'App\\Models\\CoachInvoice' || notif.type === 'coach_invoice' || notif.type === 'coach_invoice_paid' || notif.type === 'coach_invoice_cancelled') {
            if (notif.reference_id) {
                return @json(route('runner.invoices.show', ':id')).replace(':id', notif.reference_id);
            }
            return @json(route('runner.invoices.index'));
        }
        return @json(route('notifications.index'));
    }

    function fetchNotifications(showLoading = false) {
        if (!isAuthenticated || !supportsNotif) return;

        if (showLoading && notifList) {
            notifList.innerHTML =
                '<div class="p-8 text-center">' +
                    '<div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>' +
                    '<p class="text-slate-400 text-sm">Memuat notifikasi...</p>' +
                '</div>';
        }

        fetch('{{ route("notifications.unread") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (res.status === 401 || res.status === 403) return null;
            return res.json();
        })
        .then(data => {
            if (!data) return; // Stop if no data (e.g. 401)

            if (data.count > 0) {
                if(notifBadge) {
                    notifBadge.classList.remove('hidden');
                    notifBadge.innerText = data.count > 9 ? '9+' : data.count;
                }
                if(mobileNotifBadge) {
                    mobileNotifBadge.classList.remove('hidden');
                    mobileNotifBadge.innerText = data.count > 9 ? '9+' : data.count;
                }
            } else {
                if (notifBadge) notifBadge.classList.add('hidden');
                if (mobileNotifBadge) mobileNotifBadge.classList.add('hidden');
            }

            if (notifList) {
                if (!data.notifications || data.notifications.length === 0) {
                    notifList.innerHTML =
                        '<div class="p-8 text-center">' +
                            '<div class="w-12 h-12 {{ $lightMode ? "bg-slate-100" : "bg-slate-800" }} rounded-full flex items-center justify-center mx-auto mb-3">' +
                                '<svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>' +
                                '</svg>' +
                            '</div>' +
                            '<p class="text-slate-400 text-sm">No new notifications</p>' +
                        '</div>';
                } else {
                    var html = '';
                    data.notifications.forEach(function(n) {
                        var url = getNotificationUrl(n);
                        var title = n.title || 'Notification';
                        var message = n.message || '';
                        var timeText;
                        try { timeText = dayjs(n.created_at).fromNow(); } catch (e) { timeText = n.created_at; }
                        html += '<a href="' + url + '" data-id="' + n.id + '" class="block p-4 border-b {{ $lightMode ? "border-slate-100 hover:bg-slate-50" : "border-slate-800 hover:bg-slate-800/50" }} transition-colors group">';
                        html +=   '<div class="flex gap-3">';
                        html +=     '<div class="mt-1 w-2 h-2 rounded-full bg-primary shrink-0"></div>';
                        html +=     '<div>';
                        html +=       '<p class="text-sm {{ $lightMode ? "text-slate-700" : "text-slate-200" }} group-hover:text-primary transition-colors font-bold">' + title + '</p>';
                        html +=       '<p class="text-xs text-slate-400 mt-1 line-clamp-2">' + message + '</p>';
                        html +=       '<p class="text-[10px] text-slate-500 mt-2">' + timeText + '</p>';
                        html +=     '</div>';
                        html +=   '</div>';
                        html += '</a>';
                    });
                    notifList.innerHTML = html;
                }
            }
        })
        .catch(err => console.error('Notif error:', err));
    }

    // Initial fetch and interval (only when authenticated)
    if (notifList) {
        if (isAuthenticated && supportsNotif) {
            fetchNotifications();
            setInterval(fetchNotifications, 30000);
        } else {
            if (notifBadge) notifBadge.classList.add('hidden');
            notifList.innerHTML =
                '<div class="p-8 text-center">' +
                    '<div class="w-12 h-12 {{ $lightMode ? "bg-slate-100" : "bg-slate-800" }} rounded-full flex items-center justify-center mx-auto mb-3">' +
                        '<svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>' +
                        '</svg>' +
                    '</div>' +
                    '<p class="text-slate-400 text-sm">Login untuk melihat notifikasi</p>' +
                '</div>';
        }
    }

    // Mark as read click handler
    if (notifList) {
        notifList.addEventListener('click', (e) => {
            const link = e.target.closest('a[data-id]');
            if (link) {
                e.preventDefault();
                const id = link.dataset.id;
                
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    const target = link.getAttribute('href') || '{{ route("notifications.index") }}';
                    window.location.href = target;
                });
            }
        });
    }

    // Mark all read
    const markAllBtn = document.getElementById('mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            fetch('{{ route("notifications.read-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(() => {
                fetchNotifications();
            });
        });
    }

    // Cart Count Logic
    const cartBadge = document.getElementById('nav-cart-count');
    const mobileCartBadge = document.getElementById('mobile-cart-count');

    function fetchCartCount() {
        if (!isAuthenticated || !supportsCart) return;

        fetch('{{ route("marketplace.cart.count") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (res.status === 401 || res.status === 403) return null;
            return res.json();
        })
        .then(data => {
            if (!data) return;

            if (data.count > 0) {
                if(cartBadge) {
                    cartBadge.classList.remove('hidden');
                    cartBadge.innerText = data.count > 9 ? '9+' : data.count;
                }
                if(mobileCartBadge) {
                    mobileCartBadge.classList.remove('hidden');
                    mobileCartBadge.innerText = data.count > 9 ? '9+' : data.count;
                }
            } else {
                cartBadge?.classList.add('hidden');
                mobileCartBadge?.classList.add('hidden');
            }
        })
        .catch(err => console.error('Cart error:', err));
    }

    if ((cartBadge || mobileCartBadge) && supportsCart) {
        fetchCartCount();
        window.addEventListener('focus', fetchCartCount);
    } else {
        cartBadge?.classList.add('hidden');
        mobileCartBadge?.classList.add('hidden');
    }

    // Mobile Menu Toggle
    const mobileBtn = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu-panel');
    const mobileMenuLinks = mobileMenu ? mobileMenu.querySelectorAll('a') : [];

    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close when clicking a link
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
        
        // Close on click outside
        document.addEventListener('click', (e) => {
             if (!mobileMenu.classList.contains('hidden') && !mobileMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    }

    // Global Quick Search (Lazy Reveal — 0 network work until clicked)
    const searchTriggerBtn = document.getElementById('nav-quick-search-btn');
    const searchModal = document.getElementById('nav-quick-search-modal');
    const searchInput = document.getElementById('nav-quick-search-input');
    const searchCloseBtn = document.getElementById('nav-quick-search-close');
    const searchResultsEl = document.getElementById('nav-quick-search-results');
    const searchStatusEl = document.getElementById('nav-quick-search-status');

    if (searchTriggerBtn && searchModal && searchInput && searchResultsEl) {
        const searchEndpoint = @json(route('api.quick-search'));
        const localCache = new Map();
        let searchDebounce = null;
        let activeController = null;
        let activeResultIndex = -1;

        const defaultTools = [
            { title: 'Buat Program Lari AI (Coach Ruang Lari)', subtitle: 'Susun jadwal latihan personal VDOT ke kalender', url: @json(route('programs.realistic')), badge: 'Tool' },
            { title: 'Kalkulator Pace & VDOT', subtitle: 'Hitung pace target, race predictor & zona latihan', url: @json(route('calculator')), badge: 'Tool' },
            { title: 'Kalender Pelari Studio (Cetak PDF)', subtitle: 'Desain kalender meja/dinding dengan foto lari 300 DPI', url: @json(route('kalender-pelari.landing')), badge: 'Tool' },
            { title: 'Pace Pro Strategy', subtitle: 'Rencana strategi pace per kilometer & elevasi GPX', url: @json(route('tools.pace-pro')), badge: 'Tool' },
            { title: 'Buat Rute Lari & GPX', subtitle: 'Gambar rute lari dan ekspor file GPX', url: @json(route('tools.buat-rute-lari')), badge: 'Tool' },
            { title: 'Kalender Event Lari', subtitle: 'Jadwal lomba lari nasional & komunitas', url: @json(route('calendar.public')), badge: 'Tool' }
        ];

        function escapeSearchHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderGroup(label, items) {
            if (!items || !items.length) return '';
            let html = `<div class="mb-3 last:mb-0"><div class="px-2.5 py-1 text-xs font-semibold text-slate-400">${escapeSearchHtml(label)}</div><div class="space-y-1">`;
            items.forEach((item) => {
                html += `<a href="${escapeSearchHtml(item.url)}" class="quick-search-item flex items-center justify-between gap-3 px-3 py-2.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 hover:bg-slate-800 transition-colors">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-white truncate">${escapeSearchHtml(item.title)}</div>
                        ${item.subtitle ? `<div class="text-xs text-slate-400 truncate mt-0.5">${escapeSearchHtml(item.subtitle)}</div>` : ''}
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-medium">${escapeSearchHtml(item.badge || '')}</span>
                </a>`;
            });
            html += '</div></div>';
            return html;
        }

        function renderSearchPayload(data, isDefault) {
            activeResultIndex = -1;
            const sections = [
                renderGroup(isDefault ? 'Akses Cepat Tools' : 'Fitur & Tools', data.tools || []),
                renderGroup('Event Lari', data.events || []),
                renderGroup('Program Latihan', data.programs || []),
                renderGroup('Blog & Journal', data.articles || [])
            ].join('');

            if (!sections) {
                searchResultsEl.innerHTML = `<div class="py-10 text-center text-sm text-slate-400">Tidak ada hasil untuk "<span class="text-white font-semibold">${escapeSearchHtml(searchInput.value.trim())}</span>".</div>`;
                return;
            }
            searchResultsEl.innerHTML = sections;
        }

        function openQuickSearch() {
            searchModal.classList.remove('hidden');
            searchModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if (!searchInput.value.trim()) {
                renderSearchPayload({ tools: defaultTools }, true);
                if (searchStatusEl) searchStatusEl.textContent = 'Ketik minimal 2 huruf untuk mencari Event, Program, Blog, atau Tools';
            }
            setTimeout(() => searchInput.focus(), 20);
        }

        function closeQuickSearch() {
            searchModal.classList.add('hidden');
            searchModal.classList.remove('flex');
            document.body.style.overflow = '';
            if (activeController) {
                activeController.abort();
                activeController = null;
            }
        }

        function updateKeyboardHighlight(links) {
            links.forEach((el, idx) => {
                if (idx === activeResultIndex) {
                    el.classList.add('border-lime-400', 'bg-slate-800');
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('border-lime-400', 'bg-slate-800');
                }
            });
        }

        searchTriggerBtn.addEventListener('click', openQuickSearch);
        searchCloseBtn?.addEventListener('click', closeQuickSearch);
        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) closeQuickSearch();
        });

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (searchModal.classList.contains('hidden')) openQuickSearch();
                else closeQuickSearch();
                return;
            }
            if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName) && !document.activeElement?.isContentEditable) {
                e.preventDefault();
                openQuickSearch();
                return;
            }
            if (!searchModal.classList.contains('hidden')) {
                if (e.key === 'Escape') {
                    closeQuickSearch();
                    return;
                }
                const links = Array.from(searchResultsEl.querySelectorAll('.quick-search-item'));
                if (!links.length) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeResultIndex = (activeResultIndex + 1) % links.length;
                    updateKeyboardHighlight(links);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeResultIndex = (activeResultIndex - 1 + links.length) % links.length;
                    updateKeyboardHighlight(links);
                } else if (e.key === 'Enter' && activeResultIndex >= 0 && links[activeResultIndex]) {
                    e.preventDefault();
                    window.location.href = links[activeResultIndex].getAttribute('href');
                }
            }
        });

        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim();
            clearTimeout(searchDebounce);

            if (q.length < 2) {
                if (activeController) activeController.abort();
                renderSearchPayload({ tools: defaultTools }, true);
                if (searchStatusEl) searchStatusEl.textContent = 'Ketik minimal 2 huruf untuk mencari Event, Program, Blog, atau Tools';
                return;
            }

            const cacheKey = q.toLowerCase();
            if (localCache.has(cacheKey)) {
                renderSearchPayload(localCache.get(cacheKey), false);
                if (searchStatusEl) searchStatusEl.textContent = 'Hasil instan';
                return;
            }

            if (searchStatusEl) searchStatusEl.textContent = 'Mencari...';
            searchDebounce = setTimeout(() => {
                if (activeController) activeController.abort();
                activeController = new AbortController();

                fetch(searchEndpoint + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json' },
                    signal: activeController.signal
                })
                .then((res) => res.ok ? res.json() : Promise.reject(res.status))
                .then((data) => {
                    localCache.set(cacheKey, data);
                    renderSearchPayload(data, false);
                    if (searchStatusEl) searchStatusEl.textContent = 'Gunakan panah atas/bawah & Enter untuk membuka';
                })
                .catch((err) => {
                    if (err && err.name === 'AbortError') return;
                    if (searchStatusEl) searchStatusEl.textContent = 'Gagal memuat hasil pencarian';
                });
            }, 220);
        });
    }
});
</script>

<!-- Global Quick Search Modal (Hidden & Zero Overhead Until Triggered) -->
<div id="nav-quick-search-modal"
     role="dialog"
     aria-modal="true"
     aria-label="Pencarian Cepat Ruang Lari"
     class="fixed inset-0 z-[120] hidden items-start justify-center bg-slate-950/85 px-3 pt-16 sm:pt-24">
    <div class="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-lg overflow-hidden shadow-xl">
        <div class="p-3 sm:p-4 border-b border-slate-800 flex items-center gap-2.5 bg-slate-950">
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
            </svg>
            <input
                type="search"
                id="nav-quick-search-input"
                autocomplete="off"
                placeholder="Cari fitur tools, event lari, program latihan, atau artikel..."
                class="w-full min-h-[40px] bg-transparent text-sm sm:text-base text-white placeholder-slate-400 focus:outline-none"
            >
            <button
                type="button"
                id="nav-quick-search-close"
                class="px-3 min-h-[36px] rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 hover:text-white transition cursor-pointer shrink-0"
            >
                Tutup
            </button>
        </div>

        <div id="nav-quick-search-results" class="p-3 sm:p-4 max-h-[65vh] overflow-y-auto"></div>

        <div class="px-4 py-2.5 border-t border-slate-800 bg-slate-950 flex items-center justify-between gap-2 text-xs text-slate-400">
            <span id="nav-quick-search-status">Ketik minimal 2 huruf untuk mencari Event, Program, Blog, atau Tools</span>
            <span class="hidden sm:inline font-mono text-slate-300">Ctrl+K</span>
        </div>
    </div>
</div>
@endpush
