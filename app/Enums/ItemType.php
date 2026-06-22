<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tipe item koleksi perpustakaan.
 *
 * Dipakai untuk membedakan jenis koleksi:
 * - Book: buku dengan atribut jumlah halaman
 * - Magazine: majalah dengan atribut nomor edisi
 * - Dvd: DVD dengan atribut durasi dalam menit
 */
enum ItemType: string
{
    case Book = 'book';
    case Magazine = 'magazine';
    case Dvd = 'dvd';
}
