<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ItemType;
use App\Enums\LoanStatus;
use App\Enums\MemberStatus;
use App\Models\Book;
use App\Models\Category;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Feature test untuk API Transaksi Peminjaman dan Pengembalian.
 */
class LoanApiTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;
    private Member $member;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::create([
            'name' => 'Librarian A',
            'email' => 'lib@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $this->member = Member::create([
            'member_number' => 'MBR-20260001',
            'full_name' => 'Dewi Persik',
            'identity_number' => '3273010101010002',
            'join_date' => '2026-06-20',
            'status' => MemberStatus::Active,
        ]);

        $category = Category::create(['name' => 'Fiksi']);
        $this->book = Book::create([
            'category_id' => $category->id,
            'type' => ItemType::Book,
            'title' => 'Harry Potter',
            'author' => 'J.K. Rowling',
            'year' => 2005,
            'code' => 'ISBN-HP-01',
            'total_stock' => 5,
            'available_stock' => 5,
        ]);
    }

    /**
     * Uji alur sukses peminjaman baru (mengurangi stok).
     */
    public function test_librarian_can_process_new_loan(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->postJson('/api/loans', [
                'member_id' => $this->member->id,
                'item_id' => $this->book->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'borrowed');

        // Pastikan stok berkurang
        $this->book->refresh();
        $this->assertEquals(4, $this->book->available_stock);
    }

    /**
     * Uji anggota nonaktif dilarang meminjam (422).
     */
    public function test_suspended_member_cannot_borrow(): void
    {
        $this->member->update(['status' => MemberStatus::Suspended]);

        $response = $this->actingAs($this->librarian, 'web')
            ->postJson('/api/loans', [
                'member_id' => $this->member->id,
                'item_id' => $this->book->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'code' => 'MEMBER_NOT_ACTIVE',
            ]);
    }

    /**
     * Uji anggota dengan denda belum lunas dilarang meminjam.
     */
    public function test_member_with_unpaid_fines_cannot_borrow(): void
    {
        // Buat loan sebelumnya yang didenda
        $oldLoan = Loan::create([
            'member_id' => $this->member->id,
            'item_id' => $this->book->id,
            'librarian_id' => $this->librarian->id,
            'loan_date' => Carbon::today()->subDays(20),
            'due_date' => Carbon::today()->subDays(6),
            'status' => LoanStatus::Borrowed,
        ]);

        Fine::create([
            'loan_id' => $oldLoan->id,
            'amount' => 3000.0,
            'reason' => 'late',
            'paid_status' => 'unpaid',
        ]);

        // Coba pinjam item lain
        $response = $this->actingAs($this->librarian, 'web')
            ->postJson('/api/loans', [
                'member_id' => $this->member->id,
                'item_id' => $this->book->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'code' => 'FINE_UNPAID',
            ]);
    }

    /**
     * Uji pengembalian tepat waktu (tanpa denda).
     */
    public function test_return_loan_on_time(): void
    {
        $loan = Loan::create([
            'member_id' => $this->member->id,
            'item_id' => $this->book->id,
            'librarian_id' => $this->librarian->id,
            'loan_date' => Carbon::today()->subDays(5),
            'due_date' => Carbon::today()->addDays(9),
            'status' => LoanStatus::Borrowed,
        ]);
        $this->book->update(['available_stock' => 4]);

        $response = $this->actingAs($this->librarian, 'web')
            ->postJson("/api/loans/{$loan->id}/return");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'returned')
            ->assertJsonPath('data.fine', null);

        // Pastikan stok bertambah kembali
        $this->book->refresh();
        $this->assertEquals(5, $this->book->available_stock);
    }

    /**
     * Uji pengembalian terlambat (otomatis hitung dan buat record denda).
     */
    public function test_return_loan_late_creates_fine(): void
    {
        $loan = Loan::create([
            'member_id' => $this->member->id,
            'item_id' => $this->book->id,
            'librarian_id' => $this->librarian->id,
            'loan_date' => Carbon::today()->subDays(20),
            'due_date' => Carbon::today()->subDays(5), // Terlambat 5 hari
            'status' => LoanStatus::Borrowed,
        ]);
        $this->book->update(['available_stock' => 4]);

        $response = $this->actingAs($this->librarian, 'web')
            ->postJson("/api/loans/{$loan->id}/return");

        // Book rate per day = 500. Terlambat 5 hari -> 2500
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'returned')
            ->assertJsonPath('data.fine.amount', 2500)
            ->assertJsonPath('data.fine.paid_status', 'unpaid');

        $this->assertDatabaseHas('fines', [
            'loan_id' => $loan->id,
            'amount' => 2500,
            'paid_status' => 'unpaid',
        ]);
    }
}
