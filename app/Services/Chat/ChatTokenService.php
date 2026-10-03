<?php

namespace App\Services\Chat;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChatTokenService
{
    public function getToken(User $user)
    {
        $cacheKey = $this->getCacheKey($user);

        return Cache::remember(
            $cacheKey,
            now()->addDays(6)->addHours(23),
            function () use ($user) {
                $result = app(ChatService::class)->createToken(
                    externalType: 'USER',
                    externalId: (string) $user->id,
                );

                return $result['token'];
            }
        );
    }

    public function getAdminToken(Admin $admin): string
    {
        $cacheKey = $this->getAdminCacheKey($admin);

        return Cache::remember(
            $cacheKey,
            now()->addDays(6)->addHours(23),
            function () use ($admin) {
                $result = app(ChatService::class)->createToken(
                    externalType: 'ADMIN',
                    externalId: (string) $admin->id,
                );

                return $result['token'];
            }
        );
    }

    private function getCacheKey(User $user): string
    {
        return "chat:token:user:{$user->id}";
    }

    private function getAdminCacheKey(Admin $admin): string
    {
        return "chat:token:admin:{$admin->id}";
    }
}
