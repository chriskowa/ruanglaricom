@extends('layouts.app')

@section('title', 'Program Lari - Marketplace')

@section('page-title', 'Program Lari')

@push('styles')
<style>
    .program-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .program-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .program-thumbnail {
        height: 200px;
        object-fit: cover;
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .category-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(13, 110, 253, 0.9);
        color: white;
    }
    .price-display {
        font-size: 20px;
        font-weight: 700;
    }
    .filter-sidebar {
        position: sticky;
        top: 20px;
    }
    .loading-overlay {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        z-index: 999;
        align-items: center;
        justify-content: center;
    }
    .loading-overlay.active {
        display: flex;
    }
    #programs-container {
        position: relative;
        min-height: 400px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-3 col-xxl-3">
        <div class="card filter-sidebar">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Program</h5>
            </div>
            <div class="card-body">
                <form id="filter-form">
                    <!-- Kategori -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="cat-all" value="" checked>
                                <label class="form-check-label" for="cat-all">Semua</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="cat-5k" value="5k">
                                <label class="form-check-label" for="cat-5k">5K</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="cat-10k" value="10k">
                                <label class="form-check-label" for="cat-10k">10K</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="cat-21k" value="21k">
                                <label class="form-check-label" for="cat-21k">Half Marathon (21K)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="cat-42k" value="42k">
                                <label class="form-check-label" for="cat-42k">Marathon (42K)</label>
                            </div>
                        </div>
                    </div>

                    <!-- Difficulty -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tingkat Kesulitan</label>
                        <select class="form-control default-select" name="difficulty" id="filter-difficulty">
                            <option value="">Semua</option>
                            <option value="beginner">Pemula</option>
                            <option value="intermediate">Menengah</option>
                            <option value="advanced">Lanjutan</option>
                        </select>
                    </div>

                    <!-- Rating -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rating Minimum</label>
                        <select class="form-control default-select" name="rating" id="filter-rating">
                            <option value="">Semua</option>
                            <option value="4">4+ Bintang</option>
                            <option value="3">3+ Bintang</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Urutkan</label>
                        <select class="form-control default-select" name="sort" id="filter-sort">
                            <option value="newest">Terbaru</option>
                            <option value="popular">Paling Populer</option>
                            <option value="rating">Rating Tertinggi</option>
                            <option value="price_asc">Harga Terendah</option>
                            <option value="price_desc">Harga Tertinggi</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-secondary w-100" onclick="resetFilters()">Reset Filter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-xxl-9">
        <!-- Header dengan Search & Generate Button -->
        <div class="d-flex align-items-center justify-content-between p-2 border rounded">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="search-input" class="form-control" placeholder="Cari program atau coach..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="button" onclick="applyFilters()">
                                <i class="fas fa-search me-1"></i>Cari
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        @auth
                            @if(auth()->user()->role === 'runner')
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateProgramModal">
                                    <i class="fas fa-plus-circle me-1"></i>Generate Program Sendiri
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Grid -->
        <div id="programs-container">
            <div class="loading-overlay" id="loading-overlay">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat program...</p>
                </div>
            </div>
            
            <div class="row" id="programs-grid">
                @include('programs.partials.program-grid', ['programs' => $programs])
            </div>
            
            <div id="programs-pagination">
                @include('programs.partials.pagination', ['programs' => $programs])
            </div>
        </div>
    </div>
</div>

<!-- Generate Program Modal (Daniels Formula) -->
@auth
    @if(auth()->user()->role === 'runner')
        @include('programs.modals.generate-program')
    @endif
@endauth
@endsection

@push('scripts')
<script>
    // Auto-apply filters on change
    $(document).ready(function() {
        // Radio buttons
        $('input[name="category"]').on('change', function() {
            applyFilters();
        });

        // Select dropdowns
        $('#filter-difficulty, #filter-rating, #filter-sort').on('change', function() {
            applyFilters();
        });

        // Search on Enter key
        $('#search-input').on('keypress', function(e) {
            if (e.which === 13) {
                applyFilters();
            }
        });
    });

    function applyFilters() {
        const loadingOverlay = $('#loading-overlay');
        loadingOverlay.addClass('active');

        const formData = {
            category: $('input[name="category"]:checked').val(),
            difficulty: $('#filter-difficulty').val(),
            rating: $('#filter-rating').val(),
            sort: $('#filter-sort').val(),
            search: $('#search-input').val(),
        };

        $.ajax({
            url: '{{ route("programs.index") }}',
            method: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#programs-grid').html(response.html);
                $('#programs-pagination').html(response.pagination);
                
                // Update URL tanpa reload
                const url = new URL(window.location);
                Object.keys(formData).forEach(key => {
                    if (formData[key]) {
                        url.searchParams.set(key, formData[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                window.history.pushState({}, '', url);
            },
            error: function() {
                alert('Gagal memuat program. Silakan coba lagi.');
            },
            complete: function() {
                loadingOverlay.removeClass('active');
            }
        });
    }

    function resetFilters() {
        $('input[name="category"]').prop('checked', false);
        $('#cat-all').prop('checked', true);
        $('#filter-difficulty').val('').trigger('change');
        $('#filter-rating').val('').trigger('change');
        $('#filter-sort').val('newest').trigger('change');
        $('#search-input').val('');
        applyFilters();
    }

    // Handle pagination links with AJAX
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const loadingOverlay = $('#loading-overlay');
        loadingOverlay.addClass('active');

        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#programs-grid').html(response.html);
                $('#programs-pagination').html(response.pagination);
                window.history.pushState({}, '', url);
            },
            error: function() {
                alert('Gagal memuat halaman. Silakan coba lagi.');
            },
            complete: function() {
                loadingOverlay.removeClass('active');
            }
        });
    });
</script>
@endpush
