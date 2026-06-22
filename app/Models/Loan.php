<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanStatus;
use App\Interfaces\Reportable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model Loan — peminjaman item perpustakaan.
 *
 * @property int $id
 * @property int $member_id
 * @property int $item_id
 * @property int $librarian_id
 * @property Carbon $loan_date
 * @property Carbon $due_date
 * @property Carbon|null $return_date
 * @property LoanStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $is_overdue
 */
class Loan extends Model implements Reportable
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'item_id',
        'librarian_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
    ];

    /**
     * Definisi cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'loan_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
        ];
    }

    /**
     * Accessor: cek apakah peminjaman sudah lewat jatuh tempo.
     * Properti turunan (computed) — mendemonstrasikan Attribute::make (poin h).
     *
     * @return Attribute<bool, never>
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === LoanStatus::Borrowed
                && $this->due_date->isPast(),
        );
    }

    /**
     * Hitung jumlah hari keterlambatan.
     * Mengembalikan 0 jika belum lewat jatuh tempo.
     */
    public function daysLate(): int
    {
        $returnDate = Carbon::parse($this->return_date ?? Carbon::today())->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();

        return max(0, (int) $dueDate->diffInDays($returnDate, false));
    }

    /**
     * Relasi: anggota yang meminjam.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relasi: item yang dipinjam.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi: pustakawan yang memproses peminjaman.
     */
    public function librarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'librarian_id');
    }

    /**
     * Relasi: denda terkait peminjaman ini.
     */
    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }

    /**
     * Konversi peminjaman ke array untuk keperluan laporan.
     *
     * @return array<string, mixed>
     */
    public function toReportArray(): array
    {
        return [
            'id' => $this->id,
            'anggota' => $this->member?->full_name,
            'member_name' => $this->member?->full_name,
            'item' => $this->item?->title,
            'item_title' => $this->item?->title,
            'tipe_item' => $this->item?->type->value,
            'pustakawan' => $this->librarian?->name,
            'tanggal_pinjam' => $this->loan_date->format('Y-m-d'),
            'tanggal_jatuh_tempo' => $this->due_date->format('Y-m-d'),
            'tanggal_kembali' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'terlambat' => $this->is_overdue,
            'hari_terlambat' => $this->daysLate(),
        ];
    }
}
