<?php

namespace App\Notifications\User\V1;

use App\Enums\SmsProviders;
use App\Models\Order;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
            SmsChannel::class,
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_created',
            'title' => 'سفارش ثبت شد',
            'body' => 'سفارش شما با موفقیت ثبت شد.',
            'action' => [
                'type' => 'order',
                'id' => $this->order->id,
            ],
        ];
    }

    public function toSms($notifiable): array
    {
        return [
            'provider' => SmsProviders::SMS_IR,
            'message' => 'سفارش شما ثبت شد.',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
