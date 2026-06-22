# Arsitektur

> Aktivasi yang disarankan: **Always On**.

## Pola Monorepo
Satu proyek Laravel yang membungkus:
- **Backend API** di prefix `/api`
- **Frontend Vue SPA** di-build Vite, disajikan lewat route catch-all `resources/views/app.blade.php`

## Alur Layer (WAJIB DIIKUTI)
```
Controller (tipis, orchestration saja)
    ↓
Service (semua business logic di sini)
    ↓
Eloquent Model (akses data langsung)
```

- Controller **HANYA** boleh memanggil Service dan mengembalikan Resource
- **DILARANG** menaruh business logic di Controller
- **DILARANG** membuat Repository class

## Struktur Folder Backend
```
app/
├── Enums/          → PHP native backed enum
├── Exports/        → Class export Excel/PDF
├── Http/
│   ├── Controllers/Api/  → Controller tipis
│   ├── Requests/         → Form Request validasi
│   └── Resources/        → API Resource transformasi JSON
├── Interfaces/     → PHP interface (Borrowable, Reportable)
├── Models/         → Eloquent model + inheritance (Item→Book/Magazine/Dvd)
├── Services/       → Business logic
└── ValueObjects/   → Value object immutable (FineAmount)
```

## Keamanan
- Auth menggunakan Cookie-based Sanctum. Gunakan middleware Sanctum di route API.
- Admin memiliki middleware tersendiri untuk membatasi aksi delete (pustakawan tidak boleh hapus data).

## Struktur Folder Frontend
```
resources/js/
├── app.ts              → Entry point
├── App.vue             → Root component
├── components/
│   ├── ui/             → Komponen shadcn-vue (hasil generate)
│   └── *.vue           → Komponen custom
├── composables/        → Hook vue-query per modul
├── lib/                → axios.ts, queryClient.ts, utils.ts
├── pages/              → Halaman per modul
├── router/             → Route + guard
├── stores/             → Pinia store (auth, theme)
└── types/              → Interface TypeScript
```
