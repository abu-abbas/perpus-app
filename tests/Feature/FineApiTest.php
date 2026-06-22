<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\FineReason;
use App\Enums\ItemType;
use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
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
 * Feature test untuk API Manajemen Denda.
 */
class FineApiTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;
    private Fine $unpaidFine;
    private Fine $paidFine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::create([
            'name' => 'Librarian',
            'email' => 'lib@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $member = Member::create([
            'member_number' => 'MBR-9000',
            'full_name' => 'Hasanudin',
            'identity_number' => '3273010101019000',
            'join_date' => '2026-06-20',
            'status' => 'active',
        ]);

        $category = Category::create(['name' => 'Fiksi']);
        $book = Book::create([
            'category_id' => $category->id,
            'type' => ItemType::Book,
            'title' => 'Harry Potter',
            'author' => 'J.K. Rowling',
            'year' => 2005,
            'code' => 'ISBN-HP-01',
            'total_stock' => 5,
            'available_stock' => 5,
        ]);

        $loan1 = Loan::create([
            'member_id' => $member->id,
            'item_id' => $book->id,
            'librarian_id' => $this->librarian->id,
            'loan_date' => Carbon::today()->subDays(20),
            'due_date' => Carbon::today()->subDays(5),
            'status' => LoanStatus::Returned,
            'return_date' => Carbon::today(),
        ]);

        $loan2 = Loan::create([
            'member_id' => $member->id,
            'item_id' => $book->id,
            'librarian_id' => $this->librarian->id,
            'loan_date' => Carbon::today()->subDays(20),
            'due_date' => Carbon::today()->subDays(5),
            'status' => LoanStatus::Returned,
            'return_date' => Carbon::today(),
        ]);

        // 1. Denda belum dibayar
        $this->unpaidFine = Fine::create([
            'loan_id' => $loan1->id,
            'amount' => 2500.0,
            'reason' => FineReason::Late,
            'paid_status' => PaymentStatus::Unpaid,
        ]);

        // 2. Denda sudah dibayar
        $this->paidFine = Fine::create([
            'loan_id' => $loan2->id,
            'amount' => 2500.0,
            'reason' => FineReason::Late,
            'paid_status' => PaymentStatus::Paid,
            'paid_date' => Carbon::today(),
        ]);
    }

    /**
     * Uji melihat daftar denda.
     */
    public function test_librarian_can_view_fines_list(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->getJson('/api/fines');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Uji menyaring denda berdasarkan status pembayaran (paid/unpaid).
     */
    public function test_librarian_can_filter_fines_by_status(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->getJson('/api/fines?paid_status=paid');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->paidFine->id);
    }

    /**
     * Uji proses pelunasan denda (success path).
     */
    public function test_librarian_can_mark_fine_as_paid(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->postJson("/api/fines/{$this->unpaidFine->id}/pay");

        $response->assertStatus(200)
            ->assertJsonPath('data.paid_status', 'paid');

        $this->assertDatabaseHas('fines', [
            'id' => $this->unpaidFine->id,
            'paid_status' => 'paid',
            'paid_date' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * Uji denda yang sudah lunas tidak bisa dibayar kembali (error 422).
     */
    public function test_cannot_pay_already_paid_fine(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->postJson("/api/fines/{$this->paidFine->id}/pay");

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'code' => 'FINE_ALREADY_PAID',
            ]);
    }
}
