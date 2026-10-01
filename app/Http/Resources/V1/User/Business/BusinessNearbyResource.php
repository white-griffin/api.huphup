<?php

namespace App\Http\Resources\V1\User\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessNearbyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_type' => $this->business_type,
            'name' => $this->name,
            'logo' => $this->logo_url,
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
            'distance' => round((float) $this->distance, 2),
        ];
    }
}
