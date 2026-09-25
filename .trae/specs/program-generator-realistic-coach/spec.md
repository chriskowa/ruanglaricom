# Spec Perbaikan Generator Program Lari (Realistic Coach Engine)

## Ringkasan Masalah
Generator saat ini membiarkan atlet **10K target 37 menit** memasukkan `weekly_mileage` 20km atau 30km — nilai yang fisiologis **tidak memadai** untuk mencapai target pace rata-rata 3:42/km (VDOT ≥ 53.5). Tidak ada validasi "target vs beban" yang cerdas, saran mileage di UI masih generik, dan tidak ada feedback realisme yang jelas sebelum program di-generate.

Masalah utama teridentifikasi (audit code):
1. Validasi backend `weekly_mileage min:5` (SelfGeneratedProgramController L179) tanpa memandang target jarak + waktu.
2. Input form step-3 `weekly_mileage min="15"` (generator_v2.blade L820) juga generik.
3. `recommendMileage()` dan `idealMileage` (JS) belum ada rumusan yang terikat pada target spesifik (hanya statis/tidak berjalan).
4. Logic realism (SelfGeneratedProgramController L201-208) hanya membatasi peningkatan VDOT 6/8/10% **tanpa menyentuh kelayakan mileage**.
5. Tidak ada validasi minimum durasi pekan vs target jarak (misal 5K butuh minimal 8 pekan; Marathon tidak mungkin 8 pekan untuk atlet intermediate).
6. Tidak ada "warning block" di step wizard yang menjelaskan secara edukatif *mengapa* beban saat ini tidak memadai dan *berapa* nilai minimal yang direkomendasikan.

Yang SUDAH benar & tidak boleh dirusak:
- `ProgramBuilderService::buildMileageSchedule()` sudah menerapkan aturan 10% peningkatan mingguan + deload kelipatan 4 + taper per jarak.
- `DanielsRunningService::calculateVDOT()` dan pace zones sudah akurat sesuai Jack Daniels.
- Struktur session harian, phase Base→Strength→Speed→Taper, quality session ordering, strength training days, run-walk beginner, tropical adjustment — SEMUA sudah tepat dan harus dipertahankan.

## Tujuan (Goals)
- G1: Cegah atlet memasukkan beban latihan (weekly_mileage) yang **tidak memadai** untuk target waktu spesifiknya (khusus: 10K target 37 menit → tidak boleh < batas minimum fisiologis).
- G2: Generator berperilaku seperti pelatih profesional yang **memberikan saran realistis**: target pencapaian, total mileage mingguan, progres peningkatan waktu yang terukur, dan aturan 10% peningkatan untuk mencegah cedera (yang sudah ada dipertahankan + dilengkapi guardrail di input).
- G3: Validasi input berlapis memastikan saran sesuai tingkat kebugaran awal, target waktu, dan timeline persiapan.
- G4: Pengujian skenario multi-atlet (minimal 5 skenario) membuktikan semua saran logis dan dapat dijalankan.

## Non-Goals (Tidak Dikerjakan)
- Tidak mengubah struktur periodisasi `ProgramBuilderService::buildPeriodizedProgram()`.
- Tidak mengubah algoritma VDOT / pace zones di `DanielsRunningService`.
- Tidak menambah dependency eksternal baru.
- Tidak redesign UI generator v2 secara visual (hanya menambah warning block, ubah min/max input, perbaiki logic saran JS).
- Tidak menyentuh GenerateProgramController legacy (jika ada); fokus di `generator_v2.blade` + `SelfGeneratedProgramController` + `ProgramBuilderService`.
- Tidak mengubah metode `ProgramAdaptationService::generateFeedback` / `applyAdaptation` (rule project memory).

## Functional Requirements

### FR1 — Matriks Kelayakan Mileage Minimum per Target
Buat engine rekomendasi / validasi `CoachFeasibilityEngine` (class di Service / trait inline jika sederhana) yang untuk setiap kombinasi:
```
(jarak_target, target_waktu_finish, runner_level, durasi_pekan)
```
menghasilkan:
- `min_peak_mileage_km`: batas minimum mileage puncak mingguan (hard floor) yang **harus** diinput user; jika di bawah ini → tolak generate dengan alasan yang jelas.
- `ideal_peak_mileage_km`: nilai saran "Sweet spot" pelatih profesional yang di-apply saat klik `recommendMileage`.
- `max_safe_peak_mileage_km`: batas atas aman (misal pemula 10K tidak boleh > 55km/minggu tanpa base; advanced bisa 70+).
- `min_weeks` / `max_weeks`: durasi persiapan yang diizinkan.
- `aggressiveness_score`: nilai 0.0 (konservatif) – 1.0 (ambisius tinggi) + label "Realistis / Agresif / Sangat Agresif / Tidak Realistis".

