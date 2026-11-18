@extends('layouts.pacerhub', ['skipHeavyAssets' => true, 'skipLoader' => true])

@php
    $pacerName = $pacer->nickname ? $pacer->user->name . ' (' . $pacer->nickname . ')' : $pacer->user->name;
    $pacerCity = $pacer->user->city ? $pacer->user->city->name : '';
    $highlightPbLabel = 'PB';
    $highlightPbValue = '-';

    if ($pacer->user) {
        if ($pacer->category === '5K' && $pacer->user->pb_5k) {
            $highlightPbLabel = '5K PB';
            $highlightPbValue = $pacer->user->pb_5k;
        } elseif ($pacer->category === '10K' && $pacer->user->pb_10k) {
            $highlightPbLabel = '10K PB';
            $highlightPbValue = $pacer->user->pb_10k;
        } elseif ($pacer->category === 'HM (21K)' && $pacer->user->pb_hm) {
            $highlightPbLabel = 'HM PB';
            $highlightPbValue = $pacer->user->pb_hm;
        } elseif ($pacer->category === 'FM (42K)' && $pacer->user->pb_fm) {
            $highlightPbLabel = 'FM PB';
            $highlightPbValue = $pacer->user->pb_fm;
        } else {
            foreach (['FM PB' => $pacer->user->pb_fm, 'HM PB' => $pacer->user->pb_hm, '10K PB' => $pacer->user->pb_10k, '5K PB' => $pacer->user->pb_5k] as $lbl => $val) {
                if ($val) {
                    $highlightPbLabel = $lbl;
                    $highlightPbValue = $val;
                    break;
                }
            }
        }
    }

    $avatarUrl = $pacer->user && $pacer->user->avatar
        ? (str_starts_with($pacer->user->avatar, 'http')
            ? $pacer->user->avatar
            : (str_starts_with($pacer->user->avatar, '/storage')
                ? asset(ltrim($pacer->user->avatar, '/'))
                : asset('storage/' . $pacer->user->avatar)))
        : ($pacer->user && $pacer->user->gender === 'female'
            ? asset('images/default-female.svg')
            : asset('images/default-male.svg'));

    $bannerUrl = $pacer->user && $pacer->user->banner
        ? asset('storage/' . $pacer->user->banner)
        : null;

    $canonicalUrl = route('pacer.show', $pacer->seo_slug);

    $seoTitle = $pacerName . ' - Pacer ' . $pacer->category . ' (' . $pacer->pace . ')' . ($pacerCity ? ' di ' . $pacerCity : '') . ' | RuangLari';
    $seoDescription = 'Sewa ' . $pacerName . ($pacer->verified ? ' (Verified Pacer)' : '') . ' untuk kategori ' . $pacer->category . ' dengan target pace ' . $pacer->pace
        . ($pacerCity ? ' berbasis di ' . $pacerCity : '') . '. Pengalaman ' . ($pacer->total_races ?? 0) . ' race resmi.'
        . ($pacer->bio ? ' ' . \Illuminate\Support\Str::limit(strip_tags($pacer->bio), 95) : '');
    $seoKeywords = implode(', ', array_filter([
        'sewa pacer',
        'jasa pacer lari',
        'pacer ' . $pacer->category,
        $pacer->user ? $pacer->user->name : null,
        $pacer->nickname ?: null,
        $pacerCity ? 'pacer ' . $pacerCity : null,
        'RuangLari',
    ]));

    $sameAsLinks = [];
    if ($contactUnlocked && $pacer->user) {
        foreach ([$pacer->user->strava_url, $pacer->user->instagram_url, $pacer->user->facebook_url, $pacer->user->tiktok_url] as $socialLink) {
            if ($socialLink) {
                $sameAsLinks[] = $socialLink;
            }
        }
    }

    $knowsAbout = array_values(array_filter(array_merge(
        [$pacer->category, 'Running Pacer', 'Race Strategy'],
        is_array($pacer->tags) ? $pacer->tags : []
    )));
@endphp

