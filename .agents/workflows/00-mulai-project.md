# Mulai Project

> Invoke dengan: `/00-mulai-project`
> Jalankan workflow ini di awal setiap sesi sebelum masuk ke fase pembangunan.

## Deskripsi
Persiapan environment dan pencatatan TODO sebelum agen mulai mengerjakan fase 1–8.
Dipicu biasanya saat user memberi perintah `/goal`.

## Prasyarat
- [ ] Sudah baca seluruh rule di `.agents/rules/`
- [ ] Sudah baca `.agents/skills/perpus-app-development/SKILL.md`
- [ ] Sudah baca `.agents/skills/perpus-app-development/references/error-handling.md`
- [ ] Sudah baca `.agents/skills/perpus-app-development/references/implementation-plan.md`

## Langkah

1. **Buat file `task.md`** di folder artifacts program (`<appDataDir>/brain/<conversation-id>/task.md`)
   untuk mencatat daftar TODO yang sedang berjalan.
2. **Verifikasi environment**:
   - PHP: pastikan menggunakan versi PHP yang tersedia (minimal PHP 8.3/8.4).
   - Node & Yarn: pastikan path yarn valid (misal `/opt/homebrew/bin/yarn`). Jika command
     `yarn` diblokir sandbox, gunakan full path-nya atau alat bantu terminal yang disetujui.
3. **Jalankan Fase 1 hingga Fase 8 secara berurutan** (lihat workflow `/fase-1-scaffold-konfigurasi`
   sampai `/fase-8-dokumentasi-finishing`):
   - **JANGAN melompati fase**.
   - Setiap fase harus diselesaikan dengan kode yang benar-benar bisa di-build dan di-test.
   - Selesaikan backend (database, models, service, controller) sebelum berpindah ke frontend.
4. **Tulis kode nyata** — lihat rule `.agents/rules/08-larangan-keras.md` untuk larangan placeholder/mock data.

## Lanjutkan ke
`/fase-1-scaffold-konfigurasi`
