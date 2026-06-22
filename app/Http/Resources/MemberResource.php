<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Resource untuk transformasi data anggota ke JSON. */
class MemberResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_number' => $this->member_number,
            'full_name' => $this->full_name,
            'identity_number' => $this->identity_number,
            'phone' => $this->phone,
            'address' => $this->address,
            'join_date' => $this->join_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'active_loans_count' => $this->activeLoansCount(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
