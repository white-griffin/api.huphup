<?php

namespace App\Http\Resources\V1\User;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'business' => [
                'id' => $this->business_id,
                'name' => $this->business->name
            ],
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'stock' => $this->stock,
            'attributes' => $this->attributes,
        ];
    }
}
