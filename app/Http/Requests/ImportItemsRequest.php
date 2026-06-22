<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi request import file item. */
class ImportItemsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,csv,xls|max:5120',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload.',
            'file.mimes' => 'Format file harus XLSX, XLS, atau CSV.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
