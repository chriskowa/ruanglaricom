@php
    $customSection = $event->premium_amenities['custom_section'] ?? null;
    $hasCustomSection = !empty($customSection['enabled']) && (!empty($customSection['title']) || !empty($customSection['description']));

    if (!$hasCustomSection) {
        return;
    }

    $title = $customSection['title'] ?? 'Informasi Tambahan';
    $tag = $customSection['tag'] ?? 'Fasilitas & Akses';
    $desc = $customSection['description'] ?? '';
    $buttonText = $customSection['button_text'] ?? null;
    $buttonUrl = $customSection['button_url'] ?? null;

    $rawImages = $customSection['images'] ?? [];
    if (!is_array($rawImages)) {
        $rawImages = [];
    }
    $images = [];
    foreach ($rawImages as $img) {
        if (!empty($img)) {
            $images[] = (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))
                ? $img
                : asset('storage/' . ltrim($img, '/'));
        }
    }

    $template = $variant ?? ($event->template ?? 'beach-theme');
    $isDark = isset($isDark) ? (bool)$isDark : in_array($template, ['modern-dark', 'paolo-fest-dark']);

    $sectionBg = $isDark ? 'bg-slate-950 text-white' : 'bg-white text-slate-900';
    $cardBg = $isDark ? 'bg-slate-900 border-slate-800' : 'bg-slate-50 border-slate-200';
    $borderColor = $isDark ? 'border-slate-800' : 'border-slate-200';
    $textColor = $isDark ? 'text-slate-300' : 'text-slate-600';
    $headingColor = $isDark ? 'text-white' : 'text-slate-900';
@endphp

