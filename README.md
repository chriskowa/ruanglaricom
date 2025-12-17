# Ruang Lari - SaaS Running Platform

Platform SaaS untuk pengalaman lari dengan fitur program latihan, marketplace, event, pacer, dan KOL.

## Setup

### Environment
- Database: MySQL (`ruanglariweb`)
- URL: `http://localhost/ruanglariweb`
- Laravel 12

### Konfigurasi .env
```env
APP_NAME="Ruang Lari"
APP_URL=http://localhost/ruanglariweb
DB_CONNECTION=mysql
DB_DATABASE=ruanglariweb
```

### Installasi
```bash
composer install
php artisan migrate
php artisan db:seed
```

## Struktur URL

### Public Routes
- `/` - Redirect ke login
- `/login` - Halaman login
- `/register/{role?}` - Halaman registrasi (role: admin, coach, runner, eo)

### Runner Routes (setelah login)
- `/runner/dashboard` - Dashboard runner
- `/runner/calendar` - Kalender program lari (week-first view)
- `/runner/calendar/events` - API endpoint untuk events calendar (JSON)

### Coach Routes (setelah login)
- `/coach/dashboard` - Dashboard coach
- `/coach/programs` - CRUD Program Lari
- `/coach/programs/create` - Buat program baru
- `/coach/programs/{id}/edit` - Edit program
- `/coach/programs/generate-template` - Generate template JSON
- `/coach/programs/import-json` - Import JSON program
- `/coach/programs/generate-vdot` - Generate program dengan VDOT

## Fitur Utama

### 1. Program Lari
- Coach dapat membuat program dengan JSON sessions
- Program disimpan dalam format JSON di field `program_json`
- Runner dapat enroll program dan melihat di calendar
- Calendar week-first view dengan FullCalendar
- Support import JSON dan generate template

### 2. Calendar System
- Week-first view (Senin sebagai hari pertama)
- Load program dari JSON ke calendar
- Event click untuk melihat detail session
- Support multiple program enrollments

### 3. Authentication
- Multi-role login (Admin, Coach, Runner, EO)
- Auto redirect berdasarkan role setelah login
- Wallet otomatis dibuat saat registrasi

## Database Structure

### Core Tables
- `users` - User dengan role (admin, coach, runner, eo)
- `wallets` - Wallet per user
- `wallet_transactions` - Transaksi wallet
- `programs` - Program lari (dengan `program_json`)
- `program_enrollments` - Enrollment runner ke program
- `provinces` - Provinsi
- `cities` - Kota
- `fee_configs` - Konfigurasi fee per module

## Format Program JSON

```json
{
  "sessions": [
    {
      "day": 1,
      "type": "easy_run",
      "distance": 5,
      "duration": "00:30:00",
      "description": "Easy run 5km"
    },
    {
      "day": 2,
      "type": "rest",
      "description": "Rest day"
    }
  ],
  "duration_weeks": 12
}
```

## Testing

### Buat User Test
```bash
php artisan tinker
```

```php
// Runner
$user = \App\Models\User::create([
    'name' => 'Test Runner',
    'email' => 'runner@test.com',
    'password' => bcrypt('password'),
    'role' => 'runner',
]);
$wallet = \App\Models\Wallet::create(['user_id' => $user->id, 'balance' => 0]);
$user->update(['wallet_id' => $wallet->id]);

// Coach
$coach = \App\Models\User::create([
    'name' => 'Test Coach',
    'email' => 'coach@test.com',
    'password' => bcrypt('password'),
    'role' => 'coach',
]);
$wallet = \App\Models\Wallet::create(['user_id' => $coach->id, 'balance' => 0]);
$coach->update(['wallet_id' => $wallet->id]);
```

## Akses Aplikasi

- Login: `http://localhost/ruanglariweb/login`
- Runner Dashboard: `http://localhost/ruanglariweb/runner/dashboard`
- Calendar: `http://localhost/ruanglariweb/runner/calendar`
- Coach Dashboard: `http://localhost/ruanglariweb/coach/dashboard`

## Template

Template menggunakan Gymove dari folder `template/` yang sudah di-copy ke `public/`:
- CSS: `public/css/style.css`
- JS: `public/js/`
- Images: `public/images/`
- Vendor: `public/vendor/`
