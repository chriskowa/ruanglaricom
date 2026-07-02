@extends('layouts.pacerhub')
@php($withSidebar = true)
@section('title', 'Runner Calendar')
@push('styles')
    @include('runner.calendar.styles')
@endpush
@section('content')
    @include('runner.calendar.html')
@endsection
@push('scripts')
    @include('runner.calendar.scripts')
@endpush
