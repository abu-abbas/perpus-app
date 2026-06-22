<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentStatus;
use App\Models\Fine;
use App\Models\Item;
use App\Models\Loan;
use App\Models\Member;

/**
 * Service untuk laporan dan dashboard perpustakaan.
 *
 * Mengandung operasi array_map, array_filter, dan array_reduce
 * untuk agregasi data (poin f ujian).
 *
 * Juga mendemonstrasikan polimorfisme nyata: loop atas koleksi Item campuran
 * (Book/Magazine/Dvd) yang memanggil displayInfo() dan calculateLateFee()
 * melalui referensi tipe dasar Item (poin h).
 */
class ReportService
{
    /**
     * Ambil ringkasan statistik untuk dashboard.
     *
     * @return array<string, mixed> Data ringkasan dashboard
     */
    public function getDashboardSummary(): array
    {
        return [
            'total_items' => Item::count(),
            'total_members' => Member::count(),
            'active_members' => Member::where('status', MemberStatus::Active)->count(),
            'active_loans' => Loan::where('status', LoanStatus::Borrowed)->count(),
            'overdue_loans' => Loan::where('status', LoanStatus::Borrowed)
                ->where('due_date', '<', now())
                ->count(),
            'total_fines_unpaid' => Fine::where('paid_status', PaymentStatus::Unpaid)->sum('amount'),
            'recent_loans' => Loan::with(['member', 'item'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Ambil laporan peminjaman teragregasi.
     *
     * Menggunakan array_map, array_filter, dan array_reduce untuk
     * mengolah data (poin f — operasi array).
     *
     * @param array<string, mixed> $filters Filter: date_from, date_to
     * @return array<string, mixed> Data laporan teragregasi
     */
    public function getLoanReport(array $filters = []): array
    {
        $query = Loan::with(['member', 'item.category', 'librarian', 'fine']);

        if (! empty($filters['date_from'])) {
            $query->where('loan_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('loan_date', '<=', $filters['date_to']);
        }

        $loans = $query->get();

        // === POLIMORFISME NYATA (poin h) ===
        // Ambil koleksi Item campuran (Book/Magazine/Dvd) dan panggil
        // displayInfo() serta calculateLateFee() melalui referensi tipe dasar.
        $items = Item::all();
        $itemDisplayInfos = [];

        foreach ($items as $item) {
            // Setiap item (Book/Magazine/Dvd) memanggil displayInfo() versi masing-masing
            $itemDisplayInfos[] = [
                'id' => $item->id,
                'display_info' => $item->displayInfo(),
                'denda_per_hari' => $item->calculateLateFee(1),
            ];
        }

        // === OPERASI ARRAY (poin f) ===

        // array_map: transformasi setiap peminjaman ke format laporan
        $loanReports = array_map(function (Loan $loan) {
            return $loan->toReportArray();
        }, $loans->all());

        // array_filter: hanya peminjaman yang terlambat
        $overdueLoans = array_filter($loanReports, function (array $report) {
            return $report['terlambat'] === true || $report['hari_terlambat'] > 0;
        });

        // array_reduce: total denda dari semua peminjaman yang punya denda
        $totalFines = array_reduce($loans->all(), function (float $carry, Loan $loan) {
            return $carry + ($loan->fine?->amount ?? 0);
        }, 0.0);

        return [
            'total_peminjaman' => count($loanReports),
            'peminjaman_terlambat' => count($overdueLoans),
            'total_denda' => $totalFines,
            'detail_peminjaman' => array_values($loanReports),
            'item_info_polimorfisme' => $itemDisplayInfos,
        ];
    }
}
