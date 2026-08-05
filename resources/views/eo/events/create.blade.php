@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Create Event')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <style>
        /* CKEditor Custom Height & Dark Styling */
        .ck-editor__editable_inline {
            min-height: 250px !important;
            max-height: 850px !important;
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-radius: 0 0 0.75rem 0.75rem !important;
            padding: 1.25rem !important;
        }
        #full_description_editor .ck-editor__editable_inline {
            min-height: 480px !important;
        }
        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-color: #334155 !important;
        }
        .ck.ck-editor__main>.ck-editor__editable.ck-focused {
            border-color: #eab308 !important;
            box-shadow: 0 0 0 1px #eab308 !important;
        }
        .ck.ck-toolbar {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }
        .ck.ck-toolbar .ck-button {
            color: #cbd5e1 !important;
        }
        .ck.ck-toolbar .ck-button:hover,
        .ck.ck-toolbar .ck-button.ck-on {
            background-color: #334155 !important;
            color: #facc15 !important;
        }
        .ck.ck-dropdown__panel {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        .ck.ck-list__item button {
            color: #f8fafc !important;
        }
        .ck.ck-list__item button:hover {
            background-color: #334155 !important;
        }
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
        if (typeof tailwind !== 'undefined') {
            tailwind.config = tailwind.config || {};
            tailwind.config.theme = tailwind.config.theme || {};
            tailwind.config.theme.extend = tailwind.config.theme.extend || {};
            tailwind.config.theme.extend.colors = {
                ...(tailwind.config.theme.extend.colors || {}),
                neon: {
                    ...((tailwind.config.theme.extend.colors && tailwind.config.theme.extend.colors.neon) || {}),
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                }
            };
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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800 md:col-span-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Tampilkan Promo Modal <span class="text-red-400">*</span></label>
                            <div class="flex items-center gap-5 pt-0.5">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="premium_amenities[promo_modal_enabled]" value="1" class="peer sr-only" {{ old('premium_amenities.promo_modal_enabled') == '1' ? 'checked' : '' }} required>
                                        <div class="w-4 h-4 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                            <div class="w-1.5 h-1.5 bg-black rounded-full"></div>
                                        </div>
                                    </div>
                                    <span class="ml-2 text-xs text-slate-300 group-hover:text-white transition-colors">Ya (Aktif)</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="premium_amenities[promo_modal_enabled]" value="0" class="peer sr-only" {{ old('premium_amenities.promo_modal_enabled', '0') == '0' ? 'checked' : '' }} required>
                                        <div class="w-4 h-4 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                            <div class="w-1.5 h-1.5 bg-black rounded-full"></div>
                                        </div>
                                    </div>
                                    <span class="ml-2 text-xs text-slate-300 group-hover:text-white transition-colors">Tidak</span>
                                </label>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Popup promo otomatis muncul saat pendaftaran dibuka.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Custom Content / HTML Promo Modal <span class="text-slate-500 text-[10px]">(Opsional)</span></label>
                            <input type="text" name="premium_amenities[promo_modal_html]" value="{{ old('premium_amenities.promo_modal_html') }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:border-yellow-400 focus:outline-none" placeholder="Isikan pesan promo / HTML custom...">
                            <p class="text-[10px] text-slate-500 mt-1">Kosongkan untuk merender promo default tema.</p>
                        </div>
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
                                <input type="radio" name="template" value="quick-light" class="peer sr-only" {{ old('template') == 'quick-light' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-white h-24 rounded-lg mb-3 border border-slate-200 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-gradient-to-tr from-sky-200 to-emerald-200"></div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Quick Light</h4>
                                    <p class="text-xs text-slate-400">Form pendaftaran ringkas, modern clean, cepat.</p>
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

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="template" value="golden-run" class="peer sr-only" {{ old('template') == 'golden-run' ? 'checked' : '' }}>
                                <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col">
                                    <div class="bg-slate-800 h-24 rounded-lg mb-3 border border-slate-700 flex items-center justify-center overflow-hidden">
                                        <div class="w-full h-full bg-zinc-950 relative flex flex-col items-center justify-center">
                                            <div class="absolute inset-0 bg-gradient-to-br from-yellow-600/20 via-transparent to-yellow-900/20"></div>
                                            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-yellow-200 to-yellow-500"></div>
                                            <div class="absolute bottom-0 right-0 w-full h-1 bg-gradient-to-r from-yellow-500 via-yellow-200 to-yellow-400"></div>
                                            <div class="w-12 h-12 rounded-full border border-yellow-500/50 flex items-center justify-center relative z-10">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-white mb-1">Golden Run</h4>
                                    <p class="text-xs text-slate-400">Tema eksklusif dengan nuansa emas dan hitam. Mewah dan elegan.</p>
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

                    <div class="md:col-span-2 space-y-6 pt-4 border-t border-slate-700/60">
                        <!-- Template Theme Selection -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-2">Desain Template Email Tiket</label>
                            <p class="text-xs text-slate-400 mb-4">Pilih gaya tampilan email tiket yang dikirimkan otomatis ke peserta.</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Modern Dark -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="ticket_email_template" value="modern" class="peer sr-only" {{ old('ticket_email_template', 'modern') === 'modern' ? 'checked' : '' }}>
                                    <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800/80 transition-all hover:border-slate-500 h-full flex flex-col justify-between">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-wider text-yellow-400 block mb-1">1. Modern Dark</span>
                                            <p class="text-xs text-slate-400">Header gelap slate dengan aksen neon modern dan tiket bertanda putus-putus.</p>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <div class="bg-yellow-400 rounded-full p-1">
                                            <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                </label>

                                <!-- Minimalist Clean -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="ticket_email_template" value="minimalist" class="peer sr-only" {{ old('ticket_email_template') === 'minimalist' ? 'checked' : '' }}>
                                    <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800/80 transition-all hover:border-slate-500 h-full flex flex-col justify-between">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-wider text-slate-200 block mb-1">2. Clean Minimalist</span>
                                            <p class="text-xs text-slate-400">Tampilan bersih berlatar putih dengan border tegas, cocok untuk corporate event.</p>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <div class="bg-yellow-400 rounded-full p-1">
                                            <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                </label>

                                <!-- Bold Sporty -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="ticket_email_template" value="sporty" class="peer sr-only" {{ old('ticket_email_template') === 'sporty' ? 'checked' : '' }}>
                                    <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800/80 transition-all hover:border-slate-500 h-full flex flex-col justify-between">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-wider text-orange-400 block mb-1">3. Bold Sporty</span>
                                            <p class="text-xs text-slate-400">Desain atletis energik dengan warna gradien oranye kontras dan badge tiket besar.</p>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <div class="bg-yellow-400 rounded-full p-1">
                                            <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <button type="button" onclick="openTicketEmailPreviewModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-yellow-400/10 hover:bg-yellow-400 hover:text-black text-yellow-400 text-xs font-bold rounded-xl border border-yellow-400/30 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Preview HTML Template Email Tiket</span>
                                </button>
                            </div>
                        </div>

                        <!-- Content Items Toggle -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-2">Item yang Ditampilkan di Email Tiket</label>
                            <p class="text-xs text-slate-400 mb-3">Centang bagian yang ingin ditampilkan di dalam tiket email peserta:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 bg-slate-900/50 hover:bg-slate-900 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ticket_email_use_qr" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-0" {{ old('ticket_email_use_qr', '1') == '1' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-slate-200">QR Code Verifikasi Tiket</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 bg-slate-900/50 hover:bg-slate-900 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ticket_email_show_jersey" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-0" {{ old('ticket_email_show_jersey', '1') == '1' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-slate-200">Ukuran Jersey Peserta</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 bg-slate-900/50 hover:bg-slate-900 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ticket_email_show_addons" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-0" {{ old('ticket_email_show_addons', '1') == '1' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-slate-200">Detail Addon / Item Tambahan</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 bg-slate-900/50 hover:bg-slate-900 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ticket_email_show_pic" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-0" {{ old('ticket_email_show_pic', '1') == '1' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-slate-200">Informasi & Kontak PIC</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Public Participants & SEO Slug (1 Row, 2 Columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-4">Daftar Peserta (Public)</label>
                            <div class="flex flex-wrap items-center gap-6">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="show_participant_list" value="1" class="peer sr-only" {{ old('show_participant_list', '1') == '1' ? 'checked' : '' }}>
                                        <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                            <div class="w-2 h-2 bg-black rounded-full"></div>
                                        </div>
                                    </div>
                                    <span class="text-slate-300 group-hover:text-white transition-colors text-sm">Tampilkan Daftar Peserta</span>
                                </label>

                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input type="radio" name="show_participant_list" value="0" class="peer sr-only" {{ old('show_participant_list') == '0' ? 'checked' : '' }}>
                                        <div class="w-5 h-5 border-2 border-slate-500 rounded-full peer-checked:border-yellow-400 peer-checked:bg-yellow-400 transition-colors"></div>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                            <div class="w-2 h-2 bg-black rounded-full"></div>
                                        </div>
                                    </div>
                                    <span class="text-slate-300 group-hover:text-white transition-colors text-sm">Sembunyikan Daftar Peserta</span>
                                </label>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Mengatur apakah daftar peserta dapat dilihat oleh publik di halaman event.</p>
                            @error('show_participant_list') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Event Slug (SEO URL)</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-700 bg-slate-800 text-slate-400 text-xs">
                                    /events/
                                </span>
                                <input type="text" name="slug" value="{{ old('slug') }}" class="flex-1 bg-slate-900 border border-slate-700 rounded-r-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors text-sm" placeholder="jakarta-marathon-2025">
                            </div>
                            <p class="text-slate-500 text-xs mt-1">Kosongkan untuk otomatis di-generate dari nama event.</p>
                            @error('slug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Short Desc & Full Desc (1 Row, 2 Columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Short Description</label>
                            <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                                <div id="short_description_editor"></div>
                                <textarea name="short_description" id="short_description" class="hidden">{{ old('short_description') }}</textarea>
                            </div>
                            @error('short_description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Full Description</label>
                            <div class="bg-white rounded-xl overflow-hidden text-slate-900">
                                <div id="full_description_editor"></div>
                                <textarea name="full_description" id="full_description" class="hidden">{{ old('full_description') }}</textarea>
                            </div>
                            @error('full_description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
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
                        <label class="block text-sm font-medium text-slate-300 mb-2 flex items-center justify-between">
                            <span>Platform Fee (Per Participant)</span>
                            @if($canCustomPlatformFee ?? false)
                                <span class="text-[10px] font-bold text-amber-400 bg-amber-400/10 border border-amber-400/20 px-2 py-0.5 rounded-full uppercase tracking-wider">Akses Khusus Admin EO</span>
                            @endif
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500 text-sm font-bold">Rp</span>
                            @if($canCustomPlatformFee ?? false)
                                <input type="number" name="platform_fee" value="{{ old('platform_fee', $defaultPlatformFee ?? 5000) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="0">
                            @else
                                <input type="number" value="{{ $defaultPlatformFee ?? 5000 }}" readonly disabled class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-slate-400 font-bold cursor-not-allowed" placeholder="0">
                            @endif
                        </div>
                        <p class="text-slate-500 text-xs mt-1">
                            @if($canCustomPlatformFee ?? false)
                                Anda memiliki akses khusus untuk menentukan nominal Platform Fee per peserta untuk event ini.
                            @else
                                Biaya platform fee per peserta ditentukan otomatis oleh sistem sesuai kebijakan Admin.
                            @endif
                        </p>
                        @if($canCustomPlatformFee ?? false)
                            @error('platform_fee') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    <!-- WhatsApp Configuration (Hidden) -->
                    <div class="hidden">
                        <input type="hidden" name="whatsapp_config[enabled]" value="0">
                        <input type="hidden" name="whatsapp_config[template]" value="">
                    </div>
                </div>

                <!-- Payment Configuration -->
                <div class="mt-6 border-t border-slate-700 pt-6">
                    <label class="block text-sm font-medium text-slate-300 mb-4">Midtrans Demo Mode (Event Only)</label>
                    @php
                        $midtransDemoMode = old('payment_config.midtrans_demo_mode', '0');
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
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="payment_config[allowed_methods][]" value="midtrans" class="peer sr-only" checked>
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
                            <input type="radio" name="payment_config[allowed_methods][]" value="moota" class="peer sr-only">
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
                            <input type="radio" name="payment_config[allowed_methods][]" value="cod" class="peer sr-only">
                            <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-full bg-orange-500/20 text-orange-400 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4 0 1.3.62 2.46 1.58 3.2L12 21l2.42-5.8A3.99 3.99 0 0016 12c0-2.21-1.79-4-4-4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                </div>
                                <h4 class="font-bold text-white mb-1">COD</h4>
                                <p class="text-xs text-slate-400">Bayar langsung sesuai arahan EO</p>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="bg-yellow-400 rounded-full p-1">
                                    <svg class="w-3 h-3 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input type="radio" name="payment_config[allowed_methods][]" value="all" class="peer sr-only">
                            <div class="bg-slate-900 border-2 border-slate-700 rounded-xl p-4 peer-checked:border-yellow-400 peer-checked:bg-slate-800 transition-all hover:border-slate-500 h-full flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                </div>
                                <h4 class="font-bold text-white mb-1">Semua Metode</h4>
                                <p class="text-xs text-slate-400">Aktifkan Midtrans, Moota, COD</p>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Start Date & Time <span class="text-red-400">*</span></label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at', date('Y-m-d\TH:i', strtotime('+30 days 06:00'))) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]" required>
                        @error('start_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">End Date & Time</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at', date('Y-m-d\TH:i', strtotime('+30 days 12:00'))) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        @error('end_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Registration Open <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="datetime-local" name="registration_open_at" value="{{ old('registration_open_at', date('Y-m-d\TH:i', strtotime('today 08:00'))) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to open immediately.</p>
                        @error('registration_open_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Registration Close <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="datetime-local" name="registration_close_at" value="{{ old('registration_close_at', date('Y-m-d\TH:i', strtotime('+25 days 23:59'))) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors [color-scheme:dark]">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to close when event starts.</p>
                        @error('registration_close_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Promo Beli X Gratis 1 <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <input type="number" name="promo_buy_x" value="{{ old('promo_buy_x') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. 10">
                        <p class="text-xs text-slate-500 mt-1">Isi jumlah beli untuk dapat 1 gratis (misal 10).</p>
                        @error('promo_buy_x') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kupon Diskon Terkait <span class="text-slate-500 text-xs">(Bisa Pilih Banyak)</span></label>
                        <div class="relative">
                            <div id="coupon_tags_container" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 flex flex-wrap items-center gap-2 min-h-[48px] focus-within:border-yellow-400 focus-within:ring-1 focus-within:ring-yellow-400 transition-colors cursor-text" onclick="document.getElementById('coupon_search_input').focus()">
                                <div id="selected_coupon_badges" class="flex flex-wrap items-center gap-2"></div>
                                <input type="text" id="coupon_search_input" class="flex-1 bg-transparent border-0 text-white text-sm focus:outline-none focus:ring-0 p-1 min-w-[120px]" placeholder="Cari kode kupon..." autocomplete="off">
                                <button type="button" onclick="openCreateCouponModal()" class="ml-auto px-3 py-1.5 bg-yellow-400/20 hover:bg-yellow-400 text-yellow-400 hover:text-black font-bold rounded-lg border border-yellow-400/40 transition-all text-xs flex items-center gap-1.5 whitespace-nowrap cursor-pointer shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Buat Kupon</span>
                                </button>
                            </div>

                            <input type="hidden" id="promo_code_input" name="promo_code" value="{{ old('promo_code') }}">
                            <div id="promo_codes_hidden_container"></div>

                            <div id="coupon_search_dropdown" class="absolute left-0 right-0 top-full mt-2 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-40 hidden max-h-60 overflow-y-auto divide-y divide-slate-800"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Ketik kode kupon untuk mencari kupon yang ada atau klik <strong>+ Buat Kupon</strong> untuk menambah kupon baru.</p>
                        @error('promo_code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
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
                            <input type="text" name="location_name" id="location_name" value="{{ old('location_name', 'Gelora Bung Karno (GBK), Jakarta') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm" placeholder="e.g. Gelora Bung Karno" required>
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

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Twibbon Image (PNG) <span class="text-slate-500 text-xs">(Optional)</span></label>
                        <div id="twibbon-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                            <div class="dz-message text-center py-8">
                                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-sm text-slate-400">Click or Drag Twibbon (PNG)</p>
                            </div>
                        </div>
                        <input type="hidden" name="twibbon_image" id="twibbon_image_input">
                        @error('twibbon_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
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

@endsection

@push('scripts')
<script>
    window.laravelErrors = @json($errors->getMessages());
</script>
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
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
                folder: id.includes('hero') ? 'events/hero' : 
                       (id.includes('logo') ? 'events/logo' : 
                       (id.includes('twibbon') ? 'events/twibbon' : 
                       (id.includes('gallery') ? 'events/gallery' : 
                       (id.includes('jersey') ? 'events/jersey' : 
                       (id.includes('medal') ? 'events/medal' : 'events/sponsors')))))
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
        initDropzone('twibbon-dropzone', 'twibbon_image', 1);
        initDropzone('jersey-dropzone', 'jersey_image', 1);
        initDropzone('medal-dropzone', 'medal_image', 1);
        initDropzone('gallery-dropzone', 'gallery[]', 10);
        initDropzone('sponsors-dropzone', 'sponsors[]', 30);

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
            // Prevent crash if template changes and selector is missing
            if (input) {
                input.name = `categories[${currentCategoryIndex}][${name}]`;
                if (data && data[name] !== undefined && data[name] !== null) input.value = data[name];
            }
        }

        // Dynamic Prizes Logic
        const prizesContainer = item.querySelector('.cat-prizes-container');
        const addPrizeBtn = item.querySelector('.add-prize-btn');
        let prizeIndex = 1;

        const addPrizeRow = (rank, value = '') => {
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-start prize-row'; // Changed items-center to items-start for error message alignment
            
            const errorKey = `categories.${currentCategoryIndex}.prizes.${rank}`;
            const errorMessage = window.laravelErrors && window.laravelErrors[errorKey] ? window.laravelErrors[errorKey][0] : null;
            const borderClass = errorMessage ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-700 focus:border-yellow-400 focus:ring-yellow-400';

            row.innerHTML = `
                <span class="text-xs font-mono text-slate-500 w-8 mt-2">#${rank}</span>
                <div class="flex-1">
                    <input type="text" name="categories[${currentCategoryIndex}][prizes][${rank}]" value="${value}" 
                           class="w-full bg-slate-900 border ${borderClass} rounded-lg px-3 py-2 text-white text-sm focus:ring-1 transition-colors" 
                           placeholder="Prize description...">
                    ${errorMessage ? `<p class="text-red-400 text-xs mt-1">${errorMessage}</p>` : ''}
                </div>
                <button type="button" class="text-slate-500 hover:text-red-400 remove-prize-btn mt-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            `;
            
            row.querySelector('.remove-prize-btn').onclick = () => {
                row.remove();
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
    window.editors = {};
    const commonConfig = {
        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
    };

    // Short Description
    ClassicEditor
        .create(document.querySelector('#short_description_editor'), commonConfig)
        .then(editor => {
            window.editors['short_description'] = editor;
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
            window.editors['full_description'] = editor;
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
            window.editors['terms_and_conditions'] = editor;
            editor.setData(`{!! old('terms_and_conditions') !!}`);
            editor.model.document.on('change:data', () => {
                document.querySelector('#terms_and_conditions').value = editor.getData();
            });
        })
        .catch(error => console.error(error));

    function openLivePreview() {
        if (window.editors) {
            Object.keys(window.editors).forEach(key => {
                const textarea = document.querySelector('#' + key);
                if (textarea && window.editors[key]) {
                    textarea.value = window.editors[key].getData();
                }
            });
        }

        const mainForm = document.getElementById('eventForm');
        if (!mainForm) return;

        const formData = new FormData(mainForm);
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = "{{ route('eo.events.live-preview') }}";
        tempForm.target = '_blank';
        tempForm.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        tempForm.appendChild(csrfInput);

        for (let [key, value] of formData.entries()) {
            if (value instanceof File) continue;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }

        document.body.appendChild(tempForm);
        tempForm.submit();
        document.body.removeChild(tempForm);
    }

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

    // Mapbox Map
    document.addEventListener('DOMContentLoaded', function() {
        const mapboxToken = '{{ config('services.mapbox.token') }}';
        if (!mapboxToken) {
            console.error('Mapbox token is missing in .env (expected MAPBOX_TOKEN)');
            return;
        }
        
        mapboxgl.accessToken = mapboxToken;
        
        const defaultLat = -6.2088;
        const defaultLng = 106.8456;
        
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/light-v11', // dark style to match dashboard
            center: [defaultLng, defaultLat],
            zoom: 13
        });

        // Add navigation controls (zoom, rotate)
        map.addControl(new mapboxgl.NavigationControl());

        // Add geolocate control (current location)
        const geolocate = new mapboxgl.GeolocateControl({
            positionOptions: {
                enableHighAccuracy: true
            },
            trackUserLocation: true,
            showUserHeading: true
        });
        map.addControl(geolocate);

        const marker = new mapboxgl.Marker({
            draggable: true,
            color: "#eab308" // yellow-500
        })
        .setLngLat([defaultLng, defaultLat])
        .addTo(map);
        
        function updateLocation(lat, lng) {
            document.getElementById('location_lat').value = lat;
            document.getElementById('location_lng').value = lng;
            
            // Reverse geocoding using Mapbox API
            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${mapboxToken}`)
                .then(res => res.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        const feature = data.features[0];
                        document.getElementById('location_address').value = feature.place_name;
                        
                        // If venue name is empty, try to fill it from the first feature
                        if (!document.getElementById('location_name').value) {
                            document.getElementById('location_name').value = feature.text || '';
                        }
                    }
                })
                .catch(err => console.error('Reverse geocoding error:', err));
        }

        marker.on('dragend', function() {
            const lngLat = marker.getLngLat();
            updateLocation(lngLat.lat, lngLat.lng);
        });

        map.on('click', function(e) {
            marker.setLngLat(e.lngLat);
            updateLocation(e.lngLat.lat, e.lngLat.lng);
        });

        // Search location
        const searchInput = document.getElementById('location_search');
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value;
                
                fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${mapboxToken}&limit=1`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.features && data.features.length > 0) {
                            const first = data.features[0];
                            const [lng, lat] = first.center;
                            
                            map.flyTo({
                                center: [lng, lat],
                                zoom: 16,
                                essential: true
                            });
                            
                            marker.setLngLat([lng, lat]);
                            updateLocation(lat, lng);
                            document.getElementById('location_name').value = first.text || query;
                        }
                    })
                    .catch(err => console.error('Search geocoding error:', err));
            }
        });

        // Handle successful geolocation
        geolocate.on('geolocate', (e) => {
            const lng = e.coords.longitude;
            const lat = e.coords.latitude;
            marker.setLngLat([lng, lat]);
            updateLocation(lat, lng);
        });
        
        // Initial update
        updateLocation(defaultLat, defaultLng);
    });
</script>

<!-- Live HTML Email Preview Modal -->
<div id="ticketEmailPreviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Live Preview HTML Template Email Tiket</span>
            </h3>
            <button type="button" onclick="closeTicketEmailPreviewModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 p-4 overflow-y-auto bg-slate-950/50">
            <iframe id="ticketEmailPreviewFrame" name="ticketEmailPreviewFrame" class="w-full h-[65vh] rounded-xl border border-slate-800 bg-white" src="about:blank"></iframe>
        </div>
        <div class="p-4 border-t border-slate-800 bg-slate-950 flex justify-end">
            <button type="button" onclick="closeTicketEmailPreviewModal()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition-all">Tutup Preview</button>
        </div>
    </div>
</div>

<!-- Modal Buat Kupon Baru (Persis /eo/coupons/create) -->
<div id="createCouponModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl my-auto">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Buat Kupon Baru Terkait</span>
            </h3>
            <button type="button" onclick="closeCreateCouponModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="createCouponModalForm" onsubmit="submitModalCoupon(event)" class="p-6 space-y-5 overflow-y-auto max-h-[75vh]">
            <div id="couponModalStatus" class="hidden text-xs p-3 rounded-xl"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Kode Kupon <span class="text-red-400">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="modal_coupon_code" name="code" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white font-mono uppercase text-sm focus:border-yellow-400 focus:outline-none" placeholder="MERDEKA45" required>
                        <button type="button" onclick="generateModalCouponCode()" class="px-3 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl border border-slate-700 text-xs" title="Generate Code">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Tipe Diskon <span class="text-red-400">*</span></label>
                    <select id="modal_coupon_type" name="type" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none" required>
                        <option value="percent">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Nilai Diskon <span class="text-red-400">*</span></label>
                    <input type="number" id="modal_coupon_value" name="value" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="10" required min="0">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Min. Transaksi (Rp)</label>
                    <input type="number" id="modal_coupon_min" name="min_transaction_amount" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="0" min="0" value="0">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Kuota Total (Stok)</label>
                    <input type="number" name="max_uses" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="Kosongkan untuk unlimited" min="1">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Batas Per User</label>
                    <input type="number" name="usage_limit_per_user" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="1" min="1" value="1">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Tanggal Mulai</label>
                    <input type="datetime-local" name="start_at" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none [color-scheme:dark]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Tanggal Berakhir</label>
                    <input type="datetime-local" name="expires_at" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:border-yellow-400 focus:outline-none [color-scheme:dark]">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 space-y-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-yellow-400" checked>
                    <span class="text-xs text-slate-300">Aktifkan Kupon Ini</span>
                </label>
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_stackable" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-yellow-400">
                    <span class="text-xs text-slate-300">Dapat Digabungkan dengan Promo Lain</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="closeCreateCouponModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl">Batal</button>
                <button type="submit" id="submitCouponBtn" class="px-5 py-2 bg-yellow-400 hover:bg-yellow-300 text-black text-xs font-black rounded-xl shadow-lg transition-all">Simpan & Tautkan Kupon</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTicketEmailPreviewModal() {
        const modal = document.getElementById('ticketEmailPreviewModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const content = document.querySelector('#custom_email_message')?.value || '';
        const name = document.querySelector('input[name="name"]')?.value || 'Jakarta Marathon 2025';
        const ticketEmailUseQr = document.querySelector('input[name="ticket_email_use_qr"]')?.checked ? 1 : 0;
        const templateVal = document.querySelector('input[name="ticket_email_template"]:checked')?.value || 'modern';
        const showJerseyVal = document.querySelector('input[name="ticket_email_show_jersey"]')?.checked ? 1 : 0;
        const showAddonsVal = document.querySelector('input[name="ticket_email_show_addons"]')?.checked ? 1 : 0;
        const showPicVal = document.querySelector('input[name="ticket_email_show_pic"]')?.checked ? 1 : 0;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('eo.events.preview-email-create') }}";
        form.target = 'ticketEmailPreviewFrame';

        const appendInp = (n, v) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = n;
            inp.value = v;
            form.appendChild(inp);
        };

        appendInp('_token', document.querySelector('input[name="_token"]').value);
        appendInp('custom_email_message', content);
        appendInp('name', name);
        appendInp('slug', document.querySelector('input[name="slug"]')?.value || 'jakarta-marathon-2025');
        appendInp('ticket_email_use_qr', ticketEmailUseQr);
        appendInp('ticket_email_template', templateVal);
        appendInp('ticket_email_show_jersey', showJerseyVal);
        appendInp('ticket_email_show_addons', showAddonsVal);
        appendInp('ticket_email_show_pic', showPicVal);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function closeTicketEmailPreviewModal() {
        const modal = document.getElementById('ticketEmailPreviewModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openCreateCouponModal() {
        const modal = document.getElementById('createCouponModal');
        if (!modal) return;
        generateModalCouponCode();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateCouponModal() {
        const modal = document.getElementById('createCouponModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function generateModalCouponCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const input = document.getElementById('modal_coupon_code');
        if (input) input.value = code;
    }

    async function submitModalCoupon(e) {
        e.preventDefault();
        const form = document.getElementById('createCouponModalForm');
        const statusEl = document.getElementById('couponModalStatus');
        const submitBtn = document.getElementById('submitCouponBtn');
        if (!form) return;

        const formData = new FormData(form);
        const data = {};
        formData.forEach((val, key) => { data[key] = val; });
        data._token = document.querySelector('input[name="_token"]').value;

        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Menyimpan...'; }
        if (statusEl) { statusEl.className = 'text-xs p-3 rounded-xl bg-slate-800 text-slate-300'; statusEl.textContent = 'Menyimpan kupon...'; statusEl.classList.remove('hidden'); }

        try {
            const res = await fetch("{{ route('eo.coupons.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': data._token
                },
                body: JSON.stringify(data)
            });

            const resData = await res.json().catch(() => ({}));

            if (res.ok && resData.success) {
                if (resData.coupon && resData.coupon.code) {
                    selectCouponCode(resData.coupon.code);
                }
                if (statusEl) {
                    statusEl.className = 'text-xs p-3 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                    statusEl.textContent = 'Kupon berhasil dibuat dan ditautkan!';
                }
                setTimeout(() => {
                    closeCreateCouponModal();
                    if (statusEl) statusEl.classList.add('hidden');
                }, 800);
            } else {
                const msg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join(', ') : 'Gagal membuat kupon.');
                if (statusEl) {
                    statusEl.className = 'text-xs p-3 rounded-xl bg-red-500/20 text-red-300 border border-red-500/30';
                    statusEl.textContent = msg;
                }
            }
        } catch (err) {
            if (statusEl) {
                statusEl.className = 'text-xs p-3 rounded-xl bg-red-500/20 text-red-300 border border-red-500/30';
                statusEl.textContent = 'Terjadi kesalahan sistem saat membuat kupon.';
            }
        } finally {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Simpan & Tautkan Kupon'; }
        }
    }

    let selectedCoupons = [];

    function initCouponMultiSelect(initialCodesStr) {
        if (initialCodesStr && typeof initialCodesStr === 'string') {
            selectedCoupons = initialCodesStr.split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
        }
        renderCouponBadges();

        const searchInput = document.getElementById('coupon_search_input');
        const dropdown = document.getElementById('coupon_search_dropdown');

        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            debounceTimer = setTimeout(() => searchCouponsAjax(query), 200);
        });

        searchInput.addEventListener('focus', function () {
            searchCouponsAjax(this.value.trim());
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#coupon_tags_container') && !e.target.closest('#coupon_search_dropdown')) {
                if (dropdown) dropdown.classList.add('hidden');
            }
        });
    }

    async function searchCouponsAjax(query) {
        const dropdown = document.getElementById('coupon_search_dropdown');
        if (!dropdown) return;

        try {
            const res = await fetch(`{{ route('eo.coupons.search') }}?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            if (data.success && data.coupons) {
                renderCouponDropdown(data.coupons);
            }
        } catch (e) {
            console.error('Error searching coupons:', e);
        }
    }

    function renderCouponDropdown(coupons) {
        const dropdown = document.getElementById('coupon_search_dropdown');
        if (!dropdown) return;

        if (coupons.length === 0) {
            dropdown.innerHTML = `<div class="p-3 text-xs text-slate-400 text-center">Kupon tidak ditemukan. Klik <strong>+ Buat Kupon</strong> untuk menambah baru.</div>`;
            dropdown.classList.remove('hidden');
            return;
        }

        let html = '';
        coupons.forEach(c => {
            const isSelected = selectedCoupons.includes(c.code.toUpperCase());
            const typeLabel = c.type === 'percent' ? `${parseFloat(c.value)}%` : `Rp ${parseInt(c.value).toLocaleString('id-ID')}`;
            html += `
                <div onclick="selectCouponCode('${c.code.toUpperCase()}')" class="p-3 hover:bg-slate-800 cursor-pointer flex items-center justify-between transition-colors ${isSelected ? 'opacity-50 bg-slate-950/40' : ''}">
                    <div>
                        <span class="font-mono font-bold text-yellow-400 text-xs">${c.code}</span>
                        <span class="text-xs text-slate-400 ml-2">(${typeLabel})</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full ${c.is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300'}">
                        ${c.is_active ? 'Aktif' : 'Non-Aktif'}
                    </span>
                </div>
            `;
        });
        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');
    }

    function selectCouponCode(code) {
        code = code.trim().toUpperCase();
        if (!code) return;
        if (!selectedCoupons.includes(code)) {
            selectedCoupons.push(code);
        }
        renderCouponBadges();
        const searchInput = document.getElementById('coupon_search_input');
        if (searchInput) searchInput.value = '';
        const dropdown = document.getElementById('coupon_search_dropdown');
        if (dropdown) dropdown.classList.add('hidden');
    }

    function removeCouponCode(code) {
        selectedCoupons = selectedCoupons.filter(c => c !== code);
        renderCouponBadges();
    }

    function renderCouponBadges() {
        const badgesContainer = document.getElementById('selected_coupon_badges');
        const hiddenContainer = document.getElementById('promo_codes_hidden_container');
        const mainInput = document.getElementById('promo_code_input');

        if (mainInput) mainInput.value = selectedCoupons.join(', ');

        if (hiddenContainer) {
            hiddenContainer.innerHTML = selectedCoupons.map(c => `<input type="hidden" name="promo_codes[]" value="${c}">`).join('');
        }

        if (badgesContainer) {
            badgesContainer.innerHTML = selectedCoupons.map(c => `
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-400/20 border border-yellow-400/40 text-yellow-300 rounded-lg text-xs font-mono font-bold">
                    <span>${c}</span>
                    <button type="button" onclick="removeCouponCode('${c}')" class="hover:text-white transition-colors text-slate-400 font-bold ml-1">&times;</button>
                </span>
            `).join('');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCouponMultiSelect("{{ old('promo_code') }}");
    });
</script>
@endpush
