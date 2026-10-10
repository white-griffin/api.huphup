<?php

namespace App\Notifications\User\V1;

use App\Enums\UserNoticeTypes;
use App\Notifications\Channels\UserNoticeChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserNoticeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string                 $title,
        public string                 $body,
        public UserNoticeTypes|string $type = UserNoticeTypes::GENERAL->value,
        public array                  $data = [],
    ) {}

    public function via($notifiable): array
    {
        return [UserNoticeChannel::class];
    }

    public function toUserNotice(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type->value,
            'data' => $this->data ?: null,
        ];
    }
}
