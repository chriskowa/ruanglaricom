# Kalender Pelari - Phase 0 Audit Implementation Plan

## Task 1: Audit A - Framework, Routing, Authentication, Session Management
- **Status**: `completed`
- **Priority**: high
- **Depends On**: None
- **Description**:
  - Identifikasi framework version (Laravel 13, PHP 8.3)
  - Dokumentasi struktur routing: web.php (1611 baris, 10+ route group role-based) + api.php (V1 Mobile Bearer Token)
  - Dokumentasi auth system: Session guard web, custom AuthenticateApiToken middleware (bukan Sanctum default), 7 metode login (email-pass, phone-OTP, Google, Strava, signed-token, social-API, Pacer-OTP)
  - Dokumentasi session management: database driver, 120 menit idle, HttpOnly=true, SameSite=lax
  - Dokumentasi middleware kustom: RedirectAppToCanonicalDomain, SetLocaleFromSession, HandleInertiaRequests, CheckRole, AuthenticateApiToken (9 CSRF-exempt webhook)
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - `rule` TR-1.1: Path reference untuk Laravel version, route structure, auth guard, session driver semuanya valid dan file bisa di-read. Evidence: audit result mencakup `config/app.php`, `bootstrap/app.php`, `config/auth.php`, `config/session.php`, `routes/web.php`, `app/Models/User.php`, `app/Http/Middleware/*`
  - `rubric` TR-1.2: Kelengkapan mapping middleware; scale 1-5; anchors 1=hanya default Laravel, 3=default+2 custom, 5=semua 5 custom middleware + 9 CSRF-exempt didokumentasikan; threshold >=4; evidence: list table middleware + CheckRole pipe-separated support
- **Completion Evidence**:
  - Laravel 13, PHP 8.3, Vite 7 + Tailwind v4 + Vue 3 (CDN + Inertia hybrid) teridentifikasi
  - 10 route group + 4 role dashboard (admin/eo/coach/runner) terpetakan
  - 7 login methods + PersonalAccessToken custom model tercatat
  - Session driver=database, 120m idle, HttpOnly=true, SameSite=lax terverifikasi

## Task 2: Audit B-C - Existing Auth Flow + Strava Integration + Activity Data Model
- **Status**: `completed`
- **Priority**: high
- **Depends On**: Task 1
- **Description**:
  - Dokumentasi 3 OAuth flow Strava yang ada (Auth login/register, Runner Connect, Calendar Connect) + logic token exchange duplikasi
  - Dokumentasi token storage: User.strava_id, strava_access_token, strava_refresh_token, strava_expires_at (tabel users) + StravaConfig (admin level)
  - Dokumentasi StravaApiService::getValidAccessToken() refresh token flow reusable + inline fallback di Controller sync
  - Dokumentasi 2 model activity berbeda: StravaActivity (jarak meter, raw JSON payload) vs UserActivity (jarak km, internal GPX/manual)
  - Dokumentasi 12 endpoint sync activity: runner.strava.sync (UTAMA), admin challenge sync, coach athlete sync, API v1 strava sync (placeholder - BELUM hit API)
  - Dokumentasi tersedianya: detail activity dasar, splits/km, laps, streams time-series, AI Analysis (classification+evidence+suggestion) + guardrail jarak>=14km bukan interval
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - `rule` TR-2.1: Model StravaActivity dan UserActivity teridentifikasi dengan field kunci perbedaan satuan jarak (meter vs km). Evidence: `app/Models/StravaActivity.php` dan `app/Models/UserActivity.php` fields list.
  - `rule` TR-2.2: Auto-link logic ProgramSessionTracking teridentifikasi: 1 hari -> distance terbesar -> status completed + strava_link terisi. Evidence: `app/Http/Controllers/Runner/StravaController.php sync()` line ~350-420.
  - `rubric` TR-2.3: Kelengkapan coverage data activity; scale 1-5; anchors 1=hanya nama+jarak, 3=dasar+splits+laps, 5=dasar+splits+laps+streams+AI analysis+confidence scoring; threshold >=4; evidence: daftar 5 kategori data activity yang tersedia
- **Completion Evidence**:
  - 3 OAuth flow Strava terpetakan (Auth, Runner, Calendar) dengan scope masing-masing
  - Dua model activity (StravaActivity vs UserActivity) didokumentasikan beserta perbedaan satuan
  - 12 route strava endpoints terdaftar lengkap dengan fungsi
  - Data activity yang tersedia: dasar (11 field), splits (7 field), laps (7 field), streams (6 field), AI analysis (11 field + guardrail jarak>=14km)

