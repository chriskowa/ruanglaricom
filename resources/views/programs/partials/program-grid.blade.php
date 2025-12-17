@php use Illuminate\Support\Str; @endphp
@forelse($programs as $program)
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="card program-card h-100">
            <div class="position-relative">
                @if($program->thumbnail)
                    <img src="{{ $program->thumbnail_url }}" class="program-thumbnail" alt="{{ $program->title }}">
                @else
                    <div class="program-thumbnail bg-light d-flex align-items-center justify-content-center">
                        <i class="fas fa-running fa-3x text-muted"></i>
                    </div>
                @endif
                <span class="badge category-badge bg-primary">
                    {{ strtoupper($program->distance_target) }}
                </span>
                @if($program->isFree())
                    <span class="badge bg-success" style="position: absolute; top: 15px; left: 15px; padding: 6px 12px;">GRATIS</span>
                @endif
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">
                    <a href="{{ route('programs.show', $program->slug) }}" class="text-black">{{ Str::limit($program->title, 40) }}</a>
                </h5>
                <p class="text-muted small mb-2">
                    oleh <strong>{{ $program->coach->name }}</strong>
                </p>
                
                @if($program->average_rating > 0)
                    <div class="d-flex align-items-center mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= round($program->average_rating) ? 'text-warning' : 'text-muted' }}" style="font-size: 12px;"></i>
                        @endfor
                        <span class="ms-2 small text-muted">
                            {{ number_format($program->average_rating, 1) }} ({{ $program->total_reviews }})
                        </span>
                    </div>
                @endif
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">
                        <i class="fas fa-users me-1"></i>{{ $program->enrolled_count }} peserta
                    </span>
                    <span class="badge bg-{{ $program->difficulty == 'beginner' ? 'success' : ($program->difficulty == 'intermediate' ? 'warning' : 'danger') }}">
                        {{ ucfirst($program->difficulty) }}
                    </span>
                </div>
                
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="price-display">
                            @if($program->isFree())
                                <span class="text-success fw-bold">GRATIS</span>
                            @else
                                <span class="text-primary fw-bold">Rp {{ number_format($program->price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('programs.show', $program->slug) }}" class="btn btn-sm btn-primary">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center p-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-3">Tidak ada program ditemukan.</p>
                <button type="button" class="btn btn-primary" onclick="resetFilters()">Reset Filter</button>
            </div>
        </div>
    </div>
@endforelse

