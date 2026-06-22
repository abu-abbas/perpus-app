<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FineReason;
use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Models\Fine;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder untuk data peminjaman demo.
 * Membuat contoh peminjaman: aktif, sudah dikembalikan, terlambat, dan dengan denda.
 */
class LoanSeeder extends Seeder
{
    public function run(): void
    {
        $pustakawan = User::where('email', 'pustakawan@perpus.test')->first();

        // Peminjaman 1: Aktif, belum jatuh tempo
        $loan1 = Loan::create([
            'member_id' => 1,
            'item_id' => 1,
            'librarian_id' => $pustakawan->id,
            'loan_date' => Carbon::today()->subDays(3),
            'due_date' => Carbon::today()->addDays(11),
            'status' => LoanStatus::Borrowed,
        ]);
        Item::where('id', 1)->decrement('available_stock');

        // Peminjaman 2: Sudah dikembalikan tepat waktu
        Loan::create([
            'member_id' => 2,
            'item_id' => 3,
            'librarian_id' => $pustakawan->id,
            'loan_date' => Carbon::today()->subDays(20),
            'due_date' => Carbon::today()->subDays(6),
            'return_date' => Carbon::today()->subDays(8),
            'status' => LoanStatus::Returned,
        ]);

        // Peminjaman 3: Terlambat (masih dipinjam, lewat jatuh tempo)
        $loan3 = Loan::create([
            'member_id' => 1,
            'item_id' => 5,
            'librarian_id' => $pustakawan->id,
            'loan_date' => Carbon::today()->subDays(21),
            'due_date' => Carbon::today()->subDays(7),
            'status' => LoanStatus::Borrowed,
        ]);
        Item::where('id', 5)->decrement('available_stock');

        // Peminjaman 4: Dikembalikan terlambat — ada denda
        $loan4 = Loan::create([
            'member_id' => 3,
            'item_id' => 6,
            'librarian_id' => $pustakawan->id,
            'loan_date' => Carbon::today()->subDays(25),
            'due_date' => Carbon::today()->subDays(11),
            'return_date' => Carbon::today()->subDays(5),
            'status' => LoanStatus::Returned,
        ]);

        // Denda untuk peminjaman 4 (terlambat 6 hari, majalah Rp1000/hari)
        Fine::create([
            'loan_id' => $loan4->id,
            'amount' => 6000.00,
            'reason' => FineReason::Late,
            'paid_status' => PaymentStatus::Unpaid,
        ]);

        // Peminjaman 5: Dikembalikan terlambat — denda sudah lunas
        $loan5 = Loan::create([
            'member_id' => 2,
            'item_id' => 9,
            'librarian_id' => $pustakawan->id,
            'loan_date' => Carbon::today()->subDays(30),
            'due_date' => Carbon::today()->subDays(16),
            'return_date' => Carbon::today()->subDays(10),
            'status' => LoanStatus::Returned,
        ]);

        // Denda untuk peminjaman 5 (terlambat 6 hari, DVD Rp2000/hari, sudah lunas)
        Fine::create([
            'loan_id' => $loan5->id,
            'amount' => 12000.00,
            'reason' => FineReason::Late,
            'paid_status' => PaymentStatus::Paid,
            'paid_date' => Carbon::today()->subDays(9),
        ]);
    }
}
