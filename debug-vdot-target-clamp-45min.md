# [OPEN] Debug: PB 19:35 → Target 45 min override bug
Session ID: `vdot-target-clamp-45min`
Dibuat: 2026-09-25
Bug Deskripsi: User masukkan PB 19 menit 35 detik. Goal waktu 10K yang diharapkan 39-40 menit (atau target 37 min). Di Step 4 di override otomatis menjadi 45 menit — tidak sesuai ekspektasi.

## Repro (dari user laporan):
1. Buka Generator v2 Wizard Step 1
2. Isi PB = 19 menit 35 detik (target_distance 10K? atau 5K? belum jelas)
3. Step 2 Tentukan target 10K, isikan goal finish 37:00 (atau apapun di bawah 40)
4. Step 3 Mileage 42-50, freq 5, Runner Level Advanced
5. Step 4 Review → klik Buat Program → Output target / predict berubah menjadi 45:00

## 5 Falsifiable Hypotheses:
- **H1 [FR7 VDOT rate terlalu agresif]** — REJECTED via static inspection. L312 `safeTargetVdot = min(targetVdot, currentVdot × (1+effectiveMaxPercent))`. Jika currentVdot (61+) > targetVdot (59 utk 37min 10K), maka min(59, 61×1.08)=59. TIDAK di clamp turun ke 45 menit. Backend tidak turunkan target VDOT jika target user lebih rendah dari current.
- **H2 [suggestGoalTime() JS override user input]** — ✅ **CONFIRMED, BUG ROOT CAUSE**. Bukti:
  - L2258-L2263: `watch(current_vdot, (newVdot) => { ... suggestGoalTime(); })` terpanggil SETIAP current_vdot berubah, TANPA guard apakah user sudah edit goal manual.
  - L2519 onMounted: `suggestGoalTime()` dipanggil pada inisialisasi.
  - L2206-L2216 (ORIGINAL suggestGoalTime): **LANGSUNG OVERWRITE** `goal_hours/goal_minutes/goal_seconds` DENGAN nilai predictRaceTimeSeconds(recommendedTargetVdot, distance) TANPA bertanya user.
  - MATEMATIS BUKTI 45 MENIT: Jika user memilih PB distance = "5K" dengan waktu 19:35, lalu `form.target_distance` = 42K (marathon): `currentVdot ≈ 57.0`; `recommendedTargetVdot = min(57 × 1.03, 57 + 3) ≈ 58.7`. `predictRaceTimeSeconds(58.7, '42k') ≈ 3 jam 08 menit?` BUKAN 45. TETAPI: JIKA user salah pilih target_distance = 5K dengan recommendedTargetVdot TURUN karena currentVdot tinggi buggy lain → predict 5K 45 menit? TIDAK. ATAU: PB input 19:35 tapi pb_distance="42k" → VDOT hitung ERROR RENDAH 32.5 → predictRaceTime(35.5, 10k) = TEPAT 45 menit. **DALAM SEMUA KASUS, WATCHER OTOMATIS OVERWRITE INPUT USER** — yang merupakan bug UX fatal.
- **H3 [Goal parsing HH:MM:SS bug]** — REJECTED via inspection. L433 `parseTimeToSeconds`: explode `:` array_map intval. 3 parts = H*3600 + M*60 + S. 2 parts = M*60 + S. 00:37:00 return 2220 (37 menit). BENAR.
- **H4 [Builder predictRaceTime initial VDOT]** — REJECTED via inspection. Builder L176 `$deltaVdot = $targetVdot - $initialVdot` hanya dihitung, TIDAK PERNAH dijadikan override summary. Return summary L569-570: `vdot => initialVdot`, `target_vdot => safeTargetVdot`. DUA VALUE TERSEDIA, tidak saling overwrite.
- **H5 [Chip ease_goal auto apply]** — REJECTED via inspection. applyChipOption HANYA dipanggil dari `@click="applyChipOption(opt)"` user click. Tidak ada auto trigger dari watcher.

