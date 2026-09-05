# Workspace Rules

- **Do not automatically perform `git commit` or `git push`**: Do not stage, commit, or push code changes to the Git repository unless explicitly requested by the user. Leave all changes unstaged in the workspace for manual review.
- **Ponytail Pragmatic Engineering (The Lazy Senior Dev)**: Always apply the Ponytail Decision Ladder (`.agents/skills/ponytail`). *"The best code is the code you never wrote."* Prioritize YAGNI, code reuse, standard/native features, and simplicity before writing new code. Never over-engineer, over-abstract, or write speculative code.

---

# UI/UX & Design Guidelines (Strictly Mandatory)

## 0. Mandatory Execution Workflow

Sebelum melakukan perubahan kode atau antarmuka UI/UX, WAJIB mengacu pada `AGENTS.md` dan mengeksekusi tahapan skills ini secara berurutan:

```
AGENTS.md
│
├── ponytail (.agents/skills/ponytail)
│   → Evaluasi Decision Ladder: YAGNI, Reuse, Native/Standard Library, Simplicity & Zero Over-Engineering
│
├── design-intelligence (.agents/skills/design-intelligence)
│   → Menentukan arah desain, konteks produk, palet warna, tipografi, dan komposisi layout
│
├── frontend-quality (.agents/skills/frontend-quality)
│   → Memastikan kualitas kode, arsitektur komponen, responsivitas mobile-first, dan state handling
│
└── ux-review (.agents/skills/ux-review)
    → Mengaudit hasil akhir, mendeteksi friksi usability, dan memastikan standard anti-AI terpenuhi
```

---

## 1. Anti-AI Clichés & 30 Golden Rules (Standard Desain Manusia & Profesional)

Desain dan antarmuka harus terlihat *human-crafted*, fungsional, bersih, berbobot, dan siap produksi—bebas dari ciri khas template generator AI:

