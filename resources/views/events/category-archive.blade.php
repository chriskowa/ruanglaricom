@php
    $pageSuffix = request()->has('page') && request('page') > 1 ? ' - Halaman ' . request('page') : '';
    $title = $meta['title'] . $pageSuffix;
    $metaDescription = $meta['meta_description'];
    
    $canonicalUrl = request()->url();
    if (request()->has('page') && request('page') > 1) {
        $canonicalUrl .= '?page=' . request('page');
    }
@endphp

@extends('layouts.pacerhub')

@section('title', $title)
@section('meta_title', $title)
@section('meta_description', $metaDescription)
@section('canonical_url', $canonicalUrl)
@section('og_image', 'https://ruanglari.com/images/og/jadwal-lari-2026.jpg')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 bg-dark relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-12 text-center relative z-10" data-aos="fade-down">
        <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-4 uppercase">
            {{ $meta['h1'] }}
        </h1>
        <div class="text-slate-400 text-base md:text-lg max-w-3xl mx-auto space-y-4 leading-relaxed mb-6">
            <p>
                {{ $meta['description'] }}
            </p>
        </div>
        
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3" data-aos="fade-up" data-aos-delay="50">
            <a href="{{ url('/jadwal-lari') }}" class="px-8 py-3 rounded-full bg-slate-800 border border-slate-700 text-white font-bold hover:bg-neon hover:text-dark hover:border-neon transition-all inline-flex items-center justify-center gap-2 shadow-lg hover:shadow-neon/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Kembali ke Semua Jadwal Lari
            </a>
        </div>
    </div>

    <!-- Event List -->
    <div class="max-w-7xl mx-auto relative z-10">
        <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter mb-6 uppercase">
            Daftar Event Lari {{ strtoupper($categorySlug) }} Terbaru
        </h2>

        <div id="events-container" class="space-y-4">
            @include('events.partials.list', ['events' => $events])
        </div>
        
        <div id="pagination-container" class="mt-8">
            {{ $events->links() }}
        </div>
    </div>

    <!-- SEO Content Sections -->
    <div class="max-w-7xl mx-auto mt-16 pt-16 border-t border-slate-800 relative z-10 space-y-12">
        <!-- Jadwal Lari Berdasarkan Kota -->
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter uppercase">
                Jadwal Lari Berdasarkan Kota
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @php
                    $seoCities = [
                        'jakarta' => 'Jakarta',
                        'bandung' => 'Bandung',
                        'surabaya' => 'Surabaya',
                        'yogyakarta' => 'Yogyakarta',
                        'malang' => 'Malang',
                        'bali' => 'Bali',
                        'semarang' => 'Semarang',
                        'bogor' => 'Bogor',
                        'makassar' => 'Makassar',
                        'medan' => 'Medan',
                        'balikpapan' => 'Balikpapan',
                        'batam' => 'Batam'
                    ];
                @endphp
                @foreach($seoCities as $slug => $cityName)
                    <a href="/event-lari-di-{{ $slug }}" class="p-4 bg-slate-900 border border-slate-800 hover:border-neon hover:text-neon rounded-xl text-center transition group">
                        <h3 class="font-bold text-slate-300 group-hover:text-neon text-sm transition">
                            Jadwal lari {{ strtoupper($categorySlug) }} di {{ $cityName }}
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Jadwal Lari Berdasarkan Kategori Lain -->
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter uppercase">
                Jadwal Lari Berdasarkan Kategori Lainnya
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @php
                    $seoCategories = [
                        '5k' => 'Jadwal lari 5K',
                        '10k' => 'Jadwal lari 10K',
                        'half-marathon' => 'Jadwal half marathon',
                        'marathon' => 'Jadwal marathon',
                        'trail-run' => 'Jadwal trail run',
                        'fun-run' => 'Jadwal fun run',
                        'virtual-run' => 'Jadwal virtual run',
                        'ultra-marathon' => 'Jadwal ultra marathon'
                    ];
                @endphp
                @foreach($seoCategories as $slug => $label)
                    @if($slug !== $categorySlug)
                        <a href="/jadwal-{{ $slug }}" class="p-4 bg-slate-900 border border-slate-800 hover:border-neon hover:text-neon rounded-xl text-center transition group">
                            <h3 class="font-bold text-slate-300 group-hover:text-neon text-sm transition">{{ $label }}</h3>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "{{ e($meta['title']) }}",
  "description": "{{ e($meta['meta_description']) }}",
  "url": "{{ request()->url() }}",
  "isPartOf": {
    "@type": "WebSite",
    "name": "Ruang Lari",
    "url": "https://ruanglari.com"
  }
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Daftar Jadwal Lari {{ strtoupper($categorySlug) }} 2026 Indonesia",
  "itemListElement": [
    @foreach($events as $index => $event)
    {
      "@type": "ListItem",
      "position": {{ $index + 1 }},
      "url": "{{ $event->public_url }}",
      "name": "{{ e($event->name) }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Beranda",
      "item": "https://ruanglari.com"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Jadwal Lari",
      "item": "https://ruanglari.com/jadwal-lari"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "Kategori {{ strtoupper($categorySlug) }}",
      "item": "{{ request()->url() }}"
    }
  ]
}
</script>
@endpush

@endsection
