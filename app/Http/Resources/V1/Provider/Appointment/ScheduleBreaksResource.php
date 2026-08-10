<?php

namespace App\Http\Resources\V1\Provider\Appointment;

use App\Models\ScheduleBreak;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ScheduleBreak */
class ScheduleBreaksResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'start_time' => $this->start_time,
            'end_time' => $this->end_time
        ];
    }
}
