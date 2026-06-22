# Sistem Manajemen Perpustakaan — Rencana Implementasi

## Ringkasan

Membangun aplikasi Sistem Manajemen Perpustakaan secara full-stack menggunakan Laravel 13.x (PHP 8.4+) sebagai monorepo dengan frontend Vue 3 + TypeScript SPA. Aplikasi ini harus memenuhi seluruh **12 kriteria ujian (a–l)** dengan kode yang nyata dan berfungsi — tanpa placeholder.

**Workspace**: `./` (saat ini kosong, repo git baru)

### Lingkungan yang Sudah Diverifikasi
| Alat | Versi |
|------|-------|
| PHP | 8.4.16 |
| Composer | 2.9.3 |
| Node | 25.9.0 |
| Yarn | tersedia di `/opt/homebrew/bin/yarn` |

### Aturan Komunikasi (WAJIB)

> [!IMPORTANT]
> **Semua output, komentar kode (PHPDoc/TSDoc), pesan commit, isi README, dan komunikasi dengan user WAJIB menggunakan Bahasa Indonesia.** Ini berlaku sepanjang proyek, termasuk:
> - Deskripsi method/class di PHPDoc dan TSDoc
> - Pesan error dan response API (kecuali key teknis)
> - Komentar dalam kode
> - Dokumentasi README.md
> - Semua respons ke user
>
> Pengecualian: nama variabel, nama class, nama method, dan istilah teknis pemrograman tetap dalam Bahasa Inggris sesuai konvensi.

---

## Perlu Review dari User

> [!IMPORTANT]
> **Versi Laravel**: Spesifikasi menyebutkan "Laravel 13.x". Laravel 13 dirilis Maret 2026 dan membutuhkan minimal PHP 8.3+. PHP 8.4.16 kita sudah kompatibel. Saya akan pakai `composer create-project laravel/laravel ./` yang otomatis mengambil versi stabil terbaru (13.x).

> [!IMPORTANT]
> **Versi Yarn**: Yarn tersedia lewat Homebrew. Spesifikasi mewajibkan `yarn` bukan `npm`. Semua perintah akan pakai `yarn`.

> [!IMPORTANT]
> **SQLite untuk Kolom Enum**: SQLite tidak mendukung tipe kolom `ENUM` secara native. Migration akan pakai kolom `string` dengan constraint validasi, lalu Eloquent yang melakukan cast ke PHP backed enum. Ini adalah praktik standar Laravel untuk SQLite.

> [!WARNING]
> **intervention/image**: Spesifikasi menyebutkan `intervention/image`, tapi package Laravel modern-nya adalah `intervention/image-laravel` (yang sudah include `intervention/image` sebagai dependency). Saya akan install `intervention/image-laravel` supaya service provider-nya otomatis terdaftar.

---

## Pertanyaan Terbuka

> [!IMPORTANT]
> **Template PDF Export**: Template view PDF laporan akan ditulis dalam **Bahasa Indonesia** (judul kolom, label, keterangan). Kalau mau pakai Bahasa Inggris, kasih tahu ya.

> [!NOTE]
> **Dimensi Resize Cover Image**: Cover image akan di-resize ke **maksimal lebar 800px** dengan menjaga rasio aspek. Kalau mau ukuran lain, kasih tahu ya.

---

## Rencana Perubahan

Proyek akan bangun dalam **8 fase**, masing-masing menghasilkan kode yang berfungsi dan bisa ditest.

---

### Fase 1: Scaffold Proyek & Konfigurasi

Membuat proyek Laravel 13, install semua dependency composer/yarn, dan setup konfigurasi dasar.

#### Langkah-langkah:
1. `composer create-project laravel/laravel ./` — scaffold Laravel 13
2. Konfigurasi `.env` dan `.env.example` untuk SQLite (`DB_CONNECTION=sqlite`)
3. Buat file `database/database.sqlite`
4. Install package Composer:
   - `laravel/sanctum` (kemungkinan sudah termasuk)
   - `barryvdh/laravel-dompdf`
   - `rap2hpoutre/fast-excel`
   - `intervention/image-laravel`
