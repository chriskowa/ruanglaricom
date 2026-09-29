# Redesign Hero Halaman /jadwal-lari Implementation Plan

## Repository Research

### Kondisi Hero Saat Ini (events/landing.blade.php)
1. **Latar Gambar Foto Cinematic Marathon** `marathon-hero-cinematic.jpg` sebagai `hero-cinematic-bg` overlay 75% gelap → user **tidak cocok dengan foto sebagai latar hero** (akun user feedback).
2. **Arbitrary Hex Color `#08111F` / `#B7FF00` via `<style>` inline + inline `style=""`** → dilarang design-intelligence. Tidak konsisten dengan standar Tailwind palette Ruang Lari (`bg-slate-950`/`slate-900`/`slate-800`). Aksen lime #B7FF00 (text-shadow glow 25px, box-shadow glow 20px, neon badge dot glow with box-shadow) → excessive glow AI slop (R-13 dose cap).
3. **Headline H1:** `hero-headline` → font weight 900, **ALL UPPERCASE**, letter-spacing -0.025, font-family Inter (tidak sesuai pakem design-intelligence: H1 wajib pakai **Inter Tight/Sora font-weight 800 tracking -0.03, TIDAK uppercase untuk judul umum platform**).
4. **Badge:** `hero-badge` → dot 6px dengan `box-shadow: 0 0 8px` neon glow + uppercase tracking 0.08em all caps → excessive decoration tanpa purpose.
5. **Glassmorphism:** Card `floating-race-card` kanan menggunakan `backdrop-blur(16px)` + `background rgba(13,21,39,0.94)` (R-10 excessive glassmorphism dose cap: hanya boleh 1-2 elemen, ini dipakai di SEMUA upcoming card + header transparan).
6. **Blob decoration:** 2x `blur-3xl rounded-full` (lime accent ambient glow blob) + no purpose (R-01 default tanpa purpose + R-07 background pattern AI slop).
7. **Button primary:** inline style override `#ffffffff` putih sebagai background + uppercase letter-spacing 0.05 font-900 (bukan pakem: primary CTA bg-lime text-slate-900).
8. **Typography body:** Inter (tidak sesuai pakem: **Plus Jakarta Sans / Inter body**).
9. **Layout kolom:** lg 7 + 5 col, max-width 1280 px 12 col grid (OK, tapi padding container arbitrary).
10. **Trust Statistics 500+/34/10K+** → R-17: Perlu verifikasi angka asli dari DB (jika tidak ada data asli harus hilangkan / pakai data dari count query yang sebenarnya, bukan hardcoded).

### User Request Explicit:
- Hero GAMBARNYA diganti (user "gak cocok sekali gambarnya").
- Desain yang **ramah, nyaman, enak dipandang, UI UX terbaik**.
- Domain page: **KALENDER EVENT LARI INDONESIA** (bukan VDOT calculator / coach assessment). Primary action: **cari event** dan **submit event untuk EO**.

### Standar Pakem Wajib Berlaku (design-intelligence skill):
1. **Heading (H1/H2):** `font-family: 'Inter Tight', 'Sora', sans-serif; font-weight: 800; letter-spacing: -0.03em; line-height: 1.05-1.15` → **DILARANG uppercase untuk judul umum**.
2. **Body:** `'Plus Jakarta Sans', 'Inter', sans-serif; text-sm text-slate-200 leading-relaxed`.
3. **Color Palette (Maks 3 core + 1 accent):**
   - Kanvas hero: `bg-slate-950` (bukan arbitrary `#08111F`)
   - Elevated surface card: `bg-slate-900` + `border border-slate-800` (solid opak, TIDAK backdrop-blur)
   - Subelemen: `bg-slate-800`
   - **Primary accent (hanya CTA primary):** Brand lime (gunakan `bg-lime-400` atau `bg-emerald-500` Tailwind standard, JANGAN arbitrary `#B7FF00`).
