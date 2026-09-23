# RuangLari — Adaptive Run Intelligence (Popup Calendar)
## Product Requirements Document

## Overview
- **Summary**: Popup interaktif bernama **Adaptive Run Intelligence** yang muncul saat runner membuka halaman Calendar, menampilkan 4-pilar analisis adaptif: Volume Readiness, Intensity Decision, Recovery Alert, dan Training Phase Awareness — beserta rekomendasi "kenapa" dan action yang bisa dilakukan runner.
- **Purpose**: Memecahkan masalah utama runner (dan nilai jual RuangLari): bukan "program apa minggu ini?" tapi **"kenapa saya harus tambah/kurang, dan apakah tubuh saya siap?"** — jawaban otomatis berbasis data aktivitas, RPE/feeling, perubahan VDOT/PB, dan fase latihan.
- **Target Users**: Runner RuangLari yang memiliki **enrollment program aktif** (terdaftar di program coach / self-generated aktif). Runner tanpa program tidak menampilkan popup (tidak relevan, tidak ada baseline).

## Goals
1. Runner melihat **status training personal (phase + readiness + rekomendasi)** secara sekaligus ketika buka calendar, tanpa harus scroll menu atau cek statistik manual.
2. 4 pilar adaptif user request **100% ter-cover**: Volume Ready (tambah jarak?), Intensity (tambah speed?), Recovery alert (istirahat dulu?), Training Phase (sedang fase apa?).
3. Rekomendasi **bisa di-action-kan**: tombol "Terapkan ke jadwal" memanggil `ProgramAdaptationService::applyAdaptation()` yang sudah ada.
4. Tidak mengganti peran coach — tetap ada disclaimer "Rekomendasi Adaptive Run Intelligence AI, konsultasikan coach Anda sebelum perubahan drastis".

## Non-Goals
1. **TIDAK** membuat program training baru dari nol (sudah di-cover ProgramBuilder).
2. **TIDAK** mengubah completed sessions / history runner (hanya future sessions, sesuai `applyAdaptation` existing policy).
3. **TIDAK** implementasi bio sensor hardware baru (HR strap / watch connect) di fase ini — tetap pakai data yang sudah ada: VDOT, completed sessions, RPE/feeling, Strava data average speed/distance, kolom feeling dari tracking.
4. **TIDAK** mengirim notifikasi push / email otomatis di fase ini — hanya visual popup ketika user buka calendar.
5. **TIDAK** mengganti logic scoring `ProgramAdaptationService` / `DanielsRunningService` yang sudah battle tested (jaga zero regression).

