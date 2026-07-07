<?php

namespace App\Http\Resources\V1\Provider\Products;

use App\Http\Resources\V1\Provider\CategoryResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'slug'               => $this->slug,
            'description'        => $this->description,

            'effective_price'    => optional($this->activeVariations->min('price')),
            'total_stock'        => $this->activeVariations->sum('stock'),

            'publication_status' => $this->publication_status,

            'images'             => ProductImageResource::collection($this->images),
            'categories'         => CategoryResource::collection($this->categories),
            'brand'              => BrandResource::make($this->brand),

            'variations'         => ProductVariationResource::collection($this->activeVariations),
        ];
    }
}
