@extends('layouts.pacerhub')

@php
    $isEn = app()->getLocale() === 'en';
    $baseUrl = route('blog.show', $article->slug);
    $enUrl = $baseUrl . '?lang=en';
    
    if ($isEn) {
        $canonicalUrl = $article->canonical_url_en ?: $enUrl;
    } else {
        $canonicalUrl = $article->canonical_url ?: $baseUrl;
    }
    
    $metaTitle = $article->localized_meta_title ?: $article->localized_title . ' | Ruang Lari';
    $metaDescription = $article->localized_meta_description ?: ($article->localized_excerpt ?: Str::limit(preg_replace('/\s+/', ' ', trim(strip_tags($article->localized_content))), 160));
    $publishedAtIso = optional($article->published_at ?: $article->created_at)?->toIso8601String();
    $modifiedAtIso = optional($article->updated_at ?: $article->created_at)?->toIso8601String();
    $hasEnglish = !empty($article->title_en) && !empty($article->content_en);
@endphp

@section('html_lang', app()->getLocale())
@section('title', $metaTitle)
@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $article->localized_meta_keywords ?? '')
@section('canonical_url', $canonicalUrl)
@section('og_type', 'article')
@section('og_url', $canonicalUrl)
@section('article_published_time', $publishedAtIso)
@section('article_modified_time', $modifiedAtIso)
@section('article_author', $article->user?->name ?? 'Ruang Lari')
@section('article_section', $article->category?->name ?? 'Blog')
@section('article_tags', $article->tags->pluck('name')->implode(', '))

@section('hreflang_tags')
    <link rel="alternate" hreflang="id" href="{{ $baseUrl }}" />
    @if($hasEnglish)
        <link rel="alternate" hreflang="en" href="{{ $enUrl }}" />
    @endif
    <link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}" />
@endsection

@php
    $bgImage = method_exists($article, 'getFeaturedImageUrl')
        ? $article->getFeaturedImageUrl()
        : ($article->featured_image ? (Str::startsWith($article->featured_image, ['http://', 'https://']) ? $article->featured_image : asset('storage/' . ltrim($article->featured_image, '/'))) : asset('ruanglari.webp'));
@endphp

@if($bgImage)
    @section('og_image', $bgImage)
@endif

