<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exceptions\ExportFailedException;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export daftar item koleksi ke PDF atau Excel.
 */
class ItemsExport
{
    /**
     * Export item sesuai format yang diminta.
     *
     * @param string $format Format export: 'pdf' atau 'excel'
     * @return StreamedResponse|\Illuminate\Http\Response
     *
     * @throws ExportFailedException Jika format tidak valid atau proses gagal
     */
    public function export(string $format): StreamedResponse|\Illuminate\Http\Response
    {
        try {
            return match ($format) {
                'pdf' => $this->exportPdf(),
                'excel' => $this->exportExcel(),
                default => throw new ExportFailedException($format, 'Format tidak didukung. Gunakan pdf atau excel.'),
            };
        } catch (ExportFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ExportFailedException($format, $e->getMessage(), $e);
        }
    }

    /**
     * Export ke PDF menggunakan dompdf.
     */
    private function exportPdf(): \Illuminate\Http\Response
    {
        $items = Item::with('category')->get();

        $pdf = Pdf::loadView('exports.items-pdf', ['items' => $items]);

        return $pdf->download('daftar-item-perpustakaan.pdf');
    }

    /**
     * Export ke Excel menggunakan fast-excel.
     */
    private function exportExcel(): StreamedResponse
    {
        $items = Item::with('category')->get();

        return (new FastExcel($items))->download('daftar-item-perpustakaan.xlsx', function (Item $item) {
            return [
                'ID' => $item->id,
                'Kategori' => $item->category?->name,
                'Tipe' => $item->type->value,
                'Judul' => $item->title,
                'Penulis' => $item->author,
                'Penerbit' => $item->publisher,
                'Tahun' => $item->year,
                'Kode' => $item->code,
                'Stok Total' => $item->total_stock,
                'Stok Tersedia' => $item->available_stock,
            ];
        });
    }
}
