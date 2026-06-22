<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exceptions\ExportFailedException;
use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export laporan peminjaman ke PDF atau Excel.
 */
class LoanReportExport
{
    /**
     * Export laporan sesuai format.
     *
     * @param string $format Format export: 'pdf' atau 'excel'
     * @param array<string, mixed> $filters Filter: date_from, date_to
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    public function export(string $format, array $filters = []): StreamedResponse|\Illuminate\Http\Response
    {
        try {
            return match ($format) {
                'pdf' => $this->exportPdf($filters),
                'excel' => $this->exportExcel($filters),
                default => throw new ExportFailedException($format, 'Format tidak didukung.'),
            };
        } catch (ExportFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ExportFailedException($format, $e->getMessage(), $e);
        }
    }

    /**
     * Ambil data peminjaman sesuai filter.
     *
     * @param array<string, mixed> $filters
     * @return \Illuminate\Database\Eloquent\Collection<int, Loan>
     */
    private function getLoans(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $query = Loan::with(['member', 'item', 'librarian', 'fine']);

        if (! empty($filters['date_from'])) {
            $query->where('loan_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('loan_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('loan_date', 'desc')->get();
    }

    private function exportPdf(array $filters): \Illuminate\Http\Response
    {
        $loans = $this->getLoans($filters);
        $pdf = Pdf::loadView('exports.loan-report-pdf', ['loans' => $loans]);

        return $pdf->download('laporan-peminjaman.pdf');
    }

    private function exportExcel(array $filters): StreamedResponse
    {
        $loans = $this->getLoans($filters);

        return (new FastExcel($loans))->download('laporan-peminjaman.xlsx', function (Loan $loan) {
            return [
                'ID' => $loan->id,
                'Anggota' => $loan->member?->full_name,
                'Item' => $loan->item?->title,
                'Tipe Item' => $loan->item?->type->value,
                'Tgl Pinjam' => $loan->loan_date?->format('Y-m-d'),
                'Tgl Jatuh Tempo' => $loan->due_date?->format('Y-m-d'),
                'Tgl Kembali' => $loan->return_date?->format('Y-m-d') ?? '',
                'Status' => $loan->status->value,
                'Denda (Rp)' => $loan->fine?->amount ?? 0,
            ];
        });
    }
}
