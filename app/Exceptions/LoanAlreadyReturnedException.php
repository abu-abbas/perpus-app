<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika proses pengembalian dilakukan untuk peminjaman yang sudah dikembalikan.
 */
class LoanAlreadyReturnedException extends BusinessException
{
    protected string $errorCode = 'SUDAH_DIKEMBALIKAN';

    public function __construct(int $loanId)
    {
        $this->context = [
            'loan_id' => $loanId,
        ];

        parent::__construct(
            "Peminjaman #{$loanId} sudah dikembalikan sebelumnya."
        );
    }
}
