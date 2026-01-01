@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manage Users')

@section('content')
<div x-data="{ 
    showModal: false, 
    createModal: false,
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

    openModal(user) {
        // Clone user to avoid reference issues
        this.selectedUser = JSON.parse(JSON.stringify(user));
        
        // Fix storage path for avatar and banner
        if (this.selectedUser.avatar) {
            this.selectedUser.avatar = this.selectedUser.avatar.replace(/^storage\//, '');
        }
        if (this.selectedUser.banner) {
            this.selectedUser.banner = this.selectedUser.banner.replace(/^storage\//, '');
        }
        
        // Ensure bank_account exists
        let bank = this.selectedUser.bank_account || {};
        
        // Map nested bank fields to top-level properties for the form
        this.selectedUser.bank_name = bank.bank_name || '';
        this.selectedUser.bank_account_name = bank.account_name || '';
        this.selectedUser.bank_account_number = bank.account_number || '';
        
        // Format date for input
        if (this.selectedUser.date_of_birth) {
            this.selectedUser.date_of_birth = this.selectedUser.date_of_birth.split('T')[0];
        }
        
        // Ensure boolean for checkbox
        this.selectedUser.is_active = !!this.selectedUser.is_active;

        this.showModal = true;
    },
    closeModal() {
        this.showModal = false;
        this.selectedUser = null;
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
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-500 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" 
                    class="block w-full rounded-xl border-slate-700 bg-slate-800/50 text-white pl-10 pr-3 py-2.5 focus:border-blue-500 focus:ring-blue-500 placeholder-slate-500 sm:text-sm transition-all" 
                    placeholder="Search users...">
            </div>
            
            <select name="role" onchange="this.form.submit()" 
                class="rounded-xl border-slate-700 bg-slate-800/50 text-white py-2.5 pl-3 pr-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all cursor-pointer">
                <option value="all">All Roles</option>
                <option value="runner" {{ request('role') == 'runner' ? 'selected' : '' }}>Runners</option>
                <option value="coach" {{ request('role') == 'coach' ? 'selected' : '' }}>Coaches</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admins</option>
            </select>

            <select name="status" onchange="this.form.submit()" 
                class="rounded-xl border-slate-700 bg-slate-800/50 text-white py-2.5 pl-3 pr-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all cursor-pointer">
                <option value="all">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
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
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 font-bold">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-700/20 transition-colors group">
                        <td class="px-6 py-4 cursor-pointer" @click="openModal(@js($user))">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold overflow-hidden relative">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/'.preg_replace('/^storage\//', '', $user->avatar)) }}" 
                                            alt="{{ $user->name }}" 
                                            class="w-full h-full object-cover">
                                    @else
                                        {{ substr($user->name, 0, 1) }}
                                    @endif



                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold text-white group-hover:text-blue-400 transition-colors flex items-center gap-2">
                                        {{ $user->name }}
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->role === 'admin' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 
                                  ($user->role === 'coach' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 
                                  'bg-blue-500/10 text-blue-400 border border-blue-500/20') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->is_active ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-400' : 'bg-slate-400' }}"></span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                
                                @if(auth()->id() !== $user->id)
                                    <!-- Impersonate -->
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-colors" title="Login as {{ $user->name }}" onclick="return confirm('Are you sure you want to login as this user?')">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                        </button>
                                    </form>

                                    <!-- Toggle Status -->
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-lg hover:bg-slate-700 {{ $user->is_active ? 'text-green-400 hover:text-red-400' : 'text-slate-500 hover:text-green-400' }} transition-colors" 
                                            title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                            onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                                            @if($user->is_active)
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @endif
                                        </button>
                                    </form>
                                @endif
                                
                                <button @click="openModal(@js($user))" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors" title="Edit Profile">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-700/50">
            {{ $users->links() }}
        </div>
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

                <form x-bind:action="selectedUser ? '{{ url('admin/users') }}/' + selectedUser.id : '#'" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-4 py-5 sm:p-6 max-h-[70vh] overflow-y-auto">
                        <template x-if="selectedUser">
                            <div class="space-y-6">
                                
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

                                <!-- Personal Bests -->
                                <div>
                                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-t border-slate-700 pt-4">Personal Bests (HH:MM:SS)</h4>
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

                                <!-- Social Media -->
                                <div>
                                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-t border-slate-700 pt-4">Social Media</h4>
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

                                <!-- Bank Info -->
                                <div>
                                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-t border-slate-700 pt-4">Bank Information</h4>
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

                <!-- Wallet Management Section (Separate Form) -->
                <div class="bg-slate-800/30 border-t border-slate-700 px-4 py-5 sm:p-6">
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
                                        <template x-if="selectedUser && selectedUser.wallet && selectedUser.wallet.transactions && selectedUser.wallet.transactions.length > 0">
                                            <template x-for="trx in selectedUser.wallet.transactions" :key="trx.id">
                                                <tr>
                                                    <td class="px-3 py-2" x-text="new Date(trx.created_at).toLocaleDateString()"></td>
                                                    <td class="px-3 py-2 uppercase font-bold" x-text="trx.type" :class="trx.type === 'deposit' ? 'text-emerald-400' : 'text-red-400'"></td>
                                                    <td class="px-3 py-2 font-mono" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(trx.amount)"></td>
                                                    <td class="px-3 py-2" x-text="trx.status"></td>
                                                </tr>
                                            </template>
                                        </template>
                                        <template x-if="!selectedUser || !selectedUser.wallet || !selectedUser.wallet.transactions || selectedUser.wallet.transactions.length === 0">
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