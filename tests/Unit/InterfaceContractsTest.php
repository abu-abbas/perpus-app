<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Interfaces\Borrowable;
use App\Interfaces\Reportable;
use App\Models\Book;
use App\Models\Category;
use App\Models\Item;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Unit test untuk memverifikasi kontrak interface Borrowable dan Reportable.
 */
class InterfaceContractsTest extends TestCase
{
    use RefreshDatabase;

    private Book $item;
    private Member $member;
    private Loan $loan;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Sains']);
        $this->item = Book::create([
            'category_id' => $category->id,
            'type' => 'book',
            'title' => 'Fisika Kuantum',
            'author' => 'Author A',
            'year' => 2026,
            'code' => 'ISBN-1111',
            'total_stock' => 5,
            'available_stock' => 5,
        ]);

        $this->member = Member::create([
            'member_number' => 'MBR-20269999',
            'full_name' => 'Dewi Sartika',
            'identity_number' => '1234567890',
            'join_date' => '2026-06-20',
            'status' => 'active',
        ]);

        $librarian = User::create([
            'name' => 'Librarian A',
            'email' => 'lib@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $this->loan = Loan::create([
            'member_id' => $this->member->id,
            'item_id' => $this->item->id,
            'librarian_id' => $librarian->id,
            'loan_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => 'borrowed',
        ]);
    }

    /**
     * Uji bahwa Model Item mengimplementasikan Borrowable interface dengan benar.
     */
    public function test_item_implements_borrowable_correctly(): void
    {
        $this->assertInstanceOf(Borrowable::class, $this->item);

        // Awalnya tersedia (stok = 5)
        $this->assertTrue($this->item->isAvailable());

        // Tandai dipinjam -> stok harus berkurang ke 4
        $this->item->markBorrowed();
        $this->assertEquals(4, $this->item->available_stock);

        // Tandai dikembalikan -> stok harus bertambah kembali ke 5
        $this->item->markReturned();
        $this->assertEquals(5, $this->item->available_stock);

        // Set stok tersedia ke 0 untuk uji isAvailable() = false
        $this->item->update(['available_stock' => 0]);
        $this->assertFalse($this->item->isAvailable());
    }

    /**
     * Uji bahwa Model Item, Member, dan Loan mengimplementasikan Reportable interface.
     */
    public function test_models_implement_reportable_correctly(): void
    {
        $this->assertInstanceOf(Reportable::class, $this->item);
        $this->assertInstanceOf(Reportable::class, $this->member);
        $this->assertInstanceOf(Reportable::class, $this->loan);

        // Cek struktur array laporan masing-masing model
        $itemReport = $this->item->toReportArray();
        $this->assertArrayHasKey('id', $itemReport);
        $this->assertArrayHasKey('judul', $itemReport);
        $this->assertArrayHasKey('info_spesifik', $itemReport);

        $memberReport = $this->member->toReportArray();
        $this->assertArrayHasKey('nomor_anggota', $memberReport);
        $this->assertArrayHasKey('nama_lengkap', $memberReport);

        $loanReport = $this->loan->toReportArray();
        $this->assertArrayHasKey('id', $loanReport);
        $this->assertArrayHasKey('member_name', $loanReport);
        $this->assertArrayHasKey('item_title', $loanReport);
    }
}
