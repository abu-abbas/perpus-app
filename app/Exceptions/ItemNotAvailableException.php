<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika item yang diminta tidak tersedia untuk dipinjam.
 */
class ItemNotAvailableException extends BusinessException
{
    protected string $errorCode = 'ITEM_TIDAK_TERSEDIA';

    public function __construct(string $itemTitle)
    {
        $this->context = [
            'item_title' => $itemTitle,
        ];

        parent::__construct(
            "Item \"{$itemTitle}\" tidak tersedia untuk dipinjam."
        );
    }
}
