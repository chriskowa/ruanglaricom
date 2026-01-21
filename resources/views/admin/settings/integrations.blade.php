@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Integration Settings')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Integration Settings</h4>
                <p class="mb-0">Manage 3rd party integrations and tracking codes.</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Settings</a></li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tracking & SEO Integrations</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success solid alert-dismissible fade show">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('admin.integration.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Google Analytics (Measurement ID)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="google_analytics" placeholder="G-XXXXXXXXXX" value="{{ $settings['google_analytics'] }}">
                                <small class="text-muted">Enter your GA4 Measurement ID.</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Google Search Console</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="google_search_console" placeholder="HTML Tag Content (e.g. xxxxx-yyyyy-zzzzz)" value="{{ $settings['google_search_console'] }}">
                                <small class="text-muted">Enter only the <code>content</code> value of the meta tag named <code>google-site-verification</code>.</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Bing Search Console</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="bing_search_console" placeholder="HTML Tag Content" value="{{ $settings['bing_search_console'] }}">
                                <small class="text-muted">Enter only the <code>content</code> value of the meta tag named <code>msvalidate.01</code>.</small>
                            </div>
                        </div>

                         <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Google Ads (Conversion ID)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="google_ads_tag" placeholder="AW-XXXXXXXXXX" value="{{ $settings['google_ads_tag'] }}">
                                <small class="text-muted">Enter your Google Ads Conversion ID.</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
