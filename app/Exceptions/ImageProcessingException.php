<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika proses resize/simpan cover image gagal.
 */
class ImageProcessingException extends AppException
{
    protected string $errorCode = 'IMAGE_PROCESSING_FAILED';

    public function __construct(string $filename, string $reason, ?\Throwable $previous = null)
    {
        $this->debugContext = [
            'filename' => $filename,
            'reason' => $reason,
        ];

        parent::__construct(
            "Gagal memproses gambar \"{$filename}\": {$reason}",
            0,
            $previous
        );
    }
}
