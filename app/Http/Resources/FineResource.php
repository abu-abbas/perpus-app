<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Resource untuk transformasi data denda ke JSON. */
class FineResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loan_id' => $this->loan_id,
            'loan' => new LoanResource($this->whenLoaded('loan')),
            'amount' => $this->amount,
            'reason' => $this->reason->value,
            'paid_status' => $this->paid_status->value,
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