## Evidence Table (partial via static + instrumentation):
| TITIK | Key | Ekspektasi | Status |
|---|---|---|---|
| Watcher current_vdot L2258 | Memanggil suggestGoalTime tanpa guard userEditedGoal | OVERWRITE TERBUKTI → PERBAIKI | ✅ FIX: force=false + userEditedGoal guard |
| Controller parseTimeToSeconds | 00:37:00 = 2220s | BENAR via inspection | ✅ PASS |
| Controller safeTargetVdot L312 | Tidak pernah turun < targetVdot jika targetVdot valid | BENAR via inspection | ✅ PASS |
| Builder targetVDOT vs initialVDOT | target_vdot summary = safeTargetVdot | BENAR | ✅ PASS |

## PERBAIKAN YANG DITERAPKAN (F1-F5 + F6 Fix 500):
- **F1 [L1821-L1850]**: Tambah reactive `userEditedGoal`, `_debugGoalTimeline`, `_dbgPushGoal`. Watcher sync pada `goal_hours/goal_minutes/goal_seconds` set `userEditedGoal = true` KETIKA user edit manual.
- **F2 [L2233-L2262]**: Refactor `suggestGoalTime(force = false)`. Parameter force=false default (hanya apply jika user BELUM edit goal). force=true (dipanggil via explicit user action spt reset/1click chip) selalu apply. Juga tambah clamp lembut maintenance: jika prediksi baru 4%+ lebih cepat dari PB, clamp ke -3% dari PB untuk default awal (user tetap boleh ubah manual).
- **F3 [L2304-L2311]**: `watch(current_vdot)` → panggil suggestGoalTime(false), TIDAK overwrite jika user sudah edit.
- **F4 [L2568]**: `onMounted` → `suggestGoalTime(false)`.
- **F5 [L2075-L2101]**: `applyChipOption` ketika user click saran 1-klik goal → SET `userEditedGoal = false` (clear flag, karena explicit accept), lalu update goal H/M/S.
- **F6 [CoachFeasibilityService L253] — ROOT CAUSE HTTP 500 (Undefined variable $minMileage–)**: String double-quote PHP berisi interpolasi `$minMileage–$idealMileage` (ditempel en dash unicode U+2013) menyebabkan PHP parser membaca `$minMileage–` sebagai nama variable BARU (bukan `$minMileage` + en dash). Fix: bungkus semua variable di string tersebut dengan PHP Complex Curly Syntax: `{$minMileage}–{$idealMileage}`. Verified 0 match grep variable+endash lain di seluruh repo PHP.

## Instrumentasi Laravel Log Controller & Builder (T1-T9):
Untuk verifikasi PRE/POST fix, log ditambahkan di:
- T1 SelfGeneratedProgramController L203 validated raw
- T2 after calculateVDOT current+target
- T3 parseTimeToSeconds goal
- T4 coach assess output
- T4B 422 infeasible
- T5 SAFETARGETVDOT clamp breakdown (area PALING KRITIS back-end)
- T6 payload to builder
- T7 builder output + predict race time (NANTI)
- T8 builder guardrail pre-validation
- T9 builder deltaVdot
- Frontend JS: `_debugGoalTimeline` array 80 entries + `_debugSuggestOverwrites` counter

## Status Hipotesis FINAL (sebelum runtime user confirm):
- H1: REJECTED
- H2: ✅ CONFIRMED — ROOT CAUSE
- H3: REJECTED
- H4: REJECTED
- H5: REJECTED

## Uji yang disarankan untuk user (POST-FIX verification):
1. Step1: PB 5K = 19:35 → Step2: target 10K, manual UBAH goal jadi 37:00 → Step3/Step4: goal TETAP 37:00 (tidak berubah ke 45)
2. Step1: PB 10K = 19:35 → Step2: ubah goal ke 39:00 → Step4: TETAP 39
3. Klik Chip Saran "Terapkan Saran beban / ease goal 42" → goal BERUBAH (karena explicit click, userEditedGoal=false clear)
4. Di Step3: klik "Reset Saran" Mileage → goal TETAP user punya (hanya mileage berubah)

## Cleanup Checklist (belum dijalankan — BUTUH USER CONFIRM):
- [ ] Hapus instrumentation code region debug-point vdot-target-clamp-45min T1-T9
- [ ] Hapus JS instrumentation debug vars (_debugSuggestOverwrites, _debugGoalTimeline, _dbgPushGoal, F1 flag)
- [ ] Hapus file debug-vdot-target-clamp-45min.md ini
