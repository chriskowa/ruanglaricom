@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Create Page')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.pages.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Pages
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                CREATE PAGE
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title & Slug -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Page Title">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Slug (Optional)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="page-title-slug">
                            <p class="text-xs text-slate-500 mt-1">Leave blank to auto-generate from title.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Excerpt</label>
                            <textarea name="excerpt" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Short summary...">{{ old('excerpt') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Editor -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <label class="block text-sm font-bold text-slate-300 mb-2">Content</label>
                    <textarea id="editor" name="content">{{ old('content') }}</textarea>
                </div>

                <!-- SEO -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        SEO Optimization
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">{{ old('meta_description') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="keyword1, keyword2">
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
                        <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
                            Save Page
                        </button>
                    </div>
                </div>

                <!-- Dynamic Template -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Template Selection</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Page Template</label>
                            <select name="template_id" id="templateSelect" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="">Default (Standard Page)</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }} data-sections="{{ json_encode($template->sections) }}">
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Select a predefined template for this page.</p>
                        </div>

                        <!-- Template Data Sections -->
                        <div id="templateDataContainer" class="space-y-4 pt-4 border-t border-slate-700 hidden">
                            <h4 class="text-xs font-bold text-neon uppercase tracking-widest mb-2">Template Content</h4>
                            <div id="templateFields" class="space-y-4">
                                <!-- Dynamic fields will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">System Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Hardcoded View (Optional)</label>
                            <input type="text" name="hardcoded" value="{{ old('hardcoded') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="e.g. about, contact">
                            <p class="text-xs text-slate-500 mt-1">If set, will load views/pages/{value}.blade.php</p>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Featured Image</h3>
                    <div class="space-y-4">
                        <div class="relative w-full aspect-video bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl overflow-hidden flex items-center justify-center group hover:border-neon transition-colors">
                            <input type="file" name="featured_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                            <img id="img-preview" class="absolute inset-0 w-full h-full object-cover hidden">
                            <div class="text-center p-4 pointer-events-none" id="img-placeholder">
                                <svg class="w-8 h-8 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs text-slate-400">Click to upload</span>
                            </div>
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
        selector: '#editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        skin: 'oxide-dark',
        content_css: 'dark'
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img-preview').src = e.target.result;
                document.getElementById('img-preview').classList.remove('hidden');
                document.getElementById('img-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Template Selection Logic
    document.addEventListener('DOMContentLoaded', function() {
        const templateSelect = document.getElementById('templateSelect');
        const templateDataContainer = document.getElementById('templateDataContainer');
        const templateFields = document.getElementById('templateFields');
        const oldData = {!! json_encode(old('template_data') ?? []) !!};

        function renderTemplateFields() {
            const selectedOption = templateSelect.options[templateSelect.selectedIndex];
            const sections = selectedOption.dataset.sections ? JSON.parse(selectedOption.dataset.sections) : null;

            if (sections && sections.length > 0) {
                templateDataContainer.classList.remove('hidden');
                templateFields.innerHTML = '';

                sections.forEach(section => {
                    const value = oldData[section.key] || '';
                    let fieldHtml = `<div><label class="block text-xs font-bold text-slate-400 mb-1 uppercase">${section.label}</label>`;

                    if (section.type === 'textarea') {
                        fieldHtml += `<textarea name="template_data[${section.key}]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" rows="3">${value}</textarea>`;
                    } else if (section.type === 'image') {
                        fieldHtml += `<input type="text" name="template_data[${section.key}]" value="${value}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors" placeholder="Image URL or path">`;
                    } else {
                        fieldHtml += `<input type="text" name="template_data[${section.key}]" value="${value}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors">`;
                    }
                    
                    fieldHtml += `</div>`;
                    templateFields.insertAdjacentHTML('beforeend', fieldHtml);
                });
            } else {
                templateDataContainer.classList.add('hidden');
                templateFields.innerHTML = '';
            }
        }

        templateSelect.addEventListener('change', renderTemplateFields);
        renderTemplateFields();
    });
</script>
@endpush
@endsection