4. **Border radius:** Button/Input `rounded-md`, Card/Panel `rounded-lg`, Badge `rounded` (tidak rounded-full / pill, tidak rounded-2xl berlebihan).
5. **ZERO Blob / Blur / Glow neon ambient (R-01 / R-07 / R-13 dose cap):** Hapus 2x `blur-3xl` lime.
6. **Anti Emoji / Lucide berlebihan (R-04):** Setiap baris meta tidak perlu FontAwesome icon; cukup typography hierarchy.

---

## Files and Modules

| File Path | Perubahan yang Diharapkan |
|---|---|
| `resources/views/events/landing.blade.php` | 1. Hapus blok `<style>` 035-400 arbitrary hex CSS, ganti Tailwind standard utility class inline. 2. Hapus copy logic @php 11-19 gambar hero public_path (tidak dipakai). 3. Section hero L1049-1260: ganti gambar latar cinematic → **solid data-driven panel pattern** + identity motif (garis start-finish running track subtle sebagai top accent stripe). 4. Rewrite H1, subhead, CTA primary/secondary pakem tipografi. 5. Kolom kanan floating-race-card → hapus backdrop-blur, ganti solid bg-slate-900 border-slate-800, hapus ambient glow blob. 6. Trust stats: query hitung asli dari model (bukan hardcoded 500+). 7. Mobile breakpoint test. |
| `app/Http/Controllers/PublicRunningEventController.php` L200-210 index() | Inject variable `$statsCount` (count events, count unique cities, count submissions) agar trust stats menampilkan DATA ASLI DARI DB (R-17 no fake data). Atau langsung compact count query ke view. |

---

## Design Read (Liveliness Dials - antislop R-37)
> Reading this as: **Public Jadwal Lari Landing** untuk pelari & EO Indonesia, dalam **Athletic Calm Editorial** (tidak hype, tidak neon, ramah nyaman profesional) style, dial **ENERGY 2 / RHYTHM 2 / MOTION 1**.

- **ENERGY 2 (Balanced):** Tidak terlalu hype/stark (Linear tenang) tapi tidak membosankan. Aksen lime hanya di CTA primary. Headline tegas tidak teriak.
- **RHYTHM 2:** Hero asimetris 7 + 5 (main headline + card), section selanjutnya filter & list tidak perlu bento tapi grid seragam.
- **MOTION 1 (Quiet):** Transisi hover standard `transition duration-150`, tidak ada parallax/scroll reveal. Transisi antar slide carousel race card tetap 300ms.

### Identity Motif Baru (anti generic - R-20):
**Running Track Start/Finish Stripe (6 garis tipis berwarna slate-700 / lime subtle)** di **atas border bawah section hero** sebagai motif berulang yang khas Ruang Lari (kalau di swap logo pun masih terasa "ini platform lari", bukan generic SaaS). Alasannya: kalender lari = identitas dunia lari, start finish stripe = pengumuman race baru / garis start karir lari user → 100% purpose sesuai domain (lulus Purpose-Gate R-07).

---

## Implementation Steps (Dependency Order)

### Step 1: Backend — Inject COUNT Query Real untuk Trust Stats (R-17)
**File:** `PublicRunningEventController.php::index()` sebelum `return view`
1. Query hitung real:
   - `$statsTotalEvents = RunningEvent::where('is_published', true)->count();`
   - `$statsTotalCities = RunningEvent::whereNotNull('city_id')->distinct('city_id')->count('city_id');`
   - `$statsTotalSubmissions = RunningEventSubmission::count();` (atau RunningEventRegistration kalau ada)
2. Compact ketiga variable ini ke view.
3. Jika model nama beda: fallback `DB::table()` supaya tetap ada data ASLI, JANGAN hardcoded 500+.

### Step 2: Hapus @PHP Copy gambar hero arbitrary & Bersihkan inline style CSS section
**File:** `events/landing.blade.php`
1. Hapus seluruh blok `@php` L11-L19 (copy artifact `marathon_hero_cinematic.jpg` dari folder gemini → tidak lagi dipakai).
2. Hapus seluruh blok `<style>` L34-L450+ yang berisi:
   - `#events-page` variable arbitrary hex
   - `.hero-v2-container, .hero-cinematic-bg, .hero-badge, .hero-headline, .text-lime-highlight, .btn-hero-primary, .btn-hero-secondary, .floating-race-card, .btn-card-action, .ep-hero (old fallback)`
