@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="w-full select-none">
        {{-- Mobile View (< sm) --}}
        <div class="flex items-center justify-between w-full max-w-sm mx-auto gap-2 sm:hidden px-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-[#080D17] border border-slate-800 text-xs font-medium text-slate-600 cursor-not-allowed select-none min-h-[42px]">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-[#0B1220] border border-slate-800 text-xs font-medium text-slate-200 hover:bg-slate-800 hover:text-white hover:border-slate-700 transition select-none min-h-[42px]">
                    Sebelumnya
                </a>
            @endif

            {{-- Current Page Indicator --}}
            <div class="inline-flex items-center gap-1.5 text-xs text-slate-300 font-mono">
                <span>Hal</span>
                <span class="px-2 py-0.5 rounded-md bg-[#080D17] border border-slate-800 text-neon font-bold">{{ $paginator->currentPage() }}</span>
                <span>/</span>
                <span class="font-bold text-white">{{ $paginator->lastPage() }}</span>
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-[#0B1220] border border-slate-800 text-xs font-medium text-slate-200 hover:bg-slate-800 hover:text-white hover:border-slate-700 transition select-none min-h-[42px]">
                    Berikutnya
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-[#080D17] border border-slate-800 text-xs font-medium text-slate-600 cursor-not-allowed select-none min-h-[42px]">
                    Berikutnya
                </span>
            @endif
        </div>

        {{-- Desktop & Tablet View (>= sm) --}}
        <div class="hidden sm:flex items-center justify-center gap-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="@lang('pagination.previous')" class="w-10 h-10 rounded-md flex items-center justify-center bg-[#080D17] border border-slate-800 text-slate-600 cursor-not-allowed">
                    <i class="fas fa-chevron-left text-xs"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" class="w-10 h-10 rounded-md flex items-center justify-center bg-[#0B1220] border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 hover:bg-slate-800 transition">
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="w-8 h-10 flex items-center justify-center text-slate-400 font-mono text-xs">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="w-10 h-10 rounded-md flex items-center justify-center font-bold text-xs bg-neon text-dark shadow-sm font-mono ring-1 ring-neon/50">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-10 h-10 rounded-md flex items-center justify-center font-bold text-xs bg-[#0B1220] border border-slate-800 text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-700 transition font-mono">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" class="w-10 h-10 rounded-md flex items-center justify-center bg-[#0B1220] border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 hover:bg-slate-800 transition">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            @else
                <span aria-disabled="true" aria-label="@lang('pagination.next')" class="w-10 h-10 rounded-md flex items-center justify-center bg-[#080D17] border border-slate-800 text-slate-600 cursor-not-allowed">
                    <i class="fas fa-chevron-right text-xs"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
