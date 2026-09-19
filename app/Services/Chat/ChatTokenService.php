<?php

namespace App\Services\Chat;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChatTokenService
{
    public function getToken(User $user): string
    {
        $cacheKey = $this->getCacheKey($user);

        $result = Cache::remember(
            $cacheKey,
            now()->addDays(6)->addHours(23),
            function () use ($user) {
                return app(ChatService::class)->createToken(
                    externalType: 'USER',
                    externalId: (string) $user->id,
                );
            }
        );

        return $result['token'];
    }

    private function getCacheKey(User $user): string
    {
        return "chat:token:user:{$user->id}";
    }
}
