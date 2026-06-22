<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller untuk dashboard dan laporan.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /** Ringkasan statistik dashboard. */
    public function summary(): JsonResponse
    {
        $data = $this->reportService->getDashboardSummary();

        return response()->json(['data' => $data]);
    }

    /** Laporan peminjaman teragregasi. */
    public function loanReport(Request $request): JsonResponse
    {
        $data = $this->reportService->getLoanReport($request->all());

        return response()->json(['data' => $data]);
    }

    /** Export laporan peminjaman ke PDF atau Excel. */
    public function exportLoanReport(Request $request, string $format): mixed
    {
        $exporter = app(\App\Exports\LoanReportExport::class);

        return $exporter->export($format, $request->all());
    }
}
