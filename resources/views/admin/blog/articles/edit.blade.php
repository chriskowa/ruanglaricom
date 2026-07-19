@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Edit Article')
 
@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.blog.articles.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Articles
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                EDIT ARTICLE
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.blog.articles.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="inline-flex rounded-2xl bg-slate-900/70 border border-slate-700 p-1">
                            <button type="button" data-lang-tab="id" class="lang-tab px-4 py-2 rounded-xl text-sm font-bold transition-colors bg-neon/15 text-neon">Indonesia</button>
                            <button type="button" data-lang-tab="en" class="lang-tab px-4 py-2 rounded-xl text-sm font-bold transition-colors text-slate-300 hover:text-white">English</button>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-bold text-slate-300 mb-2">Slug (Shared)</label>
                            <input type="text" name="slug" value="{{ old('slug', $article->slug) }}" class="w-full md:w-[360px] bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="article-title-slug">
                        </div>
                    </div>

                    <div class="mt-6 space-y-6">
                        <div data-lang-panel="id" class="lang-panel space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Title (ID)</label>
                                <input type="text" name="title" value="{{ old('title', $article->title) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Judul artikel Indonesia">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Excerpt (ID)</label>
                                <textarea name="excerpt" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Ringkasan untuk listing...">{{ old('excerpt', $article->excerpt) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Content (ID)</label>
                                <textarea id="editor_id" class="js-editor" name="content">{{ old('content', $article->content) }}</textarea>
                            </div>
                            <div class="border-t border-slate-700/60 pt-6">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    SEO (ID)
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Title (ID)</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title', $article->meta_title) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Description (ID)</label>
                                        <textarea name="meta_description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">{{ old('meta_description', $article->meta_description) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Keywords (ID)</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $article->meta_keywords) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="run, marathon, training">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Canonical URL (ID)</label>
                                        <input type="url" name="canonical_url" value="{{ old('canonical_url', $article->canonical_url) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div data-lang-panel="en" class="lang-panel hidden space-y-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div class="text-sm font-mono text-slate-400">Opsional, akan fallback ke ID jika kosong.</div>
                                <button type="button" id="btn-copy-id-to-en" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm">Copy ID → EN</button>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Title (EN)</label>
                                <input type="text" name="title_en" value="{{ old('title_en', $article->title_en) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="English title">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Excerpt (EN)</label>
                                <textarea name="excerpt_en" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="English summary...">{{ old('excerpt_en', $article->excerpt_en) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Content (EN)</label>
                                <textarea id="editor_en" class="js-editor" name="content_en">{{ old('content_en', $article->content_en) }}</textarea>
                            </div>
                            <div class="border-t border-slate-700/60 pt-6">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    SEO (EN)
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Title (EN)</label>
                                        <input type="text" name="meta_title_en" value="{{ old('meta_title_en', $article->meta_title_en) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Description (EN)</label>
                                        <textarea name="meta_description_en" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">{{ old('meta_description_en', $article->meta_description_en) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Keywords (EN)</label>
                                        <input type="text" name="meta_keywords_en" value="{{ old('meta_keywords_en', $article->meta_keywords_en) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="run, marathon, training">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Canonical URL (EN)</label>
                                        <input type="url" name="canonical_url_en" value="{{ old('canonical_url_en', $article->canonical_url_en) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Publish</h3>
                    <div class="space-y-4">
                        <button type="button" onclick="openArticleAgent()" class="w-full py-3 rounded-xl bg-slate-800 border border-slate-600 text-white font-semibold hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                            Article Agent
                        </button>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="draft" {{ old('status', $article->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $article->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $article->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 bg-slate-900 border border-slate-700 rounded-xl px-4 py-3">
                            <input type="checkbox" name="is_featured" value="1" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0" {{ old('is_featured', $article->is_featured) ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-300">Featured di Home</span>
                        </label>
                        <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
                            Update Article
                        </button>
                    </div>
                </div>

                <!-- Taxonomy -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Taxonomy</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-bold text-slate-300">Categories</label>
                                <button type="button" id="btn-toggle-category-form" class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-colors text-xs font-bold">Add</button>
                            </div>

                            <div id="category-form" class="hidden mb-3 rounded-xl bg-slate-900 border border-slate-700 p-3 space-y-3">
                                <input type="hidden" id="cat-edit-id" value="">
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-1">Name</label>
                                        <input type="text" id="cat-name" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-neon transition-colors" placeholder="e.g., Training">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-1">Slug (optional)</label>
                                        <input type="text" id="cat-slug" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-neon transition-colors" placeholder="training">
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" id="btn-cancel-category" class="px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-colors text-xs font-bold">Cancel</button>
                                    <button type="button" id="btn-save-category" class="px-3 py-2 rounded-lg bg-neon text-dark hover:bg-neon/90 transition-colors text-xs font-black">Save</button>
                                </div>
                            </div>

                            <div id="category-list" data-store-url="{{ route('admin.blog.categories.store') }}" data-update-template="{{ route('admin.blog.categories.update', ['category' => 0]) }}" class="max-h-56 overflow-y-auto bg-slate-900 border border-slate-700 rounded-xl p-3 space-y-2">
                                @php($selectedCategoryIds = old('categories', $articleCategoryIds ?? []))
                                @foreach($categories as $category)
                                    <div data-row-cat-id="{{ $category->id }}" class="flex items-center justify-between gap-2">
                                        <label class="flex items-center gap-2 min-w-0">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0" {{ in_array($category->id, $selectedCategoryIds) ? 'checked' : '' }}>
                                            <span class="text-sm text-slate-300 truncate cat-name" data-cat-id="{{ $category->id }}">{{ $category->name }}</span>
                                        </label>
                                        <button type="button" class="btn-edit-category px-2 py-1 rounded-lg bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-colors text-[11px] font-bold" data-cat-id="{{ $category->id }}" data-cat-name="{{ $category->name }}" data-cat-slug="{{ $category->slug }}">Edit</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Tags</label>
                            <div class="max-h-40 overflow-y-auto bg-slate-900 border border-slate-700 rounded-xl p-3 mb-2 space-y-2">
                                @foreach($tags as $tag)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0" {{ in_array($tag->id, old('tags', $articleTags)) ? 'checked' : '' }}>
                                        <span class="text-sm text-slate-300">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="text" name="new_tags" value="{{ old('new_tags') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="Add new tags (comma separated)">
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Featured Image</h3>
                    <div class="space-y-4">
                        <div class="relative w-full aspect-video bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl overflow-hidden flex items-center justify-center group hover:border-neon transition-colors">
                            <input type="file" id="featured_image_file" name="featured_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                            <input type="hidden" name="featured_image_url" id="featured_image_url">
                            @if($article->featured_image)
                                <img id="img-preview" src="{{ Str::startsWith($article->featured_image, ['http://', 'https://']) ? $article->featured_image : asset('storage/' . $article->featured_image) }}" class="absolute inset-0 w-full h-full object-cover">
                                <div class="text-center p-4 pointer-events-none hidden" id="img-placeholder">
                            @else
                                <img id="img-preview" class="absolute inset-0 w-full h-full object-cover hidden">
                                <div class="text-center p-4 pointer-events-none" id="img-placeholder">
                            @endif
                                <svg class="w-8 h-8 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs text-slate-400">Click to replace</span>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <button type="button" onclick="openMediaForFeatured()" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700 border border-slate-700 transition-colors">
                                Select from Media Library
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Article Agent Modal --}}
<div id="article-agent-modal" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/80 hidden">
    <div class="bg-slate-900 w-11/12 max-w-3xl max-h-[90vh] rounded-2xl border border-fuchsia-500/40 shadow-2xl flex flex-col overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-slate-800/80">
            <h3 class="text-white font-bold text-lg">Article Agent</h3>
            <button onclick="closeArticleAgent()" class="text-slate-400 hover:text-white transition-colors" aria-label="Tutup">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 space-y-5">
            <div class="flex items-center gap-2 text-xs font-semibold">
                <button type="button" onclick="aaGotoStep(1)" id="aa-step-1" class="px-3 py-1 rounded-full bg-fuchsia-500 text-white hover:opacity-80 transition-opacity">1. Topik</button>
                <span class="text-slate-600">&rarr;</span>
                <button type="button" onclick="aaGotoStep(2)" id="aa-step-2" class="px-3 py-1 rounded-full bg-slate-700 text-slate-300 hover:opacity-80 transition-opacity">2. Pilih</button>
                <span class="text-slate-600">&rarr;</span>
                <button type="button" onclick="aaGotoStep(3)" id="aa-step-3" class="px-3 py-1 rounded-full bg-slate-700 text-slate-300 hover:opacity-80 transition-opacity">3. Tulis</button>
            </div>

            <div id="aa-panel-topic">
                <label class="block text-sm font-semibold text-slate-300 mb-2">Topik / Niche</label>
                <input type="text" id="aa-topic" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-fuchsia-500 transition-colors" placeholder="e.g., Training lari 10K pemula">
                <label class="block text-sm font-semibold text-slate-300 mt-4 mb-2">Strategi</label>
                <select id="aa-strategy" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-fuchsia-500 transition-colors">
                    <option value="free">Bebas (langsung brainstorm)</option>
                    <option value="gap">Cari Celah Baru (hindari topik serupa)</option>
                    <option value="cluster">Pillar & Cluster (topik turunan)</option>
                    <option value="formula">Tiru Formula Judul Teratas</option>
                </select>
                <button type="button" onclick="aaBrainstorm()" id="aa-btn-brainstorm" class="mt-4 w-full py-3 rounded-xl bg-fuchsia-600 text-white font-semibold hover:bg-fuchsia-500 transition-all flex items-center justify-center gap-2">
                    Brainstorm 10 Ide
                </button>
            </div>

            <div id="aa-panel-select" class="hidden">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-white font-semibold">Pilih 1 Ide (atau input manual)</h4>
                    <button type="button" onclick="aaShowManual()" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-colors">Input Manual</button>
                </div>
                <div id="aa-options" class="space-y-2"></div>
                <div id="aa-manual" class="hidden space-y-3 mt-3">
                    <input type="text" id="aa-manual-title" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-white" placeholder="Judul artikel">
                    <input type="text" id="aa-manual-keyword" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-white" placeholder="Kata kunci utama">
                </div>
                <label class="flex items-center gap-2 mt-3 text-sm text-slate-300">
                    <input type="checkbox" id="aa-research-manual" class="rounded bg-slate-800 border-slate-600 text-fuchsia-500">
                    Skip riset web (langsung tulis dari topik)
                </label>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="aaGotoStep(1)" class="flex-1 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white font-semibold hover:bg-slate-700 transition-all">
                        &larr; Topik
                    </button>
                    <button type="button" onclick="aaResearch()" id="aa-btn-research" class="flex-[2] py-3 rounded-xl bg-fuchsia-600 text-white font-semibold hover:bg-fuchsia-500 transition-all flex items-center justify-center gap-2">
                        Riset & Lanjut
                    </button>
                </div>
            </div>

            <div id="aa-panel-write" class="hidden">
                <div id="aa-write-status" class="text-slate-300 text-sm mb-3">Menulis artikel...</div>
                <div id="aa-result-preview" class="hidden bg-slate-950 border border-slate-700 rounded-xl p-4 text-sm text-slate-200 max-h-72 overflow-y-auto"></div>

                {{-- Panel upload gambar per [Gambar: ...] --}}
                <div id="aa-image-panel" class="hidden mt-4 border-t border-slate-700 pt-4">
                    <div class="flex items-center justify-between mb-2 gap-2 flex-wrap">
                        <h4 class="text-sm font-semibold text-white">Gambar Artikel (Prompt AI)</h4>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 hidden sm:inline">Upload tiap gambar, lalu otomatis menggantikan marker di artikel.</span>
                            <button type="button" onclick="aaCopyPrompts()" id="aa-btn-copy-prompts" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Copy Prompt
                            </button>
                        </div>
                    </div>
                    <div id="aa-image-list" class="space-y-3"></div>
                </div>

                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="aaGotoStep(2)" id="aa-btn-back-write" class="flex-1 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white font-semibold hover:bg-slate-700 transition-all hidden">
                        &larr; Pilih
                    </button>
                    <button type="button" onclick="aaApply()" id="aa-btn-apply" class="flex-[2] py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all flex items-center justify-center gap-2 hidden">
                        Generate Ulang Artikel Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/jmsd06m7clya0xqmr43culaqsx8b77z5djnmhavamejsiypc/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '.js-editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image',
        skin: 'oxide-dark',
        content_css: 'dark',
        document_base_url: '{{ url('/') }}/',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        images_upload_url: '{{ route("admin.blog.images.upload") }}',
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: (callback, value, meta) => {
            // Check if we want image or file
            if (meta.filetype === 'image') {
                openMediaModal((url, alt) => {
                    callback(url, { alt: alt });
                });
            }
        },
        setup: (editor) => {
            editor.on('init', () => {
                const imgs = editor.getBody().querySelectorAll('img[src]');
                imgs.forEach((img) => {
                    const raw = (img.getAttribute('src') || '').trim();
                    if (!raw) return;
                    if (/^storage\//i.test(raw)) {
                        img.setAttribute('src', '/' + raw.replace(/^\/+/, ''));
                    }
                });
            });
        },
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '{{ route("admin.blog.images.upload") }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 403) {
                    reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }

                const json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                resolve(json.location);
            };

            xhr.onerror = () => {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            xhr.send(formData);
        })
    });

    const setLangTab = (lang) => {
        const tabs = Array.from(document.querySelectorAll('.lang-tab'));
        const panels = Array.from(document.querySelectorAll('.lang-panel'));
        tabs.forEach((t) => {
            const isActive = t.getAttribute('data-lang-tab') === lang;
            t.classList.toggle('bg-neon/15', isActive);
            t.classList.toggle('text-neon', isActive);
            t.classList.toggle('text-slate-300', !isActive);
        });
        panels.forEach((p) => {
            const isTarget = p.getAttribute('data-lang-panel') === lang;
            p.classList.toggle('hidden', !isTarget);
        });
    };

    document.querySelectorAll('.lang-tab').forEach((btn) => {
        btn.addEventListener('click', () => setLangTab(btn.getAttribute('data-lang-tab')));
    });

    const copyIdToEn = () => {
        const titleId = document.querySelector('input[name="title"]')?.value || '';
        const excerptId = document.querySelector('textarea[name="excerpt"]')?.value || '';
        const metaTitleId = document.querySelector('input[name="meta_title"]')?.value || '';
        const metaDescId = document.querySelector('textarea[name="meta_description"]')?.value || '';
        const metaKeywordsId = document.querySelector('input[name="meta_keywords"]')?.value || '';
        const canonicalId = document.querySelector('input[name="canonical_url"]')?.value || '';

        const titleEn = document.querySelector('input[name="title_en"]');
        const excerptEn = document.querySelector('textarea[name="excerpt_en"]');
        const metaTitleEn = document.querySelector('input[name="meta_title_en"]');
        const metaDescEn = document.querySelector('textarea[name="meta_description_en"]');
        const metaKeywordsEn = document.querySelector('input[name="meta_keywords_en"]');
        const canonicalEn = document.querySelector('input[name="canonical_url_en"]');

        if (titleEn && !titleEn.value) titleEn.value = titleId;
        if (excerptEn && !excerptEn.value) excerptEn.value = excerptId;
        if (metaTitleEn && !metaTitleEn.value) metaTitleEn.value = metaTitleId;
        if (metaDescEn && !metaDescEn.value) metaDescEn.value = metaDescId;
        if (metaKeywordsEn && !metaKeywordsEn.value) metaKeywordsEn.value = metaKeywordsId;
        if (canonicalEn && !canonicalEn.value) canonicalEn.value = canonicalId;

        const idEditor = tinymce.get('editor_id');
        const enEditor = tinymce.get('editor_en');
        if (idEditor && enEditor && !enEditor.getContent()) {
            enEditor.setContent(idEditor.getContent());
        }
    };

    const copyBtn = document.getElementById('btn-copy-id-to-en');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            copyIdToEn();
            setLangTab('en');
        });
    }

    function openMediaModal(onSelectCallback) {
        let modalId = 'media-library-modal';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 hidden';
            modal.innerHTML = `
                <div class="bg-slate-900 w-11/12 h-5/6 rounded-2xl border border-slate-700 shadow-2xl flex flex-col overflow-hidden">
                    <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-slate-800">
                        <h3 class="text-white font-bold">Select Media</h3>
                        <button onclick="document.getElementById('${modalId}').classList.add('hidden')" class="text-slate-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-hidden relative">
                        <iframe src="{{ route('admin.blog.media.index') }}?picker=true" class="w-full h-full border-0"></iframe>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        modal.classList.remove('hidden');

        const messageHandler = (event) => {
            if (event.data && event.data.mceAction === 'insertMedia') {
                onSelectCallback(event.data.url, event.data.alt);
                modal.classList.add('hidden');
                window.removeEventListener('message', messageHandler);
            }
        };
        window.addEventListener('message', messageHandler);
    }

    function openMediaForFeatured() {
        openMediaModal((url, alt) => {
            document.getElementById('featured_image_url').value = url;
            const fileInput = document.getElementById('featured_image_file');
            if (fileInput) fileInput.value = '';
            const imgPreview = document.getElementById('img-preview');
            const imgPlaceholder = document.getElementById('img-placeholder');
            
            imgPreview.src = url;
            imgPreview.classList.remove('hidden');
            if(imgPlaceholder) imgPlaceholder.classList.add('hidden');
        });
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const urlInput = document.getElementById('featured_image_url');
            if (urlInput) urlInput.value = '';
            var reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('img-preview').src = e.target.result;
                document.getElementById('img-preview').classList.remove('hidden');
                document.getElementById('img-placeholder').classList.add('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    const catList = document.getElementById('category-list');
    const catForm = document.getElementById('category-form');
    const catToggleBtn = document.getElementById('btn-toggle-category-form');
    const catCancelBtn = document.getElementById('btn-cancel-category');
    const catSaveBtn = document.getElementById('btn-save-category');
    const catEditId = document.getElementById('cat-edit-id');
    const catName = document.getElementById('cat-name');
    const catSlug = document.getElementById('cat-slug');

    const resetCategoryForm = () => {
        if (catEditId) catEditId.value = '';
        if (catName) catName.value = '';
        if (catSlug) catSlug.value = '';
    };

    const showCategoryForm = (show) => {
        if (!catForm) return;
        catForm.classList.toggle('hidden', !show);
    };

    if (catToggleBtn) {
        catToggleBtn.addEventListener('click', () => {
            const willShow = catForm ? catForm.classList.contains('hidden') : false;
            if (willShow) resetCategoryForm();
            showCategoryForm(willShow);
        });
    }

    if (catCancelBtn) {
        catCancelBtn.addEventListener('click', () => {
            resetCategoryForm();
            showCategoryForm(false);
        });
    }

    const csrfToken = '{{ csrf_token() }}';
    const storeUrl = catList?.getAttribute('data-store-url');
    const updateTemplate = catList?.getAttribute('data-update-template');

    const buildUpdateUrl = (id) => {
        if (!updateTemplate) return null;
        return updateTemplate.replace(/\/0$/, `/${id}`);
    };

    const bindEditButtons = () => {
        document.querySelectorAll('.btn-edit-category').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!catEditId || !catName || !catSlug) return;
                catEditId.value = btn.getAttribute('data-cat-id') || '';
                catName.value = btn.getAttribute('data-cat-name') || '';
                catSlug.value = btn.getAttribute('data-cat-slug') || '';
                showCategoryForm(true);
                catName.focus();
            });
        });
    };

    bindEditButtons();

    const upsertCategoryRow = (cat, shouldCheck) => {
        if (!catList) return;
        const id = String(cat.id);
        let row = catList.querySelector(`[data-row-cat-id="${id}"]`);

        if (!row) {
            row = document.createElement('div');
            row.setAttribute('data-row-cat-id', id);
            row.className = 'flex items-center justify-between gap-2';
            row.innerHTML = `
                <label class="flex items-center gap-2 min-w-0">
                    <input type="checkbox" name="categories[]" value="${id}" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0">
                    <span class="text-sm text-slate-300 truncate cat-name" data-cat-id="${id}"></span>
                </label>
                <button type="button" class="btn-edit-category px-2 py-1 rounded-lg bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-colors text-[11px] font-bold">Edit</button>
            `;
            catList.prepend(row);
        }

        const nameEl = row.querySelector('.cat-name');
        if (nameEl) nameEl.textContent = cat.name || '';

        const editBtn = row.querySelector('.btn-edit-category');
        if (editBtn) {
            editBtn.setAttribute('data-cat-id', id);
            editBtn.setAttribute('data-cat-name', cat.name || '');
            editBtn.setAttribute('data-cat-slug', cat.slug || '');
        }

        if (shouldCheck) {
            const cb = row.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = true;
        }

        bindEditButtons();
    };

    if (catSaveBtn) {
        catSaveBtn.addEventListener('click', async () => {
            if (!catName || !catSlug) return;
            const name = (catName.value || '').trim();
            const slug = (catSlug.value || '').trim();
            const editId = (catEditId?.value || '').trim();

            if (!name) {
                alert('Nama kategori wajib diisi.');
                return;
            }

            const url = editId ? buildUpdateUrl(editId) : storeUrl;
            const method = editId ? 'PUT' : 'POST';

            if (!url) {
                alert('URL kategori tidak ditemukan.');
                return;
            }

            catSaveBtn.disabled = true;

            try {
                const resp = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ name, slug }),
                });

                const data = await resp.json().catch(() => null);
                if (!resp.ok || !data || !data.success) {
                    alert('Gagal menyimpan kategori.');
                    return;
                }

                upsertCategoryRow(data.category, !editId);
                resetCategoryForm();
                showCategoryForm(false);
            } catch (e) {
                alert('Gagal menyimpan kategori.');
            } finally {
                catSaveBtn.disabled = false;
            }
        });
    }

    /* ===================== ARTICLE AGENT ===================== */
    let aaUuid = null;
    let aaOptions = [];
    let aaContent = '';
    let aaSavedTopic = '';
    let aaSavedStrategy = 'free';
    let aaSavedSelection = null;
    const AA_STORAGE_KEY = 'aa_draft_edit_' + ({{ $article->id ?? 0 }} || 'new');

    // Simpan progres agent ke localStorage agar tidak hilang saat refresh/navigasi.
    function aaPersist() {
        try {
            localStorage.setItem(AA_STORAGE_KEY, JSON.stringify({
                uuid: aaUuid,
                options: aaOptions,
                content: aaContent,
                topic: aaSavedTopic,
                strategy: aaSavedStrategy,
                selection: aaSavedSelection
            }));
        } catch (e) { /* abaikan quota error */ }
    }

    function aaClearPersist() {
        try { localStorage.removeItem(AA_STORAGE_KEY); } catch (e) {}
    }

    // Tawarkan pemulihan draft jika ada di localStorage saat modal dibuka.
    function aaMaybeRestore() {
        let saved;
        try { saved = JSON.parse(localStorage.getItem(AA_STORAGE_KEY) || 'null'); } catch (e) { saved = null; }
        if (!saved || !saved.content) return;

        const restore = confirm('Ditemukan draft artikel dari sesi sebelumnya. Pulihkan?');
        if (!restore) { aaClearPersist(); return; }

        aaUuid = saved.uuid || null;
        aaOptions = saved.options || [];
        aaContent = saved.content || '';
        aaSavedTopic = saved.topic || '';
        aaSavedStrategy = saved.strategy || 'free';
        aaSavedSelection = saved.selection || null;

        aaSetStep(3);
        const status = document.getElementById('aa-write-status');
        const preview = document.getElementById('aa-result-preview');
        const btnApply = document.getElementById('aa-btn-apply');
        const backWrite = document.getElementById('aa-btn-back-write');
        status.textContent = 'Draft dipulihkan. Review lalu terapkan.';
        preview.classList.remove('hidden');
        const previewText = aaContent.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 500);
        preview.innerHTML = '<div class="text-slate-300 text-xs">' + previewText + '...</div>';
        btnApply.classList.remove('hidden');
        if (backWrite) backWrite.classList.remove('hidden');

        const prompts = {};
        const re = /\[Gambar:\s*(.*?)\s*\]/gu;
        let m;
        while ((m = re.exec(aaContent)) !== null) {
            if (m[1].trim() !== '') prompts[m[0]] = m[1].trim();
        }
        const imgPanel = document.getElementById('aa-image-panel');
        const imgList = document.getElementById('aa-image-list');
        if (Object.keys(prompts).length > 0) {
            imgPanel.classList.remove('hidden');
            imgList.innerHTML = '';
            Object.keys(prompts).forEach((marker, i) => {
                const wrap = document.createElement('div');
                wrap.className = 'bg-slate-900 border border-slate-700 rounded-xl p-3';
                wrap.innerHTML =
                    '<div class="text-xs text-fuchsia-300 mb-1 font-semibold">Gambar ' + (i + 1) + '</div>' +
                    '<div class="text-xs text-slate-300 mb-2">Prompt: <span class="aa-img-prompt">' + prompts[marker].replace(/</g, '&lt;') + '</span></div>' +
                    '<div class="flex items-center gap-2">' +
                        '<input type="file" accept="image/*" data-marker="' + marker.replace(/"/g, '&quot;') + '" class="aa-img-input text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-fuchsia-600 file:text-white">' +
                        '<span class="aa-img-status text-xs text-slate-400"></span>' +
                    '</div>';
                imgList.appendChild(wrap);
            });
            imgList.querySelectorAll('.aa-img-input').forEach(input => {
                input.addEventListener('change', aaUploadImage);
            });
        }
    }

    const aaModal = () => document.getElementById('article-agent-modal');
    const aaSetStep = (n) => {
        for (let i = 1; i <= 3; i++) {
            const el = document.getElementById('aa-step-' + i);
            if (!el) continue;
            const active = i === n;
            el.className = 'px-3 py-1 rounded-full ' + (active ? 'bg-fuchsia-500 text-white' : 'bg-slate-700 text-slate-300');
        }
        document.getElementById('aa-panel-topic').classList.toggle('hidden', n !== 1);
        document.getElementById('aa-panel-select').classList.toggle('hidden', n !== 2);
        document.getElementById('aa-panel-write').classList.toggle('hidden', n !== 3);
        // Tombol kembali di panel write hanya muncul setelah tulisan selesai.
        const backWrite = document.getElementById('aa-btn-back-write');
        if (backWrite) backWrite.classList.toggle('hidden', n !== 3 || document.getElementById('aa-btn-apply').classList.contains('hidden'));
    };

    // Navigasi bebas antar section — indicator step bisa diklik kapan saja
    // tanpa alert. Penjagaan kehilangan data ada di tombol aksi (brainstorm/research).
    function aaGotoStep(n) {
        aaSetStep(n);
    }

    function openArticleAgent() {
        aaModal().classList.remove('hidden');
        aaUuid = null;
        aaOptions = [];
        aaSetStep(1);
        document.getElementById('aa-topic').value = document.querySelector('input[name="title"]')?.value || '';
        aaMaybeRestore();
    }

    function closeArticleAgent() {
        aaModal().classList.add('hidden');
    }

    async function aaPost(url, payload) {
        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        });
        return resp.json().catch(() => ({ success: false, message: 'Invalid response' }));
    }

    async function aaBrainstorm() {
        const topic = document.getElementById('aa-topic').value.trim();
        const strategy = document.getElementById('aa-strategy').value;
        if (!topic) { alert('Masukkan topik terlebih dahulu.'); return; }
        aaSavedTopic = topic;
        aaSavedStrategy = strategy;

        // Jangan hancurkan artikel yang sudah ditulis jika user hanya ingin
        // melihat kembali step 1. Konfirmasi dulu sebelum brainstorm ulang.
        if (aaContent) {
            const ok = confirm('Brainstorm ulang akan membuang artikel yang sudah dibuat. Lanjutkan?');
            if (!ok) { aaSetStep(3); return; }
        }

        const btn = document.getElementById('aa-btn-brainstorm');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memikirkan...';

        const res = await aaPost('{{ route("admin.blog.articles.agent.brainstorm") }}', { topic, strategy });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-lightbulb"></i> Brainstorm 10 Ide';

        if (!res.success) { alert('Gagal: ' + (res.message || 'Unknown')); return; }

        aaUuid = res.uuid;
        aaOptions = res.options || [];
        const box = document.getElementById('aa-options');
        box.innerHTML = '';
        aaOptions.forEach((opt, i) => {
            const div = document.createElement('label');
            div.className = 'flex items-start gap-3 bg-slate-950 border border-slate-700 rounded-xl p-3 cursor-pointer hover:border-fuchsia-500 transition-colors';
            div.innerHTML = `
                <input type="radio" name="aa-option" value="${i}" class="mt-1 rounded bg-slate-800 border-slate-600 text-fuchsia-500">
                <div class="min-w-0">
                    <div class="text-white font-bold text-sm">${opt.title || ''}</div>
                    <div class="text-fuchsia-300 text-xs">${opt.keyword || ''}</div>
                    <div class="text-slate-400 text-xs mt-1">${opt.summary || ''}</div>
                </div>`;
            box.appendChild(div);
        });
        aaSetStep(2);
    }

    function aaShowManual() {
        document.getElementById('aa-manual').classList.toggle('hidden');
    }

    async function aaResearch() {
        // Riset ulang akan menulis ulang artikel. Konfirmasi jika sudah ada hasil.
        if (aaContent) {
            const ok = confirm('Riset & tulis ulang akan membuang artikel yang sudah dibuat. Lanjutkan?');
            if (!ok) { aaSetStep(3); return; }
        }

        const manualOpen = !document.getElementById('aa-manual').classList.contains('hidden');
        let selection;
        if (manualOpen) {
            const t = document.getElementById('aa-manual-title').value.trim();
            const k = document.getElementById('aa-manual-keyword').value.trim();
            if (!t || !k) { alert('Isi judul & keyword manual.'); return; }
            selection = { title: t, keyword: k };
        } else {
            const checked = document.querySelector('input[name="aa-option"]:checked');
            if (!checked) { alert('Pilih 1 ide terlebih dahulu.'); return; }
            selection = aaOptions[parseInt(checked.value)];
        }
        aaSavedSelection = selection;

        const researchManual = document.getElementById('aa-research-manual').checked;
        const btn = document.getElementById('aa-btn-research');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Riset...';

        const res = await aaPost('{{ route("admin.blog.articles.agent.research") }}', {
            uuid: aaUuid, selection, research_manual: researchManual
        });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-search"></i> Riset & Lanjut';

        if (!res.success) { alert('Gagal riset: ' + (res.message || 'Unknown')); return; }
        aaUuid = res.uuid;
        aaSetStep(3);
        await aaWrite();
    }

    async function aaWrite() {
        const status = document.getElementById('aa-write-status');
        const preview = document.getElementById('aa-result-preview');
        const btnApply = document.getElementById('aa-btn-apply');
        const imgPanel = document.getElementById('aa-image-panel');
        const imgList = document.getElementById('aa-image-list');
        status.classList.remove('hidden');
        preview.classList.add('hidden');
        imgPanel.classList.add('hidden');
        btnApply.classList.add('hidden');
        status.textContent = 'Menulis artikel... (30-60 detik)';

        const res = await aaPost('{{ route("admin.blog.articles.agent.write") }}', { uuid: aaUuid });
        if (!res.success) { status.textContent = 'Gagal: ' + (res.message || 'Unknown'); return; }

        const result = res.result || {};
        const rawResult = (typeof result === 'string') ? result : '';
        aaContent = result.content || rawResult || '';
        status.textContent = 'Selesai! Klik tombol untuk menimpa artikel ini.';
        aaPersist();
        preview.classList.remove('hidden');
        const previewText = aaContent
            ? aaContent.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 500)
            : '(Konten kosong — pastikan model mengembalikan JSON dengan key "content")';
        preview.innerHTML = '<div class="font-bold text-white mb-1">' + (result.title || '') + '</div>' +
            '<div class="text-fuchsia-300 text-xs mb-2">' + (result.meta_description || '') + '</div>' +
            '<div class="text-slate-300 text-xs">' + previewText + (aaContent ? '...' : '') + '</div>';
        btnApply.classList.remove('hidden');
        const backWrite = document.getElementById('aa-btn-back-write');
        if (backWrite) backWrite.classList.remove('hidden');

        const prompts = res.image_prompts || {};
        const markers = Object.keys(prompts);
        if (markers.length > 0) {
            imgPanel.classList.remove('hidden');
            imgList.innerHTML = '';
            markers.forEach((marker, i) => {
                const wrap = document.createElement('div');
                wrap.className = 'bg-slate-900 border border-slate-700 rounded-xl p-3';
                wrap.innerHTML =
                    '<div class="text-xs text-fuchsia-300 mb-1 font-semibold">Gambar ' + (i + 1) + '</div>' +
                    '<div class="text-xs text-slate-300 mb-2">Prompt: <span class="aa-img-prompt">' + prompts[marker].replace(/</g, '&lt;') + '</span></div>' +
                    '<div class="flex items-center gap-2">' +
                        '<input type="file" accept="image/*" data-marker="' + marker.replace(/"/g, '&quot;') + '" class="aa-img-input text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-fuchsia-600 file:text-white">' +
                        '<span class="aa-img-status text-xs text-slate-400"></span>' +
                    '</div>';
                imgList.appendChild(wrap);
            });
            imgList.querySelectorAll('.aa-img-input').forEach(input => {
                input.addEventListener('change', aaUploadImage);
            });
        }
    }

    async function aaUploadImage(e) {
        const input = e.target;
        const marker = input.getAttribute('data-marker');
        const status = input.parentElement.querySelector('.aa-img-status');
        if (!input.files || !input.files[0]) return;

        const fd = new FormData();
        fd.append('file', input.files[0]);
        status.textContent = 'Upload...';
        status.className = 'aa-img-status text-xs text-slate-400';

        try {
            const res = await fetch('{{ route("admin.blog.images.upload") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: fd
            });
            const data = await res.json();
            if (!data.location) throw new Error(data.message || 'Upload gagal');
            const alt = (marker.match(/\[Gambar:\s*(.*?)\s*\]/) || ['', ''])[1].substring(0, 120);
            const imgTag = '<img src="' + data.location + '" alt="' + alt.replace(/"/g, '') + '" style="max-width:100%;border-radius:12px;margin:16px 0;">';
            aaContent = aaContent.split(marker).join(imgTag);
            status.textContent = '✓ Terpasang';
            status.className = 'aa-img-status text-xs text-green-400';
            input.disabled = true;
        } catch (err) {
            status.textContent = '✗ ' + err.message;
            status.className = 'aa-img-status text-xs text-red-400';
        }
    }

    // Salin semua prompt gambar sebagai satu list siap pakai.
    function aaCopyPrompts() {
        const prompts = [];
        document.querySelectorAll('#aa-image-list .aa-img-prompt').forEach(el => {
            const p = el.textContent.trim();
            if (p) prompts.push(p);
        });
        if (prompts.length === 0) { alert('Belum ada prompt gambar.'); return; }
        const text = 'generate multiple gambar ratio 16:9:\n' + prompts.map(p => '- ' + p).join('\n');
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('aa-btn-copy-prompts');
            const old = btn.innerHTML;
            btn.innerHTML = '✓ Tersalin';
            setTimeout(() => { btn.innerHTML = old; }, 1500);
        }).catch(() => {
            const ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select();
            document.execCommand('copy'); document.body.removeChild(ta);
            alert('Prompt tersalin ke clipboard.');
        });
    }

    async function aaApply() {
        const btn = document.getElementById('aa-btn-apply');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        const res = await aaPost('{{ route("admin.blog.articles.agent.apply") }}', {
            uuid: aaUuid,
            article_id: {{ $article->id }},
            content_override: aaContent
        });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Generate Ulang Artikel Ini';

        if (!res.success) { alert('Gagal: ' + (res.message || 'Unknown')); return; }
        aaClearPersist();
        window.location.href = res.redirect;
    }
</script>
@endpush
@endsection
