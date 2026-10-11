# PLATINUM PROJECT - FULLSTACK ARCHITECTURE & CONTEXT DOCUMENTATION

Dokumen ini disusun untuk diunggah langsung ke LLM / chatbox AI (Claude, ChatGPT, Gemini, DeepSeek) sebagai system context teknis lengkap agar AI memahami seluruh struktur, aliran bisnis, skema basis data, API contract, dan dependensi proyek.

---

## 1. Ringkasan Eksekutif & Domain Bisnis

Platinum Project adalah aplikasi web pemesanan dan manajemen operasional vendor dekorasi pernikahan/acara (wedding & event decoration). Aplikasi melayani dua aktor utama:
1. **Klien / Calon Pengantin (Publik)**: Memilih paket dekorasi (Rumah, Layos, Gedung Kecil, Gedung Besar, Lamaran), menentukan koordinat lokasi pemasangan melalui peta interaktif OpenStreetMap/Leaflet, melakukan booking dengan DP terkunci Rp 1.000.000, serta otomatis dihubungkan ke WhatsApp admin untuk konfirmasi pembayaran.
2. **Admin / Manajemen Vendor**: Mengelola status pemesanan, jadwal kalender acara, pelunasan, pencatatan biaya operasional riil (RAB/expenses), arus kas bulanan, dan margin laba bersih.

---

## 2. Tech Stack & Dependensi Inti

### Backend (`platinumproject-backend`)
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Autentikasi**: Laravel Sanctum 4.x (SPA cookie / Bearer token API)
- **Basis Data**: MySQL / MariaDB (Production), SQLite (Testing/Development)
- **Ekspor Dokumen**: `maatwebsite/excel` (Excel xlsx export)
- **Arsitektur**: Controller-Service-Resource pattern dengan Form Requests
- **Struktur Route**:
  - `routes/api.php`: Semua endpoint RESTful API
  - `bootstrap/app.php`: Middleware configuration & alias (`admin` -> `AdminOnly`)

### Frontend (`platinumproject-frontend`)
- **Framework**: Next.js 16.3.4 (App Router, Turbopack)
- **UI Library & CSS**: React 19.2, Tailwind CSS 3.4, `tailwindcss-animate`, Lucide React
- **Komponen Peta**: Leaflet 1.9, `react-leaflet` 5.0 (Client-side dynamic import tanpa SSR window error)
- **Validasi Form**: React Hook Form 7.87 + Zod 4.5
- **HTTP Client**: Axios 1.20 (Admin & Booking) dan Fetch API (Financial API)
- **State & Data Fetching**: TanStack React Query 5.x
- **Grafik & Visualisasi**: Recharts 3.10
- **Ekspor Dokumen**: `jspdf`, `jspdf-autotable`, `xlsx`
- **Notifikasi**: Sonner

---

## 3. Alur Kerja Sistem (Business Flows)

```
[Klien Publik]
   │
   ├─ 1. Buka Katalog Paket (/paket)
   ├─ 2. Isi Form Booking (/booking) -> Pilih paket, tgl H+7, pin lokasi Leaflet
   ├─ 3. POST /api/bookings -> Generate Booking Code #PB[YYYYMMDD][XXX]
   ├─ 4. Redirect /booking/success -> Muncul 3 link WhatsApp Admin
   │
[WhatsApp Konfirmasi]
   │
   └─ Klien kirim format booking & bukti transfer DP Rp 1.000.000
         │
[Admin Platinum Project]
   │
   ├─ 1. Login Admin (/admin/login) -> POST /api/admin/login -> Bearer Token
   ├─ 2. Konfirmasi Jadwal (/admin/bookings) -> Ubah status jadi 'confirmed' & set total_amount
   ├─ 3. Catat DP (/admin/financial) -> POST /api/financial/payment (type: dp)
   ├─ 4. Catat Pengeluaran RAB -> POST /api/financial/expense (bunga, transportasi, tukang)
   ├─ 5. Catat Pelunasan H-1 -> POST /api/financial/payment (type: pelunasan)
   └─ 6. Pantau Cash Flow & Laba Bersih -> Dashboard & Laporan PDF/Excel
```

