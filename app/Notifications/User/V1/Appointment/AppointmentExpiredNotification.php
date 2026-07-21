<?php

namespace App\Notifications\User\V1\Appointment;

use App\Enums\SmsProviders;
use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
    )
    {
    }

    public function via($notifiable): array
    {
        return [
            'database',
            SmsChannel::class,
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'appointment_expired',
            'title' => 'رزرو منقضی شد',
            'body' => 'پرداخت انجام نشد و رزرو منقضی شد.',
            'action' => [
                'type' => 'appointment_expired',
                'id' => $this->appointment->id,
            ],
        ];
    }

    public function toSms($notifiable): array
    {
        return [
            'provider' => SmsProviders::SMS_IR,
            'message' => 'رزرو شما به علت عدم پرداخت منقضی شد.',
        ];
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
