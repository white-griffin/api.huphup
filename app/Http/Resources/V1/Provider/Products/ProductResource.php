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
        $hasVariations = $this->activeVariations->isNotEmpty();

        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'slug'               => $this->slug,
            'description'        => $this->description,
            'price'              => $hasVariations ? null : $this->price,
            'discount_price'     => $hasVariations ? null : $this->discount_price,
            'stock'              => $hasVariations ? null : $this->stock,
            'sku'                => $hasVariations ? null : $this->sku,
            'effective_price'    => $this->getEffectivePrice(),
            'total_stock'        => $this->getTotalStock(),
            'has_variations'     => $hasVariations,
            'variations'         => $hasVariations
                ? ProductVariationResource::collection($this->activeVariations)
                : null,
            'publication_status' => $this->publication_status,
            'images'             => ProductImageResource::collection($this->images),
            'categories'         => CategoryResource::collection($this->categories),
            'brand'              => BrandResource::make($this->brand),
        ];
    }

}
