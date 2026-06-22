<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data kategori koleksi perpustakaan.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiksi', 'description' => 'Novel, cerpen, dan karya fiksi lainnya'],
            ['name' => 'Non-Fiksi', 'description' => 'Buku referensi, biografi, dan karya non-fiksi'],
            ['name' => 'Sains', 'description' => 'Buku sains, matematika, dan teknologi'],
            ['name' => 'Sejarah', 'description' => 'Buku sejarah, budaya, dan peradaban'],
            ['name' => 'Teknologi', 'description' => 'Buku pemrograman, IT, dan teknologi informasi'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