### FR2 — Aturan Khusus 10K Sub-37 Menit (User Priority)
Untuk `target_distance = 10k, goal_time ≤ 00:37:00`:
- `min_peak_mileage_km` = 42 km (advanced) / 45 km (intermediate) — 20km / 30km **TIDAK DIIZINKAN** dan harus memunculkan error validasi.
- `ideal_peak_mileage_km` = 48–52 km (advanced) / 50–55 km (intermediate).
- `frequency minimum` = 5 hari/minggu (4 hari hanya diizinkan jika advanced + 52km peak).
- `min_weeks` = 10 pekan (8 pekan hanya untuk advanced yang sudah punya base VDOT ≥ 51).
- Jika user input 20 atau 30 km → toast + inline block merah menjelaskan: *"Untuk target 10K sub 37 menit (3:42/km rata-rata), stimulus aerobik + ambang laktat tidak dapat tercapai dengan beban < 42km/minggu. Rekomendasi kami: 48-52 km peak dengan 5-6 sesi/minggu selama 12 pekan."*

### FR3 — Matriks Umum per Target Jarak × Level
Buat tabel lookup fisiologis berdasarkan Jack Daniels 4th ed + Pfitzinger Douglas mileages:
- **5K**: beginner 15–25 km, intermediate 22–40, advanced 30–55
- **10K**: beginner 18–30, intermediate 28–50, advanced 42–70 (khusus sub-37 masuk bucket advanced floor 42)
- **21K**: beginner 22–40, intermediate 35–60, advanced 50–85
- **42K**: beginner 30–55, intermediate 50–85, advanced 70–110

Catatan: semua mileage ini harus **disesuaikan lagi dengan durasi pekan tersedia** (misal target 42K dalam 8 pekan = tidak realistis untuk intermediate, min_weeks 16 untuk FM intermediate).

### FR4 — Validasi Berlapis (3 Lapisan)
1. **Lapisan Frontend JS (generator_v2 step-3)**: Saat user blur / ubah input `weekly_mileage` / `goal_time` / `target_distance` / `runner_level`, tampilkan warning block inline (bukan hanya disable button) menjelaskan masalah dan menawarkan tombol "Terapkan Saran Pelatih" untuk auto-set nilai ideal.
2. **Lapisan Backend Controller Validasi (SelfGeneratedProgramController)**: Sebelum melewati `$request->validate()`, jalankan validator kustom `CoachFeasibilityValidator` yang mengembalikan error 422 dengan structured payload: `{ feasibility: 'INFEASIBLE', min_required: 42, ideal: 50, reason: '...' }` dengan message yang jelas untuk ditampilkan UI toast.
3. **Lapisan Service Guard (ProgramBuilderService)**: Di awal `build()`, jika `weekly_mileage < minMileageForTarget(...)`, log warning dan raise `InfeasibleTrainingLoadException` (atau auto-normalize dengan jelas di response `adjustments_applied: [{from:30, to:48, reason:"..."}]`), agar tidak ada silent-fail menghasilkan program berbahaya.

### FR5 — Fungsi recommendMileage() Profesional di UI
Ganti logic `recommendMileage` (saat ini belum terdefinisi dengan benar / statis) menjadi engine yang memanggil matriks FR1 / FR3:
```
idealMileage = hitung_ideal(target_distance, goal_time, runner_level, weeks_count, injury_history)
```
- Auto-set `form.weekly_mileage` ke nilai ideal, dengan transisi highlight hijau yang menjelaskan "Saran diterapkan: berdasarkan PB 5K 27:30 (VDOT 36.5) + target 10K 37:00 (VDOT 53.8 ~ +47% tidak ok, kita clamp ke VDOT 42 ~ +15% realistis → perlu 12 pekan dengan peak 42-48 km)".
- Ada parameter `aggressiveness` (Conservative / Standard / Sharp / Advanced-Agresif) — default Standard, jika Sharp → naikkan 10% mileage, jika Conservative → turunkan 5% dan naikkan min_weeks.

### FR6 — Realism Score Card di Step-2 & Step-4 Review
Tambah komponen "Skor Kelayakan Target" yang ditampilkan setelah user isi goal_time:
- Label warna & skor (0–100): Hijau (70–100 = Realistis), Kuning (40–69 = Agresif), Oranye (20–39 = Risiko Tinggi), Merah (< 20 = Tidak Realistis).
- Penjelasan naratif seperti pelatih: *"Target 10K sub-37 dari PB 5K 27:30 membutuhkan peningkatan ~+47% VDOT, yang secara fisiologis membutuhkan 24+ pekan base + specialization bulding block. Opsi kami: (a) Naikkan target finish menjadi 42 menit (Realistis, 12 pekan, 35 km peak). (b) Perpanjang timeline menjadi 24 pekan (Realistis, 48 km peak). (c) Tetap 37 menit & 12 pekan (Tidak Realistis, risiko cedera > 60%)."*
- Opsi (a/b/c) adalah chip yang clickable untuk auto-set parameter.

