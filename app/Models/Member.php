<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemberStatus;
use App\Interfaces\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model Member — anggota perpustakaan.
 *
 * @property int $id
 * @property string $member_number
 * @property string $full_name
 * @property string $identity_number
 * @property string|null $phone
 * @property string|null $address
 * @property \Illuminate\Support\Carbon $join_date
 * @property MemberStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Member extends Model implements Reportable
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'member_number',
        'full_name',
        'identity_number',
        'phone',
        'address',
        'join_date',
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
            'status' => MemberStatus::class,
            'join_date' => 'date',
        ];
    }

    /**
     * Relasi: daftar peminjaman anggota ini.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relasi: daftar denda anggota ini melalui peminjaman.
     */
    public function fines(): HasManyThrough
    {
        return $this->hasManyThrough(Fine::class, Loan::class);
    }

    /**
     * Hitung jumlah peminjaman aktif (status borrowed).
     */
    public function activeLoansCount(): int
    {
        return $this->loans()
            ->where('status', 'borrowed')
            ->count();
    }

    /**
     * Hitung jumlah denda belum lunas.
     */
    public function unpaidFinesCount(): int
    {
        return $this->fines()
            ->where('paid_status', 'unpaid')
            ->count();
    }

    /**
     * Konversi anggota ke array untuk keperluan laporan.
     *
     * @return array<string, mixed>
     */
    public function toReportArray(): array
    {
        return [
            'id' => $this->id,
            'nomor_anggota' => $this->member_number,
            'nama_lengkap' => $this->full_name,
            'nomor_identitas' => $this->identity_number,
            'telepon' => $this->phone,
            'tanggal_bergabung' => $this->join_date->format('Y-m-d'),
            'status' => $this->status->value,
            'total_peminjaman' => $this->loans()->count(),
        ];
    }
}
