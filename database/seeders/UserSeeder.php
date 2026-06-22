<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data user demo (admin dan pustakawan).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@perpus.test',
            'password' => 'password',
            'role' => UserRole::Admin,
        ]);

        User::create([
            'name' => 'Pustakawan Demo',
            'email' => 'pustakawan@perpus.test',
            'password' => 'password',
            'role' => UserRole::Pustakawan,
        ]);
    }
}
