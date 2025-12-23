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
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/profile.png') }}" alt="Avatar" class="w-full h-full object-cover">
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

                    <div class="grid grid-cols-3 gap-2 border-t border-slate-700 pt-6">
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-white">{{ $user->wallet ? number_format($user->wallet->balance/1000, 0) . 'K' : '0' }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Wallet</p>
                        </div>
                        <div class="text-center border-l border-r border-slate-700">
                            <h4 class="text-lg font-bold text-white">{{ $user->events_count ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Events</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-neon">{{ ucfirst($user->package_tier ?? 'Free') }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">Tier</p>
                        </div>
                    </div>
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

                        <!-- Section 3: Security -->
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