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
    <div class="relative h-64 md:h-80 w-full rounded-b-3xl overflow-hidden group">
        <div class="absolute inset-0 bg-slate-900">
            @if($user->banner)
                <img src="{{ asset('storage/' . $user->banner) }}" alt="Banner" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700">
            @else
                <div class="w-full h-full bg-gradient-to-r from-slate-900 to-slate-800 relative overflow-hidden">
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
                    <div class="relative inline-block mb-4">
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-dark shadow-2xl overflow-hidden relative z-10 mx-auto">
                            <img src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : (str_starts_with($user->avatar, '/storage') ? asset(ltrim($user->avatar, '/')) : asset('storage/' . $user->avatar))) : asset('images/profile/profile.png') }}" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute inset-0 rounded-full border-2 border-neon blur-md opacity-50 animate-pulse"></div>
                        <div class="absolute bottom-2 right-2 z-20 w-8 h-8 bg-neon rounded-full flex items-center justify-center text-dark shadow-lg cursor-pointer hover:scale-110 transition-transform" onclick="document.getElementById('avatar_input').click()" title="Change Avatar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
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
                        <a href="{{ route('runner.profile.show', $user->username) }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
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

                <div class="glass rounded-2xl p-4 flex items-center justify-center gap-3" data-aos="fade-up" data-aos-delay="50">
                    <a href="{{ route('wallet.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                        Wallet
                    </a>
                    <a href="{{ route('wallet.index') }}#topup-form" class="px-5 py-2.5 bg-neon text-dark rounded-xl text-sm font-black hover:bg-lime-400 transition-all shadow-lg shadow-neon/20">
                        Top-up
                    </a>
                    <a href="{{ route('wallet.index') }}#withdraw-form" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                        Withdraw
                    </a>
                </div>

                <!-- Gallery Preview -->
                @if($user->profile_images && count($user->profile_images) > 0)
                <div class="glass rounded-2xl p-6" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center justify-between">
                        Gallery
                        <span class="text-xs font-normal text-slate-500">{{ count($user->profile_images) }} Photos</span>
                    </h3>
                    <div class="grid grid-cols-3 gap-2" id="lightgallery">
                        @foreach($user->profile_images as $image)
                            <a href="{{ asset('storage/' . $image) }}" class="block aspect-square rounded-lg overflow-hidden group relative">
                                <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

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
                                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-4 text-center hover:border-neon transition-colors cursor-pointer" onclick="document.getElementById('banner_input').click()">
                                        <input type="file" name="banner" id="banner_input" class="hidden" accept="image/*">
                                        <span class="text-sm text-slate-400 block">Click to upload new banner</span>
                                        <span class="text-xs text-slate-500 block mt-1">Rec: 1200x400px (Max 5MB)</span>
                                    </div>
                                    @error('banner') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Gallery Images (Max 3)</label>
                                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-4 text-center hover:border-neon transition-colors cursor-pointer" onclick="document.getElementById('gallery_input').click()">
                                        <input type="file" name="profile_images[]" id="gallery_input" class="hidden" accept="image/*" multiple>
                                        <span class="text-sm text-slate-400 block">Click to upload photos</span>
                                        <span class="text-xs text-slate-500 block mt-1">Max 3 images (Max 5MB each)</span>
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

        // Avatar Preview (Optional Enhancement)
        document.getElementById('avatar_input').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                // You could add JS to update the image src immediately here
            }
        });
    </script>
@endpush
