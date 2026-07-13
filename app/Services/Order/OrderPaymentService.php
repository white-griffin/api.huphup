<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Models\Order;
use App\Notifications\User\V1\OrderCreatedNotification;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    /**
     * پرداخت موفق
     */
    public function succeeded(Order $order): void
    {
        DB::transaction(function () use ($order) {

            $order->refresh();

            if ($order->payment_status == PaymentStatuses::PAID->value) {
                return;
            }

            $order->update([
                'payment_status' => PaymentStatuses::PAID->value,
                'order_status'   => OrderStatuses::PAID->value,
            ]);

            $order->user->notify(
                new OrderCreatedNotification($order)
            );

            // TODO:
            // ارسال نوتیفیکیشن
            // ثبت لاگ
            // ثبت امتیاز
            // ...
        });
    }

    /**
     * پرداخت ناموفق
     */
    public function failed(Order $order): void
    {
        DB::transaction(function () use ($order) {

            $order->refresh();

            if ($order->payment_status == PaymentStatuses::PAID->value) {
                return;
            }

            $order->update([
                'payment_status' => PaymentStatuses::FAILED->value,
            ]);

            // عمداً order_status تغییر نمی‌کند.
        });
    }
}
