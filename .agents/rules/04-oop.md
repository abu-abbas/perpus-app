# Aturan OOP (Kriteria Ujian)

> Aktivasi yang disarankan: **Always On**.

## Inheritance
- `Item` (base) → `Book`, `Magazine`, `Dvd` (subclass)
- Single table inheritance: satu tabel `items`, dibedakan kolom `type`
- Setiap subclass WAJIB override `displayInfo()` dan `calculateLateFee()`
- `$lateFeePerDay` berbeda per subclass: Book = 500, Magazine = 1000, Dvd = 2000

## Polymorphism (WAJIB TERPAKAI DI FITUR NYATA)
- Harus ada loop atas koleksi `Item` campuran yang memanggil method lewat reference tipe dasar
- Bukan cuma didefinisikan tapi tidak dipakai

## Interface
- `Borrowable`: `isAvailable()`, `markBorrowed()`, `markReturned()` — diimplementasikan di `Item`
- `Reportable`: `toReportArray()` — diimplementasikan di `Item`, `Loan`, `Member`

## Simulasi Overloading
- Di `FineCalculator` lewat parameter default/opsional
- WAJIB ada PHPDoc yang menjelaskan ini simulasi

## Visibility & Readonly
- Pakai `private`/`protected`/`public` secara bermakna
- `FineAmount` value object pakai `readonly` property

## Factory
- `ItemFactory` dipakai untuk inisialisasi `Book`/`Magazine`/`Dvd` sesuai `ItemType`
- `newFromBuilder()` di-override di `Item` agar Eloquent menginstansiasi subclass yang benar
