<aside id="ph-sidebar" class="fixed left-0 top-20 bottom-0 w-72 bg-slate-900 border-r border-slate-700 z-50 transform transition-transform duration-200 ease-out -translate-x-full">
    <div class="h-full flex flex-col">
        <div class="px-4 py-4 border-b border-slate-800">
            <div class="text-xs font-mono text-slate-400 uppercase tracking-widest">Navigation</div>
        </div>
        <nav class="flex-1 overflow-y-auto" role="navigation" aria-label="Sidebar">
            <ul class="px-2 space-y-1">
                @auth
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Main</li>
                    @if(auth()->user()->isAdmin())
                        <li><a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                        <li>
                            <a href="{{ route('admin.events.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">
                                <span class="w-5 text-slate-400"><i class="fas fa-calendar-check"></i></span>
                                <span>Event Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.event-submissions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">
                                <span class="w-5 text-slate-400"><i class="fas fa-clipboard-check"></i></span>
                                <span>Event Approval</span>
                            </a>
                        </li>
                        <li><a href="{{ route('admin.master-gpx.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Master GPX</a></li>
                        <li><a href="{{ route('admin.races.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Race Master</a></li>
                        <li><a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Manage Users</a></li>
                        <li><a href="{{ route('admin.challenge.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Approval Setoran</a></li>
                        <li><a href="{{ route('admin.blog.articles.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Blog Articles</a></li>
                        <li><a href="{{ route('admin.pages.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Pages</a></li>
                        <li><a href="{{ route('admin.strava.config') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Strava Config</a></li>
                        <li><a href="{{ route('admin.blog.media.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Media Library</a></li>
                        <li><a href="{{ route('admin.menus.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Menu Manager</a></li>
                        <li>
                            <a href="{{ route('admin.email-reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">
                                <span class="w-5 text-slate-400"><i class="fas fa-envelope-open-text"></i></span>
                                <span>Email Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.email-monitoring.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">
                                <span class="w-5 text-slate-400"><i class="fas fa-chart-line"></i></span>
                                <span>Email Monitoring</span>
                            </a>
                        </li>
                        <li><a href="{{ route('admin.integration.settings') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Settings</a></li>
                        <li><a href="{{ route('admin.vcard.settings') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">V-Card</a></li>
                        <li><a href="{{ route('admin.seo.settings') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">SEO Settings</a></li>
                        <li><a href="{{ route('admin.transactions.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Transactions</a></li>

                        <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Popup Management</li>
                        <li><a href="{{ route('admin.popups.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">All Popups</a></li>
                        <li><a href="{{ route('admin.popups.create') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Create New Popup</a></li>
                        <li><a href="{{ route('admin.popups.scheduled') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Scheduled Popups</a></li>
                        <li><a href="{{ route('admin.popups.analytics') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Analytics</a></li>
                        <li><a href="{{ route('admin.popups.settings') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Settings</a></li>
                        
                        <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Commerce Marketplace</li>
                        <li><a href="{{ route('admin.marketplace.categories.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Categories</a></li>
                        <li><a href="{{ route('admin.marketplace.brands.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Brands</a></li>
                    @elseif(auth()->user()->isCoach())
                        <li><a href="{{ route('coach.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                        <li><a href="{{ route('coach.programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Programs</a></li>
                        <li><a href="{{ route('coach.master-workouts.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Workout Library</a></li>
                    @elseif(auth()->user()->isRunner())
                        <li><a href="{{ route('runner.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                        <li><a href="{{ route('runner.calendar') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Calendar</a></li>
                        <li><a href="{{ route('calculator') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Calculator</a></li>
                        <li><a href="{{ route('programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Programs</a></li>
                        <li><a href="{{ route('challenge.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Leaderboard 40days</a></li>
                    @elseif(auth()->user()->isEventOrganizer())
                        <li><a href="{{ route('eo.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                        <li><a href="{{ route('eo.coupons.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Master Kupon</a></li>
                        <li><a href="{{ route('eo.community.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Community Participants</a></li>
                        <li>
                            <a href="{{ route('eo.email-reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">
                                <span class="w-5 text-slate-400"><i class="fas fa-envelope-open-text"></i></span>
                                <span>Email Laporan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('eo.email-monitoring.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">
                                <span class="w-5 text-slate-400"><i class="fas fa-chart-line"></i></span>
                                <span>Email Monitoring</span>
                            </a>
                        </li>
                    @endif
                    
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Commerce</li>
                    <li><a href="{{ route('marketplace.index') }}" class="flex items-center px-3 py-2 rounded-lg {{ request()->routeIs('marketplace.index') ? 'text-primary bg-slate-800' : 'text-slate-200 hover:text-primary hover:bg-slate-800' }}">Marketplace</a></li>
                    <li><a href="{{ route('marketplace.orders.index') }}" class="flex items-center px-3 py-2 rounded-lg {{ request()->routeIs('marketplace.orders.*') ? 'text-primary bg-slate-800' : 'text-slate-200 hover:text-primary hover:bg-slate-800' }}">My Orders</a></li>
                    @if(auth()->user()->is_seller)
                        <li><a href="{{ route('marketplace.seller.products.index') }}" class="flex items-center px-3 py-2 rounded-lg {{ request()->routeIs('marketplace.seller.*') ? 'text-primary bg-slate-800' : 'text-slate-200 hover:text-primary hover:bg-slate-800' }}">Seller Dashboard</a></li>
                    @endif
                    
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Community</li>
                    <li><a href="{{ route('feed.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Community</a></li>
                    <li><a href="{{ route('chat.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Messages</a></li>
                    <li><a href="{{ route('notifications.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Notifications</a></li>
                    
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Account</li>
                    <li><a href="{{ route('profile.show') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Profile</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="px-3 py-2">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-red-400 hover:bg-slate-800">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Main</li>
                    <li><a href="{{ route('home') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Home</a></li>
                    <li><a href="{{ route('calendar.public') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Calendar</a></li>
                    <li><a href="{{ route('calculator') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Calculator</a></li>
                    <li><a href="{{ route('programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Marketplace</a></li>
                    <li><a href="{{ route('challenge.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Leaderboard 40days</a></li>
                    
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Account</li>
                    <li><a href="{{ route('login') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Login</a></li>
                    <li><a href="{{ route('register') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Register</a></li>
                @endauth
            </ul>
        </nav>
    </div>
</aside>
