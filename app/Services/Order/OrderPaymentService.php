<?php

namespace App\Services\Order;

use App\Enums\OrderItemStatuses;
use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function succeeded(
        Order $order,
        Payment $payment,
    ): void {
        DB::transaction(function () use ($order, $payment) {

            $order = Order::query()
                ->lockForUpdate()
                ->with([
                    'vendors.items',
                    'vendors.business',
                ])
                ->findOrFail($order->id);

            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (
                $order->payment_status ==
                PaymentStatuses::PAID->value
            ) {
                return;
            }

            $paymentAmount = (int) $payment->amount;

            $totalOrderAmount = (int) $order->total_amount;

            if ($totalOrderAmount <= 0) {
                throw new \DomainException(
                    'مبلغ سفارش معتبر نیست.'
                );
            }

            $remainingPayment = $paymentAmount;

            foreach ($order->vendors as $vendor) {

                $vendorTotal = (int) $vendor->total_amount;

                if ($vendorTotal <= 0) {
                    $vendor->update([
                        'paid_amount' => 0,
                        'status' => OrderVendorStatuses::PAID->value,
                    ]);

                    continue;
                }

                /*
                 * سهم Vendor از مبلغ واقعی پرداخت‌شده.
                 *
                 * آخرین Vendor باقیمانده را می‌گیرد تا
                 * rounding باعث اختلاف نشود.
                 */
                $isLastVendor =
                    $vendor->id === $order->vendors->last()->id;

                if ($isLastVendor) {
                    $vendorPaidAmount = $remainingPayment;
                } else {
                    $vendorPaidAmount = (int) round(
                        $paymentAmount
                        * ($vendorTotal / $totalOrderAmount)
                    );

                    $remainingPayment -= $vendorPaidAmount;
                }

                $vendor->update([
                    'paid_amount' => $vendorPaidAmount,
                    'status' => OrderVendorStatuses::PAID->value,
                ]);

                /*
                 * تخصیص مبلغ Vendor بین Itemهای خودش.
                 */
                $remainingVendorAmount = $vendorPaidAmount;

                $vendorItems = $vendor->items
                    ->where(
                        'status',
                        OrderItemStatuses::PENDING->value
                    )
                    ->values();

                $vendorTotalAmount = $vendorItems->sum(
                    fn ($item) => (int) $item->total_price
                );

                foreach ($vendorItems as $index => $item) {

                    $isLastItem =
                        $index === $vendorItems->count() - 1;

                    if ($isLastItem) {
                        $itemPaidAmount = $remainingVendorAmount;
                    } else {
                        $itemPaidAmount = $vendorTotalAmount > 0
                            ? (int) round(
                                $vendorPaidAmount
                                * (
                                    (int) $item->total_price
                                    / $vendorTotalAmount
                                )
                            )
                            : 0;

                        $remainingVendorAmount -= $itemPaidAmount;
                    }

                    $item->update([
                        'paid_amount' => $itemPaidAmount,
                    ]);
                }

                if ($remainingVendorAmount != 0) {
                    throw new \DomainException(
                        'تخصیص مبلغ پرداخت بین آیتم‌های فروشنده با مبلغ پرداختی مطابقت ندارد.'
                    );
                }

                if ($vendorPaidAmount > 0) {
                    app(WalletService::class)->creditPending(
                        wallet: $vendor->business->getWallet(),
                        amount: $vendorPaidAmount,
                        type: WalletTransactionType::PAYMENT,
                        payment: $payment,
                        description: "ایجاد موجودی معلق سفارش #{$order->id}",
                    );
                }
            }

            if ($remainingPayment != 0) {
                throw new \DomainException(
                    'تخصیص مبلغ پرداخت بین فروشندگان با مبلغ پرداختی مطابقت ندارد.'
                );
            }

            $order->update([
                'payment_status' => PaymentStatuses::PAID->value,
                'order_status' => OrderStatuses::PAID->value,
            ]);
        });
    }

    public function failed(
        Order $order,
        Payment $payment,
    ): void {
        DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (
                $order->payment_status ==
                PaymentStatuses::PAID->value
            ) {
                return;
            }

            $order->update([
                'payment_status' => PaymentStatuses::FAILED->value,
            ]);

            $order->vendors()->update([
                'status' => OrderVendorStatuses::FAILED->value,
            ]);
        });
    }
}
