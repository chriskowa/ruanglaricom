@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manage Users')

@section('content')
<div x-data="{ 
    showModal: false, 
    createModal: false,
    isLoading: false,
    loadingTransactions: false,
    loadingUser: false,
    searchQuery: '{{ request('q') }}',
    filterRole: '{{ request('role', 'all') }}',
    filterStatus: '{{ request('status', 'all') }}',
    activeTab: 'profile', // profile, socials, financial, performance
    
    selectedUser: null,
    
    // Create Form Data
    newUser: {
        name: '',
        email: '',
        username: '',
        password: '',
        role: 'runner',
        program_id: ''
    },

    async fetchUsers(url = null) {
        this.isLoading = true;
        const params = new URLSearchParams({
            q: this.searchQuery,
            role: this.filterRole,
            status: this.filterStatus
        });
        
        try {
            const endpoint = url || '{{ route('admin.users.index') }}';
            const res = await fetch(`${endpoint}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const html = await res.text();
            document.getElementById('users-table-container').innerHTML = html;
            
            // Re-initialize pagination links to use AJAX
            this.initPagination();
        } catch (error) {
            console.error('Error fetching users:', error);
        } finally {
            this.isLoading = false;
        }
    },

    initPagination() {
        const container = document.getElementById('users-table-container');
        if (!container) return;
        const links = container.querySelectorAll('a.page-link, .pagination a');
        
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.fetchUsers(link.href);
            });
        });
    },

    async openModal(userId) {
        this.showModal = true;
        this.loadingUser = true;
        this.selectedUser = null; // Clear previous data
        this.activeTab = 'profile';

        try {
            const res = await fetch(`/admin/users/${userId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!res.ok) throw new Error('Failed to fetch user');
            
            const user = await res.json();
            
            // Normalize Data
            this.selectedUser = user;
            
            // Ensure null fields are strings for inputs
            const textFields = [
                'username', 'phone', 'gender', 'address', 
                'pb_5k', 'pb_10k', 'pb_hm', 'pb_fm',
                'strava_url', 'instagram_url', 'facebook_url', 'tiktok_url',
                'bank_name', 'bank_account_name', 'bank_account_number' // if flat
            ];
            
            textFields.forEach(f => {
                if (this.selectedUser[f] === null || this.selectedUser[f] === undefined) {
                    this.selectedUser[f] = '';
                }
            });

            // Date fix
            if (this.selectedUser.date_of_birth) {
                this.selectedUser.date_of_birth = this.selectedUser.date_of_birth.split('T')[0];
            }

            // Bank Object Flattening (if coming from JSON cast on model)
            // The controller might update specific fields but model has `bank_account` json column.
            // If the user object has bank_account json, let's try to populate flat fields if empty
            if (this.selectedUser.bank_account && typeof this.selectedUser.bank_account === 'object') {
                this.selectedUser.bank_name = this.selectedUser.bank_name || this.selectedUser.bank_account.bank_name || '';
                this.selectedUser.bank_account_name = this.selectedUser.bank_account_name || this.selectedUser.bank_account.account_name || '';
                this.selectedUser.bank_account_number = this.selectedUser.bank_account_number || this.selectedUser.bank_account.account_number || '';
            }

            // Wallet Init
            this.selectedUser.wallet = this.selectedUser.wallet || { balance: 0, transactions: [] };
            
            // Clean paths
            if (this.selectedUser.avatar) this.selectedUser.avatar = this.selectedUser.avatar.replace(/^\/?storage\//, '');
            if (this.selectedUser.banner) this.selectedUser.banner = this.selectedUser.banner.replace(/^\/?storage\//, '');

            // Fetch transactions (already loaded via load('wallet') but transactions might need separate call if we want pagination/limit)
            // Controller returns `user->load('wallet')`. Wallet transactions are usually separate relation `wallet->transactions`.
            // Let's fetch latest transactions explicitly to be safe/fresh
            this.fetchTransactions(userId);

        } catch (error) {
            console.error(error);
            alert('Failed to load user data');
            this.showModal = false;
        } finally {
            this.loadingUser = false;
        }
    },

    async fetchTransactions(userId) {
        this.loadingTransactions = true;
        try {
            const res = await fetch(`/admin/users/${userId}/transactions`);
            const data = await res.json();
            if (this.selectedUser && this.selectedUser.id === userId && this.selectedUser.wallet) {
                this.selectedUser.wallet.transactions = data.transactions;
            }
        } catch (error) {
            console.error('Error fetching transactions:', error);
        } finally {
            this.loadingTransactions = false;
        }
    },

    closeModal() {
        this.showModal = false;
        setTimeout(() => {
            this.selectedUser = null;
        }, 300);
    },
    openCreateModal() {
        this.createModal = true;
    },
    closeCreateModal() {
        this.createModal = false;
        this.newUser = {
            name: '',
            email: '',
            username: '',
            password: '',
            role: 'runner',
            program_id: ''
        };
    }
}" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                USER MANAGEMENT
            </h1>
            <p class="text-slate-400 mt-1">Monitor, manage, and support your platform users.</p>
        </div>
        
        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-end">
            <button @click="openCreateModal()" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Add New User
            </button>
        </div>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl relative z-10">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h4 class="font-bold">There were errors with your submission:</h4>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl relative z-10">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    
    @if (session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl relative z-10">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="font-bold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Search & Filter -->
    <div class="mb-6">
        <form @submit.prevent="fetchUsers()" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-center">
            <div class="relative group w-full sm:w-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-500 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="searchQuery" @input.debounce.500ms="fetchUsers()"
                    class="block w-full rounded-xl border-slate-700 bg-slate-800/50 text-white pl-10 pr-3 py-2.5 focus:border-blue-500 focus:ring-blue-500 placeholder-slate-500 sm:text-sm transition-all" 
                    placeholder="Search users...">
            </div>
            
            <select x-model="filterRole" @change="fetchUsers()" 
                class="w-full sm:w-auto rounded-xl border-slate-700 bg-slate-800/50 text-white py-2.5 pl-3 pr-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all cursor-pointer">
                <option value="all">All Roles</option>
                <option value="runner">Runners</option>
                <option value="coach">Coaches</option>
                <option value="admin">Admins</option>
            </select>

            <select x-model="filterStatus" @change="fetchUsers()" 
                class="w-full sm:w-auto rounded-xl border-slate-700 bg-slate-800/50 text-white py-2.5 pl-3 pr-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all cursor-pointer">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div x-show="isLoading" class="text-blue-500" style="display: none;">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 relative z-10">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-blue-500/10 text-blue-500">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Total Users</p>
                <p class="text-2xl font-black text-white">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-green-500/10 text-green-500">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Runners</p>
                <p class="text-2xl font-black text-white">{{ $stats['runners'] }}</p>
            </div>
        </div>
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-purple-500/10 text-purple-500">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Coaches</p>
                <p class="text-2xl font-black text-white">{{ $stats['coaches'] }}</p>
            </div>
        </div>
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-emerald-500/10 text-emerald-500">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Active Now</p>
                <p class="text-2xl font-black text-white">{{ $stats['active'] }}</p>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div id="users-table-container">
        @include('admin.users.partials.table')
    </div>

    <!-- Edit User Modal -->
    <div x-show="showModal" 
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" 
                @click="closeModal" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-700">
                
                <div class="bg-slate-800/50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-700">
                    <div class="sm:flex sm:items-center justify-between">
                        <h3 class="text-xl font-bold leading-6 text-white" id="modal-title">
                            Edit User Profile
                        </h3>
                        <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-700 px-6 gap-6 overflow-x-auto">
                    <button @click="activeTab = 'profile'" :class="{'text-blue-500 border-blue-500': activeTab === 'profile', 'text-slate-400 border-transparent hover:text-white': activeTab !== 'profile'}" class="py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Profile & Media</button>
                    <button @click="activeTab = 'socials'" :class="{'text-blue-500 border-blue-500': activeTab === 'socials', 'text-slate-400 border-transparent hover:text-white': activeTab !== 'socials'}" class="py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Socials</button>
                    <button @click="activeTab = 'performance'" :class="{'text-blue-500 border-blue-500': activeTab === 'performance', 'text-slate-400 border-transparent hover:text-white': activeTab !== 'performance'}" class="py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Performance (PB)</button>
                    <button @click="activeTab = 'financial'" :class="{'text-blue-500 border-blue-500': activeTab === 'financial', 'text-slate-400 border-transparent hover:text-white': activeTab !== 'financial'}" class="py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Financial Info</button>
                    <button @click="activeTab = 'wallet'" :class="{'text-blue-500 border-blue-500': activeTab === 'wallet', 'text-slate-400 border-transparent hover:text-white': activeTab !== 'wallet'}" class="py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Wallet Manager</button>
                </div>

                <!-- Loading State -->
                <div x-show="loadingUser" class="p-12 flex flex-col items-center justify-center text-slate-500">
                    <svg class="animate-spin h-10 w-10 mb-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p>Loading user data...</p>
                </div>

                <!-- Main Form -->
                <form x-show="!loadingUser && activeTab !== 'wallet'" x-bind:action="selectedUser ? '{{ url('admin/users') }}/' + selectedUser.id : '#'" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-4 py-5 sm:p-6 max-h-[70vh] overflow-y-auto">
                        <template x-if="selectedUser">
                            <div class="space-y-6">
                                
                                <!-- Tab: Profile -->
                                <div x-show="activeTab === 'profile'" class="space-y-6">
                                    <!-- Basic Info -->
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Basic Information</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                                                <input type="text" name="name" x-model="selectedUser.name" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                                                <input type="text" name="username" x-model="selectedUser.username" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                                                <input type="email" name="email" x-model="selectedUser.email" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                                                <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                                                <input type="text" name="phone" x-model="selectedUser.phone" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Role</label>
                                                <select name="role" x-model="selectedUser.role" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="runner">Runner</option>
                                                    <option value="coach">Coach</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="eo">Event Organizer</option>
                                                </select>
                                            </div>
                                            <div class="flex items-center pt-6">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" value="1" x-model="selectedUser.is_active" class="rounded bg-slate-800 border-slate-700 text-blue-500 focus:ring-blue-500 w-5 h-5">
                                                    <span class="text-sm font-medium text-slate-300">Account Active</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Personal Details -->
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-t border-slate-700 pt-4">Personal Details</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Gender</label>
                                                <select name="gender" x-model="selectedUser.gender" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">Select Gender</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Date of Birth</label>
                                                <input type="date" name="date_of_birth" x-model="selectedUser.date_of_birth" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                                                <textarea name="address" x-model="selectedUser.address" rows="2" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Media Files -->
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-t border-slate-700 pt-4">Media Assets</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-2">Avatar</label>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-16 h-16 rounded-full bg-slate-700 overflow-hidden flex-shrink-0">
                                                        <template x-if="selectedUser.avatar">
                                                            <img :src="'/storage/' + selectedUser.avatar" class="w-full h-full object-cover">
                                                        </template>
                                                        <template x-if="!selectedUser.avatar">
                                                            <div class="w-full h-full flex items-center justify-center text-slate-500">No Img</div>
                                                        </template>
                                                    </div>
                                                    <input type="file" name="avatar" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-700 file:text-white hover:file:bg-slate-600">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-2">Banner</label>
                                                <div class="w-full h-24 bg-slate-700 rounded-xl overflow-hidden mb-2 relative">
                                                     <template x-if="selectedUser.banner">
                                                        <img :src="'/storage/' + selectedUser.banner" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!selectedUser.banner">
                                                        <div class="w-full h-full flex items-center justify-center text-slate-500">No Banner</div>
                                                    </template>
                                                </div>
                                                <input type="file" name="banner" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-700 file:text-white hover:file:bg-slate-600">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Performance -->
                                <div x-show="activeTab === 'performance'">
                                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Personal Bests (HH:MM:SS)</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">5K</label>
                                            <input type="text" name="pb_5k" x-model="selectedUser.pb_5k" placeholder="00:00:00" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">10K</label>
                                            <input type="text" name="pb_10k" x-model="selectedUser.pb_10k" placeholder="00:00:00" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Half Marathon</label>
                                            <input type="text" name="pb_hm" x-model="selectedUser.pb_hm" placeholder="00:00:00" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Full Marathon</label>
                                            <input type="text" name="pb_fm" x-model="selectedUser.pb_fm" placeholder="00:00:00" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Socials -->
                                <div x-show="activeTab === 'socials'">
                                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Social Media Links</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Strava URL</label>
                                            <input type="url" name="strava_url" x-model="selectedUser.strava_url" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Instagram URL</label>
                                            <input type="url" name="instagram_url" x-model="selectedUser.instagram_url" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Facebook URL</label>
                                            <input type="url" name="facebook_url" x-model="selectedUser.facebook_url" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">TikTok URL</label>
                                            <input type="url" name="tiktok_url" x-model="selectedUser.tiktok_url" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Financial Info (Bank) -->
                                <div x-show="activeTab === 'financial'">
                                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Bank Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Bank Name</label>
                                            <input type="text" name="bank_name" x-model="selectedUser.bank_name" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Account Name</label>
                                            <input type="text" name="bank_account_name" x-model="selectedUser.bank_account_name" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Account Number</label>
                                            <input type="text" name="bank_account_number" x-model="selectedUser.bank_account_number" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>
                    <div class="bg-slate-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Changes
                        </button>
                        <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-600 shadow-sm px-4 py-2 bg-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>

                <!-- Tab: Wallet Manager (Separate Form) -->
                <div x-show="!loadingUser && activeTab === 'wallet'" class="bg-slate-800/30 border-t border-slate-700 px-4 py-5 sm:p-6">
                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Wallet Management</h4>
                    <div class="bg-slate-900/50 rounded-xl p-4 border border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-slate-400">Current Balance</p>
                                <p class="text-2xl font-black text-white" x-text="'Rp ' + (selectedUser && selectedUser.wallet ? new Intl.NumberFormat('id-ID').format(selectedUser.wallet.balance) : '0')"></p>
                            </div>
                        </div>
                        
                        <form x-bind:action="selectedUser ? '{{ url('admin/users') }}/' + selectedUser.id + '/wallet' : '#'" method="POST" class="space-y-3">
                            @csrf
                            <p class="text-xs font-bold text-slate-500 uppercase">Manual Adjustment</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <select name="type" class="rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="deposit">Deposit (Add)</option>
                                    <option value="withdraw">Withdraw (Deduct)</option>
                                </select>
                                <input type="number" name="amount" placeholder="Amount" required min="1" class="rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <input type="text" name="description" placeholder="Description / Reason" required class="md:col-span-3 rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <div class="md:col-span-3 flex justify-end">
                                    <button type="submit" onclick="return confirm('Are you sure you want to adjust this wallet balance?')" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                        Process Adjustment
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Transaction History -->
                        <div class="mt-6 pt-4 border-t border-slate-700">
                            <h5 class="text-xs font-bold text-slate-400 uppercase mb-3">Recent Transactions</h5>
                            <div class="overflow-x-auto rounded-lg border border-slate-700">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-800 text-slate-400">
                                        <tr>
                                            <th class="px-3 py-2">Date</th>
                                            <th class="px-3 py-2">Type</th>
                                            <th class="px-3 py-2">Amount</th>
                                            <th class="px-3 py-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700 text-slate-300 bg-slate-900/30">
                                        <template x-if="loadingTransactions">
                                            <tr>
                                                <td colspan="4" class="px-3 py-4 text-center text-slate-500">
                                                    <div class="flex justify-center items-center gap-2">
                                                        <svg class="animate-spin h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span>Loading...</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="!loadingTransactions && selectedUser && selectedUser.wallet && selectedUser.wallet.transactions && selectedUser.wallet.transactions.length > 0">
                                            <template x-for="trx in selectedUser.wallet.transactions" :key="trx.id">
                                                <tr>
                                                    <td class="px-3 py-2" x-text="new Date(trx.created_at).toLocaleDateString()"></td>
                                                    <td class="px-3 py-2 uppercase font-bold" x-text="trx.type" :class="trx.type === 'deposit' ? 'text-emerald-400' : 'text-red-400'"></td>
                                                    <td class="px-3 py-2 font-mono" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(trx.amount)"></td>
                                                    <td class="px-3 py-2" x-text="trx.status"></td>
                                                </tr>
                                            </template>
                                        </template>
                                        <template x-if="!loadingTransactions && (!selectedUser || !selectedUser.wallet || !selectedUser.wallet.transactions || selectedUser.wallet.transactions.length === 0)">
                                            <tr>
                                                <td colspan="4" class="px-3 py-4 text-center text-slate-500">No transactions found</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-show="createModal" 
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="createModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" 
                @click="closeCreateModal" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="createModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-700">
                
                <div class="bg-slate-800/50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-700">
                    <div class="sm:flex sm:items-center justify-between">
                        <h3 class="text-xl font-bold leading-6 text-white" id="modal-title">
                            Add New User
                        </h3>
                        <button @click="closeCreateModal" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-4">
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                                <input type="text" name="name" x-model="newUser.name" required class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                                <input type="email" name="email" x-model="newUser.email" required class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                                    <input type="text" name="username" x-model="newUser.username" required class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                                    <input type="password" name="password" x-model="newUser.password" required class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Role</label>
                                <select name="role" x-model="newUser.role" required class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                    <option value="runner">Runner</option>
                                    <option value="coach">Coach</option>
                                    <option value="admin">Admin</option>
                                    <option value="eo">Event Organizer</option>
                                </select>
                            </div>

                            <!-- Program Enrollment (Only if Runner) -->
                            <div x-show="newUser.role === 'runner'" x-transition>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Enroll in Program (Optional)</label>
                                <select name="program_id" x-model="newUser.program_id" class="w-full rounded-xl bg-slate-800 border-slate-700 text-white focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Select Program --</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->title }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-500 mt-1">User will be enrolled immediately.</p>
                            </div>

                        </div>
                    </div>
                    <div class="bg-slate-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create User
                        </button>
                        <button type="button" @click="closeCreateModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-600 shadow-sm px-4 py-2 bg-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
@endpush
