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
    /** Kode error bisnis yang unik, dipakai frontend untuk handling spesifik */
    protected string $errorCode = 'BUSINESS_ERROR';

    /** Data konteks tambahan yang aman dikirim ke frontend */
    protected array $context = [];

    /**
     * Ambil kode error bisnis.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Ambil data konteks untuk debugging frontend.
     *
     * @return array<string, mixed>
     */
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

        return true;
    }
}
