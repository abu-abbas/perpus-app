<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\FineReason;
use App\Enums\ItemType;
use App\Enums\LoanStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\Category;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\ValueObjects\FineAmount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Unit test untuk Value Object (FineAmount) dan Enum Casting di Model Eloquent.
 */
class ValueObjectAndEnumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji bahwa Value Object FineAmount bersifat immutable dan readonly.
     */
    public function test_fine_amount_value_object_properties(): void
    {
        $amount = 15000.0;
        $reason = FineReason::Late;

        $vo = new FineAmount($amount, $reason);

        // Assert properties read correctly
        $this->assertEquals($amount, $vo->amount);
        $this->assertEquals($reason, $vo->reason);

        // PHP readonly properties throw Error if writing is attempted
        $this->expectException(\Error::class);
        /** @phpstan-ignore-next-line */
        $vo->amount = 20000.0;
    }

    /**
     * Uji casting enum otomatis di berbagai model.
     */
    public function test_model_attributes_cast_to_enum(): void
    {
        $category = Category::create(['name' => 'Kategori A']);

        // 1. User role casting
        $user = User::create([
            'name' => 'Pustakawan A',
            'email' => 'pustaka@perpus.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Pustakawan,
        ]);
        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::Pustakawan, $user->role);

        // 2. Item type casting
        $item = Book::create([
            'category_id' => $category->id,
            'type' => ItemType::Book,
            'title' => 'Casting Book',
            'author' => 'Author X',
            'year' => 2026,
            'code' => 'CODE-1',
            'total_stock' => 1,
            'available_stock' => 1,
        ]);
        $this->assertInstanceOf(ItemType::class, $item->type);
        $this->assertEquals(ItemType::Book, $item->type);

        // 3. Member status casting
        $member = Member::create([
            'member_number' => 'MBR-9988',
            'full_name' => 'Member X',
            'identity_number' => 'KTP-9988',
            'join_date' => '2026-06-20',
            'status' => MemberStatus::Suspended,
        ]);
        $this->assertInstanceOf(MemberStatus::class, $member->status);
        $this->assertEquals(MemberStatus::Suspended, $member->status);

        // 4. Loan status casting
        $loan = Loan::create([
            'member_id' => $member->id,
            'item_id' => $item->id,
            'librarian_id' => $user->id,
            'loan_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => LoanStatus::Borrowed,
        ]);
        $this->assertInstanceOf(LoanStatus::class, $loan->status);
        $this->assertEquals(LoanStatus::Borrowed, $loan->status);

        // 5. Fine reason and status casting
        $fine = Fine::create([
            'loan_id' => $loan->id,
            'amount' => 5000.0,
            'reason' => FineReason::Damaged,
            'paid_status' => PaymentStatus::Unpaid,
        ]);
        $this->assertInstanceOf(FineReason::class, $fine->reason);
        $this->assertInstanceOf(PaymentStatus::class, $fine->paid_status);
        $this->assertEquals(FineReason::Damaged, $fine->reason);
        $this->assertEquals(PaymentStatus::Unpaid, $fine->paid_status);
    }
}
