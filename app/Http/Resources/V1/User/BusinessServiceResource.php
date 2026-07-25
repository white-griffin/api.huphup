<?php

namespace App\Http\Resources\V1\User;

use App\Models\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BusinessService  */
class BusinessServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->service->name,
            'description' => $this->service->description,
            'icon' => $this->service->icon,
            'image' => $this->service->image_url,
            'order' => $this->service->sort_order,

            'price' => $this->price,
            'duration' => $this->duration,
            'settings' => $this->settings,

            'reviews' => ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),
        ];
    }
}
