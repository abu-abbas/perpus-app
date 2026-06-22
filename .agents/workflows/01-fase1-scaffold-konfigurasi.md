# Fase 1: Scaffold & Konfigurasi

> Invoke dengan: `/fase-1-scaffold-konfigurasi`

## Deskripsi
Membuat proyek Laravel, konfigurasi environment, dan install seluruh dependency
backend & frontend. Referensi detail boilerplate ada di
`.agents/skills/perpus-app-development/references/implementation-plan.md` (Fase 1).

## Langkah

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

## Validasi
- [ ] `php artisan serve` jalan tanpa error
- [ ] `yarn dev` jalan tanpa error

## Lanjutkan ke
`/fase-2-data-layer`
