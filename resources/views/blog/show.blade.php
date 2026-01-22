@extends('layouts.pacerhub')

@section('title', $article->meta_title ?? $article->title)
@section('meta_title', $article->meta_title ?? $article->title)
@section('meta_description', $article->meta_description ?? Str::limit(strip_tags($article->content), 150))
@section('meta_keywords', $article->meta_keywords ?? '')

@php
    $bgImage = null;
    if ($article->featured_image) {
        if (Str::startsWith($article->featured_image, ['http://', 'https://'])) {
            $bgImage = $article->featured_image;
        } else {
            $bgImage = asset('storage/' . $article->featured_image);
        }
    }
@endphp

@if($bgImage)
    @section('og_image', $bgImage)
@endif

@section('content')
<div class="min-h-screen bg-dark">
    <!-- Hero Section / Featured Image -->
    <div class="relative w-full h-[40vh] md:h-[50vh] lg:h-[60vh] overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/50 to-transparent z-10"></div>
        @if($bgImage)
            <img src="{{ $bgImage }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        @else
            <!-- Default gradient if no image -->
            <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900"></div>
        @endif
        
        <div class="absolute bottom-0 left-0 w-full z-20 pb-12 md:pb-20">
            <div class="container mx-auto px-4 md:px-8">
                <div class="max-w-4xl mx-auto">
                    @if($article->category)
                        <span class="inline-block px-3 py-1 mb-4 rounded-full bg-neon/20 text-neon border border-neon/50 text-xs font-bold uppercase tracking-wider">
                            {{ $article->category->name }}
                        </span>
                    @endif
                    <h1 class="text-4xl md:text-6xl font-black text-white mb-4 leading-tight tracking-tight">
                        {{ $article->title }}
                    </h1>
                    @if($article->excerpt)
                        <p class="text-lg md:text-xl text-slate-300 max-w-2xl leading-relaxed">
                            {{ $article->excerpt }}
                        </p>
                    @endif
                    <div class="flex items-center gap-4 mt-6 text-sm text-slate-400 font-mono">
                        <span class="flex items-center gap-2">
                            <i class="far fa-calendar-alt"></i>
                            {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                        </span>
                        @if($article->user)
                            <span class="flex items-center gap-2">
                                <i class="far fa-user"></i>
                                {{ $article->user->name }}
                            </span>
                        @endif
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
                    
                    {!! $article->content !!}
                    
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
