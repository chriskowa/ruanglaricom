## Target Halaman
- Dashboard runner yang saat ini memakai `layouts.pacerhub` (`resources/views/runner/dashboard.blade.php`).
- Layout `resources/views/layouts/pacerhub.blade.php` belum memuat sidebar.

## Implementasi Sidebar (Tailwind)
- Buat partial baru `resources/views/layouts/components/pacerhub-sidebar.blade.php` berisi menu terkelompok: Main, Commerce, Community, Account.
- Gunakan Tailwind utility untuk styling (dark neon) agar konsisten dengan PacerHub.
- Tautan role-aware:
  - Admin: `admin.dashboard`
  - Coach: `coach.dashboard`, `coach.programs.index`
  - Runner: `runner.dashboard`, `runner.calendar`, `programs.index`
  - EO: `eo.dashboard`
  - Umum: `feed.index`, `chat.index`, `notifications.index`, `profile.show`, `logout`.
  - Guest: `home`, `calendar.public`, `programs.index`, `login`, `register`.

## Integrasi ke Layout PacerHub
- Di `layouts/pacerhub.blade.php`:
  - Tambah tombol toggle sidebar (hamburger) di `pacerhub-nav.blade.php` (kanan header).
  - Sisipkan off-canvas sidebar: `fixed left-0 top-20 bottom-0 w-64 bg-slate-900 border-slate-700`, dengan backdrop `fixed inset-0 bg-black/40`.
  - Tampilkan/sembunyikan via JS kecil (toggle class `hidden`), tanpa dependensi ekstra.

## Khusus Dashboard
- Aktifkan sidebar pada halaman dashboard runner. Opsi:
  - Set flag Blade: `@php($withSidebar = true)` di `runner/dashboard.blade.php`.
  - Di layout, `@if(isset($withSidebar) && $withSidebar)` untuk render sidebar + backdrop.
- Opsi lanjutan: izinkan sidebar untuk halaman lain (calendar) jika diperlukan, cukup set flag yang sama.

## Interaksi & Aksesibilitas
- Hamburger di header membuka/menutup sidebar; backdrop klik menutup.
- Fokus keyboard tetap: shortcut yang sudah ada tetap berjalan.
- ARIA: `role="navigation"`, tombol dengan `aria-expanded` dan `aria-controls`.

## Verifikasi
- Buka dashboard runner, klik hamburger untuk membuka sidebar, cek semua tautan berfungsi sesuai peran/login.
- Periksa responsivitas: mobile dan desktop, memastikan konten tidak tertutup saat sidebar aktif.

Konfirmasi untuk lanjut: saya akan menambahkan partial sidebar Tailwind, toggle di nav, dan integrasi conditional di layout PacerHub sesuai rincian di atas.