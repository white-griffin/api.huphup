<?php

namespace App\Http\Resources\V1\User;

use App\Filament\Resources\Users\UserResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Review */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'rating' => $this->rating,
            'title' => $this->title,
            'body' => $this->body,

            'status' => $this->status,

            'is_verified_purchase' => $this->is_verified_purchase,

            'created_at' => $this->created_at,

            'user' => UserResource::make($this->whenLoaded('user')),

            'messages' => ReviewMessageResource::collection(
                $this->whenLoaded('messages')
            ),
        ];
    }
}
