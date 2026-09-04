@extends('layouts.pacerhub')

@section('title', $activeCategory ? $activeCategory->name . ' | Blog Ruang Lari' : 'Blog & Warta | Ruang Lari')
@section('meta_title', $activeCategory ? 'Arsip Artikel ' . $activeCategory->name . ' - Ruang Lari' : 'Blog Ruang Lari | Berita & Panduan Lari')
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
</style>

<div class="min-h-screen bg-dark pt-6 pb-16">
    {{-- Outer Container aligned exactly with Navbar Header (max-w-7xl) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            {{-- Main Column (8 of 12 columns) --}}
            <div class="lg:col-span-8 min-w-0">
                
                {{-- Header & Search Row --}}
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 pb-6 border-b border-slate-800">
                    <div>
                        <div class="inline-flex items-center gap-2 text-xs font-mono text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></span>
                            Portal Blog
                        </div>
                        <h1 class="mt-2 text-3xl md:text-4xl lg:text-5xl font-black tracking-tight text-white">Ruang Lari Newsroom</h1>
                        <p class="mt-2 text-slate-300 text-sm md:text-base max-w-2xl leading-relaxed">
                            Berita, insight, dan panduan lari yang ringkas tapi dalam. Filter cepat tanpa reload.
                        </p>
                        <div class="mt-4 inline-flex rounded-md bg-[#080D17] border border-slate-800 p-1">
                            <a href="{{ route('lang.switch', 'id') }}" class="px-3.5 py-1.5 rounded text-xs font-bold transition-colors {{ app()->getLocale() === 'id' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="px-3.5 py-1.5 rounded text-xs font-bold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">EN</a>
                        </div>
                    </div>
                    <div class="w-full md:w-auto">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1 sm:w-64">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 pointer-events-none">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input id="blog-q" value="{{ $searchValue }}" placeholder="Cari artikel..." class="w-full pl-10 pr-4 py-2.5 rounded-md bg-[#080D17] border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500 transition-all">
                            </div>
                            <div class="relative">
                                <select id="blog-sort" class="w-full sm:w-36 px-3.5 py-2.5 rounded-md bg-[#080D17] border border-slate-800 text-sm text-slate-200 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500 transition-all cursor-pointer">
                                    <option value="latest" {{ ! $isPopular ? 'selected' : '' }}>Terbaru</option>
                                    <option value="popular" {{ $isPopular ? 'selected' : '' }}>Terpopuler</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Category Horizontal Filter Bar (Mobile-friendly Quick Chips) --}}
                <div class="mt-6 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <a href="{{ route('blog.index') }}" data-cat-kind="chip" data-category="" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-2 px-3.5 py-2 rounded-md border text-xs font-bold transition-all {{ $activeSlug ? 'border-slate-800 bg-[#080D17] text-slate-300 hover:text-white hover:border-slate-700' : 'border-white bg-white text-slate-950 font-bold shadow-sm' }}">
                        <span>Semua</span>
                        <span data-cat-count class="text-[11px] font-mono {{ $activeSlug ? 'text-slate-400' : 'text-slate-600' }}">{{ $categories->sum('published_articles_count') }}</span>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blog.category', $cat->slug) }}" data-cat-kind="chip" data-category="{{ $cat->slug }}" class="blog-cat flex-none whitespace-nowrap inline-flex items-center gap-2 px-3.5 py-2 rounded-md border text-xs font-bold transition-all {{ $activeSlug === $cat->slug ? 'border-white bg-white text-slate-950 font-bold shadow-sm' : 'border-slate-800 bg-[#080D17] text-slate-300 hover:text-white hover:border-slate-700' }}">
                            <span>{{ $cat->name }}</span>
                            <span data-cat-count class="text-[11px] font-mono {{ $activeSlug === $cat->slug ? 'text-slate-600' : 'text-slate-500' }}">{{ $cat->published_articles_count }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Articles Section --}}
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-800/60">
                        <h2 class="text-base font-bold uppercase tracking-wider text-white">Semua Artikel</h2>
                        <div id="blog-status" class="text-xs font-mono text-slate-400"></div>
                    </div>

                    <div id="blog-inline-loader" class="hidden mb-6 rounded-lg border border-slate-800 bg-[#0B1220] p-6">
                        <div class="flex items-center gap-3 text-slate-300">
                            <div class="w-5 h-5 border-2 border-slate-500 border-t-white rounded-full animate-spin"></div>
                            <div class="text-sm font-mono">Memuat artikel…</div>
                        </div>
                    </div>

                    <div id="blog-results">
                        @include('blog.partials.results', ['articles' => $articles])
                    </div>
                </div>
            </div>

            {{-- Sidebar Column (4 of 12 columns) --}}
            <aside class="lg:col-span-4 mt-10 lg:mt-0">
                <div class="sticky top-24 space-y-6">
                    {{-- Categories Box (Solid Opaque #0B1220) --}}
                    <div class="bg-[#0B1220] border border-slate-800 rounded-lg p-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">Kategori</h3>
                            <a href="{{ route('blog.index') }}" class="text-[11px] font-mono text-slate-400 hover:text-white transition-colors">Reset</a>
                        </div>
                        <div class="mt-3 space-y-1.5">
                            <button type="button" data-cat-kind="sidebar" data-category="" class="blog-cat w-full flex items-center justify-between px-3 py-2.5 rounded-md border text-xs transition-all {{ $activeSlug ? 'border-slate-800 bg-[#080D17] text-slate-300 hover:border-slate-700 hover:text-white' : 'border-white bg-white text-slate-950 font-bold shadow-sm' }}">
                                <span data-cat-label class="{{ $activeSlug ? 'text-slate-300' : 'text-slate-950 font-bold' }}">Semua</span>
                                <span data-cat-count class="text-[11px] font-mono {{ $activeSlug ? 'text-slate-400' : 'text-slate-600' }}">{{ $categories->sum('published_articles_count') }}</span>
                            </button>
                            @foreach($categories as $cat)
                                <button type="button" data-cat-kind="sidebar" data-category="{{ $cat->slug }}" class="blog-cat w-full flex items-center justify-between px-3 py-2.5 rounded-md border text-xs transition-all {{ $activeSlug === $cat->slug ? 'border-white bg-white text-slate-950 font-bold shadow-sm' : 'border-slate-800 bg-[#080D17] text-slate-300 hover:border-slate-700 hover:text-white' }}">
                                    <span data-cat-label class="{{ $activeSlug === $cat->slug ? 'text-slate-950 font-bold' : 'text-slate-300' }}">{{ $cat->name }}</span>
                                    <span data-cat-count class="text-[11px] font-mono {{ $activeSlug === $cat->slug ? 'text-slate-600' : 'text-slate-500' }}">{{ $cat->published_articles_count }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Trending Box (Solid Opaque #0B1220) --}}
                    <div class="bg-[#0B1220] border border-slate-800 rounded-lg p-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-white pb-3 border-b border-slate-800">Trending</h3>
                        <div class="mt-4 space-y-3.5">
                            @foreach($trending as $t)
                                @php
                                    $tDt = $t->published_at ?: $t->created_at;
                                @endphp
                                <a href="{{ route('blog.show', $t->slug) }}" class="group flex items-start gap-3">
                                    <div class="w-7 h-7 rounded bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-mono font-bold text-slate-300 group-hover:text-white group-hover:border-slate-500 transition-colors flex-none">
                                        #{{ $loop->iteration }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-white leading-snug line-clamp-2 group-hover:text-slate-200 transition-colors">
                                            {{ $t->localized_title }}
                                        </div>
                                        <div class="mt-1 text-[11px] font-mono text-slate-400 flex items-center gap-2">
                                            @if($t->category)
                                                <span class="text-slate-400">{{ $t->category->name }}</span>
                                                <span class="text-slate-600">•</span>
                                            @endif
                                            <span>{{ optional($tDt)->format('d M') }}</span>
                                            <span class="text-slate-600">•</span>
                                            <span class="inline-flex items-center gap-1"><i class="far fa-eye text-slate-500"></i> {{ number_format((int) ($t->views_count ?? 0)) }}</span>
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
        const isChip = btn.getAttribute('data-cat-kind') === 'chip';
        
        btn.classList.remove(
            'border-slate-800', 'bg-[#080D17]', 'bg-slate-900/60', 'bg-slate-900/40', 'text-slate-300', 
            'hover:text-white', 'hover:border-slate-700', 'border-white', 'bg-white', 
            'text-slate-950', 'shadow-sm', 'border-neon/40', 'bg-neon/10', 'text-neon', 
            'border-slate-700', 'hover:border-slate-500'
        );

        if (isActive) {
            btn.classList.add('border-white', 'bg-white', 'text-slate-950', 'shadow-sm');
        } else {
            btn.classList.add('border-slate-800', 'bg-[#080D17]', 'text-slate-300', 'hover:text-white', 'hover:border-slate-700');
        }

        const label = btn.querySelector('[data-cat-label]');
        if (label) {
            label.classList.remove('text-slate-950', 'font-bold', 'text-slate-200', 'text-slate-300', 'text-neon');
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
})();
</script>
@endsection
