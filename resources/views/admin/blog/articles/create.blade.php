@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Create Article') 

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
                CREATE ARTICLE
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.blog.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- AI Generator -->
                <div class="bg-card/50 backdrop-blur-md border border-neon/30 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-12 h-12 text-neon" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-neon/10 text-neon">
                            <i class="fas fa-robot"></i>
                        </span>
                        AI Article Generator
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Topic or Keyword</label>
                                <input type="text" id="ai-topic" onkeyup="syncPrompt()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="e.g., Tips Lari Marathon">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Reference URL (Optional Rewrite)</label>
                                <input type="url" id="ai-url" onkeyup="syncPrompt()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://external-article.com/...">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Final AI Prompt Preview</label>
                            <textarea id="ai-prompt-preview" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors" readonly></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" onclick="generateArticle()" id="btn-generate-ai" class="px-8 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all flex items-center gap-2">
                                <i class="fas fa-magic"></i> Generate Article
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 italic text-center">AI akan melakukan riset faktual & optimasi SEO 2026. Hasil akan mengisi form di bawah.</p>
                    </div>
                    <!-- Loading State -->
                    <div id="ai-loading" class="hidden absolute inset-0 bg-slate-900/80 backdrop-blur-sm z-20 flex flex-col items-center justify-center text-center p-6">
                        <div class="w-12 h-12 border-4 border-neon border-t-transparent rounded-full animate-spin mb-4"></div>
                        <h4 class="text-white font-bold mb-1">Menyusun Artikel...</h4>
                        <p class="text-slate-400 text-sm">Proses ini memakan waktu sekitar 30-60 detik karena AI melakukan riset faktual.</p>
                    </div>
                </div>

                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="inline-flex rounded-2xl bg-slate-900/70 border border-slate-700 p-1">
                            <button type="button" data-lang-tab="id" class="lang-tab px-4 py-2 rounded-xl text-sm font-bold transition-colors bg-neon/15 text-neon">Indonesia</button>
                            <button type="button" data-lang-tab="en" class="lang-tab px-4 py-2 rounded-xl text-sm font-bold transition-colors text-slate-300 hover:text-white">English</button>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-bold text-slate-300 mb-2">Slug (Shared)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full md:w-[360px] bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="article-title-slug">
                            <div class="text-xs text-slate-500 mt-1">Kosongkan untuk auto-generate dari judul Indonesia.</div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-6">
                        <div data-lang-panel="id" class="lang-panel space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Title (ID)</label>
                                <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Judul artikel Indonesia">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Excerpt (ID)</label>
                                <textarea name="excerpt" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Ringkasan untuk listing...">{{ old('excerpt') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Content (ID)</label>
                                <textarea id="editor_id" class="js-editor" name="content">{{ old('content') }}</textarea>
                            </div>
                            <div class="border-t border-slate-700/60 pt-6">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    SEO (ID)
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Title (ID)</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Description (ID)</label>
                                        <textarea name="meta_description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">{{ old('meta_description') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Keywords (ID)</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="run, marathon, training">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Canonical URL (ID)</label>
                                        <input type="url" name="canonical_url" value="{{ old('canonical_url') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
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
                                <input type="text" name="title_en" value="{{ old('title_en') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="English title">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Excerpt (EN)</label>
                                <textarea name="excerpt_en" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="English summary...">{{ old('excerpt_en') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Content (EN)</label>
                                <textarea id="editor_en" class="js-editor" name="content_en">{{ old('content_en') }}</textarea>
                            </div>
                            <div class="border-t border-slate-700/60 pt-6">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    SEO (EN)
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Title (EN)</label>
                                        <input type="text" name="meta_title_en" value="{{ old('meta_title_en') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Description (EN)</label>
                                        <textarea name="meta_description_en" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">{{ old('meta_description_en') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Meta Keywords (EN)</label>
                                        <input type="text" name="meta_keywords_en" value="{{ old('meta_keywords_en') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="run, marathon, training">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 mb-2">Canonical URL (EN)</label>
                                        <input type="url" name="canonical_url_en" value="{{ old('canonical_url_en') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
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
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 bg-slate-900 border border-slate-700 rounded-xl px-4 py-3">
                            <input type="checkbox" name="is_featured" value="1" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0" {{ old('is_featured') ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-300">Featured di Home</span>
                        </label>
                        <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
                            Save Article
                        </button>
                    </div>
                </div>

                <!-- Taxonomy -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Taxonomy</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Category</label>
                            <select name="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="">Uncategorized</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Tags</label>
                            <div class="max-h-40 overflow-y-auto bg-slate-900 border border-slate-700 rounded-xl p-3 mb-2 space-y-2">
                                @foreach($tags as $tag)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0">
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
                            <img id="img-preview" class="absolute inset-0 w-full h-full object-cover hidden">
                            <div class="text-center p-4 pointer-events-none" id="img-placeholder">
                                <svg class="w-8 h-8 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs text-slate-400">Click to upload</span>
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
    })
});

const promptTemplate = `Anda adalah penulis SEO senior (Bahasa Indonesia) untuk Ruang Lari.

Aturan:
- Tulis unik (parafrase total), tidak plagiarisme.
- Factual: jangan mengarang data/statistik. Jika menyebut angka/klaim penting, sertakan URL sumber pada field sources.
- SEO 2026-friendly: fokus intent, E-E-A-T, dan keterbacaan mobile.
- Struktur: JANGAN gunakan <h1> di content (judul halaman sudah H1). Mulai dari <h2>/<h3>. Paragraf 2–4 kalimat.
- HTML saja untuk content (pakai <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <blockquote>, <table>).
- Jika URL referensi diberikan tetapi Anda tidak bisa mengakses isinya, jangan mengklaim sudah membaca URL tersebut.

Input:
- Topik: {topic}
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ topic: topic, url: url })
        });

        const result = await response.json();

        if (result.success) {
            const data = result.data;
            
            document.querySelector('input[name="title"]').value = data.seo_title || '';
            document.querySelector('input[name="slug"]').value = data.slug || '';
            document.querySelector('input[name="meta_title"]').value = data.seo_title || '';
            document.querySelector('textarea[name="meta_description"]').value = data.meta_description || '';
            document.querySelector('input[name="meta_keywords"]').value = data.keywords || '';
            
            if (tinymce.get('editor_id')) {
                tinymce.get('editor_id').setContent(data.content || '');
            }
            
            if (data.excerpt || data.meta_description) {
                document.querySelector('textarea[name="excerpt"]').value = data.excerpt || data.meta_description;
            }

            alert('Artikel berhasil di-generate!');
        } else {
            alert('Gagal generate artikel: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem.');
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
</script>
@endpush
@endsection
