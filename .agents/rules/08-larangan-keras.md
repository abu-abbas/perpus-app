# Larangan Keras

> Aktivasi yang disarankan: **Always On** (kritis, harus selalu dicek sebelum menulis kode).

- ❌ Kode placeholder, `// TODO: implement later`, atau komentar "implement later" di controller/service produksi
- ❌ Mock data permanen di kode produksi
- ❌ Repository pattern
- ❌ `maatwebsite/excel` → pakai `rap2hpoutre/fast-excel`
- ❌ `npm` → pakai `yarn`
- ❌ Fetch manual di komponen Vue → pakai vue-query
- ❌ String mentah untuk perbandingan enum → pakai enum case
- ❌ Business logic di Controller
- ❌ Kode yang tidak terpakai hanya untuk "memenuhi syarat"

## Catatan Tambahan

Semua fungsi upload cover, export PDF/Excel, validasi strict TypeScript, dan error handling
harus diimplementasikan penuh — tidak boleh ada bagian yang "disederhanakan sementara".
