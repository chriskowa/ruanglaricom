@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Create Event')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<div class="min-h-screen pt-4 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.events.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Events
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                CREATE EVENT
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.events.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Event Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Nama Event</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Contoh: Jakarta Marathon 2026">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Tanggal Pelaksanaan</label>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Jam & Menit</label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Kota / Wilayah Race</label>
                                <select name="city_id" id="city_id" class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-3 text-white focus:outline-none focus:border-[#CCFF00] transition-colors">
                                    <option value="" data-lat="" data-lng="">Pilih Kota (Optional)</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" 
                                            data-lat="{{ $city->latitude }}" 
                                            data-lng="{{ $city->longitude }}"
                                            {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Nama Lokasi / Venue</label>
                                <input type="text" name="location_name" id="location_name" value="{{ old('location_name') }}" class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-3 text-white focus:outline-none focus:border-[#CCFF00] transition-colors" placeholder="Contoh: GBK Senayan" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Alamat Lengkap Venue (Optional)</label>
                            <input type="text" name="location_address" id="location_address" value="{{ old('location_address') }}" placeholder="Alamat jalan, nomor, kecamatan, kelurahan tempat race" class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-3 text-white focus:outline-none focus:border-[#CCFF00] transition-colors">
                        </div>

                        <!-- Map Selector Card -->
                        <div class="p-4 bg-slate-950/80 border border-slate-700 rounded-lg space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <span class="text-xs font-mono uppercase text-[#CCFF00] font-semibold tracking-wider">PETA KOORDINAT RACE (MAP SELECT)</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Klik di peta atau geser pin penanda untuk menentukan titik koordinat lat & long secara presisi.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-center-city" class="px-3 py-1.5 text-xs font-medium rounded-md bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white border border-slate-700 transition-colors">
                                        Pusatkan ke Kota
                                    </button>
                                </div>
                            </div>

                            <!-- Search Address Bar -->
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <input type="text" id="map_search_input" placeholder="Cari nama lokasi atau jalan di peta..." class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-sm text-white focus:outline-none focus:border-[#CCFF00] placeholder:text-slate-500">
                                </div>
                                <button type="button" id="btn-search-location" class="px-4 py-2 text-xs font-semibold rounded-md bg-slate-800 text-white hover:bg-slate-700 border border-slate-600 transition-colors">
                                    Cari
                                </button>
                            </div>
                            <div id="search-results-box" class="hidden max-h-44 overflow-y-auto bg-slate-900 border border-slate-700 rounded-md p-1 space-y-1 text-xs"></div>

                            <!-- Interactive Leaflet Map -->
                            <div id="admin_event_map" class="w-full rounded-lg border border-slate-700 bg-slate-900 overflow-hidden relative" style="height: 320px; z-index: 1;"></div>

                            <!-- Coordinates Inputs -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Latitude</label>
                                    <input type="number" step="any" name="location_lat" id="location_lat" value="{{ old('location_lat') }}" placeholder="-6.2088" class="w-full font-mono text-sm bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-white focus:outline-none focus:border-[#CCFF00]">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Longitude</label>
                                    <input type="number" step="any" name="location_lng" id="location_lng" value="{{ old('location_lng') }}" placeholder="106.8456" class="w-full font-mono text-sm bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-white focus:outline-none focus:border-[#CCFF00]">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Deskripsi Event</label>
                            <div class="text-slate-900">
                                <textarea name="description" id="description" rows="5" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Penjelasan singkat tentang event...">{{ old('description') }}</textarea>
                            </div>
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
                                    <option value="{{ $type->id }}" {{ old('race_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Kategori Jarak (Bisa pilih lebih dari satu)</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($raceDistances as $distance)
                                    <label class="flex items-center gap-2 p-3 rounded-lg bg-slate-800 border border-slate-700 cursor-pointer hover:border-neon transition-colors">
                                        <input type="checkbox" name="race_distances[]" value="{{ $distance->id }}" class="rounded bg-slate-900 border-slate-600 text-neon focus:ring-0" {{ in_array($distance->id, old('race_distances', [])) ? 'checked' : '' }}>
                                        <span class="text-sm text-slate-300">{{ $distance->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-bold text-slate-300 mb-2">Atau Tambahkan Jarak Baru (pisahkan dengan koma)</label>
                                <input type="text" name="custom_distances" value="{{ old('custom_distances') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Contoh: 7K, 100K, 50 mil">
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
                            <input type="url" name="registration_link" value="{{ old('registration_link') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://...">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Sosmed Event (URL)</label>
                            <input type="url" name="social_media_link" value="{{ old('social_media_link') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Instagram/Facebook URL">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Penyelenggara / EO</label>
                                <input type="text" name="organizer_name" value="{{ old('organizer_name') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Kontak Penyelenggara</label>
                                <input type="text" name="organizer_contact" value="{{ old('organizer_contact') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="No HP/WA">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Kontak Contributor</label>
                                <input type="text" name="contributor_contact" value="{{ old('contributor_contact') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="No HP/WA">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Template Event -->
                <div id="template-card" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Template Event</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Pilih Template</label>
                            <select name="template" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="paolo-fest" {{ old('template') == 'paolo-fest' ? 'selected' : '' }}>Paolo Fest (Default)</option>
                                <option value="golden-run" {{ old('template') == 'golden-run' ? 'selected' : '' }}>Golden Run</option>
                                <option value="latbar" {{ old('template') == 'latbar' ? 'selected' : '' }}>Latbar</option>
                                <option value="light-clean" {{ old('template') == 'light-clean' ? 'selected' : '' }}>Light Clean</option>
                                <option value="modern-dark" {{ old('template') == 'modern-dark' ? 'selected' : '' }}>Modern Dark</option>
                                <option value="paolo-fest-dark" {{ old('template') == 'paolo-fest-dark' ? 'selected' : '' }}>Paolo Fest Dark</option>
                                <option value="professional-city-run" {{ old('template') == 'professional-city-run' ? 'selected' : '' }}>Professional City Run</option>
                                <option value="simple-minimal" {{ old('template') == 'simple-minimal' ? 'selected' : '' }}>Simple Minimal</option>
                                <option value="quick-light" {{ old('template') == 'quick-light' ? 'selected' : '' }}>Quick Light (Simple)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Publish -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Publish</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Jenis Event</label>
                            <select name="event_kind" id="event_kind_select" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="directory" {{ old('event_kind') == 'directory' ? 'selected' : '' }}>Directory (Listing Saja)</option>
                                <option value="managed" {{ old('event_kind') == 'managed' ? 'selected' : '' }}>Managed (Dikelola Ruang Lari)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
                            Save Event
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
                                <svg class="w-8 h-8 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs text-slate-400">Select from Library / Cloudinary</span>
                            </div>
                        </div>
                        <input type="hidden" name="banner_image" id="banner-input" value="{{ old('banner_image') }}">
                        <input type="text" value="{{ old('banner_image') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-400 focus:outline-none focus:border-neon transition-colors" placeholder="Or paste URL here..." oninput="updateBannerPreview(this.value)">
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
@php($tinymceKey = config('services.tinymce.api_key') ?: 'jmsd06m7clya0xqmr43culaqsx8b77z5djnmhavamejsiypc')
<script src="https://cdn.tiny.cloud/1/{{ $tinymceKey }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#description',
        height: 450,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons template help',
        toolbar: 'undo redo | formatselect blocks | bold italic backcolor forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | table image link | fullscreen code',
        skin: 'oxide-dark',
        content_css: 'dark',
        document_base_url: '{{ url('/') }}/',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        images_upload_url: '{{ route("admin.blog.images.upload") }}',
        automatic_uploads: true,
        file_picker_types: 'image',
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

    // Toggle template card based on event kind
    const eventKindSelect = document.getElementById('event_kind_select');
    const templateCard = document.getElementById('template-card');
    function toggleTemplateCard() {
        if (eventKindSelect && templateCard) {
            if (eventKindSelect.value === 'managed') {
                templateCard.style.display = 'block';
            } else {
                templateCard.style.display = 'none';
            }
        }
    }
    if (eventKindSelect) {
        eventKindSelect.addEventListener('change', toggleTemplateCard);
        toggleTemplateCard();
    }
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const latInput = document.getElementById('location_lat');
        const lngInput = document.getElementById('location_lng');
        const addrInput = document.getElementById('location_address');
        const locNameInput = document.getElementById('location_name');
        const citySelect = document.getElementById('city_id');
        const mapSearchInput = document.getElementById('map_search_input');
        const btnSearch = document.getElementById('btn-search-location');
        const searchResultsBox = document.getElementById('search-results-box');
        const btnCenterCity = document.getElementById('btn-center-city');

        const existingLat = parseFloat(latInput.value);
        const existingLng = parseFloat(lngInput.value);

        let initialLat = !isNaN(existingLat) ? existingLat : null;
        let initialLng = !isNaN(existingLng) ? existingLng : null;

        if (initialLat === null || initialLng === null) {
            const selectedOpt = citySelect ? citySelect.options[citySelect.selectedIndex] : null;
            if (selectedOpt && selectedOpt.dataset.lat && selectedOpt.dataset.lng) {
                initialLat = parseFloat(selectedOpt.dataset.lat);
                initialLng = parseFloat(selectedOpt.dataset.lng);
            }
        }

        const startLat = (initialLat !== null && !isNaN(initialLat)) ? initialLat : -6.2088;
        const startLng = (initialLng !== null && !isNaN(initialLng)) ? initialLng : 106.8456;
        const startZoom = (!isNaN(existingLat) && latInput.value) ? 15 : (initialLat !== null ? 12 : 5);

        const map = L.map('admin_event_map').setView([startLat, startLng], startZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        let marker = null;

        function updateMarker(lat, lng, pan = false) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function(e) {
                    const pos = e.target.getLatLng();
                    setCoordinateInputs(pos.lat, pos.lng, true);
                });
            }
            if (pan) {
                map.setView([lat, lng], Math.max(map.getZoom(), 14));
            }
        }

        function setCoordinateInputs(lat, lng, doReverseGeocode = false) {
            latInput.value = Number(lat).toFixed(6);
            lngInput.value = Number(lng).toFixed(6);

            if (doReverseGeocode) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.display_name) {
                            if (!addrInput.value.trim()) {
                                addrInput.value = data.display_name;
                            }
                            if (!locNameInput.value.trim()) {
                                locNameInput.value = data.name || (data.address ? (data.address.road || data.address.suburb || data.address.city) : '');
                            }
                        }
                    })
                    .catch(() => {});
            }
        }

        if (!isNaN(existingLat) && latInput.value && !isNaN(existingLng) && lngInput.value) {
            updateMarker(existingLat, existingLng, false);
        }

        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng, false);
            setCoordinateInputs(e.latlng.lat, e.latlng.lng, true);
        });

        function onManualCoordInput() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                updateMarker(lat, lng, true);
            }
        }
        latInput.addEventListener('input', onManualCoordInput);
        lngInput.addEventListener('input', onManualCoordInput);

        if (citySelect) {
            citySelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.dataset.lat && opt.dataset.lng) {
                    const cLat = parseFloat(opt.dataset.lat);
                    const cLng = parseFloat(opt.dataset.lng);
                    if (!isNaN(cLat) && !isNaN(cLng)) {
                        if (!latInput.value || !lngInput.value) {
                            map.setView([cLat, cLng], 12);
                            updateMarker(cLat, cLng, false);
                            setCoordinateInputs(cLat, cLng, false);
                        }
                    }
                }
            });
        }

        if (btnCenterCity && citySelect) {
            btnCenterCity.addEventListener('click', function() {
                const opt = citySelect.options[citySelect.selectedIndex];
                if (opt && opt.dataset.lat && opt.dataset.lng) {
                    const cLat = parseFloat(opt.dataset.lat);
                    const cLng = parseFloat(opt.dataset.lng);
                    if (!isNaN(cLat) && !isNaN(cLng)) {
                        map.setView([cLat, cLng], 13);
                        updateMarker(cLat, cLng, false);
                        setCoordinateInputs(cLat, cLng, false);
                    }
                } else {
                    alert('Silakan pilih kota terlebih dahulu.');
                }
            });
        }

        function executeSearch() {
            const q = mapSearchInput.value.trim();
            if (!q) return;

            btnSearch.disabled = true;
            const originalText = btnSearch.innerText;
            btnSearch.innerText = '...';

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=id`)
                .then(res => res.json())
                .then(data => {
                    btnSearch.disabled = false;
                    btnSearch.innerText = originalText;
                    searchResultsBox.innerHTML = '';

                    if (!data || data.length === 0) {
                        searchResultsBox.innerHTML = '<div class="p-2 text-slate-400">Lokasi tidak ditemukan di peta. Coba nama kota atau tempat lain.</div>';
                        searchResultsBox.classList.remove('hidden');
                        return;
                    }

                    searchResultsBox.classList.remove('hidden');
                    data.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'p-2 hover:bg-slate-800 cursor-pointer rounded text-slate-200 border-b border-slate-800 last:border-0 transition-colors';
                        row.textContent = item.display_name;
                        row.addEventListener('click', function() {
                            const lat = parseFloat(item.lat);
                            const lng = parseFloat(item.lon);
                            map.setView([lat, lng], 15);
                            updateMarker(lat, lng, true);
                            setCoordinateInputs(lat, lng, false);
                            if (!addrInput.value.trim()) {
                                addrInput.value = item.display_name;
                            }
                            if (!locNameInput.value.trim()) {
                                locNameInput.value = item.name || '';
                            }
                            searchResultsBox.classList.add('hidden');
                        });
                        searchResultsBox.appendChild(row);
                    });
                })
                .catch(err => {
                    btnSearch.disabled = false;
                    btnSearch.innerText = originalText;
                    console.error('Nominatim error:', err);
                });
        }

        if (btnSearch) {
            btnSearch.addEventListener('click', executeSearch);
        }
        if (mapSearchInput) {
            mapSearchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    executeSearch();
                }
            });
        }

        setTimeout(function() {
            map.invalidateSize();
        }, 400);
    });
</script>
@endpush
@endsection
