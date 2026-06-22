<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\FineAlreadyPaidException;
use App\Models\Fine;
use App\Models\Loan;
use App\ValueObjects\FineAmount;
use Illuminate\Support\Carbon;

/**
 * Service untuk manajemen denda perpustakaan.
 *
 * Menangani pembuatan denda, pembayaran, dan daftar denda terpaginasi.
 */
class FineService
{
    /**
     * Buat record denda baru untuk peminjaman.
     *
     * @param Loan $loan Peminjaman terkait
     * @param FineAmount $fineAmount Value object berisi jumlah dan alasan denda
     * @return Fine Instance denda yang sudah tersimpan
     */
    public function createFine(Loan $loan, FineAmount $fineAmount): Fine
    {
        return Fine::create([
            'loan_id' => $loan->id,
            'amount' => $fineAmount->amount,
            'reason' => $fineAmount->reason,
            'paid_status' => PaymentStatus::Unpaid,
        ]);
    }

    /**
     * Tandai denda sebagai lunas.
     *
     * @param Fine $fine Instance denda
     * @return Fine Instance denda yang sudah diupdate
     *
     * @throws FineAlreadyPaidException Jika denda sudah dibayar sebelumnya
     */
    public function markPaid(Fine $fine): Fine
    {
        if ($fine->isPaid()) {
            throw new FineAlreadyPaidException($fine->id);
        }

        $fine->update([
            'paid_status' => PaymentStatus::Paid,
            'paid_date' => Carbon::today(),
        ]);

        return $fine->fresh();
    }

    /**
     * Ambil daftar denda terpaginasi dengan filter.
     *
     * @param array<string, mixed> $filters Filter pencarian
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listFines(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Fine::with(['loan.member', 'loan.item']);

        if (! empty($filters['paid_status'])) {
            $query->where('paid_status', $filters['paid_status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
}
