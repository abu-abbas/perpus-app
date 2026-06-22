<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Kontrak untuk item yang bisa dipinjam.
 *
 * Interface ini memastikan setiap item koleksi perpustakaan memiliki
 * mekanisme pengecekan ketersediaan dan perubahan status stok.
 */
interface Borrowable
{
    /**
     * Cek apakah item tersedia untuk dipinjam.
     *
     * @return bool True jika stok tersedia > 0
     */
    public function isAvailable(): bool;

    /**
     * Tandai item sebagai dipinjam — kurangi stok tersedia.
     */
    public function markBorrowed(): void;

    /**
     * Tandai item sebagai dikembalikan — tambah stok tersedia.
     */
    public function markReturned(): void;
}
