<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Resource untuk transformasi data item ke JSON — termasuk displayInfo() polimorfis. */
class ItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'type' => $this->type->value,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'year' => $this->year,
            'code' => $this->code,
            'total_stock' => $this->total_stock,
            'available_stock' => $this->available_stock,
            'cover_image_url' => $this->cover_image_url,
            'attributes' => $this->attributes,
            'display_info' => $this->displayInfo(),
            'late_fee_per_day' => $this->getLateFeePerDay(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
