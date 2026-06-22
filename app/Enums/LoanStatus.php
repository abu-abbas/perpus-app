<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status peminjaman item perpustakaan.
 *
 * - Borrowed: sedang dipinjam
 * - Returned: sudah dikembalikan
 * - Overdue: terlambat mengembalikan
 * - Lost: item hilang/tidak dikembalikan
 */
enum LoanStatus: string
{
    case Borrowed = 'borrowed';
    case Returned = 'returned';
    case Overdue = 'overdue';
    case Lost = 'lost';
}
