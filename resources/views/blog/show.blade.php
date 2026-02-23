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
<div class="min-h-screen bg-dark pt-6">
    
    <div class="container mx-auto px-4 md:px-8 py-4">
        <div class="flex items-center justify-between md:hidden">
            <a href="{{ route('home') }}" class="inline-flex items-center text-xs text-slate-400 hover:text-neon transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="truncate max-w-[220px]">{{ $article->title }}</span>
            </a>
        </div>
        <nav class="hidden md:flex text-sm text-slate-400 font-mono" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center hover:text-neon transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-600 mx-2 text-xs"></i>
                        <span class="text-slate-400">Blog</span>
                    </div>
                </li>
                @if($article->category)
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-600 mx-2 text-xs"></i>
                        <span class="text-slate-400">{{ $article->category->name }}</span>
                    </div>
                </li>
                @endif
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-600 mx-2 text-xs"></i>
                        <span class="text-slate-200 font-bold truncate max-w-[200px] md:max-w-xs">{{ $article->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="relative w-full h-[40vh] md:h-[50vh] lg:h-[60vh] overflow-hidden rounded-3xl mx-auto container px-4 md:px-8 mt-4">
        <div class="relative w-full h-full rounded-3xl overflow-hidden shadow-2xl border border-slate-700/50">
            <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/20 to-transparent z-10"></div>
            @if($bgImage)
                <img src="{{ $bgImage }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            @else
                {{-- Replaced gradient div with fallback image as requested --}}
                <img src="{{ asset('images/ruanglari.webp') }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            @endif
            
            <div class="absolute bottom-0 left-0 w-full z-20 pb-12 md:pb-16 pl-8 md:pl-12">
                <div class="max-w-4xl">
                    @if($article->category)
                        <span class="inline-block px-3 py-1 mb-4 rounded-full bg-neon/20 text-neon border border-neon/50 text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                            {{ $article->category->name }}
                        </span>
                    @endif
                    <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-white mb-4 leading-tight tracking-tight drop-shadow-lg">
                        {{ $article->title }}
                    </h1>
                    <div class="flex items-center gap-6 text-sm text-slate-200 font-mono">
                        <span class="flex items-center gap-2 backdrop-blur-sm px-2 py-1 rounded-lg bg-black/30">
                            <i class="far fa-calendar-alt text-neon"></i>
                            {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                        </span>
                        @if($article->user)
                            <span class="flex items-center gap-2 backdrop-blur-sm px-2 py-1 rounded-lg bg-black/30">
                                <i class="far fa-user text-neon"></i>
                                {{ $article->user->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 mt-12">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-8 lg:col-start-3">
                    @if($article->excerpt)
                        <div class="text-xl md:text-2xl text-slate-300 leading-relaxed font-light mb-10 border-l-4 border-neon pl-6 italic">
                            {{ $article->excerpt }}
                        </div>
                    @endif

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

                    @php
                        $shareUrl = urlencode(url()->current());
                        $shareText = urlencode($article->title);
                    @endphp

                    <div class="mt-16 pt-8 border-t border-slate-700/50 flex justify-between items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors group">
                            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                            <span>Kembali</span>
                        </a>
                        <div class="flex gap-4">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-neon transition-colors" aria-label="Share ke Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-neon transition-colors" aria-label="Share ke X"><i class="fab fa-x-twitter"></i></a>
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-neon transition-colors" aria-label="Share ke WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($relatedArticles->count() > 0)
    <div class="mt-24 py-16 bg-slate-900/50 border-t border-slate-800">
        <div class="container mx-auto px-4 md:px-8">
            <div class="flex items-center justify-between mb-10">
                <h3 class="text-2xl font-bold text-white">More from {{ $article->category->name ?? 'Blog' }}</h3>
                <a href="{{ $article->category ? route('blog.category', $article->category->slug) : route('blog.index') }}" class="text-sm text-neon hover:underline">View All</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($relatedArticles as $related)
                <a href="{{ route('blog.show', $related->slug) }}" class="group block h-full bg-card rounded-2xl overflow-hidden border border-slate-700 hover:border-neon/50 transition-all hover:shadow-lg hover:shadow-neon/10">
                    <div class="relative h-48 overflow-hidden">
                        @php
                            $relImage = null;
                            if ($related->featured_image) {
                                if (Str::startsWith($related->featured_image, ['http://', 'https://'])) {
                                    $relImage = $related->featured_image;
                                } else {
                                    $relImage = asset('storage/' . $related->featured_image);
                                }
                            }
                        @endphp
                        @if($relImage)
                            <img src="{{ $relImage }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <img src="{{ asset('images/ruanglari.webp') }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-1 rounded text-xs font-mono text-white">
                            {{ $related->published_at ? $related->published_at->format('d M') : $related->created_at->format('d M') }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h4 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-neon transition-colors">{{ $related->title }}</h4>
                        <p class="text-slate-400 text-sm line-clamp-3">{{ Str::limit(strip_tags($related->excerpt ?? $related->content), 100) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    
    <div class="pb-20"></div>
</div>
@endsection
