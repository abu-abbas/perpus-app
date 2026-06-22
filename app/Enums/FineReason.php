<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Alasan pengenaan denda kepada anggota.
 *
 * - Late: terlambat mengembalikan item
 * - Damaged: item dikembalikan dalam kondisi rusak
 * - Lost: item hilang dan tidak dikembalikan
 */
enum FineReason: string
{
    case Late = 'late';
    case Damaged = 'damaged';
    case Lost = 'lost';
}
