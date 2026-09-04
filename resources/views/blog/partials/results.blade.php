<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    @forelse($articles as $a)
        @php
            $img = method_exists($a, 'getFeaturedImageUrl')
                ? $a->getFeaturedImageUrl()
                : asset('ruanglari.webp');
            $dt = $a->published_at ?: $a->created_at;
        @endphp
        <article class="group flex flex-col bg-[#0B1220] border border-slate-800 hover:border-slate-700 rounded-lg overflow-hidden transition-all duration-200">
            {{-- Image Header --}}
            <a href="{{ route('blog.show', $a->slug) }}" class="relative h-48 overflow-hidden block bg-slate-900">
                <img
                    src="{{ $img }}"
                    alt="{{ $a->localized_title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-[#0B1220]/80 via-transparent to-transparent opacity-60"></div>
            </a>

            {{-- Content Body --}}
            <div class="p-5 flex flex-col flex-1 justify-between">
                <div>
                    {{-- Category & Date Row --}}
                    <div class="flex items-center justify-between gap-2 mb-2.5 text-[11px] text-slate-400">
                        @if($a->category)
                            <span class="inline-block px-2.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                {{ $a->category->name }}
                            </span>
                        @else
                            <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider">Artikel</span>
                        @endif
                        <span class="font-mono text-slate-400">
                            {{ optional($dt)->format('d M Y') }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h3 class="text-base font-bold text-white leading-snug group-hover:text-slate-200 transition-colors line-clamp-2">
                        <a href="{{ route('blog.show', $a->slug) }}">
                            {{ $a->localized_title }}
                        </a>
                    </h3>

                    {{-- Excerpt --}}
                    <p class="mt-2 text-xs text-slate-300/90 leading-relaxed line-clamp-2">
                        {{ $a->localized_excerpt ?: Str::limit(strip_tags((string) $a->localized_content), 120) }}
                    </p>
                </div>

                {{-- Footer Stats & Action --}}
                <div class="mt-4 pt-3.5 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                    <span class="inline-flex items-center gap-1.5 font-mono text-[11px] text-slate-400">
                        <i class="far fa-eye text-slate-500"></i>
                        {{ number_format((int) ($a->views_count ?? 0)) }}
                    </span>
                    <a href="{{ route('blog.show', $a->slug) }}" class="text-xs font-semibold text-slate-300 group-hover:text-white inline-flex items-center gap-1 transition-colors">
                        <span>Baca</span>
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="sm:col-span-2">
            <div class="rounded-lg border border-slate-800 bg-[#0B1220] p-12 text-center">
                <div class="text-xl font-bold text-white">Tidak ada artikel</div>
                <div class="mt-1.5 text-xs text-slate-400">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</div>
            </div>
        </div>
    @endforelse
</div>

@if($articles->hasPages())
    <div class="mt-12 flex justify-center">
        {{ $articles->links('partials.pagination-circle') }}
    </div>
@endif
