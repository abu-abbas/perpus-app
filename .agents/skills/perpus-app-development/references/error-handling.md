# Strategi Error Handling — Perpus App

> Dokumen ini mendefinisikan cara menangani error secara profesional di seluruh aplikasi.
> Semua agent WAJIB mengikuti pola ini saat menulis kode error handling.

---

## Prinsip Utama

1. **Pisahkan Business Error dan Application Error** — jangan campur aduk
2. **User harus dapat pesan yang jelas** — bukan stack trace atau pesan teknis
3. **Developer harus dapat konteks debugging** — log detail di backend
4. **Frontend menampilkan pesan sesuai tipe error** — beda tampilan untuk beda jenis
5. **Konsisten** — semua error lewat jalur yang sama, tidak ada penanganan ad-hoc

---

## Hierarki Exception (Backend PHP)

```
app/Exceptions/
├── BusinessException.php              → Base untuk SEMUA error logika bisnis
│   ├── InsufficientStockException.php → Stok item tidak cukup untuk dipinjam
│   ├── ItemNotAvailableException.php  → Item tidak tersedia (stok 0)
│   ├── MemberNotActiveException.php   → Anggota tidak aktif/suspended
│   ├── MaxLoanExceededException.php   → Anggota sudah mencapai batas pinjam
│   ├── LoanAlreadyReturnedException.php → Peminjaman sudah dikembalikan
│   ├── LoanOverdueException.php       → Ada pinjaman terlambat yang belum dikembalikan
│   ├── FineAlreadyPaidException.php   → Denda sudah dibayar
│   └── FineUnpaidException.php        → Masih ada denda belum lunas
│
├── AppException.php                   → Base untuk SEMUA error teknis/sistem
│   ├── FileImportException.php        → Gagal parsing/import file CSV/Excel
│   ├── ExportFailedException.php      → Gagal generate PDF/Excel
│   └── ImageProcessingException.php   → Gagal resize/simpan cover image
```

---

## Detail Implementasi Exception

### 1. BusinessException (Base Class)

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Base exception untuk semua pelanggaran aturan bisnis.
 *
 * Error bisnis adalah error yang DIHARAPKAN terjadi sebagai bagian dari
 * validasi logika domain — bukan bug, bukan masalah teknis.
 * Contoh: stok habis, anggota di-suspend, denda sudah dibayar.
 *
 * Response: HTTP 422 (Unprocessable Entity)
 * Log level: INFO (bukan ERROR, karena ini bukan bug)
 */
abstract class BusinessException extends Exception
{
    /** Kode error bisnis yang unik, dipakai frontend untuk i18n/handling spesifik */
    protected string $errorCode = 'BUSINESS_ERROR';

    /** Data konteks tambahan yang aman dikirim ke frontend */
    protected array $context = [];

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render response JSON yang konsisten untuk error bisnis.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => true,
            'type' => 'business',
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
            'context' => $this->context,
        ], 422);
    }

    /**
     * Tentukan level log — bisnis error cukup INFO, bukan ERROR.
     */
    public function report(): bool
    {
        logger()->info("[BISNIS] {$this->errorCode}: {$this->getMessage()}", [
            'code' => $this->errorCode,
            'context' => $this->context,
        ]);

        return true; // true = jangan log ulang di default handler
    }
}
```

### 2. Contoh Business Exception Spesifik

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika item yang ingin dipinjam stoknya habis (available_stock = 0).
 */
class InsufficientStockException extends BusinessException
{
    protected string $errorCode = 'ITEM_STOCK_HABIS';

    public function __construct(string $itemTitle, int $availableStock)
    {
        $this->context = [
            'item_title' => $itemTitle,
            'available_stock' => $availableStock,
        ];

        parent::__construct(
            "Item \"{$itemTitle}\" tidak bisa dipinjam. Stok tersedia: {$availableStock}."
        );
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\MemberStatus;

/**
 * Dilempar ketika anggota yang statusnya bukan 'active' mencoba meminjam.
 */
class MemberNotActiveException extends BusinessException
{
    protected string $errorCode = 'ANGGOTA_TIDAK_AKTIF';

    public function __construct(string $memberName, MemberStatus $status)
    {
        $this->context = [
            'member_name' => $memberName,
            'current_status' => $status->value,
        ];

        parent::__construct(
            "Anggota \"{$memberName}\" tidak bisa meminjam karena status: {$status->value}."
        );
    }
}
```

