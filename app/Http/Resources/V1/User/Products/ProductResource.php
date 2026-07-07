<?php

namespace App\Http\Resources\V1\User\Products;


use App\Http\Resources\V1\User\CategoryResource;
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

            'effective_price'    => (int) $this->activeVariations->min('price'),
            'discount_price'     => (int) $this->activeVariations
                ->whereNotNull('discount_price')
                ->min('discount_price'),
            'total_stock'        => (int) $this->activeVariations->sum('stock'),

            'variations'         => VariationResource::collection($this->activeVariations),

            'images'             => ProductImageResource::collection($this->images),
            'categories'         => CategoryResource::collection($this->categories),
            'brand'              => BrandResource::make($this->brand),
        ];
    }
}
