@extends('layouts.pacerhub', ['skipHeavyAssets' => true, 'skipLoader' => true])

@php
    $activeSlug = $categorySlug ?? null;
    $isPopular = ($sort ?? 'latest') === 'popular';
    $searchValue = $search ?? '';

    $firstArticle = $articles->first();
    $ogImage = $firstArticle && method_exists($firstArticle, 'getFeaturedImageUrl')
        ? $firstArticle->getFeaturedImageUrl()
        : asset('ruanglari.webp');

    $schemaArticles = [];
    foreach ($articles->take(10) as $idx => $art) {
        $schemaArticles[] = [
            '@type' => 'ListItem',
            'position' => $idx + 1,
            'url' => route('blog.show', $art->slug),
            'name' => $art->localized_title,
        ];
    }

    $collectionSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $metaTitle,
        'description' => $metaDescription,
        'url' => $canonicalUrl,
        'inLanguage' => app()->getLocale() === 'en' ? 'en-US' : 'id-ID',
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $schemaArticles,
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Beranda',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Blog',
                'item' => route('blog.index'),
            ],
        ],
    ];

    if ($activeCategory) {
        $breadcrumbSchema['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $activeCategory->name,
            'item' => route('blog.category', $activeCategory->slug),
        ];
    }
@endphp

@section('title', $metaTitle)
@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_image', $ogImage)
@if(!empty($isSearch))
@section('meta_robots', 'noindex, follow')
@endif

@push('styles')
<style>
    .blog-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('head')
@if($articles->previousPageUrl())
<link rel="prev" href="{{ $articles->previousPageUrl() }}">
@endif
@if($articles->nextPageUrl())
<link rel="next" href="{{ $articles->nextPageUrl() }}">
@endif
@endpush