@section('title', $seoTitle)
@section('meta_title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', $seoKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_image', $avatarUrl)

@push('styles')
<style>
    .pacer-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": "Beranda",
          "item": "{{ url('/') }}"
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": "Direktori Pacer",
          "item": "{{ route('pacer.index') }}"
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "name": @json($pacerName),
          "item": @json($canonicalUrl)
        }
      ]
    },
    {
      "@@type": "Person",
      "name": @json($pacer->user->name),
      @if($pacer->nickname)
      "alternateName": @json($pacer->nickname),
      @endif
      "description": @json($seoDescription),
      "url": @json($canonicalUrl),
      "image": @json($avatarUrl),
      "jobTitle": @json('Pacer Lari ' . $pacer->category),
      @if($pacerCity)
      "homeLocation": {
        "@@type": "Place",
        "name": @json($pacerCity)
      },
      @endif
      @if(count($sameAsLinks) > 0)
      "sameAs": @json($sameAsLinks),
      @endif
      "knowsAbout": @json($knowsAbout)
    },
    {
      "@@type": "Service",
      "name": @json('Jasa Pacer ' . $pacer->category . ' - ' . $pacerName),
      "serviceType": "Running Pacer",
      "description": @json($seoDescription),
      "url": @json($canonicalUrl),
      "provider": {
        "@@type": "Person",
        "name": @json($pacer->user->name)
      }@if($pacerCity),
      "areaServed": {
        "@@type": "City",
        "name": @json($pacerCity)
      }@endif
    }
  ]
}
</script>
@endpush

