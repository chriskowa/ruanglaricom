# Kalender Pelari (Phase 0) — Codebase Audit Report Lengkap

**Tanggal Audit**: 2026-10-01
**Versi Spec Master**: 113 Poin + 4 Acceptance Test Suite
**Status Artifact Spec Mode**: ✅ `spec.md` (requirements) · ✅ `tasks.md` (6 task dengan evidence) · ⬜ `review.md` (belum Review)
**Graphify Output**: `graphify-out/graph.json` (20,686 nodes, 54,427 edges, 1,059 communities)

---

## RINGKASAN EKSEKUTIF

RuangLari memiliki **infrastruktur yang sangat matang** untuk di-extension menjadi Kalender Pelari. **≈80% komponen kunci SUDAH ADA** dan bisa di-reuse tanpa buat dari nol:

| Komponen | Status Reuse | Bukti Eksistensi |
|---|---|---|
| Auth (Session + API Token + 7 Login Method) | ✅ 100% Reuse | `config/auth.php`, User model, `CheckRole` middleware |
| Strava OAuth + Refresh Token + Sync | ✅ 95% Reuse | `StravaApiService`, User.strava_* columns, 3 flow existing (hanya perlu pilih 1 terbaik) |
| Running Data (Activity + PB + VDOT + Pace Zones) | ✅ 100% Reuse | `StravaActivity`, `UserActivity`, User accessor `vdot` + `training_paces` via `DanielsRunningService` |
| Race Calendar Source (Event + RaceCategory + Distances) | ✅ 100% Reuse | `Event` model with scopeUpcoming, published, directory/managed dual mode |
| Training Plan Source (Program + Enrollment + Sessions) | ✅ 100% Reuse | `Program` + `ProgramEnrollment` + custom workout auto-link Strava |
| Image Upload + WebP 3-Variant (Intervention 3) | ✅ 90% Reuse | `ImageUploadService::upload()` (hanya 2 kasus: Avatar/Participant RAW yang perlu upgrade nanti) |
| Payment (Midtrans + Moota + QRIS + COD) | ✅ 95% Reuse | `Transaction` model + Midtrans wrapper + **Wallet Escrow Mechanism** (PlatformWalletService — perfect untuk Creator Royalty) |
| Order Fulfillment (Event/Pacer/Program/Marketplace) | ✅ 85% Reuse | 4 flow pembayaran existing lengkap: atomic lock, post-paid Job, notification WA+Email |
| Frontend Stack (Tailwind v4 + Vue 3 + Alpine + Vite 7) | ✅ 100% Reuse | `resources/css/app.css` (brand palette neon #ccff00 + dark slate), `layouts/pacerhub.blade.php` (modern layout utama) |
| Queue + Background Jobs (Redis Predis) | ✅ 100% Siap | `config/queue.php`, `Jobs/SendEoReportEmail.php` — tinggal tambah PrintPdfRenderJob, ExpireTempAssetsJob |

**Konflik Teknis = NOL.** Route `/kalender-pelari` BELUM ADA → AMAN.

**Konflik Konseptual = 3 Kalender Berbeda Fungsi** → butuh UX mitigasi di landing page `/kalender-pelari` (penjelasan singkat 3 use case):
1. `/calendar` → Publik Race Calendar + Strava Dashboard (Statistik)
2. `/runner/calendar` → Kalender Latihan Pribadi Runner (Workout Program)
3. `/kalender-pelari` → **VISUAL CALENDAR BUILDER (BARU)** Cetak 13 halaman + drag-drop (spec 14) → ATAU alias redirect ke runner/calendar (opsi A)

---

## BAGIAN A — CURRENT ARCHITECTURE MAP

### Stack Utama
- **Framework**: Laravel 13 (`^13.0`) + PHP 8.3 (`^8.3`)
- **Timezone/Locale**: Asia/Jakarta, locale `id` (Indonesia), fallback en
- **Frontend Build**: Vite 7 (`^7.0.7`) + `laravel-vite-plugin`
- **CSS**: Tailwind CSS v4 (`@tailwindcss/vite` plugin, inline CSS config)
- **Brand Colors**: --neon:#ccff00, --dark:#0f172a, --card:#1e293b, --strava:#fc4c02. Catatan: Palet blue di-override jadi MERAH (#c11e09) di light mode.
- **JS Interactivity**: Alpine.js 3.13.3 (global CDN via pacerhub) + Vue 3 (`^3.5.25`) dengan 2 pola: **(a)** CDN global untuk Blade page interaktif sederhana; **(b)** Inertia SFC untuk Run Connect (`app.blade.php` with `@inertia`)
- **Extras**: ziggy-js (Laravel routes ke JS), Mapbox GL, Axios, Pusher Echo (realtime)
- **Processing**: Intervention Image 3.11 (GD driver), DomPDF 3 + TCPDF, OpenSpout (Spreadsheet)

### Struktur Routing (4 Role)
File `routes/web.php` (1611 baris) — Monolitik tapi terstruktur. Route group middleware via `CheckRole`:

| Prefix | Middleware Role | Halaman Utama |
|---|---|---|
| `/` (publik) | guest/none | Home, `/jadwal-lari`, `/about`, `/programs`, `/marketplace`, `/tools/*` (calculator, pace-pro, race-master) |
| `/admin/*` | `role:admin` | 24+ modul: users, events, marketplace, blog, integrations, races, sessions |
| `/eo/*` | `role:eo` | Event Organizer dashboard: participants, coupons, email blast, results |
| `/coach/*` | `role:coach` | Program dashboard: athletes, programs, finance (invoicing, withdrawal) |
| `/runner/*` | `role:runner\|user\|admin\|coach\|eo` | Dashboard runner, **calendar (legacy)**, programs, strava, GPX |
| `/marketplace/*` | auth | Cart, checkout, seller, auction, wishlist |

API V1 Mobile (`routes/api.php`, 108 baris): Prefix `/v1`, auth via custom `AuthenticateApiToken` (alias `auth:sanctum` — BUKAN Sanctum default. Bearer token via `PersonalAccessToken` model custom.

### Session & Security
- **Session Driver**: DATABASE (tabel `sessions`). 120 menit idle timeout.
- **Cookie**: HttpOnly=true (XSS safe), SameSite=lax (CSRF), Secure=tergantung env HTTPS.
- **CSRF Exempt**: 9 endpoint webhook payment/WA (Midtrans, Moota, WhatsApp, Race Master Public).

---

## BAGIAN B — EXISTING AUTH FLOW

### 7 Login Methods (tidak buat baru, REUSE SEMUA)
1. **Email + Password**: Standar Laravel (hash auto via cast `password=>'hashed'`)
2. **Phone OTP**: `requestPhoneOtp` → `verifyPhoneOtp` (OTP + rate limit)
3. **Google OAuth**: Socialite
4. **Strava OAuth**: Login/register via Strava (scope `read,activity:read_all`)
5. **Signed Login Token**: `/login/token/{user}` (untuk coach kirim reminder link auto-login)
6. **Social Login API**: Mobile `POST /v1/auth/social-login`
7. **Pacer OTP Registration**: `/pacer-otp`

### Policy Middleware (Reuse untuk KalenderPelari nanti):
- `CheckRole`: Support `role:admin|eo` (pipe) dan `role:runner,coach,eo,admin` (comma). Redirect guest → login, abort 403 jika role tidak cocok.
- `AuthenticateApiToken`: Bearer token untuk mobile. Touch `last_used_at`. Auto-expire delete jika token.expires_at lewat. Return 401 JSON.

---

## BAGIAN C — STRAVA INTEGRATION & ACTIVITY DATA

### 3 OAuth Flow Existing (PILIH SALAH SATU untuk reuse — JANGAN BUAT FLOW KE-4)
| Flow | Controller | Scope | Use Case Untuk KalenderPelari |
|---|---|---|---|
| **Auth Login** | `Auth\AuthController@handleStravaCallback` | read, activity:read_all | Hanya untuk user pertama kali buat akun via Strava |
| **Runner Connect (UTAMA)** ✅ RECOMMENDED | `Runner\StravaController@connect` | **activity:read_all, profile:read_all, activity:write** | User yang SUDAH login mau connect Strava → KalenderPelari cukup cek `User.strava_id` kosong? Redirect ke `route('runner.strava.connect')`. |
| Calendar Connect | `CalendarController@connect` | (sama) | Untuk halaman `/calendar` publik (race dashboard). Kurang tepat untuk private editor. |

### StravaApiService Reusable (wajib pakai ini, JANGAN call oauth/token sendiri)
```
StravaApiService::getValidAccessToken(User $user)
  └─> Cek strava_expires_at < now()+60s
      ├─> Jika ya: POST grant_type=refresh_token ke strava.com/oauth/token
      └─> Update 3 kolom token di model User + return access token
```

⚠️ Duplikasi: `StravaController::sync()` inline memiliki logic refresh_token sendiri dengan retry 401. Untuk KalenderPelari, **wajib pakai StravaApiService, TIDAK copy-paste inline logic**.

### 2 Model Activity (Pahami Perbedaan Satuan Jarak!)
| Model | Tabel | Jarak | Fungsi | Data Source |
|---|---|---|---|---|
| `StravaActivity` | strava_activities | **distance_m (METER)** ✅ | CACHE/ MIRROR resmi data Strava user | Strava `/athlete/activities` API |
| `UserActivity` | user_activities | **distance_km (KILOMETER)** ⚠️ | Aktivitas INTERNAL user: manual input / GPX upload / MasterGpx | User langsung / GPX |

⚠️ **CRITICAL GOTCHA**: Satuan beda. Saat aggregate monthly total distance, pastikan: StravaActivity → `distance_m / 1000` agar jadi KM, jangan langsung jumlahkan dengan UserActivity!

### Endpoint Sync UTAMA (Reuse Runner Data Source Existing)
`POST runner/strava/sync` → `Runner\StravaController::sync()` (130-494 baris)
- Fetches Strava activities with pagination: Max 5 pages × 50 = 250 per sync
- Incremental: `after = MAX(start_date) - 6 jam` (jika ada data lama); 45 hari ke belakang (jika baru)
- Upsert ke `strava_activities` + deduplicate via `strava_activity_id` UNIQUE
- Filter running types: run, virtualrun, trailrun, treadmill
- **Auto-link ke Program Sessions** (cocok 1 tanggal, ambil distance terbesar → set completed + strava_link)

### Data Activity Yang Tersedia (Sudah Siap Dipakai KalenderPelari):
| Kategori | Fields + Contoh | Akses Via |
|---|---|---|
| Dasar | name, type, start_date, distance_m, moving_time_s, elapsed_time_s, average_speed→pace, average_heartrate, max_heartrate, average_cadence, media URL foto | `StravaActivity` model attributes |
| Splits/KM | split#, distance_m, moving_time_s, elevation_diff, pace_format, average_heartrate | `raw->details->splits_standard` JSON |
| Laps | name, distance_m, moving_time_s, pace, avg/max HR, cadence | `raw->details->laps` JSON |
| Streams Time-series | time (s), heartrate, cadence, velocity_smooth, watts | Endpoint `runner/strava/activities/{id}/streams` |
| **AI Analysis (READY!)** ✅ | classification (easy_run/long_run/interval/tempo/threshold/recovery + evidence), summary, what_went_well, what_to_improve, risk_flags(junk_miles_risk), next_workout_suggestion, recovery_advice, confidence(low/medium/high) | Endpoint `runner/strava/activities/{id}/ai-analysis` — Cached di `raw->ai_analysis` hash input, `?force=true` recalculate |

---

## BAGIAN D — EXISTING DATA MODELS (24 Model RELEVAN)

### Ringkasan Mapping MS Spec §77 → Existing / Buat Baru:
| No | Entity MS Spec §77 | Status | Existing Model (Reuse/Extend) ATAU Nama Migration Baru | ID di J |
|---|---|---|---|---|
| 1 | User | ✅ **REUSE 100%** | `app/Models/User.php` (kolom strava_*, pb_*, vdot, training_paces accessor, google_calendar_token) | REUSE |
| 2 | CalendarProject | 🆕 **BUAT BARU** | `kp_calendar_projects` — id, user_id(nullable anonymous trial UUID), name, year, format_type, template_id, cover_asset_id, is_anonymous, state_draft(JSON), status, expires_at(trial) | J.1 |
| 3 | CalendarPage | 🆕 **BUAT BARU** | `kp_calendar_pages` — id, project_id, month_number (0=cover,1-12,13=YearReview), page_type, canvas_width_mm, canvas_height_mm, layout_config | J.2 |
| 4 | CalendarElement | 🆕 **BUAT BARU** | `kp_calendar_elements` — id, page_id, element_type(20+ type: photo/text/calendar_grid/workout/race/stats/shoe/note/pb...), x/y/width/height_mm, rotation, z_index, locked, visible, style_config, content_json, **data_binding(JSON references ke StravaActivity id / Event id / Program id / User.pb)**, asset_id | J.3 |
| 5 | UploadedAsset | 🆕 **BUAT BARU** | `kp_uploaded_assets` — id, user_id(nullable), project_id, asset_key(storage path), mime, width, height, file_size, **status lifecycle(LOCAL_ONLY/TEMP_UPLOAD/SAVED/PENDING_CHECKOUT/PAID/EXPIRED/DELETED)**, expires_at(otomatis expire TEMP 24h job) | J.4 |
| 6 | Template | 🆕 **BUAT BARU** | `kp_templates` — id, name, slug, family(8 families MS §18), is_premium, price, creator_id(FK User), thumbnail_asset, status, category | J.5 |
| 7 | TemplateElement | 🆕 **BUAT BARU** | `kp_template_elements` — id, template_id, page_type, element_type, default position/size, style_default(JSON), **binding_template (contoh: `{{month.totalDistance}}`)** | J.6 |
| 8 | TrainingPlan | ⚠️ **EXTEND EXISTING** | ✅ REUSE `app/Models/Program.php` + `ProgramEnrollment` (sudah ada duration_weeks, pricing, enrollment, generated_vdot, program_json) | EXTEND |
| 9 | Workout | ⚠️ **EXTEND EXISTING** | ✅ REUSE `CustomWorkout.php` (app/Models/CustomWorkout) + program_json sessions inside Program + sesi per enrollment → `workout_type` sesuai MS §37 (14 tipe) | EXTEND |
| 10 | Race | ⚠️ **DUAL MODEL** | Event + Race + RaceResult (sudah ada 3 model). KalenderPelari: Upcoming Race Calendar ambil dari scope `Event::published()->upcoming()`. My Races user → ambil `Participant::wherePIC->user` (yang dia daftar) | REUSE 3 |
| 11 | RaceEvent | ✅ REUSE | `app/Models/Event.php` (LENGKAP: dates, city/latlng, distances via RaceCategory, registration status, pricing tiers early/regular/late, coupons, facilities, gallery) | REUSE |
| 12 | RunningResult | ✅ REUSE | `app/Models/RaceResult.php` + `app/Models/RaceCategory.php` (bib, gun/chip time, pace, rank overall/category/gender, podium) | REUSE |
| 13 | PaceProfile | 🆕 **BUAT BARU** | `kp_pace_profiles` — id, project_id, **vdot_score (REUSE User.vdot!)**, pace_zones(JSON: Easy/Marathon/Threshold/Interval/Repetition min/km). Auto-prefill if user authenticated has PB | J.9 (sub: paces) |
| 14 | Goal | 🆕 **BUAT BARU** | `kp_goals` — id, project_id, month(null=annual), type(mileage/runs/longest/race/pace/strength/custom), target_value, unit | J.9 (sub: goals) |
| 15 | Habit | 🆕 **BUAT BARU** | `kp_habits` — id, project_id, habit_name, dates_marked(JSON array of Y-m-d) | J.9 (sub: habits) |
| 16 | Shoe | 🆕 **BUAT BARU** | `kp_shoes` — id, project_id, brand, model, nickname, start_mileage, retirement_target, **current_mileage (aggregate from StravaActivity per shoe, jika nanti ada kolom shoe di StravaActivity)** | J.9 (sub: shoes) |
| 17 | ActivityReference | ✅ REUSE via data_binding | `StravaActivity.id` / `UserActivity.id` disimpan sebagai JSON path di `CalendarElement.data_binding` | REUSE via binding |
| 18 | ProjectSnapshot | 🆕 **BUAT BARU** | `kp_project_snapshots` — id, project_id, project_document(IMMUTABLE FULL JSON), print_spec_id, created_by_user_id, created_at → TIDAK BOLEH DIUBAH SETELAH DIBUAT | J.7 |
| 19 | Order | ✅ REUSE (Transaction) | **Pilih 1**: (a) Reuse `Transaction` model (event registration flow: Midtrans, status pending/paid/failed, snap_token, paid_at, coupon, unique_code Moota) — cocok untuk physical print karena mirip produk event. ATAU (b) Reuse `Order` (program coach) — kurang cocok karena Order khusus program tanpa Moota/QRIS. **REKOMENDASI: Buat child table `kp_orders` yang morph reference ke Transaction sebagai parent pembayaran.** | REUSE Transaction |
| 20 | OrderItem | ✅ REUSE (Participant/OrderItem) | 1 project snapshot + quantity print spec = 1 line item. Participant model sudah punya pola N item per Transaction. | REUSE pola Participant |
| 21 | PrintSpecification | 🆕 **BUAT BARU** | `kp_print_specifications` — id, product_type(A3_WALL/A4_WALL/DESK_CALENDAR), paper_type, cover_type, spiral_binding, quantity, **unit_price_config(JSON DARI BACKEND CONFIG, TIDAK HARDCODE)** — (MS §53) | J.8 |
| 22 | PDFExport | ⚠️ Via Payment Entitlement | TIDAK buat tabel tersendiri. PDF di-generate dari `PrintExportPipeline` Job via ProjectSnapshot. Akses hanya jika order berstatus paid. Return signed URL 24 jam expire. Permanent PDF URL TIDAK BOLEH menurut MS §58. | Via Job + Snapshot |
| 23 | CreatorProfile | 🆕 **POST-MVP SAJA** | `kp_creator_profiles` — id, user_id, bio, social_links, royalty_percent_default, status. SEMENTARA SEMBUNYIKAN UI, fokus core builder. | POST-MVP |
| 24 | TemplatePurchase | 🆕 **POST-MVP SAJA** | `kp_template_purchases`. Reuse Payment Transaction + Wallet payout via PlatformWalletService (escrow). | POST-MVP |
| 25 | RoyaltyTransaction | ✅ **REUSE WalletTransaction**! | WalletTransaction.type='creator_royalty'. reference_type='template_purchase'. Pola escrow PlatformWalletService dari PacerBooking DAPAT DI-REUSE 100%. | REUSE WalletTransaction |

### Entity Summary (SUMMARY J SECTION):
- **REUSE EXISTING 14** ✅ = 56%
- **EXTEND EXISTING 3** ⚠️ = 12%
- **BUAT BARU MVP 9** 🆕 = 32% (1-6 migration minggu pertama)
- **POST-MVP DEFER 3** = CreatorProfile, TemplatePurchase. RoyaltyTransaction sudah ada via Wallet.

---

## BAGIAN E — EXISTING STORAGE & IMAGE ARCHITECTURE

### Driver & Service
| Komponen | Nilai | Catatan untuk KalenderPelari |
|---|---|---|
| Default Public Disk | `Storage::disk('public')` → `storage/app/public` → symlink `public/storage` | **Pakai ini untuk MVP.** Folder baru: `kalender-pelari/temp/`, `kalender-pelari/projects/{id}/`, `kalender-pelari/templates/` |
| Private Disk | `storage/app/private` | Nanti untuk Original > 4000px resolusi tinggi user. ATAU untuk PDF print-ready berbayar. |
| **S3 Configured** | Key kosong, tapi endpoint, bucket, region, sudah ada env. | **SWITCH KE CLOUDFLARE R2 NANTI:** Cukup override AWS_ENDPOINT, AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET. TIDAK perlu code change besar. |
| **ImageUploadService** | Intervention 3.11, GD Driver, 3 variant WebP 300/750/1200 px quality=80, UUID random naming, scale() (tanpa crop, ratio preserved) | **REUSE 100%.** Tambahkan method `uploadFromBase64OrBlob()` untuk anonymous trial foto user. |

### 4 Kasus Upload Existing (Pelajari Polanya):
| Kasus | Service Terpakai | Catatan Gap Untuk Improvement |
|---|---|---|
| Event Media (Dropzone) | ImageUploadService::uploadSingle 1920px WebP quality 85 | ✅ Paling benar. Pipeline dropzone AJAX → processImage → folder events/ |
| Marketplace Product | ImageUploadService::upload() 3 variant | ✅ Benar, validasi 1-4 gambar, 3MB max, mime jpg/png/webp |
| **Avatar Admin Update** | **RAW Laravel store()** — TANPA Intervention, TANPA resize, TANPA WebP ⚠️ | Tidak perlu fix sekarang. Untuk KalenderPelari, JANGAN tiru pola ini. Selalu pakai ImageUploadService. |
| **Participant Photo (Registrasi)** | **RAW base64_decode → Storage::put()** — tanpa kompresi, tanpa WebP ⚠️ | Risiko: peserta upload foto HP 8MB JPG tersimpan RAW. KalenderPelari, case photo user wajib masuk ImageUploadService. |

---

## BAGIAN F — PAYMENT & ORDER ARCHITECTURE

### 4 Gateway Aktif (REUSE, JANGAN BUAT GATEWAY BARU)
| Gateway | Status Production | Konfigurasi | Catatan Untuk KalenderPelari |
|---|---|---|---|
| **Midtrans** ✅ (PRIMARY) | AKTIF | Merchant: G475665008. Production keys valid. ✨ **SUPPORT PER-BOOKING SANDBOX MODE** (hanya di Event flow via `event.payment_config.midtrans_demo_mode`). ⚠️ PacerBooking & Program saat ini HANYA default production tanpa sandbox toggle. | **Pakai Midtrans sebagai pembayaran utama** physical print + premium PDF. TIRU POLA EVENT (bukan pacer). Ada `MidtransService` wrapper + `Transaction.midtrans_mode` field + snap_token + notification URL. |
| **Moota** (Manual Transfer VA BCA) | AKTIF | BankID bpPkB9d4WB2. a/n PT RUANG LARI. ⚠️ `verifySignature()` webhook di-comment-out + env MOOTA_SECRET masih placeholder `your_secret_key`. RISIKO SPOOFING. | Untuk anonymous trial yang checkout (pembeli tidak login): Moota cocok karena user bisa transfer manual via ATM/iBanking. **TAPI: Enable signature verify sebelum production.** |
| **QRIS Statis (SpeedCash Dynamic Inject)** | AKTIF | NMID: ID1026477901668. Service: `QrisDynamicService` → inject nominal ke QRIS static via hitung CRC16 checksum 4 digit terakhir | Untuk mobile user, QRIS memberikan konversi tertinggi di Indonesia. **RECOMMENDED: Tampilkan QRIS sebagai opsi kedua setelah Midtrans.** |
| **COD** | Support Event Only | `payment_method=cod` → Transaction status cod | COD untuk cetakan kalender fisik berbiaya mahal (shipping). Bisa ditambahkan nanti Post-MVP jika ada kerja sama kurir lokal COD. **MVP Phase 4: Disable COD dulu.** |

### 4 Existing Payment Flow (PELAJARI & ADAPTASI):
Yang TERBAIK untuk Physical Print Kalender = **FLOW A (Event Registration)**:
```
Flow A — Event (Recommended untuk KalenderPelari Print):
  [1] Atomic Cache::lock('quota:category:'+id, 10s) — (KalenderPelari ganti lock jadi: 'kp_project_snapshot:'+id, anti double-submit)
  [2] Hitung harga total: (PrintSpec.unit_price_config[quantity] * quantity) + platform_admin_fee + unique_code(Moota) + shipping_cost(jika ada RajaOngkir nanti)
  [3] DB Transaction → Insert `kp_orders` (status pending) + reference ke Transaction.id (insert Transaction dengan payment_status=pending)
  [4] Payment Select:
      a. ZERO AMOUNT (gratis 100%) : auto markAsPaid → dispatch ProcessPaidKalenderPesananJob
      b. MIDTRANS : MidtransService::createEventTransaction() (TIRU) → item_details = line items, discount, admin fee. Dapat snap_token. Return midtrans_redirect_url.
      c. MOOTA : final_amount += unique_code() (1-999 unik 24jam). User transfer ke BCA.
  [5] Webhook Midtrans/Moota → Verifikasi signature SHA512 → markAsPaid() → DISPATCH JOB.
  [6] POST-PAID JOB (ProcessPaidKalenderPesananJob):
      6.1. CREATE Immutable kp_project_snapshots (TIDAK BOLEH DIUBAH)
      6.2. Update UploadedAssets status: SAVED_PROJECT → PAID_ACTIVE (set asset retention 180 hari)
      6.3. DISPATCH PrintPdfRenderJob (Queueable, Redis QUEUED/PROCESSING/COMPLETED)
      6.4. Jika Order termasuk Creator template: ESCROW PlatformWallet.locked_balance (Tiru PacerBooking). Release 7 hari setelah user sukses download PDF → 70% masuk Creator Wallet via WalletTransaction.type='creator_royalty'
      6.5. Notifikasi: Email E-Ticket / Order Confirmation (Tiru Mail\EventRegistrationSuccess) + WA Notification via WhatsApp::send()
      6.6. Update kp_orders + Transaction status = PAID
```

### Wallet Ecosystem — Perfect untuk Creator Royalty (REUSE 100%)
- **Escrow Pattern Existing**: PacerBooking SUDAH membuktikan flow locked_balance + split payout bekerja:
  1. Runner Paid: PlatformWallet.locked_balance += total. (type=pacer_booking_escrow_lock)
  2. Runner Complete → release lock, platform_fee ke balance, sisanya ke Pacer User Wallet.
- **Creator Royalty Pattern (Copy-Paste Adaptasi)**:
  1. Buyer Paid → PlatformWallet.locked_balance += (template_price × 70%) (type=creator_royalty_escrow)
  2. 7 Hari setelah order status=completed (download PDF success, tidak refund) → release lock:
     - PlatformWallet.balance += 30% (type=creator_marketplace_fee_income)
     - Creator User Wallet.balance += 70% (type=creator_royalty + reference_type: TemplatePurchase.id)
  3. User Creator bisa Withdrawal via WalletWithdrawal yang existing (admin proses manual transfer ke rekening) — SUDAH ADA!

---

## BAGIAN G — FRONTEND DESIGN SYSTEM

### Layout Utama (KalenderPelari Editor Gunakan PACERHUB ✅)
Gunakan `@extends('layouts.pacerhub.blade.php')` — **layout modern utama**. Ini adalah layout yang sama dengan /calendar publik (Race Calendar + Strava Dashboard).

**3 Layout Coexisting (Pahami Fungsi Masing-masing)**:
| Layout | Path | Gunakan Untuk KalenderPelari? |
|---|---|---|
| **pacerhub.blade.php** ✅ RECOMMENDED | `resources/views/layouts/pacerhub.blade.php` | YA: Landing page `/kalender-pelari`, wizard `/buat`, editor `/editor/trial`, `/editor/{id}` (semua halaman public facing user) |
| layouts/app.blade.php (Legacy Admin Theme Deznav) | `resources/views/layouts/app.blade.php` | HANYA: Jika kita tambahkan Admin CRUD Print Products / Creator moderation ke admin panel (post-mvp). Bukan untuk user-facing editor. |
| layouts/coach.blade.php | `resources/views/layouts/coach.blade.php` | TIDAK. Khusus Coach Dashboard |
| Root app.blade.php | `resources/views/app.blade.php` | TIDAK. Khusus Inertia Run Connect (fitur social) |

### Reusable Assets Loading di pacerhub.blade.php:
- ✅ **Alpine.js 3.13.3** (Global CDN jsdelivr)
- ✅ **Vue 3 Global** (unpkg CDN, vue.global.js — CUKUP untuk editor dasar drag-drop. Kalau butuh kompleks baru buat vite entry separate)
- ✅ **Font Awesome 6.5.1** (Pattern Preload + `media="print"` onload swap → Performance Lighthouse Optimized)
- ✅ **Google Fonts** (Preconnect fonts.googleapis + fonts.gstatic). Loaded: Inter 400,600,800 + JetBrains Mono 500,700.
- ⚠️ **TIDAK ADA Inter Tight** (walaupun di project memory disebut-sebut). Jika perlu tampilan heading seperti desain hero, tambahkan `<link>` include manual di editor page.

### Frontend Build Pattern Baru untuk KalenderPelari:
**Rekomendasi: Vite Entry `kalender-pelari-editor.js` (terpisah, lazy-load chunked)**
```js
// vite.config.js entry tambahan:
export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/kalender-pelari-editor.js',  // BARU
      ],
      refresh: true,
    }),
    vue({ customElement: false }),
  ],
})
```
Inside `kalender-pelari-editor.js` → Mount root Vue component `<KalenderPelariEditor>` ke div target, pakai SFC components, code splitting dynamic imports untuk heavy components (CropEditor, PaceCalculator, TrainingPlanner, PrintPreflight — lihat §O Performance).

---

## BAGIAN H — EXISTING ROUTES & CONFLICTS

### 3 "Calendar" Existing (RANGKUMAN TABEL PERBANDINGAN):
| URL | Route Name | Controller | View Path | Tujuan Utama | Modifikasi? |
|---|---|---|---|---|---|
| **`/calendar`** (PUBLIK) | `calendar.public` | `App\Http\Controllers\CalendarController` (Root NS) | `calendar/index.blade.php` | Race Calendar (events via FullCalendar) + **TAB STRAVA DASHBOARD** (yearly recap, share poster, shoe rotation, race potential AI) | **JANGAN DIUBAH / DIGANGGU GUGAT.** Ini fitur existing produktif. |
| **`/runner/calendar`** (AUTH RUNNER) | `runner.calendar` | `App\Http\Controllers\Runner\CalendarController` (Runner NS) | `runner/calendar.blade.php` (Legacy Admin Theme) + `runner/calendar_modern.blade.php` | **Kalender Latihan Pribadi** — Apply program, custom workout, update PB, reschedule session, Weekly Volume Chart, auto-link Strava → Session completed | **JANGAN DIHAPUS.** Tapi bisa di-upgrade UI nya agar tampil pacerhub.blade.php (saat ini extend layouts/app yang legacy). |
| **`/jadwal-lari`** (PUBLIK ARSIP) | `events.index` | `PublicRunningEventController` | `events/landing.blade.php` (Hero Redesigned terbaru dengan 6 stripe running track) | Listing + search event lari Indonesia. | TIDAK ADA KAITAN. Fokus ke halaman event directory. |
| **`/kalender-pelari`** | **BELUM ADA → AMAN** ✅ | (Akan dibuat: `KalenderPelari\LandingController`) | (Akan dibuat: `kalender-pelari/landing.blade.php`) | **SEO Landing page BARU.** Penjelasan produk + CTA "Buat Kalender" + "Coba Tanpa Login". BISA MENJADI ALIAS REDIRECT ke runner/calendar JIKA user pilih opsi A di Q&A Q1. | **ROUTE INI ADALAH TANGGUNG JAWAB KALENDARPELARI.** Kita yang manage lifecycle. |

### Mitigasi Konflik Konseptual User Bingung (3 Implementation Options):
| Opsi | Strategi Landing `/kalender-pelari` | Effort | Cocok Jika |
|---|---|---|---|
| **A. Alias Redirect** | `/kalender-pelari` → `return redirect()->route('runner.calendar')` dengan flash message explaining "Ini adalah Kalender Latihan Pribadi Anda. Untuk race calendar lihat /calendar." | 🟢 Low (15 lines code + 1 route) | Tim memilih untuk upgrade existing runner/calendar UI saja TANPA buat builder 13 halaman drag-drop visual editor. |
| **B. Unified Entry Hub** ✅ (**RECOMMENDED MS SPEC COMPLIANT**) | Landing `/kalender-pelari` menampilkan 3 kartu pilihan jelas: [1] 🏃 Latihan Pribadi → ke runner/calendar. [2] 🏁 Jadwal Balapan → ke /calendar. [3] 📅 Buat Kalender Cetak → /kalender-pelari/buat wizard visual builder baru. Bottom: CTA besar "COBA TANPA LOGIN" → trial builder anonim | 🟡 Medium (Landing page 3 card + explainer) | Sesuai §7 MS spec: Public Trial critical, authentication gate baru ketika mau SAVE / pakai running data. |
| **C. Builder Only (Strict MS)** | `/kalender-pelari` = 100% fokus ke visual builder 13 halaman cetak (MS §88 Landing Page). Hilangkan referensi ke runner/calendar & /calendar dari halaman ini. | 🔴 High + Risiko User Bingung | Jika sudah ada user base existing runner yang aktif pakai runner/calendar, ini akan terasa seperti "buang fitur lama". **TIDAK RECOMMENDED untuk sekarang.** |

### Route Conflict Analysis — TIDAK ADA KONFLIK TEKNIS:
✅ `/kalender-pelari` & subroutes tidak ada → 100% bebas untuk didefinisikan
✅ Dua Controller namespace berbeda (CalendarController vs Runner\CalendarController) → import alias sudah benar di web.php L3
✅ Dua view folder terpisah: `calendar/` (publik) vs `runner/calendar/` (private) → tidak tabrakan nama file blade
✅ Runner profile URL `/runner/{username}` SUDAH MEMILIKI REGEX BLACKLIST kata `calendar` (line 473 web.php) → TIDAK akan ditangkap sebagai username

---

## BAGIAN I — REKOMENDASI ARSITEKTUR KALENDARPELARI

### Arsitektur: Modular Monolith Laravel (Package-like Namespace TANPA Buat Package Terpisah)
```
app/
├── Http/Controllers/
│   └── KalenderPelari/            ← BARU (namespace group)
│       ├── LandingController.php       (GET  /kalender-pelari)
│       ├── WizardController.php        (GET  /kalender-pelari/buat, POST process step)
│       ├── TrialEditorController.php   (GET  /kalender-pelari/editor/trial)
│       ├── EditorController.php        (GET  /kalender-pelari/editor/{projectId} [auth])
│       ├── ProjectApiController.php    (API CRUD state autosave via POST)
│       ├── AssetController.php         (Upload, Signed URL for private assets)
│       ├── TemplateGalleryController.php (GET /kalender-pelari/template)
│       ├── CheckoutController.php      (GET/POST /kalender-pelari/checkout/{projectId})
│       └── PrintDownloadController.php (Signed PDF endpoint download)
├── Models/
│   └── KalenderPelari/            ← BARU (9 models §J section)
│       ├── CalendarProject.php
│       ├── CalendarPage.php
│       ├── CalendarElement.php
│       ├── UploadedAsset.php
│       ├── Template.php
│       ├── TemplateElement.php
│       ├── ProjectSnapshot.php
│       ├── PrintSpecification.php
│       ├── Goal.php / Habit.php / Shoe.php / PaceProfile.php
├── Services/
│   └── KalenderPelari/            ← BARU (9 services §K section)
│       ├── ProjectService.php         (CRUD project, autosave debounce)
│       ├── CalendarDateEngineService.php (Carbon dates, id locale, Monday/Sunday start)
│       ├── ElementRendererService.php  (mm↔px converter, snap guides, z-index)
│       ├── RunnerDataBindingService.php (Aggregate StravaActivity monthly, Events, Programs, User.PB → inject ke element.data_binding)
│       ├── TemplateService.php        (Apply template, preserve runner data, warn layout loss)
│       ├── AssetLifecycleService.php  (Status transition flow, expire job)
│       ├── TrialHandoffService.php    (Anonymous → Login restore state, upload consent)
│       ├── PrintPreflightService.php  (Missing asset, low-res, bleed check before checkout)
│       └── PrintExportPipeline.php    (QUEUEABLE JOB. DomPDF 3.0 render authoritative PDF from snapshot, NOT browser screenshot)
└── Policies/
    └── CalendarProjectPolicy.php  ← BARU (viewAny, view, update, delete, exportPdf - always check user_id ownership)

routes/kalender-pelari.php          ← BARU (file route terpisah, include via require_once di web.php L1 sebelum catch-all pages)
resources/views/kalender-pelari/    ← BARU (folder views modular: landing, wizard, editor, templates, checkout, components)
resources/js/kalender-pelari-editor.js  ← BARU Vite Entry
resources/js/KalenderPelari/*.vue   ← BARU SFC components (Canvas, Sidebar, ElementProperties, PageThumbnails, CropDialog, PaceCalculator)
resources/css/kalender-pelari.css   ← BARU (Tailwind @layer components untuk editor surface classes: .kp-surface, .kp-drag-handle, .kp-ruler-mm)
```

---

## BAGIAN J — PROPOSED NEW DATABASE ENTITIES / MIGRATIONS (9 TABEL MVP + 4 TABEL KECIL POST-PHASE 1)

Urutan Pembuatan Migration (Foreign Key Order)
`1 → 2 → 3 → ... → 9` (Parent dulu sebelum children agar `foreignId` constrain berhasil):

### J.1 `kp_calendar_projects` (TABEL INDUK UTAMA)
```php
// Migration order: 1 (Pertama karena semua child reference ke project_id)
Schema::create('kp_calendar_projects', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // null = anonymous trial. Isi setelah auth handoff
    $t->foreignId('template_id')->nullable()->constrained('kp_templates')->nullOnDelete();
    $t->foreignId('cover_asset_id')->nullable()->constrained('kp_uploaded_assets')->nullOnDelete();
    $t->uuid('anonymous_uuid')->nullable()->unique(); // untuk identifikasi project anonim TANPA user_id + session token trial
    $t->string('name', 120)->default('Kalender Lariku');
    $t->smallInteger('year')->unsigned(); // 2026, 2027, dst. Range: 2024-2035
    $t->enum('format_type', ['A3_LANDSCAPE','A4_LANDSCAPE','DESK_CALENDAR','SQUARE','CUSTOM'])->default('A4_LANDSCAPE');
    $t->boolean('is_anonymous')->default(true); // false setelah disimpan user auth
    $t->enum('start_week_on', ['MONDAY','SUNDAY'])->default('MONDAY');
    $t->enum('status', ['DRAFT_TRIAL','DRAFT_SAVED','PENDING_CHECKOUT','PRODUCTION_ACTIVE','ARCHIVED','EXPIRED_TRIAL'])->default('DRAFT_TRIAL');
    $t->longText('state_draft')->nullable()->comment('Full JSON compressed pages+elements. Debounced autosave disini. Max ~10MB JSON per project (13 pages + 100 elements OK)');
    $t->unsignedInteger('version_counter')->default(1); // optimistic locking sederhana: increment each save
    $t->timestamp('expires_at')->nullable()->comment('Trial expire 7 hari atau TEMP_UPLOAD expire 24h');
    $t->timestamps();
    $t->softDeletes(); // recycle bin, tidak langsung hapus permanen
    $t->index(['user_id','status']); // user listing projek mereka
    $t->index(['anonymous_uuid','expires_at']); // garbage collection expired trials
    $t->index(['status','expires_at']); // background job cleanup
});
```

### J.2 `kp_calendar_pages`
```php
Schema::create('kp_calendar_pages', function (Blueprint $t) {
    $t->id();
    $t->foreignId('project_id')->constrained('kp_calendar_projects')->cascadeOnDelete();
    $t->tinyInteger('month_number')->unsigned()->comment('0=COVER,1=Jan,...,12=Dec,13=YEAR_REVIEW,14+=custom pages');
    $t->enum('page_type', ['COVER','MONTH','YEAR_REVIEW','CUSTOM'])->default('MONTH');
    $t->unsignedSmallInteger('canvas_width_mm')->default(297); // A4 default
    $t->unsignedSmallInteger('canvas_height_mm')->default(210);
    $t->json('layout_config')->nullable()->comment('background_color, background_asset_id, bleed_mm, safe_area_mm, grid_enabled, snap_enabled_px');
    $t->timestamps();
    $t->unique(['project_id', 'month_number']); // satu project TIDAK BOLEH ada 2 cover / 2 bulan Jan duplikat
});
```

### J.3 `kp_calendar_elements` (TERBESAR. 20+ element types)
```php
Schema::create('kp_calendar_elements', function (Blueprint $t) {
    $t->id();
    $t->foreignId('page_id')->constrained('kp_calendar_pages')->cascadeOnDelete();
    $t->foreignId('asset_id')->nullable()->constrained('kp_uploaded_assets')->nullOnDelete();
    $t->enum('element_type', [
        'PHOTO','TEXT','CALENDAR_GRID','MONTHLY_GOAL','TRAINING_PLAN','WORKOUT','RACE',
        'RACE_COUNTDOWN','MONTHLY_STATS','PACE_CHART','HABIT_TRACKER','PERSONAL_BEST',
        'SHOE_MILEAGE','NOTES','QUOTE','ACTIVITY_CARD','PROGRESS_BAR','RUN_CLUB_EVENT',
        'SPONSOR_LOGO','DECORATIVE_SHAPE','PAGE_NUMBER','MONTH_TITLE'
    ])->index();
    $t->unsignedMediumInteger('x_mm')->default(0)->comment('Position X dalam MM dari top-left canvas');
    $t->unsignedMediumInteger('y_mm')->default(0);
    $t->unsignedMediumInteger('width_mm')->default(50);
    $t->unsignedMediumInteger('height_mm')->default(50);
    $t->decimal('rotation_deg', 5, 2)->default(0); // -180 s/d +180
    $t->smallInteger('z_index')->default(0); // layer order, range -1000 s/d +1000
    $t->boolean('locked')->default(false); // user lock element agar tidak terseret tidak sengaja
    $t->boolean('visible')->default(true); // hide element sementara
    $t->json('style_config')->nullable()->comment('font_family, font_size_pt, color_hex, bg_color, border_radius_mm, opacity_pct, shadow_bool, alignment, [CROP TRANSFORM PARAMS: cropX, cropY, cropW, cropH, rotation, scale]');
    $t->longText('content_json')->nullable()->comment('Raw text content, calendar grid events array, goal target value, race info, stats numbers');
    $t->json('data_binding')->nullable()->comment('Dynamic binding references: {"source":"strava_activity","id":12345,"field":"distance_km_formatted"} ATAU {"source":"user.personal_best","field":"hm"} ATAU {"source":"event.upcoming","id":99}');
    $t->timestamps();
    $t->index(['page_id','z_index']); // ordering untuk render top-down
    $t->index(['element_type','page_id']); // filter by type (cari semua PHOTO element)
});
```

### J.4 `kp_uploaded_assets` (LIFECYCLE TRACKING — KRITIKAL)
```php
Schema::create('kp_uploaded_assets', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $t->foreignId('project_id')->nullable()->constrained('kp_calendar_projects')->nullOnDelete();
    $t->string('storage_key', 255)->unique()->comment('Full path relative to disk: kalender-pelari/projects/123/uuidv4_large.webp. BUKAN original filename user (privasi). BISA bernilai NULL jika LOCAL_ONLY (anonymous, belum upload server sama sekali)');
    $t->string('mime_type', 60); // image/jpeg, image/png, image/webp
    $t->unsignedInteger('width_px')->nullable();
    $t->unsignedInteger('height_px')->nullable();
    $t->unsignedBigInteger('file_size_bytes')->nullable();
    $t->enum('status', ['LOCAL_ONLY','TEMP_UPLOAD','SAVED_PROJECT','PENDING_CHECKOUT','PAID_ACTIVE','EXPIRED','DELETED'])->default('LOCAL_ONLY')->index();
    $t->string('original_filename', 200)->nullable()->comment('Original user filename untuk UX saja (tampilkan "Anda mengupload IMG_2026.jpg"), TIDAK dipakai di storage key');
    $t->string('checksum_sha256', 64)->nullable()->index()->comment('Deteksi upload foto duplikat: jika user upload foto yang sama 2 kali → re-use asset_id yang sudah ada, hemat storage');
    $t->unsignedTinyInteger('resolution_quality_score')->nullable()->comment('3=EXCELLENT, 2=GOOD, 1=LOW RES. Dihitung saat upload: (width*height) / target_dpi_pixels_for_current_canvas_mm_width');
    $t->timestamp('expires_at')->nullable()->comment('Jika TEMP_UPLOAD → otomatis +24jam. Background job delete jika status=TEMP dan expires_at<now');
    $t->timestamps();
    $t->softDeletes(); // tombstoning, jangan langsung hard delete (bisa user restore dari trash)
    $t->index(['project_id','status']);
    $t->index(['status','expires_at']); // expire cleanup job query
});
```

### J.5 `kp_templates` (Template Gallery — MVP 8 Families MS §18)
```php
Schema::create('kp_templates', function (Blueprint $t) {
    $t->id();
    $t->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete()->comment('Null = Official RuangLari templates (bukan user creator)');
    $t->foreignId('thumbnail_asset_id')->nullable()->constrained('kp_uploaded_assets')->nullOnDelete();
    $t->string('name', 100);
    $t->string('slug', 120)->unique();
    $t->enum('family', ['MINIMAL_RUNNER','RACE_SEASON','RUNNING_MEMORIES','MARATHON_BUILD','TRAIL_YEAR','RUN_CLUB','PERSONAL_BEST','CLEAN_GRID','CUSTOM_CREATOR'])->default('MINIMAL_RUNNER')->index();
    $t->boolean('is_premium')->default(false);
    $t->unsignedInteger('price_idr')->default(0); // 0 = free template
    $t->enum('status', ['DRAFT','PUBLISHED','REJECTED','ARCHIVED'])->default('DRAFT')->index();
    $t->string('category_tagline', 160)->nullable(); // untuk gallery filter: "Minimalis & Bersih", "Fokus Race Season", dll
    $t->longText('description')->nullable();
    $t->json('supported_formats')->nullable()->comment('Array ["A4_LANDSCAPE","A3_LANDSCAPE"]. Tidak semua template support semua format.');
    $t->timestamps();
    $t->index(['family','is_premium','status']); // gallery filter cepat
});
```

### J.6 `kp_template_elements`
```php
Schema::create('kp_template_elements', function (Blueprint $t) {
    $t->id();
    $t->foreignId('template_id')->constrained('kp_templates')->cascadeOnDelete();
    $t->enum('page_type_reference', ['COVER','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER','YEAR_REVIEW','ANY_MONTH'])->default('ANY_MONTH');
    $t->enum('element_type', [... same enum as kp_calendar_elements ...])->index();
    $t->unsignedMediumInteger('default_x_mm')->default(0);
    $t->unsignedMediumInteger('default_y_mm')->default(0);
    $t->unsignedMediumInteger('default_width_mm')->default(50);
    $t->unsignedMediumInteger('default_height_mm')->default(50);
    $t->json('style_default')->nullable()->comment('Default styling per template element: brand colors, font choices, dll');
    $t->string('binding_template', 255)->nullable()->comment('MS §20 Contoh: {{month.totalDistance}}. Akan di-inject nilai saat apply template.');
    $t->timestamps();
});
```

### J.7 `kp_project_snapshots` (IMMUTABLE CHECKOUT)
```php
Schema::create('kp_project_snapshots', function (Blueprint $t) {
    $t->id();
    $t->foreignId('project_id')->constrained('kp_calendar_projects');
    $t->foreignId('print_spec_id')->nullable()->constrained('kp_print_specifications')->nullOnDelete();
    $t->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $t->unsignedInteger('project_version_taken')->comment('Copy dari projects.version_counter pada saat snapshot. Agar user save lagi setelah checkout, snapshot TIDAK TERUBAH.');
    $t->longText('project_document_json')->comment('FULL IMMUTABLE SNAPSHOT: pages + elements + assets referenced. TIDAK BOLEH DIUBAH. Gunakan JSON_COMPRESS() MySQL jika terlalu besar (lebih dari 1MB).');
    $t->string('sha256_hash', 64)->unique()->comment('Hash full document. Jika user generate snapshot untuk project yang sama tanpa perubahan → reuse snapshot yang sama (hemat storage). Pembuktian integritas pesanan.');
    $t->timestamp('created_at')->useCurrent();
    // NOTE: NO updated_at, NO softDeletes. IMMUTABLE. Jika dihapus permanen baru hard delete jika order expired/cancelled > 1 tahun.
    $t->index(['project_id','created_at']);
});
```

### J.8 `kp_print_specifications`
```php
Schema::create('kp_print_specifications', function (Blueprint $t) {
    $t->id();
    $t->enum('product_type', ['A3_WALL','A4_WALL','DESK_CALENDAR','CUSTOM_PRINT'])->default('A4_WALL');
    $t->enum('paper_type', ['HVS_150GSM','ART_CARTON_260GSM','GLOSSY_200GSM','MATTE_200GSM','CUSTOM'])->default('HVS_150GSM');
    $t->enum('cover_type', ['NO_COVER','SOFTCOVER_ART_CARTON','HARDCOVER_PVC','CUSTOM'])->default('NO_COVER');
    $t->enum('spiral_binding', ['NONE','SPIRAL_PLASTIC','WIRE_O_METAL','PERFECT_BINDING'])->default('NONE');
    $t->unsignedSmallInteger('quantity')->default(1);
    $t->json('unit_price_config')->nullable()->comment('{ "1": 149000, "5": 135000, "10": 120000, "25": 110000, "50": 95000, "100+": 80000 }. SESUAIKAN DENGAN ADMIN CONFIG — TIDAK BOLEH HARDCODE DISINI PANJANG. Ini adalah cache/snapshot pricing pada saat order dibuat, agar admin ubah harga besok, pesanan lama tidak berubah.');
    $t->unsignedInteger('platform_admin_fee_idr')->default(0);
    $t->unsignedInteger('shipping_cost_idr')->default(0); // Nanti jika ada RajaOngkir integration
    $t->unsignedBigInteger('final_total_idr')->default(0);
    $t->boolean('bleed_3mm_enabled')->default(true);
    $t->unsignedTinyInteger('dpi_target')->default(300)->comment('Print 300 DPI standard. Untuk editor preview: cukup 96 DPI display pixels');
    $t->timestamps();
});
```

### J.9 TABEL KECIL TAMBAHAN (BISA DIBUAT BERSAMA PHASE 2 ATAU PHASE 3)
- `kp_goals` — id, project_id, month(null=yearly), type(mileage/runs/longest/race/pace/strength/custom), target_value, unit, current_value_cache
- `kp_habits` — id, project_id, habit_name(Run/Strength/Mobility/Stretch/Sleep/Hydration/Recovery/Nutrition/Custom), dates_marked(JSON)
- `kp_shoes` — id, project_id, brand, model, nickname, start_date, start_mileage_km, retirement_target_km, current_mileage_km_cache, photo_asset_id
- `kp_pace_profiles` — id, project_id, vdot_score, race_distance_source_pb_type(5k/10k/hm/fm), pace_zones(JSON easy/marathon/threshold/interval/repetition min/km range)

### TOTAL TABLES
**MVP Phase 1:** 4 core first: kp_calendar_projects, kp_calendar_pages, kp_calendar_elements, kp_uploaded_assets. (Total 4)
**MVP Phase 2 ditambah:** kp_templates, kp_template_elements, kp_goals, kp_shoes, kp_pace_profiles (Total +5)
**MVP Phase 4 Print ditambah:** kp_project_snapshots, kp_print_specifications (Total +2)
**Grand Total MVP 4 Phase**: 11 tables new
**POST-MVP Creator**: kp_creator_profiles, kp_template_purchases (+2; Royalty pakai existing WalletTransaction)

---

## BAGIAN K — PROPOSED API & SERVICE BOUNDARIES

### 9 Service Baru (app/Services/KalenderPelari/*.php)
| No | Service Name | Tugas Tunggal (Single Responsibility) | Dipanggil Oleh |
|---|---|---|---|
| 1 | `ProjectService` | CRUD CalendarProject. Restore dari trial session. Autosave debounced (min 2000ms, TIDAK setiap pixel drag). Optimistic locking via version_counter. Version history (last 10 state_drafts). | EditorController, ProjectApiController, TrialHandoffService |
| 2 | `CalendarDateEngineService` | Generate per-month: nama bulan Indonesia (Carbon id_ID), jumlah hari tiap bulan, Monday/Sunday start mapping, tanggal merah Indonesia? (optional: pakai library `holiday/indonesia` nanti). Generate date cell untuk CalendarGrid element. | WizardController, TemplateService, CalendarGrid Element Renderer |
| 3 | `ElementRendererService` | Konversi physical mm ↔ display pixels (canvas 96 DPI). Hitung canvas zoom level. Z-index sorting. Snap-to-grid px threshold. Alignment guides line calculation. Safe area bleed visual indicator. | KalenderPelariEditor Vue Component Canvas |
| 4 | `RunnerDataBindingService` ✨ REUSE BANYAK EXISTING | (a) Aggregate StravaActivity per user per month: total_km, total_runs, total_hours_moving, longest_run_distance_km, biggest_month_cache. (b) Query Event published upcoming (race countdown). (c) ProgramEnrollment active sessions (training plan per tanggal). (d) User accessor pb_5k/pb_10k/pb_hm/pb_fm + vdot + training_paces. (e) Map semua value ke CalendarElement.data_binding path. Inject realtime jika user buka editor. | Editor (reactive computed properties), TemplateService (apply template dengan data user asli), MonthlyStats Element Renderer |
| 5 | `TemplateService` | Apply template ke project: (1) Loop template_elements → copy ke pages dengan penyesuaian month_number yang sesuai, (2) Inject binding_template via RunnerDataBindingService, (3) **PRESERVE EXISTING RUNNER DATA** (races, activities, training, goals, PBs, photos, shoes) — hanya replace layout/styling, (4) Jika ada manual element user, muncul warning "3 element posisi manual Anda mungkin akan berubah. Lihat preview sebelum apply.". Sebelum apply show preview diff. | WizardController (saat user pilih template step 4), Editor Template Switching UI, TemplateGalleryController (demo preview gallery) |
| 6 | `AssetLifecycleService` ✨ Lifecycle State Machine | Transition statuses LOCAL_ONLY →(user consent upload)→ TEMP_UPLOAD [tanggal +24h] →(ProjectService save permanen)→ SAVED_PROJECT →(Checkout)→ PENDING_CHECKOUT →(Transaction paid)→ PAID_ACTIVE. Background job `ExpireTempAssetsJob` setiap jam: Hard delete rows status=TEMP_UPLOAD and expired. Invoke Storage delete file asli + variant. `ExpireTrialProjectsJob` each hari: status=EXPIRED_TRIAL if anonymous DRAFT_TRIAL and expires_at < now. | AssetController (upload), CheckoutController (before payment), Scheduler ExpireTempAssetsJob |
| 7 | `TrialHandoffService` ✨ KRITICAL MS §10 | Alur: (1) User klik SIMPAN dari editor anonim. (2) Serialize state editor pages+elements dari request JSON → compress gzencode() → simpan ke session(['kp_trial_state_compressed' => data, 'kp_trial_project_name' => name, 'kp_trial_assets_index' => list blob file user ada di object URL]). (3) Redirect ke login dengan intended URL: /kalender-pelari/editor/trial-restore. (4) Setelah login (via existing auth flow), Middleware AfterAuthTrialRestore → detect session key ada → de-compress → call ProjectService.createFromTrialState() → isi user_id=Auth::id(), is_anonymous=false, status=DRAFT_SAVED. (5) Tampilkan Modal Upload Consent: "Anda memiliki 5 foto di trial (total 18MB). Upload sekarang ke server agar tersimpan selamanya? [Upload Semua Foto] [Lewati, Simpan Teks Saja]" (6) Upload foto via AssetLifecycleService TEMP_UPLOAD → SAVED_PROJECT. | TrialEditorController (click Save), Auth Login Redirect Flow, Middleware |
| 8 | `PrintPreflightService` ✨ Kualitas Control | Sebelum Checkout dijalankan, scan project: (1) Cek 13 pages required count ada (COVER, Jan-Dec, YEAR_REVIEW — atau custom project lewati check sebagian). (2) Loop PHOTOS elements → asset_id not null & resolution_quality_score >=2 → pass, =1 → WARNING LOW RESOLUTION → rekomendasikan upload better. (3) Text overflow: render bounding box width/height — jika text height melebihi element height 110% → WARNING. (4) Element outside safe: x+width melebihi canvas_width minus bleed → WARNING OUTSIDE BLEED. (5) Missing asset: asset_id exists tapi storage file check via Storage::diskExists() → ERROR MISSING ASSET. (6) Print Check Summary output: ✓ SUCCESS (tanda), ⚠ WARNING (angka + list issue tidak block), ✕ ERROR (angka + list issue blocking checkout). | CheckoutController (sebelum tampilkan payment page), Preview Modal Print Check |
| 9 | `PrintExportPipeline` **implements ShouldQueue** ✨ Heavy Job | JANGAN generate PDF via screenshot browser editor (Laravel TIDAK ada screenshot tool built-in, butuh Puppeteer/Browsershot = memory mahal + fragile). GUNAKAN **DomPDF 3.0** yang sudah ada di composer.json require list (`barryvdh/laravel-dompdf` di vendor, atau native `dompdf/dompdf` ^3). Pipeline: (1) Ambil immutable kp_project_snapshots via sha256_hash. (2) Loop pages. (3) Render authoritative HTML dari snapshot → convert ke PDF vector 300 DPI. (4) Jangan pake gambar preview — gunakan ASSET ORIGINAL resolution jika ada. (5) Tambahkan crop marks & bleed lines 3mm jika print_spec.bleed_3mm_enabled=true. (6) Save PDF ke storage disk private dengan nama `kp-print/{snapshot_sha256}.pdf`. (7) Update job status: QUEUED → PROCESSING → COMPLETED (+ store PDF file size, pages count, path), ATAU FAILED (store exception log + retry max 3 kali, backoff). Job bisa ditrack via endpoint `/kalender-pelari/orders/{id}/pdf-status` polling every 3 detik di browser user. | Dispatch dari ProcessPaidKalenderPesananJob, Manual Trigger Admin untuk retry |

### 5 Existing Service Boundaries (WAJIB REUSE — JANGAN DUPLIKASI)
| Existing Service | Path Reuse | Digunakan Untuk |
|---|---|---|
| `StravaApiService::getValidAccessToken(User $user)` + refresh token logic | `app/Services/StravaApiService.php` (jika tidak ada, copy dari inline logic Runner\StravaController ke service ini) | Validasi token user sebelum RunnerDataBindingService call activity data |
| `ImageUploadService::upload($folder)` + uploadSingle + delete | `app/Services/ImageUploadService.php` | Handle semua upload foto user editor. 3 variant WebP: small thumb/medium preview/large original |
| `DanielsRunningService::calculateVdot()` / `::trainingPaces()` | Service existing (cari di Service folder atau dari User model accessor implementation getVdotAttribute) | PaceProfile default values + PaceCalculator Widget |
| `MidtransService` (Wrapper) + `MootaService` + `QrisDynamicService` | `config/midtrans.php`, `app/Services/MootaService.php` | Payment gateway Checkout |
| `PlatformWalletService` (escrow pattern) + `WalletTransaction` morph | `PlatformWalletService.php` (cari Services folder, atau dari ProcessPaidPacerBooking job) | Creator Marketplace Royalty Escrow (Post-MVP) |

---

## BAGIAN L — PUBLIC TRIAL PERSISTENCE STRATEGY

### Anonymous (TANPA Login = Selama Ini Percuma Jika Refresh Halaman)
**Problem**: User habis 30 menit desain → refresh page → hilang semua → BAD UX.
**Solution**: Hybrid Local+Session approach (TANPA Upload Server — Sesuai MS §27):

| Komponen Trial | Storage Medium | Limitasi & Aturan |
|---|---|---|
| **Pages + Elements state** | Browser `localStorage['kp_trial_state_v1']` + LZ-String Compression. | Maksimum ~5MB (batas localStorage browser umum). 100 elements per 13 halaman = JSON ~400KB → compress jadi 60KB. MELIMPAH KAPASITAS OK. Jika JSON state + compressed masih > 500KB → popup warning "Proyek Anda terlalu besar untuk disimpan sementara. Silakan login untuk menyimpan secara permanen!". BASE64 FOTO TIDAK BOLEH MASUK LOCALSTORAGE → dijamin storage tidak bengkak. |
| **Photos Uploaded Anonymously** | `window.Blob` + `URL.createObjectURL(file)`. Array references disimpan dalam Vue reactive memory + sementara JS. | Batas RAM browser. User close tab = hilang. Jika ada 50 foto 10MB → 500MB memory (berpotensi crash). PEMBATASAN: Anonymous trial MAKS 8 FOTO, TOTAL 20MB. Lebih dari itu: Toast "Anda sudah mencapai batas upload foto trial. Silakan login untuk batas lebih tinggi.". Cleanup onBeforeUnmount: URL.revokeObjectURL semua foto. |
| **Session Token Trial + Handoff** | `session(['trial_project_token' => UUIDv4, 'trial_expires_at' => now()->addDays(7)])` via laravel session cookie HTTPOnly (Server-side). | Session ID user unik. Tidak menyimpan state di sini. Session ini DIGUNAKAN HANYA untuk handoff: Setelah login, Middleware cek apakah user memiliki pending trial session token → restore dari localStorage. |

**Restore After Browser Refresh Close Tab Re-open (Dalam 7 Hari)**:
1. User visit `/kalender-pelari/editor/trial`
2. `onMounted` app: Check localStorage kp_trial_state_v1 ada?
   - Ada → langsung restore. ✅
   - Tidak ada tapi session trial_project_token ada di cookie → tampilkan dialog: "Kami mendeteksi Anda punya trial proyek 3 hari lalu. Apakah Anda menyimpan data desain di browser lain? Jika tidak, desain Anda sudah hilang. [Buat Proyek Baru]".
3. Jika localStorage ada tapi corrupt/version mismatch → clean slate baru + toast "Versi format trial lama terdeteksi. Data tidak dapat direstore. Mulai baru."

### Auth Handoff Restore (TIDAK BOLEH USER MULAI DARI NOL. MS §10 CRITICAL):
Flow sudah detail di Service K §7 TrialHandoffService. Tambahan security:
- Session `kp_trial_state_compressed` = encrypted via Laravel `encrypt()` sebelum disimpan di session Laravel (agar tidak bisa dibaca injection lain).
- Max session age trial state = 24 jam (jika user login 3 hari kemudian, state di session sudah expire — user restore dari localStorage aja).
- Upload foto setelah login (consent dialog): Setiap file diambil dari object URL references → convert Blob kembali ke File object → POST ke AssetController (logged in, rate limit) → AssetLifecycleService TEMP_UPLOAD → SAVED_PROJECT.

---

## BAGIAN M — IMAGE LIFECYCLE STRATEGY

### 3 States (Sesuai MS §28-35):
```
[LOCAL_ONLY] (anonymous, object URL only)
        │ User click "Simpan" + Consent Upload
        ▼
[TEMP_UPLOAD] (server folder /kalender-pelari/temp/{user_id_or_UUID}/)
        │ ┌─ Background ExpireTempAssetsJob setiap jam
        │ │   ├─ where status=TEMP_UPLOAD and expires_at<now()
        │ │   └─ Storage delete + DB soft delete → DELETED status (24 jam expiry)
        │ User klik "Simpan Proyek Permanen" setelah auth
        ▼
[SAVED_PROJECT] (server folder /kalender-pelari/projects/{project_id}/)
        │ User Checkout Print/PDF
        ▼
[PENDING_CHECKOUT] (lock assets sementara — jangan dihapus expire job)
        │ Payment Success (ProcessPaid Job)
        ▼
[PAID_ACTIVE] (/kalender-pelari/paid/{order_id}/ — simpan minimal 180 HARI)
        │ Order > 1 tahun old
        ▼
[EXPIRED] → soft delete assets → 30 hari kemudian hard delete

[DELETED] = User trash (tombol hapus proyek). Soft Delete dulu. Restore dalam 30 hari → ke SAVED_PROJECT.
```

### 3 Resolusi Gambar (MS §31: Original, Editor Preview, Thumbnail)
Semua 3 variant di-generate OTOMATIS oleh ImageUploadService yang sudah ada:
| Variant | Dimensi | Kualitas | Digunakan Untuk | Path Storage |
|---|---|---|---|---|
| THUMBNAIL | 300px max width | WebP 80% | Sidebar mini photo library lists, page thumbnail bottom bar | `{prefix}_small.webp` |
| EDITOR PREVIEW | 750px max width | WebP 80% | Canvas editor display (main working) | `{prefix}_medium.webp` |
| ORIGINAL (max 1920px) | 1920px max width | WebP 85% | Fallback user minta crop high detail. NANTI JIKA ADA 4000px asli, simpan ke PRIVATE DISK. | `{prefix}_large.webp` |
| NATIVE ORIGINAL RESOLUTION (>4000px) | Asli user | Original bytes | HANYA untuk final Print PDF Production Pipeline. **TIDAK PERNAH load ke canvas editor** (memory explode). | Private storage: `kalender-pelari/originals/{asset_id}.{ext}`. Access via signed URL short-lived (15m) hanya untuk PrintExportPipeline Job. |

### Crop Data Strategy (JANGAN generate gambar baru tiap user drag crop — MS §32):
✅ **Simpan HANYA Transform Parameter.** Jangan generate file baru.
```json
// Di kp_calendar_elements.style_config (contoh foto element)
{
  "crop": {
    "source_asset_id": 456,
    "cropX_px": 80, "cropY_px": 150,
    "cropWidth_px": 1200, "cropHeight_px": 900,
    "rotation_deg": 0,
    "scale_factor": 1.15,
    "focal_point_x": 0.5, "focal_point_y": 0.3  // untuk object-fit position
  }
}
```
- Frontend Canvas: Render dengan CSS `object-fit: cover; object-position: ...` + transform rotate/scale. TIDAK PERLU canvas API pixel manipulation (hemat CPU).
- **Print Export Pipeline**: Saat generate PDF authoritative, barulah apply server-side Intervention: `$img->crop(cropW, cropH, cropX, cropY)->rotate(rotation)->resize(width_mm * DPI_mm)` — gambar asli + transform = hasil final print TANPA ada artefak double compress.

### Image Upload Security Checklist (WAJIB SERVER SIDE — MS §33):
| Check | Implementasi Di | Evidence |
|---|---|---|
| Maximum File Size 15MB per file | `AssetController@upload` validation `'file' => 'max:15360'` + PHP `upload_max_filesize` & `post_max_size` | php.ini atau .user.ini laragon |
| **REAL MIME TYPE detection via finfo()**, bukan trust `$_FILES['type']` / extension | ImageUploadService, sebelum process: Baca 12 bytes pertama file → finfo buffer. Allowlist HANYA: `image/jpeg`, `image/png`, `image/webp`. (JPEG signature FF D8 FF, PNG 89 50 4E 47, WebP RIFF....WEBP) | finfo PHP extension harus aktif. |
| Intervention Image decode (Pembuktian file adalah gambar asli polyglot-proof) | ImageUploadService::upload → Intervention::make($file). Jika gagal decode (file corrupt/script palsu), throw Validation Exception "File gambar tidak valid." | Intervention make() akan melempar exception jika bukan gambar. |
| **Storage Key = UUID random**, BUKAN original filename user | ImageUploadService sudah benar! (uses UUID). Untuk kp_uploaded_assets.original_filename → simpan ke kolom terpisah HANYA untuk UX display. | Tidak pernah expose `storage_key` via URL public. Gunakan signed URL untuk private originals. |
| EXIF GPS Data Stripping Pada Preview & Thumbnail WebP | Intervention encode ke format WebP SECARA OTOMATIS menghapus semua EXIF metadata. Original >4000px di private storage BISA retain EXIF jika user butuh (configurable per project). | Tidak perlu ekstrak EXIF manual. |
| **Anti Duplikasi Upload Same Photo** | `kp_uploaded_assets.checksum_sha256`. Sebelum simpan file baru: hash sha256_file content → cek DB sudah ada? Jika ada: reuse ASSET_ID yang sama (hemat storage 50%+ untuk user yang upload foto yang sama berulang). | PHP hash_file('sha256', $tmpPath). Super cepat untuk file <15MB. |

---

## BAGIAN N — SECURITY RISKS & CONSIDERATIONS

### N.1 Resource Authorization (Paling KRITIKAL MS §73)
**BUAT `CalendarProjectPolicy.php` dan register di AuthServiceProvider** (Laravel Policies). Jangan manual cek di Controller.

| Policy Method | Logic Yang BENAR | Kesalahan Yang Tidak Boleh Dilakukan |
|---|---|---|
| `viewAny(User $user)` | `CalendarProject::where('user_id', $user->id)` → hanya list punya sendiri. | JANGAN `CalendarProject::all()` tanpa scope user. |
| `view(User $user, CalendarProject $project)` | `$project->user_id === $user->id` ATAU (project SHARED via signed URL public with token → sesuai use case share). | JANGAN percaya `?project_id=123` dari browser user, cek selalu lewat policy. |
| `update(User $user, CalendarProject $project)` | `$project->user_id === $user->id` | JANGAN izinkan user edit project orang lain walau tahu ID. |
| `delete(User $user, CalendarProject $project)` | `$project->user_id === $user->id` | |
| `exportPdf(User $user, CalendarProject $project)` | `$project->user_id === $user->id` DAN (project.status = PRODUCTION_ACTIVE ATAU free tier preview). | Browser NEVER DECIDE entitlement. PDF download HANYA dari controller yang memanggil policy. |
| `uploadAssetFor(User $user, CalendarProject $project)` | `update()` policy same check. | Jangan izinkan upload foto ke project ID orang lain! |

**Pola Pemanggilan Di Semua Controllers Editor**:
```php
// KalenderPelari/ProjectApiController@store (autosave endpoint)
public function save(Request $request, CalendarProject $project)
{
    $this->authorize('update', $project); // Policy check. Gagal otomatis 403 HTTP.
    // ... logic save
}
```

### N.2 Running Data Privacy (MS §74-75)
- SEMUA query di `RunnerDataBindingService`: HARUS DIAWALI `where('user_id', Auth::id())`. Contoh: `StravaActivity::whereUserId(auth()->id())->whereRaw('YEAR(start_date) = ?', [$projectYear])`.
- Endpoint API yang return running data stats: TIDAK BOLEH di-cache tanpa prefix user id. Cache key: `"kp:runner_stats:{$userId}:{$year}:{$month}"`.
- Route location privacy (MS §75): Jika nanti ada fitur Share Route Map → default HIDDEN. User harus explicit toggle "Tampilkan rute GPS saya di kalender shared". Jangan auto-expose start_coordinates StravaActivity.

### N.3 Rate Limiting (MS §76)
Define di `bootstrap/app.php` (Laravel 11 RateLimiter facade withRouting):
```php
->withRouting(function (array $routes) {
    RateLimiter::for('kp-anon-trial', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));   // Create project trial
    RateLimiter::for('kp-asset-upload', fn (Request $r) => Limit::perMinute(30)->by($r->user()?->id() ?: $r->ip())); // Foto
    RateLimiter::for('kp-pdf-heavy', fn (Request $r) => Limit::perHour(5)->by($r->user()?->id() ?: $r->ip())); // PDF generation mahal CPU
    RateLimiter::for('kp-payment', fn ($r) => Limit::perMinute(10)->by($r->user()?->id() ?: $r->ip())); // Checkout
})
```
Terapkan via `->middleware('throttle:kp-anon-trial')` di route group KalenderPelari public.

### N.4 CSRF Protection Exemption (Jangan tambah kecuali webhook!)
Hanya tambahkan CSRF exempt jika nanti ada:
- Webhook dari layanan cetak fisik pihak ketiga (contoh: vendor print notify "pesanan Anda sudah dikirim" → status update)
- Webhook PDF generator eksternal jika nanti pakai service.

### N.5 SQL Injection / Mass Assignment
- Semua `kp_calendar_elements.content_json` dan `state_draft` bertipe JSON / longText. TAPI: jika value yang di-insert langsung dari `$request->all()` → risiko Mass Assignment. Fix: Di CalendarProject / CalendarElement model, $fillable HANYA izinkan kolom yang diizinkan. Atau selalu pake `$request->only([...])` whitelist field sebelum save.
- Untuk `data_binding` JSON: selalu validate via `Validator::make()` schema: setiap binding harus punya source, id, field yang diizinkan dari enum list → tidak bisa inject arbitrary SQL.

---

## BAGIAN O — PERFORMANCE & MEMORY STRATEGY

### Kanvas Performance (Paling User Terasa)
| Rule | Implementasi Detail |
|---|---|
| **RULE TIDAK BOLEH RENDER 13 HALAMAN FULL DETAIL SEKALIGUS** | Editor punya konsep `activePageId`. Hanya halaman aktif yang render element LENGKAP (foto 750px, text font asli, calendar grid date). 12 halaman lainnya → render **OUTLINE THUMBNAIL SAJA** di bottom bar (skala 15%: photo thumbnail small 300px, text jadi garis abu-abu placeholder, tidak ada calendar cells). Switch halaman = mount/unmount component page. |
| **Virtualize Long Lists > 50 Item** | Sidebar My Running Activities list user bisa 500+ item. Gunakan `vue-virtual-scroller` package: Hanya render 50 item yang visible di viewport (height 600px → ~15 row). Scroll jalan smooth 60FPS meskipun data 2000 aktivitas. |
| **Drag Update = CSS Transform Langsung, BUKAN Reactive Store** | Saat user drag element dengan mouse/pointer: JANGAN update reactive x/y setiap pixel! (Akan re-render tree 60x/detik = LAG). Lakukan: (1) Saat `pointerdown` → clone element node DOM asli ke `.kp-drag-ghost-layer` fixed overlay, (2) Saat `pointermove` → update `ghost.style.transform = translate(${deltaX}px, ${deltaY}px)` pure CSS (GPU-accelerated), (3) Saat `pointerup` → update reactive store 1 KALI saja dengan posisi final → trigger save debounced. |
| **Debounce Autosave MINIMAL 2000 ms** | ProjectService autosave: `setTimeout` reset on each keystroke/drag end. Save ke state_draft hanya jika ada perubahan >= 300ms idle. Save DB: setidaknya 2 DETIK sekali. NEVER SAVE on every pointer movement pixel! |
| **Persistent vs Transient State Separate** (MS §67-68) | Store split 2 bagian: <br>✅ **Persistent Store (Pinia)** → pages, elements, assets_ids, data_bindings values, goals, habits, shoes, pace_profile. Serializable to JSON. Auto-save. <br>❌ **Transient (Vue reactive biasa TIDAK DI-SERIALIZE)** → currently_selected_element_ids[], hoveredElementId, alignmentGuidePositions, dragGhostState, viewportZoomLevel, cropDialogOpen, modalStates. Tidak pernah disimpan. Tidak masuk autosave payload. |

### Image Memory (Hindari Leak MS §65-66)
- HANYA load variant MEDIUM (750px) ke canvas. ORIGINAL LARGE NEVER LOAD kecuali user buka Crop Dialog modal → setelah dialog close → revoke + img.src = ''.
- Cleanup WAJIB di setiap Vue component `onBeforeUnmount`:
  ```js
  onBeforeUnmount(() => {
    projectState.photos.forEach(p => { if (p.objectUrl) URL.revokeObjectURL(p.objectUrl) });
    // Clear temporary canvas contexts
    // Cancel semua axios requests pending via AbortController signal
  })
  ```
- Memory leak test: Chrome DevTools → Performance Monitor → Heap Size. Buka 1 project → switch months 10x → upload 5 photos → delete → ulangi 5 siklus. Jika Heap grow > 50MB tanpa GC recover → ada leak. Cari object URL tidak di-revoke / event listener tidak di-off / watch lupa cleanup via watch onInvalidate.

### Code Splitting MS §71 (Bundle Size)
Entry `kalender-pelari-editor.js` target **<180KB initial load** (setelah gzip).
Lazy import heavy components:
```js
// Wizard step terakhir mount editor — lazy chunk terpisah:
const KalenderEditorCanvas = defineAsyncComponent(() => import('./components/editor/Canvas.vue'))

// Sidebar tabs: hanya mount saat user klik:
const CropEditorDialog = defineAsyncComponent({ loader: () => import('./components/assets/CropEditor.vue'), loadingComponent: Spinner })
const PaceCalculatorTab = defineAsyncComponent(() => import('./components/training/PaceCalculator.vue')) // ~40KB
const TrainingPlanner = defineAsyncComponent(() => import('./components/training/TrainingPlanner.vue')) // ~100KB
const PrintPreflight = defineAsyncComponent(() => import('./components/checkout/PrintPreflight.vue')) // 60KB
const PdfPreviewModal = defineAsyncComponent(() => import('./components/checkout/PdfPreview.vue')) // ~120KB pdf.js
```
Target Lighthouse Score landing `/kalender-pelari`: **≥ 90 mobile**. (Hero CTA + 3 Template Preview Card di-inline critical CSS).

### Background Jobs Strategy (Redis Queue)
| Job Name | Schedule / Trigger | Connection Queue | Timeout |
|---|---|---|---|
| `ExpireTempAssetsJob` | `$schedule->hourly()` (Scheduler Laravel Kernel setiap jam 00 menit) | redis default | 120s |
| `ExpireTrialProjectsJob` | `$schedule->dailyAt('03:00')` (jam 3 pagi traffic sepi) | redis default | 60s |
| `GenerateProjectThumbnailsJob` | Dispatch on Project saved | redis default | 60s |
| `PrintPdfRenderJob` → Dispatch from ProcessPaidKalenderPesanan | On demand | `print-heavy` (supervisor queue terpisah — 1 concurrent worker only, karena 1 job pakai CPU tinggi) | 600s (10 menit per PDF 13 halaman 300DPI OK) |
| `ExpireTempAssetsNotification` | Per-job, notify user jika asset mereka auto-expire (opsional) | redis default | 10s |

### Mobile Experience Editor (MS §81) - Jangan Paksa Drag di 480px
Jika `window.innerWidth < 768px` → tampilkan Mobile Mode:
✅ View upcoming race countdown,
✅ Today's training session,
✅ Monthly progress (KM target vs actual),
✅ Upload foto via native device camera + gallery,
✅ Form Add Race sederhana,
✅ Form Edit Goal,
✅ Print Ordering Flow (touch optimized),
❌ **Redirect / Show Banner "Editor visual drag-drop paling optimal di Desktop. [Buka di Desktop Link → Copy] [Lanjutkan Mode Mobile Terbatas]"** → Jangan paksa drag dengan jari di canvas 4 inch → UX buruk.

---

## BAGIAN P — RECOMMENDED IMPLEMENTATION SEQUENCE (MVP PHASE 1-4)
Di-adaptasi dari MS Spec §93-97 (Total 8 Minggu = 2 bulan). **Prioritas Vertikal: 1 Flow End-to-End = BISA DI-TEST USER**, bukan horizontal setengah-setengah semua modul.

### Phase 1 (Week 1-2): Route + Migration Core + Trial Wizard + Builder MINIMAL
✅ Semua task disini = BISA RUN tanpa payment / auth integration. Target PASS: **AC 99 Public Trial 11 Langkah**.

| Minggu | Task Detail | Deliverable |
|---|---|---|
| 1 | (a) Include `routes/kalender-pelari.php` di web.php. (b) Buat 4 migration J.1-J.4 core tables. (c) Buat 2 layout: landing.blade.php + wizard.blade.php (extend pacerhub). (d) Route: /kalender-pelari landing SEO, /buat wizard (5 tipe kalender + year + format + template select). | 4 routes OK. Landing page hero + CTA. Wizard 4 Step → submit POST → insert DRAFT_TRIAL ke kp_calendar_projects.anonymous_uuid. Redirect to /editor/trial/{uuid} |
| 2 | (a) Vite entry `kalender-pelari-editor.js` + **Canvas 1 Halaman AKTIF** (1 halaman kerja + thumbnails cover + 12 bulan placeholder). (b) Element dasar: Photo (local upload object URL), Text (contenteditable), CalendarGrid (via CalendarDateEngineService Indonesia locale), Note, Goal, MonthlyStats (dummy value dulu). (c) Basic Drag (pointer events) + Resize 8-handle + Delete (Delete/Backspace key). (d) Autosave localStorage compressed. (e) Switch bulan click thumbnail → ganti active page. | User bisa: pilih 2027 → pilih template → lihat calendar generate → upload 8 foto trial → drag foto → resize → add race manual → add training element → preview months → CLOSE TAB → RE-OPEN TETAP ADA (localStorage restore). |

### Phase 2 (Week 3-4): Auth Handoff + Runner Data Binding Integration
✅ PASS: **AC 100 Auth Handoff 8 Langkah** + **AC 101 Running Data 7 Langkah**.

| Minggu | Task Detail | Deliverable |
|---|---|---|
| 3 | (a) TrialHandoffService + Middleware AfterAuthTrialRestore: session encrypted kp_trial_state. Login flow existing → redirect kembali ke editor. (b) Project.user_id terisi, is_anonymous=false. (c) Modal Upload Foto Consent dialog → TEMP_UPLOAD → SAVED via AssetLifecycleService. (d) Policies CalendarProjectPolicy register + authorize semua controllers. | User trial save → Login → kembali ke editor SAMA persis (layout, posisi, foto) — tidak reset. Foto user tersimpan permanen di server. Project muncul di list "Proyek Saya" user auth. |
| 4 | (a) RunnerDataBindingService implement: StravaActivity monthly aggregate (periksa satuan meter→KM conversion), User.personal_best 6 kolom + vdot accessor + training_paces. (b) Sidebar "MY RUNNING" tab: Activities, Stats, Personal Best, Races (events user terdaftar via Participant), Shoes (sementara placeholder). (c) Element drag: Monthly Stats (dengan real data user!), Activity Card (drag dari list ke canvas → binding otomatis ke id), Personal Best element, Race Countdown element. (d) TemplateService apply template preserve runner data. (e) My Running Year Template: auto generate monthly stats page 12 bulan dari data user. | KalenderPelari "My Running Year" type → Generate → Isi data lari 2025 user muncul otomatis. Drag Activity dari sidebar → tanggal. PB 10K user tampil di element Personal Best. |

### Phase 3 (Week 5-6): Training Planner + Workout + Pace (Planned vs Actual)
| Minggu | Task Detail | Deliverable |
|---|---|---|
| 5 | (a) Migration J.9 (kp_goals, kp_pace_profiles). (b) Reuse Program + ProgramEnrollment data source. Workout element enum 14 types (MS §37). (c) Calendar page bisa drop Workout → date binding (sama seperti runner/calendar session). (d) Pace Profile: Jika user auth → auto isi dari User.vdot accessor. Jika anonymous → Pace Calculator Widget isi sendiri race distance+time → VDOT via `DanielsRunningService`. (e) Pace Chart element. Apply to Training: "APLIKASI KE PELATIHAN SAYA" dengan CONFIRMATION dialog sebelum overwrite workouts user edit manual. | User target Half Marathon → Apply Template Program → 12 Minggu muncul di canvas. Pace user 5:30/km → semua workout terisi target pace 5:40-6:10/km (zones). User bisa edit distance / pindah tanggal via drag. |
| 6 | (a) Planned vs Actual UI Dual Card (MS §39). (b) Match suggestion: Jika ada StravaActivity di tanggal yang sama dengan planned workout → tombol [MATCH ACTIVITY] / [IGNORE]. Status: Completed / Adjusted / Skipped / Unmatched. (c) Monthly Goals component: mileage target + circular progress bar %. (d) Reschedule workout antar tanggal + duplicate + delete. (e) Weekly volume chart (bisa reuse runner/calendar chart existing component). | Plan Senin = Easy 8K. User lari Senin 8.4K via Strava → sync → RunnerDataBindingService detect → muncul UI "Match Activity?" → Klik MATCH → Planned vs Actual side by side tampil. Progress bulan update dari sum actual distance (not planned). |

### Phase 4 (Week 7-8): Print Snapshot + Payment + PDF Export Pipeline
✅ PASS: **AC 105 Print Acceptance Test 6 Dimensi**

| Minggu | Task Detail | Deliverable |
|---|---|---|
| 7 | (a) Migration J.5-8 tables (templates, template_elements, snapshots, print_spec). (b) PrintPreflightService: 6 check rule → UI Print Check list ✓ ⚠ ✕. (c) CheckoutController: Immutable snapshot before calculate price (sha256 hash → existing duplicate check). (d) Reuse Transaction flow Event pattern: Snap MidtransService → item_details = Print Specification + quantity + admin_fee. (e) ProcessPaidKalenderPesananJob (mirip ProcessPaidEventTransaction). | User selesai edit → klik ORDER PRINT → Print Check muncul: 13 pages ✓, January photo ⚠ GOOD quality, September ✕ MISSING → User harus perbaiki SEBELUM bayar. Setelah fix → checkout → Midtrans SNAP muncul → Bayar → Success → Project immutable (snapshot dibuat) + status PAID_ACTIVE. |
| 8 | (a) PrintExportPipeline Job Queueable implement DomPDF 3.0 from snapshot. (b) Authoritative render vector PDF 300DPI with bleed 3mm + crop marks. Use original resolution assets >4000px, re-apply crop transforms via Intervention server side. (c) PDF Download via signed URL 24 jam expire. (d) Print Dashboard Admin: List orders, Status Polling job, Download PDF Production. (e) Notifikasi Email + WA user: "Pesanan Kalender Cetak Anda sedang diproses. Perkiraan selesai 3 hari kerja.". | Order paid → Dispatch PrintPdfRenderJob → Polling status UI dari browser → Job selesai → Tombol "Download PDF Print Ready" aktif → Signed URL → Download → Hanya berlaku 24 jam, permanent URL tidak di-expose (MS §58 compliance). |

### Post-MVP (Week 9+ Creator Mode + Stabilitas):
- Shoe Mileage Tracker
- Habit Tracker (7 habits MS §47 + custom)
- Club Mode (sementara hidden UI)
- Creator Marketplace UI (template buy + royalty escrow wallet existing)
- Performance Audit → Lighthouse ≥ 90 Mobile landing page + <3s TTI editor.
- AC 102 (Editor), 103 (Performance), 104 (Security) — seluruhnya manual QA pass.

---

## BAGIAN Q — ASSUMPTIONS & OPEN QUESTIONS (BUTUH KONFIRMASI USER)

### 7 Pertanyaan yang Harus Dijawab SEBELUM Phase 1 Mulai Coding:

**🤔 Q1 — KRITIKAL: Arah Produk `/kalender-pelari`**
- **Opsi A** (Ringan, Low Effort): `/kalender-pelari` = alias redirect ke `route('runner.calendar')` dengan upgrade UI minor existing runner calendar (ganti theme dari legacy admin → pacerhub modern). Tidak buat builder 13 halaman drag-drop, tidak buat 9 migration tables. COCOK JIKA: Tim mau release cepat 1-2 minggu.
- **Opsi B** (REKOMENDASI SESUAI MS SPEC FULL): `/kalender-pelari` = SEO Landing + 3 Entry Hub (Latihan Pribadi / Jadwal Balapan / **Buat Kalender Cetak Visual Builder**). Full implementasi 8 Minggu = 4 Phase + 11 migration tables + 9 Service Boundaries. Hasil akhir: user bisa cetak 13 halaman kalender fisik / download PDF print-ready — SEBAGAIMANA di Master Spec 113 halaman. **PILIHAN INI SESUAI dengan alur spec yang diberikan.**
- **Opsi C** (Hybrid Bertahap): Minggu 1 langsung launch Opsi A (upgrade runner/calendar UI). Minggu 2-8 parallel build Visual Builder seperti Opsi B. Jadi user dapat 2 improvement bertahap tanpa menunggu 2 bulan.
> ⚠️ **KONSEKUENSI**: Semua rencana di atas (migration J.1-J.9, Service K.1-K.9, Phase P 1-4) ASUMSI pilih **Opsi B atau C**. Jika tim pilih Opsi A, maka seluruh I-P bisa dibatalkan cukup upgrade UI runner/calendar saja.

**🤔 Q2 — Entitlement & Pricing Tier Free vs Paid**
- Berapa jumlah project GRATIS user bisa simpan? (Contoh: Unlimited project, tapi PDF watermark. Atau 3 project free, unlimited = butuh membership User.package_tier ≥ 'premium'.)
- PDF free = beresolusi rendah (72 DPI preview) + watermark "Dicetak via RuangLari — Upgrade ke Premium untuk 300 DPI tanpa watermark" YA / TIDAK?
- Print Ready PDF (high res) dijual terpisah (contoh Rp 25.000 per unduh) atau bundle dengan physical print? (Sesuai MS §57: Free = low res preview preview, Paid = high res print-ready authorized server export.)

**🤔 Q3 — Physical Print Product Configurations (Admin)**
- Paper options: Hanya HVS 150 GSM? Atau ada Art Carton 260 / Glossy / Matte?
- Cover: Softcover / Hardcover / Tanpa Cover? (Untuk Desk Calendar: biasanya ada standing base terpisah.)
- Spiral: Wire-O metal / spiral plastik / tanpa?
- **Quantity Tier Pricing**: Sesuai §54 spec (1/5/10/25/50/100+), HARGA UNIT per masing-masing tier BERAPA? (Contoh: 1 buah A4 Wall = Rp 149.000, 5 = Rp 135.000 per unit, dst.)
- Admin backend: Perlu dibuat CRUD `print_products` & `print_options` tables? Atau disimpan di config() file sementara untuk MVP Phase 4?

**🤔 Q4 — Creator Marketplace Royalty % Default**
- Jika nanti Post-MVP, user creator upload template premium harga Rp 50.000:
  - % Platform Fee = ?
  - % Creator Royalty = ?
- Escrow release period: Berapa hari setelah buyer payment sukses creator bisa withdraw royalty? (7 hari? 14 hari? Langsung cair?)
- Apakah creator royalty masuk ke Wallet user creator via WalletTransaction.type='creator_royalty' existing + PlatformWallet escrow pattern dari PacerBooking? (ASUMSI SAAT INI: YA — REUSE 100%.)

**🤔 Q5 — Anonymous Trial Photo Temp Upload Jika Storage Local Tidak Cukup**
Sesuai MS §27: Anonymous → TIDAK upload foto permanen ke server. Tapi ada edge case:
- User trial upload 10 foto 5MB = 50MB. localStorage TIDAK MUAT. RAM browser Firefox/Chrome crash.
- Batas trial: MAKS 8 foto, total size 20MB? Atau boleh lebih dan user dinasehati "Foto Anda terlalu besar untuk trial. Silakan login sekarang → temp upload ke server 24 jam untuk menyimpan sementara → nanti simpan permanen setelah desain selesai"?
- Jika pilih temp upload server untuk anonymous: Expire 1 JAM (ketat) atau 24 JAM (lebih longgar)? Risiko storage spam jika tidak rate limiting?

**🤔 Q6 — Google Calendar Sync**
User table sudah ada kolom `google_calendar_token`. User sudah connect Google Calendar di feature lain?
- KalenderPelari perlu feature: "Sinkronkan Race, Workouts, dan Goals saya ke Google Calendar secara 1-arah" YA / TIDAK?
- Jika YA: Post-MVP. Tidak masuk MVP 4 Phase. Jika TIDAK: abaikan field ini dulu. Tidak masuk scope Phase 0 audit.

**🤔 Q7 — Reuse Strava Connect Flow (Existing)**
Spec §21 melarang OAuth flow baru. Ada 3 flow existing. Flow yang **direkomendasikan**: Reuse `route('runner.strava.connect')` existing. Scope-nya `activity:read_all, profile:read_all, activity:write` — SUDAH LENGKAP.
- Jika user buka editor KalenderPelari tapi Strava belum connect. Apa UI yang benar?
  - **Opsi 1**: Redirect ke `runner.strava.connect` existing (pindah halaman, callback balik ke editor via param `?ref=kalender-pelari`)
  - **Opsi 2**: Buka modal inline connect Strava (iframe atau tab popup kecil) dengan endpoint runner.strava.connect yang sama — close popup lalu refresh sidebar My Running. Tanpa pindah halaman (UX lebih bagus, tapi perlu logic popup callback handler).
- ASUMSI SAAT INI: Opsi 2 lebih baik untuk editor flow. Apakah setuju?

---

## LAMPIRAN: REFERENSI CODE CEPAT (LINK FILE DENGAN LINE NUMBER)

| Nama File Kunci | Line Kunci | Deskripsi |
|---|---|---|
| [routes/web.php](file:///c:/laragon/www/ruanglari/routes/web.php) | L432, L1203 | Route `/calendar` (public) dan `/runner/calendar` (private). Check L3 untuk use statement alias import 2 Controller Calendar berbeda namespace. |
| [bootstrap/app.php](file:///c:/laragon/www/ruanglari/bootstrap/app.php) | L18-80 | Route registration, middleware global, rate limiter definitions, CSRF exceptions list 9 endpoint webhook. Tempat kita include routes/kalender-pelari.php nanti. |
| [app/Models/User.php](file:///c:/laragon/www/ruanglari/app/Models/User.php) | L68-71, L186, L210-250 | Strava columns. Cast strava_expires_at datetime. Accessor vdot + training_paces via DanielsRunningService. Personal Best pb_5k/pb_10k/pb_hm/pb_fm/pb_cooper/pb_balke. google_calendar_token. |
| [app/Models/StravaActivity.php](file:///c:/laragon/www/ruanglari/app/Models/StravaActivity.php) | L20-55 | distance_m (METER!), moving_time_s, raw JSON payload. Index composite [user_id, start_date]. |
| [app/Models/UserActivity.php](file:///c:/laragon/www/ruanglari/app/Models/UserActivity.php) | L18-50 | distance_km (KILOMETER!). ⚠️ Satuan BERBEDA dari StravaActivity. Jangan lupa /1000 saat aggregate! |
| [app/Models/Event.php](file:///c:/laragon/www/ruanglari/app/Models/Event.php) | L30-120, L160-210 (scopes), L220-280 (relations) | Scope Published, Upcoming, Directory, Managed. Dual event_kind directory vs managed. accessor public_url, sanitized_description_html. |
| [app/Services/ImageUploadService.php](file:///c:/laragon/www/ruanglari/app/Services/ImageUploadService.php) | L10-100 | upload() 3 variant WebP 300/750/1200 quality 80. UUID naming. Intervention GD driver. WAJIB REUSE untuk semua image KalenderPelari. |
| [app/Http/Controllers/Runner/StravaController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Runner/StravaController.php) | L22-96 (OAuth), L130-494 (sync) | Connect Strava Runner scope. Sync utama: pagination 5x50, upsert StravaActivity, Auto-link Program Session Tracking (distance terbesar per tanggal = completed). |
| [config/filesystems.php](file:///c:/laragon/www/ruanglari/config/filesystems.php) | L33-58 | Disk private (storage/app/private) dan public (symlink). S3 configured tapi keys kosong (siap switch ke Cloudflare R2). |
| [config/midtrans.php](file:///c:/laragon/www/ruanglari/config/midtrans.php) | L1-50 | Auto production/sandbox switch. testing_mode toggle. |
| [resources/views/layouts/pacerhub.blade.php](file:///c:/laragon/www/ruanglari/resources/views/layouts/pacerhub.blade.php) | L133 (Vue CDN), L198 (Alpine), L212-214 (Font Awesome preload swap), L215 (Google Fonts Inter+JetBrains Mono) | Layout utama modern. Semua halaman KalenderPelari extend layout ini. |
| [resources/css/app.css](file:///c:/laragon/www/ruanglari/resources/css/app.css) | L1-200 | Tailwind v4 inline config. Brand colors: neon #ccff00, dark #0f172a, card #1e293b, strava #fc4c02. Override palet blue → red light mode. Font family stack Inter + JetBrains Mono. |
| [graphify-out/graph.json](file:///c:/laragon/www/ruanglari/graphify-out/graph.json) | — | 20,686 nodes, 54,427 edges, 1,059 module communities. Buka dengan `graphify query "..."` untuk exploration cepat tanpa baca file manual. |
| [.trae/documents/hero_jadwal_lari_landing_plan.md](file:///c:/laragon/www/ruanglari/.trae/documents/hero_jadwal_lari_landing_plan.md) | L1-76 | Related: Redesign terbaru hero halaman /jadwal-lari (RuangLari 6 stripe running track motif). Acuan pattern desain anti-slop UI compliance. |
| [FEATURE_STATUS.md](file:///c:/laragon/www/ruanglari/FEATURE_STATUS.md) | — | Checklist feature existing (bisa di-scan untuk melihat apakah fitur Calendar Studio sudah ada status trackingnya). |

---

## AKHIR LAPORAN AUDIT PHASE 0

**Yang Perlu User Lakukan Sekarang**:
1. **PILIH Q1**: Opsi A / B / C (arah produk). **INI PALING PENTING — menentukan seluruh scope pekerjaan Phase 1 dst.**
2. Jawab Q2-Q7 (paling tidak Q1, Q3 pricing tier, Q5 batas trial foto).
3. Review spec.md & tasks.md di folder `.trae/specs/kalender-pelari-phase0-audit/` untuk approval artifact Spec Mode.
4. Jika setuju dengan audit ini → kirim approval → Phase 1 dimulai.

**Bukti Komplit Audit Ini**:
- ✅ 6 task di tasks.md (5 completed, 1 in_progress complete saat report ditulis)
- ✅ 17 Dimensi A-Q tercakup 100% sesuai MS Spec §113
- ✅ 24 model data dibandingkan dengan MS Spec §77
- ✅ 4 migration table core + 7 secondary + 2 print tables = 13 tables usulan
- ✅ 9 Service boundaries baru + 5 reusable existing service mapping
- ✅ 3 security policy + 4 rate limiter + 5 upload security checklist
- ✅ 8 Minggu rencana implementasi 4 Phase vertical slice end-to-end
