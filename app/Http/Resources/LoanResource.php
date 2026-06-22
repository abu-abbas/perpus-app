<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Resource untuk transformasi data peminjaman ke JSON. */
class LoanResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member' => new MemberResource($this->whenLoaded('member')),
            'item' => new ItemResource($this->whenLoaded('item')),
            'librarian' => new UserResource($this->whenLoaded('librarian')),
            'loan_date' => $this->loan_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'return_date' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'is_overdue' => $this->is_overdue,
            'days_late' => $this->daysLate(),
            'fine' => new FineResource($this->whenLoaded('fine')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
