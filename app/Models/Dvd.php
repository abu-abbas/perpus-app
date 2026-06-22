<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model Dvd — subclass dari Item untuk koleksi jenis DVD.
 *
 * Tarif denda: Rp 2.000/hari (paling tinggi karena nilai item tinggi).
 * Atribut spesifik: durasi dalam menit (duration_minutes) dari kolom JSON `attributes`.
 */
class Dvd extends Item
{
    /**
     * Tarif denda per hari untuk DVD — paling tinggi karena nilai item lebih mahal.
     */
    protected float $lateFeePerDay = 2000.0;

    /**
     * Informasi tampilan spesifik DVD.
     * Override dari parent untuk menampilkan durasi.
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
            'tipe' => 'DVD',
            'judul' => $this->title,
            'penulis' => $this->author,
            'durasi_menit' => $attributes['duration_minutes'] ?? '-',
        ];
    }

    /**
     * Hitung denda keterlambatan dengan tarif DVD (Rp 2.000/hari).
     *
     * @param int $daysLate Jumlah hari keterlambatan
     * @return float Jumlah denda dalam Rupiah
     */
    public function calculateLateFee(int $daysLate): float
    {
        return $this->lateFeePerDay * max(0, $daysLate);
    }
}
