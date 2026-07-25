<?php

namespace App\Http\Resources\V1\User;

use App\Models\ReviewMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReviewMessage */
class ReviewMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'body' => $this->body,

            'status' => $this->status,

            'created_at' => $this->created_at,

            'author' => $this->whenLoaded('author', function () {
                return [
                    'type' => class_basename($this->author_type),
                    'data' => $this->author,
                ];
            }),

            'business' => BusinessResource::make(
                $this->whenLoaded('business')
            ),

            'replies' => self::collection(
                $this->whenLoaded('replies')
            ),
        ];
    }
}
