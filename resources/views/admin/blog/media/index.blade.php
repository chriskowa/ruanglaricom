@extends('layouts.pacerhub')
@php
    $withSidebar = true;
    if(request('picker')) {
        $hideNav = true;
        $hideFooter = true;
        $hideSidebar = true;
        $hideChat = true;
    }
@endphp

@section('title', 'Media Library')

@section('content')
<div class="min-h-screen {{ request('picker') ? 'pt-4' : 'pt-20' }} pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    @if(!request('picker'))
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                MEDIA LIBRARY
            </h1>
            <p class="text-slate-400 mt-1">Manage your blog images and files.</p>
        </div>
    </div>
    @endif

    <!-- Tabs -->
    <div class="flex border-b border-slate-700 mb-6 gap-6">
        <a href="{{ route('admin.blog.media.index', ['picker' => request('picker')]) }}" class="pb-2 text-sm font-bold border-b-2 {{ !request('source') ? 'border-neon text-white' : 'border-transparent text-slate-400 hover:text-white' }} transition-colors">
            Local Storage
        </a>
        <a href="{{ route('admin.blog.media.index', ['source' => 'cloudinary', 'picker' => request('picker')]) }}" class="pb-2 text-sm font-bold border-b-2 {{ request('source') === 'cloudinary' ? 'border-neon text-white' : 'border-transparent text-slate-400 hover:text-white' }} transition-colors">
            Cloudinary
        </a>
    </div>

    <!-- Upload Area (Only for Local) -->
    <div id="dropzone" class="mb-8 border-2 border-dashed border-slate-700 rounded-2xl p-8 text-center bg-slate-800/30 hover:bg-slate-800/50 hover:border-neon transition-all cursor-pointer relative z-10 group {{ request('source') === 'cloudinary' ? 'hidden' : '' }}">
        <input type="file" id="file-upload" class="hidden" multiple accept="image/*">
        <div class="pointer-events-none">
            <div class="w-16 h-16 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-neon/10 group-hover:text-neon transition-colors">
                <svg class="w-8 h-8 text-slate-400 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-1">Drag & Drop files here</h3>
            <p class="text-slate-500 text-sm">or click to browse from your computer</p>
        </div>
        <!-- Progress Bar -->
        <div id="upload-progress" class="hidden mt-4 w-full max-w-md mx-auto">
            <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-neon transition-all duration-300 w-0"></div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Uploading...</p>
        </div>
    </div>

    <!-- Cloudinary Error -->
    @if(isset($cloudinaryError))
    <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl flex items-center gap-3">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div>
            <p class="font-bold">Cloudinary Error</p>
            <p class="text-sm">{{ $cloudinaryError }}</p>
            <p class="text-xs mt-1 text-slate-500">Please configure CLOUDINARY_CLOUD_NAME, API_KEY, and API_SECRET in .env</p>
        </div>
    </div>
    @endif

    <!-- Media Grid -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 relative z-10">
        <!-- Toolbar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
                <select id="filter-type" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-neon">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                </select>
            </div>
            <div class="relative w-full md:w-64">
                <input type="text" id="search-media" placeholder="Search files..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 pl-10 text-white text-sm focus:outline-none focus:border-neon">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <div id="media-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @if(request('source') === 'cloudinary')
                @include('admin.blog.media.partials.grid_cloudinary', ['media' => $media])
            @else
                @include('admin.blog.media.partials.grid', ['media' => $media])
            @endif
        </div>
        
        <div class="mt-6 flex justify-between items-center">
            @if(request('source') === 'cloudinary')
                @if(isset($nextCursor) && $nextCursor)
                    <button onclick="loadMoreCloudinary('{{ $nextCursor }}')" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700">Load More</button>
                @endif
            @else
                {{ $media->appends(request()->query())->links() }}
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-upload');
    const progressBar = document.getElementById('upload-progress');
    const progressFill = progressBar.querySelector('div');
    const mediaGrid = document.getElementById('media-grid');

    // Tab Switching
    function switchTab(tab) {
        const urlParams = new URLSearchParams(window.location.search);
        if (tab === 'cloudinary') {
            urlParams.set('source', 'cloudinary');
        } else {
            urlParams.delete('source');
        }
        window.location.search = urlParams.toString();
    }

    // Drag & Drop Events (Only active if dropzone visible)
    if (dropzone && !dropzone.classList.contains('hidden')) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('border-neon', 'bg-slate-800/50'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('border-neon', 'bg-slate-800/50'), false);
        });

        dropzone.addEventListener('drop', handleDrop, false);
        dropzone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => handleFiles(e.target.files));
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        ([...files]).forEach(uploadFile);
    }

    function uploadFile(file) {
        progressBar.classList.remove('hidden');
        progressFill.style.width = '0%';

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("admin.blog.media.store") }}', true);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressFill.style.width = percentComplete + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    refreshGrid();
                }
            } else {
                alert('Upload failed');
            }
            progressBar.classList.add('hidden');
        };

        xhr.send(formData);
    }

    function refreshGrid() {
        const search = document.getElementById('search-media').value;
        const type = document.getElementById('filter-type').value;
        const urlParams = new URLSearchParams(window.location.search);
        const source = urlParams.get('source') || '';
        
        fetch(`{{ route('admin.blog.media.index') }}?search=${search}&type=${type}&source=${source}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            mediaGrid.innerHTML = data.html;
        });
    }

    // Load More Cloudinary
    window.loadMoreCloudinary = function(cursor) {
        const search = document.getElementById('search-media').value;
        fetch(`{{ route('admin.blog.media.index') }}?source=cloudinary&cursor=${cursor}&search=${search}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            mediaGrid.insertAdjacentHTML('beforeend', data.html);
            // Update button or remove if no more
        });
    }

    // Delete Media
    window.deleteMedia = function(id, event) {
        event.stopPropagation(); // Prevent triggering item click
        if (!confirm('Are you sure you want to delete this file?')) return;

        fetch(`{{ url('admin/blog/media') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                refreshGrid();
            }
        });
    }

    // Search & Filter
    let timeout = null;
    document.getElementById('search-media').addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(refreshGrid, 500);
    });

    document.getElementById('filter-type').addEventListener('change', refreshGrid);

    // Copy URL on click (optional UX) or Select for TinyMCE
    document.addEventListener('click', function(e) {
        if (e.target.closest('.media-item')) {
            const item = e.target.closest('.media-item');
            const url = item.dataset.url;
            const filename = item.dataset.filename;
            
            // If opened via TinyMCE popup (detected via query param 'picker')
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('picker') === 'true') {
                // Send message to parent
                window.parent.postMessage({
                    mceAction: 'insertMedia',
                    url: url,
                    alt: filename
                }, '*');
            } else {
                // Just copy to clipboard
                navigator.clipboard.writeText(url).then(() => {
                    alert('URL copied to clipboard!');
                });
            }
        }
    });
</script>
@endpush
@endsection