5. Install package Yarn:
   - `vue` `vue-router` `pinia` `@tanstack/vue-query` `axios` `vee-validate` `@vee-validate/zod` `zod`
   - Dev: `@vitejs/plugin-vue` `typescript` `vue-tsc` `@vue/tsconfig`
6. Inisialisasi shadcn-vue: `npx shadcn-vue@latest init`
7. Tambah komponen shadcn-vue: `button`, `input`, `dialog`, `table`, `dropdown-menu`, `switch`, `card`, `badge`, `toast`/`sonner`, `select`, `label`, `textarea`, `separator`, `avatar`, `sheet`, `popover`, `command`, `pagination`
8. Konfigurasi:
   - `tailwind.config.ts` dengan `darkMode: 'class'`
   - `tsconfig.json` dengan `strict: true`, `noImplicitAny: true`, `strictNullChecks: true`
   - `vite.config.ts` dengan `laravel-vite-plugin` + `@vitejs/plugin-vue`
   - `resources/css/app.css` dengan CSS variables shadcn-vue (light + dark)
   - Sanctum SPA config di `config/sanctum.php` dan `config/cors.php`

#### File yang dibuat/dimodifikasi:
- **[BARU]** `.env.example`
- **[MODIFIKASI]** `vite.config.ts`
- **[MODIFIKASI]** `tailwind.config.ts`
- **[MODIFIKASI]** `tsconfig.json`
- **[MODIFIKASI]** `resources/css/app.css`
- **[BARU]** `resources/views/app.blade.php` — template Blade catch-all untuk SPA
- **[BARU]** `components.json` — konfigurasi shadcn-vue

---

### Fase 2: Database — Enum, Migration, Model, Interface, Value Object

Membangun seluruh lapisan data: PHP enum, model Eloquent dengan inheritance, interface, value object, dan **hierarki exception untuk error handling**.

#### Enum (`app/Enums/`)

| File | Isi |
|------|-----|
| **[BARU]** `ItemType.php` | `Book = 'book'`, `Magazine = 'magazine'`, `Dvd = 'dvd'` |
| **[BARU]** `UserRole.php` | `Admin = 'admin'`, `Pustakawan = 'pustakawan'` |
| **[BARU]** `MemberStatus.php` | `Active = 'active'`, `Inactive = 'inactive'`, `Suspended = 'suspended'` |
| **[BARU]** `LoanStatus.php` | `Borrowed = 'borrowed'`, `Returned = 'returned'`, `Overdue = 'overdue'`, `Lost = 'lost'` |
| **[BARU]** `FineReason.php` | `Late = 'late'`, `Damaged = 'damaged'`, `Lost = 'lost'` |
| **[BARU]** `PaymentStatus.php` | `Unpaid = 'unpaid'`, `Paid = 'paid'` |

---

#### Interface (`app/Interfaces/`)

**[BARU]** `Borrowable.php`
```php
interface Borrowable {
    public function isAvailable(): bool;
    public function markBorrowed(): void;
    public function markReturned(): void;
}
```

**[BARU]** `Reportable.php`
```php
interface Reportable {
    public function toReportArray(): array;
}
```

---

#### Value Object (`app/ValueObjects/`)

**[BARU]** `FineAmount.php`
- Immutable dengan properti `readonly`: `float $amount`, `FineReason $reason`
- Menunjukkan PHP 8.1+ readonly + visibilitas yang tepat (poin h)

---

#### Migration (`database/migrations/`)

6 file migration berurutan:
1. `create_users_table` — sudah ada, modifikasi untuk tambah kolom `role` (string)
2. `create_categories_table`
3. `create_items_table` — dengan `type` (string), `attributes` (json), `cover_image`, foreign key ke categories
4. `create_members_table` — dengan `member_number` (unique), `status` (string)
5. `create_loans_table` — foreign key ke members, items, users
6. `create_fines_table` — foreign key ke loans, `amount` decimal, `reason` string, `paid_status` string

