<?php

namespace App\Http\Resources\V1\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'path' => $this->image_url,
            'is_primary' => $this->is_primary,
            'order' => $this->order
        ];
    }
}