3. Ganti `#events-page` wrapper tetap ada, tapi background pakai `bg-slate-950`, font body pakai Tailwind class `font-sans` (Plus Jakarta Sans / Inter sesuai layout global pacerhub), TIDAK set `font-family: Inter` inline style.

### Step 3: Redesign Hero Container + Identity Motif (TANPA GAMBAR FOTO LATAR)
**Area L1049-L1062 Section hero-v2-container:**
1. HAPUS: `<div class="hero-cinematic-bg">` + `<img>` + overlay + blob L1051-1060.
2. Ganti section root class menjadi:
   ```html
   <section class="relative overflow-hidden border-b border-slate-800 bg-slate-950">
       <!-- Identity Motif: Running Track Start-Finish 6 stripes atas border bawah -->
       <div class="absolute bottom-0 left-0 right-0 h-1 grid grid-cols-6 gap-[3px] px-0">
           <span class="bg-slate-800"></span><span class="bg-lime-500/60"></span>
           <span class="bg-slate-800"></span><span class="bg-lime-500/60"></span>
           <span class="bg-slate-800"></span><span class="bg-lime-500/60"></span>
       </div>
       <div class="relative max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 py-16 lg:py-24">
   ```
   - **Purpose (R-31 / C-1):** Kanvas SOLID slate-950 tanpa foto = user "cocok" (tidak ada gambar yang mengganggu), enak dipandang (high contrast), identity stripe start finish = domain lari, TIDAK ada foto placeholder.

### Step 4: Rewrite Hero Headline, Subheadline, CTA (pakem typografi + anti uppercase + CTA spesifik R-15)
**Area L1067-L1115 Left Column:**
1. **Badge (HILANGKAN glow dot + neon):**
   ```html
   <span class="inline-flex items-center gap-2 rounded bg-slate-900 border border-slate-800 px-2.5 py-1 text-xs font-semibold text-lime-400">
     <span class="inline-block w-1.5 h-1.5 rounded-full bg-lime-400"></span>
     Update Terbaru 2026
   </span>
   ```
   Purpose (R-09): Badge label status konten, bukan decoration neon.
2. **H1 Headline (Inter Tight 800, TIDAK uppercase, tracking -0.03, title case):**
   ```html
   <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-[1.05] tracking-tight">
     Jadwal Lari Indonesia
     <span class="block text-lime-400 mt-1">Terlengkap & Terpercaya</span>
   </h1>
   ```
   Reason: sesuai pakem design-intelligence Inter Tight 800. Highlight benefit spesifik "Terlengkap & Terpercaya" bukan uppercase generic.
3. **Subheadline (Plus Jakarta Sans, text-slate-300 leading-relaxed):**
   ```html
   <p class="mt-5 text-sm sm:text-base text-slate-300 leading-relaxed max-w-xl">
     Jelajahi event fun run, 5K, 10K, setengah marathon, marathon, hingga trail dari seluruh Indonesia. Semua informasi tanggal, lokasi, kategori jarak, dan link pendaftaran resmi dalam satu halaman.
   ```
   Reason: lebih jelas (sebutkan kategori jarak + link resmi, bukan cuma "satu kalender").
4. **CTA 2 Button (R-15 spesifik, R-11 radius md, TIDAK uppercase + glow):**
   - Primary CTA scroll filter:
     ```html
     <a href="#filter-form"
        onclick="document.getElementById('filter-form')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
        class="inline-flex items-center justify-center gap-2 rounded-md bg-lime-500 hover:bg-lime-400 text-slate-950 font-semibold px-5 py-3 text-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-lime-400 focus:ring-offset-2 focus:ring-offset-slate-950">
       Cari Event Sekarang
     </a>
     ```
   - Secondary CTA Submit Event:
     ```html
     <button id="btn-open-submit-event" type="button"
        class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-900 border border-slate-700 hover:bg-slate-800 hover:border-slate-600 text-white font-medium px-5 py-3 text-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-slate-950">
       <span class="text-lime-400 font-bold">+</span>
       Submit Event Gratis
     </button>
     ```
   Reason CTA (R-15): Bukan generic "Get Started / Learn More". User page ini punya 2 goal pencari event (cari) & EO (submit). Font weight normal-medium, tidak perlu 900 uppercase lagi.