> [!NOTE]
> Semua kolom "enum" pakai tipe `string` di SQLite, di-cast ke PHP backed enum di model.

---

#### Exception — Error Handling (`app/Exceptions/`)

Strategi error handling profesional — pisah antara **error bisnis** dan **error sistem**.
Detail lengkap ada di `.agents/skills/perpus-app-development/references/error-handling.md`.

**[BARU]** `BusinessException.php` — Base class untuk semua pelanggaran aturan bisnis
- Response HTTP 422, log level INFO
- Format: `{ error: true, type: 'business', code: '...', message: '...', context: {...} }`

**[BARU]** Exception bisnis spesifik:
| Exception | Kode Error | Kapan Dilempar |
|-----------|-----------|----------------|
| `InsufficientStockException` | `ITEM_STOK_HABIS` | Stok = 0 saat proses pinjam |
| `ItemNotAvailableException` | `ITEM_TIDAK_TERSEDIA` | Item tidak ditemukan saat pinjam |
| `MemberNotActiveException` | `ANGGOTA_TIDAK_AKTIF` | Status anggota bukan `active` |
| `MaxLoanExceededException` | `BATAS_PINJAM_TERCAPAI` | Anggota sudah punya >= 3 pinjaman aktif |
| `LoanAlreadyReturnedException` | `SUDAH_DIKEMBALIKAN` | Proses return untuk pinjaman yang sudah dikembalikan |
| `FineAlreadyPaidException` | `DENDA_SUDAH_LUNAS` | Bayar denda yang sudah lunas |
| `FineUnpaidException` | `DENDA_BELUM_LUNAS` | Pinjam tapi masih ada denda belum lunas |

**[BARU]** `AppException.php` — Base class untuk semua error teknis/sistem
- Response HTTP 500, log level ERROR
- Pesan generik ke user, detail teknis hanya di log

**[BARU]** Exception sistem spesifik:
| Exception | Kode Error | Kapan Dilempar |
|-----------|-----------|----------------|
| `FileImportException` | `IMPORT_GAGAL` | File CSV/Excel rusak atau format salah |
| `ExportFailedException` | `EXPORT_GAGAL` | Gagal generate PDF/Excel |
| `ImageProcessingException` | `GAMBAR_GAGAL_DIPROSES` | Gagal resize/simpan cover image |

---

#### Model (`app/Models/`)

**[MODIFIKASI]** `User.php`
- Tambah cast `role` ke enum `UserRole`
- Tambah relasi: `loans()` hasMany

**[BARU]** `Category.php`
- Relasi: `items()` hasMany
- Implements `Reportable`

**[BARU]** `Item.php` — **Class dasar (parent)**
- Cast: `type` → `ItemType`, `attributes` → `array`
- `protected float $lateFeePerDay = 1000.0`
- Implements `Borrowable`, `Reportable`
- Method: `displayInfo(): array`, `calculateLateFee(int $daysLate): float`, `isAvailable()`, `markBorrowed()`, `markReturned()`, `toReportArray()`
- Relasi: `category()`, `loans()`
- Override `newFromBuilder()` untuk instansiasi subclass yang benar lewat `ItemFactory`
- Accessor: `coverImageUrl` lewat `Attribute::make`

**[BARU]** `Book.php` extends Item
- `protected float $lateFeePerDay = 500.0` (lebih rendah)
- Override `displayInfo()` — menampilkan `pages` dari attributes
- Override `calculateLateFee()` — pakai tarif lebih rendah

**[BARU]** `Magazine.php` extends Item
- `protected float $lateFeePerDay = 1000.0` (menengah)
- Override `displayInfo()` — menampilkan `edition_number`
- Override `calculateLateFee()`

