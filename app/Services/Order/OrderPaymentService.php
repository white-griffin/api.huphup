<?php

namespace App\Services\Order;

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

            if (
                $payment->payment_status !=
                PaymentStatuses::PAID->value
            ) {
                throw new \DomainException(
                    'وضعیت پرداخت موفق نیست.'
                );
            }

            $paymentAmount = (int) $payment->amount;
            $orderTotalAmount = (int) $order->total_amount;

            if ($orderTotalAmount <= 0) {
                throw new \DomainException(
                    'مبلغ سفارش معتبر نیست.'
                );
            }

            if ($paymentAmount <= 0) {
                throw new \DomainException(
                    'مبلغ پرداخت معتبر نیست.'
                );
            }

            if ($paymentAmount != $orderTotalAmount) {
                throw new \DomainException(
                    'مبلغ پرداخت با مبلغ سفارش مطابقت ندارد.'
                );
            }

            $allocatedVendorAmount = 0;
            $allocatedShippingAmount = 0;

            foreach ($order->vendors as $vendor) {

                $vendorProductAmount =
                    (int) $vendor->subtotal_amount
                    - (int) $vendor->discount_amount;


                $shippingAmount = (int) $vendor->shipping_amount;

                if ($vendorProductAmount < 0) {
                    throw new \DomainException(
                        'مبلغ محصولات فروشنده معتبر نیست.'
                    );
                }

                if ($shippingAmount < 0) {
                    throw new \DomainException(
                        'هزینه ارسال فروشنده معتبر نیست.'
                    );
                }

                $vendorTotalAmount =
                    $vendorProductAmount + $shippingAmount;

                if (
                    $vendorTotalAmount !=
                    (int) $vendor->total_amount
                ) {
                    throw new \DomainException(
                        'مبلغ نهایی فروشنده با مبالغ کالا و ارسال مطابقت ندارد.'
                    );
                }

                /*
                 * مبلغی که متعلق به خود فروشنده است.
                 * هزینه ارسال اینجا وارد نمی‌شود.
                 */
                $vendor->update([
                    'paid_amount' => $vendorProductAmount,
                    'status' => OrderVendorStatuses::PAID->value,
                ]);

                /*
                 * تخصیص مبلغ فروشنده بین آیتم‌ها
                 */
                $vendorItems = $vendor->items->values();

                $itemsTotal = $vendorItems->sum(
                    fn ($item) => (int) $item->total_price
                );

                if ($itemsTotal != $vendorProductAmount) {
                    throw new \DomainException(
                        'مجموع مبلغ آیتم‌های فروشنده با مبلغ قابل پرداخت فروشنده مطابقت ندارد.'
                    );
                }

                $remainingVendorAmount = $vendorProductAmount;

                foreach ($vendorItems as $index => $item) {

                    $isLastItem =
                        $index == $vendorItems->count() - 1;

                    if ($isLastItem) {
                        $itemPaidAmount = $remainingVendorAmount;
                        $remainingVendorAmount = 0;
                    } else {
                        $itemPaidAmount = $itemsTotal > 0
                            ? (int) round(
                                $vendorProductAmount
                                * (
                                    (int) $item->total_price
                                    / $itemsTotal
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
                        'تخصیص مبلغ پرداخت بین آیتم‌های فروشنده صحیح نیست.'
                    );
                }

                /*
                 * فقط سهم فروشنده وارد موجودی معلق می‌شود.
                 * shipping_amount متعلق به فروشنده نیست.
                 */
                if ($vendorProductAmount > 0) {
                    app(WalletService::class)->creditPending(
                        wallet: $vendor->business->getWallet(),
                        amount: $vendorProductAmount,
                        type: WalletTransactionType::PAYMENT,
                        payment: $payment,
                        description: "ایجاد موجودی معلق سفارش #{$order->id}",
                    );
                }

                $allocatedVendorAmount += $vendorProductAmount;
                $allocatedShippingAmount += $shippingAmount;
            }

            $allocatedTotal =
                $allocatedVendorAmount
                + $allocatedShippingAmount;

            if ($allocatedTotal != $paymentAmount) {
                throw new \DomainException(
                    'مجموع مبالغ فروشندگان و هزینه ارسال با مبلغ پرداختی مطابقت ندارد.'
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
