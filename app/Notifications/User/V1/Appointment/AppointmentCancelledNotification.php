<?php

namespace App\Notifications\User\V1\Appointment;

use App\Enums\SmsProviders;
use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
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
            'type' => 'appointment_cancelled',
            'title' => 'رزرو لغو شد',
            'body' => 'بازه زمانی انتخاب‌ شده دیگر در دسترس نبود و مبلغ به کیف پول شما بازگشت داده شد.',
            'action' => [
                'type' => 'appointment_cancelled',
                'id' => $this->appointment->id,
            ],
        ];
    }

    public function toSms($notifiable): array
    {
        return [
            'provider' => SmsProviders::SMS_IR,
            'message' => 'بازه زمانی انتخاب‌ شده دیگر در دسترس نبود و مبلغ به کیف پول شما بازگشت داده شد.',
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
