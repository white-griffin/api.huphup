<?php

namespace App\Http\Resources\V1\User\Products;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductImage */
class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'path' => $this->image_url,
            'is_primary' => (bool)$this->is_primary,
            'order' => $this->order
        ];
    }
}
