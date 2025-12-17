@extends('layouts.app')

@section('title', 'Tambah Event')

@push('styles')
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
                <h4 class="card-title">Tambah Event</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('eo.events.store') }}" method="POST" enctype="multipart/form-data" id="eventForm">
                    @csrf

                    <!-- Informasi Dasar -->
                    <div class="form-section">
                        <h5 class="section-title">Informasi Dasar</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Event <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Halaman (SEO URL)</label>
                            <div class="slug-preview">
                                <span class="slug-prefix">{{ config('app.url') }}/events/</span>
                                <input type="text" class="form-control slug-input" name="slug" value="{{ old('slug') }}" placeholder="indonesia-run-2025">
                            </div>
                            <small class="form-text text-muted">
                                Biarkan kosong jika ingin dibuat otomatis dari nama event. Gunakan huruf kecil, angka, dan tanda minus (-).
                            </small>
                            @error('slug')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="short_description" rows="3" maxlength="500" required>{{ old('short_description') }}</textarea>
                            <small class="form-text text-muted">Maksimal 500 karakter</small>
                            @error('short_description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea class="form-control" name="full_description" rows="6">{{ old('full_description') }}</textarea>
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
                                <input type="datetime-local" class="form-control" name="start_at" value="{{ old('start_at') }}" required>
                                @error('start_at')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Selesai</label>
                                <input type="datetime-local" class="form-control" name="end_at" value="{{ old('end_at') }}">
                                @error('end_at')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="location_name" value="{{ old('location_name') }}" placeholder="GBK, Jakarta" required>
                            @error('location_name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" name="location_address" rows="2">{{ old('location_address') }}</textarea>
                            @error('location_address')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control" name="location_lat" value="{{ old('location_lat') }}" placeholder="-6.2275">
                                @error('location_lat')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control" name="location_lng" value="{{ old('location_lng') }}" placeholder="106.802">
                                @error('location_lng')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Media & Link -->
                    <div class="form-section">
                        <h5 class="section-title">Media & Link</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Hero Image URL</label>
                            <input type="url" class="form-control" name="hero_image_url" value="{{ old('hero_image_url') }}" placeholder="https://example.com/image.jpg">
                            <small class="form-text text-muted">URL gambar untuk banner hero di landing page</small>
                            @error('hero_image_url')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Map Embed URL</label>
                            <textarea class="form-control" name="map_embed_url" rows="3" placeholder="Kode embed Google Maps iframe">{{ old('map_embed_url') }}</textarea>
                            <small class="form-text text-muted">Kode embed Google Maps untuk ditampilkan di landing page</small>
                            @error('map_embed_url')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Google Calendar URL</label>
                            <input type="url" class="form-control" name="google_calendar_url" value="{{ old('google_calendar_url') }}" placeholder="https://calendar.google.com/...">
                            <small class="form-text text-muted">Link untuk menambahkan event ke Google Calendar</small>
                            @error('google_calendar_url')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Kategori Lari -->
                    <div class="form-section">
                        <h5 class="section-title">Kategori Lari</h5>
                        <p class="text-muted mb-3">Tambahkan kategori lari yang tersedia untuk event ini. Minimal 1 kategori harus diisi.</p>
                        
                        <div id="categoriesWrapper">
                            <!-- Kategori pertama -->
                            <div class="category-item" data-index="0">
                                <button type="button" class="btn btn-sm btn-danger remove-category">Hapus</button>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="categories[0][name]" value="{{ old('categories.0.name') }}" placeholder="5K Fun Run" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Jarak (km)</label>
                                        <input type="number" step="0.01" class="form-control" name="categories[0][distance_km]" value="{{ old('categories.0.distance_km') }}" placeholder="5.00">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Kode</label>
                                        <input type="text" class="form-control" name="categories[0][code]" value="{{ old('categories.0.code') }}" placeholder="5K">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Kuota</label>
                                        <input type="number" class="form-control" name="categories[0][quota]" value="{{ old('categories.0.quota') }}" placeholder="1000">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Usia Min</label>
                                        <input type="number" class="form-control" name="categories[0][min_age]" value="{{ old('categories.0.min_age') }}" placeholder="13">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Usia Max</label>
                                        <input type="number" class="form-control" name="categories[0][max_age]" value="{{ old('categories.0.max_age') }}" placeholder="99">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Cut-off Time (menit)</label>
                                        <input type="number" class="form-control" name="categories[0][cutoff_minutes]" value="{{ old('categories.0.cutoff_minutes') }}" placeholder="60">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Harga Early Bird</label>
                                        <input type="number" class="form-control" name="categories[0][price_early]" value="{{ old('categories.0.price_early') }}" placeholder="150000">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Harga Regular</label>
                                        <input type="number" class="form-control" name="categories[0][price_regular]" value="{{ old('categories.0.price_regular') }}" placeholder="200000">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Harga Late</label>
                                        <input type="number" class="form-control" name="categories[0][price_late]" value="{{ old('categories.0.price_late') }}" placeholder="250000">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Registrasi Mulai</label>
                                        <input type="datetime-local" class="form-control" name="categories[0][reg_start_at]" value="{{ old('categories.0.reg_start_at') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Registrasi Selesai</label>
                                        <input type="datetime-local" class="form-control" name="categories[0][reg_end_at]" value="{{ old('categories.0.reg_end_at') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[0][is_active]" value="1" checked>
                                        <label class="form-check-label">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary" id="addCategory">
                            <i class="fa fa-plus"></i> Tambah Kategori
                        </button>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('eo.events.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let categoryIndex = 1;
    const categoriesWrapper = document.getElementById('categoriesWrapper');
    const addCategoryBtn = document.getElementById('addCategory');
    const template = categoriesWrapper.querySelector('.category-item').cloneNode(true);

    // Add category
    addCategoryBtn.addEventListener('click', function() {
        const newCategory = template.cloneNode(true);
        const currentIndex = categoryIndex++;
        
        // Update all input names
        newCategory.querySelectorAll('input, textarea, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.startsWith('categories[0]')) {
                const newName = name.replace('categories[0]', `categories[${currentIndex}]`);
                input.setAttribute('name', newName);
                input.value = '';
                
                // Reset checkbox to checked
                if (input.type === 'checkbox') {
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

    // Remove category (event delegation)
    categoriesWrapper.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-category') || e.target.closest('.remove-category')) {
            const categoryItem = e.target.closest('.category-item');
            const allCategories = categoriesWrapper.querySelectorAll('.category-item');
            
            // Prevent removing if only one category left
            if (allCategories.length <= 1) {
                alert('Minimal 1 kategori harus tersisa');
                return;
            }
            
            categoryItem.remove();
        }
    });
});
</script>
@endpush

