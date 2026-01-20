@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Edit Event')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.events.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Events
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                EDIT EVENT
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.events.update', $event) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Event Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Nama Event</label>
                            <input type="text" name="name" value="{{ old('name', $event->name) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Tanggal Pelaksanaan</label>
                                <input type="date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Jam & Menit</label>
                                <input type="time" name="start_time" value="{{ old('start_time', $event->start_time ? $event->start_time->format('H:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Kota / Lokasi Race</label>
                                <select name="city_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                    <option value="">Pilih Kota (Optional)</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $event->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Lokasi Spesifik (Optional)</label>
                                <input type="text" name="location_name" value="{{ old('location_name', $event->location_name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Deskripsi Event</label>
                            <textarea name="description" rows="5" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">{{ old('description', $event->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Race Categories</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Jenis Lomba</label>
                            <select name="race_type_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="">Pilih Jenis Lomba</option>
                                @foreach($raceTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('race_type_id', $event->race_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Kategori Jarak</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($raceDistances as $distance)
                                    <label class="flex items-center gap-2 p-3 rounded-lg bg-slate-800 border border-slate-700 cursor-pointer hover:border-neon transition-colors">
                                        <input type="checkbox" name="race_distances[]" value="{{ $distance->id }}" class="rounded bg-slate-900 border-slate-600 text-neon focus:ring-0" {{ in_array($distance->id, old('race_distances', $selectedDistances)) ? 'checked' : '' }}>
                                        <span class="text-sm text-slate-300">{{ $distance->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Links & Contacts -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Registration & Contact</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Link Pendaftaran</label>
                            <input type="url" name="registration_link" value="{{ old('registration_link', $event->registration_link) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Sosmed Event (URL)</label>
                            <input type="url" name="social_media_link" value="{{ old('social_media_link', $event->social_media_link) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Penyelenggara / EO</label>
                                <input type="text" name="organizer_name" value="{{ old('organizer_name', $event->organizer_name) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Kontak Penyelenggara</label>
                                <input type="text" name="organizer_contact" value="{{ old('organizer_contact', $event->organizer_contact) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Kontak Contributor</label>
                                <input type="text" name="contributor_contact" value="{{ old('contributor_contact', $event->contributor_contact) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
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
                                <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $event->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $event->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
                            Update Event
                        </button>
                    </div>
                </div>

                <!-- Banner Image -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Banner Event</h3>
                    <div class="space-y-4">
                        <div class="relative w-full aspect-video bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl overflow-hidden flex items-center justify-center group hover:border-neon transition-colors cursor-pointer" onclick="openMediaLibrary()">
                            <img id="banner-preview" class="absolute inset-0 w-full h-full object-cover hidden">
                            <div class="text-center p-4 pointer-events-none" id="banner-placeholder">
                                <svg class="w-8 h-8 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs text-slate-400">Click to change (Library / Cloudinary)</span>
                            </div>
                        </div>
                        <input type="hidden" name="banner_image" id="banner-input" value="{{ old('banner_image', $event->banner_image) }}">
                        <input type="text" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-400 focus:outline-none focus:border-neon transition-colors" placeholder="Or paste URL here..." oninput="updateBannerPreview(this.value)">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Media Library Modal Container -->
<div id="media-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 hidden">
    <div class="bg-slate-900 w-11/12 h-5/6 rounded-2xl border border-slate-700 shadow-2xl flex flex-col overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-slate-800">
            <h3 class="text-white font-bold">Select Banner</h3>
            <button onclick="closeMediaLibrary()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="flex-1 overflow-hidden relative">
            <iframe id="media-frame" src="" class="w-full h-full border-0"></iframe>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openMediaLibrary() {
        const frame = document.getElementById('media-frame');
        // Always refresh to ensure tabs are correct
        frame.src = "{{ route('admin.blog.media.index') }}?picker=true&t=" + new Date().getTime();
        
        document.getElementById('media-modal').classList.remove('hidden');
        window.addEventListener('message', handleMediaSelect);
    }

    function closeMediaLibrary() {
        document.getElementById('media-modal').classList.add('hidden');
        window.removeEventListener('message', handleMediaSelect);
    }

    function handleMediaSelect(event) {
        if (event.data && event.data.mceAction === 'insertMedia') {
            const url = event.data.url;
            document.getElementById('banner-input').value = url;
            updateBannerPreview(url);
            closeMediaLibrary();
        }
    }

    function updateBannerPreview(url) {
        const preview = document.getElementById('banner-preview');
        const placeholder = document.getElementById('banner-placeholder');
        const input = document.getElementById('banner-input');
        
        if (url) {
            preview.src = url;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            input.value = url;
        } else {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }

    // Init preview if value exists
    const initialUrl = document.getElementById('banner-input').value;
    if (initialUrl) updateBannerPreview(initialUrl);
</script>
@endpush
@endsection
