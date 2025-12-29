<nav class="border-b border-slate-800 backdrop-blur-md fixed w-full z-40 bg-dark/80">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between h-20">
            <!-- Left Side: Logo -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-8 w-auto">
                <a href="{{ auth()->check() ? route(auth()->user()->role . '.dashboard') : route('home') }}" class="text-2xl font-black italic tracking-tighter flex items-center">
                    RUANG<span class="text-primary">LARI</span>
                </a>
            </div>
            @guest
            <div class="flex-1 hidden md:flex items-center justify-center gap-1">
                <a href="{{ url('programs') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Programs</a>
                <a href="{{ url('coaches') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Coach</a>
                <a href="{{ url('runcalendar') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Calendar</a>
                <a href="{{ url('pacers') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Pacers</a>
                <a href="{{ url('challenge') }}" class="px-3 py-2 text-sm font-bold text-slate-300 hover:text-neon transition-colors">Challenge</a>
            </div>
            @endguest
            
            <!-- Right Side: Navigation & Actions -->
            <div class="flex items-center gap-3">
                @auth
                <button id="ph-sidebar-toggle" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors" title="Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @endauth

                <!-- Desktop Search -->
                <div class="hidden md:block">
                    <form action="{{ route('users.index') }}" method="GET">
                        <input name="q" placeholder="Search runners, clubs..." class="px-4 py-2 w-64 rounded-xl bg-slate-900/60 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                    </form>
                </div>
                
                <!-- Cart Icon -->
                @auth
                <a href="{{ route('marketplace.cart.index') }}" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors relative" title="Cart">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span id="nav-cart-count" class="absolute top-1.5 right-1 w-4 h-4 bg-neon text-dark text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                </a>
                @endauth

                <!-- Chat / Messages -->
                @auth
                <a href="{{ route('chat.index') }}" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors relative" title="Messages">
                    @include('layouts.components.svg-chat')
                </a>
                @endauth
                
                <!-- Notifications -->
                @auth
                <div class="relative" id="notification-container">
                    <button id="nav-bell-btn" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors relative" title="Notifications">
                        @include('layouts.components.svg-bell')
                        <span id="notification-badge" class="absolute top-1.5 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border border-dark hidden"></span>
                    </button>
                    <div id="nav-bell-dropdown" class="absolute right-0 mt-4 w-80 sm:w-96 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-2xl shadow-2xl hidden overflow-hidden transform transition-all origin-top-right z-50">
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
                    <button id="user-menu-btn" class="flex items-center gap-3 p-1.5 pr-3 rounded-full hover:bg-slate-800 border border-transparent hover:border-slate-700 transition-all">
                        <img class="w-8 h-8 rounded-full object-cover border border-slate-600" src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" alt="{{ auth()->user()->name }}">
                        <span class="hidden md:block text-sm font-medium text-slate-200">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-500 hidden md:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    
                    <div id="user-menu-dropdown" class="absolute right-0 mt-4 w-56 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-2xl shadow-2xl hidden transform transition-all origin-top-right z-50">
                        <div class="p-4 border-b border-slate-800">
                            <p class="text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-neon font-medium uppercase tracking-wider mt-0.5">{{ auth()->user()->role }}</p>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profile
                            </a>
                            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors">
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
                @else
                <div class="flex items-center gap-3">
                    <button id="mobile-menu-toggle" class="md:hidden p-2 rounded-lg hover:bg-slate-800 text-slate-300 transition-colors" title="Menu">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-300 hover:text-white transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-bold bg-slate-800 text-white rounded-xl hover:bg-slate-700 transition-colors">Register</a>
                </div>
                @endauth
                
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

<div id="mobile-menu-panel" class="md:hidden hidden fixed top-20 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-xl border border-slate-700 rounded-b-2xl shadow-2xl">
    <div class="p-3 grid grid-cols-1 gap-1">
        <a href="{{ url('programs') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Programs</a>
        <a href="{{ url('coaches') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Coach</a>
        <a href="{{ url('runcalendar') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Calendar</a>
        <a href="{{ url('pacers') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Pacers</a>
        <a href="{{ url('challenge') }}" class="px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-bold">Challenge</a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

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
                notifBadge.classList.remove('hidden');
                notifBadge.innerText = data.count > 9 ? '9+' : data.count;
            } else {
                notifBadge.classList.add('hidden');
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
                cartBadge.classList.remove('hidden');
                cartBadge.innerText = data.count > 9 ? '9+' : data.count;
            } else {
                cartBadge.classList.add('hidden');
            }
        })
        .catch(err => console.error('Cart error:', err));
    }

    if (cartBadge) {
        fetchCartCount();
        // Update every time page is focused or periodically
        window.addEventListener('focus', fetchCartCount);
    }

    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const mobilePanel = document.getElementById('mobile-menu-panel');
    function closeMobile(){ if(mobilePanel) mobilePanel.classList.add('hidden'); }
    function toggleMobile(){ if(!mobilePanel) return; mobilePanel.classList.toggle('hidden'); }
    if(mobileToggle){
        mobileToggle.addEventListener('click', function(e){ e.stopPropagation(); toggleMobile(); });
        document.addEventListener('click', function(e){
            if(mobilePanel && !mobilePanel.classList.contains('hidden')){
                closeMobile();
            }
        });
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape') closeMobile();
        });
    }
});
</script>
@endpush
