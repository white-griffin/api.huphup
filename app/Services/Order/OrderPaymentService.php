<?php

namespace App\Services\Order;

use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\Order;
use App\Models\OrderVendor;
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
                ->with('vendors.business')
                ->findOrFail($order->id);

            if (
                $order->payment_status ===
                PaymentStatuses::PAID->value
            ) {
                return;
            }

            $vendors = $order->vendors
                ->filter(
                    fn (OrderVendor $vendor) =>
                        $vendor->status ===
                        OrderVendorStatuses::PENDING->value
                )
                ->values();

            if ($vendors->isEmpty()) {
                throw new \DomainException(
                    'هیچ فروشنده‌ای در وضعیت قابل پرداخت نیست.'
                );
            }

            $totalAmount = (int) $vendors->sum('total_amount');

            if ($totalAmount <= 0) {
                throw new \DomainException(
                    'مبلغ قابل پرداخت سفارش معتبر نیست.'
                );
            }

            $remainingAmount = (int) $payment->amount;

            foreach ($vendors as $index => $vendor) {

                if ($index === $vendors->count() - 1) {
                    $paidAmount = $remainingAmount;
                } else {
                    $paidAmount = (int) round(
                        $payment->amount
                        * ($vendor->total_amount / $totalAmount)
                    );

                    $remainingAmount -= $paidAmount;
                }

                $vendor->update([
                    'paid_amount' => $paidAmount,
                    'status' => OrderVendorStatuses::PAID->value,
                ]);

                if ($paidAmount > 0) {
                    app(WalletService::class)->creditPending(
                        wallet: $vendor->business->getWallet(),
                        amount: $paidAmount,
                        type: WalletTransactionType::PAYMENT,
                        payment: $payment,
                        description: "ایجاد موجودی معلق سفارش #{$order->id}",
                    );
                }
            }

            if ($remainingAmount !== 0) {
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
                $order->payment_status ===
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
