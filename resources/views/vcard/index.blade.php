<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Ruang Lari - Sports Media Platform' }}</title>
    <meta name="description" content="{{ $description ?? 'Official Sports Media & Running Platform Indonesia.' }}">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Meta -->
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
    
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    
    <!-- Sports Editorial Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-primary: #090B0F;
            --surface: #151922;
            --surface-hover: #1c222e;
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.18);
            --accent-red: #E63946;
            --highlight-orange: #FF8C00;
            --text-main: #FFFFFF;
            --text-secondary: #A7ADB7;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        .font-sports-title {
            font-family: 'Oswald', 'Bebas Neue', sans-serif;
            letter-spacing: 0.03em;
        }

        .font-sports-condensed {
            font-family: 'Bebas Neue', 'Oswald', sans-serif;
            letter-spacing: 0.05em;
        }

        /* Subtle Fade In Animation */
        @keyframes subtleFadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-enter {
            animation: subtleFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .link-card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            min-height: 70px;
            transition: transform 0.15s ease, border-color 0.15s ease, background-color 0.15s ease;
        }

        .link-card:hover {
            background-color: var(--surface-hover);
            border-color: var(--border-hover);
            transform: translateY(-2px);
        }

        .link-card:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-between p-4 sm:p-6 selection:bg-[#E63946] selection:text-white"
      x-data="{ 
          activeModal: null, 
          openModal(item) { this.activeModal = item; }, 
          closeModal() { this.activeModal = null; } 
      }">

    <!-- Container -->
    <main class="w-full max-w-[420px] mx-auto flex flex-col items-center flex-1 animate-enter">

        <!-- 1. PROFILE HEADER -->
        <header class="w-full flex flex-col items-center text-center pt-4 pb-6">
            
            <!-- Rectangular Logo Container -->
            <div class="mb-4">
                <div class="px-5 py-3 rounded-lg bg-[#151922] border border-white/10 flex items-center justify-center shadow-lg">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Ruang Lari Logo" class="h-8 w-auto max-w-[140px] object-contain">
                    @else
                        <span class="font-sports-title text-xl font-bold tracking-wider text-white">
                            RUANG<span class="text-[#E63946]">LARI</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Verified Badge -->
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-[#151922] border border-white/10 mb-3">
                <span class="w-2 h-2 rounded-full bg-[#E63946]"></span>
                <span class="text-[10px] font-semibold tracking-wider uppercase text-[#A7ADB7]">OFFICIAL MEDIA</span>
            </div>

            <!-- Brand Name & Category -->
            <h1 class="font-sports-title text-3xl font-bold uppercase text-white leading-tight">
                RUANG LARI
            </h1>
            <div class="text-xs font-semibold uppercase tracking-widest text-[#E63946] mt-0.5 mb-2">
                SPORT MEDIA PLATFORM
            </div>

            <!-- Description -->
            <p class="text-xs leading-relaxed text-[#A7ADB7] max-w-[340px]">
                {{ $description ?? 'Official sports platform for runners in Indonesia. News, community, race events & sports culture.' }}
            </p>
        </header>

        <!-- 2. HERO BRAND CARD -->
        <section class="w-full mb-6">
            <div class="relative bg-[#151922] border border-white/10 rounded-2xl p-5 shadow-xl overflow-hidden group">
                <!-- Left Sport Accent Line -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#E63946]"></div>
                
                <div class="pl-2">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[10px] font-bold tracking-wider uppercase text-[#FF8C00]">
                            PRESS & MEDIA
                        </span>
                        <span class="text-[11px] font-mono text-[#A7ADB7]">ID • 2026</span>
                    </div>

                    <h2 class="font-sports-title text-xl font-bold uppercase text-white tracking-wide">
                        RUANG LARI MEDIA
                    </h2>
                    
                    <p class="text-xs text-[#A7ADB7] leading-relaxed mt-1 mb-4">
                        Breaking Sports Updates • Digital Sports Community
                    </p>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('home') }}" 
                            class="flex-1 px-4 py-2.5 rounded-lg bg-[#E63946] hover:bg-[#d52b38] text-white text-xs font-semibold tracking-wide uppercase text-center transition">
                            Explore Platform
                        </a>
                        <button type="button" onclick="shareVCard()"
                            class="px-3.5 py-2.5 rounded-lg bg-[#090B0F] hover:bg-[#1a202c] border border-white/10 text-[#A7ADB7] hover:text-white text-xs font-medium transition flex items-center justify-center gap-1.5" title="Bagikan Card">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. MAIN LINK BUTTONS (70px height, 16px radius) -->
        <section class="w-full space-y-3 mb-8">
            @php
                // Standard default sports links if not customized
                $mediaLinks = [
                    [
                        'title' => 'Official Website',
                        'desc' => 'Portal Berita & Jadwal Event Lari',
                        'url' => route('home'),
                        'icon' => 'globe',
                        'action_type' => 'link'
                    ],
                    [
                        'title' => 'Kalender Lari Indonesia',
                        'desc' => 'Database Jadwal Race & Marathon',
                        'url' => route('events.index'),
                        'icon' => 'calendar-days',
                        'action_type' => 'link'
                    ],
                    [
                        'title' => 'Kalkulator Pace & Race',
                        'desc' => 'Prediksi Waktu Finish & Training Pace',
                        'url' => route('calculator'),
                        'icon' => 'calculator',
                        'action_type' => 'link'
                    ],
                    [
                        'title' => 'Database Rute GPX',
                        'desc' => 'Download Rute Lari Siap Pakai',
                        'url' => route('gpx.index'),
                        'icon' => 'map-location-dot',
                        'action_type' => 'link'
                    ],
                    [
                        'title' => 'Media Partnership',
                        'desc' => 'Kolaborasi, Liputan & Sponsorship',
                        'url' => 'https://wa.me/6287866950667?text=' . urlencode('Halo Ruang Lari Media, saya tertarik untuk kolaborasi / media partnership.'),
                        'icon' => 'handshake',
                        'external' => true,
                        'action_type' => 'link'
                    ]
                ];

                // If user defined custom links in database, merge with clean description fallback
                if (isset($links) && is_array($links) && count($links) > 0) {
                    $mediaLinks = array_map(function($l) {
                        $title = $l['title'] ?? 'Tautan';
                        $desc = $l['description'] ?? match(true) {
                            str_contains(strtolower($title), 'kalender') => 'Database Jadwal Race & Marathon',
                            str_contains(strtolower($title), 'kalkulator') => 'Prediksi Waktu Finish & Target Pace',
                            str_contains(strtolower($title), 'training') || str_contains(strtolower($title), 'program') => 'Program Latihan Atlet & Pemula',
                            str_contains(strtolower($title), 'challenge') => 'Tantangan Lari 40 Hari',
                            str_contains(strtolower($title), 'gpx') || str_contains(strtolower($title), 'rute') => 'Download Rute Lari GPS',
                            str_contains(strtolower($title), 'foto') => 'Galeri Foto Marathon Gratis',
                            str_contains(strtolower($title), 'artikel') || str_contains(strtolower($title), 'blog') => 'Berita & Tips Lari Terkini',
                            str_contains(strtolower($title), 'marketplace') || str_contains(strtolower($title), 'shop') => 'Perlengkapan & Apparel Pelari',
                            str_contains(strtolower($title), 'pacer') => 'Hire Professional Sports Pacer',
                            default => 'Official Sports Content'
                        };
                        return [
                            'title' => $title,
                            'desc' => $desc,
                            'url' => $l['url'] ?? '#',
                            'icon' => $l['icon'] ?? 'arrow-up-right-from-square',
                            'external' => !empty($l['external']),
                            'action_type' => $l['action_type'] ?? 'link',
                            'modal_title' => !empty($l['modal_title']) ? $l['modal_title'] : $title,
                            'modal_description' => !empty($l['modal_description']) ? $l['modal_description'] : $desc,
                            'modal_image' => $l['modal_image'] ?? '',
                            'modal_button_text' => !empty($l['modal_button_text']) ? $l['modal_button_text'] : 'Buka Tautan',
                            'modal_button_url' => $l['modal_button_url'] ?? ($l['url'] ?? '')
                        ];
                    }, $links);
                }
            @endphp

            @foreach($mediaLinks as $item)
                @php
                    $faIcon = match($item['icon']) {
                        'globe' => 'fa-solid fa-globe',
                        'calendar-alt', 'calendar-days', 'calendar' => 'fa-solid fa-calendar-days',
                        'calculator' => 'fa-solid fa-calculator',
                        'map-location-dot', 'map', 'map-marker-alt' => 'fa-solid fa-map-location-dot',
                        'handshake' => 'fa-solid fa-handshake',
                        'dumbbell', 'running' => 'fa-solid fa-person-running',
                        'fire' => 'fa-solid fa-fire',
                        'camera' => 'fa-solid fa-camera',
                        'book-open', 'newspaper' => 'fa-solid fa-newspaper',
                        'shopping-bag', 'shopping-cart' => 'fa-solid fa-bag-shopping',
                        'instagram' => 'fa-brands fa-instagram',
                        'whatsapp' => 'fa-brands fa-whatsapp',
                        'youtube' => 'fa-brands fa-youtube',
                        'tiktok' => 'fa-brands fa-tiktok',
                        default => 'fa-solid fa-arrow-up-right-from-square'
                    };
                    $isModal = ($item['action_type'] ?? 'link') === 'modal';
                @endphp
                
                @if($isModal)
                    <button type="button" 
                        @click="openModal({{ json_encode($item) }})"
                        class="w-full link-card rounded-2xl px-4 py-3 flex items-center justify-between gap-3.5 group text-left cursor-pointer">
                        
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Icon Box -->
                            <div class="w-11 h-11 rounded-xl bg-[#090B0F] border border-white/10 flex items-center justify-center text-white group-hover:text-[#E63946] shrink-0 transition">
                                <i class="{{ $faIcon }} text-base"></i>
                            </div>
                            
                            <!-- Text -->
                            <div class="min-w-0">
                                <div class="font-bold text-sm text-white tracking-wide uppercase group-hover:text-[#E63946] transition truncate">
                                    {{ $item['title'] }}
                                </div>
                                <div class="text-xs text-[#A7ADB7] truncate mt-0.5">
                                    {{ $item['desc'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Modal Expand Indicator -->
                        <div class="text-[#A7ADB7] group-hover:text-white group-hover:scale-110 transition shrink-0 pr-1">
                            <i class="fa-solid fa-expand text-xs"></i>
                        </div>
                    </button>
                @else
                    <a href="{{ $item['url'] }}" 
                        {{ !empty($item['external']) ? 'target="_blank" rel="noopener"' : '' }}
                        class="link-card rounded-2xl px-4 py-3 flex items-center justify-between gap-3.5 group text-left">
                        
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Icon Box -->
                            <div class="w-11 h-11 rounded-xl bg-[#090B0F] border border-white/10 flex items-center justify-center text-white group-hover:text-[#E63946] shrink-0 transition">
                                <i class="{{ $faIcon }} text-base"></i>
                            </div>
                            
                            <!-- Text -->
                            <div class="min-w-0">
                                <div class="font-bold text-sm text-white tracking-wide uppercase group-hover:text-[#E63946] transition truncate">
                                    {{ $item['title'] }}
                                </div>
                                <div class="text-xs text-[#A7ADB7] truncate mt-0.5">
                                    {{ $item['desc'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Arrow Indicator -->
                        <div class="text-[#A7ADB7] group-hover:text-white group-hover:translate-x-0.5 transition shrink-0 pr-1">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </div>
                    </a>
                @endif
            @endforeach
        </section>

        <!-- 4. SOCIAL FOOTER -->
        <footer class="w-full pt-4 pb-2 border-t border-white/10 text-center mt-auto">
            
            <!-- Social Channels -->
            <div class="flex items-center justify-center gap-3 mb-4">
                <a href="https://instagram.com/ruanglaricom" target="_blank" rel="noopener" 
                    class="w-10 h-10 rounded-xl bg-[#151922] border border-white/10 flex items-center justify-center text-[#A7ADB7] hover:text-white hover:border-white/20 transition" title="Instagram">
                    <i class="fa-brands fa-instagram text-base"></i>
                </a>
                <a href="https://youtube.com/@ruanglari" target="_blank" rel="noopener" 
                    class="w-10 h-10 rounded-xl bg-[#151922] border border-white/10 flex items-center justify-center text-[#A7ADB7] hover:text-white hover:border-white/20 transition" title="YouTube">
                    <i class="fa-brands fa-youtube text-base"></i>
                </a>
                <a href="https://tiktok.com/@ruanglaricom" target="_blank" rel="noopener" 
                    class="w-10 h-10 rounded-xl bg-[#151922] border border-white/10 flex items-center justify-center text-[#A7ADB7] hover:text-white hover:border-white/20 transition" title="TikTok">
                    <i class="fa-brands fa-tiktok text-base"></i>
                </a>
                <a href="https://twitter.com/ruanglaricom" target="_blank" rel="noopener" 
                    class="w-10 h-10 rounded-xl bg-[#151922] border border-white/10 flex items-center justify-center text-[#A7ADB7] hover:text-white hover:border-white/20 transition" title="X (Twitter)">
                    <i class="fa-brands fa-x-twitter text-base"></i>
                </a>
                <a href="https://wa.me/6287866950667" target="_blank" rel="noopener" 
                    class="w-10 h-10 rounded-xl bg-[#151922] border border-white/10 flex items-center justify-center text-[#A7ADB7] hover:text-white hover:border-white/20 transition" title="WhatsApp Media Contact">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                </a>
            </div>

            <!-- Media Brand Credits -->
            <div class="space-y-1">
                <div class="font-sports-title text-sm font-bold uppercase tracking-wider text-white">
                    RUANG LARI MEDIA
                </div>
                <div class="text-xs text-[#A7ADB7]">
                    Sports Culture & Community
                </div>
                <div class="text-[11px] font-mono text-[#A7ADB7]/60 pt-1">
                    &copy; 2026 Ruang Lari Indonesia. All rights reserved.
                </div>
            </div>
        </footer>

    </main>

    <!-- DYNAMIC VCARD MODAL POPUP -->
    <div x-show="activeModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/85 backdrop-blur-sm" @click="closeModal()"></div>

        <!-- Modal Dialog -->
        <div class="relative w-full max-w-sm bg-[#151922] border border-white/10 rounded-2xl p-5 sm:p-6 shadow-2xl z-10 text-left"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">
            
            <!-- Close Button -->
            <button type="button" @click="closeModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-[#090B0F] border border-white/10 flex items-center justify-center text-[#A7ADB7] hover:text-white hover:border-white/20 transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <!-- Header Icon & Title -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#090B0F] border border-white/10 flex items-center justify-center text-[#E63946] shrink-0">
                    <i class="fa-solid fa-circle-info text-base"></i>
                </div>
                <div class="pr-8">
                    <h3 class="font-sports-title text-lg font-bold uppercase text-white tracking-wide leading-tight" x-text="activeModal?.modal_title || activeModal?.title"></h3>
                    <div class="text-[10px] uppercase tracking-wider text-[#FF8C00] font-bold">INFORMASI RUANGLARI</div>
                </div>
            </div>

            <!-- Optional Image / Flyer -->
            <template x-if="activeModal?.modal_image">
                <div class="mb-3.5 rounded-xl overflow-hidden border border-white/10">
                    <img :src="activeModal.modal_image" alt="Modal Image" class="w-full max-h-48 object-cover">
                </div>
            </template>

            <!-- Content Body -->
            <div class="text-xs text-[#CBD5E1] leading-relaxed whitespace-pre-line space-y-2" x-text="activeModal?.modal_description || activeModal?.desc"></div>

            <!-- Modal Action Buttons -->
            <div class="mt-5 flex items-center gap-2">
                <template x-if="activeModal?.modal_button_url && activeModal?.modal_button_url !== '#'">
                    <a :href="activeModal?.modal_button_url" 
                       :target="activeModal?.external ? '_blank' : '_self'"
                       rel="noopener"
                       class="flex-1 py-2.5 px-4 rounded-lg bg-[#E63946] hover:bg-[#d52b38] text-white text-xs font-bold uppercase tracking-wider text-center transition flex items-center justify-center gap-2">
                        <span x-text="activeModal?.modal_button_text || 'Buka Tautan'"></span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </template>
                <button type="button" @click="closeModal()" class="py-2.5 px-4 rounded-lg bg-[#090B0F] hover:bg-[#1a202c] border border-white/10 text-[#A7ADB7] hover:text-white text-xs font-bold uppercase tracking-wider transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Share Helper Script -->
    <script>
        async function shareVCard() {
            const shareData = {
                title: 'Ruang Lari Media - Official Sports Platform',
                text: 'Ruang Lari Media • Official Sports Platform, News, Community & Events in Indonesia:',
                url: window.location.href
            };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                } catch(e) {}
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href);
                showToast('Tautan media card disalin ke clipboard');
            }
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 z-50 bg-[#151922] border border-white/20 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-2xl transition-all duration-300';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 250);
            }, 2500);
        }
    </script>
</body>
</html>
