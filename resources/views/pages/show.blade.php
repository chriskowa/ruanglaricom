@extends('layouts.pacerhub')

@section('title', $page->meta_title ?? $page->title)
@section('meta_title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? Str::limit(strip_tags($page->content), 150))
@section('meta_keywords', $page->meta_keywords ?? '')
@if($page->featured_image)
    @section('og_image', asset('storage/' . $page->featured_image))
@endif

@push('styles')
<style>
    .prose p {
        font-size: 1.125rem !important;
        line-height: 1.85 !important;
        color: #cbd5e1 !important;
        margin-top: 1.25rem !important;
        margin-bottom: 1.75rem !important;
    }
    .prose strong, .prose b {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .prose a {
        color: #a3e635 !important;
        text-decoration: underline !important;
        font-weight: 600 !important;
    }
    .prose h1, .prose h2, .prose h3, .prose h4 {
        color: #ffffff !important;
        font-weight: 800 !important;
    }
    .prose ul {
        list-style-type: disc !important;
        padding-left: 1.75rem !important;
        margin-top: 1.25rem !important;
        margin-bottom: 1.75rem !important;
    }
    .prose ol {
        list-style-type: decimal !important;
        padding-left: 1.75rem !important;
        margin-top: 1.25rem !important;
        margin-bottom: 1.75rem !important;
    }
    .prose li {
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        line-height: 1.8 !important;
        color: #cbd5e1 !important;
    }
    .prose li::marker {
        color: #a3e635 !important;
        font-weight: bold !important;
    }
    .prose table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin-top: 2rem !important;
        margin-bottom: 2rem !important;
        border: 1px solid #334155 !important;
        border-radius: 1rem !important;
        overflow: hidden !important;
        background-color: rgba(15, 23, 42, 0.7) !important;
    }
    .prose thead {
        background-color: #1e293b !important;
    }
    .prose th {
        padding: 0.875rem 1.25rem !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        border-bottom: 2px solid #334155 !important;
    }
    .prose td {
        padding: 0.875rem 1.25rem !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid rgba(51, 65, 85, 0.5) !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-dark">
    <!-- Hero Section / Featured Image -->
    <div class="relative w-full h-[40vh] md:h-[50vh] lg:h-[60vh] overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/50 to-transparent z-10"></div>
        @if($page->featured_image)
            <img src="{{ asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}" class="w-full h-full object-cover">
        @else
            <!-- Default gradient if no image -->
            <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900"></div>
        @endif
        
        <div class="absolute bottom-0 left-0 w-full z-20 pb-12 md:pb-20">
            <div class="container mx-auto px-4 md:px-8">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-6xl font-black text-white mb-4 leading-tight tracking-tight">
                        {{ $page->title }}
                    </h1>
                    @if($page->excerpt)
                        <p class="text-lg md:text-xl text-slate-300 max-w-2xl leading-relaxed">
                            {{ $page->excerpt }}
                        </p>
                    @endif
                    <div class="flex items-center gap-4 mt-6 text-sm text-slate-400 font-mono">
                        <span><i class="far fa-calendar-alt mr-2"></i>{{ $page->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="relative z-20 -mt-10">
        <div class="container mx-auto px-4 md:px-8">
            <div class="max-w-4xl mx-auto bg-card/50 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-6 md:p-12 shadow-2xl">
                <article class="prose prose-invert prose-lg max-w-none 
                    prose-headings:font-bold prose-headings:text-white prose-headings:tracking-tight
                    prose-p:text-slate-300 prose-p:leading-relaxed
                    prose-a:text-neon prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-white
                    prose-blockquote:border-l-4 prose-blockquote:border-neon prose-blockquote:bg-slate-800/50 prose-blockquote:py-2 prose-blockquote:px-6 prose-blockquote:rounded-r-lg prose-blockquote:not-italic
                    prose-ul:list-disc prose-ul:text-slate-300
                    prose-ol:list-decimal prose-ol:text-slate-300
                    prose-img:rounded-2xl prose-img:shadow-lg prose-img:border prose-img:border-slate-700/50
                    prose-hr:border-slate-700">
                    
                    {!! $page->content !!}
                    
                </article>

                <div class="mt-12 pt-8 border-t border-slate-700/50 flex justify-between items-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        <span>Back to Home</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="pb-20"></div>
</div>
@endsection
