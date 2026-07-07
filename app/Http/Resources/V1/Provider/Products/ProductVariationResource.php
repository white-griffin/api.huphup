<?php

namespace App\Http\Resources\V1\Provider\Products;

use App\Enums\ProductAttributeType;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductVariation */
class ProductVariationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'price'           => $this->price,
            'discount_price'  => $this->discount_price,
            'stock'           => $this->stock,
            'is_default'      => $this->is_default,
            'activity_status' => $this->activity_status,

            'attributes' => ProductVariationAttributeResource::collection(
                $this->variationAttributes
            ),
        ];
    }
}
