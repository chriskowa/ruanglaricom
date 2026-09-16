@extends('layouts.pacerhub')

@section('title', 'Rekayasa Lalu Lintas Pelari: Panduan Teknis, Standar Keamanan & Simulasi Jalur Lomba | Ruang Lari')
@section('meta_title', 'Rekayasa Lalu Lintas Pelari: Panduan Teknis, Standar Keamanan & Simulasi Jalur Lomba')
@section('meta_description', 'Panduan komprehensif rekayasa lalu lintas pelari untuk race director dan event organizer. Standar kapasitas jalur, manajemen persimpangan, simulasi 4 model arus adaptif, dan SOP hari-H.')
@section('meta_keywords', 'rekayasa lalu lintas pelari, manajemen arus lomba lari, simulasi rute maraton, crowd control pelari, traffic management running event, kapasitas jalur pelari, wave start maraton, level of service pelari')
@section('canonical_url', url('/rekayasa-lalu-lintas-pelari'))
@section('og_type', 'article')

@push('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "TechArticle",
      "@id": "{{ url('/rekayasa-lalu-lintas-pelari') }}#article",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "{{ url('/') }}#website",
        "name": "Ruang Lari",
        "url": "{{ url('/') }}"
      },
      "headline": "Rekayasa Lalu Lintas Pelari: Panduan Teknis, Standar Keamanan & Simulasi Jalur Lomba",
      "description": "Pedoman teknis komprehensif rekayasa lalu lintas pelari untuk road race organizer, mencakup teori kapasitas aliran pejalan kaki, pemisahan tingkat, bundaran konsentris, serta simulasi interaktif.",
      "inLanguage": "id-ID",
      "datePublished": "2026-09-15T08:00:00+07:00",
      "dateModified": "2026-09-15T16:50:00+07:00",
      "author": {
        "@type": "Organization",
        "name": "Divisi Rekayasa Lintasan Ruang Lari",
        "url": "{{ url('/') }}"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Ruang Lari",
        "url": "{{ url('/') }}",
        "logo": {
          "@type": "ImageObject",
          "url": "{{ asset('images/logo.png') }}"
        }
      },
      "keywords": [
        "rekayasa lalu lintas pelari",
        "manajemen arus lomba lari",
        "kapasitas jalur pelari",
        "simulasi persimpangan pelari",
        "wave start maraton",
        "road race safety"
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ url('/rekayasa-lalu-lintas-pelari') }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Beranda",
          "item": "{{ url('/') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Race Management",
          "item": "{{ url('/eo') }}"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Rekayasa Lalu Lintas Pelari",
          "item": "{{ url('/rekayasa-lalu-lintas-pelari') }}"
        }
      ]
    },
    {
      "@type": "FAQPage",
      "@id": "{{ url('/rekayasa-lalu-lintas-pelari') }}#faq",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Apa yang dimaksud dengan rekayasa lalu lintas pelari dalam event lari jalan raya?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Rekayasa lalu lintas pelari adalah metodologi teknis perencanaan, perancangan geometri lintasan, dan pengendalian arus pejalan kaki cepat (pelari) serta kendaraan bermotor guna memastikan keselamatan fisik pelari, menjaga ritme lomba tanpa hambatan (zero choke-point), dan meminimalkan dampak kemacetan pada jaringan jalan perkotaan."
          }
        },
        {
          "@type": "Question",
          "name": "Berapa standar lebar jalur minimum untuk pelari menurut Highway Capacity Manual (HCM)?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Standar HCM merekomendasikan ruang minimum 1.4 hingga 2.3 meter persegi per pelari untuk mencapai Level of Service (LOS) C (kondisi arus stabil). Pada area start corral, dibutuhkan minimal 0.75 meter persegi per pelari, sedangkan pada lintasan terbuka dibutuhkan lebar jalur efektif minimal 3.5 meter untuk setiap 500 pelari aktif per menit."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara mengatasi persimpangan jalan sibuk tanpa harus menutup jalan raya total?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Race director dapat menerapkan salah satu dari 4 model rekayasa: (1) Sistem Persimpangan Bergantian Jalur (Alternating Bypass) dengan komando marshal, (2) Bundaran Aliran Bebas Konsentris (Adaptive Roundabout) yang memisahkan radius motor dan pelari, (3) Pemisahan Tingkat Vertikal (Grade Separation Overpass) berupa jembatan modular, atau (4) Sistem Katup Pintu Air Otomatis (Smart Sluice Gate) yang memanfaatkan celah alami antar-gelombang (wave interval)."
          }
        },
        {
          "@type": "Question",
          "name": "Apa fungsi penerapan Wave Start terhadap rekayasa lalu lintas pelari?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Wave start berfungsi memecah konsentrasi massa pelari (platoon dispersion) menjadi kelompok-kelompok terukur dengan jeda waktu 3 sampai 7 menit. Hal ini menjaga kepadatan jalan tetap pada LOS A atau B, mencegah penumpukan di titik sempit (bottle-neck), dan memberikan jendela waktu hijau bagi lalu lintas kendaraan publik di persimpangan kritis."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana protokol penanganan kendaraan darurat (ambulans) saat rute lari sedang aktif?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Rekayasa rute wajib menyediakan Emergency Blue Corridor selebar minimal 3 meter yang dipisahkan oleh traffic cone berat. Petugas marshal di setiap perempatan dilengkapi radio frekuensi khusus untuk menghentikan rombongan pelari secara bertahap dalam waktu 10 detik apabila sirene ambulans darurat terkonfirmasi mendekat."
          }
        },
        {
          "@type": "Question",
          "name": "Kapan pembukaan jalan kembali (road reopening) boleh dilakukan setelah race?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Pembukaan jalan dilakukan secara bertahap menggunakan metode Rolling Sweep Vehicle. Begitu kendaraan penutup (Sweeper Car) melintasi kilometer tertentu sesuai batas Cut-Off Time (COT), tim marshal dan Dishub langsung membuka kembali barikade jalan dalam waktu maksimal 15 menit pasca pelari terakhir lewat."
          }
        }
      ]
    }
  ]
}
</script>
@endpush

