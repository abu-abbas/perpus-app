<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi request update item. */
class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'type' => 'sometimes|in:book,magazine,dvd',
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'code' => 'sometimes|string|max:100|unique:items,code,' . ($this->route('item')?->id ?? $this->route('item')),
            'total_stock' => 'sometimes|integer|min:0',
            'available_stock' => 'sometimes|integer|min:0',
            'cover_image' => 'nullable|image|max:2048',
            'attributes' => 'nullable|array',
        ];
    }
}