1. **Tanpa Gradien Warna Terlalu Keras**: Hindari gradasi warna bertabrakan tanpa alasan (seperti ungu ke biru ke pink dalam satu tombol). Gunakan palet tenang, 2 sampai 3 warna brand solid, dan kontras tegas antara teks dan latar.
2. **Tanpa Ikon Lucide/Garis di Mana-Mana**: Jangan pasang ikon garis tipis di setiap judul, tombol, atau label. Ikon hanya digunakan jika membantu pemindaian visual (*fast scan*). Jika hanya hiasan, hapus.
3. **Kontras Tinggi & Aksesibilitas Jelas**: Hindari teks abu-abu pucat (`text-slate-500`) di latar putih atau latar gelap. Rasio kontras minimal 4.5:1 (`text-white`, `text-slate-200`, `text-slate-900`).
4. **Tanpa Warna Pelangi**: Hindari mencampur merah, kuning, hijau, dan biru dalam satu tampilan tanpa hierarki. Gunakan 2–3 warna brand dan satu warna aksen status; sisanya netral.
5. **Tanpa Bayangan Jatuh Berlebihan (Excessive Drop Shadows)**: Hindari shadow tebal berlapis 3D atau neon glow. Gunakan shadow tipis/subtle (`shadow-sm`) hanya pada elemen yang membutuhkan elevasi nyata (modal/dropdown).
6. **Hindari Layout Template "3 Kartu Sejajar" yang Membosankan**: Hindari layout generator generik (ikon + judul + 1 kalimat sama rata 3 kolom). Bangun struktur hierarki yang menonjolkan fitur dan fungsi nyata produk.
7. **NO Emojis Anywhere**: Dilarang keras menggunakan emoji (🏃‍♂️, ⚡, 🚀, ⏱️, ✨, dll.) di template UI, alert, badge, tombol, maupun placeholder. Gunakan teks profesional atau ikon SVG/FontAwesome fungsional.
8. **Tanpa Efek Kaca Cair (Liquid Glass / Glassmorphism Berlebih)**: Hindari penggunaan `backdrop-blur` dan `bg-white/5` atau `bg-slate-900/40` semi-transparan yang memudarkan keterbacaan. Kartu, panel, dan modal harus 100% solid, tajam, dan *opaque*.
9. **Gaya Penulisan Manusiawi (No Em Dashes `—` Berlebihan & No AI Buzzwords)**: Hindari tanda pisah panjang (`—`) di setiap kalimat dan pola puitis AI. Gunakan tanda baca titik dan koma yang lugas dan natural.
10. **Tipografi Berkarakter Sesuai Brand**: Hindari tipografi generik tanpa hierarki. Gunakan font yang tegas dan berkarakter (misal Oswald/Bebas Neue untuk judul olahraga, Inter untuk body text, JetBrains Mono untuk angka/telemetri).
11. **Tanpa Garis Warna Sisi Kiri (Stripe Border Kiri Klise)**: Hindari stripe warna dekoratif vertikal di sisi kiri kartu yang tidak membawa informasi fungsi. Bangun hierarki lewat ukuran, ketebalan, dan jarak.
12. **Data & Testimoni Nyata**: Hindari konten ulasan buatan tanpa identitas. Tampilkan data riil, metrik performa asli, atau format interaksi nyata.
13. **Hindari Bento Grid Kosong**: Jangan membuat kotak bento hanya untuk diisi 1 kalimat pendek. Gunakan grid hanya jika konten padat informasi.
14. **Tanpa Hero Jendela Terminal Palsu**: Jangan memasang mockup editor terminal/coding di halaman depan aplikasi non-developer. Tampilkan instrumen dan produk asli.
15. **Hindari Copywriting Template AI ("Bukan X, Ini Y")**: Hindari kalimat klise seperti *"Bukan aplikasi biasa, ini solusi..."*. Tulis manfaat spesifik dan langsung untuk pengguna.
16. **Daftar Poin Natural (Bukan Checklist Centang di Mana-Mana)**: Jangan memakai ikon centang hijau di setiap baris. Gunakan list natural; centang hanya untuk checklist tugas sebenarnya.
17. **Struktur Harga / Opsi yang Relevan**: Jangan memaksakan template 3 tier harga (Basic, Pro, Enterprise) jika aplikasi hanya butuh alur langsung.
18. **Tampilkan Instrumen & Demo Nyata**: Jangan hanya teks promosi; sediakan instrumen interaktif, input presisi, dan preview nyata.
19. **Batas Radius Proporsional (No Pill Badges Everywhere)**: Dilarang mengubah semua elemen menjadi bentuk pil lonjong (`rounded-full`). Gunakan standar border-radius kotak presisi (`rounded-md`, `rounded-lg`).
20. **Hindari Kombinasi Ungu + Hitam Klise AI**: Gunakan palet warna resmi platform (misal Deep Athletic `#080A0D`, Slate solid `#12161D`, Sport Red `#E63946`, Performance Orange `#ccff00`, Athletic Green `#22C55E`).
21. **Skeleton Loader Halus**: Gunakan skeleton loader subtle saat proses memuat data agar UI tidak terasa *freeze* atau kosong melompong.
22. **Tanpa Orbs / Bola Radial Blur di Latar**: Hapus orbs/blobs cahaya di background yang mengaburkan teks. Latar harus bersih dan kontras.
23. **Tanpa Dot Grid / Pattern Titik-Titik AI**: Jangan gunakan background pola grid titik-titik jika tidak memiliki fungsi layout/canvas.
24. **Dilarang Menggunakan Ikon Sparkle / Kilau (✨ / Fa-Sparkles)**: Hapus simbol kilau AI. Gantilah dengan angka terukur dan data nyata.
25. **Tanpa Animasi Gelisah / Panah Berjalan Terus**: Gerakan harus memberikan umpan balik arah yang fungsional, bukan dekorasi bergerak terus-menerus.
26. **Kelengkapan Navigasi & Syarat Ketentuan**: Pastikan struktur tautan footer, legalitas, dan navigasi formal tersedia rapi.
27. **Kejelasan Privasi Data**: Jelaskan alur penyimpanan data (misal: data disimpan lokal di browser pelatih tanpa upload ke server eksternal).
28. **Animasi Hover Fungsional & Tenang**: Hindari efek zoom/bounce berlebih pada setiap elemen yang disentuh kursor. Cukup transisi warna halus (`transition duration-150`).
29. **Tanpa Warna Neon Menyilaukan**: Hindari teks/border neon menyala yang menusuk mata di mode gelap. Gunakan solid high-contrast tones.
30. **Tanpa Warna Pastel Generik / Pudar**: Hindari warna pastel template yang mencuci kontras teks.
31. **Tanpa Span Pill di Atas H1**: Dilarang keras memasang span pill / chip badge melayang di atas judul H1 (seperti chip kategori dengan dot berkedip/ping). Judul H1 harus langsung menyapa pengguna secara bersih dan profesional.
32. **Top Padding Halaman Nol (`pt-0`)**: Hindari memberi `min-h-screen pt-20` atau `pt-24` pada pembungkus halaman utama. Jadikan `pt-0` agar konten halaman langsung terlihat rapi di layar (*above the fold*) tanpa ruang kosong menganga.

