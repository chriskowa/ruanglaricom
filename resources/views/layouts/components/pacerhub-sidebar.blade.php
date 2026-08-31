@php
    $lightMode = $lightMode ?? false;
    $inactiveClass = $lightMode ? 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' : 'text-slate-300 hover:text-white hover:bg-slate-900';
    $activeClass = $lightMode ? 'text-slate-900 bg-slate-100 font-semibold' : 'text-white bg-slate-900 border border-slate-800 font-semibold';
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
                        <li class="px-3 mb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Main</li>
                        
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.running-analysis.sessions.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.running-analysis.sessions.*', 'admin.running-analysis.trials.*', 'admin.running-analysis.capture', 'admin.running-analysis.upload-video.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-person-running"></i></span>
                                    <span>Analisis Lari</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.running-analysis.requests.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.running-analysis.requests.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-list"></i></span>
                                    <span>Permintaan Analisis</span>
                                    @php
                                        $pendingReqCount = \App\Models\RunningAnalysis\AnalysisRequest::where('status', 'pending')->count();
                                    @endphp
                                    @if ($pendingReqCount > 0)
                                        <span class="ml-auto text-[10px] font-black bg-neon text-[#121212] rounded-full px-1.5 py-0.5">{{ $pendingReqCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.events.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.events.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-check"></i></span>
                                    <span>Event Management</span>
                                </a>
                            </li>

                            <!-- SECTION: MARKETPLACE MANAGEMENT -->
                            <li class="pt-2 pb-1">
                                <div class="px-3 mb-1.5 text-[10px] font-extrabold text-neon uppercase tracking-widest flex items-center justify-between">
                                    <span>Marketplace Admin</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-neon animate-pulse"></span>
                                </div>
                            </li>
                            <li>
                                <a href="{{ route('admin.marketplace.orders.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.marketplace.orders.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-bag"></i></span>
                                    <span>Orders & Dispute</span>
                                    @php
                                        $disputedCount = \App\Models\Marketplace\MarketplaceOrder::where('status', 'disputed')->count();
                                    @endphp
                                    @if($disputedCount > 0)
                                        <span class="ml-auto text-[10px] font-black bg-rose-500 text-white rounded-full px-1.5 py-0.5 animate-pulse">{{ $disputedCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.transactions.index', ['tab' => 'withdrawals']) }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.transactions.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-wallet"></i></span>
                                    <span>Penarikan Dana (WD)</span>
                                    @php
                                        $pendingWdCount = \App\Models\WalletWithdrawal::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingWdCount > 0)
                                        <span class="ml-auto text-[10px] font-black bg-neon text-dark rounded-full px-1.5 py-0.5">{{ $pendingWdCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.marketplace.products.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.marketplace.products.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-boxes"></i></span>
                                    <span>Moderasi Produk</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.programs.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.programs.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-list-check"></i></span>
                                    <span>Program Management</span>
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
                                    @php
                                        $pendingGpxSugCount = \App\Models\GpxTitleSuggestion::pending()->count();
                                    @endphp
                                    @if ($pendingGpxSugCount > 0)
                                        <span class="ml-auto text-[10px] font-black bg-neon text-[#121212] rounded-full px-1.5 py-0.5" title="{{ $pendingGpxSugCount }} saran penamaan rute baru">{{ $pendingGpxSugCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.strength-exercises.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.strength-exercises.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-dumbbell"></i></span>
                                    <span>Strength Exercises</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.races.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.races.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-flag-checkered"></i></span>
                                    <span>Race Master</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.race-sessions.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.race-sessions.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-stopwatch"></i></span>
                                    <span>Sesi Balap (Sessions)</span>
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
                                <a href="{{ route('admin.whatsapp-logs.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.whatsapp-logs.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fab fa-whatsapp"></i></span>
                                    <span>WhatsApp Logs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.integration.settings') }}?tab=whatsapp" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.integration.*') && request('tab') === 'whatsapp' ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-server"></i></span>
                                    <span>WhatsApp Gateway</span>
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
                            <li>
                                <a href="{{ route('coach.finance.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.finance.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-wallet"></i></span>
                                    <span>Keuangan & Saldo</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('coach.invoices.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.invoices.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <span>Tagihan Atlet</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('coach.withdrawals.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('coach.withdrawals.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-money-bill-transfer"></i></span>
                                    <span>Penarikan Dana</span>
                                </a>
                            </li>
                        @elseif(auth()->user()->isRunner())
                            @php
                                $isTrainingActive = request()->routeIs('runner.dashboard', 'runner.calendar*', 'runner.programs*', 'calculator');
                                $isActivityActive = request()->routeIs('activities.*', 'runner.analysis-requests.*', 'runner.gpx.*', 'runner.strava.*') || request()->is('calendar*');
                            @endphp

                            <!-- GRUP: LATIHAN & PERFORMA -->
                            <li class="space-y-1" x-data="{ open: {{ $isTrainingActive ? 'true' : 'false' }} }">
                                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-bold text-white uppercase tracking-wider hover:bg-slate-900 rounded-lg transition-colors group">
                                    <span class="flex items-center gap-2.5">
                                        <i class="fas fa-person-running text-xs text-white"></i>
                                        <span>Latihan & Performa</span>
                                    </span>
                                    <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <ul x-show="open" x-transition.opacity.duration.150ms class="space-y-1 pl-2 border-l border-slate-800 ml-3.5 my-1">
                                    <li>
                                        <a href="{{ route('runner.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.dashboard') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-th-large"></i></span>
                                            <span class="text-white">Dashboard</span>
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" onclick="openGlobalPbModal()" class="{{ $linkBaseClass }} {{ $inactiveClass }} w-full text-left">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-stopwatch"></i></span>
                                            <span class="flex items-center justify-between w-full">
                                                <span class="text-white">Update PB</span>
                                                <span class="text-[9px] bg-slate-800 text-white border border-slate-700 px-1.5 py-0.5 rounded-full font-bold">Rekor</span>
                                            </span>
                                        </button>
                                    </li>
                                    <li>
                                        <a href="{{ route('runner.calendar') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.calendar.*') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-calendar-alt"></i></span>
                                            <span class="text-white">Kalender Latihan</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('runner.programs') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.programs*') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-clipboard-list"></i></span>
                                            <span class="text-white">Program Latihan</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('calculator') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('calculator') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-calculator"></i></span>
                                            <span class="text-white">Kalkulator Pace</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- GRUP: AKTIVITAS & RUTE -->
                            <li class="space-y-1 pt-1" x-data="{ open: {{ $isActivityActive ? 'true' : 'false' }} }">
                                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-bold text-white uppercase tracking-wider hover:bg-slate-900 rounded-lg transition-colors group">
                                    <span class="flex items-center gap-2.5">
                                        <i class="fas fa-route text-xs text-white"></i>
                                        <span>Aktivitas & Rute</span>
                                    </span>
                                    <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <ul x-show="open" x-transition.opacity.duration.150ms class="space-y-1 pl-2 border-l border-slate-800 ml-3.5 my-1">
                                    <li>
                                        <a href="{{ route('activities.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('activities.*') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-running"></i></span>
                                            <span class="text-white">Aktivitas Lari</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('calendar.public') }}#strava" class="{{ $linkBaseClass }} {{ request()->is('calendar*') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fab fa-strava"></i></span>
                                            <span class="flex items-center justify-between w-full">
                                                <span class="text-white">Strava Activity</span>
                                                @if(auth()->user()->strava_access_token)
                                                    <span class="text-[9px] bg-slate-800 text-white border border-slate-700 px-1.5 py-0.5 rounded-full font-bold">Sync</span>
                                                @endif
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        @if(auth()->user()->strava_access_token)
                                            <a href="{{ route('runner.strava.disconnect') }}" onclick="return confirm('Apakah Anda yakin ingin melepaskan koneksi (Unauthorize) Strava?');" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.strava.*') ? $activeClass : $inactiveClass }}" title="Klik untuk melepaskan otorisasi Strava">
                                                <span class="w-5 text-center text-xs text-white"><i class="fab fa-strava"></i></span>
                                                <span class="flex items-center justify-between w-full">
                                                    <span class="text-white">Strava Connected</span>
                                                    <span class="text-[9px] bg-slate-800 text-white border border-slate-700 px-1.5 py-0.5 rounded-full font-bold group-hover:bg-rose-900 group-hover:border-rose-700 transition">Disconnect</span>
                                                </span>
                                            </a>
                                        @else
                                            <a href="{{ route('runner.strava.connect') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.strava.*') ? $activeClass : $inactiveClass }}">
                                                <span class="w-5 text-center text-xs text-white"><i class="fab fa-strava"></i></span>
                                                <span class="flex items-center justify-between w-full">
                                                    <span class="text-white">Koneksi Strava</span>
                                                    <span class="text-[9px] bg-slate-800 text-white border border-slate-700 px-1.5 py-0.5 rounded-full font-bold">Connect</span>
                                                </span>
                                            </a>
                                        @endif
                                    </li>
                                    <li>
                                        <a href="{{ route('runner.analysis-requests.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.analysis-requests.*') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-chart-line"></i></span>
                                            <span class="text-white">Analisis Lari</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('runner.gpx.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('runner.gpx.*') ? $activeClass : $inactiveClass }}">
                                            <span class="w-5 text-center text-xs text-white"><i class="fas fa-map-marked-alt"></i></span>
                                            <span class="text-white">My GPX</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @elseif(auth()->user()->isEventOrganizer())
                            <li>
                                <a href="{{ route('eo.dashboard') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.dashboard') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('eo.events.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('eo.events.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-alt"></i></span>
                                    <span>Manage Event</span>
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
                            <li class="px-3 mb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Popup Management</li>
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

                <!-- SECTION: FINANCE -->
                @php
                    $isFinanceActive = request()->routeIs('wallet.*');
                @endphp
                <li class="space-y-1 pt-1" x-data="{ open: {{ $isFinanceActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-bold text-white uppercase tracking-wider hover:bg-slate-900 rounded-lg transition-colors group">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-wallet text-xs text-white"></i>
                            <span>Keuangan</span>
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.150ms class="space-y-1 pl-2 border-l border-slate-800 ml-3.5 my-1">
                        <li>
                            <a href="{{ route('wallet.index', ['action' => 'deposit']) }}" class="{{ $linkBaseClass }} {{ request()->routeIs('wallet.*') && request('action') === 'deposit' ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-arrow-down"></i></span>
                                <span class="text-white">Deposit</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('wallet.index', ['action' => 'withdraw']) }}#withdraw-form" class="{{ $linkBaseClass }} {{ request()->routeIs('wallet.*') && request('action') === 'withdraw' ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-arrow-up"></i></span>
                                <span class="text-white">Withdraw</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- SECTION: COMMERCE -->
                @php
                    $isCommerceActive = request()->routeIs('marketplace.*', 'admin.marketplace.*');
                @endphp
                <li class="space-y-1 pt-1" x-data="{ open: {{ $isCommerceActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-bold text-white uppercase tracking-wider hover:bg-slate-900 rounded-lg transition-colors group">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-shopping-bag text-xs text-white"></i>
                            <span>Marketplace</span>
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.150ms class="space-y-1 pl-2 border-l border-slate-800 ml-3.5 my-1">
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.marketplace.categories.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.marketplace.categories.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs text-white"><i class="fas fa-tags"></i></span>
                                    <span class="text-white">Categories</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.marketplace.brands.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('admin.marketplace.brands.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs text-white"><i class="fas fa-copyright"></i></span>
                                    <span class="text-white">Brands</span>
                                </a>
                            </li>
                        @endif

                        <li>
                            <a href="{{ route('marketplace.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-store"></i></span>
                                <span class="text-white">Katalog Produk</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('marketplace.orders.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.orders.*') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-shopping-cart"></i></span>
                                <span class="text-white">Pesanan Saya</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('marketplace.wishlist.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.wishlist.*') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-heart"></i></span>
                                <span class="text-white">Wishlist Saya</span>
                            </a>
                        </li>
                        @if(auth()->user()->is_seller)
                            <li>
                                <a href="{{ route('marketplace.seller.products.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('marketplace.seller.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs text-white"><i class="fas fa-store-alt"></i></span>
                                    <span class="text-white">Seller Dashboard</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <!-- SECTION: COMMUNITY -->
                @php
                    $isCommunityActive = request()->routeIs('feed.*', 'chat.*', 'notifications.*');
                @endphp
                <li class="space-y-1 pt-1" x-data="{ open: {{ $isCommunityActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-bold text-white uppercase tracking-wider hover:bg-slate-900 rounded-lg transition-colors group">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-users text-xs text-white"></i>
                            <span>Komunitas</span>
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <ul x-show="open" x-transition.opacity.duration.150ms class="space-y-1 pl-2 border-l border-slate-800 ml-3.5 my-1">
                        <li>
                            <a href="{{ route('feed.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('feed.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-hashtag"></i></span>
                                <span class="text-white">Feed Komunitas</span>
                            </a>
                        </li>
                        @if(auth()->user()->role !== 'eo')
                            <li>
                                <a href="{{ route('chat.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('chat.*') ? $activeClass : $inactiveClass }}">
                                    <span class="w-5 text-center text-xs text-white"><i class="fas fa-comments"></i></span>
                                    <span class="text-white">Pesan & Chat</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('notifications.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('notifications.*') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs text-white"><i class="fas fa-bell"></i></span>
                                <span class="text-white">Notifikasi</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @else
                <!-- GUEST VIEW -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Main</li>
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
                            <a href="{{ route('gpx.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('gpx.*') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-map-marked-alt"></i></span>
                                <span>Database GPX</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('programs.index') }}" class="{{ $linkBaseClass }} {{ request()->routeIs('programs.index') ? $activeClass : $inactiveClass }}">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-bag"></i></span>
                                <span>Marketplace</span>
                            </a>
                        </li>
                        
                    </ul>
                </li>
            @endauth
        </ul>
    </nav>

    <!-- User Profile Footer Section -->
    @auth
        <div class="p-4 border-t {{ $lightMode ? 'border-slate-100 bg-slate-50/80' : 'border-slate-900 bg-slate-900/50' }} shrink-0">
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
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 {{ $lightMode ? 'hover:bg-slate-200/60' : 'hover:bg-slate-800' }} transition-colors" title="Logout">
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

@include('layouts.partials.update-pb-modal')
