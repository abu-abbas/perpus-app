<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika anggota mencoba meminjam tapi masih ada denda yang belum dibayar.
 */
class FineUnpaidException extends BusinessException
{
    protected string $errorCode = 'FINE_UNPAID';

    public function __construct(string $memberName, int $unpaidCount)
    {
        $this->context = [
            'member_name' => $memberName,
            'unpaid_fines_count' => $unpaidCount,
        ];

        parent::__construct(
            "Anggota \"{$memberName}\" masih memiliki {$unpaidCount} denda yang belum dilunasi."
        );
    }
}
