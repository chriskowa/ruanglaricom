@if($products->hasPages())
    <div class="flex items-center justify-between px-6 py-4 border-t border-slate-800 font-sans">
        <div class="text-xs text-slate-400 font-medium">
            Showing <span class="font-bold text-white">{{ $products->firstItem() }}</span> to <span class="font-bold text-white">{{ $products->lastItem() }}</span> of <span class="font-bold text-white">{{ $products->total() }}</span> products
        </div>
        <div class="flex items-center gap-1">
            @if ($products->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-slate-900 text-slate-600 text-xs font-bold border border-slate-800 cursor-not-allowed">Previous</span>
            @else
                <button @click="loadPage('{{ $products->previousPageUrl() }}')" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-200 hover:bg-neon hover:text-dark text-xs font-bold border border-slate-700 transition">Previous</button>
            @endif

            @if ($products->hasMorePages())
                <button @click="loadPage('{{ $products->nextPageUrl() }}')" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-200 hover:bg-neon hover:text-dark text-xs font-bold border border-slate-700 transition">Next</button>
            @else
                <span class="px-3 py-1.5 rounded-lg bg-slate-900 text-slate-600 text-xs font-bold border border-slate-800 cursor-not-allowed">Next</span>
            @endif
        </div>
    </div>
@endif
