## Target
- Hilangkan duplikasi saat edit workout (override per tanggal, upsert + dedup feed).
- Satukan Advanced Workout Builder agar konsisten di: coach athletes, runner calendar, dan coach programs edit.

## Langkah Teknis
1. Upsert CustomWorkout
- Coach/Runner: sebelum create, cek existing (enrollment_id + workout_date). Jika ada → update; jika tidak → create.
- Tambah unique index (enrollment_id, workout_date) di custom_workouts.

2. Dedup Event Feed
- Coach/Runner: saat menggabungkan program + custom, jika ada custom untuk tanggal X, jangan render sesi program default untuk X.

3. Advanced Builder Reusable
- Ekstrak komponen builder reusable (Blade/Vue) dengan props: value, defaults, mode; emits: save/delete/cancel.
- Tambah util JS: normalizeType(raw), buildSummary(state), computeTotalDistance(state). Dipakai di semua halaman.
- Integrasikan komponen di:
  - [coach/athletes/show](file:///c:/laragon/www/ruanglari/resources/views/coach/athletes/show.blade.php)
  - [runner/calendar_modern](file:///c:/laragon/www/ruanglari/resources/views/runner/calendar_modern.blade.php)
  - [coach/programs/id/edit] (halaman edit program coach).

4. Payload Seragam
- Kirim: type (hasil normalize), workout_structure {advanced}, description, distance (km), duration (HH:MM:SS bila waktu), notes.

## Verifikasi
- Edit tanggal sama berkali-kali → tetap satu custom (upsert), feed hanya satu event (dedup).
- Hasil builder identik di 3 halaman (summary, distance, duration, type, notes).

Konfirmasi: Lanjutkan implementasi sesuai rencana ini?