# Kontrol Struktur & Operasi Array (Kriteria Ujian d, f)

> Aktivasi yang disarankan: **Always On**.

## Kontrol Struktur (Kriteria Ujian d) — WAJIB ADA

| Struktur | Lokasi | Contoh |
|----------|--------|--------|
| `if-elseif-else` | `LoanService` | Penentuan alasan denda |
| `foreach`/`for` | `ItemService`/`ReportService` | Proses import atau agregasi |
| `do-while` | `MemberService` | Generate kode anggota unik |

## Operasi Array (Kriteria Ujian f) — WAJIB ADA

Gunakan `array_map`, `array_filter`, `array_reduce` secara nyata di `ReportService`.
