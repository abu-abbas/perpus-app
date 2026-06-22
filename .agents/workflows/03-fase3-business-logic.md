# Fase 3: Business Logic (Service Layer)

> Invoke dengan: `/fase-3-business-logic`

## Urutan Pengerjaan
1. **ItemFactory** — factory method Book/Magazine/Dvd
2. **MemberService** — termasuk `do-while` untuk generate kode unik
3. **FineCalculator** — simulasi overloading lewat parameter opsional
4. **FineService** — CRUD denda
5. **ItemService** — CRUD item, upload cover, termasuk `foreach` untuk import
6. **LoanService** — pinjam/kembali, termasuk `if-elseif-else` untuk denda
7. **ImportService** — import massal dari CSV/Excel
8. **ReportService** — dashboard + laporan, termasuk `array_map`/`array_filter`/`array_reduce`

## Checklist Kontrol Struktur (Poin d)
- [ ] `if-elseif-else` di LoanService (penentuan alasan denda)
- [ ] `foreach` di ItemService/ReportService (proses data)
- [ ] `do-while` di MemberService (generate kode unik)

## Checklist Error Handling di Service
- [ ] Service melempar `BusinessException` untuk pelanggaran aturan bisnis
- [ ] Service melempar `AppException` untuk kegagalan teknis
- [ ] Service TIDAK menangkap exception sendiri (biarkan naik ke Controller/Handler)
- [ ] Setiap throw exception disertai konteks yang cukup untuk debugging

## Validasi
- [ ] Unit test untuk setiap service method kritis

## Lanjutkan ke
`/fase-4-http-layer`