**[BARU]** `Dvd.php` extends Item
- `protected float $lateFeePerDay = 2000.0` (paling tinggi)
- Override `displayInfo()` — menampilkan `duration_minutes`
- Override `calculateLateFee()`

**[BARU]** `Member.php`
- Cast: `status` → `MemberStatus`, `join_date` → `date`
- Implements `Reportable`
- Relasi: `loans()`

**[BARU]** `Loan.php`
- Cast: `status` → `LoanStatus`, kolom tanggal di-cast
- Implements `Reportable`
- Accessor: `isOverdue` lewat `Attribute::make` (properti turunan — poin h)
- Relasi: `member()`, `item()`, `librarian()`, `fine()`

**[BARU]** `Fine.php`
- Cast: `reason` → `FineReason`, `paid_status` → `PaymentStatus`
- Relasi: `loan()`

---

#### Seeder (`database/seeders/`)

| File | Isi |
|------|-----|
| **[BARU]** `DatabaseSeeder.php` | Memanggil semua sub-seeder secara berurutan |
| **[BARU]** `UserSeeder.php` | Admin: `admin@perpus.test` / `password` / role=admin; Pustakawan: `pustakawan@perpus.test` / `password` / role=pustakawan |
| **[BARU]** `CategorySeeder.php` | 5 kategori: Fiksi, Non-Fiksi, Sains, Sejarah, Teknologi |
| **[BARU]** `ItemSeeder.php` | 10+ item campuran Book/Magazine/Dvd, data realistis |
| **[BARU]** `MemberSeeder.php` | 5 anggota contoh dengan status bervariasi |
| **[BARU]** `LoanSeeder.php` | Contoh peminjaman: ada yang terlambat, sudah dikembalikan, dan ada dendanya |

---

### Fase 3: Service & Business Logic

Semua business logic di class Service. Tanpa Repository pattern.
Service melempar `BusinessException` untuk pelanggaran aturan bisnis dan `AppException` untuk kegagalan teknis — **TIDAK** menangkap exception sendiri, biarkan naik ke Controller/Handler.

**[BARU]** `app/Services/ItemFactory.php`
- `make(ItemType $type, array $data): Item` — factory method yang mengembalikan Book/Magazine/Dvd
- Dipakai di `newFromBuilder()` model Item dan di `ItemService::create()`

**[BARU]** `app/Services/ItemService.php`
- `list(array $filters)` — paginasi, bisa filter berdasarkan type/category/search
- `create(array $data): Item` — pakai ItemFactory
- `update(Item $item, array $data): Item`
- `delete(Item $item): void`
- `uploadCoverImage(UploadedFile $file): string` — resize pakai intervention/image, simpan ke storage
- `importFromFile(UploadedFile $file): array` — parsing pakai fast-excel, validasi baris, return hasil/error
- Mengandung loop **foreach** untuk proses import (poin d)

**[BARU]** `app/Services/LoanService.php`
- `borrow(Member $member, Item $item, User $librarian): Loan` — panggil `$item->markBorrowed()` (interface poin h)
- `return(Loan $loan): Loan` — panggil `$item->markReturned()`, pakai FineCalculator kalau terlambat
- Mengandung blok **if-elseif-else** untuk menentukan alasan denda berdasarkan jumlah hari terlambat (poin d)

**[BARU]** `app/Services/FineCalculator.php`
- **Simulasi overloading** (poin h) lewat parameter default/opsional:
  ```php
  /**
   * Simulasi method overloading (PHP tidak mendukung overloading bawaan seperti Java/C#).
   * Method ini berperilaku berbeda tergantung parameter yang diisi:
   * - calculate($loan): hitung berdasarkan tarif default subclass item
   * - calculate($loan, $customRate): hitung dengan tarif custom per hari
   * - calculate($loan, $customRate, $maxDays): hitung dengan tarif custom dan batas hari maksimum
   */
  public function calculate(Loan $loan, ?float $customRatePerDay = null, ?int $maxDays = null): FineAmount
  ```

