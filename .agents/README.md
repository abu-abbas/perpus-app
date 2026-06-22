# `.agents/` — Konfigurasi Agen untuk Antigravity

Struktur ini mengikuti konvensi Antigravity (lihat dokumentasi *Rules / Workflows*, *Skills*,
dan *Task Groups*). Isi dari `rules.md`, `workflow.md`, dan `agent_instructions.md` versi lama
sudah dipecah ke lokasi berikut:

```
.agents/
├── rules/                              → Always On, dibaca otomatis setiap prompt
│   ├── 01-bahasa-komunikasi.md
│   ├── 02-tech-stack.md
│   ├── 03-arsitektur.md
│   ├── 04-oop.md
│   ├── 05-kontrol-struktur-dan-array.md
│   ├── 06-file-storage.md
│   ├── 07-coding-standards.md
│   └── 08-larangan-keras.md
├── workflows/                          → dipanggil manual via /nama-file
│   ├── 00-mulai-project.md
│   ├── 01-fase1-scaffold-konfigurasi.md
│   ├── 02-fase2-data-layer.md
│   ├── 03-fase3-business-logic.md
│   ├── 04-fase4-http-layer.md
│   ├── 05-fase5-export-import.md
│   ├── 06-fase6-frontend-spa.md
│   ├── 07-fase7-testing.md
│   └── 08-fase8-dokumentasi-finishing.md
└── skills/
    └── perpus-app-development/
        ├── SKILL.md                            → skema DB, enum, endpoint API, desain OOP
        └── references/
            ├── error-handling.md                → hierarki exception & axios interceptor
            └── implementation-plan.md           → rencana detail + boilerplate kode Fase 1-8
```

## Cara Pakai

- **Rules** otomatis aktif di setiap prompt (disarankan diset **Always On** lewat panel
  Customizations → Rules di Antigravity, karena semua isinya berlabel "WAJIB" di dokumen asli).
- **Workflows** dijalankan berurutan mulai dari `/00-mulai-project`, lalu
  `/01-fase1-scaffold-konfigurasi` sampai `/08-fase8-dokumentasi-finishing`. Setiap file workflow
  punya pointer "Lanjutkan ke" di bagian bawah agar agen tahu langkah berikutnya.
- **Skill** `perpus-app-development` otomatis terdeteksi relevan saat agen bekerja di proyek ini
  (lewat `description` di frontmatter `SKILL.md`) — tidak perlu di-mention manual.

> Catatan: dokumentasi resmi Antigravity tidak menampilkan sintaks frontmatter eksplisit untuk
> file Rules (berbeda dengan Skills yang wajib YAML frontmatter `name`/`description`). Karena itu
> file-file di `rules/` sengaja dibiarkan tanpa frontmatter — atur mode aktivasinya lewat panel
> Rules di UI Antigravity sesuai rekomendasi yang tertulis di baris pertama tiap file.
