@extends('layouts.pacerhub')

@section('title', $activeCategory ? $activeCategory->name . ' | Blog Ruang Lari' : 'Blog | Ruang Lari')
@section('meta_title', $activeCategory ? 'Arsip Artikel ' . $activeCategory->name . ' - Ruang Lari' : 'Blog Ruang Lari')
@section('meta_description', $activeCategory ? 'Kumpulan artikel, berita, dan tips seputar ' . $activeCategory->name . ' di Ruang Lari.' : 'Portal berita dan insight lari: event, pacer, training, gear, dan komunitas.')
@section('meta_keywords', $activeCategory ? 'blog ' . $activeCategory->name . ', artikel ' . $activeCategory->name : 'blog lari, berita lari, komunitas lari, event lari, training plan, pacer')

@section('content')
@php
    $activeSlug = $categorySlug ?? null;
    $isPopular = ($sort ?? 'latest') === 'popular';
    $searchValue = $search ?? '';
@endphp

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    @keyframes categoryMarquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    .animate-category-marquee {
        display: flex;
        width: max-content;
        animation: categoryMarquee 50s linear infinite;
        will-change: transform;
    }

    .category-marquee-wrapper:hover .animate-category-marquee {
        animation-play-state: paused;
    }
</style>

<div class="min-h-screen bg-dark pt-6 pb-10">
    <div class="container mx-auto px-4 md:px-8">
        <div class="flex flex-col lg:flex-row gap-10 items-start">
            <div class="flex-1 w-full min-w-0">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 text-xs font-mono text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-neon animate-pulse"></span>
                            Portal Blog
                        </div>
                        <h1 class="mt-2 text-3xl md:text-5xl font-black tracking-tight">Ruang Lari Newsroom</h1>
                        <p class="mt-3 text-slate-300 max-w-2xl leading-relaxed">
                            Berita, insight, dan panduan lari yang ringkas tapi dalam. Filter cepat tanpa reload.
                        </p>
                        <div class="mt-4 inline-flex rounded-lg bg-slate-900/70 border border-slate-700 p-1">
                            <a href="{{ route('lang.switch', 'id') }}" class="px-4 py-2 rounded-md text-sm font-bold transition-colors {{ app()->getLocale() === 'id' ? 'bg-neon/15 text-neon' : 'text-slate-300 hover:text-white' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="px-4 py-2 rounded-md text-sm font-bold transition-colors {{ app()->getLocale() === 'en' ? 'bg-neon/15 text-neon' : 'text-slate-300 hover:text-white' }}">EN</a>
                        </div>
                    </div>
                    <div class="w-full md:w-auto">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1 sm:w-[360px]">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                                    <i class="fas fa-search"></i>
                                </div>
                                <input id="blog-q" value="{{ $searchValue }}" placeholder="Cari artikel (judul, ringkasan, isi)..." class="w-full pl-11 pr-4 py-3 rounded-lg bg-slate-900/60 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                            </div>
                            <div class="relative">
                                <select id="blog-sort" class="w-full sm:w-[180px] px-4 py-3 rounded-lg bg-slate-900/60 border border-slate-700 text-slate-200 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                    <option value="latest" {{ ! $isPopular ? 'selected' : '' }}>Terbaru</option>
                                    <option value="popular" {{ $isPopular ? 'selected' : '' }}>Terpopuler</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 text-xs font-mono text-slate-500">
                            Tips: ketik untuk mencari, klik kategori untuk mempersempit.
                        </div>
                    </div>
                </div>

                <div class="mt-8 relative w-full min-w-0 max-w-full overflow-hidden category-marquee-wrapper rounded-lg py-2">
                    <div class="pointer-events-none absolute left-0 top-0 bottom-0 w-8 md:w-16 bg-gradient-to-r from-dark to-transparent z-10"></div>
                    <div class="pointer-events-none absolute right-0 top-0 bottom-0 w-8 md:w-16 bg-gradient-to-l from-dark to-transparent z-10"></div>

                    <div class="flex items-center gap-3 animate-category-marquee">
                        @for($i = 0; $i < 2; $i++)
                            <div class="flex items-center gap-3 flex-none" @if($i > 0) aria-hidden="true" @endif>
                                <a href="{{ route('blog.index') }}" data-cat-kind="chip" data-category="" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-bold transition-all {{ $activeSlug ? 'border-slate-700 text-slate-300 hover:text-white' : 'border-neon/40 bg-neon/10 text-neon' }}">
                                    Semua
                                    <span class="text-xs font-mono text-slate-400">{{ $categories->sum('published_articles_count') }}</span>
                                </a>
                                @foreach($categories as $cat)
                                    <a href="{{ route('blog.category', $cat->slug) }}" data-cat-kind="chip" data-category="{{ $cat->slug }}" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-bold transition-all {{ $activeSlug === $cat->slug ? 'border-neon/40 bg-neon/10 text-neon' : 'border-slate-700 text-slate-300 hover:text-white' }}">
                                        {{ $cat->name }}
                                        <span class="text-xs font-mono text-slate-500">{{ $cat->published_articles_count }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>

                @if($heroArticle)
                <div id="blog-hero">
                    @php
                        $heroImg = method_exists($heroArticle, 'getFeaturedImageUrl')
                            ? $heroArticle->getFeaturedImageUrl()
                            : asset('ruanglari.webp');
                        $heroDt = $heroArticle->published_at ?: $heroArticle->created_at;
                    @endphp

                    <a href="{{ route('blog.show', $heroArticle->slug) }}" class="group block mt-10 rounded-xl overflow-hidden border border-slate-700/60 hover:border-neon/40 transition-all bg-card/40">
                        <div class="relative h-[320px] md:h-[420px] overflow-hidden">
                            <img src="{{ $heroImg }}" alt="{{ $heroArticle->localized_title }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-700" onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';">
                            <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/25 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-7 md:p-10">
                                <div class="flex flex-wrap items-center gap-3 text-xs font-mono text-slate-300">
                                    @if($heroArticle->category)
                                        <span class="px-3 py-1 rounded-md bg-neon/15 text-neon border border-neon/30">{{ $heroArticle->category->name }}</span>
                                    @endif
                                    <span class="inline-flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-neon"></i>
                                        {{ optional($heroDt)->format('d M Y') }}
                                    </span>
                                    <span class="inline-flex items-center gap-2">
                                        <i class="far fa-eye text-neon"></i>
                                        {{ number_format((int) ($heroArticle->views_count ?? 0)) }}
                                    </span>
                                </div>
                                <h2 class="mt-4 text-2xl md:text-4xl font-black leading-tight tracking-tight group-hover:text-neon transition-colors">
                                    {{ $heroArticle->localized_title }}
                                </h2>
                                <p class="mt-3 text-slate-200/90 max-w-3xl leading-relaxed line-clamp-2">
                                    {{ $heroArticle->localized_excerpt ?: Str::limit(strip_tags((string) $heroArticle->localized_content), 160) }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                @endif

                <div class="mt-10">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold">Terbaru</h3>
                        <div id="blog-status" class="text-xs font-mono text-slate-500"></div>
                    </div>

                    <div id="blog-inline-loader" class="hidden mb-6 rounded-xl border border-slate-700/60 bg-card/40 p-6">
                        <div class="flex items-center gap-3 text-slate-300">
                            <div class="w-5 h-5 border-2 border-slate-500 border-t-neon rounded-full animate-spin"></div>
                            <div class="text-sm font-mono">Memuat artikel…</div>
                        </div>
                    </div>

                    <div id="blog-results">
                        @include('blog.partials.results', ['articles' => $articles])
                    </div>
                </div>
            </div>

            <aside class="w-full lg:w-[360px] sticky top-24 space-y-6">
                <div class="bg-card/50 border border-slate-700/60 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold">Kategori</h3>
                        <a href="{{ route('blog.index') }}" class="text-xs font-mono text-slate-400 hover:text-neon transition-colors">Reset</a>
                    </div>
                    <div class="mt-4 space-y-2">
                        <button type="button" data-cat-kind="sidebar" data-category="" class="blog-cat w-full flex items-center justify-between px-4 py-3 rounded-lg border transition-all {{ $activeSlug ? 'border-slate-700 hover:border-slate-500' : 'border-neon/40 bg-neon/10' }}">
                            <span data-cat-label class="{{ $activeSlug ? 'text-slate-200' : 'text-neon font-bold' }}">Semua</span>
                            <span class="text-xs font-mono text-slate-400">{{ $categories->sum('published_articles_count') }}</span>
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" data-cat-kind="sidebar" data-category="{{ $cat->slug }}" class="blog-cat w-full flex items-center justify-between px-4 py-3 rounded-lg border transition-all {{ $activeSlug === $cat->slug ? 'border-neon/40 bg-neon/10' : 'border-slate-700 hover:border-slate-500' }}">
                                <span data-cat-label class="{{ $activeSlug === $cat->slug ? 'text-neon font-bold' : 'text-slate-200' }}">{{ $cat->name }}</span>
                                <span class="text-xs font-mono text-slate-500">{{ $cat->published_articles_count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-card/50 border border-slate-700/60 rounded-xl p-6">
                    <h3 class="font-bold">Trending</h3>
                    <div class="mt-4 space-y-4">
                        @foreach($trending as $t)
                            @php
                                $tDt = $t->published_at ?: $t->created_at;
                            @endphp
                            <a href="{{ route('blog.show', $t->slug) }}" class="group flex gap-3">
                                <div class="w-12 h-12 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-neon font-black">#{{ $loop->iteration }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-bold text-white leading-snug line-clamp-2 group-hover:text-neon transition-colors">
                                        {{ $t->localized_title }}
                                    </div>
                                    <div class="mt-1 text-[11px] font-mono text-slate-400 flex items-center gap-3">
                                        @if($t->category)
                                            <span class="text-slate-500">{{ $t->category->name }}</span>
                                        @endif
                                        <span>{{ optional($tDt)->format('d M') }}</span>
                                        <span class="inline-flex items-center gap-1"><i class="far fa-eye text-neon"></i>{{ number_format((int) ($t->views_count ?? 0)) }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
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
    const heroEl = document.getElementById('blog-hero');

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
        const url = new URL(window.location.href);
        const params = url.searchParams;

        const category = overrides.category !== undefined ? overrides.category : activeCategory;
        const q = overrides.q !== undefined ? overrides.q : activeQuery;
        const sort = overrides.sort !== undefined ? overrides.sort : activeSort;
        const page = overrides.page !== undefined ? overrides.page : null;

        if (category) params.set('category', category); else params.delete('category');
        if (q) params.set('q', q); else params.delete('q');
        if (sort && sort !== 'latest') params.set('sort', sort); else params.delete('sort');
        if (page) params.set('page', page); else params.delete('page');

        return url.pathname + '?' + params.toString();
    };

    const setCatButtonState = (btn, isActive) => {
        btn.classList.remove('border-slate-700', 'text-slate-300', 'hover:text-white', 'hover:border-slate-500', 'border-neon/40', 'bg-neon/10', 'text-neon');

        const kind = btn.getAttribute('data-cat-kind') || (btn.classList.contains('inline-flex') ? 'chip' : 'sidebar');
        if (isActive) {
            btn.classList.add('border-neon/40', 'bg-neon/10');
            if (kind === 'chip') {
                btn.classList.add('text-neon');
            }
        } else {
            btn.classList.add('border-slate-700');
            if (kind === 'chip') {
                btn.classList.add('text-slate-300', 'hover:text-white');
            } else {
                btn.classList.add('hover:border-slate-500');
            }
        }

        const label = btn.querySelector('[data-cat-label]');
        if (label) {
            label.classList.remove('text-neon', 'font-bold', 'text-slate-200');
            if (isActive) {
                label.classList.add('text-neon', 'font-bold');
            } else {
                label.classList.add('text-slate-200');
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

    const setHeroVisibility = (page = null) => {
        if (!heroEl) return;
        const shouldShow = !activeCategory && !activeQuery && (activeSort === 'latest') && (!page || String(page) === '1');
        heroEl.classList.toggle('hidden', !shouldShow);
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
            setHeroVisibility(page);
            if (typeof window.phHideLoader === 'function') {
                window.phHideLoader();
            }

            if (push) {
                const fullUrl = new URL(window.location.href);
                fullUrl.search = url.split('?')[1] || '';
                history.pushState({}, '', fullUrl.toString());
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
        activeCategory = url.searchParams.get('category');
        activeQuery = (url.searchParams.get('q') || '').trim();
        activeSort = url.searchParams.get('sort') || 'latest';
        qInput.value = activeQuery;
        sortSelect.value = activeSort;
        fetchAndRender({ push: false, page: url.searchParams.get('page') });
    });

    setHeroVisibility(new URL(window.location.href).searchParams.get('page'));
})();
</script>
@endsection
