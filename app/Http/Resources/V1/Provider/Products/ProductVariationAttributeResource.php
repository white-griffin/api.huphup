<?php

namespace App\Http\Resources\V1\Provider\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationAttributeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'attribute_id' => $this->attribute_id,
            'attribute_name' => $this->attribute->name,
            'attribute_slug' => $this->attribute->slug,

            'option_id' => $this->attribute_option_id,
            'option_value' => $this->option->value,
            'option_label' => $this->option->label,
            'option_slug' => $this->option->slug,
        ];
    }
}
