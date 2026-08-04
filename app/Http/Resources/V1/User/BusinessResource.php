<?php

namespace App\Http\Resources\V1\User;

use App\Enums\ActivityStatus;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Business */
class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' =>$this->id,
            'business_type' => $this->business_type,
            'name' => $this->name,
            'description' => $this->description,
            'logo' => $this->logo_url,
            'cover' => $this->cover_url,
            'province' => [
                'id' => $this->province->id,
                'name' => $this->province->name
            ],
            'city' => [
                'id' => $this->city->id,
                'name' => $this->city->name
            ],
            'latitude' => (float)$this->latitude,
            'longitude' => (float)$this->longitude,
            'street' => preg_split('/\s*[،,]\s*/u', $this->address)[0] ?? null,
            'phone' => $this->phone,
            'work_time' => $this->settings['work_time'] ?? null,
            'services' => BusinessServiceResource::collection(
                $this->whenLoaded('services')
            ),
            'rating' => $this->reputation?->rating_avg,

            'rating_count' => $this->reputation?->rating_count,

            'review_count' => $this->reputation?->review_count,
        ];
    }
}
