<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ItemType;
use App\Exceptions\FileImportException;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * Service untuk import data item secara massal dari file CSV/Excel.
 *
 * Menggunakan fast-excel untuk parsing file dan melakukan validasi per baris.
 */
class ImportService
{
    /**
     * Import item dari file CSV/Excel.
     *
     * Setiap baris divalidasi secara individual — baris yang gagal
     * tidak menghentikan proses import baris lainnya.
     *
     * @param UploadedFile $file File CSV/Excel yang diupload
     * @return array{success: int, failed: int, errors: array<int, array<string, mixed>>}
     *
     * @throws FileImportException Jika file tidak bisa dibaca sama sekali
     */
    public function importItems(UploadedFile $file): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 0;

        try {
            $rows = (new FastExcel())->import($file->getRealPath());
        } catch (\Throwable $e) {
            throw new FileImportException(
                $file->getClientOriginalName(),
                'File tidak bisa dibaca: ' . $e->getMessage(),
                $e,
            );
        }

        // Proses setiap baris — menggunakan foreach (poin d)
        foreach ($rows as $row) {
            $rowNumber++;

            $data = [
                'category_id' => $row['category_id'] ?? $row['kategori_id'] ?? null,
                'type' => $row['type'] ?? $row['tipe'] ?? null,
                'title' => $row['title'] ?? $row['judul'] ?? null,
                'author' => $row['author'] ?? $row['penulis'] ?? null,
                'publisher' => $row['publisher'] ?? $row['penerbit'] ?? null,
                'year' => $row['year'] ?? $row['tahun'] ?? null,
                'code' => $row['code'] ?? $row['kode'] ?? null,
                'total_stock' => $row['total_stock'] ?? $row['stok_total'] ?? null,
                'available_stock' => $row['available_stock'] ?? $row['stok_tersedia'] ?? null,
            ];

            // Validasi baris
            $validator = Validator::make($data, [
                'category_id' => 'required|integer|exists:categories,id',
                'type' => 'required|in:book,magazine,dvd',
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'publisher' => 'nullable|string|max:255',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'code' => 'required|string|unique:items,code',
                'total_stock' => 'required|integer|min:0',
                'available_stock' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = [
                    'baris' => $rowNumber,
                    'data' => $data,
                    'pesan' => $validator->errors()->all(),
                ];

                continue;
            }

            // Tambah atribut spesifik berdasarkan tipe
            $attributes = [];
            $type = ItemType::from($data['type']);

            if ($type === ItemType::Book) {
                $attributes['pages'] = (int) ($row['pages'] ?? $row['halaman'] ?? 0);
            } elseif ($type === ItemType::Magazine) {
                $attributes['edition_number'] = $row['edition_number'] ?? $row['nomor_edisi'] ?? '';
            } elseif ($type === ItemType::Dvd) {
                $attributes['duration_minutes'] = (int) ($row['duration_minutes'] ?? $row['durasi_menit'] ?? 0);
            }

            $data['attributes'] = json_encode($attributes);

            try {
                Item::create($data);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = [
                    'baris' => $rowNumber,
                    'data' => $data,
                    'pesan' => [$e->getMessage()],
                ];
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}
