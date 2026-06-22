<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ItemType;
use App\Interfaces\Borrowable;
use App\Interfaces\Reportable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Model Item — class dasar (parent) untuk semua koleksi perpustakaan.
 *
 * Menggunakan Single Table Inheritance: satu tabel `items`, dibedakan
 * lewat kolom `type`. Subclass: Book, Magazine, Dvd.
 *
 * Mengimplementasi interface Borrowable untuk kontrak peminjaman dan
 * Reportable untuk kontrak pelaporan.
 *
 * @property int $id
 * @property int $category_id
 * @property ItemType $type
 * @property string $title
 * @property string $author
 * @property string|null $publisher
 * @property int $year
 * @property string $code
 * @property int $total_stock
 * @property int $available_stock
 * @property string|null $cover_image
 * @property array|null $attributes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Item extends Model implements Borrowable, Reportable
{
    use HasFactory;

    /**
     * Nama tabel database — semua subclass menggunakan tabel yang sama.
     */
    protected $table = 'items';

    /**
     * Tarif denda per hari keterlambatan (default, akan di-override oleh subclass).
     */
    protected float $lateFeePerDay = 1000.0;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'type',
        'title',
        'author',
        'publisher',
        'year',
        'code',
        'total_stock',
        'available_stock',
        'cover_image',
        'attributes',
    ];

    /**
     * Definisi cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'attributes' => 'array',
            'year' => 'integer',
            'total_stock' => 'integer',
            'available_stock' => 'integer',
        ];
    }

    /**
     * Override newFromBuilder untuk instansiasi subclass yang benar.
     *
     * Ketika Eloquent mengambil data dari database, method ini memastikan
     * instance yang dibuat sesuai dengan kolom `type` — Book, Magazine, atau Dvd.
     * Ini adalah inti dari Single Table Inheritance di Eloquent.
     *
     * @param array<string, mixed> $attributes
     * @param string|null $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null): static
    {
        $type = $attributes->type ?? ($attributes['type'] ?? null);

        $model = match ($type) {
            'book', ItemType::Book->value => new Book(),
            'magazine', ItemType::Magazine->value => new Magazine(),
            'dvd', ItemType::Dvd->value => new Dvd(),
            default => null,
        };

        if ($model !== null) {
            $model->exists = true;
            $model->setRawAttributes((array) $attributes, true);
        } else {
            $model = parent::newFromBuilder($attributes, $connection);
        }

        $model->setConnection($connection ?: $this->getConnectionName());
        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Accessor: URL lengkap cover image.
     *
     * @return Attribute<string|null, never>
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cover_image
                ? Storage::url($this->cover_image)
                : null,
        );
    }

    /**
     * Relasi: kategori yang memiliki item ini.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: daftar peminjaman untuk item ini.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Informasi tampilan spesifik per tipe item.
     * Method ini akan di-override oleh subclass (Book, Magazine, Dvd).
     *
     * @return array<string, mixed> Data informasi spesifik tipe
     */
    public function displayInfo(): array
    {
        return [
            'tipe' => $this->type->value,
            'judul' => $this->title,
            'penulis' => $this->author,
        ];
    }

    /**
     * Hitung denda keterlambatan berdasarkan jumlah hari terlambat.
     * Method ini akan di-override oleh subclass dengan tarif berbeda.
     *
     * @param int $daysLate Jumlah hari keterlambatan
     * @return float Jumlah denda dalam Rupiah
     */
    public function calculateLateFee(int $daysLate): float
    {
        return $this->lateFeePerDay * max(0, $daysLate);
    }

    /**
     * Ambil tarif denda per hari untuk item ini.
     */
    public function getLateFeePerDay(): float
    {
        return $this->lateFeePerDay;
    }

    // ========================================
    // Implementasi Interface Borrowable
    // ========================================

    /**
     * {@inheritDoc}
     */
    public function isAvailable(): bool
    {
        return $this->available_stock > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function markBorrowed(): void
    {
        $this->decrement('available_stock');
        $this->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function markReturned(): void
    {
        $this->increment('available_stock');
        $this->refresh();
    }

    // ========================================
    // Implementasi Interface Reportable
    // ========================================

    /**
     * {@inheritDoc}
     */
    public function toReportArray(): array
    {
        return [
            'id' => $this->id,
            'tipe' => $this->type->value,
            'judul' => $this->title,
            'penulis' => $this->author,
            'penerbit' => $this->publisher,
            'tahun' => $this->year,
            'kode' => $this->code,
            'stok_total' => $this->total_stock,
            'stok_tersedia' => $this->available_stock,
            'kategori' => $this->category?->name,
            'info_spesifik' => $this->displayInfo(),
        ];
    }
}
