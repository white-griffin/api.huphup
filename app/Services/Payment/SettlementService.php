<?php

namespace App\Services\Payment;

use App\Enums\WalletTransactionType;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\WalletTransaction;
use App\Services\Commission\CommissionService;
use App\Services\Wallet\WalletService;

class SettlementService
{

    public function __construct(
        private readonly WalletService $walletService,
        private readonly CommissionService $commissionService,
    ) {
    }

    public function settle(Payment $payment): void
    {
        $exists = WalletTransaction::query()
            ->where('payment_id', $payment->id)
            ->where('type', WalletTransactionType::PAYMENT->value)
            ->exists();


        if ($exists) {
            return;
        }


        $payable = $payment->payable;

        $business = $payable->business;


        $amount = (int) $payment->amount;


        $rate = $business->reputation?->current_commission_rate ?? 0;


        $commissionAmount = $this->commissionService
            ->calculateAmount(
                amount: $amount,
                rate: $rate
            );


        if ($commissionAmount > 0) {

            Commission::firstOrCreate(
                [
                    'payment_id' => $payment->id,
                ],
                [
                    'business_id' => $business->id,
                    'payable_type' => $payment->payable_type,
                    'payable_id' => $payment->payable_id,
                    'amount' => $commissionAmount,
                    'rate' => $rate,
                ]
            );
        }


        $this->walletService
            ->creditPending(
                wallet: $business->getWallet(),
                amount: $amount - $commissionAmount,
                type: WalletTransactionType::PAYMENT,
                payment: $payment,
                description: "تسویه پرداخت #{$payment->id}"
            );
    }
}
