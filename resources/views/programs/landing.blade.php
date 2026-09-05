@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', $title)
@section('meta_title', $title)
@section('meta_description', $metaDesc)

@push('styles')
<script>
    if (typeof tailwind !== 'undefined' && tailwind && tailwind.config) {
        tailwind.config.theme = tailwind.config.theme || {};
        tailwind.config.theme.extend = tailwind.config.theme.extend || {};
        tailwind.config.theme.extend.colors = tailwind.config.theme.extend.colors || {};
        tailwind.config.theme.extend.colors.neon = '#ccff00';
    }
</script>
<style>
    .accordion-header {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .accordion-header:hover {
        background-color: rgba(30, 41, 59, 0.7);
    }
    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.25s ease-out, padding 0.25s ease-out;
    }
    .accordion-item.active .accordion-content {
        max-height: 400px;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .accordion-item.active .accordion-icon {
        transform: rotate(180deg);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen pt-0 pb-16 font-sans bg-dark text-slate-200">
    
    <!-- Hero Header: Clean & Solid Without Orbs or Floating Pills -->
    <header class="border-b border-slate-800 bg-slate-950/70 py-10 md:py-14 mb-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white tracking-tight mb-3">
                {{ $h1 }}
            </h1>
            <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed mb-6">
                {{ $metaDesc }}
            </p>
            <div>
                <a href="#related-programs" class="inline-flex items-center justify-center px-6 py-2.5 bg-neon hover:bg-neon/90 text-slate-950 font-semibold rounded-md text-xs sm:text-sm transition-colors">
                    Lihat Program Terkait
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            
            <!-- Main Content: Educational Article -->
            <div class="lg:col-span-2 space-y-6">
                
                <article class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-7">
                    <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight pb-3 border-b border-slate-800 mb-5">
                        Panduan Edukasi Lari Terstruktur
                    </h2>
                    <div class="prose prose-invert max-w-none text-slate-200 leading-relaxed text-sm">
                        {!! $content !!}
                    </div>
                </article>

                <!-- Related Programs Section -->
                <section id="related-programs" class="space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                            Program Latihan Terkait
                        </h2>
                        <a href="{{ url('/programs') }}" class="text-xs font-medium text-slate-400 hover:text-white transition-colors">
                            Lihat semua &rarr;
                        </a>
                    </div>
                    
                    @if($programs->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($programs as $program)
                                <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-lg overflow-hidden transition-colors flex flex-col">
                                    <!-- Image -->
                                    <div class="relative h-44 overflow-hidden bg-slate-950">
                                        <img src="{{ $program->image_url ?? asset('images/product/program lari ruang lari.webp') }}" alt="{{ $program->title }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80"></div>
                                        
                                        <!-- Badges -->
                                        <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded bg-slate-900/95 text-[11px] font-semibold text-slate-200 border border-slate-700">
                                                {{ strtoupper($program->distance_target) }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold text-slate-950 bg-neon">
                                                {{ ucfirst($program->difficulty) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="p-4 sm:p-5 flex-1 flex flex-col">
                                        <!-- Coach Info -->
                                        <div class="flex items-center gap-2 mb-2.5">
                                            <img src="{{ ($program->coach && $program->coach->avatar) ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" class="w-5 h-5 rounded-full object-cover border border-slate-700" alt="Coach Avatar">
                                            <span class="text-xs text-slate-400">Coach {{ $program->coach->name ?? 'Unknown' }}</span>
                                        </div>

                                        <h3 class="text-sm sm:text-base font-semibold text-white mb-2 line-clamp-2 hover:text-neon transition-colors leading-snug">
                                            <a href="{{ url('/programs/' . $program->slug) }}">{{ $program->title }}</a>
                                        </h3>

                                        <!-- Rating -->
                                        <div class="flex items-center gap-1.5 mb-3">
                                            <div class="flex text-amber-400 text-xs">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= round($program->average_rating ?? 0) ? 'fas' : 'far' }} fa-star"></i>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-slate-400 font-mono">({{ $program->total_reviews ?? 0 }})</span>
                                        </div>

                                        <!-- Stats Row -->
                                        <div class="grid grid-cols-2 gap-2 mb-4 py-2.5 border-y border-slate-800 text-center">
                                            <div class="border-r border-slate-800">
                                                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Durasi</p>
                                                <p class="text-xs font-semibold text-white">{{ $program->duration_weeks }} Minggu</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Sesi</p>
                                                <p class="text-xs font-semibold text-white">{{ $program->sessions_per_week }}/minggu</p>
                                            </div>
                                        </div>

                                        <!-- Footer / Pricing & CTA -->
                                        <div class="mt-auto pt-1 flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Harga</p>
                                                <p class="text-sm font-bold text-white">
                                                    {{ $program->price > 0 ? 'Rp ' . number_format($program->price, 0, ',', '.') : 'GRATIS' }}
                                                </p>
                                            </div>
                                            <a href="{{ url('/programs/' . $program->slug) }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-neon text-slate-900 hover:text-slate-950 font-semibold rounded-md transition-colors text-xs">
                                                Detail Program
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 bg-slate-900 border border-slate-800 rounded-lg p-6">
                            <p class="text-slate-400 text-sm mb-3">Belum ada program latihan yang terdaftar untuk kategori ini.</p>
                            <a href="{{ url('/programs') }}" class="inline-block px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium rounded-md text-xs transition-colors">
                                Telusuri Semua Program
                            </a>
                        </div>
                    @endif
                </section>

                <!-- FAQ Section -->
                <section class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-7">
                    <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight pb-3 border-b border-slate-800 mb-5">
                        Pertanyaan Umum (FAQ)
                    </h2>
                    <div class="space-y-2.5">
                        @foreach($faqs as $index => $faq)
                            <div class="accordion-item border border-slate-800 rounded-md overflow-hidden bg-slate-950/60">
                                <button type="button" class="accordion-header w-full text-left p-3.5 sm:p-4 flex justify-between items-center bg-slate-900/40" onclick="toggleAccordion(this)">
                                    <span class="font-semibold text-white text-xs sm:text-sm pr-3">
                                        {{ $faq['question'] }}
                                    </span>
                                    <svg class="accordion-icon w-4 h-4 text-slate-400 flex-shrink-0 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="accordion-content px-4 text-slate-300 text-xs sm:text-sm leading-relaxed border-t border-slate-800/60 bg-slate-950/30">
                                    <p>{{ $faq['answer'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

            </div>

            <!-- Sidebar: Navigation & Internal Linking -->
            <aside class="lg:col-span-1">
                <div class="sticky top-24 space-y-4">
                    
                    <!-- Landing Page Links -->
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                            Program Kategori Lari
                        </h3>
                        <ul class="space-y-1.5">
                            <li>
                                <a href="{{ url('/program-lari-5k') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs sm:text-sm {{ Request::is('program-lari-5k') ? 'bg-slate-800 text-neon font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                                    <span>Program Lari 5K</span>
                                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-lari-5k-pemula') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs sm:text-sm {{ Request::is('program-lari-5k-pemula') ? 'bg-slate-800 text-neon font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                                    <span>Program Lari 5K Pemula</span>
                                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-lari-10k') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs sm:text-sm {{ Request::is('program-lari-10k') ? 'bg-slate-800 text-neon font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                                    <span>Program Lari 10K</span>
                                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-half-marathon') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs sm:text-sm {{ Request::is('program-half-marathon') ? 'bg-slate-800 text-neon font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                                    <span>Program Half Marathon 21K</span>
                                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-lari-sub-20') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs sm:text-sm {{ Request::is('program-lari-sub-20') ? 'bg-slate-800 text-neon font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                                    <span>Program 5K Sub-20 Menit</span>
                                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/coach-lari-online') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs sm:text-sm {{ Request::is('coach-lari-online') ? 'bg-slate-800 text-neon font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors">
                                    <span>Coach Lari Online</span>
                                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Custom VDOT Tool CTA -->
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                        <h3 class="text-sm font-bold text-white mb-1.5">Program Personal VDOT</h3>
                        <p class="text-xs text-slate-300 mb-4 leading-relaxed">
                            Belum menemukan jadwal yang pas? Buat training plan personal gratis berbasis rumus Jack Daniels VDOT yang disesuaikan dengan tingkat kebugaran Anda.
                        </p>
                        <a href="{{ route('programs.realistic') }}" class="block w-full py-2.5 bg-neon hover:bg-neon/90 text-slate-950 font-semibold text-center text-xs rounded-md transition-colors">
                            Buat Program Latihan VDOT
                        </a>
                    </div>

                    <!-- Main Programs Catalog Link -->
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 text-center">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Katalog Marketplace</h3>
                        <p class="text-xs text-slate-300 mb-3">Temukan pilihan training plan dari coach bersertifikasi di Indonesia.</p>
                        <a href="{{ url('/programs') }}" class="block w-full py-2 border border-slate-700 hover:border-slate-500 text-slate-200 hover:text-white font-medium text-center rounded-md text-xs transition-colors">
                            Lihat Semua Program Lari
                        </a>
                    </div>

                </div>
            </aside>

        </div>
    </div>
</div>

<script>
    function toggleAccordion(element) {
        var item = element.closest('.accordion-item');
        if (item) {
            item.classList.toggle('active');
        }
    }
</script>

<!-- Structured Data (JSON-LD) -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "{{ Request::url() }}#webpage",
      "url": "{{ Request::url() }}",
      "name": {!! json_encode($title) !!},
      "description": {!! json_encode($metaDesc) !!},
      "breadcrumb": {
        "@id": "{{ Request::url() }}#breadcrumb"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ Request::url() }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ url('/') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Programs",
          "item": "{{ url('/programs') }}"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": {!! json_encode($h1) !!},
          "item": "{{ Request::url() }}"
        }
      ]
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        @foreach($faqs as $index => $faq)
        {
          "@type": "Question",
          "name": {!! json_encode($faq['question']) !!},
          "acceptedAnswer": {
            "@type": "Answer",
            "text": {!! json_encode($faq['answer']) !!}
          }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
      ]
    }
  ]
}
</script>
@endsection
