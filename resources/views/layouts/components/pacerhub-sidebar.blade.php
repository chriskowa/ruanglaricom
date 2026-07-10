@php
    $lightMode = $lightMode ?? false;
    $inactiveClass = $lightMode ? 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' : 'text-slate-400 hover:text-white hover:bg-slate-900/60';
    $activeClass = $lightMode ? 'text-slate-900 bg-slate-100 font-semibold' : 'text-primary bg-slate-900/80 font-semibold';
    $linkBaseClass = 'group flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150';
@endphp

<aside id="ph-sidebar" class="{{ $lightMode ? 'bg-white border-slate-200 text-slate-800' : 'bg-slate-950 border-slate-900 text-slate-200' }} border-r flex flex-col h-screen">
    
    <!-- Branding Header -->
    <div class="h-20 flex items-center px-6 border-b {{ $lightMode ? 'border-slate-100' : 'border-slate-900' }} shrink-0">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-8 w-auto">
            <a href="{{ auth()->check() ? route(auth()->user()->role . '.dashboard') : route('home') }}" 
                class="text-lg font-black italic tracking-tighter flex items-center {{ $lightMode ? 'text-slate-900' : 'text-white' }}">
                RUANG<span class="pl-0.5" style="{{ $lightMode ? 'color: #000000ff;' : 'color: #ccff00;' }}">LARI</span>
            </a>
        </div>
    </div>

    <!-- Scrollable Navigation Area -->
    <nav class="flex-grow overflow-y-auto px-4 py-6 scrollbar-thin" role="navigation" aria-label="Sidebar">
        <ul class="space-y-6">
            @auth
                <!-- SECTION: MAIN -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Main</li>
                        
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.running-analysis.sessions.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.running-analysis.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-person-running"></i></span>
                                    <span>Analisis Lari</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.events.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.events.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-check"></i></span>
                                    <span>Event Management</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.event-submissions.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.event-submissions.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-check"></i></span>
                                    <span>Event Approval</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.master-gpx.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.master-gpx.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-map-marked-alt"></i></span>
                                    <span>Master GPX</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.races.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.races.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-flag-checkered"></i></span>
                                    <span>Race Master</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-users"></i></span>
                                    <span>Manage Users</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.challenge.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.challenge.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-check-circle"></i></span>
                                    <span>Approval Setoran</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.blog.articles.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.blog.articles.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-newspaper"></i></span>
                                    <span>Blog Articles</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pages.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.pages.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-file-alt"></i></span>
                                    <span>Pages</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.strava.config') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.strava.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fab fa-strava"></i></span>
                                    <span>Strava Config</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.blog.media.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.blog.media.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-images"></i></span>
                                    <span>Media Library</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.menus.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.menus.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-bars"></i></span>
                                    <span>Menu Manager</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.email-reports.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.email-reports.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-envelope-open-text"></i></span>
                                    <span>Email Report</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.email-monitoring.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.email-monitoring.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-chart-line"></i></span>
                                    <span>Email Monitoring</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.event-finance.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.reports.event-finance.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-coins"></i></span>
                                    <span>Event Finance</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.integration.settings') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.integration.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-cog"></i></span>
                                    <span>Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.vcard.settings') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.vcard.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-id-card"></i></span>
                                    <span>V-Card</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.seo.settings') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.seo.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-search"></i></span>
                                    <span>SEO Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.transactions.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.transactions.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-exchange-alt"></i></span>
                                    <span>Transactions</span>
                                </a>
                            </li>
                        @elseif(auth()->user()->isCoach())
                            <li>
                                <a href="{{ route('coach.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.dashboard') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('coach.programs.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.programs.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-list"></i></span>
                                    <span>Programs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('coach.master-workouts.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.master-workouts.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-dumbbell"></i></span>
                                    <span>Workout Library</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('coach.athletes.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.athletes.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-users"></i></span>
                                    <span>Manage Athlete</span>
                                </a>
                            </li>
                        @elseif(auth()->user()->isRunner())
                            <li>
                                <a href="{{ route('runner.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.dashboard') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('runner.calendar') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.calendar.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-alt"></i></span>
                                    <span>Calendar</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('calculator') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('calculator') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calculator"></i></span>
                                    <span>Calculator</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('runner.programs') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.programs') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-list"></i></span>
                                    <span>Programs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('challenge.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('challenge.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-trophy"></i></span>
                                    <span>Leaderboard 40days</span>
                                </a>
                            </li>
                        @elseif(auth()->user()->isEventOrganizer())
                            <li>
                                <a href="{{ route('eo.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.dashboard') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.coupons.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.coupons.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-ticket-alt"></i></span>
                                    <span>Master Kupon</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.community.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.community.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-users"></i></span>
                                    <span>Community Participants</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.email-reports.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.email-reports.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-envelope-open-text"></i></span>
                                    <span>Email Laporan</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.email-monitoring.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.email-monitoring.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-chart-line"></i></span>
                                    <span>Email Monitoring</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.email-campaigns.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.email-campaigns.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-paper-plane"></i></span>
                                    <span>Email Campaigns</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.blasts.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.blasts.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-bullhorn"></i></span>
                                    <span>Email Blasts (CSV)</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <!-- SECTION: POPUP MANAGEMENT (ADMIN ONLY) -->
                @if(auth()->user()->isAdmin())
                    <li>
                        <ul class="space-y-1">
                            <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Popup Management</li>
                            <li>
                                <a href="{{ route('admin.popups.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.popups.index') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-window-maximize"></i></span>
                                    <span>All Popups</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.popups.create') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.popups.create') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-plus-circle"></i></span>
                                    <span>Create New Popup</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.popups.scheduled') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.popups.scheduled') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clock"></i></span>
                                    <span>Scheduled Popups</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.popups.analytics') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.popups.analytics') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-chart-bar"></i></span>
                                    <span>Analytics</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.popups.settings') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.popups.settings') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-sliders-h"></i></span>
                                    <span>Settings</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- SECTION: COMMERCE -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Commerce</li>
                        
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.marketplace.categories.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.marketplace.categories.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-tags"></i></span>
                                    <span>Categories</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.marketplace.brands.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.marketplace.brands.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-copyright"></i></span>
                                    <span>Brands</span>
                                </a>
                            </li>
                        @endif

                        <li>
                            <a href="{{ route('marketplace.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-bag"></i></span>
                                    <span>Marketplace</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('marketplace.orders.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.orders.*') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-cart"></i></span>
                                <span>My Orders</span>
                            </a>
                        </li>
                        @if(auth()->user()->is_seller)
                            <li>
                                <a href="{{ route('marketplace.seller.products.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.seller.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-store"></i></span>
                                    <span>Seller Dashboard</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <!-- SECTION: COMMUNITY -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Community</li>
                        <li>
                            <a href="{{ route('feed.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('feed.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-hashtag"></i></span>
                                <span>Community</span>
                            </a>
                        </li>
                        @if(auth()->user()->role !== 'eo')
                            <li>
                                <a href="{{ route('chat.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('chat.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-comments"></i></span>
                                    <span>Messages</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('notifications.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('notifications.*') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-bell"></i></span>
                                <span>Notifications</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @else
                <!-- GUEST VIEW -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Main</li>
                        <li>
                            <a href="{{ route('home') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('home') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-home"></i></span>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('calendar.public') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('calendar.public') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-alt"></i></span>
                                <span>Calendar</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('calculator') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('calculator') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calculator"></i></span>
                                <span>Calculator</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('programs.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('programs.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-bag"></i></span>
                                <span>Marketplace</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('challenge.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('challenge.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-trophy"></i></span>
                                <span>Leaderboard 40days</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endauth
        </ul>
    </nav>

    <!-- User Profile Footer Section -->
    @auth
        <div class="p-4 border-t {{ $lightMode ? 'border-slate-100' : 'border-slate-900' }} bg-slate-50/50 dark:bg-slate-950/20 shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('profile.show') }}" class="shrink-0">
                    <img class="w-9 h-9 rounded-full object-cover border {{ $lightMode ? 'border-slate-200' : 'border-slate-800' }} hover:opacity-80 transition" 
                        src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg') }}" 
                        alt="{{ auth()->user()->name }}">
                </a>
                <div class="min-w-0 flex-1">
                    <a href="{{ route('profile.show') }}" class="block text-xs font-bold truncate {{ $lightMode ? 'text-slate-800 hover:text-slate-900' : 'text-white hover:text-primary' }} transition">
                        {{ auth()->user()->name }}
                    </a>
                    <div class="text-[9px] text-slate-500 uppercase font-semibold tracking-wider truncate">
                        {{ auth()->user()->role }}
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="p-4 border-t {{ $lightMode ? 'border-slate-100' : 'border-slate-900' }} flex flex-col gap-2 shrink-0">
            <a href="{{ route('login') }}" class="text-center text-xs font-bold py-2 rounded-lg bg-primary hover:bg-lime-400 text-dark transition">Login</a>
            <a href="{{ route('register') }}" class="text-center text-xs font-bold py-2 rounded-lg border {{ $lightMode ? 'border-slate-200 text-slate-700 hover:bg-slate-50' : 'border-slate-800 text-slate-350 hover:bg-slate-900' }} transition">Register</a>
        </div>
    @endauth

</aside>
