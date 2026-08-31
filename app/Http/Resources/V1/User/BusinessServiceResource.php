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
            'id' => $this->id,
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
            'rating' => $this->reviewSummary?->rating_avg,

            'rating_count' => $this->reviewSummary?->rating_count,

            'rating_stars' => [
                'one_star' => $this->reviewSummary?->one_star ?? 0,
                'two_star' => $this->reviewSummary?->two_star ?? 0,
                'three_star' => $this->reviewSummary?->three_star ?? 0,
                'four_star' => $this->reviewSummary?->four_star ?? 0,
                'five_star' => $this->reviewSummary?->five_star ?? 0
            ],

            'review_count' => $this->reviewSummary?->review_count,
        ];
    }
}
