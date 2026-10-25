<?php

namespace App\Http\Resources\V1\User\User;

use App\Http\Resources\V1\User\Pets\PetNearbyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNearbyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->fullName,
            'avatar' => $this->avatar_url,
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
            'distance' => round((float) $this->distance, 2),
            'pets' => PetNearbyResource::collection($this->pets)
        ];
    }
}
