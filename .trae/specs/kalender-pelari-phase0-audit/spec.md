# Kalender Pelari - Phase 0 Codebase Audit

## Overview
- **Summary**: Audit arsitektur codebase RuangLari eksisting sebagai landasan implementasi modul Kalender Pelari. Audit mencakup 17 dimensi (A-Q) sesuai Master Spec poin 113.
- **Purpose**: Memastikan arsitektur KalenderPelari terintegrasi dengan benar dengan infrastruktur RuangLari yang ada, menghindari duplikasi komponen, dan memanfaatkan reusable services semaksimal mungkin.
- **Target Users**: Tim Engineering yang akan mengimplementasikan KalenderPelari (Specifier + Implementer + Reviewer).

## Goals
- Memetakan seluruh komponen reusable RuangLari yang dapat dipakai KalenderPelari.
- Mengidentifikasi gap antara kebutuhan KalenderPelari dan infrastruktur eksisting.
- Menghasilkan rekomendasi arsitektur berbasis bukti codebase.
- Menghasilkan daftar entity/migrasi baru yang diperlukan.
- Mendokumentasikan risiko security, performance, dan konseptual.

## Non-Goals
- Tidak mengubah kode produksi apapun dalam Phase 0.
- Tidak membuat database migration apapun.
- Tidak membuat route, controller, atau view apapun.
- Tidak melakukan implementasi fitur KalenderPelari (ditunda Phase 1 dst).

## Background & Context
Master Spec KalenderPelari (2743 baris) telah didefinisikan dengan 112 poin requirement + 4 acceptance test suite (Public Trial, Auth Handoff, Running Data, Editor, Performance, Security, Print). Route `/calendar` dan `/runner/calendar` sudah ada dengan fungsi yang berbeda (Race Calendar vs Kalender Latihan). Route `/kalender-pelari` BELOM ADA dan AMAN untuk dibuat sebagai SEO landing + alias.

## Functional Requirements (Audit)
- **FR-1**: Audit menghasilkan mapping arsitektur framework, routing, auth, session (A).
- **FR-2**: Audit menghasilkan dokumentasi alur auth eksisting + Strava integration + data activity yang tersedia (B, C).
- **FR-3**: Audit menghasilkan inventaris model database relevan + relasinya (D).
- **FR-4**: Audit menghasilkan dokumentasi storage/image architecture + payment/order architecture (E, F).
- **FR-5**: Audit menghasilkan inventaris frontend design system + route conflict analysis (G, H).
- **FR-6**: Audit menghasilkan 10 rekomendasi: arsitektur KP, entity baru, API boundaries, trial persistence, image lifecycle, security, performance, implementation sequence, assumptions (I-Q).

## Non-Functional Requirements
- **NFR-1**: Audit coverage: minimal 10 model data, 5 service layer, 3 payment gateway, 2 layout frontend.
- **NFR-2**: Semua claim dalam audit harus merujuk ke path file + line number yang eksak di codebase.
- **NFR-3**: Rekomendasi arsitektur (I) harus menyebutkan minimal 3 komponen reusable eksisting yang dipakai.
- **NFR-4**: Assumptions yang tidak bisa diverifikasi dari codebase harus diberi label UNKNOWN — NEEDS CONFIRMATION.

## Constraints
- **Technical**:
  - Framework wajib: Laravel 13, Blade + Alpine.js + Vue (hybrid), Tailwind v4, Vite 7.
  - Auth wajib reuse: Session guard web + CheckRole middleware + PersonalAccessToken API.
  - Strava wajib reuse: StravaApiService + User.strava_* columns + StravaActivity model.
  - Payment wajib reuse: MidtransService + MootaService + QrisDynamicService + Wallet ecosystem.
  - Storage wajib reuse: ImageUploadService (Intervention Image 3, WebP 3-variant) + Storage::disk('public').
- **Business**:
  - Route `/calendar` (publik Race + Strava Dashboard) TIDAK BOLEH diubah/diganti fungsi.
  - Route `/runner/calendar` (Kalender Latihan Pribadi Runner) TIDAK BOLEH dihapus.
  - Authentication sistem RuangLari yang ada TIDAK BOLEH diduplikasi.
  - OAuth Strava flow TIDAK BOLEH dibuat lagi (sudah ada 3 flow, cari yang paling tepat untuk reuse).
- **Dependencies**:
  - Semua data running (Activity, PB, VDOT, Training Paces) diambil via User model accessor atau StravaActivity model - TIDAK BOLEH hit Strava API langsung dari KalenderPelari tanpa melalui StravaApiService.

## Assumptions
- KalenderPelari editor awalnya akan berbasis Blade + Alpine.js + Vue (CDN global) mengikuti pola existing `calendar/index.blade.php`, BUKAN full Inertia SFC (kecuali Run Connect).
- Physical print fulfillment akan menggunakan pipeline export PDF server-side (tidak screenshot browser).
- Anonymous trial state awalnya disimpan di localStorage/session Laravel (bukan IndexedDB) untuk kemudahan handoff ke auth.