## Task 3: Audit D - Database/Data Models Relevant to KalenderPelari
- **Status**: `completed`
- **Priority**: high
- **Depends On**: Task 1
- **Description**:
  - Inventarisasi 21 model relevan: User, StravaActivity, UserActivity, Event, RunningEvent, Race, RaceSession, RaceCategory, RaceDistance, RaceType, RaceResult, Participant, Transaction, Order, OrderItem, Coupon, Program, ProgramEnrollment, ChallengeActivity, City, Province, Pacer, PacerBooking, MasterGpx
  - Dokumentasi field kunci + Accessor krusial per model (contoh: User.vdot, User.training_paces, Event.public_url, Event.sanitized_description_html, MasterGpx.route_type auto-detect, ProgramEnrollment subscription support)
  - Dokumentasi relationship diagram (1:N, N:M, polymorphic) antar model utama
  - Identifikasi 10 Catatan Arsitektur Krusial: dual Event model mode (directory/managed), alur pembelian Event Transaction->Participant, payment gateway central di Transaction, Program pricing hybrid subscription+quota, Race vs Event perbedaan scope, MasterGpx universal rute, User.PB+VDOT otomatis, Strava sync di 3 tempat, PacerBooking payment independen, Location hierarchy Province->City
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - `rule` TR-3.1: Minimal 15 model relevan terinventarisasi dengan fillable kunci + relationships. Evidence: daftar model dengan file path + field kunci
  - `rubric` TR-3.2: Kualitas relationship mapping; scale 1-5; anchors 1=hanya nama model tanpa relasi, 3=relasi FK dasar, 5=full diagram relasi + catatan arsitektur krusial (contoh: boot logic auto-slug + cache invalidation Event, optimistic locking lock_version Event, polymorphic WalletTransaction reference_type); threshold >=4
- **Completion Evidence**:
  - 24 model relevan terinventarisasi dengan field, accessor, relationship
  - Diagram hubungan antar model (ASCII) tersusun
  - 10 Catatan Arsitektur Krusial teridentifikasi (dua mode Event, Subscription ProgramEnrollment, dll)

## Task 4: Audit E-F - Storage/Image Architecture + Payment/Order Architecture
- **Status**: `completed`
- **Priority**: medium
- **Depends On**: Task 1, Task 3
- **Description**:
  - Dokumentasi storage driver: default=local (storage/app/public), S3 terkonfigurasi tapi key kosong, Private Disk jarang dipakai, Symlink public/storage
  - Dokumentasi ImageUploadService (Intervention Image 3, GD driver, 3 variant WebP 300/750/1200px quality 80, UUID naming, scale tanpa crop)
  - Dokumentasi 4 kasus lifecycle upload: Event (Dropzone->1920px WebP), Avatar (RAW tanpa resize/convert, TIDAK pakai Intervention), Marketplace (3 variant WebP), Participant (RAW base64 decode tanpa Intervention)
  - Dokumentasi 4 Payment Gateway aktif: Midtrans (Production, support sandbox per-event), Moota (signature verify di-comment RISK), QRIS Static (CRC16 inject nominal), COD (Event only)
  - Dokumentasi 4 flow pembayaran LENGKAP (5-6 step masing-masing): Event (atomic lock quota 10s -> ProcessPaidJob deposit EO wallet + bib generate), Pacer Booking (ESCROW locked_balance -> split platform_fee + pacer_payout), Program Coach (Cart->Order, wallet OR midtrans), Marketplace Product (Single-Stock Reservation 15min + RajaOngkir shipping)
  - Dokumentasi Wallet Ecosystem: Wallet.balance/locked_balance, WalletTransaction MorphTo polymorphic, WalletTopup, WalletWithdrawal, PlatformWalletService (escrow mechanism)
- **Acceptance Criteria Addressed**: AC-1, AC-2
- **Test Requirements**:
  - `rule` TR-4.1: ImageUploadService methods dan parameter output teridentifikasi. Evidence: `app/Services/ImageUploadService.php` upload(), uploadSingle(), delete(), deleteMultiple() signature
  - `rule` TR-4.2: Escrow mechanism PacerBooking terverifikasi: locked_balance saat paid, release + split saat complete. Evidence: `ProcessPaidPacerBooking` job + `PacerBookingController::complete()`
  - `rubric` TR-4.3: Kelengkapan flow payment documentation; scale 1-5; anchors 1=hanya gateway name, 3=gateway+event flow saja, 5=4 gateway+4 full flow+wallet escrow+5 risk points; threshold >=4
- **Completion Evidence**:
  - Storage: 2 disk (public+private), S3 configured tapi kosong, 1 reusable ImageUploadService + 3 kasus gap (avatar/participant tidak terkompresi)
  - Payment: 4 gateway (Midtrans/Moota/QRIS/COD) + 4 complete flow (Event/Pacer/Program/Marketplace) + 5 risk points (Moota signature disabled, pacer/program default production, avatar no resize, dll)

