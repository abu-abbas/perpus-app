<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature test untuk API Item (CRUD, Upload Cover, Import/Export).
 */
class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $librarian;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->librarian = User::create([
            'name' => 'Pustakawan',
            'email' => 'pustaka@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $this->category = Category::create(['name' => 'Teknologi']);
    }

    /**
     * Uji tambah item baru dengan file cover image.
     */
    public function test_librarian_can_create_item_with_cover(): void
    {
        $cover = UploadedFile::fake()->image('cover_novel.jpg', 600, 800);

        $response = $this->actingAs($this->librarian, 'web')
            ->postJson('/api/items', [
                'type' => 'book',
                'category_id' => $this->category->id,
                'title' => 'Framework Modern',
                'author' => 'Taylor Otwell',
                'publisher' => 'Laravel Press',
                'year' => 2026,
                'code' => 'ISBN-9988-77',
                'total_stock' => 10,
                'cover_image' => $cover,
                'attributes' => ['pages' => 450],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Framework Modern')
            ->assertJsonPath('data.attributes.pages', 450);

        $item = Item::first();
        $this->assertNotNull($item->cover_image);
        Storage::disk('public')->assertExists($item->cover_image);
    }

    /**
     * Uji perbarui item dan mengganti cover lamanya.
     */
    public function test_librarian_can_update_item(): void
    {
        $oldCover = UploadedFile::fake()->image('old.jpg');
        $item = Item::create([
            'category_id' => $this->category->id,
            'type' => ItemType::Book,
            'title' => 'Judul Lama',
            'author' => 'Author',
            'year' => 2024,
            'code' => 'CODE-OLD',
            'total_stock' => 5,
            'available_stock' => 5,
            'cover_image' => Storage::disk('public')->put('covers', $oldCover),
        ]);

        $newCover = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->librarian, 'web')
            ->putJson("/api/items/{$item->id}", [
                'type' => 'book',
                'category_id' => $this->category->id,
                'title' => 'Judul Baru',
                'author' => 'Author',
                'year' => 2024,
                'code' => 'CODE-OLD',
                'total_stock' => 5,
                'cover_image' => $newCover,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', ['id' => $item->id, 'title' => 'Judul Baru']);

        // Pastikan cover lama sudah dihapus dan cover baru disimpan
        Storage::disk('public')->assertMissing($item->cover_image);
        $item->refresh();
        Storage::disk('public')->assertExists($item->cover_image);
    }

    /**
     * Uji admin bisa menghapus item dan cover terhapus dari storage.
     */
    public function test_admin_can_delete_item(): void
    {
        $cover = UploadedFile::fake()->image('delete.jpg');
        $item = Item::create([
            'category_id' => $this->category->id,
            'type' => ItemType::Dvd,
            'title' => 'DVD Film',
            'author' => 'Sutradara',
            'year' => 2025,
            'code' => 'DVD-DEL',
            'total_stock' => 2,
            'available_stock' => 2,
            'cover_image' => Storage::disk('public')->put('covers', $cover),
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
        Storage::disk('public')->assertMissing($item->cover_image);
    }

    /**
     * Uji pustakawan DILARANG menghapus item.
     */
    public function test_librarian_cannot_delete_item(): void
    {
        $item = Item::create([
            'category_id' => $this->category->id,
            'type' => ItemType::Book,
            'title' => 'Buku Terlarang',
            'author' => 'Unknown',
            'year' => 2026,
            'code' => 'ISBN-BAN',
            'total_stock' => 1,
            'available_stock' => 1,
        ]);

        $response = $this->actingAs($this->librarian, 'web')
            ->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    /**
     * Uji export item ke PDF.
     */
    public function test_user_can_export_items_pdf(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->get('/api/items/export/pdf');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }
}
