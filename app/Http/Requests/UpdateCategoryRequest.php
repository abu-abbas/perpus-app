<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi request update kategori. */
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . ($this->route('category')?->id ?? $this->route('category')),
            'description' => 'nullable|string|max:500',
        ];
    }
}
