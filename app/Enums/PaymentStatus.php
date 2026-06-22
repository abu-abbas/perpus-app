<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status pembayaran denda.
 *
 * - Unpaid: denda belum dibayar
 * - Paid: denda sudah dilunasi
 */
enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
}
