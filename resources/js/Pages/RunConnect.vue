<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, defineAsyncComponent } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

// Import Components using relative paths
import LocationPermissionState from '../Components/RunConnect/LocationPermissionState.vue';
import RunThreadFilters from '../Components/RunConnect/RunThreadFilters.vue';
const RunConnectMap = defineAsyncComponent(() => import('../Components/RunConnect/RunConnectMap.vue'));
import RunThreadList from '../Components/RunConnect/RunThreadList.vue';
import RunThreadBottomSheet from '../Components/RunConnect/RunThreadBottomSheet.vue';
import CreateRunThreadModal from '../Components/RunConnect/CreateRunThreadModal.vue';
import RandomMatchModal from '../Components/RunConnect/RandomMatchModal.vue';
import RunThreadRatingModal from '../Components/RunConnect/RunThreadRatingModal.vue';
import LoginModal from '../Components/RunConnect/LoginModal.vue';

const props = defineProps({
    mapboxToken: {
        type: String,
        default: ''
    },
    auth: {
        type: Object,
        default: () => ({ user: null })
    }
});

// Theme Management
const theme = ref('dark'); // default

const logoSrc = computed(() => {
    return theme.value === 'light' ? '/images/logo-red.png' : '/images/logo saja ruang lari.png';
});

const updateHtmlClass = (newTheme) => {
    if (newTheme === 'dark') {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
    } else {
        document.documentElement.classList.add('light');
        document.documentElement.classList.remove('dark');
    }
};

const initTheme = () => {
    const savedTheme = localStorage.getItem('run-connect-theme');
    if (savedTheme) {
        theme.value = savedTheme;
    } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
        theme.value = 'light';
    } else {
        theme.value = 'dark';
    }
    updateHtmlClass(theme.value);
};

const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
    localStorage.setItem('run-connect-theme', theme.value);
    updateHtmlClass(theme.value);
};

// App State
const userLocation = ref(null);
const permissionDenied = ref(false);
const viewMode = ref('map'); // 'map' or 'list'
const threads = ref([]);
const isLoading = ref(false);
const isJoining = ref(false);
const selectedThread = ref(null);
const isCreateOpen = ref(false);
const isMatchOpen = ref(false);
const isRatingOpen = ref(false);

const editingThread = ref(null);
const notifications = ref([]);
const isNotificationsOpen = ref(false);
const unreadNotificationsCount = computed(() => notifications.value.filter(n => !n.is_read).length);

const isLoginOpen = ref(false);
const isUserMenuOpen = ref(false);
const isFabOpen = ref(false);

const getUserAvatar = (user) => {
    if (!user?.avatar) return '/images/profile/17.jpg';
    if (user.avatar.startsWith('http')) return user.avatar;
    if (user.avatar.startsWith('/storage')) return user.avatar;
    return `/storage/${user.avatar}`;
};

const getUserInitials = (user) => {
    if (!user?.name) return '';
    return user.name.split(' ').map(word => word[0]).join('').substring(0, 2).toUpperCase();
};

const filters = ref({
    radius: 5,
    type: '',
    distance_filter: '',
    pace_filter: '',
    start_time_filter: '',
    slot_available: false,
    beginner_friendly: false,
    women_friendly: false
});

// Computed properties
const hasActiveFilters = computed(() => {
    return filters.value.type !== '' || 
           filters.value.distance_filter !== '' || 
           filters.value.pace_filter !== '' || 
           filters.value.start_time_filter !== '' || 
           filters.value.slot_available || 
           filters.value.beginner_friendly || 
           filters.value.women_friendly;
});

// Methods
const handleLocationSelected = (location) => {
    userLocation.value = location;
    permissionDenied.value = false;
    fetchThreads();
};

const handlePermissionDenied = () => {
    permissionDenied.value = true;
};

