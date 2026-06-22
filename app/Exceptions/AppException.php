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

    /**
     * Ambil kode error aplikasi.
     */
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