### FR7 — Validasi Peningkatan VDOT Maksimum per Pekan
Perluas aturan existing (L202-208):
- Beginner: maks +0.4 VDOT / pekan (total +6% selama 12 pekan)
- Intermediate: maks +0.5 VDOT / pekan (total +8%)
- Advanced: maks +0.6 VDOT / pekan (total +10%)
- Hitung `deltaVDOT = targetVDOT - currentVDOT`, lalu `weeks_needed_min = ceil(deltaVDOT / ratePerWeek)`. Jika `weeks_until_race < weeks_needed_min` → masuk realism score down + opsi perpanjang / naikkan goal.

### FR8 — Aturan 10% Peningkatan Mileage (Pertahankan & Audit)
Yang SUDAH ADA di `buildMileageSchedule()` L572-576 harus dipertahankan. Tambahkan unit test / scenario test untuk memastikan:
- Untuk 12 pekan build dari 25km → 50km: tidak ada single week yang kenaikan > 10% dari week sebelumnya.
- Deload minggu ke-4, 8, 12 dipotong 20% dengan benar.
- Taper factors per jarak dijalankan (10K = taper 1 minggu 50%, Marathon taper 3 minggu).

### FR9 — Zero Regression UI & UX
- Semua alur existing: login, guest generate, store-pending after-login, save ke kalender runner calendar, AI deskripsi enhancement — TETAP BERJALAN.
- `generate()` method signature dan response JSON keys tetap backward-compatible (tambah field BARU `feasibility`, `adjustments_appended` tanpa menghapus yang lama).
- `ProgramAdaptationService` tidak diubah signature method (rule project memory).

## Non-Functional Requirements
- **NFR1 Performance**: Validasi feasibility per request < 5ms (tabel lookup + aritmatika sederhana; TIDAK ada query DB, TIDAK ada panggilan AI).
- **NFR2 Error Handling**: Seluruh guardrail feasibility selalu mengembalikan JSON HTTP 200 dengan `success: true` + `feasibility_warnings` array jika warning saja; HTTP 422 + `success: false` + structured error jika HARD infeasible (tidak boleh generate). Ikuti pattern Adaptive Run Intelligence di runner calendar: endpoint selalu JSON agar UI tidak hancur.
- **NFR3 Backward Compatible**: Semua endpoint `POST /api/programs/generate` existing tanpa parameter aggressiveness → default ke `standard`, tetap jalan tanpa error.
- **NFR4 Zero Emoji di text warning & coaching advice** (aturan anti-slop UI project memory: hindari emoji, gunakan SVG native atau badge warna + label teks).
- **NFR5 Kontras teks**: Warning teks putih minimal slate-200 pada background merah/tinggi (tidak boleh tulisan slate-400 di atas merah gelap).

## Constraints, Dependencies, Assumptions
- **Constraint HARD**: 10K sub-37 menit TIDAK BOLEH lolos validasi dengan weekly_mileage < 42 km (user explicit requirement).
- **Constraint**: Tidak boleh mengubah method signature `ProgramBuilderService::build(array $config): array`, hanya TAMBAHKAN pre-validation guard di dalam method awal.
- **Dependency**: `DanielsRunningService::calculateVDOT()` sebagai sumber nilai VDOT target & awal.
- **Assumption**: Runner level "advanced" didefinisikan sebagai atlet yang sudah konsisten latih 12+ bulan / punya race history formal (tidak self-declare tanpa guard). Validasi VDOT yang terlalu tinggi untuk beginner otomatis turunkan level ke intermediate.
- **Assumption**: `injury_history != none` → otomatis kurangi aggressiveness 1 level (Sharp → Standard, Standard → Conservative) dan naikkan min_weeks 2 minggu untuk proteksi.
- **Dependency runtime**: Local CLI PHP 7.4, tapi code harus sesuai PHP 8.3 production (typed property boleh, null-safe operator). Verifikasi via code inspection, bukan `php artisan`.

## Acceptance Criteria (Hanya rule / rubric)

### AC-1 | RULE: Validasi Minimum Mileage Khusus 10K Target ≤ 00:37:00
**Kondisi Pass**: Jika user submit generate program dengan `target_distance=10k, goal_time ≤ 00:37:00, weekly_mileage < 42 km` → dikembalikan HTTP 422 structured error dengan `min_required_peak_mileage >= 42` dan pesan menyatakan 20km/30km tidak memadai. Program TIDAK dibuat.
**Evidence**: Run scenario test S1 (lihat Tasks.md) + grep response JSON.

