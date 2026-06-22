<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\MemberStatus;
use App\Exceptions\FineUnpaidException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\LoanAlreadyReturnedException;
use App\Exceptions\MaxLoanExceededException;
use App\Exceptions\MemberNotActiveException;
use App\Models\Item;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Service untuk manajemen peminjaman dan pengembalian item perpustakaan.
 *
 * Menangani validasi bisnis, perubahan stok, dan perhitungan denda.
 */
class LoanService
{
    public function __construct(
        private readonly FineCalculator $fineCalculator,
        private readonly FineService $fineService,
    ) {}

    /**
     * Proses peminjaman item oleh anggota.
     *
     * Validasi bisnis:
     * 1. Anggota harus berstatus aktif
     * 2. Anggota tidak boleh punya denda belum lunas
     * 3. Anggota tidak boleh punya lebih dari 3 peminjaman aktif
     * 4. Item harus tersedia (stok > 0)
     *
     * @param Member $member Anggota yang meminjam
     * @param Item $item Item yang dipinjam
     * @param User $librarian Pustakawan yang memproses
     * @return Loan Instance peminjaman yang sudah tersimpan
     *
     * @throws MemberNotActiveException Jika anggota tidak aktif
     * @throws FineUnpaidException Jika ada denda belum lunas
     * @throws MaxLoanExceededException Jika batas pinjam tercapai
     * @throws InsufficientStockException Jika stok habis
     */
    public function borrow(Member $member, Item $item, User $librarian): Loan
    {
        // Validasi: anggota harus aktif
        if ($member->status !== MemberStatus::Active) {
            throw new MemberNotActiveException($member->full_name, $member->status);
        }

        // Validasi: tidak boleh ada denda belum lunas
        $unpaidFines = $member->unpaidFinesCount();
        if ($unpaidFines > 0) {
            throw new FineUnpaidException($member->full_name, $unpaidFines);
        }

        // Validasi: maksimum 3 peminjaman aktif
        $activeLoans = $member->activeLoansCount();
        if ($activeLoans >= 3) {
            throw new MaxLoanExceededException($member->full_name, $activeLoans);
        }

        // Validasi: stok tersedia — memanfaatkan interface Borrowable (poin h)
        if (! $item->isAvailable()) {
            throw new InsufficientStockException($item->title, $item->available_stock);
        }

        // Kurangi stok — memanfaatkan interface Borrowable
        $item->markBorrowed();

        // Buat record peminjaman
        $loan = Loan::create([
            'member_id' => $member->id,
            'item_id' => $item->id,
            'librarian_id' => $librarian->id,
            'loan_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => LoanStatus::Borrowed,
        ]);

        return $loan->load(['member', 'item', 'librarian']);
    }

    /**
     * Proses pengembalian item.
     *
     * Menghitung denda otomatis jika terlambat menggunakan if-elseif-else (poin d).
     *
     * @param Loan $loan Instance peminjaman yang dikembalikan
     * @return Loan Instance peminjaman yang sudah diupdate
     *
     * @throws LoanAlreadyReturnedException Jika sudah dikembalikan sebelumnya
     */
    public function return(Loan $loan): Loan
    {
        // Validasi: belum dikembalikan
        if ($loan->status === LoanStatus::Returned) {
            throw new LoanAlreadyReturnedException($loan->id);
        }

        $returnDate = Carbon::today();
        $daysLate = $loan->daysLate();

        // Tentukan status dan proses denda — if-elseif-else (poin d)
        if ($daysLate === 0) {
            // Tepat waktu — tidak ada denda
            $loan->update([
                'return_date' => $returnDate,
                'status' => LoanStatus::Returned,
            ]);
        } elseif ($daysLate > 0 && $daysLate <= 90) {
            // Terlambat — kenakan denda keterlambatan
            $fineAmount = $this->fineCalculator->calculate($loan);

            $loan->update([
                'return_date' => $returnDate,
                'status' => LoanStatus::Returned,
            ]);

            // Buat record denda
            $this->fineService->createFine($loan, $fineAmount);
        } else {
            // Sangat terlambat (> 90 hari) — dianggap hampir hilang, denda maksimum
            $fineAmount = $this->fineCalculator->calculate($loan, null, 90);

            $loan->update([
                'return_date' => $returnDate,
                'status' => LoanStatus::Returned,
            ]);

            $this->fineService->createFine($loan, $fineAmount);
        }

        // Kembalikan stok — memanfaatkan interface Borrowable
        $loan->item->markReturned();

        return $loan->fresh()->load(['member', 'item', 'librarian', 'fine']);
    }

    /**
     * Ambil daftar peminjaman terpaginasi dengan filter.
     *
     * @param array<string, mixed> $filters Filter pencarian
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Loan::with(['member', 'item', 'librarian', 'fine']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('member', fn ($mq) => $mq->where('full_name', 'like', "%{$search}%"))
                    ->orWhereHas('item', fn ($iq) => $iq->where('title', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
}
