# Status Fitur Ruang Lari

## ✅ Fitur yang Sudah Dibuat

### 1. Authentication & User Management
- ✅ Multi-role login system (Admin, Coach, Runner, EO)
- ✅ Register per role
- ✅ Profile setup dengan update profile
- ✅ Role-based dashboards (semua role menggunakan template index.html)
- ✅ Avatar upload

### 2. Database & Models
- ✅ Migrations untuk semua tabel utama:
  - users (dengan role, city_id, package_tier, bank_account, wallet_id, referral_code, dll)
  - wallets, wallet_transactions
  - provinces, cities
  - programs, program_enrollments
  - fee_configs
- ✅ Models dengan relationships lengkap
- ✅ Seeders:
  - ProvinceSeeder (Jawa Timur)
  - CitySeeder (Malang Kota, Surabaya, Jakarta, Bandung)
  - FeeConfigSeeder (program, marketplace, event, pacer, kol)
  - AdminUserSeeder
  - UserSeeder (Coach, Runner, EO)

### 3. Program Lari
- ✅ Database schema untuk programs (dengan program_json)
- ✅ Database schema untuk program_enrollments
- ✅ Coach ProgramController (CRUD)
- ✅ Runner CalendarController (load JSON programs)
- ✅ Calendar view dengan FullCalendar.js (week-first)
- ✅ Route untuk generate dan import JSON program (placeholder)

### 4. Middleware & Authorization
- ✅ CheckRole middleware
- ✅ ProgramPolicy untuk authorization
- ✅ Role-based route protection

### 5. UI/UX
- ✅ Template Gymove terintegrasi
- ✅ Dashboard untuk semua role (Admin, Coach, Runner, EO)
- ✅ Profile page dengan update form
- ✅ Sidebar dengan menu sesuai role
- ✅ Responsive layout

## 🚧 Fitur yang Masih Perlu Dibuat

### 1. Wallet & Referral/Affiliate
- ⏳ Wallet deposit/withdraw functionality
- ⏳ Bank account verification
- ⏳ Referral program (unique code, commission payout)
- ⏳ Affiliate program (seller sets % commission)
- ⏳ Transaction history

### 2. Program Lari (Lanjutan)
- ⏳ Generate program JSON menggunakan metode VDOT
- ⏳ Import program JSON dari file
- ⏳ Runner enroll program
- ⏳ Calendar sync dengan Strava
- ⏳ Export ke Google Calendar

### 3. Marketplace
- ⏳ Product CRUD
- ⏳ Product orders
- ⏳ Inventory management
- ⏳ Affiliate links
- ⏳ Package tier restrictions (Basic: max 3 products)

### 4. Event Management
- ⏳ Event CRUD
- ⏳ Ticket types
- ⏳ Ticket sales
- ⏳ QR check-in
- ⏳ Event analytics untuk EO

### 5. Pacer
- ⏳ Pacer profiles
- ⏳ Pacer booking
- ⏳ Reviews system

### 6. KOL (Influencer)
- ⏳ KOL profiles
- ⏳ KOL booking
- ⏳ Rate card management

### 7. Payment Gateway
- ⏳ Midtrans integration
- ⏳ Payment processing

### 8. Chat/Message
- ⏳ Chat system antar user
- ⏳ UI/UX menggunakan template Gymove

## 📝 Catatan

- Semua dashboard sudah menggunakan template index.html
- Asset paths sudah diperbaiki (menggunakan asset() helper)
- Profile update sudah berfungsi dengan upload avatar
- Seeders sudah dibuat untuk testing semua role
- Database structure sudah siap untuk semua modul