const fetchThreads = async () => {
    if (!userLocation.value) return;

    isLoading.value = true;
    try {
        const res = await axios.get('/api/run-connect/threads', {
            params: {
                latitude: userLocation.value.lat,
                longitude: userLocation.value.lng,
                radius: filters.value.radius,
                type: filters.value.type,
                distance_filter: filters.value.distance_filter,
                pace_filter: filters.value.pace_filter,
                start_time_filter: filters.value.start_time_filter,
                slot_available: filters.value.slot_available ? 'true' : 'false',
                beginner_friendly: filters.value.beginner_friendly ? 'true' : 'false',
                women_friendly: filters.value.women_friendly ? 'true' : 'false'
            }
        });
        threads.value = res.data.data || [];
    } catch (err) {
        console.error('Error fetching threads:', err);
    } finally {
        isLoading.value = false;
    }
};

const handleMapMoved = (center) => {
    userLocation.value = {
        ...userLocation.value,
        lat: center.lat,
        lng: center.lng,
        isApprox: true
    };
    fetchThreads();
};

const handleSelectThread = (thread) => {
    selectedThread.value = thread;
};

const handleJoinThread = async (threadId) => {
    if (!props.auth.user) {
        isLoginOpen.value = true;
        return;
    }

    isJoining.value = true;
    try {
        const res = await axios.post(`/api/run-connect/threads/${threadId}/join`);
        
        // Update local state
        const updated = res.data.thread;
        threads.value = threads.value.map(t => t.id === threadId ? { ...t, ...updated } : t);
        if (selectedThread.value && selectedThread.value.id === threadId) {
            selectedThread.value = { ...selectedThread.value, ...updated };
        }
        alert(res.data.message || 'Berhasil bergabung!');
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal bergabung dengan running thread.');
    } finally {
        isJoining.value = false;
    }
};

const handleLeaveThread = async (threadId) => {
    isJoining.value = true;
    try {
        const res = await axios.post(`/api/run-connect/threads/${threadId}/leave`);
        
        // Update local state
        const updated = res.data.thread;
        threads.value = threads.value.map(t => t.id === threadId ? { ...t, ...updated } : t);
        if (selectedThread.value && selectedThread.value.id === threadId) {
            selectedThread.value = { ...selectedThread.value, ...updated };
        }
        alert(res.data.message || 'Berhasil keluar.');
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal keluar dari running thread.');
    } finally {
        isJoining.value = false;
    }
};

const handleThreadCreated = (newThread) => {
    threads.value.unshift(newThread);
    selectedThread.value = newThread;
};

const resetFilters = () => {
    filters.value = {
        radius: 5,
        type: '',
        distance_filter: '',
        pace_filter: '',
        start_time_filter: '',
        slot_available: false,
        beginner_friendly: false,
        women_friendly: false
    };
    fetchThreads();
};

const handleEditThread = (thread) => {
    editingThread.value = thread;
    isCreateOpen.value = true;
};

const handleDeletedThread = (threadId) => {
    threads.value = threads.value.filter(t => t.id !== threadId);
    if (selectedThread.value && selectedThread.value.id === threadId) {
        selectedThread.value = null;
    }
};

const handleThreadUpdated = (updatedThread) => {
    threads.value = threads.value.map(t => t.id === updatedThread.id ? { ...t, ...updatedThread } : t);
    if (selectedThread.value && selectedThread.value.id === updatedThread.id) {
        selectedThread.value = { ...selectedThread.value, ...updatedThread };
    }
};

const fetchNotifications = async () => {
    try {
        const res = await axios.get('/api/notifications');
        const oldNotifications = [...notifications.value];
        notifications.value = res.data.notifications || [];
        
        // Trigger browser push notification for newly arrived unread notifications
        if (oldNotifications.length > 0) {
            notifications.value.forEach(notification => {
                if (!notification.is_read) {
                    const isNew = !oldNotifications.some(old => old.id === notification.id);
                    if (isNew) {
                        triggerBrowserPushNotification(notification.title, notification.message);
                    }
                }
            });
        }
    } catch (err) {
        console.error('Error fetching notifications:', err);
    }
};

const triggerBrowserPushNotification = (title, body) => {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: body
        });
    }
};

const requestNotificationPermission = () => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
};