## Acceptance Criteria

### AC-1: Audit A-H (Current State Mapping) Lengkap & Ter-reference
- **Type**: `rule`
- **Given**: Semua 8 dimensi (Framework/Routing/Auth/Session + AuthFlow + StravaActivity/Model + DB Models + Storage/Payment + Frontend/Routes) telah di-audit.
- **When**: Setiap claim dalam audit dicek keberadaan file path-nya.
- **Then**: Minimal 95% claim dalam A-H memiliki reference path file eksplisit (contoh: `routes/web.php L432` atau `app/Models/User.php L68-71`).
- **Pass Condition**: Tidak ada dimensi A-H yang hilang, dan minimal 3 path reference per dimensi.
- **Evidence**: File `.trae/specs/kalender-pelari-phase0-audit/AUDIT_REPORT.md` bagian A-H.

### AC-2: Rekomendasi I-Q (Target Architecture) Konsisten dengan Eksisting
- **Type**: `rubric`
- **Dimension**: Arsitektur rekomendasi selaras dengan infrastruktur RuangLari existing
- **Scale**: 1-5
- **Anchors**:
  - 1 = 80% rekomendasi buat komponen baru dari nol tanpa reuse
  - 3 = 50% reuse komponen eksisting, 50% komponen baru
  - 5 = >=80% komponen KalenderPelari memanfaatkan service/model/controller existing yang sudah terbukti bekerja (StravaApiService, ImageUploadService, Midtrans wrapper, Wallet, CheckRole middleware, Event model relationships, User.vdot accessor)
- **Pass Threshold**: >= 4
- **Evidence**: Daftar explicit mapping: `Komponen KP -> Komponen RuangLari yang direuse + Path file` dalam AUDIT_REPORT.md bagian I.

### AC-3: Entity Proposal (J) Tidak Duplikasi Model Existing
- **Type**: `rule`
- **Given**: Daftar 25 model entity Master Spec poin 77.
- **When**: Setiap entity dibandingkan dengan model di `app/Models/` berdasarkan nama + fields + relasi.
- **Then**: Jika ada model eksisting yang ekuivalen (contoh: User, Event, StravaActivity, Order, Transaction, Program, ProgramEnrollment, Race, RaceResult, RaceCategory, RaceDistance, City, Province, PacerBooking, Coupon, Wallet, WalletTransaction), entity tersebut TIDAK dimasukkan ke migration baru.
- **Pass Condition**: Hanya entity yang BENAR-BARU (CalendarProject, CalendarPage, CalendarElement, UploadedAsset/TrackAssetLifecycle, Template, TemplateElement, ProjectSnapshot, PrintSpecification, CreatorProfile, TemplatePurchase, RoyaltyTransaction, Goal, Habit, Shoe, PaceProfile) yang masuk daftar migrations.
- **Evidence**: Tabel perbandingan `Entity MS Spec -> Status (Reuse Existing / Buat Baru / Extend Existing)` dalam AUDIT_REPORT.md bagian J.

### AC-4: Conflict Detection (H) & Open Questions (Q) Jelas
- **Type**: `rule`
- **Given**: Route `/calendar` dan `/runner/calendar` sudah ada.
- **When**: Diperiksa potensi tabrakan URL, nama controller, nama view folder, nama route prefix.
- **Then**: Risiko konseptual (user bingung beda 3 kalender) harus didokumentasikan dengan usulan mitigasi routing alias. Assumptions yang butuh konfirmasi business (subscription pricing tiers, template marketplace fee %, physical print paper options, admin tools scope) harus didaftar eksplisit.
- **Pass Condition**: Daftar minimal 3 open questions bisnis + minimal 2 mitigasi konflik konseptual route.
- **Evidence**: AUDIT_REPORT.md bagian H (Konflik) dan Q (Open Questions).

## Open Questions
- [ ] Apakah `/kalender-pelari` akan menjadi SEO landing page baru yang di-link ke `/runner/calendar` (alias redirect), ATAU menjadi entry point ke editor visual builder BARU yang terpisah dari runner calendar legacy? (Implikasi: jika redirect, tidak perlu UI baru landing; jika builder baru, perlu landing + wizard terpisah)
- [ ] Pricing entitlement: Apakah fitur KalenderPelari editor + save project termasuk GRATIS untuk semua user terdaftar, atau butuh membership tier tertentu? (Lihat User.package_tier field)
- [ ] Untuk physical print: apakah paper options, cover type, spiral binding sudah tersedia konfigurasi di admin (seperti Event)? Atau perlu dibuat tabel `print_products` dan `print_options` baru?
- [ ] Creator marketplace royalty percentage: Berapa default royalty % untuk creator template? Apakah masuk ke Wallet payout mechanism yang sudah ada, atau perlu flow terpisah?
- [ ] Apakah Trial Anonymous yang memasukkan foto lokal perlu di-simpan temporari ke server storage TEMP_UPLOAD (expire 24h) atau cukup di browser (Blob/ObjectURL) saja sampai user login?
