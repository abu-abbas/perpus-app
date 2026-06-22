---
name: perpus-app-development
description: >
  Skill untuk membangun Sistem Manajemen Perpustakaan (Laravel 13 + Vue 3 SPA).
  Aktifkan skill ini saat mengerjakan fitur, memperbaiki bug, atau menambahkan kode baru
  di proyek perpus-app. Skill ini berisi referensi lengkap skema database, enum, endpoint API,
  desain OOP, dan kriteria ujian yang harus dipenuhi.
---

# Skill: Pengembangan Perpus App

## Kapan Menggunakan Skill Ini

Gunakan skill ini setiap kali:
- Membuat atau memodifikasi kode di proyek perpus-app
- Menambah fitur baru
- Memperbaiki bug
- Menulis test
- Membuat dokumentasi

**WAJIB baca file-file berikut sebelum mulai bekerja:**
1. `.agents/rules.md` — aturan proyek (bahasa, arsitektur, larangan)
2. `.agents/workflow.md` — langkah kerja per fase
3. `.agents/skills/perpus-app-development/references/error-handling.md` — strategi error handling

---

## Skema Database

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigIncrements | PK |
| name | string | |
| email | string | unique |
| password | string | |
| role | string | Cast ke `App\Enums\UserRole` (`admin`, `pustakawan`) |
| timestamps | timestamps | |

### Tabel `categories`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigIncrements | PK |
| name | string | |
| description | string | nullable |
| timestamps | timestamps | |

### Tabel `items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigIncrements | PK |
| category_id | foreignId | FK → categories.id |
| type | string | Cast ke `App\Enums\ItemType` (`book`, `magazine`, `dvd`) |
| title | string | |
| author | string | |
| publisher | string | nullable |
| year | integer | |
| code | string | unique — ISBN untuk buku, kode unik untuk lainnya |
| total_stock | integer | |
| available_stock | integer | |
| cover_image | string | nullable — path file di storage, BUKAN binary |
| attributes | json | nullable — atribut spesifik: `pages`, `edition_number`, `duration_minutes` |
| timestamps | timestamps | |

### Tabel `members`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigIncrements | PK |
| member_number | string | unique |
| full_name | string | |
| identity_number | string | |
| phone | string | nullable |
| address | text | nullable |
| join_date | date | |
| status | string | Cast ke `App\Enums\MemberStatus` (`active`, `inactive`, `suspended`) |
| timestamps | timestamps | |

### Tabel `loans`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigIncrements | PK |
| member_id | foreignId | FK → members.id |
| item_id | foreignId | FK → items.id |
| librarian_id | foreignId | FK → users.id |
| loan_date | date | |
| due_date | date | |
| return_date | date | nullable |
| status | string | Cast ke `App\Enums\LoanStatus` (`borrowed`, `returned`, `overdue`, `lost`) |
| timestamps | timestamps | |

### Tabel `fines`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigIncrements | PK |
| loan_id | foreignId | FK → loans.id |
| amount | decimal(10,2) | |
| reason | string | Cast ke `App\Enums\FineReason` (`late`, `damaged`, `lost`) |
| paid_status | string | Cast ke `App\Enums\PaymentStatus` (`unpaid`, `paid`) |
| paid_date | date | nullable |
| timestamps | timestamps | |

---

## Enum (PHP Native Backed Enum)

```php
// App\Enums\ItemType (string)
Book = 'book', Magazine = 'magazine', Dvd = 'dvd'

// App\Enums\UserRole (string)
Admin = 'admin', Pustakawan = 'pustakawan'

// App\Enums\MemberStatus (string)
Active = 'active', Inactive = 'inactive', Suspended = 'suspended'

// App\Enums\LoanStatus (string)
Borrowed = 'borrowed', Returned = 'returned', Overdue = 'overdue', Lost = 'lost'

// App\Enums\FineReason (string)
Late = 'late', Damaged = 'damaged', Lost = 'lost'

// App\Enums\PaymentStatus (string)
Unpaid = 'unpaid', Paid = 'paid'
```

Semua kolom enum di database disimpan sebagai `string` (SQLite tidak support ENUM), tapi **WAJIB** di-cast ke enum class lewat `protected function casts()` di Eloquent model.

---

## Endpoint API

