@extends('layouts.pacerhub')

@section('title', 'Edit Event')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script>
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                }
            }
        }
    </script>
@endpush

@section('content')
<div id="eo-edit-event-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header Section -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('eo.events.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Master Events</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex justify-between items-end">
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                EDIT <span class="text-yellow-400">EVENT</span>
            </h1>
            <a href="{{ route('eo.events.show', $event) }}" class="text-sm font-bold text-yellow-400 hover:text-white transition-colors flex items-center gap-1">
                View Detail <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8 relative z-10">
        <form action="{{ route('eo.events.update', $event) }}" method="POST" enctype="multipart/form-data" id="eventForm" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="border-b border-slate-700 pb-8">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">1</span>
                    Basic Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $event->name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" required>
                        @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Hardcoded Template</label>
                        <input type="text" name="hardcoded" value="{{ old('hardcoded', $event->hardcoded) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="misal: latbarkamis">
                        @error('hardcoded') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Template</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="modern-dark" class="peer sr-only" {{ old('template', $event->template) == 'modern-dark' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-800 h-24 rounded-lg mb-3 border border-slate-700 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-gradient-to-br from-slate-900 via-slate-800 to-black relative">
                                            <div class="absolute top-2 left-2 w-8 h-2 bg-yellow-400/50 rounded-sm"></div>
                                            <div class="absolute top-6 left-2 w-16 h-2 bg-slate-700 rounded-sm"></div>
                                            <div class="absolute bottom-2 right-2 w-6 h-6 rounded-full bg-yellow-400/20 border border-yellow-400"></div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Modern Dark</h4>
                                    <p class="text-xs text-slate-400">Desain gelap, elegan, dengan aksen neon. Cocok untuk event malam atau premium.</p>
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <div class="bg-yellow-400 rounded-full p-1">
                                        <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="light-clean" class="peer sr-only" {{ old('template', $event->template) == 'light-clean' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-200 h-24 rounded-lg mb-3 border border-slate-300 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-gradient-to-br from-white via-slate-50 to-slate-100 relative">
                                            <div class="absolute top-2 left-2 w-8 h-2 bg-blue-500/50 rounded-sm"></div>
                                            <div class="absolute top-6 left-2 w-16 h-2 bg-slate-300 rounded-sm"></div>
                                            <div class="absolute bottom-2 right-2 w-6 h-6 rounded-full bg-blue-500/20 border border-blue-500"></div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Light Clean</h4>
                                    <p class="text-xs text-slate-400">Tampilan terang, bersih, dan profesional. Cocok untuk fun run atau event siang hari.</p>
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <div class="bg-yellow-400 rounded-full p-1">
                                        <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="simple-minimal" class="peer sr-only" {{ old('template', $event->template) == 'simple-minimal' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-800 h-24 rounded-lg mb-3 border border-slate-700 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-slate-900 relative flex flex-col items-center justify-center gap-2">
                                            <div class="w-12 h-2 bg-slate-700 rounded-sm"></div>
                                            <div class="w-20 h-2 bg-slate-700 rounded-sm"></div>
                                            <div class="w-16 h-2 bg-slate-700 rounded-sm"></div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Simple Minimal</h4>
                                    <p class="text-xs text-slate-400">Fokus pada konten, tanpa banyak ornamen. Ringan dan cepat dimuat.</p>
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <div class="bg-yellow-400 rounded-full p-1">
                                        <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('template') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Slug (SEO URL)</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-700 bg-slate-800 text-slate-400 text-sm">
                                {{ config('app.url') }}/events/
                            </span>
                            <input type="text" name="slug" value="{{ old('slug', $event->slug) }}" class="flex-1 bg-slate-900 border border-slate-700 rounded-r-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                            <a href="{{ config('app.url') }}/events/{{ old('slug', $event->slug) }}" target="_blank" class="inline-flex items-center px-4 rounded-r-xl border border-l-0 border-slate-700 bg-slate-800 text-slate-400 text-sm">
                                View
                            </a>
                        </div>
                        @error('slug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Short Description</label>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="short_description_editor"></div>
                            <textarea name="short_description" id="short_description" class="hidden">{{ old('short_description', $event->short_description) }}</textarea>
                        </div>
                        @error('short_description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Full Description</label>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="full_description_editor"></div>
                            <textarea name="full_description" id="full_description" class="hidden">{{ old('full_description', $event->full_description) }}</textarea>
                        </div>
                        @error('full_description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Terms & Conditions</label>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="terms_and_conditions_editor"></div>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="hidden">{{ old('terms_and_conditions', $event->terms_and_conditions) }}</textarea>
                        </div>
                        @error('terms_and_conditions') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Platform Fee (Per Participant)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500 text-sm">Rp</span>
                            <input type="number" name="platform_fee" value="{{ old('platform_fee', $event->platform_fee) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="0">
                        </div>
                        <p class="text-slate-500 text-xs mt-1">Biaya tambahan yang dikenakan per peserta (masuk ke Platform).</p>
                        @error('platform_fee') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Date & Location -->
            <div class="border-b border-slate-700 pb-8">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">2</span>
                    Date & Location
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Start Date & Time <span class="text-red-400">*</span></label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at', $event->start_at ? $event->start_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]" required>
                        @error('start_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">End Date & Time</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        @error('end_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Event Location <span class="text-red-400">*</span></label>
                    <div class="relative mb-4">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="location_search" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="Search location...">
                    </div>
                    
                    <div id="map" class="w-full h-80 rounded-xl border border-slate-600 mb-4 z-0"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Venue Name</label>
                            <input type="text" name="location_name" id="location_name" value="{{ old('location_name', $event->location_name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Full Address</label>
                            <input type="text" name="location_address" id="location_address" value="{{ old('location_address', $event->location_address) }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                        </div>
                        <input type="hidden" name="location_lat" id="location_lat" value="{{ old('location_lat', $event->location_lat) }}">
                        <input type="hidden" name="location_lng" id="location_lng" value="{{ old('location_lng', $event->location_lng) }}">
                    </div>
                </div>
            </div>

            <!-- Race Categories (New) -->
            <div class="border-b border-slate-700 pb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">3</span>
                        Race Categories
                    </h3>
                    <button type="button" onclick="addCategory()" class="px-4 py-2 rounded-lg bg-slate-800 text-yellow-400 border border-yellow-500/30 hover:bg-slate-700 hover:border-yellow-400 transition-all text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Category
                    </button>
                </div>
                
                <div id="categories_container" class="space-y-4">
                    <!-- Categories will be added here -->
                </div>
                <div id="empty_categories_msg" class="text-center py-8 border-2 border-dashed border-slate-800 rounded-xl hidden">
                    <p class="text-slate-500 mb-2">No categories added yet.</p>
                    <p class="text-xs text-slate-600">Click "Add Category" to create race categories.</p>
                </div>
                @error('categories') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Add-ons -->
            <div class="border-b border-slate-700 pb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">4</span>
                        Add-ons
                    </h3>
                    <button type="button" onclick="addAddon()" class="px-4 py-2 rounded-lg bg-slate-800 text-yellow-400 border border-yellow-500/30 hover:bg-slate-700 hover:border-yellow-400 transition-all text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Add-on
                    </button>
                </div>
                <div id="addons_container" class="space-y-4">
                    <!-- Add-ons will be added here -->
                </div>
                <div id="empty_addons_msg" class="text-center py-8 border-2 border-dashed border-slate-800 rounded-xl">
                    <p class="text-slate-500 mb-2">Belum ada add-on.</p>
                    <p class="text-xs text-slate-600">Klik \"Add Add-on\" untuk menambahkan opsi tambahan.</p>
                </div>
                @error('addons') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Premium Amenities -->
            @include('eo.events.partials.premium-amenities', ['event' => $event])

            <!-- Media & Branding -->
            <div class="border-b border-slate-700 pb-8">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">6</span>
                    Media & Branding
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Hero Image</label>
                        <div class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-yellow-400 transition-colors cursor-pointer relative" onclick="document.getElementById('hero_image').click()">
                            <input type="file" name="hero_image" id="hero_image" class="hidden" accept="image/*" onchange="previewImage(this, 'hero_preview')">
                            
                            <!-- Current/Preview -->
                            <div id="hero_preview" class="{{ $event->hero_image ? '' : 'hidden' }} mb-2">
                                <img src="{{ $event->hero_image ? asset('storage/' . $event->hero_image) : '' }}" class="max-h-40 mx-auto rounded-lg shadow-lg">
                            </div>
                            
                            <div id="hero_placeholder" class="{{ $event->hero_image ? 'hidden' : '' }}">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click to replace</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Logo</label>
                        <div class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-yellow-400 transition-colors cursor-pointer relative" onclick="document.getElementById('logo_image').click()">
                            <input type="file" name="logo_image" id="logo_image" class="hidden" accept="image/*" onchange="previewImage(this, 'logo_preview')">
                            
                            <div id="logo_preview" class="{{ $event->logo_image ? '' : 'hidden' }} mb-2">
                                <img src="{{ $event->logo_image ? asset('storage/' . $event->logo_image) : '' }}" class="max-h-40 mx-auto rounded-lg shadow-lg">
                            </div>
                            
                            <div id="logo_placeholder" class="{{ $event->logo_image ? 'hidden' : '' }}">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click to replace</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4 pt-4">
                <a href="{{ route('eo.events.index') }}" class="px-6 py-3 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-800 transition-colors font-bold">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black shadow-lg shadow-yellow-500/20 transition-all transform hover:scale-105">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template for Category Item -->
<template id="category-template">
    <div class="category-item bg-slate-800/50 border border-slate-700 rounded-xl p-4 relative group hover:border-slate-500 transition-colors">
        <button type="button" class="remove-category absolute top-4 right-4 text-slate-500 hover:text-red-400 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </button>
        <input type="hidden" class="cat-id">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
                <label class="block text-xs font-medium text-slate-400 mb-1">Category Name <span class="text-red-400">*</span></label>
                <input type="text" class="cat-name w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="e.g. 10K Open" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400 mb-1">Distance (KM)</label>
                <input type="number" step="0.1" class="cat-distance w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="10">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400 mb-1">Quota</label>
                <input type="number" class="cat-quota w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="1000">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400 mb-1">Price (IDR)</label>
                <input type="number" class="cat-price w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="150000">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400 mb-1">COT (Mins)</label>
                <input type="number" class="cat-cot w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="120">
            </div>
        </div>
    </div>
</template>

<!-- Template for Addon Item -->
<template id="addon-template">
    <div class="addon-item bg-slate-800/50 border border-slate-700 rounded-xl p-4 relative group hover:border-slate-500 transition-colors">
        <button type="button" class="remove-addon absolute top-4 right-4 text-slate-500 hover:text-red-400 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6">
                <label class="block text-xs font-medium text-slate-400 mb-1">Addon Name <span class="text-red-400">*</span></label>
                <input type="text" class="addon-name w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="e.g. Jersey Finisher" required>
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-medium text-slate-400 mb-1">Price (IDR)</label>
                <input type="number" class="addon-price w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="150000">
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Initialize Editor
    const commonConfig = {
        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
    };

    // Short Description
    ClassicEditor
        .create(document.querySelector('#short_description_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('short_description', $event->short_description) !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#short_description').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    // Full Description
    ClassicEditor
        .create(document.querySelector('#full_description_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('full_description', $event->full_description) !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#full_description').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    // Terms and Conditions
    ClassicEditor
        .create(document.querySelector('#terms_and_conditions_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('terms_and_conditions', $event->terms_and_conditions) !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#terms_and_conditions').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    // Categories Logic
    let categoryIndex = 0;
    const container = document.getElementById('categories_container');
    const emptyMsg = document.getElementById('empty_categories_msg');
    const template = document.getElementById('category-template');

    function addCategory(data = null) {
        emptyMsg.classList.add('hidden');
        
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.category-item');
        
        // Set names with index
        const inputs = {
            'cat-id': 'id',
            'cat-name': 'name',
            'cat-distance': 'distance_km',
            'cat-quota': 'quota',
            'cat-price': 'price_regular',
            'cat-cot': 'cutoff_minutes'
        };

        for (const [cls, name] of Object.entries(inputs)) {
            const input = item.querySelector('.' + cls);
            input.name = `categories[${categoryIndex}][${name}]`;
            if (data && data[name]) input.value = data[name];
        }

        // Remove button
        item.querySelector('.remove-category').onclick = function() {
            item.remove();
            if (container.children.length === 0) {
                emptyMsg.classList.remove('hidden');
            }
        };

        container.appendChild(item);
        categoryIndex++;
    }

    // Load existing categories
    const existingCategories = @json($event->categories);
    if (existingCategories && existingCategories.length > 0) {
        existingCategories.forEach(cat => addCategory(cat));
    } else {
        emptyMsg.classList.remove('hidden');
    }

    // Addons Logic
    let addonIndex = 0;
    const addonsContainer = document.getElementById('addons_container');
    const emptyAddonsMsg = document.getElementById('empty_addons_msg');
    const addonTemplate = document.getElementById('addon-template');

    function addAddon(data = null) {
        emptyAddonsMsg.classList.add('hidden');
        
        const clone = addonTemplate.content.cloneNode(true);
        const item = clone.querySelector('.addon-item');
        
        // Set names with index
        const inputs = {
            'addon-name': 'name',
            'addon-price': 'price'
        };
        
        for (const [cls, field] of Object.entries(inputs)) {
            const input = item.querySelector(`.${cls}`);
            input.name = `addons[${addonIndex}][${field}]`;
            if (data) input.value = data[field];
        }

        // Remove button
        item.querySelector('.remove-addon').addEventListener('click', function() {
            item.remove();
            if (addonsContainer.children.length === 0) {
                emptyAddonsMsg.classList.remove('hidden');
            }
        });

        addonsContainer.appendChild(item);
        addonIndex++;
    }

    // Load existing addons
    const existingAddons = @json($event->addons ?? []);
    if (existingAddons && existingAddons.length > 0) {
        existingAddons.forEach(addon => addAddon(addon));
    }

    // Image Preview Helper
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(previewId.replace('preview', 'placeholder'));
        const img = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.classList.remove('hidden');
                if(placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Leaflet Map Logic
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $event->location_lat ?? -6.2088 }};
        const lng = {{ $event->location_lng ?? 106.8456 }};
        
        const map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        let marker = L.marker([lat, lng], {draggable: true}).addTo(map);
        
        function updateLocation(lat, lng) {
            document.getElementById('location_lat').value = lat;
            document.getElementById('location_lng').value = lng;
        }

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateLocation(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateLocation(e.latlng.lat, e.latlng.lng);
        });

        // Search
        const searchInput = document.getElementById('location_search');
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value;
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const newLat = parseFloat(data[0].lat);
                            const newLng = parseFloat(data[0].lon);
                            map.setView([newLat, newLng], 16);
                            marker.setLatLng([newLat, newLng]);
                            updateLocation(newLat, newLng);
                            document.getElementById('location_name').value = data[0].name || query;
                        }
                    });
            }
        });
    });
</script>
@endpush
