<?php

namespace App\Services\Chat;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChatTokenService
{
    public function getToken(User $user): string
    {
        $cacheKey = $this->getCacheKey($user);

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($this->getTokenTtl()),
            function () use ($user) {
                $result = app(ChatService::class)->createToken(
                    externalType: 'USER',
                    externalId: (string) $user->id,
                );

                return $result['token'];
            }
        );
    }

    private function getCacheKey(User $user): string
    {
        return "chat:token:user:{$user->id}";
    }

    private function getTokenTtl(): int
    {
        return 60 * 60 * 24 * 6;
    }
}
