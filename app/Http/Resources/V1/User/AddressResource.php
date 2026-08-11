<?php

namespace App\Http\Resources\V1\User;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'province' => Province::query()->find($this->province_id)->name,
            'city' => City::query()->find($this->city_id)->name,
            'name' => $this->name,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'latitude' => (float)$this->latitude,
            'longitude' => (float)$this->longitude
        ];
    }
}
