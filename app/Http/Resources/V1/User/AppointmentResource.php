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
            'payment_status' => $this->payment_status,
            'start_time'      => $this->start_time,
            'end_time'        => $this->end_time,
            'price'          => $this->service_price,
            'duration'       => $this->service_duration,
            'pet' => [
                'id'   => $this->pet->id,
                'name' => $this->pet->name,
            ],
            'service' => [
                'id'   => $this->service->id,
                'name' => $this->service->name,
            ],

            'business' => [
                'id'   => $this->business->id,
                'name' => $this->business->name,
            ],
        ];
    }
}
