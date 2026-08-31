<?php

namespace App\Http\Resources\V1\User\PetRoutines;

use App\Enums\RoutineCategoryTypes;
use App\Http\Resources\V1\User\Pets\SpeciesResource;
use App\Models\RoutineTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoutineTemplate */
class RoutineTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'species' => SpeciesResource::make($this->species),
            'category' => $this->routine_category,
            'default_interval_days' => $this->default_interval_days,
            'reminder_days_before' => $this->reminder_days_before,
            'image' => $this->image_url,
            'description' => $this->description
        ];
    }
}
