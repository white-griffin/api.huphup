<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
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

            return $orderVendor->fresh([
                'order',
                'items',
                'business',
                'shipments',
            ]);
        });
    }
}
