<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FineReason;
use App\Models\Loan;
use App\ValueObjects\FineAmount;

/**
 * Service untuk menghitung denda keterlambatan peminjaman.
 *
 * Mengimplementasikan SIMULASI METHOD OVERLOADING (poin h ujian).
 *
 * PHP tidak mendukung method overloading bawaan seperti Java/C#.
 * Sebagai gantinya, digunakan parameter default/opsional yang memungkinkan
 * method dipanggil dengan 1, 2, atau 3 argumen — masing-masing menghasilkan
 * perilaku yang berbeda, layaknya overloaded methods.
 */
class FineCalculator
{
    /**
     * Hitung denda berdasarkan data peminjaman.
     *
     * SIMULASI METHOD OVERLOADING melalui parameter default/opsional:
     *
     * Variasi 1 — `calculate($loan)`:
     *   Hitung denda menggunakan tarif default dari subclass item (Book=500, Magazine=1000, Dvd=2000).
     *
     * Variasi 2 — `calculate($loan, 750.0)`:
     *   Hitung denda menggunakan tarif custom per hari, abaikan tarif default item.
     *
     * Variasi 3 — `calculate($loan, 750.0, 30)`:
     *   Hitung denda menggunakan tarif custom dengan batas maksimum hari yang dihitung.
     *   Berguna untuk mencegah denda yang terlalu besar pada keterlambatan sangat lama.
     *
     * @param Loan $loan Data peminjaman yang akan dihitung dendanya
     * @param float|null $customRatePerDay Tarif custom per hari (opsional)
     * @param int|null $maxDays Batas maksimum hari yang dihitung (opsional)
     * @return FineAmount Value object berisi jumlah denda dan alasan
     */
    public function calculate(
        Loan $loan,
        ?float $customRatePerDay = null,
        ?int $maxDays = null,
    ): FineAmount {
        $daysLate = $loan->daysLate();

        // Terapkan batas maksimum hari jika parameter diberikan (variasi 3)
        if ($maxDays !== null) {
            $daysLate = min($daysLate, $maxDays);
        }

        // Tentukan tarif per hari: custom (variasi 2/3) atau default dari subclass item (variasi 1)
        $ratePerDay = $customRatePerDay ?? $loan->item->getLateFeePerDay();

        $amount = $ratePerDay * $daysLate;

        // Tentukan alasan denda berdasarkan jumlah hari terlambat
        $reason = $this->determineReason($daysLate);

        return new FineAmount(
            amount: $amount,
            reason: $reason,
        );
    }

    /**
     * Tentukan alasan denda berdasarkan jumlah hari keterlambatan.
     *
     * @param int $daysLate Jumlah hari keterlambatan
     * @return FineReason Alasan denda
     */
    private function determineReason(int $daysLate): FineReason
    {
        if ($daysLate > 90) {
            return FineReason::Lost;
        }

        return FineReason::Late;
    }
}
