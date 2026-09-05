@extends('layouts.pacerhub')

@section('title', $metaTitle)
@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@if(!empty($isSearch))
@section('meta_robots', 'noindex, follow')
@endif

@push('structured_data')
@php
    $schemaArticles = [];
    foreach($articles->take(10) as $idx => $art) {
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
                'name' => 'Home',
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
<script type="application/ld+json">{!! json_encode($collectionSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@php
    $activeSlug = $categorySlug ?? null;
    $isPopular = ($sort ?? 'latest') === 'popular';
    $searchValue = $search ?? '';
@endphp

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="min-h-screen bg-dark pt-0 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            {{-- Main Column (8 of 12 columns) --}}
            <div class="lg:col-span-8 min-w-0">
                
                {{-- Header & Search Row: Clean Editorial without Floating Span Pills --}}
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5 pb-6 border-b border-slate-800">
                    <div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                            {{ $pageHeading }}
                        </h1>
                        <p class="mt-2 text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            {{ $pageSubheading }}
                        </p>
                        <div class="mt-3 inline-flex rounded-md bg-[#080D17] border border-slate-800 p-1">
                            <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded text-xs font-semibold transition-colors {{ app()->getLocale() === 'id' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded text-xs font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">EN</a>
                        </div>
                    </div>
                    <div class="w-full md:w-auto">
                        <div class="flex flex-col sm:flex-row gap-2.5">
                            <div class="relative flex-1 sm:w-64">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 pointer-events-none">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input id="blog-q" value="{{ $searchValue }}" placeholder="Cari artikel..." class="w-full pl-9 pr-3.5 py-2 rounded-md bg-[#080D17] border border-slate-800 text-xs sm:text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500 transition-all">
                            </div>
                            <div class="relative">
                                <select id="blog-sort" class="w-full sm:w-36 px-3 py-2 rounded-md bg-[#080D17] border border-slate-800 text-xs sm:text-sm text-slate-200 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500 transition-all cursor-pointer">
                                    <option value="latest" {{ ! $isPopular ? 'selected' : '' }}>Terbaru</option>
                                    <option value="popular" {{ $isPopular ? 'selected' : '' }}>Terpopuler</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Category Horizontal Filter Bar (Mobile-friendly Quick Chips) --}}
                <div class="mt-5 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <a href="{{ route('blog.index') }}" data-cat-kind="chip" data-category="" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border text-xs font-semibold transition-all {{ $activeSlug ? 'border-slate-800 bg-[#080D17] text-slate-300 hover:text-white hover:border-slate-700' : 'border-white bg-white text-slate-950 shadow-sm' }}">
                        <span>Semua</span>
                        <span data-cat-count class="text-[11px] font-mono {{ $activeSlug ? 'text-slate-400' : 'text-slate-600' }}">{{ $categories->sum('published_articles_count') }}</span>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blog.category', $cat->slug) }}" data-cat-kind="chip" data-category="{{ $cat->slug }}" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border text-xs font-semibold transition-all {{ $activeSlug === $cat->slug ? 'border-white bg-white text-slate-950 shadow-sm' : 'border-slate-800 bg-[#080D17] text-slate-300 hover:text-white hover:border-slate-700' }}">
                            <span>{{ $cat->name }}</span>
                            <span data-cat-count class="text-[11px] font-mono {{ $activeSlug === $cat->slug ? 'text-slate-600' : 'text-slate-500' }}">{{ $cat->published_articles_count }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Articles Section --}}
                <div class="mt-7">
                    <div class="flex items-center justify-between mb-5 pb-2 border-b border-slate-800">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-300">
                            {{ $activeCategory ? 'Daftar Artikel ' . $activeCategory->name : 'Semua Artikel' }}
                        </h2>
                        <div id="blog-status" class="text-xs font-mono text-slate-400"></div>
                    </div>

                    <div id="blog-inline-loader" class="hidden mb-6 rounded-lg border border-slate-800 bg-[#0B1220] p-5">
                        <div class="flex items-center gap-3 text-slate-300">
                            <div class="w-4 h-4 border-2 border-slate-500 border-t-white rounded-full animate-spin"></div>
                            <div class="text-xs font-mono">Memuat artikel…</div>
                        </div>
                    </div>

                    <div id="blog-results">
                        @include('blog.partials.results', ['articles' => $articles])
                    </div>
                </div>
            </div>

            {{-- Sidebar Column (4 of 12 columns) --}}
            <aside class="lg:col-span-4 mt-8 lg:mt-0">
                <div class="sticky top-24 space-y-5">
                    
                    {{-- Categories Box --}}
                    <div class="bg-[#0B1220] border border-slate-800 rounded-lg p-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">Kategori Artikel</h3>
                            <a href="{{ route('blog.index') }}" class="text-[11px] font-mono text-slate-400 hover:text-white transition-colors">Semua</a>
                        </div>
                        <div class="mt-3 space-y-1.5">
                            <a href="{{ route('blog.index') }}" data-cat-kind="sidebar" data-category="" class="blog-cat w-full flex items-center justify-between px-3 py-2 rounded-md border text-xs transition-all {{ $activeSlug ? 'border-slate-800 bg-[#080D17] text-slate-300 hover:border-slate-700 hover:text-white' : 'border-white bg-white text-slate-950 font-bold shadow-sm' }}">
                                <span data-cat-label class="{{ $activeSlug ? 'text-slate-300' : 'text-slate-950 font-bold' }}">Semua Kategori</span>
                                <span data-cat-count class="text-[11px] font-mono {{ $activeSlug ? 'text-slate-400' : 'text-slate-600' }}">{{ $categories->sum('published_articles_count') }}</span>
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('blog.category', $cat->slug) }}" data-cat-kind="sidebar" data-category="{{ $cat->slug }}" class="blog-cat w-full flex items-center justify-between px-3 py-2 rounded-md border text-xs transition-all {{ $activeSlug === $cat->slug ? 'border-white bg-white text-slate-950 font-bold shadow-sm' : 'border-slate-800 bg-[#080D17] text-slate-300 hover:border-slate-700 hover:text-white' }}">
                                    <span data-cat-label class="{{ $activeSlug === $cat->slug ? 'text-slate-950 font-bold' : 'text-slate-300' }}">{{ $cat->name }}</span>
                                    <span data-cat-count class="text-[11px] font-mono {{ $activeSlug === $cat->slug ? 'text-slate-600' : 'text-slate-500' }}">{{ $cat->published_articles_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Internal Link Ecosystem Card: Running Tools & Programs --}}
                    <div class="bg-[#0B1220] border border-slate-800 rounded-lg p-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300 pb-3 border-b border-slate-800">
                            Fitur & Program Lari
                        </h3>
                        <div class="mt-3.5 space-y-2.5">
                            <a href="{{ route('programs.realistic') }}" class="group block p-3 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="text-xs font-semibold text-white group-hover:text-neon transition-colors flex items-center justify-between">
                                    <span>Program Latihan VDOT</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">GRATIS</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Buat training plan lari personal berbasis metode Jack Daniels.</p>
                            </a>
                            <a href="{{ url('/programs') }}" class="group block p-3 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="text-xs font-semibold text-white group-hover:text-neon transition-colors flex items-center justify-between">
                                    <span>Katalog Program Lari</span>
                                    <i class="fas fa-chevron-right text-[10px] text-slate-500 group-hover:text-white"></i>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Pilihan program 5K, 10K, Half Marathon dari coach bersertifikasi.</p>
                            </a>
                            <a href="{{ url('/events') }}" class="group block p-3 rounded-md bg-[#080D17] border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="text-xs font-semibold text-white group-hover:text-neon transition-colors flex items-center justify-between">
                                    <span>Kalender Race Indonesia</span>
                                    <i class="fas fa-chevron-right text-[10px] text-slate-500 group-hover:text-white"></i>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Jadwal lengkap event lari jalan raya & trail di seluruh Indonesia.</p>
                            </a>
                        </div>
                    </div>

                    {{-- Trending Box --}}
                    <div class="bg-[#0B1220] border border-slate-800 rounded-lg p-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300 pb-3 border-b border-slate-800">Artikel Trending</h3>
                        <div class="mt-3.5 space-y-3">
                            @foreach($trending as $t)
                                @php
                                    $tDt = $t->published_at ?: $t->created_at;
                                @endphp
                                <a href="{{ route('blog.show', $t->slug) }}" class="group flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-mono font-semibold text-slate-300 group-hover:text-white group-hover:border-slate-500 transition-colors flex-none">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-semibold text-white leading-snug line-clamp-2 group-hover:text-neon transition-colors">
                                            {{ $t->localized_title }}
                                        </div>
                                        <div class="mt-1 text-[10px] font-mono text-slate-400 flex items-center gap-1.5">
                                            @if($t->category)
                                                <span>{{ $t->category->name }}</span>
                                                <span class="text-slate-600">•</span>
                                            @endif
                                            <span>{{ optional($tDt)->format('d M') }}</span>
                                            <span class="text-slate-600">•</span>
                                            <span>{{ number_format((int) ($t->views_count ?? 0)) }} views</span>
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
        btn.classList.remove(
            'border-slate-800', 'bg-[#080D17]', 'text-slate-300', 
            'hover:text-white', 'hover:border-slate-700', 'border-white', 'bg-white', 
            'text-slate-950', 'shadow-sm'
        );

        if (isActive) {
            btn.classList.add('border-white', 'bg-white', 'text-slate-950', 'shadow-sm');
        } else {
            btn.classList.add('border-slate-800', 'bg-[#080D17]', 'text-slate-300', 'hover:text-white', 'hover:border-slate-700');
        }

        const label = btn.querySelector('[data-cat-label]');
        if (label) {
            label.classList.remove('text-slate-950', 'font-bold', 'text-slate-300');
            if (isActive) {
                label.classList.add('text-slate-950', 'font-bold');
            } else {
                label.classList.add('text-slate-300');
            }
        }

        const count = btn.querySelector('[data-cat-count]');
        if (count) {
            count.classList.remove('text-slate-600', 'text-slate-400', 'text-slate-500');
            if (isActive) {
                count.classList.add('text-slate-600');
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
        setStatus('Memuat…');
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
@endsection
