# Fase 5: Export & Import

> Invoke dengan: `/fase-5-export-import`

## Pengerjaan
1. **ItemsExport** — export daftar item ke PDF dan Excel
2. **LoanReportExport** — export laporan peminjaman ke PDF dan Excel
3. **Blade template PDF** — `items-pdf.blade.php`, `loan-report-pdf.blade.php`
4. **Import logic** di ImportService — pakai fast-excel

## Checklist Error Handling Import
- [ ] File format tidak valid → lempar `FileImportException`
- [ ] Baris individual gagal → kumpulkan error, lanjut baris berikutnya
- [ ] Return summary: `{ success: N, failed: N, errors: [...] }`
- [ ] File terlalu besar → validasi di Form Request (max:5120)

## Validasi
- [ ] Export PDF menghasilkan file yang bisa dibuka
- [ ] Export Excel menghasilkan file yang bisa dibuka
- [ ] Import CSV dengan data valid → semua masuk
- [ ] Import CSV dengan baris error → laporkan baris mana yang gagal

## Lanjutkan ke
`/fase-6-frontend-spa`
