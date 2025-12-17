@extends('layouts.app')

@section('title', 'Edit Event')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .category-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: #f9fafb;
        position: relative;
    }
    .category-item:first-child .remove-category {
        display: none;
    }
    .remove-category {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .form-section:last-child {
        border-bottom: none;
    }
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #1d3557;
    }
    .slug-preview {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .slug-prefix {
        padding: 0.55rem 0.7rem;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem 0 0 0.5rem;
        font-size: 0.9rem;
        color: #6b7280;
    }
    .slug-input {
        border-radius: 0 0.5rem 0.5rem 0;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Event</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('eo.events.update', $event) }}" method="POST" enctype="multipart/form-data" id="eventForm">
                    @csrf
                    @method('PUT')

                    <!-- Informasi Dasar -->
                    <div class="form-section">
                        <h5 class="section-title">Informasi Dasar</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Event <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $event->name) }}" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Halaman (SEO URL)</label>
                            <div class="slug-preview">
                                <span class="slug-prefix">{{ config('app.url') }}/events/</span>
                                <input type="text" class="form-control slug-input" name="slug" value="{{ old('slug', $event->slug) }}" placeholder="indonesia-run-2025">
                            </div>
                            <small class="form-text text-muted">
                                Biarkan kosong jika ingin dibuat otomatis dari nama event. Gunakan huruf kecil, angka, dan tanda minus (-).
                            </small>
                            @error('slug')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <div id="ckeditor"></div>
                            <textarea name="short_description" id="short_description" style="display:none;">{{ old('short_description', $event->short_description) }}</textarea>
                            <small class="form-text text-muted">Deskripsi singkat yang akan ditampilkan di halaman event</small>
                            @error('short_description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <div id="ckeditor_full"></div>
                            <textarea name="full_description" id="full_description" style="display:none;">{{ old('full_description', $event->full_description) }}</textarea>
                            <small class="form-text text-muted">Digunakan di landing page detail event</small>
                            @error('full_description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Waktu & Lokasi -->
                    <div class="form-section">
                        <h5 class="section-title">Waktu & Lokasi</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="start_at" value="{{ old('start_at', $event->start_at ? $event->start_at->format('Y-m-d\TH:i') : '') }}" required>
                                @error('start_at')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Selesai</label>
                                <input type="datetime-local" class="form-control" name="end_at" value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}">
                                @error('end_at')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Event <span class="text-danger">*</span></label>
                            <div class="mb-2">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="location_search" placeholder="Cari lokasi event... (Tekan Enter)">
                                </div>
                            </div>
                            <div id="map" style="height: 400px; width: 100%; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #e5e7eb; z-index: 1;"></div>
                            <small class="form-text text-muted mb-2 d-block">Geser marker untuk menentukan lokasi yang tepat.</small>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lokasi / Kota</label>
                                    <input type="text" class="form-control" name="location_name" id="location_name" value="{{ old('location_name', $event->location_name) }}" placeholder="GBK, Jakarta" required>
                                    @error('location_name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <input type="text" class="form-control" name="location_address" id="location_address" value="{{ old('location_address', $event->location_address) }}" placeholder="Jl. Jendral Sudirman...">
                                    @error('location_address')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" step="any" class="form-control bg-light" name="location_lat" id="location_lat" value="{{ old('location_lat', $event->location_lat) }}" placeholder="-6.2275" readonly>
                                    @error('location_lat')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" step="any" class="form-control bg-light" name="location_lng" id="location_lng" value="{{ old('location_lng', $event->location_lng) }}" placeholder="106.802" readonly>
                                    @error('location_lng')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- RPC Location Section -->
                        <div class="mb-3 mt-4 pt-4 border-top">
                            <h5 class="section-title text-primary" style="font-size: 1.1rem;">Lokasi Pengambilan Race Pack (RPC)</h5>
                            <p class="text-muted small mb-3">Kosongkan jika lokasi RPC sama dengan lokasi event atau belum ditentukan.</p>
                            
                            <div class="mb-2">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="rpc_search" placeholder="Cari lokasi RPC... (Tekan Enter)">
                                </div>
                            </div>
                            <div id="rpc_map" style="height: 400px; width: 100%; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #e5e7eb; z-index: 1;"></div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lokasi RPC</label>
                                    <input type="text" class="form-control" name="rpc_location_name" id="rpc_location_name" value="{{ old('rpc_location_name', $event->rpc_location_name) }}" placeholder="Lobby Mall FX Sudirman">
                                    @error('rpc_location_name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alamat Lengkap RPC</label>
                                    <input type="text" class="form-control" name="rpc_location_address" id="rpc_location_address" value="{{ old('rpc_location_address', $event->rpc_location_address) }}" placeholder="Jl. Jendral Sudirman Pintu Satu Senayan...">
                                    @error('rpc_location_address')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude RPC</label>
                                    <input type="number" step="any" class="form-control bg-light" name="rpc_latitude" id="rpc_latitude" value="{{ old('rpc_latitude', $event->rpc_latitude) }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude RPC</label>
                                    <input type="number" step="any" class="form-control bg-light" name="rpc_longitude" id="rpc_longitude" value="{{ old('rpc_longitude', $event->rpc_longitude) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Waktu Registrasi & Promo -->
                    <div class="form-section">
                        <h5 class="section-title">Waktu Registrasi & Promo</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registrasi Dibuka</label>
                                <input type="datetime-local" class="form-control" name="registration_open_at" value="{{ old('registration_open_at', $event->registration_open_at ? $event->registration_open_at->format('Y-m-d\TH:i') : '') }}">
                                <small class="form-text text-muted">Waktu mulai registrasi. Form registrasi akan ditampilkan setelah waktu ini.</small>
                                @error('registration_open_at')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registrasi Ditutup</label>
                                <input type="datetime-local" class="form-control" name="registration_close_at" value="{{ old('registration_close_at', $event->registration_close_at ? $event->registration_close_at->format('Y-m-d\TH:i') : '') }}">
                                <small class="form-text text-muted">Waktu tutup registrasi. Form registrasi akan disembunyikan setelah waktu ini.</small>
                                @error('registration_close_at')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kode Promo</label>
                            <input type="text" class="form-control" name="promo_code" value="{{ old('promo_code', $event->promo_code) }}" placeholder="PROMO2025" maxlength="50">
                            <small class="form-text text-muted">Kode promo yang akan mempengaruhi total harga saat checkout. Kosongkan jika tidak ada.</small>
                            @error('promo_code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Media & Link -->
                    <div class="form-section">
                        <h5 class="section-title">Media & Link</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Hero Image</label>
                            @if($event->hero_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $event->hero_image) }}" alt="Current Hero Image" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                                </div>
                            @elseif($event->hero_image_url)
                                <div class="mb-2">
                                    <img src="{{ $event->hero_image_url }}" alt="Current Hero Image" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="hero_image" id="hero_image" accept="image/*" onchange="previewImage(this, 'hero_preview')">
                            <small class="form-text text-muted">Rekomendasi ukuran: 1920x1080px (16:9). Format: JPG, PNG, atau WebP. Maksimal 5MB.</small>
                            <div id="hero_preview" class="mt-2" style="display:none;">
                                <img src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                            </div>
                            @error('hero_image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <!-- Fallback to URL if needed -->
                            <div class="mt-2">
                                <small class="text-muted">Atau gunakan URL gambar:</small>
                                <input type="url" class="form-control mt-1" name="hero_image_url" value="{{ old('hero_image_url', $event->hero_image_url) }}" placeholder="https://example.com/image.jpg">
                                @error('hero_image_url')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Logo Event</label>
                            @if($event->logo_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $event->logo_image) }}" alt="Current Logo" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="logo_image" id="logo_image" accept="image/*" onchange="previewImage(this, 'logo_preview')">
                            <small class="form-text text-muted">Logo yang akan ditampilkan di halaman depan. Rekomendasi ukuran: 400x400px (1:1). Format: PNG dengan transparan. Maksimal 2MB.</small>
                            <div id="logo_preview" class="mt-2" style="display:none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                            </div>
                            @error('logo_image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3" style="display:none;">
                            <label class="form-label">Floating Image (Animasi Kanan)</label>
                            @if($event->floating_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $event->floating_image) }}" alt="Current Floating Image" style="max-width: 200px; max-height: 300px; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="floating_image" id="floating_image" accept="image/*" onchange="previewImage(this, 'floating_preview')">
                            <small class="form-text text-muted">Gambar dengan animasi floating di bagian kanan hero section. Rekomendasi ukuran: 600x800px. Format: PNG dengan transparan. Maksimal 2MB.</small>
                            <div id="floating_preview" class="mt-2" style="display:none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 300px; border-radius: 8px;">
                            </div>
                            @error('floating_image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Gallery Section -->
                        <div class="mb-3">
                            <label class="form-label">Galeri Foto Event</label>
                            
                            @if($event->gallery && count($event->gallery) > 0)
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Foto Saat Ini (Centang untuk menghapus):</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($event->gallery as $imagePath)
                                            <div class="position-relative border rounded p-1" style="width: 100px; height: 100px;">
                                                <img src="{{ asset('storage/' . $imagePath) }}" class="w-100 h-100 object-fit-cover rounded">
                                                <div class="form-check position-absolute top-0 end-0 m-1 bg-white rounded shadow-sm" style="padding: 2px;">
                                                    <input class="form-check-input m-0" type="checkbox" name="remove_gallery_images[]" value="{{ $imagePath }}" title="Hapus foto ini">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <input type="file" class="form-control" name="gallery[]" id="gallery" accept="image/*" multiple onchange="previewGallery(this, 'gallery_preview')">
                            <small class="form-text text-muted">Upload foto baru untuk ditambahkan ke galeri. Format: JPG, PNG, atau WebP. Maksimal 2MB per file.</small>
                            <div id="gallery_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                            @error('gallery')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gambar Medali</label>
                            @if($event->medal_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $event->medal_image) }}" alt="Current Medal" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="medal_image" id="medal_image" accept="image/*" onchange="previewImage(this, 'medal_preview')">
                            <small class="form-text text-muted">Gambar medali finisher. Rekomendasi ukuran: 800x800px (1:1). Format: JPG atau PNG. Maksimal 2MB.</small>
                            <div id="medal_preview" class="mt-2" style="display:none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                            </div>
                            @error('medal_image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gambar Jersey</label>
                            @if($event->jersey_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $event->jersey_image) }}" alt="Current Jersey" style="max-width: 200px; max-height: 250px; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="jersey_image" id="jersey_image" accept="image/*" onchange="previewImage(this, 'jersey_preview')">
                            <small class="form-text text-muted">Gambar jersey event. Rekomendasi ukuran: 800x1000px. Format: JPG atau PNG. Maksimal 2MB.</small>
                            <div id="jersey_preview" class="mt-2" style="display:none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 250px; border-radius: 8px;">
                            </div>
                            @error('jersey_image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Map Embed URL</label>
                            <textarea class="form-control" name="map_embed_url" rows="3" placeholder="Kode embed Google Maps iframe">{{ old('map_embed_url', $event->map_embed_url) }}</textarea>
                            <small class="form-text text-muted">Kode embed Google Maps untuk ditampilkan di landing page</small>
                            @error('map_embed_url')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Google Calendar URL</label>
                            <input type="url" class="form-control" name="google_calendar_url" value="{{ old('google_calendar_url', $event->google_calendar_url) }}" placeholder="https://calendar.google.com/...">
                            <small class="form-text text-muted">Link untuk menambahkan event ke Google Calendar</small>
                            @error('google_calendar_url')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tema Warna -->
                    <div class="form-section">
                        <h5 class="section-title">Tema Warna Landing Page</h5>
                        <p class="text-muted mb-3">Sesuaikan warna landing page dengan branding event Anda.</p>
                        
                        @php
                            $colors = $event->theme_colors ?? [];
                        @endphp

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Warna Utama (Neon/Volt Green)</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[neon]" value="{{ old('theme_colors.neon', $colors['neon'] ?? '#ccff00') }}" title="Pilih warna">
                                <small class="text-muted">Tombol utama, highlight</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Neon Hover</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[neonHover]" value="{{ old('theme_colors.neonHover', $colors['neonHover'] ?? '#b3e600') }}" title="Pilih warna">
                                <small class="text-muted">Warna saat tombol di-hover</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Accent (Biru)</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[accent]" value="{{ old('theme_colors.accent', $colors['accent'] ?? '#3b82f6') }}" title="Pilih warna">
                                <small class="text-muted">Elemen dekoratif sekunder</small>
                            </div>
                             <div class="col-md-3 mb-3">
                                <label class="form-label">Danger (Merah)</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[danger]" value="{{ old('theme_colors.danger', $colors['danger'] ?? '#ef4444') }}" title="Pilih warna">
                                <small class="text-muted">Warna error/alert</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dark Background (Slate 900)</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[dark]" value="{{ old('theme_colors.dark', $colors['dark'] ?? '#0f172a') }}" title="Pilih warna">
                                <small class="text-muted">Latar belakang utama</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Card Background (Slate 800)</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[card]" value="{{ old('theme_colors.card', $colors['card'] ?? '#1e293b') }}" title="Pilih warna">
                                <small class="text-muted">Latar belakang kartu/section</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Input Background (Slate 950)</label>
                                <input type="color" class="form-control form-control-color w-100" name="theme_colors[input]" value="{{ old('theme_colors.input', $colors['input'] ?? '#020617') }}" title="Pilih warna">
                                <small class="text-muted">Latar belakang input form</small>
                            </div>
                        </div>
                    </div>

                    <!-- Fasilitas Event -->
                    <div class="form-section">
                        <h5 class="section-title">Fasilitas Event</h5>
                        <p class="text-muted mb-3">Pilih fasilitas yang tersedia dan tambahkan deskripsi untuk setiap fasilitas.</p>
                        
                        @php
                            $facilityTemplates = [
                                'Race Pack Lengkap',
                                'Hydration & Energy Station',
                                'Keamanan & Medis',
                                'Bag Drop Area',
                                'Live Timing & E-Certificate',
                                'Entertainment & Photo Spot'
                            ];
                            $existingFacilities = $event->facilities ?? [];
                            $facilityIndex = 0;
                        @endphp
                        
                        <div id="facilitiesWrapper">
                            @foreach($facilityTemplates as $templateName)
                                @php
                                    $facility = collect($existingFacilities)->firstWhere('name', $templateName);
                                    $isEnabled = $facility ? true : false;
                                    $facilityName = $facility['name'] ?? $templateName;
                                    $facilityDesc = $facility['description'] ?? '';
                                @endphp
                                <div class="facility-item mb-3" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; background: #f9fafb;">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="form-check me-3" style="flex-shrink: 0; margin-top: 0.25rem;">
                                            <input class="form-check-input" type="checkbox" name="facilities[{{ $facilityIndex }}][enabled]" value="1" id="facility_{{ $facilityIndex }}_enabled" {{ old("facilities.{$facilityIndex}.enabled", $isEnabled) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="facility_{{ $facilityIndex }}_enabled"></label>
                                        </div>
                                        <div style="flex: 1;">
                                        <input type="text" class="form-control mb-2" name="facilities[{{ $facilityIndex }}][name]" value="{{ old("facilities.{$facilityIndex}.name", $facilityName) }}" placeholder="Nama Fasilitas" id="facility_{{ $facilityIndex }}_name">
                                        <textarea class="form-control mb-2" name="facilities[{{ $facilityIndex }}][description]" rows="2" placeholder="Deskripsi fasilitas">{{ old("facilities.{$facilityIndex}.description", $facilityDesc) }}</textarea>
                                        
                                        @if(isset($facility['image']) && $facility['image'])
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $facility['image']) }}" alt="Icon" style="height: 40px; border-radius: 4px;">
                                                <input type="hidden" name="facilities[{{ $facilityIndex }}][existing_image]" value="{{ $facility['image'] }}">
                                            </div>
                                        @endif
                                        
                                        <input type="file" class="form-control form-control-sm" name="facilities[{{ $facilityIndex }}][image]" accept="image/*">
                                        <small class="text-muted" style="font-size: 0.8rem;">Upload Icon/Gambar Fasilitas (Optional)</small>
                                    </div>
                                    </div>
                                </div>
                                @php $facilityIndex++; @endphp
                            @endforeach
                        </div>
                    </div>

                    <!-- Ukuran Jersey -->
                    <div class="form-section">
                        <h5 class="section-title">Ukuran Jersey yang Tersedia</h5>
                        <p class="text-muted mb-3">Pilih ukuran jersey yang tersedia untuk event ini.</p>
                        
                        <div class="row">
                            @php
                                $jerseySizes = ['XS' => 'Extra Small', 'S' => 'Small', 'M' => 'Medium', 'L' => 'Large', 'XL' => 'Extra Large', 'XXL' => 'Double Extra Large'];
                                $selectedSizes = old('jersey_sizes', $event->jersey_sizes ?? []);
                            @endphp
                            @foreach($jerseySizes as $size => $label)
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="jersey_sizes[]" value="{{ $size }}" id="jersey_size_{{ $size }}" {{ in_array($size, $selectedSizes) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="jersey_size_{{ $size }}">
                                            {{ $size }} - {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('jersey_sizes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kategori Lari -->
                    <div class="form-section">
                        <h5 class="section-title">Kategori Lari</h5>
                        <p class="text-muted mb-3">Kelola kategori lari untuk event ini. Minimal 1 kategori harus diisi.</p>
                        
                        <div id="categoriesWrapper">
                            @foreach($event->categories as $index => $category)
                                <div class="category-item" data-index="{{ $index }}" data-category-id="{{ $category->id }}">
                                    <button type="button" class="btn btn-sm btn-danger remove-category">Hapus</button>
                                    <input type="hidden" name="categories[{{ $index }}][id]" value="{{ $category->id }}">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="categories[{{ $index }}][name]" value="{{ old("categories.{$index}.name", $category->name) }}" placeholder="5K Fun Run" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Jarak (km)</label>
                                            <input type="number" step="0.01" class="form-control" name="categories[{{ $index }}][distance_km]" value="{{ old("categories.{$index}.distance_km", $category->distance_km) }}" placeholder="5.00">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Kode</label>
                                            <input type="text" class="form-control" name="categories[{{ $index }}][code]" value="{{ old("categories.{$index}.code", $category->code) }}" placeholder="5K">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Kuota</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][quota]" value="{{ old("categories.{$index}.quota", $category->quota) }}" placeholder="1000">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Usia Min</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][min_age]" value="{{ old("categories.{$index}.min_age", $category->min_age) }}" placeholder="13">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Usia Max</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][max_age]" value="{{ old("categories.{$index}.max_age", $category->max_age) }}" placeholder="99">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Cut-off Time (menit)</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][cutoff_minutes]" value="{{ old("categories.{$index}.cutoff_minutes", $category->cutoff_minutes) }}" placeholder="60">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Harga Early Bird</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][price_early]" value="{{ old("categories.{$index}.price_early", $category->price_early) }}" placeholder="150000">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Harga Regular</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][price_regular]" value="{{ old("categories.{$index}.price_regular", $category->price_regular) }}" placeholder="200000">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Harga Late</label>
                                            <input type="number" class="form-control" name="categories[{{ $index }}][price_late]" value="{{ old("categories.{$index}.price_late", $category->price_late) }}" placeholder="250000">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Registrasi Mulai</label>
                                            <input type="datetime-local" class="form-control" name="categories[{{ $index }}][reg_start_at]" value="{{ old("categories.{$index}.reg_start_at", $category->reg_start_at ? $category->reg_start_at->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Registrasi Selesai</label>
                                            <input type="datetime-local" class="form-control" name="categories[{{ $index }}][reg_end_at]" value="{{ old("categories.{$index}.reg_end_at", $category->reg_end_at ? $category->reg_end_at->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[{{ $index }}][is_active]" value="1" {{ old("categories.{$index}.is_active", $category->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label">Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-outline-primary" id="addCategory">
                            <i class="fa fa-plus"></i> Tambah Kategori
                        </button>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('eo.events.index') }}" class="btn btn-secondary">Batal</a>
                        <a href="{{ route('eo.events.preview', $event) }}" target="_blank" class="btn btn-info">
                            <i class="fa fa-eye"></i> Preview
                        </a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Leaflet Map Init ---
    function initMap(mapId, latId, lngId, addressId, nameId, searchId) {
        const defaultLat = -6.2088; // Jakarta
        const defaultLng = 106.8456;
        
        const latInput = document.getElementById(latId);
        const lngInput = document.getElementById(lngId);
        
        const initialLat = latInput && latInput.value ? parseFloat(latInput.value) : defaultLat;
        const initialLng = lngInput && lngInput.value ? parseFloat(lngInput.value) : defaultLng;

        if(!document.getElementById(mapId)) return;

        const map = L.map(mapId).setView([initialLat, initialLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker([initialLat, initialLng], {
            draggable: true
        }).addTo(map);

        // Update inputs
        function updateInputs(lat, lng) {
            if(latInput) latInput.value = lat.toFixed(6);
            if(lngInput) lngInput.value = lng.toFixed(6);
        }

        // Fetch address
        function fetchAddress(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        if(addressId) {
                             const addrInput = document.getElementById(addressId);
                             if(addrInput) addrInput.value = data.display_name;
                        }
                        
                        if (nameId) {
                             const nameInput = document.getElementById(nameId);
                             if(nameInput) {
                                let locName = data.name || '';
                                if (!locName) {
                                    locName = data.address.building || data.address.amenity || data.address.city || data.address.town || data.address.village || '';
                                }
                                if(locName) nameInput.value = locName;
                             }
                        }
                    }
                })
                .catch(err => console.error('Geocoding error:', err));
        }

        // Marker drag end
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateInputs(position.lat, position.lng);
            fetchAddress(position.lat, position.lng);
        });

        // Map click
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
            fetchAddress(e.latlng.lat, e.latlng.lng);
        });

        // Search functionality
        const searchInput = document.getElementById(searchId);
        if(searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value;
                    if(query.length < 3) return;

                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                        .then(r => r.json())
                        .then(data => {
                            if(data && data.length > 0) {
                                const result = data[0];
                                const lat = parseFloat(result.lat);
                                const lon = parseFloat(result.lon);
                                
                                map.setView([lat, lon], 16);
                                marker.setLatLng([lat, lon]);
                                updateInputs(lat, lon);
                                
                                if(addressId) document.getElementById(addressId).value = result.display_name;
                                if(nameId) document.getElementById(nameId).value = result.name || query;
                            } else {
                                alert('Lokasi tidak ditemukan');
                            }
                        });
                }
            });
        }
        
        // Fix map resize issues when tab/section becomes visible
        setTimeout(() => { map.invalidateSize(); }, 500);
    }

    // Initialize Main Map
    initMap('map', 'location_lat', 'location_lng', 'location_address', 'location_name', 'location_search');

    // Initialize RPC Map
    initMap('rpc_map', 'rpc_latitude', 'rpc_longitude', 'rpc_location_address', 'rpc_location_name', 'rpc_search');
    
    // --- End Leaflet Map ---

    let categoryIndex = {{ $event->categories->count() }};
    const categoriesWrapper = document.getElementById('categoriesWrapper');
    const addCategoryBtn = document.getElementById('addCategory');

    // Initialize CKEditor for short description
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#ckeditor'), {
                toolbar: {
                    items: [
                        'heading',
                        '|', 'bold', 'italic',
                        '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                        '|', 'link', 'bulletedList', 'numberedList',
                        '|', 'undo', 'redo',
                    ]
                },
            })
            .then(editor => {
                window.ckEditor = editor;
                
                // Sync with hidden textarea
                editor.model.document.on('change:data', () => {
                    document.getElementById('short_description').value = editor.getData();
                });
                
                // Load initial content
                const initialContent = document.getElementById('short_description').value;
                if (initialContent) {
                    editor.setData(initialContent);
                }
            })
            .catch(err => {
                console.error(err.stack);
            });

        // Initialize CKEditor for full description
        ClassicEditor
            .create(document.querySelector('#ckeditor_full'), {
                toolbar: {
                    items: [
                        'heading',
                        '|', 'bold', 'italic',
                        '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                        '|', 'link', 'bulletedList', 'numberedList',
                        '|', 'undo', 'redo',
                    ]
                },
            })
            .then(editor => {
                window.ckEditorFull = editor;
                
                // Sync with hidden textarea
                editor.model.document.on('change:data', () => {
                    document.getElementById('full_description').value = editor.getData();
                });
                
                // Load initial content
                const initialContent = document.getElementById('full_description').value;
                if (initialContent) {
                    editor.setData(initialContent);
                }
            })
            .catch(err => {
                console.error(err.stack);
            });
    }

    // Preview image function
    window.previewImage = function(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                const img = preview.querySelector('img');
                if (img) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Preview Gallery function
    window.previewGallery = function(input, previewId) {
        const previewContainer = document.getElementById(previewId);
        previewContainer.innerHTML = '';
        
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.style.border = '1px solid #ddd';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    };

    // Add category
    if (addCategoryBtn && categoriesWrapper) {
        const template = categoriesWrapper.querySelector('.category-item').cloneNode(true);
        if (template) {
            // Clear existing category data from template
            template.querySelectorAll('input').forEach(input => {
                if (input.type !== 'hidden' || !input.name.includes('[id]')) {
                    if (input.type === 'checkbox') {
                        input.checked = true;
                    } else {
                        input.value = '';
                    }
                }
            });
            
            addCategoryBtn.addEventListener('click', function() {
                const newCategory = template.cloneNode(true);
                const currentIndex = categoryIndex++;
                
                // Remove category ID if exists
                newCategory.querySelectorAll('input[name*="[id]"]').forEach(input => {
                    input.remove();
                });
                
                // Update all input names
                newCategory.querySelectorAll('input, textarea, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        const newName = name.replace(/categories\[\d+\]/, `categories[${currentIndex}]`);
                        input.setAttribute('name', newName);
                        
                        // Update IDs
                        const id = input.getAttribute('id');
                        if (id) {
                            input.setAttribute('id', id.replace(/categories_\d+/, `categories_${currentIndex}`));
                        }
                        
                        if (input.type !== 'checkbox' && input.type !== 'hidden') {
                            input.value = '';
                        } else if (input.type === 'checkbox') {
                            input.checked = true;
                        }
                    }
                });
                
                // Update data-index
                newCategory.setAttribute('data-index', currentIndex);
                
                // Show remove button
                const removeBtn = newCategory.querySelector('.remove-category');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                }
                
                categoriesWrapper.appendChild(newCategory);
            });
        }
    }

    // Remove category (event delegation)
    if (categoriesWrapper) {
        categoriesWrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-category') || e.target.closest('.remove-category')) {
                const categoryItem = e.target.closest('.category-item');
                const allCategories = categoriesWrapper.querySelectorAll('.category-item');
                
                if (allCategories.length <= 1) {
                    alert('Minimal 1 kategori harus tersisa');
                    return;
                }
                
                categoryItem.remove();
            }
        });
    }
});
</script>
@endpush