const markNotificationRead = async (notification) => {
    if (notification.is_read) return;
    try {
        await axios.post(`/api/notifications/${notification.id}/read`);
        notification.is_read = true;
        
        // Optionally select referenced thread
        if (notification.reference_type === 'RunThread' && notification.reference_id) {
            const thread = threads.value.find(t => t.id === notification.reference_id);
            if (thread) {
                selectedThread.value = thread;
                isNotificationsOpen.value = false;
            }
        }
    } catch (err) {
        console.error('Error marking notification read:', err);
    }
};

const markAllNotificationsRead = async () => {
    try {
        await axios.post('/api/notifications/read-all');
        notifications.value = notifications.value.map(n => ({ ...n, is_read: true, read_at: new Date().toISOString() }));
    } catch (err) {
        console.error('Error marking all notifications read:', err);
    }
};

const handleLoginSuccess = () => {
    router.reload({
        only: ['auth'],
        onSuccess: () => {
            fetchNotifications();
            fetchThreads();
            requestNotificationPermission();
            if (!notificationInterval) {
                notificationInterval = setInterval(fetchNotifications, 10000);
            }
        }
    });
};

const handleLogout = async () => {
    try {
        await axios.post('/logout', {}, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (notificationInterval) {
            clearInterval(notificationInterval);
            notificationInterval = null;
        }
        notifications.value = [];
        isUserMenuOpen.value = false;
        
        router.reload({
            only: ['auth'],
            onSuccess: () => {
                fetchThreads();
            }
        });
    } catch (err) {
        console.error('Logout failed:', err);
    }
};

let notificationInterval = null;

const handleOutsideClick = (e) => {
    const userMenu = document.getElementById('user-menu-container');
    if (userMenu && !userMenu.contains(e.target)) {
        isUserMenuOpen.value = false;
    }
};

onMounted(() => {
    initTheme();
    requestNotificationPermission();
    if (props.auth?.user) {
        fetchNotifications();
        notificationInterval = setInterval(fetchNotifications, 10000); // poll every 10s
    }
    window.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
    if (notificationInterval) {
        clearInterval(notificationInterval);
    }
    window.removeEventListener('click', handleOutsideClick);
});
</script>

<template>
    <Head>
        <title>Run Connect - Temukan Buddy Lari Terdekat | Ruang Lari</title>
        <meta name="description" content="Cari teman lari (running buddy) terdekat, buat running thread, dan bergabung dengan komunitas pelari di sekitar Anda secara real-time melalui Run Connect Ruang Lari." />
        <meta name="keywords" content="teman lari, running buddy, komunitas lari, run connect, ruang lari, cari teman lari, peta pelari, jadwal lari" />
        <meta property="og:title" content="Ruang Lari - Temukan Buddy Lari Terdekat | Run Connect" />
        <meta property="og:description" content="Cari teman lari (running buddy) terdekat, buat running thread, dan bergabung dengan komunitas pelari di sekitar Anda secara real-time melalui Run Connect Ruang Lari." />
        <meta property="og:image" content="/images/ruanglari-512x512.png" />
        <meta property="og:type" content="website" />
    </Head>
    
    <div :class="theme" class="min-h-screen transition-colors duration-300">
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col">
            <!-- Header -->
            <header class="border-b border-slate-200 dark:border-slate-850 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-30 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <a href="/" class="flex items-center gap-2 cursor-pointer">
                            <img :src="logoSrc" alt="RuangLari" class="h-6 sm:h-8 w-auto">
                            <div class="text-sm sm:text-lg md:text-xl font-black italic tracking-tighter flex items-center text-slate-900 dark:text-white">
                                RUANG<span class="pl-1 text-slate-900 dark:text-[#ccff00]">LARI</span>
                            </div>
                        </a>    
                        <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full hidden md:inline"></span>
                        <span class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 hidden md:inline">Run Connect</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Gamification Info -->
                        <div v-if="auth.user" class="hidden md:flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 font-semibold text-xs">
                            {{ auth.user.run_points }} pts
                            <span v-if="auth.user.buddy_rating" class="ml-1.5 pl-1.5 border-l border-slate-300 dark:border-slate-600">{{ Number(auth.user.buddy_rating).toFixed(1) }} rating</span>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div v-if="auth.user" class="relative">
                            <button 
                                @click="isNotificationsOpen = !isNotificationsOpen" 
                                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-750 transition-colors cursor-pointer relative"
                                title="Notifikasi"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span v-if="unreadNotificationsCount > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            </button>

                            <!-- Notifications Panel -->
                            <div v-if="isNotificationsOpen" class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 py-2">
                                <div class="px-4 py-2 border-b border-slate-150 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900">
                                    <span class="font-bold text-xs text-slate-800 dark:text-white">Notifikasi</span>
                                    <button @click="markAllNotificationsRead" class="text-[10px] text-blue-650 dark:text-[#ccff00] hover:underline font-bold cursor-pointer">Tandai semua dibaca</button>
                                </div>
                                <div class="max-h-64 overflow-y-auto scroll-thin">
                                    <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada notifikasi.</div>
                                    <div 
                                        v-for="n in notifications" 
                                        :key="n.id"
                                        @click="markNotificationRead(n)"
                                        class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer flex flex-col gap-1 transition-colors duration-150"
                                        :class="!n.is_read ? 'bg-blue-50/20 dark:bg-blue-500/5' : ''"
                                    >
                                        <div class="flex justify-between items-start gap-2">
                                            <span class="font-semibold text-xs leading-none" :class="!n.is_read ? 'text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-450'">
                                                {{ n.title }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 shrink-0">{{ new Date(n.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                        </div>
                                        <p class="text-[11px] leading-relaxed" :class="!n.is_read ? 'text-slate-700 dark:text-slate-305 font-medium' : 'text-slate-500 dark:text-slate-450'">
                                            {{ n.message }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Toggle Button -->
                        <button 
                            @click="toggleTheme" 
                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-200 dark:hover:bg-slate-750 transition-colors cursor-pointer mr-1"
                            title="Toggle Light/Dark Mode"
                        >
                            <svg v-if="theme === 'dark'" class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <svg v-else class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- Auth Actions -->
                        <div class="flex items-center gap-2">
                            <button 
                                v-if="!auth.user"
                                @click="isLoginOpen = true"
                                class="px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white dark:text-slate-200 text-[11px] font-black uppercase tracking-widest rounded-full hover:bg-blue-650 dark:hover:bg-[#ccff00] dark:hover:text-slate-950 transition-all duration-300 transform hover:scale-105 active:scale-95 cursor-pointer shadow-lg hover:shadow-blue-500/20"
                            >
                                Login
                            </button>

                            <!-- User Profile Dropdown -->
                            <div v-else class="relative" id="user-menu-container">
                                <button 
                                    @click="isUserMenuOpen = !isUserMenuOpen" 
                                    class="flex items-center gap-1.5 p-1 pr-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 transition-all cursor-pointer"
                                >
                                    <img 
                                        class="w-7 h-7 rounded-full object-cover border border-slate-200 dark:border-slate-700" 
                                        :src="getUserAvatar(auth.user)" 
                                        :alt="auth.user.name"
                                    >
                                    <span class="hidden md:block text-xs font-semibold text-slate-700 dark:text-slate-200 mr-0.5">
                                        {{ getUserInitials(auth.user) }}
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div 
                                    v-if="isUserMenuOpen" 
                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 py-1.5"
                                >
                                    <div class="px-4 py-2 border-b border-slate-150 dark:border-slate-850">
                                        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ auth.user.name }}</p>
                                        <p class="text-[10px] text-blue-650 dark:text-[#ccff00] font-bold uppercase tracking-wider mt-0.5">{{ auth.user.role }}</p>
                                    </div>
                                    <div class="p-1.5 space-y-0.5">
                                        <a :href="`/${auth.user.role}/dashboard`" class="flex items-center gap-2.5 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                            Dashboard
                                        </a>
                                        <a href="/profile" class="flex items-center gap-2.5 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            Profile
                                        </a>
                                        <div class="h-px bg-slate-100 dark:bg-slate-800 my-1"></div>
                                        <button 
                                            @click="handleLogout"
                                            class="w-full flex items-center gap-2.5 px-3 py-1.5 text-xs text-red-500 hover:bg-red-500/10 hover:text-red-650 dark:hover:bg-red-500/10 dark:hover:text-red-400 rounded-lg transition-colors cursor-pointer text-left font-medium"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                            Logout
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Body Area -->
            <main class="flex-grow max-w-7xl w-full mx-auto px-4 py-6 flex flex-col">
                
                <!-- Location Permission / Setup Phase -->
                <div v-if="!userLocation" class="flex-grow flex items-center justify-center">
                    <LocationPermissionState 
                        :theme="theme"
                        @location-selected="handleLocationSelected"
                        @permission-denied="handlePermissionDenied"
                    />
                </div>

                <!-- Active Discovery Interface -->
                <template v-else>
                    <!-- Mobile Toggle View Bar (Only visible on mobile) -->
                    <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 mb-4 lg:hidden transition-colors duration-300">
                        <div class="flex bg-slate-100 dark:bg-slate-950 rounded-xl p-1 w-full">
                            <button 
                                @click="viewMode = 'map'"
                                :class="viewMode === 'map' ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                                class="flex-1 py-2 text-xs font-bold rounded-lg transition-all cursor-pointer"
                            >
                                🗺️ Map View
                            </button>
                            <button 
                                @click="viewMode = 'list'"
                                :class="viewMode === 'list' ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                                class="flex-1 py-2 text-xs font-bold rounded-lg transition-all cursor-pointer"
                            >
                                📋 List View
                            </button>
                        </div>
                    </div>

                    <!-- Split-Screen Grid Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start flex-grow">
                        
                        <!-- LEFT COLUMN: Filters + List (Hidden on mobile if viewMode is 'map') -->
                        <div 
                            :class="{'hidden lg:flex': viewMode === 'map', 'flex': viewMode === 'list'}"
                            class="lg:col-span-5 xl:col-span-4 flex-col space-y-4 h-full"
                        >
                            <RunThreadFilters 
                                v-model:filters="filters"
                                :theme="theme"
                                @change="fetchThreads"
                            />
                            
                            <div class="flex-grow overflow-y-auto max-h-[70vh] pr-1 scroll-thin">
                                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3">Running Threads Terdekat</h3>
                                <RunThreadList 
                                    :threads="threads"
                                    :user="auth.user"
                                    :is-loading="isLoading"
                                    :is-joining="isJoining"
                                    :has-filters="hasActiveFilters"
                                    :theme="theme"
                                    @select-thread="handleSelectThread"
                                    @join-thread="handleJoinThread"
                                    @leave-thread="handleLeaveThread"
                                    @reset-filters="resetFilters"
                                    @create-click="isCreateOpen = true"
                                />
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Mapbox (Hidden on mobile if viewMode is 'list') -->
                        <div 
                            :class="{'hidden lg:block': viewMode === 'list', 'block': viewMode === 'map'}"
                            class="lg:col-span-7 xl:col-span-8 sticky top-24"
                        >
                             <RunConnectMap 
                                 :mapbox-token="mapboxToken"
                                 :user-location="userLocation"
                                 :threads="threads"
                                 :theme="theme"
                                 @select-thread="handleSelectThread"
                                 @map-moved="handleMapMoved"
                                 @location-selected="handleLocationSelected"
                             />
                        </div>

                    </div>
                </template>
            </main>

            <!-- Selected Thread Bottom Sheet Details -->
            <RunThreadBottomSheet 
                :thread="selectedThread"
                :user="auth.user"
                :is-joining="isJoining"
                :theme="theme"
                @close="selectedThread = null"
                @join="handleJoinThread"
                @leave="handleLeaveThread"
                @open-rating="isRatingOpen = true"
                @report-success="fetchThreads"
                @edit="handleEditThread"
                @deleted="handleDeletedThread"
            />

            <!-- Multi-step Creation Modal -->
            <CreateRunThreadModal 
                :is-open="isCreateOpen"
                :user-location="userLocation"
                :theme="theme"
                :mapbox-token="mapboxToken"
                :edit-thread="editingThread"
                @close="isCreateOpen = false; editingThread = null"
                @created="handleThreadCreated"
                @updated="handleThreadUpdated"
            />

            <!-- Random Match Modal -->
            <RandomMatchModal 
                v-if="isMatchOpen && userLocation"
                :is-open="isMatchOpen"
                :user-location="userLocation"
                :theme="theme"
                @close="isMatchOpen = false"
                @select-thread="handleSelectThread"
            />

            <!-- Rating Modal -->
            <RunThreadRatingModal
                v-if="isRatingOpen && selectedThread"
                :thread="selectedThread"
                :user="auth.user"
                @close="isRatingOpen = false"
                @rated="fetchThreads"
            />

            <!-- Floating Action Button (FAB) Menu (Only visible after userLocation selected) -->
            <div 
                v-if="userLocation"
                class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3 select-none"
            >
                <!-- Sub-actions (reveal upward) -->
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform translate-y-4 opacity-0 scale-95"
                    enter-to-class="transform translate-y-0 opacity-100 scale-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100 scale-100"
                    leave-to-class="transform translate-y-4 opacity-0 scale-95"
                >
                    <div 
                        v-if="isFabOpen" 
                        class="flex flex-col items-end gap-3 mb-1"
                    >
                        <!-- Match Buddy Button -->
                        <button 
                            @click="auth.user ? (isMatchOpen = true) : (isLoginOpen = true); isFabOpen = false"
                            class="flex items-center gap-2 px-4.5 py-2.5 bg-blue-650 dark:bg-indigo-650 hover:bg-blue-700 dark:hover:bg-indigo-600 text-white text-xs font-bold rounded-full shadow-lg cursor-pointer transform transition hover:scale-105"
                        >
                            <span>Match Buddy</span>
                            <span class="text-sm">🎯</span>
                        </button>

                        <!-- Buat Thread Button -->
                        <button 
                            @click="auth.user ? (isCreateOpen = true) : (isLoginOpen = true); isFabOpen = false"
                            class="flex items-center gap-2 px-4.5 py-2.5 bg-blue-650 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-bold rounded-full shadow-lg cursor-pointer transform transition hover:scale-105"
                        >
                            <span>Buat Thread Lari</span>
                            <span class="text-sm">🏃</span>
                        </button>
                    </div>
                </transition>

                <!-- Main Action FAB -->
                <button 
                    @click="isFabOpen = !isFabOpen"
                    class="w-14 h-14 bg-blue-650 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 rounded-full flex items-center justify-center shadow-2xl transition-all duration-300 transform cursor-pointer animate-fab-breath"
                    :class="{ 'rotate-45': isFabOpen }"
                    title="Menu Aksi Lari"
                >
                    <svg class="w-6 h-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>

            <!-- Login Modal -->
            <LoginModal 
                :is-open="isLoginOpen"
                :theme="theme"
                @close="isLoginOpen = false"
                @success="handleLoginSuccess"
            />
        </div>
    </div>
</template>

<style scoped>
.scroll-thin::-webkit-scrollbar {
    width: 4px;
}
.scroll-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scroll-thin::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.2);
    border-radius: 2px;
}
.dark .scroll-thin::-webkit-scrollbar-thumb {
    background: #1e293b;
}

@keyframes fab-breath {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.35);
    }
    50% {
        transform: scale(1.06);
        box-shadow: 0 4px 22px 0 rgba(37, 99, 235, 0.65);
    }
}
.animate-fab-breath {
    animation: fab-breath 2s infinite ease-in-out;
}

@keyframes fab-breath-dark {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 4px 14px 0 rgba(204, 255, 0, 0.35);
    }
    50% {
        transform: scale(1.06);
        box-shadow: 0 4px 22px 0 rgba(204, 255, 0, 0.75);
    }
}
.dark .animate-fab-breath {
    animation: fab-breath-dark 2s infinite ease-in-out;
}
</style>