### Aturan Bisnis Kunci
- **Tanggal Acara**: Wajib minimal H+7 hari dari tanggal saat booking dibuat (`after_or_equal:today+7`).
- **Komitmen DP**: Terkunci default Rp 1.000.000 untuk penguncian tanggal dan jadwal survei.
- **Kode Booking**: Dibuat otomatis dengan format `#PB{YYYYMMDD}{SEQ}` (misal: `#PB20261011001`).
- **Pelunasan**: Maksimal H-1 sebelum Hari H acara. Status otomatis berubah menjadi `fully_paid` ketika `total_paid >= total_amount`.
- **Posisi Kas**: `Cash Position = Total Paid - Total Expenses`.
- **Expected Profit**: `Package Total Amount - Total Expenses`.

---

## 4. Skema Basis Data & Relasi Entitas

```
┌──────────────────┐       1:N       ┌────────────────────────┐
│      users       │────────────────<│        bookings        │
│ id, name, email, │                 │ id, user_id, code,     │
│ role (admin/user)│                 │ bride_names, lat, lng, │
└──────────────────┘                 │ total_amount, paid, .. │
                                     └────────────────────────┘
                                            │            │
                                     1:N    │            │ 1:N
                                            ▼            ▼
                            ┌─────────────────┐  ┌──────────────────┐
                            │  payment_logs   │  │     expenses     │
                            │ booking_id,     │  │ booking_id,      │
                            │ type (dp/lunas) │  │ item_name,       │
                            │ amount, method  │  │ amount, date     │
                            └─────────────────┘  └──────────────────┘
```

### Tabel `bookings`
- `id` (bigint, PK)
- `user_id` (bigint, nullable, FK -> users.id)
- `booking_code` (string, unique, index)
- `bride_names` (string)
- `initials` (string, max 10)
- `event_date` (date, index)
- `event_type` (string, enum: Wedding, Birthday, Corporate, Other)
- `decoration_type` (enum: 'Dalam', 'Luar')
- `phone` (string, format 08xx atau +62xx)
- `address` (text)
- `lat` (decimal 10,8), `lng` (decimal 11,8)
- `maps_url` (string)
- `package_type` (string)
- `dp_amount` (decimal 12,2)
- `total_amount` (decimal 12,2, nullable)
- `total_paid` (decimal 15,2, default 0)
- `remaining_amount` (decimal 15,2, auto-sync)
- `payment_status` (enum: 'pending', 'dp_paid', 'paid', 'fully_paid')
- `status` (enum: 'pending', 'confirmed', 'completed', 'cancelled')
- `notes` (text, nullable)
- timestamps (`created_at`, `updated_at`)

### Tabel `payment_logs`
- `id`, `booking_id` (FK -> bookings.id), `payment_type` ('dp'|'pelunasan'|'lainnya'), `amount` (decimal), `payment_date` (date), `payment_method` ('transfer'|'cash'|'ewallet'), `notes` (text), `created_by` (FK -> users.id), timestamps.

### Tabel `expenses`
- `id`, `booking_id` (FK -> bookings.id), `item_name` (string), `description` (text), `amount` (decimal), `expense_date` (date), `notes` (text), `created_by` (FK -> users.id), timestamps.

### Tabel `packages`
- `id`, `name`, `slug` (unique), `category` ('rumah'|'layos'|'gedung_kecil'|'gedung_besar'|'engagement'), `description`, `features` (json), `sort_order` (int), `is_active` (bool).

---

## 5. Ringkasan Endpoint API

