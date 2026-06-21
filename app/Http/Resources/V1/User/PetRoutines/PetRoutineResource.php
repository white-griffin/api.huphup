<?php

namespace App\Http\Resources\V1\User\PetRoutines;

use App\Http\Resources\V1\User\Pets\PetResource;
use App\Models\PetRoutine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PetRoutine */
class PetRoutineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pet' => PetResource::make($this->whenLoaded('pet')),
            'template' => $this->template,
            'interval_days' => $this->interval_days,
            'start_date' => $this->start_date,
            'last_done_at' => $this->last_done_at,
            'next_due_at' => $this->next_due_at,
            'notification_enabled' => $this->notification_enabled,
            'routine_status' => $this->routine_status,
            'settings' => $this->settings
        ];
    }
}
