<?php

namespace App\Http\Resources\V1\User;

use App\Enums\GenderType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
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
                'name_fa' => $this->species->name_fa,
            ],
            'breed' => [
                'id' => $this->breed_id,
                'name_en' => $this->breed->name_en,
                'name_fa' => $this->breed->name_fa,
            ],
            'name' => $this->name,
            'gender_type' => $this->gender_type,
            'birth_date' => $this->birth_date,
            'weight' => $this->weight,
            'color' => $this->color,
            'avatar' => $this->avatar_url,
            'medical_records' => $this->medical_records,
            'settings' => $this->settings,
            'bio' => $this->bio
        ];
    }
}