<section id="custom-info" class="py-20 {{ $sectionBg }} border-b {{ $borderColor }} scroll-mt-16 relative">
    <style>
        .custom-section-prose ul { list-style-type: disc !important; padding-left: 1.25rem !important; margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
        .custom-section-prose ol { list-style-type: decimal !important; padding-left: 1.25rem !important; margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
        .custom-section-prose li { margin-top: 0.25rem !important; margin-bottom: 0.25rem !important; }
        .custom-section-prose p { margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
        .custom-section-prose strong { font-weight: 700 !important; color: inherit !important; }
        .custom-section-prose a { color: var(--theme-primary, #0284c7) !important; text-decoration: underline !important; }
        .custom-section-prose table { width: 100% !important; border-collapse: collapse !important; margin: 1rem 0 !important; }
        .custom-section-prose th, .custom-section-prose td { border: 1px solid rgba(148, 163, 184, 0.25) !important; padding: 0.5rem 0.75rem !important; text-align: left !important; }
    </style>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Heading, Info/Desc, Custom Link Button -->
            <div class="{{ count($images) > 0 ? 'lg:col-span-6' : 'lg:col-span-8 lg:col-start-3 text-center' }} space-y-5">
                @if(!empty($tag))
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-theme-light text-theme-primary text-xs font-bold uppercase tracking-wider {{ $isDark ? '!bg-slate-800 !text-slate-200' : '' }}">
                            {{ $tag }}
                        </span>
                    </div>
                @endif

                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-heading font-extrabold {{ $headingColor }} tracking-tight leading-tight">
                    {{ $title }}
                </h2>

                @php
                    $isHtml = strip_tags($desc) !== $desc;
                    $renderedDesc = $isHtml ? $desc : nl2br(e($desc));
                @endphp
                <div class="{{ $textColor }} text-sm sm:text-base leading-relaxed prose prose-slate max-w-none {{ $isDark ? 'prose-invert !text-slate-300' : '' }} custom-section-prose">
                    {!! $renderedDesc !!}
                </div>

                @if(!empty($buttonUrl))
                    <div class="pt-3 {{ count($images) === 0 ? 'flex justify-center' : '' }}">
                        <a href="{{ $buttonUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-sm font-bold shadow-sm transition hover:shadow duration-150 group">
                            <span>{{ $buttonText ?: 'Informasi Selengkapnya' }}</span>
                            <i class="fas fa-external-link-alt text-xs opacity-80 group-hover:opacity-100 transition"></i>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right Column: Multiple Images Gallery Showcase -->
            @if(count($images) > 0)
            <div class="lg:col-span-6 w-full">
                <div class="{{ $cardBg }} border rounded-lg p-3 sm:p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b {{ $borderColor }} pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-theme-light text-theme-primary flex items-center justify-center text-xs {{ $isDark ? '!bg-slate-800 !text-slate-200' : '' }}">
                                <i class="fas fa-images"></i>
                            </span>
                            <h3 class="text-xs sm:text-sm font-bold {{ $headingColor }} uppercase tracking-wider">Galeri & Informasi Visual</h3>
                        </div>
                        <span class="text-[11px] font-mono {{ $isDark ? 'text-slate-400' : 'text-slate-500' }} font-semibold" id="customSectionCounter">1 / {{ count($images) }}</span>
                    </div>

                    <!-- Main Featured Image with Click-to-Zoom Lightbox -->
                    <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] overflow-hidden rounded-md bg-slate-900/10 border {{ $borderColor }} group">
                        <img id="customSectionMainImg"
                             src="{{ $images[0] }}"
                             alt="{{ $title }}"
                             class="w-full h-full object-cover transition-all duration-300 cursor-zoom-in group-hover:scale-105"
                             onclick="if(window.openLightbox){window.openLightbox(this.src)}else{window.open(this.src,'_blank')}"
                             loading="lazy" decoding="async">

                        <!-- Zoom overlay badge -->
                        <button type="button"
                                onclick="if(window.openLightbox){window.openLightbox(document.getElementById('customSectionMainImg').src)}else{window.open(document.getElementById('customSectionMainImg').src,'_blank')}"
                                class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded bg-slate-900/75 hover:bg-slate-900 text-white text-[11px] font-medium backdrop-blur-sm shadow transition flex items-center gap-1.5"
                                title="Perbesar Foto">
                            <i class="fas fa-search-plus text-xs"></i>
                            <span class="hidden sm:inline">Perbesar</span>
                        </button>

                        @if(count($images) > 1)
                        <!-- Prev / Next Navigation Controls -->
                        <button type="button"
                                id="customSectionPrev"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center text-xs transition shadow-md focus:outline-none"
                                aria-label="Foto Sebelumnya">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <button type="button"
                                id="customSectionNext"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center text-xs transition shadow-md focus:outline-none"
                                aria-label="Foto Berikutnya">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                        @endif
                    </div>

                    @if(count($images) > 1)
                    <!-- Thumbnails row -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 custom-scrollbar" id="customSectionThumbs">
                        @foreach($images as $idx => $imgUrl)
                            <button type="button"
                                    class="custom-section-thumb-btn shrink-0 w-16 sm:w-20 aspect-video rounded overflow-hidden border-2 transition-all duration-150 {{ $loop->first ? 'border-theme-primary ring-2 ring-theme-primary/30' : 'border-slate-200 dark:border-slate-700 opacity-60 hover:opacity-100' }}"
                                    onclick="window.switchCustomSectionImage({{ $idx }})"
                                    aria-label="Lihat foto {{ $idx + 1 }}">
                                <img src="{{ $imgUrl }}" alt="Thumbnail {{ $idx + 1 }}" class="w-full h-full object-cover pointer-events-none" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

@if(count($images) > 1)
<script>
(function() {
    const customImages = @json($images);
    if (!Array.isArray(customImages) || customImages.length <= 1) return;

    let currentIndex = 0;
    const mainImg = document.getElementById('customSectionMainImg');
    const counter = document.getElementById('customSectionCounter');
    const prevBtn = document.getElementById('customSectionPrev');
    const nextBtn = document.getElementById('customSectionNext');
    const thumbs = document.querySelectorAll('.custom-section-thumb-btn');

    window.switchCustomSectionImage = function(index) {
        if (index < 0) index = customImages.length - 1;
        if (index >= customImages.length) index = 0;
        currentIndex = index;

        if (mainImg) {
            mainImg.style.opacity = '0.35';
            mainImg.src = customImages[currentIndex];
            setTimeout(function() { mainImg.style.opacity = '1'; }, 100);
        }
        if (counter) {
            counter.textContent = (currentIndex + 1) + ' / ' + customImages.length;
        }

        thumbs.forEach(function(thumb, idx) {
            if (idx === currentIndex) {
                thumb.classList.add('border-theme-primary', 'ring-2', 'ring-theme-primary/30');
                thumb.classList.remove('border-slate-200', 'border-slate-700', 'opacity-60');
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('border-theme-primary', 'ring-2', 'ring-theme-primary/30');
                thumb.classList.add('opacity-60');
            }
        });
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.switchCustomSectionImage(currentIndex - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.switchCustomSectionImage(currentIndex + 1);
        });
    }
})();
</script>
@endif