@push('styles')
<style>
  :root {
    --bg-main: #090A0E;
    --bg-surface: #12161F;
    --bg-surface-elevated: #181E2B;
    --bg-input: #1E2432;
    --border-dark: #1E2430;
    --border-light: #2D3748;
    --text-white: #FFFFFF;
    --text-slate-100: #F1F5F9;
    --text-slate-200: #E2E8F0;
    --text-slate-300: #CBD5E1;
    --text-slate-400: #94A3B8;
    --brand-green: #10B981;
    --brand-green-dark: #0F6E56;
    --brand-blue: #2563EB;
    --brand-orange: #F97316;
    --brand-red: #EF4444;
    --radius-sm: 4px;
    --radius-md: 6px;
    --radius-lg: 8px;
  }

  .rekayasa-page {
    background-color: var(--bg-main);
    color: var(--text-slate-200);
    line-height: 1.65;
    font-size: 0.9375rem;
    padding: 0;
    margin: 0;
  }

  .rekayasa-page a {
    color: #38BDF8;
    text-decoration: none;
    transition: color 0.15s ease;
  }

  .rekayasa-page a:hover {
    color: #7DD3FC;
  }

  .page-wrapper {
    max-width: 1040px;
    margin: 0 auto;
    padding: 20px 20px 80px;
  }

  .hero-section {
    padding: 10px 0 32px;
    border-bottom: 1px solid var(--border-dark);
    margin-bottom: 36px;
  }

  .breadcrumb-nav {
    font-size: 0.75rem;
    color: var(--text-slate-400);
    margin-bottom: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
  }

  .breadcrumb-nav span {
    color: #475569;
  }

  h1.page-title {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.25;
    color: var(--text-white);
    letter-spacing: -0.025em;
    margin-bottom: 16px;
  }

  .hero-lead {
    font-size: 1.0625rem;
    line-height: 1.6;
    color: var(--text-slate-300);
    margin-bottom: 24px;
    max-width: 900px;
  }

  .meta-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
    font-size: 0.8125rem;
    color: var(--text-slate-400);
    padding: 12px 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
  }

  .meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .meta-item strong {
    color: var(--text-slate-200);
  }

  .metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-top: 24px;
  }

  .metric-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    padding: 14px 18px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .metric-label {
    font-size: 0.6875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    color: var(--text-slate-400);
  }

  .metric-val {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-white);
    font-family: 'JetBrains Mono', monospace;
  }

  .metric-desc {
    font-size: 0.6875rem;
    color: var(--text-slate-400);
  }

  .toc-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    margin-bottom: 40px;
  }

  .toc-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-white);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .toc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 8px 24px;
  }

  .toc-link {
    font-size: 0.8125rem;
    color: var(--text-slate-300);
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
  }

  .toc-link:hover {
    color: #38BDF8;
  }

  .toc-num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.6875rem;
    font-weight: 700;
    color: #38BDF8;
    background: var(--bg-surface-elevated);
    padding: 1px 6px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-dark);
  }

  .article-section {
    margin-bottom: 56px;
  }

  .section-header {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-dark);
  }

  .section-header h2 {
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--text-white);
    letter-spacing: -0.015em;
  }

  .section-header p {
    font-size: 0.875rem;
    color: var(--text-slate-300);
    margin-top: 4px;
  }

  .prose-text {
    color: var(--text-slate-200);
    font-size: 0.9375rem;
    line-height: 1.7;
    margin-bottom: 20px;
  }

  .prose-text strong {
    color: var(--text-white);
  }

  .info-panel {
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    margin: 20px 0;
  }

  .info-panel-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-white);
    margin-bottom: 8px;
  }

  .table-container {
    width: 100%;
    overflow-x: auto;
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-lg);
    margin: 24px 0;
  }

  table.tech-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
    text-align: left;
  }

  table.tech-table th {
    background: var(--bg-surface-elevated);
    color: var(--text-white);
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-dark);
    text-transform: uppercase;
    font-size: 0.6875rem;
    letter-spacing: 0.04em;
  }

  table.tech-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-dark);
    color: var(--text-slate-200);
    vertical-align: top;
  }

  table.tech-table tr:last-child td {
    border-bottom: none;
  }

  table.tech-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
  }

  .tag-badge {
    display: inline-block;
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
  }

  .tag-green { background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3); }
  .tag-blue { background: rgba(37, 99, 235, 0.15); color: #60A5FA; border: 1px solid rgba(37, 99, 235, 0.3); }
  .tag-orange { background: rgba(249, 115, 22, 0.15); color: #FB923C; border: 1px solid rgba(249, 115, 22, 0.3); }
  .tag-purple { background: rgba(124, 58, 237, 0.15); color: #A78BFA; border: 1px solid rgba(124, 58, 237, 0.3); }
  .tag-red { background: rgba(239, 68, 68, 0.15); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.3); }

  .calc-container {
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-lg);
    padding: 24px;
    margin: 28px 0;
  }

  .calc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  .calc-inputs {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .calc-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .calc-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-slate-300);
    display: flex;
    justify-content: space-between;
  }

  .calc-input {
    background: var(--bg-input);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    color: var(--text-white);
    padding: 9px 12px;
    font-size: 0.875rem;
    font-family: 'JetBrains Mono', monospace;
    outline: none;
    transition: border-color 0.15s ease;
  }

  .calc-input:focus {
    border-color: #38BDF8;
  }

  .calc-results {
    background: var(--bg-surface-elevated);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 16px;
  }

  .result-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-dark);
  }

  .result-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .result-label {
    font-size: 0.75rem;
    color: var(--text-slate-400);
  }

  .result-val {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--text-white);
    font-family: 'JetBrains Mono', monospace;
  }

  .sim-wrapper-outer {
    margin: 40px 0;
  }

  .sim-container {
    width: 100%;
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-dark);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 24px;
    margin-bottom: 40px;
  }

  .sim-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-dark);
  }

  .sim-title-group {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .model-badge {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    background: var(--bg-surface-elevated);
    color: #38BDF8;
    border: 1px solid var(--border-dark);
    margin-bottom: 4px;
  }

  .sim-title-group h3 {
    font-size: 1.1875rem;
    font-weight: 700;
    color: var(--text-white);
    letter-spacing: -0.01em;
  }

  .sim-title-group p {
    font-size: 0.8125rem;
    color: var(--text-slate-300);
  }

  .lane-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 12px;
    border-radius: var(--radius-md);
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(16, 185, 129, 0.12);
    color: #34D399;
    border: 1px solid rgba(16, 185, 129, 0.25);
  }

  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35);
    animation: pulse 2s infinite ease-in-out;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.15); }
  }

  .telemetry-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 10px;
  }

  .telemetry-item {
    background: var(--bg-surface-elevated);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .telemetry-label {
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-slate-400);
  }

  .telemetry-val {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-white);
    font-family: 'JetBrains Mono', monospace;
  }

  .canvas-frame {
    width: 100%;
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--border-dark);
    background: #FFFFFF;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .canvas-frame svg {
    display: block;
    width: 100%;
    height: auto;
    max-height: 520px;
    user-select: none;
  }

  .sim-legend {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px 18px;
    font-size: 0.75rem;
    color: var(--text-slate-300);
    padding: 10px 14px;
    background: var(--bg-surface-elevated);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
  }

  .legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .legend-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
  }

  .ind-runner { background: #0F6E56; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #0F6E56; }
  .ind-motor-south { background: #2563EB; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #2563EB; }
  .ind-motor-north { background: #7c3aed; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #7c3aed; }
  .ind-island { background: #10B981; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #10B981; }
  .ind-lane-divider { width: 16px; height: 0px; border-top: 2px dashed #94a3b8; border-radius: 0; }
  .ind-overpass { background: #7c3aed; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #7c3aed; }
  .ind-gate-open { background: #10b981; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #10b981; }
  .ind-gate-closed { background: #ef4444; border: 1.5px solid #ffffff; box-shadow: 0 0 0 1px #ef4444; }
  .ind-sluice-chamber { background: #cbd5e1; border: 1.5px dashed #64748b; border-radius: 2px; }

  .controls-panel {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    background: var(--bg-surface-elevated);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    padding: 18px;
  }

  .control-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border-dark);
    padding-bottom: 6px;
  }

  .group-title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--text-white);
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .group-badge {
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: var(--radius-sm);
    background: var(--bg-input);
    color: var(--text-slate-300);
    border: 1px solid var(--border-dark);
  }

  .slider-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .slider-label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
  }

  .slider-title {
    font-weight: 500;
    color: var(--text-slate-200);
  }

  .slider-badge {
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    background: var(--bg-input);
    border: 1px solid var(--border-dark);
    color: var(--text-white);
    font-family: 'JetBrains Mono', monospace;
  }

  input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: #2A3447;
    outline: none;
    cursor: pointer;
  }

  input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #38BDF8;
    border: 2px solid #090A0E;
    box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    cursor: pointer;
  }

  input[type="range"].slider-runner::-webkit-slider-thumb {
    background: #10B981;
  }

  input[type="range"].slider-motor::-webkit-slider-thumb {
    background: #3B82F6;
  }

  .checkbox-field {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    cursor: pointer;
    user-select: none;
    color: var(--text-slate-300);
    margin-top: 4px;
  }

  .checkbox-field input[type="checkbox"] {
    width: 15px;
    height: 15px;
    accent-color: #10B981;
    cursor: pointer;
  }

  .actions-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: auto;
  }

  .btn-sim {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 14px;
    font-family: inherit;
    font-size: 0.8125rem;
    font-weight: 600;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-dark);
    background: var(--bg-surface-elevated);
    color: var(--text-white);
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .btn-sim:hover {
    background: #242D3D;
    border-color: #38BDF8;
  }

  .btn-sim:active {
    transform: scale(0.98);
  }

  .btn-sim-primary {
    background: #0F6E56;
    border-color: #10B981;
    color: #FFFFFF;
  }

  .btn-sim-primary:hover {
    background: #0D5C48;
  }

  .btn-sim-danger {
    color: #F87171;
    border-color: rgba(239, 68, 68, 0.3);
  }

  .btn-sim-danger:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: #EF4444;
  }

  .sim-footer {
    font-size: 0.75rem;
    color: var(--text-slate-400);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid var(--border-dark);
  }

  .faq-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .faq-item {
    background: var(--bg-surface);
    border: 1px solid var(--border-dark);
    border-radius: var(--radius-md);
    overflow: hidden;
  }

  .faq-question {
    padding: 16px 20px;
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--text-white);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
  }

  .faq-question:hover {
    background: rgba(255, 255, 255, 0.02);
  }

  .faq-answer {
    padding: 0 20px 16px;
    font-size: 0.875rem;
    color: var(--text-slate-300);
    line-height: 1.65;
  }

  @media (max-width: 768px) {
    .calc-grid {
      grid-template-columns: 1fr;
    }
    h1.page-title {
      font-size: 1.5rem;
    }
    .telemetry-bar {
      grid-template-columns: repeat(2, 1fr);
    }
    .controls-panel {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="rekayasa-page">
  <div class="page-wrapper">

    <!-- Hero Section (Top Padding Zero, H1 Langsung Bersih) -->
    <header class="hero-section">
      <div class="breadcrumb-nav">
        <a href="{{ url('/') }}">Beranda</a>
        <span>/</span>
        <a href="{{ url('/eo') }}">Race Management</a>
        <span>/</span>
        <span style="color: var(--text-slate-200);">Rekayasa Lalu Lintas Pelari</span>
      </div>

      <h1 class="page-title">Rekayasa Lalu Lintas Pelari: Analisis Teknis, Standar Keamanan dan Simulasi Jalur Lomba</h1>
      
      <p class="hero-lead">
        Panduan teknis komprehensif bagi Race Director, Dinas Perhubungan, Satlantas Polri, dan Event Organizer untuk merancang koridor lomba lari jalan raya yang steril, mengalir bebas tanpa hambatan (*zero bottle-neck*), dan meminimalkan gesekan terhadap mobilitas warga kota.
      </p>

      <div class="meta-bar">
        <div class="meta-item">
          <span>Penyusun:</span>
          <strong>Divisi Rekayasa Lintasan Ruang Lari</strong>
        </div>
        <div class="meta-item">
          <span>Standar Rujukan:</span>
          <strong>World Athletics &amp; US Highway Capacity Manual (HCM)</strong>
        </div>
        <div class="meta-item">
          <span>Status Dokumen:</span>
          <span class="tag-badge tag-green">Technical Whitepaper</span>
        </div>
        <div class="meta-item">
          <span>Waktu Pembacaan:</span>
          <strong>14 Menit</strong>
        </div>
      </div>

      <!-- Quick Telemetry Grid -->
      <div class="metric-grid">
        <div class="metric-card">
          <span class="metric-label">Standar Ruang Koridor</span>
          <span class="metric-val">0.75 - 1.4 m²</span>
          <span class="metric-desc">Kebutuhan luas per pelari pada kecepatan jelajah</span>
        </div>
        <div class="metric-card">
          <span class="metric-label">Batas Kecepatan Aliran</span>
          <span class="metric-val">8.5 - 14.0 km/h</span>
          <span class="metric-desc">Rentang laju rombongan (platoon velocity)</span>
        </div>
        <div class="metric-card">
          <span class="metric-label">Ambang Batas V/C Jalan</span>
          <span class="metric-val">&lt; 0.85</span>
          <span class="metric-desc">Volume-to-capacity rasio jalan pengalihan</span>
        </div>
        <div class="metric-card">
          <span class="metric-label">Celah Menyeberang Kritis</span>
          <span class="metric-val">4.5 - 7.0 Detik</span>
          <span class="metric-desc">Waktu hijau minimum pelepasan batch kendaraan</span>
        </div>
      </div>
    </header>

    <!-- Daftar Isi Artikel -->
    <section class="toc-card" aria-label="Daftar Isi Panduan">
      <div class="toc-title">
        <span>Daftar Isi Pembahasan Teknis</span>
        <span style="font-size:0.6875rem; color:var(--text-slate-400);">Navigasi Cepat</span>
      </div>
      <div class="toc-grid">
        <a class="toc-link" href="#urgensi"><span class="toc-num">01</span> Urgensi Rekayasa Arus Pelari di Jalan Raya</a>
        <a class="toc-link" href="#karakteristik"><span class="toc-num">02</span> Karakteristik Mobilitas Pelari vs Kendaraan</a>
        <a class="toc-link" href="#kapasitas-hcm"><span class="toc-num">03</span> Standar Geometrik dan Tingkat Pelayanan (LOS)</a>
        <a class="toc-link" href="#kalkulator-kapasitas"><span class="toc-num">04</span> Kalkulator Kapasitas Lintasan dan Wave Start</a>
        <a class="toc-link" href="#model-persimpangan"><span class="toc-num">05</span> 4 Model Solusi Persimpangan &amp; Simulasi Interaktif</a>
        <a class="toc-link" href="#sim1-anchor"><span class="toc-num">05.1</span> Model 1: Persimpangan Silang Bergantian</a>
        <a class="toc-link" href="#sim2-anchor"><span class="toc-num">05.2</span> Model 2: Bundaran Aliran Bebas Konsentris</a>
        <a class="toc-link" href="#sim3-anchor"><span class="toc-num">05.3</span> Model 3: Pemisahan Tingkat (Overpass Ramp)</a>
        <a class="toc-link" href="#sim4-anchor"><span class="toc-num">05.4</span> Model 4: Katup Pintu Air Otomatis (Sluice Gate)</a>
        <a class="toc-link" href="#matriks-efisiensi"><span class="toc-num">06</span> Matriks Komparasi Efisiensi &amp; Biaya Lapangan</a>
        <a class="toc-link" href="#sop-hari-h"><span class="toc-num">07</span> Standar Operasional Prosedur (SOP) Hari-H</a>
        <a class="toc-link" href="#faq"><span class="toc-num">08</span> Tanya Jawab Teknis (FAQ) Regulasi &amp; Keamanan</a>
      </div>
    </section>

    <!-- BAB 1: URGENSI -->
    <article class="article-section" id="urgensi">
      <div class="section-header">
        <h2>1. Urgensi Rekayasa Lalu Lintas Pelari pada Event Jalan Raya</h2>
        <p>Transformasi fungsi jalan publik menjadi arena atletik berkecepatan tinggi dengan risiko keselamatan mutlak.</p>
      </div>
      <p class="prose-text">
        Penyelenggaraan lomba lari massal (road race) berskala 5K, 10K, Half Marathon, hingga Full Marathon menuntut pengambilalihan ruang jalan publik secara dinamis. Berbeda dengan penutupan jalan untuk festival statis atau pasar kaget, <strong>rekayasa lalu lintas pelari</strong> mengatur aliran ribuan manusia yang bergerak serentak dengan kecepatan jelajah 8 hingga 15 kilometer per jam. 
      </p>
      <p class="prose-text">
        Ketika ribuan pelari dilepas ke jalan raya, tiga risiko utama langsung muncul apabila rekayasa lalu lintas tidak dirancang secara ilmiah:
      </p>
      <div class="info-panel">
        <div class="info-panel-title">Tiga Titik Rawan Utama Tanpa Rekayasa Lalu Lintas:</div>
        <ul style="padding-left: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 0.875rem;">
          <li><strong>Benturan Fisik Fatal Kendaraan-Pelari:</strong> Pelari tidak memiliki perlindungan benturan (zero protective shell). Interaksi frontal maupun bersudut dengan sepeda motor atau mobil di persimpangan jalan berisiko fatalitas fatal.</li>
          <li><strong>Efek Botol dan Gelombang Desak-Desakan (Crush Hazard):</strong> Penyempitan lebar jalan mendadak tanpa transisi gradien menyebabkan fenomena gelombang kejut (shockwave), di mana pelari di barisan belakang menabrak barisan depan yang melambat mendadak.</li>
          <li><strong>Gridlock Urat Nadi Transportasi Perkotaan:</strong> Penutupan simpang protokol tanpa jalur pengalihan (detour network) yang terukur dapat melumpuhkan akses ambulans rumah sakit umum, pemadam kebakaran, dan aktivitas ekonomi warga.</li>
        </ul>
      </div>
    </article>

    <!-- BAB 2: KARAKTERISTIK MOBILITAS -->
    <article class="article-section" id="karakteristik">
      <div class="section-header">
        <h2>2. Karakteristik Mobilitas Pelari vs Kendaraan Bermotor</h2>
        <p>Memahami perbedaan fisik, akselerasi, manuver lateral, dan hukum aliran dinamis rombongan pelari.</p>
      </div>
      <p class="prose-text">
        Dalam disiplin rekayasa transportasi, pejalan kaki cepat dan pelari dimodelkan menggunakan adaptasi dari <em>Greenshields Traffic Stream Model</em>:
      </p>
      
      <div class="info-panel" style="text-align: center; padding: 20px;">
        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-slate-400);">Formula Dasar Aliran Kontinu Pelari</span>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.375rem; font-weight: 700; color: #38BDF8; margin: 8px 0;">
          q = k × v
        </div>
        <p style="font-size: 0.8125rem; color: var(--text-slate-300); max-width: 600px; margin: 0 auto;">
          Di mana <strong>q</strong> adalah debit laju aliran pelari (pelari per menit per meter lebar lajur), <strong>k</strong> adalah kerapatan atau densitas (pelari per m²), dan <strong>v</strong> adalah kecepatan jelajah rata-rata (meter per menit).
        </p>
      </div>

      <p class="prose-text">
        Berikut adalah perbandingan parameter operasional yang membedakan rombongan pelari dengan arus sepeda motor:
      </p>

      <div class="table-container">
        <table class="tech-table">
          <thead>
            <tr>
              <th>Parameter Teknis</th>
              <th>Rombongan Pelari (Runners Platoon)</th>
              <th>Arus Kendaraan Bermotor (Motorcycle Traffic)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Kecepatan Jelajah</strong></td>
              <td>2.2 – 4.2 m/detik (8 – 15 km/jam)</td>
              <td>8.3 – 13.8 m/detik (30 – 50 km/jam)</td>
            </tr>
            <tr>
              <td><strong>Jarak Pengereman / Henti</strong></td>
              <td>0.8 – 1.5 meter (instan, reflek kaki)</td>
              <td>6.0 – 14.0 meter (tergantung friksi aspal &amp; rem)</td>
            </tr>
            <tr>
              <td><strong>Manuver Lateral (Samping)</strong></td>
              <td>Sangat fleksibel (mampu zig-zag 0.5 meter mendadak)</td>
              <td>Kaku (membutuhkan radius belok minimum 3.0 meter)</td>
            </tr>
            <tr>
              <td><strong>Pola Sebaran Waktu</strong></td>
              <td>Berkelompok rapat di awal, menyebar eksponensial (*platoon dispersion*)</td>
              <td>Terdistribusi acak atau teratur sesuai sinyal lampu lalu lintas</td>
            </tr>
            <tr>
              <td><strong>Dampak Interupsi Berhenti</strong></td>
              <td>Merusak ritme fisiologis asam laktat atlet, memicu emosi massal</td>
              <td>Penumpukan emisi gas buang, antrean mekanis terukur</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <!-- BAB 3: STANDAR GEOMETRIK & HCM -->
    <article class="article-section" id="kapasitas-hcm">
      <div class="section-header">
        <h2>3. Standar Geometrik dan Tingkat Pelayanan (Level of Service - LOS)</h2>
        <p>Kriteria penilaian kapasitas koridor menurut Highway Capacity Manual (HCM) Pedestrian Chapter.</p>
      </div>
      <p class="prose-text">
        Berdasarkan metodologi <em>Highway Capacity Manual</em>, ruang gerak pelari diklasifikasikan ke dalam 6 tingkatan Level of Service (LOS). Race director profesional wajib merancang rute agar berada minimal pada <strong>LOS B atau LOS C</strong> sepanjang perlombaan:
      </p>

      <div class="table-container">
        <table class="tech-table">
          <thead>
            <tr>
              <th>Klasifikasi LOS</th>
              <th>Luas Area per Pelari</th>
              <th>Karakteristik Aliran Lapangan</th>
              <th>Status Kelayakan Rute</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="tag-badge tag-green">LOS A</span></td>
              <td>&gt; 3.7 m² / pelari</td>
              <td>Sangat leluasa. Pelari bebas memilih kecepatan langkah dan menyalip tanpa kontak fisik.</td>
              <td>Ideal (Sangat Aman)</td>
            </tr>
            <tr>
              <td><span class="tag-badge tag-green">LOS B</span></td>
              <td>2.3 – 3.7 m² / pelari</td>
              <td>Leluasa. Kecepatan jelajah normal, manuver mendahului memerlukan sedikit penyesuaian visual.</td>
              <td>Standar Utama Lomba</td>
            </tr>
            <tr>
              <td><span class="tag-badge tag-blue">LOS C</span></td>
              <td>1.4 – 2.3 m² / pelari</td>
              <td>Stabil namun mulai padat. Pelari harus memperhatikan ayunan lengan pelari di sekitar.</td>
              <td>Batas Aman Minimum</td>
            </tr>
            <tr>
              <td><span class="tag-badge tag-orange">LOS D</span></td>
              <td>0.9 – 1.4 m² / pelari</td>
              <td>Padat terbatas. Kecepatan langkah mulai terhambat oleh pelari di depan. Menyalip sulit.</td>
              <td>Peringatan Kepadatan</td>
            </tr>
            <tr>
              <td><span class="tag-badge tag-red">LOS E</span></td>
              <td>0.5 – 0.9 m² / pelari</td>
              <td>Sangat padat. Terjadi kontak fisik bahu/lengan. Kecepatan langkah turun drastis hingga jalan cepat.</td>
              <td>Rawan Bottle-Neck</td>
            </tr>
            <tr>
              <td><span class="tag-badge tag-red">LOS F</span></td>
              <td>&lt; 0.5 m² / pelari</td>
              <td>Kondisi mogok total (gridlock). Gelombang berhenti penuh. Risiko terinjak dan desak-desakan tinggi.</td>
              <td>Dilarang Keras (Bahaya)</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <!-- BAB 4: KALKULATOR INTERAKTIF -->
    <article class="article-section" id="kalkulator-kapasitas">
      <div class="section-header">
        <h2>4. Kalkulator Interaktif Kapasitas Lintasan dan Pelepasan Wave</h2>
        <p>Instrumen perhitungan ilmiah untuk menentukan jumlah gelombang start dan kelayakan ruang jalan.</p>
      </div>

      <div class="calc-container">
        <div class="calc-grid">
          <div class="calc-inputs">
            <div class="calc-field">
              <label class="calc-label" for="calcTotalRunners">
                <span>Total Peserta Lomba (Orang)</span>
                <span id="calcTotalRunnersVal" style="font-family:'JetBrains Mono'; color:#38BDF8;">3000</span>
              </label>
              <input type="range" id="calcTotalRunners" min="500" max="15000" step="250" value="3000" oninput="calculateRunnerCapacity()">
            </div>

            <div class="calc-field">
              <label class="calc-label" for="calcCorralWidth">
                <span>Lebar Jalur Start / Koridor Kritis (Meter)</span>
                <span id="calcCorralWidthVal" style="font-family:'JetBrains Mono'; color:#38BDF8;">8.0 m</span>
              </label>
              <input type="range" id="calcCorralWidth" min="3" max="20" step="0.5" value="8.0" oninput="calculateRunnerCapacity()">
            </div>

            <div class="calc-field">
              <label class="calc-label" for="calcCorralLength">
                <span>Panjang Kantong Start Corral (Meter)</span>
                <span id="calcCorralLengthVal" style="font-family:'JetBrains Mono'; color:#38BDF8;">50 m</span>
              </label>
              <input type="range" id="calcCorralLength" min="20" max="150" step="5" value="50" oninput="calculateRunnerCapacity()">
            </div>

            <div class="calc-field">
              <label class="calc-label" for="calcWaveInterval">
                <span>Interval Pelepasan Antar Wave (Menit)</span>
                <span id="calcWaveIntervalVal" style="font-family:'JetBrains Mono'; color:#38BDF8;">4.0 Menit</span>
              </label>
              <input type="range" id="calcWaveInterval" min="2" max="10" step="0.5" value="4.0" oninput="calculateRunnerCapacity()">
            </div>
          </div>

          <div class="calc-results">
            <div>
              <div class="result-row">
                <span class="result-label">Total Luas Ruang Corral:</span>
                <span class="result-val" id="calcAreaVal">400 m²</span>
              </div>
              <div class="result-row">
                <span class="result-label">Kepadatan Ruang (Per Wave):</span>
                <span class="result-val" id="calcDensityVal">0.80 m² / orang</span>
              </div>
              <div class="result-row">
                <span class="result-label">Tingkat Pelayanan (LOS Start):</span>
                <span id="calcLosBadge" class="tag-badge tag-green">LOS B (Aman)</span>
              </div>
              <div class="result-row">
                <span class="result-label">Rekomendasi Pembagian Gelombang:</span>
                <span class="result-val" id="calcRecommendedWaves">3 Wave (1,000 / wave)</span>
              </div>
              <div class="result-row">
                <span class="result-label">Estimasi Waktu Pelepasan Selesai:</span>
                <span class="result-val" id="calcTotalReleaseTime">8.0 Menit</span>
              </div>
            </div>
            
            <div id="calcRecommendationBox" style="background: var(--bg-surface); padding: 12px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-dark); font-size: 0.75rem; line-height: 1.5; color: var(--text-slate-300);">
              Formasi jalur memenuhi standar keselamatan World Athletics. Celah antar-gelombang memberikan jendela waktu yang cukup bagi pembagian lalu lintas di persimpangan KM 1.
            </div>
          </div>
        </div>
      </div>
    </article>

    <!-- BAB 5: 4 MODEL SOLUSI PERSIMPANGAN & SIMULASI INTERAKTIF -->
    <article class="article-section" id="model-persimpangan">
      <div class="section-header">
        <h2>5. 4 Model Solusi Rekayasa Persimpangan dan Simulasi Interaktif</h2>
        <p>Simulasi dinamis interaktif untuk menganalisis arus persilangan jalan sebidang pada berbagai skenario lapangan.</p>
      </div>

      <p class="prose-text">
        Persimpangan sebidang (*at-grade intersections*) adalah zona konflik kritis dengan risiko gesekan tertinggi dalam lomba lari jalan raya. Race organizer dihadapkan pada tantangan mengalirkan ribuan pelari tanpa henti sembari tetap memberi jalan bagi mobilitas sepeda motor komuter lokal. Berikut adalah 4 model rekayasa lalu lintas yang dapat disimulasikan dan disesuaikan parameternya secara langsung:
      </p>

      <!-- SIMULASI 1 -->
      <div class="sim-wrapper-outer" id="sim1-anchor">
        <div class="sim-container" id="sim1-container">
          <header class="sim-header">
            <div class="sim-title-group">
              <span class="model-badge">Model 1 • Persimpangan Silang Bergantian</span>
              <h3>Simulasi Persimpangan Dua Jalur Bergantian (Marshal Alternating)</h3>
              <p>Jalur Lari Satu Arah (Barat ke Timur) dengan Pengalihan Lajur Atas/Bawah melalui Komando Marshal</p>
            </div>
            <div class="lane-status-badge" id="activeLaneBadge">
              <span class="status-dot"></span>
              <span id="activeLaneText">Jalur Atas Aktif</span>
            </div>
          </header>

          <div class="telemetry-bar">
            <div class="telemetry-item">
              <span class="telemetry-label">Formasi Pelari</span>
              <span class="telemetry-val" id="totalRunnersCount">4 Pelari (2B × 2K)</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Motor di Lintasan</span>
              <span class="telemetry-val" id="activeMotorsCount">0 Motor</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Antrean Atas</span>
              <span class="telemetry-val" id="queueAtasText">0/8 Slot</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Antrean Bawah</span>
              <span class="telemetry-val" id="queueBawahText">0/8 Slot</span>
            </div>
          </div>

          <div class="canvas-frame">
            <svg width="100%" viewBox="0 0 680 560" aria-label="Simulasi dua jalur silang adaptif" style="background: #ffffff;">
              <rect class="c-gray" x="300" y="0" width="80" height="560" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <rect class="c-gray" x="0" y="220" width="680" height="120" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <rect x="300" y="240" width="80" height="80" fill="#e5e7eb" />

              <line x1="340" y1="0" x2="340" y2="560" stroke="#fff" stroke-dasharray="8 6" stroke-width="2" />
              <line x1="0" y1="280" x2="680" y2="280" stroke="#fff" stroke-dasharray="8 6" stroke-width="2" />

              <rect id="atasZone" x="300" y="210" width="80" height="40" rx="4" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-dasharray="6 4" />
              <rect id="bawahZone" x="300" y="310" width="80" height="40" rx="4" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-dasharray="6 4" />

              <text x="320" y="30" text-anchor="middle" font-size="10" fill="#9ca3af">▼ Ke Sltn</text>
              <text x="360" y="535" text-anchor="middle" font-size="10" fill="#9ca3af">▲ Ke Utr</text>

              <text class="ts" x="392" y="230" dominant-baseline="central" font-size="12" fill="#4b5563">Jalur atas</text>
              <text class="ts" x="392" y="330" dominant-baseline="central" font-size="12" fill="#4b5563">Jalur bawah</text>
              <text x="340" y="280" text-anchor="middle" dominant-baseline="central" font-size="10" fill="#6b7280">Median Transit</text>

              <g id="roadGuides">
                <path id="guideArrowAtas" d="M 265 272 Q 282 258 300 234" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="6 4" opacity="0.9" stroke-linecap="round" />
                <polygon id="guideHeadAtas" points="304,230 296,228 300,237" fill="#10b981" />
                <path id="guideArrowBawah" d="M 265 288 Q 282 302 300 326" fill="none" stroke="#9ca3af" stroke-width="2" stroke-dasharray="6 4" opacity="0.25" stroke-linecap="round" />
                <polygon id="guideHeadBawah" points="304,330 300,323 296,332" fill="#9ca3af" opacity="0.25" />
              </g>

              <g id="marshalGroup" style="cursor: pointer;" onclick="manualSwitchLane()" aria-label="Marshal Pengatur Jalur Lari - Klik untuk Switch">
                <ellipse cx="240" cy="216" rx="24" ry="7" fill="#cbd5e1" stroke="#94a3b8" stroke-width="1" />
                <rect x="218" y="212" width="44" height="4" rx="2" fill="#f59e0b" />
                <line x1="225" y1="212" x2="229" y2="216" stroke="#1e293b" stroke-width="1.5" />
                <line x1="235" y1="212" x2="239" y2="216" stroke="#1e293b" stroke-width="1.5" />
                <line x1="245" y1="212" x2="249" y2="216" stroke="#1e293b" stroke-width="1.5" />
                <line x1="255" y1="212" x2="259" y2="216" stroke="#1e293b" stroke-width="1.5" />

                <circle id="marshalWave" cx="240" cy="180" r="14" fill="none" stroke="#f59e0b" stroke-width="2.5" opacity="0" />
                <ellipse cx="240" cy="214" rx="11" ry="3" fill="#64748b" opacity="0.35" />
                
                <line x1="236" y1="198" x2="236" y2="213" stroke="#1e293b" stroke-width="3.5" stroke-linecap="round" />
                <line x1="244" y1="198" x2="244" y2="213" stroke="#1e293b" stroke-width="3.5" stroke-linecap="round" />
                <ellipse cx="235" cy="214" rx="3.5" ry="2" fill="#0f172a" />
                <ellipse cx="245" cy="214" rx="3.5" ry="2" fill="#0f172a" />

                <rect x="232" y="176" width="16" height="23" rx="4" fill="#f97316" stroke="#ea580c" stroke-width="1" />
                <line x1="232" y1="182" x2="248" y2="182" stroke="#ffffff" stroke-width="2" />
                <line x1="232" y1="191" x2="248" y2="191" stroke="#ffffff" stroke-width="2" />
                <line x1="236" y1="176" x2="236" y2="199" stroke="#ffffff" stroke-width="1.5" />
                <line x1="244" y1="176" x2="244" y2="199" stroke="#ffffff" stroke-width="1.5" />

                <path d="M 233 180 Q 227 187 233 193" fill="none" stroke="#f97316" stroke-width="3.5" stroke-linecap="round" />
                <circle cx="240" cy="168" r="6.5" fill="#fcd34d" stroke="#f59e0b" stroke-width="0.75" />
                <rect x="238" y="166" width="6" height="2.5" rx="1" fill="#0f172a" />
                <path d="M 233 166 Q 240 158 247 166 Z" fill="#ea580c" />
                <path d="M 239 166 Q 247 166 250 168" stroke="#c2410c" stroke-width="1.5" stroke-linecap="round" />

                <g id="marshalArm" transform="rotate(-35, 246, 180)">
                  <line x1="246" y1="180" x2="260" y2="180" stroke="#f97316" stroke-width="4.5" stroke-linecap="round" />
                  <circle cx="260" cy="180" r="2.5" fill="#ffffff" />
                  <line id="marshalBaton" x1="260" y1="180" x2="282" y2="180" stroke="#10b981" stroke-width="4" stroke-linecap="round" />
                  <circle id="marshalBatonTip" cx="282" cy="180" r="3.5" fill="#10b981" />
                  <polygon id="marshalArrowTip" points="285,180 291,176 291,184" fill="#10b981" />
                </g>

                <g transform="translate(186, 134)">
                  <rect x="0" y="0" width="94" height="26" rx="5" fill="#0f172a" stroke="#334155" stroke-width="1.5" />
                  <rect x="2" y="2" width="90" height="22" rx="3" fill="#020617" />
                  <text id="marshalSignText" x="47" y="17" text-anchor="middle" font-size="9.5" font-weight="800" fill="#10b981" letter-spacing="0.5">JALUR ATAS</text>
                </g>

                <rect x="219" y="224" width="42" height="12" rx="3" fill="#f8fafc" stroke="#e2e8f0" stroke-width="0.75" />
                <text x="240" y="233" text-anchor="middle" font-size="7" font-weight="800" fill="#f97316" letter-spacing="0.5">MARSHAL</text>
              </g>

              <g id="slotsAtas"></g>
              <g id="slotsBawah"></g>
              <text id="countAtas" class="ts" x="555" y="230" dominant-baseline="central" font-size="12" fill="#4b5563">0/8</text>
              <text id="countBawah" class="ts" x="555" y="330" dominant-baseline="central" font-size="12" fill="#4b5563">0/8</text>

              <g id="motorLayer"></g>
              <g id="runnersLayer"></g>
            </svg>
          </div>

          <div class="sim-legend">
            <div class="legend-item">
              <span class="legend-indicator ind-runner"></span>
              <span>Pelari (Formasi Terbuka)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-motor-south"></span>
              <span>Sepeda Motor (Dua Arah)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator" style="background:#f97316; border:1px solid #ffffff; box-shadow:0 0 0 1px #f97316;"></span>
              <span>Marshal Pengarah (Atas / Bawah)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator" style="background:#5DCAA5; border:1px dashed #0F6E56;"></span>
              <span>Jalur Aktif Penyeberangan</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator" style="background:#cbd5e1; border-radius:3px;"></span>
              <span>Median Transit Antara</span>
            </div>
          </div>

          <div class="controls-panel">
            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Formasi Pelari</span>
                <span class="group-badge" id="formationSummaryBadge">2×2 Formasi</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Baris Pelari (Rows)</span>
                  <span class="slider-badge" id="runnerRowsLabel">2 Baris</span>
                </div>
                <input type="range" class="slider-runner" id="runnerRowsSlider" min="1" max="4" step="1" value="2" oninput="setRunnerRows(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kolom Pelari (Cols)</span>
                  <span class="slider-badge" id="runnerColsLabel">2 Kolom</span>
                </div>
                <input type="range" class="slider-runner" id="runnerColsSlider" min="1" max="14" step="1" value="2" oninput="setRunnerCols(this.value)">
              </div>
              <div class="slider-field" id="spacingField">
                <div class="slider-label-row">
                  <span class="slider-title">Jarak Kolom Formasi</span>
                  <span class="slider-badge" id="runnerSpacingLabel">40 px</span>
                </div>
                <input type="range" class="slider-runner" id="runnerSpacingSlider" min="20" max="100" step="5" value="40" oninput="setRunnerSpacing(this.value)">
              </div>
              <label class="checkbox-field">
                <input type="checkbox" id="spreadCheckbox" onchange="toggleSpread(this.checked)">
                <span>Sebar merata sepanjang jalur (loop)</span>
              </label>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Lalu Lintas Motor</span>
                <span class="group-badge" id="motorDensityBadge">Tingkat 2</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kepadatan Kendaraan</span>
                  <span class="slider-badge" id="motorDensityLabel">Level 2 • Normal (2.0s)</span>
                </div>
                <input type="range" class="slider-motor" id="motorDensitySlider" min="1" max="5" step="1" value="2" oninput="setMotorDensity(this.value)">
              </div>
              <p style="font-size: 0.75rem; color: var(--text-slate-300); line-height: 1.4;">
                Sistem otomatis menghentikan motor saat penyeberang lewat dan mencegah penumpukan kendaraan di titik temu median transit.
              </p>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Kontrol Simulasi</span>
                <span class="group-badge" id="speedBadge">1.0x</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kecepatan Animasi</span>
                  <span class="slider-badge" id="speedLabel">1x</span>
                </div>
                <input type="range" id="speedSlider" min="0.5" max="2.5" step="0.25" value="1" oninput="setSpeed(this.value)">
              </div>
              <div class="actions-row">
                <button class="btn-sim btn-sim-primary" id="playBtn" onclick="togglePlay()" aria-label="Jeda atau mulai simulasi">
                  <span id="playText">Pause</span>
                </button>
                <button class="btn-sim" id="switchLaneBtn" onclick="manualSwitchLane()" aria-label="Switch jalur lari melalui Marshal">
                  <span>Switch Jalur (Marshal)</span>
                </button>
                <button class="btn-sim btn-sim-danger" onclick="resetSimulation()" aria-label="Reset simulasi ke default">
                  <span>Reset</span>
                </button>
              </div>
            </div>
          </div>

          <footer class="sim-footer">
            <span>Model 1: Alternating Lane Switching System</span>
            <span>Ruang Lari Technical Traffic Simulation</span>
          </footer>
        </div>
      </div>

      <!-- SIMULASI 2 -->
      <div class="sim-wrapper-outer" id="sim2-anchor">
        <div class="sim-container" id="sim2-container">
          <header class="sim-header">
            <div class="sim-title-group">
              <span class="model-badge">Model 2 • Bundaran Aliran Bebas Konsentris</span>
              <h3>Simulasi Bundaran Bebas Hambatan (Adaptive Roundabout)</h3>
              <p>Motor Mengalir di Lajur Cincin Dalam Saat Jalur Pelari Kosong • Pelari Non-Stop di Lajur Luar</p>
            </div>
            <div class="lane-status-badge" id="rbStatusBadge">
              <span class="status-dot"></span>
              <span id="rbStatusText">Grup Pelari di Putaran Atas</span>
            </div>
          </header>

          <div class="telemetry-bar">
            <div class="telemetry-item">
              <span class="telemetry-label">Formasi Pelari (Lajur Luar)</span>
              <span class="telemetry-val" id="rbTotalRunnersCount">8 Pelari (2B × 4K)</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Motor Selatan (Lajur Kiri)</span>
              <span class="telemetry-val" id="rbMotorSouthStatus">Melaju Bebas</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Motor Utara (Lajur Kanan)</span>
              <span class="telemetry-val" id="rbMotorNorthStatus">Melaju Bebas</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Jalur Pelari Aktif</span>
              <span class="telemetry-val" id="rbActiveTrackName" style="color:#10B981;">Putaran Atas</span>
            </div>
          </div>

          <div class="canvas-frame">
            <svg width="100%" viewBox="0 0 680 560" aria-label="Simulasi bundaran dua arah motor dan pelari non-stop" style="background: #ffffff;">
              <rect x="0" y="240" width="205" height="80" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <rect x="475" y="240" width="205" height="80" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />

              <rect x="300" y="0" width="80" height="190" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <rect x="300" y="370" width="80" height="190" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />

              <circle cx="340" cy="280" r="158" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <circle cx="340" cy="280" r="114" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-dasharray="8 6" />

              <circle cx="340" cy="280" r="64" fill="#10B981" stroke="#059669" stroke-width="3" />
              <circle cx="340" cy="280" r="36" fill="#047857" opacity="0.25" />
              <text x="340" y="278" text-anchor="middle" font-size="9.5" font-weight="800" fill="#ffffff" letter-spacing="0.5">BUNDARAN</text>
              <text x="340" y="291" text-anchor="middle" font-size="7" font-weight="600" fill="#d1fae5">BEBAS HAMBATAN</text>

              <line x1="0" y1="280" x2="204" y2="280" stroke="#fff" stroke-dasharray="8 6" stroke-width="2" />
              <line x1="476" y1="280" x2="680" y2="280" stroke="#fff" stroke-dasharray="8 6" stroke-width="2" />
              <line x1="340" y1="0" x2="340" y2="188" stroke="#fff" stroke-dasharray="8 6" stroke-width="2" />
              <line x1="340" y1="372" x2="340" y2="560" stroke="#fff" stroke-dasharray="8 6" stroke-width="2" />

              <path id="rbTrackAtas" d="M 476 280 A 136 136 0 0 0 204 280" fill="none" stroke="#0F6E56" stroke-width="24" stroke-linecap="round" stroke-dasharray="6 4" opacity="0.45" />
              <path id="rbTrackBawah" d="M 476 280 A 136 136 0 0 1 204 280" fill="none" stroke="#9ca3af" stroke-width="24" stroke-linecap="round" stroke-dasharray="6 4" opacity="0.2" />

              <path d="M 320 372 A 94 94 0 0 1 320 188" fill="none" stroke="#2563EB" stroke-width="16" opacity="0.14" />
              <path d="M 360 188 A 94 94 0 0 1 360 372" fill="none" stroke="#7c3aed" stroke-width="16" opacity="0.14" />

              <line x1="300" y1="442" x2="340" y2="442" stroke="#ffffff" stroke-width="3.5" />
              <rect x="270" y="434" width="28" height="16" rx="3" fill="#fee2e2" stroke="#ef4444" stroke-width="1" />
              <text x="284" y="445" text-anchor="middle" font-size="7.5" font-weight="800" fill="#b91c1c">STOP</text>

              <line x1="340" y1="118" x2="380" y2="118" stroke="#ffffff" stroke-width="3.5" />
              <rect x="382" y="110" width="28" height="16" rx="3" fill="#fee2e2" stroke="#ef4444" stroke-width="1" />
              <text x="396" y="121" text-anchor="middle" font-size="7.5" font-weight="800" fill="#b91c1c">STOP</text>

              <text x="340" y="108" text-anchor="middle" font-size="8.5" font-weight="700" fill="#0F6E56">LAJUR LUAR: PELARI (PUTARAN ATAS)</text>
              <text x="340" y="166" text-anchor="middle" font-size="8" font-weight="600" fill="#4b5563">Lajur Dalam: Motor Mengalir Bebas</text>

              <text x="340" y="456" text-anchor="middle" font-size="8.5" font-weight="700" fill="#0F6E56">LAJUR LUAR: PELARI (PUTARAN BAWAH)</text>
              <text x="340" y="396" text-anchor="middle" font-size="8" font-weight="600" fill="#4b5563">Lajur Dalam: Motor Mengalir Bebas</text>

              <text x="645" y="235" font-size="10" font-weight="700" fill="#0F6E56">Pelari Masuk (Timur)</text>
              <text x="35" y="235" font-size="10" font-weight="700" fill="#0F6E56">Pelari Keluar (Barat)</text>
              
              <text x="315" y="540" text-anchor="middle" font-size="9" font-weight="700" fill="#2563EB">Sltn ke Utr (Lajur Kiri)</text>
              <text x="365" y="25" text-anchor="middle" font-size="9" font-weight="700" fill="#7c3aed">Utr ke Sltn (Lajur Kanan)</text>

              <g id="rbMotorLayer"></g>
              <g id="rbRunnersLayer"></g>
            </svg>
          </div>

          <div class="sim-legend">
            <div class="legend-item">
              <span class="legend-indicator ind-runner"></span>
              <span>Pelari (Lajur Luar R=136px)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-motor-south"></span>
              <span>Motor Selatan (Lajur Dalam Kiri)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-motor-north"></span>
              <span>Motor Utara (Lajur Dalam Kanan)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-lane-divider"></span>
              <span>Marka Pemisah Lajur</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-island"></span>
              <span>Pulau Bundaran Bebas Hambatan</span>
            </div>
          </div>

          <div class="controls-panel">
            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Formasi Pelari (Non-Stop)</span>
                <span class="group-badge" id="rbFormationBadge">2×4 Formasi (8 Pelari)</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Baris Pelari (Rows)</span>
                  <span class="slider-badge" id="rbRunnerRowsLabel">2 Baris</span>
                </div>
                <input type="range" class="slider-runner" id="rbRunnerRowsSlider" min="1" max="4" step="1" value="2" oninput="setRbRunnerRows(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kolom Pelari (Cols)</span>
                  <span class="slider-badge" id="rbRunnerColsLabel">4 Kolom</span>
                </div>
                <input type="range" class="slider-runner" id="rbRunnerColsSlider" min="1" max="10" step="1" value="4" oninput="setRbRunnerCols(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Jarak Rombongan</span>
                  <span class="slider-badge" id="rbSpacingLabel">32 px</span>
                </div>
                <input type="range" class="slider-runner" id="rbSpacingSlider" min="20" max="60" step="4" value="32" oninput="setRbSpacing(this.value)">
              </div>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Kepadatan Motor (Dua Arah)</span>
                <span class="group-badge" id="rbMotorDensityBadge">Tingkat 2</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Frekuensi Motor</span>
                  <span class="slider-badge" id="rbMotorDensityLabel">Level 2 • Normal (2.2s)</span>
                </div>
                <input type="range" class="slider-motor" id="rbMotorDensitySlider" min="1" max="5" step="1" value="2" oninput="setRbMotorDensity(this.value)">
              </div>
              <p style="font-size: 0.75rem; color: var(--text-slate-300); line-height: 1.4;">
                Motor membaca jeda celah rombongan secara otomatis. Ketika ekor kelompok pelari keluar, motor langsung berputar melintasi cincin dalam.
              </p>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Kontrol Animasi</span>
                <span class="group-badge" id="rbSpeedBadge">1.0x</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kecepatan Animasi</span>
                  <span class="slider-badge" id="rbSpeedLabel">1x</span>
                </div>
                <input type="range" id="rbSpeedSlider" min="0.5" max="2.5" step="0.25" value="1" oninput="setRbSpeed(this.value)">
              </div>
              <div class="actions-row">
                <button class="btn-sim btn-sim-primary" id="rbPlayBtn" onclick="toggleRbPlay()" aria-label="Jeda atau mulai simulasi bundaran">
                  <span id="rbPlayText">Pause</span>
                </button>
                <button class="btn-sim btn-sim-danger" onclick="resetRbSimulation()" aria-label="Reset simulasi bundaran ke default">
                  <span>Reset</span>
                </button>
              </div>
            </div>
          </div>

          <footer class="sim-footer">
            <span>Model 2: Concentric Roundabout Separation</span>
            <span>Ruang Lari Technical Traffic Simulation</span>
          </footer>
        </div>
      </div>

      <!-- SIMULASI 3 -->
      <div class="sim-wrapper-outer" id="sim3-anchor">
        <div class="sim-container" id="sim3-container">
          <header class="sim-header">
            <div class="sim-title-group">
              <span class="model-badge">Model 3 • Pemisahan Tingkat Vertikal (Grade Separation)</span>
              <h3>Simulasi Jembatan Layang Khusus Pelari (Overpass Ramp)</h3>
              <p>Pemisahan Ketinggian Vertikal (+4.5m) • 100% Arus Bebas Tanpa Hambatan &amp; Nol Risiko Tabrakan</p>
            </div>
            <div class="lane-status-badge" style="background:rgba(124,58,237,0.15); color:#A78BFA; border-color:rgba(124,58,237,0.3);">
              <span class="status-dot" style="background:#8B5CF6;"></span>
              <span>Elevasi Bebas Hambatan (+4.5m)</span>
            </div>
          </header>

          <div class="telemetry-bar">
            <div class="telemetry-item">
              <span class="telemetry-label">Formasi Pelari (Overpass)</span>
              <span class="telemetry-val" id="gsTotalRunnersCount">8 Pelari (2B × 4K)</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Arus Motor (Jalan Bawah)</span>
              <span class="telemetry-val" id="gsMotorStatus" style="color:#60A5FA;">Bebas Melaju (0s Delay)</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Arus Pelari (Jembatan)</span>
              <span class="telemetry-val" id="gsRunnerStatus" style="color:#A78BFA;">100% Kontinu Tanpa Henti</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Efisiensi Sistem</span>
              <span class="telemetry-val" id="gsEfficiencyBadge" style="color:#34D399;">100% (Zero Collision)</span>
            </div>
          </div>

          <div class="canvas-frame">
            <svg width="100%" viewBox="0 0 680 440" aria-label="Simulasi Grade Separation Jembatan Layang" style="background: #ffffff;">
              <rect x="300" y="0" width="80" height="440" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <line x1="340" y1="0" x2="340" y2="440" stroke="#ffffff" stroke-dasharray="8 6" stroke-width="2" />

              <text x="320" y="25" text-anchor="middle" font-size="10" font-weight="700" fill="#9ca3af">▼ Ke Sltn</text>
              <text x="360" y="425" text-anchor="middle" font-size="10" font-weight="700" fill="#9ca3af">▲ Ke Utr</text>

              <g id="gsMotorLayer"></g>

              <rect x="220" y="185" width="240" height="85" rx="8" fill="#0f172a" opacity="0.16" />

              <rect x="220" y="165" width="18" height="120" rx="3" fill="#64748b" stroke="#475569" stroke-width="1" />
              <rect x="216" y="280" width="26" height="8" rx="2" fill="#334155" />
              <text x="229" y="225" text-anchor="middle" font-size="7" font-weight="800" fill="#f8fafc" transform="rotate(-90 229 225)">PILAR BARAT</text>

              <rect x="442" y="165" width="18" height="120" rx="3" fill="#64748b" stroke="#475569" stroke-width="1" />
              <rect x="438" y="280" width="26" height="8" rx="2" fill="#334155" />
              <text x="451" y="225" text-anchor="middle" font-size="7" font-weight="800" fill="#f8fafc" transform="rotate(-90 451 225)">PILAR TIMUR</text>

              <path d="M 30 190 L 230 190 L 230 260 L 30 260 Z" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1" />
              <line x1="70" y1="192" x2="70" y2="258" stroke="#e2e8f0" stroke-width="1.5" />
              <line x1="110" y1="192" x2="110" y2="258" stroke="#cbd5e1" stroke-width="1.5" />
              <line x1="150" y1="192" x2="150" y2="258" stroke="#cbd5e1" stroke-width="1.5" />
              <line x1="190" y1="192" x2="190" y2="258" stroke="#94a3b8" stroke-width="1.5" />
              <text x="130" y="180" text-anchor="middle" font-size="9" font-weight="700" fill="#7c3aed">RAMP NAIK (0m → +4.5m)</text>

              <rect x="230" y="188" width="220" height="74" rx="4" fill="#ffffff" stroke="#7c3aed" stroke-width="2" />
              <rect x="230" y="190" width="220" height="70" fill="#ede9fe" opacity="0.45" />

              <line x1="30" y1="190" x2="650" y2="190" stroke="#7c3aed" stroke-width="3.5" stroke-linecap="round" />
              <line x1="30" y1="260" x2="650" y2="260" stroke="#7c3aed" stroke-width="3.5" stroke-linecap="round" />
              <line x1="230" y1="190" x2="450" y2="190" stroke="#0ea5e9" stroke-width="2.5" />
              <line x1="230" y1="260" x2="450" y2="260" stroke="#0ea5e9" stroke-width="2.5" />

              <g transform="translate(260, 142)">
                <rect x="0" y="0" width="160" height="24" rx="5" fill="#1e1b4b" stroke="#7c3aed" stroke-width="1.5" />
                <text x="80" y="16" text-anchor="middle" font-size="9" font-weight="800" fill="#e0e7ff" letter-spacing="0.5">JEMBATAN MARATON (+4.5M)</text>
              </g>

              <path d="M 450 190 L 650 190 L 650 260 L 450 260 Z" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1" />
              <line x1="490" y1="192" x2="490" y2="258" stroke="#94a3b8" stroke-width="1.5" />
              <line x1="530" y1="192" x2="530" y2="258" stroke="#cbd5e1" stroke-width="1.5" />
              <line x1="570" y1="192" x2="570" y2="258" stroke="#cbd5e1" stroke-width="1.5" />
              <line x1="610" y1="192" x2="610" y2="258" stroke="#e2e8f0" stroke-width="1.5" />
              <text x="550" y="180" text-anchor="middle" font-size="9" font-weight="700" fill="#7c3aed">RAMP TURUN (+4.5m → 0m)</text>

              <line x1="30" y1="225" x2="650" y2="225" stroke="#ffffff" stroke-dasharray="6 4" stroke-width="2" />
              <text x="40" y="278" font-size="9.5" font-weight="700" fill="#0F6E56">Pelari Masuk (Barat)</text>
              <text x="640" y="278" text-anchor="end" font-size="9.5" font-weight="700" fill="#0F6E56">Pelari Keluar (Timur)</text>

              <g id="gsRunnersLayer"></g>
            </svg>
          </div>

          <div class="sim-legend">
            <div class="legend-item">
              <span class="legend-indicator ind-runner"></span>
              <span>Pelari (Elevasi Overpass +4.5m)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-overpass"></span>
              <span>Struktur Gelagar Overpass</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-motor-south"></span>
              <span>Sepeda Motor (Jalan Bawah Bebas Mengalir)</span>
            </div>
          </div>

          <div class="controls-panel">
            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Formasi Pelari Overpass</span>
                <span class="group-badge" id="gsFormationBadge">2×4 Formasi</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Baris Pelari (Rows)</span>
                  <span class="slider-badge" id="gsRunnerRowsLabel">2 Baris</span>
                </div>
                <input type="range" class="slider-runner" id="gsRunnerRowsSlider" min="1" max="4" step="1" value="2" oninput="setGsRunnerRows(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kolom Pelari (Cols)</span>
                  <span class="slider-badge" id="gsRunnerColsLabel">4 Kolom</span>
                </div>
                <input type="range" class="slider-runner" id="gsRunnerColsSlider" min="1" max="10" step="1" value="4" oninput="setGsRunnerCols(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Jarak Kolom Formasi</span>
                  <span class="slider-badge" id="gsSpacingLabel">32 px</span>
                </div>
                <input type="range" class="slider-runner" id="gsSpacingSlider" min="20" max="80" step="4" value="32" oninput="setGsSpacing(this.value)">
              </div>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Volume Sepeda Motor Bawah</span>
                <span class="group-badge" id="gsMotorDensityBadge">Tingkat 2</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kepadatan Kendaraan</span>
                  <span class="slider-badge" id="gsMotorDensityLabel">Level 2 • Normal (1.8s)</span>
                </div>
                <input type="range" class="slider-motor" id="gsMotorDensitySlider" min="1" max="5" step="1" value="2" oninput="setGsMotorDensity(this.value)">
              </div>
              <p style="font-size: 0.75rem; color: var(--text-slate-300); line-height: 1.4;">
                Pemisahan tingkat vertikal menjamin kapasitas lalu lintas 100% tanpa delay bagi kedua belah pihak. Kemiringan ramp landai (&lt;8%) menjaga kenyamanan sendi pelari.
              </p>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Kontrol Simulasi</span>
                <span class="group-badge" id="gsSpeedBadge">1.0x</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kecepatan Animasi</span>
                  <span class="slider-badge" id="gsSpeedLabel">1x</span>
                </div>
                <input type="range" id="gsSpeedSlider" min="0.5" max="2.5" step="0.25" value="1" oninput="setGsSpeed(this.value)">
              </div>
              <div class="actions-row">
                <button class="btn-sim btn-sim-primary" id="gsPlayBtn" onclick="toggleGsPlay()" aria-label="Jeda atau mulai simulasi grade separation">
                  <span id="gsPlayText">Pause</span>
                </button>
                <button class="btn-sim btn-sim-danger" onclick="resetGsSimulation()" aria-label="Reset simulasi ke default">
                  <span>Reset</span>
                </button>
              </div>
            </div>
          </div>

          <footer class="sim-footer">
            <span>Model 3: Vertical Grade Separation Structure</span>
            <span>Ruang Lari Technical Traffic Simulation</span>
          </footer>
        </div>
      </div>

      <!-- SIMULASI 4 -->
      <div class="sim-wrapper-outer" id="sim4-anchor">
        <div class="sim-container" id="sim4-container">
          <header class="sim-header">
            <div class="sim-title-group">
              <span class="model-badge">Model 4 • Katup Pintu Air Otomatis (Smart Sluice Gate)</span>
              <h3>Simulasi Sistem Katup Pintu Air Otomatis (Batching Barrier)</h3>
              <p>Penyaringan Rombongan Motor Dinamis • Lintasan Pelari Lurus Datar Tanpa Belokan</p>
            </div>
            <div class="lane-status-badge" id="sgStatusBadge" style="background:rgba(239,68,68,0.12); color:#F87171; border-color:rgba(239,68,68,0.25);">
              <span class="status-dot" id="sgStatusDot" style="background:#EF4444;"></span>
              <span id="sgStatusText">Gerbang Ditutup • Menampung Batch Motor</span>
            </div>
          </header>

          <div class="telemetry-bar">
            <div class="telemetry-item">
              <span class="telemetry-label">Status Gerbang Katup</span>
              <span class="telemetry-val" id="sgGateStatus" style="color:#EF4444;">TERTUTUP (Merah)</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Sensor Keluar (x=295)</span>
              <span class="telemetry-val" id="sgExitSensorStatus" style="color:#34D399;">Siaga</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Sensor Masuk (Hulu x=540)</span>
              <span class="telemetry-val" id="sgEntrySensorStatus" style="color:#38BDF8;">Aman (+160px)</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Motor Lolos Batch Ini</span>
              <span class="telemetry-val" id="sgBatchCount" style="color:#60A5FA;">0 Motor</span>
            </div>
            <div class="telemetry-item">
              <span class="telemetry-label">Formasi per Grup</span>
              <span class="telemetry-val" id="sgTotalRunnersCount">8 Pelari (2B × 4K)</span>
            </div>
          </div>

          <div class="canvas-frame">
            <svg width="100%" viewBox="0 0 680 460" aria-label="Simulasi Smart Sluice Gate" style="background: #ffffff;">
              <rect x="0" y="195" width="680" height="70" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <line x1="0" y1="230" x2="680" y2="230" stroke="#ffffff" stroke-dasharray="8 6" stroke-width="2" />

              <rect x="300" y="0" width="80" height="460" fill="#e5e7eb" stroke="#d1d5db" stroke-width="0.5" />
              <line x1="340" y1="0" x2="340" y2="195" stroke="#ffffff" stroke-dasharray="8 6" stroke-width="2" />
              <line x1="340" y1="265" x2="340" y2="460" stroke="#ffffff" stroke-dasharray="8 6" stroke-width="2" />

              <rect x="300" y="195" width="80" height="70" fill="#cbd5e1" opacity="0.4" />
              <line x1="380" y1="195" x2="380" y2="265" stroke="#ef4444" stroke-width="1.5" stroke-dasharray="3 3" opacity="0.6" />
              <line x1="300" y1="195" x2="300" y2="265" stroke="#10b981" stroke-width="1.5" stroke-dasharray="3 3" opacity="0.6" />

              <rect x="300" y="90" width="80" height="95" fill="#eff6ff" stroke="#93c5fd" stroke-width="1.5" stroke-dasharray="5 3" />
              <text x="340" y="138" text-anchor="middle" font-size="7.5" font-weight="700" fill="#1e40af">KANTUNG BATCH UTARA</text>

              <rect x="300" y="275" width="80" height="95" fill="#eff6ff" stroke="#93c5fd" stroke-width="1.5" stroke-dasharray="5 3" />
              <text x="340" y="323" text-anchor="middle" font-size="7.5" font-weight="700" fill="#1e40af">KANTUNG BATCH SELATAN</text>

              <g id="sgSensorGroupEast">
                <rect x="535" y="187" width="10" height="8" rx="2" fill="#1e293b" />
                <circle id="sgEmitterEastTop" cx="540" cy="191" r="2.5" fill="#0284c7" />
                <line id="sgSensorBeamEast" x1="540" y1="195" x2="540" y2="265" stroke="#0284c7" stroke-width="2.5" stroke-dasharray="4 2" opacity="0.85" />
                <rect x="535" y="265" width="10" height="8" rx="2" fill="#1e293b" />
                <circle id="sgEmitterEastBottom" cx="540" cy="269" r="2.5" fill="#0284c7" />
                <text x="540" y="174" text-anchor="middle" font-size="8" font-weight="800" fill="#0284c7">SENSOR MASUK</text>
                <text x="540" y="184" text-anchor="middle" font-size="6.5" font-weight="700" fill="#64748b">(Hulu +160px)</text>
              </g>

              <g id="sgSensorGroupWest">
                <rect x="290" y="187" width="10" height="8" rx="2" fill="#1e293b" />
                <circle id="sgEmitterWestTop" cx="295" cy="191" r="2.5" fill="#10b981" />
                <line id="sgSensorBeamWest" x1="295" y1="195" x2="295" y2="265" stroke="#10b981" stroke-width="2.5" stroke-dasharray="3 2" opacity="0.85" />
                <rect x="290" y="265" width="10" height="8" rx="2" fill="#1e293b" />
                <circle id="sgEmitterWestBottom" cx="295" cy="269" r="2.5" fill="#10b981" />
                <text x="295" y="174" text-anchor="middle" font-size="8" font-weight="800" fill="#059669">SENSOR KELUAR</text>
                <text x="295" y="184" text-anchor="middle" font-size="6.5" font-weight="800" fill="#10b981">(BUKA INSTAN)</text>
              </g>

              <g id="sgMotorLayer"></g>
              <g id="sgRunnersLayer"></g>

              <g id="sgGateNorthGroup">
                <rect x="382" y="172" width="16" height="24" rx="3" fill="#1e293b" stroke="#0f172a" stroke-width="1" />
                <circle id="sgLightNorthRed" cx="390" cy="179" r="3.5" fill="#ef4444" stroke="#7f1d1d" stroke-width="0.5" />
                <circle id="sgLightNorthGreen" cx="390" cy="189" r="3.5" fill="#064e3b" stroke="#022c22" stroke-width="0.5" />
                <g id="sgBarrierNorthArm" transform="rotate(0, 384, 185)">
                  <line x1="0" y1="0" x2="-86" y2="0" stroke="#ef4444" stroke-width="5" stroke-dasharray="14 10" stroke-linecap="round" />
                  <circle cx="0" cy="0" r="4.5" fill="#f59e0b" />
                </g>
                <line x1="300" y1="187" x2="380" y2="187" stroke="#ffffff" stroke-width="3" />
              </g>

              <g id="sgGateSouthGroup">
                <rect x="282" y="264" width="16" height="24" rx="3" fill="#1e293b" stroke="#0f172a" stroke-width="1" />
                <circle id="sgLightSouthRed" cx="290" cy="271" r="3.5" fill="#ef4444" stroke="#7f1d1d" stroke-width="0.5" />
                <circle id="sgLightSouthGreen" cx="290" cy="281" r="3.5" fill="#064e3b" stroke="#022c22" stroke-width="0.5" />
                <g id="sgBarrierSouthArm" transform="rotate(0, 296, 275)">
                  <line x1="0" y1="0" x2="86" y2="0" stroke="#ef4444" stroke-width="5" stroke-dasharray="14 10" stroke-linecap="round" />
                  <circle cx="0" cy="0" r="4.5" fill="#f59e0b" />
                </g>
                <line x1="300" y1="273" x2="380" y2="273" stroke="#ffffff" stroke-width="3" />
              </g>

              <text x="645" y="222" font-size="9.5" font-weight="700" fill="#0F6E56">Pelari Lurus</text>
              <text x="35" y="222" font-size="9.5" font-weight="700" fill="#0F6E56">Pelari Keluar</text>
              <text x="315" y="445" text-anchor="middle" font-size="9" font-weight="700" fill="#2563EB">Sltn ke Utr</text>
              <text x="365" y="20" text-anchor="middle" font-size="9" font-weight="700" fill="#7c3aed">Utr ke Sltn</text>
            </svg>
          </div>

          <div class="sim-legend">
            <div class="legend-item">
              <span class="legend-indicator ind-runner"></span>
              <span>Pelari (Gelombang Teratur)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-gate-closed"></span>
              <span>Gerbang Tertutup (Merah)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-gate-open"></span>
              <span>Gerbang Terbuka (Hijau)</span>
            </div>
            <div class="legend-item">
              <span class="legend-indicator ind-sluice-chamber"></span>
              <span>Kantung Antrean Batch</span>
            </div>
          </div>

          <div class="controls-panel">
            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Formasi &amp; Celah Pelari</span>
                <span class="group-badge" id="sgFormationBadge">2×4 Formasi</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Baris Pelari per Grup</span>
                  <span class="slider-badge" id="sgRunnerRowsLabel">2 Baris</span>
                </div>
                <input type="range" class="slider-runner" id="sgRunnerRowsSlider" min="1" max="4" step="1" value="2" oninput="setSgRunnerRows(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kolom Pelari per Grup</span>
                  <span class="slider-badge" id="sgRunnerColsLabel">4 Kolom</span>
                </div>
                <input type="range" class="slider-runner" id="sgRunnerColsSlider" min="1" max="10" step="1" value="4" oninput="setSgRunnerCols(this.value)">
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Jeda Celah Antar-Grup (Waktu Hijau)</span>
                  <span class="slider-badge" id="sgGapLabel">480 px (~5.0s Waktu Hijau)</span>
                </div>
                <input type="range" class="slider-runner" id="sgGapSlider" min="280" max="750" step="20" value="480" oninput="setSgGap(this.value)">
              </div>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Volume Motor &amp; Katup</span>
                <span class="group-badge" id="sgMotorDensityBadge">Tingkat 2</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kepadatan Motor Masuk</span>
                  <span class="slider-badge" id="sgMotorDensityLabel">Level 2 • Normal (2.0s)</span>
                </div>
                <input type="range" class="slider-motor" id="sgMotorDensitySlider" min="1" max="5" step="1" value="2" oninput="setSgMotorDensity(this.value)">
              </div>
              <div class="actions-row" style="margin-top: 10px;">
                <button class="btn-sim" id="overrideSluiceBtn" onclick="overrideSluiceOpen()" style="background:rgba(245,158,11,0.15); color:#FBBF24; border-color:rgba(245,158,11,0.3);">
                  <span>Buka Gerbang Manual (Override)</span>
                </button>
              </div>
            </div>

            <div class="control-group">
              <div class="group-header">
                <span class="group-title">Kontrol Simulasi</span>
                <span class="group-badge" id="sgSpeedBadge">1.0x</span>
              </div>
              <div class="slider-field">
                <div class="slider-label-row">
                  <span class="slider-title">Kecepatan Animasi</span>
                  <span class="slider-badge" id="sgSpeedLabel">1x</span>
                </div>
                <input type="range" id="sgSpeedSlider" min="0.5" max="2.5" step="0.25" value="1" oninput="setSgSpeed(this.value)">
              </div>
              <div class="actions-row">
                <button class="btn-sim btn-sim-primary" id="sgPlayBtn" onclick="toggleSgPlay()" aria-label="Jeda atau mulai simulasi sluice gate">
                  <span id="sgPlayText">Pause</span>
                </button>
                <button class="btn-sim btn-sim-danger" onclick="resetSgSimulation()" aria-label="Reset simulasi ke default">
                  <span>Reset</span>
                </button>
              </div>
            </div>
          </div>

          <footer class="sim-footer">
            <span>Model 4: Automatic Sluice Gate Platoon Batching</span>
            <span>Ruang Lari Technical Traffic Simulation</span>
          </footer>
        </div>
      </div>
    </article>

    <!-- BAB 6: MATRIKS KOMPARASI -->
    <article class="article-section" id="matriks-efisiensi">
      <div class="section-header">
        <h2>6. Matriks Komparasi Efisiensi dan Rekomendasi Lapangan</h2>
        <p>Analisis perbandingan komparatif 4 model untuk penentuan keputusan oleh Race Director dan Dishub.</p>
      </div>

      <div class="table-container">
        <table class="tech-table">
          <thead>
            <tr>
              <th>Model Rekayasa</th>
              <th>Kapasitas Arus Pelari</th>
              <th>Delay Kendaraan Publik</th>
              <th>Kebutuhan Infrastruktur</th>
              <th>Kebutuhan Marshal</th>
              <th>Rekomendasi Kategori Lomba</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>1. Persimpangan Bergantian</strong></td>
              <td>Sedang (800 – 1.400 pelari/menit)</td>
              <td>Rendah (Antrean bergantian 15 – 25 detik)</td>
              <td>Road cone, barikade lipat, lampu LED tongkat</td>
              <td>Tinggi (6 – 8 personil terlatih)</td>
              <td>10K &amp; Half Marathon di simpang koridor kota sedang</td>
            </tr>
            <tr>
              <td><strong>2. Bundaran Bebas Hambatan</strong></td>
              <td>Tinggi (1.500 – 2.500 pelari/menit)</td>
              <td>Sangat Rendah (Aliran memutar kontinu)</td>
              <td>Marka pembatas fleksibel (R=114m &amp; 136m)</td>
              <td>Sedang (4 personil pemandu arah)</td>
              <td>Full Marathon &amp; event mayor dengan bundaran eksisting</td>
            </tr>
            <tr>
              <td><strong>3. Pemisahan Tingkat (Overpass)</strong></td>
              <td>Maksimal (Tanpa batas interupsi)</td>
              <td>Nol (0 detik, lalu lintas jalan terus)</td>
              <td>Struktur modular flyover baja &amp; ram aksesibilitas</td>
              <td>Minimal (2 personil pemantau)</td>
              <td>World Major Marathon / persilangan jalan arteri utama</td>
            </tr>
            <tr>
              <td><strong>4. Katup Pintu Air (Sluice Gate)</strong></td>
              <td>Tinggi (Teratur per Wave)</td>
              <td>Sedang (Tersaring per batch gelombang)</td>
              <td>Boom barrier otomatis / tali safety tarik, sensor hulu</td>
              <td>Sedang (4 personil operator gerbang)</td>
              <td>Lomba dengan skema Wave Start ketat di kawasan padat</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <!-- BAB 7: SOP HARI-H -->
    <article class="article-section" id="sop-hari-h">
      <div class="section-header">
        <h2>7. Standar Operasional Prosedur (SOP) Manajemen Lalu Lintas Hari-H</h2>
        <p>Protokol eksekusi lapangan dari tahap persiapan hingga pemulihan jaringan jalan raya.</p>
      </div>

      <div class="info-panel">
        <div class="info-panel-title">Fase 1: Pra-Event (D-30 hingga D-1)</div>
        <p class="prose-text" style="font-size:0.875rem; margin-bottom: 0;">
          Survei volume lalu lintas harian rata-rata (LHR), pengajuan izin dispensasi pemanfaatan jalan ke Ditlantas Polda dan Dinas Perhubungan, simulasi waktu Cut-Off Time (COT), serta pemasangan plang sosialisasi pengalihan arus di 12 titik simpul D-7 sebelum lomba.
        </p>
      </div>

      <div class="info-panel">
        <div class="info-panel-title">Fase 2: Eksekusi Hari-H (Jam 02.00 – 10.00 WIB)</div>
        <p class="prose-text" style="font-size:0.875rem; margin-bottom: 0;">
          Pemasangan cone dan water barrier mulai pukul 02.30 WIB. Sterilisasi lintasan oleh motor voorijder kepolisian pada H-30 menit sebelum flag-off. Penerapan Emergency Blue Corridor untuk ambulans di lajur paling kanan dengan separator kerucut lalu lintas.
        </p>
      </div>

      <div class="info-panel">
        <div class="info-panel-title">Fase 3: Pemulihan Bertahap (Rolling Road Reopening)</div>
        <p class="prose-text" style="font-size:0.875rem; margin-bottom: 0;">
          Begitu mobil penyapu (Sweeper Bus) melintasi kilometer tertentu sesuai ambang batas COT (pace 9:00 min/km), tim logistik langsung menarik cone dan membuka jalan untuk kendaraan umum. Tidak diperbolehkan menahan penutupan jalan setelah pelari terakhir melintas demi meminimalkan keluhan publik.
        </p>
      </div>
    </article>

    <!-- BAB 8: FAQ -->
    <article class="article-section" id="faq">
      <div class="section-header">
        <h2>8. Tanya Jawab Teknis (FAQ) Rekayasa Lalu Lintas Pelari</h2>
        <p>Pertanyaan umum seputar regulasi jalan, keselamatan atlet, dan izin koordinasi instansi.</p>
      </div>

      <div class="faq-list">
        <div class="faq-item">
          <div class="faq-question" onclick="toggleFaq(this)">
            <span>Apa yang dimaksud dengan rekayasa lalu lintas pelari dalam event jalan raya?</span>
            <span style="font-family:'JetBrains Mono'; font-size:1.125rem;">+</span>
          </div>
          <div class="faq-answer">
            Rekayasa lalu lintas pelari adalah metodologi ilmiah perencanaan rute, perancangan geometrik lintasan, dan pengendalian pergerakan pejalan kaki cepat (pelari) serta kendaraan bermotor guna memastikan keselamatan fisik pelari, menjaga ritme lomba tanpa hambatan (*zero choke-point*), dan menekan dampak kemacetan pada urat nadi transportasi perkotaan.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question" onclick="toggleFaq(this)">
            <span>Berapa lebar jalur minimum untuk pelari menurut Highway Capacity Manual (HCM)?</span>
            <span style="font-family:'JetBrains Mono'; font-size:1.125rem;">+</span>
          </div>
          <div class="faq-answer">
            Standar HCM merekomendasikan luas ruang minimum 1.4 hingga 2.3 meter persegi per pelari untuk mencapai Level of Service (LOS) C (kondisi aliran stabil). Pada area start corral dibutuhkan minimal 0.75 meter persegi per orang, sedangkan pada koridor lintasan terbuka dibutuhkan lebar jalan efektif minimal 3.5 meter per 500 pelari aktif per menit.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question" onclick="toggleFaq(this)">
            <span>Bagaimana cara mengendalikan persimpangan sibuk tanpa menutup total jalan raya?</span>
            <span style="font-family:'JetBrains Mono'; font-size:1.125rem;">+</span>
          </div>
          <div class="faq-answer">
            Race director dapat memilih 4 model rekayasa: (1) Sistem Persimpangan Bergantian Jalur dengan komando marshal, (2) Bundaran Aliran Bebas Konsentris yang memisahkan radius putar motor dan pelari, (3) Pemisahan Tingkat Vertikal (Overpass Ramp) berupa jembatan modular, atau (4) Sistem Katup Pintu Air Otomatis (Smart Sluice Gate) yang memanfaatkan celah waktu gelombang (wave gap).
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question" onclick="toggleFaq(this)">
            <span>Mengapa sistem Wave Start sangat krusial dalam rekayasa lalu lintas lomba lari?</span>
            <span style="font-family:'JetBrains Mono'; font-size:1.125rem;">+</span>
          </div>
          <div class="faq-answer">
            Wave start memecah konsentrasi rombongan pelari menjadi kelompok-kelompok terukur dengan jeda waktu 3 sampai 7 menit. Hal ini menjaga densitas jalan tetap pada LOS A atau B, mencegah penumpukan di titik sempit (bottle-neck), dan memberikan jendela waktu hijau bagi lalu lintas kendaraan umum di persimpangan kritis.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question" onclick="toggleFaq(this)">
            <span>Bagaimana protokol evakuasi ambulans saat rute lari sedang padat pelari?</span>
            <span style="font-family:'JetBrains Mono'; font-size:1.125rem;">+</span>
          </div>
          <div class="faq-answer">
            Rekayasa rute wajib menyediakan Koridor Medis Darurat (Emergency Blue Corridor) selebar minimal 3 meter di sisi terluar lintasan yang dipisahkan oleh traffic cone berat. Petugas marshal di setiap perempatan memegang radio frekuensi darurat khusus untuk mensterilkan lintasan dalam waktu 10 detik apabila ambulans darurat mendekat.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question" onclick="toggleFaq(this)">
            <span>Kapan jalan raya boleh dibuka kembali setelah event berlangsung?</span>
            <span style="font-family:'JetBrains Mono'; font-size:1.125rem;">+</span>
          </div>
          <div class="faq-answer">
            Pembukaan jalan dilakukan bertahap menggunakan metode Rolling Sweep Vehicle. Begitu kendaraan penutup (Sweeper Car) melewati kilometer tertentu sesuai batas Cut-Off Time (COT), tim marshal dan Dishub langsung membuka kembali barikade jalan dalam waktu maksimal 15 menit.
          </div>
        </div>
      </div>
    </article>

  </div>
</div>
@endsection

@push('scripts')
<script>
  function calculateRunnerCapacity() {
    const totalRunnersEl = document.getElementById('calcTotalRunners');
    if (!totalRunnersEl) return;
    const totalRunners = parseInt(totalRunnersEl.value, 10);
    const width = parseFloat(document.getElementById('calcCorralWidth').value);
    const length = parseFloat(document.getElementById('calcCorralLength').value);
    const waveInterval = parseFloat(document.getElementById('calcWaveInterval').value);

    document.getElementById('calcTotalRunnersVal').textContent = totalRunners.toLocaleString('id-ID');
    document.getElementById('calcCorralWidthVal').textContent = width.toFixed(1) + ' m';
    document.getElementById('calcCorralLengthVal').textContent = length.toFixed(0) + ' m';
    document.getElementById('calcWaveIntervalVal').textContent = waveInterval.toFixed(1) + ' Menit';

    const totalArea = width * length;
    document.getElementById('calcAreaVal').textContent = totalArea.toFixed(0) + ' m²';

    const idealWaveCapacity = Math.floor(totalArea / 0.85);
    const recommendedWaves = Math.max(1, Math.ceil(totalRunners / idealWaveCapacity));
    const runnersPerWave = Math.ceil(totalRunners / recommendedWaves);

    const spacePerRunner = totalArea / runnersPerWave;
    document.getElementById('calcDensityVal').textContent = spacePerRunner.toFixed(2) + ' m² / orang';

    const totalReleaseMinutes = (recommendedWaves - 1) * waveInterval;
    document.getElementById('calcTotalReleaseTime').textContent = totalReleaseMinutes.toFixed(1) + ' Menit';

    document.getElementById('calcRecommendedWaves').textContent = `${recommendedWaves} Wave (${runnersPerWave.toLocaleString('id-ID')} / wave)`;

    const losBadge = document.getElementById('calcLosBadge');
    const recBox = document.getElementById('calcRecommendationBox');

    if (spacePerRunner >= 1.2) {
      losBadge.className = 'tag-badge tag-green';
      losBadge.textContent = 'LOS A (Sangat Leluasa)';
      recBox.textContent = 'Formasi corral sangat aman dan longgar. Aliran awal akan bergerak mulus tanpa friksi bahu pelari.';
    } else if (spacePerRunner >= 0.75) {
      losBadge.className = 'tag-badge tag-green';
      losBadge.textContent = 'LOS B (Standar Aman)';
      recBox.textContent = 'Formasi corral memenuhi standar ideal World Athletics. Waktu pelepasan terkontrol baik untuk meminimalkan bottle-neck di KM 1.';
    } else if (spacePerRunner >= 0.5) {
      losBadge.className = 'tag-badge tag-orange';
      losBadge.textContent = 'LOS C / D (Mulai Padat)';
      recBox.textContent = 'Perhatian: Ruang start mulai padat. Disarankan menambah jumlah wave atau memperpanjang area corral start agar pelari tidak berdesakan.';
    } else {
      losBadge.className = 'tag-badge tag-red';
      losBadge.textContent = 'LOS E / F (Bahaya Kepadatan)';
      recBox.textContent = 'Peringatan Bahaya: Kepadatan melebihi ambang batas aman. Wajib membagi ke dalam lebih banyak wave start atau menambah lebar jalur.';
    }
  }

  function toggleFaq(el) {
    const answer = el.nextElementSibling;
    const icon = el.querySelector('span:last-child');
    if (answer.style.display === 'none' || answer.style.display === '') {
      answer.style.display = 'block';
      icon.textContent = '−';
    } else {
      answer.style.display = 'none';
      icon.textContent = '+';
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
    calculateRunnerCapacity();
    const firstFaq = document.querySelector('.faq-question');
    if (firstFaq) {
      firstFaq.nextElementSibling.style.display = 'block';
      firstFaq.querySelector('span:last-child').textContent = '−';
    }
    document.querySelectorAll('.faq-item:not(:first-child) .faq-answer').forEach(ans => {
      ans.style.display = 'none';
    });
  });

  // SIMULASI 1 LOGIC
  (function () {
    const svgNS = 'http://www.w3.org/2000/svg';
    const runnersLayer = document.getElementById('runnersLayer');
    const motorLayer = document.getElementById('motorLayer');
    const slotsAtas = document.getElementById('slotsAtas');
    const slotsBawah = document.getElementById('slotsBawah');
    const atasZone = document.getElementById('atasZone');
    const bawahZone = document.getElementById('bawahZone');
    const countAtas = document.getElementById('countAtas');
    const countBawah = document.getElementById('countBawah');
    const playBtn = document.getElementById('playBtn');
    const playText = document.getElementById('playText');
    const activeLaneText = document.getElementById('activeLaneText');
    const queueAtasText = document.getElementById('queueAtasText');
    const queueBawahText = document.getElementById('queueBawahText');
    const activeMotorsCount = document.getElementById('activeMotorsCount');
    const totalRunnersCount = document.getElementById('totalRunnersCount');
    const formationSummaryBadge = document.getElementById('formationSummaryBadge');

    const Y_MAIN = 280, Y_ATAS = 230, Y_BAWAH = 330;
    const RUNNER_COLORS = ['#0F6E56', '#993C1D', '#3C3489', '#0284c7'];
    const RUNNER_SPEED = 0.09, LOOP_LEN = 750;
    const QUEUE_MAX = 8;
    const INC_RATE = 0.0015;

    let runnerRows = 2;
    let runnerCols = 2;
    let runnerSpacing = 40;
    let spreadEvenly = false;
    let motorDensity = 2;
    let speedMul = 1;
    let playing = true;
    let elapsed = 0;
    let lastTime = null;

    const MOTOR_INTERVALS = [3500, 2000, 1300, 800, 500];
    const MOTOR_LABELS = [
      'Level 1 • Lengang (3.5s)',
      'Level 2 • Normal (2.0s)',
      'Level 3 • Ramai (1.3s)',
      'Level 4 • Padat (0.8s)',
      'Level 5 • Sangat Padat (0.5s)'
    ];

    const runners = [];
    const motors = [];

    function getRowOffsets(rows) {
      if (rows === 1) return [0];
      if (rows === 2) return [-8, 8];
      if (rows === 3) return [-12, 0, 12];
      return [-15, -5, 5, 15];
    }

    function rebuildRunners() {
      if (!runnersLayer) return;
      while (runnersLayer.firstChild) {
        runnersLayer.removeChild(runnersLayer.firstChild);
      }
      runners.length = 0;

      const offsets = getRowOffsets(runnerRows);
      const total = runnerRows * runnerCols;

      for (let c = 0; c < runnerCols; c++) {
        const baseX = spreadEvenly
          ? c * (LOOP_LEN / runnerCols)
          : c * runnerSpacing;

        for (let r = 0; r < runnerRows; r++) {
          const el = document.createElementNS(svgNS, 'circle');
          el.setAttribute('r', runnerRows >= 4 ? '5' : '6');
          const color = RUNNER_COLORS[(r + c) % RUNNER_COLORS.length];
          el.setAttribute('fill', color);
          el.setAttribute('stroke', '#ffffff');
          el.setAttribute('stroke-width', '1');
          runnersLayer.appendChild(el);

          runners.push({
            baseX: baseX + (Math.random() * 2 - 1),
            offsetY: offsets[r],
            el
          });
        }
      }

      if (totalRunnersCount) totalRunnersCount.textContent = `${total} Pelari (${runnerRows}B × ${runnerCols}K)`;
      if (formationSummaryBadge) formationSummaryBadge.textContent = `${runnerRows}×${runnerCols} Formasi`;
    }

    function makeSlots(container, cy) {
      if (!container) return [];
      const arr = [];
      for (let i = 0; i < QUEUE_MAX; i++) {
        const el = document.createElementNS(svgNS, 'circle');
        el.setAttribute('cx', (470 + i * 10).toFixed(1));
        el.setAttribute('cy', cy);
        el.setAttribute('r', '4');
        el.setAttribute('fill', 'none');
        el.setAttribute('stroke', '#9ca3af');
        container.appendChild(el);
        arr.push(el);
      }
      return arr;
    }
    const slotElsAtas = makeSlots(slotsAtas, 230);
    const slotElsBawah = makeSlots(slotsBawah, 330);

    function updateSlots(els, count) {
      const filled = Math.floor(count);
      els.forEach((el, i) => {
        if (i < filled) {
          el.setAttribute('fill', '#2563EB');
          el.setAttribute('stroke', 'none');
        } else {
          el.setAttribute('fill', 'none');
          el.setAttribute('stroke', '#9ca3af');
        }
      });
    }

    function setZoneActive(zoneEl, isActive) {
      if (!zoneEl) return;
      if (isActive) {
        zoneEl.setAttribute('stroke', '#0F6E56');
        zoneEl.setAttribute('fill', '#5DCAA5');
        zoneEl.setAttribute('fill-opacity', '0.35');
      } else {
        zoneEl.setAttribute('stroke', '#9ca3af');
        zoneEl.setAttribute('fill', 'none');
      }
    }

    let activeLane = 'atas';
    let queueAtas = 0, queueBawah = 0;
    setZoneActive(atasZone, true);
    setZoneActive(bawahZone, false);

    const marshalArm = document.getElementById('marshalArm');
    const marshalBaton = document.getElementById('marshalBaton');
    const marshalBatonTip = document.getElementById('marshalBatonTip');
    const marshalArrowTip = document.getElementById('marshalArrowTip');
    const marshalSignText = document.getElementById('marshalSignText');
    const marshalWave = document.getElementById('marshalWave');
    const guideArrowAtas = document.getElementById('guideArrowAtas');
    const guideHeadAtas = document.getElementById('guideHeadAtas');
    const guideArrowBawah = document.getElementById('guideArrowBawah');
    const guideHeadBawah = document.getElementById('guideHeadBawah');

    let currentArmAngle = -35;
    let targetArmAngle = -35;

    function updateMarshalVisual() {
      if (marshalArm) {
        marshalArm.setAttribute('transform', `rotate(${currentArmAngle.toFixed(1)}, 246, 180)`);
      }
      const isAtas = activeLane === 'atas';
      const activeColor = isAtas ? '#10b981' : '#f59e0b';

      if (marshalBaton) marshalBaton.setAttribute('stroke', activeColor);
      if (marshalBatonTip) marshalBatonTip.setAttribute('fill', activeColor);
      if (marshalArrowTip) marshalArrowTip.setAttribute('fill', activeColor);

      if (marshalSignText) {
        marshalSignText.textContent = isAtas ? 'JALUR ATAS' : 'JALUR BAWAH';
        marshalSignText.setAttribute('fill', activeColor);
      }

      if (guideArrowAtas && guideHeadAtas && guideArrowBawah && guideHeadBawah) {
        if (isAtas) {
          guideArrowAtas.setAttribute('stroke', '#10b981');
          guideArrowAtas.setAttribute('stroke-width', '3');
          guideArrowAtas.setAttribute('opacity', '0.9');
          guideHeadAtas.setAttribute('fill', '#10b981');
          guideHeadAtas.setAttribute('opacity', '0.9');

          guideArrowBawah.setAttribute('stroke', '#9ca3af');
          guideArrowBawah.setAttribute('stroke-width', '2');
          guideArrowBawah.setAttribute('opacity', '0.25');
          guideHeadBawah.setAttribute('fill', '#9ca3af');
          guideHeadBawah.setAttribute('opacity', '0.25');
        } else {
          guideArrowBawah.setAttribute('stroke', '#f59e0b');
          guideArrowBawah.setAttribute('stroke-width', '3');
          guideArrowBawah.setAttribute('opacity', '0.9');
          guideHeadBawah.setAttribute('fill', '#f59e0b');
          guideHeadBawah.setAttribute('opacity', '0.9');

          guideArrowAtas.setAttribute('stroke', '#9ca3af');
          guideArrowAtas.setAttribute('stroke-width', '2');
          guideArrowAtas.setAttribute('opacity', '0.25');
          guideHeadAtas.setAttribute('fill', '#9ca3af');
          guideHeadAtas.setAttribute('opacity', '0.25');
        }
      }
    }

    function triggerMarshalWave() {
      if (!marshalWave) return;
      marshalWave.setAttribute('r', '14');
      marshalWave.setAttribute('opacity', '0.8');
      let waveR = 14;
      let waveOp = 0.8;
      const waveTimer = setInterval(() => {
        waveR += 3;
        waveOp -= 0.08;
        if (waveOp <= 0) {
          clearInterval(waveTimer);
          marshalWave.setAttribute('opacity', '0');
        } else {
          marshalWave.setAttribute('r', waveR.toString());
          marshalWave.setAttribute('opacity', waveOp.toFixed(2));
        }
      }, 30);
    }

    window.manualSwitchLane = function () {
      activeLane = (activeLane === 'atas') ? 'bawah' : 'atas';
      setZoneActive(atasZone, activeLane === 'atas');
      setZoneActive(bawahZone, activeLane === 'bawah');
      if (activeLaneText) activeLaneText.textContent = `Jalur ${activeLane === 'atas' ? 'Atas' : 'Bawah'} Aktif`;
      targetArmAngle = (activeLane === 'atas') ? -35 : 35;
      triggerMarshalWave();
      updateMarshalVisual();
    };

    function updateSim1(dt) {
      if (!playing) return;
      elapsed += dt * speedMul;

      const diff = targetArmAngle - currentArmAngle;
      if (Math.abs(diff) > 0.5) {
        currentArmAngle += diff * 0.15;
        updateMarshalVisual();
      }

      const runnerTravel = (elapsed * RUNNER_SPEED) % LOOP_LEN;
      runners.forEach(r => {
        let currX = (r.baseX + runnerTravel) % LOOP_LEN;
        let currY;
        if (currX < 265) {
          currY = Y_MAIN + r.offsetY;
        } else if (currX <= 300) {
          const t = (currX - 265) / 35;
          const targetY = (activeLane === 'atas' ? Y_ATAS : Y_BAWAH) + r.offsetY;
          currY = (Y_MAIN + r.offsetY) + t * (targetY - (Y_MAIN + r.offsetY));
        } else if (currX <= 380) {
          currY = (activeLane === 'atas' ? Y_ATAS : Y_BAWAH) + r.offsetY;
        } else if (currX <= 415) {
          const t = (currX - 380) / 35;
          const startY = (activeLane === 'atas' ? Y_ATAS : Y_BAWAH) + r.offsetY;
          currY = startY + t * ((Y_MAIN + r.offsetY) - startY);
        } else {
          currY = Y_MAIN + r.offsetY;
        }

        r.el.setAttribute('cx', currX.toFixed(1));
        r.el.setAttribute('cy', currY.toFixed(1));
      });

      if (activeLane === 'atas') {
        queueAtas = Math.min(QUEUE_MAX, queueAtas + INC_RATE * dt * speedMul);
        queueBawah = Math.max(0, queueBawah - INC_RATE * dt * speedMul * 1.5);
      } else {
        queueBawah = Math.min(QUEUE_MAX, queueBawah + INC_RATE * dt * speedMul);
        queueAtas = Math.max(0, queueAtas - INC_RATE * dt * speedMul * 1.5);
      }

      updateSlots(slotElsAtas, queueAtas);
      updateSlots(slotElsBawah, queueBawah);

      if (countAtas) countAtas.textContent = `${Math.floor(queueAtas)}/8`;
      if (countBawah) countBawah.textContent = `${Math.floor(queueBawah)}/8`;
      if (queueAtasText) queueAtasText.textContent = `${Math.floor(queueAtas)}/8 Slot`;
      if (queueBawahText) queueBawahText.textContent = `${Math.floor(queueBawah)}/8 Slot`;

      if (Math.random() < 0.012 * speedMul) {
        const isSouth = Math.random() > 0.5;
        const mEl = document.createElementNS(svgNS, 'rect');
        mEl.setAttribute('width', '8');
        mEl.setAttribute('height', '14');
        mEl.setAttribute('rx', '3');
        mEl.setAttribute('fill', isSouth ? '#2563EB' : '#7c3aed');
        if (motorLayer) motorLayer.appendChild(mEl);

        motors.push({
          el: mEl,
          x: isSouth ? 356 : 316,
          y: isSouth ? 560 : -20,
          dir: isSouth ? -1 : 1,
          speed: 0.14,
          dead: false
        });
      }

      for (let i = motors.length - 1; i >= 0; i--) {
        const m = motors[i];
        m.y += m.dir * m.speed * dt * speedMul;
        if (m.y < -30 || m.y > 590) {
          m.dead = true;
        }
        if (m.dead) {
          if (m.el.parentNode) m.el.parentNode.removeChild(m.el);
          motors.splice(i, 1);
        } else {
          m.el.setAttribute('x', m.x.toFixed(1));
          m.el.setAttribute('y', m.y.toFixed(1));
        }
      }
      if (activeMotorsCount) activeMotorsCount.textContent = `${motors.length} Motor`;
    }

    function sim1Loop(now) {
      if (!lastTime) lastTime = now;
      const dt = Math.min(now - lastTime, 80);
      lastTime = now;
      updateSim1(dt);
      requestAnimationFrame(sim1Loop);
    }

    window.setRunnerRows = function (val) {
      runnerRows = parseInt(val, 10);
      const l = document.getElementById('runnerRowsLabel');
      if (l) l.textContent = `${runnerRows} Baris`;
      rebuildRunners();
    };

    window.setRunnerCols = function (val) {
      runnerCols = parseInt(val, 10);
      const l = document.getElementById('runnerColsLabel');
      if (l) l.textContent = `${runnerCols} Kolom`;
      rebuildRunners();
    };

    window.setRunnerSpacing = function (val) {
      runnerSpacing = parseInt(val, 10);
      const l = document.getElementById('runnerSpacingLabel');
      if (l) l.textContent = `${runnerSpacing} px`;
      rebuildRunners();
    };

    window.toggleSpread = function (checked) {
      spreadEvenly = checked;
      const spField = document.getElementById('spacingField');
      if (spField) spField.style.display = checked ? 'none' : 'flex';
      rebuildRunners();
    };

    window.setMotorDensity = function (val) {
      motorDensity = parseInt(val, 10);
      const l = document.getElementById('motorDensityLabel');
      const b = document.getElementById('motorDensityBadge');
      if (l) l.textContent = MOTOR_LABELS[motorDensity - 1];
      if (b) b.textContent = `Tingkat ${motorDensity}`;
    };

    window.setSpeed = function (val) {
      speedMul = parseFloat(val);
      const l = document.getElementById('speedLabel');
      const b = document.getElementById('speedBadge');
      if (l) l.textContent = `${speedMul}x`;
      if (b) b.textContent = `${speedMul.toFixed(1)}x`;
    };

    window.togglePlay = function () {
      playing = !playing;
      if (playText) playText.textContent = playing ? 'Pause' : 'Play';
      if (playBtn) playBtn.className = playing ? 'btn-sim btn-sim-primary' : 'btn-sim';
    };

    window.resetSimulation = function () {
      runnerRows = 2;
      runnerCols = 2;
      runnerSpacing = 40;
      spreadEvenly = false;
      motorDensity = 2;
      speedMul = 1;
      playing = true;
      activeLane = 'atas';
      targetArmAngle = -35;
      currentArmAngle = -35;
      queueAtas = 0;
      queueBawah = 0;

      setZoneActive(atasZone, true);
      setZoneActive(bawahZone, false);
      if (activeLaneText) activeLaneText.textContent = 'Jalur Atas Aktif';

      const rRows = document.getElementById('runnerRowsSlider');
      if (rRows) rRows.value = 2;
      const rRowsL = document.getElementById('runnerRowsLabel');
      if (rRowsL) rRowsL.textContent = '2 Baris';

      const rCols = document.getElementById('runnerColsSlider');
      if (rCols) rCols.value = 2;
      const rColsL = document.getElementById('runnerColsLabel');
      if (rColsL) rColsL.textContent = '2 Kolom';

      const rSpace = document.getElementById('runnerSpacingSlider');
      if (rSpace) rSpace.value = 40;
      const rSpaceL = document.getElementById('runnerSpacingLabel');
      if (rSpaceL) rSpaceL.textContent = '40 px';

      const spField = document.getElementById('spacingField');
      if (spField) spField.style.display = 'flex';
      const chk = document.getElementById('spreadCheckbox');
      if (chk) chk.checked = false;

      const mDens = document.getElementById('motorDensitySlider');
      if (mDens) mDens.value = 2;
      const mDensL = document.getElementById('motorDensityLabel');
      if (mDensL) mDensL.textContent = MOTOR_LABELS[1];
      const mDensB = document.getElementById('motorDensityBadge');
      if (mDensB) mDensB.textContent = 'Tingkat 2';

      const sSlider = document.getElementById('speedSlider');
      if (sSlider) sSlider.value = 1;
      const sL = document.getElementById('speedLabel');
      if (sL) sL.textContent = '1x';
      const sB = document.getElementById('speedBadge');
      if (sB) sB.textContent = '1.0x';

      if (playText) playText.textContent = 'Pause';
      if (playBtn) playBtn.className = 'btn-sim btn-sim-primary';

      motors.forEach(m => {
        if (m.el.parentNode) m.el.parentNode.removeChild(m.el);
      });
      motors.length = 0;

      updateMarshalVisual();
      rebuildRunners();
    };

    rebuildRunners();
    updateMarshalVisual();
    requestAnimationFrame(sim1Loop);
  })();

  // SIMULASI 2 LOGIC
  (function () {
    const svgNS = 'http://www.w3.org/2000/svg';
    const rbMotorLayer = document.getElementById('rbMotorLayer');
    const rbRunnersLayer = document.getElementById('rbRunnersLayer');
    const rbPlayBtn = document.getElementById('rbPlayBtn');
    const rbPlayText = document.getElementById('rbPlayText');
    const rbStatusText = document.getElementById('rbStatusText');
    const rbTotalRunnersCount = document.getElementById('rbTotalRunnersCount');
    const rbMotorSouthStatus = document.getElementById('rbMotorSouthStatus');
    const rbMotorNorthStatus = document.getElementById('rbMotorNorthStatus');
    const rbActiveTrackName = document.getElementById('rbActiveTrackName');
    const rbFormationBadge = document.getElementById('rbFormationBadge');
    const rbTrackAtas = document.getElementById('rbTrackAtas');
    const rbTrackBawah = document.getElementById('rbTrackBawah');

    const CX = 340, CY = 280;
    const R_ROUNDABOUT = 136;
    const R_MOTOR_ARC = 94;
    const RUNNER_COLORS = ['#0F6E56', '#993C1D', '#3C3489', '#0284c7'];
    const RB_RUNNER_SPEED = 0.088;
    const RB_MOTOR_SPEED = 0.125;
    const RB_ARC_SPEED = 0.00135;

    let rbRunnerRows = 2;
    let rbRunnerCols = 4;
    let rbSpacing = 32;
    let rbMotorDensity = 2;
    let rbSpeedMul = 1;
    let rbPlaying = true;
    let rbElapsed = 0;
    let rbLastTime = null;

    const RB_MOTOR_INTERVALS = [3500, 2200, 1400, 900, 550];
    const RB_MOTOR_LABELS = [
      'Level 1 • Lengang (3.5s)',
      'Level 2 • Normal (2.2s)',
      'Level 3 • Ramai (1.4s)',
      'Level 4 • Padat (0.9s)',
      'Level 5 • Sangat Padat (0.55s)'
    ];

    const rbRunners = [];
    const rbMotors = [];
    let rbSouthSpawnTimer = 0;
    let rbNorthSpawnTimer = 800;

    function getRowOffsets(rows) {
      if (rows === 1) return [0];
      if (rows === 2) return [-7, 7];
      if (rows === 3) return [-11, 0, 11];
      return [-14, -5, 5, 14];
    }

    function rebuildRbRunners() {
      if (!rbRunnersLayer) return;
      while (rbRunnersLayer.firstChild) {
        rbRunnersLayer.removeChild(rbRunnersLayer.firstChild);
      }
      rbRunners.length = 0;

      const offsets = getRowOffsets(rbRunnerRows);
      const groupTotal = rbRunnerRows * rbRunnerCols;

      for (let g = 0; g < 2; g++) {
        const track = (g === 0) ? 'atas' : 'bawah';

        for (let c = 0; c < rbRunnerCols; c++) {
          for (let r = 0; r < rbRunnerRows; r++) {
            const el = document.createElementNS(svgNS, 'circle');
            el.setAttribute('r', rbRunnerRows >= 4 ? '5' : '6');
            const color = RUNNER_COLORS[(r + c) % RUNNER_COLORS.length];
            el.setAttribute('fill', color);
            el.setAttribute('stroke', '#ffffff');
            el.setAttribute('stroke-width', '1');
            rbRunnersLayer.appendChild(el);

            rbRunners.push({
              groupId: g,
              track,
              col: c,
              row: r,
              offsetY: offsets[r],
              el,
              x: 0,
              y: 0
            });
          }
        }
      }

      if (rbTotalRunnersCount) rbTotalRunnersCount.textContent = `${groupTotal} Pelari per Grup (${rbRunnerRows}B × ${rbRunnerCols}K)`;
      if (rbFormationBadge) rbFormationBadge.textContent = `${rbRunnerRows}×${rbRunnerCols} (${groupTotal} Pelari)`;
    }

    function getRunnerContinuousPos(r, elapsedMs) {
      const groupSpan = (rbRunnerCols - 1) * rbSpacing;
      const groupGap = 340;
      const cycleLen = 2 * (groupSpan + groupGap);

      const groupBase = r.groupId * (groupSpan + groupGap);
      const runnerOffset = r.col * rbSpacing;
      const progress = (groupBase + runnerOffset + elapsedMs * RB_RUNNER_SPEED) % cycleLen;

      const normProgress = progress / cycleLen;
      const rawX = 730 - normProgress * 840;

      let x, y;
      if (rawX < 476 && rawX > 204) {
        const p = (476 - rawX) / 272;
        const alpha = p * Math.PI;
        x = CX + R_ROUNDABOUT * Math.cos(alpha);

        if (r.track === 'atas') {
          y = CY - R_ROUNDABOUT * Math.sin(alpha) + r.offsetY;
        } else {
          y = CY + R_ROUNDABOUT * Math.sin(alpha) + r.offsetY;
        }
      } else {
        x = rawX;
        y = 280 + r.offsetY;
      }

      return { x, y };
    }

    function checkConflictZones() {
      const trackAtasHasRunners = rbRunners.some(r => r.track === 'atas' && r.x >= 195 && r.x <= 485);
      const trackBawahHasRunners = rbRunners.some(r => r.track === 'bawah' && r.x >= 195 && r.x <= 485);
      const northCrossingBlocked = rbRunners.some(r => r.track === 'atas' && r.x >= 285 && r.x <= 395);

      let activeTrack = 'celah';
      if (trackAtasHasRunners) activeTrack = 'atas';
      else if (trackBawahHasRunners) activeTrack = 'bawah';

      return { trackAtasHasRunners, trackBawahHasRunners, northCrossingBlocked, activeTrack };
    }

    function createRbMotor(direction) {
      const el = document.createElementNS(svgNS, 'g');
      const bodyRect = document.createElementNS(svgNS, 'rect');
      bodyRect.setAttribute('x', '-5');
      bodyRect.setAttribute('y', '-9');
      bodyRect.setAttribute('width', '10');
      bodyRect.setAttribute('height', '18');
      bodyRect.setAttribute('rx', '3');
      
      const color = (direction === 'south_to_north') ? '#2563EB' : '#7c3aed';
      bodyRect.setAttribute('fill', color);
      bodyRect.setAttribute('stroke', '#ffffff');
      bodyRect.setAttribute('stroke-width', '1');

      const light = document.createElementNS(svgNS, 'circle');
      light.setAttribute('cx', '0');
      light.setAttribute('cy', '-8');
      light.setAttribute('r', '2.5');
      light.setAttribute('fill', '#fde047');

      el.appendChild(bodyRect);
      el.appendChild(light);
      if (rbMotorLayer) rbMotorLayer.appendChild(el);

      if (direction === 'south_to_north') {
        return {
          el,
          direction,
          stage: 'south_approach',
          x: 320,
          y: 560,
          angle: Math.PI * 0.5,
          rotation: 0
        };
      } else {
        return {
          el,
          direction,
          stage: 'north_approach',
          x: 360,
          y: -20,
          angle: Math.PI * 1.5,
          rotation: 180
        };
      }
    }

    function rbFrame(ts) {
      if (rbLastTime === null) rbLastTime = ts;
      const dt = Math.min(ts - rbLastTime, 50);
      rbLastTime = ts;

      if (rbPlaying) {
        rbElapsed += dt * rbSpeedMul;

        rbRunners.forEach(r => {
          const pos = getRunnerContinuousPos(r, rbElapsed);
          r.x = pos.x;
          r.y = pos.y;
          r.el.setAttribute('cx', pos.x.toFixed(1));
          r.el.setAttribute('cy', pos.y.toFixed(1));
        });

        const { trackAtasHasRunners, trackBawahHasRunners, northCrossingBlocked, activeTrack } = checkConflictZones();

        if (activeTrack === 'atas') {
          if (rbTrackAtas) rbTrackAtas.setAttribute('opacity', '0.6');
          if (rbTrackBawah) rbTrackBawah.setAttribute('opacity', '0.2');
          if (rbActiveTrackName) rbActiveTrackName.textContent = 'Grup di Putaran Atas';
          if (rbStatusText) rbStatusText.textContent = 'Jalur Atas Sibuk • Motor Selatan Jalan, Motor Utara Tunggu';
        } else if (activeTrack === 'bawah') {
          if (rbTrackBawah) rbTrackBawah.setAttribute('opacity', '0.6');
          if (rbTrackAtas) rbTrackAtas.setAttribute('opacity', '0.2');
          if (rbActiveTrackName) rbActiveTrackName.textContent = 'Grup di Putaran Bawah';
          if (rbStatusText) rbStatusText.textContent = 'Jalur Bawah Sibuk • Motor Utara Jalan, Motor Selatan Tunggu';
        } else {
          if (rbTrackAtas) rbTrackAtas.setAttribute('opacity', '0.3');
          if (rbTrackBawah) rbTrackBawah.setAttribute('opacity', '0.3');
          if (rbActiveTrackName) rbActiveTrackName.textContent = 'Celah Terbuka (Kosong)';
          if (rbStatusText) rbStatusText.textContent = 'Celah Terbuka • Kedua Arah Motor Melaju Bebas';
        }

        const southCanGo = !trackBawahHasRunners;
        if (rbMotorSouthStatus) {
          rbMotorSouthStatus.textContent = southCanGo ? 'Melaju Bebas (Lajur Kiri)' : 'Tunggu Garis Henti';
          rbMotorSouthStatus.style.color = southCanGo ? '#60A5FA' : '#FBBF24';
        }

        const northCanGo = !trackAtasHasRunners;
        if (rbMotorNorthStatus) {
          rbMotorNorthStatus.textContent = northCanGo ? 'Melaju Bebas (Lajur Kanan)' : 'Tunggu Garis Henti';
          rbMotorNorthStatus.style.color = northCanGo ? '#A78BFA' : '#FBBF24';
        }

        const spawnInterval = RB_MOTOR_INTERVALS[rbMotorDensity - 1] || 2200;

        rbSouthSpawnTimer += dt * rbSpeedMul;
        if (rbSouthSpawnTimer >= spawnInterval) {
          const isClear = !rbMotors.some(m => m.direction === 'south_to_north' && m.stage === 'south_approach' && m.y > 490);
          if (isClear) {
            rbSouthSpawnTimer = 0;
            rbMotors.push(createRbMotor('south_to_north'));
          }
        }

        rbNorthSpawnTimer += dt * rbSpeedMul;
        if (rbNorthSpawnTimer >= spawnInterval) {
          const isClear = !rbMotors.some(m => m.direction === 'north_to_south' && m.stage === 'north_approach' && m.y < 70);
          if (isClear) {
            rbNorthSpawnTimer = 0;
            rbMotors.push(createRbMotor('north_to_south'));
          }
        }

        for (let i = rbMotors.length - 1; i >= 0; i--) {
          const m = rbMotors[i];
          let canMove = true;
          let targetRot = m.rotation;

          if (m.direction === 'south_to_north') {
            if (m.stage === 'south_approach') {
              if (trackBawahHasRunners && m.y <= 455 && m.y >= 438) {
                canMove = false;
              }
              rbMotors.forEach(other => {
                if (other !== m && other.direction === 'south_to_north' && other.stage === 'south_approach' && other.y < m.y && (m.y - other.y) < 24) {
                  canMove = false;
                }
              });
              if (canMove) {
                m.y -= RB_MOTOR_SPEED * dt * rbSpeedMul;
                targetRot = 0;
                if (m.y <= 372) {
                  m.stage = 'left_arc';
                  m.angle = Math.acos(-20 / R_MOTOR_ARC);
                }
              }
            }
            else if (m.stage === 'left_arc') {
              rbMotors.forEach(other => {
                if (other !== m && other.direction === 'south_to_north' && other.stage === 'left_arc') {
                  const diff = other.angle - m.angle;
                  if (diff > 0 && diff < 0.26) canMove = false;
                }
              });
              if (canMove) {
                m.angle += RB_ARC_SPEED * dt * rbSpeedMul;
                m.x = CX + R_MOTOR_ARC * Math.cos(m.angle);
                m.y = CY + R_MOTOR_ARC * Math.sin(m.angle);
                targetRot = (m.angle * 180 / Math.PI) + 180;
                const exitAngle = 2 * Math.PI - Math.acos(-20 / R_MOTOR_ARC);
                if (m.angle >= exitAngle) {
                  m.stage = 'north_exit';
                  m.x = 320;
                  m.y = 188;
                  targetRot = 0;
                }
              }
            }
            else if (m.stage === 'north_exit') {
              if (northCrossingBlocked && m.y <= 175 && m.y >= 155) {
                canMove = false;
              }
              rbMotors.forEach(other => {
                if (other !== m && other.direction === 'south_to_north' && other.stage === 'north_exit' && other.y < m.y && (m.y - other.y) < 24) {
                  canMove = false;
                }
              });
              if (canMove) {
                m.y -= RB_MOTOR_SPEED * dt * rbSpeedMul;
                targetRot = 0;
              }
            }
          } else {
            if (m.stage === 'north_approach') {
              if (trackAtasHasRunners && m.y >= 105 && m.y <= 122) {
                canMove = false;
              }
              rbMotors.forEach(other => {
                if (other !== m && other.direction === 'north_to_south' && other.stage === 'north_approach' && other.y > m.y && (other.y - m.y) < 24) {
                  canMove = false;
                }
              });
              if (canMove) {
                m.y += RB_MOTOR_SPEED * dt * rbSpeedMul;
                targetRot = 180;
                if (m.y >= 188) {
                  m.stage = 'right_arc';
                  m.angle = -Math.acos(20 / R_MOTOR_ARC);
                }
              }
            }
            else if (m.stage === 'right_arc') {
              rbMotors.forEach(other => {
                if (other !== m && other.direction === 'north_to_south' && other.stage === 'right_arc') {
                  const diff = other.angle - m.angle;
                  if (diff > 0 && diff < 0.26) canMove = false;
                }
              });
              if (canMove) {
                m.angle += RB_ARC_SPEED * dt * rbSpeedMul;
                m.x = CX + R_MOTOR_ARC * Math.cos(m.angle);
                m.y = CY + R_MOTOR_ARC * Math.sin(m.angle);
                targetRot = (m.angle * 180 / Math.PI);
                const exitAngle = Math.acos(20 / R_MOTOR_ARC);
                if (m.angle >= exitAngle) {
                  m.stage = 'south_exit';
                  m.x = 360;
                  m.y = 372;
                  targetRot = 180;
                }
              }
            }
            else if (m.stage === 'south_exit') {
              if (trackBawahHasRunners && m.y >= 405 && m.y <= 425) {
                canMove = false;
              }
              rbMotors.forEach(other => {
                if (other !== m && other.direction === 'north_to_south' && other.stage === 'south_exit' && other.y > m.y && (other.y - m.y) < 24) {
                  canMove = false;
                }
              });
              if (canMove) {
                m.y += RB_MOTOR_SPEED * dt * rbSpeedMul;
                targetRot = 180;
              }
            }
          }

          m.rotation = targetRot;
          m.el.setAttribute('transform', `translate(${m.x.toFixed(1)}, ${m.y.toFixed(1)}) rotate(${m.rotation.toFixed(0)})`);

          if (m.y < -35 || m.y > 595) {
            if (m.el.parentNode) m.el.parentNode.removeChild(m.el);
            rbMotors.splice(i, 1);
          }
        }
      }

      requestAnimationFrame(rbFrame);
    }

    window.setRbRunnerRows = function (val) {
      rbRunnerRows = parseInt(val, 10);
      const l = document.getElementById('rbRunnerRowsLabel');
      if (l) l.textContent = `${rbRunnerRows} Baris`;
      rebuildRbRunners();
    };

    window.setRbRunnerCols = function (val) {
      rbRunnerCols = parseInt(val, 10);
      const l = document.getElementById('rbRunnerColsLabel');
      if (l) l.textContent = `${rbRunnerCols} Kolom`;
      rebuildRbRunners();
    };

    window.setRbSpacing = function (val) {
      rbSpacing = parseInt(val, 10);
      const l = document.getElementById('rbSpacingLabel');
      if (l) l.textContent = `${rbSpacing} px`;
      rebuildRbRunners();
    };

    window.setRbMotorDensity = function (val) {
      rbMotorDensity = parseInt(val, 10);
      const l = document.getElementById('rbMotorDensityLabel');
      const b = document.getElementById('rbMotorDensityBadge');
      if (l) l.textContent = RB_MOTOR_LABELS[rbMotorDensity - 1];
      if (b) b.textContent = `Tingkat ${rbMotorDensity}`;
    };

    window.setRbSpeed = function (val) {
      rbSpeedMul = parseFloat(val);
      const l = document.getElementById('rbSpeedLabel');
      const b = document.getElementById('rbSpeedBadge');
      if (l) l.textContent = `${rbSpeedMul}x`;
      if (b) b.textContent = `${rbSpeedMul.toFixed(1)}x`;
    };

    window.toggleRbPlay = function () {
      rbPlaying = !rbPlaying;
      if (rbPlayText) rbPlayText.textContent = rbPlaying ? 'Pause' : 'Play';
      if (rbPlayBtn) rbPlayBtn.className = rbPlaying ? 'btn-sim btn-sim-primary' : 'btn-sim';
    };

    window.resetRbSimulation = function () {
      rbRunnerRows = 2;
      rbRunnerCols = 4;
      rbSpacing = 32;
      rbMotorDensity = 2;
      rbSpeedMul = 1;
      rbPlaying = true;
      rbElapsed = 0;

      const rRows = document.getElementById('rbRunnerRowsSlider');
      if (rRows) rRows.value = 2;
      const rRowsL = document.getElementById('rbRunnerRowsLabel');
      if (rRowsL) rRowsL.textContent = '2 Baris';

      const rCols = document.getElementById('rbRunnerColsSlider');
      if (rCols) rCols.value = 4;
      const rColsL = document.getElementById('rbRunnerColsLabel');
      if (rColsL) rColsL.textContent = '4 Kolom';

      const rSpace = document.getElementById('rbSpacingSlider');
      if (rSpace) rSpace.value = 32;
      const rSpaceL = document.getElementById('rbSpacingLabel');
      if (rSpaceL) rSpaceL.textContent = '32 px';

      const mDens = document.getElementById('rbMotorDensitySlider');
      if (mDens) mDens.value = 2;
      const mDensL = document.getElementById('rbMotorDensityLabel');
      if (mDensL) mDensL.textContent = RB_MOTOR_LABELS[1];
      const mDensB = document.getElementById('rbMotorDensityBadge');
      if (mDensB) mDensB.textContent = 'Tingkat 2';

      const sSlider = document.getElementById('rbSpeedSlider');
      if (sSlider) sSlider.value = 1;
      const sL = document.getElementById('rbSpeedLabel');
      if (sL) sL.textContent = '1x';
      const sB = document.getElementById('rbSpeedBadge');
      if (sB) sB.textContent = '1.0x';

      if (rbPlayText) rbPlayText.textContent = 'Pause';
      if (rbPlayBtn) rbPlayBtn.className = 'btn-sim btn-sim-primary';

      rbMotors.forEach(m => {
        if (m.el.parentNode) m.el.parentNode.removeChild(m.el);
      });
      rbMotors.length = 0;
      rebuildRbRunners();
    };

    rebuildRbRunners();
    requestAnimationFrame(rbFrame);
  })();

  // SIMULASI 3 LOGIC
  (function () {
    const svgNS = 'http://www.w3.org/2000/svg';
    const gsRunnersLayer = document.getElementById('gsRunnersLayer');
    const gsMotorLayer = document.getElementById('gsMotorLayer');
    const gsTotalRunnersCount = document.getElementById('gsTotalRunnersCount');
    const gsFormationBadge = document.getElementById('gsFormationBadge');
    const gsRunnerRowsLabel = document.getElementById('gsRunnerRowsLabel');
    const gsRunnerColsLabel = document.getElementById('gsRunnerColsLabel');
    const gsSpacingLabel = document.getElementById('gsSpacingLabel');
    const gsMotorDensityLabel = document.getElementById('gsMotorDensityLabel');
    const gsMotorDensityBadge = document.getElementById('gsMotorDensityBadge');
    const gsSpeedLabel = document.getElementById('gsSpeedLabel');
    const gsSpeedBadge = document.getElementById('gsSpeedBadge');
    const gsPlayBtn = document.getElementById('gsPlayBtn');
    const gsPlayText = document.getElementById('gsPlayText');

    const RUNNER_COLORS = ['#0F6E56', '#993C1D', '#3C3489', '#0284c7'];
    const GS_RUNNER_SPEED = 0.10;
    const GS_LOOP_LEN = 760;
    const Y_BRIDGE = 225;

    let gsRunnerRows = 2;
    let gsRunnerCols = 4;
    let gsSpacing = 32;
    let gsMotorDensity = 2;
    let gsSpeedMul = 1;
    let gsPlaying = true;
    let gsElapsed = 0;
    let lastTime = null;

    const GS_MOTOR_INTERVALS = [3200, 1800, 1100, 700, 450];
    const GS_MOTOR_LABELS = [
      'Level 1 • Lengang (3.2s)',
      'Level 2 • Normal (1.8s)',
      'Level 3 • Ramai (1.1s)',
      'Level 4 • Padat (0.7s)',
      'Level 5 • Sangat Padat (0.45s)'
    ];

    const gsRunners = [];
    const gsMotors = [];

    function getRowOffsets(rows) {
      if (rows === 1) return [0];
      if (rows === 2) return [-8, 8];
      if (rows === 3) return [-12, 0, 12];
      return [-15, -5, 5, 15];
    }

    function rebuildGsRunners() {
      if (!gsRunnersLayer) return;
      while (gsRunnersLayer.firstChild) {
        gsRunnersLayer.removeChild(gsRunnersLayer.firstChild);
      }
      gsRunners.length = 0;

      const offsets = getRowOffsets(gsRunnerRows);
      const total = gsRunnerRows * gsRunnerCols;
      if (gsTotalRunnersCount) gsTotalRunnersCount.textContent = `${total} Pelari (${gsRunnerRows}B × ${gsRunnerCols}K)`;
      if (gsFormationBadge) gsFormationBadge.textContent = `${gsRunnerRows}×${gsRunnerCols} Formasi (${total} Pelari)`;

      for (let c = 0; c < gsRunnerCols; c++) {
        const baseX = c * gsSpacing;
        for (let r = 0; r < gsRunnerRows; r++) {
          const el = document.createElementNS(svgNS, 'circle');
          el.setAttribute('r', '5.5');
          const color = RUNNER_COLORS[(r + c) % RUNNER_COLORS.length];
          el.setAttribute('fill', color);
          el.setAttribute('stroke', '#ffffff');
          el.setAttribute('stroke-width', '1.2');
          gsRunnersLayer.appendChild(el);

          gsRunners.push({
            baseX: baseX,
            offsetY: offsets[r],
            el: el
          });
        }
      }
    }

    const GS_MOTOR_SPEED = 0.15;
    let spawnTimerSouth = 0;
    let spawnTimerNorth = 800;

    function createGsMotor(origin) {
      const el = document.createElementNS(svgNS, 'rect');
      el.setAttribute('width', '8');
      el.setAttribute('height', '14');
      el.setAttribute('rx', '3');
      el.setAttribute('fill', origin === 'south' ? '#2563EB' : '#7c3aed');
      if (gsMotorLayer) gsMotorLayer.appendChild(el);

      const isSouth = origin === 'south';
      return {
        el,
        origin,
        x: isSouth ? 356 : 316,
        y: isSouth ? 450 : -20,
        speed: GS_MOTOR_SPEED,
        dead: false
      };
    }

    function updateGsSim(dt) {
      if (!gsPlaying) return;
      gsElapsed += dt * gsSpeedMul;

      const runnerTravel = (gsElapsed * GS_RUNNER_SPEED) % GS_LOOP_LEN;
      gsRunners.forEach(r => {
        let currX = (r.baseX + runnerTravel) % GS_LOOP_LEN - 40;
        let currY = Y_BRIDGE + r.offsetY;

        let rad = 5.2;
        if (currX > 230 && currX < 450) {
          rad = 6.4;
        } else if (currX >= 30 && currX <= 230) {
          const factor = (currX - 30) / 200;
          rad = 5.2 + factor * 1.2;
        } else if (currX >= 450 && currX <= 650) {
          const factor = 1 - (currX - 450) / 200;
          rad = 5.2 + factor * 1.2;
        }

        r.el.setAttribute('cx', currX.toFixed(1));
        r.el.setAttribute('cy', currY.toFixed(1));
        r.el.setAttribute('r', rad.toFixed(1));
      });

      spawnTimerSouth += dt * gsSpeedMul;
      spawnTimerNorth += dt * gsSpeedMul;
      const interval = GS_MOTOR_INTERVALS[gsMotorDensity - 1];

      if (spawnTimerSouth >= interval) {
        spawnTimerSouth = 0;
        gsMotors.push(createGsMotor('south'));
      }
      if (spawnTimerNorth >= interval) {
        spawnTimerNorth = 0;
        gsMotors.push(createGsMotor('north'));
      }

      for (let i = gsMotors.length - 1; i >= 0; i--) {
        const m = gsMotors[i];
        const effDt = dt * gsSpeedMul;

        if (m.origin === 'south') {
          m.y -= m.speed * effDt;
          if (m.y < -30) m.dead = true;
        } else {
          m.y += m.speed * effDt;
          if (m.y > 470) m.dead = true;
        }

        if (m.dead) {
          if (m.el.parentNode) gsMotorLayer.removeChild(m.el);
          gsMotors.splice(i, 1);
        } else {
          m.el.setAttribute('x', m.x.toFixed(1));
          m.el.setAttribute('y', m.y.toFixed(1));
        }
      }
    }

    function gsLoop(now) {
      if (!lastTime) lastTime = now;
      const dt = Math.min(now - lastTime, 100);
      lastTime = now;
      updateGsSim(dt);
      requestAnimationFrame(gsLoop);
    }

    window.setGsRunnerRows = function (val) {
      gsRunnerRows = parseInt(val, 10);
      if (gsRunnerRowsLabel) gsRunnerRowsLabel.textContent = `${gsRunnerRows} Baris`;
      rebuildGsRunners();
    };

    window.setGsRunnerCols = function (val) {
      gsRunnerCols = parseInt(val, 10);
      if (gsRunnerColsLabel) gsRunnerColsLabel.textContent = `${gsRunnerCols} Kolom`;
      rebuildGsRunners();
    };

    window.setGsSpacing = function (val) {
      gsSpacing = parseInt(val, 10);
      if (gsSpacingLabel) gsSpacingLabel.textContent = `${gsSpacing} px`;
      rebuildGsRunners();
    };

    window.setGsMotorDensity = function (val) {
      gsMotorDensity = parseInt(val, 10);
      if (gsMotorDensityLabel) gsMotorDensityLabel.textContent = GS_MOTOR_LABELS[gsMotorDensity - 1];
      if (gsMotorDensityBadge) gsMotorDensityBadge.textContent = `Tingkat ${gsMotorDensity}`;
    };

    window.setGsSpeed = function (val) {
      gsSpeedMul = parseFloat(val);
      if (gsSpeedLabel) gsSpeedLabel.textContent = `${gsSpeedMul}x`;
      if (gsSpeedBadge) gsSpeedBadge.textContent = `${gsSpeedMul.toFixed(1)}x`;
    };

    window.toggleGsPlay = function () {
      gsPlaying = !gsPlaying;
      if (gsPlayText) gsPlayText.textContent = gsPlaying ? 'Pause' : 'Play';
      if (gsPlayBtn) gsPlayBtn.className = gsPlaying ? 'btn-sim btn-sim-primary' : 'btn-sim';
    };

    window.resetGsSimulation = function () {
      gsRunnerRows = 2;
      gsRunnerCols = 4;
      gsSpacing = 32;
      gsMotorDensity = 2;
      gsSpeedMul = 1;
      gsPlaying = true;

      const rRows = document.getElementById('gsRunnerRowsSlider');
      if (rRows) rRows.value = 2;
      if (gsRunnerRowsLabel) gsRunnerRowsLabel.textContent = '2 Baris';

      const rCols = document.getElementById('gsRunnerColsSlider');
      if (rCols) rCols.value = 4;
      if (gsRunnerColsLabel) gsRunnerColsLabel.textContent = '4 Kolom';

      const rSpace = document.getElementById('gsSpacingSlider');
      if (rSpace) rSpace.value = 32;
      if (gsSpacingLabel) gsSpacingLabel.textContent = '32 px';

      const mDens = document.getElementById('gsMotorDensitySlider');
      if (mDens) mDens.value = 2;
      if (gsMotorDensityLabel) gsMotorDensityLabel.textContent = GS_MOTOR_LABELS[1];
      if (gsMotorDensityBadge) gsMotorDensityBadge.textContent = 'Tingkat 2';

      const sSlider = document.getElementById('gsSpeedSlider');
      if (sSlider) sSlider.value = 1;
      if (gsSpeedLabel) gsSpeedLabel.textContent = '1x';
      if (gsSpeedBadge) gsSpeedBadge.textContent = '1.0x';

      if (gsPlayText) gsPlayText.textContent = 'Pause';
      if (gsPlayBtn) gsPlayBtn.className = 'btn-sim btn-sim-primary';

      gsMotors.forEach(m => {
        if (m.el.parentNode) gsMotorLayer.removeChild(m.el);
      });
      gsMotors.length = 0;
      rebuildGsRunners();
    };

    rebuildGsRunners();
    requestAnimationFrame(gsLoop);
  })();

  // SIMULASI 4 LOGIC
  (function () {
    const svgNS = 'http://www.w3.org/2000/svg';
    const sgRunnersLayer = document.getElementById('sgRunnersLayer');
    const sgMotorLayer = document.getElementById('sgMotorLayer');
    const sgBarrierNorthArm = document.getElementById('sgBarrierNorthArm');
    const sgBarrierSouthArm = document.getElementById('sgBarrierSouthArm');
    const sgLightNorthRed = document.getElementById('sgLightNorthRed');
    const sgLightNorthGreen = document.getElementById('sgLightNorthGreen');
    const sgLightSouthRed = document.getElementById('sgLightSouthRed');
    const sgLightSouthGreen = document.getElementById('sgLightSouthGreen');
    const sgSensorBeamEast = document.getElementById('sgSensorBeamEast');
    const sgSensorBeamWest = document.getElementById('sgSensorBeamWest');
    const sgEmitterEastTop = document.getElementById('sgEmitterEastTop');
    const sgEmitterWestTop = document.getElementById('sgEmitterWestTop');

    const sgGateStatus = document.getElementById('sgGateStatus');
    const sgExitSensorStatus = document.getElementById('sgExitSensorStatus');
    const sgEntrySensorStatus = document.getElementById('sgEntrySensorStatus');
    const sgBatchCount = document.getElementById('sgBatchCount');
    const sgTotalRunnersCount = document.getElementById('sgTotalRunnersCount');
    const sgStatusBadge = document.getElementById('sgStatusBadge');
    const sgStatusDot = document.getElementById('sgStatusDot');
    const sgStatusText = document.getElementById('sgStatusText');

    const sgFormationBadge = document.getElementById('sgFormationBadge');
    const sgRunnerRowsLabel = document.getElementById('sgRunnerRowsLabel');
    const sgRunnerColsLabel = document.getElementById('sgRunnerColsLabel');
    const sgGapLabel = document.getElementById('sgGapLabel');
    const sgMotorDensityBadge = document.getElementById('sgMotorDensityBadge');
    const sgMotorDensityLabel = document.getElementById('sgMotorDensityLabel');
    const sgSpeedBadge = document.getElementById('sgSpeedBadge');
    const sgSpeedLabel = document.getElementById('sgSpeedLabel');
    const sgPlayBtn = document.getElementById('sgPlayBtn');
    const sgPlayText = document.getElementById('sgPlayText');

    const SG_RUNNER_SPEED = 0.095;
    const Y_RUNNER_CORRIDOR = 230;

    let sgRunnerRows = 2;
    let sgRunnerCols = 4;
    let sgGap = 480;
    let sgMotorDensity = 2;
    let sgSpeedMul = 1;
    let sgPlaying = true;
    let sgElapsed = 0;
    let lastTime = null;

    const SG_MOTOR_INTERVALS = [3200, 2000, 1300, 850, 520];
    const SG_MOTOR_LABELS = [
      'Level 1 • Lengang (3.2s)',
      'Level 2 • Normal (2.0s)',
      'Level 3 • Ramai (1.3s)',
      'Level 4 • Padat (0.85s)',
      'Level 5 • Sangat Padat (0.52s)'
    ];

    const sgRunners = [];
    const sgMotors = [];
    let northBarrierAngle = 0;
    let southBarrierAngle = 0;
    let gateState = 'CLOSED';
    let batchCrossedCount = 0;
    let overrideTimer = 0;

    function getRowOffsets(rows) {
      if (rows === 1) return [0];
      if (rows === 2) return [-8, 8];
      if (rows === 3) return [-12, 0, 12];
      return [-15, -5, 5, 15];
    }

    function rebuildSgRunners() {
      if (!sgRunnersLayer) return;
      while (sgRunnersLayer.firstChild) {
        sgRunnersLayer.removeChild(sgRunnersLayer.firstChild);
      }
      sgRunners.length = 0;

      const offsets = getRowOffsets(sgRunnerRows);
      const total = sgRunnerRows * sgRunnerCols;
      if (sgTotalRunnersCount) sgTotalRunnersCount.textContent = `${total} Pelari (${sgRunnerRows}B × ${sgRunnerCols}K)`;
      if (sgFormationBadge) sgFormationBadge.textContent = `${sgRunnerRows}×${sgRunnerCols} Formasi`;

      for (let c = 0; c < sgRunnerCols; c++) {
        for (let r = 0; r < sgRunnerRows; r++) {
          const el = document.createElementNS(svgNS, 'circle');
          el.setAttribute('r', '5.5');
          el.setAttribute('fill', '#0F6E56');
          el.setAttribute('stroke', '#ffffff');
          el.setAttribute('stroke-width', '1');
          sgRunnersLayer.appendChild(el);

          sgRunners.push({
            col: c,
            row: r,
            offsetY: offsets[r],
            el,
            x: 0,
            y: 0
          });
        }
      }
    }

    function createSgMotor(origin) {
      const el = document.createElementNS(svgNS, 'rect');
      el.setAttribute('width', '8');
      el.setAttribute('height', '14');
      el.setAttribute('rx', '3');
      el.setAttribute('fill', origin === 'south' ? '#2563EB' : '#7c3aed');
      if (sgMotorLayer) sgMotorLayer.appendChild(el);

      const isSouth = origin === 'south';
      return {
        el,
        origin,
        x: isSouth ? 356 : 316,
        y: isSouth ? 450 : -20,
        speed: 0.13,
        dead: false,
        passedIntersection: false
      };
    }

    let spawnTimerSouth = 0;
    let spawnTimerNorth = 600;

    function updateSgSim(dt) {
      if (!sgPlaying) return;
      sgElapsed += dt * sgSpeedMul;

      const groupWidth = (sgRunnerCols - 1) * 32;
      const totalCycle = groupWidth + sgGap;
      const headProgress = (sgElapsed * SG_RUNNER_SPEED) % totalCycle;

      let frontRunnerX = 720 - headProgress;
      let tailRunnerX = frontRunnerX + groupWidth;

      sgRunners.forEach(r => {
        const rx = frontRunnerX + r.col * 32;
        const ry = Y_RUNNER_CORRIDOR + r.offsetY;
        r.x = rx;
        r.y = ry;
        r.el.setAttribute('cx', rx.toFixed(1));
        r.el.setAttribute('cy', ry.toFixed(1));
      });

      const entrySensorTripped = (frontRunnerX <= 540 && frontRunnerX >= 370);
      const exitSensorTripped = (tailRunnerX < 295);
      const intersectionBlocked = (tailRunnerX >= 295 && frontRunnerX <= 385);

      if (sgEntrySensorStatus) {
        if (entrySensorTripped) {
          sgEntrySensorStatus.textContent = 'TERDETEKSI (Siaga Tutup)';
          sgEntrySensorStatus.style.color = '#EF4444';
          if (sgEmitterEastTop) sgEmitterEastTop.setAttribute('fill', '#ef4444');
          if (sgSensorBeamEast) sgSensorBeamEast.setAttribute('stroke', '#ef4444');
        } else {
          sgEntrySensorStatus.textContent = 'Aman (+160px)';
          sgEntrySensorStatus.style.color = '#38BDF8';
          if (sgEmitterEastTop) sgEmitterEastTop.setAttribute('fill', '#0284c7');
          if (sgSensorBeamEast) sgSensorBeamEast.setAttribute('stroke', '#0284c7');
        }
      }

      if (sgExitSensorStatus) {
        if (exitSensorTripped && !intersectionBlocked) {
          sgExitSensorStatus.textContent = 'BERSIH (Buka Instan)';
          sgExitSensorStatus.style.color = '#34D399';
          if (sgEmitterWestTop) sgEmitterWestTop.setAttribute('fill', '#10b981');
          if (sgSensorBeamWest) sgSensorBeamWest.setAttribute('stroke', '#10b981');
        } else {
          sgExitSensorStatus.textContent = 'Lintasan Sibuk';
          sgExitSensorStatus.style.color = '#FBBF24';
          if (sgEmitterWestTop) sgEmitterWestTop.setAttribute('fill', '#f59e0b');
          if (sgSensorBeamWest) sgSensorBeamWest.setAttribute('stroke', '#f59e0b');
        }
      }

      if (overrideTimer > 0) {
        overrideTimer -= dt * sgSpeedMul;
        gateState = 'OPEN';
      } else {
        if (intersectionBlocked || entrySensorTripped) {
          gateState = 'CLOSED';
        } else {
          gateState = 'OPEN';
        }
      }

      if (gateState === 'OPEN') {
        northBarrierAngle = Math.min(65, northBarrierAngle + 0.28 * dt * sgSpeedMul);
        southBarrierAngle = Math.max(-65, southBarrierAngle - 0.28 * dt * sgSpeedMul);
        if (sgGateStatus) {
          sgGateStatus.textContent = 'TERBUKA (Hijau)';
          sgGateStatus.style.color = '#34D399';
        }
        if (sgStatusBadge) {
          sgStatusBadge.style.background = 'rgba(16,185,129,0.12)';
          sgStatusBadge.style.color = '#34D399';
          sgStatusBadge.style.borderColor = 'rgba(16,185,129,0.25)';
        }
        if (sgStatusDot) sgStatusDot.style.background = '#10B981';
        if (sgStatusText) sgStatusText.textContent = 'Gerbang Terbuka • Batch Motor Melintas';

        if (sgLightNorthRed) sgLightNorthRed.setAttribute('fill', '#450a0a');
        if (sgLightNorthGreen) sgLightNorthGreen.setAttribute('fill', '#10b981');
        if (sgLightSouthRed) sgLightSouthRed.setAttribute('fill', '#450a0a');
        if (sgLightSouthGreen) sgLightSouthGreen.setAttribute('fill', '#10b981');
      } else {
        northBarrierAngle = Math.max(0, northBarrierAngle - 0.35 * dt * sgSpeedMul);
        southBarrierAngle = Math.min(0, southBarrierAngle + 0.35 * dt * sgSpeedMul);
        if (sgGateStatus) {
          sgGateStatus.textContent = 'TERTUTUP (Merah)';
          sgGateStatus.style.color = '#EF4444';
        }
        if (sgStatusBadge) {
          sgStatusBadge.style.background = 'rgba(239,68,68,0.12)';
          sgStatusBadge.style.color = '#F87171';
          sgStatusBadge.style.borderColor = 'rgba(239,68,68,0.25)';
        }
        if (sgStatusDot) sgStatusDot.style.background = '#EF4444';
        if (sgStatusText) sgStatusText.textContent = 'Gerbang Ditutup • Menampung Batch Motor';

        if (sgLightNorthRed) sgLightNorthRed.setAttribute('fill', '#ef4444');
        if (sgLightNorthGreen) sgLightNorthGreen.setAttribute('fill', '#064e3b');
        if (sgLightSouthRed) sgLightSouthRed.setAttribute('fill', '#ef4444');
        if (sgLightSouthGreen) sgLightSouthGreen.setAttribute('fill', '#064e3b');
      }

      if (sgBarrierNorthArm) sgBarrierNorthArm.setAttribute('transform', `rotate(${northBarrierAngle.toFixed(1)}, 384, 185)`);
      if (sgBarrierSouthArm) sgBarrierSouthArm.setAttribute('transform', `rotate(${southBarrierAngle.toFixed(1)}, 296, 275)`);
      if (sgBatchCount) sgBatchCount.textContent = `${batchCrossedCount} Motor`;

      spawnTimerSouth += dt * sgSpeedMul;
      spawnTimerNorth += dt * sgSpeedMul;
      const interval = SG_MOTOR_INTERVALS[sgMotorDensity - 1];

      if (spawnTimerSouth >= interval) {
        spawnTimerSouth = 0;
        sgMotors.push(createSgMotor('south'));
      }
      if (spawnTimerNorth >= interval) {
        spawnTimerNorth = 0;
        sgMotors.push(createSgMotor('north'));
      }

      const effDt = dt * sgSpeedMul;
      let southQueueRank = 0;
      let northQueueRank = 0;

      const southMotors = sgMotors.filter(m => m.origin === 'south');
      const northMotors = sgMotors.filter(m => m.origin === 'north');

      southMotors.forEach(m => {
        if (m.y <= 270) {
          m.y -= m.speed * effDt;
          if (!m.passedIntersection && m.y < 195) {
            m.passedIntersection = true;
            batchCrossedCount++;
          }
        } else {
          if (gateState === 'OPEN' && northBarrierAngle > 25) {
            m.y -= m.speed * effDt;
            if (!m.passedIntersection && m.y < 195) {
              m.passedIntersection = true;
              batchCrossedCount++;
            }
          } else {
            const targetStopY = 276 + southQueueRank * 18;
            if (m.y > targetStopY) {
              m.y = Math.max(targetStopY, m.y - m.speed * effDt);
            }
            southQueueRank++;
          }
        }
        if (m.y < -30) m.dead = true;
      });

      northMotors.forEach(m => {
        if (m.y >= 190) {
          m.y += m.speed * effDt;
          if (!m.passedIntersection && m.y > 265) {
            m.passedIntersection = true;
            batchCrossedCount++;
          }
        } else {
          if (gateState === 'OPEN' && southBarrierAngle < -25) {
            m.y += m.speed * effDt;
            if (!m.passedIntersection && m.y > 265) {
              m.passedIntersection = true;
              batchCrossedCount++;
            }
          } else {
            const targetStopY = 180 - northQueueRank * 18;
            if (m.y < targetStopY) {
              m.y = Math.min(targetStopY, m.y + m.speed * effDt);
            }
            northQueueRank++;
          }
        }
        if (m.y > 480) m.dead = true;
      });

      for (let i = sgMotors.length - 1; i >= 0; i--) {
        const m = sgMotors[i];
        if (m.dead) {
          if (m.el.parentNode) sgMotorLayer.removeChild(m.el);
          sgMotors.splice(i, 1);
        } else {
          m.el.setAttribute('x', m.x.toFixed(1));
          m.el.setAttribute('y', m.y.toFixed(1));
        }
      }
    }

    function sgLoop(now) {
      if (!lastTime) lastTime = now;
      const dt = Math.min(now - lastTime, 100);
      lastTime = now;
      updateSgSim(dt);
      requestAnimationFrame(sgLoop);
    }

    window.setSgRunnerRows = function (val) {
      sgRunnerRows = parseInt(val, 10);
      if (sgRunnerRowsLabel) sgRunnerRowsLabel.textContent = `${sgRunnerRows} Baris`;
      rebuildSgRunners();
    };

    window.setSgRunnerCols = function (val) {
      sgRunnerCols = parseInt(val, 10);
      if (sgRunnerColsLabel) sgRunnerColsLabel.textContent = `${sgRunnerCols} Kolom`;
      rebuildSgRunners();
    };

    window.setSgGap = function (val) {
      sgGap = parseInt(val, 10);
      const approxTime = (sgGap / (SG_RUNNER_SPEED * 1000)).toFixed(1);
      if (sgGapLabel) sgGapLabel.textContent = `${sgGap} px (~${approxTime}s Waktu Hijau)`;
      rebuildSgRunners();
    };

    window.setSgMotorDensity = function (val) {
      sgMotorDensity = parseInt(val, 10);
      if (sgMotorDensityLabel) sgMotorDensityLabel.textContent = SG_MOTOR_LABELS[sgMotorDensity - 1];
      if (sgMotorDensityBadge) sgMotorDensityBadge.textContent = `Tingkat ${sgMotorDensity}`;
    };

    window.setSgSpeed = function (val) {
      sgSpeedMul = parseFloat(val);
      if (sgSpeedLabel) sgSpeedLabel.textContent = `${sgSpeedMul}x`;
      if (sgSpeedBadge) sgSpeedBadge.textContent = `${sgSpeedMul.toFixed(1)}x`;
    };

    window.overrideSluiceOpen = function () {
      overrideTimer = 3500;
    };

    window.toggleSgPlay = function () {
      sgPlaying = !sgPlaying;
      if (sgPlayText) sgPlayText.textContent = sgPlaying ? 'Pause' : 'Play';
      if (sgPlayBtn) sgPlayBtn.className = sgPlaying ? 'btn-sim btn-sim-primary' : 'btn-sim';
    };

    window.resetSgSimulation = function () {
      sgRunnerRows = 2;
      sgRunnerCols = 4;
      sgGap = 480;
      sgMotorDensity = 2;
      sgSpeedMul = 1;
      sgPlaying = true;
      gateState = 'CLOSED';
      batchCrossedCount = 0;
      overrideTimer = 0;

      const rRows = document.getElementById('sgRunnerRowsSlider');
      if (rRows) rRows.value = 2;
      if (sgRunnerRowsLabel) sgRunnerRowsLabel.textContent = '2 Baris';

      const rCols = document.getElementById('sgRunnerColsSlider');
      if (rCols) rCols.value = 4;
      if (sgRunnerColsLabel) sgRunnerColsLabel.textContent = '4 Kolom';

      const rGap = document.getElementById('sgGapSlider');
      if (rGap) rGap.value = 480;
      if (sgGapLabel) sgGapLabel.textContent = '480 px (~5.0s Waktu Hijau)';

      const mDens = document.getElementById('sgMotorDensitySlider');
      if (mDens) mDens.value = 2;
      if (sgMotorDensityLabel) sgMotorDensityLabel.textContent = SG_MOTOR_LABELS[1];
      if (sgMotorDensityBadge) sgMotorDensityBadge.textContent = 'Tingkat 2';

      const sSlider = document.getElementById('sgSpeedSlider');
      if (sSlider) sSlider.value = 1;
      if (sgSpeedLabel) sgSpeedLabel.textContent = '1x';
      if (sgSpeedBadge) sgSpeedBadge.textContent = '1.0x';

      if (sgPlayText) sgPlayText.textContent = 'Pause';
      if (sgPlayBtn) sgPlayBtn.className = 'btn-sim btn-sim-primary';

      sgMotors.forEach(m => {
        if (m.el.parentNode) m.el.parentNode.removeChild(m.el);
      });
      sgMotors.length = 0;
      rebuildSgRunners();
    };

    rebuildSgRunners();
    requestAnimationFrame(sgLoop);
  })();
</script>
@endpush