## Task 5: Audit G-H - Frontend Design System + Routes and Conflicts
- **Status**: `completed`
- **Priority**: medium
- **Depends On**: Task 1
- **Description**:
  - Dokumentasi stack frontend: Vite 7, Tailwind v4 (via CSS config, TANPA tailwind.config.js), Vue 3 (2 pola: CDN global untuk Blade pages + Inertia SFC untuk Run Connect), Alpine 3.13.3 (global via pacerhub), Ziggy-js route exposure
  - Dokumentasi Brand Colors via CSS: --color-neon:#ccff00 (primary accent), --color-dark:#0f172a, --color-card:#1e293b, --color-strava:#fc4c02, trik override palet blue->merah di light mode, biru normal di dark mode
  - Dokumentasi 3 layout utama: layouts/pacerhub.blade.php (UTAMA modern), layouts/app.blade.php (legacy admin template), layouts/coach.blade.php (khusus coach) + root app.blade.php (Inertia khusus Run Connect)
  - Dokumentasi reusable component pattern: layouts/components/* (global), *partials/ (modul-scoped), runner/calendar/{html,scripts,styles}.blade.php (slice per concern)
  - Dokumentasi Google Fonts: Inter (400,600,800) + JetBrains Mono (500,700) sebagai utama, TIDAK ADA Inter Tight, Bebas Neue/Oswald hanya halaman spesifik
  - Dokumentasi Font Awesome 6.5.1 pattern preload+media swap untuk Lighthouse critical path
  - Analisis konflik route: 3 kalender eksisting beda fungsi: /calendar (PUBLIK: Race + Strava Dashboard), /runner/calendar (PRIVATE: Latihan Pribadi Runner, 24 endpoint CRUD), /jadwal-lari (Arsip Event Directory). Route /kalender-pelari: BELUM ADA -> AMAN
  - Mitigasi konflik konseptual: user potentially bingung 3 kalender berbeda. Usulan: /kalender-pelari sebagai landing page SEO yang menjelaskan 3 fungsi atau redirect ke editor visual builder baru
- **Acceptance Criteria Addressed**: AC-1, AC-4
- **Test Requirements**:
  - `rule` TR-5.1: Route /kalender-pelari TIDAK ADA di routes/web.php dan route list. Evidence: grep output tidak menemukan pattern ini.
  - `rule` TR-5.2: Dua Controller berbeda namespace: CalendarController (App root, publik) vs Runner\CalendarController (Runner group, private) teridentifikasi import alias benar di web.php L3. Evidence: `routes/web.php` line ~3 use statement
  - `rubric` TR-5.3: Kualitas analisis konflik; scale 1-5; anchors 1=hanya cek URL unique, 3=cek URL+controller+view folder, 5=URL+controller+view+route name+regex whitelist username blacklist+analisis konseptual UX user bingung+mitigasi; threshold >=4
- **Completion Evidence**:
  - 4 layout + Tailwind v4 palette + Font Awesome swap pattern terdokumentasi
  - 3 kalender eksisting beda fungsi terpetakan dengan jelas
  - Route /kalender-pelari konfirmasi AMAN (belum ada), mitigasi 3 usulan konflik konseptual tercatat

## Task 6: Audit I-Q - Recommendations, Architecture, Entities, API, Strategy, Security, Performance, Implementation Sequence, Assumptions
- **Status**: `in_progress`
- **Priority**: medium
- **Depends On**: Task 1, 2, 3, 4, 5
- **Description**:
  - **I. Rekomendasi Arsitektur KalenderPelari**:
    - Modular Monolith extension: tambahkan namespace App\Http\Controllers\KalenderPelari + App\Models\KalenderPelari, TIDAK buat package terpisah
    - Reuse komponen eksisting: StravaApiService (activity), ImageUploadService (upload 3 variant WebP), Midtrans wrapper via Transaction/payment flow, Wallet escrow (Pacer model) untuk Creator royalty, CheckRole middleware, Event model (Race Calendar data source), Program + ProgramEnrollment (Training data source), User.accessor (VDOT, training_paces)
    - Frontend strategy: KalenderPelari Editor -> Vite Entry Point Baru bernama `kalender-pelari-editor.js` + SFC Vue 3 components (drag/drop) dipasang di blade layout pacerhub.blade.php (TIDAK perlu full Inertia page), tetap pakai Alpine untuk interaksi dasar tab/sidebar
    - Calendar Date Engine: Reuse Carbon (Laravel built-in) -> Indonesia locale id_ID sudah ter-set (config/app.php timezone Asia/Jakarta, locale id), Monday/Sunday start configurable
    - Pace calculation: Reuse DanielsRunningService yang sudah dipakai User.vdot accessor (TIDAK buat pace calculator baru dari scratch)
  - **J. Entity/Migration Proposal (bandingkan 25 model MS Spec dengan eksisting)**:
    - REUSE EXISTING (14): User, StravaActivity, UserActivity, Event, Race, RaceResult, RaceCategory, RaceDistance, Program, ProgramEnrollment, Order (atau Transaction), OrderItem/Participant, City, Province
    - EXTEND EXISTING (2): User (tambah creator_profile_id FK opsional), Event (juga dijadikan Race Calendar source, field sudah ada)
    - BUAT BARU (9, tidak ada di eksisting):
      1. `kp_calendar_projects` (CalendarProject): id, user_id(nullable untuk anonymous trial UUID), name, year, format_type (A3L/A4L/Desk/Square), template_id, cover_asset_id, is_anonymous, state_draft(JSON), snapshot_version, status, expires_at(untuk trial), created_at, updated_at
      2. `kp_calendar_pages` (CalendarPage): id, project_id, month_number(0=cover, 1-12=Jan-Dec,13=YearReview), page_type, canvas_width_mm, canvas_height_mm, layout_config(JSON)
      3. `kp_calendar_elements` (CalendarElement): id, page_id, element_type (photo/text/calendar_grid/goal/workout/race/stats/habit/shoe/note/pb/progress), x_mm, y_mm, width_mm, height_mm, rotation_deg, z_index, locked, visible, style_config(JSON), content_json, data_binding(JSON - reference ke StravaActivity id / Event id / Program id / User pb), asset_id
      4. `kp_uploaded_assets` (UploadedAsset - lifecycle): id, user_id(nullable untuk anonymous), project_id, asset_key(object storage), mime_type, width_px, height_px, file_size_bytes, status(LOCAL_ONLY/TEMP_UPLOAD/SAVED_PROJECT/PENDING_CHECKOUT/PAID_ACTIVE/EXPIRED/DELETED), original_filename, created_at, expires_at(otomatis TEMP_UPLOAD 24h)
      5. `kp_templates` (Template): id, name, slug, family(MINIMAL_RUNNER/RACE_SEASON/RUNNING_MEMORIES/MARATHON_BUILD/TRAIL_YEAR/RUN_CLUB/PERSONAL_BEST/CLEAN_GRID), is_premium, price, creator_id (user), thumbnail_asset_id, status(draft/published), category, description
      6. `kp_template_elements` (TemplateElement): id, template_id, page_type, element_type, default_x, default_y, default_width, default_height, style_default(JSON), binding_template (contoh: {{month.totalDistance}})
      7. `kp_project_snapshots` (ProjectSnapshot - immutable saat checkout): id, project_id, project_document(JSON penuh), print_spec_id, created_by_user_id, created_at (TIDAK BOLEH diedit setelah create)
      8. `kp_print_specifications` (PrintSpecification): id, product_type(A3_WALL/A4_WALL/DESK), paper_type, cover_type, spiral_binding, quantity, unit_price_config(JSON - dari backend config, TIDAK hardcode)
      9. `kp_goals_habits_shoes_paces` (Pivot table gabungan modular): BISA DIPISAH JADI 4 TABEL KECIL: `kp_goals` (project_id, month, type(mileage/runs/longest/race/pace/strength/custom), target_value, unit), `kp_habits` (project_id, habit_name, dates_marked(JSON)), `kp_shoes` (project_id, brand, model, nickname, start_mileage, retirement_target, current_mileage), `kp_pace_profiles` (project_id, vdot_score, pace_zones(JSON - reference ke User.training_paces accessor jika terhubung auth))
      - Creator/Royalty (POST-MVP, baru dibuat jika feature): kp_creator_profiles, kp_template_purchases, kp_royalty_transactions (bisa reuse WalletTransaction dengan type='creator_royalty')
  - **K. API/Service Boundaries**:
    - Http\Kernel Middleware: Tetap pakai CheckRole + auth:sanctum (AuthenticateApiToken custom)
    - Service Boundaries BARU (src: app/Services/KalenderPelari/):
      1. `ProjectService` - CRUD CalendarProject, import trial state dari localStorage, serialize state ke kp_calendar_projects.state_draft, autosave debounce interval via job (TIDAK every pointer move)
      2. `CalendarDateEngineService` - Generate dates per month, Monday/Sunday start, locale id/en, month names Indonesia. Reuse Carbon::setLocale
      3. `ElementRendererService` - Render element drag-drop ke normalized position (mm -> pixel display converter), z-index management, snap/guides
      4. `RunnerDataBindingService` - Bridge data source: StravaActivity monthly aggregate, Event (upcoming races), Program.session (training), User.personal_best, User.shoe (kalau nanti ada UserShoe model) -> inject ke element.data_binding
      5. `TemplateService` - Apply template ke project (preserve runner data: races/activities/training/goals/PBs/photos/shoes), warn jika manual layout akan hilang
      6. `AssetLifecycleService` - Status transition LOCAL_ONLY->TEMP_UPLOAD->SAVED_PROJECT (consent upload)->PENDING_CHECKOUT->PAID_ACTIVE. Background job expire TEMP_UPLOAD after config(asset.retention_hours=24)
      7. `PrintPreflightService` - Detect missing photo, low res (<300dpi safe area), text overflow, element outside bleed, missing asset
      8. `PrintExportPipeline` (Queueable Job): Menerima ProjectSnapshot + PrintSpec -> render authoritative PDF (TIDAK screenshot browser). Bisa pakai DomPDF 3.0 (sudah ada di composer) + tcpdf fallback.
      9. `TrialHandoffService` - Saat user klik Save dari anonymous: serialize project state ke session, redirect login, restore state pasca-auth, create CalendarProject terhubung user_id. TIDAK destroy work.
    - Existing Service Boundaries (REUSE TANPA UBAH SIGNATURE):
      1. `StravaApiService::getValidAccessToken()` + refresh logic
      2. `ImageUploadService::upload()` variant WebP
      3. `DanielsRunningService::calculateVdot()` / `::trainingPaces()`
      4. `MidtransService` / `MootaService` / `QrisDynamicService`
      5. `PlatformWalletService` escrow untuk creator royalty payment split
  - **L. Public Trial Persistence Strategy**:
    - **Anonymous (TANPA login)**:
      - Project state (pages + elements): localStorage browser (max 5MB). Compress JSON dengan lz-string (bundle di kalender-pelari-editor.js entry point) jika > 200KB. Base64 TIDAK BOLEH masuk localStorage.
      - Photos preview: Blob + URL.createObjectURL() HANYA. Revoke object URL saat pindah page / switch element (TIDAK bocor memory). TIDAK upload ke server saat anonymous trial -> sesuai MS Spec 27.
      - Session laravel: simpan `trial_project_token` = UUID random + `trial_expires_at` = +7 hari (configurable) di session() Laravel. Digunakan untuk handoff.
    - **Pasca-login Handoff Flow**:
      1. User klik "SIMPAN PROYEK" (butuh auth)
      2. TrialHandoffService:
        - Serialize pages+elements dari localStorage ke JSON string -> compress
        - Simpan ke `session(['kp_trial_state' => compressed_json, 'kp_trial_assets_index' => ['nama_file_hash' => 'blob_sizes']])`
        - Redirect ke `login` dengan `?redirect_to=/kalender-pelari/editor/trial/restore`
      3. Setelah login sukses (default Laravel LoginController redirectPath), tambahkan middleware AfterLogin yang cek session key kp_trial_state:
        - Jika ada -> CalendarProject dibuat dengan user_id = Auth::id(), status dari trial -> aktif
        - Muncul modal: "Upload foto yang Anda pakai di trial? (Tanpa upload, foto hanya tersimpan sementara di browser Anda)" [Upload Semua] [Lewati Dulu]
        - Upload Semua: Iterasi trial_assets_index -> user explicit upload each file -> masuk SAVED_PROJECT via AssetLifecycleService
    - **Security**: Session kp_trial_state TIDAK berisi base64 foto (hanya metadata index). Max JSON state size 1MB per session trial.
  - **M. Image Lifecycle Strategy** (sesuai spec 28-35):
    - **3 States**:
      1. **LOCAL_ONLY** (Anonymous Trial): Blob di memory browser, TIDAK ada server footprint, revoke URL saat unmount
      2. **TEMP_UPLOAD** (User save, atau foto butuh pre-process):
         - User explicit consent (lewati checkbox consent dialog)
         - Upload via ImageUploadService ke folder `kalender-pelari/temp/{user_id}/{UUID}/` -> 3 variant WebP (small 300/medium 750/large 1200)
         - Simpan ke kp_uploaded_assets dengan status=TEMP_UPLOAD, expires_at=+24jam
         - Background Job `ExpireTempAssetsJob` (jalan setiap jam via Laravel Scheduler):
           - DELETE where status=TEMP_UPLOAD AND expires_at < now()
           - Storage::disk('public')->delete($asset->asset_key) untuk path temp
      3. **SAVED_PROJECT** (User klik simpan permanen):
         - Copy move file dari folder temp ke `kalender-pelari/projects/{project_id}/{asset-UUID}.webp`
         - Update status to SAVED_PROJECT, expires_at=null (tidak expire)
         - Di checkout -> status PENDING_CHECKOUT
         - Pasca payment sukses -> status PAID_ACTIVE (simpan minimal 180 hari, sesuai kebutuhan cetak ulang)
    - **3 Representations** (sesuai spec 31):
      1. ORIGINAL (hanya upload ke server jika user save + consent. Disimpan disk private jika ada resolusi > 4000px)
      2. EDITOR PREVIEW (variant medium 750px WebP quality 80)
      3. THUMBNAIL (variant small 300px WebP quality 80)
    - **Crop Data** (spec 32): TIDAK buat file permanen baru tiap crop. Simpan di kp_calendar_elements.style_config: {cropX, cropY, cropWidth, cropHeight, rotation, scale} (transform parameter)
    - **Security Upload** (spec 33-35):
      - Validasi server-side: filesize < 15MB per file, mimetype via finfo (bukan trust extension/$_FILES['type'] -> JPEG=image/jpeg, PNG=image/png, WebP=image/webp)
      - Gambar di-decode oleh Intervention Image untuk memastikan file adalah gambar BENAR (bukan polyglot script)
      - Object identifier: `{random_uuid_v4}_{variant}.webp` -> TIDAK pakai nama user / original filename di storage key
      - EXIF GPS stripping untuk editor-preview/thumbnail: Intervention decode->encode otomatis strip EXIF. Original retain EXIF jika user butuh (configurable).
      - Private access: Jika S3/R2 diaktifkan nanti -> Signed URL short-lived (15 menit) untuk original resolution
  - **N. Security Considerations** (sesuai spec 73-76):
    1. **Resource Authorization** (73):
       - Setiap request ke KalenderPelari endpoints (project save, asset access, PDF export) -> Policy Laravel: `CalendarProjectPolicy`
       - viewAny: user hanya bisa lihat project punya sendiri (user_id === Auth::id())
       - view: project.user_id === Auth::id() ATAU status=public_sharing + signed URL (untuk preview share ke temen)
       - update: project.user_id === Auth::id()
       - delete: project.user_id === Auth::id() (soft-delete)
       - exportPdf: project.user_id === Auth::id() DAN payment_status=paid (jika PDF paid) atau free tier
       - TIDAK BOLEH percaya projectId dari browser: selalu cek ownership via DB
    2. **Running Data Privacy** (74):
       - Endpoint RunnerDataBindingService selalu scope by Auth::id(): StravaActivity.where('user_id', auth()->id()), ProgramEnrollment.whereRunnerId(auth()->id())
       - TIDAK BOLEH ada endpoint yang ambil activity user lain tanpa role admin explicit
       - Cache response personalized: TIDAK simpan di shared cache (Redis shared) tanpa prefix user_id
    3. **Rate Limiting** (76):
       - Gunakan existing Laravel RateLimiter di bootstrap/app.php
       - Anonymous trial create: throttle:60,1 (60 per menit per IP)
       - Asset upload: throttle:30,1 (30 upload per menit per user)
       - PDF generation: throttle:5,60 (5 per jam per user, karena berat CPU)
       - Payment related endpoints: throttle:10,1
    4. **Image Upload Security** (33-34): Sudah tercakup di M.
    5. **CSRF Protection**: Semua endpoint POST/PUT/DELETE KalenderPelari TIDAK termasuk CSRF exempt. Webhook cetak PDF dari third-party (jika nanti ada) baru yang didaftarkan.
  - **O. Performance/Memory Strategy** (spec 64-71, 81):
    - **Editor Canvas Performance** (64):
      - TIDAK render 13 halaman sekaligus full detail. Gunakan pattern "Current Page Full Detail + Other Pages Thumbnail Only":
        - Canvas current page: render elements lengkap (photos HD, text, grid, dll)
        - Thumbnail bottom bar: render low-res (scale 0.15) hanya outline element, tanpa decode full resolusi foto
      - Virtualization untuk long list di sidebar (My Running activities 100+): Pakai vue-virtual-scroller (hanya render item terlihat di viewport 50)
      - Lazy mounting untuk heavy components: PaceCalculator, CropEditor, PrintPreflight (mount hanya saat tab aktif)
    - **Image Memory** (65-66):
      - Editor preview HANYA pakai variant medium (750px). Original >2MB TIDAK pernah di-load ke canvas editor kecuali user buka Crop Dialog.
      - Cleanup effect di Vue components `onBeforeUnmount()`:
        - `URL.revokeObjectURL(objectUrl)` untuk semua temporary photo preview anonymous
        - `img.src = ''` + canvas context clear
        - Cancel axios cancel token untuk requests foto yang in-flight saat user ganti halaman
      - Background: Monitor Chrome DevTools Heap Snapshot -> jika growth > 50MB setelah 10 kali switch month tanpa refresh -> ada leak (object URL / event listener).
    - **Editor State Performance** (67-68):
      - Pisahkan persistent state (pages/elements/bindings/assets) VS transient state (selection, hover, guides, drag state, viewport, temp assets):
        - Persistent: disimpan ke Pinia store (jika Vue SFC) atau Alpine store + autosave debounce 2000ms (TIDAK setiap pointer move)
        - Transient: disimpan di reactive() biasa, TIDAK diserialisasi, TIDAK diautosave
      - Drag interactions: update lokal CSS transform secara langsung (tanpa update reactive store setiap pixel). Store update HANYA saat `pointerup` / drag end -> meaningful change.
    - **Code Splitting** (71):
      - Entry `kalender-pelari-editor.js`: chunk `editor-core` (landing page + wizard) -> 150KB target. Lazy import:
        - `const CropEditor = () => import('./components/CropEditor.vue')` -> ~80KB (image processing)
        - `const PaceCalculator = () => import('./components/PaceCalculator.vue')` -> ~40KB
        - `const TrainingPlanner = () => import('./components/TrainingPlanner.vue')` -> ~100KB
        - `const PrintPreflight = () => import('./components/PrintPreflight.vue')` -> ~60KB
        - `const PdfPreview = () => import('./components/PdfPreview.vue')` -> ~120KB
      - Target LCP landing /kalender-pelari < 2.5s (Lighthouse mobile). Critical CSS di-inline untuk hero + 3 template preview card.
    - **Background Jobs** (72 / 68):
      - Heavy tasks dispatch ke Queue (Redis, sesuai konfigurasi cache/queue/session driver Redis):
        - ExpireTempAssetsJob (setiap jam)
        - GenerateProjectThumbnailJob (setiap save, generate 13 thumbnail pages 200px)
        - PrintPdfRenderJob (queue QUEUED->PROCESSING->COMPLETED/FAILED, Status Polling via endpoint /kalender-pelari/checkout/{id}/status)
    - **Mobile Experience** (81):
      - Full drag/drop editor -> TIDAK dipaksakan di viewport < 768px. Tampilkan MobileView: Today's training, Next Race Countdown, Monthly Progress, Upload Foto, Race creation (form sederhana), Print Ordering (layout teroptimasi touch). Redirect ke "Buka di Desktop" jika user coba edit drag-drop di mobile.
  - **P. Recommended Implementation Sequence** (sesuai MS Spec 93-97, MVP Phase 1-4):
    - **Phase 0 (SELESAI)**: Codebase Audit + Architecture Document
    - **MVP Phase 1 (Week 1-2: Landing + Trial Core Builder)**:
      1. Route setup: `/kalender-pelari` (landing SEO), `/kalender-pelari/buat` (wizard), `/kalender-pelari/editor/trial` (anonymous)
      2. Migration: kp_calendar_projects, kp_calendar_pages, kp_calendar_elements, kp_uploaded_assets (4 table core)
      3. Service: CalendarDateEngineService (Carbon, locale id), ProjectService (CRUD basic), TrialHandoffService skeleton
      4. Frontend: Wizard pilih type (Training/MyRunningYear/Photo/RunClub/Blank), pilih Year, pilih Format (A3L/A4L/Desk/Square), pilih Template (3 template minimal family Minimal Runner)
      5. Editor Canvas Sederhana: 1 halaman aktif, 5 element dasar (Photo upload local, Text, Calendar Grid, Goal, Note), basic drag + resize 8 handle + delete
      6. Preview months Jan-Dec via thumbnail bar (tanpa full render 13)
      7. AC 99 (Public Trial Acceptance Test) 11 Langkah harus PASS
    - **MVP Phase 2 (Week 3-4: Auth + Running Data Integration)**:
      1. TrialHandoffService lengkap: session state → pasca login → restore + upload consent dialog foto
      2. Service: RunnerDataBindingService (StravaActivity monthly aggregator: total KM, jumlah runs, longest, notable per month) + Event query untuk Upcoming Races
      3. Integration: My Running tab di sidebar (Activities, Stats, Personal Best (dari User.pb_*), Races, Shoes placeholder)
      4. Element: Monthly Stats, Activity Card draggable, Personal Best element, Race Countdown element
      5. Autosave ke database: ProjectService dengan debounce 2s, version history sederhana (last 10 version via state_draft JSON)
      6. My Running Year Template: Auto generate monthly stats page dari data user
      7. AC 100 (Auth Handoff) 8 Langkah + AC 101 (Running Data) 7 Langkah PASS
    - **MVP Phase 3 (Week 5-6: Training + Planner)**:
      1. Reuse Program + ProgramEnrollment data source
      2. Element: Training Plan, Workout (tipe sesuai 14 spec 37: Easy/Recovery/Long/Tempo/Threshold/Interval/Repetition/RacePace/Strides/Strength/Cross/Rest/Race)
      3. Workout drag/drop between dates + duplicate + edit distance
      4. Service: PaceProfile via existing DanielsRunningService (VDOT)
      5. Element: Pace Chart (apply to my training dengan confirmation sebelum overwrite)
      6. Planned vs Actual: Compare ProgramSession planned distance vs StravaActivity actual, status Completed/Adjusted/Skipped/Unmatched
      7. Monthly Goals: Mileage target, number of runs, progress bar
    - **MVP Phase 4 (Week 7-8: Print + Commerce)**:
      1. Migration: kp_templates, kp_template_elements, kp_project_snapshots, kp_print_specifications
      2. Service: TemplateService (switch template dengan preview, preserve runner data), AssetLifecycleService (TEMP_UPLOAD expire via scheduler), PrintPreflightService (13 pages ready, low res warning, missing photo)
      3. Snapshot at Checkout: immutable kp_project_snapshots sebelum calculate price
      4. Reuse Transaction (atau buat table order kp_orders yang berelasi dengan Transaction) flow payment Midtrans (support sandbox per booking) + Wallet payment
      5. PrintExportPipeline Job (queueable, Redis), render PDF via DomPDF 3.0 (existing) menggunakan data snapshot. TIDAK screenshot browser.
      6. Payment entitlement: PDF download hanya jika status=snapshot paid, signed URL 24 jam expire, TIDAK expose permanent URL
      7. AC 105 (Print Acceptance) dimensi, safe area, bleed, immutable snapshot test
    - **Post-MVP (Week 9+: Stabilitas + Creator)**:
      - Shoe Mileage, Habit Tracking, Club Mode (sementara hide UI), Creator Marketplace skeleton hanya page, tanpa royalty automation
      - Performance monitoring: Lighthouse score >= 90 mobile landing
  - **Q. Assumptions yang Butuh Konfirmasi (Open Questions)**:
    - **Q1 [Product Scope]**: Apakah `/kalender-pelari` landing page ARAHNYA: (A) Alias redirect ke `/runner/calendar` yang sudah ada dengan UX upgrade kecil; ATAU (B) Entry point ke VISUAL BUILDER BARU 13 halaman cetak dengan drag-drop editor seperti spec 14 (editor layout professional)? -> IMPLIKASI: Jika A, pekerjaan 80% reuse existing runner calendar + refresh UI. Jika B, butuh full Vue SFC editor baru + 4 migration table + 9 service boundaries baru.
    - **Q2 [Entitlement/Free Tier]**: User terdaftar GRATIS bisa simpan berapa CalendarProject? (contoh: 3 project free, unlimited butuh membership tier Package::tier >= 2). -> Berlaku juga untuk PDF free watermark / print low-res preview.
    - **Q3 [Physical Print Products]**: Paper options (HVS 150gr / Art Carton 260gr / Glossy), cover (Hardcover/Softcover/No Cover), spiral (Wire-O biasa/spiral plastik/tidak ada spiral) sudah tersedia di backend config? Atau perlu dibuat tabel print_products + admin CRUD? Quantity pricing tier (1/5/10/25/50/100+) sesuai spec 54?
    - **Q4 [Creator Marketplace Royalty]**: Default % royalty untuk creator template? (contoh: 70% creator, 30% platform). Apakah langsung masuk Wallet user creator via WalletTransaction type='creator_royalty' + mechanism auto-release 7 hari setelah pembeli berhasil download / print order terkirim?
    - **Q5 [Anonymous Trial Photo Temp Upload]**: Sesuai spec 27-28, anonymous TIDAK upload foto sebelum login. Namun jika foto user > 20MB total (misal 5 foto resolusi tinggi), localStorage 5MB tidak cukup. Perbolehkan TEMP_UPLOAD server dengan strict expire 1 JAM (bukan 24h) untuk anonymous? Atau batasi total anonymous photos sampai 3 foto?
    - **Q6 [Google Calendar Sync]**: User punya field `google_calendar_token` di tabel users. Apakah KalenderPelari perlu one-way sync (Races, Workouts, Goals) ke Google Calendar user? Atau fitur ini low priority masuk Post-MVP?
    - **Q7 [Strava Connect di KalenderPelari]**: Spec 21 melarang OAuth flow baru. Apakah KalenderPelari cukup panggil Runner\StravaController::connect existing (jika user belum connect Strava) + RunnerDataBindingService? Atau butuh dedicated flow dengan prompt scope tambahan `profile:read_all` kalau belum?
- **Acceptance Criteria Addressed**: AC-2, AC-3, AC-4
- **Test Requirements**:
  - `rule` TR-6.1: Setiap entity migration proposal (9) memiliki field kunci yang diperlukan, TIDAK duplikasi 14 model existing. Evidence: Tabel perbandingan J.
  - `rule` TR-6.2: Setiap Service Boundary baru (9) dan reuse (5) memiliki tugas tunggal jelas. Evidence: daftar K.
  - `rule` TR-6.3: Minimal 3 open questions bisnis (Q1, Q2, Q3, Q4, Q5, Q6, Q7 total 7) terdokumentasi. Evidence: AUDIT_REPORT.md Q section.
  - `rubric` TR-6.4: Kualitas phase 1-4 plan implementasi; scale 1-5; anchors 1=cuma list tasks, 3=phase+duration, 5=phase+target minggu+AC acceptance test mapped (99,100,101,105)+scope Post-MVP jelas; threshold >=4
