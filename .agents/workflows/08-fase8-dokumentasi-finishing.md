# Fase 8: Dokumentasi & Finishing

> Invoke dengan: `/fase-8-dokumentasi-finishing`
> Ini adalah workflow terakhir — selesaikan semua item di bawah sebelum proyek dianggap selesai.

## Pengerjaan
1. **README.md** — deskripsi, tech stack, instalasi, compliance matrix, akun demo
2. **PHPDoc** — cek semua class dan method PHP
3. **TSDoc** — cek semua composable dan fungsi util
4. **Laravel Pint** — format otomatis
5. **Build check** — `yarn build` tanpa error

## Validasi Akhir
```bash
php artisan migrate:fresh --seed    # Database bersih + seed
php artisan storage:link            # Symlink storage
yarn build                          # Frontend compile
./vendor/bin/pint --test            # PSR-12 compliance
php artisan test                    # Semua test pass
```

## Penyelesaian Tugas (Checklist Akhir)

Setelah seluruh fase selesai:
1. Pastikan semua unit test (`php artisan test`) lolos 100%.
2. Pastikan build frontend (`yarn build`) berhasil tanpa error TypeScript.
3. Jalankan `pint` untuk memformat kode PHP agar sesuai standar PSR-12.
4. Update file `walkthrough.md` di folder artifacts dengan rangkuman hasil kerja,
   screenshot/video interaksi (jika ada), dan instruksi menjalankan aplikasi.
5. Buat tabel *compliance matrix* di `README.md` root proyek untuk memetakan kriteria
   kelulusan (a–l) ke file fisik yang bersangkutan (lihat checklist di bawah).

## Checklist Kriteria Ujian (a–l)

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

Preview tabel compliance matrix tersedia di
`.agents/skills/perpus-app-development/references/implementation-plan.md`.
