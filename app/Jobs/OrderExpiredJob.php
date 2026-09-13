<?php

namespace App\Jobs;

use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $orderService = app(OrderService::class);
        $order = Order::query()
            ->where('id', $this->orderId)
            ->where('order_status', OrderStatuses::PENDING->value)
            ->where('payment_status', PaymentStatuses::UNPAID->value)
            ->lockForUpdate()
            ->first();

        if (!$order) {
            return; // سفارش پرداخت شده یا قبلاً لغو شده
        }

        $orderService->cancel($order);
    }
}
