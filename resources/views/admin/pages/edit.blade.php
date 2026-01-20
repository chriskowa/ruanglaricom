@extends('layouts.pacerhub')
@php($withSidebar = true)
@section('title', 'Edit Page: ' . $page->title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight uppercase italic">Edit <span class="text-neon">Page</span></h2>
            <p class="text-slate-400 mt-1">Update page content and settings.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.pages.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 hover:bg-slate-800/50 transition-all font-bold">
                Cancel
            </a>
            <button type="submit" form="pageForm" class="px-6 py-2.5 rounded-xl bg-neon text-slate-900 font-black uppercase tracking-wider hover:shadow-[0_0_20px_rgba(195,255,0,0.4)] transition-all">
                Update Page
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                <h3 class="font-bold text-red-400">Please fix the following errors:</h3>
            </div>
            <ul class="list-disc list-inside text-red-300 space-y-1 ml-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="pageForm" action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title & Slug -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Title</label>
                            <input type="text" name="title" value="{{ old('title', $page->title) }}" required 
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" 
                                placeholder="Page Title">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" 
                                placeholder="page-title-slug">
                            <p class="text-xs text-slate-500 mt-1">Leave blank to auto-generate from title.</p>
                        </div>
                    </div>
                </div>

                <!-- Excerpt -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <label class="block text-sm font-bold text-slate-300 mb-2">Excerpt</label>
                    <textarea name="excerpt" rows="3" 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors"
                        placeholder="Brief summary of the page...">{{ old('excerpt', $page->excerpt) }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Short description for search engines and social media.</p>
                </div>

                <!-- Content -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <label class="block text-sm font-bold text-slate-300 mb-2">Content</label>
                    <textarea name="content" id="editor" class="min-h-[500px]">{{ old('content', $page->content) }}</textarea>
                </div>

                <!-- SEO Settings -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-700 pb-2">SEO Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" 
                                placeholder="SEO Title (optional)">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="2" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors"
                                placeholder="SEO Description (optional)">{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords) }}" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" 
                                placeholder="keyword1, keyword2, keyword3">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish Status -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Publish</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $page->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t border-slate-700">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Created</span>
                                <span class="text-white">{{ $page->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm mt-2">
                                <span class="text-slate-400">Last Updated</span>
                                <span class="text-white">{{ $page->updated_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Featured Image</h3>
                    
                    <div class="mb-4">
                        <div id="imagePreview" class="w-full aspect-video bg-slate-900 rounded-xl border border-slate-700 flex items-center justify-center overflow-hidden relative group">
                            @if($page->featured_image)
                                <img src="{{ asset('storage/' . $page->featured_image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-slate-600 flex flex-col items-center">
                                    <i class="fas fa-image text-3xl mb-2"></i>
                                    <span class="text-xs">No image selected</span>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                <span class="text-white text-xs font-bold uppercase tracking-wider">Change Image</span>
                            </div>
                        </div>
                    </div>

                    <input type="file" name="featured_image" id="featured_image" accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-slate-800 file:text-neon hover:file:bg-slate-700 transition-all cursor-pointer">
                    <p class="text-xs text-slate-500 mt-2">Recommended size: 1200x630px. Max: 2MB.</p>
                </div>

                <!-- Template Settings (Hardcoded) -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Template</h3>
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Hardcoded View</label>
                        <input type="text" name="hardcoded" value="{{ old('hardcoded', $page->hardcoded) }}" 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" 
                            placeholder="e.g. home">
                        <p class="text-xs text-slate-500 mt-1">Leave blank for default page template. Use only for special system pages.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image Preview
        const imageInput = document.getElementById('featured_image');
        const imagePreview = document.getElementById('imagePreview');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                }
                reader.readAsDataURL(file);
            }
        });

        // TinyMCE Init
        tinymce.init({
            selector: '#editor',
            height: 600,
            plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
            skin: "oxide-dark",
            content_css: "dark",
            content_style: `
                body { background-color: #0f172a; color: #cbd5e1; font-family: 'Inter', sans-serif; }
                a { color: #c3ff00; }
            `
        });
    });
</script>
@endsection
