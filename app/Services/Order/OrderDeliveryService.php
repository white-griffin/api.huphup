<?php

namespace App\Services\Order;

use App\Enums\OrderItemStatuses;
use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\ShipmentStatuses;
use App\Models\Shipment;
use App\Services\Payment\SettlementService;
use DomainException;
use Illuminate\Support\Facades\DB;

class OrderDeliveryService
{

    public function __construct(
        private readonly SettlementService $settlementService,
    ) {
    }

    public function delivered(Shipment $shipment): void
    {

        DB::transaction(function () use ($shipment) {

            $shipment = Shipment::query()
                ->lockForUpdate()
                ->with([
                    'orderVendor.order.vendors',
                    'orderVendor.business',
                ])
                ->findOrFail($shipment->id);

            if ($shipment->status != ShipmentStatuses::DELIVERED) {
                throw new DomainException(
                    'مرسوله هنوز تحویل داده نشده است.'
                );
            }

            $orderVendor = $shipment->orderVendor;

            if (! $orderVendor) {
                throw new DomainException(
                    'فروشنده سفارش برای این مرسوله یافت نشد.'
                );
            }

            $order = $orderVendor->order;

            if (! $order) {
                throw new DomainException(
                    'سفارش مربوط به مرسوله یافت نشد.'
                );
            }

            /*
             * اگر قبلاً این فروشنده تکمیل شده باشد،
             * دوباره settlement انجام نمی‌شود.
             */
            if (
                $orderVendor->status ==
                OrderVendorStatuses::COMPLETED->value
            ) {
                return;
            }

            /*
             * فقط فروشنده‌ی مربوط به همین Shipment
             * تکمیل می‌شود.
             */
            $orderVendor->update([
                'status' => OrderVendorStatuses::COMPLETED->value,
            ]);

            $orderVendor->items()->update([
                'status' => OrderItemStatuses::COMPLETED->value,
            ]);

            /*
             * بعد از تکمیل فروشنده، فقط همان فروشنده
             * تسویه می‌شود.
             */
            $payment = $orderVendor
                ->order
                ->payments()
                ->where('payment_status', PaymentStatuses::PAID->value)
                ->whereNull('settled_at')
                ->latest('id')
                ->first();

            if ($payment) {
                $this->settlementService->settle($payment,$orderVendor);
            }

            /*
             * اگر همه‌ی فروشندگان تکمیل شده باشند،
             * وضعیت کل سفارش COMPLETED می‌شود.
             */
            $hasIncompleteVendor = $order->vendors()
                ->whereNotIn('status', [
                    OrderVendorStatuses::COMPLETED->value,
                    OrderVendorStatuses::CANCELED->value,
                ])
                ->exists();

            if (! $hasIncompleteVendor) {
                $order->update([
                    'order_status' =>
                        OrderStatuses::COMPLETED->value,
                ]);

                return;
            }

            /*
             * اگر حداقل یک فروشنده ارسال شده باشد ولی
             * هنوز همه‌ی فروشندگان تکمیل نشده باشند،
             * سفارش SHIPPED باقی می‌ماند.
             */
            $hasShippedVendor = $order->vendors()
                ->whereIn('status', [
                    OrderVendorStatuses::SHIPPED->value,
                    OrderVendorStatuses::COMPLETED->value,
                ])
                ->exists();

            if ($hasShippedVendor) {
                $order->update([
                    'order_status' =>
                        OrderStatuses::SHIPPED->value,
                ]);
            }
        });
    }
}
