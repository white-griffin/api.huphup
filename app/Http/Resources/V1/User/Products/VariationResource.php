<?php

namespace App\Http\Resources\V1\User\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,

            'price' => (int) $this->price,
            'discount_price' => $this->discount_price ? (int) $this->discount_price : null,
            'stock' => (int) $this->stock,

            'is_default' => (bool) $this->is_default,

            'attributes' => $this->variationAttributes->map(function ($attribute) {
                return [
                    'attribute_id' => $attribute->attribute_id,
                    'name' => $attribute->attribute->name,
                    'slug' => $attribute->attribute->slug,

                    'option_id' => $attribute->attribute_option_id,
                    'value' => $attribute->option->value,
                    'label' => $attribute->option->label,
                    'option_slug' => $attribute->option->slug,
                ];
            })->values(),
        ];
    }
}
