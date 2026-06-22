<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Kontrak untuk entitas yang bisa dimasukkan ke dalam laporan.
 *
 * Interface ini memastikan setiap model yang perlu diekspor
 * ke laporan memiliki format array yang konsisten.
 */
interface Reportable
{
    /**
     * Konversi entitas ke array untuk keperluan laporan/export.
     *
     * @return array<string, mixed> Data entitas dalam format array
     */
    public function toReportArray(): array;
}
