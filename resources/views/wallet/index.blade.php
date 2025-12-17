@extends('layouts.app')

@section('title', 'Wallet')

@section('page-title', 'Wallet')

@push('styles')
<link rel="stylesheet" href="https://app.sandbox.midtrans.com/snap/snap.css" />
<style>
    .balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
    }
    .quick-topup-btn {
        padding: 15px 25px;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        border-radius: 10px;
        transition: all 0.3s;
    }
    .quick-topup-btn:hover {
        background: #667eea;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-8">
        <!-- Balance Card -->
        <div class="card balance-card mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-2 opacity-75">Saldo Wallet</p>
                    <h2 class="mb-0">Rp {{ number_format($wallet->balance ?? 0, 0, ',', '.') }}</h2>
                    <p class="mb-0 mt-2 small opacity-75">Saldo Tersedia</p>
                </div>
                <div class="text-end">
                    <i class="fas fa-wallet fa-3x opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Top-up Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Top-up Wallet</h5>
            </div>
            <div class="card-body">
                <!-- Quick Amount Buttons -->
                <div class="mb-3">
                    <label class="form-label">Pilih Nominal Cepat</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn quick-topup-btn" data-amount="50000">Rp 50.000</button>
                        <button type="button" class="btn quick-topup-btn" data-amount="100000">Rp 100.000</button>
                        <button type="button" class="btn quick-topup-btn" data-amount="200000">Rp 200.000</button>
                        <button type="button" class="btn quick-topup-btn" data-amount="500000">Rp 500.000</button>
                    </div>
                </div>

                <!-- Top-up Form -->
                <form action="{{ route('wallet.topup') }}" method="POST" id="topup-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nominal Top-up</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="amount" id="topup-amount" class="form-control" 
                                   placeholder="Masukkan nominal" min="10000" max="10000000" required>
                        </div>
                        <small class="text-muted">Minimum: Rp 10.000, Maximum: Rp 10.000.000</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-credit-card me-2"></i>Top-up Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Deskripsi</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : 'danger' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                    <td class="{{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type == 'deposit' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada transaksi
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <!-- Top-up History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Top-up</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @forelse($topups as $topup)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Rp {{ number_format($topup->amount, 0, ',', '.') }}</h6>
                                    <small class="text-muted">{{ $topup->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <span class="badge bg-{{ $topup->status == 'success' ? 'success' : ($topup->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($topup->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Belum ada top-up</p>
                    @endforelse
                </div>
                {{ $topups->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Top-up Payment Modal (Midtrans Snap) -->
<div id="snap-container"></div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    // Initialize snap if not exists
    if (typeof snap === 'undefined') {
        console.error('Midtrans Snap SDK not loaded');
    }
    // Quick top-up buttons
    $('.quick-topup-btn').on('click', function() {
        var amount = $(this).data('amount');
        $('#topup-amount').val(amount);
    });

    // Top-up form submission
    $('#topup-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = form.serialize();
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.testing_mode) {
                    // Testing mode: Auto approved
                    alert(response.message || 'Top-up berhasil! (Testing Mode - Pembayaran otomatis approved)');
                    window.location.reload();
                } else if (response.success && response.snap_token) {
                    // Production/Sandbox mode: Show Midtrans Snap popup
                    snap.pay(response.snap_token, {
                        onSuccess: function(result) {
                            window.location.href = '{{ route("wallet.index") }}?success=1';
                        },
                        onPending: function(result) {
                            window.location.href = '{{ route("wallet.index") }}?pending=1';
                        },
                        onError: function(result) {
                            alert('Top-up gagal. Silakan coba lagi.');
                            submitBtn.prop('disabled', false).html(originalText);
                        },
                        onClose: function() {
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                } else {
                    alert(response.message || 'Gagal membuat transaksi top-up.');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var message = xhr.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.';
                
                if (Object.keys(errors).length > 0) {
                    var errorMessages = Object.values(errors).flat();
                    alert(errorMessages.join('\n'));
                } else {
                    alert(message);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Show success message
    @if(request('success'))
        alert('Top-up berhasil!');
    @endif
</script>
@endpush

