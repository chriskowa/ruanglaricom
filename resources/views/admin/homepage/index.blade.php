@extends('layouts.pacerhub')
@php($withSidebar = true)
@section('title', 'Homepage Content')

@section('content')
<div class="max-w-4xl mx-auto p-8 bg-slate-900 rounded-xl overflow-hidden shadow-xl border border-slate-700">
    <h2 class="text-2xl font-bold text-white mb-6">Homepage Content Management</h2>

    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500 text-green-100 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.homepage.content.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-slate-300 mb-2" for="headline">Headline</label>
                <input type="text" name="headline" id="headline" value="{{ old('headline', $content->headline) }}" class="w-full bg-slate-800 border border-slate-700 rounded p-2 text-white focus:outline-none focus:border-neon">
            </div>
            <div>
                <label class="block text-slate-300 mb-2" for="subheadline">Subheadline</label>
                <input type="text" name="subheadline" id="subheadline" value="{{ old('subheadline', $content->subheadline) }}" class="w-full bg-slate-800 border border-slate-700 rounded p-2 text-white focus:outline-none focus:border-neon">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 mb-2" for="hero_image">Hero Background Image</label>
            @if($content->hero_image)
                <div class="mb-2">
                    <img src="{{ asset($content->hero_image) }}" alt="Current Hero Image" class="h-32 rounded object-cover">
                </div>
            @endif
            <input type="file" name="hero_image" id="hero_image" class="block w-full text-sm text-slate-400
                file:mr-4 file:py-2 file:px-4
                file:rounded-full file:border-0
                file:text-sm file:font-semibold
                file:bg-neon file:text-dark
                hover:file:bg-neon/90">
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 mb-2" for="floating_image">Floating Image (Right Side)</label>
            @if($content->floating_image)
                <div class="mb-2">
                    <img src="{{ asset($content->floating_image) }}" alt="Current Floating Image" class="h-32 rounded object-contain bg-slate-800">
                </div>
            @endif
            <input type="file" name="floating_image" id="floating_image" class="block w-full text-sm text-slate-400
                file:mr-4 file:py-2 file:px-4
                file:rounded-full file:border-0
                file:text-sm file:font-semibold
                file:bg-neon file:text-dark
                hover:file:bg-neon/90">
        </div>

        <button type="submit" class="bg-neon text-dark font-bold py-2 px-6 rounded hover:bg-neon/90 transition">Save Changes</button>
    </form>
</div>
@endsection
