<?php

namespace App\Services\MongoChatService;

use App\Models\MongoDB\ChatUser;
use App\Models\User;

class ChatUserResolver
{
    public function resolve(ChatUser $chatUser): ?User
    {
        if ($chatUser->externalType !== 'USER') {
            return null;
        }

        return User::query()
            ->find((int) $chatUser->externalId);
    }

    public function resolveMany(iterable $chatUsers): array
    {
        $userIds = collect($chatUsers)
            ->filter()
            ->filter(
                fn (ChatUser $chatUser) =>
                    $chatUser->externalType === 'USER'
            )
            ->pluck('externalId')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id')
            ->all();
    }
}
