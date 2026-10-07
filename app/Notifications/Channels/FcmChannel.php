<?php

namespace App\Notifications\Channels;

use App\Models\DeviceToken;
use App\Notifications\Contracts\FcmNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Throwable;

class FcmChannel
{
    public function __construct(
        private readonly Messaging $messaging,
    ) {
    }

    public function send(
        object $notifiable,
        Notification $notification,
    ): void {
        if (!$notification instanceof FcmNotification) {
            return;
        }

        try {
            $payload = $notification->toFcm($notifiable);

            if (empty($payload)) {
                return;
            }

            $tokens = DeviceToken::query()
                ->where('user_id', $notifiable->getKey())
                ->pluck('token')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($tokens)) {
                return;
            }

            $message = CloudMessage::new()
                ->withNotification(
                    FirebaseNotification::create(
                        $payload['title'] ?? '',
                        $payload['body'] ?? '',
                    )
                )
                ->withData(
                    collect($payload['data'] ?? [])
                        ->map(fn ($value) => (string) $value)
                        ->all()
                );

            $report = $this->messaging->sendMulticast(
                $message,
                $tokens,
            );

            $this->removeInvalidTokens($report);

            $this->logFailures($report);
        } catch (Throwable $e) {
            Log::error('FCM notification failed.', [
                'user_id' => $notifiable->getKey(),
                'notification' => get_class($notification),
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }
    }

    private function removeInvalidTokens($report): void
    {
        $tokens = array_unique([
            ...$report->invalidTokens(),
            ...$report->unknownTokens(),
        ]);

        if (empty($tokens)) {
            return;
        }

        DeviceToken::query()
            ->whereIn('token', $tokens)
            ->delete();
    }

    private function logFailures($report): void
    {
        if (!$report->hasFailures()) {
            return;
        }

        foreach ($report->failures()->getItems() as $failure) {
            Log::warning('FCM message delivery failed.', [
                'token' => $failure->target()->value(),
                'message' => $failure->error()->getMessage(),
                'code' => $failure->error()->getCode(),
            ]);
        }
    }
}
