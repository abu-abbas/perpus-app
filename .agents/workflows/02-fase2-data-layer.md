# Fase 2: Data Layer (Enum, Migration, Model, Interface, Value Object)

> Invoke dengan: `/fase-2-data-layer`

## Deskripsi
Membangun seluruh lapisan data. Referensi skema lengkap ada di
`.agents/skills/perpus-app-development/SKILL.md` (bagian Skema Database & Enum),
dan strategi exception ada di
`.agents/skills/perpus-app-development/references/error-handling.md`.

## Urutan Pengerjaan
1. **Enum** (`app/Enums/`) — 6 file
2. **Interface** (`app/Interfaces/`) — `Borrowable`, `Reportable`
3. **Value Object** (`app/ValueObjects/`) — `FineAmount`
4. **Migration** (`database/migrations/`) — 6 tabel sesuai skema
5. **Model** (`app/Models/`) — User, Category, Item, Book, Magazine, Dvd, Member, Loan, Fine
6. **Exception** (`app/Exceptions/`) — `BusinessException`, `AppException`, dan semua turunannya
7. **Seeder** (`database/seeders/`) — data demo

## Checklist OOP
- [ ] Item → Book/Magazine/Dvd inheritance benar
- [ ] `displayInfo()` dan `calculateLateFee()` di-override tiap subclass
- [ ] `$lateFeePerDay` berbeda per subclass (Book: 500, Magazine: 1000, Dvd: 2000)
- [ ] `Borrowable` interface diimplementasi di Item
- [ ] `Reportable` interface diimplementasi di Item, Loan, Member
- [ ] `FineAmount` pakai `readonly` property
- [ ] Semua enum di-cast di model lewat `casts()`
- [ ] `newFromBuilder()` override di Item untuk instansiasi subclass

## Checklist Error Handling
- [ ] `BusinessException` base class dengan render() dan report()
- [ ] Semua specific business exception extends `BusinessException`
- [ ] `AppException` base class dengan pesan generik ke user
- [ ] Semua specific app exception extends `AppException`
- [ ] Response format konsisten (lihat referensi error-handling.md)

## Validasi
- [ ] `php artisan migrate:fresh --seed` sukses
- [ ] Cek data seed ada di database

## Lanjutkan ke
`/fase-3-business-logic`
