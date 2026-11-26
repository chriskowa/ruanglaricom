@php
    $selectedTemplate = $selectedTemplate ?? 'modern-dark';
    $themes = [
        [
            'slug' => 'modern-dark',
            'name' => 'Modern Dark',
            'tag' => 'Gelap & High Contrast',
            'desc' => 'Desain gelap modern dengan aksen kontras tinggi untuk event malam, city run, dan lomba kompetitif.',
            'image' => asset('images/hero/marathon-hero-cinematic.jpg'),
            'swatch_bg' => '#020617',
            'swatch_accent' => '#ccff00',
            'is_dark' => true,
        ],
        [
            'slug' => 'quick-light',
            'name' => 'Quick Light',
            'tag' => 'Ringkas & Cepat',
            'desc' => 'Alur pendaftaran praktis dan ringkas dengan tema terang modern untuk konversi cepat di mobile.',
            'image' => asset('images/hero/jadwal-lari.webp'),
            'swatch_bg' => '#ffffff',
            'swatch_accent' => '#f97316',
            'is_dark' => false,
        ],
        [
            'slug' => 'light-clean',
            'name' => 'Light Clean',
            'tag' => 'Terang & Informatif',
            'desc' => 'Tampilan cerah, bersih, dan terstruktur lengkap untuk fun run, komunitas, hingga marathon.',
            'image' => asset('images/hero/runner-hero.jpg'),
            'swatch_bg' => '#f8fafc',
            'swatch_accent' => '#0284c7',
            'is_dark' => false,
        ],
        [
            'slug' => 'beach-theme',
            'name' => 'Beach Theme',
            'tag' => 'Coastal & Pesisir',
            'desc' => 'Estetika coastal run bernuansa pesisir dengan hero visual sinematik, galeri interaktif, dan palet hangat.',
            'image' => asset('images/hero/indonesian-runner-sunrise.jpg'),
            'swatch_bg' => '#f6f3eb',
            'swatch_accent' => '#0f766e',
            'is_dark' => false,
        ],
        [
            'slug' => 'professional-city-run',
            'name' => 'Professional City',
            'tag' => 'Korporat & Marathon',
            'desc' => 'Tata letak editorial yang tegas dan rapi untuk city marathon skala besar serta event bersponsor.',
            'image' => asset('images/hero/vdot-runner-training.jpg'),
            'swatch_bg' => '#ffffff',
            'swatch_accent' => '#0f172a',
            'is_dark' => false,
        ],
        [
            'slug' => 'simple-minimal',
            'name' => 'Simple Minimal',
            'tag' => 'Minimalis & Ringan',
            'desc' => 'Fokus penuh pada informasi kategori dan form pendaftaran tanpa distraksi visual berlebih.',
            'image' => asset('images/hero/runner-hero.jpg'),
            'swatch_bg' => '#ffffff',
            'swatch_accent' => '#18181b',
            'is_dark' => false,
        ],
        [
            'slug' => 'paolo-fest',
            'name' => 'Paolo Fest',
            'tag' => 'Festif Terang',
            'desc' => 'Nuansa festival lari yang cerah, energik, dan ramah untuk acara komunitas maupun keluarga.',
            'image' => asset('images/hero/indonesian-runner-sunrise.jpg'),
            'swatch_bg' => '#eff6ff',
            'swatch_accent' => '#2563eb',
            'is_dark' => false,
        ],
        [
            'slug' => 'paolo-fest-dark',
            'name' => 'Paolo Fest Dark',
            'tag' => 'Festif Gelap',
            'desc' => 'Variasi gelap bernuansa malam dengan aksen biru elektrik dan hierarki kartu yang tegas.',
            'image' => asset('images/hero/marathon-hero-cinematic.jpg'),
            'swatch_bg' => '#0f172a',
            'swatch_accent' => '#38bdf8',
            'is_dark' => true,
        ],
        [
            'slug' => 'golden-run',
            'name' => 'Golden Run',
            'tag' => 'Eksklusif Emas',
            'desc' => 'Tampilan premium berkarakter gelap dengan aksen emas untuk ajang lari bergengsi.',
            'image' => asset('images/hero/vdot-runner-training.jpg'),
            'swatch_bg' => '#09090b',
            'swatch_accent' => '#eab308',
            'is_dark' => true,
        ],
    ];

    $selectedIndex = 0;
    foreach ($themes as $idx => $t) {
        if ($t['slug'] === $selectedTemplate) {
            $selectedIndex = $idx;
            break;
        }
    }
    $demoUrlTemplate = route('eo.events.theme-demo', ['template' => '__TEMPLATE__']);
