<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($articles as $a)
        @php
            $img = null;
            if ($a->featured_image) {
                $img = Str::startsWith($a->featured_image, ['http://', 'https://'])
                    ? $a->featured_image
                    : asset('storage/' . ltrim($a->featured_image, '/'));
            }
            $dt = $a->published_at ?: $a->created_at;
        @endphp
        <a href="{{ route('blog.show', $a->slug) }}" class="group block bg-card/60 border border-slate-700/60 rounded-2xl overflow-hidden hover:border-neon/40 hover:shadow-lg hover:shadow-neon/10 transition-all">
            <div class="relative h-44 overflow-hidden">
                @if($img)
                    <img src="{{ $img }}" alt="{{ $a->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-dark/90 via-dark/20 to-transparent"></div>
                <div class="absolute bottom-3 left-4 right-4">
                    @if($a->category)
                        <div class="text-xs font-mono text-neon/90 mb-2">{{ $a->category->name }}</div>
                    @endif
                    <div class="text-white font-bold leading-snug line-clamp-2">{{ $a->title }}</div>
                    <div class="mt-2 text-[11px] font-mono text-slate-300 flex items-center gap-3">
                        <span class="inline-flex items-center gap-2"><i class="far fa-calendar-alt text-neon"></i>{{ optional($dt)->format('d M Y') }}</span>
                        <span class="inline-flex items-center gap-2"><i class="far fa-eye text-neon"></i>{{ number_format((int) ($a->views_count ?? 0)) }}</span>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="text-sm text-slate-300 leading-relaxed line-clamp-3">
                    {{ $a->excerpt ?: Str::limit(strip_tags((string) $a->content), 140) }}
                </div>
            </div>
        </a>
    @empty
        <div class="md:col-span-2 xl:col-span-3">
            <div class="rounded-2xl border border-slate-700/60 bg-card/40 p-10 text-center">
                <div class="text-2xl font-black">Tidak ada artikel</div>
                <div class="mt-2 text-slate-400">Coba ubah kata kunci atau pilih kategori lain.</div>
            </div>
        </div>
    @endforelse
</div>
<div class="mt-10">
    {!! $articles->links() !!}
</div>
