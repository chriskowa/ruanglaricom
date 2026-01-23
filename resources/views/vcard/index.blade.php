<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">

    @if($bgImageUrl)
        <link rel="preload" as="image" href="{{ $bgImageUrl }}">
    @endif
    @if($logoUrl)
        <link rel="preload" as="image" href="{{ $logoUrl }}">
    @endif

    <style>
        :root { --bg:#0f172a; --card:rgba(15,23,42,.62); --line:rgba(148,163,184,.22); --txt:#e5e7eb; --muted:#94a3b8; --neon:#ccff00; }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{margin:0;background:var(--bg);color:var(--txt);font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif}
        a{color:inherit;text-decoration:none}
        .wrap{min-height:100%;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:28px 16px}
        .bg{position:fixed;inset:0;z-index:-2;overflow:hidden}
        .bg img{width:100%;height:100%;object-fit:cover;filter:saturate(1.05)}
        .shade{position:fixed;inset:0;z-index:-1;background:linear-gradient(180deg, rgba(15,23,42,.86), rgba(15,23,42,.62), rgba(15,23,42,.92))}
        .container{width:100%;max-width:520px}
        .brand{display:flex;flex-direction:column;align-items:center;gap:14px;margin-bottom:18px}
        .logo{width:184px;height:auto;filter:drop-shadow(0 18px 26px rgba(0,0,0,.45))}
        .title{font-size:22px;font-weight:800;letter-spacing:-.02em;margin:0;text-align:center}
        .desc{margin:6px 0 0 0;color:var(--muted);font-size:13px;line-height:1.45;text-align:center}
        .featured{margin-top:18px}
        .cardGlow{border-radius:18px;padding:1px;background:linear-gradient(135deg, rgba(204,255,0,.85), rgba(34,211,238,.55));box-shadow:0 18px 60px rgba(0,0,0,.5)}
        .cardGlow > a{display:block;border-radius:17px;background:linear-gradient(135deg, rgba(16,185,129,.85), rgba(6,95,70,.85));padding:14px 14px}
        .row{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .badge{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:rgba(236,253,245,.85);font-weight:800}
        .featTitle{font-size:16px;font-weight:900;color:white;margin:2px 0 0 0}
        .iconBox{width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .arrow{width:34px;height:34px;border-radius:999px;background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .grid{display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:10px;margin-top:14px}
        .btn{border-radius:16px;background:rgba(255,255,255,.08);backdrop-filter: blur(12px);border:1px solid rgba(255,255,255,.14);padding:14px 12px;min-height:122px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;transition:transform .18s ease,border-color .18s ease,background .18s ease}
        .btn:hover{transform:translateY(-2px);border-color:rgba(204,255,0,.35);background:rgba(255,255,255,.11)}
        .miniIcon{width:46px;height:46px;border-radius:999px;background:linear-gradient(180deg, rgba(204,255,0,.14), rgba(204,255,0,0));border:1px solid rgba(204,255,0,.18);display:flex;align-items:center;justify-content:center}
        .btnTitle{font-size:13px;font-weight:800;color:#f1f5f9;text-align:center;line-height:1.2}
        .ads{margin-top:14px;border-radius:16px;padding:16px;border:2px dashed rgba(148,163,184,.35);background:rgba(255,255,255,.04);transition:border-color .18s ease, background .18s ease}
        .ads:hover{border-color:rgba(204,255,0,.45);background:rgba(15,23,42,.55)}
        .adsTitle{font-size:13px;font-weight:900;color:#e2e8f0;margin:0}
        .adsDesc{font-size:12px;color:rgba(148,163,184,.9);margin:6px 0 0 0}
        .footer{margin-top:18px;text-align:center}
        .social{display:flex;justify-content:center;gap:12px;margin:10px 0 12px}
        .social a{width:42px;height:42px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);transition:transform .18s ease,border-color .18s ease}
        .social a:hover{transform:translateY(-2px);border-color:rgba(204,255,0,.35)}
        .copy{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:rgba(148,163,184,.7)}
        .sr{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0}
        @media (prefers-reduced-motion: reduce){
            .btn,.social a,.cardGlow > a{transition:none}
            .btn:hover,.social a:hover{transform:none}
        }
    </style>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Ruang Lari Indonesia',
            'url' => url('/'),
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
</head>
<body>
    <div class="bg" aria-hidden="true">
        @if($bgImageUrl)
            <img src="{{ $bgImageUrl }}" alt="" decoding="async" fetchpriority="high">
        @endif
    </div>
    <div class="shade" aria-hidden="true"></div>

    <main class="wrap">
        <div class="container">
            <header class="brand">
                @if($logoUrl)
                    <img class="logo" src="{{ $logoUrl }}" alt="Ruang Lari" decoding="async" fetchpriority="high">
                @endif
                <div>
                    <h1 class="title">Selamat Datang di Ruang Lari</h1>
                    <p class="desc">{{ $description }}</p>
                </div>
            </header>

            <section class="featured" aria-label="Featured">
                @foreach($featuredLinks as $feat)
                    <div class="cardGlow">
                        <a href="{{ $feat['url'] ?? '#' }}" {{ !empty($feat['external']) ? 'target=_blank rel=noopener' : '' }} aria-label="{{ $feat['title'] ?? 'Featured' }}">
                            <div class="row">
                                <div class="row" style="gap:12px;align-items:center;">
                                    <div class="iconBox" aria-hidden="true">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="badge">{{ $feat['badge'] ?? 'Featured' }}</div>
                                        <div class="featTitle">{{ $feat['title'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="arrow" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"></path><path d="M13 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </section>

            <section class="grid" aria-label="Links">
                @foreach($links as $link)
                    <a class="btn" href="{{ $link['url'] ?? '#' }}" {{ !empty($link['external']) ? 'target=_blank rel=noopener' : '' }} aria-label="{{ $link['title'] ?? 'Link' }}">
                        <div class="miniIcon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--neon)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20"></path><path d="M2 12h20"></path>
                            </svg>
                        </div>
                        <div class="btnTitle">{{ $link['title'] ?? '' }}</div>
                    </a>
                @endforeach
            </section>

            @if($adsUrl)
                <a class="ads" href="{{ $adsUrl }}" target="_blank" rel="noopener" aria-label="Iklan">
                    <p class="adsTitle">{{ $adsTitle }}</p>
                    <p class="adsDesc">{{ $adsDescription }}</p>
                </a>
            @endif

            <footer class="footer">
                <div class="social" aria-label="Social">
                    @foreach($socialLinks as $s)
                        <a href="{{ $s['url'] ?? '#' }}" {{ !empty($s['external']) ? 'target=_blank rel=noopener' : '' }} aria-label="{{ $s['title'] ?? 'Social' }}">
                            <span class="sr">{{ $s['title'] ?? 'Social' }}</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--txt)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 21a9 9 0 1 0-9-9"></path><path d="M12 7v5l3 3"></path>
                            </svg>
                        </a>
                    @endforeach
                </div>
                <div class="copy">© {{ date('Y') }} Ruang Lari Indonesia</div>
            </footer>
        </div>
    </main>
</body>
</html>

