# Fase 6: Frontend SPA

> Invoke dengan: `/fase-6-frontend-spa`

## Urutan Pengerjaan
1. **Fondasi**: `app.ts`, `App.vue`, router, store, axios instance, query client
2. **Tipe TypeScript**: `types/index.ts` (termasuk tipe error)
3. **Store**: `auth.ts`, `theme.ts`
4. **Composable**: vue-query hooks per modul (7 file)
5. **Komponen custom**: Layout, Sidebar, ThemeToggle, DataTable, FormDialog, dll
6. **Halaman**: Login, Dashboard, Categories, Items, Members, Loans, Fines, Reports

Detail struktur composable, store, router, dan halaman ada di
`.agents/skills/perpus-app-development/references/implementation-plan.md` (Fase 6).

## Checklist Error Handling Frontend
- [ ] Axios interceptor menangani 401, 403, 422 (bisnis), 500 (sistem)
- [ ] Toast warning untuk business error (kuning/oranye)
- [ ] Toast error untuk system error (merah)
- [ ] Toast success untuk operasi berhasil (hijau)
- [ ] Form validation error ditampilkan inline di field yang bersangkutan
- [ ] Loading state di semua tombol aksi (disable saat proses)
- [ ] Error boundary/fallback untuk crash komponen

## Validasi
- [ ] Login/logout berfungsi
- [ ] Semua CRUD berfungsi di setiap modul
- [ ] Theme toggle tersimpan di localStorage
- [ ] Role-based menu berfungsi

## Lanjutkan ke
`/fase-7-testing`
