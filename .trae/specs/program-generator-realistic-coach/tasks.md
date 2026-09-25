# Tasks Implementasi — Generator Program Lari Realistic Coach Engine

> Parent spec: [spec.md](./spec.md)
> Map ke AC: Setiap task menandai AC yang dicakup. Semua AC/TR bertipe `rule` atau `rubric` sesuai Spec Mode Vocabulary.

## Task 1: Buat Service `CoachFeasibilityService` (Validasi Matriks Kelayakan)
- **Status**: pending
- **Priority**: high
- **Scope**: 1 file baru `app/Services/CoachFeasibilityService.php`
- **Coverage AC**: AC-1, AC-2, AC-7, AC-10
- **Description**:
  1. Class dengan method public:
     ```php
     public function assess(array $input): array
     ```
     Input array shape:
     ```php
     [
       'target_distance' => '5k|10k|21k|42k',
       'goal_time_sec' => int,          // hasil parse HH:MM:SS
       'runner_level' => 'beginner|intermediate|advanced',
       'weekly_mileage' => float,
       'frequency' => int,              // 3..7
       'weeks' => int,
       'initial_vdot' => float,
       'target_vdot' => float,
       'injury_history' => string,
       'aggressiveness' => 'conservative|standard|sharp',
     ]
     ```
  2. Return array shape (TR-1.1 rule: WAJIB memiliki semua key dibawah):
     ```php
     [
       'feasibility' => 'FEASIBLE|AGGRESSIVE|HIGH_RISK|INFEASIBLE',
       'score' => 0..100,
       'min_required_peak_mileage' => float,
       'ideal_peak_mileage' => float,
       'max_safe_peak_mileage' => float,
       'min_weeks' => int,
       'max_weeks' => int,
       'min_frequency' => int,
       'color' => 'emerald|amber|orange|red',
       'label' => 'Realistis|Agresif|Risiko Tinggi|Tidak Realistis',
       'reason' => string, // narasi pelatih profesional (AC-7 rubric)
       'options' => [      // chip opsi (AC-8 rubric)
         ['id'=>'adjust_goal', 'label'=>'Ubah target finish menjadi 00:42:00', 'apply'=>[...overrides]],
         ...
       ],
       'vdot_rate_per_week' => float, // untuk FR7
       'required_weeks_by_vdot' => int,
       'violations' => string[],      // list aturan yang dilanggar (bisa >1)
     ]
     ```
  3. Method private `loadMileageMatrix()`: Tabel FR3 (5K/10K/HM/FM × beginner/intermediate/advanced) untuk min/ideal/max mileage (aturan hardcoded bukan AI).
  4. Khusus 10K + `goal_pace_sec_per_km <= (37*60)/10 = 222 detik/km` → override `min_required_peak_mileage` menjadi 42 jika intermediate, 40 jika advanced, 48 jika intermediate+sharp, 52 jika advanced+standard (FR2).
  5. `injury_history != none` → auto clamp aggressiveness turun 1 tingkat + `min_weeks += 2`.
  6. FR7 Validasi VDOT rate:
     - beginner max +0.4 VDOT/minggu
     - intermediate max +0.5
     - advanced max +0.6
     - Hitung `required_weeks = ceil(abs(targetVdot-initialVdot) / rate)`. Jika `weeks < required_weeks` → turunkan feasibility dan tambah violation.
- **Test Requirements (TR)**:
  - **TR-1.1 (rule)**: Output method `assess()` selalu mengandung 13 key di atas (array_key_exists).
  - **TR-1.2 (rule)**: Skenario 10K + goal_time=00:37:00 + weekly_mileage=20 → feasibility=INFEASIBLE & min_required_peak_mileage>=42 & frequency_minimum >= 5 (AC-1).
  - **TR-1.3 (rule)**: Skenario 5K beginner 8 minggu → ideal_peak_mileage berada di range 18..25 (±2 km tolerance) (AC-2).
  - **TR-1.4 (rubric)**: Kualitas `reason`. Scale 0-2: (0) angka tanpa penjelasan, (1) penjelasan generik, (2) penjelasan yang sebut: VDOT delta %, butuh pekan berapa, risiko jika kurang, opsi alternatif. Pass = 2.
  - **TR-1.5 (rule)**: `options[]` array minimal 2 chip jika feasibility ∈ {AGGRESSIVE, HIGH_RISK, INFEASIBLE}.
