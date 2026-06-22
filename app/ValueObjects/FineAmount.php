<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\FineReason;

/**
 * Value Object untuk merepresentasikan jumlah denda.
 *
 * Objek ini bersifat immutable — sekali dibuat, nilainya tidak bisa diubah.
 * Menggunakan readonly properties (PHP 8.1+) untuk menjamin immutability.
 * Mendemonstrasikan penggunaan visibility dan readonly (poin h ujian).
 */
final class FineAmount
{
    /**
     * Buat instance FineAmount baru.
     *
     * @param float $amount Jumlah denda dalam Rupiah
     * @param FineReason $reason Alasan pengenaan denda
     */
    public function __construct(
        public readonly float $amount,
        public readonly FineReason $reason,
    ) {}

    /**
     * Format jumlah denda ke format Rupiah.
     *
     * @return string Jumlah denda terformat, contoh: "Rp 5.000"
     */
    public function formatted(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