**[BARU]** `app/Services/FineService.php`
- `createFine(Loan $loan, FineAmount $fineAmount): Fine`
- `markPaid(Fine $fine): Fine`
- `listFines(array $filters)` — daftar denda terpaginasi

**[BARU]** `app/Services/ReportService.php`
- `getDashboardSummary(): array` — total untuk dashboard
- `getLoanReport(array $filters): array` — laporan teragregasi
- Mengandung operasi **array_map, array_filter, array_reduce** untuk agregasi (poin f)
- Mengandung **demo polimorfisme**: loop atas koleksi Item campuran dan memanggil `displayInfo()` serta `calculateLateFee()` (poin h)

**[BARU]** `app/Services/ImportService.php`
- `importItems(UploadedFile $file): array` — pakai fast-excel
- Return `['success' => int, 'failed' => int, 'errors' => array]`

**[BARU]** `app/Services/MemberService.php`
- `generateMemberNumber(): string` — mengandung loop **do-while** untuk generate kode unik (poin d)
- `create(array $data): Member`
- `update(Member $member, array $data): Member`

---

### Fase 4: Lapisan HTTP — Controller, Request, Resource, Route

#### Form Request (`app/Http/Requests/`)

| File | Kegunaan |
|------|----------|
| `LoginRequest.php` | Validasi email + password |
| `StoreCategoryRequest.php` | Validasi name (required, unique) |
| `UpdateCategoryRequest.php` | Validasi name (unique kecuali diri sendiri) |
| `StoreItemRequest.php` | Validasi field item + cover_image (image, max:2048) |
| `UpdateItemRequest.php` | Sama dengan partial rules |
| `ImportItemsRequest.php` | Validasi file (xlsx, csv, max:5120) |
| `StoreMemberRequest.php` | Validasi field anggota |
| `UpdateMemberRequest.php` | Sama dengan partial rules |
| `StoreLoanRequest.php` | Validasi member_id, item_id |
| `PayFineRequest.php` | (kosong, hanya cek autentikasi) |

#### API Resource (`app/Http/Resources/`)

| File | Kegunaan |
|------|----------|
| `UserResource.php` | Data user (tanpa password) |
| `CategoryResource.php` | Kategori dengan items_count |
| `ItemResource.php` | Item dengan `displayInfo()` yang di-merge, cover_image_url |
| `MemberResource.php` | Data anggota |
| `LoanResource.php` | Peminjaman dengan nested member, item, pustakawan |
| `FineResource.php` | Denda dengan nested loan |
| `DashboardResource.php` | Statistik ringkasan |

#### Controller (`app/Http/Controllers/Api/`)

| File | Endpoint |
|------|----------|
| `AuthController.php` | `POST /login`, `POST /logout`, `GET /user` |
| `CategoryController.php` | CRUD `/categories` |
| `ItemController.php` | CRUD `/items`, `POST /items/import`, `GET /items/export/{format}` |
| `MemberController.php` | CRUD `/members` |
| `LoanController.php` | `GET/POST /loans`, `POST /loans/{id}/return` |
| `FineController.php` | `GET /fines`, `POST /fines/{id}/pay` |
| `DashboardController.php` | `GET /dashboard/summary` |

#### Route

**[MODIFIKASI]** `routes/api.php`
- Publik: `POST /login`
- Dilindungi (middleware Sanctum): semua route lainnya
- Middleware khusus admin untuk operasi destruktif (hapus kategori/item/anggota)

**[MODIFIKASI]** `routes/web.php`
- Route catch-all `/{any}` → `app.blade.php`

---

### Fase 5: Class Export

**[BARU]** `app/Exports/ItemsExport.php`
- Export Excel lewat `fast-excel`: koleksi item → `(new FastExcel($items))->download('items.xlsx')`
- Export PDF lewat `dompdf`: render blade view → stream PDF

**[BARU]** `app/Exports/LoanReportExport.php`
- Export ganda (Excel/PDF) untuk laporan peminjaman
- Pakai `ReportService` untuk agregasi data

