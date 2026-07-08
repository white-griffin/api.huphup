<?php

namespace App\Http\Resources\V1\Provider\Products;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Attribute */
class AttributeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_filterable' => $this->is_filterable,

            'options' => AttributeOptionResource::collection($this->options),
        ];
    }
}
