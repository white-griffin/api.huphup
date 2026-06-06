<?php

namespace App\Http\Resources\V1\User;

use App\Enums\CategoryTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @see \App\Models\Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'parent' => self::make($this->parent),
            'children' => self::collection($this->children),
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image_url,
            'type' => CategoryTypes::label($this->type)
        ];
    }
}
