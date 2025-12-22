        <nav class="border-b border-slate-800 backdrop-blur-md fixed w-full z-40 bg-dark/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-8 w-auto">
                        <a href="{{ route('home') }}" class="text-2xl font-black italic tracking-tighter flex items-center">
                            RUANG<span class="text-primary">LARI</span>
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button id="ph-sidebar-toggle" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300" title="Menu">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="hidden md:block">
                            <form action="{{ route('users.index') }}" method="GET">
                                <input name="q" placeholder="Search…" class="px-3 py-2 w-64 rounded-lg bg-slate-900/60 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </form>
                        </div>
                        
                        <a href="{{ route('chat.index') }}" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300" title="Messages">
                            @include('layouts.components.svg-chat')
                        </a>
                        
                        <div class="relative">
                            <button id="nav-bell-btn" class="p-2 rounded-lg hover:bg-slate-800 text-slate-300" title="Notifications">
                                @include('layouts.components.svg-bell')
                            </button>
                            <div id="nav-bell-dropdown" class="absolute right-0 mt-2 w-80 bg-slate-900 border border-slate-700 rounded-xl shadow-xl hidden">
                                @include('layouts.components.notification-dropdown')
                            </div>
                        </div>
                        
                        @auth
                        <div class="relative">
                            <button class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-800">
                                <img class="w-8 h-8 rounded-full object-cover" src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" alt="{{ auth()->user()->name }}">
                                <span class="hidden md:block text-sm text-slate-200">{{ auth()->user()->name }}</span>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-slate-900 border border-slate-700 rounded-xl shadow-xl hidden">
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Profile</a>
                                <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Notifications</a>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-800">Logout</button>
                                </form>
                            </div>
                        </div>
                        @endauth
                        
                        <div class="hidden md:flex items-center gap-2">
                            <a href="{{ route('pacer.index') }}" class="px-3 py-2 text-sm font-medium hover:text-primary transition">Pacers</a>
                            <a href="{{ route('pacer.register') }}" class="px-3 py-2 text-sm font-bold text-primary border border-primary rounded hover:bg-primary hover:text-dark transition">Daftar Pacer</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