@endphp

<div class="md:col-span-2" id="theme-picker-wrapper">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <label class="text-sm font-semibold text-white">Event Template (Halaman Pendaftaran)</label>
                <span id="selectedThemeBadge" class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-xs font-mono text-yellow-400">
                    {{ $themes[$selectedIndex]['name'] ?? 'Modern Dark' }}
                </span>
                <span class="text-xs font-mono text-slate-400">{{ count($themes) }} Tema</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Geser ke samping untuk membandingkan gaya tampilan, lalu klik Lihat Demo untuk membuka simulasi lengkap di tab baru.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a
                id="previewSelectedThemeLink"
                href="{{ route('eo.events.theme-demo', ['template' => $selectedTemplate]) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-yellow-400 hover:text-slate-950 text-white border border-slate-700 text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400"
            >
                Preview Tema Terpilih
            </a>
            <button
                type="button"
                id="themeStripPrevBtn"
                aria-label="Geser daftar tema ke kiri"
                class="w-9 h-9 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-yellow-400 text-slate-200 flex items-center justify-center transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                type="button"
                id="themeStripNextBtn"
                aria-label="Geser daftar tema ke kanan"
                class="w-9 h-9 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-yellow-400 text-slate-200 flex items-center justify-center transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    <div
        id="themeCarouselTrack"
        class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-3 scroll-smooth theme-strip-scroll"
        role="radiogroup"
        aria-label="Pilihan Template Event"
    >
        @foreach($themes as $t)
            @php
                $isActive = $selectedTemplate === $t['slug'];
                $radioId = 'event_template_' . $t['slug'];
            @endphp
            <div
                class="theme-card-item relative w-[272px] sm:w-[292px] shrink-0 snap-start bg-slate-900 border-2 {{ $isActive ? 'border-yellow-400 bg-slate-800' : 'border-slate-800 hover:border-slate-600' }} rounded-lg p-3.5 transition-colors duration-150 flex flex-col"
                data-theme-card="{{ $t['slug'] }}"
                data-theme-name="{{ $t['name'] }}"
            >
                <input
                    id="{{ $radioId }}"
                    type="radio"
                    name="template"
                    value="{{ $t['slug'] }}"
                    class="sr-only theme-radio-input"
                    {{ $isActive ? 'checked' : '' }}
                >

                <label for="{{ $radioId }}" class="block cursor-pointer group select-none flex-1">
                    <!-- Mini Realistic Visual Preview -->
                    <div class="relative h-32 rounded-md mb-3 border border-slate-700 overflow-hidden bg-slate-950">
                        <img
                            src="{{ $t['image'] }}"
                            alt="Preview {{ $t['name'] }}"
                            loading="lazy"
                            decoding="async"
                            class="w-full h-full object-cover opacity-80 group-hover:opacity-95 transition-opacity duration-150"
                        >
                        <!-- Solid bottom UI preview strip representing theme colors -->
                        <div class="absolute inset-x-0 bottom-0 p-2.5 border-t border-slate-700 flex items-center justify-between gap-2" style="background-color: {{ $t['swatch_bg'] }};">
                            <div class="min-w-0">
                                <span class="block text-[11px] font-bold truncate" style="color: {{ $t['is_dark'] ? '#f8fafc' : '#0f172a' }};">
                                    {{ $t['name'] }}
                                </span>
                                <span class="block text-[10px] truncate" style="color: {{ $t['is_dark'] ? '#cbd5e1' : '#475569' }};">
                                    {{ $t['tag'] }}
                                </span>
                            </div>
                            <span
                                class="shrink-0 px-2 py-1 rounded text-[10px] font-bold"
                                style="background-color: {{ $t['swatch_accent'] }}; color: {{ in_array($t['slug'], ['modern-dark', 'golden-run']) ? '#0f172a' : '#ffffff' }};"
                            >
                                Daftar
                            </span>
                        </div>

                        <!-- Selected Indicator Badge -->
                        <div class="theme-active-badge {{ $isActive ? '' : 'hidden' }} absolute top-2 right-2 px-2 py-0.5 rounded bg-yellow-400 text-slate-950 text-[10px] font-bold tracking-wide">
                            Dipilih
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h4 class="font-bold text-sm text-white truncate">{{ $t['name'] }}</h4>
                        <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[10px] font-medium text-slate-300 shrink-0">
                            {{ $t['tag'] }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-300 leading-relaxed line-clamp-2 min-h-[2.25rem]">
                        {{ $t['desc'] }}
                    </p>
                </label>

                <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                    <label for="{{ $radioId }}" class="theme-select-label text-xs font-semibold cursor-pointer {{ $isActive ? 'text-yellow-400' : 'text-slate-300 hover:text-white' }}">
                        {{ $isActive ? 'Tema Aktif' : 'Pilih Tema' }}
                    </label>
                    <a
                        href="{{ route('eo.events.theme-demo', ['template' => $t['slug']]) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-1.5 rounded-md bg-slate-950 hover:bg-yellow-400 hover:text-slate-950 text-white border border-slate-700 text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400 shrink-0"
                    >
                        Lihat Demo
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    @error('template') <p class="text-rose-400 text-xs mt-1" role="alert">{{ $message }}</p> @enderror
</div>

@once
<style>
    .theme-strip-scroll {
        scrollbar-width: thin;
        scrollbar-color: #475569 #0f172a;
    }
    .theme-strip-scroll::-webkit-scrollbar {
        height: 8px;
    }
    .theme-strip-scroll::-webkit-scrollbar-track {
        background: #0f172a;
        border-radius: 4px;
    }
    .theme-strip-scroll::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 4px;
    }
    .theme-strip-scroll::-webkit-scrollbar-thumb:hover {
        background: #facc15;
    }