- **Depends on**: (none)

## Task 2: Integrasi Validasi Backend 2 Lapisan (Controller + Service Guard)
- **Status**: pending
- **Priority**: high
- **Scope Files**:
  - `SelfGeneratedProgramController::generate()` L151-351
  - `ProgramBuilderService::build()` awal method sebelum `buildPeriodizedProgram`
- **Coverage AC**: AC-1, AC-4, AC-5, AC-6
- **Description**:
  1. **Lapisan 2 Controller**: Sebelum `$validated = $request->validate(...)` di L157, jalankan:
     ```php
     $assess = $this->coachFeasibilityService->assess([
         // parse goal_time ke detik, parse pb_time->initial_vdot via DanielsRunningService
     ]);
     if ($assess['feasibility'] === 'INFEASIBLE' && !$request->boolean('force_infeasible_ack')) {
         return response()->json([
             'success' => false,
             'message' => $assess['reason'],
             'feasibility' => $assess,
         ], 422);
     }
     ```
     Jangan lupa inject `CoachFeasibilityService` di constructor.
  2. **Lapisan 3 Service Guard**: Di `ProgramBuilderService::build()` sebelum `buildPeriodizedProgram`, jalankan assess lagi (defense-in-depth). Jika INFEASIBLE, auto-normalize `weekly_mileage` ke `$assess['min_required_peak_mileage']` dan append ke return array key `adjustments_applied: [['field'=>'weekly_mileage','from'=>X,'to'=>Y,'reason'=>$assess['reason']]]`. JANGAN throw exception (zero regression UI).
  3. Pertahankan `maxVdotImprovementPercent` L202-208 di controller, tapi gabung dengan FR7 VDOT rate pekanan (lebih ketat yang menang).
  4. Validasi tambahan di controller rule:
     - `weekly_mileage >= min_required_peak_mileage (dari assess) jika INFEASIBLE (hanya untuk rule hard; AGGRESSIVE/HIGH_RISK dilewatkan dengan warning)`.
     - `frequency >= min_frequency (dari assess)` → misal 10K sub37 butuh 5 hari/minggu minimum.
     - Minimal durasi pekan: 5K ≥8, 10K ≥10, HM ≥12, FM ≥16 (sesuaikan FR3 matriks). Kalau kurang dari min, tambah violation.
- **Test Requirements (TR)**:
  - **TR-2.1 (rule)**: POST `/api/programs/generate` payload 10K+goal=00:37:00+mileage=20 → HTTP 422 + JSON berisi key `feasibility.min_required_peak_mileage`.
  - **TR-2.2 (rule)**: POST payload sama dengan `force_infeasible_ack=true` → response `success:true` + `adjustments_applied[]` ada di dalam result.summary.
  - **TR-2.3 (rule)**: POST payload LAMA (semua field default tanpa aggressiveness / tanpa force) → `success:true` & 100% field legacy response ADA (vdot, paces, hr_zones, bmi, sessions, summary) (AC-6 zero regression).
  - **TR-2.4 (rule)**: `weeks < min_weeks` untuk 42K intermediate (weeks=8) → feasibility INFEASIBLE atau HIGH_RISK (min 16 pekan).
  - **TR-2.5 (rule)**: Inject constructor TIDAK memecah existing service (MidtransService, OpenAiService, DanielsRunningService tetap ada).

