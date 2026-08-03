<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\Commission;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Services\Commission\CommissionService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly CommissionService $commissionService,
    ) {}

    public function settle(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {

            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->settled_at !== null) {
                return;
            }

            if ($payment->payment_status !== PaymentStatuses::PAID->value) {
                throw new \DomainException(
                    'فقط پرداخت موفق قابل تسویه است.'
                );
            }

            if ($payment->payable_type !== OrderVendor::class) {
                throw new \DomainException(
                    'فقط پرداخت سفارش فروشنده قابل تسویه است.'
                );
            }

            /** @var OrderVendor $orderVendor */
            $orderVendor = $payment->payable;

            $orderVendor->loadMissing('business');

            $business = $orderVendor->business;

            if (!$business) {
                throw new \DomainException(
                    'فروشنده سفارش یافت نشد.'
                );
            }

            $amount = (int) $payment->amount;

            $rate = $business->reputation?->current_commission_rate ?? 0;

            $commissionAmount = $this->commissionService->calculateAmount(
                amount: $amount,
                rate: $rate,
            );

            $commission = Commission::firstOrCreate(
                [
                    'payment_id' => $payment->id,
                ],
                [
                    'business_id' => $business->id,
                    'order_vendor_id' => $orderVendor->id,
                    'payable_type' => $payment->payable_type,
                    'payable_id' => $payment->payable_id,
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

            $payment->update([
                'settled_at' => now(),
            ]);
        });
    }
}
