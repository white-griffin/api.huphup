<?php

namespace App\Jobs;

use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Models\Order;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $order = Order::query()
                ->where('id', $this->orderId)
                ->where('order_status', OrderStatuses::PENDING->value)
                ->where('payment_status', PaymentStatuses::UNPAID->value)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return; // سفارش پرداخت شده یا قبلاً لغو شده
            }

            $order->load('items');

            $variationIds = $order->items->pluck('product_variation_id')->sort()->values();

            $variations = ProductVariation::query()
                ->whereIn('id', $variationIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($order->items as $item) {
                $variations->get($item->product_variation_id)
                    ?->increment('stock', $item->quantity);
            }

            $order->update([
                'order_status' => OrderStatuses::CANCELED->value,
                'payment_status' => PaymentStatuses::EXPIRED->value,
            ]);
        });
    }
}
