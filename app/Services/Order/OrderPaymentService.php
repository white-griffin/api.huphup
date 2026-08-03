<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function vendorSucceeded(
        OrderVendor $orderVendor,
        Payment $payment,
    ): void {
        DB::transaction(function () use ($orderVendor, $payment) {

            $orderVendor = OrderVendor::query()
                ->lockForUpdate()
                ->with('business')
                ->findOrFail($orderVendor->id);

            /*
             * Idempotency
             */
            if (
                $orderVendor->status ===
                OrderVendorStatuses::PAID->value
            ) {
                return;
            }

            /*
             * پرداخت‌های غیر Wallet:
             * مبلغ ابتدا وارد pending_balance بیزنس می‌شود.
             *
             * پرداخت Wallet قبلاً توسط PaymentService
             * به pending_balance منتقل شده است.
             */
            if (
                $payment->gateway !==
                PaymentGateways::WALLET->value
            ) {
                app(WalletService::class)->creditPending(
                    wallet: $orderVendor->business->getWallet(),
                    amount: (int) $payment->amount,
                    type: WalletTransactionType::PAYMENT,
                    payment: $payment,
                    description: "ایجاد موجودی معلق پرداخت #{$payment->id}",
                );
            }

            /*
             * Vendor پرداخت شده است.
             *
             * نکته مهم:
             * اینجا هنوز Settlement انجام نمی‌شود.
             *
             * Settlement بعد از تحویل موفق Shipment انجام می‌شود.
             */
            $orderVendor->update([
                'status' => OrderVendorStatuses::PAID->value,
            ]);

            /*
             * وضعیت Order اصلی را Sync می‌کنیم.
             */
            $this->syncOrderStatus($orderVendor);
        });
    }

    public function vendorFailed(
        OrderVendor $orderVendor,
        Payment $payment,
    ): void {
        DB::transaction(function () use ($orderVendor) {

            $orderVendor = OrderVendor::query()
                ->lockForUpdate()
                ->findOrFail($orderVendor->id);

            /*
             * اگر قبلاً موفق شده، Failed نباید آن را تغییر دهد.
             */
            if (
                $orderVendor->status ===
                OrderVendorStatuses::PAID->value
            ) {
                return;
            }

            $orderVendor->update([
                'status' => OrderVendorStatuses::FAILED->value,
            ]);

            $this->syncOrderStatus($orderVendor);
        });
    }

    private function syncOrderStatus(
        OrderVendor $orderVendor
    ): void {
        $order = $orderVendor->order()
            ->lockForUpdate()
            ->firstOrFail();

        $vendors = $order->vendors()->get();

        /*
         * همه Vendorها پرداخت شده‌اند.
         */
        if (
            $vendors->isNotEmpty()
            && $vendors->every(
                fn (OrderVendor $vendor) =>
                    $vendor->status ===
                    OrderVendorStatuses::PAID->value
            )
        ) {
            $order->update([
                'payment_status' =>
                    PaymentStatuses::PAID->value,

                'order_status' =>
                    OrderStatuses::PAID->value,
            ]);

            return;
        }

        /*
         * حداقل یک Vendor پرداخت ناموفق داشته است.
         */
        if (
            $vendors->contains(
                fn (OrderVendor $vendor) =>
                    $vendor->status ===
                    OrderVendorStatuses::FAILED->value
            )
        ) {
            $order->update([
                'payment_status' =>
                    PaymentStatuses::FAILED->value,
            ]);
        }
    }
}
