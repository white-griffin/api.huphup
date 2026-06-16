<?php

namespace App\Http\Resources\V1\User;

use App\Enums\CategoryTypes;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'parent' => $this->whenLoaded('parent', function () {
                return [
                    'name' => $this->parent->name,
                    'slug' => $this->parent->slug,
                ];
            }),

            'children' => CategoryResource::collection(
                $this->whenLoaded('children')
            ),
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image_url,
            'type' => CategoryTypes::label($this->type)
        ];
    }
}
