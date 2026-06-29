<?php

namespace App\Http\Resources\V1\User\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'price' => (int)$this->price,
            'discount_price' => (int)$this->discount_price,
            'stock' => $this->stock,
            'is_default' => (bool)$this->is_default
        ];
    }
}
