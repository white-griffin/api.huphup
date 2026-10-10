<?php

namespace App\Notifications\Channels;


use App\Models\UserNotice;
use Illuminate\Notifications\Notification;

class UserNoticeChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $data = $notification->toUserNotice($notifiable);

        UserNotice::create([
            'user_id' => $notifiable->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'data' => $data['data'] ?? null,
        ]);
    }
}
