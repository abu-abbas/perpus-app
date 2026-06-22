<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model Magazine — subclass dari Item untuk koleksi jenis majalah.
 *
 * Tarif denda: Rp 1.000/hari (menengah).
 * Atribut spesifik: nomor edisi (edition_number) dari kolom JSON `attributes`.
 */
class Magazine extends Item
{
    /**
     * Tarif denda per hari untuk majalah — tarif menengah.
     */
    protected float $lateFeePerDay = 1000.0;

    /**
     * Informasi tampilan spesifik majalah.
     * Override dari parent untuk menampilkan nomor edisi.
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
            'tipe' => 'Majalah',
            'judul' => $this->title,
            'penulis' => $this->author,
            'edisi' => $attributes['edition_number'] ?? '-',
        ];
    }

    /**
     * Hitung denda keterlambatan dengan tarif majalah (Rp 1.000/hari).
     *
     * @param int $daysLate Jumlah hari keterlambatan
     * @return float Jumlah denda dalam Rupiah
     */
    public function calculateLateFee(int $daysLate): float
    {
        return $this->lateFeePerDay * max(0, $daysLate);
    }
}
