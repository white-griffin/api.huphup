<?php

namespace App\Http\Resources\V1\Provider;

use App\Enums\ActivityStatus;
use App\Models\BusinessSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BusinessSchedule */
class BusinessScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_duration' => $this->slot_duration,
            'capacity' => $this->capacity,
            'activity_status' => $this->activity_status,
            'breaks' => ScheduleBreaksResource::collection($this->breaks)
        ];
    }
}
