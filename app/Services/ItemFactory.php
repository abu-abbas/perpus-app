<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ItemType;
use App\Models\Book;
use App\Models\Dvd;
use App\Models\Item;
use App\Models\Magazine;

/**
 * Factory untuk membuat instance subclass Item yang benar.
 *
 * Mengimplementasi Factory Method pattern: mengembalikan Book, Magazine,
 * atau Dvd berdasarkan tipe yang diberikan.
 */
class ItemFactory
{
    /**
     * Buat instance Item sesuai tipe.
     *
     * @param ItemType $type Tipe item yang akan dibuat
     * @param array<string, mixed> $data Data atribut item
     * @return Item Instance Book, Magazine, atau Dvd
     */
    public function make(ItemType $type, array $data): Item
    {
        $model = match ($type) {
            ItemType::Book => new Book(),
            ItemType::Magazine => new Magazine(),
            ItemType::Dvd => new Dvd(),
        };

        $model->fill($data);

        return $model;
    }
}