### 3. AppException (Base Class)

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Base exception untuk semua error teknis/sistem.
 *
 * Error aplikasi adalah error yang TIDAK DIHARAPKAN — ini bisa jadi bug,
 * masalah infrastruktur, atau kegagalan dependency eksternal.
 * Contoh: gagal tulis file, gagal koneksi, format file rusak.
 *
 * Response: HTTP 500 (Internal Server Error)
 * Log level: ERROR (ini perlu perhatian developer)
 */
abstract class AppException extends Exception
{
    /** Kode error aplikasi yang unik */
    protected string $errorCode = 'APP_ERROR';

    /** Detail teknis untuk logging (TIDAK dikirim ke frontend) */
    protected array $debugContext = [];

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Render response JSON — pesan generik ke user, detail di log saja.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => true,
            'type' => 'system',
            'code' => $this->errorCode,
            'message' => $this->getUserMessage(),
        ], 500);
    }

    /**
     * Pesan aman untuk ditampilkan ke user (tanpa detail teknis).
     */
    protected function getUserMessage(): string
    {
        return 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.';
    }

    /**
     * Log detail error termasuk konteks teknis untuk debugging.
     */
    public function report(): bool
    {
        logger()->error("[SISTEM] {$this->errorCode}: {$this->getMessage()}", [
            'code' => $this->errorCode,
            'debug' => $this->debugContext,
            'trace' => $this->getTraceAsString(),
        ]);

        return true;
    }
}
```

### 4. Contoh Application Exception Spesifik

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika proses import file CSV/Excel gagal.
 */
class FileImportException extends AppException
{
    protected string $errorCode = 'IMPORT_GAGAL';

    public function __construct(string $filename, string $reason, ?\Throwable $previous = null)
    {
        $this->debugContext = [
            'filename' => $filename,
            'reason' => $reason,
        ];

        parent::__construct(
            "Gagal import file \"{$filename}\": {$reason}",
            0,
            $previous
        );
    }

    protected function getUserMessage(): string
    {
        return 'Gagal memproses file yang diupload. Pastikan format file benar (CSV/XLSX) dan coba lagi.';
    }
}
```

---

## Daftar Lengkap Exception & Kode Error

### Business Exceptions (HTTP 422)

| Exception | Kode Error | Kapan Dilempar |
|-----------|-----------|----------------|
| `InsufficientStockException` | `ITEM_STOK_HABIS` | `available_stock` = 0 saat proses pinjam |
| `ItemNotAvailableException` | `ITEM_TIDAK_TERSEDIA` | Item dihapus atau tidak ditemukan saat proses pinjam |
| `MemberNotActiveException` | `ANGGOTA_TIDAK_AKTIF` | Status anggota bukan `active` saat proses pinjam |
| `MaxLoanExceededException` | `BATAS_PINJAM_TERCAPAI` | Anggota sudah punya >= 3 pinjaman aktif |
| `LoanAlreadyReturnedException` | `SUDAH_DIKEMBALIKAN` | Proses return untuk peminjaman yang sudah status `returned` |
| `FineAlreadyPaidException` | `DENDA_SUDAH_LUNAS` | Tandai bayar untuk denda yang sudah `paid` |
| `FineUnpaidException` | `DENDA_BELUM_LUNAS` | Anggota mau pinjam tapi masih ada denda belum dibayar |

### Application Exceptions (HTTP 500)

| Exception | Kode Error | Kapan Dilempar |
|-----------|-----------|----------------|
| `FileImportException` | `IMPORT_GAGAL` | File CSV/Excel rusak, format salah, atau gagal dibaca |
| `ExportFailedException` | `EXPORT_GAGAL` | Gagal generate PDF atau Excel |
| `ImageProcessingException` | `GAMBAR_GAGAL_DIPROSES` | Gagal resize/simpan cover image |

---

## Format Response Error (Backend → Frontend)

### Business Error (422)
```json
{
    "error": true,
    "type": "business",
    "code": "ITEM_STOK_HABIS",
    "message": "Item \"Laskar Pelangi\" tidak bisa dipinjam. Stok tersedia: 0.",
    "context": {
        "item_title": "Laskar Pelangi",
        "available_stock": 0
    }
}
```

