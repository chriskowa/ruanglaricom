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
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Links (JSON)</h3>
                <div class="text-xs text-slate-500 mb-4">Format: array of objects. Minimal field: title, url. Optional: badge, external.</div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Featured Links JSON</label>
                        <textarea name="vcard_featured_links" rows="6" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white font-mono text-xs">{{ old('vcard_featured_links', $settings['vcard_featured_links']) }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Regular Links JSON</label>
                        <textarea name="vcard_links" rows="10" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white font-mono text-xs">{{ old('vcard_links', $settings['vcard_links']) }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Social Links JSON</label>
                        <textarea name="vcard_social_links" rows="6" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white font-mono text-xs">{{ old('vcard_social_links', $settings['vcard_social_links']) }}</textarea>
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

