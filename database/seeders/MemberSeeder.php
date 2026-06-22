<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder untuk data anggota perpustakaan demo.
 */
class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'member_number' => 'MBR-20240001',
                'full_name' => 'Budi Santoso',
                'identity_number' => '3201010101900001',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 1, Jakarta Pusat',
                'join_date' => Carbon::parse('2024-01-15'),
                'status' => MemberStatus::Active,
            ],
            [
                'member_number' => 'MBR-20240002',
                'full_name' => 'Siti Nurhaliza',
                'identity_number' => '3201010101910002',
                'phone' => '081234567891',
                'address' => 'Jl. Sudirman No. 10, Jakarta Selatan',
                'join_date' => Carbon::parse('2024-02-20'),
                'status' => MemberStatus::Active,
            ],
            [
                'member_number' => 'MBR-20240003',
                'full_name' => 'Ahmad Dahlan',
                'identity_number' => '3201010101920003',
                'phone' => '081234567892',
                'address' => 'Jl. Diponegoro No. 5, Bandung',
                'join_date' => Carbon::parse('2024-03-10'),
                'status' => MemberStatus::Active,
            ],
            [
                'member_number' => 'MBR-20240004',
                'full_name' => 'Dewi Sartika',
                'identity_number' => '3201010101930004',
                'phone' => '081234567893',
                'address' => 'Jl. Gatot Subroto No. 15, Surabaya',
                'join_date' => Carbon::parse('2024-04-01'),
                'status' => MemberStatus::Inactive,
            ],
            [
                'member_number' => 'MBR-20240005',
                'full_name' => 'Raden Ajeng Kartini',
                'identity_number' => '3201010101940005',
                'phone' => '081234567894',
                'address' => 'Jl. Kartini No. 20, Yogyakarta',
                'join_date' => Carbon::parse('2024-05-15'),
                'status' => MemberStatus::Suspended,
            ],
        ];

        foreach ($members as $member) {
            Member::create($member);
        }
    }
}
