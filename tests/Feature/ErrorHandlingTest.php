<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Exceptions\ImageProcessingException;
use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk memverifikasi Standardisasi Penanganan Error (Error Handling).
 */
class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;
    private Member $suspendedMember;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::create([
            'name' => 'Librarian',
            'email' => 'lib@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $this->suspendedMember = Member::create([
            'member_number' => 'MBR-9001',
            'full_name' => 'Budi Suspended',
            'identity_number' => '3273010101019001',
            'join_date' => '2026-06-20',
            'status' => MemberStatus::Suspended,
        ]);

        $category = Category::create(['name' => 'Umum']);
        $this->book = Book::create([
            'category_id' => $category->id,
            'type' => 'book',
            'title' => 'Buku Fisika',
            'author' => 'Eksperimen',
            'year' => 2026,
            'code' => 'ISBN-PHYS-X',
            'total_stock' => 5,
            'available_stock' => 5,
        ]);
    }

    /**
     * Uji format error 422 saat BusinessException dilempar.
     * Response JSON harus terstandarisasi (error, type, code, message, context).
     */
    public function test_business_exception_renders_correct_json_format(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->postJson('/api/loans', [
                'member_id' => $this->suspendedMember->id,
                'item_id' => $this->book->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'type' => 'business',
                'code' => 'MEMBER_NOT_ACTIVE',
            ])
            ->assertJsonStructure(['error', 'type', 'code', 'message', 'context']);
    }

    /**
     * Uji format error 500 saat AppException (kegagalan teknis) dilempar.
     * Pesan ke user harus generik (tidak bocor detail teknis), response berkode system.
     */
    public function test_app_exception_renders_safe_user_message_in_json(): void
    {
        // Mock ItemService list method untuk melempar ImageProcessingException (subclass dari AppException)
        $this->mock(\App\Services\ItemService::class, function ($mock) {
            $mock->shouldReceive('list')
                ->andThrow(new ImageProcessingException('cover_image.jpg', 'Gagal memproses resolusi tinggi.'));
        });

        $response = $this->actingAs($this->librarian, 'web')
            ->getJson('/api/items');

        $response->assertStatus(500)
            ->assertJson([
                'error' => true,
                'type' => 'system',
                'code' => 'IMAGE_PROCESSING_FAILED',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
            ]);
    }
}
