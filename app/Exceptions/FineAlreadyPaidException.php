<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika denda sudah dibayar dan user mencoba membayar lagi.
 */
class FineAlreadyPaidException extends BusinessException
{
    protected string $errorCode = 'FINE_ALREADY_PAID';

    public function __construct(int $fineId)
    {
        $this->context = [
            'fine_id' => $fineId,
        ];

        parent::__construct(
            "Denda #{$fineId} sudah dibayar sebelumnya."
        );
    }
}
