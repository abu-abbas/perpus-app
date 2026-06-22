# Fase 7: Testing

> Invoke dengan: `/fase-7-testing`

## Unit Test (WAJIB)
- [ ] `ItemPolymorphismTest` — displayInfo() berbeda per subclass
- [ ] `FineCalculatorTest` — simulasi overloading, 3 variasi
- [ ] `BorrowableInterfaceTest` — kontrak interface
- [ ] `ReportableInterfaceTest` — kontrak interface
- [ ] `EnumCastingTest` — casting enum di model
- [ ] `FineAmountValueObjectTest` — immutable, readonly
- [ ] `StockManagementTest` — stok berkurang/bertambah
- [ ] `BusinessExceptionTest` — response format benar, log level benar
- [ ] `AppExceptionTest` — pesan generik ke user, detail di log

## Feature Test (WAJIB)
- [ ] `AuthTest` — login/logout Sanctum
- [ ] `CategoryApiTest` — CRUD
- [ ] `ItemApiTest` — CRUD + import/export
- [ ] `LoanApiTest` — pinjam + kembali + denda otomatis
- [ ] `FineApiTest` — daftar + bayar
- [ ] `ErrorHandlingTest` — business error 422, system error 500

## Validasi
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

## Lanjutkan ke
`/fase-8-dokumentasi-finishing`
