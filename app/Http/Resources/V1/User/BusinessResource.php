<?php

namespace App\Http\Resources\V1\User;

use App\Enums\ActivityStatus;
use App\Enums\BusinessTypes;
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
            'business_type' => BusinessTypes::label($this->business_type),
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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'services' => ServiceResource::collection(
                $this->services()
                    ->where('business_services.activity_status',ActivityStatus::ACTIVE->value)
                    ->get()
            )
        ];
    }
}
