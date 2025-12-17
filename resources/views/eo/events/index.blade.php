@extends('layouts.app')

@section('title', 'Master Events')

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('eo.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><a href="javascript:void(0)">Master Events</a></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Master Events</h4>
                <a href="{{ route('eo.events.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus me-1"></i>Tambah Event
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-responsive-md">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Nama Event</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Registrasi</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $index => $event)
                            <tr>
                                <td><strong class="text-black">{{ $events->firstItem() + $index }}</td>
                                <td>{{ $event->name }}</td>
                                <td>{{ $event->start_at->format('d F Y') }}</td>
                                <td>{{ $event->start_at->format('H:i') }}</td>
                                <td>{{ $event->location_name }}</td>
                                <td>
                                    <span class="badge light badge-info">
                                        {{ $event->categories->count() }} Kategori
                                    </span>
                                </td>
                                <td>
                                    <span class="badge light badge-success">Active</span>
                                </td>
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
                                            <a class="dropdown-item" href="{{ route('events.show', $event->slug) }}" target="_blank">
                                                <i class="fa fa-eye me-2"></i>Lihat Landing Page
                                            </a>
                                            <a class="dropdown-item" href="{{ route('eo.events.show', $event) }}">
                                                <i class="fa fa-info-circle me-2"></i>Detail
                                            </a>
                                            <a class="dropdown-item" href="{{ route('eo.events.participants', $event) }}">
                                                <i class="fa fa-users me-2"></i>Daftar Peserta
                                            </a>
                                            <a class="dropdown-item" href="{{ route('eo.events.results', $event) }}">
                                                <i class="fa fa-trophy me-2"></i>Race Results
                                            </a>
                                            <a class="dropdown-item" href="{{ route('eo.events.edit', $event) }}">
                                                <i class="fa fa-pencil me-2"></i>Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus event ini?')) { document.getElementById('delete-form-{{ $event->id }}').submit(); }">
                                                <i class="fa fa-trash me-2"></i>Delete
                                            </a>
                                            <form id="delete-form-{{ $event->id }}" action="{{ route('eo.events.destroy', $event) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada event. <a href="{{ route('eo.events.create') }}">Tambah event pertama Anda</a></p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($events->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $events->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection