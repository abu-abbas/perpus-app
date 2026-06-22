# Tech Stack (TIDAK BOLEH DIGANTI)

> Aktivasi yang disarankan: **Always On**.

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

## Library Eksternal WAJIB

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
