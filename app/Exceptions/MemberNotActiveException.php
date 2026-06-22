<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\MemberStatus;

/**
 * Dilempar ketika anggota yang statusnya bukan 'active' mencoba meminjam.
 */
class MemberNotActiveException extends BusinessException
{
    protected string $errorCode = 'MEMBER_NOT_ACTIVE';

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
