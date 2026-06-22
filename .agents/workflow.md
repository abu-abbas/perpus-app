# Workflow Pengembangan — Perpus App

> Panduan langkah demi langkah untuk membangun dan mengembangkan Sistem Manajemen Perpustakaan.
> Ikuti urutan ini untuk memastikan setiap komponen dibangun di atas fondasi yang benar.

---

## Prasyarat

Sebelum mulai, pastikan:
- [ ] Sudah baca `.agents/rules.md` (aturan proyek)
- [ ] Sudah baca `.agents/skills/perpus-app-development/SKILL.md` (referensi teknis)
- [ ] Sudah baca `.agents/skills/perpus-app-development/references/error-handling.md` (strategi error)

---

## Fase 1: Scaffold & Konfigurasi

### Langkah:
1. Buat proyek Laravel: `composer create-project laravel/laravel ./`
2. Konfigurasi `.env` dan `.env.example` untuk SQLite
3. Buat `database/database.sqlite`
4. Install dependency Composer:
   ```bash
   composer require barryvdh/laravel-dompdf rap2hpoutre/fast-excel intervention/image-laravel
   ```
5. Install dependency Yarn:
   ```bash
   yarn add vue vue-router pinia @tanstack/vue-query axios vee-validate @vee-validate/zod zod
   yarn add -D @vitejs/plugin-vue typescript vue-tsc
   ```
6. Inisialisasi shadcn-vue: `npx shadcn-vue@latest init`
7. Tambah komponen shadcn-vue yang dibutuhkan
8. Konfigurasi: Tailwind, TypeScript, Vite, CSS variables, Sanctum

### Validasi:
- [ ] `php artisan serve` jalan tanpa error
- [ ] `yarn dev` jalan tanpa error

---

## Fase 2: Data Layer (Enum, Migration, Model, Interface, Value Object)

### Urutan pengerjaan:
1. **Enum** (`app/Enums/`) — 6 file
2. **Interface** (`app/Interfaces/`) — `Borrowable`, `Reportable`
3. **Value Object** (`app/ValueObjects/`) — `FineAmount`
4. **Migration** (`database/migrations/`) — 6 tabel sesuai skema
5. **Model** (`app/Models/`) — User, Category, Item, Book, Magazine, Dvd, Member, Loan, Fine
6. **Exception** (`app/Exceptions/`) — `BusinessException`, `AppException`, dan semua turunannya
7. **Seeder** (`database/seeders/`) — data demo

### Checklist OOP:
- [ ] Item → Book/Magazine/Dvd inheritance benar
- [ ] `displayInfo()` dan `calculateLateFee()` di-override tiap subclass
- [ ] `$lateFeePerDay` berbeda per subclass (Book: 500, Magazine: 1000, Dvd: 2000)
- [ ] `Borrowable` interface diimplementasi di Item
- [ ] `Reportable` interface diimplementasi di Item, Loan, Member
- [ ] `FineAmount` pakai `readonly` property
- [ ] Semua enum di-cast di model lewat `casts()`
- [ ] `newFromBuilder()` override di Item untuk instansiasi subclass

### Checklist Error Handling:
- [ ] `BusinessException` base class dengan render() dan report()
- [ ] Semua specific business exception extends `BusinessException`
- [ ] `AppException` base class dengan pesan generik ke user
- [ ] Semua specific app exception extends `AppException`
- [ ] Response format konsisten (lihat error-handling.md)

### Validasi:
- [ ] `php artisan migrate:fresh --seed` sukses
- [ ] Cek data seed ada di database

---

## Fase 3: Business Logic (Service Layer)

