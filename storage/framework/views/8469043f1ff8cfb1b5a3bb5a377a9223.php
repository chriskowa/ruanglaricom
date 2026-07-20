<?php
    $lightMode = $lightMode ?? false;
    $inactiveClass = $lightMode ? 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' : 'text-slate-400 hover:text-white hover:bg-slate-900/60';
    $activeClass = $lightMode ? 'text-slate-900 bg-slate-100 font-semibold' : 'text-primary bg-slate-900/80 font-semibold';
    $linkBaseClass = 'group flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150';
?>

<aside id="ph-sidebar" class="<?php echo e($lightMode ? 'bg-white border-slate-200 text-slate-800' : 'bg-slate-950 border-slate-900 text-slate-200'); ?> border-r flex flex-col h-screen">
    
    <!-- Branding Header -->
    <div class="h-20 flex items-center px-6 border-b <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-900'); ?> shrink-0">
        <div class="flex items-center gap-2">
            <img src="<?php echo e(asset('images/logo saja ruang lari.png')); ?>" alt="RuangLari" class="h-8 w-auto">
            <a href="<?php echo e(auth()->check() ? route(auth()->user()->role . '.dashboard') : route('home')); ?>" 
                class="text-lg font-black italic tracking-tighter flex items-center <?php echo e($lightMode ? 'text-slate-900' : 'text-white'); ?>">
                RUANG<span class="pl-0.5" style="<?php echo e($lightMode ? 'color: #000000ff;' : 'color: #ccff00;'); ?>">LARI</span>
            </a>
        </div>
    </div>

    <!-- Scrollable Navigation Area -->
    <nav class="flex-grow overflow-y-auto px-4 py-6 scrollbar-thin" role="navigation" aria-label="Sidebar">
        <ul class="space-y-6">
            <?php if(auth()->guard()->check()): ?>
                <!-- SECTION: MAIN -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Main</li>
                        
                        <?php if(auth()->user()->isAdmin()): ?>
                            <li>
                                <a href="<?php echo e(route('admin.dashboard')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.running-analysis.sessions.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.running-analysis.sessions.*', 'admin.running-analysis.trials.*', 'admin.running-analysis.capture', 'admin.running-analysis.upload-video.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-person-running"></i></span>
                                    <span>Analisis Lari</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.running-analysis.requests.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.running-analysis.requests.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-list"></i></span>
                                    <span>Permintaan Analisis</span>
                                    <?php
                                        $pendingReqCount = \App\Models\RunningAnalysis\AnalysisRequest::where('status', 'pending')->count();
                                    ?>
                                    <?php if($pendingReqCount > 0): ?>
                                        <span class="ml-auto text-[10px] font-black bg-neon text-[#121212] rounded-full px-1.5 py-0.5"><?php echo e($pendingReqCount); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.events.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.events.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-check"></i></span>
                                    <span>Event Management</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.event-submissions.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.event-submissions.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-check"></i></span>
                                    <span>Event Approval</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.master-gpx.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.master-gpx.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-map-marked-alt"></i></span>
                                    <span>Master GPX</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.races.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.races.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-flag-checkered"></i></span>
                                    <span>Race Master</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.users.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-users"></i></span>
                                    <span>Manage Users</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.challenge.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.challenge.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-check-circle"></i></span>
                                    <span>Approval Setoran</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.blog.articles.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.blog.articles.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-newspaper"></i></span>
                                    <span>Blog Articles</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.pages.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.pages.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-file-alt"></i></span>
                                    <span>Pages</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.strava.config')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.strava.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fab fa-strava"></i></span>
                                    <span>Strava Config</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.blog.media.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.blog.media.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-images"></i></span>
                                    <span>Media Library</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.menus.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.menus.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-bars"></i></span>
                                    <span>Menu Manager</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.email-reports.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.email-reports.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-envelope-open-text"></i></span>
                                    <span>Email Report</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.email-monitoring.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.email-monitoring.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-chart-line"></i></span>
                                    <span>Email Monitoring</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.whatsapp-logs.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.whatsapp-logs.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fab fa-whatsapp"></i></span>
                                    <span>WhatsApp Logs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.reports.event-finance.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.reports.event-finance.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-coins"></i></span>
                                    <span>Event Finance</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.integration.settings')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.integration.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-cog"></i></span>
                                    <span>Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.vcard.settings')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.vcard.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-id-card"></i></span>
                                    <span>V-Card</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.seo.settings')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.seo.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-search"></i></span>
                                    <span>SEO Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.transactions.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.transactions.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-exchange-alt"></i></span>
                                    <span>Transactions</span>
                                </a>
                            </li>
                        <?php elseif(auth()->user()->isCoach()): ?>
                            <li>
                                <a href="<?php echo e(route('coach.dashboard')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('coach.dashboard') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('coach.programs.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('coach.programs.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-list"></i></span>
                                    <span>Programs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('coach.master-workouts.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('coach.master-workouts.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-dumbbell"></i></span>
                                    <span>Workout Library</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('coach.athletes.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('coach.athletes.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-users"></i></span>
                                    <span>Manage Athlete</span>
                                </a>
                            </li>
                        <?php elseif(auth()->user()->isRunner()): ?>
                            <li>
                                <a href="<?php echo e(route('runner.dashboard')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('runner.dashboard') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('runner.calendar')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('runner.calendar.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-alt"></i></span>
                                    <span>Calendar</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('calculator')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('calculator') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calculator"></i></span>
                                    <span>Calculator</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('runner.programs')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('runner.programs') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clipboard-list"></i></span>
                                    <span>Programs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('challenge.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('challenge.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-trophy"></i></span>
                                    <span>Leaderboard 40days</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('runner.analysis-requests.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('runner.analysis-requests.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-person-running"></i></span>
                                    <span>Analisis Lari</span>
                                </a>
                            </li>
                        <?php elseif(auth()->user()->isEventOrganizer()): ?>
                            <li>
                                <a href="<?php echo e(route('eo.dashboard')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.dashboard') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-th-large"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('eo.coupons.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.coupons.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-ticket-alt"></i></span>
                                    <span>Master Kupon</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('eo.community.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.community.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-users"></i></span>
                                    <span>Community Participants</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('eo.email-reports.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.email-reports.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-envelope-open-text"></i></span>
                                    <span>Email Laporan</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('eo.email-monitoring.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.email-monitoring.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-chart-line"></i></span>
                                    <span>Email Monitoring</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('eo.email-campaigns.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.email-campaigns.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-paper-plane"></i></span>
                                    <span>Email Campaigns</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('eo.blasts.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('eo.blasts.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-bullhorn"></i></span>
                                    <span>Email Blasts (CSV)</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- SECTION: POPUP MANAGEMENT (ADMIN ONLY) -->
                <?php if(auth()->user()->isAdmin()): ?>
                    <li>
                        <ul class="space-y-1">
                            <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Popup Management</li>
                            <li>
                                <a href="<?php echo e(route('admin.popups.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.popups.index') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-window-maximize"></i></span>
                                    <span>All Popups</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.popups.create')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.popups.create') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-plus-circle"></i></span>
                                    <span>Create New Popup</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.popups.scheduled')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.popups.scheduled') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-clock"></i></span>
                                    <span>Scheduled Popups</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.popups.analytics')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.popups.analytics') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-chart-bar"></i></span>
                                    <span>Analytics</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.popups.settings')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.popups.settings') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-sliders-h"></i></span>
                                    <span>Settings</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- SECTION: COMMERCE -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Commerce</li>
                        
                        <?php if(auth()->user()->isAdmin()): ?>
                            <li>
                                <a href="<?php echo e(route('admin.marketplace.categories.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.marketplace.categories.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-tags"></i></span>
                                    <span>Categories</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.marketplace.brands.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('admin.marketplace.brands.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-copyright"></i></span>
                                    <span>Brands</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li>
                            <a href="<?php echo e(route('marketplace.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('marketplace.index') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-bag"></i></span>
                                    <span>Marketplace</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('marketplace.orders.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('marketplace.orders.*') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-cart"></i></span>
                                <span>My Orders</span>
                            </a>
                        </li>
                        <?php if(auth()->user()->is_seller): ?>
                            <li>
                                <a href="<?php echo e(route('marketplace.seller.products.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('marketplace.seller.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-store"></i></span>
                                    <span>Seller Dashboard</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- SECTION: COMMUNITY -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Community</li>
                        <li>
                            <a href="<?php echo e(route('feed.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('feed.index') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-hashtag"></i></span>
                                <span>Community</span>
                            </a>
                        </li>
                        <?php if(auth()->user()->role !== 'eo'): ?>
                            <li>
                                <a href="<?php echo e(route('chat.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('chat.*') ? $activeClass : $inactiveClass); ?>">
                                    <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-comments"></i></span>
                                    <span>Messages</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?php echo e(route('notifications.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('notifications.*') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-bell"></i></span>
                                <span>Notifications</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php else: ?>
                <!-- GUEST VIEW -->
                <li>
                    <ul class="space-y-1">
                        <li class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Main</li>
                        <li>
                            <a href="<?php echo e(route('home')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('home') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-home"></i></span>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('calendar.public')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('calendar.public') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calendar-alt"></i></span>
                                <span>Calendar</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('calculator')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('calculator') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-calculator"></i></span>
                                <span>Calculator</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('programs.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('programs.index') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-shopping-bag"></i></span>
                                <span>Marketplace</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('challenge.index')); ?>" class="<?php echo e($linkBaseClass); ?> <?php echo e(request()->routeIs('challenge.index') ? $activeClass : $inactiveClass); ?>">
                                <span class="w-5 text-center text-xs group-hover:scale-105 transition-transform"><i class="fas fa-trophy"></i></span>
                                <span>Leaderboard 40days</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- User Profile Footer Section -->
    <?php if(auth()->guard()->check()): ?>
        <div class="p-4 border-t <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-900'); ?> bg-slate-50/50 dark:bg-slate-950/20 shrink-0">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('profile.show')); ?>" class="shrink-0">
                    <img class="w-9 h-9 rounded-full object-cover border <?php echo e($lightMode ? 'border-slate-200' : 'border-slate-800'); ?> hover:opacity-80 transition" 
                        src="<?php echo e(auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg')); ?>" 
                        alt="<?php echo e(auth()->user()->name); ?>">
                </a>
                <div class="min-w-0 flex-1">
                    <a href="<?php echo e(route('profile.show')); ?>" class="block text-xs font-bold truncate <?php echo e($lightMode ? 'text-slate-800 hover:text-slate-900' : 'text-white hover:text-primary'); ?> transition">
                        <?php echo e(auth()->user()->name); ?>

                    </a>
                    <div class="text-[9px] text-slate-500 uppercase font-semibold tracking-wider truncate">
                        <?php echo e(auth()->user()->role); ?>

                    </div>
                </div>
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="shrink-0">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="p-4 border-t <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-900'); ?> flex flex-col gap-2 shrink-0">
            <a href="<?php echo e(route('login')); ?>" class="text-center text-xs font-bold py-2 rounded-lg bg-primary hover:bg-lime-400 text-dark transition">Login</a>
            <a href="<?php echo e(route('register')); ?>" class="text-center text-xs font-bold py-2 rounded-lg border <?php echo e($lightMode ? 'border-slate-200 text-slate-700 hover:bg-slate-50' : 'border-slate-800 text-slate-350 hover:bg-slate-900'); ?> transition">Register</a>
        </div>
    <?php endif; ?>

</aside>
<?php /**PATH C:\laragon\www\ruanglari\resources\views/layouts/components/pacerhub-sidebar.blade.php ENDPATH**/ ?>