<?php

namespace App\Http\Resources\V1\Provider\Appointment;

use App\Http\Resources\V1\User\BusinessResource;
use App\Http\Resources\V1\User\BusinessServiceResource;
use App\Http\Resources\V1\User\Pets\PetResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'service_price' => $this->service_price,
            'service_duration' => $this->service_duration,
            'status' => $this->status,
            'notes' => $this->notes,
            'cancelled_at' => $this->cancelled_at,
            'refund_percentage' => $this->refund_percentage,
            'refund_amount' => $this->refund_amount,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at,

            'user' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'avatar' => $this->user->avatar_url,
                'mobile' => $this->user->mobile,
            ],
            'pet' => PetResource::make($this->whenLoaded('pet')),

            'service' => new BusinessServiceResource($this->whenLoaded('businessService')),
            'review' => new ReviewResource($this->whenLoaded('review')),
        ];
    }
}
