@extends('layouts.app')

@section('title', $event->name)

@section('content')
<!-- Hero Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card position-relative" style="overflow: hidden;">
            @if($event->hero_image_url)
                <img src="{{ $event->hero_image_url }}" class="w-100" style="height: 400px; object-fit: cover;" alt="{{ $event->name }}">
            @else
                <div class="bg-primary d-flex align-items-center justify-content-center" style="height: 400px;">
                    <h2 class="text-white">{{ $event->name }}</h2>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Detail Event</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Waktu Mulai:</strong> {{ $event->start_at->format('d F Y, H:i') }} WIB
                    @if($event->end_at)
                        <br><strong>Waktu Selesai:</strong> {{ $event->end_at->format('d F Y, H:i') }} WIB
                    @endif
                </div>
                <div class="mb-3">
                    <strong>Lokasi:</strong> {{ $event->location_name }}
                    @if($event->location_address)
                        <br><small class="text-muted">{{ $event->location_address }}</small>
                    @endif
                </div>
                @if($event->short_description)
                <div class="mb-3">
                    <strong>Deskripsi Singkat:</strong>
                    <p>{{ $event->short_description }}</p>
                </div>
                @endif
                @if($event->full_description)
                <div class="mb-3">
                    <strong>Deskripsi Lengkap:</strong>
                    <div>{!! nl2br(e($event->full_description)) !!}</div>
                </div>
                @endif
                @if($event->map_embed_url)
                <div class="mb-3">
                    <strong>Peta Lokasi:</strong>
                    <div class="mt-2">
                        {!! $event->map_embed_url !!}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Kategori Lari</h4>
            </div>
            <div class="card-body">
                @forelse($categories as $category)
                <div class="mb-3 p-3 border rounded">
                    <h5>{{ $category->name }}</h5>
                    @if($category->distance_km)
                        <p class="mb-1"><strong>Jarak:</strong> {{ number_format($category->distance_km, 2) }} km</p>
                    @endif
                    @if($category->code)
                        <p class="mb-1"><strong>Kode:</strong> {{ $category->code }}</p>
                    @endif
                    @if($category->price_early || $category->price_regular || $category->price_late)
                        <p class="mb-1"><strong>Harga:</strong>
                            @if($category->price_early)
                                <span class="text-success">Early: Rp {{ number_format($category->price_early, 0, ',', '.') }}</span>
                            @endif
                            @if($category->price_regular)
                                <span class="text-primary">Regular: Rp {{ number_format($category->price_regular, 0, ',', '.') }}</span>
                            @endif
                            @if($category->price_late)
                                <span class="text-danger">Late: Rp {{ number_format($category->price_late, 0, ',', '.') }}</span>
                            @endif
                        </p>
                    @endif
                    @if($category->quota)
                        <p class="mb-1"><strong>Kuota:</strong> {{ number_format($category->quota, 0, ',', '.') }} peserta</p>
                    @endif
                    @if($category->min_age || $category->max_age)
                        <p class="mb-1"><strong>Batas Usia:</strong>
                            @if($category->min_age) Min. {{ $category->min_age }} tahun @endif
                            @if($category->max_age) Max. {{ $category->max_age }} tahun @endif
                        </p>
                    @endif
                    @if($category->cutoff_minutes)
                        <p class="mb-1"><strong>Cut-off Time:</strong> {{ $category->cutoff_minutes }} menit</p>
                    @endif
                    @if(!$category->is_active)
                        <span class="badge bg-secondary">Tidak Aktif</span>
                    @endif
                </div>
                @empty
                <p class="text-muted">Belum ada kategori tersedia</p>
                @endforelse
            </div>
        </div>
        
        @if($event->google_calendar_url)
        <div class="card mt-3">
            <div class="card-body text-center">
                <a href="{{ $event->google_calendar_url }}" target="_blank" class="btn btn-outline-primary w-100">
                    <i class="fa fa-calendar me-2"></i>Tambahkan ke Google Calendar
                </a>
            </div>
        </div>
        @endif
        
        <div class="card mt-3">
            <div class="card-body text-center">
                <a href="{{ route('events.register', $event->slug) }}" class="btn btn-primary btn-lg w-100">
                    Daftar Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
