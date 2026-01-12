@extends('layouts.pacerhub')

@section('title', 'Create Event')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <style>
        .dropzone {
            background: rgba(15, 23, 42, 0.5);
            border: 2px dashed #334155;
            border-radius: 0.75rem;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem;
        }
        .dropzone:hover {
            border-color: #facc15;
        }
        .dropzone .dz-preview {
            background: transparent;
            margin: 0;
        }
        .dropzone .dz-preview .dz-image {
            border-radius: 0.5rem;
            width: 100px;
            height: 100px;
        }
        .dropzone .dz-preview .dz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .dropzone .dz-remove {
            position: absolute;
            top: -5px;
            right: -5px;
            z-index: 10;
            background: #ef4444;
            color: white;
            border-radius: 9999px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .dropzone .dz-remove:hover {
            text-decoration: none;
            background: #dc2626;
        }
    </style>
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
<div id="eo-create-event-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
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
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
            CREATE <span class="text-yellow-400">NEW EVENT</span>
        </h1>
    </div>

    <!-- Form Container -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8 relative z-10">
        <form action="{{ route('eo.events.store') }}" method="POST" enctype="multipart/form-data" id="eventForm" class="space-y-8">
            @csrf

            <!-- Basic Info -->
            <div class="border-b border-slate-700 pb-8">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">1</span>
                    Basic Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Jakarta Marathon 2025" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Hardcoded Template</label>
                        <input type="text" name="hardcoded" value="{{ old('hardcoded') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="misal: latbarkamis">
                        @error('hardcoded') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Template</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="modern-dark" class="peer sr-only" {{ old('template', 'modern-dark') == 'modern-dark' ? 'checked' : '' }}>
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
                                <input type="radio" name="template" value="light-clean" class="peer sr-only" {{ old('template') == 'light-clean' ? 'checked' : '' }}>
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
                                <input type="radio" name="template" value="simple-minimal" class="peer sr-only" {{ old('template') == 'simple-minimal' ? 'checked' : '' }}>
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

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="professional-city-run" class="peer sr-only" {{ old('template') == 'professional-city-run' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-800 h-24 rounded-lg mb-3 border border-slate-700 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-slate-50 relative flex flex-col items-center justify-center">
                                            <div class="absolute top-0 w-full h-8 bg-blue-600"></div>
                                            <div class="w-16 h-2 bg-slate-300 rounded-sm mt-4"></div>
                                            <div class="w-12 h-2 bg-slate-300 rounded-sm mt-1"></div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Professional City</h4>
                                    <p class="text-xs text-slate-400">Tampilan profesional untuk city run dan marathon besar.</p>
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <div class="bg-yellow-400 rounded-full p-1">
                                        <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="paolo-fest" class="peer sr-only" {{ old('template') == 'paolo-fest' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-800 h-24 rounded-lg mb-3 border border-slate-700 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-blue-600 relative flex flex-col items-center justify-center">
                                            <div class="absolute inset-0 bg-gradient-to-tr from-blue-700 to-orange-500 opacity-50"></div>
                                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                                <div class="w-8 h-8 bg-white rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Paolo Fest</h4>
                                    <p class="text-xs text-slate-400">Desain festif, cerah, dan modern. Cocok untuk event komunitas dan festival lari.</p>
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <div class="bg-yellow-400 rounded-full p-1">
                                        <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="paolo-fest-dark" class="peer sr-only" {{ old('template') == 'paolo-fest-dark' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-800 h-24 rounded-lg mb-3 border border-slate-700 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-gradient-to-br from-slate-950 via-blue-950 to-black relative flex flex-col items-center justify-center">
                                            <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/30 via-slate-900/0 to-fuchsia-500/20"></div>
                                            <div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center border border-blue-400/30">
                                                <div class="w-8 h-8 bg-blue-400/70 rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Paolo Fest Dark</h4>
                                    <p class="text-xs text-slate-400">Versi gelap dengan aksen neon. Cocok untuk tampilan malam/premium.</p>
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
                            <input type="text" name="slug" value="{{ old('slug') }}" class="flex-1 bg-slate-900 border border-slate-700 rounded-r-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="jakarta-marathon-2025">
                        </div>
                        <p class="text-slate-500 text-xs mt-1">Leave empty to auto-generate from name.</p>
                        @error('slug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Short Description</label>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="short_description_editor"></div>
                            <textarea name="short_description" id="short_description" class="hidden">{{ old('short_description') }}</textarea>
                        </div>
                        @error('short_description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Full Description</label>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="full_description_editor"></div>
                            <textarea name="full_description" id="full_description" class="hidden">{{ old('full_description') }}</textarea>
                        </div>
                        @error('full_description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Terms & Conditions</label>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="terms_and_conditions_editor"></div>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="hidden">{{ old('terms_and_conditions') }}</textarea>
                        </div>
                        @error('terms_and_conditions') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Platform Fee (Per Participant)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500 text-sm">Rp</span>
                            <input type="number" name="platform_fee" value="{{ old('platform_fee', 0) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="0">
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
                        <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]" required>
                        @error('start_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">End Date & Time</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        @error('end_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Registration Open <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="datetime-local" name="registration_open_at" value="{{ old('registration_open_at') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to open immediately.</p>
                        @error('registration_open_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Registration Close <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="datetime-local" name="registration_close_at" value="{{ old('registration_close_at') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to close when event starts.</p>
                        @error('registration_close_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Event Location <span class="text-red-400">*</span></label>
                    <div class="relative mb-4">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="location_search" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="Search location (e.g. GBK Jakarta)...">
                    </div>
                    
                    <div id="map" class="w-full h-80 rounded-xl border border-slate-600 mb-4 z-0"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Venue Name</label>
                            <input type="text" name="location_name" id="location_name" value="{{ old('location_name') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm" placeholder="e.g. Gelora Bung Karno" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Full Address</label>
                            <input type="text" name="location_address" id="location_address" value="{{ old('location_address') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm" placeholder="Street address...">
                        </div>
                        <input type="hidden" name="location_lat" id="location_lat" value="{{ old('location_lat') }}">
                        <input type="hidden" name="location_lng" id="location_lng" value="{{ old('location_lng') }}">
                    </div>
                </div>
            </div>

            <!-- Add-ons -->
            <div class="border-b border-slate-700 pb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">3</span>
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
                    <p class="text-xs text-slate-600">Klik "Add Add-on" untuk menambahkan opsi tambahan.</p>
                </div>
                @error('addons') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Race Categories (New) -->
            <div class="border-b border-slate-700 pb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">4</span>
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
                <div id="empty_categories_msg" class="text-center py-8 border-2 border-dashed border-slate-800 rounded-xl">
                    <p class="text-slate-500 mb-2">No categories added yet.</p>
                    <p class="text-xs text-slate-600">Click "Add Category" to create race categories (e.g., 5K, 10K, FM).</p>
                </div>
                @error('categories') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Premium Amenities -->
            @include('eo.events.partials.premium-amenities', ['event' => new \App\Models\Event()])

            <!-- Media & Branding -->
            <div class="border-b border-slate-700 pb-8">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">6</span>
                    Media & Branding
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Hero Image <span class="text-red-400">*</span></label>
                        <div id="hero-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                            <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Image (16:9)</p>
                            </div>
                        </div>
                        <input type="hidden" name="hero_image" id="hero_image_input">
                        @error('hero_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Logo</label>
                        <div id="logo-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                            <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Logo (Square)</p>
                            </div>
                        </div>
                        <input type="hidden" name="logo_image" id="logo_image_input">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Event Gallery (Multiple, Drag to Reorder)</label>
                        <div id="gallery-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors min-h-[150px]">
                             <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Photos</p>
                            </div>
                        </div>
                        <div id="gallery-inputs"></div>
                        @error('gallery') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Sponsor Logos (Max 30, Drag to Reorder)</label>
                        <div id="sponsors-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors min-h-[150px]">
                             <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Logos</p>
                            </div>
                        </div>
                        <div id="sponsors-inputs"></div>
                        @error('sponsors') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('sponsors.*') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4 pt-4">
                <a href="{{ route('eo.events.index') }}" class="px-6 py-3 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-800 transition-colors font-bold">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black shadow-lg shadow-yellow-500/20 transition-all transform hover:scale-105">
                    Create Event
                </button>
            </div>

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

        </form>
    </div>
</div>

<!-- Template for Category Item -->
<template id="category-template">
    <div class="category-item bg-slate-800/50 border border-slate-700 rounded-xl p-4 relative group hover:border-slate-500 transition-colors">
        <button type="button" class="remove-category absolute top-4 right-4 text-slate-500 hover:text-red-400 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </button>
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
                <label class="block text-xs font-medium text-slate-400 mb-1">COT (Mins)</label>
                <input type="number" class="cat-cot w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="120">
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Early Price (IDR)</label>
                <input type="number" class="cat-price-early w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="150000">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Regular Price (IDR)</label>
                <input type="number" class="cat-price w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="150000">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Late Price (IDR)</label>
                <input type="number" class="cat-price-late w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="150000">
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Hadiah Juara 1</label>
                <input type="text" class="cat-prize-1 w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="Rp 5.000.000 / Trofi / dll">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Hadiah Juara 2</label>
                <input type="text" class="cat-prize-2 w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="Rp 3.000.000 / Trofi / dll">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Hadiah Juara 3</label>
                <input type="text" class="cat-prize-3 w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="Rp 1.500.000 / Trofi / dll">
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    Dropzone.autoDiscover = false;

    function initDropzone(id, inputName, maxFiles = 1, existingFiles = []) {
        const el = document.getElementById(id);
        if (!el) return;

        const container = document.getElementById(id.replace('-dropzone', '-inputs')) || el.parentNode; // Fallback
        
        // Ensure container for inputs exists if not provided
        if (id.includes('hero') || id.includes('logo')) {
            // For single files, we use a single hidden input that might already exist
            // But we will handle it dynamically
        }

        const dz = new Dropzone("#" + id, {
            url: "{{ route('eo.events.upload-media') }}",
            paramName: "file",
            maxFiles: maxFiles,
            maxFilesize: 5, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            params: {
                folder: id.includes('hero') ? 'events/hero' : (id.includes('logo') ? 'events/logo' : (id.includes('gallery') ? 'events/gallery' : 'events/sponsors'))
            },
            success: function(file, response) {
                if (response.success) {
                    file.serverPath = response.path; // Store path
                    addHiddenInput(inputName, response.path);
                } else {
                    this.removeFile(file);
                    alert('Upload failed');
                }
            },
            removedfile: function(file) {
                if (file.serverPath) {
                    removeHiddenInput(inputName, file.serverPath);
                }
                if (file.previewElement != null && file.previewElement.parentNode != null) {
                    file.previewElement.parentNode.removeChild(file.previewElement);
                }
                return this._updateMaxFilesReachedClass();
            },
            init: function() {
                const myDropzone = this;
                
                // Load existing files
                if (existingFiles && existingFiles.length > 0) {
                    existingFiles.forEach(path => {
                        // Mock file
                        const mockFile = { name: path.split('/').pop(), size: 12345, serverPath: path, accepted: true };
                        myDropzone.emit("addedfile", mockFile);
                        myDropzone.emit("thumbnail", mockFile, "{{ asset('storage') }}/" + path);
                        myDropzone.emit("complete", mockFile);
                        myDropzone.files.push(mockFile);
                        
                        addHiddenInput(inputName, path);
                    });
                }

                // Sortable
                if (maxFiles > 1) {
                    new Sortable(el, {
                        animation: 150,
                        ghostClass: 'bg-slate-800',
                        onEnd: function() {
                            // Reorder hidden inputs based on DOM order
                            reorderInputs(myDropzone, inputName);
                        }
                    });
                }
            }
        });
    }

    function addHiddenInput(name, value) {
        // For single file, replace value
        if (!name.includes('[]')) {
            let input = document.getElementById(name + '_input');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.id = name + '_input';
                document.getElementById('eventForm').appendChild(input);
            }
            input.value = value;
            return;
        }

        // For array
        const container = document.getElementById(name.replace('[]', '') + '-inputs');
        if (!container) return; // Should exist

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        input.className = 'dz-hidden-input'; // Marker
        input.dataset.path = value;
        container.appendChild(input);
    }

    function removeHiddenInput(name, value) {
        if (!name.includes('[]')) {
            const input = document.getElementById(name + '_input');
            if (input) input.value = '';
            return;
        }

        const container = document.getElementById(name.replace('[]', '') + '-inputs');
        if (!container) return;

        const input = container.querySelector(`input[value="${value}"]`);
        if (input) input.remove();
    }

    function reorderInputs(dropzoneInstance, inputName) {
        const container = document.getElementById(inputName.replace('[]', '') + '-inputs');
        if (!container) return;

        // Get all preview elements in current order
        const previews = dropzoneInstance.element.querySelectorAll('.dz-preview');
        
        // Clear container
        container.innerHTML = '';

        // Re-add inputs in order
        previews.forEach(preview => {
            // Find the file object corresponding to this preview
            // Dropzone doesn't link DOM to File object easily in reverse, 
            // but we can assume the preview element has the image src or we can use the file object if we can find it.
            // Actually, Sortable sorts the DOM elements.
            // We need to find which file corresponds to this DOM element.
            
            // Simpler approach: Dropzone attaches 'file' property to previewElement? No.
            // But we can iterate through dropzone.files and match previewElement.
            const file = dropzoneInstance.files.find(f => f.previewElement === preview);
            if (file && file.serverPath) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = inputName;
                input.value = file.serverPath;
                container.appendChild(input);
            }
        });
    }

    // Initialize Dropzones
    document.addEventListener("DOMContentLoaded", function() {
        initDropzone('hero-dropzone', 'hero_image', 1);
        initDropzone('logo-dropzone', 'logo_image', 1);
        initDropzone('gallery-dropzone', 'gallery[]', 10);
        initDropzone('sponsors-dropzone', 'sponsors[]', 30);
    });

    // Categories LogicCategories Logic
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
            'cat-name': 'name',
            'cat-distance': 'distance_km',
            'cat-quota': 'quota',
            'cat-price-early': 'price_early',
            'cat-price': 'price_regular',
            'cat-price-late': 'price_late',
            'cat-cot': 'cutoff_minutes'
        };

        for (const [cls, name] of Object.entries(inputs)) {
            const input = item.querySelector('.' + cls);
            input.name = `categories[${categoryIndex}][${name}]`;
            if (data && data[name] !== undefined && data[name] !== null) input.value = data[name];
        }

        // Handle prizes separately
        const prizeInputs = {
            'cat-prize-1': 1,
            'cat-prize-2': 2,
            'cat-prize-3': 3
        };

        for (const [cls, pIdx] of Object.entries(prizeInputs)) {
            const input = item.querySelector('.' + cls);
            input.name = `categories[${categoryIndex}][prizes][${pIdx}]`;
            if (data && data.prizes && data.prizes[pIdx] !== undefined && data.prizes[pIdx] !== null) {
                input.value = data.prizes[pIdx];
            }
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

    // Initialize with one empty category if none exist (optional, or wait for user)
    // addCategory(); 

    // CKEditor Init
    const commonConfig = {
        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
    };

    // Short Description
    ClassicEditor
        .create(document.querySelector('#short_description_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('short_description') !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#short_description').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    // Full Description
    ClassicEditor
        .create(document.querySelector('#full_description_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('full_description') !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#full_description').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    // Terms and Conditions
    ClassicEditor
        .create(document.querySelector('#terms_and_conditions_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('terms_and_conditions') !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#terms_and_conditions').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    // Image Preview
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

    // Sponsor Preview Logic
    function previewSponsors(input) {
        const preview = document.getElementById('sponsors_preview');
        const placeholder = document.getElementById('sponsors_placeholder');
        const count = document.getElementById('sponsors_count');
        
        preview.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            placeholder.classList.add('hidden');
            preview.classList.remove('hidden');
            count.textContent = input.files.length + ' files selected';
            
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'aspect-square bg-slate-800 rounded-lg overflow-hidden border border-slate-600 relative';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        } else {
            placeholder.classList.remove('hidden');
            preview.classList.add('hidden');
            count.textContent = '0 files selected';
        }
    }

    // Leaflet Map
    document.addEventListener('DOMContentLoaded', function() {
        const defaultLat = -6.2088;
        const defaultLng = 106.8456;
        
        const map = L.map('map').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        let marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
        
        function updateLocation(lat, lng) {
            document.getElementById('location_lat').value = lat;
            document.getElementById('location_lng').value = lng;
            
            // Reverse geocoding (optional, simple fetch)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if(data.display_name) {
                        document.getElementById('location_address').value = data.display_name;
                        // Try to get a short name
                        const name = data.name || data.address.building || data.address.suburb || '';
                        if(name && !document.getElementById('location_name').value) {
                            document.getElementById('location_name').value = name;
                        }
                    }
                });
        }

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateLocation(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateLocation(e.latlng.lat, e.latlng.lng);
        });

        // Search location
        const searchInput = document.getElementById('location_search');
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value;
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            map.setView([lat, lng], 16);
                            marker.setLatLng([lat, lng]);
                            updateLocation(lat, lng);
                            document.getElementById('location_name').value = data[0].name || query;
                        }
                    });
            }
        });
        
        // Initial update
        updateLocation(defaultLat, defaultLng);
    });
</script>
@endpush
