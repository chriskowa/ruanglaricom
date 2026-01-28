@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'V-Card Settings')

@section('content')
<div class="min-h-screen pt-8 pb-10 px-4 md:px-8" 
    x-data="{
        iconPickerOpen: false,
        iconPickerSearch: '',
        iconPickerCallback: null,
        icons: [
            'address-book', 'address-card', 'align-center', 'align-justify', 'align-left', 'align-right', 'anchor', 
            'angle-double-down', 'angle-double-left', 'angle-double-right', 'angle-double-up', 'angle-down', 'angle-left', 
            'angle-right', 'angle-up', 'archive', 'arrow-alt-circle-down', 'arrow-alt-circle-left', 'arrow-alt-circle-right', 
            'arrow-alt-circle-up', 'arrow-circle-down', 'arrow-circle-left', 'arrow-circle-right', 'arrow-circle-up', 
            'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrows-alt', 'asterisk', 'at', 'award', 'baby', 
            'balance-scale', 'ban', 'band-aid', 'barcode', 'bars', 'basketball-ball', 'battery-full', 'bell', 'bicycle', 
            'birthday-cake', 'bolt', 'bomb', 'book', 'book-open', 'bookmark', 'box', 'briefcase', 'bug', 'building', 
            'bullhorn', 'bullseye', 'bus', 'calculator', 'calendar', 'calendar-alt', 'camera', 'camera-retro', 'car', 
            'caret-down', 'caret-left', 'caret-right', 'caret-up', 'cart-plus', 'chart-area', 'chart-bar', 'chart-line', 
            'chart-pie', 'check', 'check-circle', 'check-square', 'chevron-circle-down', 'chevron-circle-left', 
            'chevron-circle-right', 'chevron-circle-up', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 
            'child', 'circle', 'clipboard', 'clock', 'clone', 'cloud', 'cloud-download-alt', 'cloud-upload-alt', 'code', 
            'cog', 'cogs', 'coins', 'columns', 'comment', 'comment-alt', 'comments', 'compass', 'compress', 'copy', 
            'credit-card', 'crop', 'crosshairs', 'cube', 'cubes', 'cut', 'database', 'desktop', 'dollar-sign', 'download', 
            'dumbbell', 'edit', 'eject', 'ellipsis-h', 'ellipsis-v', 'envelope', 'envelope-open', 'eraser', 'exchange-alt', 
            'exclamation', 'exclamation-circle', 'exclamation-triangle', 'expand', 'external-link-alt', 'eye', 'eye-slash', 
            'fast-backward', 'fast-forward', 'fax', 'feather', 'female', 'file', 'file-alt', 'film', 'filter', 'fingerprint', 
            'fire', 'flag', 'flag-checkered', 'flask', 'folder', 'folder-open', 'font', 'football-ball', 'forward', 
            'gamepad', 'gavel', 'gem', 'gift', 'glass-martini', 'globe', 'graduation-cap', 'h-square', 'hand-holding-heart', 
            'hand-paper', 'hand-point-down', 'hand-point-left', 'hand-point-right', 'hand-point-up', 'hand-rock', 
            'hand-scissors', 'handshake', 'hashtag', 'hdd', 'headphones', 'heart', 'heartbeat', 'history', 'home', 
            'hospital', 'hourglass', 'id-badge', 'id-card', 'image', 'images', 'inbox', 'indent', 'industry', 'info', 
            'info-circle', 'instagram', 'italic', 'key', 'keyboard', 'language', 'laptop', 'leaf', 'lemon', 'life-ring', 
            'lightbulb', 'link', 'list', 'list-alt', 'list-ol', 'list-ul', 'location-arrow', 'lock', 'lock-open', 
            'magic', 'magnet', 'male', 'map', 'map-marker', 'map-marker-alt', 'map-pin', 'medal', 'medkit', 'meh', 
            'microphone', 'minus', 'minus-circle', 'mobile', 'mobile-alt', 'money-bill', 'money-bill-alt', 'moon', 
            'motorcycle', 'mouse-pointer', 'music', 'newspaper', 'paper-plane', 'paperclip', 'paste', 'pause', 
            'pause-circle', 'paw', 'pen', 'pen-alt', 'pen-square', 'pencil-alt', 'percent', 'phone', 'phone-alt', 
            'phone-square', 'phone-volume', 'plane', 'play', 'play-circle', 'plug', 'plus', 'plus-circle', 'plus-square', 
            'podcast', 'power-off', 'print', 'puzzle-piece', 'qrcode', 'question', 'question-circle', 'quote-left', 
            'quote-right', 'random', 'recycle', 'redo', 'redo-alt', 'registered', 'reply', 'reply-all', 'retweet', 
            'road', 'rocket', 'rss', 'running', 'save', 'search', 'search-minus', 'search-plus', 'server', 'share', 
            'share-alt', 'share-square', 'shield-alt', 'ship', 'shopping-bag', 'shopping-basket', 'shopping-cart', 
            'shower', 'sign-in-alt', 'sign-out-alt', 'signal', 'sitemap', 'sliders-h', 'smile', 'sort', 'sort-alpha-down', 
            'sort-alpha-up', 'sort-amount-down', 'sort-amount-up', 'sort-down', 'sort-numeric-down', 'sort-numeric-up', 
            'sort-up', 'space-shuttle', 'spinner', 'square', 'star', 'star-half', 'step-backward', 'step-forward', 
            'stethoscope', 'sticky-note', 'stop', 'stop-circle', 'stopwatch', 'street-view', 'subway', 'suitcase', 
            'sun', 'sync', 'sync-alt', 'table', 'tablet', 'tablet-alt', 'tag', 'tags', 'tasks', 'taxi', 'terminal', 
            'text-height', 'text-width', 'th', 'th-large', 'th-list', 'thumbs-down', 'thumbs-up', 'thumbtack', 
            'ticket-alt', 'times', 'times-circle', 'tint', 'toggle-off', 'toggle-on', 'tools', 'trademark', 'train', 
            'transgender', 'trash', 'trash-alt', 'tree', 'trophy', 'truck', 'tv', 'twitter', 'umbrella', 'underline', 
            'undo', 'undo-alt', 'universal-access', 'university', 'unlock', 'unlock-alt', 'upload', 'user', 'user-circle', 
            'user-md', 'user-plus', 'user-secret', 'user-times', 'users', 'video', 'volume-down', 'volume-off', 
            'volume-up', 'walking', 'wallet', 'whatsapp', 'wheelchair', 'wifi', 'window-close', 'window-maximize', 
            'window-minimize', 'window-restore', 'wrench', 'youtube'
        ],
        openIconPicker(currentValue, callback) {
            this.iconPickerSearch = '';
            this.iconPickerCallback = callback;
            this.iconPickerOpen = true;
        },
        selectIcon(icon) {
            if (this.iconPickerCallback) this.iconPickerCallback(icon);
            this.iconPickerOpen = false;
        },
        get filteredIcons() {
            if (!this.iconPickerSearch) return this.icons;
            return this.icons.filter(i => i.toLowerCase().includes(this.iconPickerSearch.toLowerCase()));
        }
    }">
    
    <div class="max-w-5xl mx-auto">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Admin Settings</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">V-Card</h1>
                <div class="text-slate-400 text-sm mt-1">Edit konten halaman v-card publik.</div>
            </div>
            <a href="{{ route('vcard.index') }}" target="_blank" rel="noopener" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:border-neon/40 hover:text-neon transition">
                Preview ↗
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-2xl border border-neon/30 bg-neon/10 text-neon px-4 py-3 text-sm font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-2xl border border-red-500/30 bg-red-500/10 text-red-300 px-4 py-3 text-sm font-bold">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-2xl border border-red-500/30 bg-red-500/10 text-red-300 px-4 py-3 text-sm">
                <div class="font-black mb-2">Validation error</div>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.vcard.settings.update') }}" class="space-y-6">
            @csrf

            <!-- Theme Colors -->
            <div class="bg-card border border-slate-700 rounded-3xl p-6" x-data="{ 
                bgColor: '{{ old('vcard_bg_color', $settings['vcard_bg_color']) }}',
                accentColor: '{{ old('vcard_accent_color', $settings['vcard_accent_color']) }}',
                textColor: '{{ old('vcard_text_color', $settings['vcard_text_color']) }}'
            }">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Theme Customization</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Background Color</label>
                        <div class="flex items-center gap-3">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden shadow-lg border border-slate-600">
                                <input type="color" x-model="bgColor" class="absolute -top-2 -left-2 w-16 h-16 p-0 cursor-pointer border-0">
                            </div>
                            <input name="vcard_bg_color" type="text" x-model="bgColor" class="w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-white font-mono text-sm uppercase">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Accent Color</label>
                        <div class="flex items-center gap-3">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden shadow-lg border border-slate-600">
                                <input type="color" x-model="accentColor" class="absolute -top-2 -left-2 w-16 h-16 p-0 cursor-pointer border-0">
                            </div>
                            <input name="vcard_accent_color" type="text" x-model="accentColor" class="w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-white font-mono text-sm uppercase">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Text Color</label>
                        <div class="flex items-center gap-3">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden shadow-lg border border-slate-600">
                                <input type="color" x-model="textColor" class="absolute -top-2 -left-2 w-16 h-16 p-0 cursor-pointer border-0">
                            </div>
                            <input name="vcard_text_color" type="text" x-model="textColor" class="w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-white font-mono text-sm uppercase">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meta & Assets -->
            <div class="bg-card border border-slate-700 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Meta \u0026 Assets</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Title</label>
                        <input name="vcard_title" value="{{ old('vcard_title', $settings['vcard_title']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">OG Image URL</label>
                        <input name="vcard_og_image_url" value="{{ old('vcard_og_image_url', $settings['vcard_og_image_url']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Description</label>
                    <textarea name="vcard_description" rows="3" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">{{ old('vcard_description', $settings['vcard_description']) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Logo URL</label>
                        <input name="vcard_logo_url" value="{{ old('vcard_logo_url', $settings['vcard_logo_url']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Background Image URL</label>
                        <input name="vcard_bg_image_url" value="{{ old('vcard_bg_image_url', $settings['vcard_bg_image_url']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                    </div>
                </div>
            </div>

            <div class="bg-card border border-slate-700 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Links Management</h3>
                <div class="text-xs text-slate-500 mb-6">Manage content for Featured, Regular, and Social links.</div>

                <div class="space-y-8">
                    <!-- Featured Links -->
                    <div x-data="{
                        items: {{ $settings['vcard_featured_links'] ?: '[]' }},
                        addItem() {
                            this.items.push({ title: '', url: '', badge: '', icon: 'star', color: '', bg_color: '#1e293b', text_color: '#ffffff' });
                        },
                        removeItem(index) {
                            this.items.splice(index, 1);
                        }
                    }">
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xs font-bold text-neon uppercase tracking-widest">Featured Links (Big Cards)</label>
                            <button type="button" @click="addItem()" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg border border-slate-600 transition flex items-center gap-2">
                                <i class="fas fa-plus"></i> Add Featured
                            </button>
                        </div>
                        
                        <textarea name="vcard_featured_links" x-model="JSON.stringify(items)" class="hidden"></textarea>
                        
                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-slate-900/50 border border-slate-700 p-4 rounded-xl relative group">
                                    <button type="button" @click="removeItem(index)" class="absolute top-2 right-2 text-slate-600 hover:text-red-500 transition p-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pr-6">
                                        <div>
                                            <label class="text-[10px] text-slate-500 uppercase font-bold">Title</label>
                                            <input x-model="item.title" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none" placeholder="Link Title">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-slate-500 uppercase font-bold">URL</label>
                                            <input x-model="item.url" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none" placeholder="https://...">
                                        </div>
                                        <div class="grid grid-cols-3 gap-3 md:col-span-2">
                                            <div>
                                                <label class="text-[10px] text-slate-500 uppercase font-bold">Icon (fa-)</label>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="openIconPicker(item.icon, (icon) => item.icon = icon)" class="w-10 h-10 rounded bg-slate-800 border border-slate-700 hover:border-neon flex items-center justify-center text-slate-400 hover:text-white transition">
                                                        <i :class="'fas fa-' + (item.icon || 'star')"></i>
                                                    </button>
                                                    <input x-model="item.icon" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none" placeholder="star">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-slate-500 uppercase font-bold">Badge</label>
                                                <input x-model="item.badge" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none" placeholder="New, Promo...">
                                            </div>
                                            <div>
                                                <label class="text-[10px] text-slate-500 uppercase font-bold">Color Classes</label>
                                                <input x-model="item.color" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 focus:border-neon focus:outline-none" placeholder="from-pink-500 to-rose-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="items.length === 0" class="text-center py-4 text-slate-600 text-sm italic border border-dashed border-slate-800 rounded-xl">
                                No featured links yet.
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-slate-800"></div>

                    <!-- Regular Links -->
                    <div x-data="{
                        items: {{ $settings['vcard_links'] ?: '[]' }},
                        addItem() {
                            this.items.push({ title: '', url: '', icon: 'link', external: false });
                        },
                        removeItem(index) {
                            this.items.splice(index, 1);
                        }
                    }">
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xs font-bold text-neon uppercase tracking-widest">Regular Links (Grid)</label>
                            <button type="button" @click="addItem()" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg border border-slate-600 transition flex items-center gap-2">
                                <i class="fas fa-plus"></i> Add Link
                            </button>
                        </div>
                        
                        <textarea name="vcard_links" x-model="JSON.stringify(items)" class="hidden"></textarea>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-slate-900/50 border border-slate-700 p-3 rounded-xl relative group">
                                    <button type="button" @click="removeItem(index)" class="absolute top-2 right-2 text-slate-600 hover:text-red-500 transition p-1 z-10">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="space-y-2 pr-6">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="openIconPicker(item.icon, (icon) => item.icon = icon)" class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0 border border-transparent hover:border-neon transition">
                                                <i :class="'fas fa-' + (item.icon || 'link')"></i>
                                            </button>
                                            <input x-model="item.title" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-white focus:border-neon focus:outline-none" placeholder="Link Title">
                                        </div>
                                        <input x-model="item.url" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-300 focus:border-neon focus:outline-none" placeholder="https://...">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 w-1/2">
                                                <span class="text-[10px] text-slate-500 uppercase font-bold">Icon:</span>
                                                <input x-model="item.icon" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-slate-300 focus:border-neon focus:outline-none" placeholder="link">
                                            </div>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" x-model="item.external" class="rounded bg-slate-800 border-slate-700 text-neon focus:ring-neon">
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">New Tab</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="items.length === 0" class="text-center py-4 text-slate-600 text-sm italic border border-dashed border-slate-800 rounded-xl mt-2">
                            No regular links yet.
                        </div>
                    </div>

                    <div class="h-px bg-slate-800"></div>

                    <!-- Social Links -->
                    <div x-data="{
                        items: {{ $settings['vcard_social_links'] ?: '[]' }},
                        addItem() {
                            this.items.push({ title: '', url: '', icon: 'link' });
                        },
                        removeItem(index) {
                            this.items.splice(index, 1);
                        }
                    }">
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xs font-bold text-neon uppercase tracking-widest">Social Footer Icons</label>
                            <button type="button" @click="addItem()" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg border border-slate-600 transition flex items-center gap-2">
                                <i class="fas fa-plus"></i> Add Social
                            </button>
                        </div>
                        
                        <textarea name="vcard_social_links" x-model="JSON.stringify(items)" class="hidden"></textarea>
                        
                        <div class="flex flex-wrap gap-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-slate-900/50 border border-slate-700 p-3 rounded-xl relative group w-full md:w-[calc(50%-0.5rem)] lg:w-[calc(33.33%-0.5rem)]">
                                    <button type="button" @click="removeItem(index)" class="absolute top-2 right-2 text-slate-600 hover:text-red-500 transition p-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="space-y-2 pr-6">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="openIconPicker(item.icon, (icon) => item.icon = icon)" class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0 border border-transparent hover:border-neon transition">
                                                <i :class="'fab fa-' + (item.icon || 'link')"></i>
                                            </button>
                                            <input x-model="item.title" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-white focus:border-neon focus:outline-none" placeholder="Platform Name">
                                        </div>
                                        <input x-model="item.url" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-300 focus:border-neon focus:outline-none" placeholder="https://...">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-500 uppercase font-bold">Icon (fab fa-):</span>
                                            <input x-model="item.icon" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-slate-300 focus:border-neon focus:outline-none" placeholder="instagram">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="items.length === 0" class="text-center py-4 text-slate-600 text-sm italic border border-dashed border-slate-800 rounded-xl mt-2">
                            No social links yet.
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-slate-700 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Ads Slot</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ads URL</label>
                        <input name="vcard_ads_url" value="{{ old('vcard_ads_url', $settings['vcard_ads_url']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ads Title</label>
                        <input name="vcard_ads_title" value="{{ old('vcard_ads_title', $settings['vcard_ads_title']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ads Description</label>
                    <input name="vcard_ads_description" value="{{ old('vcard_ads_description', $settings['vcard_ads_description']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button class="px-6 py-3 rounded-2xl bg-neon text-dark font-black hover:bg-neon/90 transition">Save Settings</button>
            </div>
        </form>
    </div>

    <!-- Icon Picker Modal -->
    <div x-show="iconPickerOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.window.escape="iconPickerOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;">
        
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="iconPickerOpen = false"></div>
        
        <div class="relative bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl">
            <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Select Icon</h3>
                <button @click="iconPickerOpen = false" class="text-slate-400 hover:text-white transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 pb-2">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input x-model="iconPickerSearch" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white focus:border-neon focus:outline-none" placeholder="Search icons (e.g. facebook, heart, star)..." autofocus>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                <template x-for="icon in filteredIcons" :key="icon">
                    <button @click="selectIcon(icon)" class="aspect-square flex flex-col items-center justify-center gap-2 p-2 rounded-xl border border-slate-700 hover:border-neon hover:bg-slate-800 transition group">
                        <i :class="'fas fa-' + icon" class="text-xl text-slate-400 group-hover:text-neon transition-colors"></i>
                        <span class="text-[10px] text-slate-500 truncate w-full text-center group-hover:text-white" x-text="icon"></span>
                    </button>
                </template>
                <div x-show="filteredIcons.length === 0" class="col-span-full text-center py-10 text-slate-500">
                    No icons found for "<span x-text="iconPickerSearch"></span>"
                </div>
            </div>
        </div>
    </div>
</div>
@endsection