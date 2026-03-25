@extends('layouts.app')

@section('title', $event->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">{{ $event->name }}</h4>
                <div>
                    <a href="{{ route('eo.events.edit', $event) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('eo.events.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <h5>Detail Event</h5>
                @php
                    $startAt = $event->start_at ?? null;
                    $startDateDisplay = '-';
                    $startTimeDisplay = '-';
                    if ($startAt) {
                        try {
                            if (is_string($startAt)) {
                                $startDateDisplay = \Illuminate\Support\Carbon::parse($startAt)->format('d F Y');
                                $startTimeDisplay = \Illuminate\Support\Carbon::parse($startAt)->format('H:i');
                            } else {
                                $startDateDisplay = $startAt->format('d F Y');
                                $startTimeDisplay = $startAt->format('H:i');
                            }
                        } catch (\Exception $e) {
                            $startDateDisplay = '-';
                            $startTimeDisplay = '-';
                        }
                    }
                @endphp
                <p><strong>Tanggal:</strong> {{ $startDateDisplay }}</p>
                <p><strong>Waktu:</strong> {{ $startTimeDisplay }}</p>
                <p><strong>Lokasi:</strong> {{ $event->location_name ?? '-' }}</p>
                <p><strong>Status:</strong> 
                    @if($event->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </p>
                
                <hr>
                
                <h5>Paket</h5>
                <a href="#" class="btn btn-sm btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPackageModal">Tambah Paket</a>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Quota</th>
                                <th>Terjual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($event->packages as $package)
                            <tr>
                                <td>{{ $package->name }}</td>
                                <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                                <td>{{ $package->quota }}</td>
                                <td>{{ $package->sold_count }}</td>
                                <td>
                                    @if($package->is_sold_out)
                                        <span class="badge bg-danger">Sold Out</span>
                                    @else
                                        <span class="badge bg-success">Available</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('eo.events.packages.destroy', $package) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada paket</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <h5>Kupon</h5>
                <a href="#" class="btn btn-sm btn-primary mb-3">Tambah Kupon</a>
                <!-- Kupon list akan ditambahkan -->
            </div>
        </div>
    </div>
</div>
@endsection









