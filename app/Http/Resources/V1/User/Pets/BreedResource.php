<?php

namespace App\Http\Resources\V1\User\Pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BreedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'species' => [
                'id' => $this->species_id,
                'name_en' => $this->species->name_en,
                'name_fa' => $this->species->name_fa
            ],
            'name_en' => $this->name_en,
            'name_fa' => $this->name_fa,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image_url,
            'characteristics' => $this->characteristics
        ];
    }
}