| Method | Path | Deskripsi |
|--------|------|-----------|
| POST | `/api/login` | Login admin/pustakawan |
| POST | `/api/logout` | Logout |
| GET | `/api/user` | Data user yang sedang login |
| GET | `/api/dashboard/summary` | Ringkasan statistik dashboard |
| GET | `/api/categories` | Daftar kategori |
| POST | `/api/categories` | Buat kategori baru |
| PUT | `/api/categories/{id}` | Update kategori |
| DELETE | `/api/categories/{id}` | Hapus kategori (admin only) |
| GET | `/api/items` | Daftar item (filter: type, category, search) |
| POST | `/api/items` | Buat item baru (dengan upload cover) |
| PUT | `/api/items/{id}` | Update item |
| DELETE | `/api/items/{id}` | Hapus item (admin only) |
| POST | `/api/items/import` | Import massal dari CSV/Excel |
| GET | `/api/items/export/{format}` | Export PDF atau Excel |
| GET | `/api/members` | Daftar anggota |
| POST | `/api/members` | Buat anggota baru |
| PUT | `/api/members/{id}` | Update anggota |
| DELETE | `/api/members/{id}` | Hapus anggota (admin only) |
| GET | `/api/loans` | Daftar peminjaman |
| POST | `/api/loans` | Buat peminjaman baru |
| POST | `/api/loans/{id}/return` | Proses pengembalian |
| GET | `/api/fines` | Daftar denda |
| POST | `/api/fines/{id}/pay` | Tandai denda lunas |

---

## Desain OOP

### Hierarki Model Item (Inheritance)

```
Item (base class, tabel: items)
├── Book   → lateFeePerDay: 500, displayInfo() tampilkan pages
├── Magazine → lateFeePerDay: 1000, displayInfo() tampilkan edition_number
└── Dvd    → lateFeePerDay: 2000, displayInfo() tampilkan duration_minutes
```

### Interface

```php
// App\Interfaces\Borrowable
interface Borrowable {
    public function isAvailable(): bool;
    public function markBorrowed(): void;
    public function markReturned(): void;
}
// Diimplementasi oleh: Item (berlaku juga untuk Book/Magazine/Dvd)

// App\Interfaces\Reportable
interface Reportable {
    public function toReportArray(): array;
}
// Diimplementasi oleh: Item, Loan, Member
```

### Value Object

```php
// App\ValueObjects\FineAmount
// Immutable dengan readonly properties
class FineAmount {
    public function __construct(
        public readonly float $amount,
        public readonly FineReason $reason,
    ) {}
}
```

### ItemFactory

```php
// App\Services\ItemFactory
// Mengembalikan instance Book/Magazine/Dvd sesuai ItemType
public function make(ItemType $type, array $data): Item
```

### FineCalculator (Simulasi Overloading)

```php
// App\Services\FineCalculator
// Parameter default/opsional sebagai simulasi overloading
public function calculate(
    Loan $loan,
    ?float $customRatePerDay = null,
    ?int $maxDays = null
): FineAmount
```

---

## Daftar Service (Business Logic Layer)

| Service | Tanggung Jawab |
|---------|---------------|
| `ItemFactory` | Factory method buat instance Book/Magazine/Dvd |
| `ItemService` | CRUD item, upload cover, import file |
| `LoanService` | Proses pinjam & kembali, validasi stok |
| `FineCalculator` | Hitung denda (simulasi overloading) |
| `FineService` | CRUD denda, tandai lunas |
| `ReportService` | Dashboard summary, agregasi laporan |
| `ImportService` | Import item massal dari CSV/Excel |
| `MemberService` | CRUD anggota, generate kode unik |

---

## Fitur Aplikasi

1. Login & logout (admin dan pustakawan) via Sanctum
2. CRUD kategori
3. CRUD item (Book/Magazine/Dvd) dengan upload cover image
4. CRUD anggota
5. Proses peminjaman — validasi stok, kurangi available_stock
6. Proses pengembalian — hitung denda otomatis jika terlambat
7. Manajemen denda — daftar, tandai lunas
8. Dashboard ringkasan statistik
9. Export laporan ke PDF dan Excel
10. Import data item massal dari CSV/Excel
11. Role-based UI — admin bisa hapus, pustakawan hanya operasional
12. Dark/Light/System theme toggle

---

## Akun Demo (Hasil Seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@perpus.test` | `password` |
| Pustakawan | `pustakawan@perpus.test` | `password` |
