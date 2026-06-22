# Aturan Proyek — Sistem Manajemen Perpustakaan

> File ini adalah sumber kebenaran utama untuk semua agent yang bekerja di workspace ini.
> WAJIB dibaca dan dipatuhi sebelum melakukan perubahan apa pun.

---

## 1. Bahasa Komunikasi

- **WAJIB Bahasa Indonesia** untuk semua:
  - Respons ke user
  - Komentar dalam kode (PHPDoc, TSDoc, inline comment)
  - Isi README.md dan dokumentasi
  - Pesan commit git
  - Label dan pesan error di response API (kecuali key teknis)
  - Template PDF/export
- **PENGECUALIAN** — tetap Bahasa Inggris:
  - Nama variabel, fungsi, method, class, interface, enum case
  - Nama file dan folder
  - Istilah teknis pemrograman (contoh: "middleware", "migration", "seeder")
  - Key JSON response API

---

## 2. Tech Stack (TIDAK BOLEH DIGANTI)

| Layer | Teknologi | Catatan |
|-------|-----------|---------|
| Backend | Laravel 13.x (PHP 8.4+) | `declare(strict_types=1)` di semua file PHP |
| Database | SQLite | File di `database/database.sqlite` |
| ORM | Eloquent | Tanpa Repository pattern |
| Auth | Laravel Sanctum | Cookie-based SPA, BUKAN token Bearer |
| Frontend | Vue 3 + TypeScript | `<script setup lang="ts">` di semua komponen |
| UI Library | shadcn-vue (radix-vue) | Untuk semua komponen UI |
| CSS | Tailwind CSS | `darkMode: 'class'` |
| HTTP Client | Axios | Instance terpusat di `resources/js/lib/axios.ts` |
| Data Fetching | @tanstack/vue-query | SEMUA fetch data wajib lewat vue-query |
| State | Pinia | Hanya untuk auth dan theme |
| Form | vee-validate + zod | Skema zod jadi sumber type TypeScript |
| Routing | vue-router 4 | History mode, guard berbasis role |
| Package Manager | Composer (backend), **Yarn** (frontend) | **DILARANG pakai npm** |

### Library Eksternal WAJIB

**Composer:**
- `barryvdh/laravel-dompdf` — export PDF
- `rap2hpoutre/fast-excel` — import/export Excel/CSV (**BUKAN** `maatwebsite/excel`)
- `intervention/image-laravel` — resize cover image
- `laravel/sanctum` — autentikasi

**Yarn:**
- `shadcn-vue` + `radix-vue` — komponen UI
- `@tanstack/vue-query` — data fetching
- `axios` — HTTP client
- `vee-validate` + `@vee-validate/zod` + `zod` — validasi form
- `pinia` — state management
- `vue-router` — routing SPA

---

## 3. Arsitektur

### Pola Monorepo
Satu proyek Laravel yang membungkus:
- **Backend API** di prefix `/api`
- **Frontend Vue SPA** di-build Vite, disajikan lewat route catch-all `resources/views/app.blade.php`

### Alur Layer (WAJIB DIIKUTI)
```
Controller (tipis, orchestration saja)
    ↓
Service (semua business logic di sini)
    ↓
Eloquent Model (akses data langsung)
```

- Controller **HANYA** boleh memanggil Service dan mengembalikan Resource
- **DILARANG** menaruh business logic di Controller
- **DILARANG** membuat Repository class

### Struktur Folder Backend
```
app/
├── Enums/          → PHP native backed enum
├── Exports/        → Class export Excel/PDF
├── Http/
│   ├── Controllers/Api/  → Controller tipis
│   ├── Requests/         → Form Request validasi
│   └── Resources/        → API Resource transformasi JSON
├── Interfaces/     → PHP interface (Borrowable, Reportable)
├── Models/         → Eloquent model + inheritance (Item→Book/Magazine/Dvd)
├── Services/       → Business logic
└── ValueObjects/   → Value object immutable (FineAmount)
```

### Struktur Folder Frontend
```
resources/js/
├── app.ts              → Entry point
├── App.vue             → Root component
├── components/
│   ├── ui/             → Komponen shadcn-vue (hasil generate)
│   └── *.vue           → Komponen custom
├── composables/        → Hook vue-query per modul
├── lib/                → axios.ts, queryClient.ts, utils.ts
├── pages/              → Halaman per modul
├── router/             → Route + guard
├── stores/             → Pinia store (auth, theme)
└── types/              → Interface TypeScript
```

---

## 4. Aturan OOP (Kriteria Ujian)

### Inheritance
- `Item` (base) → `Book`, `Magazine`, `Dvd` (subclass)
- Single table inheritance: satu tabel `items`, dibedakan kolom `type`
- Setiap subclass WAJIB override `displayInfo()` dan `calculateLateFee()`

### Polymorphism (WAJIB TERPAKAI DI FITUR NYATA)
- Harus ada loop atas koleksi `Item` campuran yang memanggil method lewat reference tipe dasar
- Bukan cuma didefinisikan tapi tidak dipakai

### Interface
- `Borrowable`: `isAvailable()`, `markBorrowed()`, `markReturned()`
- `Reportable`: `toReportArray()`

### Simulasi Overloading
- Di `FineCalculator` lewat parameter default/opsional
- WAJIB ada PHPDoc yang menjelaskan ini simulasi

### Visibility & Readonly
- Pakai `private`/`protected`/`public` secara bermakna
- `FineAmount` value object pakai `readonly` property

---

## 5. Kontrol Struktur (Kriteria Ujian d) — WAJIB ADA

| Struktur | Lokasi | Contoh |
|----------|--------|--------|
| `if-elseif-else` | `LoanService` | Penentuan alasan denda |
| `foreach`/`for` | `ItemService`/`ReportService` | Proses import atau agregasi |
| `do-while` | `MemberService` | Generate kode anggota unik |

---

## 6. Operasi Array (Kriteria Ujian f) — WAJIB ADA

Gunakan `array_map`, `array_filter`, `array_reduce` secara nyata di `ReportService`.

---

## 7. File Storage (Kriteria Ujian g)

| Fitur | Cara |
|-------|------|
| Cover image | Upload ke `storage/app/public`, validasi image max 2MB, resize 800px |
| Export PDF | `barryvdh/laravel-dompdf`, stream download |
| Export Excel | `rap2hpoutre/fast-excel`, stream download |
| Import CSV/Excel | `rap2hpoutre/fast-excel`, validasi per baris |

---

## 8. Coding Standards

- **Backend**: PSR-12 (Laravel Pint), `declare(strict_types=1)`, type hint penuh
- **Frontend**: ESLint + Prettier, Vue 3 Style Guide
- **PHP**: PHPDoc wajib di setiap class dan public method
- **TypeScript**: `strict: true`, TSDoc wajib di composable dan fungsi util

---

## 9. Larangan Keras

- ❌ Kode placeholder, TODO, atau "implement later"
- ❌ Mock data permanen di kode produksi
- ❌ Repository pattern
- ❌ `maatwebsite/excel` → pakai `rap2hpoutre/fast-excel`
- ❌ `npm` → pakai `yarn`
- ❌ Fetch manual di komponen Vue → pakai vue-query
- ❌ String mentah untuk perbandingan enum → pakai enum case
- ❌ Business logic di Controller
- ❌ Kode yang tidak terpakai hanya untuk "memenuhi syarat"