## Task 3: Perbaiki Frontend Generator (Warning Inline + Recommend + Realism Score Card)
- **Status**: pending
- **Priority**: high
- **Scope File**: `resources/views/programs/generator_v2.blade.php`
  - Step wizard 2 section target (tambah realism score card setelah goal time)
  - Step wizard 3 section weekly_mileage (tambah warning block + change min attribute dynamic + recommendMileage logic)
  - Step wizard 4 review (tambah feasibility badge detail)
  - JS setup(): computed untuk `coachAssessment` dari local mirror matriks FR1/FR3 (copy subset PHP matrix ke JS object literal agar realtime tanpa network call).
- **Coverage AC**: AC-2, AC-4, AC-5, AC-7, AC-8
- **Description**:
  1. **Clone subset logic CoachFeasibilityService ke JS** (object `MILEAGE_MATRIX` di script, function `calculateCoachAssessment()`). Karena ini hanya untuk realtime preview (before submit), nilai tidak harus 100% sama persis, tapi harus selisih ≤ ±3 km dan feasibility label SAMA dengan backend (jika tidak → user percaya frontend, submit di tolak backend → bad UX).
  2. **Wizard Step 2 setelah goal time**: Tambah card compact `Skor Kelayakan Target` dengan warna badge, skor 0-100, narasi `reason`, 3 chip button opsi (a/b/c) onclick auto-set parameter ke formulir.
  3. **Wizard Step 3 section Mileage**:
     - Ubah input `weekly_mileage` `:min` binding dynamic ke `Math.floor(coachAssessment.min_required_peak_mileage - 1)`.
     - Di bawah input, tampilkan warning block compact dengan warna sesuai feasibility:
       - FEASIBLE (hijau): "Beban memadai untuk target Anda"
       - AGGRESSIVE (kuning): "Beban agresif — pastikan Anda sudah punya base mileage 6+ bulan"
       - HIGH_RISK (orange): "Berisiko tinggi cedera — saran kami: 48 km peak"
       - INFEASIBLE (merah tebal border 2px): "TIDAK MEMADAI — 10K sub 37 butuh ≥ 42 km peak. Alasan: [...]. Terapkan Saran?" + 1-click lime button.
     - `@click="recommendMileage"` → set `form.weekly_mileage = Math.round(coachAssessment.ideal_peak_mileage)` + highlight flash hijau.
  4. **UX Button Disabled Policy**: JANGAN disable button generate permanen (aturan antislop UI project memory). Jika infeasible, klik generate → tampilkan confirm modal "Target Anda menurut analisis tidak realistis (skor X/100). Risiko cedera > 60%. (A) Terapkan Saran Pelatih (B) Lanjutkan dengan Pemahaman Risiko". Opsi B kirim `force_infeasible_ack=true` di payload POST.
  5. **Step 4 Review**: Tambah row "Skor Kelayakan" dengan label + badge.
  6. **UX Helper text**: Semua text warning wajib warna `slate-200` di atas merah / orange bg / slate-900 agar kontras memadai (tidak `slate-400`) — aturan project memory.
- **Test Requirements (TR)**:
  - **TR-3.1 (rule)**: Ketika user pilih 10K, goal_time=37:00, mileage=20 di step 3 → warning block MERAH muncul dengan tombol "Terapkan Saran 48 km" dan min attribute input berubah otomatis (AC-1 frontend mirror).
  - **TR-3.2 (rule)**: Fungsi `recommendMileage()` di panggil → `form.weekly_mileage` set ke nilai ideal (AC-2) + highlight lime class ditambahkan selama 1.5 detik.
  - **TR-3.3 (rule)**: Realism card step 2 menampilkan minimal 3 chip opsi jika skor < 60 (contoh: "Ubah target finish ke 42:00", "Perpanjang timeline ke 24 pekan", "Naikkan mileage ke 48 km") (AC-8).
  - **TR-3.4 (rule)**: Tombol generate TIDAK pernah disabled; jika infeasible muncul modal confirm (bukan silent exit button tidak bisa klik) (AC-4 antislop disabled button rule).
  - **TR-3.5 (rubric)**: Kualitas teks warning + reason. Scale 0-2: (0) cuma angka, (1) generik, (2) sebut: VDOT delta peningkatan berapa %, butuh stimulus aerobic minimal berapa km, sebut risiko bila kurang, sebut 1 contoh opsi realistis. Pass threshold ≥ 2 (AC-7).

