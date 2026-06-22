<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk Autentikasi Sanctum (Login/Logout/User Session).
 */
class LibraryAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji user bisa login dengan data yang valid.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@perpus.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'role']
            ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Uji login gagal jika password salah.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@perpus.test',
            'password' => 'wrong-password',
        ]);

        // Gagal login mengembalikan validation/business error (422)
        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'code' => 'LOGIN_FAILED',
            ]);

        $this->assertGuest();
    }

    /**
     * Uji mengambil data user yang sedang aktif login.
     */
    public function test_user_can_retrieve_their_profile_when_authenticated(): void
    {
        $user = User::create([
            'name' => 'Librarian',
            'email' => 'librarian@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Librarian',
                    'email' => 'librarian@perpus.test',
                    'role' => 'pustakawan',
                ]
            ]);
    }

    /**
     * Uji user bisa logout dengan sukses.
     */
    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Librarian',
            'email' => 'librarian@perpus.test',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout berhasil.']);

        $this->assertGuest();
    }
}
