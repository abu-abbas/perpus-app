<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status keanggotaan anggota perpustakaan.
 *
 * - Active: anggota aktif, bisa meminjam
 * - Inactive: anggota nonaktif, tidak bisa meminjam
 * - Suspended: anggota ditangguhkan karena pelanggaran
 */
enum MemberStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}
