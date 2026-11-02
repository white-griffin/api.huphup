<?php

namespace App\Services\Order;

use App\Enums\OrderItemStatuses;
use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\ProductVariation;
use App\Services\Logistics\ShippingService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class OrderVendorService
{
    public function __construct(
        private readonly ShippingService $shippingService,
    ) {
    }

    public function accept(OrderVendor $orderVendor): OrderVendor
    {
        return DB::transaction(function () use ($orderVendor) {

            $orderVendor = OrderVendor::query()
                ->lockForUpdate()
                ->findOrFail($orderVendor->id);

            if (
                $orderVendor->status !=
                OrderVendorStatuses::PAID->value
            ) {
                throw new \DomainException(
                    'این سفارش در وضعیت قابل تأیید نیست.'
                );
            }

            $orderVendor->update([
                'status' => OrderVendorStatuses::PROCESSING->value,
            ]);

            $orderVendor->items()->update([
                'status' => OrderItemStatuses::PROCESSING->value,
            ]);

            $this->shippingService->create($orderVendor);

            return $orderVendor->fresh([
                'order',
                'items',
                'business',
                'shipments',
            ]);
        });
    }

    public function reject(OrderVendor $orderVendor): OrderVendor
    {
        return DB::transaction(function () use ($orderVendor) {

            $orderVendor = OrderVendor::query()
                ->lockForUpdate()
                ->with([
                    'order.user',
                    'business',
                    'items',
                ])
                ->findOrFail($orderVendor->id);

            if (
                $orderVendor->status !=
                OrderVendorStatuses::PAID->value
            ) {
                throw new \DomainException(
                    'این سفارش در وضعیت قابل رد کردن نیست.'
                );
            }

            $order = $orderVendor->order;

            $payment = $order->payments()
                ->where(
                    'payment_status',
                    PaymentStatuses::PAID->value
                )
                ->latest('id')
                ->first();

            if (! $payment) {
                throw new \DomainException(
                    'پرداخت موفقی برای این سفارش پیدا نشد.'
                );
            }

            $refundAmount = (int) $orderVendor->paid_amount;

            if ($refundAmount > 0) {

                app(WalletService::class)->refundPending(
                    from: $orderVendor->business->getWallet(),
                    to: $order->user->getWallet(),
                    amount: $refundAmount,
                    debitType: WalletTransactionType::REFUND,
                    creditType: WalletTransactionType::REFUND,
                    payment: $payment,
                    description: "بازگشت وجه فروشنده #{$orderVendor->id} از سفارش #{$order->id}",
                );

                $orderVendor->update([
                    'paid_amount' => 0,
                ]);
            }

            foreach ($orderVendor->items as $item) {
                ProductVariation::query()
                    ->whereKey($item->product_variation_id)
                    ->increment(
                        'stock',
                        $item->quantity
                    );
            }

            $orderVendor->update([
                'status' => OrderVendorStatuses::FAILED->value,
            ]);

            $orderVendor->items()->update([
                'status' => OrderItemStatuses::CANCELED->value,
                'paid_amount' => 0,
            ]);

            return $orderVendor->fresh([
                'order',
                'items',
                'business',
                'shipments',
            ]);
        });
    }

    public function cancelItem(
        OrderItem $orderItem,
    ): OrderItem {
        return DB::transaction(function () use ($orderItem) {

            $orderItem = OrderItem::query()
                ->lockForUpdate()
                ->with([
                    'orderVendor.business',
                    'orderVendor.order.user',
                    'orderVendor.items',
                ])
                ->findOrFail($orderItem->id);

            if (
                $orderItem->status !=
                OrderItemStatuses::PENDING->value
            ) {
                throw new \DomainException(
                    'این آیتم در وضعیت قابل لغو نیست.'
                );
            }

            $orderVendor = $orderItem->orderVendor;

            if (
                $orderVendor->status ==
                OrderVendorStatuses::FAILED->value
            ) {
                throw new \DomainException(
                    'سفارش فروشنده قبلاً لغو شده است.'
                );
            }

            $order = $orderVendor->order;

            $payment = $order->payments()
                ->where(
                    'payment_status',
                    PaymentStatuses::PAID->value
                )
                ->latest('id')
                ->first();

            if (! $payment) {
                throw new \DomainException(
                    'پرداخت موفقی برای این سفارش پیدا نشد.'
                );
            }

            $refundAmount = (int) $orderItem->paid_amount;

            if ($refundAmount > 0) {

                app(WalletService::class)->refundPending(
                    from: $orderVendor->business->getWallet(),
                    to: $order->user->getWallet(),
                    amount: $refundAmount,
                    debitType: WalletTransactionType::REFUND,
                    creditType: WalletTransactionType::REFUND,
                    payment: $payment,
                    description: "بازگشت وجه آیتم #{$orderItem->id} از سفارش #{$order->id}",
                );

                $orderVendor->decrement(
                    'paid_amount',
                    $refundAmount
                );
            }

            ProductVariation::query()
                ->whereKey($orderItem->product_variation_id)
                ->increment(
                    'stock',
                    $orderItem->quantity
                );

            $orderItem->update([
                'paid_amount' => 0,
                'status' => OrderItemStatuses::CANCELED->value,
            ]);

            $remainingItems = $orderVendor->items()
                ->whereNotIn('status', [
                    OrderItemStatuses::CANCELED->value,
                ])
                ->exists();

            if (! $remainingItems) {
                $orderVendor->update([
                    'status' => OrderVendorStatuses::FAILED->value,
                    'paid_amount' => 0,
                ]);
            }

            return $orderItem->fresh([
                'product',
                'variation',
                'orderVendor',
            ]);
        });
    }

    public function getStats(): array
    {
        $stats = OrderVendor::query()
            ->where('business_id', app('business')->id)
            ->selectRaw('
            COUNT(*) as total,

            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as shipped,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as canceled,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed
        ', [
                OrderStatuses::PENDING->value,
                OrderStatuses::PAID->value,
                OrderStatuses::PROCESSING->value,
                OrderStatuses::SHIPPED->value,
                OrderStatuses::COMPLETED->value,
                OrderStatuses::CANCELED->value,
                OrderStatuses::FAILED->value,
            ])
            ->first();

        return [
            'total' => (int) $stats->total,
            'pending' => (int) $stats->pending,
            'paid' => (int) $stats->paid,
            'processing' => (int) $stats->processing,
            'shipped' => (int) $stats->shipped,
            'completed' => (int) $stats->completed,
            'canceled' => (int) $stats->canceled,
            'failed' => (int) $stats->failed,
        ];
    }
}
