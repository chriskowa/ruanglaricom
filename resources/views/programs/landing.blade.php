@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', $title)
@section('meta_title', $title)
@section('meta_description', $metaDesc)

@push('styles')
<script>
    tailwind.config.theme.extend.colors.neon = '#ccff00';
</script>
<style>
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .accordion-header {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .accordion-header:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }
    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease-out;
    }
    .accordion-item.active .accordion-content {
        max-height: 200px;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .accordion-item.active .accordion-icon {
        transform: rotate(180deg);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-16 font-sans bg-dark text-slate-200">
    
    <!-- Hero Banner -->
    <div class="relative overflow-hidden mb-12 border-b border-slate-800 bg-slate-950/80 py-16 md:py-24">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-neon/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-green-500/10 rounded-full blur-[100px] pointer-events-none"></div>
        
        <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-neon/10 text-neon mb-4 uppercase tracking-widest font-mono">
                <span class="w-1.5 h-1.5 rounded-full bg-neon animate-ping"></span> Panduan & Program Latihan
            </span>
            <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tight mb-6 uppercase pr-2">
                {{ $h1 }}
            </h1>
            <p class="text-slate-400 text-base md:text-lg max-w-2xl mx-auto font-light leading-relaxed">
                {{ $metaDesc }}
            </p>
            <div class="mt-8">
                <a href="#related-programs" class="inline-block px-8 py-4 bg-neon hover:bg-neon/90 text-dark font-black rounded-xl text-base shadow-lg shadow-neon/20 hover:shadow-neon/30 transition-all uppercase tracking-wider">
                    Lihat Program Terkait
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content: Educational Article -->
            <div class="lg:col-span-2 space-y-8">
                <div class="glass-panel rounded-2xl p-6 md:p-8" data-aos="fade-up">
                    <h2 class="text-2xl font-black text-white uppercase mb-6 tracking-tight pb-4 border-b border-slate-800">
                        Panduan Edukasi Lari Terstruktur
                    </h2>
                    <div class="prose prose-invert max-w-none text-slate-300 leading-relaxed text-sm md:text-base">
                        {!! $content !!}
                    </div>
                </div>

                <!-- Related Programs Section -->
                <div id="related-programs" class="space-y-6" data-aos="fade-up">
                    <h2 class="text-2xl font-black text-white uppercase tracking-tight">
                        Program Latihan Terkait
                    </h2>
                    
                    @if($programs->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($programs as $program)
                                <div class="group bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-neon/50 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 flex flex-col hover:shadow-xl hover:shadow-neon/5">
                                    <!-- Image -->
                                    <div class="relative h-48 overflow-hidden">
                                        <img src="{{ $program->image_url ?? 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&w=400&q=80' }}" alt="{{ $program->title }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80"></div>
                                        
                                        <!-- Badges -->
                                        <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                                            <span class="px-3 py-1 rounded-full bg-slate-900/90 backdrop-blur text-xs font-bold text-white border border-slate-700">
                                                {{ strtoupper($program->distance_target) }}
                                            </span>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold text-dark border border-transparent bg-neon">
                                                {{ ucfirst($program->difficulty) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="p-5 flex-1 flex flex-col">
                                        <!-- Coach Info -->
                                        <div class="flex items-center gap-2 mb-3">
                                            <img src="{{ ($program->coach && $program->coach->avatar) ? asset('storage/' . $program->coach->avatar) : asset('images/profile/17.jpg') }}" class="w-6 h-6 rounded-full object-cover border border-slate-600">
                                            <span class="text-xs text-slate-400">Coach {{ $program->coach->name ?? 'Unknown' }}</span>
                                        </div>

                                        <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-neon transition-colors">
                                            <a href="{{ url('/programs/' . $program->slug) }}">{{ $program->title }}</a>
                                        </h3>

                                        <!-- Rating -->
                                        <div class="flex items-center gap-1 mb-4">
                                            <div class="flex text-yellow-500 text-xs">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= round($program->average_rating ?? 0) ? 'fas' : 'far' }} fa-star"></i>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-slate-500">({{ $program->total_reviews ?? 0 }})</span>
                                        </div>

                                        <!-- Stats Row -->
                                        <div class="grid grid-cols-2 gap-2 mb-4 py-3 border-y border-slate-800/80">
                                            <div class="text-center border-r border-slate-800">
                                                <p class="text-[10px] text-slate-500 uppercase">Durasi</p>
                                                <p class="text-sm font-bold text-white">{{ $program->duration_weeks }} Minggu</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-[10px] text-slate-500 uppercase">Sesi</p>
                                                <p class="text-sm font-bold text-white">{{ $program->sessions_per_week }}/minggu</p>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="mt-auto flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-xs text-slate-500">Harga</p>
                                                <p class="text-lg font-black text-white">
                                                    {{ $program->price > 0 ? 'Rp ' . number_format($program->price, 0, ',', '.') : 'GRATIS' }}
                                                </p>
                                            </div>
                                            <a href="{{ url('/programs/' . $program->slug) }}" class="px-4 py-2 bg-white hover:bg-neon hover:text-dark text-dark font-bold rounded-lg transition-colors text-sm">
                                                Ikut Program Ini
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-900/40 rounded-2xl border border-dashed border-slate-800">
                            <p class="text-slate-500">Belum ada program berbayar/gratis yang terdaftar untuk kategori ini.</p>
                            <a href="{{ url('/programs') }}" class="mt-4 inline-block px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg text-sm transition-colors">
                                Telusuri Semua Program
                            </a>
                        </div>
                    @endif
                </div>

                <!-- FAQ Section -->
                <div class="glass-panel rounded-2xl p-6 md:p-8" data-aos="fade-up">
                    <h2 class="text-2xl font-black text-white uppercase mb-6 tracking-tight pb-4 border-b border-slate-800">
                        Frequently Asked Questions (FAQ)
                    </h2>
                    <div class="space-y-4">
                        @foreach($faqs as $index => $faq)
                            <div class="accordion-item border border-slate-800 rounded-xl overflow-hidden bg-slate-950/40">
                                <div class="accordion-header p-4 flex justify-between items-center bg-slate-900/30" onclick="toggleAccordion(this)">
                                    <h3 class="font-bold text-white text-sm md:text-base pr-4">
                                        {{ $faq['question'] }}
                                    </h3>
                                    <svg class="accordion-icon w-5 h-5 text-slate-400 transform transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                <div class="accordion-content px-4 text-slate-400 text-sm leading-relaxed border-t border-slate-900/80 bg-slate-950/20">
                                    <p>{{ $faq['answer'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Sidebar: Navigation & Internal Linking -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    
                    <!-- Landing Page Links -->
                    <div class="glass-panel rounded-2xl p-6">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">
                            Program Kategori Lari
                        </h3>
                        <ul class="space-y-3">
                            <li>
                                <a href="{{ url('/program-lari-5k') }}" class="flex items-center justify-between text-sm {{ Request::is('program-lari-5k') ? 'text-neon font-bold' : 'text-slate-300 hover:text-white' }} transition-colors">
                                    <span>Program Lari 5K</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-lari-5k-pemula') }}" class="flex items-center justify-between text-sm {{ Request::is('program-lari-5k-pemula') ? 'text-neon font-bold' : 'text-slate-300 hover:text-white' }} transition-colors">
                                    <span>Program Lari 5K Pemula</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-lari-10k') }}" class="flex items-center justify-between text-sm {{ Request::is('program-lari-10k') ? 'text-neon font-bold' : 'text-slate-300 hover:text-white' }} transition-colors">
                                    <span>Program Lari 10K</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-half-marathon') }}" class="flex items-center justify-between text-sm {{ Request::is('program-half-marathon') ? 'text-neon font-bold' : 'text-slate-300 hover:text-white' }} transition-colors">
                                    <span>Program Half Marathon 21K</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/program-lari-sub-20') }}" class="flex items-center justify-between text-sm {{ Request::is('program-lari-sub-20') ? 'text-neon font-bold' : 'text-slate-300 hover:text-white' }} transition-colors">
                                    <span>Program 5K Sub-20 Menit</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/coach-lari-online') }}" class="flex items-center justify-between text-sm {{ Request::is('coach-lari-online') ? 'text-neon font-bold' : 'text-slate-300 hover:text-white' }} transition-colors">
                                    <span>Coach Lari Online</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- AI Tool CTA -->
                    <div class="glass-panel rounded-2xl p-6 border-l-4 border-neon">
                        <h3 class="text-base font-bold text-white mb-2">Custom AI Program Generator</h3>
                        <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                            Belum menemukan program latihan lari yang pas? Buat training plan personal gratis berbasis VDOT yang disesuaikan secara ilmiah.
                        </p>
                        <a href="{{ url('/tools/realistic-running-program') }}" class="block w-full py-3 bg-neon hover:bg-white text-dark font-black text-center text-xs rounded-xl transition-all uppercase tracking-wider">
                            Buat Program VDOT (Gratis)
                        </a>
                    </div>

                    <!-- Main Programs Catalog Link -->
                    <div class="glass-panel rounded-2xl p-6 text-center">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Katalog Marketplace</h3>
                        <p class="text-xs text-slate-500 mb-4">Temukan ratusan training plan dari coach bersertifikasi di Indonesia.</p>
                        <a href="{{ url('/programs') }}" class="block w-full py-2.5 border border-slate-700 hover:border-white text-slate-300 hover:text-white font-bold text-center rounded-xl text-xs transition-colors">
                            Lihat Semua Program Lari
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function toggleAccordion(element) {
        var item = element.parentElement;
        item.classList.toggle('active');
    }
</script>

<!-- Structured Data (JSON-LD) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
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
