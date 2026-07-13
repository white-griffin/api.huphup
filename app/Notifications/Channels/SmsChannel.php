<?php

namespace App\Notifications\Channels;

use App\Services\Sms\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $data = $notification->toSms($notifiable);

        app(SmsService::class)
            ->provider($data['provider'])
            ->send(
                to: $notifiable->mobile,
                message: $data['message']
            );
    }
}