@push('structured_data')
@php
    $schemaImage = $bgImage ?: asset('images/ruanglari.webp');
    $schemaDescription = $metaDescription;
    $breadcrumbItems = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => url('/'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Blog',
            'item' => route('blog.index'),
        ],
    ];

    if ($article->category) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $article->category->name,
            'item' => route('blog.category', $article->category->slug),
        ];
    }

    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => count($breadcrumbItems) + 1,
        'name' => $article->localized_title,
        'item' => $canonicalUrl,
    ];

    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl,
        ],
        'headline' => $article->localized_title,
        'description' => $schemaDescription,
        'image' => [$schemaImage],
        'datePublished' => $publishedAtIso,
        'dateModified' => $modifiedAtIso,
        'author' => [
            '@type' => 'Person',
            'name' => $article->user?->name ?? 'Ruang Lari',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Ruang Lari',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/green/favicon-32x32.png'),
            ],
        ],
        'articleSection' => $article->category?->name ?? 'Blog',
        'keywords' => implode(', ', array_filter([$article->localized_meta_keywords, $article->tags->pluck('name')->implode(', ')])),
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('styles')
<style>
    .vertical-text {
        writing-mode: vertical-lr;
        text-orientation: mixed;
    }
    /* Reader friendly blog & rich text prose styling */
    .prose p {
        font-size: 1.125rem !important;
        line-height: 1.85 !important;
        color: #cbd5e1 !important; /* slate-300 */
        margin-top: 1.25rem !important;
        margin-bottom: 1.75rem !important;
    }
    .prose strong, .prose b {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .prose em, .prose i {
        font-style: italic !important;
        color: #e2e8f0 !important;
    }
    .prose a {
        color: #a3e635 !important;
        text-decoration: underline !important;
        text-underline-offset: 4px !important;
        font-weight: 600 !important;
        transition: color 0.2s ease !important;
    }
    .prose a:hover {
        color: #ccff00 !important;
    }
    .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
        color: #ffffff !important;
        font-weight: 800 !important;
        line-height: 1.3 !important;
        letter-spacing: -0.02em !important;
    }
    .prose h1 {
        font-size: 2.25rem !important;
        margin-top: 2.5rem !important;
        margin-bottom: 1.25rem !important;
    }
    .prose h2 {
        font-size: 1.875rem !important;
        margin-top: 2.5rem !important;
        margin-bottom: 1.25rem !important;
        border-bottom: 1px solid rgba(51, 65, 85, 0.5) !important;
        padding-bottom: 0.5rem !important;
    }
    .prose h3 {
        font-size: 1.5rem !important;
        margin-top: 2rem !important;
        margin-bottom: 1rem !important;
    }
    .prose h4 {
        font-size: 1.25rem !important;
        margin-top: 1.75rem !important;
        margin-bottom: 0.75rem !important;
    }
    .prose h5 {
        font-size: 1.125rem !important;
        margin-top: 1.5rem !important;
        margin-bottom: 0.5rem !important;
        font-weight: 700 !important;
    }
    .prose h6 {
        font-size: 1rem !important;
        margin-top: 1.25rem !important;
        margin-bottom: 0.5rem !important;
        font-weight: 700 !important;
        color: #94a3b8 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    /* INLINE FORMATTING & TINYMCE FORMATS */
    .prose u {
        text-decoration: underline !important;
        text-underline-offset: 4px !important;
    }
    .prose s, .prose del, .prose strike {
        text-decoration: line-through !important;
        color: #94a3b8 !important;
    }
    .prose sub {
        vertical-align: sub !important;
        font-size: 0.75em !important;
    }
    .prose sup {
        vertical-align: super !important;
        font-size: 0.75em !important;
    }
    .prose mark {
        background-color: rgba(234, 179, 8, 0.25) !important;
        color: #fef08a !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.25rem !important;
        border: 1px solid rgba(234, 179, 8, 0.4) !important;
    }
    .prose kbd {
        background-color: #334155 !important;
        color: #ffffff !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.25rem !important;
        font-family: monospace !important;
        border: 1px solid #475569 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.5) !important;
    }

    /* LISTS (Unordered, Ordered & Definition Lists) */
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
        padding-left: 0.25rem !important;
        line-height: 1.8 !important;
        color: #cbd5e1 !important;
    }
    .prose li::marker {
        color: #a3e635 !important;
        font-weight: bold !important;
    }
    .prose ul ul, .prose ol ul {
        list-style-type: circle !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .prose ul ol, .prose ol ol {
        list-style-type: lower-alpha !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .prose dl {
        margin-top: 1.25rem !important;
        margin-bottom: 1.75rem !important;
    }
    .prose dt {
        font-weight: 700 !important;
        color: #ffffff !important;
        margin-top: 0.75rem !important;
    }
    .prose dd {
        padding-left: 1.5rem !important;
        color: #cbd5e1 !important;
        margin-bottom: 0.75rem !important;
    }

    /* TABLES (TinyMCE & Standard HTML Tables) */
    .prose table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin-top: 2rem !important;
        margin-bottom: 2rem !important;
        border: 1px solid #1e293b !important; /* slate-800 */
        border-radius: 0.5rem !important;
        overflow: hidden !important;
        background-color: #080D17 !important;
        box-shadow: none !important;
    }
    .prose caption {
        caption-side: bottom !important;
        margin-top: 0.75rem !important;
        font-size: 0.875rem !important;
        color: #94a3b8 !important;
        text-align: center !important;
        font-style: italic !important;
    }
    .prose thead {
        background-color: #1e293b !important; /* slate-800 */
    }
    .prose th {
        padding: 0.875rem 1.25rem !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #ffffff !important;
        border-bottom: 2px solid #334155 !important;
    }
    .prose td {
        padding: 0.875rem 1.25rem !important;
        font-size: 0.9375rem !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid rgba(51, 65, 85, 0.5) !important;
    }
    .prose tbody tr:last-child td {
        border-bottom: none !important;
    }
    .prose tbody tr:nth-child(even) {
        background-color: rgba(30, 41, 59, 0.35) !important;
    }
    .prose tbody tr:hover {
        background-color: rgba(51, 65, 85, 0.45) !important;
    }

    /* ALIGNMENTS */
    .prose .text-left, .prose [align="left"] { text-align: left !important; }
    .prose .text-center, .prose [align="center"] { text-align: center !important; }
    .prose .text-right, .prose [align="right"] { text-align: right !important; }
    .prose .text-justify, .prose [align="justify"] { text-align: justify !important; }

    /* BLOCKQUOTES */
    .prose blockquote {
        border-left: 4px solid #a3e635 !important;
        background-color: #0B1220 !important;
        padding: 1.25rem 1.5rem !important;
        margin-top: 2rem !important;
        margin-bottom: 2rem !important;
        border-top-right-radius: 0.5rem !important;
        border-bottom-right-radius: 0.5rem !important;
        font-style: italic !important;
        color: #f8fafc !important;
    }
    .prose blockquote p {
        margin-bottom: 0 !important;
        font-size: 1.15rem !important;
        color: #f8fafc !important;
    }

    /* CODE & PRE */
    .prose code {
        background-color: #1e293b !important;
        color: #a3e635 !important;
        padding: 0.2rem 0.45rem !important;
        border-radius: 0.375rem !important;
        font-family: monospace !important;
        font-size: 0.875em !important;
        border: 1px solid rgba(51, 65, 85, 0.8) !important;
    }
    .prose pre {
        background-color: #0f172a !important;
        color: #f1f5f9 !important;
        padding: 1.25rem !important;
        border-radius: 0.5rem !important;
        overflow-x: auto !important;
        margin-top: 1.75rem !important;
        margin-bottom: 1.75rem !important;
        border: 1px solid #334155 !important;
    }
    .prose pre code {
        background-color: transparent !important;
        padding: 0 !important;
        border: none !important;
        color: inherit !important;
    }

    /* IMAGES, FIGURES & EMBEDS */
    .prose img {
        margin-top: 2.25rem !important;
        margin-bottom: 2.25rem !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2) !important;
        border: 1px solid #1e293b !important;
        max-width: 100% !important;
        height: auto !important;
        display: block !important;
    }
    .prose figure {
        margin-top: 2.25rem !important;
        margin-bottom: 2.25rem !important;
        text-align: center !important;
    }
    .prose figcaption {
        font-size: 0.875rem !important;
        color: #94a3b8 !important;
        margin-top: 0.75rem !important;
        font-style: italic !important;
    }
    .prose iframe, .prose video {
        width: 100% !important;
        aspect-ratio: 16 / 9 !important;
        border-radius: 0.5rem !important;
        margin-top: 2rem !important;
        margin-bottom: 2rem !important;
        border: 1px solid #1e293b !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2) !important;
    }
    .prose hr {
        border: none !important;
        border-top: 1px solid #334155 !important;
        margin-top: 2.5rem !important;
        margin-bottom: 2.5rem !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#020617] pt-0 pb-20 text-slate-200">
    <!-- Reading Progress Bar -->
    <div class="fixed top-0 left-0 w-full h-1 bg-slate-800 z-[100]">
        <div id="readingProgress" class="h-full bg-[#C7FF00] w-0 transition-all duration-100"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between md:hidden">
            <a href="{{ route('home') }}" class="inline-flex items-center text-xs text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="truncate max-w-[220px]">{{ $article->localized_title }}</span>
            </a>
            <div class="inline-flex rounded-md bg-[#080D17] border border-slate-800 p-1">
                <a href="{{ route('blog.show', ['slug' => $article->slug, 'lang' => 'id']) }}" class="px-3 py-1.5 rounded text-xs font-bold transition-colors {{ app()->getLocale() === 'id' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">ID</a>
                <a href="{{ route('blog.show', ['slug' => $article->slug, 'lang' => 'en']) }}" class="px-3 py-1.5 rounded text-xs font-bold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">EN</a>
            </div>
        </div>
        <div class="hidden md:flex items-center justify-between gap-4">
            <nav class="text-xs text-slate-400 font-mono" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center hover:text-white transition-colors">
                            <i class="fas fa-home mr-2"></i>
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-slate-600 mx-2 text-[10px]"></i>
                            <a href="{{ route('blog.index') }}" class="text-slate-400 hover:text-white transition-colors">Blog</a>
                        </div>
                    </li>
                    @if($article->category)
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-slate-600 mx-2 text-[10px]"></i>
                            <a href="{{ route('blog.category', $article->category->slug) }}" class="text-slate-400 hover:text-white transition-colors">{{ $article->category->name }}</a>
                        </div>
                    </li>
                    @endif
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-slate-600 mx-2 text-[10px]"></i>
                            <span class="text-slate-200 font-bold truncate max-w-[200px] md:max-w-xs">{{ $article->localized_title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="inline-flex rounded-md bg-[#080D17] border border-slate-800 p-1">
                <a href="{{ route('blog.show', ['slug' => $article->slug, 'lang' => 'id']) }}" class="px-3.5 py-1.5 rounded text-xs font-bold transition-colors {{ app()->getLocale() === 'id' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">ID</a>
                <a href="{{ route('blog.show', ['slug' => $article->slug, 'lang' => 'en']) }}" class="px-3.5 py-1.5 rounded text-xs font-bold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">EN</a>
            </div>
        </div>
    </div>

    <!-- Title and Image will be rendered cleanly inside the content grid below -->

    <div class="relative z-20 mt-1 md:mt-4">
        <!-- Desktop Floating Share Sidebar -->
        @php
            $shareUrl = urlencode(url()->current());
            $shareText = urlencode($article->localized_title);
        @endphp
        <div class="hidden lg:flex flex-col items-center gap-2.5 fixed left-6 xl:left-12 top-1/2 -translate-y-1/2 z-40 bg-[#080D17] border border-slate-800 p-2.5 rounded-lg shadow-xl">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest vertical-text mb-2 select-none">BAGIKAN</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-md bg-[#0B1220] border border-slate-800 hover:border-slate-700 hover:text-white flex items-center justify-center text-slate-400 transition-colors relative group" aria-label="Share ke Facebook">
                <i class="fab fa-facebook-f text-xs"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded bg-[#080D17] text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-slate-800 shadow-xl">Facebook</span>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-md bg-[#0B1220] border border-slate-800 hover:border-slate-700 hover:text-white flex items-center justify-center text-slate-400 transition-colors relative group" aria-label="Share ke X">
                <i class="fab fa-x-twitter text-xs"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded bg-[#080D17] text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-slate-800 shadow-xl">X (Twitter)</span>
            </a>
            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-md bg-[#0B1220] border border-slate-800 hover:border-slate-700 hover:text-white flex items-center justify-center text-slate-400 transition-colors relative group" aria-label="Share ke WhatsApp">
                <i class="fab fa-whatsapp text-xs"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded bg-[#080D17] text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-slate-800 shadow-xl">WhatsApp</span>
            </a>
            <button onclick="copyArticleLink(this)" class="w-9 h-9 rounded-md bg-[#0B1220] border border-slate-800 hover:border-slate-700 hover:text-white flex items-center justify-center text-slate-400 transition-colors relative group" aria-label="Salin Tautan">
                <i class="fas fa-link text-xs"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded bg-[#080D17] text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-slate-800 shadow-xl">Salin Link</span>
            </button>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-8 min-w-0">
                    <!-- Article Header & Title (Editorial Style) -->
                    <div class="mb-8 mt-1">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white leading-tight tracking-tight mb-4">
                            {{ $article->localized_title }}
                        </h1>
                        
                        <div class="flex items-center flex-wrap gap-2.5 text-xs text-slate-300">
                            @if($article->category)
                                <a href="{{ route('blog.category', $article->category->slug) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-[#0B1220] hover:bg-[#111A2C] text-slate-200 border border-slate-800 text-[11px] font-semibold transition-colors">
                                    {{ $article->category->name }}
                                </a>
                                <span class="text-slate-600 select-none">•</span>
                            @endif
                            <span class="flex items-center gap-1.5 py-1 text-slate-400">
                                <i class="far fa-calendar-alt text-slate-500"></i>
                                {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                            </span>
                            @if($article->user)
                                <span class="text-slate-600 select-none">•</span>
                                <span class="flex items-center gap-1.5 py-1 text-slate-300 font-medium">
                                    <i class="far fa-user text-slate-500"></i>
                                    {{ $article->user->name }}
                                </span>
                            @endif
                            <span class="text-slate-600 select-none">•</span>
                            <span class="flex items-center gap-1.5 py-1 text-slate-400 font-mono" title="Jumlah dibaca">
                                <i class="far fa-eye text-slate-500"></i>
                                {{ number_format((int) ($article->views_count ?? 0)) }} {{ app()->getLocale() === 'en' ? 'views' : 'dibaca' }}
                            </span>
                        </div>

                        <!-- Mobile Share Bar -->
                        <div class="flex lg:hidden items-center gap-2 mt-4 pt-3 border-t border-slate-800">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-2">Bagikan:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 flex items-center justify-center text-slate-400 active:text-white" aria-label="Share ke Facebook">
                                <i class="fab fa-facebook-f text-xs"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 flex items-center justify-center text-slate-400 active:text-white" aria-label="Share ke X">
                                <i class="fab fa-x-twitter text-xs"></i>
                            </a>
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 flex items-center justify-center text-slate-400 active:text-white" aria-label="Share ke WhatsApp">
                                <i class="fab fa-whatsapp text-xs"></i>
                            </a>
                            <button onclick="copyArticleLink(this)" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 flex items-center justify-center text-slate-400 active:text-white" aria-label="Salin Tautan">
                                <i class="fas fa-link text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Feature Image (Clean magazine aspect-ratio, solid border) -->
                    <div class="relative w-full h-[240px] md:h-[460px] overflow-hidden rounded-lg border border-slate-800 shadow-xl mb-10 bg-[#080D17]">
                        @if($bgImage)
                            <img src="{{ $bgImage }}" alt="{{ $article->localized_title }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/ruanglari.webp') }}" alt="{{ $article->localized_title }}" class="w-full h-full object-cover">
                        @endif
                    </div>

                    @if($article->localized_excerpt)
                        <div class="text-lg md:text-xl text-slate-300 leading-relaxed font-normal mb-8 border-l-2 border-slate-600 pl-4 italic">
                            {{ $article->localized_excerpt }}
                        </div>
                    @endif

                    <article class="prose prose-invert prose-lg max-w-none 
                        prose-headings:font-bold prose-headings:text-white prose-headings:tracking-tight
                        prose-p:text-slate-300 prose-p:leading-relaxed
                        prose-a:text-slate-200 prose-a:underline hover:prose-a:text-white
                        prose-strong:text-white
                        prose-blockquote:border-l-2 prose-blockquote:border-slate-600 prose-blockquote:bg-[#0B1220] prose-blockquote:py-2 prose-blockquote:px-5 prose-blockquote:rounded-r-md prose-blockquote:not-italic
                        prose-ul:list-disc prose-ul:text-slate-300
                        prose-ol:list-decimal prose-ol:text-slate-300
                        prose-img:rounded-lg prose-img:shadow-lg prose-img:border prose-img:border-slate-800
                        prose-hr:border-slate-800">
                        
                        {!! $article->localized_content !!}
                        
                    </article>

                    <div class="mt-14 pt-6 border-t border-slate-800 flex flex-col sm:flex-row gap-6 justify-between items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors group text-xs font-mono">
                            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                            <span>Kembali ke Beranda</span>
                        </a>
                        <div class="flex items-center gap-2.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Bagikan artikel:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors" aria-label="Share ke Facebook">
                                <i class="fab fa-facebook-f text-xs"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors" aria-label="Share ke X">
                                <i class="fab fa-x-twitter text-xs"></i>
                            </a>
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors" aria-label="Share ke WhatsApp">
                                <i class="fab fa-whatsapp text-xs"></i>
                            </a>
                            <button onclick="copyArticleLink(this)" class="w-8 h-8 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors" aria-label="Salin Tautan">
                                <i class="fas fa-link text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <aside class="lg:col-span-4 mt-12 lg:mt-0">
                    <div class="sticky top-24">
                        <!-- Single Sidebar Card with Tabs -->
                        <div x-data="{ activeTab: 'trending' }" class="bg-[#080D17] border border-slate-800 rounded-lg p-5">
                            <!-- Tabs Header Navigation -->
                            <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                                <button 
                                    @click="activeTab = 'trending'" 
                                    :class="activeTab === 'trending' ? 'text-white border-white font-bold' : 'text-slate-400 border-transparent hover:text-slate-200 font-medium'"
                                    class="text-xs uppercase tracking-wider py-1.5 px-3 border-b-2 transition-all duration-200 cursor-pointer">
                                    Trending Artikel
                                </button>
                                <button 
                                    @click="activeTab = 'events'" 
                                    :class="activeTab === 'events' ? 'text-white border-white font-bold' : 'text-slate-400 border-transparent hover:text-slate-200 font-medium'"
                                    class="text-xs uppercase tracking-wider py-1.5 px-3 border-b-2 transition-all duration-200 cursor-pointer">
                                    Jadwal Lari
                                </button>
                            </div>

                            <!-- Tab 1: Trending Articles -->
                            <div x-show="activeTab === 'trending'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                @if(isset($trending) && $trending->count() > 0)
                                    <div class="space-y-3.5 mt-4">
                                        @foreach($trending as $t)
                                            @php
                                                $tImg = null;
                                                if ($t->featured_image) {
                                                    $tImg = Str::startsWith($t->featured_image, ['http://', 'https://'])
                                                        ? $t->featured_image
                                                        : asset('storage/' . ltrim($t->featured_image, '/'));
                                                }
                                            @endphp
                                            <a href="{{ route('blog.show', $t->slug) }}" class="group flex items-start gap-3 pb-3 border-b border-slate-800/80 last:border-0 last:pb-0">
                                                <div class="w-6 h-6 rounded bg-[#0B1220] border border-slate-800 flex items-center justify-center text-xs font-mono font-bold text-slate-400 flex-none select-none">
                                                    {{ $loop->iteration }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs font-semibold text-slate-200 group-hover:text-white leading-snug line-clamp-2 transition-colors">
                                                        {{ $t->localized_title }}
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-2 text-[10px] text-slate-500 font-mono">
                                                        @if($t->category)
                                                            <span class="text-slate-400 font-sans font-medium">{{ $t->category->name }}</span>
                                                            <span>•</span>
                                                        @endif
                                                        <span>{{ number_format((int) ($t->views_count ?? 0)) }} dibaca</span>
                                                    </div>
                                                </div>
                                                @if($tImg)
                                                    <div class="w-11 h-11 rounded-md bg-slate-900 border border-slate-800 flex-none overflow-hidden">
                                                        <img src="{{ $tImg }}" alt="{{ $t->localized_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                    </div>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>

                                    <div class="mt-5 pt-3.5 border-t border-slate-800 text-center">
                                        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-mono text-slate-400 hover:text-white transition-colors">
                                            <span>Lihat Semua Artikel</span>
                                            <i class="fas fa-arrow-right text-[10px]"></i>
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-4 text-xs text-slate-500 italic text-center py-3">Belum ada artikel populer.</div>
                                @endif
                            </div>

                            <!-- Tab 2: Jadwal Lari Terdekat -->
                            <div x-show="activeTab === 'events'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                                @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                                    <div class="space-y-2.5 mt-4">
                                        @foreach($upcomingEvents as $ev)
                                            @php
                                                $evDate = $ev->start_at ? \Carbon\Carbon::parse($ev->start_at) : null;
                                                $cityName = $ev->city ? $ev->city->name : ($ev->location_name ?: 'Indonesia');
                                            @endphp
                                            <a href="{{ url('/events/' . $ev->slug) }}" class="group flex items-center gap-3 p-2.5 rounded-md bg-[#0B1220] hover:bg-[#111A2C] border border-slate-800 hover:border-slate-700 transition-colors">
                                                <div class="flex-none w-10 h-10 rounded bg-[#080D17] border border-slate-800 flex flex-col items-center justify-center text-center p-1">
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase font-mono leading-none">{{ $evDate ? $evDate->translatedFormat('M') : 'EVT' }}</span>
                                                    <span class="text-xs font-bold text-white font-mono leading-tight mt-0.5">{{ $evDate ? $evDate->format('d') : '•' }}</span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs font-bold text-slate-200 group-hover:text-white truncate transition-colors">
                                                        {{ $ev->name }}
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-400 font-mono">
                                                        <i class="fas fa-map-marker-alt text-[9px] text-slate-500"></i>
                                                        <span class="truncate">{{ $cityName }}</span>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-slate-300 transition-colors mr-1"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-4 text-xs text-slate-500 italic text-center py-3">Belum ada jadwal event mendatang.</div>
                                @endif

                                <div class="mt-5 pt-3.5 border-t border-slate-800 text-center">
                                    <a href="{{ url('/events') }}" class="inline-flex items-center gap-1.5 text-xs font-mono text-slate-400 hover:text-white transition-colors">
                                        <span>Jelajahi Kalender Event</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    @if(isset($relatedArticles) && $relatedArticles->count() > 0)
    <div class="mt-20 py-14 bg-[#080D17] border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-bold text-white">Artikel Lainnya dari {{ $article->category->name ?? 'Blog' }}</h3>
                <a href="{{ $article->category ? route('blog.category', $article->category->slug) : route('blog.index') }}" class="text-xs font-mono text-slate-400 hover:text-white transition-colors">Lihat Semua →</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedArticles as $related)
                <a href="{{ route('blog.show', $related->slug) }}" class="group flex flex-col bg-[#080D17] rounded-lg overflow-hidden border border-slate-800 hover:border-slate-700 hover:bg-[#0B1220] transition-colors">
                    <div class="relative h-44 overflow-hidden bg-slate-900">
                        @php
                            $relImage = method_exists($related, 'getFeaturedImageUrl')
                                ? $related->getFeaturedImageUrl()
                                : asset('ruanglari.webp');
                        @endphp
                        <img src="{{ $relImage }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';">
                        <div class="absolute top-2 right-2 bg-black/70 px-2 py-0.5 rounded text-[10px] font-mono text-slate-300">
                            {{ $related->published_at ? $related->published_at->format('d M') : $related->created_at->format('d M') }}
                        </div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-white mb-2 line-clamp-2 group-hover:text-slate-200 transition-colors">{{ $related->title }}</h4>
                            <p class="text-slate-400 text-xs line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($related->excerpt ?? $related->content), 90) }}</p>
                        </div>
                        <div class="mt-4 text-xs font-semibold text-slate-300 inline-flex items-center gap-1 group-hover:text-white transition-colors">
                            <span>Baca Selengkapnya</span>
                            <span class="transition-transform group-hover:translate-x-0.5">→</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    
    <div class="pb-16"></div>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('scroll', () => {
        const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        const progressBar = document.getElementById('readingProgress');
        if (progressBar) {
            progressBar.style.width = scrolled + '%';
        }
    });

    function copyArticleLink(btn) {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const icon = btn.querySelector('i');
            if (icon) {
                const originalClass = icon.className;
                icon.className = 'fas fa-check text-emerald-400';
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            }
            
            const tooltip = btn.querySelector('span');
            if (tooltip) {
                const originalText = tooltip.innerText;
                tooltip.innerText = 'Tersalin!';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.prose table').forEach(function(table) {
            if (!table.parentElement.classList.contains('table-responsive')) {
                var wrapper = document.createElement('div');
                wrapper.className = 'table-responsive overflow-x-auto my-6 rounded-lg border border-slate-800';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    });
</script>
@endpush
