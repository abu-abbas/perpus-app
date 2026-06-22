<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\FineReason;
use App\Enums\ItemType;
use App\Models\Book;
use App\Models\Category;
use App\Models\Dvd;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Services\FineCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Unit test untuk menguji FineCalculator (Simulasi Overloading).
 */
class FineCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private FineCalculator $calculator;
    private Loan $loan;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new FineCalculator();

        // Buat data pendukung
        $category = Category::create(['name' => 'Sains']);
        $this->book = Book::create([
            'category_id' => $category->id,
            'type' => ItemType::Book,
            'title' => 'Fisika Kuantum',
            'author' => 'Dr. Einstein',
            'year' => 2020,
            'code' => 'BK-PHYS-01',
            'total_stock' => 10,
            'available_stock' => 10,
            'attributes' => ['attributes' => ['pages' => 300]],
        ]);

        $member = Member::create([
            'member_number' => 'MBR-20260001',
            'full_name' => 'Budi Santoso',
            'identity_number' => '3273010101010001',
            'join_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $librarian = User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Buat loan dengan tanggal pinjam 20 hari lalu, due date 14 hari setelah pinjam (terlambat 6 hari)
        $this->loan = Loan::create([
            'member_id' => $member->id,
            'item_id' => $this->book->id,
            'librarian_id' => $librarian->id,
            'loan_date' => Carbon::today()->subDays(20),
            'due_date' => Carbon::today()->subDays(6), // Jatuh tempo 6 hari lalu
            'status' => 'borrowed',
        ]);
    }

    /**
     * Uji Variasi 1: Hitung denda dengan tarif default (Book = 500/hari).
     * Terlambat 6 hari -> 500 * 6 = 3.000
     */
    public function test_calculate_with_default_rate(): void
    {
        $fineAmount = $this->calculator->calculate($this->loan);

        $this->assertEquals(3000.0, $fineAmount->amount);
        $this->assertEquals(FineReason::Late, $fineAmount->reason);
    }

    /**
     * Uji Variasi 2: Hitung denda dengan tarif custom per hari (misal 1.500/hari).
     * Terlambat 6 hari -> 1500 * 6 = 9.000
     */
    public function test_calculate_with_custom_rate(): void
    {
        $fineAmount = $this->calculator->calculate($this->loan, 1500.0);

        $this->assertEquals(9000.0, $fineAmount->amount);
        $this->assertEquals(FineReason::Late, $fineAmount->reason);
    }

    /**
     * Uji Variasi 3: Hitung denda dengan tarif custom dan batas hari maksimal (misal max 4 hari).
     * Terlambat 6 hari terpotong max 4 hari -> 1000 * 4 = 4.000
     */
    public function test_calculate_with_custom_rate_and_max_days(): void
    {
        $fineAmount = $this->calculator->calculate($this->loan, 1000.0, 4);

        $this->assertEquals(4000.0, $fineAmount->amount);
        $this->assertEquals(FineReason::Late, $fineAmount->reason);
    }
}
