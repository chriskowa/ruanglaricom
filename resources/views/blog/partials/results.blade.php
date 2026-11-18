@php
    $showFeaturedLead = $articles->currentPage() === 1 && !request()->filled('q') && $articles->count() > 1;
    $leadArticle = $showFeaturedLead ? $articles->first() : null;
    $gridArticles = $showFeaturedLead ? $articles->slice(1) : $articles;
@endphp

@if($leadArticle)
    @php
        $leadImg = method_exists($leadArticle, 'getFeaturedImageUrl')
            ? $leadArticle->getFeaturedImageUrl()
            : asset('ruanglari.webp');
        $leadDt = $leadArticle->published_at ?: $leadArticle->created_at;
    @endphp
    <article class="group mb-6 bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-lg overflow-hidden transition-colors duration-150">
        <div class="grid grid-cols-1 md:grid-cols-12">
            <a href="{{ route('blog.show', $leadArticle->slug) }}" class="md:col-span-6 block aspect-[16/10] md:aspect-auto md:min-h-[260px] bg-slate-950 overflow-hidden">
                <img
                    src="{{ $leadImg }}"
                    alt="{{ $leadArticle->localized_title }}"
                    class="w-full h-full object-cover group-hover:opacity-90 transition-opacity duration-150"
                    loading="eager"
                    decoding="async"
                    onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';"
                >
            </a>
            <div class="md:col-span-6 p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3 text-xs">
                        @if($leadArticle->category)
                            <span class="inline-block px-2.5 py-0.5 rounded bg-slate-800 text-lime-300 font-semibold">
                                {{ $leadArticle->category->name }}
                            </span>
                        @else
                            <span class="inline-block px-2.5 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold">
                                Artikel Utama
                            </span>
                        @endif
                        <time datetime="{{ optional($leadDt)->toIso8601String() }}" class="font-mono tabular-nums text-slate-400">
                            {{ optional($leadDt)->format('d M Y') }}
                        </time>
                    </div>

                    <h3 class="blog-heading text-xl sm:text-2xl text-white leading-snug group-hover:text-lime-300 transition-colors">
                        <a href="{{ route('blog.show', $leadArticle->slug) }}">
                            {{ $leadArticle->localized_title }}
                        </a>
                    </h3>

                    <p class="mt-3 text-sm text-slate-300 leading-relaxed line-clamp-3">
                        {{ $leadArticle->localized_excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $leadArticle->localized_content), 170) }}
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="font-mono tabular-nums text-slate-400">
                        {{ number_format((int) ($leadArticle->views_count ?? 0)) }} dibaca
                    </span>
                    <a href="{{ route('blog.show', $leadArticle->slug) }}" class="font-semibold text-lime-300 hover:text-lime-200 transition-colors">
                        Baca selengkapnya
                    </a>
                </div>
            </div>
        </div>
    </article>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    @forelse($gridArticles as $a)
        @php
            $img = method_exists($a, 'getFeaturedImageUrl')
                ? $a->getFeaturedImageUrl()
                : asset('ruanglari.webp');
            $dt = $a->published_at ?: $a->created_at;
        @endphp
        <article class="group flex flex-col bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-lg overflow-hidden transition-colors duration-150">
            <a href="{{ route('blog.show', $a->slug) }}" class="block aspect-[16/10] overflow-hidden bg-slate-950">
                <img
                    src="{{ $img }}"
                    alt="{{ $a->localized_title }}"
                    class="w-full h-full object-cover group-hover:opacity-90 transition-opacity duration-150"
                    loading="lazy"
                    decoding="async"
                    onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';"
                >
            </a>

            <div class="p-5 flex flex-col flex-1 justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2.5 text-xs">
                        @if($a->category)
                            <span class="inline-block px-2.5 py-0.5 rounded bg-slate-800 text-slate-200 font-medium">
                                {{ $a->category->name }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400">Artikel</span>
                        @endif
                        <time datetime="{{ optional($dt)->toIso8601String() }}" class="font-mono tabular-nums text-slate-400">
                            {{ optional($dt)->format('d M Y') }}
                        </time>
                    </div>

                    <h3 class="blog-heading text-base sm:text-lg text-white leading-snug group-hover:text-lime-300 transition-colors line-clamp-2">
                        <a href="{{ route('blog.show', $a->slug) }}">
                            {{ $a->localized_title }}
                        </a>
                    </h3>

                    <p class="mt-2 text-sm text-slate-300 leading-relaxed line-clamp-2">
                        {{ $a->localized_excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $a->localized_content), 120) }}
                    </p>
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="font-mono tabular-nums text-slate-400">
                        {{ number_format((int) ($a->views_count ?? 0)) }} dibaca
                    </span>
                    <a href="{{ route('blog.show', $a->slug) }}" class="font-semibold text-slate-200 group-hover:text-lime-300 transition-colors">
                        Baca artikel
                    </a>
                </div>
            </div>
        </article>
    @empty
        @if(!$leadArticle)
            <div class="sm:col-span-2">
                <div class="rounded-lg border border-slate-800 bg-slate-900 p-10 text-center">
                    <div class="blog-heading text-xl text-white">Artikel tidak ditemukan</div>
                    <p class="mt-1.5 text-sm text-slate-300">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
                </div>
            </div>
        @endif
    @endforelse
</div>

@if($articles->hasPages())
    <div class="mt-10 flex justify-center w-full">
        {{ $articles->links('partials.pagination-circle') }}
    </div>
@endif
