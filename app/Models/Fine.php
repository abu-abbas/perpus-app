<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FineReason;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

/**
 * Model Fine — denda atas peminjaman yang terlambat/rusak/hilang.
 *
 * @property int $id
 * @property int $loan_id
 * @property float $amount
 * @property FineReason $reason
 * @property PaymentStatus $paid_status
 * @property Carbon|null $paid_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Fine extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'loan_id',
        'amount',
        'reason',
        'paid_status',
        'paid_date',
    ];

    /**
     * Definisi cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reason' => FineReason::class,
            'paid_status' => PaymentStatus::class,
            'amount' => 'float',
        ];
    }

    /**
     * Accessor & Mutator untuk paid_date.
     * Memastikan format tanggal disimpan sebagai Y-m-d di SQLite,
     * tetapi tetap dapat diakses sebagai objek Carbon.
     */
    protected function paidDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null
        );
    }

    /**
     * Relasi: peminjaman terkait denda ini.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Cek apakah denda sudah lunas.
     */
    public function isPaid(): bool
    {
        return $this->paid_status === PaymentStatus::Paid;
    }
}
