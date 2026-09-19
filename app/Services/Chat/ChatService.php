<?php

namespace App\Services\Chat;

use Http;
use RuntimeException;

class ChatService
{

    public function createToken(
        string $externalType,
        string $externalId,
        ?string $nickname = null
    ): array {
        $response = Http::withHeaders([
            'X-Chat-Service-Secret' => env('CHAT_SERVICE_SECRET'),
        ])
            ->timeout(10)
            ->post(
                rtrim(env('CHAT_SERVICE_URL'), '/') . '/auth/internal/token',
                [
                    'externalType' => $externalType,
                    'externalId' => $externalId,
                    'nickname' => $nickname ?: $externalType . '=' . $externalId,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to authenticate with Chat Service'
            );
        }

        return $response->json();
    }

}