**[BARU]** `resources/views/exports/items-pdf.blade.php`
- Template PDF untuk daftar item

**[BARU]** `resources/views/exports/loan-report-pdf.blade.php`
- Template PDF untuk laporan peminjaman

---

### Fase 6: Frontend SPA

#### File Fondasi

**[BARU]** `resources/js/app.ts`
- Mount aplikasi Vue dengan router, Pinia, VueQuery

**[BARU]** `resources/js/App.vue`
- Komponen root dengan `<RouterView />`

**[BARU]** `resources/js/lib/axios.ts`
- Instance Axios terpusat: `baseURL: '/api'`, `withCredentials: true`
- Interceptor error global yang membedakan tipe error:
  - **401** → sesi habis, redirect ke login, toast info
  - **403** → tidak punya akses, toast error
  - **422 + type=business** → pelanggaran aturan bisnis, toast warning (kuning)
  - **422 + errors** → validasi form, biarkan vee-validate handle
  - **500** → error sistem, toast error dengan pesan generik
- Lihat detail di `.agents/skills/perpus-app-development/references/error-handling.md`

**[BARU]** `resources/js/lib/queryClient.ts`
- Konfigurasi client TanStack VueQuery

**[BARU]** `resources/js/lib/utils.ts`
- Fungsi `cn()` untuk penggabungan class shadcn-vue

---

#### Tipe TypeScript (`resources/js/types/`)

**[BARU]** `index.ts`
```typescript
// Tipe union enum yang sesuai dengan PHP enum
type ItemType = 'book' | 'magazine' | 'dvd'
type UserRole = 'admin' | 'pustakawan'
type MemberStatus = 'active' | 'inactive' | 'suspended'
type LoanStatus = 'borrowed' | 'returned' | 'overdue' | 'lost'
type FineReason = 'late' | 'damaged' | 'lost'
type PaymentStatus = 'unpaid' | 'paid'

// Tipe error response dari backend
interface BusinessError { error: true; type: 'business'; code: string; message: string; context: Record<string, unknown> }
interface SystemError { error: true; type: 'system'; code: string; message: string }
interface ValidationError { message: string; errors: Record<string, string[]> }
type ApiError = BusinessError | SystemError | ValidationError

interface ApiResponse<T> { data: T; message?: string }
interface PaginatedResponse<T> { data: T[]; meta: PaginationMeta }
interface Item { id: number; type: ItemType; title: string; /* ... */ display_info: Record<string, string> }
interface Loan { id: number; status: LoanStatus; is_overdue: boolean; /* ... */ }
// ... semua interface lainnya
```

---

#### Store (`resources/js/stores/`)

**[BARU]** `auth.ts`
- State: `user`, `isAuthenticated`, `isAdmin`, `isPustakawan`
- Action: `login()`, `logout()`, `fetchUser()`

**[BARU]** `theme.ts`
- State: `theme: 'light' | 'dark' | 'system'`
- Simpan ke localStorage
- Terapkan class `dark` ke `document.documentElement`
- Default ikuti `prefers-color-scheme` sistem jika user belum pernah memilih

---

#### Router (`resources/js/router/`)

**[BARU]** `index.ts`
- Route: `/login`, `/dashboard`, `/categories`, `/items`, `/members`, `/loans`, `/fines`, `/reports`
- Guard `beforeEach`: cek autentikasi, redirect ke login kalau belum login
- Meta berbasis role: `meta: { roles: ['admin'] }` untuk halaman khusus admin

---

#### Composable (`resources/js/composables/`)

Semua pakai `@tanstack/vue-query`. Setiap composable mengekspor hook query dan hook mutasi.

