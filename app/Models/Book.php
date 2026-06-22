<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model Book — subclass dari Item untuk koleksi jenis buku.
 *
 * Tarif denda: Rp 500/hari (paling rendah).
 * Atribut spesifik: jumlah halaman (pages) dari kolom JSON `attributes`.
 */
class Book extends Item
{
    /**
     * Tarif denda per hari untuk buku — paling rendah karena buku paling umum.
     */
    protected float $lateFeePerDay = 500.0;

    /**
     * Informasi tampilan spesifik buku.
     * Override dari parent untuk menampilkan jumlah halaman.
     *
     * @return array<string, mixed>
     */
    public function displayInfo(): array
    {
        $attributes = $this->getAttribute('attributes') ?? [];
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true) ?? [];
        }
        if (isset($attributes['attributes'])) {
            $attributes = $attributes['attributes'];
        }

        return [
            'tipe' => 'Buku',
            'judul' => $this->title,
            'penulis' => $this->author,
            'halaman' => $attributes['pages'] ?? '-',
        ];
    }

    /**
     * Hitung denda keterlambatan dengan tarif buku (Rp 500/hari).
     *
     * @param int $daysLate Jumlah hari keterlambatan
     * @return float Jumlah denda dalam Rupiah
     */
    public function calculateLateFee(int $daysLate): float
    {
        return $this->lateFeePerDay * max(0, $daysLate);
    }
}
