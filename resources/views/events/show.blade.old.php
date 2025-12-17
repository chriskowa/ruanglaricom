<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>{{ $event->name }} - Official Race Event</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="{{ strip_tags($event->short_description ?? $event->name) }}" />
  <meta name="keywords" content="event lari, lari indonesia, lomba lari, fun run, half marathon, {{ $event->location_name }}" />
  <meta name="robots" content="index, follow" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="canonical" href="{{ url('/events/' . $event->slug) }}" />
  
  <!-- Midtrans Snap SDK -->
  @php
    $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com');
  @endphp
  <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
  <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

  <!-- Open Graph / Social -->
  @php
    $heroImageUrl = null;
    if ($event->hero_image) {
      $heroImageUrl = asset('storage/' . $event->hero_image);
    } elseif ($event->hero_image_url) {
      $heroImageUrl = $event->hero_image_url;
    } else {
      $heroImageUrl = url('/assets/default-event-cover.jpg');
    }
  @endphp
  @php
    $shortDescriptionClean = strip_tags($event->short_description ?? '');
    if (empty($shortDescriptionClean)) {
      $shortDescriptionClean = 'Ikuti event lari ' . $event->name . ' dengan rute ikonik, jersey eksklusif, dan medali finisher. Kuota terbatas!';
    }
  @endphp
  <meta property="og:title" content="{{ $event->name }} - Official Race Event" />
  <meta property="og:description" content="{{ $shortDescriptionClean }}" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="{{ url('/events/' . $event->slug) }}" />
  <meta property="og:image" content="{{ $heroImageUrl }}" />

  <!-- Structured Data: Event -->
  @php
    $structuredData = [
      '@context' => 'https://schema.org',
      '@type' => 'SportsEvent',
      'name' => $event->name,
      'startDate' => $event->start_at->toIso8601String(),
      'eventStatus' => 'https://schema.org/EventScheduled',
      'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
      'location' => [
        '@type' => 'Place',
        'name' => $event->location_name,
      ],
      'description' => strip_tags($event->short_description ?? $event->name),
    ];

    if ($event->end_at) {
      $structuredData['endDate'] = $event->end_at->toIso8601String();
    }

    if ($event->location_address) {
      $structuredData['location']['address'] = [
        '@type' => 'PostalAddress',
        'streetAddress' => $event->location_address,
        'addressCountry' => 'ID',
      ];
    }

    if ($event->location_lat && $event->location_lng) {
      $structuredData['location']['geo'] = [
        '@type' => 'GeoCoordinates',
        'latitude' => (float) $event->location_lat,
        'longitude' => (float) $event->location_lng,
      ];
    }

    $heroImage = null;
    if ($event->hero_image) {
      $heroImage = asset('storage/' . $event->hero_image);
    } elseif ($event->hero_image_url) {
      $heroImage = $event->hero_image_url;
    }
    if ($heroImage) {
      $structuredData['image'] = [$heroImage];
    }

    if ($event->user) {
      $structuredData['organizer'] = [
        '@type' => 'Organization',
        'name' => $event->user->name,
        'url' => url('/'),
      ];
    }
  @endphp
  <script type="application/ld+json">
  {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>

  <style>
    :root {
      --primary: #e63946;
      --primary-dark: #c62832;
      --secondary: #1d3557;
      --background: #f8f9fa;
      --text: #222222;
      --muted: #6c757d;
      --radius-lg: 1.25rem;
      --radius-md: 0.75rem;
      --max-width: 1100px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--text);
      background: var(--background);
      line-height: 1.6;
    }

    img {
      max-width: 100%;
      height: auto;
      display: block;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    .container {
      width: 100%;
      max-width: var(--max-width);
      margin: 0 auto;
      padding: 0 1.25rem;
    }

    header {
      position: sticky;
      top: 0;
      z-index: 50;
      background: rgba(248, 249, 250, 0.96);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.75rem 0;
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 700;
      letter-spacing: 0.03em;
      color: var(--secondary);
    }

    .nav-brand img.event-logo {
      width: 32px;
      height: 32px;
      object-fit: contain;
      border-radius: 4px;
    }

    .nav-brand span.logo-mark {
      width: 32px;
      height: 32px;
      border-radius: 999px;
      background: radial-gradient(circle at 30% 20%, #ffffff, var(--primary));
      display: inline-block;
    }

    .nav-links {
      display: flex;
      gap: 1.25rem;
      font-size: 0.95rem;
      font-weight: 500;
    }

    .nav-links a {
      position: relative;
    }

    .nav-links a::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -4px;
      width: 0;
      height: 2px;
      background: var(--primary);
      transition: width 0.2s ease;
    }

    .nav-links a:hover::after {
      width: 100%;
    }

    .nav-cta {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.7rem 1.3rem;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.95rem;
      transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
      white-space: nowrap;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary);
      color: #ffffff;
      box-shadow: 0 8px 18px rgba(230, 57, 70, 0.35);
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(198, 40, 50, 0.4);
    }

    .btn-outline {
      background: transparent;
      color: var(--secondary);
      border: 1px solid rgba(0,0,0,0.12);
    }

    .btn-outline:hover {
      background: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .mobile-nav-toggle {
      display: none;
      border: none;
      background: transparent;
      font-size: 1.5rem;
      cursor: pointer;
    }

    /* Hero */
    .hero {
      padding: 3rem 0 2.5rem;
    }

    .hero-grid {
      display: grid;
      gap: 2rem;
      align-items: center;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.25rem 0.8rem;
      border-radius: 999px;
      background: rgba(230, 57, 70, 0.08);
      color: var(--primary-dark);
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 0.75rem;
    }

    .hero-title {
      font-size: clamp(2rem, 4vw, 2.7rem);
      margin: 0 0 0.75rem;
      color: var(--secondary);
    }

    .hero-subtitle {
      font-size: 1rem;
      color: var(--muted);
      margin-bottom: 1.25rem;
    }

    .hero-subtitle p {
      margin: 0;
    }

    .hero-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }

    .hero-tag {
      padding: 0.35rem 0.9rem;
      border-radius: 999px;
      background: #ffffff;
      border: 1px solid rgba(0,0,0,0.06);
      font-size: 0.8rem;
      display: inline-flex;
      gap: 0.4rem;
      align-items: center;
      color: var(--muted);
    }

    .hero-cta {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      align-items: center;
      margin-bottom: 1.25rem;
    }

    .hero-note {
      font-size: 0.8rem;
      color: var(--muted);
    }

    .hero-stat-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 1.25rem;
      padding-top: 1.25rem;
      border-top: 1px dashed rgba(0,0,0,0.08);
    }

    .stat {
      min-width: 120px;
    }

    .stat-label {
      font-size: 0.75rem;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .stat-value {
      font-weight: 700;
      font-size: 1.1rem;
    }

    .hero-visual {
      position: relative;
      border-radius: var(--radius-lg);
      overflow: hidden;
      background: radial-gradient(circle at 20% 0%, #ffffff, #1d3557);
      color: #ffffff;
      min-height: 260px;
      padding: 1.25rem;
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
    }

    .floating-image {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      max-width: 200px;
      max-height: 300px;
      animation: float 3s ease-in-out infinite;
      z-index: 1;
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(-50%) translateX(0);
      }
      50% {
        transform: translateY(-50%) translateX(10px);
      }
    }

    .hero-visual .overlay-card {
      background: rgba(255, 255, 255, 0.07);
      padding: 0.8rem 1rem;
      border-radius: var(--radius-md);
      font-size: 0.8rem;
      max-width: 200px;
      backdrop-filter: blur(4px);
    }

    .hero-visual .runner-shape {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .hero-visual .runner-shape::before {
      content: "";
      position: absolute;
      width: 120%;
      height: 120%;
      border-radius: 50%;
      border: 1px dashed rgba(255,255,255,0.4);
      top: -10%;
      left: -10%;
    }

    .hero-visual .runner-text {
      font-weight: 700;
      text-align: center;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    /* Sections general */
    section {
      padding: 2.75rem 0;
    }

    section:nth-of-type(even) {
      background: #ffffff;
    }

    .section-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .section-title {
      margin: 0;
      font-size: 1.6rem;
      color: var(--secondary);
    }

    .section-subtitle {
      margin-top: 0.5rem;
      color: var(--muted);
      font-size: 0.95rem;
    }

    /* Fasilitas */
    .facility-grid {
      display: grid;
      gap: 1.5rem;
    }

    .facility-card {
      background: #ffffff;
      border-radius: var(--radius-md);
      padding: 1.25rem;
      border: 1px solid rgba(0,0,0,0.06);
      box-shadow: 0 6px 14px rgba(0,0,0,0.02);
    }

    .facility-card h3 {
      margin: 0 0 0.35rem;
      font-size: 1rem;
      color: var(--secondary);
    }

    .facility-card p {
      margin: 0;
      font-size: 0.9rem;
      color: var(--muted);
    }

    /* Jersey & Medal */
    .two-column {
      display: grid;
      gap: 2rem;
    }

    .card {
      background: #ffffff;
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      border: 1px solid rgba(0,0,0,0.06);
      box-shadow: 0 8px 18px rgba(0,0,0,0.03);
    }

    .card h3 {
      margin-top: 0;
      font-size: 1.2rem;
      color: var(--secondary);
    }

    .tag-row {
      display: flex;
      flex-wrap: wrap;
      gap: 0.35rem;
      margin-top: 0.75rem;
    }

    .tag {
      font-size: 0.75rem;
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      border: 1px solid rgba(0,0,0,0.08);
      color: var(--muted);
      background: #fdfdfd;
    }

    /* FAQ */
    .faq-list {
      max-width: 800px;
      margin: 0 auto;
      text-align: left;
    }

    details {
      background: #ffffff;
      border-radius: var(--radius-md);
      border: 1px solid rgba(0,0,0,0.06);
      padding: 0.75rem 1rem;
      margin-bottom: 0.75rem;
    }

    summary {
      cursor: pointer;
      font-weight: 600;
      color: var(--secondary);
      list-style: none;
    }

    summary::-webkit-details-marker {
      display: none;
    }

    summary::after {
      content: "+";
      float: right;
      font-weight: 700;
      color: var(--muted);
      transition: transform 0.2s ease;
    }

    details[open] summary::after {
      transform: rotate(45deg);
    }

    details p {
      margin-top: 0.5rem;
      font-size: 0.9rem;
      color: var(--muted);
    }

    /* Registration Form */
    .form-grid {
      display: grid;
      gap: 2rem;
    }

    form {
      display: grid;
      gap: 1rem;
    }

    .form-row {
      display: grid;
      gap: 1rem;
    }

    .form-row-2 {
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }

    label {
      display: block;
      font-size: 0.85rem;
      margin-bottom: 0.25rem;
      font-weight: 600;
      color: var(--secondary);
    }

    input, select, textarea {
      width: 100%;
      padding: 0.55rem 0.7rem;
      border-radius: 0.5rem;
      border: 1px solid rgba(0,0,0,0.16);
      font-size: 0.9rem;
      font-family: inherit;
      background: #ffffff;
    }

    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 1px rgba(230,57,70,0.3);
    }

    textarea {
      min-height: 80px;
      resize: vertical;
    }

    .form-helper {
      font-size: 0.75rem;
      color: var(--muted);
      margin-top: 0.15rem;
    }

    .checkbox-row {
      display: flex;
      gap: 0.5rem;
      align-items: flex-start;
      font-size: 0.85rem;
    }

    .checkbox-row input {
      width: auto;
      margin-top: 0.2rem;
    }

    /* Participant Item */
    .participant-item {
      transition: box-shadow 0.2s ease;
    }

    .participant-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .remove-participant {
      transition: background 0.2s ease;
    }

    .remove-participant:hover {
      background: var(--primary-dark) !important;
    }

    .category-info {
      min-height: 1.2rem;
    }

    /* Map & Save Date */
    .map-save-grid {
      display: grid;
      gap: 1.5rem;
    }

    .map-wrapper {
      position: relative;
      padding-bottom: 60%;
      height: 0;
      overflow: hidden;
      border-radius: var(--radius-lg);
      border: 1px solid rgba(0,0,0,0.08);
      box-shadow: 0 6px 14px rgba(0,0,0,0.03);
    }

    .map-wrapper iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: 0;
    }

    .save-date-card {
      background: #ffffff;
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      border: 1px solid rgba(0,0,0,0.06);
    }

    .save-date-card h3 {
      margin-top: 0;
      margin-bottom: 0.4rem;
    }

    .save-date-info {
      font-size: 0.9rem;
      color: var(--muted);
      margin-bottom: 0.75rem;
    }

    .pill-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
      margin-bottom: 0.75rem;
    }

    .pill {
      font-size: 0.75rem;
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      background: rgba(29,53,87,0.05);
      color: var(--secondary);
    }

    footer {
      border-top: 1px solid rgba(0,0,0,0.06);
      background: #ffffff;
      padding: 1.5rem 0 1.75rem;
      font-size: 0.85rem;
      color: var(--muted);
    }

    .footer-grid {
      display: grid;
      gap: 1rem;
      align-items: center;
    }

    .footer-links {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      justify-content: flex-end;
      font-size: 0.85rem;
    }

    .footer-links a {
      color: var(--muted);
    }

    .footer-links a:hover {
      color: var(--secondary);
    }

    /* Responsive */
    @media (min-width: 768px) {
      .hero-grid {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
      }
      .facility-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
      .two-column {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .form-grid {
        grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.9fr);
        align-items: flex-start;
      }
      .map-save-grid {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
      }
      .footer-grid {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
      }
    }

    @media (max-width: 768px) {
      .nav-links,
      .nav-cta {
        display: none;
      }
      .mobile-nav-toggle {
        display: block;
      }
      .nav-open .nav-links,
      .nav-open .nav-cta {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding-top: 0.75rem;
      }
      .nav {
        flex-wrap: wrap;
      }
      .hero {
        padding-top: 2rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="container nav" id="navbar">
      <a href="#top" class="nav-brand">
        @if($event->logo_image)
          <img src="{{ asset('storage/' . $event->logo_image) }}" alt="{{ $event->name }} Logo" class="event-logo">
        @else
          <span class="logo-mark" aria-hidden="true"></span>
        @endif
        <span>{{ $event->name }}</span>
      </a>
      <button class="mobile-nav-toggle" aria-label="Toggle navigation" id="navToggle">☰</button>
      <nav class="nav-links" aria-label="Navigasi utama">
        <a href="#fasilitas">Fasilitas</a>
        <a href="#jersey">Jersey</a>
        <a href="#medali">Medali</a>
        <a href="#registrasi">Registrasi</a>
        <a href="#lokasi">Lokasi</a>
        <a href="#faq">FAQ</a>
      </nav>
      @php
        $now = now();
        $isRegistrationOpen = true;
        
        if ($event->registration_open_at && $now < $event->registration_open_at) {
          $isRegistrationOpen = false;
        } elseif ($event->registration_close_at && $now > $event->registration_close_at) {
          $isRegistrationOpen = false;
        }
      @endphp
      <div class="nav-cta">
        @if($isRegistrationOpen)
          <a href="#registrasi" class="btn btn-primary">Daftar Sekarang</a>
        @endif
        <a href="#lokasi" class="btn btn-outline">Lihat Lokasi</a>
      </div>
    </div>
  </header>

  <main id="top">
    <!-- Hero Section -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="container hero-grid">
        <div>
          <div class="hero-badge">
            <span>Event Lari Nasional</span> • <span>{{ $event->start_at->format('d F Y') }}</span>
          </div>
          <h1 class="hero-title" id="hero-title">
            {{ $event->name }}
          </h1>
          <div class="hero-subtitle">
            {!! $event->short_description ?? '<p>Rasakan pengalaman lari dengan kategori yang tersedia. Kuota terbatas, termasuk jersey eksklusif dan medali finisher resmi.</p>' !!}
          </div>

          <div class="hero-meta">
            <span class="hero-tag">📍 {{ $event->location_name }}</span>
            <span class="hero-tag">⏰ Start {{ $event->start_at->format('H.i') }} WIB</span>
            <span class="hero-tag">🎽 Jersey &amp; Medali Finisher</span>
          </div>

          @php
            $now = now();
            $isRegistrationOpen = true;
            
            if ($event->registration_open_at && $now < $event->registration_open_at) {
              $isRegistrationOpen = false;
            } elseif ($event->registration_close_at && $now > $event->registration_close_at) {
              $isRegistrationOpen = false;
            }
          @endphp

          <div class="hero-cta">
            @if($isRegistrationOpen)
              <a href="#registrasi" class="btn btn-primary">Amankan Slot Lari</a>
            @else
              <button type="button" class="btn btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">
                @if($event->registration_open_at && $now < $event->registration_open_at)
                  Registrasi Belum Dibuka
                @else
                  Registrasi Sudah Ditutup
                @endif
              </button>
            @endif
            <a href="#faq" class="btn btn-outline">Lihat Detail Event</a>
          </div>

          <p class="hero-note">
            *Early bird terbatas. Setelah kuota habis, harga otomatis naik ke regular.
          </p>

          @php
            $categoryNames = $categories->pluck('name')->join(' • ');
            $totalQuota = $categories->sum('quota');
            $minAge = $categories->where('min_age', '!=', null)->min('min_age');
          @endphp

          <div class="hero-stat-bar" aria-label="Statistik event">
            <div class="stat">
              <div class="stat-label">Kategori</div>
              <div class="stat-value">{{ $categoryNames ?: 'Tersedia' }}</div>
            </div>
            @if($totalQuota > 0)
            <div class="stat">
              <div class="stat-label">Kuota Peserta</div>
              <div class="stat-value">&gt; {{ number_format($totalQuota, 0, ',', '.') }}</div>
            </div>
            @endif
            @if($minAge)
            <div class="stat">
              <div class="stat-label">Batas Usia</div>
              <div class="stat-value">Min. {{ $minAge }} Tahun</div>
            </div>
            @endif
          </div>
        </div>

        @php
          $heroImage = null;
          if ($event->hero_image) {
            $heroImage = asset('storage/' . $event->hero_image);
          } elseif ($event->hero_image_url) {
            $heroImage = $event->hero_image_url;
          }
        @endphp
        <div class="hero-visual" aria-hidden="true" @if($heroImage) style="background-image: url('{{ $heroImage }}'); background-size: cover; background-position: center;" @endif>
          @if($event->floating_image)
            <img src="{{ asset('storage/' . $event->floating_image) }}" alt="Floating Animation" class="floating-image">
          @endif
          @if(!$heroImage)
          <div class="overlay-card">
            <strong>Rute Kota Ikonik</strong>
            <p style="margin:0.3rem 0 0;">
              Menyusuri landmark utama dengan water station setiap ±2,5 km dan medical point di titik strategis.
            </p>
          </div>
          <div class="runner-shape">
            <div class="runner-text">
              {{ strtoupper(substr($event->name, 0, 3)) }}<br />{{ $event->start_at->format('Y') }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </section>

    <!-- Fasilitas Section -->
    <section id="fasilitas" aria-labelledby="fasilitas-title">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title" id="fasilitas-title">Fasilitas Event</h2>
          <p class="section-subtitle">
            Dirancang untuk kenyamanan dan keamanan pelari, dari start hingga finish line.
          </p>
        </div>

        <div class="facility-grid">
          @php
            $facilities = $event->facilities ?? [];
          @endphp
          @if(!empty($facilities) && is_array($facilities))
            @foreach($facilities as $facility)
              <article class="facility-card">
                <h3>{{ $facility['name'] ?? 'Fasilitas' }}</h3>
                <p>{!! nl2br(e($facility['description'] ?? 'Deskripsi fasilitas.')) !!}</p>
              </article>
            @endforeach
          @else
            <!-- Default facilities jika belum ada data -->
            <article class="facility-card">
              <h3>Race Pack Lengkap</h3>
              <p>Jersey lari, BIB dengan timing chip, tas race pack, dan panduan peserta dalam bentuk digital.</p>
            </article>
            <article class="facility-card">
              <h3>Hydration &amp; Energy Station</h3>
              <p>Air mineral dan isotonic setiap ±2,5 km, ditambah buah dan snack di area finish.</p>
            </article>
            <article class="facility-card">
              <h3>Keamanan &amp; Medis</h3>
              <p>Tim medis, ambulans, marshal, serta koordinasi dengan kepolisian dan dinas perhubungan.</p>
            </article>
            <article class="facility-card">
              <h3>Bag Drop Area</h3>
              <p>Area penitipan barang resmi dengan sistem penandaan BIB dan nomor loker.</p>
            </article>
            <article class="facility-card">
              <h3>Live Timing &amp; E-Certificate</h3>
              <p>Tersedia hasil waktu lari online dan sertifikat digital yang dapat diunduh.</p>
            </article>
            <article class="facility-card">
              <h3>Entertainment &amp; Photo Spot</h3>
              <p>Music performance, MC, dan photo wall resmi untuk dokumentasi pribadi maupun tim.</p>
            </article>
          @endif
        </div>
      </div>
    </section>

    <!-- Jersey & Medal Section -->
    <section aria-labelledby="jersey-title">
      <div class="container two-column">
        <!-- Jersey -->
        <article id="jersey" class="card">
          <h3 id="jersey-title">Jersey Resmi {{ $event->name }}</h3>
          <p>
            Desain jersey terinspirasi dengan kombinasi cutting atletik yang nyaman
            untuk lari jarak pendek maupun menengah.
          </p>
          <div class="tag-row">
            <span class="tag">Bahan quick-dry</span>
            <span class="tag">Unisex cutting</span>
            @php
              $jerseySizes = $event->jersey_sizes ?? [];
            @endphp
            @if(!empty($jerseySizes) && is_array($jerseySizes))
              <span class="tag">Size {{ implode(', ', $jerseySizes) }}</span>
            @else
              <span class="tag">Size XS–XXL</span>
            @endif
            <span class="tag">Full sublimation</span>
          </div>
          <!-- Gambar jersey -->
          <div style="margin-top:1rem;">
            @if($event->jersey_image)
              <img src="{{ asset('storage/' . $event->jersey_image) }}" alt="Desain jersey resmi {{ $event->name }}" style="width: 100%; border-radius: 0.5rem;" />
            @else
              <img src="https://via.placeholder.com/640x360?text=Desain+Jersey+{{ urlencode($event->name) }}" alt="Mockup desain jersey resmi {{ $event->name }}" />
            @endif
          </div>
          <p style="font-size:0.85rem;color:var(--muted);margin-top:0.75rem;">
            *Ilustrasi desain. Detail final dapat sedikit menyesuaikan produksi tanpa mengurangi kualitas.
          </p>
        </article>

        <!-- Medali -->
        <article id="medali" class="card">
          <h3>Medali Finisher Eksklusif</h3>
          <p>
            Setiap finisher akan mendapatkan medali dengan desain edisi {{ $event->start_at->format('Y') }} yang hanya diproduksi sekali,
            menjadikannya koleksi bernilai untuk perjalanan lari kamu.
          </p>
          <div class="tag-row">
            <span class="tag">Full metal casting</span>
            <span class="tag">Lanyard custom</span>
            <span class="tag">Kategori {{ $categoryNames ?: 'Tersedia' }}</span>
          </div>
          <!-- Gambar medali -->
          <div style="margin-top:1rem;">
            @if($event->medal_image)
              <img src="{{ asset('storage/' . $event->medal_image) }}" alt="Desain medali finisher {{ $event->name }}" style="width: 100%; border-radius: 0.5rem;" />
            @else
              <img src="https://via.placeholder.com/640x360?text=Desain+Medali+Finisher+{{ urlencode($event->name) }}" alt="Mockup desain medali finisher {{ $event->name }}" />
            @endif
          </div>
          <p style="font-size:0.85rem;color:var(--muted);margin-top:0.75rem;">
            *Medali diberikan untuk seluruh kategori yang menyelesaikan lomba sesuai cut-off time (COT).
          </p>
        </article>
      </div>
    </section>

    <!-- Deskripsi Lengkap Section -->
    @if($event->full_description)
    <section aria-labelledby="deskripsi-title">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title" id="deskripsi-title">Tentang Event</h2>
        </div>
        <div class="full-description" style="max-width: 900px; margin: 0 auto; text-align: left;">
          {!! $event->full_description !!}
        </div>
      </div>
    </section>
    @endif

    <!-- Registrasi Section -->
    <section id="registrasi" aria-labelledby="registrasi-title">
      <div class="container form-grid">
        <div>
          <div class="section-header" style="text-align:left;margin-bottom:1.5rem;">
            <h2 class="section-title" id="registrasi-title">Form Registrasi Peserta</h2>
            <p class="section-subtitle">
              Lengkapi data di bawah dengan benar untuk memperlancar proses verifikasi dan pengambilan race pack.
            </p>
          </div>

          @php
            $now = now();
            $isRegistrationOpen = true;
            $registrationMessage = null;
            
            if ($event->registration_open_at && $now < $event->registration_open_at) {
              $isRegistrationOpen = false;
              $registrationMessage = 'Registrasi akan dibuka pada ' . $event->registration_open_at->format('d F Y, H:i') . ' WIB.';
            } elseif ($event->registration_close_at && $now > $event->registration_close_at) {
              $isRegistrationOpen = false;
              $registrationMessage = 'Registrasi telah ditutup pada ' . $event->registration_close_at->format('d F Y, H:i') . ' WIB.';
            }
          @endphp

          @if(!$isRegistrationOpen)
            <div style="padding: 2rem; background: #fff3cd; border: 2px solid #ffc107; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
              <h3 style="margin-top: 0; color: #856404;">Registrasi Belum/Tidak Tersedia</h3>
              <p style="margin: 0.5rem 0 0; color: #856404; font-size: 1.1rem;">{{ $registrationMessage }}</p>
            </div>
          @endif

          @if(session('success') || session('snap_token'))
            <div style="padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 0.5rem; margin-bottom: 1.5rem; color: #155724;">
              <strong>Registrasi Berhasil!</strong>
              @if(session('snap_token'))
                <p style="margin: 0.5rem 0 0;">Silakan lanjutkan ke halaman pembayaran.</p>
              @endif
            </div>
          @endif

          @if(request('payment') == 'success')
            <div style="padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 0.5rem; margin-bottom: 1.5rem; color: #155724;">
              <strong>Pembayaran Berhasil!</strong>
              <p style="margin: 0.5rem 0 0;">Terima kasih. Pembayaran Anda telah dikonfirmasi. Silakan cek email untuk informasi lebih lanjut.</p>
            </div>
          @endif

          @if(request('payment') == 'pending')
            <div style="padding: 1rem; background: #fff3cd; border: 1px solid #ffc107; border-radius: 0.5rem; margin-bottom: 1.5rem; color: #856404;">
              <strong>Pembayaran Pending</strong>
              <p style="margin: 0.5rem 0 0;">Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran sesuai instruksi yang diberikan.</p>
            </div>
          @endif

          @if($errors->any())
            <div style="padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.5rem; margin-bottom: 1.5rem; color: #721c24;">
              <strong>Terjadi Kesalahan:</strong>
              <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" style="{{ !$isRegistrationOpen ? 'display: none;' : '' }}">
            @csrf

            <!-- PIC Information -->
            <div class="card" style="margin-bottom: 1.5rem;">
              <h3 style="margin-top: 0;">Data PIC (Penanggung Jawab)</h3>
              <div class="form-row form-row-2">
                <div>
                  <label for="pic_name">Nama Lengkap <span style="color: var(--primary);">*</span></label>
                  <input type="text" id="pic_name" name="pic_name" value="{{ old('pic_name') }}" required>
                </div>
                <div>
                  <label for="pic_email">Email <span style="color: var(--primary);">*</span></label>
                  <input type="email" id="pic_email" name="pic_email" value="{{ old('pic_email') }}" required>
                </div>
                <div>
                  <label for="pic_phone">No. Telepon <span style="color: var(--primary);">*</span></label>
                  <input type="text" id="pic_phone" name="pic_phone" value="{{ old('pic_phone') }}" required>
                </div>
              </div>
            </div>

            <!-- Participants Section -->
            <div class="card" style="margin-bottom: 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin-top: 0;">Data Peserta</h3>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; cursor: pointer; margin: 0;">
                    <input type="checkbox" id="enableBulkAttendee" style="width: auto; margin: 0;">
                    <span>Enable Bulk Attendee</span>
                  </label>
                  <button type="button" class="btn btn-outline" id="addParticipant" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    + Tambah Peserta
                  </button>
                </div>
              </div>

              <!-- Bulk Attendee Input -->
              <div id="bulkAttendeeSection" style="display: none; margin-bottom: 1rem; padding: 1rem; background: #f0f4f8; border-radius: 0.5rem; border: 1px solid rgba(0,0,0,0.1);">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary);">
                  Input Banyak Peserta Sekaligus
                </label>
                <textarea id="bulkAttendeeInput" rows="8" placeholder="Format: nama,email,phone,id_card,category_id,jersey_size,target_time&#10;&#10;Contoh:&#10;John Doe,john@example.com,081234567890,1234567890123456,1,M,02:30:00&#10;Jane Doe,jane@example.com,081234567891,1234567890123457,1,L,02:45:00&#10;&#10;Catatan:&#10;- category_id: ID kategori dari dropdown di bawah&#10;- jersey_size: XS, S, M, L, XL, XXL (opsional)&#10;- target_time: format HH:MM:SS (opsional)" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(0,0,0,0.16); font-family: monospace; font-size: 0.85rem;"></textarea>
                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; align-items: center;">
                  <button type="button" id="parseBulkAttendee" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    Parse & Tambahkan Peserta
                  </button>
                  <small style="color: var(--muted); font-size: 0.75rem;">
                    Minimal 2 peserta untuk bulk mode
                  </small>
                </div>
                <div id="bulkAttendeeMessage" style="margin-top: 0.5rem; font-size: 0.85rem;"></div>
              </div>

              <div id="participantsWrapper">
                <!-- Participant 1 -->
                <div class="participant-item" data-index="0" style="border: 1px solid rgba(0,0,0,0.1); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; background: #f9fafb; position: relative;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <strong style="color: var(--secondary);">Peserta #1</strong>
                    <button type="button" class="remove-participant" style="display: none; background: var(--primary); color: white; border: none; border-radius: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; cursor: pointer;">Hapus</button>
                  </div>
                  <div class="form-row form-row-2">
                    <div>
                      <label>Nama Lengkap <span style="color: var(--primary);">*</span></label>
                      <input type="text" name="participants[0][name]" value="{{ old('participants.0.name') }}" required>
                    </div>
                    <div>
                      <label>Email <span style="color: var(--primary);">*</span></label>
                      <input type="email" name="participants[0][email]" value="{{ old('participants.0.email') }}" required>
                    </div>
                    <div>
                      <label>No. Telepon <span style="color: var(--primary);">*</span></label>
                      <input type="text" name="participants[0][phone]" value="{{ old('participants.0.phone') }}" required>
                    </div>
                    <div>
                      <label>No. KTP/NIK <span style="color: var(--primary);">*</span></label>
                      <input type="text" name="participants[0][id_card]" value="{{ old('participants.0.id_card') }}" required>
                    </div>
                    <div>
                      <label>Kategori Lari <span style="color: var(--primary);">*</span></label>
                      <select name="participants[0][category_id]" class="category-select" data-index="0" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                          <option value="{{ $category->id }}" 
                            data-price-early="{{ $category->price_early ?? 0 }}"
                            data-price-regular="{{ $category->price_regular ?? 0 }}"
                            data-price-late="{{ $category->price_late ?? 0 }}"
                            data-reg-start="{{ $category->reg_start_at ? $category->reg_start_at->format('Y-m-d H:i:s') : '' }}"
                            data-reg-end="{{ $category->reg_end_at ? $category->reg_end_at->format('Y-m-d H:i:s') : '' }}"
                            data-quota="{{ $category->quota ?? 0 }}"
                            {{ old('participants.0.category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}@if($category->distance_km) ({{ $category->distance_km }} km)@endif
                          </option>
                        @endforeach
                      </select>
                      <small class="category-info" data-index="0" style="display: block; margin-top: 0.25rem; font-size: 0.75rem; color: var(--muted);"></small>
                    </div>
                    <div>
                      <label>Ukuran Jersey</label>
                      <select name="participants[0][jersey_size]">
                        <option value="">Pilih Ukuran</option>
                        @php
                          $availableJerseySizes = $event->jersey_sizes ?? ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                          $jerseySizeLabels = ['XS' => 'Extra Small', 'S' => 'Small', 'M' => 'Medium', 'L' => 'Large', 'XL' => 'Extra Large', 'XXL' => 'Double Extra Large'];
                        @endphp
                        @foreach($availableJerseySizes as $size)
                          <option value="{{ $size }}" {{ old('participants.0.jersey_size') == $size ? 'selected' : '' }}>{{ $size }}@if(isset($jerseySizeLabels[$size])) - {{ $jerseySizeLabels[$size] }}@endif</option>
                        @endforeach
                      </select>
                    </div>
                    <div>
                      <label>Target Waktu (opsional)</label>
                      <input type="time" name="participants[0][target_time]" value="{{ old('participants.0.target_time') }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Coupon Section -->
            <div class="card" style="margin-bottom: 1.5rem;">
              <h3 style="margin-top: 0;">Kode Kupon (Opsional)</h3>
              @if($event->promo_code)
                <div style="padding: 0.75rem; background: #e7f3ff; border-radius: 0.5rem; margin-bottom: 0.75rem; border: 1px solid #b3d9ff;">
                  <small style="color: var(--muted);">Kode promo event: </small>
                  <strong style="color: var(--primary);">{{ $event->promo_code }}</strong>
                </div>
              @endif
              <div style="display: flex; gap: 0.5rem;">
                <input type="text" id="coupon_code" name="coupon_code" value="{{ old('coupon_code', $event->promo_code) }}" placeholder="Masukkan kode kupon" style="flex: 1;">
                <button type="button" id="applyCoupon" class="btn btn-outline">Apply</button>
              </div>
              <div id="couponMessage" style="margin-top: 0.5rem; font-size: 0.85rem;"></div>
              <input type="hidden" id="applied_coupon_id" name="applied_coupon_id" value="">
            </div>

            <!-- Price Summary -->
            <div class="card" style="margin-bottom: 1.5rem; background: var(--secondary); color: white;">
              <h3 style="margin-top: 0; color: white;">Ringkasan Pembayaran</h3>
              <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Subtotal:</span>
                <span id="subtotal">Rp 0</span>
              </div>
              <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #90caf9;" id="discountRow" style="display: none;">
                <span>Diskon:</span>
                <span id="discountAmount">Rp 0</span>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 700; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.2);">
                <span>Total:</span>
                <span id="totalAmount">Rp 0</span>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;" id="submitBtn">
              Lanjutkan ke Pembayaran
            </button>
          </form>
        </div>

        <!-- Side info -->
        <aside aria-label="Informasi biaya dan timeline">
          <div class="card">
            <h3>Biaya Pendaftaran &amp; Timeline</h3>
            <p style="font-size:0.9rem;">
              Informasi biaya pendaftaran berdasarkan kategori:
            </p>
            <ul style="padding-left:1.1rem;font-size:0.9rem;color:var(--muted);">
              @forelse($categories as $category)
              <li style="margin-bottom: 0.5rem;">
                <strong>{{ $category->name }}:</strong>
                @if($category->price_early)
                  Early: Rp {{ number_format($category->price_early, 0, ',', '.') }}
                @endif
                @if($category->price_regular)
                  | Regular: Rp {{ number_format($category->price_regular, 0, ',', '.') }}
                @endif
                @if($category->price_late)
                  | Late: Rp {{ number_format($category->price_late, 0, ',', '.') }}
                @endif
                @if($category->reg_start_at && $category->reg_end_at)
                  <br><small>({{ $category->reg_start_at->format('d M Y') }} - {{ $category->reg_end_at->format('d M Y') }})</small>
                @endif
              </li>
              @empty
              <li>Informasi harga akan segera diumumkan</li>
              @endforelse
            </ul>
            <p style="font-size:0.85rem;color:var(--muted);">
              *Biaya sudah termasuk jersey, BIB, medali finisher, dan fasilitas standar event.
            </p>
          </div>
        </aside>
      </div>
    </section>

    <!-- Lokasi & Save Date Section -->
    <section id="lokasi" aria-labelledby="lokasi-title">
      <div class="container map-save-grid">
        <div>
          <div class="section-header" style="text-align:left;margin-bottom:1rem;">
            <h2 class="section-title" id="lokasi-title">Lokasi &amp; Rute Start</h2>
            <p class="section-subtitle">
              Titik kumpul dan start/finish berada di {{ $event->location_name }}@if($event->location_address), {{ $event->location_address }}@endif.
            </p>
          </div>

          <div class="map-wrapper" aria-label="Peta lokasi event">
            @if($event->map_embed_url)
              {!! $event->map_embed_url !!}
            @else
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.1006816348025!2d106.802!3d-6.2275!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14f0d5f9ff9%3A0x50e5afc1c8f7b0a1!2sGelora%20Bung%20Karno!5e0!3m2!1sid!2sid!4v0000000000000"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
            @endif
          </div>
        </div>

        <aside aria-label="Simpan tanggal & informasi kedatangan">
          <div class="save-date-card">
            <h3>Simpan Tanggal di Kalender</h3>
            <p class="save-date-info">
              Pastikan kamu tidak ketinggalan lomba. Tambahkan jadwal {{ $event->name }} ke Google Calendar.
            </p>

            <div class="pill-list">
              <span class="pill">📅 {{ $event->start_at->format('d F Y') }}</span>
              <span class="pill">⏰ {{ $event->start_at->format('H.i') }}@if($event->end_at) – {{ $event->end_at->format('H.i') }}@endif WIB</span>
              <span class="pill">📍 {{ $event->location_name }}</span>
            </div>

            @php
              $googleCalendarUrl = $event->google_calendar_url;
              if (!$googleCalendarUrl && $event->start_at) {
                $startDate = $event->start_at->utc()->format('Ymd\THis\Z');
                $endDate = ($event->end_at ? $event->end_at : $event->start_at->copy()->addHours(3))->utc()->format('Ymd\THis\Z');
                $title = urlencode($event->name);
                $details = urlencode($event->short_description ?? $event->name);
                $location = urlencode($event->location_name . ($event->location_address ? ', ' . $event->location_address : ''));
                $googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$startDate}/{$endDate}&details={$details}&location={$location}&sf=true&output=xml";
              }
            @endphp

            @if($googleCalendarUrl)
            <a
              class="btn btn-primary"
              href="{{ $googleCalendarUrl }}"
              target="_blank"
              rel="noopener"
            >
              Tambahkan ke Google Calendar
            </a>
            @endif

            <p class="form-helper" style="margin-top:0.75rem;">
              Disarankan datang minimal 60 menit sebelum flag-off untuk pemanasan dan persiapan start.
            </p>

            <h4 style="margin-top:1.25rem;font-size:1rem;color:var(--secondary);">Tips Kedatangan</h4>
            <ul style="padding-left:1.1rem;font-size:0.9rem;color:var(--muted);margin-top:0.4rem;">
              <li>Gunakan transportasi umum (MRT, TransJakarta) untuk menghindari kemacetan.</li>
              <li>Siapkan identitas dan email konfirmasi saat pengambilan race pack.</li>
              <li>Gunakan sepatu lari yang sudah pernah dipakai latihan, bukan baru 100%.</li>
            </ul>
          </div>
        </aside>
      </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" aria-labelledby="faq-title">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title" id="faq-title">Pertanyaan yang Sering Diajukan (FAQ)</h2>
          <p class="section-subtitle">
            Beberapa pertanyaan umum tentang {{ $event->name }}. Sesuaikan isi ini dengan ketentuan resmi event kamu.
          </p>
        </div>

        <div class="faq-list">
          <details>
            <summary>Bagaimana cara mendaftar {{ $event->name }}?</summary>
            <p>
              Peserta dapat mendaftar melalui tombol registrasi di halaman ini. Setelah mengisi form, sistem akan mengirimkan
              email berisi instruksi pembayaran. Pendaftaran dianggap sah setelah pembayaran terverifikasi.
            </p>
          </details>

          <details>
            <summary>Apakah biaya pendaftaran dapat dikembalikan?</summary>
            <p>
              Secara umum, biaya pendaftaran tidak dapat dikembalikan. Namun, panitia dapat membuka opsi pengalihan
              kepesertaan/penggantian nama sesuai kebijakan dan periode yang ditentukan.
            </p>
          </details>

          <details>
            <summary>Kapan dan di mana pengambilan race pack?</summary>
            <p>
              Pengambilan race pack biasanya dilakukan H-1 hingga H-2 di lokasi yang akan diumumkan melalui email resmi
              dan media sosial. Peserta wajib membawa identitas dan bukti pendaftaran/pembayaran.
            </p>
          </details>

          <details>
            <summary>Apakah ada syarat kesehatan untuk mengikuti lomba?</summary>
            <p>
              Peserta disarankan dalam kondisi sehat dan telah melakukan latihan yang cukup. Untuk peserta dengan
              penyakit tertentu, konsultasikan terlebih dahulu dengan dokter. Panitia berhak menolak peserta yang tidak
              memenuhi kriteria kesehatan di hari lomba.
            </p>
          </details>

          <details>
            <summary>Apakah tersedia kategori untuk pelari pemula?</summary>
            <p>
              Ya. Kategori dengan jarak pendek sangat cocok untuk pemula yang baru ingin mencoba mengikuti event lari resmi dengan
              atmosfer yang aman dan menyenangkan.
            </p>
          </details>
        </div>
      </div>
    </section>
  </main>

  <footer aria-label="Footer">
    <div class="container footer-grid">
      <div>
        <div>&copy; {{ date('Y') }} {{ $event->name }}. Seluruh hak cipta dilindungi.</div>
        <div style="margin-top:0.3rem;">
          Diselenggarakan oleh <strong>@if($event->user){{ $event->user->name }}@else{{ config('app.name') }}@endif</strong> bekerja sama dengan partner dan sponsor resmi.
        </div>
      </div>
      <div class="footer-links">
        <a href="#top">Kembali ke atas</a>
        <a href="#!" rel="nofollow">Syarat &amp; Ketentuan</a>
        <a href="#!" rel="nofollow">Kebijakan Privasi</a>
        @if($event->user && $event->user->email)
        <a href="mailto:{{ $event->user->email }}">Kontak Panitia</a>
        @endif
      </div>
    </div>
  </footer>

  <script>
    // Simple mobile nav toggle
    const nav = document.getElementById("navbar");
    const toggle = document.getElementById("navToggle");

    toggle.addEventListener("click", () => {
      nav.classList.toggle("nav-open");
    });

    // Optional: smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener("click", function (e) {
        const targetId = this.getAttribute("href");
        if (targetId.length > 1) {
          e.preventDefault();
          const target = document.querySelector(targetId);
          if (target) {
            window.scrollTo({
              top: target.offsetTop - 80,
              behavior: "smooth"
            });
          }
        }
      });
    });

    // Registration Form JavaScript
    (function() {
      let participantIndex = 1;
      const participantsWrapper = document.getElementById('participantsWrapper');
      const addParticipantBtn = document.getElementById('addParticipant');
      const template = participantsWrapper.querySelector('.participant-item').cloneNode(true);
      const eventId = {{ $event->id }};
      const eventSlug = '{{ $event->slug }}';
      let appliedCoupon = null;

      // Bulk Attendee Toggle
      const enableBulkAttendee = document.getElementById('enableBulkAttendee');
      const bulkAttendeeSection = document.getElementById('bulkAttendeeSection');
      
      if (enableBulkAttendee && bulkAttendeeSection) {
        enableBulkAttendee.addEventListener('change', function() {
          bulkAttendeeSection.style.display = this.checked ? 'block' : 'none';
          if (this.checked) {
            participantsWrapper.style.opacity = '0.5';
          } else {
            participantsWrapper.style.opacity = '1';
          }
        });
      }

      // Parse Bulk Attendee
      const parseBulkAttendeeBtn = document.getElementById('parseBulkAttendee');
      const bulkAttendeeInput = document.getElementById('bulkAttendeeInput');
      const bulkAttendeeMessage = document.getElementById('bulkAttendeeMessage');

      if (parseBulkAttendeeBtn && bulkAttendeeInput) {
        parseBulkAttendeeBtn.addEventListener('click', function() {
          const input = bulkAttendeeInput.value.trim();
          if (!input) {
            bulkAttendeeMessage.textContent = 'Masukkan data peserta terlebih dahulu';
            bulkAttendeeMessage.style.color = 'var(--primary)';
            return;
          }

          const lines = input.split('\n').filter(line => line.trim());
          if (lines.length < 2) {
            bulkAttendeeMessage.textContent = 'Minimal 2 peserta untuk bulk mode';
            bulkAttendeeMessage.style.color = 'var(--primary)';
            return;
          }

          let successCount = 0;
          let errorCount = 0;
          const errors = [];

          // Clear existing participants (keep first one as template)
          const existingParticipants = participantsWrapper.querySelectorAll('.participant-item');
          existingParticipants.forEach((item, index) => {
            if (index > 0) {
              item.remove();
            }
          });
          participantIndex = 1;

          lines.forEach((line, lineIndex) => {
            const parts = line.split(',').map(p => p.trim());
            if (parts.length < 4) {
              errorCount++;
              errors.push(`Baris ${lineIndex + 1}: Format tidak valid (minimal: nama,email,phone,id_card)`);
              return;
            }

            const [name, email, phone, idCard, categoryId, jerseySize, targetTime] = parts;

            if (!name || !email || !phone || !idCard) {
              errorCount++;
              errors.push(`Baris ${lineIndex + 1}: Data tidak lengkap`);
              return;
            }

            // Create new participant item
            const newParticipant = template.cloneNode(true);
            const currentIndex = participantIndex++;

            // Fill in the data
            newParticipant.querySelector('input[name*="[name]"]').value = name;
            newParticipant.querySelector('input[name*="[email]"]').value = email;
            newParticipant.querySelector('input[name*="[phone]"]').value = phone;
            newParticipant.querySelector('input[name*="[id_card]"]').value = idCard;
            
            if (categoryId) {
              const categorySelect = newParticipant.querySelector('.category-select');
              if (categorySelect) {
                categorySelect.value = categoryId;
                categorySelect.dispatchEvent(new Event('change'));
              }
            }

            if (jerseySize) {
              const jerseySelect = newParticipant.querySelector('select[name*="[jersey_size]"]');
              if (jerseySelect) {
                jerseySelect.value = jerseySize;
              }
            }

            if (targetTime) {
              const targetTimeInput = newParticipant.querySelector('input[name*="[target_time]"]');
              if (targetTimeInput) {
                targetTimeInput.value = targetTime;
              }
            }

            // Update all input names
            newParticipant.querySelectorAll('input, select').forEach(input => {
              const name = input.getAttribute('name');
              if (name) {
                const newName = name.replace(/participants\[\d+\]/, `participants[${currentIndex}]`);
                input.setAttribute('name', newName);
              }
            });

            // Update data-index
            newParticipant.setAttribute('data-index', currentIndex);
            newParticipant.querySelector('strong').textContent = `Peserta #${currentIndex + 1}`;
            
            // Update category select data-index
            const categorySelect = newParticipant.querySelector('.category-select');
            if (categorySelect) {
              categorySelect.setAttribute('data-index', currentIndex);
              categorySelect.addEventListener('change', handleCategoryChange);
            }

            // Show remove button
            const removeBtn = newParticipant.querySelector('.remove-participant');
            if (removeBtn) {
              removeBtn.style.display = 'block';
            }

            participantsWrapper.appendChild(newParticipant);
            successCount++;
          });

          // Show result message
          if (successCount > 0) {
            bulkAttendeeMessage.textContent = `Berhasil menambahkan ${successCount} peserta${errorCount > 0 ? `. ${errorCount} error.` : ''}`;
            bulkAttendeeMessage.style.color = errorCount > 0 ? 'orange' : '#28a745';
            if (errors.length > 0) {
              bulkAttendeeMessage.innerHTML += '<br><small>' + errors.slice(0, 3).join('<br>') + (errors.length > 3 ? '...' : '') + '</small>';
            }
            bulkAttendeeInput.value = '';
            updatePriceSummary();
            updateParticipantNumbers();
          } else {
            bulkAttendeeMessage.textContent = 'Gagal menambahkan peserta. ' + (errors[0] || 'Format tidak valid');
            bulkAttendeeMessage.style.color = 'var(--primary)';
          }
        });
      }

      // Add participant
      if (addParticipantBtn) {
        addParticipantBtn.addEventListener('click', function() {
          const newParticipant = template.cloneNode(true);
          const currentIndex = participantIndex++;
          
          // Update all input names
          newParticipant.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
              const newName = name.replace(/participants\[\d+\]/, `participants[${currentIndex}]`);
              input.setAttribute('name', newName);
              if (input.type !== 'hidden') {
                input.value = '';
              }
            }
          });

          // Update data-index
          newParticipant.setAttribute('data-index', currentIndex);
          newParticipant.querySelector('strong').textContent = `Peserta #${currentIndex + 1}`;
          
          // Update category select data-index
          const categorySelect = newParticipant.querySelector('.category-select');
          if (categorySelect) {
            categorySelect.setAttribute('data-index', currentIndex);
            categorySelect.addEventListener('change', handleCategoryChange);
          }

          // Show remove button
          const removeBtn = newParticipant.querySelector('.remove-participant');
          if (removeBtn) {
            removeBtn.style.display = 'block';
          }

          participantsWrapper.appendChild(newParticipant);
          updatePriceSummary();
        });
      }

      // Remove participant (event delegation)
      if (participantsWrapper) {
        participantsWrapper.addEventListener('click', function(e) {
          if (e.target.classList.contains('remove-participant') || e.target.closest('.remove-participant')) {
            const participantItem = e.target.closest('.participant-item');
            const allParticipants = participantsWrapper.querySelectorAll('.participant-item');
            
            if (allParticipants.length <= 1) {
              alert('Minimal 1 peserta harus tersisa');
              return;
            }
            
            participantItem.remove();
            updatePriceSummary();
            updateParticipantNumbers();
          }
        });
      }

      // Update participant numbers
      function updateParticipantNumbers() {
        const items = participantsWrapper.querySelectorAll('.participant-item');
        items.forEach((item, index) => {
          item.querySelector('strong').textContent = `Peserta #${index + 1}`;
        });
      }

      // Handle category change
      function handleCategoryChange(e) {
        const select = e.target;
        const index = select.getAttribute('data-index');
        const option = select.options[select.selectedIndex];
        const infoEl = document.querySelector(`.category-info[data-index="${index}"]`);
        
        if (option.value && infoEl) {
          const priceEarly = parseFloat(option.getAttribute('data-price-early') || 0);
          const priceRegular = parseFloat(option.getAttribute('data-price-regular') || 0);
          const priceLate = parseFloat(option.getAttribute('data-price-late') || 0);
          const regStart = option.getAttribute('data-reg-start');
          const regEnd = option.getAttribute('data-reg-end');
          const quota = parseInt(option.getAttribute('data-quota') || 0);

          // Calculate current price based on date
          let currentPrice = priceRegular;
          const now = new Date();
          
          if (regStart && regEnd) {
            const startDate = new Date(regStart);
            const endDate = new Date(regEnd);
            
            if (now < startDate) {
              infoEl.textContent = 'Registrasi belum dibuka';
              infoEl.style.color = 'var(--muted)';
            } else if (now >= startDate && now < endDate) {
              currentPrice = priceEarly || priceRegular;
            } else {
              currentPrice = priceLate || priceRegular;
            }
          } else {
            currentPrice = priceEarly || priceRegular;
          }

          if (quota > 0) {
            infoEl.textContent = `Harga: Rp ${formatCurrency(currentPrice)} | Kuota: ${quota}`;
          } else {
            infoEl.textContent = `Harga: Rp ${formatCurrency(currentPrice)}`;
          }
          infoEl.style.color = 'var(--secondary)';
        } else if (infoEl) {
          infoEl.textContent = '';
        }

        // Check quota
        checkQuota([option.value]);
        updatePriceSummary();
      }

      // Attach category change handlers
      document.querySelectorAll('.category-select').forEach(select => {
        select.addEventListener('change', handleCategoryChange);
      });

      // Check quota via AJAX
      function checkQuota(categoryIds) {
        if (!categoryIds || categoryIds.length === 0 || !categoryIds[0]) return;

        fetch(`/events/${eventSlug}/register/quota`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({ category_ids: categoryIds })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.quotas) {
            Object.keys(data.quotas).forEach(categoryId => {
              const quota = data.quotas[categoryId];
              const selects = document.querySelectorAll(`.category-select option[value="${categoryId}"]`);
              selects.forEach(option => {
                if (quota.is_sold_out) {
                  option.textContent += ' (HABIS)';
                  option.disabled = true;
                }
              });
            });
          }
        })
        .catch(error => {
          console.error('Error checking quota:', error);
        });
      }

      // Apply coupon
      const applyCouponBtn = document.getElementById('applyCoupon');
      const couponCodeInput = document.getElementById('coupon_code');
      const couponMessage = document.getElementById('couponMessage');

      if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', function() {
          const code = couponCodeInput.value.trim();
          if (!code) {
            couponMessage.textContent = 'Masukkan kode kupon';
            couponMessage.style.color = 'var(--primary)';
            return;
          }

          // Calculate subtotal first
          const subtotal = calculateSubtotal();
          if (subtotal === 0) {
            couponMessage.textContent = 'Pilih kategori terlebih dahulu';
            couponMessage.style.color = 'var(--primary)';
            return;
          }

          fetch(`/events/${eventSlug}/register/coupon`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
              event_id: eventId,
              coupon_code: code,
              total_amount: subtotal
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              appliedCoupon = data.coupon;
              document.getElementById('applied_coupon_id').value = data.coupon.id;
              couponMessage.textContent = `Kupon berhasil diterapkan! Diskon: Rp ${formatCurrency(data.discount_amount)}`;
              couponMessage.style.color = '#28a745';
              updatePriceSummary();
            } else {
              appliedCoupon = null;
              document.getElementById('applied_coupon_id').value = '';
              couponMessage.textContent = data.message || 'Kode kupon tidak valid';
              couponMessage.style.color = 'var(--primary)';
              updatePriceSummary();
            }
          })
          .catch(error => {
            console.error('Error applying coupon:', error);
            couponMessage.textContent = 'Terjadi kesalahan saat memproses kupon';
            couponMessage.style.color = 'var(--primary)';
          });
        });
      }

      // Calculate price based on category and date
      function getCategoryPrice(categoryId) {
        const select = document.querySelector(`.category-select option[value="${categoryId}"]`);
        if (!select) return 0;

        const priceEarly = parseFloat(select.getAttribute('data-price-early') || 0);
        const priceRegular = parseFloat(select.getAttribute('data-price-regular') || 0);
        const priceLate = parseFloat(select.getAttribute('data-price-late') || 0);
        const regStart = select.getAttribute('data-reg-start');
        const regEnd = select.getAttribute('data-reg-end');

        const now = new Date();
        
        if (regStart && regEnd) {
          const startDate = new Date(regStart);
          const endDate = new Date(regEnd);
          
          if (now < startDate) {
            return 0; // Not open yet
          } else if (now >= startDate && now < endDate) {
            return priceEarly || priceRegular;
          } else {
            return priceLate || priceRegular;
          }
        } else {
          return priceEarly || priceRegular;
        }
      }

      // Calculate subtotal
      function calculateSubtotal() {
        let total = 0;
        document.querySelectorAll('.category-select').forEach(select => {
          if (select.value) {
            total += getCategoryPrice(select.value);
          }
        });
        return total;
      }

      // Update price summary
      function updatePriceSummary() {
        const subtotal = calculateSubtotal();
        let discount = 0;
        let total = subtotal;

        if (appliedCoupon && subtotal > 0) {
          // Re-apply coupon with new subtotal
          fetch(`/events/${eventSlug}/register/coupon`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
              event_id: eventId,
              coupon_code: appliedCoupon.code,
              total_amount: subtotal
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              discount = data.discount_amount;
              total = data.final_amount;
              updatePriceDisplay(subtotal, discount, total);
            } else {
              updatePriceDisplay(subtotal, 0, subtotal);
            }
          })
          .catch(() => {
            updatePriceDisplay(subtotal, 0, subtotal);
          });
        } else {
          updatePriceDisplay(subtotal, 0, subtotal);
        }
      }

      function updatePriceDisplay(subtotal, discount, total) {
        document.getElementById('subtotal').textContent = `Rp ${formatCurrency(subtotal)}`;
        
        const discountRow = document.getElementById('discountRow');
        if (discount > 0) {
          discountRow.style.display = 'flex';
          document.getElementById('discountAmount').textContent = `-Rp ${formatCurrency(discount)}`;
        } else {
          discountRow.style.display = 'none';
        }
        
        document.getElementById('totalAmount').textContent = `Rp ${formatCurrency(total)}`;
      }

      function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID').format(Math.round(amount));
      }

      // Form validation and submission with Midtrans popup
      const registrationForm = document.getElementById('registrationForm');
      if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
          e.preventDefault();

          const categorySelects = document.querySelectorAll('.category-select');
          let hasInvalid = false;

          categorySelects.forEach(select => {
            if (!select.value) {
              hasInvalid = true;
              select.style.borderColor = 'var(--primary)';
            } else {
              select.style.borderColor = '';
            }
          });

          if (hasInvalid) {
            alert('Harap pilih kategori untuk semua peserta');
            return false;
          }

          // Check if subtotal is valid
          const subtotal = calculateSubtotal();
          if (subtotal === 0) {
            alert('Harap pilih kategori yang valid');
            return false;
          }

          // Disable submit button
          const submitBtn = document.getElementById('submitBtn');
          const originalText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

          // Submit via AJAX
          const formData = new FormData(registrationForm);

          fetch(registrationForm.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
          })
          .then(response => {
            if (!response.ok) {
              return response.json().then(data => {
                throw new Error(data.message || data.error || 'Terjadi kesalahan');
              });
            }
            return response.json();
          })
          .then(data => {
            if (data.success && data.snap_token) {
              // Show Midtrans Snap popup
              if (typeof snap !== 'undefined') {
                snap.pay(data.snap_token, {
                  onSuccess: function(result) {
                    window.location.href = '{{ route("events.show", $event->slug) }}?payment=success';
                  },
                  onPending: function(result) {
                    window.location.href = '{{ route("events.show", $event->slug) }}?payment=pending';
                  },
                  onError: function(result) {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                  },
                  onClose: function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                  }
                });
              } else {
                alert('Midtrans SDK tidak tersedia. Silakan refresh halaman.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
              }
            } else if (data.testing_mode) {
              // Testing mode
              alert(data.message || 'Registrasi berhasil! (Testing Mode)');
              window.location.reload();
            } else {
              // Error
              alert(data.message || data.error || 'Terjadi kesalahan saat registrasi');
              submitBtn.disabled = false;
              submitBtn.innerHTML = originalText;
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan saat mengirim form. Silakan coba lagi.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          });
        });
      }

      // Initial price update
      updatePriceSummary();
    })();
  </script>
</body>
</html>
