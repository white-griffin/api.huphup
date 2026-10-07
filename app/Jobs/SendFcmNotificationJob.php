<?php

namespace App\Jobs;

use App\Enums\FcmNotificationAudience;
use App\Enums\FcmNotificationStatus;
use App\Models\FcmNotification;
use App\Models\User;
use App\Notifications\User\V1\FcmPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $fcmNotificationId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification = FcmNotification::findOrFail(
            $this->fcmNotificationId
        );

        $notification->update([
            'status' => FcmNotificationStatus::PROCESSING,
        ]);

        $query = User::query();

        match ($notification->audience_type) {
            FcmNotificationAudience::ALL => null,

            FcmNotificationAudience::USERS => $query->whereIn(
                'id',
                $notification->user_ids ?? [],
            ),

            FcmNotificationAudience::PLATFORM => $query->whereHas(
                'deviceTokens',
                fn ($query) => $query->where(
                    'platform',
                    $notification->platform->value,
                ),
            ),
        };

        $total = (clone $query)->count();

        $notification->update([
            'total_recipients' => $total,
        ]);

        $query->chunkById(100, function ($users) use ($notification) {
            foreach ($users as $user) {
                try {
                    $user->notify(
                        new FcmPushNotification(
                            title: $notification->title,
                            body: $notification->body,
                            data: $notification->data ?? [],
                        )
                    );

                    $notification->increment('processed_recipients');
                } catch (Throwable) {
                    $notification->increment('failed_recipients');
                }
            }
        });

        $notification->update([
            'status' => FcmNotificationStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        FcmNotification::query()
            ->whereKey($this->fcmNotificationId)
            ->update([
                'status' => FcmNotificationStatus::FAILED,
            ]);
    }
}
