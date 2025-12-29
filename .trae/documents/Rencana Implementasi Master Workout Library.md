# Implementasi Fitur Master Workout & Strength Training

Terima kasih. Berikut adalah detail referensi jenis workout yang akan saya masukkan ke dalam **Master Workout Library** (Database Seeder). Saya telah mengelompokkannya agar mencakup kebutuhan pelari dari pemula hingga advanced.

## 1. Database & Seeder Content
Saya akan mengisi database `master_workouts` dengan preset berikut:

### 🟢 Easy & Recovery (Base Building)
- **Easy Run**: Lari santai (Zona 2), fokus pada kenyamanan.
- **Recovery Run**: Lari sangat ringan & singkat pasca-latihan keras.
- **Shakeout Run**: Lari 15-20 menit sangat santai (biasanya H-1 Race).

### 🔵 Long Runs (Endurance)
- **LSD (Long Slow Distance)**: Lari jarak jauh dengan pace konstan nyaman.
- **Progression Long Run**: Dimulai pelan, semakin cepat di akhir sesi.
- **Long Run with Surges**: Lari jauh diselingi percepatan singkat.

### 🟡 Speed & Power (Threshold & VO2Max)
- **Tempo Run**: Lari di ambang laktat (*comfortably hard*) selama 20-40 menit.
- **Interval 400m**: Kecepatan tinggi (Pace 3K/5K) untuk repetisi pendek.
- **Interval 1000m (1K)**: Klasik interval untuk target 10K & Half Marathon.
- **Fartlek**: "Speed play", kombinasi lari cepat/lambat berbasis waktu (mis: 1 min on / 1 min off).
- **Hill Repeats**: Lari tanjakan untuk kekuatan otot kaki.
- **Strides**: 6-8x 100m lari cepat untuk melatih teknik & *cadence*.

### 🟣 Strength & Conditioning (Baru)
- **Runners Leg Strength**: Fokus Squat, Lunge, Calf Raise (tanpa alat/dumbbell).
- **Core Blaster**: Plank, Side Plank, Deadbug untuk kestabilan postur lari.
- **Full Body Gym**: Latihan menyeluruh di gym.
- **Yoga for Runners**: Fokus fleksibilitas Hip & Hamstring.
- **Cross Training**: Bersepeda/Renang sebagai alternatif lari (*low impact*).

## 2. Update Backend
- Membuat Model `MasterWorkout` dan Migration.
- Controller akan mengambil data ini dan mengirimkannya ke View dalam format Grouped JSON.

## 3. UI/UX "Workout Library" (Sidebar Baru)
- Mengganti daftar statis dengan **Accordion Menu** berdasarkan 4 kategori di atas.
- **Visual Distinction**:
    - Strength Training akan menggunakan kode warna **Ungu (Purple)**.
    - Speed session menggunakan warna **Merah/Kuning**.
- **Smart Drag & Drop**:
    - Saat item "Strength" di-drag, form input `distance` akan otomatis 0 atau disembunyikan, digantikan fokus pada `duration` atau `description`.

Apakah daftar workout ini sudah sesuai dengan ekspektasi Anda? Jika ya, saya akan mulai eksekusi.