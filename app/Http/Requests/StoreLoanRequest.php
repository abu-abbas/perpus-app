<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi request buat peminjaman baru. */
class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'member_id' => 'required|integer|exists:members,id',
            'item_id' => 'required|integer|exists:items,id',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota tidak ditemukan.',
            'item_id.required' => 'Item wajib dipilih.',
            'item_id.exists' => 'Item tidak ditemukan.',
        ];
    }
}