### Application Error (500)
```json
{
    "error": true,
    "type": "system",
    "code": "IMPORT_GAGAL",
    "message": "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator."
}
```

### Validation Error (422 — bawaan Laravel)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["Email wajib diisi."],
        "password": ["Password minimal 8 karakter."]
    }
}
```

### Authentication Error (401 — bawaan Sanctum)
```json
{
    "message": "Unauthenticated."
}
```

---

## Penanganan Error di Frontend (Vue/TypeScript)

### Tipe Error TypeScript

```typescript
/** Tipe response error dari backend */
interface BusinessError {
  error: true
  type: 'business'
  code: string
  message: string
  context: Record<string, unknown>
}

interface SystemError {
  error: true
  type: 'system'
  code: string
  message: string
}

interface ValidationError {
  message: string
  errors: Record<string, string[]>
}

type ApiError = BusinessError | SystemError | ValidationError
```

### Axios Interceptor

```typescript
// resources/js/lib/axios.ts
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const data = error.response?.data

    switch (status) {
      case 401:
        // Sesi habis → redirect ke login
        authStore.clearUser()
        router.push('/login')
        toast.error('Sesi Anda telah berakhir. Silakan login kembali.')
        break

      case 403:
        // Tidak punya akses
        toast.error('Anda tidak memiliki akses untuk melakukan aksi ini.')
        break

      case 422:
        if (data?.type === 'business') {
          // Error bisnis → tampilkan pesan spesifik dengan toast WARNING (bukan error)
          toast.warning(data.message, { description: 'Pelanggaran aturan bisnis' })
        }
        // Validation error → biarkan vee-validate handle di komponen
        break

      case 500:
        // Error sistem → tampilkan pesan generik dengan toast ERROR
        toast.error(
          data?.message || 'Terjadi kesalahan sistem. Silakan coba lagi.',
          { description: 'Kesalahan Sistem' }
        )
        break

      default:
        // Error tidak dikenal
        toast.error('Terjadi kesalahan yang tidak diketahui.')
    }

    return Promise.reject(error)
  }
)
```

### Penanganan di Vue-Query Mutation

```typescript
// Contoh di composable
const useCreateLoan = () => {
  return useMutation({
    mutationFn: (data: CreateLoanPayload) => api.post('/loans', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['loans'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard'] })
      toast.success('Peminjaman berhasil dicatat.')
    },
    onError: (error: AxiosError<ApiError>) => {
      // Error bisnis spesifik yang perlu handling khusus di UI
      if (error.response?.data?.type === 'business') {
        const code = (error.response.data as BusinessError).code
        if (code === 'DENDA_BELUM_LUNAS') {
          // Tampilkan dialog khusus: "Anggota masih punya denda, bayar dulu?"
          showFineWarningDialog.value = true
        }
      }
      // Error lain sudah di-handle oleh interceptor global
    },
  })
}
```

---

## Aturan Penting

### Kapan Lempar BusinessException vs Return Biasa

| Situasi | Pendekatan |
|---------|-----------|
| Stok habis saat proses pinjam | ✅ Lempar `InsufficientStockException` |
| Input form tidak valid | ❌ Jangan lempar exception → pakai Form Request Laravel |
| Item tidak ditemukan | ❌ Jangan lempar exception → pakai `findOrFail()` (404 otomatis) |
| Logika bisnis dilanggar | ✅ Lempar `BusinessException` yang sesuai |
| File upload gagal dibaca | ✅ Lempar `FileImportException` |
| Database down | ❌ Biarkan Laravel handle otomatis (500) |

### Aturan Logging

| Tipe Error | Level Log | Detail yang Dicatat |
|------------|-----------|---------------------|
| Business | `INFO` | Kode error + pesan + konteks bisnis |
| Application | `ERROR` | Kode error + pesan + debug context + stack trace |
| Validation | Tidak perlu log | Sudah di-return ke user |
| Auth (401/403) | `WARNING` | User ID + endpoint yang diakses |

### Aturan Pesan Error ke User

- **Business error**: Tampilkan pesan asli dari backend (sudah ditulis ramah user)
- **System error**: Tampilkan pesan generik "Terjadi kesalahan sistem..."
- **JANGAN pernah** tampilkan stack trace, query SQL, atau nama file internal ke user
- Semua pesan error ke user dalam **Bahasa Indonesia**
