<?php

namespace App\Http\Resources\V1\User\Pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetNearbyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar_url,
            'bio' => $this->bio,
            'species_name' => $this->species->name_fa,
            'breed_name' => $this->breed->name_fa

        ];
    }
}