### AC-2 | RULE: Rekomendasi Mileage Ideal per Kombinasi Target × Level
**Kondisi Pass**: Fungsi `recommendMileage()` mengembalikan nilai ideal dalam range matriks FR3 ± 5km untuk setiap 5 target × 3 level × 3 aggressiveness.
**Evidence**: Manual trace 5 scenario test (S1-S5) di Tasks.md.

### AC-3 | RULE: Aturan 10% Peningkatan Mileage & Deload
**Kondisi Pass**: Untuk SEMUA scenario S1–S5, setidaknya 10 dari 12 pekan build memiliki delta kenaikan terhadap week sebelumnya ≤ 10% (dibulatkan 0.5km tolerance). Minggu 4 / 8 / 12 memiliki mileage dikurangi ~20% (deload).
**Evidence**: Compute `mileageSchedule[]` dari `ProgramBuilderService::buildMileageSchedule` dan audit delta.

### AC-4 | RULE: Realism Score Menolak Target Tidak Realistis
**Kondisi Pass**: Jika `requiredWeeks = ceil(deltaVDOT / ratePerWeek) > weeksUntilRace × 1.5` → realism score berwarna MERAH ("Tidak Realistis") dan tombol generate **tidak disabled permanent**, tetapi menampilkan modal confirm "Anda memilih target yang menurut analisis kami tidak realistis. Pahami risiko cedera > 60%, tetap lanjut?" dengan 2 opsi "Terapkan Saran" (auto-set feasible) atau "Lanjut dengan Risiko" (boleh generate, tapi simpan flag `is_aggressive_user_override=true` di output).
**Evidence**: UX flow manual + UI grep.

### AC-5 | RULE: Validasi Berlapis 3 Lapisan (Frontend + Controller + Service Guard)
**Kondisi Pass**: Untuk setiap skenario infeasible:
1. Warning block inline muncul di JS sebelum click generate (Lapisan 1).
2. Request yang dipaksakan POST ke controller → HTTP 422 structured JSON (Lapisan 2).
3. Bahkan jika dilewatkan (tamper payload) ke `ProgramBuilderService::build()` → flag `adjustments_applied[]` di return atau InfeasibleException tertangkap & log error (Lapisan 3).
**Evidence**: 3 manual test / curl payload.

### AC-6 | RULE: Zero Regression Endpoint Lama & Save ke Kalender
**Kondisi Pass**:
- Postman / curl ke `/api/programs/generate` dengan payload existing (tanpa aggressiveness / tanpa realism) → `success: true` dan 100% field lama ADA di response.
- `/api/programs/save` masih insert `Program` + `ProgramEnrollment` dengan benar (tidak ada field baru required).
**Evidence**: Inspection code diff — hanya menambah optional field, tidak ganti required.

### AC-7 | RUBRIC: Kualitas Naratif Saran Seperti Pelatih Profesional
**Scale 0-2**:
- 0: Saran hanya berupa angka tanpa penjelasan (misal "Min 42 km").
- 1: Ada penjelasan tapi generik / AI-slop (terlalu banyak kata, tidak ada data fisiologis).
- 2: Narasi mengandung (a) mengapa butuh mileage itu, (b) peningkatan VDOT berapa %, (c) berapa pekan ideal, (d) sebut risiko jika kurang, (e) sebut opsi alternatif jika atlet tidak sanggup (naikkan target waktu / perpanjang timeline).
**Pass Threshold**: Skor ≥ 2, diverifikasi dari 5 scenario message content.
**Evidence**: Capture inline warning block di step-3.

### AC-8 | RUBRIC: Edukasi Atlet (Bukan Hanya Larangan)
**Scale 0-1**:
- 0: Jika infeasible hanya tampilkan "Tidak bisa generate, batalkan".
- 1: Setiap infeasible / agresif menawarkan auto-apply 3 opsi chip (ubah target / ubah timeline / apply saran mileage) dengan 1-click.
**Pass Threshold**: Skor = 1 untuk minimal 3 dari 5 skenario infeasible.
**Evidence**: Step-2 realism card + step-3 mileage warning block.

### AC-9 | RULE: Skenario Multi-Atlet Logis & Dapat Dijalankan (Unit Scenario Test)
**Kondisi Pass**: Semua 5 skenario test utama di Tasks.md lulus: TIDAK ada yang menghasilkan long run > 40% dari weekly mileage (cidera), tidak ada beginner yang weekly mileage jump > 10%, dan semua nilai finish target berada pada range VDOT yang seharusnya.
**Evidence**: Tabel hasil skenario di Tasks.md "Completion Evidence".

### AC-10 | RULE: Zero Dependency Baru & Kode Minimal
**Kondisi Pass**: `composer.json` & `package.json` TIDAK berubah. Logic baru dimasukkan ke class Service yang sudah ada atau 1 class baru `CoachFeasibilityService.php` saja (TIDAK > 3 file new).
**Evidence**: Git diff (manual inspection).
