@extends('layouts.app')

@section('title', 'Invoice #' . $order->order_number)

@section('page-title', 'Invoice #' . $order->order_number)

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice #{{ $order->order_number }}</h5>
                <a href="{{ route('marketplace.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-print me-1"></i>Print Invoice
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Sukses!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Invoice Header -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Dari:</h6>
                        <strong>Ruang Lari</strong><br>
                        <small class="text-muted">Platform Program Lari Indonesia</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6 class="text-muted">Kepada:</h6>
                        <strong>{{ $order->user->name }}</strong><br>
                        <small class="text-muted">{{ $order->user->email }}</small>
                    </div>
                </div>

                <hr>

                <!-- Order Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Order Number:</strong> {{ $order->order_number }}</p>
                        <p class="mb-1"><strong>Tanggal:</strong> {{ $order->created_at->format('d F Y H:i') }}</p>
                        <p class="mb-1"><strong>Metode Pembayaran:</strong> {{ ucfirst($order->payment_method) }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'processing' ? 'primary' : 'warning') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </p>
                        <p class="mb-1">
                            <strong>Payment:</strong> 
                            <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Order Items -->
                <h6 class="mb-3">Item Pesanan</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->program_title }}</strong>
                                        @if($item->program && $item->program->coach)
                                            <br><small class="text-muted">oleh {{ $item->program->coach->name }}</small>
                                        @elseif($item->program)
                                            <br><small class="text-muted">Program tidak aktif</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Pajak:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($order->tax, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong class="text-primary fs-5">Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($order->notes)
                    <div class="mt-3">
                        <strong>Catatan:</strong>
                        <p class="text-muted">{{ $order->notes }}</p>
                    </div>
                @endif

                <hr>

                <div class="text-end">
                    <a href="{{ route('marketplace.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Pesanan
                    </a>
                    @if($order->payment_status == 'paid')
                        <a href="{{ route('runner.calendar') }}" class="btn btn-primary">
                            <i class="fas fa-calendar me-2"></i>Lihat di Kalender
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

