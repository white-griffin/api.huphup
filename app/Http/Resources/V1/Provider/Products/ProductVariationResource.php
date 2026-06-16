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
            'id'         => $this->id,
            'sku'        => $this->sku,
            'price'      => $this->price,
            'stock'      => $this->stock,
            'is_active'  => $this->is_active,
            'attributes' => $this->formatAttributes(),
        ];
    }

    private function formatAttributes(): array
    {
        if (empty($this->attributes)) {
            return [];
        }

        $formatted = [];

        foreach ($this->attributes as $key => $value) {

            $formatted[] = [
                'key'   => $key,
                'label' => ProductAttributeType::label($key) ?? $key,
                'value' => $value,
            ];
        }

        return $formatted;
    }
}
