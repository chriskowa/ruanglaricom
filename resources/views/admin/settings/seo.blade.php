@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'SEO Settings')

@section('content')
<div class="min-h-screen pt-8 pb-10 px-4 md:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Admin Settings</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">SEO Configuration</h1>
                <div class="text-slate-400 text-sm mt-1">Manage default SEO metadata, Open Graph, and Schema.org settings.</div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-2xl border border-neon/30 bg-neon/10 text-neon px-4 py-3 text-sm font-bold">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-2xl border border-red-500/30 bg-red-500/10 text-red-300 px-4 py-3 text-sm">
                <ul class="list-disc ml-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.seo.settings.update') }}" class="space-y-6">
            @csrf

            <!-- Default Meta Tags -->
            <div class="bg-card border border-slate-700 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Default Meta Tags</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Default Meta Title</label>
                        <input name="seo_meta_title_default" value="{{ old('seo_meta_title_default', $settings['seo_meta_title_default']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white focus:border-neon focus:outline-none">
                        <div class="text-[10px] text-slate-500 mt-1">Fallback title if specific page title is missing.</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Default Meta Description</label>
                        <textarea name="seo_meta_description_default" rows="3" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white focus:border-neon focus:outline-none">{{ old('seo_meta_description_default', $settings['seo_meta_description_default']) }}</textarea>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Default Keywords</label>
                        <input name="seo_meta_keywords_default" value="{{ old('seo_meta_keywords_default', $settings['seo_meta_keywords_default']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white focus:border-neon focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Social Media (OG & Twitter) -->
            <div class="bg-card border border-slate-700 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Social Media Defaults</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Default OG Image URL</label>
                        <input name="seo_og_image_default" value="{{ old('seo_og_image_default', $settings['seo_og_image_default']) }}" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white focus:border-neon focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Twitter Card Type</label>
                        <select name="seo_twitter_card_default" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white focus:border-neon focus:outline-none">
                            <option value="summary" {{ $settings['seo_twitter_card_default'] == 'summary' ? 'selected' : '' }}>Summary</option>
                            <option value="summary_large_image" {{ $settings['seo_twitter_card_default'] == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Advanced / Schema -->
            <div class="bg-card border border-slate-700 rounded-3xl p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Advanced / Schema.org</h3>
                
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Default JSON-LD Schema</label>
                    <textarea name="seo_json_ld_schema_default" rows="6" class="mt-2 w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-3 text-white font-mono text-xs focus:border-neon focus:outline-none">{{ old('seo_json_ld_schema_default', $settings['seo_json_ld_schema_default']) }}</textarea>
                    <div class="text-[10px] text-slate-500 mt-1">Global organization schema or default website schema (JSON format).</div>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
