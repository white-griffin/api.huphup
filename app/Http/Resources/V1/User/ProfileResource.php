<?php

namespace App\Http\Resources\V1\User;

use App\Enums\GenderType;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'avatar' => $this->avatar_url,
            'birth_date' => $this->birth_date,
            'national_code' => $this->national_code,
            'gender_type' => $this->gender_type,
            'bio' => $this->bio,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude
        ];
    }
}