## Task 4: Pengujian Multi-Skenario Atlet (5 Skenario Utama)
- **Status**: pending
- **Priority**: high
- **Scope**: Bukti completion evidence berupa tabel di catatan task ini (tidak perlu file test formal — local runtime 7.4 tidak memungkinkan; code inspection trace + manual kalkulasi).
- **Coverage AC**: AC-3, AC-9
- **Skenario Test (wajib semua lulus)**:

| ID | Nama Skenario | PB | Target | Goal | Weeks | Level | Mileage | Expected feasibility | Expected ideal_peak | Expected 10% rule pass? | Notes |
|---|---|---|---|---|---|---|---|---|---|---|---|
| S1 | **10K Sub-37 (User Priority)** | 5K=27:30 (VDOT 36.5) | 10K | 00:37:00 | 12 | intermediate | 20 → auto naik ke 48 | INFEASIBLE awal → FEASIBLE setelah apply saran | 48-52 km | YA (trace buildMileageSchedule: start ~ 75% × 48 = 36, gradual 10%/week peak di w10 → 48) | User explicit scenario |
| S2 | 10K Sub-42 Realistis | 5K=22:15 (VDOT 44) | 10K | 00:42:00 | 10 | intermediate | 30 | FEASIBLE atau AGGRESSIVE | 35-40 km | YA | Skenario masuk akal |
| S3 | FM Pemula (42K Beginner) | 10K=1:02:00 (VDOT 30) | 42K | 05:30:00 | 8 | beginner | 40 | INFEASIBLE (min_weeks=16 dibutuhkan) | 42-48 km (tapi 8 minggu = tidak cukup) | TIDAK BERLAKU (weeks<min) | Uji validasi min_weeks |
| S4 | 5K Pemula | 10K=55:00 (VDOT 34) | 5K | 00:28:00 | 8 | beginner | 15 → saran 20 | FEASIBLE | 20-25 km | YA (20 peak → build dari 13 → gradual 10%) | Skenario realistic pemula |
| S5 | Half Marathon Advanced | HM=1:45 (VDOT 48) | HM | 01:28:00 | 14 | advanced | 50 → saran 60 | AGGRESSIVE | 60-68 km | YA | 14 pekan → sharp |

- **Cara Verifikasi (trace tanpa runtime)**:
  1. Manual kalkulasi VDOT awal dan target via `DanielsRunningService::calculateVDOT` source formula (L13-73).
  2. Hitung `deltaVDOT`, hitung `rate_per_week`, hitung `required_weeks`.
  3. Jalankan logika matriks mileage.
  4. Trace `ProgramBuilderService::buildMileageSchedule()` L535-583 untuk 12 pekan target 48km:
     ```
     begin w1: 48 * 0.75 = 36 → intermediate = 36
     peak w10: 48
     kemudian check setiap delta: |w2-w1| / w1 ≤ 10% ?
     ```
  5. Long Run distance calculation trace L597-645: Pastikan long run ≤ weekly_mileage * 0.40 + cap per jarak (10K max 16km ratio 0.35).
