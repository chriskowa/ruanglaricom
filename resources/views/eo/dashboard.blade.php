@extends('layouts.app')

@section('title', 'Dashboard Event Organizer')

@section('page-title', 'Dashboard Event Organizer')

@section('content')
<div class="row">
    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="widget-stat card bg-primary">
            <div class="card-body p-4">
                <div class="media">
                    <span class="me-3">
                        <i class="flaticon-381-wallet"></i>
                    </span>
                    <div class="media-body text-white text-end">
                        <p class="mb-1">Wallet Balance</p>
                        <h3 class="text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="widget-stat card bg-success">
            <div class="card-body p-4">
                <div class="media">
                    <span class="me-3">
                        <i class="flaticon-381-diamond"></i>
                    </span>
                    <div class="media-body text-white text-end">
                        <p class="mb-1">Total Penghasilan</p>
                        <h3 class="text-white">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-sm-6">
        <div class="widget-stat card">
            <div class="card-body p-4">
                <div class="media ai-icon">
                    <span class="me-3 bgl-success text-success">
                        <svg id="icon-customers" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <div class="media-body">
                        <p class="mb-1">My Events</p>
                        <h4 class="mb-0">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