| Method | Endpoint | Akses | Fungsi |
|---|---|---|---|
| `GET` | `/api/health` | Publik | Health check status server |
| `GET` | `/api/packages` | Publik | Katalog 13 paket aktif (cached 1 jam) |
| `GET` | `/api/packages/{slug}` | Publik | Detail paket spesifik |
| `POST` | `/api/bookings` | Publik (Rate limit 60/m) | Membuat booking & generate WhatsApp link |
| `POST` | `/api/admin/login` | Publik | Otentikasi admin, mengembalikan Bearer token |
| `POST` | `/api/logout` | Auth | Revoke token Sanctum |
| `GET` | `/api/admin/dashboard/stats` | Admin Only | Statistik ringkas booking bulanan |
| `GET` | `/api/admin/bookings` | Admin Only | List booking dengan pencarian & filter |
| `GET` | `/api/admin/bookings/{id}` | Admin Only | Detail lengkap satu booking |
| `PATCH` | `/api/admin/bookings/{id}` | Admin Only | Update status, total harga, payment status |
| `DELETE` | `/api/admin/bookings/{id}` | Admin Only | Hapus data booking |
| `GET` | `/api/admin/export/excel` | Admin Only | Unduh spreadsheet Excel data booking |
| `GET` | `/api/admin/financial/dashboard` | Admin Only | KPI ringkasan keuangan, omzet, laba, outstanding |
| `GET` | `/api/admin/financial/cash-flow` | Admin Only | Data arus kas 12 bulan per tahun |
| `GET` | `/api/admin/financial/profit-by-package` | Admin Only | Analisis profit per jenis paket |
| `GET` | `/api/admin/financial/bookings` | Admin Only | Tabel overview keuangan seluruh pesanan |
| `GET` | `/api/admin/financial/booking/{id}` | Admin Only | Detail RAB, log pembayaran, dan analisis margin |
| `POST` | `/api/admin/financial/payment` | Admin Only | Catat transaksi pembayaran (DP / pelunasan) |
| `POST` | `/api/admin/financial/expense` | Admin Only | Catat item pengeluaran RAB baru |
| `PUT` | `/api/admin/financial/expense/{id}` | Admin Only | Update item pengeluaran |
| `DELETE` | `/api/admin/financial/expense/{id}` | Admin Only | Hapus item pengeluaran |

---

## 6. Struktur Direktori Proyek

```
platinum project/
├── PROJECT_CONTEXT_AI.md            <-- Dokumen konteks ini
├── platinumproject-backend/         <-- Laravel 12 Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/DashboardController.php
│   │   │   │   └── Api/AuthController.php, BookingController.php,
│   │   │   │          FinancialController.php, PackageController.php, ExportController.php
│   │   │   ├── Middleware/AdminOnly.php
│   │   │   ├── Requests/StoreBookingRequest.php, UpdateBookingRequest.php, LoginRequest.php
│   │   │   └── Resources/BookingResource.php, PackageResource.php
│   │   ├── Models/Booking.php, Expense.php, Package.php, PaymentLog.php, User.php
│   │   └── Services/BookingService.php, WhatsAppService.php
│   ├── database/
│   │   ├── migrations/ (12 skema migrasi tabel)
│   │   └── seeders/AdminSeeder.php, PackageSeeder.php
│   ├── routes/api.php, web.php
│   ├── tests/Feature/BookingApiTest.php, FinancialApiTest.php
│   ├── .env, .env.example
│   └── composer.json
└── platinumproject-frontend/        <-- Next.js 16 Frontend
    ├── src/
    │   ├── app/
    │   │   ├── page.tsx             <-- Landing page publik
    │   │   ├── paket/page.tsx       <-- Katalog paket
    │   │   ├── booking/page.tsx     <-- Form booking interaktif Leaflet
    │   │   ├── booking/success/     <-- Halaman sukses & tombol WA admin
    │   │   ├── portfolio/page.tsx   <-- Galeri dekorasi
    │   │   └── admin/
    │   │       ├── login/page.tsx   <-- Login admin
    │   │       ├── dashboard/       <-- Dashboard statistik
    │   │       ├── bookings/        <-- Manajemen tabel booking
    │   │       └── financial/       <-- Dashboard keuangan, RAB, laporan
    │   ├── components/
    │   │   ├── admin/financial/     <-- Charts, ExpenseModal, PaymentModal
    │   │   ├── maps/LocationPicker.tsx <-- OpenStreetMap Leaflet component
    │   │   └── layout/Header.tsx, Footer.tsx
    │   ├── lib/
    │   │   ├── api.ts               <-- Axios API client
    │   │   ├── auth.ts              <-- LocalStorage & auth state helper
    │   │   ├── api/financial.ts     <-- Financial Fetch API client
    │   │   └── validations/booking.ts <-- Zod schema form booking
    │   └── types/index.ts           <-- TypeScript interface model
    ├── package.json
    └── tailwind.config.ts
```

---

## 7. Cara Menjalankan Proyek Secara Lokal

1. **Jalankan Backend (Laravel)**:
   ```bash
   cd platinumproject-backend
   php artisan migrate --seed
   php artisan serve --port=8000
   ```
   Akun Default Admin:
   - Email: `admin@platinumproject.my.id`
   - Password: `PlatinumAdmin@2026`

2. **Jalankan Frontend (Next.js)**:
   ```bash
   cd platinumproject-frontend
   npm install
   npm run dev # Port 3000
   ```