- **Test Requirements (TR)**:
  - **TR-4.1 (rule)**: S1: feasibility awal INFEASIBLE saat mileage=20 dan berubah FEASIBLE setelah di-adjust ke 48 (AC-1).
  - **TR-4.2 (rule)**: S3 (42K beginner 8 pekan): feasibility label ∉ FEASIBLE (harus AGGRESSIVE atau lebih buruk; sesuai min_weeks rule 16 pekan FM).
  - **TR-4.3 (rule)**: Skenario S1 + S4 + S5: Trace `buildMileageSchedule` → tidak ada single jump kenaikan mileage > 10.5% (0.5% tolerance rounding). Sebutkan nilai week-by-week di completion evidence (AC-3).
  - **TR-4.4 (rule)**: S1 Long Run untuk 10K target: minggu peak long run TIDAK melebihi `min(maxKm=16km, 48km * 0.35=16.8km)` → ≤ 16km (tidak ada 18km long run yang over-ratio). (AC-9)
  - **TR-4.5 (rubric)**: Tabel skenario diisi secara lengkap dengan nilai-nilai konkret (tidak ada "YA" tanpa angka). Scale 0-1. Pass = 1 (lengkap dengan angka trace).

## Task 5: Final Review Lintas Komponen (Verifikasi AC Coverage)
- **Status**: pending
- **Priority**: medium
- **Scope**: Review semua file: 1 service baru + 2 edit existing (controller, builder) + 1 view blade
- **Coverage AC**: Semua AC (AC1-AC10)
- **Description**:
  1. Cross-check setiap AC di spec.md → pastikan task mana yang meng-cover → pastikan TR yang mana yang jadi bukti.
  2. Audit **Lint / PHP-Stan style** (manual code inspection tanpa runtime):
     - Tanda kurung namespace, import use diurutkan, tidak ada `var_dump` / `dd` yang tertinggal.
     - Tidak ada emoji di string teks warning / reason (aturan antislop).
     - Kontras warna text `slate-200` minimal di blade view warning block.
  3. Route tidak diubah (hanya endpoint existing).
  4. Pastikan method `ProgramAdaptationService::generateFeedback` & `applyAdaptation` signature tidak berubah (project memory zero regression rule).
  5. Pastikan NFR2: controller generate SELALU kembalikan JSON (HTTP 500 TIDAK BOLEH; wrap dalam try/catch existing L347-350 dan tambah feasibility key jika catch exception).
- **Test Requirements (TR)**:
  - **TR-5.1 (rule)**: Setiap AC di spec.md memiliki mapping ≥1 task + ≥1 TR yang mengcovernya (tabel mapping).
  - **TR-5.2 (rule)**: Grep seluruh `/resources/views/programs/generator_v2.blade.php` untuk emoji regex → 0 match (aturan anti-slop).
  - **TR-5.3 (rule)**: Grep `font-mono` di section wizard step 2-4 → 0 match (jika ada font-mono → hapus, kecuali data numerik tabular pace).
  - **TR-5.4 (rule)**: Semua field required di input validation tetap ada (tidak dihapus aturan existing L157-L195). Nilai min/max weekly_mileage TIDAK DIHAPUS; hanya DITAMBAHKAN validator custom.

---

## Summary Coverage AC → Task

| AC | Tipe | Tasks yang Mengcover |
|---|---|---|
| AC-1 10K Sub37 Floor 42km | rule | T1 TR1.2, T2 TR2.1, T3 TR3.1, T4 TR4.1 |
| AC-2 Recommend Range Matrix | rule | T1 TR1.3, T3 TR3.2 |
| AC-3 Rule 10% & Deload | rule | T4 TR4.3 |
| AC-4 Realism Modal Confirm | rule | T3 TR3.4, T2 TR2.2 |
| AC-5 3 Layers Validation | rule | T2 TR2.1 TR2.4, T3 TR3.1, T1 TR1.2 |
| AC-6 Zero Regression Endpoint | rule | T2 TR2.3, T2 TR2.5 |
| AC-7 Narasi Kualitas Coach | rubric | T1 TR1.4, T3 TR3.5 |
| AC-8 Edukasi 3 Chip Opsi | rubric | T1 TR1.5, T3 TR3.3 |
| AC-9 5 Skenario Multi-Atlet Logis | rule | T4 TR4.1-TR4.5 |
| AC-10 Kode Minimal Tanpa Dep Baru | rule | T1 scope, T2 scope, T3 scope, T5 TR5.2/TR5.3 |
