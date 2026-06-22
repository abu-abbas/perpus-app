<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika proses export PDF/Excel gagal.
 */
class ExportFailedException extends AppException
{
    protected string $errorCode = 'EXPORT_GAGAL';

    public function __construct(string $format, string $reason, ?\Throwable $previous = null)
    {
        $this->debugContext = [
            'format' => $format,
            'reason' => $reason,
        ];

        parent::__construct(
            "Gagal export ke format {$format}: {$reason}",
            0,
            $previous
        );
    }

    protected function getUserMessage(): string
    {
        return 'Gagal mengekspor data. Silakan coba lagi.';
    }
}