---

## 2. Standar Border Radius (Mandatory)

Semua komponen UI WAJIB mematuhi skala border-radius berikut:

| Elemen UI           | Class Tailwind | Keterangan                                  |
|---------------------|----------------|---------------------------------------------|
| Tombol (Buttons)    | `rounded-md`   | Semua tombol aksi, CTA, submit formulir     |
| Input / Select / Form| `rounded-md`  | Input teks, dropdown, textarea              |
| Kartu / Panels      | `rounded-lg`   | Kartu konten, kotak statistik, filter panel |
| Modals / Dialogs    | `rounded-lg`   | Dialog modal, popup konfirmasi              |
| Badges / Tags       | `rounded`      | Label status, chip kategori                 |
| Container Tabel     | `rounded-lg`   | Pembungkus luar tabel                       |
| Avatar / Foto Profil| `rounded-full` | **HANYA** untuk foto profil & avatar bulat  |
| Progress Bar Track  | `rounded-sm`   | Jalur dan isian progress bar                |
| Alerts / Notifikasi | `rounded-md`   | Banner notifikasi dan flash messages        |

> **Nilai yang DILARANG pada elemen non-avatar**: `rounded-xl`, `rounded-2xl`, `rounded-3xl`, `rounded-full`.

---

## 3. Kebijakan Ikon pada Tombol (Button Icon Policy)

- **Tombol Teks TIDAK MEMERLUKAN Ikon**, kecuali tombol aksi utama "Tambah/Buat Baru" (dapat menggunakan simbol teks `+`).
- **Tombol Icon-Only** hanya diperbolehkan untuk aksi tabel yang sangat ringkas (edit, hapus, unduh) dengan ukuran `text-xs` tanpa background mencolok.
- **Jangan memasangkan ikon + teks di setiap tombol**. Teks yang jelas sudah sangat informatif.

---

## 4. Skala Tipografi & Kontras (Typography Scale)

| Kegunaan            | Class Tailwind                          | JANGAN Gunakan                                 |
|---------------------|-----------------------------------------|------------------------------------------------|
| Judul Halaman (H1)  | `text-2xl font-bold text-white`         | `font-black`, `italic`, `tracking-tighter`     |
| Judul Seksi (H2)    | `text-lg font-semibold text-white`      | `font-extrabold`, `font-black`, `tracking-tight`|
| Judul Kartu (H3/H4) | `text-base font-semibold text-white`    | `font-black`                                   |
| Teks Utama / Body   | `text-sm text-slate-200`                | `text-slate-500` (terlalu gelap/pudar)         |
| Subtitle / Label    | `text-sm text-slate-300`                | `font-mono uppercase tracking-widest`          |
| Keterangan / Meta   | `text-xs text-slate-400`                | `text-[10px]`, `text-[11px]` (gunakan hemat)   |
| Angka / Telemetri   | `text-sm font-mono text-white`          | Terapkan font monospace hanya pada angka/kode  |

---

## 5. Spacing & Whitespace (Anti-Crowded)

- **Ample Container Padding**: Wadah utama kartu/modal menggunakan padding lega (`p-5`, `p-6`), hindari padding sesak (`p-2`, `p-3`).
- **Separasi Jelas**: Gunakan jarak antar seksi `space-y-4` hingga `space-y-6`, dan jarak grid card `gap-3.5` hingga `gap-6`.
- **Ruang Bernapas Teks**: Pastikan jarak antara label, input, dan judul memiliki margin teratur (`mb-1.5`, `mt-1`, `leading-relaxed`).
- **Zero Page Top Padding (`pt-0`)**: Pembungkus halaman utama wajib menggunakan `pt-0` (bukan `pt-20` atau `pt-24`) agar konten langsung tampil tanpa gap kosong atas.
- **Tanpa Elemen Mengambang di Atas H1**: Jangan letakkan tag span, pill badge, atau kicker di atas H1. Mulai langsung dari H1.
