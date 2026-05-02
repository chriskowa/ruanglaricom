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

                <!-- Dynamic Template Settings -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Template Selection</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Page Template</label>
                            <select name="template_id" id="templateSelect" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="">Default (Standard Page)</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id', $page->template_id) == $template->id ? 'selected' : '' }} data-sections="{{ json_encode($template->sections) }}">
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

                <!-- Template Settings (Hardcoded) -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">System Settings</h3>
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
@php($tinymceKey = config('services.tinymce.api_key') ?: 'jmsd06m7clya0xqmr43culaqsx8b77z5djnmhavamejsiypc')
<script src="https://cdn.tiny.cloud/1/{{ $tinymceKey }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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

        // Template Selection Logic
        const templateSelect = document.getElementById('templateSelect');
        const templateDataContainer = document.getElementById('templateDataContainer');
        const templateFields = document.getElementById('templateFields');
        const currentPageData = {!! json_encode($page->template_data ?? []) !!};

        function renderTemplateFields() {
            const selectedOption = templateSelect.options[templateSelect.selectedIndex];
            const sections = selectedOption.dataset.sections ? JSON.parse(selectedOption.dataset.sections) : null;

            if (sections && sections.length > 0) {
                templateDataContainer.classList.remove('hidden');
                templateFields.innerHTML = '';

                sections.forEach(section => {
                    const value = currentPageData[section.key] || '';
                    const fieldId = `template_data_${section.key}`;
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
        renderTemplateFields(); // Initial call on load

        // TinyMCE Init
        tinymce.init({
            selector: '#editor',
            height: 500,
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image',
            skin: "oxide-dark",
            content_css: "dark",
            images_upload_url: '{{ route("admin.blog.images.upload") }}',
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: (callback, value, meta) => {
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
    });
</script>
@endsection
