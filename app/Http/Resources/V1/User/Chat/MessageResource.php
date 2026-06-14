<?php

namespace App\Http\Resources\V1\User\Chat;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'body'            => $this->body,
            'type'            => $this->type,
            'read_at'         => $this->read_at,
            'created_at'      => $this->created_at->toISOString(),
            'sender'          => [
                'id'     => $this->sender->id,
                'name'   => $this->sender->full_name,
                'avatar' => $this->sender->avatar_url ?? null,
            ],
            'is_mine' => $this->sender_id === $request->user()->id,
        ];
    }
}