</style>

@push('scripts')
<script>
    (function() {
        const demoUrlTemplate = @json($demoUrlTemplate);
        const track = document.getElementById('themeCarouselTrack');
        const prevBtn = document.getElementById('themeStripPrevBtn');
        const nextBtn = document.getElementById('themeStripNextBtn');
        const headerBadge = document.getElementById('selectedThemeBadge');
        const headerPreviewLink = document.getElementById('previewSelectedThemeLink');

        function scrollStrip(direction) {
            if (!track) return;
            const step = Math.max(300, Math.floor(track.clientWidth * 0.75));
            track.scrollBy({ left: direction * step, behavior: 'smooth' });
        }

        if (prevBtn) prevBtn.addEventListener('click', function() { scrollStrip(-1); });
        if (nextBtn) nextBtn.addEventListener('click', function() { scrollStrip(1); });

        function syncSelectedCard(selectedSlug) {
            const cards = document.querySelectorAll('[data-theme-card]');
            let selectedName = selectedSlug;

            cards.forEach(function(card) {
                const isSelected = card.dataset.themeCard === selectedSlug;
                card.classList.toggle('border-yellow-400', isSelected);
                card.classList.toggle('bg-slate-800', isSelected);
                card.classList.toggle('border-slate-800', !isSelected);
                card.classList.toggle('bg-slate-900', !isSelected);

                const badge = card.querySelector('.theme-active-badge');
                const label = card.querySelector('.theme-select-label');
                if (badge) badge.classList.toggle('hidden', !isSelected);
                if (label) {
                    label.textContent = isSelected ? 'Tema Aktif' : 'Pilih Tema';
                    label.classList.toggle('text-yellow-400', isSelected);
                    label.classList.toggle('text-slate-300', !isSelected);
                }
                if (isSelected) {
                    selectedName = card.dataset.themeName || selectedSlug;
                }
            });

            if (headerBadge) headerBadge.textContent = selectedName;
            if (headerPreviewLink) {
                headerPreviewLink.href = demoUrlTemplate.replace('__TEMPLATE__', encodeURIComponent(selectedSlug));
            }
        }

        document.querySelectorAll('input.theme-radio-input').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.checked) syncSelectedCard(this.value);
            });
        });

        const initialChecked = document.querySelector('input.theme-radio-input:checked');
        if (initialChecked) {
            const card = initialChecked.closest('[data-theme-card]');
            if (card) {
                card.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
            }
        }
    })();
</script>
@endpush
@endonce
