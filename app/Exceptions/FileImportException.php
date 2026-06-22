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
