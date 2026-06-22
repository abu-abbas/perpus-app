<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk membuat tabel items (koleksi perpustakaan).
 *
 * Tabel ini menyimpan semua jenis koleksi (book, magazine, dvd)
 * dalam satu tabel menggunakan Single Table Inheritance lewat kolom `type`.
 * Kolom `attributes` berisi data JSON spesifik per tipe (pages, edition_number, duration_minutes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->integer('year');
            $table->string('code')->unique();
            $table->integer('total_stock');
            $table->integer('available_stock');
            $table->string('cover_image')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
