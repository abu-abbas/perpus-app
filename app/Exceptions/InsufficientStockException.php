<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Dilempar ketika item yang ingin dipinjam stoknya habis (available_stock = 0).
 */
class InsufficientStockException extends BusinessException
{
    protected string $errorCode = 'INSUFFICIENT_STOCK';

    public function __construct(string $itemTitle, int $availableStock)
    {
        $this->context = [
            'item_title' => $itemTitle,
            'available_stock' => $availableStock,
        ];

        parent::__construct(
            "Item \"{$itemTitle}\" tidak bisa dipinjam. Stok tersedia: {$availableStock}."
        );
    }
}