5. **Trust Stats (DATA ASLI DARI BACKEND step 1, R-17 no fake):**
   ```html
   <div class="mt-12 pt-8 border-t border-slate-800 grid grid-cols-3 gap-4 max-w-lg">
     <div>
       <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-mono tabular-nums">{{ $statsTotalEvents }}<span class="text-lime-400">+</span></div>
       <div class="mt-1 text-xs text-slate-400 font-medium">Event Terdaftar</div>
     </div>
     <div class="border-l border-slate-800 pl-4 sm:pl-6">
       <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-mono tabular-nums">{{ $statsTotalCities }}</div>
       <div class="mt-1 text-xs text-slate-400 font-medium">Kota</div>
     </div>
     <div class="border-l border-slate-800 pl-4 sm:pl-6">
       <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-mono tabular-nums">{{ $statsTotalSubmissions }}<span class="text-lime-400">+</span></div>
       <div class="mt-1 text-xs text-slate-400 font-medium">Pendaftaran Masuk</div>
     </div>
   </div>
   ```
   Reason (R-17, C-5, R-36): Semua angka pakai count query DB asli, bukan hardcoded 500+/10K+. Tabular-nums font-mono untuk data (pakem telemetry design-intelligence).

### Step 5: Redesign Upcoming Race Card (Hapus Glassmorphism + Glow + Solid Surface R-10)
**Area L1119-L1260 Right Column floating-race-card:**
1. Hapus `backdrop-blur(16px)`, `rgba(13,21,39,0.94)`, glow blob L1134.
2. Ganti wrapper card:
   ```html
   <div class="relative bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-xl">
   ```
3. **Card Header UPCOMING RACE:** hilangkan glow dot. Label pakai text-slate-300 font-medium, uppercase TIDAK perlu berlebihan (label fungsional). Days HARI LAGI badge tetap (karena fungsional countdown).
4. **Hapus FA icon untuk setiap meta (R-04 dose):** Untuk calendar + location: gunakan DIV 2 baris text sendiri tanpa icon. Cukup bold label warna slate-400 "Tanggal" / "Lokasi" dengan typography hierarchy:
   ```html
   <div class="mt-3 space-y-2 text-sm">
     <div class="flex gap-3">
       <span class="w-16 text-slate-500 font-medium">Tanggal</span>
       <span class="text-slate-200">{{ $race->start_at ? ... }}</span>
     </div>
     <div class="flex gap-3">
       <span class="w-16 text-slate-500 font-medium">Lokasi</span>
       <span class="text-slate-200 line-clamp-1">{{ $race->city?->name }}</span>
     </div>
   </div>
   ```
   Purpose (R-04): FA icon terlalu banyak untuk UI statis; label text "Tanggal/Lokasi" lebih jelas untuk mata daripada icon FA.
5. **Button CTA Detail Race:**
   ```html
   <a href="{{ $race->public_url }}" class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-md bg-lime-500 hover:bg-lime-400 text-slate-950 font-semibold px-4 py-2.5 text-sm transition">Lihat Detail Race</a>
   ```
   (Hapus arrow icon; CTA cukup text.)

### Step 6: Audit Semua Inline `style="color: #..."` atau `bg-[#...]` di Bawahnya Section
- Grep untuk setiap `style="...#..."` di bawah section hero, pastikan juga filter form dan list event card TIDAK ada arbitrary hex (jangan sampai hero udah bersih tapi section bawah masih berantakan). Jika ada → ganti dengan Tailwind standard.

### Step 7: Responsive Mobile Breakpoint Audit (R-03)
1. Mobile ≤ 640px:
   - H1 `text-3xl` tidak overflow
   - 2 CTA full-width stack (flex-col) agar tap target 44px tinggi (R-03 minimum tap target).
   - Stats 3 kolom tetap 3 kolom tapi text `text-2xl` tidak break line
   - Upcoming race card margin top 1rem (stack di bawah headline di mobile, bukan side-by-side)

