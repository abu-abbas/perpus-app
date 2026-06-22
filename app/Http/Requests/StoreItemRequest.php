<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi request buat item baru. */
class StoreItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'type' => 'required|in:book,magazine,dvd',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'code' => 'required|string|max:100|unique:items,code',
            'total_stock' => 'required|integer|min:0',
            'available_stock' => 'sometimes|integer|min:0',
            'cover_image' => 'nullable|image|max:2048',
            'attributes' => 'nullable|array',
            'attributes.pages' => 'nullable|integer|min:1',
            'attributes.edition_number' => 'nullable|string|max:100',
            'attributes.duration_minutes' => 'nullable|integer|min:1',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'type.required' => 'Tipe item wajib dipilih.',
            'type.in' => 'Tipe item harus book, magazine, atau dvd.',
            'title.required' => 'Judul wajib diisi.',
            'code.required' => 'Kode item wajib diisi.',
            'code.unique' => 'Kode item sudah digunakan.',
            'cover_image.image' => 'File harus berupa gambar.',
            'cover_image.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
