<?php

namespace App\Http\Resources\V1\User;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'image' => $this->image_url,
            'order' => $this->sort_order,
            'price' => $this->pivot->price,
            'duration' => $this->pivot->duration,
            'settings' => $this->pivot->settings,
        ];
    }
}
