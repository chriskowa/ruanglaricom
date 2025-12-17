@extends('layouts.app')

@section('title', 'Daftar Pesanan')

@section('page-title', 'Daftar Pesanan')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h5 class="mb-0">Daftar Pesanan</h5>
            </div>
            <div class="card-body py-0 px-3">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="align-middle">Order Number</th>
                                <th class="align-middle">Tanggal</th>
                                <th class="align-middle">Items</th>
                                <th class="align-middle text-end">Total</th>
                                <th class="align-middle text-end">Status</th>
                                <th class="align-middle text-end">Payment</th>
                                <th class="align-middle text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="py-2">
                                        <strong class="text-primary">#{{ $order->order_number }}</strong>
                                    </td>
                                    <td class="py-2">{{ $order->created_at->format('d M Y H:i') }}</td>
                                    <td class="py-2">
                                        <small>{{ $order->items->count() }} item(s)</small>
                                    </td>
                                    <td class="py-2 text-end">
                                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="py-2 text-end">
                                        @if($order->status == 'completed')
                                            <span class="badge badge-success badge-sm light">Completed</span>
                                        @elseif($order->status == 'processing')
                                            <span class="badge badge-primary badge-sm light">Processing</span>
                                        @elseif($order->status == 'cancelled')
                                            <span class="badge badge-danger badge-sm light">Cancelled</span>
                                        @else
                                            <span class="badge badge-warning badge-sm light">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-end">
                                        @if($order->payment_status == 'paid')
                                            <span class="badge badge-success badge-sm light">Paid</span>
                                        @elseif($order->payment_status == 'failed')
                                            <span class="badge badge-danger badge-sm light">Failed</span>
                                        @else
                                            <span class="badge badge-warning badge-sm light">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-end">
                                        <a href="{{ route('marketplace.orders.show', $order->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Belum ada pesanan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection










