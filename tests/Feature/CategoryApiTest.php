<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk API Kategori (CRUD dan Proteksi Role).
 */
class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $librarian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->librarian = User::create([
            'name' => 'Pustakawan A',
            'email' => 'pustaka@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);
    }

    /**
     * Uji guest tidak bisa mengakses kategori (401).
     */
    public function test_guest_cannot_access_categories(): void
    {
        $this->getJson('/api/categories')->assertStatus(401);
    }

    /**
     * Uji pustakawan/admin bisa melihat daftar kategori.
     */
    public function test_user_can_view_categories_list(): void
    {
        Category::create(['name' => 'Novel', 'description' => 'Fiksi Novel']);
        Category::create(['name' => 'Sains', 'description' => 'Ilmiah']);

        $response = $this->actingAs($this->librarian, 'web')->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description']
                ]
            ]);
    }

    /**
     * Uji buat kategori baru.
     */
    public function test_user_can_create_category(): void
    {
        $response = $this->actingAs($this->librarian, 'web')
            ->postJson('/api/categories', [
                'name' => 'Sejarah',
                'description' => 'Buku-buku sejarah dunia',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Sejarah',
                    'description' => 'Buku-buku sejarah dunia',
                ]
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Sejarah']);
    }

    /**
     * Uji perbarui kategori.
     */
    public function test_user_can_update_category(): void
    {
        $category = Category::create(['name' => 'Biografi', 'description' => 'Kisah hidup']);

        $response = $this->actingAs($this->librarian, 'web')
            ->putJson("/api/categories/{$category->id}", [
                'name' => 'Autobiografi',
                'description' => 'Ditulis sendiri',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Autobiografi',
        ]);
    }

    /**
     * Uji pustakawan DILARANG menghapus kategori (403).
     */
    public function test_librarian_cannot_delete_category(): void
    {
        $category = Category::create(['name' => 'Filsafat']);

        $response = $this->actingAs($this->librarian, 'web')
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Uji admin BISA menghapus kategori.
     */
    public function test_admin_can_delete_category(): void
    {
        $category = Category::create(['name' => 'Komputer']);

        $response = $this->actingAs($this->admin, 'web')
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Kategori berhasil dihapus.']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
