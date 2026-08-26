@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Create Article') 

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.blog.articles.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Articles
            </a>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>CREATE ARTICLE</span>
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.blog.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- AI Generator -->
                <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-white flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-neon/10 text-neon border border-neon/20">
                                <i class="fas fa-robot text-sm"></i>
                            </span>
                            AI Article Generator
                        </h3>
                        <span class="text-xs text-slate-400 italic">Riset & Optimasi SEO</span>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Topic or Keyword</label>
                                <input type="text" id="ai-topic" onkeyup="syncPrompt()" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="e.g., Tips Lari Marathon Pemula">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Reference URL (Optional Rewrite)</label>
                                <input type="url" id="ai-url" onkeyup="syncPrompt()" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="https://external-article.com/...">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">AI Prompt Preview</label>
                            <textarea id="ai-prompt-preview" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-300 text-xs font-mono focus:outline-none focus:border-neon transition-colors" readonly></textarea>
                        </div>

                        <div class="flex flex-wrap justify-end gap-3 pt-1">
                            <button type="button" onclick="openArticleAgent()" class="px-5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-semibold hover:bg-slate-700 transition-all text-sm flex items-center gap-2">
                                <i class="fas fa-brain text-neon text-xs"></i>
                                Article Agent
                            </button>
                            <button type="button" onclick="generateArticle()" id="btn-generate-ai" class="px-6 py-2.5 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90 transition-all text-sm flex items-center gap-2 shadow-lg shadow-neon/10">
                                <i class="fas fa-bolt text-xs"></i>
                                Generate Article
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 italic text-center">AI akan melakukan riset faktual & optimasi SEO. Hasil akan mengisi form di bawah.</p>
                    </div>
                    <!-- Loading State -->
                    <div id="ai-loading" class="hidden absolute inset-0 bg-slate-950/85 backdrop-blur-sm z-20 flex flex-col items-center justify-center text-center p-6">
                        <div class="w-10 h-10 border-3 border-neon border-t-transparent rounded-full animate-spin mb-3"></div>
                        <h4 class="text-white font-bold mb-1 text-sm">Menyusun Artikel...</h4>
                        <p class="text-slate-400 text-xs max-w-sm">Proses memakan waktu sekitar 30-60 detik karena AI melakukan riset faktual.</p>
                    </div>
                </div>

                <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-800">
                        <div class="inline-flex rounded-xl bg-slate-950 border border-slate-800 p-1">
                            <button type="button" data-lang-tab="id" class="lang-tab px-4 py-2 rounded-lg text-xs font-bold transition-all bg-neon text-dark shadow-sm">Bahasa Indonesia</button>
                            <button type="button" data-lang-tab="en" class="lang-tab px-4 py-2 rounded-lg text-xs font-bold transition-all text-slate-400 hover:text-white">English</button>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Slug (Shared)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full md:w-[320px] bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="article-title-slug">
                            <div class="text-[11px] text-slate-400 mt-1">Kosongkan untuk auto-generate dari judul Indonesia.</div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-6">
                        <div data-lang-panel="id" class="lang-panel space-y-6">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Title (ID)</label>
                                <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="Judul artikel Indonesia">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Excerpt (ID)</label>
                                <textarea name="excerpt" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="Ringkasan pendek artikel...">{{ old('excerpt') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Content (ID)</label>
                                <textarea id="editor_id" class="js-editor" name="content">{{ old('content') }}</textarea>
                            </div>
                            <div class="border-t border-slate-800 pt-6">
                                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    SEO & Metadata (ID)
                                </h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-neon mb-1.5 flex items-center justify-between">
                                                <span>Focus Keyword (Utama - ID)</span>
                                                <span class="text-[10px] text-slate-400 font-normal">Target ranking #1</span>
                                            </label>
                                            <input type="text" name="focus_keyword" id="seo_focus_keyword" value="{{ old('focus_keyword') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="e.g. sepatu lari marathon">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                                                <span>Secondary Keywords (LSI - ID)</span>
                                                <span class="text-[10px] text-slate-400 font-normal">Pisahkan koma</span>
                                            </label>
                                            <input type="text" name="secondary_keywords" id="seo_secondary_keywords" value="{{ old('secondary_keywords') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="e.g. rekomendasi sepatu lari, sepatu road race">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Meta Title (ID)</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Meta Description (ID)</label>
                                        <textarea name="meta_description" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors">{{ old('meta_description') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Meta Keywords Fallback (ID - Opsional)</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="Otomatis terisi dari Focus + Secondary Keywords jika kosong">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Canonical URL (ID)</label>
                                        <input type="url" name="canonical_url" value="{{ old('canonical_url') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div data-lang-panel="en" class="lang-panel hidden space-y-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-slate-950 border border-slate-800 rounded-xl p-3">
                                <div class="text-xs text-slate-400">Versi Bahasa Inggris (opsional, akan fallback ke ID jika kosong).</div>
                                <button type="button" id="btn-copy-id-to-en" class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 transition-all font-semibold text-xs flex items-center gap-1.5">
                                    <i class="fas fa-copy text-[10px]"></i> Copy ID → EN
                                </button>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Title (EN)</label>
                                <input type="text" name="title_en" value="{{ old('title_en') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="English title">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Excerpt (EN)</label>
                                <textarea name="excerpt_en" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="English summary...">{{ old('excerpt_en') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Content (EN)</label>
                                <textarea id="editor_en" class="js-editor" name="content_en">{{ old('content_en') }}</textarea>
                            </div>
                            <div class="border-t border-slate-800 pt-6">
                                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    SEO & Metadata (EN)
                                </h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-neon mb-1.5 flex items-center justify-between">
                                                <span>Focus Keyword (Primary - EN)</span>
                                                <span class="text-[10px] text-slate-400 font-normal">Target ranking #1</span>
                                            </label>
                                            <input type="text" name="focus_keyword_en" id="seo_focus_keyword_en" value="{{ old('focus_keyword_en') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="e.g. best marathon running shoes">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                                                <span>Secondary Keywords (LSI - EN)</span>
                                                <span class="text-[10px] text-slate-400 font-normal">Comma separated</span>
                                            </label>
                                            <input type="text" name="secondary_keywords_en" id="seo_secondary_keywords_en" value="{{ old('secondary_keywords_en') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="e.g. running shoe review, road race shoes">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Meta Title (EN)</label>
                                        <input type="text" name="meta_title_en" value="{{ old('meta_title_en') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Meta Description (EN)</label>
                                        <textarea name="meta_description_en" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors">{{ old('meta_description_en') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Meta Keywords Fallback (EN - Optional)</label>
                                        <input type="text" name="meta_keywords_en" value="{{ old('meta_keywords_en') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="Auto-filled from Focus + Secondary Keywords">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Canonical URL (EN)</label>
                                        <input type="url" name="canonical_url_en" value="{{ old('canonical_url_en') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
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
                <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Publish</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status</label>
                            <select name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="rounded bg-slate-800 border-slate-700 text-neon focus:ring-0 accent-lime-400" {{ old('is_featured') ? 'checked' : '' }}>
                            <span class="text-xs font-semibold text-slate-300">Featured di Home</span>
                        </label>
                        <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/10 text-sm">
                            Save Article
                        </button>
                    </div>
                </div>

                <!-- Live SEO Score & Content Auditor Widget -->
                <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-neon"></i> Live SEO Audit
                        </h3>
                        <div id="seo-score-badge" class="px-2.5 py-1 rounded-lg text-xs font-black bg-slate-800 text-slate-300 border border-slate-700">
                            <span id="seo-score-num">0</span> / 100
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-950 rounded-full h-2 mb-3 overflow-hidden border border-slate-800">
                        <div id="seo-progress-bar" class="h-full bg-red-500 transition-all duration-300" style="width: 0%"></div>
                    </div>

                    <div id="seo-verdict" class="text-xs font-bold text-slate-300 mb-4 pb-3 border-b border-slate-800 leading-relaxed">
                        Masukkan Focus Keyword untuk memulai audit SEO.
                    </div>

                    <!-- Checklist -->
                    <div class="space-y-2.5 text-xs" id="seo-audit-checklist">
                        <div data-audit="focus-title" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Focus Keyword di Judul (Title H1)</span>
                        </div>
                        <div data-audit="focus-slug" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Focus Keyword di URL Slug</span>
                        </div>
                        <div data-audit="focus-desc" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Focus Keyword di Meta Description</span>
                        </div>
                        <div data-audit="focus-lead" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Focus Keyword di Paragraf Pertama (Lead)</span>
                        </div>
                        <div data-audit="focus-heading" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Focus Keyword di Subheading (H2 / H3)</span>
                        </div>
                        <div data-audit="title-len" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Panjang Judul Ideal (40 – 65 Karakter)</span>
                        </div>
                        <div data-audit="desc-len" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Panjang Meta Description (120 – 165 Karakter)</span>
                        </div>
                        <div data-audit="word-count" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Panjang Konten Artikel (Min. 600 kata)</span>
                        </div>
                        <div data-audit="density" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Kepadatan Keyword (0.8% – 2.5%)</span>
                        </div>
                        <div data-audit="secondary" class="flex items-start gap-2 text-slate-400">
                            <i class="fa-regular fa-circle mt-0.5 shrink-0"></i>
                            <span>Secondary Keywords Digunakan di Konten</span>
                        </div>
                    </div>
                </div>

                <!-- Taxonomy -->
                <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Taxonomy</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-slate-300">Categories</label>
                                <button type="button" id="btn-toggle-category-form" class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 transition-colors text-xs font-semibold">Add</button>
                            </div>

                            <div id="category-form" class="hidden mb-3 rounded-xl bg-slate-950 border border-slate-800 p-3 space-y-3">
                                <input type="hidden" id="cat-edit-id" value="">
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-400 mb-1">Name</label>
                                        <input type="text" id="cat-name" class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-neon transition-colors" placeholder="e.g., Training">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-400 mb-1">Slug (optional)</label>
                                        <input type="text" id="cat-slug" class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-neon transition-colors" placeholder="training">
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" id="btn-cancel-category" class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 transition-colors text-xs font-semibold">Cancel</button>
                                    <button type="button" id="btn-save-category" class="px-3 py-1.5 rounded-lg bg-neon text-dark hover:bg-neon/90 transition-colors text-xs font-bold">Save</button>
                                </div>
                            </div>

                            <div id="category-list" data-store-url="{{ route('admin.blog.categories.store') }}" data-update-template="{{ route('admin.blog.categories.update', ['category' => 0]) }}" class="max-h-56 overflow-y-auto bg-slate-950 border border-slate-800 rounded-xl p-3 space-y-2">
                                @foreach($categories as $category)
                                    <div data-row-cat-id="{{ $category->id }}" class="flex items-center justify-between gap-2">
                                        <label class="flex items-center gap-2 min-w-0 cursor-pointer">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="rounded bg-slate-800 border-slate-700 text-neon focus:ring-0 accent-lime-400" {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                                            <span class="text-xs text-slate-300 truncate cat-name" data-cat-id="{{ $category->id }}">{{ $category->name }}</span>
                                        </label>
                                        <button type="button" class="btn-edit-category px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors text-[11px] font-medium" data-cat-id="{{ $category->id }}" data-cat-name="{{ $category->name }}" data-cat-slug="{{ $category->slug }}">Edit</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-2">Tags</label>
                            <div class="max-h-40 overflow-y-auto bg-slate-950 border border-slate-800 rounded-xl p-3 mb-2 space-y-2">
                                @foreach($tags as $tag)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="rounded bg-slate-800 border-slate-700 text-neon focus:ring-0 accent-lime-400">
                                        <span class="text-xs text-slate-300">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="text" name="new_tags" value="{{ old('new_tags') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-neon transition-colors" placeholder="Add new tags (comma separated)">
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Featured Image</h3>
                    <div class="space-y-4">
                        <div class="relative w-full aspect-video bg-slate-950 border-2 border-dashed border-slate-800 rounded-xl overflow-hidden flex items-center justify-center group hover:border-neon transition-colors">
                            <input type="file" id="featured_image_file" name="featured_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                            <input type="hidden" name="featured_image_url" id="featured_image_url">
                            <img id="img-preview" class="absolute inset-0 w-full h-full object-cover hidden">
                            <div class="text-center p-4 pointer-events-none" id="img-placeholder">
                                <svg class="w-8 h-8 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs text-slate-400">Click to upload image</span>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <button type="button" onclick="openMediaForFeatured()" class="px-4 py-2 bg-slate-800 text-white text-xs rounded-xl hover:bg-slate-700 border border-slate-700 transition-colors font-semibold">
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
<div id="article-agent-modal" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/80 backdrop-blur-sm hidden">
    <div class="bg-slate-900 w-11/12 max-w-3xl max-h-[90vh] rounded-2xl border border-slate-700/80 shadow-2xl flex flex-col overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-800 bg-slate-800/60">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-neon/10 border border-neon/20 flex items-center justify-center text-neon">
                    <i class="fas fa-brain text-sm"></i>
                </div>
                <h3 class="text-white font-bold text-base">Article Agent Assistant</h3>
            </div>
            <button onclick="closeArticleAgent()" class="text-slate-400 hover:text-white transition-colors" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            {{-- Step indicator --}}
            <div class="flex items-center gap-2 text-xs font-semibold pb-2 border-b border-slate-800">
                <button type="button" onclick="aaGotoStep(1)" id="aa-step-1" class="px-3.5 py-1.5 rounded-lg bg-neon text-dark font-bold shadow-sm transition-all">1. Topik & Strategi</button>
                <span class="text-slate-600">&rarr;</span>
                <button type="button" onclick="aaGotoStep(2)" id="aa-step-2" class="px-3.5 py-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-all">2. Pilih Ide</button>
                <span class="text-slate-600">&rarr;</span>
                <button type="button" onclick="aaGotoStep(3)" id="aa-step-3" class="px-3.5 py-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-all">3. Tulis Artikel</button>
            </div>

            {{-- Step 1: Topic --}}
            <div id="aa-panel-topic" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Topik / Keyword Utama (Maks. 255 Karakter)</label>
                    <input type="text" id="aa-topic" maxlength="255" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600" placeholder="e.g., Training lari 10K pemula atau Borobudur Marathon 2026">
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Cuplikan Berita Realtime / Isu Viral (Threads, Instagram, News Paste)</label>
                        <span class="text-[10px] text-neon font-mono">Opsional / Berita Panjang</span>
                    </div>
                    <textarea id="aa-raw-news" rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors placeholder:text-slate-600 leading-relaxed custom-scrollbar" placeholder="Tempelkan (paste) cuplikan berita realtime, postingan Threads, Instagram, atau rincian berita panjang di sini..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Strategi Konten</label>
                    <select id="aa-strategy" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                        <option value="free">Bebas (langsung brainstorm)</option>
                        <option value="gap">Cari Celah Baru (hindari topik serupa)</option>
                        <option value="cluster">Pillar & Cluster (topik turunan)</option>
                        <option value="formula">Formula Google Discover 2026 (E-E-A-T & Anti-Clickbait)</option>
                    </select>
                </div>
                <button type="button" onclick="aaBrainstorm()" id="aa-btn-brainstorm" class="mt-2 w-full py-3 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90 transition-all flex items-center justify-center gap-2 shadow-lg shadow-neon/10 text-sm">
                    <i class="fas fa-lightbulb text-xs"></i> Brainstorm 10 Ide
                </button>
            </div>

            {{-- Step 2: Select --}}
            <div id="aa-panel-select" class="hidden space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-white font-semibold text-sm">Pilih 1 Ide (atau input manual)</h4>
                    <button type="button" onclick="aaShowManual()" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 transition-colors">Input Manual</button>
                </div>
                <div id="aa-options" class="space-y-2.5 max-h-72 overflow-y-auto pr-1"></div>
                <div id="aa-manual" class="hidden space-y-3 p-4 bg-slate-950 border border-slate-800 rounded-xl">
                    <input type="text" id="aa-manual-title" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon placeholder:text-slate-500" placeholder="Judul artikel">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input type="text" id="aa-manual-keyword" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon placeholder:text-slate-500" placeholder="Focus Keyword (Utama)">
                        <input type="text" id="aa-manual-secondary" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-neon placeholder:text-slate-500" placeholder="Secondary Keywords (pisahkan koma)">
                    </div>
                </div>
                <label class="flex items-center gap-2.5 text-xs text-slate-300">
                    <input type="checkbox" id="aa-research-manual" class="rounded bg-slate-800 border-slate-700 text-neon focus:ring-0 accent-lime-400">
                    Skip riset web (langsung tulis dari topik)
                </label>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="aaGotoStep(1)" class="flex-1 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm font-semibold hover:bg-slate-700 transition-all">
                        &larr; Kembali
                    </button>
                    <button type="button" onclick="aaResearch()" id="aa-btn-research" class="flex-[2] py-2.5 rounded-xl bg-neon text-dark text-sm font-bold hover:bg-neon/90 transition-all flex items-center justify-center gap-2 shadow-lg shadow-neon/10">
                        <i class="fas fa-search text-xs"></i> Riset & Lanjut
                    </button>
                </div>
            </div>

            {{-- Step 3: Write --}}
            <div id="aa-panel-write" class="hidden space-y-4">
                <div id="aa-write-status" class="text-slate-300 text-sm">Menulis artikel...</div>
                <div id="aa-result-preview" class="hidden bg-slate-950 border border-slate-800 rounded-xl p-4 text-sm text-slate-200 max-h-72 overflow-y-auto"></div>

                {{-- Panel upload gambar per [Gambar: ...] --}}
                <div id="aa-image-panel" class="hidden border-t border-slate-800 pt-4">
                    <div class="flex items-center justify-between mb-3 gap-2 flex-wrap">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Gambar Artikel (Prompt AI)</h4>
                        <button type="button" onclick="aaCopyPrompts()" id="aa-btn-copy-prompts" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Copy Prompt
                        </button>
                    </div>
                    <div id="aa-image-list" class="space-y-3"></div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="aaGotoStep(2)" id="aa-btn-back-write" class="flex-1 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm font-semibold hover:bg-slate-700 transition-all hidden">
                        &larr; Pilih Ide
                    </button>
                    <button type="button" onclick="aaApply()" id="aa-btn-apply" class="flex-[2] py-2.5 rounded-xl bg-neon text-dark text-sm font-bold hover:bg-neon/90 transition-all flex items-center justify-center gap-2 hidden shadow-lg shadow-neon/10">
                        <i class="fas fa-check text-xs"></i> Terapkan ke Form & Simpan Draft
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
        }),
        setup: (editor) => {
            editor.on('input change keyup SetContent', () => {
                runLiveSeoAudit();
            });
        }
    });

    function runLiveSeoAudit() {
        const focusKw = (document.getElementById('seo_focus_keyword')?.value || '').toLowerCase().trim();
        const secKwsRaw = (document.getElementById('seo_secondary_keywords')?.value || '').toLowerCase().trim();
        const title = (document.querySelector('input[name="title"]')?.value || '').trim();
        const slug = (document.querySelector('input[name="slug"]')?.value || '').trim();
        const metaDesc = (document.querySelector('textarea[name="meta_description"]')?.value || '').trim();
        
        let contentHtml = '';
        if (typeof tinymce !== 'undefined' && tinymce.get('editor_id')) {
            contentHtml = tinymce.get('editor_id').getContent();
        } else {
            contentHtml = document.getElementById('editor_id')?.value || '';
        }
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = contentHtml;
        const contentText = (tempDiv.textContent || tempDiv.innerText || '').trim();
        const words = contentText.split(/\s+/).filter(w => w.length > 0);
        const wordCount = words.length;

        let score = 0;

        const setItemStatus = (key, passed) => {
            const item = document.querySelector(`[data-audit="${key}"]`);
            if (!item) return;
            const icon = item.querySelector('i');
            
            if (passed) {
                item.className = 'flex items-start gap-2 text-emerald-400 font-medium';
                if (icon) icon.className = 'fa-solid fa-circle-check mt-0.5 text-emerald-400 shrink-0';
            } else {
                item.className = 'flex items-start gap-2 text-slate-400';
                if (icon) icon.className = 'fa-regular fa-circle mt-0.5 text-slate-500 shrink-0';
            }
        };

        if (!focusKw) {
            document.getElementById('seo-score-num').textContent = '0';
            document.getElementById('seo-progress-bar').style.width = '0%';
            document.getElementById('seo-verdict').innerHTML = 'Masukkan Focus Keyword untuk mengaktifkan audit SEO.';
            document.querySelectorAll('#seo-audit-checklist [data-audit]').forEach(el => {
                el.className = 'flex items-start gap-2 text-slate-500';
                const i = el.querySelector('i');
                if (i) i.className = 'fa-regular fa-circle mt-0.5 text-slate-600 shrink-0';
            });
            return;
        }

        // 1. Focus in Title (15 pts)
        const hasFocusInTitle = title.toLowerCase().includes(focusKw);
        if (hasFocusInTitle) score += 15;
        setItemStatus('focus-title', hasFocusInTitle);

        // 2. Focus in Slug (10 pts)
        const slugNorm = slug.toLowerCase().replace(/-/g, ' ');
        const hasFocusInSlug = slugNorm.includes(focusKw.replace(/\s+/g, ' '));
        if (hasFocusInSlug) score += 10;
        setItemStatus('focus-slug', hasFocusInSlug);

        // 3. Focus in Meta Description (10 pts)
        const hasFocusInDesc = metaDesc.toLowerCase().includes(focusKw);
        if (hasFocusInDesc) score += 10;
        setItemStatus('focus-desc', hasFocusInDesc);

        // 4. Focus in Lead Paragraph (15 pts)
        const firstP = (tempDiv.querySelector('p')?.textContent || '').toLowerCase();
        const hasFocusInLead = firstP.includes(focusKw);
        if (hasFocusInLead) score += 15;
        setItemStatus('focus-lead', hasFocusInLead);

        // 5. Focus in Headings H2/H3 (10 pts)
        const headings = Array.from(tempDiv.querySelectorAll('h2, h3')).map(h => h.textContent.toLowerCase());
        const hasFocusInHeadings = headings.some(h => h.includes(focusKw));
        if (hasFocusInHeadings) score += 10;
        setItemStatus('focus-heading', hasFocusInHeadings);

        // 6. Title Length 40 - 65 (10 pts)
        const titleLenOk = title.length >= 40 && title.length <= 65;
        if (titleLenOk) score += 10;
        setItemStatus('title-len', titleLenOk);

        // 7. Desc Length 120 - 165 (10 pts)
        const descLenOk = metaDesc.length >= 120 && metaDesc.length <= 165;
        if (descLenOk) score += 10;
        setItemStatus('desc-len', descLenOk);

        // 8. Word Count >= 600 (10 pts)
        const wordCountOk = wordCount >= 600;
        if (wordCountOk) score += 10;
        else if (wordCount >= 300) score += 5;
        setItemStatus('word-count', wordCountOk);

        // 9. Keyword Density (0.8% - 2.5%) (10 pts)
        let kwCount = 0;
        if (wordCount > 0) {
            const regex = new RegExp(focusKw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            const matches = contentText.match(regex);
            kwCount = matches ? matches.length : 0;
        }
        const kwDensity = wordCount > 0 ? ((kwCount * focusKw.split(/\s+/).length) / wordCount) * 100 : 0;
        const densityOk = kwDensity >= 0.8 && kwDensity <= 2.5;
        if (densityOk) score += 10;
        else if (kwCount >= 2) score += 5;
        setItemStatus('density', densityOk);

        // 10. Secondary Keywords in Content (10 pts)
        let hasSecOk = false;
        if (secKwsRaw) {
            const secList = secKwsRaw.split(',').map(s => s.trim()).filter(s => s.length > 0);
            const secFound = secList.filter(s => contentText.toLowerCase().includes(s));
            hasSecOk = secFound.length > 0;
            if (hasSecOk) score += 10;
        }
        setItemStatus('secondary', hasSecOk);

        // Update Overall Score UI
        document.getElementById('seo-score-num').textContent = score;
        const prog = document.getElementById('seo-progress-bar');
        const badge = document.getElementById('seo-score-badge');
        const verdict = document.getElementById('seo-verdict');

        prog.style.width = score + '%';

        if (score >= 80) {
            prog.className = 'h-full bg-emerald-500 transition-all duration-300';
            badge.className = 'px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-950 text-emerald-400 border border-emerald-800';
            verdict.innerHTML = `<span class="text-emerald-400 font-bold">Sangat Optimal (${score}/100)</span> • Siap bersaing di Google Page 1.`;
        } else if (score >= 50) {
            prog.className = 'h-full bg-amber-500 transition-all duration-300';
            badge.className = 'px-2.5 py-1 rounded-lg text-xs font-black bg-amber-950 text-amber-400 border border-amber-800';
            verdict.innerHTML = `<span class="text-amber-400 font-bold">Cukup (${score}/100)</span> • Lengkapi checklist hijau di bawah.`;
        } else {
            prog.className = 'h-full bg-red-500 transition-all duration-300';
            badge.className = 'px-2.5 py-1 rounded-lg text-xs font-black bg-red-950 text-red-400 border border-red-800';
            verdict.innerHTML = `<span class="text-red-400 font-bold">Kurang Optimal (${score}/100)</span> • Fokus keyword & konten belum selaras.`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        ['seo_focus_keyword', 'seo_secondary_keywords'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', runLiveSeoAudit);
        });
        document.querySelector('input[name="title"]')?.addEventListener('input', runLiveSeoAudit);
        document.querySelector('input[name="slug"]')?.addEventListener('input', runLiveSeoAudit);
        document.querySelector('textarea[name="meta_description"]')?.addEventListener('input', runLiveSeoAudit);
        setTimeout(runLiveSeoAudit, 600);
    });

const promptTemplate = `Anda adalah jurnalis dan penulis SEO senior (Bahasa Indonesia) untuk Ruang Lari dengan gaya penulisan faktual, lugas, dan mendalam seperti Kompas.com.

Aturan Penulisan Berita & Artikel:
- Faktual & Berimbang: Tulislah berita/artikel dengan gaya jurnalistik faktual (5W+1H pada lead berita). Jangan mengarang data/hoaks. Jika ada cuplikan berita dari Threads/Instagram/Media, olah menjadi liputan jurnalistik yang terstruktur, rapi, dan bersumber.
- SEO 2026-Friendly: Fokus pada intent pembaca, kredibilitas E-E-A-T, dan keterbacaan mobile.
- Struktur: JANGAN gunakan <h1> di content (judul halaman sudah H1). Gunakan <h2> dan <h3>. Paragraf ringkas 2–4 kalimat.
- HTML saja untuk content (pakai <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <blockquote>, <table>).
- Jika URL referensi diberikan tetapi Anda tidak bisa mengakses isinya, jangan mengklaim sudah membaca URL tersebut.

Instruksi Prompt Gambar:
- Buatkan marker prompt gambar [Gambar: Deskripsi visual...] di atas paragraf 1 (cover) dan di bawah <h2>.
- Style Prompt Gambar: Objek orang Indonesia natural & realistis (candid photorealistic, wajar & santai, bukan pose kaku/3D AI sintetis), ratio 3:2, lighting alami/hangat (Grok Imagine style), tekstur kulit alami tanpa oversharpening.

Input:
- Topik / Berita Realtime: {topic}
{url_section}

Output HARUS JSON valid TANPA markdown dan TANPA teks lain. Format:
{
  "seo_title": "... (<= 60 karakter)",
  "keywords": "... (utama + 3-5 LSI)",
  "meta_description": "... (140-160 karakter)",
  "excerpt": "... (ringkas 1-2 kalimat)",
  "content": "... (HTML body, tanpa <h1>)",
  "slug": "... (slug pendek)",
  "sources": ["https://..."]
}`;

function syncPrompt() {
    const topic = document.getElementById('ai-topic').value || '{topic}';
    const url = document.getElementById('ai-url').value;
    const urlSection = url ? `- URL referensi: ${url}` : '';
    
    document.getElementById('ai-prompt-preview').value = promptTemplate
        .replace('{topic}', topic)
        .replace('{url_section}', urlSection);
}

// Initialize prompt
syncPrompt();

async function generateArticle() {
    const topic = document.getElementById('ai-topic').value;
    const url = document.getElementById('ai-url').value;
    
    if (!topic) {
        alert('Silakan masukkan topik atau keyword terlebih dahulu.');
        return;
    }

    if (!confirm('AI akan meng-overwrite konten yang sedang Anda tulis. Lanjutkan?')) {
        return;
    }

    const btn = document.getElementById('btn-generate-ai');
    const loading = document.getElementById('ai-loading');
    
    btn.disabled = true;
    loading.classList.remove('hidden');

    try {
        const response = await fetch('{{ route("admin.blog.articles.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ topic: topic, url: url })
        });

        const contentType = response.headers.get('content-type') || '';
        let result;

        if (contentType.includes('application/json')) {
            result = await response.json();
        } else {
            const rawText = await response.text();
            console.error('Non-JSON response:', rawText);
            throw new Error(`Respon server tidak valid (${response.status}). Silakan coba lagi.`);
        }

        if (result.success) {
            const data = result.data;
            
            document.querySelector('input[name="title"]').value = data.seo_title || '';
            document.querySelector('input[name="slug"]').value = data.slug || '';
            document.querySelector('input[name="meta_title"]').value = data.seo_title || '';
            document.querySelector('textarea[name="meta_description"]').value = data.meta_description || '';
            
            if (data.focus_keyword) {
                document.querySelector('input[name="focus_keyword"]').value = data.focus_keyword;
            }
            if (data.secondary_keywords) {
                document.querySelector('input[name="secondary_keywords"]').value = data.secondary_keywords;
            }
            document.querySelector('input[name="meta_keywords"]').value = data.keywords || [data.focus_keyword, data.secondary_keywords].filter(Boolean).join(', ') || '';
            
            if (tinymce.get('editor_id')) {
                tinymce.get('editor_id').setContent(data.content || '');
            }
            
            if (data.excerpt || data.meta_description) {
                document.querySelector('textarea[name="excerpt"]').value = data.excerpt || data.meta_description;
            }

            runLiveSeoAudit();
            alert('Artikel berhasil di-generate!');
        } else {
            alert('Gagal generate artikel: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Gagal: ' + (error.message || 'Terjadi kesalahan sistem.'));
        console.error(error);
    } finally {
        btn.disabled = false;
        loading.classList.add('hidden');
    }
}

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
    const focusKwId = document.querySelector('input[name="focus_keyword"]')?.value || '';
    const secKwsId = document.querySelector('input[name="secondary_keywords"]')?.value || '';
    const metaKeywordsId = document.querySelector('input[name="meta_keywords"]')?.value || '';
    const canonicalId = document.querySelector('input[name="canonical_url"]')?.value || '';

    const titleEn = document.querySelector('input[name="title_en"]');
    const excerptEn = document.querySelector('textarea[name="excerpt_en"]');
    const metaTitleEn = document.querySelector('input[name="meta_title_en"]');
    const metaDescEn = document.querySelector('textarea[name="meta_description_en"]');
    const focusKwEn = document.querySelector('input[name="focus_keyword_en"]');
    const secKwsEn = document.querySelector('input[name="secondary_keywords_en"]');
    const metaKeywordsEn = document.querySelector('input[name="meta_keywords_en"]');
    const canonicalEn = document.querySelector('input[name="canonical_url_en"]');

    if (titleEn && !titleEn.value) titleEn.value = titleId;
    if (excerptEn && !excerptEn.value) excerptEn.value = excerptId;
    if (metaTitleEn && !metaTitleEn.value) metaTitleEn.value = metaTitleId;
    if (metaDescEn && !metaDescEn.value) metaDescEn.value = metaDescId;
    if (focusKwEn && !focusKwEn.value) focusKwEn.value = focusKwId;
    if (secKwsEn && !secKwsEn.value) secKwsEn.value = secKwsId;
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
            imgPlaceholder.classList.add('hidden');
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
    const AA_STORAGE_KEY = 'aa_draft_' + (document.querySelector('input[name="title"]')?.value || 'new');

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

        // Tampilkan kembali panel write dengan konten tersimpan.
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

        // Render ulang panel gambar dari marker [Gambar: ...] di konten.
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
                wrap.className = 'bg-slate-950 border border-slate-800 rounded-xl p-3.5';
                wrap.innerHTML =
                    '<div class="text-xs text-neon font-bold mb-1">Gambar ' + (i + 1) + '</div>' +
                    '<div class="text-xs text-slate-300 mb-2">Prompt: <span class="aa-img-prompt font-mono text-[11px] text-slate-400">' + prompts[marker].replace(/</g, '&lt;') + '</span></div>' +
                    '<div class="flex items-center gap-3">' +
                        '<input type="file" accept="image/*" data-marker="' + marker.replace(/"/g, '&quot;') + '" class="aa-img-input text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-slate-800 file:text-white file:text-xs file:font-semibold hover:file:bg-slate-700 transition-colors">' +
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
            el.className = 'px-3.5 py-1.5 rounded-lg transition-all ' + (active ? 'bg-neon text-dark font-bold shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white');
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
        // Cek draft tersimpan di localStorage (survive refresh/navigasi).
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
        const rawNews = document.getElementById('aa-raw-news') ? document.getElementById('aa-raw-news').value.trim() : '';
        const strategy = document.getElementById('aa-strategy').value;
        if (!topic && !rawNews) { alert('Masukkan topik atau tempelkan cuplikan berita terlebih dahulu.'); return; }
        aaSavedTopic = topic || (rawNews.length > 100 ? rawNews.substring(0, 100) + '...' : rawNews);
        aaSavedStrategy = strategy;

        // Jangan hancurkan artikel yang sudah ditulis jika user hanya ingin
        // melihat kembali step 1. Konfirmasi dulu sebelum brainstorm ulang.
        if (aaContent) {
            const ok = confirm('Brainstorm ulang akan membuang artikel yang sudah dibuat. Lanjutkan?');
            if (!ok) { aaSetStep(3); return; }
        }

        const btn = document.getElementById('aa-btn-brainstorm');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memikirkan...';

        const res = await aaPost('{{ route("admin.blog.articles.agent.brainstorm") }}', { 
            topic: topic ? topic.substring(0, 250) : (rawNews.length > 150 ? rawNews.substring(0, 150) : rawNews), 
            raw_news: rawNews,
            strategy 
        });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-lightbulb text-xs"></i> Brainstorm 10 Ide';

        if (!res.success) { alert('Gagal: ' + (res.message || 'Unknown')); return; }

        aaUuid = res.uuid;
        aaOptions = res.options || [];
        const box = document.getElementById('aa-options');
        box.innerHTML = '';
        aaOptions.forEach((opt, i) => {
            const div = document.createElement('label');
            div.className = 'flex items-start gap-3 bg-slate-950 border border-slate-800 rounded-xl p-3.5 cursor-pointer hover:border-neon/60 transition-all';
            div.innerHTML = `
                <input type="radio" name="aa-option" value="${i}" class="mt-1 rounded bg-slate-800 border-slate-700 text-neon focus:ring-0 accent-lime-400">
                <div class="min-w-0 flex-1">
                    <div class="text-white font-bold text-sm leading-snug">${opt.title || ''}</div>
                    <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-neon/15 text-neon text-[11px] font-bold">Focus: ${opt.keyword || ''}</span>
                        ${opt.secondary_keywords ? `<span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 text-[11px] font-medium border border-slate-700">LSI: ${opt.secondary_keywords}</span>` : ''}
                    </div>
                    <div class="text-slate-400 text-xs mt-1.5 leading-relaxed">${opt.summary || ''}</div>
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
            const sec = (document.getElementById('aa-manual-secondary')?.value || '').trim();
            if (!t || !k) { alert('Isi judul & focus keyword manual.'); return; }
            selection = { title: t, keyword: k, secondary_keywords: sec };
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
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-search text-xs"></i> Riset & Lanjut';

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
        // Fallback: jika AI mengembalikan teks mentah (bukan JSON ber-key content),
        // gunakan raw result agar konten tidak kosong & preview tetap tampil.
        const rawResult = (typeof result === 'string') ? result : '';
        aaContent = result.content || rawResult || '';
        status.textContent = 'Selesai! Review lalu terapkan.';
        // Simpan otomatis ke localStorage agar tidak hilang saat refresh/navigasi.
        aaPersist();
        preview.classList.remove('hidden');
        const previewText = aaContent
            ? aaContent.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 500)
            : '(Konten kosong — pastikan model mengembalikan JSON dengan key "content")';
        preview.innerHTML = '<div class="font-bold text-white text-sm mb-1">' + (result.title || '') + '</div>' +
            '<div class="text-neon/80 text-xs font-semibold mb-2">' + (result.meta_description || '') + '</div>' +
            '<div class="text-slate-300 text-xs leading-relaxed">' + previewText + (aaContent ? '...' : '') + '</div>';
        btnApply.classList.remove('hidden');
        const backWrite = document.getElementById('aa-btn-back-write');
        if (backWrite) backWrite.classList.remove('hidden');

        // Render upload field per [Gambar: ...]
        const prompts = res.image_prompts || {};
        const markers = Object.keys(prompts);
        if (markers.length > 0) {
            imgPanel.classList.remove('hidden');
            imgList.innerHTML = '';
            markers.forEach((marker, i) => {
                const wrap = document.createElement('div');
                wrap.className = 'bg-slate-950 border border-slate-800 rounded-xl p-3.5';
                wrap.innerHTML =
                    '<div class="text-xs text-neon font-bold mb-1">Gambar ' + (i + 1) + '</div>' +
                    '<div class="text-xs text-slate-300 mb-2">Prompt: <span class="aa-img-prompt font-mono text-[11px] text-slate-400">' + prompts[marker].replace(/</g, '&lt;') + '</span></div>' +
                    '<div class="flex items-center gap-3">' +
                        '<input type="file" accept="image/*" data-marker="' + marker.replace(/"/g, '&quot;') + '" class="aa-img-input text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-slate-800 file:text-white file:text-xs file:font-semibold hover:file:bg-slate-700 transition-colors">' +
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
        const text = 'generate multiple gambar ratio 3:2 (natural realistic photography, Indonesian people, natural lighting Grok Imagine style, soft skin texture, no oversharpening, candid photorealistic):\n' + prompts.map(p => '- ' + p).join('\n');
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('aa-btn-copy-prompts');
            const old = btn.innerHTML;
            btn.innerHTML = '✓ Tersalin';
            setTimeout(() => { btn.innerHTML = old; }, 1500);
        }).catch(() => {
            // Fallback untuk browser tanpa clipboard API
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
            article_id: null,
            content_override: aaContent
        });
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Terapkan ke Form & Simpan Draft';

        if (!res.success) { alert('Gagal: ' + (res.message || 'Unknown')); return; }
        aaClearPersist();
        window.location.href = res.redirect;
    }
</script>
@endpush
@endsection