@push('structured_data')
<script type="application/ld+json">{!! json_encode($collectionSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-200 pt-0 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-slate-400">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ url('/') }}" class="hover:text-white transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                @if($activeCategory)
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition">Blog</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-slate-200 font-medium">{{ $activeCategory->name }}</li>
                @else
                    <li class="text-slate-200 font-medium">Blog</li>
                @endif
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            <div class="lg:col-span-8 min-w-0">
                <header class="flex flex-col md:flex-row md:items-end md:justify-between gap-5 pb-6 border-b border-slate-800">
                    <div>
                        <h1 class="blog-heading text-2xl sm:text-3xl lg:text-4xl text-white leading-tight">
                            {{ $pageHeading }}
                        </h1>
                        <p class="mt-2 text-slate-300 text-sm max-w-2xl leading-relaxed">
                            {{ $pageSubheading }}
                        </p>
                        <div class="mt-3 inline-flex rounded-md bg-slate-900 border border-slate-800 p-1">
                            <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded text-xs font-semibold transition-colors {{ app()->getLocale() === 'id' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded text-xs font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">EN</a>
                        </div>
                    </div>

                    <div class="w-full md:w-auto">
                        <div class="flex flex-col sm:flex-row gap-2.5">
                            <div class="flex-1 sm:w-64">
                                <label for="blog-q" class="sr-only">Cari artikel</label>
                                <input id="blog-q" type="search" value="{{ $searchValue }}" placeholder="Cari artikel atau topik..." class="w-full px-3.5 py-2 rounded-md bg-slate-900 border border-slate-800 text-sm text-slate-200 placeholder-slate-400 focus:outline-none focus:border-lime-400 transition-colors">
                            </div>
                            <div>
                                <label for="blog-sort" class="sr-only">Urutkan artikel</label>
                                <select id="blog-sort" class="w-full sm:w-36 px-3 py-2 rounded-md bg-slate-900 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:border-lime-400 transition-colors cursor-pointer">
                                    <option value="latest" {{ !$isPopular ? 'selected' : '' }}>Terbaru</option>
                                    <option value="popular" {{ $isPopular ? 'selected' : '' }}>Terpopuler</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="mt-5 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <a href="{{ route('blog.index') }}" data-cat-kind="chip" data-category="" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border text-xs font-semibold transition-colors {{ $activeSlug ? 'border-slate-800 bg-slate-900 text-slate-300 hover:text-white hover:border-slate-700' : 'border-lime-400 bg-lime-400 text-slate-950' }}">
                        <span>Semua</span>
                        <span data-cat-count class="text-xs font-mono tabular-nums {{ $activeSlug ? 'text-slate-400' : 'text-slate-900' }}">{{ $categories->sum('published_articles_count') }}</span>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blog.category', $cat->slug) }}" data-cat-kind="chip" data-category="{{ $cat->slug }}" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border text-xs font-semibold transition-colors {{ $activeSlug === $cat->slug ? 'border-lime-400 bg-lime-400 text-slate-950' : 'border-slate-800 bg-slate-900 text-slate-300 hover:text-white hover:border-slate-700' }}">
                            <span>{{ $cat->name }}</span>
                            <span data-cat-count class="text-xs font-mono tabular-nums {{ $activeSlug === $cat->slug ? 'text-slate-900' : 'text-slate-400' }}">{{ $cat->published_articles_count }}</span>
                        </a>
                    @endforeach
                </div>

                <section class="mt-6">
                    <div class="flex items-center justify-between mb-5 pb-2.5 border-b border-slate-800">
                        <h2 class="blog-heading text-lg text-white">
                            {{ $activeCategory ? 'Artikel ' . $activeCategory->name : 'Semua Artikel' }}
                        </h2>
                        <div id="blog-status" class="text-xs font-mono tabular-nums text-slate-400"></div>
                    </div>

                    <div id="blog-inline-loader" class="hidden mb-6 rounded-lg border border-slate-800 bg-slate-900 p-5">
                        <div class="flex items-center gap-3 text-slate-200">
                            <div class="w-4 h-4 border-2 border-slate-600 border-t-lime-400 rounded-full animate-spin"></div>
                            <div class="text-xs font-mono">Memuat artikel...</div>
                        </div>
                    </div>

                    <div id="blog-results">
                        @include('blog.partials.results', ['articles' => $articles])
                    </div>
                </section>
            </div>

            <aside class="lg:col-span-4 mt-8 lg:mt-0">
                <div class="sticky top-24 space-y-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 class="blog-heading text-base text-white">Kategori Artikel</h3>
                            <a href="{{ route('blog.index') }}" class="text-xs text-slate-300 hover:text-white transition-colors">Semua</a>
                        </div>
                        <div class="mt-3 space-y-1.5">
                            <a href="{{ route('blog.index') }}" data-cat-kind="sidebar" data-category="" class="blog-cat w-full flex items-center justify-between px-3 py-2 rounded-md border text-xs transition-colors {{ $activeSlug ? 'border-slate-800 bg-slate-950 text-slate-300 hover:border-slate-700 hover:text-white' : 'border-lime-400 bg-lime-400 text-slate-950 font-semibold' }}">
                                <span data-cat-label class="{{ $activeSlug ? 'text-slate-300' : 'text-slate-950 font-semibold' }}">Semua Kategori</span>
                                <span data-cat-count class="text-xs font-mono tabular-nums {{ $activeSlug ? 'text-slate-400' : 'text-slate-900' }}">{{ $categories->sum('published_articles_count') }}</span>
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('blog.category', $cat->slug) }}" data-cat-kind="sidebar" data-category="{{ $cat->slug }}" class="blog-cat w-full flex items-center justify-between px-3 py-2 rounded-md border text-xs transition-colors {{ $activeSlug === $cat->slug ? 'border-lime-400 bg-lime-400 text-slate-950 font-semibold' : 'border-slate-800 bg-slate-950 text-slate-300 hover:border-slate-700 hover:text-white' }}">
                                    <span data-cat-label class="{{ $activeSlug === $cat->slug ? 'text-slate-950 font-semibold' : 'text-slate-300' }}">{{ $cat->name }}</span>
                                    <span data-cat-count class="text-xs font-mono tabular-nums {{ $activeSlug === $cat->slug ? 'text-slate-900' : 'text-slate-400' }}">{{ $cat->published_articles_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <h3 class="blog-heading text-base text-white pb-3 border-b border-slate-800">
                            Program &amp; Kalender Lari
                        </h3>
                        <div class="mt-3.5 space-y-2.5">
                            <a href="{{ route('programs.realistic') }}" class="group block p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="text-xs font-semibold text-white group-hover:text-lime-300 transition-colors flex items-center justify-between">
                                    <span>Program Latihan VDOT</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 font-semibold">Gratis</span>
                                </div>
                                <p class="text-xs text-slate-300 mt-1 leading-relaxed">Buat training plan lari personal berbasis metode Jack Daniels.</p>
                            </a>
                            <a href="{{ url('/programs') }}" class="group block p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="text-xs font-semibold text-white group-hover:text-lime-300 transition-colors">
                                    Katalog Program Lari
                                </div>
                                <p class="text-xs text-slate-300 mt-1 leading-relaxed">Pilihan program 5K, 10K, dan Half Marathon dari coach bersertifikasi.</p>
                            </a>
                            <a href="{{ url('/events') }}" class="group block p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="text-xs font-semibold text-white group-hover:text-lime-300 transition-colors">
                                    Kalender Race Indonesia
                                </div>
                                <p class="text-xs text-slate-300 mt-1 leading-relaxed">Jadwal lengkap event lari jalan raya dan trail di seluruh Indonesia.</p>
                            </a>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <h3 class="blog-heading text-base text-white pb-3 border-b border-slate-800">Artikel Populer</h3>
                        <div class="mt-3.5 space-y-3.5">
                            @foreach($trending as $t)
                                @php
                                    $tDt = $t->published_at ?: $t->created_at;
                                @endphp
                                <a href="{{ route('blog.show', $t->slug) }}" class="group flex items-start gap-3">
                                    <div class="w-7 h-7 rounded bg-slate-950 border border-slate-800 flex items-center justify-center text-xs font-mono tabular-nums font-semibold text-slate-300 group-hover:text-lime-300 group-hover:border-slate-700 transition-colors flex-none">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-white leading-snug line-clamp-2 group-hover:text-lime-300 transition-colors">
                                            {{ $t->localized_title }}
                                        </div>
                                        <div class="mt-1 text-xs font-mono tabular-nums text-slate-400 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            @if($t->category)
                                                <span>{{ $t->category->name }}</span>
                                            @endif
                                            <span>{{ optional($tDt)->format('d M') }}</span>
                                            <span>{{ number_format((int) ($t->views_count ?? 0)) }} dibaca</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const qInput = document.getElementById('blog-q');
    const sortSelect = document.getElementById('blog-sort');
    const resultsEl = document.getElementById('blog-results');
    const statusEl = document.getElementById('blog-status');
    const inlineLoaderEl = document.getElementById('blog-inline-loader');
    const catButtons = () => Array.from(document.querySelectorAll('.blog-cat'));

    if (!qInput || !sortSelect || !resultsEl) return;

    let activeCategory = @json($activeSlug);
    let activeSort = @json($sort ?? 'latest');
    let activeQuery = @json($searchValue);
    let abortController = null;
    let debounceTimer = null;

    const setStatus = (text) => {
        if (!statusEl) return;
        statusEl.textContent = text || '';
    };

    const setInlineLoading = (isLoading) => {
        if (!inlineLoaderEl) return;
        inlineLoaderEl.classList.toggle('hidden', !isLoading);
    };

    const buildUrl = (overrides = {}) => {
        const category = overrides.category !== undefined ? overrides.category : activeCategory;
        const q = overrides.q !== undefined ? overrides.q : activeQuery;
        const sort = overrides.sort !== undefined ? overrides.sort : activeSort;
        const page = overrides.page !== undefined ? overrides.page : null;

        let basePath = category ? ('/blog/kategori/' + encodeURIComponent(category)) : '/blog';
        const params = new URLSearchParams();

        if (q) params.set('q', q);
        if (sort && sort !== 'latest') params.set('sort', sort);
        if (page) params.set('page', page);

        const qs = params.toString();
        return basePath + (qs ? ('?' + qs) : '');
    };

    const setCatButtonState = (btn, isActive) => {
        const isSidebar = btn.getAttribute('data-cat-kind') === 'sidebar';
        btn.classList.remove(
            'border-slate-800', 'bg-slate-900', 'bg-slate-950', 'text-slate-300',
            'hover:text-white', 'hover:border-slate-700', 'border-lime-400', 'bg-lime-400',
            'text-slate-950', 'font-semibold'
        );

        if (isActive) {
            btn.classList.add('border-lime-400', 'bg-lime-400', 'text-slate-950', 'font-semibold');
        } else {
            btn.classList.add(
                'border-slate-800',
                isSidebar ? 'bg-slate-950' : 'bg-slate-900',
                'text-slate-300',
                'hover:text-white',
                'hover:border-slate-700'
            );
        }

        const label = btn.querySelector('[data-cat-label]');
        if (label) {
            label.classList.remove('text-slate-950', 'font-semibold', 'text-slate-300');
            if (isActive) {
                label.classList.add('text-slate-950', 'font-semibold');
            } else {
                label.classList.add('text-slate-300');
            }
        }

        const count = btn.querySelector('[data-cat-count]');
        if (count) {
            count.classList.remove('text-slate-900', 'text-slate-400');
            if (isActive) {
                count.classList.add('text-slate-900');
            } else {
                count.classList.add('text-slate-400');
            }
        }
    };

    const markActiveCats = () => {
        catButtons().forEach((btn) => {
            const slug = btn.getAttribute('data-category') || '';
            const isActive = (activeCategory || '') === slug;
            setCatButtonState(btn, isActive);
        });
    };

    const fetchAndRender = async ({ push = true, page = null } = {}) => {
        if (abortController) abortController.abort();
        abortController = new AbortController();

        const url = buildUrl({ page });
        setStatus('Memuat...');
        setInlineLoading(true);

        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: abortController.signal
            });
            if (!res.ok) throw new Error('Request gagal');

            const html = await res.text();
            resultsEl.innerHTML = html;
            setStatus('');
            setInlineLoading(false);
            markActiveCats();
            if (typeof window.phHideLoader === 'function') {
                window.phHideLoader();
            }

            if (push) {
                history.pushState({}, '', url);
            }

            if (page) {
                const navOffset = 90;
                const topPos = resultsEl.getBoundingClientRect().top + window.pageYOffset - navOffset;
                window.scrollTo({ top: Math.max(0, topPos), behavior: 'smooth' });
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
            setStatus('Gagal memuat. Coba lagi.');
            setInlineLoading(false);
        }
    };

    const onCategoryClick = (btn) => {
        const slug = btn.getAttribute('data-category') || '';
        activeCategory = slug || null;
        fetchAndRender({ push: true, page: null });
    };

    catButtons().forEach((btn) => btn.addEventListener('click', (e) => {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return;
        e.preventDefault();
        onCategoryClick(btn);
    }));

    qInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            activeQuery = qInput.value.trim();
            fetchAndRender({ push: true, page: null });
        }, 350);
    });

    sortSelect.addEventListener('change', () => {
        activeSort = sortSelect.value;
        fetchAndRender({ push: true, page: null });
    });

    resultsEl.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (!a) return;
        if (!a.getAttribute('href')) return;

        const href = a.getAttribute('href');
        if (href.includes('page=')) {
            e.preventDefault();
            try {
                const url = new URL(href, window.location.origin);
                const page = url.searchParams.get('page');
                fetchAndRender({ push: true, page });
            } catch (_) {}
        }
    });

    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        const matchCat = url.pathname.match(/\/blog\/kategori\/([^\/?#]+)/);
        activeCategory = matchCat ? decodeURIComponent(matchCat[1]) : (url.searchParams.get('category') || null);
        activeQuery = (url.searchParams.get('q') || '').trim();
        activeSort = url.searchParams.get('sort') || 'latest';
        qInput.value = activeQuery;
        sortSelect.value = activeSort;
        fetchAndRender({ push: false, page: url.searchParams.get('page') });
    });
})();
</script>
@endpush
@endsection