### Urutan pengerjaan:
1. **ItemFactory** — factory method Book/Magazine/Dvd
2. **MemberService** — termasuk `do-while` untuk generate kode unik
3. **FineCalculator** — simulasi overloading lewat parameter opsional
4. **FineService** — CRUD denda
5. **ItemService** — CRUD item, upload cover, termasuk `foreach` untuk import
6. **LoanService** — pinjam/kembali, termasuk `if-elseif-else` untuk denda
7. **ImportService** — import massal dari CSV/Excel
8. **ReportService** — dashboard + laporan, termasuk `array_map`/`array_filter`/`array_reduce`

### Checklist Kontrol Struktur (Poin d):
- [ ] `if-elseif-else` di LoanService (penentuan alasan denda)
- [ ] `foreach` di ItemService/ReportService (proses data)
- [ ] `do-while` di MemberService (generate kode unik)

### Checklist Error Handling di Service:
- [ ] Service melempar `BusinessException` untuk pelanggaran aturan bisnis
- [ ] Service melempar `AppException` untuk kegagalan teknis
- [ ] Service TIDAK menangkap exception sendiri (biarkan naik ke Controller/Handler)
- [ ] Setiap throw exception disertai konteks yang cukup untuk debugging

### Validasi:
- [ ] Unit test untuk setiap service method kritis

---

## Fase 4: HTTP Layer (Controller, Request, Resource, Route)

### Urutan pengerjaan:
1. **Form Request** (`app/Http/Requests/`) — 10 file validasi
2. **API Resource** (`app/Http/Resources/`) — 7 file transformasi
3. **Controller** (`app/Http/Controllers/Api/`) — 7 controller tipis
4. **Route** (`routes/api.php`) — semua endpoint
5. **Middleware** — role-based access (admin vs pustakawan)
6. **Catch-all route** (`routes/web.php`) — untuk SPA

### Aturan Controller:
- Controller HANYA orchestration: terima request → panggil service → return resource
- JANGAN tangkap exception di controller — biarkan exception render sendiri
- Contoh:
  ```php
  public function store(StoreLoanRequest $request): LoanResource
  {
      // Service akan lempar BusinessException jika aturan dilanggar
      // Exception otomatis ter-render jadi response JSON oleh Laravel
      $loan = $this->loanService->borrow(
          member: Member::findOrFail($request->integer('member_id')),
          item: Item::findOrFail($request->integer('item_id')),
          librarian: $request->user(),
      );

      return new LoanResource($loan);
  }
  ```

### Validasi:
- [ ] Semua endpoint bisa diakses sesuai role
- [ ] Error bisnis menghasilkan response 422 yang konsisten
- [ ] Error sistem menghasilkan response 500 yang aman
- [ ] Endpoint yang dilindungi menolak akses tanpa login (401)

---

## Fase 5: Export & Import

### Pengerjaan:
1. **ItemsExport** — export daftar item ke PDF dan Excel
2. **LoanReportExport** — export laporan peminjaman ke PDF dan Excel
3. **Blade template PDF** — `items-pdf.blade.php`, `loan-report-pdf.blade.php`
4. **Import logic** di ImportService — pakai fast-excel

### Checklist Error Handling Import:
- [ ] File format tidak valid → lempar `FileImportException`
- [ ] Baris individual gagal → kumpulkan error, lanjut baris berikutnya
- [ ] Return summary: `{ success: N, failed: N, errors: [...] }`
- [ ] File terlalu besar → validasi di Form Request (max:5120)

### Validasi:
- [ ] Export PDF menghasilkan file yang bisa dibuka
- [ ] Export Excel menghasilkan file yang bisa dibuka
- [ ] Import CSV dengan data valid → semua masuk
- [ ] Import CSV dengan baris error → laporkan baris mana yang gagal

---

## Fase 6: Frontend SPA

### Urutan pengerjaan:
1. **Fondasi**: `app.ts`, `App.vue`, router, store, axios instance, query client
2. **Tipe TypeScript**: `types/index.ts` (termasuk tipe error)
3. **Store**: `auth.ts`, `theme.ts`
4. **Composable**: vue-query hooks per modul (7 file)
5. **Komponen custom**: Layout, Sidebar, ThemeToggle, DataTable, FormDialog, dll
6. **Halaman**: Login, Dashboard, Categories, Items, Members, Loans, Fines, Reports

