<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\Member;

/**
 * Service untuk manajemen anggota perpustakaan.
 *
 * Menangani CRUD anggota dan pembuatan nomor keanggotaan unik.
 */
class MemberService
{
    /**
     * Generate nomor anggota yang unik.
     *
     * Menggunakan loop do-while untuk memastikan nomor yang dihasilkan
     * belum pernah dipakai sebelumnya (poin d — kontrol struktur).
     *
     * @return string Nomor anggota unik, format: MBR-YYYYXXXX
     */
    public function generateMemberNumber(): string
    {
        $year = date('Y');
        $memberNumber = '';

        // Loop do-while: terus generate sampai dapat nomor yang unik (poin d)
        do {
            $random = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $memberNumber = "MBR-{$year}{$random}";
        } while (Member::where('member_number', $memberNumber)->exists());

        return $memberNumber;
    }

    /**
     * Buat anggota baru dengan nomor anggota otomatis.
     *
     * @param array<string, mixed> $data Data anggota dari request
     * @return Member Instance anggota yang sudah tersimpan
     */
    public function create(array $data): Member
    {
        $data['member_number'] = $this->generateMemberNumber();
        $data['join_date'] = $data['join_date'] ?? now()->toDateString();
        $data['status'] = $data['status'] ?? MemberStatus::Active;

        return Member::create($data);
    }

    /**
     * Update data anggota.
     *
     * @param Member $member Instance anggota yang akan diupdate
     * @param array<string, mixed> $data Data baru
     * @return Member Instance anggota yang sudah diupdate
     */
    public function update(Member $member, array $data): Member
    {
        $member->update($data);

        return $member->fresh();
    }

    /**
     * Hapus anggota.
     *
     * @param Member $member Instance anggota yang akan dihapus
     */
    public function delete(Member $member): void
    {
        $member->delete();
    }

    /**
     * Ambil daftar anggota terpaginasi dengan filter pencarian.
     *
     * @param array<string, mixed> $filters Filter pencarian
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Member::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
}
