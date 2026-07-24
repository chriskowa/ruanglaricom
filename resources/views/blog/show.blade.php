@extends('layouts.pacerhub')

@php
    $canonicalUrl = $article->localized_canonical_url ?: route('blog.show', $article->slug);
    $metaTitle = $article->localized_meta_title ?: $article->localized_title . ' | Ruang Lari';
    $metaDescription = $article->localized_meta_description ?: ($article->localized_excerpt ?: Str::limit(preg_replace('/\s+/', ' ', trim(strip_tags($article->localized_content))), 160));
    $publishedAtIso = optional($article->published_at ?: $article->created_at)?->toIso8601String();
    $modifiedAtIso = optional($article->updated_at ?: $article->created_at)?->toIso8601String();
@endphp

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
        'keywords' => $article->tags->pluck('name')->implode(', '),
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
    /* Reader friendly blog styling improvements */
    .prose p {
        font-size: 1.125rem;
        line-height: 1.85;
        color: #cbd5e1; /* slate-300 */
        margin-bottom: 1.75rem;
    }
    .prose blockquote p {
        font-size: 1.25rem;
        line-height: 1.6;
        color: #f8fafc; /* slate-50 */
    }
    .prose h2, .prose h3, .prose h4 {
        color: #ffffff !important;
        font-weight: 800;
        margin-top: 2.5rem;
        margin-bottom: 1.25rem;
    }
    .prose h2 {
        font-size: 1.875rem;
    }
    .prose h3 {
        font-size: 1.5rem;
    }
    .prose img {
        margin-top: 2.5rem;
        margin-bottom: 2.5rem;
        border-radius: 1.5rem;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-dark pt-6 pb-20">
    <!-- Reading Progress Bar -->
    <div class="fixed top-0 left-0 w-full h-1 bg-white/10 z-[100]">
        <div id="readingProgress" class="h-full bg-brand-400 w-0 transition-all duration-100 shadow-[0_0_10px_#4ade80]"></div>
    </div>
    
    <div class="container mx-auto px-4 md:px-8 py-4">
        <div class="flex items-center justify-between md:hidden">
            <a href="{{ route('home') }}" class="inline-flex items-center text-xs text-slate-400 hover:text-neon transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                        <span class="truncate max-w-[220px]">{{ $article->localized_title }}</span>
            </a>
            <div class="inline-flex rounded-2xl bg-slate-900/70 border border-slate-700 p-1">
                <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ app()->getLocale() === 'id' ? 'bg-neon/15 text-neon' : 'text-slate-300 hover:text-white' }}">ID</a>
                <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ app()->getLocale() === 'en' ? 'bg-neon/15 text-neon' : 'text-slate-300 hover:text-white' }}">EN</a>
            </div>
        </div>
        <div class="hidden md:flex items-center justify-between gap-4">
            <nav class="text-sm text-slate-400 font-mono" aria-label="Breadcrumb">
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
                            <span class="text-slate-200 font-bold truncate max-w-[200px] md:max-w-xs">{{ $article->localized_title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="inline-flex rounded-2xl bg-slate-900/70 border border-slate-700 p-1">
                <a href="{{ route('lang.switch', 'id') }}" class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ app()->getLocale() === 'id' ? 'bg-neon/15 text-neon' : 'text-slate-300 hover:text-white' }}">ID</a>
                <a href="{{ route('lang.switch', 'en') }}" class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ app()->getLocale() === 'en' ? 'bg-neon/15 text-neon' : 'text-slate-300 hover:text-white' }}">EN</a>
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
        <div class="hidden lg:flex flex-col items-center gap-3 fixed left-6 xl:left-12 top-1/2 -translate-y-1/2 z-40 bg-slate-900/60 backdrop-blur-lg border border-white/10 p-3 rounded-2xl shadow-2xl">
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest vertical-text mb-2 select-none">BAGIKAN</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-white/5 border border-white/5 hover:border-brand-500/30 hover:bg-brand-500/10 hover:text-brand-400 flex items-center justify-center text-slate-400 transition-all duration-300 hover:scale-110 relative group" aria-label="Share ke Facebook">
                <i class="fab fa-facebook-f text-sm"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-950 text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-white/10 shadow-xl">Facebook</span>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-white/5 border border-white/5 hover:border-sky-500/30 hover:bg-sky-500/10 hover:text-sky-400 flex items-center justify-center text-slate-400 transition-all duration-300 hover:scale-110 relative group" aria-label="Share ke X">
                <i class="fab fa-x-twitter text-sm"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-950 text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-white/10 shadow-xl">X (Twitter)</span>
            </a>
            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-white/5 border border-white/5 hover:border-emerald-500/30 hover:bg-emerald-500/10 hover:text-emerald-400 flex items-center justify-center text-slate-400 transition-all duration-300 hover:scale-110 relative group" aria-label="Share ke WhatsApp">
                <i class="fab fa-whatsapp text-sm"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-950 text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-white/10 shadow-xl">WhatsApp</span>
            </a>
            <button onclick="copyArticleLink(this)" class="w-10 h-10 rounded-xl bg-white/5 border border-white/5 hover:border-brand-400/30 hover:bg-brand-400/10 hover:text-brand-400 flex items-center justify-center text-slate-400 transition-all duration-300 hover:scale-110 relative group" aria-label="Salin Tautan">
                <i class="fas fa-link text-sm"></i>
                <span class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-950 text-white text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none border border-white/10 shadow-xl">Salin Link</span>
            </button>
        </div>

        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-8 lg:col-start-3">
                    <!-- Article Header & Title (Editorial Style) -->
                    <div class="mb-8 mt-1">
                        @if($article->category)
                            <a href="{{ route('blog.category', $article->category->slug) }}" class="inline-block px-3.5 py-1.5 mb-5 rounded-full bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/20 text-xs font-bold uppercase tracking-wider transition-all">
                                {{ $article->category->name }}
                            </a>
                        @endif
                        
                        <h1 class="text-3xl md:text-5xl font-black text-white leading-tight tracking-tight mb-6">
                            {{ $article->localized_title }}
                        </h1>
                        
                        <div class="flex items-center flex-wrap gap-3 text-xs text-slate-400 font-mono">
                            <span class="flex items-center gap-1.5 py-1">
                                <i class="far fa-calendar-alt text-brand-400"></i>
                                {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                            </span>
                            <span class="text-slate-600 font-sans select-none">•</span>
                            @if($article->user)
                                <span class="flex items-center gap-1.5 py-1">
                                    <i class="far fa-user text-brand-400"></i>
                                    {{ $article->user->name }}
                                </span>
                                <span class="text-slate-600 font-sans select-none">•</span>
                            @endif
                            <span class="flex items-center gap-1.5 py-1 text-slate-300 font-semibold" title="Jumlah dibaca">
                                <i class="far fa-eye text-brand-400"></i>
                                {{ number_format((int) ($article->views_count ?? 0)) }} {{ app()->getLocale() === 'en' ? 'views' : 'dibaca' }}
                            </span>
                        </div>

                        <!-- Mobile Share Bar -->
                        <div class="flex lg:hidden items-center gap-2 mt-4 pt-3 border-t border-white/5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mr-2">Bagikan:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 active:bg-brand-500/20 active:text-brand-400" aria-label="Share ke Facebook">
                                <i class="fab fa-facebook-f text-xs"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 active:bg-sky-500/20 active:text-sky-400" aria-label="Share ke X">
                                <i class="fab fa-x-twitter text-xs"></i>
                            </a>
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 active:bg-emerald-500/20 active:text-emerald-400" aria-label="Share ke WhatsApp">
                                <i class="fab fa-whatsapp text-xs"></i>
                            </a>
                            <button onclick="copyArticleLink(this)" class="w-8 h-8 rounded-lg bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 active:bg-brand-400/20 active:text-brand-400" aria-label="Salin Tautan">
                                <i class="fas fa-link text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Feature Image (Separated, rounded, premium aspect-ratio, no overlay) -->
                    <div class="relative w-full h-[220px] md:h-[450px] overflow-hidden rounded-2xl border border-slate-700/50 shadow-2xl mb-12">
                        @if($bgImage)
                            <img src="{{ $bgImage }}" alt="{{ $article->localized_title }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/ruanglari.webp') }}" alt="{{ $article->localized_title }}" class="w-full h-full object-cover">
                        @endif
                    </div>

                    @if($article->localized_excerpt)
                        <div class="text-xl md:text-2xl text-slate-300 leading-relaxed font-light mb-10 border-l-4 border-neon pl-6 italic">
                            {{ $article->localized_excerpt }}
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
                        
                        {!! $article->localized_content !!}
                        
                    </article>

                    <div class="mt-16 pt-8 border-t border-white/10 flex flex-col sm:flex-row gap-6 justify-between items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors group text-sm font-bold">
                            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                            <span>Kembali ke Beranda</span>
                        </a>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-2">Bagikan artikel:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-xl bg-white/5 border border-white/5 hover:border-brand-500/30 hover:bg-brand-500/10 hover:text-brand-400 flex items-center justify-center text-slate-400 transition-colors" aria-label="Share ke Facebook">
                                <i class="fab fa-facebook-f text-xs"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-xl bg-white/5 border border-white/5 hover:border-sky-500/30 hover:bg-sky-500/10 hover:text-sky-400 flex items-center justify-center text-slate-400 transition-colors" aria-label="Share ke X">
                                <i class="fab fa-x-twitter text-xs"></i>
                            </a>
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-xl bg-white/5 border border-white/5 hover:border-emerald-500/30 hover:bg-emerald-500/10 hover:text-emerald-400 flex items-center justify-center text-slate-400 transition-colors" aria-label="Share ke WhatsApp">
                                <i class="fab fa-whatsapp text-xs"></i>
                            </a>
                            <button onclick="copyArticleLink(this)" class="w-9 h-9 rounded-xl bg-white/5 border border-white/5 hover:border-brand-400/30 hover:bg-brand-400/10 hover:text-brand-400 flex items-center justify-center text-slate-400 transition-colors" aria-label="Salin Tautan">
                                <i class="fas fa-link text-xs"></i>
                            </button>
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
                setTimeout(() => {
                    tooltip.innerText = originalText;
                }, 2000);
            }
        });
    }
</script>
@endpush
