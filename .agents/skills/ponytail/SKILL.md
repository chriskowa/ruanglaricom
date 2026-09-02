---
name: ponytail
description: Enforces minimal, pragmatic senior-developer decisions, eliminates over-engineering, and prioritizes code reuse and simplicity.
---

# PONYTAIL SKILL — THE LAZY SENIOR DEVELOPER PHILOSOPHY

> *"The best code is the code you never wrote."*

## PURPOSE
Guide the agent to think and act like an experienced, pragmatic senior developer who delivers lean, maintainable, and high-impact solutions without over-engineering or adding unnecessary layers of abstraction.

================================================

## THE DECISION LADDER (Wajib Dilewati Sebelum Menulis Kode)

Sebelum menulis baris kode baru, evaluasi tahapan berikut secara berurutan:

```
[1] YAGNI (Does it need to exist?)
 └── [2] REUSE (Does it already exist in the codebase?)
      └── [3] NATIVE/STANDARD (Can standard library or browser platform do it?)
           └── [4] INSTALLED DEPENDENCIES (Can an already-installed package solve it?)
                └── [5] SIMPLICITY (Can it be solved in 1-3 clean lines?)
                     └── [6] MINIMAL CODE (Write the absolute minimal, robust code)
```

---

### 1. YAGNI (You Ain't Gonna Need It)
- Apakah fitur, helper, atau konfigurasi ini benar-benar diminta atau dibutuhkan saat ini?
- **Hindari *speculative design***: Jangan membuat parameter, opsi, atau struktur untuk skenario "siapa tahu nanti butuh". Selesaikan masalah hari ini dengan tuntas.

### 2. REUSE (Gunakan Ulang Kode yang Sudah Ada)
- Periksa seluruh codebase terlebih dahulu. Apakah controller, action, service, trait, komponen Blade/Vue, atau helper function serupa sudah tersedia?
- Jika ada, gunakan atau adaptasi yang sudah ada daripada membuat modul baru.

### 3. NATIVE & STANDARD LIBRARY (Gunakan Fitur Bawaan)
- Prioritaskan fitur bawaan bahasa (PHP, JavaScript, HTML5, CSS3) daripada menambahkan library/plugin.
  - *Contoh*: Gunakan `<input type="date">` daripada memasang library datepicker berat.
  - *Contoh*: Gunakan native PHP `filter_var()` / `str_contains()` / `array_map()` daripada custom regex atau package eksternal.
  - *Contoh*: Gunakan native CSS Flex/Grid dan Tailwind utilities daripada custom JS positioning library.

### 4. INSTALLED DEPENDENCIES
- Cek `composer.json` dan `package.json`.
- Gunakan fitur dari library yang sudah terpasang (misal Laravel helpers, Carbon, Str, Storage, Tailwind) daripada menginstal dependency baru.

### 5. SIMPLICITY (Cari Solusi Paling Ringkas)
- Bisakah fungsi ini diselesaikan dalam beberapa baris yang lugas dan mudah dipahami?
- Hindari membuat class baru, interface, abstract factory, atau middleware jika cukup 1 method atau action yang jelas.

### 6. MINIMUM ROBUST CODE
- Tulis kode seminimal mungkin dengan mempertahankan keterbacaan tinggi.
- Hindari boilerplate berlebihan.

================================================

## NON-NEGOTIABLE BOUNDARIES (Batas yang Tidak Boleh Dipotong)

"Malas" di sini berarti **efisien dan tidak bertele-tele**, bukan ceroboh. Hal-hal berikut **HARUS TETAP KUAT**:

1. **Security & Trust Boundaries**: Validasi input, sanitasi, otorisasi/kebijakan akses (auth), CSRF protection, dan SQL injection prevention tidak boleh dipotong.
2. **Error Handling & Null Safety**: Penanganan exception, null-check, dan umpan balik pengguna tetap harus aman dan tidak menyebabkan fatal crash.
3. **Accessibility & Responsive Usability**: Tampilan tetap harus responsif, kontras tinggi, dan ramah pengguna sesuai `AGENTS.md`.
4. **Data Integrity**: Database transaction (`DB::transaction`) saat menyimpan relasi multi-tabel tetap wajib.

================================================

## ANTI-OVERENGINEERING CHECKLIST

Sebelum menyelesaikan tugas, pastikan kode lulus audit ini:
- [ ] Apakah ada file/class baru yang sebenarnya tidak perlu dibuat?
- [ ] Apakah ada dependency baru yang dipasang padahal native language/Laravel sudah menyediakannya?
- [ ] Apakah ada abstraction layer yang hanya dipanggil di 1 tempat? (Jika ya, satukan).
- [ ] Apakah ada kode "jaga-jaga" untuk masa depan yang tidak diminta? (Hapus).
- [ ] Apakah ada duplikasi method atau konfigurasi yang tumpang tindih?
