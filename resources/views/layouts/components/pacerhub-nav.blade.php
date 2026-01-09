<nav class="border-b border-slate-800 backdrop-blur-md fixed w-full z-40 bg-dark/80">
    <div class="max-w-7xl mx-auto p-2">
        <div class="flex items-center justify-between h-20">
            <!-- Left Side: Logo -->
            <div class="flex items-center gap-1 pl-2">
                <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-8 w-auto">
                <a href="{{ auth()->check() ? route(auth()->user()->role . '.dashboard') : route('home') }}" class="text-xl xs:text-xl font-black italic tracking-tighter flex items-center">
                    RUANG<span class="pl-1 text-primary">LARI</span>
                </a>
            </div>
       
            <div class="flex-1 hidden md:flex items-center justify-center gap-1">
                <a href="{{ route('marketplace.index') }}" class="px-3 py-2 text-sm font-bold {{ request()->routeIs('marketplace.*') ? 'text-neon' : 'text-slate-300 hover:text-neon' }} transition-colors">Marketplace</a>
                <a href="{{ route('programs.index') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Programs</a>
                <a href="{{ route('coaches.index') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Coach</a>
                <a href="{{ route('calendar.public') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Calendar</a>
                
                <!-- Pacers Dropdown -->
                <div class="relative">
                    <button id="nav-pacers-btn" class="flex items-center gap-1 px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors focus:outline-none">
                        Pacers
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="nav-pacers-dropdown" class="absolute left-0 mt-2 w-48 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-xl shadow-2xl hidden transform transition-all origin-top-left z-50">
                        <div class="p-1 space-y-1">
                            <a href="{{ route('pacer.index') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                Find Pacers
                            </a>
                            <a href="{{ route('pacer.register') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                Register Pacer
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button id="nav-challenge-btn" class="flex items-center gap-1 px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors focus:outline-none">
                        Challenge
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="nav-challenge-dropdown" class="absolute left-0 mt-2 w-48 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-xl shadow-2xl hidden transform transition-all origin-top-left z-50">
                        <div class="p-1 space-y-1">
                            <a href="{{ route('challenge.40days') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                40 Days Running Challenge
                            </a>
                            <!--<a href="{{ route('leaderboard.cyberpunk') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                Leaderboard
                            </a>-->
                            <a href="{{ route('challenge.index') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                Leaderboard 40days
                            </a>
                            <a href="{{ route('challenge.create') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                Lapor Aktivitas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
         
            
            <!-- Right Side: Navigation & Actions -->
            <div class="flex items-center gap-1">
                <button id="ph-sidebar-toggle" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors" title="Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- Desktop Search -->
                <!--<div class="hidden md:block">
                    <form action="{{ route('users.index') }}" method="GET">
                        <input name="q" placeholder="Search runners, clubs..." class="px-4 py-2 w-64 rounded-xl bg-slate-900/60 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                    </form>
                </div>-->
                
                <!-- Cart Icon -->
                @auth
                <a href="{{ route('marketplace.cart.index') }}" class="p-1 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors relative" title="Cart">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span id="nav-cart-count" class="absolute top-1.5 right-1 w-4 h-4 bg-neon text-dark text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                </a>
                @endauth

                <!-- Chat / Messages -->
                @auth
                <a href="{{ route('chat.index') }}" class="hidden md:block p-1 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors relative" title="Messages">
                    @include('layouts.components.svg-chat')
                </a>
                @endauth
                
                <!-- Notifications -->
                @auth
                <div class="relative" id="notification-container">
                    <button id="nav-bell-btn" class="p-1 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors relative" title="Notifications">
                        @include('layouts.components.svg-bell')
                        <span id="notification-badge" class="absolute top-1.5 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border border-dark hidden"></span>
                    </button>
                    <div id="nav-bell-dropdown" class="absolute right-0 -mr-16 md:mr-0 mt-4 w-80 sm:w-96 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-2xl shadow-2xl hidden overflow-hidden transform transition-all origin-top-right z-50">
                        <div class="p-4 border-b border-slate-800 flex justify-between items-center">
                            <h3 class="font-bold text-white">Notifications</h3>
                            <button id="mark-all-read" class="text-xs text-neon hover:text-white transition-colors">Mark all read</button>
                        </div>
                        <div id="notification-list" class="max-h-[400px] overflow-y-auto">
                            <div class="p-8 text-center text-slate-500 text-sm">Loading...</div>
                        </div>
                        <div class="p-3 border-t border-slate-800 bg-slate-900/50">
                            <a href="{{ route('notifications.index') }}" class="block w-full text-center py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm transition-colors">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>
                @endauth
                
                @auth
                <!-- User Profile Dropdown -->
                <div class="relative" id="user-menu-container">
                    <button id="user-menu-btn" class="flex items-center gap-1 p-1.5 pr-1 rounded-full hover:bg-slate-800 border border-transparent hover:border-slate-700 transition-all">
                        <img class="w-8 h-8 rounded-full object-cover border border-slate-600" src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ auth()->user()->name }}">
                        @php
                            $name = auth()->user()->name;
                            $initials = collect(explode(' ', $name))
                                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                ->implode('');
                        @endphp                            
                        <span class="hidden md:block text-sm font-medium text-slate-200">{{ $initials }}</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    
                    <div id="user-menu-dropdown" class="absolute right-0 mt-4 w-56 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-2xl shadow-2xl hidden transform transition-all origin-top-right z-50">
                        <div class="p-4 border-b border-slate-800">
                            <p class="text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-neon font-medium uppercase tracking-wider mt-0.5">{{ auth()->user()->role }}</p>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                Dashboard
                            </a>
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profile
                            </a>
                            <a href="{{ route('notifications.index') }}" class="flex items-center gap-1 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                Notifications
                            </a>
                            <div class="h-px bg-slate-800 my-1"></div>
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
                <div class="flex items-center gap-3 xs:gap-2">
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-300 hover:text-white transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-bold bg-neon text-dark rounded-xl hover:bg-lime-400 hover:shadow-lg hover:shadow-neon/20 transition-all transform hover:-translate-y-0.5">Register</a>
                </div>
                @endguest
                
                @auth
                <div class="hidden md:flex items-center gap-2 pl-2 border-l border-slate-800">
                    <a href="{{ route('pacer.index') }}" class="px-4 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Pacers</a>
                    <a href="{{ route('pacer.register') }}" class="px-4 py-2 text-sm font-bold bg-neon text-dark rounded-xl hover:bg-lime-400 hover:shadow-lg hover:shadow-neon/20 transition-all transform hover:-translate-y-0.5">
                        Join Pacer
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<div id="mobile-menu-panel" class="md:hidden hidden fixed top-20 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-b-2xl shadow-2xl max-h-[80vh] overflow-y-auto">
    <div class="p-3 grid grid-cols-1 gap-1">
        <a href="{{ route('programs.index') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Programs</a>
        <a href="{{ route('marketplace.index') }}" class="px-3 py-3 rounded-lg {{ request()->routeIs('marketplace.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors font-bold">Marketplace</a>
        <a href="{{ route('coaches.index') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Coach</a>
        <a href="{{ route('calendar.public') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Calendar</a>
        
        <div class="px-3 py-2">
            <div class="text-xs font-bold text-slate-500 uppercase mb-2">Pacers</div>
            <a href="{{ route('pacer.index') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Find Pacers</a>
            <a href="{{ route('pacer.register') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Register Pacer</a>
        </div>

        <div class="px-3 py-2">
            <div class="text-xs font-bold text-slate-500 uppercase mb-2">Challenge</div>
            <a href="{{ route('challenge.40days') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">40 Days Challenge</a>
            <!--<a href="{{ route('leaderboard.cyberpunk') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Leaderboard</a>-->
            <a href="{{ route('challenge.index') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Leaderboard 40days</a>
            <a href="{{ route('challenge.create') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Lapor Aktivitas</a>
        </div>

        @auth
        <div class="px-3 py-2 border-t border-slate-800 mt-2">
            <div class="flex items-center gap-3 mb-4 px-2 py-2 bg-slate-800/50 rounded-lg">
                 <img class="w-10 h-10 rounded-full object-cover border border-slate-600" src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ auth()->user()->name }}">
                 <div>
                     <div class="font-bold text-white text-sm">{{ auth()->user()->name }}</div>
                     <div class="text-xs text-neon font-medium uppercase tracking-wider">{{ auth()->user()->role }}</div>
                 </div>
            </div>

            <div class="text-xs font-bold text-slate-500 uppercase mb-2">Account</div>
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">
                Dashboard
            </a>
            <a href="{{ route('profile.show') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">
                Profile
            </a>
            <a href="{{ route('marketplace.cart.index') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">
                Cart
                <span id="mobile-cart-count" class="ml-2 bg-neon text-dark text-[10px] font-bold px-1.5 rounded-full hidden">0</span>
            </a>
            <a href="{{ route('chat.index') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">
                Messages
            </a>
            <a href="{{ route('notifications.index') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">
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
        <div class="px-3 py-2 border-t border-slate-800 mt-2">
             <div class="text-xs font-bold text-slate-500 uppercase mb-2">Account</div>
             <a href="{{ route('login') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Login</a>
             <a href="{{ route('register') }}" class="block py-2 text-slate-300 hover:text-white pl-4 border-l border-slate-700 hover:border-neon transition-colors">Register</a>
        </div>
        @endguest
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

    // Dropdown Toggles
    const toggles = [
        { btn: 'nav-bell-btn', menu: 'nav-bell-dropdown' },
        { btn: 'user-menu-btn', menu: 'user-menu-dropdown' },
        { btn: 'nav-pacers-btn', menu: 'nav-pacers-dropdown' },
        { btn: 'nav-challenge-btn', menu: 'nav-challenge-dropdown' }
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

    // Notifications Logic
    const notifBadge = document.getElementById('notification-badge');
    const mobileNotifBadge = document.getElementById('mobile-notif-count');
    const notifList = document.getElementById('notification-list');
    
    function fetchNotifications() {
        if (!isAuthenticated) return;

        fetch('{{ route("notifications.unread") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (res.status === 401) return null; // Handle unauthorized
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
                notifBadge?.classList.add('hidden');
                mobileNotifBadge?.classList.add('hidden');
            }
            
            if (notifList) {
                if (!data.notifications || data.notifications.length === 0) {
                    notifList.innerHTML = `
                        <div class="p-8 text-center">
                            <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <p class="text-slate-400 text-sm">No new notifications</p>
                        </div>`;
                } else {
                    notifList.innerHTML = data.notifications.map(n => `
                        <a href="#" data-id="${n.id}" class="block p-4 border-b border-slate-800 hover:bg-slate-800/50 transition-colors group">
                            <div class="flex gap-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-neon shrink-0"></div>
                                <div>
                                    <p class="text-sm text-slate-200 group-hover:text-white transition-colors">${n.title || 'Notification'}</p>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">${n.message}</p>
                                    <p class="text-[10px] text-slate-500 mt-2">${dayjs(n.created_at).fromNow()}</p>
                                </div>
                            </div>
                        </a>
                    `).join('');
                }
            }
        })
        .catch(err => console.error('Notif error:', err));
    }

    // Initial fetch and interval (only when authenticated)
    if (notifList) {
        if (isAuthenticated) {
            fetchNotifications();
            setInterval(fetchNotifications, 60000); // Check every minute
        } else {
            notifBadge?.classList.add('hidden');
            notifList.innerHTML = `
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <p class="text-slate-400 text-sm">Login untuk melihat notifikasi</p>
                </div>`;
        }
    }

    // Mark as read click handler
    if (notifList) {
        notifList.addEventListener('click', (e) => {
            const link = e.target.closest('a[data-id]');
            if (link) {
                e.preventDefault();
                const id = link.dataset.id;
                // Ideally redirect to the actual link, but we need the link URL. 
                // For now just mark read and maybe reload or redirect if we had the URL.
                // The API returns basic info. If we want URL, we should update Controller.
                // Assuming generic redirect for now or just mark read.
                
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    window.location.href = '{{ route("notifications.index") }}'; // Redirect to index as fallback
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
        if (!isAuthenticated) return;

        fetch('{{ route("marketplace.cart.count") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (res.status === 401) return null;
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

    if (cartBadge || mobileCartBadge) {
        fetchCartCount();
        // Update every time page is focused or periodically
        window.addEventListener('focus', fetchCartCount);
    }

    // Mobile Menu Toggle
    const mobileBtn = document.getElementById('mobile-menu-toggle') || document.getElementById('ph-sidebar-toggle');
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
});
</script>
@endpush
