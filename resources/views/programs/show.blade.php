@extends('layouts.app')

@section('title', $program->title)

@section('page-title', 'Detail Program')

@push('styles')
<style>
    .program-banner {
        height: 400px;
        object-fit: cover;
        width: 100%;
    }
    .sticky-cta {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 100;
    }
    .review-card {
        border-left: 3px solid #0d6efd;
        padding-left: 15px;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card position-relative" style="overflow: hidden;">
            @if($program->banner)
                <img src="{{ $program->banner_url }}" class="program-banner" alt="{{ $program->title }}">
            @else
                <div class="program-banner bg-primary d-flex align-items-center justify-content-center">
                    <h2 class="text-white">{{ $program->title }}</h2>
                </div>
            @endif
            <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    {{ strtoupper($program->distance_target) }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-8">
        <!-- Program Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="mb-3">{{ $program->title }}</h2>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <span class="badge bg-{{ $program->difficulty == 'beginner' ? 'success' : ($program->difficulty == 'intermediate' ? 'warning' : 'danger') }}">
                        {{ ucfirst($program->difficulty) }}
                    </span>
                    <span class="badge bg-info">
                        <i class="fas fa-clock me-1"></i>{{ $program->duration_weeks ?? 12 }} Minggu
                    </span>
                    @if($program->average_rating > 0)
                        <span class="badge bg-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($program->average_rating) ? '' : 'text-muted' }}"></i>
                            @endfor
                            {{ number_format($program->average_rating, 1) }}
                        </span>
                    @endif
                    <span class="badge bg-secondary">
                        <i class="fas fa-users me-1"></i>{{ $program->enrolled_count }} Peserta
                    </span>
                </div>

                <!-- Coach Info -->
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ $program->coach->avatar ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" 
                         class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;" alt="{{ $program->coach->name }}">
                    <div>
                        <h6 class="mb-0">{{ $program->coach->name }}</h6>
                        <small class="text-muted">Coach</small>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h5>Tentang Program</h5>
                    <p class="text-muted">{!! nl2br(e($program->description)) !!}</p>
                </div>

                <!-- Sessions Preview -->
                @if($program->program_json && isset($program->program_json['sessions']))
                    <div class="mb-4">
                        <h5>Preview Program (Minggu Pertama)</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Hari</th>
                                        <th>Jenis</th>
                                        <th>Jarak</th>
                                        <th>Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($program->program_json['sessions'], 0, 7) as $session)
                                        <tr>
                                            <td>Hari {{ $session['day'] ?? '-' }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $session['type'] ?? 'Run')) }}</td>
                                            <td>{{ $session['distance'] ?? '-' }} km</td>
                                            <td>{{ $session['duration'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ulasan & Rating</h5>
            </div>
            <div class="card-body">
                @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                        <div class="review-card mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <img src="{{ $review->runner->avatar ? asset('storage/' . $review->runner->avatar) : asset('images/profile/17.jpg') }}" 
                                     class="rounded-circle me-2" width="40" height="40" alt="{{ $review->runner->name }}">
                                <div>
                                    <h6 class="mb-0">{{ $review->runner->name }}</h6>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <small class="text-muted ms-auto">{{ $review->created_at->format('d M Y') }}</small>
                            </div>
                            @if($review->review)
                                <p class="mb-0">{{ $review->review }}</p>
                            @endif
                        </div>
                    @endforeach

                    {{ $reviews->links() }}
                @else
                    <p class="text-muted text-center py-4">Belum ada ulasan untuk program ini.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <!-- Price & CTA Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="price-display mb-3">
                    @if($program->isFree())
                        <h2 class="text-success">GRATIS</h2>
                    @else
                        <h2 class="text-primary">Rp {{ number_format($program->price, 0, ',', '.') }}</h2>
                    @endif
                </div>

                @if($isEnrolled)
                    <button class="btn btn-success w-100 mb-2" disabled>
                        <i class="fas fa-check me-2"></i>Sudah Terdaftar
                    </button>
                    <a href="{{ route('runner.calendar') }}" class="btn btn-outline-primary w-100">
                        Lihat di Kalender
                    </a>
                @else
                    @if($program->isFree())
                        <form action="{{ route('runner.programs.enroll-free', $program->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-2"></i>Daftar Program Gratis
                            </button>
                        </form>
                    @else
                        @auth
                            @if(auth()->user()->role === 'runner')
                                <form action="{{ route('marketplace.cart.add', $program->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                                    </button>
                                </form>
                                <a href="{{ route('marketplace.cart.index') }}" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-shopping-bag me-2"></i>Lihat Keranjang
                                </a>
                                <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary w-100">
                                    Top-up Wallet
                                </a>
                            @else
                                <p class="text-muted">Silakan login sebagai runner untuk membeli program.</p>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                Login untuk Membeli
                            </a>
                        @endauth
                    @endif
                @endif
            </div>
        </div>

        <!-- Program Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Program</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Durasi:</strong> {{ $program->duration_weeks ?? 12 }} Minggu
                    </li>
                    <li class="mb-2">
                        <strong>Target Jarak:</strong> {{ strtoupper($program->distance_target) }}
                    </li>
                    <li class="mb-2">
                        <strong>Tingkat:</strong> {{ ucfirst($program->difficulty) }}
                    </li>
                    @if($program->target_time)
                        <li class="mb-2">
                            <strong>Target Waktu:</strong> {{ $program->target_time }}
                        </li>
                    @endif
                    <li class="mb-2">
                        <strong>Peserta:</strong> {{ $program->enrolled_count }} orang
                    </li>
                    @if($program->average_rating > 0)
                        <li class="mb-2">
                            <strong>Rating:</strong> 
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($program->average_rating) ? 'text-warning' : 'text-muted' }}"></i>
                            @endfor
                            ({{ number_format($program->average_rating, 1) }})
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Sticky CTA Bar (Mobile) -->
<div class="sticky-cta d-lg-none">
    <div class="container-fluid">
        @if(!$isEnrolled)
            @if($program->isFree())
                <form action="{{ route('runner.programs.enroll-free', $program->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">Daftar Gratis</button>
                </form>
            @else
                    <div class="d-flex gap-2">
                        <div class="flex-grow-1">
                            <strong class="text-primary">Rp {{ number_format($program->price, 0, ',', '.') }}</strong>
                        </div>
                        @auth
                            @if(auth()->user()->role === 'runner')
                                <form action="{{ route('marketplace.cart.add', $program->id) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">Tambah ke Keranjang</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
                        @endauth
                    </div>
            @endif
        @endif
    </div>
</div>
@endsection

