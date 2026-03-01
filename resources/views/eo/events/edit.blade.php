@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Edit Event')

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
                    ...tailwind.config.theme.extend.colors.neon,
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
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    EDIT <span class="text-yellow-400">EVENT</span>
                </h1>
                <p class="text-slate-400 mt-2">Update event information, categories, and settings.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('eo.events.show', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center gap-2 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    Preview
                </a>
                <a href="{{ route('eo.events.participants', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center gap-2 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Participants
                </a>
                <a href="{{ route('eo.events.blast', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center gap-2 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Blast Email
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8 relative z-10">
        <form action="{{ route('eo.events.update', $event) }}" method="POST" enctype="multipart/form-data" id="eventForm" class="space-y-8">
            @csrf
            @method('PUT')
            <div id="removed_sponsors_container"></div>

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
                        <label class="block text-sm font-medium text-slate-300 mb-2">Tampilkan Promo Modal <span class="text-red-400">*</span></label>
                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="premium_amenities[promo_modal_enabled]" value="1" class="peer sr-only" {{ old('premium_amenities.promo_modal_enabled', ($event->premium_amenities ?? [])['promo_modal_enabled'] ?? 0) == '1' ? 'checked' : '' }} required>
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="ml-2 text-slate-300 group-hover:text-white transition-colors">Ya</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="premium_amenities[promo_modal_enabled]" value="0" class="peer sr-only" {{ old('premium_amenities.promo_modal_enabled', ($event->premium_amenities ?? [])['promo_modal_enabled'] ?? 0) == '0' ? 'checked' : '' }} required>
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="ml-2 text-slate-300 group-hover:text-white transition-colors">Tidak</span>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Jika aktif, modal promo akan muncul otomatis saat user membuka halaman event (hanya jika pendaftaran dibuka).</p>
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

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="professional-city-run" class="peer sr-only" {{ old('template', $event->template) == 'professional-city-run' ? 'checked' : '' }}>
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
                                <input type="radio" name="template" value="paolo-fest" class="peer sr-only" {{ old('template', $event->template) == 'paolo-fest' ? 'checked' : '' }}>
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
                                <input type="radio" name="template" value="paolo-fest-dark" class="peer sr-only" {{ old('template', $event->template) == 'paolo-fest-dark' ? 'checked' : '' }}>
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
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-slate-300">Custom Email Message (Ticket)</label>
                            <button type="button" onclick="previewEmail()" class="text-xs bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded-lg transition-colors flex items-center gap-1 border border-slate-600">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                Preview Email
                            </button>
                        </div>
                        <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                            <div id="custom_email_message_editor"></div>
                            <textarea name="custom_email_message" id="custom_email_message" class="hidden">{{ old('custom_email_message', $event->custom_email_message) }}</textarea>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Pesan ini akan muncul di email tiket peserta.</p>
                        <div class="mt-4 space-y-3">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Send Test Email</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="md:col-span-2">
                                    <input type="email" id="test_email_to" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="email tujuan test (contoh: test@domain.com)">
                                </div>
                                <div>
                                    <button type="button" id="sendTestEmailBtn" onclick="sendTestEmail()" class="w-full px-4 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-black font-bold transition-colors disabled:opacity-60 disabled:cursor-not-allowed">Send Test Email</button>
                                </div>
                            </div>
                            <div id="testEmailStatus" class="text-sm"></div>
                            <div id="testEmailRemaining" class="text-xs text-slate-500"></div>
                        </div>
                        @error('custom_email_message') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-4">Template Tiket Email</label>
                        <div class="flex flex-wrap items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="ticket_email_use_qr" value="1" class="peer sr-only" {{ old('ticket_email_use_qr', ($event->ticket_email_use_qr ?? true) ? '1' : '0') === '1' ? 'checked' : '' }} required>
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="text-slate-300 group-hover:text-white transition-colors">Gunakan QR Code</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="ticket_email_use_qr" value="0" class="peer sr-only" {{ old('ticket_email_use_qr', ($event->ticket_email_use_qr ?? true) ? '1' : '0') === '0' ? 'checked' : '' }}>
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="text-slate-300 group-hover:text-white transition-colors">Tanpa QR Code</span>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Jika dimatikan, email tiket tetap menampilkan nomor tiket tanpa QR.</p>
                        <p id="ticketEmailQrError" class="text-red-400 text-xs mt-1 hidden"></p>
                        @error('ticket_email_use_qr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-4 border border-slate-700 rounded-xl bg-slate-800/50 hover:bg-slate-800 transition-colors cursor-pointer group">
                            <input type="checkbox" name="is_instant_notification" value="1" class="w-5 h-5 rounded border-slate-600 bg-slate-700 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-0" {{ old('is_instant_notification', $event->is_instant_notification) ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">Instant Email Notification</span>
                                <span class="block text-xs text-slate-400 mt-0.5">Kirim email tiket secara cepat dengan batas maksimal 5 email/menit per event. Gunakan hanya untuk demo atau event kecil.</span>
                            </div>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-4">Daftar Peserta (Public)</label>
                        <div class="flex flex-wrap items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="show_participant_list" value="1" class="peer sr-only" {{ old('show_participant_list', $event->show_participant_list ?? true) ? 'checked' : '' }}>
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="text-slate-300 group-hover:text-white transition-colors">Tampilkan Daftar Peserta</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="show_participant_list" value="0" class="peer sr-only" {{ !old('show_participant_list', $event->show_participant_list ?? true) ? 'checked' : '' }}>
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="text-slate-300 group-hover:text-white transition-colors">Sembunyikan Daftar Peserta</span>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Mengatur apakah daftar peserta dapat dilihat oleh publik di halaman event.</p>
                        @error('show_participant_list') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Rate Limit Email (Non-Instant)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Ticket Email (per menit)</label>
                                <input type="number" min="1" max="10000" name="ticket_email_rate_limit_per_minute" value="{{ old('ticket_email_rate_limit_per_minute', $event->ticket_email_rate_limit_per_minute) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="Kosong = unlimited">
                                @error('ticket_email_rate_limit_per_minute') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Blast Email (per menit)</label>
                                <input type="number" min="1" max="10000" name="blast_email_rate_limit_per_minute" value="{{ old('blast_email_rate_limit_per_minute', $event->blast_email_rate_limit_per_minute) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="Kosong = unlimited">
                                @error('blast_email_rate_limit_per_minute') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="text-slate-500 text-xs mt-2">Ticket rate dipakai saat Instant dimatikan. Instant tetap mengikuti batas 5 email/menit. Blast akan mengikuti setting ini jika diisi.</p>
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

                    <!-- WhatsApp Configuration -->
                    <div class="md:col-span-2 mt-4 pt-6 border-t border-slate-700">
                        <label class="block text-sm font-medium text-slate-300 mb-4">WhatsApp Notification (After Payment)</label>
                        
                        @php
                            $whatsappEnabled = $event->whatsapp_config['enabled'] ?? false;
                            $whatsappTemplate = $event->whatsapp_config['template'] ?? '';
                        @endphp

                        <div class="flex items-center gap-6 mb-4">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="whatsapp_config[enabled]" value="1" class="peer sr-only" {{ ($whatsappEnabled ?? false) ? 'checked' : '' }} onchange="toggleWhatsappTemplate(this.value)">
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="text-slate-300 group-hover:text-white transition-colors">Enable</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="whatsapp_config[enabled]" value="0" class="peer sr-only" {{ !($whatsappEnabled ?? false) ? 'checked' : '' }} onchange="toggleWhatsappTemplate(this.value)">
                                    <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <div class="w-2 h-2 bg-black rounded-full"></div>
                                    </div>
                                </div>
                                <span class="text-slate-300 group-hover:text-white transition-colors">Disable</span>
                            </label>
                        </div>

                        <div id="whatsapp_template_container" class="{{ ($whatsappEnabled ?? false) ? '' : 'opacity-50 pointer-events-none' }} transition-all duration-200">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Message Template</label>
                            <textarea name="whatsapp_config[template]" id="whatsapp_template" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-green-400 focus:ring-1 focus:ring-green-400 transition-colors font-mono text-sm" placeholder="Halo @{{name}}, terima kasih telah mendaftar di @{{event_name}}. ID Transaksi Anda: @{{transaction_id}}.">{{ old('whatsapp_config.template', $whatsappTemplate ?? '') }}</textarea>
                            <p class="text-xs text-slate-500 mt-2">
                                Available variables: <code class="bg-slate-800 px-1 py-0.5 rounded text-green-400">@{{name}}</code>, <code class="bg-slate-800 px-1 py-0.5 rounded text-green-400">@{{event_name}}</code>, <code class="bg-slate-800 px-1 py-0.5 rounded text-green-400">@{{transaction_id}}</code>, <code class="bg-slate-800 px-1 py-0.5 rounded text-green-400">@{{amount}}</code>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Configuration -->
                <div class="mt-6 border-t border-slate-700 pt-6">
                    <label class="block text-sm font-medium text-slate-300 mb-4">Midtrans Demo Mode (Event Only)</label>
                    @php
                        $midtransDemoMode = old('payment_config.midtrans_demo_mode', (string) (int) ($event->payment_config['midtrans_demo_mode'] ?? 0));
                    @endphp
                    <div class="flex flex-wrap items-center gap-6 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="radio" name="payment_config[midtrans_demo_mode]" value="1" class="peer sr-only" {{ (string) $midtransDemoMode === '1' ? 'checked' : '' }}>
                                <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                    <div class="w-2 h-2 bg-black rounded-full"></div>
                                </div>
                            </div>
                            <span class="text-slate-300 group-hover:text-white transition-colors">ON (Sandbox)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="radio" name="payment_config[midtrans_demo_mode]" value="0" class="peer sr-only" {{ (string) $midtransDemoMode !== '1' ? 'checked' : '' }}>
                                <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                    <div class="w-2 h-2 bg-black rounded-full"></div>
                                </div>
                            </div>
                            <span class="text-slate-300 group-hover:text-white transition-colors">OFF (Production)</span>
                        </label>
                    </div>
                    <p class="text-xs text-slate-500 mb-6">Hanya mempengaruhi pembayaran event (Snap token + Snap JS). Tidak mempengaruhi wallet topup.</p>
                    @error('payment_config.midtrans_demo_mode') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror

                    <label class="block text-sm font-medium text-slate-300 mb-4">Payment Methods</label>
                    @php
                        $paymentMethods = $event->payment_config['allowed_methods'] ?? ['midtrans'];
                        $isMidtrans = in_array('midtrans', $paymentMethods) && !in_array('moota', $paymentMethods);
                        $isMoota = in_array('moota', $paymentMethods) && !in_array('midtrans', $paymentMethods);
                        $isAll = in_array('midtrans', $paymentMethods) && in_array('moota', $paymentMethods);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="payment_config[allowed_methods][]" value="midtrans" class="peer sr-only" {{ $isMidtrans ? 'checked' : '' }}>
                            <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                                <h4 class="font-bold text-white mb-1">Otomatis (Midtrans)</h4>
                                <p class="text-xs text-slate-400">QRIS, E-Wallet, VA (Verifikasi Otomatis)</p>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="bg-yellow-400 rounded-full p-1">
                                    <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input type="radio" name="payment_config[allowed_methods][]" value="moota" class="peer sr-only" {{ $isMoota ? 'checked' : '' }}>
                            <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-full bg-green-500/20 text-green-400 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                </div>
                                <h4 class="font-bold text-white mb-1">Transfer Bank (Moota)</h4>
                                <p class="text-xs text-slate-400">Transfer Manual + Kode Unik</p>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="bg-yellow-400 rounded-full p-1">
                                    <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input type="radio" name="payment_config[allowed_methods][]" value="all" class="peer sr-only" {{ $isAll ? 'checked' : '' }}>
                            <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                </div>
                                <h4 class="font-bold text-white mb-1">Semua Metode</h4>
                                <p class="text-xs text-slate-400">Aktifkan Midtrans & Moota</p>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="bg-yellow-400 rounded-full p-1">
                                    <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </label>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Registration Open <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="datetime-local" name="registration_open_at" value="{{ old('registration_open_at', $event->registration_open_at ? $event->registration_open_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to open immediately.</p>
                        @error('registration_open_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Registration Close <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="datetime-local" name="registration_close_at" value="{{ old('registration_close_at', $event->registration_close_at ? $event->registration_close_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to close when event starts.</p>
                        @error('registration_close_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Promo Code <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="text" name="promo_code" value="{{ old('promo_code', $event->promo_code) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. EARLYBIRD">
                        <p class="text-xs text-slate-500 mt-1">Code for discount.</p>
                        @error('promo_code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Promo Beli X Gratis 1 <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="number" name="promo_buy_x" value="{{ old('promo_buy_x', $event->promo_buy_x) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. 10">
                        <p class="text-xs text-slate-500 mt-1">Isi jumlah beli untuk dapat 1 gratis (misal 10).</p>
                        @error('promo_buy_x') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <div id="hero-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                            <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Image (16:9)</p>
                            </div>
                        </div>
                        <input type="hidden" name="hero_image" id="hero_image_input" value="{{ $event->hero_image }}">
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
                        <input type="hidden" name="logo_image" id="logo_image_input" value="{{ $event->logo_image }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Twibbon Image (PNG) <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <div id="twibbon-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                            <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Twibbon (PNG)</p>
                            </div>
                        </div>
                        <input type="hidden" name="twibbon_image" id="twibbon_image_input" value="{{ $event->twibbon_image }}">
                        @error('twibbon_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
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
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-400 mb-1">Category Name <span class="text-red-400">*</span></label>
                <input type="text" class="cat-name w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" placeholder="e.g. 10K Open" required>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-400 mb-1">Route Map (GPX)</label>
                <select class="cat-gpx w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    <option value="">-- Select Route --</option>
                    @foreach($gpxList as $gpx)
                        <option value="{{ $gpx->id }}">{{ $gpx->title }} ({{ $gpx->distance_km }}km)</option>
                    @endforeach
                </select>
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
                
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <div>
                        <label class="block text-[10px] font-medium text-slate-500 mb-1">Quota (Opt)</label>
                        <input type="number" class="cat-eb-quota w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-xs" placeholder="Limit">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-500 mb-1">End Date (Opt)</label>
                        <input type="datetime-local" class="cat-eb-end w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-xs [color-scheme:dark]">
                    </div>
                </div>
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

        <div class="mt-4">
            <label class="block text-xs font-medium text-slate-400 mb-2">Prizes (Hadiah Juara)</label>
            <div class="cat-prizes-container space-y-2"></div>
            <button type="button" class="add-prize-btn mt-2 text-xs bg-slate-700 hover:bg-slate-600 text-white px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Prize Row
            </button>
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
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    window.laravelErrors = @json($errors->getMessages());
    function toggleWhatsappTemplate(value) {
        const container = document.getElementById('whatsapp_template_container');
        const textarea = document.getElementById('whatsapp_template');
        
        if (value == '1') {
            container.classList.remove('opacity-50', 'pointer-events-none');
            textarea.required = true;
        } else {
            container.classList.add('opacity-50', 'pointer-events-none');
            textarea.required = false;
        }
    }

    Dropzone.autoDiscover = false;

    function initDropzone(id, inputName, maxFiles = 1, existingFiles = []) {
        const el = document.getElementById(id);
        if (!el) return;

        const container = document.getElementById(id.replace('-dropzone', '-inputs')) || el.parentNode;
        
        const dz = new Dropzone("#" + id, {
            url: "{{ route('eo.events.upload-media') }}",
            paramName: "file",
            maxFiles: maxFiles,
            maxFilesize: 5,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            params: {
                folder: id.includes('hero') ? 'events/hero' : 
                       (id.includes('logo') ? 'events/logo' : 
                       (id.includes('twibbon') ? 'events/twibbon' : 
                       (id.includes('gallery') ? 'events/gallery' : 
                       (id.includes('jersey') ? 'events/jersey' : 
                       (id.includes('medal') ? 'events/medal' : 'events/sponsors')))))
            },
            success: function(file, response) {
                if (response.success) {
                    file.serverPath = response.path;
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
                        if(!path) return;
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
                            reorderInputs(myDropzone, inputName);
                        }
                    });
                }
            }
        });
    }

    function addHiddenInput(name, value) {
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

        const container = document.getElementById(name.replace('[]', '') + '-inputs');
        if (!container) return;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        input.className = 'dz-hidden-input';
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

        const previews = dropzoneInstance.element.querySelectorAll('.dz-preview');
        container.innerHTML = '';

        previews.forEach(preview => {
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
        initDropzone('hero-dropzone', 'hero_image', 1, @json($event->hero_image ? [$event->hero_image] : []));
        initDropzone('logo-dropzone', 'logo_image', 1, @json($event->logo_image ? [$event->logo_image] : []));
        initDropzone('twibbon-dropzone', 'twibbon_image', 1, @json($event->twibbon_image ? [$event->twibbon_image] : []));
        initDropzone('jersey-dropzone', 'jersey_image', 1, @json($event->jersey_image ? [$event->jersey_image] : []));
        initDropzone('medal-dropzone', 'medal_image', 1, @json($event->medal_image ? [$event->medal_image] : []));
        initDropzone('gallery-dropzone', 'gallery[]', 10, @json($event->gallery ?? []));
        initDropzone('sponsors-dropzone', 'sponsors[]', 30, @json($event->sponsors ?? []));
    });

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

    // Custom Email Message
    ClassicEditor
        .create(document.querySelector('#custom_email_message_editor'), commonConfig)
        .then(editor => {
            editor.setData(`{!! old('custom_email_message', $event->custom_email_message) !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#custom_email_message').value = editor.getData();
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
        const currentCategoryIndex = categoryIndex;
        
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.category-item');
        
        // Set names with index
        const inputs = {
            'cat-id': 'id',
            'cat-name': 'name',
            'cat-distance': 'distance_km',
            'cat-quota': 'quota',
            'cat-price-early': 'price_early',
            'cat-price': 'price_regular',
            'cat-price-late': 'price_late',
            'cat-cot': 'cutoff_minutes',
            'cat-gpx': 'master_gpx_id',
            'cat-eb-quota': 'early_bird_quota',
            'cat-eb-end': 'early_bird_end_at'
        };

        for (const [cls, name] of Object.entries(inputs)) {
            const input = item.querySelector('.' + cls);
            if (input) {
                input.name = `categories[${currentCategoryIndex}][${name}]`;
                if (data && data[name] !== undefined && data[name] !== null) {
                    // Handle datetime-local format
                    if (name === 'early_bird_end_at' && data[name]) {
                        // If it's a full ISO string, slice it. If it's already Y-m-d H:i, format it.
                        // Assuming standard Laravel serialization to ISO 8601
                        let dateVal = data[name];
                        if (dateVal.length > 16) dateVal = dateVal.substring(0, 16);
                        input.value = dateVal;
                    } else {
                        input.value = data[name];
                    }
                }
            }
        }

        // Dynamic Prizes Logic
        const prizesContainer = item.querySelector('.cat-prizes-container');
        const addPrizeBtn = item.querySelector('.add-prize-btn');
        let prizeIndex = 1;

        const addPrizeRow = (rank, value = '') => {
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center prize-row';
            
            // Error handling
            const errorKey = `categories.${currentCategoryIndex}.prizes.${rank}`;
            const hasError = window.laravelErrors && window.laravelErrors[errorKey];
            const borderColor = hasError ? 'border-red-500' : 'border-slate-700';
            const errorMessage = hasError ? `<p class="text-red-500 text-xs mt-1 w-full">${window.laravelErrors[errorKey][0]}</p>` : '';

            row.innerHTML = `
                <div class="w-full">
                    <div class="flex gap-2 items-center">
                        <span class="text-xs font-mono text-slate-500 w-8">#${rank}</span>
                        <input type="text" name="categories[${currentCategoryIndex}][prizes][${rank}]" value="${value}" 
                               class="flex-1 bg-slate-900 border ${borderColor} rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" 
                               placeholder="Prize description...">
                        <button type="button" class="text-slate-500 hover:text-red-400 remove-prize-btn">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    ${errorMessage}
                </div>
            `;
            
            row.querySelector('.remove-prize-btn').onclick = () => {
                row.remove();
                // Optional: re-index ranks? Usually ranks are fixed 1,2,3... 
                // If user deletes #2, should #3 become #2?
                // Let's implement re-indexing for consistency.
                reindexPrizes(prizesContainer);
            };

            prizesContainer.appendChild(row);
            prizeIndex++;
        };

        addPrizeBtn.onclick = () => addPrizeRow(prizesContainer.children.length + 1);

        // Load existing prizes
        if (data && data.prizes) {
            // Check if prizes is array or object
            const entries = Object.entries(data.prizes);
            // Sort by rank key if possible
            entries.sort((a, b) => parseInt(a[0]) - parseInt(b[0]));
            
            entries.forEach(([rank, val]) => {
                addPrizeRow(rank, val);
            });
            // Update prizeIndex to next available
            if (entries.length > 0) {
                 prizeIndex = parseInt(entries[entries.length - 1][0]) + 1;
            }
        } else {
            // Default 3 rows if empty
            addPrizeRow(1);
            addPrizeRow(2);
            addPrizeRow(3);
        }

        // Helper to re-index
        const reindexPrizes = (container) => {
            Array.from(container.children).forEach((row, idx) => {
                const newRank = idx + 1;
                row.querySelector('span').innerText = `#${newRank}`;
                row.querySelector('input').name = `categories[${currentCategoryIndex}][prizes][${newRank}]`;
            });
            prizeIndex = container.children.length + 1;
        };

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
    let existingCategories = @json(old('categories', $event->categories));
    if (existingCategories && !Array.isArray(existingCategories)) {
        existingCategories = Object.values(existingCategories);
    }

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
                const proxyUrl = '/image-proxy?url=' + encodeURIComponent(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`);
                fetch(proxyUrl)
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

    function previewEmail() {
        const content = document.querySelector('#custom_email_message').value;
        const name = document.querySelector('input[name="name"]').value;
        const ticketEmailUseQr = document.querySelector('input[name="ticket_email_use_qr"]:checked')?.value;
        
        // Create a temporary form to post
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('eo.events.preview-email', $event) }}";
        form.target = '_blank';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('input[name="_token"]').value;
        form.appendChild(csrfToken);
        
        const contentInput = document.createElement('input');
        contentInput.type = 'hidden';
        contentInput.name = 'custom_email_message';
        contentInput.value = content;
        form.appendChild(contentInput);

        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'name';
        nameInput.value = name;
        form.appendChild(nameInput);

        if (ticketEmailUseQr !== undefined) {
            const qrInput = document.createElement('input');
            qrInput.type = 'hidden';
            qrInput.name = 'ticket_email_use_qr';
            qrInput.value = ticketEmailUseQr;
            form.appendChild(qrInput);
        }
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    async function sendTestEmail() {
        const email = (document.getElementById('test_email_to')?.value || '').trim();
        const statusEl = document.getElementById('testEmailStatus');
        const remainingEl = document.getElementById('testEmailRemaining');
        const btn = document.getElementById('sendTestEmailBtn');

        if (statusEl) statusEl.textContent = '';
        if (remainingEl) remainingEl.textContent = '';

        if (!email) {
            if (statusEl) {
                statusEl.className = 'text-sm text-red-400';
                statusEl.textContent = 'Email tujuan wajib diisi.';
            }
            return;
        }

        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        if (!emailOk) {
            if (statusEl) {
                statusEl.className = 'text-sm text-red-400';
                statusEl.textContent = 'Format email tidak valid.';
            }
            return;
        }

        const content = document.querySelector('#custom_email_message')?.value || '';
        const name = document.querySelector('input[name=\"name\"]')?.value || '';
        const ticketEmailUseQr = document.querySelector('input[name=\"ticket_email_use_qr\"]:checked')?.value;

        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Sending...';
        }
        if (statusEl) {
            statusEl.className = 'text-sm text-slate-300';
            statusEl.textContent = 'Mengirim test email...';
        }

        try {
            const res = await fetch("{{ route('eo.events.send-test-email', $event) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=\"_token\"]').value
                },
                body: JSON.stringify({
                    test_email: email,
                    custom_email_message: content,
                    name: name,
                    ticket_email_use_qr: ticketEmailUseQr
                })
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                const msg = data.message || (res.status === 429 ? 'Batas kirim test email tercapai.' : 'Gagal mengirim test email.');
                if (statusEl) {
                    statusEl.className = 'text-sm text-red-400';
                    statusEl.textContent = msg;
                }
                if (remainingEl && typeof data.remaining !== 'undefined') {
                    remainingEl.textContent = `Sisa kuota sesi ini: ${data.remaining}/3`;
                }
                return;
            }

            if (statusEl) {
                statusEl.className = 'text-sm text-green-400';
                statusEl.textContent = data.message || 'Test email berhasil dikirim.';
            }
            if (remainingEl && typeof data.remaining !== 'undefined') {
                remainingEl.textContent = `Sisa kuota sesi ini: ${data.remaining}/3`;
            }
        } catch (e) {
            if (statusEl) {
                statusEl.className = 'text-sm text-red-400';
                statusEl.textContent = 'Terjadi kesalahan saat mengirim email.';
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Send Test Email';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('eventForm');
        const errorEl = document.getElementById('ticketEmailQrError');
        if (form && errorEl) {
            form.addEventListener('submit', function (e) {
                const selected = form.querySelector('input[name="ticket_email_use_qr"]:checked');
                if (!selected) {
                    e.preventDefault();
                    errorEl.textContent = 'Silakan pilih salah satu opsi template tiket email.';
                    errorEl.classList.remove('hidden');
                } else {
                    errorEl.classList.add('hidden');
                    errorEl.textContent = '';
                }
            });
        }
    });
</script>
@endpush
