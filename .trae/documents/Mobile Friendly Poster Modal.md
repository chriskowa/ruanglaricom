## Masalah Saat Ini (Mobile)
- Poster modal berisi 3 panel (Preview + Customize + Styles). Di mobile ini menjadi tumpukan vertikal sehingga:
  - ruang untuk kontrol jadi sempit, banyak fitur “ketutup” karena tiap panel punya scroll sendiri (`overflow-y-auto` + `max-h`),
  - tombol penting (Close/Download) tidak selalu terlihat,
  - kontrol map (zoom/reset) pakai `group-hover` jadi cenderung tidak muncul di mobile.

## Target UX (Mobile)
- **Preview selalu terlihat** (tidak hilang saat user scroll kontrol).
- **Kontrol cukup lebar dan mudah disentuh** (hit area ≥ 44px, jarak antar tombol cukup).
- **Aksi utama selalu ada** (Download, Close, Reset) lewat sticky action bar.
- **Kontrol dibagi per tab/section** supaya tidak overwhelming.

## Rencana Perubahan UI (Responsif)
### 1) Layout Mobile: Preview + Bottom Sheet Controls
- Untuk `<md`:
  - Preview ditempatkan di atas dengan tinggi tetap (mis. `h-[42vh]` sampai `h-[50vh]`), `sticky top-0` di dalam modal.
  - Controls dijadikan **bottom sheet** (panel bawah) dengan `h-[50vh]`, `overflow-y-auto`, dan `rounded-t-3xl`.
  - Hanya bottom sheet yang scroll (hindari 2–3 scroll container sekaligus).

### 2) Satukan 2 Sidebar Jadi Tab
- Sembunyikan 2 sidebar desktop di mobile (`hidden md:flex`).
- Buat satu panel mobile dengan tab:
  - Tab 1: **Customize** (Edit Data, Elements, Stats size, Map position/size, Splits)
  - Tab 2: **Styles** (style picker + deskripsi)
- Tab bar dibuat sticky di atas bottom sheet supaya gampang pindah.

### 3) Action Bar Sticky
- Tambah bar aksi sticky (di bawah modal atau di bottom sheet footer):
  - Download
  - Close
  - Reset cepat (mis. Reset Map + Reset Splits)
- Tambahkan padding safe-area: `pb-[env(safe-area-inset-bottom)]`.

### 4) Map Controls: Mobile-Friendly
- Ubah visibilitas kontrol map agar tidak bergantung hover:
  - di mobile: `opacity-100`
  - di desktop: tetap `opacity-0 group-hover:opacity-100`
- Pertahankan drag touch yang sudah ada.

### 5) Kontrol Advanced: Accordion
- Bagian yang panjang (Map Offset, Splits Position) dijadikan accordion/collapsible di mobile:
  - default collapsed
  - expandable ketika diperlukan

## Implementasi Teknis
- Refactor markup poster modal di:
  - [index.blade.php](file:///c:/laragon/www/ruanglari/resources/views/calendar/index.blade.php)
- Prinsip implementasi:
  - Desktop (md+) tetap 3 kolom seperti sekarang.
  - Mobile menggunakan elemen baru (tabs/bottom sheet) dan menyembunyikan sidebar lama.
  - Reuse state Vue yang sudah ada (posterData, posterOptions, posterStyle, showChart, dsb) tanpa ubah logic bisnis.

## Verifikasi
- Mobile viewport (375×667 dan 390×844):
  - semua kontrol bisa diakses tanpa “ketutup”,
  - download/close selalu terlihat,
  - map controls bisa dipakai,
  - scroll terasa natural (1 scroll utama).
- Desktop tetap sama (tidak regress).

Jika disetujui, saya akan langsung mengimplementasikan layout bottom-sheet + tabs untuk mobile dan memastikan semua kontrol yang sebelumnya di sidebar tetap tersedia.