---

## Dependencies and Considerations
- **Route existing `/jadwal-lari` tetap, nama variable Blade `$events`, `$featuredEvents`, `$cities`, `$raceTypes`, `$raceDistances`, `$mapEvents` TIDAK DIUBAH (backward compat).**
- Submit Event button ID `btn-open-submit-event` TIDAK DIUBAH. Target anchor `#filter-form` tidak diubah agar onclick scroll tetap work.
- Mapbox script L31 & FA CSS L32 TETAP di @push styles (dipakai section map & list event icons, tidak dihapus).
- **Tabular-nums untuk stats** untuk stabil digit (tidak goyang jika angka 1 vs 8) → purpose R-06 typo data.
- **Warna lime standard Tailwind:** pakai `lime-500` (bukan custom `#B7FF00` arbitrary) → tetap sesuai identitas visual Ruang Lari tanpa memaksa compile custom hex.
- Jika `RunningEventSubmission` model tidak ada (R-17), ganti `$statsTotalSubmissions` dengan:
  - `$statsTotalRunners = RunningEventRegistration::count();`
  - ATAU jika tidak ada model registration sama sekali → HAPUS kolom ke-3 (EMPTY lebih baik R-17 daripada fake claim 10K+).

---

## Validation Setelah Implementasi
1. **Visual No-Foto:** Refresh `/jadwal-lari` → Hero latar TIDAK ADA gambar foto sama sekali. Ada 6 stripe start-finish di border bawah. ✔️
2. **Typography Pakem:** H1 = title case, 800, tracking tight. Body text-sm/slate-300. Stats tabular-nums mono. ✔️
3. **No Neon/Glow:** Grep `blur-3xl`, `box-shadow` glow → 0 match di hero. No glassmorphism `backdrop-blur`. ✔️
4. **Color Tailwind Standard:** Grep `#08111F` / `#B7FF00` / arbitrary `bg-[#...]` inline style di hero → 0 match (kecuali di section lain yang TIDAK kita sentuh boleh). ✔️
5. **Trust Stats R-17:** Buka source DB / Tinker → angka events count sesuai query asli. Jangan hardcoded. ✔️
6. **CTA Buttons Functional (R-26 R-35):**
   - Klik "Cari Event Sekarang" → smooth scroll ke `#filter-form`. ✔️
   - Klik "Submit Event Gratis" → Modal submit event terbuka. ✔️
   - Carousel prev/next upcoming (jika > 1 event) → jalan. ✔️
   - "Lihat Detail Race" → menuju event page public_url. ✔️
7. **Mobile R-03:** DevTools iPhone 14 Pro. Cek: No horizontal overflow, tinggi CTA min 44px tap. ✔️
8. **WCAG AA contrast R-25:** Text slate-300 / bg-slate-950 (ratio >4.5:1). Lime-500 bg + slate-950 text (WCAG >4.5:1). ✔️

---

## Risiko dan Mitigasi
| Risiko | Penanganan |
|---|---|
| `$statsTotalEvents` query error karena model beda nama (RunningEvent vs Event) | Wrap dengan `try/catch` di Controller, fallback ke `(int) $events->total()` jika paginate. |
| Header column stats di mobile overflow 3 kolom | Jika lebar < 320px → fallback `grid-cols-1 sm:grid-cols-3` (tapi default coba 3 kolom dulu). |
| Section filter-form ID TIDAK ADA (anchor scroll broken) | Grep view terlebih dahulu sebelum final; jika tidak ada set ke `#events-list` / section filter terdekat. |
| Font Inter Tight / Plus Jakarta Sans tidak diload di layout pacerhub | Cek head layout; jika belum ada link import Google Fonts → tambahkan ke layout parent. |
| Upcoming race carousel `x-data Alpine` logic next/prev broken karena class CSS dihapus | Pastikan wrapper div `x-data` dan `@click next/prev` TIDAK dihapus; hanya ganti class CSS background/card saja. |
