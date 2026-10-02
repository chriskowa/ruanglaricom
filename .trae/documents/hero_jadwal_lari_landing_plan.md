# Redesign Hero Halaman /jadwal-lari Implementation Plan

## Repository Research

### Kondisi Hero Saat Ini (events/landing.blade.php)

### User Request Explicit:

### Standar Pakem Wajib Berlaku (design-intelligence skill):

***

## Files and Modules

| File Path                                                                | Perubahan yang Diharapkan                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| ------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `resources/views/events/landing.blade.php`                               | 1. Hapus blok `<style>` 035-400 arbitrary hex CSS, ganti Tailwind standard utility class inline. 2. Hapus copy logic @php 11-19 gambar hero public\_path (tidak dipakai). 3. Section hero L1049-1260: ganti gambar latar cinematic → **solid data-driven panel pattern** + identity motif (garis start-finish running track subtle sebagai top accent stripe). 4. Rewrite H1, subhead, CTA primary/secondary pakem tipografi. 5. Kolom kanan floating-race-card → hapus backdrop-blur, ganti solid bg-slate-900 border-slate-800, hapus ambient glow blob. 6. Trust stats: query hitung asli dari model (bukan hardcoded 500+). 7. Mobile breakpoint test. |
| `app/Http/Controllers/PublicRunningEventController.php` L200-210 index() | Inject variable `$statsCount` (count events, count unique cities, count submissions) agar trust stats menampilkan DATA ASLI DARI DB (R-17 no fake data). Atau langsung compact count query ke view.                                                                                                                                                                                                                                                                                                                                                                                                                                                        |

***

## Design Read (Liveliness Dials - antislop R-37)

> Reading this as: **Public Jadwal Lari Landing** untuk pelari & EO Indonesia, dalam **Athletic Calm Editorial** (tidak hype, tidak neon, ramah nyaman profesional) style, dial **ENERGY 2 / RHYTHM 2 / MOTION 1**.

### Identity Motif Baru (anti generic - R-20):

**Running Track Start/Finish Stripe (6 garis tipis berwarna slate-700 / lime subtle)** di **atas border bawah section hero** sebagai motif berulang yang khas Ruang Lari (kalau di swap logo pun masih terasa "ini platform lari", bukan generic SaaS). Alasannya: kalender lari = identitas dunia lari, start finish stripe = pengumuman race baru / garis start karir lari user → 100% purpose sesuai domain (lulus Purpose-Gate R-07).

***

## Implementation Steps (Dependency Order)

### Step 1: Backend — Inject COUNT Query Real untuk Trust Stats (R-17)

**File:** `PublicRunningEventController.php::index()` sebelum `return view`

### Step 2: Hapus @PHP Copy gambar hero arbitrary & Bersihkan inline style CSS section

**File:** `events/landing.blade.php`

### Step 3: Redesign Hero Container + Identity Motif (TANPA GAMBAR FOTO LATAR)

**Area L1049-L1062 Section hero-v2-container:**

### Step 4: Rewrite Hero Headline, Subheadline, CTA (pakem typografi + anti uppercase + CTA spesifik R-15)

**Area L1067-L1115 Left Column:**

### Step 5: Redesign Upcoming Race Card (Hapus Glassmorphism + Glow + Solid Surface R-10)

**Area L1119-L1260 Right Column floating-race-card:**

### Step 6: Audit Semua Inline `style="color: #..."` atau `bg-[#...]` di Bawahnya Section

### Step 7: Responsive Mobile Breakpoint Audit (R-03)

***

## Dependencies and Considerations

***

## Validation Setelah Implementasi

***

## Risiko dan Mitigasi

| Risiko                                                                                 | Penanganan                                                                                                      |
| -------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| `$statsTotalEvents` query error karena model beda nama (RunningEvent vs Event)         | Wrap dengan `try/catch` di Controller, fallback ke `(int) $events->total()` jika paginate.                      |
| Header column stats di mobile overflow 3 kolom                                         | Jika lebar < 320px → fallback `grid-cols-1 sm:grid-cols-3` (tapi default coba 3 kolom dulu).                    |
| Section filter-form ID TIDAK ADA (anchor scroll broken)                                | Grep view terlebih dahulu sebelum final; jika tidak ada set ke `#events-list` / section filter terdekat.        |
| Font Inter Tight / Plus Jakarta Sans tidak diload di layout pacerhub                   | Cek head layout; jika belum ada link import Google Fonts → tambahkan ke layout parent.                          |
| Upcoming race carousel `x-data Alpine` logic next/prev broken karena class CSS dihapus | Pastikan wrapper div `x-data` dan `@click next/prev` TIDAK dihapus; hanya ganti class CSS background/card saja. |

