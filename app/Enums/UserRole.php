<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Peran pengguna dalam sistem perpustakaan.
 *
 * - Admin: memiliki akses penuh termasuk hapus data
 * - Pustakawan: hanya akses operasional (CRUD tanpa hapus)
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Pustakawan = 'pustakawan';
}