### Checklist Error Handling Frontend:
- [ ] Axios interceptor menangani 401, 403, 422 (bisnis), 500 (sistem)
- [ ] Toast warning untuk business error (kuning/oranye)
- [ ] Toast error untuk system error (merah)
- [ ] Toast success untuk operasi berhasil (hijau)
- [ ] Form validation error ditampilkan inline di field yang bersangkutan
- [ ] Loading state di semua tombol aksi (disable saat proses)
- [ ] Error boundary/fallback untuk crash komponen

### Validasi:
- [ ] Login/logout berfungsi
- [ ] Semua CRUD berfungsi di setiap modul
- [ ] Theme toggle tersimpan di localStorage
- [ ] Role-based menu berfungsi

---

## Fase 7: Testing

### Unit Test (WAJIB):
- [ ] `ItemPolymorphismTest` — displayInfo() berbeda per subclass
- [ ] `FineCalculatorTest` — simulasi overloading, 3 variasi
- [ ] `BorrowableInterfaceTest` — kontrak interface
- [ ] `ReportableInterfaceTest` — kontrak interface
- [ ] `EnumCastingTest` — casting enum di model
- [ ] `FineAmountValueObjectTest` — immutable, readonly
- [ ] `StockManagementTest` — stok berkurang/bertambah
- [ ] `BusinessExceptionTest` — response format benar, log level benar
- [ ] `AppExceptionTest` — pesan generik ke user, detail di log

### Feature Test (WAJIB):
- [ ] `AuthTest` — login/logout Sanctum
- [ ] `CategoryApiTest` — CRUD
- [ ] `ItemApiTest` — CRUD + import/export
- [ ] `LoanApiTest` — pinjam + kembali + denda otomatis
- [ ] `FineApiTest` — daftar + bayar
- [ ] `ErrorHandlingTest` — business error 422, system error 500

### Validasi:
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

---

## Fase 8: Dokumentasi & Finishing

### Pengerjaan:
1. **README.md** — deskripsi, tech stack, instalasi, compliance matrix, akun demo
2. **PHPDoc** — cek semua class dan method PHP
3. **TSDoc** — cek semua composable dan fungsi util
4. **Laravel Pint** — format otomatis
5. **Build check** — `yarn build` tanpa error

### Validasi Akhir:
```bash
php artisan migrate:fresh --seed    # Database bersih + seed
php artisan storage:link            # Symlink storage
yarn build                          # Frontend compile
./vendor/bin/pint --test            # PSR-12 compliance
php artisan test                    # Semua test pass
```

---

## Checklist Kriteria Ujian (a-l)

Sebelum dianggap selesai, pastikan SEMUA ini terpenuhi:

- [ ] **(a)** Program sesuai rancangan — skema DB dan OOP sesuai spesifikasi
- [ ] **(b)** Coding guidelines — PSR-12, Vue Style Guide
- [ ] **(c)** Interface I/O — form + data table di setiap modul
- [ ] **(d)** Tipe data & kontrol — enum, if-elseif, foreach, do-while
- [ ] **(e)** Prosedur/fungsi — semua logic di Service class
- [ ] **(f)** Array — array_map/filter/reduce di ReportService
- [ ] **(g)** Simpan/baca file — cover image, export PDF/Excel, import CSV
- [ ] **(h)** OOP lengkap — inheritance, polymorphism, interface, overloading, readonly
- [ ] **(i)** Namespace — 7+ namespace berbeda
- [ ] **(j)** Library eksternal — Composer + Yarn packages
- [ ] **(k)** Database — SQLite + Eloquent + migration + seeder
- [ ] **(l)** Dokumentasi — PHPDoc, TSDoc, README + compliance matrix
