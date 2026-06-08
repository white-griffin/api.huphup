<?php

namespace App\Http\Resources\V1\Provider;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'attributes' => $this->attributes,
            'images' => ProductImageResource::collection($this->images),
            'categories' => CategoryResource::collection($this->categories),
            'brand' => BrandResource::make($this->brand)
        ];
    }
}
