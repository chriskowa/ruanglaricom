@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'V-Card Settings')

@section('content')
<div class="min-h-screen pt-8 pb-10 px-4 md:px-8">
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
                            this.items.push({ title: '', url: '', badge: '', icon: 'star', color: '' });
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
                                                    <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-slate-400">
                                                        <i :class="'fas fa-' + (item.icon || 'star')"></i>
                                                    </div>
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
                                            <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0">
                                                <i :class="'fas fa-' + (item.icon || 'link')"></i>
                                            </div>
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
                                            <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0">
                                                <i :class="'fab fa-' + (item.icon || 'link')"></i>
                                            </div>
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
</div>
@endsection

