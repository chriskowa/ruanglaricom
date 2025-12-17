@extends('layouts.app')

@section('title', 'Checkout')

@section('page-title', 'Checkout')

@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Pembayaran</h5>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('marketplace.checkout.store') }}" method="POST" id="checkout-form">
                    @csrf
                    
                    <!-- Payment Method -->
                    <div class="mb-4">
                        <h5 class="mb-3">Metode Pembayaran</h5>
                        <div class="d-block">
                            <div class="form-check custom-radio mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="wallet" value="wallet" checked>
                                <label class="form-check-label" for="wallet">
                                    <strong>Wallet</strong>
                                    <small class="d-block text-muted">
                                        Saldo: Rp {{ number_format($walletBalance, 0, ',', '.') }}
                                    </small>
                                    @if($walletBalance < $total)
                                        <small class="text-danger d-block">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Saldo tidak cukup. 
                                            <a href="{{ route('wallet.index') }}">Top-up sekarang</a>
                                        </small>
                                    @endif
                                </label>
                            </div>
                            <div class="form-check custom-radio mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="midtrans" value="midtrans" disabled>
                                <label class="form-check-label" for="midtrans">
                                    <strong>Midtrans</strong>
                                    <small class="d-block text-muted">Belum tersedia</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Catatan khusus untuk pesanan..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn">
                        <i class="fas fa-credit-card me-2"></i>Bayar Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan Pesanan</h5>
            </div>
            <div class="card-body">
                <ul class="list-group mb-3">
                    @foreach($cartItems as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="my-0">{{ $item->program->title }}</h6>
                                <small class="text-muted">{{ $item->program->coach->name }}</small>
                            </div>
                            <span class="text-muted">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

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
                    <strong class="text-primary fs-5">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#checkout-form').on('submit', function(e) {
            var submitBtn = $('#submit-btn');
            var form = $(this);
            
            // Disable button and show loading
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
            
            // Allow form to submit normally
            // Form will be submitted via POST to server
        });
    });
</script>
@endpush

