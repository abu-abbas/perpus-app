<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ItemType;
use App\Exceptions\ImageProcessingException;
use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Service untuk manajemen item koleksi perpustakaan.
 *
 * Menangani CRUD item, upload cover image, dan filter pencarian.
 */
class ItemService
{
    /**
     * Buat ItemFactory lewat constructor injection.
     */
    public function __construct(
        private readonly ItemFactory $itemFactory,
    ) {}

    /**
     * Ambil daftar item terpaginasi dengan filter.
     *
     * @param array<string, mixed> $filters Filter: type, category_id, search
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Item::with('category');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Buat item baru menggunakan ItemFactory.
     *
     * @param array<string, mixed> $data Data item dari request
     * @return Item Instance item yang sudah tersimpan
     */
    public function create(array $data): Item
    {
        $type = ItemType::from($data['type']);

        // Upload cover image jika ada
        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            $data['cover_image'] = $this->uploadCoverImage($data['cover_image']);
        }

        // Jika available_stock tidak disediakan, samakan dengan total_stock
        if (! isset($data['available_stock']) && isset($data['total_stock'])) {
            $data['available_stock'] = $data['total_stock'];
        }

        $item = $this->itemFactory->make($type, $data);
        $item->save();

        return $item->fresh()->load('category');
    }

    /**
     * Update item yang sudah ada.
     *
     * @param Item $item Instance item yang akan diupdate
     * @param array<string, mixed> $data Data baru
     * @return Item Instance item yang sudah diupdate
     */
    public function update(Item $item, array $data): Item
    {
        // Upload cover image baru jika ada
        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            // Hapus cover lama jika ada
            if ($item->cover_image) {
                Storage::disk('public')->delete($item->cover_image);
            }
            $data['cover_image'] = $this->uploadCoverImage($data['cover_image']);
        }

        $item->update($data);

        return $item->fresh()->load('category');
    }

    /**
     * Hapus item.
     *
     * @param Item $item Instance item yang akan dihapus
     */
    public function delete(Item $item): void
    {
        // Hapus cover image dari storage jika ada
        if ($item->cover_image) {
            Storage::disk('public')->delete($item->cover_image);
        }

        $item->delete();
    }

    /**
     * Upload dan resize cover image.
     *
     * Gambar di-resize ke maksimal lebar 800px dengan menjaga rasio aspek,
     * kemudian disimpan ke storage/app/public/covers.
     *
     * @param UploadedFile $file File gambar yang diupload
     * @return string Path file relatif di storage
     *
     * @throws ImageProcessingException Jika gagal memproses gambar
     */
    public function uploadCoverImage(UploadedFile $file): string
    {
        try {
            $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();
            $path = 'covers/' . $filename;

            // Resize gambar ke maks lebar 800px, pertahankan aspek rasio
            $image = Image::read($file->getRealPath());
            $image->scaleDown(width: 800);

            // Simpan ke storage public
            Storage::disk('public')->put($path, (string) $image->encode());

            return $path;
        } catch (\Throwable $e) {
            throw new ImageProcessingException(
                $file->getClientOriginalName(),
                $e->getMessage(),
                $e,
            );
        }
    }
}