## Background & Context
Eksplorasi codebase menemukan **90% logic scoring adaptif SUDAH ADA dan battle-tested** di RuangLari — yang missing hanya UI popup + endpoint on-demand status:
- [ProgramAdaptationService](file:///c:/laragon/www/ruanglari/app/Services/ProgramAdaptationService.php): Lengkap punya `generateFeedback()` ([L26-L159](file:///c:/laragon/www/ruanglari/app/Services/ProgramAdaptationService.php#L26-L159)) = output 4 pillars (pacing/volume/quality/strength/long_run/readiness). Method `evaluateReadiness()` ([L366-L400](file:///c:/laragon/www/ruanglari/app/Services/ProgramAdaptationService.php#L366-L400)) = badge `emerald/amber/blue` (exactly 🟢🟡🔴 user konsep). `calculateOptimalMileage()` ([L335-L361](file:///c:/laragon/www/ruanglari/app/Services/ProgramAdaptationService.php#L335-L361)) = 10% safe overload rule Volume Readiness. `generateQualityRecommendations()` ([L405-L438](file:///c:/laragon/www/ruanglari/app/Services/ProgramAdaptationService.php#L405-L438)) = Intensity Decision (tambah tempo/interval? + contoh struktur workout).
- [CalendarController::getTrainingPhase()](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Runner/CalendarController.php#L624-L637): Sudah ada 4 fase persentase (25% / 50% / 75%) = foundation/early_quality/quality/final_prep — tinggal mapping nama ke konsep user: **Build / Development / Peak / Recovery**.
- [ProgramSessionTracking](file:///c:/laragon/www/ruanglari/app/Models/ProgramSessionTracking.php#L22-L24): Fillable sudah punya `rpe` (1-10) + `feeling` (enum strong/good/average/weak/terrible) — parameter Recovery Alert user request sudah dikumpulkan setiap selesai sesi.
- Route sudah ada: `calendar.apply-program-adaptation` ([web.php L1215](file:///c:/laragon/www/ruanglari/routes/web.php#L1215)) untuk action "Terapkan rekomendasi ke jadwal".
- Halaman Calendar runner: `runner.dashboard tab=calendar` redirect dari [CalendarController@index](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Runner/CalendarController.php#L22-L25). View: `runner/calendar.blade.php` + `calendar_modern.blade.php`.

## Functional Requirements
### FR-1: Trigger Popup on Calendar Load
Popup hanya muncul **jika user punya enrollment program aktif** (`ProgramEnrollment status=active + program.is_active=true`) ketika:
- User navigate ke route `runner.calendar` (atau `runner.dashboard?tab=calendar`) untuk pertama kali dalam sesi.
- User klik tombol CTA "Training Status" floating/sidebar di halaman calendar (popup bisa dibuka ulang setelah ditutup).
- Popup TIDAK muncul jika enrollment tidak ada, atau program tidak aktif.

### FR-2: Training Phase Awareness Card (Pojok kiri atas popup)
Tampilkan:
- Badge Phase dengan warna unik per fase + nama fase:
  - 🔵 **BUILD PHASE** (foundation: 0-25% duration) → accent sky/neon.
  - 🟢 **DEVELOPMENT PHASE** (early_quality: 25-50%) → accent emerald.
  - 🟠 **PEAK PHASE** (quality: 50-75%) → accent orange.
  - 🟣 **RECOVERY / TAPER PHASE** (final_prep: 75-100%) → accent violet.
- Line deskripsi 1 kalimat: `[Nama fase] · Minggu ke-X dari total Y minggu · Target race: [distance_target | "-"]`
- Catatan kecil focus area fase (Build = "Tambah volume & aerobic base"; Development = "Tambah intensity threshold"; Peak = "Race specific training"; Recovery = "Adaptasi & taper").

### FR-3: Volume Readiness Widget (Fitur 1 user)
Input evaluation (dari `ProgramAdaptationService::pillars.volume` + `readiness`):
- Weekly mileage (aktual 7 hari terakhir vs rekomendasi)
- Long run progression banding 2 minggu terakhir
- Pace consistency (dari `UserActivity.avg_pace_sec` atau `StravaActivity.average_speed` std-dev 3 easy run terakhir)
- Recovery score aggregate (`rpe` avg 3 hari terakhir + `feeling` enum value mapping)

Output visual badge 3 warna (sesuai user):
- 🟢 **Ready to increase volume**: `emerald` + teks "Tubuh adaptasi dengan baik. Pertimbangkan kenaikan mileage mingguan 5–10%." + button CTA "Terapkan Volume +8%"
- 🟡 **Maintain current volume**: `amber` + teks "Fitness membaik, tapi sinyal recovery menyarankan pertahankan volume dulu minggu ini."
- 🔴 **Reduce load**: `rose/red` + teks "Kelelahan tinggi terdeteksi. Recovery week disarankan (kurangi volume 20-30%)."

### FR-4: Intensity Decision Engine Widget (Fitur 2 user)
Decision rule dari `generateQualityRecommendations` + VDOT diff + aggregate pace HR trend:
- Jika easy pace membaik + HR stabil + feeling = strong → badge ⚡ **Add quality session** (neon/volt) + contoh rekomendasi spesifik: "Aerobik foundation membaik. Minggu ini cocok tambah Tempo Training." + contoh struktur main set.
- Jika pace stagnan + feeling weak/average + RPE > 7 → badge **Tahan dulu speed work** (slate/amber) +: "Speed work bisa berlebihan. Fokus easy mileage dulu untuk bangun aerobik base."

### FR-5: Recovery Alert (Fitur 3 user)
Parameter (semua sudah bisa dihitung dari existing schema):
- Training streak (hari berturut-turut status completed tanpa rest)
- Trend feeling turun (good → average → weak dalam 3 hari)
- RPE avg > 6.5 dalam 5 hari terakhir
- Pace drop 7%+ dari 30 hari avg (StravaActivity / UserActivity)

Output:
- Jika parameter accumulate → badge 🛌 **Recovery Week Rekomendasi** (rose) + teks: "14 hari terakhir menunjukkan akumulasi kelelahan. Pertimbangkan minggu easy — mileage -25%, 0 quality sessions."
- Selalu sampaikan disclaimer: "Rekomendasi berbasis data RPE dan pace log Anda. Jika ada cedera, hubungi coach atau ahli fisioterapi."

### FR-6: Why Section (Narasi Penjelasan)
Tepat di bawah rekomendasi utama, **WAJIB ADA** penjelasan "Mengapa ini rekomendasinya?" — 1-3 kalimat naratif Indonesia mengutip bukti data konkret:
  - Contoh untuk Ready +8%: *"Pace easy run Anda membaik 4 detik/km minggu ini, sementara RPE rata-rata tetap 4/10 (stabil). 5 hari completed tanpa kelelahan berlebih → tubuh siap overload 8% sesuai aturan aman 10%."*
  - Contoh untuk Reduce Load: *"RPE rata-rata Anda 7.2/10 (naik 2 poin) + pace easy drop 9 detik/km + training streak 11 hari tanpa rest day penuh → tubuh butuh taper minggu ini."*
- Dilarang hanya tampilkan badge tanpa penjelasan why.

### FR-7: CTA "Apply Rekomendasi" + Cancel
Popup memiliki 2 action utama:
- **(Primary) Terapkan ke jadwal** — hanya aktif jika `can_adapt_program=true` dari generateFeedback (remaining sessions > 0 + vdot diff signifikan). OnClick: POST ke route `calendar.apply-program-adaptation` existing (L1215). Setelah sukses: toast sukses + tutup popup + refresh events calendar.
- **(Secondary) Nanti dulu / Tutup** — close popup, tampilkan floating mini-badge "Training Status" di sudut kanan atas calendar agar bisa dibuka kembali tanpa reload.
- **(Tersier) Saran / Tanya Coach** (opsional low priority): link untuk kirim pesan ke coach terkait rekomendasi ini (jika program punya coach_id).

## Non-Functional Requirements
- **NFR-1 (Speed)**: Endpoint on-demand untuk kalkulasi status popup **respons < 400ms** di local, karena 90% logic sudah ada di `ProgramAdaptationService::generateFeedback()` — hanya baca tambahan RPE/feeling/Strava trend 2 minggu terakhir.
- **NFR-2 (Dark Theme)**: 100% ikuti tema RuangLari dark (`bg-slate-900/80 backdrop-blur`, `--color-neon #ccff00` aksen, text `slate-100/200`). Tidak ada latar putih menyilaukan. Konsisten dengan style [review.blade.php](file:///c:/laragon/www/ruanglari/resources/views/admin/running-analysis/trials/review.blade.php) accent system baru (warna unik per section).
- **NFR-3 (Zero Regression)**: Tidak ubah output `ProgramAdaptationService::applyAdaptation()` dan tidak ubah signature method apapun. Semua call ke method existing tetap identik. Jangan ubah completed sessions.
- **NFR-4 (Fallback Graceful)**: Jika data tidak cukup (misal runner baru 1 sesi, belum ada RPE log) → tetap tampilkan popup tapi isi bagian yang tidak punya data dengan badge "Insufficient Data (butuh 3+ sesi)" dalam slate, TIDAK crash / error HTTP 500.
- **NFR-5 (Accessibility)**: Popup focus trap, bisa di-close tombol ESC, role=dialog, aria-labelledby judul popup.

## Constraints
### Technical
- Wajib pakai **Stack Tailwind v4 + Blade component yang sudah ada** (tidak install library UI baru). Konsisten dengan style codebase RuangLari sekarang.
- PHP tetap 8.3 (project requirement), tidak ada downgrade logic buat PHP 7.4 CLI mismatch local (verifikasi code inspection, bukan runtime).
- DB schema: tambah kolom BOLEH hanya jika benar-benar perlu (sleep quality, resting HR) → tapi 4 pilar utama bisa jalan tanpa schema change dengan data yang sudah ada (RPE/feeling/Strava). Opsional sleep/rest HR masuk fase 2 (out of scope MVP).
- JS inline di blade atau file public js yang sudah ada (tidak build ulang Vite jika tidak perlu) — mengikuti pattern FullCalendar existing di runner/calendar.blade.

### Business
- **Bukan menggantikan coach**: Selalu ada disclaimer kecil di footer popup "Rekomendasi adaptif berbasis data. Untuk perubahan besar / cedera, diskusikan dengan coach Anda."
- Fitur MVP hanya untuk runner **yang punya enrollment program aktif**. Runner tanpa program tidak lihat popup (tidak ada baseline adaptasi).
- Bahasa default UI: Indonesia (sesuai seluruh copy RuangLari), istilah teknis Inggris (Tempo/Interval/RPE/VO2Max) tetap pakai Inggris sesuai terbiasa runner.

### Dependencies
- Sudah ada di codebase (tidak install baru): `ProgramAdaptationService`, `DanielsRunningService`, `CalendarController`, `ProgramSessionTracking`, `ProgramEnrollment`, `StravaActivity`, `UserActivity`.

## Assumptions
1. Runner yang membuka calendar sudah login (sudah dipastikan middleware `auth` di route group L1202 routes web.php).
2. `ProgramAdaptationService::generateFeedback()` saat ini hanya dipanggil ketika runner `updatePb` (set PB mingguan). Untuk popup on-demand: kita akan bikin method service baru **mirror** logic `generateFeedback` tapi TANPA membutuhkan parameter `$newVdot` (jika VDOT tidak berubah, pakai `$oldVdot` = current enrollment vdot, dan hitung readiness dari training log saja).
3. Jika runner `updatePb` → popup otomatis juga terbuka dengan data feedback terbaru (opsional, nice to have).
4. Color scheme badge 🟢🟡🔴: mapping ke emerald/amber/rose Tailwind 300 text + 400/10 ring (sesuai pattern redesign halaman review trial sebelumnya).

## Acceptance Criteria

### AC-1: Popup muncul hanya untuk enrollment program aktif
- **Type**: `rule`
- **Given**: Runner login punya `ProgramEnrollment` status=active + program.is_active=true
- **When**: Runner navigate ke halaman calendar (runner.dashboard tab=calendar)
- **Then**: Popup Adaptive Training muncul otomatis (modal) dengan 4 section utama (Phase Card, Volume, Intensity, Recovery)
- **Pass Condition**: Enrollment aktif → ada popup DOM dengan `role=dialog`; enrollment tidak ada → tidak ada popup DOM.
- **Evidence**: Blade conditional render check, browser snapshot DOM, unit test enclosure "no active program = no insight script injected".

### AC-2: Training Phase ditampilkan sesuai persentase durasi dengan warna benar
- **Type**: `rule`
- **Given**: Program duration_weeks = 12 (totalDays = 84). Session day ke 10 = 11.9%, session day ke 30 = 35.7%, session day ke 55 = 65.5%, session day ke 80 = 95.2%.
- **When**: Kalkulasi phase dari enrollment (hari ini / totalDays)
- **Then**: Fase dan warna (Build=sky, Development=emerald, Peak=orange, Recovery=violet) sesuai persentase mapping.
- **Pass Condition**: 4 test case percentage -> phase -> color class semuanya match.
- **Evidence**: Unit test wrapper untuk method phase mapping (extract `CalendarController::getTrainingPhase` ke service public jika perlu).

### AC-3: Volume Readiness output 3-tier color badge + CTA apply ketika Ready
- **Type**: `rule`
- **Given**: Enrollment aktif + current VDOT 40, can_adapt_program = remaining session > 0
- **When**: VDOT naik signifikan + feeling strong + RPE avg rendah
- **Then**: Volume badge = 🟢 emerald + teks "Ready to increase volume +8%" + tombol primary "Terapkan ke Jadwal" ada & bisa diklik.
- **Pass Condition**: DOM element `.volume-readiness-badge.emerald` + `<button data-action=apply-adaptation>` ada ketika can_adapt=true.
- **Evidence**: Browser test 3 scenario readiness (good/moderate/bad) + snapshot UI badge 3 warna.

### AC-4: Intensity Decision punya dua cabang benar sesuai aerobik base
- **Type**: `rule`
- **Given**: Ada log pace 10 easy run terakhir.
- **When**: Scenario A: pace membaik 3%+ + RPE stabil. Scenario B: pace stagnan + RPE naik 2 poin.
- **Then**: Scenario A ⚡ Add quality session badge; Scenario B badge "Tahan speed work dulu".
- **Pass Condition**: Dua skenario UI berbeda (icon + accent color beda).
- **Evidence**: Test case engine output expected flag `add_quality_session` bool benar di kedua skenario.

### AC-5: Recovery Alert aktif saat akumulasi fatigue parameter terpenuhi
- **Type**: `rule`
- **Given**: Training streak 12 hari berturut-turut completed (tidak ada rest).
- **When**: Cek parameter recovery.
- **Then**: Section Recovery menampilkan badge 🛌 rose "Recovery Week Rekomendasi".
- **Pass Condition**: Badge rose muncul saat streak > 10 OR RPE avg > 6.5 5 hari terakhir OR pace drop > 7%.
- **Evidence**: 3 test conditional OR masing-masing return true -> flag alert.

### AC-6: Why section wajib berisi data konkret (bukan kalimat generik)
- **Type**: `rubric`
- **Dimension**: Kualitas narasi penjelasan / justifikasi rekomendasi.
- **Scale**: 1-5
- **Anchors**:
  1 = Tidak ada Why section sama sekali / hanya "AI menyarankan".
  3 = Ada Why tapi tanpa angka (generik "recovery bagus").
  5 = Why section mengutip setidaknya 2 angka data konkret (RPE avg, delta pace, kenaikan %, jumlah hari, dll) dan bahasa natural Indonesia mudah dipahami runner awam.
- **Pass Threshold**: >= 4
- **Evidence**: Screenshot / HTML snapshot isi `.why-section` dari 3 kondisi berbeda (Ready/Maintain/Reduce).

### AC-7: CTA Terapkan ke jadwal memanggil route apply-program-adaptation existing & refresh calendar
- **Type**: `rule`
- **Given**: can_adapt_program = true
- **When**: Klik tombol "Terapkan ke Jadwal"
- **Then**: Request POST ke `route('calendar.apply-program-adaptation')` dengan enrollment_id + new_vdot sesuai rekomendasi; response ok -> toast sukses + FullCalendar refetch events tanpa page reload.
- **Pass Condition**: Network tab menunjukkan POST ke route benar dan events FullCalendar berubah setelah sukses.
- **Evidence**: Browser DevTools network capture + DOM snapshot events baru sesudah apply.

### AC-8: Zero regression ProgramAdaptationService — tidak ada perubahan signature dan apply tetap hanya ubah future sessions
- **Type**: `rule`
- **Given**: 3 completed sessions (Day 1-7) + 3 remaining sessions (Day 8-21)
- **When**: Call applyAdaptation 10x dengan newVdot berbeda
- **Then**: Array session index completed 0-2 TIDAK PERNAH berubah byte-per-byte. Hanya index 3+ yang berubah distance/pace/description.
- **Pass Condition**: Hash md5 completed sessions sama persis sebelum dan sesudah apply.
- **Evidence**: Unit test compare session arrays pre/post applyAdaptation.

## Decisions Log (Tertutup, 2026-09-22)
Semua open question telah dijawab eksplisit oleh user:
- [x] **Nama Fitur (Branding)**: **Adaptive Run Intelligence** (dipilih dari 3 opsi: lebih teknikal, menekankan aspek "adaptif cerdas" dibanding kompetitor static plan).
- [x] **Input Sleep Quality & Resting HR**: **FASE 2 TIDAK MASUK MVP**. MVP launch cepat dengan data yang sudah ada (RPE, feeling enum, pace log dari StravaActivity/UserActivity) — akurasi 80% cukup untuk keputusan dasar. Fase 2 nanti tambah tabel `user_daily_checkins` + UI input pagi untuk akurasi +20%.
- [x] **Mini Floating Badge Setelah Popup Ditutup**: **SELALU TAMPIL (PERSISTENT)** di sudut kanan atas calendar. Reason: user tidak lupa cek status training, risk user tutup popup lalu lupa ada recovery alert = 0. Jika alert merah, badge highlight rose; jika hijau stabil, badge default accent phase.
