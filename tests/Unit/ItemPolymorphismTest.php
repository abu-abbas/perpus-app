<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ItemType;
use App\Models\Book;
use App\Models\Category;
use App\Models\Dvd;
use App\Models\Item;
use App\Models\Magazine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test untuk memverifikasi Polimorfisme dan STI (Single Table Inheritance) pada Model Item.
 */
class ItemPolymorphismTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji instansiasi subclass Book, Magazine, dan Dvd.
     */
    public function test_item_is_instantiated_as_correct_subclass(): void
    {
        $category = Category::create(['name' => 'Umum']);

        // 1. Buku
        $book = Item::create([
            'category_id' => $category->id,
            'type' => ItemType::Book,
            'title' => 'Buku Polimorfisme',
            'author' => 'Author A',
            'year' => 2026,
            'code' => 'ISBN-123',
            'total_stock' => 5,
            'available_stock' => 5,
            'attributes' => ['attributes' => ['pages' => 320]],
        ]);

        // 2. Majalah
        $magazine = Item::create([
            'category_id' => $category->id,
            'type' => ItemType::Magazine,
            'title' => 'Majalah Polimorfisme',
            'author' => 'Author B',
            'year' => 2026,
            'code' => 'ISSN-123',
            'total_stock' => 3,
            'available_stock' => 3,
            'attributes' => ['attributes' => ['edition_number' => 12]],
        ]);

        // 3. DVD
        $dvd = Item::create([
            'category_id' => $category->id,
            'type' => ItemType::Dvd,
            'title' => 'DVD Polimorfisme',
            'author' => 'Author C',
            'year' => 2026,
            'code' => 'DVD-123',
            'total_stock' => 2,
            'available_stock' => 2,
            'attributes' => ['attributes' => ['duration_minutes' => 120]],
        ]);

        // Ambil kembali dari DB menggunakan referensi tipe dasar (Item)
        $retrievedBook = Item::find($book->id);
        $retrievedMagazine = Item::find($magazine->id);
        $retrievedDvd = Item::find($dvd->id);

        // Assert class instansi yang didapatkan
        $this->assertInstanceOf(Book::class, $retrievedBook);
        $this->assertInstanceOf(Magazine::class, $retrievedMagazine);
        $this->assertInstanceOf(Dvd::class, $retrievedDvd);
    }

    /**
     * Uji bahwa displayInfo() di-override dengan benar oleh masing-masing subclass.
     */
    public function test_display_info_is_polymorphic(): void
    {
        $category = Category::create(['name' => 'Umum']);

        $book = new Book([
            'category_id' => $category->id,
            'title' => 'Rancang Bangun Laravel',
            'author' => 'Penulis Buku',
            'attributes' => ['attributes' => ['pages' => 450]],
        ]);

        $magazine = new Magazine([
            'category_id' => $category->id,
            'title' => 'Kreatifitas Anak',
            'author' => 'Penulis Majalah',
            'attributes' => ['attributes' => ['edition_number' => 88]],
        ]);

        $dvd = new Dvd([
            'category_id' => $category->id,
            'title' => 'Interstellar',
            'author' => 'Nolan',
            'attributes' => ['attributes' => ['duration_minutes' => 169]],
        ]);

        $bookInfo = $book->displayInfo();
        $this->assertEquals('Buku', $bookInfo['tipe']);
        $this->assertEquals(450, $bookInfo['halaman']);

        $magazineInfo = $magazine->displayInfo();
        $this->assertEquals('Majalah', $magazineInfo['tipe']);
        $this->assertEquals(88, $magazineInfo['edisi']);

        $dvdInfo = $dvd->displayInfo();
        $this->assertEquals('DVD', $dvdInfo['tipe']);
        $this->assertEquals(169, $dvdInfo['durasi_menit']);
    }

    /**
     * Uji tarif denda keterlambatan berbeda per tipe item.
     */
    public function test_calculate_late_fee_is_subclass_specific(): void
    {
        $book = new Book();
        $magazine = new Magazine();
        $dvd = new Dvd();

        // Terlambat 5 hari
        $days = 5;

        // Book: 500 * 5 = 2500
        $this->assertEquals(2500.0, $book->calculateLateFee($days));

        // Magazine: 1000 * 5 = 5000
        $this->assertEquals(5000.0, $magazine->calculateLateFee($days));

        // Dvd: 2000 * 5 = 10000
        $this->assertEquals(10000.0, $dvd->calculateLateFee($days));
    }
}
