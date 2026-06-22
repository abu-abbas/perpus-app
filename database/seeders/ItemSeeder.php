<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ItemType;
use App\Models\Item;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data item koleksi perpustakaan (campuran Book, Magazine, Dvd).
 */
class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Buku — Kategori Fiksi (1)
            [
                'category_id' => 1,
                'type' => ItemType::Book,
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'year' => 2005,
                'code' => 'ISBN-9789793062792',
                'total_stock' => 5,
                'available_stock' => 5,
                'attributes' => json_encode(['pages' => 529]),
            ],
            [
                'category_id' => 1,
                'type' => ItemType::Book,
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'publisher' => 'Hasta Mitra',
                'year' => 1980,
                'code' => 'ISBN-9789799731234',
                'total_stock' => 3,
                'available_stock' => 3,
                'attributes' => json_encode(['pages' => 535]),
            ],
            // Buku — Kategori Non-Fiksi (2)
            [
                'category_id' => 2,
                'type' => ItemType::Book,
                'title' => 'Sapiens: Riwayat Singkat Umat Manusia',
                'author' => 'Yuval Noah Harari',
                'publisher' => 'Kepustakaan Populer Gramedia',
                'year' => 2014,
                'code' => 'ISBN-9786024810023',
                'total_stock' => 4,
                'available_stock' => 4,
                'attributes' => json_encode(['pages' => 464]),
            ],
            // Buku — Kategori Sains (3)
            [
                'category_id' => 3,
                'type' => ItemType::Book,
                'title' => 'A Brief History of Time',
                'author' => 'Stephen Hawking',
                'publisher' => 'Bantam Books',
                'year' => 1988,
                'code' => 'ISBN-9780553380163',
                'total_stock' => 2,
                'available_stock' => 2,
                'attributes' => json_encode(['pages' => 212]),
            ],
            // Buku — Kategori Teknologi (5)
            [
                'category_id' => 5,
                'type' => ItemType::Book,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'year' => 2008,
                'code' => 'ISBN-9780132350884',
                'total_stock' => 3,
                'available_stock' => 3,
                'attributes' => json_encode(['pages' => 464]),
            ],
            // Majalah — Kategori Sains (3)
            [
                'category_id' => 3,
                'type' => ItemType::Magazine,
                'title' => 'National Geographic Indonesia',
                'author' => 'National Geographic Society',
                'publisher' => 'Gramedia Majalah',
                'year' => 2024,
                'code' => 'MAG-NATGEO-2024-06',
                'total_stock' => 10,
                'available_stock' => 10,
                'attributes' => json_encode(['edition_number' => 'Vol. 20 No. 6']),
            ],
            [
                'category_id' => 5,
                'type' => ItemType::Magazine,
                'title' => 'InfoKomputer',
                'author' => 'Kompas Gramedia',
                'publisher' => 'Gramedia Majalah',
                'year' => 2024,
                'code' => 'MAG-INFOKOMPUTER-2024-03',
                'total_stock' => 8,
                'available_stock' => 8,
                'attributes' => json_encode(['edition_number' => 'Edisi Maret 2024']),
            ],
            // Majalah — Kategori Sejarah (4)
            [
                'category_id' => 4,
                'type' => ItemType::Magazine,
                'title' => 'Historia',
                'author' => 'Gramedia',
                'publisher' => 'Gramedia Majalah',
                'year' => 2023,
                'code' => 'MAG-HISTORIA-2023-12',
                'total_stock' => 6,
                'available_stock' => 6,
                'attributes' => json_encode(['edition_number' => 'Edisi Desember 2023']),
            ],
            // DVD — Kategori Sejarah (4)
            [
                'category_id' => 4,
                'type' => ItemType::Dvd,
                'title' => 'Dokumenter Majapahit',
                'author' => 'TVRI',
                'publisher' => 'TVRI Productions',
                'year' => 2020,
                'code' => 'DVD-MAJAPAHIT-001',
                'total_stock' => 2,
                'available_stock' => 2,
                'attributes' => json_encode(['duration_minutes' => 120]),
            ],
            // DVD — Kategori Sains (3)
            [
                'category_id' => 3,
                'type' => ItemType::Dvd,
                'title' => 'Cosmos: A Personal Voyage',
                'author' => 'Carl Sagan',
                'publisher' => 'PBS',
                'year' => 1980,
                'code' => 'DVD-COSMOS-001',
                'total_stock' => 3,
                'available_stock' => 3,
                'attributes' => json_encode(['duration_minutes' => 780]),
            ],
            // DVD — Kategori Teknologi (5)
            [
                'category_id' => 5,
                'type' => ItemType::Dvd,
                'title' => 'The Social Network',
                'author' => 'David Fincher',
                'publisher' => 'Columbia Pictures',
                'year' => 2010,
                'code' => 'DVD-SOCNET-001',
                'total_stock' => 4,
                'available_stock' => 4,
                'attributes' => json_encode(['duration_minutes' => 120]),
            ],
            // Buku tambahan — Kategori Fiksi (1)
            [
                'category_id' => 1,
                'type' => ItemType::Book,
                'title' => 'Perahu Kertas',
                'author' => 'Dee Lestari',
                'publisher' => 'Bentang Pustaka',
                'year' => 2009,
                'code' => 'ISBN-9789793062888',
                'total_stock' => 4,
                'available_stock' => 4,
                'attributes' => json_encode(['pages' => 444]),
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
