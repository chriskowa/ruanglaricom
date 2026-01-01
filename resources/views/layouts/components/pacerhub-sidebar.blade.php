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
                        <li><a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Manage Users</a></li>
                    @elseif(auth()->user()->isCoach())
                        <li><a href="{{ route('coach.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                        <li><a href="{{ route('coach.programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Programs</a></li>
                        <li><a href="{{ route('coach.master-workouts.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Workout Library</a></li>
                    @elseif(auth()->user()->isRunner())
                        <li><a href="{{ route('runner.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                        <li><a href="{{ route('runner.calendar') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Calendar</a></li>
                        <li><a href="{{ route('programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Programs</a></li>
                    @elseif(auth()->user()->isEventOrganizer())
                        <li><a href="{{ route('eo.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Dashboard</a></li>
                    @endif
                    
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Commerce</li>
                    <li><a href="{{ route('programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Marketplace</a></li>
                    
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
                    <li><a href="{{ route('programs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Marketplace</a></li>
                    
                    <li class="px-3 py-2 text-xs font-mono text-slate-500 uppercase tracking-wider">Account</li>
                    <li><a href="{{ route('login') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Login</a></li>
                    <li><a href="{{ route('register') }}" class="flex items-center px-3 py-2 rounded-lg text-slate-200 hover:text-primary hover:bg-slate-800">Register</a></li>
                @endauth
            </ul>
        </nav>
    </div>
</aside>
