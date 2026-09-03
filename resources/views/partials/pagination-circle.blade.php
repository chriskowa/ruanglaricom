@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1.5 sm:gap-2 select-none">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="@lang('pagination.previous')" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center bg-slate-950/70 border border-slate-850 text-slate-600 cursor-not-allowed">
                <i class="fas fa-chevron-left text-[11px]"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center bg-slate-900 border border-slate-750 text-slate-300 hover:text-white hover:border-slate-500 hover:bg-slate-800 transition">
                <i class="fas fa-chevron-left text-[11px]"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span aria-disabled="true" class="w-7 h-9 sm:w-8 sm:h-10 flex items-center justify-center text-slate-500 font-mono text-xs">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-xs bg-neon text-dark shadow-lg shadow-neon/20 ring-2 ring-neon/40 font-mono">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-xs bg-slate-900 border border-slate-750 text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-500 transition font-mono">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center bg-slate-900 border border-slate-750 text-slate-300 hover:text-white hover:border-slate-500 hover:bg-slate-800 transition">
                <i class="fas fa-chevron-right text-[11px]"></i>
            </a>
        @else
            <span aria-disabled="true" aria-label="@lang('pagination.next')" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center bg-slate-950/70 border border-slate-850 text-slate-600 cursor-not-allowed">
                <i class="fas fa-chevron-right text-[11px]"></i>
            </span>
        @endif
    </nav>
@endif
