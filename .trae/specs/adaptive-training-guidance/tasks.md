# RuangLari — Adaptive Training Guidance
## Implementation Plan

Urutan pengerjaan: **1 (Backend Service Core) → 2 & 3 (Route + Phase Helper) → 4 (UI Popup Blade) → 5 + 6 (Toggle + CTA wiring) → 7 (Self Audit Zero Regression)**.

---

## Task 1: Extend ProgramAdaptationService — method `getCurrentTrainingStatus()` on-demand
- **Status**: `pending`
- **Priority**: high
- **Depends On**: "None"
- **Description**:
  - Tambahkan **public method baru** di [ProgramAdaptationService](file:///c:/laragon/www/ruanglari/app/Services/ProgramAdaptationService.php) bernama `getCurrentTrainingStatus(User $user): array`.
  - Perbedaannya dengan `generateFeedback()` existing: **TIDAK membutuhkan $newVdot explicit** (karena popup muncul tanpa harus update PB). Kalau enrollment punya `current_vdot` → pakai itu sebagai baseline; kalau tidak, fallback ke `program->vdot_score`.
  - Kalkulasi tambahan (parameter yang belum ada di generateFeedback, dibutuhkan untuk Recovery Alert):
    1. **Training streak**: hitung jumlah completed sessions berturut-turut (ProgramSessionTracking status=completed tanggal berurutan tanpa rest) — break jika ada hari tanpa completed atau session type=rest.
    2. **RPE avg 5 hari terakhir**: dari `ProgramSessionTracking.rpe` yang tidak null → cast int, avg, jika < 3 data → null (insufficient data).
    3. **Feeling trend 3 hari terakhir**: map enum strong=5, good=4, average=3, weak=2, terrible=1 → jika turun 2 point (misal good → weak) → flag `feeling_declining=true`.
    4. **Pace consistency / trend**: dari `StravaActivity` (type=Run) atau `UserActivity` (sport_type=Run) 14 hari terakhir → hitung avg_pace_sec moving avg 3 hari terakhir vs 30 hari. Jika drop > 7% (pace lebih lambat 7%) → flag `pace_decline=true`.
    5. **Recovery Alert aggregate boolean**: `(streak >= 10) OR (rpe_avg_5d >= 6.5) OR (feeling_declining AND pace_decline)`.
  - Return struktur JSON output konsisten dengan generateFeedback TAMBAH field:
    ```
    training_phase: {key, label, accent, focus, progress_pct, week_current, week_total, target_race}
    readiness_3tier: {status: 'ready'|'maintain'|'reduce', accent: 'emerald'|'amber'|'rose', title, message, suggested_volume_pct_diff}
    intensity_decision: {add_quality: bool, badge_icon, accent, title, message, structure_example?}
    recovery_alert: {is_alert: bool, accent, title, message, parameters_breached: []}
    why_section: {lines: string[]} (array 2-3 kalimat narasi Indonesia dengan angka konkret)
    can_adapt_program: bool
    ```
  - **JANGAN ubah apapun** di method `generateFeedback()`, `applyAdaptation()`, dan semua private method yang ada. Semua tetap 100% identik untuk zero regression. Private helper baru dipisah dengan prefix `current*` agar jelas.
- **Acceptance Criteria Addressed**: AC-3 (Volume), AC-4 (Intensity), AC-5 (Recovery), AC-8 (Zero regression).
- **Test Requirements**:
  - `rule` TR-1.1: Diberikan enrollment aktif dengan current_vdot 40 dan 5 completed sessions (rpe 4 avg, strong 3hr last). Ketika panggil `getCurrentTrainingStatus()`. Output `readiness_3tier.status === 'ready'`, `readiness_3tier.accent === 'emerald'`. Evidence: unit test assertion on output fields.
  - `rule` TR-1.2: Diberikan 12 completed sessions berturut-turut tanpa rest (hari berurutan). Output `recovery_alert.is_alert === true` + parameters_breached mengandung "training_streak". Evidence: test streak counter.
  - `rule` TR-1.3: Signature method `applyAdaptation(ProgramEnrollment, float, array)` tidak berubah sama sekali; array sessions completed di index 0-N sebelum apply dan sesudah apply identik hash-nya. Evidence: md5 hash compare completed chunk.
  - `rubric` TR-1.4: Kelengkapan field output untuk UI. Scale 1-5. 1 = cuma 1/5 field utama ada. 3 = 3/5 field utama ada tapi why_section tanpa angka konkret. 5 = 5/5 field lengkap, why_section punya setidaknya 2 angka. Threshold >= 4. Evidence: var_dump struktur output sample 3 kondisi.
- **Notes**: Rule 10% volume cap (safe overload) sudah ada di private `calculateOptimalMileage()` — REUSE TANPA UBAH. Tidak recalculate ulang.

---

## Task 2: Public Training Phase Helper — extract dari private CalendarController ke trait/service terpisah
- **Status**: `pending`
- **Priority**: high
- **Depends On**: "None"
- **Description**:
  - Logic `getTrainingPhase(int $day, int $totalWeeks)` SEKARANG private di [CalendarController L624-L637](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Runner/CalendarController.php#L624-L637). Extract jadi **public static** di trait baru `App\Traits\TrainingPhaseAware` atau di static method `ProgramAdaptationService::calculatePhaseKey()`.
  - **Tambahkan mapping label + accent warna ke output array** (bukan cuma return string 'foundation'):
    ```php
    // key => [label_idn, accent_color, focus_desc_idn]
    'foundation'    => ['Build',          'sky',     'Tambah volume & aerobic base'],
    'early_quality' => ['Development',    'emerald', 'Tambah intensity & threshold'],
    'quality'       => ['Peak',           'orange',  'Race specific training'],
    'final_prep'    => ['Recovery/Taper', 'violet',  'Adaptasi & pengurangan beban'],
    ```
  - Tetap kompatibel backward: CalendarController private `getTrainingPhase` lama masih bisa ada, tapi delegasi ke method baru (atau dihapus private lalu use trait). Yang penting existing call di events() untuk FullCalendar `extendedProps.phase` TIDAK BERUBAH string aslinya (foundation/early_quality/quality/final_prep) — kalau berubah, event color di calendar berubah. Hanya UI popup yang pakai label baru "Build / Development / Peak / Recovery".
- **Acceptance Criteria Addressed**: AC-2 (Phase mapping + warna).
- **Test Requirements**:
  - `rule` TR-2.1: 4 input day untuk 12 minggu (totalDays=84): day 10 → foundation; day 30 → early_quality; day 55 → quality; day 80 → final_prep. Return `key` asli TIDAK BERUBAH (backward compatible). Evidence: 4 assertion unit test.
  - `rule` TR-2.2: Accent untuk key=foundation adalah 'sky' (tidak slate/abu). Evidence: assert mapping array `accent === 'sky'`.
  - `rule` TR-2.3: CalendarController events() `extendedProps.phase` untuk 4 day sample masih string original sama, tidak berubah. Evidence: dump JSON extendedProps compare sebelum/sesudah extract trait.
- **Notes**: Fokus zero-regression untuk existing calendar rendering.

---

## Task 3: Tambah Endpoint `GET /runner/calendar/training-status` (auth) dan expose service di CalendarController
- **Status**: `pending`
- **Priority**: high
- **Depends On**: "Task 1, Task 2"
- **Description**:
  - Di [routes/web.php group runner auth](file:///c:/laragon/www/ruanglari/routes/web.php#L1202) (dekat L1228 `weekly-volume`), tambah route baru:
    ```php
    Route::get('/calendar/training-status', [CalendarController::class, 'trainingStatus'])->name('calendar.training-status');
    ```
  - Di Runner `CalendarController`, tambah method **public** `trainingStatus(Request $request)`:
    1. Validasi user login (sudah auth middleware).
    2. Panggil **ProgramAdaptationService Task 1 method** `getCurrentTrainingStatus(auth()->user())`.
    3. Jika return null (tidak ada enrollment aktif) → return response JSON `{'insufficient': true, 'message': 'Tidak ada program aktif'}` HTTP 200 (bukan 404, supaya frontend graceful).
    4. Kalau ada → return JSON struktur lengkap dari service.
  - Tambahkan `__construct` dependency injection `ProgramAdaptationService $adaptation` di CalendarController (jika belum inject; cek existing constructor).
- **Acceptance Criteria Addressed**: AC-1 (trigger popup data benar), NFR-1 (<400ms).
- **Test Requirements**:
  - `rule` TR-3.1: Route `GET /calendar/training-status` middleware auth required. Guest → redirect / login. Evidence: curl test tanpa login → 302 redirect.
  - `rule` TR-3.2: User dengan enrollment aktif → response JSON ada field `training_phase`, `readiness_3tier`, `recovery_alert`. Evidence: JSON keys assertion.
  - `rule` TR-3.3: User tanpa program aktif → response `insufficient: true` HTTP 200, tidak ada error 500. Evidence: response status 200.
- **Notes**: Nama method bebas, tapi pastikan tidak bentrok dengan method existing di CalendarController (index/events/... sudah ada, trainingStatus unik).

---

## Task 4: UI Popup Blade Component "Adaptive Training Status" — inject ke calendar view
- **Status**: `pending`
- **Priority**: high
- **Depends On**: "Task 3 (endpoint ready untuk fetch)"
- **Description**:
  - View target: tentukan mana yang dipakai — [runner/calendar.blade.php](file:///c:/laragon/www/ruanglari/resources/views/runner/calendar.blade.php) atau [calendar_modern.blade.php](file:///c:/laragon/www/ruanglari/resources/views/runner/calendar_modern.blade.php). (Cek dulu `view('runner.calendar')` call dari Dashboard; inject di BOTH jika tidak yakin, tapi idealnya 1 saja.)
  - Struktur HTML popup (modal dengan backdrop blur) — SESUAIKAN DENGAN KONSEP USER:
    ```
    [Header Section]
    Icon + Tagline "Adaptive Training Guidance"
    Close button (X) top-right
    ---------------------------------------------------------
    [LEFT PILL CARD: TRAINING PHASE (FR-2)]
    Phase Badge Box: warna sesuai accent (sky/emerald/orange/violet).
    Isi: "🟢 BUILD PHASE" ukuran besar; subtitle: "Minggu ke-X dari total Y · Target 10K · 20 Sept 2026"; focus area 1 line.
    Progress bar persentase durasi (% di bawah badge).

    [RIGHT: 3 TILES VERTICAL (FR3, FR4, FR5)]
    Tile 1: Volume Readiness
      Icon 📈 / ⚠️ / 🛑 + Badge warna 3-tier + title; deskripsi 1 line; CTA inline "Terapkan +8%" jika ready.
    Tile 2: Intensity Decision
      Icon ⚡ / 🧘 + title; struktur contoh main set jika add_quality=true.
    Tile 3: Recovery Alert
      Icon 🛌 jika alert ON (rose) atau ✅ (emerald off); parameter mana yang dilanggar (list bullet).

    [FULL WIDTH BELOW: WHY SECTION (FR-6)]
    Heading "Mengapa rekomendasi ini?"
    2-3 paragraph dengan angka konkret (RPE 4.1, pace drop 9s/km, streak 11 hari).

    [FOOTER CTA BAR (FR-7)]
    KIRI: Disclaimer text-xs: "Rekomendasi adaptif berbasis data · Diskusikan coach untuk perubahan besar."
    KANAN: 2 tombol → (Secondary) Nanti dulu / (Primary) Terapkan ke Jadwal
    ```
  - Trigger fetch: Saat DOMContentLoaded (jika halaman adalah tab=calendar): fetch JSON ke `route('calendar.training-status')` — jika response **BUKAN `insufficient:true`** → open modal otomatis. Jika insufficient → tidak muncul popup (AC-1).
  - Styling: **100% dark tema RuangLari**: backdrop `bg-slate-900/80 backdrop-blur`, card `bg-slate-900/95 border border-slate-800 rounded-2xl shadow-2xl`. Text: body `text-slate-200`, title pure `text-white`, accent colors = phase accent sky/emerald/orange/violet sesuai Task 2 mapping. **Konsisten dengan pattern redesign review.blade accent color system**: text-[accent-300], border-[accent-400]/20, bg-[accent-400]/10 untuk icon rings/badges.
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3, AC-4, AC-5, AC-6, NFR-2 dark theme, NFR-4 fallback.
- **Test Requirements**:
  - `rule` TR-4.1: Popup DOM `<div role=dialog aria-modal=true>` ada di halaman calendar ketika enrollment aktif. User tidak punya program aktif → div itu tidak dirender / tetap hidden. Evidence: browser snapshot DOM.
  - `rule` TR-4.2: 4 scenario phase → warna badge sesuai mapping (Build=sky, Dev=emerald, Peak=orange, Rec=violet). Evidence: screenshot / computed CSS class check 4x.
  - `rule` TR-4.3: Popup close via (a) klik tombol X, (b) tombol ESC keyboard, (c) klik backdrop blur di luar card. Semua 3 cara berhasil tutup. Evidence: test interact 3 cara via browser tools / manual test log.
  - `rubric` TR-4.4: Kualitas visual & readability dark tema. Scale 1-5. 1 = teks masih slate-400 (kontras rendah) + heading slate generik abu. 3 = warna benar tapi layout rata kiri semua tidak ada hierarki visual. 5 = warna benar per section accent, hierarki jelas, teks body minimal slate-200, heading putih/tebal. Threshold >= 4. Evidence: screenshot full popup.
  - `rubric` TR-4.5: Kualitas isi Why section (AC-6). Scale 1-5 anchor spec AC-6. Threshold >= 4. Evidence: inspeksi teks Why baris per baris.
- **Notes**: Gunakan icon FontAwesome (sudah ada di RuangLari, lihat review.blade guna `fas fa-*`) — tidak install icon baru. Contoh icons: Build=🏗️ / Development=🚀 / Peak=🏔️ / Recovery=🧘 (atau pilih FA yang ada secara aman tanpa unicode emoji: `fa-layer-group`, `fa-chart-line`, `fa-mountain-sun`, `fa-mug-hot`).

---

## Task 5: Floating Mini-Badge "Training Status" (toggle ketika popup ditutup)
- **Status**: `pending`
- **Priority**: medium
- **Depends On**: "Task 4"
- **Description**:
  - Ketika popup ditutup via ESC / tombol tutup, popup hilang. Tapi tambah **floating persistent badge** di pojok kanan atas (dekat tombol bulan FullCalendar prev/next) dengan:
    - Mini phase badge color (sesuai training_phase.accent circle kecil)
    - Label "Status Latihan" atau icon kompas
    - Jika recovery_alert.is_alert = true → badge berwarna rose + icon tanda seru (notifikasi)
  - Ketika klik badge → reopen popup (fetch ulang data training-status untuk memastikan data fresh, tidak pakai cache lama)
  - Badge position: absolute top-right di container calendar, z-index 40 (tidak overlap modal popup z=50). Responsive mobile: geser ke bottom-right jika layar kecil.
- **Acceptance Criteria Addressed**: AC-1 (bisa dibuka ulang tanpa reload).
- **Test Requirements**:
  - `rule` TR-5.1: Close popup → badge muncul di DOM. Klik badge → popup muncul lagi (fetch network call terlihat). Evidence: DOM + network snapshot.
  - `rule` TR-5.2: Jika recovery alert ON, badge punya class `border-rose-400/50` (rose highlight). Jika alert OFF → border default (slate/neon). Evidence: class assertion.
- **Notes**: Default sesuai spec Open Question 3: persistent selalu ada. Jika user kemudian jawab berbeda, mudah dimodif.

---

## Task 6: Wire CTA "Terapkan ke Jadwal" ke route apply-program-adaptation existing + refresh calendar
- **Status**: `pending`
- **Priority**: medium
- **Depends On**: "Task 4, Task 1 (karena butuh enrollment_id dari status output)"
- **Description**:
  - Tombol "Terapkan ke Jadwal" HANYA AKTIF ketika output `can_adapt_program = true`. Jika false → disabled + tooltip gray "Belum ada perubahan yang bisa diterapkan (tidak ada sisa sesi / VDOT stabil)".
  - Ketika klik tombol:
    1. Show loading state di tombol (spinner, text "Menerapkan...").
    2. POST ke `route('calendar.apply-program-adaptation')` [sudah ada web.php L1215](file:///c:/laragon/www/ruanglari/routes/web.php#L1215) — gunakan CSRF token meta tag csrf-token (standard Laravel). Payload: `{enrollment_id: status.enrollment_id, new_vdot: status.new_vdot, adapt_volume: true}` (sesuai expected controller method existing; cek dulu signature apply adaptation controller, jika butuh fields tambah dari `generateFeedback` output, tinggal lewatkan).
    3. Jika response success: **tutup popup** → tampilkan toast sukses (gunakan toast library yang sudah ada / notification component RuangLari yang ada di layout) → **panggil FullCalendar refetchEvents()** agar events yang sudah diadaptasi (pace baru, distance baru) muncul di grid tanpa page reload.
    4. Jika error (422 / 500): tetap di popup, tampilkan inline error di footer CTA, tidak tutup.
- **Acceptance Criteria Addressed**: AC-7.
- **Test Requirements**:
  - `rule` TR-6.1: can_adapt=false → tombol disabled atribute ada, tidak bisa diklik. Evidence: inspect disabled property.
  - `rule` TR-6.2: can_adapt=true, klik → network POST request ke `calendar.apply-program-adaptation` URL benar dengan enrollment_id. Success → FullCalendar event list memuat ulang (ada 1 call extra ke `/calendar/events` route di network). Evidence: network tab 3 request sequence: POST adapt → 200 OK → GET events (refresh).
  - `rule` TR-6.3: Jika response error → popup tidak tertutup otomatis. Evidence: DOM dialog tetap ada setelah error response.
- **Notes**: Jangan buat endpoint POST baru; REUSE route L1215 existing. Jika controller method existing butuh parameter beda, cek dulu isinya — kalau perlu wrapper kecil di method baru Task 3 CalendarController, tapi idealnya signature cocok karena service yang sama (ProgramAdaptationService::applyAdaptation).

---

## Task 7: Final Audit — Zero Regression Checklist + Dark Theme Compliance
- **Status**: `pending`
- **Priority**: low
- **Depends On**: "Task 1 s/d 6 (semua)"
- **Description**:
  1. **Code audit ProgramAdaptationService**: diff signature lama vs baru — pastikan tidak ada parameter tambah / return type berubah di method generateFeedback & applyAdaptation.
  2. **Grep audit completed sessions**: Cari semua assignment `$sessions[Y]` dengan `$day <= $daysPassed` atau completedDays — pastikan Task 1 dan Task 6 tidak pernah modif chunk completed.
  3. **Grep audit calendar colors**: Pastikan FullCalendar `getEventColors()` dan phase value `extendedProps.phase` string original tidak berubah untuk existing user (backward compat).
  4. **Grep audit contrast popup**: Jalankan `ripgrep text-slate-400` di file popup blade — pastikan **0 match** di area konten body (boleh hanya di decorative icon prefix, seperti review.blade rules). Body text minimal `slate-200`; title `white`; placeholder keterangan opsional `slate-400`.
  5. **Cek fallback insufficient data**: Simulasikan kondisi runner baru 1 completed session (RPE null, pace log 0) — cek popup menampilkan badge "Insufficient Data" untuk sections yang kosong, tidak ada tampilan broken / null pointer error.
- **Acceptance Criteria Addressed**: AC-8 (zero regression), NFR-3, NFR-4.
- **Test Requirements**:
  - `rule` TR-7.1: `git diff app/Services/ProgramAdaptationService.php` — 0 perubahan pada line signature method `generateFeedback` dan `applyAdaptation` (line number function declaration & closing unchanged, hanya tambahan method baru sesudah). Evidence: git diff output.
  - `rule` TR-7.2: Runner 1 session condition → popup TIDAK ADA error 500 / warning undefined array key. Evidence: HTTP response 200 + HTML render tidak ada error notice.
  - `rubric` TR-7.3: Tingkat kepatuhan dark theme & accent system review.blade. Scale 1-2. 2 = 100% ikut pola accent ring (bg/accent/10, border/20, text/30), tidak ada color hex hardcode random selain brand --neon dan slate. 1 = ada warna acak. Threshold >= 2. Evidence: grep hasil color classes di popup blade.
- **Notes**: Simpan hasil audit sebagai completion evidence.
