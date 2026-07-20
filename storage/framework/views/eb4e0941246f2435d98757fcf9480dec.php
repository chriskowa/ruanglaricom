<?php $lightMode = $lightMode ?? false; ?>
<style>[x-cloak]{display:none !important;}</style>
<nav id="pacerhub-nav" class="border-b <?php echo e($lightMode ? 'border-slate-200 bg-white/90' : 'border-slate-800 bg-dark/80'); ?> backdrop-blur-md fixed w-full z-40">
    <?php
        $headerMenu = \App\Models\Menu::where('location', 'header')
            ->with(['items' => function($q) {
                $q->whereNull('parent_id')
                  ->with('children')
                  ->orderBy('order');
            }])
            ->first();
    ?>
    <div class="max-w-7xl mx-auto p-2">
        <div class="flex items-center justify-between h-20">
            <!-- Left Side: Logo & Sidebar Toggle -->
            <div class="flex items-center gap-2 pl-2">
                <?php if(isset($isDashboard) && $isDashboard): ?>
                <button id="ph-sidebar-toggle" class="p-2 rounded-lg <?php echo e($lightMode ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-slate-800 text-slate-300'); ?> transition-colors" title="Toggle Sidebar">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <?php endif; ?>
                <div class="flex items-center gap-1 nav-logo">
                    <img src="<?php echo e(asset('images/logo saja ruang lari.png')); ?>" alt="RuangLari" class="h-6 sm:h-8 w-auto">
                    <a href="<?php echo e(auth()->check() ? route(auth()->user()->role . '.dashboard') : route('home')); ?>" 
                        class="text-sm sm:text-lg md:text-xl font-black italic tracking-tighter flex items-center <?php echo e($lightMode ? 'text-slate-900' : 'text-white'); ?>">
                        RUANG<span class="pl-1" style="<?php echo e($lightMode ? 'color: #000000ff;' : 'color: #ccff00;'); ?>">LARI</span>
                    </a>
                </div>
            </div>
       
            <div class="flex-1 hidden md:flex items-center justify-center gap-1">
                <?php if($headerMenu): ?>
                    <?php $__currentLoopData = $headerMenu->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($item->children->count() > 0): ?>
                            <!-- Dropdown for <?php echo e($item->title); ?> -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @keydown.escape.window="open = false" @click.outside="open = false">
                                <button id="nav-<?php echo e(Str::slug($item->title)); ?>-btn" type="button" class="flex items-center gap-1 px-3 py-2 text-sm font-bold <?php echo e($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon'); ?> transition-colors focus:outline-none" :class="{ '<?php echo e($lightMode ? 'text-slate-900' : 'text-neon'); ?>': open }" @click="open = !open">
                                    <?php echo e($item->title); ?>

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
                                     class="absolute left-0 mt-2 w-48 <?php echo e($lightMode ? 'bg-white border-slate-200' : 'bg-slate-900/95 border-slate-700'); ?> backdrop-blur-xl border rounded-xl shadow-2xl origin-top-left z-50">
                                    <div class="p-1 space-y-1">
                                        <?php $__currentLoopData = $item->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(url($child->url)); ?>" target="<?php echo e($child->target); ?>" class="block px-4 py-2 text-sm <?php echo e($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> rounded-lg transition-colors">
                                                <?php echo e($child->title); ?>

                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Single Link <?php echo e($item->title); ?> -->
                            <a href="<?php echo e(url($item->url)); ?>" target="<?php echo e($item->target); ?>" class="px-3 py-2 text-sm font-bold <?php echo e(request()->is(trim($item->url, '/') . '*') ? ($lightMode ? 'text-slate-900 border-b-2 border-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon')); ?> transition-colors">
                                <?php echo e($item->title); ?>

                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <!-- Fallback if no menu found -->
                    <a href="<?php echo e(route('marketplace.index')); ?>" class="px-3 py-2 text-sm font-bold <?php echo e(request()->routeIs('marketplace.*') ? ($lightMode ? 'text-slate-900' : 'text-neon') : ($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon')); ?> transition-colors">Marketplace</a>
                    <a href="<?php echo e(route('programs.index')); ?>" class="px-3 py-2 text-sm font-bold <?php echo e($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon'); ?> transition-colors">Programs</a>
                    <a href="<?php echo e(route('coaches.index')); ?>" class="px-3 py-2 text-sm font-bold <?php echo e($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon'); ?> transition-colors">Coach</a>
                    <a href="<?php echo e(route('calendar.public')); ?>" class="px-3 py-2 text-sm font-bold <?php echo e($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon'); ?> transition-colors">Calendar</a>
                <?php endif; ?>
            </div>
         
            
            <!-- Right Side: Navigation & Actions -->
            <div class="flex items-center gap-1">
                <?php if(!isset($isDashboard) || !$isDashboard): ?>
                <button id="mobile-menu-toggle" class="md:hidden p-2 rounded-lg <?php echo e($lightMode ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-slate-800 text-slate-300'); ?> transition-colors" title="Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <?php endif; ?>

                <!-- Cart Icon -->
                <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('marketplace.cart.index')); ?>" class="p-1 rounded-lg <?php echo e($lightMode ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-slate-800 text-slate-300'); ?> transition-colors relative" title="Cart">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span id="nav-cart-count" class="absolute top-1.5 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                </a>
                <?php endif; ?>

                <!-- Chat / Messages -->
                <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->role !== 'eo'): ?>
                <a href="<?php echo e(route('chat.index')); ?>" class="hidden md:block p-1 rounded-lg <?php echo e($lightMode ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-slate-800 text-slate-300'); ?> transition-colors relative" title="Messages">
                    <?php echo $__env->make('layouts.components.svg-chat', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </a>
                <?php endif; ?>
                <?php endif; ?>
                
                <!-- Notifications -->
                <?php if(auth()->guard()->check()): ?>
                <div class="relative" id="notification-container">
                    <button id="nav-bell-btn" class="p-1 rounded-lg <?php echo e($lightMode ? 'hover:bg-slate-100 text-slate-800' : 'hover:bg-slate-800 text-slate-300'); ?> transition-colors relative" title="Notifications">
                        <?php echo $__env->make('layouts.components.svg-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span id="notification-badge" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-[10px] font-bold text-white rounded-full flex items-center justify-center border <?php echo e($lightMode ? 'border-white' : 'border-slate-900'); ?> hidden"></span>
                    </button>
                    <div id="nav-bell-dropdown" class="absolute right-0 -mr-16 md:mr-0 mt-4 w-80 sm:w-96 <?php echo e($lightMode ? 'bg-white border-slate-200 shadow-xl' : 'bg-slate-900/95 border-slate-700 shadow-2xl'); ?> backdrop-blur-xl border rounded-2xl hidden overflow-hidden transform transition-all origin-top-right z-50">
                        <div class="p-4 border-b <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-800'); ?> flex justify-between items-center">
                            <h3 class="font-bold <?php echo e($lightMode ? 'text-slate-900' : 'text-white'); ?>">Notifications</h3>
                            <button id="mark-all-read" class="text-xs <?php echo e($lightMode ? 'text-slate-900' : 'text-neon'); ?> hover:text-slate-900 transition-colors">Mark all read</button>
                        </div>
                        <div id="notification-list" class="max-h-[400px] overflow-y-auto">
                            <div class="p-8 text-center text-slate-500 text-sm">Loading...</div>
                        </div>
                        <div class="p-3 border-t <?php echo e($lightMode ? 'border-slate-100 bg-slate-50/50' : 'border-slate-800 bg-slate-900/50'); ?>">
                            <a href="<?php echo e(route('notifications.index')); ?>" class="block w-full text-center py-2 rounded-lg <?php echo e($lightMode ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-slate-800 hover:bg-slate-700 text-slate-300'); ?> text-sm transition-colors">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(auth()->guard()->check()): ?>
                <!-- User Profile Dropdown -->
                <div class="relative" id="user-menu-container">
                    <button id="user-menu-btn" class="flex items-center gap-1 p-1.5 pr-1 rounded-full <?php echo e($lightMode ? 'hover:bg-slate-100 border-slate-200' : 'hover:bg-slate-800 border-transparent hover:border-slate-700'); ?> border transition-all">
                        <img class="w-8 h-8 rounded-full object-cover border <?php echo e($lightMode ? 'border-slate-200' : 'border-slate-600'); ?>" src="<?php echo e(auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg')); ?>" alt="<?php echo e(auth()->user()->name); ?>">
                        <?php
                            $name = auth()->user()->name;
                            $initials = collect(explode(' ', $name))
                                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                ->implode('');
                        ?>                            
                        <span class="hidden md:block text-sm font-medium <?php echo e($lightMode ? 'text-slate-900' : 'text-slate-200'); ?>"><?php echo e($initials); ?></span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    
                    <div id="user-menu-dropdown" class="absolute right-0 mt-4 w-56 <?php echo e($lightMode ? 'bg-white border-slate-200 shadow-xl' : 'bg-slate-900/95 border-slate-700 shadow-2xl'); ?> backdrop-blur-xl border rounded-2xl hidden transform transition-all origin-top-right z-50">
                        <div class="p-4 border-b <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-800'); ?>">
                            <p class="text-sm font-bold <?php echo e($lightMode ? 'text-slate-900' : 'text-white'); ?>"><?php echo e(auth()->user()->name); ?></p>
                            <p class="text-xs <?php echo e($lightMode ? 'text-slate-500' : 'text-primary'); ?> font-medium uppercase tracking-wider mt-0.5"><?php echo e(auth()->user()->role); ?></p>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="<?php echo e(route(auth()->user()->role . '.dashboard')); ?>" class="flex items-center gap-3 px-3 py-2 text-sm <?php echo e($lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                Dashboard
                            </a>
                            <a href="<?php echo e(route('profile.show')); ?>" class="flex items-center gap-3 px-3 py-2 text-sm <?php echo e($lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profile
                            </a>
                            <a href="<?php echo e(route('notifications.index')); ?>" class="flex items-center gap-1 px-3 py-2 text-sm <?php echo e($lightMode ? 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                Notifications
                            </a>
                            <div class="h-px <?php echo e($lightMode ? 'bg-slate-100' : 'bg-slate-800'); ?> my-1"></div>
                            <form action="<?php echo e(route('logout')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(auth()->guard()->guest()): ?>
                <div class="flex items-center gap-1.5 sm:gap-3">
                    <button onclick="openLoginModal()" class="text-xs sm:text-sm font-bold <?php echo e($lightMode ? 'text-slate-800 hover:text-slate-900' : 'text-slate-300 hover:text-white'); ?> transition-colors px-1 py-1">Login</button>
                    <button onclick="openRegisterModal()" class="px-2.5 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold bg-slate-900 text-white rounded-xl hover:bg-slate-800 hover:shadow-lg transition-all transform hover:-translate-y-0.5">Register</button>
                </div>
                <?php endif; ?>
                
                <?php if(auth()->guard()->check()): ?>
                <div class="hidden md:flex items-center gap-2 pl-2 border-l <?php echo e($lightMode ? 'border-slate-200' : 'border-slate-800'); ?>">
                    <a href="<?php echo e(route('pacer.index')); ?>" class="px-4 py-2 text-sm font-bold <?php echo e($lightMode ? 'text-slate-700 hover:text-slate-900' : 'text-slate-300 hover:text-neon'); ?> transition-colors">Pacers</a>
                    <a href="<?php echo e(route('pacer.register')); ?>" class="px-4 py-2 text-sm font-bold <?php echo e($lightMode ? 'bg-slate-900 text-white hover:bg-slate-800' : 'bg-neon text-dark hover:bg-lime-400 hover:shadow-neon/20'); ?> rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                        Join Pacer
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div id="mobile-menu-panel" class="md:hidden hidden fixed top-20 left-0 right-0 z-40 <?php echo e($lightMode ? 'bg-white/95 border-slate-200' : 'bg-slate-900/95 border-slate-700'); ?> backdrop-blur-xl border rounded-b-2xl shadow-2xl max-h-[80vh] overflow-y-auto">
    <div class="p-3 grid grid-cols-1 gap-1">
        <?php if($headerMenu): ?>
            <?php $__currentLoopData = $headerMenu->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($item->children->count() > 0): ?>
                    <!-- Submenu for <?php echo e($item->title); ?> -->
                    <div class="px-3 py-2">
                        <div class="text-xs font-bold text-slate-500 uppercase mb-2"><?php echo e($item->title); ?></div>
                        <?php $__currentLoopData = $item->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(url($child->url)); ?>" target="<?php echo e($child->target); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">
                                <?php echo e($child->title); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <!-- Single Link <?php echo e($item->title); ?> -->
                    <a href="<?php echo e(url($item->url)); ?>" target="<?php echo e($item->target); ?>" class="px-3 py-3 rounded-lg <?php echo e(request()->is(trim($item->url, '/') . '*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-300 hover:bg-slate-800 hover:text-white')); ?> transition-colors font-bold">
                        <?php echo e($item->title); ?>

                    </a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <!-- Fallback -->
            <a href="<?php echo e(route('programs.index')); ?>" class="px-3 py-3 rounded-lg <?php echo e($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> transition-colors font-bold">Programs</a>
            <a href="<?php echo e(route('marketplace.index')); ?>" class="px-3 py-3 rounded-lg <?php echo e(request()->routeIs('marketplace.*') ? ($lightMode ? 'bg-slate-50 text-primary' : 'bg-slate-800 text-white') : ($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-300 hover:bg-slate-800 hover:text-white')); ?> transition-colors font-bold">Marketplace</a>
            <a href="<?php echo e(route('coaches.index')); ?>" class="px-3 py-3 rounded-lg <?php echo e($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> transition-colors font-bold">Coach</a>
            <a href="<?php echo e(route('calendar.public')); ?>" class="px-3 py-3 rounded-lg <?php echo e($lightMode ? 'text-slate-600 hover:bg-slate-50 hover:text-primary' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?> transition-colors font-bold">Calendar</a>
        <?php endif; ?>


        <?php if(auth()->guard()->check()): ?>
        <div class="px-3 py-2 border-t <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-800'); ?> mt-2">
            <div class="flex items-center gap-3 mb-4 px-2 py-2 <?php echo e($lightMode ? 'bg-slate-50' : 'bg-slate-800/50'); ?> rounded-lg">
                 <img class="w-10 h-10 rounded-full object-cover border <?php echo e($lightMode ? 'border-slate-200' : 'border-slate-600'); ?>" src="<?php echo e(auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : (str_starts_with(auth()->user()->avatar, '/storage') ? asset(ltrim(auth()->user()->avatar, '/')) : asset('storage/' . auth()->user()->avatar))) : asset('images/profile/17.jpg')); ?>" alt="<?php echo e(auth()->user()->name); ?>">
                 <div>
                     <div class="font-bold <?php echo e($lightMode ? 'text-slate-900' : 'text-white'); ?> text-sm"><?php echo e(auth()->user()->name); ?></div>
                     <div class="text-xs text-primary font-medium uppercase tracking-wider"><?php echo e(auth()->user()->role); ?></div>
                 </div>
            </div>

            <div class="text-xs font-bold text-slate-500 uppercase mb-2">Account</div>
            <a href="<?php echo e(route(auth()->user()->role . '.dashboard')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">
                Dashboard
            </a>
            <a href="<?php echo e(route('profile.show')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">
                Profile
            </a>
            <a href="<?php echo e(route('marketplace.cart.index')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">
                Cart
                <span id="mobile-cart-count" class="ml-2 bg-neon text-dark text-[10px] font-bold px-1.5 rounded-full hidden">0</span>
            </a>
            <a href="<?php echo e(route('chat.index')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">
                Messages
            </a>
            <a href="<?php echo e(route('notifications.index')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">
                Notifications
                <span id="mobile-notif-count" class="ml-2 bg-red-500 text-white text-[10px] font-bold px-1.5 rounded-full hidden">0</span>
            </a>
            
            <form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full text-left block py-2 text-red-400 hover:text-red-300 pl-4 border-l border-transparent hover:border-red-500 transition-colors">
                    Logout
                </button>
            </form>
        </div>
        <?php endif; ?>

        <?php if(auth()->guard()->guest()): ?>
        <div class="px-3 py-2 border-t <?php echo e($lightMode ? 'border-slate-100' : 'border-slate-800'); ?> mt-2">
             <div class="text-xs font-bold text-slate-500 uppercase mb-2">Account</div>
             <a href="<?php echo e(route('login')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">Login</a>
             <a href="<?php echo e(route('register')); ?>" class="block py-2 <?php echo e($lightMode ? 'text-slate-600 hover:text-primary' : 'text-slate-300 hover:text-white'); ?> pl-4 border-l <?php echo e($lightMode ? 'border-slate-100 hover:border-primary' : 'border-slate-700 hover:border-neon'); ?> transition-colors">Register</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAuthenticated = <?php echo e(auth()->check() ? 'true' : 'false'); ?>;
    const userRole = <?php echo json_encode(auth()->check() ? auth()->user()->role : null); ?>;
    const supportsCartAndNotif = ['runner', 'coach', 'eo'].includes(userRole);

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
            return <?php echo json_encode(route('feed.index'), 15, 512) ?> + '#post-' + notif.reference_id;
        }
        if (notif.reference_type === 'EventSubmission' && notif.reference_id && userRole === 'admin') {
            return <?php echo json_encode(route('admin.event-submissions.show', ':id'), 512) ?>.replace(':id', notif.reference_id);
        }
        if (notif.reference_type === 'App\\Models\\RunningAnalysis\\AnalysisRequest') {
            if (userRole === 'admin' && notif.reference_id) {
                return <?php echo json_encode(route('admin.running-analysis.requests.show', ':id'), 512) ?>.replace(':id', notif.reference_id);
            }
            if (notif.reference_id) {
                return <?php echo json_encode(route('runner.analysis-requests.index'), 15, 512) ?>;
            }
        }
        return <?php echo json_encode(route('notifications.index'), 15, 512) ?>;
    }

    function fetchNotifications(showLoading = false) {
        if (!isAuthenticated || !supportsCartAndNotif) return;

        if (showLoading && notifList) {
            notifList.innerHTML =
                '<div class="p-8 text-center">' +
                    '<div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>' +
                    '<p class="text-slate-400 text-sm">Memuat notifikasi...</p>' +
                '</div>';
        }

        fetch('<?php echo e(route("notifications.unread")); ?>', {
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
                            '<div class="w-12 h-12 <?php echo e($lightMode ? "bg-slate-100" : "bg-slate-800"); ?> rounded-full flex items-center justify-center mx-auto mb-3">' +
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
                        var timeText = dayjs(n.created_at).fromNow();
                        html += '<a href="' + url + '" data-id="' + n.id + '" class="block p-4 border-b <?php echo e($lightMode ? "border-slate-100 hover:bg-slate-50" : "border-slate-800 hover:bg-slate-800/50"); ?> transition-colors group">';
                        html +=   '<div class="flex gap-3">';
                        html +=     '<div class="mt-1 w-2 h-2 rounded-full bg-primary shrink-0"></div>';
                        html +=     '<div>';
                        html +=       '<p class="text-sm <?php echo e($lightMode ? "text-slate-700" : "text-slate-200"); ?> group-hover:text-primary transition-colors font-bold">' + title + '</p>';
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
        if (isAuthenticated && supportsCartAndNotif) {
            fetchNotifications();
            setInterval(fetchNotifications, 60000);
        } else {
            if (notifBadge) notifBadge.classList.add('hidden');
            notifList.innerHTML =
                '<div class="p-8 text-center">' +
                    '<div class="w-12 h-12 <?php echo e($lightMode ? "bg-slate-100" : "bg-slate-800"); ?> rounded-full flex items-center justify-center mx-auto mb-3">' +
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
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    const target = link.getAttribute('href') || '<?php echo e(route("notifications.index")); ?>';
                    window.location.href = target;
                });
            }
        });
    }

    // Mark all read
    const markAllBtn = document.getElementById('mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            fetch('<?php echo e(route("notifications.read-all")); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
        if (!isAuthenticated || !supportsCartAndNotif) return;

        fetch('<?php echo e(route("marketplace.cart.count")); ?>', {
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

    if ((cartBadge || mobileCartBadge) && supportsCartAndNotif) {
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
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\ruanglari\resources\views/layouts/components/pacerhub-nav.blade.php ENDPATH**/ ?>