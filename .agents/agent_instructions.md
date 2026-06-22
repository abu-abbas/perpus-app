# Petunjuk Eksekusi Agen (Agent Instructions)

Dokumen ini ditulis untuk memandu agen AI baru yang akan mengambil alih pengerjaan proyek Sistem Manajemen Perpustakaan (Laravel 13 + Vue 3 SPA) di workspace ini. Dokumen ini bertujuan untuk mencegah halusinasi dan memastikan agen bekerja secara terstruktur dari awal hingga akhir.

---

## 1. Sumber Kebenaran (Sources of Truth)

Sebelum menulis kode atau menjalankan command apa pun, Anda **WAJIB** membaca dan mematuhi file-file konfigurasi agen yang telah dibuat di folder `.agents/`:

1. **Aturan Utama Proyek**: [rules.md](./rules.md)
   - Berisi batasan teknologi (Laravel 13, Vue 3 SPA, Yarn, SQLite, Sanctum Cookie, TypeScript strict).
   - Berisi larangan keras (TIDAK BOLEH pakai Repository pattern, TIDAK BOLEH pakai Maatwebsite/excel, dll).
   - **Aturan Bahasa (Kritis)**: Semua komunikasi, respons, komentar kode (PHPDoc & TSDoc), dokumentasi README, dan pesan error WAJIB dalam **Bahasa Indonesia**. Pengecualian hanya untuk penamaan class/variabel/method.
2. **Alur Kerja & Checklist Fase**: [workflow.md](./workflow.md)
   - Berisi checklist 8 fase pembangunan aplikasi.
   - Gunakan file ini untuk memantau kemajuan Anda.
3. **Referensi Spesifikasi Teknis**: [SKILL.md](./skills/perpus-app-development/SKILL.md)
   - Berisi skema database lengkap, enum, endpoint API, desain class OOP, dan pemetaan kriteria kelulusan ujian.
4. **Strategi Error Handling**: [error-handling.md](./skills/perpus-app-development/references/error-handling.md)
   - Referensi implementasi subclass Exception (BusinessException vs AppException) serta integrasi Axios interceptor di frontend.
5. **Rencana Implementasi Detail**: [implementation_plan.md](./implementation_plan.md)
   - Rencana detail dari Fase 1 sampai Fase 8, termasuk struktur file dan kode boilerplate yang disepakati.

---

## 2. Cara Memulai Pengerjaan (Menggunakan /goal)

User disarankan untuk menggunakan perintah slash `/goal` di chat UI untuk memerintahkan Anda. Jika user memberikan perintah `/goal`, Anda harus:

1. **Buat file `task.md`** di folder artifacts program (`<appDataDir>/brain/<conversation-id>/task.md`) untuk mencatat daftar TODO yang sedang berjalan.
2. **Lakukan verifikasi environment**:
   - PHP: pastikan menggunakan versi PHP yang tersedia (minimal PHP 8.3/8.4).
   - Node & Yarn: pastikan path yarn valid (misal `/opt/homebrew/bin/yarn`). Jika command `yarn` diblokir sandbox, gunakan full path-nya atau gunakan alat bantu terminal yang disetujui.
3. **Jalankan Fase 1 hingga Fase 8 secara berurutan**:
   - **JANGAN melompati fase**.
   - Setiap fase harus diselesaikan dengan kode yang benar-benar bisa di-build dan di-test.
   - Selesaikan backend (database, models, service, controller) sebelum berpindah ke frontend.
4. **Tulis Kode Nyata (TIDAK BOLEH Ada Placeholder)**:
   - Dilarang keras menulis `// TODO: implement later` atau mock data di controller/service produksi.
   - Semua fungsi upload cover, export PDF/Excel, validasi strict TypeScript, dan error handling harus diimplementasikan penuh.

---

## 3. Catatan Penting Saat Coding

- **Tanpa Repository Pattern**: Semua controller memanggil Service. Service memanggil model Eloquent secara langsung.
- **Library Excel**: Gunakan `rap2hpoutre/fast-excel`, bukan `maatwebsite/excel`.
- **Library Image**: Gunakan `intervention/image-laravel` untuk Laravel 13, resize ke maks lebar 800px dengan mempertahankan aspek rasio.
- **Keamanan**: Auth menggunakan Cookie-based Sanctum. Gunakan middleware Sanctum di route API. Admin memiliki middleware tersendiri untuk membatasi aksi delete.
- **Frontend**: Gunakan `@tanstack/vue-query` untuk fetching data, `vee-validate` + `zod` untuk handling form.
- **Polimorfisme**: Model `Item` memiliki subclass `Book`, `Magazine`, dan `Dvd`. Gunakan `ItemFactory` untuk inisialisasi. Method `displayInfo()` dan `calculateLateFee()` wajib di-override di subclass.

---

## 4. Penyelesaian Tugas

Setelah seluruh fase selesai:
1. Pastikan semua unit test (`php artisan test`) lolos 100%.
2. Pastikan build frontend (`yarn build`) berhasil tanpa error TypeScript.
3. Jalankan `pint` untuk memformat kode PHP agar sesuai standar PSR-12.
4. Update file `walkthrough.md` di folder artifacts dengan rangkuman hasil kerja, screenshot/video interaksi (jika ada), dan instruksi menjalankan aplikasi.
5. Buat tabel *compliance matrix* di `README.md` root proyek untuk memetakan kriteria kelulusan (a-l) ke file fisik yang bersangkutan.
