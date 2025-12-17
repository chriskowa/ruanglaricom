@extends('layouts.app')

@section('title', 'Daftar Peserta - ' . $event->name)

@section('content')
@php
    // Generate route templates for JavaScript
    $paymentStatusRouteBase = route('eo.events.transactions.payment-status', ['event' => $event->id, 'transaction_id' => 999999]);
    $paymentStatusRouteBase = preg_replace('/\/999999\/payment-status$/', '/TRANS_ID/payment-status', $paymentStatusRouteBase);
@endphp
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('eo.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('eo.events.index') }}">Master Events</a></li>
        <li class="breadcrumb-item active"><a href="javascript:void(0)">Daftar Peserta</a></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Daftar Peserta - {{ $event->name }}</h4>
                <div>
                    <a href="{{ route('eo.events.participants.export', $event) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success">
                        <i class="fa fa-download me-1"></i>Export CSV
                    </a>
                    <a href="{{ route('eo.events.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filters -->
                <form method="GET" action="{{ route('eo.events.participants', $event) }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status Pembayaran</label>
                            <select name="payment_status" class="form-select">
                                <option value="">Semua</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Pengambilan</label>
                            <select name="is_picked_up" class="form-select">
                                <option value="">Semua</option>
                                <option value="0" {{ request('is_picked_up') === '0' ? 'selected' : '' }}>Belum Diambil</option>
                                <option value="1" {{ request('is_picked_up') === '1' ? 'selected' : '' }}>Sudah Diambil</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('eo.events.participants', $event) }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Summary Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="text-white-50">Total Peserta</h6>
                                <h3 class="text-white mb-0">{{ $participants->total() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="text-white-50">Sudah Bayar</h6>
                                <h3 class="mb-0">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status', 'paid'); })->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="text-white-50">Sudah Diambil</h6>
                                <h3 class="mb-0">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id); })->where('is_picked_up', true)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="text-white-50">Belum Diambil</h6>
                                <h3 class="mb-0">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status', 'paid'); })->where('is_picked_up', false)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Participants Table -->
                <div class="table-responsive">
                    <table class="table table-responsive-md">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Kategori</th>
                                <th>BIB Number</th>
                                <th>Status Pembayaran</th>
                                <th>Status Pengambilan</th>
                                <th>Tanggal Registrasi</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($participants as $index => $participant)
                            <tr>
                                <td><strong>{{ $participants->firstItem() + $index }}</strong></td>
                                <td>{{ $participant->name }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ $participant->phone }}</td>
                                <td>
                                    @if($participant->category)
                                        <span class="badge light badge-info">{{ $participant->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($participant->bib_number)
                                        <span class="badge light badge-primary">{{ $participant->bib_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $paymentStatus = $participant->transaction->payment_status ?? 'pending';
                                        $transaction = $participant->transaction;
                                    @endphp
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                            @if($paymentStatus == 'paid')
                                                <span class="badge light badge-success">Paid</span>
                                            @elseif($paymentStatus == 'pending')
                                                <span class="badge light badge-warning">Pending</span>
                                            @else
                                                <span class="badge light badge-danger">Failed</span>
                                            @endif
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button class="dropdown-item" onclick="updatePaymentStatus({{ $transaction->id }}, 'pending')">
                                                <i class="fa fa-clock me-2"></i>Set Pending
                                            </button></li>
                                            <li><button class="dropdown-item" onclick="updatePaymentStatus({{ $transaction->id }}, 'paid')">
                                                <i class="fa fa-check me-2"></i>Set Paid
                                            </button></li>
                                            <li><button class="dropdown-item" onclick="updatePaymentStatus({{ $transaction->id }}, 'failed')">
                                                <i class="fa fa-times me-2"></i>Set Failed
                                            </button></li>
                                            <li><button class="dropdown-item" onclick="updatePaymentStatus({{ $transaction->id }}, 'expired')">
                                                <i class="fa fa-hourglass-end me-2"></i>Set Expired
                                            </button></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $paymentStatus = $participant->transaction->payment_status ?? 'pending';
                                    @endphp
                                    @if($paymentStatus == 'paid')
                                        <button type="button" class="btn p-0 border-0 bg-transparent" onclick="openPickupModal({{ $participant->id }}, '{{ addslashes($participant->name) }}', {{ $participant->is_picked_up ? 'true' : 'false' }}, '{{ addslashes($participant->picked_up_by ?? '') }}')" style="cursor: pointer;">
                                            @if($participant->is_picked_up)
                                                <span class="badge light badge-success">Sudah Diambil</span>
                                                @if($participant->picked_up_at)
                                                    <small class="text-muted d-block">{{ $participant->picked_up_at->format('d M Y H:i') }}</small>
                                                @endif
                                                @if($participant->picked_up_by)
                                                    <small class="text-muted d-block">Oleh: {{ $participant->picked_up_by }}</small>
                                                @endif
                                            @else
                                                <span class="badge light badge-warning">Belum Diambil</span>
                                            @endif
                                        </button>
                                    @else
                                        @if($participant->is_picked_up)
                                            <span class="badge light badge-success">Sudah Diambil</span>
                                            @if($participant->picked_up_at)
                                                <small class="text-muted d-block">{{ $participant->picked_up_at->format('d M Y H:i') }}</small>
                                            @endif
                                            @if($participant->picked_up_by)
                                                <small class="text-muted d-block">Oleh: {{ $participant->picked_up_by }}</small>
                                            @endif
                                        @else
                                            <span class="badge light badge-secondary">Belum Diambil</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $participant->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-primary light sharp" data-bs-toggle="dropdown">
                                            <svg width="20px" height="20px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24"/>
                                                    <circle fill="#000000" cx="5" cy="12" r="2"/>
                                                    <circle fill="#000000" cx="12" cy="12" r="2"/>
                                                    <circle fill="#000000" cx="19" cy="12" r="2"/>
                                                </g>
                                            </svg>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="mailto:{{ $participant->email }}">
                                                <i class="fa fa-envelope me-2"></i>Kirim Email
                                            </a>
                                            <a class="dropdown-item" href="tel:{{ $participant->phone }}">
                                                <i class="fa fa-phone me-2"></i>Hubungi
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada peserta yang terdaftar.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($participants->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $participants->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Status Pengambilan -->
<div class="modal fade" id="pickupModal" tabindex="-1" aria-labelledby="pickupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pickupModalLabel">Status Pengambilan Race Pack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pickupForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="participant_id" id="participant_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Peserta</label>
                        <input type="text" class="form-control" id="participant_name_display" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Pengambilan <span class="text-danger">*</span></label>
                        <select class="form-select" name="is_picked_up" id="pickup_status" required>
                            <option value="0">Belum Diambil</option>
                            <option value="1">Sudah Diambil</option>
                        </select>
                    </div>

                    <div class="mb-3" id="picked_by_container" style="display: none;">
                        <label class="form-label">Diambil Oleh (PIC/Pendaftar) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="picked_up_by" id="picked_up_by" placeholder="Masukkan nama PIC atau pendaftar">
                        <small class="form-text text-muted">Nama orang yang mengambil race pack (bisa PIC atau pendaftar sendiri)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

// Open pickup modal
function openPickupModal(participantId, participantName, isPickedUp, pickedUpBy) {
    document.getElementById('participant_id').value = participantId;
    document.getElementById('participant_name_display').value = participantName;
    document.getElementById('pickup_status').value = isPickedUp ? '1' : '0';
    document.getElementById('picked_up_by').value = pickedUpBy || '';
    
    const form = document.getElementById('pickupForm');
    form.action = `{{ url('/eo/events/' . $event->id . '/participants') }}/${participantId}/status`;
    
    // Show/hide picked_by_container based on status
    const statusSelect = document.getElementById('pickup_status');
    const pickedByContainer = document.getElementById('picked_by_container');
    const pickedByInput = document.getElementById('picked_up_by');
    
    if (statusSelect.value == '1') {
        pickedByContainer.style.display = 'block';
        pickedByInput.required = true;
    } else {
        pickedByContainer.style.display = 'none';
        pickedByInput.required = false;
    }
    
    // Listen to status change
    statusSelect.onchange = function() {
        if (this.value == '1') {
            pickedByContainer.style.display = 'block';
            pickedByInput.required = true;
        } else {
            pickedByContainer.style.display = 'none';
            pickedByInput.required = false;
            pickedByInput.value = '';
        }
    };
    
    const modal = new bootstrap.Modal(document.getElementById('pickupModal'));
    modal.show();
}

// Update payment status
function updatePaymentStatus(transactionId, status) {
    if (!confirm('Apakah Anda yakin ingin mengubah status pembayaran menjadi ' + status + '?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('payment_status', status);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
    
    // Use route template from PHP
    const url = '{{ $paymentStatusRouteBase }}'.replace('TRANS_ID', transactionId);
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text.substring(0, 500));
                throw new Error('HTTP ' + response.status + ': ' + (text.substring(0, 100) || 'Unknown error'));
            });
        }
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Unexpected response:', text.substring(0, 500));
                throw new Error('Server returned non-JSON response. Status: ' + response.status);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            alert('Status pembayaran berhasil diperbarui');
            window.location.reload();
        } else {
            alert(data?.message || 'Gagal memperbarui status pembayaran');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui status pembayaran: ' + error.message);
    });
}

// Handle pickup form submission
document.addEventListener('DOMContentLoaded', function() {
    const pickupForm = document.getElementById('pickupForm');
    if (pickupForm) {
        pickupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(pickupForm);
            const participantId = formData.get('participant_id');
            
            fetch(pickupForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pickupModal'));
                    modal.hide();
                    alert('Status pengambilan berhasil diperbarui');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal memperbarui status pengambilan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memperbarui status pengambilan');
            });
        });
    }
});
</script>
@endpush





