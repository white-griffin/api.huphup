<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Services\Commission\CommissionService;
use App\Services\Wallet\WalletService;
use DomainException;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly CommissionService $commissionService,
    ) {
    }

    public function settle(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {

            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->settled_at != null) {
                return;
            }

            if (
                $payment->payment_status !=
                PaymentStatuses::PAID->value
            ) {
                throw new DomainException(
                    'فقط پرداخت موفق قابل تسویه است.'
                );
            }

            $payable = $payment->payable;

            if (! $payable) {
                throw new DomainException(
                    'موضوع پرداخت برای تسویه یافت نشد.'
                );
            }

            match (true) {
                $payable instanceof Order =>
                $this->settleOrder(
                    payment: $payment,
                    order: $payable,
                ),

                $payable instanceof Appointment =>
                $this->settleAppointment(
                    payment: $payment,
                    appointment: $payable,
                ),

                $payable instanceof OrderVendor =>
                $this->settleOrderVendor(
                    payment: $payment,
                    orderVendor: $payable,
                ),

                default => null,
            };

            $payment->update([
                'settled_at' => now(),
            ]);
        });
    }

    private function settleOrder(
        Payment $payment,
        Order $order,
    ): void {
        $order->loadMissing('vendors.business');

        foreach ($order->vendors as $orderVendor) {
            $this->settleOrderVendorAmount(
                payment: $payment,
                orderVendor: $orderVendor,
                amount: (int) $orderVendor->paid_amount,
            );
        }
    }

    private function settleOrderVendor(
        Payment $payment,
        OrderVendor $orderVendor,
    ): void {
        $orderVendor->loadMissing('business');

        $this->settleOrderVendorAmount(
            payment: $payment,
            orderVendor: $orderVendor,
            amount: (int) $orderVendor->paid_amount,
        );
    }

    private function settleOrderVendorAmount(
        Payment $payment,
        OrderVendor $orderVendor,
        int $amount,
    ): void {
        if ($amount <= 0) {
            return;
        }

        $business = $orderVendor->business;

        if (! $business) {
            throw new DomainException(
                'فروشنده سفارش یافت نشد.'
            );
        }

        $rate = $business->reputation?->current_commission_rate ?? 0;

        $commissionAmount = $this->commissionService->calculateAmount(
            amount: $amount,
            rate: $rate,
        );

        $commission = Commission::firstOrCreate(
            [
                'payment_id' => $payment->id,
                'payable_type' => OrderVendor::class,
                'payable_id' => $orderVendor->id,
            ],
            [
                'business_id' => $business->id,
                'amount' => $commissionAmount,
                'rate' => $rate,
            ]
        );

        $this->walletService->settlePending(
            wallet: $business->getWallet(),
            amount: $amount,
            commissionAmount: (int) $commission->amount,
            type: WalletTransactionType::PAYMENT,
            payment: $payment,
            description: "تسویه پرداخت #{$payment->id}",
        );
    }

    private function settleAppointment(
        Payment $payment,
        Appointment $appointment,
    ): void {
        $appointment->loadMissing('business');

        $business = $appointment->business;

        if (! $business) {
            throw new DomainException(
                'فروشنده رزرو یافت نشد.'
            );
        }

        $amount = max(
            0,
            (int) $payment->amount - (int) $appointment->refund_amount
        );

        if ($amount <= 0) {
            return;
        }

        $rate = $business->reputation?->current_commission_rate ?? 0;

        $commissionAmount = $this->commissionService->calculateAmount(
            amount: $amount,
            rate: $rate,
        );

        $commission = Commission::firstOrCreate(
            [
                'payment_id' => $payment->id,
                'payable_type' => Appointment::class,
                'payable_id' => $appointment->id,
            ],
            [
                'business_id' => $business->id,
                'amount' => $commissionAmount,
                'rate' => $rate,
            ]
        );

        $this->walletService->settlePending(
            wallet: $business->getWallet(),
            amount: $amount,
            commissionAmount: (int) $commission->amount,
            type: WalletTransactionType::PAYMENT,
            payment: $payment,
            description: "تسویه رزرو #{$appointment->id}",
        );
    }
}
