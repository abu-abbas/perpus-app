# Fase 4: HTTP Layer (Controller, Request, Resource, Route)

> Invoke dengan: `/fase-4-http-layer`

## Urutan Pengerjaan
1. **Form Request** (`app/Http/Requests/`) — 10 file validasi
2. **API Resource** (`app/Http/Resources/`) — 7 file transformasi
3. **Controller** (`app/Http/Controllers/Api/`) — 7 controller tipis
4. **Route** (`routes/api.php`) — semua endpoint
5. **Middleware** — role-based access (admin vs pustakawan)
6. **Catch-all route** (`routes/web.php`) — untuk SPA

## Aturan Controller
- Controller HANYA orchestration: terima request → panggil service → return resource
- JANGAN tangkap exception di controller — biarkan exception render sendiri
- Contoh:
  ```php
  public function store(StoreLoanRequest $request): LoanResource
  {
      // Service akan lempar BusinessException jika aturan dilanggar
      // Exception otomatis ter-render jadi response JSON oleh Laravel
      $loan = $this->loanService->borrow(
          member: Member::findOrFail($request->integer('member_id')),
          item: Item::findOrFail($request->integer('item_id')),
          librarian: $request->user(),
      );

      return new LoanResource($loan);
  }
  ```

## Validasi
- [ ] Semua endpoint bisa diakses sesuai role
- [ ] Error bisnis menghasilkan response 422 yang konsisten
- [ ] Error sistem menghasilkan response 500 yang aman
- [ ] Endpoint yang dilindungi menolak akses tanpa login (401)

## Lanjutkan ke
`/fase-5-export-import`
