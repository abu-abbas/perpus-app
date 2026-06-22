<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Category — kategori koleksi perpustakaan.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Category extends Model implements Reportable
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relasi: item-item yang termasuk dalam kategori ini.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Konversi kategori ke array untuk keperluan laporan.
     *
     * @return array<string, mixed>
     */
    public function toReportArray(): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->name,
            'deskripsi' => $this->description,
            'jumlah_item' => $this->items()->count(),
        ];
    }
}
