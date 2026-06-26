@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Profile Settings')

@push('styles')
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('vendor/lightgallery/dist/css/lightgallery.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/lightgallery/dist/css/lg-thumbnail.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/lightgallery/dist/css/lg-zoom.css') }}" rel="stylesheet">
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        
        /* Glassmorphism */
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
@endpush

@section('content')
<div class="min-h-screen text-slate-200 font-sans pb-20 pt-20">
    
    <!-- Hero / Banner Section -->
    <div class="relative h-64 md:h-80 w-full rounded-b-3xl overflow-hidden group" id="main_banner_container">
        <div class="absolute inset-0 bg-slate-900">
            @if($user->banner)
                <img id="main_banner_img" src="{{ asset('storage/' . $user->banner) }}" alt="Banner" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700">
            @else
                <div id="main_banner_placeholder" class="w-full h-full bg-gradient-to-r from-slate-900 to-slate-800 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#ccff00 1px, transparent 1px); background-size: 20px 20px;"></div>
                </div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent"></div>
        </div>
        
        <!-- Breadcrumb on Banner -->
        <div class="absolute top-6 left-6 md:left-10 z-10">
            <nav class="flex text-sm font-medium text-slate-400 mb-2">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route(auth()->user()->role . '.dashboard') }}" class="hover:text-neon transition-colors">Dashboard</a></li>
                    <li><span class="text-slate-600">/</span></li>
                    <li class="text-neon">Profile</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 md:px-8 -mt-24 relative z-20">
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between backdrop-blur-md" data-aos="fade-down">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="hover:text-white"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Profile Card & Stats -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Profile Card -->
                <div class="glass rounded-2xl p-6 text-center relative overflow-hidden" data-aos="fade-up">
                    <div class="relative inline-block mb-4 group/avatar" id="avatar_dropzone">
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-dark shadow-2xl overflow-hidden relative z-10 mx-auto bg-slate-800 cursor-pointer">
                            <img id="avatar_preview_img" src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : (str_starts_with($user->avatar, '/storage') ? asset(ltrim($user->avatar, '/')) : asset('storage/' . $user->avatar))) : asset('images/profile/profile.png') }}" alt="Avatar" class="w-full h-full object-cover group-hover/avatar:opacity-80 transition-opacity">
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover/avatar:opacity-100 transition-opacity flex flex-col items-center justify-center z-20">
                                <svg class="w-8 h-8 text-neon mb-1 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <span class="text-[10px] text-neon uppercase font-bold">Drop Photo</span>
                            </div>
                        </div>
                        <div class="absolute inset-0 rounded-full border-2 border-neon blur-md opacity-50 animate-pulse"></div>
                        <div class="absolute bottom-2 right-2 z-30 w-8 h-8 bg-neon rounded-full flex items-center justify-center text-dark shadow-lg cursor-pointer hover:scale-110 transition-transform" title="Change Avatar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </div>
                        <div id="avatar_spinner" class="hidden absolute inset-0 rounded-full bg-slate-950/80 z-30 flex items-center justify-center">
                            <div class="w-8 h-8 border-4 border-neon border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-white mb-1">{{ $user->name }}</h2>
                    <p class="text-neon text-sm font-medium uppercase tracking-wider mb-4">{{ ucfirst($user->role) }}</p>
                    
                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        @if($user->city)
                        <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $user->city->name }}
                        </span>
                        @endif
                        <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Joined {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-4 gap-2 border-t border-slate-700 pt-6">
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-white">{{ $user->wallet ? number_format($user->wallet->balance/1000, 0) . 'K' : '0' }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Wallet</p>
                        </div>
                        <div class="text-center border-l border-slate-700">
                            <h4 class="text-lg font-bold text-white">{{ $user->events_count ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Events</p>
                        </div>
                        <div class="text-center border-l border-slate-700">
                            <h4 class="text-lg font-bold text-white">{{ $user->followers_count ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Followers</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-neon">{{ ucfirst($user->package_tier ?? 'Free') }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Tier</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-3 mt-6">
                        <a href="{{ route('runner.profile.show', $user->username ?? $user->id) }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                            Runner Profile
                        </a>
                        <a href="{{ route('feed.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                            Runner Feed
                        </a>
                        <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                            Find Runner
                        </a>
                        @if(!empty($pacer))
                        <a href="{{ route('pacer.show', $pacer->seo_slug) }}" class="px-5 py-2.5 bg-neon text-dark rounded-xl text-sm font-black hover:bg-lime-400 transition-all shadow-lg shadow-neon/20">
                            Pacer Detail
                        </a>
                        @endif
                    </div>
                </div>

                <div class="glass rounded-2xl p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3" data-aos="fade-up" data-aos-delay="50">
                    <a href="{{ route('wallet.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all text-center w-full sm:w-auto">
                        Wallet
                    </a>
                    <a href="{{ route('wallet.index') }}#topup-form" class="px-5 py-2.5 bg-neon text-dark rounded-xl text-sm font-black hover:bg-lime-400 transition-all shadow-lg shadow-neon/20 text-center w-full sm:w-auto">
                        Top-up
                    </a>
                    <a href="{{ route('wallet.index') }}#withdraw-form" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all text-center w-full sm:w-auto">
                        Withdraw
                    </a>
                </div>

                <!-- Gallery Preview -->
                <div class="glass rounded-2xl p-6 {{ (!$user->profile_images || count($user->profile_images) === 0) ? 'hidden' : '' }}" id="sidebar_gallery_card" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center justify-between">
                        Gallery
                        <span id="sidebar_gallery_count" class="text-xs font-normal text-slate-500">{{ count($user->profile_images ?? []) }} Photos</span>
                    </h3>
                    <div class="grid grid-cols-3 gap-2" id="lightgallery">
                        @if($user->profile_images)
                            @foreach($user->profile_images as $image)
                                <a href="{{ asset('storage/' . $image) }}" data-src="{{ asset('storage/' . $image) }}" class="block aspect-square rounded-lg overflow-hidden group relative gallery-item">
                                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Column: Settings Form -->
            <div class="lg:col-span-8">
                <div class="glass rounded-2xl p-6 md:p-8" data-aos="fade-up" data-aos-delay="200">
                    
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-white">Edit Profile</h2>
                        <div class="flex gap-2">
                             <span class="w-3 h-3 rounded-full bg-red-500"></span>
                             <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                             <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        </div>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Hidden File Inputs Triggered by UI -->
                        <input type="file" name="avatar" id="avatar_input" class="hidden" accept="image/*">
                        
                        <!-- Section 1: Personal Info -->
                        <div class="space-y-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Personal Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Full Name</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" required>
                                    @error('name') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Email Address</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" required>
                                    @error('email') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Phone Number</label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                    @error('phone') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Date of Birth</label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                    @error('date_of_birth') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Gender</label>
                                    <select name="gender" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Weight (kg)</label>
                                    <input type="number" step="0.01" name="weight" value="{{ old('weight', $user->weight) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="e.g. 65.5">
                                    @error('weight') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Height (cm)</label>
                                    <input type="number" name="height" value="{{ old('height', $user->height) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="e.g. 175">
                                    @error('height') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">City</label>
                                    <select name="city_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                        <option value="">Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}, {{ $city->province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city_id') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            
                            <div class="space-y-2 mt-4">
                                <label class="text-xs font-bold text-slate-400 uppercase">Full Address</label>
                                <textarea name="address" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">{{ old('address', $user->address) }}</textarea>
                                @error('address') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Section 4: Running Profile (PB) -->
                        <div class="space-y-6 pt-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Running Profile</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">5K PB</label>
                                    <input type="text" name="pb_5k" value="{{ old('pb_5k', $user->pb_5k) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all font-mono" placeholder="00:25:00">
                                    @error('pb_5k') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">10K PB</label>
                                    <input type="text" name="pb_10k" value="{{ old('pb_10k', $user->pb_10k) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all font-mono" placeholder="00:55:00">
                                    @error('pb_10k') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Half Marathon PB</label>
                                    <input type="text" name="pb_hm" value="{{ old('pb_hm', $user->pb_hm) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all font-mono" placeholder="02:00:00">
                                    @error('pb_hm') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Full Marathon PB</label>
                                    <input type="text" name="pb_fm" value="{{ old('pb_fm', $user->pb_fm) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all font-mono" placeholder="04:30:00">
                                    @error('pb_fm') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Pacer Activation -->
                        <div class="mt-8 pt-6 border-t border-slate-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-neon text-sm font-bold uppercase tracking-wider">Join Pacer Program</h3>
                                    <p class="text-xs text-slate-400 mt-1">Activate your profile to be listed in the Pacer Hub.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_pacer" value="0">
                                    <input type="checkbox" name="is_pacer" value="1" class="sr-only peer" {{ old('is_pacer', $user->is_pacer) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-neon/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-neon"></div>
                                </label>
                            </div>
                        </div>

                        @if($pacer)
                        <!-- Section 5: Pacer & Portfolio -->
                        <div class="space-y-6 pt-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Pacer & Portfolio</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Nickname</label>
                                    <input type="text" name="pacer_nickname" value="{{ old('pacer_nickname', $pacer->nickname) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                    @error('pacer_nickname') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Category</label>
                                    <select name="pacer_category" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                        <option value="">Select Category</option>
                                        <option value="10K" {{ old('pacer_category', $pacer->category) == '10K' ? 'selected' : '' }}>10K</option>
                                        <option value="HM (21K)" {{ old('pacer_category', $pacer->category) == 'HM (21K)' ? 'selected' : '' }}>HM (21K)</option>
                                        <option value="FM (42K)" {{ old('pacer_category', $pacer->category) == 'FM (42K)' ? 'selected' : '' }}>FM (42K)</option>
                                    </select>
                                    @error('pacer_category') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Target Pace</label>
                                    <input type="text" name="pacer_pace" value="{{ old('pacer_pace', $pacer->pace) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all font-mono" placeholder="05:30/km">
                                    @error('pacer_pace') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">WhatsApp</label>
                                    <input type="text" name="pacer_whatsapp" value="{{ old('pacer_whatsapp', $pacer->whatsapp) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                    @error('pacer_whatsapp') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase">Short Bio</label>
                                <textarea name="pacer_bio" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">{{ old('pacer_bio', $pacer->bio) }}</textarea>
                                @error('pacer_bio') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase">Tags</label>
                                <input type="text" name="pacer_tags" value="{{ old('pacer_tags', $pacer->tags ? implode(', ', $pacer->tags) : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="trail, endurance, marathon">
                                @error('pacer_tags') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase">Race Portfolio</label>
                                <textarea name="pacer_race_portfolio" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Contoh: Jakarta Marathon, Borobudur Marathon, Bali Marathon">{{ old('pacer_race_portfolio', $pacer->race_portfolio ? implode(', ', $pacer->race_portfolio) : '') }}</textarea>
                                @error('pacer_race_portfolio') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @endif

                        <!-- Section 2: Media -->
                        <div class="space-y-6 pt-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Media & Customization</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Banner Image</label>
                                    <div id="banner_dropzone" class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-neon transition-colors cursor-pointer relative group/banner bg-slate-900/50">
                                        <input type="file" name="banner" id="banner_input" class="hidden" accept="image/*">
                                        <div class="space-y-2" id="banner_dropzone_content">
                                            <svg class="w-8 h-8 text-slate-500 mx-auto group-hover/banner:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            <span class="text-sm text-slate-400 block font-bold">Drag and drop or click to upload banner</span>
                                            <span class="text-xs text-slate-500 block">Recommended: 1200x400px (Max 5MB)</span>
                                        </div>
                                        <div id="banner_spinner" class="hidden absolute inset-0 bg-slate-950/80 rounded-xl flex items-center justify-center">
                                            <div class="w-8 h-8 border-4 border-neon border-t-transparent rounded-full animate-spin"></div>
                                        </div>
                                    </div>
                                    @error('banner') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Gallery Images (Max 3)</label>
                                    
                                    <div id="gallery_preview_list" class="grid grid-cols-3 gap-4 mb-4">
                                        @if($user->profile_images)
                                            @foreach($user->profile_images as $image)
                                                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-700 group/gallery-item" data-image-path="{{ $image }}">
                                                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover">
                                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover/gallery-item:opacity-100 transition-opacity flex items-center justify-center">
                                                        <button type="button" onclick="deleteGalleryImage('{{ $image }}')" class="p-2 bg-red-500 hover:bg-red-600 rounded-full text-white transition-all transform hover:scale-110 shadow-lg" title="Delete Image">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <div id="gallery_dropzone" class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-neon transition-colors cursor-pointer relative group/gallery bg-slate-900/50">
                                        <input type="file" id="gallery_input" class="hidden" accept="image/*">
                                        <div class="space-y-2" id="gallery_dropzone_content">
                                            <svg class="w-8 h-8 text-slate-500 mx-auto group-hover/gallery:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                            <span class="text-sm text-slate-400 block font-bold">Drag and drop or click to upload photos</span>
                                            <span id="gallery_upload_status" class="text-xs text-slate-500 block">Max 3 images (Max 5MB each)</span>
                                        </div>
                                        <div id="gallery_spinner" class="hidden absolute inset-0 bg-slate-950/80 rounded-xl flex items-center justify-center">
                                            <div class="w-8 h-8 border-4 border-neon border-t-transparent rounded-full animate-spin"></div>
                                        </div>
                                    </div>
                                    @error('profile_images.*') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Social Media -->
                        <div class="space-y-6 pt-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Social Media</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Strava URL</label>
                                    <input type="url" name="strava_url" value="{{ old('strava_url', $user->strava_url) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="https://www.strava.com/athletes/...">
                                    @error('strava_url') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Instagram URL</label>
                                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $user->instagram_url) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="https://instagram.com/...">
                                    @error('instagram_url') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Facebook URL</label>
                                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $user->facebook_url) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="https://facebook.com/...">
                                    @error('facebook_url') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">TikTok URL</label>
                                    <input type="url" name="tiktok_url" value="{{ old('tiktok_url', $user->tiktok_url) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="https://tiktok.com/@...">
                                    @error('tiktok_url') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6 pt-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Bank Account</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Bank</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Nama Pemilik Rekening</label>
                                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Nomor Rekening</label>
                                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Security -->
                        <div class="space-y-6 pt-6">
                            <h3 class="text-neon text-sm font-bold uppercase tracking-wider border-b border-slate-700 pb-2 mb-4">Security</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">New Password</label>
                                    <input type="password" name="password" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Leave empty to keep current">
                                    @error('password') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all" placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-8 flex justify-end gap-4 border-t border-slate-700 mt-8">
                            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="px-6 py-3 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-800 transition-colors font-bold">
                                Cancel
                            </a>
                            <button type="submit" class="px-8 py-3 rounded-xl bg-neon hover:bg-lime-400 text-black font-black shadow-lg shadow-neon/20 transition-all transform hover:scale-105">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/lightgallery/dist/lightgallery.min.js') }}"></script>
    <script src="{{ asset('vendor/lightgallery/dist/plugins/thumbnail/lg-thumbnail.min.js') }}"></script>
    <script src="{{ asset('vendor/lightgallery/dist/plugins/zoom/lg-zoom.min.js') }}"></script>
    <script>
        // Initialize LightGallery
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('lightgallery')) {
                lightGallery(document.getElementById('lightgallery'), {
                    plugins: [lgThumbnail, lgZoom],
                    speed: 500,
                    thumbnail: true
                });
            }
        });

        // Modern Drag and Drop AJAX Upload Handlers
        const csrfToken = document.querySelector('input[name="_token"]')?.value;

        // Custom Flash Message Toast
        function showFlashMessage(message) {
            let container = document.getElementById('dynamic_flash_container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'dynamic_flash_container';
                container.className = 'fixed top-24 right-6 z-50 space-y-3 pointer-events-none max-w-sm';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center gap-3 backdrop-blur-md shadow-2xl transition-all duration-300 transform translate-x-12 opacity-0 pointer-events-auto';
            toast.innerHTML = `
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="text-sm font-medium">${message}</span>
                <button class="ml-auto hover:text-white flex-shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-12', 'opacity-0'), 10);

            const removeToast = () => {
                toast.classList.add('translate-x-12', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            };

            toast.querySelector('button').addEventListener('click', removeToast);
            setTimeout(removeToast, 4000);
        }

        // Generic setup drag & drop handler
        function setupDragAndDrop(dropzoneEl, fileInputEl, onFileSelect) {
            if (!dropzoneEl || !fileInputEl) return;

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzoneEl.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzoneEl.addEventListener(eventName, () => {
                    dropzoneEl.classList.add('border-neon', 'bg-neon/5');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzoneEl.addEventListener(eventName, () => {
                    dropzoneEl.classList.remove('border-neon', 'bg-neon/5');
                }, false);
            });

            dropzoneEl.addEventListener('drop', e => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    onFileSelect(files[0]);
                }
            });

            dropzoneEl.addEventListener('click', e => {
                if (e.target !== fileInputEl && !e.target.closest('button') && !e.target.closest('input')) {
                    fileInputEl.click();
                }
            });

            fileInputEl.addEventListener('change', e => {
                if (e.target.files && e.target.files.length > 0) {
                    onFileSelect(e.target.files[0]);
                }
            });
        }

        // Setup Avatar Drag & Drop
        setupDragAndDrop(
            document.getElementById('avatar_dropzone'),
            document.getElementById('avatar_input'),
            function(file) {
                const spinner = document.getElementById('avatar_spinner');
                spinner.classList.remove('hidden');

                const formData = new FormData();
                formData.append('avatar', file);

                fetch("{{ route('profile.upload-avatar') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('hidden');
                    if (data.success) {
                        document.getElementById('avatar_preview_img').src = data.url;
                        showFlashMessage('Foto profil berhasil diperbarui!');
                    } else {
                        alert(data.message || 'Gagal mengunggah foto profil.');
                    }
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengunggah foto profil.');
                });
            }
        );

        // Setup Banner Drag & Drop
        setupDragAndDrop(
            document.getElementById('banner_dropzone'),
            document.getElementById('banner_input'),
            function(file) {
                const spinner = document.getElementById('banner_spinner');
                spinner.classList.remove('hidden');

                const formData = new FormData();
                formData.append('banner', file);

                fetch("{{ route('profile.upload-banner') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('hidden');
                    if (data.success) {
                        const mainBannerImg = document.getElementById('main_banner_img');
                        if (mainBannerImg) {
                            mainBannerImg.src = data.url;
                        } else {
                            const placeholder = document.getElementById('main_banner_placeholder');
                            if (placeholder) {
                                const newImg = document.createElement('img');
                                newImg.id = 'main_banner_img';
                                newImg.src = data.url;
                                newImg.alt = 'Banner';
                                newImg.className = 'w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700';
                                placeholder.parentNode.replaceChild(newImg, placeholder);
                            }
                        }
                        showFlashMessage('Foto banner berhasil diperbarui!');
                    } else {
                        alert(data.message || 'Gagal mengunggah banner.');
                    }
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengunggah banner.');
                });
            }
        );

        // Setup Gallery Drag & Drop
        setupDragAndDrop(
            document.getElementById('gallery_dropzone'),
            document.getElementById('gallery_input'),
            function(file) {
                const spinner = document.getElementById('gallery_spinner');
                spinner.classList.remove('hidden');

                const formData = new FormData();
                formData.append('file', file);

                fetch("{{ route('profile.upload-gallery') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('hidden');
                    if (data.success) {
                        appendImageToGalleryPreview(data.image, data.url);
                        refreshSidebarGallery(data.image, data.url, 'add');
                        showFlashMessage('Foto berhasil ditambahkan ke galeri!');
                    } else {
                        alert(data.message || 'Gagal mengunggah foto galeri.');
                    }
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengunggah foto galeri.');
                });
            }
        );

        function appendImageToGalleryPreview(imagePath, imageUrl) {
            const list = document.getElementById('gallery_preview_list');
            const wrapper = document.createElement('div');
            wrapper.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate-700 group/gallery-item';
            wrapper.setAttribute('data-image-path', imagePath);
            wrapper.innerHTML = `
                <img src="${imageUrl}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover/gallery-item:opacity-100 transition-opacity flex items-center justify-center">
                    <button type="button" onclick="deleteGalleryImage('${imagePath}')" class="p-2 bg-red-500 hover:bg-red-600 rounded-full text-white transition-all transform hover:scale-110 shadow-lg" title="Delete Image">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            `;
            list.appendChild(wrapper);
        }

        window.deleteGalleryImage = function(imagePath) {
            if (!confirm('Apakah Anda yakin ingin menghapus foto ini dari galeri?')) {
                return;
            }

            const spinner = document.getElementById('gallery_spinner');
            spinner.classList.remove('hidden');

            fetch("{{ route('profile.delete-gallery') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ image: imagePath })
            })
            .then(response => response.json())
            .then(data => {
                spinner.classList.add('hidden');
                if (data.success) {
                    const item = document.querySelector(`[data-image-path="${imagePath}"]`);
                    if (item) {
                        item.remove();
                    }
                    refreshSidebarGallery(imagePath, null, 'delete');
                    showFlashMessage('Foto berhasil dihapus dari galeri!');
                } else {
                    alert(data.message || 'Gagal menghapus foto galeri.');
                }
            })
            .catch(error => {
                spinner.classList.add('hidden');
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus foto.');
            });
        };

        function refreshSidebarGallery(imagePath, imageUrl, action) {
            const card = document.getElementById('sidebar_gallery_card');
            const container = document.getElementById('lightgallery');
            const countEl = document.getElementById('sidebar_gallery_count');
            if (!card || !container) return;

            if (action === 'delete') {
                const item = container.querySelector(`[data-src*="${imagePath}"]`);
                if (item) {
                    item.remove();
                }
                const items = container.querySelectorAll('.gallery-item');
                const count = items.length;
                if (countEl) countEl.textContent = `${count} Photos`;
                if (count === 0) {
                    card.classList.add('hidden');
                }
            } else if (action === 'add') {
                card.classList.remove('hidden');
                const newLink = document.createElement('a');
                newLink.href = imageUrl;
                newLink.setAttribute('data-src', imageUrl);
                newLink.className = 'block aspect-square rounded-lg overflow-hidden group relative gallery-item';
                newLink.innerHTML = `
                    <img src="${imageUrl}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                `;
                container.appendChild(newLink);
                const count = container.querySelectorAll('.gallery-item').length;
                if (countEl) countEl.textContent = `${count} Photos`;
            }

            if (window.lightGallery && container) {
                try {
                    const lgInstance = window.lgData[container.getAttribute('lg-uid')];
                    if (lgInstance) {
                        lgInstance.destroy(true);
                    }
                } catch(e) {}
                lightGallery(container, {
                    plugins: [lgThumbnail, lgZoom],
                    speed: 500,
                    thumbnail: true
                });
            }
        }
    </script>
@endpush
