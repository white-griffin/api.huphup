<?php

namespace App\Services\Order;

use App\Enums\OrderVendorStatuses;
use App\Models\OrderVendor;
use App\Services\Logistics\ShippingService;
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
                $orderVendor->status !==
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
                ->findOrFail($orderVendor->id);

            if (
                $orderVendor->status !==
                OrderVendorStatuses::PAID->value
            ) {
                throw new \DomainException(
                    'این سفارش در وضعیت قابل رد کردن نیست.'
                );
            }

            $orderVendor->update([
                'status' => OrderVendorStatuses::FAILED->value,
            ]);

            return $orderVendor->fresh([
                'order',
                'items',
                'business',
            ]);
        });
    }
}
