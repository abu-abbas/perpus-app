<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika anggota sudah mencapai batas maksimum peminjaman aktif (>= 3).
 */
class MaxLoanExceededException extends BusinessException
{
    protected string $errorCode = 'BATAS_PINJAM_TERCAPAI';

    public function __construct(string $memberName, int $activeLoans)
    {
        $this->context = [
            'member_name' => $memberName,
            'active_loans' => $activeLoans,
        ];

        parent::__construct(
            "Anggota \"{$memberName}\" sudah memiliki {$activeLoans} peminjaman aktif. Batas maksimum: 3."
        );
    }
}
