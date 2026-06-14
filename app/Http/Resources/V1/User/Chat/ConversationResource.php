<?php

namespace App\Http\Resources\V1\User\Chat;

use App\Enums\AccessStatuses;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $me = $request->user();
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->type === AccessStatuses::PUBLIC->value
                ? $this->name
                : $this->getPrivateName($me),
            'activity_status' => $this->activity_status,
            'unread_count' => $this->messages()
                ->where('sender_id', '!=', $me->id)
                ->whereNull('read_at')
                ->count(),
            'last_message' => $this->whenLoaded('latestMessage', fn() => new MessageResource($this->latestMessage)
            ),
            'participants' => $this->whenLoaded('participants', fn() => $this->participants->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->full_name,
                'avatar' => $u->avatar_url ?? null,
            ])
            ),
            'created_at' => $this->created_at,
        ];
    }

    private function getPrivateName($me): ?string
    {
        if (!$this->relationLoaded('participants')) return null;

        $other = $this->participants->firstWhere('id', '!=', $me->id);
        return $other?->full_name;
    }
}