@section('content')
<div class="bg-slate-950 text-slate-200 pt-0 pb-24 md:pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-slate-400">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ url('/') }}" class="hover:text-white transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('pacer.index') }}" class="hover:text-white transition">Direktori Pacer</a></li>
                @if($pacerCity)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('pacer.index', ['city' => $pacerCity]) }}" class="hover:text-white transition">{{ $pacerCity }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                <li class="text-slate-200 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $pacerName }}</li>
            </ol>
        </nav>

        <section class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
            @if($bannerUrl)
                <div class="h-36 sm:h-48 w-full bg-slate-800 overflow-hidden border-b border-slate-800">
                    <img src="{{ $bannerUrl }}" alt="Banner {{ $pacerName }}" class="w-full h-full object-cover" loading="eager" decoding="async">
                </div>
            @else
                <div class="h-20 sm:h-24 w-full bg-slate-800 border-b border-slate-800"></div>
            @endif

            <div class="p-5 sm:p-6">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full overflow-hidden border-2 border-slate-800 bg-slate-950 flex-shrink-0 {{ $bannerUrl ? '-mt-14 sm:-mt-16' : '-mt-12' }}">
                            <img src="{{ $avatarUrl }}" alt="{{ $pacerName }}" class="w-full h-full object-cover" loading="eager" decoding="async">
                        </div>

                        <div class="min-w-0">
                            <h1 class="pacer-heading text-2xl sm:text-3xl text-white leading-tight break-words">
                                {{ $pacerName }}
                            </h1>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                <span class="px-2.5 py-1 rounded bg-slate-800 text-lime-300 font-semibold">
                                    {{ $pacer->category }}
                                </span>
                                @if($pacer->verified)
                                    <span class="px-2.5 py-1 rounded bg-emerald-950 text-emerald-300 font-semibold">
                                        Terverifikasi
                                    </span>
                                @endif
                                @if($pacerCity)
                                    <span class="px-2.5 py-1 rounded bg-slate-800 text-slate-200">
                                        {{ $pacerCity }}
                                    </span>
                                @endif
                                <span class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 font-mono tabular-nums">
                                    Pace {{ $pacer->pace }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button id="btnHirePacerTop" type="button" class="px-4 py-2.5 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition cursor-pointer">
                            Sewa Pacer
                        </button>
                        <button id="btnChatWhatsAppTop" type="button" class="px-4 py-2.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-semibold text-sm transition cursor-pointer">
                            Chat WhatsApp
                        </button>
                        <button id="btnShareProfile" type="button" class="px-3.5 py-2.5 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-200 font-medium text-sm transition cursor-pointer">
                            Bagikan
                        </button>
                        <button id="btnSaveContact" type="button" class="px-3.5 py-2.5 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-200 font-medium text-sm transition cursor-pointer">
                            Simpan Kontak
                        </button>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-800 grid grid-cols-3 gap-3 sm:gap-4">
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                        <div class="text-xs text-slate-400">Target Pace</div>
                        <div class="text-base sm:text-lg font-mono tabular-nums font-bold text-lime-300 mt-1">{{ $pacer->pace }}</div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                        <div class="text-xs text-slate-400">Total Race</div>
                        <div class="text-base sm:text-lg font-mono tabular-nums font-bold text-white mt-1">{{ $pacer->total_races ?? 0 }}</div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                        <div class="text-xs text-slate-400">{{ $highlightPbLabel }}</div>
                        <div class="text-base sm:text-lg font-mono tabular-nums font-bold text-white mt-1">{{ $highlightPbValue }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                @if($pacer->bio || ($pacer->tags && is_array($pacer->tags) && count($pacer->tags) > 0))
                <section x-data="{ open: true }" class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="pacer-heading text-lg text-white">Tentang Pacer</h2>
                        <button type="button" x-on:click="open = !open" class="text-xs text-slate-300 hover:text-white px-2.5 py-1 rounded bg-slate-800 cursor-pointer" x-text="open ? 'Tutup' : 'Buka'"></button>
                    </div>
                    <div x-show="open" class="mt-4 space-y-4">
                        @if($pacer->bio)
                            <p class="text-sm text-slate-200 leading-relaxed whitespace-pre-line">{{ $pacer->bio }}</p>
                        @endif
                        @if($pacer->tags && is_array($pacer->tags) && count($pacer->tags) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($pacer->tags as $tag)
                                    <span class="px-2.5 py-1 rounded bg-slate-800 text-slate-200 text-xs font-medium">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
                @endif

                @if($pacer->user && ($pacer->user->pb_5k || $pacer->user->pb_10k || $pacer->user->pb_hm || $pacer->user->pb_fm))
                <section x-data="{ open: true }" class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="pacer-heading text-lg text-white">Personal Best</h2>
                        <button type="button" x-on:click="open = !open" class="text-xs text-slate-300 hover:text-white px-2.5 py-1 rounded bg-slate-800 cursor-pointer" x-text="open ? 'Tutup' : 'Buka'"></button>
                    </div>
                    <div x-show="open" class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                            <div class="text-xs text-slate-400">5K</div>
                            <p class="mt-1 text-lg font-mono tabular-nums font-bold {{ $pacer->category === '5K' ? 'text-lime-300' : 'text-white' }}">{{ $pacer->user->pb_5k ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                            <div class="text-xs text-slate-400">10K</div>
                            <p class="mt-1 text-lg font-mono tabular-nums font-bold {{ $pacer->category === '10K' ? 'text-lime-300' : 'text-white' }}">{{ $pacer->user->pb_10k ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                            <div class="text-xs text-slate-400">Half Marathon</div>
                            <p class="mt-1 text-lg font-mono tabular-nums font-bold {{ $pacer->category === 'HM (21K)' ? 'text-lime-300' : 'text-white' }}">{{ $pacer->user->pb_hm ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5">
                            <div class="text-xs text-slate-400">Full Marathon</div>
                            <p class="mt-1 text-lg font-mono tabular-nums font-bold {{ $pacer->category === 'FM (42K)' ? 'text-lime-300' : 'text-white' }}">{{ $pacer->user->pb_fm ?? '-' }}</p>
                        </div>
                    </div>
                </section>
                @endif

                @if($pacer->user && $pacer->user->profile_images && count($pacer->user->profile_images) > 0)
                <section x-data="{ open: true }" class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="pacer-heading text-lg text-white">Galeri</h2>
                        <button type="button" x-on:click="open = !open" class="text-xs text-slate-300 hover:text-white px-2.5 py-1 rounded bg-slate-800 cursor-pointer" x-text="open ? 'Tutup' : 'Buka'"></button>
                    </div>
                    <div x-show="open" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($pacer->user->profile_images as $image)
                            @php
                                $imgSrc = str_starts_with($image, 'http')
                                    ? $image
                                    : (str_starts_with($image, '/storage') ? asset(ltrim($image, '/')) : asset('storage/' . $image));
                            @endphp
                            <button type="button" data-gallery-src="{{ $imgSrc }}" class="group block aspect-square bg-slate-950 border border-slate-800 rounded-lg overflow-hidden cursor-pointer focus:outline-none focus:ring-2 focus:ring-lime-400">
                                <img src="{{ $imgSrc }}" alt="Galeri {{ $pacerName }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:opacity-90 transition">
                            </button>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <h2 class="pacer-heading text-lg text-white">Siap untuk Race Day?</h2>
                    <p class="text-sm text-slate-300 mt-1.5 leading-relaxed">
                        Isi kebutuhan race dan target pace kamu, selesaikan pembayaran di platform, lalu akses kontak pacer akan langsung terbuka.
                    </p>
                    <div class="mt-4 space-y-2.5">
                        <button id="btnHirePacerSide" type="button" class="w-full py-2.5 px-4 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition cursor-pointer">
                            Sewa Pacer
                        </button>
                        <button id="btnChatWhatsAppSide" type="button" class="w-full py-2.5 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-semibold text-sm transition cursor-pointer">
                            Chat WhatsApp
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        Sertakan nama race, tanggal, dan target pace agar konfirmasi lebih cepat.
                    </p>
                </div>

                @if($pacer->race_portfolio && is_array($pacer->race_portfolio) && count($pacer->race_portfolio) > 0)
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <h3 class="pacer-heading text-base text-white mb-3">Portofolio Race</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($pacer->race_portfolio as $race)
                            <span class="px-2.5 py-1 rounded bg-slate-800 text-slate-200 text-xs font-medium">
                                {{ $race }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <h3 class="pacer-heading text-base text-white mb-3">Kontak &amp; Sosial Media</h3>

                    @if($contactUnlocked)
                        <div class="space-y-2.5">
                            @if($pacer->user && $pacer->user->strava_url)
                            <a href="{{ $pacer->user->strava_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-3.5 py-2.5 bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-md text-slate-200 hover:text-white transition text-sm">
                                <span class="font-semibold">Strava</span>
                                <span class="text-xs text-slate-400">Buka</span>
                            </a>
                            @endif
                            @if($pacer->user && $pacer->user->instagram_url)
                            <a href="{{ $pacer->user->instagram_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-3.5 py-2.5 bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-md text-slate-200 hover:text-white transition text-sm">
                                <span class="font-semibold">Instagram</span>
                                <span class="text-xs text-slate-400">Buka</span>
                            </a>
                            @endif
                            @if($pacer->user && $pacer->user->facebook_url)
                            <a href="{{ $pacer->user->facebook_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-3.5 py-2.5 bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-md text-slate-200 hover:text-white transition text-sm">
                                <span class="font-semibold">Facebook</span>
                                <span class="text-xs text-slate-400">Buka</span>
                            </a>
                            @endif
                            @if($pacer->user && $pacer->user->tiktok_url)
                            <a href="{{ $pacer->user->tiktok_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-3.5 py-2.5 bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-md text-slate-200 hover:text-white transition text-sm">
                                <span class="font-semibold">TikTok</span>
                                <span class="text-xs text-slate-400">Buka</span>
                            </a>
                            @endif
                            @if($pacer->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $pacer->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-3.5 py-2.5 bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-md text-emerald-300 hover:text-emerald-200 transition text-sm">
                                <span class="font-semibold">WhatsApp</span>
                                <span class="text-xs text-emerald-400">Chat</span>
                            </a>
                            @endif
                        </div>
                    @else
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4">
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Tautan WhatsApp dan media sosial pacer terlindungi. Selesaikan booking untuk membuka kontak langsung.
                            </p>
                            <div class="mt-3">
                                @auth
                                    <button id="btnUnlockContacts" type="button" class="w-full py-2 px-3 rounded-md bg-slate-800 hover:bg-slate-700 text-lime-300 font-semibold text-xs transition cursor-pointer">
                                        Booking untuk Buka Kontak
                                    </button>
                                @endauth
                                @guest
                                    <a href="{{ route('login') }}" class="block text-center w-full py-2 px-3 rounded-md bg-slate-800 hover:bg-slate-700 text-lime-300 font-semibold text-xs transition">
                                        Login untuk Booking
                                    </a>
                                @endguest
                            </div>
                        </div>
                    @endif

                    <div class="mt-5 pt-5 border-t border-slate-800">
                        <h4 class="text-xs font-semibold text-slate-300 mb-3">QR Profil Pacer</h4>
                        <div class="flex items-center justify-center">
                            <div class="p-2.5 rounded-lg border border-slate-800 bg-white">
                                <div id="pacer-profile-qr-inner"></div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 text-center mt-2">Pindai untuk membuka profil ini</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<div class="fixed bottom-0 left-0 right-0 z-40 md:hidden bg-slate-900 border-t border-slate-800">
    <div class="mx-auto max-w-4xl px-4 py-3">
        <div class="grid grid-cols-3 gap-2">
            <button id="btnHirePacerSticky" type="button" class="py-2.5 rounded-md bg-lime-400 text-slate-950 font-bold text-sm cursor-pointer">Sewa</button>
            <button id="btnChatWhatsAppSticky" type="button" class="py-2.5 rounded-md bg-slate-800 border border-slate-700 text-white font-semibold text-sm cursor-pointer">Chat WA</button>
            <button id="btnSaveContactSticky" type="button" class="py-2.5 rounded-md bg-slate-800 border border-slate-700 text-white font-semibold text-sm cursor-pointer">Simpan</button>
        </div>
    </div>
</div>

<div id="hire-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="hire-modal-title">
    <div id="hire-modal-overlay" class="absolute inset-0 bg-slate-950/90"></div>
    <div class="relative z-10 w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-sm flex flex-col overflow-hidden max-h-full">
            <div class="p-5 sm:p-6 border-b border-slate-800 flex items-start justify-between gap-4 flex-shrink-0">
                <div>
                    <div class="text-xs text-slate-400 font-semibold">Sewa Pacer</div>
                    <div id="hire-modal-title" class="pacer-heading text-white text-xl mt-1">{{ $pacerName }}</div>
                    <p class="text-xs text-slate-300 mt-1">Isi detail race, lanjutkan pembayaran di platform. Kontak pacer terbuka setelah status paid.</p>
                </div>
                <button id="btnCloseHireModal" type="button" aria-label="Tutup modal" class="w-9 h-9 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white flex items-center justify-center cursor-pointer">✕</button>
            </div>
            <form id="hireBookingForm" method="POST" action="{{ route('pacer.bookings.store', $pacer->seo_slug) }}" class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="hireRaceName" class="text-xs text-slate-300 font-medium">Nama Race</label>
                        <input id="hireRaceName" name="event_name" type="text" class="mt-1.5 w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:border-lime-400" placeholder="Contoh: Borobudur Marathon">
                    </div>
                    <div>
                        <label for="hireRaceDate" class="text-xs text-slate-300 font-medium">Tanggal Race</label>
                        <input id="hireRaceDate" name="race_date" type="date" class="mt-1.5 w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:border-lime-400">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="hireDistance" class="text-xs text-slate-300 font-medium">Jarak</label>
                        <input id="hireDistance" name="distance" type="text" class="mt-1.5 w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:border-lime-400" placeholder="Contoh: 10K / HM / FM">
                    </div>
                    <div>
                        <label for="hireTargetPace" class="text-xs text-slate-300 font-medium">Target Pace</label>
                        <input id="hireTargetPace" name="target_pace" type="text" class="mt-1.5 w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-sm font-mono focus:outline-none focus:border-lime-400" placeholder="Contoh: 6:00/km">
                    </div>
                </div>
                <div>
                    <label for="hireMeetPoint" class="text-xs text-slate-300 font-medium">Titik Kumpul</label>
                    <input id="hireMeetPoint" name="meeting_point" type="text" class="mt-1.5 w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:border-lime-400" placeholder="Contoh: Gate start line / area pacer">
                </div>
                <div>
                    <label for="hireNotes" class="text-xs text-slate-300 font-medium">Catatan</label>
                    <textarea id="hireNotes" name="notes" rows="3" class="mt-1.5 w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:border-lime-400" placeholder="Contoh: butuh strategi negative split dan pengaturan hidrasi"></textarea>
                </div>
                <div class="rounded-lg bg-slate-950 border border-slate-800 p-4">
                    <div class="text-xs text-slate-400 font-semibold">Pratinjau Pesan</div>
                    <pre id="hirePreview" class="mt-2 whitespace-pre-wrap text-xs sm:text-sm text-slate-200 font-mono"></pre>
                </div>
            </form>
            <div class="p-5 sm:p-6 border-t border-slate-800 space-y-2 flex-shrink-0">
                <button id="btnProceedPayment" type="button" class="w-full py-3 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition cursor-pointer">Lanjutkan Pembayaran</button>
                <div class="text-xs text-slate-400 text-center">Pembayaran aman melalui Midtrans. Kontak pacer terbuka setelah lunas.</div>
            </div>
        </div>
    </div>
</div>

<div id="gallery-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-label="Pratinjau galeri">
    <div id="gallery-modal-overlay" class="absolute inset-0 bg-slate-950/95"></div>
    <div class="relative z-10 h-full w-full flex items-center justify-center p-4">
        <button id="btnCloseGallery" type="button" aria-label="Tutup galeri" class="absolute top-4 right-4 w-10 h-10 rounded-md bg-slate-900 border border-slate-800 text-white flex items-center justify-center cursor-pointer">✕</button>
        <button id="btnPrevGallery" type="button" aria-label="Foto sebelumnya" class="absolute left-3 md:left-8 w-10 h-10 rounded-md bg-slate-900 border border-slate-800 text-white flex items-center justify-center cursor-pointer">‹</button>
        <button id="btnNextGallery" type="button" aria-label="Foto berikutnya" class="absolute right-3 md:right-8 w-10 h-10 rounded-md bg-slate-900 border border-slate-800 text-white flex items-center justify-center cursor-pointer">›</button>
        <img id="gallery-image" src="" alt="Foto galeri pacer" class="max-h-[85vh] max-w-[92vw] rounded-lg border border-slate-800 object-contain">
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var btnSave = document.getElementById('btnSaveContact');
    var btnShare = document.getElementById('btnShareProfile');
    var btnSaveSticky = document.getElementById('btnSaveContactSticky');
    var btnHireTop = document.getElementById('btnHirePacerTop');
    var btnHireSide = document.getElementById('btnHirePacerSide');
    var btnHireSticky = document.getElementById('btnHirePacerSticky');
    var btnChatTop = document.getElementById('btnChatWhatsAppTop');
    var btnChatSide = document.getElementById('btnChatWhatsAppSide');
    var btnChatSticky = document.getElementById('btnChatWhatsAppSticky');

    var hireModal = document.getElementById('hire-modal');
    var hireOverlay = document.getElementById('hire-modal-overlay');
    var btnCloseHireModal = document.getElementById('btnCloseHireModal');
    var btnProceedPayment = document.getElementById('btnProceedPayment');
    var hireBookingForm = document.getElementById('hireBookingForm');
    var hirePreview = document.getElementById('hirePreview');
    var hireRaceName = document.getElementById('hireRaceName');
    var hireRaceDate = document.getElementById('hireRaceDate');
    var hireDistance = document.getElementById('hireDistance');
    var hireTargetPace = document.getElementById('hireTargetPace');
    var hireMeetPoint = document.getElementById('hireMeetPoint');
    var hireNotes = document.getElementById('hireNotes');
    var btnUnlockContacts = document.getElementById('btnUnlockContacts');

    var pacerName = @json($pacerName);
    var pacerCategory = @json($pacer->category ?? '');
    var pacerPace = @json($pacer->pace ?? '');
    var contactUnlocked = @json((bool) ($contactUnlocked ?? false));
    var pacerWhatsapp = @json($pacer->whatsapp ?? '');
    var waNumber = contactUnlocked ? String(pacerWhatsapp || '').replace(/\D/g, '') : '';

    function downloadVCard(){
        var name = @json($pacer->user->name);
        var nickname = @json($pacer->nickname ?? '');
        var category = @json($pacer->category);
        var whatsapp = contactUnlocked ? @json($pacer->whatsapp ?? '') : '';
        var bio = @json($pacer->bio ?? '');
        var url = window.location.href;
        var parts = String(name).split(' ');
        var nField = parts.length > 1 ? (parts.slice(1).join(';') + ';' + parts[0]) : name + ';;;;';
        var vCardData = 'BEGIN:VCARD\n'
            + 'VERSION:3.0\n'
            + 'FN:' + name + '\n'
            + 'N:' + nField + '\n'
            + (nickname ? ('NICKNAME:' + nickname + '\n') : '')
            + 'ORG:Ruang Lari Indonesia\n'
            + 'TITLE:Professional Pacer (' + category + ')\n'
            + (whatsapp ? ('TEL;TYPE=CELL:' + whatsapp + '\n') : '')
            + 'URL:' + url + '\n'
            + (bio ? ('NOTE:' + bio + '\n') : '')
            + 'END:VCARD';
        var blob = new Blob([vCardData], { type: 'text/vcard' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = String(name).replace(/\s+/g, '_') + '.vcf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    async function shareProfile(){
        var shareData = {
            title: @json($pacer->user->name) + ' - Profil Pacer RuangLari',
            text: 'Lihat profil pacer ini di RuangLari dan sewa untuk race day.',
            url: window.location.href
        };
        if (navigator.share) {
            try { await navigator.share(shareData); } catch(e){}
        } else {
            try { await navigator.clipboard.writeText(window.location.href); alert('Link profil disalin ke clipboard.'); } catch(e){}
        }
    }

    if (btnSave) btnSave.addEventListener('click', downloadVCard);
    if (btnSaveSticky) btnSaveSticky.addEventListener('click', downloadVCard);
    if (btnShare) btnShare.addEventListener('click', shareProfile);

    var qrContainer = document.getElementById('pacer-profile-qr-inner');
    function initQr(){
        if (qrContainer && typeof QRCode !== 'undefined' && !qrContainer.hasChildNodes()) {
            new QRCode(qrContainer, {
                text: window.location.href,
                width: 164,
                height: 164,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    }
    initQr();
    window.addEventListener('load', initQr);

    function buildHireMessage(){
        var raceName = (hireRaceName && hireRaceName.value || '').trim();
        var raceDate = (hireRaceDate && hireRaceDate.value || '').trim();
        var distance = (hireDistance && hireDistance.value || '').trim();
        var targetPace = (hireTargetPace && hireTargetPace.value || '').trim();
        var meetPoint = (hireMeetPoint && hireMeetPoint.value || '').trim();
        var notes = (hireNotes && hireNotes.value || '').trim();

        var lines = [];
        lines.push('Halo ' + pacerName + ', saya ingin menyewa jasa pacer.');
        if (pacerCategory) lines.push('Kategori pacer: ' + pacerCategory);
        if (pacerPace) lines.push('Pace pacer: ' + pacerPace);
        lines.push('');
        if (raceName) lines.push('Race: ' + raceName);
        if (raceDate) lines.push('Tanggal: ' + raceDate);
        if (distance) lines.push('Jarak: ' + distance);
        if (targetPace) lines.push('Target pace: ' + targetPace);
        if (meetPoint) lines.push('Titik kumpul: ' + meetPoint);
        if (notes) lines.push('Catatan: ' + notes);
        lines.push('');
        lines.push('Link profil: ' + window.location.href);
        return lines.join('\n');
    }

    function updateHirePreview(){
        if (!hirePreview) return;
        hirePreview.textContent = buildHireMessage();
    }

    function openHireModal(){
        if (!hireModal) return;
        hireModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        updateHirePreview();
    }

    function closeHireModal(){
        if (!hireModal) return;
        hireModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function chatWhatsAppQuick(){
        if (!contactUnlocked) {
            openHireModal();
            return;
        }
        if (!waNumber) {
            alert('Pacer belum menambahkan nomor WhatsApp.');
            return;
        }
        var msg = encodeURIComponent('Halo ' + pacerName + ', saya tertarik menyewa pacer untuk race day. Boleh info ketersediaannya?\n\nLink profil: ' + window.location.href);
        window.open('https://wa.me/' + waNumber + '?text=' + msg, '_blank', 'noopener');
    }

    if (btnHireTop) btnHireTop.addEventListener('click', openHireModal);
    if (btnHireSide) btnHireSide.addEventListener('click', openHireModal);
    if (btnHireSticky) btnHireSticky.addEventListener('click', openHireModal);
    if (btnUnlockContacts) btnUnlockContacts.addEventListener('click', openHireModal);
    if (btnCloseHireModal) btnCloseHireModal.addEventListener('click', closeHireModal);
    if (hireOverlay) hireOverlay.addEventListener('click', closeHireModal);

    if (btnChatTop) btnChatTop.addEventListener('click', chatWhatsAppQuick);
    if (btnChatSide) btnChatSide.addEventListener('click', chatWhatsAppQuick);
    if (btnChatSticky) btnChatSticky.addEventListener('click', chatWhatsAppQuick);

    [hireRaceName, hireRaceDate, hireDistance, hireTargetPace, hireMeetPoint, hireNotes].forEach(function(el){
        if (el) el.addEventListener('input', updateHirePreview);
    });

    if (btnProceedPayment) btnProceedPayment.addEventListener('click', function(){
        if (!hireBookingForm) return;
        hireBookingForm.submit();
    });

    var galleryButtons = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-src]'));
    var galleryModal = document.getElementById('gallery-modal');
    var galleryOverlay = document.getElementById('gallery-modal-overlay');
    var galleryImage = document.getElementById('gallery-image');
    var btnCloseGallery = document.getElementById('btnCloseGallery');
    var btnPrevGallery = document.getElementById('btnPrevGallery');
    var btnNextGallery = document.getElementById('btnNextGallery');
    var galleryIndex = 0;

    function openGalleryAt(idx){
        if (!galleryModal || !galleryImage || galleryButtons.length === 0) return;
        galleryIndex = Math.max(0, Math.min(idx, galleryButtons.length - 1));
        galleryImage.src = galleryButtons[galleryIndex].getAttribute('data-gallery-src');
        galleryModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function closeGallery(){
        if (!galleryModal) return;
        galleryModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    function prevGallery(){
        openGalleryAt((galleryIndex - 1 + galleryButtons.length) % galleryButtons.length);
    }
    function nextGallery(){
        openGalleryAt((galleryIndex + 1) % galleryButtons.length);
    }

    galleryButtons.forEach(function(btn, idx){
        btn.addEventListener('click', function(){ openGalleryAt(idx); });
    });
    if (btnCloseGallery) btnCloseGallery.addEventListener('click', closeGallery);
    if (btnPrevGallery) btnPrevGallery.addEventListener('click', prevGallery);
    if (btnNextGallery) btnNextGallery.addEventListener('click', nextGallery);
    if (galleryOverlay) galleryOverlay.addEventListener('click', closeGallery);
});
</script>
@endpush
@endsection
