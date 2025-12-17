@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('page-title', 'Keranjang Belanja')

@push('styles')
<style>
    .cart-item-card {
        border-radius: 12px;
        transition: transform 0.3s;
    }
    .cart-item-card:hover {
        transform: translateY(-2px);
    }
    .product-image {
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Keranjang Belanja</h5>
                @if($cartItems->count() > 0)
                    <form action="{{ route('marketplace.cart.clear') }}" method="POST" onsubmit="return confirm('Yakin ingin mengosongkan keranjang?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash me-1"></i>Kosongkan Keranjang
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body">
                @forelse($cartItems as $item)
                    <div class="card cart-item-card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    @if($item->program->thumbnail)
                                        <img src="{{ $item->program->thumbnail_url }}" class="product-image w-100" alt="{{ $item->program->title }}">
                                    @else
                                        <div class="product-image w-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-running fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-7">
                                    <h5 class="mb-2">
                                        <a href="{{ route('programs.show', $item->program->slug) }}" class="text-black">
                                            {{ $item->program->title }}
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        oleh <strong>{{ $item->program->coach->name }}</strong>
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">{{ strtoupper($item->program->distance_target) }}</span>
                                        <span class="badge bg-{{ $item->program->difficulty == 'beginner' ? 'success' : ($item->program->difficulty == 'intermediate' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($item->program->difficulty) }}
                                        </span>
                                    </div>
                                    <p class="text-primary fw-bold mb-0">
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="mb-2">
                                        <label class="form-label small">Quantity</label>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">-</button>
                                            <input type="number" class="form-control text-center" value="{{ $item->quantity }}" 
                                                   id="qty-{{ $item->id }}" min="1" max="10" 
                                                   onchange="updateQuantity({{ $item->id }}, this.value)">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">+</button>
                                        </div>
                                    </div>
                                    <p class="fw-bold mb-2">
                                        Rp <span id="subtotal-{{ $item->id }}">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </p>
                                    <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Keranjang Anda kosong</p>
                        <a href="{{ route('programs.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Pajak</span>
                    <strong>Rp {{ number_format($tax, 0, ',', '.') }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Total</span>
                    <strong class="text-primary fs-5">Rp <span id="cart-total">{{ number_format($total, 0, ',', '.') }}</span></strong>
                </div>
                
                @if($cartItems->count() > 0)
                    <a href="{{ route('marketplace.checkout.index') }}" class="btn btn-primary w-100">
                        <i class="fas fa-credit-card me-2"></i>Checkout
                    </a>
                    <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateQuantity(cartId, quantity) {
        if (quantity < 1 || quantity > 10) return;
        
        $.ajax({
            url: '{{ url("marketplace/cart") }}/' + cartId,
            method: 'PUT',
            data: {
                quantity: quantity,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#qty-' + cartId).val(quantity);
                $('#subtotal-' + cartId).text(response.subtotal.toLocaleString('id-ID'));
                $('#cart-total').text(response.total.toLocaleString('id-ID'));
            },
            error: function() {
                alert('Gagal update quantity');
            }
        });
    }
</script>
@endpush