| File | Hook yang Diekspor |
|------|-----|
| `useAuth.ts` | `useLogin()`, `useLogout()`, `useCurrentUser()` |
| `useCategories.ts` | `useCategories()`, `useCreateCategory()`, `useUpdateCategory()`, `useDeleteCategory()` |
| `useItems.ts` | `useItems(filters)`, `useCreateItem()`, `useUpdateItem()`, `useDeleteItem()`, `useImportItems()`, `useExportItems()` |
| `useMembers.ts` | `useMembers()`, `useCreateMember()`, `useUpdateMember()`, `useDeleteMember()` |
| `useLoans.ts` | `useLoans()`, `useCreateLoan()`, `useReturnLoan()` |
| `useFines.ts` | `useFines()`, `usePayFine()` |
| `useDashboard.ts` | `useDashboardSummary()` |

---

#### Halaman (`resources/js/pages/`)

Setiap halaman adalah SFC Vue lengkap dengan `<script setup lang="ts">`.

| File | Deskripsi |
|------|-----------|
| `LoginPage.vue` | Form login dengan vee-validate + skema zod |
| `DashboardPage.vue` | 4 kartu statistik + aktivitas terbaru |
| `CategoriesPage.vue` | DataTable + Dialog Create/Edit |
| `ItemsPage.vue` | DataTable dengan filter tab type/category + Dialog Create/Edit dengan upload cover |
| `MembersPage.vue` | DataTable + Dialog Create/Edit |
| `LoansPage.vue` | DataTable + Dialog Peminjaman (pilih anggota + item) + Tombol aksi Pengembalian |
| `FinesPage.vue` | DataTable + Tombol Bayar |
| `ReportsPage.vue` | Filter rentang tanggal + tabel ringkasan + tombol export (PDF/Excel) |

---

#### Komponen Custom (`resources/js/components/`)

| File | Deskripsi |
|------|-----------|
| `AppLayout.vue` | Layout utama dengan sidebar + topbar |
| `AppSidebar.vue` | Link navigasi, visibilitas berbasis role |
| `ThemeToggle.vue` | DropdownMenu shadcn-vue dengan ikon Sun/Moon/Monitor |
| `DataTable.vue` | Wrapper reusable untuk Table shadcn-vue dengan search, paginasi |
| `FormDialog.vue` | Wrapper dialog reusable untuk form create/edit |
| `StatCard.vue` | Komponen kartu statistik dashboard |
| `FileUpload.vue` | Komponen upload file drag-and-drop |

---

### Fase 7: Unit Test & Feature Test

#### Unit Test (`tests/Unit/`)

| File | Yang Ditest | Poin Ujian |
|------|-------------|------------|
| `ItemPolymorphismTest.php` | `displayInfo()` menghasilkan hasil berbeda untuk Book/Magazine/Dvd | h (polimorfisme) |
| `FineCalculatorTest.php` | Simulasi overloading — 3 variasi pemanggilan menghasilkan hasil benar | h (overloading) |
| `BorrowableInterfaceTest.php` | Kontrak `isAvailable()`, `markBorrowed()`, `markReturned()` | h (interface) |
| `ReportableInterfaceTest.php` | Semua model yang implement menghasilkan `toReportArray()` yang valid | h (interface) |
| `EnumCastingTest.php` | Setiap model benar meng-cast string ke enum | d (tipe data) |
| `FineAmountValueObjectTest.php` | Immutable, properti readonly | h (properti) |
| `StockManagementTest.php` | `available_stock` berkurang saat pinjam, bertambah saat kembali | e (method/logic) |

#### Feature Test (`tests/Feature/`)

| File | Yang Ditest |
|------|-------------|
| `AuthTest.php` | Alur login/logout lewat Sanctum |
| `CategoryApiTest.php` | Endpoint CRUD kategori |
| `ItemApiTest.php` | Endpoint CRUD + import/export |
| `LoanApiTest.php` | Alur pinjam + kembali termasuk pembuatan denda |
| `FineApiTest.php` | Daftar + bayar denda |
| `ErrorHandlingTest.php` | Business error → 422, system error → 500, format response konsisten |

---

### Fase 8: Dokumentasi & Finishing

