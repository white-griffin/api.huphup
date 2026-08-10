<?php

namespace App\Http\Resources\V1\User;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'start_time'      => $this->start_time,
            'end_time'        => $this->end_time,
            'price'          => $this->service_price,
            'duration'       => $this->service_duration,
            'pet' => [
                'id'   => $this->pet->id,
                'avatar' => $this->pet->avatar_url,
                'name' => $this->pet->name,
                'breed' => $this->pet->breed->name_en
            ],
            'business_service' => [
                'id'   => $this->businessService->id,
                'name' => $this->businessService->service->name,
            ],

            'business' => [
                'logo' => $this->business->logo_url,
                'id'   => $this->business->id,
                'name' => $this->business->name,
            ],
        ];
    }
}