**[BARU]** `README.md`

Isi:
1. Deskripsi proyek
2. Tech stack (Laravel 13.x, PHP 8.4+, Vue 3, TypeScript, yarn)
3. Langkah instalasi:
   ```bash
   composer install
   yarn install
   cp .env.example .env
   php artisan key:generate
   touch database/database.sqlite
   php artisan migrate --seed
   php artisan storage:link
   yarn dev
   ```
4. Tabel akun demo
5. **Compliance matrix** — tabel pemetaan poin a–l ke file/class spesifik

---

## Tabel Compliance Matrix (Preview)

| Poin | Kriteria | File/Class |
|------|----------|------------|
| a | Sesuai rancangan | Semua migration sesuai `database_schema`, semua model sesuai `oop_domain_design` |
| b | Coding guidelines | PSR-12 lewat Pint, Vue Style Guide lewat ESLint |
| c | Interface I/O | Semua `*Page.vue` dengan form + DataTable |
| d | Tipe data, kontrol | `App\Enums\*`, if-elseif di `LoanService`, foreach di `ItemService`, do-while di `MemberService` |
| e | Prosedur/fungsi/method | Semua class `App\Services\*` |
| f | Array | `ReportService` — `array_map`, `array_filter`, `array_reduce` |
| g | Simpan/baca file | Upload cover image, export PDF/Excel, import CSV/Excel |
| h | OOP (inheritance, polimorfisme, dll.) | `Item→Book/Magazine/Dvd`, simulasi overloading `FineCalculator`, interface `Borrowable`/`Reportable`, `FineAmount` readonly |
| i | Namespace/package | `App\Models`, `App\Enums`, `App\Interfaces`, `App\Services`, `App\Exports`, `App\ValueObjects`, `App\Http\Controllers\Api` |
| j | Library eksternal | Composer: dompdf, fast-excel, intervention/image. Yarn: shadcn-vue, tanstack-query, axios, vee-validate, zod |
| k | Basis data | SQLite + Eloquent ORM, 6 tabel dengan migration + seeder |
| l | Dokumentasi | PHPDoc di semua class/method, TSDoc di composable, README dengan compliance matrix |

---

## Rencana Verifikasi

### Test Otomatis
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Verifikasi Manual
1. Jalankan `php artisan migrate:fresh --seed` — pastikan semua tabel terbuat, data seed ada
2. Jalankan `yarn dev` — pastikan SPA bisa dibuka di `http://localhost:8000`
3. Login sebagai admin dan pustakawan — pastikan UI berbasis role berfungsi
4. Buat/edit/hapus item dengan upload cover image — pastikan penyimpanan file berfungsi
5. Buat peminjaman, kembalikan yang terlambat — pastikan denda otomatis terhitung
6. Export PDF dan Excel — pastikan download berhasil
7. Import CSV — pastikan pembuatan massal berhasil
8. Toggle tema dark/light/system — pastikan tema tersimpan
9. Cek polimorfisme: daftar item menampilkan `displayInfo()` yang berbeda per tipe

### Cek Build
```bash
yarn build           # Pastikan frontend terkompilasi tanpa error TypeScript
./vendor/bin/pint --test  # Pastikan kepatuhan PSR-12
```

---

## Perkiraan Jumlah File

| Area | Perkiraan File |
|------|----------------|
| Enum | 6 |
| Interface | 2 |
| Value Object | 1 |
| Model | 8 |
| Service | 7 |
| Controller | 7 |
| Form Request | 10 |
| API Resource | 7 |
| Export | 2 |
| Migration | 6 |
| Seeder | 6 |
| Blade view | 3 |
| Halaman Vue | 8 |
| Komponen Vue | 7+ |
| Composable | 7 |
| Store | 2 |
| Tipe TypeScript | 1 |
| Utilitas lib | 3 |
| Router | 1 |
| Exception | 10 |
| Test | 13+ |
| Config/Docs | 5+ |
| **Total** | **~110+** |
